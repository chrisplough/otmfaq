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
	define(VBSEO_NOTLOGGEDIN_GID, 1); // change this to your "Unregistered / Not Logged In Usergroup ID"
	$vba_url = $vbseo_vars['bburl'] . '/';

	// uncomment the line below if your vbadvanced root URL is in upper level relative to vB url
	//$vba_url = preg_replace('#[^/]*/$#', '', $vba_url);

	$vba_options = array();

	$q_opts = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "adv_setting");
	while ($opt = $db->fetch_array($q_opts))
		$vba_options[$opt['varname']] = $opt['value'];

	$mods = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "adv_pages WHERE active = 1 ORDER BY displayorder");
	while ($mod = $db->fetch_array($mods))
	if(in_array(VBSEO_NOTLOGGEDIN_GID, explode(',',$mod['userperms'])))
	{	
		$url = $vba_options['portal_homeurl'].'?'.$vba_options['portal_pagevar'].'='.$mod['name'];

		if(VBSEO_ON)
			$url = vbseo_any_url($url);

		if(!strstr($url, '://'))
			$url = $vba_url.$url;

  		vbseo_add_url($url, 1.0, '', 'daily');
	}

?>