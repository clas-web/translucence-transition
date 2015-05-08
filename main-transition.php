<?php
/*
Plugin Name: Translucence Transition - Theme Transition
Plugin URI: 
Description: 
Version: 0.0.1
Author: Crystal Barton
Author URI: http://www.crystalbarton.com
*/


if( !defined('TTTT') ):

define( 'TTTT', 'Translucence Transition - Theme Transition' );

define( 'TTTT_DEBUG', true );

define( 'TTTT_PLUGIN_PATH', dirname(__FILE__) );
define( 'TTTT_PLUGIN_URL', plugins_url('', __FILE__) );

define( 'TTTT_VERSION', '0.0.1' );
define( 'TTTT_VERSION_OPTION', 'tt-version' );

endif;


add_action( 'update_option_template', array('TTTT_Main', 'theme_switch'), 10, 2 );
add_action( 'after_switch_theme', array('TTTT_Main', 'after_switch_theme'), 1 );


if( !class_exists('TTTT_Main') ):
class TTTT_Main
{
	
	public static function theme_switch( $old_template, $new_template )
	{	
		if( $new_template !== 'variations-template-theme' ) return;
		if( $old_template !== '2010-translucence' ) return;
		
		update_option( 'tt_transition_complete', '0' );
		update_option( 'tt_previous_theme', get_option('stylesheet') );
	}
	
	
	public static function after_switch_theme()
	{
		//
		// 
		//
		
		if( get_option('template') !== 'variations-template-theme' ) return;
		if( ($previous_theme = get_option('tt_previous_theme')) === false )
		{
			update_option( 'tt_transition_complete', '1' );
			return;
		}
		
		$transition_complete = get_option('tt_transition_complete', '1');
		if( $transition_complete === '1' ) return;
		
		$allowed_themes = array(
			'translucence-uncc-minimal-light',
			'translucence-uncc-minimal-dark',
			'translucence-uncc',
		);
		
		if( !in_array($previous_theme, $allowed_themes) ) return;
		
		
		//
		// 
		//
		
		$previous_theme_mods = get_option( 'theme_mods_'.$previous_theme );
		if( !$previous_theme_mods ) return;

		$stylesheet = get_option( 'stylesheet' );
		$theme_mods = get_option( 'theme_mods_'.$stylesheet );
		if( !$theme_mods )
		{
			$theme_mods = array( false );
		}

		$vtt_options = get_option( 'vtt-options', array() );
		
		//
		//
		//
		
		TTTT_Main::convert_widgets( $previous_theme_mods, $theme_mods );
		
		//
		//
		//
		
		$css = Jetpack_Custom_CSS::get_css();
		if( !$css ) $css = '';
		
		
		$css_options = get_option( 'sccss_settings', array() );
		if( !empty($css_options['sccss-content']) )
		{
			$css_options = isset( $css_options['sccss-content'] ) ? $css_options['sccss-content'] : '';
			$css_options = wp_kses( $css_options, array( '\'', '\"' ) );
			$css_options = str_replace( '&gt;', '>', $css_options );
			$css_options = preg_replace('!/\*.*?\*/!s', '', $css_options);
			$css_options = preg_replace('/\n\s*\n/', "\n", $css_options);
			$css .= "\n\n".$css_options;
		}
		$css .= "\n\n";
		
		//
		// create new css based on old translucence settings.
		//
		
		//
		// change variation.
		//
		switch( $previous_theme )
		{
			case '2010-translucence':
				$options = get_option( '2010_translucence_options' );
				break;
			
			case 'translucence-uncc':
			case 'translucence-uncc-minimal-dark':
			case 'translucence-uncc-minimal-light':
				$options = get_option( 'translucence_unc_charlotte_options' );
				break;
			
			default:
				$theme_mods['vtt-variation'] = 'default';
				break;
		}
		
		if( $options )
		{
			switch( $options['background'] )
			{
				case 'uncc-white':
					$theme_mods['vtt-variation'] = 'uncc-light';
					break;
					
				case 'uncc-std02':
					$theme_mods['vtt-variation'] = 'uncc';
					break;
			
				case 'uncc-min-dark':
				case 'uncc-dark-gray':
					$theme_mods['vtt-variation'] = 'dark';
					break;
			
				case 'uncc-min-light':
				default:
					$theme_mods['vtt-variation'] = 'default';
					break;
			}

			switch( $options['background'] )
			{
				case 'uncc-white':
				case 'uncc-std02':
					TTTT_Main::get_uncc_changes( $css, $previous_theme_mods, $theme_mods, $vtt_options );
					break;
				
				case 'uncc-min-dark':
				case 'uncc-dark-gray':
					TTTT_Main::get_dark_changes( $css, $previous_theme_mods, $theme_mods, $vtt_options );
					break;
				
				case 'uncc-min-light':
					TTTT_Main::get_light_changes( $css, $previous_theme_mods, $theme_mods, $vtt_options );
					break;
				
				default:
					break;
			}
		}
		
		$css = str_replace( '>', '&gt;', $css );
		$css = preg_replace( '/\t+/', "\t", $css );
		$css = preg_replace( '/  +/', ' ', $css );
		$css = preg_replace( '/\}/', "}\n", $css );
		$css = preg_replace( '/([ \t]+)\}/', '}', $css );
		$css = preg_replace( '/,\n(\s+)/', ",\n", $css );
		$css = preg_replace( '/\n( +)/', "\n", $css );

		
		//
		// update database with new css and theme mods.
		//
		
		update_option( 'vtt-variation', $theme_mods['vtt-variation'] );
		update_option( 'vtt-options', $vtt_options );
		update_option( 'theme_mods_'.$stylesheet, $theme_mods );
		
		$css_options = get_option( 'sccss_settings', array() );
		$css_options['sccss-content'] = $css;
		update_option( 'sccss_settings', $css_options );
		
//		apl_print($css_options);

//		Jetpack_Custom_CSS::save( array( 'css' => $css ) );
		update_option( 'tt_transition_complete', '1' );
	}
	

