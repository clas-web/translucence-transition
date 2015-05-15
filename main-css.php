<?php
/*
Plugin Name: Translucence Transition - CSS Admin Page
Plugin URI: 
Description: 
Version: 0.0.1
Author: Crystal Barton
Author URI: http://www.crystalbarton.com
Network: True
*/


if( !defined('TTCSSAP') ):

define( 'TTCSSAP', 'Translucence Transition - CSS Admin Page' );

define( 'TTCSSAP_DEBUG', true );

define( 'TTCSSAP_PLUGIN_PATH', dirname(__FILE__) );
define( 'TTCSSAP_PLUGIN_URL', plugins_url('', __FILE__) );

define( 'TTCSSAP_VERSION', '0.0.1' );
define( 'TTCSSAP_DB_VERSION', '1.0' );

define( 'TTCSSAP_VERSION_OPTION', 'ttcss-version' );
define( 'TTCSSAP_DB_VERSION_OPTION', 'ttcss-db-version' );

define( 'TTCSSAP_OPTIONS', 'tt-options' );

endif;


if( is_admin() ):

add_action( 'wp_loaded', array('TTCSSAP_Main', 'load') );

endif;


if( !class_exists('TTCSSAP_Main') ):
class TTCSSAP_Main
{
	
	public static function load()
	{
		require_once( dirname(__FILE__).'/admin-pages/require.php' );
		
		$pages = new APL_Handler( true );
		$pages->add_page( new TT_CssListAdminPage );
		$pages->setup();
	}

}
endif;