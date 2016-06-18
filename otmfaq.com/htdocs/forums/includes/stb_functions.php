<?php
/*======================================================================*\
|| #################################################################### ||
|| # Stop the Registration Bots Release 1.2.1						  # ||
|| # ---------------------------------------------------------------- # ||
|| # Copyright © 2005-2009 Greg Lynch. All Rights Reserved.           # ||
|| # noppid@lakecs.com http://www.cpurigs.com/						  # ||
|| #																  # ||
|| #################################################################### ||
\*======================================================================*/

/**
* Checks time to about 6 decimal places in string format.
*
* @return	String
*/
function utime_string()
{
	preg_match("/^(.*?) (.*?)$/", microtime(), $match);
    return $match[2] . "." . substr($match[1],2,strlen($match[1]-2));
}

/**
* Fetches a random string of mixed upper and lower case letters.
*
* @param	integer		Length of string to return. Default = 8.
*
* @return	String
*/
function random_alpha_string($length=8)
{
	$ranchars = array();
	for($i = 65; $i <= 90; $i++)
		$ranchars[] = chr($i);
	for($i = 97; $i <= 122; $i++)
		$ranchars[] = chr($i);
	$max = count($ranchars)-1;
	$astr = '';
	for ($i = 1; $i <= $length; $i++)
		$astr .= $ranchars[mt_rand(0,$max)];
	return $astr;
}
?>