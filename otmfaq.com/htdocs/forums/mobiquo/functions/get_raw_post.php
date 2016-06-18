<?php

defined('IN_MOBIQUO') or exit;

define('GET_EDIT_TEMPLATES', true);
define('CSRF_PROTECTION', false);
define('THIS_SCRIPT', 'editpost');

$phrasegroups = array();
$specialtemplates = array();
$globaltemplates = array();
$actiontemplates = array();


require_once('./global.php');
require_once(DIR . '/includes/functions_newpost.php');
require_once(DIR . '/includes/functions_bigthree.php');
require_once(DIR . '/includes/functions_editor.php');
require_once(DIR . '/includes/functions_log_error.php');


function get_raw_post_func($xmlrpc_params)
{
    global $vbulletin, $forumperms, $vbphrase;

    $decode_params = php_xmlrpc_decode($xmlrpc_params);
    $postid = intval($decode_params[0]);

    $vbulletin->GPC['postid'] = $postid;

    if ($vbulletin->GPC['postid'] AND $postinfo = mobiquo_verify_id('post', $vbulletin->GPC['postid'], 0, 1))
    {
        $postid =& $postinfo['postid'];
        $vbulletin->GPC['threadid'] =& $postinfo['threadid'];
    }

    // automatically query $threadinfo & $foruminfo if $threadid exists
    if ($vbulletin->GPC['threadid'] AND $threadinfo = mobiquo_verify_id('thread', $vbulletin->GPC['threadid'], 0, 1))
    {
        $threadid =& $threadinfo['threadid'];
        $vbulletin->GPC['forumid'] = $forumid = $threadinfo['forumid'];
        if ($forumid)
        {
            $foruminfo = fetch_foruminfo($threadinfo['forumid']);
            if (($foruminfo['styleoverride'] == 1 OR $vbulletin->userinfo['styleid'] == 0) AND !defined('BYPASS_STYLE_OVERRIDE'))
            {
                $codestyleid = $foruminfo['styleid'];
            }
        }

        if ($vbulletin->GPC['pollid'])
        {
            $pollinfo = verify_id('poll', $vbulletin->GPC['pollid'], 0, 1);
            $pollid =& $pollinfo['pollid'];
        }
    }
    
    if (!$postinfo['postid'] OR $postinfo['isdeleted'] OR (!$postinfo['visible'] AND !can_moderate($threadinfo['forumid'], 'canmoderateposts')))
    {
        return_fault(fetch_error('invalidid', $vbphrase['post']));
    }

    if (!$threadinfo['threadid'] OR $threadinfo['isdeleted'] OR (!$threadinfo['visible'] AND !can_moderate($threadinfo['forumid'], 'canmoderateposts')))
    {
        return_fault(fetch_error('invalidid', $vbphrase['thread']));
    }

    if ($vbulletin->options['wordwrap'])
    {
        $threadinfo['title'] = fetch_word_wrapped_string($threadinfo['title']);
    }

    // get permissions info
    $_permsgetter_ = 'edit post';
    $forumperms = fetch_permissions($threadinfo['forumid']);
    if (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canview'])
    OR
    !($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads']) OR
    (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewothers']) AND
    ($threadinfo['postuserid'] != $vbulletin->userinfo['userid'] OR $vbulletin->userinfo['userid'] == 0))
    )
    {
        return_fault();
    }

    $foruminfo = fetch_foruminfo($threadinfo['forumid'], false);

    // check if there is a forum password and if so, ensure the user has it set
    if (!verify_forum_password($foruminfo['forumid'], $foruminfo['password'], false))
        return_fault('Your administrator has required a password to access this forum.');

    // need to get last post-type information
    cache_ordered_forums(1);
    if (!can_moderate($threadinfo['forumid'], 'caneditposts'))
    { // check for moderator
        if (!$threadinfo['open'])
        {
            return_fault(fetch_error('threadclosed'));
        }
        if (!($forumperms & $vbulletin->bf_ugp_forumpermissions['caneditpost']))
        {
            return_fault();
        }
        else
        {
            if ($vbulletin->userinfo['userid'] != $postinfo['userid'])
            {
                return_fault();
            }
            else
            {
                // check for time limits
                if ($postinfo['dateline'] < (TIMENOW - ($vbulletin->options['edittimelimit'] * 60)) AND $vbulletin->options['edittimelimit'] != 0)
                {
                    return_fault(fetch_error('edittimelimit', $vbulletin->options['edittimelimit']));
                }
            }
        }
    }
    
    $post_content = mobiquo_encode($postinfo['pagetext']);
    $post_title   = mobiquo_encode($postinfo['title']);
    $return_data = array(
        'post_id'       => new xmlrpcval($postid, 'string'),
        'post_title'    => new xmlrpcval($post_title, 'base64'),
        'post_content'  => new xmlrpcval($post_content, 'base64'),
    );

    return new xmlrpcresp(new xmlrpcval($return_data, 'struct'));
}
