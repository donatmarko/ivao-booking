<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Creative Intellectual Property Policy (https://wiki.ivao.aero/en/home/ivao/intellectual-property-policy)
 * @author Donat Marko
 * @copyright 2025 Donat Marko | www.donatus.hu
 */

/**
 * Common functions used by the booking system
 * @author Donat Marko | www.donatus.hu
 */

/**
 * Converting DATETIME to human readable format
 * @param string $dt SQL DATETIME string
 * @return string e.g. "Sept 21, 17:00z"
 */
function getHumanDateTime($dt, $time_only = false)
{
	if ($time_only)
		return date("H:i", strtotime($dt)) . "Z";
	
	return date("d M H:i", strtotime($dt)) . "Z";
}

/**
 * Function redirects to a specified URL according to the following:
 * 		if headers has already been sent, uses JavaScript,
 * 		otherwise set the headers to the specified location
 * @param string $url
 */
function redirect($url)
{	
	if (headers_sent())
	{
		die(sprintf("<script>window.location.href='%s';</script>", $url));
	}
	else
	{
		header("Location: $url");
		die();
	}
}

/**
 * Calculates great circle distance between two given coordinates with Haversine-formula
 * @param float $latitudeFrom Latitude of start point in [deg decimal]
 * @param float $longitudeFrom Longitude of start point in [deg decimal]
 * @param float $latitudeTo Latitude of target point in [deg decimal]
 * @param float $longitudeTo Longitude of target point in [deg decimal]
 * @param float $earthRadius - 6371000 m, 6371 km, 3440 nm, 3959 miles (sm)
 * @return float Distance between the two points
 */
function haversineGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
{
	$latFrom = deg2rad($latitudeFrom);
	$lonFrom = deg2rad($longitudeFrom);
	$latTo = deg2rad($latitudeTo);
	$lonTo = deg2rad($longitudeTo);
  
	$latDelta = $latTo - $latFrom;
	$lonDelta = $lonTo - $lonFrom;
  
	$angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
	return $angle * $earthRadius;
}

function calculate_eet($dist, $tas, $climb_descent_factor = 0.55, $taxi_out_min = 10, $climb_min = 30, $descent_min = 30, $taxi_in_min = 5)
{
	$taxi_out_hrs = $taxi_out_min / 60;
	$climb_hrs = $climb_min / 60;
	$descent_hrs = $descent_min / 60;
	$taxi_in_hrs = $taxi_in_min / 60;

	$climb_dist = $tas * $climb_descent_factor * $climb_hrs;
	$descent_dist = $tas * $climb_descent_factor * $descent_hrs;

	$cruise_dist = $dist - $climb_dist - $descent_dist;
	if ($cruise_dist < 0) {
		$cruise_hrs = $dist / (0.4 * $tas);
		return round(($taxi_out_hrs + $cruise_hrs + $taxi_in_hrs) * 3600);
	}

	$cruise_hrs = $cruise_dist / (0.89 * $tas);
	return round(($taxi_out_hrs + $climb_hrs + $cruise_hrs + $descent_hrs + $taxi_in_hrs) * 3600);
}

/**
 * Calling API with curl.
 * @param string method POST, PUT, GET
 * @param string url
 * @param mixed data
 * @return mixed
 */
function callApi($method, $url, $data = false)
{
	$curl = curl_init();

	switch ($method)
	{
		case "POST":
			curl_setopt($curl, CURLOPT_POST, 1);
			if ($data)
				curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
			break;
		case "PUT":
			curl_setopt($curl, CURLOPT_PUT, 1);
			break;
		default:
			if ($data)
				$url = sprintf("%s?%s", $url, http_build_query($data));
	}

	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_USERAGENT, 'curl/' . curl_version()["version"] . '/donatmarko/ivao-booking');
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	$result = curl_exec($curl);
	curl_close($curl);

	return $result;
}

/**
 * Checks whether array is associative or sequential.
 * @param array array
 * @return bool 
 */
function isArrayAssociative(array $arr)
{
	if (array() === $arr)
		return false;
	ksort($arr);
	return array_keys($arr) !== range(0, count($arr) - 1);
}

function str_replace_first($from, $to, $haystack): string
{
	$from = '/' . preg_quote($from, '/') . '/';
	return preg_replace($from, $to, $haystack, 1);
}

