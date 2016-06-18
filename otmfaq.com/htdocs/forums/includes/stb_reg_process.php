<?php
/*======================================================================*\
|| #################################################################### ||
|| # Stop the Registration Bots Release 1.2.2						  # ||
|| # ---------------------------------------------------------------- # ||
|| # Copyright © 2005-2008 Greg Lynch. All Rights Reserved.           # ||
|| # noppid@lakecs.com http://www.cpurigs.com/						  # ||
|| #																  # ||
|| #################################################################### ||
\*======================================================================*/

if (!isset($GLOBALS['vbulletin']->db))
	exit;

	$vbulletin->input->clean_gpc('p', $vbulletin->options['StopBotReg_userpage_hash'], TYPE_STR);

	if( $vbulletin->GPC[$vbulletin->options[StopBotReg_userpage_hash]] == '')
		eval (standard_error( fetch_error('fieldmissing')));

	$formvalue = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "stopbotsregistry WHERE hash='".$db->escape_string($vbulletin->GPC[$vbulletin->options[StopBotReg_userpage_hash]])."'");

	$vbulletin->input->clean_gpc('p', $formvalue[uf_name], TYPE_STR );

	if (!$formvalue || $formvalue[uf_value] == '' || $vbulletin->GPC[$formvalue[uf_name]] != $formvalue[uf_value])
	{
		eval (standard_error( fetch_error('fieldmissing')));
	}

	$StopBotRegHash = $formvalue[hash];

	$userdata->pre_save();

	if (empty($userdata->errors))
	{
		$regtimestart = array();
		$regtimestart[regtime] = $formvalue[regtime];

		// sort time
		$regtimepast = intval(TIMENOW - $regtimestart[regtime]);

		//echo $regtimestart[regtime];

		// deny if no record or too fast.
		if( $regtimepast <= $vbulletin->options['StopBotRegMinTime'] || !$regtimestart[regtime])
		{
			$db->query_write("DELETE FROM " . TABLE_PREFIX . "stopbotsregistry WHERE hash='".$db->escape_string($StopBotRegHash)."' ");

			if($vbulletin->options[StopBotReg_enable_email])
			{
				$message = $vbphrase[stop_reg_bot_email_body] . " " . $vbulletin->GPC['username'] . " - " . $vbulletin->GPC['email'] . " " . $vbphrase[stop_reg_bot_email_time_desc] . $regtimepast . "";

				vbmail($vbulletin->options['webmasteremail'], $vbphrase[stop_reg_bot_email_subject] , $message, true);
			}
			eval(standard_error(fetch_error('noregister')));
		}

		// member is in, delete bot tracking record.
		$db->query_write("DELETE FROM " . TABLE_PREFIX . "stopbotsregistry WHERE hash='" . $db->escape_string($StopBotRegHash) . "' ");
	}

	// delete records over an hour old.
	$oldtime = intval(TIMENOW - 3600);
	$db->query_write("DELETE FROM " . TABLE_PREFIX . "stopbotsregistry WHERE regtime<='$oldtime' ");

	// if the email is a dupe or other errors, we will go back to register
	// setup vars passed.
	$_POST[$vbulletin->options[StopBotReg_rulespage_hash]] = $StopBotRegHash;
	$_POST[$formvalue[rf_name]] = $formvalue[rf_value];





?>