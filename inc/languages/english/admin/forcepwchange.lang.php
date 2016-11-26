<?php
/* 
 * Force Password Change 1.3
 * MyBB 1.8 Plugin
 * Author: Will Pillar
 * Copyright 2010 Will Pillar, All Rights Reserved.
 */

$l['forcepwchange_plugin_name'] = "Force Password Change";

$l['forcepwchange_admin_tab_home'] = "Force User";
$l['forcepwchange_admin_tab_awaiting'] = "Users Awaiting Change";
$l['forcepwchange_admin_tab_forcegroup'] = "Force Group(s)";
$l['forcepwchange_admin_tab_home_desc'] = "From here you can force a single user to change their password.";
$l['forcepwchange_admin_tab_awaiting_desc'] = "This shows you the users who are set to change their password but haven't yet done so.";
$l['forcepwchange_admin_tab_forcegroup_desc'] = "From here you can force one or more usergroups to change their password.";

$l['forcepwchange_admin_error_nouser'] = "That user does not exist, please try again.";
$l['forcepwchange_admin_success_forced'] = "Success! Next time the user accesses a page they will have to change their password.";
$l['forcepwchange_admin_success_group'] = "Success! All the users in those usergroups will now be force to change their passwords.";
$l['forcepwchange_admin_table_heading_awaiting'] = "Users Awaiting Forced Password Change";
$l['forcepwchange_admin_row_username'] = "Username";
$l['forcepwchange_admin_row_options'] = "Options";
$l['forcepwchange_admin_success_revoked'] = "Success! You have revoked the user's password change.";
$l['forcepwchange_admin_table_heading_force'] = "Force Password Change On User";
$l['forcepwchange_admin_table_heading_forcegroup'] = "Force Password Change On Usergroup(s)";
$l['forcepwchange_admin_field_username'] = "Username";
$l['forcepwchange_admin_field_desc_username'] = "Please enter the username of the user who needs a new password.";
$l['forcepwchange_admin_submit'] = "Submit";

$l['forcepwchange_admin_usergroups'] = "Usergroups";
$l['forcepwchange_admin_usergroups_desc'] = "Select which usergroups you'd like to force a password change on. Use CTRL to select multiple.";

$l['forcepwchange_user_message'] = "The Administrator is forcing you to change your password. You cannot use the rest of the forum until your password is changed.";

?>
