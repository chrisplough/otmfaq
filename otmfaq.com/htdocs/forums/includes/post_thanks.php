<?php
/*=====================================*\
|| ################################### ||
|| # Post Thank You Hack version 7.5 # ||
|| ################################### ||
\*=====================================*/

// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'post_thanks');
define('CSRF_PROTECTION', true);
define('LOCATION_BYPASS', 1);
define('NOPMPOPUP', 1);

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array();

// get special data templates from the datastore
$specialtemplates = array();

// pre-cache templates used by all actions
$globaltemplates = array(
	'post_thanks_box', 
    'post_thanks_box_bit', 
    'post_thanks_button', 
    'post_thanks_postbit', 
    'post_thanks_postbit_legacy'
);

// pre-cache templates used by specific actions
$actiontemplates = array();

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/includes/functions_post_thanks.php');

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

$vbulletin->input->clean_array_gpc('r', array(
	'using_ajax'       => TYPE_UINT,
	'securitytoken' => TYPE_STR
));
$using_ajax = $vbulletin->GPC['using_ajax'];
$securitytoken = $vbulletin->GPC['securitytoken'];

($hook = vBulletinHook::fetch_hook('post_thanks_main_start')) ? eval($hook) : false;

if ($_REQUEST['do'] == 'post_thanks_add')
{
	($hook = vBulletinHook::fetch_hook('post_thanks_main_add_thanks_start')) ? eval($hook) : false;

	$postinfo = array_merge($postinfo, fetch_userinfo($postinfo['userid']));
	if (post_thanks_off($forumid, $postinfo, $threadinfo['firstpostid']) || !can_thank_this_post($postinfo, $threadinfo['isdeleted'], true, $securitytoken) || thanked_already($postinfo))
	{
		$using_ajax ? exit : print_no_permission();
	}

	add_thanks($postinfo);

	($hook = vBulletinHook::fetch_hook('post_thanks_main_add_thanks_end')) ? eval($hook) : false;

	if ($using_ajax)
	{
		$thanks = fetch_thanks($postid, '', true);

		$postinfo['post_thanks_bit'] = fetch_thanks_bit($forumid, $thanks);
		
		$postinfo['post_thanks_amount'] = $postinfo['post_thanks_amount'] + 1;
		$postinfo['post_thanks_amount_formatted'] = vb_number_format($postinfo['post_thanks_amount']);

		if ($vbulletin->options['post_thanks_delete_own'])
		{
			$postinfo['show_thanks_remove_option'] = true;
		}

		$echo = fetch_post_thanks_template($postinfo);
		echo "$echo";
		exit;
	}
	else
	{
		$vbulletin->url = "showthread.php?$session[sessionurl]p=$postid";
		eval(print_standard_redirect('redirect_post_thanks'));
	}
}

if ($_REQUEST['do'] == 'post_thanks_remove_all')
{
	($hook = vBulletinHook::fetch_hook('post_thanks_main_remove_all_thanks_start')) ? eval($hook) : false;

	if (!(can_delete_all_thanks()))
	{
		$using_ajax ? exit : print_no_permission();
	}

	delete_all_thanks($postinfo);

	($hook = vBulletinHook::fetch_hook('post_thanks_main_remove_all_thanks_end')) ? eval($hook) : false;

	if ($using_ajax)
	{
		exit;
	}
	else
	{
		$vbulletin->url = "showthread.php?$session[sessionurl]p=$postid";
		eval(print_standard_redirect('redirect_post_thanks'));
	}
}

