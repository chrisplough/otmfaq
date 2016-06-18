/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 1.0.0
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2000-2007 Jelsoft Enterprises Ltd. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/

vBulletin.events.systemInit.subscribe(function()
{
	new vB_UserfieldEditor_Factory();
});

/*
Elements to use this system should have the following class name format:
vB_UserfieldEditor[profilefieldid||pageuserid||bbuserid||element_to_contain_button_id]

In MEMBERINFO, include YUI framework (dom-event and connection), and include this file
In memberinfo_customfields, include this in class="..." for the field value element:
	vB_UserfieldEditor[$profilefield[profilefieldid]||$userinfo[userid]||$bbuserinfo[userid]||userfield_title_$profilefield[profilefieldid]]
*/


// =============================================================================

/**
* Factory to build userfield editor objects
*/
function vB_UserfieldEditor_Factory()
{
	this.controls = new Array();
	this.open_fieldid = null;
	this.loading = false;
	this.init();
};

/**
* Initialises the system
*/
vB_UserfieldEditor_Factory.prototype.init = function()
{
	this.control_image = new Image();
	this.control_image.src = IMGDIR_MISC + "/blog/userfield_edit.gif";

	if (vBulletin.elements["vB_UserfieldEditor"])
	{
		for (var i = 0; i < vBulletin.elements["vB_UserfieldEditor"].length; i++)
		{
			var element = vBulletin.elements["vB_UserfieldEditor"][i];
			var args = element[1].split("||");

			// only allow editing of OWN profile fields
			if (args[1] != args[2])
			{
				continue;
			}

			var fieldid = args[0];
			var ctrlname = args[3];

			this.controls[fieldid] = new vB_UserfieldEditor(element[0], fieldid, ctrlname, this);
		}
		vBulletin.elements["vB_UserfieldEditor"] = null;
	}

	this.progress_image = new Image();
	this.progress_image.src = IMGDIR_MISC + "/11x11progress.gif";
};

/**
* Deactivates all controls
*/
vB_UserfieldEditor_Factory.prototype.close_all = function()
{
	if (this.open_fieldid)
	{
		// close identified active menu
		this.controls[this.open_fieldid].deactivate();
	}
	/*else
	{
		for (var i in this.controls)
		{
			if (typeof(this.controls[i]) == "object")
			{
				this.controls[i].deactivate();
			}
		}
	}*/
};

/**
* Records the name of the currently-active userfield
*/
vB_UserfieldEditor_Factory.prototype.set_open_fieldid = function(value)
{
	vBulletin.console("set_open_fieldid(%s)", value);
	this.open_fieldid = value;
};

// #############################################################################

/**
* Creates a single editable userfield value
*
* @param	element	HTML element to be controlled
* @param	string	Fieldid of the userfield to be edited
* @param	string	HTML element to which to attach the edit button
* @param	vB_UserfieldEditor_Factory	Controlling factory class
*/
function vB_UserfieldEditor(contentelement, fieldid, control_parent, factory)
{
	this.element = YAHOO.util.Dom.get(contentelement);
	this.control_parent = YAHOO.util.Dom.get(control_parent);
	this.fieldid = fieldid;
	this.factory = factory;

	this.element.style.cursor = "default";

	this.value = this.element.innerHTML;

	// create button
	if (this.control_parent)
	{
		this.control = this.control_parent.appendChild(document.createElement("a"));
		this.control.href = "#";
		this.control_image = this.control.appendChild(document.createElement("img"));
		this.control_image.src = this.factory.control_image.src;
		this.control_image.border = 0;
		YAHOO.util.Event.on(this.control, "click", this.activate, this, true);
	}

	YAHOO.util.Event.on(this.element, "dblclick", this.activate, this, true);
}

