<?php
// ++=========================================================================++
// || vBadvanced CMPS v3.2.1 (vB 3.6 - vB 3.8) - 63458
// || © 2003-2009 vBadvanced.com - All Rights Reserved
// || This file may not be redistributed in whole or significant part.
// || http://vbadvanced.com
// || Downloaded 23:34, Sun Jul 26th 2009
// || 2405244_563458070293
// ++ ========================================================================++

if ($mod_options['portal_threads_maxthreads'])
{
	$dyna_expand_width = 150;

	if ($mod_options['portal_threads_showicon'])
	{
		$dyna_expand_width+= 30;
	}

	if ($mod_options['portal_threads_lastpost'])
	{
		$dyna_expand_width+= 140;
	}

	if ($mod_options['portal_threads_showforum'])
	{
		$dyna_expand_width += 80;
	}

	$recthread_tempname = ($mod_options['portal_threads_expanded'] == 1 OR ($mod_options['portal_threads_expanded'] == 2 AND $vba_colwidths[$vba_modcols[$modcol]] >= $dyna_expand_width)) ? 'exp' : 'lean';

	if ($recthread_tempname == 'exp')
	{
		eval('$home[$mods[\'modid\']][\'content\'] = "' . fetch_template('adv_portal_recthreads_exp_head') . '";');

		$mods['colspan'] = 4;

		if ($mod_options['portal_threads_lastpost'])
		{
			$mods['colspan']++;
		}

		if ($mod_options['portal_threads_showforum'])
		{
			$mods['colspan']++;
		}
	}

	$vba_threads_condition = '';

	// Threads & forums
	if (!empty($mod_options['portal_threadids']) AND is_array($mod_options['portal_threadids']))
	{
		$vba_threads_condition = 'AND (thread.threadid IN(' . implode(',', array_keys($mod_options['portal_threadids'])) . ')';
	}
	if (!empty($mod_options['portal_threads_forumids']) AND is_array($mod_options['portal_threads_forumids']))
	{
		$mods['inforums'] = $mod_options['portal_threads_forumids'];

		if ($mod_options['portal_applypermissions'])
		{
			$mods['inforums'] = array_diff($mods['inforums'], $adv_canviewforums);
		}
		if (empty($mods['inforums']))
		{
			if (empty($mod_options['portal_threadids']))
			{
				$mods['nodisplay'] = true;
			}
		}
		else
		{
			$vba_threads_condition .= iif(!empty($mod_options['portal_threadids']), ' OR thread', ' AND (thread') . '.forumid IN(' . implode(',', $mods['inforums']) . ')';
		}
	}

	// Add ) if we had forum or thread ids
	if ($vba_threads_condition)
	{
		$vba_threads_condition .= ')';
	}

	if (!$mods['inforums'] AND !empty($adv_canviewforums) AND $mod_options['portal_applypermissions'])
	{
		$vba_threads_condition .= ' AND thread.forumid NOT IN(' . implode(',', $adv_canviewforums) . ')';
	}

	$show['lastpost'] = $mod_options['portal_threads_lastpost'];

	if (!$mods['nodisplay'])
	{
		if ($mod_options['portal_threads_orderby'] == 'dateline')
		{
			$mod_options['portal_threads_orderby'] = 'thread.dateline';
		}

		if (!$mod_options['portal_threads_orderby'])
		{
			$mod_options['portal_threads_orderby'] = 'lastpost';
		}
		if (!$mod_options['portal_threads_direction'])
		{
			$mod_options['portal_threads_direction'] = 'DESC';
		}

		$markinglimit = (TIMENOW - ($vbulletin->options['markinglimit'] * 86400));

		// Set the last visit date to the marking limit so new threads will not be based on individual cookie
		if ($pages['name'] == 'home' AND !$vbulletin->userinfo['userid'] AND $vba_options['portal_guestcache'])
		{
			$vbulletin->userinfo['lastvisit'] = $markinglimit;
		}

		// Rating
		$oldforumratings = $foruminfo['allowratings'];
		$foruminfo['allowratings'] = $mod_options['portal_threads_showrating'];

		$rtrating_fields = '';
		if ($mod_options['portal_threads_showrating'] OR $mod_options['portal_threads_orderby'] == 'voteavg')
		{
			$rtrating_fields = 'IF(votenum >= ' . $vbulletin->options['showvotes'] . ', votenum, 0) AS votenum, IF(votenum >= ' . $vbulletin->options['showvotes'] . ' AND votenum != 0, votetotal / votenum, 0) AS voteavg, votetotal,';
		}

		$rthread_fields = '';
		$rthread_join = '';

		// Subscriptions
		if ($mod_options['portal_threads_showsubscribed'] AND $vbulletin->userinfo['userid'])
		{
			$rthread_fields .= ', NOT ISNULL(subscribethread.subscribethreadid) AS subscribed';
			$rthread_join .= ' LEFT JOIN ' . TABLE_PREFIX . 'subscribethread AS subscribethread ON (subscribethread.threadid = thread.threadid AND subscribethread.userid = ' . $vbulletin->userinfo['userid'] . ')';
		}

		// Thread Icon
		if ($mod_options['portal_threads_showicon'])
		{
			$rthread_fields .= ', thread.iconid AS threadiconid, iconpath AS threadiconpath';
			$rthread_join .= ' LEFT JOIN ' . TABLE_PREFIX . 'icon AS icon ON (icon.iconid = thread.iconid)';
		}

		// Preview
		if ($mod_options['portal_threads_showpreview'] AND $vbulletin->options['threadpreview'])
		{
			$rthread_fields .= ', post.pagetext AS preview';
			$rthread_join .= ' LEFT JOIN ' . TABLE_PREFIX . 'post AS post ON (post.postid = thread.firstpostid)';
		}

		// Database read marking
		if ($vbulletin->options['threadmarking'] AND $vbulletin->userinfo['userid'])
		{
			$rthread_fields .= ', threadread.readtime AS threadread, forumread.readtime AS forumread';
			$rthread_join .= ' LEFT JOIN ' . TABLE_PREFIX . 'threadread AS threadread ON (threadread.threadid = thread.threadid AND threadread.userid = ' . $vbulletin->userinfo['userid'] . ')
				LEFT JOIN ' . TABLE_PREFIX . 'forumread AS forumread ON (thread.forumid = forumread.forumid AND forumread.userid = ' . $vbulletin->userinfo['userid'] . ')';
		}

		// Attach paperclip
		if ($mod_options['portal_threads_showpaperclip'])
		{
			$rthread_fields .= ', thread.attach';
		}

		// Prefixes
		if (IS_VB_37 AND $mod_options['portal_threads_prefix'])
		{
			$rthread_fields .= ', thread.prefixid';
		}

		($hook = vBulletinHook::fetch_hook('vba_cmps_module_recthreads_start')) ? eval($hook) : false;

		$threads = $db->query_read("
			SELECT $rtrating_fields thread.threadid, thread.title, thread.replycount, postusername, postuserid, thread.dateline AS postdateline, IF(views <= thread.replycount, thread.replycount+1, views) AS views, thread.lastposter, thread.lastpost, thread.lastpostid, pollid, thread.forumid, thread.open, sticky
			$rthread_fields
			FROM " . TABLE_PREFIX . "thread as thread
			$rthread_join
			WHERE open != 10
				AND thread.visible = 1 " .
				iif($mod_options['portal_threads_cutoffdate'],
					'AND thread.lastpost > ' . (TIMENOW - $mod_options['portal_threads_cutoffdate'] * 86400)
				) .
				iif($ignusers,
					' AND thread.postuserid NOT IN(' . $ignusers . ')'
				) . "
				$vba_threads_condition
			ORDER BY $mod_options[portal_threads_orderby] $mod_options[portal_threads_direction]
			LIMIT $mod_options[portal_threads_maxthreads]
		");
		$mods['threadcount'] = $db->num_rows($threads);

		if ($mods['threadcount'])
		{
			require_once(DIR . '/includes/functions_forumdisplay.php');

			if ($mod_options['portal_threads_multipage'])
			{
				vba_template_alter('threadbit_pagelink',
					'"$address"',
					'"' . $vbulletin->options['bburl'] . '/$address"'
				);
			}

			// Table cell classes
			$bgclass = 'alt1';

			if ($show['lastpost'])
			{
				// Don't need a variable since it's after a known class, just switch
				exec_switch_bg();
			}

			if ($mod_options['portal_threads_replies'])
			{
				$class_reply = exec_switch_bg();
			}

			if ($mod_options['portal_threads_views'])
			{
				$class_view = exec_switch_bg();
			}

			if ($mod_options['portal_threads_showforum'])
			{
				$class_ftitle = exec_switch_bg();
			}

			$recthreads_comma = '';
			if ($mod_options['portal_threads_views'] AND $mod_options['portal_threads_replies'])
			{
				$recthreads_comma = ', ';
			}
		}

		while ($thread = $db->fetch_array($threads))
		{
			$bgclass = exec_switch_bg();

			if (!($adv_forumperms[$thread['forumid']] & $vbulletin->bf_ugp_forumpermissions['canviewthreads']))
			{
				$thread['preview'] = '';
			}
			else
			{
				// The vB preview function can be intensive w/ long strings, so let's try to save some overhead
				$thread['preview'] = substr($thread['preview'], 0, ($vbulletin->options['threadpreview'] * 10));
			}

			// Trim title
			if (strlen($thread['title']) > $mod_options['portal_threads_maxchars'] AND $mod_options['portal_threads_maxchars'])
			{
				$thread['title'] = fetch_trimmed_title($thread['title'], $mod_options['portal_threads_maxchars']);
			}

			// Thread prefix
			if ($thread['prefixid'])
			{
				$thread['prefix'] = $vbphrase['prefix_' . $thread['prefixid'] . '_title_rich'];
			}

			// Check for long words that may stretch the page
			if ($mod_options['portal_threads_maxwordchars'])
			{
				$thread['titlecheck'] = explode(' ', $thread['title']);

				if (!empty($thread['titlecheck']))
				{
					$thread['title'] = '';

					foreach ($thread['titlecheck'] AS $key => $word)
					{
						if (!$thread['titletrimmed'])
						{
							if (strlen($word) > $mod_options['portal_threads_maxwordchars'])
							{
								$word = fetch_trimmed_title($word, $mod_options['portal_threads_maxwordchars']);
								$thread['titletrimmed'] = true;
							}

							if ($thread['title'])
							{
								$thread['title'] .= ' ';
							}

							$thread['title'] .= $word;
						}
					}
				}
			}

			// Thread read marking
			if ($vbulletin->options['threadmarking'] AND $vbulletin->userinfo['userid'])
			{
				if ($thread['threadread'] < $thread['forumread'])
				{
					$thread['threadread'] = $thread['forumread'];
				}
			}
			// Check the cookie, but only if guest caching is not enabled
			else
			{
				if (!$vb_read_cookies[$thread['forumid']] AND (!$vba_options['portal_guestcache'] OR $pages['name'] != 'home'))
				{
					$vb_read_cookies[$thread['forumid']] = fetch_bbarray_cookie('forum_view', $thread['forumid']);
					$thread['threadread'] = ($vb_read_cookies[$thread['forumid']] > $vbulletin->userinfo['lastvisit']) ? $vb_read_cookies[$thread['forumid']] : $vbulletin->userinfo['lastvisit'];
				}
			}

			$thread = process_thread_array(
				$thread,
				$thread['threadread'],
				$mod_options['portal_threads_showicon']
			);

			// Rating
			$thread['rating'] = intval(round($thread['voteavg']));

			($hook = vBulletinHook::fetch_hook('vba_cmps_module_recthreadsbits')) ? eval($hook) : false;

			eval('$home[$mods[\'modid\']][\'content\'] .= "' . fetch_template('adv_portal_recthreads_' . $recthread_tempname) . '";');
	 	}
	}

	$db->free_result($threads);
	unset($thread);

	$foruminfo['allowratings'] = $oldforumratings;

	if (!$mods['threadcount'] OR $mods['nodisplay'])
	{
		$show['tablerow'] = true;

		$home[$mods['modid']]['content'] = construct_phrase($vbphrase['no_x_to_display'], $vbphrase['threads']);
	}

}

?>