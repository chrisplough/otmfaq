<?php 

 /******************************************************************************************
 * vBSEO Search Engine XML Sitemap for vBulletin v3.x and 4.x by Crawlability, Inc.    *
 *-----------------------------------------------------------------------------------------*
 *                                                                                         *
 * Copyright © 2010, Crawlability, Inc. All rights reserved.                               *
 * You may not redistribute this file or its derivatives without written permission.       *
 *                                                                                         *
 * Sales Email: sales@crawlability.com                                                     *
 *                                                                                         *
 *-------------------------------------LICENSE AGREEMENT-----------------------------------*
 * 1. You are free to download and install this plugin on any vBulletin forum for which    *
 *    you hold a valid vB license.                                                         *
 * 2. You ARE NOT allowed to REMOVE or MODIFY the copyright text within the .php files     *
 *    themselves.                                                                          *
 * 3. You ARE NOT allowed to DISTRIBUTE the contents of any of the included files.         *
 * 4. You ARE NOT allowed to COPY ANY PARTS of the code and/or use it for distribution.    *
 ******************************************************************************************/

define('CSRF_PROTECTION', true);
define('SKIP_SESSIONCREATE', 1);
define('NOCOOKIES', 1);
define('THIS_SCRIPT', 'login');

error_reporting(E_ALL & ~E_NOTICE & (defined('E_DEPRECATED') ? ~E_DEPRECATED : ~0));
if(!$_GET && !$_POST)
	$_GET['rlist'] = true;
require dirname(__FILE__).'/vbseo_sitemap_config.php';

chdir('../');
$globaltemplates = $actiontemplates = $phrasegroups = $specialtemplates = array();
include getcwd().'/global.'.VBSEO_PHP_EXT;
if(!isset($config))
{
	include getcwd().'/includes/config.'.VBSEO_PHP_EXT;
}
require_once(dirname(__FILE__). '/vbseo_sitemap_functions.php');


if(isset($_POST['runcode']))
{
	setcookie('runcode',md5($_POST['runcode']));
    $_COOKIE['runcode'] = md5($_POST['runcode']);
}

$logged_in = ($_COOKIE['runcode'] == md5($vboptions['vbseo_sm_runcode']));

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>vBSEO Search Engine XML Sitemap for vBulletin</title>

