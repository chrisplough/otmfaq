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

require_once('./global.php');

function subscribe_forum_func($xmlrpc_params)
{
    global $vbulletin, $permissions, $db, $vbphrase;

    if (!$vbulletin->userinfo['userid']
        OR ($vbulletin->userinfo['userid'] AND !($permissions['forumpermissions'] & $vbulletin->bf_ugp_forumpermissions['canview']))
        OR $vbulletin->userinfo['usergroupid'] == 4
        OR !($permissions['genericoptions'] & $vbulletin->bf_ugp_genericoptions['isnotbannedgroup']))
    {
        return_fault();
    }

    $params = php_xmlrpc_decode($xmlrpc_params);
    $emailupdate = isset($params[1]) && in_array($params[1], array(0,2,3)) ? $params[1] : 0;

    if ($params[0] == 'ALL' && isset($params[1]) && in_array($params[1], array(0,2,3)))
    {
        $db->query_write("
            UPDATE " . TABLE_PREFIX . "subscribeforum 
            SET emailupdate = '$emailupdate'
            WHERE userid = " . $vbulletin->userinfo['userid']
        );
    }
    else
    {
        $forumid = intval($params[0]);
        $threadinfo = array();
        $foruminfo = array();
    
        $foruminfo = mobiquo_verify_id('forum', $forumid, 0, 1);
    
        if (!$foruminfo['forumid'])
        {
            return_fault(fetch_error('invalidid', $vbphrase['forum']));
        }
    
        $forumperms = fetch_permissions($foruminfo['forumid']);
        if (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canview']))
        {
            return_fault();
        }
    
        if (!$foruminfo['allowposting'] OR $foruminfo['link'] OR !$foruminfo['cancontainthreads'])
        {
            return_fault(fetch_error('forumclosed'));
        }
    
        // check if there is a forum password and if so, ensure the user has it set
        if (!verify_forum_password($foruminfo['forumid'], $foruminfo['password'], false))
            return_fault('Your administrator has required a password to access this forum.');
    
        $db->query_write("
            REPLACE INTO " . TABLE_PREFIX . "subscribeforum (userid, emailupdate, forumid)
            VALUES (" . $vbulletin->userinfo['userid'] . ", $emailupdate, $foruminfo[forumid])
        ");
    }
    
    return new xmlrpcresp(new xmlrpcval(array(
        'result' => new xmlrpcval(true, 'boolean'),
    ), 'struct'));
}
