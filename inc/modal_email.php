<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Intellectual Property Policy (https://doc.ivao.aero/rules2:ipp)
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
					<p>However it's not compulsory, we strongly recommend to enter it. Without it you can only "pre-book", and the Events staff might decide to delete the unconfirmed bookings if the interest become high on the flights.</p>
					<p>We won't send spam, only the confirmation mails per flight, and if there are, the additional infos (e.g. flight briefing). You won't be opted-in to newsletters either.</p>					
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