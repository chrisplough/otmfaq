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

include('./include/xmlrpc.inc');
include('./include/xmlrpcs.inc');
// ####################### SET PHP ENVIRONMENT ###########################

@set_time_limit(0);

define('CWD1', (($getcwd = getcwd()) ? $getcwd : '.'));

error_reporting(0);

$phrasegroups = array();
$specialtemplates = array();
$globaltemplates = array();
$actiontemplates = array();

require_once(CWD1.'/include/common.php');

define('SCRIPT_ROOT', get_root_dir());
chdir(SCRIPT_ROOT);

define('GET_EDIT_TEMPLATES', true);
define('THIS_SCRIPT', 'newattachment');
define('CSRF_PROTECTION', false);

$phrasegroups = array('posting');
$specialtemplates = array();
$globaltemplates = array();
$actiontemplates = array();

$_POST['f'] = $_POST['forum_id'];
$_REQUEST['f'] = $_REQUEST['forum_id'];

require_once('./global.php');
require_once(DIR . '/includes/functions_newpost.php');
require_once(DIR . '/includes/functions_file.php');
require_once(DIR . '/packages/vbattach/attach.php');

$method = $_POST['method_name'];
if(isset($vbulletin) && $vbulletin->userinfo['userid'] != 0){
    header('Mobiquo_is_login:true');
} else {
    header('Mobiquo_is_login:false');
}

$server_param = array(
    'upload_attach' => array(
        'function' => 'upload_attach_func',
        'signature' => array(array($xmlrpcStruct)),
    ),
    
    'set_avatar' => array(
        'function' => 'upload_avatar_func',
        'signature' => array(array($xmlrpcStruct)),
    ),
    
    'upload_avatar' => array(
        'function' => 'upload_avatar_func',
        'signature' => array(array($xmlrpcStruct)),
    ),
);

$rpcServer = new xmlrpc_server($server_param, false);
$xml = new xmlrpcmsg($method);
$request = $xml->serialize();
$rpcServer->compress_response = 'true';
$rpcServer->response_charset_encoding ='UTF-8';
$response = $rpcServer->service($request);


function upload_attach_func()
{
    global $vbulletin;
    
    chdir(CWD1);
    chdir('../');

    error_reporting(0);

    $group_id = $_POST['group_id'];

    if (!$vbulletin->userinfo['userid'] || empty($vbulletin->userinfo['attachmentextensions'])) // Guests can not post attachments
    {
        return_fault();
    }

    // Variables that are reused in templates
    if(isset($group_id) && $group_id != null && strlen($group_id) == 32){
        $posthash = $group_id;
    } else {
        $posthash = md5(TIMENOW . $vbulletin->userinfo['userid'] . $vbulletin->userinfo['salt']);
    }
    $vbulletin->input->clean_gpc('f', 'attachment', TYPE_FILE);

    $vbulletin->GPC['posthash'] = $posthash ;
    $forumid = $vbulletin->GPC['forumid'];
    if (
    !($attachlib =& vB_Attachment_Store_Library::fetch_library($vbulletin, 1, 0, array('f' => $forumid, 'posthash' => $posthash)))
    OR
    !$attachlib->verify_permissions()
    )
    {
        return_fault();
    }

    if (!$attachlib->fetch_attachcount())
    {
        return_fault();
    }

    $uploadids = $attachlib->upload($vbulletin->GPC['attachment'], array(), array());
    $uploads = explode(', ', $uploadids);

    // if $uploads > 1 then we are in a case where $currentattachment isn't used
    $attachmentid= $uploads[0];

    //($hook = vBulletinHook::fetch_hook('newattachment_attach')) ? eval($hook) : false;

    if (!empty($attachlib->errors))
    {
        $errorlist = '';
        foreach ($attachlib->errors AS $error)
        {
            $filename = htmlspecialchars_uni($error['filename']);
            $errorlist .= $error['error'];
        }
        
        return new xmlrpcresp(new xmlrpcval(array(
            'result'        => new xmlrpcval(false, 'boolean'),
            'result_text'   => new xmlrpcval(mobiquo_encode($errorlist), 'base64')
        ), 'struct'));
    }
    else
    {
        return new xmlrpcresp(new xmlrpcval(array(
            'result'        => new xmlrpcval(true, 'boolean'),
            'attachment_id' => new xmlrpcval($attachmentid, 'string'),
            'group_id'      => new xmlrpcval($posthash, 'string'),
        ), 'struct'));
    }
}

function upload_avatar_func()
{
    global $vbulletin, $permissions;

    if (!($permissions['genericpermissions'] & $vbulletin->bf_ugp_genericpermissions['canmodifyprofile']))
    {
        return_fault();
    }

    if (!$vbulletin->options['avatarenabled'])
    {
        return_fault(fetch_error('avatardisabled'));
    }

    $vbulletin->GPC['avatarid'] = 0;

    if ($vbulletin->GPC['avatarid'] == 0 AND ($vbulletin->userinfo['permissions']['genericpermissions'] & $vbulletin->bf_ugp_genericpermissions['canuseavatar']))
    {
        $vbulletin->input->clean_gpc('f', 'upload', TYPE_FILE);

        // begin custom avatar code
        require_once(DIR . '/includes/class_upload.php');
        require_once(DIR . '/includes/class_image.php');

        $upload = new vB_Upload_Userpic($vbulletin);

        $upload->data =& datamanager_init('Userpic_Avatar', $vbulletin, ERRTYPE_STANDARD, 'userpic');
        $upload->image =& vB_Image::fetch_library($vbulletin);
        $upload->maxwidth = $vbulletin->userinfo['permissions']['avatarmaxwidth'];
        $upload->maxheight = $vbulletin->userinfo['permissions']['avatarmaxheight'];
        $upload->maxuploadsize = $vbulletin->userinfo['permissions']['avatarmaxsize'];
        $upload->allowanimation = ($vbulletin->userinfo['permissions']['genericpermissions'] & $vbulletin->bf_ugp_genericpermissions['cananimateavatar']) ? true : false;

        if (!$upload->process_upload($vbulletin->GPC['avatarurl']))
        {
            $errors = $upload->fetch_error();
            $errorlist = '';
            foreach ($errors AS $error)
            {
                $filename = htmlspecialchars_uni($error['filename']);
                $errorlist .= $error['error'];
            }

            return new xmlrpcresp(
                new xmlrpcval(array(
                    'attachment_id' => new xmlrpcval(0, 'string'),
                    'result'        => new xmlrpcval(false, 'boolean'),
                    'result_text'   => new xmlrpcval(mobiquo_encode($errorlist), 'base64')
                ), 'struct')
            );
        }
    }

    // init user data manager
    $userdata =& datamanager_init('User', $vbulletin, ERRTYPE_STANDARD);
    $userdata->set_existing($vbulletin->userinfo);
    $userdata->set('avatarid', $vbulletin->GPC['avatarid']);
    $userdata->save();

    return new xmlrpcresp(
        new xmlrpcval(array(
            'attachment_id' => new xmlrpcval($attachmentid, 'string'),
            'result'        => new xmlrpcval(true, 'boolean'),
            'result_text'   => new xmlrpcval('', 'base64')
        ), 'struct')
    );
}

?>