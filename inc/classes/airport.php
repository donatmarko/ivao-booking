<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Creative Intellectual Property Policy (https://wiki.ivao.aero/en/home/ivao/intellectual-property-policy)
 * @author Donat Marko
 * @copyright 2024 Donat Marko | www.donatus.hu
 */

/**
 * Represents one airport which can be origin, destination or part of the event
 */
class Airport
{
	/**
	 * Returns an airport found in the database based on its ICAO code, otherwise returns null
	 * @param string $icao
	 * @return Airport
	 */
	public static function Find($icao)
	{
		global $db;
		$query = $db->Query("SELECT * FROM nav_airports WHERE ident = §", $icao);
		if ($row = $query->fetch_assoc())
			return new Airport($row);
		return null;
	}
	
	public $icao, $iata, $country, $latitude, $longitude, $name, $elevation, $type;
	public function __construct($row)
	{
		$this->icao = $row["ident"];
		$this->iata = $row["iata_code"];
		$this->country = $row["iso_country"];
		$this->latitude = (float)$row["latitude_deg"];
		$this->longitude = (float)$row["longitude_deg"];
		$this->name = $row["name"];
		$this->elevation = (int)$row["elevation_ft"];
		$this->type = $row["type"];
		
		$this->name = str_replace("International Airport", "", $this->name);
		$this->name = str_replace("Airport", "", $this->name);
		$this->name = str_replace("Airfield", "", $this->name);
		$this->name = str_replace("Air Base", "", $this->name);
		$this->name = trim($this->name);
	}
	
	/**
	 * Returns the country flag PNG if exists.
	 * @param int $size = 32
	 * @return string HTML
	 */
	public function getCountryFlag($size = 32)
	{
		$files = [
			sprintf("img/flags/%s/%s.png", $size, strtolower($this->country)),
			sprintf("img/flags/%s/%s.png", $size, $this->country),
			sprintf("img/flags/%s/_unknown.png", $size),
		];

		foreach ($files as $file) 
		{
			if (!file_exists($file))
				continue;
			
			return sprintf('<img src="%s" alt="%s" data-toggle="tooltip" title="%s" class="img-fluid flag-%s"> ', $file, $this->country, $this->country, $size);
		}
	}

	/**
	 * Returns the METAR/TAF of the airport.
	 * @return null|string 
	 */
	public function getWeather(string $type)
	{
		global $config;

		if (empty($config["wx_url"]))
			return null;
		
		$url = strtr($config["wx_url"], [
			"{type}" => $type,
			"{icao}" => $this->icao,
		]);

		$result = str_replace("\n", "<br>", file_get_contents($url));
		return json_encode(["result" => $result]);
	}

	/**
	 * Converts the object fields to JSON, also adds the additional data from functions
	 * METAR and TAF fields are not used because of the high performance load!
	 * @return string JSON
	 */
	public function ToJson()
	{
		$apt = (array)$this;
		
		// adding data from functions to the feed
		$data = [
			"countryFlag24" => $this->getCountryFlag(24),
			"countryFlag32" => $this->getCountryFlag(32),
			"countryFlag48" => $this->getCountryFlag(48),
		];
		
		return json_encode(array_merge($apt, $data));
	}
}
 