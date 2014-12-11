<?php

/**
 * Change the featured image text
 * @return void
 */
function ppp_tweets_change_image_box() {
	remove_meta_box( 'postimagediv', 'ppp_tweet', 'side' );
	add_meta_box( 'postimagediv', __( 'Add Media', 'ppp-tweets-txt' ), 'post_thumbnail_meta_box', 'ppp_tweet', 'normal', 'high' );
}
add_action( 'do_meta_boxes', 'ppp_tweets_change_image_box' );

/**
 * Saves the tweet information upon saving the ppp_tweet post type
 * @param  int $post_id Post ID
 * @return void
 */
function ppp_tweets_save_tweet( $post_id ) {
	if ( 'ppp_tweet' !== get_post_type( $post_id ) ) {
		return;
	}

	if ( !isset( $_POST['_ppp_tweets_link'] ) &&
		 !isset( $_POST['_ppp_tweets_link_post_id'] ) &&
		 !isset( $_POST['_ppp_tweets_crop_image' ] ) ) {
		return;
	}

	$crop_image = isset( $_POST['_ppp_tweets_crop_image' ] ) ? '1' : '0';
	update_post_meta( $post_id, '_ppp_tweets_crop_image', $crop_image );

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

/**
 * Display output for the custom columns in the list table
 * @param  string $column  Column Name
 * @param  int $post_id    The post ID for the row
 * @return void
 */
function ppp_tweets_custom_columns( $column, $post_id ) {
	if ( 'ppp_tweet' !== get_post_type( $post_id ) ) {
		return;
	}

	switch ( $column ) {
		case 'tweet_status':
			$status = get_post_status( $post_id );

			$class = 'minus';

			if ( $status === 'draft' ) {
				$class = 'lightbulb';
			} elseif ( $status === 'future' ) {
				$class = 'clock';
			} elseif ( $status === 'publish' ) {
				$tweet_status = get_post_meta( $post_id, '_ppp_tweets_status', true );
				// Check for legacy nested status (from first push)
				$tweet_status = is_array( $tweet_status ) && isset( $tweet_status['twitter'] ) ? $tweet_status['twitter'] : $tweet_status;
				if ( isset( $tweet_status->id_str ) ) {
					$class = 'yes';
					$link  = 'https://twitter.com/' . $tweet_status->user->screen_name . '/status/' . $tweet_status->id_str;
				} elseif( isset( $tweet_status->errors ) ) {
					$class   = 'no';
					$message = $tweet_status->errors[0]->message;
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

/**
 * Sharing out the post to Twitter
 * @param  string $new_status The new status of the post
 * @param  string $old_status The old status of the post
 * @param  object $post       The Post Object
 * @return void
 */
function ppp_tweets_share_post( $new_status, $old_status, $post ) {

	if ( 'ppp_tweet' !== $post->post_type ) {
		return;
	}

	$has_been_shared = get_post_meta( $post->ID, '_ppp_tweets_was_shared', true );
	if ( !empty( $has_been_shared ) ) {
		return;
	}

	if ( $new_status == 'publish' && $old_status != 'publish' ) {

		global $ppp_options;

		// Determine if we're seeing the share on publish in meta or $_POST
		$share_content = $post->post_title;
		$name          = 'ppp_tweets_share' . $post->ID;
		$media         = ppp_tweets_use_media( $post->ID );
		$url           = '';

		if ( isset( $_POST['_ppp_tweets_link_post_id'] ) ) {
			$maybe_post = $_POST['_ppp_tweets_link_post_id'];
		} else {
			$maybe_post = get_post_meta( $post->ID, '_ppp_tweets_link_post_id', true );
		}

		if ( isset( $_POST['_ppp_tweets_link'] ) ) {
			$maybe_link = $_POST['_ppp_tweets_link'];
		} else {
			$maybe_link = get_post_meta( $post->ID, '_ppp_tweets_link', true );
		}

		if ( !empty( $maybe_post ) && $maybe_post !== '0' ) {
			$url = get_permalink( $maybe_post );
		} elseif ( !empty( $maybe_link ) ) {
			$url = $maybe_link;
		}

		$status = ppp_send_tweet( $share_content . ' ' . $url, $post->ID, $media );

		update_post_meta( $post->ID, '_ppp_tweets_status', $status );
		update_post_meta( $post->ID, '_ppp_tweets_was_shared', 'true' );

	}
}
add_action( 'transition_post_status', 'ppp_tweets_share_post', 99, 3);

/**
 * Ajax endoint for searching posts in Chosen select box
 * @return string JSON encoded results
 */
function ppp_tweets_post_search() {
	global $wpdb;

	$search  = esc_sql( sanitize_text_field( $_GET['s'] ) );
	$results = array();
	$post_types = get_post_types( array( 'exclude_from_search' => true ) );
	$post_types_query = implode( ',', $post_types );
	if ( current_user_can( 'edit_products' ) ) {
		$items = $wpdb->get_results( "SELECT ID,post_title FROM $wpdb->posts WHERE `post_type` IN ( $post_types_query ) AND `post_title` LIKE '%$search%' LIMIT 50" );
	} else {
		$items = $wpdb->get_results( "SELECT ID,post_title FROM $wpdb->posts WHERE `post_type` IN ( $post_types_query ) AND `post_status` = 'publish' AND `post_title` LIKE '%$search%' LIMIT 50" );
	}

	if( $items ) {

		foreach( $items as $item ) {

			$results[] = array(
				'id'   => $item->ID,
				'name' => $item->post_title
			);
		}

	} else {

		$items[] = array(
			'id'   => 0,
			'name' => __( 'No results found', 'edd' )
		);

	}

	echo json_encode( $results );

	edd_die();
}
add_action( 'wp_ajax_ppp_tweets_post_search', 'ppp_tweets_post_search' );
add_action( 'wp_ajax_nopriv_ppp_tweets_post_search', 'ppp_tweets_post_search' );
