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

function vbseo_check_multi_urls($aformats, $vbseo_url_)
{
global $vbseo_found_fn, $vbseo_found;
$selfurl = $selfurl_more = '';
foreach($aformats as $fmt)
{
if($vbseo_arr = vbseo_check_url_strict($fmt, $vbseo_url_))
{  
switch($fmt)
{
case 'VBSEO_URL_GROUPS_PICTURE_IMG':
if(!$selfurl)
{
$selfurl = VBSEO_PIC_SCRIPT.'.';
if(preg_match('#^(\d+)(d\d+)?(t)?#', $vbseo_arr['picture_id'], $atm))
$vbseo_arr['picture_id'] = $atm[1];
$selfurl_more = (isset($atm[3])?'&thumb=1&dl='.$atm[2]:'').'&';
}
case 'VBSEO_URL_GROUPS_PIC_PAGE':
case 'VBSEO_URL_GROUPS_PIC':
if(!$selfurl_more)
$selfurl_more = '&do=grouppictures';
case 'VBSEO_URL_GROUPS_PICTURE_PAGE':
case 'VBSEO_URL_GROUPS_PICTURE':
if(!$selfurl_more)
$selfurl_more = '&do=picture';
case 'VBSEO_URL_GROUPS_MEMBERS_PAGE':
case 'VBSEO_URL_GROUPS_MEMBERS':
if(!$selfurl_more)
$selfurl_more = '&do=viewmembers';
case 'VBSEO_URL_GROUPS_CATEGORY':
case 'VBSEO_URL_GROUPS_CATEGORY_PAGE':
if(!$selfurl_more)
{
if ($vbseo_arr['cat_name'] && !$vbseo_arr['cat_id'])
$vbseo_arr['cat_id'] = vbseo_reverse_object('groupcat', $vbseo_arr['cat_name']);
$selfurl_more = '&cat='.$vbseo_arr['cat_id'];
}
case 'VBSEO_URL_GROUPS_DISCUSSION_PAGE':
case 'VBSEO_URL_GROUPS_DISCUSSION':
if(!$selfurl_more)
$selfurl_more = '&do=discuss&discussionid='.$vbseo_arr['discussion_id'];
case 'VBSEO_URL_GROUPS_CATEGORY_LIST':
case 'VBSEO_URL_GROUPS_CATEGORY_LIST_PAGE':
if(!$selfurl_more)
$selfurl_more = '&do=categorylist';
case 'VBSEO_URL_GROUPS_ALL':
case 'VBSEO_URL_GROUPS_ALL_PAGE':
if(!$selfurl_more)
$selfurl_more .= '&do=grouplist';
case 'VBSEO_URL_GROUPS_PAGE':
case 'VBSEO_URL_GROUPS_HOME':
case 'VBSEO_URL_GROUPS':
if ($vbseo_arr['group_name'] && !$vbseo_arr['group_id'])
$vbseo_arr['group_id'] = vbseo_reverse_object('group', $vbseo_arr['group_name']);
$selfurl = ($selfurl ? $selfurl : 'group.') . VBSEO_VB_EXT;
$selfurl_more .= ($vbseo_arr['group_id'] ? '&groupid=' . $vbseo_arr['group_id'] : '') ;
if($vbseo_arr['picture_id'])
$selfurl_more .= '&'.VBSEO_PICID_URI.'='.$vbseo_arr['picture_id'];
if($vbseo_arr['page'])
$selfurl_more .= '&page='.$vbseo_arr['page'];
break;
case 'VBSEO_URL_TAGS_ENTRY':
case 'VBSEO_URL_TAGS_ENTRYPAGE':
if(VBSEO_REWRITE_UTF8_CONVERT && VBSEO_REWRITE_UTF8_SRC_CHARSET)
$vbseo_arr['tag'] = vbseo_convert_charset_any($vbseo_arr['tag'], VBSEO_REWRITE_UTF8_SRC_CHARSET, 'utf-8');
if(VBSEO_URL_TAGS_FILTER)
$vbseo_arr['tag'] = urlencode(vbseo_reverse_object('tag', $vbseo_arr['tag']));
else 
$vbseo_arr['tag'] = urlencode($vbseo_arr['tag']);
$selfurl_more = '?tag='.$vbseo_arr['tag'];
if($vbseo_arr['page'])
$selfurl_more .= '&page=' . $vbseo_arr['page'];
case 'VBSEO_URL_TAGS_HOME':
$selfurl = 'tags.' . VBSEO_VB_EXT ;
break;
case 'VBSEO_URL_CMS_ATTACHMENT':
preg_match('#^(\d+)(d\d+)?(t)?#', $vbseo_arr['attachment_id'], $atm);
$selfurl = 'attachment.' . VBSEO_VB_EXT . '?attachmentid=' . $atm[1] . (isset($atm[3])?'&thumb=1&stc=1':'');
break;
case 'VBSEO_URL_CMS_CATEGORY':
case 'VBSEO_URL_CMS_CATEGORY_PAGE':
$selfurl_more = '?r=category/'.$vbseo_arr['category_id'].'-'.$vbseo_arr['category_title'].
($vbseo_arr['page'] ? '/'.$vbseo_arr['page'] : '');
case 'VBSEO_URL_CMS_SECTION_LIST':
if(!$selfurl_more)
$selfurl_more = '?r=section/'.$vbseo_arr['section_id'].
($vbseo_arr['page'] ? '/'.$vbseo_arr['page'] : '');
case 'VBSEO_URL_CMS_AUTHOR_PAGE':
case 'VBSEO_URL_CMS_AUTHOR':
if(!$selfurl_more)
$selfurl_more = '?r=author/'.$vbseo_arr['user_id'].'-'.$vbseo_arr['user_name'].
($vbseo_arr['page'] ? '/'.$vbseo_arr['page'] : '');
$selfurl = 'list.' . VBSEO_VB_EXT ;
break;
case 'VBSEO_URL_CMS_ENTRY_PAGE':
$selfurl_more  = ($vbseo_arr['page'] ? '/view/'.$vbseo_arr['page'].'/' : '');
case 'VBSEO_URL_CMS_ENTRY_COMPAGE':
if(!$selfurl_more)
$selfurl_more  = '&page='.$vbseo_arr['page'];
case 'VBSEO_URL_CMS_ENTRY':
$selfurl_more = '?r='.$vbseo_arr['entry_id'].'-'.$vbseo_arr['entry_title'] . $selfurl_more;
case 'VBSEO_URL_CMS_SECTION':
case 'VBSEO_URL_CMS_SECTION_PAGE':
if(!$selfurl_more)
{
if(!$vbseo_arr['section_id'] && $vbseo_arr['section_title'])
$vbseo_arr['section_id'] = vbseo_reverse_object('cmsnode', $vbseo_arr['section_title']);
$selfurl_more = '?r='.$vbseo_arr['section_id'] .
($vbseo_arr['page'] ? '&page='.$vbseo_arr['page'] : '');
}
case 'VBSEO_URL_CMS_HOME':
$selfurl = 'content.' . VBSEO_VB_EXT ;
break;
}
if($selfurl)
{
vBSEO_Storage::set('url_vbseoed', true);
if($selfurl_more[0] == '&')$selfurl_more[0] = '?';
$selfurl .= $selfurl_more;
vbseo_set_self($selfurl);
$vbseo_found_fn = preg_replace('#\?.*$#', '', $selfurl);
$vbseo_found = true;
return true;
}
}
}
return false;
}
function vbseo_url_check_canonical(&$entry_path, &$path)
{
if(vBSEO_Storage::get('url_vbseoed'))
$path = $entry_path;
}
function vbseo_prep_format_replacements($foreignchars, $spacer, $morechars)
{
if ($foreignchars == 0)
{
$validchars = '\S';
$validset = '[^/]';
}
else
if ($foreignchars == 1)
{
$validchars = 'a-z\._';
$validset = '[' . $validchars . 'A-Z\d-]';
}
else
{
$validchars = 'a-z\._\\' . $spacer . 'ŠŽšžŸÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÑÒÓÔÕÖØÙÚÛÜÝàáâãäåçèéêëìíîïñòóôõöøùúûüýÿµ';
$validset = '[' . $validchars . 'A-Z\d-]';
}
$replace = array(
'#%attachment_id%#' => '([dt\d]+)',
'#%picture_id%#' => '([dt\d]+)',
'#%[a-z_]+_id%#' => '(\d+)',
'#%year%#' => '(\d+)',
'#%month%#' => '(\d+)',
'#%day%#' => '(\d+)',
'#%[a-z_]+_path%#' => '([' . $validchars . 'A-Z\d/-]+)',
'#%[a-z_]+_filename%#' => '(.+)',
'#%tag%#' => '(.+)',
'#%(album|group)_title%#' => '([^/]+)',
'#%[a-z_]+_name%#' => '([^/]+)',
'#%[a-z_]+_title%#' => '(' . $validset . '+)',
'#%[a-z_]+_ext%#' => '([^/]+)', 
'#%post_count%#' => '(\d*?)',
'#%letter%#' => '([a-z]|0|all)',
'#%[a-z_]*page%#' => '(\d+)',
'#%[a-z_]+%#' => '(' . $validset . ')+',
);
return $replace;
}
function vbseo_check_url_strict($format_name, $vbseo_url_)
{
return vbseo_check_url($format_name, $vbseo_url_, false, true);
}
function vbseo_check_url($format_name, $vbseo_url_, $allow_part = false, $strict_check = false)
{
global $vbseo_url_formats, $vbseo_url_suggest;
$expr = $vbseo_url_formats[$format_name];
if (!$expr)
{	
$replace = vbseo_prep_format_replacements(VBSEO_FILTER_FOREIGNCHARS, VBSEO_SPACER, VBSEO_REWRITE_MEMBER_MORECHARS);
$format = preg_quote(defined($format_name)?constant($format_name):'', '#');
$format = preg_replace(array_keys($replace), $replace, $format);
$expr = preg_replace('#%' . $validset . '+%#',
'[^/]+', $format);
}
if ($expr[strlen($expr) - 1] == '/' && $vbseo_url_[strlen($vbseo_url_)-1] != '/' && !file_exists($vbseo_url_)
)
{
$folder_type = true;
$expr .= '?';
}
else
$folder_type = false;
$expr = $allow_part ? '#' . $expr . '#' : '#^' . $expr . '$#';
$u2check = $vbseo_url_;
if (strstr($expr, 'http\://') && !strstr($u2check, 'http:')
&& !strstr(VBSEO_REQURL_FULL, VBSEO_TOPREL_FULL) )
$u2check = VBSEO_HTTP_DOMAIN  . '/' . $vbseo_url_;
if (preg_match($expr, $u2check, $matches))
{
if ($folder_type && $vbseo_url_)
{
$vbseo_url_suggest = $vbseo_url_ . '/';
if($strict_check)
return null;
}
$format1 = defined($format_name) ? constant($format_name) : '';
if (preg_match_all('#%([a-z_]+)%#', $format1, $matches2, PREG_PATTERN_ORDER))
$fields = array_values(array_unique($matches2[1]));
else
$fields = array();
$fieldCount = count($fields);
$results = array(1);
for($i = 0; $i < $fieldCount; $i++)
$results[$fields[$i]] = $matches[$i + 1];
return $results;
}
return null;
}
function vbseo_create_full_url($url)
{
global $vboptions, $vbulletin;
if (!strstr($url, '://'))
{
$bburl = $vboptions ? $vboptions['bburl'] : ($vbulletin ? $vbulletin->options['bburl'] : '');
if($url[0] == '/')
$url = preg_replace('#^(.*?//[^/]*).*#', '$1', $bburl) . $url;
else
$url = preg_replace('#/$#', '', $bburl) . '/' . $url;
}
return $url;
}
function vbseo_check_attachment_url()
{
global $found_object_ids,$vbseo_gcache;
$found_object_ids[VBSEO_PIC_STORAGE][] = ($atid = intval($_GET[VBSEO_PICID_URI]));
vbseo_get_object_info(VBSEO_PIC_STORAGE);
$found_object_ids['album'][] = $_GET['albumid'];
if($_GET['albumid'])
{
vbseo_get_object_info('album');
vbseo_get_user_info(array($vbseo_gcache['album'][$_GET['albumid']]['userid']));
$newurl = vbseo_album_url('VBSEO_URL_MEMBER_PICTURE_IMG', $q=array(
VBSEO_PICID_URI => intval($_GET[VBSEO_PICID_URI]),
'thumb' => strstr($_GET[VBSEO_PICID_URI], 't'),
'albumid' => $_GET['albumid'])
);
}else
{
$newurl = vbseo_attachment_url($atid);
if(!$newurl || strstr($newurl,'%'))
return;
}
$catturl = preg_replace('#\?.*#', '', VBSEO_REQURL);
if(!strstr($newurl,$catturl) &&
!strstr($newurl, preg_replace('#(\d)(d(\d+))?t?#', '$1', $catturl) ))
{
if($_GET['albumid'])
{
$db = vbseo_get_db();
$ainfo = $db->vbseodb_query_first($q="
SELECT pl.*
FROM " . vbseo_tbl_prefix("picturelegacy")." AS pl
INNER JOIN " . TABLE_PREFIX . "attachment AS a ON (pl.attachmentid = a.attachmentid)
WHERE pl.pictureid = " . intval($_GET[VBSEO_PICID_URI]) . "
" . ($_GET['albumid'] ? "AND pl.type = 'album' AND pl.primaryid = " . intval($_GET['albumid']) : "") . "
");
if($ainfo)
{
$found_object_ids[VBSEO_PIC_STORAGE] = array($ainfo[VBSEO_PICID_URI]);
vbseo_get_object_info(VBSEO_PIC_STORAGE);
$newurl = vbseo_album_url('VBSEO_URL_MEMBER_PICTURE_IMG', $q=array(
VBSEO_PICID_URI => $ainfo[VBSEO_PICID_URI],
'albumid' => $_GET['albumid']));
}
}
if($newurl)
{
vbseo_safe_redirect($newurl,array('albumid',VBSEO_PICID_URI), true);
exit;
}
}
}
function vbseo_check_stripsids()
{
global $vboptions;
if (!defined('VBSEO_STRIP_SIDS'))
{
$cookiesEnabled = (isset($_COOKIE[COOKIE_PREFIX . 'sessionhash']) || isset($HTTP_COOKIE_VARS[COOKIE_PREFIX . 'sessionhash']));
define('VBSEO_STRIP_SIDS',
($cookiesEnabled || 
((false === strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']))
&& ($_SERVER['HTTP_REFERER'] || !$_GET['s'])
)
) ? 1 : 0);
}
}
function vbseo_parse_query($str, &$params)
{
$params = array();
$pairs = explode('&', $str);
foreach($pairs as $pair) 
{
list($name, $value) = explode('=', $pair, 2);
$params[$name] = urldecode($value);
}
}
function vbseo_set_self($vbseo_url_)
{              
if (!isset($_SERVER))
{
$_ENV = &$HTTP_ENV_VARS;
$_SERVER = &$HTTP_SERVER_VARS;
$_GET = &$HTTP_GET_VARS;
$_POST = &$HTTP_POST_VARS;
$_REQUEST = &$HTTP_GET_VARS;
}
else
{
$GLOBALS['HTTP_SERVER_VARS'] = &$_SERVER;
}
if ($vbseo_url_[0] == '/')
$page = $vbseo_url_;
else
{
$page = VBSEO_TOPREL . $vbseo_url_;
if (strstr($page, '../'))
{
do
{
$ap = $page;
$page = preg_replace('#/?[^/]*/\.\.#', '', $ap, 1);
}
while ($page != $ap);
}
}
@list($basepage, $query) = explode('?', $page, 2);
$basepage = str_replace('//', '/', $basepage);
preg_match('#([^/]+)$#', $basepage, $pm);
$_SERVER['vbseo_fn'] = $pm[1];
$pagepath = dirname(vBSEO_Storage::path('vbseo')) . '/' . str_replace(VBSEO_TOPREL, '', $basepage);
$_SERVER['VBSEO_URI'] = $_SERVER['REQUEST_URI'];
$setvars = array($page, $basepage, $pagepath);
$setar = array('REQUEST_URI' => 0,
'SCRIPT_NAME' => 1,
'PHP_SELF' => 1,
'PATH_INFO' => 1,
'SCRIPT_FILENAME' => 2,
'PATH_TRANSLATED' => 2,
);
foreach($setar as $sa => $st)
{
$_SERVER[$sa] = $setvars[$st];
$_ENV[$sa] = $setvars[$st];
$GLOBALS[$sa] = $setvars[$st];
}
$unsets = array('REDIRECT_QUERY_STRING', 'REDIRECT_URL');
foreach($unsets as $ui => $us)
{
unset($_SERVER[$us]);
unset($_ENV[$us]);
unset($GLOBALS[$us]);
}
$_SERVER['argv'][0] = $query;
$GLOBALS['argv'][0] = $query;
if ($query)
{
$_SERVER['QUERY_STRING'] = $query;
$_ENV['QUERY_STRING'] = $query;
$GLOBALS['QUERY_STRING'] = $query;
vbseo_parse_query($query, $params);
while (list($name, $value) = each($params))
if(!$_REQUEST[$name])
{
$_REQUEST[$name] = $value;
$_GET[$name] = $value;
}
}
}
function vbseo_404_routine($vbseo_url_ = '')
{
$handle404 = VBSEO_404_HANDLE;
if (preg_match('#\.(jpg|gif|png|js|css)$#', $vbseo_url_) && ($handle404 == 0))
$handle404 = 1;
switch ($handle404)
{
case 1:
vbseo_404();
break;
case 2:
$vbseo_incf = VBSEO_404_CUSTOM;
if ($vbseo_incf[0] != '/')
$vbseo_incf = vBSEO_Storage::path('vb') . '/' . $vbseo_incf;
include($vbseo_incf);
break;
default:
$fhome = VBSEO_TOPREL;
vbseo_get_options();
global $vboptions;
if ($vboptions['forumhome'] && ($vboptions['forumhome'] != 'index'))
$fhome .= $vboptions['forumhome'] . '.' . VBSEO_VB_EXT;
header ("HTTP/1.x 301 Moved Permanently");
header ("Location: " . $fhome);
break;
}
}
function vbseo_404()
{
header ("HTTP/1.x 404 Not Found");
if (defined('VBSEO_STATUS_HEADER') && VBSEO_STATUS_HEADER)
header ("Status: 404 Not Found");
echo 'Page not found';
vbseo_close_db();
exit;
}
function vbseo_requested_url()
{
global $vbseo_url_, $vbseo_relpath;
if(isset($_GET['vbseourl']))
{
$vbseo_url_ =  $_GET['vbseourl'];
}else
{
list($vbseo_url_, $vbseo_url_par) = explode('?', VBSEO_REQURL);
$vbseo_url_ = urldecode($vbseo_url_);
}
if (ini_get("magic_quotes_gpc"))
{
$vbseo_url_ = stripslashes($vbseo_url_);
}
$vbseo_url_ = preg_replace('#[\x00-\x1F]#', '', $vbseo_url_);
if (preg_match('#^(.*?\.php)/(.*)$#', $vbseo_url_, $vu_match) &&
file_exists($vu_match[1]))
$vbseo_url_ = $vu_match[1];
define('VBSEO_BASEURL', basename($vbseo_url_));
$vbseo_relpath = '';
if(isset($_GET['vbseorelpath']) && $_GET['vbseourl'])
$vbseo_relpath = $_GET['vbseorelpath'];
$vbseo_relpath = preg_replace('#[\x00-\x1F]#', '', $vbseo_relpath);
if(substr_count(VBSEO_TOPREL,'/') <= substr_count($vbseo_relpath,'..'))
$vbseo_relpath = '';
if($vbseo_relpath && !file_exists($vbseo_relpath))
$vbseo_relpath = '';
define('VBSEO_RELPATH', $vbseo_relpath != '');
}
function vbseo_chdir($dirname)
{
$_fulldir = getcwd() . '/' . $dirname;
if( ($dirname[0] == '/') || strstr($dirname, './../')
|| (is_writable($_fulldir) && !is_writable(getcwd())))
{
vbseo_404();
}
else
{
chdir($_fulldir);
}
}
function vbseo_security_check($vbseo_url_)
{
global $vbseo_relpath;
if(
($vbseo_url_[0] == '/') || 
strstr($vbseo_url_, '/../') || 
strstr($vbseo_url_, '://') || 
strstr(VBSEO_REQURL, 'vbseourl=') || 
(substr($vbseo_url_, 0, 3) == '../') || 
(isset($vbseo_relpath) && 
(($vbseo_relpath[0] == '/') || strstr($vbseo_relpath, './../')) ) ||
strstr($vbseo_url_, '<script') ||
strstr(urldecode($vbseo_url_), '<script')
)
return true;
}
function vbseo_url_autoadjust($vbse_rurl, $excpars = array(), $force_redirect = false)
{
$vbse_rurl_enc = urldecode($vbse_rurl);
$vbse_rurl_deenc = urldecode($vbse_rurl_enc);
$vbseo_requrl2 = preg_replace('#\?.*$#', '', VBSEO_REQURL);
$urls_not_match = 
($vbse_rurl != $vbseo_requrl2)
&& (strstr(VBSEO_REQURL_FULL,VBSEO_TOPREL_FULL)) 
&& ($vbse_rurl_deenc != $vbseo_requrl2)
&& ($vbse_rurl_deenc != urldecode(VBSEO_REQURL) )
&& ($vbse_rurl != VBSEO_TOPREL_FULL . $vbseo_requrl2)
&&
(($vbse_rurl==$vbse_rurl_enc) || ($vbse_rurl != substr($vbse_rurl_enc, 0, strlen($vbse_rurl))) 
);
if($urls_not_match || $force_redirect )
{
vbseo_safe_redirect($vbse_rurl, $excpars);
}
}
function vbseo_safe_redirect($vbseo_url_, $unset_par = array(), $unset_all = false)
{
$vbroot = VBSEO_TOPREL_FULL;
if (defined('VBSEO_UNREG_EXPIRED'))return;
if ($vbroot[strlen($vbroot)-1] != '/') $vbroot .= '/';
if (!$unset_all)
{
$unset_par = array_merge($unset_par,
array('grab_output', 'goto', 
'vbseourl', 'vbseorelpath', 'vbseoaddon')
);
if(!defined('VBSEO_STRIP_SIDS') || VBSEO_STRIP_SIDS)
$unset_par[] = 's';
$qstring = $_SERVER['QUERY_STRING'];
if (strstr($vbseo_url_, '?'))
{
list($vbseo_url_, $qstring) = explode('?', $vbseo_url_);
}
$pars = $qstring ? 
explode('&', str_replace('&amp;', '&', preg_replace('|#.*|', '', $qstring))) :
array();
$req = '';
for($i2 = 0; $i2 < count($pars); $i2++)
{
list($k, $v) = explode('=', $pars[$i2], 2);
if (!in_array($k, $unset_par) && !strstr($k, 'redirect_')
&& ($k || $v)
)
$req .= ($req?'&':'') . $k . '=' . $v;
}
}
if ($vbseo_url_[0] == '/') $vbseo_url_ = substr($vbseo_url_, 1);
$fulluri = (strstr($vbseo_url_, '://')?'':$vbroot) . $vbseo_url_;
if ($req)
{
$fulluri = preg_replace('#^([^\#]*)#', '$1?' . $req, $fulluri);
}
header ("HTTP/1.x 301 Moved Permanently");
if (defined('VBSEO_STATUS_HEADER') && VBSEO_STATUS_HEADER)
header ("Status: 301 Moved Permanently");
$fulluri = preg_replace('#[\r\n]#', '', $fulluri);
header ("Location: $fulluri");
vbseo_close_db();
exit();
}
function vbseo_filter_text($text, $allowchars = null, $filter_stop_words = true, $reversable = false, $keep_tailspaces = false)
{
global $vbseo_trans_table;
static $trarr_table;
if ($allowchars) $allowchars = preg_quote($allowchars, '#');
$validchars = 'a-z\d\_' . $allowchars;
$q_spacer = preg_quote(VBSEO_SPACER, '#');
if (!$reversable || !VBSEO_REWRITE_MEMBER_MORECHARS)
{
if (defined('VBSEO_TRANSLIT_CALLBACK') && VBSEO_TRANSLIT_CALLBACK && function_exists(VBSEO_TRANSLIT_CALLBACK))
{
$validchars = '[^/\\#\,\.\+\!\?\:\s\)\(]';
eval('$text = ' . VBSEO_TRANSLIT_CALLBACK . '($text);');
}
else
if (VBSEO_FILTER_FOREIGNCHARS == 1)
{
if(!$reversable)$text = str_replace('\'', '', $text);
$validchars = 'a-z\d\_';
}
else
if (VBSEO_FILTER_FOREIGNCHARS == 0)
{
if(VBSEO_REWRITE_UTF8_CONVERT)
$text = vbseo_convert_charset_any($text, 'utf-8', vbseo_current_charset());
$validchars = '[^/\\#\,\.\+\!\?\:\s\)\(]';
if (!$trarr_table)
$trarr_table = $GLOBALS['vbseo_custom_char_replacement'];
if(!isset($trarr_table['\'']))
$text = str_replace('\'', $reversable ? VBSEO_SPACER : '', $text);
$text = strtr($text, $trarr_table);
}
else
{
if (!$trarr_table)
$trarr_table =
@array_merge(
array('Þ' => 'th', 'þ' => 'th', 'Ð' => 'dh', 'ð' => 'dh', 'ß' => 'ss',
'Œ' => 'oe', 'œ' => 'oe', 'Æ' => 'ae', 'æ' => 'ae',
'Ä' => 'ae', 'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue'
), $GLOBALS['vbseo_custom_char_replacement']);
if(!isset($trarr_table['\'']))
$text = str_replace('\'', $reversable ? VBSEO_SPACER : '', $text);
$text = strtr(
strtr($text,
$trarr_table
),
'ŠŽšžŸÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÑÒÓÔÕÖØÙÚÛÜÝàáâãäåçèéêëìíîïñòóôõöøùúûüýÿµ',
'szszyaaaaaaceeeeiiiinoooooouuuuyaaaaaaceeeeiiiinoooooouuuuyyu'
);
if (!isset($vbseo_trans_table))
{
$vbseo_trans_table = get_html_translation_table(HTML_ENTITIES);
$vbseo_trans_table = array_flip($vbseo_trans_table);
}
$text = strtr($text, $vbseo_trans_table);
}
}
if($validchars[1] == '^')
{
$validchars = str_replace($allowchars, '', $validchars);
$invalidchars =  "[" . substr($validchars, 2);
}else
{
$validchars .= $allowchars;
$invalidchars =  "[^$validchars]";
}
$text = (VBSEO_UTF8_SUPPORT && function_exists('mb_strtolower')) ? 
mb_strtolower ($text, 'UTF-8') 
: strtolower($text);
if ($reversable && VBSEO_REWRITE_MEMBER_MORECHARS)
{
$text = str_replace('/', VBSEO_SPACER, $text);
$text = urlencode($text);
}
else
{
$text = str_replace('&amp;', ' and ', $text);
if (preg_match('#\S#', $text2 = preg_replace('#&\#?[a-z\d]+;#i', ' ', $text)))
$text = $text2;
$text = str_replace('&', ' and ', $text);
if ($validchars)
$text = preg_replace('#' . $invalidchars . '+#s', VBSEO_SPACER, $text);
}
$repl_wl = true;
$repl_sw = ($filter_stop_words && VBSEO_FILTER_STOPWORDS && VBSEO_STOPWORDS);
$w_cnt = VBSEO_URL_PART_MAX + 1;
if (VBSEO_SPACER == '_')
$text = str_replace('_', ' ', $text);
if (!$reversable && VBSEO_KEEP_STOPWORDS_SHORT && $repl_sw && VBSEO_URL_PART_MAX > 0)
{
preg_match_all('#([^' . $q_spacer . ' ]+)#s' . (VBSEO_UTF8_SUPPORT?'u':''), $text, $v_tm1);
preg_match_all('#\b(' . VBSEO_STOPWORDS . ')\b#s', $text, $v_tm2);
$s_cnt = count($v_tm2[1]);
$w_cnt = count($v_tm1[1]) - $s_cnt;
}
else
{
if ($reversable || (VBSEO_URL_PART_MAX == 0))
$repl_wl = false;
}
if ($repl_sw)
{
if ($w_cnt >= VBSEO_URL_PART_MAX)
{
$text = preg_replace('#\b(' . VBSEO_STOPWORDS . ')\b#i', '', $t2 = $text);
}
else
{
$as_cnt = VBSEO_URL_PART_MAX - $w_cnt;
$text = preg_replace('#\b(' . VBSEO_STOPWORDS . ')\b#ei', '(($as_cnt--)>0)?\'$1\':\'\'', $t2 = $text);
}
if (!$text) $text = $t2;
}
if (VBSEO_SPACER == '_')
$text = str_replace(' ', '_', $text);
if ($repl_wl)
$text = preg_replace('#(([^' . $q_spacer . ']+' . $q_spacer . '*){' . VBSEO_URL_PART_MAX . '}).*$#s' . (VBSEO_UTF8_SUPPORT?'u':''), '\\1', $text);
if (!($reversable && VBSEO_REWRITE_MEMBER_MORECHARS) &&
(!defined('VBSEO_REWRITE_NO_URLENCODING') || !VBSEO_REWRITE_NO_URLENCODING)
)
$text = urlencode($text);
if (VBSEO_SPACER != '' && !($reversable && !$keep_tailspaces && VBSEO_REWRITE_MEMBER_MORECHARS))
{
$expr = ($reversable && $keep_tailspaces) ?
array('#(' . $q_spacer . '){2,}#'
)
:
array('#(' . $q_spacer . '){2,}#',
'#^(' . $q_spacer . ')+#',
'#(' . $q_spacer . ')$#'
);
$repl = array(VBSEO_SPACER, '', '');
$text = preg_replace($expr, $repl, $text);
}
if (!$text) $text = VBSEO_APPEND_CHAR;
return $text;
}
function vbseo_unfilter_text($text, $anyspace = false)
{
if (VBSEO_FILTER_FOREIGNCHARS == 1)
$replace = array(
(VBSEO_SPACER . 'and' . VBSEO_SPACER) => '#**%**#',
VBSEO_SPACER => '(&[\#\da-z]*;|[^a-z\d])+',
);
else
$replace = array('ue' => '(ue|ü)',
'oe' => '(oe|ö|_|_)',
'ae' => '(ae|ä|Æ|æ)',
'ss' => '(ss|ß)',
(VBSEO_SPACER . 'and' . VBSEO_SPACER) => '#**%**#',
's' => '[sŠš]',
'z' => '[zŽž]',
'y' => '[yŸÝýÿ]',
'a' => '[aÀÁÂÃÄÅàáâãäå]',
'c' => '[cÇc]',
'e' => '[eÈÉÊËèéêë]',
'i' => '[iÌÍÎÏìíîï]',
'n' => '[nÑñ]',
'o' => '[oÒÓÔÕÖØòóôõöø]',
'th' => '(th|Þ|þ)',
'dh' => '(dh|ð|Ð)',
'u' => '([uÙÚÛÜùúûüµ]|u|Æ|æ)',
VBSEO_SPACER => '(&[\#\da-z]*;|[^a-z\d])*',
);
if($anyspace)
$replace[VBSEO_SPACER] = '.*';
$replace2 = array();
if(is_array($GLOBALS['vbseo_custom_char_replacement']))
foreach($GLOBALS['vbseo_custom_char_replacement'] as $k => $v)
{
if (isset($replace[$v]))
{
if ($replace[$v][0] == '(')
$replace[$v] = "($k|" . substr($replace[$v], 1);
else
$replace[$v] = "($k|" . $replace[$v] . ")";
}
else
{
if (isset($replace[$v[0]]))
$replace2[$v] = "($k|$v)";
else
$replace[$v] = "($k|$v)";
}
}
$replace = array_merge($replace2, $replace);
$replace['#**%**#'] = '[^a-z\d]*(and|&amp;|&)[^a-z\d]*';
$text = str_replace(array_keys($replace), $replace, $text);
return ('^(&[\#\da-z]*;|[^a-z\d])*' . $text . '(&[a-z]*;|[^a-z\d])*$');
}
function vbseo_checkurl_cms($vbseo_url_, $pass)
{
$urldomain = defined('VBSEO_URL_CMS_DOMAIN') ? VBSEO_URL_CMS_DOMAIN : '';
if(!$urldomain && ($pass == 1)
||($urldomain && ($pass == 2)))
return false;
$domainmatch = !$urldomain || (substr(VBSEO_REQURL_FULL, 0, strlen($urldomain)) == $urldomain);
if(!$domainmatch) return false;
if (VBSEO_REWRITE_CMS && VBSEO_VB_CMS)
{
if(vbseo_check_multi_urls(
array(
'VBSEO_URL_CMS_ENTRY_COMPAGE', 
'VBSEO_URL_CMS_ATTACHMENT',
'VBSEO_URL_CMS_ENTRY_PAGE', 
'VBSEO_URL_CMS_ENTRY', 
'VBSEO_URL_CMS_HOME', 
'VBSEO_URL_CMS_CATEGORY_PAGE', 
'VBSEO_URL_CMS_CATEGORY', 
'VBSEO_URL_CMS_AUTHOR_PAGE', 
'VBSEO_URL_CMS_AUTHOR', 
'VBSEO_URL_CMS_SECTION_LIST',
'VBSEO_URL_CMS_SECTION_PAGE',
'VBSEO_URL_CMS_SECTION',
), 
$vbseo_url_) )
{
return true;
}
}
return false;
}
function vbseo_checkurl_blog($vbseo_url_)
{
global $vbseo_arr, $vbseo_url_suggest, $vbseo_found, $vbseo_found_fn;
$urldomain = defined('VBSEO_URL_BLOG_DOMAIN') ? VBSEO_URL_BLOG_DOMAIN : '';
if(!$urldomain && ($pass == 1)
||($urldomain && ($pass == 2)))
return false;
$domainmatch = !$urldomain || (substr(VBSEO_REQURL_FULL, 0, strlen($urldomain)) == $urldomain);
if(!$domainmatch) return false;
if (VBSEO_REWRITE_BLOGS && VBSEO_VB_BLOG && (
($vbseo_arr29 = vbseo_check_url('VBSEO_URL_BLOG_HOME', $vbseo_url_))||
($vbseo_arr5 = vbseo_check_url('VBSEO_URL_BLOG_NEXT', $vbseo_url_)) ||
($vbseo_arr6 = vbseo_check_url('VBSEO_URL_BLOG_PREV', $vbseo_url_)) ||
($vbseo_arr27 = vbseo_check_url('VBSEO_URL_BLOG_ENTRY_PAGE', $vbseo_url_)) ||
($vbseo_arr26 = vbseo_check_url('VBSEO_URL_BLOG_ENTRY_REDIR', $vbseo_url_)) ||
($vbseo_arr = vbseo_check_url('VBSEO_URL_BLOG_ENTRY', $vbseo_url_)) ||
($vbseo_arr11 = vbseo_check_url('VBSEO_URL_BLOG_ATT', $vbseo_url_)) ||
($vbseo_arr23 = vbseo_check_url('VBSEO_URL_BLOG_BLIST_PAGE', $vbseo_url_)) ||
($vbseo_arr15 = vbseo_check_url('VBSEO_URL_BLOG_BLIST', $vbseo_url_)) ||
($vbseo_arr22 = vbseo_check_url('VBSEO_URL_BLOG_BEST_BLOGS_PAGE', $vbseo_url_)) ||
($vbseo_arr12 = vbseo_check_url('VBSEO_URL_BLOG_BEST_BLOGS', $vbseo_url_)) ||
($vbseo_arr21 = vbseo_check_url('VBSEO_URL_BLOG_BEST_ENT_PAGE', $vbseo_url_)) ||
($vbseo_arr13 = vbseo_check_url('VBSEO_URL_BLOG_BEST_ENT', $vbseo_url_)) ||
($vbseo_arr31 = vbseo_check_url('VBSEO_URL_BLOG_LAST_ENT_PAGE', $vbseo_url_)) ||
($vbseo_arr32 = vbseo_check_url('VBSEO_URL_BLOG_LAST_ENT', $vbseo_url_)) ||
($vbseo_arr24 = vbseo_check_url('VBSEO_URL_BLOG_DAY_PAGE', $vbseo_url_)) ||
($vbseo_arr10 = vbseo_check_url('VBSEO_URL_BLOG_DAY', $vbseo_url_)) ||
($vbseo_arr25 = vbseo_check_url('VBSEO_URL_BLOG_MONTH_PAGE', $vbseo_url_)) ||
($vbseo_arr9 = vbseo_check_url('VBSEO_URL_BLOG_MONTH', $vbseo_url_)) ||
($vbseo_arr16 = vbseo_check_url('VBSEO_URL_BLOG_UDAY', $vbseo_url_)) ||
($vbseo_arr17 = vbseo_check_url('VBSEO_URL_BLOG_UMONTH', $vbseo_url_)) ||
($vbseo_arr7 = vbseo_check_url('VBSEO_URL_BLOG_FEEDUSER', $vbseo_url_)) ||
($vbseo_arr8 = vbseo_check_url('VBSEO_URL_BLOG_FEED', $vbseo_url_)) ||
(VBSEO_REWRITE_BLOGS_TAGS_ENTRY && ($vbseo_arr34 = vbseo_check_url('VBSEO_URL_BLOG_TAGS_ENTRY_PAGE', $vbseo_url_))) ||
(VBSEO_REWRITE_BLOGS_TAGS_ENTRY && ($vbseo_arr35 = vbseo_check_url('VBSEO_URL_BLOG_TAGS_ENTRY', $vbseo_url_))) ||
(VBSEO_REWRITE_BLOGS_TAGS_ENTRY && ($vbseo_arr33 = vbseo_check_url('VBSEO_URL_BLOG_TAGS_HOME', $vbseo_url_))) ||
($vbseo_arr20 = vbseo_check_url('VBSEO_URL_BLOG_LIST_PAGE', $vbseo_url_)) ||
($vbseo_arr4 = vbseo_check_url('VBSEO_URL_BLOG_LIST', $vbseo_url_)) ||
($vbseo_arr18 = vbseo_check_url('VBSEO_URL_BLOG_CLIST_PAGE', $vbseo_url_)) ||
($vbseo_arr19 = vbseo_check_url('VBSEO_URL_BLOG_CLIST', $vbseo_url_)) ||
(VBSEO_REWRITE_BLOGS_CUSTOM && ($vbseo_arr30 = vbseo_check_url('VBSEO_URL_BLOG_CUSTOM', $vbseo_url_))) ||
($vbseo_arr28 = vbseo_check_url('VBSEO_URL_BLOG_USER_PAGE', $vbseo_url_)) ||
($vbseo_arr36 = vbseo_check_url('VBSEO_URL_BLOG_GLOB_CAT_PAGE', $vbseo_url_)) ||
($vbseo_arr36 = vbseo_check_url('VBSEO_URL_BLOG_GLOB_CAT', $vbseo_url_)) ||
($vbseo_arr2 = vbseo_check_url('VBSEO_URL_BLOG_CAT_PAGE', $vbseo_url_)) ||
($vbseo_arr3 = vbseo_check_url('VBSEO_URL_BLOG_USER', $vbseo_url_))||
($vbseo_arr2 = vbseo_check_url('VBSEO_URL_BLOG_CAT', $vbseo_url_)) 
)
)
{
if ($vbseo_arr)
{
$_vsself = (VBSEO_BLOGENTRY_URI . '.' . VBSEO_VB_EXT . '?b=' . $vbseo_arr['blog_id']);
}
else
if ($vbseo_arr34 || ($vbseo_arr34 = $vbseo_arr35))
{
if(VBSEO_URL_TAGS_FILTER)
$vbseo_arr['tag'] = urlencode(vbseo_reverse_object('tag', $vbseo_arr34['tag']));
else 
$vbseo_arr['tag'] = urlencode($vbseo_arr34['tag']);
$_vsself = ('blog.' . VBSEO_VB_EXT . '?tag='.$vbseo_arr['tag'].($vbseo_arr34['page']?'&page='.$vbseo_arr34['page']:'') );
}
else
if ($vbseo_arr33)
{
$_vsself = ('blog_tag.' . VBSEO_VB_EXT );
}
else
if ($vbseo_arr31)
{
$_vsself = ('blog.' . VBSEO_VB_EXT . '?do=list&span=24&page='.$vbseo_arr31['page'] );
}
else
if ($vbseo_arr32)
{
$_vsself = ('blog.' . VBSEO_VB_EXT . '?do=list&span=24');
}
else
if ($vbseo_arr30)
{
$_vsself = ('blog.' . VBSEO_VB_EXT . '?cp= '.$vbseo_arr30['page_id'] );
}
else
if ($vbseo_arr27)
{
$_vsself = (VBSEO_BLOGENTRY_URI . '.' . VBSEO_VB_EXT . '?b=' . $vbseo_arr27['blog_id'] . '&page=' . $vbseo_arr27['page']);
}
else
if ($vbseo_arr29)
{
$_vsself = ('blog.' . VBSEO_VB_EXT );
}
else
if ($vbseo_arr36)
{
if (!$vbseo_arr36['category_id'])
$vbseo_arr36['category_id'] = vbseo_reverse_object('blogcat', $vbseo_arr36['category_title'], 0);
$_vsself = ('blog.' . VBSEO_VB_EXT . '?' . VBSEO_BLOG_CATID_URI . '=' . $vbseo_arr36['category_id'].($vbseo_arr36['page']?'&page=' . $vbseo_arr36['page']:''));
}
else
if ($vbseo_arr2)
{
if (empty($vbseo_arr2['user_id']) && isset($vbseo_arr2['user_name']))
$vbseo_arr2['user_id'] = vbseo_reverse_username($vbseo_arr2['user_name']);
if(isset($vbseo_arr2['category_id']) && $vbseo_arr2['category_id']==0)
$vbseo_arr2['category_id'] = -1;
if (!$vbseo_arr2['category_id'])
$vbseo_arr2['category_id'] = vbseo_reverse_object('blogcat', $vbseo_arr2['category_title'], $vbseo_arr2['user_id']);
$_vsself = ('blog.' . VBSEO_VB_EXT . '?u=' . $vbseo_arr2['user_id'] . ($vbseo_arr2['page']?'&page=' . $vbseo_arr2['page']:'') . '&' . VBSEO_BLOG_CATID_URI . '=' . ($vbseo_arr2['category_id']?$vbseo_arr2['category_id']:-1));
}
else
if ($vbseo_arr7)
{
if (empty($vbseo_arr7['user_id']) && isset($vbseo_arr7['user_name']))
$vbseo_arr7['user_id'] = vbseo_reverse_username($vbseo_arr7['user_name']);
$_vsself = ('blog_external.' . VBSEO_VB_EXT . '?bloguserid=' . $vbseo_arr7['user_id']);
}
else
if ($vbseo_arr8)
{
$_vsself = ('blog_external.' . VBSEO_VB_EXT);
}
else
if ($vbseo_arr26)
{
$_vsself = ('blog.' . VBSEO_VB_EXT . '?bt='.$vbseo_arr26['comment_id']);
}
else
if ($vbseo_arr4)
{
$_vsself = ('blog.' . VBSEO_VB_EXT . '?do=list');
}
else
if ($vbseo_arr20)
{
$_vsself = ('blog.' . VBSEO_VB_EXT . '?do=list&page=' . $vbseo_arr20['page']);
}
else
if ($vbseo_arr23)
{
$_vsself = ('blog.' . VBSEO_VB_EXT . '?do=bloglist&page=' . $vbseo_arr23['page']);
}
else
if ($vbseo_arr15)
{
$_vsself = ('blog.' . VBSEO_VB_EXT . '?do=bloglist');
}
else
if ($vbseo_arr5)
{
$_vsself = (VBSEO_BLOGENTRY_URI . '.' . VBSEO_VB_EXT . '?b=' . $vbseo_arr5['blog_id'] . '&goto=next');
}
else
if ($vbseo_arr6)
{
$_vsself = (VBSEO_BLOGENTRY_URI . '.' . VBSEO_VB_EXT . '?b=' . $vbseo_arr6['blog_id'] . '&goto=prev');
}
else
if ($vbseo_arr25)
{
$_vsself = ('blog.' . VBSEO_VB_EXT . '?do=list&page=' . $vbseo_arr25['page'] . '&y=' . $vbseo_arr25['year'] . '&m=' . $vbseo_arr25['month']);
}
else
if ($vbseo_arr9)
{
$_vsself = ('blog.' . VBSEO_VB_EXT . '?do=list&y=' . $vbseo_arr9['year'] . '&m=' . $vbseo_arr9['month']);
}
else
if ($vbseo_arr24)
{
$_vsself = ('blog.' . VBSEO_VB_EXT . '?do=list&page=' . $vbseo_arr24['page'] . '&y=' . $vbseo_arr24['year'] . '&m=' . $vbseo_arr24['month'] . '&d=' . $vbseo_arr24['day']);
}
else
if ($vbseo_arr10)
{
$_vsself = ('blog.' . VBSEO_VB_EXT . '?do=list&y=' . $vbseo_arr10['year'] . '&m=' . $vbseo_arr10['month'] . '&d=' . $vbseo_arr10['day']);
}
else
if ($vbseo_arr16)
{
if (empty($vbseo_arr16['user_id']) && isset($vbseo_arr16['user_name']))
$vbseo_arr16['user_id'] = vbseo_reverse_username($vbseo_arr16['user_name']);
$_vsself = ('blog.' . VBSEO_VB_EXT . '?u=' . $vbseo_arr16['user_id'] . '&y=' . $vbseo_arr16['year'] . '&m=' . $vbseo_arr16['month'] . '&d=' . $vbseo_arr16['day']);
}
else
if ($vbseo_arr17)
{
if (empty($vbseo_arr17['user_id']) && isset($vbseo_arr17['user_name']))
$vbseo_arr17['user_id'] = vbseo_reverse_username($vbseo_arr17['user_name']);
$_vsself = ('blog.' . VBSEO_VB_EXT . '?u=' . $vbseo_arr17['user_id'] . '&y=' . $vbseo_arr17['year'] . '&m=' . $vbseo_arr17['month'] . '&d=' . $vbseo_arr17['day']);
}
else
if ($vbseo_arr3)
{
if (empty($vbseo_arr3['user_id']) && isset($vbseo_arr3['user_name']))
$vbseo_arr3['user_id'] = vbseo_reverse_username($vbseo_arr3['user_name']);
if($vbseo_arr3['user_id'])
$_vsself = ('blog.' . VBSEO_VB_EXT . '?u=' . $vbseo_arr3['user_id']);
}
else
if ($vbseo_arr28)
{
if (empty($vbseo_arr28['user_id']) && isset($vbseo_arr28['user_name']))
$vbseo_arr28['user_id'] = vbseo_reverse_username($vbseo_arr28['user_name']);
$_vsself = ('blog.' . VBSEO_VB_EXT . '?u=' . $vbseo_arr28['user_id'] . '&page=' . $vbseo_arr28['page']);
}
else
if ($vbseo_arr11)
{
preg_match('#^(\d+)(d\d+)?(t)?#', $vbseo_arr11['attachment_id'], $atm);
$_vsself = (VBSEO_BLOGATT_URI . '.' . VBSEO_VB_EXT . '?attachmentid=' . $atm[1] . '&d=' . $atm[2] . (isset($atm[3])?'&thumb=1':''));
}
else
if ($vbseo_arr22)
{
$_vsself = ('blog.' . VBSEO_VB_EXT . '?do=bloglist&blogtype=best&page=' . $vbseo_arr22['page']);
}
else
if ($vbseo_arr12)
{
$_vsself = ('blog.' . VBSEO_VB_EXT . '?do=bloglist&blogtype=best');
}
else
if ($vbseo_arr21)
{
$_vsself = ('blog.' . VBSEO_VB_EXT . '?do=list&blogtype=best&page=' . $vbseo_arr21['page']);
}
else
if ($vbseo_arr13)
{
$_vsself = ('blog.' . VBSEO_VB_EXT . '?do=list&blogtype=best');
}
else
if ($vbseo_arr18)
{
$_vsself = ('blog.' . VBSEO_VB_EXT . '?do=comments&page=' . $vbseo_arr18['page']);
}
else
if ($vbseo_arr19)
{
$_vsself = ('blog.' . VBSEO_VB_EXT . '?do=comments');
}
if (!$vbseo_url_suggest)
{
$vbseo_found = true;
vbseo_set_self($_vsself);
$vbseo_found_fn = $_SERVER['vbseo_fn'];
}
return true;
}
return false;
}
?>