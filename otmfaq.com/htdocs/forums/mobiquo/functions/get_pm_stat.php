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

define('IN_MOBIQUO', true);
require_once(CWD1.'/include/functions_private_message.php');

define('GET_EDIT_TEMPLATES', 'newpm,insertpm');
define('THIS_SCRIPT', 'private');
define('CSRF_PROTECTION', false);

$phrasegroups = array(
    'posting',
    'postbit',
    'pm',
    'reputationlevel',
    'user'
);

$specialtemplates = array(
    'smiliecache',
    'bbcodecache',
    'banemail',
    'noavatarperms',
);

$globaltemplates = array(
    'USERCP_SHELL',
    'usercp_nav_folderbit'
);

$actiontemplates = array(
    'editfolders' => array(
        'pm_editfolders',
        'pm_editfolderbit',
    ),
    'emptyfolder' => array(
        'pm_emptyfolder',
    ),
    'showpm' => array(
        'pm_showpm',
        'pm_messagelistbit_user',
        'postbit',
        'postbit_wrapper',
        'postbit_onlinestatus',
        'postbit_reputation',
        'bbcode_code',
        'bbcode_html',
        'bbcode_php',
        'bbcode_quote',
        'im_aim',
        'im_icq',
        'im_msn',
        'im_yahoo',
        'im_skype',
    ),
    'newpm' => array(
        'pm_newpm',
    ),
    'managepm' => array(
        'pm_movepm',
    ),
    'trackpm' => array(
        'pm_trackpm',
        'pm_receipts',
        'pm_receiptsbit',
    ),
    'messagelist' => array(
        'pm_messagelist',
        'pm_messagelist_periodgroup',
        'pm_messagelistbit',
        'pm_messagelistbit_user',
        'pm_messagelistbit_ignore',
    )
);
$actiontemplates['insertpm'] =& $actiontemplates['newpm'];


require_once('./global.php');
require_once(DIR . '/includes/functions_user.php');
require_once(DIR . '/includes/functions_misc.php');


function parse_pm_bbcode($bbcode, $smilies = true)
{
    global $vbulletin;
    require_once(DIR . '/includes/class_bbcode.php');
    
    $bbcode_parser =& new vB_BbCodeParser($vbulletin, fetch_tag_list());
    return $bbcode_parser->parse($bbcode, 'privatemessage', $smilies);
}

