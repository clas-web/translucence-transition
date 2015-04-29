<?php

/**
 * TT_ActivateJetpackAdminPage
 * 
 * This class controls the admin page "Activate Jetpack".
 * 
 * @package    orghub
 * @subpackage admin-pages/pages
 * @author     Crystal Barton <cbarto11@uncc.edu>
 */

if( !class_exists('TT_ActivateJetpackAdminPage') ):
class TT_ActivateJetpackAdminPage extends APL_AdminPage
{
	
	private $model = null;	
	
	
	/**
	 * Creates an TT_SwitchThemeAdminPage object.
	 */
	public function __construct( 
		$name = 'tt-activate-jetpack',
		$menu_title = 'TT Activate Jetpack',
		$page_title = 'TT Activate Jetpack',
		$capability = 'administrator' )
	{
		parent::__construct( $name, $menu_title, $page_title, $capability );
		$this->model = TT_Model::get_instance();
	}
	
//		wp_redirect( $this->get_page_url().'?page=tt-switch-theme&blog_id='.$_REQUEST['blog_id'].'&action=1' );
	
	/**
	 * Displays the current admin page.
	 */
	public function display()
	{
 		$plugin_path = ABSPATH . 'wp-content/plugins/jetpack/jetpack.php';
		$sites = wp_get_sites( array('limit'=>999999) );
		
		foreach( $sites as $site )
		{
			if( !is_plugin_active($plugin_path) )
				activate_plugin( $plugin_path );
		}
		
		
// 		if( !array_key_exists('blog_id', $_REQUEST) )
// 		{
// 			echo 'The blog id must be specified.';
// 		}
// 		else
// 		{
// 			$plugin_path = ABSPATH . 'wp-content/plugins/jetpack/jetpack.php';
// 			
// 			echo $plugin_path.'<br/>';
// 			echo 'activating Jetpack for blog '.$_REQUEST['blog_id'].'<br/>';
// 			
// 			switch_to_blog( $_REQUEST['blog_id'] );
// 		
// 			if( !is_plugin_active($plugin_path) )
// 			{
// 				echo 'plugin not active... activating...';
// 				$result = activate_plugin( $plugin_path );
// 			}
// 			else
// 			{
// 				echo 'plugin active... ';
// 			}
// 		
// 			// connection
// // 			echo 'connecting Jetpack... ';
// // 			$jcs = new Jetpack_Client_Server;
// // 			apl_print( $jcs->authorize() );
// //			$results = Jetpack::try_registration();
// //			apl_print($results);
// // 			$jp = Jetpack_Network::init();
// // 			$jp->do_subsiteregister( $_REQUEST['blog_id'] );
// 		
// 			echo 'checking module... ';
// 			if( !Jetpack::is_module_active('custom-css') )
// 			{
// 				echo 'module not active... activating...';
// 				Jetpack::activate_module('custom-css');
// 			}
// 			else
// 			{
// 				echo 'module active...';
// 			}
// 		
// 			if( is_plugin_active($plugin_path) )
// 			{
// 				if( Jetpack::is_module_active('custom-css') )
// 				{
// 					apl_print( 'Jetpack is active with Custom CSS module.' );
// 				}
// 				else
// 				{
// 					apl_print( 'Jetpack is active but Custom CSS module is not.' );
// 				}
// 			}
// 			else
// 			{
// 				apl_print( 'Jetpack NOT enabled' );
// 			}
// 
// 			restore_current_blog();
// 		}		
// 		
// 		$this->form_start_get( 'blog-entry' );
// 		
// 		
// 		$this->form_end();
	}

	public function get_page_url()
	{
		$page_url = 'http';
		if( isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on') ) $page_url .= 's';
		$page_url .= '://';
		$request_uri_parts = explode('?', $_SERVER['REQUEST_URI']);
		$request_uri = $request_uri_parts[0];
		if( $_SERVER['SERVER_PORT'] != '80' )
			$page_url .= $_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].$request_uri;
		else
			$page_url .= $_SERVER['SERVER_NAME'].$request_uri;
		return $page_url;
	}
	
} // class TT_ActivateJetpackAdminPage extends APL_AdminPage
endif; // if( !class_exists('TT_ActivateJetpackAdminPage') )



