<?php
/*======================================*\
|| #################################### ||
|| # Post Thank You Hack version 7.80 # ||
|| #################################### ||
\*======================================*/

function post_thanks_off($forumid = 0, $postinfo = array(), $threadfirstpostid = 0, $this_script = '')
{
	global $vbulletin;

	($hook = vBulletinHook::fetch_hook('post_thanks_function_post_thanks_off_start')) ? eval($hook) : false;

	if (!($vbulletin->options['post_thanks_on_off']) || $forumid == 0 || post_thanks_in_array($forumid, $vbulletin->options['post_thanks_forum_off']))
	{
		return true;
	}

	if (($vbulletin->options['post_thanks_forum_first_all'] && !($postinfo['postid'] == $threadfirstpostid)) || (post_thanks_in_array($forumid, $vbulletin->options['post_thanks_forum_first']) && !($postinfo['postid'] == $threadfirstpostid)))
	{
		return true;
	}

	if (!($this_script == 'showthread' || $this_script == 'showpost' || $this_script == ''))
	{
		return true;
	}

	if ($vbulletin->options['post_thanks_usergroup_getting'])
	{
		if (is_member_of($postinfo, explode("|", $vbulletin->options['post_thanks_usergroup_getting'])))
		{
			return true;
		}
	}

	if ($vbulletin->options['post_thanks_user_getting'])
	{
		if (in_array($postinfo['userid'], explode("|", $vbulletin->options['post_thanks_user_getting'])))
		{
			return true;
		}
	}

	($hook = vBulletinHook::fetch_hook('post_thanks_function_post_thanks_off_end')) ? eval($hook) : false;

	return false;
}

function can_thank_this_post($postinfo = array(), $threadisdeleted = 0, $check_security = false, $securitytoken = '')
{
	global $vbulletin;

	($hook = vBulletinHook::fetch_hook('post_thanks_function_can_thank_this_post_start')) ? eval($hook) : false;

	if ($postinfo['postid'] == 0 || $vbulletin->userinfo['userid'] == 0 || $postinfo['isdeleted'] || $threadisdeleted || (!($vbulletin->options['post_thanks_poster_button']) && $postinfo['userid'] == $vbulletin->userinfo['userid']))
	{
		return false;
	}

	if (post_thanks_in_array($vbulletin->userinfo['usergroupid'], $vbulletin->options['post_thanks_usergroup_using']) || post_thanks_in_array($vbulletin->userinfo['userid'], $vbulletin->options['post_thanks_user_useing']))
	{
		return false;
	}

	if ($vbulletin->userinfo['posts'] < $vbulletin->options['post_thanks_post_count_needed'])
	{
		return false;
	}

	if ($vbulletin->options['post_thanks_max_per_day'])
	{
		global $count_thanks_so_far_totay;

		if ($count_thanks_so_far_totay === null)
		{
			$count_thanks_so_far_totay = $vbulletin->db->query_first("SELECT COUNT(*) AS total FROM " .TABLE_PREFIX. "post_thanks WHERE userid = " . $vbulletin->userinfo['userid'] . " AND date > " . (TIMENOW - (60 * 60 * 24)) . "");
		}

		if ($vbulletin->options['post_thanks_max_per_day'] <= $count_thanks_so_far_totay['total'])
		{
			return false;
		}
	}

	if ($vbulletin->options['post_thanks_days_old'])
	{
		if (TIMENOW > (($vbulletin->options['post_thanks_days_old'] * 60 * 60 * 24) + $postinfo['dateline']))
		{
			return false;
		}
	}

	if ($vbulletin->options['post_groan_integrate'])
	{
		require_once(DIR . '/includes/functions_post_groan.php');
		if (groaned_already($postinfo))
		{
			return false;
		}
	}

	if ($check_security && function_exists(verify_security_token))
	{
		if (!verify_security_token($securitytoken, $vbulletin->userinfo['securitytoken_raw']))
		{
			return false;
		}
	}

	($hook = vBulletinHook::fetch_hook('post_thanks_function_can_thank_this_post_end')) ? eval($hook) : false;

	return true;
}

