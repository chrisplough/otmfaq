<?php
/*==============================================*\
|| ############################################ ||
|| # Force Users to Read a Thread version 2.10 # ||
|| ############################################ ||
\*==============================================*/

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// ##################### DEFINE IMPORTANT CONSTANTS #######################
define('NOZIP', 1);

// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array();
$specialtemplates = array();

// ########################## REQUIRE BACK-END ############################
require_once('./global.php');

// ######################## CHECK ADMIN PERMISSIONS #######################
if (!can_administer('canadminthreads'))
{
	print_cp_no_permission();
}

// ################################ FUNCTIONS #############################
function dash_to_array($text)
{
	$text = str_replace('-', '', $text);
	return explode(',', $text);
}

function force_read_print_chooser_row($title, $name, $tablename, $selvalue = -1, $extra = '', $size = 0, $wherecondition = '')
{
	global $vbulletin;

	$tableid = $tablename . 'id';

	// check for existence of $iusergroupcache / $vbulletin->iforumcache etc first...
	$cachename = 'i' . $tablename . 'cache_' .  md5($wherecondition);

	if (!is_array($GLOBALS["$cachename"]))
	{
		$GLOBALS["$cachename"] = array();
		$result = $vbulletin->db->query_read("SELECT title, $tableid FROM " . TABLE_PREFIX . "$tablename $wherecondition ORDER BY title");
		while ($currow = $vbulletin->db->fetch_array($result))
		{
			$GLOBALS["$cachename"]["$currow[$tableid]"] = $currow['title'];
		}
		unset($currow);
		$vbulletin->db->free_result($result);
	}

	$selectoptions = array();
	if ($extra)
	{
		$selectoptions['-1'] = $extra;
	}

	foreach ($GLOBALS["$cachename"] AS $itemid => $itemtitle)
	{
		$selectoptions["$itemid"] = $itemtitle;
	}

	print_select_row($title, $name, $selectoptions, $selvalue, 0, $size, true);
}

function fetch_last_order_number()
{
	global $vbulletin;

	$order_number = $vbulletin->db->query_first("
		SELECT force_read_order
		FROM " . TABLE_PREFIX . "thread AS thread
		WHERE force_read = 1
		ORDER BY force_read_order DESC
	");
	return $order_number['force_read_order'];
}

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################

print_cp_header($vbphrase['thread_manager']);

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'choose';
}

// #############################################################################

if ($_REQUEST['do'] == 'update_order')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'force_read_order'   => TYPE_ARRAY
	));

	if ($vbulletin->GPC['force_read_order'])
	{
		foreach ($vbulletin->GPC['force_read_order'] AS $threadid => $order)
		{
			$db->query_write("
				UPDATE " . TABLE_PREFIX . "thread AS thread
				SET force_read_order = '$order'
				WHERE threadid = '$threadid'
			");
		}
	}

	$_REQUEST['do'] = 'choose';
}

// #############################################################################

if ($_REQUEST['do'] == 'reset')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'threadid'   => TYPE_INT
	));

	$db->query_write("
		DELETE FROM " . TABLE_PREFIX . "force_read_users
		WHERE force_read_threadid = '" . $vbulletin->GPC['threadid'] . "'
	");

	$db->query_write("
		DELETE FROM " . TABLE_PREFIX . "force_read_guests
		WHERE force_read_threadid = '" . $vbulletin->GPC['threadid'] . "'
	");

	$_REQUEST['do'] = 'choose';
}

// #############################################################################

if ($_REQUEST['do'] == 'delete')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'threadid'   => TYPE_INT
	));

	$db->query_write("
		UPDATE " . TABLE_PREFIX . "thread AS thread
		SET force_read = '0'
		WHERE threadid = '" . $vbulletin->GPC['threadid'] . "'
	");

	$_REQUEST['do'] = 'choose';
}

