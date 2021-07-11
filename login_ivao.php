<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Intellectual Property Policy (https://doc.ivao.aero/rules2:ipp)
 * @author Donat Marko
 * @copyright 2021 Donat Marko | www.donatus.hu
 */

session_start();
define('COOKIE_NAME', 'IVAO_LOGIN');
define('LOGIN_URL', 'http://login.ivao.aero/index.php');
define('API_URL', 'http://login.ivao.aero/api.php');
define('URL', $_GET["url"] . '/login_ivao.php');

// redirect function
function redirect()
{
	setcookie(COOKIE_NAME, '', time()-3600);
	header('Location: ' . LOGIN_URL . '?url=' . URL);
	exit;
}

// if the token is set in the link
if (isset($_GET["IVAOTOKEN"]) && $_GET['IVAOTOKEN'] !== 'error')
{
	setcookie(COOKIE_NAME, $_GET['IVAOTOKEN'], time() + 3600);
	header('Location: ' . URL);
	exit;
}
elseif ($_GET['IVAOTOKEN'] == 'error')
	die('This domain is not allowed to use the Login API! Contact webmaster at wm@ivao.aero!');

// check if the cookie is set and/or is correct
if (isset($_COOKIE[COOKIE_NAME]))
{
	$user_array = json_decode(file_get_contents(API_URL . '?type=json&token=' . $_COOKIE[COOKIE_NAME]));
	
	if ($user_array->result == 1)
	{
		$_SESSION["LOGIN"] = $user_array;
		header('Location: ' . $_GET["url"] . '/login');
	}
	else
		redirect();
}
else
	redirect();