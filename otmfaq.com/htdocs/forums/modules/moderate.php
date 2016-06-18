<?php
// ++=========================================================================++
// || vBadvanced CMPS v3.2.1 (vB 3.6 - vB 3.8) - 63458
// || © 2003-2009 vBadvanced.com - All Rights Reserved
// || This file may not be redistributed in whole or significant part.
// || http://vbadvanced.com
// || Downloaded 23:34, Sun Jul 26th 2009
// || 2405244_563458070293
// ++ ========================================================================++

require_once(DIR . '/includes/functions_calendar.php');

$admincpdir =& $vbulletin->config['Misc']['admincpdir'];
$modcpdir =& $vbulletin->config['Misc']['modcpdir'];

if (can_moderate(0, 'canmoderateattachments'))
{
	$attachments = $db->query_first("SELECT COUNT(*) AS count FROM " . TABLE_PREFIX . "attachment WHERE visible = 0");
	$attachments['count'] = vb_number_format($attachments['count']);
	$show['attachments'] = true;
}

if (can_moderate() AND can_moderate_calendar())
{
	$events = $db->query_first("SELECT COUNT(*) AS count FROM " . TABLE_PREFIX . "event WHERE visible = 0");
	$events['count'] = vb_number_format($events['count']);
	$show['events'] = true;
}

$users = $db->query_first("SELECT COUNT(*) AS count FROM " . TABLE_PREFIX . "user WHERE usergroupid = 4");
$users['count'] = vb_number_format($users['count']);

if (can_moderate(0, 'canmoderateposts'))
{
	$show['posts'] = true;
	$show['threads'] = true;

	if (can_moderate(0, 'canmoderatevisitormessages') AND IS_VB_37)
	{
		$show['visitormessage'] = true;
	}

	$moditems = array();

	$getmoderation = $db->query_read("SELECT type FROM " . TABLE_PREFIX . "moderation WHERE type != 'groupmessage'");
	while ($moderation = $db->fetch_array($getmoderation))
	{
		$moditems[$moderation['type']]++;
	}

	$moditems['reply'] = vb_number_format($moditems['reply']);
	$moditems['thread'] = vb_number_format($moditems['thread']);
	$moditems['visitormessage'] = vb_number_format($moditems['visitormessage']);
}

($hook = vBulletinHook::fetch_hook('vba_cmps_module_moderate')) ? eval($hook) : false;

eval('$home["$mods[modid]"][\'content\'] = "' . fetch_template('adv_portal_moderation') . '";');

?>