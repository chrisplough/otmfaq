<?php
/*======================================*\
|| #################################### ||
|| # Post Thank You Hack version 7.80 # ||
|| #################################### ||
\*======================================*/

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);
@set_time_limit(0);
ignore_user_abort(1);

// ##################### DEFINE IMPORTANT CONSTANTS #######################

// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array('maintenance');

// ########################## REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/includes/functions_post_thanks.php');

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

($hook = vBulletinHook::fetch_hook('post_thanks_admin_start')) ? eval($hook) : false;

if ($_REQUEST['do'] == 'recounters')
{
	($hook = vBulletinHook::fetch_hook('post_thanks_admin_recounters_start')) ? eval($hook) : false;

	print_form_header('post_thanks_admin', 'post_thanks_user_amount');
	print_table_header($vbphrase['post_thanks_user_amount'], 2, 0);
	print_description_row($vbphrase['post_thanks_user_amount_help']);
	print_input_row($vbphrase['number_of_users_to_process_per_cycle'], 'perpage', 1000);
	print_submit_row($vbphrase['post_thanks_user_amount']);

	print_form_header('post_thanks_admin', 'post_thanks_thanked_posts');
	print_table_header($vbphrase['post_thanks_thanked_posts'], 2, 0);
	print_description_row($vbphrase['post_thanks_thanked_posts_help']);
	print_input_row($vbphrase['number_of_users_to_process_per_cycle'], 'perpage', 1000);
	print_submit_row($vbphrase['post_thanks_thanked_posts']);

	print_form_header('post_thanks_admin', 'post_thanks_thanked_times');
	print_table_header($vbphrase['post_thanks_thanked_times'], 2, 0);
	print_description_row($vbphrase['post_thanks_thanked_times_help']);
	print_input_row($vbphrase['number_of_users_to_process_per_cycle'], 'perpage', 1000);
	print_submit_row($vbphrase['post_thanks_thanked_times']);
	
	print_form_header('post_thanks_admin', 'post_thanks_post_amount');
	print_table_header($vbphrase['post_thanks_post_amount'], 2, 0);
	print_description_row($vbphrase['post_thanks_post_amount_help']);
	print_input_row($vbphrase['number_of_posts_to_process_per_cycle'], 'perpage', 1000);
	print_submit_row($vbphrase['post_thanks_post_amount']);

	($hook = vBulletinHook::fetch_hook('post_thanks_admin_recounters_end')) ? eval($hook) : false;
}

