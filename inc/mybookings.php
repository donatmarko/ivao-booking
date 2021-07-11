<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Intellectual Property Policy (https://doc.ivao.aero/rules2:ipp)
 * @author Donat Marko
 * @copyright 2021 Donat Marko | www.donatus.hu
 */

$flights = Session::User()->getBookedFlights();
$slots = Session::User()->getSlots();

function getFlights($flights)
{
	$result = "";
	foreach ($flights as $f)
	{
		$airline = $f->getAirline();
		$result .= '<a href="javascript:void(0)" class="list-group-item list-group-item-action" onclick="getFlight(' . $f->id . ')">';
		$result .= ($airline ? $airline->getLogo() : "") . '<strong>' . $f->callsign . '</strong> ' . $f->originIcao . ' – ' . $f->destinationIcao;
		$result .= '<span class="float-right">';

		if ($f->booked == "prebooked")
			$result .= '<span class="badge badge-warning">Prebooked</span>';
		elseif ($f->booked == "booked")
			$result .= '<span class="badge badge-danger">Booked</span>';
			
		$result .= '</span></a>';
	}
	
	if (empty($flights))
		$result = '<div class="alert alert-info">You have no booked flights yet. Book one now!</div>';
	
	return $result;
}

function getSlots($flights)
{
	$result = "";
	foreach ($flights as $f)
	{
		$airline = $f->getAirline();
		$result .= '<a href="javascript:void(0)" class="list-group-item list-group-item-action" onclick="getSlot(' . $f->id . ', true)">';
		$result .= ($airline ? $airline->getLogo() : "") . '<strong>' . $f->callsign . '</strong> ' . $f->originIcao . ' – ' . $f->destinationIcao;
		$result .= '<span class="float-right">';

		if ($f->booked == "requested")
			$result .= '<span class="badge badge-warning">Requested</span>';
		elseif ($f->booked == "granted")
			$result .= '<span class="badge badge-danger">Granted</span>';
			
		$result .= '</span></a>';
	}
	
	if (empty($flights))
		$result = '<div class="alert alert-info">You have no private slots yet. Request one now!</div>';
	
	return $result;
}

?>

<main role="main" class="container">
	<h1>My booked flights</h1>
	
	<div class="card mb-4">
		<h5 class="card-header">Scheduled flights (<?=count($flights)?>)</h5>
		<div class="card-body">
			<div class="list-group">
				<?=getFlights($flights)?>
			</div>
		</div>
	</div>

<?php if (count(Timeframe::GetAll()) > 0) : ?>
	<div class="card mb-4">
		<h5 class="card-header">Private slots (<?=count($slots)?>)</h5>
		<div class="card-body">		
			<div class="list-group">
				<?=getSlots($slots)?>
			</div>
		</div>
	</div>
<?php endif; ?>

</main>

<?php
require 'modal_flight.php';
require 'modal_slot.php';
Pages::AddJS("flights");
Pages::AddJS("slots");
?>