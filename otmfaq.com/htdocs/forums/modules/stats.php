<?php
// ++=========================================================================++
// || vBadvanced CMPS v3.2.1 (vB 3.6 - vB 3.8) - 63458
// || © 2003-2009 vBadvanced.com - All Rights Reserved
// || This file may not be redistributed in whole or significant part.
// || http://vbadvanced.com
// || Downloaded 23:34, Sun Jul 26th 2009
// || 2405244_563458070293
// ++ ========================================================================++

$statsc =& $vbulletin->adv_portal_stat;

$updatestatscache = false;

// ##### Thread / Post counts
if ($mod_options['portal_stats_threads'] OR $mod_options['portal_stats_posts'])
{
	$inforumquery = '';

	if ($mod_options['portal_stats_cacheforums'])
	{
		$doquery = $statsc['forum_lastupdate'] < (TIMENOW - (3600 * $mod_options['portal_stats_cacheforums'])) ? true : false;
	}
	else
	{
		$doquery = true;
		if (!empty($adv_canviewforums) AND $mod_options['portal_stats_forumperms'])
		{
			$inforumquery = 'WHERE forumid NOT IN(' . implode(',', $adv_canviewforums) .')';
		}
	}

	if ($doquery)
	{
		$totalthreads = 0;
		$totalposts = 0;

		$getforumstats = $db->query_read("
			SELECT threadcount, replycount
			FROM " . TABLE_PREFIX . "forum
			$inforumquery
		");
		while ($fstats = $db->fetch_array($getforumstats))
		{
			$totalthreads += $fstats['threadcount'];
			$totalposts += $fstats['replycount'];
		}

		$db->free_result($getforumstats);
		unset($fstats);

		$totalthreads = vb_number_format($totalthreads);
		$totalposts = vb_number_format($totalposts);

		if ($mod_options['portal_stats_cacheforums'])
		{
			$updatestatscache = true;

			$statsc['forum_lastupdate'] = TIMENOW;
			$statsc['totalthreads'] = $totalthreads;
			$statsc['totalposts'] = $totalposts;
		}

	}
	else if ($mod_options['portal_stats_cacheforums'])
	{
		$totalthreads = $statsc['totalthreads'];
		$totalposts = $statsc['totalposts'];
	}
}

// ##### Top Poster
if ($mod_options['portal_stats_topposter'])
{
	// Cached version
	if ($mod_options['portal_stats_updatefrequency'])
	{
		$topposter = $statsc['topposter'];

		// Time to update
		if ($statsc['tp_lastupdate'] < (TIMENOW - (3600 * $mod_options['portal_stats_updatefrequency'])))
		{
			$topcheck = $db->query_first("SELECT userid, username, posts FROM " . TABLE_PREFIX . "user ORDER BY posts DESC");

			$topposter = $topcheck;

			if (!$mod_options['portal_stats_topposter_posts'])
			{
				unset($topcheck['posts'], $topcheck['username']);
			}

			$statsc['topposter'] = $topcheck;
			$statsc['tp_lastupdate'] = TIMENOW;

			$updatestatscache = true;
		}

		// No username from the cache, so query the info
		if (!$topposter['username'] OR !$mod_options['portal_stats_topposter_posts'])
		{
			$topposter = $db->query_first("SELECT userid, username, posts FROM " . TABLE_PREFIX . "user WHERE userid = '$topposter[userid]'");
		}
	}
	else
	{
		$topposter = $db->query_first('SELECT userid, username, posts FROM ' . TABLE_PREFIX . 'user ORDER BY posts DESC');
	}

	$topposter['posts'] = vb_number_format($topposter['posts']);
}

$numbermembers = vb_number_format($vbulletin->userstats['numbermembers']);
$newusername = $vbulletin->userstats['newusername'];
$newuserid = $vbulletin->userstats['newuserid'];

($hook = vBulletinHook::fetch_hook('vba_cmps_module_stats')) ? eval($hook) : false;

if ($updatestatscache)
{
	build_datastore('adv_portal_stat', serialize($vbulletin->adv_portal_stat), 1);
}

eval('$home["$mods[modid]"][\'content\'] = "' . fetch_template('adv_portal_stats') . '";');

?>