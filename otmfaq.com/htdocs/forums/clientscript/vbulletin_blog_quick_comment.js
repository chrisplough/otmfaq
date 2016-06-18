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

/*
* IMPORTANT: call like this:
*
* var quick_comment = new vB_QuickComment_Blog("qc_form", $vboptions[postminchars]);
*
* The variable name 'quick_comment' is important!
*/

function vB_QuickComment_Blog(formid, minchars, returnorder)
{
	this.repost       = false;
	this.errors_shown = false;
	this.posting      = false;
	this.submit_str   = null;
	this.lastelement  = YAHOO.util.Dom.get("lastcommentdiv");
	this.returnorder  = 'ASC';
	this.form = YAHOO.util.Dom.get(formid);
	if (typeof(this.form.allow_ajax_qc) != 'undefined' && this.form.allow_ajax_qc.value == 0)
	{
		this.allow_ajax_qc = false;
	}
	else	// Allow AJAX
	{
		this.allow_ajax_qc = true;
	}

	if (returnorder == 'DESC')
	{
		this.returnorder = 'DESC';
	}

	this.minchars = minchars;

	YAHOO.util.Event.on("qr_submit", "click", this.submit_comment, this, true);
	YAHOO.util.Event.on("qr_preview", "click", this.submit_comment, this, true);
	YAHOO.util.Event.on("qc_hide_errors", "click", this.hide_errors, this, true);
}

/**
* Works with form data to decide what to do
*
* @param	event	Javascript event object
*
* @return	boolean
*/
vB_QuickComment_Blog.prototype.check_data = function(e)
{
	if (typeof(this.form.preview) != 'undefined' && YAHOO.util.Event.getTarget(e) == this.form.preview)
	{
		minchars = 0;
	}
	else
	{
		minchars = this.minchars;
	}

	return vB_Editor[QR_EditorID].prepare_submit(0, minchars);
}

/**
* Checks the contents of the new comment and decides whether or not to allow it through
*
* @param	event	Javascript event object
*
* @return	boolean
*/
vB_QuickComment_Blog.prototype.submit_comment = function(e)
{
	if (this.repost == true)
	{
		return true;
	}
	else if (!AJAX_Compatible || !this.allow_ajax_qc)
	{
		if (!this.check_data(e))
		{
			YAHOO.util.Event.stopEvent(e);
			return false;
		}
		return true;
	}
	else if (this.check_data(e))
	{
		if (is_ie && userAgent.indexOf("msie 5.") != -1)
		{
			// IE 5 has problems with non-ASCII characters being returned by
			// AJAX. Don't universally disable it, but if we're going to be sending
			// non-ASCII, let's not use AJAX.
			if (PHP.urlencode(this.form.message.value).indexOf('%u') != -1)
			{
				return true;
			}
		}

		if (this.posting == true)
		{
			YAHOO.util.Event.stopEvent(e);
			return false;
		}
		else
		{
			this.posting = true;
			setTimeout("quick_comment.posting = false", 1000); // ATTENTION
		}

		if (typeof(this.form.preview) != 'undefined' && YAHOO.util.Event.getTarget(e) == this.form.preview)
		{
			return true;
		}
		else
		{
			this.submit_str = this.build_submit_string();

			YAHOO.util.Dom.setStyle("qc_posting_msg", "display", "");
			YAHOO.util.Dom.setStyle(document.body, "cursor", "wait");

			this.save(this.form.action, this.submit_str);

			YAHOO.util.Event.stopEvent(e);
			return false;
		}
	}
	else
	{
		YAHOO.util.Event.stopEvent(e);
		return false;
	}
}

/**
* Builds the submit string for the AJAX request
*
* @return	string	Submit query URI string
*/
vB_QuickComment_Blog.prototype.build_submit_string = function()
{
	this.submit_str = 'ajax=1';

	var hiddenform = new vB_Hidden_Form(null);
	hiddenform.add_variables_from_object(this.form);

	return this.submit_str += "&" + hiddenform.build_query_string();
}

/**
* Sends quick comment data to blog.php via AJAX
*
* @param	string	GET string for action (blog.php)
* @param	string	String representing form data ('x=1&y=2&z=3' etc.)
*/
vB_QuickComment_Blog.prototype.save = function()
{
	this.repost = false;

	YAHOO.util.Connect.asyncRequest("POST", this.form.action, {
		success: this.post_save,
		failure: this.ajax_fail,
		timeout: 5000,
		scope: this
	}, this.submit_str);
}

