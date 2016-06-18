<?php
// ++=========================================================================++
// || CinVin vB Forum Feed Listing v2.3.0 - 1
// || © 2007 CinVin - All Rights Reserved
// || This file may not be redistributed in whole or significant part.
// || http://www.CinVin.com
// || Downloaded 17:06, Wed Nov 7th 2007
// || 
// ++ ========================================================================++

// ####################### SET PHP ENVIRONMENT ########################### 
error_reporting(E_ALL & ~E_NOTICE); 

// #################### DEFINE IMPORTANT CONSTANTS ####################### 
define('NO_REGISTER_GLOBALS', 1); 
define('THIS_SCRIPT', 'cv_rss_feeds');
define('VBSEO_USE_HOSTNAME_IN_URL', 1);

// ################### PRE-CACHE TEMPLATES AND DATA ###################### 
// get special phrase groups 
$phrasegroups = array(
     'forum'
);

// get special data templates from the datastore 
$specialtemplates = array(     
); 

// pre-cache templates used by all actions 
$globaltemplates = array( 
     'cv_ffl_debug_msg',
     'cv_ffl_forumlist',
     'cv_ffl_forum_bit_forum_name',
     'cv_ffl_forum_bit_noposts',
     'cv_ffl_forum_bit_posts',
     'cv_ffl_forum_category_seperator',
     'cv_ffl_forum_feed_link',
     'cv_ffl_forum_link',
     'cv_ffl_forum_sub_forum_indicator',
     'cv_ffl_rss_button_in_footer'
); 

// pre-cache templates used by specific actions 
$actiontemplates = array( 
); 

// ######################### REQUIRE BACK-END ############################
// vBulletin
require_once('./global.php');

// vBadvanced CMPS
if ($vbulletin->options['cv_ffl_cmps']) {
     $vba_cmps_required_file = './includes/vba_cmps_include_template.php';
     if (file_exists($vba_cmps_required_file)) {
          require_once($vba_cmps_required_file);
     }
}