if ($_REQUEST['do'] == 'post_thanks_user_amount')
{
	if (empty($vbulletin->GPC['perpage']))
	{
		$vbulletin->GPC['perpage'] = 1000;
	}

	$finishat = $vbulletin->GPC['startat'] + $vbulletin->GPC['perpage'];

	($hook = vBulletinHook::fetch_hook('post_thanks_admin_user_amount_start')) ? eval($hook) : false;

	echo '<p>' . $vbphrase['post_thanks_user_amount'] . '</p>';

	$users = $db->query_read("
		SELECT *
		FROM " . TABLE_PREFIX . "user
		WHERE userid >= " . $vbulletin->GPC['startat'] . " AND userid < $finishat
		ORDER BY userid
	");
	while ($user = $db->fetch_array($users))
	{
		$total = $db->query_first("
			SELECT COUNT(*) AS post_thanks_user_amount FROM " . TABLE_PREFIX . "post_thanks
			WHERE userid = $user[userid]
		");

        if (!($total[post_thanks_user_amount]))
        {
          $total[post_thanks_user_amount] = 0;
        }

		$db->query_write("
            UPDATE " . TABLE_PREFIX . "user
            SET post_thanks_user_amount = $total[post_thanks_user_amount]
            WHERE userid = $user[userid]
            ");

		echo construct_phrase($vbphrase['processing_x'], $user['userid']) . "<br />\n";
		vbflush();
	}

	($hook = vBulletinHook::fetch_hook('post_thanks_admin_user_amount_end')) ? eval($hook) : false;

	if ($checkmore = $db->query_first("SELECT userid FROM " . TABLE_PREFIX . "user WHERE userid >= $finishat LIMIT 1"))
	{
		print_cp_redirect("post_thanks_admin.php?" . $vbulletin->session->vars['sessionurl'] . "do=post_thanks_user_amount&startat=$finishat&pp=" . $vbulletin->GPC['perpage']);
		echo "<p><a href=\"post_thanks_admin.php?" . $vbulletin->session->vars['sessionurl'] . "do=post_thanks_user_amount&amp;startat=$finishat&amp;pp=" . $vbulletin->GPC['perpage'] . "\">" . $vbphrase['click_here_to_continue_processing'] . "</a></p>";
	}
	else
	{
		define('CP_REDIRECT', 'post_thanks_admin.php');
		print_stop_message('updated_post_counts_successfully');
	}
}

if ($_REQUEST['do'] == 'post_thanks_thanked_posts')
{
	if (empty($vbulletin->GPC['perpage']))
	{
		$vbulletin->GPC['perpage'] = 1000;
	}

	$finishat = $vbulletin->GPC['startat'] + $vbulletin->GPC['perpage'];

	($hook = vBulletinHook::fetch_hook('post_thanks_admin_thanked_posts_start')) ? eval($hook) : false;

	echo '<p>' . $vbphrase['post_thanks_thanked_posts'] . '</p>';

	$users = $db->query_read("
		SELECT *
		FROM " . TABLE_PREFIX . "user
		WHERE userid >= " . $vbulletin->GPC['startat'] . " AND userid < $finishat
		ORDER BY userid
	");
	while ($user = $db->fetch_array($users))
	{
		$total = $db->query_first("
			SELECT COUNT(*) AS post_thanks_thanked_posts FROM " . TABLE_PREFIX . "post
			WHERE userid = $user[userid] AND post_thanks_amount > 0
		");

        if (!($total[post_thanks_thanked_posts]))
        {
          $total[post_thanks_thanked_posts] = 0;
        }

		$db->query_write("
            UPDATE " . TABLE_PREFIX . "user
            SET post_thanks_thanked_posts = $total[post_thanks_thanked_posts]
            WHERE userid = $user[userid]
            ");

		echo construct_phrase($vbphrase['processing_x'], $user['userid']) . "<br />\n";
		vbflush();
	}

	($hook = vBulletinHook::fetch_hook('post_thanks_admin_thanked_posts_end')) ? eval($hook) : false;

	if ($checkmore = $db->query_first("SELECT userid FROM " . TABLE_PREFIX . "user WHERE userid >= $finishat LIMIT 1"))
	{
		print_cp_redirect("post_thanks_admin.php?" . $vbulletin->session->vars['sessionurl'] . "do=post_thanks_thanked_posts&startat=$finishat&pp=" . $vbulletin->GPC['perpage']);
		echo "<p><a href=\"post_thanks_admin.php?" . $vbulletin->session->vars['sessionurl'] . "do=post_thanks_thanked_posts&amp;startat=$finishat&amp;pp=" . $vbulletin->GPC['perpage'] . "\">" . $vbphrase['click_here_to_continue_processing'] . "</a></p>";
	}
	else
	{
		define('CP_REDIRECT', 'post_thanks_admin.php');
		print_stop_message('updated_post_counts_successfully');
	}
}

if ($_REQUEST['do'] == 'post_thanks_thanked_times')
{
	if (empty($vbulletin->GPC['perpage']))
	{
		$vbulletin->GPC['perpage'] = 1000;
	}

	$finishat = $vbulletin->GPC['startat'] + $vbulletin->GPC['perpage'];

	($hook = vBulletinHook::fetch_hook('post_thanks_admin_thanked_times_start')) ? eval($hook) : false;

	echo '<p>' . $vbphrase['post_thanks_thanked_times'] . '</p>';

	$users = $db->query_read("
		SELECT *
		FROM " . TABLE_PREFIX . "user
		WHERE userid >= " . $vbulletin->GPC['startat'] . " AND userid < $finishat
		ORDER BY userid
	");
	while ($user = $db->fetch_array($users))
	{
		$total = $db->query_first("
			SELECT SUM(post_thanks_amount) AS post_thanks_thanked_times FROM " . TABLE_PREFIX . "post
			WHERE userid = $user[userid] AND post_thanks_amount > 0
		");

        if (!($total[post_thanks_thanked_times]))
        {
          $total[post_thanks_thanked_times] = 0;
        }

		$db->query_write("
            UPDATE " . TABLE_PREFIX . "user
            SET post_thanks_thanked_times = $total[post_thanks_thanked_times]
            WHERE userid = $user[userid]
            ");

		echo construct_phrase($vbphrase['processing_x'], $user['userid']) . "<br />\n";
		vbflush();
	}

	($hook = vBulletinHook::fetch_hook('post_thanks_admin_thanked_times_end')) ? eval($hook) : false;

	if ($checkmore = $db->query_first("SELECT userid FROM " . TABLE_PREFIX . "user WHERE userid >= $finishat LIMIT 1"))
	{
		print_cp_redirect("post_thanks_admin.php?" . $vbulletin->session->vars['sessionurl'] . "do=post_thanks_thanked_times&startat=$finishat&pp=" . $vbulletin->GPC['perpage']);
		echo "<p><a href=\"post_thanks_admin.php?" . $vbulletin->session->vars['sessionurl'] . "do=post_thanks_thanked_times&amp;startat=$finishat&amp;pp=" . $vbulletin->GPC['perpage'] . "\">" . $vbphrase['click_here_to_continue_processing'] . "</a></p>";
	}
	else
	{
		define('CP_REDIRECT', 'post_thanks_admin.php');
		print_stop_message('updated_post_counts_successfully');
	}
}

if ($_REQUEST['do'] == 'post_thanks_post_amount')
{
	if (empty($vbulletin->GPC['perpage']))
	{
		$vbulletin->GPC['perpage'] = 1000;
	}

	$finishat = $vbulletin->GPC['startat'] + $vbulletin->GPC['perpage'];

	($hook = vBulletinHook::fetch_hook('post_thanks_admin_post_amount_start')) ? eval($hook) : false;

	echo '<p>' . $vbphrase['post_thanks_post_amount'] . '</p>';

	$posts = $db->query_read("
		SELECT *
		FROM " . TABLE_PREFIX . "post
		WHERE postid >= " . $vbulletin->GPC['startat'] . " AND postid < $finishat
		ORDER BY postid
	");
	while ($post = $db->fetch_array($posts))
	{
		$total = $db->query_first("
			SELECT COUNT(*) AS post_thanks_amount FROM " . TABLE_PREFIX . "post_thanks
			WHERE postid = $post[postid]
		");

        if (!($total[post_thanks_amount]))
        {
          $total[post_thanks_amount] = 0;
        }

		$db->query_write("
            UPDATE " . TABLE_PREFIX . "post
            SET post_thanks_amount = $total[post_thanks_amount]
            WHERE postid = $post[postid]
            ");

		echo construct_phrase($vbphrase['processing_x'], $post['postid']) . "<br />\n";
		vbflush();
	}

	($hook = vBulletinHook::fetch_hook('post_thanks_admin_post_amount_end')) ? eval($hook) : false;

	if ($checkmore = $db->query_first("SELECT postid FROM " . TABLE_PREFIX . "post WHERE postid >= $finishat LIMIT 1"))
	{
		print_cp_redirect("post_thanks_admin.php?" . $vbulletin->session->vars['sessionurl'] . "do=post_thanks_post_amount&startat=$finishat&pp=" . $vbulletin->GPC['perpage']);
		echo "<p><a href=\"post_thanks_admin.php?" . $vbulletin->session->vars['sessionurl'] . "do=post_thanks_post_amount&amp;startat=$finishat&amp;pp=" . $vbulletin->GPC['perpage'] . "\">" . $vbphrase['click_here_to_continue_processing'] . "</a></p>";
	}
	else
	{
		define('CP_REDIRECT', 'post_thanks_admin.php');
		print_stop_message('updated_post_counts_successfully');
	}
}

if ($_REQUEST['do'] == 'special_actions')
{
	($hook = vBulletinHook::fetch_hook('post_thanks_admin_special_actions_start')) ? eval($hook) : false;

	print_form_header('post_thanks_admin', 'delete_all_users_thanks');
	print_table_header($vbphrase['post_thanks_delete_all_users_thanks'], 2, 0);
	print_description_row($vbphrase['post_thanks_delete_all_users_thanks_help']);
	print_input_row($vbphrase['userid'], 'userid');
	print_submit_row($vbphrase['post_thanks_delete_all_users_thanks']);

	($hook = vBulletinHook::fetch_hook('post_thanks_admin_special_actions_end')) ? eval($hook) : false;
}

if ($_REQUEST['do'] == 'delete_all_users_thanks')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'userid' => TYPE_UINT
	));

	$userid = $vbulletin->GPC['userid'];

	($hook = vBulletinHook::fetch_hook('post_thanks_admin_delete_all_users_thanks_start')) ? eval($hook) : false;

	echo '<p>' . $vbphrase['post_thanks_delete_all_users_thanks'] . '</p>';

	$thanks = $db->query_read("
		SELECT *
		FROM " . TABLE_PREFIX . "post_thanks
		WHERE userid = $userid
		ORDER BY postid
	");
	while ($thank = $db->fetch_array($thanks))
	{
		$postinfo = fetch_postinfo($thank['postid']);

		if ($postinfo === false)
		{
			$vbulletin->db->query_write("DELETE FROM ". TABLE_PREFIX ."post_thanks WHERE postid = '$thank[postid]' AND userid = '$userid'");
		}
		else
		{
			delete_thanks($postinfo, $userid);
		}

		echo construct_phrase($vbphrase['processing_x'], $thank['postid']) . "<br />\n";
		vbflush();
	}

	($hook = vBulletinHook::fetch_hook('post_thanks_admin_delete_all_users_thanks_end')) ? eval($hook) : false;

	define('CP_REDIRECT', 'post_thanks_admin.php?do=special_actions');
	print_stop_message('post_thanks_delete_all_users_thanks_successfully');
}

($hook = vBulletinHook::fetch_hook('post_thanks_admin_end')) ? eval($hook) : false;

print_cp_footer();
?>