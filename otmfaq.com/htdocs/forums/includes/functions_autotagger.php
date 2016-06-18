<?php
/*======================================================================*\
|| #################################################################### ||
|| # Automatic Thread Tagger                                          # ||
|| # ---------------------------------------------------------------- # ||
|| # Originally created by MrEyes (1.0 Beta 3)                        # ||
|| # Copyright 2008-2009 Marius Czyz. All Rights Reserved.            # ||
|| #################################################################### ||
\*======================================================================*/ 

function DeleteThreadTags($thread)
{
	global $vbulletin;
	
	if (intval($thread['threadid']) == 0) 
	{
		return;
	}
	
	$tags = $vbulletin->db->query_read("SELECT 
		t.tagid
		FROM " . TABLE_PREFIX . "tagthread as t
		WHERE t.threadid=".$thread['threadid']." AND autotag=1
		"); 

	while ($tag = $vbulletin->db->fetch_array($tags))
	{
		$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "tag where autotag=1 AND tagid=".$tag['tagid']);
	}
	
	$vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "tagthread WHERE autotag=1 AND threadid=".$thread['threadid']);
	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "thread SET autoskip=0 WHERE threadid=".$thread['threadid']);
}

function ProcessThread($thread)
{
	global $vbulletin, $vbphrase;
	
	if (intval($thread['threadid']) == 0) 
	{
		return;
	}
	
	//RSS?
	$checkrss = $vbulletin->db->query_read_slave("
		SELECT rssfeedid
		FROM " . TABLE_PREFIX . "rsslog
		WHERE itemid=".$thread['threadid']." AND itemtype='thread'
	");
	
	$thread['rssfeedid'] = 0;
	if ($vbulletin->db->num_rows($checkrss) > 0)
	{
		$arrrssfeed = $vbulletin->db->fetch_array($checkrss);
		$thread['rssfeedid'] = $arrrssfeed['rssfeedid'];
	}
	unset($checkrss);
	
	if ($vbulletin->options['autotag_tag_prefix'] AND strlen($thread['prefixid']) > 0)
	{
			$thread['prefix'] = htmlspecialchars_uni($vbphrase["prefix_$thread[prefixid]_title_plain"]);
	}
	
		
	$taglist = GetAutoTags($thread);


	if (count($taglist) == 0)
	{
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "thread SET autoskip=2 WHERE threadid=".$thread['threadid']);
		return;
	}
	else
	{
		$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "thread SET autoskip=0 WHERE threadid=".$thread['threadid']);
	}
	
	$taglist = array_unique($taglist);
	
	$taglist_db = array();
	$taglist_insert = array();
	foreach ($taglist AS $tag)
	{
		$tag = $vbulletin->db->escape_string($tag);

		$taglist_db[] = $tag;
		$taglist_insert[] = "('$tag', " . $thread['dateline'] . ", 1)";
	}

	$vbulletin->db->query_write("
		INSERT IGNORE INTO " . TABLE_PREFIX . "tag
			(tagtext, dateline, autotag)
		VALUES
			" . implode(',', $taglist_insert)
	);

	$tagthread = array();
	$tagid_sql = $vbulletin->db->query_read("SELECT
		tagid
		FROM " . TABLE_PREFIX . "tag
		WHERE tagtext IN ('" . implode("', '", $taglist_db) . "')
	");
	
	while ($tag = $vbulletin->db->fetch_array($tagid_sql))
	{
		$tagthread[] = "(". $thread['threadid'] .", ". $tag['tagid'] .", " . $thread['postuserid'] . ", " . $thread['dateline'] . ", 1)";
	}

	if ($tagthread)
	{
		$vbulletin->db->query_write("
			INSERT IGNORE INTO " . TABLE_PREFIX . "tagthread
				(threadid, tagid, userid, dateline, autotag)
			VALUES
				" . implode(',', $tagthread)
		);
	}

	rebuild_thread_taglist($thread['threadid']); 
	
}

