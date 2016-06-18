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

function vbseo_append_a(&$title)
{
$ishort = false;
if ((preg_match('#[-_\s](?:post)?\d+$#', $title) || !$title))
{
$title .= ($title?VBSEO_SPACER:'') . VBSEO_APPEND_CHAR;
$ishort = true;
}
return $ishort;
}
function vbseo_forum_seotitle(&$vbseo_gcache_forum)
{
if (!isset($vbseo_gcache_forum['seotitle']))
$vbseo_gcache_forum['seotitle'] =
isset($GLOBALS['vbseo_forum_slugs'][$vbseo_gcache_forum['forumid']]) ?
$GLOBALS['vbseo_forum_slugs'][$vbseo_gcache_forum['forumid']] :
vbseo_filter_text(
(isset($vbseo_gcache_forum['title_clean']) && $vbseo_gcache_forum['title_clean']) ? $vbseo_gcache_forum['title_clean'] :
strip_tags($vbseo_gcache_forum['title'])
);
return isset($GLOBALS['vbseo_forum_slugs'][$vbseo_gcache_forum['forumid']]) ?
$GLOBALS['vbseo_forum_slugs'][$vbseo_gcache_forum['forumid']] : '';
}
function vbseo_thread_seotitle(&$vbseo_gcache_thread)
{
global $vbphrase;
if (!isset($vbseo_gcache_thread['seotitle']))
{
$ttl = ($vbseo_gcache_thread['title'] ? $vbseo_gcache_thread['title'] : $vbseo_gcache_thread['threadtitle']);
if(VBSEO_URL_THREAD_PREFIX && 
($prefid = $vbseo_gcache_thread['prefixid']) )
$ttl = (VBSEO_URL_THREAD_PREFIX_NAME ? $vbphrase["prefix_".$prefid."_title_plain"] : $prefid) . ' ' . $ttl;
$vbseo_gcache_thread['seotitle'] = vbseo_filter_replace_text ($ttl);
}
}
function vbseo_thread_url_row($thread_row, $page = 1)
{
global $vbseo_gcache;
$vbseo_gcache['thread'][$thread_row['threadid']] = $thread_row;
$url = vbseo_thread_url($thread_row['threadid'], $page);
unset($vbseo_gcache['thread'][$thread_row['threadid']]);
return $url;
}
function vbseo_thread_url_row_spec($thread_row, $spec)
{
global $vbseo_gcache;
$vbseo_gcache['thread'][$thread_row['threadid']] = $thread_row;
$url = vbseo_thread_url($thread_row['threadid'], 1, $spec);
unset($vbseo_gcache['thread'][$thread_row['threadid']]);
return $url;
}
function vbseo_poll_url_direct($thread_row, $poll_row)
{
global $vbseo_gcache;
$vbseo_gcache['thread'][$thread_row['threadid']] = $thread_row;
$poll_row['threadid'] = $thread_row['threadid'];
$vbseo_gcache['polls'][$poll_row['pollid']] = $poll_row;
$url = vbseo_poll_url($poll_row['pollid']);
unset($vbseo_gcache['thread'][$thread_row['threadid']]);
unset($vbseo_gcache['polls'][$poll_row['pollid']]);
return $url;
}
function vbseo_member_url_row($userid, $username)
{
global $vbseo_gcache, $vbseo_vars;
$vbseo_gcache['user'][$userid] = compact('userid', 'username');
$url = vbseo_member_url($userid);
unset($vbseo_gcache['user'][$userid]);
return $url;
}
function vbseo_post_url_row($thread_row, $post_row, $postcount)
{
global $vbseo_gcache;
$vbseo_gcache['post'][$post_row['postid']] = $post_row;
$vbseo_gcache['thread'][$thread_row['threadid']] = $thread_row;
$url = vbseo_post_url($post_row['postid'], $postcount);
unset($vbseo_gcache['post'][$post_row['postid']]);
unset($vbseo_gcache['thread'][$thread_row['threadid']]);
return $url;
}
function vbseo_post_url($postid, $post_count)
{
global $vbseo_gcache;
$pinfo = $vbseo_gcache['post'][$postid];
$threadid = $pinfo['threadid'];
$thread = &$vbseo_gcache['thread'][$threadid];
$forumid = $thread['forumid'];
if($GLOBALS['vbseo_applyto_forums'] && !in_array($forumid, $GLOBALS['vbseo_applyto_forums']))
return '';
if (!$thread['seotitle'])
vbseo_thread_seotitle($thread);
$replace = array('%post_id%' => $postid,
'%post_count%' => $post_count,
'%thread_id%' => $threadid,
'%thread_title%' => $thread['seotitle'],
'%forum_id%' => $forumid,
'%forum_title%' => $vbseo_gcache['forum'][$forumid]['seotitle'],
'%forum_path%' => $vbseo_gcache['forum'][$forumid]['path'],
);
$rets = str_replace(
array_keys($replace),
$replace,
VBSEO_URL_POST_SHOW
);
return $rets;
}
function vbseo_page_size($cachedonly = false)
{
global $vboptions, $vbulletin, $perpage;
$vbo = $vboptions ? $vboptions : ($vbulletin?$vbulletin->options:array());
$bbu = vbseo_vb_userinfo();
if($perpage && (THIS_SCRIPT=='showthread')) $bbu['maxposts'] = $perpage;
if (!$bbu['maxposts'] && vbseo_vb_userinfo('userid') && !$cachedonly)
{
$db = vbseo_get_db();
$getmaxposts = $db->vbseodb_query_first("
SELECT maxposts
FROM " . vbseo_tbl_prefix('user') . "
WHERE userid = '" . intval($bbu['userid']) . "'
LIMIT 1
");
$bbu['maxposts'] = $getmaxposts['maxposts'];
}
if ($bbu['maxposts'] > 0 && $vboptions['usermaxposts'])
$vbo['maxposts'] = $bbu['maxposts'];
return $vbo['maxposts'];
}
function vbseo_thread_pagenum($postcount, $div = true)
{
$maxposts = vbseo_page_size();
return vbseo_get_pagenum($postcount, $maxposts, $div);
}
function vbseo_get_pagenum($postcount, $maxposts, $div = true)
{
if($div)
return  ($maxposts != 0 ? @ceil($postcount / $maxposts) : 0);
else
return $postcount * $maxposts;
}
function vbseo_thread_url_postid($postid, $page = 1, $gotopost = false, $postcount = 0)
{
global $vbseo_gcache, $vboptions, $found_object_ids;
if (!$vbseo_gcache['post'][$postid])
{                           
vbseo_get_post_thread_info($found_object_ids['postthread_ids']);
vbseo_get_thread_info($found_object_ids['postthreads']);
}
$pinfo = &$vbseo_gcache['post'][$postid];
if (!$pinfo)
return '';
$threadid = $pinfo['threadid'];
if (!$tinfo = $vbseo_gcache['thread'][$threadid])
return '';
if ($postcount>0)
{
$pinfo['preposts'] = $postcount;
}
$totr = preg_replace('#[^0-9]#', '', $tinfo['replycount']);
if ($postcount == -1 && !$pinfo['preposts'])
{
$pinfo['preposts'] = 1;
}
if (!$pinfo['preposts'] && isset($tinfo['replycount']) )
{   
if($tinfo['firstpostid'] == $pinfo['postid'] )
$pinfo['preposts'] = 1;
if($tinfo['lastpostid'] == $pinfo['postid'] || vbseo_page_size(true) > $totr )
$pinfo['preposts'] = $totr + 1;
}
if ((vbseo_vb_userinfo('postorder') == 1) && !$pinfo['prepostsproc'] && isset($pinfo['preposts']) )
{   
$pinfo['preposts'] = $totr - $pinfo['preposts'] + 2;
}
if ((isset($pinfo['preposts']) && $page == 1 && !$gotopost) || ($page > 1))
{    
if($page <= 1)
$page = vbseo_thread_pagenum($pinfo['preposts']);
if($returl = vbseo_thread_url($threadid, $page))
$returl .= '#post' . $postid;
return  $returl;
}
else 
return vbseo_thread_url($threadid, $page,
$page > 1 ? VBSEO_URL_THREAD_GOTOPOST_PAGENUM : VBSEO_URL_THREAD_GOTOPOST, $postid);
}
function vbseo_thread_url($threadid, $page = null, $special_format = '', $postid = '')
{
global $vbseo_gcache;
$forumid = $vbseo_gcache['thread'][$threadid]['forumid'];
if (!$forumid)
return '';
if($GLOBALS['vbseo_applyto_forums'] && !in_array($forumid, $GLOBALS['vbseo_applyto_forums']))
return '';
$thread = &$vbseo_gcache['thread'][$threadid];
if (!$thread['seotitle'])
vbseo_thread_seotitle($thread);
$title = $thread['seotitle'];
$ishort = vbseo_append_a($title);
if(!$vbseo_gcache['forum'])
vbseo_get_forum_info();
vbseo_forum_seotitle($vbseo_gcache['forum'][$forumid]);
$replace = array('%post_id%' => $postid,
'%thread_id%' => $threadid,
'%thread_title%' => $title,
'%thread_page%' => $page,
'%forum_id%' => $forumid,
'%forum_title%' => $vbseo_gcache['forum'][$forumid]['seotitle'],
'%forum_path%' => $vbseo_gcache['forum'][$forumid]['path'],
);
$uformat = $special_format ? $special_format :
(($page <= 1) ? VBSEO_URL_THREAD : VBSEO_URL_THREAD_PAGENUM);
$rets = str_replace(
array_keys($replace),
$replace,
$uformat
);
if ($ishort)
$rets = str_replace(VBSEO_SPACER . VBSEO_SPACER, VBSEO_SPACER, $rets);
return $rets;
}
function vbseo_gen_url($url_format)
{
$replace = array(
);
$rets = str_replace(
array_keys($replace),
$replace,
$url_format
);
return $rets;
}
function vbseo_poll_url($pollid)
{
global $vbseo_gcache;
vbseo_int_var($pollid);
$threadid = $vbseo_gcache['polls'][$pollid]['threadid'];
if (!$threadid || !$vbseo_gcache['thread'][$threadid])
{
vbseo_get_poll_info($pollid);
$db = vbseo_get_db();
$tar = $db->vbseodb_query_first($q = "
SELECT threadid
FROM " . vbseo_tbl_prefix('thread') . " AS thread
WHERE pollid = $pollid
LIMIT 1
");
$threadid = $tar['threadid'];
vbseo_get_thread_info($threadid);
}
$forumid = $vbseo_gcache['thread'][$threadid]['forumid'];
if($GLOBALS['vbseo_applyto_forums'] && !in_array($forumid, $GLOBALS['vbseo_applyto_forums']))
return '';
vbseo_forum_seotitle($vbseo_gcache['forum'][$forumid]);
$title = vbseo_filter_text(strip_tags($vbseo_gcache['polls'][$pollid]['question']));
$replace = array('%poll_id%' => $pollid,
'%poll_title%' => $title,
'%forum_id%' => $forumid,
'%forum_title%' => $vbseo_gcache['forum'][$forumid]['seotitle'],
'%forum_path%' => $vbseo_gcache['forum'][$forumid]['path'],
);
$rets = str_replace(
array_keys($replace),
$replace,
VBSEO_URL_POLL
);
return $rets;
}
function vbseo_attachment_url($attid, $reformat = '', $d = '', $thumb = '')
{
global $vbseo_gcache, $found_object_ids;
$atarr = $vbseo_gcache['attach'][$attid];
$apars = array(
VBSEO_PICID_URI => $attid,
'd' => $d,
'thumb' => $thumb
);
if(vbseo_content_type($atarr) == 'album')
return VBSEO_REWRITE_MALBUMS ? vbseo_album_url('VBSEO_URL_MEMBER_PICTURE_IMG', $apars) : '';
if(vbseo_content_type($atarr) == 'group')
return vbseo_group_url(VBSEO_URL_GROUPS_PICTURE_IMG, $apars);
if(vbseo_content_type($atarr) == 'blog')
return VBSEO_REWRITE_BLOGS_ATT ? vbseo_blog_url(VBSEO_URL_BLOG_ATT, $apars) : '';
if(vbseo_content_type($atarr) == 'cms_article')
$reformat = VBSEO_URL_CMS_DOMAIN . VBSEO_URL_CMS_ATTACHMENT;
else
{
if(!$atarr['postid'] && (vbseo_content_type($atarr)=='forum'))
$atarr['postid'] = $atarr['contentid'];
$postid = $atarr['postid'];
if (!$attid || !$postid)
return '';
if (!$vbseo_gcache['post'][$postid])
{
vbseo_get_post_thread_info($found_object_ids['postthread_ids']);
vbseo_get_thread_info($found_object_ids['postthreads']);
}
$threadid = $vbseo_gcache['post'][$postid]['threadid'];
if (!$threadid)
return '';
$forumid = $vbseo_gcache['thread'][$threadid]['forumid'];
if(!$vbseo_gcache['forum'][$forumid])
return '';
if($GLOBALS['vbseo_applyto_forums'] && !in_array($forumid, $GLOBALS['vbseo_applyto_forums']))
return '';
vbseo_forum_seotitle($vbseo_gcache['forum'][$forumid]);
$t2 = &$vbseo_gcache['thread'][$threadid]['seotitle'];
if (!$t2)
{
$t2 = vbseo_filter_text($vbseo_gcache['thread'][$threadid]['threadtitle']);
}
}
if ($d)$attid .= 'd' . $d;
if ($thumb)$attid .= 't';
$replace = array('%attachment_id%' => $attid,
'%original_filename%' => vbseo_filter_text($atarr['filename'], '.'),
'%thread_title%' => $vbseo_gcache['thread'][$threadid]['seotitle'],
'%thread_title_ue%' => ($vbseo_gcache['thread'][$threadid]['title']),
'%forum_title%' => $vbseo_gcache['forum'][$forumid]['seotitle'],
'%forum_path%' => $vbseo_gcache['forum'][$forumid]['path'],
'%forum_id%' => $forumid,
);
$rets = str_replace(
array_keys($replace),
$replace,
$reformat ? $reformat : VBSEO_ATTACHMENTS_PREFIX . VBSEO_URL_ATTACHMENT
);
return $rets;
}
function vbseo_announcement_url($forumid, $announcementid = 0)
{
global $vbseo_gcache;
if (!$vbseo_gcache['forum'][$forumid]['announcement'])
return '';
if($GLOBALS['vbseo_applyto_forums'] && !in_array($forumid, $GLOBALS['vbseo_applyto_forums']))
return '';
$aid = $announcementid;
if ($announcementid)
$ann_title = $vbseo_gcache['forum'][$forumid]['announcement'][$announcementid];
else
{
reset($vbseo_gcache['forum'][$forumid]['announcement']);
list($aid, $ann_title) = each($vbseo_gcache['forum'][$forumid]['announcement']);
}
$seo_title = vbseo_filter_replace_text($ann_title);
vbseo_forum_seotitle($vbseo_gcache['forum'][$forumid]);
$replace = array('%forum_id%' => $forumid,
'%forum_title%' => $vbseo_gcache['forum'][$forumid]['seotitle'],
'%announcement_title%' => $seo_title,
'%announcement_id%' => $aid,
'%forum_path%' => $vbseo_gcache['forum'][$forumid]['path'],
'%forum_page%' => $page,
);
$rets = str_replace(
array_keys($replace),
$replace,
$announcementid?VBSEO_URL_FORUM_ANNOUNCEMENT:VBSEO_URL_FORUM_ANNOUNCEMENT_ALL
);
return $rets;
}
function vbseo_album_url_row($urlformat, $arow)
{
global $vbseo_gcache, $vbseo_vars;
$vbseo_gcache['user'][$arow['userid']] = compact($arow['userid'], $arow['username']);
$vbseo_gcache['album'][$arow['albumid']] = $arow;
$vbseo_gcache[VBSEO_PIC_STORAGE][$arow[VBSEO_PICID_URI]] = $arow;
$url = vbseo_album_url($urlformat, $arow);
unset($vbseo_gcache['user'][$arow['userid']]);
unset($vbseo_gcache['album'][$arow['albumid']]);
unset($vbseo_gcache[VBSEO_PIC_STORAGE][$arow[VBSEO_PICID_URI]]);
return $url;
}
function vbseo_album_url($urlformat, $apars)
{
global $vbseo_gcache;
$repl = array();
if($picid = $apars[VBSEO_PICID_URI])
{
$pic = $vbseo_gcache[VBSEO_PIC_STORAGE][$picid];
if(!$pic)
return '';
if(!$apars['albumid'])
$apars['albumid'] = vbseo_attachment_contentid($pic);
$repl['%picture_title%'] = vbseo_filter_text($pic['caption']);
$repl['%picture_id%'] = $pic[VBSEO_PICID_URI];
if(!$pic['extension'])
$pic['extension'] = preg_replace('#^.*\.#', '', $pic['filename']);
$repl['%original_ext%'] = $pic['extension'];
if ($apars['thumb'])$repl['%picture_id%'] .= 't';
}
if($apars['albumid'])
{
$alb = $vbseo_gcache['album'][$apars['albumid']];
if(!$alb) $alb = $apars;
$repl['%album_title%'] = vbseo_filter_text($alb['title'], false, true, true);
$repl['%album_id%'] = $apars['albumid'];
}
if(!$apars['u'])
$apars['u'] = $alb['userid'];
if($apars['page'])
$repl['%page%'] = $apars['page'];
if(!$apars['u'])
return '';
$newurl = vbseo_member_url($apars['u'], $apars['username'], $urlformat, $repl);
return $newurl;
}
function vbseo_member_url($userid, $username = '', $urlformat = '', $replace = array(), $apars = array())
{
global $vbseo_gcache;
if(!$urlformat)
$urlformat = 'VBSEO_URL_MEMBER';
if (!$userid && $username)
{
$tmpuser = &$vbseo_gcache['usernm'][strtolower($username)];
}
else
{
$tmpuser = &$vbseo_gcache['user'][$userid];
if (!$tmpuser['userid'])
$tmpuser['userid'] = $userid;
}
if (!isset($tmpuser['seoname']))
{
if ($username)
$tmpuser['username'] = $username;
if(!$tmpuser['username'] && strstr(constant($urlformat), '%user_name%'))
return '';
$tmpuser['seoname'] =
vbseo_filter_text($tmpuser['username'], null, false, true, true);
}
if($apars['page'])
{
$replace['%page%'] = $apars['page'];
}
if($apars['u2'])
{
$tmpuser2 = &$vbseo_gcache['user'][$apars['u2']];
if (!isset($tmpuser2['seoname']))
{
$tmpuser2['seoname'] =
vbseo_filter_text($tmpuser2['username'], null, false, true);
}
$replace['%visitor_id%'] = $apars['u2'];
$replace['%visitor_name%'] = $tmpuser2['seoname'];
}
if (!isset($tmpuser[$urlformat]))
{
$replace['%user_id%'] = $tmpuser['userid'];
$replace['%user_name%'] = $tmpuser['seoname'];
$form = ($urlformat=='VBSEO_URL_AVATAR'?VBSEO_AVATAR_PREFIX:'') . constant($urlformat);
$ret = str_replace(array_keys($replace), $replace, $form);
if(!$replace)
$tmpuser[$urlformat] = $ret;
}else
$ret = $tmpuser[$urlformat];
return $ret;
}
function vbseo_forum_url($forumid, $page = 0, $special_format = '')
{
global $vbseo_gcache;
if ((VBSEO_FORUMLINK_DIRECT || $vbseo_gcache['forum'][$forumid]['nametitle']) && $vbseo_gcache['forum'][$forumid]['link'])
return $vbseo_gcache['forum'][$forumid]['link'];
if($GLOBALS['vbseo_applyto_forums'] && !in_array($forumid, $GLOBALS['vbseo_applyto_forums']))
return '';
vbseo_forum_seotitle($vbseo_gcache['forum'][$forumid]);
$replace = array('%forum_id%' => $forumid,
'%forum_title%' => $vbseo_gcache['forum'][$forumid]['seotitle'],
'%forum_path%' => $vbseo_gcache['forum'][$forumid]['path'],
'%forum_page%' => $page,
);
$rets = str_replace(
array_keys($replace),
$replace,
($special_format ? $special_format :
(($page <= 1) ? VBSEO_URL_FORUM : VBSEO_URL_FORUM_PAGENUM)
)
);
return $rets;
}
function vbseo_memberlist_url($letter = '', $page = 1)
{
if (!$page) $page = 1;
if ($letter == '%23') $letter = '0';
$replace = array('%letter%' => strtolower($letter),
'%page%' => (int)$page,
);
$url = VBSEO_URL_MEMBERLIST;
if ($letter != '') $url = VBSEO_URL_MEMBERLIST_LETTER;
if ($letter == '' && $page > 1) $url = VBSEO_URL_MEMBERLIST_PAGENUM;
$rets = str_replace(
array_keys($replace),
$replace,
$url
);
return $rets;
}
function vbseo_reverse_forumtitle($arr)
{
global $vbseo_gcache;
$fid = 0;
vbseo_get_options();
vbseo_prepare_seo_replace();
vbseo_get_forum_info();
if (isset($arr['forum_path']))
{
reset($vbseo_gcache['forum']);
while (list(, $forum) = each($vbseo_gcache['forum']))
{
if ($forum['path'] == $arr['forum_path'])
{
$fid = $forum['forumid'];
break;
}
}
}
else if (isset($arr['forum_title']) && is_array($vbseo_gcache['forum']))
{
reset($vbseo_gcache['forum']);
$ue_title = urlencode(($arr['forum_title']));
while (list(, $forum) = each($vbseo_gcache['forum']))
{
if (vbseo_forum_seotitle($forum))
{
}
if (($forum['seotitle'] == $ue_title)||($forum['seotitle'] == $arr['forum_title']))
{
$fid = $forum['forumid'];
break;
}
}
}
return $fid;
}
function vbseo_sanitize_url($url)
{
return $url;
}
function vbseo_tag_filter_url($tag)
{
$tag = str_replace('%2F', '/', $tag);
$tag = str_replace('+', '%20', $tag);
if(VBSEO_URL_TAGS_FILTER)
{
$tag = urldecode($tag);
$tag = vbseo_filter_text($tag, '', false);
}
return $tag;
}
function vbseo_tags_url($urlformat, $apars = array())
{
$replace = array();
$replace['%tag%'] = vbseo_tag_filter_url($apars['tag']);
if ($apars['page'])
{
$replace['%page%'] = $apars['page'];
}
$returl = str_replace(array_keys($replace), $replace, $urlformat);
$returl = vbseo_sanitize_url($returl);
return $returl;
}
function vbseo_groupdis_urlf($multipage)
{
if(vbseo_vbversion()>='3.8')
$urlf = $multipage ? VBSEO_URL_GROUPS_DISCUSSION_PAGE : VBSEO_URL_GROUPS_DISCUSSION;
else
$urlf = $multipage ? VBSEO_URL_GROUPS_PAGE : VBSEO_URL_GROUPS;
return $urlf;
}
function vbseo_group_url_row($urlformat, $arow)
{
global $vbseo_gcache, $vbseo_vars;
$vbseo_gcache['groups'][$arow['groupid']] = $arow;
$vbseo_gcache[VBSEO_PIC_STORAGE][$arow[VBSEO_PICID_URI]] = $arow;
$vbseo_gcache['groupsdis'][$arow['discussionid']] = $arow;
$url = vbseo_group_url($urlformat, $arow);
unset($vbseo_gcache['groups'][$arow['groupid']]);
unset($vbseo_gcache[VBSEO_PIC_STORAGE][$arow[VBSEO_PICID_URI]]);
unset($vbseo_gcache['groupsdis'][$arow['discussionid']]);
return $url;
}
function vbseo_group_url($urlformat, $apars = array())
{
global $vbseo_gcache;
$replace = array();
if($apars[VBSEO_PICID_URI])
{
$pic = $vbseo_gcache[VBSEO_PIC_STORAGE][$apars[VBSEO_PICID_URI]];
$replace['%picture_title%'] = vbseo_filter_text($pic['caption']);
$replace['%picture_id%'] = $pic[VBSEO_PICID_URI];
if(!$pic['extension'])
$pic['extension'] = preg_replace('#.*\.#', '', $pic['filename']);
$replace['%original_ext%'] = $pic['extension'];
if ($apars['thumb'])$replace['%picture_id%'] .= 't';
if(!$apars['groupid'])
$apars['groupid'] = vbseo_attachment_contentid($pic);
if(!$apars['groupid'])
return '';
}
$groupid = $apars['groupid'] ;
if ($apars['discussionid'])
{
$gdinfo = &$vbseo_gcache['groupsdis'][$apars['discussionid']];
if(!$gdinfo && function_exists('verify_socialdiscussion'))
$vbseo_gcache['groupsdis'][$apars['discussionid']] = verify_socialdiscussion($apars['discussionid'], false);
if (!isset($gdinfo['seotitle']))
$gdinfo['seotitle'] = vbseo_filter_text($gdinfo['title'], false, true, true);
if(!$gdinfo['seotitle'])return '';
$replace['%discussion_id%'] = $gdinfo['discussionid'];
$replace['%discussion_name%'] = $gdinfo['seotitle'];
$groupid = $gdinfo['groupid'];
if(!$gdinfo['seotitle']||!$groupid)return '';
}
if ($groupid)
{
$ginfo = &$vbseo_gcache['groups'][$groupid];
if (!isset($ginfo['seotitle']))
$ginfo['seotitle'] = vbseo_filter_text($ginfo['name'], false, true, true);
if(!$ginfo['name'])return '';
$replace['%group_id%'] = $ginfo['groupid'];
$replace['%group_name%'] = $ginfo['seotitle'];
}
if ($apars['cat'])
{
$gcinfo = &$vbseo_gcache['groupscat'][$apars['cat']];
if (!isset($gcinfo['seotitle']))
$gcinfo['seotitle'] = vbseo_filter_text($gcinfo['title'], false, true, true);
if(!$gcinfo['seotitle'])return '';
$replace['%cat_id%'] = $gcinfo['categoryid'];
$replace['%cat_name%'] = $gcinfo['seotitle'];
}
if ($apars['page'])
{
$replace['%page%'] = $apars['page'];
}
$returl = str_replace(array_keys($replace), $replace, $urlformat);
return $returl;
}
function vbseo_cms_url($url_route, $type = '', $force_retrieve = false, $apars = array())
{
if(!$url_route && $apars['page'])
{
global $vbulletin;
$url_route = $vbulletin->options['default_page'];
}
preg_match('#^(list/)?(category|content|author|section)?/?(\d+)-?(.+?)?(?:/(?:view/)?(\d+))?$#', $url_route, $rm);
$islist = $rm[1];
if(!$type)
$type = $rm[2] ? $rm[2] : ($islist ? 'category' : '');
if(!$rm)
{
if(!$url_route || ($url_route == 'content'))
{
$type = $rm = 'home';
}
}
if(preg_match('#/(edit|addcontent)$#', $url_route))
return;
if(!$type || !$rm) return '';
$cmsformats = array(
'category' => VBSEO_URL_CMS_CATEGORY,
'content'  => VBSEO_URL_CMS_ENTRY,
'home'     => VBSEO_URL_CMS_HOME,
);
return vbseo_cms_url_type($cmsformats[$type], $type, array($rm[2], $rm[3], $rm[4], $rm[5]) , $force_retrieve, $apars);
}
function vbseo_cms_title($_c)
{
return $_c['url'] ? $_c['url'] : $_c['title'];
}
function vbseo_cms_url_type($urlformat, $type, $rm = array(), $force_retrieve = false, $apars = array())
{
global $vbseo_gcache;
$replace = array();
$o_type = $type;
list($c_type, $c_id, $c_ttl, $c_page)  = $rm;
if(!$c_page && $apars['page'])$c_page = $apars['page'];
if($c_page && ($c_page>1))
{
$replace['%page%'] = $c_page;
}else
$c_page = 0;
if(($o_type[1] == 'author') || ($c_type == 'author'))
{
$replace['%user_id%'] = $c_id;
if($_t = $vbseo_gcache['user'][$c_id]['username'])
$c_ttl = $_t;
if(!$c_ttl)return '';
$replace['%user_name%'] = vbseo_filter_text($c_ttl);
$urlformat = $c_page ? VBSEO_URL_CMS_AUTHOR_PAGE : VBSEO_URL_CMS_AUTHOR;
}
if($o_type == 'category')
{
if($c_type == 'section')
{
$o_type = 'content';
}else
{
$replace['%category_id%'] = $c_id;
if($_t = $vbseo_gcache['cms_cat'][$c_id]['category'])
$c_ttl = $_t;
if(!$c_ttl)return '';
$replace['%category_title%'] = vbseo_filter_text($c_ttl);
if($c_page)
$urlformat = VBSEO_URL_CMS_CATEGORY_PAGE;
}
}
if(($o_type == '') || ($o_type == 'content') || ($o_type == 'section'))
{
if($force_retrieve && !$vbseo_gcache['cmscont'][$c_id])
{
$GLOBALS['found_object_ids']['cmscont'][] = $c_id;
vbseo_get_object_info('cmscont');
}   
if($_c = $vbseo_gcache['cmscont'][$c_id])
$c_ttl = vbseo_cms_title($_c);
if(!$c_ttl)
return '';
if(vbseo_content_type($_c) == 'cms_section')
{
$replace['%section_id%'] = $c_id;
$replace['%section_title%'] = vbseo_filter_text($c_ttl);
$urlformat = $c_page ? VBSEO_URL_CMS_SECTION_PAGE : VBSEO_URL_CMS_SECTION;
if($_c['contenttypeid'] && !$_c['parentnode'] && !$c_page)
$urlformat = VBSEO_URL_CMS_HOME;
if($type == 'category' && !$c_page)
$urlformat = VBSEO_URL_CMS_SECTION_LIST;
}else
{
$replace['%entry_id%'] = $c_id;
$replace['%entry_title%'] = vbseo_filter_text($c_ttl);
if($_c['parentnode'])
{
$replace['%section_id%'] = $_c['parentnode'];
if(!$vbseo_gcache['cmscont'][$_c['parentnode']] && strstr($urlformat,'%section_title%'))
{
$GLOBALS['found_object_ids']['cmscont'] = array($_c['parentnode']);
vbseo_get_object_info('cmscont');
}
if($_c2 = $vbseo_gcache['cmscont'][$_c['parentnode']])
{
$replace['%section_title%'] = vbseo_filter_text(vbseo_cms_title($_c2));
}
}
if($c_page)
$urlformat = VBSEO_URL_CMS_ENTRY_PAGE;
if($apars['page'])
{
$replace['%page%'] = $apars['page'];
$urlformat = VBSEO_URL_CMS_ENTRY_COMPAGE;
}
}
}
if ($apars['page'])
{
$replace['%page%'] = $apars['page'];
}
$returl = str_replace(array_keys($replace), $replace, $urlformat);
if(preg_match('#\%[a-z]#',$returl))
$returl = '';
else
if (defined('VBSEO_URL_CMS_DOMAIN') && !strstr($returl,'://'))
$returl = VBSEO_URL_CMS_DOMAIN . $returl;
return $returl;
}
function vbseo_blog_url($urlformat, $apars = array())
{
global $vbseo_gcache;
$replace = array();
$userid = $apars['bloguserid'] ? $apars['bloguserid'] : $apars['u'];
$blogid = $apars['b'] ? $apars['b'] : $apars['blogid'];
$catid = $apars[VBSEO_BLOG_CATID_URI];
$attid = $apars['attachmentid'];
$comid = $apars['bt'];
if($apars['cp'])
{
$apars['page_id'] = intval($apars['cp']);
$userid = $vbseo_gcache['blogcp_ids'][$apars['page_id']]['userid'];
$replace['%page_title%'] = vbseo_filter_text($vbseo_gcache['blogcp_ids'][$apars['page_id']]['title']);
}
if($apars['tag'])
$replace['%tag%'] = vbseo_tag_filter_url($apars['tag']);
if ($comid)
{
$replace['%comment_id%'] = $comid;
if($urlformat == VBSEO_URL_BLOG_ENTRY)
{
vbseo_get_pagenum($postcount, $maxposts, $div);
}
}
if ($attid)
{
$replace['%original_filename%'] = vbseo_filter_text($vbseo_gcache['battach'][$attid]['filename'], '.');
$blogid = vbseo_attachment_contentid($vbseo_gcache['battach'][$attid]);
if(!$blogid)
return '';
if ($apars['d'])$attid .= 'd' . $apars['d'];
if ($apars['thumb'])$attid .= 't';
$replace['%attachment_id%'] = $attid;
}
if ($blogid)
{
$bloginfo = &$vbseo_gcache['blog'][$blogid];
if (!isset($bloginfo['seotitle']))
$bloginfo['seotitle'] = vbseo_filter_replace_text($bloginfo['title']);
$userid = $bloginfo['userid'];
$replace['%blog_id%'] = $bloginfo['blogid'];
$replace['%blog_title%'] = $bloginfo['seotitle'];
}
if ($catid)
{
if ($catid == -1)
$catinfo = array('blogcategoryid' => 0, 'title' => VBSEO_BLOG_CAT_UNDEF);
else
$catinfo = &$vbseo_gcache['blogcat'][$catid];
if (!isset($catinfo['seotitle']))
{
$catinfo['seotitle'] = vbseo_filter_text($catinfo['title'], '', false);
vbseo_append_a($catinfo['seotitle']);
}
$replace['%category_id%'] = $catinfo['blogcategoryid'];
$replace['%category_title%'] = $catinfo['seotitle'];
}
if ($userid)
{
$tmpuser = &$vbseo_gcache['user'][$userid];
$tmpuser['userid'] = $userid;
if (!$tmpuser['username'])
$tmpuser['username'] = $bloginfo['username'];
if (!isset($tmpuser['seoname']))
$tmpuser['seoname'] =
vbseo_filter_text($tmpuser['username'], null, false, true);
$replace['%user_id%'] = $tmpuser['userid'];
$replace['%user_name%'] = $tmpuser['seoname'];
}
if ($apars['y'])
{
$replace['%year%'] = $apars['y'];
$replace['%month%'] = $apars['m'];
$replace['%day%'] = $apars['d'];
}
if ($apars['page_id'])
{
$replace['%page_id%'] = $apars['page_id'];
}
if ($apars['page'])
{
$replace['%page%'] = $apars['page'];
}
$returl = str_replace(array_keys($replace), $replace, $urlformat);
if (VBSEO_URL_BLOG_DOMAIN && !strstr($returl,'://'))
$returl = VBSEO_URL_BLOG_DOMAIN . $returl;
return $returl;
}
function vbseo_generic_url($urlformat, $apars)
{
$replace = array();
if($apars['page'])
$replace['%page%'] = $apars['page'];
$ret = str_replace(array_keys($replace), $replace, $urlformat);
return $ret;
}
function vbseo_any_url($url)
{
$re_url = vbseo_replace_urls('', $url);
return $re_url;
}
function vbseo_make_url($url)
{
$re_url = vbseo_replace_urls('', $url);
return $re_url;
}
function vbseo_reverse_username($username)
{
$db = vbseo_get_db();
$usrname_prep = VBSEO_REWRITE_MEMBER_MORECHARS ? $username : str_replace(VBSEO_SPACER, ' ', $username);
$queryId = $db->vbseodb_query($q = 'select userid from ' . vbseo_tbl_prefix('user') . ' where' . ' username like "' . $db->vbseodb_escape_like($usrname_prep) . '" limit 1');
$user = $db->vbseodb_fetch_array($queryId);
if (!$user)
{
$username2 = preg_quote(vbseo_unfilter_text(htmlspecialchars($username)));
$queryId = $db->vbseodb_query($q = 'select userid from ' . vbseo_tbl_prefix('user') . ' where username regexp "' . ($username2) . '" limit 1');
$user = $db->vbseodb_fetch_array($queryId);
}
$db->vbseodb_free_result($queryId);
return $user['userid'];
}
function vbseo_reverse_object($otype, $title, $linkedid = 0)
{
$whr = $fld = $tbl = $ttl2 = $ttl3 = '';
$ttl_app = false;
$ttl_unfilter = true;
$db = vbseo_get_db();
switch($otype)
{
case 'blogcat':
if ($title == VBSEO_BLOG_CAT_UNDEF)
return 0;
$fld = 'blogcategoryid';
$tbl = 'blog_category';
$whr = '(userid = "'.intval($linkedid).'" or userid=0)  AND title';
$ttl_app = true;
break;
case 'thread':
$fld = 'threadid';
$tbl = 'thread';
$whr = ($linkedid?'forumid = '.intval($linkedid).' AND ':'').'title';
$ttl_app = true;
$ttl_unfilter = false;
break;
case 'album':
$fld = 'albumid';
$tbl = 'album';
$whr = 'userid = "'.intval($linkedid).'"  AND title';
$ttl_app = true;
break;
case 'cmsnode':
$fld = 'ni.nodeid';
$tbl = 'cms_nodeinfo ni left join '.vbseo_tbl_prefix('cms_node').' n on ni.nodeid=n.nodeid';
$whr = 'if(url!="",url,title)';
$ttl_app = true;
break;
case 'group':
$fld = 'groupid';
$tbl = 'socialgroup';
$whr = 'name';
break;
case 'groupcat':
$fld = 'socialgroupcategoryid';
$tbl = 'socialgroupcategory';
$whr = 'title';
$ttl_app = true;
break;
case 'tag':
$fld = 'tagtext';
$tbl = 'tag';
$whr = 'tagtext';
break;
}
if($ttl_app)
$title = preg_replace('#-a$#', '', $title);
$preq = 'select '.$fld.' as iid from  ' . vbseo_tbl_prefix($tbl) . ' where '.$whr.' ';
$queryId = $db->vbseodb_query($q = $preq.' like "' . $db->vbseodb_escape_like(str_replace(VBSEO_SPACER, ' ', $title)) . '" limit 1');
$mg = $db->vbseodb_fetch_array($queryId);
if (!$mg)
{
if($ttl_unfilter)
{
$ttl2 = vbseo_unfilter_text(preg_quote(htmlspecialchars(VBSEO_SPACER.str_replace(' ', VBSEO_SPACER, $title).VBSEO_SPACER)));
$ttl3 = vbseo_unfilter_text(preg_quote(htmlspecialchars(VBSEO_SPACER.str_replace(' ', VBSEO_SPACER, $title).VBSEO_SPACER)), true);
}
else
$ttl2 = '%'.str_replace(VBSEO_SPACER, '%', $db->vbseodb_escape_like($title)).'%';
$queryId = $db->vbseodb_query($q=$preq.
($ttl_unfilter ? 'regexp' : 'like' ).
' "' . $ttl2 . '" order by length('.$whr.') limit 1');
if($queryId)
$mg = $db->vbseodb_fetch_array($queryId);
if(!$mg && $ttl3)
{
$queryId = $db->vbseodb_query($q=$preq.' regexp "' . $ttl3 . '" order by length('.$whr.') limit 1');
if($queryId)
$mg = $db->vbseodb_fetch_array($queryId);
}
}
$db->vbseodb_free_result($queryId);
return $mg['iid'];
}
function vbseo_reverse_formats()
{
if (!defined('VBSEO_FIND_T_FORMAT'))
{
$replace = array('#%thread_id%#' => '(\d+)',
'#%thread_page%#' => '\d+',
'#%post_id%#' => '(\d+)',
'#%post_count%#' => '\d+',
'#%[a-z_]+_id%#' => '\d+',
'#%[a-z_]+_path%#' => '.+',
'#%[a-z_]+%#' => '[^/]+'
);
define('VBSEO_FIND_T_FORMAT', preg_replace(array_keys($replace), $replace, preg_quote(VBSEO_URL_THREAD, '#')));
define('VBSEO_FIND_MT_FORMAT', preg_replace(array_keys($replace), $replace, preg_quote(VBSEO_URL_THREAD_PAGENUM, '#')));
define('VBSEO_FIND_P_FORMAT', preg_replace(array_keys($replace), $replace, preg_quote(VBSEO_URL_POST_SHOW, '#')));
define('VBSEO_FIND_F_FORMAT', preg_replace(array_keys($replace), $replace, preg_quote(VBSEO_URL_FORUM, '#')));
}
}
?>