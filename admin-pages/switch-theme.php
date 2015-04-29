<?php

/**
 * TT_SwitchThemeAdminPage
 * 
 * This class controls the admin page "Theme List".
 * 
 * @package    orghub
 * @subpackage admin-pages/pages
 * @author     Crystal Barton <cbarto11@uncc.edu>
 */

if( !class_exists('TT_SwitchThemeAdminPage') ):
class TT_SwitchThemeAdminPage extends APL_AdminPage
{
	
	private $model = null;	
	
	
	/**
	 * Creates an TT_SwitchThemeAdminPage object.
	 */
	public function __construct( 
		$name = 'tt-switch-theme',
		$menu_title = 'TT Switch Theme',
		$page_title = 'TT Switch Theme',
		$capability = 'administrator' )
	{
		parent::__construct( $name, $menu_title, $page_title, $capability );
		$this->model = TT_Model::get_instance();
	}
	
	
	/**
	 * Processes the current admin page.
	 */
	public function process()
	{
		if( !array_key_exists('blog_id', $_REQUEST) ) return;
		
		switch_to_blog( $_REQUEST['blog_id'] );
		
		$plugin_path = ABSPATH . 'wp-content/plugins/jetpack/jetpack.php';
		echo $plugin_path;
		
		if( is_plugin_active($plugin_path) )
		{
			echo 'plugin active... checking module... ';
			if( !Jetpack::is_module_active('custom-css') )
			{
				echo 'module not active... activating...';
				Jetpack::activate_module('custom-css');
			}
			else
			{
				echo 'module active...';
			}
			restore_current_blog();
			return;
		}
		else
		{
			echo 'plugin not active... activating...';
			$result = activate_plugin( $plugin_path );
			echo '<pre>'; var_dump($result); echo '</pre>';
			restore_current_blog();
		}
//		wp_redirect( $this->get_page_url().'?page=tt-switch-theme&blog_id='.$_REQUEST['blog_id'].'&action=1' );
	}
	
	
	/**
	 * Displays the current admin page.
	 */
	public function display()
	{
		if( !array_key_exists('blog_id', $_REQUEST) )
		{
			echo 'The blog id must be specified.';
			return;
		}
		
		switch_to_blog( $_REQUEST['blog_id'] );
		
		$plugin_path = ABSPATH . 'wp-content/plugins/jetpack/jetpack.php';
		
		if( is_plugin_active($plugin_path) )
		{
			echo 'Jetpack is active.<br/>';
			
			if( Jetpack::is_module_active('custom-css') )
			{
				echo 'Custom CSS is active.<br/>';
			}
			else
			{
				echo 'Custom CSS is NOT active.<br/>';
			}
		}
		else
		{
			echo 'Jetpack is NOT active.<br/>';
		}
		
		restore_current_blog();		
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
	
} // class TT_SwitchThemeAdminPage extends APL_AdminPage
endif; // if( !class_exists('TT_SwitchThemeAdminPage') )



