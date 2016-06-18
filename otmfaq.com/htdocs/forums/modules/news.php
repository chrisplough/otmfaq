<?php
// ++=========================================================================++
// || vBadvanced CMPS v3.2.1 (vB 3.6 - vB 3.8) - 63458
// || © 2003-2009 vBadvanced.com - All Rights Reserved
// || This file may not be redistributed in whole or significant part.
// || http://vbadvanced.com
// || Downloaded 23:34, Sun Jul 26th 2009
// || 2405244_563458070293
// ++ ========================================================================++

$hasarchive = false;
if (!$newsprocessed[$mods['modid']])
{
	foreach ($modules AS $omodid)
	{
		$omods =& $vbulletin->adv_modules[$omodid];
		// Archive comes first
		if (($mods['parent'] AND $mods['parent'] == $omods['identifier']) OR (!$mods['parent'] AND $mods['modid'] == $omods['modid']))
		{
			$newsmod = $vbulletin->adv_modules[$omods['modid']];
		}
		else if (($mods['parent'] AND $mods['modid'] == $omods['modid']) OR (!$mods['parent'] AND $omods['parent'] == $mods['identifier']))
		{
			$hasarchive = true;
			$archivemod = $vbulletin->adv_modules[$omods['modid']];
		}
	}
}

$currentmodule = ($mods['modid'] == $newsmod['modid']) ? 'news' : 'archive';

