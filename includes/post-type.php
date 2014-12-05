<?php

if( !defined( 'ABSPATH' ) ) exit;

/**
 * Registers and sets up the Share custom post type
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
		'exclude_from_search' => true,
		'supports'            => apply_filters( 'ppp_tweets_supports', array( 'title', 'thumbnail' ) )
	);
	register_post_type( 'ppp_tweet', apply_filters( 'ppp_tweets_post_args', $tweets_args  ) );

}
add_action( 'init', 'ppp_tweets_post_type' );


function ppp_tweets_title_placeholder_text( $input ) {

	global $post_type;
	if( 'ppp_tweet' == $post_type && is_admin() && 'Enter title here' == $input ) {
		return __( 'Enter Tweet text', 'ppp-tweets-txt' );
	}

	if( 'ppp_tweet' == $post_type && is_admin() && 'Set featured image' == $input ) {
		return __( 'Attach an image to this Tweet', 'ppp-tweets-txt' );
	}

	return $input;
}

add_filter( 'gettext','ppp_tweets_title_placeholder_text' );

function ppp_tweets_change_image_box() {
	remove_meta_box( 'postimagediv', 'ppp_tweet', 'side' );
	add_meta_box( 'postimagediv', __( 'Add Media', 'ppp-tweets-txt' ), 'post_thumbnail_meta_box', 'ppp_tweet', 'normal', 'high' );
}
add_action( 'do_meta_boxes', 'ppp_tweets_change_image_box' );

/**
 * Register the metaboxe for Post Promoter Pro - Tweets
 * @return void
 */
function ppp_tweets_register_meta_box() {
	global $post;

	if ( $post->post_type !== 'ppp_tweet' ) {
		return;
	}

	add_meta_box( 'ppp_tweets_callback', 'Add a Link to this Tweet', 'ppp_tweets_callback', 'ppp_tweet', 'normal', 'high' );

}
add_action( 'add_meta_boxes', 'ppp_tweets_register_meta_box', 12 );

/**
 * Display the Metabox for Post Promoter Pro - Tweets
 * @return void
 */
function ppp_tweets_callback() {
	global $post;
	$current_link = get_post_meta( $post->ID, '_ppp_tweets_link', true );
	if ( empty( $current_link ) ) {
		$current_link = '';
	}
	$current_post_id = get_post_meta( $post->ID, '_ppp_tweets_link_post_id', true );
	?>
	<p id="ppp-tweets-post-link" <?php echo !empty( $current_link ) ? 'style="display: none;"' : ''; ?>>
	<?php
	$args = array(
		'name'     => '_ppp_tweets_link_post_id',
		'id'       => 'ppp_tweets_link',
		'chosen'   => true,
		'selected' => false !== $current_post_id ? $current_post_id : 0
		);
	echo ppp_tweets_post_dropdown( $args );
	?>
	</p>
	<span id="ppp-tweets-ext-notice" <?php echo !empty( $current_link ) ? 'style="display: none;"' : ''; ?>>Or <a href="#" id="ppp-tweets-link-to-post"><?php _e( 'Link to an external URL', 'ppp-tweets-txt' ); ?></a></span>
	<p id="ppp-tweets-ext-link" <?php echo empty( $current_link ) ? 'style="display: none;"' : ''; ?>>
		<input display="none" type="text" size="50" placeholder="Insert Link URL" name="_ppp_tweets_link" value="<?php echo $current_link; ?>" />&nbsp;<a class="button secondary" href="#" id="ppp-tweets-cancel-ext">Cancel</a><br />
	</p>
	<?php
}