	public static function convert_widgets( $previous_theme_mods, &$theme_mods )
	{
		$sidebars_widgets = $previous_theme_mods['sidebars_widgets'];
		$widget_areas = $sidebars_widgets['data'];
		
		//
		// create a new sidebars_widgets array.
		//
		
		$left = array();
		$right = array();
		$footer1 = array();
		$footer2 = array();
		$footer3 = array();
		$footer4 = array();
		
		if( array_key_exists('primary-widget-area', $widget_areas) )
		{
			$right = $widget_areas['primary-widget-area'];
		}
		
		if( array_key_exists('secondary-widget-area', $widget_areas) )
		{
			$right = array_merge(
				$right,
				$widget_areas['secondary-widget-area']
			);
		}
		
		if( array_key_exists('tertiary-widget-area', $widget_areas) )
		{
			$left = $widget_areas['tertiary-widget-area'];
		}

		if( array_key_exists('first-footer-widget-area', $widget_areas) )
		{
			$footer1 = $widget_areas['first-footer-widget-area'];
		}

		if( array_key_exists('second-footer-widget-area', $widget_areas) )
		{
			$footer2 = $widget_areas['second-footer-widget-area'];
		}

		if( array_key_exists('third-footer-widget-area', $widget_areas) )
		{
			$footer3 = $widget_areas['third-footer-widget-area'];
		}

		if( array_key_exists('fourth-footer-widget-area', $widget_areas) )
		{
			$footer4 = $widget_areas['fourth-footer-widget-area'];
		}
		
		$new_widget_areas = array(
			'vtt-left-sidebar' 		=> $left,
			'vtt-right-sidebar'		=> $right,
			'vtt-footer-1' 			=> $footer1,
			'vtt-footer-2' 			=> $footer2,
			'vtt-footer-3' 			=> $footer3,
			'vtt-footer-4' 			=> $footer4,
		);
		
		
// 		echo '<pre>';
// 		var_dump($widget_areas);
// 		var_dump($new_widget_areas);
// 		echo '</pre>';
		
		//
		// update sidebar widgets.
		//
		
		$sidebars_widgets['data'] = $new_widget_areas;
		$theme_mods['sidebars_widgets'] = $sidebars_widgets;	
	}
	
	
	public static function get_uncc_changes( &$css, &$previous_theme_mods, &$theme_mods, &$vtt_options )
	{
		$theme_mod_keys_to_copy = array(
			'header_image',
			'header_image_data',
		);
		
		foreach( $theme_mod_keys_to_copy as $copy_key )
		{
			if( array_key_exists($copy_key, $previous_theme_mods) )
			{
				$theme_mods[$copy_key] = $previous_theme_mods[$copy_key];
			}
		}

		if( is_array($previous_theme_mods) &&
		    array_key_exists('nav_menu_locations', $previous_theme_mods) &&
		    is_array($previous_theme_mods['nav_menu_locations']) &&
		    array_key_exists('primary', $previous_theme_mods['nav_menu_locations']) )
		{
			$theme_mods['nav_menu_locations'] = array(
				'header-navigation'	=> $previous_theme_mods['nav_menu_locations']['primary'],
			);
		}		
		
	}


