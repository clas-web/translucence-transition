<?php

/**
 * OrgHub_SitesAdminPage
 * 
 * This class controls the admin page "Theme List".
 * 
 * @package    orghub
 * @subpackage admin-pages/pages
 * @author     Crystal Barton <cbarto11@uncc.edu>
 */

if( !class_exists('TT_ThemeListAdminPage') ):
class TT_ThemeListAdminPage extends APL_AdminPage
{
	
	private $model = null;	
	private $list_table = null;
	
	private $filter_types;
	private $filter;
	private $search;
	private $orderby;
	
	
	/**
	 * Creates an TT_ThemeListAdminPage object.
	 */
	public function __construct( 
		$name = 'tt-sites-list',
		$menu_title = 'TT Sites List',
		$page_title = 'TT Sites List',
		$capability = 'administrator' )
	{
		parent::__construct( $name, $menu_title, $page_title, $capability );
		$this->model = TT_Model::get_instance();
	}
	
	
	/**
	 * Initialize the admin page.  Called during "admin_init" action.
	 */
	public function init()
	{
//		$this->setup_filters();
//		$this->list_table = new TT_ThemeListTable( $this );
	}
	
	
	/**
	 * Loads the admin page.  Called during "load-{page}" action.
	 */
	public function load()
	{
//		$this->list_table->load();
	}
	
	
	/**
	 * Add the screen options for the page.
	 * Called during "load-{page}" action.
	 */
	public function add_screen_options()
	{
		$this->add_per_page_screen_option( 'tt_sites_per_page', 'Sites', 100 );
//		$this->add_selectable_columns( $this->list_table->get_selectable_columns() );
	}

	
	
	/**
	 * Processes the current admin page.
	 */
	public function process()
	{
//		if( $this->list_table->process_batch_action() ) return;

		if( empty($_REQUEST['action']) ) return;
		
		switch( $_REQUEST['action'] )
		{
			case 'refresh':
// 				$this->model->site->refresh_all_sites();
// 				$this->handler->force_redirect_url = $this->get_page_url();
				break;
		}
	}
	
	
	/**
	 * Enqueues all the scripts or styles needed for the admin page. 
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_script( 'tt-sites', TTNAP_PLUGIN_URL.'/admin-pages/theme-list.js', array('jquery') );
		wp_enqueue_style( 'tt-sites', TTNAP_PLUGIN_URL.'/admin-pages/style.css' );
	}
	
	
	/**
	 * Displays the current admin page.
	 */
	public function display()
	{
		$old_site_url = 'http://clas-incubator-wp.uncc.edu';
		$as = $this->model->analyze_sites();
		
		echo count($as['sites']).' Sites found.';
		
		?>
		<h3>Theme Variation List</h3>
		<div id="tt-variations-list">
		<?php
		
		foreach( $as['variations'] as $variation => $sites )
		{
			echo '<a href="#variation-'.$variation.'">'.$variation.' ('.count($sites).')</a>&nbsp;&nbsp;&nbsp;&nbsp;';
		}
		
		foreach( $as['variations'] as $variation => $sites )
		{
			echo '<h4 id="variation-'.$variation.'">'.$variation.' ('.count($sites).')</h4>';
			foreach( $sites as $site )
			{
				echo '
					<div class="variation-site-link">
					<a class="link-down" href="#blog-'.$site['blog_id'].'">&darr;</a>
					<a class="link-site" href="'.$site['url'].'" target="_blank">'.$site['title'].'</a>
					</div>
				';
			}
		}
		
		?>
		</div>
		
		<h3>All Translucence Sites</h3>
		<div id="tt-translucence-sites">
		<?php
		
		foreach( $as['sites'] as $site )
		{
			echo '<div class="site">';
			
			echo '<h4 id="blog-'.$site['blog_id'].'"><a href="'.$site['url'].'" target="_blank">'.$site['title'].'</a> Blog '.$site['blog_id'].'</h4>';
			
			if( $site['status'] == false )
			{
				echo '
					<div class="site_error_message">
						<h5>Error Message</h5>
						<pre>'.$site['message'].'</pre>
					</div>
				';
				echo '</div>';
				continue;
			}

			echo '<div class="stylesheet">'.$site['stylesheet'].' / '.$site['options']['background'].'</div>';

			echo '<div class="convert-links">';

/*			
				$this->form_start( 'convert-site' );
				
				?>
				<input type="hidden" name="blog_id" value="<?php echo $site['blog_id']; ?>" />
				<?php
				
				$this->create_ajax_submit_button(
					'Convert', 
					'convert-site', 
					null,
					null,
					'convert_site_start',
					'convert_site_end',
					'convert_site_loop_start',
					'convert_site_loop_end' );
				
				$this->form_end();
*/				
				echo '&nbsp;<a href="'.$site['url'].'/wp-admin/themes.php" target="_blank">Themes</a>&nbsp;';
				echo '&nbsp;<a href="'.$old_site_url.$site['path'].'" target="_blank">Original Site</a>&nbsp;';
				echo '&nbsp;<a href="'.$site['url'].'" target="_blank">Converted Site</a>&nbsp;';
			
			echo '</div>';
			
			if( $site['widget_area_warnings'] )
				echo '
					<div class="widget_area_warnings">
						<h5>Widget Area Warnings</h5>
						<pre>'.$this->print_array($site['widget_area_warnings']).'</pre>
					</div>
				';
			if( $site['css'] )
				echo '
					<div class="css">
						<h5>Current CSS</h5>
						<pre>'.$site['css'].'</pre>
					</div>
				';
			if( $site['css_additions'] )
				echo '
					<div class="css_additions">
						<h5>CSS Additions</h5>
						<pre>'.$this->print_array($site['css_additions']).'</pre>
					</div>
				';
// 			if( $site['theme_mods'] )
// 				echo '
// 					<div class="theme_mods">
// 						<h5>Current Theme Mods</h5>
// 						<pre>'.$this->print_array($site['theme_mods']).'</pre>
// 					</div>
// 				';
			if( $site['theme_mods_additions'] )
				echo '
					<div class="theme_mods_additions">
						<h5>Theme Mods Additions</h5>
						<pre>'.$this->print_array($site['theme_mods_additions']).'</pre>
					</div>
				';
			if( $site['vtt_options_additions'] )
				echo '
					<div class="vtt_options">
						<h5>VTT Options</h5>
						<pre>'.$this->print_array($site['vtt_options_additions']).'</pre>
					</div>
				';
			
			echo '</div>';
		}
	}
	
	
	private function print_array( $array, $num = 0 )
	{
		$html = '';
		
		foreach( $array as $key => $value )
		{
			for( $i = 0; $i < $num; $i++ )
			{
				$html .= '     ';
			}
			
			if( is_array($value) )
			{
				$html .= "[$key] =>\r\n";
				$html .= $this->print_array( $value, $num+1 );
			}
			elseif( is_object($value) )
			{
				$html .=  "[$key] =>\r\n";
				$html .= $this->print_array( $value, $num+1 );
			}
			else
			{
				$html .=  "[$key] => $value\r\n";
			}
		}
		
		return $html;
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
	
} // class TT_ThemeListAdminPage extends APL_AdminPage
endif; // if( !class_exists('TT_ThemeListAdminPage') )

