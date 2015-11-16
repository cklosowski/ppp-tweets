<?php

if( !defined( 'ABSPATH' ) ) exit;

/**
 * Registers and sets up the ppp_tweet custom post type
 *
 * @since 1.0
 * @return void
 */
function ppp_tweets_post_type() {

	$tweets_labels =  apply_filters( 'ppp_tweets_labels', array(
			'name'               => 'Tweets',
			'singular_name'      => 'Tweet',
			'add_new'            => __( 'Add Tweet', 'ppp-tweets-txt' ),
			'add_new_item'       => __( 'Add Tweet', 'ppp-tweets-txt' ),
			'edit_item'          => __( 'Edit Tweet', 'ppp-tweets-txt' ),
			'new_item'           => __( 'New Tweet', 'ppp-tweets-txt' ),
			'all_items'          => __( 'All Tweets', 'ppp-tweets-txt' ),
			'view_item'          => __( 'View Tweet', 'ppp-tweets-txt' ),
			'search_items'       => __( 'Search Tweets', 'ppp-tweets-txt' ),
			'not_found'          => __( 'No Tweets found', 'ppp-tweets-txt' ),
			'not_found_in_trash' => __( 'No Tweets found in Trash', 'ppp-tweets-txt' ),
			'parent_item_colon'  => '',
			'menu_name'          => __( 'Tweets', 'ppp-tweets-txt' )
		) );

	$tweets_args = array(
		'labels'              => $tweets_labels,
		'public'              => false,
		'publicly_queryable'  => false,
		'show_ui'             => true,
		'menu_position'       => 20,
		'menu_icon'           => 'dashicons-twitter',
		'show_in_menu'        => true,
		'query_var'           => true,
		'map_meta_cap'        => true,
		'has_archive'         => false,
		'hierarchical'        => false,
		'exclude_from_search' => false,
		'supports'            => apply_filters( 'ppp_tweets_supports', array( 'title', 'thumbnail' ) )
	);
	register_post_type( 'ppp_tweet', apply_filters( 'ppp_tweets_post_args', $tweets_args  ) );

}
add_action( 'init', 'ppp_tweets_post_type' );
