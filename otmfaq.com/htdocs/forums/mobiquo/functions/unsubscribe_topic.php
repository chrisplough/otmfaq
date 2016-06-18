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

function unsubscribe_topic_func($xmlrpc_params)
{
    global $vbulletin, $permissions, $db, $vbphrase;

    if (($vbulletin->userinfo['userid'] AND !($permissions['forumpermissions'] & $vbulletin->bf_ugp_forumpermissions['canview']))
        OR $vbulletin->userinfo['usergroupid'] == 4
        OR !($permissions['genericoptions'] & $vbulletin->bf_ugp_genericoptions['isnotbannedgroup']))
    {
        return_fault();
    }

    $params = php_xmlrpc_decode($xmlrpc_params);
    
    if ($params[0] == 'ALL')
    {
        $threadidfilter = '';
    }
    else
    {
        $threadid = intval($params[0]);
        if (!$threadid) {
            return_fault(fetch_error('invalidid', $vbphrase['thread']));
        }
        $threadidfilter = " AND threadid = '$threadid'";
    }

    $db->query_write("
        DELETE FROM " . TABLE_PREFIX . "subscribethread
        WHERE userid = " . $vbulletin->userinfo['userid'] .
        $threadidfilter
    );
    
    return new xmlrpcresp(new xmlrpcval(array(
        'result' => new xmlrpcval(true, 'boolean'),
    ), 'struct'));
}
