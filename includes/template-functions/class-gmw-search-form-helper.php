<?php
/**
 * GMW search form helper class.
 *
 * @package gmw-my-wp.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Search Form Helper class
 *
 * @author Eyal Fitoussi
 *
 * @Since 3.0
 */
class GMW_Search_Form_Helper {

	/**
	/**
	 * Options selector field generator
	 *
	 * Deprecated since 4.0.
	 *
	 * @param  array $args    selector arguments.
	 *
	 * @param  array $options options.
	 *
	 * @return HTML element.
	 */
	public static function options_selector_builder( $args = array(), $options = array() ) {
		return array();

		/*$defaults = array(
			'id'               => 0,
			'id_tag'           => '',
			'class_tag'        => '',
			'usage'            => 'dropdown',
			'object'           => '',
			'show_options_all' => '',
			'options'          => array(),
			'name_tag'         => '',
			'required'         => 0,
		);

		// can pass $options via args or separate array as argument.
		if ( ! empty( $options ) ) {
			$args['options'] = $options;
		}

		$args = wp_parse_args( $args, $defaults );

		// modify the args.
		$args = apply_filters( 'gmw_search_form_options_selector_builder_args', $args );

		// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
		$args = apply_filters( 'gmw_search_form_' . str_replace( '-', '_', $args['object'] ) . '_options_selector_builder_args', $args );

		$id_tag = '' !== $args['id_tag'] ? 'id="' . $args['id_tag'] . '"' : '';
		$output = '';

		if ( 'hidden' === $args['usage'] ) {

			foreach ( $args['options'] as $value => $name ) {
				$output .= '<input type="hidden" name="' . esc_attr( $args['name_tag'] ) . '[]" value="' . esc_attr( sanitize_text_field( $value ) ) . '" />';
			}

			// dropdown.
		} elseif ( 'dropdown' === $args['usage'] || 'select' === $args['usage'] ) {

			$required = ! empty( $args['required'] ) ? 'required' : '';
			$output  .= '<select name="' . esc_attr( $args['name_tag'] ) . '[]" ' . esc_attr( $id_tag ) . ' class="gmw-form-field gmw-' . esc_attr( $args['object'] ) . '-field ' . esc_attr( $args['class_tag'] ) . '" ' . $required .'>';

			if ( '' !== $args['show_options_all'] ) {
				$output .= '<option value="">' . esc_html( $args['show_options_all'] ) . '</option>';
			}

			foreach ( $args['options'] as $value => $name ) {

				$selected = ( isset( $_GET[ $args['name_tag'] ] ) && in_array( $value, $_GET[ $args['name_tag'] ], true ) ) ? 'selected="selected"' : ''; // WPCS: sanitization ok, CSRF ok.

				$output .= '<option value="' . esc_attr( $value ) . '" ' . $selected . '>' . esc_html( $name ) . '</option>';
			}

			$output .= '</select>';

			// add custom styles.
		} else {
			$output .= apply_filters( 'gmw_search_form_' . $args['usage'] . '_options_selector', $output, $args, $args['options'] );
		}

		return $output;*/
	}

	/**
	 * Keywords field
	 *
	 * Deprecated since 4.0.
	 *
	 * @since 3.0
	 *
	 * @param  array $args array of arguments.
	 *
	 * @return HTML text field.
	 */
	public static function keywords_field( $args = array() ) {
		return;

		/*$url_px = gmw_get_url_prefix();

		$defaults = array(
			'id'          => 0,
			'label'       => __( 'Keywords', 'geo-my-wp' ),
			'placeholder' => __( 'Enter keywords', 'geo-my-wp' ),
			'class'       => '',
			'required'    => 0,
			'name_tag'    => $url_px . 'keywords',
		);

		$args = wp_parse_args( $args, $defaults );

		// Deprecated - misspelled.
		$args = apply_filters( 'gmw_search_forms_keywords_args', $args );

		// new filter.
		$args = apply_filters( 'gmw_search_form_keywords_field_args', $args );

		$required     = ! empty( $args['required'] ) ? 'required' : '';
		$placeholder  = ! empty( $args['placeholder'] ) ? esc_attr( $args['placeholder'] ) : '';
		$value        = ! empty( $_GET[ $url_px . 'keywords' ] ) ? sanitize_text_field( wp_unslash( $_GET[ $url_px . 'keywords' ] ) ) : ''; // WPCS: CSRF ok.

		$output = array();

		$output['div'] = '<div class="gmw-form-field-wrapper gmw-keywords-field-wrapper">';

		if ( '' !== $args['label'] ) {
			$output['label'] = '<label for="gmw-keywords-' . absint( $args['id'] ) . '" class="gmw-field-label">' . esc_html( $args['label'] ) . '</label>';
		}

		$output['field'] = '<input type="text" id="gmw-keywords-' . absint( $args['id'] ) . '" class="gmw-form-field keywords-field ' . esc_attr( $args['class'] ) . '" name="' . esc_attr( $args['name_tag'] ) . '" value="' . $value . '" placeholder="' . $placeholder . '" ' . $required . ' />';

		$output['/div'] = '</div>';

		$output = apply_filters( 'gmw_search_form_keywords_field_output', $output, $args );

		return implode( '', $output );*/
	}

