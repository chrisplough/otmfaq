<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin  - Licence Number VBF98A5CB5
|| # ---------------------------------------------------------------- # ||
|| # All PHP code in this file is ©2000-2006 Jelsoft Enterprises Ltd. # ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/

/**
* The database proxy object.
*
* This handles interaction with the different types of database.
*
* @package 		ImpEx
* @version		$Revision: 1.177 $
* @author		Jerry Hutchings <jerry.hutchings@vbulletin.com>
* @checkedout	$Name:  $
* @date 		$Date: 2005/10/03 20:14:17 $
* @copyright 	http://www.vbulletin.com/license.html
*
*/

class ImpExDatabase extends ImpExFunction
{
	/**
	* Class version
	*
	* This will allow the checking for inter-operability of class version in different
	* versions of ImpEx
	*
	* @var    string
	*/
	var $_version = '0.0.1';

	var $_customernumber = '[#]customernumber[#]';

	/**
	* Constructor
	*
	* Empty
	*
	*/
	function ImpExDatabase()
	{
	}


	/**
	* Retrieves the values needed to define a ImpExData object
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	string	mixed			The type of object being created
	*
	* @return	array|boolean
	*/
	function create_data_type(&$Db_object, &$databasetype, &$tableprefix, $type)
	{
		$returnarray = array();

		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$result = $Db_object->query("
					SELECT fieldname, vbmandatory, defaultvalue, dictionary
					FROM " . $tableprefix . "vbfields
					WHERE tablename = '" . $type . "'
					ORDER BY vbmandatory
				");
				while ($line = $Db_object->fetch_array($result))
				{
						if ($line['vbmandatory'] == 'Y')
						{
							$returnarray["$type"]['mandatory']["$line[fieldname]"] =  $line['defaultvalue'];
						}
						if ($line['vbmandatory'] == 'N' || $line['vbmandatory'] == 'A')
						{
							$returnarray["$type"]['nonmandatory']["$line[fieldname]"] = $line['defaultvalue'];
						}
						$returnarray["$type"]['dictionary']["$line[fieldname]"] = $line['dictionary'];
				}
				return $returnarray;
			}

			// Postgres Database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Modifies a table to include an importid
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	string	mixed			The name of the table to change
	* @param	string	mixed			The name of the field to add to the table
	*
	* @return	array|boolean
	*/
	function add_import_id(&$Db_object, &$databasetype, &$tableprefix, $tablename, $importname)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$rows = $Db_object->query("DESCRIBE {$tableprefix}{$tablename} $importname");

				if ($Db_object->num_rows($rows))
				{
					return true;
				}
				else
				{
					$olderror = $Db_object->reporterror;
					$Db_object->reporterror = 0;
					$Db_object->query("ALTER TABLE " . $tableprefix . $tablename . " ADD COLUMN " . $importname . " BIGINT NOT NULL DEFAULT 0");
					$haserror = $Db_object->geterrno();
					$Db_object->reporterror = $olderror;

					if (!$haserror)
					{
						return true;
					}
					else
					{
						return false;
					}
				}
			}

