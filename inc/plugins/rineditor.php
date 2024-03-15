<?php

/*
Page Manager Plugin for MyBB

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

if (!defined('IN_MYBB'))
{
    die('This file cannot be accessed directly.');
}

if (defined('THIS_SCRIPT') && THIS_SCRIPT == 'misc.php')
{
    global $mybb, $cache;
    $pagecache = $cache->read('pages');

    if (!isset($mybb->input['page'])) {
        $mybb->input['page'] = NULL;
    }

    if ($mybb->input['page'] && isset($pagecache[$mybb->input['page']]) && $pagecache[$mybb->input['page']]['online'] != 1)
    {
        define('NO_ONLINE', 1);
    }
}

if (defined('IN_ADMINCP'))
{
    $plugins->add_hook("admin_config_plugins_deactivate_commit", 'pagemanager_delete_plugin');
    $plugins->add_hook('admin_config_action_handler', 'pagemanager_admin_action');
    $plugins->add_hook('admin_config_menu', 'pagemanager_admin_menu');
    $plugins->add_hook('admin_config_permissions', 'pagemanager_admin_permissions');
    $plugins->add_hook('admin_load', 'pagemanager_admin');
    $plugins->add_hook("admin_tools_cache_start", "pagemanager_tools_cache_rebuild");
    $plugins->add_hook("admin_tools_cache_rebuild", "pagemanager_tools_cache_rebuild");
}
else
{
    $plugins->add_hook('misc_start', 'pagemanager');
    $plugins->add_hook('build_friendly_wol_location_end', 'pagemanager_online');
}

function pagemanager_info()
{
    global $mybb, $db, $lang, $plugins_cache;
    $lang->load("config_pagemanager");

    $editedby = '*Edited for MyBB 1.8 &amp; maintained by: <a href="https://community.mybb.com/user-91011.html" target="_blank">SvePu</a>';
    $sources = '*Sources: <a href="https://github.com/SvePu/MyBB-PageManager" target="_blank">GitHub</a>';


    $info = array(
        'name'      =>  $db->escape_string($lang->pagemanager_info_name),
        'description'   =>  $db->escape_string($lang->pagemanager_info_description),
        'website'   =>  'https://community.mybb.com/thread-208230.html',
        'author'    =>  'Sebastian "querschlaeger" Wunderlich',
        'authorsite'    =>  '',
        'version'   =>  '2.1.5',
        'codename'  =>  'mybbpagemanager',
        'compatibility' =>  '18*'
    );

    $info_paypal = "";
    if (is_array($plugins_cache) && is_array($plugins_cache['active']) && $plugins_cache['active']['pagemanager'])
    {
        $info_paypal = '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="float: right;" target="_blank" />
    <input type="hidden" name="cmd" value="_s-xclick" />
    <input type="hidden" name="hosted_button_id" value="VGQ4ZDT8M7WS2" />
    <input type="image" src="https://www.paypalobjects.com/webstatic/en_US/btn/btn_donate_pp_142x27.png" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!" />
    <img alt="" border="0" src="https://www.paypalobjects.com/de_DE/i/scr/pixel.gif" width="1" height="1" />
    </form>';
    }

    $info['description'] = $info_paypal . $info['description'] . '<br />' . $editedby . '<br />' . $sources;

    $installed_func = "pagemanager_is_installed";

    if (function_exists($installed_func) && $installed_func() != true)
    {
        $info['description'] = "<span class=\"float_right\"><a href=\"index.php?module=config-plugins&amp;action=deactivate&amp;plugin=pagemanager&amp;delete=1&amp;my_post_key={$mybb->post_code}\"><img src=\"./styles/default/images/icons/delete.png\" title=\"" . $db->escape_string($lang->delete_pagemanager_link) . "\" alt=\"settings_icon\" width=\"16\" height=\"16\" /></a></span>" . $info['description'];
    }

    return $info;
}

function pagemanager_activate()
{
    pagemanager_plugin_update();
    change_admin_permission('config', 'pagemanager');
    pagemanager_cache();
}

function pagemanager_deactivate()
{
    change_admin_permission('config', 'pagemanager', -1);
    pagemanager_cache(true, false);
}

function pagemanager_install()
{
    global $db;

    /** Install DB Table */
    $collation = $db->build_create_table_collation();

    if (!$db->table_exists('pages'))
    {
        switch ($db->type)
        {
            case "pgsql":
                $db->write_query("CREATE TABLE " . TABLE_PREFIX . "pages (
                    pid serial,
                    name varchar(120) NOT NULL default '',
                    url varchar(30) NOT NULL default '',
                    pagegroups varchar(200) NOT NULL default '',
                    framework smallint NOT NULL default '0',
                    template text NOT NULL default '',
                    deviceselect varchar(10) NOT NULL default '',
                    online smallint NOT NULL default '1',
                    enabled smallint NOT NULL default '1',
                    dateline int NOT NULL default '0',
                    PRIMARY KEY (pid)
                );");
                break;
            case "sqlite":
                $db->write_query("CREATE TABLE " . TABLE_PREFIX . "pages (
                    pid INTEGER PRIMARY KEY,
                    name varchar(120) NOT NULL default '',
                    url varchar(30) NOT NULL default '',
                    pagegroups varchar(200) NOT NULL default '',
                    framework tinyint(1) NOT NULL default '0',
                    template text NOT NULL,
                    deviceselect varchar(10) NOT NULL default '',
                    online tinyint(1) NOT NULL default '1',
                    enabled tinyint(1) NOT NULL default '1',
                    dateline int(10) NOT NULL default '0'
                );");
                break;
            default:
                $db->write_query("CREATE TABLE " . TABLE_PREFIX . "pages (
                    pid int(10) unsigned NOT NULL auto_increment,
                    name varchar(120) NOT NULL default '',
                    url varchar(30) NOT NULL default '',
                    pagegroups varchar(200) NOT NULL default '',
                    framework tinyint(1) NOT NULL default '0',
                    template text NOT NULL,
                    deviceselect varchar(10) NOT NULL default '',
                    online tinyint(1) NOT NULL default '1',
                    enabled tinyint(1) NOT NULL default '1',
                    dateline int unsigned NOT NULL default '0',
                    UNIQUE KEY url (url),
                    PRIMARY KEY (pid)
                ) ENGINE=MyISAM{$collation};");
                break;
        }
    }
}

function pagemanager_is_installed()
{
    global $mybb, $db;
    $pmcache = $db->simple_select('datacache', '*', 'title="pages"');
    if ($db->num_rows($pmcache) > 0 && $db->table_exists('pages'))
    {
        $fields = $db->show_fields_from('pages');
        $list = array();
        $check = array(
            'pid',
            'name',
            'url',
            'pagegroups',
            'framework',
            'template',
            'deviceselect',
            'online',
            'enabled',
            'dateline'
        );
        foreach ($fields as $key => $val)
        {
            array_push($list, $val['Field']);
        }
        $diff = array_diff($check, $list);
        if (empty($diff))
        {
            return true;
        }
    }
    return false;
}

