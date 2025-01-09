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
	 * Generate a complex field.
	 *
	 * Usually with 2 fields when doing a between comparison.
	 *
	 * @param  array $args   arguments for the main wrapper.
	 *
	 * @param  array $fields array of field args.
	 *
	 * @param  array $gmw    gmw form.
	 *
	 * @since 4.0
	 *
	 * @return [type]         [description]
	 */
	public static function get_complex_field( $args = array(), $fields = array(), $gmw = array() ) {

		if ( empty( $fields ) || ! is_array( $fields ) || empty( $fields[0] ) || ! is_array( $fields[0] ) ) {
			return;
		}

		$defaults = array(
			'id'            => 0,
			'slug'          => '',
			'id_attr'       => '',
			'class'         => '',
			'attributes'    => '',
			'label'         => '',
			'wrap_disabled' => false,
		);

		$args       = wp_parse_args( $args, $defaults );
		$slug       = '' !== $args['slug'] ? $args['slug'] : $fields[0]['slug'];
		$id_attr    = '' !== $args['id_attr'] ? 'id="' . esc_attr( $args['id_attr'] ) . '"' : '';
		$class      = 'gmw-form-field-wrapper gmw-' . $slug . '-field-wrapper gmw-field-type-complex-wrapper';
		$class      = '' !== $args['class'] ? $class . ' ' . $args['class'] : $class;
		$attributes = '';
		$count      = 0;

		$output = array();

		if ( ! $args['wrap_disabled'] ) {
			$output['wrapper'] = '<div ' . $id_attr . 'class="' . esc_attr( $class ) . '" ' . $attributes . '>';
		}

		if ( '' !== $args['label'] ) {
			$output['label'] = '<span class="gmw-field-label">' . esc_attr( $args['label'] ) . '</span>';
		}

		if ( ! $args['wrap_disabled'] ) {
			$output['inner'] = '<div class="gmw-complex-field-inner">';
		}

		$output['fields'] = '';

		foreach ( $fields as $field_args ) {

			$field_args['wrapper_open']  = false;
			$field_args['wrapper_close'] = false;
			$field_args['inner_element'] = true;
			// phpcs:ignore.
			// $field_args['label_inside']  = true;
			$field_args['array_key']   = ! empty( $field_args['array_key'] ) ? $field_args['array_key'] : $count;
			$field_args['id_attr']     = 'gmw-' . $field_args['slug'] . '-field-' . $field_args['id'] . '-' . $field_args['array_key'];
			$field_args['inner_class'] = 'gmw-' . $field_args['slug'] . '-field-' . $field_args['array_key'];

			// phpcs:ignore.
			//$output['fields'] .= '<div class="gmw-single-complex-field-wrapper">' . self::get_field( $field_args, $gmw ) . '</div>';
			$output['fields'] .= '<div class="gmw-single-complex-field-wrapper">' . self::get_field( $field_args, $gmw ) . '</div>';
			$count++;
		}

		if ( ! $args['wrap_disabled'] ) {
			$output['/inner']   = '</div>';
			$output['/wrapper'] = '</div>';
		}

		// phpcs:ignore.
		// $output = apply_filters( 'gmw_search_form_' . str_replace( '-', '_', $args['slug'] ) . '_complex_field_output', $output, $args, $fields, $gmw ); // Maybe security issue.
		return implode( '', $output );
	}

	/**
	 * Get a single form field.
	 *
	 * @param  array $args   field args.
	 *
	 * @param  array $gmw    gmw form object.
	 *
	 * @return [type]         [description]
	 *
	 * @since 4.0
	 *
	 * @author Eyal Fitoussi.
	 */
	public static function get_field( $args = array(), $gmw = array() ) {

		$defaults = array(
			'id'                 => 0, // Form ID.
			'slug'               => '', // Slug.
			'type'               => 'text', // Field type.
			'id_attr'            => '', // Id attribute. By deafult is generated dynamically using form ID + slug.
			'name'               => '', // name attribute. By deafult is generated dynamically.
			'sub_name'           => '', // sub_name attribute.
			'is_array'           => false, // When value is an array ( for multiple select or checkboxes for example ).
			'array_key'          => '', // When value is an array, you can provide a custom key for that array.
			'label'              => '', // Label. Can be blank.
			'inner_label'        => '',
			'placeholder'        => '', // Placeholder.
			'class'              => '', // Class attribute. for field.
			'required'           => 0, // Required field.
			'value'              => '',
			'min_value'          => '0',
			'max_value'          => '100',
			'step'               => '1',
			'value_prefix'       => '',
			'value_suffix'       => '',
			'options'            => array(),
			'date_format'        => 'd/m/Y',
			'time_format'        => 'h:i K',
			'datetime_format'    => 'd/m/Y h:i K',
			'smartbox'           => false,
			'show_options_all'   => '',
			'conditions'         => array(),
			'wrapper_open'       => true,
			'wrapper_close'      => true,
			'wrapper_id'         => '',
			'wrapper_class'      => '',
			'wrapper_atts'       => array(),
			'attributes'         => array(),
			'inner_element'      => false,
			'label_inside'       => false,
			'inner_class'        => '',
			'show_reset_field'   => '',
			'additional_args'    => array(),
			'sb_search_text'     => __( 'Search', 'geo-my-wp' ),
			'sb_no_results_text' => __( 'No results found', 'geo-my-wp' ),
		);

		if ( empty( $args['slug'] ) ) {
			$args['slug'] = $args['type'] . '_' . $args['id'] . '_' . wp_rand( 0, 100 );
		}

		$args        = wp_parse_args( $args, $defaults );
		$filter_name = str_replace( '-', '_', $args['slug'] );
		$args        = apply_filters( 'gmw_search_form_' . $filter_name . '_field_args', $args );
		$args        = apply_filters( 'gmw_search_form_options_selector_builder_args', $args ); // Deprecated.
		$args        = apply_filters( 'gmw_search_form_' . $filter_name . '_options_selector_builder_args', $args ); // deprecated.
		$org_args    = $args;
		$url_px      = gmw_get_url_prefix();

		if ( 'dropdown' === $args['type'] ) {
			$args['type'] = 'select';
		}

		$id                 = esc_attr( $args['id'] );
		$args['name']       = ! empty( $args['name'] ) ? $url_px . $args['name'] : $url_px . $filter_name;
		$class_attr         = 'gmw-form-field gmw-' . $args['slug'] . '-field gmw-' . $args['type'] . '-type-field';
		$args['class_attr'] = ! empty( $args['class'] ) ? $class_attr . ' ' . $args['class'] : $class_attr;
		$args['id_attr']    = ! empty( $args['id_attr'] ) ? $args['id_attr'] : 'gmw-' . $args['slug'] . '-field-' . $id;
		$value              = $args['is_array'] ? array() : ''; // Default.

		if ( ! empty( $args['conditions'] ) ) {
			$args['wrapper_atts']['data-conditions'] = wp_json_encode( $args['conditions'] );
		}

		if ( empty( $args['step'] ) ) {
			$args['step'] = '1';
		}

		if ( ! empty( $args['smartbox'] ) ) {
			$args['class_attr'] .= ' gmw-smartbox';
		}

		// phpcs:disable.
		/*if ( ! empty( $args['array_key'] ) ) {
			$args['is_array'] = true;
		}*/
		// phpcs:enable.

		if ( '' !== $args['array_key'] && false !== $args['array_key'] ) {
			$args['is_array'] = true;
		}

		// Get submitted value. Value is sanitized later in the sctipt.
		if ( isset( $_GET[ $args['name'] ] ) && ! empty( $_GET[ $url_px . 'form' ] ) && $id === $_GET[ $url_px . 'form' ] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.

			if ( '' !== $args['sub_name'] ) {

				$value = ! empty( $_GET[ $args['name'] ][ $args['sub_name'] ] ) ? wp_unslash( $_GET[ $args['name'] ][ $args['sub_name'] ] ) : $value; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, sanitization ok, CSRF ok.

			} else {
				$value = wp_unslash( $_GET[ $args['name'] ] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, sanitization ok, CSRF ok.
			}

			// Otherwise look for default value.
		} elseif ( ! empty( $args['value'] ) ) {
			$value = $args['value'];
		}

		// Look for value in specific array key.
		if ( is_array( $value ) && '' !== $args['array_key'] ) {
			$value = '' !== $args['array_key'] ? $value[ $args['array_key'] ] : '';
		}

		// Escape value.
		if ( isset( $value ) ) {

			if ( is_array( $value ) ) {

				$value = array_map( 'stripslashes', $value );
				$value = array_map( 'esc_attr', $value );
			} else {
				$value = esc_attr( stripslashes( $value ) );
			}
		}

		// If this is a sub array name.
		if ( '' !== $args['sub_name'] ) {
			$args['name'] .= '[' . $args['sub_name'] . ']';
		}

		if ( $args['is_array'] ) {
			$args['name'] .= '' !== $args['array_key'] ? '[' . $args['array_key'] . ']' : '[]';
		}

		if ( ! is_array( $args['attributes'] ) ) {
			$args['attributes'] = array();
		}

		$args['attributes']['data-form_id'] = $id;
		$args['attributes']['class']        = $args['class_attr'];
		$args['attributes']['name']         = $args['name'];
		$args['attributes']['id']           = $args['id_attr'];

		if ( ! empty( $args['required'] ) ) {
			$args['attributes']['required'] = 'required';
		}

		if ( ! empty( $args['placeholder'] ) ) {
			$args['attributes']['placeholder'] = $args['placeholder'];
		}

		$attributes = '';

		foreach ( array_reverse( $args['attributes'] ) as $attribute_name => $attribute_value ) {
			$attributes .= ' ' . esc_attr( $attribute_name ) . '="' . esc_attr( $attribute_value ) . '"';
		}

		$field = '';

		switch ( $args['type'] ) {

			case '':
			case 'input':
			case 'text':
				$field .= '<input type="text" ' . $attributes . ' value="' . $value . '" />'; // WPCS: XSS ok. Value already escaped.

				break;

			case 'date':
			case 'time':
			case 'datetime':
				if ( 'datetime' === $args['type'] ) {

					$format = $args['datetime_format'];

				} elseif ( 'time' === $args['type'] ) {

					$format = $args['time_format'];

				} else {
					$format = $args['date_format'];
				}

				$field .= '<input type="text" ' . $attributes . ' value="' . $value . '" data-date_format="' . $format . '" />'; // WPCS: XSS ok. Value already escaped.

				if ( ! wp_script_is( 'gmw-flatpickr', 'enqueued' ) ) {
					wp_enqueue_script( 'gmw-flatpickr' );
					wp_enqueue_style( 'gmw-flatpickr' );
				}

				$args['show_reset_field'] = 'button';

				break;

			case 'search':
				$field .= '<input type="search" ' . $attributes . ' value="' . $value . '" />'; // WPCS: XSS ok. Value already escaped.

				break;

			case 'number':
				$field .= '<input type="number" ' . $attributes . ' value="' . $value . '" />'; // WPCS: XSS ok. Value already escaped.

				break;

			case 'hidden':
			case 'pre_defined':
				// When in block editor page return hidden field value empty.
				if ( is_admin() || defined( 'REST_REQUEST' ) && REST_REQUEST ) {

					$field .= '';

				} else {

					if ( is_array( $value ) ) {
						foreach ( $value as $val ) {
							$field .= '<input type="hidden" ' . $attributes . ' value="' . $val . '" />'; // WPCS: XSS ok. Value already escaped.
						}
					} else {
						$field .= '<input type="hidden" ' . $attributes . ' value="' . $value . '" />'; // WPCS: XSS ok. Value already escaped.
					}
				}

				break;

			case 'address':
				$field_args = $args['additional_args'];

				if ( ! empty( $value ) && is_array( $value ) ) {
					$value = implode( ' ', $value );
					$value = wp_unslash( $value );
				} elseif ( empty( $value ) ) {
					$value = '';
				}

				$field .= '<input type="text" value="' . $value . '" ' . $attributes . ' autocorrect="off" autocapitalize="off" spellcheck="false" />'; // WPCS: XSS ok. Value already escaped.

				if ( ! empty( $field_args['locator_button'] ) ) {
					$field .= '<i class="gmw-locator-button inside ' . esc_attr( $field_args['icon'] ) . '" data-locator_submit="' . esc_attr( $field_args['locator_submit'] ) . '" data-form_id="' . $id . '"></i>'; // WPCS: XSS ok. $id is already escaped.
				}

				break;

			case 'select':
			case 'multiselect':
			case 'dropdown':
			case 'smartbox':
			case 'smartbox_multiple':
				if ( 'multiselect' === $args['type'] || 'smartbox_multiple' === $args['type'] ) {
					$options_all = '';
					$attributes .= ' multiple="multiple"';
				} else {
					$options_all = ! empty( $args['show_options_all'] ) ? $args['show_options_all'] : '';
				}

				$options_all = ! empty( $args['show_options_all'] ) ? $args['show_options_all'] : '';

				// Requires Premium Settings extension.
				if ( ( 'smartbox' === $args['type'] || 'smartbox_multiple' === $args['type'] || $args['smartbox'] ) ) {

					$attributes .= ' data-placeholder="' . $options_all . '" data-search_text="' . esc_attr( $args['sb_search_text'] ) . '" data-no_results_text="' . esc_attr( $args['sb_no_results_text'] ) . '"';

					$library = gmw_get_option( 'general_settings', 'smartbox_library', 'fselect' );

					if ( wp_script_is( 'gmw-' . $library, 'registered' ) && ! wp_script_is( 'gmw-' . $library, 'enqueued' ) ) {
						wp_enqueue_script( 'gmw-' . $library );
						wp_enqueue_style( 'gmw-' . $library );
					}
				}

				$field .= '<select ' . $attributes . '>';

				if ( ! empty( $options_all ) && in_array( $args['type'], array( 'select', 'dropdown', 'smartbox' ), true ) ) {
					$field .= '<option value>' . esc_attr( stripslashes( $options_all ) ) . '</option>';
				}

				if ( ! empty( $args['options'] ) ) {

					if ( ! is_array( $args['options'] ) ) {
						$args['options'] = explode( ',', $args['options'] );
						$args['options'] = array_combine( $args['options'], $args['options'] );
					}

					foreach ( $args['options'] as $option_value => $option_label ) {

						if ( is_array( $option_label ) ) {

							$option_value = isset( $option_label['value'] ) ? esc_attr( stripslashes( $option_label['value'] ) ) : '';
							$label        = isset( $option_label['label'] ) ? esc_attr( stripslashes( $option_label['label'] ) ) : '';

							// Otherwise, if label only.
						} else {
							$option_value = esc_attr( stripslashes( $option_value ) );
							$label        = esc_attr( stripslashes( $option_label ) );
						}

						if ( is_array( $value ) ) {
							// phpcs:ignore.
							$selected = in_array( $option_value, $value ) ? 'selected="selected"' : ''; // Loose compration ok.
						} else {
							// phpcs:ignore.
							$selected = $value == $option_value ? 'selected="selected"' : ''; // Loose compration ok.
						}

						$field .= '<option value="' . $option_value . '" ' . $selected . '>' . $label . '</option>';
					}
				}

				$field .= '</select>';

				break;

			case 'checkbox':
			case 'checkboxes':
				$field .= '<ul class="gmw-field-checkboxes gmw-checkbox-level-top gmw-checkboxes-options-selector">';

				if ( ! empty( $args['options'] ) ) {

					if ( ! is_array( $args['options'] ) ) {
						$args['options'] = explode( ',', $args['options'] );
						$args['options'] = array_combine( $args['options'], $args['options'] );
					}

					foreach ( $args['options'] as $option_value => $option_label ) {

						// If label is an array of value => label.
						if ( is_array( $option_label ) ) {

							$option_value = isset( $option_label['value'] ) ? esc_attr( stripslashes( $option_label['value'] ) ) : '';
							$label        = isset( $option_label['label'] ) ? esc_attr( stripslashes( $option_label['label'] ) ) : '';

							// Otherwise, if label only.
						} else {
							$option_value = esc_attr( stripslashes( $option_value ) );
							$label        = esc_attr( stripslashes( $option_label ) );
						}

						if ( is_array( $value ) ) {
							// phpcs:ignore.
							$checked = in_array( $option_value, $value, true ) ? 'checked="checked"' : ''; // Loose compration ok.
						} else {
							// phpcs:ignore.
							$checked = $value == $option_value ? 'checked="checked"' : ''; // Loose compration ok.
						}

						$field .= '<li class="gmw-field-checkbox-wrapper" data-value="' . $option_value . '">';
						$field .= '<label for="' . $args['id_attr'] . '-' . $option_value . '" class="gmw-checkbox-label">';
						$field .= '<input type="checkbox" id="' . $args['id_attr'] . '-' . $option_value . '" name="' . $args['name'] . '" class="gmw-' . $args['slug'] . '-field-checkbox gmw-field-checkbox" value="' . $option_value . '" ' . $checked . '>';
						$field .= $label;
						$field .= '</label></li>';
					}
				}

				$field .= '</ul>';

				break;

			case 'radio':
				$args['show_reset_field'] = 'link';

				$field .= '<ul class="gmw-field-radio-buttons">';

				if ( ! empty( $args['options'] ) ) {

					if ( ! is_array( $args['options'] ) ) {
						$args['options'] = explode( ',', $args['options'] );
						$args['options'] = array_combine( $args['options'], $args['options'] );
					}
				} else {
					$args['options'] = array();
				}

				if ( ! empty( $args['show_options_all'] ) ) {
					$args['options'] = array( '' => $args['show_options_all'] ) + $args['options'];
				}

				foreach ( $args['options'] as $option_value => $option_label ) {

					// If label is an array of value => label.
					if ( is_array( $option_label ) ) {

						$option_value = isset( $option_label['value'] ) ? esc_attr( stripslashes( $option_label['value'] ) ) : '';
						$label        = isset( $option_label['label'] ) ? esc_attr( stripslashes( $option_label['label'] ) ) : '';

						// Otherwise, if label only.
					} else {
						$option_value = esc_attr( stripslashes( $option_value ) );
						$label        = esc_attr( stripslashes( $option_label ) );
					}

					if ( is_array( $value ) ) {
						// phpcs:ignore.
						$checked = in_array( $option_value, $value, true ) ? 'checked="checked"' : ''; // Loose compration ok.
					} else {
						// phpcs:ignore.
						$checked = $value == $option_value ? 'checked="checked"' : ''; // Loose compration ok.
					}

					$field .= '<li class="gmw-field-radio-wrapper" value="' . $option_value . '">';
					$field .= '<label for="' . esc_attr( $args['id_attr'] ) . '-' . $option_value . '" class="gmw-radio-label">';
					$field .= '<input type="radio" id="' . esc_attr( $args['id_attr'] ) . '-' . $option_value . '" name="' . esc_attr( $args['name'] ) . '" class="gmw-' . esc_attr( $args['slug'] ) . '-field-radio gmw-field-radio" value="' . $option_value . '" ' . $checked . '>';
					$field .= $label;
					$field .= '</label></li>';
				}

				$field .= '</ul>';

				break;

			case 'textarea':
				$attributes .= empty( $args['attributes']['cols'] ) ? $attributes . ' cols="40"' : '';
				$attributes .= empty( $args['attributes']['rows'] ) ? $attributes . ' rows="5"' : '';

				$field .= '<textarea ' . $attributes . '>' . $value . '</textarea>'; // WPCS: XSS ok. Value already escaped.

				break;

			case 'submit':
				$field .= '<input type="submit" id="gmw-submit-' . $id . '" class="' . esc_attr( $args['class_attr'] ) . ' gmw-form-button gmw-submit-button gmw-submit" value="' . $value . '" data-button_type="' . esc_attr( $args['slug'] ) . '" data-form_id="' . $id . '" />'; // WPCS: XSS ok. Value and $id already escaped.

				break;

			case 'locator_button':
				$field_args   = $args['additional_args'];
				$button_usage = esc_attr( $field_args['usage'] );

				// when using an icon.
				if ( 'image' === $button_usage || 'url' === $button_usage ) {

					$img_url = ( 'image' === $button_usage ) ? GMW_IMAGES . '/locator-images/' . $field_args['image'] : $field_args['image_url'];

					$field .= '<img id="gmw-locator-image-' . $id . '" class="gmw-locator-button image ' . esc_attr( $args['class_attr'] ) . '" data-button_type="locator" data-locator_submit="' . absint( $field_args['form_submit'] ) . '" src="' . esc_url( $img_url ) . '" alt="' . __( 'locator button', 'geo-my-wp' ) . '" data-form_id="' . $id . '" />'; // WPCS: XSS ok. $id already escaped.

					// text button.
				} elseif ( 'text' === $button_usage ) {

					$label = ! empty( $field_args['label'] ) ? esc_attr( $field_args['label'] ) : '';

					$field .= '<span id="gmw-locator-text-' . $id . '" class="gmw-locator-button text" data-button_type="locator" data-locator_submit="' . absint( $field_args['form_submit'] ) . '" data-form_id="' . $id . '">' . esc_attr( $field_args['label'] ) . '</span>'; // WPCS: XSS ok. $id already escaped.
				}

				$field .= '<i id="gmw-locator-loader-' . $id . '" class="gmw-locator-loader gmw-icon-spin animate-spin" style="display:none;"></i>'; // WPCS: XSS ok. $id already escaped.

				break;

			case 'taxonomy':
				$field .= self::get_taxonomy_element( $args['additional_args'], $gmw );

				break;

			case 'link':
				$field .= '<a ' . $attributes . '>' . esc_attr( $args['inner_label'] ) . '</a>';

				break;

			case 'slider':
				// phpcs:disable.
				//$inner_label = ! empty( $args['inner_label'] ) ? '<span class="range-slider-label"> ' . esc_attr( $args['inner_label'] ) . '</span>' : '';

				//$field .= '<input type="range" ' . $attributes . '" min="' . esc_attr( $args['min_value'] ) . '" max="' . esc_attr( $args['max_value'] ) . '" step="' . esc_attr( $args['steps'] ) . '" value="' . $value . '">';
				// phpcs:enable.
				$field .= '<div ' . $attributes . ' data-min="' . esc_attr( $args['min_value'] ) . '" data-max="' . esc_attr( $args['max_value'] ) . '" data-step="' . esc_attr( $args['step'] ) . '" data-value="' . $value . '" data-prefix="' . esc_attr( $args['value_prefix'] ) . '" data-suffix="' . esc_attr( $args['value_suffix'] ) . '"></div>';

				$field .= '<input type="hidden" id="' . $args['attributes']['id'] . '-hidden" name="' . $args['attributes']['name'] . '" value="' . $value . '" />';

				// phpcs:ignore.
				//$field .= '<span class="gmw-range-slider-output"><span class="range-slider-value">' . $value . '</span>' . $inner_label . '</span>';

				break;

			case 'range_slider':
				// phpcs:disable.
				//$inner_label = ! empty( $args['inner_label'] ) ? '<span class="range-slider-label"> ' . esc_attr( $args['inner_label'] ) . '</span>' : '';

				//$field .= '<input type="range" ' . $attributes . '" min="' . esc_attr( $args['min_value'] ) . '" max="' . esc_attr( $args['max_value'] ) . '" step="' . esc_attr( $args['steps'] ) . '" value="' . $value . '">';
				// phpcs:enable.
				$field .= '<div ' . $attributes . ' data-min="' . esc_attr( $args['min_value'] ) . '" data-max="' . esc_attr( $args['max_value'] ) . '" data-step="' . esc_attr( $args['step'] ) . '" data-value="' . $value[0] . '" data-value_second="' . $value[1] . '" data-prefix="' . esc_attr( $args['value_prefix'] ) . '" data-suffix="' . esc_attr( $args['value_suffix'] ) . '"></div>';

				$field .= '<input type="hidden" id="' . $args['attributes']['id'] . '-hidden" name="' . $args['attributes']['name'] . '[]" value="' . $value[0] . '" />';
				$field .= '<input type="hidden" id="' . $args['attributes']['id'] . '-hidden-second" name="' . $args['attributes']['name'] . '[]" value="' . $value[1] . '" />';

				// phpcs:ignore.
				//$field .= '<span class="gmw-range-slider-output"><span class="range-slider-value">' . $value . '</span>' . $inner_label . '</span>';

				break;

			case 'function':
				$args['value'] = $value;

				$field .= apply_filters( 'gmw_search_form_' . $args['function'] . '_field_function', '', $args, $gmw, $attributes, $org_args );

				break;

			default:
				$args['value'] = $value;

				$field .= apply_filters( 'gmw_search_form_' . $args['type'] . '_field', '', $args, $gmw, $attributes, $org_args );

				break;
		}

		if ( empty( $field ) ) {
			return;
		}

		$output = array();

		if ( 'hidden' === $args['type'] || 'pre_defined' === $args['type'] ) {

			$output['field'] = '';

		} else {

			// Reset field button.
			if ( 'link' === $args['show_reset_field'] ) {

				$field .= '<a href="#" class="gmw-reset-field-trigger gmw-reset-field-' . esc_attr( $args['show_reset_field'] ) . '">' . esc_attr__( 'Reset', 'geo-my-wp' ) . '</a>';

			} elseif ( 'button' === $args['show_reset_field'] ) {

				$field .= '<span class="gmw-reset-field-trigger gmw-reset-field-button gmw-icon-cancel"></span>';

				$args['inner_element'] = true;
				$args['inner_class']  .= ' gmw-reset-button-enabled ';
			}

			if ( $args['wrapper_open'] ) {

				$wrapper_id    = ! empty( $args['wrapper_id'] ) ? 'id="' . esc_attr( $args['wrapper_id'] ) . '" ' : '';
				$wrapper_class = 'gmw-form-field-wrapper gmw-' . $args['slug'] . '-field-wrapper gmw-field-type-' . $args['type'] . '-wrapper';
				$wrapper_class = ! empty( $args['wrapper_class'] ) ? $wrapper_class . ' ' . esc_attr( $args['wrapper_class'] ) : $wrapper_class;
				$attributes    = '';

				// attributes.
				if ( is_array( $args['wrapper_atts'] ) && ! empty( $args['wrapper_atts'] ) ) {

					foreach ( $args['wrapper_atts'] as $attribute_name => $attribute_value ) {
						$attributes .= ' ' . esc_attr( $attribute_name ) . '="' . esc_attr( $attribute_value ) . '"';
					}
				}

				$output['wrapper'] = '<div ' . $wrapper_id . 'class="' . esc_attr( $wrapper_class ) . '" ' . $attributes . '>';
			}

			if ( $args['inner_element'] ) {

				$inner_class = '' !== $args['inner_class'] ? ' ' . esc_attr( $args['inner_class'] ) : '';

				if ( ! $args['label_inside'] ) {
					$output['label'] = '';
				}

				$output['inner'] = '<div class="gmw-field-inner' . $inner_class . '">';

				if ( $args['label_inside'] ) {
					$output['label'] = '';
				}

				$output['field']  = '';
				$output['/inner'] = '</div>';
			} else {
				$output['label'] = '';
				$output['field'] = '';
			}

			if ( $args['wrapper_close'] ) {

				$output['/wrapper'] = '</div>';
			}

			if ( '' !== $args['label'] ) {
				$output['label'] = '<label for="' . esc_attr( $args['id_attr'] ) . '" class="gmw-field-label">' . esc_attr( $args['label'] ) . '</label>';
			}
		}

		$output['field'] = $field;

		// phpcs:disable.
		// Maybe security issue. Need to check before enabling.
		// $output          = apply_filters( 'gmw_search_form_' . $filter_name . '_field_output', $output, $args, $gmw );
		// enqueue date picker styles and scripts.
		/*if ( ! wp_script_is( 'datetime-picker', 'enqueued' ) && in_array( $args['type'], array( 'date', 'time', 'datetime' ), true ) ) {
			//wp_enqueue_script( 'datetime-picker' );
			//wp_enqueue_style( 'datetime-picker' );
		}*/
		// phpcs:enable.

		if ( ! wp_script_is( 'gmw-nouislider', 'enqueued' ) && ( 'slider' === $args['type'] || 'range_slider' === $args['type'] ) ) {
			wp_enqueue_script( 'gmw-nouislider' );
			wp_enqueue_style( 'gmw-nouislider' );
		}

		return implode( '', $output );
	}

	/**
	 * Generate a single search form taxonomy
	 *
	 * @param  array $args [description].
	 *
	 * @param  array $gmw  [description].
	 *
	 * @return [type]       [description]
	 */
	public static function get_taxonomy_element( $args = array(), $gmw = array() ) {

		$defaults = array(
			'id'                  => 0,
			'label'               => '',
			'taxonomy'            => 'category',
			'post_types'          => array(),
			'usage'               => 'dropdown',
			'show_options_all'    => true,
			'orderby'             => 'id',
			'order'               => 'ASC',
			'include'             => '',
			'exclude'             => '',
			'show_count'          => 0,
			'hide_empty'          => 1,
			'category_icons'      => 0,
			'multiple_selections' => 0,
			'smartbox'            => 0,
			'required'            => 0,
			'name_attr'           => 'tax',
			'sub_name_attr'       => '',
		);

		$args          = wp_parse_args( $args, $defaults );
		$id            = absint( $args['id'] );
		$tax_name      = esc_attr( $args['taxonomy'] );
		$taxonomy      = get_taxonomy( $tax_name );
		$hierarchical  = is_taxonomy_hierarchical( $tax_name ) ? true : false;
		$options_all   = 0;
		$placeholder   = 0;
		$id_attr       = $tax_name . '-taxonomy-' . $id;
		$name_attr     = ! empty( $args['name_attr'] ) ? $args['name_attr'] : 'tax';
		$sub_name_attr = ! empty( $args['sub_name_attr'] ) ? $args['sub_name_attr'] : $tax_name;
		$selected      = ! empty( $_GET[ $name_attr ][ $sub_name_attr ] ) ? wp_unslash( array_map( 'absint', $_GET[ $name_attr ][ $sub_name_attr ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.

		if ( empty( $args['show_options_all'] ) || 'checkboxes' === $args['usage'] ) {

			$options_all = 0;
			$placeholder = 0;

		} elseif ( is_string( $args['show_options_all'] ) ) {

			$options_all = $args['show_options_all'];
			$placeholder = $args['show_options_all'];

		} else {

			/* translators: %s: taxonomy name. */
			$options_all = sprintf( __( 'All %s', 'geo-my-wp' ), esc_attr( $taxonomy->labels->name ) );
			/* translators: %s: taxonomy name. */
			$placeholder = sprintf( __( 'Select %s', 'geo-my-wp' ), esc_attr( $taxonomy->labels->name ) );
		}

		// set taxonomy args.
		$tax_args = apply_filters(
			'gmw_search_form_' . $args['usage'] . '_taxonomy_args',
			array(
				'taxonomy'            => $tax_name,
				'orderby'             => $args['orderby'],
				'order'               => $args['order'],
				'hide_empty'          => $args['hide_empty'],
				'include'             => $args['include'],
				'exclude'             => $args['exclude'],
				'exclude_tree'        => '',
				'number'              => 0,
				'hierarchical'        => $hierarchical,
				'parent'              => '',
				'child_of'            => 0,
				'pad_counts'          => 1,
				//'selected'            => ! empty( $_GET['tax'][ $tax_name ] ) ? $_GET['tax'][ $tax_name ] : '', // phpcs:ignore: CSRF ok, sanitization ok. $_GET['tax'][ $tax_name ] is an array and should be sanitized in the walker class.
				'selected'            => $selected,
				'depth'               => $hierarchical ? 0 : -1,
				'category_icons'      => $args['category_icons'],
				'name_attr'           => ! empty( $args['name_attr'] ) ? $args['name_attr'] : 'tax',
				'sub_name_attr'       => ! empty( $args['sub_name_attr'] ) ? $args['sub_name_attr'] : $tax_name,
				'gmw_form_id'         => $id,
				'show_option_all'     => $options_all,
				// phpcs:ignore.
				'show_count'          => 1 == $args['show_count'] ? 1 : 0, // Loose compration ok.
				'usage'               => $args['usage'],
				'multiple_selections' => $args['multiple_selections'],
				'smartbox'            => $args['smartbox'],
				'placeholder'         => $placeholder,
				'no_results_text'     => __( 'No results match', 'geo-my-wp' ),
			),
			$taxonomy,
			$gmw
		);

		// deprected hook. Will be removed in the future.
		$tax_args = apply_filters( 'gmw_pt_' . $args['usage'] . '_taxonomy_args', $tax_args, $gmw, $taxonomy, $tax_name, $args );

		$args['usage'] = $tax_args['usage']; // In case the usage was modified via the filter above.

		// set terms_hash args. only args that control the output of the terms should be here.
		// This will be used with the cache helper.
		$terms_args = array(
			'taxonomy'     => $tax_args['taxonomy'],
			'orderby'      => $tax_args['orderby'],
			'order'        => $tax_args['order'],
			'hide_empty'   => $tax_args['hide_empty'],
			'exclude'      => $tax_args['exclude'],
			'exclude_tree' => $tax_args['exclude_tree'],
			'include'      => $tax_args['include'],
			'hierarchical' => $tax_args['hierarchical'],
			'child_of'     => $tax_args['child_of'],
			'parent'       => $tax_args['parent'],
		);

		// include GMW_Post_Category_Walker file.
		if ( ! class_exists( 'GMW_Post_Category_Walker' ) ) {
			require_once GMW_PT_PATH . '/includes/class-gmw-post-category-walker.php';
		}

		// phpcs:disable.
		// $wrap_element = apply_filters( 'gmw_search_form_enable_field_wrapping_element', false, 'taxonomy' );
		// $output['wrapper'] = '<div id="gmw-' . $args['taxonomy'] .'-taxonomy-wrapper" class="gmw-form-field-wrapper gmw-single-taxonomy-wrapper gmw-' . $args['usage'] . '-taxonomy-wrapper" data-post_types="' . implode( ',', $args['post_types'] ) . '">';
		// if showing label.
		// if ( ! empty( $args['label'] ) ) {
		// $output['label'] = '<label class="gmw-field-label" for="' . $id_attr . '">' . esc_attr( $args['label'] ) . '</label>';
		// }
		// if ( $wrap_element ) {
		// $output['inner'] = '<div class="gmw-form-field-input-wrapper">';
		// }
		// phpcs:enable.

		$output = '';

		if ( ! empty( $args['smartbox'] ) && ( 'select' === $args['usage'] || 'dropdown' === $args['usage'] || 'multiselect' === $args['usage'] ) ) {
			$args['usage']     = 'multiselect' === $args['usage'] ? 'smartbox_multiple' : 'smartbox';
			$tax_args['usage'] = $args['usage'];
		}

		// if dropdown style taxonomies.
		if ( 'select' === $args['usage'] || 'dropdown' === $args['usage'] || 'multiselect' === $args['usage'] ) {

			$multiple  = ( 'multiselect' === $args['usage'] && class_exists( 'GMW_Premium_Settings_Addon' ) ) ? ' multiple="multiple" ' : '';
			$required  = ! empty( $args['required'] ) ? 'required="required"' : '';
			$name_attr = esc_attr( $tax_args['name_attr'] . '[' . $tax_args['sub_name_attr'] . ']' );

			// select tag.
			$output .= "<select name=\"{$name_attr}[]\" id=\"{$id_attr}\" class=\"gmw-form-field gmw-taxonomy-field {$tax_name}\" data-gmw-dropdown-parent=\"#{$taxonomy->name}-taxonomy-wrapper\" {$required} {$multiple}>";

			if ( ! empty( $tax_args['show_option_all'] ) ) {
				$output .= '<option value="" selected="selected">' . esc_attr( $tax_args['show_option_all'] ) . '</option>';
			}

			$terms_args = apply_filters( 'gmw_pt_taxonomy_terms_args', $terms_args, $tax_args, $gmw );

			// get the taxonomies terms.
			$terms = gmw_get_terms( $tax_name, $terms_args );

			// new category walker.
			$walker = new GMW_Post_Category_Walker();

			// run the category walker.
			$output .= $walker->walk( $terms, $tax_args['depth'], $tax_args );

			// closing select tag.
			$output .= '</select>';

			// Filter to generate your custom style.
		} else {

			$output .= apply_filters( 'gmw_generate_' . $args['usage'] . '_taxonomy', $output, $tax_args, $taxonomy );
		}

		// phpcs:disable.
		// if ( $wrap_element ) {
		// $output['/inner'] = '</div>';
		// }
		// $output['/wrapper'] = '</div>';
		// phpcs:enable.

		return $output;
	}

	/**
	 * Hidden submission fields
	 *
	 * @param array $gmw gmw form.
	 *
	 * @return mixed
	 */
	public static function submission_fields( $gmw = array() ) {

		if ( empty( $gmw['ID'] ) ) {
			return;
		}

		if ( ! isset( $gmw['search_results']['per_page'] ) ) {
			$gmw['search_results']['per_page'] = 10;
		}

		$prefix = esc_attr( gmw_get_url_prefix() );
		$id     = esc_attr( absint( $gmw['ID'] ) );

		if ( ! empty( $_GET[ $prefix . 'lat' ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.
			$lat = sanitize_text_field( wp_unslash( $_GET[ $prefix . 'lat' ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.
			$lat = esc_attr( filter_var( $lat, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION ) );
		} else {
			$lat = '';
		}

		if ( ! empty( $_GET[ $prefix . 'lng' ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.
			$lng = sanitize_text_field( wp_unslash( $_GET[ $prefix . 'lng' ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.
			$lng = esc_attr( filter_var( $lng, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION ) );
		} else {
			$lng = '';
		}

		// generate fields.
		$output = '<div id="gmw-submission-fields-' . $id . '" class="gmw-submission-field" data-form_id="' . $id . '" style="display:none">';

		// set the page number to 1. We do this to reset the page number when form submitted again.
		$output .= '<input type="hidden" id="gmw-page-' . $id . '" class="gmw-page" name="page" value="1" />';

		// Fix for home page pagination when going to the first page.
		if ( is_front_page() || is_single() ) {
			$output .= '<input type="hidden" id="gmw-paged-' . $id . '" class="gmw-paged" name="paged" value="1" />';
		}

		$per_page = absint( current( explode( ',', $gmw['search_results']['per_page'] ) ) );

		$output .= '<input type="hidden" id="gmw-per-page-' . $id . '" class="gmw-per-page" name="' . $prefix .'per_page" value="' . esc_attr( $per_page ) . '" />';
		$output .= '<input type="hidden" id="gmw-lat-' . $id . '" class="gmw-lat" name="' . $prefix . 'lat" value="' . $lat . '" />';
		$output .= '<input type="hidden" id="gmw-lng-' . $id . '" class="gmw-lng" name="' . $prefix . 'lng" value="' . $lng . '" />';

		if ( 'global_maps' === $gmw['addon'] || 'ajax_forms' === $gmw['addon'] ) {
			$output .= '<input type="hidden" id="gmw-swlatlng-' . $id . '" class="gmw-swlatlng" name="swlatlng" />';
			$output .= '<input type="hidden" id="gmw-nelatlng-' . $id . '" class="gmw-nelatlng" name="nelatlng" />';
		}

		// State boundaries.
		if ( ! empty( $gmw['boundaries_search']['state'] ) ) {

			if ( ! empty( $_GET['state'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.
				$state = str_replace( '=', '', sanitize_text_field( wp_unslash( $_GET['state'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.
				$state = esc_attr( filter_var( $state ) );
			} else {
				$state = '';
			}
			echo $state;
			$output .= '<input type="hidden" id="gmw-state-' . $id . '" class="gmw-state" name="state" value="' . $state . '" />';
		}

		// Country boundaries.
		if ( ! empty( $gmw['boundaries_search']['country'] ) ) {

			if ( ! empty( $_GET['country'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.
				$country = sanitize_key( wp_unslash( $_GET['country'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.
				$country = esc_attr( filter_var( $country ) );
			} else {
				$country = '';
			}

			$output .= '<input type="hidden" id="gmw-country-' . $id . '" class="gmw-country" name="country" value="' . $country . '" />';
		}

		$output .= '<input type="hidden" id="gmw-form-id-' . $id . '" class="gmw-form-id" name="' . $prefix . 'form" value="' . $id . '" />';
		$output .= '<input type="hidden" id="gmw-action-' . $id . '" class="gmw-action" name="' . $prefix . 'action" value="fs"/>';

		$output = apply_filters( 'gmw_submission_fields', $output, $id, $_GET ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.

		$output .= '</div>';

		return $output;
	}

	/**
	 * Options selector field generator
	 *
	 * Deprecated since 4.0.
	 *
	 * @param  array $args    selector arguments.
	 *
	 * @param  array $options options.
	 *
	 * @return array
	 */
	public static function options_selector_builder( $args = array(), $options = array() ) {
		return array();

		// phpcs:disable.
		/*
		$defaults = array(
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
		// phpcs:enable.
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
	 * @return void
	 */
	public static function keywords_field( $args = array() ) {
		return;

		// phpcs:disable.
		/*
		$url_px = gmw_get_url_prefix();

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
		// phpcs:enable.
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
	 * @return void
	 */
	public static function address_field( $args = array() ) {
		return;

		// phpcs:disable.
		/*
		$url_px = gmw_get_url_prefix();

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
		// phpcs:enable.
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
	 * @return void
	 */
	public static function radius_field( $args = array() ) {
		return;

		// phpcs:disable.
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
		// phpcs:enable.
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
	 * @return void
	 */
	public static function units_field( $args = array() ) {
		return;

		// phpcs:disable.
		/*
		$defaults = array(
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
		// phpcs:enable.
	}

	/**
	 * Locator button
	 *
	 * Depreacated since 4.0.
	 *
	 * @param  array $args array of arguments.
	 *
	 * @return void
	 */
	public static function locator_button( $args = array() ) {
		return;

		// phpcs:disable.
		/*
		$defaults = array(
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

		return implode( '', $output );
		*/
		// phpcs:enable.
	}

	/**
	 * Submit Button.
	 *
	 * Depreacated since 4.0.
	 *
	 * @param  array $args array of arguments.
	 *
	 * @return void
	 */
	public static function submit_button( $args = array() ) {
		return;

		// phpcs:disable.
		/*
		$defaults = array(
			'id'    => 0,
			'class' => '',
			'label' => __( 'Submit', 'geo-my-wp' ),
		);

		$args = wp_parse_args( $args, $defaults );
		$args = apply_filters( 'gmw_search_form_submit_button_args', $args );
		$id   = ! empty( $args['id'] ) ? 'gmw-submit-' . absint( $args['id'] ) : 'gmw-submit';

		return '<input type="submit" id="' . $id . '" class="gmw-submit gmw-submit-button gmw-form-button ' . esc_attr( $args['class'] ) . '" value="' . esc_attr( $args['label'] ) . '" data-button_type="submit" data-form_id="' . $id . '" />';*/
		// phpcs:enable.
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

		// phpcs:disable.
		/*
		$url_px   = gmw_get_url_prefix();
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
		// phpcs:enable.
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

		// phpcs:disable.
		/*
		if ( ! function_exists( 'bp_is_active' ) || ! bp_is_active( 'groups' ) ) {
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
			'show_options_all' => __( 'Search groups', 'geo-my-wp' ),
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
		// phpcs:enable.
	}
}