// #############################################################################
if ($_REQUEST['do'] == 'choose')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'orderby'   => TYPE_STR
	));

	switch ($vbulletin->GPC['orderby'])
	{
		case 'threadid':
			$order = 'threadid ASC';
			break;
		case 'threadtitle':
			$order = 'title ASC, force_read_order ASC';
			break;
		case 'order':
			$order = 'force_read_order ASC, threadid ASC';
			break;
		case 'date':
			$order = 'force_read_expire_date ASC, force_read_order ASC';
			break;
		default:
			$vbulletin->GPC['orderby'] = 'threadid';
			$order = 'force_read_order ASC';
	}

	$threads = $db->query_read("
		SELECT *
		FROM " . TABLE_PREFIX . "thread AS thread
		WHERE force_read = 1
		ORDER BY $order
	");

	print_form_header('force_read_thread', 'update_order');

	$headings = array();
	$headings[] = "<a href='force_read_thread.php?" . $vbulletin->session->vars['sessionurl'] . "do=choose&orderby=threadid'>" . $vbphrase['force_read_thread_id'] . "</a>";
	$headings[] = "<a href='force_read_thread.php?" . $vbulletin->session->vars['sessionurl'] . "do=choose&orderby=threadtitle'>" . $vbphrase['force_read_thread_title'] . "</a>";
	$headings[] = "<a href='force_read_thread.php?" . $vbulletin->session->vars['sessionurl'] . "do=choose&orderby=order'>" . $vbphrase['force_read_order'] . "</a>";
	$headings[] = "<a href='force_read_thread.php?" . $vbulletin->session->vars['sessionurl'] . "do=choose&orderby=date'>" . $vbphrase['force_read_expire_date'] . "</a>";
	$headings[] = $vbphrase['force_read_usergroups'];
	$headings[] = $vbphrase['force_read_location'];
	$headings[] = $vbphrase['force_read_action'];
	print_cells_row($headings, 1);

	while ($thread_info = $db->fetch_array($threads))
	{
		$cell = array();
		$cell[] = $thread_info['threadid'];
		$cell[] = '<a href="force_read_thread.php?' . $vbulletin->session->vars['sessionurl'] . 'do=edit&threadid=' . $thread_info['threadid'] . '">' . $thread_info['title'] . '</a>';
		$cell[] = '<input type="text" size="5" name="force_read_order[' . $thread_info['threadid'] . ']" value="' . $thread_info['force_read_order'] . '" \>';
		if ($thread_info['force_read_expire_date'] == 0)
		{
			$cell[] = $vbphrase['force_read_never'];
		}
		else if ($thread_info['force_read_expire_date'] < TIMENOW)
		{
			$cell[] = $vbphrase['force_read_expired'];
		}
		else
		{
			$cell[] = vbdate($vbulletin->options['logdateformat'], $thread_info['force_read_expire_date']);
		}

		if ($thread_info['force_read_usergroups'])
		{
			$usergroups = array();
			foreach(dash_to_array($thread_info['force_read_usergroups']) AS $usergroupid)
			{
				$usergroups[] = $vbulletin->usergroupcache[$usergroupid]['title'];
			}
			$cell[] = implode("<br \\>\n", $usergroups);
		}
		else
		{
			$cell[] = $vbphrase['force_read_all_usergroups'];
		}

		if ($thread_info['force_read_forums'])
		{
			$forums = array();
			foreach(dash_to_array($thread_info['force_read_forums']) AS $forumid)
			{
				$foruminfo = fetch_foruminfo($forumid);
				$forums[] = $foruminfo['title'];
			}
			$cell[] = implode("<br \\>\n", $forums);
		}
		else
		{
			$cell[] = $vbphrase['force_read_site_wide'];
		}

		$cell[] = '<a href="force_read_thread.php?' . $vbulletin->session->vars['sessionurl'] . 'do=edit&threadid=' . $thread_info['threadid'] . '">' . $vbphrase['force_read_edit'] . '</a> <a href="force_read_thread.php?' . $vbulletin->session->vars['sessionurl'] . 'do=reset&threadid=' . $thread_info['threadid'] . '">' . $vbphrase['force_read_reset'] . '</a> <a href="force_read_thread.php?' . $vbulletin->session->vars['sessionurl'] . 'do=delete&threadid=' . $thread_info['threadid'] . '">' . $vbphrase['force_read_delete'] . '</a>';
		print_cells_row($cell);
	}

	print_table_footer(7, '<input type="submit" class="button" value="' . $vbphrase['force_read_save_display_order'] . '" \> <input type="button" class="button" value="' . $vbphrase['force_read_add_new_thread'] . '" onclick="window.location=\'force_read_thread.php?do=add\';" \>');
}

// #############################################################################