function pagemanager_uninstall()
{
    global $mybb, $db;
    if ($mybb->request_method != 'post')
    {
        global $page, $lang;
        $lang->load('config_pagemanager');
        $page->output_confirm_action('index.php?module=config-plugins&action=deactivate&uninstall=1&plugin=pagemanager', $lang->pagemanager_uninstall_message, $lang->pagemanager_uninstall);
    }

    if (!isset($mybb->input['no']) && $db->table_exists('pages'))
    {
        $db->drop_table('pages');
    }
    pagemanager_cache(true, true);
}

function pagemanager_plugin_update()
{
    global $db;
    if ($db->table_exists('pages'))
    {
        if (!$db->field_exists('pagegroups', 'pages'))
        {
            $db->add_column("pages", "pagegroups", "varchar(200) NOT NULL default '' AFTER `url`");
            $db->update_query("pages", array('pagegroups' => '-1'));
        }

        if (!$db->field_exists('deviceselect', 'pages'))
        {
            $db->add_column("pages", "deviceselect", "varchar(10) NOT NULL default '' AFTER `template`");
            $db->update_query("pages", array('deviceselect' => 'all'));
        }

        if ($db->field_exists('groups', 'pages') && $db->field_exists('pagegroups', 'pages'))
        {
            $db->drop_column("pages", "pagegroups");
            $db->rename_column("pages", "groups", "pagegroups", "varchar(200) NOT NULL default '' AFTER `url`");
        }
    }
}

function pagemanager_admin_action(&$action)
{
    $action['pagemanager'] = array(
        'active' => 'pagemanager'
    );
}

function pagemanager_admin_menu(&$sub_menu)
{
    global $lang;
    $lang->load("config_pagemanager");
    end($sub_menu);
    $key = (key($sub_menu)) + 10;
    $sub_menu[$key] = array(
        'id'    =>  'pagemanager',
        'title' =>  $lang->pagemanager_info_name,
        'link'  =>  'index.php?module=config-pagemanager'
    );
}

function pagemanager_admin_permissions(&$admin_permissions)
{
    global $lang;
    $lang->load("config_pagemanager");
    $admin_permissions['pagemanager'] = $lang->pagemanager_can_manage_pages;
}

