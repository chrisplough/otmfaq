<?php
/*======================================================================*\
|| #################################################################### ||
|| # Automatic Thread Tagging Ajax Helper                             # ||
|| # Copyright ©2008-2009 Marius Czyz                                 # ||
|| #################################################################### ||
\*======================================================================*/

header("cache-Control: post-check=0, pre-check=0", false); 
header("Pragma: no-cache"); 
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 

require_once('./global.php');

if ($_REQUEST['do'] == 'gettags') {
	
	require_once(DIR . '/includes/functions_autotagger.php');
	
	if (!$vbulletin->options['autotag_ajax_existing'])
	{
		$tagger['taglist'] = $vbulletin->input->clean_gpc('r', 'existing', TYPE_NOHTML);		
	} else {
		$tagger['taglist'] = "";
	}

	$tagger['prefix'] = $vbulletin->input->clean_gpc('r', 'prefix', TYPE_NOHTML);
	$tagger['title'] = $vbulletin->input->clean_gpc('r', 'title', TYPE_NOHTML);
	$tagger['forumid'] = $vbulletin->input->clean_gpc('r', 'forumid', TYPE_INT);
	$tagger['postuserid'] = $vbulletin->input->clean_gpc('r', 'userid', TYPE_INT);
	$tags_remain = $vbulletin->input->clean_gpc('r', 'tags_remain', TYPE_INT);

	$autotags = GetAutoTags($tagger, true, $tags_remain);
	
	if (count($autotags) > 0)
	{
		echo implode(',', $autotags);
	}


}	

?>