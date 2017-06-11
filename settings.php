<?php
add_action( 'admin_menu', 'classes_add_admin_menu' );
add_action( 'admin_init', 'classes_settings_init' );


function classes_add_admin_menu() {

	add_options_page( 'Classes Page Messages', 'Classes Settings', 'manage_options', 'classes', 'classes_options_page' );

}


function classes_settings_init() {

	register_setting( 'pluginPage', 'classes_settings' );

	add_settings_section(
		'classes_pluginPage_section',
		__( 'The messages you would like to show visitors to classes that they are not allowed to view', 'classes' ),
		'classes_settings_section_callback',
		'pluginPage'
	);

	add_settings_field(
		'guest_user_text',
		__( 'When a visitor who is not logged in visits a class page what message would you like to display.', 'classes' ),
		'classes_guest_user_text_render',
		'pluginPage',
		'classes_pluginPage_section'
	);

	add_settings_field(
		'classes_not_yet_purchased_by_user_message',
		__( 'When a logged in user that has not purchased a class visits the class page what message would you like to display.', 'classes' ),
		'classes_not_yet_purchased_by_user_message_render',
		'pluginPage',
		'classes_pluginPage_section'
	);

}


function classes_guest_user_text_render() {

	$options = get_option( 'classes_settings' );
	?>
    <textarea cols='60' rows='5' name='classes_settings[guest_user_message]'><?php echo $options['guest_user_message']; ?></textarea>
	<?php
}


function classes_not_yet_purchased_by_user_message_render() {

	$options = get_option( 'classes_settings' );
	?>
    <textarea cols='60' rows='5' name='classes_settings[not_yet_purchased_message]'><?php echo $options['not_yet_purchased_message']; ?></textarea>
	<?php

}


function classes_settings_section_callback() {

	echo __( 'Messages specific to the class can be set on the class post', 'classes' );

}


function classes_options_page() {

	?>
    <form action='options.php' method='post'>

        <h2>Settings for Class Pages</h2>

		<?php
		settings_fields( 'pluginPage' );
		do_settings_sections( 'pluginPage' );
		submit_button();
		?>

    </form>
	<?php

}