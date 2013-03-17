<?php

	/**
	 * Plugin Name: WP Admin Settings
	 * Plugin URI: https://github.com/colegeissinger/wordpress-admin-settings
	 * Description: A plugin that brings streamlined admin options with no coding knowledge needed.
	 * Version: 0.1
	 * Author: Cole Geissinger
	 * Author URI: http://www.colegeissinger.com
	 *
	 * @version 1.0
	 * @since 1.0
	 * @author Cole Geissinger
	 *
	 */

	/********** Set some global variables **********/
	// Set our defaults

	// Set the $active_tab variable. Feed it the default
	$active_tab = isset($_GET['tab']) ? $_GET['tab'] : $defaults['default-page'];


	// If we are currently viewing the theme options in Appearance, then load the necessary file for our options
	if($pagenow == 'options.php' || (isset($_GET['page']) && $_GET['page'] == 'wpas_theme_options')) {
		include('options/' . $active_tab . '.php');
	}


	/**
	 * Load our custom theme options page within Appearance in the admin area using add_theme_page().
	 * add_theme_page() then loads wpas_theme_options_display() to load in the HTML for the theme page.
	 * @return void
	 *
	 * @version 1.0
	 * @since 1.0
	 */
	function wpas_theme_options_menu() {
		add_theme_page('Theme Options', 'Theme Options', 'administrator', 'wpas_theme_options', 'wpas_theme_options_display');
	}
	add_action('admin_menu', 'wpas_theme_options_menu');


	/**
	 * Defines the HTML for the theme options page. This is loaded via add_theme_page() in wpas_theme_options_display()
	 * @return HTML
	 *
	 * @version 1.0
	 * @since 1.0
	 */
	function wpas_theme_options_display() {
		global $options_data, $active_tab, $page_url; ?>
		<div class="wrap">
			<div id="icon-themes" class="icon32"></div>
			<h2>Theme Options</h2>

			<?php settings_errors(); ?>

			<h2 class="nav-tab-wrapper">
				<a href="?page=wpas_theme_options&amp;tab=display_options" class="nav-tab <?php echo $active_tab == 'display_options' ? 'nav-tab-active' : ''; ?>">Display Options</a>
			</h2>

			<form action="options.php" method="post">
				<?php
					if($active_tab == 'display_options') {
						settings_fields('wpas_theme_display_options');
						do_settings_sections('wpas_theme_display_options');
					}

					submit_button();
				?>
			</form>
		</div>
		<?php echo $html;
	}


	/**
	 * Initialize the theme options page by registering our sections, fields and settings.
	 * @return void
	 *
	 * @version 1.0
	 * @since 1.0
	 */
	function wpas_initialize_theme_options() {
		global $options_data;

		//echo '<pre>'; print_r($options_data); echo '</pre>';

		if(get_option($options_data['section-id'] == false)) {
			add_option($options_data['section-id']);
		}

		if(isset($options_data)) : // Double check that our data array was imported, or else we'll get errors
			foreach($options_data as $data => $value) {
				//echo '<pre>'; print_r($value); echo '</pre>';

				if(isset($value['type'])) : // Load the switch only if the type key is set
					switch($value['type']) {
						case 'settings-section' : // Register our Settings Section
							add_settings_section($value['id'], $value['title'], $value['callback'], $value['section']);
							break;
						case 'settings-field' : // Register our Settings Fields
							add_settings_field($value['args']['id'], $value['args']['label'], $value['callback'], $value['section'], $value['settings-id'], $value['args']);
							break;
						case 'register-setting' : // Register our settings ;)
							register_setting($value['args'][0], $value['args'][1], 'wpas_form_validation');
							break;
					}
				endif;
			}
		endif;
	}
	add_action('admin_init', 'wpas_initialize_theme_options');



	/****************************************************************************
	 * Section Callback
	 ****************************************************************************/


	/**
	 * Allows us to output a description for the active section.
	 * This function is called through the settings-section array found in the appropriate page in the options directory.
	 * @return HTML
	 *
	 * @version 1.0
	 * @since 1.0
	 */
	function wpas_section_description_callback() {
		global $options_data;

		echo '<p>' . $options_data['section-desc'] . '</p>';
	}


	/****************************************************************************
	 * Fieldset Callback
	 ****************************************************************************/

	/**
	 * Creates the output for our standard input text field. Pass any settings field through this callback to output
	 *
	 * @param  Array $args The array of information for the callback.
	 * @return HTML
	 *
	 * @version 1.0
	 * @since 1.0
	 */
	function wpas_text_callback($args) {
		global $options_data;

		// Return the data saved into the database (if the data exists...)
		$options = get_option($options_data['section-id']);

		// Set a variable for our name field here to keep the source code below clean.
		$option_name = $options_data['section-id'] . '[' . $args['id'] . ']';

		// Check if our options data is saved. If not, then return empty
		if(!empty($options[$args['id']])) {
			$value = $options[$args['id']];
		} else {
			$value = '';
		}

		// Output the html while feeding in various pieces of information from our $args array in the $options_data array
		$output = '<input type="text" id="' . $args['id'] . '" class="regular-text" name="' . $option_name . '" value="' . sanitize_text_field($value) . '" />';

		// Output the description
		$output .= '<p class="description"> '  . $args['description'] . '</p>';

		echo $output;
	}


	/**
	 * Creates the output for our standard textarea. Pass any settings field through this callback to output
	 *
	 * @param  Array $args The array of information for the callback.
	 * @return HTML
	 *
	 * @version 1.0
	 * @since 1.0
	 */
	function wpas_textarea_callback($args) {
		global $options_data;

		// Return the data saved into the database (if the data exists...)
		$options = get_option($options_data['section-id']);

		// Set a variable for our name field here to keep the source code below clean.
		$option_name = $options_data['section-id'] . '[' . $args['id'] . ']';

		// Check if our options data is saved. If not, then return empty
		if(!empty($options[$args['id']])) {
			$value = $options[$args['id']];
		} else {
			$value = '';
		}

		// Output the html while feeding in various pieces of information from our $args array in the $options_data array
		$output = '<textarea name="' . $option_name . '" id="' . $args['id'] . '" class="large-text" rows="5" cols="30">' . sanitize_text_field($value) . '</textarea>';

		// Output the description
		$output .= '<p class="description"> '  . $args['description'] . '</p>';

		echo $output;
	}


	/**
	 * Creates the output for our standard checkbox. Pass any settings field through this callback to output
	 *
	 * @param  Array $args The array of information for the callback.
	 * @return HTML
	 *
	 * @version 1.0
	 * @since 1.0
	 */
	function wpas_checkbox_callback($args) {
		global $options_data;

		// Return the data saved into the database (if the data exists...)
		$options = get_option($options_data['section-id']);

		// Set a variable for our name field here to keep the source code below clean.
		$option_name = $options_data['section-id'] . '[' . $args['id'] . ']';

		// Check if our options data is saved. If not, then return empty
		if(!empty($options[$args['id']])) {
			$value = $options[$args['id']];
		} else {
			$value = '';
		}

		// Output the description
		$output .= '<p class="description"> '  . $args['description'] . '</p>';

		// Output the html while feeding in various pieces of information from our $args array in the $options_data array if it is set.
		if(isset($args['options'])) {
			foreach($args['options'] as $key => $val) :
				$output .= '<input type="checkbox" id="' . $val . '" name="' . $option_name . '" value="' . $val . '" ' . checked($value, esc_attr($value), false) . '/>';
				$output .= '<label for="' . $val . '"> '  . $key . '</label><br />';
			endforeach;
		} else { // Display one checkbox if $args['options'] doesn't exist
			$output .= '<input type="checkbox" id="' . $args['id'] . '" name="' . $option_name . '" value="1" ' . checked(1, intval($value), false) . '/>';
			$output .= '<label for="' . $args['id'] . '"> '  . $args['label'] . '</label>';
		}

		echo $output;
	}


	/**
	 * Creates the output for our standard drop-down menu. Pass any settings field through this callback to output
	 *
	 * @param  Array $args The array of information for the callback.
	 * @return HTML
	 *
	 * @version 1.0
	 * @since 1.0
	 */
	function wpas_dropdown_callback($args) {
		global $options_data;

		// Return the data saved into the database (if the data exists...)
		$options = get_option($options_data['section-id']);

		// Set a variable for our name field here to keep the source code below clean.
		$option_name = $options_data['section-id'] . '[' . $args['id'] . ']';

		// Check if our options data is saved. If not, then return empty
		if(!empty($options[$args['id']])) {
			$value = $options[$args['id']];
		} else {
			$value = '';
		}

		// Output the html while feeding in various pieces of information from our $args array in the $options_data array
		$output = '<select name="' . $option_name . '" id="' . $args['id'] . '">';

		// Check if $args['options'] exists. If not, don't load anything so we can avoid the errors
		if(isset($args['options'])) {
			foreach($args['options'] as $key => $val) :
				$output .= '<option value="' . $val . '" ' . selected($value, esc_attr($val), false) . '>' . $key . '</option>';
			endforeach;
		}

		$output .= '</select>';

		// Output the description
		$output .= '<p class="description"> '  . $args['description'] . '</p>';

		echo $output;
	}


	/**
	 * Creates the output for our standard radio fields. Pass any settings field through this callback to output
	 *
	 * @param  Array $args The array of information for the callback.
	 * @return HTML
	 *
	 * @version 1.0
	 * @since 1.1
	 */
	function wpas_radio_callback($args) {
		global $options_data;

		// Return the data saved into the data (if the data exists...)
		$options = get_option($options_data['section-id']);

		// Set a variable for our name field here to keep the source code below clean.
		$option_name = $options_data['section-id'] . '[' . $args['id'] . ']';

		// Check if our options data is saved. If not, then return empty
		if(!empty($options[$args['id']])) {
			$value = $options[$args['id']];
		} else {
			$value = '';
		}

		// Output the description
		$output .= '<p class="description"> '  . $args['description'] . '</p>';

		// Output the html while feeding in various pieces of information from our $args array in the $options_data array if it is set.
		if(isset($args['options'])) {
			foreach($args['options'] as $key => $val) :
				$output .= '<input type="radio" id="' . $val . '" name="' . $option_name . '" value="' . $val . '" ' . checked($value, esc_attr($val), false) . '/>';
				$output .= '<label for="' . $val . '"> '  . $key . '</label><br />';
			endforeach;
		} else { // Display one checkbox if $args['options'] doesn't exist
			$output .= '<input type="radio" id="' . $args['id'] . '" name="' . $option_name . '" value="1" ' . checked(1, intval($value), false) . '/>';
			$output .= '<label for="' . $args['id'] . '"> '  . $args['label'] . '</label>asdf';
		}

		echo $output;
	}



	/****************************************************************************
	 * Sanitization Callback
	 ****************************************************************************/


	/**
	 * Ensure our form information is sanitized and safe for database inclusion
	 * @param  String $input [description]
	 * @return String
	 *
	 * @version 1.0
	 * @since 1.1
	 */
	function wpas_form_validation($input) {
		$output = array();

		foreach($input as $key => $value) {
			if(isset($input[$key])) {
				// Remove any code and handle quotes
				$output[$key] = strip_tags(stripslashes($input[$key]));
			}
		}

		// Return the array so we can sanitize additional functions
		return apply_filters('wpas_form_validation', $output, $input);
	}



