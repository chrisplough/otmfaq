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

@set_time_limit(0);

define('GET_EDIT_TEMPLATES', true);
define('THIS_SCRIPT', 'newattachment');
define('CSRF_PROTECTION', false);

$phrasegroups = array('posting');
$specialtemplates = array();
$globaltemplates = array();
$actiontemplates = array();

require_once('./global.php');
require_once(DIR . '/includes/functions_newpost.php');
require_once(DIR . '/includes/functions_file.php');
require_once(DIR . '/packages/vbattach/attach.php');


function remove_attachment_func($xmlrpc_params)
{
    global $vbulletin, $db, $forumperms, $permissions;

    $decode_params = php_xmlrpc_decode($xmlrpc_params);
    $attachmentid = intval($decode_params[0]);
    $group_id = $decode_params[2];
    $forumid = intval($decode_params[1]);
    if (!$vbulletin->userinfo['userid'] OR empty($vbulletin->userinfo['attachmentextensions'])) // Guests can not post attachments
    {
        $return = array(20, 'security error (user may not have permission to access this feature)');
        return_fault($return);
    }

    // Variables that are reused in templates
    $vbulletin->input->clean_gpc('f', 'attachment',    TYPE_FILE);
    $posthash  = $group_id;
    $vbulletin->GPC['posthash'] = $posthash ;

    if (!($attachlib =& vB_Attachment_Store_Library::fetch_library($vbulletin, 1, 0, array('f' => $forumid, 'posthash' => $posthash)))
        OR!$attachlib->verify_permissions())
    {
        $return = array(20, 'security error (user may not have permission to access this feature)');
        return_fault($return);
    }

    if (!$attachlib->fetch_attachcount())
    {
        $return = array(20, 'security error (user may not have permission to access this feature)');
        return_fault($return);
    }

    $attachlib->delete(array($attachmentid => "0"));

    return new xmlrpcresp(new xmlrpcval(array(
        'result' => new xmlrpcval(true, 'boolean'),
    ), 'struct'));
}
