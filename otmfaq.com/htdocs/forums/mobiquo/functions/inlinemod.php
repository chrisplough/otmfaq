<?php

defined('IN_MOBIQUO') or exit;

define('THIS_SCRIPT', 'inlinemod');
define('CSRF_PROTECTION', false);

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array('banning', 'threadmanage', 'posting', 'inlinemod');

// get special data templates from the datastore
$specialtemplates = array(
    'smiliecache',
    'bbcodecache',
);

// pre-cache templates used by all actions
$globaltemplates = array(
    'THREADADMIN',
    'threadadmin_authenticate'
);

// pre-cache templates used by specific actions
$actiontemplates = array(
    'mergethread'  => array('threadadmin_mergethreads'),
    'deletethread' => array('threadadmin_deletethreads'),
    'movethread'   => array('threadadmin_movethreads'),
    'moveposts'    => array('threadadmin_moveposts'),
    'copyposts'    => array('threadadmin_copyposts'),
    'mergeposts'   => array('threadadmin_mergeposts'),
    'domergeposts' => array('threadadmin_mergeposts'),
    'deleteposts'  => array('threadadmin_deleteposts'),
    // spam management
    'spampost'     => array('threadadmin_easyspam', 'threadadmin_easyspam_userbit', 'threadadmin_easyspam_ipbit', 'threadadmin_easyspam_headinclude'),
    'spamthread'   => array('threadadmin_easyspam', 'threadadmin_easyspam_userbit', 'threadadmin_easyspam_ipbit', 'threadadmin_easyspam_headinclude'),
    'spamconfirm'  => array('threadadmin_easyspam_confirm', 'threadadmin_easyspam_ban', 'threadadmin_easyspam_user_option', 'threadadmin_easyspam_headinclude'),
    'dodeletespam' => array('threadadmin_easyspam_headinclude', 'threadadmin_easyspam_userbit', 'threadadmin_easyspam_skipped_prune'),
);
$actiontemplates['mergethreadcompat'] =& $actiontemplates['mergethread'];

// ####################### PRE-BACK-END ACTIONS ##########################
require_once('./global.php');
require_once(DIR . '/includes/functions_editor.php');
require_once(DIR . '/includes/functions_threadmanage.php');
require_once(DIR . '/includes/functions_databuild.php');
require_once(DIR . '/includes/functions_log_error.php');
require_once(DIR . '/includes/modfunctions.php');
require_once(DIR . '/vb/search/indexcontroller/queue.php');
require_once(DIR . '/includes/class_bootstrap_framework.php');
vB_Bootstrap_Framework::init();

// tapatalk add
header('Mobiquo_is_login:'.(isset($vbulletin) && $vbulletin->userinfo['userid'] != 0 ? 'true' : 'false'));

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

//verify_forum_url();

if (($current_memory_limit = ini_size_to_bytes(@ini_get('memory_limit'))) < 128 * 1024 * 1024 AND $current_memory_limit > 0)
{
    @ini_set('memory_limit', 128 * 1024 * 1024);
}
@set_time_limit(0);

// Wouldn't be fun if someone tried to manipulate every post in the database ;)
// Should be made into options I suppose - too many and you exceed what a cookie can hold anyway
$postlimit = 400;
$threadlimit = 200;

if (!can_moderate())
{
    return_fault();
}

// This is a list of ids that were checked on the page we submitted from
$vbulletin->input->clean_array_gpc('p', array(
    'tlist' => TYPE_ARRAY_KEYS_INT,
    'plist' => TYPE_ARRAY_KEYS_INT,
));

// If we have javascript, all ids should be in here
$vbulletin->input->clean_array_gpc('c', array(
    'vbulletin_inlinethread' => TYPE_STR,
    'vbulletin_inlinepost'   => TYPE_STR,
));



$tlist = array();
if (!empty($vbulletin->GPC['vbulletin_inlinethread']))
{
    $tlist = explode('-', $vbulletin->GPC['vbulletin_inlinethread']);
    $tlist = $vbulletin->input->clean($tlist, TYPE_ARRAY_UINT);
}
$tlist = array_unique(array_merge($tlist, $vbulletin->GPC['tlist']));

$plist = array();
if (!empty($vbulletin->GPC['vbulletin_inlinepost']))
{
    $plist = explode('-', $vbulletin->GPC['vbulletin_inlinepost']);
    $plist = $vbulletin->input->clean($plist, TYPE_ARRAY_UINT);
}
$plist = array_unique(array_merge($plist, $vbulletin->GPC['plist']));

switch ($_POST['do'])
{
    case 'dodeletethreads':
    case 'domovethreads':
    case 'domergethreads':
    case 'dodeleteposts':
    case 'domergeposts':
    case 'domoveposts':
    case 'docopyposts':
    case 'spamconfirm':
    case 'dodeletespam':
    {
        $inline_mod_authenticate = true;
        break;
    }
    default:
    {
        $inline_mod_authenticate = false;
        ($hook = vBulletinHook::fetch_hook('inlinemod_authenticate_switch')) ? eval($hook) : false;
    }
}

if ($inline_mod_authenticate AND !inlinemod_authenticated())
{
    return_mod_fault('Please login again to verify the legitimacy of this request.', false);
}

switch ($_POST['do'])
{
    case 'mergethreadcompat':
        $vbulletin->input->clean_gpc('p', 'mergethreadurl', TYPE_STR);

        $mergethreadid = extract_threadid_from_url($vbulletin->GPC['mergethreadurl']);
        if (!$mergethreadid)
        {
            // Invalid URL
            return_mod_fault(fetch_error('mergebadurl'));
        }

        $threadids = "$threadid,$mergethreadid";
        break;
    case 'open':
    case 'close':
    case 'stick':
    case 'unstick':
    case 'deletethread':
    case 'undeletethread':
    case 'approvethread':
    case 'unapprovethread':
    case 'movethread':
    case 'mergethread':
    case 'viewthread':
    case 'spamthread':
    case 'renamethread':
    {
        if (empty($tlist))
        {
            return_mod_fault(fetch_error('you_did_not_select_any_valid_threads'));
        }

        if (count($tlist) > $threadlimit)
        {
            return_mod_fault(fetch_error('you_are_limited_to_working_with_x_threads', $threadlimit));
        }

        $threadids = implode(',', $tlist);

        break;
    }
    case 'dodeletethreads':
    case 'domovethreads':
    case 'domergethreads':
    {
        $vbulletin->input->clean_array_gpc('p', array(
            'threadids' => TYPE_STR,
        ));

        $threadids = explode(',', $vbulletin->GPC['threadids']);
        foreach ($threadids AS $index => $threadid)
        {
            if (intval($threadid) == 0)
            {
                unset($threadids["$index"]);
            }
            else
            {
                $threadids["$index"] = intval($threadid);
            }

        }

        if (empty($threadids))
        {
            return_mod_fault(fetch_error('you_did_not_select_any_valid_threads'));
        }

        if (count($threadids) > $threadlimit)
        {
            return_mod_fault(fetch_error('you_are_limited_to_working_with_x_threads', $threadlimit));
        }

        break;
    }
    case 'deleteposts':
    case 'undeleteposts':
    case 'approveposts':
    case 'unapproveposts':
    case 'mergeposts':
    case 'moveposts':
    case 'copyposts':
    case 'approveattachments':
    case 'unapproveattachments':
    case 'viewpost':
    case 'spampost':
    {
        if (empty($plist))
        {
            return_mod_fault(fetch_error('no_applicable_posts_selected'));
        }

        if (count($plist) > $postlimit)
        {
            return_mod_fault(fetch_error('you_are_limited_to_working_with_x_posts', $postlimit));
        }

        $postids = implode(',', $plist);

        break;
    }
    case 'dodeleteposts':
    case 'domergeposts':
    case 'domoveposts':
    case 'docopyposts':
    {
        $vbulletin->input->clean_array_gpc('p', array(
            'postids' => TYPE_STR,
        ));

        $postids = explode(',', $vbulletin->GPC['postids']);
        foreach ($postids AS $index => $postid)
        {
            if (intval($postid) == 0)
            {
                unset($postids["$index"]);
            }
            else
            {
                $postids["$index"] = intval($postid);
            }
        }

        if (empty($postids))
        {
            return_mod_fault(fetch_error('no_applicable_posts_selected'));
        }

        if (count($postids) > $postlimit)
        {
            return_mod_fault(fetch_error('you_are_limited_to_working_with_x_posts', $postlimit));
        }
        break;
    }
    case 'spamconfirm':
    { // thse can be either posts OR threads
        $vbulletin->input->clean_array_gpc('p', array(
            'type' => TYPE_STR,
        ));
        if ($vbulletin->GPC['type'] == 'post')
        {
            $vbulletin->input->clean_array_gpc('p', array(
                'postids' => TYPE_STR,
            ));

            $postids = explode(',', $vbulletin->GPC['postids']);
            foreach ($postids AS $index => $postid)
            {
                if (intval($postid) == 0)
                {
                    unset($postids["$index"]);
                }
                else
                {
                    $postids["$index"] = intval($postid);
                }
            }

            if (empty($postids))
            {
                return_mod_fault(fetch_error('no_applicable_posts_selected'));
            }

            if (count($postids) > $postlimit)
            {
                return_mod_fault(fetch_error('you_are_limited_to_working_with_x_posts', $postlimit));
            }
        }
        else
        {
            $vbulletin->input->clean_array_gpc('p', array(
                'threadids' => TYPE_STR,
            ));

            $threadids = explode(',', $vbulletin->GPC['threadids']);
            foreach ($threadids AS $index => $threadid)
            {
                if (intval($threadid) == 0)
                {
                    unset($threadids["$index"]);
                }
                else
                {
                    $threadids["$index"] = intval($threadid);
                }

            }

            if (empty($threadids))
            {
                return_mod_fault(fetch_error('you_did_not_select_any_valid_threads'));
            }

            if (count($threadids) > $threadlimit)
            {
                return_mod_fault(fetch_error('you_are_limited_to_working_with_x_threads', $threadlimit));
            }
        }
        break;
    }
    case 'dodeletespam':
    case 'clearthread':
    case 'clearpost':
    {
        break;
    }
    default: // throw and error about invalid $_REQUEST['do']
    {
        $handled_do = false;
        ($hook = vBulletinHook::fetch_hook('inlinemod_action_switch')) ? eval($hook) : false;
        if (!$handled_do)
        {
            return_mod_fault(fetch_error('invalid_action'));
        }
    }
}

// set forceredirect for IIS
$forceredirect = (strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== false);

$threadarray = array();
$postarray = array();
$postinfos = array();
$forumlist = array();
$threadlist = array();


