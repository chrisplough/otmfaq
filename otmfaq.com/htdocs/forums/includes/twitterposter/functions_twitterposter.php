<?php

function twitterposter_print($text)
{
	if (defined('IN_CONTROL_PANEL') AND !defined('TWITTERPOSTER_SILENT'))
	{
		echo $text;
	}
	
	return;
}

function twitterposter_print_r($array)
{
	if (defined('IN_CONTROL_PANEL') AND !defined('TWITTERPOSTER_SILENT'))
	{
		if (is_array($array))
		{
			print_r($array);
		}
		else
		{
			return;
		}
	}
	
	return;
}

function fetch_userinfo_from_threadinfo($threadinfo)
{
	$userinfo['userid'] = $threadinfo['userid'];unset($threadinfo['userid']);
	$userinfo['usergroupid'] = $threadinfo['usergroupid']; unset($threadinfo['usergroupid']);
	$userinfo['membergroupids'] = $threadinfo['membergroupids']; unset($threadinfo['membergroupids']);
	
	return $userinfo;
}

function initialize_vbseo()
{
	global $vbulletin;
	
	if ($vbulletin->products['crawlability_vbseo'])
	{
		// Based on this tutorial: http://www.vbseo.com/f2/vbseo-functions-extensibility-1662/
		
		require_once(DIR . '/includes/functions_vbseo.php');
			
		vbseo_get_options();
		vbseo_prepare_seo_replace();
		vbseo_get_forum_info();
		
		twitterposter_print('vBSEO has been detected and initialized!');
	}
	else
	{
		twitterposter_print('vBSEO isn\'t found!');
	}
}

function twitterposter_update_lastrun()
{
	global $vbulletin;
	
	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value = '" . TIMENOW . "' WHERE varname = 'twitter_lastrun'");
	
	require_once(DIR . '/includes/adminfunctions.php');
	build_options();
}

function twitterposter_excluded_forums()
{
	global $vbulletin;
	
	$restricted_forums = array();

	twitterposter_print('Excluded forums');

	foreach ($vbulletin->forumcache AS $forumid => $foruminfo)
	{
		$forumperms = fetch_permissions($forumid, 0, array('userid' => 0, 'usergroupid' => 1));
		
		if (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canview']) OR !($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewothers']) OR !$foruminfo['twitterenabled'])
		{
			$restricted_forums[] = $forumid;
			
			twitterposter_print('<br /><a href="' . $vbulletin->options['bburl'] . '/forumdisplay.php?' . $vbulletin->session->vars['sessionurl'] . 'f=' . $forumid . '" target="_blank">' . $foruminfo['title_clean'] . '</a>');
		}
	}
	
	if (count($restricted_forums) == 0)
	{
		twitterposter_print(': none!');
	}
	
	return $restricted_forums;
}

?>