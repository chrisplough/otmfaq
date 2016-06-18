<?php
/*=====================================*\
|| ################################### ||
|| # Post Groan Hack version 3.1     # ||
|| ################################### ||
\*=====================================*/

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);
@set_time_limit(0);
ignore_user_abort(1);

// ##################### DEFINE IMPORTANT CONSTANTS #######################

// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array('maintenance');

// ########################## REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/includes/functions_post_groan.php');

// ######################## CHECK ADMIN PERMISSIONS #######################
if (!can_administer('canadminthreads'))
{
	print_cp_no_permission();
}

// ############################# LOG ACTION ###############################
log_admin_action();

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################

print_cp_header($vbphrase['maintenance']);

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'recounters';
}

$vbulletin->input->clean_array_gpc('r', array(
	'perpage' => TYPE_UINT,
	'startat' => TYPE_UINT
));

($hook = vBulletinHook::fetch_hook('post_groan_admin_start')) ? eval($hook) : false;

if ($_REQUEST['do'] == 'recounters')
{
	($hook = vBulletinHook::fetch_hook('post_groan_admin_recounters_start')) ? eval($hook) : false;

	print_form_header('post_groan_admin', 'post_groan_user_amount');
	print_table_header($vbphrase['post_groan_user_amount'], 2, 0);
	print_description_row($vbphrase['post_groan_user_amount_help']);
	print_input_row($vbphrase['number_of_users_to_process_per_cycle'], 'perpage', 1000);
	print_submit_row($vbphrase['post_groan_user_amount']);

	print_form_header('post_groan_admin', 'post_groan_groaned_posts');
	print_table_header($vbphrase['post_groan_groaned_posts'], 2, 0);
	print_description_row($vbphrase['post_groan_groaned_posts_help']);
	print_input_row($vbphrase['number_of_users_to_process_per_cycle'], 'perpage', 1000);
	print_submit_row($vbphrase['post_groan_groaned_posts']);

	print_form_header('post_groan_admin', 'post_groan_groaned_times');
	print_table_header($vbphrase['post_groan_groaned_times'], 2, 0);
	print_description_row($vbphrase['post_groan_groaned_times_help']);
	print_input_row($vbphrase['number_of_users_to_process_per_cycle'], 'perpage', 1000);
	print_submit_row($vbphrase['post_groan_groaned_times']);
	
	print_form_header('post_groan_admin', 'post_groan_post_amount');
	print_table_header($vbphrase['post_groan_post_amount'], 2, 0);
	print_description_row($vbphrase['post_groan_post_amount_help']);
	print_input_row($vbphrase['number_of_posts_to_process_per_cycle'], 'perpage', 1000);
	print_submit_row($vbphrase['post_groan_post_amount']);

	($hook = vBulletinHook::fetch_hook('post_groan_admin_recounters_end')) ? eval($hook) : false;
}