function pagemanager_admin()
{
    global $mybb, $page, $db, $lang;
    $lang->load("config_pagemanager");
    if ($page->active_action != 'pagemanager')
    {
        return false;
    }
    $info = pagemanager_info();
    $sub_tabs['pagemanager'] = array(
        'title'     =>  $lang->pagemanager_main_title,
        'link'      =>  'index.php?module=config-pagemanager',
        'description'   =>  $lang->pagemanager_main_description
    );
    $sub_tabs['pagemanager_add'] = array(
        'title'     =>  $lang->pagemanager_add_title,
        'link'      =>  'index.php?module=config-pagemanager&amp;action=add',
        'description'   =>  $lang->pagemanager_add_description
    );
    $sub_tabs['pagemanager_import'] = array(
        'title'     =>  $lang->pagemanager_import_title,
        'link'      =>  'index.php?module=config-pagemanager&amp;action=import',
        'description'   =>  $lang->pagemanager_import_description
    );
    if (!$mybb->input['action'])
    {
        $page->add_breadcrumb_item($lang->pagemanager_info_name);
        $page->output_header($lang->pagemanager_info_name);
        if (!pagemanager_is_installed())
        {
            $page->output_error('<p><em>' . $lang->pagemanager_install_error . '</em></p>');
        }
        $page->output_nav_tabs($sub_tabs, 'pagemanager');
        $table = new Table;
        $table->construct_header($lang->name);
        $table->construct_header($lang->pagemanager_main_table_id, array('width' => 40, 'class' => 'align_center'));
        $table->construct_header($lang->pagemanager_main_table_framework, array('width' => 125, 'class' => 'align_center'));
        $table->construct_header($lang->pagemanager_main_table_online, array('width' => 100, 'class' => 'align_center'));
        $table->construct_header($lang->pagemanager_main_table_groups, array('width' => 125, 'class' => 'align_center'));
        $table->construct_header($lang->pagemanager_main_table_devices, array('width' => 125, 'class' => 'align_center'));
        $table->construct_header($lang->pagemanager_main_table_modified, array('width' => 150, 'class' => 'align_center'));
        $table->construct_header($lang->controls, array('width' => 100, 'class' => 'align_center'));
        $query = $db->simple_select('pages', '*', '', array('order_by' => 'name', 'order_dir' => 'ASC'));
        if ($db->num_rows($query) > 0)
        {
            while ($pages = $db->fetch_array($query))
            {

                // PHP 8 fix - 2024-March-13 - Dave
                if (!isset($mybb->input['highlight'])) {
                    $mybb->input['highlight'] = NULL;
                }

                if ($mybb->input['highlight'] == $pages['pid'])
                {
                    $highlight = array('style' => 'text-align:left; background-color: #FFFBD9;');
                    $highlightc40 = array('width' => 40, 'style' => 'background-color: #FFFBD9;', 'class' => 'align_center');
                    $highlightc100 = array('width' => 100, 'style' => 'background-color: #FFFBD9;', 'class' => 'align_center');
                    $highlightc125 = array('width' => 125, 'style' => 'background-color: #FFFBD9;', 'class' => 'align_center');
                    $highlightc150 = array('width' => 150, 'style' => 'background-color: #FFFBD9;', 'class' => 'align_center');
                }
                else
                {
                    $highlight = array('style' => 'text-align:left;');
                    $highlightc40 = array('width' => 40, 'class' => 'align_center');
                    $highlightc100 = array('width' => 100, 'class' => 'align_center');
                    $highlightc125 = array('width' => 125, 'class' => 'align_center');
                    $highlightc150 = array('width' => 150, 'class' => 'align_center');
                }
                if ($pages['enabled'])
                {
                    $status_icon = '<img src="styles/' . $page->style . '/images/icons/page_active.png" alt="' . $lang->pagemanager_main_table_enabled . '" title="' . $lang->pagemanager_main_table_enabled . '" style="vertical-align:middle;" /> ';
                    $status_lang = $lang->pagemanager_main_control_disable;
                    $status_action = 'disable';
                    $pagelink = '<br /><small>' . $lang->pagemanager_main_open_page . '<a href="' . $mybb->settings['bburl'] . '/misc.php?page=' . $pages['url'] . '" target="_blank">' . $mybb->settings['bburl'] . '/misc.php?page=' . $pages['url'] . '</a></small>';
                }
                else
                {
                    $status_icon = '<img src="styles/' . $page->style . '/images/icons/page_inactive.png" alt="' . $lang->pagemanager_main_table_disabled . '" title="' . $lang->pagemanager_main_table_disabled . '" style="vertical-align:middle;" /> ';
                    $status_lang = $lang->pagemanager_main_control_enable;
                    $status_action = 'enable';
                    $pagelink = '<br /><span style="color:#f00">' . $lang->pagemanager_main_page_disabled . '</span>';
                }
                if ($pages['framework'])
                {
                    $framework_status = $lang->yes;
                }
                else
                {
                    $framework_status = $lang->no;
                }
                if ($pages['online'])
                {
                    $online_status = $lang->yes;
                }
                else
                {
                    $online_status = $lang->no;
                }
                if ($pages['pagegroups'] == "-1")
                {
                    $groups_allowed = $lang->all_groups;
                }
                else
                {
                    $groups_allowed = $comma = '';
                    $groups = $db->simple_select("usergroups", "gid, title", "gid IN ({$pages['pagegroups']})");
                    while ($gt = $db->fetch_array($groups))
                    {
                        $groups_allowed .= $comma . '<span style="cursor:pointer;" title="' . $gt['title'] . '">' . $gt['gid'] . '</span>';
                        $comma = ', ';
                    }
                }

                $visible_on_devices = "pagemanager_edit_form_deviceselect_" . $pages['deviceselect'];

                $table->construct_cell($status_icon . '<strong><a href="' . $sub_tabs['pagemanager']['link'] . '&amp;action=edit&amp;pid=' . $pages['pid'] . '" title="' . $lang->pagemanager_main_edit . $pages['name'] . '">' . $pages['name'] . '</a></strong>' . $pagelink, $highlight);
                $table->construct_cell($pages['pid'], $highlightc40);
                $table->construct_cell($framework_status, $highlightc125);
                $table->construct_cell($online_status, $highlightc100);
                $table->construct_cell($groups_allowed, $highlightc125);
                $table->construct_cell($lang->$visible_on_devices, $highlightc125);
                $table->construct_cell($lang->sprintf($lang->pagemanager_main_table_dateline, my_date($mybb->settings['dateformat'], $pages['dateline']), my_date($mybb->settings['timeformat'], $pages['dateline'])), $highlightc150);
                $popup = new PopupMenu('page_' . $pages['pid'], $lang->options);
                $popup->add_item($lang->pagemanager_main_control_edit, $sub_tabs['pagemanager']['link'] . '&amp;action=edit&amp;pid=' . $pages['pid']);
                $popup->add_item($lang->pagemanager_main_control_export, $sub_tabs['pagemanager']['link'] . '&amp;action=export&amp;pid=' . $pages['pid']);
                $popup->add_item($status_lang, $sub_tabs['pagemanager']['link'] . '&amp;action=' . $status_action . '&amp;pid=' . $pages['pid'] . '&amp;my_post_key=' . $mybb->post_code);
                $popup->add_item($lang->pagemanager_main_control_delete, $sub_tabs['pagemanager']['link'] . '&amp;action=delete&amp;pid=' . $pages['pid'] . '&amp;my_post_key=' . $mybb->post_code, 'return AdminCP.deleteConfirmation(this,\'' . $lang->pagemanager_main_control_delete_question . '\')');
                $table->construct_cell($popup->fetch(), $highlightc100);
                $table->construct_row();
            }
        }
        else
        {
            $table->construct_cell($lang->pagemanager_main_table_no_pages, array('colspan' => 8));
            $table->construct_row();
        }
        $table->output($lang->pagemanager_main_table);
        $page->output_footer();
    }
    if ($mybb->input['action'] == 'add')
    {
        if ($mybb->request_method == 'post')
        {
            if ($mybb->input['import'])
            {
                if (!$_FILES['file'] || $_FILES['file']['error'] == 4)
                {
                    $error = $lang->pagemanager_import_error_no_file;
                }
                elseif ($_FILES['file']['error'])
                {
                    $error = $lang->sprintf($lang->pagemanager_import_error_php, $_FILES['file']['error']);
                }
                else
                {
                    if (!is_uploaded_file($_FILES['file']['tmp_name']))
                    {
                        $error = $lang->pagemanager_import_error_lost;
                    }
                    else
                    {
                        $contents = @file_get_contents($_FILES['file']['tmp_name']);
                        @unlink($_FILES['file']['tmp_name']);
                        if (!trim($contents))
                        {
                            $error = $lang->pagemanager_import_error_no_contents;
                        }
                    }
                }
                if (!$error)
                {
                    require_once MYBB_ROOT . 'inc/class_xml.php';
                    $parser = new XMLParser($contents);
                    $tree = $parser->get_tree();
                    if (!is_array($tree) || !is_array($tree['pagemanager']) || !is_array($tree['pagemanager']['attributes']) || !is_array($tree['pagemanager']['page']))
                    {
                        $error = $lang->pagemanager_import_error_no_contents;
                    }
                    if (!$error)
                    {
                        foreach ($tree['pagemanager']['page'] as $property => $value)
                        {
                            if ($property == 'tag' || $property == 'value')
                            {
                                continue;
                            }
                            $input_array[$property] = $value['value'];
                        }
                        if (!$mybb->input['version'] && $info['version'] != $tree['pagemanager']['attributes']['version'])
                        {
                            $error = $lang->pagemanager_import_error_version;
                        }
                        if ($mybb->input['name_overwrite'])
                        {
                            $input_array['name'] = $mybb->input['name_overwrite'];
                        }
                        $form_array = pagemanager_setinput($input_array, true);
                        if (!$form_array['name'] || !$form_array['url'] || !$form_array['template'])
                        {
                            $error = $lang->pagemanager_import_error_no_contents;
                        }
                    }
                }
                if ($error)
                {
                    flash_message($error, 'error');
                    admin_redirect($sub_tabs['pagemanager']['link'] . '&amp;action=import');
                }
            }
            else
            {
                $form_array = pagemanager_setinput($mybb->input);
            }
            $querycheck = $db->simple_select('pages', 'pid', 'url="' . $db->escape_string($form_array['url']) . '"');
            $check = $db->fetch_array($querycheck);
            if (!$form_array['name'])
            {
                $errors[] = $lang->pagemanager_edit_error_name;
            }
            if (!$form_array['url'])
            {
                $errors[] = $lang->pagemanager_edit_error_url;
            }
            if ($check['pid'])
            {
                $errors[] = $lang->pagemanager_edit_error_url_duplicate;
            }
            if (!$form_array['template'])
            {
                $errors[] = $lang->pagemanager_edit_error_template;
            }
            if ($mybb->input['group_type'] == 2)
            {
                if (count($mybb->input['group_1_groups']) < 1)
                {
                    $errors[] = $lang->pagemanager_edit_no_groups_selected;
                }

                $group_checked[2] = "checked=\"checked\"";
            }
            else
            {
                $group_checked[1] = "checked=\"checked\"";
            }
            if (!$errors && !$mybb->input['manual'])
            {
                if ($mybb->input['group_type'] == 2)
                {
                    if (is_array($mybb->input['group_1_groups']))
                    {
                        $checked = array();
                        foreach ($mybb->input['group_1_groups'] as $gid)
                        {
                            $checked[] = (int)$gid;
                        }

                        $selected_groups = implode(',', $checked);
                    }
                }
                else
                {
                    $selected_groups = '-1';
                }

                $updated_page = array(
                    'name'      =>  $db->escape_string($form_array['name']),
                    'url'       =>  $db->escape_string($form_array['url']),
                    'pagegroups' =>  $selected_groups,
                    'framework' =>  $form_array['framework'],
                    'template'  =>  $db->escape_string($form_array['template']),
                    'deviceselect'  =>  $db->escape_string($form_array['deviceselect']),
                    'online'    =>  $form_array['online'],
                    'enabled'   =>  $form_array['enabled'],
                    'dateline'  =>  TIME_NOW
                );
                $db->insert_query('pages', $updated_page);
                $query = $db->simple_select('pages', '*', 'url="' . $db->escape_string($form_array['url']) . '"');
                $pages = $db->fetch_array($query);
                pagemanager_cache();
                if ($mybb->input['import'])
                {
                    flash_message($lang->pagemanager_import_success, 'success');
                }
                else
                {
                    flash_message($lang->pagemanager_add_success, 'success');
                }
                admin_redirect($sub_tabs['pagemanager']['link'] . '&amp;highlight=' . $pages['pid']);
            }
        }
        else
        {
            $form_array = pagemanager_setinput();
            $mybb->input['group_1_groups'] = '';
            $group_checked[1] = "checked=\"checked\"";
            $group_checked[2] = '';
        }
        $queryadmin = $db->simple_select('adminoptions', '*', 'uid=' . $mybb->user['uid']);
        $admin_options = $db->fetch_array($queryadmin);
        if ($admin_options['codepress'] != 0)
        {
            $page->extra_header = '<link href="./jscripts/codemirror/lib/codemirror.css" rel="stylesheet">
            <link href="./jscripts/codemirror/theme/mybb.css?ver=1804" rel="stylesheet">
            <script src="./jscripts/codemirror/lib/codemirror.js"></script>
            <script src="./jscripts/codemirror/mode/xml/xml.js"></script>
            <script src="./jscripts/codemirror/mode/javascript/javascript.js"></script>
            <script src="./jscripts/codemirror/mode/css/css.js"></script>
            <script src="./jscripts/codemirror/mode/htmlmixed/htmlmixed.js"></script>
            <script src="./jscripts/codemirror/mode/clike/clike.js"></script>
            <script src="./jscripts/codemirror/mode/php/php.js"></script>
            <link href="./jscripts/codemirror/addon/dialog/dialog-mybb.css" rel="stylesheet">
            <script src="./jscripts/codemirror/addon/dialog/dialog.js"></script>
            <script src="./jscripts/codemirror/addon/search/searchcursor.js"></script>
            <script src="./jscripts/codemirror/addon/search/search.js?ver=1808"></script>
            <script src="./jscripts/codemirror/addon/fold/foldcode.js"></script>
            <script src="./jscripts/codemirror/addon/fold/xml-fold.js"></script>
            <script src="./jscripts/codemirror/addon/edit/matchbrackets.js"></script>
            <script src="./jscripts/codemirror/addon/edit/matchtags.js"></script>
            <script src="./jscripts/codemirror/addon/edit/closetag.js"></script>
            <script src="./jscripts/codemirror/addon/fold/foldgutter.js"></script>
            <link href="./jscripts/codemirror/addon/fold/foldgutter.css" rel="stylesheet">
            <style type="text/css">div.CodeMirror span.CodeMirror-matchingbracket {background: rgba(255, 150, 0, .3);border:1px dotted; border-radius: 2px;}</style>';
        }
        $page->add_breadcrumb_item($lang->pagemanager_info_name, $sub_tabs['pagemanager']['link']);
        $page->add_breadcrumb_item($sub_tabs['pagemanager_add']['title']);
        $page->output_header($lang->pagemanager_info_name . ' - ' . $sub_tabs['pagemanager_add']['title']);
        if (!pagemanager_is_installed())
        {
            $page->output_error('<p><em>' . $lang->pagemanager_install_error . '</em></p>');
        }
        $page->output_nav_tabs($sub_tabs, 'pagemanager_add');
        $form = new Form($sub_tabs['pagemanager_add']['link'], 'post', 'add_template');
        if ($errors)
        {
            $page->output_inline_error($errors);
        }
        $form_container = new FormContainer($lang->pagemanager_add_form);
        $form_container->output_row($lang->pagemanager_edit_form_name . ' <em>*</em>', $lang->pagemanager_edit_form_name_description, $form->generate_text_box('name', $form_array['name'], array('id' => 'name')), 'name');
        $form_container->output_row($lang->pagemanager_edit_form_url . ' <em>*</em>', $lang->pagemanager_edit_form_url_description, $form->generate_text_box('url', $form_array['url'], array('id' => 'url')), 'url');
        $group_select = "<script type=\"text/javascript\">
            function checkAction(id)
            {
                var checked = '';

                $('.'+id+'s_check').each(function(e, val)
                {
                    if($(this).prop('checked') == true)
                    {
                        checked = $(this).val();
                    }
                });
                $('.'+id+'s').each(function(e)
                {
                    $(this).hide();
                });
                if($('#'+id+'_'+checked))
                {
                    $('#'+id+'_'+checked).show();
                }
            }
        </script>
        <dl style=\"margin-top: 0; margin-bottom: 0; width: 100%\">
        <dt><label style=\"display: block;\"><input type=\"radio\" name=\"group_type\" value=\"1\" {$group_checked[1]} class=\"groups_check\" onclick=\"checkAction('group');\" style=\"vertical-align: middle;\" /> <strong>{$lang->all_groups}</strong></label></dt>
            <dt><label style=\"display: block;\"><input type=\"radio\" name=\"group_type\" value=\"2\" {$group_checked[2]} class=\"groups_check\" onclick=\"checkAction('group');\" style=\"vertical-align: middle;\" /> <strong>{$lang->select_groups}</strong></label></dt>
            <dd style=\"margin-top: 4px;\" id=\"group_2\" class=\"groups\">
                <table cellpadding=\"4\">
                    <tr>
                        <td valign=\"top\"><small>{$lang->groups_colon}</small></td>
                        <td>" . $form->generate_group_select('group_1_groups[]', $mybb->input['group_1_groups'], array('multiple' => true, 'size' => 10)) . "</td>
                    </tr>
                </table>
            </dd>
        </dl>
        <script type=\"text/javascript\">
            checkAction('group');
        </script>";
        $form_container->output_row($lang->pagemanager_add_edit_groupselect . " <em>*</em>", '', $group_select);
        $form_container->output_row($lang->pagemanager_edit_form_framework, $lang->pagemanager_edit_form_framework_description, $form->generate_yes_no_radio('framework', $form_array['framework']));
        $form_container->output_row($lang->pagemanager_edit_form_template . ' <em>*</em>', $lang->pagemanager_edit_form_template_description, $form->generate_text_area('template', $form_array['template'], array('id' => 'template', 'style' => 'width:100%;height:500px;')), 'template');

        $device_select = array(
            'all' => $lang->pagemanager_edit_form_deviceselect_all,
            'desktop' => $lang->pagemanager_edit_form_deviceselect_desktop,
            'mobile' => $lang->pagemanager_edit_form_deviceselect_mobile
        );
        $form_container->output_row($lang->pagemanager_edit_form_deviceselect, $lang->pagemanager_edit_form_deviceselect_description, $form->generate_select_box('deviceselect', $device_select, $form_array['deviceselect'], array('id' => 'deviceselect')), 'deviceselect');
        $form_container->output_row($lang->pagemanager_edit_form_online, $lang->pagemanager_edit_form_online_description, $form->generate_yes_no_radio('online', $form_array['online']));
        $form_container->output_row($lang->pagemanager_edit_form_enable, $lang->pagemanager_edit_form_enable_description, $form->generate_yes_no_radio('enabled', $form_array['enabled']));
        $form_container->end();
        $buttons[] = $form->generate_submit_button($lang->pagemanager_edit_form_close);
        $form->output_submit_wrapper($buttons);
        $form->end();
        if ($admin_options['codepress'] != 0)
        {
            echo '<script type="text/javascript">
            var editor = CodeMirror.fromTextArea(document.getElementById("template"), {
                lineNumbers: true,
                lineWrapping: true,
                foldGutter: true,
                gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"],
                viewportMargin: Infinity,
                indentWithTabs: true,
                indentUnit: 4,
                mode: "application/x-httpd-php",
                matchBrackets: true,
                matchTags: {bothTags: true},
                autoCloseTags: true,
                theme: "mybb"
            });
        </script>';
        }
        $page->output_footer();
    }
    if ($mybb->input['action'] == 'import')
    {
        $page->add_breadcrumb_item($lang->pagemanager_info_name, $sub_tabs['pagemanager']['link']);
        $page->add_breadcrumb_item($sub_tabs['pagemanager_import']['title']);
        $page->output_header($lang->pagemanager_info_name . ' - ' . $sub_tabs['pagemanager_import']['title']);
        if (!pagemanager_is_installed())
        {
            $page->output_error('<p><em>' . $lang->pagemanager_install_error . '</em></p>');
        }
        $page->output_nav_tabs($sub_tabs, 'pagemanager_import');
        $form = new Form($sub_tabs['pagemanager_add']['link'], 'post', '', 1);
        $form_container = new FormContainer($lang->pagemanager_import_form);
        $form_container->output_row($lang->pagemanager_import_form_file . ' <em>*</em>', $lang->pagemanager_import_form_file_description, $form->generate_file_upload_box('file'));
        $form_container->output_row($lang->pagemanager_import_form_name, $lang->pagemanager_import_form_name_description, $form->generate_text_box('name_overwrite', '', array('id' => 'name_overwrite')), 'name_overwrite');
        $form_container->output_row($lang->pagemanager_import_form_manual, $lang->pagemanager_import_form_manual_description, $form->generate_on_off_radio('manual', 0));
        $form_container->output_row($lang->pagemanager_import_form_version, $lang->pagemanager_import_form_version_description, $form->generate_yes_no_radio('version', 0));
        $form_container->end();
        $buttons[] = $form->generate_submit_button($lang->pagemanager_import_form_action, array('name' => 'import'));
        $form->output_submit_wrapper($buttons);
        $form->end();
        $page->output_footer();
    }
    if ($mybb->input['action'] == 'edit')
    {
        $query = $db->simple_select('pages', '*', 'pid=' . intval($mybb->input['pid']));
        $pages = $db->fetch_array($query);
        if (!$pages['pid'])
        {
            flash_message($lang->pagemanager_invalid_page, 'error');
            admin_redirect($sub_tabs['pagemanager']['link']);
        }
        if ($mybb->request_method == 'post')
        {
            $form_array = pagemanager_setinput($mybb->input);
            $querycheck = $db->simple_select('pages', 'pid', 'url="' . $db->escape_string($form_array['url']) . '" AND pid != ' . $pages['pid']);
            $check = $db->fetch_array($querycheck);
            if (!$form_array['name'])
            {
                $errors[] = $lang->pagemanager_edit_error_name;
            }
            if (!$form_array['url'])
            {
                $errors[] = $lang->pagemanager_edit_error_url;
            }
            if ($check['pid'])
            {
                $errors[] = $lang->pagemanager_edit_error_url_duplicate;
            }
            if (!$form_array['template'])
            {
                $errors[] = $lang->pagemanager_edit_error_template;
            }
            if ($mybb->input['group_type'] == 2)
            {
                if (count($mybb->input['group_1_groups']) < 1)
                {
                    $errors[] = $lang->pagemanager_edit_no_groups_selected;
                }
                $group_checked[2] = "checked=\"checked\"";
            }
            else
            {
                $group_checked[1] = "checked=\"checked\"";
            }
            if (!$errors)
            {
                if ($mybb->input['group_type'] == 2)
                {
                    if (is_array($mybb->input['group_1_groups']))
                    {
                        $checked = array();
                        foreach ($mybb->input['group_1_groups'] as $gid)
                        {
                            $checked[] = (int)$gid;
                        }

                        $selected_groups = implode(',', $checked);
                    }
                }
                else
                {
                    $selected_groups = '-1';
                }
                if ($form_array['name'] == $pages['name'] && $form_array['url'] == $pages['url'] &&  $form_array['framework'] == $pages['framework'] && $form_array['template'] == $pages['template'] && $form_array['deviceselect'] == $pages['deviceselect'] && $form_array['online'] == $pages['online'] && $pages['pagegroups'] == $selected_groups)
                {
                    $modified = $pages['dateline'];
                    if ($form_array['enabled'] == $pages['enabled'])
                    {
                        $update_lang = $lang->pagemanager_edit_success_nothing;
                    }
                    else
                    {
                        if ($form_array['enabled'])
                        {
                            $update_lang = $lang->pagemanager_enable_success;
                        }
                        else
                        {
                            $update_lang = $lang->pagemanager_disable_success;
                        }
                    }
                }
                else
                {
                    $modified = TIME_NOW;
                    $update_lang = $lang->pagemanager_edit_success;
                }
                $updated_page = array(
                    'name'      =>  $db->escape_string($form_array['name']),
                    'url'       =>  $db->escape_string($form_array['url']),
                    'pagegroups' =>  $selected_groups,
                    'framework' =>  $form_array['framework'],
                    'template'  =>  $db->escape_string($form_array['template']),
                    'deviceselect'  =>  $db->escape_string($form_array['deviceselect']),
                    'online'    =>  $form_array['online'],
                    'enabled'   =>  $form_array['enabled'],
                    'dateline'  =>  $modified
                );
                $db->update_query('pages', $updated_page, 'pid=' . $pages['pid']);
                pagemanager_cache();
                flash_message($update_lang, 'success');
                if ($mybb->input['continue'])
                {
                    admin_redirect($sub_tabs['pagemanager']['link'] . '&amp;action=edit&amp;pid=' . $pages['pid']);
                }
                else
                {
                    admin_redirect($sub_tabs['pagemanager']['link'] . '&amp;highlight=' . $pages['pid']);
                }
            }
        }
        else
        {
            $form_array = pagemanager_setinput($pages);
            $mybb->input['group_1_groups'] = explode(",", $pages['pagegroups']);

            if (!$pages['pagegroups'] || $pages['pagegroups'] == -1)
            {
                $group_checked[1] = "checked=\"checked\"";
                $group_checked[2] = '';
            }
            else
            {
                $group_checked[1] = '';
                $group_checked[2] = "checked=\"checked\"";
            }
        }
        $queryadmin = $db->simple_select('adminoptions', '*', 'uid=' . $mybb->user['uid']);
        $admin_options = $db->fetch_array($queryadmin);
        $sub_tabs['pagemanager_edit'] = array(
            'title'     =>  $lang->pagemanager_edit_title,
            'link'      =>  'index.php?module=config-pagemanager&amp;action=edit&amp;pid=' . $pages['pid'],
            'description'   =>  $lang->pagemanager_edit_description
        );
        if ($admin_options['codepress'] != 0)
        {
            $page->extra_header = '<link href="./jscripts/codemirror/lib/codemirror.css" rel="stylesheet">
            <link href="./jscripts/codemirror/theme/mybb.css?ver=1804" rel="stylesheet">
            <script src="./jscripts/codemirror/lib/codemirror.js"></script>
            <script src="./jscripts/codemirror/mode/xml/xml.js"></script>
            <script src="./jscripts/codemirror/mode/javascript/javascript.js"></script>
            <script src="./jscripts/codemirror/mode/css/css.js"></script>
            <script src="./jscripts/codemirror/mode/htmlmixed/htmlmixed.js"></script>
            <script src="./jscripts/codemirror/mode/clike/clike.js"></script>
            <script src="./jscripts/codemirror/mode/php/php.js"></script>
            <link href="./jscripts/codemirror/addon/dialog/dialog-mybb.css" rel="stylesheet">
            <script src="./jscripts/codemirror/addon/dialog/dialog.js"></script>
            <script src="./jscripts/codemirror/addon/search/searchcursor.js"></script>
            <script src="./jscripts/codemirror/addon/search/search.js?ver=1808"></script>
            <script src="./jscripts/codemirror/addon/fold/foldcode.js"></script>
            <script src="./jscripts/codemirror/addon/fold/xml-fold.js"></script>
            <script src="./jscripts/codemirror/addon/edit/matchbrackets.js"></script>
            <script src="./jscripts/codemirror/addon/edit/matchtags.js"></script>
            <script src="./jscripts/codemirror/addon/edit/closetag.js"></script>
            <script src="./jscripts/codemirror/addon/fold/foldgutter.js"></script>
            <link href="./jscripts/codemirror/addon/fold/foldgutter.css" rel="stylesheet">
            <style type="text/css">div.CodeMirror span.CodeMirror-matchingbracket {background: rgba(255, 150, 0, .3);border:1px dotted; border-radius: 2px;}</style>';
        }
        $page->add_breadcrumb_item($lang->pagemanager_info_name, $sub_tabs['pagemanager']['link']);
        $page->add_breadcrumb_item($sub_tabs['pagemanager_edit']['title']);
        $page->output_header($lang->pagemanager_info_name . ' - ' . $sub_tabs['pagemanager_edit']['title']);
        if (!pagemanager_is_installed())
        {
            $page->output_error('<p><em>' . $lang->pagemanager_install_error . '</em></p>');
        }
        $page->output_nav_tabs($sub_tabs, 'pagemanager_edit');
        $form = new Form($sub_tabs['pagemanager_edit']['link'], 'post', 'edit_template');
        if ($errors)
        {
            $page->output_inline_error($errors);
        }
        $form_container = new FormContainer($lang->pagemanager_edit_form);
        $form_container->output_row($lang->pagemanager_edit_form_name . ' <em>*</em>', $lang->pagemanager_edit_form_name_description, $form->generate_text_box('name', $form_array['name'], array('id' => 'name')), 'name');
        $form_container->output_row($lang->pagemanager_edit_form_url . ' <em>*</em>', $lang->pagemanager_edit_form_url_description, $form->generate_text_box('url', $form_array['url'], array('id' => 'url')), 'url');
        $group_select = "<script type=\"text/javascript\">
            function checkAction(id)
            {
                var checked = '';

                $('.'+id+'s_check').each(function(e, val)
                {
                    if($(this).prop('checked') == true)
                    {
                        checked = $(this).val();
                    }
                });
                $('.'+id+'s').each(function(e)
                {
                    $(this).hide();
                });
                if($('#'+id+'_'+checked))
                {
                    $('#'+id+'_'+checked).show();
                }
            }
        </script>
        <dl style=\"margin-top: 0; margin-bottom: 0; width: 100%\">
        <dt><label style=\"display: block;\"><input type=\"radio\" name=\"group_type\" value=\"1\" {$group_checked[1]} class=\"groups_check\" onclick=\"checkAction('group');\" style=\"vertical-align: middle;\" /> <strong>{$lang->all_groups}</strong></label></dt>
            <dt><label style=\"display: block;\"><input type=\"radio\" name=\"group_type\" value=\"2\" {$group_checked[2]} class=\"groups_check\" onclick=\"checkAction('group');\" style=\"vertical-align: middle;\" /> <strong>{$lang->select_groups}</strong></label></dt>
            <dd style=\"margin-top: 4px;\" id=\"group_2\" class=\"groups\">
                <table cellpadding=\"4\">
                    <tr>
                        <td valign=\"top\"><small>{$lang->groups_colon}</small></td>
                        <td>" . $form->generate_group_select('group_1_groups[]', $mybb->input['group_1_groups'], array('multiple' => true, 'size' => 10)) . "</td>
                    </tr>
                </table>
            </dd>
        </dl>
        <script type=\"text/javascript\">
            checkAction('group');
        </script>";
        $form_container->output_row($lang->pagemanager_add_edit_groupselect . " <em>*</em>", '', $group_select);
        $form_container->output_row($lang->pagemanager_edit_form_framework, $lang->pagemanager_edit_form_framework_description, $form->generate_yes_no_radio('framework', $form_array['framework']));
        $form_container->output_row($lang->pagemanager_edit_form_template . ' <em>*</em>', $lang->pagemanager_edit_form_template_description, $form->generate_text_area('template', $form_array['template'], array('id' => 'template', 'style' => 'width: 100%; height: 500px;')), 'template');

        $device_select = array(
            'all' => $lang->pagemanager_edit_form_deviceselect_all,
            'desktop' => $lang->pagemanager_edit_form_deviceselect_desktop,
            'mobile' => $lang->pagemanager_edit_form_deviceselect_mobile
        );
        $form_container->output_row($lang->pagemanager_edit_form_deviceselect, $lang->pagemanager_edit_form_deviceselect_description, $form->generate_select_box('deviceselect', $device_select, $form_array['deviceselect'], array('id' => 'deviceselect')), 'deviceselect');
        $form_container->output_row($lang->pagemanager_edit_form_online, $lang->pagemanager_edit_form_online_description, $form->generate_yes_no_radio('online', $form_array['online']));
        $form_container->output_row($lang->pagemanager_edit_form_enable, $lang->pagemanager_edit_form_enable_description, $form->generate_yes_no_radio('enabled', $form_array['enabled']));
        $form_container->end();
        $buttons[] = $form->generate_submit_button($lang->pagemanager_edit_form_continue, array('name' => 'continue'));
        $buttons[] = $form->generate_submit_button($lang->pagemanager_edit_form_close, array('name' => 'close'));
        $form->output_submit_wrapper($buttons);
        $form->end();
        if ($admin_options['codepress'] != 0)
        {
            echo '<script type="text/javascript">
            var editor = CodeMirror.fromTextArea(document.getElementById("template"), {
                lineNumbers: true,
                lineWrapping: true,
                foldGutter: true,
                gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"],
                viewportMargin: Infinity,
                indentWithTabs: true,
                indentUnit: 4,
                mode: "application/x-httpd-php",
                matchBrackets: true,
                matchTags: {bothTags: true},
                autoCloseTags: true,
                theme: "mybb"
            });
        </script>';
        }
        $page->output_footer();
    }
    if ($mybb->input['action'] == 'export')
    {
        $query = $db->simple_select('pages', '*', 'pid=' . intval($mybb->input['pid']));
        $pages = $db->fetch_array($query);
        if (!$pages['pid'])
        {
            flash_message($lang->pagemanager_invalid_page, 'error');
            admin_redirect($sub_tabs['pagemanager']['link']);
        }
        $extra_xml = '';
        if ($pages['framework'])
        {
            $extra_xml .= '
            <framework>' . $pages['framework'] . '</framework>';
        }
        if ($pages['deviceselect'] != 'all')
        {
            $extra_xml .= '
            <deviceselect>' . $pages['deviceselect'] . '</deviceselect>';
        }
        if (isset($pages['online']) && $pages['online'] == 0)
        {
            $extra_xml .= '
            <online>' . $pages['online'] . '</online>';
        }
        $xml = '<?xml version="1.0" encoding="' . $lang->settings['charset'] . '"?>
        <pagemanager version="' . $info['version'] . '" xmlns="' . $info['website'] . '">
            <page>
                <name><![CDATA[' . $pages['name'] . ']]></name>
                <url><![CDATA[' . $pages['url'] . ']]></url>
                <template><![CDATA[' . base64_encode($pages['template']) . ']]></template>
                <checksum>' . md5($pages['template']) . '</checksum>' . $extra_xml . '
            </page>
        </pagemanager>';
        header('Content-Disposition: attachment; filename=' . rawurlencode($pages['url']) . '.xml');
        header('Content-Type: application/xhtml+xml');
        header('Content-Length: ' . strlen($xml));
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $xml;
    }
    if ($mybb->input['action'] == 'enable' || $mybb->input['action'] == 'disable')
    {
        $highlight = '&amp;highlight=' . intval($mybb->input['pid']);
        if (!verify_post_check($mybb->input['my_post_key']))
        {
            $highlight = '';
            flash_message($lang->invalid_post_verify_key2, 'error');
        }
        else
        {
            $query = $db->simple_select('pages', 'pid', 'pid=' . intval($mybb->input['pid']));
            $pages = $db->fetch_array($query);
            if (!$pages['pid'])
            {
                $highlight = '';
                flash_message($lang->pagemanager_invalid_page, 'error');
            }
            else
            {
                if ($mybb->input['action'] == 'enable')
                {
                    $status_lang = $lang->pagemanager_enable_success;
                    $status_action = array('enabled' => 1);
                }
                else
                {
                    $status_lang = $lang->pagemanager_disable_success;
                    $status_action = array('enabled' => 0);
                }
                $db->update_query('pages', $status_action, 'pid=' . $pages['pid']);
                pagemanager_cache();
                flash_message($status_lang, 'success');
            }
        }
        admin_redirect($sub_tabs['pagemanager']['link'] . $highlight);
    }
    if ($mybb->input['action'] == 'delete')
    {
        if (!verify_post_check($mybb->input['my_post_key']))
        {
            flash_message($lang->invalid_post_verify_key2, 'error');
        }
        else
        {
            $query = $db->simple_select('pages', 'pid', 'pid=' . intval($mybb->input['pid']));
            $pages = $db->fetch_array($query);
            if (!$pages['pid'])
            {
                flash_message($lang->pagemanager_invalid_page, 'error');
            }
            else
            {
                $db->delete_query('pages', 'pid=' . $pages['pid']);
                pagemanager_cache();
                flash_message($lang->pagemanager_delete_success, 'success');
            }
        }
        admin_redirect($sub_tabs['pagemanager']['link']);
    }
    exit();
}

