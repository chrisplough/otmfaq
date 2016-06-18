<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.1.1 Patch Level 1 - Licence Number VBS309B87F
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2000-2011 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/

if (!defined('VB_ENTRY'))
{
die('Access denied.');
}

/**
 *
 *
 */
/**
 * vBCms_Rssfeed
 *
 * @package
 * @author ebrown
 * @copyright Copyright (c) 2009
 * @version $Id: rssfeed.php 40911 2010-12-02 22:38:25Z ksours $
 * @access public
 */
class vBCms_Rssfeed
{
 
	/**
	 * Constructor
	 */
	private function __construct()
	{

	}


	/**
	 * vBCms_Rssfeed::makeRss()
	 * This is the function that processes the request. All the parameters
	 * we need are in vbulletin->GPC. The variables we look for are
	 * 'type' => TYPE_UINT,
	 * 'count' => TYPE_UINT,
	 * 'id' => TYPE_UINT,
	 * 'grouped' => TYPE_UINT,
	 * 'days' => TYPE_UINT, (date limit)
	 * 'detail' => TYPE_STR, (can be any extra info)
	 * 'name' => TYPE_STR
	 * We need at least the type, but the others may or may not be needed by
	 * a particular feed
	 *
	 * @param vB_Legacy_User
	 */
	public static function makeRss()
	{
		global $vbulletin;

		$user = vB_Legacy_User::createFromId(0);

		if (!intval($vbulletin->options['externalcache']) OR $vbulletin->options['externalcache'] > 1440)
		{
			$externalcache = 60;
		}
		else
		{
			$externalcache = $vbulletin->options['externalcache'];
		}
		
		$vbulletin->input->clean_array_gpc('r', array(
			'sectionid' => TYPE_UINT,
			'days' => TYPE_UINT,
			'count' => TYPE_UINT
			));

	
		if ($vbulletin->GPC_exists['count'] AND intval($vbulletin->GPC['count'])
			and intval($vbulletin->GPC['count']) < 21)
		{
			$max_count = intval($vbulletin->GPC['count']);
		}
		else
		{
			$max_count = 10;
		}
		
		//and set the date limit
		if ($vbulletin->GPC_exists['days'] AND intval($vbulletin->GPC['days'] ))
		{
			$datelimit = TIMENOW - ( intval($vbulletin->GPC['days']) * 86400);
			$days = intval($vbulletin->GPC['days']);
		}
		else
		{
			$datelimit = TIMENOW - ( 2 * 86400);
			$days = 2;
		}

		$cachehash = md5(
			$vbulletin->GPC['sectionid'] . '|' .
			$days . '|' .
			$vbulletin->GPC['type'] . '|' .
			$max_count . '|' .
			$vbulletin->GPC['grouped'] . '|' .
			$vbulletin->GPC['detail'] . '|' .
			$vbulletin->GPC['name']
		);

		$result = vB_Cache::instance()->read($cachehash, true, false);

		if (!$result)
		{

			switch($vbulletin->GPC['type'])
			{
//				case 'newposts':
//					$result = self::getNewPosts($user, $max_count, $datelimit);
//					break;
//				case 'newblogs':
//					$result = self::getNewBlogs($user, $max_count, $datelimit);
//					break;
				case 'newcontent':
					$result = self::getNewContent($user, $max_count, $datelimit);
					break;
				default:
					$result = '';
			} // switch
			vB_Cache::instance()->write($cachehash, $result, $externalcache);
		}
		header('Content-Type: text/xml' . (vB_Template_Runtime::fetchStyleVar('charset') != '' ? '; charset=' .  vB_Template_Runtime::fetchStyleVar('charset') : ''));

		echo $result ;
	}

