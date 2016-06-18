<?php
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

defined('IN_MOBIQUO') or exit;
/**
 * Factory class to create blocks for the Profile Display
 *
 * @package    vBulletin
 */

class vB_ProfileBlockFactory
{
    /**
     * Registry object
     *
     * @var    vB_Registry
     */
    var $registry;

    /**
     * The UserProfile Object
     *
     * @var    vB_UserProfile
     */
    var $profile;

    /**
     * Cache of the Profile Blocks already loaded
     *
     * @var    array
     */
    var $cache = array();

    /**
     * Cache of each block's privacy requirements
     *
     * @var    array integer
     */
    var $privacy_requirements;


    /**
     * Constructor
     *
     * @param    vB_Registry
     * @param    vB_UserProfile
     */
    function vB_ProfileBlockFactory(&$registry, &$profile)
    {
        $this->registry =& $registry;
        $this->profile =& $profile;
    }

    /**
     * Fetches a Profile Block object
     *
     * @param    string    The name of the class
     */
    function &fetch($class)
    {
        if (!isset($this->cache["$class"]) OR !is_object($this->cache["$class"]))
        {
            $classname = "vB_ProfileBlock_$class";
            $this->cache["$class"] = new $classname($this->registry, $this->profile, $this);
        }

        return $this->cache["$class"];
    }
}
/**
 * Abstract Class for Profile Blocks
 *
 * @package vBulletin
 */
class vB_ProfileBlock
{
    /**
     * Registry object
     *
     * @var    vB_Registry
     */
    var $registry;

    /**
     * User Profile Object
     *
     * @var    vB_UserProfile
     */
    var $profile;

    /**
     * Factory Object
     *
     * @var    vB_ProfileBlockFactory
     */
    var $factory;

    /**
     * Default Options for the block
     *
     * @var    array
     */
    var $option_defaults = array();

    /**
     * The name of the template to be used for the block
     *
     * @var string
     */
    var $template_name = '';

    /**
     * Variables to automatically prepare
     *
     * @var array
     */
    var $auto_prepare = array();

    /**
     * Data that is only used within the block itself
     *
     * @var array
     */
    var $block_data = array();

    /**
     * Whether to skip privacy checking.
     *
     * @var boolean
     */
    var $skip_privacy_check = false;

    /**
     * The default privacy requirement to view the block if one was not set by the user
     *
     * @var integer
     */
    var $default_privacy_requirement = 0;

    /**
     * Whether to wrap output in standard block template.
     *
     * @var boolean
     */
    var $nowrap;
    var $mobiquo_array;

    /**
     * Constructor - Prepares the block, and automatically prepares needed data
     *
     * @param    vB_Registry
     * @param    vB_UserProfile
     * @param    vB_ProfileBlockFactory
     */
    function vB_ProfileBlock(&$registry, &$profile, &$factory)
    {
        $this->registry =& $registry;
        $this->profile =& $profile;
        $this->factory =& $factory;

        foreach ($this->auto_prepare AS $prepare)
        {
            $profile->prepare($prepare);
        }

        $this->fetch_default_options();
    }

    /**
     * Whether to return an empty wrapper if there is no content in the blocks
     *
     * @return bool
     */
    function confirm_empty_wrap()
    {
        return true;
    }

    /**
     * Whether or not the block is enabled
     *
     * @return bool
     */
    function block_is_enabled($id)
    {
        return true;
    }

    /**
     * Fetch the block
     *
     * @param    string    The title of the Block
     * @param    string    The id of the Block
     * @param    array    Options specific to the block
     * @param    array    Userinfo of the visiting user
     *
     * @return    string    The Block's output to be shown on the profile page
     */
    function fetch($title, $id = '', $options = array(), $visitor)
    {
        if ($this->block_is_enabled($id))
        {
            if (!$this->visitor_can_view($id, $visitor))
            {
                return '';
            }

            $html = $this->fetch_unwrapped($title, $id, $options);

            if (trim($html) === '' AND !$this->confirm_empty_wrap())
            {
                return '';
            }
            else
            {
                if ($this->nowrap)
                {
                    return $this->mobiquo_array;
                }
                else
                {
                    return $this->mobiquo_array;
                }
            }
        }
        else
        {
            return '';
        }
    }

