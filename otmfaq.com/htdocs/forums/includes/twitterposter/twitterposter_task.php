<?php
// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

if (!is_object($vbulletin->db))
{
	exit;
}

if ($vbulletin->options['twitter_manual_tweeting'])
{
	echo 'Automatic tweeting is disabled! exiting!';
	exit;
}

if (!is_array($vbulletin->forumcache))
{
	echo 'forum cache is not available, exiting!';
	exit;
}

if (empty($vbulletin->options['twitter_consumer_key'])
		OR empty($vbulletin->options['twitter_consumer_secret'])
		OR empty($vbulletin->options['twitter_oauth_access_token'])
		OR empty($vbulletin->options['twitter_oauth_access_token_secret']))
{
	echo 'twitter keys and tokens aren\'t registered in the database, authorize the hack please! exiting!';
	exit;
}

if (!$vbulletin->options['twitter_authorized'])
{
	echo 'The hack doesn\'t seem to be authorized, please authorize it! exiting!';
	exit;
}

echo 'Last run was at: ' . vbdate($vbulletin->options['dateformat'] . ' ' . $vbulletin->options['timeformat'], $vbulletin->options['twitter_lastrun']);
echo '<hr />';

if (!$vbulletin->options['twitter_lastrun'])
{
	$vbulletin->options['twitter_lastrun'] = TIMENOW - 600;
}

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################

require_once(DIR . '/includes/twitterposter/functions_twitterposter.php');

$restricted_forums = twitterposter_excluded_forums();

echo '<hr />';

$get_latest_threads = $vbulletin->db->query_read("SELECT
		thread.threadid, thread.title, thread.forumid,
		post.pagetext,
		user.userid, user.usergroupid, user.membergroupids
	FROM " . TABLE_PREFIX . "thread AS thread
	LEFT JOIN " . TABLE_PREFIX . "post AS post ON (post.postid = thread.firstpostid)
	LEFT JOIN " . TABLE_PREFIX . "deletionlog AS deletionlog ON(thread.threadid = deletionlog.primaryid AND type = 'thread')
	LEFT JOIN " . TABLE_PREFIX . "user AS user ON (user.userid = thread.postuserid)
	WHERE thread.open <> 10
		AND thread.visible = 1
		AND thread.tweeted = 0
		AND thread.dateline >= " . $vbulletin->options['twitter_lastrun'] . "
		" . (count($restricted_forums) > 0 ? "AND thread.forumid NOT IN (" . implode(',', $restricted_forums) . ")" : "") . "
		AND deletionlog.primaryid IS NULL
	ORDER BY thread.dateline
");

if ($threads_num = $vbulletin->db->num_rows($get_latest_threads))
{
	echo $threads_num . ' thread(s) have been fetched from the database!<br />';
}
else
{
	echo 'No thread has been fetched from the database! Exiting!<br />';
	exit;
}

echo '<hr />';

initialize_vbseo();

echo '<hr />';

if ($vbulletin->options['twitter_iconv_source'] != 'none')
{
	echo 'UTF-8 Converting is enabled!<br />';
	if (!function_exists('iconv'))
	{
		echo 'But iconv() function doesn\'t exist!<br />';
	}
}
else
{
	echo 'UTF-8 Converting isn\'t enabled! (if you\'re already UTF-8 then don\'t worry!)<br />';
}

echo '<hr />';

$threads = array();

while ($thread = $vbulletin->db->fetch_array($get_latest_threads))
{
	$threads["$thread[threadid]"] = $thread;
}

$vbulletin->db->free_result($get_latest_threads);

if (count($threads) > 0)
{
	foreach ($threads AS $threadid => $threadinfo)
	{
		$userinfo = fetch_userinfo_from_threadinfo($threadinfo);
		
		$tweetpermissions = cache_permissions($userinfo);
		
		if (!($tweetpermissions['twitterposter'] & $vbulletin->bf_ugp_twitterposter['tweetthreadsby']))
		{
			echo 'Threads by user (userid: ' . $userinfo['userid'] . ') isn\'t allowed to be tweeted! skipping to the next thread!<br />';
			echo '<hr />';
			continue;
		}
		
		unset($tweetpermissions, $userinfo);
		
		require_once(DIR . '/includes/twitterposter/class_twitterposter.php');
		
		$twitter = new vB_TweetPoster($vbulletin);
		$twitter->SetThreadInfo($threadinfo);
		$twitter->tweet_status_message();
	}
	
	unset ($status, $length);
}

unset($threads, $restricted_forums);

// Update last run setting!
twitterposter_update_lastrun();

log_cron_action('', $nextitem, 1);
?>