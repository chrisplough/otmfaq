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

// #############################################################################
// vB_QuickComment_Blog
// #############################################################################

/**
* Quick Comment Class for Blog with vBulletin 3.7+ - extension of vB_QuickComment
* Handles specifics of what to do with data returned from Ajax
*
* @param	string	Form name that contains the controls
* @param	string	Minimum allowed characters
* @param	string	Are the returning posts ordered in asc or desc order?
*/
function vB_QuickComment_Blog(formid, minchars, returnorder)
{
	vB_QuickComment_Blog.baseConstructor.call(this, formid, minchars, returnorder);
	this.id = this;
}

vBulletin.extend(vB_QuickComment_Blog, vB_QuickComment);

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

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 03:15, Tue Dec 9th 2008
|| # CVS: $RCSfile$ - $Revision: 17991 $
|| ####################################################################
\*======================================================================*/