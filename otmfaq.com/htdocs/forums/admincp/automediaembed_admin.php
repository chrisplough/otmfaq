<?php

	//  AME Autmoatic Media Embedder 2.5.4
	//	Copyright ©2008-2009 All rights reserved by sweetsquared.com
	//	This code may not be used in whole or part without explicit written
	//	permission from Samuel Sweet [samuel@sweetsquared.com] .
	//  You may not distribute this or any of the associated files in whole or significant part
	//	without explicit written permission from Samuel Sweet [samuel@sweetsquared.com]

	error_reporting(E_ALL & ~E_NOTICE);
	define('NO_REGISTER_GLOBALS', 1);

	$phrasegroups 		= array('automediaembed_admin');
	$specialtemplates 	= array('AME_settings');	
	
	require_once('./global.php');
	
	if (!$_REQUEST['do'])
	{
	    $action			= "display";
	    $_REQUEST['do'] = "display";
	}
	else
	{
	    $action			= $_REQUEST['do'];
	}	

	/**
	 * Do error checking?
	 */
	switch ($action)
	{
		case 'settings':
		case 'edit':
		case 'import':
		case 'export':
			$donotices = true;
			break;
			
		default:
			$donotices = false;
	}
	
	/**
	 * Internal helper functions
	 */
	
	
	/**
	 * Simple vB Redirection Function
	 *
	 * @param string $do
	 * @param string $stopmessage
	 * @param string $var
	 */
	function redirect($do, $stopmessage, $var = '')
	{
		define('CP_REDIRECT', "automediaembed_admin.php?do=$do");
		print_stop_message($stopmessage, $var);
	}
	
	/**
	 * Ensures that vbblog is installed before commencing action on it.
	 *
	 * @return boolean or stop message
	 */
	function validate_blog()
	{
		global $db;
		$db->hide_errors();
		$result = $db->query_first_slave("SELECT max(blogtextid) total FROM " . TABLE_PREFIX . "blog_text");
		$db->show_errors();

		if ($db->errno)
		{
			print_stop_message('automediaembed_no_blog');
		}

		return true;
	}
	
	/**
	 * Ensures that Social Groups are supported before commencing action
	 *
	 *  @return boolean or stop message
	 */
	function validate_group()
	{
		global $db;
		$db->hide_errors();
		$result = $db->query_first_slave("SELECT max(gmid) total FROM " . TABLE_PREFIX . "groupmessage");
		$db->show_errors();

		if ($db->errno)
		{
			print_stop_message('automediaembed_no_group');
		}
		
		return true;		
			
	}
	
	/**
	 * Ensures Visitor Messages are supported before commencing action on the table
	 *
	 * @return boolean or stop message
	 */
	function validate_vm()
	{
		global $db;
		$db->hide_errors();
		$result = $db->query_first_slave("SELECT max(vmid) total FROM " . TABLE_PREFIX . "visitormessage");
		$db->show_errors();

		if ($db->errno)
		{
			print_stop_message('automediaembed_no_vms');
		}
	
		return true;

	}
	
	/**
	 * Controller for validation of zones
	 *
	 * @param string $zone (blog, vm or group)
	 * @return boolean
	 */
	function validate_zone($zone)
	{
		
		switch ($zone)
		{
			case 'blog' 	: return validate_blog(); break;
			case 'group'	: return validate_group(); break;
			case 'vm' 		: return validate_vm(); break;
		}
		
		return false;
	}
	
    /**
     * Wrties $content to the $filename.php in the $path provided.
     *
     * @param string $path		directory to write to (should be chmodded 0777!)
     * @param string $filename	file name to write to (will overwrite contents)
     * @param string $content	content to write (should be full contents)
     */
    function ame_write_to_file($path, $filename, &$content)
    {
            if (is_dir($path))
            {
                    $fput = @fopen($path . "$filename.php", "w");
                    @fputs ($fput, $content);
                    @fclose($fput);
            }
    }

    /**
     * Creates textual version of an array
     *
     * @param array $array	array to convert
     * @param string $title	title of array element
     * @return string		textual content of array
     */
    function ame_write_array_entry($array, $title)
    {
            if (is_array($array))
            {
                    $return	= "$title = array(\n";
                    $return .= ame_write_array_sub($array,1);
                    $return .=");\n";
            }
            
            return $return;
    }

    /**
     * designed to be overloaded to walk nested arrays
     *
     * @param array $array		array to walk
     * @param int 	$depth		depth of array (for recursion)
     * @return string			textual representation of array and children
     */
    
    function ame_write_array_sub($array, $depth = 1)
    {
            $pre 	= str_pad("\t", $depth * 3);
            $return = "";

            foreach($array as $key => $value)
            {
                    if (is_array($value))
                    {
                            $return .= "$pre'$key' => array(\n";
                            $return .= ame_write_array_sub($value, ($depth + 1));
                            $return .= "$pre),\n";
                    }
                    else
                    {
                            $return .= "$pre'$key'\t\t\t=>'" . str_replace(array("\'", "'"), array("\\\'", "\'"), $value) . "',\n";
                    }
            }
            
            return $return;
    }	
	