	public static function get_dark_changes( &$css, &$previous_theme_mods, &$theme_mods, &$vtt_options )
	{
		$options = get_option( 'translucence_unc_charlotte_options', false );
		if( !$options ) return;

		$theme_mod_keys_to_copy = array(
			'background_color',
			'header_image',
			'header_image_data',
			'background_image',
			'background_repeat',
			'background_position_x',
			'background_attachment',
		);
		
		foreach( $theme_mod_keys_to_copy as $copy_key )
		{
			if( array_key_exists($copy_key, $previous_theme_mods) )
			{
				$theme_mods[$copy_key] = $previous_theme_mods[$copy_key];
			}
		}
		
		if( is_array($previous_theme_mods) &&
		    array_key_exists('nav_menu_locations', $previous_theme_mods) &&
		    is_array($previous_theme_mods['nav_menu_locations']) &&
		    array_key_exists('primary', $previous_theme_mods['nav_menu_locations']) )
		{
			$theme_mods['nav_menu_locations'] = array(
				'header-navigation'	=> $previous_theme_mods['nav_menu_locations']['primary'],
			);
		}		
	}	
	
	public static function get_light_changes( &$css, &$previous_theme_mods, &$theme_mods, &$vtt_options )
	{
		$options = get_option( 'translucence_unc_charlotte_options', false );
		if( !$options ) return;
		
		if( strtolower($options['background_color']) != '#ffffff' )
		{
			$theme_mods['background_color'] = str_replace( '#', '', $options['background_color'] );
		}

		if( strtolower($options['background_image_file']) != 'background-white.png' )
		{
			if( array_key_exists('background_image', $options) )
				$theme_mods['background_image'] = $options['background_image'];
			
			if( array_key_exists('background_position', $options) )
			{
				$position = explode(' ', $options['background_position']);
				$theme_mods['background_position'] = $position[0];
			}
			
			if( array_key_exists('background_attachment', $options) )
				$theme_mods['background_attachment'] = $options['background_attachment'];
		}
		
		//
		// 
		//
		
		$theme_mod_keys_to_copy = array(
			'background_color',
			'header_image',
			'header_image_data',
			'background_image',
			'background_repeat',
			'background_position_x',
			'background_attachment',
		);
		
		foreach( $theme_mod_keys_to_copy as $copy_key )
		{
			if( array_key_exists($copy_key, $previous_theme_mods) )
			{
				$theme_mods[$copy_key] = $previous_theme_mods[$copy_key];
			}
		}
		
		// 
		// 
		// 
		
		if( strtolower($options['textcolor']) != '#333333' )
		{
			$css .= 
			' #content .entry-content,
			  #content .excerpt,
			  #content .description,
			  #content .entry-meta,
			  .widget {
				color:'.strtolower($options['textcolor']).';
			} ';
		}

		if( strtolower($options['linkcolor']) != '#003366' )
		{
			$css .= 
			' a {
				color:'.strtolower($options['linkcolor']).';
			} ';
		}

/*
	s:20:"title-box-visibility";
		s:5:"block";
	s:15:"title-box-color";
		s:7:"#4a6339";
	s:17:"title-box-opacity";
		s:2:".7";
	s:19:"title-box-color-rgb";
		s:20:"rgba(74, 99, 57, .7)";
	s:22:"title-box-border-color";
		s:7:"#666666";	
	s:20:"title-box-link-color";
		s:7:"#FFFFFF";
*/
		
