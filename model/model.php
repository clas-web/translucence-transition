<?php
/**
 * TT_Model
 * 
 * The sites model for the Translucence Transition plugin.
 * 
 * @package    orghub
 * @subpackage classes
 * @author     Crystal Barton <cbarto11@uncc.edu>
 */

if( !class_exists('TT_Model') ):
class TT_Model
{

	private static $instance = null;	// The only instance of this class.
	
	// Names of tables used by the model without prefix.
	private static $site_table = 'tt_site';	// 
	
	
	
	/**
	 * Private Constructor.  Needed for a Singleton class.
	 * Creates an OrgHub_SitesModel object.
	 */
	protected function __construct()
	{
		global $wpdb;
		self::$site_table        = $wpdb->base_prefix.self::$site_table;
	}


	/**
	 * Get the only instance of this class.
	 * @return  OrgHub_SitesModel  A singleton instance of the sites model class.
	 */
	public static function get_instance()
	{
		if( self::$instance	=== null )
		{
			self::$instance = new TT_Model();
		}
		return self::$instance;
	}



//========================================================================================
//================================================================== Database tables =====


	/**
	 * Create the required database tables.
	 */
	public function create_tables()
	{
		global $wpdb;
		
        $db_charset_collate = '';
        if( !empty($wpdb->charset) )
			$db_charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if( !empty($wpdb->collate) )
			$db_charset_collate .= " COLLATE $wpdb->collate";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE ".self::$site_table." (
				  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				  blog_id bigint(20) unsigned NOT NULL UNIQUE,
				  url text NOT NULL DEFAULT '',
				  title text NOT NULL DEFAULT '',
				  template text NOT NULL DEFAULT '',
				  stylesheet text NOT NULL DEFAULT '',
				  option_key text NOT NULL DEFAULT '',
				  options text NOT NULL DEFAULT '',
				  widgets text NOT NULL DEFAULT '',
				  PRIMARY KEY  (id)
				) ENGINE=InnoDB $db_charset_collate;";
		
        dbDelta($sql);
	}
	
	
	/**
	 * Drop the required database tables.
	 */
	public function delete_tables()
	{
		global $wpdb;
		$wpdb->query( 'DROP TABLE '.self::$site_table.';' );
	}


	/**
	 * Clear the required database tables.
	 */
	public function clear_tables()
	{
		global $wpdb;
		$wpdb->query( 'DELETE FROM '.self::$site_table.';' );
	}
	
	
	
//========================================================================================
//================================================ Import / Updating database tables =====
	
	
	/**
	 * Adds an OrgHub site to the database.
	 * @param   array     $args  An array of data about a site.
	 * @return  int|bool  The id of the inserted site or false on failure.
	 */
	public function add_site( &$args )
	{
		//if( !$this->check_args( $args ) ) return false;

		//
		// If site already exists, then update the user.
		//
		$db_site = $this->get_site_by_blog_id( $args['blog_id'] );
		if( $db_site )
		{
			return $this->update_site( $db_site['id'], $args );
		}
		
		global $wpdb;

		//
		// Insert new site into Sites table.
		//
		$result = $wpdb->insert(
			self::$site_table,
			array(
				'blog_id'			=> $args['blog_id'],
				'url'				=> $args['url'],
				'title'				=> $args['title'],
				'template'			=> $args['template'],
				'stylesheet'		=> $args['stylesheet'],
				'widgets'			=> $args['widgets'],
				'option_key'		=> $args['option_key'],
				'options'			=> $args['options'],
			),
			array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);
		
		//
		// Check to make sure insertion was successful.
		//
		$site_id = $wpdb->insert_id;
		if( !$site_id )
		{
			$this->last_error = 'Unable to insert site.';
			return false;
		}

		return $site_id;
	}
	
	
	/**
	 * Updates an OrgHub site in the database.
	 * @param   int       $id    The site's id (not the WordPress blog id).
	 * @param   array     $args  An array of data about a site.
	 * @return  int|bool  The id of the updated site or false on failure.
	 */
	public function update_site( $id, &$args )
	{
		global $wpdb;
		
		//
		// Update user in Users table.
		//
		$result = $wpdb->update(
			self::$site_table,
			array(
				'blog_id'			=> $args['blog_id'],
				'url'				=> $args['url'],
				'title'				=> $args['title'],
				'template'			=> $args['template'],
				'stylesheet'		=> $args['stylesheet'],
				'widgets'			=> $args['widgets'],
				'option_key'		=> $args['option_key'],
				'options'			=> $args['options'],
			),
			array( 'id' => intval( $id ) ),
			array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ),
			array( '%d' )
		);