function pagemanager()
{
    global $mybb, $cache, $lang;
    $lang->load("pagemanager");
    $pagecache = $cache->read('pages');

    if ($mybb->input['page'] && !isset($pagecache[$mybb->input['page']]))
    {
        redirect("index.php", $lang->pagemanager_page_disabled_redirect, '', true);
        exit();
    }

    if ($mybb->input['page'] && isset($pagecache[$mybb->input['page']]))
    {
        global $db;

        require_once MYBB_ROOT . $mybb->config['admin_dir'] . "/inc/functions.php";
        $useragent = $_SERVER["HTTP_USER_AGENT"];

        $query = $db->simple_select('pages', '*', 'pid=' . $pagecache[$mybb->input['page']]['pid']);
        $pages = $db->fetch_array($query);
        if ($pages && ($pages['pagegroups'] == "-1" || is_member($pages['pagegroups'])))
        {
            if ($pages['deviceselect'] == 'mobile' && !is_mobile($useragent))
            {
                error($lang->pagemanager_page_error_only_mobile);
            }
            elseif ($pages['deviceselect'] == 'desktop' && is_mobile($useragent))
            {
                error($lang->pagemanager_page_error_only_desktop);
            }
            else
            {
                if ($pages['framework'])
                {
                    global $headerinclude, $header, $theme, $footer;
                    $template = '<html>
                    <head>
                        <title>' . $pages['name'] . ' - ' . $mybb->settings['bbname'] . '</title>
                        {$headerinclude}
                    </head>
                    <body>
                        {$header}
                        ' . $pages['template'] . '
                        {$footer}
                    </body>
                    </html>';
                    $template = str_replace("\\'", "'", addslashes($template));
                    add_breadcrumb($pages['name']);
                    eval("\$page=\"" . $template . "\";");
                    output_page($page);
                }
                else
                {
                    eval('?>' . $pages['template']);
                }
                exit();
            }
        }
        else
        {
            error_no_permission();
        }
    }
}

