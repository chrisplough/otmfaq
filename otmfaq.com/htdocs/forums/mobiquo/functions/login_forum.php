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

define('THIS_SCRIPT', 'forumdisplay');
define('CSRF_PROTECTION', false);

$phrasegroups = array();
$specialtemplates = array();
$globaltemplates = array();
$actiontemplates = array();


require_once('./global.php');
require_once(DIR . '/includes/functions_forumlist.php');
require_once(DIR . '/includes/functions_bigthree.php');
require_once(DIR . '/includes/functions_forumdisplay.php');
require_once(DIR . '/includes/functions_prefix.php');
require_once(DIR . '/includes/functions_user.php');


function login_forum_func( $xmlrpc_params)
{
    global $vbulletin, $vbphrase;

    $params = php_xmlrpc_decode($xmlrpc_params);
    
    if(!$params[0])
    {
        return_fault(fetch_error('invalidid', $vbphrase['forum']));
    }
    $vbulletin->GPC['forumid'] = $params[0];
    $threadinfo = array();
    $foruminfo = array();

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
    }
    else if ($vbulletin->GPC['forumid'])
    {
        $foruminfo = mobiquo_verify_id('forum', $vbulletin->GPC['forumid'], 0, 1);
        if(!is_array($foruminfo)){
            return_fault(fetch_error('invalidid', $vbphrase['forum']));
        }
        $forumid =& $foruminfo['forumid'];

        if (($foruminfo['styleoverride'] == 1 OR $vbulletin->userinfo['styleid'] == 0) AND !defined('BYPASS_STYLE_OVERRIDE'))
        {
            $codestyleid =& $foruminfo['styleid'];
        }
    }

    if(isset($params[1]) && $params[1] && $params[1] >= 0) {
        $vbulletin->GPC['newforumpwd'] = $params[1] ;
    }


    if ($foruminfo['password'] == $vbulletin->GPC['newforumpwd'])
    {
        // set a temp cookie for guests
        if (!$vbulletin->userinfo['userid'])
        {
            set_bbarray_cookie('forumpwd', $foruminfo['forumid'], md5($vbulletin->userinfo['userid'] . $vbulletin->GPC['newforumpwd']));
        }
        else
        {
            set_bbarray_cookie('forumpwd', $foruminfo['forumid'], md5($vbulletin->userinfo['userid'] . $vbulletin->GPC['newforumpwd']), 1);
        }

        $status = true;
    } else {
        $status = false;
    }
    
    return new xmlrpcresp(new xmlrpcval(array(
        'result' => new xmlrpcval($status, 'boolean'),
        'result_text' => new xmlrpcval('', 'base64')
    ), 'struct'));
}