function GetAutoTags($thread, $IsAjax = false, $maxtags = 0)
{
	global $vbulletin;
		
	if (!$vbulletin->options['threadtagging'])
	{
		return;
	}

	// Check if disable autotag if tagged is set
	if (!$IsAjax)
	{
		if ($vbulletin->options['autotag_disable_if_tagged'] AND strlen(trim($thread['taglist'])) > 0)
		{
			return ;
		}
	}
	
	if (!$vbulletin->options['autotag_tag_rss_feeds'] && $thread['rssfeedid'] > 0)
	{
		return;
	}
	
	if ($thread['rssfeedid'] > 0)
	{
		$excludedRssFeeds = split('\|', $vbulletin->options['autotag_excluded_rss_feeds']);
		
		if (in_array($thread['rssfeedid'], $excludedRssFeeds))
		{
			return;
		}
	}
	
	// Get vars that defined allowed users/usergroups/forums.
	$excludedForums = split('\|', $vbulletin->options['autotag_exclude_forums']);
	$excludedUsers = split('\|', $vbulletin->options['autotag_exclude_users']);
	
	
	
	if (!in_array($thread['forumid'], $excludedForums) && !in_array($thread['postuserid'], $excludedUsers))
	{

		if ($vbulletin->options['autotag_tag_prefix'] AND $vbulletin->options['autotag_tag_prefix_filter'] AND strlen($thread['prefix']) > 0)
		{
			$thread['title'] = $thread['prefix'] & " " & $thread['title'];
		}

		$submittedTags = BuildSubmittedTags($thread['taglist']);
		$title = $thread['title'];
		$title = strtolower($title);
		$title = strip_tags($title);
		$title = strip_bbcode($title, true, false, false);
		$title = urldecode($title);
		$title = stripslashes($title);
		$title = preg_replace('/\s+/',' ',$title);
		$title = preg_replace('/,+/',' ',$title);
		
		
		// Get configuration items and data that drives the autotagger.
		$excludedWords = BuildExclusionList($thread, $submittedTags);
		$filterRules = BuildFilterRules();
		$compositeTagRules = BuildCompositeTags();
		
		// Check if any composite tags exist in the title, if so remove these
		// and store for later addition (after tokenise)
		$compositeTags = array();
		for($x=0; $x<count($compositeTagRules); $x++)
		{
			if(stristr($title, $compositeTagRules[$x]))
			{
				$title = str_replace($compositeTagRules[$x], '', $title);
				array_push($compositeTags, $compositeTagRules[$x]);
			}
		}
		
		// Tokenize the thread title
		$subjectTokens = array();
		if ($vbulletin->options['autotag_use_smartquotes'])
		{
			
			$title = str_replace("&quot;", '"', $title);
			$subjectTokens = preg_split('/\s(?=([^"]*"[^"]*"[^"]*)*$|[^"]*$)/', $title, -1, PREG_SPLIT_NO_EMPTY);
		}
		else
		{
			$subjectTokens = split(' ', $title);
		}
		
		// Put the composite tags back into the title tokens
		for($x=0; $x<count($compositeTags); $x++)
		{
			array_push($subjectTokens, $compositeTags[$x]);
		}
		unset($compositeTags);
		
		// Iterate through the title tokens and check if these
		// qualify for inclusion as a tag.
		$titleTags = array();
		for ($x = 0; $x < count($subjectTokens); $x++)
		{
			$subjectTokens[$x] = ProcessFiltersReplacements($subjectTokens[$x], $filterRules);
			
			// If qualifies add to titleTags.


			if (!in_array($subjectTokens[$x], $excludedWords))
			{
				$qualifies = false;
				if ((strlen($subjectTokens[$x]) <= $vbulletin->options['tagmaxlen']) OR $vbulletin->options['tagmaxlen']==0)
				{
					if ((strlen($subjectTokens[$x]) >= $vbulletin->options['tagminlen']) OR $vbulletin->options['tagminlen']==0)
					{
						$qualifies = true;
					}	
				}						

				if ($qualifies)
				{
					$subjectTokens[$x] = strtolower($subjectTokens[$x]);
					array_push($titleTags, $subjectTokens[$x]);
				}
			}
		}
		unset($excludedWords);
		unset($subjectTokens);
		
		//if this is an RSS thread and RSS tagging is enabled then add the defined additional tags
		if ($thread['rssfeedid'] > 0)
		{
			$additionalRssTags = BuildAdditionalRssTags();
			for($x=0; $x<count($additionalRssTags); $x++)
			{
				if ($thread['rssfeedid'] == $additionalRssTags[$x][0])
				{
					array_unshift($titleTags, $additionalRssTags[$x][1]);
				}
			}
			
			unset($additionalRssTags);
		}
		
		if ($vbulletin->options['autotag_tag_prefix'] AND !$vbulletin->options['autotag_tag_prefix_filter'] AND strlen($thread['prefix']) > 0)
		{
			array_unshift($titleTags, $thread['prefix']);
		}
		
		
		$titleTags = array_unique($titleTags);
				
		if ($maxtags == 0)
		{
			$maxtags = $vbulletin->options['tagmaxthread'];
		}
				
		if ($maxtags > 0)
		{
			while (count($titleTags) + count($submittedTags) > $maxtags)
			{
				array_pop($titleTags);
			}
		}
		if ($IsAjax)
		{
			$titleTags = array_unique(array_merge($titleTags, $submittedTags));			
		}

		
		if (count($titleTags) > 0)
		{
			return $titleTags;
		}
	}
}

