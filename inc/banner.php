<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Creative Intellectual Property Policy (https://wiki.ivao.aero/en/home/ivao/intellectual-property-policy)
 * @author Donat Marko
 * @copyright 2025 Donat Marko | www.donatus.hu
 */
?>

<main class="container" role="main">
	<?php if (Session::LoggedIn()) : ?>
		<div class="alert alert-primary">
			Hello, <?=Session::User()->firstname?>. You are now logged in. Time to book a flight!
		</div>
	<?php endif; ?>
	
	<?php include_once("contents/banner.html"); ?>
</main>