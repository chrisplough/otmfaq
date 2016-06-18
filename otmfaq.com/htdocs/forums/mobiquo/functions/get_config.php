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

$phrasegroups = array();
$specialtemplates = array();
$globaltemplates = array();
$actiontemplates = array();

require_once('./global.php');

function get_config_func()
{
    global $vbulletin, $config, $permissions;
    
    $return_config = array(
        'sys_version'   => new xmlrpcval(@$vbulletin->versionnumber, 'string'),
        'is_open'       => new xmlrpcval($config['is_open'], 'boolean'),
        'guest_okay'    => new xmlrpcval($config['guest_okay'], 'boolean'),
    );
    
    foreach($config as $key => $value){
        if(!$return_config[$key] && !is_array($value)){
            $return_config[$key] = new xmlrpcval(mobiquo_encode($value), 'string');
        }
    }
    
    if (isset($vbulletin->products['post_thanks']) && $vbulletin->products['post_thanks']) {
        $return_config['post_thanks'] = new xmlrpcval('1', 'string');
    }
    
    if (!$vbulletin->userinfo['userid'])
    {
        require_once(DIR . "/vb/legacy/currentuser.php");
        $current_user = new vB_Legacy_CurrentUser();
        
        if ($current_user->hasPermission('forumpermissions', 'cansearch') && $vbulletin->options['enablesearches']) {
            $return_config['guest_search'] = new xmlrpcval('1', 'string');
        }
        
        if ($vbulletin->options['WOLenable'] && $permissions['wolpermissions'] & $vbulletin->bf_ugp_wolpermissions['canwhosonline']) {
            $return_config['guest_whosonline'] = new xmlrpcval('1', 'string');
        }
    }
    
    return new xmlrpcresp(new xmlrpcval($return_config, 'struct'));
}
