<?php

error_reporting(E_ALL & ~E_NOTICE);
@set_time_limit(0);

// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array();
$specialtemplates = array('products');

// ########################## REQUIRE BACK-END ############################
require_once('./global.php');

if (empty($_REQUEST['do']) AND !empty($_GET['oauth_token']))
{
	$_REQUEST['do'] = 'authorized';
}

// #############################################################################
// ########################### START MAIN SCRIPT ###############################
// #############################################################################

if ($_REQUEST['do'] == 'doauthorize')
{
	$consumer_key = $vbulletin->input->clean_gpc('p', 'twitter_consumer_key', TYPE_STR);
	$consumer_secret = $vbulletin->input->clean_gpc('p', 'twitter_consumer_secret', TYPE_STR);
	
	require_once(DIR . '/includes/twitterposter/twitteroauth/OAuth.php');
	require_once(DIR . '/includes/twitterposter/twitteroauth/twitterOAuth.php');
	
	$to = new TwitterOAuth($consumer_key, $consumer_secret);
	
	$RequestToken = $to->getRequestToken();
	
	if (!isset($RequestToken['oauth_token']))
	{
		print_stop_message('cant_reach_twitter');
	}
	
	$request_link = $to->getAuthorizeURL($RequestToken['oauth_token']);
	
	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value = '" . $vbulletin->db->escape_string($consumer_key) . "' WHERE varname = 'twitter_consumer_key'");
	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value = '" . $vbulletin->db->escape_string($consumer_secret) . "' WHERE varname = 'twitter_consumer_secret'");
	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value = '" . $vbulletin->db->escape_string($RequestToken['oauth_token']) . "' WHERE varname = 'twitter_oauth_token'");
	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value = '" . $vbulletin->db->escape_string($RequestToken['oauth_token_secret']) . "' WHERE varname = 'twitter_oauth_token_secret'");
	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value = '0' WHERE varname = 'twitter_authorized'");
	
	require_once(DIR . '/includes/adminfunctions.php');
	build_options();
	
	print_stop_message('authorizeme', $request_link);
}

print_cp_header();

// ################ Copyrights ###########
print_table_start();
print_table_header('Milad\'s vBulletin Services');
print_label_row('<a href="http://services.milado.net/">Milad\'s vBulletin Services</a><dfn>The proficiency & reliability you want! </dfn>', 'Many administrators have their dreams come true with me.<br /><a href="http://services.milado.net/contact_me/" target="_blank">Don\'t hesitate</a>.');
print_table_footer();
// #######################################

if ($_REQUEST['do'] == 'authorize')
{
	if (!$_REQUEST['forceauthorize'])
	{
		if ($vbulletin->options['twitter_authorized'])
		{
			print_stop_message('twitter_already_authorized');
		}
	}
	
	print_form_header('twitterposter', 'doauthorize');
	print_table_header($vbphrase['authorize']);
	print_input_row($vbphrase['consumer_key'], 'twitter_consumer_key', $vbulletin->options['twitter_consumer_key']);
	print_input_row($vbphrase['consumer_secret'], 'twitter_consumer_secret', $vbulletin->options['twitter_consumer_secret']);
	print_submit_row();
}

if ($_REQUEST['do'] == 'authorized')
{
	require_once(DIR . '/includes/twitterposter/twitteroauth/OAuth.php');
	require_once(DIR . '/includes/twitterposter/twitteroauth/twitterOAuth.php');
	
	$to = new TwitterOAuth($vbulletin->options['twitter_consumer_key'], $vbulletin->options['twitter_consumer_secret'], $vbulletin->options['twitter_oauth_token'], $vbulletin->options['twitter_oauth_token_secret']);
	$AccessToken = $to->getAccessToken();
	
	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value = '1' WHERE varname = 'twitter_authorized'");
	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value = '" . $vbulletin->db->escape_string($AccessToken['oauth_token']) . "' WHERE varname = 'twitter_oauth_access_token'");
	$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value = '" . $vbulletin->db->escape_string($AccessToken['oauth_token_secret']) . "' WHERE varname = 'twitter_oauth_access_token_secret'");
	
	require_once(DIR . '/includes/adminfunctions.php');
	build_options();
	
	$vbulletin->options['twitter_oauth_access_token'] = $AccessToken['oauth_token'];
	$vbulletin->options['twitter_oauth_access_token_secret'] = $AccessToken['oauth_token_secret'];
	
	require_once(DIR . '/includes/twitterposter/class_twitterposter.php');
	
	define('TWITTERPOSTER_SILENT', true);
	
	$twitter = new vB_TweetPoster($vbulletin);
	$twitter->SetStatus('#vBulletin Tweet Poster by http://services.milado.net/ Hello World! #testoauth');
	
	require_once(DIR . '/includes/class_bitfield_builder.php');
	vB_Bitfield_Builder::save($db);
	build_forum_permissions();
	
	if ($twitter->tweet_status_message())
	{
		print_stop_message('authorized_successfully');
	}
	else
	{
		print_stop_message('authorization_failed');
	}
}

print_cp_footer();
?> 