	/**
	 * Address fields
	 *
	 * Depreacated since 4.0.
	 *
	 * @since 3.0
	 *
	 * @param  array $args array of arguments.
	 *
	 * @return HTLM input field.
	 */
	public static function address_field( $args = array() ) {
		return;

		/*$url_px = gmw_get_url_prefix();

		$defaults = array(
			'id'                   => 0,
			'id_attr'              => '',
			'class_attr'           => '',
			'label'                => __( 'Address', 'geo-my-wp' ),
			'placeholder'          => __( 'Enter an address', 'geo-my-wp' ),
			'locator_button'       => 1,
			'locator_submit'       => 0,
			'icon'                 => 'gmw-icon-target-light',
			'required'             => 0,
			'address_autocomplete' => 1,
			'name_attr'            => $url_px . 'address[]',
			'value'                => '',
		);

		$args = wp_parse_args( $args, $defaults );

		// New filter.
		$args = apply_filters( 'gmw_search_form_address_field_args', $args );

		$id           = esc_attr( $args['id'] );
		$id_attr      = '' !== $args['id_attr'] ? $args['id_attr'] : 'gmw-address-field-' . $id;
		$required     = $args['required'] ? 'required' : '';
		$placeholder  = ! empty( $args['placeholder'] ) ? esc_attr( $args['placeholder'] ) : '';
		$autocomplete = $args['address_autocomplete'] ? 'gmw-address-autocomplete' : '';

		if ( isset( $_GET[ $url_px . 'address' ] ) && ! empty( $_GET[ $url_px . 'form' ] ) && absint( $args['id'] ) === absint( $_GET[ $url_px . 'form' ] ) ) { // WPCS: CSRF ok.

			$value = is_array( $_GET[ $url_px . 'address' ] ) ? implode( ' ', $_GET[ $url_px . 'address' ] ) : $_GET[ $url_px . 'address' ]; // WPCS: sanitization ok CSRF ok.
			$value = sanitize_text_field( wp_unslash( $value ) );

		} elseif ( '' !== $args['value'] ) {

			$value = sanitize_text_field( wp_unslash( $args['value'] ) );

		} else {
			$value = '';
		}

		$output        = array();
		$output['div'] = '<div class="gmw-form-field-wrapper gmw-address-field-wrapper">';

		// Generate label only id needed.
		if ( ! empty( $args['label'] ) ) {
			$output['label'] = '<label class="gmw-field-label" for="' . esc_attr( $id_attr ) . '">' . esc_html( $args['label'] ) . '</label>';
		}

		$address_field = '<input type="text" name="' . esc_attr( $args['name_attr'] ) . '" id="' . esc_attr( $id_attr ) . '" class="gmw-form-field gmw-address gmw-full-address ' . $required . ' ' . $autocomplete . ' ' . esc_attr( $args['class_attr'] ) . '" value="' . esc_attr( $value ) . '" placeholder="' . $placeholder . '" autocorrect="off" autocapitalize="off" spellcheck="false" ' . $required . ' />';


		// if the locator button in within the address field.
		if ( $args['locator_button'] ) {
			
			$output['span']    = '<div class="gmw-form-field-inner gmw-address-locator-wrapper">';
			$output['address'] = $address_field;
			$output['locator'] = '<i class="gmw-locator-button inside ' . esc_attr( $args['icon'] ) . '" data-locator_submit="' . esc_attr( $args['locator_submit'] ) . '" data-form_id="' . $id . '"></i>';
			$output['/span']   = '</div>';
		} else {
			$output['address'] = $address_field;
		}

		$output['/div'] = '</div>';

		$output = apply_filters( 'gmw_search_form_address_field_output', $output, $args );

		return implode( '', $output );*/
	}

