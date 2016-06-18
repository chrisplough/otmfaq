<?php
/**
 * 
 * 
 * 
 *  
 * 
 * 
 * @author James MacDiarmid
 * @copyright 2010
 */
 
// ######################## SET PHP ENVIRONMENT ###########################
error_reporting( E_ALL & ~ E_NOTICE );

// ##################### DEFINE IMPORTANT CONSTANTS #######################


// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array('logging');
$specialtemplates = array();

// ########################## REQUIRE BACK-END ############################
require_once('./global.php');

// ######################## CHECK ADMIN PERMISSIONS #######################
if (!can_administer('canadmincron'))
{
	print_cp_no_permission();
}

// ############################# LOG ACTION ###############################
log_admin_action();

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################

print_cp_header("Inactivity Log");

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'choose';
}

// ###################### Start view #######################
if ($_REQUEST['do'] == 'view')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'perpage' => TYPE_INT,
		'orderby' => TYPE_STR,
		'page'    => TYPE_INT
	));

	if (empty($vbulletin->GPC['perpage']))
	{
		$vbulletin->GPC['perpage'] = 15;
	}

    //count the number of rows 
    $counter = $db->query_first("SELECT COUNT(*) AS total FROM " . TABLE_PREFIX . "inactiveuserlog"); 

	$totalpages = ceil($counter['total'] / $vbulletin->GPC['perpage']);

	if (empty($vbulletin->GPC['page']))
	{
		$vbulletin->GPC['page'] = 1;
	}
    
	$startat = ($vbulletin->GPC['page'] - 1) * $vbulletin->GPC['perpage'];    

	switch ($vbulletin->GPC['orderby'])
	{
		//case 'action':
			//$order = 'cronlog.varname ASC, cronlog.dateline DESC';
		//	break;

		case 'date':
		default:
			$order = 'inactiveuserlog.dateline DESC';
	}
    

    $logs = $vbulletin->db->query_read("SELECT id, username, userid, dateline, email, validemail 
            FROM " . TABLE_PREFIX . "inactiveuserlog 
            ORDER BY dateline DESC
            LIMIT $startat, " . $vbulletin->GPC['perpage']);

	if ($db->num_rows($logs))
	{

		if ($vbulletin->GPC['page'] != 1)
		{
			$prv = $vbulletin->GPC['page'] - 1;
			$firstpage = "<input type=\"button\" class=\"button\" tabindex=\"1\" value=\"&laquo; " . $vbphrase['first_page'] . "\" onclick=\"window.location='inactivitylog.php?" . $vbulletin->session->vars['sessionurl'] . "do=view" .
				"&pp=" . $vbulletin->GPC['perpage'] .
				"&orderby=" . urlencode($vbulletin->GPC['orderby']) . "&page=1'\">";
			$prevpage = "<input type=\"button\" class=\"button\" tabindex=\"1\" value=\"&lt; " . $vbphrase['prev_page'] . "\" onclick=\"window.location='inactivitylog.php?" . $vbulletin->session->vars['sessionurl'] . "do=view" .
				"&pp=" . $vbulletin->GPC['perpage'] .
				"&orderby=" . urlencode($vbulletin->GPC['orderby']) . "&page=$prv'\">";
		}
    
		if ($vbulletin->GPC['page'] != $totalpages)
		{
			$nxt = $vbulletin->GPC['page'] + 1;
			$page_button = "inactivitylog.php?" . $vbulletin->session->vars['sessionurl'] . "do=view&pp=" . $vbulletin->GPC['perpage'] . "&orderby=" . urlencode($vbulletin->GPC['orderby']);
			$nextpage = "<input type=\"button\" class=\"button\" tabindex=\"1\" value=\"" . $vbphrase['next_page'] . " &gt;\" onclick=\"window.location='$page_button&page=$nxt'\">";
			$lastpage = "<input type=\"button\" class=\"button\" tabindex=\"1\" value=\"" . $vbphrase['last_page'] . " &raquo;\" onclick=\"window.location='$page_button&page=$totalpages'\">";
		}

		print_form_header('inactivitylog', 'remove');
		print_description_row(construct_link_code($vbphrase['restart'], "inactivitylog.php?" . $vbulletin->session->vars['sessionurl'] . ""), 0, 6, 'thead', vB_Template_Runtime::fetchStyleVar('right'));
		print_table_header(construct_phrase($vbphrase['inactivity_log_viewer_page_x_y_there_are_z_total_log_entries'], vb_number_format($vbulletin->GPC['page']), vb_number_format($totalpages), vb_number_format($counter['total'])), 6);
    
		$headings = array();
		$headings[] = $vbphrase['id'];
        $headings[] = "Username";
		$headings[] = "<a href=\"inactivitylog.php?" . $vbulletin->session->vars['sessionurl'] . "do=view" .
			"&pp=" . $vbulletin->GPC['perpage'] .
			"&orderby=date" .
			"&page=" . $vbulletin->GPC['page'] . "\" title=\"" . $vbphrase['order_by_date'] . "\">" . $vbphrase['date'] . "</a>";
		$headings[] = 'Email';
		$headings[] = 'Valid Email';
		$headings[] = 'Bounced';
		print_cells_row($headings, 1, 1, 1);

        while ($log = $vbulletin->db->fetch_array($logs))
        {

            $cell = array();
            $userid = $log['userid'];
            $cell[] = $userid;
        	$username = $log['username'];
        	$cell[] = "<a href=\"user.php?do=edit&u=$userid\">$username</a>";
            
			$cell[] = '<span class="smallfont">' . vbdate($vbulletin->options['logdateformat'], $log['dateline']) . '</span>';
        	$cell[] = $log['email'];
        
        	// Returns a <div> container with either Yes or No depending if we have a valid email address.
        	$cell[] = ( $log['validemail'] == 1 ? "<div style=\"text-align:center;color: #72ff00;\">Yes</div>" :
        		"<div style=\"text-align:center;color: #ff0000; font-weight: bold;\">No</div>" );
        
        	// Returns a <div> container with either Yes or No depending if the sent email has bounced.
        	$cell[] = ( $log['bounced'] == 1 ? "<div style=\"text-align:center;color: #ff0000;\">Yes</div>" :
        		"<div style=\"text-align:center;color: #72ff00; font-weight: bold;\">No</div>" );
        
            print_cells_row($cell, 0, 0, -4);
        }
                
		print_table_footer(6, "$firstpage $prevpage &nbsp; $nextpage $lastpage");
    
    }
	else
	{
		print_stop_message('no_matches_found');
	}
}

