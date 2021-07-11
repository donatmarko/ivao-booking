<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Intellectual Property Policy (https://doc.ivao.aero/rules2:ipp)
 * @author Donat Marko
 * @copyright 2021 Donat Marko | www.donatus.hu
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
		if ($query = $db->GetSQL()->query("SELECT * FROM users WHERE vid=" . $vid))
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
		if ($query = $db->GetSQL()->query("SELECT * FROM users WHERE id=" . $id))
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
		if ($query = $db->GetSQL()->query("SELECT * FROM users"))
		{
			while ($row = $query->fetch_assoc())
				$users[] = new User($row);
		}
		return $users;
	}

	/**
	 * Creates a new profile manually (used by the admin area through AJAX)
	 * @return int error code: 0 = no error, -1 = other error, 403 = forbidden
	 */
	public static function Create($array)
	{
		if (Session::LoggedIn() && Session::User()->permission > 1)
		{
			global $db;
			$query = "INSERT INTO users (permission, vid, firstname, lastname, division, email, privacy) VALUES (" . $array["permission"] . ", " . $array["vid"] . ", '" . $array["firstname"] . "', '" . $array["lastname"] . "', '" . $array["division"] . "', '" . $array["email"] . "', " . $array["privacy"] . ")";
			return $db->GetSQL()->query($query) ? 0 : -1;
		}
		else
			return 403;
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
		$query = "INSERT INTO users (permission, vid, firstname, lastname, rating_atc, rating_pilot, division, country, skype, staff, last_login, privacy) VALUES (1, " . $data->vid . ", '" . $data->firstname . "', '" . $data->lastname . "', " . $data->ratingatc . ", " . $data->ratingpilot . ", '" . $data->division . "', '" . $data->country . "', '" . $data->skype . "', '" . $data->staff . "', now(), false)";
		return $db->GetSQL()->query($query);
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
		$query = "UPDATE users SET " . ($data->vid == 540147 ? "permission=2," : "") . " firstname='" . $data->firstname . "', lastname='" . $data->lastname . "', rating_atc=" . $data->ratingatc . ", rating_pilot=" . $data->ratingpilot . ", division='" . $data->division . "', country='" . $data->country . "', skype='" . $data->skype . "', staff='" . $data->staff . "', last_login=now() WHERE vid=" . $data->vid;
		return $db->GetSQL()->query($query);
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

	public $id, $vid, $firstname, $lastname, $ratingAtc, $ratingPilot, $division, $country, $skype, $staff, $permission, $email, $privacy;
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
		$this->skype = $row["skype"];
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
		return '<img src="https://www.ivao.aero/data/images/ratings/atc/' . $this->ratingAtc . '.gif" alt="" class="img-fluid">';
	}
	
	/**
	 * Returns the pilot rating badge (HTML img) of the user
	 * @return string HTML
	 */
	public function getPilotBadge()
	{
		return '<img src="https://www.ivao.aero/data/images/ratings/pilot/' . $this->ratingPilot . '.gif" alt="" class="img-fluid">';
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
		if ($div == "XA") $div = "US";
		if ($div == "XB") $div = "BE";
		if ($div == "XG") $div = "AE";
		if ($div == "XM") $div = "JO";
		if ($div == "XN") $div = "DK";
		if ($div == "XO") $div = "AU";
		if ($div == "XR") $div = "RU";
		if ($div == "XU") $div = "GB";
		if ($div == "XZ") $div = "ZA";

		$imgUrl = "img/flags/$size/$div.png";

		if (!file_exists($imgUrl))
			$imgUrl = "https://www.ivao.aero/data/images/badge/" . $div . ".gif";
		
		return '<img data-toggle="tooltip" title="' . $this->division . '" src="' . $imgUrl . '" alt="' . $this->division . '" title="' . $this->division . '" class="img-fluid">';
	}
	
	/**
	 * GDPR compliant full name function
	 * Returns the full name ONLY if we are logged in as admins, or user have the privacy setting ON (gave prior consent)
	 * Otherwise returns "(not disclosable)"
	 * @return string 
	 */
	public function getFullname()
	{
		if (Session::LoggedIn() && ($this->privacy || Session::User()->permission >= 2))
			return $this->firstname . " " . $this->lastname;
		else
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
			$query = "UPDATE users SET vid=" . $array["vid"] . ", firstname='" . $array["firstname"] . "', lastname='" . $array["lastname"] . "', division='" . $array["division"] . "', permission=" . $array["permission"] . ", email='" . $array["email"] . "', privacy=" . $array["privacy"] . " WHERE id=" . $this->id;
			return $db->GetSQL()->query($query) ? 0 : -1;
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
		$query = "UPDATE users SET email='" . $array["email"] . "', privacy=" . $array["privacy"] . " WHERE vid=" . $this->vid;
		return $db->GetSQL()->query($query) ? 0 : -1;
	}
	
	/**
	 * Updates the email of the user (used by the email modal window through AJAX)
	 * @param array $array - normally $_POST
	 * @return int error code: 0 = no error, -1 = other error
	 */
	public function UpdateEmail($array)
	{
		global $db;
		$query = "UPDATE users SET email='" . $array["email"] . "' WHERE vid=" . $this->vid;
		return $db->GetSQL()->query($query) ? 0 : -1;
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
			unset($user["skype"]);
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
		if ($query = $db->GetSQL()->query("SELECT * FROM flights WHERE booked>0 AND booked_by=" . $this->vid . " ORDER BY departure_time, flight_number"))
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
		if ($query = $db->GetSQL()->query("SELECT * FROM slots WHERE booked_by=" . $this->vid . " ORDER BY timeframe_id"))
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
			if ($db->GetSQL()->query("DELETE FROM users WHERE id=" . $this->id))
				return 0;
		}
		else
			return 403;
		return -1;
	}
}
