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
		global $dbNav;
		if ($query = $dbNav->Query("SELECT * FROM airlines WHERE icao = §", $icao))
		{
			if ($row = $query->fetch_assoc())
				return new Airline($row);
		}
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
	public function getLogo()
	{
		$imgUrl = sprintf("img/airlines/%s.gif", $this->icao);
		if (file_exists($imgUrl))
			return sprintf('<img src="%s" alt="%s" class="img-fluid"> ', $imgUrl, $this->icao);
		return "";
	}
	
	/**
	 * Returns the logo (HTML img) in smaller version of the airline if file exists.
	 * @return string HTML
	 */
	public function getLogoSmall()
	{
		$imgUrl = sprintf("img/airlines/%s.gif", $this->icao);
		if (file_exists($imgUrl))
			return sprintf('<img src="%s" alt="%s" class="img-fluid" style="width: 40%%"> ', $imgUrl, $this->icao);
		return "";
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
			"logo" => $this->getLogo(),
			"logoSmall" => $this->getLogoSmall(),
		];
		
		return json_encode(array_merge($apt, $data));
	}
}
  