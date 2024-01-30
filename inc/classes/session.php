<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Intellectual Property Policy (https://doc.ivao.aero/rules2:ipp)
 * @author Donat Marko
 * @copyright 2021 Donat Marko | www.donatus.hu
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
			$_SESSION["LOGIN"]->skype = "peter.griffin";
			$_SESSION["LOGIN"]->staff = "VA-DIR:VA-TC";
		}

		if (isset($_SESSION["LOGIN"]))
		{
			Session::GenerateXsrfToken();
			if (User::Find($_SESSION["LOGIN"]->vid))
				User::IVAOUpdate($_SESSION["LOGIN"]);
			else
				User::IVAORegister($_SESSION["LOGIN"]);
			
			redirect("/");
		}
		else
			redirect("newlogin_ivao.php?url=" . $config["url"]);
	}

	/**
	 * Function is called by /auth/callback
	 * This fuction will received call back from IVAO Oauth2 then redirect the code and state to the new login page
	 * Redirect to the newlogin_ivao.php file
	 */
    public static function OAuth2Callback()
	{
        if (isset($_GET['code']) && isset($_GET['state'])) {
            redirect('Location: newlogin_ivao.php?code='. $_GET['code'] . '&state=' . $_GET['state']);
        } else {
            redirect('Location: newlogin_ivao.php');
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
		global $db, $config, $page;

		/**
		 * Generating XSRF token is not present
		 * (user got logged off or was never logged on)
		 */
		if (!isset($_SESSION["xsrfToken"]))
			Session::GenerateXsrfToken();

		/**
		 * If user is banned, redirecting him/her to the banned page
		 */
		if (Session::LoggedIn() && Session::User()->permission < 1)
			Session::redirIfNotThere("403");

		/* If maintenance mode is active:
		* 		if we are not admins/editors and logged in, logs us out
		* 		if we are not logged in, redirects to the maintenance page
		*/
		if ($config["mode"] != 1)
		{
			if (Session::LoggedIn() && Session::User()->permission < 2)
				Session::IVAOLogout();
			
			if (!Session::LoggedIn())
				Session::redirIfNotThere("maintenance");
		}

		/**
		 * If page is admin, and we're logged in with lower permission than 2,
		 * or we are not logged in at all, redirects to the main page
		 */
		if (($page == "admin" || $page == "teszt") && ((Session::LoggedIn() && Session::User()->permission < 2) || !Session::LoggedIn()))
			redirect("/");
		
		/**
		 * If page is for logged in users only, and we're not logged in, redirects to the main page
		 */
		if (!Session::LoggedIn() && ($page == "profile" || $page == "mybookings"))
			redirect("/");	
	}
	
	/**
	 * Self-explaining function ;)
	 */
	public static function IVAOLogout()
	{
		session_destroy();
		setcookie("IVAO_LOGIN", "", time()-3600);
		redirect("/");
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
		return isset($_SESSION["LOGIN"]);
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
	 * Returns XSRF token in formatted or unformatted way
	 * @param enum (meta, js) - META: meta tag, JS: javascript snippet (storing as global variable), OTHER: plain text
	 * @return string
	 */
	public static function GetXsrfToken($type)
	{
		if ($type == "meta")
			return '<meta name="xsrf-token" value="' . $_SESSION["xsrfToken"] . '">';
		if ($type == "js")
			return '<script>var XSRF_TOKEN = "' . $_SESSION["xsrfToken"] . '";</script>';
		return $_SESSION["xsrfToken"];
	}

	/**
	 * Processes requests and acts accordingly
	 * Called at main
	 */
	public static function RequestProcessing()
	{
		global $page, $config;

		if ($page == "json")
		{
			header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
			header("Content-Type: application/json");

			/**
			 * In case of POST request and not XSRF token present (or invalid XSRF token)
			 * we're dropping error 419 ("Page Expired" - Laravel specific code but I haven't found better...)
			 */
			if (!empty($_POST))
			{
				if (!isset($_POST["xsrfToken"]) || $_SESSION["xsrfToken"] !== $_POST["xsrfToken"])
				{
					// 419 Page Expired
					echo json_encode(["error" => 419]);
					die();
				}
			}

			// In maintenace mode we accept GET requests as well
			$requestArray = $config["maintenance"] ? $_REQUEST : $_POST;

			$type = isset($requestArray["type"]) ? $requestArray["type"] : null;
			$id = isset($requestArray["id"]) ? $requestArray["id"] : null;
			$action = isset($requestArray["action"]) ? $requestArray["action"] : null;
			
			if ($type == "flights")
			{
				if ($action == "getall")
					echo Flight::ToJsonAll();
				elseif ($action == "create")
					echo json_encode(["error" => Flight::Create($requestArray)]);
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
									echo json_encode(["error" => $f->Update($requestArray)]);
									break;
								// resend confirmation email
								case "sendconfirmation":
									echo json_encode(["error" => $f->SendConfirmationEmail()]);
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
					echo json_encode(["error" => Timeframe::Create($requestArray)]);				
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
									echo json_encode(["error" => $t->Update($requestArray)]);
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
					echo json_encode(["error" => Slot::Create($requestArray)]);
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
									echo json_encode(["error" => $s->Update($requestArray)]);
									break;
								case "delete":
									echo json_encode(["error" => $s->Delete()]);
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
					elseif ($action == "create")
						echo json_encode(["error" => User::Create($requestArray)]);			
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
										echo json_encode(["error" => $u->Update($requestArray)]);
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
				if (Session::LoggedIn() && Session::User()->permission > 1)
				{
					if ($action == "getall")
						echo EventAirport::ToJsonAll(true);
					elseif ($action == "create")
						echo json_encode(["error" => EventAirport::Create($requestArray)]);
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
										echo json_encode(["error" => $apt->Update($requestArray)]);
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
					echo json_encode(["error" => Session::User()->UpdateProfile($requestArray)]);
				
				// saving email only via modal window
				if ($action == "updateEmail")
					echo json_encode(["error" => Session::User()->UpdateEmail($requestArray)]);

				die();
			}

			if ($type == "email")
			{
				if ($action == "sendFlightConfirmations")
					echo json_encode(["error" => Flight::ResendConfirmationEmails()]);
				if ($action == "sendFreeText" && !empty($requestArray))
					echo json_encode(["error" => Email::SendFreeText($requestArray)]);
				die();
			}

			if ($type == "admin")
			{
				if ($action == "updateGeneral")
					echo json_encode(["error" => Config::UpdateGeneral($requestArray)]);
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
				if (!empty($requestArray))
					echo json_encode(["error" => Email::ContactForm($requestArray)]);
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
									echo json_encode(["error" => $c->Update($requestArray)]);
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
}
