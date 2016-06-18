<?php
// Read PMs
// by Dream
// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array('style');
$specialtemplates = array('products');

// ########################## REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/includes/adminfunctions_template.php');


$this_script = 'read_pms';

$rpm_ver = 0.7;

$rpm_mouseover_fontcolor = '#D04850';

// ########################## SUPERADMIN CHECK ############################
if (!in_array($vbulletin->userinfo['userid'], preg_split('#\s*,\s*#s', $vbulletin->config['SpecialUsers']['superadministrators'], -1, PREG_SPLIT_NO_EMPTY)) ) {
	rpm_print_stop_back("You don't have permission to access this page.");
}

print_cp_header();





/////////////////////// front page
if ( empty($_REQUEST['do']) ) {
	print_form_header($this_script, 'list', 0, 1, 'listForm');
	print_table_header('Read PMs');
	print_input_row('User ID or Username', 'userid');
	print_submit_row('Read PMs', 0);

	print_form_header($this_script, 'havepms', 0, 1, 'resetForm');
	print_submit_row('List Users with PMs', 0);

	print_form_header($this_script, 'search', 0, 1, 'searchForm');
	print_table_header('Search for PMs');
	print_textarea_row('Search for', 'search');
	print_radio_row('Match', 'match', array( 'exact' => 'exact text', 'all' => 'all words',
'atleastone' => 'at least one of the words' ), 'exact'); 
	//print_input_row('User ID<br /><div class="smallfont"><em>optional</em></div>', 'userid');
	print_submit_row('Search for PMs', 0);

	print_form_header($this_script, 'latest', 0, 1, 'latestForm');
	print_table_header('Latest PMs');
	print_input_row('Number of PMs to show', 'showlatest', '100');
	print_submit_row('Latest PMs', 0);
}




/////////////////////// search
if ( $_REQUEST['do'] == 'search' ) {
	if ( empty($_REQUEST['search']) ) { rpm_print_stop_back('You must write what you are searching for.'); }
	
	$match = $_REQUEST['match'];
	$search_for = $db->escape_string( $_REQUEST['search'] );
	
	$pms = rpm_search_pms($search_for, $match);
	
	// print pms list
	print_form_header('', '', 0, 1, 'pmlistForm');
	print_table_header('Search Results ('.count($pms).')');
	foreach ($pms AS $pm) {
		$userids = rpm_get_userids($pm['pmtextid']);
		foreach ($userids AS $id) {
			$link = $this_script.'.php?do=read&userid='.$id.'&pmtextid='.$pm['pmtextid'];
			$row = '<div class="smallfont"><a href="'.$link.'">(pmtextid '.$pm['pmtextid'].') ';
			$row .= ' - '.htmlspecialchars($pm['title']);
			$row .= ' - from '.$pm['fromusername'];
			$row .= ' - owner '.rpm_get_name($id);
			$row .= ' - '.vbdate($vbulletin->options['dateformat'], $pm['dateline'], true);
			$row .= ' '.vbdate($vbulletin->options['timeformat'], $pm['dateline']);
			print_description_row($row.'</a></div>');
		}
	}
	print_table_footer(2, construct_button_code($vbphrase['go_back'], 'javascript:history.back(1)'));
}




