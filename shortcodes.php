<?php

function class_get_product_id( $class_id ) {

}

/**
 * @param int $product_id
 *
 * @return bool | int
 */
function class_get_class_id( $product_id = 0 ) {
	$class_id = false;

	if ( empty( $product_id ) ) {
		$product_id = get_the_ID();
	}

	$meta_query_args = array(
		'relation' => 'OR', // Optional, defaults to "AND"
		array(
			'key'     => '_my_custom_key',
			'value'   => 'Value I am looking for',
			'compare' => '='
		)
	);

	$post_type = Classes::POST_TYPE;
	$meta_key  = Classes::PRODUCT_ID_META_KEY;

	$class_meta_query = new WP_Query(
		array(
			'post_type'  => $post_type,
			'meta_query' => array( array( 'key' => $meta_key, 'value' => $product_id, ) )
		) );

	if ( $class_meta_query->found_posts ) {
		$class_id = $class_meta_query->posts[0]->ID;
	}

	return $class_id;

}


/**
 * @param $atts
 *
 * @return string
 */
function class_downloads( $atts = array(), $content = '' ) {

	$atts = shortcode_atts( array(
		'id'                     => 0,
		'title'                  => 'Downloadable Class Materials',
		'no_downloads_message'   => '',
		'not_authorized_message' => '',
	), $atts, 'product_downloads' );

	$product_id           = intval( $atts['id'] );
	$class_id             = intval( $atts['id'] );
	$title                = trim( wp_strip_all_tags( $atts['title'] ) );
	$no_downloads_message = trim( wp_strip_all_tags( $atts['no_downloads_message'] ) );

	if ( empty( $product_id ) ) {
		$product_id = get_the_ID();
	}

	if ( Classes::POST_TYPE == get_post_type( $product_id ) ) {
		$product_id = get_post_meta( $product_id, Classes::PRODUCT_ID_META_KEY, true );
	} else {
		$class_id = class_get_class_id( $product_id );
	}

	if ( empty( $product_id ) ) {
		$content = '<span class="class-downloads-message class-downloads-error-message">ERROR: The product was not found?</span>' . "\n";;
	} elseif ( ! can_user_access_class( $class_id ) ) {
		$content = '<span class="class-downloads-message class-downloads-not-authorized-downloads-message">' . not_authorized_message . '</span>' . "\n";;
	} elseif ( empty( $product_id ) ) {
		$content = '<span class="class-downloads-message class-downloads-none-available">' . $no_downloads_message . '</span>' . "\n";;
	} else {

		$wc_product = wc_get_product( $product_id );
		if ( ! empty( $wc_product ) ) {
			$downloads = $wc_product->get_downloads();
			$content   = '<span class="class-downloads-message class-downloads-title">' . $title . '</span>' . "\n";
			$content   .= '<span class="class-downloads-message class-downloads-list">' . "\n";

			foreach ( $downloads as $key => $each_download ) {
				$url = add_query_arg( array( 'classfile' => $key, 'class' => $class_id, 'product'=>$product_id ), get_permalink() );
				$url_path = parse_url ( $url, 'PHP_URL_PATH');
				$extension = strtolower( pathinfo($url_path, PATHINFO_EXTENSION) );
				if ( strpos( $url, '?classfile' ) ) {
					$allow_download = true;
				} else {

					$allow_download = true;
					switch ( $extension ) {
						case '' :
							$allow_download = false;
							break;

						case 'zip':
						case 'pdf':
						case 'pdf':
						case 'jpg':
						case 'doc':
						case 'png':
							$allow_download = true;
							break;

						default:
							$allow_download = true;
							break;
					}
				}

				if ( $allow_download ) {

					$url     = wp_nonce_url( $url, 'classdownload' . $product_id, '_nonce' );
					$content .= '<a class="class-downloads-icon" href="' . $each_download["file"] . '">' . '<span class="dashicons dashicons-download"></span></a>';
					//				$content .= '<a class="class-downloads-url" href="' . $each_download["file"] . '">' . $each_download["name"] . '</a>';
					$content .= "\n" . '<a target="_blank" class="class-downloads-url" href="' . $url . '">' . $each_download["name"] . '</a>';
				}
			}

			$content .= '</span>' . "\n";
		}
	}

	$content = '<div class="class-downloads">' . $content . '</div>';

	return $content;
}

add_shortcode( 'class_downloads', 'class_downloads' );