if ($_REQUEST['do'] == 'post_groan_user_amount')
{
	if (empty($vbulletin->GPC['perpage']))
	{
		$vbulletin->GPC['perpage'] = 1000;
	}

	$finishat = $vbulletin->GPC['startat'] + $vbulletin->GPC['perpage'];

	($hook = vBulletinHook::fetch_hook('post_groan_admin_user_amount_start')) ? eval($hook) : false;

	echo '<p>' . $vbphrase['post_groan_user_amount'] . '</p>';

	$users = $db->query_read("
		SELECT *
		FROM " . TABLE_PREFIX . "user
		WHERE userid >= " . $vbulletin->GPC['startat'] . " AND userid < $finishat
		ORDER BY userid
	");
	while ($user = $db->fetch_array($users))
	{
		$total = $db->query_first("
			SELECT COUNT(*) AS post_groan_user_amount FROM " . TABLE_PREFIX . "post_groan
			WHERE userid = $user[userid]
		");

        if (!($total[post_groan_user_amount]))
        {
          $total[post_groan_user_amount] = 0;
        }

		$db->query_write("
            UPDATE " . TABLE_PREFIX . "user
            SET post_groan_user_amount = $total[post_groan_user_amount]
            WHERE userid = $user[userid]
            ");

		echo construct_phrase($vbphrase['processing_x'], $user['userid']) . "<br />\n";
		vbflush();
	}

	($hook = vBulletinHook::fetch_hook('post_groan_admin_user_amount_end')) ? eval($hook) : false;

	if ($checkmore = $db->query_first("SELECT userid FROM " . TABLE_PREFIX . "user WHERE userid >= $finishat LIMIT 1"))
	{
		print_cp_redirect("post_groan_admin.php?" . $vbulletin->session->vars['sessionurl'] . "do=post_groan_user_amount&startat=$finishat&pp=" . $vbulletin->GPC['perpage']);
		echo "<p><a href=\"post_groan_admin.php?" . $vbulletin->session->vars['sessionurl'] . "do=post_groan_user_amount&amp;startat=$finishat&amp;pp=" . $vbulletin->GPC['perpage'] . "\">" . $vbphrase['click_here_to_continue_processing'] . "</a></p>";
	}
	else
	{
		define('CP_REDIRECT', 'post_groan_admin.php');
		print_stop_message('updated_post_counts_successfully');
	}
}

if ($_REQUEST['do'] == 'post_groan_groaned_posts')
{
	if (empty($vbulletin->GPC['perpage']))
	{
		$vbulletin->GPC['perpage'] = 1000;
	}

	$finishat = $vbulletin->GPC['startat'] + $vbulletin->GPC['perpage'];

	($hook = vBulletinHook::fetch_hook('post_groan_admin_groaned_posts_start')) ? eval($hook) : false;

	echo '<p>' . $vbphrase['post_groan_groaned_posts'] . '</p>';

	$users = $db->query_read("
		SELECT *
		FROM " . TABLE_PREFIX . "user
		WHERE userid >= " . $vbulletin->GPC['startat'] . " AND userid < $finishat
		ORDER BY userid
	");
	while ($user = $db->fetch_array($users))
	{
		$total = $db->query_first("
			SELECT COUNT(*) AS post_groan_posts FROM " . TABLE_PREFIX . "post
			WHERE userid = $user[userid] AND post_groan_amount > 0
		");

        if (!($total[post_groan_posts]))
        {
          $total[post_groan_posts] = 0;
        }

		$db->query_write("
            UPDATE " . TABLE_PREFIX . "user
            SET post_groan_posts = $total[post_groan_posts]
            WHERE userid = $user[userid]
            ");

		echo construct_phrase($vbphrase['processing_x'], $user['userid']) . "<br />\n";
		vbflush();
	}

	($hook = vBulletinHook::fetch_hook('post_groan_admin_groaned_posts_end')) ? eval($hook) : false;

	if ($checkmore = $db->query_first("SELECT userid FROM " . TABLE_PREFIX . "user WHERE userid >= $finishat LIMIT 1"))
	{
		print_cp_redirect("post_groan_admin.php?" . $vbulletin->session->vars['sessionurl'] . "do=post_groan_groaned_posts&startat=$finishat&pp=" . $vbulletin->GPC['perpage']);
		echo "<p><a href=\"post_groan_admin.php?" . $vbulletin->session->vars['sessionurl'] . "do=post_groan_groaned_posts&amp;startat=$finishat&amp;pp=" . $vbulletin->GPC['perpage'] . "\">" . $vbphrase['click_here_to_continue_processing'] . "</a></p>";
	}
	else
	{
		define('CP_REDIRECT', 'post_groan_admin.php');
		print_stop_message('updated_post_counts_successfully');
	}
}

if ($_REQUEST['do'] == 'post_groan_groaned_times')
{
	if (empty($vbulletin->GPC['perpage']))
	{
		$vbulletin->GPC['perpage'] = 1000;
	}

	$finishat = $vbulletin->GPC['startat'] + $vbulletin->GPC['perpage'];

	($hook = vBulletinHook::fetch_hook('post_groan_admin_groaned_times_start')) ? eval($hook) : false;

	echo '<p>' . $vbphrase['post_groan_groaned_times'] . '</p>';

	$users = $db->query_read("
		SELECT *
		FROM " . TABLE_PREFIX . "user
		WHERE userid >= " . $vbulletin->GPC['startat'] . " AND userid < $finishat
		ORDER BY userid
	");
	while ($user = $db->fetch_array($users))
	{
		$total = $db->query_first("
			SELECT SUM(post_groan_amount) AS post_groan_times FROM " . TABLE_PREFIX . "post
			WHERE userid = $user[userid] AND post_groan_amount > 0
		");

        if (!($total[post_groan_times]))
        {
          $total[post_groan_times] = 0;
        }

		$db->query_write("
            UPDATE " . TABLE_PREFIX . "user
            SET post_groan_times = $total[post_groan_times]
            WHERE userid = $user[userid]
            ");

		echo construct_phrase($vbphrase['processing_x'], $user['userid']) . "<br />\n";
		vbflush();
	}

	($hook = vBulletinHook::fetch_hook('post_groan_admin_times_end')) ? eval($hook) : false;

	if ($checkmore = $db->query_first("SELECT userid FROM " . TABLE_PREFIX . "user WHERE userid >= $finishat LIMIT 1"))
	{
		print_cp_redirect("post_groan_admin.php?" . $vbulletin->session->vars['sessionurl'] . "do=post_groan_groaned_times&startat=$finishat&pp=" . $vbulletin->GPC['perpage']);
		echo "<p><a href=\"post_groan_admin.php?" . $vbulletin->session->vars['sessionurl'] . "do=post_groan_groaned_times&amp;startat=$finishat&amp;pp=" . $vbulletin->GPC['perpage'] . "\">" . $vbphrase['click_here_to_continue_processing'] . "</a></p>";
	}
	else
	{
		define('CP_REDIRECT', 'post_groan_admin.php');
		print_stop_message('updated_post_counts_successfully');
	}
}

if ($_REQUEST['do'] == 'post_groan_post_amount')
{
	if (empty($vbulletin->GPC['perpage']))
	{
		$vbulletin->GPC['perpage'] = 1000;
	}

	$finishat = $vbulletin->GPC['startat'] + $vbulletin->GPC['perpage'];

	($hook = vBulletinHook::fetch_hook('post_groan_admin_post_amount_start')) ? eval($hook) : false;

	echo '<p>' . $vbphrase['post_groan_post_amount'] . '</p>';

	$posts = $db->query_read("
		SELECT *
		FROM " . TABLE_PREFIX . "post
		WHERE postid >= " . $vbulletin->GPC['startat'] . " AND postid < $finishat
		ORDER BY postid
	");
	while ($post = $db->fetch_array($posts))
	{
		$total = $db->query_first("
			SELECT COUNT(*) AS post_groan_amount FROM " . TABLE_PREFIX . "post_groan
			WHERE postid = $post[postid]
		");

        if (!($total[post_groan_amount]))
        {
          $total[post_groan_amount] = 0;
        }

		$db->query_write("
            UPDATE " . TABLE_PREFIX . "post
            SET post_groan_amount = $total[post_groan_amount]
            WHERE postid = $post[postid]
            ");

		echo construct_phrase($vbphrase['processing_x'], $post['postid']) . "<br />\n";
		vbflush();
	}

	($hook = vBulletinHook::fetch_hook('post_groan_admin_post_amount_end')) ? eval($hook) : false;

	if ($checkmore = $db->query_first("SELECT postid FROM " . TABLE_PREFIX . "post WHERE postid >= $finishat LIMIT 1"))
	{
		print_cp_redirect("post_groan_admin.php?" . $vbulletin->session->vars['sessionurl'] . "do=post_groan_post_amount&startat=$finishat&pp=" . $vbulletin->GPC['perpage']);
		echo "<p><a href=\"post_groan_admin.php?" . $vbulletin->session->vars['sessionurl'] . "do=post_groan_post_amount&amp;startat=$finishat&amp;pp=" . $vbulletin->GPC['perpage'] . "\">" . $vbphrase['click_here_to_continue_processing'] . "</a></p>";
	}
	else
	{
		define('CP_REDIRECT', 'post_groan_admin.php');
		print_stop_message('updated_post_counts_successfully');
	}
}

if ($_REQUEST['do'] == 'special_actions')
{
	($hook = vBulletinHook::fetch_hook('post_groan_admin_special_actions_start')) ? eval($hook) : false;

	print_form_header('post_groan_admin', 'delete_all_users_groan');
	print_table_header($vbphrase['post_groan_delete_all_users_groan'], 2, 0);
	print_description_row($vbphrase['post_groan_delete_all_users_groan_help']);
	print_input_row($vbphrase['userid'], 'userid');
	print_submit_row($vbphrase['post_groan_delete_all_users_groan']);

	($hook = vBulletinHook::fetch_hook('post_groan_admin_special_actions_end')) ? eval($hook) : false;
}

if ($_REQUEST['do'] == 'delete_all_users_groan')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'userid' => TYPE_UINT
	));

	$userid = $vbulletin->GPC['userid'];

	($hook = vBulletinHook::fetch_hook('post_groan_admin_delete_all_users_groan_start')) ? eval($hook) : false;

	echo '<p>' . $vbphrase['post_groan_delete_all_users_groan'] . '</p>';

	$groans = $db->query_read("
		SELECT *
		FROM " . TABLE_PREFIX . "post_groan
		WHERE userid = $userid
		ORDER BY postid
	");
	while ($groan = $db->fetch_array($groans))
	{
		$postinfo = fetch_postinfo($groan['postid']);
		delete_groan($postinfo, $userid);

		echo construct_phrase($vbphrase['processing_x'], $groan['postid']) . "<br />\n";
		vbflush();
	}

	($hook = vBulletinHook::fetch_hook('post_groan_admin_delete_all_users_groan_end')) ? eval($hook) : false;

	define('CP_REDIRECT', 'post_groan_admin.php?do=special_actions');
	print_stop_message('post_groan_delete_all_users_groan_successfully');
}

($hook = vBulletinHook::fetch_hook('post_groan_admin_end')) ? eval($hook) : false;

print_cp_footer();
?>