<?php
/*
Plugin Name: Post Promoter Pro - Tweets
Plugin URI: https://www.postpromoterpro.com
Description: Schedule your Twitter
Version: 1.0
Author: Chris Klosowski
Author URI: http://www.kungfugrep.com
License: GPLv2 or later
*/

class PPP_Tweets {
	private static $instance;

	public static function getInstance() {
		if( !self::$instance ) {
			self::$instance = new PPP_Tweets();
			if ( self::$instance->verify_config() ) {
				self::$instance->setup_constants();
				self::$instance->includes();
				self::$instance->load_textdomain();
				self::$instance->hooks();
			}
		}

		return self::$instance;
	}

	/**
	 * Verify conditions are set for General Sharing to work correctly
	 *
	 * @access private
	 * @since  1.0.0
	 * @return bool If Send Cart can work with the current site configuration
	 */
	private function verify_config() {
		$config_valid = true;

		if ( ! defined( 'PPP_PATH' ) || ! function_exists( 'ppp_twitter_enabled' ) || ! ppp_twitter_enabled() ) {
			add_action( 'admin_notices', array( $this, 'ppp_not_present' ) );
			$config_valid = false;
		}

		return $config_valid;
	}

	/**
	 * Display a notice when PPP isn't active
	 * @return void
	 */
	public function ppp_not_present() {
		echo '<div class="error"><p>' . __( 'Post Promoter Pro - Tweets requires Post Promoter Pro. Please activate it and enable Twitter.', 'ppp-gs-text' ) . '</p></div>';
	}

	/**
	 * Setup plugin constants
	 *
	 * @access      private
	 * @since       1.0.0
	 * @return      void
	 */
	private function setup_constants() {
		// Plugin version
		define( 'PPP_TWEETS_VERSION', '1.0.0' );

		// Plugin path
		define( 'PPP_TWEETS_DIR', plugin_dir_path( __FILE__ ) );

		// Plugin URL
		define( 'PPP_TWEETS_URL', plugin_dir_url( __FILE__ ) );
	}

	/**
	 * Include necessary files
	 *
	 * @access      private
	 * @since       1.0.0
	 * @return      void
	 */
	private function includes() {
		// Include scripts
		require_once PPP_TWEETS_DIR . 'includes/post-type.php';
		require_once PPP_TWEETS_DIR . 'includes/scripts.php';
		require_once PPP_TWEETS_DIR . 'includes/functions.php';
		//require_once PPP_TWEETS_DIR . 'includes/actions.php';
		//require_once PPP_TWEETS_DIR . 'includes/filters.php';
	}

	/**
	 * Internationalization
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      void
	 */
	public function load_textdomain() {
		// Set filter for language directory
		$lang_dir = PPP_TWEETS_DIR . '/languages/';
		$lang_dir = apply_filters( 'ppp_tweets_langs_directory', $lang_dir );

		// Traditional WordPress plugin locale filter
		$locale = apply_filters( 'ppp_tweets_locale', get_locale(), 'ppp-tweets-txt' );
		$mofile = sprintf( '%1$s-%2$s.mo', 'ppp-tweets', $locale );

		// Setup paths to current locale file
		$mofile_local   = $lang_dir . $mofile;
		$mofile_global  = WP_LANG_DIR . '/ppp-tweets/' . $mofile;

		if( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/ppp-tweets/ folder
			load_textdomain( 'ppp-tweets-txt', $mofile_global );
		} elseif( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/ppp-tweets/languages/ folder
			load_textdomain( 'ppp-tweets-txt', $mofile_local );
		} else {
			// Load the default language files
			load_plugin_textdomain( 'ppp-tweets-txt', false, $lang_dir );
		}
	}

	/**
	 * Run action and filter hooks
	 *
	 * @access      private
	 * @since       1.0.0
	 * @return      void
	 *
	 */
	private function hooks() {

		// Flush rule son activation for the custom post type
		register_activation_hook( __FILE__, array( 'PPP_Tweets', 'activation' ) );

	}

	public static function activation() {

		flush_rewrite_rules();

	}

}

function ppp_load_tweets() {
	$PPP_Tweets = PPP_Tweets::getInstance();
}
add_action( 'plugins_loaded', 'ppp_load_tweets', 99 );
