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
require_once(DIR . '/includes/class_postbit.php');


function save_raw_post_func($xmlrpc_params)
{
    global $vbulletin, $db, $forumperms, $permissions, $vbphrase, $html_content;

    $decode_params = php_xmlrpc_decode($xmlrpc_params);
    $postid = intval($decode_params[0]);
    $posttitle = mobiquo_encode($decode_params[1], 'to_local');
    $postcontent = mobiquo_encode($decode_params[2], 'to_local');
    $html_content= false;

    if(isset($decode_params[3]) && $decode_params[3]) {
        $html_content = true;
    }
    
    $vbulletin->GPC['postid'] = $postid;
    $vbulletin->GPC['message'] = $postcontent;
    $vbulletin->GPC['title']  = $posttitle;
    
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

    if ((!$threadinfo['visible'] OR $threadinfo['isdeleted']) AND !can_moderate($threadinfo['forumid']))
    {
        return_fault(fetch_error('invalidid', $vbphrase['thread']));
    }

    // get permissions info
    $_permsgetter_ = 'edit post';
    $forumperms = fetch_permissions($threadinfo['forumid']);
    if (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canview']) OR !($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads']) OR (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewothers']) AND ($threadinfo['postuserid'] != $vbulletin->userinfo['userid'] OR $vbulletin->userinfo['userid'] == 0)))
    {
        return_fault();
    }

    $foruminfo = fetch_foruminfo($threadinfo['forumid'], false);

    // check if there is a forum password and if so, ensure the user has it set
    if (!verify_forum_password($foruminfo['forumid'], $foruminfo['password'], false))
        return_fault('Your administrator has required a password to access this forum.');

    // need to get last post-type information
    cache_ordered_forums(1);

    // determine if we are allowed to be updating the thread's info
    $can_update_thread = (
        $threadinfo['firstpostid'] == $postinfo['postid']
        AND (can_moderate($threadinfo['forumid'], 'caneditthreads')
             OR ($postinfo['dateline'] + $vbulletin->options['editthreadtitlelimit'] * 60) > TIMENOW)
    );
    
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

    // Variables reused in templates
    $posthash = $vbulletin->input->clean_gpc('p', 'posthash', TYPE_NOHTML);
    $poststarttime = $vbulletin->input->clean_gpc('p', 'poststarttime', TYPE_UINT);

    $vbulletin->input->clean_array_gpc('p', array(
        'stickunstick'    => TYPE_BOOL,
        'openclose'       => TYPE_BOOL,
        'wysiwyg'         => TYPE_BOOL,
        'prefixid'        => TYPE_NOHTML,
        'iconid'          => TYPE_UINT,
        'parseurl'        => TYPE_BOOL,
        'signature'       => TYPE_BOOL,
        'disablesmilies' => TYPE_BOOL,
        'reason'          => TYPE_NOHTML,
        'preview'         => TYPE_STR,
        'folderid'        => TYPE_UINT,
        'emailupdate'     => TYPE_UINT,
        'ajax'            => TYPE_BOOL,
        'advanced'        => TYPE_BOOL,
        'postcount'       => TYPE_UINT,
        'podcasturl'      => TYPE_STR,
        'podcastsize'     => TYPE_UINT,
        'podcastexplicit' => TYPE_BOOL,
        'podcastkeywords' => TYPE_STR,
        'podcastsubtitle' => TYPE_STR,
        'podcastauthor'   => TYPE_STR,

        'quickeditnoajax' => TYPE_BOOL // true when going from an AJAX edit but not using AJAX
    ));
    // Make sure the posthash is valid

    if (md5($poststarttime . $vbulletin->userinfo['userid'] . $vbulletin->userinfo['salt']) != $posthash)
    {
        $posthash = 'invalid posthash'; // don't phrase me
    }
    
    $edit = array();
    $edit['message'] = $vbulletin->GPC['message'];

    $cansubscribe = true;
    // Are we editing someone else's post? If so load that users subscription info for this thread.
    if ($vbulletin->userinfo['userid'] != $postinfo['userid'])
    {
        if ($postinfo['userid'])
        {
            $userinfo = fetch_userinfo($postinfo['userid']);
            cache_permissions($userinfo);
        }

        $cansubscribe = (
        $userinfo['forumpermissions']["$foruminfo[forumid]"] & $vbulletin->bf_ugp_forumpermissions['canview'] AND
        $userinfo['forumpermissions']["$foruminfo[forumid]"] & $vbulletin->bf_ugp_forumpermissions['canviewthreads'] AND
        ($threadinfo['postuserid'] == $userinfo['userid'] OR $userinfo['forumpermissions']["$foruminfo[forumid]"] & $vbulletin->bf_ugp_forumpermissions['canviewothers'])
        );

        if ($cansubscribe AND $otherthreadinfo = $db->query_first_slave("
            SELECT emailupdate, folderid
            FROM " . TABLE_PREFIX . "subscribethread
            WHERE threadid = $threadinfo[threadid] AND
                userid = $postinfo[userid] AND
                canview = 1"))
        {
            $threadinfo['issubscribed'] = true;
            $threadinfo['emailupdate'] = $otherthreadinfo['emailupdate'];
            $threadinfo['folderid'] = $otherthreadinfo['folderid'];
        }
        else
        {
            $threadinfo['issubscribed'] = false;
            // use whatever emailupdate setting came through
        }
    }


    $edit['iconid'] =& $vbulletin->GPC['iconid'];
    $edit['title'] =& $vbulletin->GPC['title'];
    $edit['prefixid'] = ($vbulletin->GPC_exists['prefixid'] ? $vbulletin->GPC['prefixid'] : $threadinfo['prefixid']);

    $edit['podcasturl'] =& $vbulletin->GPC['podcasturl'];
    $edit['podcastsize'] =& $vbulletin->GPC['podcastsize'];
    $edit['podcastexplicit'] =& $vbulletin->GPC['podcastexplicit'];
    $edit['podcastkeywords'] =& $vbulletin->GPC['podcastkeywords'];
    $edit['podcastsubtitle'] =& $vbulletin->GPC['podcastsubtitle'];
    $edit['podcastauthor'] =& $vbulletin->GPC['podcastauthor'];

    // Leave this off for quickedit->advanced so that a post with unparsed links doesn't get parsed just by going to Advanced Edit
    if ($vbulletin->GPC['advanced'])
    {
        $edit['parseurl'] = false;
    }
    else
    {
        $edit['parseurl'] =& $vbulletin->GPC['parseurl'];
    }
    $edit['signature'] = $GLOBALS['config']['forum_signature'];
    $edit['disablesmilies'] =& $vbulletin->GPC['disablesmilies'];
    $edit['enablesmilies'] = $edit['allowsmilie'] = ($edit['disablesmilies']) ? 0 : 1;
    $edit['stickunstick'] =& $vbulletin->GPC['stickunstick'];
    $edit['openclose'] =& $vbulletin->GPC['openclose'];

    $edit['reason'] = fetch_censored_text($vbulletin->GPC['reason']);
    $edit['preview'] =& $vbulletin->GPC['preview'];
    $edit['folderid'] =& $vbulletin->GPC['folderid'];

    if ($vbulletin->GPC_exists['emailupdate'])
    {
        $edit['emailupdate'] =& $vbulletin->GPC['emailupdate'];
    }
    else
    {
        $edit['emailupdate'] = array_pop($array = array_keys(fetch_emailchecked($threadinfo)));
    }

    $dataman =& datamanager_init('Post', $vbulletin, ERRTYPE_ARRAY, 'threadpost');
    $dataman->set_existing($postinfo);

    // set info
    $dataman->set_info('parseurl', ($foruminfo['allowbbcode'] AND $edit['parseurl']));
    $dataman->set_info('posthash', $posthash);
    $dataman->set_info('forum', $foruminfo);
    $dataman->set_info('thread', $threadinfo);
    $dataman->set_info('show_title_error', true);
    $dataman->set_info('podcasturl', $edit['podcasturl']);
    $dataman->set_info('podcastsize', $edit['podcastsize']);
    $dataman->set_info('podcastexplicit', $edit['podcastexplicit']);
    $dataman->set_info('podcastkeywords', $edit['podcastkeywords']);
    $dataman->set_info('podcastsubtitle', $edit['podcastsubtitle']);
    $dataman->set_info('podcastauthor', $edit['podcastauthor']);
    if ($postinfo['userid'] == $vbulletin->userinfo['userid'])
    {
        $dataman->set_info('user', $vbulletin->userinfo);
    }

    // set options
    $dataman->setr('showsignature', $edit['signature']);
    $dataman->setr('allowsmilie', $edit['enablesmilies']);

    $dataman->setr('title', $edit['title']);
    $dataman->setr('pagetext', $edit['message']);
    if ($postinfo['userid'] != $vbulletin->userinfo['userid'])
    {
        $dataman->setr('iconid', $edit['iconid'], true, false);
    }
    else
    {
        $dataman->setr('iconid', $edit['iconid']);
    }

    $postusername = $vbulletin->userinfo['username'];

    $dataman->pre_save();
    if ($dataman->errors)
    {
        $errors = $dataman->errors;
    }
    if ($dataman->info['podcastsize'])
    {
        $edit['podcastsize'] = $dataman->info['podcastsize'];
    }

    if (sizeof($errors) > 0)
    {
        $error_string = mobiquo_encode(implode('', $errors));
        return_fault($error_string);
    }
    else
    {
        $dataman->save();
        $update_edit_log = true;

        // don't show edited by AND reason unchanged - don't update edit log
        if (!($permissions['genericoptions'] & $vbulletin->bf_ugp_genericoptions['showeditedby']) AND $edit['reason'] == $postinfo['edit_reason'])
        {
            $update_edit_log = false;
        }

        if ($update_edit_log)
        {
            // ug perm: show edited by
            if ($postinfo['dateline'] < (TIMENOW - ($vbulletin->options['noeditedbytime'] * 60)) OR !empty($edit['reason']))
            {
                // save the postedithistory
                if ($vbulletin->options['postedithistory'])
                {
                    // insert original post on first edit
                    if (!$db->query_first("SELECT postedithistoryid FROM " . TABLE_PREFIX . "postedithistory WHERE original = 1 AND postid = " . $postinfo['postid']))
                    {
                        $db->query_write("
                            INSERT INTO " . TABLE_PREFIX . "postedithistory
                                (postid, userid, username, title, iconid, dateline, reason, original, pagetext)
                            VALUES
                                ($postinfo[postid],
                                " . $postinfo['userid'] . ",
                                '" . $db->escape_string($postinfo['username']) . "',
                                '" . $db->escape_string($postinfo['title']) . "',
                                $postinfo[iconid],
                                " . $postinfo['dateline'] . ",
                                '',
                                1,
                                '" . $db->escape_string($postinfo['pagetext']) . "')
                        ");
                    }
                    
                    // insert the new version
                    $db->query_write("
                        INSERT INTO " . TABLE_PREFIX . "postedithistory
                            (postid, userid, username, title, iconid, dateline, reason, pagetext)
                        VALUES
                            ($postinfo[postid],
                            " . $vbulletin->userinfo['userid'] . ",
                            '" . $db->escape_string($vbulletin->userinfo['username']) . "',
                            '" . $db->escape_string($edit['title']) . "',
                            $edit[iconid],
                            " . TIMENOW . ",
                            '" . $db->escape_string($edit['reason']) . "',
                            '" . $db->escape_string($edit['message']) . "')
                    ");
                }
                
                /*insert query*/
                $db->query_write("
                    REPLACE INTO " . TABLE_PREFIX . "editlog
                        (postid, userid, username, dateline, reason, hashistory)
                    VALUES
                        ($postinfo[postid],
                        " . $vbulletin->userinfo['userid'] . ",
                        '" . $db->escape_string($vbulletin->userinfo['username']) . "',
                        " . TIMENOW . ",
                        '" . $db->escape_string($edit['reason']) . "',
                        " . ($vbulletin->options['postedithistory'] ? 1 : 0) . ")
                ");
            }
        }

        $date = vbdate($vbulletin->options['dateformat'], TIMENOW);
        $time = vbdate($vbulletin->options['timeformat'], TIMENOW);

        // initialize thread / forum update clauses
        $forumupdate = false;

        $threadman =& datamanager_init('Thread', $vbulletin, ERRTYPE_SILENT, 'threadpost');
        $threadman->set_existing($threadinfo);

        if ($can_update_thread AND $edit['title'] != '')
        {
            // need to update thread title and iconid
            if (!can_moderate($threadinfo['forumid']))
            {
                $threadman->set_info('skip_moderator_log', true);
            }

            $threadman->set_info('skip_first_post_update', true);

            if ($edit['title'] != $postinfo['title'])
            {
                $threadman->set('title', unhtmlspecialchars($edit['title']));
            }

            $threadman->set('iconid', $edit['iconid']);

            if ($vbulletin->GPC_exists['prefixid'])
            {
                $threadman->set('prefixid', $vbulletin->GPC['prefixid']);
                if ($threadman->thread['prefixid'] === '' AND ($foruminfo['options'] & $vbulletin->bf_misc_forumoptions['prefixrequired']))
                {
                    // the prefix wasn't valid or was set to an empty one, but that's not allowed
                    $threadman->do_unset('prefixid');
                }
            }

            // do we need to update the forum counters?
            $forumupdate = ($foruminfo['lastthreadid'] == $threadinfo['threadid']) ? true : false;
        }

        // can this user open/close this thread if they want to?
        if ($vbulletin->GPC['openclose'] AND (($threadinfo['postuserid'] != 0 AND $threadinfo['postuserid'] == $vbulletin->userinfo['userid'] AND $forumperms & $vbulletin->bf_ugp_forumpermissions['canopenclose']) OR can_moderate($threadinfo['forumid'], 'canopenclose')))
        {
            $threadman->set('open', ($threadman->fetch_field('open') == 1 ? 0 : 1));
        }
        if ($vbulletin->GPC['stickunstick'] AND can_moderate($threadinfo['forumid'], 'canmanagethreads'))
        {
            $threadman->set('sticky', ($threadman->fetch_field('sticky') == 1 ? 0 : 1));
        }

        $threadman->save();

        // if this is a mod edit, then log it
        if ($vbulletin->userinfo['userid'] != $postinfo['userid'] AND can_moderate($threadinfo['forumid'], 'caneditposts'))
        {
            $modlog = array(
                'threadid' => $threadinfo['threadid'],
                'forumid' => $threadinfo['forumid'],
                'postid'   => $postinfo['postid']
            );
            log_moderator_action($modlog, 'post_x_edited', $postinfo['title']);
        }

        require_once(DIR . '/includes/functions_databuild.php');

        // do forum update if necessary
        if ($forumupdate)
        {
            build_forum_counters($threadinfo['forumid']);
        }

        // don't do thread subscriptions if we are using AJAX
        if (!$vbulletin->GPC['ajax'])
        {
            // ### DO THREAD SUBSCRIPTION ###
            // We use $postinfo[userid] so that we update the user who posted this, not the user who is editing this
            if (!$threadinfo['issubscribed'] AND $edit['emailupdate'] != 9999)
            {
                // user is not subscribed to this thread so insert it
                /*insert query*/
                $db->query_write("
                    REPLACE INTO " . TABLE_PREFIX . "subscribethread (userid, threadid, emailupdate, folderid, canview)
                    VALUES ($postinfo[userid], $threadinfo[threadid], $edit[emailupdate], $edit[folderid], 1)
                ");
            }
            else
            { // User is subscribed, see if they changed the settings for this thread
                if ($edit['emailupdate'] == 9999)
                {
                    // Remove this subscription, user chose 'No Subscription'
                    /*insert query*/
                    $db->query_write("
                        DELETE FROM " . TABLE_PREFIX . "subscribethread
                        WHERE threadid = $threadinfo[threadid]
                            AND userid = $postinfo[userid]
                    ");
                }
                else if ($threadinfo['emailupdate'] != $edit['emailupdate'] OR $threadinfo['folderid'] != $edit['folderid'])
                {
                    // User changed the settings so update the current record
                    /*insert query*/
                    $db->query_write("
                        REPLACE INTO " . TABLE_PREFIX . "subscribethread (userid, threadid, emailupdate, folderid, canview)
                        VALUES ($postinfo[userid], $threadinfo[threadid], $edit[emailupdate], $edit[folderid], 1)
                    ");
                }
            }
        }

    }

    $new_post = get_post_from_id($postinfo['postid']);
    
    if (defined('NOSHUTDOWNFUNC'))
    {
        exec_shut_down();
    }

    return new xmlrpcresp(new xmlrpcval($new_post, 'struct'));
}
