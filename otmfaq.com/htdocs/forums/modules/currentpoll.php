<?php
// ++=========================================================================++
// || vBadvanced CMPS v3.2.1 (vB 3.6 - vB 3.8) - 63458
// || © 2003-2009 vBadvanced.com - All Rights Reserved
// || This file may not be redistributed in whole or significant part.
// || http://vbadvanced.com
// || Downloaded 23:34, Sun Jul 26th 2009
// || 2405244_563458070293
// ++ ========================================================================++
// Thanks to Andreas (Kirby) for help with multiple choices and other areas

switch ($mod_options['portal_poll_orderby'])
{
	case 'rand':
		$vba_poll_orderby = 'RAND()';
	break;
	case 'dateline':
		$vba_poll_orderby = 'poll.dateline';
	break;
	default:
		$vba_poll_orderby = $mod_options['portal_poll_orderby'];
}

// Figure out which forums or threads we're dealing with
$vba_poll_fields = '';
$vba_poll_join = '';
$vba_poll_where = '';

if (!empty($mod_options['portal_threadids']) AND is_array($mod_options['portal_threadids']))
{
	$vba_poll_where .= ' AND thread.threadid IN(' . implode(',', array_keys($mod_options['portal_threadids'])) . ')';
}
else if (!empty($mod_options['portal_poll_forumid']) AND is_array($mod_options['portal_poll_forumid']))
{
	$mods['inforums'] = $mod_options['portal_poll_forumid'];

	if ($mod_options['portal_applypermissions'])
	{
		$mods['inforums'] = array_diff($mods['inforums'], $adv_canviewforums);
	}

	// No permission for the forums selected
	if (empty($mods['inforums']))
	{
		$mods['inforums'] = '';
		$mods['nodisplay'] = true;
	}
	else
	{
		$vba_poll_where .= ' AND thread.forumid IN(' . implode(',', $mods['inforums']) . ')';
	}
}

if (!$mods['inforums'] AND !empty($adv_canviewforums) AND $mod_options['portal_applypermissions'])
{
	$vba_poll_where .= ' AND thread.forumid NOT IN(' . implode(',', $adv_canviewforums) . ')';
}

// User's vote or have to join to order by last vote date
if ($vbulletin->userinfo['userid'] OR $mod_options['portal_poll_orderby'] == 'votedate')
{
	$vba_poll_fields = ', voteoption';
	$vba_poll_join .= ' LEFT JOIN ' . TABLE_PREFIX . 'pollvote AS pollvote ON (pollvote.pollid = poll.pollid AND pollvote.userid = ' . $vbulletin->userinfo['userid'] . ')';
}

// Ignored users
if ($ignusers)
{
	$vba_poll_where .= ' AND thread.postuserid NOT IN(' . $ignusers . ')';
}

if ($mod_options['portal_poll_cutoffdate'])
{
	$vba_poll_where .= ' AND poll.dateline > ' . (TIMENOW - $mod_options['portal_poll_cutoffdate'] * 86400);
}

if (!$mod_options['portal_poll_allowclosed'])
{
	$vba_poll_where .= ' AND active = 1';
}

($hook = vBulletinHook::fetch_hook('vba_cmps_module_currentpoll_start')) ? eval($hook) : false;

