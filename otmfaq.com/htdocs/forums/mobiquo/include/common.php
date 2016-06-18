<?php
/*======================================================================*\
 || #################################################################### ||
 || # Copyright &copy;2009 Quoord Systems Ltd. All Rights Reserved.    # ||
 || # This file may not be redistributed in whole or significant part. # ||
 || # This file is part of the Tapatalk package and should not be used # ||
 || # and distributed for any other purpose that is not approved by    # ||
 || # Quoord Systems Ltd.                                              # ||
 || # http://www.tapatalk.com | http://www.tapatalk.com/license.html   # ||
 || #################################################################### ||
 \*======================================================================*/

defined('IN_MOBIQUO') or exit;

function get_root_dir()
{
    $dir = '../';
    
    if (!empty($_SERVER['SCRIPT_FILENAME']))
    {
        $dir = dirname($_SERVER['SCRIPT_FILENAME']);
        if (!file_exists($dir.'/global.php'))
            $dir = dirname($dir);
        
        $dir = $dir.'/';
    }
    
    return $dir;
}

function process_page($start, $end)
{
    $start = intval($start);
    //$end = intval($end);
    $start = empty($start) ? 0 : max($start, 0);
    $end = ((empty($end) && $end !== 0) || $end < $start) ? ($start + 19) : max($end, $start);
    if ($end - $start >= 50) {
        $end = $start + 49;
    }
    $limit = $end - $start + 1;
    $page = intval($start/$limit) + 1;
    
    return array($start, $limit, $page);
}

function parameter_to_local()
{
    global $vbulletin;
    
    if (isset($vbulletin->GPC['searchuser']))
        $vbulletin->GPC['searchuser'] = mobiquo_encode($vbulletin->GPC['searchuser'], 'to_local');
    
    if (isset($vbulletin->GPC['query']))
        $vbulletin->GPC['query'] = mobiquo_encode($vbulletin->GPC['query'], 'to_local');
}

function get_userid_by_name($name)
{
    global $db;
    
    $username = htmlspecialchars_uni($name);
    
    $query = "SELECT userid
          FROM " . TABLE_PREFIX . "user
          WHERE username = '" . $db->escape_string($username) . "'" ;
    
    require_once( DIR . '/includes/functions_bigthree.php');
    
    $coventry = fetch_coventry();

    $users = $db->query_read_slave($query);
    if ($db->num_rows($users))
    {
        $user = $db->fetch_array($users);
        return (in_array($user['userid'], $coventry) AND !can_moderate()) ? 0 : $user['userid'];
    }
    else
    {
        return 0;
    }
}

function mobiquo_chop($string)
{
    $string = preg_replace('/<br \/\>/', ' ', $string);
    $string = preg_replace('/\n|\r|\t/', ' ', $string);
    $string = strip_quotes($string);
    $string = trim($string);
    $string = preg_replace('/ +/', ' ', $string);
    
    $string = htmlspecialchars_uni(fetch_censored_text(fetch_trimmed_title(
              strip_bbcode($string, false, true), 200)));
    
    return $string;
}

function return_fault($errorString = '')
{
    global $vbulletin;
    
    if (is_array($errorString))
        $errorString = $errorString[1];
    elseif (empty($errorString))
    {
        if ($vbulletin->userinfo['userid']) {
            $errorString = 'You may not have permission to do this action.';
        } else {
            $errorString = 'You are not logged in or you do not have permission to do this action.';
        }
    }
    
    @header('Mobiquo_is_login:'.(isset($vbulletin) && $vbulletin->userinfo['userid'] != 0 ? 'true' : 'false'));
    @header('Content-Type: text/xml');
    
    $response = new xmlrpcresp(
        new xmlrpcval(array(
            'result'        => new xmlrpcval(false, 'boolean'),
            'result_text'   => new xmlrpcval(mobiquo_encode(strip_tags($errorString)), 'base64'),
        ), 'struct')
    );
    
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".$response->serialize('UTF-8');
    exit;
}

function return_mod_fault($errorString = '', $mod = true)
{
    global $vbulletin;
    
    if (is_array($errorString))
        $errorString = $errorString[1];
    elseif (empty($errorString))
    {
        if ($vbulletin->userinfo['userid']) {
            $errorString = 'You may not have permission to do this action.';
        } else {
            $errorString = 'You are not logged in or you do not have permission to do this action.';
        }
    }
    
    $response = new xmlrpcresp(new xmlrpcval(array(
        'result'        => new xmlrpcval(false, 'boolean'),
        'is_login_mod'  => new xmlrpcval($mod, 'boolean'),
        'result_text'   => new xmlrpcval(mobiquo_encode(strip_tags($errorString)), 'base64'),
    ), 'struct'));
    
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".$response->serialize('UTF-8');
    exit;
}

