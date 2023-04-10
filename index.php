<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Intellectual Property Policy (https://doc.ivao.aero/rules2:ipp)
 * @author Donat Marko
 * @copyright 2021 Donat Marko | www.donatus.hu
 */

// only for debug purposes
// ini_set("display_errors", "on");
// error_reporting(E_ALL);

date_default_timezone_set('Etc/UTC');
require 'config-inc.php';
require 'inc/functions.php';
require 'inc/classes/db.php';
require 'inc/classes/email.php';
require 'inc/classes/user.php';
require 'inc/classes/session.php';
require 'inc/classes/config.php';
require 'inc/classes/pages_menu.php';
require 'inc/classes/flight.php';
require 'inc/classes/airport.php';
require 'inc/classes/airline.php';
require 'inc/classes/eventairport.php';
require 'inc/classes/slot.php';
require 'inc/classes/timeframe.php';
require 'vendor/autoload.php';
session_start();

$page = isset($_GET["f"]) ? $_GET["f"] : "";
$db = new DB(SQL_SERVER, SQL_USERNAME, SQL_PASSWORD, SQL_DATABASE);
$dbNav = new DB(SQL_SERVER, SQL_USERNAME, SQL_PASSWORD, SQL_DATABASE_NAV);

$config = Config::Get();
Session::CheckAccess();
Session::RequestProcessing();
Flight::TokenProcessing();

// adding items to the main menu
Menu::addItems([
	[
		"text" => "Briefing",
		"href" => "briefing"
	],
	[
		"text" => '<i class="fas fa-plane"></i> Flight booking',
		"href" => "flights"
	],
	[
		"text" => "Private slots",
		"href" => "slots",
		"condition" => count(Timeframe::GetAll()) > 0
	],
	[
		"text" => "Statistics",
		"href" => "statistics"
	],
	[
		"text" => '<i class="fas fa-user-ninja"></i> Admin area',
		"href" => "admin",
		"permission" => 2,
	],
	[
		"text" => "Login",
		"href" => "login",
		"loggedIn" => false
	],
]);

?>

<!DOCTYPE HTML>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="author" content="Donat Marko">
	<?=Session::GetXsrfToken("meta")?>
	<title><?=$config["event_name"]?> booking system | <?=$config["division_name"]?></title>
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="node_modules/@fortawesome/fontawesome-free/css/all.min.css">
	<link rel="stylesheet" href="node_modules/datatables.net-bs4/css/dataTables.bootstrap4.min.css">
	<link rel="stylesheet" href="node_modules/leaflet/dist/leaflet.css">
	<link rel="stylesheet" href="node_modules/tempusdominus-bootstrap-4/build/css/tempusdominus-bootstrap-4.min.css">
	<link rel="stylesheet" href="css/style.css">
	<?=Session::GetXsrfToken("js")?>
</head>

<body>

<?php	
echo Menu::Get();
Pages::AddJS("main");

switch ($page)
{
	case "login":
		Session::IVAOLogin();
		break;
	case "logout":
		Session::IVAOLogout();
		break;
    case "auth/callback":
        Session::OAuth2Callback();
        break;
	default:
		Pages::Add($page);
		if (Session::LoggedIn() && empty(Session::User()->email) && empty($page))
			Pages::Add("modal_email");
		break;
}

echo Pages::Get();	
?>
	
<footer class="footer">		
	<div class="container">
		<div class="row">
			<div class="col-md-4">
				<p>&copy; 2018 <a href="<?=$config["division_web"]?>" target="_blank"><?=$config["division_name"]?></a></p>
				<p><i class="far fa-envelope-open"></i> <a href="contact">Contact us!</a></p>
			</div>
			<div class="col-md-4 text-md-center">
<?php if (!empty($config["division_facebook"])): ?>
				<p><i class="fab fa-facebook-f"></i> <a href="<?=$config["division_facebook"]?>" target="_blank">Find us on Facebook</a></p>
<?php endif; ?>
<?php if (!empty($config["division_twitter"])): ?>
				<p><i class="fab fa-twitter"></i> <a href="<?=$config["division_twitter"]?>" target="_blank">Find us on Twitter</a></p>
<?php endif; ?>
			</div>
			<div class="col-md-4 text-md-right">
				<p>Developed by <a href="https://www.ivao.aero/Member.aspx?ID=540147" target="_blank">Donat Marko (540147)</a></p>
			</div>
		</div>
	</div>
</footer>

<div class="loader"></div>
<script src="node_modules/jquery/dist/jquery.min.js"></script>
<script src="node_modules/moment/moment.js"></script>
<script src="node_modules/moment/locale/en-gb.js"></script>
<script src="node_modules/popper.js/dist/umd/popper.min.js"></script>
<script src="node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="node_modules/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
<script src="node_modules/leaflet/dist/leaflet.js"></script>
<script src="node_modules/tempusdominus-bootstrap-4/build/js/tempusdominus-bootstrap-4.min.js"></script>
<script src="node_modules/ckeditor/ckeditor.js"></script>
<script src="https://unpkg.com/leaflet-arc/bin/leaflet-arc.min.js"></script>
<?=Pages::GetJS()?>

</body>

</html>

<?php
$db->Close();
$dbNav->Close();
?>