<?php

/************************************************************************************
* vBSEO 3.6.0 for vBulletin v3.x & v4.x by Crawlability, Inc.                       *
*                                                                                   *
* Copyright © 2011, Crawlability, Inc. All rights reserved.                         *
* You may not redistribute this file or its derivatives without written permission. *
*                                                                                   *
* Sales Email: sales@crawlability.com                                               *
*                                                                                   *
*----------------------------vBSEO IS NOT FREE SOFTWARE-----------------------------*
* http://www.crawlability.com/vbseo/license/                                        *
************************************************************************************/

function vbseo_tbl_prefix($tbl, $prefixonly = false)
{
return 
(isset($GLOBALS['vbseo_table_prefix'][$tbl]) ?
$GLOBALS['vbseo_table_prefix'][$tbl] : TABLE_PREFIX) . ($prefixonly ? '' : $tbl);
}
function vbseo_close_db()
{
global $vbulletin;
if (isset($vbulletin) && is_object($vbulletin))
$vbulletin->options['disableerroremail'] = true;
$vbseo_DB_site = &$GLOBALS['vbseo_crawlDB'];
if (isset($vbseo_DB_site))
$vbseo_DB_site->vbseodb_close();
}
function vbseo_db_escape($str)
{
if($gdb = $GLOBALS['vbseo_crawlDB'])
{
if($gdb->mysqli)
return mysqli_real_escape_string($gdb->link_id, $str);
}
return ($gdb->link_id && function_exists('mysql_real_escape_string')) ? 
mysql_real_escape_string($str) : 
(function_exists('mysql_escape_string') ? mysql_escape_string($str) : addslashes($str));
}
function vbseo_get_db()
{
if(is_array($GLOBALS['config']) || !isset($GLOBALS['config']))
{
global $config;
}
$vbseo_DB_site = &$GLOBALS['vbseo_crawlDB'];
if (isset($vbseo_DB_site))
return $vbseo_DB_site;
$vbseo_DB_site = new vbseoDB;
@include(vBSEO_Storage::path('vbinc') . '/' . VBSEO_VB_CONFIG);
$conf = isset($config) ? $config : eval('return $config;');
if (isset($conf['Database']))
{
$dbname = $conf['Database']['dbname'];
$tableprefix = $conf['Database']['tableprefix'];
$servername = $conf['MasterServer']['servername'];
$port = $conf['MasterServer']['port'];
$dbusername = $conf['MasterServer']['username'];
$dbpassword = $conf['MasterServer']['password'];
$usepconnect = $conf['MasterServer']['usepconnect'];
}
$vbseo_DB_site->vbseodb_connect($servername, $dbusername, $dbpassword, $usepconnect, $tableprefix, $dbname, $port);
return $vbseo_DB_site;
}
class vbseoDB
{
var $database = "";
var $link_id = 0;
var $query_id = 0;
var $own_link = 0;
var $record = array();
function vbseoDB()
{
}
function vbseodb_connect($server, $user, $password, $usepconnect, $tableprefix = '', $database, $port)
{
global $vbulletin, $DB_site, $config;
if (!defined('TABLE_PREFIX'))
define('TABLE_PREFIX', $tableprefix);
if (0 == $this->link_id)
{
$this->mysqli = (is_array($config) && $config['Database']['dbtype'] == 'mysqli');
if (is_object($vbulletin) && isset($vbulletin->db) && 
((isset($vbulletin->db->connection_master) && ($dlnk = $vbulletin->db->connection_master)) ||
(isset($vbulletin->db->connection_slave) && ($dlnk = $vbulletin->db->connection_slave))
) && (@is_object($dlnk) ? @mysqli_get_server_info($dlnk) : @mysql_get_server_info($dlnk) )
)
$this->link_id = $dlnk;
else
if (isset($DB_site) && $DB_site->link_id)
$this->link_id = $DB_site->link_id;
else
{
$this->own_link = 1;
if ($this->mysqli)
{
$this->link_id = mysqli_init();
@mysqli_real_connect($this->link_id, $server, $user, $password, $database, $port);
}
else
{
if ($usepconnect == 1)
$this->link_id = @mysql_pconnect($server . (($port && ($port != 3306) && !strstr($server,':'))?':' . $port:''), $user, $password);
else
$this->link_id = @mysql_connect($server . (($port && ($port != 3306) && !strstr($server,':'))?':' . $port:''), $user, $password);
}
}
if (!$this->link_id)
{
return false;
}
$this->mysqli = @is_object($this->link_id);
if ($this->mysqli)
{
$this->funcs = array('get_server_info' => 'mysqli_get_server_info',
'select_db' => 'mysqli_select_db',
'query' => 'mysqli_query',
'affected_rows' => 'mysqli_affected_rows',
'num_rows' => 'mysqli_num_rows',
'free_result' => 'mysqli_free_result',
'fetch_array' => 'mysqli_fetch_array',
'fetch_assoc' => 'mysqli_fetch_assoc',
'close' => 'mysqli_close',
);
}
else
{
$this->funcs = array('get_server_info' => 'mysql_get_server_info',
'select_db' => 'mysql_select_db',
'query' => 'mysql_query',
'affected_rows' => 'mysql_affected_rows',
'num_rows' => 'mysql_num_rows',
'free_result' => 'mysql_free_result',
'fetch_array' => 'mysql_fetch_array',
'fetch_assoc' => 'mysql_fetch_assoc',
'close' => 'mysql_close',
);
$this->vbseodb_select_db($database);
}
if (is_array($config) && isset($config['Mysqli']['charset']) && ($charset = $config['Mysqli']['charset']))
{
if ($this->mysqli && function_exists('mysqli_set_charset'))
mysqli_set_charset($this->link_id, $charset);
else
$this->vbseodb_query("SET NAMES $charset");
}
$this->mysql_version = function_exists($this->funcs['get_server_info'])?$this->funcs['get_server_info']($this->link_id):'';
return true;
}
}
function vbseodb_select_db($database = "")
{
if ($database != "")
{
$this->database = $database;
}
@$this->funcs['select_db']($this->database, $this->link_id);
}
function vbseodb_query($query_string, $buffered = true)
{
$this->query_id = $this->mysqli ?
@mysqli_query($this->link_id, $query_string, ($buffered ? MYSQLI_STORE_RESULT : MYSQLI_USE_RESULT))
:
@mysql_query($query_string, $this->link_id);
return $this->query_id;
}
function vbseo_affected_rows()
{
return @$this->funcs['affected_rows'] ($this->link_id);
}
function vbseo_get_found()
{
$mget = $this->vbseodb_query_first("SELECT FOUND_ROWS()");
return $mget[0];
}
function vbseodb_query_first($query_string)
{
$query_id = $this->vbseodb_query($query_string);
$returnarray = $this->vbseodb_fetch_array($query_id, $query_string);
$this->vbseodb_free_result($query_id);
return $returnarray;
}
function vbseodb_escape_string($string)
{
if ($this->mysqli)
return mysqli_escape_string($this->link_id, $string);
else
return mysql_escape_string($string);
}
function vbseodb_escape_like($string)
{
return str_replace('_', '\\_', str_replace('%', '\\%', $this->vbseodb_escape_string($string)));
}
function vbseodb_free_result($query_id)
{
if($query_id)
@$this->funcs['free_result']($query_id);
}
function vbseodb_fetch_array($query_id = -1, $query_string = "")
{
if ($query_id != -1)
{
$this->query_id = $query_id;
}
if (isset($this->query_id) && $this->query_id)
{
return $this->record = @$this->funcs['fetch_array']($this->query_id);
}
else
return false;
}
function vbseodb_fetch_assoc($query_id = -1, $query_string = "")
{
if ($query_id != -1)
{
$this->query_id = $query_id;
}
if (isset($this->query_id) && $this->query_id)
{
return $this->record = @$this->funcs['fetch_assoc']($this->query_id);
}
else
return false;
}
function vbseodb_close()
{
if ($this->own_link && VBSEO_VB35X)
@$this->funcs['close']($this->link_id);
}
}
?>