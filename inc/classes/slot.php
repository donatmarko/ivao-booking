<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Intellectual Property Policy (https://doc.ivao.aero/rules2:ipp)
 * @author Donat Marko
 * @copyright 2021 Donat Marko | www.donatus.hu
 */

/**
 * Representing one particular slot
 */
class Slot
{
	/**
	 * Replaces the slot variables in the email text and returns the email text back
	 * @param string $email 
	 * @return string
	 */
	public function EmailReplaceVars($email)
	{
		$email = str_replace('%slot_callsign%',    $this->callsign,                                                            $email);
		$email = str_replace('%slot_aircraft%',    $this->aircraftIcao . ($this->aircraftFreighter ? ' (freighter)' : ''),     $email);

		if ($this->isDepartureEstimated)
			$email = str_replace('%slot_departure%', $this->originIcao . ' (est: ' . getHumanDateTime($this->departureTime) . ')', $email);
		else
			$email = str_replace('%slot_departure%', $this->originIcao . ' (' . getHumanDateTime($this->departureTime) . ')', $email);

		if ($this->isArrivalEstimated)
			$email = str_replace('%slot_destination%', $this->destinationIcao . ' (est: ' . getHumanDateTime($this->arrivalTime) . ')', $email);
		else
			$email = str_replace('%slot_destination%', $this->destinationIcao . ' (' . getHumanDateTime($this->arrivalTime) . ')', $email);

		$email = str_replace('%slot_position%',    $this->getPosition(false),                                                  $email);
		$email = str_replace('%slot_route%',       $this->route,                                                               $email);
		
		$user = User::Find($this->bookedBy);
		if ($user)
		{
			$email = str_replace('%slot_bookerFirstname%', $user->firstname, $email);
			$email = str_replace('%slot_bookerLastname%',  $user->lastname,  $email);
		}
		return $email;
	}

	/**
	 * Finding and returning slot based on its ID.
	 * If slot doesn't exist, returns null.
	 * @param int $id
	 * @return Slot
	 */
	public static function Find($id)
	{
		global $db;
		if ($query = $db->GetSQL()->query("SELECT * FROM slots WHERE id=" . $id))
		{
			if ($row = $query->fetch_assoc())
				return new Slot($row);
		}
		return null;
	}

	/**
	 * Gets all slots from the database
	 * @return Slot[] 
	 */
	public static function GetAll()
	{
		global $db;
		$flts = [];

		if ($query = $db->GetSQL()->query("SELECT * FROM slots ORDER BY departure_time, slotbooking_number"))
		{
			while ($row = $query->fetch_assoc())
				$flts[] = new Slot($row);
		}
		return $flts;
	}

	/**
	 * Creates a new slot by user.
	 * @param string[] $array normally $_POST
	 * @return int error code: 0 = no error, 403 = forbidden (not logged in), -1 = other error
	 */
	public static function Create($array)
	{
		global $db;
		if (Session::LoggedIn())
		{
			$u = Session::User();
			if ($db->GetSQL()->query("INSERT INTO slots (timeframe_id, callsign, origin_icao, destination_icao, aircraft_icao, aircraft_freighter, gate, route, booked, booked_by, booked_at) VALUES ('" . $array["timeframe_id"] . "', '" . $array["callsign"] . "', '" . $array["origin_icao"] . "', '" . $array["destination_icao"] . "', '" . $array["aircraft_icao"] . "', " . $array["aircraft_freighter"] . ", 'TBD', '" . $array["route"] . "', 1, " . $u->vid . ", NOW())"))
			{
				if (!empty($u->email))
				{
					$slot = @new Slot($_POST);
					$slot->bookedBy = $u->vid;
					$email = $slot->EmailReplaceVars(file_get_contents("contents/slot_request.html"));

					if (Email::Prepare($email, $u->firstname . " " . $u->lastname, $u->email, "Private slot request"))
						return 0;
				}
				else
					return 0;
			}
		}
		else
			return 403;
		return -1;
	}

	public $id, $timeframeId, $callsign, $aircraftIcao, $aircraftFreighter, $departureTime, $arrivalTime, $originIcao, $destinationIcao, $terminal, $gate, $route, $isArrivalEstimated, $isDepartureEstimated;
	public $booked, $bookedAt, $bookedBy;

	/**
	 * @param array $row - associative array from fetch_assoc()
	 */
	public function __construct($row)
	{
		$this->id = (int)$row["id"];
		$this->timeframeId = (int)$row["timeframe_id"];
		$this->callsign = $row["callsign"];
		$this->aircraftIcao = $row["aircraft_icao"];
		$this->aircraftFreighter = $row["aircraft_freighter"] == 1;
		$this->originIcao = $row["origin_icao"];
		$this->destinationIcao = $row["destination_icao"];
		$this->terminal = $row["terminal"];
		$this->gate = $row["gate"];
		$this->route = $row["route"];
		$this->bookedAt = $row["booked_at"];
		$this->bookedBy = $row["booked_by"];
		$this->isArrivalEstimated = false;
		$this->isDepartureEstimated = false;

		switch ($row["booked"])
		{
			case 1:
				$this->booked = "requested";
				break;
			case 2:
				$this->booked = "granted";
				break;
			default:
				$this->booked = "-/-";
				break;
		}

		// setting times
		$timeframe = $this->getTimeframe();
		if ($timeframe)
		{
			if ($timeframe->airportIcao == $this->originIcao)
			{
				$this->isArrivalEstimated = true;
				$this->departureTime = $timeframe->time;
				$this->arrivalTime = date("Y-m-d H:i:s", strtotime($this->departureTime) + $this->getCalculatedEET());
			}
			if ($timeframe->airportIcao == $this->destinationIcao)
			{
				$this->isDepartureEstimated = true;
				$this->arrivalTime = $timeframe->time;
				$this->departureTime = date("Y-m-d H:i:s", strtotime($this->arrivalTime) - $this->getCalculatedEET());
			}
		}
	}
	
	/**
	 * Forms ICAO airline code from the first 3 characters of the callsign and returns the Airline object, otherwise returns null.
	 * @return Airline
	 */
	public function getAirline()
	{
		if (Flight::isCommercialCallsign($this->callsign))
			return Airline::Find(substr($this->callsign, 0, 3));
		return null;
	}
	
	/**
	 * Returns the Airport object of destination, otherwise returns null.
	 * @return Airport
	 */
	public function getDestination()
	{
		return Airport::Find($this->destinationIcao);
	}
	
	/**
	 * Returns the Airport object of origin, otherwise returns null.
	 * @return Airport
	 */
	public function getOrigin()
	{
		return Airport::Find($this->originIcao);
	}
	
	/**
	 * Creates the content of "position" field to frontend/email
	 * Terminal or gate = "TBD"
	 * both empty => question mark / "no data available"
	 * @param bool $graphical if false, it will be text only for emails, otherwise Bootstrap 4 badges for UI/UX
	 * @return string
	 */
	public function getPosition($graphical = true)
	{
		$t = "";
		$g = "";
		
		if ($graphical)
		{
			if (empty($this->terminal) && empty($this->gate))
				$t = '<span class="badge badge-danger" data-toggle="tooltip" data-placement="top" title="No data available"><i class="fas fa-question"></i></span>';
			else
			{
				if (!empty($this->terminal))
				{
					if ($this->terminal === "TBD")
						$t = '<span class="badge badge-warning" data-toggle="tooltip" data-placement="top" title="Terminal: to be determined"><i class="far fa-building"></i> TBD</span> ';
					else
						$t = '<span class="badge badge-primary" data-toggle="tooltip" data-placement="top" title="Terminal"><i class="far fa-building"></i> ' . $this->terminal . '</span> ';
				}
				if (!empty($this->gate))
				{
					if ($this->gate === "TBD")
						$g = '<span class="badge badge-warning" data-toggle="tooltip" data-placement="top" title="Gate: to be determined"><i class="fas fa-plane"></i> TBD</span>';
					else
						$g = '<span class="badge badge-secondary" data-toggle="tooltip" data-placement="top" title="Gate"><i class="fas fa-plane"></i> ' . $this->gate . '</span>';								
				}
			}						
		}
		else
		{
			if (empty($this->terminal) && empty($this->gate))
				$t = '(no data available)';
			else
			{
				if (!empty($this->terminal))
				{
					if ($this->terminal === "TBD")
						$t = 'Terminal: to be determined ';
					else
						$t = 'Terminal: ' . $this->terminal;
				}
				if (!empty($this->gate))
				{
					if (!empty($t))
						$t .= ' / ';

					if ($this->gate === "TBD")
						$g = 'Gate: to be determined';
					else
						$g = 'Gate: ' . $this->gate;
				}
			}						
		}

		return $t . $g;
	}
	
	/**
	 * Returns the name of the aircraft from the NAV database
	 * @return string
	 */
	public function getAircraftName()
	{
		global $dbNav;
		if ($query = $dbNav->GetSQL()->query("SELECT * FROM aircrafts WHERE icao='" . $this->aircraftIcao . "'"))
		{
			if ($row = $query->fetch_assoc())
			{
				if ($this->aircraftFreighter)
					return $row["name"] . " (freighter)";
				else
					return $row["name"];
			}
		}
		return "";
	}
	
	/**
	 * Converts the object fields to JSON, also adds the additional data from functions
	 * Used by the JSON AJAX request
	 * @param bool $timeframeNeeded - if timeframe data is needed to the output or not 
	 * @return string JSON
	 */
	public function ToJson()
	{
		global $config;
		$slot = (array)$this;

		$ori = $this->getOrigin();
		$des = $this->getDestination();
		$airline = $this->getAirline();
		$timeframe = $this->getTimeframe();
		
		$data = [
			"departureTimeHuman" => getHumanDateTime($this->departureTime),
			"arrivalTimeHuman" => getHumanDateTime($this->arrivalTime),
			"airline" => $airline ? json_decode($airline->ToJson()) : null,
			"position" => $this->getPosition(),
			"bookedAtHuman" => $this->booked !== "free" ? getHumanDateTime($this->bookedAt) : null,
			"bookedByUser" => $this->booked !== "free" ? json_decode(User::Find($this->bookedBy)->ToJson(false)) : null,
			"sessionUser" => Session::LoggedIn() ? json_decode(Session::User()->ToJson(false)) : null,
			"originAirport" => $ori ? json_decode($ori->ToJson()) : null,
			"destinationAirport" => $des ? json_decode($des->ToJson()) : null,
			"aircraftName" => $this->getAircraftName(),
			"wxUrl" => $config["wx_url"],
			"greatCircleDistanceNm" => $ori && $des ? haversineGreatCircleDistance($ori->latitude, $ori->longitude, $des->latitude, $des->longitude, 3440) : null,
			"timeframe" => $timeframe ? json_decode($timeframe->ToJson(), true) : null,
		];

		return json_encode(array_merge($slot, $data));
	}

	/**
	 * Converts the object fields to JSON, also adds the a few additional data from functions
	 * Used by the User::slotsJsonAll() through AJAX request
	 * @return string JSON
	 */
	public function ToJsonLite()
	{
		global $config;
		$slot = (array)$this;

		$ori = $this->getOrigin();
		$des = $this->getDestination();
		$airline = $this->getAirline();
		
		$data = [
			"airline" => $airline ? json_decode($airline->ToJson()) : null,
			"position" => $this->getPosition(),
			"originAirport" => $ori ? json_decode($ori->ToJson()) : null,
			"destinationAirport" => $des ? json_decode($des->ToJson()) : null,
			"bookedAtHuman" => getHumanDateTime($this->bookedAt),
		];

		return json_encode(array_merge($slot, $data));
	}

	/**
	 * Deletes the slot by admins or user.
	 * @return int error code: 0 = no error, 403 = forbidden (not logged in, not admin or not the booker), -1 = other error
	 */
	public function Delete()
	{
		global $db;
		$sesUser = Session::User();

		if (Session::LoggedIn() && ($sesUser->permission > 1 || $sesUser->vid == $this->bookedBy))
		{
			if ($db->GetSQL()->query("DELETE FROM slots WHERE id=" . $this->id))
				return 0;
		}
		else
			return 403;
		return -1;
	}

	public function getTimeframe()
	{
		$tf = Timeframe::Find($this->timeframeId);
		if ($tf)
			return $tf;
		return null;
	}

	/**
	 * Modifies the slot.
	 * Updating bookedAt only if there was a change in the booking status
	 * @param string[] $array normally $_POST
	 * @return int error code: 0 = no error, 403 = forbidden (not logged in or not admin), -1 = other error, 1 = booker user does not exist
	 */
	public function Update($array)
	{
		global $db;
		if (Session::LoggedIn() && Session::User()->permission > 1)
		{
			if ($array["booked"] > 0 && !User::Find($array["booked_by"]))
				return 1;

			$toBeDeleted = false;
			if ($this->booked == "requested" && $array["booked"] == 2)
			{
				// requested -> granted
				$this->timeframeId = $array["timeframe_id"];
				$this->terminal = $array["terminal"];
				$this->gate = $array["gate"];
				$this->route = $array["route"];

				$user = User::Find($this->bookedBy);
				if ($user && !empty($user->email))
				{
					$email = $this->EmailReplaceVars(file_get_contents("contents/slot_accepted.html"));
					Email::Prepare($email, $user->firstname . " " . $user->lastname, $user->email, "Your slot has been accepted");
				}
			}
			else if ($array["booked"] == 0)
			{
				// requested/booked -> rejected
				$user = User::Find($this->bookedBy);
				if ($user && !empty($user->email))
				{
					// if a rejection message has been left
					if (!empty($array["reject_message"]))
						$rejectMessage = "Message of the evaluator:<br>" . $array["reject_message"];
					else
						$rejectMessage = "No message has been left by the evaluator.";

					$email = $this->EmailReplaceVars(file_get_contents("contents/slot_rejected.html"));
					$email = str_replace("%slot_rejectMessage%", $rejectMessage, $email);
					Email::Prepare($email, $user->firstname . " " . $user->lastname, $user->email, "Your slot has been rejected");
				}
				$toBeDeleted = true;
			}
			else
			{
				// if modification was made in timeframe, terminal, gate or route, we're sending a mail about the modification
				if ($array["timeframe_id"] != $this->timeframeId || $array["terminal"] != $this->terminal || $array["gate"] != $this->gate || $array["route"] != $this->route)
				{
					$this->timeframeId = $array["timeframe_id"];
					$this->terminal = $array["terminal"];
					$this->gate = $array["gate"];
					$this->route = $array["route"];

					$user = User::Find($this->bookedBy);
					if ($user && !empty($user->email))
					{
						$email = $this->EmailReplaceVars(file_get_contents("contents/slot_modified.html"));
						Email::Prepare($email, $user->firstname . " " . $user->lastname, $user->email, "Your slot has been modified");
					}
				}
			}

			if ($toBeDeleted)
				return $this->Delete();
			else
			{
				if ($array["booked_by"] == $this->bookedBy)
					$sql = "UPDATE slots SET timeframe_id=" . $array["timeframe_id"] . ", callsign='" . $array["callsign"] . "', origin_icao='" . $array["origin_icao"] . "', destination_icao='" . $array["destination_icao"] . "', aircraft_icao='" . $array["aircraft_icao"] . "', aircraft_freighter=" . $array["aircraft_freighter"] . ", terminal='" . $array["terminal"] . "', gate='" . $array["gate"] . "', route='" . $array["route"] . "', booked=" . $array["booked"] . " WHERE id=" . $array["id"];
				else
					$sql = "UPDATE slots SET timeframe_id=" . $array["timeframe_id"] . ", callsign='" . $array["callsign"] . "', origin_icao='" . $array["origin_icao"] . "', destination_icao='" . $array["destination_icao"] . "', aircraft_icao='" . $array["aircraft_icao"] . "', aircraft_freighter=" . $array["aircraft_freighter"] . ", terminal='" . $array["terminal"] . "', gate='" . $array["gate"] . "', route='" . $array["route"] . "', booked=" . $array["booked"] . ", booked_by=" . $array["booked_by"] . ", booked_at=NOW() WHERE id=" . $array["id"];
				
				if ($db->GetSQL()->query($sql))
					return 0;
			}
		}
		else
			return 403;
		return -1;
	}

	/**
	 * Returns the actual EET if neither arrival nor departure times are estimated.
	 * @return int [seconds]
	 */
	public function getActualEET()
	{
		if ($this->isArrivalEstimated || $this->isDepartureEstimated)
			return null;

		return strtotime($this->arrivalTime) - strtotime($this->departureTime);
	}
	
	/**
	 * Returns the calculated EET based on the aircraft performance and great circle distance.
	 * @return int [seconds]
	 */
	public function getCalculatedEET()
	{
		global $dbNav;

		$ori = $this->getOrigin();
		$des = $this->getDestination();
		if (!$ori || !$des)
			return null;

		$gcd = haversineGreatCircleDistance($ori->latitude, $ori->longitude, $des->latitude, $des->longitude, 3440);
		$speed = 250;

		if ($query = $dbNav->GetSQL()->query("SELECT * FROM aircrafts where icao='" . $this->aircraftIcao . "'"))
		{
			if ($row = $query->fetch_assoc())
			{
				if ($row["speed"] > 0)
					$speed = (int)$row["speed"];
			}
		}

		// some correction (for climb and descent)
		$t = round(($gcd / $speed + 0.66) * 3600);
		return $t;
	}
}