// ###################### Start prune log #######################
if ($_POST['do'] == 'prunelog')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'daysprune' => TYPE_INT
	));

	$datecut = TIMENOW - (86400 * $vbulletin->GPC['daysprune']);


    //count the number of rows 
    $logs = $db->query_first("SELECT COUNT(*) AS total FROM " . TABLE_PREFIX . "inactiveuserlog WHERE dateline < $datecut");

	if ($logs['total'])
	{
		print_form_header('inactivitylog', 'doprunelog');
		construct_hidden_code('datecut', $datecut);
		print_table_header('Prune Inactivity Log');
		print_description_row(construct_phrase($vbphrase['are_you_sure_you_want_to_prune_x_log_entries_from_inactivity_log'], vb_number_format($logs['total'])));
		print_submit_row($vbphrase['yes'], 0, 0, $vbphrase['no']);
	}
	else
	{
		print_stop_message('no_matches_found');
	}
}

// ###################### Start do prune log #######################
if ($_POST['do'] == 'doprunelog')
{
	$vbulletin->input->clean_array_gpc('p', array('datecut' => TYPE_INT));

	$db->query_write("DELETE FROM " . TABLE_PREFIX . "inactiveuserlog WHERE dateline < " . $vbulletin->GPC['datecut']);

	define('CP_REDIRECT', 'inactivity.php?do=choose');
	print_stop_message('pruned_inactivitylog_successfully');
}

// ###################### Start modify #######################
if ($_REQUEST['do'] == 'choose')
{

    $perpage = array(5 => 5, 10 => 10, 15 => 15, 20 => 20, 25 => 25, 30 => 30, 40 => 40, 50 => 50, 100 => 100);
	$orderby = array('date' => $vbphrase['date']);
        
	print_form_header('inactivitylog', 'view', 'false', 'true', 'cpform', '50%');
	print_table_header('Inactivity Log Viewer');
	print_select_row($vbphrase['log_entries_to_show_per_page'], 'perpage', $perpage, 15);
	print_select_row($vbphrase['order_by'], 'orderby', $orderby);

	print_submit_row($vbphrase['view'], 0);

	print_form_header('inactivitylog', 'prunelog', 'false', 'true', 'cpform', '50%');
	print_table_header('Prune Inactivity Log');
	print_input_row($vbphrase['remove_entries_older_than_days'], 'daysprune', 30);
	print_submit_row($vbphrase['prune'], 0);

}

print_cp_footer();


?>