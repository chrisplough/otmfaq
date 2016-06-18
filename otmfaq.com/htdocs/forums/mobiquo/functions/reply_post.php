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
require_once(CWD1."/include/functions_create_topic.php");

define('GET_EDIT_TEMPLATES', true);
define('THIS_SCRIPT', 'newreply');
define('CSRF_PROTECTION', false);

$phrasegroups = array(
    'threadmanage',
    'posting',
    'postbit',
    'reputationlevel',
);

$specialtemplates = array(
    'smiliecache',
    'bbcodecache',
    'ranks'
);

$globaltemplates = array(
    'newreply',
    'newpost_attachment',
    'newreply_reviewbit',
    'newreply_reviewbit_ignore',
    'newreply_reviewbit_ignore_global',
    'newpost_attachmentbit',
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
    'postbit_ip',
    'postbit_onlinestatus',
    'postbit_reputation',
    'bbcode_code',
    'bbcode_html',
    'bbcode_php',
    'bbcode_quote',
    'humanverify',
);

$actiontemplates = array();


require_once('./global.php');
require_once(DIR . '/includes/functions_newpost.php');
require_once(DIR . '/includes/functions_editor.php');
require_once(DIR . '/includes/functions_bigthree.php');
require_once(DIR . '/includes/class_postbit.php');

error_reporting(MOBIQUO_DEBUG);


