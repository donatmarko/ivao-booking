<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Creative Intellectual Property Policy (https://wiki.ivao.aero/en/home/ivao/intellectual-property-policy)
 * @author Donat Marko
 * @copyright 2025 Donat Marko | www.donatus.hu
 */

/**
 * Representing the active session on the page
 * static class
 */
class Session
{
	public function __construct() {  }
	
	/**
	 * Function is called by /login
	 * if function has been called and session exists, it means we've just logged in: in case of new user registering, otherwise updating last login time in DB
	 * Thereafter redirect to the main page
	 * If we are not logged in yet, redirect to the login_ivao.php file
	 */
	public static function IVAOLogin()
	{
		global $config;

		// strictly for testing purposes - circumventing the Login API
		if ($config["login_bypass_api"])
		{
			$_SESSION["LOGIN"] = new stdClass();
			$_SESSION["LOGIN"]->firstname = "Peter";
			$_SESSION["LOGIN"]->lastname = "Griffin";
			$_SESSION["LOGIN"]->vid = 100000;
			$_SESSION["LOGIN"]->ratingatc = 10;
			$_SESSION["LOGIN"]->ratingpilot = 10;
			$_SESSION["LOGIN"]->division = "VA";
			$_SESSION["LOGIN"]->country = "VA";
			$_SESSION["LOGIN"]->staff = "VA-DIR:VA-TC";
			$_SESSION["LOGIN"]->refreshToken = null;
		}
				
		if ($_SESSION["LOGIN"]->vid)
		{
			Session::GenerateXsrfToken();
			if (User::Find($_SESSION["LOGIN"]->vid))
				User::IVAOUpdate($_SESSION["LOGIN"]);
			else
				User::IVAORegister($_SESSION["LOGIN"]);
			
			redirect(SITE_URL);
		}
		else
		{
			redirect("newlogin_ivao.php?url=" . SITE_URL);
		}
	}

	/**
	 * Function redirects to the specified page if we are not already there, and we're not on login and logout pages
	 * @param $p name of the page
	 */
	private static function redirIfNotThere($p)
	{
		global $page;
		
		if ($page != $p && $page != "login" && $page != "logout")
			redirect($p);
	}
	
	/**
	 * Function checks access to the site
	 * If we are not logged in and requested page is profile or mybookings, redirects us to the main page, because profile page is only available as logged in
	 */
	public static function CheckAccess()
	{
		global $config, $page;
		$user = Session::User();

		/**
		 * Generating XSRF token is not present
		 * (user got logged off or was never logged on)
		 */
		if (!isset($_SESSION["xsrfToken"]))
			Session::GenerateXsrfToken();

		/**
		 * User is logged on, but does not exist in the database.
		 * Most likely I removed it, but who knows...
		 */
		if (Session::LoggedIn() && !$user)
		{
			Session::redirIfNotThere("logout");
		}

		/**
		 * If user is banned, redirecting them to the banned page
		 */
		if (Session::LoggedIn() && $user->permission < 1)
			Session::redirIfNotThere("403");

		/* If maintenance mode is active:
		* 		if we are not admins/editors and logged in, logs us out
		* 		if we are not logged in, redirects to the maintenance page
		*/
		if ($config["mode"] != 1 && $page != "json")
		{
			if (Session::LoggedIn() && $user->permission < 2)
				Session::IVAOLogout();
			
			if (!Session::LoggedIn())
				Session::redirIfNotThere("maintenance");
		}
		
		/**
		 * If page is admin, and we're logged in with lower permission than 2,
		 * or we are not logged in at all, redirects to the main page
		 */
		if (Session::LoggedIn() && $user->permission < 2 && in_array($page, ["admin"]))
			redirect(SITE_URL);
		
		/**
		 * If page is for logged in users only, and we're not logged in, redirects to the main page
		 */
		if (!Session::LoggedIn() && in_array($page, ["admin", "profile", "mybookings"]))
			redirect(SITE_URL);	
	}
	
	/**
	 * Self-explaining function ;)
	 */
	public static function IVAOLogout()
	{
		session_unset();
		session_destroy();
		setcookie("IVAO_LOGIN", "", time() - 3600);
		redirect(SITE_URL);
	}
	
	/**
	 * Returns the currently logged in user object, otherwise returns null
	 * @return User
	 */
	public static function User()
	{
		if (isset($_SESSION["LOGIN"]))
			return User::Find($_SESSION["LOGIN"]->vid);
		return null;
	}
	
	/**
	 * Returns whether we are logged in or not
	 * @return bool
	 */
	public static function LoggedIn()
	{
		return isset($_SESSION["LOGIN"]) && $_SESSION["LOGIN"]->vid !== null;
	}

	/**
	 * Generates and stores new XSRF token in the _SESSION
	 * @return string newly generated XSRF token
	 */
	public static function GenerateXsrfToken()
	{
		$_SESSION["xsrfToken"] = md5(uniqid(rand(), true));
		return $_SESSION["xsrfToken"];
	}

	/**
	 * Processes requests and acts accordingly
	 * Called at main
	 */
	public static function RequestProcessing()
	{
		global $page, $config;

		if ($page != "json")
			return;
	
		header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header("Content-Type: application/json");

		$type = $_POST["type"] ?? null;
		$id = $_POST["id"] ?? null;
		$action = $_POST["action"] ?? null;

		/**
		 * In case of POST request and not XSRF token present (or invalid XSRF token)
		 * we're dropping error 419 ("Page Expired" - Laravel specific code but I haven't found better...)
		 */
		if ($action != "getflights" && !empty($_POST) && (!isset($_POST["xsrfToken"]) || $_SESSION["xsrfToken"] !== $_POST["xsrfToken"]))
		{
			// 419 Page Expired
			echo json_encode(["error" => 419]);
			die();
		}
		
		if ($type == "flights")
		{
			if ($action == "getall")
				echo Flight::ToJsonAll();
			elseif ($action == "create")
				echo json_encode(["error" => Flight::Create($_POST)]);
			else
			{
				$f = Flight::Find($id);
				
				if ($f == null)
					echo json_encode(["error" => 404]);
				else
				{
					if ($action)
					{
						switch ($action)
						{
							// prebooking flight
							case "book":
								echo json_encode(["error" => $f->Prebook()]);
								break;
							// delete booking
							case "free":
								echo json_encode(["error" => $f->Free()]);
								break;
							// delete flight
							case "delete":
								echo json_encode(["error" => $f->Delete()]);
								break;
							// modify flight
							case "update":
								echo json_encode(["error" => $f->Update($_POST)]);
								break;
							// resend confirmation email
							case "sendconfirmation":
								echo json_encode(["error" => $f->SendConfirmationEmail()]);
								break;
							case "getoriginmetar":
								echo $f->getOriginWeather("metar");
								break;
							case "getorigintaf":
								echo $f->getOriginWeather("taf");
								break;
							case "getdestinationmetar":
								echo $f->getDestinationWeather("metar");
								break;
							case "getdestinationtaf":
								echo $f->getDestinationWeather("taf");
								break;
							default:
								echo json_encode(["error" => -1]);
								break;
						}
					}
					else
						echo $f->ToJSON();
				}
			}
			die();
		}

		if ($type == "timeframes")
		{
			if ($action == "getall")
				echo Timeframe::ToJsonAll();
			elseif ($action == "create")
				echo json_encode(["error" => Timeframe::Create($_POST)]);				
			else
			{
				$t = Timeframe::Find($id);

				if ($t == null)
					echo json_encode(["error" => 404]);
				else
				{
					if ($action)
					{
						switch ($action)
						{
							case "update":
								echo json_encode(["error" => $t->Update($_POST)]);
								break;
							case "delete":
								echo json_encode(["error" => $t->Delete()]);
								break;
							default:
								echo json_encode(["error" => -1]);
								break;
						}
					}
					else
						echo $t->toJsonSlots();
				}
			}
			die();
		}

		if ($type == "slots")
		{
			if ($action == "create")
				echo json_encode(["error" => Slot::Create($_POST)]);
			else
			{
				$s = Slot::Find($id);
				
				if ($s == null)
					echo json_encode(["error" => 404]);
				else
				{
					if ($action)
					{
						switch ($action)
						{
							case "update":
								echo json_encode(["error" => $s->Update($_POST)]);
								break;
							case "delete":
								echo json_encode(["error" => $s->Delete()]);
								break;
							case "getoriginmetar":
								echo $s->getOriginWeather("metar");
								break;
							case "getorigintaf":
								echo $s->getOriginWeather("taf");
								break;
							case "getdestinationmetar":
								echo $s->getDestinationWeather("metar");
								break;
							case "getdestinationtaf":
								echo $s->getDestinationWeather("taf");
								break;
							default:
								echo json_encode(["error" => -1]);
								break;
						}
					}
					else
						echo $s->ToJson();
				}
			}
			die();
		}

		if ($type == "users")
		{
			if (Session::LoggedIn() && Session::User()->permission > 1)
			{
				if ($action == "getall")
					echo User::ToJsonAll(true);		
				else
				{
					$u = User::FindId($id);

					if ($u == null)
						echo json_encode(["error" => 404]);
					else
					{
						if ($action)
						{
							switch ($action)
							{
								case "update":
									echo json_encode(["error" => $u->Update($_POST)]);
									break;
								case "delete":
									echo json_encode(["error" => $u->Delete()]);
									break;
								default:
									echo json_encode(["error" => -1]);
									break;
							}
						}
						else
							echo $u->ToJSON(true, true);
					}
				}
			}
			else
				echo json_encode(["error" => 403]);
			die();
		}

		if ($type == "eventairports")
		{
			if ($action == "getflights")
			{
				$apt = EventAirport::FindId($id);
				echo $apt->getFlights();
				die();
			}

			if (Session::LoggedIn() && Session::User()->permission > 1)
			{
				if ($action == "getall")
					echo EventAirport::ToJsonAll(true);
				elseif ($action == "create")
					echo json_encode(["error" => EventAirport::Create($_POST)]);
				else
				{
					$apt = EventAirport::FindId($id);

					if ($apt == null)
						echo json_encode(["error" => 404]);
					else
					{
						if ($action)
						{
							switch ($action)
							{
								case "update":
									echo json_encode(["error" => $apt->Update($_POST)]);
									break;
								case "delete":
									echo json_encode(["error" => $apt->Delete()]);
									break;
								default:
									echo json_encode(["error" => -1]);
									break;
							}
						}
						else
							echo $apt->ToJSON();
					}
				}
			}
			else
				echo json_encode(["error" => 403]);
			die();
		}

		if ($type == "profile")
		{
			// saving profile via profile page
			if ($action == "update")
				echo json_encode(["error" => Session::User()->UpdateProfile($_POST)]);
			
			// saving email only via modal window
			if ($action == "updateEmail")
				echo json_encode(["error" => Session::User()->UpdateEmail($_POST)]);

			die();
		}

		if ($type == "email")
		{
			if ($action == "sendFlightConfirmations")
				echo json_encode(["error" => Flight::ResendConfirmationEmails()]);
			if ($action == "sendFreeText" && !empty($_POST))
				echo json_encode(["error" => Email::SendFreeText($_POST)]);
			die();
		}

		if ($type == "admin")
		{
			if ($action == "updateGeneral")
				echo json_encode(["error" => Config::UpdateGeneral($_POST)]);
			die();
		}	

		if ($type == "session")
		{
			if (Session::LoggedIn())
				echo Session::User()->ToJson();
			else
				echo "null";
			die();
		}

		if ($type == "contact")
		{
			if (!empty($_POST))
				echo json_encode(["error" => Email::ContactForm($_POST)]);
		}

		if ($type == "contents")
		{
			if ($action == "getall")
				echo Content::ToJsonAll();
			else
			{
				$c = Content::Find($id);
				
				if ($c == null)
					echo json_encode(["error" => 404]);
				else
				{
					if ($action)
					{
						switch ($action)
						{
							case "update":
								echo json_encode(["error" => $c->Update($_POST)]);
								break;
							default:
								echo $c->ToJson();
								break;
						}
					}
					else
						echo $c->ToJson();
				}
			}
			die();
		}

		die();
	}
}