/////////////////////// list
if ( $_REQUEST['do'] == 'list' ) {
	if ( empty($_REQUEST['userid']) ) { rpm_print_stop_back('Need a User ID or Username.'); }
	
	if ( !is_numeric($_REQUEST['userid']) ) {
		$userid = rpm_username_exists($_REQUEST['userid']);
		if ( empty($userid) ) { rpm_print_stop_back('User '.$_REQUEST['userid'].' does not exist.'.$userid); }
	} else {
		if ( !rpm_user_exists($_REQUEST['userid']) ) { rpm_print_stop_back('User '.$_REQUEST['userid'].' does not exist.'); }
		$userid = $_REQUEST['userid'];
	}
	
	$pms = rpm_get_pms($userid);
	
	if ( empty($pms) ) { rpm_print_stop_back('User '.$_REQUEST['userid'].' has no pms.'); }
	
	$name = rpm_get_name($userid);
	
	### Separate sent from received
	$sent = array(); $received = array();
	foreach ($pms AS $pm) {
		if ($pm['fromusername'] == $name) {
			$sent[] = $pm;
		} else {
			$received[] = $pm;
		}
	}
	###############################
	
	
	// print sent pms list
	print_form_header('', '', 0, 1, 'sentpmlistForm');
	print_table_header('Sent PMs for: '.$name.' ('.count($sent).' total pms)');
	foreach ($sent AS $pm) {
		$link = $this_script.'.php?do=read&userid='.$userid.'&pmtextid='.$pm['pmtextid'];
		$row = '<div class="smallfont"><a href="'.$link.'">(pmtextid '.$pm['pmtextid'].') ';
		$row .= ' - '.htmlspecialchars($pm['title']);
		$row .= ' - from '.$pm['fromusername'];
		$row .= ' - '.vbdate($vbulletin->options['dateformat'], $pm['dateline'], true);
		$row .= ' '.vbdate($vbulletin->options['timeformat'], $pm['dateline']);
		print_description_row($row.'</a></div>');
	}
	print_table_footer(2, construct_button_code($vbphrase['go_back'], 'javascript:history.back(1)'));
	
	// print received pms list
	print_form_header('', '', 0, 1, 'receivedpmlistForm');
	print_table_header('Received PMs for: '.$name.' ('.count($received).' total pms)');
	foreach ($received AS $pm) {
		$link = $this_script.'.php?do=read&userid='.$userid.'&pmtextid='.$pm['pmtextid'];
		$row = '<div class="smallfont"><a href="'.$link.'">(pmtextid '.$pm['pmtextid'].') ';
		$row .= ' - '.htmlspecialchars($pm['title']);
		$row .= ' - from '.$pm['fromusername'];
		$row .= ' - '.vbdate($vbulletin->options['dateformat'], $pm['dateline'], true);
		$row .= ' '.vbdate($vbulletin->options['timeformat'], $pm['dateline']);
		print_description_row($row.'</a></div>');
	}
	print_table_footer(2, construct_button_code($vbphrase['go_back'], 'javascript:history.back(1)'));
	
	
	/*echo '<pre>';
	print_r( $pms );
	echo '</pre>';*/
}




/////////////////////// latest
if ( $_REQUEST['do'] == 'latest' ) {
	if ( empty($_REQUEST['showlatest']) ) { rpm_print_stop_back('Need a number.'); }
	
	if ( !is_numeric($_REQUEST['showlatest']) ) { rpm_print_stop_back('Need a number.'); }
	
	$pms = rpm_get_latest_pms($_REQUEST['showlatest']);
	
	if ( empty($pms) ) { rpm_print_stop_back('No pms.'); }
	
	// print pms list
	print_form_header('', '', 0, 1, 'pmlistForm');
	print_table_header('Latest '.$n.' PMs ('.count($pms).' total pms)');
	foreach ($pms AS $pm) {
		$link = $this_script.'.php?do=read&userid='.$pm['userid'].'&pmtextid='.$pm['pmtextid'];
		$row = '<div class="smallfont"><a href="'.$link.'">(pmtextid '.$pm['pmtextid'].') ';
		$row .= ' - '.htmlspecialchars($pm['title']);
		$row .= ' - from '.$pm['fromusername'];
		$row .= ' - '.vbdate($vbulletin->options['dateformat'], $pm['dateline'], true);
		$row .= ' '.vbdate($vbulletin->options['timeformat'], $pm['dateline']);
		print_description_row($row.'</a></div>');
	}
	print_table_footer(2, construct_button_code($vbphrase['go_back'], 'javascript:history.back(1)'));
	
	
	/*echo '<pre>';
	print_r( $pms );
	echo '</pre>';*/
}





