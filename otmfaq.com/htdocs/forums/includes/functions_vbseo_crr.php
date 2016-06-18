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

function vbseo_apply_crr($ourl, &$nofollow)
{
global $vbseo_crules;
$newurl = '';
if(!isset($vbseo_crules))
$vbseo_crules = vbseo_fw_customurl('rules');
if($vbseo_crules)
{
if(strpos($ourl, '#') !== false)
list($ourl1, $ourl2) = explode('#', $ourl);
else
{
$ourl1 = $ourl;
$ourl2 = '';
}
$newurl1 = preg_replace( array_keys($vbseo_crules), $vbseo_crules, $ourl1 ) . ($ourl2 ? '#'.$ourl2:'');
if($ourl != $newurl1)
{
$newurl = $newurl1;
if(strstr($newurl,'[NF]'))
{
$newurl = str_replace('[NF]', '', $newurl);
$nofollow = true;
}
}
}
return $newurl;
}
function vbseo_fw_customurl($ctype = 'rules')
{
$vbseo_crules = array();
$ctype = ($ctype == 'rules')?'vbseo_custom_rules':'vbseo_custom_301';
vbseo_cache_start();
$vbseo_crules = $GLOBALS['vbseo_cache']->cacheget($ctype);
if ($GLOBALS[$ctype] && !$vbseo_crules)
{
foreach($GLOBALS[$ctype] as $k => $v)
if ($k)
$vbseo_crules['#' . str_replace(array('#', '&'), array('\#', '&(?:amp;)?'), $k) . '#'] = $v;
$GLOBALS['vbseo_cache']->cacheset($ctype, $vbseo_crules);
}
return $vbseo_crules;
}
function vbseo_back_customurl($url)
{
global $vbseo_crules_back;
$sugg_mark = '#s#';
vbseo_cache_start();
$vbseo_crules_back_array = $GLOBALS['vbseo_cache']->cacheget('vbseo_crules_back');
if (!$vbseo_crules_back_array)
{
$vbseo_crules_back_array = array();
foreach($GLOBALS['vbseo_custom_rules'] as $k => $v)
{
if($v[0] == '/')$v = substr($v, 1);
$v = str_replace('[NF]', '', $v);
preg_match_all('#\$(\d+)#', $v, $GLOBALS['vbseo_lv_vm']);
preg_match_all('#\(.*?\)#', $k, $GLOBALS['vbseo_lv_km']);
$v = preg_replace('#\$(\d+)#e', '$GLOBALS["vbseo_lv_km"][0][\\1-1]', str_replace('\$', '$', preg_quote($v, '#')));
$ki = 0;
$k = preg_replace('#[^\\\\\]\)]\?#', '', $k);
$k = preg_replace('#\(.*?\)#e', '"$".(array_search(++$ki,$GLOBALS["vbseo_lv_vm"][1])+1)', stripslashes($k));
$k = preg_replace('#\$\d+\?#', '', $k);
$k = preg_replace('#.[\*\+]\??#', '', $k);
if ($k[0] == '^')
{
$v = '^' . $v;
$k = substr($k, 1);
}
$vadd = '';
if ($k[strlen($k)-1] == '$')
{
$vadd = '$';
$k = substr($k, 0, strlen($k)-1);
}
$vbseo_crules_back_array[0]['#' . $v . $vadd . '#'] = $k;
if ($v[strlen($v) - 1] == '/')
{
$v .= '?';
$k .= $sugg_mark;
}
$v = $v . $vadd;
$vbseo_crules_back_array[1]['#' . $v . '#'] = $k;
}
$GLOBALS['vbseo_cache']->cacheset('vbseo_crules_back', $vbseo_crules_back_array);
}
$vbseo_crules_back = $vbseo_crules_back_array ? $vbseo_crules_back_array[$url[strlen($url)-1] == '/' ? 0 : 1] : array();
$newurl = preg_replace(array_keys($vbseo_crules_back), $vbseo_crules_back, $url);
if ($newurl != $url)
{
if (strstr($newurl, $sugg_mark) && $url[strlen($url)-1] != '/')
{
$GLOBALS['vbseo_url_suggest'] = $url . '/';
return '';
}
$newurl = str_replace($sugg_mark, '', $newurl);
return $newurl;
}
else
return '';
}
?>