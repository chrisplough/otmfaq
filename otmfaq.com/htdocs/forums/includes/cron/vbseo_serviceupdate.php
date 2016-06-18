<?php

/************************************************************************************
* vBSEO 3.3.2 for vBulletin v3.x.x by Crawlability, Inc.                            *
*                                                                                   *
* Copyright © 2005-2009, Crawlability, Inc. All rights reserved.                    *
* You may not redistribute this file or its derivatives without written permission. *
*                                                                                   *
* Sales Email: sales@crawlability.com                                               *
*                                                                                   *
*----------------------------vBSEO IS NOT FREE SOFTWARE-----------------------------*
* http://www.crawlability.com/vbseo/license/                                        *
************************************************************************************/

error_reporting(E_ALL & ~E_NOTICE);
if (!is_object($vbulletin->db))
{
exit;
}
include_once dirname(__FILE__).'/../../includes/functions_vbseo.php';
vbseo_extra_inc('linkback');
vbseo_startup();
$vbseodb = vbseo_get_db();
$threads_to_update = $vbseodb->vbseodb_query("
SELECT s_threadid,s_type
FROM " . vbseo_tbl_prefix('vbseo_serviceupdate') . "
WHERE s_threadid>0 AND s_updated = 0
"
);
while ($tinfo = @$vbseodb->funcs['fetch_assoc']($threads_to_update))
{
if($tinfo['s_type'] == 1)
{
vbseo_get_blog_info(array($tinfo['s_threadid']));
$vbseo_url_t = vbseo_blog_url(VBSEO_URL_BLOG_ENTRY, array('b'=>$tinfo['s_threadid']));
}else
{
vbseo_get_thread_info($tinfo['s_threadid']);
$vbseo_url_t = vbseo_thread_url($tinfo['s_threadid']);
}
if(!strstr($vbseo_url_t, '://'))
$vbseo_url_t = $vboptions['bburl2'] . '/' . $vbseo_url_t;
vbseo_do_service_update($vbseo_url_t);
$vbseodb->vbseodb_query($q="
UPDATE " . vbseo_tbl_prefix('vbseo_serviceupdate') . "
SET s_updated=1, s_dateline = ".TIMENOW."
WHERE s_threadid = ".$tinfo['s_threadid']
);
}
log_cron_action('', $nextitem, 1);
?>