vB_QuickComment_Blog.prototype.ajax_fail = function()
{
	console.log("AJAX Timeout - Submitting form");
	this.repost = true;
	this.form.submit();
}

/**
* Handles quick comment data when AJAX says qc_ajax_post() is complete
*/
vB_QuickComment_Blog.prototype.post_save = function(ajax)
{
	YAHOO.util.Dom.setStyle(document.body, "cursor", "auto");
	YAHOO.util.Dom.setStyle("qc_posting_msg", "display", "none");
	this.posting = false;

	var comments = ajax.responseXML.getElementsByTagName("comment");
	if (comments.length > 0)
	{
		vB_Editor[QR_EditorID].write_editor_contents('');

		this.form.lastcomment.value = ajax.responseXML.getElementsByTagName("time")[0].firstChild.nodeValue;

		this.hide_errors();

		var total = 0;

		var noposts = YAHOO.util.Dom.get("noposts");
		if (noposts && comments.length > 0)
		{
			YAHOO.util.Dom.setStyle(noposts, "display", "none");
		}

		var inlinemod_delete = false;
		var inlinemod_approve = false;
		for (var i = 0; i < comments.length; i++)
		{
			var newcomment = document.createElement("div");
				newcomment.innerHTML = comments[i].firstChild.nodeValue;

			this.lastelement.parentNode.insertBefore(newcomment, this.lastelement);

			if (this.returnorder == 'DESC')
			{
				this.lastelement = newcomment;
			}

			Comment_Init(newcomment, comments[i].getAttribute("blogtextid"));
			total += parseInt(comments[i].getAttribute("visible"));
			if (comments[i].getAttribute("inlinemod_delete") == 1)
			{
				inlinemod_delete = true;
			}
			if (comments[i].getAttribute("inlinemod_approve") == 1)
			{
				inlinemod_approve = true;
			}
		}

		if (total > 0)
		{
			var countobj1 = YAHOO.util.Dom.get("commentcount1");
			if (countobj1)
			{
				countobj1.innerHTML = parseInt(countobj1.innerHTML) + total;
			}

			var countobj2 = YAHOO.util.Dom.get("commentcount2");
			if (countobj2)
			{
				countobj2.innerHTML = parseInt(countobj2.innerHTML) + total;
			}
		}

		// unfocus the qr_submit button to prevent a space from resubmitting
		var submit_btn = YAHOO.util.Dom.get("qr_submit")
		if (submit_btn)
		{
			submit_btn.blur();
		}

		//alert("Delete:" + inlinemod_delete + " Approve:" + inlinemod_approve);
		var inlinemod_comment_controls = YAHOO.util.Dom.get("inlinemod_comment_controls");
		var moderation_select_comments_optgroup = YAHOO.util.Dom.get("moderation_select_comments_optgroup");
		var moderation_select_comments = YAHOO.util.Dom.get("moderation_select_comments");

		if ((inlinemod_delete || inlinemod_approve) && inlinemod_comment_controls && moderation_select_comments_optgroup && moderation_select_comments)
		{
			var setindex = inlinemod_comment_controls.style.display;
			YAHOO.util.Dom.setStyle(inlinemod_comment_controls, "display", "");
			if (inlinemod_delete)
			{
				var inlinemod_comment_controls_delete = YAHOO.util.Dom.get("inlinemod_comment_controls_delete");
				var inlinemod_comment_controls_undelete = YAHOO.util.Dom.get("inlinemod_comment_controls_undelete");
				if (!inlinemod_comment_controls_delete)
				{
					var opt = moderation_select_comments_optgroup.appendChild(document.createElement("option"));
					opt.value = "deletecomment";
					opt.id = "inlinemod_comment_controls_delete";
					opt.appendChild(document.createTextNode(vbphrase["delete_comments"]));
					if (moderation_select_comments.options[0].value != 'deletecomment')
					{
						for (var i = moderation_select_comments.options.length - 2; i >= 0; i--)
						{
								moderation_select_comments.options[i + 1].value = moderation_select_comments.options[i].value;
								moderation_select_comments.options[i + 1].text = moderation_select_comments.options[i].text;
						}
						moderation_select_comments.options[0].value = 'deletecomment';
						moderation_select_comments.options[0].text = vbphrase["delete_comments"];
					}
				}
				if (!inlinemod_comment_controls_undelete)
				{
					var opt = moderation_select_comments_optgroup.appendChild(document.createElement("option"));
					opt.value = "undeletecomment";
					opt.id = "inlinemod_comment_controls_undelete";
					opt.appendChild(document.createTextNode(vbphrase["undelete_comments"]));
					if (moderation_select_comments.options[1].value != 'undeletecomment')
					{
						for (var i = moderation_select_comments.options.length - 2; i >= 1; i--)
						{
								moderation_select_comments.options[i + 1].value = moderation_select_comments.options[i].value;
								moderation_select_comments.options[i + 1].text = moderation_select_comments.options[i].text;
						}
						moderation_select_comments.options[1].value = 'undeletecomment';
						moderation_select_comments.options[1].text = vbphrase["undelete_comments"];
					}
				}
			}
			if (inlinemod_approve)
			{
				var inlinemod_comment_controls_approve = YAHOO.util.Dom.get("inlinemod_comment_controls_approve");
				var inlinemod_comment_controls_unapprove = YAHOO.util.Dom.get("inlinemod_comment_controls_unapprove");
				if (!inlinemod_comment_controls_approve)
				{
					var opt = moderation_select_comments_optgroup.appendChild(document.createElement("option"));
					opt.value = "approvecomment";
					opt.id = "inlinemod_comment_controls_approve";
					opt.appendChild(document.createTextNode(vbphrase["approve_comments"]));
				}
				if (!inlinemod_comment_controls_unapprove)
				{
					var opt = moderation_select_comments_optgroup.appendChild(document.createElement("option"));
					opt.value = "unapprovecomment";
					opt.id = "inlinemod_comment_controls_unapprove";
					opt.appendChild(document.createTextNode(vbphrase["unapprove_comments"]));
				}
			}

			if (setindex)
			{
				moderation_select_comments.selectedIndex = 0;
			}
		}
	}
	else // no comments found - handle the error
	{
		if (!is_saf)
		{
			this.show_errors(ajax);
			return false;
		}

		// this is the not so nice error handler, which is a fallback in case the previous one doesn't work
		this.repost = true;
		this.form.submit();
	}
}

