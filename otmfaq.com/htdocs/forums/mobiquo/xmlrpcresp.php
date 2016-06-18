<?php

defined('IN_MOBIQUO') or exit;

function return_mod_true()
{
    return new xmlrpcresp(new xmlrpcval(array(
        'result'        => new xmlrpcval(true, 'boolean'),
        'is_login_mod'  => new xmlrpcval(true, 'boolean'),
    ), 'struct'));
}

function search_func()
{
    global $vbulletin, $db, $current_user, $vbphrase, $include_topic_num, $searchnoresults, $request_method;
    
    if ($searchnoresults)
    {
        $vbulletin->GPC['searchid'] = 0;
        $total_topic_num = $total_unread_num = 0;
        $return_list = array();
    }
    else
    {
        $results = vB_Search_Results::create_from_searchid($current_user, $vbulletin->GPC['searchid']);
        if (!isset($vbulletin->GPC['showposts']))
            $vbulletin->GPC['showposts'] = $results->get_criteria()->get_search_term('showposts');
        
        $page_results = array();
        $total_topic_num = 0;
        
        if ($results)
        {
            // cookie based unread support
            if($request_method == 'get_unread_topic' && !$vbulletin->options['threadmarking'])
            {
                $page_results = $results->get_page(1, 100, 1);
                $start_num = ($vbulletin->GPC['pagenumber'] - 1) * $vbulletin->GPC['perpage'];
                $end_num = $vbulletin->GPC['pagenumber'] * $vbulletin->GPC['perpage'] - 1;
            }
            else
                $page_results = $results->get_page($vbulletin->GPC['pagenumber'], $vbulletin->GPC['perpage'], 3);
            
            $total_topic_num = $results->get_confirmed_count();
        }
        
        //prepare types for render
        $items_by_type = array();
        foreach ($page_results as $item)
        {
            $typeid = $item->get_contenttype();
    
            if ($typeid)
            {
                $items_by_type[$typeid][] = $item;
            }
        }
        
        if(is_array($items_by_type[4])){
            $total_topic_num = $total_topic_num - count($items_by_type[4]);
        }
        
        if (empty($items_by_type[$typeid])) $items_by_type[$typeid] = array();
        
        $return_list = array();
        $total_unread_num = 0;
        $index = -1;
        foreach ($items_by_type[$typeid] as $item)
        {
            $index++;
            $lastread = $vbulletin->userinfo['lastvisit'];
            
            if($vbulletin->GPC['showposts'] == false)
            {
                if(method_exists($item, 'get_thread'))
                {
                    $thread = $item->get_thread()->get_record();
                    $forum = $item->get_thread()->get_forum()->get_record();
                    $thread['threadread'] = $item->get_thread()->get_lastread($current_user);
                    
                    if ($vbulletin->options['threadmarking'] AND $vbulletin->userinfo['userid'])
                    {
                        $threadview = max($thread['threadread'], $thread['forumread'], TIMENOW - ($vbulletin->options['markinglimit'] * 86400));
                    }
                    else
                    {
                        $threadview = intval(fetch_bbarray_cookie('thread_lastview', $thread['threadid']));
                        if (!$threadview) $threadview = $lastread;
                    }
                    
                    $mobiquo_new_post= false;
                    if ($thread['lastpost'] > $threadview)
                    {
                        $thread['status']['new'] = 'new';
                        $mobiquo_new_post= true;
                        $total_unread_num++;
                        
                        if ($request_method == 'get_unread_topic' && ($index < $start_num || $index > $end_num) && !$vbulletin->options['threadmarking'])
                            continue;
                        
                    }
                    else // add for cookie based unread support
                    {
                        if ($request_method == 'get_unread_topic')
                        {
                            $index--;
                            $total_topic_num--;
                            continue;
                        }
                    }
                    
                    
                    if($thread['lastpostid']){
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
    
                    if (!$current_user->hasForumPermission($thread['forumid'], 'canviewthreads'))
                    {
                        $last_topic['pagetext'] = '';
                    }
    
                    $thread['issubscribed'] = $item->get_thread()->is_subscribed($current_user);
    
                    $mobiquo_can_move    = ($current_user->canModerateForum($thread['forumid'], 'canmanagethreads')) ? true : false;
                    $mobiquo_can_delete  = ($current_user->canModerateForum($thread['forumid'], 'candeleteposts') OR $current_user->canModerateForum($thread['forumid'], 'canremoveposts'))   ? true : false;;
                    $mobiquo_can_close   = ($current_user->canModerateForum($thread['forumid'], 'canopenclose')) ? true : false;
                    $mobiquo_isclosed = iif($thread['open'], false, true);
                    $mobiquo_isdeleted = $thread['visible'] == 2 ? true : false;
                    $mobiquo_can_approve = ($current_user->canModerateForum($thread['forumid'], 'canmoderateposts')) ? true : false;
                    $mobiquo_attach = iif(($thread['attach']>0),1,0);
                    
                    $thread['prefix_plain_html'] = '';
                    if ($thread['prefixid'])
                    {
                        $thread['prefix_plain_html'] = htmlspecialchars_uni($vbphrase["prefix_$thread[prefixid]_title_plain"]);
                    }
                    
                    $last_reply_time = mobiquo_iso8601_encode($thread['lastpost']-$vbulletin->options['hourdiff'], $vbulletin->userinfo['tzoffset']);
                    
                    $return_thread = array(
                        'forum_id'              => new xmlrpcval($thread['forumid'], 'string'),
                        'forum_name'            => new xmlrpcval(mobiquo_encode($forum['title_clean']), 'base64'),
                        'topic_id'              => new xmlrpcval($thread[threadid], 'string'),
                        'topic_title'           => new xmlrpcval(mobiquo_encode($thread['title']), 'base64'),
                        'prefix'                => new xmlrpcval(mobiquo_encode($thread['prefix_plain_html']), 'base64'),
                        'post_author_id'        => new xmlrpcval($thread['lastposterid'], 'string'),
                        'post_author_name'      => new xmlrpcval(mobiquo_encode($thread['lastposter']), 'base64'),
                      'last_reply_author_name'  => new xmlrpcval(mobiquo_encode($thread['lastposter']), 'base64'),
                        'last_reply_author_id'  => new xmlrpcval(mobiquo_encode($thread['lastposterid']), 'string'),
                        'reply_number'          => new xmlrpcval($thread['replycount'], 'int'),
                        'view_number'           => new xmlrpcval($thread['views'], 'int'),
                        'attachment'            => new xmlrpcval($mobiquo_attach, 'string'),
                        'can_subscribe'         => new xmlrpcval(true, 'boolean'),
                        'icon_url'              => new xmlrpcval(mobiquo_get_user_icon($thread['lastposterid']) , 'string'),
                        'short_content'         => new xmlrpcval(mobiquo_encode(mobiquo_chop($last_topic['pagetext'])), 'base64'),
                        'last_reply_time'       => new xmlrpcval($last_reply_time, 'dateTime.iso8601'),
                        'post_time'             => new xmlrpcval($last_reply_time, 'dateTime.iso8601'),
                        'time_string'           => new xmlrpcval(format_time_string($thread['lastpost']), 'base64'),
                        
                        'is_approved'           => new xmlrpcval($thread['visible'], 'boolean'),
                    );
                    
                    if ($mobiquo_new_post)      $return_thread['new_post']      = new xmlrpcval(true, 'boolean');
                    if ($mobiquo_can_close)     $return_thread['can_close']     = new xmlrpcval(true, 'boolean');
                    if ($mobiquo_isclosed)      $return_thread['is_closed']     = new xmlrpcval(true, 'boolean');
                    if ($mobiquo_can_delete)    $return_thread['can_delete']    = new xmlrpcval(true, 'boolean');
                    if ($mobiquo_isdeleted)     $return_thread['is_deleted']    = new xmlrpcval(true, 'boolean');
                    if ($mobiquo_can_move)      $return_thread['can_stick']     = new xmlrpcval(true, 'boolean');
                    if ($thread['sticky'])      $return_thread['is_sticky']     = new xmlrpcval(true, 'boolean');
                    if ($mobiquo_can_move)      $return_thread['can_move']      = new xmlrpcval(true, 'boolean');
                    if ($mobiquo_can_approve)   $return_thread['can_approve']   = new xmlrpcval(true, 'boolean');
                    //if ($thread['visible'])     $return_thread['is_approved']   = new xmlrpcval(true, 'boolean');
                    if ($thread['issubscribed'])$return_thread['is_subscribed'] = new xmlrpcval(true, 'boolean');
                    if ($mobiquo_can_delete || $mobiquo_can_approve) $return_thread['can_edit'] = new xmlrpcval(true, 'boolean');
                    
                    if($_SERVER['HTTP_MOBIQUO_ID'] == 11 || $_SERVER['HTTP_MOBIQUOID'] == 11)
                    {
                        $participated_uids = get_participated_uids($thread['threadid']);
                        $return_thread['participated_uids'] = new xmlrpcval($participated_uids , 'array');
                    }
                    
                    $xmlrpc_thread = new xmlrpcval($return_thread, 'struct');
                    
                    array_push($return_list, $xmlrpc_thread);
                }
            }
            else
            {
                if(method_exists($item, 'get_post'))
                {
                    $thread = $item->get_post()->get_thread()->get_record();
                    $forum = $item->get_post()->get_thread()->get_forum()->get_record();
                    $post = $item->get_post()->get_record();
                    
                    if( $vbulletin->options['threadmarking'] AND $thread['threadread'])
                    {
                        $threadview = $thread['threadread'];
                    }
                    else
                    {
                        $threadview = intval(fetch_bbarray_cookie('thread_lastview', $thread['threadid']));
                    }
                    $mobiquo_new_post= false;
                    if ($thread['lastpost'] > $lastread && $thread['lastpost'] > $threadview)
                    {
                        $thread['status']['new'] = 'new';
                        $mobiquo_new_post= true;
                    }
    
                    $mobiquo_can_move    = ($current_user->canModerateForum($thread['forumid'], 'canmanagethreads')) ? true : false;
                    $mobiquo_can_delete  = ($current_user->canModerateForum($thread['forumid'], 'candeleteposts') OR $current_user->canModerateForum($thread['forumid'], 'canremoveposts')) ? true : false;;
                    $mobiquo_can_approve = ($current_user->canModerateForum($thread['forumid'], 'canmoderateposts')) ? true : false;
                    $is_deleted = $post['visible'] == 2 ? true :false;
    
                    $return_post = array(
                        'forum_id'          => new xmlrpcval($thread['forumid'], 'string'),
                        'forum_name'        => new xmlrpcval(mobiquo_encode($forum['title_clean']), 'base64'),
                        'topic_id'          => new xmlrpcval($thread['threadid'], 'string'),
                        'post_id'           => new xmlrpcval($post['postid'], 'string'),
                        'topic_title'       => new xmlrpcval(mobiquo_encode($thread['title']), 'base64'),
                        'post_author_id'    => new xmlrpcval($post['userid'], 'string'),
                        'post_author_name'  => new xmlrpcval(mobiquo_encode($post['username']), 'base64'),
                        'reply_number'      => new xmlrpcval($thread['replycount'], 'int'),
                        'post_position'     => new xmlrpcval(0, 'int'),
                        'icon_url'          => new xmlrpcval(mobiquo_get_user_icon($post['userid']), 'string'),
                        'short_content'     => new xmlrpcval(mobiquo_encode(mobiquo_chop($post['pagetext'])), 'base64'),
                        'post_time'         => new xmlrpcval(mobiquo_iso8601_encode($post['dateline']-$vbulletin->options['hourdiff'], $vbulletin->userinfo['tzoffset']), 'dateTime.iso8601'),
                        'time_string'       => new xmlrpcval(format_time_string($post['dateline']), 'base64'),
                        
                        'is_approved'       => new xmlrpcval($post['visible'], 'boolean'),
                    );
                    
                    if ($mobiquo_new_post)      $return_post['new_post']    = new xmlrpcval(true, 'boolean');
                    if ($mobiquo_can_approve)   $return_post['can_approve'] = new xmlrpcval(true, 'boolean');
                    //if ($post['visible'])       $return_post['is_approved'] = new xmlrpcval(true, 'boolean');
                    if ($is_deleted)            $return_post['is_deleted']  = new xmlrpcval(true, 'boolean');
                    if ($mobiquo_can_delete)    $return_post['can_delete']  = new xmlrpcval(true, 'boolean');
                    if ($mobiquo_can_move)      $return_post['can_move']    = new xmlrpcval(true, 'boolean');
                    
                    $xmlrpc_post = new xmlrpcval($return_post, 'struct');
                    
                    array_push($return_list, $xmlrpc_post);
                }
            }
        }
    }
    
    if ($request_method == 'get_unread_topic') $total_unread_num = $total_topic_num;
    
    if ($include_topic_num) {
        if($vbulletin->GPC['showposts'] == false) {
            return new xmlrpcresp(new xmlrpcval(array(
                'result'            => new xmlrpcval(true, 'boolean'),
                'search_id'         => new xmlrpcval($vbulletin->GPC['searchid'], 'string'),
                'total_topic_num'   => new xmlrpcval($total_topic_num, 'int'),
                'total_unread_num'  => new xmlrpcval($total_unread_num, 'int'),
                'topics'            => new xmlrpcval($return_list, 'array'),
            ), 'struct'));
        } else {
            return new xmlrpcresp(new xmlrpcval(array(
                'result'            => new xmlrpcval(true, 'boolean'),
                'search_id'         => new xmlrpcval($vbulletin->GPC['searchid'], 'string'),
                'total_post_num'    => new xmlrpcval($total_topic_num, 'int'),
                'posts'             => new xmlrpcval($return_list, 'array'),
            ), 'struct'));
        }
    } else {
        return new xmlrpcresp(new xmlrpcval($return_list, 'array'));
    }
}
