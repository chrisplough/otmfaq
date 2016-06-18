<?php
/*=====================================*\
|| ################################### ||
|| # Post Groan Hack version 3.1     # ||
|| ################################### ||
\*=====================================*/

function post_groan_off($forumid = 0, $postinfo = array(), $threadfirstpostid = 0, $this_script = '')
{
	global $vbulletin;

	($hook = vBulletinHook::fetch_hook('post_groan_function_post_groan_off_start')) ? eval($hook) : false;

	if (!($vbulletin->options['post_groan_on_off']) || $forumid == 0 || post_groan_in_array($forumid, $vbulletin->options['post_groan_forum_off']))
	{
		return true;
	}

	if (($vbulletin->options['post_groan_forum_first_all'] && !($postinfo['postid'] == $threadfirstpostid)) || (post_groan_in_array($forumid, $vbulletin->options['post_groan_forum_first']) && !($postinfo['postid'] == $threadfirstpostid)))
	{
		return true;
	}

	if (!($this_script == 'showthread' || $this_script == 'showpost' || $this_script == ''))
	{
		return true;
	}

	if ($vbulletin->options['post_groan_usergroup_getting'])
	{
		if (is_member_of($postinfo, explode("|", $vbulletin->options['post_groan_usergroup_getting'])))
		{
			return true;
		}
	}

	($hook = vBulletinHook::fetch_hook('post_groan_function_post_groan_off_end')) ? eval($hook) : false;

	return false;
}

function can_groan_this_post($postinfo = array(), $threadisdeleted = 0)
{
	global $vbulletin;

	($hook = vBulletinHook::fetch_hook('post_groan_function_can_groan_this_post_start')) ? eval($hook) : false;

	if ($postinfo['postid'] == 0 || $vbulletin->userinfo['userid'] == 0 || $postinfo['isdeleted'] || $threadisdeleted || (!($vbulletin->options['post_groan_poster_button']) && $postinfo['userid'] == $vbulletin->userinfo['userid']))
	{
		return false;
	}

	if (post_groan_in_array($vbulletin->userinfo['usergroupid'], $vbulletin->options['post_groan_usergroup_using']) || post_groan_in_array($vbulletin->userinfo['userid'], $vbulletin->options['post_groan_user_useing']))
	{
		return false;
	}

	if ($vbulletin->userinfo['posts'] < $vbulletin->options['post_groan_post_count_needed'])
	{
		return false;
	}

	if ($vbulletin->options['post_groan_days_old'])
	{
		if (TIMENOW > (($vbulletin->options['post_groan_days_old'] * 60 * 60 * 24) + $postinfo['dateline']))
		{
			return false;
		}
	}

	if ($vbulletin->options['post_groan_integrate'])
	{
		require_once(DIR . '/includes/functions_post_thanks.php');
		if (thanked_already($postinfo))
		{
			return false;
		}
	}

	($hook = vBulletinHook::fetch_hook('post_groan_function_can_groan_this_post_end')) ? eval($hook) : false;

	return true;
}

function can_delete_all_groans()
{
	global $vbulletin;

	($hook = vBulletinHook::fetch_hook('post_groan_function_can_delete_all_groans_start')) ? eval($hook) : false;

	if ($vbulletin->userinfo['usergroupid'] == '6')
	{
		return true;
	}

	($hook = vBulletinHook::fetch_hook('post_groan_function_can_delete_all_groans_end')) ? eval($hook) : false;

	return false;
}

function groaned_already($postinfo, $userid = 0)
{
	global $vbulletin;
	$groans = fetch_groans($postinfo['postid']);

	($hook = vBulletinHook::fetch_hook('post_groan_function_groaned_already_start')) ? eval($hook) : false;

	$userid != 0 ? $userid = $userid : $userid = $vbulletin->userinfo['userid'];

	if ($postinfo['post_groan_amount'] && $groans[$userid])
	{
		return true;
	}

	($hook = vBulletinHook::fetch_hook('post_groan_function_groaned_already_end')) ? eval($hook) : false;

	return false;
}

function show_groan_date($forumid = 0)
{
	global $vbulletin;

	($hook = vBulletinHook::fetch_hook('post_groan_function_show_groan_date_start')) ? eval($hook) : false;

	if ($vbulletin->options['post_groan_date_all'] && !post_groan_in_array($forumid, $vbulletin->options['post_groan_date_forum']))
	{
		return true;
	}

	($hook = vBulletinHook::fetch_hook('post_groan_function_show_groan_date_end')) ? eval($hook) : false;

	return false;
}

