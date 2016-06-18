<?php

defined('IN_MOBIQUO') or exit;

mobi_parse_requrest();

$function_file_name = $request_method;

switch ($request_method)
{
    case 'login_mod': $function_file_name = 'login'; break;
    case 'get_thread':
    case 'get_thread_by_post':
    case 'get_thread_by_unread': $function_file_name = 'get_thread'; break;
    case 'get_forum':
    case 'get_forum_all': $function_file_name = 'get_forum'; break;
    case 'get_box_info':
    case 'get_box':
    case 'get_message':
    case 'delete_message':
    case 'create_message':
    case 'mark_pm_unread':
    case 'get_quote_pm':
    case 'mark_pm_unread':
    case 'report_pm': $function_file_name = 'get_pm_stat'; break;
    
    
    // Search related function
    case 'search':
        $function_file_name = 'search';
        $include_topic_num = true;
        $search_filter = $request_params[0];
        $_GET['pagenumber'] = isset($search_filter['page']) ? $search_filter['page'] : 1;
        $_GET['perpage'] = isset($search_filter['perpage']) ? $search_filter['perpage'] : 20;
        
        if (isset($search_filter['searchid']) && !empty($search_filter['searchid']))
        {
            $_GET['searchid'] = $search_filter['searchid'];
        }
        else
        {
            $_POST['dosearch'] = 'Search Now';
            $_POST['searchfromtype'] = 'vBForum:Post';
            $_POST['do'] = 'process';
            $_POST['contenttypeid'] = 1;
            $_POST['childforums'] = 1;
            $_POST['sortby'] = 'dateline';
            $_POST['order'] = 'descending';
            
            isset($search_filter['keywords']) && $_POST['query'] = $search_filter['keywords'];
            isset($search_filter['userid']) && $_POST['userid'] = $search_filter['userid'];
            isset($search_filter['searchuser']) && $_POST['searchuser'] = $search_filter['searchuser'];
            isset($search_filter['forumid']) && $_POST['f'] = $search_filter['forumid'];
            isset($search_filter['threadid']) && $_POST['searchthreadid'] = $search_filter['threadid'];
            isset($search_filter['titleonly']) && $_POST['titleonly'] = $search_filter['titleonly'];
            isset($search_filter['showposts']) && $_POST['showposts'] = $search_filter['showposts'];
            
            if (isset($search_filter['searchtime']) && is_numeric($search_filter['searchtime']))
            {
                $_POST['searchdate'] = $search_filter['searchtime']/86400;
                $_POST['beforeafter'] = 'after';
            }
            
            if (isset($search_filter['only_in']) && is_array($search_filter['only_in']))
            {
                $_GET['include'] = implode(', ', array_map('intval', $search_filter['only_in']));
            }
            
            if (isset($search_filter['not_in']) && is_array($search_filter['not_in']))
            {
                $_GET['exclude'] = implode(', ', array_map('intval', $search_filter['not_in']));
            }
        }
        break;
    case 'get_unread_topic':
        $function_file_name = 'search';
        $include_topic_num = true;
        list($start, $limit, $page) = process_page($request_params[0], $request_params[1]);
        $_GET['contenttype'] = 'vBForum_Post';
        $_GET['pagenumber'] = $page;
        $_GET['perpage'] = $limit;
        
        if (isset($request_params[2]) && intval($request_params[2]))
        {
            $_GET['searchid'] = intval($request_params[2]);
        }
        else
        {
            $_GET['do'] = 'getnew';
            if (isset($request_params[3]))
            {
                if (isset($request_params[3]['only_in']) && is_array($request_params[3]['only_in']))
                {
                    $_GET['include'] = implode(', ', array_map('intval', $request_params[3]['only_in']));
                }
                
                if (isset($request_params[3]['not_in']) && is_array($request_params[3]['not_in']))
                {
                    $_GET['exclude'] = implode(', ', array_map('intval', $request_params[3]['not_in']));
                }
            }
        }
        break;
    case 'get_participated_topic':
        $function_file_name = 'search';
        $include_topic_num = true;
        list($start, $limit, $page) = process_page($request_params[1], $request_params[2]);
        $_GET['pagenumber'] = $page;
        $_GET['perpage'] = $limit;
        
        if (isset($request_params[3]) && intval($request_params[3]))
        {
            $_GET['searchid'] = intval($request_params[3]);
        }
        else
        {
            $_POST['starteronly'] = 0;
            $_POST['childforums'] = 1;
            $_POST['searchdate'] = 0;
            $_POST['replyless'] = 0;
            $_POST['beforeafter'] = 'after';
            $_POST['sortby'] = 'dateline';
            $_POST['order'] = 'descending';
            $_POST['showposts'] = 0;
            $_POST['saveprefs'] = 1;
            $_POST['dosearch'] = 'Search Now';
            $_POST['searchfromtype'] = 'vBForum:Post';
            $_POST['do'] = 'process';
            $_POST['contenttypeid'] = 1;
            
            if (isset($request_params[4]) && intval($request_params[4])) {
                $_POST['userid'] = intval($request_params[4]);
            } else {
                $_POST['searchuser'] = $request_params[0];
                $_POST['exactname'] = 1;
            }
        }
        break;
    case 'get_latest_topic':
        $function_file_name = 'search';
        $include_topic_num = true;
        list($start, $limit, $page) = process_page($request_params[0], $request_params[1]);
        $_GET['contenttype'] = 'vBForum_Post';
        $_GET['pagenumber'] = $page;
        $_GET['perpage'] = $limit;
        
        if (isset($request_params[2]) && intval($request_params[2]))
        {
            $_GET['searchid'] = intval($request_params[2]);
        }
        else
        {
            $_GET['do'] = 'getdaily';
            $_GET['days'] = 3;
            if (isset($request_params[3]))
            {
                if (isset($request_params[3]['only_in']) && is_array($request_params[3]['only_in']))
                {
                    $_GET['include'] = implode(', ', array_map('intval', $request_params[3]['only_in']));
                }
                
                if (isset($request_params[3]['not_in']) && is_array($request_params[3]['not_in']))
                {
                    $_GET['exclude'] = implode(', ', array_map('intval', $request_params[3]['not_in']));
                }
            }
        }
        break;
    case 'search_topic':
        $function_file_name = 'search';
        $include_topic_num = true;
        list($start, $limit, $page) = process_page($request_params[1], $request_params[2]);
        $_GET['pagenumber'] = $page;
        $_GET['perpage'] = $limit;
        
        if (isset($request_params[3]) && intval($request_params[3]))
        {
            $_GET['searchid'] = intval($request_params[3]);
        }
        else
        {
            $_POST['query'] = $request_params[0];
            $_POST['childforums'] = 1;
            $_POST['searchdate'] = 0;
            $_POST['replyless'] = 0;
            $_POST['beforeafter'] = 'after';
            $_POST['sortby'] = 'dateline';
            $_POST['order'] = 'descending';
            $_POST['showposts'] = 0;
            $_POST['saveprefs'] = 1;
            $_POST['dosearch'] = 'Search Now';
            $_POST['searchfromtype'] = 'vBForum:Post';
            $_POST['do'] = 'process';
            $_POST['contenttypeid'] = 1;
            
            if (isset($request_params[4]))
            {
                if (isset($request_params[4]['only_in']) && is_array($request_params[4]['only_in']))
                {
                    $_GET['include'] = implode(', ', array_map('intval', $request_params[4]['only_in']));
                }
                
                if (isset($request_params[4]['not_in']) && is_array($request_params[4]['not_in']))
                {
                    $_GET['exclude'] = implode(', ', array_map('intval', $request_params[4]['not_in']));
                }
            }
        }
        break;
    case 'search_post':
        $function_file_name = 'search';
        $include_topic_num = true;
        list($start, $limit, $page) = process_page($request_params[1], $request_params[2]);
        $_GET['pagenumber'] = $page;
        $_GET['perpage'] = $limit;
        
        if (isset($request_params[3]) && intval($request_params[3]))
        {
            $_GET['searchid'] = intval($request_params[3]);
        }
        else
        {
            $_POST['query'] = $request_params[0];
            $_POST['childforums'] = 1;
            $_POST['searchdate'] = 0;
            $_POST['replyless'] = 0;
            $_POST['beforeafter'] = 'after';
            $_POST['sortby'] = 'dateline';
            $_POST['order'] = 'descending';
            $_POST['showposts'] = 1;
            $_POST['saveprefs'] = 1;
            $_POST['dosearch'] = 'Search Now';
            $_POST['searchfromtype'] = 'vBForum:Post';
            $_POST['do'] = 'process';
            $_POST['contenttypeid'] = 1;
            
            if (isset($request_params[4]))
            {
                if (isset($request_params[4]['only_in']) && is_array($request_params[4]['only_in']))
                {
                    $_GET['include'] = implode(', ', array_map('intval', $request_params[4]['only_in']));
                }
                
                if (isset($request_params[4]['not_in']) && is_array($request_params[4]['not_in']))
                {
                    $_GET['exclude'] = implode(', ', array_map('intval', $request_params[4]['not_in']));
                }
            }
        }
        break;
    case 'get_user_topic':
        $function_file_name = 'search';
        $include_topic_num = false;
        $_GET['pagenumber'] = 1;
        $_GET['perpage'] = 20;
        
        $_POST['starteronly'] = 1;
        $_POST['contenttype'] = 'vBForum_Thread';
        $_POST['do'] = 'finduser';
        
        if (isset($request_params[1]) && intval($request_params[1])) {
            $_POST['userid'] = intval($request_params[1]);
        } else {
            $_POST['searchuser'] = $request_params[0];
            $_POST['exactname'] = 1;
        }
        break;
    case 'get_user_reply_post':
        $function_file_name = 'search';
        $include_topic_num = false;
        $_GET['pagenumber'] = 1;
        $_GET['perpage'] = 20;
        
        $_GET['showposts'] = 1;
        $_GET['contenttype'] = 'vBForum_Post';
        $_GET['do'] = 'finduser';
        
        if (isset($request_params[1]) && intval($request_params[1])) {
            $_POST['userid'] = intval($request_params[1]);
        } else {
            $_POST['searchuser'] = $request_params[0];
            $_POST['exactname'] = 1;
        }
        break;
    
    
    // moderation related functions
    case 'm_stick_topic':
        $_POST['tlist'] = array($request_params[0] => 'on');
        $_POST['do'] = $request_params[1] == 1 ? 'stick' : 'unstick';
        break;
    case 'm_close_topic':
        $_POST['tlist'] = array($request_params[0] => 'on');
        $_POST['do'] = $request_params[1] == 1 ? 'open' : 'close';
        break;
    case 'm_delete_topic':
        $_POST['deletetype'] = isset($request_params[1]) ? $request_params[1] : 1;
        $_POST['deletereason'] = isset($request_params[2]) ? $request_params[2] : '';
        $_POST['threadids'] = $request_params[0];
        $_POST['do'] = 'dodeletethreads';
        break;
    case 'm_delete_post':
        $_POST['deletetype'] = isset($request_params[1]) ? $request_params[1] : 1;
        $_POST['deletereason'] = isset($request_params[2]) ? $request_params[2] : '';
        $_POST['postids'] = $request_params[0];
        $_POST['do'] = 'dodeleteposts';
        break;
    case 'm_undelete_topic':
        $_POST['tlist'] = array($request_params[0] => 'on');
        $_POST['do'] = 'undeletethread';
        break;
    case 'm_undelete_post':
        $_POST['plist'] = array($request_params[0] => 'on');
        $_POST['do'] = 'undeleteposts';
        break;
    case 'm_move_topic':
        $_POST['destforumid'] = $request_params[1];
        $_POST['threadids'] = $request_params[0];
        $_POST['do'] = 'domovethreads';
        break;
    case 'm_rename_topic':
        $_POST['title'] = $request_params[1];
        $_POST['tlist'] = array($request_params[0] => 'on');
        $_POST['do'] = 'renamethread';
        break;
    case 'm_move_post':
        if (empty($request_params[1])) {
            $_POST['type'] = 0;
            $_POST['destforumid'] = $request_params[3];
            $_POST['title'] = $request_params[2];
        } else {
            $_POST['type'] = 1;
            $_POST['destthreadid'] = $request_params[1];
        }
        $_POST['postids'] = $request_params[0];
        $_POST['do'] = 'domoveposts';
        break;
    case 'm_merge_topic':
        $_POST['destthreadid'] = $request_params[1];
        $_POST['redir'] = 1;
        $_POST['redirect'] = 'perm';
        $_POST['threadids'] = $request_params[0].','.$request_params[1];
        $_POST['do'] = 'domergethreads';
        break;
    case 'm_approve_topic':
        $_POST['tlist'] = array($request_params[0] => 'on');
        $_POST['do'] = $request_params[1] == 1 ? 'approvethread' : 'unapprovethread';
        break;
    case 'm_approve_post':
        $_POST['plist'] = array($request_params[0] => 'on');
        $_POST['do'] = $request_params[1] == 1 ? 'approveposts' : 'unapproveposts';
        break;
    case 'm_ban_user':
        $_POST['username'] = $request_params[0];
        $_POST['period'] = 'PERMANENT';
        $_POST['reason'] = $request_params[2];
        $_POST['do'] = 'dodeletespam';
        $_POST['useraction'] = 'ban';
        $_POST['deleteother'] = $request_params[1] == 2 ? 1 : 0;
        $_POST['deletetype'] = 1;
        break;
}


if (strpos($request_method, 'm_') === 0 && strpos($request_method, 'm_get') !== 0)
    $function_file_name = 'inlinemod';

foreach($_GET  as $key => $value) $_REQUEST[$key] = $value;
foreach($_POST as $key => $value) $_REQUEST[$key] = $value;

error_reporting(MOBIQUO_DEBUG);