function reply_post_func($xmlrpc_params)
{
    global $vbulletin, $db, $forumperms, $vbphrase, $html_content;

    $decode_params = php_xmlrpc_decode($xmlrpc_params);
    $reply_threadid = intval($decode_params[1]);

    if (empty($reply_threadid))
    {
        return_fault(fetch_error('invalidid', $vbphrase['thread']));
    }

    $reply_title = '';
    $reply_message = mobiquo_encode($decode_params[3], 'to_local');
    $html_content= false;
    
    if(isset($decode_params[6]) && $decode_params[6]){
        $html_content = true;
    }
    
    $coventry = fetch_coventry('string');

    $sql_first_topic = "
    SELECT thread.firstpostid
    FROM " . TABLE_PREFIX . "thread AS thread
    WHERE thread.threadid = '$reply_threadid'  
    ";

    $first_topic = $db->query_first_slave($sql_first_topic);
    $postidbythreadid = $first_topic['firstpostid'];

    $vbulletin->GPC['postid'] = $postidbythreadid;

    $checked = array();
    $newpost = array();
    $postattach = array();

    // sanity checks...
    if (empty($_REQUEST['do']))
    {
        $_REQUEST['do'] = 'newreply';
    }

    $vbulletin->GPC['noquote'] = true;

    //==============================================================
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

    //===================================================
    //($hook = vBulletinHook::fetch_hook('newreply_start')) ? eval($hook) : false;

    // ### CHECK IF ALLOWED TO POST ###
    if ($threadinfo['isdeleted'] OR (!$threadinfo['visible'] AND !can_moderate($threadinfo['forumid'], 'canmoderateposts')))
    {
        return_fault(fetch_error('invalidid', $vbphrase['thread']));
    }

    if (!$foruminfo['allowposting'] OR $foruminfo['link'] OR !$foruminfo['cancontainthreads'])
    {
        return_fault(fetch_error('forumclosed'));
    }

    if (!$threadinfo['open'])
    {
        if (!can_moderate($threadinfo['forumid'], 'canopenclose'))
        {
            return_fault(fetch_error('threadclosed'));
        }
    }

    $forumperms = fetch_permissions($foruminfo['forumid']);

    if (($vbulletin->userinfo['userid'] != $threadinfo['postuserid'] OR !$vbulletin->userinfo['userid']) AND (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewothers']) OR !($forumperms & $vbulletin->bf_ugp_forumpermissions['canreplyothers'])))
    {
        return_fault();
    }
    if (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canview']) OR !($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads']) OR (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canreplyown']) AND $vbulletin->userinfo['userid'] == $threadinfo['postuserid']))
    {
        return_fault();
    }

    // check if there is a forum password and if so, ensure the user has it set
    if (!verify_forum_password($foruminfo['forumid'], $foruminfo['password'], false))
        return_fault('Your administrator has required a password to access this forum.');

    // *********************************************************************************
    // Tachy goes to coventry
    if (in_coventry($thread['postuserid']) AND !can_moderate($thread['forumid']))
    {
        return_fault(fetch_error('invalidid', $vbphrase['thread']));
    }

    // ### GET QUOTE FEATURES (WITH MQ SUPPORT) ###
    // This section must exist before $_POST[do] == postreply because of the $newpost stuff
    $newpost['message'] = '';
    $unquoted_posts = 0;
    $multiquote_empty = '';
    $specifiedpost = 0;

    $_POST['do'] = 'postreply';

    // ############################### start post reply ###############################
    if ($_POST['do'] == 'postreply')
    {
        // Variables reused in templates
        if($decode_params[5]){
            $posthash = $decode_params[5];
        }
        $poststarttime =& $vbulletin->input->clean_gpc('p', poststarttime, TYPE_UINT);

        $vbulletin->input->clean_array_gpc('p', array(
        'wysiwyg'        => TYPE_BOOL,
        'message'        => TYPE_STR,
        'quickreply'     => TYPE_BOOL,
        'fromquickreply' => TYPE_BOOL,
        'ajaxqrfailed'   => TYPE_BOOL,
        'folderid'       => TYPE_UINT,
        'emailupdate'    => TYPE_UINT,
        'title'          => TYPE_STR,
        'iconid'         => TYPE_UINT,
        'parseurl'       => TYPE_BOOL,
        'signature'      => TYPE_BOOL,
        'preview'        => TYPE_STR,
        'disablesmilies' => TYPE_BOOL,
        'username'       => TYPE_STR,
        'rating'         => TYPE_UINT,
        'stickunstick'   => TYPE_BOOL,
        'openclose'      => TYPE_BOOL,
        'ajax'           => TYPE_BOOL,
        'ajax_lastpost' => TYPE_INT,
        'loggedinuser'   => TYPE_INT,
        'humanverify'    => TYPE_ARRAY,
        'multiquoteempty'=> TYPE_NOHTML,
        'specifiedpost' => TYPE_BOOL
        ));

        $vbulletin->GPC['title'] = $reply_title;
        $vbulletin->GPC['message'] = $reply_message;

        $vbulletin->GPC['quickreply'] = false;
        $vbulletin->GPC['fromquickreply'] = false;
        $vbulletin->GPC['specifiedpost'] = 0;
        $vbulletin->GPC['loggedinuser'] = 1;

        //$vbulletin->GPC['ajax'] = true;

        if ($vbulletin->GPC['loggedinuser'] != 0 AND $vbulletin->userinfo['userid'] == 0)
        {
            return_fault(fetch_error('session_timed_out_login'));
        }

        $newpost['message'] = $reply_message;

        if ($vbulletin->GPC['ajax'])
        {
            // posting via ajax so we need to handle those %u0000 entries
            $newpost['message'] = convert_urlencoded_unicode($newpost['message']);
        }

        if ($vbulletin->GPC['quickreply'])
        {
            $originalposter = fetch_quote_username($postinfo['username'] . ";$postinfo[postid]");
            $pagetext = trim(strip_quotes($postinfo['pagetext']));

            //($hook = vBulletinHook::fetch_hook('newreply_post_quote')) ? eval($hook) : false;

            //eval('$quotemessage = "' . fetch_template('newpost_quote', 0, false) . '";');
            $newpost['message'] = trim($quotemessage) . "\n$newpost[message]";
        }

        if ($vbulletin->GPC['fromquickreply'])
        {
            // We only add notifications to threads that don't have one if the user defaults to it, do nothing else!
            if ($vbulletin->userinfo['autosubscribe'] != -1 AND !$threadinfo['issubscribed'])
            {
                $vbulletin->GPC['folderid'] = 0;
                $vbulletin->GPC['emailupdate'] = $vbulletin->userinfo['autosubscribe'];
            }
            else if ($threadinfo['issubscribed'])
            { // Don't alter current settings
                $vbulletin->GPC['folderid'] = $threadinfo['folderid'];
                $vbulletin->GPC['emailupdate'] = $threadinfo['emailupdate'];
            }
            else
            { // Don't don't add!
                $vbulletin->GPC['emailupdate'] = 9999;
            }

            // fetch the quoted post title
            $vbulletin->GPC['title'] = fetch_quote_title($postinfo['title'], $threadinfo['title']);
        }

        $newpost['title']          =& $vbulletin->GPC['title'];
        $newpost['iconid']         =& $vbulletin->GPC['iconid'];
        $newpost['parseurl']       = $foruminfo['allowbbcode'];
        $newpost['signature']      = $GLOBALS['config']['forum_signature'];
        $newpost['preview']        =& $vbulletin->GPC['preview'];
        $newpost['disablesmilies'] =& $vbulletin->GPC['disablesmilies'];
        $newpost['rating']         =& $vbulletin->GPC['rating'];
        $newpost['username']       =& $vbulletin->GPC['username'];
        $newpost['folderid']       =& $vbulletin->GPC['folderid'];
        $newpost['quickreply']     =& $vbulletin->GPC['quickreply'];
        $newpost['poststarttime']  =& $poststarttime;
        $newpost['posthash']       =& $posthash;
        $newpost['humanverify']    =& $vbulletin->GPC['humanverify'];
        // moderation options
        $newpost['stickunstick']   =& $vbulletin->GPC['stickunstick'];
        $newpost['openclose']      =& $vbulletin->GPC['openclose'];

        $newpost['ajaxqrfailed']   = $vbulletin->GPC['ajaxqrfailed'];

        if ($vbulletin->GPC_exists['emailupdate'])
        {
            $newpost['emailupdate'] =& $vbulletin->GPC['emailupdate'];
        }
        else
        {
            $newpost['emailupdate'] = array_pop($array = array_keys(fetch_emailchecked($threadinfo, $vbulletin->userinfo)));
        }

        if ($vbulletin->GPC['specifiedpost'] AND $postinfo)
        {
            $postinfo['specifiedpost'] = true;
        }

        // $foruminfo = mobiquo_verify_id('forum', $threadinfo['threadid'], 0, 1);
        mobiquo_build_new_post('reply', $foruminfo, $threadinfo, $postinfo, $newpost, $errors);

        if (sizeof($errors) > 0)
        {
            if (defined('NOSHUTDOWNFUNC'))
            {
                exec_shut_down();
            }
            $error_string = mobiquo_encode(implode('', $errors));

            return_fault($error_string);
        }
        else
        {
            // post repeat error
            if ($newpost['postid']==0){
                if (defined('NOSHUTDOWNFUNC'))
                {
                    exec_shut_down();
                }
                
                return_fault('duplicate create/reply thread error, or the system restrict creating multiple thread within short time-frame');
            }else{
                $threadinfo = fetch_threadinfo($reply_threadid ); // need the forumread variable from this
                    
                mark_thread_read($threadinfo, $foruminfo, $vbulletin->userinfo['userid'], TIMENOW);
                $new_post = get_post_from_id($newpost['postid']);
                if (defined('NOSHUTDOWNFUNC'))
                {
                    exec_shut_down();
                }
                
                return new xmlrpcresp(new xmlrpcval($new_post, 'struct'));
            }
        }
    }
}
