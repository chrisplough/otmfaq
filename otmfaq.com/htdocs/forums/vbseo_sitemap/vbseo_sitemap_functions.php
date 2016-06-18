<?php 

 /******************************************************************************************
 * vBSEO Search Engine XML Sitemap for vBulletin v3.x and 4.x by Crawlability, Inc.    *
 *-----------------------------------------------------------------------------------------*
 *                                                                                         *
 * Copyright © 2010, Crawlability, Inc. All rights reserved.                               *
 * You may not redistribute this file or its derivatives without written permission.       *
 *                                                                                         *
 * Sales Email: sales@crawlability.com                                                     *
 *                                                                                         *
 *-------------------------------------LICENSE AGREEMENT-----------------------------------*
 * 1. You are free to download and install this plugin on any vBulletin forum for which    *
 *    you hold a valid vB license.                                                         *
 * 2. You ARE NOT allowed to REMOVE or MODIFY the copyright text within the .php files     *
 *    themselves.                                                                          *
 * 3. You ARE NOT allowed to DISTRIBUTE the contents of any of the included files.         *
 * 4. You ARE NOT allowed to COPY ANY PARTS of the code and/or use it for distribution.    *
 ******************************************************************************************/

	global $vbulletin, $vbseo_vars, $vbseo_stat, $vboptions, $db, $forumcache, $bbuserinfo;

	error_reporting(E_ALL & ~E_NOTICE);
	define('VBSEO_SM_VERSION', '3.0');

	if(is_object($vbulletin))
	{
		$vboptions = $vbulletin->options;
		$forumcache = $vbulletin->forumcache;
		$bbuserinfo = $vbulletin->userinfo;
		if(!defined('CANVIEW'))
		{
			define('CANVIEW', $vbulletin->bf_ugp_forumpermissions['canview']);
			define('CANVIEWOTHERS', $vbulletin->bf_ugp_forumpermissions['canviewothers']);
			define('CANVIEWTHREADS', $vbulletin->bf_ugp_forumpermissions['canviewthreads']);
		}
		$vboptions['hideprivateforums'] = !$vboptions['showprivateforums'];

		if($vbulletin->versionnumber >= '4.0')
			$vbseo_vars['isvb4'] = true;
	}
		

	if($vbulletin->db)
		$GLOBALS['db'] = &$vbulletin->db;


 	if($vboptions['vbseo_sm_vbseo'])
	if((@include_once(DIR . '/vbseo/includes/functions_vbseo.php'))
		||
		(@include_once(DIR . '/includes/functions_vbseo.php'))
		)
	{
	   	vbseo_startup();
	   	if(!$GLOBALS['g_cache'])
	   		$GLOBALS['g_cache'] = & $GLOBALS['vbseo_gcache'];
   	}
   	define('VBSEO_ON', defined('VBSEO_ENABLED') && VBSEO_ENABLED && $vboptions['vbseo_sm_vbseo']);

   	$vbseo_vars['bburl']  = preg_replace('#/+$#', '', $vboptions['bburl']);
   	$vbseo_vars['topurl'] = preg_replace('#/+$#', '', $vboptions['vbseo_sm_toppath'] ? $vboptions['vbseo_sm_toppath'] : $vboptions['bburl']);

	define('VBSEO_SLASH_METHOD', ((strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' AND stristr($_SERVER['SERVER_SOFTWARE'], 'apache') === false) OR (strpos(SAPI_NAME, 'cgi') !== false AND @!ini_get('cgi.fix_pathinfo'))) ? '?' : '/');
   	if(!defined('VBSEO_SM_DLDAT'))
	    define('VBSEO_SM_DLDAT', VBSEO_DAT_FOLDER.'downloads.dat');

// ================================================================================
// ================================================================================
// ================================================================================

   	function vbseo_get_last_tpl_update()
   	{
   		global $db, $vboptions, $vbseo_vars;

      	$tpl_update = $db->query_first("
            SELECT max( dateline ) as max
            FROM `".TABLE_PREFIX."template`
            WHERE styleid = '$vboptions[styleid]'
      	");

      	$vbseo_vars['tpl_update'] = $tpl_update['max'];
   	}

   	function vbseo_get_custom_priority($content_type)
   	{
   		global $vboptions, $custom_priority, $db, $vbulletin, $vbseo_vars;
   		if(!$vboptions['vbseo_sm_priority_spec'] || !$vbseo_vars['isvb4'])
   			return array();

   		if(!isset($custom_priority[$content_type]))
   		{

            $cpriority = array();
   			if($content_type == 'blog')
   			{
   				if(require_once(DIR . '/includes/class_sitemap.php'))
   				{
				
				$sitemap = new vB_SiteMap_Blog($vbulletin);
				$settings = $sitemap->get_priorities('blog');
	   		
				if ($settings['authors'])
				foreach ($settings['authors'] as $userid => $userinfo )
        			$cpriority[$userid] = $userinfo['weight'] * $vboptions['sitemap_priority'] / $settings['default'];
        		
        		}

	   		}else
   			{
   				$sq = "SELECT contenttypeid, sourceid, prioritylevel
           		FROM " . TABLE_PREFIX . "contentpriority
           		WHERE contenttypeid = '".$content_type."'";

               	$st = $db->query($sq);

        		while ($prow = $db->fetch_array($st))
        		{
        			$cpriority[$prow['sourceid']] = $prow['prioritylevel'];
        		}
   			}


			$custom_priority[$content_type] = $cpriority;
		}

		return $custom_priority[$content_type];

   	}

   	function vbseo_apply_custom_priority($content_type, $cid)
   	{
   		global $vboptions;
   		$cp = vbseo_get_custom_priority($content_type);
   		return isset($cp[$cid]) ? ($cp[$cid] / $vboptions['sitemap_priority']) : 1;
   	}

   	function vbseo_get_forumlist($parentid = 0)
   	{
    	global $vbulletin, $vboptions, $forumcache, $_FORUMOPTIONS, $bbuserinfo, $g_cache;

    	if($vbulletin->forumcache)
    	{
    		$forumcache = $vbulletin->forumcache;
		 	if(!$vboptions['vbseo_sm_vbseo'])
    		$g_cache['forum'] = &$forumcache;
    	}


    	$forumlist = array();

		$forums_scope = array_keys($forumcache);

    	foreach ($forums_scope AS $forumid)
		if($forumid>0)
		{
    		$forum = $forumcache["$forumid"];
    		if (!($forum['options'] & ($_FORUMOPTIONS?$_FORUMOPTIONS['active']:$vbulletin->bf_misc_forumoptions['active'])))
			{
				//continue;
			}

			$forumperms = $bbuserinfo['forumpermissions']["$forumid"];
			if ((!($forumperms & CANVIEW)
				||!($forumperms & CANVIEWOTHERS)
				||(defined('CANVIEWTHREADS')&&!($forumperms & CANVIEWTHREADS))) 
				
				AND ($vboptions['hideprivateforums']||!isset($vboptions['hideprivateforums'])) )
			{
				continue;
    		}

   			$forumlist[] = $forumid;

    	}   

    	return array_unique($forumlist);
   	}

   	function vbseo_sitemap_extra($progress)
   	{
   		global $vbseo_vars;
   		
   		if(vbseo_check_progress($progress)) return;

   		if(file_exists($vbseo_vars['extra_urls']) && filesize($vbseo_vars['extra_urls'])>0)
   		{
   			$pf = fopen($vbseo_vars['extra_urls'], 'r');

   			if($pf)
   			while(!feof($pf))
   			{
   				$nurl = trim(fgets($pf, 1024));
   				if($nurl)
   				{
   					$url_part = explode(',', $nurl);
        			vbseo_add_url($url_part[0], $url_part[2]?$url_part[2]:1.0, 0, $url_part[1]);
        		}
   			}
   			fclose($pf);
   		}
   	}

   	function vbseo_sitemap_forumdisplay($progress, $archived = false)
   	{
   		global $db, $vboptions, $vbseo_vars, $forumcache;
   		
   		if(vbseo_check_progress($progress)) return;

   		$added_urls = 0;

   		$perpage = $archived ? $vboptions['archive_threadsperpage'] : $vboptions['maxthreads'];
       	vbseo_log_entry("[SECTION START] forumdisplay".($archived?" archived":""), true);

       	$st = $db->query_first("
       		SELECT 
       			max(threadcount) as maxre,min(threadcount) as minre,avg(threadcount) as avgre
       		FROM " . TABLE_PREFIX . "forum
       	");
   		foreach($vbseo_vars['forumslist'] as $forumid)
   		{
   			if($forumcache[$forumid]['link'])continue;
   			$finfo = $forumcache[$forumid];
   			$dprune = $finfo['daysprune'];
        	$threadscount = $db->query_first("
        		SELECT COUNT(*) AS threads
        		FROM " . TABLE_PREFIX . "thread AS thread
        		WHERE forumid = $forumid
        			AND sticky = 0
        			AND visible = 1
        			".($archived?"AND open != 10":"")."
        			".($dprune>0?"AND lastpost >= " . (time() - ($dprune * 86400)):"")."
        	");
        	$totalthreads = $threadscount['threads'];
        	$totalpages = max(ceil($totalthreads / $perpage),1);
        	
        	vbseo_log_entry("[forumdisplay] forum_id: $forumid, total threads: $totalthreads, pages: $totalpages", !$archived);
		
			for($p=1; $p<=$totalpages; $p++)
			{
   		   	$relp1 = ($totalpages+1-$p)/$totalpages;
   		   	$relp2 = vbseo_math_avg_weight($finfo['threadcount'], $st['minre'], $st['maxre'], $st['avgre']);
   		   	$relp = $relp2*0.8 + $relp1*0.2;
  		    $relp*= vbseo_apply_custom_priority('forum', $forumid);
  		    $prior = vbseo_sm_priority($vboptions['vbseo_sm_priority_rf'], $relp);

  			$added_urls += vbseo_add_2urls(
  				vbseo_url_forum($forumid, $p, $archived),
  				vbseo_url_forum($forumid, $p, $archived, true),
  	   			$prior,
  				$forumcache[$forumid]['lastpost'],
  				$vboptions['vbseo_sm_freq_f']
			);
			}
   		}
   		vbseo_inc_progress();
   		return $added_urls;
   	}

   	function vbseo_sitemap_showthread($progress, $archived = false, $showpost = false)
   	{
   		global $db, $vboptions, $vbseo_vars, $vbseo_stat, $vbseo_progress;

   		if(vbseo_check_progress($progress)) return;

   		$added_urls = 0;
   		$perpage = $archived ? $vboptions['archive_postsperpage'] : $vboptions['maxposts'];
       	vbseo_log_entry("[SECTION START] showthread".($archived?" archived":""), true);

       	$from_forum = $vbseo_progress['step2'];
       	$smart_p_pingbacks = $vboptions['vbseo_sm_priority_smart'] && VBSEO_ON && 
       		(defined('VBSEO_IN_PINGBACK')&&(VBSEO_IN_PINGBACK || VBSEO_IN_TRACKBACK));

       	if($smart_p_pingbacks)
       	{
        	$lb_qnew = vbseo_dbtbl_exists('vbseo_linkback');
        	$lb_tblname = $lb_qnew ? 'vbseo_linkback' : 'linkback';

        	$mp_query = $db->query("
        		SELECT t_threadid,count(*) as cnt
        		FROM " . TABLE_PREFIX . $lb_tblname."
        		GROUP BY t_threadid
        	");
        	if(!$mp_query)
        	$mp_query = $db->query("
        		SELECT t_threadid,count(*) as cnt
        		FROM " . TABLE_PREFIX . "vbseo_linkback
        		GROUP BY t_threadid
        	");
        	$mp_array = array();
        	$max_ping = 0;
			while ($nextmp = $db->fetch_array($mp_query))
			{
				$mp_array[$nextmp['t_threadid']] = $nextmp['cnt'];
				if($nextmp['cnt']>$max_ping)$max_ping=$nextmp['cnt'];
			}
       	}

   		foreach($vbseo_vars['forumslist'] as $forumid)
   		{
   			if($from_forum && $from_forum!=$forumid)
   				continue;

   			$from_forum = 0;
			$vbseo_progress['step2'] = $forumid;

        	$st = $db->query_first("
        		SELECT count(*) as cnt
        			,max(views) as maxv,avg(views) as avgv
        			,max(replycount) as maxre,avg(replycount) as avgre
        		FROM " . TABLE_PREFIX . "thread 
        		WHERE forumid = $forumid
        			AND visible = 1
        	");
        	$getthreads = $db->query("
        		SELECT *
        		FROM " . TABLE_PREFIX . "thread AS thread
	            LEFT JOIN " . TABLE_PREFIX . "deletionlog AS deletionlog ON(deletionlog.primaryid = thread.threadid AND type = 'thread')
        		WHERE forumid = $forumid
        			AND visible = 1
        			AND deletionlog.primaryid IS NULL
        		LIMIT ".intval($vbseo_progress['step3']).",".$st['cnt']."
        	");
			
			while ($threadrow = $db->fetch_array($getthreads))
			{
				$vbseo_progress['step3']++;
				if($threadrow['open'] == 10) continue;

            	$totalposts = $threadrow['replycount'] + 1;
            	$totalpages = ceil($totalposts / $perpage);
            	
    		    if($vboptions['vbseo_sm_priority_smart'])
   		    	{
	   		    	if($threadrow['sticky'])
	   		    	{
	   		    		$prior = 1;
	   		    	}
	   		    	else
	   		    	{
   		    		$rate = $threadrow['votenum'] ? $threadrow['votetotal']/$threadrow['votenum'] : 0;
   		    		$relp1 = vbseo_math_avg_weight($threadrow['views'], 0, $st['maxv'], $st['avgv']);
   		    		$relp2 = vbseo_math_avg_weight($threadrow['replycount'], 0, $st['maxre'], $st['avgre']);
   		    		$relp3 = $rate/5;
   		    		$relp4 = $max_ping?$mp_array[$threadrow['threadid']]/$max_ping:0;

   		    		$relp = $relp1*0.45 + $relp2*0.25 + $relp3*0.15 + $relp4*0.15;
		  		    $relp*= vbseo_apply_custom_priority('forum', $threadrow['forumid']);

   		    		}
   		    	}
  		    	$prior = vbseo_sm_priority($vboptions['vbseo_sm_priority_rt'], $relp);

    		    if($vboptions['vbseo_sm_freq_tsmart'])
   		    	{
   		    		$dpassed = (time() - $threadrow['lastpost'])/86400;
   		    		if($dpassed<3)$freq = 'daily';
   		    		else if($dpassed<10)$freq = 'weekly';
   		    		else if($dpassed<100)$freq = 'monthly';
   		    		else $freq = 'yearly';
   		    	}else
    		    	$freq = $vboptions['vbseo_sm_freq_t'];
    		    	     
            	vbseo_log_entry("[showthread] forum_id: $forumid, thread_id: $threadrow[threadid], total posts: $totalposts, pages: $totalpages, views: $threadrow[views] $prior");

    			for($p=1; $p<=$totalpages; $p++)
    			{
    			$vbseo_stat[$archived?'at':'t'] += vbseo_add_2urls(
    				vbseo_url_thread($threadrow, $p, $archived),
    				vbseo_url_thread($threadrow, $p, $archived, true),
    				$prior,
    				$threadrow['lastpost'],
					$freq
    			);
				
				}

    			if($showpost)
    			{

            	$getposts = $db->query("
            		SELECT p.dateline,p.postid,p.threadid
            		FROM " . TABLE_PREFIX . "post AS p
            		WHERE p.threadid = $threadrow[threadid]
            		    AND visible = 1
            		ORDER BY p.dateline
            	");
    			
    			$pcount = 0;
    			while ($postrow = $db->fetch_array($getposts))
    			{
    				$pcount++;
                	vbseo_log_entry("[showpost] forum_id: $forumid, thread_id: $postrow[threadid], post_id: $postrow[postid]");
                	$relp = $relp*0.8+$pcount/($threadrow['replycount']+1)*0.2;
		  		    $relp*= vbseo_apply_custom_priority('forum', $threadrow['forumid']);
  		    		$prior2 = vbseo_sm_priority($vboptions['vbseo_sm_priority_rp'], $relp);
	    			$vbseo_stat['p'] += vbseo_add_2urls(
        				vbseo_url_post($threadrow, $postrow, $pcount),
        				vbseo_url_post($threadrow, $postrow, $pcount, true),
    		   			$prior2,
        				$postrow['dateline'],
    					$vboptions['vbseo_sm_freq_p']
        			);
				}
    			$db->free_result($getposts);
    			}
			}
			$db->free_result($getthreads);
			$vbseo_progress['step3'] = 0;
   		}
   		vbseo_inc_progress();
   	}

   	function vbseo_sitemap_polls($progress)
   	{
   		global $db, $vboptions, $vbseo_vars;

   		if(vbseo_check_progress($progress)) return;
   		$added_urls = 0;
       	vbseo_log_entry("[SECTION START] polls", true);

       	$st = $db->query_first("
       		SELECT 
       			max(voters) as maxre,min(voters) as minre,avg(voters) as avgre
       		FROM " . TABLE_PREFIX . "poll
       	");
   		foreach($vbseo_vars['forumslist'] as $forumid)
   		{
        	$getthreads = $db->query("
        		SELECT *
        		FROM " . TABLE_PREFIX . "thread AS thread
        		WHERE forumid = $forumid
        			AND visible = 1
        			AND pollid > 0
        	");

			while ($threadrow = $db->fetch_array($getthreads))
			{
            	$getpoll = $db->query_first("
            		SELECT *
            		FROM " . TABLE_PREFIX . "poll
            		WHERE pollid = ".$threadrow['pollid']."
            	");
            	if(!$getpoll)
            		continue;

            	vbseo_log_entry("[poll] forum_id: $forumid, thread_id: $threadrow[threadid], pollid: $threadrow[pollid]");

    			$added_urls++;

     		   	$relp2 = vbseo_math_avg_weight($getpoll['voters'], $st['minre'], $st['maxre'], $st['avgre']);
		  		$relp2*= vbseo_apply_custom_priority('forum', $threadrow['forumid']);
    		    $prior = vbseo_sm_priority($vboptions['vbseo_sm_priority_rpoll'], $relp2);
    			$added_urls += vbseo_add_2urls(
    				vbseo_url_poll($threadrow, $getpoll),
    				vbseo_url_poll($threadrow, $getpoll, true),
   		   			$prior,
    				$getpoll['dateline'],
   					$vboptions['vbseo_sm_freq_poll']
    			);
				
			}
			$db->free_result($getthreads);
   		}
   		vbseo_inc_progress();
   		return $added_urls;
   	}

   	function vbseo_sitemap_blogs($progress)
   	{
   		global $db, $vboptions, $vbseo_vars;
   		                                    
   		if(vbseo_check_progress($progress)) return;

      	if(!vbseo_dbtbl_exists('blog'))
      		return 0;

       	vbseo_log_entry("[SECTION START] blogs", true);
        vbseo_add_url(VBSEO_ON ? vbseo_any_url($vbseo_vars['bburl'].'/blog.'.VBSEO_PHP_EXT) : $vbseo_vars['bburl'].'/blog.'.VBSEO_PHP_EXT, 1.0);
   		$added_urls = 0;

       	$st = $db->query_first("
       		SELECT 
       			max(views) as maxre,min(views) as minre,avg(views) as avgre
       		FROM " . TABLE_PREFIX . "blog
       		WHERE state='visible'
       	");
       	$getblogs = $db->query("
       		SELECT *
       		FROM " . TABLE_PREFIX . "blog
       		WHERE state = 'visible'
       	");
   		while ($blogrow = $db->fetch_array($getblogs))
   		{
           	vbseo_log_entry("[blog] blog_id: $blogrow[blogid]");

     		$relp2 = vbseo_math_avg_weight($blogrow['views'], $st['minre'], $st['maxre'], $st['avgre']);
		  	$relp2*= vbseo_apply_custom_priority('blog', $blogrow['userid']);
   		    $prior = vbseo_sm_priority($vboptions['vbseo_sm_priority_rb'], $relp2);
   			$added_urls += vbseo_add_2urls(
   				vbseo_url_blog_entry($blogrow),
   				vbseo_url_blog_entry($blogrow, true),
  		   		$prior,
   				$blogrow['dateline'],
  				$vboptions['vbseo_sm_freq_b']
   			);
   			
   		}
   		$db->free_result($getblogs);
   		vbseo_inc_progress();
   		return $added_urls;
   	}

   	function vbseo_sitemap_blog_tags($progress)
   	{
   		global $db, $vboptions, $vbseo_vars;

   		if(vbseo_check_progress($progress)) return;
       	vbseo_log_entry("[SECTION START] blog tags", true);

      	if(!vbseo_dbtbl_exists('blog_tag'))
      		return 0;

   		$added_urls = 0;

   		$added_urls += vbseo_add_2urls(
   			vbseo_url_blogtag(array(), false, VBSEO_URL_BLOG_TAGS_HOME),
   			vbseo_url_blogtag(array(), true),
  	   		vbseo_sm_priority($vboptions['vbseo_sm_priority_rblogtag'],1),
   			0,
  			$vboptions['vbseo_sm_freq_blogtag']
   		);

       	$st = $db->query_first("
       		SELECT 
       			count(*) as maxre
       		FROM " . TABLE_PREFIX . "blog_tagentry
       		GROUP BY tagid
       		ORDER BY maxre DESC
       		LIMIT 0,1
       	");

       	$getrecords = $db->query("
       		SELECT tagid, tagtext as tag
       		FROM " . TABLE_PREFIX . "blog_tag
       	");
       	$ouserid = 0;
   		while ($rrow = $db->fetch_array($getrecords))
   		{
           	vbseo_log_entry("[tag] tag_id: $rrow[tagid]");
         	$tcount = $db->query_first("
           		SELECT COUNT(*) as cnt,max(lastcomment) as lastupdate
           		FROM " . TABLE_PREFIX . "blog AS blog
           		INNER JOIN " . TABLE_PREFIX . "blog_tagentry AS tagentry ON
           			(tagentry.tagid = $rrow[tagid] AND tagentry.blogid = blog.blogid)
           		WHERE 
           			blog.state = 'visible'
           		GROUP BY tagentry.tagid
         	");
         	if(!$tcount['cnt'])
         		continue;
         	$pcount = ceil($tcount['cnt']/$vboptions['vbblog_perpage']);
     		$relp2 = vbseo_math_avg_weight($tcount['cnt'], 0, $st['maxre'], $st['maxre']/2);
   		    $prior = vbseo_sm_priority($vboptions['vbseo_sm_priority_rblogtag'], $relp2);
         	for($i=0;$i<$pcount;$i++)
         	{
        	if($i) $rrow['page'] = $i+1;
   			$added_urls += vbseo_add_2urls(
   				vbseo_url_blogtag($rrow, false, $i ? VBSEO_URL_BLOG_TAGS_ENTRY_PAGE : VBSEO_URL_BLOG_TAGS_ENTRY),
   				vbseo_url_blogtag($rrow, true),
  		   		$prior,
   				$rrow['lastupdate'],
  				$vboptions['vbseo_sm_freq_blogtag']
   			);
   			}

   		}
   		$db->free_result($getrecords);

   		vbseo_inc_progress();
   		return $added_urls;
   	}

   	function vbseo_sitemap_cms($progress)
   	{
   		global $db, $vboptions, $vbseo_vars, $g_cache;

   		if(vbseo_check_progress($progress)) return;

      	if(!vbseo_dbtbl_exists('cms_nodeinfo'))
      		return 0;

       	vbseo_log_entry("[SECTION START] cms", true);

      	$aurl = $vbseo_vars['bburl'].'/content.'.VBSEO_PHP_EXT;
        vbseo_add_url(VBSEO_ON ? vbseo_any_url($aurl) : $aurl, 1.0);
   		$added_urls = 0;

		require_once(DIR . '/includes/class_bootstrap_framework.php');
		require_once(DIR . '/vb/types.php');
		vB_Bootstrap_Framework::init();
		$types = vB_Types::instance();
		$typesec = $types->getContentTypeID('vBCms_Section');
		$typeart = $types->getContentTypeID('vBCms_Article');
		$typehtml = $types->getContentTypeID('vBCms_StaticHtml');
		$typepage = $types->getContentTypeID('vBCms_StaticPage');

       	$getlist = $db->query("
       		SELECT cn.*,ci.*
       		FROM " . TABLE_PREFIX . "cms_node cn
       		LEFT JOIN  " . TABLE_PREFIX . "cms_permissions cp on cp.nodeid = cn.nodeid
       		LEFT JOIN  " . TABLE_PREFIX . "cms_nodeinfo ci on ci.nodeid = cn.nodeid
       		WHERE 
       			cp.usergroupid = 2 and cp.permissions > 0
       			and contenttypeid = '$typesec'
       	");
		$adrow = $asec = array();
   		while ($drow = $db->fetch_array($getlist))
		{	
		$adrow[] = $drow;
		$asec[] = $drow['nodeid'];
		$topsec[] = $drow['nodeid'];
		}

		do {
		if($topsec)
       	$getlist = $db->query("
       		SELECT cn.*,ci.*
       		FROM " . TABLE_PREFIX . "cms_node cn
       		LEFT JOIN  " . TABLE_PREFIX . "cms_nodeinfo ci on ci.nodeid = cn.nodeid
       		WHERE 
       			cn.parentnode in (".implode(',', $topsec).")
       			and contenttypeid = '$typesec'
       	");
		$topsec = array();
   		while ($drow = $db->fetch_array($getlist))
		{
		$adrow[] = $drow;
		$asec[] = $drow['nodeid'];
		$topsec[] = $drow['nodeid'];
		}
		} while(count($topsec)>0);


   		foreach($adrow as $drow)
   		{
   			$asec[] = $drow['nodeid'];
           	vbseo_log_entry("[section] node_id: $drow[nodeid]", 1);
		  	$relp2 = vbseo_apply_custom_priority('cms', $drow['nodeid']);
   		    $prior = vbseo_sm_priority($vboptions['vbseo_sm_priority_cmssec'], $relp2);
   			$added_urls += vbseo_add_2urls(
   				vbseo_url_cms($drow),
   				vbseo_url_cms($drow, true),
  		   		$prior,
   				$drow['lastupdated'],
  				$vboptions['vbseo_sm_freq_cms']
   			);
   			
   		}
   		$db->free_result($getlist);

       	$st = $db->query_first("SELECT max(viewcount) as maxre,avg(viewcount) as avgre FROM " . TABLE_PREFIX . "cms_nodeinfo");
   		
   		if($asec)
       	$getlist = $db->query("
       		SELECT cn.*,ci.*
       		FROM " . TABLE_PREFIX . "cms_node cn
       		LEFT JOIN  " . TABLE_PREFIX . "cms_nodeinfo ci on ci.nodeid = cn.nodeid
       		WHERE (publishdate is null or publishdate < unix_timestamp(NOW()))
       			and contenttypeid in ('$typeart','$typehtml','$typepage')
       			and cn.parentnode in(".implode(',',$asec).")
       	");
   		while ($drow = $db->fetch_array($getlist))
   		{
           	vbseo_log_entry("[article] node_id: $drow[nodeid]");
     		$relp2 = vbseo_math_avg_weight($drow['viewcount'], 0, $st['maxre'], $st['avgre']);
		  	$relp2*= vbseo_apply_custom_priority('cms', $drow['parentnode']);
   		    $prior = vbseo_sm_priority($vboptions['vbseo_sm_priority_cmsent'], $relp2);
   			$added_urls += vbseo_add_2urls(
   				vbseo_url_cms($drow),
   				vbseo_url_cms($drow, true),
  		   		$prior,
   				$drow['lastupdated'],
  				$vboptions['vbseo_sm_freq_cms']
   			);
   			
   		}
   		$db->free_result($getlist);
   		unset($g_cache['cmscont']);
   		vbseo_inc_progress();
   		return $added_urls;
   	}

   	function vbseo_sitemap_member($progress)
   	{
   		global $db, $vboptions, $vbseo_progress;

   		if(vbseo_check_progress($progress)) return;
       	vbseo_log_entry("[SECTION START] member", true);

   		$added_urls = 0;
       	$st = $db->query_first("
       		SELECT 
       			count(*) as cnt,
       			max(posts) as maxre,min(posts) as minre,avg(posts) as avgre
       		FROM " . TABLE_PREFIX . "user
       	");
      	$getmembers = $db->query("
      		SELECT userid, username, lastpost,posts
      		FROM " . TABLE_PREFIX . "user
      		ORDER BY username
       		LIMIT ".intval($vbseo_progress['step2']).",".$st['cnt']."
      	");
  		
  		while ($member = $db->fetch_array($getmembers))
  		{
          	vbseo_log_entry("[member] user_id: $member[userid]");
          	$vbseo_progress['step2']++;
  		
     		$relp2 = vbseo_math_avg_weight($member['posts'], $st['minre'], $st['maxre'], $st['avgre']);
   		    $prior = vbseo_sm_priority($vboptions['vbseo_sm_priority_rm'], $relp2);
  			$added_urls += vbseo_add_2urls(
  				vbseo_url_member($member['userid'], $member['username']),
  				vbseo_url_member($member['userid'], $member['username'], true),
	   			$prior,
  				$member['lastpost'],
				$vboptions['vbseo_sm_freq_m']
  			);
  		}
  		$db->free_result($getmembers);
   		vbseo_inc_progress();
  		return $added_urls;
   	}

   	function vbseo_sitemap_albums($progress)
   	{
   		global $db, $vboptions, $vbseo_vars;

   		if(vbseo_check_progress($progress)) return;
       	vbseo_log_entry("[SECTION START] albums", true);

      	if(!vbseo_dbtbl_exists('album') )
      		return 0;

   		$added_urls = 0;

       	$st = $db->query_first("
       		SELECT 
       			count(*) as cnt,
       			max(posts) as maxre,min(posts) as minre,avg(posts) as avgre
       		FROM " . TABLE_PREFIX . "user
       	");
       	$getrecords = $db->query("
       		SELECT a.*,u.username,u.posts
       		FROM " . TABLE_PREFIX . "album a
       		LEFT JOIN " . TABLE_PREFIX . "user u on u.userid=a.userid
       		WHERE visible > 0 AND state = 'public'
       		ORDER BY userid
       	");
       	$ouserid = 0;
   		while ($rrow = $db->fetch_array($getrecords))
   		{
           	vbseo_log_entry("[album] album_id: $rrow[albumid]");

     		$relp2 = vbseo_math_avg_weight($rrow['posts'], $st['minre'], $st['maxre'], $st['avgre']);
   		    $prior = vbseo_sm_priority($vboptions['vbseo_sm_priority_ra'], $relp2);

           	if($ouserid!=$rrow['userid'])
           	{
           	$rrow2 = $rrow;
           	unset($rrow2['albumid']);
   			$added_urls += vbseo_add_2urls(
   				vbseo_url_album($rrow2, false, 'VBSEO_URL_MEMBER_ALBUMS'),
   				vbseo_url_album($rrow2, true),
  		   		$prior,
   				$rrow['lastpicturedate'],
  				$vboptions['vbseo_sm_freq_a']
   			);
   			$ouserid = $rrow['userid'];
           	}

   			$added_urls += vbseo_add_2urls(
   				vbseo_url_album($rrow, false, 'VBSEO_URL_MEMBER_ALBUM'),
   				vbseo_url_album($rrow, true),
  		   		$prior*0.8,
   				$rrow['lastpicturedate'],
  				$vboptions['vbseo_sm_freq_a']
   			);
   			
   			if($vbseo_vars['isvb4'])
         	$getitems = $db->query("
         		SELECT *
         		FROM " . TABLE_PREFIX . "attachment
         		WHERE state = 'visible' AND contenttypeid = '8' and contentid = '".$rrow['albumid'] ."'
         	");
         	else
         	$getitems = $db->query("
         		SELECT ap.*,p.caption
         		FROM " . TABLE_PREFIX . "albumpicture ap
         		LEFT JOIN " . TABLE_PREFIX . "picture p on p.pictureid=ap.pictureid
         		WHERE state = 'visible' AND albumid = '".$rrow['albumid'] ."'
         	");

     		while ($ritem = $db->fetch_array($getitems))
     		{
             	vbseo_log_entry("[picture] picture_id: $ritem[pictureid]");
             	$ritem = array_merge($rrow, $ritem);

     			$added_urls += vbseo_add_2urls(
     				vbseo_url_album($ritem, false, 'VBSEO_URL_MEMBER_PICTURE'),
     				vbseo_url_album($ritem, true),
    		   		$prior,
     				$ritem['dateline'],
    				$vboptions['vbseo_sm_freq_a']
     			);
     			
     		}
     		$db->free_result($getitems);
   		}
   		$db->free_result($getrecords);

   		vbseo_inc_progress();
   		return $added_urls;
   	}

   	function vbseo_sitemap_groups($progress)
   	{
   		global $db, $vboptions, $vbseo_vars;

   		if(vbseo_check_progress($progress)) return;
       	vbseo_log_entry("[SECTION START] groups", true);

      	if(!vbseo_dbtbl_exists('socialgroup'))
      		return 0;

   		$added_urls = 0;

   		$added_urls += vbseo_add_2urls(
   			vbseo_url_group(array(), false, VBSEO_URL_GROUPS_HOME),
   			vbseo_url_group(array(), true),
  	   		vbseo_sm_priority($vboptions['vbseo_sm_priority_rg'],1),
   			0,
  			$vboptions['vbseo_sm_freq_g']
   		);

       	$st = $db->query_first("
       		SELECT 
       			max(members) as maxre,min(members) as minre,avg(members) as avgre
       		FROM " . TABLE_PREFIX . "socialgroup
       	");

       	$getrecords = $db->query("
       		SELECT g.*
       		FROM " . TABLE_PREFIX . "socialgroup g
       		WHERE type = 'public'
       	");

       	$hasdiscussions = vbseo_dbtbl_exists('discussion');

       	if($hasdiscussions)
       	$stdis = $db->query_first("
       		SELECT 
       			max(visible) as maxre,min(visible) as minre,avg(visible) as avgre
         	FROM " . TABLE_PREFIX . "discussion
         	WHERE deleted=0 AND groupid>0
       	");

       	$ouserid = 0;
   		while ($rrow = $db->fetch_array($getrecords))
   		{
           	vbseo_log_entry("[album] group_id: $rrow[groupid]");
         	$tcount = $db->query_first("
         		SELECT count(*)as cnt,max(dateline) as lastupdate
         		FROM " . TABLE_PREFIX . "groupmessage AS groupmessage
    			".($hasdiscussions?"LEFT JOIN " . TABLE_PREFIX . "discussion AS discussion ON (groupmessage.discussionid = discussion.discussionid)":"")."
         		WHERE ".
         		($hasdiscussions ? "discussion.deleted = 0 AND discussion.":"").
         			"groupid='$rrow[groupid]'
         	");
         	$pcount = max(1, ceil($tcount['cnt']/$vboptions['vm_perpage']));
         	$rrow2 = $rrow;
     		
     		$relp2 = vbseo_math_avg_weight($rrow['members'], $st['minre'], $st['maxre'], $st['avgre']);
   		    $prior = vbseo_sm_priority($vboptions['vbseo_sm_priority_rg'], $relp2);

   		    if($vboptions['vbseo_sm_group'])
         	for($i=0;$i<$pcount;$i++)
         	{
        	if($i) $rrow2['page'] = $i+1;
   			$added_urls += vbseo_add_2urls(
   				vbseo_url_group($rrow2, false, $i ? VBSEO_URL_GROUPS_PAGE : VBSEO_URL_GROUPS),
   				vbseo_url_group($rrow2, true),
  		   		$prior,
   				$rrow['lastupdate'],
  				$vboptions['vbseo_sm_freq_g']
   			);
   			}

			if($vboptions['vbseo_sm_group_img'])
			{
			// ------------------------
	   			
   		    $prior = vbseo_sm_priority($vboptions['vbseo_sm_priority_rgi'], 1);
   			if($vbseo_vars['isvb4'])
         	$getitems = $db->query("
         		SELECT *
         		FROM " . TABLE_PREFIX . "attachment
         		WHERE state = 'visible' AND contenttypeid = '7'
         	");
         	else
         	$getitems = $db->query("
         		SELECT gp.*,p.caption
         		FROM " . TABLE_PREFIX . "socialgrouppicture gp
         		LEFT JOIN " . TABLE_PREFIX . "picture p on p.pictureid=gp.pictureid
         		WHERE state = 'visible' AND groupid = '".$rrow['groupid'] ."'
         	");

         	if($getitems)
         	{
             	$par = 'do=grouppictures';
     			$added_urls += vbseo_add_2urls(
     				vbseo_url_group($rrow, false, VBSEO_URL_GROUPS_PIC, $par),
     				vbseo_url_group($rrow, true, '', $par),
    		   		$prior,
     				$rrow['lastpost'],
    				$vboptions['vbseo_sm_freq_gi']
     			);
			}

     		while ($ritem = $db->fetch_array($getitems))
     		{
             	vbseo_log_entry("[group] group_id: $ritem[groupid]");
             	$ritem = array_merge($rrow, $ritem);

             	$par = 'do=picture';
     			$added_urls += vbseo_add_2urls(
     				vbseo_url_group($ritem, false, VBSEO_URL_GROUPS_PICTURE, 1),
     				vbseo_url_group($ritem, true, '', $par),
    		   		$prior,
     				$ritem['dateline'],
    				$vboptions['vbseo_sm_freq_gi']
     			);
     			
     		}
     		$db->free_result($getitems);
			// ------------------------
			}

			if($vboptions['vbseo_sm_group_dis'] && $hasdiscussions)
			{
			// ------------------------
	   			
         	$getitems = $db->query("
         		SELECT count(*)as cnt,max(dateline) as lastupdate,discussion.visible,discussion.discussionid,max(groupmessage.title) as title
         		FROM " . TABLE_PREFIX . "groupmessage AS groupmessage
    			LEFT JOIN " . TABLE_PREFIX . "discussion AS discussion ON (groupmessage.discussionid = discussion.discussionid)
         		WHERE discussion.deleted = 0 AND groupid='$rrow[groupid]'
         		GROUP BY groupmessage.discussionid
         	");
         	if($getitems)
     		while ($ritem = $db->fetch_array($getitems))
     		{
        		$relp2 = vbseo_math_avg_weight($ritem['visible'], $stdis['minre'], $stdis['maxre'], $stdis['avgre']);
	   		    $prior = vbseo_sm_priority($vboptions['vbseo_sm_priority_rgd'], $relp2);
             	vbseo_log_entry("[group] discussion_id: $ritem[discussionid]");
             	$ritem = array_merge($rrow, $ritem);

             	$par = 'do=discuss';
     			$added_urls += vbseo_add_2urls(
     				vbseo_url_group($ritem, false, VBSEO_URL_GROUPS_DISCUSSION, $par),
     				vbseo_url_group($ritem, true, '', $par),
    		   		$prior,
     				$ritem['lastupdate'],
    				$vboptions['vbseo_sm_freq_gd']
     			);
     			
     		}
     		$db->free_result($getitems);
			// ------------------------
			}

   		}
   		$db->free_result($getrecords);

   		vbseo_inc_progress();
   		return $added_urls;
   	}

   	function vbseo_sitemap_tags($progress)
   	{
   		global $db, $vboptions, $vbseo_vars;

   		if(vbseo_check_progress($progress)) return;
       	vbseo_log_entry("[SECTION START] tags", true);

      	if(!vbseo_dbtbl_exists('tag'))
      		return 0;

   		$added_urls = 0;

   		$added_urls += vbseo_add_2urls(
   			vbseo_url_tag(array(), false, VBSEO_URL_TAGS_HOME),
   			vbseo_url_tag(array(), true),
  	   		vbseo_sm_priority($vboptions['vbseo_sm_priority_rtag'],1),
   			0,
  			$vboptions['vbseo_sm_freq_tag']
   		);

   		$tagtbl = $vbseo_vars['isvb4'] ? "tagcontent" : "tagthread"; 
   		$tagfld = $vbseo_vars['isvb4'] ? "contentid" : "threadid"; 
       	$st = $db->query_first("
       		SELECT 
       			count(*) as maxre
       		FROM " . TABLE_PREFIX . $tagtbl . "
       		GROUP BY tagid
       		ORDER BY maxre DESC
       		LIMIT 0,1
       	");

       	$getrecords = $db->query("
       		SELECT tagid, tagtext as tag
       		FROM " . TABLE_PREFIX . "tag
       	");
       	$ouserid = 0;
   		while ($rrow = $db->fetch_array($getrecords))
   		{
           	vbseo_log_entry("[tag] tag_id: $rrow[tagid]");
         	$tcount = $db->query_first("
           		SELECT COUNT(*) as cnt,max(lastpost) as lastupdate
           		FROM " . TABLE_PREFIX . "thread AS thread
           		INNER JOIN " . TABLE_PREFIX . $tagtbl . " AS ".$tagtbl." ON
           			(".$tagtbl.".tagid = $rrow[tagid] AND ".$tagtbl.".".$tagfld." = thread.threadid)
           		WHERE thread.forumid IN(" . implode(', ', $vbseo_vars['forumslist']) . ")
           			AND thread.visible = 1
           			AND thread.sticky IN (0, 1)
           			AND thread.open <> 10
           		GROUP BY ".$tagtbl.".tagid
         	");
         	if(!$tcount['cnt'])
         		continue;
         	$pcount = ceil($tcount['cnt']/$vboptions['maxthreads']);
     		$relp2 = vbseo_math_avg_weight($tcount['cnt'], 0, $st['maxre'], $st['maxre']/2);
   		    $prior = vbseo_sm_priority($vboptions['vbseo_sm_priority_rtag'], $relp2);
         	for($i=0;$i<$pcount;$i++)
         	{
        	if($i) $rrow['page'] = $i+1;
   			$added_urls += vbseo_add_2urls(
   				vbseo_url_tag($rrow, false, $i ? VBSEO_URL_TAGS_ENTRYPAGE : VBSEO_URL_TAGS_ENTRY),
   				vbseo_url_tag($rrow, true),
  		   		$prior,
   				$rrow['lastupdate'],
  				$vboptions['vbseo_sm_freq_tag']
   			);
   			}

   		}
   		$db->free_result($getrecords);

   		vbseo_inc_progress();
   		return $added_urls;
   	}

   	function vbseo_sitemap_clean()
   	{
   		global $vbseo_vars;
   		if(vbseo_check_progress(1)) return;
		vbseo_sm_prune(VBSEO_DAT_FOLDER, '.gz', 0.01);
   	}

   	function vbseo_sitemap_homepage($progress)
   	{
   		global $vbseo_vars;
   		if(vbseo_check_progress($progress)) return;

       	vbseo_log_entry("[homepage]", true);
        vbseo_add_url($vbseo_vars['bburl'].'/', 1.0);
   		vbseo_inc_progress();
   	}

   	function vbseo_sitemap_archive_homepage($progress)
   	{
   		global $vbseo_vars;

   		if(vbseo_check_progress($progress)) return;

       	vbseo_log_entry("[archive homepage]", true);

		vbseo_add_2urls(
        	$vbseo_vars['bburl'].((VBSEO_ON&&VBSEO_REWRITE_FORUM) ? VBSEO_ARCHIVE_ROOT : '/archive/index.'.VBSEO_PHP_EXT), 
        	$vbseo_vars['bburl']. '/archive/index.'.VBSEO_PHP_EXT,
        	1.0);

   		vbseo_inc_progress();
   	}

// ================================================================================
// ================================================================================
// ================================================================================
   	function vbseo_add_2urls($url, $url2, $priority = 1.0, $lastmod = 0, $freq = '')
   	{
   		global $vboptions;
   		
   		$added_urls = 1;
   		vbseo_add_url($url, $priority, $lastmod, $freq);
   		return $added_urls;
   	}

   	function vbseo_add_url($url, $priority = 1.0, $lastmod = 0, $freq = '')
   	{
   		global $vbseo_vars, $vboptions, $vbseo_stat, $vbseo_sm_dupcheck;
   		$urlhash = md5($url);
   		if($vbseo_sm_dupcheck[$urlhash]++>0)return false;

   		if(!$freq)
   			$freq = 'daily';

   		if(!$lastmod)
   			$lastmod = time();

   		if($lastmod<$vbseo_vars['tpl_update'])
   			$lastmod = $vbseo_vars['tpl_update'];

   		if(!$priority)
   			$priority = $vboptions['vbseo_sm_priority'];

		$lastmod = gmdate('Y-m-d\TH:i:s+00:00', $lastmod);
        if($vbseo_stat['urls_no'] == 0)
            $priority = min($priority+0.0001, 1.0); 

   		$vbseo_vars['sitemap_content'][] = array(
	  		'url'=> $url,
  			'priority'=> $priority,
    	    'lastmod' => $lastmod,
	        'freq' => $freq
        );
   	
		$vbseo_stat['urls_no']++;
		$vbseo_stat['urls_no_tot']++;

        if( ($vbseo_stat['urls_no'] == ($vboptions['vbseo_sm_maxurls']?$vboptions['vbseo_sm_maxurls']:50000)) )
			vbseo_flush_sitemap(true);
			else
        if( ($vbseo_stat['urls_no'] % 1000) == 0)
			vbseo_flush_sitemap(false);
   	}


   	function vbseo_flush_sitemap($split = true, $last = false)
   	{
   		global $vbseo_vars, $vboptions, $vbseo_stat;

   		if(!$vbseo_vars['sitemap_content'])return;

   		$sm_filename = vbseo_ext_gz('sitemap_'.$vbseo_vars['sitemap_type'].'_'.($vbseo_vars['sitemap_counter']+1).'.xml');
   		$xs = 'xml_started_'.count($vbseo_vars['sitemap_files']);

   		if(!$vbseo_vars[$xs])
   		{
   			$vbseo_vars['pfname'] = VBSEO_DAT_FOLDER . $sm_filename;
	   		$vbseo_vars['pf'] = fopen($vbseo_vars['pfname'], 'w');
	   	}

   		if(!$vbseo_vars[$xs])
   		fwrite($vbseo_vars['pf'], 
'<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="'.vbseo_sitemap_furl('sitemap.xsl', 'vbseo_sitemap/').'"?>
<urlset
      xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation="
            http://www.sitemaps.org/schemas/sitemap/0.9
            http://www.sitemaps.org/schemas/sitemap/09/sitemap.xsd">');//<?php

		foreach($vbseo_vars['sitemap_content'] as $sc)
		{
			$xcont = "
<url>
  <loc>$sc[url]</loc>
  <priority>$sc[priority]</priority>
  <lastmod>$sc[lastmod]</lastmod>
  <changefreq>$sc[freq]</changefreq>
</url>";
			fwrite($vbseo_vars['pf'], $xcont);
	   	
	   		$vbseo_vars[$xs] += strlen($xcont);
		}



   		$vbseo_vars['sitemap_content'] = array();

   		if(!$vbseo_vars['txt_started'] || $split)
   			$vbseo_vars['txt_started']++;


   		if(!$split)
   			return;

		fwrite($vbseo_vars['pf'], "\n</urlset>");
		fclose($vbseo_vars['pf']);
		$vbseo_vars[$xs] = filesize($vbseo_vars['pfname']);
		vbseo_gz_compress($vbseo_vars['pfname']);
		@chmod(VBSEO_DAT_FOLDER . $sm_filename, 0666);

		if($vbseo_vars['pf2'])
		{
			fclose($vbseo_vars['pf2']);
			@chmod($vbseo_vars['pf2name'], 0666);
		}

   		vbseo_log_entry("[create sitemap file] filename: $sm_filename, number of urls: $vbseo_stat[urls_no]", true);
   		$GLOBALS['vbseo_sm_dupcheck'] = array();


   		if(function_exists('clearstatcache'))
	   		clearstatcache();
   		$vbseo_vars['sitemap_files'][] = array(
   			'url'=>vbseo_sitemap_furl($sm_filename),
   			'size'=>filesize($vbseo_vars['pfname']),
   			'uncompsize'=>$vbseo_vars[$xs],
   			'urls'=>$vbseo_stat['urls_no'],
   			);

   		$vbseo_stat['urls_no'] = 0;
   		$vbseo_vars['sm_done']++;
   		$vbseo_vars['sitemap_counter']++;
		vbseo_save_progress();

		if($vbseo_vars['sm_done']==$vbseo_vars['split_generation'])
		{
	   		vbseo_log_entry("[split generation] STOP", true);
	   		exit;
		}
//	exit;	

		if(!$last)
			sleep($vboptions['vbseo_sm_delay']);

   		return;
   	}

   	function vbseo_flush_index()
   	{
   		global $vbseo_vars, $vboptions;

   		vbseo_flush_sitemap(true, true);

   		$sm_filename = vbseo_ext_gz('sitemap_index.xml');

   		$smaps = '';
    	foreach($vbseo_vars['sitemap_files'] as $smfile)
    		$smaps.="<sitemap>
	<loc>".$smfile['url']."</loc>
	<lastmod>".date('Y-m-d\TH:i:s+00:00')."</lastmod>
</sitemap>\n";

   		$smcontent = 
'<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="'.vbseo_sitemap_furl('sitemap.xsl', 'vbseo_sitemap/').'"?>
<sitemapindex
      xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation="
            http://www.sitemaps.org/schemas/sitemap/0.9
            http://www.sitemaps.org/schemas/sitemap/09/siteindex.xsd">
'.$smaps.'
</sitemapindex>';//<?php

	    vbseo_write_file(VBSEO_DAT_FOLDER . $sm_filename, $smcontent);
		vbseo_gz_compress(VBSEO_DAT_FOLDER . $sm_filename);
   		vbseo_log_entry("[create sitemap index] filename: $sm_filename, number of sitemaps: ".count($vbseo_vars['sitemap_files']), true);

   		return;
   	}

   	function vbseo_set_sitemap_type($stype)
   	{
   		global $vbseo_vars;
   		vbseo_flush_sitemap(true);
   		$vbseo_vars['sitemap_type'] = $stype;
		if($vbseo_vars['resume'])
			$vbseo_vars['resume'] = false;
		else
   			$vbseo_vars['sitemap_counter'] = 0;
   	}

   	function vbseo_sitemap_ping_url($url)
   	{
   		global $vbseo_stat;
   		$vbseo_stat['ping'] = vbseo_sitemap_ping_one(
   			'http://www.google.com/webmasters/tools/ping?sitemap='.urlencode($url), 'Received');

   		$vbseo_stat['pingyahoo'] = vbseo_sitemap_ping_one(
   			'http://search.yahooapis.com/SiteExplorerService/V1/updateNotification?appid='.VBSEO_YAHOO_APPID.'&url='.urlencode($url),
   			'successfully');

   		$vbseo_stat['pingask'] = vbseo_sitemap_ping_one(
   			'http://submissions.ask.com/ping?sitemap='.urlencode($url),
		 	'successful');


   		$vbseo_stat['pingbing'] = vbseo_sitemap_ping_one(
   			'http://www.bing.com/webmaster/ping.aspx?siteMap='.urlencode($url),
			'Thanks for');

		return ;
   	}
   	
   	function vbseo_sitemap_ping_one($purl, $searchfor)
   	{
   		for($i = 0; $i<5; $i++)
   		{
			$rping = vbseo_query_http($purl);
			if(strstr($rping, $searchfor))
				return true;
			sleep(1);
		}
		return false;
   	}

   	function vbseo_sitemap_furl($sitemap, $addfolder = '')
   	{
   		global $vbseo_vars, $vboptions;

				
   		return $vbseo_vars['topurl'] . '/' . $addfolder . ($vboptions['vbseo_sm_norwurl'] ? 'vbseo_sitemap_file.php?sitemap=':'') . $sitemap;
   	}

   	function vbseo_sitemap_ping()
   	{   
   		global $vbseo_vars;

   		$smindex = vbseo_sitemap_furl('sitemap_index.xml.gz');
   		return vbseo_sitemap_ping_url($smindex);
   	}

   	function vbseo_sitemap_stat($stat, $email)
   	{
   		global $vbseo_vars, $vboptions;

		$stat['txt'] = $vbseo_vars['txt'];

		$logfname = VBSEO_DAT_FOLDER . time() . '.log';
   		$pf = fopen($logfname, 'w');
   		fwrite($pf, serialize($stat));
   		fclose($pf);
   		@chmod($logfname, 0666);

   		if(!$email) return;


   		$mailbody = 
"Hello!

The vBSEO Google/Bing Sitemap has been successfully generated for your vBulletin forums at:
$vboptions[bbtitle] ($vbseo_vars[bburl])

Report:
============================
Click the following link for your vBSEO Google/Bing Sitemap Report:
$vbseo_vars[bburl]/vbseo_sitemap/

Summary:
============================
Forum Display: ".$stat['f']."
Show Thread: ".$stat['t']."
Show Post: ".$stat['p']."
Member Profiles: ".$stat['m']."
Poll Results: ".$stat['poll']."
Blog Entries: ".$stat['blog']."
Blog Tags: ".$stat['blogtag']."
Album URLs: ".$stat['a']."
Social Groups URLs: ".$stat['g']."
Tag URLs: ".$stat['tag']."
Archive: ".($stat['af']+$stat['at'])."

Total Indexed URLs: ".$stat['urls_no_tot']."
Total Processing Time: ".number_format($stat['end']-$stat['start'],2)." seconds

Google ping: ".(isset($stat['ping'])?($stat['ping']?'Successful':'FAILED'):'Disabled').".
Bing ping: ".(isset($stat['pingbing'])?($stat['pingbing']?'Successful':'FAILED'):'Disabled').".
Yahoo ping: ".(isset($stat['pingyahoo'])?($stat['pingyahoo']?'Successful':'FAILED'):'Disabled').".
Ask ping: ".(isset($stat['pingask'])?($stat['pingask']?'Successful':'FAILED'):'Disabled').".

============================
vBSEO (c) 2010 Crawlability, Inc.
http://www.crawlability.com/vbseo
http://www.vbseo.com


Note for vBSEO users: This version of the sitemap generator works with vBSEO 3.3.0 up.
Please download the most recent vBSEO here: http://www.vbseo.com/downloads/
";

if(!VBSEO_ON)
	$mailbody .= "

Find out more out vBSEO - vBulletin Search Engine Optimization
============================

vBSEO is the definitive SEO enhancement for your vBulletin community forums!

vBSEO makes it easier for search engines to crawl more of your valuable vBulletin content faster and more often giving you higher keyword relevancy.

By installing vBSEO for your vBulletin forums you should expect to:

    * Get more of your forum pages indexed in the major search engines
    * Get your pages indexed faster
    * Improve your keyword relevancy for all pages
    * Prevent possible duplicate content penalties

The result of installing vBSEO you should expect is:

    * Higher visitor to member conversion rate (i.e. gain more new members faster)
    * Get visitors who are more highly targeted to the content you provide
    * Increase the monthly revenues earned from your forums
    * Improve your chances of achieving big-boards.com status

vBulletin + vBSEO
============================
Serious forum admins choose vBSEO for increased search engine traffic!
http://www.vbseo.com/purchase/
";
		if(function_exists('vbmail_start'))
		{
	        vbmail_start();
    	    vbmail($email, 'vBSEO Google/Bing Sitemap Updated', $mailbody);
        	vbmail_end();
        }else
        {
			mail($email, 
			'vBSEO Google/Bing Sitemap Updated', 
			$mailbody,
			"From: ".$email);
		}
   	}


// ================================================================================
// ================================================================================
// ================================================================================

	function vbseo_load_progress()
	{
		global $vbseo_progress, $vbseo_stat, $vbseo_vars;

		$vbseo_progress = array();
		if(file_exists(VBSEO_DAT_PROGRESS))
		{
			$vbseo_progress = unserialize(implode('',file(VBSEO_DAT_PROGRESS)));
			$vbseo_stat = $vbseo_progress['stats'];
			$vbseo_vars = $vbseo_progress['vars'];
			$vbseo_vars['resume'] = true;
			$vbseo_vars['sm_done'] = 0;
	       	vbseo_log_entry("[RESUME GENERATION] step#" . $vbseo_progress['step']);
		}
    }

	function vbseo_save_progress()
	{
		global $vbseo_progress, $vbseo_stat, $vbseo_vars;

		$vbseo_progress['stats'] = $vbseo_stat;
		$vbseo_progress['vars'] = $vbseo_vars;
		$pf = fopen(VBSEO_DAT_PROGRESS,'w');
		fwrite($pf, serialize($vbseo_progress));
		fclose($pf);
   		@chmod(VBSEO_DAT_PROGRESS, 0666);
    }

	function vbseo_clean_progress()
	{
		global $vbseo_progress;
		$vbseo_progress = array();
		@unlink(VBSEO_DAT_PROGRESS);
    }

	function vbseo_check_progress($step)
	{
		global $vbseo_progress;
   		if($vbseo_progress['step'] > $step)
   			return true;

   		if($vbseo_progress['step'] < $step)
   		{
   			$vbseo_progress['step2'] = $vbseo_progress['step3'] = 0;
   			$vbseo_progress['step'] = $step;
   			vbseo_save_progress();
   		}
   		return false;
	}

	function vbseo_inc_progress()
	{
		global $vbseo_progress;
		vbseo_check_progress($vbseo_progress['step']+1);
	}

	function vbseo_url_bburl($url)
	{
		global $vbseo_vars;
		return (strstr($url, '://') ? '' : $vbseo_vars['bburl']. ($url[0]=='/' ? '' : '/')) . $url;
	}

	function vbseo_url_forum($forum_id, $page = 1, $archived = false, $old = false)
	{
		global $vbseo_vars, $g_cache;
		
		$is_vbseo = (VBSEO_ON && VBSEO_REWRITE_FORUM && !$old);

		if($archived)
		{
			$url = ($is_vbseo ? VBSEO_ARCHIVE_ROOT : '/archive/index.'.VBSEO_PHP_EXT.VBSEO_SLASH_METHOD) .
				'f-'.$forum_id.($page>1?'-p-'.$page:'').'.html';
		}else
		{
			if($is_vbseo)
				$url = vbseo_forum_url($forum_id, $page);
			else
			{
				$url = $vbseo_vars['isvb4'] ?
					fetch_seo_url('forum|nosession', $g_cache['forum'][$forum_id], ($page>1)?array('page'=>$page):array())
					: 'forumdisplay.'.VBSEO_PHP_EXT.'?f='.$forum_id . ($page>1?'&amp;page='.$page:'');
			}
		}
		return vbseo_url_bburl($url);
	}

	function vbseo_url_blog_entry($blogrow, $old = false)
	{
		global $vbseo_vars, $g_cache;
		
		$is_vbseo = (VBSEO_ON && VBSEO_REWRITE_BLOGS && VBSEO_REWRITE_BLOGS_ENT && !$old);
		$blogrow['blog_title'] = $blogrow['title'];
		$g_cache['blog'][$blogrow['blogid']] = $blogrow;

		$url = 
			($is_vbseo ? 
				vbseo_blog_url(VBSEO_URL_BLOG_ENTRY, array('b'=>$blogrow['blogid'])) :
				(
				$vbseo_vars['isvb4'] ?
					fetch_seo_url('entry|nosession', $blogrow)
					: 'blog.'.VBSEO_PHP_EXT.'?b='.$blogrow['blogid'])
				);
		unset($g_cache['blog'][$blogrow['blogid']]);
		return vbseo_url_bburl($url);
	}

	function vbseo_url_cms($drow, $old = false)
	{
		global $vbseo_vars, $g_cache, $vboptions;
		
		$is_vbseo = (VBSEO_ON && VBSEO_REWRITE_CMS && !$old);
		$drow['id'] = $drow['nodeid'];
		$g_cache['cmscont'][$drow['nodeid']] = $drow;
		$r = ($drow['issection'] ? 'section/' : 'content/') . $drow['nodeid'];
		if($is_vbseo)
			$url = vbseo_cms_url($r);
		else
		{
			$url = 
			$vbseo_vars['isvb4'] ?
				preg_replace('#((vbseo.*?|cron.*?)(\.'.VBSEO_PHP_EXT.'|/))#', $vboptions['site_tab_url'], fetch_seo_url('vbcms|nosession', $drow))
				: 'content.'.VBSEO_PHP_EXT.'?r='.$r;
			if(!strstr($url, '://'))
				$url = $vboptions['vbcms_url'] . '/'. $url;
			
		}
		//unset($g_cache['cmscont'][$drow['nodeid']]);
		return vbseo_url_bburl($url);
	}

	function vbseo_url_thread($thread_row, $page = 1, $archived = false, $old = false)
	{
		global $vbseo_vars;

		$is_vbseo = (VBSEO_ON && VBSEO_REWRITE_THREADS && !$old);

		if($archived)
		{
			$url = ($is_vbseo ? VBSEO_ARCHIVE_ROOT : '/archive/index.'.VBSEO_PHP_EXT.''.VBSEO_SLASH_METHOD) .
				't-'.$thread_row['threadid'].($page>1?'-p-'.$page:'').'.html';
		}else
			$url = ($is_vbseo ? vbseo_thread_url_row($thread_row, $page) :
			(
			$vbseo_vars['isvb4'] ?
				fetch_seo_url('thread|nosession', $thread_row, ($page>1)?array('page'=>$page):array())
				: 'showthread.'.VBSEO_PHP_EXT.'?t='.$thread_row['threadid'].($page>1?'&amp;page='.$page:'')
			));
			
		return vbseo_url_bburl($url);
	}

	function vbseo_url_post($thread_row, $post_row, $postcount = 1, $old = false)
	{
		global $vbseo_vars;

		if(VBSEO_ON && VBSEO_REWRITE_SHOWPOST && !$old)
		{
			if(strstr(VBSEO_URL_POST_SHOW, '%t')||strstr(VBSEO_URL_POST_SHOW, '%f'))
			$url = vbseo_post_url_row($thread_row, $post_row, $postcount);
			else
			$url = str_replace(
				array('%post_id%','%post_count%'),
				array($post_row['postid'],$postcount),
				VBSEO_URL_POST_SHOW
			);
		}else
			$url = $vbseo_vars['isvb4'] ?
				fetch_seo_url('post|nosession', array_merge($post_row,$thread_row))
				: 'showpost.'.VBSEO_PHP_EXT.'?p='.$post_row['postid'].'&amp;postcount='.$postcount;
		return vbseo_url_bburl($url);
	}

	function vbseo_url_member($userid, $username, $old = false)
	{
		global $vbseo_vars;

		return vbseo_url_bburl(
			( (VBSEO_ON && VBSEO_REWRITE_MEMBERS && !$old) ? vbseo_member_url_row($userid, $username) :
			($vbseo_vars['isvb4'] ?
			 	fetch_seo_url('member|nosession', array('userid'=>$userid,'username'=>$username))
			 	: 'member.'.VBSEO_PHP_EXT.'?u='.$userid
			 )
			 )
			 ) ;
	}

	function vbseo_url_poll($threadrow, $getpoll, $old = false)
	{
		global $vbseo_vars;

		return vbseo_url_bburl(
			( (VBSEO_ON && VBSEO_REWRITE_POLLS && !$old) ? vbseo_poll_url_direct($threadrow, $getpoll) :
			 'poll.'.VBSEO_PHP_EXT.'?do=showresults&amp;pollid='.$getpoll['pollid'])
			 );
	}

	function vbseo_url_group($arow, $old = false, $format = '', $par = '')
	{
		global $vbseo_vars;
		$urlpar = $par ? array($par) : array();
		if($arow['groupid'])
			$urlpar[] = 'groupid='.$arow['groupid'];
		if($arow['pictureid'])
			$urlpar[] = 'pictureid='.$arow['pictureid'];
		if($arow['discussionid'])
			$urlpar[] = 'discussionid='.$arow['discussionid'];
		if($arow['page'])
			$urlpar[] = 'page='.$arow['page'];

		return vbseo_url_bburl(
			( (VBSEO_ON && VBSEO_REWRITE_GROUPS && !$old) ? 
				(function_exists('vbseo_group_url_row') ? vbseo_group_url_row($format , $arow) : '') :
			 	'group.'.VBSEO_PHP_EXT.''.($urlpar ? '?'.implode('&amp;', $urlpar):'')
			 	)
			 );
	}
	
	function vbseo_url_tag($arow, $old = false, $format = '', $par = '')
	{
		global $vbseo_vars;
		if(function_exists('unhtmlspecialchars'))
			$arow['tag'] = unhtmlspecialchars($arow['tag']);

		$arow['tag'] = urlencode($arow['tag']);
		$urlpar = $par ? array($par) : array();
		if($arow['tag'])
			$urlpar[] = 'tag='.$arow['tag'];
		if($arow['page'])
			$urlpar[] = 'page='.$arow['page'];

		return vbseo_url_bburl(
			( (VBSEO_ON && VBSEO_REWRITE_TAGS && !$old) ? 
				(function_exists('vbseo_tags_url') ? vbseo_tags_url($format , $arow) : '') :
			 	'tags.'.VBSEO_PHP_EXT.''.($urlpar ? '?'.implode('&amp;', $urlpar):'')
			 	)
			 );
	}
	
	function vbseo_url_blogtag($arow, $old = false, $format = '', $par = '')
	{
		global $vbseo_vars;
		if(function_exists('unhtmlspecialchars'))
			$arow['tag'] = unhtmlspecialchars($arow['tag']);

		$arow['tag'] = urlencode($arow['tag']);
		$urlpar = $par ? array($par) : array();
		if($arow['tag'])
			$urlpar[] = 'tag='.$arow['tag'];
		if($arow['page'])
			$urlpar[] = 'page='.$arow['page'];

		return vbseo_url_bburl(
			( (VBSEO_ON && VBSEO_REWRITE_BLOGS_TAGS_ENTRY && !$old) ? 
				(function_exists('vbseo_blog_url') ? vbseo_blog_url($format , $arow) : '') :
			 	($arow['tag'] ? 'blog.' : 'blog_tag.').
			 	VBSEO_PHP_EXT.''.($urlpar ? '?'.implode('&amp;', $urlpar):'')
			 	)
			 );
	}
	
	function vbseo_url_album($arow, $old = false, $format = '')
	{
		global $vbseo_vars;

		$urlpar = array();
		if($arow['userid'])
			$urlpar[] = 'u='.$arow['userid'];
		if($arow['albumid'])
			$urlpar[] = 'albumid='.$arow['albumid'];
		if($arow['pictureid'])
			$urlpar[] = 'pictureid='.$arow['pictureid'];
		if($arow['page'])
			$urlpar[] = 'page='.$arow['page'];

		return vbseo_url_bburl(
			( (VBSEO_ON && VBSEO_REWRITE_MEMBERS && !$old) ? 
				( function_exists('vbseo_album_url_row') ? vbseo_album_url_row(
				$format ? $format :
				($arow['pictureid'] ? 'VBSEO_URL_MEMBER_PICTURE' : 'VBSEO_URL_MEMBER_ALBUM'), 
				$arow):'') :
			 	'album.'.VBSEO_PHP_EXT.''.($urlpar ? '?'.implode('&amp;', $urlpar):'')
			 	)
			 );
	}
	
// ================================================================================
// ================================================================================
// ================================================================================

	function vbseo_sm_priority($pset, $pval)
	{
		global $vboptions;
		list($pmin, $pmax) = explode('-', $pset);
	    if(!$pmax || !$vboptions['vbseo_sm_priority_smart'])
	    	return $pmin;

   		$pval = min(1,max($pval,0));
   		$prior = $pmin + $pval * ($pmax - $pmin);
   		$prior = intval($prior*10000)/10000;
   		return $prior;
	}

   	function vbseo_math_avg_weight($value, $min, $max, $avg)
   	{
   		if($value > $avg)
   			$relp = (($max - $avg) > 0 ? 
   				($value - $avg)/($max - $avg)*0.5 : 0 ) + 0.5;
   		else
   			$relp = $avg > 0 ? ($avg - $value)*0.5/$avg : 0;

   		return $relp;
   	}

	function vbseo_dbtbl_exists($tblname)
	{
		global $db;
		$db->hide_errors();
      	$supported = $db->query_first("SHOW TABLES LIKE '" . TABLE_PREFIX . $tblname . "'");
		$db->show_errors();
		return $supported ? 1 : 0;
	}

   	function vbseo_log_entry($message, $more_important = false)
   	{
   		global $vbseo_vars, $vbseo_stat;

   		if((THIS_SCRIPT!='cron') && ($vbseo_vars['log_detailed'] || $more_important) )
   		{
	        if (function_exists('memory_get_usage'))
    	    	$message.=' ['.number_format(memory_get_usage()/1024,1).'Kb mem used]';
			$tm = array_sum(explode(' ', microtime()))-$vbseo_stat['start'];
			$message .= ' ['.number_format($tm,0).'s (+'.number_format($tm-$vbseo_vars['last_tm'],0).'s)]';
			$vbseo_vars['last_tm'] = $tm;
	   		echo $message."<br/>\n";
	   		flush();
	   	}
   	}

   	function vbseo_write_file($filename, &$filecont, $append = false)
   	{
	    $pf = fopen($filename, $append?'a':'w');
	    fwrite($pf, $filecont);
	    fclose($pf);
	    @chmod($filename, 0666);
   	}

   	function vbseo_ext_gz($filename)
   	{
    	return VBSEO_SM_GZFUNC ? $filename.'.gz' : $filename;
   	}

   	function vbseo_gz_compress($filename)
   	{
   		if(VBSEO_SM_GZFUNC && function_exists('gzopen') && file_exists($filename))
   		{
   			$pf = fopen($filename, 'r');
   			$fcont = fread($pf, filesize($filename));
   			fclose($pf);

   			$gf = gzopen($filename, 'w');
   			gzwrite($gf, $fcont);
   			gzclose($gf);
   		}
   	}

   	function vbseo_file_gz($filename)
   	{
   		if(file_exists(VBSEO_DAT_FOLDER.$filename.'.gz'))
   			return $filename.'.gz';
   			else
   			return $filename;
   	}

   	function vbseo_sm_prune($dir, $ftype = '.log', $prune_limit = 0)
   	{    
   		if((defined('VBSEO_SM_PRUNE') && VBSEO_SM_PRUNE) || $prune_limit)
   		{
   			$prune_limit = time() - ($prune_limit ? $prune_limit : VBSEO_SM_PRUNE) * 24 * 60 * 60;
   			if(is_dir($dir))
   			{
            	$pd = @opendir($dir);
            	while($fn = @readdir($pd))
            	if(strstr($fn, $ftype) && (filemtime($dir.$fn) < $prune_limit))
            		unlink($dir.$fn);
            	@closedir($pd);
        	}else
        	if(function_exists('vbseo_get_dllog'))
        	{
				$dl_list = vbseo_get_dllog();
				$dl_list2 = array();
            	foreach($dl_list as $dl)
            		if($dl['time'] >= $prune_limit)
            		$dl_list2[] = $dl;

            	$pf = fopen(VBSEO_SM_DLDAT, 'w');
            	fwrite($pf, serialize($dl_list2));
            	fclose($pf);
        	}
   			
   		}
   	}

	function vbseo_query_http($url)
	{

		$s = @implode('', file($url));
		if(!$s)
			$s = vbseo_query_http_socket($url);
		return $s;
	}

	function vbseo_query_http_socket($url)
	{
   	    ini_set('default_socket_timeout', 5);
   	    $purl = parse_url($url);
        $connsocket = @fsockopen($purl['host'], 80, $errno, $errstr, 5);
   		$start = 0;
   		$timeout = 50;
   		while($start < $timeout)
   		{
			$start++;
			if ($connsocket)
			{
             $out = "GET ".$purl['path']."?".$purl['query']." HTTP/1.1\n";
             $out .= "Host: ".$purl['host']."\n";
   		     $out .= "Referer: http://".$purl['host']."/\n";
             $out .= "Connection: Close\n\n";
     		 $inp = '';
             @fwrite($connsocket, $out);
             while (!feof($connsocket)) {
                $inp .= @fread($connsocket, 4096);
             }
             @fclose($connsocket);
			 break;
            }

		}
        preg_match("#^(.*?)\r?\n\r?\n(.*)$#s",$inp,$hm);
        return $hm[2];
	}
?>