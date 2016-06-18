<?php
#################################################################### |;
# vBulletin  - Licence Number VBF98A5CB5
# ---------------------------------------------------------------- # |;
# Copyright ©2000–2006 Jelsoft Enterprises Ltd. All Rights Reserved. |;
# This file may not be redistributed in whole or significant part. # |;
# ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # |;
# http://www.vbulletin.com | http://www.vbulletin.com/license.html # |;
#################################################################### |;

# The following settings allow ImpEx to connect to the vBulletin 3
# database into which you will be importing data.

# If impex is installed in vBulletin you can ignore the target details
# as includes/config.php

if (!defined('IDIR')) { die; }

# For mysqli enter mysql
$impexconfig['target']['databasetype']	= 'mysql';
$impexconfig['target']['server']		= 'localhost';
$impexconfig['target']['user']			= 'otmfaqc_vbdba';
$impexconfig['target']['password']		= 'Pochado';
$impexconfig['target']['database']		= 'otmfaqc_vb';
$impexconfig['target']['tableprefix']	= 'vb_';


# If the system that is being imported from uses a database,
# enter the details for it here and set 'sourceexists' to true.
# If the source data is NOT stored in a database, set 'sourceexists' to false

$impexconfig['sourceexists']			= true;

# mysql / mssql
$impexconfig['source']['databasetype']	= 'mysql';
$impexconfig['source']['server']		= 'localhost';
$impexconfig['source']['user']			= 'cpmavenw_forumsa';
$impexconfig['source']['password']		= 'Pochado';
$impexconfig['source']['database']		= 'cpmavenw_forums';
$impexconfig['source']['tableprefix']   = 'smf_';


# Error logging will log import errors to a database table impexerror
# for use with support. 
# Language file is the file of phrases to be used, default is english.
# pagespeed is the second(s) wait before the page refreshes.

$impexconfig['system']['errorlogging']	= true;
$impexconfig['system']['language']		= '/impex_language.php';
$impexconfig['system']['pagespeed']		= 1;

define('impexdebug', false);
define('emailcasesensitive', false);
define('forcesqlmode', false);
define('skipparentids', false);
define('shortoutput', false);
define('do_mysql_fetch_assoc', false);
define('step_through', false);
?>