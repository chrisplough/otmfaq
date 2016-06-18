<?php
// ++=========================================================================++
// || vBadvanced CMPS v3.2.1 (vB 3.6 - vB 3.8) - 63458
// || © 2003-2009 vBadvanced.com - All Rights Reserved
// || This file may not be redistributed in whole or significant part.
// || http://vbadvanced.com
// || Downloaded 23:34, Sun Jul 26th 2009
// || 2405244_563458070293
// ++ ========================================================================++

$navigationbits = '';

if (!empty($vbulletin->adv_portal_page))
{
	$navpages = array_keys($vbulletin->adv_portal_page);
}

if (!empty($mod_options['portal_navigation_excludepages']))
{
	$navpages = array_diff($navpages, $mod_options['portal_navigation_excludepages']);
}

($hook = vBulletinHook::fetch_hook('vba_cmps_module_navigation_start')) ? eval($hook) : false;

if (!empty($navpages))
{
	eval('$mod_options[\'portal_navigation_mark1\'] = "' . addslashes($mod_options['portal_navigation_mark1']) . '";');
	eval('$mod_options[\'portal_navigation_mark2\'] = "' . addslashes($mod_options['portal_navigation_mark2']) . '";');

	foreach ($navpages AS $npageid)
	{
		$npage =& $vbulletin->adv_portal_page[$npageid];

		if (!array_intersect($vbulletin->userinfo['usergrouparray'], $npage['userperms']))
		{
			continue;
		}

		if ($npage['level'] <= 1)
		{
			$navmark = $mod_options['portal_navigation_mark1'];
		}
		else
		{
			$navmark = str_repeat('&nbsp;', (intval($npage['level'] - 1))) . $mod_options['portal_navigation_mark2'];
		}

		$link = $vba_options['portal_homeurl'] . ($npage['name'] != 'home' ? '?' . $vbulletin->session->vars['sessionurl'] . $vba_options['portal_pagevar'] . '=' . $npage['name'] : '');
		$title = ($npageid == $pages['pageid']) ? '<strong>' . $npage['title'] . '</strong>' : $npage['title'];

		($hook = vBulletinHook::fetch_hook('vba_cmps_module_navigation_def_pagebits')) ? eval($hook) : false;

		eval('$navigationbits .= "' . fetch_template('adv_portal_navigationbits') . '";');
	}
}

// Additional pages
$customnavigationbits = '';

if (!empty($mod_options['portal_navigation_addpages']))
{
	foreach ($mod_options['portal_navigation_addpages'] AS $key => $navlinks)
	{
		$title = $navlinks['text'];
		eval('$link = "' . addslashes($navlinks['link']) . '";');

		if ($navlinks['level'] <= 1)
		{
			$navmark = $mod_options['portal_navigation_mark1'];
		}
		else
		{
			$navmark = str_repeat('&nbsp;', intval($navlinks['level'] - 1)) . $mod_options['portal_navigation_mark2'];
		}

		($hook = vBulletinHook::fetch_hook('vba_cmps_module_navigation_cus_pagebits')) ? eval($hook) : false;

		eval('$customnavigationbits .= "' . fetch_template('adv_portal_navigationbits') . '";');
	}
}

($hook = vBulletinHook::fetch_hook('vba_cmps_module_navigation_end')) ? eval($hook) : false;

eval('$home["$mods[modid]"][\'content\'] = "' . fetch_template('adv_portal_navigation') . '";');

?>