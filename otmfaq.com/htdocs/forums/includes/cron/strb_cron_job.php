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
error_reporting(E_ALL & ~E_NOTICE);
if (!is_object($vbulletin->db))
{
	exit;
}

log_cron_action('Stop the Registration Bots Rand Generator [Started]', $nextitem);
require_once(DIR . '/includes/stb_functions.php');
// generate two fields
$seedr = mt_rand(7,12);
$seedu = mt_rand(7,12);
$StopBotReg_rulespage_hash = random_alpha_string($seedr);
$StopBotReg_userpage_hash = random_alpha_string($seedu);//'" . $vbulletin->db->escape_string($StopBotReg_userspage_hash) . "'


$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value = '" . $vbulletin->db->escape_string($StopBotReg_rulespage_hash) . "' WHERE varname='StopBotReg_rulespage_hash'");
$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value = '" . $vbulletin->db->escape_string($StopBotReg_userpage_hash) . "' WHERE varname='StopBotReg_userpage_hash'");

log_cron_action('Stop the Registration Bots Rand Generator [Finished]', $nextitem);
/*
		<phrasetype name="Scheduled Tasks" fieldname="cron">
			<phrase name="task_strb_cron_job_desc" date="1196213460" username="noppid" version="3.7.3PL1" product="stopbotreg"><![CDATA[This Script Executes Updates of the Stop the Registration Bots Random Field Name Values.]]></phrase>
			<phrase name="task_strb_cron_job_log" date="1196213460" username="noppid" version="3.7.3PL1" product="stopbotreg" />
			<phrase name="task_strb_cron_job_title" date="1196213460" username="noppid" version="3.7.3PL1" product="stopbotreg"><![CDATA[Stop the Registration Bots Random Filed Generator]]></phrase>
		</phrasetype>

	<cronentries>
		<cron varname="strb_cron_job" active="0" loglevel="1">
			<filename>./includes/cron/strb_cron_job.php</filename>
			<scheduling weekday="-1" day="-1" hour="0,2,4,6,8,10,12,14,16,18,20,22" minute="1" />
		</cron>
	</cronentries>
*/
?>