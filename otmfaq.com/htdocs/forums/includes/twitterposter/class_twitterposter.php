<?php

// ################################
// # This class is written by Milad @ http://services.milado.net/
// ################################

require_once(DIR . '/includes/twitterposter/functions_twitterposter.php');

class vB_TweetPoster
{
	var $registry = null;
	var $dbobject = null;
	var $threadinfo = array();
	var $postinfo = array();
	var $status = '';
	var $length = 0;
	var $statusid = 0;
	var $screen_name = '';

	function __construct(&$registry)
	{
		if (is_object($registry))
		{
			$this->registry =& $registry;

			if (is_object($registry->db))
			{
				$this->dbobject =& $registry->db;
			}
			else
			{
				trigger_error('Database object is not an object', E_USER_ERROR);
			}
		}
		else
		{
			trigger_error('Registry object is not an object', E_USER_ERROR);
		}
	}
	
	function SetThreadInfo($threadinfo)
	{
		if (!$threadinfo['pagetext'] AND $threadinfo['firstpostid'])
		{
			$postinfo = fetch_postinfo($threadinfo['firstpostid']);
			$threadinfo['pagetext'] = $postinfo['pagetext'];
			unset($postinfo);
		}
		
		$this->threadinfo = $threadinfo;
		
		$this->SetStatusID($threadinfo['tweeted']);
		
		unset($threadinfo['pagetext']);
		
		twitterposter_print('<pre class="alt1">');
		twitterposter_print_r($threadinfo);
		twitterposter_print('</pre>');
	}
	
	function FetchThreadInfo($threadid)
	{
		$threadinfo = fetch_threadinfo($threadid, false);
		$this->SetThreadInfo($threadinfo);
	}
	
	function SetStatus($status)
	{
		$this->status = $status;
	}
	
	function SetStatusID($statusid)
	{
		if (!$this->statusid)
		{
			$this->statusid = $statusid;
		}
		else
		{
			return false;
		}
	}
	
	function prepare_url()
	{
		$this->threadinfo['url'] = $this->registry->options['bburl'] . '/showthread.php?t=' . $this->threadinfo['threadid'];
		$this->threadinfo['shortenedurl'] = false;
		
		if ($this->registry->products['crawlability_vbseo'] AND function_exists('vbseo_thread_url_row'))
		{
			$this->threadinfo['url'] = $this->registry->options['bburl'] . '/' . vbseo_thread_url_row($this->threadinfo); // Based on this tutorial: http://www.vbseo.com/f2/vbseo-functions-extensibility-1662/
			
			twitterposter_print('URL re-written by vBSEO!<br />' . $this->threadinfo['url'] . '<br />');
		}
		
		if ($this->registry->options['bitly_login'] AND $this->registry->options['bitly_apikey'])
		{
			require_once(DIR . '/includes/twitterposter/bit.ly.php');
			
			$bitly = new BitLy($this->registry->options['bitly_login'], $this->registry->options['bitly_apikey']);
			$bitly->AddURL($this->threadinfo['url']);
			$this->threadinfo['shortenedurl'] = $bitly->Shorten();
			
			if ($this->threadinfo['shortenedurl'])
			{
				twitterposter_print('<br />URL Shortened Successfully!<br />' . $this->threadinfo['shortenedurl'] . '<br />');
				$this->threadinfo['url'] = $this->threadinfo['shortenedurl'];
			}
			else
			{
				twitterposter_print('<br />URL Shortening Failed!<br />');
			}
			
			unset($bitly);
		}
		
		unset($this->threadinfo['shortenedurl']);
	}
	
	function convert_iconv()
	{
		if ($this->registry->options['twitter_iconv_source'] != 'none' AND function_exists('iconv'))
		{
			$this->threadinfo['title'] = iconv($this->registry->options['twitter_iconv_source'], 'utf-8', $this->threadinfo['title']);
			$this->threadinfo['pagetext'] = iconv($this->registry->options['twitter_iconv_source'], 'utf-8', $this->threadinfo['pagetext']);
			twitterposter_print('Title and preview have been converted to UTF-8 by iconv!<br />');
		}
	}
	
