<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Creative Intellectual Property Policy (https://wiki.ivao.aero/en/home/ivao/intellectual-property-policy)
 * @author Donat Marko
 * @copyright 2024 Donat Marko | www.donatus.hu
 */

global $config;
$u = Session::User();
?>

<main role="page" class="container">
	<h1>Contact us!</h1>

<?php if (Session::LoggedIn() && !empty($u->email)) : ?>
	<form id="frmContact">
		<div class="form-group">
			<label for="txtCfName">Your name</label>
			<input type="text" class="form-control" id="txtCfName" value="<?=$u->firstname . " " . $u->lastname?>" readonly>
		</div>
		<div class="form-group">
			<label for="txtCfEmail">Your email address</label>
			<input type="email" class="form-control" id="txtCfEmail" value="<?=$u->email?>" readonly>
		</div>
		<div class="form-group">
			<label for="selCfType">Subject of the message</label>
			<select class="form-control" id="selCfType">
				<option value="1">General inquiries, questions</option>
				<option value="2">Reporting bug in the system</option>
				<option value="3">Reporting incorrect flight data</option>
				<option value="4">Everything regarding private slots</option>
				<option value="5">Feedback about the event</option>
			</select>
		</div>
		<div class="alert alert-info" id="cfAlert" style="display: none"></div>
		<div class="form-group">
			<label for="txtCfMessage">Message</label>
			<textarea class="form-control" id="txtCfMessage" rows="4" minlength="20" required></textarea>
		</div>
		<button class="btn btn-success btn-lg" type="submit">Send message</button>
	</form>
<?php else : ?>
	<div class="alert alert-warning">
		Getting in touch with us through this form is only available if you are logged in and your email address has been saved <a href="profile">on your profile</a>.
	</div>
<?php endif; ?>
</main>