	/**
	 * Radius field
	 *
	 * Depreacated since 4.0.
	 *
	 * @since 3.0
	 *
	 * @param  array $args array of arguments.
	 *
	 * @return HTML element.
	 */
	public static function radius_field( $args = array() ) {
		return;

		/*
		$url_px   = gmw_get_url_prefix();
		$defaults = array(
			'id'                 => 0,
			'class'              => '',
			'label'              => '',
			'usage'              => 'dropdown',
			'default_value'      => '',
			'options'            => '10,15,25,50,100',
			'first_option_label' => __( 'Miles', 'geo-my-wp' ),
			'name_tag'           => $url_px . 'distance',
			'required'           => 0,
		);

		$args = wp_parse_args( $args, $defaults );
		$args = apply_filters( 'gmw_search_form_radius_field_args', $args );

		$id       = absint( $args['id'] );
		$required = ! empty( $args['required'] ) ? 'required' : '';
		$output   = array();
	
		if ( 'dropdown' == $args['usage'] ) {

			$options = explode( PHP_EOL, $args['options'] );

			$output['div'] = '<div class="gmw-form-field-wrapper gmw-distance-field-wrapper">';

			if ( ! empty( $args['label'] ) ) {
				$output['label'] = '<label class="gmw-field-label" for="gmw-distance-' . $id . '">' . esc_attr( $args['label'] ) . '</label>';
			}

			$output['select']  = '<select id="gmw-distance-' . $id . '" class="gmw-form-field distance ' . esc_attr( $args['class'] ) . '" name="' . esc_attr( $args['name_tag'] ) . '" data-form_id="' . $id . '" ' . $required . '>';
			$output['options'] = '';

			foreach ( $options as $option ) {

				$option = explode( '|', $option );
				$value  = $option[0];
				$label  = ! empty( $option[1] ) ? $option[1] : $value;
				
				// remove blank spaces from value.
				$value = trim( $value );

				if ( ! is_numeric( $value ) ) {
					continue;
				}

				$selected = ( isset( $_GET[ $url_px . 'distance' ] ) && $value === $_GET[ $url_px . 'distance' ] ) ? 'selected="selected"' : ''; // WPCS: CSRF ok.

				$output['options'] .= '<option value="' . esc_attr( $value ) . '" ' . $selected . '>' . esc_attr( $label ) . '</option>';
			}

			$output['/select'] = '</select>';
			$output['/div']    = '</div>';

		} else if ( 'default' == $args['usage'] ) {

			$default_value = ! empty( $args['default_value'] ) ? $args['default_value'] : '';

			$output['hidden'] = '<input id="gmw-distance-' . $id . '" type="hidden" name="' . esc_attr( $url_px ) . 'distance" value="' . esc_attr( $default_value ) . '" />';
		
		} else {

			$output = apply_filters( 'gmw_search_form_radius_' . $args['usage'] . '_element', '', $args );
		}
		
		if ( empty( $output ) ) {
			return;
		}

		$output = apply_filters( 'gmw_search_form_radius_field_output', $output, $args );

		return implode( '', $output );*/
	}

	/**
	 * Radius field
	 *
	 * Depreacated since 4.0.
	 *
	 * @since 3.0
	 *
	 * @param  array $args array of arguments.
	 *
	 * @return HTML element.
	 */
	public static function units_field( $args = array() ) {
		return;

		/*$defaults = array(
			'id'           => 0,
			'label'        => '',
			'class'        => '',
			'units'        => 'imperial',
			'mi_label'     => __( 'Miles', 'geo-my-wp' ),
			'km_label'     => __( 'Kilometers', 'geo-my-wp' ),
		);

		$args = wp_parse_args( $args, $defaults );
		$args = apply_filters( 'gmw_search_form_units_field_args', $args );

		$url_px = gmw_get_url_prefix();
		$url_px = esc_attr( $url_px );
		$id     = absint( $args['id'] );

		$output = array();

		if ( 'both' === $args['units'] ) {

			$selected = ( isset( $_GET[ $url_px . 'units' ] ) && 'metric' === $_GET[ $url_px . 'units' ] ) ? 'selected="selected"' : ''; // WPCS: CSRF ok.

			$output['div'] = '<div class="gmw-form-field-wrapper gmw-units-field-wrapper">';

			if ( ! empty( $args['label'] ) ) {
				$output['label'] = '<label class="gmw-field-label" for="gmw-units-' . $id . '">' . esc_attr( $args['label'] ) . '</label>';
			}

			$output['select']   = '<select id="gmw-units-' . $id . '" name="' . $url_px . 'units" class="gmw-form-field units ' . esc_attr( $args['class'] ) . '" data-form_id="' . $id . '">';
			$output['imperial'] = '<option value="imperial" selected="selected">' . esc_attr( $args['mi_label'] ) . '</option>';
			$output['metric']   = '<option value="metric" ' . $selected . '>' . esc_attr( $args['km_label'] ) . '</option>';
			$output['/select']  = '</select>';
			$output['/div']     = '</div>';

		} else {
			$output['hidden'] = '<input type="hidden" id="gmw-units-' . $id . '" name="' . $url_px . 'units" value="' . esc_attr( sanitize_text_field( $args['units'] ) ) . '" />';
		}

		$output = apply_filters( 'gmw_search_form_units_field_output', $output, $args );

		return implode( '', $output );*/
	}

