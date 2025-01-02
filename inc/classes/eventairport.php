<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Creative Intellectual Property Policy (https://wiki.ivao.aero/en/home/ivao/intellectual-property-policy)
 * @author Donat Marko
 * @copyright 2025 Donat Marko | www.donatus.hu
 */

/**
 * Represents one airport which participates in the event
 */
class EventAirport
{
	/**
	 * Returns an airport found in the database based on its ICAO code, otherwise returns null
	 * @param string $icao
	 * @return EventAirport
	 */
	public static function Find($icao)
	{
		global $db;
		$query = $db->Query("SELECT * FROM airports WHERE icao = §", $icao);
		if ($row = $query->fetch_assoc())
			return new EventAirport($row);

		return null;
	}

	/**
	 * Returns an airport found in the database based on its id, otherwise returns null
	 * @param int $id
	 * @return EventAirport
	 */
	public static function FindId($id)
	{
		global $db;
		$query = $db->Query("SELECT * FROM airports WHERE id = §", $id);
		if ($row = $query->fetch_assoc())
			return new EventAirport($row);

		return null;
	}
	
	/**
	 * Gets all EventAirports from the database
	 * @param bool $disabledsToo if false, returns only the enabled airports, otherwise all of them
	 * @return EventAirport[] 
	 */
	public static function GetAll($disabledsToo = false)
	{
		global $db;
		$apts = [];

		if ($disabledsToo)
			$sql = "SELECT * FROM airports ORDER BY `order`";
		else
			$sql = "SELECT * FROM airports WHERE enabled = true ORDER BY `order`";

		$query = $db->Query($sql);
		while ($row = $query->fetch_assoc())
			$apts[] = new EventAirport($row);

		return $apts;
	}

	/**
	 * Converts all EventAirports to JSON format
	 * Used by the admin area through AJAX
	 * @param bool $disabledsToo if false, returns only the enabled airports, otherwise all of them
	 * @return string JSON
	 */
	public static function ToJsonAll($disabledsToo = false)
	{
		$apts = [];
		foreach (EventAirport::GetAll($disabledsToo) as $apt)
			$apts[] = json_decode($apt->ToJson(), true);
		return json_encode($apts);
	}

	/**
	 * Returns the statistic numbers in an associative array about the booked/free flights
	 * @return array
	 */
	public static function getStatisticsAll()
	{ 
		global $db;
		$stat = [
			"free" => 0,
			"prebooked" => 0,
			"booked" => 0
		];

		$query = $db->Query("SELECT booked, COUNT(*) AS num FROM flights GROUP BY booked");
		while ($row = $query->fetch_assoc())
		{
			switch ($row["booked"])
			{
				case 0:
					$stat["free"] = $row["num"];
					break;
				case 1:
					$stat["prebooked"] = $row["num"];
					break;
				case 2:
					$stat["booked"] = $row["num"];
					break;
			}
		}
		return $stat;
	}

	public static function getSlotStatisticsAll()
	{ 
		global $db;
		$stat = [
			"free" => 0,
			"requested" => 0,
			"granted" => 0
		];

		foreach (Timeframe::GetAll() as $tf)
		{
			$stat["free"] += $tf->count;
		}

		$query = $db->Query("SELECT booked, COUNT(*) AS num FROM slots GROUP BY booked");
		while ($row = $query->fetch_assoc())
		{
			switch ($row["booked"])
			{
				case 1:
					$stat["requested"] = $row["num"];
					break;
				case 2:
					$stat["granted"] = $row["num"];
					break;
			}
		}
		return $stat;
	}
	
	public $id, $icao, $name, $order, $enabled;
	/**
	 * @param array $row - associative array from fetch_assoc()
	 */
	public function __construct($row)
	{
		$this->id = (int)$row["id"];
		$this->icao = $row["icao"];
		$this->name = $row["name"];
		$this->order = (int)$row["order"];
		$this->enabled = $row["enabled"] == 1;
	}
	
	/**
	 * Returns the respective Airport object based on its ICAO code, otherwise returns null because of the nature of the Airport::Find() function
	 * @return Airport
	 */
	public function getAirport()
	{
		return Airport::Find($this->icao);
	}

	/**
	 * Returns departure flights from the airport
	 * @return Flight[]
	 */
	public function getDepartures()
	{
		global $db;
		$flights = [];
		$query = $db->Query("SELECT * FROM flights WHERE origin_icao = § ORDER BY departure_time, flight_number", $this->icao);
		while ($row = $query->fetch_assoc())
			$flights[] = new Flight($row);

		return $flights;
	}
	
	/**
	 * Returns arrival flights from the airport
	 * @return Flight[]
	 */
	public function getArrivals()
	{
		global $db;
		$flights = [];
		$query = $db->Query("SELECT * FROM flights WHERE destination_icao = § ORDER BY arrival_time, flight_number", $this->icao);
		while ($row = $query->fetch_assoc())
			$flights[] = new Flight($row);

		return $flights;
	}

	/**
	 * Returns the statistic numbers in an associative array about the booked/free flights at the airport
	 * @return array
	 */
	public function getStatistics()
	{ 
		global $db;
		$stat = [
			"free" => 0,
			"prebooked" => 0,
			"booked" => 0
		];

		$query = $db->Query("SELECT booked, COUNT(*) AS num FROM flights WHERE origin_icao = § OR destination_icao = § GROUP BY booked", $this->icao, $this->icao);
		while ($row = $query->fetch_assoc())
		{
			switch ($row["booked"])
			{
				case 0:
					$stat["free"] = $row["num"];
					break;
				case 1:
					$stat["prebooked"] = $row["num"];
					break;
				case 2:
					$stat["booked"] = $row["num"];
					break;
			}
		}
		return $stat;
	}

	public function getSlotStatistics()
	{ 
		global $db;
		$stat = [
			"free" => 0,
			"requested" => 0,
			"granted" => 0,
		];

		foreach ($this->getTimeframes() as $tf)
		{
			$stat["free"] += $tf->count;
		}

		$query = $db->Query("SELECT booked, COUNT(*) AS num FROM slots WHERE origin_icao = § OR destination_icao = § GROUP BY booked", $this->icao, $this->icao);
		while ($row = $query->fetch_assoc())
		{
			switch ($row["booked"])
			{
				case 1:
					$stat["requested"] = $row["num"];
					break;
				case 2:
					$stat["granted"] = $row["num"];
					break;
			}
		}
		return $stat;
	}

	/**
	 * Converts the object fields to JSON, also adds the additional data from functions
	 * @return string JSON
	 */
	public function ToJson()
	{
		$apt = (array)$this;
		
		// adding data from functions to the feed
		$data = [
			"airport" => $this->getAirport() ? json_decode($this->getAirport()->ToJson(), true) : null,
		];
		
		return json_encode(array_merge($apt, $data));
	}

	/**
	 * Saves data about the event airport.
	 * @param string[] $array normally $_POST
	 * @return int error code: 0 = no error, 403 = forbidden (not logged in or not admin), -1 = other error
	 */
	public function Update($array)
	{
		global $db;
		if (!Session::LoggedIn() || Session::User()->permission < 2)
			return 403;

		$db->Query("UPDATE airports SET icao = §, name = §, `order` = §, enabled = § WHERE id = §", $array["icao"], $array["name"], $array["order"], $array["enabled"] == "true", $this->id);
		return 0;
	}

	/**
	 * Creates a new event airport.
	 * @param string[] $array normally $_POST
	 * @return int error code: 0 = no error, 403 = forbidden (not logged in or not admin), -1 = other error
	 */
	public static function Create($array)
	{
		global $db;
		if (!Session::LoggedIn() || Session::User()->permission < 2)
			return 403;

		$db->Query("INSERT INTO airports (icao, name, `order`, enabled) VALUES (§, §, §, §)", $array["icao"], $array["name"], $array["order"], $array["enabled"] == "true");
		return 0;
	}

	/**
	 * Deletes the event airport.
	 * @return int error code: 0 = no error, 403 = forbidden (not logged in or not admin), -1 = other error
	 */
	public function Delete()
	{
		global $db;
		if (!Session::LoggedIn() || Session::User()->permission < 2)
			return 403;

		$db->Query("DELETE FROM airports WHERE id = §", $this->id);
		return 0;
	}

	/**
	 * Gets the assigned private slot timeframes for this airport
	 * @return Timeframe[]
	 */
	public function getTimeframes()
	{
		global $db;
		$tfs = [];
		$query = $db->Query("SELECT * FROM timeframes WHERE airport_icao = § ORDER BY time", $this->icao);
		while ($row = $query->fetch_assoc())
			$tfs[] = new Timeframe($row);
		
		return $tfs;
	}

	public function getFlights()
	{
		global $db;

		$flights = json_decode(Flight::ToJsonAll());
		$deps = array_values(array_filter($flights, fn($x) => $x->originIcao == $this->icao));
		$arrs = array_values(array_filter($flights, fn($x) => $x->destinationIcao == $this->icao));

		$slots = json_decode(Slot::ToJsonAll());
		$depslots = array_values(array_filter($slots, fn($x) => $x->originIcao == $this->icao));
		$arrslots = array_values(array_filter($slots, fn($x) => $x->destinationIcao == $this->icao));

		return json_encode([
			"flights" => [
				"departures" => $deps, 
				"arrivals" => $arrs,
			],
			"slots" => [
				"departures" => $depslots,
				"arrivals" => $arrslots,
			]
		]);
	}
}
  