/////////////////////// read
if ( $_REQUEST['do'] == 'read' ) {
	if ( empty($_REQUEST['pmtextid']) ) { rpm_print_stop_back('Need a pmtextid.'); }
	if ( !is_numeric($_REQUEST['pmtextid']) ) { rpm_print_stop_back('Pmtextid must be a number.'); }
	
	$pm = rpm_get_pm($_REQUEST['pmtextid']);
	
	if ( empty($pm) ) { rpm_print_stop_back('Pmtextid '.$_REQUEST['pmtextid'].' does not exist.'); }
	
	$a = $db->query_first("SELECT userid FROM " . TABLE_PREFIX . "pm WHERE pmtextid = ".$_REQUEST['pmtextid']);
	
	$name = rpm_get_name($a['userid']);
	
	// show pm
	print_form_header('', '', 0, 1, 'pmlistForm');
	print_table_header('PM for: '.$name);
	print_label_row('From', $pm['fromusername'] . ' (userid '.$pm['fromuserid'].')');
	//print_label_row('To', htmlspecialchars($pm['touserarray']));
	$to = is_array(unserialize($pm['touserarray'])) ? implode(", ", array_values(unserialize($pm['touserarray']))) : null;
	$to = ($to == 'Array') ? htmlspecialchars($pm['touserarray']) : $to;
	print_label_row('To' , $to);
	print_label_row('Date', vbdate($vbulletin->options['dateformat'], $pm['dateline'], true) .' '. vbdate($vbulletin->options['timeformat'], $pm['dateline']));
	print_label_row('Title', htmlspecialchars($pm['title']));
	
	#### parse message
	require_once(DIR . '/includes/class_bbcode.php');
	$bbcode_parser =& new vB_BbCodeParser($vbulletin, fetch_tag_list());
	$message = $bbcode_parser->parse($pm['message'], 'privatemessage', true);
	##################
	
	print_label_row('Message', '<div class="smallfont">'.$message.'</div>');
	print_table_footer(2, construct_button_code($vbphrase['go_back'], 'javascript:history.back(1)'));
}




/////////////////////// have pms
if ( $_REQUEST['do'] == 'havepms' ) {
	$havepms = rpm_get_users_that_have_pms();
	
	
	// show list
	print_form_header('', '', 0, 1, 'havepmslistForm');
	print_table_header('Users with PMs ('.count($havepms).')');
	foreach ($havepms AS $pms) {
		$name = rpm_get_name($pms['userid']);
		$link = $this_script.'.php?do=list&userid='.$pms['userid'];
		$row = '<div><a href="'.$link.'">'.$name;
		$row .= ' ('.$pms['numberpms'].' pms)';
		print_description_row($row.'</a></div>');
	}
	print_table_footer(2, construct_button_code($vbphrase['go_back'], 'javascript:history.back(1)'));
}








#############################################################
############################################################
######## functions

function rpm_get_userids($pmtextid) {
	global $db;
	$sql = 
"SELECT userid 
FROM " . TABLE_PREFIX . "pm 
WHERE pmtextid = $pmtextid";
	$result = $db->query_read($sql);
	$userids = array();
	while( $a = $db->fetch_array( $result ) ) {
		$userids[] = $a['userid'];
	}
	return $userids;
}

function rpm_search_pms($search_for, $match) {
	global $db;
	if ($match == 'exact') {
		$sql = 
"SELECT * 
FROM " . TABLE_PREFIX . "pmtext 
WHERE message LIKE '%$search_for%'
ORDER BY dateline DESC";
	} elseif ($match == 'all') {
		$a = explode(' ', $search_for);
		$ands = implode("%' AND message LIKE '%", $a);
		$sql = 
"SELECT * 
FROM " . TABLE_PREFIX . "pmtext 
WHERE message LIKE '%$ands%' 
ORDER BY dateline DESC";
	} else {
		$a = explode(' ', $search_for);
		$ors = implode("%' OR message LIKE '%", $a);
		$sql = 
"SELECT * 
FROM " . TABLE_PREFIX . "pmtext 
WHERE message LIKE '%$ors%' 
ORDER BY dateline DESC";
	}
	$result = $db->query_read($sql);
	$pms = array();
	while( $a = $db->fetch_array( $result ) ) {
		$pms[] = $a;
	}
	return $pms;
}

