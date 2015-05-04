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
		$site['sidebars_widgets'] = get_option( 'sidebars_widgets' );
		
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
	
	
	public function get_translucence_sites()
	{
		$sites = wp_get_sites( array( 'limit' => 99999 ) );
		
		$keys = array_keys($sites);
		$tsites = array();
		
		$variations_theme_count = 0;
		
		foreach( $keys as $key )
		{
			$site =& $sites[$key];
			$site['status'] = true;
			$site['message'] = '';
			
			switch_to_blog( $site['blog_id'] );
			
			//
			// Ignore sites that are archived or deleted.
			//
			
			if( $site['archived'] || $site['deleted'] )
				continue;
			
			//
			// Do not continue if not a translucence theme.
			//
			
			$t = get_option('template');
			if( $t == 'variations-template-theme' )
				$variations_theme_count++;
			
			if( $t != '2010-translucence' )
				continue;
			
			//
			// Store site data.
			//
			
			$site['url'] = get_bloginfo( 'url' );
			$site['title'] = get_bloginfo( 'name' );
			
			//
			// Get Transluence options.
			//
			
			$site['stylesheet'] = get_option('stylesheet');
			switch( $site['stylesheet'] )
			{
				case '2010-translucence':
					$site['options'] = get_option( '2010_translucence_options' );
					break;
				
				case 'translucence-uncc-minimal-light':
				case 'translucence-uncc-minimal-dark':
				case 'translucence-uncc':
					$site['options'] = get_option( 'translucence_unc_charlotte_options' );
					break;
				
				default:
					$site['status'] = false;
					$site['message'] = 'Unknown stylesheet: '.$site['stylesheet'];
					break;
				
			}
			
			if( !$site['status'] ) { $tsites[] = $site; restore_current_blog(); continue; }
			
			
			//
			// Custom css.
			//
			
			$css = Jetpack_Custom_CSS::get_css();
			$css = preg_replace('!/\*.*?\*/!s', '', $css);
			$css = preg_replace('/\n\s*\n/', "\n", $css);
			$site['css'] = $css;

			$css_options = get_option( 'sccss_settings' );
			if( !empty($css_options['sccss-content']) )
			{
				$css_options = $css_options['sccss-content'];
				$css_options = wp_kses( $css_options, array( '\'', '\"' ) );
				$css_options = str_replace( '&gt;', '>', $css_options );
				$css_options = preg_replace('!/\*.*?\*/!s', '', $css_options);
				$css_options = preg_replace('/\n\s*\n/', "\n", $css_options);
				$site['css'] .= "\n".$css_options;
			}
			
			$site['css'] = trim($site['css']);
			
			//
			// Jetpack.
			//
			
//			$site['jetpack'] = Jetpack::is_module_active('custom-css');
			
			//
			// Get theme mods.
			//
			
			$site['theme_mods'] = get_theme_mods();
			$site['sidebars_widgets'] = get_option( 'sidebars_widgets' );
			$tsites[] = $site; 
			
			restore_current_blog();
		}
				
//		apl_print($tsites);
		
		return array( 'sites' => $tsites, 'variations_count' => $variations_theme_count );
//		return $tsites;
	}
	
	public function analyze_sites()
	{
		// include jetpack css.
		if( !class_exists('Jetpack_Custom_CSS') )
		{
			require_once( ABSPATH . '/wp-content/plugins/jetpack/class.jetpack-user-agent.php' );
			require_once( ABSPATH . '/wp-content/plugins/jetpack/modules/custom-css/custom-css.php' );
		}
		
		if( !post_type_exists('safecss') )
			Jetpack_Custom_CSS::init();
		
		
		$ts = $this->get_translucence_sites();
		$all_translucence_variations = array( 'unsupported' => array() );
		
		
		foreach( $ts['sites'] as &$site )
		{
			if( $site['status'] == false )
			{
				$all_translucence_variations['unsupported'][] = $site;
				continue;
			}
			
			$site['widget_area_warnings'] = array();
			$site['css_additions'] = array();
			$site['theme_mods_additions'] = array();
			$site['vtt_options_additions'] = array();

			switch( $site['options']['background'] )
			{
				case 'uncc-white':
				case 'uncc-std02':
					$this->get_changes_for_uncc( $site );
					break;
				
				case 'uncc-min-dark':
				case 'uncc-dark-gray':
					$this->get_changes_for_dark( $site );
					break;
				
				case 'uncc-min-light':
				case 'translucence-gray-white':
				default:
					$this->get_changes_for_light( $site );
					break;
			}
			
			$this->get_menu( $site );
			$this->find_hidden_widgets( $site );

			if( !array_key_exists($site['options']['background'], $all_translucence_variations) )
				$all_translucence_variations[$site['options']['background']] = array();
			
			$all_translucence_variations[$site['options']['background']][] = $site;
		}
		
		
		return array(
			'sites'			=> $ts['sites'],
			'variations'	=> $all_translucence_variations,
			'variations-theme-count' => $ts['variations_count'],
		);
	}
	
	
	public function get_changes_for_uncc( &$site )
	{
		$this->get_theme_mods( $site, false );
	}
	
	
	public function get_changes_for_dark( &$site )
	{
		$options = $site['options'];

		if( strtolower($options['header-text-display']) != 'middle' )
		{
			switch( strtolower($options['header-text-display']) )
			{
				case 'top':
				case 'above':
					$site['vtt_options_additions']['header-text-display'] = 'hleft vtop';
					break;
				
				case 'bottom':
					$site['vtt_options_additions']['header-text-display'] = 'hleft vbottom';
					break;
				
				case 'hide':
					$site['css_additions']['title-box-display'] = 'none';
					break;

				case 'middle':
				default:
					break;
			}
		}
		
		$this->get_theme_mods( $site, true );
	}	
	
	public function get_changes_for_light( &$site )
	{
		$options = $site['options'];
		
		if( strtolower($options['background_color']) != '#ffffff' )
		{
			$site['theme_mods_additions']['background_color'] = str_replace( '#', '', $options['background_color'] );
		}

		if( strtolower($options['background_image_file']) != 'background-white.png' )
		{
			if( array_key_exists('background_image', $options) )
				$site['theme_mods_additions']['background_image'] = $options['background_image'];

			if( array_key_exists('background_position', $options) )
			{
				$position = explode(' ', $options['background_position']);
				$site['theme_mods_additions']['background_position'] = $position[0];
			}
			
			if( array_key_exists('background_attachment', $options) )
				$site['theme_mods_additions']['background_attachment'] = $options['background_attachment'];
		}
		
		$this->get_theme_mods( $site, true );
		
		if( strtolower($options['textcolor']) != '#333333' )
		{
			$site['css_additions']['textcolor'] = strtolower($options['textcolor']);
		}

		if( strtolower($options['linkcolor']) != '#003366' )
		{
			$site['css_additions']['linkcolor'] = strtolower($options['linkcolor']);
		}
		
		if( strtolower($options['title-box-visibility']) == 'none' )
		{
			$site['css_additions']['linkcolor'] = strtolower($options['title-box-visibility']);
		}

		if( strtolower($options['title-box-color']) != '#000000' ||
			floatval($options['title-box-opacity']) != 1 )
		{
			$site['css_additions']['title-box-bg-color'] = $this->get_background_color( strtolower($options['title-box-color']), floatval($options['title-box-opacity']) );
		}

		if( strtolower($options['site-title-color']) != '#ffffff' )
		{
			$site['css_additions']['site-title-color'] = strtolower($options['site-title-color']);
		}

		if( strtolower($options['description-box-visibility']) == 'none' )
		{
			$site['css_additions']['description-box-visibility'] = strtolower($options['description-box-visibility']);
		}

		if( strtolower($options['description-box-color']) != '#000000' ||
			floatval($options['description-box-opacity']) != 1 )
		{
			$site['css_additions']['description-box-bg-color'] = $this->get_background_color( strtolower($options['description-box-color']), floatval($options['description-box-opacity']) );
		}

		if( strtolower($options['site-description-color']) != '#ffffff' )
		{
			$site['css_additions']['site-description-color'] = strtolower($options['site-description-color']);
		}

		if( strtolower($options['header-text-display']) != 'middle' )
		{
			switch( strtolower($options['header-text-display']) )
			{
				case 'top':
				case 'above':
					$site['vtt_options_additions']['header-text-display'] = 'hleft vtop';
					break;
				
				case 'bottom':
					$site['vtt_options_additions']['header-text-display'] = 'hleft vbottom';
					break;
				
				case 'hide':
					$site['css_additions']['title-box-display'] = 'none';
					break;

				case 'middle':
				default:
					break;
			}
		}

		if( strtolower($options['site-border-style']) == 'none' )
		{
			$site['css_additions']['site-border-style'] = strtolower($options['site-border-style']);
		}

		if( strtolower($options['site-border-color']) != '#cccccc' )
		{
			$site['css_additions']['site-border-color'] = strtolower($options['site-border-color']);
		}

		if( strtolower($options['site-color']) != '#ffffff' ||
			floatval($options['site-opacity']) != 1 )
		{
			$site['css_additions']['site-bg-color'] = $this->get_background_color( strtolower($options['site-color']), floatval($options['site-opacity']) );
		}

		if( strtolower($options['header-border-style']) != 'none' )
		{
			$site['css_additions']['header-border-style'] = strtolower($options['header-border-style']);
		}

		if( strtolower($options['header-color']) != '#ffffff' ||
			floatval($options['header-opacity']) != 1 )
		{
			$site['css_additions']['header-bg-color'] = $this->get_background_color( strtolower($options['header-color']), floatval($options['header-opacity']) );
		}
		
		if( strtolower($options['cat-links-visibility']) == 'none' )
		{
			$site['css_additions']['cat-links-visibility'] = strtolower($options['cat-links-visibility']);
		}

		if( strtolower($options['cat-links-border-style']) == 'none' )
		{
			$site['css_additions']['cat-links-border-style'] = strtolower($options['cat-links-border-style']);
		}

		if( strtolower($options['cat-links-border-color']) != '#cccccc' )
		{
			$site['css_additions']['cat-links-border-color'] = strtolower($options['cat-links-border-color']);
		}

		if( strtolower($options['cat-links-link-color']) != '#003366' )
		{
			$site['css_additions']['cat-links-link-color'] = strtolower($options['cat-links-link-color']);
		}

		if( strtolower($options['cat-links-color']) != '#f6f6f6' ||
			floatval($options['cat-links-opacity']) != 1 )
		{
			$site['css_additions']['cat-links-bg-color'] = $this->get_background_color( strtolower($options['cat-links-color']), floatval($options['cat-links-opacity']) );
		}

		if( strtolower($options['tag-links-visibility']) == 'none' )
		{
			$site['css_additions']['tag-links-visibility'] = strtolower($options['tag-links-visibility']);
		}

		if( strtolower($options['tag-links-border-style']) == 'none' )
		{
			$site['css_additions']['tag-links-border-style'] = strtolower($options['tag-links-border-style']);
		}

		if( strtolower($options['tag-links-border-color']) != '#cccccc' )
		{
			$site['css_additions']['tag-links-border-color'] = strtolower($options['tag-links-border-color']);
		}

		if( strtolower($options['tag-links-link-color']) != '#003366' )
		{
			$site['css_additions']['tag-links-link-color'] = strtolower($options['tag-links-link-color']);
		}
		
		if( strtolower($options['tag-links-color']) != '#fffacd' ||
			floatval($options['tag-links-opacity']) != 1 )
		{
			$site['css_additions']['tag-links-bg-color'] = $this->get_background_color( strtolower($options['tag-links-color']), floatval($options['tag-links-opacity']) );
		}
	}
	
	
	private function get_background_color( $hex, $opacity )
	{
		if( $opacity == 0 ) return 'transparent';

		list( $r, $g, $b ) = $this->hex2rgb( $hex );
		
		return "$hex  /  rgba( $r, $g, $b, $opacity )";
	}

	public function hex2rgb( $hex )
	{
		$hex = str_replace( "#", "", $hex );
		
		if( strlen($hex) == 3 )
		{
			$r = hexdec( substr($hex,0,1).substr($hex,0,1) );
			$g = hexdec( substr($hex,1,1).substr($hex,1,1) );
			$b = hexdec( substr($hex,2,1).substr($hex,2,1) );
		}
		else
		{
			$r = hexdec( substr($hex,0,2) );
			$g = hexdec( substr($hex,2,2) );
			$b = hexdec( substr($hex,4,2) );
		}
		
		$rgb = array( $r, $g, $b );
		return $rgb; // returns an array with the rgb values
	}
	
	
	private function get_menu( &$site )
	{
		if( is_array($site['theme_mods']) &&
		    array_key_exists('nav_menu_locations', $site['theme_mods']) &&
		    is_array($site['theme_mods']['nav_menu_locations']) &&
		    array_key_exists('primary', $site['theme_mods']['nav_menu_locations']) )
		{
			$site['theme_mods_additions']['nav_menu_locations'] = array(
				'header-navigation' => $site['theme_mods']['nav_menu_locations']['primary']
			);
		}
	}
	
	
	private function get_theme_mods( &$site, $get_background_theme_mods = false )
	{
		$theme_mod_keys_to_copy = array(
			'header_image',
			'header_image_data',
		);
		
		if( $get_background_theme_mods )
		{
			$theme_mod_keys_to_copy = array_merge(
				$theme_mod_keys_to_copy,
				array(
					'background_color',
					'background_image',
					'background_repeat',
					'background_position_x',
					'background_attachment',
				)
			);
		}
		
		foreach( $theme_mod_keys_to_copy as $copy_key )
		{
			if( array_key_exists($copy_key, $site['theme_mods']) )
			{
				$site['theme_mods_additions'][$copy_key] = $site['theme_mods'][$copy_key];
			}
		}
	}
	
	
	private function find_hidden_widgets( &$site )
	{
		$options = $site['options'];
		$sidebars_widgets = $site['sidebars_widgets'];
		
//  		echo '<pre>';
//  		var_dump($sidebars_widgets);
//  		echo '</pre>';
		
		if( $options['right01-width'] == 0 )
		{
			if( array_key_exists('primary-widget-area', $sidebars_widgets) &&
			    !empty($sidebars_widgets['primary-widget-area']) )
			{
				$site['widget_area_warnings']['primary'] = 'The primary widget area has widgets but is not shown.';
			}
		}
		
		if( $options['right02-width'] == 0 )
		{
			if( array_key_exists('secondary-widget-area', $sidebars_widgets) &&
			    !empty($sidebars_widgets['secondary-widget-area']) )
			{
				$site['widget_area_warnings']['secondary'] = 'The secondary widget area has widgets but is not shown.';
			}
		}

		if( $options['left01-width'] == 0 )
		{
			if( array_key_exists('tertiary-widget-area', $sidebars_widgets) &&
			    !empty($sidebars_widgets['tertiary-widget-area']) )
			{
				$site['widget_area_warnings']['tertiary'] = 'The tertiary widget area has widgets but is not shown.';
			}
		}
	}
	
}
endif;

	