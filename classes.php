<?php

/*
Plugin Name: Video Classes
Plugin URI: http://pyebrook.com
Description: Provides custom post type for classes
Version: 1.0
Author: Jeffrey Schutzman
Author URI: http://pyebrook.com
Domain Path: localization/
Text Domain: classes
Released under the GNU General Public License (GPL)
http://www.gnu.org/licenses/gpl.txt
*/

include 'settings.php';
/////////////////////////////////////////////////////////////////////////////
class Classes {

	const POST_TYPE = 'classes';
	const VIDEO_META_KEY = 'video';
	const AUTHORIZED_META_KEY = 'authorized';
	const GUEST_META_KEY = 'guest';
	const PRODUCT_ID_META_KEY = 'product_id';

	function __construct() {
		add_action( 'init', array( &$this, 'create_classes_post_type' ), 20 );
	}

	function create_classes_post_type() {

		$labels = array(
			'name'               => 'Classes',
			'singular_name'      => 'Class',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New Class',
			'edit_item'          => 'Edit Class',
			'new_item'           => 'New Class',
			'view_item'          => 'View Class',
			'search_items'       => 'Search Classes',
			'not_found'          => 'No Classes found',
			'not_found_in_trash' => 'No Classes found in Trash',
			'parent_item_colon'  => ''
		);

		$args = array(
			'menu_icon'           => plugin_dir_url( __FILE__ ) . 'btw24.jpg',
			'labels'              => $labels,
			'public'              => true,
			'show_ui'             => true,
			'capability_type'     => 'post',
			'hierarchical'        => true,  // must be hierarchical to allow dropdown pages to work for post types
			'query_var'           => true,
			'show_in_nav_menus'   => true,
			'supports'            => array(
				'title',
				'thumbnail',
				'excerpt',
				//				'editor',
				//				'page-attributes',
				//				'make-builder',
				'author'
			),
			'taxonomies'          => array( 'category', 'post_tag' ),
			'has_archive'         => true,
			'publicly_queryable'  => true,
			'exclude_from_search' => true,
			'show_in_menu'        => true,
			'rewrite'             => array( 'slug' => 'class' ),
			//			'register_meta_box_cb' => array( &$this, 'register_meta_boxes' ),
		);

		register_post_type( self::POST_TYPE, $args );
	}

}


/**
 * @param $post
 */
function video_metabox( $post ) {
	// Note that the ID that is passed to the wp_editor() function can only be composed of
	// lower-case letters. No underscores, no hyphens. Anything else will cause the WYSIWYG
	// editor to malfunction.
	$meta_key = Classes::VIDEO_META_KEY;
	$content  = get_post_meta( $post->ID, $meta_key, true );
	wp_editor( $content, 'videocontent', $settings = array() );
}

add_action( 'save_post_' . Classes::POST_TYPE, 'save_video_content_metabox', 99 );

/**
 * @param $post_id
 */
function save_video_content_metabox( $post_id ) {
	if ( isset( $_POST['videocontent'] ) ) {
		$content  = $_POST['videocontent'];
		$meta_key = Classes::VIDEO_META_KEY;

		if ( empty( $content ) ) {
			delete_post_meta( $post_id, $meta_key );
		} else {
			update_post_meta( $post_id, $meta_key, $content );
		}
	}
}

/**
 * @param $post
 */
function authorized_metabox( $post ) {
	// Note that the ID that is passed to the wp_editor() function can only be composed of
	// lower-case letters. No underscores, no hyphens. Anything else will cause the WYSIWYG
	// editor to malfunction.
	$meta_key = Classes::AUTHORIZED_META_KEY;
	$content  = get_post_meta( $post->ID, $meta_key, true );
	wp_editor( $content, 'authorizedcontent' );
}

add_action( 'save_post_' . Classes::POST_TYPE, 'save_authorized_content_metabox', 99 );
function save_authorized_content_metabox( $post_id ) {
	if ( isset( $_POST['authorizedcontent'] ) ) {
		$content  = $_POST['authorizedcontent'];
		$meta_key = Classes::AUTHORIZED_META_KEY;

		if ( empty( $content ) ) {
			delete_post_meta( $post_id, $meta_key );
		} else {
			update_post_meta( $post_id, $meta_key, $content );
		}
	}
}

function guest_metabox( $post ) {
	// Note that the ID that is passed to the wp_editor() function can only be composed of
	// lower-case letters. No underscores, no hyphens. Anything else will cause the WYSIWYG
	// editor to malfunction.
	$meta_key = Classes::GUEST_META_KEY;
	$content  = get_post_meta( $post->ID, $meta_key, true );
	wp_editor( $content, 'guestcontent', $settings = array() );
}

add_action( 'save_post_' . Classes::POST_TYPE, 'save_guest_content_metabox', 99 );
function save_guest_content_metabox( $post_id ) {
	if ( isset( $_POST['guestcontent'] ) ) {
		$content  = $_POST['guestcontent'];
		$meta_key = Classes::GUEST_META_KEY;

		if ( empty( $content ) ) {
			delete_post_meta( $post_id, $meta_key );
		} else {
			update_post_meta( $post_id, $meta_key, $content );
		}
	}
}

$classes = new Classes();

add_action( 'add_meta_boxes', 'register_meta_boxes', 1 );

