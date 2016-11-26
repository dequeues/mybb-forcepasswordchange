<?php

/**
 * Force Password Change Plugin 1.3
 * Author: Will Pillar
 * Copyright 2010 Will Pillar, All Rights Reserved.
 */

if(!defined("IN_MYBB")) {
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$lang->load("forcepwchange");

$page->add_breadcrumb_item($lang->forcepwchange_plugin_name, "index.php?module=user-forcepwchange");

switch ($mybb->input['action']) {

    case "forcepwchange_awaitingchange":
        $nav = "forcepwchange_awaitingchange";
        break;
    case "forcepwchange_forcegroup":
        $nav = "forcepwchange_forcegroup";
        break;
    default:
        $nav = "forcepwchange_home";
}

log_admin_action();

$page->output_header($lang->forcepwchange_plugin_name);

$sub_tabs['forcepwchange_home'] = array(
        'title' => $lang->forcepwchange_admin_tab_home,
        'link' => "index.php?module=user-forcepwchange",
        'description' => $lang->forcepwchange_admin_tab_home_desc
);
$sub_tabs['forcepwchange_forcegroup'] = array(
        'title' => $lang->forcepwchange_admin_tab_forcegroup,
        'link' => "index.php?module=user-forcepwchange&amp;action=forcepwchange_forcegroup",
        'description' => $lang->forcepwchange_admin_tab_forcegroup_desc
);
$sub_tabs['forcepwchange_awaitingchange'] = array(
        'title' => $lang->forcepwchange_admin_tab_awaiting,
        'link' => "index.php?module=user-forcepwchange&amp;action=forcepwchange_awaitingchange",
        'description' => $lang->forcepwchange_admin_tab_awaiting_desc
);

$page->output_nav_tabs($sub_tabs, $nav);

if($page->active_action != "forcepwchange") {
    return;
}

if($mybb->input['action'] == "forcepwchange_do_force") {
    $query = $db->simple_select("users", "uid", "username='".$db->escape_string($mybb->input['username'])."'");
    $user = $db->fetch_field($query, "uid");

    if(!$user) {
        flash_message($lang->forcepwchange_admin_error_nouser, 'error');
        admin_redirect("index.php?module=user-forcepwchange");
    }
    else {
        $db->query("UPDATE ".TABLE_PREFIX."users SET forcepwchange=1 WHERE uid='".$user."'");

        flash_message($lang->forcepwchange_admin_success_forced, 'success');
        admin_redirect("index.php?module=user-forcepwchange");
    }
}

if($mybb->input['action'] == "forcepwchange_do_force_group") {

    //SELECT u.* FROM mybb_users u WHERE 1=1 AND (u.usergroup IN ("4") OR CONCAT(',',additionalgroups,',') LIKE '%,4,%')

    foreach($mybb->input['usergroups'] as $usergroup) {
        $query = $db->query("SELECT u.* FROM ".TABLE_PREFIX."users u WHERE 1=1 AND (u.usergroup IN (".$usergroup.")
            OR CONCAT(',',additionalgroups,',') LIKE '%,".$usergroup.",%')");

        while($users = $db->fetch_array($query)) {
            $db->query("UPDATE ".TABLE_PREFIX."users SET forcepwchange=1 WHERE uid='".$users['uid']."'");
        }
    }

    flash_message($lang->forcepwchange_admin_success_group, 'success');
    admin_redirect("index.php?module=user-forcepwchange&amp;action=forcepwchange_forcegroup");

}

if($mybb->input['action'] == "forcepwchange_awaitingchange") {

    $form = new Form("index.php?module=user-forcepwchange_awaitingchange", "post");

    $form_container = new FormContainer($lang->forcepwchange_admin_table_heading_awaiting);
    $form_container->output_row_header($lang->forcepwchange_admin_row_username, array('class' => 'align_left', width => '75%'));
    $form_container->output_row_header($lang->forcepwchange_admin_row_options, array('class' => 'align_center'));

    $query = $db->simple_select("users", "uid, username", "forcepwchange=1");

    while($users = $db->fetch_array($query)) {

        $form_container->output_cell("<div style=\"\"><strong>{$users['username']}</strong></div>");

        $popup = new PopupMenu("award_{$users['uid']}", $lang->options);

        $popup->add_item("Revoke", "index.php?module=user-forcepwchange&amp;action=forcepwchange_revoke&amp;uid={$users['uid']}");

        $form_container->output_cell($popup->fetch(), array("class" => "align_center"));

        $form_container->construct_row();

    }

    $form_container->end();
    $form->end();
}

if($mybb->input['action'] == "forcepwchange_forcegroup") {

    $query = $db->simple_select("usergroups", "gid, title", "gid != '1'", array('order_by' => 'title'));
    while($usergroup = $db->fetch_array($query)) {
        $options[$usergroup['gid']] = $usergroup['title'];
        $display_group_options[$usergroup['gid']] = $usergroup['title'];
    }

    $form = new Form("index.php?module=user-forcepwchange&amp;action=forcepwchange_do_force_group", "post");

    $form_container = new FormContainer($lang->forcepwchange_admin_table_heading_forcegroup);

    $form_container->output_row($lang->forcepwchange_admin_usergroups, $lang->forcepwchange_admin_usergroups_desc, $form->generate_select_box('usergroups[]', $options, $mybb->input['usergroups'], array('id' => 'usergroups', 'multiple' => true, 'size' => 8)), 'usergroups');

    $form_container->output_row('','',"<p style=\"font-weight:bold;color:#AB1D2E;\">Note: Usergroups with a large number of users may take a while to process, so don't be alarmed if it takes a long time
        before you see the success message.</p>");

    $form_container->construct_row();

    $form_container->end();

    $buttons[] = $form->generate_submit_button($lang->forcepwchange_admin_submit);
    $form->output_submit_wrapper($buttons);

    $form->end();
}

if($mybb->input['action'] == "forcepwchange_revoke") {

    $user = $mybb->input['uid'];
    $db->query("UPDATE ".TABLE_PREFIX."users SET forcepwchange=0 WHERE uid='".$user."'");

    flash_message($lang->forcepwchange_admin_success_revoked, 'success');
    admin_redirect("index.php?module=user-forcepwchange&amp;action=forcepwchange_awaitingchange");

}

if(!$mybb->input['action']) {
    $form = new Form("index.php?module=user-forcepwchange&amp;action=forcepwchange_do_force", "post");

    $form_container = new FormContainer($lang->forcepwchange_admin_table_heading_force);

    $form_container->output_row($lang->forcepwchange_admin_field_username." <em>*</em>",$lang->forcepwchange_admin_field_desc_username,
            $form->generate_text_box('username', $mybb->input['username'], array('id' => 'username')), 'username');

    $form_container->construct_row();

    $form_container->end();

    $buttons[] = $form->generate_submit_button($lang->forcepwchange_admin_submit);
    $form->output_submit_wrapper($buttons);

    $form->end();
}

$page->output_footer();

?>