/**
 * Print Header and Javascript helpers
 */
    
    if ($_REQUEST['do'] != "doexport")
	{
		print_cp_header($vbphrase['automediaembed_cp_title']);

		?>

		<script type="text/javascript">
			function grab_left(str, n)
		    {
		            if (n <= 0)
		            {
		                return "";
		            }
		            else if (n > String(str).length)
		            {
		                return str;
		            }
		            else
		            {
		                return String(str).substring(0,n);
		            }
		    }

		    function tick_all(formobj, type, value)
		    {
		            for (var i =0; i < formobj.elements.length; i++)
		            {
		                   var elm = formobj.elements[i];
		                   if (elm.type == "checkbox")
		                   {
		                         if (grab_left(elm.name,String(type).length) == type)
		                         {
		                             elm.checked = value;
		                         }
		                   }
		            }
		    }
		    
		    function ame_toggle_group(element_id)
		    {
		    	var obj = fetch_object('td_' + element_id);
		    	
		    	if (typeof obj != "undefined")
		    	{
		    		if (obj.style.display == "none")
		    		{
		    			obj.style.display = "block";
		    		}
		    		else
		    		{
		    			obj.style.display = "none";
		    		}
		    	}
		    	
		    	var obj = fetch_object('collapse_' + element_id);
		    	
		    	if (typeof obj != "undefined")
		    	{	
		    		if (obj.alt=="Collapse")
		    		{
		    			obj.src = "../cpstyles/<?=$vbulletin->options['cpstylefolder']?>/cp_collapse.gif";
		    			obj.alt = "Expand";
		    		}
		    		else
		    		{
		    			obj.src = "../cpstyles/<?=$vbulletin->options['cpstylefolder']?>/cp_expand.gif";		    			
		    			obj.alt = "Collapse";
		    		}
		    	}
		    }
		    
		</script>
		<?php

		/**
		 * Do error checking notification system
		 */
		
		if ($donotices)
		{			
			$dismissed 			= array();
			$notices 			= array();
			$dismissed_values 	= explode(",", $vbulletin->options['automediaembed_dismissed_notices']);
	
			if(sizeof($dismissed_values))
			{
				foreach($dismissed_values as $value)
				{
					if (trim($value))
					{
						$dismissed[trim($value)] = true;
					}
				}
			}			
	
			/**
			 * warn extraction is off
			 */
			if (!$vbulletin->options['automediaembed_resolve'] AND (!$dismissed['extract_off'] AND $_REQUEST['dismissid'] != 'extract_off'))
			{
				$notices['extract_off'] = $vbphrase['automediaembed_extraction_off'];
			}
			
			/**
			 * Warn for 0 sized dimensions of zones
			 */	
			$dims = array(
				'width'		=> array('post', 'other', 'blog', 'vm', 'group', 'sig'),
				'height'	=> array('post', 'other', 'blog', 'vm', 'group', 'sig'),
			);
	
		    foreach($dims as $dimkey => $dimvalue)
		    {
		    	foreach($dimvalue as $key)
		    	{
			    	$field = "automediaembed_$dimkey" . "_$key";
	
		    		if (!$vbulletin->options["$field"])
		    		{
		    			$notices['zone_' . $dimkey . '_off'] = $vbphrase['automediaembed_zone_' . $dimkey .'_off'];
		    		}
		    	}
		    }	
	
	
			/**
			 * Warn that file cache is off
			 */	
			if (!$vbulletin->options['automediaembed_cache'] AND (!$dismissed['cache_off'] AND $_REQUEST['dismissid'] != 'cache_off'))
			{
				$notices['cache_off'] = $vbphrase['automediaembed_cache_off'];
			}
			
			if (!$vbulletin->options['automediaembed_cache_path'])
			{
				$vbulletin->options['automediaembed_cache_path'] = DIR . "/amecache/";
				$db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value='" . $db->escape_string($vbulletin->options['automediaembed_cache_path']) . "' WHERE varname='automediaembed_cache_path' AND grouptitle='automediaembed_group'");
				
				build_options();				
			}
			
			
			/**
			 * Check validity and writeability of file cache
			 */					
			if ($vbulletin->options['automediaembed_cache'])
			{
				//######### Check path for ending slash
				if ($vbulletin->options['automediaembed_cache_path'])
				{
			    	if (strrpos($vbulletin->options['automediaembed_cache_path'], "/") != (strlen( $vbulletin->options['automediaembed_cache_path'] ) - 1))
			    	{
			    		if (strrpos($vbulletin->options['automediaembed_cache_path'], "\\") != (strlen( $vbulletin->options['automediaembed_cache_path'] )-1))
			    		{
			    			$vbulletin->options['automediaembed_cache_path'] .= '/';
			    			$db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value='" . $db->escape_string($vbulletin->options['automediaembed_cache_path']) . "' WHERE varname='automediaembed_cache_path' AND grouptitle='automediaembed_group'");
			    			build_options();
			    		}
	
			    	}
				}
	
				//########### Validate path and writeability of path
				if (!is_dir($vbulletin->options['automediaembed_cache_path']))
				{
					$notices[] = construct_phrase($vbphrase['automediaembed_bad_cache_path_x_y'], ($vbulletin->options['automediaembed_cache_path'] ? $vbulletin->options['automediaembed_cache_path'] : "blank"), DIR . "/amecache");
				}
				else
				{
					$content = "testing ames ability to write to the cache";
					ame_write_to_file($vbulletin->options['automediaembed_cache_path'], "test", $content);
	
					if (file_exists($vbulletin->options['automediaembed_cache_path'] . "/test.php"))
					{
						unlink($vbulletin->options['automediaembed_cache_path'] . "/test.php");
					}
					else
					{
						$notices[] = construct_phrase($vbphrase['automediaembed_cache_path_cant_write'], $vbulletin->options['automediaembed_cache_path']);
					}
				}
			}
	
			//########## DISPLAY NOTICES IF ANY ###############
			
			if (sizeof($notices))
			{
				print_form_header('automediaembed_admin', '', false, true, 'warningform');
				print_table_header($vbphrase['automediaembed_notice']);
				
				foreach($notices as $key => $value)
				{
					print_description_row($value);
					if (!is_numeric($key))
					{
						print_description_row("<div align=\"right\">" . construct_button_code($vbphrase['automediaembed_dismiss'], 'automediaembed_admin.php?do=dismiss&amp;dismissid=' . $key) . "</div>", false, 2, 'tcat');
					}
					else
					{
						print_description_row("&nbsp;", false, 2, 'tcat');
					}
				}
				
				print_table_footer();
			}
		}

	} 

	
	
	/**
	 * Dismiss notification
	 */
	if ($action == 'dismiss')
	{
		
		$dismissid = $vbulletin->input->clean_gpc('r', 'dismissid', TYPE_STR);
		$dismissed = ($vbulletin->options['automediaembed_dismissed_notices'] ? "," : "") . $dismissid;
		
		$db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value='" . $db->escape_string($dismissed) . "' WHERE varname='automediaembed_dismissed_notices' AND grouptitle='automediaembed_group'");
		build_options();
		
		redirect('settings', 'automediaembed_saved_settings');
	
	}


	/**
	 * Display settings
	 */
	if ($action == "settings")
	{

		$settings = array(
			'xforums'		=> explode(",", $vbulletin->options['automediaembed_forumids']),
		);

		//##### GLOBAL SETTINGS #####
		print_form_header('automediaembed_admin', 'savesettings');
		print_table_header($vbphrase['automediaembed_settings']);
		print_yes_no_row($vbphrase['automediaembed_disable_new'], 'disablenew', $vbulletin->options['automediaembed_disable']);
		print_yes_no_row($vbphrase['automediaembed_resolve'], 'resolve', $vbulletin->options['automediaembed_resolve']);
		print_input_row($vbphrase['automediaembed_limit'], 'limit', $vbulletin->options['automediaembed_limit']);

		//##### CACHE SETTINGS #####
		print_table_header($vbphrase['automediaembed_cache_settings']);
		print_yes_no_row($vbphrase['automediaembed_enable_cache'], 'enable_cache', $vbulletin->options['automediaembed_cache']);
		print_input_row($vbphrase['automediaembed_cache_path'], 'cache_path', $vbulletin->options['automediaembed_cache_path']);

		//##### OTHER ZONES (DEFAULT) #####
		print_table_header($vbphrase['automediaembed_zone_default_settings']);
		print_description_row($vbphrase['automediaembed_zone_desc_default']);
		print_input_row($vbphrase['automediaembed_default_width'], "width[other]", $vbulletin->options['automediaembed_width_other']);
		print_input_row($vbphrase['automediaembed_default_height'], "height[other]", $vbulletin->options['automediaembed_height_other']);
		print_input_row($vbphrase['automediaembed_default_template'], "template[other]", $vbulletin->options['automediaembed_template_other']);

		//##### POST ZONE (FORUMS) #####
		print_table_header($vbphrase['automediaembed_zone_post_settings']);
		print_description_row($vbphrase['automediaembed_zone_desc_post']);
		print_yes_no_row($vbphrase['automediaembed_doforums'], 'doforums', $vbulletin->options['automediaembed_doforums']);
		print_input_row($vbphrase['automediaembed_post_width'], "width[post]", $vbulletin->options['automediaembed_width_post']);
		print_input_row($vbphrase['automediaembed_post_height'], "height[post]", $vbulletin->options['automediaembed_height_post']);
		print_input_row($vbphrase['automediaembed_post_template'], "template[post]", $vbulletin->options['automediaembed_template_post']);
		print_forum_chooser($vbphrase['automediaembed_exempt_forums'], 'xforums[]', $settings['xforums'], null , false, true);

		//##### BLOG ZONE #####
		print_table_header($vbphrase['automediaembed_zone_blog_settings']);
		print_description_row($vbphrase['automediaembed_zone_desc_blog']);
		print_yes_no_row($vbphrase['automediaembed_doblogs'], 'doblogs', $vbulletin->options['automediaembed_doblogs']);
		print_input_row($vbphrase['automediaembed_blog_width'], "width[blog]", $vbulletin->options['automediaembed_width_blog']);
		print_input_row($vbphrase['automediaembed_blog_height'], "height[blog]", $vbulletin->options['automediaembed_height_blog']);
		print_input_row($vbphrase['automediaembed_blog_template'], "template[blog]", $vbulletin->options['automediaembed_template_blog']);

		//###### SOCIAL GROUP ZONE ######
		print_table_header($vbphrase['automediaembed_zone_group_settings']);
		print_description_row($vbphrase['automediaembed_zone_desc_group']);
		print_yes_no_row($vbphrase['automediaembed_dogroups'], 'dogroups', $vbulletin->options['automediaembed_dogroups']);
		print_input_row($vbphrase['automediaembed_group_width'], "width[group]", $vbulletin->options['automediaembed_width_group']);
		print_input_row($vbphrase['automediaembed_group_height'], "height[group]", $vbulletin->options['automediaembed_height_group']);
		print_input_row($vbphrase['automediaembed_group_template'], "template[group]", $vbulletin->options['automediaembed_template_group']);

		//##### VISITOR MESSAGE ZONE ####
		print_table_header($vbphrase['automediaembed_zone_vm_settings']);
		print_description_row($vbphrase['automediaembed_zone_desc_vm']);
		print_yes_no_row($vbphrase['automediaembed_dovms'], 'dovms', $vbulletin->options['automediaembed_dovms']);
		print_input_row($vbphrase['automediaembed_vm_width'], "width[vm]", $vbulletin->options['automediaembed_width_vm']);
		print_input_row($vbphrase['automediaembed_vm_height'], "height[vm]", $vbulletin->options['automediaembed_height_vm']);
		print_input_row($vbphrase['automediaembed_vm_template'], "template[vm]", $vbulletin->options['automediaembed_template_vm']);

		//##### SIGNATURE ZONE #####
		print_table_header($vbphrase['automediaembed_zone_sig_settings']);
		print_description_row($vbphrase['automediaembed_zone_desc_sig']);
		print_yes_no_row($vbphrase['automediaembed_dosigs'], 'dosigs', $vbulletin->options['automediaembed_dosigs']);
		print_input_row($vbphrase['automediaembed_sig_width'], "width[sig]", $vbulletin->options['automediaembed_width_sig']);
		print_input_row($vbphrase['automediaembed_sig_height'], "height[sig]", $vbulletin->options['automediaembed_height_sig']);
		print_input_row($vbphrase['automediaembed_sig_template'], "template[sig]", $vbulletin->options['automediaembed_template_sig']);

        print_submit_row();

	}


	/**
	 * Save Settings
	 */
	if ($action == "savesettings")
	{
		$vbulletin->input->clean_array_gpc('p', array(
			'xforums'			=> TYPE_ARRAY_UINT,
			'disablenew'		=> TYPE_BOOL,
			'doforums'			=> TYPE_BOOL,
			'doblogs'			=> TYPE_BOOL,
			'dogroups'			=> TYPE_BOOL,
			'dovms'				=> TYPE_BOOL,
			'dosigs'			=> TYPE_BOOL,
			'resolve'			=> TYPE_BOOL,
			'limit'				=> TYPE_UINT,
			'width'				=> TYPE_ARRAY_UINT,
			'height'			=> TYPE_ARRAY_UINT,
			'template'			=> TYPE_ARRAY_STR,
			'enable_cache'		=> TYPE_BOOL,
			'cache_path'		=> TYPE_STR,

		));

		if (is_array($vbulletin->GPC['xforums']))
		{
			foreach($vbulletin->GPC['xforums'] as $key => $value)
			{
				$forum_hint .= ($forum_hint ? "," : "") . "$value";
			}
		}

		$dims = array(
			'width'		=> $vbulletin->GPC['width'],
			'height'	=> $vbulletin->GPC['height'],
			'template'	=> $vbulletin->GPC['template'],
		);

		//##### Safety check for trailing slash. Man aren't we cautious! #####
		if ($vbulletin->GPC['cache_path'])
		{
	    	if (strrpos($vbulletin->GPC['cache_path'], "/") != (strlen($vbulletin->GPC['cache_path']) - 1))
	    	{
	    		if (strrpos($vbulletin->GPC['cache_path'], "\\") != (strlen($vbulletin->GPC['cache_path'])-1))
	    		{
	    			$vbulletin->GPC['cache_path'] .= '/';
	    		}
	    	}
		}
		
	    $db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value='$forum_hint' WHERE varname='automediaembed_forumids' AND grouptitle='automediaembed_group'");
	    $db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value='" . $vbulletin->GPC['disablenew'] . "' WHERE varname='automediaembed_disable' AND grouptitle='automediaembed_group'");
	    $db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value='" . $db->escape_string($vbulletin->GPC['limit']) . "' WHERE varname='automediaembed_limit' AND grouptitle='automediaembed_group'");
	    $db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value='" . $db->escape_string($vbulletin->GPC['resolve']) . "' WHERE varname='automediaembed_resolve' AND grouptitle='automediaembed_group'");
	    $db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value='" . $db->escape_string($vbulletin->GPC['doforums']) . "' WHERE varname='automediaembed_doforums' AND grouptitle='automediaembed_group'");
	    $db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value='" . $db->escape_string($vbulletin->GPC['doblogs']) . "' WHERE varname='automediaembed_doblogs' AND grouptitle='automediaembed_group'");
	    $db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value='" . $db->escape_string($vbulletin->GPC['dogroups']) . "' WHERE varname='automediaembed_dogroups' AND grouptitle='automediaembed_group'");
	    $db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value='" . $db->escape_string($vbulletin->GPC['dovms']) . "' WHERE varname='automediaembed_dovms' AND grouptitle='automediaembed_group'");
	    $db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value='" . $db->escape_string($vbulletin->GPC['dosigs']) . "' WHERE varname='automediaembed_dosigs' AND grouptitle='automediaembed_group'");
	    $db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value='" . $db->escape_string($vbulletin->GPC['enable_cache']) . "' WHERE varname='automediaembed_cache' AND grouptitle='automediaembed_group'");
	    $db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value='" . $db->escape_string($vbulletin->GPC['cache_path']) . "' WHERE varname='automediaembed_cache_path' AND grouptitle='automediaembed_group'");

	    foreach($dims as $dimkey => $dimvalue)
	    {
	    	foreach($dimvalue as $key => $value)
	    	{

		    	$field = "automediaembed_$dimkey" . "_$key";
	    		$db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value='" . $db->escape_string($value) . "' WHERE varname='$field' AND grouptitle='automediaembed_group'");
	    	}
	    }

	    build_options();
		ame_write_cache();
		
		redirect('settings', 'automediaembed_saved_settings');
	
	}

	/**
	 * Display Definitions
	 */
	if ($action == "display")
	{

		$errors = array();
		$duplicates = array();
		$keys = array();
		
		$results = $db->query_read_slave("SELECT id, title, ameid FROM " . TABLE_PREFIX . "automediaembed ORDER BY id ASC");

		while ($result = $db->fetch_array($results))
		{

			if (!$result['ameid'])
			{
				//Empty key warning
				$errors[] = construct_phrase($vbphrase['automediaembed_empty_key_x_y'], $result['id'], $result['title']);
			}
			elseif (!$keys["$result[ameid]"])
			{
				$keys["$result[ameid]"] = $result;
			}
			else
			{
				//Duplicate key found
				$duplicates["$result[ameid]"][] = $result['id'];
				$errors[] = construct_phrase($vbphrase['automediaembed_duplicate_key_x_y_z'], $result['id'], $result['title'], $keys["$result[ameid]"]['id'], $keys["$result[ameid]"]['title']);
			}
		}

		$results = $db->query_read_slave("SELECT id, title, ameid, displayorder, description, extraction, status, container from " . TABLE_PREFIX . "automediaembed ORDER BY displayorder, title ASC");

		print_form_header('automediaembed_admin', 'savedisplay');

		if ($errors)
		{
			print_table_header($vbphrase['errors']);
			
			foreach($errors as $value)
			{
				print_description_row($value);
			}
			
			print_table_break();
		}

		if ($db->num_rows($results))
		{
			print_table_header($vbphrase['automediaembed_media_definitions'], 7);
			print_cells_row(array($vbphrase['title'], $vbphrase['automediaembed_key'], $vbphrase['automediaembed_display_order'], "<label for=\"status_toggle\">$vbphrase[automediaembed_active]</label> <input type=\"checkbox\" id=\"status_toggle\" onclick=\"tick_all(this.form, 'status', this.checked)\" />", "<label for=\"container_toggle\">$vbphrase[automediaembed_contain]</label> <input type=\"checkbox\" id=\"container_toggle\" onclick=\"tick_all(this.form, 'container', this.checked)\" />", "<label for=\"extract_toggle\">$vbphrase[automediaembed_extract]</label> <input type=\"checkbox\" id=\"extract_toggle\" onclick=\"tick_all(this.form, 'extract', this.checked)\" />", "<label for=\"delete_toggle\">$vbphrase[delete]</label> <input type=\"checkbox\" id=\"delete_toggle\" onclick=\"tick_all(this.form, 'deleted', this.checked)\" />"), true);
			
			while($result = $db->fetch_array($results))
			{
				
				if ($duplicates["$result[ameid]"])
				{
					$ameid = "<strong><font color=\"red\">$vbphrase[automediaembed_duplicate]</font><strong>";
				}
				elseif (!$result["ameid"])
				{
					$ameid = "<strong><font color=\"red\">$vbphrase[automediembed_missing]</font><strong>";
				}
				else
				{
					$ameid = $result['ameid'];
				}

				construct_hidden_code("oldorder[$result[id]]", $result['displayorder']);
				construct_hidden_code("oldextract[$result[id]]", $result['extraction']);
				construct_hidden_code("oldstatus[$result[id]]", $result['status']);
				construct_hidden_code("oldcontainer[$result[id]]", $result['container']);
				
				print_cells_row(array(
					"<a href=\"automediaembed_admin.php?do=edit&id=$result[id]\">$result[title]</a><dfn>$result[description]</dfn>",
					$ameid,
					"<input type=\"input\" name=\"order[$result[id]]\" size=\"10\" value=\"$result[displayorder]\" />",
					"<input type=\"checkbox\" name=\"status[$result[id]]\" value=\"1\" " . ($result['status'] ? "checked=\"checked\"" : "") . " />",
					"<input type=\"checkbox\" name=\"container[$result[id]]\" value=\"1\" " . ($result['container'] ? "checked=\"checked\"" : "") . " />",
					"<input type=\"checkbox\" name=\"extract[$result[id]]\" value=\"1\" " . ($result['extraction'] ? "checked=\"checked\"" : "") . " />",
					"<input type=\"checkbox\" name=\"deleted[$result[id]]\" id=\"deleted_$result[id]\" value=\"1\" />"
				));
				
			}

			print_table_footer(7, construct_button_code($vbphrase['automediaembed_add_new'], "automediaembed_admin.php?do=edit") . " <input type=\"submit\" class=\"button\" tabindex=\"1\" value=\"$vbphrase[save]\" accesskey=\"s\" />");
		
		}
		else
		{

			print_table_header($vbphrase['automediaembed_media_definitions'], 2);
			print_description_row($vbphrase['automediaembed_no_definitions']);
			print_table_footer(2, construct_button_code($vbphrase['automediaembed_add_new'], "automediaembed_admin.php?do=edit"));
		
		}

	}

	/**
	 * Save Display
	 */
	if ($action == "savedisplay")
	{
		
		$vbulletin->input->clean_array_gpc('p', array(
			'order' 		=> TYPE_ARRAY_UINT,
			'oldorder' 		=> TYPE_ARRAY_UINT,
			'oldstatus'		=> TYPE_ARRAY_BOOL,
			'status'		=> TYPE_ARRAY_BOOL,
			'oldcontainer'	=> TYPE_ARRAY_BOOL,
			'container'		=> TYPE_ARRAY_BOOL,
			'oldextract'	=> TYPE_ARRAY_BOOL,
			'extract'		=> TYPE_ARRAY_BOOL,
			'deleted'		=> TYPE_ARRAY_BOOL,
			'confirmed'		=> TYPE_BOOL,
		));

		$order 			= $vbulletin->GPC['order'];
		$oldorder 		= $vbulletin->GPC['oldorder'];
		$oldstatus 		= $vbulletin->GPC['oldstatus'];
		$status 		= $vbulletin->GPC['status'];
		$oldcontainer 	= $vbulletin->GPC['oldcontainer'];
		$container 		= $vbulletin->GPC['container'];
		$oldextract 	= $vbulletin->GPC['oldextract'];
		$extract 		= $vbulletin->GPC['extract'];
		$deleted 		= $vbulletin->GPC['deleted'];
		$confirmed 		= $vbulletin->GPC['confirmed'];

		if (sizeof($deleted) AND !$confirmed)
		{
			$ids 		= "";
			$todelete 	= "";
			
			foreach($deleted as $key => $value)
			{
				$ids .= ($ids ? "," : "") . $key;
			}

			if ($ids)
			{
				$results = $db->query_read_slave("SELECT title FROM " . TABLE_PREFIX . "automediaembed where id in ($ids)");

				while($result = $db->fetch_array($results))
				{
					$todelete .= ($todelete ? ", " : "") . $result['title'];
				}
			}

			print_form_header('automediaembed_admin', 'savedisplay');
			
			foreach($oldorder as $key => $old)
			{
				construct_hidden_code("order[$key]", $order["$key"]);
				construct_hidden_code("oldorder[$key]", $oldorder["$key"]);
				construct_hidden_code("oldstatus[$key]", $oldstatus["$key"]);
				construct_hidden_code("status[$key]", $status["$key"]);
				construct_hidden_code("oldcontainer[$key]", $oldcontainer["$key"]);
				construct_hidden_code("container[$key]", $container["$key"]);
				construct_hidden_code("oldextract[$key]", $oldextract["$key"]);
				construct_hidden_code("extract[$key]", $extract["$key"]);
				construct_hidden_code("deleted[$key]", $deleted["$key"]);
			}
			
			construct_hidden_code("confirmed", "true");

			print_table_header($vbphrase['automediaembed_confirm_delete']);
			print_description_row(construct_phrase($vbphrase['automediaembed_confirm_delete_question'], $todelete));
			print_submit_row($vbphrase['yes'], '', 2, $vbphrase['no']);
		
		}
		else
		{
			
			$delete 	= "";

			if (!empty($oldorder))
			{
				$update		= array();
				
				foreach($oldorder as $key => $value)
				{
					
					if ($deleted["$key"])
					{
						$delete .= ($delete ? "," : "") . $key;
					}
					else
					{
						
						if ($value != $order["$key"])
						{
							$update["$key"]['displayorder'] = $order["$key"];
						}
						if ($oldstatus["$key"] != $status["$key"])
						{
							$update["$key"]['status'] = $status["$key"];
						}
						if ($oldcontainer["$key"] != $container["$key"])
						{
							$update["$key"]['container'] = $container["$key"];
						}
						if ($oldextract["$key"] != $extract["$key"])
						{
							$update["$key"]['extraction'] = $extract["$key"];
						}
					
					}
				}
			}

			if ($delete)
			{
				$db->query_write("DELETE FROM " . TABLE_PREFIX . "automediaembed WHERE id in ($delete)");
			}

			if (sizeof($update))
			{

				foreach($update as $id => $columns)
				{
					$columninfo = "";

					foreach ($columns as $column => $value)
					{
						$columninfo .= ($columninfo ? ", " : " SET ") . " $column = '$value'";
					}

					$db->query_write("UPDATE " . TABLE_PREFIX . "automediaembed $columninfo WHERE id=$id");
				}

			}
			
			ame_write_cache();
			
			redirect("display", "automediaembed_saved_display_order");

		}
	}

	/**
	 * Save Item
	 */
	if ($action == "save")
	{
		
		$vbulletin->input->clean_array_gpc('p', array(
			'id'			=> TYPE_UINT,
			'title'			=> TYPE_STR,
			'description'	=> TYPE_STR,
			'displayorder'	=> TYPE_UINT,
			'findcode'		=> TYPE_STR,
			'replacecode'	=> TYPE_STR,
			'status'		=> TYPE_BOOL,
			'container'		=> TYPE_BOOL,
			'embedregexp'	=> TYPE_STR,
			'validation'	=> TYPE_STR,
			'extraction'	=> TYPE_BOOL,
			'ameid'			=> TYPE_STR,
		));

		$id 			= $vbulletin->GPC['id'];
		$title 			= $vbulletin->GPC['title'];
		$description 	= $vbulletin->GPC['description'];
		$displayorder 	= $vbulletin->GPC['displayorder'];
		$findcode 		= $vbulletin->GPC['findcode'];
		$replacecode	= $vbulletin->GPC['replacecode'];
		$status			= $vbulletin->GPC['status'];
		$container		= $vbulletin->GPC['container'];
		$embedregexp	= $vbulletin->GPC['embedregexp'];
		$extraction		= $vbulletin->GPC['extraction'];
		$ameid			= $vbulletin->GPC['ameid'];
		$validation		= $vbulletin->GPC['validation'];

		$errors = array();
		$err_message = "";
		
		if (!preg_match('/\\A[A-Z0-9_-]+\\z/i', $ameid))
		{
			
			$errors[] = $vbphrase['automediaembed_key_contains_invalid_characters'];
		
		}
		else
		{
			
			$result = $db->query_first_slave("SELECT id, title FROM " . TABLE_PREFIX . "automediaembed WHERE ameid='" . $db->escape_string($ameid) . "'" . ($id ? " AND id != $id" : ""));
			
			if ($result['id'])
			{
				$errors[]  = construct_phrase($vbphrase['automediaembed_key_in_use_x_y_z'], $ameid, $result['id'], $result['title']);
			}
			
		}

		if (sizeof($errors))
		{
			
			foreach($errors as $value)
			{

				$err_message .= "<li>$value</li>";
			
			}

			print_stop_message('automediaembed_cant_save_errors', $title, $value);
		
		}


		//##### CONSTRUCT SQL. The intvals are a little OTT here. I just got bored and liked the formatting :P
		if ($id)
		{

			$sql = "UPDATE " . 		TABLE_PREFIX . "automediaembed SET
						title='" . 			$db->escape_string($title) . "',
						description = '" . 	$db->escape_string($description) . "',
						displayorder = '" . intval($displayorder) . "',
						findcode = '" . 	$db->escape_string($findcode) . "',
						replacecode = '" . 	$db->escape_string($replacecode) . "',
						status ='" . 		$status . "',
						container='" . 		intval($container) . "',
						embedregexp = '" . 	$db->escape_string($embedregexp) . "',
						extraction = '" . 	intval($extraction) . "',
						ameid = '" . 		$db->escape_string($ameid) . "',
						validation = '" . 	$db->escape_string($validation) . "'
					WHERE id = " . 		intval($id);
			
		}
		else
		{
			
			$sql = "INSERT INTO " . TABLE_PREFIX . "automediaembed (
						title,
						description,
						displayorder,
						findcode, 
						replacecode,
						status,
						container,
						embedregexp,
						extraction, 
						ameid,
						validation) 
					VALUES (
						'" . $db->escape_string($title) . "',
						'" . $db->escape_string($description) . "',
						'" . intval($displayorder) . "',
						'" . $db->escape_string($findcode) . "',
						'" . $db->escape_string($replacecode) . "', 
						'" . intval($status) . "', 
						'" . intval($container) . "', 
						'" . $db->escape_string($embedregexp) . "',
						'" . intval($extraction) . "',
						'" . $db->escape_string($ameid) . "',
						'" . $db->escape_string($validation) . "')";
		
		}

		if ($sql)
		{

			$db->query_write($sql);
			
		}
		
		ame_write_cache();
		
		redirect("display", "automediaembed_saved_x", $title);
	}

	/**
	 * Edit Item
	 */
	if ($action == "edit")
	{
		
		$id = $vbulletin->input->clean_gpc('r', 'id', TYPE_UINT);

		print_form_header('automediaembed_admin', 'save');
		
		if ($errors)
		{
			print_table_header($vbphrase['errors']);
			
			foreach($errors as $value)
			{
				
				print_description_row($value);
			
			}
			
			print_table_break();
		}

		//Global Settings
		if ($id)
		{
			
			$result = $db->query_first_slave("SELECT * FROM " . TABLE_PREFIX . "automediaembed WHERE id=$id");
			construct_hidden_code("id", $id);
			print_table_header($result['title']);
		
		}
		else
		{
			
			print_table_header($vbphrase['automediaembed_new_definition']);
		
		}

		print_input_row($vbphrase['title'], "title", $result['title']);
		print_input_row($vbphrase['description'], "description", $result['description'], true, 35, 255);
		print_input_row($vbphrase['automediaembed_unique_key'], "ameid", $result['ameid']);
		print_input_row($vbphrase['automediaembed_display_order'], "displayorder", $result['displayorder']);
		print_yes_no_row($vbphrase['automediaembed_active_desc'], 'status', $result['status']);
		print_yes_no_row($vbphrase['automediaembed_contain_desc'], 'container', $result['container']);
		
		//Definition
		print_table_header($vbphrase['automediaembed_code']);
		print_textarea_row($vbphrase['automediaembed_search'], "findcode", $result['findcode']);
		print_textarea_row($vbphrase['automediaembed_replace'], "replacecode", $result['replacecode']);
		
		//Extraction definition
		print_table_header($vbphrase['automediaembed_extraction_info']);
		print_yes_no_row($vbphrase['automediaembed_extraction'], "extraction", $result['extraction']);
		print_textarea_row($vbphrase['automediaembed_embedregexp'], "embedregexp", $result['embedregexp']);
		print_textarea_row($vbphrase['automediaembed_validation'], "validation", $result['validation']);

		print_submit_row();
	
	}

	/**
	 * Delete Item
	 */
	if ($action == 'delete')
	{

		$id = $vbulletin->input->clean_gpc('r', 'id' , TYPE_UINT);

		if ($id)
		{
			$result = $db->query_first_slave("SELECT title FROM " . TABLE_PREFIX . "automediaembed  WHERE id=$id");
	
			print_form_header('automediaembed_admin', 'kill');
			construct_hidden_code('id', $id);
			print_table_header($vbphrase['automediaembed_confirm_delete']);
			print_description_row(construct_phrase($vbphrase['automediaembed_confirm_delete_question'], $result['title']));
			print_submit_row($vbphrase['yes'], '', 2, $vbphrase['no']);

		}
		
	}


	/**
	 * Kill Item
	 */
	if ($action == 'kill')
	{

		$id = $vbulletin->input->clean_gpc('r', 'id', TYPE_UINT);
		
		if ($id)
		{
			$result = $db->query_first_slave("SELECT title FROM " . TABLE_PREFIX . "automediaembed WHERE id = $id");
			$db->query_write("DELETE FROM " . TABLE_PREFIX . "automediaembed WHERE id=$id");
		}
		
		ame_write_cache();
		
		redirect('display', 'automediaembed_deleted_x', $result['title']);
	
	}

	/**
	 * Tools Menu
	 */
	if ($action == 'tools')
	{
		
		print_form_header();
		print_table_header($vbphrase['automediaembed_tools']);
		print_description_row("<a href=\"automediaembed_admin.php?do=rebuildcache\">$vbphrase[automediaembed_rebuild_cache_title]</a><dfn>$vbphrase[automediaembed_rebuild_cache_desc]</dfn>");
		
		print_table_header($vbphrase['automediaembed_rebuild_and_convert']);
		print_description_row("<strong>$vbphrase[automediaembed_rebuild_title]</strong><dfn>$vbphrase[automediaembed_rebuild_desc]</dfn>");
		print_description_row("<a href=\"automediaembed_admin.php?do=convertposts\">$vbphrase[automediaembed_convert_title]</a><dfn>$vbphrase[automediaembed_convert_desc]</dfn>");
		
		
		print_table_header($vbphrase['automediaembed_upgrade_flags']);
		print_description_row("<a href=\"automediaembed_admin.php?do=addblog\">$vbphrase[automediaembed_addblog_title]</a><dfn>$vbphrase[automediaembed_addblog_desc]</dfn>");
		print_description_row("<a href=\"automediaembed_admin.php?do=addgroups\">$vbphrase[automediaembed_addgroup_title]</a><dfn>$vbphrase[automediaembed_addgroup_desc]</dfn>");
		print_description_row("<a href=\"automediaembed_admin.php?do=addvms\">$vbphrase[automediaembed_addvms_title]</a><dfn>$vbphrase[automediaembed_addvms_desc]</dfn>");
		print_table_footer();
	
	}

	
	/**
	 * Rebuilds and resyncs the file cache
	 */
	if ($action == 'rebuildcache')
	{
		ame_write_cache();		
		redirect('settings', 'automediaembed_saved_settings');		
	}
	
	
	/**
	 * ADD AME_FLAG TO VBBLOG TABLE
	 * Primarily used if AME was installed before vbBlog.
	 */
	if ($action == 'addblog')
	{
		
			$db->hide_errors();
			$result = $db->query_first_slave("SELECT max(blogtextid) total FROM " . TABLE_PREFIX . "blog_text");
			$db->show_errors();

			if ($db->errno)
			{
				print_stop_message('automediaembed_no_blog');
			}

			$db->hide_errors();
			$db->query_write("ALTER TABLE " . TABLE_PREFIX . "blog_text ADD ame_flag TINYINT DEFAULT '0' NOT NULL ;");
			$db->show_errors();

			redirect('display', 'automediaembed_addedblogs');
			
	}
	
	/**
	 * ADD AME_FLAG TO SOCIAL GROUPS TABLE
	 * Used is AME was isntalled on a pre-3.7 system
	 */
	if ($action == 'addgroups')
	{
		
			$db->hide_errors();
			$result = $db->query_first_slave("SELECT max(gmid) total FROM " . TABLE_PREFIX . "groupmessage");
			$db->show_errors();

			if ($db->errno)
			{
				print_stop_message('automediaembed_no_group');
			}

			$db->hide_errors();
			$db->query_write("ALTER TABLE " . TABLE_PREFIX . "groupmessage ADD ame_flag TINYINT DEFAULT '0' NOT NULL ;");
			$db->show_errors();

			redirect('display', 'automediaembed_addedgroups');
	
	}

	/**
	 * ADD AME_FLAG TO VISITOR MESSAGES TABLE
	 * Used if AME was installed on system before it was upgraded to 3.7
	 */
	if ($action == 'addvms')
	{
		
			$db->hide_errors();
			$result = $db->query_first_slave("SELECT max(vmid) total FROM " . TABLE_PREFIX . "visitormessage");
			$db->show_errors();

			if ($db->errno)
			{
				print_stop_message('automediaembed_no_vms');
			}

			$db->hide_errors();
			$db->query_write("ALTER TABLE " . TABLE_PREFIX . "visitormessage ADD ame_flag TINYINT DEFAULT '0' NOT NULL ;");
			$db->show_errors();

			redirect('display', 'automediaembed_addedvms');
	
	}

	/**
	 * REBUILD TOOL
	 * Designed to rebuild posts, blogs, groups, visitor messages and signatures with AME tags
	 * Also used to restore AME tags back to standard URL tags
	 */
	if($action == 'rebuild')
	{
		$zone 	= $vbulletin->input->clean_gpc('r', 'zone', TYPE_STR);
		
		//check for valid zone
		switch ($zone)
		{
			
			case 'post':
			case 'blog':
			case 'vm':
			case 'group':
			case 'sig':
				break;
			default:
				print_stop_message('automediaembed_invalid_zone_specified');
				
		}
		
		
		if ($zone == 'blogs')
		{
			
			$db->hide_errors();
			$result = $db->query_first_slave("SELECT max(blogtextid) total FROM " . TABLE_PREFIX . "blog_text");
			$db->show_errors();

			if ($db->errno)
			{
				
				print_stop_message('automediaembed_no_blog');
			
			}
			
		}		
		elseif ($zone == 'groups')
		{
			
			$db->hide_errors();
			$result = $db->query_first_slave("SELECT max(gmid) total FROM " . TABLE_PREFIX . "groupmessage");
			$db->show_errors();

			if ($db->errno)
			{
				
				print_stop_message('automediaembed_no_group');
			
			}
			
		}
		elseif ($zone == 'vms')
		{
			
			$db->hide_errors();
			$result = $db->query_first_slave("SELECT max(vmid) total FROM " . TABLE_PREFIX . "visitormessage");
			$db->show_errors();

			if ($db->errno)
			{
				
				print_stop_message('automediaembed_no_vms');
			
			}
		
		}


		$lengths = array(

			0			=> $vbphrase['automediaembed_length_all'],
	    	604800		=> $vbphrase['automediaembed_length_one_week'],
	    	1209600		=> $vbphrase['automediaembed_length_two_weeks'],
	    	1814400		=> $vbphrase['automediaembed_length_three_weeks'],
	    	2419200		=> $vbphrase['automediaembed_length_one_month'],
	    	7862400		=> $vbphrase['automediaembed_length_three_months'],
	    	15724800	=> $vbphrase['automediaembed_length_six_months'],
	    	31449600	=> $vbphrase['automediaembed_length_one_year'],
	    
	    	);

	    	
	    $settings = unserialize($vbulletin->AME_settings);

	    if (!$settings)
	    {
	    	//Defaults
	    	$settings = array(
	    		'length'	=> 	2419200,
	    		'perpage'	=>	100,
	    		'seconds'	=>	10,
	    	);
	    	
	    }

		print_form_header('automediaembed_admin', 'dorebuild', false, true, 'cpform', '90%', '', true, 'get');
		print_table_header($vbphrase['automediaembed_convert_warning_title']);

		switch ($zone)
		{
			
			case 'blog' 	: $tablename = "blog_text"; break;
			case 'vm'		: $tablename = "visitormessage"; break;
			case 'group'	: $tablename = "groupmessage"; break;
			case 'sig'		: $tablename = "usertextfield"; break;
			default			: $tablename = "post";
		
		}

		print_description_row(construct_phrase($vbphrase["automediaembed_convert_warning_x"], $tablename));

		print_table_break();
		print_table_header($vbphrase['automediaembed_rebuild_title_' . $zone]);
		print_yes_no_row($vbphrase['automediaembed_remove_ame'], 'deleteame', false);
		print_yes_no_row($vbphrase['automediaembed_ignore_previous'], 'ignoreprevious', true);
		print_yes_no_row($vbphrase['automediaembed_test_mode'], 'test', $settings['test']);
		print_yes_no_row($vbphrase['automediaembed_verbose_mode'], 'verbose', $settings['verbose']);
		
		if ($zone != "sig")
		{
			
			print_select_row($vbphrase['automediaembed_length'], 'length', $lengths, $settings['length']);
		
		}
		
		print_input_row($vbphrase['automediaembed_perpage'], 'perpage', $settings['perpage']);
		print_input_row($vbphrase['automediaembed_seconds_perpage'], 'seconds', $settings['seconds']);
		construct_hidden_code('zone', $zone);
		
		print_submit_row();

	}

	/**
	 * DO ACTUAL REBUILD
	 */
	if ($action == 'dorebuild')
	{
		
		$vbulletin->input->clean_array_gpc('r', array(
			'ignoreprevious'		=> TYPE_BOOL,
			'length'				=> TYPE_UINT,
			'deleteame'				=> TYPE_BOOL,
			'perpage'				=> TYPE_UINT,
			'seconds'				=> TYPE_UINT,
			'cont'					=> TYPE_UINT,
			'test'					=> TYPE_BOOL,
			'verbose'				=> TYPE_BOOL,
			'start'					=> TYPE_UINT,
			'zone'					=> TYPE_STR
		));

		$length 			= $vbulletin->GPC['length'];
		$start				= $vbulletin->GPC['start'];
		$perpage 			= $vbulletin->GPC['perpage'];
		$seconds 			= $vbulletin->GPC['seconds'];
		$ignoreprevious		= $vbulletin->GPC['ignoreprevious'];
		$deleteame			= $vbulletin->GPC['deleteame'];
		$cont				= $vbulletin->GPC['cont'];
		$test				= $vbulletin->GPC['test'];
		$verbose			= $vbulletin->GPC['verbose'];
		$zone				= $vbulletin->GPC['zone'];

		//check for valid zone
		switch ($zone)
		{
			
			case 'post':
			case 'blog':
			case 'vm':
			case 'group':
			case 'sig':
				break;
			default:
				print_stop_message('automediaembed_invalid_zone_specified');
				
		}

		//quick validation check
		validate_zone($zone);

		if ($start < 2 && !$cont)
		{
			
			$settings 	= unserialize($vbulletin->AME_settings);
			$start 		= 0;
			$limitstart = "0";
			build_datastore('AME_settings', serialize(array('length' => $length, 'perpage' => $perpage, 'seconds' => $seconds, 'verbose' => $verbose, 'test' => $test, 'codes' => $settings['codes'], 'conversions' => $settings['conversions'])));
		
		}
		else
		{
			
			$limitstart = $start * $perpage;
		
		}

		$return 		= false;
		$x				= 0;

		require_once(DIR . "/includes/ame_bbcode.php");

		if ($length)
		{
			
			switch ($zone)
			{
				case 'post': 	$and .= " AND p.dateline >= $length ";	break;
				case 'blog':
				case 'group':
				case 'vm':		$and .= " AND dateline >= $length ";	break;
			}
			
		}

		switch ($zone)
		{
			case 'vm':

				$sql = "SELECT	count(vmid) total FROM " . TABLE_PREFIX . "visitormessage WHERE 1=1  AND (pagetext LIKE '%[/url]%' OR pagetext LIKE '%[/ame]%' OR pagetext LIKE '%[/nomedia]%') $and ";
				break;
			
			case 'blog':
				
				$sql = "SELECT count(blogtextid) total FROM " . TABLE_PREFIX . "blog_text WHERE 1=1  AND (pagetext LIKE '%[/url]%' OR pagetext LIKE '%[/ame]%' OR pagetext LIKE '%[/nomedia]%') $and ";
				break;
				
			case 'group':
				
				$sql = "SELECT count(gmid) total FROM " . TABLE_PREFIX . "groupmessage WHERE 1=1  AND (pagetext LIKE '%[/url]%' OR pagetext LIKE '%[/ame]%' OR pagetext LIKE '%[/nomedia]%') $and ";
				break;
				
			case 'sig':
				
				$sql = "SELECT count(userid) total FROM " . TABLE_PREFIX . "usertextfield WHERE 1=1  AND (signature LIKE '%[/url]%' OR signature LIKE '%[/ame]%' OR signature LIKE '%[/nomedia]%') $and ";
				break;
				
			default:
				
				$sql = "SELECT count(p.postid) total FROM " . TABLE_PREFIX . "post p WHERE 1=1  AND (p.pagetext LIKE '%[/url]%' OR p.pagetext LIKE '%[/ame]%' OR pagetext LIKE '%[/nomedia]%') $and ";
		}

		$postcount = $db->query_first_slave($sql);

		if ($postcount['total'])
		{
			
			print_form_header('automediaembed_admin', 'dorebuild', false, true, 'statusform', '90%', '', true, 'get');
			print_table_header($vbphrase['automediaembed_rebuild_status']);
			print_description_row(construct_phrase($vbphrase['automediaembed_rebuild_status_x'], (ceil($postcount['total'] / $perpage) - ($start ? $start + 1 : 1))));
			print_table_footer(); vbflush();

			switch ($zone)
			{
				case 'vm':
					
					$sql = "SELECT 
								vmid, 
								pagetext, 
								ame_flag  
							FROM " . TABLE_PREFIX . "visitormessage
							WHERE 
								1=1 AND 
								(pagetext LIKE '%[/url]%' OR pagetext LIKE '%[/ame]%' OR pagetext LIKE '%[/nomedia]%') 
								$and 
							ORDER BY 
								dateline DESC 
							LIMIT $limitstart, $perpage";
					break;
					
				case 'blog':
					
					$sql = "SELECT
								blogtextid,
								pagetext,
								ame_flag  
							FROM " . TABLE_PREFIX . "blog_text
							WHERE 
								1=1 AND 
								(pagetext LIKE '%[/url]%' OR pagetext LIKE '%[/ame]%' OR pagetext LIKE '%[/nomedia]%') 
								$and 
							ORDER BY dateline DESC 
							LIMIT $limitstart, $perpage";
					break;
					
				case 'group':
					
					$sql = "SELECT 
								gmid, 
								pagetext,
								ame_flag  
							FROM " . TABLE_PREFIX . "groupmessage
							WHERE 
								1=1 AND 
								(pagetext LIKE '%[/url]%' OR pagetext LIKE '%[/ame]%' OR pagetext LIKE '%[/nomedia]%')
								$and 
							ORDER BY dateline DESC 
							LIMIT $limitstart, $perpage";
					break;
					
				case 'sig':
					
					$sql = "SELECT
								userid,
								signature pagetext 
							FROM " . TABLE_PREFIX . "usertextfield
							WHERE
								1=1 AND 
								(signature LIKE '%[/url]%' OR signature LIKE '%[/ame]%' OR signature LIKE '%[/nomedia]%')
								$and 
							ORDER BY 
								userid DESC 
							LIMIT $limitstart, $perpage";
					break;
					
				default:
					
					$sql = "SELECT 
								p.postid, 
								p.pagetext,
								p.ame_flag,
								t.forumid 
							FROM " . TABLE_PREFIX . "post p 
							INNER JOIN " . TABLE_PREFIX . "thread t on p.threadid = t.threadid
							WHERE 
								1=1 AND 
								(p.pagetext LIKE '%[/url]%' OR p.pagetext LIKE '%[/ame]%' OR pagetext LIKE '%[/nomedia]%')
								$and 
							ORDER BY
								p.dateline DESC 
							LIMIT $limitstart, $perpage";
			}

			$results = $db->query_read_slave($sql);

			define('AME_SKIP_PREM_CHECK', true);

			if ($db->num_rows($results))
			{
				
				echo("Building....<ul>");
				vbflush();

				while($result = $db->fetch_array($results))
				{
					
					$forumid = $result['forumid'];
					$x++;

					if ($x == $perpage)
					{
						
						$return = true;
						
					}

					switch ($zone)
					{
						
						case 'vm':
							echo("<li>Visitor Message $result[vmid]: ");
							break;
							
						case 'blog':
							echo("<li>Blog/Comment $result[blogtextid]: ");
							break;
							
						case 'group':
							echo("<li>Group Message $result[gmid]: ");
							break;
							
						case 'sig':
							echo("<li>Signature for userid $result[userid]: ");
							break;

						default:
							echo("<li>post $result[postid]: ");
					
					}

					if (!$deleteame)
					{
						
						if ($ignoreprevious && !$result['ame_flag'])
						{
							
							if ($verbose)
							{
								
								$text = $result['pagetext'];
							
							}

							$returnvalue = ame_prep_text($result['pagetext']);
							
						}
						else
						{
							
							$returnvalue = 0;
							
						}
						
						if ($returnvalue)
						{

							if (!$test)
							{
								
								switch ($zone)
								{
									
									case 'vm':
										$sql = "UPDATE " . TABLE_PREFIX . "visitormessage SET pagetext = '" . $db->escape_string($result['pagetext']) . "' ,ame_flag = $returnvalue WHERE vmid=$result[vmid]";
										$db->query_write($sql);
										break;
										
									case 'blog':
										$sql = "UPDATE " . TABLE_PREFIX . "blog_text SET pagetext = '" . $db->escape_string($result['pagetext']) . "' ,ame_flag = $returnvalue WHERE blogtextid=$result[blogtextid]";
										$db->query_write($sql);
										$db->query_write("DELETE FROM " . TABLE_PREFIX . "blog_textparsed WHERE blogtextid=$result[blogtextid]");
										break;
										
									case 'group':
										$sql = "UPDATE " . TABLE_PREFIX . "groupmessage SET pagetext = '" . $db->escape_string($result['pagetext']) . "' ,ame_flag = $returnvalue WHERE gmid=$result[gmid]";
										$db->query_write($sql);
										break;
										
									case 'sig':
										$sql = "UPDATE " . TABLE_PREFIX . "usertextfield SET signature = '" . $db->escape_string($result['pagetext']) . "' WHERE userid=$result[userid]";
										$db->query_write($sql);
										$db->query_write("DELETE FROM " . TABLE_PREFIX . "sigparsed WHERE userid=$result[userid]");
										break;
										
									default:
										$sql = "UPDATE " . TABLE_PREFIX . "post SET pagetext = '" . $db->escape_string($result['pagetext']) . "' ,ame_flag = $returnvalue WHERE postid=$result[postid]";
										$db->query_write($sql);
										$db->query_write("DELETE FROM " . TABLE_PREFIX . "postparsed WHERE postid=$result[postid]");
										
								}
							
							}

							if ($verbose)
							{
								
								echo("<div style=\"border: medium;\">   was:<hr>" . htmlspecialchars_uni($text) . "<hr><br />it is now:<hr>" . htmlspecialchars_uni($result['pagetext']) . "<hr></div>");
							
							}
						
						}
						else
						{
							
							if (!$ignoreprevious && $result['ame_flag'])
							{
								
								switch ($zone)
								{
									
									case 'vm':
										$db->query_write("UPDATE " . TABLE_PREFIX . "visitormessage SET ame_flag = 0  WHERE vmid=$result[vmid]");
										break;
										
									case 'blog':
										$db->query_write("UPDATE " . TABLE_PREFIX . "blog_text SET ame_flag = 0  WHERE blogtextid=$result[blogtextid]");
										break;
										
									case 'group':
										$db->query_write("UPDATE " . TABLE_PREFIX . "groupmessage SET ame_flag = 0  WHERE gmid=$result[gmid]");
										break;
										
									case 'sig':
										break;
										
									default:
										$db->query_write("UPDATE " . TABLE_PREFIX . "post SET ame_flag = 0  WHERE postid=$result[postid]");
								
								}
							
							}
							else if ($ignoreprevious && $result['ame_flag'])
							{
								
								echo("Ignored (already contains ame)");
							
							}
							else
							{
								
								echo("not changed");
							
							}
						
						}

					}
					else
					{
						
						$text = str_replace(array("[ame", "[/ame]", "[nomedia", "[/nomedia]"), array("[url", "[/url]", "[url", "[/url]"), $result['pagetext']);

						if (($text != $result['pagetext']) && $text)
						{
							
							echo("updated");
							
							if (!$test)
							{
								
								switch ($zone)
								{
									
									case 'vm':
										$db->query_write("UPDATE " . TABLE_PREFIX . "visitormessage SET pagetext = '" . $db->escape_string($text) . "', ame_flag = 0  WHERE vmid=$result[vmid]");
										break;
										
									case 'blog':
										$db->query_write("UPDATE " . TABLE_PREFIX . "blog_text SET pagetext = '" . $db->escape_string($text) . "', ame_flag = 0  WHERE blogtextid=$result[blogtextid]");
										$db->query_write("DELETE FROM " . TABLE_PREFIX . "blog_textparsed WHERE blogtextid=$result[blogtextid]");
										break;
										
									case 'group':
										$db->query_write("UPDATE " . TABLE_PREFIX . "groupmessage SET pagetext = '" . $db->escape_string($text) . "', ame_flag = 0  WHERE gmid=$result[gmid]");
										break;
										
									case 'sig':
										
										$db->query_write("UPDATE " . TABLE_PREFIX . "usertextfield SET signature = '" . $db->escape_string($text) . "' WHERE userid=$result[userid]");
										$db->query_write("DELETE FROM " . TABLE_PREFIX . "sigparsed WHERE userid=$result[userid]");
										break;
										
									default:
										$db->query_write("UPDATE " . TABLE_PREFIX . "post SET pagetext = '" . $db->escape_string($text) . "', ame_flag = 0  WHERE postid=$result[postid]");
										$db->query_write("DELETE FROM " . TABLE_PREFIX . "postparsed WHERE postid=$result[postid]");
								
								}
							
							}

							if ($verbose)
							{
								
								echo("<div style=\"border: medium;\">   was:<hr>" . htmlspecialchars_uni($result['pagetext']) . "<hr><br />it is now:<hr>" . htmlspecialchars_uni($text) . "<hr></div>");
							
							}
						
						}
						else
						{
							
							switch ($zone)
							{
								
								case 'vm':
									$db->query_write("UPDATE " . TABLE_PREFIX . "visitormessage SET ame_flag = 0  WHERE vmid=$result[vmid]");
									break;
									
								case 'blog':
									$db->query_write("UPDATE " . TABLE_PREFIX . "blog_text SET ame_flag = 0  WHERE blogtextid=$result[blogtextid]");
									break;
									
								case 'group':
									$db->query_write("UPDATE " . TABLE_PREFIX . "groupmessage SET ame_flag = 0  WHERE gmid=$result[gmid]");
									break;
									
								case 'sig':
									break;
									
								default:
									$db->query_write("UPDATE " . TABLE_PREFIX . "post SET ame_flag = 0  WHERE postid=$result[postid]");
							
							}

							echo("not changed");
						
						}
					
					}

					echo("</li>");
					vbflush();
					unset($text);

					if ($return)
					{
						
						if (ceil($postcount['total'] / $perpage) == 1)
						{
							
							$return = false;
						
						}
					
					}
				
				}
				
				echo("</ul>");

			}
			else
			{
				
				redirect('tools', 'automediaembed_no_results');

			}
		}
		else
		{
			
			redirect('tools', 'automediaembed_no_results');

		}

		print_form_header('automediaembed_admin', 'dorebuild', false, true, 'cpform', '90%', '', true, 'get');
		print_table_header($vbphrase['automediaembed_rebuild_title']);

		if ($return)
		{
			
			print_label_row($vbphrase['automediaembed_rebuild_seconds_till_next'], "<input type=\"text\" name=\"timer\" id=\"timer\" readonly=\"true\" value=\"$delay\" />");
			construct_hidden_code("cont", true);
			construct_hidden_code("ignoreprevious", $ignoreprevious);
			construct_hidden_code("deleteame", $deleteame);
			construct_hidden_code("perpage", $perpage);
			construct_hidden_code("seconds", $seconds);
			construct_hidden_code("length", $length);
			construct_hidden_code("test", $test);
			construct_hidden_code("verbose", $verbose);
			construct_hidden_code("start", $start+1);
			construct_hidden_code("zone", $zone);

			print_submit_row($vbphrase['next'], '');


			echo("<script language=\"javascript\"><!--

					var countdown = " . $seconds . ";

				  function submit_form()
				  {
				     document.cpform.submit();
				  }

				  function count_down()
				  {
				      countdown = countdown-1;
				  	  document.cpform.timer.value=countdown+ ' $vbphrase[automediaembed_rebuild_seconds_remaining]';
				  	  if (countdown == 0)
				  	  {
				  	  	submit_form();
				  	  }
				  	  else
				  	  {
				  	  	setTimeout('count_down()',1000);
				  	  }
				  }
				  //-->
				setTimeout('count_down()',1000);
			  </script>");

		
		}
		else
		{
			
			$inp = ($in ? $in . "_" : "");
			print_description_row($vbphrase['automediaembed_rebuild_completed']);
			print_table_footer();
			
		}


	}

	/**
	 * Convert othe 'media' like tags in posts to url tags.
	 */
	if($action == 'convertposts')
	{

	    $lengths = array(
	    	0			=> $vbphrase['automediaembed_length_all'],
	    	604800		=> $vbphrase['automediaembed_length_one_week'],
	    	1209600		=> $vbphrase['automediaembed_length_two_weeks'],
	    	1814400		=> $vbphrase['automediaembed_length_three_weeks'],
	    	2419200		=> $vbphrase['automediaembed_length_one_month'],
	    	7862400		=> $vbphrase['automediaembed_length_three_months'],
	    	15724800	=> $vbphrase['automediaembed_length_six_months'],
	    	31449600	=> $vbphrase['automediaembed_length_one_year'],
	    );

	    if (!$settings)
	    {
	    	$settings = array(
	    		'length'	=> 	2419200,
	    		'perpage'	=>	100,
	    		'seconds'	=>	10,
	    	);

	    	if (!isset($settings['codes']))
	    	{
	    		$settings['codes'] 			= 'youtubevid, metacafe, ifilm, putfile, googlevid, myspacevid';
	    		$settings['conversions'] 	= 'http://www.youtube.com/watch?v=\1, http://www.metacafe.com/watch/\1, http://www.ifilm.com/video/\1, http://www.putfile\.com/\1, http://www.google.com/videoplay\?docid=\1, http://www.myspace.com/index.cfm?fuseaction=vids.individual&videoid=\1';
	    	}
	    	
	    }

		print_form_header('automediaembed_admin', 'doconversion', false, true, 'cpform', '90%', '', true, 'get');

		print_table_header($vbphrase['automediaembed_convert_warning_title']);
		print_description_row($vbphrase['automediaembed_convert_warning_desc']);
		print_checkbox_row($vbphrase['automediaembed_convert_warning'], 'ok', false);
		print_table_break();
		
		print_table_header($vbphrase['automediaembed_convert_title']);
		print_textarea_row($vbphrase['automediaembed_codes'], 'codes', $settings['codes']);
		print_textarea_row($vbphrase['automediaembed_conversions'], 'conversions', $settings['conversions']);
		print_yes_no_row($vbphrase['automediaembed_test_mode'], "test", true);
		print_yes_no_row($vbphrase['automediaembed_verbose_mode'], "verbose", false);
		print_select_row($vbphrase['automediaembed_length'], 'length', $lengths, $settings['length']);
		print_input_row($vbphrase['automediaembed_perpage'], 'perpage', $settings['perpage']);
		print_input_row($vbphrase['automediaembed_seconds_perpage'], 'seconds', $settings['seconds']);
		print_submit_row();

	}

	/**
	 * Do The Conversion (sounds like a dance!)
	 */
	if ($action == 'doconversion')
	{
		
		$vbulletin->input->clean_array_gpc('r', array(
			'length'				=> TYPE_UINT,
			'deleteame'				=> TYPE_BOOL,
			'perpage'				=> TYPE_UINT,
			'seconds'				=> TYPE_UINT,
			'cont'					=> TYPE_UINT,
			'conversions'			=> TYPE_STR,
			'codes'					=> TYPE_STR,
			'test'					=> TYPE_BOOL,
			'verbose'				=> TYPE_BOOL,
		));

		$length 			= $vbulletin->GPC['length'];
		$start				= $vbulletin->GPC['start'];
		$perpage 			= $vbulletin->GPC['perpage'];
		$seconds 			= $vbulletin->GPC['seconds'];
		$ignoreprevious		= $vbulletin->GPC['ignoreprevious'];
		$deleteame			= $vbulletin->GPC['deleteame'];
		$cont				= $vbulletin->GPC['cont'];
		$codes				= urldecode($vbulletin->GPC['codes']);
		$converts			= urldecode($vbulletin->GPC['conversions']);
		$test 				= $vbulletin->GPC['test'];
		$verbose 			= $vbulletin->GPC['verbose'];

		//safety checks
		$errors = array();

		build_datastore('AME_settings', serialize(array('length' => $length, 'perpage' => $perpage, 'seconds' => $seconds, 'codes' => $codes, 'conversions' => $converts)));

		$codes = explode(",", $codes);
		$converts = explode(",", $converts);

		if (!$_REQUEST['ok'])
		{
			
			$errors[] = $vbphrase['automediaembed_err_agree'];
			
		}

		if (!sizeof($codes))
		{
			
			$errors[] = $vbphrase['automediaembed_err_nocodes'];
			
		}

		if (sizeof($codes) != sizeof($converts))
		{
			
			$errors[] = $vbphrase['automediaembed_err_miscount'];
			
		}

		foreach($converts as $key => $value)
		{
			
			if (!trim($value))
			{
				
				$errors[] = $vbphrase['automediaembed_err_empty_conv'];
				
			}
			else
			{
				
				$value = trim($value);

				if (strpos($value, '\1') === false)
				{
					
					$errors[] = $vbphrase['automediaembed_err_missing_param'];
					
				}
				else
				{
					
					$replacements[] = "[url]$value" . "[/url]";
					
				}
				
			}
			
		}

		foreach($codes as $key => $value)
		{
			
			if (!trim($value))
			{
				
				$errors[] = $vbphrase['automediaembed_err_empty_code'];
				
			}
			else
			{
				
				$value = trim($value);
				$codelist .= ($codelist ? " OR " : "") . "p.pagetext LIKE '%[/" . $db->escape_string($value) . "]%'";
				$finds[] = "%\[$value\](.*?)\[/$value\]%sim";
				
			}
		}

		if (!$codelist)
		{
			
			$errors[] = $vbphrase['automediaembed_no_codelist'];
			
		}

		if (sizeof($errors))
		{
			
			foreach($errors as $value)
			{
				
				$errlist .= "<li>$value</li>";
				
			}
			
			print_form_header('automediaembed_admin', 'convertposts', 0, 1, '', '75%');
			print_table_header($vbphrase['automediaembed_err_title']);
			print_description_row(construct_phrase($vbphrase['automediaembed_err_message'], $errlist));
			print_table_footer(2, construct_button_code($vbphrase['go_back'], 'automediaembed_admin.php?do=convertposts'));
			print_cp_footer();
			exit;
		}

		$return 		= false;
		$x				= 0;

		if ($length)
		{
			
			$and .= " AND p.dateline >= $length ";
			
		}

		$sql = "SELECT count(p.postid) total FROM " . TABLE_PREFIX . "post p WHERE $codelist ";
		$postcount = $db->query_first_slave($sql);

		if ($postcount['total'])
		{
			print_form_header('automediaembed_admin', 'doconversion', false, true, 'statusform', '90%', '', true, 'get');
			print_table_header($vbphrase['automediaembed_conversion_status']);
			print_description_row(construct_phrase($vbphrase['automediaembed_rebuild_status_x'], ceil($postcount['total'] / $perpage)));
			print_table_footer(); vbflush();

			$sql = "SELECT p.postid, p.pagetext FROM " . TABLE_PREFIX . "post p
					WHERE $codelist ORDER BY p.dateline DESC LIMIT 0, $perpage";

			$results = $db->query_read_slave($sql);

			if ($db->num_rows($results))
			{
				
				echo("Converting....<ul>"); vbflush();
				
				while($result = $db->fetch_array($results))
				{
					
					$x++;
					
					if ($x == $perpage)
					{
						
						$return = true;
						
					}

					echo("<li>post $result[postid]: ");

					$text = preg_replace($finds, $replacements, $result['pagetext']);

					if ($text != $result['pagetext'])
					{
						
						if ($text)
						{
							
							if (!$test)
							{
								
								$db->query_write("UPDATE " . TABLE_PREFIX . "post SET pagetext = '" . $db->escape_string($text) . "' WHERE postid=$result[postid]");
								$db->query_write("DELETE FROM " . TABLE_PREFIX . "postparsed WHERE postid=$result[postid]");
								echo(" updated");
								
							}

							if ($verbose)
							{
								
									echo("<div style=\"border: medium;\">   was:<hr>" . htmlspecialchars_uni($result['pagetext']) . "<hr><br />it is now:<hr>" . htmlspecialchars_uni($text) . "<hr></div>");
							
							}

						}
						else
						{
							
							die("ERROR! Empty text returned. This means that you would have wiped out the entire message. Check your settings!!! Operation aborted.");
						
						}
					}
					else
					{
						
						echo("huh... no changes?");
						
					}

					echo("</li>"); vbflush();
					unset($text);

					if ($return)
					{
						
						if (ceil($postcount['total'] / $perpage) == 1)
						{
							
							$return = false;
							
						}
						
					}
					
				}
				
				echo("</ul>");
				
			}
			else
			{
				
				redirect('convertposts', 'automediaembed_no_results');
				
			}
		}
		else
		{
			
			redirect('convertposts', 'automediaembed_no_results');
			
		}

		print_form_header('automediaembed_admin', 'doconversion', false, true, 'cpform', '90%', '', true, 'get');
		print_table_header($vbphrase['automediaembed_convert_title']);

		if ($return)
		{
			
			print_label_row($vbphrase['automediaembed_rebuild_seconds_till_next'], "<input type=\"text\" name=\"timer\" id=\"timer\" readonly=\"true\" value=\"$delay\" />");
			construct_hidden_code("codes", implode(",", $codes));
			construct_hidden_code("conversions", implode(",", $converts));
			construct_hidden_code("ok", true);
			construct_hidden_code("cont", true);
			construct_hidden_code("perpage", $perpage);
			construct_hidden_code("seconds", $seconds);
			construct_hidden_code("length", $length);
			construct_hidden_code("do", "doconversion");
			construct_hidden_code("test", $test);
			construct_hidden_code("verbose", $verbose);
			print_table_footer(2, construct_button_code($vbphrase['next']));

			echo("<script language=\"javascript\"><!--

					var countdown = " . $seconds . ";

				  function submit_form()
				  {
				     document.cpform.submit();
				  }

				  function count_down()
				  {
				      countdown = countdown-1;
				  	  document.cpform.timer.value=countdown+ ' $vbphrase[automediaembed_rebuild_seconds_remaining]';
				  	  if (countdown == 0)
				  	  {
				  	  	submit_form();
				  	  }
				  	  else
				  	  {
				  	  	setTimeout('count_down()',1000);
				  	  }
				  }
				  //-->
				setTimeout('count_down()',1000);
			  </script>");

		}
		else
		{
			
			print_description_row($vbphrase['automediaembed_convert_completed']);
			print_table_footer();
			
		}


	}

	/**
	 * Display definitions for exporting
	 */
	if($action == "export")
	{
		
		$results = $db->query_read_slave("SELECT id, title, description from " . TABLE_PREFIX . "automediaembed ORDER BY displayorder, title ASC");

		print_form_header('automediaembed_admin', 'doexport');

		if ($db->num_rows($results))
		{
			print_table_header($vbphrase['automediaembed_media_definitions'], 2);
			print_cells_row(array($vbphrase['title'], "<label for=\"export_toggle\">$vbphrase[export]</label> <input type=\"checkbox\" id=\"export_toggle\" onclick=\"tick_all(this.form, 'items', this.checked)\" />"), true);
			
			while($result = $db->fetch_array($results))
			{
				
				print_checkbox_row("$result[title]<dfn>$result[description]</dfn>", "items[$result[id]]");
			
			}

			print_submit_row();
		}
		else
		{
			
			print_table_header($vbphrase['automediaembed_media_definitions'], 2);
			print_description_row($vbphrase['automediaembed_no_definitions']);
			print_table_footer(2, construct_button_code("Add new", "automediaembed_admin.php?do=edit"));
			
		}
		
	}

	/**
	 * Do the export
	 */
	if($action == "doexport")
	{

		$items = $vbulletin->input->clean_gpc('p', 'items', TYPE_ARRAY_UINT);

		if (sizeof($items))
		{
			
			foreach ($items as $key => $value)
			{
				
				if ($value)
				{
					
					$ids .= ($ids ? "," : "") . $key;
				
				}
				
			}
			
			$results = $db->query_read_slave("SELECT * FROM " . TABLE_PREFIX . "automediaembed WHERE id IN ($ids)");
			
			while($result = $db->fetch_array($results))
			{
				
				$data["$result[id]"] = $result;
			
			}

		}
		else
		{
			
			die("No items in doexport!");
			
		}

		if (sizeof($data))
		{
			
		    require_once(DIR . '/includes/class_xml.php');
		    
			$xml = new vB_XML_Builder($vbulletin);
			$xml->add_group("AME");

		    foreach($data as $key => $value)
		    {
		    	
				$xml->add_group("item");
				foreach($value as $columnname => $columnvalue)
				{
					
					$xml->add_tag($columnname, $columnvalue);
					
				}
				
				$xml->close_group();
		    }

		    $xml->close_group();

		    // ############## Finish up
		    $doc = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n\r\n" . $xml->output();
		    unset($xml);

		    require_once(DIR . '/includes/functions_file.php');
		    file_download($doc, "AME.xml", 'text/xml');
		    exit;
		    
		}
		else
		{
			
			die("Data export size is empty!");
			
		}

	}

	/**
	 * Show options for importing
	 */
	if ($action == "import")
	{
	?>

	        <script type="text/javascript">
	        <!--
	        function js_confirm_upload(tform, filefield)
	        {
	                if (filefield.value == "")
	                {
	                        return confirm("<?php echo construct_phrase($vbphrase['you_did_not_specify_a_file_to_upload'], '" + tform.serverfile.value + "'); ?>");
	                }
	                return true;
	        }
	        //-->
	        </script>

	<?php

	        print_form_header('automediaembed_admin', 'importoptions', 1, 1, 'uploadform" onsubmit="return js_confirm_upload(this, this.uploadedfile);');
	        print_table_header($vbphrase['import']);
	        print_description_row($vbphrase['automediaembed_import_desc']);
	        print_upload_row($vbphrase['upload_xml_file'], 'uploadedfile', 999999999);
	        print_input_row($vbphrase['import_xml_file'], 'serverfile', './includes/xml/ame.xml');
	        print_submit_row($vbphrase['import'], 0);
	        
	}

	/**
	 * Display imported results
	 */
	if ($action == "importoptions")
	{
		global $stylevar, $bgcounter;
		
		$vbulletin->input->clean_gpc('f', 'uploadedfile', TYPE_FILE);
		$vbulletin->input->clean_gpc('p', 'serverfile', TYPE_FILE);

		print_dots_start($vbphrase['automediaembed_importing']);

		if (file_exists($vbulletin->GPC['uploadedfile']['tmp_name']))
		{
			
		        $xml = file_read($vbulletin->GPC['uploadedfile']['tmp_name']);
		        
		}
		else if (file_exists($vbulletin->GPC['serverfile']))
		{
			
		        $xml = file_read($vbulletin->GPC['serverfile']);
		        
		}
		else
		{
			
		        print_stop_message('no_file_uploaded_and_no_local_file_found');
		        
		}

	    require_once(DIR . '/includes/class_xml.php');
		$xmlobj = new vB_XML_Parser($xml);

	    if ($xmlobj->error_no == 1)
	    {
	    	
			print_dots_stop();
			print_stop_message('no_xml_and_no_path');
			
	    }

	    if(!$arr = $xmlobj->parse())
	    {
	    	
	            print_dots_stop();
	            print_stop_message('xml_error_x_at_line_y', $xmlobj->error_string(), $xmlobj->error_line());
	            
	    }

	    print_dots_stop();

	    $items 	= array();
		$errors = array();

	    if (is_array($arr))
	    {
	    	
			if (sizeof($arr['item']))
			{
				
                if (!isset($arr['item'][0]))
                {
                	
                        $arr['item'] = array($arr['item']);
                        
                }

                $results = $db->query_read_slave("SELECT id, title, ameid FROM " . TABLE_PREFIX . "automediaembed ORDER BY id ASC");

                while($result = $db->fetch_array($results))
                {
                	
                	$existing["$result[ameid]"] = $result;
                	
                }

				foreach($arr['item'] as $data => $value)
				{
					
					if (!trim($value['ameid']))
					{
						//lets try to make one eh?
						$value['ameid'] = strtolower(str_replace(" ", "_", preg_replace("/[^a-z \d]/i", "", $value['title'])));
						$arr['item']["$data"]['ameid'] = $value['ameid'];
					}
				
					
					if (!trim($value['ameid']))
					{
						
						$errors['empty_keys'] = $vbphrase['automediaembed_some_keys_are_empty'];
						$arr['item']["$data"]['empty_char_key'] = true;
						
					}
					elseif ($existing["$value[ameid]"])
					{
						
						$errors['duplicate_keys'] = $vbphrase['automediaembed_duplicate_keys_found'];
						$arr['item']["$data"]['existing_char_key'] = true;
						
					}
					elseif (!preg_match('/\\A[A-Z0-9_-]+\\z/i', $value['ameid']))
					{
						
						$errors['invalid_keys'] = $vbphrase['automediaembed_some_keys_contains_invalid_characters'];
						$arr['item']["$data"]['invalid_char_key'] = true;
						
					}
					else
					{
						
						$existing["$value[ameid]"] = array(
							'id'	=> 0,
							'title'	=> $value['title'],
						);
						
					}

				}
				
				
				print_form_header('automediaembed_admin', 'doimport','false');
		    	print_table_header("Items");
		    	print_description_row("This is the content of the imported file. Select which ones to import and make any changes you want before clicking the import button.<p>Note that you can only create new entries this way!</p>");
		    	print_table_break();

				if (sizeof($errors))
				{
					
					print_table_header("Errors with import");
					foreach($errors as $value)
					{
						
						print_description_row($value);
						
					}

					print_table_break();
					
				}

		    	$x = 0;

		    	print_description_row(
		    		"<div style=\"float: $stylevar[right]\"><label for=\"import_all\">Import</label><input type=\"checkbox\" id=\"import_all\" onclick=\"tick_all(this.form, 'import', this.checked)\" /></div>
		    		<strong>Definitions</strong>"
		    	,	false, 2, 'thead');
		    	
		    	function ame_cmp($a, $b)
		    	{
		    		//test
		    		$abc=1;
		    		return strcmp($a["ameid"], $b["ameid"]);
		    	}
		    	
		    	usort($arr['item'], "ame_cmp");
		    	
		    	
		    	

		    	
		        foreach($arr['item'] as $data => $value)
		        {
		        	if ($value['invalid_char_key'])
		        	{
		        		
		        		$errnote = "<br /><font color=\"red\">Invalid characters in key</font>";
		        		
		        	}
		        	elseif($value['empty_char_key'])
		        	{
		        		
		        		$errnote = "<br /><font color=\"red\">Empty key!</font>";
		        		
		        	}
		        	elseif($value['existing_char_key'])
		        	{
		        		
		        		$errnote = "<br /><font color=\"red\">Duplicate key.";
		        		
		        		if ($existing["$value[ameid]"]['id'])
		        		{
		        			
		        			$errnote .= "This key would clash with an existing item";
		        			
		        		}
		        		else
		        		{
		        			
		        			$errnote .= "This key will clash with another item you are trying to import.";
		        			
		        		}
		        		
		        		$errnote .= "</font>";
		        	}
		        	else
		        	{
		        		
		        		$errnote = "";
		        		
		        	}

                	$bgcounter++;
                	print_description_row("<div ondblclick=\"ame_toggle_group('import_$x'); return false;\">
                	<div style=\"float: $stylevar[right]\"><input type=\"checkbox\" name=\"import[$x]\" id=\"import_$x\" value=\"1\" " . ($errnote == '' ? "checked=\"checked\"" : "") . "> </div>
                	<a style=\"cursor: pointer;\"  onclick=\"ame_toggle_group('import_$x'); return false;\"><img src=\"../cpstyles/" . $vbulletin->options['cpstylefolder'] . "/cp_collapse.gif\" title=\"Expand\" id=\"collapse_import_$x\" alt=\"Expand\" border=\"0\" /></a> <strong><a style=\"cursor: pointer;\" onclick=\"ame_toggle_group('import_$x'); return false;\">$value[title]</a>$errnote</strong></div>\n\t");
					echo("<tr>\n\t<td cellspacing=\"0\" cellpadding=\"0\" colspan=\"2\" style=\"display: none\" id=\"td_import_$x\">\n\t");
                	print_table_start(false, '100%', "0", "table_import_$x");
					print_input_row($vbphrase['title'], "title[$x]", $value['title']);
					print_input_row($vbphrase['description'], "description[$x]", $value['description'], true, 35, 255);
					print_input_row($vbphrase['automediaembed_key'] . $errnote, "key[$x]", $value['ameid']);
					print_input_row($vbphrase['automediaembed_display_order'], "displayorder[$x]", $value['displayorder']);
					print_yes_no_row($vbphrase['automediaembed_active_desc'], "status[$x]", $value['status']);
					print_yes_no_row($vbphrase['automediaembed_contain_desc'], "container[$x]", $value['container']);
					print_textarea_row($vbphrase['automediaembed_search'], "findcode[$x]", $value['findcode']);
					print_textarea_row($vbphrase['automediaembed_replace'], "replacecode[$x]", $value['replacecode']);
					print_yes_no_row($vbphrase['automediaembed_extraction'], "extraction[$x]", $value['extraction']);
					print_textarea_row($vbphrase['automediaembed_embedregexp'], "embedregexp[$x]", $value['embedregexp']);
					print_textarea_row($vbphrase['automediaembed_validation'], "validation[$x]", $value['validation']);
					$x++;
					echo("</table></td></tr>");


		        }

		        print_table_break();
		        
			}
			else
			{
				
				print_stop_message('automediaembed_no_items_to_import');
				
			}
			
	    }
	    else
	    {
	    	
	    	print_stop_message('automediaembed_invalid_xml');
	    	
	    }

	    print_submit_row($vbphrase['import']);

	}

	/**
	 * Freaking well import them!
	 */
	if ($action == "doimport")
	{

		$vbulletin->input->clean_array_gpc('p', array(
			'import'		=> TYPE_ARRAY_BOOL,
			'title'			=> TYPE_ARRAY_STR,
			'key'			=> TYPE_ARRAY_STR,
			'description'	=> TYPE_ARRAY_STR,
			'displayorder'	=> TYPE_ARRAY_UINT,
			'findcode'		=> TYPE_ARRAY_STR,
			'replacecode'	=> TYPE_ARRAY_STR,
			'status'		=> TYPE_ARRAY_BOOL,
			'container'		=> TYPE_ARRAY_BOOL,
			'embedregexp'	=> TYPE_ARRAY_STR,
			'extraction'	=> TYPE_ARRAY_BOOL,
			'validation'	=> TYPE_ARRAY_STR,
		));

		$import			= $vbulletin->GPC['import'];
		$title 			= $vbulletin->GPC['title'];
		$ameid 			= $vbulletin->GPC['key'];
		$description 	= $vbulletin->GPC['description'];
		$displayorder 	= $vbulletin->GPC['displayorder'];
		$findcode 		= $vbulletin->GPC['findcode'];
		$replacecode	= $vbulletin->GPC['replacecode'];
		$status			= $vbulletin->GPC['status'];
		$container		= $vbulletin->GPC['container'];
		$embedregexp	= $vbulletin->GPC['embedregexp'];
		$extraction 	= $vbulletin->GPC['extraction'];
		$validation 	= $vbulletin->GPC['validation'];

		$errors = array();

        $results = $db->query_read_slave("SELECT id, title, ameid FROM " . TABLE_PREFIX . "automediaembed ORDER BY id ASC");

        while($result = $db->fetch_array($results))
        {
        	
        	$existing["$result[ameid]"] = $result;
        	
        }

		foreach($import as $key => $value)
		{
			
			if(!trim($ameid["$key"]))
			{
				
				$errors['empty_keys'] = $vbphrase['automediaembed_some_keys_are_empty'];
				
			}
			
			if ($existing["$ameid[$key]"])
			{
				
				$errors['duplicate_keys'] = $vbphrase['automediaembed_duplicate_keys_found'];
				
			}
			elseif (!preg_match('/\\A[A-Z0-9_-]+\\z/i', $ameid["$key"]))
			{
				
				$errors['invalid_keys'] = $vbphrase['automediaembed_some_keys_contains_invalid_characters'];
				
			}
			else
			{
				
				$existing["$ameid[$key]"] = array(
					'id'	=> 0,
					'title'	=> $title["$key"],
				);
				
			}

		}

		if (sizeof($errors))
		{
			
			foreach($errors as $value)
			{
				
				$err_message .= "<li>$value</li>";
				
			}

			print_stop_message('automediaembed_cant_save_errors', "Import", $err_message);
			
		}


		foreach ($title as $key => $value)
		{
			
			if ($import["$key"])
			{
				
				$sql = "INSERT INTO " . TABLE_PREFIX . "automediaembed (title, ameid, description, displayorder, findcode, replacecode, status, container, embedregexp, extraction, validation) VALUES (
					'" . $db->escape_string($title["$key"]) . "',
					'" . $db->escape_string($ameid["$key"]) . "',
					'" . $db->escape_string($description["$key"]) . "',
					'$displayorder[$key]',
					'" . $db->escape_string($findcode["$key"]) . "',
					'" . $db->escape_string($replacecode["$key"]) . "', '" . $status["$key"] . "', '" . $container["$key"] . "', '" . $db->escape_string($embedregexp["$key"]) . "', '" . intval($extraction["$key"]) . "', '" . $db->escape_string($validation["$key"]) . "')";

				$db->query_write($sql);
				
			}
			
		}
		
		ame_write_cache();
		redirect("display", "automediaembed_imported");
	}

	/**
	 * Writes the contents of $ameinfo to a file to avoid a DB query and array build.
	 *
	 * @return boolean
	 */
    function ame_write_cache()
    {
    	global $vbulletin;

    	$path = $vbulletin->options['automediaembed_cache_path'];

  		if (!$vbulletin->options['automediaembed_cache'] OR !$path)
  		{
  			
  			return false;
  			
  		}

    	if (!strrpos($path, "/"))
    	{
    		
    		if (!strrpos($path, "\\"))
    		{
    			
    			$path .= '/';
    			
    		}
    		
    	}

    	if (!is_dir($path))
    	{
    		
    		return false;
    		
    	}

    	require_once(DIR . '/includes/ame_bbcode.php');

    	$findonly 	= fetch_full_ameinfo(true, true);

 		$output  	=  "<?php\n";
        $output 	.= ame_write_array_entry($findonly, '$ameinfo');
        $output 	.= "\n?>";
        
    	ame_write_to_file($path, "findonly", $output);

    	$ameinfo 	= fetch_full_ameinfo(false, true);

 		$output 	=  "<?php\n";
        $output 	.= ame_write_array_entry($ameinfo, '$ameinfo');
        $output 	.= "\n?>";
        
    	ame_write_to_file($path, "ameinfo", $output);

    }

	print_cp_footer();

?>