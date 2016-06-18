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

error_reporting(E_ALL & ~E_NOTICE);
$phrasegroups = array('threadmanage', 'thread');
$specialtemplates = array();
include_once dirname(__FILE__).'/../vbseo/includes/functions_vbseo.php';
vbseo_extra_inc('linkback');
require_once('./global.'.VBSEO_VB_EXT);
require_once(DIR . '/includes/functions_databuild.'.VBSEO_VB_EXT);
$user_is_mod = ($vbulletin->userinfo['permissions']['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['ismoderator']);
$vbulletin->input->clean_array_gpc('r', array(
'startpage' => TYPE_INT,
'perpage'   => TYPE_INT
));
function vbseo_found_rows()
{
global $db;
if(method_exists($db,'found_rows'))
{
return $db->found_rows();
}else
{
$db->sql = "SELECT FOUND_ROWS()";
$queryresult = $db->execute_query(true, $db->connection_recent);
$returnarray = $db->fetch_array($queryresult, DBARRAY_NUM);
$db->free_result($queryresult);
return intval($returnarray[0]);
}
}
function vbseo_dleft($pcont, $small = 0, $align = 'left')
{
return '<div '.($small?'class="smallfont" ':'').'align="'.$align.'">'.$pcont.'</div>';
}
function vbseo_can_moderation_linkback($pingid, $forumid = 0)
{
global $db, $vbulletin, $vbseo_pback_in_use;
if(!$forumid)
{
$vbseo_pback_in_use = $pback = $db->query_first(
"SELECT tb.*,t.forumid FROM " . vbseo_tbl_prefix('vbseo_linkback'). " tb
LEFT JOIN ". vbseo_tbl_prefix('thread'). " t on tb.t_threadid=t.threadid
WHERE t_id='$pingid'");
$forumid = $pback['forumid'];
}
return ($vbulletin->userinfo['permissions']['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['ismoderator'])
|| can_moderate($forumid, 'vbseo_linkbacks');
}
function vbseo_pager($total, $what)
{
global $vbulletin;
$pp = 25;
$sp = max(1,intval($vbulletin->GPC['startpage']));
$from = ($sp - 1) * $pp ;
if($what == 'limit')return ' limit '.$from.','.$pp;
$numpages = ceil($total / $pp);
$pagelinks = '';
for ($x = 1; $x <= $numpages; $x++)
{
if ($x == $sp)
$pagelinks .= " [<b>$x</b>] ";
else
$pagelinks .= " <a href=\"vbseo_moderate.php?startpage=$x&do=".$_REQUEST['do']. "\">$x</a> ";
}
return $pagelinks;
}
if ( $_REQUEST['do'] == 'linkbacksout')
{
print_cp_header($vbphrase['vbseo_moderate_linkbacksout']);
print_table_start();
print_table_header(($vbphrase['vbseo_outgoing_linkbacks_manager']));
print_cells_row(array($vbphrase['vbseo_outgoing_linkbacks_note']));
print_table_footer();
print_form_header('vbseo_moderate', 'doout', 0, 1, 'linkbacks');
print_table_header($vbphrase['vbseo_moderate_linkbacksout'],7);
$linkbacks_no = 0;
$linkbacks = $db->query_read(
"SELECT SQL_CALC_FOUND_ROWS tb.*,t.* FROM " . vbseo_tbl_prefix('vbseo_linkback'). " tb
LEFT JOIN ". vbseo_tbl_prefix('thread'). " t on tb.t_threadid=t.threadid
WHERE t_incoming=0 and t_deleted=0
ORDER by t_time DESC".vbseo_pager(1, 'limit'));
$total = vbseo_found_rows();
while ($pback = $db->fetch_array($linkbacks))
{
if (!vbseo_can_moderation_linkback(0, $pback['forumid']))
continue;
if(!$linkbacks_no)
print_cells_row(array($vbphrase['vbseo_for'], vbseo_dleft($vbphrase['posted_by']),
$vbphrase['vbseo_type'], $vbphrase['date'], $vbphrase['vbseo_is_successful'],
vbseo_dleft(
'<label for="pingactionall">'.$vbphrase['select'].
"</label><input type=\"checkbox\" name=\"pingactionall\" value=\"1\" id=\"pingactionall\" onclick=\"
for(var i=0;i<chboxesno;i++)
this.form.elements['pingaction['+chboxes[i]+']'].checked=this.checked
\" />
<script language=\"Javascript\">chboxes = new Array();chboxesno=0;</script>
",'','center')),1);
$linkbacks_no++;
if(!$pback['title'])$pback['title']=$vbphrase['vbseo_thread_not_found'];
$pback['t_title'] = htmlspecialchars($pback['t_title'], ENT_QUOTES);
print_cells_row(array(
vbseo_dleft('<b><a href="'.htmlentities($pback['t_dest_url']).'" title="'.$pback['t_title'].'" target="_blank">'.htmlentities(strlen($pback['t_dest_url'])>50?substr($pback['t_dest_url'],0,50).'...':$pback['t_dest_url']).'</a></b>'),
vbseo_dleft('<a href="'.htmlentities($pback['t_src_url']).'" target="_blank">'.$pback['title'].'</a>'),
vbseo_dleft($vbphrase['vbseo_'.(($pback['t_type']==2)?'refback':($pback['t_type']?'trackback':'pingback'))],1),
vbseo_dleft(date('m-d-Y',$pback['t_time']),1),
$vbphrase[$pback['t_approve']?'yes':'no'],
"<input type=\"checkbox\" name=\"pingaction[$pback[t_id]]\" value=\"1\" id=\"del_$pback[t_id]\" tabindex=\"1\" />
<script language=\"Javascript\">chboxes[chboxesno++]=$pback[t_id];</script>
"
), false, false, 1
);
}
if ($linkbacks_no)
{
print_description_row(vbseo_pager($total, 'pager'), false, 9, '', 'center');
print_submit_row($vbphrase['vbseo_remove_successful_linkbacks'], '', 6, '',
'<input type="submit" class="submit" name="removesel" tabindex="1" value="'.$vbphrase['vbseo_remove_selected_linkbacks'].'" accesskey="s" />
<input type="submit" class="submit" name="resendsel" tabindex="1" value="'.$vbphrase['vbseo_resend_linkbacks'].'" accesskey="s" />
');
}else
{
print_cells_row(array($vbphrase['vbseo_no_linkbacks_yet']));
print_table_footer();
}
}
if ($_REQUEST['do'] == 'linkbackslist' )
{
print_cp_header($vbphrase['vbseo_moderate_linkbackslist']);
print_table_start();
print_table_header(($vbphrase['vbseo_incoming_linkbacks_manager']));
print_cells_row(array($vbphrase['vbseo_incoming_linkbacks_note']));
print_table_footer();
print_form_header('vbseo_moderate', 'dolinkbacks', 0, 1, 'linkbacks');
print_table_header($vbphrase['vbseo_moderate_linkbackslist'],7);
construct_hidden_code('islist',1);
?>
<script type="text/javascript">
function js_linkback_jump(pingid)
{
task = eval("document.linkbacks.p" + pingid + ".options[document.linkbacks.p" + pingid + ".selectedIndex].value");
switch (task)
{
case 'edit': window.location = "vbseo_moderate.php?<?php echo $vbulletin->session->vars['sessionurl_js']; ?>do=edit&pingid=" + pingid; break;
case 'delete': window.location = "vbseo_moderate.php?<?php echo $vbulletin->session->vars['sessionurl_js']; ?>do=remove&pingid=" + pingid; break;
default: return false; break;
}
}
</script>
<?php
$linkbacks_no = 0;
$linkbacks = $db->query_read(
"SELECT SQL_CALC_FOUND_ROWS tb.*,t.* FROM " . vbseo_tbl_prefix('vbseo_linkback'). " tb
LEFT JOIN ". vbseo_tbl_prefix('thread'). " t on tb.t_threadid=t.threadid
WHERE t_approve>0 AND t_incoming=1 and t_deleted=0
ORDER by t_time DESC".vbseo_pager(1, 'limit'));
$total = vbseo_found_rows();
while ($pback = $db->fetch_array($linkbacks))
{
if (!vbseo_can_moderation_linkback(0, $pback['forumid']))
continue;
if(!$linkbacks_no)
print_cells_row(array($vbphrase['vbseo_for'], vbseo_dleft($vbphrase['posted_by']),
$vbphrase['date'], $vbphrase['options']),1);
$linkbacks_no++;
if(!$pback['title'])$pback['title']=$vbphrase['vbseo_thread_not_found'];
$pback['t_title'] = htmlspecialchars($pback['t_title'], ENT_QUOTES);
print_cells_row(array(
vbseo_dleft('<b><a href="'.htmlentities($pback['t_dest_url']).'" target="_blank">'.$pback['title'].'</a></b>'),
vbseo_dleft('<a href="'.htmlentities($pback['t_src_url']).'" title="'.$pback['t_title'].'" target="_blank">'.htmlentities(strlen($pback['t_src_url'])>50?substr($pback['t_src_url'],0,50).'...':$pback['t_src_url']).'</a>'),
vbseo_dleft(date('m-d-Y',$pback['t_time']), 1),
"<nobr>
<select name=\"p".$pback['t_id']."\" class=\"bginput\">\n" .
construct_select_options(
array(
'edit' => $vbphrase['edit'],
'delete' => $vbphrase['delete'],
)) . "\t</select>
<input type=\"button\" class=\"button\" value=\"" . $vbphrase['go'] . "\" onclick=\"js_linkback_jump($pback[t_id]);\" />
</nobr>"
)
);
}
if (!$linkbacks_no)
{
print_cells_row(array($vbphrase['vbseo_no_linkbacks_yet']));
}else
print_description_row(vbseo_pager($total, 'pager'), false, 9, '', 'center');
print_table_footer();
}
if ($_REQUEST['do'] == 'linkbacks')
{
print_cp_header($vbphrase['vbseo_moderate_linkbacks']);
print_form_header('vbseo_moderate', 'dolinkbacks', 0, 1, 'linkbacks');
print_table_header($vbphrase['vbseo_moderate_linkbacks']);
$linkbacks_no = 0;
$linkbacks = $db->query_read(
$q="SELECT SQL_CALC_FOUND_ROWS tb.*,t.forumid FROM " . vbseo_tbl_prefix('vbseo_linkback'). " tb
LEFT JOIN ". vbseo_tbl_prefix('thread'). " t on tb.t_threadid=t.threadid
WHERE t_approve=0 AND t_incoming=1 and t_deleted=0
ORDER BY t_time DESC".vbseo_pager(1, 'limit'));
$total = vbseo_found_rows();
while ($pback = $db->fetch_array($linkbacks))
{
if (!vbseo_can_moderation_linkback(0, $pback['forumid']))
continue;
if(!$linkbacks_no)
print_description_row('
<input type="button" value="' . $vbphrase['vbseo_validate'] . '" onclick="js_check_all_option(this.form, 1);" class="button" title="' . $vbphrase['vbseo_validate'] . '" />
' . ((can_moderate('candeleteposts') OR can_moderate('canremoveposts')) ? '&nbsp;
<input type="button" value="' . $vbphrase['delete'] . '" onclick="js_check_all_option(this.form, -1);" class="button" title="' . $vbphrase['delete'] . '" />' : '') . '
&nbsp;
<input type="button" value="' . $vbphrase['ignore'] . '" onclick="js_check_all_option(this.form, 0);" class="button" title="' . $vbphrase['ignore'] . '" />
', 0, 2, 'thead', 'center');
$linkbacks_no++;
$pback['t_title'] = htmlspecialchars($pback['t_title'], ENT_QUOTES);
if($linkbacks_no>1)
print_description_row('<span class="smallfont">&nbsp;</span>', 0, 2, 'thead');
print_label_row('<b>' . $vbphrase['posted_by'] . '</b>', '<a href="'.htmlentities($pback['t_src_url'])."\" target=\"_blank\">$pback[t_src_url]</a>");
print_label_row('<b>' . $vbphrase['vbseo_for'] . '</b>', '<a href="'.htmlentities($pback['t_dest_url'])."\" target=\"_blank\">$pback[t_dest_url]</a>");
print_label_row('<b>' . $vbphrase['vbseo_type'] . '</b> ', $vbphrase['vbseo_'.(($pback['t_type']==2)?'refback':($pback['t_type']?'trackback':'pingback'))]);
print_label_row('<b>' . $vbphrase['date'] . '</b>', date('Y-m-d H:i:s',$pback['t_time']));
print_input_row($vbphrase['title'], "pingtitle[$pback[t_id]]", $pback[t_title], 0, 70);
print_textarea_row($vbphrase['message'], "pingtext[$pback[t_id]]", htmlentities($pback[t_text]), 8, 70);
print_label_row($vbphrase['action'], "
<label for=\"val_$pback[t_id]\"><input type=\"radio\" name=\"pingaction[$pback[t_id]]\" value=\"1\" id=\"val_$pback[t_id]\" tabindex=\"1\" />" . $vbphrase['vbseo_validate'] . "</label>
<label for=\"del_$pback[t_id]\"><input type=\"radio\" name=\"pingaction[$pback[t_id]]\" value=\"-1\" id=\"del_$pback[t_id]\" tabindex=\"1\" />" . $vbphrase['delete'] . "</label>
<label for=\"ign_$pback[t_id]\"><input type=\"radio\" name=\"pingaction[$pback[t_id]]\" value=\"0\" id=\"ign_$pback[t_id]\" tabindex=\"1\"  checked=\"checked\" />" . $vbphrase['ignore'] . "</label>
", '', 'top', 'postaction');
}
if (!$linkbacks_no)
{
print_description_row($vbphrase['vbseo_no_linkbacks_awaiting_moderation']);
print_table_footer();
}
else
{
print_description_row(vbseo_pager($total, 'pager'), false, 9, '', 'center');
print_submit_row();
}
}
if ($_REQUEST['do'] == 'edit')
{
$vbulletin->input->clean_array_gpc('g', array(
'pingid' => TYPE_INT,
));
$pingid = intval($vbulletin->GPC['pingid']);
print_cp_header($vbphrase['vbseo_edit_linkback']);
print_form_header('vbseo_moderate', 'dolinkbacks', 0, 1, 'linkbacks');
print_table_header($vbphrase['vbseo_edit_linkback']);
construct_hidden_code('islist',1);
$linkbacks_no = 0;
$linkbacks = $db->query_read(
"SELECT tb.*,t.forumid FROM " . vbseo_tbl_prefix('vbseo_linkback'). " tb
LEFT JOIN ". vbseo_tbl_prefix('thread'). " t on tb.t_threadid=t.threadid
WHERE t_id='$pingid'");
while ($pback = $db->fetch_array($linkbacks))
{
if (!vbseo_can_moderation_linkback(0, $pback['forumid']))
continue;
$pback['t_title'] = htmlspecialchars($pback['t_title'], ENT_QUOTES);
print_label_row('<b>' . $vbphrase['posted_by'] . '</b>', '<a href="'.htmlentities($pback['t_src_url'])."\" target=\"_blank\">".htmlentities($pback['t_src_url'])."</a>");
print_label_row('<b>' . $vbphrase['vbseo_for'] . '</b>', '<a href="'.htmlentities($pback['t_dest_url'])."\" target=\"_blank\">".htmlentities($pback['t_dest_url'])."</a>");
print_label_row('<b>' . $vbphrase['vbseo_type'] . '</b> ', $vbphrase['vbseo_'.(($pback['t_type']==2)?'refback':($pback['t_type']?'trackback':'pingback'))]);
print_label_row('<b>' . $vbphrase['date'] . '</b>', date('Y-m-d H:i:s',$pback['t_time']));
print_input_row($vbphrase['title'], "pingtitle[$pback[t_id]]", $pback[t_title], 0, 70);
print_textarea_row($vbphrase['message'], "pingtext[$pback[t_id]]", htmlentities($pback['t_text']), 8, 70);
construct_hidden_code("pingaction[$pback[t_id]]", 1);
}
print_submit_row();
}
if ($_POST['do'] == 'dolinkbacks')
{
$vbulletin->input->clean_array_gpc('p', array(
'islist' => TYPE_BOOL,
'pingtext'   => TYPE_ARRAY_STR,
'pingaction'     => TYPE_ARRAY_INT,
'pingtitle'      => TYPE_ARRAY_STR,
));
if (!empty($vbulletin->GPC['pingaction']))
{
$modlog = array();
@define('VBSEO_PREPROCESSED', true);
foreach ($vbulletin->GPC['pingaction'] AS $pingid => $action)
{
$pingid = intval($pingid);
if (!vbseo_can_moderation_linkback($pingid))
continue;
if ($action == 1)
{
if(!$vbulletin->GPC['islist'])
vbseo_send_notification_pingback(
$vbseo_pback_in_use['t_threadid'],
$vbseo_pback_in_use['t_postid'],
$vbseo_pback_in_use['t_src_url'],
$vbulletin->GPC['pingtitle']["$pingid"],
$vbulletin->GPC['pingtext']["$pingid"],
1,
0
);
$db->query_write("
UPDATE " . vbseo_tbl_prefix('vbseo_linkback'). "
SET t_title=\"".$db->escape_string($vbulletin->GPC['pingtitle']["$pingid"])."\",
t_text=\"".$db->escape_string($vbulletin->GPC['pingtext']["$pingid"])."\",
t_approve=\"1\"
WHERE t_id=\"$pingid\"
");
vbseo_linkback_approve($pingid);
}
else if ($action == -1)
{
$db->query_write($q="
UPDATE " . vbseo_tbl_prefix('vbseo_linkback'). "
SET t_deleted=\"1\"
WHERE t_id=\"$pingid\"
");
vbseo_linkback_approve($pingid);
/*
$db->query_write("
DELETE FROM " . vbseo_tbl_prefix('vbseo_linkback') . "
WHERE t_id=\"$pingid\"
");
*/
}
}
}
if($vbulletin->GPC['islist'])
{
define('CP_REDIRECT', 'vbseo_moderate.php?do=linkbackslist');
print_stop_message('vbseo_linkbacks_updated_successfully');
}else
{
define('CP_REDIRECT', 'vbseo_moderate.php?do=linkbacks');
print_stop_message('vbseo_moderated_linkbacks_successfully');
}
}
if ($_REQUEST['do'] == 'remove')
{
$vbulletin->input->clean_array_gpc('g', array(
'pingid' => TYPE_INT,
));
$pingid = intval($vbulletin->GPC['pingid']);
if (vbseo_can_moderation_linkback($pingid))
{
$db->query_write($q="
UPDATE " . vbseo_tbl_prefix('vbseo_linkback') . "
SET t_deleted=\"1\"
WHERE t_id=\"$pingid\"
");
vbseo_linkback_approve($pingid);
/*
$db->query_write("
DELETE FROM " . vbseo_tbl_prefix('vbseo_linkback'). "
WHERE t_id=\"$pingid\"
");
*/
}
define('CP_REDIRECT', 'vbseo_moderate.php?do=linkbackslist');
print_stop_message('vbseo_linkbacks_updated_successfully');
}
if ($_POST['do'] == 'doout')
{
$vbulletin->input->clean_array_gpc('p', array(
'pingaction'     => TYPE_ARRAY_INT,
));
if ($_REQUEST['resendsel'] || $_REQUEST['removesel'])
{
$modlog = array();
if (!empty($vbulletin->GPC['pingaction']))
foreach ($vbulletin->GPC['pingaction'] AS $pingid => $action)
{
$pingid = intval($pingid);
if (!vbseo_can_moderation_linkback($pingid))
continue;
if ($action == 1)
{
$pback = $db->query_first(
"SELECT * FROM ".vbseo_tbl_prefix('vbseo_linkback')."
WHERE t_id=\"$pingid\" "
);
if($_REQUEST['resendsel'])
{
if($pback['t_type']==0)
$confirum = (vbseo_do_pingback($pback['t_src_url'], $pback['t_dest_url'])>0);
else
$confirum = vbseo_do_trackback($pback['t_dest_url'], $pback['t_src_url'],
$pback['t_title'], $vbulletin->options['bbtitle'], $pback['t_text'].'...');
if($confirum)
{
$db->query_write("
UPDATE " . vbseo_tbl_prefix('vbseo_linkback'). "
SET t_approve=\"1\"
WHERE t_id=\"$pingid\"
");
vbseo_linkback_approve($pingid);
}
} else
{
$db->query_write($q="
UPDATE " . vbseo_tbl_prefix('vbseo_linkback'). "
SET t_deleted=\"1\"
WHERE t_id=\"$pingid\"
");
vbseo_linkback_approve($pingid);
}
}
}
}else
{
$linkbacks = $db->query_read(
"SELECT * FROM ".vbseo_tbl_prefix('vbseo_linkback')." tb
LEFT JOIN ". vbseo_tbl_prefix('thread') . " t on tb.t_threadid=t.threadid
WHERE t_incoming=0 AND t_approve=1 AND t_deleted=0"
);
while ($pback = $db->fetch_array($linkbacks))
{
if (!vbseo_can_moderation_linkback($pback['t_id']))
continue;
/*
if($pback['t_type']==0)
$db->query_write("DELETE FROM " . vbseo_tbl_prefix('vbseo_linkback'). " WHERE t_id=\"".$pback['t_id']."\"");
else
*/
$db->query_write("
UPDATE " . vbseo_tbl_prefix('vbseo_linkback'). "
SET t_deleted=\"1\"
WHERE t_id=\"".$pback['t_id']."\"
");
vbseo_linkback_approve($pback['t_id']);
}
}
define('CP_REDIRECT', 'vbseo_moderate.php?do=linkbacksout');
print_stop_message('vbseo_linkbacks_updated_successfully');
}
if($user_is_mod)
{
$vbseo_l_types = array(
0 => 'trackback',
1 => 'pingback',
2 => 'refback'
);
if($_REQUEST['do'] == 'docleanup')
{
$lb = unserialize($_REQUEST['confirmcleanup']);
foreach($lb as $ti=>$tt)
{
$ti .= '';
$ls = $db->query_write(
"DELETE FROM ".vbseo_tbl_prefix('vbseo_linkback')."
WHERE t_incoming='".$ti[1]."' AND t_approve='".$ti[2]."' AND t_type='".$ti[0]."'"
);
}
vbseo_linkback_recalc();
define('CP_REDIRECT', 'vbseo_moderate.php?do=cleanup');
print_stop_message('vbseo_linkback_cleanup_successfully');
}
if($_REQUEST['do'] == 'revalidate')
{
print_cp_header($vbphrase['vbseo_cleanup_linkbacks']);
foreach($_REQUEST['lb'] as $ti=>$tt)
{
$ti .= '';
echo '<br />'.$vbseo_l_types[$ti[0]].'<hr />';
$inco = $ti[1];
$appr = $ti[2];
$ls = $db->query_read( $q=
"SELECT * FROM ".vbseo_tbl_prefix('vbseo_linkback')." tb
WHERE t_incoming=1 AND t_approve=".$appr." AND t_type=".$ti[0]
);
while ($pback = $db->fetch_array($ls))
{
echo $pback['t_src_url'].'... ';
flush();
$pret = vbseo_http_query_full($pback['t_src_url']);
$dest_url = parse_url($pback['t_dest_url']);
$pcont = $pret['content'];
$fail = $pcont && !preg_match('#<a[^>]*?'.preg_quote($dest_url['host'],'#').'.*?>#',$pcont, $lm);
echo '[ '.
($pcont ? ($fail ? '<span style="color:red">FAILED</span>':'OK') : '--').
' ]<br />';
if($fail)
{
if($pback['t_approve'])
$db->query_write(
"UPDATE ".vbseo_tbl_prefix('vbseo_linkback')."
SET t_approve=0
WHERE t_id=".$pback['t_id']
);
else
$db->query_write(
"DELETE FROM ".vbseo_tbl_prefix('vbseo_linkback')."
WHERE t_id=".$pback['t_id']
);
vbseo_linkback_approve($pback['t_id']);
}
flush();
}
}
define('CP_REDIRECT', 'vbseo_moderate.php?do=cleanup');
print_stop_message('linkback_cleanup_successfully');
}
if($_REQUEST['do'] == 'cleanup2')
{
print_cp_header($vbphrase['vbseo_cleanup_linkbacks']);
print_form_header('vbseo_moderate', 'docleanup');
construct_hidden_code('confirmcleanup', serialize($_REQUEST['lb']));
print_table_header($vbphrase['confirm_linkbacks_cleanup']);
print_description_row("
<blockquote><br />
".$vbphrase['are_you_sure_you_want_to_cleanup_linkbacks']."
<br /></blockquote>\n\t");
print_submit_row($vbphrase['yes'], 0, 2, $vbphrase['no']);
print_table_footer();
}
if($_REQUEST['do'] == 'cleanup')
{
function vbseo_cleanup_field($lts, $ti, $incoming, $approved, $full = true)
{
return '<div align="left">'.
(($ti==2 && $incoming ==0)?'-':'<input type="checkbox" name="lb['.$ti.($full?$incoming.$approved:'').']" />'.$lts[$incoming][$approved])
."</div>\n";
}
print_cp_header($vbphrase['vbseo_cleanup_linkbacks']);
print_form_header('vbseo_moderate', 'cleanup2', false, true, 'cpform1');
print_table_header($vbphrase['vbseo_cleanup_linkbacks'], 6);
print_cells_row(array('',
$vbphrase['vbseo_incoming'].' '.$vbphrase['vbseo_approved'],
$vbphrase['vbseo_incoming'].' '.$vbphrase['vbseo_nonapproved'],
$vbphrase['vbseo_outgoing'].' '.$vbphrase['vbseo_approved'],
$vbphrase['vbseo_outgoing'].' '.$vbphrase['vbseo_nonapproved'],
$vbphrase['total']),
1);
$ls_q = $db->query_read(
"SELECT count(*) as cnt, t_incoming, t_approve, t_type FROM ".vbseo_tbl_prefix('vbseo_linkback')."
GROUP BY t_incoming, t_approve, t_type
"
);
$linkback_stats = array();
while ($lsi = $db->fetch_array($ls_q))
{
$linkback_stats [$lsi['t_type']][$lsi['t_incoming']][$lsi['t_approve']] = $lsi['cnt'];
}
$db->free_result($moderated);
foreach($vbseo_l_types as $ti=>$tt)
{
$lts = $linkback_stats[$ti];
$tot = $lts[0][0] + $lts[0][1] + $lts[1][0] + $lts[1][1];
print_cells_row(array($vbphrase['vbseo_'.$tt],
vbseo_cleanup_field($lts,$ti,1,1),
vbseo_cleanup_field($lts,$ti,1,0),
vbseo_cleanup_field($lts,$ti,0,1),
vbseo_cleanup_field($lts,$ti,0,0),
number_format($tot,0)));
}
print_submit_row($vbphrase['vbseo_cleanup_linkbacks'], 0, 6, '',
"\t<input type=\"submit\" name=\"chall\" class=\"button\" tabindex=\"1\" value=\"".$vbphrase['vbseo_select_all']."\" onclick=\"
js_toggle_all(document.forms['cpform1'], 'checkbox', '', '', true);
return false\" />\n"
);
print_table_footer();
print_form_header('vbseo_moderate', 'revalidate', false, true, 'cpform2');
print_table_header($vbphrase['vbseo_revalidate_linkbacks'], 6);
print_description_row($vbphrase['vbseo_revalidate_desc'],'',4);
print_cells_row(array('',
$vbphrase['vbseo_incoming'].' '.$vbphrase['vbseo_approved'],
$vbphrase['vbseo_incoming'].' '.$vbphrase['vbseo_nonapproved'],
$vbphrase['vbseo_incoming'].' '.$vbphrase['total']
),
1);
foreach($vbseo_l_types as $ti=>$tt)
{
$lts = $linkback_stats[$ti];
$tot = $lts[1][0] + $lts[1][1];
print_cells_row(array($vbphrase['vbseo_'.$tt],
vbseo_cleanup_field($lts,$ti,1,1),
vbseo_cleanup_field($lts,$ti,1,0),
number_format($tot,0)));
}
print_submit_row($vbphrase['vbseo_revalidate_linkbacks'], 0, 6, '',
"\t<input type=\"submit\" name=\"chall\" class=\"button\" tabindex=\"1\" value=\"".$vbphrase['vbseo_select_all']."\" onclick=\"
js_toggle_all(document.forms['cpform2'], 'checkbox', '', '', true);
return false\" />\n"
);
print_table_footer();
}
}
print_cp_footer();
?>