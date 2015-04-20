<?php
/*
Plugin Name: Translucence Transition Network Convert
Plugin URI: 
Description: 
Version: 0.0.1
Author: Crystal Barton
Author URI: http://www.crystalbarton.com
*/


//add_action( 'pre_update_option_template', array('TT_Transition', 'theme_switch') );
add_action( 'update_option_template', array('TT_Transition', 'theme_switch'), 10, 2 );
add_action( 'after_switch_theme', array('TT_Transition', 'after_switch_theme') );


if( !class_exists('TT_Transition') ):
class TT_Transition
{
	
	public static function theme_switch( $old_template, $new_template )
	{	
 		update_option( 'tt_theme_switch', '0' );
		
		$switch = get_option( 'tt_theme_switch_options' );
		if( !$switch ) $switch = '';
		$switch .= "   |   ".time().'  -  '.$old_template.'  -  '.$new_template;
		update_option( 'tt_theme_switch_options', $switch );
		
		if( $new_template !== 'variations-template-theme' ) return;
		if( $old_template !== '2010-translucence' ) return;
		
 		update_option( 'tt_theme_switch', '1' );
		
		update_option( 'tt_previous_theme', get_option('stylesheet') );
		
 		update_option( 'tt_theme_switch', '2' );
	}
	
	
	public static function after_switch_theme()
	{
		update_option( 'tt_after_switch_theme', '0' );
		
		if( get_option('template') !== 'variations-template-theme' ) return;
		if( ($previous_theme = get_option('tt_previous_theme')) === false ) return;
		
		update_option( 'tt_after_switch_theme', '1' );
		
		$theme_mods = get_option( 'theme_mods_'.$previous_theme );
		if( !$theme_mods ) return;
		
		update_option( 'tt_after_switch_theme', '2' );
		
		$stylesheet = get_option( 'stylesheet' );
		
 		echo '<pre>'; var_dump($theme_mods); echo '</pre>';
		$sidebars_widgets = $theme_mods['sidebars_widgets'];
		$widget_areas = $sidebars_widgets['data'];
		
		update_option( 'tt_after_switch_theme', '3' );
		
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
			unset( $widget_areas['primary-widget-area'] );
		}
		
		if( array_key_exists('secondary-widget-area', $widget_areas) )
		{
			$right = array_merge(
				$right,
				$widget_areas['secondary-widget-area']
			);
			unset( $widget_areas['secondary-widget-area'] );
		}
		
		if( array_key_exists('tertiary-widget-area', $widget_areas) )
		{
			$left = $widget_areas['tertiary-widget-area'];
			unset( $widget_areas['tertiary-widget-area'] );
		}

		if( array_key_exists('first-footer-widget-area', $widget_areas) )
		{
			$footer1 = $widget_areas['first-footer-widget-area'];
			unset( $widget_areas['first-footer-widget-area'] );
		}

		if( array_key_exists('second-footer-widget-area', $widget_areas) )
		{
			$footer2 = $widget_areas['second-footer-widget-area'];
			unset( $widget_areas['second-footer-widget-area'] );
		}

		if( array_key_exists('third-footer-widget-area', $widget_areas) )
		{
			$footer3 = $widget_areas['third-footer-widget-area'];
			unset( $widget_areas['third-footer-widget-area'] );
		}

		if( array_key_exists('fourth-footer-widget-area', $widget_areas) )
		{
			$footer4 = $widget_areas['fourth-footer-widget-area'];
			unset( $widget_areas['fourth-footer-widget-area'] );
		}
		
		$widget_areas['vtt-left-sidebar'] = $left;
		$widget_areas['vtt-right-sidebar'] = $right;
		$widget_areas['vtt-footer-1'] = $footer1;
		$widget_areas['vtt-footer-2'] = $footer2;
		$widget_areas['vtt-footer-3'] = $footer3;
		$widget_areas['vtt-footer-4'] = $footer4;
		
		update_option( 'tt_after_switch_theme', '4' );
		
		//
		// update sidebar widgets and delete 2010-transluence sidebar widgets.
		//
		
		$sidebars_widgets['data'] = $widget_areas;
 		echo '<pre>'; var_dump($sidebars_widgets); echo '</pre>';
		update_option( 'sidebars_widgets', $sidebars_widgets );
		
		//
		// create new css based on old translucence settings.
		//
		
		update_option( 'tt_after_switch_theme', '5' );
		
		$options = get_option( 'translucence_unc_charlotte_options', false );
		if( !$options ) return;

