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
* Is the abstract factory that handels data object instantiation.
*
* The obect will create itself depending on the type that is passes to
* the constructor. The object will consist of a number of elements
* some being vbmandatory and the other nonvbmandatory.
*
* A valid object is one that has values for all the vbmandatroy elements.
*
*
* @package 		ImpEx
* @version		$Revision: 1.43 $
* @author		Jerry Hutchings <jerry.hutchings@vbulletin.com>
* @checkedout	$Name:  $
* @date 		$Date: 2006/11/11 02:13:15 $
* @copyright 	http://www.vbulletin.com/license.html
*
*/
if (!class_exists('ImpExDatabase')) { die('Direct class access violation'); }

class ImpExData extends ImpExDatabase
{
	/**
	* Class version
	*
	* This will allow the checking for interoprability of class version in diffrent
	* versions of ImpEx
	*
	* @var    string
	*/
	var $_version = '0.0.1';

	/**
	* Data elements store
	*
	* 3D array to contain  'mandatory', 'nonmandatory', 'dictionary'
	*
	* @var    array
	*/
	var $_values = array();

	/**
	* Element types
	*
	* Element types in the _values array
	*
	* @var    array
	*/
	var $_elementtypes = array ('mandatory' , 'nonmandatory');

	/**
	* Object data type
	*
	* Stores the type of data object, i.e. user, post, thread.
	*
	* @var    string
	*/
	var $_datatype = '';

	/**
	* is_valid error store
	*
	* Stores the type elements that is_valid failed on
	*
	* @var    string
	*/
	var $_failedon = '';

	/**
	* flag for default values
	*
	* Stores if the object has any default fields, i.e. Location, Occupation
	*
	* @var    boolean
	*/
	var $_has_default_values = false;

	/**
	* store for default values
	*
	* Stores data for default values
	*
	* @var    array
	*/
	var $_default_values = array();

	/**
	* flag for customFields
	*
	* Stores if the object has any custom fields, i.e. new profilefiled entries
	*
	* @var    boolean
	*/
	var $_has_custom_types = FALSE;

	/**
	* store for custom fields
	*
	* Stores data for custom fields
	*
	* @var    array
	*/
	var $_custom_types = array();

	/**
	* Password flag
	*
	* Definies where the  password needs to be md5() before md5($password . $salt) or not.
	*
	* @var    boolean
	*/
	var $_password_md5_already = false;

	/**
	* Email Associate
	*
	* Matches imported users to existing and assoiaties opposed to creating new users.
	*
	* @var    boolean
	*/
	var	$_auto_email_associate = false;

	/**
	* Password flag
	*
	* Here in case the imported password can't be retrived, i.e. it in crypt so it
	* forces the board to assign a new one.
	*
	* @var    boolean
	*/
	var $_setforgottenpassword = false;

	/**
	* Instantiates a class of the child module being called by index.php
	*
	* @param	object	databaseobject	The database that has the vbfiled definitions
	* @param	object	sessionobject	The current sessionobject.
	* @param	string	mixed			The name of the object to create user,post,thread,etc
	*
	* @return	none
	*/
	function ImpExData(&$Db_object, &$sessionobject, $type)
	{
		$targetdatabasetype = $sessionobject->get_session_var('targetdatabasetype');
		$targettableprefix = $sessionobject->get_session_var('targettableprefix');
		// TODO: Include files with the data objects and checking functions, faster and less dB over head ?
		$this->_datatype=$type;
		$this->_values = $this->create_data_type(
				$Db_object,
				$targetdatabasetype,
				$targettableprefix,
				$type
		);

		if (!$this->_values)
		{
			$sessionobject->add_error(
				'fatal',
				'ImpExData',
				"ImpExData contructor failed trying to construct a $type object",
				'Does the database user have modify permissions? Is it a valid connection? Are all the tables ok?'
			);
		}
	}

	/**
	* Returns the valid state of the data object
	*
	* Searches the mandatory elements for a NULL value, if it finds one it stores it in _failed on and
	* returns FALSE, other wise returns TRUE
	*
	* @return	none
	*/
	function is_valid()
	{
		$return_state = true;

		// If any of the madatory values are null return false.
		// While there check_data($data) on them.
		foreach (($this->_values[$this->_datatype]['mandatory']) AS $key => $value)
		{
			if (empty($value) AND $value !=0 OR $value == '!##NULL##!')
			{
				$this->_failedon = $key;
				return false;
			}

			if($this->_values[$this->_datatype]['dictionary'][$key] == 'return true;')
			{
				$return_state = true;
			}
			else
			{
				// TODO: Can't lambda because of appaling memory usage and PHP not cleaning, going to have to local function it
				// Create a lambda function with the dictionary contents of the dB to check the data
				$check_data = create_function('$data', $this->_values[$this->_datatype]['dictionary'][$key]);

				if(!$check_data($value))
				{
					$this->_failedon = $key;
					return false;
				}
			}
		}

		// Check all the nonmandatory ones as well, is there are any - subscriptionlog
		if (is_array($this->_values[$this->_datatype]['nonmandatory']))
		{
			foreach (($this->_values[$this->_datatype]['nonmandatory']) AS $key => $value)
			{
				// TODO: Either ALL the database fields need a default or vBfields needs to be able to read a default list
				if ($value == '!##NULL##!')
				{
					$this->_values[$this->_datatype]['nonmandatory'][$key] = ''; // Empty it for the SQL so the database will default to the field default
				}

				if($this->_values[$this->_datatype]['dictionary'][$key] == 'return true;')
				{
					$return_state = true;
				}
				else
				{
					// TODO: Can't lambda because of appaling memory usage and PHP not cleaning, going to have to local function it
					// Create a lambda function with the dictionary contents of the dB to check the data
					$check_data = create_function('$data', $this->_values[$this->_datatype]['dictionary'][$key]);

					if(!$check_data($value))
					{
						$this->_failedon = $key;
						return false;
					}
				}
			}
		}
		return $return_state;
	}