function BuildSubmittedTags($taglist)
{
	global $vbulletin;
	
	if (function_exists(split_tag_list))
	{
		$taglist = split_tag_list($taglist);
	}
	else
	{
		$taglist = split(',', $taglist);
	}

	return $taglist;
	
}

function BuildAdditionalRssTags()
{
	global $vbulletin;
	
	$rssTags = array();
	$rawRssTags = trim(strtolower($vbulletin->options['autotag_rss_feeds_additional_tags']));
	
	if (strlen($rawRssTags) > 0)
	{
		$rawRssTagsItems = split("[\n\r\t]+", $rawRssTags);

		for ($x = 0; $x < count($rawRssTagsItems); $x++)
		{
			$parts = split("=>", $rawRssTagsItems[$x]);
			array_push($rssTags, $parts);
		}
	}
	
	return $rssTags;
}

function BuildCompositeTags()
{
	global $vbulletin;
	
	$compositeTagRules = array();
	$rawCompositeTagRules = trim(strtolower($vbulletin->options['autotag_composite_tags']));
	
	if (strlen($rawCompositeTagRules) > 0)
	{
		$compositeTagRules = split("[\n\r\t]+", $rawCompositeTagRules);
	}

	unset($rawCompositeTagRules);
	return $compositeTagRules;
}
function BuildFilterRules()
{
	global $vbulletin;
	
	$filterRules = array();
	$rawFilterReplaceItems = trim(strtolower($vbulletin->options['autotag_filter_replace_characters']));
	if (strlen($rawFilterReplaceItems) > 0)
	{
		$filterReplaceItems = split("[\n\r\t]+", $rawFilterReplaceItems);
	
		// Format the filter rules (format is 'input'=>'replacement')
		for ($x = 0; $x < count($filterReplaceItems); $x++)
		{
			$parts = split("'=>'", $filterReplaceItems[$x]);
			
			$parts[0] = substr($parts[0], 1); // Remove starting quote
			$parts[1] = substr($parts[1], 0, -1); // Remove terminating quote
			
			array_push($filterRules, $parts);
		}
	}
	
	return $filterRules;
}

function BuildExclusionList($thread, $submittedTags)
{
	global $vbulletin;
	
	$excludedWords = array();
	
	// Exclude tags already submitted
	if (strlen($thread['taglist']) > 0)
	{
		$excludedWords = array_merge($submittedTags, $excludedWords);
	}
	
	// Add configuration specified exclusions
	if (strlen(trim($vbulletin->options['autotag_excluded_words'])) > 0)
	{
		$configExclusions = split(',', strtolower($vbulletin->options['autotag_excluded_words']));
		$excludedWords = array_merge($excludedWords, $configExclusions);
	}
	
	if ($vbulletin->options['autotag_exclude_tagbadwords'])
	{
		if (strlen(trim($vbulletin->options['tagbadwords'])) > 0)
		{
			$configExclusions = preg_split('/\s+/s', vbstrtolower($vbulletin->options['tagbadwords']), -1, PREG_SPLIT_NO_EMPTY);
			$excludedWords = array_merge($excludedWords, $configExclusions);
		}
	}

	unset($configExclusions);

	// Trim current exclusions
	for ($x = 0; $x < count($excludedWords); $x++)
	{
		$excludedWords[$x] = trim($excludedWords[$x]);
	}

	// Exclude search words if configuration allows
	if ($vbulletin->options['autotag_exclude_searchwords'])
	{
		require(DIR . '/includes/searchwords.php');
		$excludedWords = array_merge($excludedWords, $badwords);
	}

	return $excludedWords;
}

function ProcessFiltersReplacements($string, $filterRules)
{
	global $vbulletin;

	//Apply date filter
	if ($vbulletin->options['autotag_exclude_date'])
	{
		$string = preg_replace("#([0-9]{1,2})(.)([0-9]{1,2})(.)([0-9]{1,4})#", "", $string);
	}

	//apply filters/replacements
	for($x=0; $x<count($filterRules); $x++)
	{
		//$string = str_replace("\\".$filterRules[$x][0], $filterRules[$x][1], $string);
		$string = str_replace($filterRules[$x][0], $filterRules[$x][1], $string);
	}

	
	//start/end trim
	$string = trim($string);
				
	return $string;
}



?>