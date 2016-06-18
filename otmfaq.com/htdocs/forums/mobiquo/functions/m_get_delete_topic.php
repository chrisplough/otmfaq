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

define('THIS_SCRIPT', 'moderation');
define('CSRF_PROTECTION', false);

$phrasegroups = array('user', 'forumdisplay', 'inlinemod');
$specialtemplates = array(
    'iconcache',
    'noavatarperms'
);

$globaltemplates = array(
    'USERCP_SHELL',
    'usercp_nav_folderbit',
);

$actiontemplates = array(
    'viewthreads' => array(
        'forumdisplay_sortarrow',
        'moderation_threads',
        'threadadmin_imod_menu_thread',
        'threadbit',
        'threadbit_deleted',
    ),
    'viewposts' => array(
        'moderation_posts',
        'search_results_postbit',
        'threadadmin_imod_menu_post',
    ),
    'viewvms' => array(
        'moderation_filter',
        'moderation_visitormessages',
        'memberinfo_visitormessage',
        'memberinfo_visitormessage_deleted',
        'memberinfo_visitormessage_ignored',
        'memberinfo_css',
    ),
    'viewgms' => array(
        'moderation_filter',
        'moderation_groupmessages',
        'memberinfo_css',
        'socialgroups_css',
        'socialgroups_message',
        'socialgroups_message_deleted',
        'socialgroups_message_ignored',
    ),
    'viewdiscussions' => array(
        'moderation_filter',
        'moderation_groupdiscussions',
        'memberinfo_css',
        'socialgroups_css',
        'socialgroups_discussion',
        'socialgroups_discussion_deleted',
        'socialgroups_discussion_ignored',
    ),
    'viewpcs' => array(
        'moderation_filter',
        'moderation_picturecomments',
        'picturecomment_css',
        'picturecomment_message_moderatedview',
    ),
    'viewpics' => array(
        'moderation_filter',
        'moderation_picturebit',
        'moderation_pictures',
        'picturecomment_css',
    ),
);

$actiontemplates['none'] =& $actiontemplates['viewthreads'];



require_once('./global.php');
require_once(DIR . '/includes/functions_user.php');
require_once(DIR . '/includes/functions_forumlist.php');



