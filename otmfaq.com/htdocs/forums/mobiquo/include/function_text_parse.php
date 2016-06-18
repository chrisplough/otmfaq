<?php
defined('IN_MOBIQUO') or exit;
/*======================================================================*\
 || #################################################################### ||
 || # Copyright &copy;2009 Quoord Systems Ltd. All Rights Reserved.    # ||
 || # This file may not be redistributed in whole or significant part. # ||
 || # This file is part of the Tapatalk package and should not be used # ||
 || # and distributed for any other purpose that is not approved by    # ||
 || # Quoord Systems Ltd.                                              # ||
 || # http://www.tapatalk.com | http://www.tapatalk.com/license.html   # ||
 || #################################################################### ||
 \*======================================================================*/
function mobiquo_handle_bbcode_attach($bbcode, $do_imgcode, $post)
{
    global $vbphrase, $vbulletin;
    $has_img_code = false;
    if (stripos($bbcode, '[/attach]') !== false)
    {
        $has_img_code  = true;
    }

    if ($has_img_code AND preg_match_all('#\[attach(?:=(right|left|config))?\](\d+)\[/attach\]#i', $bbcode, $matches))
    {
        // This forumid check needs to be moved out to an extended thread class...
        if ($post->forumid)
        {
            $forumperms = fetch_permissions($post->forumid);
            $cangetattachment = ($forumperms & $vbulletin->bf_ugp_forumpermissions['cangetattachment']);
        }
        else
        {
            $cangetattachment = true;
        }

        foreach($matches[2] AS $key => $attachmentid)
        {
            $align = $matches[1]["$key"];
            $search[] = '#\[attach' . (!empty($align) ? '=' . $align : '') . '\](' . $attachmentid . ')\[/attach\]#i';

            // attachment specified by [attach] tag belongs to this post
            if (!empty($post[attachments]["$attachmentid"]))
            {
                $attachment =& $post[attachments]["$attachmentid"];
                if (!empty($attachment['settings']) AND strtolower($align) == 'config')
                {
                    $settings = unserialize($attachment['settings']);
                }
                else
                {
                    $settings = '';
                }

                if ($attachment['state'] != 'visible' AND $attachment['userid'] != $vbulletin->userinfo['userid'])
                {    // Don't show inline unless the poster is viewing the post (post preview)
                    continue;
                }

                if ($attachment['thumbnail_filesize'] == $attachment['filesize'] AND ($vbulletin->options['viewattachedimages'] OR $vbulletin->options['attachthumbs']))
                {
                    $attachment['hasthumbnail'] = false;
                    $forceimage = true;
                }

                $attachment['filename'] = fetch_censored_text(htmlspecialchars_uni($attachment['filename']));
                $attachment['extension'] = strtolower(file_extension($attachment['filename']));
                $attachment['filesize'] = vb_number_format($attachment['filesize'], 1, true);

                $lightbox_extensions = array('gif', 'jpg', 'jpeg', 'jpe', 'png', 'bmp');

                switch($attachment['extension'])
                {
                    case 'gif':
                    case 'jpg':
                    case 'jpeg':
                    case 'jpe':
                    case 'png':
                    case 'bmp':
                    case 'tiff':
                    case 'tif':
                    case 'psd':
                    case 'pdf':
                        $imgclass = array();
                        $alt_text = $title_text = $caption_tag = $styles = '';
                        if ($settings)
                        {
                            if ($settings['alignment'])
                            {
                                switch ($settings['alignment'])
                                {
                                    case 'left':
                                        $imgclass[] = 'align_left';
                                        break;
                                    case 'center':
                                        $imgclass[] = 'align_center';
                                        break;
                                    case 'right':
                                        $imgclass[] = 'align_right';
                                        break;
                                }
                            }
                            if ($settings['size'])
                            {
                                if (isset($settings['size']))
                                {
                                    switch ($settings['size'])
                                    {
                                        case 'thumbnail':
                                            $imgclass[] = 'size_thumbnail';
                                            break;
                                        case 'medium':
                                            $imgclass[] = 'size_medium';
                                            break;
                                        case 'large':
                                            $imgclass[] = 'size_large';
                                            break;
                                        case 'fullsize':
                                            $imgclass[] = 'size_fullsize';
                                            break;
                                    }
                                }
                            }
                            if ($settings['caption'])
                            {
                                $caption_tag = "<p class=\"caption $size_class\">$settings[caption]</p>";
                            }
                            $alt_text = $settings['title'];
                            $description_text = $settings['description'];
                            $styles = $settings['styles'];
                        }

                        if (($settings OR ($vbulletin->options['attachthumbs'] AND $attachment['hasthumbnail'])) AND $vbulletin->userinfo['showimages'])
                        {
                            $lightbox = ($cangetattachment AND in_array($attachment['extension'], $lightbox_extensions));
                            $hrefbits = array(
                                    'href'   => "{$vbulletin->options['bburl']}/attachment.php?{$vbulletin->session->vars['sessionurl']}attachmentid=\\1&amp;d=$attachment[dateline]",
                                    'id'     => 'attachment\\1',
                            );
                            if ($lightbox)
                            {
                                $hrefbits["rel"] = 'Lightbox_';
                            }
                            else
                            {
                                $hrefbits["rel"] = "nofollow";
                            }
                            if ($addnewwindow)
                            {
                                $hrefbits['target'] = '_blank';
                            }
                            $atag = '';
                            foreach ($hrefbits AS $tag => $value)
                            {
                                $atag .= "$tag=\"$value\"";
                            }

                            $imgbits = array(
                                    'src'    => "{$vbulletin->options['bburl']}/attachment.php?{$vbulletin->session->vars['sessionurl']}attachmentid=\\1&amp;d=$attachment[thumbnail_dateline]",
                                    'border' => '0',
                                    'alt'    => $alt_text ? $alt_text : construct_phrase($vbphrase['image_larger_version_x_y_z'], $attachment['filename'], $attachment['counter'], $attachment['filesize'], $attachment['attachmentid'])
                            );



                            if (!empty($imgclass))
                            {
                                $imgbits['class'] = implode(' ', $imgclass);
                            }
                            else
                            {
                                $imgbits['class'] = 'thumbnail';
                            }
                            if ($description_text)
                            {
                                $imgbits['title'] = $description_text;
                            }
                            if ($styles)
                            {
                                $imgbits['style'] = $styles;
                            }
                            else if (!$settings AND $align AND $align != 'config')
                            {
                                $imgbits['style'] = "float:$align";
                            }
                            $imgtag = '';
                            foreach ($imgbits AS $tag => $value)
                            {
                                $imgtag .= "$tag=\"$value\"";
                            }
                            $replace[] = "[IMG]{$vbulletin->options['bburl']}/attachment.php?{$vbulletin->session->vars['sessionurl']}attachmentid=\\1&amp;d=$attachment[dateline][/IMG]";

                            //$replace[] = "<a $atag><img $imgtag /></a>";
                        }
                        else if ($vbulletin->userinfo['showimages'] AND ($forceimage OR $vbulletin->options['viewattachedimages']) AND !in_array($attachment['extension'], array('tiff', 'tif', 'psd', 'pdf')))
                        {    // Display the attachment with no link to bigger image

                            $replace[] = "[IMG]{$vbulletin->options['bburl']}/attachment.php?{$vbulletin->session->vars['sessionurl']}attachmentid=\\1&amp;d=$attachment[dateline][/IMG]";

                        }
                        else
                        {    // Display a link

                            $replace[] = "[IMG]{$vbulletin->options['bburl']}/attachment.php?{$vbulletin->session->vars['sessionurl']}attachmentid=\\1&amp;d=$attachment[dateline][/IMG]";

                        }
                        // remove attachment from array
                        if(is_object($post)){
                            unset($post->attachments["$attachmentid"]);
                        } else {
                            unset($post[attachments]["$attachmentid"]);

                        }
                        break;
                    default:
                        $replace[] = "[IMG]{$vbulletin->options['bburl']}/attachment.php?{$vbulletin->session->vars['sessionurl']}attachmentid=\\1&amp;d=$attachment[dateline][/IMG]";
                            
                }
            }
            else
            {    // Belongs to another post so we know nothing about it ... or we are not displying images so always show a link
                $addtarget = ($attachment['newwindow']) ? 'target="_blank"' : '';
                $replace[] = "[IMG]{$vbulletin->options['bburl']}/attachment.php?{$vbulletin->session->vars['sessionurl']}attachmentid=\\1&amp;d=$attachment[dateline][/IMG]";
                    
            }


        }

        $bbcode = preg_replace($search, $replace, $bbcode);
    }
    return $bbcode;
}
?>