<?php


//  AME Autmoatic Media Embedder 2.5.4
//	Copyright ©2008-2009 All rights reserved by sweetsquared.com
//	This code may not be used in whole or part without explicit written
//	permission from Samuel Sweet [samuel@sweetsquared.com] .
//  You may not distribute this or any of the associated files in whole or significant part
//	without explicit written permission from Samuel Sweet [samuel@sweetsquared.com]



/**
 * Strips [ame] code from text
 *
 * @param 	string $text
 * @return 	string
 */
function ame_strip(&$text)
{
	
	$find = array("%\[ame\](.*?)\[/ame\]%im",
		"%\[ame=(.*?)\](.*?)(?:@@AMEPARAM@@.*)?\[/ame\]%im",
		"%\[nomedia\](.*?)\[/nomedia\]%im",
		"%\[nomedia=(.*?)\](.*?)(?:@@AMEPARAM@@.*)?\[/nomedia\]%im");
	$replace = array('[url]\1[/url]', '[url=\1]\2[/url]', '[url]\1[/url]', '[url=\1]\2[/url]');
	
	return preg_replace($find, $replace, $text);
	
}

/**
 * Caches a key value pair for prepping bbcode. Gets ALL matches
 *
 * @return boolean	regexp, replacements array
 * @return boolean	fresh fetch
 */
function &fetch_full_ameinfo($findonly = false, $refresh = false)
{
	
	global $db, $vbulletin, $vbphrase, $stylevar;
	static $ameinfo = array();
	static $inied, $lastfind;

	if ($refresh)
	{
		
		$inied = false;
		
	}

	if ($lastfind && !$findonly)
	{
		
		$inied = false;
		$ameinfo = array();
		
	}

	if (!$inied)
	{

		if (!$refresh AND $vbulletin->options['automediaembed_cache'])
		{

	    	$path = $vbulletin->options['automediaembed_cache_path'];

	    	if (file_exists($path . "findonly.php"));
	    	{
	    		
	    		if ($findonly)
	    		{
	    			
	    			include($path . "findonly.php");
	    			
	    		}
	    		else
	    		{
	    			
	    			include($path . "ameinfo.php");
	    			
	    		}
	    		
	    		$inied = true;
				$lastfind = $findonly;

				return $ameinfo;
	      	}

		}

		if ($vbulletin->options['automediaembed_resolve'])
		{
			
			$embed = ",IF(extraction=1 AND embedregexp!= '', embedregexp, '') as embedregexp, IF(extraction=1 AND validation!= '', validation, '') as validation";
			$embedwhere = " AND ((extraction = 0 AND embedregexp = '') OR (extraction = 1)) ";
			
		}
		else
		{
			
			$embedwhere = " AND embedregexp = ''";
			
		}


		$sql = "SELECT findcode" . (!$findonly ? ", replacecode,title,container,ameid" : ",extraction$embed") . " FROM " . TABLE_PREFIX . "automediaembed WHERE status=1 $embedwhere
						ORDER BY displayorder, title ASC";

		$results = $db->query_read_slave($sql);

		while ($result = $db->fetch_array($results))
		{
			
			if ($result['findcode'])
			{

				if (!$findonly)
				{

					$ameinfo['find'][] = "~($result[findcode])~ie";
					$ameinfo['replace'][] = 'ame_match_bbcode($param1, $param2, \'' . $result['ameid'] . '\', \'' . ame_slasher($result['title']) . '\', ' . $result['container'] . ', \'' . ame_slasher($result['replacecode']) . '\', \'\\1\', \'\\2\', \'\\3\', \'\\4\', \'\\5\', \'\\6\')';


				}
				else
				{

					$ameinfo['find'][] = "~(\[url\]$result[findcode]\[/url\])~ie";
					$ameinfo['find'][] = "~(\[url=\"?$result[findcode]\"?\](.*?)\[/url\])~ie";
					$ameinfo['replace'][] = 'ame_match("\1", "", ' . intval($result['extraction']) .', "' . ($result['embedregexp'] ? "~" . ame_slasher($result['embedregexp']) . "~sim" : "") . '", "' . ($result['validation'] ? "~" . ame_slasher($result['validation']) . "~sim" : "") . '",$ameinfo)';
					$ameinfo['replace'][] = 'ame_match("\1", "\2", ' . intval($result['extraction']) .', "' . ($result['embedregexp'] ? "~" . ame_slasher($result['embedregexp']) . "~sim" : "") . '", "' . ($result['validation'] ? "~" . ame_slasher($result['validation']) . "~sim" : "") . '", $ameinfo)';

				}

			}
			
		}

		$inied = true;
	}

	$lastfind = $findonly;

	return $ameinfo;
}

