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

function vbseo_replace_urls_mini($preurl, $url, $tid, $ins = '', $ty = 't')
{
$url = str_replace('\"', '"', $url);
$preurl = str_replace('\"', '"', $preurl);
$GLOBALS['vbseo_find_tids'][] = $tid;
return $preurl . "!" . $ins . $tid . "!" . $url;
}
function vbseo_replace_urls_mini_post($preurl, $url, $pid)
{
$url = str_replace('\"', '"', $url);
$preurl = str_replace('\"', '"', $preurl);
$GLOBALS['vbseo_find_pids'][] = $pid;
return $preurl . "!p" . $pid . "!" . $url;
}
function vbseo_prepare_int_replace($ptext)
{
global $vboptions;
if (VBSEO_REWRITE_THREADS_ADDTITLE)
{
vbseo_reverse_formats();
$matchfull = preg_quote($vboptions['bburl2'].'/', '#');
if (VBSEO_REWRITE_THREADS_ADDTITLE_POST)
{
$ptext = preg_replace('#(href=")(' . $matchfull . VBSEO_FIND_P_FORMAT . '[^/"]*")#eis', 'vbseo_replace_urls_mini_post(\'$1\',\'$2\',\'$3\')', $ptext);
$ptext = preg_replace('#(href=")(' . $matchfull . 'showpost\.' . VBSEO_VB_EXT . '\?[^"]*?p(?:ostid)?=(\d+)[^/"]*")#eis', 'vbseo_replace_urls_mini_post(\'$1\',\'$2\',\'$3\')', $ptext);
}
$ptext = preg_replace('#(href=")(' . $matchfull .  VBSEO_FIND_MT_FORMAT . '[^/"]*")#eis', 'vbseo_replace_urls_mini(\'$1\',\'$2\',\'$3\',\'m\')', $ptext);
$ptext = preg_replace('#(href=")(' . $matchfull .  VBSEO_FIND_T_FORMAT . '[^/"]*")#eis', 'vbseo_replace_urls_mini(\'$1\',\'$2\',\'$3\')', $ptext);
$ptext = preg_replace('#(href=")(' . $matchfull . '(?:show|print)thread\.' . VBSEO_VB_EXT . '\?[^"]*?t(?:hreadid)?=(\d+)[^/"]*")#eis', 'vbseo_replace_urls_mini(\'$1\',\'$2\',\'$3\')', $ptext);
}
return $ptext;
}
function vbseo_complete_sec($sec, $dat_proc = '')
{
global $vboptions, $forum, $vbulletin, $vbphrase, $stylevar, $vbseo_gcache,
$vbseo_linkbacks_no;
if (!VBSEO_ENABLED)return;
if(defined('VBSEO_UNREG_EXPIRED'))
return ;
if (VBSEO_IGNOREPAGES &&
preg_match('#(' . VBSEO_IGNOREPAGES . ')#i', VBSEO_REQURL))
return;
if(isset($vbulletin) && !$vbseo_cutbburl)
$vbseo_cutbburl = preg_replace('#/$#', '', $vbulletin->options['bburl']);
if ($sec == 'init_startup' && $vbseo_cutbburl)
{
vbseo_get_options();
vbseo_check_stripsids();
vbseo_prepare_seo_replace();
if (VBSEO_IN_PINGBACK && (THIS_SCRIPT == 'showthread'))
@header('X-Pingback: ' . $vbseo_cutbburl . '/vbseo-xmlrpc/');
if(THIS_SCRIPT != 'search')
vbseo_prepare_cat_anchors();
if (THIS_SCRIPT == 'newreply' || THIS_SCRIPT == 'editpost' || THIS_SCRIPT == 'newthread')
{
$vbseo_ref = $_SERVER['HTTP_REFERER'];
$pre_repl = '';
$q=$_POST['message'];
if ($vbseo_ref && strstr(strtolower($vbseo_ref), VBSEO_HTTP_HOST))
{
$pre_repl = preg_replace('#/[^/]*$#', '/', $vbseo_ref);
}
if($pre_repl && ($pre_repl != $vbseo_cutbburl.'/'))
{
$rs = '#((?:<a[^>]*?href="|\[url="|\[url\]|<img[^>]*?src="|\[img="|\[img\]))';
$_POST['message'] = preg_replace(
$rs.
'('.preg_quote($vbseo_cutbburl).'/)?([^:"\[\]]*?\.\.[^:"\[\]]*?["\[])#i', 
'$1'.$pre_repl.'$3', 
$_POST['message']);
do {
$_pmsg = $_POST['message'];
$_POST['message'] = preg_replace('#(://[^\"\]]*?/)([^/\"\]]*/)\.\./#', '$1', $_POST['message']);
}while($_POST['message']!=$_pmsg);
}
}
}
$newpost_name = '';
if(($sec == 'blog_fpdata_presave') || ($sec == 'blog_textdata_start') || ($sec == 'blog_data_start'))
$newpost_name = 'blog';
if(in_array($sec, array('groupmessagedata_presave','groupmessagedata_start')))
$newpost_name = 'message';
if(($sec == 'newpost_process') || ($sec == 'newpost_complete'))
$newpost_name = 'newpost';
if($sec == 'visitormessagedata_start')
$newpost_name = 'message';
$clean_redir = $newpost_name ? 1 : 0;
if(!$newpost_name && ($sec == 'postdata_presave')) $newpost_name = 'edit';
$may_addttl = $clean_redir && (!isset($_POST['vbseo_is_retrtitle']) || isset($_POST['vbseo_retrtitle']));
$force_addttl = $newpost_name && (isset($_POST['vbseo_is_retrtitle']) && isset($_POST['vbseo_retrtitle']));
$addttl = VBSEO_REWRITE_EXT_ADDTITLE && ($force_addttl || $may_addttl);
global $$newpost_name;
if (isset($$newpost_name))
{
$pmsg_a = &$$newpost_name;
if (isset($pmsg_a['pagetext']))
$pmsg = &$pmsg_a['pagetext'];
else
if (isset($pmsg_a['message']))
$pmsg = &$pmsg_a['message'];
}
if ($clean_redir)
{
vbseo_get_options();
$redurl = $vboptions['bburl2'] . '/' . VBSEO_REDIRECT_URI . '?redirect=' ;
$pmsg = preg_replace('#' . preg_quote($redurl, '#') . '([^"\]\[]*)#eis', 'urldecode(\'$1\')', $pmsg);
}
if ($addttl)
{   
preg_match_all('#\[url=?\"?(.*?)\"?\](.+?)\[\/url\]#is', $pmsg, $lmatch);
for($i = 0; $i < count($lmatch[0]); $i++)
{
$ul = trim($lmatch[1][$i]);
$ulin = trim($lmatch[2][$i]);
if ($ul && !@strstr($ulin, $ul))
continue;
if (!@strstr($ulin, '://'))
$ulin = 'http://' . $ulin;
if (!$ul) $ul = $ulin;
if (preg_match('#^http://#', $ulin) 
&& (!VBSEO_REWRITE_EXT_ADDTITLE_BLACKLIST || !preg_match('#' . VBSEO_REWRITE_EXT_ADDTITLE_BLACKLIST . '#i', $ulin))
)
{
vbseo_reverse_formats();
$matchfull = preg_quote('http://' . VBSEO_HTTP_HOST, '#');
$ismatch = false;
if (VBSEO_REWRITE_THREADS_ADDTITLE_POST)
$ismatch |= preg_match('#' . $matchfull . '[^"]*?/' . VBSEO_FIND_P_FORMAT . '#is', $ulin);
$ismatch |= preg_match('#' . $matchfull . '[^"]*?/' . VBSEO_FIND_MT_FORMAT . '#is', $ulin);
$ismatch |= preg_match('#' . $matchfull . '[^"]*?/' . VBSEO_FIND_T_FORMAT . '#is', $ulin);
$ulin_shot = str_replace(VBSEO_TOPREL_FULL, '', $ulin);
if (
($arr = vbseo_check_url('VBSEO_URL_FORUM_PAGENUM', $ulin_shot)) ||
($arr = vbseo_check_url('VBSEO_URL_FORUM', $ulin_shot))
)
{
if (!isset($arr['forum_id']) &&
(isset($arr['forum_path']) || isset($arr['forum_title']))
) $arr['forum_id'] = vbseo_reverse_forumtitle($arr);
vbseo_get_forum_info();
if (!vbseo_forum_is_public($vbseo_gcache['forum'][$arr['forum_id']]))
$ismatch = true;
}
if (!$ismatch)
{
$pret = vbseo_http_query_full($ulin);
$ptitle = vbseo_get_page_title($pret['content'], defined('VBSEO_MAX_TITLE_LENGTH')?VBSEO_MAX_TITLE_LENGTH:0, true, $pret['headers']);
if ($ptitle && $ptitle != $vboptions['bbtitle'])
{
$pmsg = str_replace($lmatch[0][$i],
'[url=' . $ulin . ']' . $ptitle . '[/url]',
$pmsg);
}
}
}
}
}
$trackback = VBSEO_EXT_TRACKBACK && (($sec == 'newpost_complete') || ($sec == 'threadmanage_update'));
$pingback = VBSEO_EXT_PINGBACK && ($sec == 'newpost_complete');
if ($pingback || $trackback)
{
global $found_object_ids;
if (!$vboptions['bburl2'] || !$vbseo_gcache['forum'])
{
vbseo_startup();
}
$r_post_id = $pmsg_a['postid'];
if (!$r_post_id && $GLOBALS['threadinfo'])
$r_post_id = $GLOBALS['threadinfo']['firstpostid'];
unset($vbseo_gcache['post'][$r_post_id]);
$found_object_ids['prepostthread_ids'] = array($r_post_id);
vbseo_get_post_thread_info($r_post_id);
$threadid = $vbseo_gcache['post'][$r_post_id]['threadid'];
vbseo_get_thread_info($threadid);
$forumid = $vbseo_gcache['thread'][$threadid]['forumid'];
$vbseo_url_ = VBSEO_REWRITE_THREADS ? vbseo_thread_url_postid($r_post_id) : 'showthread.php?p='.$r_post_id;
$vbseo_url_t = vbseo_thread_url($threadid);
if (!strstr($vbseo_url_, '://'))
$vbseo_url_ = $vboptions['bburl2'] . '/' . $vbseo_url_;
if (!strstr($vbseo_url_t, '://'))
$vbseo_url_t = $vboptions['bburl2'] . '/' . $vbseo_url_t;
if (THIS_SCRIPT == 'newthread')
{
$vbulletin->db->query_write("INSERT INTO " . vbseo_tbl_prefix('vbseo_serviceupdate') . "
(s_threadid, s_updated)
VALUES
('$threadid', 0)
"
);
}
}
if($sec == 'blog_fpdata_postsave')
{
global $blogman;
if($blogman && $bid = $blogman->blog['blogid'])
$vbulletin->db->query_write("INSERT INTO " . vbseo_tbl_prefix('vbseo_serviceupdate') . "
(s_threadid, s_updated, s_type)
VALUES
('$bid', 0, 1)
"
);
}
if ($trackback && $_REQUEST['sendtrackbacks'])
{
$tracurls = explode(' ', $_REQUEST['sendtrackbacks']);
$tdetails = vbseo_get_thread_details($r_post_id);
$tdetails['pagetext'] = preg_replace('#\[.+?\]#', '', $tdetails['pagetext']);
vbseo_extra_inc('linkback');
foreach($tracurls as $turl)
if (trim($turl))
{
$turl = trim($turl);
if (!preg_match('#^http://#', $turl))
continue;
if (vbseo_pingback_exists($turl, $threadid))
continue;
$snippet = vbseo_utf8_substr($tdetails['pagetext'], 0, VBSEO_SNIPPET_LENGTH);
$res_success = vbseo_do_trackback($turl, $vbseo_url_t, $vbseo_gcache['thread'][$threadid]['title'], $vboptions['bbtitle'], $snippet . '...');
vbseo_store_pingback($vbseo_url_, $turl, 1, $r_postid, 0,
$threadid, 0, $_REQUEST['subject'], $snippet, 0, $res_success,
1, false);
}
}
if ($pingback && vbseo_forum_is_public($vbseo_gcache['forum'][$forumid]))
{
vbseo_extra_inc('linkback');
preg_match_all('#\[url=?\"?(.*?)\"?\](.+?)\[\/url\]#is', $pmsg, $lmatch);
for($i = 0; $i < count($lmatch[0]); $i++)
{
$ulin = trim($lmatch[1][$i]);
if (!$ulin) 
$ulin = trim($lmatch[2][$i]);
if (!@strstr($ulin, '://'))
$ulin = 'http://' . $ulin;
if (preg_match('#^http://#', $ulin) && !strstr($ulin, VBSEO_HTTP_HOST) && (!VBSEO_REWRITE_EXT_ADDTITLE_BLACKLIST || !preg_match('#' . VBSEO_REWRITE_EXT_ADDTITLE_BLACKLIST . '#i', $ulin))
)
{
if (vbseo_pingback_exists($ulin, $threadid))
continue;
$res_success = vbseo_do_pingback($vbseo_url_, $ulin);
if ($res_success >= 0)
{
vbseo_store_pingback($vbseo_url_, $ulin, 0, $r_postid, 0,
$threadid, 0, $_REQUEST['subject'], $snippet, 0, $res_success,
1);
}
}
}
}
switch ($sec)
{
case 'attachment_display':
case 'error_invalidid':
if(THIS_SCRIPT == 'attachment')
{
vbseo_check_attachment_url();
}
break;
case 'blog_entry_start':
if(VBSEO_REWRITE_BLOGS)
{
vbseo_code_template('blog_show_entry', '$blog[message] = vbseo_process_content_area($blog[message])');
vbseo_code_template('blog_comment', '$response[message] = vbseo_process_content_area($response[message])');
}
break;
case 'blog_sidebar_user_complete':
if(VBSEO_REWRITE_BLOGS)
{
if(is_array($dat_proc))
foreach($dat_proc['custompages'] as $ctype=>$clist)
foreach($clist as $cpblock)
$vbseo_gcache['blogcp_ids'][$cpblock['i']] = array(
'customblockid' => $cpblock['i'],
'title' => $cpblock['t'],
'userid' => $dat_proc['userid']
);
}
break;
case 'blog_entry_complete':
if(VBSEO_REWRITE_BLOGS)
if (VBSEO_REWRITE_BLOGS_ENT && ($_REQUEST['do'] == 'blog') && !$_REQUEST['page'])
{
$vbseo_cleanurl = preg_replace('#\?.+#', '', $_SERVER['VBSEO_URI']);
vbseo_add_canonic_url($vbseo_cleanurl);
}
break;
case 'blog_list_complete':
if(VBSEO_REWRITE_BLOGS)
if (VBSEO_REWRITE_BLOGS_LIST && ($_REQUEST['do'] == 'bloglist'))
{
$vbseo_cleanurl = preg_replace('#\?.+#', '', $_SERVER['VBSEO_URI']);
vbseo_add_canonic_url($vbseo_cleanurl);
}
break;
case 'process_templates_complete':
if(VBSEO_VB4)
{
vbseo_clean_basehref();
vbseo_add_canonic_url(vBSEO_Storage::get('canonical'));
if(VBSEO_VIRTUAL_HTML)
vbseo_process_template('notices');
if(class_exists('vBSEO_UI'))
{
vBSEO_UI::head_hook();
}
}
break;
case 'cache_templates':
if(!VBSEO_VB4)
{
vbseo_cache_templates();
}
break;
case 'global_bootstrap_init_complete':
vbseo_complete_sec('global_start');
vbseo_complete_sec('global_setup_complete');
define('VBSEO_GLOBAL_INIT', 1);
break;
case 'global_setup_complete':
if(VBSEO_VB4 && (THIS_SCRIPT=='attachment'))
{
$curl = vbseo_album_url('VBSEO_URL_MEMBER_PICTURE_IMG', $_GET);
}
if(!defined('VBSEO_GLOBAL_INIT'))
{
if(VBSEO_VB4)
{
vbseo_cache_templates();
}else
{
if(VBSEO_VIRTUAL_HTML)
vbseo_process_template('notices');
}
}
break;
case 'global_start':
if(!defined('VBSEO_GLOBAL_INIT'))
{
global $show;
$show['vbseo_vb_ext'] = VBSEO_VB_EXT;
if(defined('VBSEO_AJAX') && (SIMPLE_VERSION < 381))
{
ob_start("vbseo_output_handler");
ob_start();
}
if (!$vbulletin->userinfo['userid'] && VBSEO_CODE_CLEANUP_PREVIEW && (THIS_SCRIPT=='forumdisplay'))
{
$vbulletin->options['threadpreview'] = 0;
$vbseo_gcache['var']['vboptchanged'] = true;
}
if (!$vbulletin->userinfo['userid'] && VBSEO_FORUMJUMP_OFF && $vbulletin
&& (THIS_SCRIPT=='forumdisplay' || THIS_SCRIPT=='showthread'))
{
$vbulletin->options['useforumjump'] = 0;
$vbseo_gcache['var']['vboptchanged'] = true;
}
if (VBSEO_IN_REFBACK && (THIS_SCRIPT == 'showthread' || THIS_SCRIPT == 'showpost'))
{
$vbseo_ref = $_SERVER['HTTP_REFERER'];
if ($vbseo_ref && !strstr(strtolower($vbseo_ref), VBSEO_HTTP_HOST)
&& !strstr(strtolower($vbseo_ref), str_replace('www.','',VBSEO_HTTP_HOST)))
{
if (!defined('VBSEO_REFBACK_BLACKLIST') || !preg_match('#' . VBSEO_REFBACK_BLACKLIST . '#i', $vbseo_ref))
{
vbseo_extra_inc('linkback');
vbseo_ping_proc($vbseo_ref, $_GET['vbseourl'] ? 
$vbulletin->options['bburl'].'/'.$_GET['vbseourl'] :
VBSEO_TOPREL_FULL . VBSEO_REQURL, 2);
}
}
}
}
break;
case 'memberlist_bit':
global $userinfo, $usercache;
$usercache[$userinfo['userid']] = array('userid' => $userinfo['userid'],
'username' => $userinfo['username']
);
break;
case 'ajax_start':
if (VBSEO_LIKE_POST && ($_POST['do'] == 'vbseoui') )
{
vbseo_extra_inc('ui');
vBSEO_UI::ajax_hook();
}
if (($_POST['do'] == 'linkbackmod') && ($linkid = intval($_POST['id'])))
{
$ilink = $vbulletin->db->query_first("
SELECT l.*, t.forumid
FROM " . vbseo_tbl_prefix('vbseo_linkback') . " l
LEFT JOIN " . vbseo_tbl_prefix('thread') . " t ON t.threadid = l.t_threadid
WHERE t_id='$linkid'"
);
$ismod = can_moderate($ilink['forumid'], 'vbseo_linkbacks') ||
($vbulletin->userinfo['permissions']['adminpermissions'] &$vbulletin->bf_ugp_adminpermissions['ismoderator']);
if ($ismod)
{
vbseo_extra_inc('linkback');
if ($_POST['action'] == 'mod')
{
$vbulletin->db->query_write("
UPDATE " . vbseo_tbl_prefix('vbseo_linkback') . "
SET t_approve=IF(t_approve,0,1)
WHERE t_id='$linkid'"
);
if (!$ilink['t_approve'])
vbseo_send_notification_pingback($ilink['t_threadid'],
$ilink['t_postid'],
$ilink['t_src_url'],
$ilink['t_title'],
$ilink['t_text'],
1,
0
);
}
if ($_POST['action'] == 'ban')
{
$purl = parse_url($ilink['t_src_url']);
if($purl['host'])
{
$bdom = str_replace('www.', '', $purl['host']);
vbseo_linkback_bandomain($bdom, 1);
$vbulletin->db->query_write("
UPDATE " . vbseo_tbl_prefix('vbseo_linkback') . "
SET t_deleted = 1
WHERE t_src_url LIKE 'http%".vbseo_db_escape($bdom)."/%'"
);
}
}
if ($_POST['action'] == 'del')
{
$vbulletin->db->query_write("
UPDATE " . vbseo_tbl_prefix('vbseo_linkback') . "
SET t_deleted = 1
WHERE t_id = '$linkid'"
);
}
vbseo_linkback_approve($linkid);
header('Content-Type: text/plain;');
header('Connection: Close');
echo $ilink['t_approve']?'0':'1';
}
exit;
}
if ($_POST['do'] == 'updatelinkback')
{
$vbulletin->input->clean_array_gpc('p', array('linkid' => TYPE_UINT, 'title' => TYPE_STR));
$linkid = $vbulletin->GPC['linkid'];
$ilink = $vbulletin->db->query_first("
SELECT l.*
FROM " . vbseo_tbl_prefix('vbseo_linkback') . " l
WHERE t_id='" . intval($linkid) . "'"
);
$ismod = can_moderate($ilink['forumid'], 'vbseo_linkbacks') ||
($vbulletin->userinfo['permissions']['adminpermissions'] &$vbulletin->bf_ugp_adminpermissions['ismoderator']);
if ($ismod)
{
$ltitle = convert_urlencoded_unicode($vbulletin->GPC['title']);
$vbulletin->db->query_write("
UPDATE " . vbseo_tbl_prefix('vbseo_linkback') . " l
SET t_title = '" . vbseo_db_escape($ltitle) . "'
WHERE t_id = '" . intval($linkid) . "'"
);
}
$xml = new vB_AJAX_XML_Builder($vbulletin, 'text/xml');
$xml->add_tag('linkhtml', $ltitle);
$xml->print_xml();
}
break;
case 'forumadmin_update_save':
global $vboptions, $forumcache;
vbseo_get_options();
vbseo_prepare_seo_replace();
$vboptions['vbseo_opt'] = array();
$forumcache2 = $forumcache;
$forumcache = '';
vbseo_get_forum_info(true);
$forumcache = $forumcache2;
vbseo_check_datastore(true);
break;
case 'private_insertpm_process':
global $pmdm;
if (is_object($pmdm) && strstr($pmdm->pmtext['message'], '[post]'))
{
vbseo_startup();
$pmdm->pmtext['message'] =
preg_replace('#\[post\](\d+)\[\/post\]#',
'[url]' . $vboptions['bburl2'] . '/showthread.php?p=$1#post$1[/url]',
$pmdm->pmtext['message']);
$GLOBALS['VBSEO_REWRITE_TEXTURLS'] = 1;
$pmdm->pmtext['message'] = make_crawlable($pmdm->pmtext['message']);
unset($GLOBALS['VBSEO_REWRITE_TEXTURLS']);
}
break;
case 'member_infractionbit':
$GLOBALS['vbseo_gcache']['post'][$dat_proc['postid']] = $dat_proc;
break;
case 'parse_templates':
if (VBSEO_CATEGORY_ANCHOR_LINKS)
{
vbseo_modify_template34(
'forumhome_forumbit_level1_nopost',
'#(<a)([^>]*?>[^<]*?title)#', 
'$1 id="$forum[nametitle]" name="$forum[nametitle]" $2'
);
}
if(class_exists('vBSEO_UI'))
{             
if(!VBSEO_VB4)
vBSEO_UI::head_hook();
vBSEO_UI::parse_tpl_hook();
}
vbseo_code_template('socialgroups_grouplist_bit', '$GLOBALS[\'vbseo_gcache\'][\'groups\'][$group[\'groupid\']]=$group', true);
vbseo_code_template('socialgroups_categorylist_bit', '$GLOBALS[\'vbseo_gcache\'][\'groupscat\'][$category[\'categoryid\']]=$category', true);
vbseo_code_template('search_results_socialgroup', '$GLOBALS[\'vbseo_gcache\'][\'groupscat\'][$group[\'categoryid\']]=$group;', true);
vbseo_code_template('socialgroups_discussion', '$GLOBALS[\'vbseo_gcache\'][\'groupsdis\'][$discussion[\'discussionid\']]=$discussion', true);
vbseo_code_template('memberinfo_socialgroupbit', '$GLOBALS[\'vbseo_gcache\'][\'groups\'][$socialgroup[\'groupid\']]=$socialgroup', true);
vbseo_code_template('blog_entry_profile', '$GLOBALS[\'vbseo_gcache\'][\'blog\'][$this->blog[\'blogid\']]=$this->blog', true);
vbseo_code_template('blog_entry_profile', '$GLOBALS[\'vblog_categories\']=$this->categories', true);
if(!VBSEO_VB4)
{
vbseo_code_template('socialgroups_picturebit', '$GLOBALS[\'vbseo_gcache\'][\''.VBSEO_PIC_STORAGE.'\'][$picture[\''.VBSEO_PICID_URI.'\']]=$picture', true);
vbseo_code_template('album_picturebit', '$GLOBALS[\'vbseo_gcache\'][\''.VBSEO_PIC_STORAGE.'\'][$picture[\''.VBSEO_PICID_URI.'\']]=$picture', true);
}
if(!isset($_REQUEST['ajax']))
vbseo_code_template('blog_comment', '$GLOBALS[\'vbseo_gcache\'][\'blogcom\'][$response[\'blogtextid\']]=array(\'cpage\'=>true)', true);
vbseo_code_template('albumbit', '$GLOBALS[\'vbseo_gcache\'][\'album\'][$album[\'albumid\']]=$album', true);
vbseo_code_template('memberinfo_albumbit', '$GLOBALS[\'vbseo_gcache\'][\'album\'][$album[\'albumid\']]=$album', true);
vbseo_code_template('memberinfo_visitormessage', '$message[message] = vbseo_process_content_area($message[message])');
vbseo_code_template('socialgroups_message', '$message[message] = vbseo_process_content_area($message[message])');
vbseo_code_template('picturecomment_message', '$message[message] = vbseo_process_content_area($message[message])');
vbseo_code_template('pt_issuenotebit_user', '$note[message] = vbseo_process_content_area($note[message])');
vbseo_code_template('newpost_preview', '${previewmessage} = vbseo_process_content_area(${previewmessage})');
if((THIS_SCRIPT == 'blog'||THIS_SCRIPT == 'blog_post') && (VBSEO_PERMALINK_BLOG>0)
)
{
$permalinkurl = '#comment$response[blogtextid]';
if(isset($_REQUEST['ajax']))
$permalinkurl = 'blog.'.VBSEO_VB_EXT.'?bt=$response[blogtextid]' . $permalinkurl;
if(VBSEO_PERMALINK_BLOG == 2)
vbseo_modify_template34('blog_comment', '#\$response\[date\]#s', 
'<a href="'.$permalinkurl.'">$0</a>',
0, '<!--PERMALINK_INFO-->');
else
{
$plink = '<a href="'.$permalinkurl.'"><img src="'.vBSEO_Storage::path('fimages').'anchor.png" border="0" alt="$vbphrase[vbseo_permalink]" class="inlineimg" /></a>';
if(VBSEO_VB4)
$plink = '<li class="separator">|</li><li>'.$plink.'</li>';
vbseo_modify_template34('blog_comment', '#(blogipaddress.*?)(</(?:div|span|ul)>)#s', 
'$1 '.$plink.' $2',
0, '<!--PERMALINK_INFO-->');
}
vbseo_modify_template34('blog_comment', '#(OR \$show\[\\\'reportlink\\\'\])#s', 
'$1 OR 1', 0);
}
if(THIS_SCRIPT == 'member'||THIS_SCRIPT == 'visitormessage'||THIS_SCRIPT == 'converse')
{
$permalinkurl = ($_GET['tab'] && ($_GET['tab'] != 'visitor_messaging')) 
? 'member.php?u='.$_GET['u'] : '';
$permalinkurl .= '#vmessage$message[vmid]';
if(isset($_REQUEST['ajax']) && isset($_SERVER['HTTP_REFERER']))
$permalinkurl = $_SERVER['HTTP_REFERER'].$permalinkurl;
if(VBSEO_PERMALINK_PROFILE == 2)
vbseo_modify_template34('memberinfo_visitormessage', '#\$message\[date\]#s', 
'<a href="'.$permalinkurl.'">$0</a>',
0);
else
if(VBSEO_PERMALINK_PROFILE == 1)
vbseo_modify_template34('memberinfo_visitormessage', '#\$message\[date\].*?'.(VBSEO_VB4?'</span>.*?</span>' : '\\)\\)."').'#s', 
'$0 - <a href="'.$permalinkurl.'">$vbphrase[vbseo_permalink]</a>',
0, '<!--PERMALINK_INFO-->');
vbseo_modify_template34('MEMBERINFO', '#(vBulletin\.register_control\(\\\\"vB_TabCtrl\\\\", \\\\"profile_tabs\\\\", \\\\"\\$selected_tab\\\\")#s', 
"var vbseo_opentab=document.location.hash;\nvbseo_opentab = vbseo_opentab.substring(1,vbseo_opentab.length);\n".'$1 ? \\"$selected_tab\\" : vbseo_opentab');
}
$plink_option = 
((THIS_SCRIPT == 'album') || (THIS_SCRIPT == 'picturecomment')) ? VBSEO_PERMALINK_ALBUM : 
((THIS_SCRIPT == 'group') ? VBSEO_PERMALINK_GROUPS_PIC : 0);
if($plink_option)
{
$permalinkurl = '#picturecomment_$message[commentid]';
if(isset($_REQUEST['ajax']))
{
if($_REQUEST['groupid'])
$permalinkurl = 'group.' . VBSEO_VB_EXT . '?do=picture&attachmentid=$pictureinfo[attachmentid]&commentid=$message[commentid]' . $permalinkurl;
else
$permalinkurl = 'album.' . VBSEO_VB_EXT . '?albumid=$pictureinfo[albumid]&attachmentid=$pictureinfo[attachmentid]&commentid=$message[commentid]' . $permalinkurl;
}
if($plink_option == 2)
vbseo_modify_template34('picturecomment_message', '#\$message\[date\]#s', 
'<a href="'.$permalinkurl.'">$0</a>',
0);
else
if($plink_option == 1)
vbseo_modify_template34('picturecomment_message', '#\$message\[time\].*?</span>#s', 
'$0 - <a href="'.$permalinkurl.'">$vbphrase[vbseo_permalink]</a>',
0, '<!--PERMALINK_INFO-->');
}
if(THIS_SCRIPT == 'group')
{
$permalinkurl = '#gmessage$message[gmid]';
if(isset($_REQUEST['ajax']))
$permalinkurl = 'group.' . VBSEO_VB_EXT . '?do=discuss&gmid=$message[gmid]' . $permalinkurl;
if(VBSEO_PERMALINK_GROUPS== 2)
vbseo_modify_template34('socialgroups_message', '#\$message\[date\]#s', 
'<a href="'.$permalinkurl.'">$0</a>',
0);
else
if(VBSEO_PERMALINK_GROUPS== 1)
vbseo_modify_template34('socialgroups_message', '#\$message\[time\].*?</span>#s', 
'$0 - <a href="'.$permalinkurl.'">$vbphrase[vbseo_permalink]</a>',
0,'<!--PERMALINK_INFO-->');
}
if (VBSEO_IN_PINGBACK || VBSEO_IN_TRACKBACK || VBSEO_IN_REFBACK)
if (can_moderate(0, 'vbseo_linkbacks'))
{
$modlink = '<a class="smallfont" href="moderation.php?do=viewlinkbacks">'.$vbphrase[vbseo_moderated_linkbacks].'</a>';
$modrow = VBSEO_VB4 ? '<li>'.$modlink.'</li>' : 
'<tr><td class=\\"".($navclass[moderatedlinkbacks]?$navclass[moderatedlinkbacks]:"alt2")."\\">'.addslashes($modlink).'</td></tr>';
vbseo_modify_template('USERCP_SHELL',
'#(do=viewposts&amp;type=moderated.*?</(?:tr|li)>)#is',
'$1'.$modrow);
}
if (!$vbulletin->userinfo['userid'])
{
if (VBSEO_CODE_CLEANUP_MEMBER_DROPDOWN)
{
if(VBSEO_VB4)
{
vbseo_modify_template('memberaction_dropdown', '#<ul.*?</ul>#is', '');
vbseo_modify_template('memberaction_dropdown', 'popupctrl', '');
}else
{
if (THIS_SCRIPT == 'showthread')
{
$tplpostbit = vbseo_get_postbit_tpl();
vbseo_modify_template($tplpostbit,
'#<script[^>]+?>[^<]*?postmenu_\$post.*?</script>#is', '');
vbseo_modify_template($tplpostbit,
'#<div class=\\\"vbmenu_popup.*?</table>\s*</div>#is', '');
}
if (THIS_SCRIPT == 'blog')
{
$blog_tpls = array('blog_sidebar_user',
'blog_entry_with_userinfo',
'blog_entry_without_userinfo',
'blog_list_blogs_blog'
);
foreach($blog_tpls as $_btpl)
{
vbseo_modify_template($_btpl,
'#<script[^>]+?>[^<]*?blogusermenu.*?</script>#is', '');
vbseo_modify_template($_btpl,
'#<div class=\\\"vbmenu_popup.*?</div>#is', '');
}
}
}
}
if (THIS_SCRIPT == 'index') // || THIS_SCRIPT == 'forumdisplay')
{
if (VBSEO_CODE_CLEANUP_LASTPOST == 2)
{
vbseo_process_template('lastpost_col');
}
if (VBSEO_CODE_CLEANUP_LASTPOST == 1)
{
vbseo_process_template('lastpost_links');
}
vbseo_modify_template('threadbit',
'#<a href=\\\"misc\.php\?do=whoposted.*?>(.*?)</a>#is', '$1');
}
}
break;
case 'moderation_start':
if ($_REQUEST['do'] == 'viewlinkbacks')
{                 
global $navbar, $navclass, $HTML, $navbits, $headinclude, $header, $stylevar,
$footer, $db, $show, $navclass, $notices, $pmbox, $notifications_total,
$vbseo_showhits, $vbcsspath;
if (!can_moderate(0, 'vbseo_linkbacks'))
print_no_permission();
vbseo_startup();
$vbseolinkbackbits = '';
$perpage = 20;
$pagenumber = $_GET['page'] ? $_GET['page'] : 1;
$vbseodb = vbseo_get_db();
$tp = $vbseodb->vbseodb_query_first("
SELECT COUNT(*) as cnt
FROM " . vbseo_tbl_prefix('vbseo_linkback') . "
WHERE t_incoming=1 AND t_deleted=0 AND t_approve=0"
);
$totalposts = $tp['cnt'];
$vbseopings = $vbseodb->vbseodb_query($q="
SELECT t_id, t_time, t_src_url, t_dest_url, t_type, t_postid, t_postcount, t_threadid, t_page, t_title, t_text, t_approve, forumid, t_hits
FROM " . vbseo_tbl_prefix('vbseo_linkback') . "
LEFT JOIN " . vbseo_tbl_prefix('thread') . " on threadid=t_threadid
WHERE t_incoming=1 AND t_deleted=0 AND t_approve=0
ORDER BY t_time " . (preg_match('#^(asc|desc)$#i', VBSEO_DEFAULT_LINKBACKS_ORDER) ? VBSEO_DEFAULT_LINKBACKS_ORDER : "DESC") . 
" LIMIT " . ($pagenumber-1) * $perpage . "," . $perpage
);
$pagenav = construct_page_nav($pagenumber, $perpage, $totalposts, "moderation.php?do=viewlinkbacks");
$vbseo_showhits = VBSEO_LINKBACK_SHOWHITS_UG && is_member_of($vbulletin->userinfo,explode(' ',VBSEO_LINKBACK_SHOWHITS_UG));
$navbits[''] = $vbphrase['moderation'];
$navbits = construct_navbits($navbits);
while ($vbseoping = @$vbseodb->funcs['fetch_assoc']($vbseopings))
if (can_moderate($vbseoping['forumid'], 'vbseo_linkbacks')
)
{
$vbseoping['postno'] = $vbseoping['t_postcount'];
$vbseoping['ismod'] = 1;
$vbseoping['date'] = vbdate($vbulletin->options['dateformat'], $vbseoping['t_time'], true);
$vbseoping['time'] = vbdate($vbulletin->options['timeformat'], $vbseoping['t_time'], true);
$vbseoping['t_src_url'] = htmlentities($vbseoping['t_src_url']);
$vbseoping['t_dest_url'] = htmlentities($vbseoping['t_dest_url']);
$vbseoping['t_text_nohtml'] = htmlspecialchars(strip_tags($vbseoping['t_text']));
$vbseoping['t_title_html'] = htmlspecialchars($vbseoping['t_title']);
eval( vbseo_eval_template('vbseo_linkbackbit','$vbseolinkbackbits', 1));
}
if ($vbseolinkbackbits)
eval(vbseo_eval_template('vbseo_linkbacks','$HTML'));
else
$HTML = $vbphrase['vbseo_no_linkbacks_found'];
unset($vbseolinkbackbits);
construct_usercp_nav('moderatedlinkbacks');
$ucpshell = vbseo_vbtemplate_render('USERCP_SHELL', $navbits,
array('HTML' => $HTML)
);
print_output($ucpshell);
}
break;
case 'misc_start':
if ($_REQUEST['do'] == 'linkbacks')
{ 
vbseo_process_template('misc_linkbacks');
}
break;
case 'archive_forum_thread':
if ($GLOBALS['pda'] == 'vbseo')
$GLOBALS['pda'] = false;
break;
case 'archive_navigation':
if (VBSEO_ARCHIVE_ORDER_DESC && !$GLOBALS['pda'])
$GLOBALS['pda'] = 'vbseo';
break;
case 'forumrules':
vbseo_process_template('forumrules');
break;
case 'showthread_complete':
global $vbseo_bookmarks, $vbseo_linkback_menu, $show, $vbseo_linkback_uri, $thread, $vbseolinkbacks;
if((!$_SERVER['HTTP_REFERER'] || !strstr($_SERVER['HTTP_REFERER'],$_SERVER['VBSEO_URI'])) && !vbseo_is_threadedmode())
{
vbseo_insert_code(
"var cpost=document.location.hash.substring(1);var cpost2='';if(cpost){ var ispost=cpost.substring(0,4)=='post';if(ispost)cpost2='post_'+cpost.substring(4);if((cobj = fetch_object(cpost))||(cobj = fetch_object(cpost2))){cobj.scrollIntoView(true);}else if(ispost){cpostno = cpost.substring(4,cpost.length);if(parseInt(cpostno)>0){location.replace('".$vboptions['bburl2'] . "/showthread.php?p='+cpostno);};} }",
'onload');
}
if(class_exists('vBSEO_UI'))
{
vBSEO_UI::thread_hook();
}
$vbseolinkbacks = '';
vbseo_startup();
$vbseo_gcache['thread'][$thread['threadid']] = $thread;
vbseo_add_canonic_url(VBSEO_REWRITE_THREADS ? 
vbseo_thread_url($thread['threadid'], vbseo_vb_gpc(VBSEO_PAGENUM_URI_GARS) ? vbseo_vb_gpc(VBSEO_PAGENUM_URI_GARS) : vbseo_vb_gpc('page'), 
vbseo_vb_gpc(VBSEO_PAGENUM_URI_GARS) ? VBSEO_URL_THREAD_GARS_PAGENUM : '') 
: 'showthread.' . VBSEO_VB_EXT .'?t='.$thread['threadid'].(vbseo_vb_gpc('page') ? '&page='.vbseo_vb_gpc('page') : ''));
$in_linkbacks = VBSEO_IN_PINGBACK || VBSEO_IN_TRACKBACK || VBSEO_IN_REFBACK;
if ($in_linkbacks)
{
$show['vbseo_linkback_uri'] = $vbseo_linkback_uri = vbseo_create_full_url(VBSEO_REWRITE_THREADS ? vbseo_thread_url($thread['threadid']) : 'showthread.' . VBSEO_VB_EXT .'?t='.$thread['threadid']);
$showactusers = ($vboptions['showthreadusers'] == 1) ||($vboptions['showthreadusers'] == 2) ||
($vboptions['showthreadusers'] > 2 AND $vbulletin->userinfo['userid']);
vbseo_process_template('linkbacks_list', array('showactusers'=>$showactusers));
$vbseolinkbackbits = '';
$vbseo_linkbacks_no = 0;
$ismod = can_moderate($thread['forumid'], 'vbseo_linkbacks') ||
($vbulletin && $vbulletin->userinfo['permissions']['adminpermissions'] &$vbulletin->bf_ugp_adminpermissions['ismoderator']);
if($thread['vbseo_linkbacks_no'] || $ismod)
{ 
$vbseo_showhits = VBSEO_LINKBACK_SHOWHITS_UG && is_member_of($vbulletin->userinfo,explode(' ',VBSEO_LINKBACK_SHOWHITS_UG));                
$vbseodb = vbseo_get_db();
$vbseopings = $vbseodb->vbseodb_query("
SELECT t_id, t_time, t_src_url, t_dest_url, t_type, t_postid, t_postcount, t_threadid, t_page, t_title, t_text, t_approve, t_hits
FROM " . vbseo_tbl_prefix('vbseo_linkback') . "
WHERE t_incoming=1 AND t_deleted=0 AND t_wait=0
AND " . ($ismod?'':'t_approve>0 AND ') . 
"t_threadid = '" . $thread['threadid'] . "' " . 
" ORDER BY t_time " . (preg_match('#^(asc|desc)$#i', VBSEO_DEFAULT_LINKBACKS_ORDER) ? VBSEO_DEFAULT_LINKBACKS_ORDER : "DESC").
" LIMIT 0,50"
);
while ($vbseoping = @$vbseodb->funcs['fetch_assoc']($vbseopings))
{
$vbseoping['postno'] = $vbseoping['t_postcount'];
$vbseoping['ismod'] = $ismod;
$vbseoping['date'] = vbdate($vbulletin->options['dateformat'], $vbseoping['t_time'], true);
$vbseoping['time'] = vbdate($vbulletin->options['timeformat'], $vbseoping['t_time'], true);
$vbseoping['t_text_nohtml'] = htmlspecialchars(strip_tags($vbseoping['t_text']));
$vbseoping['t_title_html'] = htmlspecialchars($vbseoping['t_title']);
$vbseoping['t_dest_url'] = str_replace('&amp;amp;','&amp;',str_replace('&','&amp;',$vbseoping['t_dest_url']));
if ($vbseoping['t_postid'])
$vbseo_gcache['postpings'][$vbseoping['t_postid']]++;
else
$vbseo_gcache['postcounts'][vbseo_thread_pagenum($vbseoping['t_page']-1, 0) + 1]++;
eval( $q=vbseo_eval_template('vbseo_linkbackbit','$vbseolinkbackbits', 1));
$vbseo_linkbacks_no++;
}
if($vbseo_linkbacks_no)
{
eval(vbseo_eval_template('vbseo_linkbacks','$vbseolinkbacks'));
if($showactusers) 
$vbseolinkbacks = '<br />' . $vbseolinkbacks;
else
$vbseolinkbacks = $vbseolinkbacks . '<br />';
}
unset($vbseolinkbackbits);
}
$show['vbseolinkbacks'] = $vbseolinkbacks;
}
if ($in_linkbacks || VBSEO_BOOKMARK_THREAD)
{
$vbseo_url_t = urlencode($vboptions['bburl2'] . '/' . vbseo_thread_url($thread['threadid']));
$book_t = urlencode($thread['title']);
$is_public = vbseo_forum_is_public($GLOBALS['forum'], $GLOBALS['foruminfo'], false, true);
if ($is_public && VBSEO_BOOKMARK_THREAD)
{
$bmlist = vbseo_get_bookmarks();
$vbseo_bookmarks = '';
$bmno = 0;
foreach($bmlist as $bm)
if(VBSEO_VB4)
$vbseo_bookmarks .= '<li><a href="' . str_replace('%url%', $vbseo_url_t, str_replace('%title%', $book_t, $bm[0])) . '" target="_blank">' . $bm[2] . '</a></li>';
else
$vbseo_bookmarks .= '<tr><td class="vbmenu_option"><img class="inlineimg" src="' . $bm[1] . '" alt="' . $bm[2] . '" /> <a href="' . str_replace('%url%', $vbseo_url_t, str_replace('%title%', $book_t, $bm[0])) . '" target="_blank">' . $bm[2] . '</a><a name="vbseodm_' . ($bmno++) . '"></a></td></tr>';
}
vbseo_process_template('linkback_menu', $bmlist);
if ($_GET['nojs'])
{
preg_match('#<table.*?>(.*?)</table>#is', $vbseo_linkback_menu, $vbseo_m);
$vbseo_m[1] = str_replace('vbmenu_option', 'alt1', $vbseo_m[1]);
$vbseo_m[1] = str_replace('<td', '<td colspan="2"', $vbseo_m[1]);
vbseo_modify_template('SHOWTHREAD', 
"#(sendtofriend\.gif.*?sendtofriend\.gif.*?</tr>)#is",
'$1' . (addslashes($vbseo_m[1]))
);
}
}
break;
case 'group_complete':
if($_REQUEST['groupid'] && is_array($GLOBALS['group']) && $GLOBALS['group']['groupid'] && ($_REQUEST['do'] == 'view'))
{
$vbseo_gcache['groups'][$GLOBALS['group']['groupid']] = $GLOBALS['group'];
vbseo_add_canonic_url(
VBSEO_REWRITE_GROUPS ? 
vbseo_group_url(vbseo_vb_gpc('page')>1 ? VBSEO_URL_GROUPS_PAGE : VBSEO_URL_GROUPS, $_REQUEST) : 
'group.' . VBSEO_VB_EXT .'?groupid='.$_REQUEST['groupid'].'&page='.vbseo_vb_gpc('page'));
}
break;
case 'showthread_post_start':
global  $vbseolinkbacks, $vbcollapse, $vbseo_linkback_uri, 
$thread, $db, $show, $found_object_ids, $stylevar,
$vbseo_showhits;
if (defined('VBSEO_PRIVATE_REDIRECT_POSTID'))
{
vbseo_get_options();
$mode_nonlinear = vbseo_is_threadedmode();
{
vbseo_get_forum_info();
$r_post_id = VBSEO_PRIVATE_REDIRECT_POSTID;
if(($pg = $_REQUEST['pagenumber']) > 1)
{
}else
{
$found_object_ids['prepostthread_ids'] = array($r_post_id);
$pg = 1;
}
$parr = vbseo_get_post_thread_info($r_post_id, true);
$threadid = $parr[$r_post_id]['threadid'];
vbseo_get_thread_info($threadid);
$excpars = array('t', 'p','page', 'post', 'posted', 'viewfull');
$vbse_rurl = vbseo_thread_url_postid($r_post_id, $pg, $mode_nonlinear);
if($vbse_rurl)
{
if($hlpar = vbseo_check_highlight(1))
$excpars[] = $hlpar;
vbseo_url_autoadjust($vbse_rurl, $excpars, false);
}
}
}
break;
case 'postbit_display_complete':
global $thread, $vbseo_lastmod;
if (!isset($vbseo_lastmod) || ($dat_proc['dateline'] > $vbseo_lastmod))
$vbseo_lastmod = $dat_proc['dateline'];
$dat_proc['preposts'] = $dat_proc['postcount'];
$vbseo_gcache['post'][$dat_proc['postid']] = $dat_proc;
$vbseo_gcache['thread'][$thread['threadid']] = $thread;
$vbseo_postbit_pingback = (VBSEO_POSTBIT_PINGBACK > 0) && (THIS_SCRIPT != 'private') && (THIS_SCRIPT != 'member');
$vbseo_bookmark = VBSEO_BOOKMARK_POST;
if ($vbseo_postbit_pingback || $vbseo_bookmark)
{   
if (!$vboptions['bburl2'])
{
vbseo_startup();
}
$GLOBALS['post']['linkbacksno'] = $vbseo_gcache['postpings'][$GLOBALS['post']['postid']] + $vbseo_gcache['postcounts'][$GLOBALS['post']['postcount']];
$tplpostbit = vbseo_get_postbit_tpl();
if (!defined('VBSEO_POSTBIT_PINGBACK_CHG_' . $tplpostbit) && vbseo_tpl_exists($tplpostbit))
{
define('VBSEO_POSTBIT_PINGBACK_CHG_' . $tplpostbit, 1);
if ($vbseo_postbit_pingback)
vbseo_process_template('postbit_linkback');
}
}
break;
case 'memberlist_complete':
if(VBSEO_REWRITE_MEMBER_LIST)
{
vbseo_add_canonic_url(
vbseo_memberlist_url(isset($_REQUEST['ltr']) ? $_REQUEST['ltr']:'',
vbseo_vb_gpc('page')>1 ? vbseo_vb_gpc('page') : ''));
}
break;
case 'forumhome_complete':
vbseo_insert_code(
"if (is_ie || is_moz) { var cpost=document.location.hash;if(cpost){ if(cobj = fetch_object(cpost.substring(1,cpost.length)))cobj.scrollIntoView(true); }}",
'onload');
if(!is_array($GLOBALS['birthdays']))
{
preg_match_all('#<a href="[^"]*?member\.php\?u=(\d+)".*?>(.+?)<#', $GLOBALS['birthdays'], $birthm);
foreach($birthm[1] as $k => $v)
$GLOBALS['usercache'][$v] = array('userid' => $v,
'username' => $birthm[2][$k]
);
}
vbseo_startup();
vbseo_add_canonic_url($vboptions['bburl2'] . '/' . VBSEO_HOMEPAGE);
break;
case 'forumdisplay_complete':
global $forum;
vbseo_startup();
vbseo_add_canonic_url(VBSEO_REWRITE_FORUM ? 
vbseo_forum_url(vbseo_vb_gpc('f'), vbseo_vb_gpc('page')) : 
'forumdisplay.' . VBSEO_VB_EXT .'?f='.vbseo_vb_gpc('f').'&page='.vbseo_vb_gpc('page'));
break;
case 'threadmanage_update':
global $threadinfo;
if($threadinfo['title'] != $_REQUEST['title'])
{
$vbseodb = vbseo_get_db();
$vbseodb->vbseodb_query("UPDATE " . vbseo_tbl_prefix('post') . "
SET title = '".$vbseodb->vbseodb_escape_string('re: '.$_REQUEST['title'])."'
WHERE threadid = ".intval($threadinfo['threadid'])." AND
title = '".$vbseodb->vbseodb_escape_string('re: '.$threadinfo['title'])."'");
}
break;
case 'editpost_edit_complete':
case 'newreply_form_complete':
case 'newthread_form_complete':
case 'threadmanage_complete':
global $disablesmiliesoption;
if (VBSEO_REWRITE_EXT_ADDTITLE)
{
$ds_tag = VBSEO_VB4 ? 'li' : 'div';
$disablesmiliesoption = '<'.$ds_tag.'><label for="qr_retrtitle"><input type="checkbox" name="vbseo_retrtitle" value="1" id="qr_retrtitle" ' . (($_POST['vbseo_retrtitle'] || !isset($_POST['vbseo_is_retrtitle']))? ' checked':'') . '/>' . (VBSEO_VB4 ? '' : ' ') . $vbphrase['vbseo_auto_retrieve_titles'] . '</label>
<input type="hidden" name="vbseo_is_retrtitle" value="1" /></'.$ds_tag.'>'
. $disablesmiliesoption;
}
case 'prefix_fetch_array':
global $vbseo_tracklegend;  
if (VBSEO_EXT_TRACKBACK &&
in_array(THIS_SCRIPT, array('newthread', 'postings', 'newreply')) 
&& (!$GLOBALS['threadinfo']['threadid'] || ($GLOBALS['threadinfo']['open'] == 1))
&& !$vbseo_tracklegend
)
{                
global $posticons;
$vbseodb = vbseo_get_db();
$vbseopings = $vbseodb->vbseodb_query("
SELECT t_time, t_dest_url, t_approve
FROM " . vbseo_tbl_prefix('vbseo_linkback') . "
WHERE t_incoming=0 AND t_type=1 AND t_threadid = '" .
intval(isset($GLOBALS['threadinfo'])?$GLOBALS['threadinfo']['threadid']:$thread['threadid']) . "'
ORDER BY t_time DESC"
);
$plist = '';
while ($vbseoping = @$vbseodb->funcs['fetch_assoc']($vbseopings))
{
$plist .= '<li><strong>' . ((strlen($vbseoping['t_dest_url']) > 50)?substr($vbseoping['t_dest_url'], 0, 50) . '...':$vbseoping['t_dest_url']) . '</strong></li>';
}
if ($plist) $plist = "<div>" . $vbphrase[vbseo_already_pinged] . ":<ul type=\"disc\">$plist</ul></div>";
if(VBSEO_VB4)
{
$vbseo_tracklegend = '
<div class="blockrow">
<label for="trackbackto">' . $vbphrase['vbseo_trackback'] . '</label>
<input type="text" class="primary textbox" size="50" name="sendtrackbacks" value="' . $_REQUEST['sendtrackbacks'] . '" id="trackbackto" tabindex="1" />
<p class="description">' . $vbphrase['vbseo_send_trackbacks_to'] . '</p>
' . $plist . '
</div>
';
}
else
{
$vbseo_tracklegend = '
<fieldset class="fieldset">
<legend>' . $vbphrase['vbseo_trackback'] . '</legend>
<div style="padding:' . $stylevar['formspacer'] . 'px">
' . $vbphrase['vbseo_send_trackbacks_to'] . '
<div><label for="trackbackto"><input type="text" class="bginput" size="50" name="sendtrackbacks" value="' . $_REQUEST['sendtrackbacks'] . '" id="trackbackto" tabindex="1" /></label> </div>
' . $plist . '
</div>
</fieldset>
';
}           
$posticons = $vbseo_tracklegend . $posticons;
}           
break;
}
if (VBSEO_GOOGLE_AD_SEC)
{
$sps = $usps = array();
switch ($sec)
{
case 'forumdisplay_complete':
$sps[] = &$GLOBALS['threadbits'];
break;
case 'forumbit_display':
$sps[] = &$GLOBALS['forum']['title'];
break;
case 'forumhome_complete':
$sps[] = &$GLOBALS['forumbits'];
break;
case 'postbit_display_complete':
$GLOBALS['post']['title_original'] = $GLOBALS['post']['title'];
$GLOBALS['post']['message_original'] = $GLOBALS['post']['message'];
$sps[] = &$GLOBALS['post']['title'];
$usps[] = &$GLOBALS['post']['signature'];
$usps[] = &$GLOBALS['post']['musername'];
break;
};
for($i = 0; $i < count($sps); $i++)
if ($sps[$i])
$sps[$i] = vbseo_google_ad_section($sps[$i]);
for($i = 0; $i < count($usps); $i++)
if ($usps[$i])
$usps[$i] = vbseo_google_ad_section($usps[$i], true);
}
}
?>