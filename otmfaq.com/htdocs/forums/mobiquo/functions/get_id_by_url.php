<?php

defined('IN_MOBIQUO') or exit;

require_once('./global.php');

function get_id_by_url_func($xmlrpc_params)
{
    global $vbulletin;
    
    $params = php_xmlrpc_decode($xmlrpc_params);
    $url = preg_replace('/http:\/\/|www\./i', '', trim($params[0]));
    $host = preg_replace('/http:\/\/|www\./i', '', $vbulletin->options['bburl']);
    
    if (strpos($url, $host) === 0)
    {
        $path = '/' . substr($url, strlen($host));
        $fid = $tid = $pid = "";
        
        // get forum id
        if (preg_match('/(\?|&|;)(f|fid|board)=(\d+)(\W|$)/', $path, $match)) {
            $fid = $match['3'];
        } elseif (preg_match('/\W(f|forum)-?(\d+)(\W|$)/', $path, $match)) {
            $fid = $match['2'];
        } elseif (preg_match('/\/forum\/(\d+)-(\w|-)+(\W|$)/', $path, $match)) {
            $fid = $match['1'];
            $path = str_replace($match[0], $match[3], $path);
        } elseif (preg_match('/forumdisplay\.php(\?|\/)(\d+)(\W|$)/', $path, $match)) {
            $fid = $match['2'];
            $path = str_replace($match[0], $match[3], $path);
        } elseif (preg_match('/index\.php\?forums\/.+\.(\d+)/', $path, $match)) {
            $fid = $match['1'];
        }
        
        // get topic id
        if (preg_match('/(\?|&|;)(t|tid|topic)=(\d+)(\W|$)/', $path, $match)) {
            $tid = $match['3'];
        } elseif (preg_match('/\W(t|(\w|-)+-t_|topic|article)-?(\d+)(\W|$)/', $path, $match)) {
            $tid = $match['3'];
        } elseif (preg_match('/showthread\.php(\?|\/)(\d+)(\W|$)/', $path, $match)) {
            $tid = $match['2'];
        } elseif (preg_match('/(\?|\/)(\d+)-(\w|-)+(\.|\/|$)/', $path, $match)) {
            $tid = $match['2'];
        } elseif (preg_match('/(\?|\/)(\w|-)+-(\d+)(\.|\/|$)/', $path, $match)) {
            $tid = $match['3'];
        } elseif (preg_match('/index\.php\?threads\/.+\.(\d+)/', $path, $match)) {
            $tid = $match['1'];
        }
        
        // get post id
        if (preg_match('/(\?|&|;)(p|pid)=(\d+)(\W|$)/', $path, $match)) {
            $pid = $match['3'];
        } elseif (preg_match('/\W(p|(\w|-)+-p|post|msg)(-|_)?(\d+)(\W|$)/', $path, $match)) {
            $pid = $match['4'];
        } elseif (preg_match('/__p__(\d+)(\W|$)/', $path, $match)) {
            $pid = $match['1'];
        }
    }
    
    $result = array();
    if ($fid) $result['forum_id'] = new xmlrpcval($fid, 'string');
    if ($tid) $result['topic_id'] = new xmlrpcval($tid, 'string');
    if ($pid) $result['post_id'] = new xmlrpcval($pid, 'string');
    
    $response = new xmlrpcval($result, 'struct');
    
    return new xmlrpcresp($response);
}