function get_delete_topic_func($xmlrpc_params)
{
    global $vbulletin, $db;

    $params = php_xmlrpc_decode($xmlrpc_params);
    $_REQUEST['do'] = 'viewthreads';
    list($start, $perpage, $pagenumber) = process_page($params[0], $params[1]);

    cache_moderators($vbulletin->userinfo['userid']);

    $sortfield  =& $vbulletin->GPC['sortfield'];
    $daysprune  =& $vbulletin->GPC['daysprune'];
    $type       =& $vbulletin->GPC['type'];
    $forumid    =& $vbulletin->GPC['forumid'];

    $type = 'deleted';
    $table = 'deletionlog';
    $permission = 'canmoderateposts';

    if (!can_moderate()) return return_mod_fault();
    
    $threadselect = ", deletionlog.userid AS del_userid, deletionlog.username AS del_username, deletionlog.reason AS del_reason";
    $threadjoin = "LEFT JOIN " . TABLE_PREFIX . "deletionlog AS deletionlog ON(thread.threadid = deletionlog.primaryid AND deletionlog.type = 'thread')";
    $threadfrom = "FROM " . TABLE_PREFIX . "deletionlog AS deletionlog
                    INNER JOIN " . TABLE_PREFIX . "thread AS thread ON (deletionlog.primaryid = thread.threadid)";
    $show['deleted'] = true;

    if ($vbulletin->options['threadmarking'])
    {
        cache_ordered_forums(1);
    }

    $modforums = array();
    if ($forumid)
    {
        require_once(DIR . '/includes/functions_misc.php');
        $forums = fetch_child_forums($forumid, 'ARRAY');
        $forums[] = $forumid;
        $forums = array_flip($forums);
    }
    else
    {
        $forums = $vbulletin->forumcache;
    }

    foreach ($forums AS $mforumid => $null)
    {
        $forumperms = $vbulletin->userinfo['forumpermissions']["$mforumid"];
        if (can_moderate($mforumid, $permission)
            AND $forumperms & $vbulletin->bf_ugp_forumpermissions['canview']
            AND $forumperms & $vbulletin->bf_ugp_forumpermissions['canviewothers']
        )
        {
            $modforums[] = $mforumid;
        }
    }

    if (empty($modforums)) return return_mod_fault();

    $show['inlinemod'] = true;
    $url = SCRIPTPATH;

    if (!$daysprune)
    {
        $daysprune = ($vbulletin->userinfo['daysprune']) ? $vbulletin->userinfo['daysprune'] : 30;
    }
    $datecut = ($daysprune != -1) ? "AND $table.dateline >= " . (TIMENOW - ($daysprune * 86400)) : '';

    // complete form fields on page
    $daysprunesel = iif($daysprune == -1, 'all', $daysprune);
    $daysprunesel = array($daysprunesel => 'selected="selected"');

    // look at sorting options:
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

    $sqlsortfield2 = '';

    switch ($sortfield)
    {
        case 'title':
        case 'lastpost':
        case 'replycount':
        case 'views':
        case 'postusername':
            $sqlsortfield = 'thread.' . $sortfield;
            break;
        case 'voteavg':
            $sqlsortfield = 'voteavg';
            $sqlsortfield2 = 'votenum';
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

    $hook_query_fields = $hook_query_joins = $hook_query_where = '';

    $threadscount = $db->query_first_slave("
        SELECT COUNT(*) AS threads
        $hook_query_fields
        $threadfrom
        $hook_query_joins
        WHERE type = 'thread'
            AND forumid IN (" . implode(', ', $modforums) . ")
            $datecut
            $hook_query_where
    ");
    
    $totalthreads = $threadscount['threads'];

    // set defaults
    sanitize_pageresults($totalthreads, $pagenumber, $perpage, 200, $vbulletin->options['maxthreads']);

    // display threads
    $limitlower = ($pagenumber - 1) * $perpage;
    $limitupper = ($pagenumber) * $perpage;

    if ($limitupper > $totalthreads)
    {
        $limitupper = $totalthreads;
        if ($limitlower > $totalthreads)
        {
            $limitlower = ($totalthreads - $perpage) - 1;
        }
    }
    if ($limitlower < 0)
    {
        $limitlower = 0;
    }

    $colspan = 1;

    if ($totalthreads)
    {
        $lastread = array();
        $threadids = array();
        $show['threadicons'] = false;
        $colspan = 6;

        $hook_query_fields = $hook_query_joins = $hook_query_where = '';

        // Fetch ids
        $threads = $db->query_read_slave("
            SELECT thread.threadid, thread.forumid,
                IF(votenum >= " . $vbulletin->options['showvotes'] . ", votenum, 0) AS votenum,
                IF(votenum >= " . $vbulletin->options['showvotes'] . " AND votenum > 0, votetotal / votenum, 0) AS voteavg
                $hook_query_fields
                $threadfrom
                $hook_query_joins
            WHERE type = 'thread'
                AND forumid IN (" . implode(', ', $modforums) . ")
                $datecut
                $hook_query_where
            ORDER BY $sqlsortfield $sqlsortorder" . (!empty($sqlsortfield2) ? ", $sqlsortfield2 $sqlsortorder" : '') . "
            LIMIT $limitlower, $perpage
        ");
        
        while ($thread = $db->fetch_array($threads))
        {
            $threadids[] = $thread['threadid'];
            // get last read info for each thread
            if (empty($lastread["$thread[forumid]"]))
            {
                if ($vbulletin->options['threadmarking'])
                {
                    $lastread["$thread[forumid]"] = max($vbulletin->forumcache["$thread[forumid]"]['forumread'], TIMENOW - ($vbulletin->options['markinglimit'] * 86400));
                }
                else
                {
                    $lastread["$thread[forumid]"] = max(intval(fetch_bbarray_cookie('forum_view', $thread['forumid'])), $vbulletin->userinfo['lastvisit']);
                }
            }
            if (!$show['threadicons'] AND ($vbulletin->forumcache["$thread[forumid]"]['options'] & $vbulletin->bf_misc_forumoptions['allowicons']))
            {
                $show['threadicons'] = true;
                $colspan++;
            }
        }
        $limitlower++;

        // get thread preview?
        if ($vbulletin->options['threadpreview'] > 0 AND $type == 'moderated')
        {
            $previewfield = 'post.pagetext AS preview, ';
            $previewjoin = "LEFT JOIN " . TABLE_PREFIX . "post AS post ON(post.postid = thread.firstpostid)";
        }
        else
        {
            $previewfield = '';
            $previewjoin = '';
        }

        $threadbits = '';
        $pagenav = '';
        $counter = 0;
        $toread = 0;

        $vbulletin->options['showvotes'] = intval($vbulletin->options['showvotes']);

        $hook_query_fields = $hook_query_joins = $hook_query_where = '';

        $threads = $db->query_read_slave("
            SELECT
                IF(votenum >= " . $vbulletin->options['showvotes'] . ", votenum, 0) AS votenum,
                IF(votenum >= " . $vbulletin->options['showvotes'] . " AND votenum > 0, votetotal / votenum, 0) AS voteavg,
                $previewfield thread.threadid, thread.title AS threadtitle, lastpost, forumid, pollid, open, replycount, postusername,
                postuserid, lastposter, lastpostid, thread.dateline, views, thread.iconid AS threadiconid, notes, thread.visible, thread.attach,
                thread.prefixid, thread.taglist, hiddencount, deletedcount
                $threadselect
                " . ($vbulletin->options['threadmarking'] ? ", threadread.readtime AS threadread" : '') . "
                $hook_query_fields
            FROM " . TABLE_PREFIX . "thread AS thread
            $threadjoin
            $previewjoin
            " . ($vbulletin->options['threadmarking'] ? " LEFT JOIN " . TABLE_PREFIX . "threadread AS threadread ON (threadread.threadid = thread.threadid AND threadread.userid = " . $vbulletin->userinfo['userid'] . ")" : '') . "
            $hook_query_joins
            WHERE thread.threadid IN (" . implode(', ', $threadids) . ")
            $hook_query_where
            ORDER BY $sqlsortfield $sqlsortorder" . (!empty($sqlsortfield2) ? ", $sqlsortfield2 $sqlsortorder" : '') . "
        ");
        unset($sqlsortfield, $sqlsortorder, $sqlsortfield2);

        require_once(DIR . '/includes/functions_forumdisplay.php');

        // Get Dot Threads
        $dotthreads = fetch_dot_threads_array(implode(', ', $threadids));
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
        $show['threadratings'] = true;
        $show['threadrating'] = true;
        $return_array = array();
        require_once(DIR . '/includes/adminfunctions.php');
        require_once(DIR . '/includes/functions_banning.php');
        while ($thread = $db->fetch_array($threads))
        {
            // unset the thread preview if it can't be seen
            $forumperms = fetch_permissions($thread['forumid']);
            if ($vbulletin->options['threadpreview'] > 0 AND !($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads']))
            {
                $thread['preview'] = '';
            }

            $threadid = $thread['threadid'];
            // build thread data
            $thread = process_thread_array($thread, $lastread["$thread[forumid]"]);

            // Soft Deleted Thread

            if (!$thread['visible'])
            {
                $thread['hiddencount']++;
            }
            $show['moderated'] = ($thread['hiddencount'] > 0 AND can_moderate($thread['forumid'], 'canmoderateposts')) ? true : false;
            $show['spam'] = ($show['moderated'] AND $thread['spamlog_postid']) ? true : false;
            $show['deletedthread'] = ($thread['deletedcount'] > 0) ? true : false;

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
            $mobiquo_can_move = false;
            if (can_moderate($item['forumid'], 'canmanagethreads'))
            {
                $mobiquo_can_move = true;
            }
            $mobiquo_can_approve = false;
            if (can_moderate($item['forumid'], 'canmoderateposts'))
            {
                $mobiquo_can_approve = true;
            }

            $addinfo = $db->query_first_slave("
                SELECT post.pagetext,  thread.lastposter
                FROM " . TABLE_PREFIX . "thread AS thread
                    LEFT JOIN ". TABLE_PREFIX."post AS post on post.postid = thread.firstpostid
                WHERE thread.threadid =$thread[threadid]
                AND post.visible = 1
                ". ($coventry ? "AND post.userid NOT IN ($coventry)" : '') . "
             ");

            if($vbulletin->options['threadpreview'] == 0 ||!($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads'])){
                $addinfo[pagetext] = '';
            }

            if($show['gotonewpost']){
                $mobiquo_new_post = 1;
            } else{
                $mobiquo_new_post = 0;
            }
            $fetch_userinfo_options = (
                FETCH_USERINFO_AVATAR
            );

            $authorinfo = fetch_userinfo($thread[postuserid], $fetch_userinfo_options);
            fetch_avatar_from_userinfo($authorinfo,true,false);

            if($authorinfo[avatarurl]){
                $icon_url=get_icon_real_url($authorinfo['avatarurl']);
            } else {
                $icon_url = '';
            }

            $mobiquo_isclosed = iif($thread['open'], false, true);
            $del_userinfo = fetch_userinfo($thread['del_userid'], $fetch_userinfo_options);
            fetch_avatar_from_userinfo($del_userinfo,true,false);

            if($del_userinfo[avatarurl]){
                $deleted_by_icon_url=get_icon_real_url($del_userinfo['avatarurl']);
            } else {
                $deleted_by_icon_url = '';
            }
            $mobiquo_attach = iif(($thread['attach']>0),1,0);
            cache_permissions($authorinfo, false);
    
            $mobiquo_can_ban = true;
            if (!($vbulletin->userinfo['permissions']['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['cancontrolpanel'] OR can_moderate(0, 'canbanusers')))
            {
                $mobiquo_can_ban = false;
            }

            // check that user has permission to ban the person they want to ban
            if (!($vbulletin->userinfo['permissions']['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['cancontrolpanel']))
            {
                if (can_moderate(0, '', $authorinfo['userid'], $authorinfo['usergroupid'] . (trim($authorinfo['membergroupids']) ? ", $authorinfo[membergroupids]" : ''))
                OR $authorinfo['permissions']['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['cancontrolpanel']
                OR $authorinfo['permissions']['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['ismoderator']
                OR is_unalterable_user($authorinfo['userid']))
                {
                    $mobiquo_can_ban = false;
                }
            } else {
                if ($authorinfo['permissions']['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['cancontrolpanel']
                OR is_unalterable_user($authorinfo['userid']))
                {
                    $mobiquo_can_ban = false;
                }
            }
            
            $return_topic = array(
                'forum_id'          => new xmlrpcval($thread['forumid'], 'string'),
                'forum_name'        => new xmlrpcval(mobiquo_encode($thread['forumtitle']), 'base64'),
                'topic_id'          => new xmlrpcval($thread['threadid'], 'string'),
                'topic_title'       => new xmlrpcval(mobiquo_encode($thread['threadtitle']), 'base64'),
                'post_author_id'    => new xmlrpcval($last_topic['userid'], 'string'),
                'post_author_name'  => new xmlrpcval(mobiquo_encode($thread['lastposter']), 'base64'),
                'topic_author_name' => new xmlrpcval(mobiquo_encode($thread['postusername']), 'base64'),
                'reply_number'      => new xmlrpcval($thread['replycount'], 'int'),
                'view_number'       => new xmlrpcval($thread['views'], 'int'), 
            'deleted_by_icon_url'   => new xmlrpcval($deleted_by_icon_url , 'string'),
                'can_subscribe'     => new xmlrpcval(true, 'boolean'),
                'icon_url'          => new xmlrpcval($icon_url , 'string'),
                'attachment'        => new xmlrpcval($mobiquo_attach, 'string'),
                'is_deleted'        => new xmlrpcval(true, 'boolean'),
                'deleted_by_name'   => new xmlrpcval( mobiquo_encode($thread['del_username']), 'base64'),
        'deleted_by_display_name'   => new xmlrpcval( mobiquo_encode($thread['del_username']), 'base64'),
                'del_username'      => new xmlrpcval( mobiquo_encode($thread['del_username']), 'base64'),
                'del_userid'        => new xmlrpcval( $thread['del_userid'], 'string'),
                'deleted_by_userid' => new xmlrpcval( $thread['del_userid'], 'string'),
                'del_reason'        => new xmlrpcval( mobiquo_encode($thread['del_reason']), 'base64'),
                'delete_reason'     => new xmlrpcval( mobiquo_encode($thread['del_reason']), 'base64'),
                'last_reply_user'   => new xmlrpcval(mobiquo_encode($addinfo['lastposter']), 'base64'),
                'short_content'     => new xmlrpcval(mobiquo_encode(mobiquo_chop($addinfo['pagetext'])), 'base64'),
                'last_reply_time'   => new xmlrpcval(mobiquo_iso8601_encode($thread['lastpost']-$vbulletin->options['hourdiff'], $vbulletin->userinfo['tzoffset']), 'dateTime.iso8601'),
                'post_time'         => new xmlrpcval(mobiquo_iso8601_encode($thread['lastpost']-$vbulletin->options['hourdiff'], $vbulletin->userinfo['tzoffset']), 'dateTime.iso8601'),
                'time_string'       => new xmlrpcval(format_time_string($thread['lastpost']), 'base64'),
                
                'is_approved'       => new xmlrpcval($thread['visible'], 'boolean'),
            );
            
            if ($thread['issubscribed'])$return_topic['is_subscribed']  = new xmlrpcval(true, 'boolean');
            if ($mobiquo_new_post)      $return_topic['new_post']       = new xmlrpcval(true, 'boolean');
            if ($mobiquo_can_ban)       $return_topic['can_ban']        = new xmlrpcval(true, 'boolean');
            if ($mobiquo_is_ban)        $return_topic['is_ban']         = new xmlrpcval(true, 'boolean');
            if ($mobiquo_can_close)     $return_topic['can_close']      = new xmlrpcval(true, 'boolean');
            if ($mobiquo_isclosed)      $return_topic['is_closed']      = new xmlrpcval(true, 'boolean');
            if ($mobiquo_can_sticky)    $return_topic['can_stick']      = new xmlrpcval(true, 'boolean');
            if ($thread['sticky'])      $return_topic['is_sticky']      = new xmlrpcval(true, 'boolean');
            if ($mobiquo_can_approve)   $return_topic['can_approve']    = new xmlrpcval(true, 'boolean');
            //if ($thread['visible'])     $return_topic['is_approved']    = new xmlrpcval(true, 'boolean');
            if ($mobiquo_can_delete)    $return_topic['can_delete']     = new xmlrpcval(true, 'boolean');
            if ($mobiquo_can_move)      $return_topic['can_move']       = new xmlrpcval(true, 'boolean');
            
            $return_array[] = new xmlrpcval($return_topic, 'struct');
        }

        $db->free_result($threads);
        unset($threadids);

        $show['havethreads'] = true;
    }
    else
    {
        $totalthreads = 0;
        $show['havethreads'] = false;
    }

    if ($type == 'moderated')
    {
        $show['delete'] = (can_moderate(0, 'canremoveposts') OR can_moderate(0, 'candeleteposts'));
    }
    else
    {
        $show['delete'] = can_moderate(0, 'canremoveposts');
    }
    $show['undelete'] = can_moderate(0, 'candeleteposts');
    if (defined('NOSHUTDOWNFUNC'))
    {
        exec_shut_down();
    }
    
    return new xmlrpcresp(new xmlrpcval(array(
        'total_topic_num' => new xmlrpcval($totalthreads, 'int'),
        'topics' => new xmlrpcval($return_array, 'array'),
    ), 'struct'));
}