function ppp_tweets_save_tweet( $post_id ) {
	if ( 'ppp_tweet' !== get_post_type( $post_id ) ) {
		return;
	}

	if ( !isset( $_POST['_ppp_tweets_link'] ) && !isset( $_POST['_ppp_tweets_link_post_id'] ) ) {
		return;
	}

	$validated_url = wp_http_validate_url( $_POST['_ppp_tweets_link'] );
	if ( false === $validated_url || ( isset( $_POST['_ppp_tweets_link_post_id'] ) && !empty( $_POST['_ppp_tweets_link_post_id'] ) ) ) {
		update_post_meta( $post_id, '_ppp_tweets_link', '' );
	} else {
		update_post_meta( $post_id, '_ppp_tweets_link', $validated_url );
	}

	if ( isset( $_POST['_ppp_tweets_link_post_id'] ) ) {
		update_post_meta( $post_id, '_ppp_tweets_link_post_id', (int)$_POST['_ppp_tweets_link_post_id'] );
	}

}
add_action( 'save_post', 'ppp_tweets_save_tweet', 99, 1 );

function ppp_tweets_columns( $columns ) {
	unset( $columns['author'] );
	unset( $columns['date'] );
	unset( $columns['likes'] );
	$columns['tweet_status']   = __( 'Status', 'ppp-tweets-txt' );
	$columns['tweet_link']     = __( 'Link', 'ppp-tweets-txt' );
	$columns['image_attached'] = __( 'Image', 'ppp-tweets-txt' );
	$columns['tweet_date']     = __( 'Tweet Date', 'ppp-tweets-txt' );
	return $columns;
}
add_filter( 'manage_ppp_tweet_posts_columns' , 'ppp_tweets_columns' );

function ppp_tweets_custom_columns( $column, $post_id ) {
	if ( 'ppp_tweet' !== get_post_type( $post_id ) ) {
		return;
	}

	switch ( $column ) {
		case 'tweet_status':
			$status = get_post_status( $post_id );
			if ( $status === 'draft' ) {
				$class = 'lightbulb';
			} elseif ( $status === 'future' ) {
				$class = 'clock';
			} elseif ( $status === 'publish' ) {
				$tweet_status = get_post_meta( $post_id, '_ppp_tweets_status', true );
				// Check for legacy nested status (from first push)
				$tweet_status = isset( $tweet_status['twitter'] ) ? $tweet_status['twitter'] : $tweet_status;
				if ( isset( $status->id_str ) ) {
					$class = 'yes';
					$link  = 'https://twitter.com/' . $status->user->screen_name . '/status/' . $status->id_str;
				} elseif( isset( $status->errors ) ) {
					$class   = 'no';
					$message = $status->errors[0]->message;
				}
			} elseif ( $status === 'pending' ) {
				$class = 'editor-help';
			}

			if ( isset( $link ) ) {
				echo '<span class="dashicons dashicons-' . $class . '"></span>';
				echo '&nbsp;<a title="' . __( 'View Tweet', 'ppp-tweets-txt' ) . '" href="' . $link . '" target="_blank"><span class="dashicons dashicons-external"></span></a>';
			} elseif ( isset( $message ) ) {
				echo '<span title="' . $message . '" class="dashicons dashicons-' . $class . '"></span>';
			} else {
				echo '<span class="dashicons dashicons-' . $class . '"></span>';
			}
			break;

		case 'tweet_link':
			$link = get_post_meta( $post_id, '_ppp_tweets_link', true );
			$post = (int)get_post_meta( $post_id, '_ppp_tweets_link_post_id', true );
			if ( !empty( $link ) ) {
				echo '<a href="' . $link . '" target="_blank">' . $link . '</a>';
			} elseif ( !empty( $post ) ) {
				$permalink = get_permalink( $post );
				echo '<a href="' . $permalink . '" target="_blank">' . get_the_title( $post ) . '</a>';
			} else {
				echo '<em>' . __( 'No Link', 'ppp-tweets-txt' ) . '</em>';
			}
			break;

		case 'image_attached':
			if ( has_post_thumbnail( $post_id ) ) {
				echo '<span class="dashicons dashicons-yes"></span>';
			} else {
				echo '<em>' . __( 'No Image Attached', 'ppp-tweets-txt' ) . '</em>';
			}
			break;

		case 'tweet_date':
			$date = get_the_date( get_option( 'date_format', $post_id ) );
			$time = get_the_time( get_option( 'time_format', $post_id ) );
			echo $date . ' ' . $time;
			break;
	}

}
add_action( 'manage_posts_custom_column' , 'ppp_tweets_custom_columns', 10, 2 );

