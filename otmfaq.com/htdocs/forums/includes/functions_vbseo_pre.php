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

define('VBSEO_VERSION2_MORE', '3.3.2');
define('VBSEO_SUBVERSION', 228);
define('VBSEO_TIMESTAMP', time());
define('VBSEO_SUBDATE_MORE', 1232028057);
define('VBSEO_ADDON', isset($_GET['vbseoaddon']) ? $_GET['vbseoaddon'] : '');
define('VBSEO_EXPIRED_MORE', VBSEO_TIMESTAMP > VBSEO_SUBDATE_MORE + 5184000);
define('VBSEO_INCLUDED', true);
error_reporting(E_ALL &~E_NOTICE & ~8192);
define('VBSEO_DIRNAME', dirname(__FILE__));
if (!defined('VBSEO_CONFIG_FILENAME'))
define('VBSEO_CONFIG_FILENAME', dirname(__FILE__) . '/config_vbseo.php');
global $seo_replacements, $vbseo_custom_char_replacement;
include_once VBSEO_CONFIG_FILENAME;
if (!defined('VBSEO_VB_EXT'))
define('VBSEO_VB_EXT', 'php');
function vbseo_http_s_url($url)
{
if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || isset($_SERVER['HTTP_FRONT_END_HTTPS']))
$url = str_replace('http:', 'https:', $url);
return $url;
}
if (stristr($_SERVER['SERVER_SOFTWARE'], 'apache') && isset($_SERVER['HTTP_X_REWRITE_URL']))
$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_REWRITE_URL'];
if (!defined('VBSEO_TOPREL'))
{
$vbseofn2 = str_replace("\\", "/", $_SERVER['SCRIPT_FILENAME'] ? $_SERVER['SCRIPT_FILENAME'] :
$_ENV['PATH_TRANSLATED']);
$vbseodocroot = str_replace("\\", "/", preg_replace('#\\\\+#', '/',
isset($_SERVER['SUBDOMAIN_DOCUMENT_ROOT']) ? $_SERVER['SUBDOMAIN_DOCUMENT_ROOT'] : $_SERVER['DOCUMENT_ROOT']));
$vbseofn = $vbseolink = '';
while (!$vbseolink && (strlen($vbseofn2) > 1) && ($vbseofn != $vbseofn2))
{
$vbseofn = $vbseofn2;
if (@is_link($vbseofn))
{
$vbseolink = @readlink($vbseofn);
if (strstr($vbseolink, '../'))
{
$vbseolink = $vbseofn . '/../' . $vbseolink;
do
{
$ap = $vbseolink;
$vbseolink = preg_replace('#/?[^/]*/\.\.#', '', $ap, 1);
}
while ($vbseolink != $ap);
}
if (strlen($vbseolink) > 1)
{
$vbseodocroot = str_replace('//', '/', preg_replace('#^' . preg_quote($vbseofn, '#') . '#', $vbseolink, $vbseodocroot, 1));
$vbseofn = substr(__FILE__, strlen($vbseolink));
}
break;
}
$vbseofn2 = dirname($vbseofn);
}
if (defined('VBSEO_CUSTOM_DOCROOT') && VBSEO_CUSTOM_DOCROOT &&
stristr($vbseo_toprel = str_replace("\\", "/", dirname(VBSEO_DIRNAME)), VBSEO_CUSTOM_DOCROOT))
{
if (defined('VBSEO_CUSTOM_TOPREL') && VBSEO_CUSTOM_TOPREL)
$vbseo_toprel = VBSEO_CUSTOM_TOPREL;
else
$vbseo_toprel = str_replace(VBSEO_CUSTOM_DOCROOT, "", $vbseo_toprel);
}
else
if (!$vbseodocroot)
{
$vbseo_toprel = dirname($_SERVER['PATH_INFO']);
$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_REWRITE_URL'];
}
else
if (stristr($vbseo_toprel = str_replace("\\", "/", dirname(VBSEO_DIRNAME)), $vbseodocroot))
$vbseo_toprel = preg_replace('#^.*?' . preg_quote($vbseodocroot, '#') . '#i', '', $vbseo_toprel);
else
if (stristr($vbseo_toprel = str_replace("\\", "/", dirname($_SERVER['SCRIPT_FILENAME'])), $vbseodocroot))
$vbseo_toprel = preg_replace('#^.*?' . preg_quote($vbseodocroot, '#') . '#i', '', $vbseo_toprel);
else
$vbseo_toprel = dirname(dirname($vbseofn));
define('VBSEO_FN', $vbseofn);
define('VBSEO_RWDOCROOT', $vbseodocroot);
$vbseo_toprel = preg_replace('#^.*\:#', '', $vbseo_toprel);
$vbseo_toprel = str_replace('/./', '/', $vbseo_toprel);
$standard_port = ( ( ! $_SERVER['HTTPS'] && 80 == $_SERVER['SERVER_PORT'] ) || ( $_SERVER['HTTPS'] && 443 == $_SERVER['SERVER_PORT'] ) ) ? true : false;
define('VBSEO_HTTP_HOST', ( $standard_port && strstr($_SERVER['HTTP_HOST'], ':') ) ? substr($_SERVER['HTTP_HOST'], 0, strpos($_SERVER['HTTP_HOST'], ':')) : $_SERVER['HTTP_HOST']);
define('VBSEO_HTTP_DOMAIN', vbseo_http_s_url('http://' . VBSEO_HTTP_HOST));
define('VBSEO_TOPREL', $vbseo_toprel = preg_replace('#//+#', '/', '/' . str_replace("\\", "/", $vbseo_toprel) . '/'));
define('VBSEO_TOPREL_FULL', VBSEO_HTTP_DOMAIN . VBSEO_TOPREL);
if(substr($_SERVER['REQUEST_URI'],0,2)=='//')$_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'],1);
$vbseo_req = isset($_SERVER['HTTP_X_REWRITE_URL']) ? $_SERVER['HTTP_X_REWRITE_URL'] :
(isset($_SERVER['HTTP_X_ORIGINAL_URL']) ? $_SERVER['HTTP_X_ORIGINAL_URL'] :
(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] :
(isset($_ENV['REQUEST_URI']) ? $_ENV['REQUEST_URI'] : $_SERVER['SCRIPT_NAME']))
);
if (strstr($vbseo_req, 'vbseo.php') && $_GET['vbseourl'])
$vbseo_req = preg_replace('#vbseo\.php.*#', $_GET['vbseourl'] . ($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''), $vbseo_req);
$vbseo_req = preg_replace('#\#.*$#', '', $vbseo_req);
$_SERVER['REQUEST_URI'] = $vbseo_req;
define('VBSEO_BASE', $vbseo_base = preg_replace('#[^/]*$#', '', $vbseo_req));
define('VBSEO_INFORUMDIR', stristr(VBSEO_BASE, VBSEO_TOPREL));
$vbseo_toprel_len = strlen(VBSEO_TOPREL);
$vbseo_base_len = strlen(VBSEO_BASE);
define('VBSEO_TOPBASE', ($vbseo_toprel_len < $vbseo_base_len && VBSEO_INFORUMDIR) ? substr(VBSEO_BASE, $vbseo_toprel_len) : '');
if((substr(VBSEO_BASE, 0, $vbseo_toprel_len) != VBSEO_TOPREL) && !strstr($vbseo_base,'://'))
{
for($_vi=0; $_vi<$vbseo_toprel_len; $_vi++)
if($vbseo_base[$_vi] != $vbseo_toprel[$_vi])break;
$_vcnt1 = substr_count(substr($vbseo_toprel, $_vi), '/');
$vbseo_relpath = str_repeat('../', $_vcnt1) . substr($vbseo_base, $_vi);
}
define('VBSEO_BASEDEPTH', strstr(VBSEO_TOPBASE, '/') || ($vbseo_toprel_len > $vbseo_base_len) || !VBSEO_INFORUMDIR);
$vbseo_requrl = @substr($vbseo_req, stristr(VBSEO_BASE, VBSEO_TOPREL) ? min($vbseo_base_len, $vbseo_toprel_len) : (stristr(VBSEO_TOPREL, VBSEO_BASE) ? $vbseo_base_len : 1));
define('VBSEO_REQURL', $vbseo_requrl);
define('VBSEO_REQURL_FULL', VBSEO_HTTP_DOMAIN. $vbseo_req);
define('VBSEO_VB35X', file_exists(VBSEO_DIRNAME . '/class_core.' . VBSEO_VB_EXT) ? 1 : 0);
$vbseo_redir_url = $_SERVER['REDIRECT_URL'] . ($_SERVER['REDIRECT_QUERY_STRING'] ? '?' . $_SERVER['REDIRECT_QUERY_STRING'] : '');
if(strstr($vbseo_redir_url, '/vbseo.php')) $vbseo_redir_url = '';
else
{
if(strstr($_SERVER['PHP_SELF'], '/vbseo.php')) 
$_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'] = $_SERVER['REDIRECT_URL'];
}
$vbseo_redir_url = substr($vbseo_redir_url, stristr(VBSEO_BASE, VBSEO_TOPREL) ? min($vbseo_base_len, $vbseo_toprel_len) : $vbseo_base_len);
define('VBSEO_REDIRURL', $vbseo_redir_url);
}
define('VBSEO_ROBOTS_LIST', 'appie|msnbot|jeeves|googlebot|mediapartners|harvest|htdig|yahoo|linkwalker|lycos_|scooter|ia_archiver|netcraft' . (VBSEO_EXTRA_ROBOTS?'|' . VBSEO_EXTRA_ROBOTS:''));
define('VBSEO_IS_ROBOT', preg_match('#(' . VBSEO_ROBOTS_LIST . ')#i', $_SERVER['HTTP_USER_AGENT']));
define('VBSEO_FORUMID_URI', 'f');
define('VBSEO_FORUMID_URI2', 'forumid');
define('VBSEO_THREADID_URI', 't');
define('VBSEO_USERID_URI', 'u');
define('VBSEO_POSTID_URI', 'p');
define('VBSEO_PAGENUM_URI', 'page');
define('VBSEO_PAGENUM_URI_GARS', 'garpg');
define('VBSEO_SORT_URI', 'sort');
define('VBSEO_SORTORDER_URI', 'order');
define('VBSEO_ACTION_URI', 'do');
define('VBSEO_POLLID_URI', 'pollid');
define('VBSEO_TREE_ICON', 'images/misc/navbits_finallink.gif');
define('VBSEO_REDIRECT_URI', 'redirect-to/');
define('VBSEO_APPEND_CHAR', 'a');
define('VBSEO_BLOG_CATID_URI', 'blogcategoryid');
function vbseo_init_gcache()
{
global $vbseo_gcache, $g_cache;
if(isset($vbseo_gcache))
return;
$gcache_classes = array(
'forum', 'thread', 'post', 'user',
'polls', 'usernm', 'blogcat', 'groups',
'blogcom', 'blog', 'battach', 'album', 'pic',
'var'
);
$vbseo_gcache = array();
foreach($gcache_classes as $_oc)
$vbseo_gcache[$_oc] = array();
$g_cache = &$vbseo_gcache;
}
function vbseo_init_obj_ids()
{
global $found_object_ids;
if(isset($found_object_ids))
return;
$object_classes = array('user_ids', 'blog_ids', 'blogcat_ids', 'blogatt_ids','blogcp_ids',
'postthread_ids', 'prepostthread_ids', 'thread_ids', 'poll_ids', 
'attachment_ids', 'thread_last', 'forum_last', 'user_names',
'postthreads', 'groups', 'groupscat', 'groupsdis',
'album', 'pic'
);
$found_object_ids = array();
foreach($object_classes as $_oc)
$found_object_ids[$_oc] = array();
$found_object_ids['announcements'] = false;
}
function vbseo_vars_push($var)
{
$args = func_get_args();
if(!$_SERVER['vbseo_vars']) $_SERVER['vbseo_vars'] = array();
foreach($args as $varname)
$_SERVER['vbseo_vars'][$varname] = $GLOBALS[$varname];
}
function vbseo_output_handler($outbuffer, $isxml = false)
{
global $vboptions;
if(!defined('VBSEO_OUTHANDLER'))
{
@define('VBSEO_OUTHANDLER', 1);
$detxml = (substr($outbuffer, 0, 5) == '<?xml');
$GLOBALS['vbseo_proc_xml'] = $isxml || $detxml;
if (preg_match_all('#<[^>]*?[ \<\[]data="(.*?)"#is', $outbuffer, $outm))
{
foreach($outm[1] as $outt)
{
$cont = html_entity_decode ($outt);
$cont = make_crawlable($cont);
$cont = function_exists('htmlspecialchars_uni')?htmlspecialchars_uni($cont):htmlspecialchars($cont);
$outbuffer = str_replace($outt, $cont, $outbuffer);
}
}
else
$outbuffer = make_crawlable($outbuffer);
$outbuffer = preg_replace('#([\";]|\&quot\;)(images/)#s', '$1' . $vboptions['bburl2'] . '/$2', $outbuffer);
if($detxml && (!function_exists('headers_list') || preg_match('#\|content-length\:#i', implode('|',headers_list()) ) ))
@header ('Content-Length: ' . strlen($outbuffer));
}
return $outbuffer;
}
function vbseo_vars_pop()
{
if($_SERVER['vbseo_vars'])
foreach($_SERVER['vbseo_vars'] as $varname=>$varvalue)
$GLOBALS[$varname] = $varvalue;
unset($_SERVER['vbseo_vars']);
}
function vbseo_extra_inc($include_file)
{
if (file_exists($incfile = VBSEO_DIRNAME . '/functions_vbseo_' . $include_file . '.php'))
include_once $incfile;
}
vbseo_init_gcache();
vbseo_init_obj_ids();
?>