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
    $mods = $db->query_read('SELECT mediaID,dateline FROM ' . TABLE_PREFIX . 'media'); 
    while ($mod = $db->fetch_array($mods)) 
    {     
        $url = $vbseo_vars['bburl'].'/media.php?do=details&mid='.$mod['mediaID'];

        if(VBSEO_ON) 
        $url = vbseo_any_url($url); 
        vbseo_add_url($url, '0.6', $mod['dateline'],'weekly'); 
    }
    
    $mods = $db->query_read('SELECT tagID,tagText,dateline FROM ' . TABLE_PREFIX . 'media_tag'); 
    while ($mod = $db->fetch_array($mods)) 
    {     
        $url = $vbseo_vars['bburl'].'/media.php?do=tag&tid='.$mod['tagText'];

        if(VBSEO_ON) 
        $url = vbseo_any_url($url); 
        vbseo_add_url($url, '0.45', $mod['dateline'],'daily'); 
    } 
?>