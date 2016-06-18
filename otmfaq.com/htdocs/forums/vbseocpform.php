<?php

/************************************************************************************
* vBSEO 3.3.2 for vBulletin v3.x.x by Crawlability, Inc.                            *
*                                                                                   *
* Copyright © 2005-2009, Crawlability, Inc. All rights reserved.                    *
* You may not redistribute this file or its derivatives without written permission. *
*                                                                                   *
* Sales Email: sales@crawlability.com                                               *
*                                                                                   *
*----------------------------vBSEO IS NOT FREE SOFTWARE-----------------------------*
* http://www.crawlability.com/vbseo/license/                                        *
************************************************************************************/

if(!defined('VBSEO_IS_VBSEOCP'))exit;
function vbseo_display_option_formats($desc, $optname, $aformats, $urlformat)
{
global $trclass, $alang;
?>
<tr class=<?php echo $trclass++%2?'altfirst':'altsecond';?>>
<td><?php echo ($_GET['vbseodbg']?'['.$optname.'] ':''). $desc?></td>
<td><p><?php
$fnd=false;
$nm = 0;
foreach($aformats as $v)
{
$k = preg_replace('#[\[\]]#', '%', $v);
$tf = ($urlformat==$k);
if($tf)$fnd = true;
echo '<input type=radio value="'.$v.'" name='.$optname.' id="'.$optname.(++$nm).'" '.($tf?'CHECKED ':'').'><label for="'.$optname.$nm.'">'.$v.'</label><br />';
}
?>
<input type=radio value="custom" name=<?php echo $optname;?> <?php echo $fnd?'':' checked'?> id="<?php echo $optname;?>_id">
<?php echo $alang['custom']?>:<br />
<input name=cust_<?php echo $optname;?> value="<?php echo $fnd?'':uformat_prep($urlformat)?>" size=34 onKeyUp="if(this.value)document.getElementById('<?php echo $optname;?>_id').checked=true">
</p></td>
</tr>
<?php
}
function vbseo_display_option_yesno($desc, $optname, $optvalue)
{
global $trclass, $alang;
?>
<tr class=<?php echo $trclass++%2?'altfirst':'altsecond';?>>
<td><?php echo $desc?></td>
<td>
<input type="radio" <?php echo $optvalue?'checked ':''?> value="1" name="<?php echo $optname;?>"><label for="<?php echo $optname.'_id1';?>"><?php echo $alang['yes']?></label>
<input type="radio" <?php echo $optvalue?'':'checked '?> value="0" name="<?php echo $optname;?>"><label for="<?php echo $optname.'_id2';?>"><?php echo $alang['no']?></label>
</td>
</tr>
<?php
}
function vbseo_display_option_header($desc, $anchor = '')
{
global $alang;
echo '<tr class=subheader>
<td colspan=2>'.($anchor?'<a name="'.$anchor.'" id="'.$anchor.'"></a>':'').$alang[$desc].'</td>
</tr>';
}
?><table class=area cellSpacing=1 cellPadding=5 width="100%" border=0>
<form method=post action="vbseocp.php?go=true" name="settingsform">
<input type="hidden" name="saveoptions" value="1">
<tbody>
<tr bgColor=#ffffff>
<td><table cellSpacing=1 cellPadding=7 width="100%" border=0>
<tbody>
<tr bgColor=#ffffff>
<td><table cellSpacing=0 cellPadding=0 width="100%" border=0>
<tbody>
<tr>
<td><table class=formtbl cellSpacing=1 cellPadding=4
width="100%" border=0>
<tbody>
<tr class=header>
<td colspan=2><a name="general" id="general"></a><?php echo $alang['gen_set']?></td>
</tr>
<tr class=subheader>
<td colspan=2><a name="general_vbseo"></a><?php echo $alang['vbseo_opt']?> </td>
</tr>
<tr class=altsecond>
<td><?php echo $alang['activate_desc']?> </td>
<td><input type=radio <?php echo VBSEO_ENABLED?'CHECKED ':''?> value=1
name=activate>
<?php echo $alang['yes']?>
<input type=radio <?php echo VBSEO_ENABLED?'':'CHECKED '?> value=0
name=activate>
<?php echo $alang['no']?> </td>
</tr>
<tr class=altfirst>
<td width="50%"><?php echo $alang['language_desc']?> </td>
<td><select name="vbseocplang" id="archive">
<?php foreach($vbseocp_languages as $lng)
echo '
<option value="'.$lng.'" '.($lng==VBSEO_CP_LANGUAGE?'selected':'').'>'.str_replace('( ','(',ucwords(str_replace('(','( ',$lng))).'</option>
';
?>
</select></td>
</tr>
 
<tr class=altsecond>
<td>
<?php echo $alang['redir_desc']?>                                        </td>
<td><input type=radio <?php echo VBSEO_THREAD_301_REDIRECT?'CHECKED ':''?> value=1
name=thread301redirect>
<?php echo $alang['yes']?>
<input type=radio <?php echo VBSEO_THREAD_301_REDIRECT?'':'CHECKED '?> value=0
name=thread301redirect>
<?php echo $alang['no']?></td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['domname_desc']?>                                        </td>
<td><input
name=include_domain type=radio value=1  <?php echo VBSEO_USE_HOSTNAME_IN_URL?'CHECKED ':''?>>
<?php echo $alang['yes']?>
<input type=radio value=0 name=include_domain <?php echo VBSEO_USE_HOSTNAME_IN_URL?'':'CHECKED '?>>
<?php echo $alang['no']?> </td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['noneng_desc']?>                                        </td>
<td>
<select name="foreignchars" id="foreignchars">
<option value="1" <?php echo VBSEO_FILTER_FOREIGNCHARS==1?'selected':''?>><?php echo $alang['noneng_rem']?></option>
<option value="2" <?php echo VBSEO_FILTER_FOREIGNCHARS==2?'selected':''?>><?php echo $alang['noneng_repl']?></option>
<option value="0" <?php echo VBSEO_FILTER_FOREIGNCHARS==0?'selected':''?>><?php echo $alang['noneng_keep']?></option>
</select></td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['redirext_desc']?></td>
<td><input name=redirect_ext_priv type=radio value=1 <?php echo VBSEO_REDIRECT_PRIV_EXTERNAL?'CHECKED ':''?>>
<?php echo $alang['yes']?>
<input type=radio value=0  <?php echo VBSEO_REDIRECT_PRIV_EXTERNAL?'':'CHECKED '?>
name=redirect_ext_priv>
<?php echo $alang['no']?> </td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['email_desc']?>                                       </td>
<td><input type=radio <?php echo VBSEO_REWRITE_EMAILS?'CHECKED ':''?> value=1
name=emails_urls>
<?php echo $alang['yes']?>
<input type=radio value=0 <?php echo VBSEO_REWRITE_EMAILS?'':'CHECKED '?>
name=emails_urls>
<?php echo $alang['no']?>    </td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['link_desc']?>                                          </td>
<td><input type=radio <?php echo VBSEO_LINK?'CHECKED ':''?> value=1
name=vbseolink>
<?php echo $alang['yes']?>
<input type=radio <?php echo VBSEO_LINK?'':'CHECKED '?> value=0
name=vbseolink>
<?php echo $alang['no']?> </td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['fnf_desc']?>                                         </td>
<td><input type=radio <?php echo VBSEO_404_HANDLE==0?'CHECKED ':''?> value=0 name=hp404>
<?php echo $alang['redire_hp']?> <br />
<input type=radio <?php echo VBSEO_404_HANDLE==1?'CHECKED ':''?> value=1 name=hp404>
<?php echo $alang['send_404']?> <br />
<input type=radio <?php echo VBSEO_404_HANDLE==2?'CHECKED ':''?> value=2 name=hp404 id=hp404_id>
<?php echo $alang['inc_custom']?>: <br />
<input type=text value="<?php echo VBSEO_404_CUSTOM?>" name=hp404custom size=34 onkeyup="if(this.value)document.getElementById('hp404_id').checked=true">                                          </td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['exclpages_desc']?>                                        </td>
<td><textarea name="ignorepages" cols="34" rows="5" wrap="VIRTUAL" id="ignorepages"><?php
$sw_a = explode('|',VBSEO_IGNOREPAGES);
foreach($sw_a as $v){
echo "
".htmlspecialchars($v)."";
}
?></textarea></td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['vbseo_affid']?>                                          </td>
<td><input name="aff_id" type="text" id="aff_id" size="5" value="<?php echo VBSEO_AFFILIATE_ID?>"></td>
</tr>
<tr class=subheader>
<td colspan=2><a name="general_log"></a><?php echo $alang['log_opt']?></td>
</tr>
<tr class=altfirst>
<td>
<p>
<?php echo $alang['botact_desc']?>
<?php
$vbseo_sm_url = $vboptions['bburl2'].'/vbseo_sitemap/';
?>&nbsp;(<a href="<?php echo $vbseo_sm_url?>"><?php echo $vbseo_sm_url?></a>)</p></td>
<td><input type=radio <?php echo VBSEO_SITEMAP_MOD?'CHECKED ':''?> value=1
name=sitemap_mod>
<?php echo $alang['yes']?>
<input type=radio value=0 <?php echo VBSEO_SITEMAP_MOD?'':'CHECKED '?>
name=sitemap_mod>
<?php echo $alang['no']?> </td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['analytics_desc']?>                                       </td>
<td><input name=googlean type=radio value=1 <?php echo VBSEO_ADD_ANALYTICS_CODE?'CHECKED ':''?>>
<?php echo $alang['yes']?>
<input type=radio value=0  <?php echo VBSEO_ADD_ANALYTICS_CODE?'':'CHECKED '?>
name=googlean>
<?php echo $alang['no']?> </td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['analytics_code_desc']?>                                      </td>
<td><input type="text" size="45" name="googlean_code" id="googlean_code" value="<?php echo VBSEO_ANALYTICS_CODE?>">                                        </td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['analytics_track_desc']?>                                     </td>
<td><input name=googleanext type=radio value=1 <?php echo VBSEO_ADD_ANALYTICS_CODE_EXT?'CHECKED ':''?>>
<?php echo $alang['yes']?>
<input type=radio value=0  <?php echo VBSEO_ADD_ANALYTICS_CODE_EXT?'':'CHECKED '?>
name=googleanext>
<?php echo $alang['no']?> </td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['analytics_format_desc']?></td>
<td><input type="text" size="45" name="googleanext_format" id="googleanext_format" value="<?php echo VBSEO_ANALYTICS_EXT_FORMAT?>">                                        </td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['funnel_desc']?></td>
<td><input name=googleadgoal type=radio value=1 <?php echo VBSEO_ADD_ANALYTICS_GOAL?'CHECKED ':''?>>
<?php echo $alang['yes']?>
<input type=radio value=0  <?php echo VBSEO_ADD_ANALYTICS_GOAL?'':'CHECKED '?>
name=googleadgoal>
<?php echo $alang['no']?> </td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['segmentation_desc']?></td>
<td><?php echo $alang['sel_one']?>:<br />
<select name="segmentation" id="segmentation">
<option value="1" <?php echo VBSEO_ANALYTICS_SEGMENTATION==1?'selected':''?>><?php echo $alang['segmentation_1']?></option>
<option value="2" <?php echo VBSEO_ANALYTICS_SEGMENTATION==2?'selected':''?>><?php echo $alang['segmentation_2']?></option>
<option value="0" <?php echo VBSEO_ANALYTICS_SEGMENTATION==0?'selected':''?>><?php echo $alang['segmentation_0']?></option>
</select></td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['adsense_desc']?></td>
<td><input name=googleadsec type=radio value=1 <?php echo VBSEO_GOOGLE_AD_SEC?'CHECKED ':''?>>
<?php echo $alang['yes']?>
<input type=radio value=0  <?php echo VBSEO_GOOGLE_AD_SEC?'':'CHECKED '?>
name=googleadsec>
<?php echo $alang['no']?> </td>
</tr>
<tr class=subheader>
<td colspan=2><a name="general_arc"></a><?php echo $alang['arc_opt']?></td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['arcopt_desc']?>                                        </td>
<td><?php echo $alang['sel_one']?>:<br />
<select name="archive" id="archive">
<option value="arc1" <?php echo VBSEO_REDIRECT_ARCHIVE?'selected':''?>><?php echo $alang['arc_redir_301']?></option>
<option value="arc2" <?php echo VBSEO_REWRITE_ARCHIVE_URLS?'selected':''?>><?php echo $alang['arc_rewr_vbarc']?></option>
<option value="arc3" <?php echo VBSEO_REWRITE_ARCHIVE_URLS&&VBSEO_REDIRECT_ARCHIVE?'selected':''?>><?php echo $alang['arc_redir_rewr']?></option>
<option value="arc4" <?php echo !VBSEO_REWRITE_ARCHIVE_URLS&&!VBSEO_REDIRECT_ARCHIVE?'selected':''?>><?php echo $alang['arc_keep']?></option>
</select></td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['arcroot_desc']?></td>
<td><?php
$bformat = array(
'/archive/index.php/',
'/archive/',
'/sitemap/',
);
$fnd=false;
foreach($bformat as $k=>$v){
$tf = (VBSEO_ARCHIVE_ROOT==$v);
if($tf)$fnd = true;
echo '<input type=radio value="'.$v.'" name=arcroot '.($tf?'CHECKED ':'').'>'.$v."<br />\n";
}
?>
<input type=radio value="custom" name=arcroot <?php echo $fnd?'':' checked'?> id="arcroot_id">
<?php echo $alang['custom']?>:<br />
<input name=cust_arcroot value="<?php echo $fnd?'':VBSEO_ARCHIVE_ROOT?>" size=34 onKeyUp="if(this.value)document.getElementById('arcroot_id').checked=true"></td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['arcorder_desc']?></td>
<td>
<input name=arcorder type=radio value=1 <?php echo VBSEO_ARCHIVE_ORDER_DESC?'CHECKED ':''?>>
<?php echo $alang['yes']?>
<input type=radio value=0  <?php echo VBSEO_ARCHIVE_ORDER_DESC?'':'CHECKED '?>
name=arcorder>
<?php echo $alang['no']?>
</td>
</tr>
<tr class=subheader>
<td colspan=2><a name="general_cache"></a><?php echo $alang['cache_opt']?></td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['cachetype_desc']?></td>
<td>
<script language="Javascript">
function on_cacheopt_change(thisind)
{
document.getElementById('memcacheopt').style.display = (thisind==1) ? '' : 'none'
}
</script>
<?php
if(class_exists('Memcache'))
{
$memc = new Memcache;
}
$bformat = array(
$alang['none'],
'memcached '.($memc?$alang['supported']:$alang['unsupported']),
'APC Cache '.(function_exists('apc_store')?$alang['supported']:$alang['unsupported']),
'XCache '.(function_exists('xcache_get')?$alang['supported']:$alang['unsupported']),
'eAccelerator '.(function_exists('eaccelerator_get')?$alang['supported']:$alang['unsupported']),
);
$fnd=false;
foreach($bformat as $k=>$v){
echo '<input type=radio value="'.$k.'" onclick="on_cacheopt_change('.$k.')" name=cachetype id="cachetype'.$k.'" '.((VBSEO_CACHE_TYPE==$k)?'CHECKED ':'').'><label for="cachetype'.$k.'"> '.$v."</label><br />\n";
}
?>
</td>
</tr>
<tr class=altsecond id="memcacheopt"<?php echo (VBSEO_CACHE_TYPE==1)?'':' style="display:none"'?>>
<td valign=top>
<?php echo $alang['memcacheopt_desc']?></td>
<td>
<table cellspacing="0" cellpadding="0">
<tr valign="top"><td>
<?php echo $alang['mc_hosts']?>:<br>
<textarea name=mc_hosts rows="5" cols="35"><?php echo VBSEO_MEMCACHE_HOSTS?>
</textarea>
</td>
<td style="color:#999">
<?php echo $alang['mc_vbulletin']?>:<br>
<?php
@include dirname(__FILE__).'/includes/config.php';
if($config['Misc']['memcacheserver'])
{
$mcserver = $config['Misc']['memcacheserver'];
if(!is_array($mcserver)) $mcserver = array($mcserver);
$mcport = $config['Misc']['memcacheport'];
if(!is_array($mcport)) $mcport = array($mcport);
$mcweight = $config['Misc']['memcacheweight'];
if(!is_array($mcweight)) $mcweight = array($mcweight);
for($si=1;$si<=count($mcserver);$si++)
{
echo $mcserver[$si].':'.$mcport[$si].','.$mcweight[$si]."<br/>\n";
}
}
?>
</td></tr>
<tr><td>
<?php echo $alang['mc_pers']?>:<br>
<input type=radio  name=mc_pers value="1" <?php echo VBSEO_MEMCACHE_PERS?"checked":""?>><?php echo $alang['yes']?>
<input type=radio  name=mc_pers value="0" <?php echo VBSEO_MEMCACHE_PERS?"":"checked"?>><?php echo $alang['no']?>
</td>
<td style="color:#999"><br>
<?php
echo $config['Misc']['memcachepersistent'][1]?$alang['yes']:$alang['no'];
?>
</td></tr>
<tr><td>
<?php echo $alang['mc_ttl']?>:<br>
<input name=mc_ttl value="<?php echo VBSEO_MEMCACHE_TTL?>" size=15>
</td>
<td style="color:#999"><br>
 
</td></tr>
<tr><td>
<?php echo $alang['mc_retry']?>:<br>
<input name=mc_retry value="<?php echo VBSEO_MEMCACHE_RETRY?>" size=15><br>
</td>
<td style="color:#999"><br>
<?php
echo $config['Misc']['memcacheretry_interval'][1];
?>
</td></tr>
<tr><td>
<?php echo $alang['mc_timeout']?>:<br>
<input name=mc_timeout value="<?php echo VBSEO_MEMCACHE_TIMEOUT?>" size=15><br>
</td>
<td style="color:#999"><br>
<?php
echo $config['Misc']['memcachetimeout'][1];
?>
</td></tr>
<tr><td>
<?php echo $alang['mc_compress']?>:<br>
<input name=mc_compress value="<?php echo VBSEO_MEMCACHE_COMPRESS?>" size=15><br>
</td>
<td>
</td></tr>
</table>
</td>
</tr>
<tr class=subheader>
<td colspan=2><a name="general_tb"></a><?php echo $alang['tb_opt']?></td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['vbseo_tweetboard']?>                                          </td>
<td>
<input type=radio <?php echo VBSEO_TWEETBOARD?'CHECKED ':''?> value=1 name=vbseotb>
<?php echo $alang['yes']?>
<input type=radio <?php echo VBSEO_TWEETBOARD?'':'CHECKED '?> value=0 name=vbseotb>
<?php echo $alang['no']?> 
<br />
<br />
<?php echo $alang['twitter_user']?>:<br />
<input name="vbseotbuser" type="text" id="vbseotbuser" size="25" value="<?php echo VBSEO_TWEETBOARD_USER?>">
</td>
</tr>
<tr align="right" class=header>
<td colspan=2><input type=submit value="<?php echo $alang['save_set']?>" name=edit onclick="this.form['jumpto'].value='general'">
&nbsp;<input type="reset" name="Reset" value="<?php echo $alang['reset']?>"></td>
</tr>
</tbody>
</table>
<table cellSpacing=0 cellPadding=0
width="100%" border=0>
<tbody>
<tr>
<td height="30">&nbsp;</td>
</tr>
</tbody>
</table>
<script language="Javascript">
function js_hideshow(elid, hs)
{
document.getElementById(elid).style.display = hs ? '' : 'none'
}
function js_load_preset(type)
{
presetvalue=document.forms['settingsform'].elements['preset'+type].value
if(presetvalue)
if(confirm("<?php echo $alang['load_preset_warn']?>"))
{
top.location='vbseocp.php?putpreset=1&jumpto=url&preset='+presetvalue+'&type='+type
}
}
</script>
 
<table class=formtbl cellSpacing=1 cellPadding=4
width="100%" border=0>
<tr class=header>
<td colspan=2>
<div id="forumlink"><a href="http://www.vbseo.com/f9/" target="_blank" ><?php echo $alang['url_set_forum']?></a></div>
<div><a name="url" id="url"></a><?php echo $alang['url_opt']?></div>
</td>
</tr>
<tr class=altfirst>
<td colspan="2" >
<?php echo $alang['gen_opt_description']?>
</td>
</tr>
<tr class=subheader>
<td colspan=2>
<a name="url_gen" id="url_gen"></a><?php echo $alang['url_gen']?>
</td>
</tr>
<tr class=altsecond>
<td width="50%">
<?php echo $alang['sep_desc']?></td>
<td><select name="spacer" id="spacer">
<option value="-" <?php echo VBSEO_SPACER=='-'?'selected':''?>>[ - ]</option>
<option value="_" <?php echo VBSEO_SPACER=='_'?'selected':''?>>[ _ ]</option>
<option value="." <?php echo VBSEO_SPACER=='.'?'selected':''?>>[ . ]</option>
</select></td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['limurl_desc']?>
</td>
<td><input name="length_url_part" type="text" id="length_url_part" size="5" value="<?php echo VBSEO_URL_PART_MAX?>">
</td>
</tr>
<tr class=subheader>
<td colspan=2>
<a name="url_forum" id="url_forum"></a><?php echo $alang['url_forum']?>
</td>
</tr>
<tr class=altfirst>
<td colspan="2" >
<?php echo $alang['forum_opt_description']?>
 
<div style="padding:10px; border:solid 1px black; background-color:#f2f2f2">
<?php echo $alang['current_preset']?>: <?php echo $current_preset_forum ? '<b style="color:green">'.$vbseocp_presets[$current_preset_forum].'</b>' : '<b style="color:red">'.$alang['custom_settings'].'</b>' ?>
<br /><br />
<select name="presetforum" id="selpreset">
<option value="" selected></option>
<?php
$pres_i = 0;
foreach($vbseocp_presets_f as $pset=>$ptitle)
{
$pres_i=intval($pset);
echo '
<option value="'.$pset.'">'.($alang['preset_'.$pres_i]?$alang['preset_'.$pres_i]:$ptitle).'</option>
';
}
?>
</select>
<input id="button" type="button" name="Submit" value="<?php echo $alang['load_preset']?>"
onclick="js_load_preset('forum')">
<br /><br />
<b><?php echo $alang['adv_settings']?></b> (<a href="#" onclick="js_hideshow('urlsettings_forum',true);return false;">+</a> / <a href="#" onclick="js_hideshow('urlsettings_forum',false);return false;">-</a>)
</div>
<table id="urlsettings_forum" class=formtbl cellSpacing=1 cellPadding=4 style="display:<?php    
if(!$_GET['vbseodbg']){?>none<?php }     
?>; border-top:none" width="100%" border=0>
<tbody>
 
<tr class=subheader>
<td colspan=2><a name="url_general"></a><?php echo $alang['path_opt']?></td>
</tr>
<?php
vbseo_display_option_formats($alang['pathbits_desc'], 'bformat', 
array(
'[forum_id]',
'[forum_title]',
'forum[forum_id]',
), 
VBSEO_FORUM_TITLE_BIT);
?>
<tr class=subheader>
<td colspan=2><?php echo $alang['urls_forum']?></td>
</tr>
<?php
vbseo_display_option_yesno($alang['rw_forum_desc'], 'forum_urls', VBSEO_REWRITE_FORUM);
vbseo_display_option_formats($alang['forum_format_desc'], 'fformat',
array(
'[forum_path]/',
'[forum_title]/',
'forum[forum_id]/',
'[forum_title].html',
), 
VBSEO_URL_FORUM
);
vbseo_display_option_formats($alang['forum_pformat_desc'], 'findexformat',
array(
'[forum_path]/index[forum_page].html',
'[forum_title]/index[forum_page].html',
'forum[forum_id]/index[forum_page].html',
'[forum_title]-[forum_page].html',
), 
VBSEO_URL_FORUM_PAGENUM);
?>
<tr class=subheader>
<td colspan=2><a name="url_announcement" id="url_announcement"></a><?php echo $alang['urls_ann']?></td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['rw_ann_desc']?>                                          </td>
<td><input type=radio <?php echo VBSEO_REWRITE_ANNOUNCEMENT?'CHECKED ':''?> value=1
name=announcement_urls>
<?php echo $alang['yes']?>
<input type=radio value=0 <?php echo VBSEO_REWRITE_ANNOUNCEMENT?'':'CHECKED '?>
name=announcement_urls>
<?php echo $alang['no']?> </td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['ann_format_desc']?>                                          </td>
<td><?php
$aformat = array(
'%forum_path%/announcement-%announcement_title%.html'=>'[forum_path]/announcement-[announcement_title].html',
'%forum_title%/announcement-%announcement_title%.html'=>'[forum_title]/announcement-[announcement_title].html',
'forum%forum_id%/announcement%announcement_id%.html'=>'forum[forum_id]/announcement[announcement_id].html',
'%forum_title%-announcement-%announcement_title%.html'=>'[forum_title]-announcement-[announcement_title].html',
);
$fnd=false;
foreach($aformat as $k=>$v){
$tf = (VBSEO_URL_FORUM_ANNOUNCEMENT==$k);
if($tf)$fnd = true;
echo '<input type=radio value="'.$v.'" name=aformat '.($tf?'CHECKED ':'').'>
'.$v.'<br />';
}
?>
<input type=radio value="custom" name=aformat <?php echo $fnd?'':' checked'?> id="aformat_id">
<?php echo $alang['custom']?>:<br />
<input name=cust_aformat value="<?php echo $fnd?'':uformat_prep(VBSEO_URL_FORUM_ANNOUNCEMENT)?>" size=34 onkeyup="if(this.value)document.getElementById('aformat_id').checked=true">                                          </td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['ann_pformat_desc']?>                                          </td>
<td><?php
$amultiformat = array(
'%forum_path%/announcements.html'=>'[forum_path]/announcements.html',
'%forum_title%/announcements.html'=>'[forum_title]/announcements.html',
'forum%forum_id%/announcements.html'=>'forum[forum_id]/announcements.html',
'%forum_title%-announcements.html'=>'[forum_title]-announcements.html',
);
$fnd=false;
foreach($amultiformat as $k=>$v){
$tf = (VBSEO_URL_FORUM_ANNOUNCEMENT_ALL==$k);
if($tf)$fnd = true;
echo '<input type=radio value="'.$v.'" name=amultiformat '.($tf?'CHECKED ':'').'>
'.$v.'<br />';
}
?>
<input type=radio value="custom" name=amultiformat <?php echo $fnd?'':' checked'?> id="amultiformat_id">
<?php echo $alang['custom']?>:<br />
<input name=cust_amultiformat value="<?php echo $fnd?'':uformat_prep(VBSEO_URL_FORUM_ANNOUNCEMENT_ALL)?>" size=34 onkeyup="if(this.value)document.getElementById('amultiformat_id').checked=true">                                          </td>
</tr>
<tr class=subheader>
<td colspan=2><a name="url_thread" id="url_thread"></a><?php echo $alang['urls_thread']?></td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['rw_thread_desc']?>                                          </td>
<td><input type=radio <?php echo VBSEO_REWRITE_THREADS?'CHECKED ':''?> value=1
name=thread_urls>
<?php echo $alang['yes']?>
<input type=radio value=0 <?php echo VBSEO_REWRITE_THREADS?'':'CHECKED '?>
name=thread_urls>
<?php echo $alang['no']?> </td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['thread_format_desc']?>                                          </td>
<td><?php
$tformat = array(
'%forum_path%/%thread_id%-%thread_title%.html'=>'[forum_path]/[thread_id]-[thread_title].html',
'%forum_title%/%thread_id%-%thread_title%.html'=>'[forum_title]/[thread_id]-[thread_title].html',
'forum%forum_id%/thread%thread_id%.html'=>'forum[forum_id]/thread[thread_id].html',
'%thread_id%-%thread_title%.html'=>'[thread_id]-[thread_title].html',
);
$fnd=false;
foreach($tformat as $k=>$v){
$tf = (VBSEO_URL_THREAD==$k);
if($tf)$fnd = true;
echo '<input type=radio value="'.$v.'" name=tformat '.($tf?'CHECKED ':'').'>
'.$v.'<br />';
}
?>
<input type=radio value="custom" name=tformat <?php echo $fnd?'':' checked'?> id="tformat_id">
<?php echo $alang['custom']?>:<br />
<input size=34 value="<?php echo $fnd?'':uformat_prep(VBSEO_URL_THREAD)?>" name=cust_tformat onkeyup="if(this.value)document.getElementById('tformat_id').checked=true">                                          </td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['thread_pformat_desc']?>                                          </td>
<td><?php
$tmultiformat = array(
'%forum_path%/%thread_id%-%thread_title%-%thread_page%.html'=>'[forum_path]/[thread_id]-[thread_title]-[thread_page].html',
'%forum_title%/%thread_id%-%thread_title%-%thread_page%.html'=>'[forum_title]/[thread_id]-[thread_title]-[thread_page].html',
'forum%forum_id%/thread%thread_id%-%thread_page%.html'=>'forum[forum_id]/thread[thread_id]-[thread_page].html',
'%thread_id%-%thread_title%-%thread_page%.html'=>'[thread_id]-[thread_title]-[thread_page].html',
);
$fnd=false;
foreach($tmultiformat as $k=>$v){
$tf = (VBSEO_URL_THREAD_PAGENUM==$k);
if($tf)$fnd = true;
echo '<input type=radio value="'.$v.'" name=tmultiformat '.($tf?'CHECKED ':'').'>
'.$v.'<br />';
}
?>
<input type=radio value="custom" name=tmultiformat <?php echo $fnd?'':' checked'?> id="tmultiformat_id">
<?php echo $alang['custom']?>:<br />
<input name=cust_tmultiformat value="<?php echo $fnd?'':uformat_prep(VBSEO_URL_THREAD_PAGENUM)?>" size=34 onkeyup="if(this.value)document.getElementById('tmultiformat_id').checked=true"></td>
</tr>
<?php if(VBSEO_ENABLE_GARS){?>
<tr class=altsecond>
<td>
<?php echo $alang['thread_gformat_desc']?>                                          </td>
<td><?php
$tmultiformat = array(
'%forum_title%/%thread_id%-%thread_title%-gars%thread_page%.html'=>'[forum_title]/[thread_id]-[thread_title]-gars[thread_page].html',
);
$fnd=false;
foreach($tmultiformat as $k=>$v){
$tf = (VBSEO_URL_THREAD_GARS_PAGENUM==$k);
if($tf)$fnd = true;
echo '<input type=radio value="'.$v.'" name=tgarsmultiformat '.($tf?'CHECKED ':'').'>
'.$v.'<br />';
}
?>
<input type=radio value="custom" name=tgarsmultiformat <?php echo $fnd?'':' checked'?> id="tgarsmultiformat_id">
<?php echo $alang['custom']?>:<br />
<input name=cust_tgarsmultiformat value="<?php echo $fnd?'':uformat_prep(VBSEO_URL_THREAD_GARS_PAGENUM)?>" size=34 onkeyup="if(this.value)document.getElementById('tgarsmultiformat_id').checked=true"></td>
</tr>
<?php } ?>
<tr class=altsecond>
<td>
<?php echo $alang['thread_lpformat_desc']?>                                          </td>
<td><?php
$tlastpostformat = array(
'%forum_path%/%thread_id%-%thread_title%-last-post.html'=>'[forum_path]/[thread_id]-[thread_title]-last-post.html',
'%forum_title%/%thread_id%-%thread_title%-last-post.html'=>'[forum_title]/[thread_id]-[thread_title]-last-post.html',
'forum%forum_id%/thread%thread_id%-last-post.html'=>'forum[forum_id]/thread[thread_id]-last-post.html',
'%thread_id%-%thread_title%-last-post.html'=>'[thread_id]-[thread_title]-last-post.html',
);
$fnd=false;
foreach($tlastpostformat as $k=>$v){
$tf = (VBSEO_URL_THREAD_LASTPOST==$k);
if($tf)$fnd = true;
echo '<input type=radio value="'.$v.'" name=tlastpostformat '.($tf?'CHECKED ':'').'>
'.$v.'<br />';
}
?>
<input type=radio value="custom" name=tlastpostformat <?php echo $fnd?'':' checked'?> id="tlastpostformat_id">
<?php echo $alang['custom']?>:<br />
<input name=cust_tlastpostformat id="cust_tlastpostformat_id" value="<?php echo $fnd?'':uformat_prep(VBSEO_URL_THREAD_LASTPOST)?>" size=34 onkeyup="if(this.value)document.getElementById('tlastpostformat_id').checked=true"></td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['thread_npformat_desc']?>                                          </td>
<td><?php
$tnewpostformat = array(
'%forum_path%/%thread_id%-%thread_title%-new-post.html'=>'[forum_path]/[thread_id]-[thread_title]-new-post.html',
'%forum_title%/%thread_id%-%thread_title%-new-post.html'=>'[forum_title]/[thread_id]-[thread_title]-new-post.html',
'forum%forum_id%/thread%thread_id%-new-post.html'=>'forum[forum_id]/thread[thread_id]-new-post.html',
'%thread_id%-%thread_title%-new-post.html'=>'[thread_id]-[thread_title]-new-post.html',
);
$fnd=false;
foreach($tnewpostformat as $k=>$v){
$tf = (VBSEO_URL_THREAD_NEWPOST==$k);
if($tf)$fnd = true;
echo '<input type=radio value="'.$v.'" name=tnewpostformat '.($tf?'CHECKED ':'').'>
'.$v.'<br />';
}
?>
<input type=radio value="custom" name=tnewpostformat <?php echo $fnd?'':' checked'?> id="tnewpostformat_id">
<?php echo $alang['custom']?>:<br />
<input name=cust_tnewpostformat value="<?php echo $fnd?'':uformat_prep(VBSEO_URL_THREAD_NEWPOST)?>" size=34 onkeyup="if(this.value)document.getElementById('tnewpostformat_id').checked=true"></td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['thread_gpformat_desc']?>                                          </td>
<td><?php
$tgotopostformat = array(
'%forum_path%/%thread_id%-%thread_title%-post%post_id%.html'=>'[forum_path]/[thread_id]-[thread_title]-post[post_id].html',
'%forum_title%/%thread_id%-%thread_title%-post%post_id%.html'=>'[forum_title]/[thread_id]-[thread_title]-post[post_id].html',
'forum%forum_id%/thread%thread_id%-post%post_id%.html'=>'forum[forum_id]/thread[thread_id]-post[post_id].html',
'%thread_id%-%thread_title%-post%post_id%.html'=>'[thread_id]-[thread_title]-post[post_id].html',
);
$fnd=false;
foreach($tgotopostformat as $k=>$v){
$tf = (VBSEO_URL_THREAD_GOTOPOST==$k);
if($tf)$fnd = true;
echo '<input type=radio value="'.$v.'" name=tgotopostformat '.($tf?'CHECKED ':'').'>
'.$v.'<br />';
}
?>
<input type=radio value="custom" name=tgotopostformat <?php echo $fnd?'':' checked'?> id="tgotopostformat_id">
<?php echo $alang['custom']?>:<br />
<input name=cust_tgotopostformat value="<?php echo $fnd?'':uformat_prep(VBSEO_URL_THREAD_GOTOPOST)?>" size=34 onkeyup="if(this.value)document.getElementById('tgotopostformat_id').checked=true"></td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['thread_gppformat_desc']?>                                          </td>
<td><?php
$tmultigotopostformat = array(
'%forum_path%/%thread_id%-%thread_title%-post%post_id%-%thread_page%.html'=>'[forum_path]/[thread_id]-[thread_title]-post[post_id]-[thread_page].html',
'%forum_title%/%thread_id%-%thread_title%-post%post_id%-%thread_page%.html'=>'[forum_title]/[thread_id]-[thread_title]-post[post_id]-[thread_page].html',
'forum%forum_id%/thread%thread_id%-post%post_id%-%thread_page%.html'=>'forum[forum_id]/thread[thread_id]-post[post_id]-[thread_page].html',
'%thread_id%-%thread_title%-post%post_id%-%thread_page%.html'=>'[thread_id]-[thread_title]-post[post_id]-[thread_page].html',
);
$fnd=false;
foreach($tmultigotopostformat as $k=>$v){
$tf = (VBSEO_URL_THREAD_GOTOPOST_PAGENUM==$k);
if($tf)$fnd = true;
echo '<input type=radio value="'.$v.'" name=tmultigotopostformat '.($tf?'CHECKED ':'').'>
'.$v.'<br />';
}
?>
<input type=radio value="custom" name=tmultigotopostformat <?php echo $fnd?'':' checked'?> id="tmultigotopostformat_id">
<?php echo $alang['custom']?>:<br />
<input name=cust_tmultigotopostformat value="<?php echo $fnd?'':uformat_prep(VBSEO_URL_THREAD_GOTOPOST_PAGENUM)?>" size=34 onkeyup="if(this.value)document.getElementById('tmultigotopostformat_id').checked=true"></td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['thread_ptformat_desc']?>                                          </td>
<td><?php
$tprevthreadformat = array(
'%forum_path%/%thread_id%-%thread_title%-prev-thread.html'=>'[forum_path]/[thread_id]-[thread_title]-prev-thread.html',
'%forum_title%/%thread_id%-%thread_title%-prev-thread.html'=>'[forum_title]/[thread_id]-[thread_title]-prev-thread.html',
'forum%forum_id%/thread%thread_id%-prev-thread.html'=>'forum[forum_id]/thread[thread_id]-prev-thread.html',
'%thread_id%-%thread_title%-prev-thread.html'=>'[thread_id]-[thread_title]-prev-thread.html',
);
$fnd=false;
foreach($tprevthreadformat as $k=>$v){
$tf = (VBSEO_URL_THREAD_PREV==$k);
if($tf)$fnd = true;
echo '<input type=radio value="'.$v.'" name=tprevthreadformat '.($tf?'CHECKED ':'').'>
'.$v.'<br />';
}
?>
<input type=radio value="custom" name=tprevthreadformat <?php echo $fnd?'':' checked'?> id="tprevthreadformat_id">
<?php echo $alang['custom']?>:<br />
<input name=cust_tprevthreadformat value="<?php echo $fnd?'':uformat_prep(VBSEO_URL_THREAD_PREV)?>" size=34 onkeyup="if(this.value)document.getElementById('tprevthreadformat_id').checked=true"></td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['thread_ntformat_desc']?>                                          </td>
<td><?php
$tnextthreadformat = array(
'%forum_path%/%thread_id%-%thread_title%-next-thread.html'=>'[forum_path]/[thread_id]-[thread_title]-next-thread.html',
'%forum_title%/%thread_id%-%thread_title%-next-thread.html'=>'[forum_title]/[thread_id]-[thread_title]-next-thread.html',
'forum%forum_id%/thread%thread_id%-next-thread.html'=>'forum[forum_id]/thread[thread_id]-next-thread.html',
'%thread_id%-%thread_title%-next-thread.html'=>'[thread_id]-[thread_title]-next-thread.html',
);
$fnd=false;
foreach($tnextthreadformat as $k=>$v){
$tf = (VBSEO_URL_THREAD_NEXT==$k);
if($tf)$fnd = true;
echo '<input type=radio value="'.$v.'" name=tnextthreadformat '.($tf?'CHECKED ':'').'>
'.$v.'<br />';
}
?>
<input type=radio value="custom" name=tnextthreadformat <?php echo $fnd?'':' checked'?> id="tnextthreadformat_id">
<?php echo $alang['custom']?>:<br />
<input name=cust_tnextthreadformat value="<?php echo $fnd?'':uformat_prep(VBSEO_URL_THREAD_NEXT)?>" size=34 onkeyup="if(this.value)document.getElementById('tnextthreadformat_id').checked=true"></td>
</tr>
<tr class=subheader>
<td colspan=2><a name="url_print" id="url_print"></a><?php echo $alang['urls_prnthread']?></td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['rw_pthread_desc']?>                                          </td>
<td><input type=radio <?php echo VBSEO_REWRITE_PRINTTHREAD?'CHECKED ':''?> value=1
name=printthread_urls>
<?php echo $alang['yes']?>
<input type=radio value=0 <?php echo VBSEO_REWRITE_PRINTTHREAD?'':'CHECKED '?>
name=printthread_urls>
<?php echo $alang['no']?> </td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['pthread_rel_desc']?>                                          </td>
<td>
<input name=nofollow_printthread type=radio value=1 <?php echo VBSEO_NOFOLLOW_PRINTTHREAD?'CHECKED ':''?>>
<?php echo $alang['yes']?>
<input type=radio value=0  <?php echo VBSEO_NOFOLLOW_PRINTTHREAD?'':'CHECKED '?>
name=nofollow_printthread>
<?php echo $alang['no']?>                                  </td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['pthread_format_desc']?>                                          </td>
<td><?php
$tprintthreadformat = array(
'%forum_path%/%thread_id%-%thread_title%-print.html'=>'[forum_path]/[thread_id]-[thread_title]-print.html',
'%forum_title%/%thread_id%-%thread_title%-print.html'=>'[forum_title]/[thread_id]-[thread_title]-print.html',
'forum%forum_id%/thread%thread_id%-print.html'=>'forum[forum_id]/thread[thread_id]-print.html',
'%thread_id%-%thread_title%-print.html'=>'[thread_id]-[thread_title]-print.html',
);
$fnd=false;
foreach($tprintthreadformat as $k=>$v){
$tf = (VBSEO_URL_THREAD_PRINT==$k);
if($tf)$fnd = true;
echo '<input type=radio value="'.$v.'" name=tprintthreadformat '.($tf?'CHECKED ':'').'>
'.$v.'<br />';
}
?>
<input type=radio value="custom" name=tprintthreadformat <?php echo $fnd?'':' checked'?> id="tprintthreadformat_id">
<?php echo $alang['custom']?>:<br />
<input name=cust_tprintthreadformat value="<?php echo $fnd?'':uformat_prep(VBSEO_URL_THREAD_PRINT)?>" size=34 onkeyup="if(this.value)document.getElementById('tprintthreadformat_id').checked=true"></td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['pthread_pformat_desc']?>                                          </td>
<td><?php
$tmultiprintformat = array(
'%forum_path%/%thread_id%-%thread_title%-%thread_page%-print.html'=>'[forum_path]/[thread_id]-[thread_title]-[thread_page]-print.html',
'%forum_title%/%thread_id%-%thread_title%-%thread_page%-print.html'=>'[forum_title]/[thread_id]-[thread_title]-[thread_page]-print.html',
'forum%forum_id%/thread%thread_id%-%thread_page%-print.html'=>'forum[forum_id]/thread[thread_id]-[thread_page]-print.html',
'%thread_id%-%thread_title%-%thread_page%-print.html'=>'[thread_id]-[thread_title]-[thread_page]-print.html',
);
$fnd=false;
foreach($tmultiprintformat as $k=>$v){
$tf = (VBSEO_URL_THREAD_PRINT_PAGENUM==$k);
if($tf)$fnd = true;
echo '<input type=radio value="'.$v.'" name=tmultiprintformat '.($tf?'CHECKED ':'').'>
'.$v.'<br />';
}
?>
<input type=radio value="custom" name=tmultiprintformat <?php echo $fnd?'':' checked'?> id="tmultiprintformat_id">
<?php echo $alang['custom']?>:<br />
<input name=cust_tmultiprintformat value="<?php echo $fnd?'':uformat_prep(VBSEO_URL_THREAD_PRINT_PAGENUM)?>" size=34 onkeyup="if(this.value)document.getElementById('tmultiprintformat_id').checked=true"></td>
</tr>
<tr class=subheader>
<td colspan=2><a name="url_showpost" id="url_showpost"></a><?php echo $alang['urls_post']?></td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['rw_post_desc']?>                                          </td>
<td>
<input type=radio value=1 <?php echo (VBSEO_REWRITE_SHOWPOST==1)?'CHECKED ':''?> name=showpost_urls>
<?php echo $alang['yes']?>
<input type=radio value=0 <?php echo VBSEO_REWRITE_SHOWPOST?'':'CHECKED '?> name=showpost_urls>
<?php echo $alang['no']?></td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['post_rel_desc']?>                                          </td>
<td>
<input name=nofollow_showpost type=radio value=1 <?php echo (VBSEO_NOFOLLOW_SHOWPOST==1)?'CHECKED ':''?>>
<?php echo $alang['yes']?>
<input type=radio value=0  <?php echo (VBSEO_NOFOLLOW_SHOWPOST==0)?'CHECKED ':''?>
name=nofollow_showpost>
<?php echo $alang['no']?>
<input type=radio value=2  <?php echo (VBSEO_NOFOLLOW_SHOWPOST==2)?'CHECKED ':''?>
name=nofollow_showpost>
<?php echo $alang['smart']?></td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['post_format_desc']?>                                          </td>
<td><?php
$tshowpostformat = array(
'%post_id%-post%post_count%.html'=>'[post_id]-post[post_count].html',
);
$fnd=false;
foreach($tshowpostformat as $k=>$v){
$tf = (VBSEO_URL_POST_SHOW==$k);
if($tf)$fnd = true;
echo '<input type=radio value="'.$v.'" name=tshowpostformat '.($tf?'CHECKED ':'').'>
'.$v.'<br />';
}
?>
<input type=radio value="custom" name=tshowpostformat <?php echo $fnd?'':' checked'?> id="tshowpostformat_id">
<?php echo $alang['custom']?>:<br />
<input name=cust_tshowpostformat value="<?php echo $fnd?'':uformat_prep(VBSEO_URL_POST_SHOW)?>" size=34 onkeyup="if(this.value)document.getElementById('tshowpostformat_id').checked=true"></td>
</tr>
<tr class=subheader>
<td colspan=2><a name="url_poll" id="url_poll"></a><?php echo $alang['urls_poll']?></td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['rw_poll_desc']?>                                          </td>
<td><input type=radio <?php echo VBSEO_REWRITE_POLLS?'CHECKED ':''?> value=1
name=polls_urls>
<?php echo $alang['yes']?>
<input type=radio value=0 <?php echo VBSEO_REWRITE_POLLS?'':'CHECKED '?>
name=polls_urls>
<?php echo $alang['no']?> </td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['poll_format_desc']?>                                          </td>
<td><?php
$pformat = array(
'%forum_path%/poll-%poll_id%-%poll_title%.html'=>'[forum_path]/poll-[poll_id]-[poll_title].html',
'%forum_title%/poll-%poll_id%-%poll_title%.html'=>'[forum_title]/poll-[poll_id]-[poll_title].html',
'forum%forum_id%/poll%poll_id%.html'=>'forum[forum_id]/poll[poll_id].html',
'%forum_title%-poll%poll_id%-%poll_title%.html'=>'[forum_title]-poll[poll_id]-[poll_title].html',
);
$fnd=false;
foreach($pformat as $k=>$v){
$tf = (VBSEO_URL_POLL==$k);
if($tf)$fnd = true;
echo '<input type=radio value="'.$v.'" name=pformat '.($tf?'CHECKED ':'').'>
'.$v.'<br />';
}
?>
<input type=radio value="custom" name=pformat <?php echo $fnd?'':' checked'?> id="pformat_id">
<?php echo $alang['custom']?>:<br />
<input name=cust_pformat value="<?php echo $fnd?'':uformat_prep(VBSEO_URL_POLL)?>" size=34 onkeyup="if(this.value)document.getElementById('pformat_id').checked=true">                                          </td>
</tr>
<?php
vbseo_display_option_header('urls_member', 'url_member_profile');
vbseo_display_option_yesno($alang['rw_memb_desc'], 'member_urls', VBSEO_REWRITE_MEMBERS);
vbseo_display_option_formats($alang['memb_format_desc'], 'mformat',
array(
'members/[user_name]/',
'members/[user_name].html',
'members/[user_id].html',
'member-[user_name].html',
), 
VBSEO_URL_MEMBER
);
vbseo_display_option_formats($alang['memb_msg_format_desc'], 'mmsgformat',
array(
'members/[user_name]/index[page].html',
'members/[user_name]-page[page].html',
'members/[user_id]-[page].html',
'member-[user_name]-page[page].html',
), 
VBSEO_URL_MEMBER_MSGPAGE
);
vbseo_display_option_formats($alang['memb_conv_format_desc'], 'mconvformat',
array(
'members/[user_name]/[visitor_name]/',
'members/[user_name]-with-[visitor_name].html',
'members/[user_id]-with-[visitor_id].html',
'member-[user_name]-with-[visitor_name].html',
), 
VBSEO_URL_MEMBER_CONV
);
vbseo_display_option_formats($alang['memb_convpage_format_desc'], 'mconvpageformat',
array(
'members/[user_name]/[visitor_name]/index[page].html',
'members/[user_name]-with-[visitor_name]-page[page].html',
'members/[user_id]-with-[visitor_id]-page[page].html',
'member-[user_name]-with-[visitor_name]-page[page].html',
), 
VBSEO_URL_MEMBER_CONVPAGE
);
vbseo_display_option_formats($alang['memb_friends_format_desc'], 'mfriendsformat',
array(
'members/[user_name]/friends/index[page].html',
'members/[user_name]-friends-page[page].html',
'members/[user_id]-friends-[page].html',
'member-[user_name]-friends-page[page].html'
), 
VBSEO_URL_MEMBER_FRIENDSPAGE
);
vbseo_display_option_header('url_albums', 'url_albums');
vbseo_display_option_yesno($alang['rw_albums_desc'], 'malbums_urls', VBSEO_REWRITE_MALBUMS);
vbseo_display_option_formats($alang['memb_albums_home_format_desc'], 'malbumhomeformat',
array(
'members/albums/',
'members/albums.html',
'albums.html',
), 
VBSEO_URL_MEMBER_ALBUM_HOME
);
vbseo_display_option_formats($alang['memb_albums_home_page_format_desc'], 'malbumhomepageformat',
array(
'members/albums/index[page].html',
'members/albums-[page].html',
'albums-[page].html',
), 
VBSEO_URL_MEMBER_ALBUM_HOME_PAGE
);
vbseo_display_option_formats($alang['memb_albums_format_desc'], 'malbumsformat',
array(
'members/[user_name]/albums/',
'members/[user_name]-albums.html',
'members/[user_id]-albums.html',
'member-[user_name]-albums.html'
), 
VBSEO_URL_MEMBER_ALBUMS
);
vbseo_display_option_formats($alang['memb_albumspage_format_desc'], 'malbumspageformat',
array(
'members/[user_name]/albums/page[page].html',
'members/[user_name]-albums-page[page].html',
'members/[user_id]-albums-page[page].html',
'member-[user_name]-albums-page[page].html'
), 
VBSEO_URL_MEMBER_ALBUMS_PAGE
);
vbseo_display_option_formats($alang['memb_album_format_desc'], 'malbumformat',
array(
'members/[user_name]/albums/[album_title]/',
'members/[user_name]-albums-[album_title].html',
'members/[user_id]-albums[album_id].html',
'member-[user_name]-albums-[album_title].html'
), 
VBSEO_URL_MEMBER_ALBUM
);
vbseo_display_option_formats($alang['memb_albumpage_format_desc'], 'malbumpageformat',
array(
'members/[user_name]/albums/[album_title]/index[page].html',
'members/[user_name]-albums-[album_title]-page[page].html',
'members/[user_id]-albums[album_id]-page[page].html',
'member-[user_name]-albums-[album_title]-page[page].html'
), 
VBSEO_URL_MEMBER_ALBUM_PAGE
);
vbseo_display_option_formats($alang['memb_pic_format_desc'], 'mpicformat',
array(
'members/[user_name]/albums/[album_title]/[picture_id]-[picture_title]/',
'members/[user_name]-albums-[album_title]-picture[picture_id]-[picture_title].html',
'members/[user_id]-albums[album_id]-picture[picture_id].html',
'member-[user_name]-albums-[album_title]-picture[picture_id]-[picture_title].html'
), 
VBSEO_URL_MEMBER_PICTURE
);
vbseo_display_option_formats($alang['memb_picpage_format_desc'], 'mpicpageformat',
array(
'members/[user_name]/albums/[album_title]/[picture_id]-[picture_title]/index[page].html',
'members/[user_name]-albums-[album_title]-picture[picture_id]-[picture_title]-page[page].html',
'members/[user_id]-albums[album_id]-picture[picture_id]-page[page].html',
'member-[user_name]-albums-[album_title]-picture[picture_id]-[picture_title]-page[page].html'
), 
VBSEO_URL_MEMBER_PICTURE_PAGE
);
vbseo_display_option_formats($alang['memb_picimg_format_desc'], 'mpicimgformat',
array(
'members/[user_name]/albums/[album_title]/[picture_id]-[picture_title].[original_ext]',
'members/[user_name]-albums-[album_title]-picture[picture_id]-[picture_title].[original_ext]',
'members/[user_id]-albums[album_id]-picture[picture_id].[original_ext]',
'member-[user_name]-albums-[album_title]-picture[picture_id]-[picture_title].[original_ext]'
), 
VBSEO_URL_MEMBER_PICTURE_IMG
);
?>
<tr class=subheader>
<td colspan=2><a name="url_member_list" id="url_member_list"></a><?php echo $alang['urls_mlist']?></td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['rw_mlist_desc']?>                                          </td>
<td><input type=radio <?php echo VBSEO_REWRITE_MEMBER_LIST?'CHECKED ':''?> value=1
name=memberlist_urls>
<?php echo $alang['yes']?>
<input type=radio value=0 <?php echo VBSEO_REWRITE_MEMBER_LIST?'':'CHECKED '?>
name=memberlist_urls>
<?php echo $alang['no']?> </td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['mlist_format_desc']?>                                          </td>
<td><?php
$mlistformat = array(
'members/list/'=>'members/list/',
'memberlist.html'=>'memberlist.html',
);
$fnd=false;
foreach($mlistformat as $k=>$v){
$tf = (VBSEO_URL_MEMBERLIST==$k);
if($tf)$fnd = true;
echo '<input type=radio value="'.$v.'" name=mlistformat '.($tf?'CHECKED ':'').'>
'.$v.'<br />';
}
?>
<input type=radio value="custom" name=mlistformat <?php echo $fnd?'':' checked'?> id="mlistformat_id">
<?php echo $alang['custom']?>:<br />
<input name=cust_mlistformat value="<?php echo $fnd?'':uformat_prep(VBSEO_URL_MEMBERLIST)?>" size=34 onkeyup="if(this.value)document.getElementById('mlistformat_id').checked=true"></td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['mlist_pformat_desc']?>                                          </td>
<td><?php
$mpagesformat = array(
'members/list/index%page%.html'=>'members/list/index[page].html',
'memberlist-%page%.html'=>'memberlist-[page].html',
);
$fnd=false;
foreach($mpagesformat as $k=>$v){
$tf = (VBSEO_URL_MEMBERLIST_PAGENUM==$k);
if($tf)$fnd = true;
echo '<input type=radio value="'.$v.'" name=mpagesformat '.($tf?'CHECKED ':'').'>
'.$v.'<br />';
}
?>
<input type=radio value="custom" name=mpagesformat <?php echo $fnd?'':' checked'?> id="mpagesformat_id">
<?php echo $alang['custom']?>:<br />
<input name=cust_mpagesformat value="<?php echo $fnd?'':uformat_prep(VBSEO_URL_MEMBERLIST_PAGENUM)?>" size=34 onkeyup="if(this.value)document.getElementById('mpagesformat_id').checked=true"></td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['mlist_lformat_desc']?>                                          </td>
<td><?php
$mletterformat = array(
'members/list/%letter%%page%.html'=>'members/list/[letter][page].html',
'memberlist-%letter%%page%.html'=>'memberlist-[letter][page].html',
);
$fnd=false;
foreach($mletterformat as $k=>$v){
$tf = (VBSEO_URL_MEMBERLIST_LETTER==$k);
if($tf)$fnd = true;
echo '<input type=radio value="'.$v.'" name=mletterformat '.($tf?'CHECKED ':'').'>
'.$v.'<br />';
}
?>
<input type=radio value="custom" name=mletterformat <?php echo $fnd?'':' checked'?> id="mletterformat_id">
<?php echo $alang['custom']?>:<br />
<input name=cust_mletterformat value="<?php echo $fnd?'':uformat_prep(VBSEO_URL_MEMBERLIST_LETTER)?>" size=34 onkeyup="if(this.value)document.getElementById('mletterformat_id').checked=true"></td>
</tr>
<tr class=subheader>
<td colspan=2><a name="url_custom_avatar" id="url_custom_avatar"></a><?php echo $alang['urls_avatar']?></td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['rw_avatar_desc']?>                                          </td>
<td><input type=radio <?php echo VBSEO_REWRITE_AVATAR?'CHECKED ':''?> value=1
name=avatar_urls>
<?php echo $alang['yes']?>
<input type=radio value=0 <?php echo VBSEO_REWRITE_AVATAR?'':'CHECKED '?>
name=avatar_urls>
<?php echo $alang['no']?> </td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['avatar_format_desc']?>                                          </td>
<td><p>
<?php
$avatarformat = array(
'%user_name%.gif'=>'[user_name].gif',
'%user_id%.gif'=>'[user_id].gif',
);
$fnd=false;
foreach($avatarformat as $k=>$v){
$tf = (VBSEO_URL_AVATAR==$k);
if($tf)$fnd = true;
echo '<input type=radio value="'.$v.'" name=avatarformat '.($tf?'CHECKED ':'').'>
'.$v.'<br />';
}
?>
<input type=radio value="custom" name=avatarformat <?php echo $fnd?'':' checked'?> id="avatarformat_id">
<?php echo $alang['custom']?>:<br />
<input name=cust_avatarformat value="<?php echo $fnd?'':uformat_prep(VBSEO_URL_AVATAR)?>" size=34 onkeyup="if(this.value)document.getElementById('avatarformat_id').checked=true">
</p></td>
</tr>
<tr class=subheader>
<td colspan=2><a name="url_nav_bullet" id="url_nav_bullet"></a><?php echo $alang['urls_navbul']?></td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['rw_navbul_desc']?>                                          </td>
<td><input type=radio <?php echo VBSEO_REWRITE_TREE_ICON?'CHECKED ':''?> value=1
name=treeicon_urls>
<?php echo $alang['yes']?>
<input type=radio value=0 <?php echo VBSEO_REWRITE_TREE_ICON?'':'CHECKED '?>
name=treeicon_urls>
<?php echo $alang['no']?> </td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['navbul_format_desc']?>                                          </td>
<td><?php
$fbulletformat = array(
'%forum_path%.gif'=>'[forum_path].gif',
'%forum_title%.gif'=>'[forum_title].gif',
'forum%forum_id%.gif'=>'forum[forum_id].gif',
'%forum_title%.gif'=>'[forum_title].gif',
);
$fnd=false;
foreach($fbulletformat as $k=>$v){
$tf = (VBSEO_URL_FORUM_TREE_ICON==$k);
if($tf)$fnd = true;
echo '<input type=radio value="'.$v.'" name=fbulletformat '.($tf?'CHECKED ':'').'>
'.$v.'<br />';
}
?>
<input type=radio value="custom" name=fbulletformat <?php echo $fnd?'':' checked'?> id="fbulletformat_id">
<?php echo $alang['custom']?>:<br />
<input size=34 value="<?php echo $fnd?'':uformat_prep(VBSEO_URL_FORUM_TREE_ICON)?>" name=cust_fbulletformat onkeyup="if(this.value)document.getElementById('fbulletformat_id').checked=true">                                          </td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['navbul_tformat_desc']?>                                          </td>
<td><?php
$tbulletformat = array(
'%forum_path%/%thread_title%.gif'=>'[forum_path]/[thread_title].gif',
'%forum_title%/%thread_title%.gif'=>'[forum_title]/[thread_title].gif',
'forum%forum_id%/thread%thread_id%.gif'=>'forum[forum_id]/thread[thread_id].gif',
'%thread_title%.gif'=>'[thread_title].gif',
);
$fnd=false;
foreach($tbulletformat as $k=>$v){
$tf = (VBSEO_URL_THREAD_TREE_ICON==$k);
if($tf)$fnd = true;
echo '<input type=radio value="'.$v.'" name=tbulletformat '.($tf?'CHECKED ':'').'>
'.$v.'<br />';
}
?>
<input type=radio value="custom" name=tbulletformat <?php echo $fnd?'':' checked'?> id="tbulletformat_id">
<?php echo $alang['custom']?>:<br />
<input size=34 value="<?php echo $fnd?'':uformat_prep(VBSEO_URL_THREAD_TREE_ICON)?>" name=cust_tbulletformat onkeyup="if(this.value)document.getElementById('tbulletformat_id').checked=true">                                          </td>
</tr>
<tr class=subheader>
<td colspan=2><a name="url_attachment" id="url_attachment"></a><?php echo $alang['urls_attach']?></td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['rw_attach_desc']?>                                          </td>
<td><input type=radio <?php echo VBSEO_REWRITE_ATTACHMENTS?'CHECKED ':''?> value=1
name=attachment_urls>
<?php echo $alang['yes']?>
<input type=radio value=0 <?php echo VBSEO_REWRITE_ATTACHMENTS?'':'CHECKED '?>
name=attachment_urls>
<?php echo $alang['no']?> </td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['attach_format_desc']?>                                          </td>
<td><p>
<?php
$attachformat = array(
'%forum_path%/%attachment_id%-%thread_title%-%original_filename%'=>'[forum_path]/[attachment_id]-[thread_title]-[original_filename]',
'%forum_title%/%attachment_id%-%thread_title%-%original_filename%'=>'[forum_title]/[attachment_id]-[thread_title]-[original_filename]',
'forum%forum_id%/%attachment_id%-%original_filename%'=>'forum[forum_id]/[attachment_id]-[original_filename]',
'%attachment_id%-%thread_title%-%original_filename%'=>'[attachment_id]-[thread_title]-[original_filename]',
);
$fnd=false;
foreach($attachformat as $k=>$v){
$tf = (VBSEO_URL_ATTACHMENT==$k);
if($tf)$fnd = true;
echo '<input type=radio value="'.$v.'" name=attachformat '.($tf?'CHECKED ':'').'>
'.$v.'<br />';
}
?>
<input type=radio value="custom" name=attachformat <?php echo $fnd?'':' checked'?> id="attachformat_id">
<?php echo $alang['custom']?>:<br />
<input name=cust_attachformat value="<?php echo $fnd?'':uformat_prep(VBSEO_URL_ATTACHMENT)?>" size=34 onKeyUp="if(this.value)document.getElementById('attachformat_id').checked=true">
</p></td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['rw_attach_alt_desc']?>                                          </td>
<td><input type=radio <?php echo VBSEO_REWRITE_ATTACHMENTS_ALT?'CHECKED ':''?> value=1
name=attachment_alts>
<?php echo $alang['yes']?>
<input type=radio value=0 <?php echo VBSEO_REWRITE_ATTACHMENTS_ALT?'':'CHECKED '?>
name=attachment_alts>
<?php echo $alang['no']?> </td>
</tr>
<tr class=altfirst>
<td valign=top>
<?php echo $alang['attach_alt_format_desc']?>                                          </td>
<td><p>
<?php
$attachformat = array(
'%thread_title%-%original_filename%' => '[thread_title]-[original_filename]',
'%original_filename%' => '[original_filename]',
);
$fnd=false;
foreach($attachformat as $k=>$v){
$tf = (VBSEO_URL_ATTACHMENT_ALT==$k);
if($tf)$fnd = true;
echo '<input type=radio value="'.$v.'" name=attachaltformat '.($tf?'CHECKED ':'').'>
'.$v.'<br />';
}
?>
<input type=radio value="custom" name=attachaltformat <?php echo $fnd?'':' checked'?> id="attachaltformat_id">
<?php echo $alang['custom']?>:<br />
<input name=cust_attachaltformat value="<?php echo $fnd?'':uformat_prep(VBSEO_URL_ATTACHMENT_ALT)?>" size=34 onKeyUp="if(this.value)document.getElementById('attachaltformat_id').checked=true">
</p></td>
</tr>
<tr class=subheader>
<td colspan=2><a name="url_groups" id="url_groups"></a><?php echo $alang['urls_groups']?></td>
</tr>
<?php
$trclass = 1;
vbseo_display_option_yesno($alang['rw_groups_desc'], 'groups_urls', VBSEO_REWRITE_GROUPS);
vbseo_display_option_formats($alang['groupshome_format_desc'], 'groupshomeformat', 
array(
'groups/',
), 
VBSEO_URL_GROUPS_HOME);
vbseo_display_option_formats($alang['groupsall_format_desc'], 'groupsallformat', 
array(
'groups/all/',
'groups/all.html',
), 
VBSEO_URL_GROUPS_ALL);
vbseo_display_option_formats($alang['groupsallpage_format_desc'], 'groupsallpageformat', 
array(
'groups/all/index[page].html',
'groups/all-[page].html',
), 
VBSEO_URL_GROUPS_ALL_PAGE);
vbseo_display_option_formats($alang['groups_format_desc'], 'groupsformat', 
array(
'groups/[group_name]/',
'groups/[group_name].html',
'groups/[group_id]/',
'groups/[group_id]-[group_name].html',
), 
VBSEO_URL_GROUPS);
vbseo_display_option_formats($alang['groupspage_format_desc'], 'groupspageformat', 
array(
'groups/[group_name]/index[page].html',
'groups/[group_name]-page[page].html',
'groups/[group_id]-page[page].html',
'groups/[group_id]-[group_name]-page[page].html',
), 
VBSEO_URL_GROUPS_PAGE);
vbseo_display_option_formats($alang['group_discussion_desc'], 'groupdiscussion',
array(
'groups/[group_name]/[discussion_id]-[discussion_name]/',
'groups/[group_name]-d[discussion_id]-[discussion_name].html',
'groups/[group_id]-[discussion_id].html',
'groups/[group_id]-[group_name]-d[discussion_id]-[discussion_name].html',
),VBSEO_URL_GROUPS_DISCUSSION);
vbseo_display_option_formats($alang['group_discussion_page_desc'], 'groupdiscussionpage',
array(
'groups/[group_name]/[discussion_id]-[discussion_name]/index[page].html',
'groups/[group_name]-d[discussion_id]-[discussion_name]-page[page].html',
'groups/[group_id]-[discussion_id]-page[page].html',
'groups/[group_id]-[group_name]-d[discussion_id]-[discussion_name]-page[page].html',
),VBSEO_URL_GROUPS_DISCUSSION_PAGE);
vbseo_display_option_formats($alang['group_discussion_last_post_desc'], 'groupdiscussionlastpost',
array(
'groups/[group_name]/[discussion_id]-[discussion_name]-last-post/',
'groups/[group_name]-d[discussion_id]-[discussion_name]-last-post.html',
'groups/[group_id]-[discussion_id]-last-post.html',
'groups/[group_id]-[group_name]-d[discussion_id]-[discussion_name]-last-post.html',
),VBSEO_URL_GROUPS_DISCUSSION_LAST_POST);
vbseo_display_option_formats($alang['groupmembers_format_desc'], 'groupmembersformat', 
array(
'groups/[group_name]/members/',
'groups/[group_name]-members.html',
'groups/[group_id]-members.html',
'groups/[group_id]-[group_name]-members.html',
), 
VBSEO_URL_GROUPS_MEMBERS);
vbseo_display_option_formats($alang['groupmemberspage_format_desc'], 'groupmemberspageformat', 
array(
'groups/[group_name]/members/index[page].html',
'groups/[group_name]-members-page[page].html',
'groups/[group_id]/members-page[page].html',
'groups/[group_id]-[group_name]-members-page[page].html'
), 
VBSEO_URL_GROUPS_MEMBERS_PAGE);
vbseo_display_option_formats($alang['grouppic_format_desc'], 'grouppicformat', 
array(
'groups/[group_name]/pictures/',
'groups/[group_name]-pictures.html',
'groups/[group_id]-pictures.html',
'groups/[group_id]-[group_name]-pictures.html'
), 
VBSEO_URL_GROUPS_PIC);
vbseo_display_option_formats($alang['grouppicpage_format_desc'], 'grouppicpageformat', 
array(
'groups/[group_name]/pictures/index[page].html',
'groups/[group_name]-pictures-page[page].html',
'groups/[group_id]-pictures-page[page].html',
'groups/[group_id]-[group_name]-pictures-page[page].html'
), 
VBSEO_URL_GROUPS_PIC_PAGE);
vbseo_display_option_formats($alang['grouppicture_format_desc'], 'grouppictureformat',
array(
'groups/[group_name]/pictures/[picture_id]-[picture_title]/',
'groups/[group_name]-picture[picture_id]-[picture_title].html',
'groups/[group_id]-picture[picture_id].html',
'groups/[group_id]-[group_name]-picture[picture_id]-[picture_title].html'
), 
VBSEO_URL_GROUPS_PICTURE
);
vbseo_display_option_formats($alang['grouppicturepage_format_desc'], 'grouppicturepageformat',
array(
'groups/[group_name]/pictures/[picture_id]-[picture_title]/index[page].html',
'groups/[group_name]-picture[picture_id]-[picture_title]-page[page].html',
'groups/[group_id]-picture[picture_id]-page[page].html',
'groups/[group_id]-[group_name]-picture[picture_id]-[picture_title]-page[page].html'
), 
VBSEO_URL_GROUPS_PICTURE_PAGE
);
vbseo_display_option_formats($alang['grouppictureimg_format_desc'], 'grouppictureimgformat',
array(
'groups/[group_name]/pictures/[picture_id]-[picture_title].[original_ext]',
'groups/[group_name]-picture[picture_id]-[picture_title].[original_ext]',
'groups/[group_id]-picture[picture_id].[original_ext]',
'groups/[group_id]-[group_name]-picture[picture_id]-[picture_title].[original_ext]'
), 
VBSEO_URL_GROUPS_PICTURE_IMG
);
vbseo_display_option_formats($alang['group_category_list_desc'], 'groupcategorylist',
array(
'groups/categories/',
'groups/categories.html',
),VBSEO_URL_GROUPS_CATEGORY_LIST
);
vbseo_display_option_formats($alang['group_category_list_page_desc'], 'groupcategorylistpage',
array(
'groups/categories/index[page].html',
'groups/categories-page[page].html',
),VBSEO_URL_GROUPS_CATEGORY_LIST_PAGE
);
vbseo_display_option_formats($alang['group_category_desc'], 'groupcategory',
array(
'groups/categories/[cat_name]/',
'groups/category-[cat_name].html',
'groups/category[cat_id].html',
'groups/category[cat_id]-[cat_name].html',
),VBSEO_URL_GROUPS_CATEGORY
);
vbseo_display_option_formats($alang['group_category_page_desc'], 'groupcategorypage',
array(
'groups/categories/[cat_name]/index[page].html',
'groups/category-[cat_name]-page[page].html',
'groups/category[cat_id]-page[page].html',
'groups/category[cat_id]-[cat_name]-page[page].html',
),VBSEO_URL_GROUPS_CATEGORY_PAGE
);
?>
</td>
</tr>
<tr class=subheader>
<td colspan=2><a name="url_tags" id="url_tags"></a><?php echo $alang['urls_tags']?></td>
</tr>
<?php
$trclass = 1;
vbseo_display_option_yesno($alang['rw_tags_desc'], 'tags_urls', VBSEO_REWRITE_TAGS);
vbseo_display_option_formats($alang['tagshome_format_desc'], 'tagshomeformat', 
array(
'tags/',
), 
VBSEO_URL_TAGS_HOME);
vbseo_display_option_formats($alang['tags_format_desc'], 'tagsformat', 
array(
'tags/[tag]/',
'tags/[tag].html',
), 
VBSEO_URL_TAGS_ENTRY);
vbseo_display_option_formats($alang['tagspage_format_desc'], 'tagspageformat', 
array(
'tags/[tag]/index[page].html',
'tags/[tag]-page[page].html',
), 
VBSEO_URL_TAGS_ENTRYPAGE);
?>
</td>
</tr>
<tr align="right" class=header>
<td colspan=2><input type=submit value="<?php echo $alang['save_set']?>" name=edit onClick="this.form['jumpto'].value='url'">
&nbsp;
<input type="reset" name="Reset" value="<?php echo $alang['reset']?>"></td></tr>
</tbody>
</table>
</td>
</tr>
<?php
vbseo_display_option_header('url_blog', 'url_blog');
?>
<tr class=altfirst>
<td colspan="2" >
<?php echo $alang['blog_opt_description']?>
 
<div style="padding:10px; border:solid 1px black; background-color:#f2f2f2">
<?php echo $alang['current_preset']?>: <?php echo $current_preset_blog ? '<b style="color:green">'.$vbseocp_presets[$current_preset_blog].'</b>' : '<b style="color:red">'.$alang['custom_settings'].'</b>' ?>
<br /><br />
<select name="presetblog" id="selpreset">
<option value="" selected></option>
<?php
$pres_i = 0;
foreach($vbseocp_presets_b as $pset=>$ptitle)
{
$pres_i=intval($pset);
echo '
<option value="'.$pset.'">'.($alang['preset_'.$pres_i]?$alang['preset_'.$pres_i]:$ptitle).'</option>
';
}
?>
</select>
<input id="button" type="button" name="Submit" value="<?php echo $alang['load_preset']?>"
onclick="js_load_preset('blog')">
<br /><br />
<b><?php echo $alang['adv_settings']?></b> (<a href="#" onclick="js_hideshow('urlsettings_blog',true);return false;">+</a> / <a href="#" onclick="js_hideshow('urlsettings_blog',false);return false;">-</a>)
</div>
<table id="urlsettings_blog" class=formtbl cellSpacing=1 cellPadding=4 style="display:<?php    
if(!$_GET['vbseodbg']){?>none<?php }     
?>; border-top:none" width="100%" border=0>
<tbody>
<?php
vbseo_display_option_header('urls_blog', 'urls_blog');
vbseo_display_option_yesno($alang['rw_blog_desc'], 'blog_urls', VBSEO_REWRITE_BLOGS);
vbseo_display_option_formats($alang['bloghome_format_desc'], 'bloghomeformat', 
array(
'members/blogs.html',
'blogs/',
), 
VBSEO_URL_BLOG_HOME);
vbseo_display_option_formats($alang['bloguser_format_desc'], 'bloguserformat', 
array(
'members/[user_name]/blog.html',
'blogs/[user_name]/',
'blogs/[user_id]/',
'blogs/[user_id]-[user_name].html',
), 
VBSEO_URL_BLOG_USER);
vbseo_display_option_formats($alang['bloguserpage_format_desc'], 'bloguserpageformat', 
array(
'members/[user_name]/blog-page[page].html',
'blogs/[user_name]/index[page].html',
'blogs/[user_id]/index[page].html',
'blogs/[user_id]-[user_name]-page[page].html',
),
VBSEO_URL_BLOG_USER_PAGE);
vbseo_display_option_header('urls_blog_entry');
vbseo_display_option_yesno($alang['rw_blogent_desc'], 'blog_urls_ent', VBSEO_REWRITE_BLOGS_ENT);
vbseo_display_option_formats($alang['blogind_format_desc'], 'blogindformat', 
array(
'members/[user_name]/[blog_id]-[blog_title].html',
'blogs/[user_name]/[blog_id]-[blog_title].html',
'blogs/[user_id]/blog[blog_id].html',
'blogs/blog[blog_id]-[blog_title].html',
),
VBSEO_URL_BLOG_ENTRY);
vbseo_display_option_formats($alang['blogindpage_format_desc'], 'blogindpageformat', 
array(
'members/[user_name]/[blog_id]-[blog_title]-page[page].html',
'blogs/[user_name]/[blog_id]-[blog_title]-page[page].html',
'blogs/[user_id]/blog[blog_id]-page[page].html',
'blogs/blog[blog_id]-[blog_title]-page[page].html',
),
VBSEO_URL_BLOG_ENTRY_PAGE);
vbseo_display_option_formats($alang['blogindredir_format_desc'], 'blogindredirformat', 
array(
'members/comments/comment[comment_id].html',
'blogs/comments/comment[comment_id].html',
'blogs/comment[comment_id].html',
), VBSEO_URL_BLOG_ENTRY_REDIR
);
vbseo_display_option_formats($alang['blognext_format_desc'], 'blognextformat', 
array(
'members/[user_name]/[blog_id]-[blog_title]-next.html',
'blogs/[user_name]/[blog_id]-[blog_title]-next.html',
'blogs/[user_id]/blog[blog_id]-next.html',
'blogs/blog[blog_id]-[blog_title]-next.html',
),VBSEO_URL_BLOG_NEXT
);
vbseo_display_option_formats($alang['blogprev_format_desc'], 'blogprevformat', 
array(
'members/[user_name]/[blog_id]-[blog_title]-prev.html',
'blogs/[user_name]/[blog_id]-[blog_title]-prev.html',
'blogs/[user_id]/blog[blog_id]-prev.html',
'blogs/blog[blog_id]-[blog_title]-prev.html',
),VBSEO_URL_BLOG_PREV
);
vbseo_display_option_header('urls_blog_cst_page');
vbseo_display_option_yesno($alang['rw_blog_cst_page_desc'], 'blog_urls_cst_page', VBSEO_REWRITE_BLOGS_CUSTOM);
vbseo_display_option_formats($alang['blog_cst_page_desc'], 'blogcustompageformat',
array(
'members/[user_name]/custom[page_id]-[page_title].html',
'blogs/[user_name]/custom[page_id]-[page_title].html',
'blogs/[user_id]/custom[page_id].html',
'blogs/custom[page_id]-[page_title].html',
),VBSEO_URL_BLOG_CUSTOM
);
vbseo_display_option_header('urls_blog_cat');
vbseo_display_option_yesno($alang['rw_blogcat_desc'], 'blog_urls_cat', VBSEO_REWRITE_BLOGS_CAT);
vbseo_display_option_formats($alang['blogglobcat_format_desc'], 'blogglobcatformat', 
array(
'members/categories/[category_title]/',
'blogs/categories/[category_title]/',
'blogs/categories/category[category_id]/',
'blogs/category[category_id]-[category_title].html',
),VBSEO_URL_BLOG_GLOB_CAT
);
vbseo_display_option_formats($alang['blogglobcatpage_format_desc'], 'blogglobcatpageformat', 
array(
'members/categories/[category_title]/index[page].html',
'blogs/categories/[category_title]/index[page].html',
'blogs/categories/category[category_id]/index[page].html',
'blogs/category[category_id]-[category_title]-page[page].html',
),VBSEO_URL_BLOG_GLOB_CAT_PAGE
);
vbseo_display_option_formats($alang['blogcat_format_desc'], 'blogcatformat',
array(
'members/[user_name]/[category_title]/',
'blogs/[user_name]/[category_title]/',
'blogs/[user_id]/category[category_id]/',
'blogs/[user_id]-[user_name]-category[category_id]-[category_title].html'
),VBSEO_URL_BLOG_CAT
);
vbseo_display_option_formats($alang['blogcatpage_format_desc'], 'blogcatpageformat', 
array(
'members/[user_name]/[category_title]/index[page].html',
'blogs/[user_name]/[category_title]/index[page].html',
'blogs/[user_id]/category[category_id]/index[page].html',
'blogs/[user_id]-[user_name]-category[category_id]-[category_title]-page[page].html'
),VBSEO_URL_BLOG_CAT_PAGE
);
vbseo_display_option_header('urls_blog_att');
vbseo_display_option_yesno($alang['rw_blogatt_desc'], 'blog_urls_att', VBSEO_REWRITE_BLOGS_ATT);
vbseo_display_option_formats($alang['blogatt_format_desc'], 'blogattformat', 
array(
'members/[user_name]/attachments/[attachment_id]-[blog_title]-[original_filename]',
'blogs/[user_name]/attachments/[attachment_id]-[blog_title]-[original_filename]',
'blogs/[user_id]/attachments/[attachment_id]-[original_filename]',
'blogs/attachement[attachment_id]-[blog_title]-[original_filename]',
),VBSEO_URL_BLOG_ATT
);
vbseo_display_option_header('urls_blog_feed');
vbseo_display_option_yesno($alang['rw_blogfeed_desc'], 'blog_urls_feed', VBSEO_REWRITE_BLOGS_FEED);
vbseo_display_option_formats($alang['blogfeeduser_format_desc'], 'blogfeeduserformat', 
array(
'members/[user_name]/feed.rss',
'blogs/[user_name]/feed.rss',
'blogs/[user_id]/feed.rss',
'blogs/[user_id]-[user_name]-feed.rss',
),VBSEO_URL_BLOG_FEEDUSER
);
vbseo_display_option_formats($alang['blogfeed_format_desc'], 'blogfeedformat', 
array(
'members/feed.rss',
'blogs/feed.rss',
),VBSEO_URL_BLOG_FEED
);
vbseo_display_option_header('urls_blog_list');
vbseo_display_option_yesno($alang['rw_bloglist_desc'], 'blog_urls_list', VBSEO_REWRITE_BLOGS_LIST);
vbseo_display_option_formats($alang['bloguday_format_desc'], 'blogudayformat', 
array(
'members/[user_name]/[year]/[month]/[day]/',
'blogs/[user_name]/[year]/[month]/[day]/',
'blogs/[user_id]/[year]/[month]/[day]/',
'blogs/[user_id]-[user_name]-[year]-[month]-[day].html',
),VBSEO_URL_BLOG_UDAY
);
vbseo_display_option_formats($alang['blogday_format_desc'], 'blogdayformat', 
array(
'members/[year]/[month]/[day]/',
'blogs/[year]/[month]/[day]/',
'blogs/[year]-[month]-[day].html',
),VBSEO_URL_BLOG_DAY
);
vbseo_display_option_formats($alang['blogdaypage_format_desc'], 'blogdaypageformat', 
array(
'members/[year]/[month]/[day]/index[page].html',
'blogs/[year]/[month]/[day]/index[page].html',
'blogs/[year]-[month]-[day]-page[page].html',
),VBSEO_URL_BLOG_DAY_PAGE
);
vbseo_display_option_formats($alang['blogumonth_format_desc'], 'blogumonthformat', 
array(
'members/[user_name]/[year]/[month]/',
'blogs/[user_name]/[year]/[month]/',
'blogs/[user_id]/[year]/[month]/',
'blogs/[user_id]-[user_name]-[year]-[month].html',
),VBSEO_URL_BLOG_UMONTH
);
vbseo_display_option_formats($alang['blogmonth_format_desc'], 'blogmonthformat', 
array(
'members/[year]/[month]/',
'blogs/[year]/[month]/',
'blogs/[year]-[month].html',
),VBSEO_URL_BLOG_MONTH
);
vbseo_display_option_formats($alang['blogmonthpage_format_desc'], 'blogmonthpageformat', 
array(
'members/[year]/[month]/index[page].html',
'blogs/[year]/[month]/index[page].html',
'blogs/[year]-[month]-page[page].html',
),VBSEO_URL_BLOG_MONTH_PAGE
);
vbseo_display_option_formats($alang['blogblist_format_desc'], 'blogblistformat', 
array(
'members/blogs/',
'blogs/all/',
'blogs/all.html',
),VBSEO_URL_BLOG_BLIST
);
vbseo_display_option_formats($alang['blogblistpage_format_desc'], 'blogblistpageformat', 
array(
'members/blogs/index[page].html',
'blogs/all/index[page].html',
'blogs/all-[page].html',
),VBSEO_URL_BLOG_BLIST_PAGE
);
vbseo_display_option_formats($alang['bloglist_format_desc'], 'bloglistformat', 
array(
'members/recent-entries/',
'blogs/recent-entries/',
'blogs/recent-entries.html',
),VBSEO_URL_BLOG_LIST
);
vbseo_display_option_formats($alang['bloglistpage_format_desc'], 'bloglistpageformat', 
array(
'members/recent-entries/index[page].html',
'blogs/recent-entries/index[page].html',
'blogs/recent-entries-[page].html',
),VBSEO_URL_BLOG_LIST_PAGE
);
vbseo_display_option_formats($alang['bloglatestentries_format_desc'], 'bloglatest24hourformat', 
array(
'members/latest-entries/',
'blogs/latest-entries/',
'blogs/latest-entries.html',
),VBSEO_URL_BLOG_LAST_ENT
);
vbseo_display_option_formats($alang['bloglatestentriespage_format_desc'], 'bloglatest24hourpageformat', 
array(
'members/latest-entries/index[page].html',
'blogs/latest-entries/index[page].html',
'blogs/latest-entries-[page].html',
),VBSEO_URL_BLOG_LAST_ENT_PAGE
);
vbseo_display_option_formats($alang['blogbestentry_format_desc'], 'blogbestentformat', 
array(
'members/best-entries/',
'blogs/best-entries/',
'blogs/best-entries.html',
),VBSEO_URL_BLOG_BEST_ENT
);
vbseo_display_option_formats($alang['blogbestentpage_format_desc'], 'blogbestentpageformat', 
array(
'members/best-entries/index[page].html',
'blogs/best-entries/index[page].html',
'blogs/best-entries-[page].html',
),VBSEO_URL_BLOG_BEST_ENT_PAGE
);
vbseo_display_option_formats($alang['blogbestblogs_format_desc'], 'blogbestblogsformat', 
array(
'members/best-blogs/',
'blogs/best-blogs/',
'blogs/best-blogs.html',
),VBSEO_URL_BLOG_BEST_BLOGS
);
vbseo_display_option_formats($alang['blogbestblogspage_format_desc'], 'blogbestblogspageformat', 
array(
'members/best-blogs/index[page].html',
'blogs/best-blogs/index[page].html',
'blogs/best-blogs-[page].html',
),VBSEO_URL_BLOG_BEST_BLOGS_PAGE
);
vbseo_display_option_formats($alang['blogclist_format_desc'], 'blogclistformat', 
array(
'members/comments/',
'blogs/comments/',
'blogs/comments.html',
),VBSEO_URL_BLOG_CLIST
);
vbseo_display_option_formats($alang['blogclistpage_format_desc'], 'blogclistpageformat', 
array(
'members/comments/index[page].html',
'blogs/comments/index[page].html',
'blogs/comments-[page].html',
),VBSEO_URL_BLOG_CLIST_PAGE
);
vbseo_display_option_header('urls_blog_tags');
vbseo_display_option_yesno($alang['rw_blog_tags_desc'], 'blog_urls_tags', VBSEO_REWRITE_BLOGS_TAGS_ENTRY);
vbseo_display_option_formats($alang['blog_tags_home_desc'], 'blogtagshome',
array(
'members/tags/',
'blogs/tags/',
),VBSEO_URL_BLOG_TAGS_HOME
);
vbseo_display_option_formats($alang['blog_tags_desc'], 'blogtagsformat',
array(
'members/tags/[tag]/',
'blogs/tags/[tag]/',
'blogs/tags/[tag].html',
),VBSEO_URL_BLOG_TAGS_ENTRY
);
vbseo_display_option_formats($alang['blog_tags_desc_page'], 'blogtagspageformat',
array(
'members/tags/[tag]/index[page].html',
'blogs/tags/[tag]/index[page].html',
'blogs/tags/[tag]-page[page].html',
),VBSEO_URL_BLOG_TAGS_ENTRY_PAGE
);
?>
 
<tr align="right" class=header>
<td colspan=2><input type=submit value="<?php echo $alang['save_set']?>" name=edit onClick="this.form['jumpto'].value='url'">
&nbsp;
<input type="reset" name="Reset" value="<?php echo $alang['reset']?>"></td></tr>
</tbody>
</table>
 
</td>
</tr>
</tbody>
</table>
<table cellSpacing=0 cellPadding=0
width="100%" border=0>
<tbody>
<tr>
<td height="30">&nbsp;</td>
</tr>
</tbody>
</table>
 
<table class=formtbl cellSpacing=1 cellPadding=4 width="100%" border=0>
<tbody>
<tr class=header>
<td colspan="2"><div style="position:relative"><a name="linkbacks" id="linkbacks"></a><?php echo $alang['linkbacks']?></div></td>
</tr>
<tr class=subheader>
<td colspan=2><a name="in_linkbacks" id="in_linkbacks"></a><?php echo $alang['in_linkbacks']?></td>
</tr>
<tr class=altsecond>
<td><?php echo $alang['in_pingback_desc']?> </td>
<td><input name=inpingback type=radio value=1 <?php echo VBSEO_IN_PINGBACK?'CHECKED ':''?>>
<?php echo $alang['yes']?>
<input type=radio value=0  <?php echo VBSEO_IN_PINGBACK?'':'CHECKED '?>
name=inpingback>
<?php echo $alang['no']?> </td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['in_trackback_desc']?>                                        </td>
<td><input name=intrackback type=radio value=1 <?php echo VBSEO_IN_TRACKBACK?'CHECKED ':''?>>
<?php echo $alang['yes']?>
<input type=radio value=0  <?php echo VBSEO_IN_TRACKBACK?'':'CHECKED '?>
name=intrackback>
<?php echo $alang['no']?> </td>
</tr>
<tr class=altsecond>
<td width="50%">
<?php echo $alang['in_refback_desc']?>
</td>
<td width="50%"><input name=inrefback type=radio value=1 <?php echo VBSEO_IN_REFBACK?'CHECKED ':''?>>
<?php echo $alang['yes']?>
<input type=radio value=0  <?php echo VBSEO_IN_REFBACK?'':'CHECKED '?>
name=inrefback>
<?php echo $alang['no']?> </td>
</tr>
<tr class=altfirst>
<td width="50%">
<?php echo $alang['ignore_linkback_dupe_desc']?>
</td>
<td width="50%"><input name=linkignore type=radio value=1 <?php echo VBSEO_LINKBACK_IGNOREDUPE?'CHECKED ':''?>>
<?php echo $alang['yes']?>
<input type=radio value=0  <?php echo VBSEO_LINKBACK_IGNOREDUPE?'':'CHECKED '?>
name=linkignore>
<?php echo $alang['no']?> </td>
</tr>
<tr class=altsecond>
<td width="50%">
<?php echo $alang['pingback_notify_desc']?></td>
<td width="50%"><input name=pingback_notify type=radio value=1 <?php echo VBSEO_PINGBACK_NOTIFY?'CHECKED ':''?>>
<?php echo $alang['yes']?>
<input type=radio value=0  <?php echo VBSEO_PINGBACK_NOTIFY?'':'CHECKED '?>
name=pingback_notify>
<?php echo $alang['no']?>
<br /><br />
<?php echo $alang['pingback_notify_bcc_desc']?> <br />
<input type=text size=34 value="<?php echo VBSEO_PINGBACK_NOTIFY_BCC?>" name="pingback_notify_bcc">
<?php echo $alang['pingback_notify_bcc_desc2']?>
</td>
</tr>
<tr class=altsecond>
<td width="50%">
<?php echo $alang['pingback_hits_desc']?></td>
<td width="50%">
<?php echo $alang['pingback_hits_top']?><br />
<input type=text size=34 value="<?php echo VBSEO_LINKBACK_SHOWHITS_UG?>" name="pingback_showhits">
<?php echo $alang['pingback_hits_right']?>
</td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['pingback_stopwords_desc']?>
</td>
<td><textarea name="ping_stopwords" cols="45" rows="8" wrap="VIRTUAL" id="stopwords"><?php
$sw_a = explode('|',VBSEO_PINGBACK_STOPWORDS);
foreach($sw_a as $v){
echo "\n".($v)."";
}
?></textarea></td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['linkback_black_desc']?>
</td>
<td><textarea name="linkback_black" cols="45" rows="8" wrap="VIRTUAL"><?php
vbseo_extra_inc('linkback');
$sw_a = vbseo_linkback_getbandomains(1);
if(!$sw_a && defined('VBSEO_LINKBACK_BLACKLIST')) $sw_a = explode('|',VBSEO_LINKBACK_BLACKLIST);
foreach($sw_a as $v){
echo "\n".($v)."";
}
?></textarea></td>
</tr>
<tr class=subheader>
<td colspan=2><a name="out_linkbacks" id="out_linkbacks"></a><?php echo $alang['out_linkbacks']?></td>
</tr>
<tr class=altsecond>
<td width="50%">
<?php echo $alang['pingback_desc']?>                                        </td>
<td width="50%"><input name=pingback type=radio value=1 <?php echo VBSEO_EXT_PINGBACK?'CHECKED ':''?>>
<?php echo $alang['yes']?>
<input type=radio value=0  <?php echo VBSEO_EXT_PINGBACK?'':'CHECKED '?>
name=pingback>
<?php echo $alang['no']?> </td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['trackback_desc']?>                                       </td>
<td><input name=trackback type=radio value=1 <?php echo VBSEO_EXT_TRACKBACK?'CHECKED ':''?>>
<?php echo $alang['yes']?>
<input type=radio value=0  <?php echo VBSEO_EXT_TRACKBACK?'':'CHECKED '?>
name=trackback>
<?php echo $alang['no']?> </td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['pingback_service_desc'];?></td>
<td><textarea name="pingback_service" cols="45" rows="8" wrap="VIRTUAL" id="pingback_service"><?php
$sw_a = explode('|',VBSEO_PINGBACK_SERVICE);
foreach($sw_a as $v){
echo "
".($v)."";
}
?></textarea></td>
</tr>
<tr align="center" class=header>
<td colSpan=2 align="right"><input type=submit value="<?php echo $alang['save_set']?>" name=edit onclick="this.form['jumpto'].value='linkbacks'">
&nbsp;<input type="reset" name="Reset" value="<?php echo $alang['reset']?>">
</td>
</tr>
</tbody>
</table>
<br /><br />
<table class=formtbl cellSpacing=1 cellPadding=4 width="100%" border=0>
<tbody>
<tr class=header>
<td colspan="2"><div style="position:relative"><a name="permalinks" id="permalinks"></a><?php echo $alang['permalinks']?></div></td>
</tr>
<tr class=subheader>
<td colspan=2><a name="permalink_forum" id="permalink_forum"></a><?php echo $alang['permalink_forum']?></td>
</tr>
<tr class=altfirst>
<td width="50%">
<?php echo $alang['postbit_pingback_desc']?>                                        </td>
<td width="50%">
<select name="postbitpingback" id="postbitpingback">
<option value="1" <?php echo VBSEO_POSTBIT_PINGBACK==1?'selected':''?>><?php echo $alang['ping_postbit_1']?></option>
<option value="3" <?php echo VBSEO_POSTBIT_PINGBACK==3?'selected':''?>><?php echo $alang['ping_postbit_3']?></option>
<option value="2" <?php echo VBSEO_POSTBIT_PINGBACK==2?'selected':''?>><?php echo $alang['ping_postbit_2']?></option>
<option value="0" <?php echo VBSEO_POSTBIT_PINGBACK==0?'selected':''?>><?php echo $alang['ping_postbit_0']?></option>
</select></td>
</tr>
<tr class=subheader>
<td colspan=2><a name="permalink_profile" id="permalink_profile"></a><?php echo $alang['permalink_profile']?></td>
</tr>
<tr class=altfirst>
<td width="50%">
<?php echo $alang['permalink_profile_desc']?></td>
<td width="50%">
<select name="plink_profile">
<option value="1" <?php echo VBSEO_PERMALINK_PROFILE==1?'selected':''?>><?php echo $alang['pl_profile_1']?></option>
<option value="2" <?php echo VBSEO_PERMALINK_PROFILE==2?'selected':''?>><?php echo $alang['pl_profile_2']?></option>
<option value="0" <?php echo VBSEO_PERMALINK_PROFILE==0?'selected':''?>><?php echo $alang['pl_profile_0']?></option>
</select></td>
</tr>
<tr class=subheader>
<td colspan=2><a name="permalink_album" id="permalink_album"></a><?php echo $alang['permalink_album']?></td>
</tr>
<tr class=altfirst>
<td width="50%">
<?php echo $alang['permalink_pic_desc']?></td>
<td width="50%">
<select name="plink_album">
<option value="1" <?php echo VBSEO_PERMALINK_ALBUM==1?'selected':''?>><?php echo $alang['pl_profile_1']?></option>
<option value="2" <?php echo VBSEO_PERMALINK_ALBUM==2?'selected':''?>><?php echo $alang['pl_profile_2']?></option>
<option value="0" <?php echo VBSEO_PERMALINK_ALBUM==0?'selected':''?>><?php echo $alang['pl_profile_0']?></option>
</select></td>
</tr>
<tr class=subheader>
<td colspan=2><a name="permalink_blog" id="permalink_blog"></a><?php echo $alang['permalink_blog']?></td>
</tr>
<tr class=altfirst>
<td width="50%">
<?php echo $alang['permalink_blog_desc']?></td>
<td width="50%">
<select name="plink_blog">
<option value="1" <?php echo VBSEO_PERMALINK_BLOG==1?'selected':''?>><?php echo $alang['pl_blog_1']?></option>
<option value="0" <?php echo VBSEO_PERMALINK_BLOG==0?'selected':''?>><?php echo $alang['pl_blog_0']?></option>
</select></td>
</tr>
<tr class=subheader>
<td colspan=2><a name="permalink_groups" id="permalink_groups"></a><?php echo $alang['permalink_groups']?></td>
</tr>
<tr class=altfirst>
<td width="50%">
<?php echo $alang['permalink_groups_desc']?></td>
<td width="50%">
<select name="plink_groups">
<option value="1" <?php echo VBSEO_PERMALINK_GROUPS==1?'selected':''?>><?php echo $alang['pl_groups_1']?></option>
<option value="2" <?php echo VBSEO_PERMALINK_GROUPS==2?'selected':''?>><?php echo $alang['pl_groups_2']?></option>
<option value="0" <?php echo VBSEO_PERMALINK_GROUPS==0?'selected':''?>><?php echo $alang['pl_groups_0']?></option>
</select></td>
</tr>
<tr class=subheader>
<td colspan=2><a name="permalink_groupspic" id="permalink_groups"></a><?php echo $alang['permalink_groupspic']?></td>
</tr>
<tr class=altfirst>
<td width="50%">
<?php echo $alang['permalink_groupspic_desc']?></td>
<td width="50%">
<select name="plink_groupspic">
<option value="1" <?php echo VBSEO_PERMALINK_GROUPS_PIC==1?'selected':''?>><?php echo $alang['pl_groupspic_1']?></option>
<option value="2" <?php echo VBSEO_PERMALINK_GROUPS_PIC==2?'selected':''?>><?php echo $alang['pl_groupspic_2']?></option>
<option value="0" <?php echo VBSEO_PERMALINK_GROUPS_PIC==0?'selected':''?>><?php echo $alang['pl_groupspic_0']?></option>
</select></td>
</tr>
<tr align="center" class=header>
<td colSpan=2 align="right"><input type=submit value="<?php echo $alang['save_set']?>" name=edit onclick="this.form['jumpto'].value='permalinks'">
&nbsp;<input type="reset" name="Reset" value="<?php echo $alang['reset']?>">
</td>
</tr>
</tbody>
</table>
<table cellSpacing=0 cellPadding=0
width="100%" border=0>
<tbody>
<tr>
<td height="30">&nbsp;</td>
</tr>
</tbody>
</table>
<table class=formtbl cellSpacing=1 cellPadding=4
width="100%" border=0>
<tbody>
<tr class=header>
<td colspan="2">
<div id="forumlink"><a href="http://www.vbseo.com/f23/" target="_blank"><?php echo $alang['crrs_forum']?></a></div>
<div ><a name="custom" id="custom"></a><?php echo $alang['crrs']?></div></td>
</tr>
<tr class=altsecond>
<td colspan="2">
<?php
if(!defined('VBSEO_LICENSE_CRR'))
echo $alang['avail_pro_only'];
else
{
echo $alang['crrs_def'];?>
<br />
<?php
$vbseo_cr_match = true;
foreach($vbseo_custom_rules as $k=>$v)
if(!strstr($vbseo_custom_rules_text,$v))
$vbseo_cr_match = false;
?>
<textarea style="width:99%" name="customrules" rows="20" wrap="VIRTUAL" id="customrules"><?php echo (($vbseo_custom_rules_text||!$vbseo_custom_rules)&&$vbseo_cr_match)?$vbseo_custom_rules_text:stripslashes(preg_replace('#,[\r\n]+\s*#',"\n",preg_replace('#array\s*\(\s*(.*)\s*\)#is', '\\1', var_export($vbseo_custom_rules,1))))?></textarea>
<br />
<?php echo $alang['crrs_desc'];
}
?>
</td>
</tr>
<tr align="center" class=header>
<td colSpan=2 align="right"><input type=submit value="<?php echo $alang['save_set']?>" name=edit onclick="this.form['jumpto'].value='custom'">
&nbsp;<input type="reset" name="Reset" value="<?php echo $alang['reset']?>"></td>
</tr>
</tbody>
</table>
<table cellSpacing=0 cellPadding=0
width="100%" border=0>
<tbody>
<tr>
<td height="30">&nbsp;</td>
</tr>
</tbody>
</table>
<table class=formtbl cellSpacing=1 cellPadding=4
width="100%" border=0>
<tbody>
<tr class=header>
<td colspan="2"><div style="position:relative"><a name="custom301" id="custom301"></a><?php echo $alang['custom301']?></div></td>
</tr>
<tr class=altfirst>
<td colspan="2">
<?php
echo $alang['custom301_def'];?>
<br />
<textarea style="width:99%" name="custom301" rows="10" wrap="VIRTUAL" id="custom301"><?php echo $vbseo_custom_301_text; ?></textarea>
<br />
<?php echo $alang['custom301_desc'];
?>
</td>
</tr>
<tr align="center" class=header>
<td colSpan=2 align="right"><input type=submit value="<?php echo $alang['save_set']?>" name=edit onclick="this.form['jumpto'].value='custom301'">
&nbsp;<input type="reset" name="Reset" value="<?php echo $alang['reset']?>"></td>
</tr>
</tbody>
</table>
<table cellSpacing=0 cellPadding=0
width="100%" border=0>
<tbody>
<tr>
<td height="30">&nbsp;</td>
</tr>
</tbody>
</table>
<table class=formtbl cellSpacing=1 cellPadding=4
width="100%" border=0>
<tbody>
<tr class=header>
<td>
<div id="forumlink"><a href="http://www.vbseo.com/f60/" target="_blank" ><?php echo $alang['rrs_forum']?></a></div>
<div style="position:relative"><a name="seo_relevant" id="seo_relevant"></a><?php echo $alang['rel_replacements']?></div></td>
</tr>
<tr class=altsecond>
<td><?php echo $alang['rrs_def']?> <br />
<table width="100%"  border="0" cellpadding="10" cellspacing="1" bgcolor="#000000">
<tr>
<td class="altfirst"><p><em><strong>&lt;!--VBSEO_RR_1--&gt;</strong></em> <?php echo $alang['will_replace']?>: <br />
<input style="width:590px" name="relev_repl[0]" value="<?php echo htmlspecialchars($vbseo_relev_replace[0])?>">
<?php echo $alang['rr_forumdisplay']?><br />
<input style="width:590px" name="relev_repl_t[0]" size=45 value="<?php echo htmlspecialchars($vbseo_relev_replace_t[0])?>">
<?php echo $alang['rr_showthread']?> 
</p>
<p><em><strong>&lt;!--VBSEO_RR_2--&gt;</strong></em> <?php echo $alang['will_replace']?>: <br />
<input style="width:590px" name="relev_repl[1]" size=45 value="<?php echo htmlspecialchars($vbseo_relev_replace[1])?>">
<?php echo $alang['rr_forumdisplay']?><br />
<input style="width:590px" name="relev_repl_t[1]" size=45 value="<?php echo htmlspecialchars($vbseo_relev_replace_t[1])?>">
<?php echo $alang['rr_showthread']?>
</p>
<p> <em><strong>&lt;!--VBSEO_RR_3--&gt;</strong></em> <?php echo $alang['will_replace']?>: <br />
<input style="width:590px" name="relev_repl[2]" size=45 value="<?php echo htmlspecialchars($vbseo_relev_replace[2])?>">
<?php echo $alang['rr_forumdisplay']?><br />
<input style="width:590px" name="relev_repl_t[2]" size=45 value="<?php echo htmlspecialchars($vbseo_relev_replace_t[2])?>">
<?php echo $alang['rr_showthread']?> 
</p></td>
</tr>
</table>
<?php echo $alang['rrs_desc']?></td>
</tr>
<tr align="center" class=header>
<td align="right"><input type=submit value="<?php echo $alang['save_set']?>" name=edit onclick="this.form['jumpto'].value='seo_relevant'">
&nbsp;<input type="reset" name="Reset" value="<?php echo $alang['reset']?>"></td>
</tr>
</tbody>
</table>
<table cellSpacing=0 cellPadding=0
width="100%" border=0>
<tbody>
<tr>
<td height="30">&nbsp;</td>
</tr>
</tbody>
</table>
<table class=formtbl cellSpacing=1 cellPadding=4
width="100%" border=0>
<tbody>
<tr class=header>
<td colspan=2><a name="seo" id="seo"></a><?php echo $alang['seo_func']?></td>
</tr>
<tr class=subheader>
<td colspan=2><a name="seo_meta" id="seo_meta"></a><?php echo $alang['dyn_metas']?></td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['metakw_desc']?>
</td>
<td><p>
<input type=radio <?php echo VBSEO_REWRITE_META_KEYWORDS?'CHECKED ':''?> value=1
name=replace_meta_keywords>
<?php echo $alang['yes']?>
<input type=radio value=0  <?php echo VBSEO_REWRITE_META_KEYWORDS?'':'CHECKED '?>
name=replace_meta_keywords>
<?php echo $alang['no']?></p></td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['metadesc_desc']?>
</td>
<td><p>
<input type=radio <?php echo VBSEO_REWRITE_META_DESCRIPTION?'CHECKED ':''?> value=1
name=replace_meta_description>
<?php echo $alang['yes']?>
<input type=radio value=0  <?php echo VBSEO_REWRITE_META_DESCRIPTION?'':'CHECKED '?>
name=replace_meta_description>
<?php echo $alang['no']?></p></td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['metadesc_len_desc']?>
</td>
<td><input name="length_meta_description" type="text" id="length_meta_description" size="5" value="<?php echo VBSEO_META_DESCRIPTION_MAX_CHARS?>">
(<?php echo $alang['max_chars']?>)</td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['metadesc_members_desc']?>
</td>
<td><input name="member_meta_description" type="text" id="meta_description" size="70" value="<?php echo VBSEO_META_DESCRIPTION_MEMBER?>"></td>
</tr>
<tr class=subheader>
<td colspan=2><a name="seo_npdirect" id="seo_meta"></a><?php echo $alang['dir_links']?></td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['nextprev_desc']?>
</td>
<td><p>
<input type=radio <?php echo VBSEO_URL_THREAD_NEXT_DIRECT?'CHECKED ':''?> value=1
name=next_prev_thread_direct>
<?php echo $alang['yes']?>
<input type=radio value=0  <?php echo VBSEO_URL_THREAD_NEXT_DIRECT?'':'CHECKED '?>
name=next_prev_thread_direct>
<?php echo $alang['no']?></p></td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['flinks_desc']?>
</td>
<td><p>
<input type=radio <?php echo VBSEO_FORUMLINK_DIRECT?'CHECKED ':''?> value=1
name=forum_link_direct>
<?php echo $alang['yes']?>
<input type=radio value=0  <?php echo VBSEO_FORUMLINK_DIRECT?'':'CHECKED '?>
name=forum_link_direct>
<?php echo $alang['no']?></p></td>
</tr>
<tr class=subheader>
<td colspan=2><a name="seo_addtitles" id="seo_addtitle"></a><?php echo $alang['add_ttitle']?></td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['addttl_desc']?>
</td>
<td><select name="addtitles" id="addtitles">
<option value="0" <?php echo VBSEO_REWRITE_THREADS_ADDTITLE==0?'selected':''?>><?php echo $alang['attl_1']?></option>
<option value="1" <?php echo VBSEO_REWRITE_THREADS_ADDTITLE==1?'selected':''?>><?php echo $alang['attl_2']?></option>
<option value="2" <?php echo VBSEO_REWRITE_THREADS_ADDTITLE==2?'selected':''?>><?php echo $alang['attl_3']?></option>
<option value="3" <?php echo VBSEO_REWRITE_THREADS_ADDTITLE==3?'selected':''?>><?php echo $alang['attl_4']?></option>
</select></td>
</tr>
<tr class=altsecond>
<td width="50%">
<?php echo $alang['addttlp_desc']?>
</td>
<td><p>
<input type=radio <?php echo VBSEO_REWRITE_THREADS_ADDTITLE_POST?'CHECKED ':''?> value=1
name=addtitlespost>
<?php echo $alang['yes']?>
<input type=radio value=0  <?php echo VBSEO_REWRITE_THREADS_ADDTITLE_POST?'':'CHECKED '?>
name=addtitlespost>
<?php echo $alang['no']?>
</p>
</td>
</tr>
<tr class=subheader>
<td colspan=2><a name="seo_addexttitles" id="seo_addexttitle"></a><?php echo $alang['add_extttitle']?></td>
</tr>
<tr class=altfirst>
<td width="50%">
<?php echo $alang['addttlext_desc']?>                                          </td>
<td><p>
<input type=radio <?php echo VBSEO_REWRITE_EXT_ADDTITLE?'CHECKED ':''?> value=1
name=extaddtitles>
<?php echo $alang['yes']?>
<input type=radio value=0  <?php echo VBSEO_REWRITE_EXT_ADDTITLE?'':'CHECKED '?>
name=extaddtitles>
<?php echo $alang['no']?>
</p>
</td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['addttl_black_desc']?>
</td>
<td><textarea name="extaddtitles_black" cols="45" rows="8" wrap="VIRTUAL"><?php
$sw_a = explode('|',VBSEO_REWRITE_EXT_ADDTITLE_BLACKLIST);
foreach($sw_a as $v){
echo "\n".($v)."";
}
?></textarea></td>
</tr>
<tr class=subheader>
<td colspan=2><a name="seo_stopword" id="seo_stopword"></a><?php echo $alang['stopwords']?></td>
</tr>
<tr class=altfirst>
<td width="50%">
<?php echo $alang['remstop_desc']?>
</td>
<td><p>
<input type=radio <?php echo VBSEO_FILTER_STOPWORDS && !VBSEO_KEEP_STOPWORDS_SHORT?'CHECKED ':''?> value=1
name=remove_stopwords>
<?php echo $alang['yes']?>
<input type=radio value=0  <?php echo VBSEO_FILTER_STOPWORDS?'':'CHECKED '?>
name=remove_stopwords>
<?php echo $alang['no']?>
<input type=radio value=2  <?php echo VBSEO_KEEP_STOPWORDS_SHORT?'CHECKED ':''?>
name=remove_stopwords>
<?php echo $alang['smart']?></p></td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['stopdef_desc']?>
</td>
<td><textarea name="stopwords" cols="45" rows="8" wrap="VIRTUAL" id="stopwords"><?php
$sw_a = explode('|',VBSEO_STOPWORDS);
foreach($sw_a as $v){
echo "\n".($v)."";
}
?></textarea></td>
</tr>
<tr class="subheader">
<td colspan="2"><a name="seo_acronym" id="seo_acronym"></a><?php echo $alang['acronym']?></td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['acronym_desc']?>
</td>
<td>
<select name="acroset" id="acroset">
<option value="0" <?php echo VBSEO_ACRONYM_SET==0?'selected':''?>><?php echo $alang['acro_opt1']?></option>
<option value="1" <?php echo VBSEO_ACRONYM_SET==1?'selected':''?>><?php echo $alang['acro_opt2']?></option>
<option value="2" <?php echo VBSEO_ACRONYM_SET==2?'selected':''?>><?php echo $alang['acro_opt3']?></option>
<option value="3" <?php echo VBSEO_ACRONYM_SET==3?'selected':''?>><?php echo $alang['acro_opt4']?></option>
</select></td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['acronym_def_desc']?>
</td>
<td><textarea name="replacements" cols="45" rows="8" wrap="VIRTUAL" id="replacements"><?php
foreach($seo_replacements as $k=>$v){
echo "\n'".str_replace("'","\\'",htmlspecialchars($k))."' => '".str_replace("'","\\'",htmlspecialchars($v))."'";
}
?></textarea></td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['acro_content_desc']?>
</td>
<td><input name=apply_replacements_in_cont type=radio value=1 <?php echo VBSEO_ACRONYMS_IN_CONTENT?'CHECKED ':''?>>
<?php echo $alang['yes']?>
<input type=radio value=0  <?php echo VBSEO_ACRONYMS_IN_CONTENT?'':'CHECKED '?>
name=apply_replacements_in_cont>
<?php echo $alang['no']?> </td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['acro_url_desc']?>
</td>
<td><input name=apply_replacements_in_urls type=radio value=1 <?php echo VBSEO_REWRITE_KEYWORDS_IN_URLS?'CHECKED ':''?>>
<?php echo $alang['yes']?>
<input type=radio value=0  <?php echo VBSEO_REWRITE_KEYWORDS_IN_URLS?'':'CHECKED '?>
name=apply_replacements_in_urls>
<?php echo $alang['no']?> </td>
</tr>
<tr class=subheader>
<td colspan=2><a name="seo_homepage" id="seo_homepage"></a><?php echo $alang['hp_set']?></td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['forceroot_desc']?>
</td>
<td><input name=hp_forceindexroot type=radio value=1 <?php echo VBSEO_HP_FORCEINDEXROOT?'CHECKED ':''?>>
<?php echo $alang['yes']?>
<input type=radio value=0  <?php echo VBSEO_HP_FORCEINDEXROOT?'':'CHECKED '?>
name=hp_forceindexroot>
<?php echo $alang['no']?> </td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['hpalias_desc']?>
</td>
<td><textarea name="hpaliases" cols="45" rows="8" wrap="VIRTUAL" id="hpaliases"><?php
$sw_a = explode('|',VBSEO_HOMEPAGE_ALIASES);
foreach($sw_a as $v){
echo "\n".($v)."";
}
?></textarea></td>
<tr class=subheader>
<td colspan=2><a name="seo_intrelnofollow" id="seo_intrelnofollow"></a><?php echo $alang['int_rel']?></td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['intrel_desc']?>
</td>
<td>
<input name=nofollow_sort type=radio value=1 <?php echo VBSEO_NOFOLLOW_SORT?'CHECKED ':''?>>
<?php echo $alang['yes']?>
<input type=radio value=0  <?php echo VBSEO_NOFOLLOW_SORT?'':'CHECKED '?>
name=nofollow_sort>
<?php echo $alang['no']?> </td>
</tr>
<tr class=altfirst>
<td><?php echo $alang['reldyn_desc']?></td>
<td>
<input name=nofollow_dyna type=radio value=1 <?php echo VBSEO_NOFOLLOW_DYNA?'CHECKED ':''?>>
<?php echo $alang['yes']?>
<input type=radio value=0  <?php echo VBSEO_NOFOLLOW_DYNA?'':'CHECKED '?>
name=nofollow_dyna>
<?php echo $alang['no']?> </td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['relmemb_desc']?>
</td>
<td>
<input name=nofollow_member_postbit type=radio value=1 <?php echo VBSEO_NOFOLLOW_MEMBER_POSTBIT?'CHECKED ':''?>>
<?php echo $alang['yes']?>
<input type=radio value=0  <?php echo VBSEO_NOFOLLOW_MEMBER_POSTBIT?'':'CHECKED '?>
name=nofollow_member_postbit>
<?php echo $alang['no']?> </td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['relmemb_home_desc']?>
</td>
<td>
<input name=nofollow_member_forumhome type=radio value=1 <?php echo VBSEO_NOFOLLOW_MEMBER_FORUMHOME?'CHECKED ':''?>>
<?php echo $alang['yes']?>
<input type=radio value=0  <?php echo VBSEO_NOFOLLOW_MEMBER_FORUMHOME?'':'CHECKED '?>
name=nofollow_member_forumhome>
<?php echo $alang['no']?> </td>
</tr>
<tr class=subheader>
<td colspan=2><a name="seo_extrelnofollow" id="seo_extrelnofollow"></a><?php echo $alang['ext_rel']?></td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['extrel_desc']?>
</td>
<td><input name=nofollow_ext type=radio value=1 <?php echo VBSEO_NOFOLLOW_EXTERNAL?'CHECKED ':''?>>
<?php echo $alang['yes']?>
<input type=radio value=0  <?php echo VBSEO_NOFOLLOW_EXTERNAL?'':'CHECKED '?>
name=nofollow_ext>
<?php echo $alang['no']?> </td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['extwhite_desc']?>
</td>
<td><textarea name="domwhitelist" cols="45" rows="8" wrap="VIRTUAL" id="domwhitelist"><?php
$sw_a = explode('|',VBSEO_DOMAINS_WHITELIST);
foreach($sw_a as $v){
echo "\n".($v)."";
}
?></textarea></td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['extblack_desc']?>
</td>
<td><textarea name="domblacklist" cols="45" rows="8" wrap="VIRTUAL" id="domblacklist"><?php
$sw_a = explode('|',VBSEO_DOMAINS_BLACKLIST);
foreach($sw_a as $v){
echo "\n".($v)."";
}
?></textarea></td>
</tr>
<tr class=subheader>
<td colspan=2><a name="seo_canonical" id="seo_canonical"></a><?php echo $alang['canonical_link']?></td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['canonical_link_desc']?>
</td>
<td><input name=canonicallink type=radio value=1 <?php echo VBSEO_CANONIC_LINK_TAG?'CHECKED ':''?>>
<?php echo $alang['yes']?>
<input type=radio value=0  <?php echo VBSEO_CANONIC_LINK_TAG?'':'CHECKED '?>
name=canonicallink>
<?php echo $alang['no']?> </td>
</tr>
<tr class=subheader>
<td colspan=2><a name="seo_vhtml" id="seo_vhtml"></a><?php echo $alang['virt_html']?></td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['virt_html_desc']?>
</td>
<td><select name="virthtml" id="virthtml">
<option value="0" <?php echo (!VBSEO_VIRTUAL_HTML)?'selected':''?>><?php echo $alang['vhtml_opt0']?></option>
<option value="1" <?php echo (VBSEO_VIRTUAL_HTML && !VBSEO_VIRTUAL_HTML_GUESTS_ONLY)?'selected':''?>><?php echo $alang['vhtml_opt1']?></option>
<option value="2" <?php echo (VBSEO_VIRTUAL_HTML && VBSEO_VIRTUAL_HTML_GUESTS_ONLY)?'selected':''?>><?php echo $alang['vhtml_opt2']?></option>
</select></td>
</tr>
<tr align="right" class=header>
<td colspan=2><input type=submit value="<?php echo $alang['save_set']?>" name=edit onclick="this.form['jumpto'].value='seo'">
&nbsp;<input type="reset" name="Reset" value="<?php echo $alang['reset']?>"></td>
</tr>
</tbody>
</table>
<table cellSpacing=0 cellPadding=0
width="100%" border=0>
<tbody>
<tr>
<td height="30">&nbsp;</td>
</tr>
</tbody>
</table>
<table class=formtbl cellSpacing=1 cellPadding=4
width="100%" border=0>
<tbody>
<tr class=header>
<td colspan=2><a name="other" id="other"></a><?php echo $alang['oth_enh']?></td>
</tr>
<tr class=subheader>
<td colspan=2><a name="other_cleanup" id="other_cleanup"></a><?php echo $alang['cleanup_html']?></td>
</tr>
<tr class=altfirst>
<td><?php echo $alang['clean_html_desc']?> </td>
<td><input type=radio <?php echo VBSEO_CODE_CLEANUP?'CHECKED ':''?> value=1
name=codecleanup>
<?php echo $alang['yes']?>
<input type=radio <?php echo VBSEO_CODE_CLEANUP?'':'CHECKED '?> value=0
name=codecleanup>
<?php echo $alang['no']?> </td>
</tr>
<tr class=subheader>
<td colspan=2><a name="other_arcfooter" id="other_arcfooter"></a><?php echo $alang['footer_arc']?></td>
</tr>
<tr class=altsecond>
<td><?php echo $alang['rw_archive_foot_desc']?> </td>
<td><select name="arc_footer" id="archive">
<option value="0" <?php echo (VBSEO_ARCHIVE_LINKS_FOOTER==0)?'selected':''?>><?php echo $alang['arc_opt0']?></option>
<option value="1" <?php echo (VBSEO_ARCHIVE_LINKS_FOOTER==1)?'selected':''?>><?php echo $alang['arc_opt1']?></option>
<option value="2" <?php echo (VBSEO_ARCHIVE_LINKS_FOOTER==2)?'selected':''?>><?php echo $alang['arc_opt2']?></option>
<option value="3" <?php echo (VBSEO_ARCHIVE_LINKS_FOOTER==3)?'selected':''?>><?php echo $alang['arc_opt3']?></option>
<option value="4" <?php echo (VBSEO_ARCHIVE_LINKS_FOOTER==4)?'selected':''?>><?php echo $alang['arc_opt4']?></option>
</select></td>
</tr>
<tr class=subheader>
<td colspan=2><a name="other_catlinks" id="other_catlinks"></a><?php echo $alang['catlinks']?></td>
</tr>
<tr class=altfirst>
<td width="50%">
<?php echo $alang['catlinks_desc']?>                                          </td>
<td><input type=radio <?php echo VBSEO_CATEGORY_ANCHOR_LINKS?'CHECKED ':''?> value=1
name=catlinks>
<?php echo $alang['yes']?>
<input type=radio <?php echo VBSEO_CATEGORY_ANCHOR_LINKS?'':'CHECKED '?> value=0
name=catlinks>
<?php echo $alang['no']?> </td>
</tr>
<tr class=subheader>
<td colspan=2><a name="other_guests" id="other_guests"></a><?php echo $alang['guests_only']?></td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['forum_jump_desc']?>                                          </td>
<td><input type=radio <?php echo VBSEO_FORUMJUMP_OFF?'CHECKED ':''?> value=1
name=forumjump>
<?php echo $alang['yes']?>
<input type=radio <?php echo VBSEO_FORUMJUMP_OFF?'':'CHECKED '?> value=0
name=forumjump>
<?php echo $alang['no']?> </td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['rem_prev_desc']?>                                          </td>
<td><input type=radio <?php echo VBSEO_CODE_CLEANUP_PREVIEW?'CHECKED ':''?> value=1
name=cleanup_preview>
<?php echo $alang['yes']?>
<input type=radio <?php echo VBSEO_CODE_CLEANUP_PREVIEW?'':'CHECKED '?> value=0
name=cleanup_preview>
<?php echo $alang['no']?> </td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['dir_links_threads_desc']?>
</td>
<td><input type=radio <?php echo VBSEO_DIRECTLINKS_THREADS?'CHECKED ':''?> value=1
name=dirlinks_threads>
<?php echo $alang['yes']?>
<input type=radio <?php echo VBSEO_DIRECTLINKS_THREADS?'':'CHECKED '?> value=0
name=dirlinks_threads>
<?php echo $alang['no']?> </td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['rem_member_dropdown_desc']?>
</td>
<td><input type=radio <?php echo VBSEO_CODE_CLEANUP_MEMBER_DROPDOWN?'CHECKED ':''?> value=1
name=cleanup_memdropdown>
<?php echo $alang['yes']?>
<input type=radio <?php echo VBSEO_CODE_CLEANUP_MEMBER_DROPDOWN?'':'CHECKED '?> value=0
name=cleanup_memdropdown>
<?php echo $alang['no']?> </td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['cleanup_lastpost_desc']?>                                          </td>
<td>
<select name="cleanup_lastpost" id="cleanup_lastpost">
<option value="1" <?php echo VBSEO_CODE_CLEANUP_LASTPOST==1?'selected':''?>><?php echo $alang['cleanup_lastpost_links']?></option>
<option value="2" <?php echo VBSEO_CODE_CLEANUP_LASTPOST==2?'selected':''?>><?php echo $alang['cleanup_lastpost_column']?></option>
<option value="0" <?php echo VBSEO_CODE_CLEANUP_LASTPOST==0?'selected':''?>><?php echo $alang['cleanup_lastpost_none']?></option>
</select>
</td>
</tr>
<tr class="subheader">
<td colspan="2"><a name="other_bookmark" id="other_bookmark"></a><?php echo $alang['bookmark']?></td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['bookmark_desc']?>
</td>
<td>
<select name="bookmark_disp" id="bookmark_disp">
<option value="0" <?php echo !VBSEO_BOOKMARK_THREAD&&!VBSEO_BOOKMARK_POST?'selected':''?>><?php echo $alang['bookmark_none']?></option>
<option value="1" <?php echo (VBSEO_BOOKMARK_THREAD&&VBSEO_BOOKMARK_POST)?'selected':''?>><?php echo $alang['bookmark_threadpost']?></option>
<option value="2" <?php echo (VBSEO_BOOKMARK_THREAD&&!VBSEO_BOOKMARK_POST)?'selected':''?>><?php echo $alang['bookmark_thread']?></option>
</select>
<br /><br />
<input type=checkbox <?php echo VBSEO_BOOKMARK_DIGG?'CHECKED ':''?> value=1 name=bmark_digg> <a href="http://www.digg.com" target="_blank">digg.com</a>
<br />
<input type=checkbox <?php echo VBSEO_BOOKMARK_DELICIOUS?'CHECKED ':''?> value=1 name=bmark_delicious> <a href="http://del.icio.us" target="_blank">del.icio.us</a>
<br />
<input type=checkbox <?php echo VBSEO_BOOKMARK_TECHNORATI?'CHECKED ':''?> value=1 name=bmark_tech> <a href="http://www.technorati.com" target="_blank">technorati.com</a>
<br />
<input type=checkbox <?php echo VBSEO_BOOKMARK_FURL?'CHECKED ':''?> value=1 name=bmark_furl> <a href="http://www.furl.net" target="_blank">furl.net</a>
<br />
<input type=checkbox <?php echo VBSEO_BOOKMARK_CUSTOM?'CHECKED ':''?> value=1 id="cb_bmark_cust" name=bmark_custom onclick="js_hideshow('bookmarkcustom_div',this.checked)"><label for="cb_bmark_cust"> <?php echo $alang['bookmark_custom']?></label>
<br/>
<div id="bookmarkcustom_div" <?php echo VBSEO_BOOKMARK_CUSTOM?'':' style="display:none"'?>>
<?php echo $alang['bookmark_services_desc']?>
<textarea name="bmark_serv" cols="45" rows="8" wrap="VIRTUAL" id="bmark_serv"><?php
echo str_replace("|","\n\n",VBSEO_BOOKMARK_SERVICES);
?></textarea>
</div>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['blog_bookm_desc']?></td>
<td>
<select name="blogbookm" id="blogbookm">
<option value="0" <?php echo VBSEO_BOOKMARK_BLOG==0?'selected':''?>><?php echo $alang['blogbmark_none']?></option>
<option value="1" <?php echo VBSEO_BOOKMARK_BLOG==1?'selected':''?>><?php echo $alang['blogbmark_topright']?></option>
<option value="2" <?php echo VBSEO_BOOKMARK_BLOG==2?'selected':''?>><?php echo $alang['blogbmark_bottom']?></option>
</select>
</td>
</tr>
<tr class="subheader">
<td colspan="2"><a name="other_image" id="other_image"></a><?php echo $alang['img_size']?></td>
</tr>
<tr class=altfirst>
<td>
<?php echo $alang['img_size_desc']?>                                          </td>
<td><input name=imagesdim type=radio value=1 <?php echo VBSEO_IMAGES_DIM?'CHECKED ':''?>>
<?php echo $alang['yes']?>
<input type=radio value=0  <?php echo VBSEO_IMAGES_DIM?'':'CHECKED '?>
name=imagesdim>
<?php echo $alang['no']?> </td>
</tr>
<tr class=altsecond>
<td>
<?php echo $alang['img_def_desc']?>                                          </td>
<td><textarea name="images_dim" cols="45" rows="8" wrap="VIRTUAL" id="images_dim"><?php
foreach($vbseo_images_dim as $k=>$v){
echo "\n'".addslashes($k)."' => '".$v[0].'x'.$v[1]."'";
}
?></textarea>
<br />
<?php echo $alang['img_note']?>                                          </td>
</tr>
<tr align="right" class=header>
<td colspan=2><input type=submit value="<?php echo $alang['save_set']?>" name=edit onclick="this.form['jumpto'].value='other'">
&nbsp;<input type="reset" name="Reset" value="<?php echo $alang['reset']?>"></td>
</tr>
</tbody>
</table>
</td>
</tr>
</table></td>
 
</tr>
</table></td>
</tr>
<tr class=header align=middle>
<td><div align="center">
<input type="hidden" name="jumpto" value="">
<input type="submit" accesskey="s" value="<?php echo $alang['save_set_all']?>" name="edit">
</div></td>
</tr>
</form>
</table>