<style type="text/css">
body { font-size: 11px; margin: 0px; font-family: Arial,Calibri,Verdana,Geneva,sans-serif; height:100%; background:url("http://sitemap.vbseo.com/bg3_green.jpg") repeat-x scroll 0 0 #0E0903; }
.content { margin: 15px auto; width: 900px; text-align:left; -moz-border-radius:5px; -webkit-border-radius:5px; border-radius:5px; position: relative; background: url("http://sitemap.vbseo.com/tile.gif") repeat scroll 0 0 #FFFFFF; -moz-box-shadow:0 0 40px; -webkit-box-shadow: #111 0 0 40px; box-shadow: 0 0 40px; }
.content_inner { -moz-border-radius:5px; -webkit-border-radius:5px; border-radius:5px; padding: 10px; background: url("http://sitemap.vbseo.com/fade4.png") repeat-x scroll 0 0 transparent; }
h1 { background: url("http://sitemap.vbseo.com/bg3.jpg") repeat scroll 0 0 #2b2b2b; padding:20px; color:#598f24; text-align:left; font-size:24px; margin:0px; }
h1 a.url { color: #fff; }
a.url1 { opacity: 0.8; background: #444; padding: 5px 7px; color: #fff; font-weight: 100; position: absolute; top: 10px; right: 30px; -moz-border-radius-bottomleft:5px; -moz-border-radius-bottomright:5px; -webkit-border-bottom-left-radius:5px; -webkit-border-bottom-right-radius:5px; border-bottom-left-radius:5px; border-bottom-right-radius:5px;  }
table { font-size: 11px; font-family: Verdana, Arial, Helvetica, sans-serif; -moz-border-radius:5px; -webkit-border-radius:5px; border-radius:5px; }
a:link, a:visited, a:hover, a:active { text-decoration: none; }
a:hover { text-decoration: underline; }
.formtbl { clear: both; border: 1px solid #cfcfcf; }
.header { color: #4e4e4e; background: #dce4c3; font-weight:bold;}
.subheader { color: #ffffff; background: #6E7A9A }
.altfirst { background: #ffffff; padding:6px 4px; }
.altsecond { background: #f5f5f5; padding:6px 4px; }
.alt1 { border-bottom: 1px solid #f5f5f5; }
.alt2 { border-bottom: 1px solid #fff;}
.alt3 { border-bottom: 1px solid #bbb; border-right: 5px solid #dce4c3; border-left: 5px solid #dce4c3;}
.alt4 { border-right: 0px solid #dce4c3;}

.alert { padding:5px; margin:2px; font-size:12px; width: 600px; background-color:#f00; color:#fff; clear: both; -moz-border-radius:5px; -webkit-border-radius:5px; border-radius:5px; }
.navigation { outline: none; display: block; background: url("http://sitemap.vbseo.com/bg3.jpg") repeat scroll 0 0 #2b2b2b; padding: 5px 0; margin: 0; float: left; width: 100%; }
.navigation li { outline: none; float: left; list-style: none; padding-left: 3px; margin-top: 6px; }
.navigation li.first { padding-left: 20px; }
.navigation li a {  outline: none; font-size: 12px; background: #7db249; color: #fff; -moz-border-radius-topleft:3px; -moz-border-radius-topright:3px; -webkit-border-top-left-radius:3px; -webkit-border-top-right-radius:3px; border-top-left-radius:3px; border-top-right-radius:3px; padding: 5px 7px; }
.navigation li a:hover, .navigation li a.selected { outline: none; background-color: #FFFFFF; color: #555; }
.navigation li.external { float: right; margin-top: 4px; }
.navigation li.external a { outline: none; font-size: 11px; background: transparent; color: #fff; padding: 5px;  }
.navigation li.external a:hover { outline: none; text-decoration: underline; }
.navigation li.last { padding-right: 20px; }
ul { margin-top:5px; margin-bottom:5px; }
h3 { font-size:12px; background:#7db249; margin:0px; padding:10px; display:block; }
h3 a.url { float:right; font-weight:normal; color: #333; }
a.url { color: #000; }
.footer a { color: #fff; text-decoration:underline; }
.footer a:hover { text-decoration:none; }
h2 { clear:both; color:#555555; font-size:18px; margin:15px 0; }
.footer { background: url("http://sitemap.vbseo.com/bg3.jpg") repeat scroll 0 0 #2b2b2b; color: #fff; padding:10px; font-size: 12px; }
.footer .date { float: right; }
.inner { clear: both; float: left; margin: 0 10px; width: 860px; }
.inner a { color: #5c9940; }
.clear { clear: both;}
.pagination { clear:both; margin: 3px 0; float: right; width: 640px; text-align: right; }
.pagination span { display:inline-block; padding: 3px; -moz-border-radius:3px; -webkit-border-radius:3px; border-radius:3px; margin: 1px 1.5px; color: #606060; }
.pagination a span { margin: 0; }
.pagination span.title { border: 1px solid #cfcfcf; }
.pagination span.current { border: 1px solid #a5a5a5; color:#111; font-weight: bold; background: #dce4c3; }
.pagination a:hover, .pagination span.current.prev:hover, .pagination span.current.next:hover { border: 1px solid #a5a5a5; color:#111; background: #dce4c3; font-weight: normal; }
.pagination a, .pagination span.current.prev, .pagination span.current.next { font-weight: normal; display:inline-block; background: #fff; padding: 3px; -moz-border-radius:3px; -webkit-border-radius:3px; border-radius:3px; margin: 1px 1.5px; color: #606060; border: 1px solid #cfcfcf; }
.button { color: #fff; font-size: 11px; font-weight: bold; -moz-border-radius:3px; -webkit-border-radius:3px; border-radius:3px; background:#39660d; border: none; padding: 3px 4px; cursor: pointer; }
.button:hover { background: #598f24; }
.button1 { color: #fff; font-size: 11px; font-weight: bold; -moz-border-radius:3px; -webkit-border-radius:3px; border-radius:3px; background:#598f24; border: none; padding: 3px 4px; cursor: pointer; }
.button1:hover { background: #39660d; }
.botsonly { float: left; clear: left; margin: 8px 0;}
.botsonly a { -moz-border-radius:3px; -webkit-border-radius:3px; border-radius:3px; padding: 3px 6px; color:#333;  }
.botsonly a:hover { text-decoration: underline;  }
.botsonly a.current { background: #CFCFCF; text-decoration:none; }
</style>

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.3/jquery.min.js">
</script>
<script type="text/javascript" src="jquery.pagination.js"></script>
</head>
<body>
<div class="content">
<div class="content_inner">
<h1><a class="url" href="index.php">vBSEO Search Engine XML Sitemap</a></h1>
<?php if($logged_in){?><a class="url1" target="_blank" href="<?php echo vbseo_sitemap_furl(vbseo_file_gz('sitemap_index.xml'))?>">Sitemap Index: <?php echo vbseo_sitemap_furl(vbseo_file_gz('sitemap_index.xml'))?></a>


<ul class="navigation">

<li class="first"><a <?php if($_GET['rlist'])echo 'class="selected" ';?>href="index.php?rlist=true">Reports List</a></li>
<li><a <?php if($_GET['dlist'])echo 'class="selected" ';?>href="index.php?dlist=true">Downloads Log</a></li>
<li><a <?php if($_GET['hits'])echo 'class="selected" ';?>href="index.php?hits=true" title="v3.3.0 or higher is required">SE Bots Activity Log</a></li>
<li><a href="vbseo_sitemap.php" onclick="return confirm('This action will generate an updated sitemap and ping relevant search engines. This process may take a few minutes, depending on the size of your community. Do you wish to proceed?')">Run Generator</a></li>
<li class="external last"><a href="http://www.bing.com/toolbox/posts/archive/2009/10/09/submit-a-sitemap-to-bing.aspx" target="_blank">Submit Sitemap to Bing</a></li>
<li class="external"><a href="http://www.google.com/webmasters/sitemaps" target="_blank">Google Webmasters Page</a></li>
<li class="external"><a href="http://www.google.com/support/webmasters/?hl=en" target="_blank">Google Sitemap FAQ</a></li>



</ul>
<?php
}

?>
<div class="inner">
<table style="width:100%; "><tr><td valign="top">



<?php 

if(!$logged_in)
{
?>
<form action="index.php" method=POST name="loginform">
              <table border="0" cellspacing="0" cellpadding="4" align="center">
                <tr>
                  <td align="center"><strong>This area is password protected.
<?php      
	if(isset($_POST['runcode']) && $_POST['runcode'] != $vboptions['vbseo_sm_runcode'])
	{
		echo '<br/><br/><font color="#ff0000">Login failed</font>';
	}
?>                          
                  
                  </strong></td>
                </tr>
                <tr align="center">
                  <td>Enter your "vBSEO Sitemap Interface Access Password" from admincp:
                  	<br /><br /><input type=password name="runcode" size=15>
                          <input type="hidden" name="login" value="1">
				  		  <input type="hidden" name="securitytoken" value="<?php echo $bbuserinfo[securitytoken];?>" />
                          <input type="submit" name="submit" value="Login" class="button1" />
                          
                          </td>
<SCRIPT LANGUAGE="JavaScript" type="text/javascript">
  document.forms['loginform'].runcode.focus();
</script>
	
                  </tr>
              </table>
</form>
<?php 
}else
{

	if(!$_GET)
		$_GET['rlist'] = true;

	if(!is_writable(VBSEO_DAT_FOLDER))
	echo '<div class="alert">'.VBSEO_DAT_FOLDER.' folder is not writable, make sure that it has <b>0777</b> permissions</div>';
	
	if(file_exists(VBSEO_DAT_FOLDER_BOT) && !is_writable(VBSEO_DAT_FOLDER_BOT))
	echo '<div class="alert">'.VBSEO_DAT_FOLDER_BOT.' folder is not writable, make sure that it has <b>0777</b> permissions</div>';
	if(file_exists(VBSEO_SM_DLDAT) &&
		!is_writable(VBSEO_SM_DLDAT))
	echo '<div class="alert">'.VBSEO_SM_DLDAT.' file is not writable, make sure that it has <b>0666</b> permissions</div>';
?>

<?php 

	function vbseo_get_loglist($folder = VBSEO_DAT_FOLDER)
	{
		global $log_list;

    	$log_list = array();
    	$pd = @opendir($folder);
    	while($fn = @readdir($pd))
    	if(strstr($fn, '.log'))
    		$log_list[] = $fn;
    	@closedir($pd);
    	sort($log_list);
    }

	function vbseo_get_datlog($dir, $filename = '')
	{
		$filename = preg_replace('#[^a-z0-9\-\_\.]#i', '', $filename);
		$filename = $dir . $filename;
		return unserialize(implode('', file($filename)));
	}

	function vbseo_get_dllog()
	{
   		$dl_list = file_exists(VBSEO_SM_DLDAT) ? vbseo_get_datlog(VBSEO_SM_DLDAT) : array();
   		return $dl_list;
	}

	function vbseo_chg_show($new, $old)
	{
		if(!isset($old) || !isset($new) || ($new-$old==0) )return '-';
		return ($new-$old>0?'+':'').number_format($new-$old, 0);
	}

	function vbseo_val_show($val)
	{
		return $val ? number_format($val,0) : '-';
	}

	if(($remlist = $_GET['removelog'])||($remlist = $_POST['removelog']))
	{
		foreach($remlist as $rlog=>$rval)
		 if(preg_match('#^\d+\.log$#', $rlog))
			@unlink(VBSEO_DAT_FOLDER . $rlog);
	
		$_GET['rlist'] = true;
	}

	function vbseo_sm_pager($total, $pagesize, $cpage, $preurl)
	{
		$pager = '
<div id="pagination"></div>
<script type="text/javascript">
function handlePaginationClick(newpage, pcontainer)
{
	if(newpage+1 != '.intval($cpage).')
	document.location = "'.$preurl.'&page=" + (newpage+1);
	return false;
}

$("#pagination").pagination('.intval($total).', {
        items_per_page:'.intval($pagesize).',
        callback:handlePaginationClick,
        num_edge_entries:2,
        current_page: '.intval($cpage-1).'
});
</script>
';
		//for($i=0;$i*$pagesize<$total||!$i;$i++)
		//$pager .= ($i+1==$cpage)?'<span class="current">['.($i+1).']</span> ':'<a href="'.$preurl.'&page='.($i+1).'"><span>'.($i+1).'</span></a> ';
		//return '<span class="title">Pages:</span> '.$pager;
		
		return $pager;
	}



	vbseo_sm_prune(VBSEO_DAT_FOLDER);
	vbseo_sm_prune(VBSEO_DAT_FOLDER_BOT);
	vbseo_sm_prune(VBSEO_SM_DLDAT);



	if(preg_match('#^\d+\.log#',$_GET['hitdetails']))
	{
		$stat = vbseo_get_datlog(VBSEO_DAT_FOLDER_BOT, $_GET['hitdetails']);
		$datepart = str_replace('.log', '', $_GET['hitdetails']);
		$bots = array_keys($stat);
		function ucmp_bot($a, $b) 
		{  global $stat;if ($stat[$a]['total'] == $stat[$b]['total']) return 0; return ($stat[$a]['total'] > $stat[$b]['total']) ? -1 : 1; }

		usort($bots, "ucmp_bot");

		$pages = $stat['all'];
		arsort($pages);
?>
<h2>SE Bots Activity Details - <?php echo substr($datepart,0,4).'-'.substr($datepart,4,2).'-'.substr($datepart,6)?></h2>

<TABLE class="formtbl" cellspacing="0" cellPadding="4" border="0" width="100%">
<tr class="header" bgcolor="#dce4c3" style="border-bottom: 1px solid #000000">
	<td >SE Bot / Page</td>
<?php  foreach($bots as $bot) echo "
	<td width=\"80\">".htmlentities($bot)."</td>";
?>
</tr>
<?php 
	$ln = 0;
	foreach($pages as $pg=>$cnt)
	{
	$ln++;
?>
<tr <?php echo $pg=='total' ? ' bgcolor="#cfcfcf" style="opacity: 0.6;"' : 'class="'.($ln%2?'altfirst':'altsecond').'"';?>>
	<td><?php echo htmlspecialchars(substr($pg,0,20));?></td>
<?php  foreach($bots as $bot) echo "
	<td".($bot=='all' ? ' style="font-weight:bold"':'').">".vbseo_val_show($stat[$bot][$pg])."</td>";
?>
</tr>
<?php 		
	$pind = $stat['urls_no_tot'];
	}
?>
</TABLE>
<?php   
	}else
	if($_GET['hits'])
	{
		vbseo_get_loglist(VBSEO_DAT_FOLDER_BOT);
	if(VBSEO_SORT_ORDER == 'desc')
		rsort($log_list);

	$cpage = $_GET['page'] ? intval($_GET['page']) : 1;
	$pager = vbseo_sm_pager(count($log_list), VBSEO_SM_PAGESIZE, $cpage, 'index.php?hits=true');
	$log_list = array_slice($log_list, ($cpage-1)*VBSEO_SM_PAGESIZE, VBSEO_SM_PAGESIZE);

?>
<h2>Search Engine Bots Activity Log</h2>
<div class="pagination"><?php echo $pager;?></div>
<TABLE class="formtbl" cellspacing="0" cellPadding="4" border="0" width="100%">
<?php 
	$main_bots_list = array(
	'Googlebot', 'Yahoo', 'bingbot'
	);

	$main_pages = array(
	'Forum Display' => 'forumdisplay.'.VBSEO_PHP_EXT,
	'Show Thread'   => 'showthread.'.VBSEO_PHP_EXT ,
	'Show Post'     => 'showpost.'.VBSEO_PHP_EXT,
	'Member Profile'=> 'member.'.VBSEO_PHP_EXT,
	'Blog'          => array('blog.'.VBSEO_PHP_EXT,'entry.'.VBSEO_PHP_EXT),
	);

	if($vbseo_vars['isvb4'])
		$main_pages['CMS'] = 'content.'.VBSEO_PHP_EXT;

?>

<tr cellspading="1" bgcolor="#dce4c3" style="border-bottom: 1px solid #000000">
	<td align="center" rowspan="2">No</td>
	<td align="center" rowspan="2">Date</td>
	<td align="center" rowspan="2" class="alt4">Total</td>
	<td class="alt1 alt3 alt4" align="center" colspan="<?php echo count($main_pages)+1;?>"><b>Content Type</b></td>
	<td class="alt1 alt3" align="center" colspan="<?php echo count($main_bots_list)+1?>"><b>Spiders</b></td>
	<td rowspan="2" class="alt5" align="center">Details</td>
</tr>
<tr bgcolor="#dce4c3" style="border-bottom: 1px solid #000000">
<?php
	foreach($main_pages as $k=>$v)
	echo '<td>'.$k.'</td>';
?>
	<td class="alt4">Others</td>
<?php 
	foreach($main_bots_list as $botname)
		echo "<td>$botname</td>\n";
?>
	<td>Others</td>
</tr>
<?php 


	$ln = 0;
	foreach($log_list as $ll)
	{
		$ln++;
		$stat = vbseo_get_datlog(VBSEO_DAT_FOLDER_BOT,$ll);
		$datepart = str_replace('.log', '', $ll);
?>
<tr class="<?php echo $ln%2?'altfirst':'altsecond'?>">
	<td align="center"><?php echo $ln+($cpage-1)*VBSEO_SM_PAGESIZE?></td>
	<td align="center"><?php echo substr($datepart,0,4).'-'.substr($datepart,4,2).'-'.substr($datepart,6)?></td>
	<td align="center"><?php echo vbseo_val_show($stat['all']['total'])?></td>
<?php
	$mpsum = 0;
	foreach($main_pages as $k=>$v)
	{

	$vsum = 0;
	if(!is_array($v))$v = array($v);
		foreach($v as $v1)
		$vsum += $stat['all'][$v1];
	
	
	echo '<td align="center">'.vbseo_val_show($vsum).'</td>';
	$mpsum += $vsum;
	}
?>
	<td align="center"><?php echo vbseo_val_show($stat['all']['total'] - ($mpsum));?></td>
<?php 
	$mbots_tot = 0;
	foreach($main_bots_list as $botname)
	{
		$bot_hits = $stat[$botname]['total']+0;
		$mbots_tot += $bot_hits;
		echo '<td align="center">'.vbseo_val_show($bot_hits).'</td>';
	}
?>
	<td align="center"><?php echo vbseo_val_show($stat['all']['total'] - $mbots_tot)?></td>
	<td align="center">
<a href="index.php?hits=true&hitdetails=<?php echo $ll?>">View</a>
	</td>
</tr>
<?php 		
	$pind = $stat['urls_no_tot'];
	}
?>
</TABLE>
<?php   
	}else
	if($_GET['details'])
	{
		vbseo_get_loglist();
		$stat = vbseo_get_datlog(VBSEO_DAT_FOLDER,$_GET['details']);
		$li = array_search($_GET['details'], $log_list);
		$stat2 = $li ? vbseo_get_datlog(VBSEO_DAT_FOLDER,$log_list[$li-1]):array();

?>
<h2>Generated Sitemap Details</h2>

<TABLE class="formtbl" cellspacing="0" cellPadding="4" border="0" width="100%">
<tr class="altfirst">
	<td>Date</td>
	<td><b><?php echo date('Y-m-d H:i',$stat['start'])?></b></td>
</tr>
<tr class="altsecond">
	<td>Processing time </td>
	<td><?php echo number_format($stat['end']-$stat['start'],2)?> s</td>
</tr>
<tr class="altfirst">
	<td>Total URLs</td>
	<td><?php echo number_format($stat['urls_no_tot'],0)?>
	(<?php echo vbseo_chg_show($stat['urls_no_tot'],$stat2['urls_no_tot'])?>)</td>
</tr>
<?php
$urltypes = array(
'f'=>'Forumdisplay URLs',
't'=>'Showthread URLs',
'p'=>'ShowPost URLs',
'arc'=>'Archive URLs',
'm'=>'Member Profile URLs',
'poll'=>'Poll Results URLs',
'blog'=>'Blog Entries URLs',
'blogtag'=>'Blog Tag URLs',
'a'=>'Album URLs',
'g'=>'Social Group URLs',
'tag'=>'Tag URLs',
'cms'=>'CMS URLs',
);
$stat['arc'] = $stat['af']+$stat['at'];
$stat2['arc'] = $stat2['af']+$stat2['at'];

foreach($urltypes as $t=>$desc)
{
$i++;
?>
<tr class="<?php echo $i%2 ? 'altsecond':'altfirst';?>">
	<td><?php echo $desc;?></td>
	<td><?php echo number_format($stat[$t],0)?> (<?php echo vbseo_chg_show($stat[$t],$stat2[$t])?>)</td>
</tr>
<?php 
}
?>
<TR class=header>
      <TD colSpan="2">Sitemap Files</TD>
</TR>
<tr class="altfirst">
	<td>Index File</td>
	<td><a target="_blank" href="<?php echo vbseo_sitemap_furl(vbseo_file_gz('sitemap_index.xml'))?>"><?php echo vbseo_file_gz('sitemap_index.xml');?></a></td>
</tr>
<?php  
$fn=0;
foreach($stat['files'] as $file)
{$fn++;
?>
<tr class="altfirst">
	<td>Sitemap File #<?php echo $fn?></td>
	<td><a target="_blank" href="<?php echo $file['url']?>"><?php echo basename($file['url'])?></a> (<?php echo number_format($file['urls'])?> URLs, <?php echo number_format($file['size']/1024,2)?>Kb) <span style="color:#999;"><?php echo number_format($file['uncompsize']/1024,2)?>Kb uncompressed</span></td> 
</tr>
<?php 
}?>

<TR class=header>
      <TD colSpan="2">Search Engines Pings</TD>
</TR>
<tr class="altfirst">
	<td>Google</td>
	<td><?php echo (isset($stat['ping'])?($stat['ping']?'Successful':'FAILED'):'Disabled')?></td>
</tr>
<tr class="altfirst">
	<td>Bing</td>
	<td><?php echo (isset($stat['pingbing'])?($stat['pingbing']?'Successful':'FAILED'):'Disabled')?></td>
</tr>
<tr class="altfirst">
	<td>Yahoo</td>
	<td><?php echo (isset($stat['pingyahoo'])?($stat['pingyahoo']?'Successful':'FAILED'):'Disabled')?></td>
</tr>
<tr class="altfirst">
	<td>Ask</td>
	<td><?php echo (isset($stat['pingask'])?($stat['pingask']?'Successful':'FAILED'):'Disabled')?></td>
</tr>

</table>
<?php 
	}else
	if($_REQUEST['dlist'])
	{


	$cpage = $_REQUEST['page'] ? intval($_REQUEST['page']) : 1;
	$cstart = ($cpage-1)*VBSEO_SM_PAGESIZE;

	if($remlist = $_REQUEST['removedl'])
	{
		$dllist = vbseo_get_dllog();
		if(VBSEO_SORT_ORDER != 'desc')
			$remlist = array_reverse($remlist, 1);
		
		$clist = count($dllist);
		foreach($remlist as $ri=>$rt)
		{
			$ind =$ri - 1 + $cstart;
			if(VBSEO_SORT_ORDER == 'desc')
				$ind = $clist-$ind-1;

    		if($dllist[$ind]['time']==$rt)
    			unset($dllist[$ind]);

     	}
   		$dllist = array_values($dllist);
      	$pf = fopen(VBSEO_SM_DLDAT, 'w');
      	fwrite($pf, serialize($dllist));
      	fclose($pf);
		$_GET['dlist'] = true;
	}

	$dl_list = vbseo_get_dllog();
	
	if(VBSEO_SORT_ORDER == 'desc')
		$dl_list = array_reverse($dl_list);

   	if($_GET['botsonly'])
   	{
   		$d2 = array();
   		foreach($dl_list as $dl)
   		{
   			if($dl['ua'])$d2[]=$dl;
   		}
   		$dl_list = $d2;
   	} 
	$pager = vbseo_sm_pager(count($dl_list), VBSEO_SM_PAGESIZE, $cpage, 'index.php?dlist=true'.($_REQUEST['botsonly']?'&botsonly=true':''));
	$dl_list = array_slice($dl_list, $cstart, VBSEO_SM_PAGESIZE);


?>
<h2>Sitemap Downloads Log</h2>
<div class="pagination"><?php echo $pager;?></div>
<div class="botsonly">
<a href="index.php?dlist=true" <?php if(!$_REQUEST['botsonly'])echo 'class="current"';?>>All records</a>
<a href="index.php?dlist=true&botsonly=true" <?php if($_REQUEST['botsonly'])echo 'class="current"';?>>Bots records only</a>
</div>

<TABLE class="formtbl" cellspacing="0" cellPadding="4" border="0">
<form name="selform" action="index.php" method="POST">
<input type="hidden" name="dlist" value="true" />
<input type="hidden" name="botsonly" value="<?php echo htmlentities($_REQUEST['botsonly']);?>" />
<input type="hidden" name="page" value="<?php echo htmlentities($_REQUEST['page']);?>" />
<script>
var selopts = new Array('<?php echo implode("','", range(1,count($dl_list)))?>')
function selectall(chk)
{
	for(var i=0;i<selopts.length;i++)
	{
		document.forms['selform'].elements['removedl['+selopts[i]+']'].checked = chk
	}
}
</script>
<tr bgcolor="#dce4c3" style="border-bottom: 1px solid #000000">
	<td>No</td>
	<td>Date</td>
	<td>Sitemap File</td>
	<td>Bot</td>
	<td>IP</td>
	<td>User-agent</td>
	<td>Action</td>
	<td>
<input type="checkbox" name="rm" onclick="selectall(this.checked)" value="1" />
	</td>
</tr>
<?php 


   	$dn=$dd=0;
   	foreach($dl_list as $dl)
   	{ $dn++;

   	$dt = date('Y-m-d',$dl['time']);
   	if($dt==$ddt)
   		$sdate = date('H:i',$dl['time']);
   	else
   	{
   		$sdate = date('Y-m-d H:i',$dl['time']);
   		$ddt = $dt;
   		$dd++;
   	}

   		
?>
<tr class="<?php echo $dd%2?'altfirst':'altsecond'?>">
	<td class="<?php echo $dd%2?'alt1':'alt2'?>"><?php echo $dn+($cpage-1)*VBSEO_SM_PAGESIZE?></td>
	<td class="<?php echo $dd%2?'alt1':'alt2'?>" align="right"><?php echo htmlentities($sdate)?></td>
	<td class="<?php echo $dd%2?'alt1':'alt2'?>"><?php echo htmlentities($dl['sitemap'])?></td>
	<td class="<?php echo $dd%2?'alt1':'alt2'?>"><b><?php echo htmlentities($dl['ua'])?></b></td>
	<td class="<?php echo $dd%2?'alt1':'alt2'?>"><?php echo htmlentities($dl['ip'])?></td>
	<td class="<?php echo $dd%2?'alt1':'alt2'?>"><?php echo htmlentities($dl['useragent'])?></td>
	<td class="<?php echo $dd%2?'alt1':'alt2'?>">
<a href="index.php?dlist=true&page=<?php echo intval($_REQUEST['page']);?>&removedl[<?php echo $dn?>]=<?php echo htmlentities($dl['time'])?>&botsonly=<?php echo htmlentities($_GET['botsonly'])?>" onclick="return confirm('Are you sure?')">Remove</a>
	</td>
	<td class="<?php echo $dd%2?'alt1':'alt2'?>">
<input type="checkbox" name="removedl[<?php echo $dn?>]" value="<?php echo htmlentities($dl['time'])?>" />
	</td>
</tr>
<?php  	}
?>
<tr bgcolor="#cfcfcf" style="border-bottom: 1px solid #000000; opacity: 0.6;">
	<td colspan="8" align="right"><input class="button" type="submit" name="remove" value="Remove selected"  onclick="return confirm('Are you sure?')"></td>
</tr>
</TABLE>
</form>
<?php 
	}else
	if($_GET['rlist'])
	{
		vbseo_get_loglist();

	if(VBSEO_SORT_ORDER == 'desc')
		$log_list = array_reverse($log_list);

	$cpage = $_GET['page'] ? intval($_GET['page']) : 1;
	$pager = vbseo_sm_pager(count($log_list), VBSEO_SM_PAGESIZE, $cpage, 'index.php?rlist=true');
	$log_list = array_slice($log_list, ($cpage-1)*VBSEO_SM_PAGESIZE, VBSEO_SM_PAGESIZE);

	$log_list_full = array();
	foreach($log_list as $ll)
	{
		$stat = vbseo_get_datlog(VBSEO_DAT_FOLDER,$ll);
		$ll2=array(
			'stat' => $stat,
			'pind' => $pind,
			'll' => $ll
			);
		$log_list_full[] = $ll2;
		$pind = $stat['urls_no_tot'];
	}



?>
<h2>Sitemap Reports List</h2>
<div class="pagination"><?php echo $pager;?></div>
<TABLE class="formtbl" cellspacing="0" cellPadding="4" border="0" width="100%">
<form name="selform" action="index.php" method="POST">
<script>
var selopts = new Array('<?php echo implode("','", $log_list)?>')
function selectall(chk)
{
	for(var i=0;i<selopts.length;i++)
	{
		document.forms['selform'].elements['removelog['+selopts[i]+']'].checked = chk
	}
}
</script>
<tr bgcolor="#dce4c3" style="border-bottom: 1px solid #000000">
	<td>No</td>
	<td>Date</td>
	<td>Run Time</td>
	<td>Total URLs</td>
	<td>Change</td>
	<td>Google Notify</td>
	<td>Yahoo Notify</td>
	<td>Details</td>
	<td>
<input type="checkbox" name="rm" onclick="selectall(this.checked)" value="1">
	</td>
</tr>
<?php 

	$ln = 0;
	for($ln=0;$ln<count($log_list_full);$ln++)
	{
		$ll2 = $log_list_full[$ln];
		$ll = $ll2['ll'];
		$stat = $ll2['stat'];
?>
<tr class="<?php echo $ln%2?'altfirst':'altsecond'?>">
	<td><?php echo $ln+1+($cpage-1)*VBSEO_SM_PAGESIZE?></td>
	<td><?php echo date('Y-m-d H:i',$stat['start'])?></td>
	<td><?php echo number_format($stat['end']-$stat['start'],2)?> s</td>
	<td><?php echo number_format($stat['urls_no_tot'],0)?></td>
	<td><?php echo vbseo_chg_show($stat['urls_no_tot'], $log_list_full[$ln+((VBSEO_SORT_ORDER=='desc')?1:-1)]['stat']['urls_no_tot'])?></td>
	<td><?php echo $stat['ping']?'Yes':'No'?></td>
	<td><?php echo $stat['pingyahoo']?'Yes':'No'?></td>
	<td>
<a href="index.php?rlist=true&details=<?php echo $ll?>">View details</a> |
<a href="index.php?removelog[<?php echo $ll?>]=1" onclick="return confirm('Are you sure?')">Remove</a>
	</td>
	<td>
<input type="checkbox" name="removelog[<?php echo $ll?>]" value="1" />
	</td>
</tr>
<?php 		
	}
?>
<tr bgcolor="#cfcfcf" style="border-bottom: 1px solid #000000; opacity: 0.6;">
	<td colspan="9" align="right"><input class="button" type="submit" name="remove" value="Remove selected"  onclick="return confirm('Are you sure?')" /></td>
</tr>
</TABLE>
</form>
<?php   }
}

?>
</TD></TR>
<TR><TD>
      <TABLE cellSpacing=0 cellPadding=0 width=500 align=center 
      border=0>
        <TBODY>
        <TR>
          <TD></TD></TR></TBODY></TABLE>
      <TABLE cellSpacing=0 cellPadding=1 align=center border=0>
        <TBODY>
        <TR>
          <TD align=center>
          </TD>
        </TR></TBODY></TABLE>
      </TD>
  </TR></TBODY></TABLE>
</TD></TR></TBODY></TABLE>
<?php
if(!VBSEO_ON)
{
?>
<script language="Javascript" type="text/javascript" src="http://www.crawlability.com/js/vbseo_menu.js"></script>
<?php
}
?>
</div><div class="clear"></div>
     <div class="footer">vBSEO Search Engine XML Sitemap v<?php echo VBSEO_SM_VERSION?> is &copy; 2010 <a href="http://www.crawlability.com" target="_blank">Crawlability, Inc.</a> All Rights Reserved.
          <span class="date"><?php echo date('Y-m-d H:i')?></span>
     </div>
</div>
</div>
</body>
</html>