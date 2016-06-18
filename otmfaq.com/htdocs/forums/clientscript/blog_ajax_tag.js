/*!======================================================================*\
|| #################################################################### ||
|| # vBulletin 2.0.2
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2000-2009 Jelsoft Enterprises Ltd. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/

vBulletin.events.systemInit.subscribe(function()
{
	if (AJAX_Compatible)
	{
		vB_Blog_Tag_Factory = new vB_Blog_Tag_Factory();
	}
});

// #############################################################################
// vB_Blog_Tag_Factory
// #############################################################################

/**
* Class for inline modification of blog entry tags
*
* @package	vBulletin
* @version	$Revision: 25662 $
* @date		$Date: 2008-02-04 14:33:20 -0800 (Mon, 04 Feb 2008) $
* @author	Freddie Bingham
*/
function vB_Blog_Tag_Factory()
{
	this.controls = new Array();
	this.init();
}

// =============================================================================
// vB_Blog_Tag_Factory methods

vB_Blog_Tag_Factory.prototype.init = function()
{
	if (vBulletin.elements["vB_Blog_Tag"])
	{
		for (var i = 0; i < vBulletin.elements["vB_Blog_Tag"].length; i++)
		{
			var objectid = vBulletin.elements["vB_Blog_Tag"][i];
			var openlink = YAHOO.util.Dom.get("blogtag_" + objectid);
			if (openlink)
			{
				this.controls[objectid] = new vB_Blog_TagLoader(objectid, this);
			}
		}
		vBulletin.elements["vB_Blog_Tag"] = null;
	}
}

/**
* Redirect upon failed Ajax
*
*/
vB_Blog_Tag_Factory.prototype.redirect = function(objectid)
{
	window.location = "blog_tag.php?" + SESSIONURL + "b=" + objectid;
}

// #############################################################################

/**
* Loads a single item
*
* @package	vBulletin
* @version	$Revision: 24798 $
* @date		$Date: 2007-11-22 13:59:49 +0000 (Thu, 22 Nov 2007) $
* @author	Freddie Bingham
*
* @param	string	Objectid of the message to be edited
* @param	vB_Blog_Tag_Factory	Controlling factory class
*/
function vB_Blog_TagLoader(objectid, factory)
{
	this.divobj = null;
	this.vbmenu = null;
	this.do_ajax_submit = true;

	this.divname = 'blogtagmenu_' + objectid + '_menu';
	this.entryobj = YAHOO.util.Dom.get("entry" + objectid);
	this.vbmenuname = 'blogtagmenu_' + objectid;
	this.edit_submit = 'blogtageditsubmit_' + objectid;
	this.edit_cancel = 'blogtageditcancel_' + objectid;
	this.edit_input = 'blogtageditinput_' + objectid;
	this.submit_progress = 'blogtageditprogress_' + objectid;
	this.tag_list = 'blogtaglist_' + objectid;
	this.tag_container = 'blogtagcontainer_' + objectid;

	this.init(objectid, factory);
}

/**
* Initialize the onclick action
*
* @return	boolean
*/
vB_Blog_TagLoader.prototype.init = function(objectid, factory)
{
	if (objectid)
	{
		this.objectid = objectid;
	}
	if (factory)
	{
		this.factory = factory;
	}

	var loadlink = YAHOO.util.Dom.get("blogtag_" + objectid);
	YAHOO.util.Event.on(loadlink, "click", this.load, this, true);
}

/**
* Prepare to display the tag popup
*
* @return	boolean	false
*/
vB_Blog_TagLoader.prototype.load = function(e)
{
	if (e)
	{
		YAHOO.util.Event.stopEvent(e);
	}

	 if (vBmenu.activemenu == this.vbmenuname)
	{
		this.vbmenu.hide();
	}
	else
	{
		YAHOO.util.Connect.asyncRequest("POST", "blog_tag.php?b=" + this.objectid, {
			success: this.display,
			failure: this.handle_ajax_error,
			timeout: vB_Default_Timeout,
			scope: this
		}, SESSIONURL + "securitytoken=" + SECURITYTOKEN + "&do=tagedit&b=" + this.objectid);
	}

	return false;
}

/**
* Handles AJAX Errors
*
* @param	object	YUI AJAX
*/
vB_Blog_TagLoader.prototype.handle_ajax_error = function(ajax)
{
	//TODO: Something bad happened, try again
	vBulletin_AJAX_Error_Handler(ajax);
}

/**
* Handles an error in the AJAX submission of form contents.
*/
vB_Blog_TagLoader.prototype.handle_ajax_submit_error = function(ajax)
{
	vBulletin_AJAX_Error_Handler(ajax);
	this.do_ajax_submit = false;
}

