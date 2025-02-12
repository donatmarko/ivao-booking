<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Creative Intellectual Property Policy (https://wiki.ivao.aero/en/home/ivao/intellectual-property-policy)
 * @author Donat Marko
 * @copyright 2025 Donat Marko | www.donatus.hu
 */

/**
 * Returns a progress bar according to the given parametres
 * @param int $booked
 * @param int $free
 * @return string HTML
 */
function progressBar($booked, $free)
{
	$all = $booked + $free;
	if ($all < 1)
	{
		return '
		<div class="progress" style="height: 1.5rem">
		</div>';
	}

	$percentBooked = $booked / $all * 100; 
	$percentFree = 100 - $percentBooked;

	return '
		<div class="progress" style="height: 1.5rem">
			<div class="progress-bar progress-bar-striped progress-bar-animated bg-danger" role="progressbar" style="width: ' . $percentBooked . '%" aria-valuenow="' . $percentBooked . '" aria-valuemin="0" aria-valuemax="100">' . round($percentBooked, 2) . '%</div>
			<div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: ' . $percentFree . '%" aria-valuenow="' . $percentFree . '" aria-valuemin="0" aria-valuemax="100">' . round($percentFree, 2) . '%</div>
		</div>
		<div class="row">				
			<div class="col-sm-4 h5 text-center">
				Reserved: <span class="badge badge-danger">' . $booked . '</span>
			</div>
			<div class="col-sm-4 h5 text-center">
				Total: <span class="badge badge-primary">' . $all . '</span>
			</div>
			<div class="col-sm-4 h5 text-center">
				Available: <span class="badge badge-success">' . $free . '</span>
			</div>
		</div>';
}

/**
 * Returns a box of statistics about the given airport
 * if airport = null, it is the all-long statistics
 * @param Airport $airport
 * @return string HTML
 */
function statisticLine($airport)
{
	$result = '<div class="airport">';
	if (!$airport)
	{
		$result .= '<h2>Total Statistics</h2>';
		$fs = EventAirport::getStatisticsAll();

		if (!empty(Timeframe::GetAll()))
		{
			$result .= '<h5>Flights</h5>';
			$result .= progressBar($fs["prebooked"] + $fs["booked"], $fs["free"]);

			$result .= '<h5>Private slots</h5>';
			$ss = EventAirport::getSlotStatisticsAll();
			$result .= progressBar($ss["granted"], $ss["requested"] + $ss["free"]);
		}
		else
		{
			$result .= progressBar($fs["prebooked"] + $fs["booked"], $fs["free"]);
		}
	}
	else
	{
		$result .= '<h2>';
		$result .= $airport->getAirport()?->getCountryFlag();
		$result .= ' <a href="flights#' . $airport->icao . '">' . $airport->icao . '</a> <small>' . $airport->name . '</small></h2>';
		$fs = $airport->getStatistics();

		if ($airport?->getTimeframes())
		{
			$result .= '<h5>Flights</h5>';
			$result .= progressBar($fs["prebooked"] + $fs["booked"], $fs["free"]);

			$result .= '<h5>Private slots</h5>';
			$ss = $airport->getSlotStatistics();
			$result .= progressBar($ss["granted"], $ss["requested"] + $ss["free"]);
		}
		else
		{
			$result .= progressBar($fs["prebooked"] + $fs["booked"], $fs["free"]);
		}
	}
	$result .= '</div>';
	return $result;
}

?>

<main role="main" class="container">
	<h1>Statistics</h1>

	<?php
	echo statisticLine(null);

	$apts = EventAirport::GetAll();
	if (count($apts) > 1)
	{
		foreach (EventAirport::GetAll() as $airport)
			echo statisticLine($airport);
	}
	?>	
</main>
