<?php
/*
Plugin Name: Translucence Transition Network Display
Plugin URI: 
Description: 
Version: 0.0.1
Author: Crystal Barton
Author URI: http://www.crystalbarton.com
Network: True
*/


if( !defined('TT') ):

define( 'TT', 'Translucence Transition' );

define( 'TT_DEBUG', true );

define( 'TT_PLUGIN_PATH', dirname(__FILE__) );
define( 'TT_PLUGIN_URL', plugins_url('', __FILE__) );

define( 'TT_VERSION', '0.0.1' );
define( 'TT_DB_VERSION', '1.0' );

define( 'TT_VERSION_OPTION', 'tt-version' );
define( 'TT_DB_VERSION_OPTION', 'tt-db-version' );

define( 'TT_OPTIONS', 'tt-options' );
define( 'TT_LOG_FILE', dirname(__FILE__).'/log.txt' );

endif;


if( is_admin() ):

add_action( 'admin_enqueue_scripts', array('TT_Display', 'enqueue_scripts') );
require_once( dirname(__FILE__).'/apl/apl.php' );
add_action( 'wp_loaded', array('TT_Display', 'load') );
add_action( 'network_admin_menu', array('TT_Display', 'update'), 5 );

endif;


if( !class_exists('TT_Display') ):
class TT_Display
{
	
	public static function enqueue_scripts()
	{
		wp_enqueue_script( 'apl-ajax', plugins_url('apl/ajax.js', __FILE__), array('jquery') );
	}
	
	public static function load()
	{
		require_once( dirname(__FILE__).'/admin-pages/require.php' );
		
		// Network admin pages.
		$pages = new APL_Handler( true );
		
		$pages->add_page( new TT_ThemeListAdminPage );
		
		$pages->setup();
	}
	
	
	public static function update()
	{
//		$version = get_option( TT_DB_VERSION_OPTION );
//  	if( $version !== TT_DB_VERSION )
//  	{
 			$model = TT_Model::get_instance();
 			$model->create_tables();
//  	}
 		
 		update_option( TT_VERSION_OPTION, TT_VERSION );
 		update_option( TT_DB_VERSION_OPTION, TT_DB_VERSION );
	}
	
}
endif;