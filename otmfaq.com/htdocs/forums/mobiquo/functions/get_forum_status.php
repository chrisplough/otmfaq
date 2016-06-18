<?php

defined('IN_MOBIQUO') or exit;

require_once('./global.php');
require_once(DIR . '/includes/functions_forumlist.php');


function get_forum_status_func($xmlrpc_params)
{
    global $vbulletin, $lastpostarray, $config;
    
    $params = php_xmlrpc_decode($xmlrpc_params);
    
    if (!is_array($params[0]))
        return_fault(fetch_error('invalidid', $vbphrase['forum']));
    
    $forumids = $vbulletin->input->clean($params[0], TYPE_ARRAY_UINT);
    $forumids = array_unique($forumids);
    
    if (empty($forumids))
    {
        return_fault(fetch_error('you_did_not_select_any_valid_entries'));
    }
    
    cache_ordered_forums(1, 1);
    fetch_last_post_array();
    
    $forums = array();
    foreach ($forumids as $forumid)
    {
        $forum = $vbulletin->forumcache["$forumid"];
        
        if (!$forum['displayorder'] OR !($forum['options'] & $vbulletin->bf_misc_forumoptions['active'])) {
            continue;
        }
        
        $forumperms = $vbulletin->userinfo['forumpermissions']["$forumid"];
        if (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canview']) AND ($vbulletin->forumcache["$forumid"]['showprivate'] == 1 OR (!$vbulletin->forumcache["$forumid"]['showprivate'] AND !$vbulletin->options['showprivateforums']))) {
            continue;
        }
        
        $lastpostinfo = $vbulletin->forumcache[$lastpostarray[$forumid]];
        $forum['statusicon'] = fetch_forum_lightbulb($forumid, $lastpostinfo, $forum);
        $show['newposticon'] = (($forum['statusicon'] == 'new') ? true : false);
        
        $forum_data = array(
            'forum_id'      => new xmlrpcval($forum['forumid'], 'string'),
            'forum_name'    => new xmlrpcval(mobiquo_encode($forum['title']), 'base64'),
            'logo_url'      => new xmlrpcval(get_forum_icon($forumid), 'string'),
        );
        
        if ($forum['password']) $forum_data['is_protected'] = new xmlrpcval(true, 'boolean');
        if ($show['newposticon']) $forum_data['new_post'] = new xmlrpcval(true, 'boolean');
        
        $forums[] = new xmlrpcval($forum_data, 'struct');
    }
    
    return new xmlrpcresp(new xmlrpcval(array(
        'forums' => new xmlrpcval($forums, 'array'),
    ), 'struct'));
}