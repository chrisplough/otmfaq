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

 	if(!defined('VBSEO_SMDIR'))exit;
	$ev_list = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "event WHERE visible = 1");
	
	while ($event = $db->fetch_array($ev_list))
	{
		$url = 'calendar.php?do=getinfo&amp;e=' . $event['eventid'] . '&amp;day=' . 
			date('Y-m-d', $event['dateline_from']) . '&amp;c=' . $event['calendarid'];

		if(VBSEO_ON)
			$url = vbseo_any_url($url);

		if(!strstr($url, '://'))
			$url = $vbseo_vars['bburl'] . '/' . $url;
  		vbseo_add_url($url, 0.5, '', 'daily');
	}

?>