<?php

/**
 * @author James MacDiarmid
 * @copyright 2010
 */
 
// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// ##################### DEFINE IMPORTANT CONSTANTS #######################


// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array();
$specialtemplates = array( 'products' );


// ######################### REQUIRE BACK-END ############################
require_once('./global.php');



// ######################## CHECK ADMIN PERMISSIONS #######################
if (!can_administer('canadmincron'))
{
	print_cp_no_permission();
}


// Insert an entry into the Admin Log if nothing was passed in.
if ( empty( $_REQUEST['do'] ) )
{
	log_admin_action();
}


print_cp_header( 'Inactivity Reminder Email Reset' );

// Clean any input that was submitted
$do = $vbulletin->input->clean_gpc( 'g', 'do', TYPE_STR);

// Was a test email submitted?
if ( $do == 'reset' )
{

	$userlist = $vbulletin->input->clean_gpc( 'p', 'userlist', TYPE_STR );

	if ( is_null( $userlist ) || $userlist == '' )
	{
		print_cp_message( 'A list of users is required.', 'resetreminders.php', 5 );
	}

    $sql = "UPDATE " . TABLE_PREFIX . "user 
            SET rmEmailDate = '0', 
        	rmEmailCount = '0'";   

    if ($userlist != 0)
    {
        $sqlcond = str_replace(",", "','", $userlist);
        $sql .= "WHERE userid IN ('" . $sqlcond . "')";        
    }  
            
    //print($sql);             
    
    $result = $vbulletin->db->query_write($sql);

	if ( $vbulletin->db->affected_rows() != 0 )
	{
        $count = $vbulletin->db->affected_rows();
		print_cp_message( "$count user(s) reset", "resetreminders.php", 5 );
    }
    else
    {
		print_cp_message( "No reset was needed.", "resetreminders.php", 5 );
    }


}
else
{
   
	print_form_header('resetreminders', 'reset', '', 'true', '', '', '', '', 'POST');
	print_table_header('Reset User Reminders');
	print_input_row('Enter IDs for each user you want to reset: (Separated by commas, or 0 for all)', 'userlist');
	print_submit_row( "Submit", 0 );
	print_table_footer();
    
}
print_cp_footer();


?>