function fetch_groans($postid = 0, $postids = '', $fetch_again = false)
{
	global $vbulletin;
	static $cache, $act;

	($hook = vBulletinHook::fetch_hook('post_groan_function_fetch_groans_start')) ? eval($hook) : false;

	if ((!($cache) && !($act)) || ($fetch_again))
	{
		$cache = array();

		if ($postids)
		{
			$post_ids = "0$postids";
		}
		else
		{
			$post_ids = $postid;
		}

		$groans = $vbulletin->db->query_read("SELECT * FROM " .TABLE_PREFIX. "post_groan WHERE postid IN (" . $post_ids . ") ORDER BY username ASC");

		while ($groan = $vbulletin->db->fetch_array($groans))
		{
			$cache[$groan['postid']][$groan['userid']]['userid'] = $groan['userid'];
			$cache[$groan['postid']][$groan['userid']]['username'] = $groan['username'];
			$cache[$groan['postid']][$groan['userid']]['date'] = $groan['date'];
			$cache[$groan['postid']][$groan['userid']]['postid'] = $groan['postid'];
		}
	}

	($hook = vBulletinHook::fetch_hook('post_groan_function_fetch_groans_end')) ? eval($hook) : false;

	$act = true;
	return $cache[$postid];
}

function fetch_groan_bit($forumid = 0, $groans)
{
	global $vbulletin;

	($hook = vBulletinHook::fetch_hook('post_groan_function_fetch_groan_bit_start')) ? eval($hook) : false;

	$number_rows = count($groans);

	$cmpt=1;

	foreach ($groans AS $groan)
	{
		if ($cmpt<$number_rows)
		{
			$virg=",";
		}
		else
		{
			$virg="";
		}

		if (show_groan_date($forumid))
		{
			$date_groan = vbdate($vbulletin->options['dateformat'], $groan['date'], true);
		}

		eval('$liste_user .= " ' . fetch_template('post_groan_box_bit') . '";');
		$cmpt++;
	}

	($hook = vBulletinHook::fetch_hook('post_groan_function_fetch_groan_bit_end')) ? eval($hook) : false;

	return $liste_user;
}

function fetch_post_groan_template($post)
{
    global $vbulletin, $vbphrase, $stylevar;

	($hook = vBulletinHook::fetch_hook('post_groan_function_fetch_post_groan_template_start')) ? eval($hook) : false;

	if ($vbulletin->options['legacypostbit'])
	{
		eval('$template = "' . fetch_template('post_groan_postbit_legacy') . '";');
	}
	else
	{
		eval('$template = "' . fetch_template('post_groan_postbit') . '";');
	}

	($hook = vBulletinHook::fetch_hook('post_groan_function_fetch_post_groan_template_end')) ? eval($hook) : false;

	return $template;
}