		update_option( 'tt_after_switch_theme', '6' );
		
		$theme_mods = get_option( 'theme_mods_'.$stylesheet );
		if( !$theme_mods )
		{
			$theme_mods = array( false );
			$theme_mods['vtt-variation'] = 'default';
		}
		
		update_option( 'tt_after_switch_theme', '7' );
		
		$vtt_options = get_option( 'vtt-options', array() );
		
		update_option( 'tt_after_switch_theme', '8' );
		
		// include jetpack css.
		if( !class_exists('Jetpack_Custom_CSS') )
			require_once( ABSPATH . '/wp-content/plugins/jetpack/modules/custom-css/custom-css.php' );
		
		update_option( 'tt_after_switch_theme', '9' );
		
		if( !post_type_exists('safecss') )
			Jetpack_Custom_CSS::init();
		
		update_option( 'tt_after_switch_theme', '10' );
		
		$css = '';

		if( strtolower($options['background_color']) != '#ffffff' )
		{
			$theme_mods['background_color'] = $options['background_color'];
		}

		if( strtolower($options['background_image_file']) != 'background-white.png' )
		{
			$theme_mods['background_image'] = $options['background_image'];
			
			$position = explode($options['background_position']);
			$theme_mods['background_position'] = $position[0];
			
			$theme_mods['background_attachment'] = $options['background_attachment'];
		}
		
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
			' #header #title-box .name {
				display:none;
			} ';
		}

		if( strtolower($options['title-box-color']) != '#000000' ||
			floatval($options['title-box-opacity']) != 1 )
		{
			$css .= 
			' #header #title-box .name { '.
				TT_Transition::background_color( strtolower($options['title-box-color']), floatval($options['title-box-opacity']) ).
			' } ';
		}

		if( strtolower($options['site-title-color']) != '#ffffff' )
		{
			$css .= 
			' #header #title-box .name {
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
			' #header #title-box .description {
				display:none;
			} ';
		}

		if( strtolower($options['description-box-color']) != '#000000' ||
			floatval($options['description-box-opacity']) != 1 )
		{
			$css .= 
			' #header #title-box .description { '.
				TT_Transition::background_color( strtolower($options['description-box-color']), floatval($options['description-box-opacity']) ).
			' } ';
		}

		if( strtolower($options['site-description-color']) != '#ffffff' )
		{
			$css .= 
			' #header #title-box .description {
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
				case 'top':
				case 'above':
					$vtt_options['header']['title-position'] = 'hleft vtop';
					break;
				
				case 'bottom':
					$vtt_options['header']['title-position'] = 'hleft vbottom';
					break;
				
				case 'hide':
					$css .= 
					' #header #title-box {
						display:none;
					} ';
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
			' #site-inside-wrapper { '.
				TT_Transition::background_color( strtolower($options['site-color']), floatval($options['site-opacity']) ).
			' } ';
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
			' #header { '.
				TT_Transition::background_color( strtolower($options['header-color']), floatval($options['header-opacity']) ).
			' } ';
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
			' .taxonomy-list.category-list > a { '.
				TT_Transition::background_color( strtolower($options['cat-links-color']), floatval($options['cat-links-opacity']) ).
			' } ';
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
			' .taxonomy-list.post_tag-list > a { '.
				TT_Transition::background_color( strtolower($options['tag-links-color']), floatval($options['tag-links-opacity']) ).
			' } ';
		}
		
		//
		// update database with new css and theme mods.
		//
		
		update_option( 'tt_after_switch_theme', '11' );
		
		update_option( 'tt_vtt-options', $vtt_options );
		update_option( 'tt_theme_mods_'.$stylesheet, $theme_mods );
		update_option( 'tt_css', $css );
		
		update_option( 'tt_after_switch_theme', '12' );
		
		update_option( 'vtt-options', $vtt_options );
		
		update_option( 'tt_after_switch_theme', '13' );
		
		update_option( 'theme_mods_'.$stylesheet, $theme_mods );
		
		update_option( 'tt_after_switch_theme', '14' );
		
		Jetpack_Custom_CSS::save( array( 'css' => $css ) );
		
		update_option( 'tt_after_switch_theme', '15' );
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
			return "background-color: transparent";
		}

		list( $r, $g, $b ) = TT_Transition::hex2rgb( $hex );
		
		return "
			background-color: $hex;
			background-color: rgba( $r, $g, $b, $opacity );
		";
	}
	
}
endif;