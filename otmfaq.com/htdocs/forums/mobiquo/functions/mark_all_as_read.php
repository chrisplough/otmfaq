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


$specialtemplates = array(
);

$globaltemplates = array();

$actiontemplates = array(
);





require_once('./global.php');

function mark_all_as_read_func( $xmlrpc_params)
{
    global $vbulletin;
    $params = php_xmlrpc_decode($xmlrpc_params);
    if ($vbulletin->userinfo['userid'] == 0)
    {
        return_fault();
    }
    
    require_once(DIR . '/includes/functions_misc.php');
    if(isset($params[0]) && $params[0])
    {
        $vbulletin->GPC['forumid'] = $params[0];
        $foruminfo = mobiquo_verify_id('forum', $vbulletin->GPC['forumid'], 0, 1);
        if(!is_array($foruminfo)){
            return $foruminfo;
        }
        mark_forums_read($foruminfo['forumid']);
    } else {

        $mark_read_result = mark_forums_read();
    }

    if (defined('NOSHUTDOWNFUNC'))
    {
        exec_shut_down();
    }
    
    return new xmlrpcresp(new xmlrpcval(array(
        'result' => new xmlrpcval(true, 'boolean'),
    ), 'struct'));
}
