<?php

defined('IN_MOBIQUO') or exit;

require_once('./global.php');
require_once(DIR . '/vb/search/core.php');
require_once(DIR . '/vb/search/results.php');
require_once(DIR . '/vb/legacy/currentuser.php');

function get_topic_status_func($xmlrpc_params)
{
    global $vbulletin, $vbphrase;
    
    $params = php_xmlrpc_decode($xmlrpc_params);
    if (!is_array($params[0]))
        return_fault(fetch_error('invalidid', $vbphrase['thread']));
    
    $threadids = $vbulletin->input->clean($params[0], TYPE_ARRAY_UINT);
    $threadids = array_unique($threadids);
    
    if (empty($threadids))
    {
        return_fault(fetch_error('you_did_not_select_any_valid_threads'));
    }

    if (count($threadids) > 100)
    {
        return_fault(fetch_error('you_are_limited_to_working_with_x_threads', 100));
    }
    
    
    $typeid = vB_Search_Core::get_instance()->get_contenttypeid('vBForum', 'Thread');
    $result_array = array();
    foreach ($threadids as $id)
    {
        $result_array[] = array($typeid, $id);
    }
    
    $current_user = new vB_Legacy_CurrentUser();
    $results = vB_Search_Results::create_from_array($current_user, $result_array);
    
    $page_results = $results->get_page(1, count($result_array), 1);
    
    $return_list = array();
    foreach ($page_results as $index => $item)
    {
        $thread = $item->get_thread()->get_record();
        $thread['threadread'] = $item->get_thread()->get_lastread($current_user);
        if( $vbulletin->options['threadmarking'] AND $thread['threadread'])
        {
            $threadview = $thread['threadread'];
        }
        else
        {
            $threadview = intval(fetch_bbarray_cookie('thread_lastview', $thread['threadid']));
        }

        $mobiquo_new_post= false;
        $lastread = $vbulletin->userinfo['lastvisit'];
        
        if ($thread['lastpost'] > $lastread && $thread['lastpost'] > $threadview)
        {
            $thread['status']['new'] = 'new';
            $mobiquo_new_post= true;
        }
        
        
        $thread['issubscribed'] = $item->get_thread()->is_subscribed($current_user);
        
        $mobiquo_can_move    = ($current_user->canModerateForum($thread[forumid], 'canmanagethreads')) ? true : false;
        $mobiquo_can_delete  = ($current_user->canModerateForum($thread[forumid], 'candeleteposts') OR $current_user->canModerateForum($thread['forumid'], 'canremoveposts')) ? true : false;;
        $mobiquo_can_close   = ($current_user->canModerateForum($thread[forumid], 'canopenclose')) ? true : false;
        $mobiquo_isclosed = iif($thread['open'], false, true);
        $mobiquo_isdeleted = $thread['visible'] == 2 ? true : false;
        $mobiquo_can_approve = ($current_user->canModerateForum($thread[forumid], 'canmoderateposts')) ? true : false;
        $last_reply_time = mobiquo_iso8601_encode($thread['lastpost']-$vbulletin->options['hourdiff'], $vbulletin->userinfo['tzoffset']);
        
        $return_thread = array(
            'topic_id'          => new xmlrpcval($thread['threadid'], 'string'),
            'reply_number'      => new xmlrpcval($thread['replycount'], 'int'),
            'view_number'       => new xmlrpcval($thread['views'], 'int'),
            'can_subscribe'     => new xmlrpcval(true, 'boolean'),
            'last_reply_time'   => new xmlrpcval($last_reply_time, 'dateTime.iso8601'),
            'time_string'       => new xmlrpcval(format_time_string($thread['lastpost']), 'base64'),
            
            'is_approved'       => new xmlrpcval($thread['visible'], 'boolean'),
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
        
        $xmlrpc_thread = new xmlrpcval($return_thread, 'struct');
        
        array_push($return_list, $xmlrpc_thread);
    }
    
    return new xmlrpcresp(new xmlrpcval(array(
        'result'    => new xmlrpcval(true, 'boolean'),
        'status'    => new xmlrpcval($return_list, 'array'),
    ), 'struct'));
}
