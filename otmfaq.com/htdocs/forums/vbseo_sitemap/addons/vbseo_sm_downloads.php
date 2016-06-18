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
$mods = $db->query("SELECT id,name FROM " . TABLE_PREFIX . "dl_cats ORDER BY `id`");
while ($mod = $db->fetch_array($mods))
{ 
$url = $vbseo_vars['bburl'].'/downloads.php?do=cat&id='.$mod['id'];
if(VBSEO_ON)
$url = vbseo_any_url($url);
vbseo_add_url($url, 1.0, '', 'daily');
}
$mods = $db->query("SELECT id as fid FROM " . TABLE_PREFIX . "dl_files");
while ($mod = $db->fetch_array($mods))
{ 
$url = $vbseo_vars['bburl'].'/downloads.php?do=file&id='.$mod['fid'];
if(VBSEO_ON)
$url = vbseo_any_url($url);
vbseo_add_url($url, 1.0, '', 'daily');
}
?>