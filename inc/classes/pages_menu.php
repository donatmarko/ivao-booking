<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Creative Intellectual Property Policy (https://wiki.ivao.aero/en/home/ivao/intellectual-property-policy)
 * @author Donat Marko
 * @copyright 2025 Donat Marko | www.donatus.hu
 */

/**
 * Represents editable contents in the system.
 * Can be emails or (static) pages.
 */
class Content 
{
	/**
	 * Returns all pages.
	 * For the time being objects are statically populated.
	 * @return Content[]
	 */
	public static function GetAll()
	{
		return [
			new Content("banner", "Banner (home page)", "page"),
			new Content("briefing", "Briefing", "page"),
			new Content("slot_instructions", "Private slot instructions", "page"),
			new Content("403", "403 - forbidden", "page"),
			new Content("404", "404 - not found", "page"),
			new Content("maintenance", "Under maintenance", "page"),
			new Content("flight_booking", "Email: flight booking", "email"),
			new Content("slot_request", "Email: slot requested", "email"),
			new Content("slot_accepted", "Email: slot accepted", "email"),
			new Content("slot_modified", "Email: slot modified", "email"),
			new Content("slot_rejected", "Email: slot rejected", "email"),
		];
	}

	/**
	 * Returns a specific content based on its name.
	 * @param string $name
	 * @return Content
	 */
	public static function Find($name)
	{
		foreach (Content::GetAll() as $content)
		{
			if ($content->name == $name)
				return $content;
		}
		return null;
	}

	/**
	 * Converts all contents to JSON format
	 * Used by admin panel through AJAX request
	 * @return string JSON
	 */
	public static function ToJsonAll()
	{
		$list = [];
		foreach (Content::GetAll() as $c)
			$list[] = json_decode($c->ToJson(false), true);
		return json_encode($list);
	}

	public $name, $title, $type, $body;
	public function __construct($name, $title, $type)
	{
		$this->name = $name;
		$this->title = $title;
		$this->type = $type;
		$filename = sprintf("contents/%s.html", $name);

		if (file_exists($filename))
			$this->body = file_get_contents($filename);
	}

	/**
	 * Converts content to JSON format
	 * Optionally unsets the $body (for ToJsonAll())
	 * @param bool $withBody = true
	 * @return string JSON
	 */
	public function ToJson($withBody = true)
	{
		$data = (array)$this;

		if (!$withBody)
			unset($data["body"]);

		return json_encode($data);
	}

	/**
	 * Updates content body.
	 * Only for administrators.
	 * @param array normally $_POST
	 * @return int error code - 0: no errors, -1: other error, 403: forbidden
	 */
	public function Update($array)
	{
		if (Session::LoggedIn() && Session::User()->permission > 1)
		{
			$filename = sprintf("contents/%s.html", $this->name);

			if (file_exists($filename))
			{
				if (file_put_contents($filename, $array["body"]))
					return 0;
			}
		}
		else
			return 403;
		return -1;
	}
}

class Pages
{
	private static $scripts = [];
	private static $pages = [];
	private static $js;

	public static function Add($href)
	{
		if (empty($href))
			$href = "banner";

		$filename = sprintf("inc/%s.php", $href);

		if (!empty($href) && file_exists($filename))
			Pages::$pages[] = $filename;
		else
			Pages::$pages[] = "inc/404.php";

		Pages::AddJS($href);
	}
	
	public static function AddJS($href)
	{
		if (empty($href))
			$href = "banner";

		$filename = sprintf("js/%s.js", $href);

		if (file_exists($filename))
			Pages::$scripts[] = $filename;
	}

	public static function AddJSinline($js)
	{
		Pages::$js .= $js;
	}

	public static function Get()
	{
		foreach (Pages::$pages as $p)
			include_once($p);
	}

	public static function GetJS()
	{
		$result = "";
		foreach (Pages::$scripts as $s)
			$result .= sprintf('<script>%s</script>', file_get_contents($s));

		if (!empty(Pages::$js))
			$result .= sprintf('<script>%s</script>', Pages::$js);
			
		return $result;
	}
}

/**
 * Represents the main navbar at the top of the page
 */
class Menu
{	
	protected static $menuItems = [];

	/**
	 * Stroring menu items
	 * @param array[] 
	 */
	public static function addItems($menuItems)
	{
		Menu::$menuItems = array_merge(Menu::$menuItems, $menuItems);
	}

	/**
	 * Returning the menu itself
	 * @return string
	 */
	public static function Get()
	{
		global $config;
		global $page;
				
		echo '
		<nav class="navbar navbar-expand-md navbar-dark bg-primary fixed-top">
			<div class="container">
				<a class="navbar-brand" href="' . SITE_URL . '">';

		if (!empty($config["division_logo"]))
			echo '<img class="img-fluid" src="' . $config["division_logo"] . '" width="130">';
		else
			echo $config["event_name"];

		echo '</a>
				<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
				<div class="collapse navbar-collapse" id="navbarCollapse">
					<ul class="navbar-nav ml-auto">';

		foreach (Menu::$menuItems as $item)
		{
			$ok = false;

			if (isset($item["condition"]) && $item["condition"])
			{
				$ok = true;
			}

			if (isset($item["permission"]) && Session::LoggedIn())
			{
				if (Session::User()->permission >= $item["permission"])
					$ok = true;
			}

			if (isset($item["loggedIn"]))
			{
				if ($item["loggedIn"] && Session::LoggedIn())
					$ok = true;
				if (!$item["loggedIn"] && !Session::LoggedIn())
					$ok = true;
			}
			
			if (!isset($item["loggedIn"]) && !isset($item["permission"]) && !isset($item["condition"]))
				$ok = true;
			
			if ($ok)
				echo '<li class="nav-item"><a class="nav-link' . ($page === $item["href"] ? ' active' : '') . '" href="' . $item["href"] . '">' . $item["text"] . "</a></li>";
		}
		
		// if we are logged in, adding an user menu at the end of the menu bar
		if (Session::LoggedIn())
		{
			echo '<li class="nav-item dropdown">
				<a class="nav-link dropdown-toggle' . ($page === "profile" || $page === "mybookings" ? ' active' : '') . '" href="#" role="button" data-toggle="dropdown">' . Session::User()->getFullname() . '</a>
				<div class="dropdown-menu">
					<a class="dropdown-item' . ($page === "mybookings" ? ' active' : '') . '" href="mybookings">My booked flights</a>
					<a class="dropdown-item' . ($page === "profile" ? ' active' : '') . '" href="profile">Profile</a>
					<a class="dropdown-item" href="logout">Logout</a>
				</div>
			  </li>';
		}
		
		echo '</ul>
				</div>
			</div>
		</nav>
		';
	 }
}