function can_delete_all_thanks()
{
	global $vbulletin;

	($hook = vBulletinHook::fetch_hook('post_thanks_function_can_delete_all_thanks_start')) ? eval($hook) : false;

	if ($vbulletin->userinfo['usergroupid'] == '6')
	{
		return true;
	}

	($hook = vBulletinHook::fetch_hook('post_thanks_function_can_delete_all_thanks_end')) ? eval($hook) : false;

	return false;
}

function thanked_already($postinfo, $userid = 0, $fetch_again = false)
{
	global $vbulletin;
	$thanks = fetch_thanks($postinfo['postid'], '', $fetch_again);

	($hook = vBulletinHook::fetch_hook('post_thanks_function_thanked_already_start')) ? eval($hook) : false;

	$userid != 0 ? $userid = $userid : $userid = $vbulletin->userinfo['userid'];

	if ($postinfo['post_thanks_amount'] && $thanks[$userid])
	{
		return true;
	}

	($hook = vBulletinHook::fetch_hook('post_thanks_function_thanked_already_end')) ? eval($hook) : false;

	return false;
}

function show_thanks_date($forumid = 0)
{
	global $vbulletin;

	($hook = vBulletinHook::fetch_hook('post_thanks_function_show_thanks_date_start')) ? eval($hook) : false;

	if ($vbulletin->options['post_thanks_date_all'] && !post_thanks_in_array($forumid, $vbulletin->options['post_thanks_date_forum']))
	{
		return true;
	}

	($hook = vBulletinHook::fetch_hook('post_thanks_function_show_thanks_date_end')) ? eval($hook) : false;

	return false;
}

function fetch_thanks($postid = 0, $postids = array(), $fetch_again = false)
{
	global $vbulletin;
	static $cache, $act;

	($hook = vBulletinHook::fetch_hook('post_thanks_function_fetch_thanks_start')) ? eval($hook) : false;

	if ((!($cache) && !($act)) || ($fetch_again))
	{
		$cache = array();

		if (empty($postids))
		{
			$postids[] = $postid;
		}

		if ($vbulletin->options['post_thanks_use_musername'])
		{
			$thanks = $vbulletin->db->query_read("SELECT * FROM " .TABLE_PREFIX. "post_thanks AS post_thanks INNER JOIN " .TABLE_PREFIX. "user AS user USING (userid) WHERE post_thanks.postid IN (" . implode(',', $postids) . ") ORDER BY post_thanks.username ASC");
		}
		else
		{
			$thanks = $vbulletin->db->query_read("SELECT * FROM " .TABLE_PREFIX. "post_thanks WHERE postid IN (" . implode(',', $postids) . ") ORDER BY username ASC");
		}

		while ($thank = $vbulletin->db->fetch_array($thanks))
		{
			$cache[$thank['postid']][$thank['userid']]['userid'] = $thank['userid'];

			if ($vbulletin->options['post_thanks_use_musername'])
			{
				$cache[$thank['postid']][$thank['userid']]['username'] = fetch_musername($thank);
			}
			else
			{
				$cache[$thank['postid']][$thank['userid']]['username'] = $thank['username'];
			}

			$cache[$thank['postid']][$thank['userid']]['date'] = $thank['date'];
			$cache[$thank['postid']][$thank['userid']]['postid'] = $thank['postid'];
		}
	}

	($hook = vBulletinHook::fetch_hook('post_thanks_function_fetch_thanks_end')) ? eval($hook) : false;

	$act = true;
	return $cache[$postid];
}

function fetch_thanks_bit($forumid = 0, $thanks)
{
	global $vbulletin;

	($hook = vBulletinHook::fetch_hook('post_thanks_function_fetch_thanks_bit_start')) ? eval($hook) : false;

	$number_rows = count($thanks);

	$cmpt=1;

	if ($number_rows > 0)
	{
		foreach ($thanks AS $thank)
		{
			if ($cmpt<$number_rows)
			{
				$virg=",";
			}
			else
			{
				$virg="";
			}

			if (show_thanks_date($forumid))
			{
				$date_thank = vbdate($vbulletin->options['dateformat'], $thank['date'], true);
			}

			$templater = vB_Template::create('post_thanks_box_bit');
			$templater->register('date_thank', $date_thank);
			$templater->register('thank', $thank);
			$templater->register('virg', $virg);
			$liste_user .= $templater->render();

			$cmpt++;
		}
	}

	($hook = vBulletinHook::fetch_hook('post_thanks_function_fetch_thanks_bit_end')) ? eval($hook) : false;

	return $liste_user;
}