function pagemanager_tools_cache_rebuild()
{
    global $cache;
    class MyPagemanagerCache extends datacache
    {
        function update_pages()
        {
            pagemanager_cache();
        }
    }
    $cache = null;
    $cache = new MyPagemanagerCache;
}

function pagemanager_online(&$plugin_array)
{
    if ($plugin_array['user_activity']['activity'] == 'misc' && my_strpos($plugin_array['user_activity']['location'], 'page='))
    {
        global $cache;
        $pagecache = $cache->read('pages');
        $location = parse_url($plugin_array['user_activity']['location']);
        while (my_strpos($location['query'], '&amp;'))
        {
            $location['query'] = html_entity_decode($location['query']);
        }
        $var = explode('&', $location['query']);
        foreach ($var as $val)
        {
            $param = explode('=', $val);
            $list[$param[0]] = $param[1];
        }
        if (isset($pagecache[$list['page']]))
        {
            global $lang;
            $lang->load("pagemanager");
            $plugin_array['location_name'] = $lang->sprintf($lang->pagemanager_online, $pagecache[$list['page']]['url'], $pagecache[$list['page']]['name']);
        }
    }
}

function pagemanager_cache($clear = false, $deinst = false)
{
    global $cache;
    if ($clear == true)
    {
        $cache->update('pages', false);

        if ($deinst == true)
        {
            $cache->delete('pages');
        }
    }
    else
    {
        global $db;
        $pages = array();
        $query = $db->simple_select('pages', 'pid,name,url,pagegroups,deviceselect,online', 'enabled=1');
        while ($page = $db->fetch_array($query))
        {
            $pages[$page['url']] = $page;
        }
        $cache->update('pages', $pages);
    }
}