/**
* Activates the controls
*
* @param	event	Event object
*/
vB_UserfieldEditor.prototype.activate = function(e)
{
	YAHOO.util.Event.stopEvent(e);

	if (this.factory.open_fieldid == this.fieldid)
	{
		vBulletin.console("This field (%s) is already open", this.fieldid);
		return false;
	}
	else if (this.factory.loading)
	{
		vBulletin.console("Loading already in progress...");
		return false;
	}

	this.factory.close_all();

	if (this.control_parent)
	{
		this.control_image.src = this.factory.progress_image.src;
	}

	this.value = this.element.innerHTML;

	this.factory.loading = true;

	YAHOO.util.Connect.asyncRequest("POST", "blog_ajax.php", {
		success: this.show_controls,
		failure: this.request_timeout,
		timeout: 5000,
		scope: this
	}, "do=fetchuserfield&fieldid=" + PHP.urlencode(this.fieldid));

	return false;
}

/**
* Reads the editor template from AJAX request and shows the controls
*
* @param	AJAX	YUI AJAX object from activate()
*/
vB_UserfieldEditor.prototype.show_controls = function(ajax)
{
	this.factory.loading = false;

	var error = ajax.responseXML.getElementsByTagName("error");
	if (error[0])
	{
		alert("Error" + error);
		this.deactivate();
	}
	else
	{
		this.factory.set_open_fieldid(this.fieldid);

		if (this.control_parent)
		{
			this.control_image.src = this.factory.control_image.src;
		}

		this.element.innerHTML = ajax.responseXML.getElementsByTagName("template")[0].firstChild.nodeValue;

		this.form = this.element.getElementsByTagName("form")[0];

		YAHOO.util.Event.on(this.form, "submit", this.save, this, true);
		YAHOO.util.Event.on(this.form, "reset", this.deactivate, this, true);

		for (var i = 0; i < this.form.elements.length; i++)
		{
			if (this.form.elements[i].tagName == "INPUT" || this.form.elements[i].tagName == "SELECT" || this.form.elements[i].tagName == "TEXTAREA")
			{
				this.form.elements[i].focus();
				break;
			}
		}
	}
}

/**
* Initializes the save mechanism
*
* @param	event	Event object
*/
vB_UserfieldEditor.prototype.save = function(e)
{
	YAHOO.util.Event.stopEvent(e);

	if (this.control_parent)
	{
		this.control_image.src = this.factory.progress_image.src;
	}

	var hidden_form = new vB_Hidden_Form(null);
	hidden_form.add_variables_from_object(this.element);

	this.element.innerHTML = this.value;
	YAHOO.util.Dom.addClass(this.element, "shade");

	YAHOO.util.Connect.asyncRequest("POST", "blog_ajax.php", {
		success: this.post_save,
		failure: this.request_timeout,
		timeout: 5000,
		scope: this
	}, "do=saveuserfield&fieldid=" + this.fieldid + "&" + hidden_form.build_query_string());
}

/**
* Handles the result of the AJAX save
*
* @param	AJAX	YUI AJAX object from save()
*/
vB_UserfieldEditor.prototype.post_save = function(ajax)
{
	var error = ajax.responseXML.getElementsByTagName("error");
	if (error[0])
	{
		alert(error[0].firstChild.nodeValue);
	}
	else
	{
		this.value = ajax.responseXML.getElementsByTagName("value")[0].firstChild.nodeValue;
	}

	YAHOO.util.Dom.removeClass(this.element, "shade");
	this.deactivate();
}

/**
* Deactivates a control without saving
*/
vB_UserfieldEditor.prototype.deactivate = function()
{
	if (this.control_parent)
	{
		this.control_image.src = this.factory.control_image.src;
	}
	this.element.innerHTML = this.value;
	this.factory.set_open_fieldid(null);
}

/**
* Handles timeout errors
*/
vB_UserfieldEditor.prototype.request_timeout = function()
{
	alert("The server failed to respond in time. Please try again.");
	this.deactivate();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 09:06, Sat Sep 8th 2007
|| # CVS: $RCSfile$ - $Revision: 17990 $
|| ####################################################################
\*======================================================================*/