if ($_REQUEST['do'] == 'post_thanks_remove_user')
{
	($hook = vBulletinHook::fetch_hook('post_thanks_main_remove_user_thanks_start')) ? eval($hook) : false;

	if (!(delete_thanks($postinfo, $vbulletin->userinfo['userid'])))
	{
		$using_ajax ? exit : print_no_permission();
	}	

	($hook = vBulletinHook::fetch_hook('post_thanks_main_remove_user_thanks_end')) ? eval($hook) : false;

	$postinfo['post_thanks_amount'] = $postinfo['post_thanks_amount'] - 1;

	if ($postinfo['post_thanks_amount'] > 0 && $using_ajax)
	{     
		$thanks = fetch_thanks($postid, '', true);

		$postinfo['post_thanks_bit'] = fetch_thanks_bit($forumid, $thanks);

		$postinfo['post_thanks_amount_formatted'] = vb_number_format($postinfo['post_thanks_amount']);
		$postinfo['post_thanks_user'] = false;
		$postinfo['ajax'] = true;

		$echo = fetch_post_thanks_template($postinfo);
		echo "$echo";
		exit;
	}
	else if ($using_ajax)
	{
		exit;
	}
	else
	{
		$vbulletin->url = "showthread.php?$session[sessionurl]p=$postid";
		eval(print_standard_redirect('redirect_post_thanks'));
	}
}