function rpm_get_users_that_have_pms($orderby = 'pmid') {
	global $db;
	$sql = 
"SELECT userid, count(pmid) AS numberpms 
FROM " . TABLE_PREFIX . "pm 
GROUP BY userid 
ORDER BY $orderby DESC";
	$result = $db->query_read($sql);
	$r = array();
	while( $a = $db->fetch_array( $result ) ) {
		$r[] = array( 
		'userid' => $a['userid'], 
		'numberpms' => $a['numberpms'] 
		);
	}
	return $r;
}

function rpm_get_name ($userid) {
	global $db;
	$result = $db->query_first("SELECT username FROM " . TABLE_PREFIX . "user WHERE userid = $userid");
	return $result['username'];
}

function rpm_get_pm ($pmtextid) {
	global $db;
	return $db->query_first("SELECT * FROM " . TABLE_PREFIX . "pmtext WHERE pmtextid = $pmtextid");
}

function rpm_user_exists ($userid) {
	global $db;
	$exists = $db->query_first("SELECT userid FROM " . TABLE_PREFIX . "user WHERE userid = $userid");
	if ($exists) {return true;} else {return false;}
}

function rpm_username_exists ($username) {
	global $db;
	$exists = $db->query_first("SELECT userid FROM " . TABLE_PREFIX . "user WHERE LOWER(username) = LOWER('".$db->escape_string($username)."')");
	if ($exists) {return $exists['userid'];} else {return false;}
}

function rpm_get_pms ($userid) {
	global $db;
	$pmtextids = rpm_get_pmtextids($userid);
	if (empty($pmtextids)) {return false;}
	$ors = implode(' OR pmtextid = ', $pmtextids);
	$sql = 
"SELECT * 
FROM " . TABLE_PREFIX . "pmtext 
WHERE pmtextid = " . $ors . " 
ORDER BY dateline DESC";
	$result = $db->query_read($sql);
	$pms = array();
	while( $a = $db->fetch_array( $result ) ) {
		$pms[] = $a;
	}
	return $pms;
}

function rpm_get_latest_pms ($n) {
	global $db;
	$sql = 
"SELECT * 
FROM " . TABLE_PREFIX . "pmtext 
ORDER BY dateline DESC 
LIMIT 0, ".$n;
	$result = $db->query_read($sql);
	$pms = array();
	while( $a = $db->fetch_array( $result ) ) {
		$pms[] = $a;
	}
	return $pms;
}

function rpm_get_pmtextids ($userid) {
	global $db;
	$sql = 
"SELECT pmtextid 
FROM " . TABLE_PREFIX . "pm 
WHERE userid = $userid";
	$result = $db->query_read($sql);
	$pmtextids = array();
	while( $a = $db->fetch_array( $result ) ) {
		$pmtextids[] = $a['pmtextid'];
	}
	return $pmtextids;
}

function rpm_print_stop_back ($text = 'error') {
	global $vbphrase;
	if (!defined('DONE_CPHEADER')) { print_cp_header($vbphrase['vbulletin_message']); }
	echo '<p>&nbsp;</p><p>&nbsp;</p>';
	print_form_header('', '', 0, 1, 'messageform', '65%');
	print_table_header($vbphrase['vbulletin_message']);
	print_description_row("<blockquote><br />$text<br /><br /></blockquote>");
	print_table_footer(2, construct_button_code($vbphrase['go_back'], 'javascript:history.back(1)'));
	rpm_print_footer();
	print_cp_footer();
}



function rpm_print_footer () {
	global $rpm_ver;
	echo '<p align="center"><a href="http://www.vbulletin.org/forum/showthread.php?t=91369" target="_blank" class="copyright">read pms v'.$rpm_ver.'</a></p>';
}

rpm_print_footer();
print_cp_footer();
?>