		//
		// Check to make sure update was successful.
		//
		if( $result === false )
		{
			$this->last_error = 'Unable to update site.';
			return false;
		}
		
		return $id;
	}



//========================================================================================
//================================================= Retrieve site data from database =====
	

	/**
	 * Retrieve a complete list of OrgHub site from the database after filtering.
	 * @param   array   $filter       An array of filter name and values.
	 * @param   array   $search       An array of search columns and phrases.
	 * @param   string  $orderby      The column to orderby.
	 * @param   int     $offset       The offset of the users list.
	 * @param   int     $limit        The amount of users to retrieve.
	 * @return  array   An array of sites given the filtering.
	 */
	public function get_sites( $filter = array(), $search = array(), $orderby = array(), $offset = 0, $limit = -1 )
	{
		global $wpdb;
		
		$list = array();
		$list[self::$site_table] = array(
			'id', 'blog_id', 'title', 'url', 'template', 'stylesheet', 'widgets', 'option_key', 'options'
		);
		
		$list = $this->get_column_list( $list );
		
		$groupby = 'blog_id';
//		$filter = $this->filter_sql($filter, $search, $groupby, $orderby, $offset, $limit);
		
// 		apl_print( 'SELECT '.$list.' FROM '.self::$site_table.' '.$filter );
//		return $wpdb->get_results( 'SELECT '.$list.' FROM '.self::$site_table.' '.$filter, ARRAY_A );
		$sites = $wpdb->get_results( 'SELECT '.$list.' FROM '.self::$site_table, ARRAY_A );
		foreach( $sites as &$site )
		{
			$site['options'] = json_decode( $site['options'], true );
		}
		
		return $sites;
	}
	
	
	/**
	 * The amount of OrgHub sites from the database after filtering.
	 * @param   array   $filter       An array of filter name and values.
	 * @param   array   $search       An array of search columns and phrases.
	 * @param   string  $orderby      The column to orderby.
	 * @return  array   The amount of sites given the filtering.
	 */
	public function get_sites_count( $filter, $search, $orderby )
	{
		global $wpdb;
 		$groupby = null;
//		return $wpdb->get_var( "SELECT COUNT(DISTINCT ".self::$site_table.".id) FROM ".self::$site_table.' '.$this->filter_sql($filter, $search, $groupby, $orderby) );
		return $wpdb->get_var( "SELECT COUNT(DISTINCT ".self::$site_table.".id) FROM ".self::$site_table );
	}


	/**
	 * Get a site's information based on its blog id.
	 * @param   int         $blog_id  The blog's id.
	 * @return  array|bool  The site's data on success, otherwise false.
	 */
	public function get_site_by_blog_id( $blog_id )
	{
		global $wpdb;
		
		$list = array();
		$list[self::$site_table] = array(
			'id', 'blog_id', 'title', 'url', 'template', 'stylesheet', 'widgets', 'option_key', 'options'
		);
		
		$list = $this->get_column_list( $list );

		$site = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT '.$list.' FROM '.self::$site_table.' WHERE blog_id = %d',
				$blog_id
			),
			ARRAY_A
		);
		
		if( $site ) return $site;
		return false;
	}
	
	
	/**
	 * Gets the ids of all blogs on the site.
	 * @return  An array of all blog ids.
	 */
	public function get_blog_ids()
	{
		global $wpdb;
		return $wpdb->get_col( 'SELECT blog_id FROM '.$wpdb->blogs );
	}



