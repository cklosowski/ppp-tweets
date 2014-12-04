<?php
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

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
		$media         = ppp_post_has_media( $post->ID, 'tw', true );
		$media         = strpos( $media, 'wp-includes/images/media/default.png' ) ? false : $media;
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

		$status['twitter'] = ppp_send_tweet( $share_content . ' ' . $url, $post->ID, $media );

		if ( isset( $ppp_options['enable_debug'] ) && $ppp_options['enable_debug'] == '1' ) {
			update_post_meta( $post->ID, '_ppp-' . $name . '-status', $status );
		}

		update_post_meta( $post->ID, '_ppp_tweets_was_shared', 'true' );

	}
}
add_action( 'transition_post_status', 'ppp_tweets_share_post', 99, 3);

function ppp_tweets_post_dropdown( $args = array() ) {

	$defaults = array(
		'name'        => 'posts',
		'id'          => 'ppp-posts-dropdown',
		'class'       => '',
		'multiple'    => false,
		'selected'    => 0,
		'chosen'      => true,
		'placeholder' => __( 'Select a Post', 'ppp-tweets-txt' ),
		'number'      => 30
	);

	$args = wp_parse_args( $args, $defaults );

	$posts = get_posts( array(
		'post_type'      => 'any',
		'orderby'        => 'title',
		'order'          => 'ASC',
		'posts_per_page' => $args['number']
	) );

	$options = array();

	if ( $posts ) {
		$options[0] = __( 'Select a post', 'ppp-tweets-txt' );
		foreach ( $posts as $post ) {
			$options[ absint( $post->ID ) ] = esc_html( $post->post_title );
		}
	} else {
		$options[0] = __( 'No posts found', 'ppp-tweets-txt' );
	}

	// This ensures that any selected products are included in the drop down
	if( is_array( $args['selected'] ) ) {
		foreach( $args['selected'] as $item ) {
			if( ! in_array( $item, $options ) ) {
				$options[$item] = get_the_title( $item );
			}
		}
	} elseif ( is_numeric( $args['selected'] ) && $args['selected'] !== 0 ) {
		if ( ! in_array( $args['selected'], $options ) ) {
			$options[$args['selected']] = get_the_title( $args['selected'] );
		}
	}

	$output = ppp_tweets_render_select( array(
		'name'             => $args['name'],
		'selected'         => $args['selected'],
		'id'               => $args['id'],
		'class'            => $args['class'],
		'options'          => $options,
		'chosen'           => $args['chosen'],
		'multiple'         => $args['multiple'],
		'placeholder'      => $args['placeholder'],
		'show_option_all'  => false,
		'show_option_none' => false
	) );

	return $output;
}

function ppp_tweets_render_select( $args = array() ) {

	$defaults = array(
		'options'          => array(),
		'name'             => null,
		'class'            => '',
		'id'               => '',
		'selected'         => 0,
		'chosen'           => false,
		'placeholder'      => null,
		'multiple'         => false,
		'show_option_all'  => _x( 'All', 'all dropdown items', 'ppp-tweets-txt' ),
		'show_option_none' => _x( 'None', 'no dropdown items', 'ppp-tweets-txt' )
	);

	$args = wp_parse_args( $args, $defaults );

	if( $args['multiple'] ) {
		$multiple = ' MULTIPLE';
	} else {
		$multiple = '';
	}

	if( $args['chosen'] ) {
		$args['class'] .= ' ppp-tweets-select-chosen';
	}

	if( $args['placeholder'] ) {
		$placeholder = $args['placeholder'];
	} else {
		$placeholder = '';
	}

	$output = '<select name="' . esc_attr( $args[ 'name' ] ) . '" id="' . esc_attr( sanitize_key( str_replace( '-', '_', $args[ 'id' ] ) ) ) . '" class="ppp-tweets-select ' . esc_attr( $args[ 'class'] ) . '"' . $multiple . ' data-placeholder="' . $placeholder . '">';

	if ( ! empty( $args[ 'options' ] ) ) {
		if ( $args[ 'show_option_all' ] ) {
			if( $args['multiple'] ) {
				$selected = selected( true, in_array( 0, $args['selected'] ), false );
			} else {
				$selected = selected( $args['selected'], 0, false );
			}
			$output .= '<option value="all"' . $selected . '>' . esc_html( $args[ 'show_option_all' ] ) . '</option>';
		}

		if ( $args[ 'show_option_none' ] ) {
			if( $args['multiple'] ) {
				$selected = selected( true, in_array( -1, $args['selected'] ), false );
			} else {
				$selected = selected( $args['selected'], -1, false );
			}
			$output .= '<option value="-1"' . $selected . '>' . esc_html( $args[ 'show_option_none' ] ) . '</option>';
		}

		foreach( $args[ 'options' ] as $key => $option ) {

			if( $args['multiple'] && is_array( $args['selected'] ) ) {
				$selected = selected( true, in_array( $key, $args['selected'] ), false );
			} else {
				$selected = selected( $args['selected'], $key, false );
			}

			$output .= '<option value="' . esc_attr( $key ) . '"' . $selected . '>' . esc_html( $option ) . '</option>';
		}
	}

	$output .= '</select>';

	return $output;
}

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
