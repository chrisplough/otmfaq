<?php

define('IN_MOBIQUO', true);

require('include/common.php');
define('SCRIPT_ROOT', get_root_dir());

if (isset($_GET['user_id']))
{
    $_GET['u'] = $_GET['user_id'];
    chdir(SCRIPT_ROOT);
    include('image.php');
}
elseif (isset($_GET['username']))
{
    define('IN_MOBIQUO', true);
    define('THIS_SCRIPT', 'image');
    define('VB_AREA', 'Forum');
    
    chdir(SCRIPT_ROOT);
    require('includes/init.php');
    $uid = get_userid_by_name(base64_decode($_GET['username']));
    header('Location: '.$vbulletin->options['bburl']."/image.php?u=$uid");
}