<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Creative Intellectual Property Policy (https://wiki.ivao.aero/en/home/ivao/intellectual-property-policy)
 * @author Donat Marko
 * @copyright 2025 Donat Marko | www.donatus.hu
 */

/**
 * Generates one table (either departures or arrivals) for the specified airport
 * @param Airport $airport 
 * @param string $type ["arrivals", "departures"]
 */
function flightsTable($airport, $type)
{
	global $config;

	if ($type == "departures")		
	{
		$flights = $airport->getDepartures();
		$result = '<div class="tab-pane fade show active table-responsive" id="departures' . $airport->icao . '" role="tabpanel">';
	}
	if ($type == "arrivals")
	{
		$flights = $airport->getArrivals();
		$result = '<div class="tab-pane fade table-responsive" id="arrivals' . $airport->icao . '" role="tabpanel">';
	}	

	$result .= '<table class="table table-hover table-striped tblFlights" id="tblFlight_' . $airport->icao . '_' . $type . '">
				<thead>
				<tr>
					<th>Airline</th>
					<th>Flight</th>
					<th>Aircraft</th>';
					
	if ($type == "arrivals")
	{
		$result .= '<th>Origin</th>';
		$result .= '<th>On-block</th>';
	}
	elseif ($type == "departures")
	{
		$result .= '<th>Destination</th>';
		$result .= '<th>Off-block</th>';
	}
	
	$result .= "	<th>Parking</th>
					<th>Status</th>
				</tr>
			</thead>
		<tbody>";
	
	$user = Session::User();
	foreach ($flights as $f)
	{
		if (str_starts_with($f->callsign, "HUN") && $user?->division != "HU" && $user?->permission < 2)
			continue;

		$result .= '<tr>';		

		if ($airline = $f->getAirline())
			$result .= '<td data-search="' . $airline->name . '" data-order="' . $airline->name . '">' . $airline->getLogo() . " ";
		else
			$result .= '<td data-search="' . $f->flightNumber . '" data-order="' . $f->flightNumber . '">';		
		$result .= '</td>';

		$result .= '<td>';
		if (empty($f->flightNumber) || $f->flightNumber == $f->callsign)
			$result .= "<strong>" . $f->callsign . "</strong>";
		else if (empty($f->callsign))
			$result .= $f->flightNumber;
		else
			$result .= "<strong>" . $f->callsign . '</strong><br><small class="text-muted">' . $f->flightNumber . '</small>';
			// $result .= $f->flightNumber . ' / ' . $f->callsign;

		$result .= $f->getTurnoverFlights() ? '<span data-toggle="tooltip" title="Turnover flight available" class="ml-2"><i class="fas fa-sync fa-spin"></i></span> ' : '';
		$result .= $f->briefing !== null ? '<span data-toggle="tooltip" title="Check Flight Briefing section" class="ml-1"><i class="fas fa-info-circle"></i></span> ' : '';
		$result .= '</td>';

		$result .= '<td><span data-toggle="tooltip" title="' . $f->getAircraftName() . '">' . $f->aircraftIcao . ($f->aircraftFreighter ? '<br><small class="text-muted">Cargo</small>' : '') . '</span></td>';
		
		if ($type == "arrivals")
		{
			if ($origin = $f->getOrigin())
				$result .= '<td><strong>' . $origin->getCountryFlag(24) . $origin->icao . '</strong><br><small class="text-muted">' . $origin->name . '</small></td>';
			else
				$result .= '<td><strong>' . $f->originIcao . '</strong></td>';
			$result .= '<td data-order="' . $f->arrivalTime . '">' . getHumanDateTime($f->arrivalTime, $config["time_only_in_list"]) . '</td>';						
		}
		elseif ($type == "departures")
		{
			if ($dest = $f->getDestination())
				$result .= '<td><strong>' . $dest->getCountryFlag(24) . $dest->icao . '</strong><br><small class="text-muted">' . $dest->name . '</small></td>';
			else
				$result .= '<td><strong>' . $f->destinationIcao . '</strong></td>';

			$result .= '<td data-order="' . $f->departureTime . '">' . getHumanDateTime($f->departureTime, $config["time_only_in_list"]) . '</td>';						
		}
		
		$result .= '<td>' . $f->getPosition(true, true) . '</td>';

		if ($f->booked == "free")
			$result .= '<td data-order="0" data-search="free"><button class="btn btn-success btn-sm btn-block" onclick="getFlight(' . $f->id . ')"><i class="fas fa-thumbs-up"></i> Available</button></td>';
		
		if ($f->booked == "prebooked")
			// $result .= '<td data-order="1" data-search="prebooked"><button class="btn btn-warning btn-sm btn-block" onclick="getFlight(' . $f->id . ')"><i class="fas fa-lock"></i> Reserved by <strong>' . $f->bookedBy . '</strong></button></td>';
			$result .= '<td data-order="1" data-search="prebooked"><button class="btn btn-warning btn-sm btn-block" onclick="getFlight(' . $f->id . ')"><i class="fas fa-lock"></i> Reserved</strong></button></td>';
		
		if ($f->booked == "booked")
			// $result .= '<td data-order="2" data-search="booked"><button class="btn btn-danger btn-sm btn-block" onclick="getFlight(' . $f->id . ')"><i class="fas fa-lock"></i> Booked by <strong>' . $f->bookedBy . '</strong></button></td>';
			$result .= '<td data-order="2" data-search="booked"><button class="btn btn-danger btn-sm btn-block" onclick="getFlight(' . $f->id . ')"><i class="fas fa-lock"></i> Booked</strong></button></td>';

		$result .= '</tr>';
	}
	
	$result .= "</tbody></table></div>";
	
	return $result;
}

// getting all airports which are participating in the event
$apts = EventAirport::GetAll();

echo '<main role="main" class="container">
		<div class="row">
			<div class="col-lg-8">';

// if only one airport participates, including its name to the header, otherwise it will be included in each box
if (count($apts) == 1)
{
	if ($airport = $apts[0]->getAirport())
		echo '<h1>Book your flight! <span class="text-muted">' . $airport->getCountryFlag(48) . $apts[0]->icao . ' <small>' . $apts[0]->name . '</small></span></h1>';
	else
		echo '<h1>Book your flight! <span class="text-muted">' . $apts[0]->icao . ' <small>' . $apts[0]->name . '</small></span></h1>';
}
else
	echo '<h1>Book your flight!</h1>';

echo '</div>
	<div class="col-lg-4 mb-4 pt-lg-3">
		<div class="custom-control custom-checkbox float-lg-right">
			<input type="checkbox" class="custom-control-input" id="fltOnlyFree">
			<label class="custom-control-label" for="fltOnlyFree">Show available flights only</label>
		</div>
	</div>
</div>';

if (count($apts) > 0)
{
	foreach ($apts as $apt)
	{
		if (count($apts) > 1)
		{
			echo '<div class="airport" id="' . $apt->icao . '">';
			if ($airport = $apt->getAirport())
				echo '<h2>' . $airport->getCountryFlag(32) . $apt->icao . ' <small>' . $apt->name . '</small></h2>';
			else
				echo '<h2>' . $apt->icao . ' <small>' . $apt->name . '</small></h2>';
		}
		
		echo '
			<ul class="nav nav-tabs" id="arrDepTab" role="tablist">
				<li class="nav-item">
					<a class="nav-link active text-success tabFlightlist" data-toggle="tab" href="#departures' . $apt->icao . '" role="tab" aria-selected="true"><i class="fas fa-plane-departure"></i> Departures</a>
				</li>
				<li class="nav-item">
					<a class="nav-link text-danger tabFlightlist" data-toggle="tab" href="#arrivals' . $apt->icao . '" role="tab" aria-selected="false"><i class="fas fa-plane-arrival"></i> Arrivals</a>
				</li>';

		if (count($apt->getTimeframes()) > 0)
		{
			echo '
				<li class="nav-item">
					<a class="nav-link text-info" href="slots#' . $apt->icao . '" role="tab" aria-selected="true"><i class="fas fa-key"></i> Private slots</a>
				</li>';
		}
			
		echo '</ul>
			<div class="tab-content">' . flightsTable($apt, "departures") . flightsTable($apt, "arrivals") . '</div>';
		
		if (count($apts) > 1)
			echo '</div>';
	}
}
else
	echo '<div class="alert alert-info">Currently, no airports are participating in the event. Please check back regularly for updates.</div>';

echo '</main>';

require 'modal_flight.php';
?>