	function generate_status_message()
	{
		if ($this->threadinfo['threadid'])
		{
			$prefix = trim($this->registry->options['twitter_status_prefix']);
			
			if (defined('TWITTERPOSTER_HOTTHREAD'))
			{
				$prefix = 'HOT:';
			}
			
			if ($prefix != '')
			{
				$this->status .= $prefix . ' ';
				$this->length += strlen($this->status);
			}
		}
		
		$this->length += strlen($this->threadinfo['url']);
		
		if (($this->length + strlen($this->threadinfo['title'])) > 139) // 139 because there is at least one space between the title and the URL
		{
			 $this->threadinfo['title'] = fetch_trimmed_title($this->threadinfo['title'], (139 - $this->length), false);
		}
		
		$this->length += strlen($this->threadinfo['title']);
		
		$show['pagetext'] = false;
		
		if ($this->length < 138) // 138 because there is (: + space) after the title
		{
			$this->threadinfo['pagetext'] = strip_bbcode($this->threadinfo['pagetext'], true, false, false);
			$this->threadinfo['pagetext'] = strip_tags($this->threadinfo['pagetext']);
			$this->threadinfo['pagetext'] = str_replace("\r", "", $this->threadinfo['pagetext']);
			$this->threadinfo['pagetext'] = str_replace("\n", " ", $this->threadinfo['pagetext']);
			
			while (strpos($this->threadinfo['pagetext'], '  ') !== false)
			{
				$this->threadinfo['pagetext'] = str_replace('  ', ' ', $this->threadinfo['pagetext']);
			}
			
			$this->threadinfo['pagetext'] = fetch_trimmed_title($this->threadinfo['pagetext'], (138 - $this->length), false);
			$show['pagetext'] = true;
		}
		
		$this->status .= $this->threadinfo['title'];
		
		if ($show['pagetext'])
		{
			$this->status .= ': ' . $this->threadinfo['pagetext'];
		}
		
		$this->status .= ' ' . $this->threadinfo['url'];
		
		twitterposter_print('Tweet: ' . $this->status . '<br />');
		twitterposter_print('Length: ' . strlen($this->status) . '<br /><br />');
	}
	
	function tweet_status_message()
	{
		if ($this->status == '')
		{
			$this->prepare_url();
			$this->convert_iconv();
			$this->generate_status_message();
		}
		
		$content = $this->twitter_api('https://twitter.com/statuses/update.xml', array('status' => $this->status), 'POST');
		
		$content_array = $this->xml_parse($content);
		
		if (is_array($content_array))
		{
			if ($content_array['id'])
			{
				$this->statusid = $content_array['id'];
				$this->screen_name = $content_array['user']['screen_name'];
				
				if ($this->threadinfo['threadid'])
				{
					$this->post_tweet_status_message();
				}
			}
			
			twitterposter_print('<pre class="alt1">');
			twitterposter_print_r($content_array);
			twitterposter_print('</pre>');
		}
		
		$content = str_replace(array('<', '>'), array('&lt;', '&gt;'), $content);
		
		twitterposter_print('Twitter message:<br />');
		twitterposter_print('<pre class="alt1">' . $content . '</pre>');
		twitterposter_print('<hr />');
		
		unset($to, $content);
		
		if ($this->statusid)
		{
			return $this->statusid;
		}
		else
		{
			return false;
		}
	}
	
	function post_tweet_status_message()
	{
		$this->mark_thread_tweeted();
	}
	
	function detweet_status_message()
	{
		if (!$this->statusid)
		{
			return false;
		}
		
		$content = $this->twitter_api('https://twitter.com/statuses/destroy/' . $this->statusid . '.xml', array(), 'POST');
		
		$content_array = $this->xml_parse($content);
		
		if (is_array($content_array))
		{
			if ($content_array['id'] == $this->statusid)
			{
				if ($this->threadinfo['threadid'])
				{
					$this->mark_thread_detweeted();
				}
				
				return true;
			}
			else
			{
				return false;
			}
		}
	}
	
	function mark_thread_tweeted()
	{
		$this->dbobject->query_write("UPDATE " . TABLE_PREFIX . "thread SET tweeted = " . $this->statusid . ", tweet_screen_name = '" . $this->dbobject->escape_string($this->screen_name) . "' WHERE threadid = " . $this->threadinfo['threadid']);
		
		/*
		$threadman =& datamanager_init('Thread', $this->registry, ERRTYPE_SILENT, 'threadpost');
		$threadman->set_existing($this->threadinfo);
		$threadman->set('tweeted', $this->statusid);
		$threadman->set('tweet_screen_name', $this->screen_name);
		$threadman->save();
		unset($threadman);
		*/
	}
	
	function mark_thread_detweeted()
	{
		$this->dbobject->query_write("UPDATE " . TABLE_PREFIX . "thread SET tweeted = 0, tweet_screen_name = '' WHERE threadid = " . $this->threadinfo['threadid']);
		
		/*
		$threadman =& datamanager_init('Thread', $this->registry, ERRTYPE_SILENT, 'threadpost');
		$threadman->set_existing($this->threadinfo);
		$threadman->set('tweeted', 0);
		$threadman->set('tweet_screen_name', '');
		$threadman->save();
		unset($threadman);
		*/
	}
	
	function xml_parse($xml)
	{
		if (!$xml)
		{
			return false;
		}
		
		require_once(DIR . '/includes/class_xml.php');
		
		$xmlobj = new vB_XML_Parser($xml);
		
		return $xmlobj->parse();
	}
	
	function twitter_api($url, $args = array(), $method = NULL)
	{
		require_once(DIR . '/includes/twitterposter/twitteroauth/OAuth.php');
		require_once(DIR . '/includes/twitterposter/twitteroauth/twitterOAuth.php');
		
		$to = new TwitterOAuth($this->registry->options['twitter_consumer_key'], $this->registry->options['twitter_consumer_secret'], $this->registry->options['twitter_oauth_access_token'], $this->registry->options['twitter_oauth_access_token_secret']);
		
		return $to->OAuthRequest($url, $args, $method);
	}
}

?>