    /**
     * Prepare any data needed for the output
     *
     * @param    string    The id of the block
     * @param    array    Options specific to the block
     */
    function prepare_output($id = '', $options = array())
    {
    }

    /**
     * Should we actually display anything?
     *
     * @return    bool
     */
    function confirm_display()
    {
        return true;
    }

    /**
     * Sets/Fetches the default options for the block
     *
     */
    function fetch_default_options()
    {
    }

    /**
     * Fetches the unwrapped (no box around it) version of the block
     *
     * @param    string    The title of the block
     * @param    string    The id of the block
     * @param    array    Options specific to the block
     *
     * @return    string
     */
    function fetch_unwrapped($title, $id = '', $options = array())
    {
        global $show, $vbphrase, $vbcollapse;

        $this->prepare_output($id, $options);

        if (!$this->confirm_display())
        {
            return '';
        }

        $prepared = $this->profile->prepared;
        $userinfo = $this->profile->userinfo;
        $block_data = $this->block_data;

        ($hook = vBulletinHook::fetch_hook('member_profileblock_fetch_unwrapped')) ? eval($hook) : false;

        $templater = vB_Template::create($this->template_name);
        $templater->register('block_data', $block_data);
        $templater->register('id', $id);
        $templater->register('prepared', $prepared);
        $templater->register('template_hook', $template_hook);
        $templater->register('userinfo', $userinfo);
        $templater->register('title', $title);
        $templater->register('nowrap', $this->nowrap);
        return $templater->render();
    }

    /**
     * Wraps the given HTML in it's containing block
     *
     * @param    string    The title of the block
     * @param    string    The id of the block
     * @param    string    The HTML to be wrapped
     *
     * @return    string
     */
    function wrap($title, $id = '', $html = '')
    {
        global $show, $vbphrase, $vbcollapse, $selected_tab;


        $templater = vB_Template::create('memberinfo_block');
        $templater->register('html', $html);
        $templater->register('id', $id);
        $templater->register('title', $title);
        $templater->register('show', $show);
        $templater->register('selected_tab', $selected_tab);
        return $templater->render();
    }

    /**
     * Determines whether the visitor is allowed to view a block based on their
     * relationship to the subject user, and what the subject user has configured.
     *
     * @param    integer    Id of the block
     * @param    array    Userinfo of the visitor
     */
    function visitor_can_view($id, $visitor)
    {
        // Some blocks should always be shown
        if ($this->skip_privacy_check)
        {
            return true;
        }

        if (!$this->registry->options['profileprivacy'] OR (!($this->profile->prepared['userperms']['usercsspermissions'] & $this->registry->bf_ugp_usercsspermissions['caneditprivacy'])))
        {
            $requirement = $this->default_privacy_requirement;
        }
        else
        {
            if (!isset($this->factory->privacy_requirements))
            {
                $this->fetch_privacy_requirements();
            }

            $requirement = (isset($this->factory->privacy_requirements[$id]) ? $this->factory->privacy_requirements[$id] : $this->default_privacy_requirement);
        }

        return (fetch_user_relationship($this->profile->userinfo['userid'], $this->registry->userinfo['userid']) >= $requirement);
    }

    /**
     * Fetches the privacy requirements for the current user.
     */
    function fetch_privacy_requirements()
    {
        $this->factory->privacy_requirements = array();

        $requirements = $this->registry->db->query_read_slave("
            SELECT blockid, requirement
            FROM " . TABLE_PREFIX . "profileblockprivacy
            WHERE userid = " . intval($this->profile->userinfo['userid']) . "
        ");

        while ($requirement = $this->registry->db->fetch_array($requirements))
        {
            $this->factory->privacy_requirements[$requirement['blockid']] = $requirement['requirement'];
        }
        $this->registry->db->free_result($requirements);
    }
}

/**
 * Profile Block for Profile Fields
 *
 * @package vBulletin
 */
class vB_ProfileBlock_ProfileFields extends vB_ProfileBlock
{
    /**
     * The name of the template to be used for the block
     *
     * @var string
     */
    var $template_name = 'memberinfo_block_profilefield';

