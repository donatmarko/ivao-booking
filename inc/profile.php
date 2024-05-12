<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Creative Intellectual Property Policy (https://wiki.ivao.aero/en/home/ivao/intellectual-property-policy)
 * @author Donat Marko
 * @copyright 2024 Donat Marko | www.donatus.hu
 */
?>

<main role="main" class="container">
	<h1>Your profile stored by us</h1>
	
	<?php
	$u = Session::User();
	$fullname = $u->firstname . " " . $u->lastname;
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
</main>
