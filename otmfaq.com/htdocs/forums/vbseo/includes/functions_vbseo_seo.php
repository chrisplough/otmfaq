<?php

/************************************************************************************
* vBSEO 3.6.0 for vBulletin v3.x & v4.x by Crawlability, Inc.                       *
*                                                                                   *
* Copyright © 2011, Crawlability, Inc. All rights reserved.                         *
* You may not redistribute this file or its derivatives without written permission. *
*                                                                                   *
* Sales Email: sales@crawlability.com                                               *
*                                                                                   *
*----------------------------vBSEO IS NOT FREE SOFTWARE-----------------------------*
* http://www.crawlability.com/vbseo/license/                                        *
************************************************************************************/

function vbseo_seo_replace_callback($sk, $sv, $pretag, $prefound, $found, $afterpart)
{
global $vbseo_acronym_format, $vbseo_acronym_counter;
$found = str_replace('\\"', '"', $found);
$afterpart = str_replace('\\"', '"', $afterpart);
$prepre = str_replace('\\"', '"', $pretag . $prefound);
if(VBSEO_ACRONYM_PAGELIMIT > 0)
{
if(!isset($vbseo_acronym_counter))
$vbseo_acronym_counter = array();
if(++$vbseo_acronym_counter[$sk] > VBSEO_ACRONYM_PAGELIMIT)
return $prepre . $found . $afterpart;
}
$islink = strstr($found, "http:") || strstr($found, "https:") || strstr($found, "www.");
$formatlink = strstr($sv, '://');
$asv = str_replace('%1', $sv, str_replace('%2', $found, $vbseo_acronym_format));
$asv2 = str_replace('%1', $sv, str_replace('%2', '$1', $vbseo_acronym_format));
if(!$formatlink)
{
$asv = preg_replace('#\b(' . $sk . ')\b#i', $found, $asv);
$asv2 = preg_replace('#\b(' . $sk . ')\b#i', '\$1', $asv2);
}
$rapplied = 0;
if(VBSEO_ACRONYM_PAGELIMIT > 0)
{
$after_repl = @preg_replace('#\b(' . $sk . ')\b#i', $asv2, $afterpart,
(VBSEO_ACRONYM_PAGELIMIT ? max(VBSEO_ACRONYM_PAGELIMIT - $vbseo_acronym_counter[$sk],0) : -1), 
$rapplied);
$vbseo_acronym_counter[$sk] += $rapplied;
}else
$after_repl = preg_replace('#\b(' . $sk . ')\b#i', $asv2, $afterpart);
$modstring = $prepre .
($islink ? 
$found . $afterpart: 
$asv . $after_repl);
return $modstring;
}
function vbseo_prepare_seo_replace()
{
global $seo_replacements, $seo_replace_inurls, $seo_preg_replace, $vbseo_acronym_format;
global $vbulletin;
if((vbseo_vb_userid()>0) && VBSEO_ACRONYM_GUESTS)
{
$seo_replacements = $seo_replace_inurls = $seo_preg_replace = array();
return;
}
if ($seo_replace_inurls)return;
$vbseo_acronym_format = '';
switch(VBSEO_ACRONYM_SET)
{
case 1:
$vbseo_acronym_format = '%1';
break;
case 2:
$vbseo_acronym_format = '<acronym title="%1">%2</acronym>';
break;
case 3:
$vbseo_acronym_format = '<abbr title="%1">%2</abbr>';
break;
}
$seo_replace_inurls = $seo_preg_replace = $seo_links_replace = array();
reset($seo_replacements);
foreach($seo_replacements as $sk => $sv)
{
if (strstr($sv, 'http:')||strstr($sv, 'https:'))
$sv = str_replace('%1', $sv, str_replace('%2', $sk, VBSEO_AUTOLINK_FORMAT));
else
{
if (VBSEO_REWRITE_KEYWORDS_IN_URLS)
$seo_replace_inurls['#\b' . $sk . '\b#i'] = str_replace(' ', '-', strtolower($sv));
}
if ($vbseo_acronym_format)
{
if(0&&VBSEO_ACRONYMS_IN_CONTENT)
{
$asv = str_replace('%1', $sv, str_replace('%2', '\\1', $vbseo_acronym_format));
$seo_preg_replace['#\b(' . $sk . ')\b#i'] = $asv;
}
else
{
$r1 = (VBSEO_UTF8_SUPPORT?'\\s|':'\\b');
$r2 = (VBSEO_UTF8_SUPPORT?'<|\\s':'\\b');
$seo_preg_replace['#(<(?:[^sa]|sp)[^<]*>)([^<]*?'.$r1.')(' . $sk . ')('.$r2.'[^<]*)#eis'.(VBSEO_UTF8_SUPPORT?'u':'')] = 
'vbseo_seo_replace_callback(\''.str_replace("'","\\'",$sk).'\',\''.
str_replace("'","\\'",$sv).'\',\'$1\',\'$2\',\'$3\',\'$4\')';
}
}
}
}
function vbseo_process_cms_article(&$view)
{
$view->pagetext = vbseo_process_content_area($view->pagetext);
vBSEO_Storage::set('cms_text',$view->pagetext);
vBSEO_Storage::set('cms_title',$view->title);
if(class_exists('vBSEO_UI'))
{
vBSEO_UI::cms_hook($view);
}
}
function vbseo_process_content_area($text)
{
global $seo_preg_replace;
if($seo_preg_replace && VBSEO_ACRONYMS_IN_CONTENT)
{
$text = '<z>'.$text;
$text = preg_replace(array_keys($seo_preg_replace), $seo_preg_replace, $text);
$text = substr($text, 3);
}
$text = vbseo_google_ad_section($text);
return $text;
}
function vbseo_process_content_tpl($tplname)
{
if(vbseo_tpl_exists($tplpostbit))
$vbulletin->templatecache[$tplname] = 
vbseo_process_content_area($vbulletin->templatecache[$tplname]);
}
function vbseo_filter_replace_text($text, $allowchars = null, $filter_stop_words = true, $reversable = false)
{
global $seo_replace_inurls;
$text = $seo_replace_inurls ? preg_replace(array_keys($seo_replace_inurls),
$seo_replace_inurls, $text) : $text;
$text = vbseo_filter_text($text, $allowchars, $filter_stop_words, $reversable);
return $text;
}
function vbseo_replace_meta($metaname, $replace_content)
{
$GLOBALS['vbseo_meta'][$metaname] = $replace_content;
}
function vbseo_insert_code($inscode, $place)
{
global $vbseo_inscode;
$vbseo_inscode[$place] .= $inscode."\n";
}
function vbseo_insert_code_parse()
{
global $vbseo_inscode;
if(isset($vbseo_inscode['onload']))
{
vbseo_insert_code('
window.orig_onload = window.onload;
window.onload = function() {
'.$vbseo_inscode['onload'].'
if(typeof window.orig_onload == "function") window.orig_onload();
}', 'body_end_js');
unset($vbseo_inscode['onload']);
}
if(is_array($vbseo_inscode))
foreach($vbseo_inscode as $place=>$inscode)
if(strstr($place, '_js'))
{
vbseo_insert_code('
<script type="text/javascript">
//<![CDATA[
'.$inscode.'
//]]>
</script>', str_replace('_js', '', $place));
unset($vbseo_inscode[$place]);
}
}
function vbseo_urchin_out(&$preurl, $url, &$posturl, $handlername = '')
{
if(!$handlername)
$handlername = 'onclick';
if (VBSEO_ADD_ANALYTICS_CODE && VBSEO_ADD_ANALYTICS_CODE_EXT)
{
$plink = parse_url($url);
$linkout = '_gaq.push([\'_trackEvent\', \'Outgoing\', \''.addslashes($plink['host']).'\', \'' . addslashes($plink['path'] . ($plink['query'] ? '?' . $plink['query'] : '')) . '\']);';
$posturl2 = preg_replace('#(\sonclick=")(javascript\:)?#is', '\\1' . $linkout, $posturl);
if ($posturl != $posturl2)
$posturl = $posturl2;
else
{
$preurl2 = preg_replace('#(\sonclick=")(javascript\:)?#is', '\\1' . $linkout, $preurl);
if ($preurl != $preurl2)
$preurl = $preurl2;
else
$preurl = preg_replace('#(<a\s)#is', '\\1'.$handlername.'="' . $linkout . '" ', $preurl);
}
}
}
function vbseo_google_ad_section($str, $ignore = false)
{
if(VBSEO_GOOGLE_AD_SEC)
return "<!-- google_ad_section_start" . ($ignore?"(weight=ignore)":"") . " -->" . $str . "<!-- google_ad_section_end -->";
else
return $str;
}
function vbseo_hit_log($oldurl = '')
{
global $vboptions;
$old_err = error_reporting();
define('VBSEO_SMDIR', vBSEO_Storage::path('vbseo') . '/../vbseo_sitemap');
if (@include_once(VBSEO_SMDIR . '/vbseo_sitemap_config.php'))
{
if (!file_exists(VBSEO_DAT_FOLDER_BOT))
{
@mkdir(VBSEO_DAT_FOLDER_BOT);
@chmod(VBSEO_DAT_FOLDER_BOT, 0777);
}
if (file_exists(VBSEO_DAT_FOLDER_BOT))
{
preg_match('#(' . VBSEO_ROBOTS_LIST . ')#i', $_SERVER['HTTP_USER_AGENT'], $botm);
$hfilename = VBSEO_DAT_FOLDER_BOT . @date('Ymd') . '.log';
if (file_exists($hfilename))
{
$botdat = array();
for($i = 0; $i < 5 && !$botdat; $i++)
$botdat = @unserialize(implode('', file($hfilename)));
if (!$botdat) return;
}
else
$botdat = array();
if ($oldurl)
{
if(preg_match('#forumdisplay|showthread|member#', $bscript))
$bscript = '[old url - '.$bscript.']';
else return;
}else
{
$bscript = substr($_SERVER['SCRIPT_NAME'], strstr(VBSEO_BASE, VBSEO_TOPREL) ? min(strlen(VBSEO_BASE), strlen(VBSEO_TOPREL)) : strlen(VBSEO_BASE));
if (preg_match('#^(archive/index\.' . VBSEO_VB_EXT . ')#', $bscript, $bscriptm))
$bscript = $bscriptm[1];
if (!$vboptions['bbactive'])
$bscript = '[forums-inactive]';
}
if(preg_match('#[<>\/\?]#',$bscript))$bscript='other';
if (!$bscript)$bscript = 'home';
$botdat[$botm[1]]['total'] ++;
$botdat[$botm[1]][$bscript] ++;
$botdat['all']['total'] ++;
$botdat['all'][$bscript] ++;
$wr = serialize($botdat);
$pf = @fopen($hfilename, 'w');
@fwrite($pf, $wr);
@fclose($pf);
@chmod($hfilename, 0666);
}
}
error_reporting($old_err);
}
function vbseo_prepare_cat_anchors()
{
global $vbulletin, $vbseo_gcache;
if (VBSEO_CATEGORY_ANCHOR_LINKS)
{   
$cutbburl = preg_replace('#/$#', '', $vbulletin->options['bburl']);
$cutbburl = vbseo_http_s_url($cutbburl);
if (isset($vbulletin->forumcache))
foreach($vbulletin->forumcache as $fk => $fv)
{
if (!($fv['options'] &$vbulletin->bf_misc_forumoptions['cancontainthreads']))
{
if (!$vbulletin->forumcache[$fk]['link'])
{
$nametitle = vbseo_filter_text($fv['title']);
$nametitle = preg_replace('#^(\d)#', 'a-$1', $nametitle);
$lnk = $cutbburl . '/' . (($vbulletin->options['forumhome'] == 'index' && VBSEO_HP_FORCEINDEXROOT) ? '' : $vbulletin->options['forumhome'] . '.' . VBSEO_VB_EXT) . '#' . $nametitle;
if($newurl = vbseo_apply_crr($lnk, $nofollow))
$lnk = $newurl;
$vbulletin->forumcache[$fk]['link'] = $lnk;
$vbulletin->forumcache[$fk]['nametitle'] = urldecode($nametitle);
if ($vbseo_gcache['forum'])
$vbseo_gcache['forum'][$fk] = $vbulletin->forumcache[$fk];
}
}
}
}
}
function vbseo_prepare_arc_links()
{
global $vboptions, $vbseo_gcache, $vbulletin;
vbseo_cache_start();
$links_str = $GLOBALS['vbseo_cache']->cacheget('vbseo_prepare_arc_links');
if (!$links_str)
{
$perpage = $vboptions['archive_threadsperpage'];
if (!defined('CANVIEW') && isset($vbulletin))
{
define('CANVIEW', $vbulletin->bf_ugp_forumpermissions['canview']);
}
$linksno = 1;
$opt = isset($vboptions['vbseo_opt']) ? $vboptions['vbseo_opt'] : array();
foreach($vbseo_gcache['forum'] as $forumid => $finfo)
{
$fperm = vbseo_vb_userinfo('forumpermissions');
$forumperms = $fperm["$forumid"];
if (!($forumperms &CANVIEW) || $finfo['link'])
continue;
if ((VBSEO_ARCHIVE_LINKS_FOOTER == 2) || (VBSEO_ARCHIVE_LINKS_FOOTER == 4))
{
$totalthreads = $opt['forumthreads'][$forumid] ? $opt['forumthreads'][$forumid] : $finfo['threadcount'];
$totalpages = max(ceil($totalthreads / $perpage), 1);
}
else
$totalpages = 1;
for($p = 1; $p <= $totalpages; $p++)
$links_str .= '<a href="' . $vboptions['bburl2'] . VBSEO_ARCHIVE_ROOT . 'f-' . $forumid . ($p > 1?'-p-' . $p:'') . '.html"' .
'>' . ($linksno++) . '</a> ';
}
$GLOBALS['vbseo_cache']->cacheset('vbseo_prepare_arc_links', $links_str);
}
return $links_str;
}
function vbseo_get_bookmarks()
{
global $vbseo_bmlist, $vbphrase;
if (!$vbseo_bmlist)
{
$vbseo_bmlist = array();
$bmarkurlist = '';
if (VBSEO_BOOKMARK_CUSTOM)
{
$abmlist = explode('|', VBSEO_BOOKMARK_SERVICES);
foreach($abmlist as $bm)
if ($bm && $bm[0] != '/')
{
$vbseo_bmlist[] = explode(',', $bm);
$bmarkurlist .= $bm;
}
}
if (VBSEO_BOOKMARK_FURL && !strstr($bmarkurlist, 'twitter.com'))
array_unshift($vbseo_bmlist, array('http://twitter.com/home?status=%title% %url%', vBSEO_Storage::path('fimages').'twitter.png', $vbphrase['vbseo_add_to_twitter'], $vbphrase['vbseo_add_to_twitter_post'], $vbphrase['vbseo_add_to_twitter_blog']));
if (VBSEO_BOOKMARK_TECHNORATI && !strstr($bmarkurlist, 'technorati.com'))
array_unshift($vbseo_bmlist, array('http://technorati.com/faves/?add=%url%', vBSEO_Storage::path('fimages').'technorati.gif', $vbphrase['vbseo_add_to_technorati'], $vbphrase['vbseo_add_to_technorati_post'], $vbphrase['vbseo_add_to_technorati_blog']));
if (VBSEO_BOOKMARK_DELICIOUS && !strstr($bmarkurlist, 'del.icio.us'))
array_unshift($vbseo_bmlist, array('http://del.icio.us/post?url=%url%&amp;title=%title%', vBSEO_Storage::path('fimages').'delicious.gif', $vbphrase['vbseo_add_to_delicious'], $vbphrase['vbseo_add_to_delicious_post'], $vbphrase['vbseo_add_to_delicious_blog']));
if (VBSEO_BOOKMARK_DIGG && !strstr($bmarkurlist, 'digg.com'))
array_unshift($vbseo_bmlist, array('http://digg.com/submit?phase=2&amp;url=%url%&amp;title=%title%', vBSEO_Storage::path('fimages').'digg.gif', $vbphrase['vbseo_add_to_digg'], $vbphrase['vbseo_add_to_digg_post'], $vbphrase['vbseo_add_to_digg_blog']));
}
return $vbseo_bmlist;
}
?>