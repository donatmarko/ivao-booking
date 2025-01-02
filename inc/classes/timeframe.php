<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Creative Intellectual Property Policy (https://wiki.ivao.aero/en/home/ivao/intellectual-property-policy)
 * @author Donat Marko
 * @copyright 2025 Donat Marko | www.donatus.hu
 */

/**
 * Represents one timeframe in where private slots can be booked.
 */
class Timeframe 
{
	const TYPES = [
		"BOTH",
		"ARR",
		"DEP"
	];

	/**
	 * Converts all tiemframes to JSON format
	 * Used by the admin area through AJAX
	 * @return string JSON
	 */
	public static function ToJsonAll()
	{
		$timeframes = [];
		foreach (Timeframe::GetAll() as $tf)
		{
			$timeframes[] = json_decode($tf->ToJson(), true);
		}
		return json_encode($timeframes);
	}

	/**
	 * Returns a timeframe found in the database based on its id, otherwise returns null
	 * @param string $icao
	 * @return Timeframe
	 */
	public static function Find($id)
	{
		global $db;
		if ($query = $db->Query("SELECT * FROM timeframes WHERE id = §", $id))
		{
			if ($row = $query->fetch_assoc())
				return new Timeframe($row);
		}
		return null;
	}

	public static function GetAll()
	{
		global $db;
		$timeframes = [];

		if ($query = $db->Query("SELECT * FROM timeframes ORDER BY airport_icao, time, type"))
		{
			while ($row = $query->fetch_assoc())
				$timeframes[] = new Timeframe($row);
		}
		return $timeframes;
	}

	/**
	 * Creates a new timeframe (batch).
	 * @param string[] $array normally $_POST
	 * @return int error code: 0 = no error, 403 = forbidden (not logged in or not admin), -1 = other error
	 */
	public static function Create($array)
	{
		global $db;
		if (!Session::LoggedIn() || Session::User()->permission < 2)
			return 403;

		$ok = true;
		for ($i = $array["hour_from"]; $i <= $array["hour_to"]; $i++)
		{
			$datetime = sprintf("%s %s:%s:00", $array["date"], $i, $array["minute"]);
			$db->Query("INSERT INTO timeframes (airport_icao, `type`, `time`, `count`) VALUES (§, §, §, §)", $array["airport_icao"], $array["timeframe_type"], $datetime, $array["count"]);
		}

		return 0;
	}

	public $id, $airportIcao, $type, $time, $count;	
	public function __construct($row)
	{
		$this->id = (int)$row["id"];
		$this->airportIcao = $row["airport_icao"];
		$this->type = (int)$row["type"];
		$this->time = $row["time"];
		$this->count = (int)$row["count"];
	}

	/**
	 * Deletes the timeframe and also the child bookings.
	 * @return int error code: 0 = no error, 403 = forbidden (not logged in or not admin), -1 = other error
	 */
	public function Delete()
	{
		global $db;
		if (Session::LoggedIn() && Session::User()->permission > 1)
		{
			foreach ($this->getSlots() as $sb)
				$sb->Delete();

			if ($db->Query("DELETE FROM timeframes WHERE id = §", $this->id))
				return 0;
		}
		else
			return 403;
		return -1;
	}

	/**
	 * Modifies the timeframe.
	 * @param string[] $array normally $_POST
	 * @return int error code: 0 = no error, 403 = forbidden (not logged in or not admin), -1 = other error
	 */
	public function Update($array)
	{
		global $db;
		if (!Session::LoggedIn() || Session::User()->permission < 2)
			return 403;

		$db->Query("UPDATE timeframes SET `type` = §, `time` = §, `count` = § WHERE id = §", $array["timeframe_type"], $array["time"], $array["count"], $this->id);
		return 0;
	}

	/**
	 * Converts the object fields to JSON, also adds the bookings and other additional data from functions
	 * @return string JSON
	 */
	public function toJsonSlots()
	{
		$timeframe = json_decode($this->ToJson(), true);
		
		$data = [
			"slots" => null
		];

		$slots = $this->getSlots();
		foreach ($slots as $s)
		{
			$obj = json_decode($s->ToJsonLite(), true);
			
			if (Session::LoggedIn() && Session::User()->permission > 1)
				$data["slots"][] = $obj;
			else
			{
				if ($obj["booked"] == "granted" || (Session::LoggedIn() && $obj["bookedBy"] == Session::User()->vid))
					$data["slots"][] = $obj;
			}
		}
		
		return json_encode(array_merge($timeframe, $data));
	}

	/**
	 * Converts the object fields to JSON, also adds the additional data from functions
	 * @return string JSON
	 */
	public function ToJson()
	{
		$timeframe = (array)$this;
		$apt = $this->getEventAirport();
		
		// adding data from functions to the feed
		$data = [
			"eventAirport" => $apt ? json_decode($apt->ToJson()) : null,
			"timeHuman" => getHumanDateTime($this->time),
			"typeHuman" => $this->getType(),
			"statistics" => $this->GetStatistics(),
			"sessionUser" => Session::LoggedIn() ? json_decode(Session::User()->ToJson()) : null,
		];
		
		return json_encode(array_merge($timeframe, $data));
	}

	/**
	 * Get all slots connected to this timeframe.
	 * @return Slot[]
	 */
	public function getSlots()
	{
		global $db;
		$ss = [];

		if ($query = $db->Query("SELECT * FROM slots WHERE timeframe_id = § ORDER BY booked, booked_at", $this->id))
		{
			while ($row = $query->fetch_assoc())
				$ss[] = new Slot($row);
		}
		return $ss;
	}

	/**
	 * Returns the statistic numbers in an associative array about the booked/free flights at the airport
	 * @return array
	 */
	public function getStatistics()
	{ 
		$stat = [
			"free" => $this->count,
			"requested" => 0,
			"granted" => 0
		];

		foreach ($this->getSlots() as $s)
		{			
			if ($s->booked == "granted")
			{
				$stat["granted"]++;
				
				if ($stat["free"] > 0)
					$stat["free"]--;
			}
			if ($s->booked == "requested")
				$stat["requested"]++;
		}

		return $stat;
	}

	/**
	 * Returns the EventAirport object, otherwise returns null.
	 * @return EventAirport
	 */
	public function getEventAirport()
	{
		return EventAirport::Find($this->airportIcao);
	}

	public function getType()
	{
		return self::TYPES[$this->type];
	}
}