function register_meta_boxes( $post ) {
	if ( empty( $post ) ) {
		return;
	}

	add_meta_box(
		Classes::AUTHORIZED_META_KEY,
		'Content to Show Authorized Visitors',
		'authorized_metabox',
		Classes::POST_TYPE,
		'advanced',
		'high'
	);

	add_meta_box(
		Classes::GUEST_META_KEY,
		'Content to Show Guests',
		'guest_metabox',
		Classes::POST_TYPE,
		'advanced',
		'high' );


	add_meta_box(
		Classes::VIDEO_META_KEY,
		'Video Embed HTML',
		'video_metabox',
		Classes::POST_TYPE,
		'advanced',
		'high'
	);

	add_meta_box(
		Classes::PRODUCT_ID_META_KEY,
		'Product',
		'product_metabox',
		Classes::POST_TYPE,
		'side',
		'high' );

}


// ********* Get all products and variations and sort alphbetically, return in array (title, sku, id)*******
function get_woocommerce_product_list() {
	$full_product_list = array();
	$products          = new WP_Query( array(
		'post_type'      => array( 'product', 'product_variation' ),
		'posts_per_page' => - 1,
		'product_cat'    => 'learn',
	) );

	$products= $products->posts;

	foreach ( $products as $index => $product ) {
		$theid = $product->ID;

		// its a variable product
		if ( $product->post_type == 'product_variation' ) {
			$parent_id = wp_get_post_parent_id( $theid );
			$sku       = get_post_meta( $theid, '_sku', true );
			$thetitle  = get_the_title( $parent_id );

			// ****** Some error checking for product database *******
			// check if variation sku is set
			if ( $sku == '' ) {
				if ( $parent_id == 0 ) {
					// Remove unexpected orphaned variations.. set to auto-draft
			} else {
					// there's no sku for this variation > copy parent sku to variation sku
					// & remove the parent sku so the parent check below triggers
					$sku = get_post_meta( $parent_id, '_sku', true );
					if ( function_exists( 'add_to_debug' ) ) {
						add_to_debug( 'empty sku id=' . $theid . 'parent=' . $parent_id . 'setting sku to ' . $sku );
					}
					update_post_meta( $theid, '_sku', $sku );
					update_post_meta( $parent_id, '_sku', '' );
				}
			}
			// ****************** end error checking *****************

			// its a simple product
		} else {
			$thetitle = $product->post_title;
		}
		// add product to array but don't add the parent of product variations
		$full_product_list[ $theid ] = $thetitle;
	}

	return $full_product_list;
}


function product_metabox( $post ) {
	$product_list       = get_woocommerce_product_list();
	$current_product_id = get_post_meta( $post->ID, Classes::PRODUCT_ID_META_KEY, true );
	?>Product:
    <select name="product_id">
        <option value="0">Select a Product</option>
		<?php
		foreach ( $product_list as $product_id => $product_title ) {
			if ( $current_product_id == $product_id ) {
				$default = ' selected="selected" ';
			} else {
				$default = '';
			}
			echo '<option ' . $default . ' value="' . $product_id . '">' . $product_title . '</option>';
		}
		?>

    </select>
    <br>
    <span>Select the product the customer must purchase to have access to this class:</span>
	<?php
}

add_action( 'save_post_' . Classes::POST_TYPE, 'save_product_id_metabox', 99 );
function save_product_id_metabox( $post_id ) {
	if ( isset( $_POST['product_id'] ) ) {
		$content = $_POST['product_id'];

		if ( empty( $content ) ) {
			delete_post_meta( $post_id, Classes::PRODUCT_ID_META_KEY );
		} else {
			update_post_meta( $post_id, Classes::PRODUCT_ID_META_KEY, $content );
		}
	}
}


function classes_replace_the_content( $content ) {
	global $post;
	if ( $post->post_type == Classes::POST_TYPE ) {
		$current_product_id = get_post_meta( $post->ID, Classes::PRODUCT_ID_META_KEY, true );
		$options = get_option( 'classes_settings' );

		if ( ! is_user_logged_in() ) {
			$content .= '<div class="class-guest-message class-guest-message-common">' . $options['guest_user_message'] . '</div>';
			$content .= '<div class="class-guest-message">' . get_post_meta( $post->ID, Classes::GUEST_META_KEY, true ) . '</div>';
			$content .= '[woocommerce_my_account]';
			$content .= '[product_page  id="' . $current_product_id . '"]';
		} else {
			if ( current_user_can( 'edit_posts' ) ) {
				$content = '<p><span style="color:red;">You are seeing the full post content because you are logged in as an administrative user.</span></p>';
				$content .= '<div class="class-authorized-message">' . get_post_meta( $post->ID, Classes::AUTHORIZED_META_KEY, true ) . '</div>';
				$content .= '<div class="class-video">' . get_post_meta( $post->ID, Classes::VIDEO_META_KEY, true ) . '</div>';
			} else {
				$current_user       = wp_get_current_user();

				if ( wc_customer_bought_product( $current_user->user_email, $current_user->ID, $current_product_id ) ) {
					$content = '<div class="class-authorized-message">' . get_post_meta( $post->ID, Classes::AUTHORIZED_META_KEY, true ) . '</div>';
					$content .= '<div class="class-video">' . get_post_meta( $post->ID, Classes::VIDEO_META_KEY, true ) . '</div>';
				} else {
				    $content .= '<div class="class-guest-message class-guest-message-common">' . $options['not_yet_purchased_message'] . '</div>';
					$content = '<div class="class-guest-message">' . get_post_meta( $post->ID, Classes::GUEST_META_KEY, true ) . '</div>';
					$content .= '[product_page  id="' . $current_product_id . '"]';

				}
			}
		}

		$content = do_shortcode( $content );
	}

	return $content;
}

if ( ! is_admin() ) {
	add_filter( 'the_content', 'classes_replace_the_content' );
}