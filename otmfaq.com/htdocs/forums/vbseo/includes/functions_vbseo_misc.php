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

function vbseo_cdn_alt(&$link, &$topurl)
{
if(vbseo_is_https())return;
$apaths = vBSEO_Storage::get('cdn_paths');
if(!$apaths)
{
if(vBSEO_Storage::is_set('cdn_paths'))
return $link;
$apaths = $akeys = array();
$apreset = array(
'clientscript/' => 'VBSEO_CDN_JS',
'images/' => 'VBSEO_CDN_IMAGES',
'avatars/' => 'VBSEO_CDN_AVATARS',
'customavatars/' => 'VBSEO_CDN_AVATARS',
'attachments/' => 'VBSEO_CDN_ATTACH',
);
foreach($apreset as $k=>$v)
if(vBSEO_Storage::setting($v))
{
$ap = vBSEO_Storage::setting('VBSEO_CDN_SINGLE') ? 
vBSEO_Storage::setting('VBSEO_CDN_ZONE') :
vBSEO_Storage::setting($v.'_ZONE') ;
if($ap)
{
$apaths[$k] = $ap;
$akeys[] = $k;
}
}
$acustom = vBSEO_Storage::setting('cdn_custom');
foreach($acustom as $k=>$v)
{
$apaths[$k] = $v;
$akeys[] = preg_quote($k, '#');
}
vBSEO_Storage::set('cdn_paths', $apaths);
vBSEO_Storage::set('cdn_keys', $akeys ? implode('|', $akeys) : '');
}
$akeys = vBSEO_Storage::get('cdn_keys');
if($akeys)                  
{
if(preg_match('#^(?:'.preg_quote($topurl, '#').')?('.$akeys.')(.*)$#', $link, $lm))
{
$topurl = $apaths[$lm[1]];
$lapp = (vBSEO_Storage::setting('VBSEO_CDN_SINGLE') || !strstr($lm[1],'/')) ? $lm[1] : '';
$link = $topurl . $lapp . $lm[2];
}
}
}
function vbseo_utf8_substr($str, $from, $len)
{
return preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,' . $from . '}' . '((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,' . $len . '}).*#s',
'$1', $str);
}
function vbseo_replace_last($search, $replacement, $text, $append, $preg_match = '')
{
$text1 = '';
if(function_exists('str_ireplace'))
{
$fpos = -1;
while($iter++ < 10)
{
$fpos2 = stripos($text, $search, $fpos+1);
if($fpos2 === false)
break;
$fpos = $fpos2;
}
if($iter > 1)
{
$text1 = substr($text, 0, $fpos);
$text = substr($text, $fpos);
}
if(!$preg_match) 
{
$replacement = $append ? $search . $replacement : $replacement . $search;
$text = str_ireplace($search, $replacement, $text);
}
}else
{
if(!$preg_match)
$preg_match = '#('.preg_quote($search).')#is';
}
if($preg_match)
{
$replacement = $append ? '$1' . $replacement : $replacement . '$1';
$text = preg_replace($preg_match, $replacement, $text, 1);
}
return $text1 . $text;
}
function vbseo_substr($str, $from, $len)
{
return (defined('VBSEO_UTF8_SUPPORT') && VBSEO_UTF8_SUPPORT) ?
vbseo_utf8_substr($str, $from, $len) : substr($str, $from, $len);
}
function vbseo_substr_words($str, $maxlen)
{
$str = vbseo_substr($str, 0, $maxlen + 1);
$str = preg_replace('#\s+\w+$#', '', $str );
return $str;
}
function vbseo_current_charset()
{
global $stylevar;
return class_exists('vB_Template_Runtime') ? 
vB_Template_Runtime::fetchStyleVar('charset') :
(is_array($stylevar) ? $stylevar['charset'] : '');
}
function vbseo_convert_charset($ptitle, $charset = '')
{
global $stylevar;
$styleset = vbseo_current_charset();
return vbseo_convert_charset_any($ptitle, $styleset, $charset);
}
function vbseo_convert_charset_any($ptitle, $charset_to, $charset_from = '')
{
if($charset_from && ($charset_from == $charset_to))
return $ptitle;
$ptitle2 = '';
if (function_exists('iconv') && $charset_from && VBSEO_RECODE_TITLES)
{
if(strtolower($charset_from) != strtolower($charset_to))
$ptitle2 = iconv($charset_from, $charset_to, $ptitle);
}else
if (function_exists('mb_detect_encoding') && VBSEO_RECODE_TITLES)
{
if (!$charset_from)
$charset_from = mb_detect_encoding($ptitle);
$compat = !function_exists('mb_list_encodings') ||
!($listencodings = mb_list_encodings()) ||
(in_array($charset, $listencodings)
||in_array(strtoupper($charset_from), $listencodings));
if(strtolower($charset_from) != strtolower($charset_to))
if ($charset_from && $compat)
$ptitle2 = @mb_convert_encoding($ptitle, $charset_to, $charset_from);
}
return $ptitle2 ? $ptitle2 : $ptitle;
}
function vbseo_get_page_title($ptext, $limit = 0, $convert = false, $pheaders = '')
{
$ptext = preg_replace('#<!--.*?-->#s', '', $ptext);
preg_match('#<title.*?\>(.+?)</title.*?\>#is', $ptext, $tmatch);
if($convert)
{
$pcharset = vbseo_get_page_charset($pcont, $pheaders);
$tmatch[1] = vbseo_convert_charset($tmatch[1], $pcharset);
}
if($tmptm = @preg_replace('#&\#x([a-fA-F0-9]{2});#ue', '\'&#\'.hexdec(\'$1\').\';\'', $tmatch[1]))
$tmatch[1] = $tmptm;
if($tmptm = @preg_replace('#&\#([0-9]{3});#ue', 'chr(\'$1\')', $tmatch[1]))
$tmatch[1] = $tmptm;
$ptitle = str_replace(
array('&rsaquo;', '&trade;'), array(chr(155), chr(153)),
vbseo_unhtmlentities(
trim(preg_replace('#\s+#', ' ', $tmatch[1]))
));
return $limit ? vbseo_substr($ptitle, 0, $limit) : $ptitle;
}
function vbseo_get_page_charset($ptext, $pheaders = '')
{
if(!preg_match('#content-type:.*?charset=([a-z0-9\-]+)#is', $pheaders, $tmatch))
{
$ptext = preg_replace('#<!--.*?-->#s', '', $ptext);
preg_match('#<meta[^>]*?content-type[^>]*?charset=(.+?)\"#is', $ptext, $tmatch);
}
return $tmatch[1];
}
function vbseo_unhtmlentities ($string)
{
$trans_tbl = get_html_translation_table (HTML_ENTITIES);
$trans_tbl = array_flip ($trans_tbl);
return strtr ($string , $trans_tbl);
}
function vbseo_addon_function($func, &$data) 
{
if (VBSEO_ADDON &&
preg_match('#^[a-zA-Z0-9\_\-]+$#', VBSEO_ADDON) &&
file_exists(vBSEO_Storage::path('vbseoinc') . '/functions_vbseo_' . VBSEO_ADDON . '.php'))
{
include_once vBSEO_Storage::path('vbseoinc') . '/functions_vbseo_' . VBSEO_ADDON . '.php';
$fullfunction = 'vbseo_' . $func . '_' . VBSEO_ADDON;
if (function_exists($fullfunction))
$fullfunction($data);
}
}
function vbseo_http_query($url)
{
$ret = vbseo_http_query_full($url);
return $ret['content'];
}
function vbseo_http_query_full($url, $type = 'GET', $cont = '', $dp = 0, $ctype = '')
{
global $vbseo_gcache;
if (!defined('VBSEO_NET_TIMEOUT'))
define('VBSEO_NET_TIMEOUT', 5);
if (@isset($vbseo_gcache['http_in'][$url]))
return $vbseo_gcache['http_in'][$url];
@ini_set('default_socket_timeout', VBSEO_NET_TIMEOUT);
$purl = @parse_url($url);
if (!$purl['path'])$purl['path'] = '/';
$connsocket = @fsockopen($purl['host'], 80, $errno, $errstr, VBSEO_NET_TIMEOUT);
$start = 0;
$timeout = 50;
$contenttype = '';
while ($start < $timeout)
{
$start++;
if ($connsocket)
{
$qstring = $purl['path'];
if (isset($purl['query']) && $purl['query'])$qstring .= '?' . $purl['query'];
$start = 100;
$out = $type . " " . $qstring . " HTTP/1.0\r\n";
$out .= "Host: " . $purl['host'] . "\r\n";
$out .= "Referer: http://" . $purl['host'] . "/\r\n";
$out .= "User-Agent: " . (defined('VBSEO_USER_AGENT')?VBSEO_USER_AGENT:"Mozilla/4.0 (vBSEO; http://www.vbseo.com)") . "\r\n";
$out .= "Connection: Close\r\n";
if ($type == 'POST')
{
$out .= "Content-type: $ctype\r\n";
$out .= "Content-Length: " . strlen($cont) . "\r\n";
}
$out .= "\r\n";
$out .= $cont;
$inp = '';
$sttime = time();
@fwrite($connsocket, $out);
while (!@feof($connsocket))
{
$inp .= @fread($connsocket, 4096);
if (!$contenttype && preg_match('#^content-type:\s*(.+)#im', $inp, $cmatch))
{
$contenttype = $cmatch[1];
if (!strstr(strtolower($contenttype), 'text/'))
break;
}
if ((time() - $sttime) > VBSEO_NET_TIMEOUT)
break;
if (strlen($inp) > 1024 * 1024)
break;
}
@fclose($connsocket);
}
}
preg_match("#^(.*?)\r?\n\r?\n(.*)$#s", $inp, $hm);
$headersstr = isset($hm[1])?$hm[1]:$inp;
$headers = preg_split("#\r?\n#", $headersstr);
list($proto, $code, $res) = explode(' ', $headers[0]);
if ($dp < 5 && ($code == '301' || $code == '302'))
{
if (preg_match("#location\s*:\s*(\S+)#im", $headersstr, $locm))
{
$rurl = $locm[1];
if (!strstr($rurl, '://'))
$rurl = 'http://' . $purl['host'] . $rurl;
return vbseo_http_query_full($rurl, $type, $cont, $dp + 1, $ctype);
}
}
$rt = ($code == '200') ? $hm[2] : '';
$vbseo_gcache['http_in'][$url] = array('content' => $rt,
'headers' => $headersstr
);
return $vbseo_gcache['http_in'][$url];
}
?>