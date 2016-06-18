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
require_once(CWD1. '/include/function_text_parse.php');

define('THIS_SCRIPT', 'showthread');
define('CSRF_PROTECTION', false);
$phrasegroups = array(
    'posting',
    'postbit',
    'showthread',
    'inlinemod',
    'reputationlevel'
);

$specialtemplates = array(
    'smiliecache',
    'bbcodecache',
    'mailqueue',
    'bookmarksitecache',
);

$globaltemplates = array(
    'ad_showthread_beforeqr',
    'ad_showthread_firstpost',
    'ad_showthread_firstpost_start',
    'ad_showthread_firstpost_sig',
    'forumdisplay_loggedinuser',
    'forumrules',
    'im_aim',
    'im_icq',
    'im_msn',
    'im_yahoo',
    'im_skype',
    'postbit',
    'postbit_wrapper',
    'postbit_attachment',
    'postbit_attachmentimage',
    'postbit_attachmentthumbnail',
    'postbit_attachmentmoderated',
    'postbit_deleted',
    'postbit_ignore',
    'postbit_ignore_global',
    'postbit_ip',
    'postbit_onlinestatus',
    'postbit_reputation',
    'bbcode_code',
    'bbcode_html',
    'bbcode_php',
    'bbcode_quote',
    'SHOWTHREAD',
    'showthread_list',
    'showthread_similarthreadbit',
    'showthread_similarthreads',
    'showthread_quickreply',
    'showthread_bookmarksite',
    'tagbit',
    'tagbit_wrapper',
    'polloptions_table',
    'polloption',
    'polloption_multiple',
    'pollresults_table',
    'pollresult',
    'threadadmin_imod_menu_post',
    'editor_css',
    'editor_clientscript',
    'editor_jsoptions_font',
    'editor_jsoptions_size',
);
$actiontemplates = array();

require_once('./global.php');
require_once(DIR . '/includes/functions_bigthree.php');
require_once(DIR . '/includes/class_postbit.php');
require_once(DIR . '/includes/html_color_names.php');


foreach($html_color_names as $c_name => $c_code)
{
    $c_code = strtolower($c_code);
    $color_names["[COLOR=#$c_code]"] = "[COLOR=$c_name]";
}


function get_thread_func($xmlrpc_params)
{
    global $db, $vbulletin, $vbphrase, $html_content;

    $params = php_xmlrpc_decode($xmlrpc_params);
    
    $threadid = intval($params[0]);
    if(empty($threadid)) return_fault(fetch_error('invalidid', $vbphrase['thread']));
    list($start, $perpage, $page) = process_page($params[1], $params[2]);
    $html_content = isset($params[3]) && $params[3];
    
    return get_thread_content($threadid, $start, $perpage, 0);
}

function get_thread_by_unread_func($xmlrpc_params)
{
    global $db, $vbulletin, $vbphrase, $html_content;

    $params = php_xmlrpc_decode($xmlrpc_params);
    
    $threadid = intval($params[0]);
    if(empty($threadid)) return_fault(fetch_error('invalidid', $vbphrase['thread']));
    $perpage = empty($params[1]) ? 20 : $params[1];
    $html_content = isset($params[2]) && $params[2];
    
    return get_thread_content($threadid, 0, $perpage, 'unread');
}

function get_thread_by_post_func($xmlrpc_params)
{
    global $db, $vbulletin, $html_content;
    
    $params = php_xmlrpc_decode($xmlrpc_params);
    
    $postid = intval($params[0]);
    if(empty($postid)) return_fault(fetch_error('invalidid', $vbphrase['post']));
    
    $postinfo = mobiquo_verify_id('post', $postid, 1, 1);
    if(!is_array($postinfo)) return_fault(fetch_error('invalidid', $vbphrase['post']));
    $threadid = $postinfo['threadid'];
    
    $perpage = empty($params[1]) ? 20 : $params[1];
    $html_content = isset($params[2]) && $params[2];
    
    return get_thread_content($threadid, 0, $perpage, $postid);
}