			// Postgres Database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Modifys the profilefield AND usertextfield table for a custom user entry
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	string	mixed			The title of the custom field
	* @param	string	mixed			The description of the custom field
	*
	* @return	array|boolean
	*/
	function add_custom_field(&$Db_object, &$databasetype, &$tableprefix, $profiletitle, $profiledescription)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$rows = $Db_object->query("
					SELECT title
					FROM " . $tableprefix . "profilefield
					WHERE title = '" . $profiletitle . "'
				");

				if ($Db_object->num_rows($rows) > 0)
				{
					return true;
				}
				else
				{
					$displayorder = $Db_object->query_first("
						SELECT displayorder
						FROM " . $tableprefix . "profilefield
						ORDER BY displayorder DESC
						LIMIT 1
					");
					$neworder = intval($displayorder['displayorder']) + 1;

					$Db_object->query("
						INSERT INTO " . $tableprefix . "profilefield
							(title, description, displayorder)
						VALUES
						('" . $profiletitle . "', '" . $profiledescription ."', '" . $neworder ."')
					");

					if ($Db_object->affected_rows())
					{
						$fieldid = $Db_object->insert_id();

						$olderror = $Db_object->reporterror;
						$Db_object->reporterror = 0;
						$Db_object->query("ALTER TABLE " . $tableprefix . "userfield ADD field" . $fieldid . " CHAR(250) NOT NULL");
						$haserror = $Db_object->geterrno();
						$Db_object->reporterror = $olderror;
						if (!$haserror)
						{
							return true;
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
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}

	function import_attachment(&$Db_object, &$databasetype, &$tableprefix, $import_post_id = TRUE)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				if($import_post_id)
				{
					if($this->get_value('nonmandatory', 'postid'))
					{
						// Get the real post id
						$post_id = $Db_object->query_first("
							SELECT postid, userid
							FROM " . $tableprefix . "post
							WHERE
							importpostid = " . $this->get_value('nonmandatory', 'postid'));
	
						if(empty($post_id['postid']))
						{
							// Its not there to be attached through.
							return false;
						}
					}
					else
					{
						// No post id !!!
						return false;
					}
				}
				else
				{
					$sql ="
					SELECT userid, postid
					FROM " . $tableprefix . "post 
					WHERE postid = " . $this->get_value('nonmandatory', 'postid');
								
					$post_id = $Db_object->query_first($sql);
				}
				
				// Update the post attach
				$Db_object->query("UPDATE " . $tableprefix . "post SET attach = attach+1 WHERE postid = " . $post_id['postid']);

				// Ok, so now where is it going ......
				$attachpath =  $this->get_options_setting($Db_object, $databasetype, $tableprefix, 'attachpath');
				$attachfile = $this->get_options_setting($Db_object, $databasetype, $tableprefix, 'attachfile');

				$Db_object->query("
					INSERT INTO " . $tableprefix . "attachment
					(
						importattachmentid, filename, filedata,
						dateline, visible, counter, filesize,
						postid, filehash, userid
					)
					VALUES
					(
						'" . $this->get_value('mandatory', 'importattachmentid') . "',
						'" . addslashes($this->get_value('mandatory', 'filename')) . "',
						'',
						'" . $this->get_value('nonmandatory', 'dateline')  . "',
						'" . $this->get_value('nonmandatory', 'visible')  . "',
						'" . $this->get_value('nonmandatory', 'counter')  . "',
						'',
						'" . $post_id['postid']  . "',
						'" . $this->get_value('nonmandatory', 'filehash')  . "',
						'" . $post_id['userid'] . "'
					)
				");

				$attachment_id = $Db_object->insert_id();

				switch (intval($attachfile))
				{
					case '0':	// Straight into the dB
					{
						$Db_object->query("
							UPDATE " . $tableprefix . "attachment
							SET
							filedata = '" . addslashes($this->get_value('mandatory', 'filedata')) . "',
							filesize = " . intval($this->get_value('nonmandatory', 'filesize'))  . "
							WHERE attachmentid = {$attachment_id}
						");

						return $attachment_id;
					}

					case '1':	// file system OLD naming schema
					{
						$full_path = $this->fetch_attachment_path($post_id['userid'], $attachpath, false, $attachment_id);

						if($this->vbmkdir(substr($full_path, 0, strrpos($full_path, '/'))))
						{
							if ($fp = fopen($full_path, 'wb'))
							{
								fwrite($fp, $this->get_value('mandatory', 'filedata'));
								fclose($fp);
								$filesize = filesize($full_path);

								if($filesize)
								{
									$Db_object->query("
										UPDATE " . $tableprefix . "attachment
										SET
										filesize = " . intval($this->get_value('nonmandatory', 'filesize'))  . "
										WHERE attachmentid = {$attachment_id}
									");

									return $attachment_id;
								}
							}
						}
						return false;
					}

					case '2':	// file system NEW naming schema
					{
						$full_path = $this->fetch_attachment_path($post_id['userid'], $attachpath, true, $attachment_id);

						if($this->vbmkdir(substr($full_path, 0, strrpos($full_path, '/'))))
						{
							if ($fp = fopen($full_path, 'wb'))
							{
								fwrite($fp, $this->get_value('mandatory', 'filedata'));
								fclose($fp);
								$filesize = filesize($full_path);

								if($filesize)
								{
									$Db_object->query("
										UPDATE " . $tableprefix . "attachment
										SET
										filesize = " . $this->get_value('nonmandatory', 'filesize')  . "
										WHERE attachmentid = {$attachment_id}
									");

									return $attachment_id;
								}
							}
						}
						return false;
					}
					default :
					{
						// Shouldn't ever get here
						return false;
					}
				}
			}

			// Postgres Database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}

	/**
	* Imports the current objects values as a PMtext and returns the insert_id
	*string
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	int	insert_id
	*/
	function import_pm_text(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$Db_object->query("
					INSERT INTO " . $tableprefix . "pmtext
					(
						importpmid, fromuserid, title, message,
						touserarray, fromusername, iconid,
						dateline, showsignature, allowsmilie
					)
					VALUES
					(
						'" . $this->get_value('mandatory', 'importpmid') . "',
						'" . $this->get_value('mandatory', 'fromuserid') . "',
						'" . addslashes($this->get_value('mandatory', 'title')) . "',
						'" . addslashes($this->get_value('mandatory', 'message')) . "',
						'" . $this->get_value('mandatory', 'touserarray') . "',
						'" . addslashes($this->get_value('nonmandatory', 'fromusername')) . "',
						'" . $this->get_value('nonmandatory', 'iconid') . "',
						'" . $this->get_value('nonmandatory', 'dateline') . "',
						'" . $this->get_value('nonmandatory', 'showsignature') . "',
						'" . $this->get_value('nonmandatory', 'allowsmilie') . "'
					)
				");

				if ($Db_object->affected_rows())
				{
					return $Db_object->insert_id();
				}
				else
				{
					return false;
				}
			}

			// Postgres Database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Imports the current objects values as a PM
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	boolean
	*/
	function import_pm(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				if(!$this->get_value('mandatory', 'importpmid'))
				{
					$importpmid = 1;
				}
				else
				{
					$importpmid = $this->get_value('mandatory', 'importpmid');
				}

				if(!$this->get_value('mandatory', 'userid'))
				{
					$this->set_value('mandatory', 'userid', '0');
				}

				if(!$this->get_value('nonmandatory', 'folderid'))
				{
					$this->set_value('nonmandatory', 'folderid', '0');
				}

				if(!$this->get_value('nonmandatory', 'messageread'))
				{
					$this->set_value('nonmandatory', 'messageread', '0');
				}

				$Db_object->query("
					INSERT INTO " . $tableprefix . "pm
					(
						pmtextid, userid, folderid, messageread, importpmid
					)
					VALUES
					(
						" . $this->get_value('mandatory', 'pmtextid') . ",
						" . $this->get_value('mandatory', 'userid') . ",
						" . $this->get_value('nonmandatory', 'folderid') . ",
						" . $this->get_value('nonmandatory', 'messageread') . ",
						" . $importpmid . "
					)
				");

				if ($Db_object->affected_rows())
				{
					return $Db_object->insert_id();
				}
				else
				{
					return false;
				}
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Imports the current object as a vB3 avatar
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	boolean
	*/
	function import_vb3_avatar(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$Db_object->query("
					INSERT INTO " . $tableprefix . "avatar
					(
						importavatarid, title, minimumposts, avatarpath, imagecategoryid, displayorder
					)
					VALUES
					(
						'" . $this->get_value('mandatory', 'importavatarid') . "',
						'" . addslashes($this->get_value('nonmandatory', 'title')) . "',
						'" . $this->get_value('nonmandatory', 'minimumposts') . "',
						'" . $this->get_value('nonmandatory', 'avatarpath') . "',
						'" . $this->get_value('nonmandatory', 'imagecategoryid') . "',
						'" . $this->get_value('nonmandatory', 'displayorder') . "'
					)
				");

				if ($Db_object->affected_rows())
				{
					return $Db_object->insert_id();
				}
				else
				{
					return false;
				}
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Imports the current object as a vB3 avatar
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	boolean
	*/
	function import_vb3_customavatar(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				
				#$size = getimagesize($this->get_value('nonmandatory', 'filedata'));

				if(!$width = $this->get_value('nonmandatory', 'width'))
				{
					$width = 80;
				}
				
				if(!$height = $this->get_value('nonmandatory', 'height'))
				{
					$height = 80;
				}
				
				if(!$file_sz = $this->get_value('nonmandatory', 'filesize'))
				{
					if(!$file_sz = @filesize($this->get_value('nonmandatory', 'filedata')))
					{
						$file_sz = 0;
					}
				}

				$sql ="
					REPLACE INTO " . $tableprefix . "customavatar
					(
						importcustomavatarid, userid, filedata, dateline, filename, visible, filesize, width, height
					)
					VALUES
					(
						'" . $this->get_value('mandatory', 'importcustomavatarid') . "',
						'" . $this->get_value('nonmandatory', 'userid') . "',
						'" . $this->get_value('nonmandatory', 'filedata') . "',
						'" . $this->get_value('nonmandatory', 'dateline') . "',
						'" . addslashes($this->get_value('nonmandatory', 'filename')) . "',
						'" . $this->get_value('nonmandatory', 'visible') . "',
						'" . $file_sz . "',
						'" . $width . "',
						'" . $height . "'
					)
				";

				if ($Db_object->query($sql))
				{
					return true;
				}
				else
				{
					return false;
				}
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Imports the current objects values as a buddy or ignore value, needs an array of :
	* $user('userid' => 'vbuserid'
	*		'buddylist' => space delimited buddy ids
	*		'ignorelist' => space delimited ignore ids
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	boolean
	*/
	function import_buddy_ignore(&$Db_object, &$databasetype, &$tableprefix, $user)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				if ($Db_object->query_first("SELECT userid FROM " . $tableprefix . "usertextfield WHERE userid = '$user[userid]'"))
				{
					// The user is there
				}
				else
				{
					$Db_object->query("INSERT INTO " . $tableprefix ."usertextfield (userid) VALUES ('$user[userid]')");
					if ($Db_object->affected_rows())
					{
						// It went in
					}
					else
					{
						return false;
					}
				}

				$sql = array();

				// add to buddy list
				if ($user['buddylist'] != '')
				{
					$sql[] = "buddylist = CONCAT(buddylist, ' $user[buddylist]')";
				}

				// add to ignore list
				if ($user['ignorelist'] != '')
				{
					$sql[] = "ignorelist = CONCAT(ignorelist,' $user[ignorelist]')";
				}

				if (!empty($sql))
				{
					$Db_object->query("UPDATE " . $tableprefix . "usertextfield SET " . implode(', ', $sql) . " WHERE userid = '$user[userid]'");

					return ($Db_object->affected_rows() > 0);
				}
				else
				{
					return true; // They were adding blank lists to a users, 0+0=0 == true;
				}
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Imports the an arrary as a ban list in various formats $key => $value, $int => $data
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	boolean
	*/
	function import_ban_list(&$Db_object, &$databasetype, &$tableprefix, $list, $type)
	{
		if (empty($list))
		{
			return true;
		}

		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$sql = '';
				$internal_list = '';

				switch ($type)
				{
					case 'emaillist':
					{
						foreach($list as $key => $data)
						{
							$internal_list .= $data . " ";
						}
						// For datastore opposed to setting table if it ever gets used
						// $sql = "UPDATE " . $tableprefix . "settings SET value=CONCAT(value,' " . $list . "') WHERE varname='banemail'";

						$sql = "UPDATE " . $tableprefix . "datastore SET data = CONCAT(data, '$internal_list') WHERE title = 'banemail'";
					}
					break;

					case 'iplist':
					{
						foreach($list as $key => $ip)
						{
							$internal_list .= $ip . " ";
						}
						// For datastore opposed to setting table
						$sql = "UPDATE " . $tableprefix . "setting SET value = CONCAT(value, ' $internal_list') WHERE varname = 'banip'";
					}
					break;

					case 'namebansfull':
					{
						$user_id_list = array();
						foreach ($list as $key => $vb_user_name)
						{
							$banned_userid = $Db_object->query_first("SELECT userid FROM " . $tableprefix . "user WHERE username = '$vb_user_name'");

							$user_id_list[] = $banned_userid['userid'];
						}

						return $this->import_ban_list($Db_object, $databasetype, $tableprefix, $user_id_list, 'userid');
					}
					break;

					case 'userid':
					{
						$banned_group_id = $Db_object->query_first("SELECT usergroupid FROM " . $tableprefix . "usergroup WHERE title= 'Banned Users'");

						if($banned_group_id['usergroupid'] != null)
						{
							foreach($list as $key => $banned_user_id)
							{
								$Db_object->query("UPDATE " . $tableprefix . "user SET membergroupids = CONCAT(membergroupids, ' $banned_group_id[usergroupid]') WHERE userid = '$banned_user_id'");
							}
						}
						return true;
					}
					break;

					default:
					{
						return false;
					}
				}

				$Db_object->query($sql);
				return ($Db_object->affected_rows() > 0);
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Imports the current objects values as a Post
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	boolean
	*/
	function import_post(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$sql = "
					INSERT INTO " . $tableprefix . "post
					(
						threadid, userid, importthreadid,
						parentid, username, title,
						dateline, pagetext, allowsmilie,
						showsignature, ipaddress, iconid,
						visible, attach, importpostid
					)
					VALUES
					(
						'" . $this->get_value('mandatory', 'threadid') . "',
						'" . $this->get_value('mandatory', 'userid') . "',
						'" . $this->get_value('mandatory', 'importthreadid') . "',
						'" . $this->get_value('nonmandatory', 'parentid') . "',
						'" . addslashes($this->get_value('nonmandatory', 'username')) . "',
						'" . addslashes($this->get_value('nonmandatory', 'title')) . "',
						'" . $this->get_value('nonmandatory', 'dateline') . "',
						'".  addslashes($this->get_value('nonmandatory', 'pagetext')) . "',
						'" . $this->get_value('nonmandatory', 'allowsmilie') . "',
						'" . $this->get_value('nonmandatory', 'showsignature') . "',
						'" . $this->get_value('nonmandatory', 'ipaddress') . "',
						'" . $this->get_value('nonmandatory', 'iconid') . "',
						'" . $this->get_value('nonmandatory', 'visible') . "',
						'" . addslashes($this->get_value('nonmandatory', 'attach')) . "',
						'" . $this->get_value('nonmandatory', 'importpostid') . "'
					)
				";
				$Db_object->query($sql);
				$post_id = $Db_object->insert_id();

				return $post_id;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Imports the current objects values as a User
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	boolean
	*/
	function import_user(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				// Auto email associate
				if ($this->_auto_email_associate)
				{
					// Do a search for the email address to find the user to match this imported one to :
					$email_match = $Db_object->query_first("SELECT userid FROM " . $tableprefix . "user WHERE email='". $this->get_value('mandatory', 'email') . "'");


					if ($email_match)
					{

						if($this->associate_user($Db_object, $databasetype, $tableprefix, $this->get_value('mandatory', 'importuserid'), $email_match["userid"]))
						{
							// We matched the email address and associated propperly
							$result['automerge'] = true;
							return $result;
						}
						else
						{
							// Hmmm found the email but didn't associate !!
						}
					}
					else
					{
						// There is no email to match with, so return nothing and let the user import normally.
					}
				}

				$newpassword = '';
				$salt =	$this->fetch_user_salt();

				if ($this->_password_md5_already)
				{
					 $newpassword = md5($this->get_value('nonmandatory', 'password') . $salt);
				}
				else
				{
					$newpassword = md5(md5($this->get_value('nonmandatory', 'password')) . $salt);
				}

				// Link the admins
				if(strtolower($this->get_value('mandatory', 'username')) == 'admin')
				{
					$this->set_value('mandatory', 'username', 'imported_admin');
				}

				// If there is a dupe username pre_pend "imported_"
				$double_name = $Db_object->query("SELECT username FROM " . $tableprefix . "user WHERE username='". addslashes($this->get_value('mandatory', 'username')) . "'");

				if($Db_object->num_rows($double_name))
				{
					$this->set_value('mandatory', 'username', 'imported_' . $this->get_value('mandatory', 'username'));
				}


				$sql = "
					INSERT INTO	" . $tableprefix . "user
					(
						username, email, usergroupid,
						importuserid, password, salt,
						passworddate, options, homepage,
						posts, joindate, icq,
						daysprune, aim, membergroupids,
						displaygroupid, styleid, parentemail,
						yahoo, showvbcode, usertitle,
						customtitle, lastvisit, lastactivity,
						lastpost, reputation, reputationlevelid,
						timezoneoffset, pmpopup, avatarid,
						avatarrevision, birthday, birthday_search, maxposts,
						startofweek, ipaddress, referrerid,
						languageid, msn, emailstamp,
						threadedmode, pmtotal, pmunread,
						autosubscribe, profilepicrevision 
					)
					VALUES
					(
						'" . addslashes($this->get_value('mandatory', 'username')) . "',
						'" . addslashes($this->get_value('mandatory', 'email')) . "',
						'" . $this->get_value('mandatory', 'usergroupid') . "',
						'" . $this->get_value('mandatory', 'importuserid') . "',
						'" . $newpassword . "',
						'" . addslashes($salt) . "',
						NOW(),
						'" . $this->get_value('nonmandatory', 'options') . "',
						'" . addslashes($this->get_value('nonmandatory', 'homepage')) . "',
						'" . $this->get_value('nonmandatory', 'posts') . "',
						'" . $this->get_value('nonmandatory', 'joindate') . "',
						'" . addslashes($this->get_value('nonmandatory', 'icq')) . "',
						'" . $this->get_value('nonmandatory', 'daysprune') . "',
						'" . addslashes($this->get_value('nonmandatory', 'aim')) . "',
						'" . $this->get_value('nonmandatory', 'membergroupids') . "',
						'" . $this->get_value('nonmandatory', 'displaygroupid') . "',
						'" . $this->get_value('nonmandatory', 'styleid') . "',
						'" . $this->get_value('nonmandatory', 'parentemail') . "',
						'" . addslashes($this->get_value('nonmandatory', 'yahoo')) . "',
						'" . $this->get_value('nonmandatory', 'showvbcode') . "',
						'" . addslashes($this->get_value('nonmandatory', 'usertitle')) . "',
						" . intval($this->get_value('nonmandatory', 'customtitle')) . ",
						'" . $this->get_value('nonmandatory', 'lastvisit') . "',
						'" . $this->get_value('nonmandatory', 'lastactivity') . "',
						'" . $this->get_value('nonmandatory', 'lastpost') . "',
						'" . $this->get_value('nonmandatory', 'reputation') . "',
						'" . $this->get_value('nonmandatory', 'reputationlevelid') . "',
						'" . $this->get_value('nonmandatory', 'timezoneoffset') . "',
						'" . $this->get_value('nonmandatory', 'pmpopup') . "',
						'" . $this->get_value('nonmandatory', 'avatarid') . "',
						'" . $this->get_value('nonmandatory', 'avatarrevision') . "',
						'" . $this->get_value('nonmandatory', 'birthday') . "',
						'" . $this->get_value('nonmandatory', 'birthday_search') . "',
						'" . $this->get_value('nonmandatory', 'maxposts') . "',
						'" . $this->get_value('nonmandatory', 'startofweek') . "',
						'" . $this->get_value('nonmandatory', 'ipaddress') . "',
						'" . $this->get_value('nonmandatory', 'referrerid') . "',
						'" . $this->get_value('nonmandatory', 'languageid') . "',
						'" . addslashes($this->get_value('nonmandatory', 'msn')) . "',
						'" . addslashes($this->get_value('nonmandatory', 'emailstamp')) . "',
						'" . $this->get_value('nonmandatory', 'threadedmode') . "',
						'" . $this->get_value('nonmandatory', 'pmtotal') . "',
						'" . $this->get_value('nonmandatory', 'pmunread') . "',
						'" . $this->get_value('nonmandatory', 'autosubscribe') . "',
						'" . $this->get_value('nonmandatory', 'profilepicrevision') . "'
					)
				";

				$userdone = $Db_object->query($sql);
				$userid = $Db_object->insert_id();

				if ($userdone)
				{
					$exists = $Db_object->query_first("SELECT userid FROM " . $tableprefix . "usertextfield WHERE userid = $userid");

					if (!$exists)
					{
						if (!$Db_object->query("INSERT INTO " . $tableprefix . "usertextfield (userid) VALUES ($userid)"))
						{
							$this->_failedon = "usertextfield fill";
							return false;
						}

						if (!$Db_object->query("INSERT INTO " . $tableprefix . "userfield (userid) VALUES ($userid)"))
						{
							$this->_failedon = "userfield fill";
							return false;
						}
					}

					if ($this->_has_custom_types)
					{
						foreach ($this->get_custom_values() as $key => $value)
						{
							if (!$this->import_user_field_value($Db_object, $databasetype, $tableprefix, $key, $value, $userid))
							{
								$this->_failedon = "import_user_field_value - $key - $value - $userid";
								return false;
							}
						}
					}

					if ($this->get_value('nonmandatory', 'avatar') != NULL)
					{
						$this->import_avatar($Db_object, $databasetype, $tableprefix,$userid,$this->get_value('nonmandatory', 'avatar'));
					}

					if ($this->_has_default_values)
					{
						foreach ($this->get_default_values() as  $key => $value)
						{
							if ($key != 'signature')
							{
								if (!$this->import_user_field_value($Db_object, $databasetype, $tableprefix, $key, $value, $userid))
								{
									$this->_failedon = "import_user_field_value - $key - $value - $userid";
									return false;
								}
							}
						}
					}

					if (array_key_exists('signature',$this->_default_values))
					{
						if (!$Db_object->query("UPDATE " . $tableprefix . "usertextfield SET signature='" . addslashes($this->_default_values['signature']) . "' WHERE userid='" . $userid ."'"))
						{
							$this->_failedon = "usertextfield SET signature";
							return false;
						}
					}
				}
				else
				{
					return false;
				}

				return $userid;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}

	// Overridden to maintain salt and password details
	function import_vb3_user(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				// TODO: Still need to check and see if all the current usersnames being imported are unique
				if(strtolower($this->get_value('mandatory', 'username')) == 'admin')
				{
					$this->set_value('mandatory', 'username', 'admin_old');
				}

				// Auto email associate
				if ($this->_auto_email_associate)
				{
					// Do a search for the email address to find the user to match this imported one to :
					$email_match = $Db_object->query_first("SELECT userid FROM " . $tableprefix . "user WHERE email='". $this->get_value('mandatory', 'email') . "'");


					if ($email_match)
					{
						if($this->associate_user($Db_object, $databasetype, $tableprefix, $this->get_value('mandatory', 'importuserid'), $email_match["userid"]))
						{
							// We matched the email address and associated propperly
							$result['automerge'] = true;
							return $result;
						}
						else
						{
							// Hmmm found the email but didn't associate !!
						}
					}
					else
					{
						// There is no email to match with, so return nothing and let the user import normally.
					}
				}

				// If there is a dupe username pre_pend "imported_"
				$double_name = $Db_object->query("SELECT username FROM " . $tableprefix . "user WHERE username='". addslashes($this->get_value('mandatory', 'username')) . "'");

				if($Db_object->num_rows($double_name))
				{
					$this->set_value('mandatory', 'username', 'imported_' . $this->get_value('mandatory', 'username'));
				}				
				
				$sql = "
					INSERT INTO	" . $tableprefix . "user
					(
						username, email, usergroupid,
						importuserid, password, salt,
						passworddate, options, homepage,
						posts, joindate, icq,
						daysprune, aim, membergroupids,
						displaygroupid, styleid, parentemail,
						yahoo, showvbcode, usertitle,
						customtitle, lastvisit, lastactivity,
						lastpost, reputation, reputationlevelid,
						timezoneoffset, pmpopup, avatarid,
						avatarrevision, birthday, birthday_search, maxposts,
						startofweek, ipaddress, referrerid,
						languageid, msn, emailstamp,
						threadedmode, pmtotal, pmunread,
						autosubscribe, profilepicrevision 
					)
					VALUES
					(
						'" . addslashes($this->get_value('mandatory', 'username')) . "',
						'" . addslashes($this->get_value('mandatory', 'email')) . "',
						'" . $this->get_value('mandatory', 'usergroupid') . "',
						'" . $this->get_value('mandatory', 'importuserid') . "',
						'" . addslashes($this->get_value('nonmandatory', 'password')) . "',
						'" . addslashes($this->get_value('nonmandatory', 'salt')) . "',
						'" . $this->get_value('nonmandatory', 'passworddate') . "',
						'" . $this->get_value('nonmandatory', 'options') . "',
						'" . addslashes($this->get_value('nonmandatory', 'homepage')) . "',
						'" . $this->get_value('nonmandatory', 'posts') . "',
						'" . $this->get_value('nonmandatory', 'joindate') . "',
						'" . addslashes($this->get_value('nonmandatory', 'icq')) . "',
						'" . $this->get_value('nonmandatory', 'daysprune') . "',
						'" . addslashes($this->get_value('nonmandatory', 'aim')) . "',
						'" . $this->get_value('nonmandatory', 'membergroupids') . "',
						'" . $this->get_value('nonmandatory', 'displaygroupid') . "',
						'" . $this->get_value('nonmandatory', 'styleid') . "',
						'" . addslashes($this->get_value('nonmandatory', 'parentemail')) . "',
						'" . addslashes($this->get_value('nonmandatory', 'yahoo')) . "',
						'" . $this->get_value('nonmandatory', 'showvbcode') . "',
						'" . addslashes($this->get_value('nonmandatory', 'usertitle')) . "',
						'" . addslashes($this->get_value('nonmandatory', 'customtitle')) . "',
						'" . $this->get_value('nonmandatory', 'lastvisit') . "',
						'" . $this->get_value('nonmandatory', 'lastactivity') . "',
						'" . $this->get_value('nonmandatory', 'lastpost') . "',
						'" . $this->get_value('nonmandatory', 'reputation') . "',
						'" . $this->get_value('nonmandatory', 'reputationlevelid') . "',
						'" . $this->get_value('nonmandatory', 'timezoneoffset') . "',
						'" . $this->get_value('nonmandatory', 'pmpopup') . "',
						'" . $this->get_value('nonmandatory', 'avatarid') . "',
						'" . $this->get_value('nonmandatory', 'avatarrevision') . "',
						'" . $this->get_value('nonmandatory', 'birthday') . "',
						'" . $this->get_value('nonmandatory', 'birthday_search') . "',
						'" . $this->get_value('nonmandatory', 'maxposts') . "',
						'" . $this->get_value('nonmandatory', 'startofweek') . "',
						'" . $this->get_value('nonmandatory', 'ipaddress') . "',
						'" . $this->get_value('nonmandatory', 'referrerid') . "',
						'" . $this->get_value('nonmandatory', 'languageid') . "',
						'" . addslashes($this->get_value('nonmandatory', 'msn')) . "',
						'" . $this->get_value('nonmandatory', 'emailstamp') . "',
						'" . $this->get_value('nonmandatory', 'threadedmode') . "',
						'" . $this->get_value('nonmandatory', 'pmtotal') . "',
						'" . $this->get_value('nonmandatory', 'pmunread') . "',
						'" . $this->get_value('nonmandatory', 'autosubscribe') . "',
						'" . $this->get_value('nonmandatory', 'profilepicrevision') . "'
					)
				";

				$userdone = $Db_object->query($sql);
				$userid = $Db_object->insert_id();

				if ($userdone)
				{
					$exists = $Db_object->query_first("SELECT userid FROM " . $tableprefix . "usertextfield WHERE userid = $userid");

					if (!$exists)
					{
						if (!$Db_object->query("INSERT INTO " . $tableprefix . "usertextfield (userid) VALUES ($userid)"))
						{
							$this->_failedon = "usertextfield fill";
							return false;
						}

						if (!$Db_object->query("INSERT INTO " . $tableprefix . "userfield (userid) VALUES ($userid)"))
						{
							$this->_failedon = "userfield fill";
							return false;
						}
					}

					if ($this->_has_default_values)
					{
						foreach ($this->get_default_values() as  $key => $value)
						{
							if ($key != 'signature')
							{
								if (!$this->import_user_field_value($Db_object, $databasetype, $tableprefix, $key, $value, $userid))
								{
									$this->_failedon = "import_user_field_value - $key - $value - $userid";
									return false;
								}
							}
						}
					}

					if (array_key_exists('signature',$this->_default_values))
					{
						if (!$Db_object->query("UPDATE " . $tableprefix . "usertextfield SET signature='" . addslashes($this->_default_values['signature']) . "' WHERE userid='" . $userid ."'"))
						{
							$this->_failedon = "usertextfield SET signature";
							return false;
						}
					}
				}
				else
				{
					return false;
				}

				return $userid;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}

	/**
	* Copy of the function_user.php fetch_user_salt
	*
	* @param	string	int		The lenght of the salt
	*
	* @return	string
	*/
	function fetch_user_salt($length = 3)
	{
		$salt = '';
		for ($i = 0; $i < $length; $i++)
		{
			$salt .= chr(rand(32, 126));
		}
		return $salt;
	}


	/**
	* Imports the users avatar from a local file or URL.
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	string	int		The userid
	* @param	string	int		The location of the avatar file
	*
	* @return	insert_id
	*/
	function import_avatar(&$Db_object, &$databasetype, &$tableprefix, $userid, $file)
	{
		if ($filenum = @fopen($file, 'r'))
		{
			$contents = $this->vb_file_get_contents($file);

			$size = getimagesize($file);

			if($size)
			{
				$width 	= $size[0];
				$height = $size[1];
			}
			else
			{
				$width 	= '0';
				$height = '0';
			}			
			
			if(!$file_sz = @filesize($file))
			{
				$file_sz = 0;
			}
			
			$urlbits = parse_url($file);
			$pathbits = pathinfo($urlbits['path']);

			$Db_object->query("
				INSERT INTO " . $tableprefix . "customavatar
					(userid, filedata, dateline, filename, filesize, width, height)
				VALUES
				(
					$userid,
					'" . addslashes($contents) . "',
					NOW(),
					'" . addslashes($pathbits['basename'])."',
					". $file_sz . ",
					{$width},
					{$height}
				)
			");
			
			if ($Db_object->affected_rows())
			{
				return $Db_object->insert_id();
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
	* Imports a usergroup
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	insert_id
	*/
	function import_user_group(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			case 'mysql':
			{
				$Db_object->query("
					INSERT INTO " . $tableprefix . "usergroup
					(
						importusergroupid, title, description,
						usertitle, passwordexpires, passwordhistory,
						pmquota, pmsendmax, pmforwardmax,
						opentag, closetag, canoverride,
						ispublicgroup, forumpermissions, pmpermissions,
						calendarpermissions, wolpermissions, adminpermissions,
						genericpermissions, genericoptions, attachlimit,
						avatarmaxwidth, avatarmaxheight, avatarmaxsize,
						profilepicmaxwidth, profilepicmaxheight, profilepicmaxsize
					)
					VALUES
					(
						'" . $this->get_value('mandatory', 'importusergroupid') . "',
						'" . addslashes($this->get_value('nonmandatory','title')) . "',
						'" . addslashes($this->get_value('nonmandatory','description')) . "',
						'" . addslashes($this->get_value('nonmandatory','usertitle')) . "',
						'" . $this->get_value('nonmandatory','passwordexpires') . "',
						'" . $this->get_value('nonmandatory','passwordhistory') . "',
						'" . $this->get_value('nonmandatory','pmquota') . "',
						'" . $this->get_value('nonmandatory','pmsendmax') . "',
						'" . $this->get_value('nonmandatory','pmforwardmax') . "',
						'" . addslashes($this->get_value('nonmandatory','opentag')) . "',
						'" . addslashes($this->get_value('nonmandatory','closetag')) . "',
						'" . $this->get_value('nonmandatory','canoverride') . "',
						'" . $this->get_value('nonmandatory','ispublicgroup') . "',
						'" . $this->get_value('nonmandatory','forumpermissions') . "',
						'" . $this->get_value('nonmandatory','pmpermissions') . "',
						'" . $this->get_value('nonmandatory','calendarpermissions') . "',
						'" . $this->get_value('nonmandatory','wolpermissions') . "',
						'" . $this->get_value('nonmandatory','adminpermissions') . "',
						'" . $this->get_value('nonmandatory','genericpermissions') . "',
						'" . $this->get_value('nonmandatory','genericoptions') . "',
						'" . $this->get_value('nonmandatory','attachlimit') . "',
						'" . $this->get_value('nonmandatory','avatarmaxwidth') . "',
						'" . $this->get_value('nonmandatory','avatarmaxheight') . "',
						'" . $this->get_value('nonmandatory','avatarmaxsize') . "',
						'" . $this->get_value('nonmandatory','profilepicmaxwidth') . "',
						'" . $this->get_value('nonmandatory','profilepicmaxheight') . "',
						'" . $this->get_value('nonmandatory','profilepicmaxsize') . "'
					)
				");
				if ($Db_object->affected_rows())
				{
					return $Db_object->insert_id();
				}
				else
				{
					return false;
				}
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Imports the current objects values as a Forum
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	boolean
	*/
	function import_category(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				// Catch the legacy importers that haven't been
				// updated				
				if (!$this->get_value('mandatory', 'options'))
				{
					$this->set_value('mandatory', 'options', $this->_default_cat_permissions);
				}
				
				$result = $Db_object->query("
					INSERT INTO " . $tableprefix . "forum
					(
						styleid, title, description,
						options, daysprune, displayorder,
						parentid, importforumid, importcategoryid,
						title_clean, description_clean
					)
					VALUES
					(
						'" . $this->get_value('mandatory', 'styleid') . "',
						'" . addslashes($this->get_value('mandatory', 'title')) . "',
						'" . addslashes($this->get_value('nonmandatory', 'description')) . "',
						" . $this->get_value('mandatory', 'options') . ",
						'30',
						'" . $this->get_value('mandatory', 'displayorder') . "',
						'-1',
						'" . $this->get_value('mandatory', 'importforumid') . "',
						'" . $this->get_value('mandatory', 'importcategoryid') . "',
						'" . addslashes($this->get_value('mandatory', 'title')) . "',
						'" . addslashes($this->get_value('nonmandatory', 'description')) . "'
					)
				");
				$categoryid = $Db_object->insert_id($result);

				if ($result)
				{
					$Db_object->query("UPDATE {$tableprefix}forum SET parentlist = '$categoryid,-1' WHERE forumid = '$categoryid'");
					if ($Db_object->affected_rows())
					{
						return $categoryid;
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

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Imports the current objects values as a Forum
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	boolean
	*/
	function import_forum(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				// Catch the legacy importers that haven't been
				// updated
				if (!$this->get_value('mandatory', 'options'))
				{
					$this->set_value('mandatory', 'options', $this->_default_forum_permissions);
				}

				$result = $Db_object->query("
					INSERT INTO " . $tableprefix . "forum
					(
						styleid, title, options,
						displayorder, parentid, importforumid,
						importcategoryid, description, replycount,
						lastpost, lastposter, lastthread,
						lastthreadid, lasticonid, threadcount,
						daysprune, newpostemail, newthreademail,
						parentlist, password, link, childlist,
						title_clean, description_clean
					)
					VALUES
					(
						'" . $this->get_value('mandatory', 'styleid') . "',
						'" . addslashes($this->get_value('mandatory', 'title')) . "',
						" . $this->get_value('mandatory', 'options') . ",
						'" . $this->get_value('mandatory', 'displayorder') . "',
						'" . $this->get_value('mandatory', 'parentid') . "',
						'" . $this->get_value('mandatory', 'importforumid') . "',
						'" . $this->get_value('mandatory', 'importcategoryid') . "',
						'" . addslashes($this->get_value('nonmandatory', 'description')) . "',
						'" . $this->get_value('nonmandatory', 'replycount') . "',
						'" . $this->get_value('nonmandatory', 'lastpost') . "',
						'" . addslashes($this->get_value('nonmandatory', 'lastposter')) . "',
						'" . $this->get_value('nonmandatory', 'lastthread') . "',
						'" . $this->get_value('nonmandatory', 'lastthreadid') . "',
						'" . $this->get_value('nonmandatory', 'lasticonid') . "',
						'" . $this->get_value('nonmandatory', 'threadcount') . "',
						'" . $this->get_value('nonmandatory', 'daysprune') . "',
						'" . $this->get_value('nonmandatory', 'newpostemail') . "',
						'" . $this->get_value('nonmandatory', 'newthreademail') . "',
						'" . $this->get_value('nonmandatory', 'parentlist') . "',
						'" . $this->get_value('nonmandatory', 'password') . "',
						'" . $this->get_value('nonmandatory', 'link') . "',
						'" . $this->get_value('nonmandatory', 'childlist') . "',
						'" . addslashes($this->get_value('mandatory', 'title')) . "',
						'" . addslashes($this->get_value('nonmandatory', 'description')) . "'
					)
				");
				$forumid = $Db_object->insert_id($result);

				if ($result)
				{
					$Db_object->query("UPDATE {$tableprefix}forum SET parentlist='$forumid,-1' WHERE forumid='$forumid'");
					if ($Db_object->affected_rows())
					{
						return $forumid;
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

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}

	function import_vb2_forum(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$result = $Db_object->query("
					INSERT INTO " . $tableprefix . "forum
					(
						styleid, title, options,
						displayorder, parentid, importforumid,
						description, replycount,
						lastpost, lastposter, lastthread,
						lastthreadid, lasticonid, threadcount,
						daysprune, newpostemail, newthreademail,
						parentlist, password, link, childlist
					)
					VALUES
					(
						'" . $this->get_value('mandatory', 'styleid') . "',
						'" . addslashes($this->get_value('mandatory', 'title')) . "',
						" . $this->get_value('mandatory', 'options') . ",
						'" . $this->get_value('mandatory', 'displayorder') . "',
						'" . $this->get_value('mandatory', 'parentid') . "',
						'" . $this->get_value('mandatory', 'importforumid') . "',
						'" . addslashes($this->get_value('nonmandatory', 'description')) . "',
						'" . $this->get_value('nonmandatory', 'replycount') . "',
						'" . $this->get_value('nonmandatory', 'lastpost') . "',
						'" . addslashes($this->get_value('nonmandatory', 'lastposter')) . "',
						'" . $this->get_value('nonmandatory', 'lastthread') . "',
						'" . $this->get_value('nonmandatory', 'lastthreadid') . "',
						'" . $this->get_value('nonmandatory', 'lasticonid') . "',
						'" . $this->get_value('nonmandatory', 'threadcount') . "',
						'" . $this->get_value('nonmandatory', 'daysprune') . "',
						'" . $this->get_value('nonmandatory', 'newpostemail') . "',
						'" . $this->get_value('nonmandatory', 'newthreademail') . "',
						'" . $this->get_value('nonmandatory', 'parentlist') . "',
						'" . $this->get_value('nonmandatory', 'password') . "',
						'" . $this->get_value('nonmandatory', 'link') . "',
						'" . $this->get_value('nonmandatory', 'childlist') . "'
					)
				");
				$forumid = $Db_object->insert_id($result);

				return $forumid;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}

	/**
	* Imports the current objects values as a Thread
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	boolean
	*/
	function import_thread(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$Db_object->query("
					INSERT INTO " . $tableprefix . "thread
					(
						forumid, title, importforumid,
						importthreadid, firstpostid, lastpost,
						pollid, open, replycount,
						postusername, postuserid, lastposter,
						dateline, views, iconid,
						notes, visible, sticky,
						votenum, votetotal, attach, similar,
						hiddencount
					)
					VALUES
					(
						'" . $this->get_value('mandatory', 'forumid') . "',
						'" . addslashes($this->get_value('mandatory', 'title')) . "',
						'" . $this->get_value('mandatory', 'importforumid') . "',
						'" . $this->get_value('mandatory', 'importthreadid') . "',
						'" . $this->get_value('nonmandatory', 'firstpostid') . "',
						'" . addslashes($this->get_value('nonmandatory', 'lastpost')) . "',
						'" . $this->get_value('nonmandatory', 'pollid') . "',
						'" . $this->get_value('nonmandatory', 'open')  . "',
						'" . $this->get_value('nonmandatory', 'replycount') . "',
						'" . addslashes($this->get_value('nonmandatory', 'postusername')) . "',
						'" . $this->get_value('nonmandatory', 'postuserid') . "',
						'" . addslashes($this->get_value('nonmandatory', 'lastposter')) . "',
						'" . $this->get_value('nonmandatory', 'dateline') . "',
						'" . $this->get_value('nonmandatory', 'views') . "',
						'" . $this->get_value('nonmandatory', 'iconid') . "',
						'" . addslashes($this->get_value('nonmandatory', 'notes')) . "',
						'" . $this->get_value('nonmandatory', 'visible') . "',
						'" . $this->get_value('nonmandatory', 'sticky') . "',
						'" . $this->get_value('nonmandatory', 'votenum') . "',
						'" . $this->get_value('nonmandatory', 'votetotal') . "',
						'" . addslashes($this->get_value('nonmandatory', 'attach')) . "',
						'" . addslashes($this->get_value('nonmandatory', 'similar')) . "',
						'" . $this->get_value('nonmandatory', 'hiddencount') . "'
					)
				");

				if ($Db_object->affected_rows())
				{
					return $Db_object->insert_id();
				}
				else
				{
					return false;
				}
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Imports the current objects values as a Moderator
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	boolean
	*/
	function import_moderator(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$result = $Db_object->query("
					INSERT INTO " . $tableprefix . "moderator
					(
					userid, forumid, importmoderatorid, permissions
					)
					VALUES
					(
						'" . $this->get_value('mandatory', 'userid') . "',
						'" . $this->get_value('mandatory', 'forumid') . "',
						'" . $this->get_value('mandatory', 'importmoderatorid') . "',
						'" . $this->get_value('nonmandatory', 'permissions') . "'
					)
				");

				if ($Db_object->affected_rows())
				{
					return $Db_object->insert_id();
				}
				else
				{
					return false;
				}
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Imports the current objects values as a Custom profile pic
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	boolean
	*/
	function import_custom_profile_pic(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$size = @getimagesize($this->get_value('nonmandatory', 'filedata'));

				if($size)
				{
					$width 	= $size[0];
					$height = $size[1];
				}
				else
				{
					$width 	= '0';
					$height = '0';
				}			
				
				if(!$file_sz = @filesize($file))
				{
					$file_sz = 0;
				}
				
				$sql ="
					INSERT INTO
					" . $tableprefix . "customprofilepic
					(
						importcustomprofilepicid, userid, filedata, 
						dateline, filename, visible, filesize, height, width
					)
					VALUES
					(
						'" . $this->get_value('mandatory', 'importcustomprofilepicid') . "',
						'" . $this->get_value('nonmandatory', 'userid') . "',
						'" . $this->get_value('nonmandatory', 'filedata') . "',
						" . $this->get_value('nonmandatory', 'dateline') . ",
						'" . $this->get_value('nonmandatory', 'filename') . "',
						" . $this->get_value('nonmandatory', 'visible') . ",
						" . $file_sz . ",
						{$height},
						{$width}
					)
				";

				if ($Db_object->query($sql))
				{
					return true;
				}
				else
				{
					return false;
				}
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Imports a customer userfield value
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	string	mixed			The key i.e. 'surname'
	* @param	string	mixed			The value i.e. 'Hutchings'
	*
	* @return	boolean
	*/
	function import_user_field_value(&$Db_object, &$databasetype, &$tableprefix, $title, $value, $userid)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$fieldid = $Db_object->query_first("SELECT profilefieldid FROM ". $tableprefix . "profilefield WHERE title = '$title'");

				// TODO: This will break with a 0 on field id, need to handel it a lot better and have a return
				if($fieldid['profilefieldid'])
				{
					$Db_object->query("UPDATE ". $tableprefix . "userfield SET field" . $fieldid['profilefieldid'] ." = '" . addslashes($value) . "' WHERE userid = '$userid'");
				}

				return true;
				// TODO: Fix this, it dosn't work. affected_rows() Isn't picking up an UPDATE
				if ($Db_object->affected_rows())
				{
					return true;
				}
				else
				{
					return false;
				}

			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Moves a user into the administrators group
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	string	mixed			The key i.e. 'surname'
	* @param	string	mixed			The value i.e. 'Hutchings'
	*
	* @return	boolean
	*/
	function add_admin_user(&$Db_object, &$databasetype, &$tableprefix, $userid)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$fieldid = $Db_object->query_first("SELECT usergroupid FROM " . $tableprefix . "usergroup WHERE title = 'Administrators'");
				if (!$fieldid)
				{
					$fieldid['usergroupid'] = 6;
				}

				$Db_object->query("UPDATE ". $tableprefix . "user SET  membergroupids = CONCAT(membergroupids, " . $fieldid['usergroupid'] . ") WHERE userid = '$userid'");
				if ($Db_object->affected_rows())
				{
					return true;
				}
				else
				{
					return false;
				}

			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Imports a rank, has to be used incombination with import usergroup to make sense get its usergroupid
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed		The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed		The prefix to the table name i.e. 'vb3_'
	*
	* @return	false/int		The tablerow inc id
	*/
	function import_rank(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$Db_object->query("
					INSERT INTO " . $tableprefix ."ranks
					(importrankid, minposts, ranklevel, rankimg, usergroupid, type, stack, display)
					VALUES
					(
					" . $this->get_value('mandatory', 'importrankid') . ",
					" . $this->get_value('nonmandatory', 'minposts') . ",
					" . $this->get_value('nonmandatory', 'ranklevel') . ",
					'" . addslashes($this->get_value('nonmandatory', 'rankimg')) . "',
					0,
					'" . $this->get_value('nonmandatory', 'type') . "',
					'" . $this->get_value('nonmandatory', 'stack') . "',
					'" . $this->get_value('nonmandatory', 'display') . "'
					)
				");

				if ($Db_object->affected_rows())
				{
					return $Db_object->insert_id();
				}
				else
				{
					return false;
				}
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	function import_smilie_image_group(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$row_id = $Db_object->query_first("SELECT imagecategoryid FROM ". $tableprefix . "imagecategory WHERE title='Imported Smilies'");

				if (!$row_id[0])
				{
					$result = $Db_object->query("
						INSERT INTO " . $tableprefix . "imagecategory
						(title, imagetype, displayorder)
						VALUES
						(
							'" . $this->get_value('nonmandatory', 'title') . "',
							'" . $this->get_value('nonmandatory', 'imagetype') . "',
							'" . $this->get_value('nonmandatory', 'displayorder') . "'
						)
					");
					return $Db_object->insert_id();
				}
				else
				{
					return $row_id[0];
				}

			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}

	/**
	* Imports a rank, has to be used incombination with import usergroup to make sense get its usergroupid
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed		The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed		The prefix to the table name i.e. 'vb3_'
	*
	* @return	false/int		The tablerow inc id
	*/
	function import_usergroup(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$cols = $Db_object->query("describe {$tableprefix}usergroup");
				$there = false;

				while ($col = $Db_object->fetch_array($cols))
				{
					if($col['Field'] == 'pmforwardmax')
					{
						$there = true;
					}
				}

				if(!$there)
				{
					$Db_object->query("ALTER TABLE `{$tableprefix}usergroup` ADD `pmforwardmax` SMALLINT( 5 ) UNSIGNED DEFAULT '5' NOT NULL");
				}

				$Db_object->query("
					INSERT INTO " . $tableprefix ."usergroup
					(
						importusergroupid, title, description,
						usertitle, passwordexpires, passwordhistory,
						pmquota, pmsendmax, pmforwardmax,
						opentag, closetag, canoverride,
						ispublicgroup, forumpermissions, pmpermissions,
						calendarpermissions, wolpermissions, adminpermissions,
						genericpermissions, genericoptions, attachlimit,
						avatarmaxwidth, avatarmaxheight, avatarmaxsize,
						profilepicmaxwidth, profilepicmaxheight, profilepicmaxsize
					)
					VALUES
					(
						'" . $this->get_value('mandatory', 'importusergroupid') . "',
						'" . addslashes($this->get_value('nonmandatory', 'title')) . "',
						'" . addslashes($this->get_value('nonmandatory', 'description')) . "',
						'" . addslashes($this->get_value('nonmandatory', 'usertitle')) . "',
						'" . $this->get_value('nonmandatory', 'passwordexpires') . "',
						'" . $this->get_value('nonmandatory', 'passwordhistory') . "',
						'" . $this->get_value('nonmandatory', 'pmquota') . "',
						'" . $this->get_value('nonmandatory', 'pmsendmax') . "',
						'" . $this->get_value('nonmandatory', 'pmforwardmax') . "',
						'" . $this->get_value('nonmandatory', 'opentag') . "',
						'" . $this->get_value('nonmandatory', 'closetag') . "',
						'" . $this->get_value('nonmandatory', 'canoverride') . "',
						'" . $this->get_value('nonmandatory', 'ispublicgroup') . "',
						'" . $this->get_value('nonmandatory', 'forumpermissions') . "',
						'" . $this->get_value('nonmandatory', 'pmpermissions') . "',
						'" . $this->get_value('nonmandatory', 'calendarpermissions') . "',
						'" . $this->get_value('nonmandatory', 'wolpermissions') . "',
						'" . $this->get_value('nonmandatory', 'adminpermissions') . "',
						'" . $this->get_value('nonmandatory', 'genericpermissions') . "',
						'" . $this->get_value('nonmandatory', 'genericoptions') . "',
						'" . $this->get_value('nonmandatory', 'attachlimit') . "',
						'" . $this->get_value('nonmandatory', 'avatarmaxwidth') . "',
						'" . $this->get_value('nonmandatory', 'avatarmaxheight') . "',
						'" . $this->get_value('nonmandatory', 'avatarmaxsize') . "',
						'" . $this->get_value('nonmandatory', 'profilepicmaxwidth') . "',
						'" . $this->get_value('nonmandatory', 'profilepicmaxheight') . "',
						'" . $this->get_value('nonmandatory', 'profilepicmaxsize') . "'
					)
				");

				if ($Db_object->affected_rows())
				{
					return $Db_object->insert_id();
				}
				else
				{
					return false;
				}
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Returns the vBuserd id associated with an importid
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	string	mixed			The imported user id
	*
	* @return	int
	*/
	function get_vb_userid(&$Db_object, &$databasetype, &$tableprefix, $importuserid)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$result = $Db_object->query_first("SELECT userid FROM " . $tableprefix . "user WHERE importuserid = " . $importuserid);
				if ($result)
				{
					return $result['userid'];
				}
				else
				{
					return false;
				}
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Returns the username by searching on the importuserid or the userid
	*
	* @param	object	databaseobject		The database that the function is going to interact with.
	* @param	string	mixed				The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed				The prefix to the table name i.e. 'vb3_'
	* @param	string	mixed				The user id
	* @param	string	importuserid|userid	A switch to indicate if you are searching on the importuserid or the userid
	*
	* @return	int
	*/
	function get_one_username(&$Db_object, &$databasetype, &$tableprefix, $theuserid, $id='importuserid')
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				switch ($id)
				{
					case 'importuserid':
					{
						$sql = "SELECT username FROM " . $tableprefix . "user WHERE importuserid = " . $theuserid;
					}
					break;

					case 'userid':
					{
						$sql = "SELECT username FROM " . $tableprefix . "user WHERE userid = " . $theuserid;
					}
					break;

					default:
					{
						return false;
					}
				}

				$result = $Db_object->query_first($sql);

				if ($result)
				{
					return $result['username'];
				}
				else
				{
					return false;
				}
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Returns a 2D array of the users [userid][username][importuserid]
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	int		mixed			The importuserid to start at
	* @param	int		mixed			The number of user rows to return
	* @param	mixed	boolean|array	FALSE or the data array
	*
	* @return	int
	*/
	function get_user_array(&$Db_object, $databasetype, $tableprefix, $startat = null, $perpage = null)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$_usersarray = array();

				if ($startat == null OR $perpage == null)
				{
					$sql = "SELECT userid,username,importuserid FROM " . $tableprefix . "user";
				}
				else
				{
					$sql = "SELECT userid, username, importuserid FROM " . $tableprefix . "user LIMIT $startat, $perpage";
				}

				$result = $Db_object->query($sql);

				if (!$Db_object->num_rows($result))
				{
					return false;
				}

				if ($result)
				{
					while ($user = $Db_object->fetch_array($result))
					{
						$tempArray = array(
							'userid' => $user['userid'],
							'username' => $user['username'],
							'importuserid' => $user['importuserid']
						);
						array_push($_usersarray, $tempArray);
					}
					$Db_object->free_result($result);

					return $_usersarray;

				}
				else
				{
					return false;
				}

			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Returns a string of the banned group id
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	string	mixed			The id/name of the Banned group (needs to be updated for permissions)
	*/
	function get_banned_group(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$banned_grouip_id = $Db_object->query_first("SELECT usergroupid FROM " . $tableprefix . "usergroup  WHERE title='Banned Users'");

				if($banned_grouip_id)
				{
					return $banned_grouip_id['usergroupid'];
				}
				else
				{
					return false;
				}
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}

	function zuul($dana)
	{
		$zuul = "There is no importers only zuul";

		return $zuul;
	}

	/**
	* Returns an array of the 'importedusergroupid'=>'usergroupid'
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	string	mixed			The id/name of the Banned group (needs to be updated for permissions)
	*/
	function get_imported_group_ids(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$user_groups = $Db_object->query("SELECT usergroupid, importusergroupid FROM " . $tableprefix . "usergroup  WHERE importusergroupid <> 0");

				while ($group = $Db_object->fetch_array($user_groups))
				{
					$return_data["$group[importusergroupid]"] = $group['usergroupid'];
				}
				$Db_object->free_result($user_groups);

				return $return_data;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}



	/**
	* Returns a vB thread id
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	string	mixed			The imported thread id
	* @param	string	mixed			The imported forum id
	*
	* @return	int		mixed			The vb id of the thread
	*/
	function get_thread_id(&$Db_object, &$databasetype, &$tableprefix, &$importthreadid, &$forumid)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$result = $Db_object->query_first("
					SELECT threadid FROM " . $tableprefix . "thread
					WHERE importthreadid= " . intval($importthreadid) . "
					AND importforumid = '" . $forumid . "'
				");
				return $result['threadid'];
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}

	function get_forum_and_thread_ids(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$result = $Db_object->query("SELECT threadid, importthreadid, importforumid FROM " . $tableprefix . "thread");

				while ($ids = $Db_object->fetch_array($result))
				{
					$return_data["$ids[importforumid]"]["$ids[importthreadid]"] = $ids['threadid'];
				}
				$Db_object->free_result($result);
				
				return $return_data;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}

	/**
	* Returns a vB thread id array
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	array	mixed			The array of vb ids of the threads
	*/
	function get_threads_ids(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$threads = $Db_object->query("SELECT threadid, importthreadid FROM " . $tableprefix . "thread WHERE importthreadid <> 0");

				while ($thread = $Db_object->fetch_array($threads))
				{
					$threadid["$thread[importthreadid]"] = $thread['threadid'];
				}
				$Db_object->free_result($threads);

				return $threadid;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}

	/**
	* Returns a vB post id array
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	array	mixed			The array of vb ids of the threads
	*/
	function get_posts_ids(&$Db_object, &$databasetype, &$tableprefix)
	{
		$return_array = array();

		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$posts = $Db_object->query("SELECT postid, importpostid FROM " . $tableprefix . "post WHERE importpostid <> 0");

				while ($post = $Db_object->fetch_array($posts))
				{
					$return_array["$post[importpostid]"] = $post['postid'];
				}
				$Db_object->free_result($posts);

				return $return_array;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Returns an array of the forum ids key'ed to the importforum id's
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	array	mixed			The vb id of the thread
	*/
	function get_category_ids(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$forums = $Db_object->query("SELECT forumid, importcategoryid FROM " . $tableprefix . "forum WHERE importcategoryid <> 0");

				while ($forum = $Db_object->fetch_array($forums))
				{
					$categoryid["$forum[importcategoryid]"] = $forum['forumid'];
				}
				$Db_object->free_result($forums);

				return $categoryid;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}

	function get_category_id_by_name(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$forums = $Db_object->query("SELECT forumid, title FROM " . $tableprefix . "forum WHERE importcategoryid <> 0");

				while ($forum = $Db_object->fetch_array($forums))
				{
					$categoryid["$forum[title]"] = $forum['forumid'];
				}
				$Db_object->free_result($forums);

				return $categoryid;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}

	function get_forum_id_by_name(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$forums = $Db_object->query("SELECT forumid,  importforumid, title FROM " . $tableprefix . "forum WHERE importforumid <> 0");

				while ($forum = $Db_object->fetch_array($forums))
				{
					$categoryid["$forum[title]"]['forumid'] 		= $forum['forumid'];
					$categoryid["$forum[title]"]['importforumid'] 	= $forum['importforumid'];
				}
				$Db_object->free_result($forums);

				return $categoryid;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Returns an array of 'import_user_id' => 'vb_user_id'
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	string	mixed			importuser id delimited string
	*/
	function get_done_user_ids(&$Db_object, &$databasetype, &$tableprefix)
	{
		$return_array = array();

		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$user_ids = $Db_object->query("SELECT userid, importuserid FROM " . $tableprefix . "user WHERE importuserid <> 0");

				while ($user_id = $Db_object->fetch_array($user_ids))
				{
					$return_array["$user_id[importuserid]"] = $user_id['userid'];
				}
				$Db_object->free_result($user_ids);

				return $return_array;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Returns an array of the user ids key'ed to the import user id's $userid[$importuserid] = $user[userid]
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	array	mixed			Data array[importuserid] = userid
	*/
	function get_user_ids(&$Db_object, &$databasetype, &$tableprefix, $do_int_val = false)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$users = $Db_object->query("
					SELECT userid, username, importuserid
					FROM " . $tableprefix . "user
					WHERE importuserid <> 'null'
				");
				while ($user = $Db_object->fetch_array($users))
				{
					if ($do_int_val)
					{
						$importuserid = intval($user['importuserid']);
					}
					else
					{
						$importuserid = $user['importuserid'];
					}

					$userid["$importuserid"] = $user['userid'];
				}
				$Db_object->free_result($users);

				return $userid;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Returns an array of the import user ids key'ed to the username
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	array	mixed			Data array[importuserid] = username
	*/
	function get_username(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$users = $Db_object->query("SELECT username, userid, importuserid AS importuserid FROM " . $tableprefix ."user WHERE importuserid <> 0");
				while ($user = $Db_object->fetch_array($users))
				{
					// The normal
					$username["$user[importuserid]"]		= $user['username'];

					// The reverse
					// TODO: Check where this is actuall used ?
					#$username['userid']["$user[userid]"]	= $user['username'];
				}
				$Db_object->free_result($users);

				return $username;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}

	function get_username_to_ids(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$users = $Db_object->query("SELECT username, userid FROM " . $tableprefix ."user WHERE importuserid <> 0");
				while ($user = $Db_object->fetch_array($users))
				{
					$username["$user[username]"] = $user['userid'];
				}
				$Db_object->free_result($users);

				return $username;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}

	function get_email_to_ids(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$users = $Db_object->query("SELECT email, userid, username FROM " . $tableprefix ."user WHERE importuserid <> 0");
				while ($user = $Db_object->fetch_array($users))
				{
					$email_addy = strtolower($user['email']);

					$email[$email_addy]['userid'] 	= $user['userid'];
					$email[$email_addy]['username'] = $user['username'];
				}
				$Db_object->free_result($users);

				return $email;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}

	/**
	* Returns one postid if from an importpostid, slow but used mainly for parentid's while in a loop
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	array	mixed			Data array[importuserid] = username
	*/
	function get_vb_post_id(&$Db_object, &$databasetype, &$tableprefix, $import_post_id)
	{
		if(!$import_post_id)
		{
			return false;
		}

		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$sql = "SELECT postid, importpostid FROM " . $tableprefix ."post WHERE importpostid = " . $import_post_id ;

				$post_id = $Db_object->query_first($sql);

				return $post_id[0];
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Returns an array of the forum ids key'ed to the import forum id's
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	int		0|1				Wether or not to inval the import forum id
	*
	* @return	array	mixed			Data array[impforumid] = forumid
	*/
	function get_forum_ids(&$Db_object, &$databasetype, &$tableprefix, $pad=0)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$forums = $Db_object->query("SELECT forumid, importforumid FROM " . $tableprefix . "forum");

				while ($forum = $Db_object->fetch_array($forums))
				{
					if ($pad)
					{
						$impforumid = intval($forum['importforumid']);
						$forumid["$impforumid"] = $forum['forumid'];
					}
					else
					{
						$forumid["$forum[importforumid]"] = $forum['forumid'];
					}
				}
				$Db_object->free_result($forums);

				return $forumid;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Clears ALL the IP AND email address in the banlists
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	boolean
	*/
	function clear_ban_list(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$Db_object->query("UPDATE " . $tableprefix . "datastore SET data = '' WHERE title = 'banemail'");
				$Db_object->query("UPDATE " . $tableprefix . "setting SET value = '' WHERE varname = 'banip'");
				// TODO: Error and return handeling
				return true;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}
	
	
	function clear_imported_attachments(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$Db_object->query("DELETE FROM " . $tableprefix  . "attachment WHERE importattachmentid <> 0");

				$Db_object->query("ALTER TABLE " . $tableprefix  . "attachment AUTO_INCREMENT = 0");
				$Db_object->query("ALTER TABLE " . $tableprefix  . "attachment auto_increment = 0");
				return true;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	function clear_imported_subscriptions(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$Db_object->query("DELETE FROM " . $tableprefix  . "subscribeforum WHERE importstyleid <> 0");

				$Db_object->query("ALTER TABLE " . $tableprefix  . "subscribeforum AUTO_INCREMENT = 0");
				$Db_object->query("ALTER TABLE " . $tableprefix  . "subscribeforum auto_increment = 0");

				$Db_object->query("DELETE FROM " . $tableprefix  . "subscribethread WHERE importstyleid <> 0");

				$Db_object->query("ALTER TABLE " . $tableprefix  . "subscribethread AUTO_INCREMENT = 0");
				$Db_object->query("ALTER TABLE " . $tableprefix  . "subscribethread auto_increment = 0");
				return true;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}

	/**
	* Clears the currently imported avatars
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	boolean
	*/
	function clear_imported_avatars(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$Db_object->query("DELETE FROM " . $tableprefix  . "avatar WHERE importavatarid <> 0");
				$Db_object->query("DELETE FROM " . $tableprefix  . "customavatar WHERE importcustomavatarid <> 0");

				$Db_object->query("ALTER TABLE " . $tableprefix  . "avatar AUTO_INCREMENT = 0");
				$Db_object->query("ALTER TABLE " . $tableprefix  . "avatar auto_increment = 0");

				$Db_object->query("ALTER TABLE " . $tableprefix  . "customavatar AUTO_INCREMENT = 0");
				$Db_object->query("ALTER TABLE " . $tableprefix  . "customavatar auto_increment = 0");
				return true;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Clears the currently imported forums
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	boolean
	*/
	function clear_imported_forums(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				// delete imported categories and forums
				$Db_object->query("
					DELETE FROM " . $tableprefix  . "forum
					WHERE importforumid <> 0
					OR importcategoryid <> 0
				");

				// reset the auto increment
				$Db_object->query("ALTER TABLE " . $tableprefix  . "forum AUTO_INCREMENT = 0");
				$Db_object->query("ALTER TABLE " . $tableprefix  . "forum auto_increment = 0");

				return true;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Clears the currently imported threads
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	boolean
	*/
	function clear_imported_threads(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$Db_object->query("DELETE FROM " . $tableprefix  . "thread WHERE importthreadid <> 0");

				$Db_object->query("ALTER TABLE " . $tableprefix  . "thread AUTO_INCREMENT = 0");
				$Db_object->query("ALTER TABLE " . $tableprefix  . "thread auto_increment = 0");

				return true;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Clears the currently banned users
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	boolean
	*/
	function clear_banned_users(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$user_id = $Db_object->query_first("SELECT usergroupid FROM " . $tableprefix . "usergroup WHERE title = 'Banned Users'");
				if ($user_id)
				{
					$Db_object->query("DELETE FROM " . $tableprefix  . "user WHERE usergroupid <> $user_id[usergroupid]");
				}
				return true;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Clears the currently imported users
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	boolean
	*/
	function clear_imported_users(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$users = $Db_object->query("SELECT userid FROM " . $tableprefix  . "user WHERE importuserid <> 0");

				if ($Db_object->num_rows($users))
				{
					$removeid = array('0');
					while ($user = $Db_object->fetch_array($users))
					{
						$removeid[] = $user['userid'];
					}
					$Db_object->free_result($users);

					$ids = implode(',', $removeid);

					// user
					$Db_object->query("DELETE FROM " . $tableprefix  . "user WHERE userid IN(" . $ids . ")");
					$Db_object->query("ALTER TABLE " . $tableprefix  . "user AUTO_INCREMENT=0");
					$Db_object->query("ALTER TABLE " . $tableprefix  . "user auto_increment=0");

					// customavatar
					$Db_object->query("DELETE FROM " . $tableprefix  . "customavatar WHERE userid IN(" . $ids . ")");
					$Db_object->query("ALTER TABLE " . $tableprefix  . "customavatar AUTO_INCREMENT = 0");
					$Db_object->query("ALTER TABLE " . $tableprefix  . "customavatar auto_increment = 0");

					// customprofilepic
					$Db_object->query("DELETE FROM " . $tableprefix  . "customprofilepic WHERE userid IN(" . $ids . ")");
					$Db_object->query("ALTER TABLE " . $tableprefix  . "customprofilepic AUTO_INCREMENT = 0");
					$Db_object->query("ALTER TABLE " . $tableprefix  . "customprofilepic auto_increment = 0");

					// userfield
					$Db_object->query("DELETE FROM " . $tableprefix  . "userfield WHERE userid IN(" . $ids . ")");
					$Db_object->query("ALTER TABLE " . $tableprefix  . "userfield AUTO_INCREMENT = 0");
					$Db_object->query("ALTER TABLE " . $tableprefix  . "userfield auto_increment = 0");

					// usertextfield
					$Db_object->query("DELETE FROM " . $tableprefix  . "usertextfield WHERE userid IN(" . $ids . ")");
					$Db_object->query("ALTER TABLE " . $tableprefix  . "usertextfield AUTO_INCREMENT = 0");
					$Db_object->query("ALTER TABLE " . $tableprefix  . "usertextfield auto_increment = 0");
				}

				 return true;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}

	/**
	* Clears the currently imported posts
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	boolean
	*/
	function clear_imported_posts(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$Db_object->query("DELETE FROM " . $tableprefix  . "post WHERE importthreadid <> 0");
				$Db_object->query("DELETE FROM " . $tableprefix  . "post WHERE importpostid <> 0");
				$Db_object->query("ALTER TABLE " . $tableprefix  . "post AUTO_INCREMENT = 0");
				$Db_object->query("ALTER TABLE " . $tableprefix  . "post auto_increment = 0");
				return true;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}

	/**
	* Clears the currently imported polls
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	boolean
	*/
	function clear_imported_polls(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				// poll ids
				$polls = $Db_object->query("SELECT pollid FROM " . $tableprefix  . "poll WHERE importpollid <> '0'");

				if ($Db_object->num_rows($polls))
				{
					$removeid = array('0');

					while ($poll = $Db_object->fetch_array($polls))
					{
						$removeid[] = $poll['pollid'];
					}
					$poll_ids = implode(',', $removeid);

					// Remove them
					$Db_object->query("UPDATE " . $tableprefix  . "thread SET pollid=0 WHERE importthreadid <> 0 ");

					$Db_object->query("DELETE from " . $tableprefix  . "poll WHERE pollid IN($poll_ids)");
					$Db_object->query("DELETE from " . $tableprefix  . "pollvote WHERE pollid IN($poll_ids)");

					$Db_object->query("ALTER TABLE " . $tableprefix  . "poll AUTO_INCREMENT = 0");
					$Db_object->query("ALTER TABLE " . $tableprefix  . "poll auto_increment = 0");

					$Db_object->query("ALTER TABLE " . $tableprefix  . "pollvote AUTO_INCREMENT = 0");
					$Db_object->query("ALTER TABLE " . $tableprefix  . "pollvote auto_increment = 0");
				}
				return true;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Clears the currently imported buddy list(s) from the currently imported users
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	boolean
	*/
	function clear_imported_buddy_list(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$imported_users = $Db_object->query("SELECT userid FROM " . $tableprefix  . "user WHERE importuserid <> 0");

				if ($Db_object->num_rows($imported_users))
				{
					$userids = array('0');
					while ($userid = $Db_object->fetch_array($imported_users))
					{
						$userids[] = $userid['userid'];
					}
					$Db_object->query("UPDATE " . $tableprefix . "usertextfield SET buddylist = '' WHERE userid IN (" . implode(',', $userids) . ")");
				}
				return true;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}

	/**
	* Clears the currently imported ignore list(s) from the currently imported users
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	boolean
	*/
	function clear_imported_ignore_list(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$imported_users = $Db_object->query("SELECT userid FROM " . $tableprefix  . "user WHERE importuserid <> 0");

				if ($Db_object->num_rows($imported_users))
				{
					$userids = array('0');
					while ($userid = $Db_object->fetch_array($imported_users))
					{
						$userids[] = $userid['userid'];
					}
					$Db_object->query("UPDATE " . $tableprefix . "usertextfield SET ignorelist = '' WHERE userid IN (" . implode(',', $userids) . ")");
				}
				return true;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}

	/**
	* Clears the currently imported pm's & pmtext's from the currently imported users
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	boolean
	*/
	function clear_imported_private_messages(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				// user ids
				$users = $Db_object->query("SELECT userid FROM " . $tableprefix  . "user WHERE importuserid <> 0");

				if ($Db_object->num_rows($users))
				{
					$removeid = array('0');

					while ($user = $Db_object->fetch_array($users))
					{
						$removeid[] = $user['userid'];
					}
					$user_ids = implode(',', $removeid);

					// pm_texts
					$pm_text_ids = $Db_object->query("SELECT pmtextid from " . $tableprefix  . "pm WHERE userid IN(" . $user_ids . ")");
					$removeid = array('0');

					while ($pm_text = $Db_object->fetch_array($pm_text_ids))
					{
						$removeid[] = $pm_text['pmtextid'];
					}
					$_pm_text_ids = implode(',', $removeid);

					// Remove them
					$Db_object->query("DELETE from " . $tableprefix  . "pmtext WHERE fromuserid IN(" . $_pm_text_ids . ")");
					$Db_object->query("DELETE from " . $tableprefix  . "pm WHERE userid IN(" . $user_ids . ")");

					// Just to make sure.
					$check_sql = "DESCRIBE `" .$tableprefix . "pm`";
					$keys = $Db_object->query($check_sql);

					while ($key = $Db_object->fetch_array($keys))
					{
						if($key['Field'] == "importpmid")
						{
							$Db_object->query("DELETE from " . $tableprefix  . "pm WHERE importpmid <> 0");
						}
					}

					$check_sql = "DESCRIBE `" .$tableprefix . "pmtext`";
					$keys = $Db_object->query($check_sql);

					while ($key = $Db_object->fetch_array($keys))
					{
						if($key['Field'] == "importpmid")
						{
							$Db_object->query("DELETE from " . $tableprefix  . "pmtext WHERE importpmid <> 0");
						}
					}
				}
				return true;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}

	/**
	* Clears the currently imported moderators
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	boolean
	*/
	function clear_imported_moderators(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$imported_users = $Db_object->query("SELECT userid FROM " . $tableprefix  . "user WHERE importuserid <> 0");

				if ($Db_object->num_rows($imported_users))
				{
					$removeid = array('0');
					while ($userid = $Db_object->fetch_array($imported_users))
					{
						$removeid[] = $userid['userid'];
					}
					$Db_object->query("DELETE FROM " . $tableprefix  . "moderator WHERE userid IN (" . implode(',', $removeid) . ")");
				}
				return true;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}

	/**
	* Clears the currently imported smilies
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	boolean
	*/
	function clear_imported_smilies(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$Db_object->query("DELETE FROM " . $tableprefix  . "smilie WHERE importsmilieid <> 0");
				return true;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}

	/**
	* Clears the currently imported smilies
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	boolean
	*/
	function clear_imported_user_groups(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$Db_object->query("DELETE FROM " . $tableprefix  . "usergroup WHERE importusergroupid <> 0");

				$Db_object->query("ALTER TABLE " . $tableprefix  . "usergroup AUTO_INCREMENT=0");
				$Db_object->query("ALTER TABLE " . $tableprefix  . "usergroup auto_increment=0");
				return true;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Clears the currently imported smilies
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	boolean
	*/
	function clear_imported_custom_pics(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$Db_object->query("DELETE FROM " . $tableprefix  . "customprofilepic WHERE importcustomprofilepicid <> 0");

				$Db_object->query("ALTER TABLE " . $tableprefix  . "customprofilepic AUTO_INCREMENT=0");
				$Db_object->query("ALTER TABLE " . $tableprefix  . "customprofilepic auto_increment=0");

				return true;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Clears the currently imported ranks
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	boolean
	*/
	function clear_imported_ranks(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$Db_object->query("DELETE FROM " . $tableprefix  . "ranks WHERE importrankid  <> 0");
				
				$Db_object->query("ALTER TABLE " . $tableprefix  . "ranks AUTO_INCREMENT=0");
				$Db_object->query("ALTER TABLE " . $tableprefix  . "ranks auto_increment=0");				
				
				return true;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Clears the currently imported usergroups
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	boolean
	*/
	function clear_imported_usergroups(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$Db_object->query("DELETE FROM " . $tableprefix  . "usergroup WHERE importusergroupid  <> 0");
				
				$Db_object->query("ALTER TABLE " . $tableprefix  . "usergroup AUTO_INCREMENT=0");
				$Db_object->query("ALTER TABLE " . $tableprefix  . "usergroup auto_increment=0");					
				
				return true;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	function clear_non_admin_users(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$users = $Db_object->query("
					SELECT userid, username
					FROM " . $tableprefix . "user AS user
					LEFT JOIN " . $tableprefix . "usergroup AS usergroup USING (usergroupid)
					WHERE !(usergroup.adminpermissions & 3) # this is the 'cancontrolpanel' option
				");

				if ($Db_object->num_rows($users))
				{
					$removeid = array('0');
					while ($user = $Db_object->fetch_array($users))
					{
						$Db_object->query("
							UPDATE " . $tableprefix . "post
							SET username = '" . addslashes($user['username']) . "',
							userid = 0
							WHERE userid = $user[userid]
						");
						$Db_object->query("
							UPDATE " . $tableprefix . "usernote
							SET username = '" . addslashes($user['username']) . "',
							posterid = 0
							WHERE posterid = $user[userid]
						");

						$removeid[] = $user['userid'];
					}

					$ids = implode(',', $removeid);

					// user-related
					$Db_object->query("DELETE FROM " . $tableprefix . "usernote WHERE userid IN ($ids)");
					$Db_object->query("DELETE FROM " . $tableprefix . "user WHERE userid IN ($ids)");
					$Db_object->query("DELETE FROM " . $tableprefix . "userfield WHERE userid IN ($ids)");
					$Db_object->query("DELETE FROM " . $tableprefix . "usertextfield WHERE userid IN ($ids)");
					$Db_object->query("DELETE FROM " . $tableprefix . "access WHERE userid IN ($ids)");
					$Db_object->query("DELETE FROM " . $tableprefix . "event WHERE userid IN ($ids)");
					$Db_object->query("DELETE FROM " . $tableprefix . "customavatar WHERE userid IN ($ids)");
					$Db_object->query("DELETE FROM " . $tableprefix . "customprofilepic WHERE userid IN ($ids)");
					$Db_object->query("DELETE FROM " . $tableprefix . "moderator WHERE userid IN ($ids)");
					$Db_object->query("DELETE FROM " . $tableprefix . "subscribeforum WHERE userid IN ($ids)");
					$Db_object->query("DELETE FROM " . $tableprefix . "subscribethread WHERE userid IN ($ids)");
					$Db_object->query("DELETE FROM " . $tableprefix . "subscriptionlog WHERE userid IN ($ids)");
					$Db_object->query("DELETE FROM " . $tableprefix . "session WHERE userid IN ($ids)");
					$Db_object->query("DELETE FROM " . $tableprefix . "userban WHERE userid IN ($ids)");
					$Db_object->query("DELETE FROM " . $tableprefix . "administrator WHERE userid IN ($ids)");

					// user
					$Db_object->query("DELETE FROM " . $tableprefix  . "user WHERE userid IN($ids)");
					$Db_object->query("DELETE FROM " . $tableprefix  . "customavatar WHERE userid IN($ids)");
					$Db_object->query("DELETE FROM " . $tableprefix  . "customprofilepic WHERE userid IN($ids)");
					$Db_object->query("DELETE FROM " . $tableprefix  . "userfield WHERE userid IN($ids)");
					$Db_object->query("DELETE FROM " . $tableprefix  . "usertextfield WHERE userid IN($ids)");
				}

				 return true;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Updates the parentids of the posts in the database if they are 0
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	boolean
	*/
	function update_post_parent_ids(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$thread_ids = $Db_object->query("SELECT DISTINCT threadid FROM " . $tableprefix . "post where importthreadid <> 0");

				if ($Db_object->num_rows($thread_ids))
				{
					while($thread_id = $Db_object->fetch_array($thread_ids))
					{
						$parentpost = $Db_object->query_first("
							SELECT postid FROM " . $tableprefix . "post
							WHERE threadid = $thread_id[threadid]
							ORDER BY dateline LIMIT 1
						");

						$Db_object->query("
							UPDATE " . $tableprefix . "post
							SET parentid = $parentpost[postid]
							WHERE threadid = $thread_id[threadid]
								AND postid <> $parentpost[postid]
								AND parentid = 0
						");
					}
				}
				return true;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Set as users importuserid, used when linking import users during assosiate
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	string	mixed			The user_id from the soruce board being imported
	* @param	string	mixed			The vB userid to associate with
	*
	* @return	boolean
	*/
	function associate_user(&$Db_object, &$databasetype, &$tableprefix, $importuserid, $userid)
	{
		// NOTE: Handeling for passing in an array ?
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$does_user_exist = $Db_object->query("SELECT userid FROM " . $tableprefix . "user WHERE userid = '$userid'");

				if ($Db_object->num_rows($does_user_exist))
				{
					$Db_object->query("
						UPDATE " . $tableprefix . "user
						SET importuserid = {$importuserid}
						WHERE userid = {$userid}
					");

					return ($Db_object->affected_rows() > 0);
				}
				else
				{
					return false;
				}

			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Checks the database tables.
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	string	mixed			The board type that we are importing from
	*
	* @return	boolean
	*/
	function check_database(&$Db_object, &$databasetype, &$tableprefix, $sourceexists)
	{
		$found_tables = array();
		
		if(!$sourceexists)
		{
			// TODO: return code of phrase
			return array( 	'code'	=>	false,
							'text'	=>	"<h4>Please set 'sourceexists = true' in ImpExConfig.php</h4>");
		}

		$return_string = '';

		if (count($this->_valid_tables) == 0)
		{
			// TODO: return code of phrase
			die('<h4>ImpExDatabase :: check_database $this->_valid_tables must be over ridden in the 000 module of the system</h4>');
		}

		foreach ($this->_valid_tables as $key => $value)
		{
			$valid_tables["$key"] = $tableprefix . $value;
		}

		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$tables = $Db_object->query("SHOW TABLES");
				$return_string .= "<br /><br /><b>Valid found tables :</b><br /> ";

				while ($table = $Db_object->fetch_array($tables))
				{
					/// The above call to fetch_array() needs to be passed DBARRAY_NUM if db_mysql.php is updated!
					// NOTE: Building display data here ! AARRRGGGHHH Must change !
					// TODO: -
					if (in_array($table[0], $valid_tables))
					{
						// TODO: return code of phrase
						$return_string .= "<br /><span class=\"isucc\">" . $table[0] . " found.</span>";
						
						// List the found ones 
						$found_tables[] = $table[0];
					}
					else
					{
						foreach($this->_valid_tables AS $valid_table)
						{
							if($pos = strpos($table[0], $valid_table))
							{
								$poss_key = substr($table[0], 0, $pos);
								$prefix_poss[$poss_key]++;
							}
						}	
					}
				}
				
				$not_found = array_diff($valid_tables, $found_tables);
				
				if(is_array($not_found))
				{
					$return_string .= "<br /><br /><b>Possiablly custom tables or incorrect prefix :</b><br /> ";
					// Found some
					foreach($not_found as $table_name)
					{
						// TODO: Phrase
						$return_string .= "<br /><span class=\"ifail\">{$table_name} <b>NOT</b> found.</span>";
					}
				}
				
				if(is_array($prefix_poss))
				{
					$return_string .= "<br /><br /><span><b>If you have all red tables, i.e. none correct this could possiable be your table prefix :</b></span><br /><br /><list>";
					// Possiable table prefix
					// Sort to get the most common found one  
					krsort($prefix_poss, SORT_NUMERIC);
					end($prefix_poss);
					$return_string .="<li>" . key($prefix_poss) . "</li>";
					$return_string .= "</list>";
				}
				
				return array(	'code'	=>	true,
								'text'	=>	$return_string);
					
			}

			// MS-SQL database
			case 'mssql':
			{
				$tables = $Db_object->query("SELECT	TABLE_NAME FROM	INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'");

				while ($table = $Db_object->fetch_array($tables))
				{
					if (in_array($table['TABLE_NAME'], $valid_tables))
					{
						// TODO: return code of phrase
						$return_string .= "<br /><span class=\"isucc\">" . $table[0] . " found.</span>";
						
						// List the found ones 
						$found_tables[] = $table[0];
					}
				}
				
				$not_found = array_diff($valid_tables, $found_tables);
				
				foreach($not_found as $table_name)
				{
					// TODO: Phrase
					$return_string .= "<br /><span class=\"ifail\">{$table_name} <b>NOT</b> found.</span>";
				}				
				
				return array(	'code'	=>	true,
								'text'	=>	$return_string);
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	function check_table(&$Db_object, &$databasetype, &$tableprefix, $table_name)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$tables = $Db_object->query("SHOW TABLES");

				while ($table = $Db_object->fetch_array($tables))
				{
					if ($table[0] == $tableprefix . $table_name)
					{
						return true;
					}
				}
				return false;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}

	/**
	* Imports a poll
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	string	mixed			The board type that we are importing from
	*
	* @return	boolean
	*/
	function import_poll(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$Db_object->query("
					INSERT INTO " . $tableprefix . "poll
					(
						importpollid, question, dateline,
						options, votes, active,
						numberoptions, timeout, multiple,
						voters, public, lastvote
					)
					VALUES
					(
						'" . $this->get_value('mandatory', 'importpollid') . "',
						'" . addslashes($this->get_value('mandatory', 'question')) . "',
						'" . $this->get_value('mandatory', 'dateline') . "',
						'" . addslashes($this->get_value('mandatory', 'options')) . "',
						'" . $this->get_value('mandatory', 'votes') . "',
						'" . $this->get_value('nonmandatory', 'active') . "',
						'" . $this->get_value('nonmandatory', 'numberoptions') . "',
						'" . $this->get_value('nonmandatory', 'timeout') . "',
						'" . $this->get_value('nonmandatory', 'multiple')  . "',
						'" . $this->get_value('nonmandatory', 'voters') . "',
						'" . $this->get_value('nonmandatory', 'public') . "',
						'" . $this->get_value('nonmandatory', 'lastvote') . "'
					)
				");

				return $Db_object->insert_id();
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Updates a thread pollid
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	string	mixed			The vB poll id
	* @param	string	mixed			The import thread id of the thread that you want to attach the poll to
	*
	* @return	boolean
	*/
	function import_poll_to_thread(&$Db_object, &$databasetype, &$tableprefix, $vb_poll_id, $import_thread_id, $vb_thread_id = false)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				if (!$vb_thread_id)
				{
					$thread_exsists = $Db_object->query("SELECT threadid FROM " . $tableprefix . "thread WHERE importthreadid='". $import_thread_id ."'");

					if ($Db_object->num_rows($thread_exsists))
					{
						$Db_object->query("
							UPDATE " . $tableprefix . "thread
							SET pollid = {$vb_poll_id} WHERE importthreadid = {$import_thread_id}
						");

						return true;
						
						// This dosn't work
						if ($Db_object->affected_rows())
						{
							return true;
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
				else
				{
					$Db_object->query("
							UPDATE " . $tableprefix . "thread
							SET pollid = {$vb_poll_id} WHERE threadid = {$import_thread_id}
						");
						// Its not the &import_thread_id its the vB one
						if ($Db_object->affected_rows())
						{
							return true;
						}
						else
						{
							return false;
						}

				}
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Updates a thread pollid
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	string	mixed			poll_voters_array = $var = array ( 'vb_user_id' => 'vote_option'); etc
	* @param	string	mixed			The vB poll id
	*
	* @return	boolean
	*/
	function import_poll_voters(&$Db_object, &$databasetype, &$tableprefix, $poll_voters_array ,$vb_poll_id)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				// if $vote_option == 0 then it wasn't possiable to get hold of the pollvote.voteoption
				
				if(!empty($poll_voters_array))
				{
					foreach ($poll_voters_array AS $vb_user_id => $vote_option)
					{
						if(empty($vb_user_id))
						{
							continue;
						}

						if ($vote_option == 0 OR empty($vote_option))
						{
							$sql = "
								INSERT INTO " . $tableprefix . "pollvote
									(pollid, userid)
								VALUES
									('$vb_poll_id', '$vb_user_id')
							";
						}
						else
						{
							$sql = "
							INSERT INTO " . $tableprefix . "pollvote
								(pollid, userid, voteoption)
							VALUES
								('$vb_poll_id', '$vb_user_id', '$vote_option')
							";
						}

						$Db_object->query($sql);
					}
					
					return true;
				}
				else
				{
					return false;
				}
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Checks for a smilie text
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	string	mixed			array( title => '', smilietext => '', smiliepath => '')
	*
	* @return	boolean
	*/
	function does_smilie_exists(&$Db_object, &$databasetype, &$tableprefix, $smilie)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				return $Db_object->query_first("SELECT smilieid FROM " . $tableprefix . "smilie WHERE smilietext='". addslashes($smilie['smilietext']) ."'");
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}

	/**
	* Imports the current objects values as a Smilie
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	boolean
	*/
	function import_smilie(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$smilie_path = 'images/smilies/';

				$update = $Db_object->query_first("SELECT smilieid FROM " . $tableprefix . "smilie WHERE smilietext = '". addslashes($this->get_value('mandatory', 'smilietext')) . "'");

				if (!$update)
				{
					$sql = "
						INSERT INTO	" . $tableprefix . "smilie
						(
							title, smilietext, smiliepath,
							imagecategoryid, displayorder, importsmilieid
						)
						VALUES
						(
							'" . addslashes($this->get_value('nonmandatory', 'title')) . "',
							'" . addslashes($this->get_value('mandatory', 'smilietext')) . "',
							'" . $smilie_path . $this->get_value('nonmandatory', 'smiliepath') . "',
							'" . $this->get_value('nonmandatory', 'imagecategoryid') . "',
							'" . $this->get_value('nonmandatory', 'displayorder') . "',
							'" . $this->get_value('mandatory', 'importsmilieid') . "'
						)
					";
				}
				else
				{
					$sql = "
						UPDATE " . $tableprefix . "smilie SET
						title = '" . addslashes($this->get_value('nonmandatory', 'title')) . "',
						smiliepath = '" . $smilie_path . $this->get_value('nonmandatory', 'smiliepath') . "'
						WHERE smilietext = '" . addslashes($this->get_value('mandatory', 'smilietext')) . "'
					";
				}

				$Db_object->query($sql);
				return ($Db_object->affected_rows() > 0);
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Updates forum permissions
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	string	mixed			The forumid
	*
	* @return	boolean
	*/
	function set_forum_private(&$Db_object, &$databasetype, &$tableprefix, &$forum_id)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$usergroupids = $Db_object->query("
					SELECT usergroupid
					FROM " . $tableprefix . "usergroup
					WHERE title IN ('Super Moderators', 'Administrators', 'Moderators')
				");

				if ($Db_object->num_rows($usergroupids))
				{
					$extended_insert = array();

					while ($usergroupid = $Db_object->fetch_array($usergroupids))
					{
						$extended_insert[] = "($forum_id, $usergroupid[usergroupid], 0)";
					}

					if (!empty($extended_insert))
					{
						$Db_object->query("
							INSERT INTO " . $tableprefix . "forumpermission
								(forumid, usergroupid, forumpermissions)
							VALUES
								" . implode(', ', $extended_insert) . "
						");
					}
				}

				// TODO: Need to actually check this opposed to just returning it !
				return true;

			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Updates forum permissions
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	string	mixed			The forumid
	*
	* @return	boolean
	*/
	function build_user_statistics(&$Db_object, &$databasetype, &$tableprefix)
	{
		// get total members
		$members = $Db_object->query_first("SELECT COUNT(*) AS users, MAX(userid) AS max FROM " . $tableprefix . "user");

		// get newest member
		$newuser = $Db_object->query_first("SELECT userid, username FROM " . $tableprefix . "user WHERE userid = $members[max]");

		// make a little array with the data
		$values = array(
			'numbermembers' => $members['users'],
			'newusername' => $newuser['username'],
			'newuserid' => $newuser['userid']
		);

		// update the special template
		$Db_object->query("REPLACE INTO " . $tableprefix . "datastore (title, data)
						VALUES ('userstats', '" . addslashes(serialize($values)) . "')");
	}

	/**
	* Rebuilds a forums child list string
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	string	mixed			The forumid
	*
	* @return	string
	*/
	function construct_child_list(&$Db_object, &$databasetype, &$tableprefix, $forumid)
	{
		if ($forumid == -1)
		{
			return '-1';
		}

		$childlist = $forumid;

		$children = $Db_object->query("SELECT forumid FROM " . $tableprefix . "forum WHERE parentid = {$forumid}");
		while ($child = $Db_object->fetch_array($children))
		{
			$childlist .= ',' . $child['forumid'];
		}

		$childlist .= ',-1';

		return $childlist;
	}


	/**
	* Rebuilds all the forums child lists
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	string	mixed			The forumid
	*
	* @return	none
	*/
	function build_forum_child_lists(&$Db_object, &$databasetype, &$tableprefix, $forumid = -1)
	{
		$forums = $Db_object->query("SELECT forumid FROM " . $tableprefix . "forum WHERE childlist = ''");


		while ($forum = $Db_object->fetch_array($forums))
		{
			$childlist = $this->construct_child_list($Db_object, $databasetype, $tableprefix, $forum['forumid']);
			$Db_object->query("
				UPDATE " . $tableprefix . "forum
				SET childlist = '$childlist'
				WHERE forumid = " . $forum['forumid']
			);
		}
	}


	/**
	* Import a poll from one vB3 board to another
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	int	mixed				The vb_poll_id
	* @param	int	mixed				The import_poll_id
	*
	* @return	boolean
	*/
	function import_poll_to_vb3_thread(&$Db_object, &$databasetype, &$tableprefix, $vb_poll_id, $import_poll_id)
	{
		switch ($databasetype)
		{
			case 'mysql':
			{
				$sql = "UPDATE " . $tableprefix . "thread
					SET pollid = {$vb_poll_id} WHERE pollid = {$import_poll_id}
					";

				$Db_object->query($sql);

				if ($Db_object->affected_rows())
				{
					return true;
				}
				else
				{
					return false;
				}
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* returns an array of usergroup => usergroupid
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	array
	*/
	function get_imported_group_ids_by_name(&$Db_object, &$databasetype, &$tableprefix)
	{
		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$user_groups = $Db_object->query("SELECT usergroupid, title FROM " . $tableprefix . "usergroup  WHERE importusergroupid <> 0");

				while ($group = $Db_object->fetch_array($user_groups))
				{
					$return_data["$group[title]"] = $group['usergroupid'];
				}
				$Db_object->free_result($user_groups);

				return $return_data;
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}


	/**
	* Updates vB3 poll ids after a vB3 import
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	*
	* @return	array
	*/
	function update_poll_ids(&$Db_object, &$databasetype, &$tableprefix)
	{
		if ($databasetype == 'mysql')
		{
			$result = $Db_object->query("SELECT pollid, threadid, importthreadid FROM " . $tableprefix . "thread WHERE open=10 AND pollid <> 0 AND importthreadid <> 0");

			while ($thread = $Db_object->fetch_array($result))
			{
				$new_thread_id = $Db_object->query_first("SELECT threadid FROM " . $tableprefix . "thread where importthreadid = ".$thread['pollid']);

				if($new_thread_id['threadid'])
				{
					// Got it
					$Db_object->query("UPDATE " . $tableprefix . "thread SET pollid =" . $new_thread_id['threadid'] . " WHERE threadid=".$thread['threadid']);
				}
				else
				{
					// Why does it miss some ????
				}
			}
		}
		else
		{
			return false;
		}
	}


	/**
	* Adds an index to a table
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	string	mixed			The table name
	*
	* @return	array
	*/
	function add_index(&$Db_object, &$databasetype, &$tableprefix, $tablename)
	{
		// Check that there is not a empty value
		if(empty($tablename)) { return false; }

		if ($databasetype == 'mysql')
		{
			$check_sql = "SHOW KEYS FROM `" .
			$tableprefix . $tablename . "`";

			$keys = $Db_object->query($check_sql);

			while ($key = $Db_object->fetch_array($keys))
			{
				if($key['Key_name'] == "import" . $tablename . "_index")
				{
					return true;
				}
			}

			$sql = "
			ALTER TABLE `" .
			$tableprefix . $tablename . "`
			ADD INDEX `import" . $tablename . "_index` ( `import" . $tablename . "id` )
			";

			$moo_baa_wee = $Db_object->query($sql);

			return true;
		}
		else
		{
			return false;
		}
	}


	/**
	* Updates parent ids of imported forums where parent id = 0
	*
	* @param	object	databaseobject	The database that the function is going to interact with.
	* @param	string	mixed			The type of database 'mysql', 'postgresql', etc
	* @param	string	mixed			The prefix to the table name i.e. 'vb3_'
	* @param	array	mixed			importforumid => forumid
	*
	* @return	array
	*/
	function clean_nested_forums(&$Db_object, &$databasetype, &$tableprefix, $importid)
	{
		if ($databasetype == 'mysql')
		{
			$sql = "
			SELECT forumid, importcategoryid FROM " .
			$tableprefix."forum
			WHERE
			parentid = 0
			AND
			importforumid <> 0";


			$do_list = $Db_object->query($sql);

			while ($do = $Db_object->fetch_array($do_list))
			{
				$catid = $do['importcategoryid'];
				$fid = $do['forumid'];
				if ($importid[$catid] AND $fid)
				{
					$sql = "UPDATE " . $tableprefix."forum SET parentid=" . $importid[$catid] . " WHERE forumid =" . $fid;
				}

				$Db_object->query($sql);
			}
		}
		else
		{
			return false;
		}
	}


	function update_user_pm_count(&$Db_object, &$databasetype, &$tableprefix)
	{
		if ($databasetype == 'mysql')
		{
			$users = $Db_object->query("SELECT userid, username FROM " . $tableprefix ."user");

			while ($user = $Db_object->fetch_array($users))
			{
				$pmcount = $Db_object->query("SELECT count(*) FROM " . $tableprefix ."pm WHERE userid = " . $user['userid']);
				
				$pms = $Db_object->fetch_array($pmcount);

				if(intval($pms[0]) != 0)
				{
					$Db_object->query("UPDATE " . $tableprefix ."user SET pmtotal=" . $pms[0] . " WHERE userid=" . $user['userid']);
				}
			}
			return true;
		}
		else
		{
			return false;
		}
	}


	function get_options_setting(&$Db_object, &$databasetype, &$tableprefix, $name)
	{
		if ($databasetype == 'mysql')
		{
			$options_return = $Db_object->query_first("SELECT data FROM " . $tableprefix ."datastore WHERE title='options'");

			$options_array = unserialize($options_return['data']);

			return $options_array[$name];
		}
		else
		{
			return false;
		}
	}


	function get_vb_post_user_id(&$Db_object, &$databasetype, &$tableprefix, $post_id)
	{
		// Check that there is not a empty value
		if(empty($post_id)) { return $return_array; }


		if ($databasetype == 'mysql')
		{
			$sql = "
			SELECT userid FROM " .
			$tableprefix."post
			WHERE postid={$post_id}
			";


			$details_list = $Db_object->query_first($sql);

			return $details_list['userid'];
		}
		else
		{
			return false;
		}
		return $return_array;
	}


	function select_profilefield_list(&$Db_object, &$databasetype, &$tableprefix, $title)
	{
		$return_array = array();

		switch ($databasetype)
		{
			// MySQL database
			case 'mysql':
			{
				$fieldid = $Db_object->query_first("SELECT profilefieldid FROM ". $tableprefix . "profilefield WHERE title = '$title'");

				// TODO: This will break with a 0 on field id, need to handel it a lot better and have a return
				if($fieldid['profilefieldid'])
				{
					$list = $Db_object->query("SELECT userid, field" . $fieldid['profilefieldid'] ." as $title FROM " . $tableprefix . "userfield");

					while ($fielddata = $Db_object->fetch_array($list))
					{
						if($fielddata[$title])
						{
							$return_array[$fielddata['userid']] = strtolower($fielddata[$title]);
						}
					}

					return $return_array;
				}
			}

			// Postgres database
			case 'postgresql':
			{
				return false;
			}

			// other
			default:
			{
				return false;
			}
		}
	}
}
/*======================================================================*\
|| ####################################################################
|| # Downloaded: 03:45, Mon Nov 13th 2006
|| # CVS: $RCSfile: ImpExDatabase.php,v $ - $Revision: 1.177 $
|| ####################################################################
\*======================================================================*/
?>
