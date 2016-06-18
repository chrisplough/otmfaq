<?php

defined('IN_MOBIQUO') or exit;

function mobiquo_build_new_post($type = 'thread', $foruminfo, $threadinfo, $postinfo, &$post, &$errors)
{
    //NOTE: permissions are not checked in this function

    // $post is passed by reference, so that any changes (wordwrap, censor, etc) here are reflected on the copy outside the function
    // $post[] includes:
    // title, iconid, message, parseurl, email, signature, preview, disablesmilies, rating
    // $errors will become any error messages that come from the checks before preview kicks in
    global $vbulletin, $vbphrase, $forumperms;

    // ### PREPARE OPTIONS AND CHECK VALID INPUT ###
    $post['disablesmilies'] = intval($post['disablesmilies']);
    $post['enablesmilies'] = ($post['disablesmilies'] ?  0 : 1);
    $post['folderid'] = intval($post['folderid']);
    $post['emailupdate'] = intval($post['emailupdate']);
    $post['rating'] = intval($post['rating']);
    $post['podcastsize'] = intval($post['podcastsize']);
    /*$post['parseurl'] = intval($post['parseurl']);
     $post['email'] = intval($post['email']);
     $post['signature'] = intval($post['signature']);
     $post['preview'] = iif($post['preview'], 1, 0);
     $post['iconid'] = intval($post['iconid']);
     $post['message'] = trim($post['message']);
     $post['title'] = trim(preg_replace('/&#0*32;/', ' ', $post['title']));
     $post['username'] = trim($post['username']);
     $post['posthash'] = trim($post['posthash']);
     $post['poststarttime'] = trim($post['poststarttime']);*/

    // Make sure the posthash is valid

    // OTHER SANITY CHECKS
    $threadinfo['threadid'] = intval($threadinfo['threadid']);

    // create data manager
    if ($type == 'thread')
    {
        $dataman =& datamanager_init('Thread_FirstPost', $vbulletin, ERRTYPE_ARRAY, 'threadpost');
        $dataman->set('prefixid', $post['prefixid']);
    }
    else
    {
        $dataman =& datamanager_init('Post', $vbulletin, ERRTYPE_ARRAY, 'threadpost');
    }

    // set info
    $dataman->set_info('preview', $post['preview']);
    $dataman->set_info('parseurl', $post['parseurl']);
    $dataman->set_info('posthash', $post['posthash']);
    $dataman->set_info('forum', $foruminfo);
    $dataman->set_info('thread', $threadinfo);
    if (!$vbulletin->GPC['fromquickreply'])
    {
        $dataman->set_info('show_title_error', true);
    }
    if ($foruminfo['podcast'] AND (!empty($post['podcasturl']) OR !empty($post['podcastexplicit']) OR !empty($post['podcastauthor']) OR !empty($post['podcastsubtitle']) OR !empty($post['podcastkeywords'])))
    {
        $dataman->set_info('podcastexplicit', $post['podcastexplicit']);
        $dataman->set_info('podcastauthor', $post['podcastauthor']);
        $dataman->set_info('podcastkeywords', $post['podcastkeywords']);
        $dataman->set_info('podcastsubtitle', $post['podcastsubtitle']);
        $dataman->set_info('podcasturl', $post['podcasturl']);
        if ($post['podcastsize'])
        {
            $dataman->set_info('podcastsize', $post['podcastsize']);
        }
    }

    // set options
    $dataman->setr('showsignature', $post['signature']);
    $dataman->setr('allowsmilie', $post['enablesmilies']);

    // set data
    $dataman->setr('userid', $vbulletin->userinfo['userid']);
    if ($vbulletin->userinfo['userid'] == 0)
    {
        $dataman->setr('username', $post['username']);
    }
    $dataman->setr('title', $post['title']);
    $dataman->setr('pagetext', $post['message']);
    $dataman->setr('iconid', $post['iconid']);



    // see if post has to be moderated or if poster in a mod
    if (
    ((
    (
    ($foruminfo['moderatenewthread'] AND $type == 'thread') OR ($foruminfo['moderatenewpost'] AND $type == 'reply')
    )
    OR !($forumperms & $vbulletin->bf_ugp_forumpermissions['followforummoderation'])
    )
    AND !can_moderate($foruminfo['forumid']))
    OR
    ($type == 'reply' AND (($postinfo['postid'] AND !$postinfo['visible'] AND !empty($postinfo['specifiedpost'])) OR !$threadinfo['visible']))
    )
    {
        // note: specified post comes from a variable passed into newreply.php
        $dataman->set('visible', 0);
        $post['visible'] = 0;
    }
    else
    {
        $dataman->set('visible', 1);
        $post['visible'] = 1;
    }

    if ($type != 'thread')
    {
        if ($postinfo['postid'] == 0)
        {
            // get parentid of the new post
            // we're not posting a new thread, so make this post a child of the first post in the thread
            $getfirstpost = $vbulletin->db->query_first("SELECT postid FROM " . TABLE_PREFIX . "post WHERE threadid=$threadinfo[threadid] ORDER BY dateline LIMIT 1");
            $parentid = $getfirstpost['postid'];
        }
        else
        {
            $parentid = $postinfo['postid'];
        }
        $dataman->setr('parentid', $parentid);
        $dataman->setr('threadid', $threadinfo['threadid']);
    }
    else
    {
        $dataman->setr('forumid', $foruminfo['forumid']);
    }

    $errors = array();

    // done!
    ($hook = vBulletinHook::fetch_hook('newpost_process')) ? eval($hook) : false;

    if ($vbulletin->GPC['fromquickreply'] AND $post['preview'])
    {
        $errors = array();
        return;
    }

    if ($vbulletin->options['hvcheck_post'] AND !$post['preview'] AND !$vbulletin->userinfo['userid'])
    {
        require_once(DIR . '/includes/class_humanverify.php');
        
        $verify =& vB_HumanVerify::fetch_library($vbulletin);
        if (!$verify->verify_token($post['humanverify']))
        {
            $dataman->error($verify->fetch_error());
        }
    }

    if ($dataman->info['podcastsize'])
    {
        $post['podcastsize'] = $dataman->info['podcastsize'];
    }

    // check if this forum requires a prefix
    if ($type == 'thread' AND !$dataman->fetch_field('prefixid') AND ($foruminfo['options'] & $vbulletin->bf_misc_forumoptions['prefixrequired']))
    {
        // only require a prefix if we actually have options for this forum
        require_once(DIR . '/includes/functions_prefix.php');
        
        if (fetch_prefix_array($foruminfo['forumid']))
        {
            $dataman->error('thread_prefix_required');
        }
    }

    if ($type == 'thread' AND $post['taglist'])
    {
        fetch_valid_tags($dataman->thread, $post['taglist'], $tag_errors, true, false);
        if ($tag_errors)
        {
            foreach ($tag_errors AS $error)
            {
                $dataman->error($error);
            }
        }
    }

    $dataman->pre_save();

    $errors = array_merge($errors, $dataman->errors);

    if ($post['preview'])
    {
        return;
    }


    // ### DUPE CHECK ###
    $dupehash = md5($foruminfo['forumid'] . $post['title'] . $post['message'] . $vbulletin->userinfo['userid'] . $type);
    $prevpostfound = false;
    $prevpostthreadid = 0;

    if ($prevpost = $vbulletin->db->query_first("
        SELECT posthash.threadid
        FROM " . TABLE_PREFIX . "posthash AS posthash
        WHERE posthash.userid = " . $vbulletin->userinfo['userid'] . " AND
            posthash.dupehash = '" . $vbulletin->db->escape_string($dupehash) . "' AND
            posthash.dateline > " . (TIMENOW - 300) . "
    "))
    {
        if (($type == 'thread' AND $prevpost['threadid'] == 0) OR ($type == 'reply' AND $prevpost['threadid'] == $threadinfo['threadid']))
        {
            $prevpostfound = true;
            $prevpostthreadid = $prevpost['threadid'];
        }
    }


    // Redirect user to forumdisplay since this is a duplicate post
    if ($prevpostfound)
    {
        if ($type == 'thread')
        {


            $return = array(19, 'duplicate create/reply thread error, or the system restrict creating multiple thread within short time-frame');
            return_fault($return);
        }
        else
        {
            // with ajax quick reply we need to use the error system
            if ($vbulletin->GPC['ajax'])
            {
                $dataman->error('duplicate_post');
                $errors = $dataman->errors;
                return;
            }
            else
            {
                $vbulletin->url = 'showthread.php?' . $vbulletin->session->vars['sessionurl'] . "t=$prevpostthreadid&goto=newpost";
                if ($post['ajaxqrfailed'])
                {

                    $return = array(19, 'duplicate create/reply thread error, or the system restrict creating multiple thread within short time-frame');
                    return_fault($return);
                }
                else
                {

                    $return = array(19, 'duplicate create/reply thread error, or the system restrict creating multiple thread within short time-frame');
                    return_fault($return);
                }
            }
        }
    }

    if (sizeof($errors) > 0)
    {
        return;
    }

    $id = $dataman->save();
    if ($type == 'thread')
    {
        $post['threadid'] = $id;
        $threadinfo =& $dataman->thread;
        $post['postid'] = $dataman->fetch_field('firstpostid');
    }
    else
    {
        $post['postid'] = $id;
    }
    $post['visible'] = $dataman->fetch_field('visible');

    $set_open_status = false;
    $set_sticky_status = false;
    if ($vbulletin->GPC['openclose'] AND (($threadinfo['postuserid'] != 0 AND $threadinfo['postuserid'] == $vbulletin->userinfo['userid'] AND $forumperms & $vbulletin->bf_ugp_forumpermissions['canopenclose']) OR can_moderate($threadinfo['forumid'], 'canopenclose')))
    {
        $set_open_status = true;
    }
    if ($vbulletin->GPC['stickunstick'] AND can_moderate($threadinfo['forumid'], 'canmanagethreads'))
    {
        $set_sticky_status = true;
    }

    if ($set_open_status OR $set_sticky_status)
    {
        $thread =& datamanager_init('Thread', $vbulletin, ERRTYPE_SILENT, 'threadpost');
        if ($type == 'thread')
        {
            $thread->set_existing($dataman->thread);
            if ($set_open_status)
            {
                $post['postpoll'] = false;
            }
        }
        else
        {
            $thread->set_existing($threadinfo);
        }

        if ($set_open_status)
        {
            $thread->set('open', ($thread->fetch_field('open') == 1 ? 0 : 1));
        }
        if ($set_sticky_status)
        {
            $thread->set('sticky', ($thread->fetch_field('sticky') == 1 ? 0 : 1));
        }

        $thread->save();
    }

    if ($type == 'thread')
    {
        //add_tags_to_thread($threadinfo, $post['taglist']);
    }

    // ### DO THREAD RATING ###
    build_thread_rating($post['rating'], $foruminfo, $threadinfo);

    // ### DO EMAIL NOTIFICATION ###
    if ($post['visible'] AND $type != 'thread' AND !in_coventry($vbulletin->userinfo['userid'], true)) // AND !$prevpostfound (removed as redundant - bug #22935)
    {
        exec_send_notification($threadinfo['threadid'], $vbulletin->userinfo['userid'], $post['postid']);
    }

    // ### DO THREAD SUBSCRIPTION ###
    if ($vbulletin->userinfo['userid'] != 0)
    {
        require_once(DIR . '/includes/functions_misc.php');
        
        $post['emailupdate'] = verify_subscription_choice($post['emailupdate'], $vbulletin->userinfo, 9999);

        ($hook = vBulletinHook::fetch_hook('newpost_subscribe')) ? eval($hook) : false;

        if (!$threadinfo['issubscribed'] AND $post['emailupdate'] != 9999)
        { // user is not subscribed to this thread so insert it
            /*insert query*/
            $vbulletin->db->query_write("INSERT IGNORE INTO " . TABLE_PREFIX . "subscribethread (userid, threadid, emailupdate, folderid, canview)
                    VALUES (" . $vbulletin->userinfo['userid'] . ", $threadinfo[threadid], $post[emailupdate], $post[folderid], 1)");
        }
        else
        { // User is subscribed, see if they changed the settings for this thread
            if ($post['emailupdate'] == 9999)
            {    // Remove this subscription, user chose 'No Subscription'
                $vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "subscribethread WHERE threadid = $threadinfo[threadid] AND userid = " . $vbulletin->userinfo['userid']);
            }
            else if ($threadinfo['emailupdate'] != $post['emailupdate'] OR $threadinfo['folderid'] != $post['folderid'])
            {
                // User changed the settings so update the current record
                /*insert query*/
                $vbulletin->db->query_write("REPLACE INTO " . TABLE_PREFIX . "subscribethread (userid, threadid, emailupdate, folderid, canview)
                    VALUES (" . $vbulletin->userinfo['userid'] . ", $threadinfo[threadid], $post[emailupdate], $post[folderid], 1)");
            }
        }
    }

    //($hook = vBulletinHook::fetch_hook('newpost_complete')) ? eval($hook) : false;
}