	/**
	 *This generates the rss for new content.
	 *
	 * @param mixed $user : The user object
	 * @return xml for the feed
	 */
	private static function getNewContent($user, $max_count, $datelimit)
	{
		global $vbulletin;
		require_once DIR . '/vb/search/core.php' ;
		require_once DIR . '/vb/search/criteria.php' ;
		require_once DIR . '/includes/functions_databuild.php' ;
		require_once DIR . '/includes/functions_misc.php' ;
		global $vbphrase;

		//We can use the existing new structures to create this feed. We don't
		// have to, we could do a direct sql query. But this structure is tested
		// and we know it handles permissions properly.
		fetch_phrase_group('vbcms');

		//First we need a criteria object
		if (! file_exists(DIR . '/packages/vbcms/search/searchcontroller/newcontentnode.php'))
		{
			return false;
		}
		$criteria = vB_Search_Core::get_instance()->create_criteria(vB_Search_Core::SEARCH_NEW);

		// sort by newest published first
		$criteria->set_sort('publishdate', 'desc');


		//Do we get a forum id? If so, limit the query.
		if ($vbulletin->GPC_exists['id'] AND intval($vbulletin->GPC['id']))
		{
			$criteria->add_forumid_filter($vbulletin->GPC['id'], true);
		}

		//ungroup.
		$criteria->set_grouped(vB_Search_Core::GROUP_NO);

		$criteria->add_newitem_filter($datelimit, null, null);

		$search_controller = new vBCms_Search_SearchController_NewContentNode;

		$results = vB_Search_Results::create_from_criteria($user, $criteria, $search_controller);

		$page = $results->get_page(1, $max_count, 1);
		$items= array();
		$headers = array(
			'title' => $vbulletin->options['hometitle'] ,
			'link' => $vbulletin->options['bburl'],
			'description' => construct_phrase($vbphrase['recent_content_from_x'], $vbulletin->options['hometitle']) ,
			'language' => 'en-us',
			'updated' => date('Y-m-d\TH:i:s', TIMENOW),
			'lastBuildDate' => date('Y-m-d\TH:i:s', TIMENOW)
		);

		if (count($page))
		{
			$parser = new vBCms_BBCode_HTML(vB::$vbulletin, vBCms_BBCode_HTML::fetchCmsTags());

			foreach ($page as $result)
			{
				$record = $result->get_record();
				$summary = $parser->get_preview($record['pagetext'], 800);

				$items[] = array(
					'title' => $record['title'],
					'summary' => $summary,
					'link' => vB_Route::create('vBCms_Route_Content', $record['nodeid'] .
						(empty($record['url']) ? '' : '-' . $record['url'])  )->getCurrentURL(),
					'publishdate' => (intval($record['publishdate'])));
			}
		}
		return self::makeXml($headers, null, $items);
	}



	/**
	 * This generates the rss for new posts.
	 *
	 * @param mixed $user : The user object
	 * @return xml for the feed
	 */

/*	
//per ed this isn't used anywhere.
//additionally, checks of the code confirm this, and manually constructing links to hit
//it show problems.  In short, it isn't used, its buggy, and I don't see a future need for it.
	private static function getNewPosts($user, $max_count, $datelimit)
	{
		global $vbulletin;
		global $vbphrase;
		require_once DIR . '/packages/vbforum/search/searchcontroller/newpost.php' ;
		require_once DIR . '/vb/search/core.php' ;
		require_once DIR . '/vb/search/criteria.php' ;
		require_once DIR . '/includes/functions_databuild.php' ;
		fetch_phrase_group('vbcms');

		//We can use the existing new structures to create this feed. We don't
		// have to, we could do a direct sql query. But this structure is tested
		// and we know it handles permissions properly.
		//First we need a criteria object

		$criteria = vB_Search_Core::get_instance()->create_criteria(vB_Search_Core::SEARCH_NEW);

		//Do we get a forum id? If so, limit the query.
		if ($vbulletin->GPC_exists['id'] AND intval($vbulletin->GPC['id']))
		{
			$criteria->add_forumid_filter($vbulletin->GPC['id'], true);
		}

		//Check to see if we're grouped
		$criteria->set_grouped(vB_Search_Core::GROUP_NO);

		$criteria->add_newitem_filter($datelimit, null, null);

		$search_controller = new vBForum_Search_SearchController_NewPost;

		if (! $results = vB_Search_Results::create_from_cache($user, $criteria, $search_controller))
		{
			$results = vB_Search_Results::create_from_criteria($user, $criteria, $search_controller);
		}

		$page = $results->get_page(1, $max_count, 1);
		$headers = array(
			'title' => $vbulletin->options['hometitle'] ,
			'link' => $vbulletin->options['bburl'] ,
			'description' => construct_phrase($vbphrase['recent_posts_from_x'], $vbulletin->options['hometitle']) ,
			'language' => 'en-us',
			'updated' => date('Y-m-d\TH:i:s', TIMENOW),
			'lastBuildDate' => date('Y-m-d\TH:i:s', TIMENOW)
		);
		$items= array();

		if (count($page))
		{
			$parser = new vBCms_BBCode_HTML(vB::$vbulletin, vBCms_BBCode_HTML::fetchCmsTags());
			foreach ($page as $result)
			{
				$record = $result->get_post();
				$items[] = array(
					'title' => $record->get_field('title'),
					'summary' => $parser->get_preview($record->get_field('pagetext'), 800),
					'link' => $vbulletin->options['bburl'] . '/showthread.php?'
						. $record->get_field('threadid') .'#post' . $record->get_field('postid') ,
					'author' => 'noreply@noreply.com-' . $record->get_field('username')	);
			}
		}
		return self::makeXml($headers, null, $items);
	}
 */

