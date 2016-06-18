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

if(!defined('VBSEO_IS_VBSEOCP')) exit;
class VBSEOCP_XML {
var $xmldoc = '';
var $charset = 'UTF-8';
function start_xml($charset = '')
{
if($charset)
$this->charset = $charset;
$this->opentags = array();
}
function send_xml($send = true)
{
if($send)
header('Content-type: text/xml; charset=' . $this->charset);
$this->xmldoc = '<?xml version="1.0" encoding="'.$this->charset.'"?'.">\n" . $this->xmldoc;
return $this->xmldoc;
}
function add_tag($tag, $value)
{
if(is_array($value))
{
$this->start_tag($tag);
foreach($value as $k=>$v)
{
if(preg_match('#^\d+$#', $k))
$k = $tag . $k;
$this->add_tag($k,$v);
}
$this->end_tag($tag);
}else
{
if(preg_match('#[^\w ]#', $value))
$value = '<![CDATA['.$value.']]>';
$this->xmldoc .= "<$tag>$value</$tag>\n";
}
}
function start_tag($tag)
{
$this->opentags[] = $tag;
$this->xmldoc .= "<$tag>\n";
}
function end_tag()
{
$tag = array_pop($this->opentags);
$tag = preg_replace('# .*$#', '', $tag);
$this->xmldoc .= "</$tag>\n";
}
}
class CP_Abstract {
public static 
$logged_in, 
$script = '',
$is_utf = false,
$proc_error = false,
$lang = array(),
$elang = array(),
$exclude_deutf = array('char_repl'),
$trans_tbl = array()	;
private static
$legacy_files = array(),
$list_opt = array('VBSEO_PINGBACK_STOPWORDS', 'VBSEO_STOPWORDS', 
'VBSEO_PINGBACK_SERVICE', 
'VBSEO_DOMAINS_WHITELIST', 'VBSEO_BOOKMARK_SERVICES',
'VBSEO_DOMAINS_BLACKLIST', 'VBSEO_HOMEPAGE_ALIASES', 
'VBSEO_IGNOREPAGES', 'VBSEO_REWRITE_EXT_ADDTITLE_BLACKLIST');
public static function init()
{
self::$is_utf = vBSEO_Storage::setting('VBSEO_UTF8_SUPPORT') || $_POST['setting']['VBSEO_UTF8_SUPPORT'];
self::check_init_settings();
self::check_login($_COOKIE['vbseocpid'], true);
self::$lang = self::read_lang();
self::$elang = self::read_lang('english');
self::proc_gpc($_POST, get_magic_quotes_gpc(), self::charset());
vbseo_get_options();
}
public static function proc_gpc(&$p, $unmagic, $tocharset, $depth = 0)
{
if($depth>5) return;
foreach($p as $k=>$v)
{
if(is_array($v))
self::proc_gpc($p[$k], $unmagic, $tocharset, $depth+1);
else
{
if($tocharset && ($tocharset != 'UTF-8') && function_exists('iconv'))
{
if(in_array($k . '', self::$exclude_deutf))
{
$v2 = self::proc_deutf($p[$k], $tocharset);
}else
{
$v2 = iconv('UTF-8', $tocharset, $p[$k]);
}
if($v2)
$p[$k] = $v2;
}
if($unmagic)
$p[$k] = stripslashes($p[$k]);
}
}
}
public static function check_login ($password, $cookie)
{
$login_defined = vBSEO_Storage::setting('VBSEO_ADMIN_PASSWORD');
if($cookie)
{
$cfgid = substr($password, 32);
$password = md5(substr($password, 0, 32));
}else
{
$password = md5(md5($password));
$cfgid = VBSEO_CONFIG_ID;
}
self::$logged_in = ($password == $login_defined) && ($cfgid == VBSEO_CONFIG_ID);
return self::$logged_in;
}
public static function logout ()
{
setcookie('vbseocpid', '');
self::$logged_in = false;
}
public static function login ($password)
{
if(self::check_login($password, false))
{
setcookie('vbseocpid', md5($password) . VBSEO_CONFIG_ID);
return true;
}else
return false;
}
public static function setpass ($pass1, $pass2)
{
if ($pass1 != $pass2)
$fail_setpass = 'pass_notsame';
elseif ($pass1 == '')
$fail_setpass = 'pass_empty';
elseif (vBSEO_Storage::setting('VBSEO_ADMIN_PASSWORD'))
$fail_setpass = 'pass_defined';
elseif (!is_writable(vBSEO_Storage::path('config')))
$fail_setpass = 'config_readonly';
if(!$fail_setpass)
{
$pass = md5(md5($pass1));
self::check_init_settings();
self::save_settings(array('VBSEO_ADMIN_PASSWORD' => $pass));
self::login($pass1);
}
return $fail_setpass;
}
public static function check_legacy_files($afiles = array())
{
$ffiles = array();
if(!$afiles)$afiles = self::$legacy_files;
foreach($afiles as $f)
{
if(file_exists(vBSEO_Storage::path('vb') . '/' . $f))
{
$ffiles[] = $f;
}
}
return $ffiles ? self::lang('existing_files').'<br />'.implode('<br />', $ffiles) : '';
}
public static function check_empty_formats()
{
$all_settings = vBSEO_Storage::setting();
$fformats = array();
foreach($all_settings as $st=>$sv)
if (strstr($st, 'VBSEO_URL_') 
|| strstr($st, 'VBSEO_FORUM_TITLE_BIT')
)
if(!$sv && !strstr($st, '_DIRECT') && !strstr($st, '_DOMAIN') && !strstr($st, '_GARS')
&& !strstr($st, '_MAX') && !strstr($st, '_PREFIX') && !strstr($st, '_FILTER'))
{
if(!strstr($st, '_HOME') ||
(!$all_settings['VBSEO_URL_CMS_DOMAIN'] && !$all_settings['VBSEO_URL_BLOG_DOMAIN']) )
$fformats[] = $st;
}
return $fformats ? self::lang('empty_formats').'<br />'.implode(', ', $fformats) : '';
}
public static function get_template ($tplname, $vars = array())
{
global $vboptions;
$vars = array_merge( array(
'version' => VBSEO_VERSION2_MORE,
'checkver'=> 'http://www.vbseo.com/info/vbseo_checkver.js?ver=' . VBSEO_VERSION2_MORE,
'cpscript'=> self::$script,
'isvb4'=> VBSEO_VB4,
'iscms'=> VBSEO_VB_CMS,
'isblog'=> VBSEO_VB_BLOG,
'bburl'   => $vboptions['bburl']
), $vars);
if(!$_POST['setting']&& !$_POST['previtem'])
if($lsver = self::get_product('crawlabi_livestats'))
{
if($lsver >= '1.0 RC4')
$vars['livestats'] = '<script type="text/javascript" src="misc.'.VBSEO_VB_EXT.'?do=livestats_cp"></script>';
}
$output = file_get_contents(vBSEO_Storage::path('html') . '/' . $tplname . '.html');
$output = preg_replace('#\{path:(\w+)\}#ei', 'vBSEO_Storage::path("$1")', $output);
$output = preg_replace('#\{opt:(\w+)\[(.*?)\]\}#ei', 'htmlspecialchars(vBSEO_Storage::setting("$1","$2"))', $output);
$output = preg_replace('#\{opt:(\w+)\}#ei', 'vBSEO_Storage::setting("$1")', $output);
$output = preg_replace('#\{lang_esc:([\w\_]+)\}#ei', 'addslashes(vBSEO_CP::lang("$1"))', $output);
$output = preg_replace('#\{lang:([\w\_]+)\}#ei', 'vBSEO_CP::lang("$1")', $output);
$output = preg_replace('#\{var_esc:([\w\_]+)\}#ei', 'htmlspecialchars(\$vars["$1"])', $output);
$output = preg_replace('#\{var:([\w\_]+)\}#ei', '\$vars["$1"]', $output);
return $output;
}
public static function output_content ($litem, $repl)
{
$output = self::get_template('cp_' . preg_replace('#[^a-z0-9\_\-]#', '', $litem), $repl);
return $output;
}
public static function display_header($optname)
{
return '<div class="column-body"><div class="header"><h4>'.vBSEO_CP::lang($optname).'</h4></div></div>';
}
public static function display_option($tleft, $tright = '')
{
global $altopt;
$tleft = self::lang($tleft);
$tleft = preg_replace('#<(?:strong|b)>(.*?)</(?:strong|b)>(\s*<span class=.*?>.*?</span>)?(?:\s*<br.*?>)?(.*)$#is', "<h4>\$1\$2</h4>\n<p>\$3</p>", $tleft);
$tleft = str_replace('"new"', '"hot"', $tleft);
if($tright)
$tleft = '<div class="left">' . $tleft . '</div>
<div class="right"><dl>' . $tright . '</dl></div>';
else
$tleft = '<div class="single-column">' . $tleft . '</div>';
$c = '
<div class="column-body' . ($altopt ? ' alt' : '') . '">
'.$tleft.'
<div class="clear"></div>
</div>';
$altopt = $altopt ? 0 : 1;
return $c;
}
public static function display_option_cb_field($optname, $text = '', $attr = '')
{
$checked = vBSEO_Storage::setting($optname);
return '<dd><input '.($checked?'checked="checked" ':'').'value="1" name="setting['.$optname.']" id="'.$optname.'" '.$attr.' type="checkbox"><label for="'.$optname.'"> '.$text.'</label></dd>';
}
public static function display_option_textfield($optname, $attr = '', $value = '')
{
$optvalue = $value ? $value : vBSEO_Storage::setting($optname);
if(is_array($optvalue))
{
$optvalue = implode(' ', $optvalue);
}
$class = 'small-input';
if($attr == 'medium')
{
$class = 'medium-input';
$attr  = '';
}
return '<dd><input class="'.$class.'" name="setting['.$optname.']" size="34" '.$attr.' type="text" value="'.htmlspecialchars($optvalue).'" /></dd>';
}
public static function display_option_text($desc, $optname, $attr = '', $prepend = '')
{
$optvalue = vBSEO_Storage::setting($optname);
$c = $prepend . self::display_option_textfield($optname, $attr);
return self::display_option($desc, $c);
}
public static function display_option_area($desc, $optname, $proctype = '', $descright = '')
{
$optvalue = vBSEO_Storage::setting($optname);
$c = '';
switch($proctype)
{
case '|':
$sw_a = explode('|',$optvalue);
foreach($sw_a as $v)
{
$c .= "\n".htmlspecialchars($v);
}
break;
case 'l':
vbseo_extra_inc('linkback');
$sw_a = vbseo_linkback_getbandomains(1);
foreach($sw_a as $v)
{
$c .= "\n".htmlspecialchars($v);
}
break;
case 'x':
foreach($optvalue as $k=>$v)
{
$c .= "\n'".addslashes($k)."' => '".$v[0].'x'.$v[1]."'";
}
break;
case 'd':
foreach($optvalue as $k=>$v)
{
$c .= "\n".addslashes($k)." => '$v'";
}
break;
default:
$c = htmlspecialchars($optvalue);
break;
}
$n1 = 'setting['.$optname.']';
$n2 = 'setting_'.$optname;
$c = '<dt>'.$descright.'</dt>
<dd><textarea class="small-input" name="'.$n1.'" rows="10" wrap="VIRTUAL" id="'.$n2.'">'.($c).'</textarea></dd>';
return $desc ? self::display_option($desc, $c) : $c;
}
public static function display_option_formats($desc, $optname, $options)
{
$optvalue = vBSEO_Storage::setting($optname);
$aopt = array();
$selopt = 'custom'; 
$selval = preg_replace('#\%(.*?)\%#', '[$1]', $optvalue);
foreach($options as $v)
{
$k = preg_replace('#[\[\]]#', '%', $v);
$aopt[$k] = $v;
if($k == $optvalue)
{
$selopt = $k;
$selval = '';
}
}
$aopt['custom'] = self::lang('custom');
$optname = 'format_'.$optname;
return self::display_option_radio($desc, $optname, $aopt,
self::display_option_textfield($optname.'_custom_skip',
'onkeyup="if(this.value)document.getElementById(\'setting_'.$optname.count($aopt).'\').checked=true"',
$selval
),
$selopt
);
}
public static function display_option_radio($desc, $optname, $options, $extra = '', $selopt = '')
{
$optvalue = $selopt ? $selopt : vBSEO_Storage::setting($optname);
$n1 = 'setting['.$optname.']';
$n2 = 'setting_'.$optname;
$c = ''; $on = 0; 
foreach($options as $k=>$v)
$c .= '<dd><input type="radio" name="'.$n1.'" id="'.$n2.(++$on).'" '.(($optvalue == $k) ? 'checked="checked" ' : '').
(is_array($extra) ? $extra[$k].' ' : '') .
'value="'.$k.'" /><label for="'.$n2.$on.'"> '.$v.'</label></dd>';
if(!is_array($extra))
$c .= $extra;
return self::display_option($desc, $c);
}
public static function display_option_yesno($desc, $optname, $extra = '')
{
return self::display_option_radio($desc, $optname,
array(
1 => vBSEO_CP::lang('yes'), 
0 => vBSEO_CP::lang('no')
),
$extra
);
}
public static function display_option_select($desc, $optname, $options, $selopt = '', $extra = '')
{
$optvalue = vBSEO_Storage::setting($optname);
if(!$optvalue) $optvalue = $selopt;
$n1 = 'setting['.$optname.']';
$n2 = 'setting_'.$optname;
if(!$desc)
$n1 = $n2 = $optname;
$c  = '<select '.($desc ? 'class="small-input" ' : '') .'name="'.$n1.'" id="'.$n2.'">';
foreach($options as $k=>$v)
$c .='<option value="'.$k.'"'.(($k==$optvalue) ? ' selected="selected"' : '').'>'.$v.'</option>';
$c .='</select>';
if(!$desc) 
return $c;
$c = '<dt>'.vBSEO_CP::lang('sel_one').':</dt><dd>' . $c . '</dd>';
$c .= $extra;
return self::display_option($desc, $c);
}
public static function charset()
{
return self::$is_utf ? 'UTF-8' : (
vBSEO_Storage::setting('VBSEO_CP_CHARSET') ? vBSEO_Storage::setting('VBSEO_CP_CHARSET')
: self::lang('htmlcharset'));
}
public static function lang($var)
{
$lv = self::$lang[$var];
if(!$lv)$lv = self::$elang[$var];
if(self::$is_utf && function_exists('utf8_encode'))
if($v2 = iconv(self::$lang['htmlcharset'], 'UTF-8', $lv))
$lv = $v2;
else
$lv = utf8_encode($lv);
return $lv;
}
public static function unhtmlentities($string)
{
if(!self::$trans_tbl)
{
self::$trans_tbl = get_html_translation_table (HTML_ENTITIES);
self::$trans_tbl = array_flip (self::$trans_tbl);
}
return strtr ($string , self::$trans_tbl);
}
public static function read_lang($lng = '')
{
if(!$lng)$lng = vBSEO_Storage::setting('VBSEO_CP_LANGUAGE');
if(!$lng)$lng = 'english';
$lcont = file_get_contents(vBSEO_Storage::path('xml') . '/vbseocp_' . $lng . '.xml');
preg_match_all('#<message>.*?<name>(.*?)</name>.*?<value>(.*?)</value>#is', $lcont, $langm, PREG_SET_ORDER);
$lang = array();
foreach($langm as $kl)
$lang[$kl[1]] = self::unhtmlentities($kl[2]);
return $lang;
}
public static function get_flist($pattern)
{
$flist = array();
$pd = @opendir(vBSEO_Storage::path('xml'));
while ($fn = @readdir($pd))
if (preg_match('#^' . $pattern . '$#', $fn, $fm))
$flist[] = $fm[1];
@closedir($pd);
return $flist;
}
public static function preset_name($pname)
{
return vBSEO_Storage::path('xml') . '/vbseo_urls_' . $pname . '.xml';
}
public static function get_presets()
{
return self::get_flist('vbseo_urls_(\d{3}.*)\.xml');
}
public static function check_preset_match(&$presets_def, $type)
{
$pmatch = '';
$plist = self::get_presets();
foreach($plist as $pname)
{
$ismatch = true;
$cfgname = self::preset_name($pname);
$all_settings = vBSEO_Storage::read_config($cfgname);
$xcont = file_get_contents($cfgname);
preg_match('#<settings title="(.*?)">#i', $xcont, $pm);
$matches = 0;
$extraopts = array();
foreach($all_settings as $k=>$v)
{
if(in_array($k, array('VBSEO_SPACER','VBSEO_URL_PART_MAX')))
continue;
if(strstr($k, 'VBSEO_REWRITE_'))
continue;
$utype = strstr($k,'VBSEO_URL_BLOG') ? 'blog':
( strstr($k,'VBSEO_URL_GROUPS') ? 'groups':
( strstr($k,'VBSEO_URL_MEMBER') ? 'member': 
(strstr($k,'VBSEO_URL_CMS') ? 'cms' : 'forum')
));
if($type != $utype)
continue;
$matches++;
$v2 = $presets_def['settings'] ? $presets_def['settings'][$k] : vBSEO_Storage::setting($k);
if(!$v2)
{
$extraopts[] = $k;
if($presets_def['settings'])
$presets_def['settings'][$k] = $v;
}else
if($v != $v2)
{
$ismatch = false;
break;
}
}
if($matches)
{
$presets_def['presets'][$pname] = $pm[1];
$presets_def['presets_extra'][$pname] = $extraopts;
if($ismatch)
$pmatch = $pname;
}
}
return $pmatch;
}
public static function create_xml($settings, $send )
{
$thexml = new VBSEOCP_XML();
$sets = '';
$thexml->start_xml(self::charset());
$thexml->start_tag('settings');
foreach($settings as $k=>$v)
$thexml->add_tag('setting', array(
'name' => $k,
'value'=> $v
));
$thexml->end_tag();
return $thexml->send_xml($send);
}
public static function filter_settings($settings, $type)
{
if($type == 'import')
{
unset($settings['VBSEO_ADMIN_PASSWORD']);
unset($settings['VBSEO_LICENSE_CODE']);
global $vboptions;
if($vboptions['vbseo_confirmation_code'])
$settings['VBSEO_LICENSE_CODE'] = $vboptions['vbseo_confirmation_code'];
return $settings;
}
$ns = array();
foreach($settings as $k=>$v)
{
if(strstr($k,'VBSEO_URL'))
{
$utype = strstr($k,'VBSEO_URL_BLOG') ? 'blog':
(strstr($k,'VBSEO_URL_GROUPS') ? 'groups':
(strstr($k,'VBSEO_URL_MEMBER') ? 'member': 
(strstr($k,'VBSEO_URL_CMS') ? 'cms' : 'forum')
));
if($utype != $type)
continue;
}
$ns[$k] = $v;
}
return $ns;
}
public static function reset_settings()
{
$db = vbseo_get_db();
vbseo_extra_inc('linkback');
$vlink = $db->vbseodb_query_first("SELECT COUNT(*) as cnt FROM " . vbseo_tbl_prefix('thread') . " WHERE vbseo_linkbacks_no>0");
if($vlink['cnt'] == 0)
vbseo_linkback_recalc();
$vbo = vbseo_get_datastore('options');
$vbseoo = vbseo_get_datastore('vbseo_options');
$all_settings = vBSEO_Storage::setting();
unset($all_settings['VBSEO_ADMIN_PASSWORD']);
$vbseoo['settings_backup'] = $all_settings;
$vbo['vbseo_opt'] = array();
vbseo_set_datastore('vbseo_options', $vbseoo);
vbseo_set_datastore('options', $vbo);
$rid = $db->vbseodb_query("SHOW COLUMNS FROM " . vbseo_tbl_prefix('plugin') . " LIKE 'executionorder'");
$excolumn = $db->funcs['fetch_assoc']($rid);
if($excolumn)
$db->vbseodb_query("UPDATE " . vbseo_tbl_prefix('plugin') . "
SET executionorder = 15
WHERE product = 'crawlability_vbseo' AND hookname = 'global_complete' AND executionorder = 5");
vbseo_cache_start();
global $vbseo_cache;
$vbseo_cache->cachereset();
}
public static function get_product($product_code)
{
$db = vbseo_get_db();
$rid = $db->vbseodb_query("SELECT version,active FROM " . vbseo_tbl_prefix('product') . " WHERE productid LIKE '".vbseo_db_escape($product_code)."'");
$ret = $db->funcs['fetch_assoc']($rid);
return $ret['active'] ? $ret['version'] : '';
}
public static function save_settings($settings)
{
foreach($settings as $k=>$v)
{
vBSEO_Storage::setting_set($k, $v);
}
$all_settings = vBSEO_Storage::setting();
foreach($all_settings as $k=>$v)
{
if(is_array($v))
$all_settings[$k] = serialize($v);
}
$xcont = self::create_xml($all_settings, false);
if($xcont)
{
$pf = @fopen(vBSEO_Storage::path('config'), 'w');
@fwrite($pf, $xcont);
@fclose($pf);
}
self::reset_settings();
return $pf ? true : false;
}
public static function get_settings($gettype)
{
$all_settings = vBSEO_Storage::setting();
unset($all_settings['VBSEO_ADMIN_PASSWORD']);
unset($all_settings['VBSEO_LICENSE_CODE']);
if($gettype == 'urw')
{
$ns = array();
foreach($all_settings as $st=>$sv)
if ((strstr($st, 'VBSEO_REWRITE_') 
&& !strstr($st, 'META') 
&& !strstr($st, 'ADDTITLE') 
&& !strstr($st, 'KEYWORDS') 
&& !strstr($st, 'EMAILS')
&& !strstr($st, 'URLENCODING')
) 
|| (strstr($st, 'VBSEO_URL_') 
&& !strstr($st, '_DIRECT')
)
|| strstr($st, 'VBSEO_FORUM_TITLE_BIT')
)$ns[$st] = $sv;
$all_settings = $ns;
}
$xcont = self::create_xml($all_settings, true);
return $xcont;
}
public static function detect_presets(&$settings)
{
$aftypes = array('forum', 'blog', 'cms', 'member', 'groups');
foreach($aftypes as $ftype)
{
$pdef = array('settings'=>$settings);
if($presetmatch = self::check_preset_match($pdef, $ftype))
{
if($pdef['presets_extra'][$presetmatch])
$settings = $pdef['settings'];
}
}
}
public static function check_init_settings()
{
if(vBSEO_Storage::setting('VBSEO_CONFIG_INIT'))
{
$vbseoo = vbseo_get_datastore('vbseo_options');
$setm = isset($vbseoo['settings_backup']) ? $vbseoo['settings_backup'] : array();
if($setm && is_array($setm[0]))
{
$all_settings = array();
foreach($setm as $sk)
{
$v = stripslashes($sk[2]);
$all_settings[$sk[1]] = $v;
}
}else
$all_settings = $setm;
$all_settings['VBSEO_CONFIG_INIT'] = 0;
self::detect_presets($all_settings);
self::save_settings($all_settings);
}
}
public static function proc_deutf($ptxt, $tocharset)
{
$ptxt = preg_replace('#\'([^\']*)(\'\s*\=\>)#mie', '"\'".(($_s = iconv("UTF-8", \''.$tocharset.'\', \'$1\')) ? $_s : \'$1\').stripslashes(\'$2\')', $ptxt);
return $ptxt;
}
public static function array_to_list($parr)
{
$list = '';
foreach($parr as $k=>$v)
{
$list .= "'$k' => '$v'\n";
}
return $list;
}
public static function proc_list(&$ptxt, $imported = false)
{
if(!is_array($ptxt))
{
$ptxt = trim($ptxt);
$ptxt = $ptxt ? explode(' ', $ptxt) : array();
}
return $ptxt;
}
public static function proc_array(&$ptxt, $imported = false, $proctype = '')
{
if(is_array($ptxt))
{
$ptxt = implode('\n', $ptxt);
}
if($imported && preg_match('#^\\\\\'#m', $ptxt))
$ptxt = self::unhtmlentities(stripslashes($ptxt));
$ptxt = trim($ptxt);
$psplit = preg_split('#[\r\n]+#', trim($ptxt));
$pcomb = array();
for($i = 0; $i < count($psplit); $i++)
{
unset($vbseo_crcheck);
$psi = preg_replace('#,+\s*$#', '', $psplit[$i]);
$mpattern = '#^\s*\'(\\\\\'|[^\']*)\'\s*\=\>\s*\'[^\']*\'\s*$#';
if($proctype == 'd')
$mpattern = '#^\s*\d+\s*\=\>\s*\'[^\']*\'\s*$#';
if(preg_match($mpattern, $psi))
@eval($q="\$vbseo_crcheck = array(\n" . $psi . "\n);");
if ($vbseo_crcheck)
{
$k = key($vbseo_crcheck);
if(!$k || (!$vbseo_crcheck[$k] && !strstr($psi, "''")))
unset($vbseo_crcheck2);
else
{
if($proctype == 'x')
{
$vbseo_crcheck[$k] = explode('x', $vbseo_crcheck[$k], 2);
}
$pcomb[$k] = $vbseo_crcheck[$k];
}
}
if (!$vbseo_crcheck)
if ($psi && !preg_match('#^\s*//#', $psi))
{
self::$proc_error = true;
$ptxt = preg_replace('#^' . preg_quote($psi, '#') . '\s*$#m', '// $0', $ptxt);
}
}               
return $pcomb;
}
public static function proc_settings(&$settings, $imported = false)
{
global $messages;
$ns = array();
if(isset($settings['combo_arc']))
{
$settings['VBSEO_REWRITE_ARCHIVE_URLS'] = ($settings['combo_arc'] & 2) ? 1 : 0; 
$settings['VBSEO_REDIRECT_ARCHIVE'] = ($settings['combo_arc'] & 1) ? 1 : 0;
}
if(isset($settings['combo_stopwords']))
{
$settings['VBSEO_FILTER_STOPWORDS'] = ($settings['combo_stopwords']>0) ? 1 : 0; 
$settings['VBSEO_KEEP_STOPWORDS_SHORT'] = ($settings['combo_stopwords'] == 2) ? 1 : 0;
}
if(isset($settings['combo_canonical']))
{
$settings['VBSEO_VIRTUAL_HTML'] = ($settings['combo_canonical']>0) ? 1 : 0; 
$settings['VBSEO_VIRTUAL_HTML_GUESTS_ONLY'] = ($settings['combo_canonical'] == 2) ? 1 : 0;
}
if(isset($settings['combo_bookmark']))
{
$settings['VBSEO_BOOKMARK_THREAD'] = ($settings['combo_bookmark']>0) ? 1 : 0; 
$settings['VBSEO_BOOKMARK_POST'] = ($settings['combo_bookmark'] == 1) ? 1 : 0;
}
if(isset($settings['VBSEO_URL_THREAD_NEXT_DIRECT']))
{
$settings['VBSEO_URL_THREAD_PREV_DIRECT'] = ($settings['VBSEO_URL_THREAD_NEXT_DIRECT']>0) ? 1 : 0; 
}
if(isset($settings['linkback_bl_skip']))
$blackdoms = preg_split('#[\r\n]+#', trim($settings['linkback_bl_skip']));
if(isset($settings['linkback_blacklist']))
$blackdoms = unserialize($settings['linkback_blacklist']);
if(isset($blackdoms))
{
vbseo_extra_inc('linkback');
vbseo_linkback_unbandomain('', 1);
foreach($blackdoms as $bdom)
vbseo_linkback_bandomain($bdom, 1);
}
if(isset($settings['custom_rules_text']))
{
$settings['custom_rules'] = self::proc_array($settings['custom_rules_text'], $imported);
}
if(isset($settings['custom_301_text']))
{           
$settings['custom_301'] = self::proc_array($settings['custom_301_text'], $imported);
}
if(isset($settings['cdn_custom_text']))
{           
$settings['cdn_custom'] = self::proc_array($settings['cdn_custom_text'], $imported);
}
if(isset($settings['acronyms']))
{
$settings['acro'] = self::proc_array($settings['acronyms'], $imported);
}
if(isset($settings['char_repl']))
{
if(is_array($settings['crepl'] = unserialize($settings['char_repl'])))
{
$settings['char_repl'] = self::array_to_list($settings['crepl']);
}
else
$settings['crepl'] = self::proc_array($settings['char_repl'], $imported);
}
if(isset($settings['images_dim']))
{
$max_img = 200;
$settings['images_dim'] = self::proc_array($settings['images_dim'], $imported, 'x');
if(count($settings['images_dim'])>$max_img)
{
$settings['images_dim'] = array_slice($settings['images_dim'], 0, $max_img);
$messages[] = array('attention', vBSEO_CP::lang('imgdim_toolarge').$max_img);
}
}
if(isset($settings['forum_slugs']))
{
$settings['forum_slugs'] = self::proc_array($settings['forum_slugs'], $imported, 'd');
}
if(isset($settings['applyto_forums']))
{
$settings['applyto_forums'] = self::proc_list($settings['applyto_forums'], $imported);
}
foreach($settings as $k=>$v)
if(!strstr($k,'combo_') && !strstr($k,'_skip'))
{
if(in_array($k, self::$list_opt))
{
$v = preg_replace('#\s*[\r\n]+\s*#', '|', trim($v));
}
if(preg_match('#format_(.*)$#', $k, $km))
{
if(($v == 'custom') && isset($settings[$k.'_custom_skip']))
$v = $settings[$k.'_custom_skip'];
$k = $km[1];
$v = preg_replace('#[\[\]]#', '%', $v);
}
$ns[$k] = $v;
}
$settings = $ns;
}
}