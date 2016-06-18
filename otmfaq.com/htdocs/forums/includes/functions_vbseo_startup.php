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

function vbseo_startup()
{
vbseo_get_options();
vbseo_prepare_seo_replace();
vbseo_get_forum_info();
}
function vbseo_get_options($getuserinfo = true)
{
global $vboptions, $bbuserinfo, $vbulletin, $vbseo_gcache,
$forumcache, $threadcache, $config, $session,
$GAS_settings, $vbseo_bitfields, $vbseo_cache;
vbseo_cache_start();
if (!isset($vboptions) || !isset($vboptions['bburl']) || !isset($forumcache))
{
if (isset($vbulletin) && isset($vbulletin->options))
{
$vboptions = $vbulletin->options;
$forumcache = $vbulletin->forumcache;
$session = $vbulletin->session->vars;
}
else
{
$options = &$vboptions;
$bitfields = &$vbseo_bitfields;
$optimported = false;
if ($GLOBALS['config']['Datastore']['class'] == 'vB_Datastore_Filecache')
{
$dsfolder = VBSEO_DIRNAME . '/datastore';
$include_return = @include_once($dsfolder . '/datastore_cache.' . VBSEO_VB_EXT);
if($options)$optimported = true;
}
if(!$optimported)
{
$optarr2 = array('options', 'bitfields', 'forumcache', 'GAS_settings');
if ($optarr2)
{
$db = vbseo_get_db();
$rid = $db->vbseodb_query("select title,data from " . vbseo_tbl_prefix('datastore') . "
where title in ('" . implode("','", $optarr2) . "')");
if ($rid)
{
while ($dstore = @$db->funcs['fetch_assoc']($rid))
{
$$dstore['title'] = @unserialize($dstore['data']);
if(!$$dstore['title'])
$$dstore['title'] = @unserialize(utf8_decode($dstore['data']));
}
$db->vbseodb_free_result($rid);
}
}
}
}
}
if (defined('VBSEO_CUSTOM_BBURL'))
$vboptions['bburl'] = VBSEO_CUSTOM_BBURL;
$vboptions['bburl2'] = vbseo_http_s_url(preg_replace('#/+$#', '', $vboptions['bburl']));
$vboptions['cutbburl'] = preg_replace('#^https?://[^/]+(.*)$#', '$1', $vboptions['bburl2']);
$vboptions['relbburl'] = VBSEO_USE_HOSTNAME_IN_URL ? $vboptions['bburl2'] : $vboptions['cutbburl'];
$vbseo_gcache['post'] = isset($GLOBALS['itemids'])?$GLOBALS['itemids']:(isset($GLOBALS['postcache'])?$GLOBALS['postcache']:array());
if (isset($GLOBALS['getlastpost']) && $GLOBALS['getlastpost']['postid'])
{
$vbseo_gcache['post'][$GLOBALS['getlastpost']['postid']] = $GLOBALS['getlastpost'];
}
$url = isset($vboptions['forumhome'])?($vboptions['forumhome'] . '.' . VBSEO_VB_EXT . ''):'';
if ((($url == 'index.' . VBSEO_VB_EXT) || VBSEO_FORCEHOMEPAGE_ROOT) && VBSEO_HP_FORCEINDEXROOT)
$url = '';
@define('VBSEO_HOMEPAGE', $url);
if (isset($threadcache))
foreach($threadcache as $threadid => $tar)
{
if ($tar['firstpostid'] && !isset($vbseo_gcache['post'][$tar['firstpostid']]))
$vbseo_gcache['post'][$tar['firstpostid']] = array('threadid' => $threadid,
'postid' => $tar['firstpostid']
);
if ($tar['lastpostid'] && !isset($vbseo_gcache['post'][$tar['lastpostid']]))
$vbseo_gcache['post'][$tar['lastpostid']] = array('threadid' => $threadid,
'postid' => $tar['lastpostid']
);
}
if ($getuserinfo)
{
if (isset($vbulletin) && (!$bbuserinfo || !$bbuserinfo['usergroupid']))
$bbuserinfo = $vbulletin->userinfo;
if (!isset($bbuserinfo) || !$bbuserinfo['userid'] ||
(isset($vbulletin) && !$bbuserinfo['usergroupid'])
)
{
$cvisit = @intval($_COOKIE[vbseo_vb_cprefix() . 'lastvisit']);
$bbuserinfo['lastvisit'] = $cvisit ? $cvisit : time();
if ($_COOKIE[vbseo_vb_cprefix() . 'userid'] && !$bbuserinfo['userid'])
$bbuserinfo['userid'] = $_COOKIE[vbseo_vb_cprefix() . 'userid'];
}
$bbuserinfo['isadmin'] = isset($bbuserinfo['usergroupid']) && ($bbuserinfo['usergroupid'] == 6);
}
vbseo_check_confirmation();
vbseo_check_datastore();
}
function vbseo_check_datastore($forceupdate = false)
{
global $vboptions, $forumcache, $vbseo_gcache;
$opt = isset($vboptions['vbseo_opt']) ? $vboptions['vbseo_opt'] : array();
if (!$forceupdate && isset($opt['stamp']) && (VBSEO_TIMESTAMP < ($opt['stamp'] + 86400)))
return;
$opt['forumthreads'] = $opt['forumpaths'] = array();
$totalthreads = 0;
if ($forumcache)
foreach($forumcache as $forumid => $finfo)
{
if (isset($finfo['forumid']))
{
$tcount = (isset($finfo['forumid']) && $finfo['threadcount']) ?
$finfo['threadcount'] : (isset($vbseo_gcache['forum'])?$vbseo_gcache['forum'][$finfo['forumid']]['threadcount']:0);
if ($tcount)
$opt['forumthreads'][$finfo['forumid']] = $tcount;
$totalthreads += $tcount;
if (($fpath = $finfo['path']) || ($fpath = $vbseo_gcache['forum'][$finfo['forumid']]['path']))
$opt['forumpaths'][$finfo['forumid']] = $fpath;
}
}
if (!$totalthreads)return;
vbseo_update_datastore($opt);
}
function vbseo_update_datastore($opt)
{
global $vboptions, $vbulletin;
$vbo = vbseo_get_datastore('options');
$opt['stamp'] = VBSEO_TIMESTAMP;
$vboptions['vbseo_opt'] = $vbo['vbseo_opt'] = $opt;
vbseo_set_datastore('options', $vbo);
}
function vbseo_get_datastore($record)
{
global $vbseo_isvb_360;
$db = vbseo_get_db();
$rid = $db->vbseodb_query("select * from " . vbseo_tbl_prefix('datastore') . " where title = '$record'");
if ($rid)
{
$vbseostore = @$db->funcs['fetch_assoc']($rid);
$db->vbseodb_free_result($rid);
if (!$vbseo_isvb_360)
$vbseo_isvb_360 = isset($vbseostore['unserialize']);
return @unserialize($vbseostore['data']);
}
else
return array();
}
function vbseo_set_datastore($record, $arr)
{
global $vbulletin, $vbseo_isvb_360, $vbseo_gcache;
if($vbseo_gcache['var']['vboptchanged']) return;
$db = vbseo_get_db();
$db->vbseodb_query($q = "REPLACE INTO " . vbseo_tbl_prefix('datastore') . " (title, data" . ($vbseo_isvb_360?",unserialize":"") . ")
VALUES ('$record', '" . addslashes(serialize($arr)) . "'" . ($vbseo_isvb_360?",1":"") . ")");
if (($record == 'options') && $vbulletin && @method_exists($vbulletin->datastore, 'build'))
{
$vbulletin->datastore->build($record, serialize($arr));
}
}
function vbseo_check_keys($thekey, &$keys)
{
global $vboptions;
$lic_type = 0;
if (strstr($thekey, $keys[0]))
$lic_type = 1; 
else
if (strstr($thekey, $keys[1]))
$lic_type = 2; 
else
if (strstr($thekey, $keys[2]))
$lic_type = 3; 
else
if (strstr($thekey, $keys[3]))
$lic_type = 4; 
$fail_params = array();
if (defined('VBSEO_LICENSE_TYPE'))
{
/*
if( ($lic_type != VBSEO_LICENSE_TYPE)
&&
($vboptions['vbseo_confirmation_code']!=$keys[VBSEO_LICENSE_TYPE-1])
)
$fail_params[] = "vBSEO License Type redefinition detected.";
*/
}
else
{
if (!$lic_type) return false;
define('VBSEO_LICENSE_TYPE', $lic_type);
$type_string = array('', 'Pro', 'Lite', 'Standard', 'Branding free');
define('VBSEO_LICENSE_STR', $type_string[$lic_type]);
switch (VBSEO_LICENSE_TYPE)
{
case 4:
define('VBSEO_BRANDING_FREE', true);
case 1:
define('VBSEO_LICENSE_CRR', true);
break;
case 3:
break;
case 2:
define('VBSEO_LITE', true);
break;
}
}
if (count($fail_params))
{
if (defined('VBSEO_NO_LICENSE_CHECK_5342')) return;
echo 'vBSEO license usage error: ' .
implode(', ', $fail_params) . '<br />Please correct the mentioned issues or contact licenses@crawlability.com';
exit();
}
return ($lic_type ? true : false);
}
function vbseo_check_confirmation()
{
global $vboptions, $vbulletin, $licresponse;
if (!$vboptions['bburl2'])
return;
$url = $vboptions['bburl'];
if (!strstr($url, '://'))$url = 'http://' . $url;
$purl = @parse_url($url);
$dom = preg_replace('#\bwww\.#', '', $purl['host']);
$vbtop = $dom . VBSEO_VERSION2_MORE;
$vbseo_keys = array(
md5(md5($vbtop . 'Xi8J0)DZ5O9FN9gt')),
md5(md5($vbtop . 'Ak9K8;MZ6K1RK4iw')),
md5(md5($vbtop . 'Ga4H6^NA1W7TT9il')),
md5(md5($vbtop . 'Os9U0_OI5J7LX9jn')),
);
if (vbseo_check_keys(VBSEO_LICENSE_CODE, $vbseo_keys))
$vboptions['vbseo_confirmation_code'] = VBSEO_LICENSE_CODE;
if (!defined('VBSEO_IS_VBSEOCP') && 
vbseo_check_keys($vboptions['vbseo_confirmation_code'], $vbseo_keys)) 
return;
$db = vbseo_get_db();
$vbo = vbseo_get_datastore('options');
$vbseoo = vbseo_get_datastore('vbseo_options');
if (!defined('VBSEO_IS_VBSEOCP') && vbseo_check_keys($vbseoo['license'], $vbseo_keys))
{
$vboptions['vbseo_confirmation_code'] = $vbo['vbseo_confirmation_code'] = $vbseoo['license'];
$check_again = false;
}
else
{
$qurl = 'http://www.crawlability.com/';
$qurl .= 'vbseo-reg/vbseo-reg.php?vbtop=' . urlencode($vboptions['bburl']) . '&ver=' . urlencode(VBSEO_VERSION2_MORE) . '&t=5&ccode=' . urlencode(substr($vboptions['vbseo_confirmation_code'], 0, 100));
$gq = vbseo_http_query($qurl);
$lcode = substr(preg_replace('#[^\w ]#', '', $gq), 0, 100);
if($lcode)
$vboptions['vbseo_confirmation_code'] = $vbseoo['license'] = $vbo['vbseo_confirmation_code'] = $lcode;
$check_again = true;
}
vbseo_set_datastore('vbseo_options', $vbseoo);
vbseo_set_datastore('options', $vbo);
if (
!vbseo_check_keys($vbo['vbseo_confirmation_code'], $vbseo_keys) && !defined('VBSEO_NO_LICENSE_CHECK_5342'))
{
define('VBSEO_UNREG', true);
if (defined('VBSEO_LITE_DEFAULT'))
define('VBSEO_LITE', true);
if (VBSEO_EXPIRED_MORE)
define('VBSEO_UNREG_EXPIRED', 1);
}
}
?>