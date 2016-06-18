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
	// replace the URL below with your vBa links main folder URL
	$vba_links_url = 'http://www.domain.com/links/';

	$lnkg = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "adv_links_categories WHERE private = 0");
	while ($lnk = $db->fetch_array($lnkg))
	{
		$url = 'category-'.$lnk['catid'].'/';
		$url = $vba_links_url . $url;
  		vbseo_add_url($url, 1.0, $lnk['lastupdated'], 'daily');
	}

	$lnkg = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "adv_links WHERE valid != 2 AND open > 0");

	while ($lnk = $db->fetch_array($lnkg))
	{
		$url = 'category-'.$lnk['catid'].'/link-'.$lnk['linkid'].'/';
		$url = $vba_links_url . str_replace($vbseo_vars['bburl'] . '/', '', $url);
  		vbseo_add_url($url, 0.5, $lnk['lastupdated'] ? $lnk['lastupdated'] : $lnk['dateline'], 'weekly');

	}

?>