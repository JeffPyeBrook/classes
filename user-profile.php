<?php


//add_action( 'personal_options_update', 'my_save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'my_save_extra_profile_fields' );

function my_save_extra_profile_fields( $user_id ) {

	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return false;
	}

	if ( isset( $_POST['classes'] ) ) {
		update_user_meta( $user_id, 'class_admin_access', $_POST['classes'] );
	}
}


//add_action( 'show_user_profile', 'my_show_extra_profile_fields' );
add_action( 'edit_user_profile', 'set_class_admin_access', 1 );

/**
 * @param WP_User $user
 */
function set_class_admin_access( $user ) {

    $class_admin_access = get_user_meta( $user->ID, 'class_admin_access', true );
    ?>

    <h3>Grant Access to Classes</h3>

    <blockquote>
        You can give access to any class by selecting the apprpriate checkbox. This can be used to allow a
        visitor to access any class without having the complete the purchase.
    </blockquote>

    <table class="widefat">

        <tr>
            <tr>
            <th style="width:20px"></th>
            <th style="width:30px;">ID</th>
            <th style="width:200px;">Class Name
            <th>Class URI</th>
        </tr>
        <?php
        $classes = get_class_list();
        foreach( $classes as $class_id => $class_name ) {
	        $class_uri = get_the_permalink( $class_id );
	        $uri_tag = '<a target="_blank" href="' . $class_uri . '">' . $class_uri . '</a>';
	        if( isset( $class_admin_access[$class_id] ) && ! empty( $class_admin_access[$class_id] ) ) {
	            $checked = ' checked ';
            } else {
	            $checked = ' ';
            }
	        ?>
            <tr>
                <td>
                    <input value="0" type="hidden" name="classes[<?php echo $class_id ?>]"/>
                    <input <?php echo $checked;?> value="1" type="checkbox" name="classes[<?php echo $class_id ?>]"/>
                </td>
                <td><?php echo $class_id ?></td>
                <td><?php echo $class_name ?></td>
                <td><?php echo $uri_tag ?></td>
            </tr>
	        <?php
        }
        ?>
    </table>
<?php
}

function can_user_access_class( $class_id, $user_id = 0 ) {

    $user_can_access_class = false;

    if ( empty( $user_id ) ) {
        $user_id = get_current_user_id();
    }

    if ( ! empty( $user_id ) ) {

	    if ( current_user_can( 'edit_posts' ) ) {
		    $user_can_access_class = true;
	    } else {

		    $class_admin_access = get_user_meta( $user_id, 'class_admin_access', true );

		    if ( isset( $class_admin_access[ $class_id ] ) && ! empty( $class_admin_access[ $class_id ] ) ) {
			    $user_can_access_class = true;
		    } else {

			    $current_user       = wp_get_current_user();
			    $current_product_id = get_post_meta( $class_id, Classes::PRODUCT_ID_META_KEY, true );

			    if ( wc_customer_bought_product( $current_user->user_email, $current_user->ID, $current_product_id ) ) {
				    $user_can_access_class = true;
			    }
		    }
	    }
    }

    return $user_can_access_class;


}