//========================================================================================
//============================================================= Actions / Refreshing =====
	
	
	/**
	 * Refresh all the sites.
	 */
	public function refresh_all_sites()
	{
		$sites = wp_get_sites( array( 'limit' => 1000000 ) );
		
		foreach( $sites as &$site )
		{
			$this->refresh_site( $site['blog_id'] );
		}
		
//		$this->update_option( 'tt-sites-refresh-time', date('Y-m-d H:i:s') );
	}
	
	
	/**
	 * Refresh a single site.
	 * @param   int         $blog_id  The blog id of the site.
	 * @return  array|bool  The site's info on success, otherwise false.
	 */
	public function refresh_site( $blog_id )
	{
		global $wpdb;
		
		$site = $wpdb->get_row( 'SELECT * FROM '.$wpdb->blogs.' WHERE blog_id = '.intval($blog_id), ARRAY_A );
		if( !$site ) return false;
		
		switch_to_blog( $blog_id );

		$site['url'] = get_bloginfo( 'url' );
		$site['title'] = get_bloginfo( 'name' );

		$site['template'] = get_option( 'template', '' );
		$site['stylesheet'] = get_option( 'stylesheet', '' );

		$site['widgets'] = json_encode( get_option( 'sidebars_widgets', array() ) );
		
		// TODO:  get options based on stylesheet....
		switch( $site['stylesheet'] )
		{
			case 'translucence': 		// parent (not used on any sites!!)
			case '2010-translucence': 	// parent (not used on any sites!!)
				$site['option_key'] = '2010_translucence_options';
				$site['options'] = get_option( '2010_translucence_options', array() );
				break;
			
			case 'translucence-uncc':  // Translucence UNC Charlotte - child of 2010-translucence
				$site['option_key'] = '2010_translucence_options';
				$site['options'] = array();
				break;
			
			case 'translucence-uncc-minimal-light': // Translucence Minimal Light - child of 2010-translucence
				$site['option_key'] = 'translucence_unc_charlotte_options';
				$site['options'] = get_option( 'translucence_unc_charlotte_options', array() );
				break;
			
			case 'twentyeleven_translucence_child': // child of twentyeleven (not used!!)
			default:
				$site['option_key'] = '';
				$site['options'] = array();
				break;
		}
		
		if( array_key_exists('css', $site['options']) )
			unset( $site['options']['css'] );
		
		$site['options'] = json_encode( $site['options'] );
		
		restore_current_blog();
		
		$this->add_site( $site );
		
		return $this->get_site_by_blog_id( $blog_id );
	}


	public function get_column_list( $columns )
	{
		$list = '';
		$i = 0;
		foreach( $columns as $table => $names )
		{
			if( count($names) === 0 ) continue;
			if( $i > 0 ) $list .= ',';
			$list .= $table.'.'.implode( ','.$table.'.', $names );
			$i++;
		}
		
		if( $list === '' ) $list = '*';
		return $list;
	}
	
	
	public function analyze_sites( $admin_page )
	{
		// include jetpack css.
		if( !class_exists('Jetpack_Custom_CSS') )
			require_once( ABSPATH . '/wp-content/plugins/jetpack/modules/custom-css/custom-css.php' );
		
		if( !post_type_exists('safecss') )
			Jetpack_Custom_CSS::init();
		
		$old_site_url = 'http://clas-incubator-wp.uncc.edu';
		
// 		$defaults = array(
// 			'jon-crane (new template)' 	=> array(
// 				'blog_id'		=> 445,
// 				'url'			=> '',
// 				'stylesheet'	=> 'translucence-uncc-minimal-light',
// 				'option_key'	=> 'translucence_unc_charlotte_options',
// 				'options'		=> null,
// 				'sites'			=> array(),
// 				'default_sites'	=> array(),
// 			),
// 			'elizabeth-r-miller (old template)' 	=> array(
// 				'blog_id'		=> 341,
// 				'url'			=> '',
// 				'stylesheet'	=> 'translucence-uncc-minimal-light',
// 				'option_key'	=> 'translucence_unc_charlotte_options',
// 				'options'		=> null,
// 				'sites'			=> array(),
// 				'default_sites'	=> array(),
// 			),
// 		);
// 		
// 		
// 		$ignore_keys = array(
// 			'activated-theme',
// 			'options-mode',
// 			'revert',
// 			'variation-type',
// 			'description-text-padding-top',
// 			'header-text-padding-top',
// 			'page-links-display',
// 			'site-border-shadow',
// 			'site-hover-border-style',
// 			'site-border-width',
// 			'header-image-options',
// 			'content-width',
// 			'overall-content-width',
// 			'overall-right01-width',
// 			'right01-width',
// 			'right01-visibility',
// 			'header-color-hover-rgb',
// 			'header-highlight-hover-color-rgb',
// 			'header-highlight-color-rgb',
// 			'header-opacity',
// 			'top-highlight-hover-color-rgb',
// 			'header-block-height',
// 			'title-box-border-width',
// 			'headerblock-border-style',
// 			'headerblock-hover-border-style',
// 			'site-border-style',
// 			'description-box-highlight-color-rgb',
// 			'description-box-color-hover-rgb',
// 			'description-box-highlight-hover-color-rgb',
// 			'title-box-color-hover-rgb',
// 			'title-box-highlight-color-rgb',
// 			'top-margin-top',
// 			'top-margin-bottom',
// 			'menu-width',
// 			'bottom-highlight-hover-color-rgb',
// 		);
// 		
// 		
// 		$exempt_sites = array();
// 		foreach( $defaults as $name => &$options )
// 		{
// 			switch_to_blog( $options['blog_id'] );
// 			
// 			$options['url'] = get_bloginfo( 'url' );
// 			
// 			$options['options'] = get_option( $options['option_key'], array() );
// 			if( array_key_exists('css', $options['options']) )
// 				unset( $options['options']['css'] );
// 			
// 			restore_current_blog();
// 			
// 			$exempt_sites[] = $options['blog_id'];
// 		}
		
		
		$sites = $this->get_sites();
		
		$count = 0;
		$no_error_count = 0;
		foreach( $sites as $site )
		{
			if( $site['template'] != '2010-translucence' ) continue;
			if( $site['stylesheet'] != 'translucence-uncc-minimal-light' ) continue;

			echo '<div><div>'.
				'<a href="'.$site['url'].'" target="_blank">'.$site['title'].'</a>'.
				'</div>';
				
			switch_to_blog( $site['blog_id'] );
			
			$css = Jetpack_Custom_CSS::get_css();
			$css = preg_replace('!/\*.*?\*/!s', '', $css);
			$css = preg_replace('/\n\s*\n/', "\n", $css);
			echo '<div style="color:green"><pre>'.$css.'</pre></div>';
			
			restore_current_blog();
			
			$options = $site['options'];

			if( strtolower($options['background_color']) != '#ffffff' )
			{
				echo '<div style="color:#369">background_color => '.strtolower($options['background_color']).'</div>';
			}

			if( strtolower($options['background_image_file']) != 'background-white.png' )
			{
				$position = explode($options['background_position']);
				echo '<div style="color:#369">background_image_file => '.strtolower($options['background_image_file']).'</div>';
				echo '<div style="color:#369">background_position => '.strtolower('$position[0]').'</div>';
				echo '<div style="color:#369">background_attachment => '.strtolower($options['background_attachment']).'</div>';
			}
		
			if( strtolower($options['textcolor']) != '#333333' )
			{
				echo '<div style="color:blue">textcolor => '.strtolower($options['textcolor']).'</div>';
			}

			if( strtolower($options['linkcolor']) != '#003366' )
			{
				echo '<div style="color:blue">linkcolor => '.strtolower($options['linkcolor']).'</div>';
			}

			if( strtolower($options['title-box-visibility']) == 'none' )
			{
				echo '<div style="color:blue">title-box-visibility => '.strtolower($options['title-box-visibility']).'</div>';
			}

			if( strtolower($options['title-box-color']) != '#000000' )
			{
				echo '<div style="color:blue">title-box-color => '.strtolower($options['title-box-color']).'</div>';
			}

			if( floatval($options['title-box-opacity']) != 0.6 )
			{
				echo '<div style="color:blue">title-box-opacity => '.strtolower($options['title-box-opacity']).'</div>';
			}
		
			if( strtolower($options['title-box-link-color']) != '#ffffff' )
			{
				echo '<div style="color:blue">title-box-link-color => '.strtolower($options['title-box-link-color']).'</div>';
			}
			
			if( strtolower($options['description-box-visibility']) == 'none' )
			{
				echo '<div style="color:blue">description-box-visibility => '.strtolower($options['description-box-visibility']).'</div>';
			}

			if( strtolower($options['description-box-color']) != '#000000' )
			{
				echo '<div style="color:blue">description-box-color => '.strtolower($options['description-box-color']).'</div>';
			}

			if( floatval($options['description-box-opacity']) != 0.6 )
			{
				echo '<div style="color:blue">description-box-opacity => '.strtolower($options['description-box-opacity']).'</div>';
			}
		
			if( strtolower($options['description-box-link-color']) != '#ffffff' )
			{
				echo '<div style="color:blue">description-box-link-color => '.strtolower($options['description-box-link-color']).'</div>';
			}

			switch( strtolower($options['header-text-display']) )
			{
				case 'top':
				case 'above':
					echo '<div style="color:#369">title-position => hleft vtop</div>';
					break;
			
				case 'bottom':
					echo '<div style="color:#369">title-position => hleft vbottom</div>';
					break;
			
				case 'hide':
					echo '<div style="color:blue">title-position => hide</div>';
					break;

				case 'middle':
				default:
					break;
			}

			if( strtolower($options['site-border-style']) == 'none' )
			{
				echo '<div style="color:blue">site-border-style => '.strtolower($options['site-border-style']).'</div>';
			}

			if( strtolower($options['site-border-color']) != '#cccccc' )
			{
				echo '<div style="color:blue">site-border-color => '.strtolower($options['site-border-color']).'</div>';
			}
		
			if( strtolower($options['site-color']) != '#ffffff' )
			{
				echo '<div style="color:blue">site-color => '.strtolower($options['site-color']).'</div>';
			}
		
			if( intval($options['site-opacity']) != 1 )
			{
				echo '<div style="color:blue">site-opacity => '.strtolower($options['site-opacity']).'</div>';
			}

			if( strtolower($options['header-border-style']) != 'none' )
			{
				echo '<div style="color:blue">header-border-style => '.strtolower($options['header-border-style']).'</div>';
			}
		
			if( strtolower($options['header-color']) != '#ffffff' )
			{
				echo '<div style="color:blue">header-color => '.strtolower($options['header-color']).'</div>';
			}
		
			if( intval($options['header-opacity']) != 1 )
			{
				echo '<div style="color:blue">header-opacity => '.strtolower($options['header-opacity']).'</div>';
			}
		
			if( strtolower($options['cat-links-visibility']) == 'none' )
			{
				echo '<div style="color:blue">cat-links-visibility => '.strtolower($options['cat-links-visibility']).'</div>';
			}

			if( strtolower($options['cat-links-border-style']) == 'none' )
			{
				echo '<div style="color:blue">cat-links-border-style => '.strtolower($options['cat-links-border-style']).'</div>';
			}

			if( strtolower($options['cat-links-border-color']) != '#cccccc' )
			{
				echo '<div style="color:blue">cat-links-border-color => '.strtolower($options['cat-links-border-color']).'</div>';
			}

			if( strtolower($options['cat-links-link-color']) != '#003366' )
			{
				echo '<div style="color:blue">cat-links-link-color => '.strtolower($options['cat-links-link-color']).'</div>';
			}

			if( strtolower($options['cat-links-color']) != '#f6f6f6' )
			{
				echo '<div style="color:blue">cat-links-color => '.strtolower($options['cat-links-color']).'</div>';
			}

			if( floatval($options['cat-links-opacity']) != 1 )
			{
				echo '<div style="color:blue">cat-links-opacity => '.strtolower($options['cat-links-opacity']).'</div>';
			}

			if( strtolower($options['tag-links-visibility']) == 'none' )
			{
				echo '<div style="color:blue">tag-links-visibility => '.strtolower($options['tag-links-visibility']).'</div>';
			}

			if( strtolower($options['tag-links-border-style']) == 'none' )
			{
				echo '<div style="color:blue">tag-links-border-style => '.strtolower($options['tag-links-border-style']).'</div>';
			}

			if( strtolower($options['tag-links-border-color']) != '#cccccc' )
			{
				echo '<div style="color:blue">tag-links-border-color => '.strtolower($options['tag-links-border-color']).'</div>';
			}

			if( strtolower($options['tag-links-link-color']) != '#003366' )
			{
				echo '<div style="color:blue">tag-links-link-color => '.strtolower($options['tag-links-link-color']).'</div>';
			}
		
			if( strtolower($options['tag-links-color']) != '#fffacd' )
			{
				echo '<div style="color:blue">tag-links-color => '.strtolower($options['tag-links-color']).'</div>';
			}

			if( floatval($options['tag-links-opacity']) != 1 )
			{
				echo '<div style="color:blue">tag-links-opacity => '.strtolower($options['tag-links-opacity']).'</div>';
			}

			$path = parse_url($site['url'], PHP_URL_PATH);
		
			echo '<div class="convert-links">';
			
			echo '&nbsp;<a href="'.$site['url'].'" target="_blank">Convert Site</a>&nbsp;';
			
			$admin_page->form_start( 'convert-site' );
			?>
			<input type="hidden" name="blog_id" value="<?php echo $site['blog_id']; ?>" />
			<?php
			$admin_page->create_ajax_submit_button(
				'Convert', 
				'convert-site', 
				null,
				null,
				'convert_site_start',
				'convert_site_end',
				'convert_site_loop_start',
				'convert_site_loop_end' );
			$admin_page->form_end();
			
			echo '&nbsp;<a href="'.$old_site_url.$path.'" target="_blank">New Site</a>&nbsp;';
			
			echo '</div>';
			echo '</div>';
		
/*		<button type="button" 
		        class="apl-ajax-button"
		        page="<?php echo $this->handler->get_page_name(); ?>"
		        tab="<?php echo $this->handler->get_tab_name(); ?>"
		        action="<?php echo $action; ?>"
		        form="<?php echo $form_classes; ?>"
		        input="<?php echo $input_names; ?>"
		        cb_start="<?php echo $cb_start; ?>"
		        cb_end="<?php echo $cb_end; ?>"
		        cb_loop_start="<?php echo $cb_loop_start; ?>"
		        cb_loop_end="<?php echo $cb_loop_end; ?>"
		        nonce="<?php echo $nonce; ?>">
		    <?php echo $text; ?>
		</button>
*/

/*			if( in_array($site['blog_id'], $exempt_sites) ) continue;
						
// 			apl_print($site['options']);
			$site_url = str_replace( 'https', 'http', $site['url'] );
			
			// display:
			$print_html = false;
			$html = '<div>';
			$html .= '<div class="site-name"><a href="'.$site['url'].'">'.$site['title'].'</a></div>';
			
			$count_for_this_site = false;
			foreach( $defaults as $name => &$options )
			{
				if( $options['stylesheet'] !== $site['stylesheet'] ) continue;
				if( $options['option_key'] !== $site['option_key'] ) continue;
				
// 				apl_print($options);
				$option_url = str_replace( 'https', 'http', $options['url'] );
				$options['sites'][] = $site;

				// display:
				$h = '';
				$h .= '<div>Options URL: '.$option_url.'</div>';
				$h .= '<div>Site URL: '.$site_url.'</div>';
					
				$num_of_problems = 0;
				$is_default = true;
				foreach( $options['options'] as $name => $value )
				{
					if( !empty($value) || ($value === 0) || ($value === '0') )
					{
						if( $site['options'][$name] === '' ) continue;
						if( $site['options'][$name] === false ) continue;
						if( $site['options'][$name] === null ) continue;
					}
					
					if( strpos( $name, 'ie' ) !== false ) continue;
					if( strpos( $name, 'padding' ) !== false ) continue;
					if( strpos( $name, 'header-border' ) !== false ) continue;
					if( strpos( $name, 'top-color' ) !== false ) continue;
					if( strpos( $name, 'left01-' ) !== false ) continue; 
					if( strpos( $name, 'right01-' ) !== false ) continue; 
					if( strpos( $name, 'right02-' ) !== false ) continue; 
					if( strpos( $name, 'entry-link-' ) !== false ) continue;
					if( strpos( $name, 'border-width' ) !== false ) continue;
					if( strpos( $name, 'content-margin-' ) !== false ) continue;
					
					if( in_array($name, $ignore_keys) ) continue;
					
					$dov = str_replace( 'https', 'http', $value );
					$dov = str_replace( $option_url, '', $dov );
					$dov = strtolower( $dov );

					$sov = str_replace( 'https', 'http', $site['options'][$name] );
					$sov = str_replace( $site_url, '', $sov );
					$sov = strtolower( $sov );
					
					
					$num = '';
					if( $sov != $dov )
					{
						$num_of_problems++;
						$is_default = false;
						$color = 'red';
						$num = $num_of_problems;
						$h .= '<div style="color:'.$color.'">';
						$h .= '<span style="width:2em;display:inline-block;text-align:right;margin-right:0.5em;">'.$num."</span>".$name.":   '".$dov."'   ===>   '".$sov."'";
						$h .= '</div>';
					}
					else
					{
						$color = 'green';
					}
					
					// display:
// 					echo '<div style="color:'.$color.'">';
// 					echo '<span style="width:2em;display:inline-block;text-align:right;margin-right:0.5em;">'.$num."</span>".$name.":   '".$dov."'   ===>   '".$sov."'";
// 					echo '</div>';
				}
				
				if( $num_of_problems < 20 )
				{
					if( $count_for_this_site == false )
					{
						$count++;
						$count_for_this_site = true;
						if( $num_of_problems == 0 )
							$no_error_count++;
					}
					$print_html = true;
					$html .= $h;
					$options['default_sites'][] = array( 'site' => $site, 'num_problems' => $num_of_problems );
				}
					
// 				if( $is_default )
// 					$option['default_sites'][] = $site;
			}

			// display:
			$html .= '</div>';
			
			if( $print_html ) echo $html;
*/		}
		
/*		?>
		<div>
			Number of Site with less than 20 errors: <?php echo $count; ?>
			Number of Sites with no errors: <?php echo $no_error_count; ?>
		</div>
		<?php */
		
		return $defaults;
	}
	
}
endif;

	