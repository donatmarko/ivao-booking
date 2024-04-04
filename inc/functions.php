<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Intellectual Property Policy (https://doc.ivao.aero/rules2:ipp)
 * @author Donat Marko
 * @copyright 2024 Donat Marko | www.donatus.hu
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
function getHumanDateTime($dt)
{
	return date("d M Hi", strtotime($dt)) . "Z";
}

/**
 * Decides whether given string starts with the given part or not
 * @param string $query
 * @param string $string
 * @return bool
 */
function startsWith($query, $string)
{
	return substr($string, 0, strlen($query)) === $query;
}

/**
 * Decides whether the given string ends with the given parameter or not.
 * @param string $query
 * @param string $string
 * @return bool
 */
function endsWith($query, $string)
{
	$length = strlen($query);
	return $length === 0 || (substr($string, -$length) === $query);
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
		die("<script>window.location.href='$url';</script>");
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
  
?>