/**
 * Escape single quotes
 * Used instead of addslashes as we only want to escape single quotes. NOT double
 *
 * @param string $text to escape
 */
function ame_slasher(&$text, $single = true)
{
	if ($single)
	{
		return str_replace("'", "\'", $text);
	}
	else 
	{
		return str_replace('"', '\"', $text);
	}
}


/**
 * Runs checks to see if urls should be converted
 *
 * @param 	boolean $dopost
 * @return 	boolean
 */
function ame_doconversion($ineditor = false)
{

	global $vbulletin;
	
	($hook = vBulletinHook::fetch_hook('automediaembed_check_perms')) ? eval($hook) : false;
	
	if ($vbulletin->options['automediaembed_disable'])
	{
		return false;
	}
	
	if (defined('AME_SKIP_PREM_CHECK'))
	{
		return true;	
	}
	
	if (!$ineditor)
	{
	
		if ($_POST['parseame_check'] && !$_POST['parseame'])
		{
			return false;
		}	
	
	}
		
	$zone = ame_fetch_zone();

	if (!$zone)
	{
		
		$zone = "default";
		
	}	

	if (!($vbulletin->userinfo['permissions']['automediaembed_edit_permissions'] & $vbulletin->bf_ugp['automediaembed_edit_permissions']["can_$zone"]))
	{
		
		return false;
		
	}	
	
	if ($vbulletin->options['automediaembed_doforums'] AND $zone == 'post')
	{
		
		global $forumid;

		if (intval($forumid) && $vbulletin->options['automediaembed_forumids'])
		{
		
			$check = "," . $vbulletin->options['automediaembed_forumids'] . ",";
			
			if (strpos($check, ",$forumid,") !== false)
			{
				
				return false;
				
			}

		}

		return true;
	
	}

	if ($vbulletin->options['automediaembed_doblogs'] AND $zone == 'blog')
	{
		
		return true;
	
	}

	if ($vbulletin->options['automediaembed_dovms'] AND $zone == 'vm')
	{
		
		return true;
	
	}

	if ($vbulletin->options['automediaembed_dogroups'] AND $zone == 'group')
	{
		
		return true;
				
	}

	if ($vbulletin->options['automediaembed_dosigs'] AND $zone == 'sig')
	{		
		
		return true;
				
	}

}

/**
 * Parses text and replaces with [ame] bbcode
 *
 * @param	string	$text is a reference to the text getting parsed
 * @param	array	$ameinfo a key value pair of replacements. Optional
 * @return	string	Reference to parsed text
 */
function ame_prep_text(&$text)
{

	$flag = 0;
	

	($hook = vBulletinHook::fetch_hook('automediaembed_prep_text_start')) ? eval($hook) : false;
	
	if (!ame_doconversion())
	{

		$flag = 2;
	
	}
	else
	{

		if (!sizeof($ameinfo))
		{
			
			$ameinfo = fetch_full_ameinfo(true);
		
		}

		$substitutes = array(
			'%\[quote([^\]]*)\](.*?)\[/quote\]%sime',
			'%\[php\](.*?)\[/php\]%sime',
			'%\[html\](.*?)\[/html\]%sime',
			'%\[code\](.*?)\[/code\]%sime',
			'%\[nomedia([^\]]*)\](.*?)\[/nomedia\]%sime',
		);

		$subhandlers = array(
			'ame_substitute(1, \'\1\', \'\2\', $ame_subbed)',
			'ame_substitute(2, \'\1\', \'\2\', $ame_subbed)',
			'ame_substitute(3, \'\1\', \'\2\', $ame_subbed)',
			'ame_substitute(4, \'\1\', \'\2\', $ame_subbed)',
			'ame_substitute(6, \'\1\', \'\2\', $ame_subbed)',
		);

		$GLOBALS['ame_subbed'] = array();
		$ame_subbed =& $GLOBALS['ame_subbed'];
			
		if (sizeof($ameinfo))
		{
			
			$text = preg_replace($substitutes, $subhandlers, $text);
			$text = preg_replace($ameinfo['find'], $ameinfo['replace'], $text);
		
		}

		if (sizeof($ame_subbed))
		{
			
			$text = preg_replace('/<<<@!([0-9]+)!@>>>/sme', 'ame_unsubstitute(\'\\1\',$ame_subbed)', $text);
		
		}

		if (strpos($text, "[/ame]") !== false)
		{
			$flag = 1;		
		}
		else if (strpos($text, "[/nomedia]") !== false)
		{
			$flag = 1;
		}

	}
	
	($hook = vBulletinHook::fetch_hook('automediaembed_prep_text_end')) ? eval($hook) : false;

	return $flag;
}

