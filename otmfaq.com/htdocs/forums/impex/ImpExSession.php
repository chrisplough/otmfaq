<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin  - Licence Number VBF98A5CB5
|| # ---------------------------------------------------------------- # ||
|| # All PHP code in this file is �2000-2006 Jelsoft Enterprises Ltd. # ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/
/**
* Core session module that holds the state of the system.
*
* Holds lots of groovy session data, yum yum yum.
*
* @package 		ImpEx
* @version		$Revision: 1.29 $
* @author		Jerry Hutchings <jerry.hutchings@vbulletin.com>
* @checkedout	$Name:  $
* @date 		$Date: 2006/07/18 03:26:45 $
* @copyright 	http://www.vbulletin.com/license.html
*
*/
if (!defined('IDIR')) { die; }

class ImpExSession
{
	/**
	* Class version
	*
	* This will allow the checking for interoprability of class version in diffrent
	* versions of ImpEx
	*
	* @var    string
	*/
	var $_version = "0.0.1";

	/**
	* The main data array for the storage of session variables
	*
	* All the session variables are stored in here
	*
	*
	* @var    array
	*/
	var $_session_vars = array
	(
		'version'		=>		'0.0.1',
		'system'		=>		'NONE',
		'begun'			=>		'FALSE',
		'warning'		=>		'FALSE',
		'finished'		=>		'FALSE',
		'modulelist'	=>		'FALSE'
	);

	/**
	* The title text
	*
	* The title text for the modules of the currently running importer
	*
	*
	* @var    array
	*/
	var $_moduletitles = array();
	
	var $_target_db;


	/**
	* The Session errors
	*
	* The errors are held in a 2D array, each entry  holds : 'timestamp','type','module','errorstring','remedy'
	*
	*
	* @var    array
	*/
	var $_session_errors = array();

	/**
	* Constructor
	*
	* Empty
	*
	*/
	function ImpExSession()
	{
	}

	/**
	* Lists all the modules in a system/XXXX/ folder and gets their title texts and adds the module
	* number and sets it to FALSE
	*
	* @return	none
	*/
	function build_module_list(&$displayobject)
	{
		// TODO: Check this with rogue .php files
		if ($this->get_session_var('modulelist') == 'FALSE')
		{
			$dir = IDIR . '/systems/' . $this->get_session_var('system');

			if (is_dir($dir))
			{
				if ($dh = opendir($dir))
				{
					while (($filename = readdir($dh)) !== false)
					{
						if (substr($filename, -strlen('.php')) == '.php' AND $filename{0} != '.' AND $filename != 'index.html')
						{
							$sourcefile = '';
							$sourcefile = $dir .'/'. $filename;
							require_once($sourcefile);
							$classname = $this->get_session_var('system') . '_'. substr($filename, 0 , 3);
							$module = new $classname($displayobject);
							$this->add_session_var(substr($filename, 0 , 3), 'FALSE');
							$this->add_module_title(substr($filename, 0 , 3), $module->_modulestring);
							unset($sourcefile);
						}
					}
					closedir($dh);
				}
			}
			
			// Add the default clean up ones
			$this->add_session_var('cleanup_module_title', 'FALSE');
			$this->add_module_title("901", $displayobject->phrases['cleanup_module_title']);
			
			$this->add_session_var('feedback_module_title', 'FALSE');
			$this->add_module_title("910", $displayobject->phrases['feedback_module_title']);
			
			
			$this->set_session_var('modulelist', 'TRUE');
		}
	}

