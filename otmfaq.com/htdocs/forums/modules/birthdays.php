<?php
// ++=========================================================================++
// || vBadvanced CMPS v3.2.1 (vB 3.6 - vB 3.8) - 63458
// || © 2003-2009 vBadvanced.com - All Rights Reserved
// || This file may not be redistributed in whole or significant part.
// || http://vbadvanced.com
// || Downloaded 23:34, Sun Jul 26th 2009
// || 2405244_563458070293
// ++ ========================================================================++

$tdate = vbdate('Y-m-d', TIMENOW, false, false);

if (!is_array($vbulletin->birthdaycache) OR ($tdate != $vbulletin->birthdaycache['day1'] AND $tdate != $vbulletin->birthdaycache['day2']))
{
	include_once('./includes/functions_databuild.php');
	$vbulletin->birthdaycache = build_birthdays();
}
switch($tdate)
{
	case $vbulletin->birthdaycache['day1']:
		$birthdays =& $vbulletin->birthdaycache['users1'];
	break;

	case $vbulletin->birthdaycache['day2'];
		$birthdays =& $vbulletin->birthdaycache['users2'];
	break;
}
$show['tablerow'] = true;

$birthdays_str = '';

// Birthdays change from a string to array in vB 3.7 RC3
if (is_array($birthdays))
{
	if (empty($birthdays))
	{
		$birthdays_str = $vbphrase['none'];
	}
	else
	{
		foreach ($birthdays AS $birthkey => $birthinfo)
		{
			$birthdays_str .= '<a href="' . $vbulletin->options['bburl'] . '/member.php?' . $vbulletin->session->vars['sessionurl'] . 'u=' . $birthinfo['userid'] . '">' . $birthinfo['username'] . '</a>' . iif($birthinfo['age'], ' (' . $birthinfo['age'] . ')') . ($mod_options['portal_birth_newline'] ? '<br />' : ', ');
		}

		if (!$mod_options['portal_birth_newline'] AND sizeof($birthdays) > 1)
		{
			$birthdays_str = substr($birthdays_str, 0, strlen($birthdays_str) - 2);
		}
	}
}
else if ($birthdays)
{
	$birth_find = array('member.php');
	$birth_replace = array($vbulletin->options['bburl'] . '/member.php');

	if ($mod_options['portal_birth_newline'])
	{
		$birth_find[] = ',';
		$birth_replace[] = '<br />';
	}
	
	$birthdays_str = str_replace($birth_find, $birth_replace, $birthdays);
}
else
{
	$birthdays_str = $vbphrase['none'];
}

($hook = vBulletinHook::fetch_hook('vba_cmps_module_birthdays')) ? eval($hook) : false;

$home[$mods['modid']]['content'] = $birthdays_str;

?>