// ###################### Start pm update counters #######################
// update the pm counters for $vbulletin->userinfo
function build_pm_counters()
{
    global $vbulletin;
    
    $pmcount = $vbulletin->db->query_first("
        SELECT
            COUNT(pmid) AS pmtotal,
            SUM(IF(messageread = 0 AND folderid >= 0, 1, 0)) AS pmunread
        FROM " . TABLE_PREFIX . "pm AS pm
        WHERE pm.userid = " . $vbulletin->userinfo['userid'] . "
    ");

    $pmcount['pmtotal'] = intval($pmcount['pmtotal']);
    $pmcount['pmunread'] = intval($pmcount['pmunread']);

    if ($vbulletin->userinfo['pmtotal'] != $pmcount['pmtotal'] OR $vbulletin->userinfo['pmunread'] != $pmcount['pmunread'])
    {
        // init user data manager
        $userdata =& datamanager_init('User', $vbulletin, ERRTYPE_STANDARD);
        $userdata->set_existing($vbulletin->userinfo);
        $userdata->set('pmtotal', $pmcount['pmtotal']);
        $userdata->set('pmunread', $pmcount['pmunread']);
        $userdata->save();
    }
}


function check_pm_permession()
{
    global $vbulletin, $permissions, $db;
    
    if (!$vbulletin->options['enablepms'])
    {
        return_fault(fetch_error('pm_adminoff'));
    }

    // the following is the check for actions which allow creation of new pms
    if ($permissions['pmquota'] < 1 OR !$vbulletin->userinfo['receivepm'])
    {
        $show['createpms'] = false;
    }

    // check permission to use private messaging
    if (($permissions['pmquota'] < 1 AND (!$vbulletin->userinfo['pmtotal'] OR in_array($_REQUEST['do'], array('insertpm', 'newpm')))) OR !$vbulletin->userinfo['userid'])
    {
        return_fault();
    }

    if (!$vbulletin->userinfo['receivepm'] AND in_array($_REQUEST['do'], array('insertpm', 'newpm')))
    {
        return_fault(fetch_error('pm_turnedoff'));
    }
}

function get_box_info_func()
{
    global $vbulletin, $permissions, $db, $messagecounters;
    
    if (!$vbulletin->options['enablepms'])
    {
        return_fault(fetch_error('pm_adminoff'));
    }

    // the following is the check for actions which allow creation of new pms
    if ($permissions['pmquota'] < 1 OR !$vbulletin->userinfo['receivepm'])
    {
        $show['createpms'] = false;
    }

    // check permission to use private messaging
    if (($permissions['pmquota'] < 1 AND (!$vbulletin->userinfo['pmtotal'] OR in_array($_REQUEST['do'], array('insertpm', 'newpm')))) OR !$vbulletin->userinfo['userid'])
    {
        return_fault();
    }

    if (!$vbulletin->userinfo['receivepm'] AND in_array($_REQUEST['do'], array('insertpm', 'newpm')))
    {
        return_fault(fetch_error('pm_turnedoff'));
    }

    $frmjmpsel['pm'] = 'class="fjsel" selected="selected"';
    $onload = '';
    $show['trackpm'] = $cantrackpm = $permissions['pmpermissions'] & $vbulletin->bf_ugp_pmpermissions['cantrackpm'];
    $vbulletin->input->clean_gpc('r', 'pmid', TYPE_UINT);

    $vbulletin->input->clean_array_gpc('r', array(
        'folderid'   => TYPE_INT,
        'perpage'    => TYPE_UINT,
        'pagenumber' => TYPE_UINT
    ));

    $folderid = $vbulletin->GPC['folderid'];

    $folderjump = mobiquo_construct_folder_jump(0, $vbulletin->GPC['folderid']);
    $foldername = $foldernames["{$vbulletin->GPC['folderid']}"];

    // count receipts
    $receipts = $db->query_first_slave("
        SELECT
            SUM(IF(readtime <> 0, 1, 0)) AS confirmed,
            SUM(IF(readtime = 0, 1, 0)) AS unconfirmed
        FROM " . TABLE_PREFIX . "pmreceipt
        WHERE userid = " . $vbulletin->userinfo['userid']
    );

    // get ignored users
    $ignoreusers = preg_split('#\s+#s', $vbulletin->userinfo['ignorelist'], -1, PREG_SPLIT_NO_EMPTY);
    $return_folders = array();
    foreach($folderjump as $folder_id => $folder_info)
    {
        $pms = $db->query_first_slave("
            SELECT 
                SUM(IF(pm.messageread <> 0, 1, 0)) AS readed,
                SUM(IF(pm.messageread = 0, 1, 0)) AS unreaded
            FROM " . TABLE_PREFIX . "pm AS pm
            WHERE pm.userid=" . $vbulletin->userinfo['userid'] . " AND pm.folderid=" . $folder_id . "
        ");

        $return_folder = array(
            'box_id'        => new xmlrpcval($folder_id, 'string'),
            'box_name'      => new xmlrpcval(mobiquo_encode($folder_info['box_name']), 'base64'),
            'msg_count'     => new xmlrpcval(($pms[readed]+$pms[unreaded]), 'int'),
            'unread_count' => new xmlrpcval($pms[unreaded], 'int')
        );
        
        if($folder_id == 0){
            $return_folder['box_type'] = new xmlrpcval('INBOX', 'string');
        } elseif ( $folder_id == -1) {
            $return_folder['box_type'] = new xmlrpcval('SENT', 'string');
        } else {
            $return_folder['box_type'] = new xmlrpcval('', 'string');
        }
        $xmlrpc_return_folder = new xmlrpcval($return_folder, 'struct');
        array_push($return_folders, $xmlrpc_return_folder);
    }

    $pmtotal = $vbulletin->userinfo['pmtotal'];

    $pmquota = $vbulletin->userinfo['permissions']['pmquota'];
    if (defined('NOSHUTDOWNFUNC'))
    {
        exec_shut_down();
    }
    
    return new xmlrpcresp(new xmlrpcval(array(
        'result'            => new xmlrpcval(true, 'boolean'),
        'message_room_count'=> new xmlrpcval(($pmquota-$pmtotal), 'int'),
        'list'              => new xmlrpcval($return_folders, 'array'),
    ), 'struct'));
}

function get_box_func($xmlrpc_params)
{
    global $vbulletin, $permissions, $db, $messagecounters;
    
    $params = php_xmlrpc_decode($xmlrpc_params);
    list($start, $perpage, $page) = process_page($params[1], $params[2]);
    
    $folderid = $params[0];
    $pmstatusfilter = '';
    if($params[0] == '') {
        return_fault('Invalid folder id');
    } else if ($params[0] == 'unread') {
        $folderid = 0;
        $pmstatusfilter = ' AND pm.messageread = 0 ';
    }
    
    $show['messagelist'] = true;
    if(!$vbulletin->options['enablepms'])
    {
        return_fault(fetch_error('pm_adminoff'));
    }

    // the following is the check for actions which allow creation of new pms
    if ($permissions['pmquota'] < 1 OR !$vbulletin->userinfo['receivepm'])
    {
        $show['createpms'] = false;
    }

    // check permission to use private messaging
    if (($permissions['pmquota'] < 1 AND (!$vbulletin->userinfo['pmtotal'] OR in_array($_REQUEST['do'], array('insertpm', 'newpm')))) OR !$vbulletin->userinfo['userid'])
    {
        return_fault();
    }

    if (!$vbulletin->userinfo['receivepm'] AND in_array($_REQUEST['do'], array('insertpm', 'newpm')))
    {
        return_fault(fetch_error('pm_turnedoff'));
    }

    // select correct part of forumjump
    $frmjmpsel['pm'] = 'class="fjsel" selected="selected"';
    //    construct_forum_jump();

    $onload = '';
    $show['trackpm'] = $cantrackpm = $permissions['pmpermissions'] & $vbulletin->bf_ugp_pmpermissions['cantrackpm'];

    // get a sensible value for $perpage
    // work out the $startat value
    $startat = ($vbulletin->GPC['pagenumber'] - 1) * $vbulletin->GPC['perpage'];

    // array to store private messages in period groups
    $pm_period_groups = array();

    // query private messages
    $pms = $db->query_read_slave("
        SELECT pm.*, pmtext.*
            " . iif($vbulletin->options['privallowicons'], ", icon.title AS icontitle, icon.iconpath") . "
        FROM " . TABLE_PREFIX . "pm AS pm
        LEFT JOIN " . TABLE_PREFIX . "pmtext AS pmtext ON(pmtext.pmtextid = pm.pmtextid)
        " . iif($vbulletin->options['privallowicons'], "LEFT JOIN " . TABLE_PREFIX . "icon AS icon ON(icon.iconid = pmtext.iconid)") . "
        WHERE pm.userid=" . $vbulletin->userinfo['userid'] . " AND pm.folderid=" . $folderid . $pmstatusfilter . "
        ORDER BY pmtext.dateline DESC
        LIMIT $start, " . $perpage . "
    ");
    while ($pm = $db->fetch_array($pms))
    {
        $pm_period_groups[ fetch_period_group($pm['dateline']) ]["{$pm['pmid']}"] = $pm;
    }
    $db->free_result($pms);

    // display returned messages
    $show['pmcheckbox'] = true;
    $ignoreusers = preg_split('#\s+#s', $vbulletin->userinfo['ignorelist'], -1, PREG_SPLIT_NO_EMPTY);
    require_once(DIR . '/includes/functions_bigthree.php');
    
    $pms_count = $db->query_first_slave("
                SELECT
                    SUM(IF(pm.messageread <> 0, 1, 0)) AS readed,
                    SUM(IF(pm.messageread = 0, 1, 0)) AS unreaded
                FROM " . TABLE_PREFIX . "pm AS pm
                WHERE pm.userid=" . $vbulletin->userinfo['userid'] . " AND pm.folderid=" . $folderid . "
    ");

    $return_message_list = array();

    foreach ($pm_period_groups AS $groupid => $pms)
    {
        if (preg_match('#^(\d+)_([a-z]+)_ago$#i', $groupid, $matches))
        {
            $groupname = construct_phrase($vbphrase["x_$matches[2]_ago"], $matches[1]);
        }
        else
        {
            $groupname = $vbphrase["$groupid"];
        }
        $groupid = $vbulletin->GPC['folderid'] . '_' . $groupid;
        $collapseobj_groupid =& $vbcollapse["collapseobj_pmf$groupid"];
        $collapseimg_groupid =& $vbcollapse["collapseimg_pmf$groupid"];

        $messagesingroup = sizeof($pms);
        $messagelistbits = '';

        foreach ($pms AS $pmid => $pm)
        {
            if (in_array($pm['fromuserid'], $ignoreusers))
            {
                // from user is on Ignore List
                //eval('$messagelistbits .= "' . fetch_template('pm_messagelistbit_ignore') . '";');
            }
            else
            {
                switch($pm['messageread'])
                {
                    case 0: // unread
                        $pm['statusicon'] = 'new';
                        break;

                    case 1: // read
                        $pm['statusicon'] = 'old';
                        break;

                    case 2: // replied to
                        $pm['statusicon'] = 'replied';
                        break;

                    case 3: // forwarded
                        $pm['statusicon'] = 'forwarded';
                        break;
                }
                
                $return_to_users  = array();
                if ($folderid == -1)
                {
                    $users = unserialize($pm['touserarray']);
                    $touser = array();
                    $tousers = array();
                    if (!empty($users))
                    {
                        foreach ($users AS $key => $item)
                        {
                            if (is_array($item))
                            {
                                foreach($item AS $subkey => $subitem)
                                {
                                    $touser["$subkey"] = $subitem;
                                }
                            }
                            else
                            {
                                $touser["$key"] = $item;
                            }
                        }
                        uasort($touser, 'strnatcasecmp');
                    }
                    $icon_user_id ='';
                    foreach ($touser AS $userid => $username)
                    {
                        if($icon_user_id == ""){
                            $icon_user_id = $userid;
                        }
                        $return_to_user = new xmlrpcval(array(
                            'user_id'   => new xmlrpcval($userid, 'string'),
                            'username'  => new xmlrpcval(mobiquo_encode($username), 'base64'),
                        ), 'struct');
                        array_push($return_to_users, $return_to_user);
                    }
                    $userbit = implode(', ', $tousers);
                }
                else
                {
                    $userid =& $pm['fromuserid'];
                    $username =& $pm['fromusername'];
                    $icon_user_id = $pm['fromuserid'];
                }
                
                $return_message = array(
                    'msg_id'        => new xmlrpcval($pm['pmid'], 'string'),
                    'msg_state'     => new xmlrpcval(($pm['messageread']+1), 'int'),
                    'sent_date'     => new xmlrpcval(mobiquo_iso8601_encode($pm['dateline']-$vbulletin->options['hourdiff'], $vbulletin->userinfo['tzoffset']), 'dateTime.iso8601'),
                    'msg_from'      => new xmlrpcval(mobiquo_encode($pm['fromusername']), 'base64'),
                    'msg_from_id'   => new xmlrpcval(mobiquo_encode($pm['fromuserid']), 'string'),
                    'msg_subject'   => new xmlrpcval(mobiquo_encode($pm['title']), 'base64'),
                    'short_content' => new xmlrpcval(mobiquo_encode(mobiquo_chop($pm['message'])), 'base64'),
                    'time_string'   => new xmlrpcval(format_time_string($pm['dateline']), 'base64'),
                    'icon_url'      => new xmlrpcval(mobiquo_get_user_icon($icon_user_id), 'string'),
                    'msg_to'        => new xmlrpcval($return_to_users, 'array'),
                );
                
                $userinfo = fetch_userinfo($icon_user_id);
                $mobiquo_user_online = fetch_online_status($userinfo) ? true : false;
                if ($mobiquo_user_online) $return_message['is_online'] = new xmlrpcval(true, 'boolean');

                $xmlrpc_return_message = new xmlrpcval($return_message, 'struct');
                $return_message_list[] = $xmlrpc_return_message;
            }
        }

        // free up memory not required any more
        unset($pm_period_groups["$groupid"]);
    }
    
    if (defined('NOSHUTDOWNFUNC'))
    {
        exec_shut_down();
    }
    
    return new xmlrpcresp(new xmlrpcval(array(
        'result'                => new xmlrpcval(true, 'boolean'),
        'total_unread_count'    => new xmlrpcval($pms_count['unreaded'], 'int'),
        'total_message_count'   => new xmlrpcval(($pms_count['readed']+$pms_count['unreaded']), 'int'),
        'list'                  => new xmlrpcval($return_message_list, 'array'),
    ), 'struct'));
}

function get_message_func($xmlrpc_params)
{
    global $vbulletin, $permissions, $db, $messagecounters, $vbphrase, $html_content;
    
    $params = php_xmlrpc_decode($xmlrpc_params);
    $messageid = $params[0];
    $html_content = false;
    if(isset($params[2]) && $params[2]){
        $html_content = true;
    }
    
    $show['messagelist'] = true;
    if(!$vbulletin->options['enablepms'])
    {
        return_fault(fetch_error('pm_adminoff'));
    }

    // the following is the check for actions which allow creation of new pms
    if ($permissions['pmquota'] < 1 OR !$vbulletin->userinfo['receivepm'])
    {
        $show['createpms'] = false;
    }

    // check permission to use private messaging
    if (($permissions['pmquota'] < 1 AND !$vbulletin->userinfo['pmtotal']) OR !$vbulletin->userinfo['userid'])
    {
        return_fault();
    }

    $onload = '';
    $show['trackpm'] = $cantrackpm = $permissions['pmpermissions'] & $vbulletin->bf_ugp_pmpermissions['cantrackpm'];
    require_once(DIR . '/includes/class_postbit.php');
    require_once(DIR . '/includes/functions_bigthree.php');
    
    $vbulletin->GPC['pmid'] = $messageid;
    $pm = $db->query_first_slave("
            SELECT
                pm.*, pmtext.*,
                " . iif($vbulletin->options['privallowicons'], "icon.title AS icontitle, icon.iconpath,") . "
                IF(ISNULL(pmreceipt.pmid), 0, 1) AS receipt, pmreceipt.readtime, pmreceipt.denied,
                sigpic.userid AS sigpic, sigpic.dateline AS sigpicdateline, sigpic.width AS sigpicwidth, sigpic.height AS sigpicheight
            FROM " . TABLE_PREFIX . "pm AS pm
            LEFT JOIN " . TABLE_PREFIX . "pmtext AS pmtext ON(pmtext.pmtextid = pm.pmtextid)
            " . iif($vbulletin->options['privallowicons'], "LEFT JOIN " . TABLE_PREFIX . "icon AS icon ON(icon.iconid = pmtext.iconid)") . "
            LEFT JOIN " . TABLE_PREFIX . "pmreceipt AS pmreceipt ON(pmreceipt.pmid = pm.pmid)
            LEFT JOIN " . TABLE_PREFIX . "sigpic AS sigpic ON(sigpic.userid = pmtext.fromuserid)
            WHERE pm.userid=" . $vbulletin->userinfo['userid'] . " AND pm.pmid=" . $vbulletin->GPC['pmid'] . "
        ");

    if (!$pm)
    {
        return_fault(fetch_error('invalidid', $vbphrase['private_message']));
    }

    // do read receipt
    $show['receiptprompt'] = $show['receiptpopup'] = false;
    if ($pm['receipt'] == 1 AND $pm['readtime'] == 0 AND $pm['denied'] == 0)
    {
        if ($permissions['pmpermissions'] & $vbulletin->bf_ugp_pmpermissions['candenypmreceipts'])
        {
            // set it to denied just now as some people might have ad blocking that stops the popup appearing
            $show['receiptprompt'] = $show['receiptpopup'] = true;
            $receipt_question_js = construct_phrase($vbphrase['x_has_requested_a_read_receipt'], unhtmlspecialchars($pm['fromusername']));
            $db->shutdown_query("UPDATE " . TABLE_PREFIX . "pmreceipt SET denied = 1 WHERE pmid = $pm[pmid]");
        }
        else
        {
            // they can't deny pm receipts so do not show a popup or prompt
            $db->shutdown_query("UPDATE " . TABLE_PREFIX . "pmreceipt SET readtime = " . TIMENOW . " WHERE pmid = $pm[pmid]");
        }
    }
    else if ($pm['receipt'] == 1 AND $pm['denied'] == 1)
    {
        $show['receiptprompt'] = true;
    }
    $pm_text = $pm['message'];
    $postbit_factory =& new vB_Postbit_Factory();
    $postbit_factory->registry =& $vbulletin;
    $postbit_factory->cache = array();
    $postbit_factory->bbcode_parser =& new vB_BbCodeParser($vbulletin, fetch_tag_list());
    $postbit_obj =& $postbit_factory->fetch_postbit('pm');
    $postbit = $postbit_obj->construct_postbit($pm);

    // update message to show read
    if ($pm['messageread'] == 0)
    {
        $db->shutdown_query("UPDATE " . TABLE_PREFIX . "pm SET messageread=1 WHERE userid=" . $vbulletin->userinfo['userid'] . " AND pmid=$pm[pmid]");
        //   print "UPDATE " . TABLE_PREFIX . "pm SET messageread=1 WHERE userid=" . $vbulletin->userinfo['userid'] . " AND pmid=$pm[pmid]";
        if ($pm['folderid'] >= 0)
        {
            $userdm =& datamanager_init('User', $vbulletin, ERRTYPE_SILENT);
            $userdm->set_existing($vbulletin->userinfo);
            $userdm->set('pmunread', 'IF(pmunread >= 1, pmunread - 1, 0)', false);
            $userdm->save(true, true);
            unset($userdm);
        }
    }

    $cclist = array();
    $bcclist = array();
    $ccrecipients = '';
    $bccrecipients = '';
    $touser = unserialize($pm['touserarray']);
    
    if (!is_array($touser))
    {
        $touser = array();
    }
    
    $return_to_users = array();
    $icon_user_id = "";

    foreach($touser AS $key => $item)
    {
        if (is_array($item))
        {
            foreach($item AS $subkey => $subitem)
            {
                if ($key != 'bcc' || $pm['fromuserid'] == $vbulletin->userinfo['userid'] || $subkey == $vbulletin->userinfo['userid'])
                {
                    if($icon_user_id == ''){
                        $icon_user_id = $subkey;
                    }

                    $return_to_user = new xmlrpcval(array(
                        'user_id'   => new xmlrpcval($subkey, 'string'),
                        'username'  => new xmlrpcval(mobiquo_encode($subitem), 'base64'),
                    ), 'struct');
                    array_push($return_to_users, $return_to_user);
                }
            }
        }
        else
        {
            if ($pm['fromuserid'] == $vbulletin->userinfo['userid'] || $key == $vbulletin->userinfo['userid'])
            {
                $return_to_user = new xmlrpcval(array(
                    'user_id'   => new xmlrpcval($key, 'string'),
                    'username'  => new xmlrpcval(mobiquo_encode($item), 'base64'),
                ), 'struct');
                array_push($return_to_users, $return_to_user);
            }
        }
    }
    
    if($params[1] === '0')
    {
        $icon_user_id = $pm['fromuserid'];
    }

    $show['quickreply'] = ($permissions['pmquota'] AND $vbulletin->userinfo['receivepm'] AND !fetch_privatemessage_throttle_reached($vbulletin->userinfo['userid']));

    $recipient = $db->query_first("
        SELECT usertextfield.*, user.*, userlist.type
        FROM " . TABLE_PREFIX . "user AS user
        LEFT JOIN " . TABLE_PREFIX . "usertextfield AS usertextfield ON(usertextfield.userid=user.userid)
        LEFT JOIN " . TABLE_PREFIX . "userlist AS userlist ON(user.userid = userlist.userid AND userlist.relationid = " . $vbulletin->userinfo['userid'] . " AND userlist.type = 'buddy')
        WHERE user.userid = " . intval($pm['fromuserid'])
    );
    
    if (!empty($recipient))
    {
        $recipient = array_merge($recipient , convert_bits_to_array($recipient['options'], $vbulletin->bf_misc_useroptions));
        cache_permissions($recipient, false);
        if (!($vbulletin->userinfo['permissions']['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['cancontrolpanel']) AND (!$recipient['receivepm'] OR !$recipient['permissions']['pmquota']
        OR ($recipient['receivepmbuddies'] AND !can_moderate() AND $recipient['type'] != 'buddy')
        ))
        {
            $show['quickreply'] = false;
        }
    }

    if($html_content)
    {
        $a = fetch_tag_list();
        unset($a['option']['quote']);
        unset($a['no_option']['quote']);
        unset($a['option']['url']);
        unset($a['no_option']['url']);

        $vbulletin->options['wordwrap']  = 0;
         
        $pm_text =preg_replace("/\[\/img\]/siU", '[/img1]', $pm_text);
        $bbcode_parser =& new vB_BbCodeParser($vbulletin, $a);

        $pm_text = $bbcode_parser->parse( $pm_text, $thread[forumid], false);
        $pm_text =preg_replace("/\[\/img1\]/siU", '[/IMG]', $pm_text);
         
        $pm_text =  htmlspecialchars_uni($pm_text);

        $pm_text = mobiquo_encode(post_content_clean($pm_text), '', false);
    } else {
        $pm_text = mobiquo_encode(post_content_clean($pm_text));
    }
    
    $return_message = array(
        'result'        => new xmlrpcval(true, 'boolean'),
        'msg_id'        => new xmlrpcval($pm['pmid'], 'string'),
        'sent_date'     => new xmlrpcval(mobiquo_iso8601_encode($pm['dateline']-$vbulletin->options['hourdiff'], $vbulletin->userinfo['tzoffset']), 'dateTime.iso8601'),
        'msg_from'      => new xmlrpcval(mobiquo_encode($pm['fromusername']), 'base64'),
        'msg_from_id'   => new xmlrpcval(mobiquo_encode($pm['fromuserid']), 'string'),
        'msg_subject'   => new xmlrpcval(mobiquo_encode($pm['title']), 'base64'),
        'text_body'     => new xmlrpcval($pm_text, 'base64'),
        'time_string'   => new xmlrpcval(format_time_string($pm['dateline']), 'base64'),
        'icon_url'      => new xmlrpcval(mobiquo_get_user_icon($icon_user_id), 'string'),
        'msg_to'        => new xmlrpcval($return_to_users, 'array'),
        
        'allow_smilies' => new xmlrpcval($pm['allowsmilie'], 'boolean'),
    );
    
    $userinfo = fetch_userinfo($icon_user_id);
    $mobiquo_user_online = fetch_online_status($userinfo) ? true : false;
    if ($mobiquo_user_online) $return_message['is_online'] = new xmlrpcval(true, 'boolean');
    //if ($pm['allowsmilie']) $return_message['allow_smilies'] = new xmlrpcval(true, 'boolean');
   

    if (defined('NOSHUTDOWNFUNC'))
    {
        exec_shut_down();
    }
    
    return new xmlrpcresp(new xmlrpcval($return_message, 'struct'));
}

function delete_message_func($xmlrpc_params)
{
    global $vbulletin, $permissions, $db, $messagecounters, $vbphrase;
    
    $params = php_xmlrpc_decode($xmlrpc_params);
    if (!$vbulletin->options['enablepms'])
    {
        return_fault(fetch_error('pm_adminoff'));
    }

    // the following is the check for actions which allow creation of new pms
    if ($permissions['pmquota'] < 1 OR !$vbulletin->userinfo['receivepm'])
    {
        $show['createpms'] = false;
    }

    // check permission to use private messaging
    if (($permissions['pmquota'] < 1 AND (!$vbulletin->userinfo['pmtotal'] OR in_array($_REQUEST['do'], array('insertpm', 'newpm')))) OR !$vbulletin->userinfo['userid'])
    {
        return_fault();
    }

    if (!$vbulletin->userinfo['receivepm'] AND in_array($_REQUEST['do'], array('insertpm', 'newpm')))
    {
        return_fault(fetch_error('pm_turnedoff'));
    }

    // check that we have an array to work with


    // make sure the ids we are going to work with are sane
    $messageids = array();
    $messageids[] = $params[0];

    $pmids = array();
    $textids = array();

    // get the pmid and pmtext id of messages to be deleted
    $pms = $db->query_read_slave("
        SELECT pmid
        FROM " . TABLE_PREFIX . "pm
        WHERE userid = " . $vbulletin->userinfo['userid'] . "
            AND pmid IN(" . implode(', ', $messageids) . ")
    ");

    // check to see that we still have some ids to work with
    if ($db->num_rows($pms) == 0)
    {
        return_fault(fetch_error('invalidid', $vbphrase['private_message']));
    }

    // build the final array of pmids to work with
    while ($pm = $db->fetch_array($pms))
    {
        $pmids[] = $pm['pmid'];
    }

    // delete from the pm table using the results from above
    $deletePmSql = "DELETE FROM " . TABLE_PREFIX . "pm WHERE pmid IN(" . implode(', ', $pmids) . ")";
    $db->query_write($deletePmSql);

    build_pm_counters();
    if (defined('NOSHUTDOWNFUNC'))
    {
        exec_shut_down();
    }
    
    return new xmlrpcresp(
        new xmlrpcval(array(
            'result'        => new xmlrpcval(true, 'boolean'),
            'result_text'   => new xmlrpcval('', 'base64'),
        ), 'struct')
    );
}

function create_message_func($xmlrpc_params)
{
    global $vbulletin, $permissions, $db, $messagecounters;
    
    $params = php_xmlrpc_decode($xmlrpc_params);
    $show['messagelist'] = true;
    if(!$vbulletin->options['enablepms'])
    {
        return_fault(fetch_error('pm_adminoff'));
    }

    // the following is the check for actions which allow creation of new pms
    if ($permissions['pmquota'] < 1 OR !$vbulletin->userinfo['receivepm'])
    {
        $show['createpms'] = false;
    }

    // check permission to use private messaging
    if (($permissions['pmquota'] < 1 AND (!$vbulletin->userinfo['pmtotal'] OR in_array($_REQUEST['do'], array('insertpm', 'newpm')))) OR !$vbulletin->userinfo['userid'])
    {
        return_fault();
    }

    if (!$vbulletin->userinfo['receivepm'] AND in_array($_REQUEST['do'], array('insertpm', 'newpm')))
    {
        return_fault(fetch_error('pm_turnedoff'));
    }

    if ($permissions['pmquota'] < 1)
    {
        return_fault();
    }
    else if (!$vbulletin->userinfo['receivepm'])
    {
        return_fault(fetch_error('pm_turnedoff'));
    }
    
    $vbulletin->GPC['message'] = mobiquo_encode($params[2], 'to_local');
    // include useful functions
    require_once(DIR . '/includes/functions_newpost.php');
    
    $vbulletin->GPC['message'] = $vbulletin->GPC['message'];
    
    // parse URLs in message text
    if ($vbulletin->options['privallowbbcode'])
    {
        $vbulletin->GPC['message'] = convert_url_to_bbcode($vbulletin->GPC['message']);
    }

    if($params[3] == 1 && $params[4]) {
        $pm_id = $params[4];
    }

    if($params[3] == 2 && $params[4]) {
        $pm_id = $params[4];
        $forward = 1;
    }
    if(is_array($params[0])){
        $mobiquo_recipient = implode(';', $params[0]);
    } else {
        $mobiquo_recipient = $params[0];
    }
    $pm['message'] =$vbulletin->GPC['message'];
    $pm['title'] =  mobiquo_encode($params[1], 'to_local');
    $pm['parseurl'] = 1;
    $pm['savecopy'] = 1;
    $pm['signature'] = 1;
    $pm['disablesmilies'] =& $vbulletin->GPC['disablesmilies'];
    $pm['sendanyway'] =& $vbulletin->GPC['sendanyway'];
    $pm['receipt'] =& $vbulletin->GPC['receipt'];
    $pm['recipients'] =  mobiquo_encode($mobiquo_recipient, 'to_local');
    $pm['bccrecipients'] =& $vbulletin->GPC['bccrecipients'];
    $pm['pmid'] = $pm_id;
    $pm['iconid'] =& $vbulletin->GPC['iconid'];
    $pm['forward'] = $forward;
    $pm['folderid'] =& $vbulletin->GPC['folderid'];

    // *************************************************************
    // PROCESS THE MESSAGE AND INSERT IT INTO THE DATABASE

    $errors = array(); // catches errors

    if ($vbulletin->userinfo['pmtotal'] > $permissions['pmquota'] OR ($vbulletin->userinfo['pmtotal'] == $permissions['pmquota'] AND $pm['savecopy']))
    {
        $errors[] = fetch_error('yourpmquotaexceeded');
    }

    // create the DM to do error checking and insert the new PM
    $pmdm =& datamanager_init('PM', $vbulletin, ERRTYPE_ARRAY);

    $pmdm->set_info('savecopy',      $pm['savecopy']);
    $pmdm->set_info('receipt',       $pm['receipt']);
    $pmdm->set_info('cantrackpm',    $cantrackpm);
    $pmdm->set_info('forward',       $pm['forward']);
    $pmdm->set_info('bccrecipients', $pm['bccrecipients']);
    if ($vbulletin->userinfo['permissions']['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['cancontrolpanel'])
    {
        $pmdm->overridequota = true;
    }

    $pmdm->set('fromuserid', $vbulletin->userinfo['userid']);
    $pmdm->set('fromusername', $vbulletin->userinfo['username']);
    $pmdm->setr('title', $pm['title']);
    $pmdm->set_recipients($pm['recipients'], $permissions, 'cc');
    $pmdm->set_recipients($pm['bccrecipients'], $permissions, 'bcc');
    $pmdm->setr('message', $pm['message']);
    $pmdm->setr('iconid', $pm['iconid']);
    $pmdm->set('dateline', TIMENOW);
    $pmdm->setr('showsignature', $pm['signature']);
    $pmdm->set('allowsmilie', $pm['disablesmilies'] ? 0 : 1);
    if (!$pm['forward'])
    {
        $pmdm->set_info('parentpmid', $pm['pmid']);
    }
    $pmdm->set_info('replypmid', $pm['pmid']);
    $pmdm->pre_save();

    // deal with user using receivepmbuddies sending to non-buddies
    if ($vbulletin->userinfo['receivepmbuddies'] AND is_array($pmdm->info['recipients']))
    {
        $users_not_on_list = array();

        // get a list of super mod groups
        $smod_groups = array();
        foreach ($vbulletin->usergroupcache AS $ugid => $groupinfo)
        {
            if ($groupinfo['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['ismoderator'])
            {
                // super mod group
                $smod_groups[] = $ugid;
            }
        }

        // now filter out all moderators (and super mods) from the list of recipients
        // to check against the buddy list
        $check_recipients = $pmdm->info['recipients'];
        $mods = $db->query_read_slave("
            SELECT user.userid
            FROM " . TABLE_PREFIX . "user AS user
            LEFT JOIN " . TABLE_PREFIX . "moderator AS moderator ON (moderator.userid = user.userid)
            WHERE user.userid IN (" . implode(', ', array_keys($check_recipients)) . ")
                AND ((moderator.userid IS NOT NULL AND moderator.forumid <> -1)
                " . (!empty($smod_groups) ? "OR user.usergroupid IN (" . implode(', ', $smod_groups) . ")" : '') . "
                )
        ");
        while ($mod = $db->fetch_array($mods))
        {
            unset($check_recipients["$mod[userid]"]);
        }

        if (!empty($check_recipients))
        {
            // filter those on our buddy list out
            $users = $db->query_read_slave("
                SELECT userlist.relationid
                FROM " . TABLE_PREFIX . "userlist AS userlist
                WHERE userid = " . $vbulletin->userinfo['userid'] . "
                    AND userlist.relationid IN(" . implode(array_keys($check_recipients), ', ') . ")
                    AND type = 'buddy'
            ");
            while ($user = $db->fetch_array($users))
            {
                unset($check_recipients["$user[relationid]"]);
            }
        }

        // what's left must be those who are neither mods or on our buddy list
        foreach ($check_recipients AS $userid => $user)
        {
            $users_not_on_list["$userid"] = $user['username'];
        }

        if (!empty($users_not_on_list) AND (!$vbulletin->GPC['sendanyway'] OR !empty($errors)))
        {
            $users = '';
            foreach ($users_not_on_list AS $userid => $username)
            {
                $users .= "<li><a href=\"member.php?$session[sessionurl]u=$userid\" target=\"profile\">$username</a></li>";
            }
            $pmdm->error('pm_non_contacts_cant_reply', $users);
        }
    }
    
    // check for message flooding
//    if ($vbulletin->options['pmfloodtime'] > 0 AND !$vbulletin->GPC['preview'])
//    {
//        if (!($permissions['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['cancontrolpanel']) AND !can_moderate())
//        {
//            $floodcheck = $db->query_first("
//                SELECT pmtextid, title, dateline
//                FROM " . TABLE_PREFIX . "pmtext AS pmtext
//                WHERE fromuserid = " . $vbulletin->userinfo['userid'] . "
//                ORDER BY dateline DESC
//            ");
//
//            if (($timepassed = TIMENOW - $floodcheck['dateline']) < $vbulletin->options['pmfloodtime'])
//            {
//                $errors[] = fetch_error('pmfloodcheck', $vbulletin->options['pmfloodtime'], ($vbulletin->options['pmfloodtime'] - $timepassed));
//            }
//        }
//    }
    
    // process errors if there are any
    $errors = array_merge($errors, $pmdm->errors);

    if (!empty($errors))
    {
        $error_string = mobiquo_encode(implode('', $errors));
        return_fault($error_string);
    }
    else if ($vbulletin->GPC['preview'] != '')
    {
        define('PMPREVIEW', 1);
        $foruminfo = array(
            'forumid' => 'privatemessage',
            'allowicons' => $vbulletin->options['privallowicons']
        );
        $preview = process_post_preview($pm);
        $_REQUEST['do'] = 'newpm';
    }
    else
    {
        // everything's good!
        $created_pm_id = $pmdm->save();

        // force pm counters to be rebuilt
        $vbulletin->userinfo['pmunread'] = -1;
        build_pm_counters();
        if (defined('NOSHUTDOWNFUNC'))
        {
            exec_shut_down();
        }
        
        return new xmlrpcresp(
            new xmlrpcval(array(
                'result'        => new xmlrpcval(true, 'boolean'),
                'result_text'   => new xmlrpcval('', 'base64'),
                'msg_id'        => new xmlrpcval($created_pm_id, 'string'),
            ), 'struct')
        );
    }
}

function mark_pm_unread_func($xmlrpc_params)
{
    global $vbulletin, $permissions, $db;
    
    $params = php_xmlrpc_decode($xmlrpc_params);
    
    if (!$vbulletin->options['enablepms'])
    {
        return_fault(fetch_error('pm_adminoff'));
    }

    $messageids = array_map('intval', explode('-', $params[0]));

    $db->query_write("UPDATE " . TABLE_PREFIX . "pm SET messageread=0 WHERE userid=" . $vbulletin->userinfo['userid'] . " AND pmid IN (" . implode(', ', $messageids) . ")");
    build_pm_counters();

    // deselect messages
    setcookie('vbulletin_inlinepm', '', TIMENOW - 3600, '/');

    if (defined('NOSHUTDOWNFUNC'))
    {
        exec_shut_down();
    }
    
    return new xmlrpcresp(
        new xmlrpcval(array(
            'result'        => new xmlrpcval(true, 'boolean'),
            'result_text'   => new xmlrpcval('', 'base64'),
        ), 'struct')
    );
}

function get_quote_pm_func($xmlrpc_params)
{
    global $vbulletin, $permissions, $db, $messagecounters;
    
    $params = php_xmlrpc_decode($xmlrpc_params);
    if (!$vbulletin->options['enablepms'])
    {
        return_fault(fetch_error('pm_adminoff'));
    }

    // the following is the check for actions which allow creation of new pms
    if ($permissions['pmquota'] < 1 OR !$vbulletin->userinfo['receivepm'])
    {
        $show['createpms'] = false;
    }

    // check permission to use private messaging
    if (($permissions['pmquota'] < 1 AND (!$vbulletin->userinfo['pmtotal'] OR in_array($_REQUEST['do'], array('insertpm', 'newpm')))) OR !$vbulletin->userinfo['userid'])
    {
        return_fault();
    }

    if (!$vbulletin->userinfo['receivepm'] AND in_array($_REQUEST['do'], array('insertpm', 'newpm')))
    {
        return_fault(fetch_error('pm_turnedoff'));
    }
    
    require_once(DIR . '/includes/functions_newpost.php');
    $messageid = $params[0];
    if($pm = $vbulletin->db->query_first_slave("
        SELECT pm.*, pmtext.*
        FROM " . TABLE_PREFIX . "pm AS pm
        LEFT JOIN " . TABLE_PREFIX . "pmtext AS pmtext ON(pmtext.pmtextid = pm.pmtextid)
        WHERE pm.userid=" . $vbulletin->userinfo['userid'] . " AND pm.pmid=" . $messageid . "
    "))
    {
        $pm = fetch_privatemessage_reply($pm);
    }

    $return_message = array(
        'result'        => new xmlrpcval(true, 'boolean'),
        'msg_id'        => new xmlrpcval($pm['pmid'], 'string'),
        'msg_subject'   => new xmlrpcval(mobiquo_encode($pm['title']), 'base64'),
        'text_body'     => new xmlrpcval(mobiquo_encode($pm['message']), 'base64'),
    );
    
    if (defined('NOSHUTDOWNFUNC'))
    {
        exec_shut_down();
    }
    
    return new xmlrpcresp(new xmlrpcval($return_message, 'struct'));
}

function report_pm_func($xmlrpc_params)
{
    global $vbulletin, $permissions, $db, $vbphrase;
    
    $decode_params = php_xmlrpc_decode($xmlrpc_params);

    $show['messagelist'] = true;
    if(!$vbulletin->options['enablepms'])
    {
        return_fault(fetch_error('pm_adminoff'));
    }

    // the following is the check for actions which allow creation of new pms
    if ($permissions['pmquota'] < 1 OR !$vbulletin->userinfo['receivepm'])
    {
        $show['createpms'] = false;
    }

    // check permission to use private messaging
    if (($permissions['pmquota'] < 1 AND (!$vbulletin->userinfo['pmtotal'] OR in_array($_REQUEST['do'], array('insertpm', 'newpm')))) OR !$vbulletin->userinfo['userid'])
    {
        return_fault();
    }

    if (!$vbulletin->userinfo['receivepm'] AND in_array($_REQUEST['do'], array('insertpm', 'newpm')))
    {
        return_fault(fetch_error('pm_turnedoff'));
    }

    $vbulletin->GPC['pmid'] = intval($decode_params[0]);

    if(isset($decode_params[1]) && strlen($decode_params[1]) > 0){
        $report_message= mobiquo_encode($decode_params[1], 'to_local');
    } else {
        $report_message= mobiquo_encode("report", 'to_local');
    }
    
    $vbulletin->GPC['reason'] = $report_message;
    $reportthread = ($rpforumid = $vbulletin->options['rpforumid'] AND $rpforuminfo = fetch_foruminfo($rpforumid));
    $reportemail = ($vbulletin->options['enableemail'] AND $vbulletin->options['rpemail']);

    if (!$reportthread AND !$reportemail)
    {
        return_fault(fetch_error('emaildisabled'));
    }

    $pminfo = $db->query_first_slave("
                SELECT
                    pm.*, pmtext.*
                FROM " . TABLE_PREFIX . "pm AS pm
                LEFT JOIN " . TABLE_PREFIX . "pmtext AS pmtext ON(pmtext.pmtextid = pm.pmtextid)
                WHERE pm.userid=" . $vbulletin->userinfo['userid'] . " AND pm.pmid=" . $vbulletin->GPC['pmid'] . "
            ");

    if (!$pminfo)
    {
        return_fault(fetch_error('invalidid', $vbphrase['private_message']));
    }

    require_once(DIR . '/includes/class_reportitem.php');
    $reportobj = new vB_ReportItem_PrivateMessage($vbulletin);
    $reportobj->set_extrainfo('pm', $pminfo);
    $perform_floodcheck = $reportobj->need_floodcheck();

    if ($perform_floodcheck)
    {
        $reportobj->perform_floodcheck_precommit();
    }

    if ($vbulletin->GPC['reason'] == '')
    {
        return_fault(fetch_error('noreason'));
    }

    $reportobj->do_report($vbulletin->GPC['reason'], $pminfo);
    
    return new xmlrpcresp(
        new xmlrpcval(array(
            'result'        => new xmlrpcval(true, 'boolean'),
            'result_text'   => new xmlrpcval('', 'base64'),
        ), 'struct')
    );
}
