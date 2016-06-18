<?php

/* $Id: mime.php,v 1.11 2004/11/08 02:05:37 ns Exp $ */

/*!
	MIME Class
	This class is multi-purpose:  you can create and send mime-encoded 
	emails or decode mime messages with it.
	
	Modified by Nikol S <ns@eyo.com.au> May 2004
	* Handle UUencoded messages
	* Handle Content-Disposition == inline
	* Handle multipart/encrypted
	* Handle multipart/signed

	* Fixed: Content-Type is case insensitive. eg. Content-Type = tExt/plain

	8 Aug 2004 Nikol S <ns@eyo.com.au>
	* Added support for multipart/mixed in the mime part header
	* Added support for encoded plain text messages, typically sent by Outlook Express
*/

class mime {

	var $recipients 	= Array();
	var $attachments 	= Array();
	var $headers 		= Array();
	var $mime_parts		= Array();
	var $headers_dec	= Array();
	var $encoded_text	='';

	/* --- MIME Encoding Section -- */

	/*!
		-- MIME Encoding --
		Use the to() function to add recipients.  Takes variable number of 
		arguments, adds each one to the recipients list.  Call as needed.
	*/

	function to() {
		$argv = func_get_args();
		foreach ($argv AS $email) {
			$this->recipients[] = $email;
		}
	}

	/*!
		-- MIME Encoding --
		Use add_attachment($contents, $content_type, $filename = '') to add
		attachments to the mime encoded email.
	*/

	function add_attachment($contents, $content_type, $filename = '') {

		$this->attachments[] = Array(	"contents" => $contents, 
										"content-type" => $content_type, 
										"filename" => $filename);

	}

	/*!
		-- MIME Encoding --
		Use add_text($contents) to add the text/plain piece that will 
		be displayed as the actual contents of the document.
	*/

	function add_text($contents) {

		$this->add_attachment($contents, "text/plain");

	}

	/*!
		-- MIME Encoding --
		Simple function to set the subject of the outgoing MIME email.
	*/

	function set_subject($subject) {
		
		$this->subject = $subject;

	}

	/*!
		-- MIME Encoding --
		Simple function to add a header to an array of customer headers 
		for this email.
	*/

	function add_header($header) {

		$this->headers[] = $header;

	}

	/*!
		-- MIME Encoding --
		Simple function to encode text into quoted-    
		printable text.	Fully RFC 1521 compliant.			 
		\private
	*/

	function quoted_printable_encode($str) {

		// OK, first, we need to loop through the entire string and replace 
		// all characters that should be.
		// RFC 1521 Rule #2 says 33-60 and 62-126 may be represented as  
		// ascii, as well as 9 (tab) and 32 (space). Carriage returns and 
		// line feeds should always be represented as themselves.

		for ($i = 0; $i < strlen($str); $i++) {
			$char = ord($str[$i]);
			if (($char < 33 || $char == 61 || $char > 126) && $char != 9 
				&& $char != 32 && $char != 10 && $char != 13) 
			{
				// If not, replace.
				$strl = substr($str, 0, $i); $strr = substr($str, $i+1);
				$hexchar = strtoupper(dechex($char));
				$hexchar = (strlen($hexchar) == 1) ? "0".$hexchar : $hexchar;
				$str = $strl."=".$hexchar.$strr;
				$i+=2;		// String grew by two, move pointer up two.
			}
		}
	
		// Remove carriage returns for right now.
		$str = str_replace("\r", "", $str);

		// Now, split by line breaks
		$str_array = split("\n", $str);

		// Move through array of lines to cut each down to less that 76 
		// characters per line (RFC 822)
		for ($i = 0; $i < sizeof($str_array); $i++) {

			if (strlen($str_array[$i]) > 73) {
				array_splice($str_array, $i + 1, 0, substr($str_array[$i], 73));
				$str_array[$i] = substr($str_array[$i], 0, 73)."=\r\n";
			} else {
				$str_array[$i].= "\r\n";
			}
		}
		return (implode("", $str_array));
	}