function pagemanager_setinput($input = false, $import = false)
{
    $default = array(
        'name'      =>  '',
        'url'       =>  '',
        'pagegroups'    =>  '-1',
        'framework' =>  0,
        'template'  =>  '',
        'deviceselect'  =>  'all',
        'online'    =>  1,
        'enabled'   =>  1
    );
    if ($input != false)
    {
        if ($input['name'])
        {
            $default['name'] = trim(my_substr($input['name'], 0, 120));
        }
        if ($input['url'])
        {
            $default['url'] = trim(my_substr($input['url'], 0, 30));
        }
        if ($input['framework'] == 1)
        {
            $default['framework'] = 1;
        }
        if ($input['template'])
        {
            if ($import == true)
            {
                if ($input['checksum'])
                {
                    if (my_strtolower(md5(base64_decode($input['template']))) == my_strtolower($input['checksum']))
                    {
                        $default['template'] = trim(base64_decode($input['template']));
                    }
                }
                else
                {
                    $default['template'] = trim($input['template']);
                }
            }
            else
            {
                $default['template'] = trim($input['template']);
            }
        }
        if ($input['deviceselect'])
        {
            if ($input['deviceselect'] != 'desktop' && $input['deviceselect'] != 'mobile')
            {
                $default['deviceselect'] = 'all';
            }
            else
            {
                $default['deviceselect'] = $input['deviceselect'];
            }
        }
        if (isset($input['online']) && $input['online'] == 0)
        {
            $default['online'] = 0;
        }
        if ($input['enabled'] == 0 || $import == true)
        {
            $default['enabled'] = 0;
        }
    }
    return $default;
}

