<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Creative Intellectual Property Policy (https://wiki.ivao.aero/en/home/ivao/intellectual-property-policy)
 * @author Donat Marko
 * @copyright 2025 Donat Marko | www.donatus.hu
 */
?>

<div class="card card-body" style="margin-bottom: 2rem">
	<input type="hidden" id="fltId">
	<div class="form-group row">
		<label class="col-sm-2 col-form-label">Flt ID:</label>
		<div class="col-sm-10">
			<div class="form-row">									
				<div class="col">
					<input class="form-control input-uppercase" id="txtFltCallsign" type="text" placeholder="ICAO callsign, e.g. HUN25K" required maxlength="10">											
				</div>
				<div class="col">
					<input class="form-control input-uppercase" id="txtFltFlightNumber" type="text" placeholder="IATA flight number, e.g. HU1120" maxlength="10">
				</div>										
			</div>
		</div>
	</div>
	<div class="form-group row">
		<label class="col-sm-2 col-form-label">Airports:</label>
		<div class="col-sm-10">
			<div class="form-row">									
				<div class="col">
					<input class="form-control input-uppercase" id="txtFltOriginIcao" type="text" placeholder="ICAO of origin" required maxlength="4">
				</div>
				<div class="col">
					<input class="form-control input-uppercase" id="txtFltDestinationIcao" type="text" placeholder="ICAO of destination" required maxlength="4">
				</div>										
			</div>
		</div>
	</div>
	<div class="form-group row">
		<label class="col-sm-2 col-form-label">Slot times:</label>
		<div class="col-sm-10">
			<div class="form-row">
				<div class="col">
					<div class="input-group date dtp" id="dtpFltDeparture" data-target-input="nearest">
						<input type="text" class="form-control datetimepicker-input" data-target="#dtpFltDeparture" required>
						<div class="input-group-append" data-target="#dtpFltDeparture" data-toggle="datetimepicker">
							<span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
						</div>
					</div>
					<div class="form-check">
						<input class="form-check-input" type="checkbox" id="chkFltDepartureAuto">
						<label class="form-check-label" for="chkFltDepartureAuto">estimating automatically</label>
					</div>
				</div>
				<div class="col">
					<div class="input-group date dtp" id="dtpFltArrival" data-target-input="nearest">
						<input type="text" class="form-control datetimepicker-input" data-target="#dtpFltArrival" required>
						<div class="input-group-append" data-target="#dtpFltArrival" data-toggle="datetimepicker">
							<span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
						</div>
					</div>
					<div class="form-check">
						<input class="form-check-input" type="checkbox" id="chkFltArrivalAuto">
						<label class="form-check-label" for="chkFltArrivalAuto">estimating automatically</label>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="form-group row">
		<label class="col-sm-2 col-form-label">Aircraft:</label>
		<div class="col-sm-10">
			<div class="form-row">									
				<div class="col">
					<input class="form-control input-uppercase" id="txtFltAircraftIcao" type="text" placeholder="ICAO identifier, e.g. B77W" required maxlength="4">
				</div>
				<div class="col">
					<div class="form-check" style="margin-top: 0.4rem">
						<input class="form-check-input" type="checkbox" id="chkFltFreighter">
						<label class="form-check-label" for="chkFltFreighter">cargo aircraft</label>
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
					<input class="form-control" id="txtFltTerminal" type="text" placeholder="Terminal" maxlength="10">
				</div>
				<div class="col">
					<input class="form-control" id="txtFltGate" type="text" placeholder="Gate/stand number" maxlength="10">
				</div>										
			</div>
		</div>
	</div>
	<div class="form-group row">
		<label class="col-sm-2 col-form-label">Route:</label>
		<div class="col-sm-10">
			<input class="form-control input-uppercase" type="text" id="txtFltRoute" placeholder="e.g. GILEP ZOLKU SUNIS DETSA BAKOR M984 EVRIP">
		</div>
	</div>
	<div class="form-group row">
		<label class="col-sm-2 col-form-label">Status:</label>
		<div class="col-sm-10">
			<div class="form-row">									
				<div class="col">
					<select class="form-control" id="selFltStatus">
						<option value="0">free</option>
						<option value="1">reserved</option>
						<option value="2">booked (confirmed)</option>
					</select>
				</div>
				<div class="col">
					<input class="form-control" id="numFltBookedBy" type="number" placeholder="VID of booker" maxlength="6">
				</div>										
			</div>
		</div>
	</div>
	<div><button class="btn btn-info" type="submit">Save flight</button></div>
</div>