<?php
/*
Plugin Name: Translucence Transition - Network Admin Page
Plugin URI: 
Description: 
Version: 0.0.1
Author: Crystal Barton
Author URI: http://www.crystalbarton.com
Network: True
*/


if( !defined('TTNAP') ):

define( 'TTNAP', 'Translucence Transition - Network Admin Page' );

define( 'TTNAP_DEBUG', true );

define( 'TTNAP_PLUGIN_PATH', dirname(__FILE__) );
define( 'TTNAP_PLUGIN_URL', plugins_url('', __FILE__) );

define( 'TTNAP_VERSION', '0.0.1' );
define( 'TTNAP_DB_VERSION', '1.0' );

define( 'TTNAP_VERSION_OPTION', 'tt-version' );
define( 'TTNAP_DB_VERSION_OPTION', 'tt-db-version' );

define( 'TTNAP_OPTIONS', 'tt-options' );

endif;


if( is_admin() ):

add_action( 'admin_enqueue_scripts', array('TTNAP_Main', 'enqueue_scripts') );
require_once( dirname(__FILE__).'/apl/apl.php' );
add_action( 'wp_loaded', array('TTNAP_Main', 'load') );
add_action( 'network_admin_menu', array('TTNAP_Main', 'update'), 5 );

endif;


if( !class_exists('TTNAP_Main') ):
class TTNAP_Main
{
	
	public static function enqueue_scripts()
	{
		wp_enqueue_script( 'apl-ajax', plugins_url('apl/ajax.js', __FILE__), array('jquery') );
	}
	
	public static function load()
	{
		require_once( dirname(__FILE__).'/admin-pages/require.php' );
		
		$pages = new APL_Handler( true );
		$pages->add_page( new TT_ThemeListAdminPage );
		$pages->setup();
	}
	
	
	public static function update()
	{
		$model = TT_Model::get_instance();
		$model->create_tables();

 		update_option( TT_VERSION_OPTION, TT_VERSION );
 		update_option( TT_DB_VERSION_OPTION, TT_DB_VERSION );
	}
	
}
endif;