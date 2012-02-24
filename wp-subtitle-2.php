<?php

/*
Plugin Name: WP Subtitle 2
Plugin URI: https://github.com/benhuson/WP-Subtitle-2
Description: Add a subtitle to pages and posts. Based on the functionality of the <a href="http://www.husani.com/ventures/wordpress-plugins/wp-subtitle/">WP Subtitle</a> plugin, this plugin is completely re-coded to work with WordPress 3.0+ including support for custom post types.
Author: Ben Huson
Version: 1.1
*/

/*
Copyright 2011 Ben Huson

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class WPSubtitle2 {
	
	/**
	 * Constructor
	 */
	function WPSubtitle2() {
		add_action( 'init', array( $this, 'default_post_type_support' ), 5 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_post' ) );
	}
	
	/**
	 * Add Default Post Type Support
	 */
	function default_post_type_support() {
		add_post_type_support( 'page', 'wps_subtitle' );
		add_post_type_support( 'post', 'wps_subtitle' );
	}
	
	/**
	 * Add Meta Boxes
	 */
	function add_meta_boxes() {
		$post_types = $this->get_supported_post_types();
		foreach ( $post_types as $post_type ) {
			$meta_box_title = apply_filters( 'wps_meta_box_title', 'Subtitle' );
			add_meta_box( 'wps_subtitle_panel', __( $meta_box_title ), array( $this, 'add_subtitle_meta_box' ), $post_type, 'normal', 'high' );
		}
	}
	
	/**
	 * Add Subtitle Meta Box
	 */
	function add_subtitle_meta_box() {
		global $post;
		echo '<input type="hidden" name="wps_noncename" id="wps_noncename" value="' . wp_create_nonce( 'wp-subtitle' ) . '" />
			<input type="text" id="wpsubtitle" name="wps_subtitle" value="' . get_post_meta( $post->ID, 'wps_subtitle', true ) . '" style="width:99%;" />';
	}
	
	/**
	 * Save Subtitle
	 */
	function save_post( $post_id ) {
		// Verify if this is an auto save routine. 
		// If it is our form has not been submitted, so we dont want to do anything
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return;
			
		// Verify nonce
		if ( isset( $_POST['wps_noncename'] ) && !wp_verify_nonce( $_POST['wps_noncename'], 'wp-subtitle' ) )
			return $post_id;
		
		// Check edit capability
		$abort = true;
		$post_types = $this->get_supported_post_types();
		$post_types_obj = (array) get_post_types( array(
			'public'   => true,
			'_builtin' => false
		), 'objects' );
		if ( isset( $_POST['post_type'] ) && in_array( $_POST['post_type'], $post_types ) ) {
			if ( 'page' == $_POST['post_type'] && current_user_can( 'edit_page', $post_id ) )
				$abort = false;
			elseif ( 'post' == $_POST['post_type'] && current_user_can( 'edit_post', $post_id ) )
				$abort = false;
			elseif ( current_user_can( $post_types_obj[$_POST['post_type']]->cap->edit_post, $post_id ) )
				$abort = false;
		}
		if ( $abort )
			return $post_id;
		
		// Save data
		update_post_meta( $post_id, 'wps_subtitle', $_POST['wps_subtitle'] );
	}
	
	/**
	 * Get Supported Post Types
	 */
	function get_supported_post_types() {
		$args = array(
			'_builtin' => false
		);
		$post_types = (array) get_post_types( $args );
		$post_types[] = 'post';
		$post_types[] = 'page';
		$supported = array();
		foreach ( $post_types as $post_type ) {
			if ( post_type_supports( $post_type, 'wps_subtitle' ) )
				$supported[] = $post_type;
		}
		return $supported;
	}
	
	/**
	 * Get the Subtitle
	 */
	function get_the_subtitle( $id = 0 ) {
		global $post;
		$id = $id == 0 ? $post->ID : (int) $id;
		$subtitle = get_post_meta( $id, 'wps_subtitle', true );
		return apply_filters( 'the_subtitle', $subtitle, $id );
	}
	
}

global $WPSubtitle2;
$WPSubtitle2 = new WPSubtitle2();

/**
 * The Subtitle
 */
function the_subtitle( $before = '', $after = '', $echo = true ) {
	global $WPSubtitle2;
	$subtitle = $WPSubtitle2->get_the_subtitle();
	
	if ( strlen( $subtitle ) == 0 )
		return;

	$subtitle = $before . $subtitle . $after;

	if ( $echo )
		echo $subtitle;
	else
		return $subtitle;
}

/**
 * Get the Subtitle
 */
function get_the_subtitle( $id = 0 ) {
	global $WPSubtitle2;
	return $WPSubtitle2->get_the_subtitle( $id );
}

?>