/**
 * Replaces certain bbcode with placeholders until parsing is done
 *
 * @param	int		$typeid a flag. 1=quote 2=php 3=html 4=code
 * @param	string	$ref1 a backref
 * @param	string	$ref2 a backref
 * @param	array	$array local ref to array that holds reveral info
 * @return	string	placeholder
 */
function ame_substitute($typeid, $ref1, $ref2, &$array)
{
	
	static $i;
	$i++;

	$ref1 = str_replace("\\\"", '"', $ref1);
	$ref2 = str_replace("\\\"", '"', $ref2);

	if( $typeid == 5 )
	{
		$array[$i] = $ref1;		
	}
	else if ($typeid == 1)
	{
		
		$ref2 = ame_strip($ref2);
		$array[$i] = "[quote$ref1]$ref2" ."[/quote]";
	
	}
	else if ($typeid == 6)
	{
		
		$ref2 = ame_strip($ref2);
		$array[$i] = "[nomedia$ref1]$ref2" ."[/nomedia]";
	
	}	
	else
	{
		
		$ref1 = ame_strip($ref1);
		
		if ($typeid == 2)
		{
			
			$array[$i] = "[php]" . $ref1 . "[/php]";
		
		}
		else if ($typeid == 3)
		{
			
			$array[$i] = "[html]" . $ref1 . "[/html]";
		
		}
		else if ($typeid == 4)
		{
			
			$array[$i] = "[code]" . $ref1 . "[/code]";
		
		}
		
	}

	if ($array[$i])
	{
		
		return "<<<@!$i!@>>>";
	
	}

}

/**
 * Returns data to placeholders
 *
 * @param	string	$item = the placeholder
 * @param	array	$array of placeholders
 * @return	string	original BBcode
 */
function ame_unsubstitute($item, &$array)
{
	
	return $array[$item];

}

/**
 * Straight forward swap of url and ame tags.
 *
 * @param unknown_type $text
 * @param unknown_type $ameinfo
 * @return unknown
 */
function ame_match($param1, $param2, $resolve, $embedregexp, $validation, &$ameinfo)
{

	static $bini, $bok;

	if (!$bini)
	{
		
		global $vbulletin;
		$bok = $vbulletin->options['automediaembed_resolve'];
		$bini = true;
	
	}

	if (!$bok AND $embedregexp)
	{
		
		return $param1;
	
	}

	if ($bok AND ($embedregexp OR $validation) AND $param2)
	{
		
		preg_match('~\\[url=?"?(.*?)"?\](.*)\[/url\]~sim', $param1, $match);
		$param2 = null;
		$param1 = ($match[1] ? $match[1] : $match[2]);
		
		if (strrpos($param1, '"'))
		{
			
			$param1 = substr($param1, 0, (strlen($param1) -1));
		
		}
	
	}

	if (!$param2 && $bok && $resolve)
	{

		$url = str_replace(array("[url]", "[URL]", "[/url]", "[/URL]"), array("","","",""), $param1);


		$www = ame_fetch_www_page(str_replace(array("[url]", "[URL]", "[/url]", "[/URL]"), array("","","",""), $url));

		if ($www)
		{
			
			$title = ame_fetch_www_title($www);
		
		}

		if ($embedregexp)
		{
			
			$extras = ame_extract_embed($www, $embedregexp);
			
			if ($extras)
			{
				
				$title .= $extras;
			
			}
		
		}
		
		if ($validation)
		{
			if (!ame_validate($www, $validation))
			{

				($hook = vBulletinHook::fetch_hook('automediaembed_invalid_to_embed')) ? eval($hook) : false;
				
				if ($title)
				{
					$store =  "[nomedia=\"$url\"]$title" . "[/nomedia]";
				}
				else 
				{
					$store =  "[nomedia]$url" . "[/nomedia]";
				}
				
				return $store;
								
			}
		}

		if ($title)
		{
			
			return "[ame=\"$url\"]$title" . "[/ame]";
		
		}
		else
		{
			
			return "[ame]$url" . "[/ame]";
		
		}
	
	}

	return str_replace(array("[url", "[/url]", "[URL", "[/URL]"), array("[ame", "[/ame]", "[ame", "[/ame]"), stripslashes($param1));
	
}