// ############################### start do open / close thread ###############################
if ($_POST['do'] == 'open' OR $_POST['do'] == 'close')
{
    // Validate threads
    $threads = $db->query_read_slave("
        SELECT threadid, visible, forumid, postuserid, title, prefixid
        FROM " . TABLE_PREFIX . "thread
        WHERE threadid IN ($threadids)
            AND open = " . ($_POST['do'] == 'open' ? 0 : 1) . "
    ");
    while ($thread = $db->fetch_array($threads))
    {
        $forumperms = fetch_permissions($thread['forumid']);
        if (
            !($forumperms & $vbulletin->bf_ugp_forumpermissions['canview'])
                OR
            !($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads'])
                OR
            (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewothers']) AND $thread['postuserid'] != $vbulletin->userinfo['userid'])
            )
        {
            return_mod_fault();
        }

        $thread['prefix_plain_html'] = ($thread['prefixid'] ? htmlspecialchars_uni($vbphrase["prefix_$thread[prefixid]_title_plain"]) . ' ' : '');

        if (!can_moderate($thread['forumid'], 'canopenclose'))
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_openclose_threads', $thread['prefix_plain_html'] . $thread['title'], $vbulletin->forumcache["$thread[forumid]"]['title']));
        }
        else if (!$thread['visible'] AND !can_moderate($thread['forumid'], 'canmoderateposts'))
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_manage_moderated_threads_and_posts'));
        }
        else if ($thread['visible'] == 2 AND !can_moderate($thread['forumid'], 'candeleteposts'))
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_manage_deleted_threads_and_posts', $thread['prefix_plain_html'] . $thread['title'], $vbulletin->forumcache["$thread[forumid]"]['title']));
        }

        $threadarray["$thread[threadid]"] = $thread;
    }

    if (!empty($threadarray))
    {
        $db->query_write("
            UPDATE " . TABLE_PREFIX . "thread
            SET open = " . ($_POST['do'] == 'open' ? 1 : 0) . "
            WHERE threadid IN(" . implode(',', array_keys($threadarray)) . ")

        ");

        foreach (array_keys($threadarray) AS $threadid)
        {
            $modlog[] = array(
                'userid'   =>& $vbulletin->userinfo['userid'],
                'forumid'  =>& $threadarray["$threadid"]['forumid'],
                'threadid' => $threadid,
            );
        }

        log_moderator_action($modlog, ($_POST['do'] == 'open') ? 'opened_thread' : 'closed_thread');
    }

    // empty cookie
    setcookie('vbulletin_inlinethread', '', TIMENOW - 3600, '/');
}

// ############################### start do stick / unstick thread ###############################
if ($_POST['do'] == 'stick' OR $_POST['do'] == 'unstick')
{
    $redirect = array();

    // Validate threads
    $threads = $db->query_read_slave("
        SELECT threadid, open, visible, forumid, postuserid, title, prefixid
        FROM " . TABLE_PREFIX . "thread
        WHERE threadid IN ($threadids)
            AND sticky = " . ($_POST['do'] == 'stick' ? 0 : 1) . "
    ");
    while ($thread = $db->fetch_array($threads))
    {
        $forumperms = fetch_permissions($thread['forumid']);
        if (
            !($forumperms & $vbulletin->bf_ugp_forumpermissions['canview'])
                OR
            !($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads'])
                OR
            (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewothers']) AND $thread['postuserid'] != $vbulletin->userinfo['userid'])
            )
        {
            return_mod_fault();
        }

        $thread['prefix_plain_html'] = ($thread['prefixid'] ? htmlspecialchars_uni($vbphrase["prefix_$thread[prefixid]_title_plain"]) . ' ' : '');

        if (!can_moderate($thread['forumid'], 'canmanagethreads'))
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_stickunstick_threads', $thread['prefix_plain_html'] . $thread['title'], $vbulletin->forumcache["$thread[forumid]"]['title']));
        }
        else if (!$thread['visible'] AND !can_moderate($thread['forumid'], 'canmoderateposts'))
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_manage_moderated_threads_and_posts'));
        }
        else if ($thread['visible'] == 2 AND !can_moderate($thread['forumid'], 'candeleteposts'))
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_manage_deleted_threads_and_posts', $thread['prefix_plain_html'] . $thread['title'], $vbulletin->forumcache["$thread[forumid]"]['title']));
        }

        $threadarray["$thread[threadid]"] = $thread;
        if ($thread['open'] == 10)
        {
            $redirect[] = $thread['threadid'];
        }
    }

    if (!empty($threadarray))
    {
        $db->query_write("
            UPDATE " . TABLE_PREFIX . "thread
            SET sticky = " . ($_POST['do'] == 'stick' ? 1 : 0) . "
            WHERE threadid IN(" . implode(',', array_keys($threadarray)) . ")
        ");

        foreach (array_keys($threadarray) AS $threadid)
        {
            if (!in_array($threadid, $redirect))
            {    // Don't add log entry for (un)sticking a redirect
                $modlog[] = array(
                    'userid'   =>& $vbulletin->userinfo['userid'],
                    'forumid'  =>& $threadarray["$threadid"]['forumid'],
                    'threadid' => $threadid,
                );
            }
        }

        log_moderator_action($modlog, ($_POST['do'] == 'stick') ? 'stuck_thread' : 'unstuck_thread');
    }

    // empty cookie
    setcookie('vbulletin_inlinethread', '', TIMENOW - 3600, '/');
}


/* permission checks for the punitive action on spam threads / posts */
if ($_POST['do'] == 'spamconfirm' OR $_POST['do'] == 'dodeletespam')
{
    $vbulletin->input->clean_array_gpc('p', array(
        'useraction' => TYPE_STR,
        'username' => TYPE_STR,
    ));
    
    $username = mobiquo_encode($vbulletin->GPC['username'], 'to_local');
    $vbulletin->GPC['userid'] = array(get_userid_by_name($username));

    $user_cache = array();
    foreach ($vbulletin->GPC['userid'] AS $userid)
    {
        $user_cache["$userid"] = fetch_userinfo($userid);
        cache_permissions($user_cache["$userid"]);
        $user_cache["$userid"]['joindate_string'] = vbdate($vbulletin->options['dateformat'], $user_cache["$userid"]['joindate']);
    }

    if ($vbulletin->GPC['useraction'] == 'ban')
    {
        require_once(DIR . '/includes/adminfunctions.php');
        require_once(DIR . '/includes/functions_banning.php');
        if (!($vbulletin->userinfo['permissions']['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['cancontrolpanel'] OR can_moderate(0, 'canbanusers')))
        {
            return_mod_fault();
        }

        // check that user has permission to ban the person they want to ban
        if (!($vbulletin->userinfo['permissions']['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['cancontrolpanel']))
        {
            foreach ($user_cache AS $userid => $userinfo)
            {
                if (can_moderate(0, '', $userinfo['userid'], $userinfo['usergroupid'] . (trim($userinfo['membergroupids']) ? ",$userinfo[membergroupids]" : ''))
                    OR $userinfo['permissions']['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['cancontrolpanel']
                    OR $userinfo['permissions']['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['ismoderator']
                    OR is_unalterable_user($userinfo['userid']))
                {
                    return_mod_fault(fetch_error('no_permission_ban_non_registered_users'));
                }
            }
        }
        else
        {
            foreach ($user_cache AS $userid => $userinfo)
            {
                if ($userinfo['permissions']['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['cancontrolpanel']
                    OR is_unalterable_user($userinfo['userid']))
                {
                    return_mod_fault(fetch_error('no_permission_ban_non_registered_users'));
                }
            }
        }
    }
    //($hook = vBulletinHook::fetch_hook('inlinemod_spam_permission')) ? eval($hook) : false;
}


if ($_POST['do'] == 'dodeletespam')
{
    $vbulletin->input->clean_array_gpc('p', array(
        'deleteother'     => TYPE_BOOL,
        'report'          => TYPE_BOOL,
        'useraction'      => TYPE_STR,
        'userid'          => TYPE_ARRAY_UINT,
        'type'            => TYPE_STR,
        'deletetype'      => TYPE_UINT, // 1 = soft, 2 = hard
        'deletereason'    => TYPE_STR,
        'keepattachments' => TYPE_BOOL,
    ));
    
    $vbulletin->GPC['deletereason'] = mobiquo_encode($vbulletin->GPC['deletereason'], 'to_local');
    
    // Check if we have users to punish
    if (!empty($user_cache))
    {
        switch ($vbulletin->GPC['useraction'])
        {
            case 'ban':
                $vbulletin->input->clean_array_gpc('p', array(
                    'usergroupid'       => TYPE_UINT,
                    'period'            => TYPE_STR,
                    'reason'            => TYPE_STR,
                ));
                
                $vbulletin->GPC['reason'] = mobiquo_encode($vbulletin->GPC['reason'], 'to_local');
                
                // tapatalk add for ban group specific
                $vbulletin->GPC['usergroupid'] = -1 ;
                foreach ($vbulletin->usergroupcache AS $usergroupid => $usergroup)
                {
                    if (!($usergroup['genericoptions'] & $vbulletin->bf_ugp_genericoptions['isnotbannedgroup']))
                    {
                        $vbulletin->GPC['usergroupid'] = $usergroupid;
                        break;
                    }
                }
                // end
                
                if (!isset($vbulletin->usergroupcache["{$vbulletin->GPC['usergroupid']}"]) OR ($vbulletin->usergroupcache["{$vbulletin->GPC['usergroupid']}"]['genericoptions'] & $vbulletin->bf_ugp_genericoptions['isnotbannedgroup']))
                {
                    return_mod_fault(fetch_error('invalid_usergroup_specified'));
                }

                // check that the number of days is valid
                if ($vbulletin->GPC['period'] != 'PERMANENT' AND !preg_match('#^(D|M|Y)_[1-9][0-9]?$#', $vbulletin->GPC['period']))
                {
                    return_mod_fault(fetch_error('invalid_ban_period_specified'));
                }

                if ($vbulletin->GPC['period'] == 'PERMANENT')
                {
                    // make this ban permanent
                    $liftdate = 0;
                }
                else
                {
                    // get the unixtime for when this ban will be lifted
                    $liftdate = convert_date_to_timestamp($vbulletin->GPC['period']);
                }

                $user_dms = array();

                $current_bans = $db->query_read("
                    SELECT user.userid, userban.liftdate, userban.bandate
                    FROM " . TABLE_PREFIX . "user AS user
                    LEFT JOIN " . TABLE_PREFIX . "userban AS userban ON(userban.userid = user.userid)
                    WHERE user.userid IN (" . implode(',', array_keys($user_cache)) . ")
                ");
                while ($current_ban = $db->fetch_array($current_bans))
                {
                    $userinfo = $user_cache["$current_ban[userid]"];
                    $userid = $userinfo['userid'];

                    if ($current_ban['bandate'])
                    { // they already have a ban, check if the current one is being made permanent, continue if its not
                        if ($liftdate AND $liftdate < $current_ban['liftdate'])
                        {
                            continue;
                        }

                        // there is already a record - just update this record
                        $db->query_write("
                            UPDATE " . TABLE_PREFIX . "userban SET
                            bandate = " . TIMENOW . ",
                            liftdate = $liftdate,
                            adminid = " . $vbulletin->userinfo['userid'] . ",
                            reason = '" . $db->escape_string($vbulletin->GPC['reason']) . "'
                            WHERE userid = $userinfo[userid]
                        ");
                    }
                    else
                    {
                        // insert a record into the userban table
                        /*insert query*/
                        $db->query_write("
                            INSERT INTO " . TABLE_PREFIX . "userban
                            (userid, usergroupid, displaygroupid, customtitle, usertitle, adminid, bandate, liftdate, reason)
                            VALUES
                            ($userinfo[userid], $userinfo[usergroupid], $userinfo[displaygroupid], $userinfo[customtitle], '" . $db->escape_string($userinfo['usertitle']) . "', " . $vbulletin->userinfo['userid'] . ", " . TIMENOW . ", $liftdate, '" . $db->escape_string($vbulletin->GPC['reason']) . "')
                        ");
                    }

                    // update the user record
                    $user_dms[$userid] =& datamanager_init('User', $vbulletin, ERRTYPE_SILENT);
                    $user_dms[$userid]->set_existing($userinfo);
                    $user_dms[$userid]->set('usergroupid', $vbulletin->GPC['usergroupid']);
                    $user_dms[$userid]->set('displaygroupid', 0);

                    // update the user's title if they've specified a special user title for the banned group
                    if ($vbulletin->usergroupcache["{$vbulletin->GPC['usergroupid']}"]['usertitle'] != '')
                    {
                        $user_dms[$userid]->set('usertitle', $vbulletin->usergroupcache["{$vbulletin->GPC['usergroupid']}"]['usertitle']);
                        $user_dms[$userid]->set('customtitle', 0);
                    }
                    $user_dms[$userid]->pre_save();
                }

                foreach ($user_dms AS $userdm)
                {
                    $userdm->save();
                }
            break;
            default:
                ($hook = vBulletinHook::fetch_hook('inlinemod_deletespam_defaultaction')) ? eval($hook) : false;
        }
    }

    // delete threads that are defined explicitly as spam by being ticked
    $physicaldel = ($vbulletin->GPC['deletetype'] == 2) ? true : false;
    $skipped_user_prune = array();

    if ($vbulletin->GPC['deleteother'] AND !empty($user_cache) AND can_moderate(-1, 'canmassprune'))
    {
        $remove_all_posts = array();
        $user_checks = $db->query_read_slave("SELECT COUNT(*) AS total, userid AS userid FROM " . TABLE_PREFIX . "post WHERE userid IN (". implode(', ', array_keys($user_cache)) . ") GROUP BY userid");
        while ($user_check = $db->fetch_array($user_checks))
        {
            if (intval($user_check['total']) <= 50)
            {
                $remove_all_posts[] = $user_check['userid'];
            }
            else
            {
                $skipped_user_prune[] = $user_check['userid'];
            }
        }

        if (!empty($remove_all_posts))
        {
            $threads = $db->query_read_slave("SELECT threadid FROM " . TABLE_PREFIX . "thread WHERE postuserid IN (". implode(', ', $remove_all_posts) . ")");
            while ($thread = $db->fetch_array($threads))
            {
                $threadids[] = $thread['threadid'];
            }

            // Yes this can pick up firstposts of threads but we check later on when fetching info, so it won't matter if its already deleted
            $posts = $db->query_read_slave("SELECT postid FROM " . TABLE_PREFIX . "post WHERE userid IN (". implode(', ', $remove_all_posts) . ")");
            while ($post = $db->fetch_array($posts))
            {
                $postids[] = $post['postid'];
            }
        }
    }

    if (!empty($threadids))
    {
        // Validate threads
        $threads = $db->query_read_slave("
            SELECT threadid, open, visible, forumid, title, postuserid
            FROM " . TABLE_PREFIX . "thread
            WHERE threadid IN (" . implode(',', $threadids) . ")
        ");
        while ($thread = $db->fetch_array($threads))
        {
            $forumperms = fetch_permissions($thread['forumid']);
            if (
                !($forumperms & $vbulletin->bf_ugp_forumpermissions['canview'])
                    OR
                !($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads'])
                    OR
                (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewothers']) AND $thread['postuserid'] != $vbulletin->userinfo['userid'])
                )
            {
                return_mod_fault();
            }

            if ($thread['open'] == 10 AND !can_moderate($thread['forumid'], 'canmanagethreads'))
            {
                // No permission to remove redirects.
                return_mod_fault(fetch_error('you_do_not_have_permission_to_manage_thread_redirects', $thread['title'], $vbulletin->forumcache["$thread[forumid]"]['title']));
            }
            else if (!$thread['visible'] AND !can_moderate($thread['forumid'], 'canmoderateposts'))
            {
                return_mod_fault(fetch_error('you_do_not_have_permission_to_manage_moderated_threads_and_posts'));
            }
            else if ($thread['visible'] == 2 AND !can_moderate($thread['forumid'], 'candeleteposts'))
            {
                return_mod_fault(fetch_error('you_do_not_have_permission_to_manage_deleted_threads_and_posts', $vbphrase['n_a'], $thread['title'], $vbulletin->forumcache["$thread[forumid]"]['title']));
            }
            else if ($thread['open'] != 10)
            {
                if (!can_moderate($thread['forumid'], 'canremoveposts') AND $physicaldel)
                {
                    return_mod_fault(fetch_error('you_do_not_have_permission_to_delete_threads_and_posts', $vbphrase['n_a'], $thread['title'], $vbulletin->forumcache["$thread[forumid]"]['title']));
                }
                else if (!can_moderate($thread['forumid'], 'candeleteposts') AND !$physicaldel)
                {
                    return_mod_fault(fetch_error('you_do_not_have_permission_to_delete_threads_and_posts', $vbphrase['n_a'], $thread['title'], $vbulletin->forumcache["$thread[forumid]"]['title']));
                }
            }

            $threadarray["$thread[threadid]"] = $thread;
            $forumlist["$thread[forumid]"] = true;
        }
    }

    $delinfo = array(
            'userid'          => $vbulletin->userinfo['userid'],
            'username'        => $vbulletin->userinfo['username'],
            'reason'          => $vbulletin->GPC['deletereason'],
            'keepattachments' => $vbulletin->GPC['keepattachments'],
    );
    foreach ($threadarray AS $threadid => $thread)
    {
        $countposts = $vbulletin->forumcache["$thread[forumid]"]['options'] & $vbulletin->bf_misc_forumoptions['countposts'];
        if (!$physicaldel AND $thread['visible'] == 2)
        {
            # Thread is already soft deleted
            continue;
        }

        $threadman =& datamanager_init('Thread', $vbulletin, ERRTYPE_SILENT, 'threadpost');
        $threadman->set_existing($thread);

        // Redirect
        if ($thread['open'] == 10)
        {
            $threadman->delete(false, true, $delinfo);
        }
        else
        {
            $threadman->delete($countposts, $physicaldel, $delinfo);

            // Search index maintenance
            vb_Search_Indexcontroller_Queue::indexQueue('vBForum', 'Post', 'delete_thread', $thread['threadid']);
        }
        unset($threadman);
    }

    if (!empty($postids))
    {
        // Validate Posts
        $posts = $db->query_read_slave("
            SELECT post.postid, post.threadid, post.parentid, post.visible, post.title,
                thread.forumid, thread.title AS threadtitle, thread.postuserid, thread.firstpostid, thread.visible AS thread_visible
            FROM " . TABLE_PREFIX . "post AS post
            LEFT JOIN " . TABLE_PREFIX . "thread AS thread USING (threadid)
            WHERE postid IN (" . implode(',', $postids) . ")
            ORDER BY postid
        ");
        while ($post = $db->fetch_array($posts))
        {
            $postarray["$post[postid]"] = $post;
            $threadlist["$post[threadid]"] = true;
            $forumlist["$post[forumid]"] = true;
            if ($post['firstpostid'] == $post['postid'])
            {    // deleting a thread so do not decremement the counters of any other posts in this thread
                $firstpost["$post[threadid]"] = true;
            }
            else if (!empty($firstpost["$post[threadid]"]))
            {
                $postarray["$post[postid]"]['skippostcount'] = true;
            }
        }
    }

    $gotothread = true;
    foreach ($postarray AS $postid => $post)
    {
        $foruminfo = fetch_foruminfo($post['forumid']);

        $postman =& datamanager_init('Post', $vbulletin, ERRTYPE_SILENT, 'threadpost');
        $postman->set_existing($post);
        $postman->delete(($foruminfo['countposts'] AND !$post['skippostcount']), $post['threadid'], $physicaldel, $delinfo);
        unset($postman);

        if ($vbulletin->GPC['threadid'] == $post['threadid'] AND $post['postid'] == $post['firstpostid'])
        {    // we've deleted the thread that we activated this action from so we can only return to the forum
            $gotothread = false;
        }
        else if ($post['postid'] == $postinfo['postid'] AND $physicaldel)
        {    // we came in via a post, which we have deleted so we have to go back to the thread
            $vbulletin->url = fetch_seo_url('thread', $postinfo, null, 'threadid', 'threadtitle');
        }
    }

    foreach(array_keys($threadlist) AS $threadid)
    {
        build_thread_counters($threadid);
    }
    foreach (array_keys($forumlist) AS $forumid)
    {
        build_forum_counters($forumid);
    }

    // empty cookie
    if ($vbulletin->GPC['type'] == 'thread')
    {
        setcookie('vbulletin_inlinethread', '', TIMENOW - 3600, '/');
    }
    else
    {
        setcookie('vbulletin_inlinepost', '', TIMENOW - 3600, '/');
    }
}

// ############################### start dodelete threads ###############################
if ($_POST['do'] == 'dodeletethreads')
{
    $vbulletin->input->clean_array_gpc('p', array(
        'deletetype'      => TYPE_UINT,     // 1=leave message; 2=removal
        'deletereason'    => TYPE_STR,
        'keepattachments' => TYPE_BOOL,
    ));
    
    $vbulletin->GPC['deletereason'] = mobiquo_encode($vbulletin->GPC['deletereason'], 'to_local');

    $physicaldel = iif($vbulletin->GPC['deletetype'] == 1, false, true);

    $delinfo = array(
        'userid'          => $vbulletin->userinfo['userid'],
        'username'        => $vbulletin->userinfo['username'],
        'reason'          => $vbulletin->GPC['deletereason'],
        'keepattachments' => $vbulletin->GPC['keepattachments']
    );

    // Validate threads
    $threads = $db->query_read_slave("
        SELECT threadid, open, visible, forumid, title, prefixid, postuserid, pollid
        FROM " . TABLE_PREFIX . "thread
        WHERE threadid IN(" . implode(',', $threadids) . ")
    ");
    while ($thread = $db->fetch_array($threads))
    {
        $forumperms = fetch_permissions($thread['forumid']);
        if (
            !($forumperms & $vbulletin->bf_ugp_forumpermissions['canview'])
                OR
            !($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads'])
                OR
            (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewothers']) AND $thread['postuserid'] != $vbulletin->userinfo['userid'])
            )
        {
            return_mod_fault();
        }

        $thread['prefix_plain_html'] = ($thread['prefixid'] ? htmlspecialchars_uni($vbphrase["prefix_$thread[prefixid]_title_plain"]) . ' ' : '');

        if ($thread['open'] == 10 AND !can_moderate($thread['forumid'], 'canmanagethreads'))
        {
            // No permission to remove redirects.
            return_mod_fault(fetch_error('you_do_not_have_permission_to_manage_thread_redirects', $thread['prefix_plain_html'] . $thread['title'], $vbulletin->forumcache["$thread[forumid]"]['title']));
        }
        else if (!$thread['visible'] AND !can_moderate($thread['forumid'], 'canmoderateposts'))
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_manage_moderated_threads_and_posts'));
        }
        else if ($thread['visible'] == 2 AND !can_moderate($thread['forumid'], 'candeleteposts'))
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_manage_deleted_threads_and_posts', $vbphrase['n_a'], $thread['prefix_plain_html'] . $thread['title'], $vbulletin->forumcache["$thread[forumid]"]['title']));
        }
        else if ($thread['open'] != 10)
        {
            if (!can_moderate($thread['forumid'], 'canremoveposts') AND $physicaldel)
            {
                return_mod_fault(fetch_error('you_do_not_have_permission_to_delete_threads_and_posts', $vbphrase['n_a'], $thread['prefix_plain_html'] . $thread['title'], $vbulletin->forumcache["$thread[forumid]"]['title']));
            }
            else if (!can_moderate($thread['forumid'], 'candeleteposts') AND !$physicaldel)
            {
                return_mod_fault(fetch_error('you_do_not_have_permission_to_delete_threads_and_posts', $vbphrase['n_a'], $thread['prefix_plain_html'] . $thread['title'], $vbulletin->forumcache["$thread[forumid]"]['title']));
            }
        }

        $threadarray["$thread[threadid]"] = $thread;
        $forumlist["$thread[forumid]"] = true;
    }

    if (empty($threadarray))
    {
            return_mod_fault(fetch_error('you_did_not_select_any_valid_threads'));
    }

    foreach ($threadarray AS $threadid => $thread)
    {
        $countposts = $vbulletin->forumcache["$thread[forumid]"]['options'] & $vbulletin->bf_misc_forumoptions['countposts'];
        if (!$physicaldel AND $thread['visible'] == 2)
        {
            # Thread is already soft deleted
            continue;
        }

        $threadman =& datamanager_init('Thread', $vbulletin, ERRTYPE_SILENT, 'threadpost');
        $threadman->set_existing($thread);

        // Redirect
        if ($thread['open'] == 10)
        {
            $threadman->delete(false, true, $delinfo);
        }
        else
        {
            $threadman->delete($countposts, $physicaldel, $delinfo);

            // Search index maintenance
        }
        unset($threadman);
    }

    foreach (array_keys($forumlist) AS $forumid)
    {
        build_forum_counters($forumid);
    }

    // empty cookie
    setcookie('vbulletin_inlinethread', '', TIMENOW - 3600, '/');
}

// ############################### start do undelete thread ###############################
if ($_POST['do'] == 'undeletethread')
{

    // Validate threads
    $threads = $db->query_read_slave("
        SELECT threadid, visible, forumid, title, prefixid, postuserid
        FROM " . TABLE_PREFIX . "thread
        WHERE threadid IN ($threadids)
            AND visible = 2
            AND open <> 10
    ");
    while ($thread = $db->fetch_array($threads))
    {
        $forumperms = fetch_permissions($thread['forumid']);
        if (
            !($forumperms & $vbulletin->bf_ugp_forumpermissions['canview'])
                OR
            !($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads'])
                OR
            (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewothers']) AND $thread['postuserid'] != $vbulletin->userinfo['userid'])
            )
        {
            return_mod_fault();
        }

        $thread['prefix_plain_html'] = ($thread['prefixid'] ? htmlspecialchars_uni($vbphrase["prefix_$thread[prefixid]_title_plain"]) . ' ' : '');

        if (!can_moderate($thread['forumid'], 'candeleteposts'))
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_manage_deleted_threads_and_posts', $vbphrase['n_a'], $thread['prefix_plain_html'] . $thread['title'], $vbulletin->forumcache["$thread[forumid]"]['title']));
        }

        $threadarray["$thread[threadid]"] = $thread;
        $forumlist["$thread[forumid]"] = true;
    }

    if (empty($threadarray))
    {
        return_mod_fault(fetch_error('you_did_not_select_any_valid_threads'));
    }

    foreach ($threadarray AS $threadid => $thread)
    {
        $countposts = $vbulletin->forumcache["$thread[forumid]"]['options'] & $vbulletin->bf_misc_forumoptions['countposts'];
        undelete_thread($thread['threadid'], $countposts, $thread);
    }

    foreach (array_keys($forumlist) AS $forumid)
    {
        build_forum_counters($forumid);
    }

    // empty cookie
    setcookie('vbulletin_inlinethread', '', TIMENOW - 3600, '/');
}

// ############################### start do approve thread ###############################
if ($_POST['do'] == 'approvethread')
{
    $countingthreads = array();
    $firstposts = array();
    // Validate threads
    $threads = $db->query_read_slave("
        SELECT threadid, visible, forumid, postuserid, firstpostid
        FROM " . TABLE_PREFIX . "thread
        WHERE threadid IN($threadids)
            AND visible = 0
            AND open <> 10
    ");
    while ($thread = $db->fetch_array($threads))
    {
        $forumperms = fetch_permissions($thread['forumid']);
        if (
            !($forumperms & $vbulletin->bf_ugp_forumpermissions['canview'])
                OR
            !($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads'])
                OR
            (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewothers']) AND $thread['postuserid'] != $vbulletin->userinfo['userid'])
            )
        {
            return_mod_fault();
        }


        if (!can_moderate($thread['forumid'], 'canmoderateposts'))
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_manage_moderated_threads_and_posts'));
        }

        $threadarray["$thread[threadid]"] = $thread;
        $forumlist["$thread[forumid]"] = true;
        $firstposts[] = $thread['firstpostid'];

        $foruminfo = fetch_foruminfo($thread['forumid']);
        if ($foruminfo['countposts'])
        {    // this thread is in a counting forum
            $countingthreads[] = $thread['threadid'];
        }
    }

    if (empty($threadarray))
    {
            return_mod_fault(fetch_error('you_did_not_select_any_valid_threads'));
    }

    // Set threads visible
    $db->query_write("
        UPDATE " . TABLE_PREFIX . "thread
        SET visible = 1
        WHERE threadid IN(" . implode(',', array_keys($threadarray)) . ")
    ");

    if (!empty($countingthreads))
    {    // Update post count for visible posts
        $userbyuserid = array();
        $posts = $db->query_read_slave("
            SELECT userid
            FROM " . TABLE_PREFIX . "post
            WHERE threadid IN(" . implode(',', $countingthreads) . ")
                AND visible = 1
                AND userid > 0
        ");
        while ($post = $db->fetch_array($posts))
        {
            if (!isset($userbyuserid["$post[userid]"]))
            {
                $userbyuserid["$post[userid]"] = 1;
            }
            else
            {
                $userbyuserid["$post[userid]"]++;
            }
        }

        if (!empty($userbyuserid))
        {
            $userbypostcount = array();
            $alluserids = '';

            foreach ($userbyuserid AS $postuserid => $postcount)
            {
                $alluserids .= ",$postuserid";
                $userbypostcount["$postcount"] .= ",$postuserid";
            }
            foreach($userbypostcount AS $postcount => $userids)
            {
                $casesql .= " WHEN userid IN (0$userids) THEN $postcount\n";
            }

            $db->query_write("
                UPDATE " . TABLE_PREFIX . "user
                SET posts = posts +
                CASE
                    $casesql
                    ELSE 0
                END
                WHERE userid IN (0$alluserids)
            ");
        }
    }

    $db->query_write("
        DELETE FROM " . TABLE_PREFIX . "moderation
        WHERE primaryid IN(" . implode(',', array_keys($threadarray)) . ")
            AND type = 'thread'
    ");
    $db->query_write("
        DELETE FROM " . TABLE_PREFIX . "spamlog
        WHERE postid IN(" . implode(',', $firstposts) . ")
    ");

    // Set thread redirects visible
    $db->query_write("
        UPDATE " . TABLE_PREFIX . "thread
        SET visible = 1
        WHERE open = 10 AND pollid IN(" . implode(',', array_keys($threadarray)) . ")
    ");

    foreach ($threadarray AS $threadid => $thread)
    {
        $modlog[] = array(
            'userid'   =>& $vbulletin->userinfo['userid'],
            'forumid'  =>& $thread['forumid'],
            'threadid' => $threadid,
        );
    }

    log_moderator_action($modlog, 'approved_thread');

    foreach (array_keys($forumlist) AS $forumid)
    {
        build_forum_counters($forumid);
    }

    // empty cookie
    setcookie('vbulletin_inlinethread', '', TIMENOW - 3600, '/');
}

// ############################### start do unapprove thread ###############################
if ($_POST['do'] == 'unapprovethread')
{

    $threadarray = array();
    $countingthreads = array();
    $modrecords = array();

    // Validate threads
    $threads = $db->query_read_slave("
        SELECT threadid, visible, forumid, title, prefixid, postuserid, firstpostid
        FROM " . TABLE_PREFIX . "thread
        WHERE threadid IN($threadids)
            AND visible > 0
            AND open <> 10
    ");
    while ($thread = $db->fetch_array($threads))
    {
        $forumperms = fetch_permissions($thread['forumid']);
        if (
            !($forumperms & $vbulletin->bf_ugp_forumpermissions['canview'])
                OR
            !($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads'])
                OR
            (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewothers']) AND $thread['postuserid'] != $vbulletin->userinfo['userid'])
            )
        {
            return_mod_fault();
        }

        $thread['prefix_plain_html'] = ($thread['prefixid'] ? htmlspecialchars_uni($vbphrase["prefix_$thread[prefixid]_title_plain"]) . ' ' : '');

        if (!can_moderate($thread['forumid'], 'canmoderateposts'))
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_manage_moderated_threads_and_posts'));
        }
        else if ($thread['visible'] == 2 AND !can_moderate($thread['forumid'], 'candeleteposts'))
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_manage_deleted_threads_and_posts', $vbphrase['n_a'], $thread['prefix_plain_html'] . $thread['title'], $vbulletin->forumcache["$thread[forumid]"]['title']));
        }

        $threadarray["$thread[threadid]"] = $thread;
        $forumlist["$thread[forumid]"] = true;

        $foruminfo = fetch_foruminfo($thread['forumid']);
        if ($thread['visible'] AND $foruminfo['countposts'])
        {    // this thread is visible AND in a counting forum
            $countingthreads[] = $thread['threadid'];
        }

        $modrecords[] = "($thread[threadid], 'thread', " . TIMENOW . ")";
    }

    if (empty($threadarray))
    {
            return_mod_fault(fetch_error('you_did_not_select_any_valid_threads'));
    }

    // Set threads hidden
    $db->query_write("
        UPDATE " . TABLE_PREFIX . "thread
        SET visible = 0
        WHERE threadid IN(" . implode(',', array_keys($threadarray)) . ")
    ");

    // Set thread redirects hidden
    $db->query_write("
        UPDATE " . TABLE_PREFIX . "thread
        SET visible = 0
        WHERE open = 10 AND pollid IN(" . implode(',', array_keys($threadarray)) . ")
    ");

    if (!empty($countingthreads))
    {    // Update post count for visible posts
        $userbyuserid = array();
        $posts = $db->query_read_slave("
            SELECT userid
            FROM " . TABLE_PREFIX . "post
            WHERE threadid IN(" . implode(',', $countingthreads) . ")
                AND visible = 1
                AND userid > 0
        ");
        while ($post = $db->fetch_array($posts))
        {
            if (!isset($userbyuserid["$post[userid]"]))
            {
                $userbyuserid["$post[userid]"] = -1;
            }
            else
            {
                $userbyuserid["$post[userid]"]--;
            }
        }

        if (!empty($userbyuserid))
        {
            $userbypostcount = array();
            $alluserids = '';

            foreach ($userbyuserid AS $postuserid => $postcount)
            {
                $alluserids .= ",$postuserid";
                $userbypostcount["$postcount"] .= ",$postuserid";
            }
            foreach($userbypostcount AS $postcount => $userids)
            {
                $casesql .= " WHEN userid IN (0$userids) THEN $postcount\n";
            }

            $db->query_write("
                UPDATE " . TABLE_PREFIX . "user
                SET posts = CAST(posts AS SIGNED) +
                CASE
                    $casesql
                    ELSE 0
                END
                WHERE userid IN (0$alluserids)
            ");
        }
    }

    // Insert Moderation Records
    $db->query_write("
        REPLACE INTO " . TABLE_PREFIX . "moderation
        (primaryid, type, dateline)
        VALUES
        " . implode(',', $modrecords) . "
    ");

    // Clean out deletionlog
    $db->query_write("
        DELETE FROM " . TABLE_PREFIX . "deletionlog
        WHERE primaryid IN(" . implode(',', array_keys($threadarray)) . ")
            AND type = 'thread'
    ");

    foreach ($threadarray AS $threadid => $thread)
    {
        $modlog[] = array(
            'userid'   =>& $vbulletin->userinfo['userid'],
            'forumid'  =>& $thread['forumid'],
            'threadid' => $threadid,
        );
    }

    log_moderator_action($modlog, 'unapproved_thread');

    foreach (array_keys($forumlist) AS $forumid)
    {
        build_forum_counters($forumid);
    }

    // empty cookie
    setcookie('vbulletin_inlinethread', '', TIMENOW - 3600, '/');
}

// ############################### start do domove thread ###############################
if ($_POST['do'] == 'domovethreads')
{
    $vbulletin->input->clean_array_gpc('p', array(
        'destforumid' => TYPE_UINT,
        'redirect'    => TYPE_STR,
        'frame'       => TYPE_STR,
        'period'      => TYPE_UINT,
    ));

    // check whether dest can contain posts
    $destforumid = verify_id('forum', $vbulletin->GPC['destforumid']);
    $destforuminfo = fetch_foruminfo($destforumid);
    if (!$destforuminfo['cancontainthreads'] OR $destforuminfo['link'])
    {
        return_mod_fault(fetch_error('moveillegalforum'));
    }

    // check destination forum permissions
    $forumperms = fetch_permissions($destforuminfo['forumid']);
    if (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canview']))
    {
        return_mod_fault();
    }

    //because of dependant controls its possible that "redirect" doesn't get passed.
    //if not then we want to assume no redirect
    if (!$vbulletin->GPC_exists['redirect'] OR $vbulletin->GPC['redirect'] == 'none')
    {
        $method = 'move';
    }
    else
    {
        $method = 'movered';
    }

    $countingthreads = array();
    $redirectids = array();

    // Validate threads
    $threads = $db->query_read_slave("
        SELECT threadid, visible, open, pollid, title, prefixid, postuserid, forumid
        " . ($method == 'movered' ? ", lastpost, replycount, postusername, lastposter, lastposterid, dateline, views, iconid" : "") . "
        FROM " . TABLE_PREFIX . "thread
        WHERE threadid IN(" . implode(',', $threadids) . ")
    ");
    while ($thread = $db->fetch_array($threads))
    {
        $forumperms = fetch_permissions($thread['forumid']);
        if (
            !($forumperms & $vbulletin->bf_ugp_forumpermissions['canview'])
                OR
            !($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads'])
                OR
            (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewothers']) AND $thread['postuserid'] != $vbulletin->userinfo['userid'])
            )
        {
            return_mod_fault();
        }

        $thread['prefix_plain_html'] = ($thread['prefixid'] ? htmlspecialchars_uni($vbphrase["prefix_$thread[prefixid]_title_plain"]) . ' ' : '');

        if (!can_moderate($thread['forumid'], 'canmanagethreads'))
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_manage_threads_and_posts', $vbphrase['n_a'], $thread['prefix_plain_html'] . $thread['title'], $vbulletin->forumcache["$thread[forumid]"]['title']));
        }
        else if (!$thread['visible'] AND !can_moderate($thread['forumid'], 'canmoderateposts'))
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_manage_moderated_threads_and_posts'));
        }
        else if ($thread['visible'] == 2 AND !can_moderate($thread['forumid'], 'candeleteposts'))
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_manage_deleted_threads_and_posts', $vbphrase['n_a'], $thread['prefix_plain_html'] . $thread['title'], $vbulletin->forumcache["$thread[forumid]"]['title']));
        }

        if ($thread['visible'] == 2 AND !can_moderate($destforuminfo['forumid'], 'candeleteposts'))
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_manage_deleted_threads_and_posts_in_destination_forum'));
        }
        else if (!$thread['visible'] AND !can_moderate($destforuminfo['forumid'], 'canmoderateposts'))
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_manage_moderated_threads_and_posts_in_destination_forum'));
        }

        // Ignore all threads that are already in the destination forum
        if ($thread['forumid'] == $destforuminfo['forumid'])
        {
            $sameforum = true;
            continue;
        }

        $threadarray["$thread[threadid]"] = $thread;
        $forumlist["$thread[forumid]"] = true;

        if ($thread['open'] == 10)
        {
            $redirectids["$thread[pollid]"][] = $thread['threadid'];
        }
        else if ($thread['visible'])
        {
            $countingthreads[] = $thread['threadid'];
        }
    }

    if (empty($threadarray))
    {
        if ($sameforum)
        {
            return_mod_fault(fetch_error('thread_is_already_in_the_forum'));
        }
        else
        {
            return_mod_fault(fetch_error('you_did_not_select_any_valid_threads'));
        }
    }

    // check to see if these threads are being returned to a forum they've already been in
    // if redirects exist in the destination forum, remove them
    $checkprevious = $db->query_read_slave("
        SELECT threadid
        FROM " . TABLE_PREFIX . "thread
        WHERE forumid = $destforuminfo[forumid]
            AND open = 10
            AND pollid IN(" . implode(',', array_keys($threadarray)) . ")
    ");
    while ($check = $db->fetch_array($checkprevious))
    {
        $old_redirect =& datamanager_init('Thread', $vbulletin, ERRTYPE_SILENT, 'threadpost');
        $old_redirect->set_existing($check);
        $old_redirect->delete(false, true, NULL, false);
        unset($old_redirect);
    }

    // check to see if a redirect is being moved to a forum where its destination thread already exists
    // if so delete the redirect
    if (!empty($redirectids))
    {
        $checkprevious = $db->query_read_slave("
            SELECT threadid
            FROM " . TABLE_PREFIX . "thread
            WHERE forumid = $destforuminfo[forumid]
                AND threadid IN(" . implode(',', array_keys($redirectids)) . ")

        ");
        while ($check = $db->fetch_array($checkprevious))
        {
            if (!empty($redirectids["$check[threadid]"]))
            {
                foreach($redirectids["$check[threadid]"] AS $threadid)
                {
                    $old_redirect =& datamanager_init('Thread', $vbulletin, ERRTYPE_SILENT, 'threadpost');
                    $old_redirect->set_existing($threadarray["$threadid"]);
                    $old_redirect->delete(false, true, NULL, false);
                    unset($old_redirect);

                    # Remove redirect threadids from $threadarray so no log entry is entered below or new redirect is added
                    unset($threadarray["$threadid"]);
                }
            }
        }
    }

    if (!empty($threadarray))
    {
        // Move threads
        // If mod can not manage threads in destination forum then unstick all moved threads
        $db->query_write("
            UPDATE " . TABLE_PREFIX . "thread
            SET forumid = $destforuminfo[forumid]
            " . (!can_moderate($destforuminfo['forumid'], 'canmanagethreads') ? ", sticky = 0" : "") . "
            WHERE threadid IN(" . implode(',', array_keys($threadarray)) . ")
        ");

        require_once(DIR . '/includes/functions_prefix.php');
        remove_invalid_prefixes(array_keys($threadarray), $destforuminfo['forumid']);

        // update canview status of thread subscriptions
        update_subscriptions(array('threadids' => array_keys($threadarray)));

        // kill the post cache for these threads
        delete_post_cache_threads(array_keys($threadarray));

        $movelog = array();
        // Insert Redirects FUN FUN FUN
        if ($method == 'movered')
        {
            $redirectsql = array();
            if ($vbulletin->GPC['redirect'] == 'expires')
            {
                switch($vbulletin->GPC['frame'])
                {
                    case 'h':
                        $expires = mktime(date('H') + $vbulletin->GPC['period'], date('i'), date('s'), date('m'), date('d'), date('y'));
                        break;
                    case 'd':
                        $expires = mktime(date('H'), date('i'), date('s'), date('m'), date('d') + $vbulletin->GPC['period'], date('y'));
                        break;
                    case 'w':
                        $expires = $vbulletin->GPC['period'] * 60 * 60 * 24 * 7 + TIMENOW;
                        break;
                    case 'y':
                        $expires =  mktime(date('H'), date('i'), date('s'), date('m'), date('d'), date('y') + $vbulletin->GPC['period']);
                        break;
                    case 'm':
                        default:
                        $expires =  mktime(date('H'), date('i'), date('s'), date('m') + $vbulletin->GPC['period'], date('d'), date('y'));
                }
            }
            foreach($threadarray AS $threadid => $thread)
            {
                if ($thread['visible'] == 1)
                {
                    $thread['open'] = 10;
                    $thread['pollid'] = $threadid;
                    unset($thread['threadid']);
                    $redir =& datamanager_init('Thread', $vbulletin, ERRTYPE_SILENT, 'threadpost');
                    foreach (array_keys($thread) AS $field)
                    {
                        // bypassing the verify_* calls; this data should be valid as is
                        if (isset($redir->validfields["$field"]))
                        {
                            $redir->setr($field, $thread["$field"], true, false);
                        }
                    }
                    $redirthreadid = $redir->save();
                    if ($vbulletin->GPC['redirect'] == 'expires')
                    {
                        $redirectsql[] = "$redirthreadid, $expires";
                    }
                    unset($redir);
                }
                else
                {
                    // else this is a moderated or deleted thread so leave no redirect behind
                    // insert modlog entry of just "move", not "moved with redirect"
                    // unset threadarray[threadid] so thread_moved_with_redirect log entry is not entered below.

                    unset($threadarray["$threadid"]);
                    $movelog = array(
                        'userid'   =>& $vbulletin->userinfo['userid'],
                        'forumid'  =>& $thread['forumid'],
                        'threadid' => $threadid,
                    );
                }
            }

            if (!empty($redirectsql))
            {
                $db->query_write("
                    INSERT INTO " . TABLE_PREFIX . "threadredirect
                        (threadid, expires)
                    VALUES
                        (" . implode("), (", $redirectsql) . ")
                ");
            }
        }

        if (!empty($movelog))
        {
            log_moderator_action($movelog, 'thread_moved_to_x', $destforuminfo['title']);
        }

        if (!empty($threadarray))
        {
            foreach ($threadarray AS $threadid => $thread)
            {
                $modlog[] = array(
                    'userid'   =>& $vbulletin->userinfo['userid'],
                    'forumid'  =>& $thread['forumid'],
                    'threadid' => $threadid,
                );
            }

            log_moderator_action($modlog, ($method == 'move') ? 'thread_moved_to_x' : 'thread_moved_with_redirect_to_a', $destforuminfo['title']);

            if (!empty($countingthreads))
            {
                $posts = $db->query_read_slave("
                    SELECT userid, threadid
                    FROM " . TABLE_PREFIX . "post
                    WHERE threadid IN(" . implode(',', $countingthreads) . ")
                        AND visible = 1
                        AND    userid > 0
                ");
                $userbyuserid = array();
                while ($post = $db->fetch_array($posts))
                {
                    $foruminfo = fetch_foruminfo($threadarray["$post[threadid]"]['forumid']);
                    if ($foruminfo['countposts'] AND !$destforuminfo['countposts'])
                    {    // Take away a post
                        if (!isset($userbyuserid["$post[userid]"]))
                        {
                            $userbyuserid["$post[userid]"] = -1;
                        }
                        else
                        {
                            $userbyuserid["$post[userid]"]--;
                        }
                    }
                    else if (!$foruminfo['countposts'] AND $destforuminfo['countposts'])
                    {    // Add a post
                        if (!isset($userbyuserid["$post[userid]"]))
                        {
                            $userbyuserid["$post[userid]"] = 1;
                        }
                        else
                        {
                            $userbyuserid["$post[userid]"]++;
                        }
                    }
                }

                if (!empty($userbyuserid))
                {
                    $userbypostcount = array();
                    $alluserids = '';

                    foreach ($userbyuserid AS $postuserid => $postcount)
                    {
                        $alluserids .= ",$postuserid";
                        $userbypostcount["$postcount"] .= ",$postuserid";
                    }
                    foreach ($userbypostcount AS $postcount => $userids)
                    {
                        $casesql .= " WHEN userid IN (0$userids) THEN $postcount";
                    }

                    $db->query_write("
                        UPDATE " . TABLE_PREFIX . "user
                        SET posts = CAST(posts AS SIGNED) +
                        CASE
                            $casesql
                            ELSE 0
                        END
                        WHERE userid IN (0$alluserids)
                    ");
                }
            }
        }
    }

    // Search index maintenance
    foreach($threadarray AS $threadid => $thread)
    {
        vb_Search_Indexcontroller_Queue::indexQueue('vBForum', 'Post',
            'thread_data_change', $threadid);
    }

    foreach(array_keys($forumlist) AS $forumid)
    {
        build_forum_counters($forumid);
    }
    build_forum_counters($destforuminfo['forumid']);

    // empty cookie
    setcookie('vbulletin_inlinethread', '', TIMENOW - 3600, '/');
}


// ############################### start do domerge thread ###############################
if ($_POST['do'] == 'domergethreads')
{
    $vbulletin->input->clean_array_gpc('p', array(
        'destforumid'   => TYPE_UINT,
        'destthreadid'  => TYPE_UINT,
        'redirect'      => TYPE_STR,
        'frame'         => TYPE_STR,
        'period'        => TYPE_UINT,
        'pollid'        => TYPE_UINT,
        'skipclearlist' => TYPE_BOOL,
    ));

    /*
    // check whether dest can contain posts
    $destforumid = verify_id('forum', $vbulletin->GPC['destforumid']);
    $destforuminfo = fetch_foruminfo($destforumid);
    if (!$destforuminfo['cancontainthreads'] OR $destforuminfo['link'])
    {
        return_mod_fault(fetch_error('moveillegalforum'));
    }

    // check destination forum permissions
    $forumperms = fetch_permissions($destforuminfo['forumid']);
    if (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canview']))
    {
        return_mod_fault();
    }

    if ($vbulletin->GPC['type'] == 1)
    {    // Mod cannot create merged hidden thread if they can't moderateposts dest forum
        if (!can_moderate($destforuminfo['forumid'], 'canmoderateposts'))
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_manage_moderated_threads_and_posts_in_destination_forum'));
        }
    }
    else if ($vbulletin->GPC['type'] == 2)
    {    // Mod can not create merged deleted thread if they can't deletethreads in dest forum
        if (!can_moderate($destforuminfo['forumid'], 'candeleteposts'))
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_manage_deleted_threads_and_posts_in_destination_forum'));
        }
    }
    */
    
    $counter = array(
        'moderated' => array(),
        'normal'    => array(),
        'deleted'   => array()
    );

    $destthread = 0;
    $pollinfo = array();
    $firstthread = array();
    $views = 0;
    $firstpostids = array();

    $sticky = 1;

    // Validate threads
    $threads = $db->query_read_slave("
        SELECT *
        FROM " . TABLE_PREFIX . "thread
        WHERE threadid IN(" . implode(',', $threadids) . ")
            AND open <> 10
        ORDER BY dateline, threadid
    ");
    while ($thread = $db->fetch_array($threads))
    {
        $forumperms = fetch_permissions($thread['forumid']);
        if (
            !($forumperms & $vbulletin->bf_ugp_forumpermissions['canview'])
                OR
            !($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads'])
                OR
            (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewothers']) AND $thread['postuserid'] != $vbulletin->userinfo['userid'])
            )
        {
            return_mod_fault();
        }

        $thread['prefix_plain_html'] = ($thread['prefixid'] ? htmlspecialchars_uni($vbphrase["prefix_$thread[prefixid]_title_plain"]) . ' ' : '');

        if (!can_moderate($thread['forumid'], 'canmanagethreads'))
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_manage_threads_and_posts', $vbphrase['n_a'], $thread['prefix_plain_html'] . $thread['title'], $vbulletin->forumcache["$thread[forumid]"]['title']));
        }
        else if (!$thread['visible'] AND !can_moderate($thread['forumid'], 'canmoderateposts'))
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_manage_moderated_threads_and_posts'));
        }
        else if ($thread['visible'] == 2 AND !can_moderate($thread['forumid'], 'candeleteposts'))
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_manage_deleted_threads_and_posts', $vbphrase['n_a'], $thread['prefix_plain_html'] . $thread['title'], $vbulletin->forumcache["$thread[forumid]"]['title']));
        }

        if ($thread['pollid'] AND (!$vbulletin->GPC['pollid'] OR ($thread['pollid'] == $vbulletin->GPC['pollid'])))
        {
            $pollinfo = array(
                'pollid'    => $thread['pollid'],
                'votenum'   => $thread['votenum'],
                'votetotal' => $thread['votetotal'],
                'threadid'  => $thread['threadid'],
            );
        }

        if (empty($firstthread))
        {
            $firstthread = $thread;
        }

        if ($thread['threadid'] == $vbulletin->GPC['destthreadid'])
        {
            $destthread = $thread;
            // tapatalk add
            $destforumid = $thread['forumid'];
            $destforuminfo = fetch_foruminfo($destforumid);
        }
        else
        {
            switch($thread['visible'])
            {
                case '0':
                    $counter['moderated'][] = $thread['threadid'];
                    break;
                case '1':
                    $counter['normal'][] = $thread['threadid'];
                    break;
                case '2':
                    $counter['deleted'][] = $thread['threadid'];
                    break;
                default: // Invalid State
                    continue;
            }
        }

        $threadarray["$thread[threadid]"] = $thread;
        $views += $thread['views'];
        $firstpostids[] = $thread['firstpostid'];
        $forumlist["$thread[forumid]"] = true;
    }
    if (empty($threadarray) OR empty($destthread))
    {
        return_mod_fault(fetch_error('you_did_not_select_any_valid_threads'));
    }

    if (count($threadarray) == 1)
    {
        return_mod_fault(fetch_error('not_much_would_be_accomplished_by_merging'));
    }

    @ignore_user_abort(true);
    $threadman =& datamanager_init('Thread', $vbulletin, ERRTYPE_STANDARD, 'threadpost');
    $threadman->set_existing($destthread);
    $threadman->set('forumid', $destforuminfo['forumid']);
    $threadman->set('views', $views);
    vb_Search_Indexcontroller_Queue::indexQueue('vBForum', 'Post', 'thread_data_change',
                $destthread['threadid']);
    // Poll coming from a thread other than the dest's current poll (if it has one)
    if (!empty($pollinfo) AND $destthread['threadid'] != $pollinfo['threadid'])
    {
        // Dest already has a poll so we need to kill it
        if ($destthread['pollid'])
        {
            $pollman =& datamanager_init('Poll', $vbulletin, ERRTYPE_STANDARD);
            $pollman->set_existing($destthread);
            $pollman->delete();
            unset($pollman);
        }

        $threadman->set('pollid', $pollinfo['pollid']);
        $threadman->set('votenum', $pollinfo['votenum']);
        $threadman->set('votetotal', $pollinfo['votetotal']);

        $threadarray["$pollinfo[threadid]"]['pollid'] = 0;
        $threadarray["$pollinfo[threadid]"]['votenum'] = 0;
        $threadarray["$pollinfo[threadid]"]['votetotal'] = 0;
        // Remove poll from source thread so delete_thread doesn't remove it
        $pollthreadinfo = array('threadid' => $pollinfo['threadid']);
        $threadpollman =& datamanager_init('Thread', $vbulletin, ERRTYPE_STANDARD, 'threadpost');
        $threadpollman->set_existing($pollthreadinfo);
        $threadpollman->set('pollid', 0);
        $threadpollman->set('votenum', 0);
        $threadpollman->set('votetotal', 0);
        $threadpollman->save();
        unset($threadpollman);
    }

    $threadman->save();
    unset($threadman);

    // Merged thread contains moderated threads
    if (count($counter['moderated']))
    {
        // Delete thread records that need to be converted into replies, simpler than constructing a massive case to alter them.
        $db->query_write("
            DELETE FROM " . TABLE_PREFIX . "moderation
            WHERE primaryid IN(" . implode(',', $counter['moderated']) . ")
                AND type = 'thread'
        ");

        $insertrecords = array();
        // Insert posts back in now
        foreach ($counter['moderated'] AS $threadid)
        {
            $insertrecords[] = "(" . $threadarray["$threadid"]['firstpostid'] . ", 'reply', " . TIMENOW . ")";
        }
        $db->query_write("
            REPLACE INTO " . TABLE_PREFIX . "moderation
                (primaryid, type, dateline)
            VALUES
            " . implode(',', $insertrecords) . "
        ");

        $db->query_write("
            UPDATE " . TABLE_PREFIX . "post AS post
            LEFT JOIN " . TABLE_PREFIX . "thread AS thread ON (post.threadid = thread.threadid)
            SET post.visible = 0
            WHERE    post.threadid IN(" . implode(',', $counter['moderated']) . ")
                AND post.visible = 1
                AND thread.firstpostid = post.postid
        ");
    }

    // Merged thread contains deleted threads
    if (count($counter['deleted']))
    {
        // Remove any deletion records for deleted threads as they are now undeleted
        $db->query_write("
            DELETE FROM " . TABLE_PREFIX . "deletionlog
            WHERE primaryid IN(" . implode(',', $counter['deleted']) . ")
                AND type = 'thread'
        ");
    }

    // Update parentids
    // Not certain about this -  seems that having a parentid of 0 is equal to having a parentid of the first postid so perhaps this is needless
    $db->query_write("
        UPDATE " . TABLE_PREFIX . "post
        SET parentid = $firstthread[firstpostid]
        WHERE threadid IN(" . implode(',', array_keys($threadarray)) . ")
            AND postid <> $firstthread[firstpostid]
            AND parentid = 0
    ");

    // Update Redirects
    $db->query_write("
        UPDATE " . TABLE_PREFIX . "thread
        SET pollid = $destthread[threadid]
        WHERE open = 10
            AND pollid IN(" . implode(',', array_keys($threadarray)) . ")
    ");

    $userbyuserid = array();

    $hiddenthreads = array_merge($counter['deleted'], $counter['moderated']);

    // Source Dest  Visible Thread    Hidden Thread
    // Yes    Yes   +hidden           -visible
    // Yes    No    -visible          -visible
    // No     Yes   +visible,+hidden  ~
    // No     No    ~                 ~

    $posts = $db->query_read_slave("
        SELECT userid, threadid
        FROM " . TABLE_PREFIX . "post
        WHERE threadid IN(" . implode(',', array_keys($threadarray)) . ")
            AND visible = 1
            AND userid > 0
    ");
    while ($post = $db->fetch_array($posts))
    {
        $set = 0;

        $foruminfo = fetch_foruminfo($threadarray["$post[threadid]"]['forumid']);

        // visible thread that merges moderated or deleted threads into a counting forum
        // increment post counts belonging to hidden/deleted threads
        if ($destthread['visible'] == 1 AND $destforuminfo['countposts'] AND in_array($post['threadid'], $hiddenthreads))
        {
            $set = 1;
        }

        // hidden thread that merges visible threads from a counting forum
        // OR visible thread that merges visible threads from a counting forum into a non counting forum
        // decrement post counts belonging to visible threads
        else if ($foruminfo['countposts'] AND (($destthread['visible'] != 1) OR ($destthread['visible'] == 1 AND !$destforuminfo['countposts'])) AND in_array($post['threadid'], $counter['normal']))
        {
            $set = -1;
        }

        // Visible thread that merges visible threads from a non counting forum into a counting forum
        // Increment post counts belonging to visible threads
        else if ($destthread['visible'] == 1 AND !$foruminfo['countposts'] AND $destforuminfo['countposts'] AND in_array($post['threadid'], $counter['normal']))
        {
            $set = 1;
        }

        if ($set != 0)
        {
            if (!isset($userbyuserid["$post[userid]"]))
            {
                $userbyuserid["$post[userid]"] = $set;
            }
            else if ($set == -1)
            {
                $userbyuserid["$post[userid]"]--;
            }
            else
            {
                $userbyuserid["$post[userid]"]++;
            }
        }
    }


    if (!empty($userbyuserid))
    {
        $userbypostcount = array();
        $alluserids = '';
        foreach ($userbyuserid AS $postuserid => $postcount)
        {
            $alluserids .= ",$postuserid";
            $userbypostcount["$postcount"] .= ",$postuserid";
        }
        foreach($userbypostcount AS $postcount => $userids)
        {
            $casesql .= " WHEN userid IN (0$userids) THEN $postcount\n";
        }

        $db->query_write("
            UPDATE " . TABLE_PREFIX . "user
            SET posts = CAST(posts AS SIGNED) +
            CASE
                $casesql
                ELSE 0
            END
            WHERE userid IN (0$alluserids)
        ");
    }

    // Update first post in each thread as title information in relation to the sames words being in the first post may have changed now.
    if (function_exists('delete_post_index') && function_exists('build_post_index'))
    {
        foreach ($firstpostids AS $firstpostid)
        {
            delete_post_index($firstpostid);
            build_post_index($firstpostid, $destforuminfo);
        }
    }


    // Update post threadids
    $db->query_write("
        UPDATE " . TABLE_PREFIX . "post
        SET threadid = $destthread[threadid]
        WHERE threadid IN(" . implode(',', array_keys($threadarray)) . ")
    ");

    // kill the post cache for the dest thread
    delete_post_cache_threads(array($destthread['threadid']));

    // Update subscribed threads
    $db->query_write("
        UPDATE IGNORE " . TABLE_PREFIX . "subscribethread
        SET threadid = $destthread[threadid]
        WHERE threadid IN(" . implode(',', array_keys($threadarray)) . ")
    ");

    require_once(DIR . '/includes/class_taggablecontent.php');
    $content = vB_Taggable_Content_Item::create($vbulletin, "vBForum_Thread",
        $destthread['threadid'], $destthread);
    $content->merge_tag_attachments(array_keys($threadarray));

    $users = array();
    $ratings = $db->query_read_slave("
        SELECT threadrateid, threadid, userid, vote, ipaddress
        FROM " . TABLE_PREFIX . "threadrate
        WHERE threadid IN(" . implode(',', array_keys($threadarray)) . ")
    ");
    while ($rating = $db->fetch_array($ratings))
    {
        $id = (!empty($rating['userid'])) ? $rating['userid'] : $rating['ipaddress'];
        $users["$id"]['vote'] += $rating['vote'];
        $users["$id"]['total'] += 1;
    }

    if (!empty($users))
    {
        $sql = array();
        $db->query_write("
            DELETE FROM " . TABLE_PREFIX . "threadrate
            WHERE threadid IN(" . implode(',', array_keys($threadarray)) . ")
        ");

        foreach ($users AS $id => $rating)
        {
            if (is_int($id))
            {
                $userid = $id;
                $ipaddress = '';
            }
            else
            {
                $userid = 0;
                $ipaddress = $id;
            }

            $vote = round($rating['vote'] / $rating['total']);
            $sql[] = "($destthread[threadid], $userid, $vote, '" . $db->escape_string($ipaddress) . "')";
        }
        unset($users);

        if (!empty($sql))
        {
            $db->query_write("
                INSERT INTO " . TABLE_PREFIX . "threadrate
                    (threadid, userid, vote, ipaddress)
                VALUES
                    " . implode(",\n", $sql)
            );
            unset($sql);
        }
    }

    // Remove destthread from the threadarray now so we don't lose it.
    unset($threadarray["{$destthread['threadid']}"]);

    // We had multiple subscriptions so remove all but the main one now
    $db->query_write("
        DELETE FROM " . TABLE_PREFIX . "subscribethread
        WHERE threadid IN(" . implode(',', array_keys($threadarray)) . ")
    ");

/*
    // remove any duplicated tags
    $db->query_write("
        DELETE FROM " . TABLE_PREFIX . "tagcontent
        WHERE contentid IN(" . implode(',', array_keys($threadarray)) . ") AND
            contenttype = 'thread'
    ");
*/

    // Update Moderator Log entries
    $db->query_write("
        UPDATE " . TABLE_PREFIX . "moderatorlog
        SET threadid = $destthread[threadid]
        WHERE threadid IN(" . implode(',', array_keys($threadarray)) . ")
    ");

    if ($vbulletin->GPC['redirect'] == 'expires')
    {
        switch($vbulletin->GPC['frame'])
        {
            case 'h':
                $expires = mktime(date('H') + $vbulletin->GPC['period'], date('i'), date('s'), date('m'), date('d'), date('y'));
                break;
            case 'd':
                $expires = mktime(date('H'), date('i'), date('s'), date('m'), date('d') + $vbulletin->GPC['period'], date('y'));
                break;
            case 'w':
                $expires = $vbulletin->GPC['period'] * 60 * 60 * 24 * 7 + TIMENOW;
                break;
            case 'y':
                $expires =  mktime(date('H'), date('i'), date('s'), date('m'), date('d'), date('y') + $vbulletin->GPC['period']);
                break;
            case 'm':
                default:
                $expires =  mktime(date('H'), date('i'), date('s'), date('m') + $vbulletin->GPC['period'], date('d'), date('y'));
        }
    }
    $redirectsql = array();


    // Remove source threads now
    foreach ($threadarray AS $threadid => $thread)
    {
        // Search index maintenance
        //this needs to happen before we nuke the old threads
        vb_Search_Indexcontroller_Queue::indexQueue('vBForum', 'Post', 'merge_group', $threadid , $destthread['threadid']);
        $foruminfo = fetch_foruminfo($thread['forumid']);
        $threadman =& datamanager_init('Thread', $vbulletin, ERRTYPE_STANDARD, 'threadpost');
        $threadman->set_existing($thread);
        if ($vbulletin->GPC['redirect'] AND $vbulletin->GPC['redirect'] != 'none')
        {
            $threadman->set('open', 10);
            $threadman->set('pollid', $destthread['threadid']);
            $threadman->set('visible', 1);
            $threadman->set('dateline', TIMENOW);
            $threadman->save();
            if ($vbulletin->GPC['redirect'] == 'expires')
            {
                $redirectsql[] = "$thread[threadid], $expires";
            }
        }
        else
        {
            $threadman->delete($foruminfo['countposts'], true);
        }
        unset($threadman);
    }

    if (!empty($redirectsql))
    {
        $db->query_write("
            INSERT INTO " . TABLE_PREFIX . "threadredirect
                (threadid, expires)
            VALUES
                (" . implode("), (", $redirectsql) . ")
        ");
    }

    build_thread_counters($destthread['threadid']);
    foreach (array_keys($forumlist) AS $forumid)
    {
        build_forum_counters($forumid);
    }

    // Add log entries
    $threadinfo = array(
        'threadid'  => $destthread['threadid'],
        'forumid' => $destforuminfo['forumid'],
    );
    log_moderator_action($threadinfo, 'thread_merged_from_multiple_threads');

    if (empty($forumlist["$destforuminfo[forumid]"]))
    {
        build_forum_counters($destforuminfo['forumid']);
    }

    // Update canview status of thread subscriptions
    update_subscriptions(array('threadids' => array($destthread['threadid'])));

    // empty cookie
    if (!$vbulletin->GPC['skipclearlist'])
    {
        setcookie('vbulletin_inlinethread', '', TIMENOW - 3600, '/');
    }
}


// ############################### start do delete posts ###############################
if ($_POST['do'] == 'dodeleteposts')
{
    $vbulletin->input->clean_array_gpc('p', array(
        'deletetype'      => TYPE_UINT,    // 1 = soft delete post, 2 = physically remove.
        'keepattachments' => TYPE_BOOL,
        'deletereason'    => TYPE_STR
    ));
    
    $vbulletin->GPC['deletereason'] = mobiquo_encode($vbulletin->GPC['deletereason'], 'to_local');

    $physicaldel = iif($vbulletin->GPC['deletetype'] == 1, false, true);

    // Validate posts
    $posts = $db->query_read_slave("
        SELECT post.postid, post.threadid, post.parentid, post.visible, post.title, post.userid AS posteruserid,
            thread.forumid, thread.title AS threadtitle, thread.postuserid, thread.firstpostid, thread.visible AS thread_visible
        FROM " . TABLE_PREFIX . "post AS post
        LEFT JOIN " . TABLE_PREFIX . "thread AS thread USING (threadid)
        WHERE postid IN (" . implode(',', $postids) . ")
        ORDER BY postid
    ");

    $deletethreads = array();
    $firstpost = array();
    while ($post = $db->fetch_array($posts))
    {
        $forumperms = fetch_permissions($post['forumid']);
        if (
            !($forumperms & $vbulletin->bf_ugp_forumpermissions['canview'])
                OR
            !($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads'])
                OR
            (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewothers']) AND $post['postuserid'] != $vbulletin->userinfo['userid'])
            )
        {
            return_mod_fault();
        }

        if ((!$post['visible'] OR !$post['thread_visible']) AND !can_moderate($post['forumid'], 'canmoderateposts'))
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_manage_moderated_threads_and_posts'));
        }
        else if (($post['visible'] == 2 OR $post['thread_visible'] == 2) AND !can_moderate($post['forumid'], 'candeleteposts'))
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_manage_deleted_threads_and_posts', $post['title'], $post['threadtitle'], $vbulletin->forumcache["$post[forumid]"]['title']));
        }
        else if (!can_moderate($post['forumid'], 'canremoveposts') AND !can_moderate($post['forumid'], 'candeleteposts'))
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_delete_threads_and_posts', $post['title'], $post['threadtitle'], $vbulletin->forumcache["$post[forumid]"]['title']));
        }

        if (!can_moderate($post['forumid'], 'canremoveposts') AND $physicaldel)
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_delete_threads_and_posts', $post['title'], $post['threadtitle'], $vbulletin->forumcache["$post[forumid]"]['title']));
        }
        else if (
            !$physicaldel
            AND (
                !can_moderate($post['forumid'], 'candeleteposts')
                AND (
                    $post['posteruserid'] != $vbulletin->userinfo['userid']
                    OR !($vbulletin->userinfo['permissions']['forumpermissions'] & $vbulletin->bf_ugp_forumpermissions['candeletepost'])
                )
            )
        )
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_delete_threads_and_posts', $post['title'], $post['threadtitle'], $vbulletin->forumcache["$post[forumid]"]['title']));
        }

        $postarray["$post[postid]"] = $post;
        $threadlist["$post[threadid]"] = true;
        $forumlist["$post[forumid]"] = true;

        if ($post['firstpostid'] == $post['postid'])
        {    // deleting a thread so do not decremement the counters of any other posts in this thread
            $firstpost["$post[threadid]"] = true;
        }
        else if (!empty($firstpost["$post[threadid]"]))
        {
            $postarray["$post[postid]"]['skippostcount'] = true;
        }
        
        if (isset($vbulletin->options['vbcmsforumid']) AND $post['forumid'] == $vbulletin->options['vbcmsforumid'])
        {
            $expire_cache = array('cms_comments_change', 'cms_comments_thread_' . intval($post['threadid']),
                'cms_comments_change_' . $post['threadid']);
            vB_Cache::instance()->event($expire_cache);
            vB_Cache::instance()->cleanNow();
        }
        
    }

    if (empty($postarray))
    {
        return_mod_fault(fetch_error('no_applicable_posts_selected'));
    }

    $firstpost = false;
    $gotothread = true;
    foreach ($postarray AS $postid => $post)
    {
        $foruminfo = fetch_foruminfo($post['forumid']);

        $postman =& datamanager_init('Post', $vbulletin, ERRTYPE_SILENT, 'threadpost');
        $postman->set_existing($post);
        $postman->delete(($foruminfo['countposts'] AND !$post['skippostcount']), $post['threadid'], $physicaldel, array(
            'userid'          => $vbulletin->userinfo['userid'],
            'username'        => $vbulletin->userinfo['username'],
            'reason'          => $vbulletin->GPC['deletereason'],
            'keepattachments' => $vbulletin->GPC['keepattachments']
        ));
        unset($postman);

        // Search index maintenance
        if ($physicaldel)
        {
            vb_Search_Indexcontroller_Queue::indexQueue('vBForum', 'Post', 'delete', $postid);
        }

        if ($vbulletin->GPC['threadid'] == $post['threadid'] AND $post['postid'] == $post['firstpostid'])
        {    // we've deleted the thread that we activated this action from so we can only return to the forum
            $gotothread = false;
        }
        else if ($post['postid'] == $postinfo['postid'] AND $physicaldel)
        {    // we came in via a post, which we have deleted so we have to go back to the thread
            $vbulletin->url = fetch_seo_url('thread', $post, null, 'threadid', 'threadtitle');
        }
    }

    foreach(array_keys($threadlist) AS $threadid)
    {
        build_thread_counters($threadid);
    }

    foreach(array_keys($forumlist) AS $forumid)
    {
        build_forum_counters($forumid);
    }

    // empty cookie
    setcookie('vbulletin_inlinepost', '', TIMENOW - 3600, '/');
}

// ############################### start do delete posts ###############################
if ($_POST['do'] == 'undeleteposts')
{

    // Validate posts
    $posts = $db->query_read_slave("
        SELECT post.postid, post.threadid, post.parentid, post.visible, post.title, post.userid,
            thread.forumid, thread.title AS thread_title, thread.postuserid, thread.firstpostid, thread.visible AS thread_visible,
            forum.options AS forum_options
        FROM " . TABLE_PREFIX . "post AS post
        LEFT JOIN " . TABLE_PREFIX . "thread AS thread USING (threadid)
        LEFT JOIN " . TABLE_PREFIX . "forum AS forum USING (forumid)
        WHERE postid IN ($postids)
            AND (post.visible = 2 OR (post.visible = 1 AND thread.visible = 2 AND post.postid = thread.firstpostid))
        ORDER BY postid
    ");

    $deletethreads = array();

    while ($post = $db->fetch_array($posts))
    {
        $forumperms = fetch_permissions($post['forumid']);
        if (
            !($forumperms & $vbulletin->bf_ugp_forumpermissions['canview'])
                OR
            !($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads'])
                OR
            (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewothers']) AND $post['postuserid'] != $vbulletin->userinfo['userid'])
            )
        {
            return_mod_fault();
        }

        if ((!$post['visible'] OR !$post['thread_visible']) AND !can_moderate($post['forumid'], 'canmoderateposts'))
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_manage_moderated_threads_and_posts'));
        }
        else if (($post['visible'] == 2 OR $post['thread_visible'] == 2) AND !can_moderate($post['forumid'], 'candeleteposts'))
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_manage_deleted_threads_and_posts', $post['title'], $post['thread_title'], $vbulletin->forumcache["$post[forumid]"]['title']));
        }

        $postarray["$post[postid]"] = $post;
        $threadlist["$post[threadid]"] = true;
        $forumlist["$post[forumid]"] = true;

        if ($post['firstpostid'] == $post['postid'])
        {    // undeleting a thread so need to update the $tinfo for any other posts in this thread
            $firstpost["$post[threadid]"] = true;
        }
        else if (!empty($firstpost["$post[threadid]"]))
        {
            $postarray["$post[postid]"]['thread_visible'] = 1;
        }
    }

    foreach ($postarray AS $postid => $post)
    {
        $tinfo = array(
            'threadid'    => $post['threadid'],
            'forumid'     => $post['forumid'],
            'visible'     => $post['thread_visible'],
            'firstpostid' => $post['firstpostid']
        );
        undelete_post($post['postid'], $post['forum_options'] & $vbulletin->bf_misc_forumoptions['countposts'], $post, $tinfo, false);
    }

    foreach (array_keys($threadlist) AS $threadid)
    {
        build_thread_counters($threadid);
    }

    foreach (array_keys($forumlist) AS $forumid)
    {
        build_forum_counters($forumid);
    }

    // empty cookie
    setcookie('vbulletin_inlinepost', '', TIMENOW - 3600, '/');
}

// ############################### start do approve posts ###############################
if ($_POST['do'] == 'approveposts')
{
    // Validate posts
    $posts = $db->query_read_slave("
        SELECT post.postid, post.threadid, post.visible, post.title, post.userid, post.dateline,
            thread.forumid, thread.title AS thread_title, thread.postuserid, thread.visible AS thread_visible,
            thread.firstpostid,
            user.usergroupid, user.displaygroupid, user.membergroupids, user.posts, usertextfield.rank # for rank updates
        FROM " . TABLE_PREFIX . "post AS post
        LEFT JOIN " . TABLE_PREFIX . "thread AS thread ON (thread.threadid = post.threadid)
        LEFT JOIN " . TABLE_PREFIX . "user AS user ON (post.userid = user.userid)
        LEFT JOIN " . TABLE_PREFIX . "usertextfield AS usertextfield ON (post.userid = usertextfield.userid)
        WHERE postid IN ($postids)
            AND (post.visible = 0 OR (post.visible = 1 AND thread.visible = 0 AND post.postid = thread.firstpostid))
        ORDER BY postid
    ");

    while ($post = $db->fetch_array($posts))
    {
        $forumperms = fetch_permissions($post['forumid']);
        if (
            !($forumperms & $vbulletin->bf_ugp_forumpermissions['canview'])
                OR
            !($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads'])
                OR
            (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewothers']) AND $post['postuserid'] != $vbulletin->userinfo['userid'])
            )
        {
            return_mod_fault();
        }

        if (!can_moderate($post['forumid'], 'canmoderateposts'))
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_manage_moderated_threads_and_posts'));
        }
        else if ($post['thread_visible'] == 2 AND !can_moderate($post['forumid'], 'candeleteposts'))
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_manage_deleted_threads_and_posts', $post['title'], $post['thread_title'], $vbulletin->forumcache["$post[forumid]"]['title']));
        }

        $postarray["$post[postid]"] = $post;
        $threadlist["$post[threadid]"] = true;
        $forumlist["$post[forumid]"] = true;

        if ($post['firstpostid'] == $post['postid'])
        {    // approving a thread so need to update the $tinfo for any other posts in this thread
            $firstpost["$post[threadid]"] = true;
        }
        else if (!empty($firstpost["$post[threadid]"]))
        {
            $postarray["$post[postid]"]['thread_visible'] = 1;
        }
    }

    if (empty($postarray))
    {
        return_mod_fault(fetch_error('no_applicable_posts_selected'));
    }

    foreach ($postarray AS $postid => $post)
    {
        $tinfo = array(
            'threadid'    => $post['threadid'],
            'forumid'     => $post['forumid'],
            'visible'     => $post['thread_visible'],
            'firstpostid' => $post['firstpostid']
        );

        $foruminfo = fetch_foruminfo($post['forumid']);
        approve_post($postid, $foruminfo['countposts'], true, $post, $tinfo, false);
    }

    foreach (array_keys($threadlist) AS $threadid)
    {
        build_thread_counters($threadid);
    }
    foreach (array_keys($forumlist) AS $forumid)
    {
        build_forum_counters($forumid);
    }

    // empty cookie
    setcookie('vbulletin_inlinepost', '', TIMENOW - 3600, '/');
}

// ############################### start do unapprove posts ###############################
if ($_POST['do'] == 'unapproveposts')
{
    // Validate posts
    $posts = $db->query_read_slave("
        SELECT post.postid, post.threadid, post.visible, post.title, post.userid,
            thread.forumid, thread.title AS thread_title, thread.postuserid, thread.visible AS thread_visible,
            thread.firstpostid,
            user.usergroupid, user.displaygroupid, user.membergroupids, user.posts, usertextfield.rank # for rank updates
        FROM " . TABLE_PREFIX . "post AS post
        LEFT JOIN " . TABLE_PREFIX . "thread AS thread ON (thread.threadid = post.threadid)
        LEFT JOIN " . TABLE_PREFIX . "user AS user ON (post.userid = user.userid)
        LEFT JOIN " . TABLE_PREFIX . "usertextfield AS usertextfield ON (post.userid = usertextfield.userid)
        WHERE postid IN ($postids)
            AND (post.visible > 0 OR (post.visible = 1 AND thread.visible > 0 AND post.postid = thread.firstpostid))
    ");

    $firstpost = array();
    while ($post = $db->fetch_array($posts))
    {
        $forumperms = fetch_permissions($post['forumid']);
        if (
            !($forumperms & $vbulletin->bf_ugp_forumpermissions['canview'])
                OR
            !($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads'])
                OR
            (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewothers']) AND $post['postuserid'] != $vbulletin->userinfo['userid'])
            )
        {
            return_mod_fault();
        }

        if (!can_moderate($post['forumid'], 'canmoderateposts'))
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_manage_moderated_threads_and_posts'));
        }
        else if (($post['visible'] == 2 OR $post['thread_visible'] == 2) AND !can_moderate($post['forumid'], 'candeleteposts'))
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_manage_deleted_threads_and_posts', $post['title'], $post['thread_title'], $vbulletin->forumcache["$post[forumid]"]['title']));
        }

        $postarray["$post[postid]"] = $post;
        $threadlist["$post[threadid]"] = true;
        $forumlist["$post[forumid]"] = true;
        if ($post['firstpostid'] == $post['postid'] AND $post['thread_visible'] == 1)
        {    // unapproving a thread so do not decremement the counters of any other posts in this thread
            $firstpost["$post[threadid]"] = true;
        }
        else if (!empty($firstpost["$post[threadid]"]))
        {
            $postarray["$post[postid]"]['skippostcount'] = true;
        }
    }

    if (empty($postarray))
    {
        return_mod_fault(fetch_error('no_applicable_posts_selected'));
    }

    foreach ($postarray AS $postid => $post)
    {
        $foruminfo = fetch_foruminfo($post['forumid']);
        $tinfo = array(
            'threadid'    => $post['threadid'],
            'forumid'     => $post['forumid'],
            'visible'     => $post['thread_visible'],
            'firstpostid' => $post['firstpostid']
        );
        // Can't send $thread without considering that thread_visible may change if we approve the first post of a thread
        unapprove_post($postid, ($foruminfo['countposts'] AND !$post['skippostcount']), true, $post, $tinfo, false);
    }

    foreach (array_keys($threadlist) AS $threadid)
    {
        build_thread_counters($threadid);
    }

    foreach (array_keys($forumlist) AS $forumid)
    {
        build_forum_counters($forumid);
    }

    // empty cookie
    setcookie('vbulletin_inlinepost', '', TIMENOW - 3600, '/');
}


// ############################### start do move posts ###############################
if ($_POST['do'] == 'domoveposts')
{
    $vbulletin->input->clean_array_gpc('p', array(
        'type'           => TYPE_UINT,
        'title'          => TYPE_NOHTML,
        'destforumid'    => TYPE_UINT,
        'destthreadid'   => TYPE_UINT
    ));
    
    $vbulletin->GPC['title'] = mobiquo_encode($vbulletin->GPC['title'], 'to_local');

    if ($vbulletin->GPC['type'] == 0)
    {    // Move to new thread
        if (empty($vbulletin->GPC['title']))
        {
            return_mod_fault(fetch_error('notitle'));
        }

        // check whether dest can contain posts
        $destforumid = verify_id('forum', $vbulletin->GPC['destforumid']);
        $destforuminfo = fetch_foruminfo($destforumid);
        if (!$destforuminfo['cancontainthreads'] OR $destforuminfo['link'])
        {
            return_mod_fault(fetch_error('moveillegalforum'));
        }

        // check destination forum permissions
        $forumperms = fetch_permissions($destforuminfo['forumid']);
        if (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canview']))
        {
            return_mod_fault();
        }
    }
    else
    {
        // Validate destination thread
        $destthreadid = $vbulletin->GPC['destthreadid'];
        if (!$destthreadid)
        {
            // Invalid URL
            return_mod_fault(fetch_error('mergebadurl'));
        }

        $destthreadid = verify_id('thread', $destthreadid);
        $destthreadinfo = fetch_threadinfo($destthreadid);
        $destforuminfo = fetch_foruminfo($destthreadinfo['forumid']);

        $forumperms = fetch_permissions($destforuminfo['forumid']);
        if (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canview']))
        {
            return_mod_fault();
        }

        if ($destthreadinfo['open'] == 10)
        {
            if (can_moderate($destthreadinfo['forumid']))
            {
                return_mod_fault(fetch_error('mergebadurl'));
            }
            else
            {
                return_mod_fault(fetch_error('invalidid', $vbphrase['thread'], $vbulletin->options['contactuslink']));
            }
        }

        if (($destthreadinfo['isdeleted'] AND !can_moderate($destthreadinfo['forumid'], 'candeleteposts')) OR (!$destthreadinfo['visible'] AND !can_moderate($destthreadinfo['forumid'], 'canmoderateposts')))
        {
            if (can_moderate($destthreadinfo['forumid']))
            {
                return_mod_fault();
            }
            else
            {
                return_mod_fault(fetch_error('invalidid', $vbphrase['thread'], $vbulletin->options['contactuslink']));
            }
        }

        // allow merging only in forums this user can moderate - otherwise, they
        // have a good vector for faking posts in other forums, etc
        if (!can_moderate($destthreadinfo['forumid']))
        {
            return_mod_fault(fetch_error('move_posts_moderated_forums_only'));
        }
    }

    $firstpost = array();
    $userbyuserid = array();
    $unique_thread_user = array();

    $posts = $db->query_read_slave("
        SELECT post.postid, post.threadid, post.visible, post.title, post.username, post.dateline, post.parentid, post.userid,
            thread.forumid, thread.title AS thread_title, thread.postuserid, thread.visible AS thread_visible, thread.firstpostid,
            thread.sticky, thread.open, thread.iconid,
            IF(subscribethread.emailupdate IS NULL, 0, 1) AS issubscribed, user.autosubscribe
        FROM " . TABLE_PREFIX . "post AS post
        INNER JOIN " . TABLE_PREFIX . "thread AS thread ON (thread.threadid = post.threadid)
        LEFT JOIN " . TABLE_PREFIX . "subscribethread AS subscribethread ON (subscribethread.threadid = thread.threadid AND subscribethread.userid = post.userid AND subscribethread.canview = 1)
        LEFT JOIN " . TABLE_PREFIX . "user AS user ON (post.userid = user.userid)
        WHERE postid IN (" . implode(',', $postids) . ")
        ORDER BY post.dateline

    ");
    while ($post = $db->fetch_array($posts))
    {

        if (!can_moderate($post['forumid'], 'canmanagethreads'))
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_manage_threads_and_posts', $post['title'], $post['thread_title'], $vbulletin->forumcache["$post[forumid]"]['title']));
        }
        else if ((!$post['visible'] OR !$post['thread_visible']) AND !can_moderate($post['forumid'], 'canmoderateposts'))
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_manage_moderated_threads_and_posts'));
        }
        else if (($post['visible'] == 2 OR $post['thread_visible'] == 2) AND !can_moderate($post['forumid'], 'candeleteposts'))
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_manage_deleted_threads_and_posts', $post['title'], $post['thread_title'], $vbulletin->forumcache["$post[forumid]"]['title']));
        }
        else if (($post['visible'] == 2 OR $post['thread_visible'] == 2) AND !can_moderate($destforuminfo['forumid'], 'candeleteposts'))
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_manage_deleted_threads_and_posts_in_destination_forum'));
        }
        else if ((!$post['visible'] OR !$post['thread_visible']) AND !can_moderate($destforuminfo['forumid'], 'canmoderateposts'))
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_manage_moderated_threads_and_posts_in_destination_forum'));
        }

        // Ignore posts that are already in the destination thread
        if ($post['threadid'] == $destthreadinfo['threadid'])
        {
            continue;
        }

        $postarray["$post[postid]"] = $post;
        $threadlist["$post[threadid]"] = true;
        $forumlist["$post[forumid]"] = true;

        if (empty($firstpost))
        {
            $firstpost = $post;
        }

        if ($post['userid'])
        {
            // find all unique thread-user combos
            $unique_thread_user["$post[threadid]"]["$post[userid]"] = array(
                'issubscribed' => $post['issubscribed'],
                'autosubscribe' => $post['autosubscribe'],
            );
        }
    }

    if (empty($postarray))
    {
        return_mod_fault(fetch_error('no_applicable_posts_selected'));
    }

    // we need the full structure of each thread before we move
    // (so we can figure out the parent relationships)
    $parentassoc = array();
    $parent_posts_sql = $db->query_read("
        SELECT postid, parentid, threadid
        FROM " . TABLE_PREFIX . "post
        WHERE threadid IN (" . implode(',', array_keys($threadlist)) . ")
        ORDER BY dateline
    ");
    while ($parent_post = $db->fetch_array($parent_posts_sql))
    {
        $parentassoc["$parent_post[threadid]"]["$parent_post[postid]"] = $parent_post['parentid'];
    }

    if ($vbulletin->GPC['type'] == 0)
    {    // Create a new thread
        $destthreadinfo = array(
            'open'         => $firstpost['open'],
            'iconid'       => $firstpost['iconid'],
            'visible'      => $firstpost['thread_visible'],
            'forumid'      => $destforuminfo['forumid'],
            'title'        => $vbulletin->GPC['title'],
            'views'        => 0,
            'dateline'     => TIMENOW,
            'postuserid'   => $firstpost['userid'],
            'postusername' => $firstpost['username'],
            'sticky'       => $firstpost['sticky']
        );

        $threadman =& datamanager_init('Thread', $vbulletin, ERRTYPE_SILENT, 'threadpost');
        $threadman->setr('forumid', $destthreadinfo['forumid'], true, false);
        $threadman->setr('title', $destthreadinfo['title'], true, false);
        $threadman->setr('iconid', $destthreadinfo['iconid'], true, false);
        $threadman->setr('open', $destthreadinfo['open'], true, false);
        $threadman->setr('views', $destthreadinfo['views']);
        $threadman->setr('visible', $destthreadinfo['visible'], true, false);
        // Rest of thread field will be populated by the build_thread_counters() call
        $destthreadinfo['threadid'] = $threadman->save();
        unset($threadman);
    }

    if ($firstpost['dateline'] <= $destthreadinfo['dateline'])
    {    // destination thread has a new first post (this will always be true for $type == 0)
        if ($firstpost['visible'] != 1)
        {    // Unhide the new first post since all first posts are visible
            $postman =& datamanager_init('Post', $vbulletin, ERRTYPE_SILENT, 'threadpost');
            $postman->set_existing($firstpost);
            $postman->set('visible', 1);
            $postman->save();
            unset($postman);

            // we need to give this user back his post if this is a visible thread in a counting forum
            if ($destthreadinfo['visible'] == 1 AND $destforuminfo['countposts'])
            {
                $userman =& datamanager_init('User', $vbulletin, ERRTYPE_STANDARD);
                $userman->set_existing($firstpost);
                $userman->set('posts', 'posts + 1', false);
                $userman->set_ladder_usertitle_relative(1);
                $userman->save();
                unset($userman);
            }

            if ($firstpost['firstpostid'] != $firstpost['postid'])
            {    // We didn't take the thread's first post so remove some records
                if (!$firstpost['visible'])
                {    // remove new first post's old moderation record
                    $db->query_write("
                        DELETE FROM " . TABLE_PREFIX . "moderation
                        WHERE primaryid = $firstpost[postid]
                            AND type = 'reply'
                    ");
                }
                else
                {    // remove new first post's old deletionlog record
                    $deletiondata =& datamanager_init('Deletionlog_ThreadPost', $vbulletin, ERRTYPE_SILENT, 'deletionlog');
                    $deletioninfo = array('type' => 'post', 'primaryid' => $firstpost['postid']);
                    $deletiondata->set_existing($deletioninfo);
                    $deletiondata->delete();
                    unset($deletiondata, $deletioninfo);
                }
            }
        }

        if (!$destthreadinfo['visible'])
        {    // Moderated thread so overwrite moderation record
            $db->query_write("
                REPLACE INTO " . TABLE_PREFIX . "moderation
                (primaryid, type, dateline)
                VALUES
                ($destthreadinfo[threadid], 'thread', " . TIMENOW . ")
            ");
        }
        else if ($destthreadinfo['visible'] == 2)
        {    // Deleted thread so overwrite the deletionlog entry
            $deletionman =& datamanager_init('Deletionlog_ThreadPost', $vbulletin, ERRTYPE_SILENT, 'deletionlog');
            $deletionman->set('primaryid', $destthreadinfo['threadid']);
            $deletionman->set('type', 'thread');
            $deletionman->set('userid', $vbulletin->userinfo['userid']);
            $deletionman->set('username', $vbulletin->userinfo['username']);
            $deletionman->save();
            unset($deletionman);
        }
    }

    // Move posts to their new thread
    $db->query_write("
        UPDATE " . TABLE_PREFIX . "post
        SET threadid = $destthreadinfo[threadid]
        WHERE postid IN (" . implode(',', array_keys($postarray)) . ")
    ");

    // kill the parsed post cache
    $db->query_write("
        DELETE FROM " . TABLE_PREFIX . "postparsed
        WHERE postid IN (" . implode(',', array_keys($postarray)) . ")
    ");

    // Search index maintenance
    vb_Search_Indexcontroller_Queue::indexQueue('vBForum', 'Post',
           'thread_data_change',  $destthreadinfo[threadid]);

    $userbyuserid = array();
    foreach ($postarray AS $postid => $post)
    {
        if ($post['userid'] AND $post['visible'] == 1)
        {
            $foruminfo = fetch_foruminfo($post['forumid']);

            if ($foruminfo['countposts'] AND $post['thread_visible'] == 1 AND (!$destforuminfo['countposts'] OR ($destforuminfo['countposts'] AND $destthreadinfo['visible'] != 1)))
            {    // Take away a post
                if (!isset($userbyuserid["$post[userid]"]))
                {
                    $userbyuserid["$post[userid]"] = -1;
                }
                else
                {
                    $userbyuserid["$post[userid]"]--;
                }
            }
            else if ($destforuminfo['countposts'] AND $destthreadinfo['visible'] == 1 AND (!$foruminfo['countposts'] OR ($foruminfo['countposts'] AND $post['thread_visible'] != 1)))
            {    // Add a post
                if (!isset($userbyuserid["$post[userid]"]))
                {
                    $userbyuserid["$post[userid]"] = 1;
                }
                else
                {
                    $userbyuserid["$post[userid]"]++;
                }
            }
        }

        // Let's deal with the residual thread(s) now
        if ($post['postid'] == $post['firstpostid'])
        {    // we moved a first post so thread must be tinkered with

            // Do we have any posts left in this thread?
            if ($firstleftpost = $db->query_first("
                SELECT postid, visible, threadid, title, pagetext
                FROM " . TABLE_PREFIX . "post
                WHERE threadid = $post[threadid]
                ORDER BY dateline, postid
                LIMIT 1
            "))
            {
                if (!$firstleftpost['visible'])
                {    // new first post is moderated so we must remove it's moderation record
                    $db->query_write("
                        DELETE FROM " . TABLE_PREFIX . "moderation
                        WHERE primaryid = $firstleftpost[postid]
                            AND type = 'reply'
                    ");
                }
                else if ($firstleftpost['visible'] == 2)
                {    // new first post is deleted so we must removed it's deletionlog record
                    $deletiondata =& datamanager_init('Deletionlog_ThreadPost', $vbulletin, ERRTYPE_SILENT, 'deletionlog');
                    $deletioninfo = array('type' => 'post', 'primaryid' => $firstleftpost['postid']);
                    $deletiondata->set_existing($deletioninfo);
                    $deletiondata->delete();
                    unset($deletiondata, $deletioninfo);
                }

                if ($firstleftpost['visible'] != 1)
                {    // post is not visible so we need to set it visible since first posts are always visible
                    $postman =& datamanager_init('Post', $vbulletin, ERRTYPE_SILENT, 'threadpost');
                    $postman->set_existing($firstleftpost);
                    $postman->set('visible', 1);
                    $postman->save();
                    unset($postman);

                    $foruminfo = fetch_foruminfo($post['forumid']);
                    // we need to give this user back his post if this is a visible thread in a counting forum
                    if ($post['thread_visible'] == 1 AND $foruminfo['countposts'])
                    {
                        $userman =& datamanager_init('User', $vbulletin, ERRTYPE_STANDARD);
                        $userman->set_existing($firstleftpost);
                        $userman->set('posts', 'posts + 1', false);
                        $userman->set_ladder_usertitle_relative(1);
                        $userman->save();
                        unset($userman);
                    }
                }

                // Update first post in each thread as title information in relation to the sames words being in the first post may have changed now.
                if (function_exists('delete_post_index') && function_exists('build_post_index'))
                {
                    delete_post_index($firstleftpost['postid'], $firstleftpost['title'], $firstleftpost['pagetext']);
                    build_post_index($firstleftpost['postid'] , $foruminfo);
                }
            }
            else    // we moved all of the thread :eek: delete the empty thread!
            {
                $threadinfo = fetch_threadinfo($post['threadid']);
                $threadman =& datamanager_init('Thread', $vbulletin, ERRTYPE_SILENT, 'threadpost');
                if ($threadinfo)
                {
                    $threadman->set_existing($threadinfo);
                }
                else
                {
                    // for legacy support, if some how we get a post that is no longer in a thread (IE: deleted twice?)
                    $threadman->set_existing($post);
                }
                $threadman->delete(false, true, NULL, false);
                unset($threadman);
            }
        }
    }

    if (!empty($userbyuserid))
    {
        $userbypostcount = array();
        $alluserids = '';

        foreach ($userbyuserid AS $postuserid => $postcount)
        {
            $alluserids .= ",$postuserid";
            $userbypostcount["$postcount"] .= ",$postuserid";
        }
        foreach ($userbypostcount AS $postcount => $userids)
        {
            $casesql .= " WHEN userid IN (0$userids) THEN $postcount";
        }

        $db->query_write("
            UPDATE " . TABLE_PREFIX . "user
            SET posts = CAST(posts AS SIGNED) +
            CASE
                $casesql
                ELSE 0
            END
            WHERE userid IN (0$alluserids)
        ");
    }

    // update parentids.
    $firstposts = array(
        $destthreadinfo['threadid'] => intval($destthreadinfo['firstpostid'])
    );

    // Remember, this loops through all posts in a thread, even if they aren't moved
    foreach ($parentassoc AS $threadid => $parentposts)
    {
        foreach ($parentposts AS $postid => $parentid)
        {
            if (empty($postarray["$postid"]) AND !empty($postarray["$parentid"]))
            {
                // case 1: post remains, but parent moved
                // we need to find the first post in this thread that wasn't moved
                $new_parentid = $parentid;

                // we continue as long as we find posts that were moved
                while (isset($postarray["$new_parentid"]) AND $new_parentid != 0)
                {
                    $new_parentid = $parentposts["$new_parentid"];
                }

                $check_threadid = $threadid;
            }
            else if (!empty($postarray["$postid"]) AND empty($postarray["$parentid"]))
            {
                // case 2: post moved, but parent remains
                // need to find the first post in this thread that was moved
                $new_parentid = $parentid;

                // we continue as long as we find posts that were not moved
                while (!isset($postarray["$new_parentid"]) AND $new_parentid != 0)
                {
                    $new_parentid = $parentposts["$new_parentid"];
                }

                $check_threadid = $destthreadinfo['threadid'];
            }
            else
            {
                // if both moved/not moved, then we don't need to do anything
                continue;
            }

            // are we trying to make this the top post in the thread?
            if ($new_parentid == 0)
            {
                if (!empty($firstposts["$check_threadid"]) AND $firstposts["$check_threadid"] != $postid)
                {
                    // already have a top post in this thread
                    $new_parentid = $firstposts["$check_threadid"];
                }
                else
                {
                    $firstposts["$check_threadid"] = $postid;
                }
            }

            $parentcasesql .= " WHEN postid = $postid THEN " . intval($new_parentid);
            $allpostids .= ",$postid";
        }
    }

    if ($parentcasesql)
    {
        $db->query_write("
            UPDATE " . TABLE_PREFIX . "post
            SET parentid =
            CASE
                $parentcasesql
            ELSE
                parentid
            END
            WHERE postid IN (0$allpostids)
        ");
    }

    if ($unique_thread_user)
    {
        // Copy thread subscriptions. To do this, we take the "minimum" subscription level.
        // If you aren't subscribed to a thread by default OR aren't subscribed to this thread,
        // you won't be subscribed to the new thread. If you subscribe by default and are subscribed
        // to this thread, you will be subscribed with the default option. (See 3.6 bug 1342.)
        $insert_subscriptions = array();

        foreach ($unique_thread_user AS $threadid => $users)
        {
            foreach ($users AS $userid => $subscriptioninfo)
            {
                if ($subscriptioninfo['issubscribed'] AND $subscriptioninfo['autosubscribe'] != -1)
                {
                    $insert_subscriptions[] = "($userid, $destthreadinfo[threadid], $subscriptioninfo[autosubscribe], 0, 1)";
                }
            }
        }

        if ($insert_subscriptions)
        {
            $db->query_write("
                INSERT IGNORE INTO " . TABLE_PREFIX . "subscribethread
                    (userid, threadid, emailupdate, folderid, canview)
                VALUES
                    " . implode(', ', $insert_subscriptions)
            );
        }

        // need to check permissions on these threads
        update_subscriptions(array('threadids' => array($destthreadinfo['threadid'])));
    }

    $getfirstpost = $db->query_first("
        SELECT *
        FROM " . TABLE_PREFIX . "post
        WHERE threadid = $destthreadinfo[threadid]
        ORDER BY dateline, postid
        LIMIT 1
    ");

    // make the first post have the title of the new split thread
    $postdata =& datamanager_init('Post', $vbulletin, ERRTYPE_SILENT, 'threadpost');
    $postdata->set_existing($getfirstpost);
    $postdata->set('title', $destthreadinfo['title'], true, false); // don't clean it -- already been cleaned
    $postdata->set('iconid', $destthreadinfo['iconid'], true, false);
    $postdata->save();

    if (function_exists('delete_post_index') && function_exists('build_post_index'))
    {
        delete_post_index($getfirstpost['postid'], $getfirstpost['title'], $getfirstpost['pagetext']);
        build_post_index($getfirstpost['postid'] , $destforuminfo);
    }

    foreach (array_keys($threadlist) AS $threadid)
    {
        build_thread_counters($threadid);
    }

    if (empty($threadlist["$destthreadinfo[threadid]"]))
    {
        build_thread_counters($destthreadinfo['threadid']);
    }

    foreach(array_keys($forumlist) AS $forumid)
    {
        build_forum_counters($forumid);
    }

    if (empty($forumlist["$destforuminfo[forumid]"]))
    {
        build_forum_counters($destforuminfo['forumid']);
    }

    log_moderator_action($threadinfo, 'thread_split_to_x', $destthreadinfo['threadid']);

    // empty cookie
    setcookie('vbulletin_inlinepost', '', TIMENOW - 3600, '/');
}

if ($_POST['do'] == 'renamethread')
{
    $vbulletin->input->clean_array_gpc('p', array(
        'title' => TYPE_STR,
    ));
    
    $vbulletin->GPC['title'] = mobiquo_encode($vbulletin->GPC['title'], 'to_local');
    
    // Validate threads
    $threads = $db->query_read_slave("
        SELECT threadid, visible, forumid, postuserid, firstpostid
        FROM " . TABLE_PREFIX . "thread
        WHERE threadid IN($threadids)
    ");
    while ($thread = $db->fetch_array($threads))
    {
        $forumperms = fetch_permissions($thread['forumid']);
        if (
            !($forumperms & $vbulletin->bf_ugp_forumpermissions['canview'])
                OR
            !($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads'])
                OR
            (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewothers']) AND $thread['postuserid'] != $vbulletin->userinfo['userid'])
            )
        {
            return_mod_fault();
        }


        if (!can_moderate($thread['forumid'], 'canmoderateposts'))
        {
            return_mod_fault(fetch_error('you_do_not_have_permission_to_manage_moderated_threads_and_posts'));
        }

        $threadarray["$thread[threadid]"] = $thread;
    }
    
    if (empty($threadarray))
    {
        return_mod_fault(fetch_error('you_did_not_select_any_valid_threads'));
    }
    
    // rename thread and post title
    $db->query_write("
        UPDATE " . TABLE_PREFIX . "thread
        SET title = '" . $db->escape_string($vbulletin->GPC['title']) . "'
        WHERE threadid = '$threadids'
    ");
    
    $db->query_write("
        UPDATE " . TABLE_PREFIX . "post
        SET title = '" . $db->escape_string($vbulletin->GPC['title']) . "'
        WHERE threadid = '$threadids' AND parentid = 0
    ");

    // empty cookie
    setcookie('vbulletin_inlinethread', '', TIMENOW - 3600, '/');
}