if ($_REQUEST['do'] == 'edit' || $_REQUEST['do'] == 'add')
{

	print_form_header('force_read_thread', 'save');
	construct_hidden_code('do', 'save');

	if ($_REQUEST['do'] == 'edit')
	{
		$vbulletin->input->clean_array_gpc('r', array(
			'threadid'   => TYPE_INT
		));

		$threadinfo = fetch_threadinfo($vbulletin->GPC['threadid']);

		construct_hidden_code('force_user[threadid]', $threadinfo['threadid']);
		print_table_header(construct_phrase($vbphrase['x_y_id_z'], $vbphrase['thread'], $threadinfo['title'], $threadinfo['threadid']));
	}
	else
	{
		print_table_header($vbphrase['force_read_add_new_thread']);
		print_input_row($vbphrase['force_read_threadid'], 'force_user[threadid]');
	}

	print_input_row($vbphrase['force_read_order'], 'force_user[force_read_order]', iif($threadinfo['force_read_order'], $threadinfo['force_read_order'], fetch_last_order_number() + 10));
	print_radio_row($vbphrase['force_read_expire_date_enabled'], 'force_user[force_read_expire_date_enabled]', array(0 => 'No', 1 => 'Yes'), iif($threadinfo['force_read_expire_date'] != 0, 1, 0));
	print_time_row($vbphrase['force_read_expire_date'], 'force_user[force_read_expire_date]', iif($threadinfo['force_read_expire_date'], $threadinfo['force_read_expire_date'], TIMENOW));
	force_read_print_chooser_row($vbphrase['force_read_usergroups'], 'force_user[force_read_usergroups][]', 'usergroup', iif($threadinfo['force_read_usergroups'] != '', dash_to_array($threadinfo['force_read_usergroups']), -1), $vbphrase['force_read_all_usergroups'], 10, iif($vbulletin->options['forcereadthread_enableforguests'], '', 'WHERE usergroupid != 1'));
	print_forum_chooser($vbphrase['force_read_location'], 'force_user[force_read_forums][]', iif($threadinfo['force_read_forums'] != '', dash_to_array($threadinfo['force_read_forums']), -1), $vbphrase['force_read_site_wide'], false, true);

	print_submit_row($vbphrase['save']);
}

if ($_REQUEST['do'] == 'save')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'force_user'   => TYPE_ARRAY
	));

	$threadid = $vbulletin->GPC['force_user']['threadid'];
	$force_read_order = $vbulletin->GPC['force_user']['force_read_order'];
	
	if ($vbulletin->GPC['force_user']['force_read_expire_date_enabled'])
	{
		$force_read_expire_date = mktime($vbulletin->GPC['force_user']['force_read_expire_date']['hour'], $vbulletin->GPC['force_user']['force_read_expire_date']['minute'], 0, $vbulletin->GPC['force_user']['force_read_expire_date']['month'], $vbulletin->GPC['force_user']['force_read_expire_date']['day'], $vbulletin->GPC['force_user']['force_read_expire_date']['year'], $vbulletin->userinfo['dstonoff']);
	}
	else
	{
		$force_read_expire_date = 0;
	}

	if ($vbulletin->GPC['force_user']['force_read_usergroups'])
	{
		if (in_array('-1', $vbulletin->GPC['force_user']['force_read_usergroups']))
		{
			$force_read_usergroups = '';
		}
		else
		{
			foreach ($vbulletin->GPC['force_user']['force_read_usergroups'] AS $usergroupid)
			{
				$force_read_usergroups[] = '-' . $usergroupid . '-';
			}
			$force_read_usergroups = implode(',', $force_read_usergroups);
		}
	}
	else
	{
		$force_read_usergroups = '';
	}

	if ($vbulletin->GPC['force_user']['force_read_forums'])
	{
		if (in_array('-1', $vbulletin->GPC['force_user']['force_read_forums']))
		{
			$force_read_forums = '';
		}
		else
		{
			foreach ($vbulletin->GPC['force_user']['force_read_forums'] AS $forumid)
			{
				$force_read_forums[] = '-' . $forumid . '-';
			}
			$force_read_forums = implode(',', $force_read_forums);
		}
	}
	else
	{
		$force_read_forums = '';
	}

	$db->query_write("
		UPDATE " . TABLE_PREFIX . "thread AS thread
		SET force_read = '1',
			force_read_order = '$force_read_order',
			force_read_expire_date = '$force_read_expire_date',
			force_read_usergroups = '$force_read_usergroups',
			force_read_forums = '$force_read_forums'
		WHERE threadid = '$threadid'
	");

	define('CP_REDIRECT', "force_read_thread.php?do=choose");
	print_stop_message('force_read_saved_successfully');
}

print_cp_footer();
?>