/**
* Display the editor HTML when AJAX says fetch_editor() is ready
*
* @param	object	YUI AJAX
*/
vB_Blog_TagLoader.prototype.display = function(ajax)
{
	if (ajax.responseXML)
	{
		var error = ajax.responseXML.getElementsByTagName('error');
		if (error.length)
		{
			alert(error[0].firstChild.nodeValue);
		}
		else
		{
			if (!this.divobj)
			{
				this.divobj = document.createElement('div');
				this.divobj.id = this.divname;
				this.divobj.style.display = 'none';
				this.divobj.style.width = '300px';
				this.entryobj.parentNode.appendChild(this.divobj);

				this.vbmenu = vbmenu_register(this.vbmenuname, true);
				YAHOO.util.Dom.get(this.vbmenu.controlkey).onmouseover = '';
				YAHOO.util.Dom.get(this.vbmenu.controlkey).onclick = '';
			}

			this.divobj.innerHTML = ajax.responseXML.getElementsByTagName('tagpopup')[0].firstChild.nodeValue;

			YAHOO.util.Event.on(this.edit_submit, 'click', this.submit_tag_edit, this, true);
			YAHOO.util.Event.on(this.edit_cancel, 'click', this.cancel_tag_edit, this, true);
			YAHOO.util.Event.on(this.divobj, "keydown", this.tagmenu_keypress);

			this.vbmenu.show(YAHOO.util.Dom.get(this.vbmenuname));
			// see #25376
			YAHOO.util.Dom.get(this.edit_input).focus();
			YAHOO.util.Dom.get(this.edit_input).focus();
		}
	}
}

/**
*	Catches the keypress of the controls to keep them from submitting to inlineMod
*
* @param	event
*/
vB_Blog_TagLoader.prototype.tagmenu_keypress = function (e)
{
	switch (e.keyCode)
	{
		case 13:
		{
			vB_Blog_Tag_Factory.controls[this.id.split(/_/)[1]].submit_tag_edit();
			if (e)
			{
				YAHOO.util.Event.stopEvent(e);
			}
			return false;
		}
		default:
		{
			return true;
		}
	}
}

/**
*	Submit tag data
*
* @param	event
*/
vB_Blog_TagLoader.prototype.submit_tag_edit = function (e)
{
	if (this.do_ajax_submit)
	{
		if (e)
		{
			YAHOO.util.Event.stopEvent(e);
		}

		var hidden_form = new vB_Hidden_Form("blog_tag.php");

		hidden_form.add_variables_from_object(YAHOO.util.Dom.get(this.divobj));
		if (typeof vBulletin.elements["vB_Blog_Userid"] != "undefined")
		{
			hidden_form.add_variable("userid", vBulletin.elements["vB_Blog_Userid"]);
		}

		YAHOO.util.Connect.asyncRequest("POST", "blog_tag.php?b=" + this.objectid, {
			success: this.handle_ajax_submit,
			failure: this.handle_ajax_submit_error,
			timeout: vB_Default_Timeout,
			scope: this
		}, hidden_form.build_query_string());

		if (YAHOO.util.Dom.get(this.submit_progress))
		{
			YAHOO.util.Dom.get(this.submit_progress).style.display = '';
		}
	}
}

/**
*	Submit tag data
*
* @param	event
*/
vB_Blog_TagLoader.prototype.cancel_tag_edit = function (e)
{
	this.vbmenu.hide();
}

/**
* Handles the AJAX response to submitting the tag form.
*/
vB_Blog_TagLoader.prototype.handle_ajax_submit = function(ajax)
{
	if (ajax.responseXML)
	{
		// check for error first
		var error = ajax.responseXML.getElementsByTagName('error');
		if (error.length)
		{
			alert(error[0].firstChild.nodeValue);
		}
		else
		{
			var taghtml = ajax.responseXML.getElementsByTagName('taghtml');
			if (taghtml.length && taghtml[0].firstChild && taghtml[0].firstChild.nodeValue !== '')
			{
				// this should only happen if they didn't add any tags, and we want to leave the "none" option
				YAHOO.util.Dom.get(this.tag_list).innerHTML = taghtml[0].firstChild.nodeValue;
				YAHOO.util.Dom.get(this.tag_container).style.display = '';
			}
			else
			{
				YAHOO.util.Dom.get(this.tag_container).style.display = 'none';
			}

			var warning = ajax.responseXML.getElementsByTagName('warning');
			if (warning.length && warning[0].firstChild)
			{
				alert(warning[0].firstChild.nodeValue);
			}

			this.vbmenu.hide();
		}
	}

	if (YAHOO.util.Dom.get(this.submit_progress))
	{
		YAHOO.util.Dom.get(this.submit_progress).style.display = 'none';
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 09:18, Thu Jul 23rd 2009
|| # CVS: $RCSfile$ - $Revision: 25811 $
|| ####################################################################
\*======================================================================*/