<?php
/**
 * Flight booking system for RFE or similar events.
 * Created by Donat Marko (IVAO VID 540147) 
 * Any artwork/content displayed on IVAO is understood to comply with the IVAO Creative Intellectual Property Policy (https://wiki.ivao.aero/en/home/ivao/intellectual-property-policy)
 * @author Donat Marko
 * @copyright 2025 Donat Marko | www.donatus.hu
 */

/**
 * SQL server settings.
 * We now use one single database for booking and navigational (airlines, airfields, aircraft) data.
 * The database user shall have read/write access to the database.
 */
define('SQL_SERVER',       'localhost');
define('SQL_USERNAME',     'booking');
define('SQL_PASSWORD',     'ITSYOURPASSWORD');
define('SQL_DATABASE',     'booking');

/**
 * URL of the booking system (full path).
 * Needed for the IVAO login API and to the emails.
 */
define('SITE_URL',         'https://booking.va.ivao.aero/');

/**
 * Maintenance mode.
 * In this mode enabled, AJAX requests could happen through both GET and POST requests for testing purposes.
 * Normally this mode should be disabled, this way only accepts POST requests.
 */
$config["login_bypass_api"]   = false;

/**
 * IVAO SSO parameters.
 * Can be requested through https://developers.ivao.aero/
 */
define('IVAOSSO_OPENID_URL',     'https://api.ivao.aero/.well-known/openid-configuration');
define('IVAOSSO_CLIENT_ID',      'your-client-id');
define('IVAOSSO_CLIENT_SECRET',  'your-client-secret');
define('IVAOSSO_REDIRECT_URI',   'https://booking.va.ivao.aero/auth/callback');

/**
 * Email settings.
 */
$config["mail_enabled"]       = true;
$config["mail_mode"]          = 'smtp';  // can be smtp or sendmail

$config["mail_from_name"]     = 'IVAO Vatican Booking System';
$config["mail_from_email"]    = 'rfe@booking.va.ivao.aero';

$config["mail_replyto_name"]  = 'IVAO Vatican Staff';
$config["mail_replyto_email"] = 'va-staff@ivao.aero';

$config["mail_smtp_server"]   = 'divisions.ivao.aero';
$config["mail_smtp_port"]     = 465;
$config["mail_smtp_auth"]     = true;
$config["mail_smtp_secure"]   = 'ssl';
$config["mail_smtp_username"] = 'rfe@booking.va.ivao.aero';
$config["mail_smtp_password"] = 'ITSYOURPASSWORD';