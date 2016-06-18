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
$selfurl = 'picture.';
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
$selfurl_more .= '&pictureid='.$vbseo_arr['picture_id'];
if($vbseo_arr['page'])
$selfurl_more .= '&page='.$vbseo_arr['page'];
break;
case 'VBSEO_URL_TAGS_ENTRY':
case 'VBSEO_URL_TAGS_ENTRYPAGE':
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
}
if($selfurl)
{
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
if (strstr($expr, 'http\\://') && !strstr($u2check, 'http:')
&& !strstr(VBSEO_REQURL_FULL, VBSEO_TOPREL_FULL) )
$u2check = VBSEO_HTTP_DOMAIN  . '/' . $vbseo_url_;
if (preg_match($expr, $u2check, $matches))
{
if ($folder_type)
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
$url = preg_replace('#/$#', '', $bburl) . '/' . $url;
}
return $url;
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
$pagepath = dirname(VBSEO_DIRNAME) . '/' . str_replace(VBSEO_TOPREL, '', $basepage);
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
$vbseo_incf = VBSEO_DIRNAME . '/../' . $vbseo_incf;
include($vbseo_incf);
break;
default:
$fhome = VBSEO_TOPREL;
vbseo_get_options();
global $vboptions;
if ($vboptions['forumhome'] && ($vboptions['forumhome'] != 'index'))
$fhome .= $vboptions['forumhome'] . '.' . VBSEO_VB_EXT;
Header ("HTTP/1.x 301 Moved Permanently");
Header ("Location: " . $fhome);
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
if (!VBSEO_ENABLED && preg_match('#^(.*?\.php)/(.*)$#', $vbseo_url_, $vu_match) &&
file_exists($vu_match[1]))
$vbseo_url_ = $vu_match[1];
define('VBSEO_BASEURL', basename($vbseo_url_));
$vbseo_relpath = '';
if(isset($_GET['vbseorelpath']))
$vbseo_relpath = $_GET['vbseorelpath'];
$vbseo_relpath = preg_replace('#[\x00-\x1F]#', '', $vbseo_relpath);
if(substr_count(VBSEO_TOPREL,'/') <= substr_count($vbseo_relpath,'..'))
$vbseo_relpath = '';
if($vbseo_relpath && !file_exists($vbseo_relpath))
$vbseo_relpath = '';
define('VBSEO_RELPATH', $vbseo_relpath != '');
}
function vbseo_security_check($vbseo_url_)
{
global $vbseo_relpath;
if(
($vbseo_url_[0] == '/') || 
strstr($vbseo_url_, '/../') || 
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
&& (
strstr(VBSEO_REQURL_FULL,VBSEO_TOPREL_FULL) ||
($vbse_rurl != $vbse_rurl)) 
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
array('grab_output', 'goto', 's',
'vbseourl', 'vbseorelpath', 'vbseoaddon')
);
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
if ($allowchars) $allowchars .= preg_quote($allowchars, '#');
$validchars = 'a-z\d\_' . $allowchars;
$q_spacer = preg_quote(VBSEO_SPACER, '#');
if (!$reversable || !VBSEO_REWRITE_MEMBER_MORECHARS)
{
if (defined('VBSEO_TRANSLIT_CALLBACK') && VBSEO_TRANSLIT_CALLBACK && function_exists(VBSEO_TRANSLIT_CALLBACK))
{
$validchars = '[^/\\#\,\.\+\!\?\:\s\)\(' . $allowchars . ']';
eval('$text = ' . VBSEO_TRANSLIT_CALLBACK . '($text);');
}
else
if (VBSEO_FILTER_FOREIGNCHARS == 1)
{
if(!$reversable)$text = str_replace('\'', '', $text);
$validchars = 'a-z\d\_' . $allowchars;
}
else
if (VBSEO_FILTER_FOREIGNCHARS == 0)
{
$validchars = '[^/\\#\,\.\+\!\?\:\s\)\(]';
$text = str_replace('\'', $reversable ? VBSEO_SPACER : '', $text);
if (!$trarr_table)
$trarr_table = $GLOBALS['vbseo_custom_char_replacement'];
$text = strtr($text, $trarr_table);
}
else
{
$text = str_replace('\'', $reversable ? VBSEO_SPACER : '', $text);
if (!$trarr_table)
$trarr_table =
array_merge(
array('Þ' => 'th', 'þ' => 'th', 'Ð' => 'dh', 'ð' => 'dh', 'ß' => 'ss',
'Œ' => 'oe', 'œ' => 'oe', 'Æ' => 'ae', 'æ' => 'ae',
'Ä' => 'ae', 'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue'
), $GLOBALS['vbseo_custom_char_replacement']);
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
$invalidchars = ($validchars[1] == '^') ? "[" . substr($validchars, 2) : "[^$validchars]";
$text = strtolower($text);
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
$text = preg_replace('#\b(' . VBSEO_STOPWORDS . ')\b#ei', '(($as_cnt--)>0)?"$1":""', $t2 = $text);
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
?>