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

$server_param = array(
    
    'login' => array(
        'function' => 'login_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcBase64, $xmlrpcBase64),
                             array($xmlrpcStruct, $xmlrpcBase64, $xmlrpcBase64, $xmlrpcBoolean)),
    ),
    
    'login_mod' => array(
        'function' => 'login_mod_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcBase64, $xmlrpcBase64)),
    ),
    
    'logout_user' => array(
        'function' => 'logout_func',
        'signature' => array(array($xmlrpcArray)),
    ),
    
    'get_forum' => array(
        'function' => 'get_forum_func',
        'signature' => array(array($xmlrpcArray),
                             array($xmlrpcArray, $xmlrpcBoolean),
                             array($xmlrpcArray, $xmlrpcBoolean, $xmlrpcString)),
    ),
    
    'get_forum_all' => array(
        'function' => 'get_forum_all_func',
        'signature' => array(array($xmlrpcArray)),
    ),
    
    'get_topic' => array(
        'function' => 'get_topic_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcInt, $xmlrpcInt, $xmlrpcString, $xmlrpcBase64),
                             array($xmlrpcStruct, $xmlrpcString, $xmlrpcInt, $xmlrpcInt, $xmlrpcString),
                             array($xmlrpcStruct, $xmlrpcString, $xmlrpcInt, $xmlrpcInt),
                             array($xmlrpcStruct, $xmlrpcString, $xmlrpcInt),
                             array($xmlrpcStruct, $xmlrpcString)),
    ),
    
    'get_thread' => array(
        'function' => 'get_thread_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcInt, $xmlrpcInt, $xmlrpcBoolean),
                             array($xmlrpcStruct, $xmlrpcString, $xmlrpcInt, $xmlrpcInt),
                             array($xmlrpcStruct, $xmlrpcString)),
    ),
    
    'get_thread_by_post' => array(
        'function' => 'get_thread_by_post_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcInt, $xmlrpcBoolean),
                             array($xmlrpcStruct, $xmlrpcString, $xmlrpcInt),
                             array($xmlrpcStruct, $xmlrpcString)),
    ),
    
    'get_thread_by_unread' => array(
        'function' => 'get_thread_by_unread_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcInt, $xmlrpcBoolean),
                             array($xmlrpcStruct, $xmlrpcString, $xmlrpcInt),
                             array($xmlrpcStruct, $xmlrpcString)),
    ),
    
    'get_user_topic' => array(
        'function' => 'search_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcBase64),
                             array($xmlrpcStruct, $xmlrpcBase64, $xmlrpcString)),
    ),
    
    'get_user_reply_post' => array(
        'function' => 'search_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcBase64),
                             array($xmlrpcStruct, $xmlrpcBase64, $xmlrpcString)),
    ),
    
    'get_user_info' => array(
        'function' => 'get_user_info_func',
        'signature' => array(array($xmlrpcStruct),
                             array($xmlrpcStruct, $xmlrpcBase64),
                             array($xmlrpcStruct, $xmlrpcBase64, $xmlrpcString)),
    ),
    
    'get_friend_list' => array(
        'function' => 'get_friend_list_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcBase64)),
    ),
    
    'add_friend' => array(
        'function' => 'add_friend_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcBase64)),
    ),
    
    'remove_friend' => array(
        'function' => 'remove_friend_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcBase64)),
    ),
    
    'get_new_topic' => array(
        'function' => 'get_new_topic_func',
        'signature' => array(array($xmlrpcArray),
                             array($xmlrpcStruct, $xmlrpcInt, $xmlrpcInt)),
    ),
    
    'get_latest_topic' => array(
        'function' => 'search_func',
        'signature' => array(array($xmlrpcArray),
                             array($xmlrpcArray, $xmlrpcInt, $xmlrpcInt),
                             array($xmlrpcArray, $xmlrpcInt, $xmlrpcInt, $xmlrpcString),
                             array($xmlrpcArray, $xmlrpcInt, $xmlrpcInt, $xmlrpcString, $xmlrpcStruct)),
    ),
    
    'get_config' => array(
        'function' => 'get_config_func',
        'signature' => array(array($xmlrpcArray)),
    ),
    
    'reply_post' => array(
        'function' => 'reply_post_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcBase64, $xmlrpcBase64),
                             array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcBase64, $xmlrpcBase64, $xmlrpcArray),
                             array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcBase64, $xmlrpcBase64, $xmlrpcArray, $xmlrpcString),
                             array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcBase64, $xmlrpcBase64, $xmlrpcArray, $xmlrpcString, $xmlrpcBoolean)),
    ),
    
    'new_topic' => array(
        'function' => 'new_topic_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcBase64, $xmlrpcBase64),
                             array($xmlrpcStruct, $xmlrpcString, $xmlrpcBase64, $xmlrpcBase64, $xmlrpcString),
                             array($xmlrpcStruct, $xmlrpcString, $xmlrpcBase64, $xmlrpcBase64, $xmlrpcString, $xmlrpcArray),
                             array($xmlrpcStruct, $xmlrpcString, $xmlrpcBase64, $xmlrpcBase64, $xmlrpcString, $xmlrpcArray, $xmlrpcString)),
    ),
    
    'get_subscribed_topic' => array(
        'function' => 'get_subscribed_topic_func',
        'signature' => array(array($xmlrpcStruct),
                             array($xmlrpcStruct, $xmlrpcInt, $xmlrpcInt)),
    ),
    
    'get_subscribed_forum' => array(
        'function' => 'get_subscribed_forum_func',
        'signature' => array(array($xmlrpcStruct)),
    ),
    
    'subscribe_topic' => array(
        'function' => 'subscribe_topic_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString),
                             array($xmlrpcStruct, $xmlrpcString, $xmlrpcInt)),
    ),
    
    'subscribe_forum' => array(
        'function' => 'subscribe_forum_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString),
                             array($xmlrpcStruct, $xmlrpcString, $xmlrpcInt)),
    ),
    
    'unsubscribe_forum' => array(
        'function' => 'unsubscribe_forum_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString)),
    ),
    
    'unsubscribe_topic' => array(
        'function' => 'unsubscribe_topic_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString)),
    ),
    
    'create_message' => array(
        'function' => 'create_message_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcArray, $xmlrpcBase64, $xmlrpcBase64, $xmlrpcInt, $xmlrpcString),
                             array($xmlrpcStruct, $xmlrpcArray, $xmlrpcBase64, $xmlrpcBase64),
                             array($xmlrpcStruct, $xmlrpcBase64, $xmlrpcBase64, $xmlrpcBase64),
                             array($xmlrpcStruct, $xmlrpcBase64, $xmlrpcBase64, $xmlrpcBase64, $xmlrpcInt, $xmlrpcString)),
    ),
    
    'get_inbox_stat' => array(
        'function' => 'get_inbox_stat_func',
        'signature' => array(array($xmlrpcStruct),
                             array($xmlrpcStruct, $xmlrpcString),
                             array($xmlrpcStruct, $xmlrpcString, $xmlrpcString)),
    ),
    
    'get_box_info' => array(
        'function' => 'get_box_info_func',
        'signature' => array(array($xmlrpcStruct)),
    ),
    
    'get_box' => array(
        'function' => 'get_box_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcInt, $xmlrpcInt),
                             array($xmlrpcStruct, $xmlrpcString)),
    ),
    
    'get_message' => array(
        'function' => 'get_message_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString),
                             array($xmlrpcStruct, $xmlrpcString, $xmlrpcString),
                             array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcBoolean)),
    ),
    
    'delete_message' => array(
        'function' => 'delete_message_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString) ,
                             array($xmlrpcStruct, $xmlrpcString, $xmlrpcString)),
    ),
    
    'get_board_stat' => array(
        'function' => 'get_board_stat_func',
        'signature' => array(array($xmlrpcStruct)),
    ),
    
    'get_online_users' => array(
        'function' => 'get_online_users_func',
        'signature' => array(array($xmlrpcStruct),
                             array($xmlrpcStruct, $xmlrpcString),
                             array($xmlrpcStruct, $xmlrpcString, $xmlrpcString)),
    ),
    
    'save_raw_post' => array(
        'function' => 'save_raw_post_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcBase64, $xmlrpcBase64),
                             array($xmlrpcStruct, $xmlrpcString, $xmlrpcBase64, $xmlrpcBase64, $xmlrpcBoolean)),
    ),
    
    'get_raw_post' => array(
        'function' => 'get_raw_post_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString)),
    ),
    
    'remove_attachment' => array(
        'function' => 'remove_attachment_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString),
                             array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString)),
    ),
    
    'search_topic' => array(
        'function' => 'search_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcBase64, $xmlrpcInt, $xmlrpcInt, $xmlrpcString, $xmlrpcStruct),
                             array($xmlrpcStruct, $xmlrpcBase64, $xmlrpcInt, $xmlrpcInt, $xmlrpcString),
                             array($xmlrpcStruct, $xmlrpcBase64, $xmlrpcInt, $xmlrpcInt)),
    ),
    
    'search_post' => array(
        'function' => 'search_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcBase64, $xmlrpcInt, $xmlrpcInt, $xmlrpcString, $xmlrpcStruct),
                             array($xmlrpcStruct, $xmlrpcBase64, $xmlrpcInt, $xmlrpcInt, $xmlrpcString),
                             array($xmlrpcStruct, $xmlrpcBase64, $xmlrpcInt, $xmlrpcInt)),
    ),
    
    'search' => array(
        'function' => 'search_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcStruct)),
    ),
    
    'mark_all_as_read' => array(
        'function' => 'mark_all_as_read_func',
        'signature' => array(array($xmlrpcArray), 
                             array($xmlrpcStruct, $xmlrpcString)),
    ),
    
    'get_unread_topic' => array(
        'function' => 'search_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcInt, $xmlrpcInt, $xmlrpcString, $xmlrpcStruct),
                             array($xmlrpcStruct, $xmlrpcInt, $xmlrpcInt, $xmlrpcString),
                             array($xmlrpcStruct, $xmlrpcInt, $xmlrpcInt),
                             array($xmlrpcArray)),
    ),
    
    'get_quote_post' => array(
        'function' => 'get_quote_post_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString)),
    ),
    
    'mark_pm_unread' => array(
        'function' => 'mark_pm_unread_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString),
                             array($xmlrpcStruct, $xmlrpcString, $xmlrpcString)),
    ),
    
    'get_quote_pm' => array(
        'function' => 'get_quote_pm_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString)),
    ),
    
    'get_participated_topic' => array(
        'function' => 'search_func',
        'signature' => array(array($xmlrpcArray, $xmlrpcBase64, $xmlrpcInt, $xmlrpcInt, $xmlrpcString, $xmlrpcString),
                             array($xmlrpcArray, $xmlrpcBase64, $xmlrpcInt, $xmlrpcInt, $xmlrpcString),
                             array($xmlrpcArray, $xmlrpcBase64, $xmlrpcInt, $xmlrpcInt),
                             array($xmlrpcArray, $xmlrpcBase64)),
    ),
    
    'report_post' => array(
        'function' => 'report_post_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString),
                             array($xmlrpcStruct, $xmlrpcString, $xmlrpcBase64)),
    ),
    
    'report_pm' => array(
        'function' => 'report_pm_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString),
                             array($xmlrpcStruct, $xmlrpcString, $xmlrpcBase64)),
    ),
    
    'login_forum' => array(
        'function' => 'login_forum_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcBase64)),
    ),
    
    'get_announcement' => array(
        'function' => 'get_announcement_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcInt, $xmlrpcInt),
                             array($xmlrpcStruct, $xmlrpcString, $xmlrpcInt, $xmlrpcInt, $xmlrpcBoolean),
                             array($xmlrpcStruct, $xmlrpcString)),
    ),
    
    'get_participated_forum' => array(
        'function' => 'get_participated_forum_func',
        'signature' => array(array($xmlrpcStruct)),
    ),
    
    'mark_topic_read' => array(
        'function' => 'mark_topic_read_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcArray)),
    ),
    
    'get_forum_status' => array(
        'function' => 'get_forum_status_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcArray)),
    ),
    
    'get_topic_status' => array(
        'function' => 'get_topic_status_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcArray)),
    ),
    
    'get_id_by_url' => array(
        'function'  => 'get_id_by_url_func',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString)),
    ),
    
    "thank_post" => array(
        "function" => "thank_post_func",
        "signature" => array(array($xmlrpcStruct, $xmlrpcString)),
    ),
    
    'get_smilies' => array(
        'function' => 'get_smilies_func',
        'signature' => array(array($xmlrpcStruct)),
    ),
    
    
    //**********************************************
    // Moderation functions
    //**********************************************
    
    'm_get_moderate_topic' => array(
        'function' => 'get_moderate_topic_func',
        'signature' => array(array($xmlrpcArray, $xmlrpcInt, $xmlrpcInt),
                             array($xmlrpcArray)),
    ),
    
    'm_get_moderate_post' => array(
        'function' => 'get_moderate_post_func',
        'signature' => array(array($xmlrpcArray, $xmlrpcInt, $xmlrpcInt),
                             array($xmlrpcArray)),
    ),
    
    'm_get_delete_topic' => array(
        'function' => 'get_delete_topic_func',
        'signature' => array(array($xmlrpcArray, $xmlrpcInt, $xmlrpcInt),
                             array($xmlrpcArray)),
    ),
    
    'm_get_delete_post' => array(
        'function' => 'get_delete_post_func',
        'signature' => array(array($xmlrpcArray, $xmlrpcInt, $xmlrpcInt),
                             array($xmlrpcArray)),
    ),
    
    'm_delete_topic' => array(
        'function' => 'return_mod_true',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString),
                             array($xmlrpcStruct, $xmlrpcString, $xmlrpcInt),
                             array($xmlrpcStruct, $xmlrpcString, $xmlrpcInt, $xmlrpcBase64)),
    ),
    
    'm_undelete_topic' => array(
        'function' => 'return_mod_true',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString),
                             array($xmlrpcStruct, $xmlrpcString, $xmlrpcBase64)),
    ),
    
    'm_delete_post' => array(
        'function' => 'return_mod_true',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString),
                             array($xmlrpcStruct, $xmlrpcString, $xmlrpcInt),
                             array($xmlrpcStruct, $xmlrpcString, $xmlrpcInt, $xmlrpcBase64)),
    ),
    
    'm_undelete_post' => array(
        'function' => 'return_mod_true',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString),
                             array($xmlrpcStruct, $xmlrpcString, $xmlrpcBase64)),
    ),
    
    'm_ban_user' => array(
        'function' => 'return_mod_true',
        'signature' => array(array($xmlrpcStruct, $xmlrpcBase64, $xmlrpcInt),
                             array($xmlrpcStruct, $xmlrpcBase64, $xmlrpcInt, $xmlrpcBase64)),
    ),
    
    'm_stick_topic' => array(
        'function' => 'return_mod_true',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcInt)),
    ),
    
    'm_close_topic' => array(
        'function' => 'return_mod_true',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcInt)),
    ),
    
    'm_approve_topic' => array(
        'function' => 'return_mod_true',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcInt)),
    ),
    
    'm_approve_post' => array(
        'function' => 'return_mod_true',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcInt)),
    ),
    
    'm_move_topic' => array(
        'function' => 'return_mod_true',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString)),
    ),
    
    'm_move_post' => array(
        'function' => 'return_mod_true',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString),
                             array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcBase64, $xmlrpcString)),
    ),
    
    'm_merge_topic' => array(
        'function' => 'return_mod_true',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString)),
    ),
    
    'm_rename_topic' => array(
        'function' => 'return_mod_true',
        'signature' => array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcBase64)),
    ),

);
