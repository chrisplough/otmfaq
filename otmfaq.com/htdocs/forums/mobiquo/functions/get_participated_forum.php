<?php

defined('IN_MOBIQUO') or exit;

require_once('./global.php');
require_once(DIR . '/includes/functions_forumlist.php');


function get_participated_forum_func()
{
    global $vbulletin, $db, $lastpostarray;
    
    if (empty($vbulletin->userinfo['userid']))
        return_fault();
    
    $tids = $vbulletin->db->query_first_slave("
        SELECT GROUP_CONCAT(DISTINCT threadid separator ', ') as tids_str
        FROM " . TABLE_PREFIX . "post WHERE userid = '" . $vbulletin->userinfo['userid'] . "'
            AND dateline > UNIX_TIMESTAMP() - 8640000
    ");
    
    $forums = array();
    if ($tids['tids_str'])
    {
        $fids = $vbulletin->db->query_read_slave("
            SELECT DISTINCT forumid
            FROM " . TABLE_PREFIX . "thread WHERE threadid IN (" . $tids['tids_str'] . ")
        ");
        
        cache_ordered_forums(1, 1);
        fetch_last_post_array();
        
        while ($fid = $vbulletin->db->fetch_array($fids))
        {
            $forumid = $fid['forumid'];
            $forum = $vbulletin->forumcache[$forumid];
            
            $lastpostinfo = $vbulletin->forumcache[$lastpostarray[$forumid]];
            $forum['statusicon'] = fetch_forum_lightbulb($forumid, $lastpostinfo, $forum);
            $show['newposticon'] = (($forum['statusicon'] == 'new') ? true : false);
            
            if(file_exists(CWD1.'/forum_icons/'.$forum['forumid'].'.png'))
                $icon_url = $vbulletin->options['bburl'].'/mobiquo/forum_icons/'.$forum['forumid'].'.png';
            elseif(file_exists(CWD1.'/forum_icons/'.$forum['forumid'].'.jpg'))
                $icon_url = $vbulletin->options['bburl'].'/mobiquo/forum_icons/'.$forum['forumid'].'.jpg';
            elseif(file_exists(CWD1.'/forum_icons/default.png'))
                $icon_url = $vbulletin->options['bburl'].'/mobiquo/forum_icons/default.png';
            elseif(file_exists(CWD1.'/forum_icons/default.jpg'))
                $icon_url = $vbulletin->options['bburl'].'/mobiquo/forum_icons/default.jpg';
            else 
                $icon_url = '';
            
            $forum_data = array(
                'forum_id'      => new xmlrpcval($forum['forumid'], 'string'),
                'forum_name'    => new xmlrpcval(mobiquo_encode($forum['title']), 'base64'),
                'logo_url'      => new xmlrpcval($icon_url, 'string'),
            );
            
            if ($forum['password']) $forum_data['is_protected'] = new xmlrpcval(true, 'boolean');
            if ($show['newposticon']) $forum_data['new_post'] = new xmlrpcval(true, 'boolean');
            
            $forums[] = new xmlrpcval($forum_data, 'struct');
        }
        $vbulletin->db->free_result($forum);
    }
    
    return new xmlrpcresp(new xmlrpcval(array(
        'total_forums_num'  => new xmlrpcval(count($forums), 'int'),
        'forums'            => new xmlrpcval($forums, 'array'),
    ), 'struct'));
}