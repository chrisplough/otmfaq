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

	// grab the hash and get field names.
	$vbulletin->input->clean_gpc('p', $vbulletin->options[StopBotReg_rulespage_hash], TYPE_STR);

	if( $vbulletin->GPC[$vbulletin->options[StopBotReg_rulespage_hash]] == '')
		eval (standard_error( fetch_error('fieldmissing')));

	$formvalue = $db->query_first("SELECT hash,rf_name,rf_value FROM " . TABLE_PREFIX . "stopbotsregistry WHERE hash='".$db->escape_string($vbulletin->GPC[$vbulletin->options[StopBotReg_rulespage_hash]])."'");

	$vbulletin->input->clean_gpc('p', $formvalue[rf_name], TYPE_STR);

	// check the fields
	if( !$formvalue || $formvalue[rf_value] == '' || $vbulletin->GPC[$formvalue[rf_name]] != $formvalue[rf_value])
		eval (standard_error( fetch_error('fieldmissing')));

	$StopBotRegHash = $formvalue[hash];

	// gen user form randoms
	// Random hidden with random name and value
	$User_form_random_name = random_alpha_string(8);
	$User_form_random_value = random_alpha_string(8);

	//add record to DB
	$db->query_write("REPLACE INTO " . TABLE_PREFIX . "stopbotsregistry (hash,rf_name,rf_value,uf_name,uf_value,regtime)
	values(
	'" . $db->escape_string($StopBotRegHash) . "',
	'" . $db->escape_string($formvalue[rf_name] ) . "',
	'" . $db->escape_string($formvalue[rf_value] ) . "',
	'" . $db->escape_string($User_form_random_name ) . "',
	'" . $db->escape_string($User_form_random_value ) . "',
	'" . TIMENOW . "'
	) ");

	// delete records over an hour old.
	$oldtime = intval(TIMENOW - 3600);
	$db->query_write("DELETE FROM " . TABLE_PREFIX . "stopbotsregistry WHERE regtime<='$oldtime' ");



?>