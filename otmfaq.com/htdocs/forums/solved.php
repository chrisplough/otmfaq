<?php

/*======================================================================*\
|| #################################################################### ||
|| #                                                                  # ||
|| # Mark Threads As 'Solved' Version 2.0.1                           # ||
|| # Project Began: June 8th, 2007                                    # ||
|| # Version Released: July 30th, 2010                                # ||
|| #                                                                  # ||
|| # ---------------------------------------------------------------- # ||
|| #                                                                  # ||
|| # This may not be redistributed without consent from Eric Sizemore # ||
|| #                                                                  # ||
|| #           Copyright 2007-2010 Eric Sizemore (SecondV)            # ||
|| #        http://www.vbulletin.org/forum/member.php?u=142777        # ||
|| #                                                                  # ||
|| #################################################################### ||
\*======================================================================*/

// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'solved');

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array();

// get special data templates from the datastore
$specialtemplates = array();

// pre-cache templates used by all actions
$globaltemplates = array();

// pre-cache templates used by specific actions
$actiontemplates = array();

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/includes/functions_databuild.php');

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################
if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'marksolved';
}

// #######################################################################
// Make sure the solved thread system is enabled.
if (!$vbulletin->options['solvedthread_enabled'])
{
	eval(standard_error(fetch_error('solvedthread_disabled')));
}

// Make sure this is a valid thread
$threadid = $vbulletin->input->clean_gpc('r', 't', TYPE_UINT);

if (!$threadid)
{
	eval(standard_error(fetch_error('invalidid', $vbphrase['thread'], $vbulletin->options['contactuslink'])));
}

$threadinfo = fetch_threadinfo($threadid);

// Permissions check
$forumperms = fetch_permissions($threadinfo['forumid']);

if (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canview'])
	OR !($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads'])
	OR !($forumperms & $vbulletin->bf_ugp_solvedthread_permissions['canmarksolved'])
	OR ($vbulletin->userinfo['userid'] != $threadinfo['postuserid'] AND !can_moderate($threadinfo['forumid']))
	)
{
	print_no_permission();
}

// Deleted, or not visible?
if (($threadinfo['isdeleted']) OR (!$threadinfo['visible']))
{
	eval(standard_error(fetch_error('invalidid', $vbphrase['thread'], $vbulletin->options['contactuslink'])));
}
	
// #######################################################################
if ($_REQUEST['do'] == 'marksolved')
{
	// C'mon now, can't mark a thread solved that's already solved.
	if ($threadinfo['is_solved'])
	{
		eval(standard_error(fetch_error('solvedthread_alreadysolved')));
	}

	// Check if there is a forum password and if so, ensure the user has it set
	verify_forum_password($threadinfo['forumid'], $threadinfo['password']);

	$threadman =& datamanager_init('Thread', $vbulletin, ERRTYPE_STANDARD, 'threadpost');
	$threadman->set_existing($threadinfo);
	$threadman->validfields['is_solved'] = array(TYPE_UINT, REQ_NO);
	$threadman->set('is_solved', 1);

	// Let's close it if the admin chooses to...
	if ($vbulletin->options['solvedthread_closethread'] == 1)
	{
		$threadman->set('open', 0);
	}

	// Set prefix
	$threadman->set('prefixid', 'solvedthread_solved');
	$threadman->pre_save();

	if (count($threadman->errors) > 0)
	{
		foreach ($threadman->errors AS $error)
		{
			$msg .= "$error<br />\n";
		}
		eval(standard_error($msg));
	}

	$threadman->save();

	// Done...
	$action = $vbphrase['solvedthread_marked'];
	$vbulletin->url = 'showthread.php?' . $vbulletin->session->vars['sessionurl'] . "t=$threadid";
	eval(print_standard_redirect('redirect_solvedthread_marked', true, true));
}

// #######################################################################
if ($_REQUEST['do'] == 'markunsolved')
{
	// C'mon now, can't mark a thread unsolved that's not already solved.
	if (!$threadinfo['is_solved'])
	{
		eval(standard_error(fetch_error('solvedthread_notsolved')));
	}

	// Check if there is a forum password and if so, ensure the user has it set
	verify_forum_password($threadinfo['forumid'], $threadinfo['password']);

	$threadman =& datamanager_init('Thread', $vbulletin, ERRTYPE_STANDARD, 'threadpost');
	$threadman->set_existing($threadinfo);
	$threadman->validfields['is_solved'] = array(TYPE_UINT, REQ_NO);
	$threadman->set('is_solved', 0);

	// Let's re-open if closed, if the admin chooses to...
	if ($vbulletin->options['solvedthread_closethread'] == 1)
	{
		$threadman->set('open', 1);
	}

	// Remove the prefix
	$threadman->set('prefixid', '');
	$threadman->pre_save();

	if (count($threadman->errors) > 0)
	{
		foreach ($threadman->errors AS $error)
		{
			$msg .= "$error<br />\n";
		}
		eval(standard_error($msg));
	}

	$threadman->save();

	// Done...
	$action = $vbphrase['solvedthread_unmarked'];
	$vbulletin->url = 'showthread.php?' . $vbulletin->session->vars['sessionurl'] . "t=$threadid";
	eval(print_standard_redirect('redirect_solvedthread_unmarked', true, true));
}

?>