<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Intellectual Property Policy (https://doc.ivao.aero/rules2:ipp)
 * @author Donat Marko
 * @copyright 2024 Donat Marko | www.donatus.hu
 */

class Config
{
	private static $config = array();

	/**
	 * Reading config from database
	 * @return int error code: 0 = no error, -1 = other error
	 */
	public static function Get()
	{
		global $db, $config;
		if ($query = $db->GetSQL()->query("SELECT `key`, `value` FROM config"))
		{
			while ($row = $query->fetch_assoc())
				Config::$config[$row["key"]] = $row["value"];
			return array_merge($config, Config::$config);
		}
		else
			return null;
	}

	/**
	 * Updates config in database
	 * @param string $key
	 * @param mixed $value
	 * @return int error code: 0 = no error, -1 = other error
	 */
	public static function Write($key, $value)
	{
		global $db;
		return $db->GetSQL()->query("UPDATE config SET `value`='$value' WHERE `key`='$key'") ? 0 : -1;
	}

	/**
	 * Updates general settings.
	 * Only available for user's permission > 1
	 * @param string[] $array normally $_POST
	 * @return int error code: 0 = no error, 403 = forbidden (not logged in or not admin), -1 = other error
	 */
	public static function UpdateGeneral($array)
	{
		if (Session::LoggedIn() && Session::User()->permission > 1)
		{
			if (Config::Write("event_name", $array["event_name"]) == 0 &&
				Config::Write("mode", $array["mode"]) == 0 &&
				Config::Write("division_name", $array["division_name"]) == 0 &&
				Config::Write("division_web", $array["division_web"]) == 0 &&
				Config::Write("division_email", $array["division_email"]) == 0 &&
				Config::Write("division_facebook", $array["division_facebook"]) == 0 &&
				Config::Write("division_twitter", $array["division_twitter"]) == 0 &&
				Config::Write("wx_url", $array["wx_url"]) == 0 &&
				Config::Write("date_start", $array["date_start"]) == 0 &&
				Config::Write("date_end", $array["date_end"]) == 0)
				return 0;
		}
		else
			return 403;
		return -1;
	}

	/**
	 * Returns event start date in the MomentJS format
	 * @return string MomentJS
	 */
	public static function getDateStart()
	{
		return date("d/m/Y H:i", strtotime(Config::$config["date_start"]));
	}

	/**
	 * Returns event finish date in the MomentJS format
	 * @return string MomentJS
	 */
	public static function getDateEnd()
	{
		return date("d/m/Y H:i", strtotime(Config::$config["date_end"]));
	}
}

?>