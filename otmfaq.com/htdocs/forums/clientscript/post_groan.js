/*=====================================*\
|| ################################### ||
|| # Post Groan Hack version 3.1     # ||
|| ################################### ||
\*=====================================*/

function post_groan_give(postid, integrate)
{
	fetch_object('post_groan_button_' + postid).style.display = 'none';

	if (integrate == true)
	{
		fetch_object('post_thanks_button_' + postid).style.display = 'none';
	}
	
	do_groan_add = new vB_AJAX_Handler(true);
	do_groan_add.postid = postid;
	do_groan_add.onreadystatechange(groan_add_Done);
	do_groan_add.send('post_groan.php?do=post_groan_add&using_ajax=1&p=' + postid);
}
function groan_add_Done()
{
	if (do_groan_add.handler.readyState == 4 && do_groan_add.handler.status == 200)
	{
		fetch_object('post_groan_box_' + do_groan_add.postid).innerHTML = do_groan_add.handler.responseText;
	}
}
function post_groan_remove_all(postid, integrate)
{
	do_groan_remove_all = new vB_AJAX_Handler(true)
	do_groan_remove_all.postid = postid
	do_groan_remove_all.onreadystatechange(groan_remove_all_Done)
	do_groan_remove_all.send('post_groan.php?do=post_groan_remove_all&using_ajax=1&p=' + postid)

	fetch_object('post_groan_button_' + postid).style.display = ''

	if (integrate == true)
	{
		fetch_object('post_thanks_button_' + postid).style.display = '';
	}
}
function groan_remove_all_Done()
{
	if (do_groan_remove_all.handler.readyState == 4 && do_groan_remove_all.handler.status == 200)
	{
		fetch_object('post_groan_box_' + do_groan_remove_all.postid).innerHTML = do_groan_remove_all.handler.responseText
	}
}
function post_groan_remove_user(postid, integrate)
{
	do_groan_remove_user = new vB_AJAX_Handler(true)
	do_groan_remove_user.postid = postid
	do_groan_remove_user.onreadystatechange(groan_remove_user_Done)
	do_groan_remove_user.send('post_groan.php?do=post_groan_remove_user&using_ajax=1&p=' + postid)

	fetch_object('post_groan_button_' + postid).style.display = ''

	if (integrate == true)
	{
		fetch_object('post_thanks_button_' + postid).style.display = '';
	}	
}
function groan_remove_user_Done()
{
	if (do_groan_remove_user.handler.readyState == 4 && do_groan_remove_user.handler.status == 200)
	{
		fetch_object('post_groan_box_' + do_groan_remove_user.postid).innerHTML = do_groan_remove_user.handler.responseText
	}
}