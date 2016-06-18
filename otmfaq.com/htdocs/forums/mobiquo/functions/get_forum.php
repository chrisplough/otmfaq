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
require_once(CWD1."/include/functions_get_forum.php");

$phrasegroups = array();
$specialtemplates = array();

$globaltemplates = array();

$actiontemplates = array();

define('THIS_SCRIPT', 'mobiquo');
define('CSRF_PROTECTION', false);
define('CSRF_SKIP_LIST', '');

require_once('./global.php');
require_once(DIR . '/includes/functions_bigthree.php');
require_once(DIR . '/includes/functions_forumlist.php');


function get_forum_all_func($params)
{
    return get_forum_func($params,1);
}

function get_forum_func($params, $show_hide = 0)
{
    global $vbulletin, $db, $xmlrpcerruser;
    
    $params = php_xmlrpc_decode($params);
    
    $forum_id = isset($params[1]) && $params[1] !== '' ? $params[1] : -1;
    define('RETURN_FIRST_LEVEL', $forum_id >= 0);
    define('RETURN_DESCRIPTION', isset($params[0]) ? $params[0] : false);
    if ($forum_id == 0) $forum_id = -1;
    
    if ($forum_id > 0)
    {
        // check forum permissions
        $foruminfo = fetch_foruminfo($forum_id);
        $_permsgetter_ = 'forumdisplay';
        $forumperms = fetch_permissions($foruminfo['forumid']);

        if (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canview']))
        {
            print_no_permission();
        }
        if (!verify_forum_password($foruminfo['forumid'], $foruminfo['password'], false))
            return_fault('Your administrator has required a password to access this forum.');
    }
    
    cache_ordered_forums(1, 1);
    
    $subscribe_forums = array();
    if ( $vbulletin->userinfo['userid'])
    {
        $query = "
            SELECT subscribeforumid, forumid
            FROM " . TABLE_PREFIX . "subscribeforum
            WHERE userid = 
        " . $vbulletin->userinfo['userid'];
        $getthings = $db->query_read_slave($query);
        if ($db->num_rows($getthings))
        {
            while ($getthing = $db->fetch_array($getthings))
            {
                $subscribe_forums["$getthing[forumid]"] = $getthing;
            }
        }
    }

    if ($vbulletin->options['showmoderatorcolumn'])
    {
        cache_moderators();
    }
    else if ($vbulletin->userinfo['userid'])
    {
        cache_moderators($vbulletin->userinfo['userid']);
    }

    $forumbits = construct_forum_bit_mobiquo($forum_id, 0, 0, $subscribe_forums, $show_hide);
    if (defined('NOSHUTDOWNFUNC'))
    {
        exec_shut_down();
    }
    
    return new xmlrpcresp(new xmlrpcval($forumbits, 'array'));
}