	/*!
		-- MIME Encoding --
		send() - create the mime message and send it to the listed recipients.
	*/
	function send() {

		srand((double) microtime()*1000000);
		$rnum = rand();

		$boundary = "=_".md5($rnum);

		$message = "This message is in MIME format.\n\n";

		foreach($this->attachments AS $attachment) {

			$message.= "--".$boundary."\n";
			$message.= "Content-Type: ".$attachment["content-type"];
			if ($attachment["content-type"] == "text/plain") {
				$message.= "; charset=ISO-8859-1\n";
				$message.= "Content-Transfer-Encoding: Quoted-Printable\n\n";
				$message.= $this->quoted_printable_encode($attachment["contents"])."\n";
			} else {
				$message.= "; name=\"".$attachment["filename"].
							"\"\nContent-Transfer-Encoding: base64\n";
				$message.= "Content-Disposition: attachment; filename=\"".
							$attachment["filename"]."\"\n\n";
				$message.= chunk_split(base64_encode($attachment["contents"]))."\n";
			}

		}
		$message.= "--".$boundary."--\n";

		$headers = "MIME-Version: 1.0\n";
		$headers.= "Content-Type: multipart/mixed;".chr(10).chr(9).
					"boundary=\"$boundary\"";

		if (is_array($this->headers)) {
			foreach($this->headers AS $header) {
				$headers.= "\n$header";
			}
		}

		foreach($this->recipients AS $email) {
			mail($email, $this->subject, $message, $headers);
		}

	}

	/* --- MIME Decoding Section --- */

	/*!
		-- MIME Decoding --
		decode($msg_text) takes the actual text of the message as it's only
		argument and decodes a MIME message.
	*/

	function decode($msg_text) {
		$this->msg_text = $msg_text;
		$this->encoded_text = str_replace("\r", "", $msg_text);
		$this->parse_msg_headers();
		// If there is no mime-version, it has to be text
		if (!$this->headers_dec['mime-version']) {
 			$this->mime_parts[0] = Array("headers"=>  	
								Array("content-type" => "text/plain",
								"content-transfer-encoding" => "7bit"),
							"body"=> substr($this->encoded_text, 
								strpos($this->encoded_text, "\n\n") + 2));
		} else {
			if ($this->mime_boundary) {
				$this->decode_body(substr($this->encoded_text, strpos($this->encoded_text, "--".$this->mime_boundary)), $this->mime_boundary);
			} else {
				if (!$this->headers_dec['content-transfer-encoding']) {
					$my_cte = "7bit";
				} else {
					preg_match("/\"?([^(;|\"|$|\n)]*)(;|$|\"|\n)/i", $this->headers_dec['content-transfer-encoding'], $match);
					$my_cte = strtolower($match[1]);
				}

				if (!$this->headers_dec['content-type']) {
					$my_ct = "text/plain";
				} else {
					preg_match("/\"?([^(;|\"|$|\n)]*)(;|$|\"|\n)/i", $this->headers_dec['content-type'], $match);
					$my_ct = strtolower($match[1]);
				}
				$this->mime_parts[0] = array("headers"=>
								array("content-type" => $my_ct,
								"content-transfer-encoding" => $my_cte),
							"body" => substr($this->encoded_text, 
                                                                strpos($this->encoded_text, "\n\n") + 2));
			}
		}
		//$this->encoded_text = ""; //we do it in the UUdecoding setion
	}

	/*!
		-- MIME Decoding --
		Parses the main headers of the email.
		\private
	*/

	function parse_msg_headers() {

		$headers = substr($this->encoded_text, 0, 
					strpos($this->encoded_text, "\n\n"))."\n";

	        $lines = explode("\n", $headers);
	        foreach ($lines as $line) {
        	    $line = trim($line);
	            if (($pos = strpos($line, ':')) !== false) {
	                $head = substr($line, 0, $pos);
        	        $this->headers_dec[strtolower($head)] = ltrim(substr($line, $pos+1));

           	    } else {
	                $this->headers_dec[strtolower($head)] .= $line;
	            }
	        }

		preg_match("/\n?\t?boundary=\"?(.*?)\"?\s*[;|\n]/i", $headers, $matches);
                $this->mime_boundary = $matches[1];

	}

	/*!
		-- MIME Decoding --
		Recursive function to decode a body part.  Calls itself if it
		encounters a multipart/alternative encoded email.
		\private
	*/