if (!$newsprocessed[$newsmod['modid']] AND (!$newsprocessed[$archivemod['modid']] OR !$hasarchive))
{
	require_once(DIR . '/includes/functions_forumdisplay.php');

	$newsprocessed[$newsmod['modid']] = true;
	$newsprocessed[$archivemod['modid']] = true;

	if ($archivemod['modid'] AND $newsmod['modid'])
	{
		$mod_options = array_merge($cmps_options['adv_portal_' . $archivemod['identifier']], $cmps_options['adv_portal_' . $newsmod['identifier']]);
	}

	if ($mod_options['portal_news_maxposts'] OR $mod_options['portal_news_enablearchive'])
	{
		$newslimit = 'LIMIT ' . ($mod_options['portal_news_maxposts'] + $mod_options['portal_news_enablearchive']);

		if ($mod_options['portal_news_enablearchive'])
		{
			// Dynamic template
			$dyna_expand_width = 250;

			if ($mod_options['portal_archive_showicon'])
			{
				$dyna_expand_width+= 30;
			}

			if ($mod_options['portal_newsarchive_lastpost'])
			{
				$dyna_expand_width+= 140;
			}

			if ($mod_options['portal_newsarchive_showforum'])
			{
				$dyna_expand_width += 80;
			}

			$archive_tempname = ($mod_options['portal_archive_expanded'] == 1 OR ($mod_options['portal_archive_expanded'] == 2 AND $vba_colwidths[$vba_modcols[$archivemod['modcol']]] >= $dyna_expand_width)) ? 'exp' : 'lean';

			$archive_wrappername = ($archivemod['altshell']) ? $archivemod['altshell'] : 'adv_portal_module_wrapper';
		}

		$news_wrappername = ($newsmod['altshell']) ? $newsmod['altshell'] : 'adv_portal_module_wrapper';
	}

	// Rating query
	$ratingsql = '';
	if ($mod_options['portal_news_showrating'] OR $mod_options['portal_archive_showrating'] OR $mod_options['portal_news_orderby'] == 'voteavg')
	{
		$ratingsql = 'IF(votenum >= ' . $vbulletin->options['showvotes'] . ', votenum, 0) AS votenum, IF(votenum >= ' . $vbulletin->options['showvotes'] . ' AND votenum != 0, votetotal / votenum, 0) AS voteavg, votetotal,';
	}
	$oldforumratings = $foruminfo['allowratings'];
	$foruminfo['allowratings'] = ($mod_options['portal_news_showrating'] OR $mod_options['portal_archive_showrating']) ? true : false;

	$newspagevar = $newsmod['identifier'] . '_page';

	// Pagination limits
	if ($mod_options['portal_news_threadsperpage'])
	{
		$vbulletin->input->clean_gpc('r', $newspagevar, TYPE_INT);

		$vbulletin->GPC[$newspagevar] = abs($vbulletin->GPC[$newspagevar]);

		if ($vbulletin->GPC[$newspagevar])
		{
			if ($vbulletin->GPC[$newspagevar] > $mod_options['portal_news_threadsperpage'])
			{
				$vbulletin->GPC[$newspagevar] = $mod_options['portal_news_threadsperpage'];
			}

			$newslimit = 'LIMIT ' . (($vbulletin->GPC[$newspagevar] - 1) * $mod_options['portal_news_maxposts']) . ', ' . ($mod_options['portal_news_maxposts'] + $mod_options['portal_news_enablearchive']);
		}
	}

	$newstids = array();
	$newspids = array();

	// Specific threads selected
	if (!empty($mod_options['portal_threadids']) AND is_array($mod_options['portal_threadids']))
	{
		$newstids = array_keys($mod_options['portal_threadids']);
		$newspids = array_values($mod_options['portal_threadids']);
	}

	$limitapplied = false;
	$threadsqueried = false;

	// Forumids
	if (!empty($mod_options['portal_news_forumid']) AND is_array($mod_options['portal_news_forumid']))
	{
		$limitapplied = true;
		
		$mods['inforums'] = $mod_options['portal_news_forumid'];

		if ($mod_options['portal_applypermissions'])
		{
			$mods['inforums'] = array_diff($mods['inforums'], $adv_canviewforumscontent);
		}

		if (!empty($mods['inforums']))
		{
			$getnewsids = $db->query_read("
				SELECT $ratingsql threadid, firstpostid
				FROM " . TABLE_PREFIX . "thread AS thread
				WHERE visible = 1
					AND open != 10
					AND (thread.forumid IN(" . implode(',', $mods['inforums']) . ")
					" . iif(!empty($newstids), ' OR threadid IN(' . implode(',', $newstids) . ')') . "
					)
					" . iif($mod_options['portal_news_cutoffdate'], 'AND thread.dateline > ' . (TIMENOW - ($mod_options['portal_news_cutoffdate'] * 86400))) . "
				ORDER BY " . iif($mod_options['portal_news_sticky'], 'sticky DESC,') . iif($mod_options['portal_news_orderby'] == 'postdateline', 'dateline', $mod_options['portal_news_orderby']) . " $mod_options[portal_news_direction]
				$newslimit
			");
			
			// Reset the array here so the threads can be combined and to prevent pagination problems
			$newstids = array();
			$newspids = array();
			while ($ids = $db->fetch_array($getnewsids))
			{
				$newstids[] = $ids['threadid'];
				$newspids[] = $ids['firstpostid'];
			}
		}
	}

	if (!empty($newstids))
	{
		// Get Attachments
		if ($mod_options['portal_news_showattachments'] OR $mod_options['portal_news_bbcode_attach'])
		{
			$nattachcache = array();
			$getnattach = $db->query_read("
				SELECT attachmentid, filename, filesize, dateline, postid, IF(thumbnail_filesize > 0, 1, 0) AS hasthumbnail, counter, attachment.thumbnail, attachment.thumbnail_dateline, LENGTH(attachment.thumbnail) AS thumbnailsize, newwindow, visible
				FROM " . TABLE_PREFIX . "attachment AS attachment
				LEFT JOIN " . TABLE_PREFIX . "attachmenttype AS attachmenttype ON (attachment.extension = attachmenttype.extension)
				WHERE postid IN(" . implode(',', $newspids) . ")
					AND visible = 1
				" . iif($mod_options['portal_news_showattachments'] != 2, 'GROUP BY attachment.postid') . "
				ORDER BY attachmentid
			");
			while ($nattach = $db->fetch_array($getnattach))
			{
				$nattach['truedateline'] = $nattach['dateline'];

				// only way to add &cmps=1 to [attach] tags 
				$nattach['dateline'] = $nattach['dateline'] . '&amp;cmps=1';
				$nattachcache[$nattach['postid']][$nattach['attachmentid']] = $nattach;
			}
		}

		$newscount = 0;

		$show['lastpost'] = $mod_options['portal_newsarchive_lastpost'];

		if (!$vbulletin->userinfo['showimages'])
		{
			$mod_options['portal_news_enablevbimage'] = 0;
		}

		if ($mod_options['portal_archive_multipage'])
		{
			vba_template_alter('threadbit_pagelink',
				'"$address"',
				'"' . $vbulletin->options['bburl'] . '/$address"'
			);
		}

		$parsedposts = '';
		$vba_news_fields = '';
		$vba_news_join = '';

		// Smilies
		if ($mod_options['portal_news_enablesmilies'])
		{
			$vba_news_fields .= ', allowsmilie';
		}

		// Post cache
		if ($mod_options['portal_news_postcache'])
		{
			$vba_news_fields .= ', pagetext_html, postparsed.hasimages';
			$vba_news_join .= ' LEFT JOIN ' . TABLE_PREFIX . 'postparsed AS postparsed ON (postparsed.postid = post.postid AND postparsed.styleid = ' . intval(STYLEID) . ' AND postparsed.languageid = ' . intval(LANGUAGEID) . ')';
		}

		// Signature
		if ($mod_options['portal_news_showsignature'])
		{
			$vba_news_fields .= ', showsignature, sigparsed.signatureparsed, sigparsed.hasimages AS sighasimages, sigpic.userid AS sigpic, sigpic.dateline AS sigpicdateline, sigpic.width AS sigpicwidth, sigpic.height AS sigpicheight, usertextfield.signature';
			$vba_news_join .= '
				LEFT JOIN ' . TABLE_PREFIX . 'sigparsed AS sigparsed ON (sigparsed.userid = user.userid AND sigparsed.styleid = ' . intval(STYLEID) . ' AND sigparsed.languageid = ' . intval(LANGUAGEID) . ')
				LEFT JOIN ' . TABLE_PREFIX . 'sigpic AS sigpic ON (sigpic.userid = post.userid)
				LEFT JOIN ' . TABLE_PREFIX . 'usertextfield AS usertextfield ON (user.userid = usertextfield.userid)';

			if ($vbulletin->options['usefileavatar'])
			{
				$vbulletin->options['sigpicurl'] = $vbulletin->options['bburl'] . '/' . $vbulletin->options['sigpicurl'];
			}
		}

		// Icons
		if ($mod_options['portal_news_showicon'] OR $mod_options['portal_archive_showicon'])
		{
			$vba_news_fields .= ', thread.iconid AS threadiconid, iconpath AS threadiconpath';
			$vba_news_join .= ' LEFT JOIN ' . TABLE_PREFIX . 'icon AS icon ON (icon.iconid = thread.iconid)';
		}

		// Avatar
		if ($mod_options['portal_news_showavatar'])
		{
			$vba_news_fields .= ', avatarpath, NOT ISNULL(customavatar.userid) AS hascustom, customavatar.dateline AS avatardateline, avatarrevision';
			$vba_news_join .= ' LEFT JOIN ' . TABLE_PREFIX . 'avatar as avatar ON (avatar.avatarid = user.avatarid)
				LEFT JOIN ' . TABLE_PREFIX . 'customavatar as customavatar ON (customavatar.userid = user.userid)';
		}

		// Subscribed icon
		if (($mod_options['portal_news_showsubscribed'] OR $mod_options['portal_archive_showsubscribed']) AND $vbulletin->userinfo['userid'])
		{
			$vba_news_fields .= ', NOT ISNULL(subscribethread.subscribethreadid) AS subscribed';
			$vba_news_join .= ' LEFT JOIN ' . TABLE_PREFIX . 'subscribethread AS subscribethread ON (subscribethread.threadid = thread.threadid AND subscribethread.userid = \''. $vbulletin->userinfo['userid'] . '\')';
		}

		// Prefixes
		if (IS_VB_37 AND $mod_options['portal_news_prefix'])
		{
			$vba_news_fields .= ', thread.prefixid';
		}

		// Figure out the ordering query
		if ($mod_options['portal_news_orderby'] != 'voteavg')
		{
			if ($mod_options['portal_news_orderby'] == 'postdateline')
			{
				$mod_options['portal_news_orderby'] = 'dateline';
			}
			
			$mod_options['portal_news_orderby'] = 'thread.' . $mod_options['portal_news_orderby'];
		}

		$lightboxid = '';
		if (IS_VB_38)
		{
			$bbcode_parser->containerid = 'vba_news' . $newsmod['modid'];
			$lightboxid = '_' . $bbcode_parser->containerid;
		}

		$origsettings_viewattachedimages = $vbulletin->options['viewattachedimages'];
		$origsettings_attachthumbs = $vbulletin->options['attachthumbs'];

		($hook = vBulletinHook::fetch_hook('vba_cmps_module_news_start')) ? eval($hook) : false;

		$getnews = $db->query_read("
			SELECT $ratingsql user.*, thread.threadid, post.title, thread.replycount, postusername, postuserid, thread.dateline AS postdateline, sticky, thread.attach, thread.lastpostid, thread.lastposter, thread.lastpost, IF(views<=thread.replycount, thread.replycount+1, views) AS views, thread.forumid, post.postid, pagetext
			$vba_news_fields
			FROM " . TABLE_PREFIX . "thread AS thread
			LEFT JOIN " . TABLE_PREFIX . "post AS post ON (post.postid = thread.firstpostid)
			LEFT JOIN " . TABLE_PREFIX . "user AS user ON (user.userid = post.userid)
			$vba_news_join
			WHERE thread.threadid IN(" . implode(',', $newstids) . ")
			" . iif(!$threadsqueried AND $mod_options['portal_news_cutoffdate'], 'AND thread.dateline > ' . (TIMENOW - ($mod_options['portal_news_cutoffdate'] * 86400))) . "
			" . iif($ignusers, 'AND thread.postuserid NOT IN(' . $ignusers . ')') . "
			" . iif($mod_options['portal_applypermissions'], $forumperms_query) . "
			ORDER BY " . iif($mod_options['portal_news_sticky'], 'sticky DESC, ') . $mod_options['portal_news_orderby'] . " $mod_options[portal_news_direction]
			" . iif($limitapplied, 'LIMIT ' . ($mod_options['portal_news_maxposts'] + $mod_options['portal_news_enablearchive']), $newslimit) . "
		");

		$newsrows = $db->num_rows($getnews);

		if ($newsrows > $mod_options['portal_news_maxposts'])
		{
			if ($archive_tempname == 'exp')
			{
				// Archive header
				eval('$newsarchivebits = "' . fetch_template('adv_portal_archivebits_exp_head') . '";');

				// Table cell classes
				$bgclass = 'alt1';

				if ($show['lastpost'])
				{
					// Don't need a variable since it's after a known class, just switch
					exec_switch_bg();
				}

				if ($mod_options['portal_archive_showreplies'])
				{
					$class_reply = exec_switch_bg();
				}

				if ($mod_options['portal_archive_showviews'])
				{
					$class_view = exec_switch_bg();
				}

				if ($mod_options['portal_newsarchive_showforum'])
				{
					$class_ftitle = exec_switch_bg();
				}
			}
			// Lean template
			else
			{
				$archive_rep_view_comma = ($mod_options['portal_archive_showreplies'] AND $mod_options['portal_archive_showviews']) ? ',' : '';
			}
		}

		while ($news = $db->fetch_array($getnews))
		{
			$newscount++;

			/* Used in a template condition since a count without formatting is needed */
			$news['reply_noformat'] = $news['replycount'];

			$news['dateposted'] = vbdate($mod_options['portal_news_dateformat'], $news['postdateline'], '', '');

			// Grab the preview, but only for the archive
			if ($archivemod['modid'] AND $mod_options['portal_news_archivepreview'] AND $newscount > $mod_options['portal_news_maxposts'] AND $vbulletin->options['threadpreview'] AND ($adv_forumperms[$news['forumid']] & $vbulletin->bf_ugp_forumpermissions['canviewthreads']))
			{
				// The vB preview function can be intensive w/ long strings, so let's try to save some overhead
				$news['preview'] = substr($news['pagetext'], 0, ($vbulletin->options['threadpreview'] * 10));
			}

			$news = process_thread_array($news, '', iif($newscount <= $mod_options['portal_news_maxposts'], $mod_options['portal_news_showicon'], $mod_options['portal_archive_showicon']));

			// Rating
			$news['rating'] = intval(round($news['voteavg']));

			// ##### Main News Module
			if ($newscount <= $mod_options['portal_news_maxposts'] AND $newsmod['modid'])
			{
				// Signature
				$show['signature'] = false;
				if ($mod_options['portal_news_showsignature'] AND $news['showsignature'] AND $vbulletin->userinfo['showsignatures'] AND $news['signature'])
				{
					$show['signature'] = true;

					$bbcode_parser->set_parse_userinfo($news, cache_permissions($news, false));

					$news['signature'] = $bbcode_parser->parse(
						$news['signature'],
						'signature',
						true,
						false,
						$news['signatureparsed'],
						$news['sighasimages'],
						false
					);

					if ($bbcode_parser->parse_userinfo['sigpic'] AND !$vbulletin->options['usefileavatar'])
					{
						$news['signature'] = str_replace(
							'"image.php',
							'"' . $vbulletin->options['bburl'] . '/image.php',
							$news['signature']
						);
					}
				}

				// News Avatar
				if ($mod_options['portal_news_showavatar'] AND $vbulletin->userinfo['showavatars'])
				{
					if ($news['avatarpath'])
					{
						$news['avatarpath'] = $vbulletin->options['bburl'] . '/' . $news['avatarpath'];
					}
					else if ($news['hascustom'])
					{
						$news['avatarpath'] = $vbulletin->options['bburl'] . '/';

						if ($vbulletin->options['usefileavatar'])
						{
							$news['avatarpath'] .= $vbulletin->options['avatarurl'] . '/avatar' . $news['postuserid']. '_' . $news['avatarrevision'] . '.gif';
						}
						else
						{
							$news['avatarpath'] .= 'image.php?' . $session['sessionurl'] . 'u=' . $news['postuserid'] . '&amp;dateline=' . $news['avatardateline'];
						}
					}
				}

				// Attach paperclip
				if (!$mod_options['portal_news_attachpaperclip'])
				{
					$news['attach'] = 0;
					$show['paperclip'] = 0;
				}

				// Forum perms
				if (empty($newsforumperms[$news['forumid']]))
				{
					$newsforumperms[$news['forumid']] = $adv_forumperms[$news['forumid']];
				}

				$vbulletin->options['viewattachedimages'] = $origsettings_viewattachedimages;
				$vbulletin->options['attachthumbs'] = $origsettings_attachthumbs;

				if (!($newsforumperms[$news['forumid']] & $vbulletin->bf_ugp_forumpermissions['cangetattachment']) OR !($newsforumperms[$news['forumid']] & $vbulletin->bf_ugp_forumpermissions['canviewthreads']))
				{
					$vbulletin->options['viewattachedimages'] = 0;
					$vbulletin->options['attachthumbs'] = 0;
				}

				if ($mod_options['portal_news_bbcode_attach'])
				{
					$bbcode_parser->attachments =& $nattachcache[$news['postid']];
				}

				$bbcode_parser->cached = array();

				// Use the post cache
				if ($mod_options['portal_news_postcache'])
				{
					$news['pagetext_html'] = $bbcode_parser->parse(
						$news['pagetext'],
						$news['forumid'],
						$news['allowsmilie'],
						true,
						$news['pagetext_html'],
						$news['hasimages'],
						true
					);

					// Insert the parsed BB code
					if ($bbcode_parser->cached['text'] AND $vbulletin->options['cachemaxage'])
					{
						$parsedposts .= '(
							' . $news['postid'] . ',
							' . TIMENOW . ',
							' . intval(STYLEID) . ',
							' . intval(LANGUAGEID) . ',
							' . $bbcode_parser->cached['has_images'] . ',
							\'' . $db->escape_string($bbcode_parser->cached['text']) . '\'
						), ';
					}

					$news['message'] = $news['pagetext_html'];
				}
				else
				{
					$news['message'] = $bbcode_parser->do_parse(
						$news['pagetext'],
						$mod_options['portal_news_enablehtml'],
						$news['allowsmilie'],
						$mod_options['portal_news_enablevbcode'],
						$mod_options['portal_news_enablevbimage']
					);

					$news['pagetext_html'] = $news['message'];
				}


				// #### Strip characters and add "read more"
				if ($mod_options['portal_news_maxchars'] AND strlen($news['message']) > $mod_options['portal_news_maxchars'])
				{
					$trimmedlength = strrpos(substr($news['message'], 0, $mod_options['portal_news_maxchars']), ' ');
					$news['message'] = substr($news['message'], 0, $trimmedlength);

					// Make sure we're not cutting off in the middle of tags

					// ##### <img>
					$lastimage = strripos($news['message'], '<img');
					if ($lastimage !== false)
					{
						$imagecheck = substr($news['message'], $lastimage);

						// Don't have the end, so find it
						if (strpos($imagecheck, ' />') === false)
						{
							$remainstring = substr($news['pagetext_html'], $trimmedlength);
							$endimgpos = strpos($remainstring, ' />') + 3;

							if ($endimgpos != false)
							{
								$news['message'] .= substr($remainstring, 0, $endimgpos);
								$trimmedlength += $endimgpos;
							}
						}
					}

					// ##### <a>
					$lasthref = strripos($news['message'], '<a');
					if ($lasthref !== false)
					{
						$hrefcheck = substr($news['message'], $lasthref);

						// Don't have the end, so just strip the tag
						if (strpos($hrefcheck, '>') === false)
						{
							$news['message'] = substr($news['message'], 0, $lasthref);
							$trimmedlength -= $lasthref;
						}
					}

					// ##### <br />
					$lastbr = strripos($news['message'], '<br');
					if ($lastbr !== false)
					{
						$brcheck = substr($news['message'], $lastbr);

						// Don't have the end, so add it
						if (strpos($brcheck, ' />') === false)
						{
							$news['message'] .= ' />';
							$trimmedlength += 3;
						}
					}

					// ##### <table>
					$lasttable = strripos($news['message'], '<table');
					if ($lasttable !== false)
					{
						$tablecheck = substr($news['message'], $lasttable);

						// Don't have the end, so strip the tag
						if (strpos($tablecheck, '>') === false)
						{
							$news['message'] = substr($news['message'], 0, $lasttable);
							$trimmedlength -= $lasttable;
						}
					}

					// ##### <td>
					$lasttd = strripos($news['message'], '<td');
					if ($lasttd !== false)
					{
						$tdcheck = substr($news['message'], $lasttd);

						// Don't have the end, so find it
						if (strpos($tdcheck, '>') === false)
						{
							$remainstring = substr($news['pagetext_html'], $trimmedlength);

							$endtdpos = strpos($remainstring, '>') + 1;

							if ($endtdpos != false)
							{
								$news['message'] .= substr($remainstring, 0, $endtdpos);
								$trimmedlength += $endtdpos;
							}
						}
					}

					// ##### <object>
					$lastobject = strripos($news['message'], '<object');
					if ($lastobject !== false)
					{
						$objectcheck = substr($news['message'], $lastobject);

						// Don't have the end, so find it
						if (strpos($objectcheck, '</object>') === false)
						{
							$remainstring = substr($news['pagetext_html'], $trimmedlength);

							$endobjectpos = strpos($remainstring, '</object>') + 9;

							if ($endobjectpos != false)
							{
								$news['message'] .= substr($remainstring, 0, $endobjectpos);
								$trimmedlength += $endobjectpos;
							}
						}
					}

					// ##### Comment tags
					$lastcom = strripos($news['message'], '<!');
					if ($lastcom)
					{
						$comcheck = substr($news['message'], $lastcom);

						// Don't have the end, so add it
						if (strpos($comcheck, '-->') === false)
						{
							$news['message'] .= '-->';
							$trimmedlength += 3;
						}
					}

					// ##### <font>
					$lastfont = strripos($news['message'], '<font');
					if ($lastfont !== false)
					{
						$fontcheck = substr($news['message'], $lastfont);

						if (strpos($fontcheck, '>') === false)
						{
							$news['message'] = substr($news['message'], 0, $lastfont);
							$trimmedlength -= $lastfont;
						}
					}

					// ##### Now check normal HTML tags
					preg_match_all("/(<([\w]+)[^>]*>)/", $news['message'], $opentags);
					preg_match_all("/(<\/([\w]+)[^>]*>)/", $news['message'], $closetags);

					$opentags = array_count_values(array_reverse($opentags[2]));
					$closetags = array_count_values(array_reverse($closetags[2]));

					foreach ($opentags AS $otag => $value)
					{
						$otag = trim($otag);

						switch ($otag)
						{
							case 'br':
							case 'hr':
							case 'img':
							case 'param':
								continue;

							default:

							if ($closetags[$otag] != $value)
							{
								for ($i = $closetags[$otag]; $i < $value; $i++)
								{
									$news['message'] .= '</' . $otag . '>';
								}
							}
						}
					}

					$news['message'] .= '...' . construct_phrase($vbphrase['read_more'], $vbulletin->options['bburl'], $news['threadid'], $vbulletin->session->vars['sessionurl']);
				}

				// Edit Button
				$show['editbutton'] = ((($newsforumperms[$news['forumid']] & $vbulletin->bf_ugp_forumpermissions['caneditpost']) AND $vbulletin->userinfo['userid'] == $news['userid']) OR can_moderate($news['forumid'], 'caneditposts')) ? true : false;

				$show['replybutton'] = (($newsforumperms[$news['forumid']] & $vbulletin->bf_ugp_forumpermissions['canreplyothers']) AND $mod_options['portal_news_allowreplies']) ? true : false;

				// ##### Attachments
				if ($nattachcache[$news['postid']] AND $mod_options['portal_news_showattachments'])
				{
					foreach ($nattachcache[$news['postid']] AS $attachid => $attachment)
					{
						$attachment['filesize'] = vb_number_format($attachment['filesize'], 1, true);

						($hook = vBulletinHook::fetch_hook('vba_cmps_module_newsbits_attachmentbits')) ? eval($hook) : false;

						if ($attachment['hasthumbnail'] AND $vbulletin->options['attachthumbs'] AND ($newsforumperms[$news['forumid']] & $vbulletin->bf_ugp_forumpermissions['cangetattachment']) AND ($newsforumperms[$news['forumid']] & $vbulletin->bf_ugp_forumpermissions['canviewthreads']) AND $vbulletin->userinfo['showimages'])
						{
							$show['newwindow'] = $attachment['newwindow'];
							eval('$news[\'attachment\'] .= "' . fetch_template('adv_portal_newsbits_attach_thumb') . '";');
						}
						else
						{
							$attachment['attachmentextension'] = file_extension($attachment['filename']);
							eval('$news[\'attachment\'] .= "' . fetch_template('adv_portal_newsbits_attach_inline') . '";');
						}
					}
				}

				// Thread prefix
				if ($news['prefixid'])
				{
					$news['prefix'] = $vbphrase['prefix_' . $news['prefixid'] . '_title_rich'];
				}

				// Icon
				$blockbullet = $vba_style['portal_blockbullet'];
				if ($mod_options['portal_news_showicon'])
				{
					$vba_style['portal_blockbullet'] = '';
					if ($news['threadiconpath'])
					{
						$vba_style['portal_blockbullet'] = '<img alt="" border="0" src="' . $news['threadiconpath'] . '" title="' . $news['threadicontitle'] . '" class="inlineimg" />';
					}
				}

				($hook = vBulletinHook::fetch_hook('vba_cmps_module_newsbits')) ? eval($hook) : false;

				// Separate the posts
				if ($mod_options['portal_news_legacy'])
				{
					$mods['title'] = ($news['prefix'] ? $news['prefix'] : '') . ' <a href="' . $vbulletin->options['bburl'] . '/showthread.php?' . $vbulletin->session->vars['sessionurl'] . 't=' . $news['threadid'] . '">' . $news['title'] . '</a>';

					$mods['collapse'] = $newsmod['modid'] . '_' . $news['threadid'];

					eval('$modulehtml = "' . fetch_template('adv_portal_newsbits') . '";');

					if ($newsmod['useshell'] & $vba_shellint['enable'])
					{
						if ($newsmod['useshell'] & $vba_shellint['collapse'])
						{
							$modcollapse = 'display: none';
							$modimgcollapse = '_collapsed';
						}
						else
						{
							$modcollapse = $vbcollapse['collapseobj_module_' . $mods['collapse']];
							$modimgcollapse = $vbcollapse['collapseimg_module_' . $mods['collapse']];
						}
						eval('$home[$newsmod[\'modid\']][\'content\'] .= "' . fetch_template($news_wrappername) . '";');
					}
					else
					{
						$home[$newsmod['modid']]['content'] .= $modulehtml;
					}
				}
				// No separation
				else
				{
					eval('$newsbits .= "' . fetch_template('adv_portal_newsbits') . '";');
				}

				$vba_style['portal_blockbullet'] = $blockbullet;
			}

			// ##### News Archive
			else if ($archivemod['modid'])
			{
				$mods['modcol'] = $archivemod['modcol'];
				if ($mods['modcol'] != 1)
				{
					$bgclass = exec_switch_bg();
				}

				if (strlen($news['title']) > $mod_options['portal_archive_maxchars'] AND $mod_options['portal_archive_maxchars'])
				{
					$news['title'] = fetch_trimmed_title($news['title'], $mod_options['portal_archive_maxchars']);
				}

				// Attach paperclip
				if (!$mod_options['portal_archive_attachpaperclip'])
				{
					$news['attach'] = 0;
					$show['paperclip'] = 0;
				}

				($hook = vBulletinHook::fetch_hook('vba_cmps_module_news_archivebits')) ? eval($hook) : false;

				eval('$newsarchivebits .= "' . fetch_template('adv_portal_archivebits_' . $archive_tempname) . '";');
			}
		}

		$vbulletin->options['viewattachedimages'] = $origsettings_viewattachedimages;
		$vbulletin->options['attachthumbs'] = $origsettings_attachthumbs;

		$db->free_result($getnews);

		// ##### Archive (module shell)
		if ($archivemod['modid'] AND ($archivemod['useshell'] & $vba_shellint['enable']) AND $newsarchivebits)
		{
			$mods['link'] = '';
			if ($archivemod['link'])
			{
				eval('$mods[\'link\'] = "' . addslashes($archivemod['link']) . '";');
			}

			$mods['colspan'] = $archivemod['colspan'];
			$mods['title'] = $archivemod['title'];
			$mods['collapse'] = $archivemod['modid'];

			if ($archivemod['useshell'] & $vba_shellint['collapse'])
			{
				$modcollapse = 'display: none';
				$modimgcollapse = '_collapsed';
			}
			else
			{
				$modcollapse = $vbcollapse['collapseobj_module_' . $archivemod['modid']];
				$modimgcollapse = $vbcollapse['collapseimg_module_' . $archivemod['collapse']];
			}

			$modulehtml =& $newsarchivebits;

			eval('$home[$archivemod[\'modid\']][\'content\'] = "' . fetch_template($archive_wrappername) . '";');
		}
		else if ($archivemod['modid'])
		{
			$home[$archivemod['modid']]['content'] =& $newsarchivebits;
		}

		// ##### Process news module if not splitting posts and we have no module shell
		if ($newsbits AND !$mod_options['portal_news_legacy'])
		{
			$mods['modcol'] = $newsmod['modcol'];
			$mods['colspan'] = $newsmod['colspan'];
			$mods['title'] = $newsmod['title'];
			$mods['collapse'] = $newsmod['modid'];
			$modcollapse = $vbcollapse['collapseobj_module_' . $newsmod['modid']];
			$modimgcollapse = $vbcollapse['collapseimg_module_' . $newsmod['modid']];

			$modulehtml = $newsbits;

			if ($newsmod['useshell'])
			{
				eval('$home[$newsmod[\'modid\']][\'content\'] = "' . fetch_template($news_wrappername) . '";');
			}
			else
			{
				$home[$newsmod['modid']]['content'] = $modulehtml;
			}
		}

		// ##### Lightbox stuff
		if ($vbulletin->options['lightboxenabled'])
		{
			if (!defined('lightbox_js'))
			{
				$headinclude .= "\r\n<script type=\"text/javascript\" src=\"" . $vbulletin->options['bburl'] . '/clientscript/vbulletin_lightbox.js?v=' . $vbulletin->options['simpleversion'] . '"></script>';
				define('lightbox_js', true);
			}

			$home[$newsmod['modid']]['content'] = '<div id="vba_news' . $newsmod['modid'] . '">' . $home[$newsmod['modid']]['content'] . '</div>
	<script type="text/javascript">
	<!--
	vBulletin.register_control("vB_Lightbox_Container", "vba_news' . $newsmod['modid'] . '", ' . $vbulletin->options['lightboxenabled'] . ');
	//-->
	</script>';
		}

		// ##### Pagination
		if ($mod_options['portal_news_threadsperpage'] AND (($newsrows >= ($mod_options['portal_news_maxposts'] + $mod_options['portal_news_enablearchive'])) OR $vbulletin->GPC[$newspagevar]))
		{
			$vba_news_where = 'visible = 1 AND open != 10';

			if (!empty($mod_options['portal_threadids']))
			{
				$vba_news_where .= ' AND (threadid IN(' . implode(',', $newstids) . ')';
			}

			if (!empty($mods['inforums']))
			{
				$vba_news_where .= iif(!empty($mod_options['portal_threadids']), ' OR', ' AND') . ' forumid IN(' . implode(', ', $mods['inforums']) . ')';
			}

			if (!empty($mod_options['portal_threadids']))
			{
				$vba_news_where .= ')';
			}

			if ($mod_options['portal_news_cutoffdate'])
			{
				$vba_news_where .= ' AND dateline > ' . (TIMENOW - ($mod_options['portal_news_cutoffdate'] * 86400));
			}

			if ($ignusers)
			{
				$vba_news_where .= ' AND postuserid NOT IN(' . $ignusers . ')';
			}

			if ($mod_options['portal_applypermissions'])
			{
				$vba_news_where .= $forumperms_query;
			}

			$newscount = $db->query_first("
				SELECT COUNT(*) AS count
				FROM " . TABLE_PREFIX . "thread
				WHERE $vba_news_where
			");

			$oldshowpopups = $show['popups'];
			$show['popups'] = false;

			$newspagenav = construct_page_nav(
				max($vbulletin->GPC[$newspagevar], 1),
				$mod_options['portal_news_maxposts'],
				min($newscount['count'], ($mod_options['portal_news_maxposts'] * $mod_options['portal_news_threadsperpage'])),
				$vba_options['portal_homeurl'] . '?' . $vba_options['portal_pagevar'] . '=' . $pages['name']
			);

			$show['popups'] = $oldshowpopups;

			$home[$newsmod['modid']]['content'] .= '<div style="padding-bottom: ' . $vba_style['portal_vspace'] . 'px">' . str_replace(
				'&amp;page=',
				'&amp;' . $newspagevar . '=',
				$newspagenav
			) . '</div>';
		}

		// Post cache
		if ($parsedposts)
		{
			$db->query_write("
				REPLACE INTO " . TABLE_PREFIX . "postparsed
					(postid, dateline, styleid, languageid, hasimages, pagetext_html)
				VALUES
					" . substr($parsedposts, 0, (strlen($parsedposts) - 2))
			);
		}
	}

	$foruminfo['allowratings'] = $oldforumratings;
	unset($newsbits, $newsarchivebits, $news);

	// reset the $mods variable
	if ($currentmodule == 'archive' AND !empty($archivemod))
	{
		$mods = $archivemod;
	}
	else if ($currentmodule == 'archive' AND !empty($newsmod))
	{
		$mods = $newsmod;
	}

}

$mods['noshell'] = true;

?>