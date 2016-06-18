<?php
/*======================================================================*\
|| #################################################################### ||
|| # Automatic Thread Tagger                                          # ||
|| # ---------------------------------------------------------------- # ||
|| # Originally created by MrEyes (1.0 Beta 3)                        # ||
|| # Copyright 2008-2009 Marius Czyz. All Rights Reserved.            # ||
|| #################################################################### ||
\*======================================================================*/ 

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);
if (!is_object($vbulletin->db))
{
    exit;
}

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################

if ($vbulletin->options['autotag_enabled_all'])
{
    require_once(DIR . '/includes/functions_autotagger.php');
    require_once(DIR . '/includes/functions_newpost.php');

    $threads = $vbulletin->db->query_read("
        SELECT taglist, dateline, forumid, postuserid, title, threadid, prefixid
        FROM ".TABLE_PREFIX."thread
        WHERE (
            taglist IS NULL OR taglist = ''
            
        ) AND autoskip = 0
        ORDER BY threadid DESC
    ");

    $processed = 0;
    $ending = $vbulletin->options['autotag_cron_count'];
    while ($thread = $vbulletin->db->fetch_array($threads) AND $processed < $ending)
    {
        ProcessThread($thread);
        $processed++;
    }
    log_cron_action('Auto Thread Tagger processed '.$processed.' threads.', $nextitem);
}

?>