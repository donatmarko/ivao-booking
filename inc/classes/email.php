<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Creative Intellectual Property Policy (https://wiki.ivao.aero/en/home/ivao/intellectual-property-policy)
 * @author Donat Marko
 * @copyright 2024 Donat Marko | www.donatus.hu
 */

/**
 * Everything necessary for the email creation and sending.
 */
class Email
{
	/**
	 * Sends email through PHPMailer SMTP. (https://github.com/PHPMailer/PHPMailer)
	 * @param mixed $from - name, email
	 * @param mixed $tos - name, email
	 * @param mixed $ccs - name, email
	 * @param mixed $bccs - name, email
	 * @param string $subject
	 * @param string $message
	 * @return int error code (check API doc)
	 */
	public static function SendSmtp($from, $tos, $ccs, $bccs, $subject, $message)
	{
		global $config;

		if (!$config["mail_enabled"])
			return 0;

		$cfg_smtp = new PHPMailer\PHPMailer\PHPMailer(true);
		try
		{       
			$cfg_smtp->SMTPDebug = 0;
			$cfg_smtp->isSMTP();                    
			$cfg_smtp->Host = $config["mail_smtp_server"];
			$cfg_smtp->SMTPAuth = $config["mail_smtp_auth"];
			$cfg_smtp->Username = $config["mail_smtp_username"];
			$cfg_smtp->Password = $config["mail_smtp_password"];
			$cfg_smtp->SMTPSecure = $config["mail_smtp_secure"];
			$cfg_smtp->Port = $config["mail_smtp_port"];
			$cfg_smtp->CharSet = "UTF-8";

			// set from
			if (is_string($from))
				$cfg_smtp->setFrom($from);
			else if (!array_key_exists('name', $from))
				$cfg_smtp->setFrom($from['email']);
			else
				$cfg_smtp->setFrom($from['email'], $from['name']);
		
			// missing recipient
			if ($tos == null)
				return 1;
			else
			{
				if (is_array($tos) && !isArrayAssociative($tos))
				{
					foreach($tos as $to)
					{
						if (is_string($to))
							$cfg_smtp->addAddress($to);
						else if (!array_key_exists('name', $to))
							$cfg_smtp->addAddress($to['email']);
						else
							$cfg_smtp->addAddress($to['email'], $to['name']);
					}
				}
				else if (is_string($tos))
					$cfg_smtp->addAddress($tos);
				else if (!array_key_exists('name', $tos))
					$cfg_smtp->addAddress($tos['email']);
				else
					$cfg_smtp->addAddress($tos['email'], $tos['name']);
			}

			// adding Carbon Copy
			if ($ccs != null)
			{
				if (is_array($ccs) && !isArrayAssociative($ccs))
				{
					foreach($ccs as $cc)
					{
						if (is_string($cc))
							$cfg_smtp->addCC($cc);
						else if (!array_key_exists('name', $cc))
							$cfg_smtp->addCC($cc['email']);
						else
							$cfg_smtp->addCC($cc['email'], $cc['name']);
					}
				}
				else if (is_string($ccs))
					$cfg_smtp->addCC($ccs);
				else if (!array_key_exists('name', $ccs))
					$cfg_smtp->addCC($ccs['email']);
				else
					$cfg_smtp->addCC($ccs['email'], $ccs['name']);
			}

			// adding Blind Carbon Copy
			if ($bccs != null)
			{
				if (is_array($bccs) && !isArrayAssociative($bccs))
				{
					foreach($bccs as $bcc)
					{
						if (is_string($bcc))
							$cfg_smtp->addBCC($bcc);
						else if (!array_key_exists('name', $bcc))
							$cfg_smtp->addBCC($bcc['email']);
						else
							$cfg_smtp->addBCC($bcc['email'], $bcc['name']);
					}
				}
				else if (is_string($bccs))
					$cfg_smtp->addBCC($bccs);
				else if (!array_key_exists('name', $bccs))
					$cfg_smtp->addBCC($bccs['email']);
				else
					$cfg_smtp->addBCC($bccs['email'], $bccs['name']);
			}

			$cfg_smtp->isHTML(true);
			$cfg_smtp->Subject = $subject;
			$cfg_smtp->Body    = $message;            
			$cfg_smtp->send();
			return 0;
		}
		catch (Exception $e)
		{
			// error in SMTP process
			return 2;
		}
	}

	/**
	 * Processes the data received from contact form.
	 * @param array $array generally _POST
	 * @return int error code - 0: no error, -1: other error, 403: user not logged in
	 */
	public static function ContactForm($array)
	{
		global $config;

		if (Session::LoggedIn())
		{
			$subject = "";
			switch ($array["subject"])
			{
				case 1:
					$subject = "General inquiries, questions";
					break;
				case 2:
					$subject = "Bugreport";
					break;
				case 3:
					$subject = "Incorrect flight data";
					break;
				case 4:
					$subject = "Private slots";
					break;
				case 5:
					$subject = "Event feedback";
					break;
			}

			$subject = sprintf("[%s] %s %s", $config["event_name"], $subject, date("d/m/Y H:i"));
			$user = Session::User();
			$fullname = sprintf("%s %s", $user->firstname, $user->lastname);
			$email = $user->email;
			$message = sprintf("<p>%s</p>--<br>%s (%s)<br>Division: %s<br>%s", nl2br(htmlspecialchars($array["message"])), $fullname, $user->vid, $user->division, $email);

			Email::SendSmtp(
				["name" => $config["mail_from_name"], "email" => $config["mail_from_email"]],
				["name" => $fullname, "email" => $email],
				null,
				$config["division_email"],
				$subject,
				$message
			);
			return 0;
		}
		else
			return 403;
		return -1;
	}

	/**
	 * Preparing email message to sending, and actually sends it 
	 * @param string $message
	 * @param string $toName
	 * @param string $toEmail
	 * @param string $subject
	 * @return int error code - 0: no error, 403: user not logged in, -1: other error
	 */
	public static function Prepare($message, $toName, $toEmail, $subject)
	{
		global $config;
		if (Session::LoggedIn())
		{
			$subject = sprintf("[%s] %s", $config["event_name"], $subject);
			$message = Email::ReplaceGlobalVars($message) . Email::getSignature();

			$result = Email::SendSmtp(["name" => $config["mail_from_name"], "email" => $config["mail_from_email"]],
				["name" => $toName, "email" => $toEmail],
				null,
				null,
				$subject,
				$message
			);

			return $result === 0;
		}
		else
			return 403;
		return -1;
	}

	/**
	 * Replaces the global (i.e. user) variables in the email message template and returns the mail text
	 * @param string $email
	 * @return string
	 */
	public static function ReplaceGlobalVars($email)
	{
		global $config;
		$u = Session::User();

		$email = str_replace('%firstname%',     $u->firstname,            $email);
		$email = str_replace('%lastname%',      $u->lastname,             $email);
		$email = str_replace('%division_name%', $config["division_name"], $email);
		$email = str_replace('%url%',           $config["url"],           $email);
		return $email;
	}

	/**
	 * Sends email with free text.
	 * From: sender user with noreply mail address
	 * To: event mail address
	 * BCCs: appropriate recipients
	 * @param array $array - recipientsCode (1: all bookers, 2: flight bookers, 3: unconfirmed flight bookers, 4: slot bookers)
	 * @return int error code - 0: no errors, -1: other error, 403: forbidden (not admin)
	 */
	public static function SendFreeText($array)
	{
		global $config;
		$sesUser = Session::User();
		$recipientsCode = (int)$array["recipients_code"];
		$subject = $array["subject"];
		$message = $array["message"];

		if ($sesUser && $sesUser->permission > 1)
		{
			// all members with at least one flight/slot
			if ($recipientsCode == 1)
			{
				$toEmails = [];
				foreach (Flight::GetAll() as $flt)
				{					
					$user = User::Find($flt->bookedBy);
					if ($user && !empty($user->email))
						$toEmails[] = $user->email;
				}
				foreach (Slot::GetAll() as $flt)
				{
					$user = User::Find($flt->bookedBy);
					if ($user && !empty($user->email))
						$toEmails[] = $user->email;
				}
			}

			// members with at least one flight
			if ($recipientsCode == 2)
			{
				$toEmails = [];
				foreach (Flight::GetAll() as $flt)
				{
					$user = User::Find($flt->bookedBy);
					if ($user && !empty($user->email))
						$toEmails[] = $user->email;
				}
			}

			// members with at least one unconfirmed (prebooked) flight
			if ($recipientsCode == 3)
			{
				$toEmails = [];
				foreach (Flight::GetAll() as $flt)
				{
					if ($flt->booked == "prebooked")
					{
						$user = User::Find($flt->bookedBy);
						if ($user && !empty($user->email))
							$toEmails[] = $user->email;
					}
				}
			}

			// members with at least one slot
			if ($recipientsCode == 4)
			{
				$toEmails = [];
				foreach (Slot::GetAll() as $flt)
				{
					$user = User::Find($flt->bookedBy);
					if ($user && !empty($user->email))
						$toEmails[] = $user->email;
				}
			}

			// only sending the email if the code is valid :)
			if ($recipientsCode >= 1 && $recipientsCode <= 4)
			{
				$toEmails = array_values(array_unique($toEmails));

				$subject = sprintf("[%s] %s", $config["event_name"], $subject);
				$message = Email::ReplaceGlobalVars($message) . Email::getSignature();
				$result = false;
			
				$result = Email::SendSmtp(
					["name" => sprintf("%s %s", $sesUser->firstname, $sesUser->lastname), "email" => $config["mail_from_email"]],
					$config["division_email"],
					null,
					$toEmails,
					$subject,
					$message
				);

				if ($result === 0)
					return 0;
			}
		}
		else
			return 403;
		return -1;
	}

	/**
	 * Returns the constant signature which must appear at the end of every email.
	 * @return string
	 */
	private static function getSignature()
	{
		global $config;		
		return sprintf('<p>--<br>
			This email has been sent automatically, please do not reply! Should you have any questions, use <a href="%s/contact">our contact form</a> in the booking system.<br>
			To opt-out from receiving such emails, we kindly invite you to remove your email address on your profile.</p>',
			$config["url"]
		);
	}
}