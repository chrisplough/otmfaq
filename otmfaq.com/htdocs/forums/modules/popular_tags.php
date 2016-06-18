<?php
// ++=========================================================================++
// || vBadvanced CMPS v3.2.1 (vB 3.6 - vB 3.8) - 63458
// || © 2003-2009 vBadvanced.com - All Rights Reserved
// || This file may not be redistributed in whole or significant part.
// || http://vbadvanced.com
// || Downloaded 23:34, Sun Jul 26th 2009
// || 2405244_563458070293
// ++ ========================================================================++

$show['tablerow'] = true;

if (!IS_VB_37)
{
	if ($vbulletin->userinfo['permissions']['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['cancontrolpanel'])
	{
		$home[$mods['modid']]['content'] = $vbphrase['sorry_only_vb_37'];
	}
}
else
{
	if (!is_array($vbulletin->tagcloud) OR $vbulletin->tagcloud['dateline'] <= (TIMENOW - ($vbulletin->options['tagcloud_cachetime'] * 60)))
	{
		require_once(DIR . '/includes/functions_search.php');

		$vbulletin->options['tagcloud_usergroup'] = 0;

		fetch_tagcloud('usage');
	}

	($hook = vBulletinHook::fetch_hook('vba_cmps_module_tags')) ? eval($hook) : false;

	if (THIS_SCRIPT == 'adv_index')
	{
		eval('$headinclude .= "' . fetch_template('tag_cloud_headinclude') . '";');
	}
	// can't add anything to $headinclude when integrated
	else
	{
		$mods['formcode'] = stripslashes(fetch_template('tag_cloud_headinclude'));
	}

	// vB 3.7 Beta 6+
	if (is_array($vbulletin->tagcloud['tags']))
	{
		vba_template_alter('tag_cloud_link',
			'"tags.php',
			'"' . $vbulletin->options['bburl'] . '/tags.php'
		);
	
		foreach ($vbulletin->tagcloud['tags'] AS $thistag)
		{
			eval('$home[$mods[\'modid\']][\'content\'] .= "' . fetch_template('tag_cloud_link') . '";');
		}
	}
	// vB 3.7 Beta 5 or prior
	else if ($vbulletin->tagcloud['links'])
	{
		$home[$mods['modid']]['content'] .= str_replace(
			'"tags.php',
			'"' . $vbulletin->options['bburl'] . '/tags.php',
			$vbulletin->tagcloud['links']
		);
	}
}

?>