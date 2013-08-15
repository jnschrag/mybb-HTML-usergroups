<?php
/**
 * HTML Usergroups
 * Copyright 2013 Jacque Schrag
 */

 // Disallow Direct Access
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

/*
*	Plugin Information
*/
function html_usergroups_info() {
    global $lang;
    
    $lang->load("html_usergroups");
    
    return array(
        "name" => "HTML Usergroups",
        "description" => "Creates settings so usergroups must have permission to use HTML in their posts.",
        "website"			=> "http://github.com/jnschrag/mybb-HTML-usergroups",
		"author"			=> "Jacque Schrag",
		"authorsite"		=> "http://jacqueschrag.com",
		"version"			=> "1.0",
		"guid"				=> "",
		"compatibility"		=> "*"       
    );   
}
/*
*	End Plugin Information
*/

/*
*	Plugin Install
*/
function html_usergroups_install() {
    global $mybb, $db, $cache;
    
    $db->write_query("ALTER TABLE `".TABLE_PREFIX."usergroups` ADD `canusehtml` INT(1) NOT NULL DEFAULT '0';");
    $db->write_query('UPDATE '.TABLE_PREFIX.'usergroups SET canusehtml = 1 WHERE gid IN (3, 4, 6)');
	
    $cache->update_usergroups();
}
/*
*	End Plugin Install
*/

/*
*	Check if plugin is installed
*/
function html_usergroups_is_installed() {
    global $db;
    return $db->field_exists("canusehtml", "usergroups");
}
/*
*	End Check if plugin is installed
*/


/*
*	Plugin Uninstall
*/
function html_usergroups_uninstall() {
    global $db, $cache;
    
    $db->query("ALTER TABLE `".TABLE_PREFIX."usergroups` DROP `canusehtml`;");
    $cache->update_usergroups();
}
/*
*	End Plugin Uninstall
*/

/*
*	Usergroup permissions
*	This function writes the permission checkboxes out to the permissions page
*/
$plugins->add_hook("admin_formcontainer_end", "html_usergroups_edit_group");
function html_usergroups_edit_group()
{
	global $run_module, $form_container, $lang, $form, $mybb;
	
	$lang->load("html_usergroups");

	if($run_module == 'user' && !empty($form_container->_title) && !empty($lang->forums_posts) && $form_container->_title == $lang->forums_posts)
	{
		$html_usergroups_options = array();
		$html_usergroups_options[] = $form->generate_check_box('canusehtml', 1, $lang->html_usergroups_perm_base, array('checked' => $mybb->input['canusehtml']));
		$form_container->output_row($lang->html_usergroups_perm, '', '<div class="group_settings_bit">'.implode('</div><div class="group_settings_bit">', $html_usergroups_options).'</div>');
	}
}
/*
*	End Usergroup Permissions
*/

/*
*	Usergroup Permissions
*	This function retrieves the permissions sent from the previous function and saves the permission settings
*/
$plugins->add_hook("admin_user_groups_edit_commit", "html_usergroups_edit_group_do");
function html_usergroups_edit_group_do()
{
	global $updated_group, $mybb;

	$updated_group['canusehtml'] = intval($mybb->input['canusehtml']);
}
/*
*	End Usergroup Permissions
*/

/*
*	Checks to see usergroup permission and forum permission for HTML
*/
$plugins->add_hook("showthread_start", "html_usergroups_parse");
function html_usergroups_parse() 
{
	global $mybb, $cache, $forum;
	
	$usergroups_cache = $cache->read("usergroups");
	
	if ($usergroups_cache[$mybb->user['usergroup']]['canusehtml'] && $forum['allowhtml'] == 1) {
		$forum['allowhtml'] = 1;
	}
	else {
		$forum['allowhtml'] = 0;
	}
}

?>