		if( strtolower($options['title-box-visibility']) == 'none' )
		{
			$css .= 
			' #title-box .name {
				display:none;
			} ';
		}

		if( strtolower($options['title-box-color']) != '#000000' ||
			floatval($options['title-box-opacity']) != 1 )
		{
			$css .= 
			' #title-box .name { 
				'.
				TTTT_Main::background_color( strtolower($options['title-box-color']), floatval($options['title-box-opacity']) ).
				'
			} ';
		}

		if( strtolower($options['site-title-color']) != '#ffffff' )
		{
			$css .= 
			' #title-box .name {
				color:'.strtolower($options['site-title-color']).';
			} ';
		}

			
/*
	s:26:"description-box-visibility";
		s:5:"block";
	s:21:"description-box-color";
		s:7:"#4a6339";
	s:23:"description-box-opacity";
		s:2:".6";
	s:25:"description-box-color-rgb";
		s:20:"rgba(74, 99, 57, .6)";
	s:28:"description-box-border-color";
		s:7:"#666666";
	s:26:"description-box-link-color";
		s:7:"#003366";
*/
		
		if( strtolower($options['description-box-visibility']) == 'none' )
		{
			$css .= 
			' #title-box .description {
				display:none;
			} ';
		}

		if( strtolower($options['description-box-color']) != '#000000' ||
			floatval($options['description-box-opacity']) != 1 )
		{
			$css .= 
			' #title-box .description { 
				'.
				TTTT_Main::background_color( strtolower($options['description-box-color']), floatval($options['description-box-opacity']) ).
				'
			} ';
		}

		if( strtolower($options['site-description-color']) != '#ffffff' )
		{
			$css .= 
			' #title-box .description {
				color:'.strtolower($options['site-description-color']).';
			} ';
		}


/*
	s:19:"header-text-display";
		s:3:"top";
*/

		if( strtolower($options['header-text-display']) != 'middle' )
		{
			if( !array_key_exists('header', $vtt_options) )
				$vtt_options['header'] = array();
			
			switch( strtolower($options['header-text-display']) )
			{
				case 'above':
					$vtt_options['header']['title-position'] = 'hleft vabove';
					break;
				
				case 'top':
					$vtt_options['header']['title-position'] = 'hleft vtop';
				
				case 'bottom':
					$vtt_options['header']['title-position'] = 'hleft vbottom';
					break;
				
				case 'hide':
					$vtt_options['header']['title-hide'] = true;
					break;

				case 'middle':
				default:
					break;
			}
		}

/*
	s:17:"site-border-style";
		s:5:"solid";
	s:17:"site-border-color";
		s:7:"#CCCCCC";
	s:10:"site-color";
		s:7:"#FFFFFF";
	s:12:"site-opacity";
		s:1:"1";
*/

		if( strtolower($options['site-border-style']) == 'none' )
		{
			$css .= 
			' #site-inside-wrapper {
				border:none;
				box-shadow:0 0 0 transparent;
			} ';
		}

		if( strtolower($options['site-border-color']) != '#cccccc' )
		{
			$css .= 
			' #site-inside-wrapper{
				border-color:'.strtolower($options['site-border-color']).';
			} ';
		}

		if( strtolower($options['site-color']) != '#ffffff' ||
			floatval($options['site-opacity']) != 1 )
		{
			$css .= 
			' #site-inside-wrapper {
				'.
				TTTT_Main::background_color( strtolower($options['site-color']), floatval($options['site-opacity']) ).
				'
			} ';
		}


/*
	s:19:"header-border-style";
		s:4:"none";
	s:19:"header-border-color";
		s:7:"#CCCCCC";
	s:12:"header-color";
		s:7:"#F9F9F9";
	s:16:"header-color-rgb";
		s:23:"rgba(249, 249, 249, .0)";
	s:14:"header-opacity";
		s:2:".0";
*/

		if( strtolower($options['header-border-style']) != 'none' )
		{
			$css .= 
			' #header {
				border:'.strtolower($options['header-border-style']).' 1px '.strtolower($options['header-border-color']).';
			} ';
		}

		if( strtolower($options['header-color']) != '#ffffff' ||
			floatval($options['header-opacity']) != 1 )
		{
			$css .= 
			' #header { 
				'.
				TTTT_Main::background_color( strtolower($options['header-color']), floatval($options['header-opacity']) ).
				'
			} ';
		}
		