/**
 * Fetches destination web page for parsing using cCurl or file_get_contents if cCurl not compiled
 *
 * @param string 	$url
 * @return string	HTML contents of URL
 */
function ame_fetch_www_page($url)
{
	
	if (function_exists("curl_int"))
	{
		
		$c = curl_init();
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_URL, $url);
		$contents = curl_exec($c);
		curl_close($c);
		return $contents;
	
	}
	elseif (function_exists("file_get_contents"))
	{
		
		return @file_get_contents($url);
	
	}
	
}

/**
 *	Parses the contents of a web page to return the title
 *	@param string web site
 *
 */
function ame_fetch_www_title(&$content)
{
	

	if(preg_match("|<[\s]*title[\s]*>([^<]+)<[\s]*/[\s]*title[\s]*>|Ui", $content, $match))
	{
		
		$title = trim(str_replace(array("\t","\n"), "", $match[1]));
		//$title = unhtmlspecialchars(utf8_decode($title), true);
		$title = unhtmlspecialchars($title);
        return $title;
        
	}
	else
	{
		
		return false;
		
	}

}

/**
 * Extracts embed code based on items embed regexp
 *
 * @param unknown_type $content
 * @param unknown_type $regexp
 * @return unknown
 */

function ame_extract_embed(&$content, &$regexp)
{
	
	if (!$regexp)
	{
		
		return;
	
	}

	if(preg_match($regexp, $content, $match))
	{
		
		if (is_array($match))
		{
			
			foreach($match as $key => $value)
			{
				
				$return .= "@@AMEPARAM@@" . trim(str_replace(array("\t","\n"), "", $value));
			
			}
			
			return $return;
		
		}
		else
		{
			
			return false;
		
		}

	}
	else
	{
		
		return false;
	
	}
}

/**
 * Simple preg_match process to see if regexp finds a match
 * used to determine if the destination doesnt allow outside mebedding
 *
 * @param unknown_type $content
 * @param unknown_type $regexp
 * @return bool
 */

function ame_validate(&$content, &$regexp)
{
	
	if (!$regexp)
	{
		
		return true;
	
	}

	if(preg_match($regexp, $content, $match))
	{
		
		return true;

	}
	else
	{
		
		return false;
	
	}
}

/**
 * Parses bbcode but only if it is being shown... NOT in the editor
 *
 * @param unknown_type $parser
 * @param unknown_type $text
 * @param unknown_type $id
 * @return unknown
 */
function ame_process_bbcode(&$parser, &$param1, $param2 = '')
{

	if (class_exists('vB_BbCodeParser_Wysiwyg') AND is_a($parser, 'vB_BbCodeParser_Wysiwyg'))
	{
		
		return $text;
	
	}
	else
	{
		
		global $vbulletin;
		($hook = vBulletinHook::fetch_hook('automediaembed_parse_bbcode_start')) ? eval($hook) : false;
		$ameinfo = fetch_full_ameinfo();
		
		$text = preg_replace($ameinfo['find'], $ameinfo['replace'], ($param2 ? $param2 : $param1), 1);

		($hook = vBulletinHook::fetch_hook('automediaembed_parse_bbcode_end')) ? eval($hook) : false;
		
		return $text;
	
	}

}

/**
 * Parses nomedia but only if it is being shown... NOT in the editor
 *
 * @param unknown_type $parser
 * @param unknown_type $text
 * @param unknown_type $id
 * @return unknown
 */
function ame_process_nomedia_bbcode(&$parser, &$param1, $param2 = '')
{
	global $vbphrase;
	
	if (class_exists('vB_BbCodeParser_Wysiwyg') AND is_a($parser, 'vB_BbCodeParser_Wysiwyg'))
	{
		
		return $text;
	
	}
	else
	{
		
		static $inc;
		$inc++;
		
		$ameinfo = array();
			
		global $vbulletin, $stylevar;
		$template 	= "ame_nomedia";		

		$ameinfo['zone'] 	= ame_fetch_zone();
		$ameinfo['id'] 		= "ame_" .TIMENOW . "_" . $inc;
		$ameinfo['id_tag'] 	= $ameinfo['zone'] . "_" . TIMENOW . "_" . $inc;
		$ameinfo['title'] 	= ($param2 ? $param1 : "");
		$ameinfo['url']		= ($ameinfo['title'] ? $param2 : $param1 );
				
		($hook = vBulletinHook::fetch_hook('automediaembed_parse_bbcode_nomedia_start')) ? eval($hook) : false;
		
		eval('$text = "' . fetch_template($template) . '";');
		
		($hook = vBulletinHook::fetch_hook('automediaembed_parse_bbcode_nomedia_end')) ? eval($hook) : false;
		
		return $text;
	
	}

}

