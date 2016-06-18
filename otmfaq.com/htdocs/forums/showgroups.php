<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.1.1 Patch Level 1 - Licence Number VBS309B87F
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2000-2011 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/

// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'showgroups');
define('CSRF_PROTECTION', true);

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array();

// get special data templates from the datastore
$specialtemplates = array();

// pre-cache templates used by all actions
$globaltemplates = array(
	'SHOWGROUPS',
	'showgroups_forumbit',
	'showgroups_usergroup',
	'showgroups_usergroupbit',
	'postbit_onlinestatus'
);

// pre-cache templates used by specific actions
$actiontemplates = array();

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/includes/functions_bigthree.php');
require_once(DIR . '/includes/functions_user.php');

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

if (!$vbulletin->options['forumleaders'])
{
	print_no_permission();
}

// 2 is the default location field and the one we always use in the template
$show['locationfield'] = $db->query_first("
	SELECT profilefieldid
	FROM " . TABLE_PREFIX . "profilefield
	WHERE profilefieldid = 2
");

$show['contactinfo'] = (bool)$vbulletin->userinfo['userid'];

function process_showgroups_userinfo($user)
{
	global $vbulletin, $permissions, $show;

	$user = array_merge($user, convert_bits_to_array($user['options'], $vbulletin->bf_misc_useroptions));
	$user = array_merge($user, convert_bits_to_array($user['adminoptions'], $vbulletin->bf_misc_adminoptions));
	cache_permissions($user, false);

	fetch_online_status($user);

	if ((!$user['invisible'] OR $permissions['genericpermissions'] & $vbulletin->bf_ugp_genericpermissions['canseehidden']))
	{
		$user['lastonline'] = vbdate($vbulletin->options['dateformat'], $user['lastactivity'], 1);
	}
	else
	{
		$user['lastonline'] = '&nbsp;';
	}

	fetch_musername($user);
	fetch_avatar_from_userinfo($user, true);

	return $user;
}

if (!($permissions & $vbulletin->bf_ugp_forumpermissions['canview']))
{
	print_no_permission();
}

($hook = vBulletinHook::fetch_hook('showgroups_start')) ? eval($hook) : false;

// get usergroups who should be displayed on showgroups
// Scans too many rows. Usergroup Rows * User Rows
$users = $db->query_read_slave("
	SELECT user.*,
		usergroup.usergroupid, usergroup.title,
		user.options, usertextfield.buddylist,
		" . ($show['locationfield'] ? 'userfield.field2,' : '') . "
		IF(user.displaygroupid = 0, user.usergroupid, user.displaygroupid) AS displaygroupid
		" . ($vbulletin->options['avatarenabled'] ? ",avatar.avatarpath, NOT ISNULL(customavatar.userid) AS hascustomavatar, customavatar.dateline AS avatardateline,customavatar.width AS avwidth,customavatar.height AS avheight, customavatar.width_thumb AS avwidth_thumb, customavatar.height_thumb AS avheight_thumb, filedata_thumb, NOT ISNULL(customavatar.userid) AS hascustom" : "") . "
	FROM " . TABLE_PREFIX . "user AS user
	LEFT JOIN " . TABLE_PREFIX . "usergroup AS usergroup ON(usergroup.usergroupid = user.usergroupid OR FIND_IN_SET(usergroup.usergroupid, user.membergroupids))
	LEFT JOIN " . TABLE_PREFIX . "userfield AS userfield ON(userfield.userid = user.userid)
	LEFT JOIN " . TABLE_PREFIX . "usertextfield AS usertextfield ON(usertextfield.userid=user.userid)
	" . ($vbulletin->options['avatarenabled'] ? "LEFT JOIN " . TABLE_PREFIX . "avatar AS avatar ON(avatar.avatarid = user.avatarid) LEFT JOIN " . TABLE_PREFIX . "customavatar AS customavatar ON(customavatar.userid = user.userid)" : "") . "
	WHERE (usergroup.genericoptions & " . $vbulletin->bf_ugp_genericoptions['showgroup'] . ")
");

$groupcache = array();
while ($user = $db->fetch_array($users))
{
	$t = strtoupper($user['title']);
	$u = strtoupper($user['username']);
	$groupcache["$t"]["$u"] = $user;
}

$usergroups = '';
if (sizeof($groupcache) >= 1)
{
	ksort($groupcache); // alphabetically sort usergroups
	foreach ($groupcache AS $users)
	{
		ksort($users); // alphabetically sort users
		$usergroupbits = '';
		foreach ($users AS $user)
		{
			exec_switch_bg();
			$user = process_showgroups_userinfo($user);

			if ($vbulletin->options['enablepms'] AND $vbulletin->userinfo['permissions']['pmquota'] AND ($vbulletin->userinfo['permissions']['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['cancontrolpanel']
	 				OR ($user['receivepm'] AND $user['permissions']['pmquota']
	 				AND (!$user['receivepmbuddies'] OR can_moderate() OR strpos(" $user[buddylist] ", ' ' . $vbulletin->userinfo['userid'] . ' ') !== false))
	 		))
			{
				$show['pmlink'] = true;
			}
			else
			{
				$show['pmlink'] = false;
			}

			if ($user['showemail'] AND $vbulletin->options['displayemails'] AND (!$vbulletin->options['secureemail'] OR ($vbulletin->options['secureemail'] AND $vbulletin->options['enableemail'])) AND $vbulletin->userinfo['permissions']['genericpermissions'] & $vbulletin->bf_ugp_genericpermissions['canemailmember'] AND $vbulletin->userinfo['userid'])
			{
				$show['emaillink'] = true;
			}
			else
			{
				$show['emaillink'] = false;
			}

			($hook = vBulletinHook::fetch_hook('showgroups_user')) ? eval($hook) : false;
			$templater = vB_Template::create('showgroups_usergroupbit');
				$templater->register('bgclass', $bgclass);
				$templater->register('showforums', $showforums);
				$templater->register('user', $user);
				$templater->register('xhtml_id', ++$xhtmlid2);
			$usergroupbits .= $templater->render();
		}

		($hook = vBulletinHook::fetch_hook('showgroups_usergroup')) ? eval($hook) : false;
		$templater = vB_Template::create('showgroups_usergroup');
			$templater->register('user', $user);
			$templater->register('usergroupbits', $usergroupbits);
			$templater->register('xhtml_id', ++$xhtmlid);
		$usergroups .= $templater->render();
	}
}

unset($groupcache);

if ($vbulletin->options['forumleaders'] == 1)
{
	// get moderators **********************************************************
	$moderators = $db->query_read_slave("
		SELECT user.*,
			moderator.forumid,
			usertextfield.buddylist,
			" . ($show['locationfield'] ? 'userfield.field2,' : '') . "
			IF(user.displaygroupid = 0, user.usergroupid, user.displaygroupid) AS displaygroupid
			" . ($vbulletin->options['avatarenabled'] ? ",avatar.avatarpath, NOT ISNULL(customavatar.userid) AS hascustomavatar, customavatar.dateline AS avatardateline,customavatar.width AS avwidth,customavatar.height AS avheight, customavatar.width_thumb AS avwidth_thumb, customavatar.height_thumb AS avheight_thumb, filedata_thumb, NOT ISNULL(customavatar.userid) AS hascustom" : "") . "
		FROM " . TABLE_PREFIX . "moderator AS moderator
		INNER JOIN " . TABLE_PREFIX . "user AS user USING(userid)
		INNER JOIN " . TABLE_PREFIX . "userfield AS userfield ON(userfield.userid = user.userid)
		INNER JOIN " . TABLE_PREFIX . "usertextfield AS usertextfield ON(usertextfield.userid=user.userid)
		" . ($vbulletin->options['avatarenabled'] ? "LEFT JOIN " . TABLE_PREFIX . "avatar AS avatar ON(avatar.avatarid = user.avatarid) LEFT JOIN " . TABLE_PREFIX . "customavatar AS customavatar ON(customavatar.userid = user.userid)" : "") . "
		WHERE moderator.forumid <> -1
	");
	$modcache = array();
	while ($moderator = $db->fetch_array($moderators))
	{
		if (!isset($modcache["$moderator[username]"]))
		{
			$modcache["$moderator[username]"] = $moderator;
		}
		$modcache["$moderator[username]"]['forums'][] = $moderator['forumid'];
	}
	unset($moderator);
	$db->free_result($moderators);

	if (is_array($modcache))
	{
		$showforums = true;
		uksort($modcache, 'strnatcasecmp'); // alphabetically sort moderator usernames
		foreach ($modcache AS $moderator)
		{
			$premodforums = array();
			foreach ($moderator['forums'] AS $forumid)
			{
				if ($vbulletin->forumcache["$forumid"]['options'] & $vbulletin->bf_misc_forumoptions['active'] AND (($vbulletin->forumcache["$forumid"]['showprivate'] > 1 OR (!$vbulletin->forumcache["$forumid"]['showprivate'] AND $vbulletin->options['showprivateforums'])) OR ($vbulletin->userinfo['forumpermissions']["$forumid"] & $vbulletin->bf_ugp_forumpermissions['canview'])))
				{
					$forumtitle = $vbulletin->forumcache["$forumid"]['title'];
					$premodforums["$forumid"] = $forumtitle;
				}
			}
			if (empty($premodforums))
			{
				continue;
			}
			$modforums = array();
			uasort($premodforums, 'strnatcasecmp'); // alphabetically sort moderator usernames
			foreach($premodforums AS $forumid => $forumtitle)
			{
				$foruminfo = array(
					'forumid' => $forumid,
					'title'   => $forumtitle,
				);
				($hook = vBulletinHook::fetch_hook('showgroups_forum')) ? eval($hook) : false;
				$templater = vB_Template::create('showgroups_forumbit');
					$templater->register('foruminfo', $foruminfo);
					$templater->register('forumtitle', $forumtitle);
				$modforums[] = $templater->render();
			}
			$user = $moderator;

			$user = process_showgroups_userinfo($user);
			$user['forumbits'] = implode("\n", $modforums);

			if ($vbulletin->options['enablepms'] AND $vbulletin->userinfo['permissions']['pmquota'] AND ($vbulletin->userinfo['permissions']['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['cancontrolpanel']
	 				OR ($user['receivepm'] AND $user['permissions']['pmquota']
	 				AND (!$user['receivepmbuddies'] OR can_moderate() OR strpos(" $user[buddylist] ", ' ' . $vbulletin->userinfo['userid'] . ' ') !== false))
	 		))
			{
				$show['pmlink'] = true;
			}
			else
			{
				$show['pmlink'] = false;
			}

			if ($user['showemail'] AND $vbulletin->options['displayemails'] AND (!$vbulletin->options['secureemail'] OR ($vbulletin->options['secureemail'] AND $vbulletin->options['enableemail'])) AND $vbulletin->userinfo['permissions']['genericpermissions'] & $vbulletin->bf_ugp_genericpermissions['canemailmember'] AND $vbulletin->userinfo['userid'])
			{
				$show['emaillink'] = true;
			}
			else
			{
				$show['emaillink'] = false;
			}

			exec_switch_bg();

			($hook = vBulletinHook::fetch_hook('showgroups_usergroup')) ? eval($hook) : false;
			$templater = vB_Template::create('showgroups_usergroupbit');
				$templater->register('bgclass', $bgclass);
				$templater->register('showforums', $showforums);
				$templater->register('user', $user);
				$templater->register('xhtml_id', ++$xhtmlid2);
			$moderatorbits .= $templater->render();
		}
	}
}

// *******************************************************

$navpopup = array(
	'id'    => 'showgroups_navpopup',
	'title' => $vbphrase['show_groups'],
	'link'  => 'showgroups.php' . $vbulletin->session->vars['sessionurl_q'],
);
construct_quick_nav($navpopup);

$navbits = construct_navbits(array('' => $vbphrase['show_groups']));
$navbar = render_navbar_template($navbits);

($hook = vBulletinHook::fetch_hook('showgroups_complete')) ? eval($hook) : false;

$templater = vB_Template::create('SHOWGROUPS');
	$templater->register_page_templates();
	$templater->register('forumjump', $forumjump);
	$templater->register('moderatorbits', $moderatorbits);
	$templater->register('navbar', $navbar);
	$templater->register('usergroups', $usergroups);
print_output($templater->render());

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:08, Tue Jul 12th 2011
|| # CVS: $RCSfile$ - $Revision: 32878 $
|| ####################################################################
\*======================================================================*/
?>