	/**
	 * Locator button
	 *
	 * Depreacated since 4.0.
	 *
	 * @param  array $args array of arguments.
	 *
	 * @return HTML element.
	 */
	public static function locator_button( $args = array() ) {
		return;

		/*$defaults = array(
			'id'          => 0,
			'class'       => '',
			'usage'       => 'image',
			'image'       => 'locate-me-blue.png',
			'image_url'   => GMW_IMAGES . '/locator-images/locate-me-blue.png',
			'form_submit' => 0,
			'label'       => __( 'Get my current location', 'geo-my-wp' ),
		);

		$args = wp_parse_args( $args, $defaults );

		// Deprecated - misspelled.
		$args = apply_filters( 'gmw_search_forms_locator_button_args', $args );

		// New filter.
		$args = apply_filters( 'gmw_search_form_locator_button_args', $args );

		$id           = absint( $args['id'] );
		$usage        = esc_attr( $args['usage'] );
		$button_class = 'text' === $args['usage'] ? 'gmw-form-button' : '';
		$output       = array();

		$output['div']  = '<div class="gmw-form-field-wrapper gmw-locator-button-wrapper locator-' . esc_attr( $args['usage'] ) . '">';
		$output['span'] = '<span class="gmw-form-field-inner gmw-locator-button-inner locator-' . esc_attr( $args['usage'] ) . ' ' . $button_class . '">';

		// when using an icon.
		if ( 'image' === $usage || 'url' === $usage ) {

			$img_url = ( 'image' === $usage ) ? GMW_IMAGES . '/locator-images/' . $args['image'] : $args['image_url'];

			$output['button']= '<img id="gmw-locator-image-' . $id . '" class="gmw-locator-button image ' . esc_attr( $args['class'] ) . '" data-button_type="locator" data-locator_submit="' . absint( $args['form_submit'] ) . '" src="' . esc_url( $img_url ) . '" alt="' . __( 'locator button', 'geo-my-wp' ) . '" data-form_id="' . $id . '" />';

			// text button.
		} elseif ( 'text' === $usage ) {

			$label = ! empty( $args['label'] ) ? esc_attr( $args['label'] ) : '';

			$output['button'] = '<span id="gmw-locator-text-' . $id . '" class="gmw-locator-button text" data-button_type="locator" data-locator_submit="' . absint( $args['form_submit'] ) . '" data-form_id="' . $id . '">' . esc_attr( $args['label'] ) . '</span>';
		}

		$output['loader'] = '<i id="gmw-locator-loader-' . $id . '" class="gmw-locator-loader gmw-icon-spin animate-spin" style="display:none;"></i>';

		$output['/span']  = '</span">';
		$output['/div']   = '</div>';
		
		$output = apply_filters( 'gmw_search_form_locator_button_output', $output, $args );

		return implode( '', $output );*/
	}

	/**
	 * Submit Button.
	 *
	 * Depreacated since 4.0.
	 *
	 * @param  array $args array of arguments.
	 *
	 * @return HTML element
	 */
	public static function submit_button( $args = array() ) {
		return;

		/*$defaults = array(
			'id'    => 0,
			'class' => '',
			'label' => __( 'Submit', 'geo-my-wp' ),
		);

		$args = wp_parse_args( $args, $defaults );
		$args = apply_filters( 'gmw_search_form_submit_button_args', $args );
		$id   = ! empty( $args['id'] ) ? 'gmw-submit-' . absint( $args['id'] ) : 'gmw-submit';

		return '<input type="submit" id="' . $id . '" class="gmw-submit gmw-submit-button gmw-form-button ' . esc_attr( $args['class'] ) . '" value="' . esc_attr( $args['label'] ) . '" data-button_type="submit" data-form_id="' . $id . '" />';*/
	}

