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
require_once(DIR . '/includes/functions_bigthree.php');

function mark_topic_read_func($xmlrpc_params)
{
    global $vbulletin, $vbphrase;
    
    $params = php_xmlrpc_decode($xmlrpc_params);
    if (!is_array($params[0]))
        return_fault(fetch_error('invalidid', $vbphrase['thread']));
    
    if ($vbulletin->userinfo['userid'])
    {
        foreach($params[0] as $threadid)
        {
            $threadid = intval($threadid);
            if (empty($threadid)) continue;
            
            $threadinfo = verify_id('thread', $threadid, $throwerror, 1);
            $foruminfo = fetch_foruminfo($threadinfo['forumid']);
            
            if ($vbulletin->options['threadmarking'] AND $vbulletin->userinfo['userid'])
            {
                $threadview = max($threadinfo['threadread'], $threadinfo['forumread'], TIMENOW - ($vbulletin->options['markinglimit'] * 86400));
            }
            else
            {
                $threadview = intval(fetch_bbarray_cookie('thread_lastview', $threadinfo['threadid']));
                if (!$threadview)
                {
                    $threadview = $vbulletin->userinfo['lastvisit'];
                }
            }
            $threadinfo['threadview'] = intval($threadview);
            $displayed_dateline = $threadinfo['lastpost'];
            
            if ($displayed_dateline AND $displayed_dateline > $threadview)
            {
                mark_thread_read($threadinfo, $foruminfo, $vbulletin->userinfo['userid'], $displayed_dateline);
            }
        }
    }
    
    return new xmlrpcresp(new xmlrpcval(array(
        'result'      => new xmlrpcval(true, 'boolean'),
    ), 'struct'));
}