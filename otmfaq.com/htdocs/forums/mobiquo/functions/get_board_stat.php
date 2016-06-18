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

define('THIS_SCRIPT', 'mobiquo');
define('CSRF_PROTECTION', false);
define('CSRF_SKIP_LIST', '');

$phrasegroups = array();
$actiontemplates = array();
$specialtemplates = array(
    'userstats',
    'birthdaycache',
    'maxloggedin',
    'iconcache',
    'eventcache',
    'mailqueue'
);
$globaltemplates = array(
    'ad_forumhome_afterforums',
    'FORUMHOME',
    'forumhome_event',
    'forumhome_forumbit_level1_nopost',
    'forumhome_forumbit_level1_post',
    'forumhome_forumbit_level2_nopost',
    'forumhome_forumbit_level2_post',
    'forumhome_lastpostby',
    'forumhome_loggedinuser',
    'forumhome_moderator',
    'forumhome_subforumbit_nopost',
    'forumhome_subforumbit_post',
    'forumhome_subforumseparator_nopost',
    'forumhome_subforumseparator_post',
    'forumhome_markread_script',
    'forumhome_birthdaybit'
);

require_once('./global.php');
require_once(DIR . '/includes/functions_bigthree.php');
require_once(DIR . '/includes/functions_forumlist.php');


function get_board_stat_func($params)
{
    global $vbulletin, $db;

    $activeusers = '';
    if (($vbulletin->options['displayloggedin'] == 1 OR $vbulletin->options['displayloggedin'] == 2 OR ($vbulletin->options['displayloggedin'] > 2 AND $vbulletin->userinfo['userid'])) AND !$show['search_engine'])
    {
        $datecut = TIMENOW - $vbulletin->options['cookietimeout'];
        $numbervisible = 0;
        $numberregistered = 0;
        $numberguest = 0;

        $hook_query_fields = $hook_query_joins = $hook_query_where = '';
        ($hook = vBulletinHook::fetch_hook('forumhome_loggedinuser_query')) ? eval($hook) : false;

        $forumusers = $db->query_read_slave("
            SELECT
                user.username, (user.options & " . $vbulletin->bf_misc_useroptions['invisible'] . ") AS invisible, user.usergroupid,
                session.userid, session.inforum, session.lastactivity,
                IF(displaygroupid=0, user.usergroupid, displaygroupid) AS displaygroupid, infractiongroupid
                $hook_query_fields
            FROM " . TABLE_PREFIX . "session AS session
            LEFT JOIN " . TABLE_PREFIX . "user AS user ON(user.userid = session.userid)
            $hook_query_joins
            WHERE session.lastactivity > $datecut
            $hook_query_where
            " . iif($vbulletin->options['displayloggedin'] == 1 OR $vbulletin->options['displayloggedin'] == 3, "ORDER BY username ASC") . "
        ");

        if ($vbulletin->userinfo['userid'])
        {
            // fakes the user being online for an initial page view of index.php
            $vbulletin->userinfo['joingroupid'] = iif($vbulletin->userinfo['displaygroupid'], $vbulletin->userinfo['displaygroupid'], $vbulletin->userinfo['usergroupid']);
            $userinfos = array
            (
                $vbulletin->userinfo['userid'] => array
                (
                    'userid'            =>& $vbulletin->userinfo['userid'],
                    'username'          =>& $vbulletin->userinfo['username'],
                    'invisible'         =>& $vbulletin->userinfo['invisible'],
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

        while ($loggedin = $db->fetch_array($forumusers))
        {
            $userid = $loggedin['userid'];
            if (!$userid)
            {    // Guest
                $numberguest++;
                $inforum["$loggedin[inforum]"]++;
            }
            else if (empty($userinfos["$userid"]) OR ($userinfos["$userid"]['lastactivity'] < $loggedin['lastactivity']))
            {
                $userinfos["$userid"] = $loggedin;
            }
        }

        if (!$vbulletin->userinfo['userid'] AND $numberguest == 0)
        {
            $numberguest++;
        }

        foreach ($userinfos AS $userid => $loggedin)
        {
            $numberregistered++;
            if ($userid != $vbulletin->userinfo['userid'])
            {
                $inforum["$loggedin[inforum]"]++;
            }
            fetch_musername($loggedin);

            ($hook = vBulletinHook::fetch_hook('forumhome_loggedinuser')) ? eval($hook) : false;

            if (fetch_online_status($loggedin))
            {
                $numbervisible++;
                $show['comma_leader'] = ($activeusers != '');
                //eval('$activeusers .= "' . fetch_template('forumhome_loggedinuser') . '";');
            }
        }

        // memory saving
        unset($userinfos, $loggedin);

        $db->free_result($forumusers);

        $totalonline = $numberregistered + $numberguest;
        $numberinvisible = $numberregistered - $numbervisible;

        // ### MAX LOGGEDIN USERS ################################
        if (intval($vbulletin->maxloggedin['maxonline']) <= $totalonline)
        {
            $vbulletin->maxloggedin['maxonline'] = $totalonline;
            $vbulletin->maxloggedin['maxonlinedate'] = TIMENOW;
            build_datastore('maxloggedin', serialize($vbulletin->maxloggedin), 1);
        }

        $recordusers = vb_number_format($vbulletin->maxloggedin['maxonline']);
        $recorddate = vbdate($vbulletin->options['dateformat'], $vbulletin->maxloggedin['maxonlinedate'], true);
        $recordtime = vbdate($vbulletin->options['timeformat'], $vbulletin->maxloggedin['maxonlinedate']);

        $show['loggedinusers'] = true;
    }
    else
    {
        $show['loggedinusers'] = false;
    }
    cache_ordered_forums(1, 1);
    if ($vbulletin->options['showmoderatorcolumn'])
    {
        cache_moderators();
    }
    else if ($vbulletin->userinfo['userid'])
    {
        cache_moderators($vbulletin->userinfo['userid']);
    }
    // define max depth for forums display based on $vbulletin->options[forumhomedepth]
    define('MAXFORUMDEPTH', $vbulletin->options['forumhomedepth']);

    $forumbits = construct_forum_bit($forumid);

    // ### BOARD STATISTICS #################################################

    // get total threads & posts from the forumcache
    $totalthreads = 0;
    $totalposts = 0;
    if (is_array($vbulletin->forumcache))
    {
        foreach ($vbulletin->forumcache AS $forum)
        {
            $totalthreads += $forum['threadcount'];
            $totalposts += $forum['replycount'];
        }
    }

    // get total members and newest member from template
    $numbermembers = $vbulletin->userstats['numbermembers'];
    $newusername = $vbulletin->userstats['newusername'];
    $newuserid = $vbulletin->userstats['newuserid'];
    $activemembers = $vbulletin->userstats['activemembers'];
    $show['activemembers'] = ($vbulletin->options['activememberdays'] > 0 AND ($vbulletin->options['activememberoptions'] & 2)) ? true : false;

    $return_data = array(
        'total_online'  => new xmlrpcval($totalonline, 'int'),
        'guest_online'  => new xmlrpcval($numberguest, 'int'),
        'total_members' => new xmlrpcval($numbermembers, 'int'),
        'total_threads' => new xmlrpcval($totalthreads, 'int'),
        'total_posts'   => new xmlrpcval($totalposts, 'int'),
    );
    
    if($show['activemembers']){
        $return_data['active_members'] = new xmlrpcval($activemembers, 'int');
    }

    if (defined('NOSHUTDOWNFUNC'))
    {
        exec_shut_down();
    }
    
    return new xmlrpcresp(new xmlrpcval($return_data, 'struct'));
}
