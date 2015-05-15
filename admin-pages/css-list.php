<?php

/**
 * TT_CssListAdminPage
 * 
 * This class controls the admin page "CSS List".
 * 
 * @package    orghub
 * @subpackage admin-pages/pages
 * @author     Crystal Barton <cbarto11@uncc.edu>
 */

if( !class_exists('TT_CssListAdminPage') ):
class TT_CssListAdminPage extends APL_AdminPage
{
	
	private $model = null;	
	private $list_table = null;
	
	private $filter_types;
	private $filter;
	private $search;
	private $orderby;
	
	
	/**
	 * Creates an TT_CssListAdminPage object.
	 */
	public function __construct( 
		$name = 'tt-css-list',
		$menu_title = 'TT CSS List',
		$page_title = 'TT CSS List',
		$capability = 'administrator' )
	{
		parent::__construct( $name, $menu_title, $page_title, $capability );
		$this->model = TT_Model::get_instance();
	}
	
	
	/**
	 * Enqueues all the scripts or styles needed for the admin page. 
	 */
	public function enqueue_scripts()
	{
//		wp_enqueue_script( 'tt-sites', TTNAP_PLUGIN_URL.'/admin-pages/theme-list.js', array('jquery') );
		wp_enqueue_style( 'tt-sites', TTNAP_PLUGIN_URL.'/admin-pages/style.css' );
	}
	
	
	/**
	 * Displays the current admin page.
	 */
	public function display()
	{
		$old_site_url = 'http://clas-pages-test.uncc.edu';
		$vtt_sites = $this->model->get_vtt_sites();
		
		foreach( $vtt_sites as $site )
		{
			echo '<div class="site">';

			echo '<h4 id="blog-'.$site['blog_id'].'"><a href="'.$site['url'].'" target="_blank">'.$site['title'].'</a> Blog '.$site['blog_id'].'</h4>';
			
			echo '<div class="convert-links">';

				echo '&nbsp;<a href="'.$old_site_url.$site['path'].'" target="_blank">Old Site</a>&nbsp;';
				echo '&nbsp;<a href="'.$site['url'].'/wp-admin/customize.php" target="_blank">Customizer</a>&nbsp;';
				echo '&nbsp;<a href="'.$site['url'].'/wp-admin/themes.php?page=simple-custom-css.php" target="_blank">Custom CSS</a>&nbsp;';
			
			echo '</div>';			

			if( $site['css'] )
				echo '
					<div class="css">
						<pre>'.$site['css'].'</pre>
					</div>
				';
			
			echo '</div>';
		}
	}
	
	
	/**
	 * Processes and displays the output of an ajax request.
	 * @param  string  $action  The AJAX action.
	 * @param  array   $input   The AJAX input array.
	 * @param  int     $count   When multiple AJAX calls are made, the current count.
	 * @param  int     $total   When multiple AJAX calls are made, the total count.
	 */
	public function ajax_request( $action, $input, $count, $total )
	{
		switch( $action )
		{
			case 'refresh-all-sites':
				$ids = $this->model->get_blog_ids();
				
				$items = array();
				foreach( $ids as $id ) $items[] = array( 'blog_id' => $id );
				
				$this->ajax_set_items( 'refresh-site', $items, 'refresh_site_start', 'refresh_site_end', 'refresh_site_loop_start', 'refresh_site_loop_end' );
				break;
			
			case 'refresh-site':
				if( !isset($input['blog_id']) )
				{
					$this->ajax_failed( 'No blog id given.' );
					return;
				}
				
				$site_data = $this->model->refresh_site( $input['blog_id'] );
				$this->ajax_set( 'site', $site_data );

				if( $count === $total )
				{
					$refresh_date = date('Y-m-d H:i:s');
//					$this->model->update_option( 'tt-sites-refresh-time', $refresh_date );
					$this->ajax_set( 'refresh_date', $refresh_date );
					
					$this->set_notice( 'Successfully refreshed '.$count.' sites.' );
				}
				break;
			
			case 'analyze-sites':
				$analyze_sites = $this->model->analyze_sites();
				$this->ajax_set( 'sites', $analyze_sites );
				$this->set_notice( 'Finished analyzing sites.' );
				break;
			
			case 'convert-site':
				$blog_id = $input['blog_id'];
				switch_to_blog( $blog_id );
				
				switch_theme( 'unc-charlotte-faculty-staff-theme' );
				$this->ajax_success( $blog_id.' => unc-charlotte-faculty-staff-theme' );
				
				restore_current_blog();
				break;
				
			default:
				$this->ajax_failed( 'No valid action was given.' );
				break;
		}
	}
	
} // class TT_CssListAdminPage extends APL_AdminPage
endif; // if( !class_exists('TT_CssListAdminPage') )