    /**
     * The categories to show in this block
     *
     * @var array
     */
    var $categories = array(0 => array());

    /**
     * The Locations of the fields within the block
     *
     * @var array
     */
    var $locations = array();

    /**
     * Whether the data has been built already
     *
     * @var bool
     */
    var $data_built = false;

    /**
     * Sets/Fetches the default options for the block
     *
     */
    function fetch_default_options()
    {
        $this->option_defaults = array(
            'category' => 'all'
            );
    }

    /**
     * Whether to return an empty wrapper if there is no content in the blocks
     *
     * @return bool
     */
    function confirm_empty_wrap()
    {
        return false;
    }

    /**
     * Should we actually display anything?
     *
     * @return    bool
     */
    function confirm_display()
    {
        return ($this->block_data['fields'] != '');
    }

    /**
     * Builds the custom Profile Field Data
     *
     * @param    boolean    Should we show hidden fields if we're allowed to view them?
     */
    function build_field_data($showhidden)
    {
        if ($this->data_built)
        {
            return;
        }

        $this->categories = array(0 => array());
        $this->locations = array();

        if (!isset($this->factory->privacy_requirements))
        {
            $this->fetch_privacy_requirements();
        }

        $profilefields_result = $this->registry->db->query_read_slave("
            SELECT pf.profilefieldcategoryid, pfc.location, pf.*
            FROM " . TABLE_PREFIX . "profilefield AS pf
            LEFT JOIN " . TABLE_PREFIX . "profilefieldcategory AS pfc ON(pfc.profilefieldcategoryid = pf.profilefieldcategoryid)
            WHERE pf.form = 0 " . iif($showhidden OR !($this->registry->userinfo['permissions']['genericpermissions'] & $this->registry->bf_ugp_genericpermissions['canseehiddencustomfields']), "
                    AND pf.hidden = 0") . "
            ORDER BY pfc.displayorder, pf.displayorder
        ");
        while ($profilefield = $this->registry->db->fetch_array($profilefields_result))
        {
            $requirement = (isset($this->factory->privacy_requirements["profile_cat$profilefield[profilefieldcategoryid]"])
            ? $this->factory->privacy_requirements["profile_cat$profilefield[profilefieldcategoryid]"]
            : $this->default_privacy_requirement
            );

            if (fetch_user_relationship($this->profile->userinfo['userid'], $this->registry->userinfo['userid']) >= $requirement)
            {
                $this->categories["$profilefield[profilefieldcategoryid]"][] = $profilefield;
                $this->locations["$profilefield[profilefieldcategoryid]"] = $profilefield['location'];
            }
        }

        $this->data_built = true;
    }

    /**
     * Prepare any data needed for the output
     *
     * @param    string    The id of the block
     * @param    array    Options specific to the block
     */
    function prepare_output($id = '', $options = array())
    {
        global $show, $vbphrase, $stylevar;

        if (is_array($options))
        {
            $options = array_merge($this->option_defaults, $options);
        }
        else
        {
            $options = $this->option_defaults;
        }

        $options['simple'] = ($this->profile->prepared['myprofile'] ? $options['simple'] : false);

        $this->build_field_data($options['simple']);

        if ($options['category'] == 'all')
        {
            $categories = $this->categories;
            $show['profile_category_title'] = true;
            $enable_ajax_edit = true;
        }
        else
        {
            $categories = isset($this->categories["$options[category]"]) ?
            array($options['category'] => $this->categories["$options[category]"]) :
            array();
            $show['profile_category_title'] = false;
            $enable_ajax_edit = false;
        }

        $profilefields = '';

        foreach ($categories AS $profilefieldcategoryid => $profilefield_items)
        {
            $category = array(
                'title' => (
            $profilefieldcategoryid == 0 ?
            construct_phrase($vbphrase['about_x'], $this->profile->userinfo['username']) :
            $vbphrase["category{$profilefieldcategoryid}_title"]
            ),
                'description' => $vbphrase["category{$profilefieldcategoryid}_desc"],
                'fields' => ''
                );

                foreach ($profilefield_items AS $profilefield)
                {
                    $field_value = $this->profile->userinfo["field$profilefield[profilefieldid]"];


                    fetch_profilefield_display($profilefield, $field_value);

                    $this->mobiquo_array[] = array("name" => $profilefield['title'], "value" =>  $profilefield['value']);

                    // can edit if viewing own profile and field is actually editable
                    $show['profilefield_edit'] = (!$options['simple'] AND $enable_ajax_edit
                    AND $this->registry->userinfo['userid'] == $this->profile->userinfo['userid']
                    AND ($profilefield['editable'] == 1 OR ($profilefield['editable'] == 2 AND empty($field_value)))
                    AND ($this->registry->userinfo['permissions']['genericpermissions'] & $this->registry->bf_ugp_genericpermissions['canmodifyprofile'])
                    );
                    if ($show['profilefield_edit'] AND $profilefield['value'] == '')
                    {
                        // this field is to be editable but there's no value -- we need to show the field
                        $profilefield['value'] = $vbphrase['n_a'];
                    }

                    ($hook = vBulletinHook::fetch_hook('member_profileblock_profilefieldbit')) ? eval($hook) : false;

                    if ($profilefield['value'] != '')
                    {
                        $show['extrainfo'] = true;

                        $templater = vB_Template::create('memberinfo_profilefield');
                        $templater->register('profilefield', $profilefield);
                        $category['fields'] .= $templater->render();
                    }
                }

                ($hook = vBulletinHook::fetch_hook('member_profileblock_profilefield_category')) ? eval($hook) : false;

                if ($category['fields'])
                {
                    $templater = vB_Template::create('memberinfo_profilefield_category');
                    $templater->register('category', $category);
                    $profilefields .= $templater->render();
                }
        }

        $this->block_data['fields'] = $profilefields;
    }
}

/**
 * Profile Block for "About Me"
 *
 * @package vBulletin
 */
class vB_ProfileBlock_AboutMe extends vB_ProfileBlock
{
    /**
     * The name of the template to be used for the block
     *
     * @var string
     */
    var $template_name = 'memberinfo_block_aboutme';