	function decode_body($body, $boundary) {
		$body_pieces = explode("--".$boundary, $body);
		for ($i = 1; $i < sizeof($body_pieces) - 1; $i++) {
			$headers = $this->get_part_headers($body_pieces[$i]);
			$part_body = substr($body_pieces[$i], 
							strpos($body_pieces[$i], "\n\n") + 2);
			
			if ($headers["content-type"] == "multipart/alternative") {
				$this->decode_body(substr($part_body, 
					strpos($part_body, "--".$headers["boundary"])), $headers["boundary"]);
                        } else if ($headers["content-type"] == "multipart/mixed") {
                                $this->decode_body(substr($part_body,
                                     strpos($part_body, "--".$headers["boundary"])), $headers["boundary"]);
			} else if ($headers["content-type"] == "multipart/encrypted") {
                                $this->decode_body(substr($part_body,
                                     strpos($part_body, "--".$headers["boundary"])), $headers["boundary"]);
			} else if ($headers["content-type"] == "multipart/signed") {
                                $this->decode_body(substr($part_body,
                                     strpos($part_body, "--".$headers["boundary"])), $headers["boundary"]);
			} else {
				$this->mime_parts[] = Array("headers" => $headers, "body" => $part_body );
			}

		}

	}		

	/*!
		-- MIME Decoding --
		Pass it the text of a part, it will return an array of the headers
		defined for that part.
		\private
	*/

	function get_part_headers($text) {

		$headers = substr($text, 0, strpos($text, "\n\n"));
		$head_pieces = split("\n", $headers);
		foreach($head_pieces AS $header_line) {
			preg_match_all("/(\s|\t){0,}([a-z0-9-]*)(=|:)\s?\"?([^(;|\"|$|\n)]*)(;|$|\"|\n)/i", $header_line, $matches);
			for($i = 0; $i < sizeof($matches[2]); $i++) {
				if ($matches[2][$i] && $matches[4][$i]) {
					if (strtolower($matches[2][$i]) == 'content-type') {
						$ret_headers[strtolower($matches[2][$i])] = strtolower($matches[4][$i]); 

					} else {
						$ret_headers[strtolower($matches[2][$i])] = $matches[4][$i];
					}
				}
			}
		}
		return $ret_headers;
	}

	/*!
		-- MIME Decoding --
		User function to retrieve an array of the message parts.
	*/

	function get_msg_array() {

		// Need an array of the contents we can work with.
		$mime_parts_tmp = $this->mime_parts;

		// Put message headers in array first
		foreach($this->headers_dec AS $key => $value) {
			$ret_msg_array[$key] = $value;
		}

		// Get text (if there is one)
		foreach($mime_parts_tmp AS $key => $part_array) {
			if ($part_array['headers']['content-type'] == 'text/plain' 
				&& (!isset($part_array['headers']['content-disposition'])
				|| $part_array['headers']['content-disposition'] == 'inline')) 
			{
				$ret_msg_array["text"] .= $this->decode_part($part_array["body"], $part_array["headers"]["content-transfer-encoding"]);
				unset($mime_parts_tmp[$key]);
				//get rid of the uuencoded contents
			        $ret_msg_array['text'] = 
				preg_replace("/begin ([0-7]{3}) (.+)\r?\n(.+)\r?\nend/Us", "", $ret_msg_array['text']);
			}
		}
		foreach($mime_parts_tmp AS $key => $part_array) {
			if ($part_array["headers"]["content-type"] == "text/html" && 
				$ret_msg_array["html"] == "") 
			{
				$ret_msg_array["html"] = $this->decode_part($part_array["body"], 
							$part_array["headers"]["content-transfer-encoding"]);
				unset($mime_parts_tmp[$key]);
			}
		}

                foreach($mime_parts_tmp AS $key => $part_array) {
                        if ($part_array["headers"]["content-type"] == "application/pgp-encrypted")
                        {
                                $ret_msg_array["text"] .= $this->decode_part($part_array["body"],
                                                        $part_array["headers"]["content-transfer-encoding"]);
                                unset($mime_parts_tmp[$key]);
                        }
                }

                foreach($mime_parts_tmp AS $key => $part_array) {
                        if ($part_array["headers"]["content-type"] == "application/pgp-signature")
                        {
                                $ret_msg_array["text"] .= $this->decode_part($part_array["body"],
                                                        $part_array["headers"]["content-transfer-encoding"]);
                                unset($mime_parts_tmp[$key]);
                        }
                }

		$attachmentid = 0;
		foreach($mime_parts_tmp AS $key => $part_array) {
			$ret_msg_array["attachment".++$attachmentid] = $part_array;
			$ret_msg_array["attachment".$attachmentid]["body"] = 
				$this->decode_part(
					$ret_msg_array["attachment".$attachmentid]["body"], 
					$ret_msg_array["attachment".$attachmentid]["headers"]["content-transfer-encoding"]);
		}

		//Decode UUEncoded parts
		$uudecoded = $this->uudecode($this->encoded_text);

		if (!empty($uudecoded)) {
		   foreach($uudecoded AS $decoded) {
			$attachmentid++;
			$ret_msg_array['attachment'. $attachmentid]['headers']['filename'] = 
				$decoded['filename'];
			$ret_msg_array['attachment'. $attachmentid]['body'] = $decoded['filedata'];
		   }
		}
		$ret_msg_array["attachments"] = $attachmentid;
		return $ret_msg_array;
	}

