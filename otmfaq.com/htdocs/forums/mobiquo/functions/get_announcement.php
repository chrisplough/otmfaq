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

define('THIS_SCRIPT', 'announcement');
define('CSRF_PROTECTION', false);
// #################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array(
    'postbit',
    'reputationlevel',
    'posting',
);

$specialtemplates = array(
    'smiliecache',
    'bbcodecache'
);

$globaltemplates = array();

$actiontemplates = array(
    'view' => array(
        'announcement',
        'im_aim',
        'im_icq',
        'im_msn',
        'im_yahoo',
        'im_skype',
        'postbit',
        'postbit_wrapper',
        'postbit_onlinestatus',
        'postbit_reputation',
        'bbcode_code',
        'bbcode_html',
        'bbcode_php',
        'bbcode_quote',
),
    'edit' => array(
        'announcement_edit',
),
);

require_once('./global.php');
require_once(DIR . '/includes/functions_bigthree.php');


function get_announcement_func($xmlrpc_params)
{
    global $db, $vbulletin, $vbphrase;
    
    $params = php_xmlrpc_decode($xmlrpc_params);
    
    if(!$params[0])
    {
        return_fault(fetch_error('invalidid', $vbphrase['forum']));
    }
    
    $vbulletin->GPC['announcementid'] = $params[0];

    $announcementinfo = mobiquo_verify_id('announcement', $vbulletin->GPC['announcementid'], 1, 1);
    if ($announcementinfo['forumid'] != -1 AND $_POST['do'] != 'update')
    {
        $vbulletin->GPC['forumid'] = $announcementinfo['forumid'];
    }
    $announcementinfo = array_merge($announcementinfo , convert_bits_to_array($announcementinfo['announcementoptions'], $vbulletin->bf_misc_announcementoptions));

    // verify that the visiting user has permission to view this announcement
    if (($announcementinfo['startdate'] > TIMENOW OR $announcementinfo['enddate'] < TIMENOW) AND !can_moderate($vbulletin->GPC['forumid'], 'canannounce'))
    {
        return_fault();
    }
    
    $forumlist = '';
    if ($announcementinfo['forumid'] > -1 OR $vbulletin->GPC['forumid'])
    {
        $foruminfo = mobiquo_verify_id('forum', $vbulletin->GPC['forumid'], 1, 1);
        $curforumid = $foruminfo['forumid'];
        $forumperms = fetch_permissions($foruminfo['forumid']);

        if (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canview']) OR !($forumperms & $vbulletin->bf_ugp_forumpermissions['canviewthreads']))
        {
            return_fault();
        }

        // check if there is a forum password and if so, ensure the user has it set
        if (!verify_forum_password($foruminfo['forumid'], $foruminfo['password'], false))
            return_fault('Your administrator has required a password to access this forum.');
            
        $forumlist = fetch_forum_clause_sql($foruminfo['forumid'], 'announcement.forumid');
    }
    else if (!$announcementinfo['announcementid'])
    {
        return_fault(fetch_error('invalidid', $vbphrase['announcement']));
    }
    
    $announcements = $db->query_read_slave("
        SELECT announcement.announcementid, announcement.announcementid AS postid, startdate, enddate, announcement.title, pagetext, announcementoptions, views,
            user.*, userfield.*, usertextfield.*,
            sigpic.userid AS sigpic, sigpic.dateline AS sigpicdateline, sigpic.width AS sigpicwidth, sigpic.height AS sigpicheight,
            IF(displaygroupid=0, user.usergroupid, displaygroupid) AS displaygroupid, infractiongroupid
            " . ($vbulletin->options['avatarenabled'] ? ",avatar.avatarpath, NOT ISNULL(customavatar.userid) AS hascustomavatar, customavatar.dateline AS avatardateline,customavatar.width AS avwidth,customavatar.height AS avheight" : "") . "
            " . (($vbulletin->userinfo['userid']) ? ", NOT ISNULL(announcementread.announcementid) AS readannouncement" : "") . "
            $hook_query_fields
        FROM  " . TABLE_PREFIX . "announcement AS announcement
        " . (($vbulletin->userinfo['userid']) ? "LEFT JOIN " . TABLE_PREFIX . "announcementread AS announcementread ON(announcementread.announcementid = announcement.announcementid AND announcementread.userid = " . $vbulletin->userinfo['userid'] . ")" : "") . "
        LEFT JOIN " . TABLE_PREFIX . "user AS user ON(user.userid=announcement.userid)
        LEFT JOIN " . TABLE_PREFIX . "userfield AS userfield ON(userfield.userid=announcement.userid)
        LEFT JOIN " . TABLE_PREFIX . "usertextfield AS usertextfield ON(usertextfield.userid=announcement.userid)
        LEFT JOIN " . TABLE_PREFIX . "sigpic AS sigpic ON(sigpic.userid = announcement.userid)
        " . ($vbulletin->options['avatarenabled'] ? "LEFT JOIN " . TABLE_PREFIX . "avatar AS avatar ON(avatar.avatarid=user.avatarid)
        LEFT JOIN " . TABLE_PREFIX . "customavatar AS customavatar ON(customavatar.userid=announcement.userid)" : "") . "
        $hook_query_joins
        WHERE
            " . ($vbulletin->GPC['announcementid'] ?
                "announcement.announcementid = " . $vbulletin->GPC['announcementid'] :
                "startdate <= " . TIMENOW . " AND enddate >= " . TIMENOW . " " . (!empty($forumlist) ? "AND $forumlist" : "")
        ) . "
        $hook_query_where
        ORDER BY startdate DESC, announcementid DESC
    ");

    if ($db->num_rows($announcements) == 0)
    {
        return_fault(fetch_error('invalidid', $vbphrase['announcement']));
    }

    if (!$vbulletin->options['oneannounce'] AND $vbulletin->GPC['announcementid'] AND !empty($forumlist))
    {
        $anncount = $db->query_first_slave("
        SELECT COUNT(*) AS total
        FROM " . TABLE_PREFIX . "announcement AS announcement
        WHERE startdate <= " . TIMENOW . "
            AND enddate >= " . TIMENOW . "
            AND $forumlist
    ");
        $anncount['total'] = intval($anncount['total']);
        $show['viewall'] = $anncount['total'] > 1 ? true : false;
    }
    else
    {
        $show['viewall'] = false;
    }

    require_once(DIR . '/includes/class_postbit.php');

    $show['announcement'] = true;

    $counter = 0;
    $anncids = array();
    $announcebits = '';
    $announceread = array();

    $postbit_factory = new vB_Postbit_Factory();
    $postbit_factory->registry =& $vbulletin;
    $postbit_factory->forum =& $foruminfo;
    $postbit_factory->cache = array();
    $postbit_factory->bbcode_parser = new vB_BbCodeParser($vbulletin, fetch_tag_list());

    while ($post = $db->fetch_array($announcements))
    {
        $postbit_obj =& $postbit_factory->fetch_postbit('announcement');

        $post['counter'] = ++$counter;
        
        $post['startdate_orig'] = $post['startdate'];
        $announcebits .= $postbit_obj->construct_postbit($post);
        $anncids[] = $post['announcementid'];
        $announceread[] = "($post[announcementid], " . $vbulletin->userinfo['userid'] . ")";
        $time_string = construct_phrase($vbphrase['x_until_y'], $post['startdate'], $post['enddate']);

        $return_post = array(
            'topic_id'          => new xmlrpcval($vbulletin->GPC['forumid'], 'string'),
            'post_id'           => new xmlrpcval($post['postid'], 'string'),
            'post_title'        => new xmlrpcval(mobiquo_encode($post['title']), 'base64'),
            'post_content'      => new xmlrpcval(mobiquo_encode(post_content_clean($post['pagetext'])), 'base64'),
            'post_author_id'    => new xmlrpcval($post['userid'], 'string'),
            'post_author_name'  => new xmlrpcval(mobiquo_encode($post['username']), 'base64'),
            'post_time'         => new xmlrpcval(mobiquo_iso8601_encode($post['startdate_orig']-$vbulletin->options['hourdiff'], $vbulletin->userinfo['tzoffset']), 'dateTime.iso8601'),
            'post_count'        => new xmlrpcval(0, 'int'),
            'attachments'       => new xmlrpcval($return_attachments, 'array'),
            'time_string'       => new xmlrpcval($time_string, 'base64'),
        );
        
        $return_post['icon_url'] = new xmlrpcval('', 'string');
        if($post[avatarurl]){
            $return_post['icon_url']=new xmlrpcval(get_icon_real_url($post[avatarurl]), 'string');
        }
        $return_post[attachment_authority] = new xmlrpcval(0, 'int');
        if(!($forumperms & $vbulletin->bf_ugp_forumpermissions['cangetattachment'])){
            $return_post[attachment_authority] = new xmlrpcval(4, 'int');
        }

        $xmlrpc_return_post = new xmlrpcval( $return_post, 'struct');
        $return_posts_list[] = $xmlrpc_return_post;
    }

    if (!empty($anncids))
    {
        $db->shutdown_query("
            UPDATE " . TABLE_PREFIX . "announcement
            SET views = views + 1
            WHERE announcementid IN (" . implode(', ', $anncids) . ")
        ");

        if ($vbulletin->userinfo['userid'])
        {
            $db->shutdown_query("
                REPLACE INTO " . TABLE_PREFIX . "announcementread
                    (announcementid, userid)
                VALUES
                    " . implode(', ', $announceread) . "
            ");
        }
    }

    return new xmlrpcresp(new xmlrpcval(array(
        'sort_order'    => new xmlrpcval($mobiquo_postorder, 'string'),
        'total_post_num'=> new xmlrpcval(1, 'int'),
        'forum_id'      => new xmlrpcval('-1', 'string'),
        'posts'         => new xmlrpcval($return_posts_list, 'array'),
    ), 'struct'));
}