	/**
	* Returns the percentage completness of the object
	*
	* Calculated the NULL's from the total amount of elements to discover the percentage
	* complete that the object is
	*
	* @return	double
	*/
	function how_complete()
	{
		$totalelements = 0;
		$nullelements = 0;

		foreach ($this->_elementtypes AS $name => $type)
		{
			if(is_array($this->_values[$this->_datatype][$type]))
			{
				foreach ($this->_values[$this->_datatype][$type] AS $key => $value)
				{
					if ($value == '!##NULL##!' OR $value == '' OR $value == NULL)
					{
						$nullelements++;
					}
				$totalelements++;
				}
			}
		}
		return number_format(((($totalelements - $nullelements) * 100) / $totalelements), 2, '.', '');
	}


	/**
	* Returns a XML representation of the object
	*
	* Returns the elements as a formed XML document
	*
	* @return	string
	*/
	function return_xml()
	{
		$xmlstring = '<?xml version="1.0" encoding="ISO-8859-1"?><' . strtolower( $this->_datatype) . '>';

		//TODO : Sort by mandatory
		foreach ($this->_elementtypes AS $name => $type)
		{
			$xmlstring .=  '<' . strtolower($type) . '>';

			foreach ($this->_values[$this->_datatype][$type] AS $key => $value)
			{
				$xmlstring .= '<' . strtolower($key) . '>' . $value . '</' . strtolower($key) . '>';
			}
			$xmlstring .= '</' . strtolower($type) . '>';
		}
		$xmlstring .= '</' . strtolower($this->_datatype) . '>';

		return $xmlstring;
	}


	/**
	* Returns a string representation of the object
	*
	* Returns the elements as a string
	*
	* @return	string
	*/
	function return_string()
	{
		// TODO: Don't HTML'ise it
		// TODO: Need to be able to allow an object return string as well
		$returnstring = '';

		foreach ($this->_values as $type)
		{
			foreach ($this->_values[$type] as $key => $value)
			{
				$returnstring .= "<br /><b>$type</b>-$key-<i>$value</i>";
			}
		}
		return $returnstring;
	}


	/**
	* Accessor
	*
	* @param	string	elementtype		The type of value being retrived
	* @param	string	name			The name of value being retrived
	*
	* @return	mixed	string|NULL
	*/
	function get_value($section, $name)
	{
		if ($this->_values[$this->_datatype][$section][$name] != 'NULL')
		{
			return $this->_values[$this->_datatype][$section][$name];
		}
		else
		{
			return false;
		}
	}


	/**
	* Accessor
	*
	* @param	string	elementtype		The type of value being set
	* @param	string	name			The name of value being set
	* @param	string	value			The passes value
	*
	* @return	boolean
	*/
	function set_value($section, $name, $value)
	{
		if (@array_key_exists($name, $this->_values[$this->_datatype][$section]))
		{
			$this->_values[$this->_datatype][$section][$name] = $value;
			return true;
		}
		else
		{
			return false;
		}
	}


	/**
	* Accessor
	*
	* @param	string	name			The name of value being set
	* @param	string	value			The passes value
	*
	* @return	boolean
	*/
	function add_default_value($key, $value)
	{
		if (empty($this->_default_values[$key]))
		{
			$tempArray = array($key => $value);
			$this->_default_values = array_merge($this->_default_values, $tempArray);
			$this->_has_default_values = true;
		}
		else
		{
			$this->_default_values[$name] = $value;
			$this->_has_default_values = true;
		}
		return $this->_has_default_values;
	}


	/**
	* Accessor : Returns the array of default value
	*
	* @param	string	name			The name of value being set
	*
	* @return	boolean|array
	*/
	function get_default_values()
	{
		return $this->_default_values;
	}


	/**
	* Accessor
	*
	* @param	string	name			The name of value being set
	* @param	string	value			The passes value
	*
	* @return	boolean
	*/
	function add_custom_value($key, $value)
	{
		if (empty($this->_custom_types[$key]))
		{
			$tempArray = array($key => $value);
			$this->_custom_types = array_merge($this->_custom_types, $tempArray);
			$this->_has_custom_types = true;
		}
		else
		{
			$this->_custom_types[$name] = $value;
			$this->_has_custom_types = true;
		}
		return $this->_has_custom_types;
	}


	/**
	* Accessor : Returns the array of custom values
	*
	* @param	string	name			The name of value being set
	*
	* @return	boolean|array
	*/
	function get_custom_values()
	{
		return $this->_custom_types;
	}
}
/*======================================================================*\
|| ####################################################################
|| # Downloaded: 03:45, Mon Nov 13th 2006
|| # CVS: $RCSfile: ImpExData.php,v $ - $Revision: 1.43 $
|| ####################################################################
\*======================================================================*/
?>
