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
define('THIS_SCRIPT', 'subscription');
define('CSRF_PROTECTION', false);

$phrasegroups = array('user', 'forumdisplay');
$specialtemplates = array(
    'iconcache',
    'noavatarperms'
);

$globaltemplates = array(
    'USERCP_SHELL',
    'usercp_nav_folderbit',
);

$actiontemplates = array(
    'viewsubscription' => array(
        'forumdisplay_sortarrow',
        'threadbit',
        'SUBSCRIBE'
    ),
    'addsubscription' => array(
        'subscribe_choosetype'
    ),
    'editfolders' => array(
        'subscribe_folderbit',
        'subscribe_showfolders'
    ),
    'dostuff' => array(
        'subscribe_move'
    )
);

$actiontemplates['none'] =& $actiontemplates['viewsubscription'];

require_once('./global.php');
require_once(DIR . '/includes/functions_forumlist.php');


function get_subscribed_forum_func()
{
    global $vbulletin, $permissions, $lastpostarray;
    
    if (!$vbulletin->userinfo['userid']
        OR ($vbulletin->userinfo['userid'] AND !($permissions['forumpermissions'] & $vbulletin->bf_ugp_forumpermissions['canview']))
        OR $vbulletin->userinfo['usergroupid'] == 4
        OR !($permissions['genericoptions'] & $vbulletin->bf_ugp_genericoptions['isnotbannedgroup']))
    {
        return_fault();
    }
    
    $emailupdate = array();
    $subscribedforums = $vbulletin->db->query_read_slave("
        SELECT *
        FROM " . TABLE_PREFIX . "subscribeforum
        WHERE userid = " . $vbulletin->userinfo['userid']
    );
    while ($sforum = $vbulletin->db->fetch_array($subscribedforums))
    {
        $emailupdate[$sforum['forumid']] = $sforum['emailupdate'];
    }
    $vbulletin->db->free_result($subscribedforums);
    
    $forumsinfo = array();
    if (!empty($emailupdate))
    {
        cache_ordered_forums(1, 1, $vbulletin->userinfo['userid']);
        fetch_last_post_array();
        
        foreach ($vbulletin->forumcache AS $forumid => $forum)
        {
            if ($forum['subscribeforumid'] != '')
            {
                $cancontainthreads = $forum['options'] & $vbulletin->bf_misc_forumoptions['cancontainthreads'];
                $subscribe_mode = isset($emailupdate[$forumid]) ? $emailupdate[$forumid] : 0;
                
                $forumsinfo[] = new xmlrpcval(array(
                    'forum_id'      => new xmlrpcval($forumid, 'string'),
                    'forum_name'    => new xmlrpcval(mobiquo_encode($forum['title']), 'base64'),
                    'logo_url'      => new xmlrpcval(get_forum_icon($forumid), 'string'),
                    'subscribe_mode'=> new xmlrpcval($subscribe_mode, 'int'),
                ), 'struct');
                
                if (!$cancontainthreads)    $forumbits_list['sub_only']     = new xmlrpcval(true, 'boolean');
                if ($forum['password'])     $forumbits_list['is_protected'] = new xmlrpcval(true, 'boolean');
                if (strpos($icon_name, 'new') !== false)   
                                            $forumbits_list['new_post']     = new xmlrpcval(true, 'boolean');
            }
        }
    }
        
    return new xmlrpcresp(new xmlrpcval(array(
        'total_forums_num' => new xmlrpcval(sizeof($forumsinfo), 'int'),
        'forums' => new xmlrpcval($forumsinfo, 'array'),
    ), 'struct'));
}
