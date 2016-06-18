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
* Class to handle the mini calendar
*
* @param	object	The form object containing the vote options
*/
function vB_AJAX_BlogCalendar(varname, calobj, month, year, userid)
{
	// AJAX handler
	this.xml_sender = null;

	this.month = month;
	this.year = year;
	this.calobj = calobj;
	this.varname = varname;
	this.userid = userid;

	// Closure
	var me = this;

	this.init = function()
	{
		var calobj = fetch_object(this.calobj);

		if (AJAX_Compatible && (typeof vb_disable_ajax == 'undefined' || vb_disable_ajax < 2) && calobj)
		{
			var tds = fetch_tags(calobj, 'td');
			for (var i = 0; i < tds.length; i++)
			{
				var day = tds[i].id.substr(tds[i].id.lastIndexOf('_') + 1);
				var month = tds[i].id.substr(20, tds[i].id.lastIndexOf('_') - 20);

				if (link = fetch_object('vb_blogcalendar_href_' + month + '_' + day))
				{
					tds[i].onclick = this.dayclick;
					tds[i].style.cursor = pointer_cursor;
				}
			}

			if (nextmonth = fetch_object('vb_blogcalendar_nextmonth'))
			{
				nextmonth.blogcalendarid = this.varname;
				nextmonth.type = 'next';
				nextmonth.onclick = swap_month;
				nextmonth.style.cursor = pointer_cursor;
			}

			if (prevmonth = fetch_object('vb_blogcalendar_prevmonth'))
			{
				prevmonth.blogcalendarid = this.varname;
				prevmonth.type = 'prev';
				prevmonth.onclick = swap_month;
				prevmonth.style.cursor = pointer_cursor;
			}
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
					var calendar = me.xml_sender.fetch_data(fetch_tags(me.xml_sender.handler.responseXML, 'calendar')[0]);
					if (calendar != '')
					{

						fetch_object(me.calobj).innerHTML = calendar;
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

	this.dayclick = function()
	{
		var day = this.id.substr(this.id.lastIndexOf('_') + 1);
		var month = this.id.substr(20, this.id.lastIndexOf('_') - 20);

		var location = fetch_object('vb_blogcalendar_href_' + month + '_' + day);
		window.location = location.href;
		return false;
	}

	this.init();
};


function swap_month(e)
{
	var calendarObj = eval(this.blogcalendarid);

	if (this.type == 'next')
	{
		var currentmonth = calendarObj.month;
		calendarObj.month= (calendarObj.month == 12) ? 1 : calendarObj.month + 1;
		calendarObj.year = (currentmonth == 12) ? ( calendarObj.year == 2037 ? 1970 : calendarObj.year + 1) : calendarObj.year;
	}
	else
	{
		var currentmonth = calendarObj.month;
		calendarObj.month = (calendarObj.month == 1) ? 12 : calendarObj.month - 1;
		calendarObj.year = (currentmonth == 1) ? (calendarObj.year == 1970 ? 2037 : calendarObj.year - 1) : calendarObj.year;
	}

	calendarObj.xml_sender = new vB_AJAX_Handler(true);
	calendarObj.xml_sender.onreadystatechange(calendarObj.handle_ajax_response);
	calendarObj.xml_sender.send(
		'blog_ajax.php?do=calendar&m=' + calendarObj.month + '&y=' + calendarObj.year + (calendarObj.userid != 'undefined' ? '&u=' + calendarObj.userid : ''), 'do=calendar&m=' + calendarObj.month + '&y=' + calendarObj.year + '&ajax=1' + (calendarObj.userid != 'undefined' ? '&u=' + calendarObj.userid : '')
	);

	return false;
};

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 03:15, Tue Dec 9th 2008
|| # CVS: $RCSfile$ - $Revision: 25812 $
|| ####################################################################
\*======================================================================*/