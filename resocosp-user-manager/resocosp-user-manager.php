<?php 
/*
	Plugin Name: ReSoCoSp User Access Manager
	Description: "ReSoCoSp User Access Manager" gestisce le operazioni degli utenti del sito, come le registrazioni.
	Version: 0.1
	Author: Progetto Resocosp
	Author URI: http://samuelestrappa.wordpress.com
*/


define( 'USER_MANAGER_PLUGIN_DIR', plugin_dir_path(__FILE__));

require_once( USER_MANAGER_PLUGIN_DIR . 'resocosp-user-manager.class.php' );

register_activation_hook( __FILE__, array( 'UserManager', 'activation'));
register_deactivation_hook( __FILE__, array( 'UserManager', 'deactivation'));

new UserManager();

?>