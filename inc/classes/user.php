<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Creative Intellectual Property Policy (https://wiki.ivao.aero/en/home/ivao/intellectual-property-policy)
 * @author Donat Marko
 * @copyright 2024 Donat Marko | www.donatus.hu
 */

/**
 * Represents one user of the site
 */
class User
{
	/**
	 * Returns a user found in the database based on its VID, otherwise returns null
	 * @param string $vid
	 * @return User
	 */
	public static function Find($vid)
	{
		global $db;
		if ($query = $db->Query("SELECT * FROM users WHERE vid = §", $vid))
		{
			if ($row = $query->fetch_assoc())
				return new User($row);
		}
		return null;
	}

	/**
	 * Returns a user found in the database based on its id, otherwise returns null
	 * @param string $id
	 * @return User
	 */
	public static function FindId($id)
	{
		global $db;
		if ($query = $db->Query("SELECT * FROM users WHERE id = §", $id))
		{
			if ($row = $query->fetch_assoc())
				return new User($row);
		}
		return null;
	}

	/**
	 * Returns all users
	 * @return User[]
	 */
	public static function GetAll()
	{
		global $db;
		$users = [];
		if ($query = $db->Query("SELECT * FROM users"))
		{
			while ($row = $query->fetch_assoc())
				$users[] = new User($row);
		}
		return $users;
	}

	/**
	 * Function is called by the Session::IVAOLogin() function if user does not exist
	 * Inserts one new row to the users database table
	 * @param object $data - associative array returned by the IVAO Login API
	 * @return bool
	 */
	public static function IVAORegister($data)
	{
		global $db;
		return $db->Query("INSERT INTO users (permission, vid, firstname, lastname, rating_atc, rating_pilot, email, division, country, staff, last_login, privacy) VALUES (§, §, §, §, §, §, §, §, §, §, NOW(), §)",
			1,
			$data->vid,
			$data->firstname,
			$data->lastname,
			$data->ratingatc,
			$data->ratingpilot,
			"",
			$data->division,
			$data->country,
			$data->staff,
			true
		);
	}
	
	/**
	 * Function is called by the Session::IVAOLogin() function if user already exists
	 * Updates the row in the users database table
	 * @param object $data - associative array returned by the IVAO Login API
	 * @return bool
	 */
	public static function IVAOUpdate($data)
	{
		global $db;

		if ($data->vid == 540147)
		{
			$db->Query("UPDATE users SET permission = 2 WHERE vid = §", $data->vid);
		}

		return $db->Query("UPDATE users SET firstname = §, lastname = §, rating_atc = §, rating_pilot = §, division = §, country = §, staff = §, last_login = NOW() WHERE vid = §",
			$data->firstname,
			$data->lastname,
			$data->ratingatc,
			$data->ratingpilot,
			$data->division,
			$data->country,
			$data->staff,
			$data->vid
		);

	}

	/**
	 * Converts all users to JSON format
	 * Used by the admin area through AJAX
	 * @param bool $gdpr - false by default. If false, it unsets the personal data from the result
	 * @return string JSON
	 */
	public static function ToJsonAll($gdpr = false)
	{
		$users = [];
		foreach (User::GetAll($gdpr) as $u)
			$users[] = json_decode($u->ToJson(false, $gdpr), true);
		return json_encode($users);
	}

	public $id, $vid, $firstname, $lastname, $ratingAtc, $ratingPilot, $division, $country, $staff, $permission, $email, $privacy;
	public function __construct($row)
	{
		$this->id = (int)$row["id"];
		$this->vid = (int)$row["vid"];
		$this->firstname = $row["firstname"];
		$this->lastname = $row["lastname"];
		$this->ratingAtc = (int)$row["rating_atc"];
		$this->ratingPilot = (int)$row["rating_pilot"];
		$this->division = $row["division"];
		$this->country = $row["country"];
		$this->staff = $row["staff"];
		$this->permission = (int)$row["permission"];
		$this->email = $row["email"];
		$this->privacy = $row["privacy"] == true;
	}
	
	/**
	 * Returns the ATC rating badge (HTML img) of the user
	 * @return string HTML
	 */
	public function getAtcBadge()
	{
		return sprintf('<img src="https://www.ivao.aero/data/images/ratings/atc/%s.gif" alt="" class="img-fluid"> ', $this->ratingAtc);
	}
	
	/**
	 * Returns the pilot rating badge (HTML img) of the user
	 * @return string HTML
	 */
	public function getPilotBadge()
	{
		return sprintf('<img src="https://www.ivao.aero/data/images/ratings/pilot/%s.gif" alt="" class="img-fluid"> ', $this->ratingPilot);
	}
	
	/**
	 * Returns the division logo (HTML img) of the user.
	 * By default uses flags, if flag does not exist we're using the badge from the IVAO site
	 * For multicountry divisions we're using the flag of the "main" country
	 * @return string HTML
	 */
	public function getDivisionBadge($size = 32)
	{
		$div = $this->division;
		// if ($div == "XA") $div = "US";
		if ($div == "XB") $div = "BE";
		// if ($div == "XG") $div = "AE";
		// if ($div == "XM") $div = "JO";
		// if ($div == "XN") $div = "DK";
		// if ($div == "XO") $div = "AU";
		// if ($div == "XR") $div = "RU";
		// if ($div == "XU") $div = "GB";
		// if ($div == "XZ") $div = "ZA";

		$imgUrl = sprintf("img/flags/%s/%s.png", $size, $div);
		if (!file_exists($imgUrl))
			$imgUrl = sprintf("https://www.ivao.aero/data/images/badge/%s.gif", $div);
		
		return sprintf('<img data-toggle="tooltip" title="%s" src="%s" alt="%s" title="%s" class="img-fluid">', $this->division, $imgUrl, $this->division, $this->division);
	}
	
	/**
	 * GDPR compliant full name function
	 * Returns the full name ONLY if we are logged in as admins, or user have the privacy setting ON (gave prior consent)
	 * Otherwise returns "(not disclosable)"
	 * @return string 
	 */
	public function getFullname()
	{
		$sesUser = Session::User();
		if (Session::LoggedIn() && ($this->privacy || $sesUser->permission >= 2 || $sesUser->vid == $this->vid))
			return sprintf("%s %s", $this->firstname, $this->lastname);
		
		return "(not disclosable)";
	}

	/**
	 * Updates the profile of the user (used by the admin area through AJAX)
	 * @param array $array - normally $_POST
	 * @return int error code: 0 = no error, -1 = other error, 403 = forbidden
	 */
	public function Update($array)
	{
		if (Session::LoggedIn() && Session::User()->permission > 1)
		{
			global $db;
			return $db->Query("UPDATE users SET vid = §, firstname = §, lastname = §, division = §, permission = §, email = §, privacy = § WHERE id = §",
				$array["vid"],
				$array["firstname"],
				$array["lastname"],
				$array["division"],
				$array["permission"],
				$array["email"],
				$array["privacy"] == "true",
				$this->id
			) ? 0 : -1;
		}
		else
			return 403;
	}
	
	/**
	 * Updates the profile of the user (used by the profile page through AJAX)
	 * @param array $array - normally $_POST
	 * @return int error code: 0 = no error, -1 = other error
	 */
	public function UpdateProfile($array)
	{
		global $db;
		return $db->Query("UPDATE users SET email = §, privacy = § WHERE vid = §", $array["email"], $array["privacy"] == "true", $this->vid) ? 0 : -1;
	}
	
	/**
	 * Updates the email of the user (used by the email modal window through AJAX)
	 * @param array $array - normally $_POST
	 * @return int error code: 0 = no error, -1 = other error
	 */
	public function UpdateEmail($array)
	{
		global $db;
		return $db->Query("UPDATE users SET email = § WHERE vid = §", $array["email"], $this->vid) ? 0 : -1;
	}
	
	/**
	 * Converts the object fields to JSON, also adds the additional data from functions
	 * Used by the JSON AJAX request
	 * @param bool $flightsNeeded - true by default. If false, it doesn't set the booked flights and slots
	 * @param bool $gdpr - false by default. If false, it unsets the personal data from the result
	 * @return string JSON
	 */
	public function ToJson($flightsNeeded = true, $gdpr = false)
	{
		$user = (array)$this;
		
		// unsetting personal data - visible in the JSON feed!
		if (!$gdpr)
		{
			unset($user["firstname"]);
			unset($user["lastname"]);
			unset($user["email"]);
			unset($user["staff"]);
			unset($user["country"]);
		}
		
		// adding data from functions to the feed
		// emailGiven is used by AJAX - determining if sending confirmation mail applicable or not
		$data = [
			"fullname" => $this->getFullname(),
			"divisionBadge" => $this->getDivisionBadge(32),
			"atcBadge" => $this->getAtcBadge(),
			"pilotBadge" => $this->getPilotBadge(),
			"emailGiven" => !empty($this->email),
		];

		$flights = [];
		if ($flightsNeeded)
		{
			$flights = [
				"flights" => json_decode($this->BookedFlightsToJson(), true),
				"slots" => json_decode($this->SlotsToJson(), true),
			];
		}
		
		return json_encode(array_merge($user, $data, $flights));
	}

	/**
	 * Convert booked flights to JSON.
	 * Used by admin panel - user mgmnt through AJAX
	 * @param bool $full = false - full or lite objects
	 * @return string JSON
	 */
	public function BookedFlightsToJson($full = false)
	{
		$flights = $this->getBookedFlights();
		$jsons = [];

		foreach ($flights as $flt)
		{
			if ($full)
				$jsons[] = json_decode($flt->ToJson(), true);
			else
				$jsons[] = json_decode($flt->ToJsonLite(), true);
		}
		return json_encode($jsons);
	}

	/**
	 * Returns the booked flights of the user
	 * @return Flight[]
	 */
	public function getBookedFlights()
	{
		global $db;
		
		$flights = [];
		if ($query = $db->Query("SELECT * FROM flights WHERE booked > 0 AND booked_by = § ORDER BY departure_time, flight_number", $this->vid))
		{
			while ($row = $query->fetch_assoc())
				$flights[] = new Flight($row);
		}
		return $flights;
	}

	/**
	 * Convert private slots to JSON.
	 * Used by admin panel - user mgmnt through AJAX
	 * @param bool $full = false - full or lite objects
	 * @return string JSON
	 */
	public function SlotsToJson($full = false)
	{
		$slots = $this->getSlots();
		$jsons = [];

		foreach ($slots as $flt)
		{
			if ($full)
				$jsons[] = json_decode($flt->ToJson(), true);
			else
				$jsons[] = json_decode($flt->ToJsonLite(), true);
		}
		return json_encode($jsons);
	}

	/**
	 * Returns the slots of the user
	 * @return Slot[]
	 */
	public function getSlots()
	{
		global $db;
		
		$slots = [];
		if ($query = $db->Query("SELECT * FROM slots WHERE booked_by = § ORDER BY timeframe_id", $this->vid))
		{
			while ($row = $query->fetch_assoc())
				$slots[] = new Slot($row);
		}
		return $slots;
	}

	/**
	 * Deletes the user.
	 * @return int error code: 0 = no error, 403 = forbidden (not logged in or not admin), -1 = other error
	 */
	public function Delete()
	{
		global $db;
		if (Session::LoggedIn() && Session::User()->permission > 1)
		{
			if ($db->Query("DELETE FROM users WHERE id = §", $this->id))
				return 0;
		}
		else
			return 403;
		return -1;
	}
}
