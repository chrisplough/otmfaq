<?php
/*======================================================================*\
|| #################################################################### ||
|| # Stop the Registration Bots Release 1.2.2						  # ||
|| # ---------------------------------------------------------------- # ||
|| # Copyright © 2005-2009 Greg Lynch. All Rights Reserved.           # ||
|| # noppid@lakecs.com http://www.cpurigs.com/						  # ||
|| #																  # ||
|| #################################################################### ||
\*======================================================================*/

if (!isset($GLOBALS['vbulletin']->db))
	exit;

	$usersalt = random_alpha_string(12);
	// Rules form
	// DB marker for user
	$StopBotRegHash = md5( $usersalt . utime_string() );

	// Random hidden with random name and value
	$Rules_form_random_name = random_alpha_string(8);
	$Rules_form_random_value = random_alpha_string(8);

	//add record to DB
	$db->query_write("REPLACE INTO " . TABLE_PREFIX . "stopbotsregistry (hash,rf_name,rf_value)
	values(
	'" . $db->escape_string($StopBotRegHash) . "',
	'" . $db->escape_string($Rules_form_random_name) . "',
	'" . $db->escape_string($Rules_form_random_value) . "'
	) ");
?>