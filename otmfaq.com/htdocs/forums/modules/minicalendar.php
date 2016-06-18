<?php
// ++=========================================================================++
// || vBadvanced CMPS v3.2.1 (vB 3.6 - vB 3.8) - 63458
// || © 2003-2009 vBadvanced.com - All Rights Reserved
// || This file may not be redistributed in whole or significant part.
// || http://vbadvanced.com
// || Downloaded 23:34, Sun Jul 26th 2009
// || 2405244_563458070293
// ++ ========================================================================++

$vba_calid = intval($mod_options['portal_calendarid']);

if ($vba_calid)
{
	require_once(DIR . '/includes/functions_calendar.php');

	$vba_today = getdate(TIMENOW - $vbulletin->options['hourdiff']);
	$vba_today['month'] = $vbphrase[strtolower($vba_today['month'])];
	$vba_year = $vba_today['year'];
	$vba_month = $vba_today['mon'];

	// Simple calendar
	if ($vba_calid == -1)
	{
		$mods['link'] = '';
		$calendarinfo['showweekends'] = 1;
	}
	else
	{
		// Set the key as the calendar id and birthdays since that should be the only differences
		$calcache =& $vbulletin->adv_portal_cale[$vba_calid . '-' . $mod_options['portal_calendar_birthdays']];

		// No cache or time to update
		if ($calcache['month'] != $vba_month
				OR !$mod_options['portal_calendar_cache']
				OR $calcache['lastupdate'] < (TIMENOW - ($mod_options['portal_calendar_cache'] * 3600))
		)
		{
			// ##### Cache the calendar permissions
			$cpermcache = array();
			$getcperms = $db->query_read("
				SELECT usergroupid, calendarpermissions
				FROM " . TABLE_PREFIX . "calendarpermission
				WHERE calendarid = $vba_calid
			");
			while ($cperm = $db->fetch_array($getcperms))
			{
				$cpermcache[$cperm['usergroupid']] = $cperm['calendarpermissions'];
			}

			$calendarinfo = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "calendar WHERE calendarid = $vba_calid");

			// ##### Get the events
			if ($calendarinfo)
			{
				$calendarinfo = array_merge($calendarinfo, convert_bits_to_array($calendarinfo['options'], $_CALENDAROPTIONS));

				if ($calendarinfo['holidays'])
				{
					$calendarinfo = array_merge($calendarinfo, convert_bits_to_array($calendarinfo['holidays'], $_CALENDARHOLIDAYS));
				}

				$range = array(
					'frommonth' => $vba_month,
					'fromyear' => $vba_year,
					'nextmonth' => $vba_month,
					'nextyear' => $vba_year
				);

				// Fake the permissions while we're caching so we get all events, then we'll check them below
				$vbulletin->userinfo['calendarpermissions'][$vba_calid] = $vbulletin->bf_ugp_calendarpermissions['canviewothersevent'];

				$eventcache = cache_events($range);
			}

			// ##### Add birthdays to the event cache
			if ($mod_options['portal_calendar_birthdays'])
			{
				$doublemonth = ($vba_month < 10) ? '0' . $vba_month : $vba_month;

				$birthdaycache = cache_birthdays();

				if (!empty($birthdaycache))
				{
					foreach ($birthdaycache[$vba_month] AS $vba_birth_day => $vba_births)
					{

						foreach ($vba_births AS $birthday_key => $vba_birth_user)
						{
							$from_date = gmmktime(0, 0, 0, $vba_month, $vba_birth_day, $vba_year);
							$eventcache['singleday'][$from_date][] = array(
								'title' => construct_phrase($vbphrase['x_birthday'], $vba_birth_user['username'])
							);
						}
					}
				}
			}

			if ($mod_options['portal_calendar_cache'])
			{
				$calcache[$vba_calid . '-' . $mod_options['portal_calendar_birthdays']] = array(
					'lastupdate' => TIMENOW,
					'month' => $vba_month,
					'eventcache' => $eventcache,
					'calendarinfo' => $calendarinfo,
					'cpermcache' => $cpermcache
				);

				build_datastore('adv_portal_cale', serialize($calcache), 1);
			}
		}
		else
		{
			$eventcache = $calcache['eventcache'];
			$calendarinfo = $calcache['calendarinfo'];
			$cpermcache = $calcache['cpermcache'];
		}
	}

	// Format the permissions
	foreach ($vbulletin->userinfo['usergrouparray'] AS $ugid)
	{
		if (isset($cpermcache[$ugid]))
		{
			$vbulletin->userinfo['calendarpermissions'][$vba_calid] |= $cpermcache[$ugid];
		}
		else
		{
			$vbulletin->userinfo['calendarpermissions'][$vba_calid] |= $vbulletin->usergroupcache[$ugid]['calendarpermissions'];
		}
	}

	// Make sure the user has permission to view events
	if (!($vbulletin->userinfo['calendarpermissions'][$vba_calid] & $vbulletin->bf_ugp_calendarpermissions['canviewothersevent']))
	{
		unset($eventcache['recurring'], $eventcache['singleday'], $eventcache['ranged']);
	}

	if ($vbulletin->userinfo['startofweek'] > 7 OR $vbulletin->userinfo['startofweek'] < 1)
	{
		$vbulletin->userinfo['startofweek'] = ($calendarinfo['startofweek']) ? $calendarinfo['startofweek'] : 1;
	}

	$usertoday = array(
		'firstday' => gmdate('w', gmmktime(0, 0, 0, $vba_month, 1, $vba_year)),
		'month' => $vba_month,
		'year' => $vba_year
	);

	($hook = vBulletinHook::fetch_hook('vba_cmps_module_minicalendar')) ? eval($hook) : false;

	// Template replacements
	$vbulletin->templatecache['calendar_smallmonth_header'] = $vbulletin->templatecache['adv_portal_calendar_header'];
	$vbulletin->templatecache['calendar_smallmonth_week'] = $vbulletin->templatecache['adv_portal_calendar_week'];
	$vbulletin->templatecache['calendar_smallmonth_day'] = $vbulletin->templatecache['adv_portal_calendar_day'];
	$vbulletin->templatecache['calendar_smallmonth_day_other'] = $vbulletin->templatecache['adv_portal_calendar_day_other'];

	$home[$mods['modid']]['content'] = construct_calendar_output($vba_today, $usertoday, $calendarinfo, 0, '');

	if ($stylevar['cellpadding'] > 4)
	{
		$stylevar['oldcellpadding'] = $stylevar['cellpadding'];
		$stylevar['cellpadding'] = 4;
	}

	$mods['title'] = $vbphrase[$months[$vba_month]] . ' ' . $vba_year;

	unset($calendarinfo, $eventcache, $birthdaycache, $usertoday, $range);
}

?>