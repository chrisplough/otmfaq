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

	define('VBSEO_SMDIR', dirname(__FILE__));
   	define('VBSEO_DAT_FOLDER', VBSEO_SMDIR . '/data/');
   	define('VBSEO_DAT_FOLDER_BOT', VBSEO_DAT_FOLDER . 'hits/');
   	define('VBSEO_DAT_FOLDER_ADDON', VBSEO_SMDIR . '/addons/');
   	//define('VBSEO_DAT_FOLDER', dirname(__FILE__) . '/../'); // choose this to place sitemap files directly to your vB root
   	define('VBSEO_DAT_PROGRESS', VBSEO_DAT_FOLDER . 'progress.dat');
   	define('VBSEO_YAHOO_SM', 'urllist.txt');
   	define('VBSEO_PHP_EXT', 'php');
    define('VBSEO_SM_DLDAT', VBSEO_DAT_FOLDER.'downloads.dat');

	define('VBSEO_SORT_ORDER', 'desc');
	define('VBSEO_SM_PAGESIZE', 20);
	define('VBSEO_SM_PRUNE', 90); // logs age in days to prune 
	define('VBSEO_SM_GZFUNC', true);
	define('VBSEO_YAHOO_APPID','GQq1UYPV34GiI.8XJTk0cwlfGZLOfz4Qd4eV_FGiVKZ6azNXF20J5tb5UdVl');
	
	global $vbseo_vars;
   	$vbseo_vars = array(
   	'log_detailed'=>false,
   	'extra_urls' => VBSEO_SMDIR.'/extra-urls.txt',
   	'sitemap_content' => array(),
   	'sitemap_files' => array(),
   	'forumslist' => array(),
   	'tpl_update' => 0,
   	'split_generation' => 0, // stop generation after each N sitemap are created (generation will be resumed with next execution)
   	'filesize_limit' => 10000000
   	);
?>