function is_surely_number(string $number): bool
{
	return is_numeric($number) && !str_starts_with($number, "0") && !str_ends_with($number, ".") && !str_contains(strtolower($number), "e");
}

function is_commercial_callsign($callsign)
{
	return preg_match("/^([A-Z]{3})(\d\w{0,3})$/", $callsign);
}

function discord_message($user, $title, $description, $color, $fields, $image)
{
	global $config;
	if (!$config["discord_webhook_url"])
		return;

	$payload = [
		"content" => null,
		"embeds" => [
			[
				"title" => $title,
				"description" => $description,
				"url" => SITE_URL,
				"color" => $color,
				"fields" => $fields,
				"author" => [
					"name" => $user->firstname.' '.$user->lastname.' ('.$user->vid.')',
					"url" => "https://ivao.aero/Member.aspx?ID=".$user->vid,
					"icon_url" => SITE_URL.'/img/flags/32/'.strtolower($user->division).'.png'
				],
				"image" => $image,
				"footer" => [
					"text" => $config["event_name"]." Booking System",
				]
			]
		],
		"attachments" => [],
	];

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
	curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
	curl_setopt($curl, CURLOPT_URL, $config["discord_webhook_url"]);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	$result = curl_exec($curl);
	curl_close($curl);
	return $result;
}

function discord_other_message($title, $description, $color)
{
	return discord_message(Session::User(), $title, $description, $color, [], null);
}

function discord_user_message($title, $color, $user)
{
	$fields = [
		[
			"name" => "VID",
			"value" => $user->vid,
		],
		[
			"name" => "Name",
			"value" => $user->firstname.' '.$user->lastname,
		],
		[
			"name" => "Division",
			"value" => $user->division,
			"inline" => true,
		],
		[
			"name" => "Country",
			"value" => $user->country,
			"inline" => true,
		],
		[
			"name" => "Rating",
			"value" => User::RATINGS_ATC[$user->ratingAtc].'/'.User::RATINGS_PILOT[$user->ratingPilot],
			"inline" => true,
		],
	];

	return discord_message($user, $title, null, $color, $fields, null);
}

function discord_flight_message($title, $color, $flight)
{
	$fields = [
		[
			"name" => "Flight",
			"value" => '**'.$flight->callsign.'** ('.$flight->flightNumber.')',
		],
		[
			"name" => "Aircraft",
			"value" => $flight->aircraftIcao.($flight->aircraftFreighter ? ' (cargo)' : ''),
		],
		[
			"name" => "Origin",
			"value" => $flight->originIcao,
			"inline" => true,
		],
		[
			"name" => "Destination",
			"value" => $flight->destinationIcao,
			"inline" => true,
		],
		[
			"name" => "Parking",
			"value" => $flight->getPosition(false),
		],
		[
			"name" => "Off-Block",
			"value" => getHumanDateTime($flight->departureTime, false),
			"inline" => true,
		],
		[
			"name" => "On-Block",
			"value" => getHumanDateTime($flight->arrivalTime, false),
			"inline" => true,
		],
	];

	$image = [
		"url" => SITE_URL.'/img/airlines/'.substr($flight->callsign, 0, 3).'.png',
	];

	return discord_message(Session::User(), $title, null, $color, $fields, $image);
}

function discord_slot_message($title, $description, $color, $slot)
{
	$fields = [
		[
			"name" => "Callsign",
			"value" => $slot->callsign,
		],
		[
			"name" => "Aircraft",
			"value" => $slot->aircraftIcao,
		],
		[
			"name" => "Origin",
			"value" => $slot->originIcao,
			"inline" => true,
		],
		[
			"name" => "Destination",
			"value" => $slot->destinationIcao,
			"inline" => true,
		],
		[
			"name" => "Route",
			"value" => '`'.$slot->route.'`',
		],
		[
			"name" => "Parking",
			"value" => $slot->getPosition(false),
		],
		[
			"name" => "Off-Block",
			"value" => getHumanDateTime($slot->departureTime, false),
			"inline" => true,
		],
		[
			"name" => "On-Block",
			"value" => getHumanDateTime($slot->arrivalTime, false),
			"inline" => true,
		],
	];

	$image = [
		"url" => SITE_URL.'/img/airlines/'.substr($slot->callsign, 0, 3).'.png',
	];

	return discord_message(Session::User(), $title, $description, $color, $fields, $image);
}
  
?>
