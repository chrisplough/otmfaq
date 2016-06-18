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
define('THIS_SCRIPT', 'newthread');
define('CSRF_PROTECTION', false);

$phrasegroups = array(
    'threadmanage',
    'postbit',
    'posting',
    'prefix'
);

$specialtemplates = array(
    'smiliecache',
    'bbcodecache',
    'ranks',
);

$globaltemplates = array(
    'newpost_attachment',
    'newpost_attachmentbit',
    'newthread',
    'humanverify',
    'optgroup',
    'postbit_attachment',
);

$actiontemplates = array();

require_once('./global.php');
require_once(DIR . '/includes/functions_newpost.php');
require_once(DIR . '/includes/functions_editor.php');
require_once(DIR . '/includes/functions_bigthree.php');


function new_topic_func($xmlrpc_params)
{
    global $vbulletin, $vbphrase, $forumperms;

    $params = php_xmlrpc_decode($xmlrpc_params);

    $forum_id = intval($params[0]);
    // $forum_id =  3;
    $subject= mobiquo_encode($params[1], 'to_local');
    //  $subject=   'mobiquo test';

    $text_body = mobiquo_encode($params[2], 'to_local');

    //  $text_body = 'mobiquo test';
    $_POST['do'] = 'postthread';
    $foruminfo = mobiquo_verify_id('forum', $forum_id, 1, 1);
    // get decent textarea size for user's browser


    // sanity checks...
    if (empty($_REQUEST['do']))
    {
        $_REQUEST['do'] = 'newthread';
    }

    //($hook = vBulletinHook::fetch_hook('newthread_start')) ? eval($hook) : false;
    if(!is_array($foruminfo)){
        return $foruminfo;
    }
    if (!$foruminfo['forumid'])
    {
        return_fault(fetch_error('invalidid', $vbphrase['forum']));
    }

    if (!$foruminfo['allowposting'] OR $foruminfo['link'] OR !$foruminfo['cancontainthreads'])
    {
        return_fault(fetch_error('forumclosed'));
    }

    $forumperms = fetch_permissions($forum_id);

    if (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canview']) OR !($forumperms & $vbulletin->bf_ugp_forumpermissions['canpostnew']))
    {
        return_fault();
    }

    // check if there is a forum password and if so, ensure the user has it set
    if (!verify_forum_password($foruminfo['forumid'], $foruminfo['password'], false))
        return_fault('Your administrator has required a password to access this forum.');

    $show['tag_option'] = ($vbulletin->options['threadtagging'] AND ($forumperms & $vbulletin->bf_ugp_forumpermissions['cantagown']));

    // ############################### start post thread ###############################
    if ($_POST['do'] == 'postthread')
    {
        // Variables reused in templates
        if($params[5]){
            $posthash = $params[5];
        }
        if($params[3]){
            $prefix_id= $params[3];
        }

        $poststarttime = $vbulletin->input->clean_gpc('p', 'poststarttime', TYPE_UINT);

        $vbulletin->input->clean_array_gpc('p', array(
        'wysiwyg'         => TYPE_BOOL,
        'preview'         => TYPE_STR,
        'message'         => TYPE_STR,
        'subject'         => TYPE_STR,
        'iconid'          => TYPE_UINT,
        'rating'          => TYPE_UINT,
        'prefixid'        => TYPE_NOHTML,
        'taglist'         => TYPE_NOHTML,

        'postpoll'        => TYPE_BOOL,
        'polloptions'     => TYPE_UINT,

        'signature'       => TYPE_BOOL,
        'disablesmilies' => TYPE_BOOL,
        'parseurl'        => TYPE_BOOL,
        'folderid'        => TYPE_UINT,
        'emailupdate'     => TYPE_UINT,
        'stickunstick'    => TYPE_BOOL,
        'openclose'       => TYPE_BOOL,

        'username'        => TYPE_STR,
        'loggedinuser'    => TYPE_INT,

        'humanverify'     => TYPE_ARRAY,

        'podcasturl'      => TYPE_STR,
        'podcastsize'     => TYPE_UINT,
        'podcastexplicit' => TYPE_BOOL,
        'podcastkeywords' => TYPE_STR,
        'podcastsubtitle' => TYPE_STR,
        'podcastauthor'   => TYPE_STR,
        ));

        if ($vbulletin->GPC['loggedinuser'] != 0 AND $vbulletin->userinfo['userid'] == 0)
        {
            return_fault(fetch_error('session_timed_out_login'));
        }
        
        $newpost = array();
        $newpost['message'] = $text_body;

        if (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canpostpoll']))
        {
            $vbulletin->GPC['postpoll'] = false;
        }

        $newpost['title'] =$subject;
        $newpost['iconid'] =& $vbulletin->GPC['iconid'];
        require_once(DIR . '/includes/functions_prefix.php');

        if (can_use_prefix($prefix_id))
        {
            $newpost['prefixid'] =& $prefix_id;
        }
        if ($show['tag_option'])
        {
            $newpost['taglist'] =& $vbulletin->GPC['taglist'];
        }
        $newpost['parseurl']        = $foruminfo['allowbbcode'];
        $newpost['signature']       = $GLOBALS['config']['forum_signature'];
        $newpost['preview']         =& $vbulletin->GPC['preview'];
        $newpost['disablesmilies']  =& $vbulletin->GPC['disablesmilies'];
        $newpost['rating']          =& $vbulletin->GPC['rating'];
        $newpost['username']        =& $vbulletin->GPC['username'];
        $newpost['postpoll']        =& $vbulletin->GPC['postpoll'];
        $newpost['polloptions']     =& $vbulletin->GPC['polloptions'];
        $newpost['folderid']        =& $vbulletin->GPC['folderid'];
        $newpost['humanverify']     =& $vbulletin->GPC['humanverify'];
        $newpost['poststarttime']   = $poststarttime;
        $newpost['posthash']        = $posthash;
        // moderation options
        $newpost['stickunstick']    =& $vbulletin->GPC['stickunstick'];
        $newpost['openclose']       =& $vbulletin->GPC['openclose'];
        $newpost['podcasturl']      =& $vbulletin->GPC['podcasturl'];
        $newpost['podcastsize']     =& $vbulletin->GPC['podcastsize'];
        $newpost['podcastexplicit'] =& $vbulletin->GPC['podcastexplicit'];
        $newpost['podcastkeywords'] =& $vbulletin->GPC['podcastkeywords'];
        $newpost['podcastsubtitle'] =& $vbulletin->GPC['podcastsubtitle'];
        $newpost['podcastauthor']   =& $vbulletin->GPC['podcastauthor'];
        
        if ($vbulletin->GPC_exists['emailupdate'])
        {
            $newpost['emailupdate'] =& $vbulletin->GPC['emailupdate'];
        }
        else
        {
            $newpost['emailupdate'] = array_pop($array = array_keys(fetch_emailchecked(array(), $vbulletin->userinfo)));
        }

        if (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads']))
        {
            $newpost['emailupdate'] = 0;
        }

        $foruminfo = mobiquo_verify_id('forum', $forum_id, 0, 1);

        mobiquo_build_new_post('thread', $foruminfo, array(), array(), $newpost, $errors);

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
            if ($newpost['threadid'] == 0)
            {
                if (defined('NOSHUTDOWNFUNC'))
                {
                    exec_shut_down();
                }
                
                return_fault('duplicate create/reply thread error, or the system restrict creating multiple thread within short time-frame');
            }
            else
            {
                //$threadinfo = mobiquo_verify_id('thread', $newpost['threadid'], 0, 1);
                $threadinfo = fetch_threadinfo($newpost['threadid']); // need the forumread variable from this
                    
                mark_thread_read($threadinfo, $foruminfo, $vbulletin->userinfo['userid'], TIMENOW);
                if (defined('NOSHUTDOWNFUNC'))
                {
                    exec_shut_down();
                }
                
                $post_stat = $newpost['visible'] ? 0 : 1;
                
                return new xmlrpcresp(new xmlrpcval(array(
                    'result'    => new xmlrpcval(true, 'boolean'),
                    'topic_id' => new xmlrpcval($newpost['threadid'], 'string'),
                    'stat'      => new xmlrpcval($post_stat, 'int'),
                ), 'struct'));
            }
        }
    }
}
