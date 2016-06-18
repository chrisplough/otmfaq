<?php

// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('NO_REGISTER_GLOBALS', 1);
define('THIS_SCRIPT', 'test'); // change this depending on your filename

// ######################### REQUIRE BACK-END ############################
chdir(dirname(__FILE__));
require_once('./global.php');

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

$fd = fopen("php://stdin", "r");
if($fd){
$email = "";
while (!feof($fd)) {
$email .= fread($fd, 1024);
}
fclose($fd);

preg_match_all("/X-Remindermail-BounceId: (.*)/", $email, $matches);
$userid = $matches[1][0];
preg_match_all("/X-Remindermail-BounceSalt: (.*)/", $email, $matches);
$salt = addslashes($matches[1][0]);
$email = addslashes($email);
$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET bounced = '1' WHERE userid = '$userid' AND salt = '$salt'");
$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "remindermail_log SET bounced = '1', message = '$email' WHERE userid = '$userid' AND message=''");
$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "remindermail_stats SET value = value + 1 WHERE title = 'bounced'");
if($vbulletin->options['reminder_bouncegrounpenabled'] == 1){
  $vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "user SET usergroupid = '" . $vbulletin->options['reminder_moveusergroup'] . "' WHERE userid = '$userid'");
}
return NULL;
} else {
print("You should not be viewing this page under ANY circumstance. Every piece of information about you has been logged! (including your inside leg measurement!)");
}
?>