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

if(!defined('VBSEO_INCLUDED'))
{
include_once dirname(__FILE__) . '/functions_vbseo_pre.php';
include_once VBSEO_DIRNAME . '/functions_vbseo_url.php';
include_once VBSEO_DIRNAME . '/functions_vbseo_createurl.php';
include_once VBSEO_DIRNAME . '/functions_vbseo_db.php';
include_once VBSEO_DIRNAME . '/functions_vbseo_vb.php';
include_once VBSEO_DIRNAME . '/functions_vbseo_seo.php';
include_once VBSEO_DIRNAME . '/functions_vbseo_misc.php';
include_once VBSEO_DIRNAME . '/functions_vbseo_crr.php';
include_once VBSEO_DIRNAME . '/functions_vbseo_cache.php';
include_once VBSEO_DIRNAME . '/functions_vbseo_hook.php';
include_once VBSEO_DIRNAME . '/functions_vbseo_startup.php';
function vbseo_replace_urls($preurl, $url, $mid_attribs = '', $posturl = '', $intag = '', $closetag = '')
{
global $vboptions, $vbseo_notop_url, $g_replace_cache, $vbseo_gcache, $vbseo_crules,
$tempusagecache, $vbulletin, $bbuserinfo;
$preurl  = str_replace('\"', '"', $preurl);
$posturl = str_replace('\"', '"', $posturl);
$intag = str_replace('\"', '"', $intag);
$closetag = str_replace('\"', '"', $closetag);
$par_str = '';
if($preurl[strlen($preurl)-1] != $posturl[0] && $posturl && !$GLOBALS['VBSEO_REWRITE_TEXTURLS'] 
&& !$GLOBALS['VBSEO_REWRITE_PRINTTHREAD']
&& !$GLOBALS['vbseo_proc_xml'])
{   
return $preurl . $url . $posturl . $intag . $closetag;
}
if(isset($g_replace_cache[$url]))
{
return ((strstr($preurl,':/')&&strstr($g_replace_cache[$url],':/')) ? '' : $preurl) . $g_replace_cache[$url] . $posturl . $intag . $closetag;
}
if($url[0]=='#')
{
return
$preurl .
( (VBSEO_BASEDEPTH && defined('VBSEO_PREPROCESSED')) ? htmlspecialchars(VBSEO_REQURL_FULL) : '' ).
$url . $posturl . $intag . $closetag
;
}
if(VBSEO_REWRITE_THREADS_ADDTITLE && $url[0]=='!')
{
preg_match('#^\!([mp])?(\d+)#', $url, $um);
$url = preg_replace('#^\![mp]?\d+\!#', '', $url);
$tid = ($um[1]=='p') ? $vbseo_gcache['post'][$um[2]]['threadid'] : $um[2];
$tinfo  = $vbseo_gcache['thread'][$tid];
$ttitle = $tinfo['title'];
$is_public = vbseo_forum_is_public($vbseo_gcache['forum'][$tinfo['forumid']]);
if( ($um[1]!='p') && !strstr($url, 'showthread.'.VBSEO_VB_EXT))
{
if($um[1]=='m')
{
$rurl = vbseo_thread_url($tid, '#m#', VBSEO_URL_THREAD_PAGENUM);
if(!preg_match('#'.str_replace('\\#m\\#', '\d+', preg_quote($rurl,'#')).'#',$url))
$ttitle = '';
}else
{
$rurl = vbseo_thread_url($tid);
if(!$rurl || !strstr($url,$rurl))
$ttitle = '';
}
}
if($ttitle && $intag!=$ttitle && ($is_public || ($tinfo['forumid']==$GLOBALS['forumid'])))
{
if(VBSEO_REWRITE_THREADS_ADDTITLE == 1)
$preurl = preg_replace('#(<a\s)#is', '\\1title="'.htmlspecialchars($ttitle).'" ', $preurl);
else
if(VBSEO_REWRITE_THREADS_ADDTITLE == 2)
$intag = $intag . " ($ttitle)";
else
if(VBSEO_REWRITE_THREADS_ADDTITLE == 3 && preg_match('#^http:#', $intag))
$intag = $ttitle;
}
}
$rn_q = $preurl[strlen($preurl)-1];
if($rn_q != "'") $rn_q = '"';
$relnofollow = 'rel='.$rn_q.'nofollow'.$rn_q;
if(strstr($preurl,'rel="novbseo"')||strstr($preurl,'rel=\'novbseo\''))
return preg_replace('#rel=[\'"]novbseo[\'"]#', '', $preurl) . $url . $posturl . $intag . $closetag;
$cproto = 0;
if(substr($url,0,7)=='mailto:' ||
substr($url,0,11)=='javascript:' ||
(($cproto=1) && strstr($url,'://')&&!strstr($url,VBSEO_HTTP_HOST)&&!strstr($url,$vboptions['bburl2'])))
{
preg_match('#(?:www\.)?(.+)$#', VBSEO_HTTP_HOST, $hmatch);
$vbseo_ext_url = !preg_match('#^[^/]*://(www\.)?'.preg_quote($hmatch[1],'#').'#', $url);
if($cproto && $vbseo_ext_url)
{
if( (VBSEO_NOFOLLOW_EXTERNAL
&& (!VBSEO_DOMAINS_WHITELIST || !preg_match('#'.VBSEO_DOMAINS_WHITELIST.'#i', $url))
)
||
(!VBSEO_NOFOLLOW_EXTERNAL
&& (VBSEO_DOMAINS_BLACKLIST && preg_match('#'.VBSEO_DOMAINS_BLACKLIST.'#i', $url))
)
)
{
if(!strstr($preurl.$mid_attribs.$posturl, $relnofollow))
$preurl = preg_replace('#(<a\s)#is', '\\1'.$relnofollow.' ', $preurl); //
}
vbseo_urchin_out($preurl, $url, $posturl, (substr($intag, 0, 5) == 'Visit' ? 'onmouseup' : ''));
}
if(VBSEO_REDIRECT_PRIV_EXTERNAL && (strstr($url,'http://')||strstr($url,'https://'))
&& in_array(THIS_SCRIPT,array('showthread','printthread','showpost','forumdisplay','newreply'))
&& strstr($preurl,'<a')
)
{
$is_public = vbseo_forum_is_public($GLOBALS['forum'], $GLOBALS['foruminfo']);
if(strstr($preurl,'href') && !$is_public && $vbseo_ext_url)
$url = $vboptions['bburl2'] . '/' . VBSEO_REDIRECT_URI . '?redirect=' . urlencode(vbseo_unhtmlentities($url));
}
return $preurl . $url . $posturl . $intag . $closetag;
}
$url = preg_replace('#([^:]/)/+#', '$1', $url);
$url_place = $url_append = '';
if(strpos($url, '?') !== false)
list($url_script, $url_append) = explode('?', $url, 2);
else
$url_script = $url;
if($url_append && $url_append[0]=='?') $url_append = substr($url_append,1);
if(THIS_SCRIPT == 'archive')
{
if($url_script == '../index.php/')
$url_script = $vboptions['relbburl'].VBSEO_ARCHIVE_ROOT;
}
$url_script = preg_replace('#^('.$vboptions['bburl2'].'/?)?archive/(index\.'.VBSEO_VB_EXT.'/?)?#', '${1}'.substr(VBSEO_ARCHIVE_ROOT, 1), $url_script);
if(strpos($url_script, '#') !== false)
list($url_script, $url_place) = explode('#', $url_script, 2);
else
if(strpos($url_append, '#') !== false)
list($url_parameters, $url_place) = explode('#', $url_append, 2);
else
$url_parameters = $url_append;
preg_match('#^(.*?)([^/]*)$#', $url_script, $um);
$base_script = $um[2];
$dir_script = $um[1];
$is_vbdir = (!$dir_script && (!VBSEO_BASEDEPTH ||         
defined('VBSEO_AJAX') ||
defined('VBSEO_BASEHREF_INDIR') || (defined('VBSEO_PREPROCESSED'))))
|| (strcasecmp ($dir_script, VBSEO_TOPREL)==0)
|| (strcasecmp ($dir_script, VBSEO_TOPREL_FULL)==0)
|| (strcasecmp ($dir_script, $vboptions['bburl2'].'/')==0)
|| (strcasecmp (str_replace('www.', '', $dir_script), VBSEO_TOPREL_FULL) == 0)
;
$topurl = $is_vbdir ? $vboptions['relbburl'] . '/' : $vboptions['bburl2'] . '/';
$is_vburl = strstr($url_script, $vboptions['bburl2']);
$pars = explode('&', str_replace('&amp;', '&', $url_parameters));     
$apars = $spars = array();
for($i2=0; $i2<count($pars); $i2++)
{
$v = '';
if(strpos($pars[$i2], '=') !== false)
list($k, $v) = explode('=', $pars[$i2], 2);
else
$k = $pars[$i2];
if($k)
{     
$dec_v = urldecode($v);
if(strstr($dec_v,'http:') && (substr($dec_v,0,strlen($vboptions['bburl2']))==$vboptions['bburl2']))
{
$dec_v = vbseo_replace_urls('', $dec_v);
$v = urlencode($dec_v);
}
$apars[$k] = $v;
$spars[] = array($k, $v);
}
}   
if(THIS_SCRIPT == 'online' && strstr($posturl,'alt'))
{
$posturl = preg_replace ('#(alt=")([^"]*)#se', "vbseo_replace_urls('$1', '$2')", $posturl );
}
if(VBSEO_IMAGES_DIM && strstr($preurl, '<img'))
{
$dexp = explode('/', $dir_script);
$base_script2 = count($dexp)>1 ? $dexp[count($dexp)-2] . '/' . $base_script : '';
$base_script3 = count($dexp)>2 ? $dexp[count($dexp)-3] . '/' . $base_script2 : '';
$base_script4 = count($dexp)>3 ? $dexp[count($dexp)-4] . '/' . $base_script3 : '';       
if(( ($imd = $GLOBALS['vbseo_images_dim'][$base_script])
|| ($imd = $GLOBALS['vbseo_images_dim'][$base_script2])
|| ($imd = $GLOBALS['vbseo_images_dim'][$base_script3])
|| ($imd = $GLOBALS['vbseo_images_dim'][$base_script4])
)
&& ($iw = $imd[0]) && ($ih = $imd[1])
&& !strstr($posturl, 'width=')
)
{
$preurl = preg_replace('#(<img\s)#is', '\\1width="'.$iw.'" height="'.$ih.'" ', $preurl);       
return $preurl.$url.$posturl . $intag . $closetag;
}
}
global $session, $vbulletin, $VBSEO_REWRITE_TEXTURLS;
if(!isset($session) && isset($vbulletin->session))
$session = $vbulletin->session->vars;
$vbseo_session_append = '';
if(isset($apars['s']))
{
$strip_sids = (isset($session) && in_array($apars['s'], $session) &&   
(!defined('VBSEO_STRIP_SIDS') || VBSEO_STRIP_SIDS)) || isset($VBSEO_REWRITE_TEXTURLS);
if(VBSEO_STRIPSID_GUESTS && !$bbuserinfo['userid'])
$strip_sids = true;
if(!$strip_sids)
$vbseo_session_append = 's='.$apars['s'];
unset($apars['s']);
$url_parameters = preg_replace('#^s=[\da-z]+(&amp;|&)*#', '', $url_parameters);
$url_parameters = preg_replace('#(&amp;|&)s=[\da-z]+#', '', $url_parameters);
if((count($spars)==1) && ($spars[0][0]=='s'))$spars=array();
}     
$clear_all_par = false;
if(THIS_SCRIPT == 'archive')
{
if(VBSEO_REWRITE_ARCHIVE_URLS && !$_COOKIE[vbseo_vb_cprefix() . 'pda'])
{
if(preg_match('#\bt-(\d+)\.html#',$base_script,$tmatch) ||
preg_match('#\bt-(\d+)\.html#',$url_parameters,$tmatch)
)
{
if(VBSEO_REWRITE_THREADS)      
$newurl = vbseo_thread_url($tmatch[1],1);
if(!$newurl)
$newurl = 'showthread.'.VBSEO_VB_EXT.'?'.VBSEO_THREADID_URI.'='.$tmatch[1];
$url_script = $topurl .$newurl;
$clear_all_par = true;
}
if(!VBSEO_VB35X && preg_match('#^f-(\d+)-p-1\.html$#',$base_script,$tmatch))
{
$url_script = str_replace('-p-1.html','.html',$url_script);
$clear_all_par = true;         
}
}
}
if(!$clear_all_par && defined('VBSEO_ARCHIVE_ROOT') && VBSEO_ARCHIVE_ROOT)
if(preg_match('#index.'.VBSEO_VB_EXT.'\?((t|f)-(\d+)(-p-\d+)?\.html)#', $url, $tmatch))
{
$url_script = $vboptions['relbburl'] . VBSEO_ARCHIVE_ROOT . $tmatch[1];
$clear_all_par = true;
}
if(!$clear_all_par)
if(count($apars)==1 && preg_match('#^[ft]-#',$url_parameters))
{
$par_str = $url_append;
$topurl = '';
$clear_all_par = true;
}
$nofollow = $follow = $noproc = false;
if(isset($apars['threadid']))
$apars[VBSEO_THREADID_URI] = $apars['threadid'];
if(!$clear_all_par && $is_vbdir)
{
if($base_script=='index.'.VBSEO_VB_EXT && !$url_parameters && VBSEO_HP_FORCEINDEXROOT)
$url_script = ((isset($VBSEO_REWRITE_TEXTURLS)||(THIS_SCRIPT=='sendmessage2'))?'':$topurl) . VBSEO_HOMEPAGE;
else
switch($base_script)
{
case 'forumdisplay.'.VBSEO_VB_EXT:
if(VBSEO_REWRITE_FORUM)
{
if( ($fid = $apars[VBSEO_FORUMID_URI]) ||
($fid = $apars[VBSEO_FORUMID_URI2]) )
$newurl = vbseo_forum_url($fid, isset($apars[VBSEO_PAGENUM_URI])?$apars[VBSEO_PAGENUM_URI]:0);
else
$noproc = true;
$def_so = $vbseo_gcache['forum'][$fid]['defaultsortorder'] ? $vbseo_gcache['forum'][$fid]['defaultsortorder'] : VBSEO_DEFAULT_FORUMDISPLAY_ORDER;
$def_sf = $vbseo_gcache['forum'][$fid]['defaultsortfield'] ? $vbseo_gcache['forum'][$fid]['defaultsortfield'] : VBSEO_DEFAULT_FORUMDISPLAY_SORT;
if( (!isset($apars[VBSEO_SORT_URI]) || $apars[VBSEO_SORT_URI] == $def_sf) &&
(!isset($apars[VBSEO_SORTORDER_URI]) || $apars[VBSEO_SORTORDER_URI] == $def_so)
&& (!isset($apars[VBSEO_ACTION_URI])))
{
unset($apars[VBSEO_SORTORDER_URI]);
unset($apars[VBSEO_ACTION_URI]);
unset($apars[VBSEO_SORT_URI]);
}
if($vbseo_gcache['forum'][$fid]['link'])
{
preg_match('#(([^\.]+\.)?[^\.]+)$#', VBSEO_HTTP_HOST, $hmatch);
if(!preg_match('#^[^/]*://[^/]*'.preg_quote($hmatch[1],'#').'#', $url_script))
vbseo_urchin_out($preurl, $url_script, $posturl);
$noproc = true;
$url_parameters = '';
}
if($newurl)
{
$url_script = $newurl;
if( isset($apars['daysprune'])
&& $GLOBALS['forumcache'][$fid]['daysprune'] == $apars['daysprune']
&& $bbuserinfo['daysprune'] == $apars['daysprune']
)
unset($apars['daysprune']);
unset($apars['pp']);
unset($apars[VBSEO_FORUMID_URI]);
unset($apars[VBSEO_FORUMID_URI2]);
unset($apars[VBSEO_PAGENUM_URI]);
}
}
break;
case 'announcement.'.VBSEO_VB_EXT:
if(VBSEO_REWRITE_ANNOUNCEMENT)
{
if( ($fid = $apars[VBSEO_FORUMID_URI]) ||
($fid = $apars[VBSEO_FORUMID_URI2]) )
{
if($newurl = vbseo_announcement_url($fid, isset($apars['a'])?$apars['a']:$apars['announcementid']))
{
$url_script = $newurl;
$clear_all_par = true; 
}
}
}
break;
case 'showthread.'.VBSEO_VB_EXT:
if(VBSEO_REWRITE_THREADS)
{
$tinfo = $vbseo_gcache['thread'][$apars[VBSEO_THREADID_URI]];
if($tinfo['forumid'])
{
$is_public = vbseo_forum_is_public($vbseo_gcache['forum'][$tinfo['forumid']],'',true);
if(!$is_public)
if(! (($apars['goto'] == 'newpost') && $GLOBALS['VBSEO_REWRITE_TEXTURLS']) )
break;
}
if((isset($apars['goto']) && $apars['goto'] == 'newpost') &&
( (defined('VBSEO_REWRITE_EXTERNAL') || !$bbuserinfo['userid'])
&& VBSEO_DIRECTLINKS_THREADS
)
)
{
$posturl = preg_replace('#(title=)".*?"#i','', $posturl);
unset($apars['goto']);
}
$newurl = '';
if((isset($apars[VBSEO_POSTID_URI]) && $r_post_id = $apars[VBSEO_POSTID_URI])
||
(isset($apars['postid']) && $r_post_id = $apars['postid']))
{
if($apars['do']||$apars[VBSEO_THREADID_URI])break;
if($newurl = vbseo_thread_url_postid($r_post_id, isset($apars[VBSEO_PAGENUM_URI])?$apars[VBSEO_PAGENUM_URI]:1,vbseo_is_threadedmode()))
{
$tinfo = $vbseo_gcache['thread'][$vbseo_gcache['post'][$r_post_id]['threadid']];
if($tinfo['forumid'])
{
$is_public = vbseo_forum_is_public($vbseo_gcache['forum'][$tinfo['forumid']],'',true);
if(!$is_public)$newurl='';
}
if($url_place)
$newurl = preg_replace('|#.*|', '', $newurl);
}
}
else
if(isset($apars['goto']))
{
if($apars['goto'] == 'newpost')
$newurl = vbseo_thread_url($apars[VBSEO_THREADID_URI], '', VBSEO_URL_THREAD_NEWPOST);
else
if($apars['goto'] == 'lastpost')
$newurl = vbseo_thread_url($apars[VBSEO_THREADID_URI], '', VBSEO_URL_THREAD_LASTPOST);
else
if($apars['goto'] == 'nextnewest')
{
if(VBSEO_URL_THREAD_NEXT_DIRECT)
{
$nthread = vbseo_get_next_thread($apars[VBSEO_THREADID_URI], false);
$follow = true;
if($nthread['threadid'])
{
$url_script = vbseo_thread_url($nthread['threadid']);
$intag = function_exists('fetch_censored_text') ? fetch_censored_text($nthread['title']) : '';
$clear_all_par = true;
}else
return '-';
}else
$newurl = vbseo_thread_url($apars[VBSEO_THREADID_URI], '', VBSEO_URL_THREAD_NEXT);
}
else
if($apars['goto'] == 'nextoldest')
{
if(VBSEO_URL_THREAD_PREV_DIRECT)
{
$follow = true;
$nthread = vbseo_get_next_thread($apars[VBSEO_THREADID_URI], true);
if($nthread['threadid'])
{
$url_script = vbseo_thread_url($nthread['threadid']);
$intag = function_exists('fetch_censored_text') ? fetch_censored_text($nthread['title']) : '';
$clear_all_par = true;
}else
return '-';
}else
$newurl = vbseo_thread_url($apars[VBSEO_THREADID_URI], '', VBSEO_URL_THREAD_PREV);
}
}
else
if(VBSEO_ENABLE_GARS && isset($apars[VBSEO_PAGENUM_URI_GARS]))
$newurl = vbseo_thread_url($apars[VBSEO_THREADID_URI], $apars[VBSEO_PAGENUM_URI_GARS], VBSEO_URL_THREAD_GARS_PAGENUM);
else
if(!isset($apars['goto']))
$newurl = vbseo_thread_url($apars[VBSEO_THREADID_URI], isset($apars[VBSEO_PAGENUM_URI])?$apars[VBSEO_PAGENUM_URI]:0);
if($newurl)
{
$url_script = $newurl;
unset($apars[VBSEO_POSTID_URI]);
unset($apars['postid']);
unset($apars[VBSEO_THREADID_URI]);
unset($apars[VBSEO_PAGENUM_URI]);
if(VBSEO_ENABLE_GARS) unset($apars[VBSEO_PAGENUM_URI_GARS]);
unset($apars['threadid']);
unset($apars['pp']);
unset($apars['goto']);
}
}
break;
case 'printthread.'.VBSEO_VB_EXT:
if(VBSEO_REWRITE_PRINTTHREAD){
$newurl = vbseo_thread_url($apars[VBSEO_THREADID_URI],
$apars[VBSEO_PAGENUM_URI],
($apars[VBSEO_PAGENUM_URI]+0>1) ? VBSEO_URL_THREAD_PRINT_PAGENUM : VBSEO_URL_THREAD_PRINT);
if($newurl)
{
$url_script = $newurl;
unset($apars[VBSEO_THREADID_URI]);
unset($apars[VBSEO_PAGENUM_URI]);
if($apars['pp'] == $vboptions['maxposts'])
unset($apars['pp']);
if(VBSEO_NOFOLLOW_PRINTTHREAD)
$nofollow = true; 
}
}
break;
case 'showpost.'.VBSEO_VB_EXT:
$url_script2 = '';
if(
(VBSEO_POSTBIT_PINGBACK==2) && $apars['postcount'])
{
global $vbphrase;
$url_script2 = vbseo_thread_url_postid($apars[VBSEO_POSTID_URI], 1, false, $apars['postcount']);
$posturl = str_replace('>', ' title="'.$vbphrase['vbseo_permalink'].'">', $posturl);
if($url_script2)
{
$posturl = str_replace('target="new"', '', $posturl);
$clear_all_par = true;
}
}
if(VBSEO_REWRITE_SHOWPOST && $apars[VBSEO_POSTID_URI])
{
if( !$url_script2 )
$url_script2 = vbseo_post_url($apars[VBSEO_POSTID_URI], $apars['postcount']);
$clear_all_par = true;
}
if($url_script2)
$url_script = $url_script2;
else
$clear_all_par = false;
if(VBSEO_NOFOLLOW_SHOWPOST==2)
{
global $threadinfo;
if( ($threadinfo['replycount'] == $apars['postcount']-1)
&& ($apars['postcount']%$vboptions['maxposts'] == 1))
$nofollow = true;
else
$follow = true;
}else
if(VBSEO_NOFOLLOW_SHOWPOST==1)
$nofollow = true;
else
if(VBSEO_NOFOLLOW_SHOWPOST==0)
$follow = true;
break;
case 'poll.'.VBSEO_VB_EXT:
if(VBSEO_REWRITE_POLLS){
if($apars[VBSEO_ACTION_URI]=='showresults')
{
if($newurl = vbseo_poll_url($apars[VBSEO_POLLID_URI]))
{
$url_script = $newurl;
$clear_all_par = true;
}
}
}
break;
case 'album.'.VBSEO_VB_EXT:
if(VBSEO_REWRITE_MALBUMS && !isset($apars['do']))
{
if(isset($apars['pictureid']) )
{
if(isset($apars['commentid']))
{
$apars['page'] = vbseo_pic_pagenum($apars['pictureid'], $apars['commentid']);
$url_place = 'picturecomment' . $apars['commentid'];
unset($apars['commentid']);
}
$newurl = vbseo_album_url(
$apars['page']>1?'VBSEO_URL_MEMBER_PICTURE_PAGE':'VBSEO_URL_MEMBER_PICTURE', 
$apars);
}
else
if(count($apars)==0)
{
$newurl = vbseo_any_url(VBSEO_URL_MEMBER_ALBUM_HOME);
}
else
if(isset($apars['albumid']) && count($apars)==1)
{
$newurl = vbseo_album_url('VBSEO_URL_MEMBER_ALBUM', $apars);
}
else
if(isset($apars['albumid']) && count($apars)==2 && $apars['page'])
{
$newurl = vbseo_album_url('VBSEO_URL_MEMBER_ALBUM_PAGE', $apars);
}
else
if(isset($apars['u']) && count($apars)==1)
{
$newurl = vbseo_album_url('VBSEO_URL_MEMBER_ALBUMS', $apars);
}
else
if(isset($apars['u']) && count($apars)==2 && $apars['page'])
{
$newurl = vbseo_album_url('VBSEO_URL_MEMBER_ALBUMS_PAGE', $apars);
}
if($newurl)
{
$url_script = $newurl;
unset($apars['pictureid']);
unset($apars['albumid']);
unset($apars['u']);
unset($apars['page']);
}
}
break;
case 'picture.'.VBSEO_VB_EXT:
if(!isset($apars['do']) )
{
if(VBSEO_REWRITE_MALBUMS && isset($apars['albumid']) && isset($apars['pictureid']))
{
$newurl = vbseo_album_url('VBSEO_URL_MEMBER_PICTURE_IMG', $apars);
}else
if(VBSEO_REWRITE_GROUPS && isset($apars['groupid']) && isset($apars['pictureid']))
{
$newurl = vbseo_group_url(VBSEO_URL_GROUPS_PICTURE_IMG, $apars);
}
if($newurl)
{
$url_script = $newurl;
$clear_all_par = true;
}
}
break;
case 'member.'.VBSEO_VB_EXT:
if(VBSEO_REWRITE_MEMBERS)
{
if(!isset($apars['u']) && isset($apars['userid']))
$apars['u'] = $apars['userid'];
if(isset($apars['find']) && $apars['find']=='lastposter')
{
if($apars[VBSEO_FORUMID_URI])
$url_script = vbseo_member_url(0,$vbseo_gcache['forum'][$apars[VBSEO_FORUMID_URI]]['lastposter']?$vbseo_gcache['forum'][$apars[VBSEO_FORUMID_URI]]['lastposter']:$intag);
else
$url_script = vbseo_member_url(0,$vbseo_gcache['thread'][$apars[VBSEO_THREADID_URI]]['lastposter']?$vbseo_gcache['thread'][$apars[VBSEO_THREADID_URI]]['lastposter']:$intag);
$clear_all_par = true;
}else
if(isset($apars['username']))
{
$url_script = vbseo_member_url(0, $apars['username']);
$clear_all_par = true;
}else
if($apars['tab'] == 'visitor_messaging' && $apars['page']>1)
{
$url_place = '';
$url_script = vbseo_member_url($apars['u'], '', 'VBSEO_URL_MEMBER_MSGPAGE', 
array('%page%'=>$apars['page']));
$clear_all_par = true;
}else
if($apars['tab'] == 'friends' && $apars['page']>1)
{
$url_script = vbseo_member_url($apars['u'], '', 'VBSEO_URL_MEMBER_FRIENDSPAGE', 
array('%page%'=>$apars['page']));
$clear_all_par = true;
}else
if(isset($apars['u']) && !isset($apars['do']) && !isset($apars['simple']) 
&& !isset($apars['dozoints']) && !isset($apars['sort'])
&& !isset($apars['showignored'])&& (!isset($apars['action'])||$apars['action']=='getinfo')
)
{
$url_script = vbseo_member_url($apars['u']);
if($apars['tab'])
{
$url_place = $apars['tab'];
}
if(isset($apars['nozoints']) || isset($apars['page']) || isset($apars['showall']) || isset($apars['explain']))
{
unset($apars['u']);
}else
{
$clear_all_par = true;
}
}
}
if($tempusagecache['SHOWTHREAD'])
{
if(VBSEO_NOFOLLOW_MEMBER_POSTBIT)
$nofollow = true;
else
$follow = true;
}else
if($tempusagecache['FORUMHOME'] || $tempusagecache['FORUMDISPLAY'])
{
if(VBSEO_NOFOLLOW_MEMBER_FORUMHOME)
$nofollow = true;
else
$follow = true;
}
break;
case 'converse.'.VBSEO_VB_EXT:
if(VBSEO_REWRITE_MEMBERS)
{
$url_script = vbseo_member_url($apars['u'], '', 
$apars['page']?'VBSEO_URL_MEMBER_CONVPAGE':'VBSEO_URL_MEMBER_CONV', 
array(),
$apars
);
if($url_script)
{
unset($apars['u']);
unset($apars['u2']);
unset($apars['page']);
}
}
break;
case 'image.'.VBSEO_VB_EXT:
if(VBSEO_REWRITE_AVATAR){
if(isset($apars[VBSEO_USERID_URI]) && (!isset($apars['type']) ||$apars['type']!='profile'))
{
$url_script = vbseo_member_url($apars[VBSEO_USERID_URI],'','VBSEO_URL_AVATAR');
unset($apars[VBSEO_USERID_URI]);
}
}
break;
case 'memberlist.'.VBSEO_VB_EXT:
if(VBSEO_REWRITE_MEMBER_LIST){
if(isset($apars['ltr']) ||
!isset($apars[VBSEO_ACTION_URI]) || $apars[VBSEO_ACTION_URI] == 'getall'
)
{
$url_script = vbseo_memberlist_url(isset($apars['ltr'])?$apars['ltr']:'',isset($apars[VBSEO_PAGENUM_URI])?$apars[VBSEO_PAGENUM_URI]:'');
$apars2 = $apars;
$unsetpar = array('ltr', 'pp', VBSEO_SORT_URI, VBSEO_SORTORDER_URI, VBSEO_ACTION_URI, VBSEO_PAGENUM_URI);
foreach($unsetpar as $i=>$up)
unset($apars2[$up]);
if( (!isset($apars[VBSEO_SORT_URI]) || $apars[VBSEO_SORT_URI] == VBSEO_DEFAULT_MEMBERLIST_SORT)
&& (!isset($apars[VBSEO_SORTORDER_URI]) || stristr($apars[VBSEO_SORTORDER_URI], VBSEO_DEFAULT_MEMBERLIST_ORDER))
&& (count($apars2)==0)
)
{
$clear_all_par = true;
}else
{
unset($apars['ltr']);
unset($apars[VBSEO_ACTION_URI]);
unset($apars[VBSEO_PAGENUM_URI]);
}
}
}
break;
case 'attachment.'.VBSEO_VB_EXT:
if($apars['attachmentid'])
{
if(VBSEO_REWRITE_ATTACHMENTS_ALT && $apars['thumb']
&& ($newalt = vbseo_attachment_url($apars['attachmentid'], 
str_replace('%thread_title%', '%thread_title_ue%', VBSEO_URL_ATTACHMENT_ALT))))
{
$posturl = preg_replace('#(alt=)"[^"]*#is','$1"'.str_replace('"','&quot;',$newalt), $posturl);
}
if(VBSEO_REWRITE_ATTACHMENTS &&
($newurl = vbseo_attachment_url($apars['attachmentid'], '', $apars['d'], $apars['thumb'])))
{
$url_script = $newurl;
unset($apars['attachmentid']);
unset($apars['stc']);
unset($apars['d']);
unset($apars['thumb']);
}
}
break;
case 'blog_attachment.'.VBSEO_VB_EXT:
if(VBSEO_REWRITE_BLOGS_ATT &&
$newurl = vbseo_blog_url(VBSEO_URL_BLOG_ATT, $apars) )
{
$url_script = $newurl;
unset($apars['attachmentid']);
unset($apars['stc']);
unset($apars['d']);
unset($apars['thumb']);
}
break;
case 'tags.'.VBSEO_VB_EXT:
if(VBSEO_REWRITE_TAGS)
{
if($apars['tag'])
{
$newurl = vbseo_tags_url($apars['page'] ? VBSEO_URL_TAGS_ENTRYPAGE : VBSEO_URL_TAGS_ENTRY, $apars);
}else
if(count($apars)==0)
$newurl = vbseo_tags_url(VBSEO_URL_TAGS_HOME);
if($newurl)
{
$url_script = $newurl;
unset($apars['tag']);
unset($apars['page']);
}
}
break;
case 'blog_external.'.VBSEO_VB_EXT:
if(VBSEO_REWRITE_BLOGS && VBSEO_REWRITE_BLOGS_FEED)
{
if($apars['bloguserid'])
{
$newurl = vbseo_blog_url(VBSEO_URL_BLOG_FEEDUSER, $apars);
}else
$newurl = vbseo_blog_url(VBSEO_URL_BLOG_FEED);
if($newurl)
{
$url_script = $newurl;
$clear_all_par = true;
}
}
break;
case 'blog_tag.'.VBSEO_VB_EXT:
if( VBSEO_REWRITE_BLOGS && VBSEO_REWRITE_BLOGS_TAGS_ENTRY)
{
if(count($apars)==0)
{
$url_script = vbseo_blog_url(VBSEO_URL_BLOG_TAGS_HOME, $apars);
$clear_all_par = true;
}
}
break;
case 'blog.'.VBSEO_VB_EXT:
if( VBSEO_REWRITE_BLOGS )
{
if(count($apars)==0)
{
$newurl = vbseo_blog_url(VBSEO_URL_BLOG_HOME, $apars);
}else
if($apars['tag'])
{     
if(VBSEO_REWRITE_BLOGS_TAGS_ENTRY)
$newurl = vbseo_blog_url($apars['page'] ? VBSEO_URL_BLOG_TAGS_ENTRY_PAGE : VBSEO_URL_BLOG_TAGS_ENTRY, $apars);
}else
if($apars['u'] || ($apars['userid'] && ($apars['u'] = $apars['userid'])))
{
unset($apars['userid']);
if(count($apars) == 1 || (count($apars) == 2 && $apars['blogtype']=='recent'))
$newurl = vbseo_blog_url(VBSEO_URL_BLOG_USER, $apars);
else
if($apars['page'] && (count($apars) == 2 || (count($apars) == 3 && $apars['blogtype']=='recent')))
$newurl = vbseo_blog_url(VBSEO_URL_BLOG_USER_PAGE, $apars);
else
if($apars[VBSEO_BLOG_CATID_URI])
{
if(VBSEO_REWRITE_BLOGS_CAT)
$newurl = vbseo_blog_url($apars['page'] ? VBSEO_URL_BLOG_CAT_PAGE : VBSEO_URL_BLOG_CAT, $apars);
}
else
if(VBSEO_REWRITE_BLOGS_LIST)
{
if($apars['d'] && !$apars['page'])
$newurl = vbseo_blog_url(VBSEO_URL_BLOG_UDAY, $apars);
else
if($apars['m'] && !$apars['page'])
$newurl = vbseo_blog_url(VBSEO_URL_BLOG_UMONTH, $apars);
}
}else
if($apars[VBSEO_BLOG_CATID_URI])
{
if(VBSEO_REWRITE_BLOGS_CAT)
$newurl = vbseo_blog_url($apars['page'] ? VBSEO_URL_BLOG_GLOB_CAT_PAGE : VBSEO_URL_BLOG_GLOB_CAT, $apars);
}else
if(($apars['b']||$apars['blogid']) && (count($apars) == 1 || $apars['goto']=='newpost'))
{
if(VBSEO_REWRITE_BLOGS_ENT)
$newurl = vbseo_blog_url(VBSEO_URL_BLOG_ENTRY, $apars);
}else
if($apars['cp'] && (count($apars) == 1))
{
if(VBSEO_REWRITE_BLOGS_CUSTOM)
{
$newurl = vbseo_blog_url(VBSEO_URL_BLOG_CUSTOM, $apars);
}
}else
if(($apars['b']||$apars['blogid']) && (count($apars) == 2) && $apars['page'])
{
if(VBSEO_REWRITE_BLOGS_ENT)
$newurl = vbseo_blog_url(VBSEO_URL_BLOG_ENTRY_PAGE, $apars);
}else
if($apars['b'] && $apars['goto'])
{
if(VBSEO_REWRITE_BLOGS_ENT)
$newurl = vbseo_blog_url($apars['goto']=='next'?VBSEO_URL_BLOG_NEXT:VBSEO_URL_BLOG_PREV, $apars);
}else
if($apars['bt'] && strstr($url, 'blog.') && (count($apars) == 1) )
{
if(VBSEO_REWRITE_BLOGS_ENT)
{
if($vbseo_gcache['blogcom'][$apars['bt']]['cpage'] && vbseo_vb_gpc('blogid'))
{
$newurl = vbseo_blog_url(
(vbseo_vb_gpc('pagenumber') > 1) ? VBSEO_URL_BLOG_ENTRY_PAGE : VBSEO_URL_BLOG_ENTRY, 
array('b' => vbseo_vb_gpc('blogid'), 'page' => vbseo_vb_gpc('pagenumber'))
);
$newurl .= '#comment'.$apars['bt'];
}else
$newurl = vbseo_blog_url(VBSEO_URL_BLOG_ENTRY_REDIR, $apars);
}
}else
if(VBSEO_REWRITE_BLOGS_LIST && $apars['do'] == 'bloglist')
{
if(!$apars['blogtype'])
{
$newurl = vbseo_blog_url(
$apars['page'] ? VBSEO_URL_BLOG_BLIST_PAGE : VBSEO_URL_BLOG_BLIST,
$apars
);
$noclear = true;
unset($apars['do']);
unset($apars['page']);
}
else
if($apars['blogtype']=='best')
$newurl = vbseo_blog_url(
$apars['page'] ? VBSEO_URL_BLOG_BEST_BLOGS_PAGE : VBSEO_URL_BLOG_BEST_BLOGS,
$apars);
}else
if(VBSEO_REWRITE_BLOGS_LIST && $apars['do'] == 'comments')
{
if(count($apars) == 1)
$newurl = vbseo_blog_url(VBSEO_URL_BLOG_CLIST);
else
if($apars['page'])
$newurl = vbseo_blog_url(VBSEO_URL_BLOG_CLIST_PAGE, $apars);
}else
if(VBSEO_REWRITE_BLOGS_LIST && $apars['do'] == 'list')
{
if((!$apars['blogtype']&&!$apars['y']&&!$apars['span']) || in_array($apars['blogtype'], array('recent','latest')))
$newurl = vbseo_blog_url($apars['page'] ? VBSEO_URL_BLOG_LIST_PAGE : VBSEO_URL_BLOG_LIST, $apars); //
else
if($apars['span']=='24')
$newurl = vbseo_blog_url(
$apars['page'] ? VBSEO_URL_BLOG_LAST_ENT_PAGE : VBSEO_URL_BLOG_LAST_ENT ,
$apars);
else
if($apars['blogtype']=='best')
$newurl = vbseo_blog_url(
$apars['page'] ? VBSEO_URL_BLOG_BEST_ENT_PAGE : VBSEO_URL_BLOG_BEST_ENT,
$apars);
else
if($apars['d'])
$newurl = vbseo_blog_url(
$apars['page'] ? VBSEO_URL_BLOG_DAY_PAGE : VBSEO_URL_BLOG_DAY,
$apars);
else
if($apars['m'])
$newurl = vbseo_blog_url(
$apars['page'] ? VBSEO_URL_BLOG_MONTH_PAGE : VBSEO_URL_BLOG_MONTH,
$apars);
}
}
if($newurl)
{
$url_script = $newurl;
if(!$noclear)
$clear_all_par = true;
}else
$noproc = true;
break;
case 'group.'.VBSEO_VB_EXT:
if( VBSEO_REWRITE_GROUPS )
{
if($apars['pp'] == $vboptions['vm_perpage'])
unset($apars['pp']);
if(isset($apars['page']) && $apars['page']<2)
unset($apars['page']);
$noclear = false;
if($apars['gmid'] && !isset($apars['do']))
{
$apars['page'] = vbseo_grp_pagenum($apars['groupid'], $apars['gmid']);
$url_place = 'gmessage'.$apars['gmid'];
unset($apars['gmid']);
}
if($apars['gmid'] && ($apars['do']=='discuss'))
{          
$apars['page'] = vbseo_gmsg_pagenum($apars['discussionid'], $apars['gmid']);
if($newurl = vbseo_group_url(vbseo_groupdis_urlf($apars['page'] > 1), $apars))
{
$url_place = 'gmessage'.$apars['gmid'];
unset($apars['gmid']);
unset($apars['do']);
unset($apars['page']);
}
}
if($apars['do'] == 'grouplist' && ($apars['sort'] == 'lastpost' || !$apars['sort']) && (!$apars['order'] || $apars['order']=='desc'))
{
unset($apars['sort']);
unset($apars['order']);
}
if(!$newurl)
{
if($apars['do'] == 'grouplist' && !$apars['cat'])
{
$newurl = vbseo_group_url($apars['page'] ? VBSEO_URL_GROUPS_ALL_PAGE : VBSEO_URL_GROUPS_ALL, $apars);
unset($apars['page']);
unset($apars['do']);
$noclear = true;
}else
if($apars['do'] == 'discuss' && !$apars['gmid'])
{
$newurl = vbseo_group_url(vbseo_groupdis_urlf($apars['page'] > 1), $apars);
unset($apars['page']);
unset($apars['do']);
unset($apars['group']);
unset($apars['discussionid']);
$noclear = true;
}else
if($apars['do'] == 'categorylist')
{
$newurl = vbseo_group_url(
$apars['page'] ? VBSEO_URL_GROUPS_CATEGORY_LIST_PAGE : VBSEO_URL_GROUPS_CATEGORY_LIST, 
$apars);
unset($apars['page']);
unset($apars['do']);
$noclear = true;
}else
if($apars['cat'] && ((count($apars)==1) || $apars['page'] || ($apars['do'] == 'grouplist')))
{
$newurl = vbseo_group_url($apars['page'] ? VBSEO_URL_GROUPS_CATEGORY_PAGE : VBSEO_URL_GROUPS_CATEGORY, $apars);
unset($apars['cat']);
unset($apars['do']);
unset($apars['page']);
if($apars['dofilter'] == 1)
unset($apars['dofilter']);
$noclear = true;
}else
if(count($apars)==0)
{
$newurl = vbseo_group_url(VBSEO_URL_GROUPS_HOME, $apars);
unset($apars['do']);
$noclear = true;
}else
if($apars['do'] == 'viewmembers')
{
$newurl = vbseo_group_url($apars['page'] > 1 ? 
VBSEO_URL_GROUPS_MEMBERS_PAGE : VBSEO_URL_GROUPS_MEMBERS, $apars);
}else
if($apars['do'] == 'grouppictures')
{
$newurl = vbseo_group_url($apars['page'] > 1 ? 
VBSEO_URL_GROUPS_PIC_PAGE : VBSEO_URL_GROUPS_PIC, $apars);
}else
if($apars['do'] == 'picture')
{
if(isset($apars['commentid']))
{
$apars['page'] = vbseo_pic_pagenum($apars['pictureid'], $apars['commentid']);
$url_place = 'picturecomment' . $apars['commentid'];
}
$newurl = vbseo_group_url($apars['page'] > 1 ? 
VBSEO_URL_GROUPS_PICTURE_PAGE : VBSEO_URL_GROUPS_PICTURE, $apars);
}else
if($apars['groupid'] && 
(!$apars['do'] || ($apars['do']=='view'))
)
{
$newurl = vbseo_group_url($apars['page']>1 ? VBSEO_URL_GROUPS_PAGE : VBSEO_URL_GROUPS, $apars);
$noclear = true;
unset($apars['do']);
unset($apars['page']);
unset($apars['groupid']);
}
}
if($newurl)
{
$url_script = $newurl;
if(!$noclear)
$clear_all_par = true;
}else
$noproc = true;
}
break;
default:
$noproc = true;
if(isset($apars['do']) && $apars['do'] == 'getdaily')
$follow = true;
break;
}
}
else
$noproc = true;
if($noproc)
{
if($newurl = vbseo_apply_crr(($is_vbdir ? $base_script : $url_script) . ($url_parameters ? '?' . $url_parameters : ''), $nofollow))
{
$url_script = $newurl;
$clear_all_par = true;
if($is_vbdir)
$noproc = false;
}
}
if( ($noproc && !$is_vbdir && (!VBSEO_INFORUMDIR||($url[0]=='/')||!(VBSEO_BASEDEPTH && defined('VBSEO_PREPROCESSED'))))
|| (isset($VBSEO_REWRITE_TEXTURLS) || (THIS_SCRIPT=='sendmessage'))
|| (!defined('VBSEO_PREPROCESSED') && !$is_vburl && !VBSEO_BASEDEPTH && THIS_SCRIPT!='index')
)
$topurl = '';
$amp_sign = isset($VBSEO_REWRITE_TEXTURLS) && !defined('VBSEO_REWRITE_EXTERNAL')?'&':'&amp;';
if(!$clear_all_par)
{
if(($url_parameters && $url_parameters[0]=='=')||!strstr($url_parameters,'='))
$par_str .= $url_parameters;
else
if((count($spars)==1) && (!strstr($url_parameters,'=')||($url_parameters[0]=='=') ))
$par_str .= $spars[0][0];
else
for($i=0;$i<count($spars);$i++)
{
$k = $spars[$i][0];
$v = $spars[$i][1];
if(isset($apars[$k]))
$par_str .= ($par_str?$amp_sign:'').$k.'='.$v;
}
}else
unset($apars);
if($vbseo_session_append )
$par_str .= ($par_str?$amp_sign :'') . $vbseo_session_append;
if( ($url_script[0] != '/') && (!strstr(substr($url_script,3,5), ':')))
$url_script = ($preurl ? $topurl : $vboptions['bburl2'].'/') .  $url_script;
$newurl = $url_script .
($par_str ? '?' . $par_str : '') .
($url_place ? '#' . $url_place : '');
if($follow)
{
$posturl = str_replace($relnofollow, '', $posturl);
$preurl  = str_replace($relnofollow, '', $preurl);
}else
if($nofollow || (isset($apars['sort']) && VBSEO_NOFOLLOW_SORT)
|| ($apars && VBSEO_NOFOLLOW_DYNA) )
{
if(!strstr($preurl.$mid_attribs.$posturl, ' rel='))
$preurl = preg_replace('#(<a\s)#is', '\\1'.$relnofollow.' ', $preurl);
}else
$g_replace_cache[$url] = $newurl;
if($GLOBALS['VBSEO_REWRITE_TEXTURLS'] && strstr($newurl, 'http://') && strstr($preurl, 'http://'))
$preurl = '';
if($url == $intag && $intag)
$intag = $newurl;
return $preurl . $newurl . $posturl . $intag . $closetag;
}
function vbseo_find_ids($newtext)
{
global $found_object_ids, $VBSEO_REWRITE_TEXTURLS, $vboptions;
$matchfull = '[\'"](?:'.$vboptions['bburl2'].'/?|'.$vboptions['cutbburl'].'/?)?';
$matchpre = (isset($VBSEO_REWRITE_TEXTURLS) || $GLOBALS['vbseo_proc_xml']) ? '' : $matchfull;
$matchpre2 = '';
if(!$VBSEO_REWRITE_TEXTURLS && !$GLOBALS['vbseo_proc_xml'])
{
preg_match_all('#(?:href=|src=|\.open\(|location=)["\'].*?["\']#is',$newtext,$tmatch,PREG_PATTERN_ORDER);
$newtext = implode(" ",$tmatch[0]);
}
if(VBSEO_REWRITE_ARCHIVE_URLS)
if(preg_match_all('#\bt-(\d+)\.html#',$newtext,$tmatch))
{
$found_object_ids['thread_ids'] = $tmatch[1];
}
$found_object_ids['pic'] = array();
if(VBSEO_REWRITE_MEMBERS)
{
if(preg_match_all('#member\.'.VBSEO_VB_EXT.'\?[^"\']*?u(?:serid)?=(\d+)#', $newtext, $matches))
$found_object_ids['user_ids'] = $matches[1];
if(preg_match_all('#'.$matchpre2.'member\.'.VBSEO_VB_EXT.'\?[^"]*?username=([^"\']+)#', $newtext, $matches))
$found_object_ids['user_names'] = $matches[1];
if(preg_match_all('#converse\.'.VBSEO_VB_EXT.'\?[^"\']*?u=(\d+)[^"\']*?u2=(\d+)#', $newtext, $matches))
$found_object_ids['user_ids'] = array_merge($found_object_ids['user_ids'], $matches[1], $matches[2]);
if(preg_match_all('#blog\.'.VBSEO_VB_EXT.'\?[^"\']*?u=(\d+)#', $newtext, $matches))
$found_object_ids['user_ids'] = array_merge($found_object_ids['user_ids'], $matches[1]);
if(preg_match_all('#'.$matchpre2.'member\.'.VBSEO_VB_EXT.'\?[^"]*?find=lastposter.*?t(?:hreadid)?=(\d+)#', $newtext, $matches))
{
$found_object_ids['thread_last'] = $matches[1];
$found_object_ids['thread_ids'] = array_merge($found_object_ids['thread_ids'], $found_object_ids['thread_last']);
}
if(preg_match_all('#'.$matchpre2.'member\.'.VBSEO_VB_EXT.'\?[^"]*?find=lastposter.*?'.VBSEO_FORUMID_URI.'=(\d+)#', $newtext, $matches))
{
$found_object_ids['forum_last'] = array_merge($found_object_ids['forum_last'], $matches[1]);
}
}
if(VBSEO_REWRITE_MALBUMS)
{
if(preg_match_all('#album\.'.VBSEO_VB_EXT.'\?[^"\']*?albumid=(\d+)#', $newtext, $matches))
$found_object_ids['album'] = $matches[1];
if(preg_match_all('#album\.'.VBSEO_VB_EXT.'\?[^"\']*?pictureid?=(\d+)#', $newtext, $matches))
$found_object_ids['pic'] = array_merge($found_object_ids['pic'], $matches[1]);
}
if(VBSEO_REWRITE_GROUPS || VBSEO_REWRITE_MEMBERS)
{
if(preg_match_all('#picture\.'.VBSEO_VB_EXT.'\?[^"\']*?pictureid?=(\d+)#', $newtext, $matches))
$found_object_ids['pic'] = array_merge($found_object_ids['pic'], $matches[1]);
}
if(isset($GLOBALS['vbseo_find_tids']) && $GLOBALS['vbseo_find_tids'])
$found_object_ids['thread_ids'] = array_merge($found_object_ids['thread_ids'], $GLOBALS['vbseo_find_tids']);
if(VBSEO_REWRITE_BLOGS)
{
if(preg_match_all('#'.$matchpre2.'blog\.'.VBSEO_VB_EXT.'\?[^"]*?u=(\d+)#', $newtext, $matches))
$found_object_ids['user_ids'] = array_merge($found_object_ids['user_ids'],$matches[1]);
if(preg_match_all('#'.$matchpre.'blog\.'.VBSEO_VB_EXT.'\?[^"]*?b(?:logid)?=(\d+)#', $newtext, $matches))
$found_object_ids['blog_ids'] = $matches[1];
if(preg_match_all('#'.$matchpre2.'blog_attachment\.'.VBSEO_VB_EXT.'\?[^"]*?attachmentid=(\d+)#', $newtext, $matches))
$found_object_ids['blogatt_ids'] = $matches[1];
if(preg_match_all('#'.$matchpre2.'blog\.'.VBSEO_VB_EXT.'\?[^"]*?cp=(\d+)#', $newtext, $matches))
$found_object_ids['blogcp_ids'] = $matches[1];
if(preg_match_all($q='#'.$matchpre2.'blog\.'.VBSEO_VB_EXT.'\?[^"]*?blogcategoryid=(\d+)#', $newtext, $matches))
$found_object_ids['blogcat_ids'] = $matches[1];
}
if(VBSEO_REWRITE_ANNOUNCEMENT)
if(preg_match_all('#'.$matchpre2.'announcement\.'.VBSEO_VB_EXT.'\?[^"]*?f(?:orumid)?=(\d+)#', $newtext, $matches))
{
$found_object_ids['announcements'] = $matches[1];
}
if(VBSEO_REWRITE_GROUPS)
{
if(preg_match_all('#group\.'.VBSEO_VB_EXT.'\?[^"\']*?pictureid?=(\d+)#', $newtext, $matches))
$found_object_ids['pic'] = array_merge($found_object_ids['pic'], $matches[1]);
if(preg_match_all('#'.$matchpre2.'group\.'.VBSEO_VB_EXT.'\?[^"]*?groupid=(\d+)#', $newtext, $matches))
$found_object_ids['groups'] = $matches[1];
if(preg_match_all('#'.$matchpre2.'picture\.'.VBSEO_VB_EXT.'\?[^"]*?groupid?=(\d+)#', $newtext, $matches))
$found_object_ids['groups'] = array_merge($found_object_ids['groups'], $matches[1]);
}
if(VBSEO_REWRITE_ATTACHMENTS)
if(preg_match_all('#'.$matchpre2.'attachment\.'.VBSEO_VB_EXT.'\?[^"]*?attachmentid=(\d+)#', $newtext, $matches))
{
$found_object_ids['attachment_ids'] = $matches[1];
}
if(VBSEO_REWRITE_THREADS)
if(preg_match_all('#'.$matchpre.'showthread\.'.VBSEO_VB_EXT.'\?[^"]*?p(?:ostid)?=(\d+)#', $newtext, $matches))
{
$found_object_ids['postthread_ids'] = $matches[1];
if( THIS_SCRIPT == 'showpost' && !$_GET['postcount'])
$found_object_ids['prepostthread_ids'] = $matches[1];
}
if(VBSEO_REWRITE_SHOWPOST == 2)
if(preg_match_all('#'.$matchpre.'showpost\.'.VBSEO_VB_EXT.'\?[^"]*'.VBSEO_POSTID_URI.'=(\d+)#', $newtext, $matches))
{
$found_object_ids['postthread_ids'] = $matches[1];
}
if(VBSEO_REWRITE_POLLS)
if(preg_match_all('#'.$matchpre2.'poll\.'.VBSEO_VB_EXT.'\?[^"]*?'.VBSEO_ACTION_URI.'=showresults&.*?'.VBSEO_POLLID_URI.'=(\d+)#', $newtext, $matches))
{
$found_object_ids['poll_ids'] = $matches[1];
}
if(VBSEO_REWRITE_THREADS)
if(preg_match_all('#'.$matchpre.'(?:show|print)thread\.'.VBSEO_VB_EXT.'\?[^"]*?t(?:hreadid)?=(\d+)#', $newtext, $matches))
{
$found_object_ids['thread_ids'] = array_merge($found_object_ids['thread_ids'], $matches[1]);
}
if(VBSEO_REWRITE_AVATAR)
if(preg_match_all('#'.$matchpre2.'image\.'.VBSEO_VB_EXT.'\?[^"]*?'.VBSEO_USERID_URI.'=(\d+)#', $newtext, $matches))
{
$found_object_ids['user_ids'] = array_merge($found_object_ids['user_ids'], $matches[1]);
}
}
function vbseo_clean_object_ids($objectname)
{
global $found_object_ids;
if($found_object_ids[$objectname] && !is_array($found_object_ids[$objectname]))
$found_object_ids[$objectname] = array($found_object_ids[$objectname]);
$found_object_ids[$objectname] = @array_values(array_unique($found_object_ids[$objectname]));
}
function make_crawlable(&$newtext)
{
if(!VBSEO_ENABLED
&& !isset($_COOKIE['VBSEO_ON_MORE'])
)return $newtext;
if(VBSEO_IGNOREPAGES &&
(  preg_match('#('.VBSEO_IGNOREPAGES.')#i', VBSEO_REQURL)
|| preg_match('#('.VBSEO_IGNOREPAGES.')#i', VBSEO_BASE)
|| preg_match('#('.VBSEO_IGNOREPAGES.')#i', $_SERVER['HTTP_HOST'])
)
)
return $newtext;
@define('VBSEO_PROCESS', true);
error_reporting(0);
vbseo_addon_function('postprocess', $newtext);
restore_error_handler();
global $vboptions, $vbulletin, $bbuserinfo, $_COOKIE, $HTTP_COOKIE_VARS;
global $vbseo_gcache, $seo_preg_replace, $seo_links_replace, $tempusagecache, 
$threadcache, $usercache, $found_object_ids;
if(isset($_COOKIE['VBSEO_EXPOSE_MORE']))
Header('X-Processed-By: vBSEO '.VBSEO_VERSION2_MORE.' (http://www.vbseo.com)');
if( VBSEO_CLEANUP_REDIRECT )
{
$vbseo_non_clean = array(
'styleid' => array(),
'view' => array('hybrid', 'threaded', 'linear'),
'mode' => array('hybrid', 'threaded', 'linear'),
);
if(THIS_SCRIPT == 'member')
{
$vbseo_non_clean['do'] = array('getinfo');
}
foreach($vbseo_non_clean as $vbseo_nn => $vbseo_nnopt)
if( isset($_GET[$vbseo_nn]) && (!$vbseo_nnopt || in_array($_GET[$vbseo_nn], $vbseo_nnopt)) )
vbseo_safe_redirect(VBSEO_REQURL, array_keys($vbseo_non_clean));
}
vbseo_get_options();
if(defined('VBSEO_EXPIRED_MORE_LICENSE')
|| isset($_COOKIE['VBSEO_OFF_MORE'])
|| isset($_GET['VBSEO_OFF_MORE'])
)
return $newtext;
$isloggedin = ($bbuserinfo['userid']<=0) ? '' : 'yes';
$isloggedin_changed = !isset($_COOKIE['vbseo_loggedin']) || ($_COOKIE['vbseo_loggedin'] != $isloggedin);
if($isloggedin_changed)
@setcookie('vbseo_loggedin', $isloggedin, $isloggedin ? time()+3600 : time()-3600, '/');
if(VBSEO_LASTMOD_HEADER)
{
if(!$isloggedin && (THIS_SCRIPT=='showthread'))
{
$lmdate_txt = gmdate('D, d M Y H:i:s', $GLOBALS['vbseo_lastmod'] ? $GLOBALS['vbseo_lastmod'] : time()-3 ). ' GMT';
@header('Last-Modified: ' . $lmdate_txt);
if(!VBSEO_IS_ROBOT)
vbseo_insert_code("
function vbseo_cache_check()
{
if(document.cookie.indexOf('vbseo_loggedin=yes')>0 && 
document.cookie.indexOf('vbseo_redirect=yes')<0)
{
document.cookie = 'vbseo_redirect=yes; path=/';
document.location.reload(true);
}
}
setTimeout('vbseo_cache_check()', 5); ", 'head_end_js');
}
if($_COOKIE['vbseo_redirect'])
setcookie('vbseo_redirect', '', time()-3600, '/');
}
vbseo_check_stripsids();
vbseo_prepare_seo_replace();
if(isset($GLOBALS['vbseo_find_pids']) && $GLOBALS['vbseo_find_pids'])
vbseo_get_posts_info($GLOBALS['vbseo_find_pids']);
foreach($vbseo_gcache['post'] as $pid=>$pv)
{
$found_object_ids['postthreads'][] = $pv['threadid'];
}
vbseo_find_ids( $newtext );
$vbseo_gcache['thread'] = array();
vbseo_get_forum_info(true);
if(VBSEO_SITEMAP_MOD && VBSEO_IS_ROBOT)
{
vbseo_hit_log();
}
global $VBSEO_REWRITE_TEXTURLS;
if(!$VBSEO_REWRITE_TEXTURLS)
define('VBSEO_SHOW_COPYRIGHT', (
!isset($tempusagecache['STANDARD_REDIRECT'])
&& !isset($tempusagecache['WHOPOSTED'])
&& !isset($tempusagecache['ATTACHMENTS'])
&& !isset($tempusagecache['SHOWTHREAD_SHOWPOST'])
&& !isset($tempusagecache['BUDDYLIST'])
&& !isset($tempusagecache['smiliepopup'])
&& !isset($tempusagecache['reputation'])
&& !isset($tempusagecache['im_message'])
&& !isset($tempusagecache['newattachment'])
&& (THIS_SCRIPT != 'blunts_whodownloaded_ip')
)
);
if(!$VBSEO_REWRITE_TEXTURLS)
if(VBSEO_SHOW_COPYRIGHT && !defined('VBSEO_BRANDING_FREE'))
{
if(VBSEO_COPYRIGHT==0)
{
$vbseo_host = VBSEO_HTTP_HOST;
$vbseo_cpno = ord($vbseo_host[strlen($vbseo_host)/2]) + strlen($vbseo_host);
$vbseo_cpno = $vbseo_cpno % 4;
}else
$vbseo_cpno = (VBSEO_COPYRIGHT-1);
$vbseo_cpa = array(
'Search Engine Friendly URLs by vBSEO '.VBSEO_VERSION2_MORE,
'Content Relevant URLs by vBSEO '.VBSEO_VERSION2_MORE,
'Search Engine Optimization by vBSEO '.VBSEO_VERSION2_MORE,
'SEO by vBSEO '.VBSEO_VERSION2_MORE,
'Search Engine Friendly URLs by vBSEO '.VBSEO_VERSION2_MORE.' &copy;2009, Crawlability, Inc.',
'Content Relevant URLs by vBSEO '.VBSEO_VERSION2_MORE.' &copy;2009, Crawlability, Inc.',
'Search Engine Optimization by vBSEO '.VBSEO_VERSION2_MORE.' &copy;2009, Crawlability, Inc.',
'SEO by vBSEO '.VBSEO_VERSION2_MORE.' &copy;2009, Crawlability, Inc.',
'LinkBacks Enabled by vBSEO '.VBSEO_VERSION2_MORE,
'LinkBacks Enabled by vBSEO '.VBSEO_VERSION2_MORE.' &copy; 2009, Crawlability, Inc.',
);
$vbseo_cpno = $vbseo_cpno % count($vbseo_cpa);
$cp_str = $vbseo_cpa[$vbseo_cpno];
$clinked = !strstr($cp_str,'&copy;');
if(VBSEO_NOVER_INFO)
$cp_str = str_replace(' '.VBSEO_VERSION2_MORE, '', $cp_str);
if(VBSEO_AFFILIATE_ID)
$cp_str = str_replace($clinked ? 'vBSEO' : $cp_str,
'<a href="http://www.vbseo.com/'.VBSEO_AFFILIATE_ID.'/">'.($clinked ? 'vBSEO' : $cp_str).'</a>', $cp_str);
else
if($clinked)
$cp_str = str_replace('vBSEO', '<a href="http://www.crawlability.com/vbseo/">vBSEO</a>', $cp_str);
if(defined('VBSEO_UNREG'))
$cp_str .= ' (<span style="color:red;font-weight:bold;">'.(preg_match('#^vresp(.*)#',$vboptions['vbseo_confirmation_code'],$unregpm)?$unregpm[1]:'Unregistered').'</span>)';
$lfollow = (THIS_SCRIPT == 'index')||(THIS_SCRIPT == 'adv_index');
if(!$lfollow)
$cp_str = str_replace('<a', '<a rel="nofollow"', $cp_str);
if(VBSEO_GOOGLE_AD_SEC)
$cp_str = vbseo_google_ad_section($cp_str, true);
if(strstr($newtext,'<!--VBSEO_COPYRIGHT-->'))
{
$newtext = str_replace('<!--VBSEO_COPYRIGHT-->', $cp_str, $newtext);
}else
if(preg_match('#(Copyright[^<]*?Jelsoft Enterprises Ltd\.)#im',$newtext,$cpfind))
{
$newtext = str_replace($cpfind[1], $cpfind[1]."\n<br />".$cp_str, $newtext);
}else
{
vbseo_insert_code('<br /><div style="z-index:3" class="smallfont" align="center">'.$cp_str.'</div>',
'body_end');
}
if(VBSEO_TWEETBOARD && VBSEO_TWEETBOARD_USER)
{
vbseo_insert_code('
<script type="text/javascript" src="http://tweetboard.com/tb.js?v=1.0&amp;user='.VBSEO_TWEETBOARD_USER.'"></script>', 'body_end');
}
}
if(VBSEO_SHOW_COPYRIGHT && VBSEO_ARCHIVE_LINKS_FOOTER && (THIS_SCRIPT != 'archive'))
{
if(VBSEO_ARCHIVE_LINKS_FOOTER<3 || !$bbuserinfo['joindate'])
{
$arc_str = vbseo_prepare_arc_links();
if(strstr($newtext,'<!--VBSEO_ARCHIVE_LINKS-->'))
{
$newtext = str_replace('<!--VBSEO_ARCHIVE_LINKS-->', $arc_str, $newtext);
}else
vbseo_insert_code('<br /><div style="z-index:3" class="smallfont" align="center">'.$arc_str.'</div>',
'body_end');
}
}
if(defined('VBSEO_UNREG_EXPIRED'))
return $newtext;
if(VBSEO_CODE_CLEANUP && !isset($_GET['vbseo_nocleanup']) && !$_POST['ajax'] && !$VBSEO_REWRITE_TEXTURLS)
{
$newtext = preg_replace(
array(  '#(<pre[^>]*?>)(.*?)</pre>#sie',
'#>\s+<#s',
'#(<s(?:cript|tyle)[^>]*?>[^<]*?<!)--#si',
'#<!--(\s*(\[|\/?VBS|google_ad))#s',
'#<!--.*?-->#s',
'#<!js#',
'#\@vbseo_r_n\@#',
),
array(  "str_replace('\\\\\"', '\"', '$1'.preg_replace(\"#\r?\n#s\",'@vbseo_r_n@','$2').'$3')",
'> <',
'\\1js', 
'<!js\\1',
'', 
'<!--', 
"\n"
),
$newtext);
}
if($found_object_ids['announcements'])
{
vbseo_clean_object_ids('announcements');
vbseo_get_forum_announcement($found_object_ids['announcements']);
}
vbseo_clean_object_ids('poll_ids');
vbseo_get_poll_info($pollids);
if(VBSEO_REWRITE_ATTACHMENTS)
{
vbseo_clean_object_ids('attachment_ids');
vbseo_get_attachments_info($found_object_ids['attachment_ids']);
}
if($found_object_ids['thread_ids'] || $found_object_ids['postthreads'])
{
$found_object_ids['thread_ids'] = array_merge($found_object_ids['thread_ids'], $found_object_ids['postthreads']);
vbseo_clean_object_ids('thread_ids');
if($threadcache)
{
$vbseo_gcache['thread'] = array();
foreach($threadcache as $tid => $tar)
{
$vbseo_gcache['thread'][$tar['threadid'] ? $tar['threadid'] : $tid] = $tar;
if($found_object_ids['thread_last'] && in_array($tar['threadid'], $found_object_ids['thread_last']))
$found_object_ids['user_names'][] = $tar['lastposter'];
}
$found_object_ids['thread_ids'] = array_diff($found_object_ids['thread_ids'], array_keys($threadcache));
foreach($vbseo_gcache['thread'] as $tid => $tar)
{
vbseo_thread_seotitle($vbseo_gcache['thread'][$tid]);
$usercache[$tar['postuserid']] = array(
'userid'=>$tar['postuserid'],
'username'=>$tar['postusername']
);
if($tar['pollid'])
$vbseo_gcache['polls'][$tar['pollid']]['threadid'] = $tid;
}
}
if(isset($GLOBALS['getlastpost']))
$vbseo_gcache['thread'][$GLOBALS['getlastpost']['threadid']] = $GLOBALS['getlastpost'];
vbseo_get_thread_info($found_object_ids['thread_ids']);
}
if($found_object_ids['prepostthread_ids'])
vbseo_get_post_thread_info($found_object_ids['prepostthread_ids'], true);
if(VBSEO_REWRITE_GROUPS)
{
if(is_array($GLOBALS['group']) && $GLOBALS['group']['groupid'])
$vbseo_gcache['groups'][$GLOBALS['group']['groupid']] = $GLOBALS['group'];
$found_object_ids['groups'] = array_diff($found_object_ids['groups'], array_keys($vbseo_gcache['groups']));
vbseo_get_group_info($found_object_ids['groups']);
foreach($vbseo_gcache['groups'] as $ginfo)
$vbseo_gcache['groupscat'][$ginfo['socialgroupcategoryid']] = array (
'categoryid' => $ginfo['socialgroupcategoryid'],
'title'      => $ginfo['categoryname'],
);
global $discussion;
if($discussion)
$vbseo_gcache['groupsdis'][$discussion['discussionid']] = $discussion;
if(isset($vbulletin) && isset($vbulletin->sg_category_cloud))
foreach($vbulletin->sg_category_cloud as $sgc)
$vbseo_gcache['groupscat'][$sgc['categoryid']] = $sgc;
}
if(VBSEO_REWRITE_BLOGS)
{
global $categories, $postattach;
if(is_array($postattach))
foreach($postattach as $pid=>$attarr)
if(is_array($attarr))
foreach($attarr as $aid=>$att)
$vbseo_gcache['battach'][$aid] = $att;
if($found_object_ids['blogatt_ids'])
vbseo_get_blogatt_info($found_object_ids['blogatt_ids']);
foreach($vbseo_gcache['battach'] as $batid=>$batarr)
$found_object_ids['blog_ids'][] = $batarr['blogid'];
if(is_array($GLOBALS['blog']) && $GLOBALS['blog']['blogid'] && $GLOBALS['blog']['userid'])
$vbseo_gcache['blog'][$GLOBALS['blog']['blogid']] = $GLOBALS['blog'];
if($found_object_ids['blog_ids'])
vbseo_get_blog_info($found_object_ids['blog_ids']);
if(isset($vbulletin->vbblog['categorycache']))
foreach($vbulletin->vbblog['categorycache'] as $uid=>$catarr)
if(is_array($catarr))
foreach($catarr as $cid=>$carr)
{
$vbseo_gcache['blogcat'][$cid] = $carr;
}
$vblog_cats = $GLOBALS['vblog_categories'] ? $GLOBALS['vblog_categories'] : $GLOBALS['categories'];
if(is_array($vblog_cats))
foreach($vblog_cats as $bid=>$catarr)
if(is_array($catarr))
foreach($catarr as $cid=>$carr)
if($carr['blogcategoryid'])
$vbseo_gcache['blogcat'][$carr['blogcategoryid']] = $carr;
$found_object_ids['blogcat_ids'] = array_diff($found_object_ids['blogcat_ids'], array_keys($vbseo_gcache['blogcat']));
if($found_object_ids['blogcat_ids'])
vbseo_get_blog_cats($found_object_ids['blogcat_ids']);
if(isset($vbseo_gcache['blog'])&&is_array($vbseo_gcache['blog']))
foreach($vbseo_gcache['blog'] as $bid=>$barr)
$found_object_ids['user_ids'][] = $barr['userid'];
if($found_object_ids['blogcp_ids'])
vbseo_get_object_info('blogcp_ids');
}
if(is_array($gpic = $GLOBALS['pictureinfo']))
$vbseo_gcache['pic'][$gpic['pictureid']] = $gpic;
vbseo_get_object_info('pic');
if(VBSEO_REWRITE_MEMBERS || VBSEO_REWRITE_MALBUMS || VBSEO_REWRITE_AVATAR || VBSEO_REWRITE_BLOGS)
{
if(!empty($found_object_ids['user_ids'])||!empty($found_object_ids['user_names']))
{
if(is_array($vbseo_gcache['pic']))
foreach($vbseo_gcache['pic'] as $pid=>$parr)
$found_object_ids['album'][] = $parr['albumid'];
if(is_array($galb = $GLOBALS['albuminfo']))
$vbseo_gcache['album'][$galb['albumid']] = $galb;
vbseo_get_object_info('album');
if(is_array($vbseo_gcache['album']))
foreach($vbseo_gcache['album'] as $pid=>$parr)
$found_object_ids['user_ids'][] = $parr['userid'];
$userids = array_unique($found_object_ids['user_ids']);
vbseo_clean_object_ids('user_names');
if(isset($GLOBALS['newuserid']))
$usercache[$GLOBALS['newuserid']] = array(
'userid'=>$GLOBALS['newuserid'],
'username'=>$GLOBALS['newusername']
);
if(!empty($usercache))
foreach($usercache as $uid => $uval)
if($uid && $uname = strip_tags($uval['username']))
{
$vbseo_gcache['user'][$uid] =
$vbseo_gcache['usernm'][strtolower($uname)] = array(
'userid'=>$uid,
'username'=>$uname
);
}
if(!empty($vbseo_gcache['post']))
foreach($vbseo_gcache['post'] as $pid => $pval)
if( isset($pval['postuserid']) &&
($uid = $pval['postuserid']) &&
($uname = $pval['postusername']) )
{
$vbseo_gcache['user'][$uid] =
$vbseo_gcache['usernm'][strtolower($uname)] = array(
'userid'=>$uid,
'username'=>$uname
);
}
$userids = array_diff($userids, array_keys($vbseo_gcache['user'] ? $vbseo_gcache['user'] : array()));
$found_object_ids['user_names'] = array_diff($found_object_ids['user_names'], array_keys($vbseo_gcache['usernm']));
if((VBSEO_GET_MEMBER_TITLES && (!empty($userids))
|| !empty($found_object_ids['user_names'])))
{
vbseo_get_user_info($userids, $found_object_ids['user_names']);
}else
{
for($ui=0; $ui<count($userids); $ui++)
$vbseo_gcache['user'][$userids[$ui]] = array(
'userid' => $userids[$ui]
);
}
}
}
$vbse_rurl = $vbse_rurl_check = '';
$force_redirect = false;
vbseo_get_options();
$mode_nonlinear = vbseo_is_threadedmode();
if(($_SERVER['REQUEST_METHOD'] != 'POST') && !defined('VBSEO_AJAX'))
{
if(!$mode_nonlinear &&
VBSEO_THREAD_301_REDIRECT &&
((VBSEO_REWRITE_THREADS && (THIS_SCRIPT == 'showthread'))||
(VBSEO_REWRITE_PRINTTHREAD && (THIS_SCRIPT == 'printthread'))
)
&& !isset($_GET[VBSEO_PAGENUM_URI_GARS])
&& (
(!isset($_GET['goto']) && ($thisthreadid = $_GET['t']))
||
(defined('VBSEO_PRIVATE_REDIRECT_THREAD') && ($thisthreadid = VBSEO_PRIVATE_REDIRECT_THREAD) )
)
)
{
vbseo_get_thread_info($thisthreadid);
$tinfo = $vbseo_gcache['thread'][$thisthreadid];
$thisforumid = $tinfo['forumid'];
if(function_exists('fetch_permissions'))
if(!$vbulletin->userinfo['userid'])
$vbseo_gcache['forum'][$thisforumid]['permissions'][1] = fetch_permissions($thisforumid);
$is_public = vbseo_forum_is_public($vbseo_gcache['forum'][$thisforumid], '', 1);
if($is_public)
{
$maxpage = vbseo_thread_pagenum($tinfo['replycount']+1);
$_page = isset($_GET['page']) ? intval($_GET['page']) : 0;
if(defined('VBSEO_PRIVATE_REDIRECT_URL'))
$vbse_rurl = VBSEO_PRIVATE_REDIRECT_URL;
else
$vbse_rurl = vbseo_thread_url($thisthreadid, min($_page, $maxpage),
(($_page>1) ?
((THIS_SCRIPT == 'showthread')?VBSEO_URL_THREAD_PAGENUM:VBSEO_URL_THREAD_PRINT_PAGENUM)
:
((THIS_SCRIPT == 'showthread')?VBSEO_URL_THREAD:VBSEO_URL_THREAD_PRINT)
));
$excpars = array(VBSEO_THREADID_URI, 'threadid',
'page', (isset($_GET['pp']) && $_GET['pp'] == $vboptions['maxposts'])?'pp':'',
'posted'
);
if($_REQUEST['posted'])
$force_redirect = true;
}
}
if(VBSEO_REWRITE_MEMBERS && (THIS_SCRIPT == 'member'))
{
$agt = $_GET; 
$excpars = array('u','userid', 'action');
unset($agt['u']); unset($agt['userid']); 
if($agt['action'] == 'getinfo')unset($agt['action']);
if(($uid = $_GET['u']) && !$agt)
{
$vbse_rurl = vbseo_member_url($uid);
}
}
if(VBSEO_REWRITE_FORUM && (THIS_SCRIPT == 'forumdisplay') && ($_GET['f']>0))
{
$vbse_rurl = vbseo_forum_url($_GET['f'], $GLOBALS['pagenumber']);
if($vbse_rurl)
$excpars = array('f','page');
}
if((VBSEO_REWRITE_BLOGS) && THIS_SCRIPT == 'blog')
{
if(VBSEO_REWRITE_BLOGS_LIST && $_GET['do'] == 'list' && $_REQUEST['page'] && $_REQUEST['page']!=$_REQUEST['pagenumber'])
{
$vbse_rurl = vbseo_blog_url($_GET['m'] ? VBSEO_URL_BLOG_MONTH_PAGE : VBSEO_URL_BLOG_LIST_PAGE, $_GET);
$excpars = array('do', 'page', 'm', 'y');
}
if( VBSEO_REWRITE_BLOGS_ENT &&
(($bid = $_GET['b']) || ($bid = $_GET['blogid'])) &&
(!$_GET['goto'] || in_array($_GET['goto'], array('next','prev')))
)
{
if($_GET['goto'])
{
if($GLOBALS['blogid'] != $bid)
$bid = $_GET['b'] = $GLOBALS['blogid'];
else
$bid = 0;
}
if($bid && VBSEO_REWRITE_BLOGS_ENT)
{
vbseo_get_blog_info($bid);
if( $vbseo_gcache['blog'][$bid]['title'])
$vbse_rurl = vbseo_blog_url(vbseo_vb_gpc('pagenumber')>1 ? VBSEO_URL_BLOG_ENTRY_PAGE : VBSEO_URL_BLOG_ENTRY, $_GET);
$excpars = array('b', 'page');
}
}
if( VBSEO_REWRITE_BLOGS_ENT && $_REQUEST['do'] == 'blog' 
&& !$_REQUEST['b'] && $_REQUEST['bt'] && $GLOBALS['blogid'] )
{
$vbse_rurl = vbseo_blog_url(
(vbseo_vb_gpc('pagenumber') > 1) ? VBSEO_URL_BLOG_ENTRY_PAGE : VBSEO_URL_BLOG_ENTRY, 
array('b' => $GLOBALS['blogid'], 'page' => vbseo_vb_gpc('pagenumber'))
);
$excpars = array('bt', 'page');
$vbse_rurl .= '#comment'.$_REQUEST['bt'];
}
if($_GET['u'] && $_GET[VBSEO_BLOG_CATID_URI] && VBSEO_REWRITE_BLOGS_CAT)
{
$vbse_rurl = vbseo_blog_url($_GET['page'] ? VBSEO_URL_BLOG_CAT_PAGE : VBSEO_URL_BLOG_CAT, $_GET);
$excpars = array('u', VBSEO_BLOG_CATID_URI);
}
}
if( VBSEO_REWRITE_MALBUMS && THIS_SCRIPT == 'album' && !isset($_GET['do']))
{  
if(isset($_GET['pictureid']) )
{
$vbse_rurl = vbseo_album_url(
$_GET['page']>1?'VBSEO_URL_MEMBER_PICTURE_PAGE':'VBSEO_URL_MEMBER_PICTURE', 
$_GET);
$excpars = array('albumid', 'pictureid', 'page');
}
}
if(VBSEO_REWRITE_TAGS && THIS_SCRIPT == 'tags' && $_GET['tag'])
{
$apars = $_GET;
$apars['tag'] = urlencode($apars['tag']);
$vbse_rurl = vbseo_tags_url($apars['page'] ? VBSEO_URL_TAGS_ENTRYPAGE : VBSEO_URL_TAGS_ENTRY, $apars);
$excpars = array('tag', 'page');
}
if(VBSEO_REWRITE_GROUPS && $_GET['gmid'] && !$_GET['do'])
{                                        
$_GET['page'] = vbseo_gmsg_pagenum($_GET['discussionid'], $_GET['gmid']);
$vbse_rurl = vbseo_group_url(vbseo_groupdis_urlf($_GET['page'] > 1), $_GET);
if($vbse_rurl)
{
$vbse_rurl .= '#gmessage'.$_GET['gmid'];
$excpars = array('do', 'discussionid', 'groupid', 'page');
}
}
if(!$vbse_rurl)
{
$vbse_rurl2 = '';
$auto_replace = array(
'group' => array('groupid', 'do', 'pp', 'page', 'pictureid', 'gmid', 
'sort', 'order', 'cat', 'discussionid', 'commentid', 'dofilter'),
'tags' => array('tag'),
);
$_vreq = VBSEO_REQURL;
if(in_array(THIS_SCRIPT, array_keys($auto_replace)) && $_vreq && ($_vreq[0]!='?'))
{
$vbse_rurl2 = vbseo_any_url(VBSEO_REQURL);
$excpars = $auto_replace[THIS_SCRIPT];
}
if(preg_match('#^(.*)\?(.*)#', $vbse_rurl2, $pmatch))
{
$_SERVER['QUERY_STRING'] = $pmatch[2];
$vbse_rurl2 = $pmatch[1];
}
$vbse_rurl = $vbse_rurl2;
}
if($vbse_rurl)
vbseo_url_autoadjust($vbse_rurl, $excpars, $force_redirect);
}
if(defined('VBSEO_PRIVATE_REDIRECT_SUGGEST') )
{
global $foruminfo;
$thisforumid = $foruminfo['forumid'];
if(function_exists('fetch_permissions'))
if(!$vbulletin->userinfo['userid'])
$vbseo_gcache['forum'][$thisforumid]['permissions'][1] = fetch_permissions($thisforumid);
$is_public = vbseo_forum_is_public($vbseo_gcache['forum'][$thisforumid], '', 1);
if($is_public)
{
if(VBSEO_PRIVATE_REDIRECT_SUGGEST != substr(VBSEO_REQURL, 0, strlen(VBSEO_PRIVATE_REDIRECT_SUGGEST)) )
vbseo_safe_redirect(VBSEO_PRIVATE_REDIRECT_SUGGEST, array(), true);
}
}
if(VBSEO_BASEDEPTH)
{
if(preg_match('#<base href="([^\"]*)#i', $newtext, $pm))
if(preg_replace('#/[^/]*$#', '', $pm[1]) == $vboptions['bburl2'])
define('VBSEO_BASEHREF_INDIR',1);
}
if( VBSEO_BASEDEPTH && defined('VBSEO_PREPROCESSED') )
{
$newtext = preg_replace('#<head>#i', "$0\n".'<base href="'.$vboptions['bburl2'].(defined('VBSEO_BASE_URL')?VBSEO_BASE_URL:'').'/" /><!--[if IE]></base><![endif]-->', $newtext, 1);
}
if(isset($VBSEO_REWRITE_TEXTURLS))
{
$newtext = preg_replace (
'#('.str_replace('tps\:','tps?\:',preg_quote($vboptions['bburl2'],'#')).'/?)([^<\]\[\"\)\s]*)#ise',
'vbseo_replace_urls("$1", "$2")',
$newtext
);
}
if($GLOBALS['vbseo_proc_xml'])
{
$newtext = preg_replace ( 
'#(<link>(?:\<\!\[CDATA\[)?)([^<\]]*)#ise',
'vbseo_replace_urls("$1", "$2")',
$newtext
);
}
if(1)
{
$newtext2 = preg_replace (
'#(value="(?:\[.*?\])?)('.preg_quote($vboptions['bburl2'],'#').'/?)([^<\]\[\"\)\s]*)#ise',
'stripslashes(\'$1\').vbseo_replace_urls("", "$2$3")',
$newtext
);
if($newtext2) $newtext = $newtext2;
}
if(!isset($VBSEO_REWRITE_TEXTURLS))
{
$r_tags = 'a|span';
if(VBSEO_ABSOLUTE_PATH_IN_URL)
{
$r_tags .= '|form|script|link';
}
if(VBSEO_ABSOLUTE_PATH_IN_URL||VBSEO_REWRITE_ATTACHMENTS||VBSEO_REWRITE_AVATAR)
{
$r_tags .= '|img';
}
$r_tattr = 'href|src|action|url|\.open|\.location';
$newtext = preg_replace (
'#(<(?:'.$r_tags.')([^>]*?)(?:'.$r_tattr.')\s*[=\(]\s*["\'])([^"\'>\)]*)(.*?[\>])([^<]*)(</a>)?#ise',
"vbseo_replace_urls('$1', '$3', '$2', '$4', '$5', '$6')",
$newtext
);
global $g_replace_cache;
if(!defined('VBSEO_AJAX') && isset($g_replace_cache))
unset($g_replace_cache);
if(strpos($_SERVER['REQUEST_URI'], 'printthread.'.VBSEO_VB_EXT)!==false)
{
$obb = $vboptions['relbburl'];
$vboptions['relbburl'] = $vboptions['bburl2'];
$GLOBALS['VBSEO_REWRITE_PRINTTHREAD'] = 1;
$newtext = preg_replace (
'#(\([^\)]*?(?:http://)?[^\)]*?)('.preg_quote($vboptions['bburl2'],'#').'/[^<\)]*)#ise',
'vbseo_replace_urls("$1", "$2")',
$newtext
);
$vboptions['relbburl'] = $obb;
}
}
if(VBSEO_ADD_ANALYTICS_CODE && VBSEO_ADD_ANALYTICS_CODE_EXT)
{
if(vbseo_is_threadedmode())
{
$newtext = preg_replace('#^(\s*pd\[\d+\] = )\'(.+)$#me', '"$1\'".preg_replace("#(pageTracker\._trackPageview \\()\'(.*?)\'#","\$1\\\'\$2\\\'", str_replace("\\\'","\'","$2"))', $newtext);
}
}
$ticonurl = str_replace('.gif', '', VBSEO_TREE_ICON);
global $forumid;
if(VBSEO_REWRITE_TREE_ICON && strpos($newtext, $ticonurl)!==false)
if(!$GLOBALS['vbseo_applyto_forums'] || in_array($forumid, $GLOBALS['vbseo_applyto_forums']))
{
if(preg_match('#'.$ticonurl.'(_...)?[^>]+?alt="([^"]+)"#', $newtext, $matches))
{
$currentdir = $matches[1];
$currentalt = $matches[2];
}
$ticonurl_full = $ticonurl . $currentdir . '.gif';
$ticon_format  = str_replace('.gif', $currentdir.'.gif', VBSEO_URL_THREAD_TREE_ICON);
$ficon_format  = str_replace('.gif', $currentdir.'.gif', VBSEO_URL_FORUM_TREE_ICON);
$url = $_SERVER['REQUEST_URI'];
if($tempusagecache['FORUMDISPLAY'])
{
if(preg_match('#'.VBSEO_FORUMID_URI.'=(\d+)#', $url, $matches))
{
$forumid = $matches[1];
$thisforum = &$vbseo_gcache['forum'][$forumid];
$newtext = str_replace($ticonurl_full,
vbseo_forum_url($forumid, 0, VBSEO_ICON_PREFIX.$ficon_format),
$newtext);
$newtext = str_replace($currentalt, str_replace('"', '&quot;', $thisforum['title']), $newtext);
}
}
else if($tempusagecache['SHOWTHREAD'])
{
reset($vbseo_gcache['thread']);
list($threadid, $thisthread) = each($vbseo_gcache['thread']);
$newtext = str_replace($ticonurl_full,
vbseo_thread_url($threadid, 0, VBSEO_ICON_PREFIX.$ticon_format),
$newtext);
$newtext = str_replace($currentalt, str_replace('"', '&quot;', $thisthread['title']), $newtext);
}
}
if(THIS_SCRIPT=='showthread' && VBSEO_REWRITE_THREADS && $GLOBALS['threadedmode'])
{
preg_match_all('#writeLink\(\s*(\d+)#', $newtext, $posts);
reset($vbseo_gcache['thread']);
list($threadid, $thisthread) = each($vbseo_gcache['thread']);
$gen_post_url = vbseo_thread_url($threadid, 1, VBSEO_URL_THREAD_GOTOPOST, "' + postid + '");
vbseo_insert_code('
var plist = new Array("'.implode('","',$posts[1]).'")
for(var i=0;i<plist.length;i++){
postid = plist[i]
postel = document.getElementById(\'div\'+postid)
if(postel){
newurl = \''.$gen_post_url.'\'
postel.innerHTML = postel.innerHTML.replace(\'showthread.'.VBSEO_VB_EXT.'?'.(VBSEO_VB35X ? 'p' : 'postid').'=\'+postid, newurl);
}
}', 'body_end_js');
}
if(VBSEO_VIRTUAL_HTML && (!VBSEO_VIRTUAL_HTML_GUESTS_ONLY || ($bbuserinfo['userid']<=0)) )
{
$js_htmls = '';
preg_match_all('#\<\!--VBSEO_VIRTUAL_HTML--\>(.*?)\<\!--\/VBSEO_VIRTUAL_HTML--\>#s', $newtext, $vhmatch, PREG_SET_ORDER);
foreach($vhmatch as $vi=>$vhm)
{
$newtext = str_replace($vhm[0], '<div id="vbseo_vhtml_'.$vi.'"></div>', $newtext);
$js_htmls .= 'vbseo_jshtml['.$vi.'] = "'.addslashes(preg_replace('#[\r\n]#','',$vhm[1])).'";'."\n";
}
if($js_htmls)
{
vbseo_insert_code('
var vbseo_jshtml = new Array();
'.$js_htmls.'
for(var vi=0;vi<vbseo_jshtml.length;vi++)
if(fetch_object("vbseo_vhtml_"+vi))fetch_object("vbseo_vhtml_"+vi).innerHTML = vbseo_jshtml[vi];', 
'body_end_js');
}
}
$vbseo_fp = '';
if(VBSEO_REWRITE_META_DESCRIPTION||VBSEO_REWRITE_META_KEYWORDS)
{
$kw_content = $desc_content = '';
$desc_append = false;
switch(THIS_SCRIPT)
{
case 'showpost':
global $postinfo;
if(VBSEO_REWRITE_META_DESCRIPTION && ($postid = $postinfo['postid']))
{
$desc_content = 'Post '.$postid.' - ';
$desc_append = true;
}
break;
case 'tags':
if(VBSEO_REWRITE_META_DESCRIPTION && ($ttag = $GLOBALS['tag']))
{
global $vbphrase;
$desc_content = construct_phrase($vbphrase['threads_tagged_with_x'], $ttag['tagtext']);
}
break;
case 'member':
$ui = & $GLOBALS['usercache'][$_GET['u']];
$kw_content = $ui['username'];
$desc_content = str_replace(
array('[username]', '[usertitle]', '[bb_title]', '[bbtitle]'),
array($ui['username'], $ui['usertitle'], $vboptions['bbtitle'], $vboptions['bbtitle']),
VBSEO_META_DESCRIPTION_MEMBER
);
$desc_content = @preg_replace('#\[user_field_(\d+)\]#e',
'$ui[\'field\'.\'$1\']',
$desc_content
);
break;
case 'forumdisplay':
global $vbphrase;
$fi = & $GLOBALS['forumcache'][$_GET['f']];
$kw_content = $fi['title'];
$kw_content = preg_replace('#[^a-zA-Z0-9_\x80-\xff]+#', ',', $kw_content);
$desc_content = $fi['title'] . ($_GET['page'] ? ', '.construct_phrase($vbphrase['page_x'],$_GET['page']) : '') . (isset($fi['description'])?' - ' . $fi['description']:'');
break;
case 'showpost':
case 'showthread':
if(VBSEO_REWRITE_META_DESCRIPTION)
{
$desc_content = vbseo_extract_msg_postbits();
if(VBSEO_META_DESCRIPTION_UNIQUE)
{
$desc_append = true;
$desc_content = vbseo_substr_words($desc_content, VBSEO_META_DESCRIPTION_MAX_CHARS - 10);
$desc_content .= ' ('.$GLOBALS['threadinfo']['threadid'].')';
}
}
if(VBSEO_REWRITE_META_KEYWORDS && ($tbits = $GLOBALS['threadinfo']['title']))
{
if((VBSEO_FILTER_STOPWORDS != 0) && VBSEO_STOPWORDS)
{
$tbits = preg_replace('#\b('.VBSEO_STOPWORDS.')\b#is', '', $tbits);
}
preg_match_all('#([a-zA-Z0-9_\x80-\xff]+)#s', $tbits, $ptext);
$kw_content = implode(',', $ptext[1]);
}
break;
case 'blog':
if(VBSEO_REWRITE_META_DESCRIPTION && $desc_content = $GLOBALS['blog']['message'])
{
$desc_content = preg_replace('#(<.*?>)+#s', ' ', $desc_content);
$desc_content = trim($desc_content);
}
if(VBSEO_REWRITE_META_KEYWORDS && ($tbits = $GLOBALS['blog']['title']))
{
if((VBSEO_FILTER_STOPWORDS != 0) && VBSEO_STOPWORDS)
{
$tbits = preg_replace('#\b('.VBSEO_STOPWORDS.')\b#is', '', $tbits);
}
preg_match_all('#([a-zA-Z0-9_\x80-\xff]+)#s', $tbits, $ptext);
$kw_content = implode(',', $ptext[1]);
}
break;
}
if(VBSEO_REWRITE_META_KEYWORDS && $kw_content)
{
$kw_content = strip_tags($kw_content);
if(VBSEO_STOPWORDS)
$kw_content = preg_replace('#,?\b(' . VBSEO_STOPWORDS . ')\b#i', '', $kw_content);
$newtext = preg_replace('#(<meta name="keywords".*?content=)"#is', '$1"'.str_replace('$','\$',$kw_content).',', $newtext);
}
if(VBSEO_REWRITE_META_DESCRIPTION && $desc_content)
{
$desc_content = strip_tags($desc_content);
$desc_content = preg_replace('#[\s\"]+#s', ' ', $desc_content);
if(strlen($desc_content) > VBSEO_META_DESCRIPTION_MAX_CHARS)
{
$desc_content = vbseo_substr_words($desc_content, VBSEO_META_DESCRIPTION_MAX_CHARS);
}
$newtext = preg_replace('#(<meta name="description".*?content=)"'.($desc_append ? '' : '[^"]*').'#is', '$1"'.str_replace('$','\$',str_replace('"','&quot;',$desc_content)), $newtext);
}
}
if($GLOBALS['vbseo_meta'])
{
foreach($GLOBALS['vbseo_meta'] as $metaname => $metacont)
$newtext = preg_replace('#(<meta name="'.$metaname.'".*?content=)"[^"]*#is', '$1"'.str_replace('$','\$',htmlspecialchars($metacont)), $newtext);
}
if(
!$GLOBALS['bbuserinfo']['badlocation'] &&
(
((THIS_SCRIPT=='forumdisplay') && ($vbseo_rr=$GLOBALS['vbseo_relev_replace']) && $GLOBALS['foruminfo']['forumid'] )
||
((THIS_SCRIPT=='showthread') && ($vbseo_rr=$GLOBALS['vbseo_relev_replace_t']) && $GLOBALS['threadinfo']['title']) 
)
)
{
$parent_forum = $vbseo_gcache['forum'][$GLOBALS['foruminfo']['parentid']];
$vbseo_rrepl = array(
'[thread_title]' => ($GLOBALS['threadinfo']['title']),
'[forum_description]' => $GLOBALS['foruminfo']['description'],
'[forum_title]' => $GLOBALS['foruminfo']['title'],
'[parent_forum_description]' => $parent_forum['description'],
'[parent_forum_title]' => $parent_forum['title'],
'[bb_title]' => $vboptions['bbtitle'],
'[default_keywords]' => $vboptions['keywords'],
'[username]' => $GLOBALS['userinfo']['username'] ,
'[thread_page]' => $_GET['page']
);
$vbseo_pn = 0;
foreach($vbseo_rr as $rr)
{
$vbseo_pn++;
if($rr)
{
$rr = str_replace(array_keys($vbseo_rrepl), array_values($vbseo_rrepl), $rr);
if(preg_match('#\[first_post_(\d+)_words\]#', $rr, $fp_m))
{
if(!$vbseo_fp)
$vbseo_fp = vbseo_extract_msg_postbits();
$rr = str_replace($fp_m[0],
preg_replace('#^\s*((\S+\s+){'.$fp_m[1].'}).*$#'.(VBSEO_UTF8_SUPPORT?'u':'').'s', '\\1', $vbseo_fp),
$rr);
}
if(preg_match('#\[thread_(\d+)_tags\]#', $rr, $fp_m))
{
global $threadinfo;
$tag_array = explode(',', $threadinfo['taglist']);
if($tag_array)
$rr = str_replace($fp_m[0],
implode(', ', array_slice($tag_array,0, min(count($tag_array),$fp_m[1]))),
$rr);
}
$rr2 = VBSEO_GOOGLE_AD_SEC ? vbseo_google_ad_section($rr) : $rr;
$newtext = str_replace('<!--VBSEO_RR_'.$vbseo_pn.'-->', $rr2, $newtext);
$newtext = str_replace('<!--VBSEO_RR_'.$vbseo_pn.'_NOHTML-->', $rr, $newtext);
}
}
}
if($seo_preg_replace && !VBSEO_ACRONYMS_IN_CONTENT && !defined('VBSEO_AJAX'))
{
$newtext = preg_replace(array_keys($seo_preg_replace), $seo_preg_replace, $newtext);
}
if(VBSEO_ADD_ANALYTICS_CODE && VBSEO_ANALYTICS_CODE)
{
global $display;
$track_url = $more_tracking = $more_tracking2 = '';
if(THIS_SCRIPT == 'search' && $_REQUEST['do'] == 'showresults' && $display['highlight'])
$track_url = 'search.php?q='.urlencode(implode(' ',$display['highlight']));
if($isloggedin_changed && VBSEO_ANALYTICS_SEGMENTATION)
{
if(VBSEO_ANALYTICS_SEGMENTATION == 1)
$more_tracking .= "\n".'pageTracker._setVar("'.(($bbuserinfo['userid']>0) ? 'member' : 'guest').'");';
else
if(VBSEO_ANALYTICS_SEGMENTATION == 2)
{
if($vbulletin && isset($vbulletin->usergroupcache))
$grouptitle = $vbulletin->usergroupcache[$bbuserinfo['usergroupid']]['title'];
else
$grouptitle = $bbuserinfo['permissions']['title'];
$more_tracking .= "\n".'pageTracker._setVar("usergroup-'.$bbuserinfo['usergroupid'].'-'.htmlspecialchars($grouptitle).'");';
}
}
if(THIS_SCRIPT == 'register')
{
$vbseo_goal = $_REQUEST['do'];
if($_GET['a'] == 'act')
$vbseo_goal = 'complete';
if($GLOBALS['templatename'] == 'register_verify_age')
$vbseo_goal = 'birthday';
$vbseo_goal_pages = array(
'coppaform'=>'coppaform.html',
'birthday'=>'enter-birthday.html',
'signup'=>'agreement.html',
'register'=>'regform-show.html',
'addmember'=>'regform-submit.html',
'complete'=>'registration-complete.html'
);
if(isset($vbseo_goal_pages[$vbseo_goal]))
$more_tracking2 .= "\n".'pageTracker._trackPageview("'.VBSEO_ANALYTICS_GOAL_PATH.$vbseo_goal_pages[$vbseo_goal].'");';
}
global $forumid;
if($forumid && VBSEO_ADD_ANALYTICS_VIRTUAL)
{
$novbseo = ($GLOBALS['vbseo_applyto_forums'] && !in_array($forumid, $GLOBALS['vbseo_applyto_forums']));
$more_tracking2 .= "\n".'pageTracker._trackPageview("/virtual/'.($novbseo?'off':'on').'/f'.$forumid.'/'.VBSEO_REQURL.'");';
}
vbseo_insert_code('
window.google_analytics_uacct = "'.VBSEO_ANALYTICS_CODE.'";
', 'head_end_js');
vbseo_insert_code('
<script type="text/javascript"><!--
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src=\'" + gaJsHost + "google-analytics.com/ga.js\' type=\'text/javascript\'%3E%3C/script%3E"));
//-->
</script>
<script type="text/javascript"><!--
try {
var pageTracker = _gat._getTracker("'.VBSEO_ANALYTICS_CODE.'");
pageTracker._trackPageview('.($track_url?"'$track_url'":"").');'
. $more_tracking . $more_tracking2 . '
} catch(err) {}
//-->
</script>', 'body_end');
}
if(VBSEO_LINK && $GLOBALS['bbuserinfo']['isadmin'])
{
vbseo_insert_code('<div style="position:absolute;z-index:3;width:100%;left:0px;top:8px;text-align:center;"><a style="BACKGROUND: #FFFFFF; padding:5px; FONT-SIZE: 11px; FONT-FAMILY: Verdana, Arial, Helvetica, sans-serif; COLOR: #003399;" href="'.$vboptions['bburl2'].'/vbseocp.php">Back to vBSEO Config Panel</a></div>',
'body_end');
}
global $vbseo_inscode;
vbseo_insert_code_parse();
foreach($vbseo_inscode as $place=>$inscode)
{
$inscode = str_replace('$', '\$', $inscode);
switch($place)
{
case 'head_end':
$newtext = preg_replace('#(<\/head>)#is', $inscode . '$1', $newtext, 1);
break;
case 'body_end':
$newtext = preg_replace('#(<\/body>)#is', $inscode . '$1', $newtext, 1);
break;
}
}
/********************************************************************************/
if(!isset($VBSEO_REWRITE_TEXTURLS))
{
}
return $newtext;
}
}
?>