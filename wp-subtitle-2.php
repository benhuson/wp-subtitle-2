<?php

/*
Plugin Name: WP Subtitle 2
Plugin URI: https://github.com/benhuson/WP-Subtitle-2
Description: Add a subtitle to pages and posts. Based on the functionality of the <a href="http://www.husani.com/ventures/wordpress-plugins/wp-subtitle/">WP Subtitle</a> plugin, this plugin is completely re-coded to work with WordPress 3.0+ including support for custom post types.
Author: Ben Huson
Author URI: http://www.benhuson.co.uk/
Version: 1.2
License: GPLv2 or later
*/

class WPSubtitle2 {

	var $version = '1.2';

	/**
	 * Constructor
	 */
	function WPSubtitle2() {
		add_action( 'init', array( $this, '_upgrade' ), 5 );
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
			<input type="text" id="wpsubtitle" name="wps_subtitle" value="' . $this->_get_post_meta( $post->ID ) . '" style="width:99%;" />';
		echo apply_filters( 'wps_subtitle_field_description', '' );
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
		update_post_meta( $post_id, '_wps_subtitle', $_POST['wps_subtitle'] );
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
		$subtitle = $this->_get_post_meta( $id );
		return apply_filters( 'the_subtitle', $subtitle, $id );
	}
	
	/**
	 * Get Post Meta
	 */
	function _get_post_meta( $id = 0 ) {
		global $post;
		$id = $id == 0 ? $post->ID : (int) $id;
		$subtitle = get_post_meta( $id, '_wps_subtitle', true );

		// Back-compat
		if ( empty( $subtitle ) && version_compare( $this->version, '1.2', '<' ) )
			$subtitle = get_post_meta( $id, 'wps_subtitle', true );

		return $subtitle;
	}

	/**
	 * Maybe Upgrade
	 */
	function _maybe_upgrade() {
		$current_version = get_option( 'wp_subtitle_2_version', 0 );
		if ( version_compare( $current_version, $this->version, '>=' ) )
			return false;
		return true;
	}

	/**
	 * Upgrade
	 */
	function _upgrade() {
		global $wpdb;

		// Check if upgrade required
		if ( ! $this->_maybe_upgrade() )
			return;

		$current_version = get_option( 'wp_subtitle_2_version', 0 );

		// 1.2 Upgrade
		// Change 'wps_subtitle' meta key to '_wps_subtitle'
		if ( version_compare( $current_version, '1.2', '<' ) ) {
			$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_key = %s WHERE meta_key = %s", '_wps_subtitle', 'wps_subtitle' ) );
		}

		// Log upgrade
		update_option( 'wp_subtitle_2_version', $this->version );
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
