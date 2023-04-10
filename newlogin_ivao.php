<?php
session_start();
define('COOKIE_NAME', 'IVAO_LOGIN');

// Get all URLs we need from the server
$openid_url = 'https://api.ivao.aero/.well-known/openid-configuration';
$openid_result = file_get_contents($openid_url, false);
if ($openid_result === FALSE) {
    /* Handle error */
    die('Error while getting openid data');
}
$openid_data = json_decode($openid_result, true);

// Now we can take care of the actual authentication
$client_id = 'REQ from DevHQ';
$client_secret = 'REQ from DevHQ';
$redirect_uri = 'http://rfe.th.ivao.aero/auth/callback';

if (isset($_GET['code']) && isset($_GET['state'])) {
    // User has been redirected back from the login page

    $code = $_GET['code']; // Valid only 15 seconds

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

    setcookie(COOKIE_NAME, json_encode(array(
        'access_token' => $access_token,
        'refresh_token' => $refresh_token,
    )), time() + 60 * 60 * 24 * 30); // 30 days

    header('Location: ' . $redirect_uri); // Remove the code and state from URL since they aren't valid anymore 

} elseif (isset($_COOKIE[COOKIE_NAME])) {
    // User has already logged in

    $tokens = json_decode($_COOKIE[COOKIE_NAME], true);
    $access_token = $tokens['access_token'];
    $refresh_token = $tokens['refresh_token'];

    // Now we can use the access token to get the data

    $user_options = array(
        'http' => array(
            'header'  => "Authorization: Bearer $access_token\r\n",
            'method'  => 'GET',
            'ignore_errors' => true,
        )
    );
    $user_context  = stream_context_create($user_options);
    $user_result = file_get_contents($openid_data['userinfo_endpoint'], false, $user_context);
    $user_res_data = json_decode($user_result, true);


    if (isset($user_res_data['description']) && ($user_res_data['description'] === 'This auth token has been revoked or expired' || $user_res_data['description'] === 'Couldn\'t decode auth token')) {
        // Access token expired, using refresh token to get a new one

        $token_req_data = array(
            'grant_type' => 'refresh_token',
            'refresh_token' => $refresh_token,
            'client_id' => $client_id,
            'client_secret' => $client_secret
        );

        $token_options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($token_req_data),
                'ignore_errors' => true,
            )
        );
        $token_context  = stream_context_create($token_options);
        $token_result = file_get_contents($openid_data['token_endpoint'], false, $token_context);
        if ($token_result === FALSE) {
            /* Handle error */
            die('Error while refreshing token');
        }

        $token_res_data = json_decode($token_result, true);

        $access_token = $token_res_data['access_token']; // Here is the new access token
        $refresh_token = $token_res_data['refresh_token']; // Here is the new refresh token

        setcookie(COOKIE_NAME, json_encode(array(
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
        )), time() + 60 * 60 * 24 * 30); // 30 days

        header('Location: ' . $redirect_uri); // Try to use the access token again
    } elseif (isset($user_res_data['description']) && ($user_res_data['description'] === 'No auth token found in request')) {
        // Access token missing from the cookie, delete cookie and authenticate user again

        setcookie(COOKIE_NAME, "", time() - 3600); // Reset cookie value to null and expire time to last hour
        header('Location: ' . $redirect_uri); // Try to login again
    }

    $_SESSION["LOGIN"]->firstname = $user_res_data['firstName'];
    $_SESSION["LOGIN"]->lastname = $user_res_data['lastName'];
    $_SESSION["LOGIN"]->vid = $user_res_data['id'];
    $_SESSION["LOGIN"]->ratingatc = $user_res_data['rating']['atcRating']['id'];
    $_SESSION["LOGIN"]->ratingpilot = $user_res_data['rating']['pilotRating']['id'];
    $_SESSION["LOGIN"]->division = $user_res_data['divisionId'];
    $_SESSION["LOGIN"]->country = $user_res_data['countryId'];
    $_SESSION["LOGIN"]->skype = "";
    $staffPosition = '';
    foreach ($user_res_data['userStaffPositions'] as $key => $value) {
        if ($key > 0) $staffPosition .= ':';
        $staffPosition .= $value['id'];
    }
    $_SESSION["LOGIN"]->staff = $staffPosition;
    header('Location: ' . $_GET["url"] . '/login');
} else {
    // First visit : Unauthenticated user

    $base_url = $openid_data['authorization_endpoint'];
    $reponse_type = 'code';
    $scopes = 'profile configuration email';
    $state = 'rfe-thailand'; // Random string to prevent CSRF attacks

    $full_url = "$base_url?response_type=$reponse_type&client_id=$client_id&scope=$scopes&redirect_uri=$redirect_uri&state=$state";
    header('location: ' . $full_url);
}
