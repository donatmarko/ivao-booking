<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Intellectual Property Policy (https://doc.ivao.aero/rules2:ipp)
 * @author Donat Marko
 * @copyright 2021 Donat Marko | www.donatus.hu
 */
?>

<main class="container" role="main">
<?php if (Session::LoggedIn()) : ?>
		<div class="alert alert-primary">
			Hello, <?=Session::User()->firstname?>! You are logged in.
		</div>
<?php endif; ?>
	
<?php include_once("contents/banner.html"); ?>

</main>