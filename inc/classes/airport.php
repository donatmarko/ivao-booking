<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Intellectual Property Policy (https://doc.ivao.aero/rules2:ipp)
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
		global $dbNav;
		if ($query = $dbNav->GetSQL()->query("SELECT * FROM airports WHERE icao='" . $icao . "'"))
		{
			if ($row = $query->fetch_assoc())
				return new Airport($row);
		}
		return null;
	}
	
	public $icao, $iata, $country, $latitude, $longitude, $name, $elevation, $type;
	public function __construct($row)
	{
		$this->icao = $row["icao"];
		$this->iata = $row["iata"];
		$this->country = $row["country"];
		$this->latitude = (float)$row["latitude"];
		$this->longitude = (float)$row["longitude"];
		$this->name = $row["name"];
		$this->elevation = (int)$row["elevation"];
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
		$imgUrl = "img/flags/$size/" . $this->country . ".png";

		if (!file_exists($imgUrl))
			$imgUrl = "img/flags/$size/_unknown.png";
			
		return '<img src="' . $imgUrl . '" alt="' . $this->country . '" data-toggle="tooltip" title="Country: ' . $this->country . '" class="img-fluid"> ';
	}

	/**
	 * Returns the METAR of the airport.
	 * Not used
	 * @return string METAR
	 */
	public function getMetar()
	{
		global $config;
		return file_get_contents($config["wx_url"] . "?type=metar&icao=" . $this->icao);
	}

	/**
	 * Returns the TAF of the airport.
	 * Not used
	 * @return string TAF
	 */
	public function getTaf()
	{
		global $config;
		return file_get_contents($config["wx_url"] . "?type=taf&icao=" . $this->icao);
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
			/*"metar" => $this->getMetar(),
			"taf" => $this->getTaf(),*/
		];
		
		return json_encode(array_merge($apt, $data));
	}
}
 