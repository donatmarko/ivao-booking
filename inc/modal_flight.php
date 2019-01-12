<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Intellectual Property Policy (https://doc.ivao.aero/rules2:ipp)
 * @author Donat Marko
 * @copyright 2019 Donat Marko | www.donatus.hu
 */

global $config;
?>

<div class="modal fade" id="flight" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<div class="btn-group btn-group-sm mr-2" role="toolbar" id="fltBtnsAdmin">
					<button type="button" class="btn btn-secondary" id="btnFltAdminEdit" data-toggle="collapse" data-target="#fltEdit">Edit</button>
					<button type="button" class="btn btn-danger" id="btnFltAdminDelete">Delete</button>
				</div>
				<h5 class="modal-title" id="fltTitle"></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="collapse" id="fltEdit">
					<form id="frmFlightEdit">
						<?php include_once("inc/admin_flightedit.php"); ?>
					</form>
				</div>
				<div class="details">
					<div class="alert alert-primary" id="fltInfobox"></div>
					<div class="flighttiles">
						<div class="row">
							<div class="col-lg-4">
								<div class="head">Flight</div>
								<div id="fltRadioCallsign" class="big text-center"></div>
								<div id="fltRadioCallsignHuman" class="foot text-center"></div>
							</div>
							<div class="col-lg-4">
								<div class="head">Origin</div>
								<div id="fltOriginIcao" class="big text-center"></div>
								<div id="fltOriginHuman" class="foot text-center"></div>
							</div>
							<div class="col-lg-4">
								<div class="head">Destination</div>
								<div id="fltDestinationIcao" class="big text-center"></div>
								<div id="fltDestinationHuman" class="foot text-center"></div>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-4">
								<div class="head">Aircraft</div>
								<div id="fltAircraftIcao" class="big text-center"></div>
								<div id="fltAircraftHuman" class="foot text-center"></div>
							</div>
							<div class="col-lg-4">
								<div class="head">Departure time</div>
								<div id="fltDepartureTimeHuman" class="big text-center"></div>
								<div id="fltDepartureTimeAuto" class="foot text-center"><span class="badge badge-danger">Automatically calculated</span></div>
							</div>
							<div class="col-lg-4">
								<div class="head">Arrival time</div>
								<div id="fltArrivalTimeHuman" class="big text-center"></div>
								<div id="fltArrivalTimeAuto" class="foot text-center"><span class="badge badge-danger">Automatically calculated</span></div>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-4">
								<div class="head">Position</div>
								<div id="fltPosition" class="big text-center"></div>
							</div>
							<div class="col-lg-8">
								<div class="head">Route <span id="fltGcd"></span></div>
								<div id="fltRoute" style="font-family: Consolas, 'Ubuntu Mono', 'Courier New', courier"></div>
							</div>
						</div>
					</div>

					<div class="flightCollapses" id="flightCollapses">
						<button class="btn btn-block btn-light collapsed" data-target="#fltMap" data-toggle="collapse">Show route on map</button>
						<div class="collapse card card-body" id="fltMap" data-parent="#flightCollapses"><div class="map" id="uiFltMap"></div></div>

						<button class="btn btn-block btn-light collapsed" data-target="#fltTurnovers" data-toggle="collapse" id="btnFltTurnovers">Turnover flight(s)</button>
						<div class="collapse card card-body" id="fltTurnovers" data-parent="#flightCollapses"></div>

						<button id="btnFltBriefing" class="btn btn-block btn-light collapsed" data-target="#fltBriefing" data-toggle="collapse">Flight briefing (weather, flight planning)</button>
						<div class="collapse card card-body" id="fltBriefing" data-parent="#flightCollapses">
<?php if (!empty($config["wx_url"])) : ?>
							<p>
								<button class="btn btn-info btn-sm" id="fltMetarOrigin"></button>
								<button class="btn btn-info btn-sm" id="fltTafOrigin"></button>
								<button class="btn btn-info btn-sm" id="fltMetarDestination"></button>
								<button class="btn btn-info btn-sm" id="fltTafDestination"></button>
							</p>
							<div id="fltWxResult" class="card card-body wxResult"></div>
<?php endif; ?>
							<p>
								<a href="https://www.simbrief.com/system/dispatch.php?newflight=1" target="_blank" class="btn btn-secondary btn-sm">SimBrief</a>
								<a href="http://rfinder.asalink.net/free" target="_blank" class="btn btn-secondary btn-sm">RouteFinder</a>
								<a href="https://www.ivao.aero/db/route/default.asp" id="lnkFltIvaoRte" target="_blank" class="btn btn-secondary btn-sm">IVAO Route Database</a>
							</p>
						</div>
					</div>

					<div class="flighttiles" id="fltBookedBy">
						<div class="row">
							<div class="col-lg-6">
								<div class="head">Flight already booked by</div>
								<div id="fltBookedByName" class="big"></div>
							</div>
							<div class="col-lg-3">
								<div class="head">VID</div>
								<div id="fltBookedByVid" class="big"></div>
							</div>
							<div class="col-lg-3">
								<div class="head">Division</div>
								<div id="fltBookedByDivision" class="pt-1"></div>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-6">
								<div class="head">Pilot rating</div>
								<div id="fltBookedByRating" class="pt-2"></div>
							</div>
							<div class="col-lg-6">
								<div class="head">Flight has been booked at</div>
								<div id="fltBookedAt" class="big"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<div id="fltBtnsLoggedOut" style="display: none">
					<a class="btn btn-primary" href="login">Click here to log in</a>
				</div>
				<div id="fltBtnsDefault" style="display: none">
					<button type="button" class="btn btn-success" id="btnFltBook">Book this flight now!</button>
					<button type="button" class="btn btn-danger" id="btnFltFree">Delete booking</button>
					<button type="button" class="btn btn-primary" id="btnFltSendEmail">Re-send confirmation mail</button>
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				</div>
				<div id="fltBtnsConfirm" style="display: none">
					<button type="button" class="btn btn-primary" id="btnFltConfirm">I agree, book the flight</button>
					<button type="button" class="btn btn-secondary" data-dismiss="modal">I don't agree</button>	
				</div>
			</div>
		</div>
	</div>
</div>

<?php
Pages::AddJS("admin_flightedit");
?>