	/*!
		-- MIME Decoding --
		Simple piece to decode a specific part based on its encoding.
		\private
	*/

	function decode_part($contents, $encoding) {
		$encoding = strtolower($encoding);
		switch($encoding) {
		case "base64":
			return base64_decode(trim($contents));
			break;
		case "quoted-printable":
			return quoted_printable_decode($contents);
			break;
		default:
			return $contents;
			break;
		}
	}

    function uudecode($input)
    {
        // Find all uuencoded sections
        preg_match_all("/begin ([0-7]{3}) (.+)\r?\n(.+)\r?\nend/Us", $input, $matches);

	$this->encoded_text = '';

        for ($j = 0; $j < count($matches[3]); $j++) {

            $str      = $matches[3][$j];
            $filename = $matches[2][$j];
            $fileperm = $matches[1][$j];

            $file = '';

//		$str = preg_replace("/\n\n`$/", "", $str);
		$str = str_replace("`", " ", trim($str));
            $str = preg_split("/\r?\n/", $str);
            $strlen = count($str);

            for ($i = 0; $i < $strlen; $i++) {
                $pos = 1;
                $d = 0;

                $len=(int)(((ord(substr($str[$i],0,1)) -32) - ' ') & 077);

                while (($d + 3 <= $len) AND ($pos + 4 <= strlen($str[$i]))) {
                    $c0 = (ord(substr($str[$i],$pos,1)) ^ 0x20);
                    $c1 = (ord(substr($str[$i],$pos+1,1)) ^ 0x20);
                    $c2 = (ord(substr($str[$i],$pos+2,1)) ^ 0x20);
                    $c3 = (ord(substr($str[$i],$pos+3,1)) ^ 0x20);
                    $file .= chr(((($c0 - ' ') & 077) << 2) | ((($c1 - ' ') & 077) >> 4));

                    $file .= chr(((($c1 - ' ') & 077) << 4) | ((($c2 - ' ') & 077) >> 2));

                    $file .= chr(((($c2 - ' ') & 077) << 6) |  (($c3 - ' ') & 077));

                    $pos += 4;
                    $d += 3;
                }

                if (($d + 2 <= $len) && ($pos + 3 <= strlen($str[$i]))) {
                    $c0 = (ord(substr($str[$i],$pos,1)) ^ 0x20);
                    $c1 = (ord(substr($str[$i],$pos+1,1)) ^ 0x20);
                    $c2 = (ord(substr($str[$i],$pos+2,1)) ^ 0x20);
                    $file .= chr(((($c0 - ' ') & 077) << 2) | ((($c1 - ' ') & 077) >> 4));

                    $file .= chr(((($c1 - ' ') & 077) << 4) | ((($c2 - ' ') & 077) >> 2));

                    $pos += 3;
                    $d += 2;
                }

                if (($d + 1 <= $len) && ($pos + 2 <= strlen($str[$i]))) {
                    $c0 = (ord(substr($str[$i],$pos,1)) ^ 0x20);
                    $c1 = (ord(substr($str[$i],$pos+1,1)) ^ 0x20);
                    $file .= chr(((($c0 - ' ') & 077) << 2) | ((($c1 - ' ') & 077) >> 4));

                }

            }
            $files[] = array('filename' => $filename, 'fileperm' => $fileperm, 'filedata' => $file);
        }

        return $files;
    }

				
}

/*
 * Local Variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 */

?>