function pagemanager_delete_plugin()
{
    global $mybb;
    if (!$mybb->get_input('delete'))
    {
        return;
    }

    if ($mybb->get_input('delete') == 1)
    {
        global $lang;
        $lang->load("config_pagemanager");
        $codename = str_replace('.php', '', basename(__FILE__));

        $installed_func = "{$codename}_is_installed";

        if (function_exists($installed_func) && $installed_func() != false)
        {
            flash_message($lang->pagemanager_still_installed, 'error');
            admin_redirect('index.php?module=config-plugins');
            exit;
        }

        if ($mybb->request_method != 'post')
        {
            global $page;
            $page->output_confirm_action("index.php?module=config-plugins&amp;action=deactivate&amp;plugin={$codename}&amp;delete=1&amp;my_post_key={$mybb->post_code}", $lang->pagemanager_delete_confirm_message, $lang->pagemanager_delete_confirm);
        }

        if (!isset($mybb->input['no']))
        {
            global $message;

            if (($handle = @fopen(MYBB_ROOT . "inc/plugins/pluginstree/" . $codename . ".csv", "r")) !== FALSE)
            {
                while (($pluginfiles = fgetcsv($handle, 1000, ",")) !== FALSE)
                {
                    foreach ($pluginfiles as $file)
                    {
                        $filepath = MYBB_ROOT . $file;

                        if (@file_exists($filepath))
                        {
                            if (is_file($filepath))
                            {
                                @unlink($filepath);
                            }
                            elseif (is_dir($filepath))
                            {
                                $dirfiles = array_diff(@scandir($filepath), array('.', '..'));
                                if (empty($dirfiles))
                                {
                                    @rmdir($filepath);
                                }
                            }
                            else
                            {
                                continue;
                            }
                        }
                    }
                }
                @fclose($handle);
                @unlink(MYBB_ROOT . "inc/plugins/pluginstree/" . $codename . ".csv");

                $message = $lang->pagemanager_delete_message;
            }
            else
            {
                flash_message($lang->pagemanager_undelete_message, 'error');
                admin_redirect('index.php?module=config-plugins');
                exit;
            }
        }
        else
        {
            admin_redirect('index.php?module=config-plugins');
            exit;
        }
    }
}
