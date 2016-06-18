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

if (!isset($GLOBALS['vbulletin']->db))
{
	exit;
}

/**
* Fetches the tagbits for display in an entry
*
* @param	array	Blog info
*
* @return	string	Tag bits
*/
function fetch_entry_tagbits($bloginfo, &$userinfo)
{
	global $vbulletin, $vbphrase, $show, $template_hook;

	if ($bloginfo['taglist'])
	{
		$tag_array = explode(',', $bloginfo['taglist']);

		$tag_list = array();
		foreach ($tag_array AS $tag)
		{
			$tag = trim($tag);
			if ($tag === '')
			{
				continue;
			}
			$tag_url = urlencode(unhtmlspecialchars($tag));
			$tag = fetch_word_wrapped_string($tag);

			($hook = vBulletinHook::fetch_hook('blog_tag_fetchbit')) ? eval($hook) : false;

			$templater = vB_Template::create('blog_tagbit');
				$templater->register('tag', $tag);
				$templater->register('tag_url', $tag_url);
				$templater->register('userinfo', $userinfo);
				$templater->register('pageinfo', array('tag' => $tag_url));
			$tag_list[] = trim($templater->render());
		}
	}
	else
	{
		$tag_list = array();
	}

	($hook = vBulletinHook::fetch_hook('blog_tag_fetchbit_complete')) ? eval($hook) : false;

	return implode(", ", $tag_list);
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:08, Tue Jul 12th 2011
|| # SVN: $Revision: 25612 $
|| ####################################################################
\*======================================================================*/
?>