function get_thread_content($threadid, $start, $perpage, $postid = 0)
{
    global $db, $vbulletin, $vbphrase, $html_content;
    
    // ******************************************************************* thread infor process
    $thread = mobiquo_verify_id('thread', $threadid, 1, 1);
    if(!is_array($thread)) return_fault(fetch_error('invalidid', $vbphrase['thread']));
    $threadinfo =& $thread;
    
    // jump page if thread is actually a redirect
    if ($thread['open'] == 10)
    {
        $thread = fetch_threadinfo($threadinfo['pollid']);
    }
    
    // check for visible / deleted thread
    if (((!$thread['visible'] AND !can_moderate($thread['forumid'], 'canmoderateposts'))) OR ($thread['isdeleted'] AND !can_moderate($thread['forumid'])))
    {
        return_fault(fetch_error('invalidid', $vbphrase['thread']));
    }
    
    // Tachy goes to coventry
    if (in_coventry($thread['postuserid']) AND !can_moderate($thread['forumid']))
    {
        return_fault(fetch_error('invalidid', $vbphrase['thread']));
    }
    
    // do word wrapping for the thread title
    if ($vbulletin->options['wordwrap'] != 0)
    {
        $thread['title'] = fetch_word_wrapped_string($thread['title']);
    }

    $thread['title'] = fetch_censored_text($thread['title']);
    
    
    // ******************************************************************* forum infor process
    // get forum info
    $forum = fetch_foruminfo($thread['forumid']);
    $foruminfo =& $forum;
    
    // check forum permissions
    $forumperms = fetch_permissions($thread['forumid']);
    if (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canview']) OR !($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads']))
    {
        return_fault();
    }
    
    if (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewothers']) AND ($thread['postuserid'] != $vbulletin->userinfo['userid'] OR $vbulletin->userinfo['userid'] == 0))
    {
        return_fault();
    }
    
    // check if there is a forum password and if so, ensure the user has it set
    if (!verify_forum_password($foruminfo['forumid'], $foruminfo['password'], false))
    {
        return_fault('Your administrator has required a password to access this forum.');
    }
    
    // get last thread view time
    if ($vbulletin->options['threadmarking'] AND $vbulletin->userinfo['userid'])
    {
        $threadview = max($threadinfo['threadread'], $threadinfo['forumread'], TIMENOW - ($vbulletin->options['markinglimit'] * 86400));
    }
    else
    {
        $threadview = intval(fetch_bbarray_cookie('thread_lastview', $thread['threadid']));
        if (!$threadview)
        {
            $threadview = $vbulletin->userinfo['lastvisit'];
        }
    }
    $threadinfo['threadview'] = intval($threadview);
    
    
    $show_unapprovedpost = (can_moderate($threadinfo['forumid'], 'canmoderateposts')) ? true : false;
    $show_deletedpost = $forumperms & $vbulletin->bf_ugp_forumpermissions['canseedelnotice'] OR can_moderate($threadinfo['forumid']) ? true : false;
    $coventry = can_moderate($thread['forumid']) ? '' : fetch_coventry('string');
    
    // consider unapproved/soft-deleted/global-ignored-users' posts
    $query_where = " threadid = $threadinfo[threadid]
                    AND visible IN (1" . ($show_deletedpost ? ",2" : "") . ($show_unapprovedpost ? ",0" : "") . ")
                    " . ($coventry ? " AND userid NOT IN ($coventry)" : "");
    
    $totalpostsnum = $db->query_first("
        SELECT COUNT(*) AS found_rows
        FROM " . TABLE_PREFIX . "post
        WHERE $query_where"
    );
    
    $totalposts = $totalpostsnum['found_rows'];
    
    if ($postid === 'unread')
    {
        $firstunreadpost = $db->query_first("
            SELECT MIN(postid) AS postid
            FROM " . TABLE_PREFIX . "post
            WHERE $query_where" . "
                AND dateline > $threadview"
        );
    
        if ($firstunreadpost['postid']) {
            $postid = $firstunreadpost['postid'];
        } else {
            $postid = $threadinfo['lastpostid'];
        }
    }
    
    if ($postid > 0)
    {
        $readpostsnum = $db->query_first("
            SELECT COUNT(*) AS found_rows
            FROM " . TABLE_PREFIX . "post
            WHERE $query_where" . "
                AND postid <= $postid"
        );
    
        $pagenumber = ceil($readpostsnum['found_rows'] / $perpage);
        $start = ($pagenumber - 1) * $perpage;
    }

    // get ignored users
    $ignore = array();
    if (trim($vbulletin->userinfo['ignorelist']))
    {
        $ignorelist = preg_split('/( )+/', trim($vbulletin->userinfo['ignorelist']), -1, PREG_SPLIT_NO_EMPTY);
        foreach ($ignorelist AS $ignoreuserid)
        {
            $ignore["$ignoreuserid"] = 1;
        }
    }
    DEVDEBUG('ignored users: ' . implode(', ', array_keys($ignore)));

    // filter out deletion notices if can't be seen
    $deljoin = $show_deletedpost ? "LEFT JOIN " . TABLE_PREFIX . "deletionlog AS deletionlog ON(post.postid = deletionlog.primaryid AND deletionlog.type = 'post')" : '';

    $show['viewpost'] = (can_moderate($threadinfo['forumid'])) ? true : false;
    $show['managepost'] = iif(can_moderate($threadinfo['forumid'], 'candeleteposts') OR can_moderate($threadinfo['forumid'], 'canremoveposts'), true, false);
    $show['approvepost'] = (can_moderate($threadinfo['forumid'], 'canmoderateposts')) ? true : false;
    $show['managethread'] = (can_moderate($threadinfo['forumid'], 'canmanagethreads')) ? true : false;
    $show['inlinemod'] = (!$show['threadedmode'] AND ($show['managethread'] OR $show['managepost'] OR $show['approvepost'])) ? true : false;

    // update views counter
    if ($vbulletin->options['threadviewslive'])
    {
        // doing it as they happen; for optimization purposes, this cannot use a DM!
        $db->shutdown_query("
            UPDATE " . TABLE_PREFIX . "thread
            SET views = views + 1
            WHERE threadid = " . intval($threadinfo['threadid'])
        );
    }
    else
    {
        // or doing it once an hour
        $db->shutdown_query("
            INSERT INTO " . TABLE_PREFIX . "threadviews (threadid)
            VALUES (" . intval($threadinfo['threadid']) . ')'
        );
    }

    $displayed_dateline = 0;

    ################################################################################
    ############################### SHOW POLL ######################################
    ################################################################################
    $poll = '';
    $counter = 0;
    if ($thread['pollid'])
    {
        $pollbits = '';
        $counter = 1;
        $pollid = $thread['pollid'];

        $show['editpoll'] = iif(can_moderate($threadinfo['forumid'], 'caneditpoll'), true, false);

        // get poll info
        $pollinfo = $db->query_first_slave("
            SELECT *
            FROM " . TABLE_PREFIX . "poll
            WHERE pollid = $pollid
        ");
        
        require_once(DIR . '/includes/class_bbcode.php');
        
        $bbcode_parser =& new vB_BbCodeParser($vbulletin, fetch_tag_list());

        $pollinfo['question'] = $bbcode_parser->parse(unhtmlspecialchars($pollinfo['question']), $forum['forumid'], true);

        $splitoptions = explode('|||', $pollinfo['options']);
        $splitvotes = explode('|||', $pollinfo['votes']);

        $showresults = 0;
        $uservoted = 0;
        if (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canvote']))
        {
            $nopermission = 1;
        }

        if (!$pollinfo['active'] OR !$thread['open'] OR ($pollinfo['dateline'] + ($pollinfo['timeout'] * 86400) < TIMENOW AND $pollinfo['timeout'] != 0) OR $nopermission)
        {
            //thread/poll is closed, ie show results no matter what
            $showresults = 1;
        }
        else
        {
            //get userid, check if user already voted
            $voted = intval(fetch_bbarray_cookie('poll_voted', $pollid));
            if ($voted)
            {
                $uservoted = 1;
            }
        }

        if ($pollinfo['timeout'] AND !$showresults)
        {
            $pollendtime = vbdate($vbulletin->options['timeformat'], $pollinfo['dateline'] + ($pollinfo['timeout'] * 86400));
            $pollenddate = vbdate($vbulletin->options['dateformat'], $pollinfo['dateline'] + ($pollinfo['timeout'] * 86400));
            $show['pollenddate'] = true;
        }
        else
        {
            $show['pollenddate'] = false;
        }

        foreach ($splitvotes AS $index => $value)
        {
            $pollinfo['numbervotes'] += $value;
        }

        if ($vbulletin->userinfo['userid'] > 0)
        {
            $pollvotes = $db->query_read_slave("
            SELECT voteoption
            FROM " . TABLE_PREFIX . "pollvote
            WHERE userid = " . $vbulletin->userinfo['userid'] . " AND pollid = $pollid
        ");
            if ($db->num_rows($pollvotes) > 0)
            {
                $uservoted = 1;
            }
        }

        if ($showresults OR $uservoted)
        {
            if ($uservoted)
            {
                $uservote = array();
                while ($pollvote = $db->fetch_array($pollvotes))
                {
                    $uservote["$pollvote[voteoption]"] = 1;
                }
            }
        }

        $option['open'] = $stylevar['left'][0];
        $option['close'] = $stylevar['right'][0];

        foreach ($splitvotes AS $index => $value)
        {
            $arrayindex = $index + 1;
            $option['uservote'] = iif($uservote["$arrayindex"], true, false);
            $option['question'] = $bbcode_parser->parse($splitoptions["$index"], $forum['forumid'], true);

            // public link
            if ($pollinfo['public'] AND $value)
            {
                $option['votes'] = '<a href="poll.php?' . $vbulletin->session->vars['sessionurl'] . 'do=showresults&amp;pollid=' . $pollinfo['pollid'] . '">' . vb_number_format($value) . '</a>';
            }
            else
            {
                $option['votes'] = vb_number_format($value);   //get the vote count for the option
            }

            $option['number'] = $counter;  //number of the option

            //Now we check if the user has voted or not
            if ($showresults OR $uservoted)
            { // user did vote or poll is closed

                if ($value <= 0)
                {
                    $option['percent'] = 0;
                }
                else if ($pollinfo['multiple'])
                {
                    $option['percent'] = vb_number_format(($value < $pollinfo['voters']) ? $value / $pollinfo['voters'] * 100 : 100, 2);
                }
                else
                {
                    $option['percent'] = vb_number_format(($value < $pollinfo['numbervotes']) ? $value / $pollinfo['numbervotes'] * 100 : 100, 2);
                }

                $option['graphicnumber'] = $option['number'] % 6 + 1;
                $option['barnumber'] = round($option['percent']) * 2;
                $option['remainder'] = 201 - $option['barnumber'];

                // Phrase parts below
                if ($nopermission)
                {
                    $pollstatus = $vbphrase['you_may_not_vote_on_this_poll'];
                }
                else if ($showresults)
                {
                    $pollstatus = $vbphrase['this_poll_is_closed'];
                }
                else if ($uservoted)
                {
                    $pollstatus = $vbphrase['you_have_already_voted_on_this_poll'];
                }

                //eval('$pollbits .= "' . fetch_template('pollresult') . '";');
            }
            else
            {
                if ($pollinfo['multiple'])
                {
                    //eval('$pollbits .= "' . fetch_template('polloption_multiple') . '";');
                }
                else
                {
                    //eval('$pollbits .= "' . fetch_template('polloption') . '";');
                }
            }
            $counter++;
        }

        if ($pollinfo['multiple'])
        {
            $pollinfo['numbervotes'] = $pollinfo['voters'];
            $show['multiple'] = true;
        }

        if ($pollinfo['public'])
        {
            $show['publicwarning'] = true;
        }
        else
        {
            $show['publicwarning'] = false;
        }

        $displayed_dateline = $threadinfo['lastpost'];

        if ($showresults OR $uservoted)
        {
            //eval('$poll = "' . fetch_template('pollresults_table') . '";');
        }
        else
        {
            //eval('$poll = "' . fetch_template('polloptions_table') . '";');
        }
    }


    // post is cachable if option is enabled, last post is newer than max age, and this user
    // isn't showing a sessionhash
    $post_cachable = (
    $vbulletin->options['cachemaxage'] > 0 AND
    (TIMENOW - ($vbulletin->options['cachemaxage'] * 60 * 60 * 24)) <= $thread['lastpost'] AND
    $vbulletin->session->vars['sessionurl'] == ''
    );

    // allow deleted posts to not be counted in number of posts displayed on the page;
    // prevents issue with page count on forum display being incorrect
    $ids = '';
    $ids2 = array();

    $getpostids = $db->query_read("
        SELECT postid
        FROM " . TABLE_PREFIX . "post
        WHERE $query_where
        ORDER BY dateline
        LIMIT $start, $perpage"
    );

    while ($post = $db->fetch_array($getpostids))
    {
        $ids .= ', ' . $post['postid'];
        $ids2[] = $post['postid'];
    }
    $db->free_result($getpostids);

    $postids = "post.postid IN (0" . $ids . ")";

    // load attachments
    if ($thread['attach'])
    {
        require_once(DIR . '/packages/vbattach/attach.php');
        $attach = new vB_Attach_Display_Content($vbulletin, 'vBForum_Post');
        $postattach = $attach->fetch_postattach(0, $ids2);
    }

    $posts = $db->query_read("
        SELECT
            post.*, post.username AS postusername, post.ipaddress AS ip, IF(post.visible = 2, 1, 0) AS isdeleted,
            user.*, userfield.*, usertextfield.*,
            " . iif($forum['allowicons'], 'icon.title as icontitle, icon.iconpath, ') . "
            " . iif($vbulletin->options['avatarenabled'], 'avatar.avatarpath, NOT ISNULL(customavatar.userid) AS hascustomavatar, customavatar.dateline AS avatardateline,customavatar.width AS avwidth,customavatar.height AS avheight, ') . "
            " . ((can_moderate($thread['forumid'], 'canmoderateposts') OR can_moderate($thread['forumid'], 'candeleteposts')) ? 'spamlog.postid AS spamlog_postid, ' : '') . "
            " . iif($deljoin, 'deletionlog.userid AS del_userid, deletionlog.username AS del_username, deletionlog.reason AS del_reason, ') . "
            editlog.userid AS edit_userid, editlog.username AS edit_username, editlog.dateline AS edit_dateline,
            editlog.reason AS edit_reason, editlog.hashistory,
            postparsed.pagetext_html, postparsed.hasimages,
            sigparsed.signatureparsed, sigparsed.hasimages AS sighasimages,
            sigpic.userid AS sigpic, sigpic.dateline AS sigpicdateline, sigpic.width AS sigpicwidth, sigpic.height AS sigpicheight,
            IF(displaygroupid=0, user.usergroupid, displaygroupid) AS displaygroupid, infractiongroupid
            " . iif(!($permissions['genericpermissions'] & $vbulletin->bf_ugp_genericpermissions['canseehiddencustomfields']), $vbulletin->profilefield['hidden']) . "
        FROM " . TABLE_PREFIX . "post AS post
        LEFT JOIN " . TABLE_PREFIX . "user AS user ON(user.userid = post.userid)
        LEFT JOIN " . TABLE_PREFIX . "userfield AS userfield ON(userfield.userid = user.userid)
        LEFT JOIN " . TABLE_PREFIX . "usertextfield AS usertextfield ON(usertextfield.userid = user.userid)
        " . iif($forum['allowicons'], "LEFT JOIN " . TABLE_PREFIX . "icon AS icon ON(icon.iconid = post.iconid)") . "
        " . iif($vbulletin->options['avatarenabled'], "LEFT JOIN " . TABLE_PREFIX . "avatar AS avatar ON(avatar.avatarid = user.avatarid) LEFT JOIN " . TABLE_PREFIX . "customavatar AS customavatar ON(customavatar.userid = user.userid)") . "
        " . ((can_moderate($thread['forumid'], 'canmoderateposts') OR can_moderate($thread['forumid'], 'candeleteposts')) ? "LEFT JOIN " . TABLE_PREFIX . "spamlog AS spamlog ON(spamlog.postid = post.postid)" : '') . "
        $deljoin
        LEFT JOIN " . TABLE_PREFIX . "editlog AS editlog ON(editlog.postid = post.postid)
        LEFT JOIN " . TABLE_PREFIX . "postparsed AS postparsed ON(postparsed.postid = post.postid AND postparsed.styleid = " . intval(STYLEID) . " AND postparsed.languageid = " . intval(LANGUAGEID) . ")
        LEFT JOIN " . TABLE_PREFIX . "sigparsed AS sigparsed ON(sigparsed.userid = user.userid AND sigparsed.styleid = " . intval(STYLEID) . " AND sigparsed.languageid = " . intval(LANGUAGEID) . ")
        LEFT JOIN " . TABLE_PREFIX . "sigpic AS sigpic ON(sigpic.userid = post.userid)
        WHERE $postids
        ORDER BY post.dateline
    ");

    if (!($forumperms & $vbulletin->bf_ugp_forumpermissions['cangetattachment']))
    {
        $vbulletin->options['viewattachedimages'] = 0;
        $vbulletin->options['attachthumbs'] = 0;
    }

    $postcount = $start;
    $count = 0;
    $postbits = '';

    $postbit_factory =& new vB_Postbit_Factory();
    $postbit_factory->registry =& $vbulletin;
    $postbit_factory->forum =& $foruminfo;
    $postbit_factory->thread =& $thread;
    $postbit_factory->cache = array();
    $postbit_factory->bbcode_parser =& new vB_BbCodeParser($vbulletin, fetch_tag_list());
    $show['deleteposts'] = can_moderate($threadinfo['forumid'], 'candeleteposts') ? true : false;
    $show['editthread'] = can_moderate($threadinfo['forumid'], 'caneditthreads') ? true : false;
    $show['movethread'] = (can_moderate($threadinfo['forumid'], 'canmanagethreads') OR ($forumperms & $vbulletin->bf_ugp_forumpermissions['canmove'] AND $threadinfo['postuserid'] == $vbulletin->userinfo['userid'])) ? true : false;
    $show['openclose'] = (can_moderate($threadinfo['forumid'], 'canopenclose') OR ($forumperms & $vbulletin->bf_ugp_forumpermissions['canopenclose'] AND $threadinfo['postuserid'] == $vbulletin->userinfo['userid'])) ? true : false;
    $show['moderatethread'] = (can_moderate($threadinfo['forumid'], 'canmoderateposts') ? true : false);
    $show['deletethread'] = (($threadinfo['visible'] != 2 AND can_moderate($threadinfo['forumid'], 'candeleteposts')) OR can_moderate($threadinfo['forumid'], 'canremoveposts') OR ($forumperms & $vbulletin->bf_ugp_forumpermissions['candeletepost'] AND $forumperms & $vbulletin->bf_ugp_forumpermissions['candeletethread'] AND $vbulletin->userinfo['userid'] == $threadinfo['postuserid'] AND ($vbulletin->options['edittimelimit'] == 0 OR $threadinfo['dateline'] > (TIMENOW - ($vbulletin->options['edittimelimit'] * 60))))) ? true : false;
    $show['adminoptions'] = ($show['editpoll'] OR $show['movethread'] OR $show['deleteposts'] OR $show['editthread'] OR $show['managethread'] OR $show['openclose'] OR $show['deletethread']) ? true : false;

    if($show['adminoptions'])
    {
        require_once(DIR . '/includes/adminfunctions.php');
        require_once(DIR . '/includes/functions_banning.php');
    }

    while ($post = $db->fetch_array($posts))
    {
        if ($tachyuser = in_coventry($post['userid']) AND !can_moderate($thread['forumid'])) {
            continue;
        }

        $post['postcount'] = ++$postcount;

        if($post['postid'] == $postid){
            $count = $postcount;
        }

        if ($post['visible'] == 2)
        {
            $fetchtype = 'post_deleted';
        }
        else if ($tachyuser)
        {
            $fetchtype = 'post_global_ignore';
        }
        else if ($ignore["$post[userid]"])
        {
            $fetchtype = 'post_ignore';
        }
        else 
        {
            $fetchtype = 'post';
        }

        $postbit_obj =& $postbit_factory->fetch_postbit($fetchtype);
        if ($fetchtype == 'post')
        {
            $postbit_obj->highlight =& $replacewords;
        }
        $postbit_obj->cachable = $post_cachable;

        //$post['islastshown'] = ($post['postid'] == $lastpostid);
        $post['attachments'] =& $postattach["$post[postid]"];

        $parsed_postcache = array('text' => '', 'images' => 1, 'skip' => false);

        $post['pagetext'] = mobiquo_handle_bbcode_attach($post['pagetext'],true, $post);
        $mobiquo_attachments = $post[attachments];
        $postbits .= $postbit_obj->construct_postbit($post);
        
        if ($fetchtype == 'post_deleted')
        {
            $delete_content = '';
            if ($post['title'] OR $show['messageicon'] OR $show['inlinemod']) {
                $delete_content = vB_Template_Runtime::parsePhrase("message_deleted_by_x", vB_Template_Runtime::linkBuild("member", $post, NULL, 'del_userid', 'del_username'), $post['del_username']);
            }
            
            if ($post['del_reason']) {
                $delete_content .= "\n".vB_Template_Runtime::parsePhrase("reason")."\n".$post['del_reason'];
            }
            
            if ($show['viewpost'])
                $post['pagetext'] = strip_tags($delete_content)."\n"."[spoiler]{$post[pagetext]}[/spoiler]";
            else
                $post['pagetext'] = strip_tags($delete_content);
        }
        
        $return_attachments = array();
        
        if(is_array($mobiquo_attachments))
        {
            foreach($mobiquo_attachments as $attach)
            {
                $attachment_url = $attachment_thumbnail_url = "";
                preg_match_all('/href=\"([^\s]+attachmentid='.$attach[attachmentid].'.+?)\"/',unhtmlspecialchars($post[imageattachmentlinks]), $image_attachment_matchs);
                preg_match_all('/href=\"([^\s]+attachmentid='.$attach[attachmentid].'.+?)\"/',unhtmlspecialchars($post[otherattachments]), $other_attachment_matchs);
                preg_match_all('/href=\"([^\s]+attachmentid='.$attach[attachmentid].'.+?)\".+img.+?src=\"(.+attachmentid='.$attach[attachmentid].'.+?)\"/s',unhtmlspecialchars($post[thumbnailattachments]), $thumbnail_attachment_matchs);
                preg_match_all('/src=\"([^\s]+attachmentid='.$attach[attachmentid].'.+?)\"/',unhtmlspecialchars($post[imageattachments]), $small_image_attachment_matchs);

                if (in_array(pathinfo($attach['filename'], PATHINFO_EXTENSION), array('gif', 'jpg', 'jpeg', 'jpe', 'png', 'bmp'))) {
                    $type = "image";
                } else {
                    $type = "other";
                }
                
                if($image_attachment_matchs[1][0]) {
                    $type = "image";
                    $attachment_url = $GLOBALS[vbulletin]->options[bburl].'/'.$image_attachment_matchs[1][0];
                }
                if($other_attachment_matchs[1][0]){
                    $type = "other";
                    $attachment_url = $GLOBALS[vbulletin]->options[bburl].'/'.$other_attachment_matchs[1][0];
                }
                if($small_image_attachment_matchs[1][0]) {
                    $type = "image";
                    $attachment_thumbnail_url= $GLOBALS[vbulletin]->options[bburl].'/'.$small_image_attachment_matchs[1][0];
                    $attachment_url = $GLOBALS[vbulletin]->options[bburl].'/'.$small_image_attachment_matchs[1][0];
                }
                if($thumbnail_attachment_matchs[1][0]){
                    $type = "image";

                    $attachment_url = $GLOBALS[vbulletin]->options[bburl].'/'.$thumbnail_attachment_matchs[1][0];
                    $attachment_thumbnail_url = $GLOBALS[vbulletin]->options[bburl].'/'.$thumbnail_attachment_matchs[2][0];
                }
                
                if(empty($attachment_url)){
                    $attachment_url = $GLOBALS[vbulletin]->options[bburl].'/'."attachment.php?attachmentid=".$attach[attachmentid];
                }

                $return_attachment = new xmlrpcval(array(
                    'filename'      => new xmlrpcval($attach['filename'], 'base64'),
                    'filesize'      => new xmlrpcval($attach['filesize'], 'int'),
                    'url'           => new xmlrpcval(unhtmlspecialchars($attachment_url), 'string'),
                    'thumbnail_url' => new xmlrpcval(unhtmlspecialchars($attachment_thumbnail_url), 'string'),
                    'content_type' => new xmlrpcval($type, 'string')
                ), 'struct');
                
                array_push($return_attachments, $return_attachment);
            }
        }
        
        if ($foruminfo['allowhtml'])
        {
            $post['pagetext']  = str_replace("\n", '<br />', $post['pagetext']);
            require_once(DIR . '/includes/class_wysiwygparser.php');
            $html_parser = new vB_WysiwygHtmlParser($vbulletin);
            
            if (method_exists($html_parser, 'parse_wysiwyg_html_to_bbcode')) {
                $post['pagetext'] = $html_parser->parse_wysiwyg_html_to_bbcode($post['pagetext'], $foruminfo['allowhtml']);
            } else if (method_exists($html_parser, 'parse')) {
                $post['pagetext'] = $html_parser->parse($post['pagetext'], $foruminfo['allowhtml']);
            }
        }
        
        if($html_content)
        {
            $a = fetch_tag_list();
            unset($a['option']['quote']);
            unset($a['no_option']['quote']);
            unset($a['option']['url']);
            unset($a['no_option']['url']);

            $vbulletin->options['wordwrap'] = 0;
            
            $post_content = post_content_clean($post['pagetext']);
            $post_content = preg_replace("/\[\/img\]/siU", '[/img1]', $post_content);
            $bbcode_parser =& new vB_BbCodeParser($vbulletin, $a, false);
            $post_content = $bbcode_parser->parse( $post_content, $thread['forumid'], false);
            $post_content = preg_replace("/\[\/img1\]/siU", '[/IMG]', $post_content);

            $post_content = htmlspecialchars_uni($post_content);
        }
        else
        {
            $post_content = post_content_clean($post['pagetext']);
        }
        
        // add spoiler for user ignored post
        if ($fetchtype == 'post_ignore')
        {
            $post_content = strip_tags(construct_phrase($vbphrase['message_hidden_x_on_ignore_list'], $post['postusername']))
                            . "[spoiler]{$post_content}[/spoiler]";
        }
        
        $post_content = mobiquo_encode($post_content, '', $html_content);
        
        if(SHORTENQUOTE == 1 && preg_match('/^(.*\[quote\])(.+)(\[\/quote\].*)$/si', $post_content))
        {
            $new_content = "";
            $segments = preg_split('/(\[quote\].+\[\/quote\])/isU', $post_content,-1, PREG_SPLIT_DELIM_CAPTURE);

            foreach($segments as $segment)
            {
                $short_quote = $segment;
                if(preg_match('/^(\[quote\])(.+)(\[\/quote\])$/si', $segment, $quote_matches)){
                    if(function_exists('mb_strlen') && function_exists('mb_substr')){
                        if(mb_strlen($quote_matches[2], 'UTF-8') > 170){
                            $short_quote = $quote_matches[1].mb_substr($quote_matches[2],0,150, 'UTF-8').$quote_matches[3];
                        }
                    }
                    else{
                        if(strlen($quote_matches[2]) > 170){
                            $short_quote = $quote_matches[1].substr($quote_matches[2],0,150).$quote_matches[3];
                        }
                    }
                    $new_content .= $short_quote;
                } else {
                    $new_content .= $segment;
                }
            }

            $post_content = $new_content;
        }
        $mobiquo_can_edit = false;
        if(isset($post['editlink']) AND strlen($post['editlink']) > 0){
            $mobiquo_can_edit = true;
        }

        $mobiquo_user_online = (fetch_online_status($post, false)) ? true : false;
        $is_deleted = false;
        if($post['visible'] == 2){
            $is_deleted = true;
        }
        $is_approved = true;
        if($post['visible'] == 0 or (!$thread['visible'] AND $post['postcount'] == 1)){
            $is_approved = false;
        }
        
        $userinfo = fetch_userinfo($post['userid']);
        cache_permissions($userinfo, false);
        
        if($show['adminoptions'])
        {
            $mobiquo_can_ban = true;
            if (!($vbulletin->userinfo['permissions']['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['cancontrolpanel'] OR can_moderate(0, 'canbanusers')))
            {
                $mobiquo_can_ban = false;
            }

            // check that user has permission to ban the person they want to ban
            if (!($vbulletin->userinfo['permissions']['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['cancontrolpanel']))
            {
                if (can_moderate(0, '', $userinfo['userid'], $userinfo['usergroupid'] . (trim($userinfo['membergroupids']) ? ", $userinfo[membergroupids]" : ''))
                OR $userinfo['permissions']['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['cancontrolpanel']
                OR $userinfo['permissions']['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['ismoderator']
                OR is_unalterable_user($userinfo['userid']))
                {
                    $mobiquo_can_ban = false;
                }
            } else {
                if ($userinfo['permissions']['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['cancontrolpanel']
                OR is_unalterable_user($userinfo['userid']))
                {
                    $mobiquo_can_ban = false;
                }
            }
        } else {
            $mobiquo_can_ban = false;
        }
        
        $mobiquo_is_ban = false;
        if(!($vbulletin->usergroupcache[$userinfo['usergroupid']]['genericoptions'] & $vbulletin->bf_ugp_genericoptions['isnotbannedgroup'])){
            $mobiquo_is_ban = true;
        }
        
        $return_post = array(
            'topic_id'          => new xmlrpcval($post['threadid'], 'string'),
            'post_id'           => new xmlrpcval($post['postid'], 'string'),
            'post_title'        => new xmlrpcval(mobiquo_encode($post['title']), 'base64'),
            'post_content'      => new xmlrpcval($post_content, 'base64'),
            'post_author_id'    => new xmlrpcval($post['userid'], 'string'),
            'post_author_name'  => new xmlrpcval(mobiquo_encode($post['postusername']), 'base64'),
            'post_time'         => new xmlrpcval(mobiquo_iso8601_encode($post['dateline']-$vbulletin->options['hourdiff'], $vbulletin->userinfo['tzoffset']), 'dateTime.iso8601'),
            'post_count'        => new xmlrpcval($post['postcount'], 'int'),
            'attachments'       => new xmlrpcval($return_attachments, 'array'),
            'time_string'       => new xmlrpcval(format_time_string($post['dateline']), 'base64'),
            
            'is_approved'       => new xmlrpcval($is_approved, 'boolean'),
            'allow_smilies'     => new xmlrpcval($post['allowsmilie'], 'boolean'),
        );
        
        if ($show['deleteposts'])  $return_post['can_delete']    = new xmlrpcval(true, 'boolean');
        if ($is_deleted)           $return_post['is_deleted']    = new xmlrpcval(true, 'boolean');
        if ($show['approvepost'])  $return_post['can_approve']   = new xmlrpcval(true, 'boolean');
        //if ($is_approved)          $return_post['is_approved']   = new xmlrpcval(true, 'boolean');
        if ($mobiquo_can_ban)      $return_post['can_ban']       = new xmlrpcval(true, 'boolean');
        if ($mobiquo_is_ban)       $return_post['is_ban']        = new xmlrpcval(true, 'boolean');
        if ($show['managethread']) $return_post['can_move']      = new xmlrpcval(true, 'boolean');
        if ($mobiquo_can_edit)     $return_post['can_edit']      = new xmlrpcval(true, 'boolean');
        if ($mobiquo_user_online)  $return_post['is_online']     = new xmlrpcval(true, 'boolean');
        //if ($post['allowsmilie'])  $return_post['allow_smilies'] = new xmlrpcval(true, 'boolean');
        
        
        $return_post['icon_url'] = new xmlrpcval('', 'string');
        if($post['avatarurl']){
            $return_post['icon_url']=new xmlrpcval(get_icon_real_url($post['avatarurl']), 'string');
        }
        $return_post['attachment_authority'] = new xmlrpcval(0, 'int');
        if(!($forumperms & $vbulletin->bf_ugp_forumpermissions['cangetattachment'])){
            $return_post['attachment_authority'] = new xmlrpcval(4, 'int');
        }
        
        if (isset($vbulletin->products['post_thanks']) && $vbulletin->products['post_thanks'])
        {
            require_once(DIR . '/includes/functions_post_thanks.php');
            $thanks = fetch_thanks($post['postid'], '', true);
            if (!post_thanks_off($threadinfo['forumid'], $post, $threadinfo['firstpostid']) && can_thank_this_post($post, $threadinfo['isdeleted'], false) && !thanked_already($post))
            {
                $return_post['can_thank'] = new xmlrpcval(true, 'boolean');
            }
            
            if (!empty($thanks))
            {
                $thank_list = array();
                foreach ($thanks as $thank)
                {
                    $thank_list[] = new xmlrpcval(array(
                        'userid'    => new xmlrpcval($thank['userid'], 'string'),
                        'username'  => new xmlrpcval(mobiquo_encode($thank['username']), 'base64'),
                    ), 'struct');
                }
                
                $return_post['thanks_info'] = new xmlrpcval($thank_list, 'array');
            }
        }
        
        $xmlrpc_return_post = new xmlrpcval( $return_post, 'struct');
        $return_posts_list[] =$xmlrpc_return_post;
        // get first and last post ids for this page (for big reply buttons)
        if (!isset($FIRSTPOSTID))
        {
            $FIRSTPOSTID = $post['postid'];
        }
        $LASTPOSTID = $post['postid'];

        if ($post['dateline'] > $displayed_dateline)
        {
            $displayed_dateline = $post['dateline'];
            if ($displayed_dateline <= $threadview)
            {
                $updatethreadcookie = true;
            }
        }
    }
    
    if ($thread['pollid'] AND $vbulletin->options['updatelastpost'] AND ($displayed_dateline == $thread['lastpost'] OR $threadview == $thread['lastpost']) AND $pollinfo['lastvote'] > $thread['lastpost'])
    {
        $displayed_dateline = $pollinfo['lastvote'];
    }

    if ((!$vbulletin->GPC['posted'] OR $updatethreadcookie) AND $displayed_dateline AND $displayed_dateline > $threadview)
    {
        mark_thread_read($threadinfo, $foruminfo, $vbulletin->userinfo['userid'], $displayed_dateline);
    }

    if (defined('NOSHUTDOWNFUNC'))
    {
        exec_shut_down();
    }
    
    $mobiquo_can_upload = false;
    if ($forumperms & $vbulletin->bf_ugp_forumpermissions['canpostattachment'] AND $vbulletin->userinfo['userid'] AND !empty($vbulletin->userinfo['attachmentextensions'])){
        $mobiquo_can_upload = true;
    }

    $mobiquo_can_reply = true;
    if($thread['isdeleted'] OR !$forum['allowposting']){
        $mobiquo_can_reply = false;
    }

    if (($vbulletin->userinfo['userid'] != $threadinfo['postuserid'] OR !$vbulletin->userinfo['userid']) AND (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewothers']) OR !($forumperms & $vbulletin->bf_ugp_forumpermissions['canreplyothers'])))
    {
        $mobiquo_can_reply = false;
    }
    if (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canview']) OR !($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads']) OR (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canreplyown']) AND $vbulletin->userinfo['userid'] == $threadinfo['postuserid']))
    {
        $mobiquo_can_reply = false;
    }
    
    $is_approved = true;
    if($threadinfo['visible'] == 0){
        $is_approved = false;
    }
    
    $return_data = array(
        'total_post_num'=> new xmlrpcval($totalposts, 'int'),
        'forum_id'      => new xmlrpcval($thread['forumid'], 'string'),
        'forum_title'   => new xmlrpcval(mobiquo_encode($foruminfo['title']), 'base64'),
        'topic_id'      => new xmlrpcval($threadinfo['threadid'], 'string'),
        'topic_title'   => new xmlrpcval(mobiquo_encode($threadinfo['title']), 'base64'),
        'prefix'        => new xmlrpcval(mobiquo_encode($thread['prefix_plain_html']), 'base64'),
        'position'      => new xmlrpcval($count, 'int'),
        
        'can_reply'     => new xmlrpcval($mobiquo_can_reply, 'boolean'),
        'can_upload'    => new xmlrpcval($mobiquo_can_upload, 'boolean'),
        'is_approved'   => new xmlrpcval($is_approved, 'boolean'),
        
        'posts'         => new xmlrpcval($return_posts_list, 'array'),
    );
    
    if ($vbulletin->userinfo['userid']) $return_data['can_subscribe']   = new xmlrpcval(true, 'boolean');
    if ($threadinfo['issubscribed'])    $return_data['is_subscribed']   = new xmlrpcval(true, 'boolean');
  //if ($mobiquo_can_reply)             $return_data['can_reply']       = new xmlrpcval(true, 'boolean');
  //if ($mobiquo_can_upload)            $return_data['can_upload']      = new xmlrpcval(true, 'boolean');
    if ($show['movethread'])            $return_data['can_move']        = new xmlrpcval(true, 'boolean');
    if ($show['deletethread'])          $return_data['can_delete']      = new xmlrpcval(true, 'boolean');
    if ($thread['isdeleted'])           $return_data['is_deleted']      = new xmlrpcval(true, 'boolean');
    if ($show['openclose'])             $return_data['can_close']       = new xmlrpcval(true, 'boolean');
    if (!$thread['open'])               $return_data['is_closed']       = new xmlrpcval(true, 'boolean');
    if ($show['movethread'])            $return_data['can_stick']       = new xmlrpcval(true, 'boolean');
    if ($threadinfo['sticky'])          $return_data['is_sticky']       = new xmlrpcval(true, 'boolean');
    if ($show['moderatethread'])        $return_data['can_approve']     = new xmlrpcval(true, 'boolean');
    if ($is_approved)                   $return_data['is_approved']     = new xmlrpcval(true, 'boolean');
    
    return new xmlrpcresp(new xmlrpcval($return_data, 'struct'));
}