/**
 * Called when parsing an AME code. Does the actual work in converting contents of [ame] code
 *
 * @param string $param1	either the title or url of [ame] tag (only title if $param2 is empty)
 * @param string $param2	the optional title of [ame] tag.
 * @param string $title		default title
 * @param boolean $container Flag to include container based on definition setting
 * @param string $code		The embed code with string placeholders (i.e $p1)
 * @param string $p0		optional parameter for complex replacements
 * @param string $p1		optional parameter for complex replacements
 * @param string $p2		optional parameter for complex replacements
 * @param string $p3		optional parameter for complex replacements
 * @param string $p4		optional parameter for complex replacements
 * @param string $p5		optional parameter for complex replacements
 * @return string			Embedded code
 */
function ame_match_bbcode($param1, $param2 = '', $ameid = '', $title = '', $container = false, $code = '', $p0 = '', $p1 = '', $p2 = '', $p3 = '', $p4 = '', $p5 = '')
{
	
	global $vbulletin, $stylevar, $vbcollapse;
	
	static $inc;
	$inc++;
	
	$ameinfo = array();

	$ameinfo['zone'] 	= ame_fetch_zone();
	$ameinfo['id'] 		= "ame_" .TIMENOW . "_" . $inc;
	$ameinfo['id_tag'] 	= $ameinfo['zone'] . "_" . TIMENOW . "_" . $inc;
	$ameinfo['title'] 	= htmlspecialchars_uni($title);
	$ameinfo['key'] 	= $ameid;

	$position = strpos($param1, "@@AMEPARAM@@");

	if ($position !== false)
	{
		
		$params = substr($param1, $position + 12);
		$param1 = substr($param1, 0, $position);
		$params = explode("@@AMEPARAM@@", $params);

		if (is_array($params))
		{
			
			foreach($params as $key => $value)
			{
				
				eval('$p' . $key . '=\'' . ame_slasher($value) . '\';');
			
			}
		
		}

	}

	$dimensions 		=  "_$ameinfo[zone]";
	$ameinfo['width'] 	= $vbulletin->options['automediaembed_width' . $dimensions];
	$ameinfo['height'] 	= $vbulletin->options['automediaembed_height' . $dimensions];	

	if ($container)
	{
		
		$ameinfo['container'] = true;
		$templatename = ame_fetch_templatename($ameinfo['zone']);
	
	}

	if (!$param2)
	{
		
		$ameinfo['url'] = $param1;
	
	}
	else
	{
		
		$ameinfo['title'] = htmlspecialchars_uni($param1);
		$ameinfo['title'] = $param1;
		$ameinfo['url'] = $param2;

		if ($vbulletin->options['automediaembed_limit'])
		{
			
			if (strlen($title) > $vbulletin->options['automediaembed_limit'])
			{
				
				if (function_exists('fetch_trimmed_title'))
				{
					
					$ameinfo['title'] = fetch_trimmed_title($ameinfo['title'], $vbulletin->options['automediaembed_limit']);
				
				}
			
			}
		
		}
	
	}
	
	($hook = vBulletinHook::fetch_hook('automediaembed_parse_bbcode_match_start')) ? eval($hook) : false;
	
	eval('$ameinfo[\'code\'] = "' . ame_slasher($code, false) . '";');
	

	if ($templatename)
	{
		
		eval('$ame_results = "' . fetch_template($templatename) . '";');

	}
	else
	{
		
		eval('$ame_results = "' . ame_slasher($ameinfo['code'], false) . '";');
	
	}

	eval('$return = "' . fetch_template('ame_output') . '";');

	($hook = vBulletinHook::fetch_hook('automediaembed_parse_bbcode_match_end')) ? eval($hook) : false;
	
	return $return;

}

/**
 * Returns current 'Zone' (i.e. group, signature, etc...)
 *
 * @return string	Current Zone
 */
