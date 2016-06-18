function tweetme(threadid)
{
	tweetlink = fetch_object('tweetlink').className = 'tweeting';
	
	do_tweet = new vB_AJAX_Handler(true);
	do_tweet.onreadystatechange(metweeted);
	do_tweet.send('ajax.php', 'do=tweetme&threadid=' + threadid);
}

function metweeted()
{
	if (do_tweet.handler.readyState == 4 && do_tweet.handler.status == 200)
	{
		if (do_tweet.handler.responseText == 'success')
		{
			fetch_object('tweetlink').className = 'twitterposter tweeted';
			fetch_object('tweetlink').title = vbphrase['already_tweeted'];
		}
		else
		{
			fetch_object('tweetlink').className = 'twitterposter untweeted';
			fetch_object('tweetlink').title = vbphrase['tweetme'];
			alert(do_tweet.handler.responseText);
		}
	}
}