function fetch_post_thanks_template($post)
{
    global $vbulletin, $vbphrase, $stylevar;

	($hook = vBulletinHook::fetch_hook('post_thanks_function_fetch_post_thanks_template_start')) ? eval($hook) : false;

	$templater = vB_Template::create('post_thanks_postbit');
	$templater->register('post', $post);
	$template = $templater->render();

	($hook = vBulletinHook::fetch_hook('post_thanks_function_fetch_post_thanks_template_end')) ? eval($hook) : false;

	return $template;
}

function add_thanks($postinfo)
{
	global $vbulletin, $vbphrase;

	($hook = vBulletinHook::fetch_hook('post_thanks_function_add_thanks_start')) ? eval($hook) : false;

	$vbulletin->db->query_write("
		INSERT INTO ". TABLE_PREFIX ."post_thanks
			(userid, username, date, postid)
		VALUES
			('" . $vbulletin->userinfo['userid'] . "', '" . $vbulletin->db->escape_string($vbulletin->userinfo['username']) . "', " . TIMENOW . ", " . $postinfo['postid'] .")
	");

	$set_user_gave = 'post_thanks_user_amount = 1 + post_thanks_user_amount';
	$set_user_got = 'post_thanks_thanked_times = 1 + post_thanks_thanked_times';
	$set_post = 'post_thanks_amount = 1 + post_thanks_amount';

	if ($postinfo['post_thanks_amount'] == 0)
	{
		$set_user_got .= ', post_thanks_thanked_posts = 1 + post_thanks_thanked_posts';
	}

	if ($vbulletin->options['post_thanks_reputation'])
	{
		$vbulletin->db->query_write("
			INSERT IGNORE INTO ". TABLE_PREFIX ."reputation
				(postid, userid, reputation, whoadded, reason, dateline)
			VALUES
				('".$postinfo['postid']."', '".$postinfo['userid']."', '" . $vbulletin->options['post_thanks_reputation'] . "', '" . $vbulletin->userinfo['userid'] . "', '$vbphrase[post_thanks_thanked_post]', " . TIMENOW . ")
		");

		if ($vbulletin->db->affected_rows() != 0)
		{
			$reputationlevel = $vbulletin->db->query_first_slave("
				SELECT reputationlevelid
 				FROM " . TABLE_PREFIX . "reputationlevel
				WHERE " . ($postinfo['reputation'] + $vbulletin->options['post_thanks_reputation']) . "  >= minimumreputation
  				ORDER BY minimumreputation DESC
				LIMIT 1
            ");
			$set_user_got .= ", reputation = " . $vbulletin->options['post_thanks_reputation'] . " + reputation, reputationlevelid = " . intval($reputationlevel['reputationlevelid']);
		}
	}

	if ($vbulletin->options['post_thanks_post_count'])
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

	($hook = vBulletinHook::fetch_hook('post_thanks_function_add_thanks_end')) ? eval($hook) : false;
}

function delete_all_thanks($postinfo, $remove_users_thanks_count = true)
{
	global $vbulletin, $vbphrase;

	($hook = vBulletinHook::fetch_hook('post_thanks_function_delete_all_thanks_start')) ? eval($hook) : false;

	$thanks=$vbulletin->db->query_read("SELECT * FROM ". TABLE_PREFIX ."post_thanks WHERE postid='$postinfo[postid]' ORDER BY username");
	$nb=$vbulletin->db->num_rows($thanks);

	if ($nb != 0)
	{
		while ($thank = $vbulletin->db->fetch_array($thanks))
		{
			$thank_userids[] = $thank['userid'];
		}

		$vbulletin->db->query_write("DELETE FROM ". TABLE_PREFIX ."post_thanks WHERE postid='$postinfo[postid]'");

		if ($remove_users_thanks_count == true || $vbulletin->options['post_thanks_delete_remove_thanks_count'])
		{
			$set_user_gave = 'post_thanks_user_amount = post_thanks_user_amount - 1';
			$set_user_got = "post_thanks_thanked_times = post_thanks_thanked_times - $nb, post_thanks_thanked_posts = post_thanks_thanked_posts - 1";
		}
		else
		{
			$set_user_gave = 'post_thanks_user_amount = post_thanks_user_amount';
			$set_user_got = "post_thanks_thanked_times = post_thanks_thanked_times, post_thanks_thanked_posts = post_thanks_thanked_posts";
		}

		$set_post = 'post_thanks_amount = 0';
		$postinfo['post_thanks_amount'] = 0;

		if ($vbulletin->options['post_thanks_reputation'] && ($remove_users_thanks_count == true || $vbulletin->options['post_thanks_delete_remove_thanks_count']))
		{
			$vbulletin->db->query_write("DELETE FROM ". TABLE_PREFIX ."reputation WHERE postid = '$postinfo[postid]' AND reason = '$vbphrase[post_thanks_thanked_post]'");

			if ($vbulletin->db->affected_rows() != 0)
			{
				$total_rep_got = $vbulletin->options['post_thanks_reputation'] * $nb;
				$set_user_got .= ", reputation = reputation - $total_rep_got";
			}
	    }

		if ($vbulletin->options['post_thanks_post_count'] && ($remove_users_thanks_count == true || $vbulletin->options['post_thanks_delete_remove_thanks_count']))
		{
			$set_user_gave .= ', posts = posts - 1';
		}

		$vbulletin->db->query_write("
			UPDATE " . TABLE_PREFIX . "user
			SET $set_user_gave
			WHERE userid IN (".implode(",",$thank_userids).")
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

	($hook = vBulletinHook::fetch_hook('post_thanks_function_delete_all_thanks_end')) ? eval($hook) : false;
}

function delete_thanks($postinfo, $userid, $remove_users_thanks_count = true)
{
	global $vbulletin, $vbphrase;

	($hook = vBulletinHook::fetch_hook('post_thanks_function_delete_thanks_start')) ? eval($hook) : false;

	if (!(thanked_already($postinfo, $userid, true)))
	{
		return false;
	}

	$vbulletin->db->query_write("DELETE FROM ". TABLE_PREFIX ."post_thanks WHERE postid = '$postinfo[postid]' AND userid = '$userid'");

	if ($remove_users_thanks_count == true || $vbulletin->options['post_thanks_delete_remove_thanks_count'])
	{
		$set_user_gave = 'post_thanks_user_amount = post_thanks_user_amount - 1';
		$set_user_got = "post_thanks_thanked_times = post_thanks_thanked_times - 1";
	}
	else
	{
		$set_user_gave = 'post_thanks_user_amount = post_thanks_user_amount';
		$set_user_got = "post_thanks_thanked_times = post_thanks_thanked_times";
	}
	
	$set_post = 'post_thanks_amount = post_thanks_amount - 1';

	if ($postinfo[post_thanks_amount] == 1 && ($remove_users_thanks_count == true || $vbulletin->options['post_thanks_delete_remove_thanks_count']))
	{
		$set_user_got .= ', post_thanks_thanked_posts = post_thanks_thanked_posts - 1';
	}

	if ($vbulletin->options['post_thanks_reputation'] && ($remove_users_thanks_count == true || $vbulletin->options['post_thanks_delete_remove_thanks_count']))
	{
		$vbulletin->db->query_write("DELETE FROM ". TABLE_PREFIX ."reputation WHERE postid = '$postinfo[postid]' AND whoadded = '$userid' AND reason = '$vbphrase[post_thanks_thanked_post]'");

		if ($vbulletin->db->affected_rows() != 0)
		{
			$set_user_got .= ", reputation = reputation - ". $vbulletin->options[post_thanks_reputation] ."";
		}
	}

	if ($vbulletin->options['post_thanks_post_count'] && ($remove_users_thanks_count == true || $vbulletin->options['post_thanks_delete_remove_thanks_count']))
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

	($hook = vBulletinHook::fetch_hook('post_thanks_function_delete_thanks_end')) ? eval($hook) : false;

	return true;
}

function post_thanks_in_array($number = 0, $array = 0)
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

function post_thanks_check_security($securitytoken = '')
{
	global $vbulletin;

	if ($securitytoken !== $vbulletin->userinfo['securitytoken'])
	{
		
	}
}
?>