if ($_REQUEST['do'] == 'findthanks')
{
	require_once(DIR . '/includes/functions_search.php');
	require_once(DIR . '/includes/functions_misc.php');

	$vbulletin->input->clean_array_gpc('r', array(
		'userid'	=> TYPE_UINT,
	));

	// valid user id?
	if (!$vbulletin->GPC['userid'])
	{
		eval(standard_error(fetch_error('invalidid', $vbphrase['user'], $vbulletin->options['contactuslink'])));
	}

	// get user info
	if ($user = $db->query_first("SELECT userid, username, posts FROM " . TABLE_PREFIX . "user WHERE userid = " . $vbulletin->GPC['userid']))
	{
		$searchuser =& $user['username'];
	}
	// could not find specified user
	else
	{
		eval(standard_error(fetch_error('invalidid', $vbphrase['user'], $vbulletin->options['contactuslink'])));
	}

	// #############################################################################
	// build search hash
	$query = '';
	$searchuser = $user['username'];
	$exactname = 1;
	$starteronly = 0;
	$forumchoice = $foruminfo['forumid'];
	$childforums = 1;
	$titleonly = 0;
	$showposts = 1;
	$searchdate = 0;
	$beforeafter = 'after';
	$replyless = 0;
	$replylimit = 0;
	$searchthreadid = 0;

	$searchhash = md5(TIMENOW . "||" . $vbulletin->userinfo['userid'] . "||" . strtolower($searchuser) . "||$exactname||$starteronly||$forumchoice||$childforums||$titleonly||$showposts||$searchdate||$beforeafter||$replyless||$replylimit||$searchthreadid");

	// check if search already done
	//if ($search = $db->query_first("SELECT searchid FROM " . TABLE_PREFIX . "search AS search WHERE searchhash = '" . $db->escape_string($searchhash) . "'"))
	//{
	//	$vbulletin->url = 'search.php?' . $vbulletin->session->vars['sessionurl'] . "searchid=$search[searchid]";
	//	eval(print_standard_redirect('search'));
	//}

	// start search timer
	$searchtime = microtime();

	$forumids = array();
	$noforumids = array();
	// #############################################################################
	// check to see if we should be searching in a particular forum or forums
	if ($forumids = fetch_search_forumids($vbulletin->GPC['forumchoice'], $vbulletin->GPC['childforums']))
	{
		$showforums = true;
	}
	else
	{
		foreach ($vbulletin->forumcache AS $forumid => $forum)
		{
			$fperms =& $vbulletin->userinfo['forumpermissions']["$forumid"];
			if (($fperms & $vbulletin->bf_ugp_forumpermissions['canview']))
			{
				$forumids[] = $forumid;
			}
		}
		$showforums = false;
	}

	if (empty($forumids))
	{
		eval(standard_error(fetch_error('searchnoresults', $displayCommon), '', false));
	}
	else
	{
		// query post ids in dateline DESC order...
		$orderedids = array();
		$posts = $db->query_read("
			SELECT postid
			FROM " . TABLE_PREFIX . "post AS post
			INNER JOIN " . TABLE_PREFIX . "thread AS thread ON(thread.threadid = post.threadid)
			WHERE post.userid = $user[userid]
				AND post.post_thanks_amount != 0
				AND thread.forumid IN(" . implode(',', $forumids) . ")
			ORDER BY post.dateline DESC
			LIMIT " . ($vbulletin->options['maxresults'] * 2) . "
		");
		while ($post = $db->fetch_array($posts))
		{
			$orderedids[] = $post['postid'];
		}
		unset($post);
		$db->free_result($posts);

		// did we get some results?
		if (empty($orderedids))
		{
			eval(standard_error(fetch_error('searchnoresults', $displayCommon), '', false));
		}

		// set display terms
		$display = array(
			'words' => array(),
			'highlight' => array(),
			'common' => array(),
			'users' => array($user['userid'] => $user['username']),
			'forums' => iif($showforums, $display['forums'], 0),
			'options' => array(
				'starteronly' => 0,
				'childforums' => 1,
				'action' => 'process'
			)
		);

		// end search timer
		$searchtime = number_format(fetch_microtime_difference($searchtime), 5, '.', '');

		/*insert query*/
		$db->query_write("
			REPLACE INTO " . TABLE_PREFIX . "search (userid, ipaddress, personal, searchuser, forumchoice, sortby, sortorder, searchtime, showposts, orderedids, dateline, displayterms, searchhash)
			VALUES (" . $vbulletin->userinfo['userid'] . ", '" . $db->escape_string(IPADDRESS) . "', 1, '" . $db->escape_string($user['username']) . "', '" . $db->escape_string($forumchoice) . "', 'post.dateline', 'DESC', $searchtime, 1, '" . $db->escape_string(implode(',', $orderedids)) . "', " . TIMENOW . ", '" . $db->escape_string(serialize($display)) . "', '" . $db->escape_string($searchhash) . "')
		");
		$searchid = $db->insert_id();

		$vbulletin->url = 'search.php?' . $vbulletin->session->vars['sessionurl'] . "searchid=$searchid";
		eval(print_standard_redirect('search'));
	}
}

if ($_REQUEST['do'] == 'findthanks_user_gave')
{
	require_once(DIR . '/includes/functions_search.php');
	require_once(DIR . '/includes/functions_misc.php');

	$vbulletin->input->clean_array_gpc('r', array(
		'userid'	=> TYPE_UINT,
	));

	// valid user id?
	if (!$vbulletin->GPC['userid'])
	{
		eval(standard_error(fetch_error('invalidid', $vbphrase['user'], $vbulletin->options['contactuslink'])));
	}

	// get user info
	if ($user = $db->query_first("SELECT userid, username, posts FROM " . TABLE_PREFIX . "user WHERE userid = " . $vbulletin->GPC['userid']))
	{
		$searchuser =& $user['username'];
	}
	// could not find specified user
	else
	{
		eval(standard_error(fetch_error('invalidid', $vbphrase['user'], $vbulletin->options['contactuslink'])));
	}

	// #############################################################################
	// build search hash
	$query = '';
	$searchuser = $user['username'];
	$exactname = 1;
	$starteronly = 0;
	$forumchoice = $foruminfo['forumid'];
	$childforums = 1;
	$titleonly = 0;
	$showposts = 1;
	$searchdate = 0;
	$beforeafter = 'after';
	$replyless = 0;
	$replylimit = 0;
	$searchthreadid = 0;

	$searchhash = md5(TIMENOW . "||" . $vbulletin->userinfo['userid'] . "||" . strtolower($searchuser) . "||$exactname||$starteronly||$forumchoice||$childforums||$titleonly||$showposts||$searchdate||$beforeafter||$replyless||$replylimit||$searchthreadid");

	// check if search already done
	//if ($search = $db->query_first("SELECT searchid FROM " . TABLE_PREFIX . "search AS search WHERE searchhash = '" . $db->escape_string($searchhash) . "'"))
	//{
	//	$vbulletin->url = 'search.php?' . $vbulletin->session->vars['sessionurl'] . "searchid=$search[searchid]";
	//	eval(print_standard_redirect('search'));
	//}

	// start search timer
	$searchtime = microtime();

	$forumids = array();
	$noforumids = array();
	// #############################################################################
	// check to see if we should be searching in a particular forum or forums
	if ($forumids = fetch_search_forumids($vbulletin->GPC['forumchoice'], $vbulletin->GPC['childforums']))
	{
		$showforums = true;
	}
	else
	{
		foreach ($vbulletin->forumcache AS $forumid => $forum)
		{
			$fperms =& $vbulletin->userinfo['forumpermissions']["$forumid"];
			if (($fperms & $vbulletin->bf_ugp_forumpermissions['canview']))
			{
				$forumids[] = $forumid;
			}
		}
		$showforums = false;
	}

	if (empty($forumids))
	{
		eval(standard_error(fetch_error('searchnoresults', $displayCommon), '', false));
	}
	else
	{
		// query post ids in dateline DESC order...
		$orderedids = array();
		$posts = $db->query_read("
			SELECT post_thanks.postid AS postid
			FROM " . TABLE_PREFIX . "post_thanks AS post_thanks
			LEFT JOIN " . TABLE_PREFIX . "post AS post USING (postid)
			INNER JOIN " . TABLE_PREFIX . "thread AS thread ON(thread.threadid = post.threadid)
			WHERE post_thanks.userid = $user[userid]
				AND thread.forumid IN(" . implode(',', $forumids) . ")
			ORDER BY post.dateline DESC
			LIMIT " . ($vbulletin->options['maxresults'] * 2) . "
		");
		while ($post = $db->fetch_array($posts))
		{
			$orderedids[] = $post['postid'];
		}
		unset($post);
		$db->free_result($posts);

		// did we get some results?
		if (empty($orderedids))
		{
			eval(standard_error(fetch_error('searchnoresults', $displayCommon), '', false));
		}

		// set display terms
		$display = array(
			'words' => array(),
			'highlight' => array(),
			'common' => array(),
			'users' => array($user['userid'] => $user['username']),
			'forums' => iif($showforums, $display['forums'], 0),
			'options' => array(
				'starteronly' => 0,
				'childforums' => 1,
				'action' => 'process'
			)
		);

		// end search timer
		$searchtime = number_format(fetch_microtime_difference($searchtime), 5, '.', '');

		/*insert query*/
		$db->query_write("
			REPLACE INTO " . TABLE_PREFIX . "search (userid, ipaddress, personal, searchuser, forumchoice, sortby, sortorder, searchtime, showposts, orderedids, dateline, displayterms, searchhash)
			VALUES (" . $vbulletin->userinfo['userid'] . ", '" . $db->escape_string(IPADDRESS) . "', 1, '" . $db->escape_string($user['username']) . "', '" . $db->escape_string($forumchoice) . "', 'post.dateline', 'DESC', $searchtime, 1, '" . $db->escape_string(implode(',', $orderedids)) . "', " . TIMENOW . ", '" . $db->escape_string(serialize($display)) . "', '" . $db->escape_string($searchhash) . "')
		");
		$searchid = $db->insert_id();

		$vbulletin->url = 'search.php?' . $vbulletin->session->vars['sessionurl'] . "searchid=$searchid";
		eval(print_standard_redirect('search'));
	}
}

// #############################################################################
if ($_REQUEST['do'] == 'findallthanks')
{
	require_once(DIR . '/includes/functions_search.php');
	require_once(DIR . '/includes/functions_misc.php');

	// #############################################################################
	// build search hash
	$query = '';
	$searchuser = $user['username'];
	$exactname = 1;
	$starteronly = 0;
	$forumchoice = $foruminfo['forumid'];
	$childforums = 1;
	$titleonly = 0;
	$showposts = 1;
	$searchdate = 0;
	$beforeafter = 'after';
	$replyless = 0;
	$replylimit = 0;
	$searchthreadid = 0;

	$searchhash = md5(TIMENOW . "||" . $vbulletin->userinfo['userid'] . "||" . strtolower($searchuser) . "||$exactname||$starteronly||$forumchoice||$childforums||$titleonly||$showposts||$searchdate||$beforeafter||$replyless||$replylimit||$searchthreadid");

	// check if search already done
	//if ($search = $db->query_first("SELECT searchid FROM " . TABLE_PREFIX . "search AS search WHERE searchhash = '" . $db->escape_string($searchhash) . "'"))
	//{
	//	$vbulletin->url = 'search.php?' . $vbulletin->session->vars['sessionurl'] . "searchid=$search[searchid]";
	//	eval(print_standard_redirect('search'));
	//}

	// start search timer
	$searchtime = microtime();

	$forumids = array();
	$noforumids = array();
	// #############################################################################
	// check to see if we should be searching in a particular forum or forums
	if ($forumids = fetch_search_forumids($vbulletin->GPC['forumchoice'], $vbulletin->GPC['childforums']))
	{
		$showforums = true;
	}
	else
	{
		foreach ($vbulletin->forumcache AS $forumid => $forum)
		{
			$fperms =& $vbulletin->userinfo['forumpermissions']["$forumid"];
			if (($fperms & $vbulletin->bf_ugp_forumpermissions['canview']))
			{
				$forumids[] = $forumid;
			}
		}
		$showforums = false;
	}

	if (empty($forumids))
	{
		eval(standard_error(fetch_error('searchnoresults', $displayCommon), '', false));
	}
	else
	{
		// query post ids in dateline DESC order...
		$orderedids = array();
		$posts = $db->query_read("
			SELECT postid
			FROM " . TABLE_PREFIX . "post AS post
			INNER JOIN " . TABLE_PREFIX . "thread AS thread ON(thread.threadid = post.threadid)
			WHERE post.post_thanks_amount != 0
				AND thread.forumid IN(" . implode(',', $forumids) . ")
			ORDER BY post.dateline DESC
			LIMIT " . ($vbulletin->options['maxresults'] * 2) . "
		");
		while ($post = $db->fetch_array($posts))
		{
			$orderedids[] = $post['postid'];
		}
		unset($post);
		$db->free_result($posts);

		// did we get some results?
		if (empty($orderedids))
		{
			eval(standard_error(fetch_error('searchnoresults', $displayCommon), '', false));
		}

		// set display terms
		$display = array(
			'words' => array(),
			'highlight' => array(),
			'common' => array(),
			'users' => array($user['userid'] => $user['username']),
			'forums' => iif($showforums, $display['forums'], 0),
			'options' => array(
				'starteronly' => 0,
				'childforums' => 1,
				'action' => 'process'
			)
		);

		// end search timer
		$searchtime = number_format(fetch_microtime_difference($searchtime), 5, '.', '');

		/*insert query*/
		$db->query_write("
			REPLACE INTO " . TABLE_PREFIX . "search (userid, ipaddress, personal, searchuser, forumchoice, sortby, sortorder, searchtime, showposts, orderedids, dateline, displayterms, searchhash)
			VALUES (" . $vbulletin->userinfo['userid'] . ", '" . $db->escape_string(IPADDRESS) . "', 1, '" . $db->escape_string($user['username']) . "', '" . $db->escape_string($forumchoice) . "', 'post.dateline', 'DESC', $searchtime, 1, '" . $db->escape_string(implode(',', $orderedids)) . "', " . TIMENOW . ", '" . $db->escape_string(serialize($display)) . "', '" . $db->escape_string($searchhash) . "')
		");
		$searchid = $db->insert_id();

		$vbulletin->url = 'search.php?' . $vbulletin->session->vars['sessionurl'] . "searchid=$searchid";
		eval(print_standard_redirect('search'));
	}
}

($hook = vBulletinHook::fetch_hook('post_thanks_main_end')) ? eval($hook) : false;
?>