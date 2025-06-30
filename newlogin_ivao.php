<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Creative Intellectual Property Policy (https://wiki.ivao.aero/en/home/ivao/intellectual-property-policy)
 * @author Donat Marko
 * @copyright 2025 Donat Marko | www.donatus.hu
 */

require_once 'config-inc.php';
session_start();

/**
 * @return int|false the HTTP response code from the given HTTP response header or false
 * if the header is not an array or does not contain a valid status code.
 */
function get_http_response_code($http_response_header) {
    if (is_array($http_response_header) && isset($http_response_header[0])) {
        $parts = explode(' ', $http_response_header[0]);
        if (count($parts) > 1) {
            return (int)$parts[1];
        }
    }
    return false;
}

define('COOKIE_NAME', 'ivao_tokens');

// Get all URLs we need from the server
$openid_result = file_get_contents(IVAOSSO_OPENID_URL, false);
if ($openid_result === FALSE) {
    /* Handle error */
    die('Error while getting openid data');
}
$openid_data = json_decode($openid_result, true);

$client_id = IVAOSSO_CLIENT_ID;
$client_secret = IVAOSSO_CLIENT_SECRET;
$redirect_uri = IVAOSSO_REDIRECT_URI;

// Now we can take care of the actual authentication

if (isset($_GET['code']) && isset($_GET['state'])) {
    // User has been redirected back from the login page

    $code = $_GET['code'];

    $token_req_data = array(
        'grant_type' => 'authorization_code',
        'code' => $code,
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'redirect_uri' => $redirect_uri,
    );

    // use key 'http' even if you send the request to https://...
    $token_options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($token_req_data)
        )
    );
    $token_context  = stream_context_create($token_options);
    $token_result = file_get_contents($openid_data['token_endpoint'], false, $token_context);
    if ($token_result === FALSE) {
        /* Handle error */
        die('Error while getting token');
    }

    $token_res_data = json_decode($token_result, true);

    $access_token = $token_res_data['access_token']; // Here is the access token
    $refresh_token = $token_res_data['refresh_token']; // Here is the refresh token

    setcookie(COOKIE_NAME, json_encode(compact('access_token', 'refresh_token')), time() + 60 * 60 * 24 * 30); // 30 days
    header('Location: '.$redirect_uri); // Remove the code and state from URL since they aren't valid anymore 

} elseif (isset($_COOKIE[COOKIE_NAME])) {
    // User has already logged in

    $tokens = json_decode($_COOKIE[COOKIE_NAME], true);
    $access_token = $tokens['access_token'];
    $refresh_token = $tokens['refresh_token'];

    // Now we can use the access token to get the data

    $user_options = [
        'http' => [
            'header'  => "Authorization: Bearer $access_token\r\n",
            'method'  => 'GET',
            'ignore_errors' => true,
        ]
    ];
    $user_context  = stream_context_create($user_options);
    $user_result = file_get_contents($openid_data['userinfo_endpoint'], false, $user_context);
    if ($user_result === false) {
        die('Error while getting user data');
    }
    $user_res_resp_code = get_http_response_code($http_response_header);
    if ($user_res_resp_code === false) {
        die('Error while getting user data http response code');
    }

    if ($user_res_resp_code >= 400) {
        // Access token expired or missing
        if (!isset($refresh_token)) {
            die('Refresh token is missing');
        }

        $token_req_data = array(
            'grant_type' => 'refresh_token',
            'refresh_token' => $refresh_token,
            'client_id' => $client_id,
            'client_secret' => $client_secret
        );

        $token_options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($token_req_data),
                'ignore_errors' => true,
            ]
        ];

        $token_context  = stream_context_create($token_options);
        $token_result = file_get_contents($openid_data['token_endpoint'], false, $token_context);
        if ($token_result === false) {
            /* Handle error */
            die('Error while refreshing token');
        }

        $token_res_resp_code = get_http_response_code($http_response_header);
        if ($token_res_resp_code === false) {
            die('Error while getting refresh token http response code');
        }
    
        if ($token_res_resp_code <= 299) {
            $token_res_data = json_decode($token_result, true);

            $access_token = $token_res_data['access_token']; // Here is the new access token
            $refresh_token = $token_res_data['refresh_token']; // Here is the new refresh token
    
            setcookie(COOKIE_NAME, json_encode(compact('access_token', 'refresh_token')), time() + 60 * 60 * 24 * 30); // 30 days
            header('Location: '.$redirect_uri); // Try to use the access token again
        }

        // Delete cookie and authenticate user again
        setcookie(COOKIE_NAME, "", time() - 3600); // Reset cookie value to null and expire time to last hour
        header('Location: '.$redirect_uri); // Try to login again
    }

    $user_res_data = json_decode($user_result, true);

    $_SESSION["LOGIN"] = (object)[
        'firstname' => $user_res_data['firstName'],
        'lastname' => $user_res_data['lastName'],
        'vid' => $user_res_data['id'],
        'ratingatc' => $user_res_data['rating']['atcRating']['id'],
        'ratingpilot' => $user_res_data['rating']['pilotRating']['id'],
        'division' => $user_res_data['divisionId'],
        'country' => $user_res_data['countryId'],
        'staff' => implode(',', array_map(fn($x) => $x['id'], $user_res_data['userStaffPositions'])),
        'email' => isset($user_res_data['userStaffDetails']) ? $user_res_data['userStaffDetails']['email'].'@ivao.aero' : '', 
        'refreshToken' => $refresh_token
    ];
    header('Location: '.SITE_URL.'/login');
} else {
    // First visit : Unauthenticated user

    $base_url = $openid_data['authorization_endpoint'];
    $scopes = 'profile configuration email';
    $state = bin2hex(random_bytes(length: 32)); // Random string to prevent CSRF attacks

    $full_url = "$base_url?".http_build_query([
        'response_type' => 'code',
        'client_id' => $client_id,
        'scope' => $scopes,
        'redirect_uri' => $redirect_uri,
        'state' => $state
    ]);
    header('Location: '.$full_url);
}