<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Creative Intellectual Property Policy (https://wiki.ivao.aero/en/home/ivao/intellectual-property-policy)
 * @author Donat Marko
 * @copyright 2024 Donat Marko | www.donatus.hu
 */

global $config;

function slotsTable($airport)
{
	global $config;
	$result = '<div class="table-responsive">
		<table class="table table-hover table-sm table-striped">
			<thead>
				<tr>
					<th class="text-center">Type</th>
					<th class="text-center">Time</th>
					<th>Booking</th>
				</tr>
			</thead>
			<tbody>';

	$timeframes = $airport->getTimeframes();
	if (empty($timeframes))
	{
		$result .= '<tr><td colspan="2" style="font-style: italic; text-align: center">(no available timeframes)</td></tr>';
	}
	else
	{
		foreach ($timeframes as $tf)
		{
			$result .= '<tr>';
			$result .= '	<td class="text-center">' . $tf->getType() . '</td>';
			$result .= '	<td class="text-center">' . getHumanDateTime($tf->time, $config["time_only_in_list"]) . '</td>';

			$stats = $tf->getStatistics();
			if ($stats["free"] > 0)
				$result .= '	<td><button class="btn btn-sm btn-success btn-block" onclick="getTimeframe(' . $tf->id . ')"><i class="fas fa-gavel"></i> Request now!</button></td>';	
			else
				$result .= '	<td><button class="btn btn-sm btn-danger btn-block" onclick="getTimeframe(' . $tf->id . ')"><i class="fas fa-times"></i> Not available</button></td>';	
			$result .= '</tr>';
		}
	}

	$result .= '</tbody>
		</table>
	</div>';
	return $result;
}

// getting all airports which are participating in the event
$apts = EventAirport::GetAll();

echo '<main role="main" class="container">';
echo '<h1>Request a private slot!</h1>';

if (count($apts) > 0)
{
	echo '<div class="row">';

	echo '<div class="col-lg-4">';
	foreach ($apts as $apt)
	{
		echo '<div class="airport" id="' . $apt->icao . '">';
		if ($airport = $apt->getAirport())
			echo '<h2>' . $airport->getCountryFlag(32) . '<span data-toggle="tooltip" title="' . $apt->name . '">' . $apt->icao . '</span></h2>';
		else
			echo '<h2><span data-toggle="tooltip" title="' . $apt->name . '">' . $apt->icao . '</span></h2>';

		echo slotsTable($apt);
		
		echo '</div>';
	}
	echo '</div>';
?>
	<div class="col-lg-8">
		<div class="collapse" id="timeframe">
			<div class="card" style="margin-bottom: 2rem">
				<h5 class="card-header"><span id="timeframeTitle"></span>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="closeTimeframe()">
						<span aria-hidden="true">&times;</span>
					</button>
				</h5>
				<div class="card-body">
					<div class="alert alert-success" id="timeframeStatus"></div>
					<div class="table-responsive">
						<table class="table table-hover table-sm table-striped" id="tblSlots">
							<thead>
								<tr>
									<th>Callsign</th>
									<th>Aircraft</th>
									<th>Origin</th>
									<th>Destination</th>
									<th>Status</th>
								</tr>
							</thead>
							<tbody>
							</tbody>						
						</table>
					</div>

<?php if (Session::LoggedIn()) : ?>
					<div class="card card-body" id="slotRequest">
						<h5>Request a slot</h5>
						<form id="frmSlotRequest">
							<input type="hidden" id="slotIcao">
							<input type="hidden" id="timeframeId">
							<div class="form-group row">
								<label class="col-sm-2 col-form-label">Callsign:</label>
								<div class="col-sm-10">
									<input class="form-control input-uppercase" id="txtSrCallsign" type="text" placeholder="ICAO callsign, e.g. HUN25K" required maxlength="10">											
								</div>
							</div>
							<div class="form-group row">
								<label class="col-sm-2 col-form-label">Airports:</label>
								<div class="col-sm-10">
									<div class="form-row">									
										<div class="col">
											<input class="form-control input-uppercase" id="txtSrOriginIcao" type="text" placeholder="ICAO of origin" required maxlength="4">
										</div>
										<div class="col">
											<input class="form-control input-uppercase" id="txtSrDestinationIcao" type="text" placeholder="ICAO of destination" required maxlength="4">
										</div>										
									</div>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-sm-2 col-form-label">Aircraft:</label>
								<div class="col-sm-10">
									<div class="form-row">									
										<div class="col">
											<input class="form-control input-uppercase" id="txtSrAircraftIcao" type="text" placeholder="ICAO identifier, e.g. B77W" required maxlength="4">
										</div>
										<div class="col">
											<div class="form-check" style="margin-top: 0.4rem">
												<input class="form-check-input" type="checkbox" id="chkSrFreighter">
												<label class="form-check-label" for="chkSrFreighter">cargo aircraft</label>
											</div>
										</div>										
									</div>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-sm-2 col-form-label">Route:</label>
								<div class="col-sm-10">
									<input class="form-control input-uppercase" type="text" id="txtSrRoute" required placeholder="e.g. GILEP ZOLKU SUNIS DETSA BAKOR M984 EVRIP">
								</div>
							</div>
							
							<button class="btn btn-info" type="submit">Send slot request</button>
						</form>
					</div>
<?php else : ?>
						You must be <a href="login"><strong>logged on</strong></a> to request a private slot!
<?php endif; ?>
				</div>
			</div>
		</div>
		
		<div class="text-justify">
			<h3>Instructions for private slot bookings</h3>

			<p>If you want to participate in our event but haven't found a suitable flight, you have the opportunity to request a private slot.</p>
			<p>A private slot ensures that you can fly your specific flight at the specified timeframe, either as an arrival or departure from/to the respective airport.</p>
			<p>Please note that without a booked private slot, the ATCs can choose <strong>not to accept</strong> handling your flight.</p>

			<p>The available slots are scheduled at times when scheduled traffic is less intense, so we will have sufficient capacity to handle such custom flights.</p>

			<p>You need a private slot for the following types of flight:</p>
			<ul>
				<li>Flights with business jets</li>
				<li>Custom flights or flights connected to virtual airlines</li>
			</ul>

			<div class="bd-callout bd-callout-danger">
				<h4>Do not request private slot for published flights</h4>
				<p>Private slots are <strong>only</strong> for the non-advertised flights mentioned above</p>
			</div>

			<p>Requesting a slot does not guarantee immediate approval. Our Events Department will review and decide on your request. If you have set your email address <a href="profile">on your profile,</a> you will receive an email about the result.</p>

			<p>To request a slot, click the button next to the desired timeframe. Please note that we may advertise more than one slot possibility for one timeframe. A <span class="badge badge-danger">red</span> button indicates that the slot is full, and no further requests can be sent.</p>

			<p>If you have any questions or issues, please contact the <?=$config["division_name"]; ?> Events Department through <a href="contact"><strong>our contact form</strong></a>.</p>
		</div>
	</div>
<?php
}
else
	echo '<div class="alert alert-info">Currently, no airports are participating in the event. Please check back regularly for updates.</div>';

echo '</main>';

include_once("inc/modal_slot.php");

?>