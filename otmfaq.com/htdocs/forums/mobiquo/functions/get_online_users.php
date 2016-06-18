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

$phrasegroups = array('wol');
$specialtemplates = array();
$globaltemplates = array();
$actiontemplates = array();

define('DISABLE_HOOKS', true);
define('THIS_SCRIPT', 'mobiquo');
define('CSRF_PROTECTION', false);
define('CSRF_SKIP_LIST', '');

require_once('./global.php');
require_once(DIR . '/includes/functions_bigthree.php');
require_once(DIR . '/includes/functions_forumlist.php');
require_once(DIR . '/includes/class_postbit.php');
require_once(DIR . '/includes/functions_user.php');
require_once(DIR . '/includes/functions_online.php');

function get_online_users_func($params)
{
    global $vbulletin, $db, $permissions, $show;
    
    if (!$vbulletin->options['WOLenable'])
    {
        return_fault(fetch_error('whosonlinedisabled'));
    }

    if (!($permissions['wolpermissions'] & $vbulletin->bf_ugp_wolpermissions['canwhosonline']))
    {
        return_fault();
    }
    
    $params = php_xmlrpc_decode($params);
    
    $filter_str = '';
    if (isset($params[0]))
    {
        $filter_id = intval($params[0]);
        if (isset($params[1]) && $params[1] == 'topic')
            $filter_str = " AND session.inthread='$filter_id' ";
        else
            $filter_str = " AND session.inforum='$filter_id' ";
    }
    
    $login_users = array();
    $activeusers = '';
    if (($vbulletin->options['displayloggedin'] == 1 OR $vbulletin->options['displayloggedin'] == 2 OR ($vbulletin->options['displayloggedin'] > 2 AND $vbulletin->userinfo['userid'])) AND !$show['search_engine'])
    {
        $datecut = TIMENOW - $vbulletin->options['cookietimeout'];
        $numbervisible = 0;
        $numberregistered = 0;
        $numberguest = 0;

        $hook_query_fields = $hook_query_joins = $hook_query_where = '';
            
        require_once(DIR . '/includes/class_userprofile.php');
        require_once(DIR . '/includes/class_profileblock.php');
        
        $userperms = cache_permissions($userinfo, false);
        $forumusers = $db->query_read_slave("
            SELECT
                user.username, (user.options & " . $vbulletin->bf_misc_useroptions['invisible'] . ") AS invisible, user.usergroupid,
                session.userid, session.inforum, session.lastactivity, session.badlocation,session.location,
                IF(displaygroupid=0, user.usergroupid, displaygroupid) AS displaygroupid, infractiongroupid
                $hook_query_fields
            FROM " . TABLE_PREFIX . "session AS session
            LEFT JOIN " . TABLE_PREFIX . "user AS user ON(user.userid = session.userid)
            $hook_query_joins
            WHERE session.lastactivity > $datecut $filter_str
            $hook_query_where
            " . iif($vbulletin->options['displayloggedin'] == 1 OR $vbulletin->options['displayloggedin'] == 3, "ORDER BY username ASC") . "
        ");

        if ($vbulletin->userinfo['userid'] && !isset($params[0]))
        {
            // fakes the user being online for an initial page view of index.php
            $vbulletin->userinfo['joingroupid'] = iif($vbulletin->userinfo['displaygroupid'], $vbulletin->userinfo['displaygroupid'], $vbulletin->userinfo['usergroupid']);
            $userinfos = array(
                $vbulletin->userinfo['userid'] => array(
                    'userid'            =>& $vbulletin->userinfo['userid'],
                    'username'          =>& $vbulletin->userinfo['username'],
                    'invisible'         =>& $vbulletin->userinfo['invisible'],
                    'location'          => 'mobiquo/mobiquo.php',
                    'inforum'           => 0,
                    'lastactivity'      => TIMENOW,
                    'usergroupid'       =>& $vbulletin->userinfo['usergroupid'],
                    'displaygroupid'    =>& $vbulletin->userinfo['displaygroupid'],
                    'infractiongroupid' =>& $vbulletin->userinfo['infractiongroupid'],
                )
            );
        }
        else
        {
            $userinfos = array();
        }
        $inforum = array();
        $mobiquo_i = 0;
        global $limitlower, $limitupper;
        $limitlower = 0;
        $limitupper = 50;
        while ($loggedin = $db->fetch_array($forumusers))
        {
            $userid = $loggedin['userid'];
            if (!$userid)
            {
                $numberguest++;
                if (!$loggedin['badlocation'])
                {
                    $inforum["$loggedin[inforum]"]++;
                }
            }
            else if (empty($userinfos["$userid"]) OR ($userinfos["$userid"]['lastactivity'] < $loggedin['lastactivity']))
            {
                $mobiquo_i ++;
                if($mobiquo_i < $limitupper){
                    $loggedin = process_online_location($loggedin, 1);
                }
                $userinfos[$userid] = $loggedin;
            }
        }
        convert_ids_to_titles();

        if (is_array($userinfos))
        {
            foreach ($userinfos AS $key => $val)
            {
                if (!$val['invisible'])
                {
                    $userinfos[$key] = construct_online_bit($val, 0);
                    $numbervisible++;
                }
                else
                {
                    $numberinvisible++;
                }
            }
        }
        if (!$vbulletin->userinfo['userid'] AND $numberguest == 0)
        {
            $numberguest++;
        }
        
        foreach ($userinfos AS $userid => $loggedin)
        {
            $numberregistered++;
            if ($userid != $vbulletin->userinfo['userid'] AND !$loggedin['badlocation'])
            {
                $inforum["$loggedin[inforum]"]++;
            }
            fetch_musername($loggedin);
            
            if (fetch_online_status($loggedin))
            {
                $icon_url = '';
                if((count($userinfos)) < 100 )
                {
                    $fetch_userinfo_options = (
                        FETCH_USERINFO_AVATAR
                    );
                    $userinfo = fetch_userinfo($loggedin['userid'], $fetch_userinfo_options);
                    fetch_avatar_from_userinfo($userinfo, true, false);
                    
                    if($userinfo[avatarurl]){
                        $icon_url = get_icon_real_url($userinfo[avatarurl]);
                    }
                }
                if($loggedin['where']){
                    if(isset($loggedin['values']['threadid'])) $loggedin['where'] = '[TOPIC]'.$loggedin['where'].'[/TOPIC]';
                    $display_text = strip_tags($loggedin['action'].": ".$loggedin['where']);
                } else {
                    $display_text = strip_tags($loggedin['action']);
                }
                if(strpos($loggedin['where'], 'mobiquo/mobiquo.php')){
                    $display_text = 'via Tapatalk Forum App';
                }
                
                $online_user = array(
                    'user_id'      => new xmlrpcval($userid, 'string'),
                    'username'     => new xmlrpcval(mobiquo_encode($loggedin['musername']), 'base64'),
                    'user_name'    => new xmlrpcval(mobiquo_encode($loggedin['musername']), 'base64'),
                    'icon_url'     => new xmlrpcval($icon_url, 'string'),
                    'display_text' => new xmlrpcval(mobiquo_encode($display_text), 'base64'),
                );
                
                if (isset($loggedin['values']['threadid']))
                    $online_user['topic_id'] = new xmlrpcval($loggedin['values']['threadid'], 'string');
                
                $login_users[] = new xmlrpcval($online_user, 'struct');
                $numbervisible++;
                $show['comma_leader'] = ($activeusers != '');
            }
        }

        // memory saving
        unset($userinfos, $loggedin);

        $db->free_result($forumusers);

        $totalonline = $numberregistered + $numberguest;
        $numberinvisible = $numberregistered - $numbervisible;

    } else {
        return_fault();
    }
    
    if (defined('NOSHUTDOWNFUNC'))
    {
        exec_shut_down();
    }
    
    $return_data = array(
        'member_count' => new xmlrpcval($numberregistered, 'int'),
        'guest_count' => new xmlrpcval($numberguest, 'int'),
        'list'         => new xmlrpcval($login_users, 'array'),
    );
    
    return new xmlrpcresp(new xmlrpcval($return_data, 'struct'));
}