	/**
	* Adds an error to the error stack
	*
	* @param	string	mixed	The type of error : 'getdetails' | 'fatal' | 'warning' | 'alert' | 'notice'
	*
	* @return	none
	*/
	function add_error($importid, $error, $remedy)
	{
		
		if (empty($importid) OR $importid == 0 OR !is_int($importid))
		{
			return false;
		}
		
		$type 	= 'none found';
		
		// Get the module type
		// TODO: Module string it
		foreach($this->_session_vars AS $name => $value)
		{
			if($value == 'working')
			{
				$type = substr($name,7); 
			}
		}
		
		$type	= addslashes($type);
		$error	= addslashes($error);
		$remedy	= addslashes($remedy);
		
		
		if ($this->get_session_var('errorlogging'))
		{
			$this->_target_db->query("
				INSERT INTO ". $this->_session_vars['targettableprefix'] ."impexerror
				(
					datatype,
					importid,
					error,
					remedy
				)
				VALUES
				( 
					'" . $type . "',"
					. intval($importid) . ",
					'" . $error . "', 
					'" . $remedy . "'
				)"
			);
		}
	}

	/**
	* Accessor : Returns a modules title text
	*
	* @param	int		mixed	The 3 digit number refrence to the object
	*
	* @return	none
	*/
	function get_module_string($position)
	{
		return $this->_moduletitles[$position];
	}

	/**
	* Returns a count of the number of modules in a system
	*
	* @return	int
	*/
	function get_number_of_modules()
	{
		return count($this->_moduletitles);
	}

	/**
	* Accessor : Returns a session variable
	*
	* @param	int		mixed	The 3 digit number refrence to the object
	*
	* @return	mixed|boolean
	*/
	function get_session_var($name)
	{
		return stripslashes($this->_session_vars[$name]);
	}

	/**
	* Returns a string of the errors
	*
	* @param	string		mixed	Returns the errors : 'getdetails' | 'fatal' | 'warning' | 'alert' | 'notice' | 'all'
	*
	* @return	string
	*/
	function display_errors($type)
	{
		$i = 0;
		$return_string = '';
		foreach ($this->_session_errors as $value)
		{
			if ($type == 'all' OR strtolower($type) == $value['type'])
			{
				$return_string .= '<br /><b>Timestamp</b> : ' . date("H:i:s",$value['timestamp']) . '. <b>Type</b> : ' . $value['type'] . '. <b>Module</b> : ' . $value['module'] . ' .<br /><u>Errorstring</u><br />' . $value['errorstring'] . '. <br /><u>Remedy</u><br />' . $value['remedy'] . '<br />';
				$i++;
			}
		}

		return "<h4>Error count of : $type = $i</h4>" . $return_string;
	}

	/**
	* Accessor : Sets a session variable
	*
	* @param	string	mixed	The name of the session variable
	* @param	mixed	mixed	The value to set the variable to
	*
	* @return	boolean
	*/
	function set_session_var($name, $value)
	{
		if ($this->_session_vars[$name] == NULL)
		{
			return false;
		}
		else
		{
			$value = addslashes($value);
			$this->_session_vars[$name] = $value;
			return true;
		}
	}

	/**
	* Accessor : Adds a session variable, if the variable exsists it sets it
	*
	* @see		set_session_var
	*
	* @param	string	mixed	The name of the session variable
	* @param	mixed	mixed	The value to set the variable to
	*
	* @return	boolean
	*/
	function add_session_var($key, $value)
	{
		if (empty($this->_session_vars[$key]))
		{
			$value = addslashes($value);
			$tempArray = array($key => $value);
			$this->_session_vars = array_merge($this->_session_vars, $tempArray);
			return true;
		}
		else
		{
			return $this->set_session_var($key, $value);
		}
	}

	/**
	* Accessor : Adds a module title, if the variable exsists it sets it if it dosen't exsist it creates it
	*
	*
	* @param	string	mixed	The 3 digit number of the module
	* @param	mixed	mixed	The value to set the module title to
	*
	* @return	boolean
	*/
	function add_module_title($key, $value)
	{
		$this->_moduletitles[$key] = $value;
	}

	/**
	* Accessor : Returns a modules title
	*
	*
	* @param	string	mixed	The 3 digit number of the module
	*
	* @return	boolean
	*/
	function get_module_title($name)
	{
		return $this->_moduletitles[$name];
	}

