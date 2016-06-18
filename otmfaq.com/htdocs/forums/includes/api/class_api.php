<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin Blog 4.1.1 Patch Level 1 - Licence Number VBS309B87F
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2000-2011 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/
if (!VB_API AND !(defined('UNIT_TESTING') AND UNIT_TESTING === true)) die;

abstract class vBI_APIMethod
{
	abstract public function output();

	public function processed_output()
	{
		$output = $this->output();
		
		if (!($charset = vB_Template_Runtime::fetchStyleVar('charset')))
		{
			global $vbulletin;
			$charset = $vbulletin->userinfo['lang_charset'];
		}

		$lower_charset = strtolower($charset);
		if ($lower_charset != 'utf-8')
		{
			// Browsers tend to interpret character set iso-8859-1 as windows-1252
			if ($lower_charset == 'iso-8859-1')
			{
				$lower_charset = 'windows-1252';
			}
			$this->to_utf8($output, $lower_charset);
		}

		return $output;
	}

	/**
	 * Util method for output error
	 *
	 * @param string $errorid The unique error id for client
	 * @param string $errormessage The human readable error message for client
	 * @return array The errormessage for JSON output
	 */
	protected function error($errorid, $errormessage = '')
	{
		return array('response' => array(
				'errormessage' => array(
					$errorid, $errormessage
				)
			)
		);
	}

	protected function to_utf8(&$value, $charset)
	{
		if (is_array($value))
		{
			foreach ($value AS &$el)
			{
				$this->to_utf8($el, $charset);
			}
		}

		if (is_string($value))
		{
			$value = unhtmlspecialchars(to_utf8($value, $charset, true), true);
		}
	}
}

function cleanAPIName($name)
{
	return preg_replace('/[^a-z0-9_.]/', '', $name);
}

// Reuse the whitelist defined in other method
function loadAPI($scriptname, $do = '', $version = 1, $updatedo = false)
{
	static $internalscriptname;
	global $VB_API_WHITELIST, $VB_API_WHITELIST_COMMON, $VB_API_ROUTE_SEGMENT_WHITELIST;

	$scriptname = cleanAPIName($scriptname);
	$internalscriptname = $scriptname;

	// Setup new API
	$internalscriptname = $scriptname;
	if ($updatedo)
	{
		$_REQUEST['do'] = $_GET['do'] = $_POST['do'] = $do;
	}

	$do = cleanAPIName($do);
	$version = intval($version);
	// Check if the api method has been defined in versions
	do {
		$api_filename = CWD_API . '/' . $version . '/' . $scriptname . (($do AND !VB_API_CMS)?'_' . $do:'') . '.php';
		--$version;
	} while (!file_exists($api_filename) AND $version > 0);

	// Still don't have the api file
	if (!file_exists($api_filename))
	{
		if (!headers_sent())
		{
			header('HTTP/1.1 404 Not Found');
		}
		die();
	}

	require_once $api_filename;

	return $internalscriptname;
}

function loadCommonWhiteList()
{
	global $VB_API_WHITELIST_COMMON;
	require CWD_API . '/commonwhitelist.php';
	return $VB_API_WHITELIST_COMMON;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:08, Tue Jul 12th 2011
|| # CVS: $RCSfile$ - $Revision: 26995 $
|| ####################################################################
\*======================================================================*/