/*
	s:20:"cat-links-visibility";
		s:5:"block";
	s:22:"cat-links-border-style";
		s:5:"solid";
	s:22:"cat-links-border-color";
		s:7:"#666666";
	s:20:"cat-links-link-color";
		s:7:"#003366";
	s:15:"cat-links-color";
		s:7:"#e9e9c9";
	s:19:"cat-links-color-rgb";
		s:24:"rgba(233, 233, 201, 0.7)";		
	s:17:"cat-links-opacity";
		d:0.6999999999999999555910790149937383830547332763671875;
*/

		if( strtolower($options['cat-links-visibility']) == 'none' )
		{
			$css .= 
			' .taxonomy-list.category-list {
				display:none;
			} ';
		}

		if( strtolower($options['cat-links-border-style']) == 'none' )
		{
			$css .= 
			' .taxonomy-list.category-list > a {
				border:0;
			} ';
		}

		if( strtolower($options['cat-links-border-color']) != '#cccccc' )
		{
			$css .= 
			' .taxonomy-list.category-list > a {
				border-color:'.strtolower($options['cat-links-border-color']).';
			} ';
		}

		if( strtolower($options['cat-links-link-color']) != '#003366' )
		{
			$css .= 
			' .taxonomy-list.category-list > a {
				color:'.strtolower($options['cat-links-link-color']).';
			} ';
		}

		if( strtolower($options['cat-links-color']) != '#f6f6f6' ||
			floatval($options['cat-links-opacity']) != 1 )
		{
			$css .= 
			' .taxonomy-list.category-list > a { 
				'.
				TTTT_Main::background_color( strtolower($options['cat-links-color']), floatval($options['cat-links-opacity']) ).
				'
			} ';
		}

/*
	s:20:"tag-links-visibility";
		s:5:"block";
	s:22:"tag-links-border-style";
		s:5:"solid";
	s:22:"tag-links-border-color";
		s:7:"#CCCCCC";
	s:20:"tag-links-link-color";
		s:7:"#003366";
	s:19:"tag-links-color-rgb";
		s:24:"rgba(255, 248, 198, 0.7)";
	s:15:"tag-links-color";
		s:7:"#FFF8C6";		
*/

		if( strtolower($options['tag-links-visibility']) == 'none' )
		{
			$css .= 
			' .taxonomy-list.post_tag-list {
				display:none;
			} ';
		}

		if( strtolower($options['tag-links-border-style']) == 'none' )
		{
			$css .= 
			' .taxonomy-list.post_tag-list > a {
				border:0;
			} ';
		}

		if( strtolower($options['tag-links-border-color']) != '#cccccc' )
		{
			$css .= 
			' .taxonomy-list.post_tag-list > a {
				border-color:'.strtolower($options['tag-links-border-color']).';
			} ';
		}

		if( strtolower($options['tag-links-link-color']) != '#003366' )
		{
			$css .= 
			' .taxonomy-list.post_tag-list > a {
				color:'.strtolower($options['tag-links-link-color']).';
			} ';
		}
		
		if( strtolower($options['tag-links-color']) != '#fffacd' ||
			floatval($options['tag-links-opacity']) != 1 )
		{
			$css .= 
			' .taxonomy-list.post_tag-list > a { 
				'.
				TTTT_Main::background_color( strtolower($options['tag-links-color']), floatval($options['tag-links-opacity']) ).
				'
			} ';
		}
		
		
		if( is_array($previous_theme_mods) &&
		    array_key_exists('nav_menu_locations', $previous_theme_mods) &&
		    is_array($previous_theme_mods['nav_menu_locations']) &&
		    array_key_exists('primary', $previous_theme_mods['nav_menu_locations']) )
		{
			$theme_mods['nav_menu_locations'] = array(
				'header-navigation'	=> $previous_theme_mods['nav_menu_locations']['primary'],
			);
		}		
	}


	public static function hex2rgb( $hex )
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
	
	
	public static function background_color( $hex, $opacity )
	{
		if( $opacity == 0 )
		{
			return 'background-color: transparent';
		}

		list( $r, $g, $b ) = TTTT_Main::hex2rgb( $hex );
		
		return "background-color: $hex;
				background-color: rgba( $r, $g, $b, $opacity );";
	}
	
	
}
endif;