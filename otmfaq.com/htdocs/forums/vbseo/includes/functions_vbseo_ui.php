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

define(VBSEO_UI_THREAD, 1);
define(VBSEO_UI_BLOG,   2);
define(VBSEO_UI_CMS,    3);
define(VBSEO_UI_CID_TOP,-1);
class vBSEO_UI
{
private static $ginfo    = array();
private static $page_ids = array();
private static $shortlist= 3;
private static $toplist= 10;
private static $longlist = 100;
private static $default_top_tree = true;
public static function head_hook()
{
$container_class = "'postbody','blogbit','content','postcontainer','vbseo_like_postbit'";
$vbseoui_options = '"'.addslashes(VBSEO_VB_EXT).'",'.(VBSEO_LIKE_LINK_VISIBLE ? 1 : 0);
eval(vbseo_eval_template('vbseo_ui_headinc', '$ls_js'));
if(THIS_SCRIPT=='vbcms' || !VBSEO_VB4)
vbseo_insert_code($ls_js, 'head_end');
else
{
global $headinclude_bottom;
$headinclude_bottom.=$ls_js;
}
}
public static function parse_tpl_hook()
{
global $template_hook;
if(THIS_SCRIPT == 'member')
{
$menuitem = vbseo_fetch_tpl('vbseo_profile_menu');
if(VBSEO_VB4)
vbseo_modify_template('MEMBERINFO', 
'#(\<ul[^>]*"usermenu".*?)(</ul>)#s', '$1'.$menuitem.'$2');
else
vbseo_modify_template('memberinfo_block_ministats', 
'#(\[posts\]</dd>)#is', '$1'.$menuitem);
}
$template_hook['custom_css_list'] .= ($template_hook['custom_css_list'] ? "," : "").
"vbseo_buttons.css" . ((THIS_SCRIPT == 'showthread') ? ",vbseo_buttons_fix.css" : "");
$tplpostbit = vbseo_get_postbit_tpl();
vbseo_modify_template($tplpostbit, $sf ='|| $post[\'signature\']', $sf . ' || 1');
if(!VBSEO_VB4)
vbseo_modify_template($tplpostbit, $sf='class=\"tborder', $sf . ' vbseo_like_postbit');
}
public static function postbit_hook(&$post, &$template_hook)
{
global $show, $thread, $vboptions, $vbseo_gcache; 
$is_public = vbseo_forum_is_public($GLOBALS['forum'], $GLOBALS['foruminfo'], false, true);
if (THIS_SCRIPT != 'showthread')
return false;
if($fi = $GLOBALS['forum'])
if(!$fi['vbseo_enable_likes'])
return;
if ($is_public && VBSEO_BOOKMARK_POST && ($bmlist = vbseo_get_bookmarks()))
{
$book_t = urlencode($thread['title']);
$vbseo_url_t = urlencode($vboptions['bburl2'] . '/' . 
vbseo_thread_url($thread['threadid'], $_GET['page']) . '#post') . $post['postid'];
$bookmarks = array();
foreach($bmlist as $bm)
{
$url = str_replace('%url%', $vbseo_url_t, str_replace('%title%', $book_t, str_replace('&amp;', '&', $bm[0])));
$bookmarks[] = array(
'url' => $url,
'image' => $bm[1],
'text'  => $bm[3]
);
}
}else $bookmarks = '';
$vbseo_likeshare = self::likeshare_bit(VBSEO_UI_THREAD, 
$post['threadid'], $post['postid'], $post['userid'], $bookmarks);
if($vbseo_likeshare)
{
vbseo_modify_template34('SHOWTHREAD', '#('.$sfor.')#s', '$1'.$liketree,
0, '<!--VBSEO_LIKE_TREE-->');
$template_hook['postbit_signature_start'] .= $vbseo_likeshare;
}
}
public static function thread_hook()
{
global $thread, $vbseo_gcache;
if($fi = $GLOBALS['forum'])
if(!$fi['vbseo_enable_likes'])
return;
$tpage = count(self::$ginfo[VBSEO_UI_THREAD][$thread['threadid']]);
if(self::$default_top_tree && (vbseo_page_size(true) <= $thread['replycount']))
{
self::$ginfo = array();
}
$liketree = self::liketree_bit(VBSEO_UI_THREAD, 
$thread['threadid'], 
$thread['vbseo_likes'],
$tpage
);
if($liketree)
{
$sfor = VBSEO_VB4 ?
'<div id="pagetitle".*?>' :
'id=\\\\"poststop\\\\".*?</a>';
vbseo_modify_template34('SHOWTHREAD', '#('.$sfor.')#s', '$1'.$liketree,
0, '<!--VBSEO_LIKE_TREE-->');
}
}
public static function liketree_bit($ctype, $cgroup, $like_total, $like_page = 0)
{
global $vbseo_gcache, $vbphrase;
$alikes = array();
$agr = self::lcache_get($ctype, $cgroup);
if(is_array($agr))
foreach($agr as $cid=>$gc)
{
$al = array();
if($ctype == VBSEO_UI_THREAD)
{
$al = $vbseo_gcache['post'][$cid];
if(!$al) 
$al = $vbseo_gcache['post'][$cid] = array(
'postid' => $cid, 
'threadid' => $cgroup
);
}
$al = array_merge($gc, $al);
$al['postno'] = $gc['preposts'];
$alikes[] = $al;
}
if($like_total)
{
self::prerender_likes($alikes);
$ret = vbseo_vbtemplate_render_any('vbseo_like_tree', array(
'page_ids' => implode(',',self::$page_ids),
'alikes' => $alikes,
'ctype' => $ctype,
'cgroup' => $cgroup,
'like_total' => $like_total,
'like_page' => $like_page)
);
}
return $ret.' ';
}
public static function blog_comment_hook($bresponse)
{
if(!is_object($bresponse) || !$bresponse->response )
return;
$vbseo_likeshare = self::likeshare_bit(VBSEO_UI_BLOG, 
$bresponse->response['blogid'], 
$bresponse->response['blogtextid'],
$bresponse->response['userid']
);
if($vbseo_likeshare)
vbseo_modify_template('blog_comment', '#(<div class="vbseo_buttons.*</div>\s*)?(<div class="commentfoot)#is', $vbseo_likeshare.'$2');
}
public static function blog_entry_hook(&$blog)
{
global $show, $vboptions; 
if (!in_array(THIS_SCRIPT, array('entry','blog')))
return false;
self::extract_likes_page(VBSEO_UI_BLOG, $blog['blogid'], -1);
if (VBSEO_BOOKMARK_BLOG)
{         
$vbseo_url_b = vbseo_http_s_url('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['VBSEO_URI']);
$book_t = urlencode($blog['title']);
$bmlist = vbseo_get_bookmarks();
$bookmarks = array();
foreach($bmlist as $bm)
{
$url = str_replace('%url%', urlencode($vbseo_url_b), str_replace('%title%', $book_t, $bm[0]));
$bookmarks[] = array(
'url' => $url,
'image' => $bm[1],
'text'  => $bm[4]
);
}
}
$vbseo_likeshare = self::likeshare_bit(VBSEO_UI_BLOG, 
$blog['blogid'], $blog['firstblogtextid'], $blog['userid'],
$bookmarks);
if($vbseo_likeshare)
{
vbseo_modify_template34('blog_show_entry', '#(<div id=\\\\?"(?:entry_text|blog_message).*?</div>)#is', 
'$1'.$vbseo_likeshare, 0, '<!--VBSEO_LIKE_SHARE-->');
if(!VBSEO_VB4)
vbseo_modify_template34('blog_show_entry', '#(<div class=\\\\")([^>]*?id=\\\\?"entry)#is', 
'$01vbseo_like_postbit $2');
}
}
public static function cms_hook(&$view)
{
global $show, $vboptions; 
if(!is_object($view))return;
$cid = $view->node;
self::extract_likes_page(VBSEO_UI_CMS, $cid, VBSEO_UI_CID_TOP);
if (VBSEO_BOOKMARK_CMS)
{         
$vbseo_url_b = vbseo_http_s_url('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['VBSEO_URI']);
$book_t = urlencode($view->title);
$bmlist = vbseo_get_bookmarks();
$bookmarks = array();
foreach($bmlist as $bm)
{
$url = str_replace('%url%', urlencode($vbseo_url_b), str_replace('%title%', $book_t, $bm[0]));
$bookmarks[] = array(
'url' => $url,
'image' => $bm[1],
'text'  => $bm[4]
);
}
}
$vbseo_likeshare = self::likeshare_bit(VBSEO_UI_CMS, $cid, VBSEO_UI_CID_TOP, $view->userid, $bookmarks);
if($vbseo_likeshare)
$view->pagetext .= $vbseo_likeshare;
}
public static function likeshare_bit($contenttype, $contentgroup, $contentid, $duserid, $bookmarks = array())
{
global $show, $vboptions, $vbphrase;
if(!$contenttype || !$contentgroup || !$contentid)
return '';
self::$page_ids[] = $contentid;
$vbseo_liked = self::get_liked_info($contenttype, $contentgroup, $contentid);
$own_liked   = self::lcache_get($contenttype, $contentgroup, $contentid, 'ownlike');
$own_content = ($duserid == vbseo_vb_userid());
$like_link_visible = VBSEO_LIKE_LINK_VISIBLE;
eval(vbseo_eval_template('vbseo_likeshare', '$vbseo_likeshare'));
return $vbseo_likeshare;
}
public static function ajax_hook()
{
global $vbulletin, $vbseo_gcache;
$response = array();
$cid = intval($_POST['contentid']);
$ctype = intval($_POST['contenttype']);
$cgroup = intval($_POST['contentgroup']);
$cduser = intval($_POST['duserid']);
if($ctype == VBSEO_UI_THREAD)
{
$pinfo = vbseo_get_post_info($cid);
if($pinfo)
{
$cgroup = $pinfo['threadid'];
$cduser = $pinfo['userid'];
}
$tinfos= vbseo_get_thread_info($cgroup);
$flist = vbseo_allowed_forums();
if(!in_array($tinfos[$cgroup]['forumid'], $flist))
{
$err = 'Access denied';
}
}else
if($ctype == VBSEO_UI_BLOG)
{
$pinfo = vbseo_get_blog_info($cid, true, true);
if($pinfo)
{
$cgroup = $pinfo['blogid'];
$cduser = $pinfo['userid'];
if(!vbseo_allowed_blog($pinfo))
{
$err = 'Access denied';
}
}else
$err = 'Content not found';
}else
if($ctype == VBSEO_UI_CMS)
{
vbseo_get_object_info('cmscont', array($cgroup));
$pinfo = $vbseo_gcache['cmscont'][$cgroup];
if($pinfo['tyid'])$pinfo['nodeid'] = $pinfo['tyid'];
if($pinfo)
{
$cduser = $pinfo['userid'];
$cid = VBSEO_UI_CID_TOP;
if(!vbseo_allowed_cms($pinfo))
{
$err = 'Access denied';
}
}else
$err = 'Content not found';
}else
{
$err = 'Unrecognized content type';
}
if(!$err)
switch($act = $_POST['action'])
{
case 'like':
case 'others':
if(!vbseo_vb_userid())
$err = 'Not logged in';
if(!$pinfo)
$err = 'Content not found';
if(!$err)
{
$li = self::get_like($cid, $cgroup, $ctype);
if($act == 'like')
{
if($cduser == vbseo_vb_userid())
$err = 'Access denied';
else
{
if($li)
$res = self::remove_like($cid, $ctype, $cgroup, 0, $cduser);
else
$res = self::add_like($cid, $ctype, $cgroup, 0, $cduser);
if(!$res)
$err = 'Error processing request';
$li = !$li;
}
}else
if($act == 'others')
{
self::$shortlist = 200;
}
global $vbphrase;
$response['contentid'] = $cid;
$response['contenttype'] = $ctype;
$response['contentgroup'] = $cgroup;
$response['self'] = $vbphrase[$li ? 'vbseo_unlike' : 'vbseo_like'];
self::extract_likes_page($ctype, $cgroup, $cid);
$ll = self::get_liked_info($ctype, $cgroup, $cid);
$response['likelist'] = $ll ? $ll : '.';
}
break;
case 'treetab':
$atabs = array('top', 'all', 'page');
$tt = $atabs[$_POST['tab']];
if($tt)
{
self::$page_ids = explode(',', $_POST['cids']) ;
vbseo_int_var(self::$page_ids);
$cids = ($tt == 'page') ? self::$page_ids : array();
self::extract_likes_bygroup($ctype, $cgroup, 0, $cids, $tt);
$liketree = self::liketree_bit($ctype,  $cgroup, $tinfos[$cgroup]['vbseo_likes']);
$liketree = preg_replace('#^.*class="vbseo-likes-list.*?>(.*?)</ul>.*$#is', '$1', $liketree);
$response['ltree'] = $liketree;
}
break;
}
if($err)
$response['error'] = $err;
$xml = new vB_AJAX_XML_Builder($vbulletin, 'text/xml');
$xml->add_group('vbseo');
foreach($response as $k=>$v)
{
$xml->add_tag($k, $v);
}
$xml->close_group();
$xml->print_xml();
exit;
}
private static function get_like($cid, $cgroup, $ctype, $userid = 0)
{
if(!$userid && (!$userid = vbseo_vb_userid()))
return false;
$db = vbseo_get_db();
$larr = $db->vbseodb_query_first("
SELECT *
FROM " . vbseo_tbl_prefix('vbseo_likes') . " 
WHERE l_contentid = " . intval($cid) . " AND l_cgroup = ".intval($cgroup)." AND l_ctype = " . intval($ctype) . " 
AND l_from_userid = ".intval($userid)."
LIMIT 1
");
return $larr;
}
public static function delete_likes($cid, $cgroup, $ctype)
{
if(!$cgroup && !$cid)return 0;
$whr = $cid ? "l_contentid=".intval($cid) : "l_cgroup=".intval($cgroup);
$whr.= " AND l_ctype=".intval($ctype);
$db = vbseo_get_db();
$gq = $db->vbseodb_query("SELECT * FROM " . vbseo_tbl_prefix('vbseo_likes') . " WHERE  $whr");
$lrems = array();
while($li = $db->vbseodb_fetch_assoc($gq))
{
$lrems[$li['l_cgroup']]++;
self::like_counter_user($li['l_from_userid'], $li['l_dest_userid'], -1);
}
$db->vbseodb_query("DELETE FROM " . vbseo_tbl_prefix('vbseo_likes') . " WHERE  $whr");
foreach($lrems as $gid=>$lrem)
self::like_counter_type($ctype, $gid, -$lrem);
return true;
}
public static function move_likes($cgroup, $destgroup, $ctype)
{
if(!$cgroup||!$destgroup)return false;
if(!is_array($cgroup))
$cgroup=array($cgroup);
$db = vbseo_get_db();
$db->vbseodb_query("
UPDATE " . vbseo_tbl_prefix('vbseo_likes') . " 
SET l_cgroup = ". intval($destgroup)."
WHERE  l_ctype = " . intval($ctype) . " 
AND l_cgroup IN (" . implode(',',$cgroup) . " )
");
$dn = $db->vbseo_affected_rows();
self::like_counter_type($ctype, $destgroup, $dn);
return true;
}
private static function remove_like($cid, $ctype, $groupid = 0, $userid = 0, $duserid = 0)
{
if(!$userid && (!$userid = vbseo_vb_userid()))
return false;
$db = vbseo_get_db();
$db->vbseodb_query("
DELETE FROM " . vbseo_tbl_prefix('vbseo_likes') . " 
WHERE  l_ctype = " . intval($ctype) . " 
AND l_cgroup = " . intval($groupid) . " 
AND l_contentid = " . intval($cid) . " 
AND l_from_userid = ".intval($userid)."
");
$dn = $db->vbseo_affected_rows();
self::like_counter_type($ctype, $groupid, -$dn);
self::like_counter_user($userid, $duserid, -$dn);
return true;
}
public static function add_like($cid, $ctype, $groupid = 0, $userid = 0, $duserid = 0, $dateline = 0, $from_username = '')
{
if(!$userid)
{ 	
$userid = vbseo_vb_userid();
$from_username = vbseo_vb_userinfo('username');
}
if(!$userid || !$from_username)
return false;
if(!$dateline)$dateline = time();
$db = vbseo_get_db();
$db->vbseodb_query($q="
INSERT INTO " . vbseo_tbl_prefix('vbseo_likes') . " 
SET l_contentid = " . intval($cid) . ",
l_ctype = " . intval($ctype) . ",
l_cgroup = " . intval($groupid) . ",
l_from_userid = " . intval($userid) . ",
l_from_username= '" . vbseo_db_escape($from_username) . "',
l_dest_userid = " . intval($duserid) . ",
l_dateline = " . intval($dateline) . "
");
self::like_counter_user($userid, $duserid, 1);
self::like_counter_type($ctype, $groupid, 1);
return true;
}
public static function like_counter($userid, $l_in, $l_out)
{
if($l_in)
{
self::like_counter_spec($userid, 'vbseo_likes_in', $l_in);
if($l_in>0)
self::like_counter_spec($userid, 'vbseo_likes_unread', $l_in);
}
if($l_out)
{
self::like_counter_spec($userid, 'vbseo_likes_out', $l_out);
}
return true;
}
public static function like_counter_user($userid, $duserid, $dn)
{
self::like_counter($userid, 0, $dn);
self::like_counter($duserid, $dn, 0);
}
public static function like_counter_spec($userid, $field, $lnum)
{
if(!$userid || !$lnum)
return false;
$sets = ($lnum>0) ? "$field=$field+$lnum" : 
"$field=if($field<".abs($lnum).", 0, $field".$lnum.")";
$db = vbseo_get_db();
$db->vbseodb_query("UPDATE " . vbseo_tbl_prefix('user') . 
" SET $sets WHERE userid=".intval($userid));
return true;
}
public static function like_counter_type($ctype, $cgroup, $lnum)
{
$uptbl = array(VBSEO_UI_THREAD => 'thread', VBSEO_UI_BLOG => 'blog', VBSEO_UI_CMS => 'cms_nodeinfo');
$upfld = array(VBSEO_UI_THREAD => 'threadid', VBSEO_UI_BLOG => 'blogid', VBSEO_UI_CMS => 'nodeid');
$t = $uptbl[$ctype];
$f = $upfld[$ctype];
if(!$t || !$f)return false;
$field= 'vbseo_likes';
$sets = ($lnum>0) ? "$field=$field+$lnum" : 
"$field=if($field<".abs($lnum).", 0, $field".$lnum.")";
$db = vbseo_get_db();
$db->vbseodb_query($q="UPDATE " . vbseo_tbl_prefix($t) . " SET $sets WHERE $f=".intval($cgroup));
return true;
}
public static function lcache_set_a($li, $val, $key = null)
{
self::lcache_set($li['l_ctype'], $li['l_cgroup'], $li['l_contentid'], $val, $key);
}
public static function lcache_get_a($li, $key = null)
{
return self::lcache_get($li['l_ctype'], $li['l_cgroup'], $li['l_contentid'], $key);
}
public static function lcache_set($ctype, $cgroup, $contentid, $val, $key = null)
{
$ret = self::lcache_get($ctype, $cgroup, $contentid);
if($key)
$ret[$key] = $val;
else
foreach($val as $k=>$v)
$ret[$k] = $v;
self::$ginfo[$ctype][$cgroup][$contentid] = $ret;
}
public static function lcache_get($ctype, $cgroup, $contentid = null, $key = null)
{
$gi = $contentid ? self::$ginfo[$ctype][$cgroup][$contentid] : self::$ginfo[$ctype][$cgroup];
return $key ? $gi[$key] : $gi;
}
public static function get_liked_info($ctype, $cgroup, $contentid)
{
global $vbphrase;
$like_count  = self::lcache_get($ctype, $cgroup, $contentid, 'count' );
$ret = '';
if($like_count)
{
$ol = self::lcache_get($ctype, $cgroup, $contentid, 'ownlike');
$alikes = self::lcache_get($ctype, $cgroup, $contentid, 'likes');
$ptype= $ol ? 'you' : 'like';
$args = array();
$unum = $un = $ol ? 1 : 0;
for($x = 0; ($unum < self::$shortlist) && ($x<self::$shortlist); $x++)
{
$li = $alikes[$x];
if($li['l_from_userid'] == vbseo_vb_userid())
continue;
if($li)
{
$l = VBSEO_REWRITE_MEMBERS ? 
vbseo_member_url($li['l_from_userid'])
: 'member.'.VBSEO_VB_EXT.'?u='.$li['l_from_userid'];
if($unum<3)
{
$unum++;
$args[] = $l;
$args[] = $li['l_from_username'];
}else
{
$linkmore[] = '<a href="'.$l.'">'.$li['l_from_username'].'<a>';
}
}
}
if(($rest = $like_count - $unum)>0)
{
$args[] = $rest;
$unum++;
}
array_unshift($args, $vbphrase[$p='vbseo_'.$ptype.'_'.$unum]);
$ret = VBSEO_VB4 ? construct_phrase($args) : @call_user_func_array('construct_phrase', $args);
if($linkmore)
$ret .= '<br />'.implode(', ', $linkmore);
}
return $ret;
}
public static function extract_likes_page($ctype, $groupid, $contentids)
{
vbseo_int_var($ctype);
vbseo_int_var($groupid);
if(!$ctype || !$groupid || !$contentids) 
return false;
if(!is_array($contentids))
$contentids = (($contentids == -1) ? array() : 
(strstr($contentids,',') ? explode(',',$contentids) : array($contentids))
);
vbseo_int_var($contentids);
global $vbseo_gcache;
$userid = vbseo_vb_userid();
$contentids = self::extract_likes_bygroup($ctype, $groupid, $userid, $contentids);
if(!$contentids)
return;
$clist = array();
$db = vbseo_get_db();
$gq = $db->vbseodb_query($q="
SELECT * FROM (
SELECT @rn := if( @lc = l.l_contentid, @rn +1, 0 ) AS rnum, @lc := l_contentid, l.* 
FROM " . vbseo_tbl_prefix('vbseo_likes') . " l, (SELECT @rn :=0, @lc :=0) r
WHERE l_ctype = ".$ctype." AND l_cgroup =".$groupid." AND l_contentid IN (" . implode(',', $contentids) . " )
ORDER BY l_contentid
) as nest
where rnum<".self::$shortlist."
");
while($li = $db->vbseodb_fetch_assoc($gq))
{
$al = self::lcache_get_a($li, 'likes');
$al[] = $li;
self::lcache_set_a($li, $al, 'likes');
$vbseo_gcache['user'][$li['l_from_userid']] = array(
'userid' => $li['l_from_userid'],
'username'=>$li['l_from_username']
);
}
$db->vbseodb_free_result($gq);
return ;
}
public static function extract_likes_bygroup($ctype, $groupid, $userid = 0, $contentids = array(), $tab = '')
{
vbseo_int_var($ctype);
vbseo_int_var($groupd);
if($tab && ($ctype!=VBSEO_UI_THREAD)) return;
if(!$groupid)return;
global $vbseo_gcache;
$clist = array();
$db = vbseo_get_db();
$q = "
SELECT l_ctype,l_cgroup,l_contentid,l_dest_userid,count(*) as cnt
".($userid ? ",IF(l_from_userid=".intval($userid).",1,0) as ownlike" : "")."
FROM " . vbseo_tbl_prefix('vbseo_likes') . " 
WHERE l_ctype = ".$ctype." AND l_cgroup = " . $groupid . 
($contentids ? " AND l_contentid IN (".implode(',', $contentids).")" : "")."
GROUP BY l_contentid".($userid ? ", ownlike" : "").
(($tab=='top') ? " ORDER by cnt desc" : "").
" LIMIT 0,".(($tab=='top') ? self::$toplist : self::$longlist);
if($tab)
{
$join = array("LEFT JOIN " . vbseo_tbl_prefix('user') . " u1 on u1.userid = l_dest_userid");
$whr = array("p.postid is not null");
$fields = array();
self::get_likes_query($fields, $join, $whr);
$q = "SELECT nest.*,".implode(',',$fields).
" FROM ($q) as nest ".
implode("\n", $join).
($whr ? " WHERE ".implode(" AND ", $whr):"").
" ORDER BY l_contentid";
}
$gq = $db->vbseodb_query($q);
while($li = $db->vbseodb_fetch_assoc($gq))
{
$clist[] = $li['l_contentid'];
self::lcache_set_a($li, $li);
self::lcache_set_a($li, self::lcache_get_a($li,'count')+$li['cnt'], 'count');
if($li['ownlike'])
self::lcache_set_a($li, true, 'ownlike');
}
$db->vbseodb_free_result($gq);
return $clist;
}
public static function get_likes_query(&$fields, &$join, &$whr)
{
global $vbulletin;
$fields[] = "u1.username , u1.userid, u1.usergroupid ";
if($vbulletin->options['avatarenabled'])
{
$join[] = "LEFT JOIN " . vbseo_tbl_prefix('avatar')." AS avatar ON(avatar.avatarid = u1.avatarid) ";
$join[] = "LEFT JOIN " . vbseo_tbl_prefix('customavatar')." AS customavatar ON(customavatar.userid = u1.userid)";
$join[] = "LEFT JOIN " . vbseo_tbl_prefix('post') . " p on l_ctype = " . VBSEO_UI_THREAD . " AND p.postid = l_contentid ".
(can_moderate(0, 'canmoderateposts') ? "" : " AND p.visible=1 ");
$join[] = "LEFT JOIN " . vbseo_tbl_prefix('thread') . " t on t.threadid = p.threadid";
$fields[] = "p.postid, if(p.title, p.title, t.title) AS posttitle, p.pagetext as posttext, p.dateline AS postdateline";
$fields[] = "t.threadid, t.title AS threadtitle, t.forumid";
$fields[] = "u1.avatarid, u1.avatarrevision, avatar.avatarpath, NOT ISNULL(customavatar.userid) AS hascustomavatar, customavatar.dateline AS avatardateline, NOT ISNULL(customavatar.userid) AS hascustom";
}
}
public static function get_likes_detailed($userid, $duserid, $start = 0, $pgsz = 15, $contentids = array())
{
global $vbulletin;
if(!$flist = vbseo_allowed_forums())
return false;
$start = intval($start);
if($start<1)$start = 1;
$whr[] = "(l_ctype <> " . VBSEO_UI_THREAD . " OR (t.forumid in (".implode(',', $flist).")))";
if($userid)
$whr[] = '(l_from_userid= '.intval($userid).')';
if($duserid)
$whr[] = '(l_dest_userid = '.intval($duserid).')';
$join = array(
"LEFT JOIN " . vbseo_tbl_prefix('user') . " u1 on u1.userid = l.l_from_userid",
"LEFT JOIN " . vbseo_tbl_prefix('user') . " u2 on u2.userid = l.l_dest_userid"
);
$fields = array(
"u2.userid as to_userid, u2.username as to_username"
);
if($contentids)
$whr[] = "l_contentid IN (".implode(',', $contentids).")";
if(VBSEO_LIKE_BLOG && VBSEO_VB_BLOG)
{
$join[] = "LEFT JOIN " . vbseo_tbl_prefix('blog') . " b 
on l_ctype = " . VBSEO_UI_BLOG . " AND b.blogid = l.l_cgroup AND b.state='visible' ";
$join[] = "LEFT JOIN " . vbseo_tbl_prefix('blog_text') . " bt
on l_ctype = " . VBSEO_UI_BLOG . " AND bt.blogtextid = l.l_contentid AND bt.state='visible' ";
$fields[] = "b.title as blogtitle,b.firstblogtextid, bt.pagetext as blogtext, bt.dateline as blogdateline";
$whr[] = "(l_ctype <> " . VBSEO_UI_BLOG . " OR (bt.blogtextid is not null))";
}
if(VBSEO_LIKE_CMS && VBSEO_VB_CMS)
{
$cperm_str = vbseo_permissions_cms_str();
if($cperm_str)
{
$join[] = "LEFT JOIN " . vbseo_tbl_prefix('cms_node') . " node
on l_ctype = " . VBSEO_UI_CMS . " AND node.nodeid = l.l_cgroup AND node.setpublish=1 AND hidden=0 ";
$join[] = "LEFT JOIN " . vbseo_tbl_prefix('cms_nodeinfo') . " ni on ni.nodeid = node.nodeid  ";
$join[] = "LEFT JOIN " . vbseo_tbl_prefix('cms_article') . " ca on ca.contentid = node.contentid  ";
$fields[] = "ni.title as cmstitle, ca.pagetext as cmstext, node.publishdate as cmsdateline";
$whr[] = "(l_ctype <> " . VBSEO_UI_CMS . " OR (".$cperm_str."))";
}
}
self::get_likes_query($fields, $join, $whr);
$db = vbseo_get_db();
$gq = $db->vbseodb_query($q = "
SELECT SQL_CALC_FOUND_ROWS l.*," . implode(', ', $fields)."
FROM " . vbseo_tbl_prefix('vbseo_likes') . " l "
. implode("\n", $join) . "
WHERE ".implode(' AND ', $whr)."
ORDER BY l_dateline desc
LIMIT ".intval(($start-1) * $pgsz).",".intval($pgsz)."
");
$results = array();
while($li = $db->vbseodb_fetch_assoc($gq))
{
$results[] = $li;
}
$db->vbseodb_free_result($gq);
return array('results' => $results,'total' => $db->vbseo_get_found() );
}
public static function likes_block($blocktype, $perpage = 5)
{
$results = array();
switch($blocktype)
{
case 'topthread':
$db = vbseo_get_db();
if(!$flist = vbseo_allowed_forums())
return '';
$gq = $db->vbseodb_query($q = "
SELECT threadid,title,postuserid,postusername,vbseo_likes
FROM " . vbseo_tbl_prefix('thread') . "
WHERE forumid in (".implode(',', $flist).")
ORDER BY vbseo_likes desc
LIMIT 0,".intval($perpage));
$results = array();
while($li = $db->vbseodb_fetch_assoc($gq))
$likes[] = $li;
break;
case 'latest':
$linfo = self::get_likes_detailed(0, 0, 1, $perpage);
$likes = $linfo['results'];
self::prerender_likes($likes);
break;
}
eval(vbseo_eval_template('vbseo_likes_widget', '$lrender'));
return $lrender;
}
public static function get_like_url_byid($cid, $ctype )
{       
$like = array('l_ctype' => $ctype, 'l_contentid' => $cid);
self::get_like_typespec($like);
return $like['url'];
}
public static function get_like_typespec(&$like, $ctype = 0)
{       
global $vbphrase;
switch($like['l_ctype'])
{
case VBSEO_UI_THREAD:
if(in_array($like['l_contentid'], self::$page_ids))
{
$turl = preg_replace('#\#.*$#', '', $_POST['lurl'] ? $_POST['lurl'] : $_SERVER['VBSEO_URI']);
$like['url'] = $turl.'#post'.$like['l_contentid'];
}else
$like['url'] = 'showthread.'.VBSEO_VB_EXT.'?p='.$like['l_contentid'];
$like['ctype']= $vbphrase['vbseo_like_post'];
$like['gtype']= $vbphrase['vbseo_like_thread'];
$like['gtitle']= $like['threadtitle'];
$like['pagetext'] = $like['posttext'];
$like['dateline'] = $like['postdateline'];
break;
case VBSEO_UI_BLOG:
if($like['l_contentid'] == $like['firstblogtextid'])
{
$like['url'] = 'blog.'.VBSEO_VB_EXT.'?b='.$like['l_cgroup'];
$like['ctype']= $vbphrase['vbseo_like_blogpost'];
$like['gtype']= '';
}else
{
$like['url'] = 'blog.'.VBSEO_VB_EXT.'?bt='.$like['l_contentid'];
$like['ctype']= $vbphrase['vbseo_like_blogcom'];
$like['gtype']= '';
}
$like['pagetext'] = $like['blogtext'];
$like['dateline'] = $like['blogdateline'];
$like['gtitle']= $like['blogtitle'];
break;
case VBSEO_UI_CMS:
$like['url'] = 'content.'.VBSEO_VB_EXT.'?r='.$like['l_cgroup'];
$like['ctype']= $vbphrase['vbseo_like_article'];
$like['gtype']= '';
$like['gtitle']= $like['cmstitle'];
$like['pagetext'] = $like['cmstext'];
$like['dateline'] = $like['cmsdateline'];
break;
}
return $like;
}
public static function prerender_likes(&$likes)
{       
global $vbulletin;
require_once(DIR . '/includes/functions_forumdisplay.'.VBSEO_VB_EXT);
require_once(DIR . '/includes/functions_user.'.VBSEO_VB_EXT);
foreach($likes as $ii=>$post)
{
exec_switch_bg();
self::get_like_typespec($post);
$post['pagetext'] = strip_tags(fetch_censored_text(trim(fetch_trimmed_title(strip_bbcode($post['pagetext'], 1), 200))));
if(!($post['avatarurl']))
{
fetch_musername($post);
fetch_avatar_from_userinfo($post, true);
}
$post['avatarurl'] = str_replace('&amp;', '&', $post['avatarurl']);
$post['likedate'] = vbdate($vbulletin->options['dateformat'], $post['l_dateline'], true);
$post['liketime'] = vbdate($vbulletin->options['timeformat'], $post['l_dateline']);
$post['postdate'] = vbdate($vbulletin->options['dateformat'], $post['dateline'], true);
$post['posttime'] = vbdate($vbulletin->options['timeformat'], $post['dateline']);
$likes[$ii] = $post;
}                  
}
public static function username_updated($userid, $username)
{       
if(!intval($userid) || !$username)
return false;
$db = vbseo_get_db();
$db->vbseodb_query($q="UPDATE ".vbseo_tbl_prefix('vbseo_likes')."
SET l_from_username = '" . vbseo_db_escape($username)."'
WHERE l_from_userid = ".intval($userid));
}
}
?>