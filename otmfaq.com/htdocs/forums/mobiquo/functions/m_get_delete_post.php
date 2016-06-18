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


function get_delete_post_func($xmlrpc_params)
{
    global $vbulletin, $db;

    $params = php_xmlrpc_decode($xmlrpc_params);
    $_REQUEST['do'] = 'viewposts';
    list($start, $perpage, $pagenumber) = process_page($params[0], $params[1]);

    cache_moderators($vbulletin->userinfo['userid']);
    $vbulletin->input->clean_array_gpc('r', array(
        'daysprune' => TYPE_INT,
        'sortfield' => TYPE_NOHTML,
        'sortorder' => TYPE_NOHTML,
        'type'       => TYPE_NOHTML,
    ));

    // Values that are reused in templates
    $sortfield  =& $vbulletin->GPC['sortfield'];
    $daysprune  =& $vbulletin->GPC['daysprune'];
    $type       =& $vbulletin->GPC['type'];

    $table = 'deletionlog';
    $permission = '';
    
    if (!can_moderate()) return return_mod_fault();
    
    $postselect = ",pdeletionlog.userid AS pdel_userid, pdeletionlog.username AS pdel_username, pdeletionlog.reason AS pdel_reason,
            tdeletionlog.userid AS tdel_userid, tdeletionlog.username AS tdel_username, tdeletionlog.reason AS tdel_reason";
    $postjoin = "LEFT JOIN " . TABLE_PREFIX . "deletionlog AS tdeletionlog ON (thread.threadid = tdeletionlog.primaryid AND tdeletionlog.type = 'thread')
            LEFT JOIN " . TABLE_PREFIX . "deletionlog AS pdeletionlog ON(post.postid = pdeletionlog.primaryid AND pdeletionlog.type = 'post')";
    $postfrom = "FROM " . TABLE_PREFIX . "deletionlog AS deletionlog
        INNER JOIN " . TABLE_PREFIX . "post AS post ON (deletionlog.primaryid = post.postid)";
    $show['deleted'] = true;
    $posttype = 'post';

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
        AND $forumperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads']
        AND $forumperms & $vbulletin->bf_ugp_forumpermissions['canviewothers']
        )
        {
            $modforums[] = $mforumid;
        }
    }

    if (empty($modforums))
    {
        return return_mod_fault();
    }

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

    switch ($sortfield)
    {
        case 'title':
        case 'dateline':
        case 'username':
            $sqlsortfield = 'post.' . $sortfield;
            break;
        default:
            $handled = false;
            ($hook = vBulletinHook::fetch_hook('moderation_posts_sort')) ? eval($hook) : false;
            if (!$handled)
            {
                $sqlsortfield = 'post.dateline';
                $sortfield = 'dateline';
            }
    }
    $sort = array($sortfield => 'selected="selected"');

    $hook_query_fields = $hook_query_joins = $hook_query_where = '';
    ($hook = vBulletinHook::fetch_hook('moderation_postsquery_postscount')) ? eval($hook) : false;

    $postscount = $db->query_first_slave("
        SELECT COUNT(*) AS posts
        $hook_query_fields
        $postfrom
        $hook_query_joins
        INNER JOIN " . TABLE_PREFIX . "thread AS thread ON (post.threadid = thread.threadid)
        WHERE type = '$posttype'
            AND forumid IN (" . implode(', ', $modforums) . ")
            $datecut
            $hook_query_where
    ");
    $totalposts = $postscount['posts'];

    // set defaults
    sanitize_pageresults($totalposts, $pagenumber, $perpage, 200, 4);

    // display posts
    $limitlower = ($pagenumber - 1) * $perpage;
    $limitupper = ($pagenumber) * $perpage;

    if ($limitupper > $totalposts)
    {
        $limitupper = $totalposts;
        if ($limitlower > $totalposts)
        {
            $limitlower = ($totalposts - $perpage) - 1;
        }
    }
    if ($limitlower < 0)
    {
        $limitlower = 0;
    }
    if ($totalposts)
    {
        $hook_query_fields = $hook_query_joins = $hook_query_where = '';
        ($hook = vBulletinHook::fetch_hook('moderation_postsquery_postid')) ? eval($hook) : false;

        $lastread = array();
        $postids = array();
        // Fetch ids
        $posts = $db->query_read_slave("
            SELECT post.postid, thread.forumid
            $hook_query_fields
            $postfrom
            $hook_query_joins
            INNER JOIN " . TABLE_PREFIX . "thread AS thread ON (post.threadid = thread.threadid)
            WHERE type = '$posttype'
                AND forumid IN (" . implode(', ', $modforums) . ")
                $datecut
                $hook_query_where
            ORDER BY $sqlsortfield $sqlsortorder
            LIMIT $limitlower, $perpage
        ");
        
        while ($post = $db->fetch_array($posts))
        {
            $postids[] = $post['postid'];
            // get last read info for each thread
            if (empty($lastread["$post[forumid]"]))
            {
                if ($vbulletin->options['threadmarking'])
                {
                    $lastread["$post[forumid]"] = max($vbulletin->forumcache["$post[forumid]"]['forumread'], TIMENOW - ($vbulletin->options['markinglimit'] * 86400));
                }
                else
                {
                    $lastread["$post[forumid]"] = max(intval(fetch_bbarray_cookie('forum_view', $post['forumid'])), $vbulletin->userinfo['lastvisit']);
                }
            }
        }
        $limitlower++;

        $hasposts = true;
        $postbits = '';
        $pagenav = '';
        $counter = 0;
        $toread = 0;

        $vbulletin->options['showvotes'] = intval($vbulletin->options['showvotes']);

        $hook_query_fields = $hook_query_joins = $hook_query_where = '';

        $posts = $db->query_read_slave("
            SELECT
                post.postid, post.title AS posttitle, post.dateline AS postdateline,
                post.iconid AS posticonid, post.pagetext, post.visible,
                IF(post.userid = 0, post.username, user.username) AS username,
                thread.threadid, thread.title AS threadtitle, thread.iconid AS threadiconid, thread.replycount,
                IF(thread.views = 0, thread.replycount + 1, thread.views) AS views, thread.firstpostid,
                thread.pollid, thread.sticky, thread.open, thread.lastpost, thread.forumid, thread.visible AS thread_visible,
                user.userid
                $postselect
                " . iif($vbulletin->options['threadmarking'] AND $vbulletin->userinfo['userid'], ', threadread.readtime AS threadread') . "
                $hook_query_fields
            FROM " . TABLE_PREFIX . "post AS post
            INNER JOIN " . TABLE_PREFIX . "thread AS thread ON (post.threadid = thread.threadid)
            $postjoin
            " . iif($vbulletin->options['threadmarking'] AND $vbulletin->userinfo['userid'], " LEFT JOIN " . TABLE_PREFIX . "threadread AS threadread ON (threadread.threadid = thread.threadid AND threadread.userid = " . $vbulletin->userinfo['userid'] . ")") . "
            LEFT JOIN " . TABLE_PREFIX . "user AS user ON(user.userid = post.userid)
            $hook_query_joins
            WHERE post.postid IN (" . implode(', ', $postids) . ")
            $hook_query_where
            ORDER BY $sqlsortfield $sqlsortorder
        ");
        unset($sqlsortfield, $sqlsortorder);

        require_once(DIR . '/includes/functions_forumdisplay.php');
        $return_array = array();
        
        require_once(DIR . '/includes/adminfunctions.php');
        require_once(DIR . '/includes/functions_banning.php');
        
        while ($post = $db->fetch_array($posts))
        {
            $item['forumtitle'] = $vbulletin->forumcache["$item[forumid]"]['title'];

            // do post folder icon
            if ($vbulletin->options['threadmarking'] AND $vbulletin->userinfo['userid'])
            {
                // new if post hasn't been read or made since forum was last read
                $isnew = ($post['postdateline'] > $post['threadread'] AND $post['postdateline'] > $vbulletin->forumcache["$post[forumid]"]['forumread']);
            }
            else
            {
                $isnew = ($post['postdateline'] > $vbulletin->userinfo['lastvisit']);
            }

            if ($isnew)
            {
                $post['post_statusicon'] = 'new';
                $post['post_statustitle'] = $vbphrase['unread'];
            }
            else
            {
                $post['post_statusicon'] = 'old';
                $post['post_statustitle'] = $vbphrase['old'];
            }

            // allow icons?
            $post['allowicons'] = $vbulletin->forumcache["$post[forumid]"]['options'] & $vbulletin->bf_misc_forumoptions['allowicons'];

            // get POST icon from icon cache
            $post['posticonpath'] =& $vbulletin->iconcache["$post[posticonid]"]['iconpath'];
            $post['posticontitle'] =& $vbulletin->iconcache["$post[posticonid]"]['title'];

            // show post icon?
            if ($post['allowicons'])
            {
                // show specified icon
                if ($post['posticonpath'])
                {
                    $post['posticon'] = true;
                }
                // show default icon
                else if (!empty($vbulletin->options['showdeficon']))
                {
                    $post['posticon'] = true;
                    $post['posticonpath'] = $vbulletin->options['showdeficon'];
                    $post['posticontitle'] = '';
                }
                // do not show icon
                else
                {
                    $post['posticon'] = false;
                    $post['posticonpath'] = '';
                    $post['posticontitle'] = '';
                }
            }
            // do not show post icon
            else
            {
                $post['posticon'] = false;
                $post['posticonpath'] = '';
                $post['posticontitle'] = '';
            }

            $post['pagetext'] = preg_replace('#\[quote(=(&quot;|"|\'|)??.*\\2)?\](((?>[^\[]*?|(?R)|.))*)\[/quote\]#siU', '', $post['pagetext']);

            // get first 200 chars of page text
            $post['pagetext'] = htmlspecialchars_uni(fetch_censored_text(trim(fetch_trimmed_title(strip_bbcode($post['pagetext'], 1), 200))));

            // get post title
            if ($post['posttitle'] == '')
            {
                $post['posttitle'] = fetch_trimmed_title($post['pagetext'], 50);
            }
            else
            {
                $post['posttitle'] = fetch_censored_text($post['posttitle']);
            }

            // format post text
            $post['pagetext'] = nl2br($post['pagetext']);

            // get info from post
            $post = process_thread_array($post, $lastread["$post[forumid]"], $post['allowicons']);

            $show['managepost'] = (can_moderate($post['forumid'], 'candeleteposts') OR can_moderate($post['forumid'], 'canremoveposts')) ? true : false;
            $show['approvepost'] = (can_moderate($post['forumid'], 'canmoderateposts')) ? true : false;
            $show['managethread'] = (can_moderate($post['forumid'], 'canmanagethreads')) ? true : false;
            $show['disabled'] = ($show['managethread'] OR $show['managepost'] OR $show['approvepost']) ? false : true;

            $show['moderated'] = (!$post['visible'] OR (!$post['thread_visible'] AND $post['postid'] == $post['firstpostid'])) ? true : false;
            $show['spam'] = ($show['moderated'] AND $post['spamlog_postid']) ? true : false;

            if ($post['pdel_userid'])
            {
                $post['del_username'] =& $post['pdel_username'];
                $post['del_userid'] =& $post['pdel_userid'];
                $post['del_reason'] = fetch_censored_text($post['pdel_reason']);
                $post['del_phrase'] = $vbphrase['message_deleted_by_x'];
                $show['deleted'] = true;
            }
            else if ($post['tdel_userid'])
            {
                $post['del_username'] =& $post['tdel_username'];
                $post['del_userid'] =& $post['tdel_userid'];
                $post['del_reason'] = fetch_censored_text($post['tdel_reason']);
                $post['del_phrase'] = $vbphrase['thread_deleted_by_x'];
                $show['deleted'] = true;
            }
            else
            {
                $show['deleted'] = false;
            }

            $fetch_userinfo_options = (
                FETCH_USERINFO_AVATAR
            );

            $authorinfo = fetch_userinfo($post['userid'], $fetch_userinfo_options);
            fetch_avatar_from_userinfo($authorinfo,true,false);

            if($authorinfo[avatarurl]){
                $icon_url=get_icon_real_url($authorinfo['avatarurl']);
            } else {
                $icon_url = '';
            }
                
            $del_userinfo = fetch_userinfo($post['del_userid'], $fetch_userinfo_options);
            fetch_avatar_from_userinfo($del_userinfo,true,false);

            if($del_userinfo[avatarurl]){
                $deleted_by_icon_url=get_icon_real_url($del_userinfo['avatarurl']);
            } else {
                $deleted_by_icon_url = '';
            }
            $is_approved = true;
            if($post['visible'] == 0){
                $is_approved = false;
            }
            
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
            $mobiquo_is_ban = false;
            if(!($vbulletin->usergroupcache[$authorinfo['usergroupid']]['genericoptions'] & $vbulletin->bf_ugp_genericoptions['isnotbannedgroup'])){
                $mobiquo_is_ban = true;
            }
            
            
            $return_post = array(
                'forum_id'          => new xmlrpcval($post['forumid'], 'string'),
                'forum_name'        => new xmlrpcval(mobiquo_encode($post['forumtitle']), 'base64'),
                'topic_id'          => new xmlrpcval($post['threadid'], 'string'),
                'topic_title'       => new xmlrpcval(mobiquo_encode($post['threadtitle']), 'base64'),
                'post_id'           => new xmlrpcval($post['postid'], 'string'),
                'post_title'        => new xmlrpcval(mobiquo_encode($post['posttitle']), 'base64'),
                'reply_number'      => new xmlrpcval($post['replycount'], 'int'),
                'post_position'     => new xmlrpcval(0, 'int'),
                'short_content'     => new xmlrpcval(mobiquo_encode(mobiquo_chop($post['pagetext'])), 'base64'),
                'post_author_id'    => new xmlrpcval($post['userid'], 'string'),
                'is_deleted'        => new xmlrpcval(true, 'boolean'),
                'icon_url'          => new xmlrpcval($icon_url , 'string'),
            'deleted_by_icon_url'   => new xmlrpcval($deleted_by_icon_url , 'string'),
                'post_author_name'  => new xmlrpcval(mobiquo_encode($post['username']), 'base64'),
                'time_string'       => new xmlrpcval(format_time_string($post['postdateline']), 'base64'),
                'post_time'         => new xmlrpcval(mobiquo_iso8601_encode( $post['postdateline']-$vbulletin->options['hourdiff'], $vbulletin->userinfo['tzoffset']), 'dateTime.iso8601'),
                'del_username'      => new xmlrpcval(mobiquo_encode($post['del_username']), 'base64'),
                'deleted_by_name'   => new xmlrpcval(mobiquo_encode($post['del_username']), 'base64'),
         'deleted_by_display_name'  => new xmlrpcval(mobiquo_encode($post['del_username']), 'base64'),
                'deleted_by_userid' => new xmlrpcval($post['del_userid'], 'string'),
                'del_userid'        => new xmlrpcval($post['del_userid'], 'string'),
                'delete_reason'     => new xmlrpcval(mobiquo_encode($post['del_reason']), 'base64'),
                'del_reason'        => new xmlrpcval(mobiquo_encode($post['del_reason']), 'base64'),
                
                'is_approved'       => new xmlrpcval($is_approved, 'boolean'),
            );
            
            if ($show['approvepost'])   $return_post['can_approve'] = new xmlrpcval(true, 'boolean');
            //if ($is_approved)           $return_post['is_approved'] = new xmlrpcval(true, 'boolean');
            if ($show['managepost'])    $return_post['can_delete']  = new xmlrpcval(true, 'boolean');
            if ($mobiquo_can_ban)       $return_post['can_ban']     = new xmlrpcval(true, 'boolean');
            if ($mobiquo_is_ban)        $return_post['is_ban']      = new xmlrpcval(true, 'boolean');
            if ($show['managethread'])  $return_post['can_move']    = new xmlrpcval(true, 'boolean');
            
            $return_array[] = new xmlrpcval($return_post, 'struct');
            exec_switch_bg();
        }

        $db->free_result($posts);
        unset($postids);
    }
    else
    {
        $totalposts = 0;
    }

    if (defined('NOSHUTDOWNFUNC'))
    {
        exec_shut_down();
    }
    
    return new xmlrpcresp(new xmlrpcval(array(
        'total_post_num' => new xmlrpcval($totalposts, 'int'),
        'posts'          => new xmlrpcval($return_array, 'array'),
    ), 'struct'));
}