function post_content_clean($str)
{
    global $html_content;
    
    $bbcode_array = array('SIZE','FONT','HIGHLIGHT','LEFT','RIGHT','CENTER','THREAD','POST','CODE','PHP','HTML','NOPARSE','ATTACH','BUG','SCREENCAST');
    if (!$html_content)
        $bbcode_array = array_merge($bbcode_array, array('B','I','U','COLOR','INDENT'));
    
    foreach($bbcode_array as $bbcode) {
        if($bbcode == 'I' or $bbcode == 'U' or $bbcode == 'B'){
            $str =preg_replace("/\[\/?$bbcode\]/siU", '', $str);
        } else{
            $str =preg_replace("/\[\/?$bbcode.*\]/siU", '', $str);
        }
    }
    
    // transform vb4 video to url
    $str = preg_replace('#\[video=(youtube|youtube_share|vimeo|dailymotion|metacafe|google|facebook);[^\]]*\]([^\[]+)\[/video\]#siU', "[URL=$2]$1 video[/URL]", $str);
    
    $str = preg_replace('#\[url\]([^\[]+\.(jpeg|jpg|png|gif))\[/url\]#siU', "[IMG]$1[/IMG]", $str);
    $str = preg_replace('#\[(featureimg|shot|thumb)(=[^\]]+)?\]([^\[]+)\[/\1\]#siU', "[IMG]$3[/IMG]", $str);
    
    $str = preg_replace('#\[vimeo\]([^\[]+)\[/vimeo\]#siU', "[URL]http://vimeo.com/$1[/URL]", $str);
    $str = preg_replace('#\[(youtube|yt)\]([-\w]+)\[/\1\]#siU', "[URL=http://www.youtube.com/watch?v=$2]YouTube Video[/URL]", $str);
    $str = preg_replace('#\[(youtube|yt)\]([^\[]*)\[/\1\]#siU', "[URL=$2]YouTube Video[/URL]", $str);
    $str = preg_replace('#\[(video|vedio|ame|email)([^\]]*)\]([^\[]+)\[/\1\]#siU', "[URL$2]$3[/URL]", $str);

    $str = preg_replace('/\[url\](.*?)\[\/url\]/sei', "'[url]'.trim('$1').'[/url]'", $str);
    $str = preg_replace('/\[timg\](.*?)\[\/timg\]/si', '[IMG]$1[/IMG]', $str);

    //$str = clean_quote($str);
    
    $str = preg_replace('/\[quote=(.*?)\]/sei', "process_quote_name('$1')", $str);
    $str = preg_replace('/(\[quote\])\s*/si', '$1', $str);
    $str = preg_replace('/\s*(\[\/quote\])/siU', '$1', $str);

    $str = process_list_tag($str);
    
    if ($html_content)
    {
        global $color_names;
        $str = str_replace(array_keys($color_names), array_values($color_names), $str);
    }
    else
    {
        $str = htmlspecialchars_uni($str);
    }
    
    return trim($str);
}

function process_quote_name($quote_option)
{
    global $vbulletin;
    
    $str = '[QUOTE]';
    if (preg_match('/^(.+)(?<!&#[0-9]{3}|&#[0-9]{4}|&#[0-9]{5});\s*(\d+)\s*$/U', $quote_option, $match))
    {
        $str .= '[url='.$vbulletin->options['bburl'].'/showthread.php?p='.$match[2].']'
                . strip_tags(vB_Template_Runtime::parsePhrase("originally_posted_by_x", $match[1]))
                . "[/url]\n";
    }
    
    return $str;
}

