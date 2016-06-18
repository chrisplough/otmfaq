<?php
// ++=========================================================================++
// || vBadvanced CMPS v3.2.1 (vB 3.6 - vB 3.8) - 63458
// || © 2003-2009 vBadvanced.com - All Rights Reserved
// || This file may not be redistributed in whole or significant part.
// || http://vbadvanced.com
// || Downloaded 23:34, Sun Jul 26th 2009
// || 2405244_563458070293
// ++ ========================================================================++

if (!$onlineprocesssed)
{
	/* Since this file is used for 2 modules, figure out which
		 are active so we can process both at the same time
		 Be sure not to use references here!
	*/
	foreach ($modules AS $omodid)
	{
		$omods =& $vbulletin->adv_modules[$omodid];

		if ($omods['identifier'] == 'buddylist')
		{
			$buddymod = $omods;
		}
		else if ($omods['identifier'] == 'onlineusers')
		{
			$onlinemod = $omods;
		}
	}

	$loggedinusers = array();
	$activeusers = '';
	$invisiblecount = 0;

	$vbulletin->userinfo['buddylist'] = trim($vbulletin->userinfo['buddylist']);

	$dobuddylist = ($buddymod['modid'] AND $vbulletin->userinfo['userid'] AND $vbulletin->userinfo['buddylist']) ? true : false;

	if ($onlinemod['modid'] OR $dobuddylist)
	{
		// Logged in user
		if ($onlinemod['modid'] AND $vbulletin->userinfo['userid'])
		{
			$loggedinusers[$vbulletin->userinfo['userid']] = array(
				'userid' => $vbulletin->userinfo['userid'],
				'username' => $vbulletin->userinfo['username'],
				'invisiblemark' => ($vbulletin->userinfo['invisible']) ? '*' : '',
				'displaygroupid' => $vbulletin->userinfo['displaygroupid'],
				'musername' => fetch_musername($vbulletin->userinfo)
			);
		}

		// Limit only to buddies if that's what we're working with
		$buddywhere = '';
		if ($dobuddylist OR $vbulletin->userinfo['buddylist'])
		{
			$vbulletin->userinfo['buddylist_array'] = explode(' ', $vbulletin->userinfo['buddylist']);

			if (!$onlinemod['modid'])
			{
				$buddywhere = 'AND session.userid IN(' . implode(', ', $vbulletin->userinfo['buddylist_array']) . ')';
			}
		}

		$getonline = $db->query_read("
			SELECT session.userid, username, usergroupid, (user.options & " . $vbulletin->bf_misc_useroptions['invisible'] . ") AS invisible, IF(displaygroupid=0, user.usergroupid, displaygroupid) AS displaygroupid
			FROM " . TABLE_PREFIX . "session AS session
			LEFT JOIN " . TABLE_PREFIX . "user AS user USING (userid)
			WHERE session.lastactivity > " . (TIMENOW - $vbulletin->options['cookietimeout']) . "
				$buddywhere
			ORDER BY username ASC
		");

		while ($onlineusers = $db->fetch_array($getonline))
		{
			if (!$onlineusers['userid'])
			{
				$numberguest++;
			}
			else
			{
				if ($onlineusers['invisible'])
				{
					if (($vbulletin->userinfo['permissions']['genericpermissions'] & $vbulletin->bf_ugp_genericpermissions['canseehidden']) OR $onlineusers['userid'] == $vbulletin->userinfo['userid'])
					{
						$onlineusers['invisiblemark'] = '*';
					}
					else
					{
						$invisiblecount++;
						continue;
					}
				}

				$loggedinusers[$onlineusers['userid']] = $onlineusers;
			}
		}

		$db->free_result($getonline);
		unset($onlineusers);

		// ##### Process Buddy List Users
		if ($vbulletin->userinfo['buddylist'])
		{
			$buddies_sorted = array_intersect(array_keys($loggedinusers), $vbulletin->userinfo['buddylist_array']);

			if (!empty($buddies_sorted))
			{
				foreach ($buddies_sorted AS $buddyid)
				{
					if ($loggedinusers[$buddyid])
					{
						$loggedin =& $loggedinusers[$buddyid];
						$loggedin['buddymark'] = '+';
						if ($dobuddylist)
						{
							($hook = vBulletinHook::fetch_hook('vba_cmps_module_buddylistbits')) ? eval($hook) : false;
	
							eval('$buddylistbits .= "' . fetch_template('adv_portal_buddylistbits') . '";');
						}
					}
				}
			}
		}
		unset($loggedin);
	}

	// ##### Process Buddy List Module
	if ($buddymod['modid'] AND $vbulletin->userinfo['userid'])
	{
		$mods['collapse'] = $buddymod['modid'];
		$mods['title'] = $buddymod['title'];
		$mods['modid'] = $buddymod['modid'];
		$modcollapse = $vbcollapse['collapseobj_module_' . $buddymod['modid']];
		$modimgcollapse = $vbcollapse['collapseimg_module_' . $buddymod['modid']];
		$show['tablerow'] = true;

		if ($buddymod['link'])
		{
			eval('$mods[\'link\'] = "' . addslashes($buddymod['link']) . '";');
		}

		if (!$buddylistbits)
		{
			$buddylistbits = construct_phrase($vbphrase['no_x_online'], $vbphrase['buddies']);
		}
		if ($buddymod['useshell'])
		{
			$modulehtml =& $buddylistbits;

			$show['tablerow'] = true;
			eval('$home[$buddymod[\'modid\']][\'content\'] = "' . fetch_template(($buddymod['altshell'] ? $buddymod['altshell'] : 'adv_portal_module_wrapper')) . '";');
		}
		else
		{
			$home[$buddymod['modid']]['content'] = $buddylistbits;
		}
	}

	// ##### Process Online Users Module
	if ($onlinemod['modid'])
	{
		$comma = '';
		$numberregistered = sizeof($loggedinusers);
		$show['comma_leader'] = false;

		if (!empty($loggedinusers))
		{
			vba_template_alter('forumhome_loggedinuser',
				'"member.php',
				'"' . $vbulletin->options['bburl'] . '/member.php'
			);

			foreach ($loggedinusers AS $loggedinuserid => $loggedin)
			{
				$loggedin['musername'] = fetch_musername($loggedin);

				($hook = vBulletinHook::fetch_hook('vba_cmps_module_onlineuserbits')) ? eval($hook) : false;

				eval('$activeusers .= "$comma ' . fetch_template('forumhome_loggedinuser') . '";');
				$comma = ', ';
			}
		}

		if (!$activeusers)
		{
			$activeusers = construct_phrase($vbphrase['no_x_online'], $vbphrase['members']);
		}

		// Process the total first, before number_format is applied
		$totalonline = $numberregistered + $numberguest + $invisiblecount;

		if ($vbulletin->maxloggedin['maxonline'] <= $totalonline)
		{
			$vbulletin->maxloggedin['maxonline'] = $totalonline;
			$vbulletin->maxloggedin['maxonlinedate'] = TIMENOW;
			build_datastore('maxloggedin', serialize($vbulletin->maxloggedin), 1);
		}

		$totalonline = vb_number_format($totalonline);
		$numberregistered = vb_number_format($numberregistered + $invisiblecount);
		$numberguest = vb_number_format($numberguest);

		$recordusers = vb_number_format($vbulletin->maxloggedin['maxonline']);
		$recorddate = vbdate($vbulletin->options['dateformat'], $vbulletin->maxloggedin['maxonlinedate'], 1);
		$recordtime = vbdate($vbulletin->options['timeformat'], $vbulletin->maxloggedin['maxonlinedate']);

		// Sort out the module
		$mods['title'] = $onlinemod['title'] . ': ' . $totalonline;
		$mods['modid'] = $onlinemod['modid'];
		$mods['collapse'] = $onlinemod['modid'];
		$modcollapse = $vbcollapse['collapseobj_module_' . $onlinemod['modid']];
		$modimgcollapse = $vbcollapse['collapseimg_module_' . $onlinemod['modid']];

		if ($onlinemod['link'])
		{
			eval('$mods[\'link\'] = "' . addslashes($onlinemod['link']) . '";');
		}

		eval('$modulehtml = "' . fetch_template('adv_portal_onlineusers') . '";');

		if ($onlinemod['useshell'] & $vba_shellint['enable'])
		{
			if ($mods['useshell'] & $vba_shellint['collapse'])
			{
				$modcollapse = 'display: none';
				$modimgcollapse = '_collapsed';
			}
			else
			{
				$modcollapse = $vbcollapse['collapseobj_module_' . $mods['modid']];
				$modimgcollapse = $vbcollapse['collapseimg_module_' . $mods['collapse']];
			}

			$show['tablerow'] = false;
			eval('$home[$onlinemod[\'modid\']][\'content\'] = "' . fetch_template(($onlinemod['altshell'] ? $onlinemod['altshell'] : 'adv_portal_module_wrapper')) . '";');
		}
		else
		{
			$home[$onlinemod['modid']]['content'] = $modulehtml;
		}
	}

	unset($loggedinusers, $activeusers, $buddylistbits);
	
	// reset mods to the right one
	$mods = ($mods['identifier'] == 'buddylist') ? $buddymod : $onlinemod;
}

$onlineprocesssed = true;
$mods['noshell'] = true;

?>