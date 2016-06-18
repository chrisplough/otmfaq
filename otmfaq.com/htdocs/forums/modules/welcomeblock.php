<?php
// ++=========================================================================++
// || vBadvanced CMPS v3.2.1 (vB 3.6 - vB 3.8) - 63458
// || © 2003-2009 vBadvanced.com - All Rights Reserved
// || This file may not be redistributed in whole or significant part.
// || http://vbadvanced.com
// || Downloaded 23:34, Sun Jul 26th 2009
// || 2405244_563458070293
// ++ ========================================================================++

if (!$show['guest'])
{
	$mods['title'] = $vbphrase['user_cp'];
	$mods['formcode'] = '';

	// Avatar
	if ($mod_options['portal_welcome_avatar']
		AND $vbulletin->userinfo['showavatars']
		AND ($vbulletin->userinfo['avatarid']
			OR ($vbulletin->userinfo['permissions']['genericpermissions'] & $vbulletin->bf_ugp_genericpermissions['canuseavatar'])
			OR $vbulletin->userinfo['adminavatar']
		)
	)
	{
		require_once(DIR . '/includes/functions_user.php');
		$avatarurl = fetch_avatar_url($vbulletin->userinfo['userid']);

		if ($avatarurl[0])
		{
			$avatarurl = $vbulletin->options['bburl'] . '/' . $avatarurl[0];
		}
		else if ($mod_options['portal_noavatarurl'])
		{
			eval('$mod_options[\'portal_noavatarurl\'] = "' . addslashes($mod_options['portal_noavatarurl']) . '";');
			$avatarurl = $mod_options['portal_noavatarurl'];
		}
	}
	else if ($vbulletin->options['avatarenabled'])
	{
		eval('$mod_options[\'portal_noavatarurl\'] = "' . addslashes($mod_options['portal_noavatarurl']) . '";');
		$avatarurl = $mod_options['portal_noavatarurl'];
	}

	$lastvisitdate = vbdate($mod_options['portal_welcome_lastvisit_date'], $vbulletin->userinfo['lastvisit'], '', '');
	$lastvisittime = vbdate($mod_options['portal_welcome_lastvisit_time'], $vbulletin->userinfo['lastvisit'], '', '');

	// Reputation
	if ($mod_options['portal_welcome_rep'])
	{
		require_once(DIR . '/includes/functions_reputation.php');
		fetch_reputation_image($vbulletin->userinfo, $permissions);
		$reppower = vb_number_format(fetch_reppower($vbulletin->userinfo, $permissions));
	}

	if ($mod_options['portal_welcome_rank'])
	{
		$vbulletin->userinfo['rank'] = str_replace('src="', 'src="' . $vbulletin->options['bburl'] . '/', $vbulletin->userinfo['rank']);
	}

	// New posts
	if ($mod_options['portal_welcome_newposts'])
	{
		if (strlen($vbulletin->session->vars['newposts']) > 0 AND !$vbulletin->options['threadmarking'])
		{
			$newposts = number_format($vbulletin->session->vars['newposts']);
		}
		else
		{
			$getnewposts = $db->query_first("
				SELECT COUNT(*) AS count
				FROM " . TABLE_PREFIX . "post AS post
				" . iif($vbulletin->options['threadmarking'],
					'LEFT JOIN ' . TABLE_PREFIX . 'threadread AS threadread ON (threadread.threadid = post.threadid AND threadread.userid = ' . $vbulletin->userinfo['userid'] . ')') . "
				WHERE dateline >= " . $vbulletin->userinfo['lastvisit'] .
					iif($vbulletin->options['threadmarking'],
						' AND dateline > IF(threadread.readtime IS NULL, ' . (TIMENOW - ($vbulletin->options['markinglimit'] * 86400)) . ', threadread.readtime)') . "
					AND visible = 1
			");

			if (!$vbulletin->options['threadmarking'])
			{
				$db->query_write("UPDATE " . TABLE_PREFIX . "session SET newposts = '$getnewposts[count]' WHERE userid = " . $vbulletin->userinfo['userid']);
			}

			$newposts = vb_number_format($getnewposts['count']);
		}
	}

	$vbulletin->userinfo['pmtotal'] = vb_number_format($vbulletin->userinfo['pmtotal']);
	$permissions['pmquota'] = vb_number_format($permissions['pmquota']);
	$vbulletin->userinfo['pmunread'] = vb_number_format($vbulletin->userinfo['pmunread']);
}
else
{
	$mods['title'] = $vbphrase['log_in'];
	$mods['link'] = '';
}

$welcome_tempname = ($mod_options['portal_welcome_expanded'] == 1 OR ($mod_options['portal_welcome_expanded'] == 2 AND $vba_colwidths[$vba_modcols[$modcol]] >= 300)) ? 'expanded' : 'lean';

($hook = vBulletinHook::fetch_hook('vba_cmps_module_welcomeblock')) ? eval($hook) : false;

eval('$home["$mods[modid]"][\'content\'] = "' . fetch_template('adv_portal_welcomeblock_' . $welcome_tempname) . '";');

?>