<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Creative Intellectual Property Policy (https://wiki.ivao.aero/en/home/ivao/intellectual-property-policy)
 * @author Donat Marko
 * @copyright 2025 Donat Marko | www.donatus.hu
 */
?>

<main role="main" class="container">
	<h1>Your profile stored by us</h1>
	
	<?php
	$u = Session::User();
	$fullname = $u->getFullname();
	?>
	
	<form id="frmProfile">
		<div class="table-responsive">
			<table class="table table-hover table-striped">
				<tr>
					<th>Your name</th>
					<td><?=$fullname?></td>
				</tr>
				<tr>
					<th>VID</th>
					<td><?=$u->vid?></td>
				</tr>
				<tr>
					<th>Division</th>
					<td><?=$u->getDivisionBadge(); ?></td>
				</tr>
				<tr>
					<th>Ratings</th>
					<td><?=$u->getAtcBadge() . " " . $u->getPilotBadge(); ?></td>
				</tr>
				<tr>
					<th>E-mail address</th>
					<td>
						<div class="input-group">
							<div class="input-group-prepend">
								<div class="input-group-text">@</div>
							</div>
							<input class="form-control" id="txtEmail" placeholder="your email address" value="<?=$u->email?>">
						</div>
						To withdraw providing your email address, simply remove it, and press <strong>Save my details</strong>
					</td>
				</tr>
				<tr>
					<th>Privacy setting</th>
					<td>
						<input type="checkbox" class="form-check-input" id="chkPrivacy" <?=$u->privacy ? "checked" : ""; ?>>
						<label class="form-check-label" for="chkPrivacy">Show my full name to other pilots at my booked flights when they're logged in</label>
					</td>
				</tr>
				<tr>
					<th></th>
					<td>
						<button class="btn btn-success btn-lg" type="submit">Save my details</button>
					</td>
				</tr>
			</table>
		</div>
	</form>

	<div class="bd-callout bd-callout-info">
		<h4 class="mb-3">Important!</h4>

		<h5>Why do we store your email address during the event?</h5>
		<p>Your email address is stored to enable us to contact you with updates, confirmations, and important announcements related to IVAO Real Flight Events (RFE).</p>

		<h5>Who processes your email?</h5>
		<p>
			IVAO (International Virtual Aviation Organisation) and its authorized representatives handle your email securely. 
			We strictly adhere to confidentiality and data protection regulations in the management of your information.
		</p>

		<h5>How do we use your email and personal information?</h5>
		<p>Your email and personal information are used to confirm your participation in the RFE. Storing your email also enables us to provide updates, schedules, operational details, and post-event surveys.</p>

		<h5>Applicable retention policy</h5>
		<p>
			Your email address will be stored only for the duration of the event and a reasonable period afterward for follow-up correspondence. 
			It will then be securely deleted unless required for legal or administrative purposes, or if you have given consent to remain in contact for future communications.
		</p>

		<h5>Your rights</h5>
		<p class="mb-0">You have the right to:</p>
		<ul>
			<li>Request access to all data we hold about you</li>
			<li>Withdraw your consent to receive further communications at any time</li>
		</ul>

		<p>If you believe your personal data rights have been violated, you can file a complaint with our Data Protection Officer at <a href="mailto:dpo@ivao.aero">dpo@ivao.aero</a>.</p>

		<p>For more details, please refer to our <a href="https://wiki.ivao.aero/en/home/ivao/privacypolicy" target="_blank">Privacy Policy</a>.</p>
	</div>
</main>