	/**
	 * Search form BP Member Types filter
	 *
	 * @param  array $args         array of arguments.
	 *
	 * @param  array $member_types array of member type.
	 *
	 * @return [type]      [description]
	 */
	public static function bp_member_types_filter( $args = array(), $member_types = array() ) {
		return;

		/*$url_px   = gmw_get_url_prefix();
		$defaults = array(
			'id'               => 1,
			'usage'            => 'smartbox',
			'id_tag'           => '',
			'class_tag'        => '',
			'name_tag'         => $url_px . 'bpmt',
			'object'           => 'bp-member-types',
			'show_options_all' => __( 'Search member types', 'geo-my-wp' ),
		);

		$args = wp_parse_args( $args, $defaults );

		if ( '' === $args['id_tag'] ) {
			$args['id_tag'] = 'gmw-bp-member-types-' . $args['id'];
		}

		/**
		 * If types are not provided
		 *
		 * We will get all types registered types.
		 */
		/*
		if ( empty( $member_types ) ) {

			$member_types = array();

			foreach ( bp_get_member_types( array(), 'object' ) as $type ) {
				$member_types[ $type->name ] = $type->labels['name'];
			}
		}

		return self::options_selector_builder( $args, $member_types );*/
	}

	/**
	 * BP groups filter
	 *
	 * We place this function here since multiple add-ons will be using it.
	 *
	 * So we do not want to have the functiosn duplicated.
	 *
	 * @param  array $args   array of arguments.
	 *
	 * @param  array $groups array of groups.
	 *
	 * @return [type]         [description]
	 */
	public static function bp_groups_filter( $args = array(), $groups = array() ) {
		return;

		/*if ( ! function_exists( 'bp_is_active' ) || ! bp_is_active( 'groups' ) ) {
			return;
		}

		$url_px = gmw_get_url_prefix();

		$defaults = array(
			'id'               => 1,
			'usage'            => 'smartbox',
			'id_tag'           => '',
			'class_tag'        => '',
			'name_tag'         => $url_px . 'bp_groups',
			'object'           => 'bp-groups',
			'show_options_all' => __( 'Search groups', 'gmw-premium-settings' ),
		);

		$args = wp_parse_args( $args, $defaults );

		if ( '' === $args['id_tag'] ) {
			$args['id_tag'] = 'gmw-bp-groups-' . $args['id'];
		}

		/**
		 * Use BP built in class to have more options when pulling groups from database.
		 * This might be a bit more memory consuming and so it is disabled by default.
		 */
		/*
		if ( apply_filters( 'gmw_ps_advanced_get_bp_groups_list', false ) ) {

			$groups = BP_Groups_Group::get(
				apply_filters(
					'gmw_search_form_get_groups_list_args',
					array(
						'type'               => 'alphabetical',
						'per_page'           => 999,
						'orderby'            => 'date_created',
						'order'              => 'DESC',
						'page'               => null,
						'user_id'            => 0,
						'slug'               => array(),
						'search_terms'       => false,
						'search_columns'     => array(),
						'group_type'         => '',
						'group_type__in'     => '',
						'group_type__not_in' => '',
						'meta_query'         => false, // WPSC: slow query ok.
						'include'            => ! empty( $groups ) ? $groups : '',
						'parent_id'          => null,
						'update_meta_cache'  => true,
						'update_admin_cache' => false,
						'exclude'            => false,
						'show_hidden'        => false,
						'status'             => array(),
					)
				)
			);

			$groups = $groups['groups'];

			/**
			 * Simpler method to retrieve the list of groups
			 */
			/*
		} else {

			global $wpdb;

			$where = '';

			if ( ! empty( $groups ) ) {
				$groups     = array_map( 'absint', $groups );
				$groups_var = implode( ',', $groups );
				$where      = "WHERE id IN ( {$groups_var} )";
			}

			$groups = $wpdb->get_results(
				"
                SELECT id, name 
                FROM {$wpdb->prefix}bp_groups
                {$where}
                "
			); // WPCS: unprepared sql ok, db call ok, cache ok.
		}

		$options = array();

		foreach ( $groups as $group ) {

			if ( 0 === absint( $group->id ) ) {
				continue;
			}

			$options[ $group->id ] = $group->name;
		}

		$args['options'] = $options;

		// get the list of groups.
		return self::options_selector_builder( $args );*/
	}
}
