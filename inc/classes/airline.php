<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Creative Intellectual Property Policy (https://wiki.ivao.aero/en/home/ivao/intellectual-property-policy)
 * @author Donat Marko
 * @copyright 2024 Donat Marko | www.donatus.hu
 */

/**
 * Represents one airline
 */
class Airline
{
	/**
	 * Returns an airline found in the database based on its ICAO code, otherwise returns null
	 * @param string $icao
	 * @return Airline
	 */
	public static function Find($icao)
	{
		global $db;
		$query = $db->Query("SELECT * FROM nav_airlines WHERE icao = §", $icao);
		if ($row = $query->fetch_assoc())
			return new Airline($row);
		return null;
	}
	
	public $icao, $name, $callsign;
	public function __construct($row)
	{
		$this->icao = $row["icao"];
		$this->name = $row["name"];
		$this->callsign = $row["callsign"];
	}
	
	/**
	 * Returns the logo (HTML img) of the airline if file exists.
	 * @return string HTML
	 */
	public function getLogo($small = false)
	{
		$files = [
			sprintf("img/airlines/%s.png", $this->icao),
			sprintf("img/airlines/%s.gif", $this->icao)
		];

		$small_size = $small ? ' style="width: 40%"' : '';

		foreach ($files as $file)
		{
			if (!file_exists($file))
				continue;

			return sprintf('<img data-toggle="tooltip" title="%s" src="%s" alt="%s" class="img-fluid airline-logo"%s> ', $this->callsign, $file, $this->icao, $small_size);
		}
	}
	
	/**
	 * Converts the object fields to JSON, also adds the additional data from functions
	 * Don't think it is currently used...
	 * @return string JSON
	 */
	public function ToJson()
	{
		$apt = (array)$this;
		
		// adding data from functions to the feed
		$data = [
			"logo" => $this->getLogo(false),
			"logoSmall" => $this->getLogo(true),
		];
		
		return json_encode(array_merge($apt, $data));
	}
}
  