function ame_fetch_zone()
{
	switch (THIS_SCRIPT)
	{
		
		case 'group':
			$name = 'group';
			break;
			
		case 'blog':
		case 'blog_post':
			$name = 'blog';
			break;
			
		case 'visitormessage':
		case 'member':
			$name = "vm";
			break;
			
		case 'showthread':
		case 'showpost':
		case 'newpost':
		case 'newthread':
		case 'newreply':
		case 'editpost':
			$name = "post";
			break;
			
		default:
			$name = "other";
	}

	if ($GLOBALS['ame_zone'] == 'signature')
	{
		$name = 'sig';
	}

	return $name;

}

/**
 * Fetches template name from provided Zone
 *
 * @param string $zone	current zone
 * @return string		associated template name
 */
function ame_fetch_templatename($zone = '')
{
	
	global $vbulletin;

	if (!$zone)
	{
		
		$zone = "other";
	
	}

	$templatename = $vbulletin->options['automediaembed_template_' . $zone];

	return ($templatename ? "ame_$templatename" : "");

}

/**
 * Prints the disable checkbox at bottom of editor
 *
 */

function ame_disable_option()
{
	
	global $show, $vbulletin, $checked, $vbphrase;

	if (!ame_doconversion(true))
	{
		
		return;
	
	}

	$hooked = "\$vbphrase[automatically_parse_links_in_text]</label></div>";
	($hook = vBulletinHook::fetch_hook('automediaembed_disable_option_start')) ? eval($hook) : false;

	if ($vbulletin->templatecache['newthread'])
	{
		
		$name = "newthread";
		$checked['parseame'] = "checked=\"checked\"";
	
	}
	else if ($vbulletin->templatecache['newreply'])
	{
		
		$name = "newreply";
		$checked['parseame'] = "checked=\"checked\"";
	
	}
	else if ($vbulletin->templatecache['editpost'])
	{
		
		global $postinfo;
		$name = "editpost";
		if ($_POST['parseame_check']) //preview
		{
			
			$checked['parseame'] = ($_POST['parseame'] ? "checked=\"checked\"" : "");
			
		}
		else 
		{
			
			$checked['parseame'] = ($postinfo['ame_flag'] != 2 ? "checked=\"checked\"" : "");
			
		}
	
	}
	else if ($vbulletin->templatecache['socialgroups_editor'])
	{
		
		$name = "socialgroups_editor";
		$checked['parseame'] = "checked=\"checked\"";
	
	}
	else if ($vbulletin->templatecache['blog_comment_editor'])
	{
		
		global $bloginfo;
		$name = "blog_comment_editor";
		
		if ($_POST['parseame_check']) //preview
		{
			
			$checked['parseame'] = ($_POST['parseame'] ? "checked=\"checked\"" : "");
			
		}
		else 
		{
			
			$checked['parseame'] = ($bloginfo['ame_flag'] != 2 ? "checked=\"checked\"" : "");
			
		}
		
	
	}
	else if ($vbulletin->templatecache['blog_entry_editor'])
	{
		
		global $bloginfo;
		$name = "blog_entry_editor";
		$hooked = "\$vbphrase[automatically_parse_links_in_text]</label>";
		
		if ($_POST['parseame_check']) //preview
		{
			
			$checked['parseame'] = ($_POST['parseame'] ? "checked=\"checked\"" : "");
			
		}
		else 
		{
			
			$checked['parseame'] = ($bloginfo['ame_flag'] != 2 ? "checked=\"checked\"" : "");
		
		}
	
	}
	else if ($vbulletin->templatecache['visitormessage_editor'])
	{
		
		global $message;
		$name = "visitormessage_editor";
		$hooked = "\$vbphrase[automatically_parse_links_in_text]</label>";

		if ($_POST['parseame_check']) //preview
		{
			
			$checked['parseame'] = ($_POST['parseame'] ? "checked=\"checked\"" : "");
			
		}
		else 
		{
		
			$checked['parseame'] = ($message['ame_flag'] != 2 ? "checked=\"checked\"" : "");
		
		}
	
	}

	$vbulletin->templatecache["$name"] = str_replace($hooked, $hooked . $pre . '<div><label for=\"cb_parseame\"><input type=\"checkbox\" name=\"parseame\" value=\"1\" id=\"cb_parseame\" tabindex=\"1\" $checked[parseame] />$vbphrase[automediaembed_disable_ame]</label><input type=\"hidden\" name=\"parseame_check\" value=\"1\" />' . $post, $vbulletin->templatecache["$name"]);
	($hook = vBulletinHook::fetch_hook('automediaembed_disable_option_end')) ? eval($hook) : false;

}


?>