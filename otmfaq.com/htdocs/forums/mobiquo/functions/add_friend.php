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


// ####################### SET PHP ENVIRONMENT ###########################


// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE & ~8192);

define('GET_EDIT_TEMPLATES', 'editsignature,updatesignature');
define('THIS_SCRIPT', 'profile');
define('CSRF_PROTECTION', false);


$phrasegroups = array('user', 'timezone', 'posting', 'cprofilefield', 'cppermission');

$specialtemplates = array(
    'smiliecache',
    'bbcodecache',
    'banemail',
    'ranks',
    'noavatarperms'
);

$globaltemplates = array(
    'USERCP_SHELL',
    'usercp_nav_folderbit'
);

$actiontemplates = array(
    'editprofile' => array(
        'modifyprofile',
        'modifyprofile_birthday',
        'userfield_checkbox_option',
        'userfield_optional_input',
        'userfield_radio',
        'userfield_radio_option',
        'userfield_select',
        'userfield_select_option',
        'userfield_select_multiple',
        'userfield_textarea',
        'userfield_textbox',
        'userfield_wrapper',
),
    'editoptions' => array(
        'modifyoptions',
        'modifyoptions_timezone',
        'userfield_checkbox_option',
        'userfield_optional_input',
        'userfield_radio',
        'userfield_radio_option',
        'userfield_select',
        'userfield_select_option',
        'userfield_select_multiple',
        'userfield_textarea',
        'userfield_textbox',
        'userfield_wrapper',
),
    'editavatar' => array(
        'modifyavatar',
        'help_avatars_row',
        'modifyavatar_category',
        'modifyavatarbit',
        'modifyavatarbit_custom',
        'modifyavatarbit_noavatar',
),
    'editusergroups' => array(
        'modifyusergroups',
        'modifyusergroups_joinrequestbit',
        'modifyusergroups_memberbit',
        'modifyusergroups_nonmemberbit',
        'modifyusergroups_displaybit',
        'modifyusergroups_groupleader',
),
    'editsignature' => array(
        'modifysignature',
        'forumrules'
        ),
    'updatesignature' => array(
        'modifysignature',
        'forumrules'
        ),
    'editpassword' => array(
        'modifypassword'
        ),
    'editprofilepic' => array(
        'modifyprofilepic'
        ),
    'joingroup' => array(
        'modifyusergroups_requesttojoin',
        'modifyusergroups_groupleader'
        ),
    'editattachments' => array(
        'GENERIC_SHELL',
        'modifyattachmentsbit',
        'modifyattachments'
        ),
    'addlist' => array(
        'modifyuserlist_confirm',
        ),
    'removelist' => array(
        'modifyuserlist_confirm',
        ),
    'buddylist' => array(
        'modifybuddylist',
        'modifybuddylist_user',
        'modifyuserlist_headinclude',
        ),
    'ignorelist' => array(
        'modifyignorelist',
        'modifyignorelist_user',
        'modifyuserlist_headinclude',
        ),
    'customize' => array(
        'memberinfo_usercss',
        'modifyusercss',
        'modifyusercss_backgroundbit',
        'modifyusercss_backgroundrow',
        'modifyusercss_bit',
        'modifyusercss_error',
        'modifyusercss_error_link',
        'modifyusercss_headinclude',
        'modifyprivacy_bit',
        ),
    'privacy' => array(
        'modifyprofileprivacy',
        'modifyprivacy_bit'
        ),
    'doprivacy' => array(
        'modifyprofileprivacy',
        'modifyprivacy_bit'
        )
        );
        $actiontemplates['docustomize'] = $actiontemplates['customize'];

        $actiontemplates['none'] =& $actiontemplates['editprofile'];

        require_once('./global.php');
        require_once(DIR . '/includes/functions_user.php');
        
        // #######################################################################
        // ######################## START MAIN SCRIPT ############################
        // #######################################################################


        if (!($permissions['forumpermissions'] & $vbulletin->bf_ugp_forumpermissions['canview']))
        {
            $return = array(20, 'security error (user may not have permission to access this feature)');
            return_fault($return);
        }

        if (empty($vbulletin->userinfo['userid']))
        {
            $return = array(20, 'security error (user may not have permission to access this feature)');
            return_fault($return);
        }


        function add_friend_func($xmlrpc_params){
            global $permissions, $vbulletin, $db;
                
                
            $params = php_xmlrpc_decode($xmlrpc_params);
            if(!$params[0])
            {
                $return = array(2, 'no  user id param.');
                return_fault($return);
            }


            $user_name =    mobiquo_encode($params[0], 'to_local');
            $user_id   = get_userid_by_name($user_name);

            if(!$user_id){

                $return = array(7, 'invalid user id');
                return_fault($return);
            }

            $vbulletin->GPC['userid'] = $user_id;
            $vbulletin->GPC['userlist'] = 'friend';

            if ($vbulletin->GPC['userlist'] == 'friend' AND (!($vbulletin->options['socnet'] & $vbulletin->bf_misc_socnet['enable_friends']) OR !($vbulletin->userinfo['permissions']['genericpermissions2'] & $vbulletin->bf_ugp_genericpermissions2['canusefriends'])))
            {
                $vbulletin->GPC['userlist'] = 'buddy';
            }


            $userinfo = mobiquo_verify_id('user', $vbulletin->GPC['userid'], true, true, FETCH_USERINFO_ISFRIEND);
            cache_permissions($userinfo);

            if ($vbulletin->GPC['userlist'] == 'buddy' OR $vbulletin->GPC['userlist'] == 'friend')
            {
                // No slave here
                $ouruser = $db->query_first("
            SELECT friend
            FROM " . TABLE_PREFIX . "userlist
            WHERE relationid = $userinfo[userid]
                AND userid = " . $vbulletin->userinfo['userid'] . "
                AND type = 'buddy'
        ");
                if ($vbulletin->GPC['userlist'] == 'friend')
                {
                    if ($ouruser['friend'] == 'pending' OR $ouruser['friend'] == 'denied')
                    {    // We are pending friends
                            
                        eval('    $message = "' .get_vb_message('redirect_friendspending') . '";');
                        $return  = new xmlrpcresp(
                        new xmlrpcval(
                        array(
                                        'result' => new xmlrpcval(false, 'boolean'),
                                      'display_text' => new xmlrpcval($message, 'base64')),
                                          'struct'
                                          )
                                          );

                                          return $return;
                    }
                    else if ($ouruser['friend'] == 'yes')
                    {    // We are already friends
                        eval('    $message = "' .get_vb_message('redirect_friendsalready') . '";');
                        $return  = new xmlrpcresp(
                        new xmlrpcval(
                        array(
                                        'result' => new xmlrpcval(false, 'boolean'),
                                      'display_text' => new xmlrpcval($message, 'base64')),
                                          'struct'
                                          )
                                          );

                                          return $return;

                    }
                    else if ($vbulletin->GPC['userid'] == $vbulletin->userinfo['userid'])
                    { // You can't be friends with yourself
                        eval('    $message = "' .get_vb_message('redirect_friendswithself') . '";');
                        $return  = new xmlrpcresp(
                        new xmlrpcval(
                        array(
                                        'result' => new xmlrpcval(false, 'boolean'),
                                      'display_text' => new xmlrpcval($message, 'base64')),
                                          'struct'
                                          )
                                          );

                                          return $return;
                    }
                }
                else if ($ouruser)
                {
                    if ($ouruser['friend'] == 'yes')
                    {
                        eval('    $message = "' .get_vb_message('redirect_friendsalready') . '";');
                        $return  = new xmlrpcresp(
                        new xmlrpcval(
                        array(
                                        'result' => new xmlrpcval(false, 'boolean'),
                                      'display_text' => new xmlrpcval($message, 'base64')),
                                          'struct'
                                          )
                                          );

                                          return $return;
                    }
                    else
                    {
                        eval('    $message = "' .get_vb_message('redirect_contactsalready') . '";');
                        $return  = new xmlrpcresp(
                        new xmlrpcval(
                        array(
                                        'result' => new xmlrpcval(false, 'boolean'),
                                      'display_text' => new xmlrpcval($message, 'base64')),
                                          'struct'
                                          )
                                          );

                                          return $return;
                    }
                }
            }

            switch ($vbulletin->GPC['userlist'])
            {
                case 'friend':
                    $friend_checked = ' checked="checked"';
                case 'buddy':
                    if ($userinfo['requestedfriend'])
                    {
                        $confirm_phrase = 'confirm_friendship_request_from_x';
                        $show['friend_checkbox'] = false;
                        $show['hiddenfriend'] = true;
                    }
                    else
                    {
                        $confirm_phrase = 'add_x_to_contacts_confirm';
                        $supplemental_phrase = 'also_send_friend_request_to_x';
                        $show['friend_checkbox'] = ($vbulletin->options['socnet'] & $vbulletin->bf_misc_socnet['enable_friends'] AND $userinfo['permissions']['genericpermissions2'] & $vbulletin->bf_ugp_genericpermissions2['canusefriends']);
                    }


                    break;
                case 'ignore':
                    $uglist = $userinfo['usergroupid'] . (trim($userinfo['membergroupids']) ? ", $userinfo[membergroupids]" : '');
                    if (!$vbulletin->options['ignoremods'] AND can_moderate(0, '', $userinfo['userid'], $uglist) AND !($permissions['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['cancontrolpanel']))
                    {
                        $return = array(20, 'security error (user may not have permission to access this feature)');
                        return_fault($return);
                    }
                    else if ($vbulletin->userinfo['userid'] == $userinfo['userid'])
                    {
                        $return = array(20, 'security error (user may not have permission to access this feature)');
                        return_fault($return);
                    }


                    break;
                default:
                    $return = array(20, 'security error (user may not have permission to access this feature)');
                    return_fault($return);
            }





            // no referring URL, send them back to the profile page
            if ($vbulletin->url == $vbulletin->options['forumhome'] . '.php')
            {
                $vbulletin->url = 'member.php?' . $vbulletin->session->vars['sessionurl'] . "u=$userinfo[userid]";
            }


            if ($vbulletin->GPC['userlist'] == 'friend' AND (!($vbulletin->options['socnet'] & $vbulletin->bf_misc_socnet['enable_friends']) OR !($userinfo['permissions']['genericpermissions2'] & $vbulletin->bf_ugp_genericpermissions2['canusefriends']) OR !($vbulletin->userinfo['permissions']['genericpermissions2'] & $vbulletin->bf_ugp_genericpermissions2['canusefriends'])))
            {
                $vbulletin->GPC['userlist'] = 'buddy';
            }

            $users = array();
            switch ($vbulletin->GPC['userlist'])
            {
                case 'friend':
                case 'buddy':

                    // No slave here
                    $ouruser = $db->query_first("
                SELECT friend
                FROM " . TABLE_PREFIX . "userlist
                WHERE relationid = $userinfo[userid]
                    AND userid = " . $vbulletin->userinfo['userid'] . "
                    AND type = 'buddy'
            ");
                    break;
                case 'ignore':
                    $uglist = $userinfo['usergroupid'] . (trim($userinfo['membergroupids']) ? ", $userinfo[membergroupids]" : '');
                    if (!$vbulletin->options['ignoremods'] AND can_moderate(0, '', $userinfo['userid'], $uglist) AND !($permissions['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['cancontrolpanel']))
                    {
                        $return = array(20, 'security error (user may not have permission to access this feature)');
                        return_fault($return);
                    }
                    else if ($vbulletin->userinfo['userid'] == $userinfo['userid'])
                    {
                        $return = array(20, 'security error (user may not have permission to access this feature)');
                        return_fault($return);
                    }

                    $db->query_write("
                INSERT IGNORE INTO " . TABLE_PREFIX . "userlist
                    (userid, relationid, type, friend)
                VALUES
                    (" . $vbulletin->userinfo['userid'] . ", " . intval($userinfo['userid']) . ", 'ignore', 'no')
            ");
                    $users[] = $vbulletin->userinfo['userid'];
                    $redirect_phrase = 'redirect_addlist_ignore';
                    break;
                default:
                    $return = array(20, 'security error (user may not have permission to access this feature)');
                    return_fault($return);
            }

            if ($vbulletin->GPC['userlist'] == 'buddy')
            { // if an entry exists already then we're fine
                if (empty($ouruser))
                {
                    $db->query_write("
                INSERT IGNORE INTO " . TABLE_PREFIX . "userlist
                    (userid, relationid, type, friend)
                VALUES
                    (" . $vbulletin->userinfo['userid'] . ", " . intval($userinfo['userid']) . ", 'buddy', 'no')
            ");
                    $users[] = $vbulletin->userinfo['userid'];
                }
                $redirect_phrase = 'redirect_addlist_contact';
            }
            else if ($vbulletin->GPC['userlist'] == 'friend')
            {
                if ($ouruser['friend'] == 'pending' OR $ouruser['friend'] == 'denied')
                {    // We are pending friends
                    eval('    $message = "' .get_vb_message('redirect_friendspending') . '";');
                    $return  = new xmlrpcresp(
                    new xmlrpcval(
                    array(
                                        'result' => new xmlrpcval(false, 'boolean'),
                                      'display_text' => new xmlrpcval($message, 'base64')),
                                          'struct'
                                          )
                                          );

                                          return $return;
                }
                else if ($ouruser['friend'] == 'yes')
                {    // We are already friends
                    eval('    $message = "' .get_vb_message('redirect_friendsalready') . '";');
                    $return  = new xmlrpcresp(
                    new xmlrpcval(
                    array(
                                        'result' => new xmlrpcval(false, 'boolean'),
                                      'display_text' => new xmlrpcval($message, 'base64')),
                                          'struct'
                                          )
                                          );

                                          return $return;
                }
                else if ($vbulletin->GPC['userid'] == $vbulletin->userinfo['userid'])
                { // You can't be friends with yourself
                    eval('    $message = "' .get_vb_message('redirect_friendswithself') . '";');
                    $return  = new xmlrpcresp(
                    new xmlrpcval(
                    array(
                                        'result' => new xmlrpcval(false, 'boolean'),
                                      'display_text' => new xmlrpcval($message, 'base64')),
                                          'struct'
                                          )
                                          );

                                          return $return;
                }

                // No slave here
                if ($db->query_first("
            SELECT friend
            FROM " . TABLE_PREFIX . "userlist
            WHERE userid = $userinfo[userid]
                AND relationid = " . $vbulletin->userinfo['userid'] . "
                AND type = 'buddy'
                AND (friend = 'pending' OR friend = 'denied')
        "))
                {
                    // Make us friends
                    $db->query_write("
                REPLACE INTO " . TABLE_PREFIX . "userlist
                    (userid, relationid, type, friend)
                VALUES
                    ({$vbulletin->userinfo['userid']}, $userinfo[userid], 'buddy', 'yes'),
                    ($userinfo[userid], {$vbulletin->userinfo['userid']}, 'buddy', 'yes')
            ");

                    $db->query_write("
                UPDATE " . TABLE_PREFIX . "user
                SET friendcount = friendcount + 1
                WHERE userid IN ($userinfo[userid], " . $vbulletin->userinfo['userid'] . ")
            ");

                    $db->query_write("
                UPDATE " . TABLE_PREFIX . "user
                SET friendreqcount = IF(friendreqcount > 0, friendreqcount - 1, 0)
                WHERE userid = " . $vbulletin->userinfo['userid']
                    );

                    $users[] = $vbulletin->userinfo['userid'];
                    $users[] = $userinfo['userid'];
                    $redirect_phrase = 'redirect_friendadded';
                }
                else
                {
                    $db->query_write("
                REPLACE INTO " . TABLE_PREFIX . "userlist
                    (userid, relationid, type, friend)
                VALUES
                    ({$vbulletin->userinfo['userid']}, $userinfo[userid], 'buddy', 'pending')
            ");

                    $cansendemail = (($userinfo['adminemail'] OR $userinfo['showemail']) AND $vbulletin->options['enableemail'] AND $vbulletin->userinfo['permissions']['genericpermissions'] & $vbulletin->bf_ugp_genericpermissions['canemailmember']);
                    if ($cansendemail AND $userinfo['options'] & $vbulletin->bf_misc_useroptions['receivefriendemailrequest'])
                    {
                        $touserinfo =& $userinfo;
                        $fromuserinfo =& $vbulletin->userinfo;


                        eval(fetch_email_phrases('friendship_request_email', $touserinfo['languageid']));
                        require_once(DIR . '/includes/class_bbcode_alt.php');
                        $plaintext_parser = new vB_BbCodeParser_PlainText($vbulletin, fetch_tag_list());
                        $plaintext_parser->set_parsing_language($touserinfo['languageid']);
                        $message = $plaintext_parser->parse($message, 'privatemessage');
                        vbmail($touserinfo['email'], $subject, $message);
                    }

                    $db->query_write("
                UPDATE " . TABLE_PREFIX . "user
                SET friendreqcount = friendreqcount + 1
                WHERE userid = " . $userinfo['userid']
                    );

                    $users[] = $vbulletin->userinfo['userid'];
                    $redirect_phrase = 'redirect_friendrequested';
                }
            }

            require_once(DIR . '/includes/functions_databuild.php');
            foreach($users AS $userid)
            {
                build_userlist($userid);
            }




            if (defined('NOSHUTDOWNFUNC'))
            {
                exec_shut_down();
            }

            return      new xmlrpcresp(
            new xmlrpcval(
            array(
                                        'result' => new xmlrpcval(true, 'boolean'),
                                      'display_text' => new xmlrpcval("", 'base64')),
                                          'struct'
                                          )
                                          );
        }

        ?>
