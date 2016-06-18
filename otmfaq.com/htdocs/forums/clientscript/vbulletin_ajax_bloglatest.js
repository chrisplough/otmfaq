/*!======================================================================*\
|| #################################################################### ||
|| # vBulletin 1.0.5
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2000-2008 Jelsoft Enterprises Ltd. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/

/**
* Class to handle thread rating
*
* @param	object	The form object containing the vote options
*/
function vB_AJAX_BlogLatest(varname)
{
	// AJAX handler
	this.xml_sender = null;

	this.varname = varname;
	this.active = null;
	this.noresults = 0;

	this.containers = new Array('latestblogs', 'latestcomments');

	// Closure
	var me = this;

	this.init = function()
	{
		if (AJAX_Compatible && (typeof vb_disable_ajax == 'undefined' || vb_disable_ajax < 2))
		{
			for (var i = 0; i < this.containers.length; i++)
			{
				var images = fetch_object(this.containers[i]).getElementsByTagName('img');
				if (images.length)
				{
					for (var j = 0; j < images.length; j++)
					{
						img_alt_2_title(images[j]);
					}
				}
			}

			var bloglatest_link = fetch_object('vb_bloglatest_latest_link');
			var blograting_link = fetch_object('vb_bloglatest_rating_link');
			var blogblograting_link = fetch_object('vb_bloglatest_blograting_link');
			var bloglatest_more = fetch_object('vb_bloglatest_latest_findmore');
			var blograting_more = fetch_object('vb_bloglatest_rating_findmore');
			var blogblograting_more = fetch_object('vb_bloglatest_blograting_findmore');

			if (this.active == null)
			{
				// default value
				this.active = 'latest';
				if (blogblograting_link && blogblograting_link.style.display == 'none')
				{
					this.active = 'blograting';
				}
				else if (blograting_link && blograting_link.style.display == 'none')
				{
					this.active = 'rating';
				}
			}

			if (blogblograting_link)
			{
				blogblograting_link.varname = this.varname;
				blogblograting_link.type = 'blog';
				blogblograting_link.which = 'blograting';
				blogblograting_link.onclick = load_data;
				blogblograting_link.style.cursor = pointer_cursor;

				blogblograting_link.style.display = (this.active == 'blograting') ? 'none' : '';
				fetch_object('vb_bloglatest_blograting_findmore').style.display = (this.active == 'blograting' && this.noresults == 0) ? '' : 'none';
			}

			fetch_object('vb_bloglatest_blograting').style.display = (this.active != 'blograting') ? 'none' : '';

			if (blograting_link)
			{
				blograting_link.varname = this.varname;
				blograting_link.type = 'blog';
				blograting_link.which = 'rating';
				blograting_link.onclick = load_data;
				blograting_link.style.cursor = pointer_cursor;

				blograting_link.style.display = (this.active == 'rating') ? 'none' : '';
				fetch_object('vb_bloglatest_rating_findmore').style.display = (this.active == 'rating' && this.noresults == 0) ? '' : 'none';
			}

			fetch_object('vb_bloglatest_rating').style.display = (this.active != 'rating') ? 'none' : '';

			if (bloglatest_link)
			{
				bloglatest_link.varname = this.varname;
				bloglatest_link.type = 'blog';
				bloglatest_link.which = 'latest';
				bloglatest_link.onclick = load_data;
				bloglatest_link.style.cursor = pointer_cursor;

				bloglatest_link.style.display = (this.active == 'latest') ? 'none' : '';
				fetch_object('vb_bloglatest_latest_findmore').style.display = (this.active == 'latest') ? '' : 'none';

			}

			fetch_object('vb_bloglatest_latest').style.display = (this.active != 'latest') ? 'none' : '';
		}
	}

	/**
	* OnReadyStateChange callback. Uses a closure to keep state.
	* Remember to use me instead of this inside this function!
	*/
	this.handle_ajax_response = function()
	{
		if (me.xml_sender.handler.readyState == 4 && me.xml_sender.handler.status == 200)
		{
			fetch_object('progress_latest').style.display = 'none';
			if (me.xml_sender.handler.responseXML)
			{
				var obj = fetch_object(me.objid);
				// check for error first
				var error = me.xml_sender.fetch_data(fetch_tags(me.xml_sender.handler.responseXML, 'error')[0]);
				if (error)
				{
					alert(error);
				}
				else
				{
					var updateinfo =  fetch_tags(me.xml_sender.handler.responseXML, 'updated')[0];
					var type = updateinfo.getAttribute('type');
					var which = updateinfo.getAttribute('which');
					var data = updateinfo.getAttribute('data');
					me.noresults = updateinfo.getAttribute('noresults');

					me.active = which;

					if (data != '')
					{
						if (type == 'blog')
						{
							fetch_object('latestblogs').innerHTML = data;
						}
						else
						{
							fetch_object('latestcomments').innerHTML = data;
						}
						me.init();

					}
				}
			}

			if (is_ie)
			{
				me.xml_sender.handler.abort();
			}
		}
	}

	this.init();
};


function load_data(e)
{
	var latestObj = eval(this.varname);

	fetch_object('progress_latest').style.display = '';
	latestObj.xml_sender = new vB_AJAX_Handler(true);
	latestObj.xml_sender.onreadystatechange(latestObj.handle_ajax_response);
	latestObj.xml_sender.send(
		'blog_ajax.php?do=loadupdated&type=' + PHP.urlencode(this.type) + '&which=' + PHP.urlencode(this.which), 'do=loadupdated&type=' + PHP.urlencode(this.type) + '&which=' + PHP.urlencode(this.which) + '&ajax=1'
	);

	return false;
};

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 03:15, Tue Dec 9th 2008
|| # CVS: $RCSfile$ - $Revision: 25812 $
|| ####################################################################
\*======================================================================*/