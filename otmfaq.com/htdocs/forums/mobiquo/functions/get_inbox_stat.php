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

define('CSRF_PROTECTION', false);

$phrasegroups = array();
$specialtemplates = array();
$globaltemplates = array(
    'USERCP_SHELL',
    'usercp_nav_folderbit'
);
$actiontemplates = array();
$actiontemplates['insertpm'] =& $actiontemplates['newpm'];

require_once('./global.php');

function get_inbox_stat_func($params)
{
    global $vbulletin, $permissions, $db;
    
    $params = php_xmlrpc_decode($params);
    $pm_last_checked_time = isset($params[0]) ? intval($params[0]) : 0;
    $subscribed_topic_last_checked_time = isset($params[1]) ? intval($params[1]) : 0;
    
    $unread_pm_num = 0;
    
    if ($vbulletin->options['enablepms'] && $vbulletin->userinfo['userid'] && ($permissions['pmquota'] >= 1 || $vbulletin->userinfo['pmtotal']))
    {
        $time_filter = '';
        if ($pm_last_checked_time)
            $time_filter = " AND pt.dateline > $pm_last_checked_time ";
        
        $pms = $db->query_first_slave("
            SELECT SUM(IF(pm.messageread = 0, 1, 0)) AS unreaded
            FROM " . TABLE_PREFIX . "pm AS pm ". 
                ($pm_last_checked_time ? " LEFT JOIN " . TABLE_PREFIX . "pmtext AS pt USING(pmtextid) " : '') . "
            WHERE pm.userid=" . $vbulletin->userinfo['userid'] . " AND pm.folderid=0 $time_filter
        ");
        
        $unread_pm_num = $pms['unreaded'];
    }
    
    // GET UNREAD COUNT OF SUBSCRIBED TOPICS
    if (!$vbulletin->options['threadmarking'])
    {
        $vbulletin->userinfo['lastvisit'] = max($vbulletin->userinfo['lastvisit'], $subscribed_topic_last_checked_time);
        if ($vbulletin->userinfo['userid'] AND in_coventry($vbulletin->userinfo['userid'], true))
        {
            $lastpost_info = ", IF(tachythreadpost.userid IS NULL, thread.lastpost, tachythreadpost.lastpost) AS lastposts";

            $tachyjoin = "LEFT JOIN " . TABLE_PREFIX . "tachythreadpost AS tachythreadpost ON " .
                "(tachythreadpost.threadid = subscribethread.threadid AND tachythreadpost.userid = " . $vbulletin->userinfo['userid'] . ')';

            $lastpost_having = "HAVING lastposts > " . $vbulletin->userinfo['lastvisit'];
        }
        else
        {
            $lastpost_info = '';
            $tachyjoin = '';
            $lastpost_having = "AND lastpost > " . $vbulletin->userinfo['lastvisit'];
        }

        $getthreads = $db->query_read_slave("
            SELECT thread.threadid, thread.forumid, thread.postuserid, subscribethread.subscribethreadid
            $lastpost_info
            FROM " . TABLE_PREFIX . "subscribethread AS subscribethread
            INNER JOIN " . TABLE_PREFIX . "thread AS thread USING (threadid)
            $tachyjoin
            WHERE subscribethread.threadid = thread.threadid
                AND subscribethread.userid = " . $vbulletin->userinfo['userid'] . "
                AND thread.visible = 1
                AND subscribethread.canview = 1
                $lastpost_having
        ");
    }
    else
    {
        $readtimeout = TIMENOW - ($vbulletin->options['markinglimit'] * 86400);
        $readtimeout = max($readtimeout, $subscribed_topic_last_checked_time);

        if ($vbulletin->userinfo['userid'] AND in_coventry($vbulletin->userinfo['userid'], true))
        {
            $lastpost_info = ", IF(tachythreadpost.userid IS NULL, thread.lastpost, tachythreadpost.lastpost) AS lastposts";

            $tachyjoin = "LEFT JOIN " . TABLE_PREFIX . "tachythreadpost AS tachythreadpost ON " .
                "(tachythreadpost.threadid = subscribethread.threadid AND tachythreadpost.userid = " . $vbulletin->userinfo['userid'] . ')';
        }
        else
        {
            $lastpost_info = ', thread.lastpost AS lastposts';
            $tachyjoin = '';
        }

        $getthreads = $db->query_read_slave("
            SELECT thread.threadid, thread.forumid, thread.postuserid,
                IF(threadread.readtime IS NULL, $readtimeout, IF(threadread.readtime < $readtimeout, $readtimeout, threadread.readtime)) AS threadread,
                IF(forumread.readtime IS NULL, $readtimeout, IF(forumread.readtime < $readtimeout, $readtimeout, forumread.readtime)) AS forumread,
                subscribethread.subscribethreadid
                $lastpost_info
            FROM " . TABLE_PREFIX . "subscribethread AS subscribethread
            INNER JOIN " . TABLE_PREFIX . "thread AS thread ON (subscribethread.threadid = thread.threadid)
            LEFT JOIN " . TABLE_PREFIX . "threadread AS threadread ON (threadread.threadid = thread.threadid AND threadread.userid = " . $vbulletin->userinfo['userid'] . ")
            LEFT JOIN " . TABLE_PREFIX . "forumread AS forumread ON (forumread.forumid = thread.forumid AND forumread.userid = " . $vbulletin->userinfo['userid'] . ")
            $tachyjoin
            WHERE subscribethread.userid = " . $vbulletin->userinfo['userid'] . "
                AND thread.visible = 1
                AND subscribethread.canview = 1
            HAVING lastposts > IF(threadread > forumread, threadread, forumread)
        ");
    }
    $threadids = array();
    $sub_threads_num = 0;

    if ($totalthreads = $db->num_rows($getthreads))
    {
        $killthreads = array();
        while ($getthread = $db->fetch_array($getthreads))
        {
            $forumperms = fetch_permissions($getthread['forumid']);
            if (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canview']) OR !($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads']) OR ($getthread['postuserid'] != $vbulletin->userinfo['userid'] AND !($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewothers'])))
            {
                $killthreads[] = $getthread['subscribethreadid'];
                continue;
            }
            $threadids[] = $getthread['threadid'];
        }
    }

    unset($getthread);
    $db->free_result($getthreads);

    if (!empty($killthreads))
    {
        // Update thread subscriptions
        $vbulletin->db->query_write("
            UPDATE " . TABLE_PREFIX . "subscribethread
            SET canview = 0
            WHERE subscribethreadid IN (" . implode(', ', $killthreads) . ")
        ");
    }
    
    if(isset($threadids)){
        $sub_threads_num = count($threadids);
    }

    $return_pm  = array(
        'inbox_unread_count'            => new xmlrpcval($unread_pm_num, 'int'),
        'subscribed_topic_unread_count' => new xmlrpcval($sub_threads_num, 'int')
    );
    
    if (defined('NOSHUTDOWNFUNC'))
    {
        //exec_shut_down();
    }
    
    return new xmlrpcresp(new xmlrpcval($return_pm, 'struct'));
}