// ############# FUNCTION TO CONSTRUCT THE LIST OF FORUMS ################
function cv_construct_forum_list($parentid = -1, &$ret, $depth = 0) {
     global $vbulletin, $stylevar, $vbphrase, $defaultselected, $forum_url, $debug_mode, $use_vbseo_links, $excluded_forums;

     $parent_count = 0;

     if (!($vbulletin->userinfo['permissions']['forumpermissions'] & $vbulletin->bf_ugp_forumpermissions['canview'])) {
          return $ret;
     }

     if (empty($vbulletin->iforumcache)) {
          require_once('./includes/functions_forumlist.php');
          cache_ordered_forums(1, 1);
     }

     if (empty($vbulletin->iforumcache["$parentid"]) OR !is_array($vbulletin->iforumcache["$parentid"])) {
          return $ret;
     }

     foreach($vbulletin->iforumcache["$parentid"] AS $forumid) {

          $forum = $vbulletin->forumcache["$forumid"];
          $forum_title = $forum[title_clean];
          $forum_desc = strip_tags($forum['description']);

          if ($vbulletin->options['cv_ffl_permissions_usergroupid']) {
               $forumperms = $forum['permissions'][$vbulletin->options['cv_ffl_permissions_usergroupid']];
          } else {
               $forumperms = $vbulletin->userinfo['forumpermissions']["$forumid"];
          }

          if ( (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canview']) AND !$vbulletin->options['showprivateforums']) OR (!$forum['displayorder']) OR !($forum['options'] & $vbulletin->bf_misc_forumoptions['active']) OR ($forum['link'] AND !$vbulletin->options['cv_ffl_show_linked'] AND !$forum['nametitle']) OR ($excluded_forums AND (in_array($forum['forumid'],$excluded_forums))) ) {
               if ($debug_mode) {
                    $debug_msg = 'SKIPPING FORUM ' . $forum[forumid] . ' - ' . $forum_title . ' ... ';
                    eval('$ret .= "' . fetch_template('cv_ffl_debug_msg') . '";');

                    if (!($forumperms & $vbulletin->bf_ugp_forumpermissions['canview']) AND !$vbulletin->options['showprivateforums']) {
                         $debug_msg = 'User does not have permissions to view the forum';
                         eval('$ret .= "' . fetch_template('cv_ffl_debug_msg') . '";');
                    }

                    if (!$forum['displayorder']) {
                         $debug_msg = 'Forum does not have a display order assigned to it';
                         eval('$ret .= "' . fetch_template('cv_ffl_debug_msg') . '";');
                    }

                    if (!($forum['options'] & $vbulletin->bf_misc_forumoptions['active'])) { 
                         $debug_msg = 'Forum is not active';
                         eval('$ret .= "' . fetch_template('cv_ffl_debug_msg') . '";');
                    }

                    if ($forum['link'] AND !$vbulletin->options['cv_ffl_show_linked'] AND !$forum['nametitle']) {
                         $debug_msg = 'Forum is a link, not a real forum';
                         eval('$ret .= "' . fetch_template('cv_ffl_debug_msg') . '";');
                    }

                    if ($excluded_forums AND (in_array($forum['forumid'],$excluded_forums))) {
                         $debug_msg = 'Forum has been specified in the ACP to be excluded.';
                         eval('$ret .= "' . fetch_template('cv_ffl_debug_msg') . '";');
                    }
               }
               continue;

          } else {
               if ($depth == 1) {
                    eval('$ret .= "' . fetch_template('cv_ffl_forum_category_seperator') . '";');
               }

               if ($depth >= 2) {
                    for($i = 0; $i < ($depth-2); $i++) { 
                         eval('$title_prefix = "' . fetch_template('cv_ffl_forum_sub_forum_indicator') . '";');
                    } 
               }

               if ($use_vbseo_links) {
                    $vbseo_forum_link = vbseo_forum_url($forum['forumid'], 1);
               }

               eval('$forum_link = "' . fetch_template('cv_ffl_forum_link') . '";');
               eval('$forum_feed_link = "' . fetch_template('cv_ffl_forum_feed_link') . '";');

               if ($forum['options'] & $vbulletin->bf_misc_forumoptions['cancontainthreads'] AND !$forum['link']) {
                    eval('$ret .= "' . fetch_template('cv_ffl_forum_bit_posts') . '";');
               } else {
                    eval('$ret .= "' . fetch_template('cv_ffl_forum_bit_noposts') . '";');
               }

               // If the current forum has any sub-forums, then create that list
               $depth++;
               cv_construct_forum_list($forumid, $ret, $depth);
               $depth--;

          } // if can view

     } // end

     return $ret;

} // End of function cv_construct_forum_list


// ####################################################################### 
// ######################## START MAIN SCRIPT ############################ 
// ####################################################################### 

// ########################## DEBUG MODE? ################################
if ($vbulletin->options['cv_ffl_debug'] == 1) {
     $debug_mode = 1;
     $debug_msg = 'DEBUG MODE IS TURNED ON';
     eval('$adv_portal_forumlist .= "' . fetch_template('cv_ffl_debug_msg') . '";');
} else {
     $debug_mode = 0;
}

// ################### INITIALIZE MISC. VARIABLES ########################
$excluded_forums = array($vbulletin->options['cv_ffl_exclude_forums']);
$forum_url = $vbulletin->options['bburl'];                     // The URL to your vBulletin forums
$use_vbseo_links = $vbulletin->options['cv_ffl_vbseo_mode'];   // Use vBSeo links if vBSEO is installed?

// Hook spot?  If needed in the future, this would be a good spot for a hook.

if (!$vbulletin->options['externalrss']) {
     $debug_msg = 'You do NOT have RSS feeds enabled in your ACP (ACP => vBulletin Options => External Data Provider => Enable RSS Syndication).';
     eval('$adv_portal_forumlist .= "' . fetch_template('cv_ffl_debug_msg') . '";');
}

// VBSEO Functions
if ($use_vbseo_links) {
     if(defined('VBSEO_ENABLED') && VBSEO_ENABLED) {
          if ($debug_mode) {
               $debug_msg = 'vBSEO mode is turned ON';
               eval('$adv_portal_forumlist .= "' . fetch_template('cv_ffl_debug_msg') . '";');
          }
          include_once './includes/functions_vbseo.php';
          vbseo_get_options();
          vbseo_prepare_seo_replace();
          get_forum_info();
     } else {
          if ($debug_mode) {
               $debug_msg = 'vBSEO mode is turned ON in the module properties but vBSEO does not appear to be installed and/or enabled.';
               eval('$adv_portal_forumlist .= "' . fetch_template('cv_ffl_debug_msg') . '";');
          }
     }  
} else {
     if ($debug_mode) {
          $debug_msg = 'vBSEO mode is turned OFF';
          eval('$adv_portal_forumlist .= "' . fetch_template('cv_ffl_debug_msg') . '";');
     }
}

// Create the 'master' RSS feed link (all forums)
$forum_desc = $vbulletin->options['description'];
$forumid = '';
eval('$forum_title = "' . fetch_template('cv_ffl_forum_bit_forum_name') . '";');
eval('$forum_link = "' . fetch_template('cv_ffl_forum_link') . '";');
eval('$forum_feed_link = "' . fetch_template('cv_ffl_forum_feed_link') . '";');
eval('$adv_portal_forumlist .= "' . fetch_template('cv_ffl_forum_bit_posts') . '";');

// Build the forum list
$adv_portal_forumlist = cv_construct_forum_list(-1, $adv_portal_forumlist, 1);

// Build the navbar navbits
$navbits = array(); 
$navbits[$parent] = $vbphrase['cv_ffl_navbar_desc']; 
$navbits = construct_navbits($navbits); 

// Build the navbar
eval('$navbar = "' . fetch_template('navbar') . '";'); 

// Build and display the output page
eval('print_output("' . fetch_template('cv_ffl_forumlist') . '");');

?>
