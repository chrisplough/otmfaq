<?php

/**
 * @author James MacDiarmid
 * @copyright 2011
 */

// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### DEFINE IMPORTANT CONSTANTS #######################

define('THIS_SCRIPT', 'rmoptout');
define('CSRF_PROTECTION', true);  
// change this depending on your filename

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array();

// get special data templates from the datastore
$specialtemplates = array();

// pre-cache templates used by all actions
$globaltemplates = array('',);

// pre-cache templates used by specific actions
$actiontemplates = array();

// ######################### REQUIRE BACK-END ############################
// if your page is outside of your normal vb forums directory, you should change directories by uncommenting the next line
// chdir ('/path/to/your/forums');
require_once('./global.php');

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

$navbits = construct_navbits(array('' => 'Inactivity OptOut'));
$navbar = render_navbar_template($navbits);

$pagetitle = 'Inactivity Reminder OptOut';

$vbulletin->input->clean_array_gpc('g', array(
    'u' => TYPE_STR,
    'e' => TYPE_STR,
));

$userid=$vbulletin->GPC['u'];
$email=$vbulletin->GPC['e'];

if (empty($userid)) 
{ 
    eval(standard_error(fetch_error('error_unsubscribed_already',$vbulletin->options['contactuslink'])));
}
  
if (empty($email)) 
{ 
    eval(standard_error(fetch_error('error_unsubscribed_already',$vbulletin->options['contactuslink'])));  
} 

$query = "SELECT username, userid, email FROM " . TABLE_PREFIX . "user ".
         "WHERE userid = $userid AND email = '$email' and rmoptout = 0";  
$result = $db->query_first($query);

if ($result == null)
{
    eval(standard_error(fetch_error('error_unsubscribed_already',$vbulletin->options['contactuslink'])));  
} 
else
{
   $db->query_write("UPDATE " . TABLE_PREFIX . "user SET rmoptout = 1 WHERE userid = '$userid' AND email = '$email'");
   eval(standard_error(fetch_error('error_unsubscribe_done')));    
}

?>