function process_list_tag($str)
{
    $contents = preg_split('#(\[LIST=1\]|\[/?LIST\])#siU', $str, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    
    $result = '';
    $status = 'out';
    foreach($contents as $content)
    {
        if ($status == 'out')
        {
            if ($content == '[LIST]')
            {
                $status = 'inlist';
            } elseif ($content == '[LIST=1]')
            {
                $status = 'inorder';
            } else {
                $result .= $content;
            }
        } elseif ($status == 'inlist')
        {
            if ($content == '[/LIST]')
            {
                $status = 'out';
            } else
            {
                $result .= str_replace('[*]', '  * ', ltrim($content));
            }
        } elseif ($status == 'inorder')
        {
            if ($content == '[/LIST]')
            {
                $status = 'out';
            } else
            {
                $index = 1;
                $result .= preg_replace('/\[\*\]/sie', "'  '.\$index++.'. '", ltrim($content));
            }
        }
    }
    
    return $result;
}

function clean_quote($text)
{
    $lowertext = strtolower($text);

    // find all [quote tags
    $start_pos = array();
    $curpos = 0;
    do
    {
        $pos = strpos($lowertext, '[quote', $curpos);
        if ($pos !== false AND ($lowertext[$pos + 6] == '=' OR $lowertext[$pos + 6] == ']'))
        {
            $start_pos["$pos"] = 'start';
        }

        $curpos = $pos + 6;
    }
    while ($pos !== false);

    if (sizeof($start_pos) == 0)
    {
        return $text;
    }

    // find all [/quote] tags
    $end_pos = array();
    $curpos = 0;
    do
    {
        $pos = strpos($lowertext, '[/quote]', $curpos);
        if ($pos !== false)
        {
            $end_pos["$pos"] = 'end';
            $curpos = $pos + 8;
        }
    }
    while ($pos !== false);

    if (sizeof($end_pos) == 0)
    {
        return $text;
    }

    // merge them together and sort based on position in string
    $pos_list = $start_pos + $end_pos;
    ksort($pos_list);

    do
    {
        // build a stack that represents when a quote tag is opened
        // and add non-quote text to the new string
        $stack = array();
        $newtext = '';
        $substr_pos = 0;
        foreach ($pos_list AS $pos => $type)
        {

            $stacksize = sizeof($stack);
            if ($type == 'start')
            {
                //
                // empty stack, so add from the last close tag or the beginning of the string
                    
                if ($stacksize == 0 or $stacksize ==1)
                {
                    $newtext .= substr($text, $substr_pos, $pos - $substr_pos);
                    $substr_pos = $pos ;


                }
                    
                array_push($stack, $pos);
            }
            else
            {
                // pop off the latest opened tag
                if ($stacksize >1)
                {
                    $substr_pos = $pos + 8;
                }
                array_pop($stack);
            }
        }

        $newtext .= substr($text, $substr_pos);


        // check to see if there's a stack remaining, remove those points
        // as key points, and repeat. Allows emulation of a non-greedy-type
        // recursion.
        if ($stack)
        {
            foreach ($stack AS $pos)
            {
                unset($pos_list["$pos"]);
            }
        }
    }
    while ($stack);
    return $newtext;
}

function mobiquo_iso8601_encode($timet, $timezone, $utc=0)
{
    $timezone = preg_replace('/\+/', '', $timezone);
    if(!$utc)
    {
        $t=strftime("%Y%m%dT%H:%M:%S", $timet);
        if($timezone >= 0){
            $timezone = sprintf("%02d", $timezone);
            $timezone = '+'.$timezone;
        }
        else{
            $timezone = $timezone * (-1);
            $timezone = sprintf("%02d", $timezone);
            $timezone = '-'.$timezone;
        }
        $t=$t.$timezone.':00';
    }
    else
    {
        if(function_exists('gmstrftime'))
        {
            // gmstrftime doesn't exist in some versions
            // of PHP
            $t=gmstrftime("%Y%m%dT%H:%M:%S", $timet);
        }
        else
        {
            $t=strftime("%Y%m%dT%H:%M:%S", $timet-date('Z'));
        }
    }
    return $t;
}

function format_time_string($timestamp, $time = true)
{
    global $vbulletin;
    
    $timediff = TIMENOW - $timestamp;
    
    if ($timediff >= 3024000 || $timediff < 0) return '';
    
    $timestr = vbdate($vbulletin->options['dateformat'], $timestamp, true)
     . ($time ? ' '. vbdate($vbulletin->options['timeformat'], $timestamp) : '');
    
    return mobiquo_encode($timestr);
}

function get_icon_real_url($iconurl)
{
    global $vbulletin;
    
    $real_url = $iconurl;
    
    if( preg_match('/^http/', $iconurl)){
        $real_url = unhtmlspecialchars($iconurl);
    }
    else{
        if(preg_match('/^\//', $iconurl)){
            $base_url = preg_replace("/http:\/\//siU", '', $vbulletin->options[homeurl]);
            $path = explode('/', $base_url);
            $host = $path[0];
            unset($path);
            $base_host = "http://".$host;
            $real_url = $base_host.unhtmlspecialchars($iconurl);
        } else {
            $real_url = $vbulletin->options['bburl'].'/'.unhtmlspecialchars($iconurl);
        }
    }
    
    return $real_url;
}

function get_participated_uids($tid)
{
    global $vbulletin;
    
    $thread_users = array();
    if (!empty($tid))
    {
        $users = $vbulletin->db->query_read_slave("
            SELECT userid, COUNT(postid) AS num
            FROM " . TABLE_PREFIX . "post
            WHERE threadid = '" . $vbulletin->db->escape_string($tid) . "'
            GROUP BY userid
            ORDER BY num DESC
            LIMIT 10"
        );
        
        while ($row = $vbulletin->db->fetch_array($users))
        {
            $thread_users[] = new xmlrpcval($row['userid'], 'string');
        }
        $vbulletin->db->free_result($users);
    }
    
    return $thread_users;
}

function mobi_parse_requrest()
{
    global $request_method, $request_params, $params_num;
    
    $ver = phpversion();
    if ($ver[0] >= 5) {
        $data = file_get_contents('php://input');
    } else {
        $data = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : '';
    }
    
    if (count($_SERVER) == 0)
    {
        $r = new xmlrpcresp('', 15, 'XML-RPC: '.__METHOD__.': cannot parse request headers as $_SERVER is not populated');
        echo $r->serialize('UTF-8');
        exit;
    }
    
    if(isset($_SERVER['HTTP_CONTENT_ENCODING'])) {
        $content_encoding = str_replace('x-', '', $_SERVER['HTTP_CONTENT_ENCODING']);
    } else {
        $content_encoding = '';
    }
    
    if($content_encoding != '' && strlen($data)) {
        if($content_encoding == 'deflate' || $content_encoding == 'gzip') {
            // if decoding works, use it. else assume data wasn't gzencoded
            if(function_exists('gzinflate')) {
                if ($content_encoding == 'deflate' && $degzdata = @gzuncompress($data)) {
                    $data = $degzdata;
                } elseif ($degzdata = @gzinflate(substr($data, 10))) {
                    $data = $degzdata;
                }
            } else {
                $r = new xmlrpcresp('', 106, 'Received from client compressed HTTP request and cannot decompress');
                echo $r->serialize('UTF-8');
                exit;
            }
        }
    }
    
    $parsers = php_xmlrpc_decode_xml($data);
    $request_method = $parsers->methodname;
    $request_params = php_xmlrpc_decode(new xmlrpcval($parsers->params, 'array'));
    $params_num = count($request_params);
}

function get_forbidden_forums()
{
    global $vbulletin;

    $unwanted_forums = array();

    foreach($vbulletin->forumcache AS $forum)
    {
        $premissions = fetch_permissions($forum['forumid']);

        if (!($premissions & $vbulletin->bf_ugp_forumpermissions['canview']) OR !($forum['options'] & 16384) OR !($premissions & $vbulletin->bf_ugp_forumpermissions['canviewothers']))
        {
            $unwanted_forums[] = $forum['forumid'];
        }
    }

    return $unwanted_forums;
}

function mobiquo_verify_id($idname, &$id, $alert = true, $selall = false, $options = 0)
{
    global $vbphrase;
    // verifies an id number and returns a correct one if it can be found
    // returns 0 if none found
    global $vbulletin, $threadcache, $vbphrase;

    if (empty($vbphrase["$idname"]))
    {
        $vbphrase["$idname"] = $idname;
    }
    
    $id = intval($id);
    $fault_string = fetch_error('invalidid', $vbphrase[$idname]);
    
    if (empty($id))
    {
        if ($alert)
        {
            return_fault($fault_string);
        }
        else
        {
            return 0;
        }
    }

    $selid = ($selall ? '*' : $idname . 'id');

    switch ($idname)
    {
        case 'thread':
        case 'forum':
        case 'post':
            $function = 'fetch_' . $idname . 'info';
            $tempcache = $function($id);
            if (!$tempcache AND $alert)
            {
                return_fault($fault_string);
            }
            return ($selall ? $tempcache : $tempcache[$idname . 'id']);

        case 'user':
            $tempcache = fetch_userinfo($id, $options);
            if (!$tempcache AND $alert)
            {
                return array();
            }
            return ($selall ? $tempcache : $tempcache[$idname . 'id']);

        default:
            if (!$check = $vbulletin->db->query_first("SELECT $selid FROM " . TABLE_PREFIX . "$idname WHERE $idname" . "id = $id"))
            {
                if ($alert)
                {
                    return_fault($fault_string);
                }
                return ($selall ? array() : 0);
            }
            else
            {
                return ($selall ? $check : $check["$selid"]);
            }
    }
}

function mobiquo_encode($str, $mode = '', $strip_tags = true)
{
    if ($strip_tags && empty($mode)) 
        $str = strip_tags($str);
    
    if (empty($str)) return $str;
    
    static $charset, $charset_89, $charset_AF, $charset_8F, $charset_chr, $charset_html, $support_mb, $charset_entity;
    
    if (!isset($charset))
    {
        $charset = trim(vB_Template_Runtime::fetchStyleVar('charset'));
        
        include_once(CWD1.'/include/charset.php');
        
        if (preg_match('/iso-?8859-?1/i', $charset))
        {
            $charset = 'Windows-1252';
            $charset_chr = $charset_8F;
        }
        if (preg_match('/iso-?8859-?(\d+)/i', $charset, $match_iso))
        {
            $charset = 'ISO-8859-' . $match_iso[1];
            $charset_chr = $charset_AF;
        }
        else if (preg_match('/windows-?125(\d)/i', $charset, $match_win))
        {
            $charset = 'Windows-125' . $match_win[1];
            $charset_chr = $charset_8F;
        }
        else
        {
            // x-sjis is not acceptable, but sjis do
            $charset = preg_replace('/^x-/i', '', $charset);
            $support_mb = function_exists('mb_convert_encoding') && @mb_convert_encoding('test', $charset, 'UTF-8');
        }
    }
    
    
    if (preg_match('/utf-?8/i', $charset))
    {
        $str = html_entity_decode($str, ENT_QUOTES, 'UTF-8');
    }
    else if (function_exists('mb_convert_encoding') && (strpos($charset, 'ISO-8859-') === 0 || strpos($charset, 'Windows-125') === 0) && isset($charset_html[$charset]))
    {
        if ($mode == 'to_local')
        {
            $str = @mb_convert_encoding($str, 'HTML-ENTITIES', 'UTF-8');
            $str = str_replace($charset_html[$charset], $charset_chr, $str);
        }
        else
        {
            if (strpos($charset, 'ISO-8859-') === 0)
            {
                // windows-1252 issue on ios
                $str = str_replace(array(chr(129), chr(141), chr(143), chr(144), chr(157)),
                                   array('&#129;', '&#141;', '&#143;', '&#144;', '&#157;'), $str);
            }
            
            $str = str_replace($charset_chr, $charset_html[$charset], $str);
            $str = @html_entity_decode($str, ENT_QUOTES, 'UTF-8');
        }
    }
    else if ($support_mb)
    {
        if ($mode == 'to_local')
        {
            $str = @mb_convert_encoding($str, 'HTML-ENTITIES', 'UTF-8');
            $str = @mb_convert_encoding($str, $charset, 'UTF-8');
        }
        else
        {
            $str = @mb_convert_encoding($str, 'UTF-8', $charset);
            $str = @html_entity_decode($str, ENT_QUOTES, 'UTF-8');
        }
    }
    else if (function_exists('iconv') && @iconv($charset, 'UTF-8', 'test-str'))
    {
        if ($mode == 'to_local')
        {
            $str = @htmlentities($str, ENT_NOQUOTES | ENT_IGNORE, 'UTF-8');
            $str = @iconv('UTF-8', $charset.'//IGNORE', $str);
        }
        else
        {
            $str = @iconv($charset, 'UTF-8//IGNORE', $str);
            $str = @html_entity_decode($str, ENT_QUOTES, 'UTF-8');
        }
    }
    else
    {
        if ($mode == 'to_local')
        {
            $str = @htmlentities($str, ENT_NOQUOTES | ENT_IGNORE, 'UTF-8');
            $str = @html_entity_decode($str, ENT_QUOTES, $charset);
            
            if($charset == 'ISO-8859-1') {
                $str = utf8_decode($str);
            }
        }
        else
        {
            $str = @html_entity_decode($str, ENT_QUOTES, 'UTF-8');
        }
    }
    
    // html entity convert
    if ($mode == 'to_local')
    {
        $str = str_replace(array_keys($charset_entity), array_values($charset_entity), $str);
    }
    
    return remove_unknown_char($str);
}

function mobiquo_get_user_icon($userid)
{
    global $vbulletin;
    
    static $useravatar;
    
    if(!$vbulletin->options['avatarenabled'] || empty($userid)) return '';
    if (isset($useravatar[$userid])) return $useravatar[$userid];
    
    $userinfo = fetch_userinfo($userid, FETCH_USERINFO_AVATAR);
    
    if(!is_array($userinfo) || empty($userinfo)) $userinfo = array();
    
    fetch_avatar_from_userinfo($userinfo, true, false);

    $useravatar[$userid] = $userinfo['avatarurl'] ? get_icon_real_url($userinfo['avatarurl']) : '';
    
    return $useravatar[$userid];
}

function get_vb_message($tempname)
{
    if (!function_exists('fetch_phrase'))
    {
        require_once(DIR . '/includes/functions_misc.php');
    }
    
    $phrase =fetch_phrase('redirect_friendspending', 'frontredirect', 'redirect_', true, false, $languageid, false);

    return $phrase;
}

function get_post_from_id($postid)
{
    global $vbulletin, $db, $forumperms, $permissions, $html_content;

    $post = $db->query_first_slave("
        SELECT
            post.*, post.username AS postusername, post.ipaddress AS ip, IF(post.visible = 2, 1, 0) AS isdeleted,
            user.*, userfield.*, usertextfield.*,
            " . iif($foruminfo['allowicons'], 'icon.title as icontitle, icon.iconpath, ') . "
            IF(displaygroupid=0, user.usergroupid, displaygroupid) AS displaygroupid, infractiongroupid,
            " . iif($vbulletin->options['avatarenabled'], 'avatar.avatarpath, NOT ISNULL(customavatar.userid) AS hascustomavatar, customavatar.dateline AS avatardateline,customavatar.width AS avwidth,customavatar.height AS avheight, ') . "
            " . ((can_moderate($threadinfo['forumid'], 'canmoderateposts') OR can_moderate($threadinfo['forumid'], 'candeleteposts')) ? 'spamlog.postid AS spamlog_postid, ' : '') . "
            editlog.userid AS edit_userid, editlog.username AS edit_username, editlog.dateline AS edit_dateline, editlog.reason AS edit_reason, editlog.hashistory,
            postparsed.pagetext_html, postparsed.hasimages,
            sigparsed.signatureparsed, sigparsed.hasimages AS sighasimages,
            sigpic.userid AS sigpic, sigpic.dateline AS sigpicdateline, sigpic.width AS sigpicwidth, sigpic.height AS sigpicheight
            " . iif(!($permissions['genericpermissions'] & $vbulletin->bf_ugp_genericpermissions['canseehiddencustomfields']), $vbulletin->profilefield['hidden']) . "
            $hook_query_fields
        FROM " . TABLE_PREFIX . "post AS post
        LEFT JOIN " . TABLE_PREFIX . "user AS user ON(user.userid = post.userid)
        LEFT JOIN " . TABLE_PREFIX . "userfield AS userfield ON(userfield.userid = user.userid)
        LEFT JOIN " . TABLE_PREFIX . "usertextfield AS usertextfield ON(usertextfield.userid = user.userid)
        " . iif($foruminfo['allowicons'], "LEFT JOIN " . TABLE_PREFIX . "icon AS icon ON(icon.iconid = post.iconid)") . "
        " . iif($vbulletin->options['avatarenabled'], "LEFT JOIN " . TABLE_PREFIX . "avatar AS avatar ON(avatar.avatarid = user.avatarid) LEFT JOIN " . TABLE_PREFIX . "customavatar AS customavatar ON(customavatar.userid = user.userid)") . "
        " . ((can_moderate($threadinfo['forumid'], 'canmoderateposts') OR can_moderate($threadinfo['forumid'], 'candeleteposts')) ? "LEFT JOIN " . TABLE_PREFIX . "spamlog AS spamlog ON(spamlog.postid = post.postid)" : '') . "
        LEFT JOIN " . TABLE_PREFIX . "editlog AS editlog ON(editlog.postid = post.postid)
        LEFT JOIN " . TABLE_PREFIX . "postparsed AS postparsed ON(postparsed.postid = post.postid AND postparsed.styleid = " . intval(STYLEID) . " AND postparsed.languageid = " . intval(LANGUAGEID) . ")
        LEFT JOIN " . TABLE_PREFIX . "sigparsed AS sigparsed ON(sigparsed.userid = user.userid AND sigparsed.styleid = " . intval(STYLEID) . " AND sigparsed.languageid = " . intval(LANGUAGEID) . ")
        LEFT JOIN " . TABLE_PREFIX . "sigpic AS sigpic ON(sigpic.userid = post.userid)
        $hook_query_joins
        WHERE post.postid = $postid
    ");

    // Tachy goes to coventry
    if (in_coventry($threadinfo['postuserid']) AND !can_moderate($threadinfo['forumid']))
    {
        // do not show post if part of a thread from a user in Coventry and bbuser is not mod
        eval(standard_error(fetch_error('invalidid', $vbphrase['thread'], $vbulletin->options['contactuslink'])));
    }
    if (in_coventry($post['userid']) AND !can_moderate($threadinfo['forumid']))
    {
        // do not show post if posted by a user in Coventry and bbuser is not mod
        eval(standard_error(fetch_error('invalidid', $vbphrase['post'], $vbulletin->options['contactuslink'])));
    }

    $postbit_factory = new vB_Postbit_Factory();
    $postbit_factory->registry =& $vbulletin;
    $postbit_factory->forum =& $foruminfo;
    $postbit_factory->thread =& $threadinfo;
    $postbit_factory->cache = array();
    $postbit_factory->bbcode_parser = new vB_BbCodeParser($vbulletin, fetch_tag_list());

    $postbit_obj =& $postbit_factory->fetch_postbit('post');
    $postbit_obj->highlight =& $replacewords;
    $postbit_obj->cachable = (!$post['pagetext_html'] AND $vbulletin->options['cachemaxage'] > 0 AND (TIMENOW - ($vbulletin->options['cachemaxage'] * 60 * 60 * 24)) <= $threadinfo['lastpost']);
    $mobiquo_attachments = $post[attachments];

    $postbits = $postbit_obj->construct_postbit($post);

    // save post to cache if relevant
    if ($postbit_obj->cachable)
    {
        /*insert query*/
        $db->shutdown_query("
            REPLACE INTO " . TABLE_PREFIX . "postparsed (postid, dateline, hasimages, pagetext_html, styleid, languageid)
            VALUES (
            $post[postid], " .
            intval($threadinfo['lastpost']) . ", " .
            intval($postbit_obj->post_cache['has_images']) . ", '" .
            $db->escape_string($postbit_obj->post_cache['text']) . "', " .
            intval(STYLEID) . ", " .
            intval(LANGUAGEID) . ")
        ");
    }

    $return_attachments = array();

    if(is_array($mobiquo_attachments))
    {
        foreach($mobiquo_attachments as $attach) {
            $attachment_url = "";
            preg_match_all('/href=\"([^\s]+attachmentid='.$attach[attachmentid].'.+?)\"/',unhtmlspecialchars($post[imageattachmentlinks]), $image_attachment_matchs);
            preg_match_all('/href=\"([^\s]+attachmentid='.$attach[attachmentid].'.+?)\"/',unhtmlspecialchars($post[otherattachments]), $other_attachment_matchs);
            preg_match_all('/href=\"([^\s]+attachmentid='.$attach[attachmentid].'.+?)\".+img.+?src=\"(.+attachmentid='.$attach[attachmentid].'.+?)\"/s',unhtmlspecialchars($post[thumbnailattachments]), $thumbnail_attachment_matchs);
            preg_match_all('/src=\"([^\s]+attachmentid='.$attach[attachmentid].'.+?)\"/',unhtmlspecialchars($post[imageattachments]), $small_image_attachment_matchs);

            $type = "other";
            if($image_attachment_matchs[1][0]) {
                $type = "image";
                $attachment_url = $GLOBALS[vbulletin]->options[bburl].'/'.$image_attachment_matchs[1][0];
            }
            if($other_attachment_matchs[1][0]){
                $type = "other";
                $attachment_url = $GLOBALS[vbulletin]->options[bburl].'/'.$other_attachment_matchs[1][0];
            }
            if($small_image_attachment_matchs[1][0]) {
                $type = "image";
                $attachment_thumbnail_url= $GLOBALS[vbulletin]->options[bburl].'/'.$small_image_attachment_matchs[1][0];
                $attachment_url = $GLOBALS[vbulletin]->options[bburl].'/'.$small_image_attachment_matchs[1][0];
            }
            if($thumbnail_attachment_matchs[1][0]){
                $type = "image";
                $attachment_url = $GLOBALS[vbulletin]->options[bburl].'/'.$thumbnail_attachment_matchs[1][0];
                $attachment_thumbnail_url = $GLOBALS[vbulletin]->options[bburl].'/'.$thumbnail_attachment_matchs[2][0];
            }
            if(empty($attachment_url)){
                $attachment_url = $GLOBALS[vbulletin]->options[bburl].'/'."attachment.php?attachmentid=".$attach[attachmentid];
            }
            
            $return_attachment = new xmlrpcval(array(
                'filename'      => new xmlrpcval($attach[filename], 'base64'),
                'filesize'      => new xmlrpcval($attach[filesize], 'int'),
                'url'           => new xmlrpcval(unhtmlspecialchars($attachment_url), 'string'),
                'thumbnail_url' => new xmlrpcval(unhtmlspecialchars($attachment_thumbnail_url), 'string'),
                'content_type' => new xmlrpcval($type, 'string')), 'struct');
            array_push($return_attachments, $return_attachment);
        }
    }

    if($html_content)
    {
        $a = fetch_tag_list();
        unset($a['option']['quote']);
        unset($a['no_option']['quote']);
        unset($a['option']['url']);
        unset($a['no_option']['url']);

        $vbulletin->options['wordwrap'] = 0;
         
        $post_content =preg_replace("/\[\/img\]/siU", '[/img1]', $post['pagetext']);
        $bbcode_parser = new vB_BbCodeParser($vbulletin, $a);
        $post_content = $bbcode_parser->parse($post_content, $thread[forumid], false);
        $post_content =preg_replace("/\[\/img1\]/siU", '[/IMG]', $post_content);
         
        $post_content = htmlspecialchars_uni($post_content);
        $post_content = mobiquo_encode(post_content_clean($post_content), '', false);

    } else {
        $post_content = mobiquo_encode(post_content_clean($post['pagetext']));
    }

    if(SHORTENQUOTE == 1 && preg_match('/^(.*\[quote\])(.+)(\[\/quote\].*)$/si', $post_content))
    {
        $new_content = "";
        $segments = preg_split('/(\[quote\].+\[\/quote\])/isU', $post_content,-1, PREG_SPLIT_DELIM_CAPTURE);

        foreach($segments as $segment)
        {
            $short_quote = $segment;
            if(preg_match('/^(\[quote\])(.+)(\[\/quote\])$/si', $segment, $quote_matches))
            {
                if(function_exists('mb_strlen') && function_exists('mb_substr'))
                {
                    if(mb_strlen($quote_matches[2], 'UTF-8') > 170) {
                        $short_quote = $quote_matches[1].mb_substr($quote_matches[2],0,150, 'UTF-8').$quote_matches[3];
                    }
                }
                else
                {
                    if(strlen($quote_matches[2]) > 170){
                        $short_quote = $quote_matches[1].substr($quote_matches[2],0,150).$quote_matches[3];
                    }
                }
                $new_content .= $short_quote;
            } else {
                $new_content .= $segment;
            }
        }

        $post_content = $new_content;
    }
    
    $mobiquo_can_edit = false;
    if(isset($post['editlink']) AND strlen($post['editlink']) > 0){
        $mobiquo_can_edit = true;
    }
    $mobiquo_user_online = (fetch_online_status($post, false)) ? true : false;

    $return_post = array(
        'result'            => new xmlrpcval(true, 'boolean'),
        'stat'              => new xmlrpcval($post['visible'] || can_moderate($foruminfo['forumid'], 'canmoderateposts') ? 0 : 1, 'int'),
        'topic_id'          => new xmlrpcval($post['threadid'], 'string'),
        'post_id'           => new xmlrpcval($post['postid'], 'string'),
        'post_title'        => new xmlrpcval(mobiquo_encode($post['title']), 'base64'),
        'post_content'      => new xmlrpcval($post_content, 'base64'),
        'post_author_id'    => new xmlrpcval($post['userid'], 'string'),
        'post_author_name'  => new xmlrpcval(mobiquo_encode($post['postusername']), 'base64'),
        'post_time'         => new xmlrpcval(mobiquo_iso8601_encode($post['dateline']-$vbulletin->options['hourdiff'], $vbulletin->userinfo['tzoffset']), 'dateTime.iso8601'),
        'time_string'       => new xmlrpcval(format_time_string($post['dateline']), 'base64'),
        'post_count'        => new xmlrpcval($post['postcount'], 'int'),
        'attachments'       => new xmlrpcval($return_attachments, 'array'),
        
        'allow_smilies'     => new xmlrpcval($post['allowsmilie'], 'boolean'),
    );
    
    if ($mobiquo_can_edit)    $return_post['can_edit']      = new xmlrpcval(true, 'boolean');
    if ($mobiquo_user_online) $return_post['is_online']     = new xmlrpcval(true, 'boolean');
    if ($show['deleteposts']) $return_post['can_delete']    = new xmlrpcval(true, 'boolean');
    //if ($post['allowsmilie']) $return_post['allow_smilies'] = new xmlrpcval(true, 'boolean');

    
    $return_post['icon_url'] = new xmlrpcval('', 'string');
    if($post['avatarurl']){
        $return_post['icon_url']=new xmlrpcval(get_icon_real_url($post['avatarurl']), 'string');
    }
    
    $return_post['attachment_authority'] = new xmlrpcval(0, 'int');
    if(!($forumperms & $vbulletin->bf_ugp_forumpermissions['cangetattachment'])) {
        $return_post['attachment_authority'] = new xmlrpcval(4, 'int');
    }
    
    return $return_post;
}

function remove_unknown_char($str)
{
    for ($i = 1; $i < 32; $i++)
    {
        if (in_array($i, array(10, 13))) continue;
        $str = str_replace(chr($i), '', $str);
    }
    
    return $str;
}

function get_forum_icon_name($forumid)
{
    global $vbulletin, $lastpostarray;
    
    $forum = $vbulletin->forumcache[$forumid];
    $lastpostinfo = $vbulletin->forumcache[$lastpostarray[$forumid]];
    $forum['statusicon'] = fetch_forum_lightbulb($forumid, $lastpostinfo, $forum);
    
    $forumperms = $vbulletin->userinfo['forumpermissions'][$forumid];
    if ($vbulletin->options['showlocks'] // show locks to users who can't post
        AND !$forum['link'] // forum is not a link
        AND(
            !($forum['options'] & $vbulletin->bf_misc_forumoptions['allowposting']) // forum does not allow posting
            OR(!($forumperms & $vbulletin->bf_ugp_forumpermissions['canpostnew']) // can't post new threads
                AND !($forumperms & $vbulletin->bf_ugp_forumpermissions['canreplyown']) // can't reply to own threads
                AND !($forumperms & $vbulletin->bf_ugp_forumpermissions['canreplyothers']) // can't reply to others' threads
            )
        )
    ) {
        $forum['statusicon'] .= '_lock';
    }
    
    if ($forum['options'] & $vbulletin->bf_misc_forumoptions['cancontainthreads']) {
        $forum['statusicon'] = 'forum_' . $forum['statusicon'] . '-48';
    } else {
        if ($forum['statusicon'] == 'new_lock') $forum['statusicon'] = 'old_lock';
        $forum['statusicon'] = 'category_forum_' . $forum['statusicon'];
    }
    
    if ($forum['statusicon'] == 'category_forum_new_lock')
        $forum['statusicon'] = 'category_forum_old_lock';
    
    return $forum['statusicon'];
}

function get_forum_icon($forumid)
{
    global $vbulletin;
    
    if(file_exists(CWD1.'/forum_icons/'.$forumid.'.png'))
        $icon_url = $vbulletin->options['bburl'].'/mobiquo/forum_icons/'.$forumid.'.png';
    elseif(file_exists(CWD1.'/forum_icons/'.$forumid.'.jpg'))
        $icon_url = $vbulletin->options['bburl'].'/mobiquo/forum_icons/'.$forumid.'.jpg';
    elseif(file_exists(CWD1.'/forum_icons/default.png'))
        $icon_url = $vbulletin->options['bburl'].'/mobiquo/forum_icons/default.png';
    elseif(file_exists(CWD1.'/forum_icons/default.jpg'))
        $icon_url = $vbulletin->options['bburl'].'/mobiquo/forum_icons/default.jpg';
    else
    {
        $icon_name = get_forum_icon_name($forumid);
        
        $statusicon_dir = vB_Template_Runtime::fetchStyleVar('imgdir_statusicon');
        if (preg_match('#^[a-z0-9]+://#si', $statusicon_dir))
        {
            $icon_url = $statusicon_dir.'/'.$icon_name.'.png';
        }
        else if (file_exists($statusicon_dir.'/'.$icon_name.'.png'))
        {
            $icon_url = $vbulletin->options['bburl'] . '/' . $statusicon_dir.'/'.$icon_name.'.png';
        }
        else
        {
             $icon_url = '';
        }
    }
    
    return $icon_url;
}

function get_read_topics_from_cookie()
{
    global $vbulletin;
    
    $cookie_name = COOKIE_PREFIX . 'thread_lastview';
    $cache_name = 'bb_cache_' . $cookiename; // name of cache variable
    global $$cache_name; // internal array for cacheing purposes
    
    $cookie =& $vbulletin->input->clean_gpc('c', $cookie_name, TYPE_STR);
    $cache =  &$$cache_name;
    if ($cookie != '' AND !isset($cache))
    {
        $cache = @unserialize(convert_bbarray_cookie($cookie));
    }
    
    return isset($cache) ? $cache : array();
}