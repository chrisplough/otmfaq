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
    $mods = $db->query("SELECT catid,title,lastimagedateline FROM " . TABLE_PREFIX . "ppgal_categories ORDER BY `displayorder`");
    while ($mod = $db->fetch_array($mods))
    {    
        $url = $gallery_url.'/browseimages.php?c='.$mod['catid'];

        if(VBSEO_ON)
            $url = vbseo_any_url($url);

          vbseo_add_url($url, '0.3', $mod['lastimagedateline'], 'daily');
    }

    $mods = $db->query_read('SELECT imageid,dateline,originalname FROM ' . TABLE_PREFIX . 'ppgal_images');
    while ($mod = $db->fetch_array($mods))
    {    
        $url = $vbseo_vars['bburl'].'/showimage.php?i='.$mod['imageid'];

        if(VBSEO_ON)
            $url = vbseo_any_url($url);
          vbseo_add_url($url, '0.4', $mod['dateline'],'daily');

        if ($mod['originalname']){
        $url = $vbseo_vars['bburl'].'/showimage.php?i='.$mod['imageid'].'&original=1';

        if(VBSEO_ON)
            $url = vbseo_any_url($url);
          vbseo_add_url($url, '0.4', $mod['dateline'],'daily');
        }
    }
?>