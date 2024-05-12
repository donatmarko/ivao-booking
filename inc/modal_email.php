<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Creative Intellectual Property Policy (https://wiki.ivao.aero/en/home/ivao/intellectual-property-policy)
 * @author Donat Marko
 * @copyright 2024 Donat Marko | www.donatus.hu
 */
?>

<div class="modal fade" id="enterEmail" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Enter your email address</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<form id="frmEmail">
				<div class="modal-body">
					<p>While it is not mandatory, we strongly recommend entering it. Without it, you can only "pre-book," and the Events staff might decide to delete unconfirmed bookings if interest in the flights becomes high.</p>
					<p>We will not send spam; only confirmation emails per flight and, if available, additional information (e.g., flight briefing). You will not be automatically opted into newsletters either.</p>
					<p>You can always withdraw providing your email address by editing your profile.</p>
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text">@</div>
						</div>
						<input class="form-control" id="txtEmail" required placeholder="your email address">
					</div>					
				</div>
				<div class="modal-footer">				
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>				
					<button type="submit" class="btn btn-success">Save e-mail</button>
				</div>
			</form>
		</div>
	</div>
</div>