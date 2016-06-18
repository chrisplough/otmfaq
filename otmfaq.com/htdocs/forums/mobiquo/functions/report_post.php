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

define('THIS_SCRIPT', 'report');
define('CSRF_PROTECTION', false);

$phrasegroups = array('messaging');
$specialtemplates = array();
$globaltemplates = array(
    'newpost_usernamecode',
    'reportitem'
);

$actiontemplates = array();

require_once('./global.php');
require_once(DIR . '/includes/functions_misc.php');
require_once(DIR . '/includes/class_reportitem.php');


function report_post_func($xmlrpc_params)
{
    global $vbulletin, $vbphrase;

    $decode_params = php_xmlrpc_decode($xmlrpc_params);
    $post_id = intval($decode_params[0]);
    if(isset($decode_params[1]) && strlen($decode_params[1]) > 0){
        $report_message= mobiquo_encode($decode_params[1], 'to_local');
    } else {
        $report_message= 'report';
    }

    $vbulletin->GPC['postid'] = $post_id;

    // automatically query $postinfo, $threadinfo & $foruminfo if $threadid exists
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

    if (!$vbulletin->userinfo['userid'])
    {
        return_fault();
    }

    $reportthread = ($rpforumid = $vbulletin->options['rpforumid'] AND $rpforuminfo = fetch_foruminfo($rpforumid));
    $reportemail = ($vbulletin->options['enableemail'] AND $vbulletin->options['rpemail']);

    if (!$reportthread AND !$reportemail)
    {
        return_fault(fetch_error('emaildisabled'));
    }

    $reportobj = new vB_ReportItem_Post($vbulletin);
    $reportobj->set_extrainfo('forum', $foruminfo);
    $reportobj->set_extrainfo('thread', $threadinfo);
    $perform_floodcheck = $reportobj->need_floodcheck();
    $result = true;
    if ($perform_floodcheck)
    {
        $result = $reportobj->perform_floodcheck_precommit();
    }

    $forumperms = fetch_permissions($threadinfo['forumid']);
    if (
        !($forumperms & $vbulletin->bf_ugp_forumpermissions['canview'])
        OR
        !($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads'])
        OR
        (($threadinfo['postuserid'] != $vbulletin->userinfo['userid']) AND !($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewothers']))
    )
    {
        return_fault();
    }

    if (!$postinfo['postid'])
    {
        return_fault(fetch_error('invalidid', $vbphrase['post']));
    }
    if ((!$postinfo['visible'] OR $postinfo ['isdeleted']) AND !can_moderate($threadinfo['forumid']))
    {
        return_fault(fetch_error('invalidid', $vbphrase['post']));
    }

    if ((!$threadinfo['visible'] OR $threadinfo['isdeleted']) AND !can_moderate($threadinfo['forumid']))
    {
        return_fault(fetch_error('invalidid', $vbphrase['thread']));
    }

    // check if there is a forum password and if so, ensure the user has it set
    if (!verify_forum_password($foruminfo['forumid'], $foruminfo['password'], false))
        return_fault('Your administrator has required a password to access this forum.');

    $vbulletin->input->clean_array_gpc('p', array(
            'reason' => TYPE_STR,
    ));
    $vbulletin->GPC['reason'] = $report_message;
    if ($vbulletin->GPC['reason'] == '')
    {
        return new xmlrpcresp(new xmlrpcval(false, 'boolean'));
    }

    if ($perform_floodcheck)
    {
        $result = $reportobj->perform_floodcheck_commit();
    }

    $reportobj->do_report($vbulletin->GPC['reason'], $postinfo);

    if (defined('NOSHUTDOWNFUNC'))
    {
        exec_shut_down();
    }
    
    return new xmlrpcresp(new xmlrpcval(array(
        'result'        => new xmlrpcval(true, 'boolean'),
    ), 'struct'));
}
