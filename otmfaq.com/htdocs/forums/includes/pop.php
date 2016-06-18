<?php // $Id: pop.php,v 1.4 2004/10/31 11:37:35 ns Exp $


//	Nikol S <ns@eyo.com.au> May 2004
//	A more than simple pop class

class pop {

	var $username = '';
	var $password = '';
	var $host = '';
	var $port = '';

	var $pop_connect = '';
	var $log = '';
	var $delete = '0';

	function prepare_connection( $user, $pass, $host = "127.0.0.1", $port = "110" )
	{
		if ($user == '') { 
			return 0; 
		}
		if ($pass == '') { 
			return 0; 
		}
		if ($host == '') { 
			return 0; 
		}
		$this->port = $port;
		$this->username = $user;
		$this->password = $pass;
		$this->host = $host;
		return 1;
	}

	// Connect to your pop server, return how many messages, or an error code

	function connect()
	{
		$this->pop_connect = fsockopen($this->host, $this->port, $error_number, $error_string, 30);
		if (!$this->pop_connect)
		{
			echo "$error_string ($error_number)\r\n";
			return -1;
		}
		$results = $this->_gets();
		$this->log .= $results;
		if ( $this->_check($results) )
		{
			$this->_write("USER $this->username");
			$results = $this->_gets();
			$this->log .= $results;
			if ( $this->_check($results) )
			{
				$this->_write("PASS $this->password");
				$results = $this->_gets();
				$this->log .= $results;
				if ( $this->_check($results) ) 
				{
					return 1;
				}
				else
					return -4;
			} else
				return -3;
		} 			
		return -2;
	}

	//How many emails avaiable
	function howmany()
	{
		return $this->_howmany();
	}

	function _howmany()
	{
		$this->_write("STAT");
		$results = $this->_gets();
		$this->log .= $results;
		list ($results, $messages, $bytes) = split(' ', $results);
		return $messages;
	}

	//See if we get +OK from the server
	function _check ($results)
	{
		if (preg_match("/\+OK/", $results))
			return 1;
		else
			return 0;
	}


	//  Read a line from the resource
        function _gets( $bytes = 512 )
        {
                $results = '';
                $results = fgets($this->pop_connect, $bytes);
                return $results;
        }

	// Send to the resource
	function _write($message)
	{
		$this->log .= $message . "\n";
		fwrite($this->pop_connect, $message . "\r\n");
	}

	//Return the logs we have so far
	function showlog()
	{
		return $this->log;
	}

	//Delete email after retrieval if this is called
	function delete()
	{
		$this->delete = "1";
	}

	//Retrieve the entire email
	function getemail ( $id )
	{
		$this->_write("RETR $id");
		$results = $this->_gets();
		$this->log .= $results;
		if ($this->_check($results)) {
			$email = '';
				$line = $this->_gets();
				while ( !ereg("^\.\r\n", $line)) {
					$email .= $line;
		       	                 $line = $this->_gets();
					if(empty($line)) {
						break; 
					}
				}
		}
		return $email;
	}

	//delete the email from the server
	function delete_mail ( $id )
	{
		$this->_write("DELE $id");
		$results = $this->_gets();
	}

	//disconnect from the server
	function disconnect ()
	{
		$this->_write("QUIT");
		$this->log .= $this->_gets();
	}

}
