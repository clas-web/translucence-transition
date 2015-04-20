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
		wp_enqueue_script( 'tt-sites', TT_PLUGIN_URL.'/admin-pages/theme-list.js', array('jquery') );
		wp_enqueue_style( 'tt-sites', TT_PLUGIN_URL.'/admin-pages/style.css' );
	}
	
	
	/**
	 * Displays the current admin page.
	 */
	public function display()
	{
//		$this->list_table->prepare_items( $this->filters, $this->search, $this->orderby );
		?>
		
		<a href="#tt-analysis">Analysis</a>
		
		<div id="ajax-buttons">
		
		<?php
		$this->form_start_get( 'refresh', null, 'refresh' );
			$this->create_ajax_submit_button( 'Refresh Sites', 'refresh-all-sites', null, null, 'refresh_all_sites_start', 'refresh_all_sites_end', 'refresh_all_sites_loop_start', 'refresh_all_sites_loop_end' );
		$this->form_end();

		$this->form_start_get( 'analyze', null, 'analyze' );
			$this->create_ajax_submit_button( 'Analyze Sites', 'analyze-sites', null, null, 'analyze_sites_start', 'analyze_sites_end', 'analyze_sites_loop_start', 'analyze_sites_loop_end' );
		$this->form_end();
		?>
		
		</div>

		<div id="ajax-status">&nbsp;</div>
		<div id="ajax-progress">&nbsp;</div>
		
		<div id="tt-sites-list">
		<?php
// 		$sites = $this->model->get_sites();
// 		
// 		foreach( $sites as $site )
// 		{
// 			foreach( $site as $key => $value )
// 			{
// 				echo $key.' => '.$value.'<br/>';
// 			}
// 			echo '<br/><br/>';
// 		}
		?>
		</div>
		
		<a href="#tt-sites-list">Site List</a>
		<div id="tt-analysis">
			<?php
				$this->model->analyze_sites( $this );

/* 				foreach( $sites as $name => $site )
				{
					?>
					<h2>Compare Site: <?php echo $name; ?></h2>
					
					<div>Number of site: <?php echo count($site['default_sites']); ?></div>
					<?php
					
					
					foreach( $site['default_sites'] as $s )
					{
						?>
						<div>
							<a href="<?php echo $s['site']['url']; ?>"><?php echo $s['site']['title']; ?></a>
							<span class="num_problems" style="margin-left:1em;">Number of Problems: <?php echo $s['num_problems']; ?></span>
						</div>
						<?php
					}
				}
*/			?>
		</div>
		<a href="#tt-sites-list">Site List</a>
		<a href="#tt-analysis">Analysis</a>
		
		<?php
//		$this->form_start( 'theme-list-table' );
//			$this->list_table->display();
//		$this->form_end();
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

