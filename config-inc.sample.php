<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Creative Intellectual Property Policy (https://wiki.ivao.aero/en/home/ivao/intellectual-property-policy)
 * @author Donat Marko
 * @copyright 2024 Donat Marko | www.donatus.hu
 */

/**
 * SQL server settings.
 * We're using separate database for booking and navigational (airlines, airfields, aircraft) data.
 * The database user shall have read/write access to the booking, and at least read access to the navigational database.
 */
define('SQL_SERVER',       'localhost');
define('SQL_USERNAME',     'booking');
define('SQL_PASSWORD',     'ITSYOURPASSWORD');
define('SQL_DATABASE',     'booking');
define('SQL_DATABASE_NAV', 'booking_nav');

/**
 * URL of the booking system (full path).
 * Needed for the IVAO login API and to the emails.
 */
$config["url"]                = 'https://booking.hu.ivao.aero';

/**
 * Maintenance mode.
 * In this mode enabled, AJAX requests could happen through both GET and POST requests for testing purposes.
 * Normally this mode should be disabled, this way only accepts POST requests.
 */
$config["maintenance"]        = false;
$config["login_bypass_api"]   = false;

/**
 * Email settings.
 */
$config["mail_enabled"]       = false;
$config["mail_from_name"]     = 'IVAO Vatican booking system';
$config["mail_from_email"]    = 'events@ivao.va';

$config["mail_smtp_server"]   = 'mail.example.com';
$config["mail_smtp_port"]     = 587;
$config["mail_smtp_auth"]     = true;
$config["mail_smtp_secure"]   = 'tls';
$config["mail_smtp_username"] = 'smtp@api.example.com';
$config["mail_smtp_password"] = 'ITSYOURPASSWORD';