/**
* Un-hides the quick comment errors element
*
* @param	object	YUI AJAX object
*
* @return	boolean	false
*/
vB_QuickComment_Blog.prototype.show_errors = function(ajax)
{
	this.errors_shown = true;

	var error_td = YAHOO.util.Dom.get("qc_error_list");
	while (error_td.hasChildNodes())
	{
		error_td.removeChild(error_td.firstChild);
	}

	// this is the nice error handler, of which Safari makes a mess
	var errors = ajax.responseXML.getElementsByTagName("error");

	var error_html = document.createElement("ol");
	for (var i = 0; i < errors.length; i++)
	{
		var current_error = document.createElement("li");
			current_error.className = "smallfont";
			//current_error.appendChild(document.createTextNode(errors[i].firstChild.nodeValue));
			// Our error phrases can contain html - createTextNode renders that HTML as text
			current_error.innerHTML = errors[i].firstChild.nodeValue;

		error_html.appendChild(current_error);

		console.warn(errors[i].firstChild.nodeValue);
	}

	error_td.appendChild(error_html);

	YAHOO.util.Dom.setStyle("qc_error_div", "display", "");

	vB_Editor[QR_EditorID].check_focus();
	return false;
}

/**
* Hides the quick comment comment element
*
* @return	boolean	false
*/
vB_QuickComment_Blog.prototype.hide_errors = function()
{
	console.log("Hiding QC Errors");
	if (this.errors_shown)
	{
		this.errors_shown = true;

		YAHOO.util.Dom.setStyle("qc_error_div", "display", "none");

		return false;
	}
}

// #############################################################################
// Initialize a Comment
/**
* This will need to be moved to a "global" file for the blog if any other actions are to load responses via AJAX
*
* This function runs all the necessary Javascript code on a Comment
* after it has been loaded via AJAX. Don't use this method before a
* complete page load or you'll have problems.
*
* @param	object	Object containing comments
*/
function Comment_Init(obj, blogtextid)
{
	if (typeof inlineMod_comment != "undefined")
	{
		im_init(obj, inlineMod_comment);
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 03:15, Tue Dec 9th 2008
|| # CVS: $RCSfile$ - $Revision: 25812 $
|| ####################################################################
\*======================================================================*/