    /**
     * Variables to automatically prepare
     *
     * @var array
     */
    var $auto_prepare = array(
        'signature',
        'profileurl'
        );

        var $nowrap = true;

        /**
         * Whether to return an empty wrapper if there is no content in the blocks
         *
         * @return bool
         */
        function confirm_empty_wrap()
        {
            return false;
        }

        /**
         * Should we actually display anything?
         *
         * @return    bool
         */
        function confirm_display()
        {
            return true;
        }

        /**
         * Prepare any data needed for the output
         *
         * @param    string    The id of the block
         * @param    array    Options specific to the block
         */
        function prepare_output($id = '', $options = array())
        {
            global $show;

            $show['simple_link'] = (!$options['simple'] AND $this->registry->userinfo['userid'] == $this->profile->userinfo['userid']);
            $show['edit_link'] = ($options['simple'] AND $this->registry->userinfo['userid'] == $this->profile->userinfo['userid']
            AND ($this->registry->userinfo['permissions']['genericpermissions'] & $this->registry->bf_ugp_genericpermissions['canmodifyprofile']));
            $blockobj =& $this->factory->fetch('ProfileFields');
            $blockobj->prepare_output($id, $options);
            $this->mobiquo_array= $blockobj->mobiquo_array;        $this->block_data['fields'] = $blockobj->block_data['fields'];
            $this->block_data['pageinfo_aboutme_view'] = array('tab' => 'aboutme', 'simple' => 1);
            $this->block_data['pageinfo_aboutme_edit'] = array('tab' => 'aboutme');
            $this->block_data['pageinfo_vcard'] = array('do' => 'vcard');
        }
}
