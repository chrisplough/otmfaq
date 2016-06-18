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
define('MOBIQUO_DEBUG', 0);
//define('DISABLE_HOOKS', true);

if (function_exists('set_magic_quotes_runtime'))
    @set_magic_quotes_runtime(0);

include("./include/xmlrpc.inc");
include("./include/xmlrpcs.inc");
$_POST['xmlrpc'] = 'true';
define('CWD1', (($getcwd = getcwd()) ? $getcwd : '.'));

error_reporting(MOBIQUO_DEBUG);

$phrasegroups = array();
$specialtemplates = array();
$globaltemplates = array();
$actiontemplates = array();
@ob_start();
require(CWD1."/include/common.php");
require(CWD1."/server_define.php");
require(CWD1.'/env_setting.php');
require(CWD1.'/xmlrpcresp.php');

define('SCRIPT_ROOT', get_root_dir());

chdir(SCRIPT_ROOT);

if(in_array($request_method, array('get_config', 'authorize_user', 'login')))
{
    define('THIS_SCRIPT', 'register');
    define('CSRF_PROTECTION', false);
    define('CSRF_SKIP_LIST', 'login');
}

if ($function_file_name && isset($server_param[$request_method]))
    require(CWD1.'/functions/'.$function_file_name.'.php');
else
    return_fault("Request function $request_method does not exist!");

if (strpos($request_method, 'm_') !== 0 || strpos($request_method, 'm_get') === 0)
{
    header('Mobiquo_is_login:'.(isset($vbulletin) && $vbulletin->userinfo['userid'] != 0 ? 'true' : 'false'));
}


require_once(CWD1.'/config/config.php');

$mobiquo_config = new mobiquo_config();
$config = $mobiquo_config->get_config();

// check if moderation function is allowed
if (strpos($request_method, 'm_') === 0 && !$config['allow_moderate'])
    return_fault('Moderation action is not allowed on this forum!');

if (strpos($request_method, 'm_') === 0 && $vbulletin->userinfo['userid'] == 0)
    return_fault();

if($config['guest_okay'] == 0 && $vbulletin->userinfo['userid'] == 0 && $request_method != 'get_config' && $request_method != 'login')
    return_fault();

if($config['disable_search'] == 1){
    if($request_method == 'search_topic' or $request_method == 'search_post'){
        return_fault();
    }
}

if(!$config['is_open'] && $request_method != 'logout_user' && $request_method != 'get_config')
    return_fault('Server is not available');

define('SHORTENQUOTE', $config['shorten_quote']);

if(!empty($config['hide_forum_id']))
{
    foreach($config['hide_forum_id'] as $h_forumid) {
        $vbulletin->userinfo['forumpermissions'][$h_forumid] = 655374;
    }
}

$rpcServer = new xmlrpc_server($server_param, false);
$rpcServer->compress_response = 'true';
$rpcServer->response_charset_encoding ='UTF-8';
$rpcServer->service();

exit;

?>