	/***
	* This function creates an xml feed of new blog entries.
	**/

/*	
//this isn't used and I don't think it quite works.
	private static function getNewBlogs($user, $max_count, $datelimit)
	{
		global $vbulletin;
		global $vbphrase;
		if (! file_exists(DIR . '/packages/vbblog/search/searchcontroller/newblogentry.php')
			or ! (vB_Search_Core::get_instance()->get_cansearch('vBBlog', 'BlogEntry') ))
		{
			return;
		}
		include_once DIR . '/packages/vbblog/search/searchcontroller/newblogentry.php' ;
		require_once DIR . '/vb/search/core.php' ;
		require_once DIR . '/vb/search/criteria.php' ;
		require_once DIR . '/includes/functions_databuild.php' ;
		//We can use the existing new structures to create this feed. We don't
		// have to, we could do a direct sql query. But this structure is tested
		// and we know it handles permissions properly.
		//First we need a criteria object
		fetch_phrase_group('vbcms');
		$criteria = vB_Search_Core::get_instance()->create_criteria(vB_Search_Core::SEARCH_NEW);

		//Do we get a user? If so, limit the query.
		if ($vbulletin->GPC_exists['userid'] AND intval($vbulletin->GPC['userid']))
		{
			$criteria->add_userid_filter($vbulletin->GPC['userid'], true);
		}
		else if ($vbulletin->GPC_exists['searchuser'])
		{
			$criteria->add_user_filter($vbulletin->GPC['searchuser'], true);
		}

		$criteria->add_newitem_filter($datelimit, null, null);
		$search_controller = new vBBlog_Search_SearchController_NewBlogEntry;
		$results = vB_Search_Results::create_from_cache($user, $criteria, $search_controller);

		if (! $results = vB_Search_Results::create_from_cache($user, $criteria, $search_controller))
		{
			$results = vB_Search_Results::create_from_criteria($user, $criteria, $search_controller);
		}
		$page = $results->get_page(1, $max_count, 1);
		$headers = array(
			'title' => $vbulletin->options['hometitle'] ,
			'link' => $vbulletin->options['bburl'],
			'description' => construct_phrase($vbphrase['recent_blogs_from_x'], $vbulletin->options['hometitle']) ,
			'language' => 'en-us',
			'updated' => date('Y-m-d\TH:i:s', TIMENOW),
			'lastBuildDate' => date('Y-m-d\TH:i:s', TIMENOW)
		);
		$items= array();

		if (count($page))
		{
			$parser = new vBCms_BBCode_HTML(vB::$vbulletin, vBCms_BBCode_HTML::fetchCmsTags());
			foreach ($page as $result)
			{
				if ($blog = $result->get_record())
				{
					$items[] = array(
						'title' => $blog['title'],
						'summary' => $parser->get_preview($blog['pagetext'], 800),
						'link' => $vbulletin->options['bburl'] . '/blog.php?blogid='
							. $blog['blogid'],
						'author' => 'noreply@noreply.com-' . $blog['username']);
				}
			}
		}
		return self::makeXml($headers, null, $items);
	}
 */

	/**** This composes the html for one item
	 *
	 * @param array
	 *
	 * @return string
	 ****/
	private static function makeXmlItem($item)
	{
		$result = "
			<item>
				<pubDate>" . ($item['publishdate']? date(DATE_RSS, $item['publishdate']): '') . "</pubDate>";
		$result .= "
				<title>" . $item['title'] . "</title> ";

		$summary = preg_replace('@<a href[^>]*?>.*?</a>@siU', '', $item['summary']);
		$summary = strip_tags($summary); /****/

		$result .= "
				<description>" . $summary . "</description>";
		$result .= "
				<link>" . $item['link'] . "</link>";
		$result .= "
				<guid>" . $item['link'] . "</guid>";
		$result .= "
			</item>
		";
		return $result;
	}


	/**** The generates the html feed
	 *
	 * @param array  (title, description, link)
	 * @param string (sql query text, if desired)
	 * @param array  (results from a query)
	 *
	 * @return string (in xml format)
	 ****/
	private static function makeXml($headers, $sql, $results)
	{

		$xml = '<?xml version="1.0" encoding="' . vB_Template_Runtime::fetchStyleVar('charset') . "\"?>
	<rss version=\"2.0\">
		<channel>
		<lastBuildDate>" . date(DATE_RSS, TIMENOW) . "</lastBuildDate>
		<title>" . $headers['title'] . "</title>
		<description>" . $headers['description'] . "</description>
		<link>" . $headers['link'] . "</link>";

		if (isset($sql))
		{
			$result = vB::$vbulletin->db->query_read($sql);
			if ($result)
			{
				while($record = vB::$vbulletin->db->fetch_array($result))
				{
					$xml .= self::makeXmlItem($record);
				}
			}
		}
		else
		{
			foreach($results as $result)
			{
				$xml .= self::makeXmlItem($result);
			}
		}
	$xml .= "
		</channel>
	</rss>";
	return $xml;

	}
}

/**
 *
 *
 * @version $Id: rssfeed.php 40911 2010-12-02 22:38:25Z ksours $
 * @copyright 2009
 */