<?php //$Id: setlastmsg.php,v 1.1.2.2 2005/07/21 03:40:13 ns Exp $

/********************************************
   NNTP (Usenet/Newsgroup) Gateway 1.0
   By Kevin Gilbertson <mail@gilby.com>
   Released: Feb. 20, 2002

   For support, please visit the thread
   at vbulletin.org
********************************************/
/*Changed by Nikol S <ns@eyo.com.au> */

if (!headers_sent())
{
	header("Content-Type: text/plain");
}

require_once("./global.php");

// Load NNTP classes
require_once("./includes/nntp.php");

// Load settings
$get_settings=$db->query("
	SELECT varname, value FROM " . TABLE_PREFIX . "nntp_settings
");

while ($setting=$db->fetch_array($get_settings)){
	$settings[$setting[varname]] = $setting[value];
}

$get_groups=$db->query("
	SELECT * FROM " . TABLE_PREFIX . "nntp_groups WHERE enabled=1
");

echo $db->num_rows($get_groups) . " group(s) gatewayed.\r\n";

while ($group = $db->fetch_array($get_groups))
{

	// connect to newsgroup server
	$news = new Net_Nntp();
	if ($group[server]){
		$server = $group[server];
		$uname = $group[username];
		$pass = $group[password];
	} else {
		$server = $settings[server];
		$uname = $settings[username];
		$pass = $settings[password];
	}
	echo $news->prepare_connection($server,119, $group[newsgroup], $uname, $pass);

	echo "Logging in to ". ($server) .", group $group[newsgroup]: ". $news->response."\r\n";
	echo "Max: " . $news->max() . " Min: ". $news->min() . "\r\n";

	if ($news->max())
	{
		$db->query("UPDATE " . TABLE_PREFIX . "nntp_groups SET lastmsg = " . ($news->max() - 20) . " WHERE newsgroup='".addslashes($group[newsgroup])."'");
		echo "Set last message id for '$group[newsgroup]' to ";
		echo $news->max() - 20 . "\r\n";
	}
}

function logging($message)
{

        if (!headers_sent())
	{
                header("Content-Type: text/plain");
        }

	echo $message;
	flush;
}


?>