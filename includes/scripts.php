<?php

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


/**
 * Load admin scripts
 *
 * @since       1.0.0
 * @global      array $edd_settings_page The slug for the EDD settings page
 * @global      string $post_type The type of post that we are editing
 * @return      void
 */
function edd_send_cart_admin_scripts( $hook ) {
	global $post_type;

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	if( $post_type == 'ppp_tweet' ) {
		wp_enqueue_style( 'jquery-chosen', PPP_TWEETS_URL . 'assets/css/chosen' . $suffix . '.css', array(), PPP_TWEETS_VERSION );
		wp_enqueue_script( 'jquery-chosen', PPP_TWEETS_URL . 'assets/js/chosen.jquery' . $suffix . '.js', array( 'jquery' ), PPP_TWEETS_VERSION );
		wp_enqueue_script( 'ppp-tweets-admin', PPP_TWEETS_URL . 'assets/js/admin-scripts' . $suffix . '.js', array( 'jquery' ), PPP_TWEETS_VERSION, true );
		wp_enqueue_style( 'ppp-tweets-admin-styles', PPP_TWEETS_URL . 'assets/css/admin-styles' . $suffix . '.css' );
	}
}
add_action( 'admin_enqueue_scripts', 'edd_send_cart_admin_scripts', 100 );