function add_groan($postinfo)
{
	global $vbulletin, $vbphrase;

	($hook = vBulletinHook::fetch_hook('post_groan_function_add_groan_start')) ? eval($hook) : false;

	$vbulletin->db->query_write("
		INSERT INTO ". TABLE_PREFIX ."post_groan
			(userid, username, date, postid)
		VALUES
			('" . $vbulletin->userinfo['userid'] . "', '" . $vbulletin->db->escape_string($vbulletin->userinfo['username']) . "', " . TIMENOW . ", " . $postinfo['postid'] .")
	");

	$set_user_gave = 'post_groan_user_amount = 1 + post_groan_user_amount';
	$set_user_got = 'post_groan_times = 1 + post_groan_times';
	$set_post = 'post_groan_amount = 1 + post_groan_amount';

	if ($postinfo['post_groan_amount'] == 0)
	{
		$set_user_got .= ', post_groan_posts = 1 + post_groan_posts';
	}

	if ($vbulletin->options['post_groan_reputation'])
	{
		$vbulletin->db->query_write("
			INSERT IGNORE INTO ". TABLE_PREFIX ."reputation
				(postid, userid, reputation, whoadded, reason, dateline)
			VALUES
				('".$postinfo['postid']."', '".$postinfo['userid']."', '-" . $vbulletin->options['post_groan_reputation'] . "', '" . $vbulletin->userinfo['userid'] . "', '$vbphrase[post_groan_groaned_post]', " . TIMENOW . ")
		");

		if ($vbulletin->db->affected_rows() != 0)
		{
			$set_user_got .= ", reputation = reputation - " . $vbulletin->options['post_groan_reputation'] . "";
		}
	}

	if ($vbulletin->options['post_groan_post_count'])
	{
		$set_user_gave .= ', posts = 1 + posts';
	}

	$vbulletin->db->query_write("
		UPDATE " . TABLE_PREFIX . "user
		SET $set_user_gave
		WHERE userid = '" . $vbulletin->userinfo['userid'] . "'
	");

	$vbulletin->db->query_write("
		UPDATE " . TABLE_PREFIX . "user
		SET $set_user_got
		WHERE userid = '$postinfo[userid]'
	");

	$vbulletin->db->query_write("
		UPDATE " . TABLE_PREFIX . "post
		SET $set_post
		WHERE postid = '$postinfo[postid]'
	");

	($hook = vBulletinHook::fetch_hook('post_groan_function_add_groan_end')) ? eval($hook) : false;
}

function delete_all_groans($postinfo)
{
	global $vbulletin, $vbphrase;

	($hook = vBulletinHook::fetch_hook('post_groan_function_delete_all_groans_start')) ? eval($hook) : false;

	$groans=$vbulletin->db->query_read("SELECT * FROM ". TABLE_PREFIX ."post_groan WHERE postid='$postinfo[postid]' ORDER BY username");
	$nb=$vbulletin->db->num_rows($groans);

	if ($nb != 0)
	{
			while ($groan = $vbulletin->db->fetch_array($groans))
		{
			$groan_userids[] = $groan['userid'];
		}

		$vbulletin->db->query_write("DELETE FROM ". TABLE_PREFIX ."post_groan WHERE postid='$postinfo[postid]'");

		$set_user_gave = 'post_groan_user_amount = post_groan_user_amount - 1';
		$set_user_got = "post_groan_times = post_groan_times - $nb, post_groan_posts = post_groan_posts - 1";
		$set_post = 'post_groan_amount = 0';
		$postinfo['post_groan_amount'] = 0;

		if ($vbulletin->options['post_groan_reputation'])
		{
			$vbulletin->db->query_write("DELETE FROM ". TABLE_PREFIX ."reputation WHERE postid = '$postinfo[postid]' AND reason = '$vbphrase[post_groan_groaned_post]'");

			if ($vbulletin->db->affected_rows() != 0)
			{
				$total_rep_got = $vbulletin->options['post_groan_reputation'] * $nb;
				$set_user_got .= ", reputation = reputation + $total_rep_got";
			}
	    }

		if ($vbulletin->options['post_groan_post_count'])
		{
			$set_user_gave .= ', posts = posts - 1';
		}

		$vbulletin->db->query_write("
			UPDATE " . TABLE_PREFIX . "user
			SET $set_user_gave
			WHERE userid IN (".implode(",",$groan_userids).")
		");

		$vbulletin->db->query_write("
			UPDATE " . TABLE_PREFIX . "user
			SET $set_user_got
			WHERE userid = '$postinfo[userid]'
		");

		$vbulletin->db->query_write("
			UPDATE " . TABLE_PREFIX . "post
			SET $set_post
			WHERE postid = '$postinfo[postid]'
		");
	}

	($hook = vBulletinHook::fetch_hook('post_groan_function_delete_all_groans_end')) ? eval($hook) : false;
}

function delete_groan($postinfo, $userid)
{
	global $vbulletin, $vbphrase;

	($hook = vBulletinHook::fetch_hook('post_groan_function_delete_groan_start')) ? eval($hook) : false;

	if (!(groaned_already($postinfo, $userid)))
	{
		return false;
	}

	$vbulletin->db->query_write("DELETE FROM ". TABLE_PREFIX ."post_groan WHERE postid = '$postinfo[postid]' AND userid = '$userid'");

	$set_user_gave = 'post_groan_user_amount = post_groan_user_amount - 1';
	$set_user_got = "post_groan_times = post_groan_times - 1";
	$set_post = 'post_groan_amount = post_groan_amount - 1';

	if ($postinfo[post_groan_amount] == 1)
	{
		$set_user_got .= ', post_groan_posts = post_groan_posts - 1';
	}

	if ($vbulletin->options['post_groan_reputation'])
	{
		$vbulletin->db->query_write("DELETE FROM ". TABLE_PREFIX ."reputation WHERE postid = '$postinfo[postid]' AND whoadded = '$userid' AND reason = '$vbphrase[post_groan_groaned_post]'");

		if ($vbulletin->db->affected_rows() != 0)
		{
			$set_user_got .= ", reputation = reputation + ". $vbulletin->options[post_groan_reputation] ."";
		}
	}

	if ($vbulletin->options['post_groan_post_count'])
	{
		$set_user_gave .= ', posts = posts - 1';
	}

	$vbulletin->db->query_write("
		UPDATE " . TABLE_PREFIX . "user
		SET $set_user_gave
		WHERE userid = '$userid'
	");

	$vbulletin->db->query_write("
		UPDATE " . TABLE_PREFIX . "user
		SET $set_user_got
		WHERE userid = '$postinfo[userid]'
	");

	$vbulletin->db->query_write("
		UPDATE " . TABLE_PREFIX . "post
		SET $set_post
		WHERE postid = '$postinfo[postid]'
	");

	($hook = vBulletinHook::fetch_hook('post_groan_function_delete_groan_end')) ? eval($hook) : false;

	return true;
}

function post_groan_in_array($number = 0, $array = 0)
{
    $array_split = explode("|", $array); 

    foreach ($array_split AS $array_number)
    {
        if ($number == $array_number)
        {
            return true; 
        } 
    } 
	return false;
}
?>