	/**
	* Accessor : Returns the number of the current working module, or FALSE
	*
	* @return	boolean|mixed
	*/
	function any_working()
	{
		if ($this->_session_vars[001] == 'FAILED')
		{
			// The tables didn't match, probally table prefix ....
			return false;
		}
		
		for ($i = 0; $i <= $this->get_number_of_modules(); ++$i)
		{
			$position = str_pad($i, 3, '0', STR_PAD_LEFT);
			if ($this->_session_vars[$position] == 'WORKING')
			{
				return $position;
			}
		}
		return false;
	}


	/**
	* Remvoes a session varaible from the array
	*
	* @param	string	var_name	The vararaible to remove
	*
	* @return	boolean
	*/
	function remove_session_var($name)
	{
		unset($this->_session_vars[$name]);
		return $this->get_session_var($name);
	}

	/**
	* Starts and stops a timer to roughly measure an import (only really works with auto commit on)
	*
	* @param	string	modulestring	The name of the module calling the function
	* @param	string	mixed			Start or Stop
	* @param	boolean					Checks wheter auto commit is on or not
	*
	* @return	mixed	string|NULL
	*/
	function timing($modulestring,$action,$isauto)
	{
		if ($action == 'start')
		{
			return $this->add_session_var($modulestring . '_' . $action, time());
		}
		else if ($action == 'stop')
		{
			$this->add_session_var($modulestring . '_' . $action, time());
			$this->add_session_var($modulestring . '_auto', $isauto);

			$taken = intval($this->get_session_var($modulestring . '_stop')) -
					 intval($this->get_session_var($modulestring . '_start'));

			if ($taken == 0)
			{
				$taken = 1;
			}

			return $this->add_session_var($modulestring . '_time_taken', $taken);
		}
		else
		{
			return false;
		}
	}

	/**
	* Returns the module timings as a 3 element array : '_start', '_stop', '_time_taken'
	*
	* @param	string	modulestring	The name of the module calling the function
	* @param	boolean					false OR the name of one of the elements to retrive
	*
	* @return	array	start time stamp, end time stamp, seconds taken
	*/
	function return_stats($modulestring, $just_one = false)
	{
		if($just_one)
		{
			return intval($this->get_session_var($modulestring . $just_one));
		}

		return array (
			'_start'			=>	intval($this->get_session_var($modulestring . '_start')),
			'_stop'				=>	intval($this->get_session_var($modulestring . '_stop')),
			'_time_taken'		=>	intval($this->get_session_var($modulestring . '_time_taken')),
			'_objects_done'		=>	intval($this->get_session_var($modulestring . '_objects_done')),
			'_objects_failed'	=>	intval($this->get_session_var($modulestring . '_objects_failed'))
			);

	}

	/**
	* If a module complete in under 1 second it sets it to 0.
	*
	* @param	string	mixed	The module being checked
	*
	* @return	mixed	string|NULL
	*/
	function end_timing($modulestring)
	{
		if($this->get_session_var($modulestring . '_time_taken') == '0')
		{
			$this->set_session_var($modulestring . '_time_taken', '1');
		}
	}


	function get_users_to_associate()
	{
		$return_array = array();
		foreach($this->_session_vars as $key => $value)
		{
			if (substr($key, 0, 12) == 'user_to_ass_' && $value != '')
			{
				array_push($return_array, array($key => $value));
			}
		}
		return $return_array;
	}

	function delete_users_to_associate()
	{
		foreach($this->_session_vars as $key => $value)
		{
			if (substr($key, 0, 12) == 'user_to_ass_')
			{
				$this->remove_session_var($key);
			}
		}
	}

}
/*======================================================================*\
|| ####################################################################
|| # Downloaded: 03:45, Mon Nov 13th 2006
|| # CVS: $RCSfile: ImpExSession.php,v $ - $Revision: 1.29 $
|| ####################################################################
\*======================================================================*/
?>
