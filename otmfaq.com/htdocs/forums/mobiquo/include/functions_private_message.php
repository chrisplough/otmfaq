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
function mobiquo_construct_folder_jump($foldertype = 0, $selectedid = false, $exclusions = false, $sentfolders = '')
{
    global $vbphrase, $folderid, $folderselect, $foldernames, $messagecounters, $subscribecounters, $folder;
    global $vbulletin;
    // 0 indicates PMs
    // 1 indicates subscriptions
    // get all folder names (for dropdown)
    // reference with $foldernames[#] .

    $folderjump = array();
    if (!is_array($foldernames))
    {
        $foldernames = array();
    }


    // get PM folders total
    $pmcounts = $vbulletin->db->query_read_slave("
                SELECT COUNT(*) AS total, folderid
                FROM " . TABLE_PREFIX . "pm AS pm
                WHERE userid = " . $vbulletin->userinfo['userid'] . "
                GROUP BY folderid
            ");
    $messagecounters = array();
    while ($pmcount = $vbulletin->db->fetch_array($pmcounts))
    {
        $messagecounters["$pmcount[folderid]"] = $pmcount['total'];
    }

    $folderfield = 'pmfolders';
    $folders = array('0' => $vbphrase['inbox'], '-1' => $vbphrase['sent_items']);
    if (!empty($vbulletin->userinfo["$folderfield"]))
    {
        $userfolder = unserialize($vbulletin->userinfo["$folderfield"]);
        if (is_array($userfolder))
        {
            $folders = $folders + $userfolder;
        }
    }
    $counters =& $messagecounters;




    if (is_array($folders))
    {
        foreach($folders AS $_folderid => $_foldername)
        {
            if (is_array($exclusions) AND in_array($_folderid, $exclusions))
            {
                continue;
            }
            else
            {
                $foldernames["$_folderid"] = $_foldername;
                $folderjump[$_folderid]['pmcount'] = intval($counters["$_folderid"]);
                $folderjump[$_folderid]['box_name'] = $_foldername;
                if ($_folderid == $selectedid AND $selectedid !== false)
                {
                    $folder = $_foldername;
                }
            }
        }
    }

    return $folderjump;

}
?>