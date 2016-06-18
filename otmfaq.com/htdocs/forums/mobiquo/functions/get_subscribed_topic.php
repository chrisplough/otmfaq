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

define('IN_MOBIQUO', true);

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
require_once(DIR . '/includes/functions_user.php');


function get_subscribed_topic_func($params)
{
    global $vbulletin, $permissions, $db;
    global $show, $dotthreads, $perpage, $ignore;
    global $vbphrase, $folderid, $folderselect, $subscribecounters;

    if (empty($_REQUEST['do']))
    {
        $_REQUEST['do'] = 'viewsubscription';
    }

    if ((!$vbulletin->userinfo['userid'] AND $_REQUEST['do'] != 'removesubscription') OR ($vbulletin->userinfo['userid'] AND !($permissions['forumpermissions'] & $vbulletin->bf_ugp_forumpermissions['canview'])) OR $userinfo['usergroupid'] == 3 OR $vbulletin->userinfo['usergroupid'] == 4 OR !($permissions['genericoptions'] & $vbulletin->bf_ugp_genericoptions['isnotbannedgroup']))
    {
        return_fault();
    }

    $decode_params = php_xmlrpc_decode($params);
    list($start, $perpage, $pagenumber) = process_page($decode_params[0], $decode_params[1]);
    
    $vbulletin->input->clean_array_gpc('r', array(
        'folderid'   => TYPE_NOHTML,
        'perpage'    => TYPE_UINT,
        'pagenumber' => TYPE_UINT,
        'sortfield'  => TYPE_NOHTML,
        'sortorder'  => TYPE_NOHTML,
    ));

    // Values that are reused in templates
    $sortfield  =& $vbulletin->GPC['sortfield'];
    //$perpage    =& $vbulletin->GPC['perpage'];
    //$pagenumber =& $vbulletin->GPC['pagenumber'];
    $folderid   =& $vbulletin->GPC['folderid'];

    /////////////edit for mobiquo//
    $getallfolders = true;
    $show['allfolders'] = true;
    /////////////edit for mobiquo//
    $folderselect["$folderid"] = 'selected="selected"';
    require_once(DIR . '/includes/functions_misc.php');
    
    $folderjump = construct_folder_jump(1, $folderid); // This is the "Jump to Folder"

    if ($vbulletin->GPC['sortorder'] != 'asc')
    {
        $vbulletin->GPC['sortorder'] = 'desc';
        $sqlsortorder = 'DESC';
        $order = array('desc' => 'selected="selected"');
    }
    else
    {
        $sqlsortorder = '';
        $order = array('asc' => 'selected="selected"');
    }

    switch ($sortfield)
    {
        case 'title':
        case 'lastpost':
        case 'replycount':
        case 'views':
        case 'postusername':
            $sqlsortfield = 'thread.' . $sortfield;
            break;
        default:
            $handled = false;

            if (!$handled)
            {
                $sqlsortfield = 'thread.lastpost';
                $sortfield = 'lastpost';
            }
    }
    $sort = array($sortfield => 'selected="selected"');

    if ($getallfolders)
    {
        if(isset($subscribecounters)){
            $totalallthreads = array_sum($subscribecounters);
        }
    }
    else
    {
        $totalallthreads = $subscribecounters["$folderid"];
    }
    
    sanitize_pageresults($totalallthreads, $pagenumber, $perpage, 200, $vbulletin->options['maxthreads']);

    $hook_query_fields = $hook_query_joins = $hook_query_where = '';

    $getthreads = $db->query_read_slave("
        SELECT thread.threadid, emailupdate, subscribethreadid, thread.forumid, thread.postuserid
        $hook_query_fields
        FROM " . TABLE_PREFIX . "subscribethread AS subscribethread
        LEFT JOIN " . TABLE_PREFIX . "thread AS thread ON(thread.threadid = subscribethread.threadid)
        $hook_query_joins
        WHERE subscribethread.userid = " . $vbulletin->userinfo['userid'] . "
            AND thread.visible = 1
            AND canview = 1
        " . iif(!$getallfolders, " AND folderid = $folderid") . "
        $hook_query_where
        ORDER BY $sqlsortfield $sqlsortorder
        LIMIT $start, $perpage
    ");

    if ($totalthreads = $db->num_rows($getthreads))
    {
        $forumids = array();
        $threadids = array();
        $emailupdate = array();
        $killthreads = array();
        while ($getthread = $db->fetch_array($getthreads))
        {
            $forumperms = fetch_permissions($getthread['forumid']);

            if (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canview']) OR !($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads']) OR ($getthread['postuserid'] != $vbulletin->userinfo['userid'] AND !($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewothers'])))
            {
                $killthreads["$getthread[subscribethreadid]"] = $getthread['subscribethreadid'];
                $totalallthreads--;
                continue;
            }
            $forumids["$getthread[forumid]"] = true;
            $threadids[] = $getthread['threadid'];
            $emailupdate["$getthread[threadid]"] = $getthread['emailupdate'];
            $subscribethread["$getthread[threadid]"] = $getthread['subscribethreadid'];
        }
        $threadids = implode(', ', $threadids);
    }
    unset($getthread);
    $db->free_result($getthreads);

    if (!empty($killthreads))
    {  // Update thread subscriptions
        $vbulletin->db->query_write("
            UPDATE " . TABLE_PREFIX . "subscribethread
            SET canview = 0
            WHERE subscribethreadid IN (" . implode(', ', $killthreads) . ")
        ");
    }
    
    $return_thread = array();
    
    if (!empty($threadids))
    {
        cache_ordered_forums(1);
        $colspan = 5;
        $show['threadicons'] = false;

        // get last read info for each thread
        $lastread = array();
        foreach (array_keys($forumids) AS $forumid)
        {
            if ($vbulletin->options['threadmarking'])
            {
                $lastread["$forumid"] = max($vbulletin->forumcache["$forumid"]['forumread'], TIMENOW - ($vbulletin->options['markinglimit'] * 86400));
            }
            else
            {
                $lastread["$forumid"] = max(intval(fetch_bbarray_cookie('forum_view', $forumid)), $vbulletin->userinfo['lastvisit']);
            }
            if ($vbulletin->forumcache["$forumid"]['options'] & $vbulletin->bf_misc_forumoptions['allowicons'])
            {
                $show['threadicons'] = true;
                $colspan = 6;
            }
        }

        // get thread preview?
        if ($vbulletin->options['threadpreview'] > 0)
        {
            $previewfield = 'post.pagetext AS preview, ';
            $previewjoin = "LEFT JOIN " . TABLE_PREFIX . "post AS post ON(post.postid = thread.firstpostid)";
        }
        else
        {
            $previewfield = '';
            $previewjoin = '';
        }

        $hasthreads = true;
        $threadbits = '';
        $pagenav = '';
        $counter = 0;
        $toread = 0;

        $vbulletin->options['showvotes'] = intval($vbulletin->options['showvotes']);

        if ($vbulletin->userinfo['userid'] AND in_coventry($vbulletin->userinfo['userid'], true))
        {
            $lastpost_info = "IF(tachythreadpost.userid IS NULL, thread.lastpost, tachythreadpost.lastpost) AS lastpost, " .
            "IF(tachythreadpost.userid IS NULL, thread.lastposter, tachythreadpost.lastposter) AS lastposter, " .
            "IF(tachythreadpost.userid IS NULL, thread.lastpostid, tachythreadpost.lastpostid) AS lastpostid";
        
            $tachyjoin = "LEFT JOIN " . TABLE_PREFIX . "tachythreadpost AS tachythreadpost ON " .
            "(tachythreadpost.threadid = thread.threadid AND tachythreadpost.userid = " . $vbulletin->userinfo['userid'] . ')';
        }
        else
        {
            $lastpost_info = 'thread.lastpost, thread.lastposter, thread.lastpostid';
            $tachyjoin = '';
        }

        $hook_query_fields = $hook_query_joins = $hook_query_where = '';

        $threads = $db->query_read_slave("
            SELECT
                IF(votenum >= " . $vbulletin->options['showvotes'] . ", votenum, 0) AS votenum,
                IF(votenum >= " . $vbulletin->options['showvotes'] . " AND votenum > 0, votetotal / votenum, 0) AS voteavg,
                $previewfield thread.threadid, thread.title AS threadtitle, thread.forumid, thread.pollid, thread.open, thread.replycount, thread.postusername, thread.prefixid, thread.sticky,
                $lastpost_info, thread.postuserid, thread.dateline, thread.views, thread.iconid AS threadiconid, thread.notes, thread.visible, thread.attach,
                thread.taglist
                " . ($vbulletin->options['threadmarking'] ? ", threadread.readtime AS threadread" : '') . "
                $hook_query_fields
            FROM " . TABLE_PREFIX . "thread AS thread
            $previewjoin
            " . ($vbulletin->options['threadmarking'] ? " LEFT JOIN " . TABLE_PREFIX . "threadread AS threadread ON (threadread.threadid = thread.threadid AND threadread.userid = " . $vbulletin->userinfo['userid'] . ")" : '') . "
            $tachyjoin
            $hook_query_joins
            WHERE thread.threadid IN ($threadids)
            ORDER BY $sqlsortfield $sqlsortorder
        ");
        unset($sqlsortfield, $sqlsortorder);
        
        require_once(DIR . '/includes/functions_forumdisplay.php');
        
        // Get Dot Threads
        $dotthreads = fetch_dot_threads_array($threadids);
        if ($vbulletin->options['showdots'] AND $vbulletin->userinfo['userid'])
        {
            $show['dotthreads'] = true;
        }
        else
        {
            $show['dotthreads'] = false;
        }

        if ($vbulletin->options['threadpreview'] AND $vbulletin->userinfo['ignorelist'])
        {
            // Get Buddy List
            $buddy = array();
            if (trim($vbulletin->userinfo['buddylist']))
            {
                $buddylist = preg_split('/( )+/', trim($vbulletin->userinfo['buddylist']), -1, PREG_SPLIT_NO_EMPTY);
                foreach ($buddylist AS $buddyuserid)
                {
                    $buddy["$buddyuserid"] = 1;
                }
            }
            DEVDEBUG('buddies: ' . implode(', ', array_keys($buddy)));
            // Get Ignore Users
            $ignore = array();
            if (trim($vbulletin->userinfo['ignorelist']))
            {
                $ignorelist = preg_split('/( )+/', trim($vbulletin->userinfo['ignorelist']), -1, PREG_SPLIT_NO_EMPTY);
                foreach ($ignorelist AS $ignoreuserid)
                {
                    if (!$buddy["$ignoreuserid"])
                    {
                        $ignore["$ignoreuserid"] = 1;
                    }
                }
            }
            DEVDEBUG('ignored users: ' . implode(', ', array_keys($ignore)));
        }

        $foruminfo['allowratings'] = true;
        $show['notificationtype'] = true;
        $show['threadratings'] = true;
        $show['threadrating'] = true;
        
        while ($thread = $db->fetch_array($threads))
        {
            // unset the thread preview if it can't be seen
            $forumperms = fetch_permissions($thread['forumid']);
            if ($vbulletin->options['threadpreview'] > 0 AND !($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads']))
            {
                $thread['preview'] = '';
            }
                
            $mobiquo_can_delete = false;
            if (can_moderate($thread['forumid'], 'candeleteposts') OR can_moderate($thread['forumid'], 'canremoveposts'))
            {
                $mobiquo_can_delete = true;
            }
            $mobiquo_can_close = false;
            if (can_moderate($item['forumid'], 'canopenclose'))
            {
                $mobiquo_can_close = true;
            }
            $mobiquo_can_sticky = false;
            if (can_moderate($item['forumid'], 'canmanagethreads'))
            {
                $mobiquo_can_sticky = true;
            }
            $mobiquo_can_approve = false;
            if (can_moderate($item['forumid'], 'canmoderateposts'))
            {
                $mobiquo_can_approve = true;
            }
            $thread_replycount = $thread[replycount];
            $threadid = $thread['threadid'];
            // build thread data
            $thread = process_thread_array($thread, $lastread["$thread[forumid]"]);

            if($thread[lastpostid]){
                $last_topic = $db->query_first_slave("
                    SELECT post.pagetext,post.userid
                    FROM " . TABLE_PREFIX . "post AS post
                    WHERE post.postid =$thread[lastpostid] 
                        AND post.visible = 1
                    ");
            } else {
                $last_topic = $db->query_first_slave("
                    SELECT post.pagetext,post.userid
                    FROM " . TABLE_PREFIX . "post AS post
                    WHERE post.threadid =$thread[threadid] 
                        AND post.visible = 1
                    ORDER BY postid DESC
                    LIMIT 1
                ");
            }
            
            if($show['gotonewpost']){
                $mobiquo_new_post = 1;
            } else{
                $mobiquo_new_post = 0;
            }
            $fetch_userinfo_options = (
                FETCH_USERINFO_AVATAR
            );
            $lastuserinfo = mobiquo_verify_id('user', $last_topic['userid'], 0, 1, $fetch_userinfo_options);
            if(!is_array($lastuserinfo)){
                $lastuserinfo = array();
            }
            fetch_avatar_from_userinfo($lastuserinfo,true,false);

            if($lastuserinfo[avatarurl]){
                $icon_url=get_icon_real_url($lastuserinfo['avatarurl']);
            } else {
                $icon_url = '';
            }
            $is_deleted = false;
            if($thread['visible'] == 2){
                $is_deleted = true;
            }
            $is_approved = true;
            if($thread['visible'] == 0){
                $is_approved = false;
            }
            
            if($mobiquo_can_delete  OR $mobiquo_can_close OR $mobiquo_can_sticky OR $mobiquo_can_approve)
            {
                require_once(DIR . '/includes/adminfunctions.php');
                require_once(DIR . '/includes/functions_banning.php');
                cache_permissions($lastuserinfo, false);
        
                $mobiquo_can_ban = true;
                if (!($vbulletin->userinfo['permissions']['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['cancontrolpanel'] OR can_moderate(0, 'canbanusers')))
                {
                    $mobiquo_can_ban = false;
                }

                // check that user has permission to ban the person they want to ban
                if (!($vbulletin->userinfo['permissions']['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['cancontrolpanel']))
                {
                    if (can_moderate(0, '', $lastuserinfo['userid'], $lastuserinfo['usergroupid'] . (trim($lastuserinfo['membergroupids']) ? ", $lastuserinfo[membergroupids]" : ''))
                    OR $lastuserinfo['permissions']['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['cancontrolpanel']
                    OR $lastuserinfo['permissions']['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['ismoderator']
                    OR is_unalterable_user($lastuserinfo['userid']))
                    {
                        $mobiquo_can_ban = false;
                    }
                } else {
                    if ($lastuserinfo['permissions']['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['cancontrolpanel']
                    OR is_unalterable_user($lastuserinfo['userid']))
                    {
                        $mobiquo_can_ban = false;
                    }
                }
            } else {
                $mobiquo_can_ban = false;
            }
            $mobiquo_is_ban = false;
            if(!($vbulletin->usergroupcache[$lastuserinfo['usergroupid']]['genericoptions'] & $vbulletin->bf_ugp_genericoptions['isnotbannedgroup'])){
                $mobiquo_is_ban = true;
            }

            $mobiquo_isclosed = iif($thread['open'], false, true);
            
            $subscribe_mode = isset($emailupdate[$thread['threadid']]) ? $emailupdate[$thread['threadid']] : 0;
            
            $return_data = array(
                'forum_id'          => new xmlrpcval($thread['forumid'], 'string'),
                'forum_name'        => new xmlrpcval(mobiquo_encode($thread['forumtitle']), 'base64'),
                'topic_id'          => new xmlrpcval($thread['threadid'], 'string'),
                'topic_title'       => new xmlrpcval(mobiquo_encode($thread['threadtitle']), 'base64'),
                'post_author_id'    => new xmlrpcval($last_topic['userid'], 'string'),
                'post_author_name'  => new xmlrpcval(mobiquo_encode($thread['lastposter']), 'base64'),
                'reply_number'      => new xmlrpcval($thread_replycount, 'int'),
                'icon_url'          => new xmlrpcval($icon_url , 'string'),
                'short_content'     => new xmlrpcval(mobiquo_encode(mobiquo_chop($last_topic['pagetext'])), 'base64'),
                'post_time'         => new xmlrpcval(mobiquo_iso8601_encode($thread['lastpost']-$vbulletin->options['hourdiff'], $vbulletin->userinfo['tzoffset']), 'dateTime.iso8601'),
                'time_string'       => new xmlrpcval(format_time_string($thread['lastpost']), 'base64'),
                'subscribe_mode'    => new xmlrpcval($subscribe_mode, 'int'),
                
                'is_approved'       => new xmlrpcval($is_approved, 'boolean'),
            );
            
            if ($mobiquo_new_post)      $return_data['new_post']      = new xmlrpcval(true, 'boolean');
            if ($mobiquo_can_close)     $return_data['can_close']     = new xmlrpcval(true, 'boolean');
            if ($mobiquo_isclosed)      $return_data['is_closed']     = new xmlrpcval(true, 'boolean');
            if ($mobiquo_can_delete)    $return_data['can_delete']    = new xmlrpcval(true, 'boolean');
            if ($is_deleted)            $return_data['is_deleted']    = new xmlrpcval(true, 'boolean');
            if ($mobiquo_can_sticky)    $return_data['can_stick']     = new xmlrpcval(true, 'boolean');
            if ($thread['sticky'])      $return_data['is_sticky']     = new xmlrpcval(true, 'boolean');
            if ($mobiquo_can_sticky)    $return_data['can_move']      = new xmlrpcval(true, 'boolean');
            if ($mobiquo_can_approve)   $return_data['can_approve']   = new xmlrpcval(true, 'boolean');
            //if ($is_approved)           $return_data['is_approved']   = new xmlrpcval(true, 'boolean');
            if ($mobiquo_can_ban)       $return_data['can_ban']       = new xmlrpcval(true, 'boolean');
            if ($mobiquo_is_ban)        $return_data['is_ban']        = new xmlrpcval(true, 'boolean');
            if ($mobiquo_can_delete || $mobiquo_can_approve) $return_data['can_edit'] = new xmlrpcval(true, 'boolean');
            
            $return_topic = new xmlrpcval($return_data, 'struct');
            
            array_push($return_thread, $return_topic);
        }
        
        $db->free_result($threads);
        unset($threadids);
        $oppositesort = iif($vbulletin->GPC['sortorder'] == 'asc', 'desc', 'asc');
        $show['havethreads'] = true;
    }
    else
    {
        $totalallthreads = 0;
        $show['havethreads'] = false;
    }
    
    if (defined('NOSHUTDOWNFUNC')) exec_shut_down();
    
    return new xmlrpcresp(new xmlrpcval(array(
        'total_topic_num' => new xmlrpcval($totalallthreads, 'int'),
        'topics' => new xmlrpcval($return_thread, 'array'),
    ), 'struct'));
}