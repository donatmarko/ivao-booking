<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Intellectual Property Policy (https://doc.ivao.aero/rules2:ipp)
 * @author Donat Marko
 * @copyright 2024 Donat Marko | www.donatus.hu
 */
?>

<div class="modal fade" id="slot" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<div class="btn-group btn-group-sm mr-2" role="toolbar" id="slotAdminButtons">
					<button type="button" class="btn btn-secondary" id="btnSlotAdminEdit" data-toggle="collapse" data-target="#slotEdit">Edit</button>
					<button type="button" class="btn btn-danger" id="btnSlotAdminDelete">Delete</button>
				</div>
				<h5 class="modal-title" id="slotTitle"></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="collapse" id="slotEdit">
					<div class="card card-body" style="margin-bottom: 2rem">
						<form id="frmSlotEdit">
							<input type="hidden" id="slotId">
							<div class="form-group row">
								<label class="col-sm-2 col-form-label">Callsign:</label>
								<div class="col-sm-10">
									<input class="form-control input-uppercase" id="txtSlotCallsign" type="text" placeholder="ICAO callsign, e.g. AUA714C" required maxlength="10">											
								</div>
							</div>
							<div class="form-group row">
								<label class="col-sm-2 col-form-label">Airports:</label>
								<div class="col-sm-10">
									<div class="form-row">									
										<div class="col">
											<input class="form-control input-uppercase" id="txtSlotOriginIcao" type="text" placeholder="ICAO of origin" required maxlength="4">
										</div>
										<div class="col">
											<input class="form-control input-uppercase" id="txtSlotDestinationIcao" type="text" placeholder="ICAO of destination" required maxlength="4">
										</div>										
									</div>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-sm-2 col-form-label">Aircraft:</label>
								<div class="col-sm-10">
									<div class="form-row">									
										<div class="col">
											<input class="form-control input-uppercase" id="txtSlotAircraftIcao" type="text" placeholder="ICAO identifier, e.g. B738" required maxlength="4">
										</div>
										<div class="col">
											<div class="form-check" style="margin-top: 0.4rem">
												<input class="form-check-input" type="checkbox" id="chkSlotFreighter">
												<label class="form-check-label" for="chkSlotFreighter">freighter aircraft</label>
											</div>
										</div>										
									</div>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-sm-2 col-form-label">Position:</label>
								<div class="col-sm-10">
									<div class="form-row">									
										<div class="col">
											<input class="form-control input-uppercase" id="txtSlotTerminal" type="text" placeholder="Terminal" maxlength="4">
										</div>
										<div class="col">
											<input class="form-control input-uppercase" id="txtSlotGate" type="text" placeholder="Gate number" maxlength="4">
										</div>										
									</div>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-sm-2 col-form-label">Route:</label>
								<div class="col-sm-10">
									<input class="form-control input-uppercase" type="text" id="txtSlotRoute">
								</div>
							</div>
							<div class="form-group row">
								<label class="col-sm-2 col-form-label">Status / action:</label>
								<div class="col-sm-10">
									<div class="form-row">									
										<div class="col">
											<select class="form-control" id="selSlotStatus">
												<option value="0">slot REJECTED</option>
												<option value="1">requested - PENDING</option>
												<option value="2">slot granted - ACCEPTED</option>
											</select>
										</div>										
										<div class="col">
											<input class="form-control" id="numSlotBookedBy" type="number" placeholder="VID of requestor" maxlength="6">
										</div>										
									</div>
								</div>
							</div>
							<div class="form-group row" id="slotRejectMessage">
								<label class="col-sm-2 col-form-label">Rejection reason:</label>
								<div class="col-sm-10">
									<input class="form-control" type="text" id="txtSlotRejectMessage">
								</div>
							</div>
							<div class="form-group row">
								<label class="col-sm-2 col-form-label">Timeframe:</label>
								<div class="col-sm-10">
									<select class="form-control" id="selSlotTimeframe"></select>
								</div>
							</div>
							
							<button class="btn btn-info" type="submit">Save slot request</button>
						</form>
					</div>
				</div>			
				<div class="details">
					<div class="alert alert-primary" id="slotInfobox"></div>
					<div class="flighttiles">
						<div class="row">
							<div class="col-lg-4">
								<div class="head">Callsign</div>
								<div id="slotRadioCallsign" class="big text-center"></div>
								<div id="slotRadioCallsignHuman" class="foot text-center"></div>
							</div>
							<div class="col-lg-4">
								<div class="head">Origin</div>
								<div id="slotOriginIcao" class="big text-center"></div>
								<div id="slotOriginHuman" class="foot text-center"></div>
							</div>
							<div class="col-lg-4">
								<div class="head">Destination</div>
								<div id="slotDestinationIcao" class="big text-center"></div>
								<div id="slotDestinationHuman" class="foot text-center"></div>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-4">
								<div class="head">Aircraft</div>
								<div id="slotAircraftIcao" class="big text-center"></div>
								<div id="slotAircraftHuman" class="foot text-center"></div>
							</div>
							<div class="col-lg-4">
								<div class="head">Departure time</div>
								<div id="slotDepartureTimeHuman" class="big text-center"></div>
								<div id="slotDepartureTimeAuto" class="foot text-center"><span class="badge badge-danger">Automatically calculated</span></div>
							</div>
							<div class="col-lg-4">
								<div class="head">Arrival time</div>
								<div id="slotArrivalTimeHuman" class="big text-center"></div>
								<div id="slotArrivalTimeAuto" class="foot text-center"><span class="badge badge-danger">Automatically calculated</span></div>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-4">
								<div class="head">Position</div>
								<div id="slotPosition" class="big text-center"></div>
							</div>
							<div class="col-lg-8">
								<div class="head">Route <span id="slotGcd"></span></div>
								<div id="slotRoute" style="font-family: Consolas, 'Ubuntu Mono', 'Courier New', courier"></div>
							</div>
						</div>
					</div>

					<div class="flightCollapses" id="slotCollapses">
						<button class="btn btn-block btn-light collapsed" data-target="#slotMap" data-toggle="collapse">Show route on map</button>
						<div class="collapse card card-body" id="slotMap" data-parent="#slotCollapses"><div class="map" id="uiSlotMap"></div></div>

						<button id="btnSlotBriefing" class="btn btn-block btn-light collapsed" data-target="#slotBriefing" data-toggle="collapse">Flight briefing (weather, flight planning)</button>
						<div class="collapse card card-body" id="slotBriefing" data-parent="#slotCollapses">
<?php global $config; if (!empty($config["wx_url"])) : ?>
							<p>
								<button class="btn btn-info btn-sm" id="slotMetarOrigin"></button>
								<button class="btn btn-info btn-sm" id="slotTafOrigin"></button>
								<button class="btn btn-info btn-sm" id="slotMetarDestination"></button>
								<button class="btn btn-info btn-sm" id="slotTafDestination"></button>
							</p>
							<div id="slotWxResult" class="card card-body wxResult"></div>
<?php endif; ?>
							<p>
								<a href="https://www.simbrief.com/system/dispatch.php?newflight=1" target="_blank" class="btn btn-secondary btn-sm">SimBrief</a>
								<a href="http://rfinder.asalink.net/free" target="_blank" class="btn btn-secondary btn-sm">RouteFinder</a>
								<a href="https://www.ivao.aero/db/route/default.asp" id="lnkSlotIvaoRte" target="_blank" class="btn btn-secondary btn-sm">IVAO Route Database</a>
							</p>
						</div>
					</div>

					<div class="flighttiles" id="slotBookedBy">
						<div class="row">
							<div class="col-lg-6">
								<div class="head" id="lblBookedByName"></div>
								<div id="slotBookedByName" class="big"></div>
							</div>
							<div class="col-lg-3">
								<div class="head">VID</div>
								<div id="slotBookedByVid" class="big"></div>
							</div>
							<div class="col-lg-3">
								<div class="head">Division</div>
								<div id="slotBookedByDivision" class="pt-1"></div>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-6">
								<div class="head">Pilot rating</div>
								<div id="slotBookedByRating" class="pt-2"></div>
							</div>
							<div class="col-lg-6">
								<div class="head">Slot has been requested at</div>
								<div id="slotBookedAt" class="big"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<div id="slotBtnsLoggedOut" style="display: none">
					<a class="btn btn-primary" href="login">Click here to log in</a>
				</div>
				<div id="slotBtnsDefault" style="display: none">
					<button type="button" class="btn btn-danger" id="btnSlotDelete">Delete slot</button>
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
</div>