<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Intellectual Property Policy (https://doc.ivao.aero/rules2:ipp)
 * @author Donat Marko
 * @copyright 2019 Donat Marko | www.donatus.hu
 */

$time_start = microtime(true); 

$flts = Flight::GetAll();

foreach ($flts as $flt)
{
	echo '<p><b>' . $flt->callsign . '</b> cEET: ' . $flt->getCalculatedEET() . ', aEET = ' . $flt->getActualEET() . '</p>';
}


$time_end = microtime(true);
$execution_time = ($time_end - $time_start);
echo "<p style='margin-top: 5rem'><b>Total Execution Time:</b> " . number_format((float)$execution_time, 10) . " seconds</p>";

?>