if (!$mods['nodisplay'])
{
	$uservote = array();

	$pollmod = $db->query_first("
		SELECT poll.*, thread.pollid, open, threadid, replycount, forumid $vba_poll_fields
		FROM " . TABLE_PREFIX . "poll AS poll
		INNER JOIN " . TABLE_PREFIX . "thread AS thread USING (pollid)
		$vba_poll_join
		WHERE open <> 10 AND visible = 1
		$vba_poll_where
		ORDER BY $vba_poll_orderby $mod_options[portal_poll_direction]
	");

	if ($pollmod['pollid'])
	{
		$pollmod['showresults'] = 0;

		$pollmod['question'] = fetch_word_wrapped_string(
			$bbcode_parser->parse(unhtmlspecialchars($pollmod['question']), $pollmod['forumid'], $mod_options['portal_poll_allowsmilies']),
			$mod_options['portal_poll_wraptitle']
		);

		$splitvotes = explode('|||', $pollmod['votes']);
		$splitoptions = explode('|||', $pollmod['options']);

		$pollmod['numbervotes'] = array_sum($splitvotes);

		if ($pollmod['voteoption'])
		{
			$uservote[$pollmod['voteoption']] = 1;
		}

		// Get all votes if option allows multiple choices and user has voted
		if ($pollmod['multiple'] AND $pollmod['voteoption'])
		{
			$pollvotes = $db->query_read("
				SELECT voteoption
				FROM " . TABLE_PREFIX . "pollvote
				WHERE pollid = $pollmod[pollid]
				AND userid = " . $vbulletin->userinfo['userid']
			);
			if ($db->num_rows($pollvotes) > 0)
			{
				$pollmod['showresults'] = 1;
				while ($pollvote = $db->fetch_array($pollvotes))
				{
					$uservote[$pollvote['voteoption']] = 1;
				}
			}
		}

		$pollforumperms =& $adv_forumperms[$pollmod['forumid']];

		// Figure out whether to display results or vote options
		if (!$pollmod['active'] OR !$pollmod['open'] OR ($pollmod['dateline'] + ($pollmod['timeout'] * 86400) < TIMENOW AND $pollmod['timeout']) OR !($pollforumperms & $vbulletin->bf_ugp_forumpermissions['canvote']))
		{
			$pollmod['showresults'] = 1;

			$pollmod['message'] = (($pollforumperms & $vbulletin->bf_ugp_forumpermissions['canvote'])) ? $vbphrase['this_poll_is_closed'] : $vbphrase['you_may_not_vote_on_this_poll'];
		}

		if (fetch_bbarray_cookie('poll_voted', $pollmod['pollid']) OR $pollmod['voteoption'])
		{
			$pollmod['showresults'] = 1;
			$pollmod['message'] = $vbphrase['you_have_already_voted_on_this_poll'];
		}

		$pollbits = '';
		$counter = 1;

		($hook = vBulletinHook::fetch_hook('vba_cmps_module_currentpoll_middle')) ? eval($hook) : false;

		// Edit poll link
		if (can_moderate($pollmod['forumid'], 'caneditpoll'))
		{
			$show['editpoll'] = true;
		}

		// Get the votes if necessary
		if ($pollmod['showresults'])
		{
			$pollmod['nvotes'] = ($pollmod['multiple']) ? $pollmod['voters'] : $pollmod['numbervotes'];
		}

		// Sort out the vote choices
		$option = array();
		foreach ($splitvotes AS $voteoption => $value)
		{
			$arrayindex = $voteoption + 1;
			$option['number'] = $counter;
			$option['question'] = fetch_word_wrapped_string(
				$bbcode_parser->parse($splitoptions[$voteoption], $pollmod['forumid'], $mod_options['portal_poll_allowsmilies']),
				$mod_options['portal_poll_wrapchoices']
			);

			($hook = vBulletinHook::fetch_hook('vba_cmps_module_currentpoll_pollbits')) ? eval($hook) : false;

			// Results
			if ($pollmod['showresults'])
			{
				$option['votes'] = vb_number_format($splitvotes[$voteoption]);
				$option['percent'] = ($value) ? vb_number_format($value / $pollmod['nvotes'] * 100, 2) : 0;
				$option['graphicnumber'] = $option['number'] % 6 + 1;
				$option['barnumber'] = intval($option['percent'] * 1.4);
				$show['voteital'] = ($uservote[$arrayindex]) ? true : false;

				$votephrase = ($option['votes'] != 1) ? $vbphrase['votes'] : $vbphrase['vote'];

				eval('$pollbits .= "' . fetch_template('adv_portal_pollresult') . '";');
			}
			// Multiple choice
			else if ($pollmod['multiple'])
			{
				eval('$pollbits .= "' . fetch_template('adv_portal_polloption_multiple') . '";');
			}
			// Single choice
			else
			{
				eval('$pollbits .= "' . fetch_template('adv_portal_polloption') . '";');
			}
			$counter++;
		}

		$pollmod['nvotes'] = vb_number_format($pollmod['nvotes']);

		($hook = vBulletinHook::fetch_hook('vba_cmps_module_currentpoll_end')) ? eval($hook) : false;

		eval('$home[$mods[\'modid\']][\'content\'] .= "' . fetch_template('adv_portal_poll') . '";');
	}
}

unset($pollbits, $pollmod);

?>