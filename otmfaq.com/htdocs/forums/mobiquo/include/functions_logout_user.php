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

function mobiquo_verify_strike_status($username = '', $supress_error = false)
{
    global $vbulletin;

    $vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "strikes WHERE striketime < " . (TIMENOW - 3600));

    if (!$vbulletin->options['usestrikesystem'])
    {
        return true;
    }

    $strikes = $vbulletin->db->query_first("
        SELECT COUNT(*) AS strikes, MAX(striketime) AS lasttime
        FROM " . TABLE_PREFIX . "strikes
        WHERE strikeip = '" . $vbulletin->db->escape_string(IPADDRESS) . "'
    ");

    if ($strikes['strikes'] >= 5 AND $strikes['lasttime'] > TIMENOW - 900)
    { //they've got it wrong 5 times or greater for any username at the moment

        // the user is still not giving up so lets keep increasing this marker
        exec_strike_user($username);
        return false;
    }
    else if ($strikes['strikes'] > 5)
    { // a bit sneaky but at least it makes the error message look right
        $strikes['strikes'] = 5;
    }

    return $strikes;
}

function mobiquo_process_logout()
{
    global $vbulletin;

    // clear all cookies beginning with COOKIE_PREFIX
    $prefix_length = strlen(COOKIE_PREFIX);
    foreach ($_COOKIE AS $key => $val)
    {
        $index = strpos($key, COOKIE_PREFIX);
        if ($index == 0 AND $index !== false)
        {
            $key = substr($key, $prefix_length);
            if (trim($key) == '')
            {
                continue;
            }
            // vbsetcookie will add the cookie prefix
            vbsetcookie($key, '', 1);
        }
    }

    if ($vbulletin->userinfo['userid'] AND $vbulletin->userinfo['userid'] != -1)
    {
        // init user data manager
        $userdata =& datamanager_init('User', $vbulletin, ERRTYPE_SILENT);
        $userdata->set_existing($vbulletin->userinfo);
        $userdata->set('lastactivity', TIMENOW - $vbulletin->options['cookietimeout']);
        $userdata->set('lastvisit', TIMENOW);
        $userdata->save();

        // make sure any other of this user's sessions are deleted (in case they ended up with more than one)
        $vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "session WHERE userid = " . $vbulletin->userinfo['userid']);
    }

    $vbulletin->db->query_write("DELETE FROM " . TABLE_PREFIX . "session WHERE sessionhash = '" . $vbulletin->db->escape_string($vbulletin->session->vars['dbsessionhash']) . "'");

    if ($vbulletin->session->created == true)
    {
        // if we just created a session on this page, there's no reason not to use it
        $newsession =& $vbulletin->session;
    }
    else
    {
        $newsession = new vB_Session($vbulletin, '', 0, '', $vbulletin->session->vars['styleid']);
    }
    $newsession->set('userid', 0);
    $newsession->set('loggedin', 0);
    $newsession->set_session_visibility(($vbulletin->superglobal_size['_COOKIE'] > 0));
    $vbulletin->session =& $newsession;
}
