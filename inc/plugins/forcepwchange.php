<?php

/**
 * Force Password Change Plugin 1.3
 * Author: Will Pillar
 * Copyright 2010 Will Pillar, All Rights Reserved
 */

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB")) {
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook("admin_user_menu", "forcepwchange_admin_nav");
$plugins->add_hook("admin_user_action_handler", "forcepwchange_action_handler");

$plugins->add_hook("global_end", "forcepwchange_check_changed");
$plugins->add_hook("usercp_do_password_end", "forcepwchange_password_changed");
$plugins->add_hook('usercp_password', 'forcepwchange_alert_handler');

function forcepwchange_info() {
  $version = '1.3';
  $plugin = 'Force Password Change';
  $author = 'Will Pillar';
  return array(
    "name"		=> $plugin,
    "description"	=> "A plugin which allows an Admin to force a single user and one or more usergroups to change their password.<br />Updated to work with MyBB 1.8 series by <a href=\"https://github.com/dequeues/mybb-forcepasswordchange\">dequeues</a>",
    "website" => "https://github.com/dequeues/mybb-forcepasswordchange",
    "author"		=> $author,
    "authorsite"	=> "http://willpillar.com",
    "version"		=> $version,
    "guid" 		=> "10bfc5251a4e99c6518a8cea54d8d86a",
    "compatibility"     => "18*"
  );
}

function forcepwchange_install() {
  global $db, $mybb;
  
  if($db->field_exists('forcepwchange', "users")) {
    $db->write_query("ALTER TABLE ".TABLE_PREFIX."users DROP forcepwchange");
  }
  
  $db->write_query("ALTER TABLE ".TABLE_PREFIX."users ADD forcepwchange int NOT NULL default 0");
}

function forcepwchange_uninstall() {
  global $db;
  
  if($db->field_exists('forcepwchange', "users")) {
    $db->write_query("ALTER TABLE ".TABLE_PREFIX."users DROP forcepwchange");
  }
}

function forcepwchange_is_installed() {
  global $db;
  
  if($db->field_exists('forcepwchange', "users")) {
    return true;
  }
  else {
    return false;
  }
}

function forcepwchange_activate() {
  global $db, $lang;
  
  $lang->load("forcepwchange");
  
  $insert_array = array(
    'title' => 'forcepw_alert',
    'template' => $db->escape_string("<div class=\"error\"><p><em>{$lang->forcepwchange_user_message}</em></p></div><br>"),
    'sid' => '-1',
    'version' => '',
    'dateline' => TIME_NOW
  );
  
  $db->insert_query("templates", $insert_array);
  
  
  include MYBB_ROOT."/inc/adminfunctions_templates.php";
  
  find_replace_templatesets("usercp_password", '#{\$errors}#', "{\$errors}\n{\$forcepwalert}\n");
}

function forcepwchange_deactivate() {
  global $db;
  
  $db->delete_query("templates", "title = 'forcepw_alert'");
  
  include MYBB_ROOT."/inc/adminfunctions_templates.php";
  
  find_replace_templatesets("usercp_password", '#(\n?){\$forcepwalert}(\n?)#', '', 0);
}

function forcepwchange_admin_nav(&$sub_menu) {
  global $mybb, $lang;
  
  $lang->load("forcepwchange");
  
  end($sub_menu);
  $key = (key($sub_menu))+10;
  
  if(!$key) {
    $key = '70';
  }
  
  $sub_menu[$key] = array('id' => 'forcepwchange', 'title' => $lang->forcepwchange_plugin_name, 'link' => "index.php?module=user-forcepwchange");
  
  return $sub_menu;
}

function forcepwchange_action_handler(&$action) {
  $action['forcepwchange'] = array('active' => 'forcepwchange', 'file' => 'forcepwchange.php');
  
  return $action;
}

function forcepwchange_check_changed() {
  global $db, $mybb, $lang;
  
  $lang->load("forcepwchange");
  
  if($mybb->user['uid'] != 0) {
    $query = $db->simple_select("users", "forcepwchange", "uid=".$mybb->user['uid']);
    
    while($user = $db->fetch_array($query)) {
      if($user['forcepwchange'] == 1) {
        global $mybb;
        
        if(!$mybb->user['uid'] || !$mybb->user['forcepwchange']) {
          return FALSE;
        }
        
        if(THIS_SCRIPT == 'usercp.php' && $mybb->input['action'] == 'password') {
          return FALSE;
        }
        if($mybb->request_method == "post") {
          return FALSE;
        }
        
        $url = "usercp.php?action=password";
        $message = $lang->forcepwchange_redirect_message;
        redirect($url, $message);
      }
    }
  }
}

function forcepwchange_password_changed() {
  global $db, $mybb;
  $db->update_query("users", array("forcepwchange" => 0), "uid='{$mybb->user['uid']}'");
  
  redirect("index.php", "Your password has been changed successfully", "Success", true);
}

function forcepwchange_alert_handler() {
  global $mybb, $templates, $forcepwalert;
  
  if(!$mybb->user['forcepwchange']) {
    return false;
  }
  
  eval("\$forcepwalert.= \"".$templates->get("forcepw_alert")."\";");
}