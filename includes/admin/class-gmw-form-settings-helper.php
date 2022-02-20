<?php
/**
 * GEO my WP form settings helper.
 *
 * @author Eyal Fitoussi
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * GEO my WP form settings helper class.
 */
class GMW_Form_Settings_Helper {

	/**
	 * Get list of pages.
	 *
	 * @return [type] [description]
	 */
	public static function get_pages() {
		$pages = array();

		foreach ( get_pages() as $page ) {
			$pages[ $page->ID ] = $page->post_title;
		}

		return $pages;
	}

	/**
	 * Generate array of post types
	 *
	 * @return [type] [description]
	 */
	public static function get_post_types() {

		$output = array();

		foreach ( get_post_types() as $post ) {
			$output[ $post ] = get_post_type_object( $post )->labels->name . ' ( ' . $post . ' )';
		}

		return $output;
	}

	/**
	 * Get a singple form field.
	 *
	 * @param  array  $field     field array.
	 *
	 * @param  string $name_attr name attribute.
	 *
	 * @param  string $value     value.
	 *
	 * @return [type]            [description]
	 */
	public static function get_form_field( $field = array(), $name_attr = '', $value = '' ) {

		$default_value = isset( $field['default'] ) ? $field['default'] : '';
		$field_name    = esc_attr( $field['name'] );
		$id_attr       = 'gmw-form-field-' . $field_name;
		$field_type    = isset( $field['type'] ) ? esc_attr( $field['type'] ) : 'text';
		$placeholder   = ! empty( $field['placeholder'] ) ? 'placeholder="' . esc_attr( $field['placeholder'] ) . '"' : '';
		$name_attr     = ! empty( $name_attr ) ? esc_attr( $name_attr . '[' . $field_name . ']' ) : $field_name;
		$attributes    = array();

		// attributes.
		if ( ! empty( $field['attributes'] ) && is_array( $field['attributes'] ) ) {
			foreach ( $field['attributes'] as $attribute_name => $attribute_value ) {
				$attributes[] = esc_attr( $attribute_name ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}

		$output = '';

		switch ( $field_type ) {

			case '':
			case 'input':
			case 'text':
			default:
				$output .= '<input type="text" id="' . $id_attr . '" class="gmw-form-field regular-text text" name="' . $name_attr . '" value="' . esc_attr( sanitize_text_field( $value ) ) . '" ' . implode( ' ', $attributes ) . ' ' . $placeholder . ' />';

				break;

			case 'checkbox':
				$output .= '<label>';
				$output .= '<input type="checkbox" id="' . $id_attr . '" class="gmw-form-field checkbox"';
				$output .= ' name="' . $name_attr . '" value="1"';
				$output .= ' ' . implode( ' ', $attributes );
				$output .= ' ' . checked( '1', $value, false ) . ' />';
				$output .= isset( $field['cb_label'] ) ? esc_attr( $field['cb_label'] ) : '';
				$output .= '</label>';

				break;

			case 'multicheckbox':
				foreach ( $field['options'] as $key_val => $name ) {

					$key_val = esc_attr( $key_val );
					$value   = ! empty( $value[ $key_val ] ) ? $value[ $key_val ] : $default_value;
					$output .= '<label>';
					$output .= '<input type="checkbox" id="' . $id_attr . '-' . $key_val . '" class="gmw-form-field ' . $field_name . ' checkbox multicheckbox" name="' . $name_attr . '[' . $key_val . ']" value="1" ' . checked( '1', $value ) . '/>';
					$output .= esc_html( $name );
					$output .= '</label>';

				}
				break;

			case 'multicheckboxvalues':
				$default_value = is_array( $default_value ) ? $default_value : array();

				foreach ( $field['options'] as $key_val => $name ) {

					$key_val = esc_attr( $key_val );
					$checked = in_array( $key_val, $value ) ? 'checked="checked"' : '';

					$output .= '<label>';
					$output .= '<input type="checkbox" id="' . $id_attr . '-' . $key_val . '"';
					$output .= ' class="gmw-form-field ' . $field_name . ' checkbox multicheckboxvalues"';
					$output .= ' name="' . $name_attr . '[]"';
					$output .= ' value="' . $key_val . '"';
					$output .= $checked;
					$output .= ' />';
					$output .= esc_html( $name );
					$output .= '</label>';

				}
				break;

			case 'textarea':
				$output .= '<textarea id="' . $id_attr . '"';
				$output .= ' class="gmw-form-field textarea large-text"';
				$output .= ' cols="50" rows="3" name="' . $name_attr . '"';
				$output .= implode( ' ', $attributes );
				$output .= ' ' . $placeholder . '>';
				$output .= esc_textarea( $value );
				$output .= '</textarea>';

				break;

			case 'radio':
				$rc = 1;
				foreach ( $field['options'] as $key_val => $name ) {

					$checked = ( 1 === $rc ) ? 'checked="checked"' : checked( $value, $key_val, false );

					$output .= '<label>';
					$output .= '<input type="radio" id="' . $id_attr . '"';
					$output .= ' class="gmw-form-field ' . $field_name . ' radio"';
					$output .= ' name="' . $name_attr . '"';
					$output .= ' value="' . esc_attr( $key_val ) . '"';
					$output .= ' ' . $checked;
					$output .= ' />';
					$output .= esc_attr( $name );
					$output .= '</label>';
					$output .= '&nbsp;&nbsp;';

					$rc++;
				}
				break;

			case 'select':
				$output .= '<select id="' . $id_attr . '" class="gmw-form-field select"';
				$output .= ' name="' . $name_attr . '"';
				$output .= ' ' . implode( ' ', $attributes );
				$output .= '>';

				foreach ( $field['options'] as $key_val => $name ) {
					$output .= '<option value="' . esc_attr( $key_val ) . '" ' . selected( $value, $key_val, false ) . '>' . esc_html( $name ) . '</option>';
				}
				$output .= '</select>';

				break;

			case 'multiselect':
				$output .= '<select id="' . $id_attr . '" multiple';
				$output .= ' class="gmw-form-field multiselect regular-text"';
				$output .= ' name="' . $name_attr . '[]"';
				$output .= ' ' . implode( ' ', $attributes );
				$output .= '>';

				foreach ( $field['options'] as $key_val => $name ) {
					$selected = ( is_array( $value ) && in_array( $key_val, $value ) ) ? 'selected="selected"' : '';
					$output  .= '<option value="' . esc_attr( $key_val ) . '" ' . $selected . '>' . esc_html( $name ) . '</option>';
				}

				$output .= '</select>';

				break;

			case 'password':
				$output .= '<input type="password" id="' . $id_attr . '"';
				$output .= ' class="gmw-form-field regular-text password" name="' . $name_attr . '"';
				$output .= ' value="' . esc_attr( sanitize_text_field( $value ) ) . '"';
				$output .= ' ' . implode( ' ', $attributes );
				$output .= ' ' . $placeholder;
				$output .= '/>';

				break;

			case 'hidden':
				$output .= '<input type="hidden" id="' . $id_attr . '"';
				$output .= ' class="gmw-form-field hidden" name="' . $name_attr . '"';
				$output .= ' value="' . esc_attr( sanitize_text_field( $value ) ) . '"';
				$output .= ' ' . implode( ' ', $attributes );
				$output .= ' />';

				break;

			// number.
			case 'number':
				$output .= '<input type="number" id="' . $id_attr . '"';
				$output .= ' class="gmw-form-field number"';
				$output .= ' name="' . $name_attr . '"';
				$output .= ' value="' . esc_attr( sanitize_text_field( $value ) ) . '"';
				$output .= ' ' . implode( ' ', $attributes );
				$output .= ' />';

				break;
		}

		return $output;
	}

	/**
	 * Taxonomy group sorting
	 *
	 * @param  [type] $a [description].
	 *
	 * @param  [type] $b [description].
	 *
	 * @return [type]    [description]
	 */
	public static function sort_taxonomy_terms_groups( $a, $b ) {
		return strcmp( $a->taxonomy, $b->taxonomy );
	}

	/**
	 * Get terms taxonomy array
	 *
	 * @param  string  $taxonomy    taxonomy name.
	 *
	 * @param  array   $values      values.
	 *
	 * @param  boolean $sort_groups use option groups.
	 *
	 * @param  string  $field       field type.
	 *
	 * @return [type]               [description]
	 */
	public static function get_taxonomy_terms( $taxonomy = 'category', $values = array(), $sort_groups = false, $field = 'term_id' ) {

		if ( ! is_array( $values ) ) {
			$values = explode( ',', $values );
		}

		$terms = get_terms( $taxonomy, array( 'hide_empty' => false ) );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return;
		}

		if ( ! $sort_groups ) {

			if ( 'term_taxonomy_id' !== $field ) {
				$field = 'term_id';
			}

			$current_tax = $terms[0]->taxonomy;

			foreach ( $terms as $term ) {

				$selected = ( ! empty( $values ) && in_array( $term->$field, $values ) ) ? 'selected="selected"' : '';

				$term_id = esc_attr( $term->$field );
				$label   = esc_attr( $term->name );

				if ( IS_ADMIN ) {
					$label .= ' ( ID ' . $term_id . ' )';
				}

				echo '<option value="' . $term_id . '" ' . $selected . ' >' . $label . '</option>'; // WPCS: XSS ok.
			}
		} else {

			$current_tax = $terms[0]->taxonomy;

			usort( $terms, array( 'self', 'sort_taxonomy_terms_groups' ) );

			echo '<optgroup label="' . esc_attr( $current_tax ) . '">'; // WPCS: XSS ok.

			foreach ( $terms as $term ) {

				$selected = in_array( $term->term_taxonomy_id, $values ) ? 'selected="selected"' : '';

				if ( $term->taxonomy != $current_tax ) {

					echo '</optgroup>';
					$current_tax = $term->taxonomy;
					echo '<optgroup label="' . esc_attr( $term->taxonomy ) . '">';
				}

				$term_id = esc_attr( $term->term_taxonomy_id );
				$label   = esc_attr( $term->slug );

				if ( IS_ADMIN ) {
					$label .= ' ( ID ' . $term_id . ' )';
				}

				echo '<option value="' . $term_id . '" ' . $selected . ' >' . $label . '</option>'; // WPCS: XSS ok.
			}
		}
	}

	/**
	 * Get location meta fields
	 *
	 * @return [type] [description]
	 */
	public static function get_location_meta() {

		global $wpdb, $blog_id, $location_meta, $location_meta_status;

		if ( ! empty( $location_meta_status ) ) {

			return $location_meta;

		} else {

			$location_meta_status = true;

			$location_meta = $wpdb->get_col(
				$wpdb->prepare(
					"
					SELECT DISTINCT meta.`meta_key`
				 	FROM {$wpdb->base_prefix}gmw_locationmeta meta
				 	INNER JOIN {$wpdb->base_prefix}gmw_locations locations
				 	ON meta.location_id = locations.ID
				 	WHERE locations.blog_id = %d",
					array( $blog_id )
				)
			); // WPCS: db call ok, cache ok.

			if ( empty( $location_meta ) ) {
				return array();
			}

			$new_array = array();

			foreach ( $location_meta as $meta ) {

				// skip days_hours since it has its own settings.
				if ( 'days_hours' === $meta ) {
					continue;
				}

				$new_array[ $meta ] = $meta;
			}

			$location_meta = $new_array;

			return $location_meta;
		}
	}

	/**
	 * Address Field
	 *
	 * @param  [type] $value     field value.
	 *
	 * @param  [type] $name_attr name attribute.
	 */
	public static function address_field( $value, $name_attr ) {
		$name_attr = esc_attr( $name_attr );
		?>
		<div class="gmw-options-box gmw-address-fields-settings single">    	
			<div class="single-option label">	
					<label><?php esc_html_e( 'Label', 'geo-my-wp' ); ?></label>	
					<div class="option-content">
					<input 
						type="text" 
						id="gmw-form-address-field-label" 
						name="<?php echo $name_attr . '[label]'; // WPCS: XSS ok. ?>" 
						value="<?php echo isset( $value['label'] ) ? esc_attr( stripcslashes( $value['label'] ) ) : ''; ?>"
					/>	 
				</div>
			</div>

			<div class="single-option placeholder">	
					<label><?php esc_html_e( 'Placeholder', 'geo-my-wp' ); ?></label>	
					<div class="option-content">
					<input 
						type="text" 
						id="gmw-form-address-field-label" 
						name="<?php echo $name_attr . '[placeholder]'; // WPCS: XSS ok. ?>" 
						value="<?php echo isset( $value['placeholder'] ) ? esc_attr( stripcslashes( $value['placeholder'] ) ) : ''; ?>" 
					/>	 
				</div>
			</div>

			<div class="single-option locator">	
					<label>
					<input 
						type="checkbox" 
						value="1" 
						name="<?php echo $name_attr . '[locator]'; // WPCS: XSS ok. ?>" 
						<?php echo ! empty( $value['locator'] ) ? 'checked="checked"' : ''; ?>
					/>	 
					<?php esc_html_e( 'Locator Button', 'geo-my-wp' ); ?>
				</label>	
			</div>

			<?php
			$disabled = '';
			$warning  = '';

			if ( 'google_maps' !== GMW()->maps_provider ) {
				$disabled = 'disabled="disabled"';
				$warning  = ' <em style="color:red;font-size:11px;">Availabe with Google Maps provider only</em>.';
			}
			?>
			<div class="single-option autocomplete">	
					<label>
					<input 
						type="checkbox" 
						value="1" 
						name="<?php echo $name_attr . '[address_autocomplete]'; // WPCS: XSS ok. ?>" 
						<?php echo $disabled; // WPCS: XSS ok. ?>
						<?php echo ! empty( $value['address_autocomplete'] ) ? 'checked="checked"' : ''; ?>
					/>
					<?php esc_html_e( 'Address Autocomplete', 'geo-my-wp' ); ?>
					<?php echo $warning; // WPCS: XSS ok. ?>
				</label>
			</div>

			<div class="single-option locator-submit">	
					<label>
					<input 
						type="checkbox" 
						value="1" 
						name="<?php echo $name_attr . '[locator_submit]'; // WPCS: XSS ok. ?>" 
						<?php echo ! empty( $value['locator_submit'] ) ? 'checked="checked"' : ''; ?>
					/>
					<?php esc_html_e( 'Locator Submit', 'geo-my-wp' ); ?>
				</label>
			</div>

			<div class="single-option mandatory">	
				<label>	
					<input 
						type="checkbox" 
						value="1" 
						name="<?php echo $name_attr . '[mandatory]'; // WPCS: XSS ok. ?>" 
						<?php echo ! empty( $value['mandatory'] ) ? 'checked="checked"' : ''; ?>
					/>	
					<?php esc_html_e( 'Mandatory', 'geo-my-wp' ); ?> 
				</label>
			</div>			
		</div>
		<?php
	}

	/**
	 * Validate address field input in form settings
	 *
	 * @param  array $output input values before validation.
	 *
	 * @return array validated input
	 */
	public static function validate_address_field( $output ) {

		$output['label']                = sanitize_text_field( $output['label'] );
		$output['placeholder']          = sanitize_text_field( $output['placeholder'] );
		$output['locator']              = ! empty( $output['locator'] ) ? 1 : '';
		$output['locator_submit']       = ! empty( $output['locator_submit'] ) ? 1 : '';
		$output['mandatory']            = ! empty( $output['mandatory'] ) ? 1 : '';
		$output['address_autocomplete'] = ! empty( $output['address_autocomplete'] ) ? 1 : '';

		return $output;
	}

	/**
	 * Search form image
	 *
	 * @param  [type] $value     [description].
	 *
	 * @param  [type] $name_attr [description].
	 */
	public static function image( $value, $name_attr ) {

		$name_attr = esc_attr( $name_attr );

		if ( empty( $value ) ) {
			$value = array(
				'enabled' => '',
				'width'   => '100',
				'height'  => '100',
			);
		}
		?>
		<p>
			<label>
				<input 
					type="checkbox" 
					onclick="jQuery( this ).closest( 'td' ).find( '.featured-image-options' ).slideToggle();" 
					name="<?php echo $name_attr . '[enabled]'; // WPCS: XSS ok. ?>" 
					value="1" 
					<?php checked( '1', $value['enabled'] ); ?> 
				/>
				<?php esc_html_e( 'Enable', 'geo-my-wp' ); ?>
				</label>
		</p>

		<div class="featured-image-options gmw-options-box" <?php echo empty( $value['enabled'] ) ? 'style="display:none";' : ''; ?>>

			<div class="single-option">
				<label><?php esc_html_e( 'Width', 'geo-my-wp' ); ?></label>

				<div class="option-content">
					<input 
						type="text" 
						size="5" 
						name="<?php echo $name_attr . '[width]'; // WPCS: XSS ok. ?>" 
						value="<?php echo ! empty( $value['width'] ) ? esc_attr( $value['width'] ) : '100'; ?>" 
						placeholder="Numeric value"
					/>
				</div>
			</div>

			<div class="single-option">

				<label><?php esc_html_e( 'Height', 'geo-my-wp' ); ?></label>

				<div class="option-content">
					<input 
						type="text" 
						size="5" 
						name="<?php echo $name_attr . '[height]'; // WPCS: XSS ok. ?>" 
						value="<?php echo ! empty( $value['height'] ) ? esc_attr( $value['height'] ) : '100'; ?>"
						placeholder="Numeric value"
					/>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Validate image field
	 *
	 * @param  array $output Input values before validation.
	 *
	 * @return array         Input values after validation
	 */
	public static function validate_image( $output ) {

		$output['enabled'] = ! empty( $output['enabled'] ) ? 1 : '';
		$output['width']   = isset( $output['width'] ) ? preg_replace( '/[^0-9%xp]/', '', $output['width'] ) : '100';
		$output['height']  = isset( $output['height'] ) ? preg_replace( '/[^0-9%xp]/', '', $output['height'] ) : '100';

		return $output;
	}

	/**
	 * Taxonomies picker.
	 *
	 * @param  [type] $value     [description].
	 *
	 * @param  [type] $name_attr [description].
	 *
	 * @param  [type] $form      [description].
	 */
	public static function taxonomies( $value, $name_attr, $form ) {
		?>
		<div id="taxonomies-wrapper" class="gmw-options-box">
			<?php
			foreach ( get_post_types() as $post ) {

				$taxes = get_object_taxonomies( $post );

				if ( ! empty( $taxes ) ) {

					$style = ( isset( $form['search_form']['post_types'] ) && ( count( $form['search_form']['post_types'] ) === 1 ) && is_array( $form['search_form']['post_types'] ) && ( in_array( $post, $form['search_form']['post_types'], true ) ) ) ? '' : 'style="display:none"';

					echo '<div id="post-type-' . esc_attr( $post ) . '-taxonomies-wrapper" class="single-option post-type-taxonomies-wrapper" ' . $style . '>'; // WPCS: XSS ok.

					foreach ( $taxes as $tax ) {

						echo '<label>' . esc_html( get_taxonomy( $tax )->labels->singular_name ) . '</label>';

						echo '<div id="' . esc_attr( $post ) . '_cat" class="taxonomy-wrapper option-content">';

							$esc_name_attr = esc_attr( $name_attr . '[' . $post . '][' . $tax . '][style]' );
							$selected      = ( ! empty( $value[ $post ][ $tax ]['style'] ) && ( 'dropdown' === $value[ $post ][ $tax ]['style'] || 'drop' === $value[ $post ][ $tax ]['style'] ) ) ? 'selected="seletced"' : '';

							echo '<select name="' . $esc_name_attr . '">'; // WPCS: XSS ok.
							echo '<option value="disable" checked="checked">' . esc_attr__( 'Disable', 'geo-my-wp' ) . '</option>';
							echo '<option value="dropdown" ' . $selected . '>' . esc_attr__( 'Dropdown', 'geo-my-wp' ) . '</option>'; // WPCS: XSS ok.
							echo '</select>';

						echo '</div>';
					}

					echo '</div>';
				}
			}
			echo '</div>';

			$style = ( empty( $form['search_form']['post_types'] ) || ( count( $form['search_form']['post_types'] ) === 0 ) ) ? '' : 'style="display: none;"';

			echo '<div id="post-types-select-taxonomies-message" ' . $style . '>'; // WPCS: XSS ok.
			echo '<p>' . esc_attr__( 'Select a post type to see its taxonomies.', 'geo-my-wp' ) . '</p>';
			echo '</div>';

			$style = ( isset( $form['search_form']['post_types'] ) && ( count( $form['search_form']['post_types'] ) === 1 ) ) ? 'style="display: none;"' : '';

			echo '<div id="post-types-no-taxonomies-message" ' . $style . '>'; // WPCS: XSS ok.
			echo '<p>' . esc_attr__( 'This feature is not availabe with multiple post types.', 'geo-my-wp' ) . '</p>';
			echo '</div>';
	}

	/**
	 * Excerpt settings.
	 *
	 * @param  [type] $value     [description].
	 *
	 * @param  [type] $name_attr [description].
	 */
	public static function excerpt( $value, $name_attr ) {

		$name_attr = esc_attr( $name_attr );

		if ( empty( $value ) ) {
			$value = array(
				'enabled' => '',
				'usage'   => 'post_content',
				'count'   => '10',
				'link'    => 'read more...',
			);
		}
		?>
		<p>
			<label>
				<input 
					type="checkbox" 
					value="1" 
					name="<?php echo $name_attr . '[enabled]'; // WPCS: XSS ok. ?>"
					onclick="jQuery( '.excerpt-options' ).slideToggle();" 
					<?php echo ! empty( $value['enabled'] ) ? 'checked=checked' : ''; ?> 
				/>
				<?php esc_attr_e( 'Enable', 'geo-my-wp' ); ?>
			</label>
		</p>

		<div class="excerpt-options gmw-options-box" <?php echo empty( $value['enabled'] ) ? 'style="display:none";' : ''; ?>>

			<div class="single-option">
				<label><?php esc_attr_e( 'Usage', 'geo-my-wp' ); ?></label>
				<div class="option-content">
					<select 
						name="<?php echo esc_attr( $name_attr . '[usage]' ); ?>"
						data-placehoder="<?php esc_attr_e( 'Select an option', 'geo-my-wp' ); ?>" 
					>
						<option value="post_content" selected="selected"><?php esc_attr_e( 'Post content', 'geo-my-wp' ); ?>
						<option value="post_excerpt" 
						<?php
						if ( ! empty( $value['usage'] ) && 'post_excerpt' === $value['usage'] ) {
							echo 'selected="selected"';
						};
						?>
						><?php esc_attr_e( 'Post excerpt', 'geo-my-wp' ); ?></option>
					</select>
					   
					<p class="description">
						<?php esc_attr_e( 'Selet the source of data between the post content or post excerpt.', 'geo-my-wp' ); ?>
					</p>
				</div>
			</div>

			<div class="single-option">
				<label><?php esc_attr_e( 'Words count', 'geo-my-wp' ); ?></label>
				<div class="option-content">
					<input 
						type="number" 
						name="<?php echo $name_attr . '[count]'; // WPCS: XSS ok. ?>"
						value="<?php echo ( ! empty( $value['count'] ) ) ? esc_attr( $value['count'] ) : ''; ?>"
						placeholder="Enter numeric value"
					/>
					<p class="description">
						<?php esc_attr_e( 'Enter the number of words that you would like to display, or leave blank to show the entire content.', 'geo-my-wp' ); ?>
					</p>
				</div>
			</div>

			<div class="single-option">
				<label><?php esc_attr_e( 'Read more link', 'geo-my-wp' ); ?></label>
				<div class="option-content">
					<input 
						type="text" 
						name="<?php echo $name_attr . '[link]'; // WPCS: XSS ok. ?>"
						value="<?php echo ( ! empty( $value['link'] ) ) ? esc_attr( stripslashes( $value['link'] ) ) : ''; ?>" 
						placeholder="Enter text"
					/>  
					<p class="description">
						<?php esc_attr_e( 'Enter a text that will be used as the "Read more" link and will link to the post page.', 'geo-my-wp' ); ?>
					</p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Validate excerpt
	 *
	 * @param  [type] $output [description].
	 *
	 * @return [type]         [description]
	 */
	public static function validate_excerpt( $output ) {

		$output['enabled'] = ! empty( $output['enabled'] ) ? 1 : '';
		$output['usage']   = ( 'post_content' === $output['usage'] || 'post_excerpt' === $output['usage'] ) ? $output['usage'] : 'post_content';
		$output['count']   = isset( $output['count'] ) ? preg_replace( '/[^0-9]/', '', $output['count'] ) : '';
		$output['link']    = isset( $output['link'] ) ? sanitize_text_field( $output['link'] ) : '';

		return $output;
	}

	/**
	 * Get BNP xprofile fields array
	 *
	 * @return [type]               [description]
	 */
	public static function get_xprofile_fields() {

		// verify BuddyPress plugin.
		if ( ! class_exists( 'Buddypress' ) ) {
			return array();
		}

		global $bp;

		// show message if Xprofile Fields component deactivated.
		if ( ! bp_is_active( 'xprofile' ) ) {

			gmw_trigger_error( esc_attr__( 'Buddypress xprofile fields component is deactivated. You need to activate in in order to use this feature.', 'geo-my-wp' ), E_USER_NOTICE );

			return array();
		}

		// check for profile fields.
		if ( function_exists( 'bp_has_profile' ) ) {

			$args = array(
				'hide_empty_fields' => false,
				'member_type'       => bp_get_member_types(),
			);

			$fields = array(
				'fields'     => array(),
				'date_field' => array(),
			);

			// display profile fields.
			if ( bp_has_profile( $args ) ) {

				while ( bp_profile_groups() ) {

					bp_the_profile_group();

					while ( bp_profile_fields() ) {

						bp_the_profile_field();

						$field_type = bp_get_the_profile_field_type();

						if ( 'datebox' === $field_type || 'birthdate' === $field_type ) {
							$fields['date_field'][ bp_get_the_profile_field_id() ] = bp_get_the_profile_field_name();
						} else {
							$fields['fields'][ bp_get_the_profile_field_id() ] = bp_get_the_profile_field_name();
						}
					}
				}
			}
		}

		return $fields;
	}

	/**
	 * Custom Fields Settings.
	 *
	 * @param  array $args   field args.
	 *
	 * @param  array $values field values.
	 */
	public static function get_custom_field( $args = array(), $values = array() ) {

		// Default args.
		$defaults = array(
			'option_name' => '',
			'name'        => '',
			'is_original' => true,
		);

		$args = wp_parse_args( $args, $defaults );

		// Default value.
		$defaults = array(
			'name'               => '',
			'usage'              => 'text',
			'type'               => 'CHAR',
			'date_type'          => 'yyyy/mm/dd',
			'compare'            => '=',
			'label'              => '',
			'second_label'       => '',
			'placeholder'        => '',
			'second_placeholder' => '',
			'value'              => '',
			'second_value'       => '',
			'required'           => 0,
		);

		$field_values = wp_parse_args( $values, $defaults );

		// Field options.
		$options = array(
			'usage'              => array(
				'hidden'      => 'Pre defined',
				'text'        => 'Textbox',
				'number'      => 'Number',
				'date'        => 'Date',
				'time'        => 'Time',
				'datetime'    => 'Date and Time',
				'select'      => 'Select dropdown',
				'multiselect' => 'Multi-select box',
				'checkboxes'  => 'Checkboxes',
				'radio'       => 'Radio buttons',
			),
			'smartbox'           => 0,
			'options'            => '',
			'second_options'     => '',
			'type'               => array(
				'CHAR'     => 'CHAR',
				'NUMERIC'  => 'NUMERIC',
				'BINARY'   => 'BINARY',
				'DATE'     => 'DATE',
				'TIME'     => 'TIME',
				'DECIMAL'  => 'DECIMAL',
				'SIGNED'   => 'SIGNED',
				'UNSIGNED' => 'UNSIGNED',
			),
			'date_type'          => array(
				'yyyy/mm/dd' => 'yyyy/mm/dd',
				'mm/dd/yyyy' => 'mm/dd/yyyy',
				'dd/mm/yyyy' => 'dd/mm/yyyy',
			),
			/*'time_format'    => array(
				'yyyy/mm/dd' => 'yyyy/mm/dd',
				'mm/dd/yyyy' => 'mm/dd/yyyy',
				'dd/mm/yyyy' => 'dd/mm/yyyy',
			),*/
			'step_time'          => '15',
			'compare'            => array( '=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN' /*'EXISTS', 'NOT EXISTS'*/ ),
			'date_compare'       => array( '=', '!=', '>', '>=', '<', '<=', 'BETWEEN', 'NOT BETWEEN' /*'EXISTS', 'NOT EXISTS'*/ ),
			'value'              => '',
			'second_value'       => '',
			'label'              => '',
			'second_label'       => '',
			'placeholderl'       => '',
			'second_placeholder' => '',
			'required'           => 0,
		);

		$option_name = esc_attr( $args['option_name'] );
		$field_name  = esc_attr( $args['name'] );
		$is_original = '';
		$disabled    = '';

		if ( $args['is_original'] ) {
			$is_original = 'original-field';
			$disabled    = 'disabled="disabled"';
		}
		?>
		<div class="gmw-custom-field-wrapper gmw-settings-group-wrapper <?php echo $is_original; // WPCS: XSS ok. ?>" data-field_name="<?php echo ( ! $is_original ) ? $field_name : ''; // WPCS: XSS ok. ?>">		

			<div class="custom-field-header gmw-settings-group-header">

				<i class="gmw-settings-group-drag-handle gmw-custom-field-sort-handle gmw-icon-sort gmw-tooltip" aria-label="<?php esc_attr_e( 'Drag to sort fields.', 'geo-my-wp' ); ?>" title="<?php esc_attr_e( 'Sort fields.', 'geo-my-wp' ); ?>"></i>
				<i class="gmw-settings-group-options-toggle gmw-custom-field-options-toggle gmw-icon-cog gmw-tooltip" aria-label="<?php esc_attr_e( 'Click to manage options.', 'geo-my-wp' ); ?>"></i>

				<span class="custom-field-label">
					<input 
						type="text"
						name="<?php echo $option_name . '[' . $field_name . '][name]';  // WPCS: XSS ok. ?>"
						value="<?php esc_attr_e( $field_values['name'] ); ?>"
						readonly="readonly"
						<?php echo $disabled; // WPCS: XSS ok. ?>
					/>
				</span>

				<i class="gmw-settings-group-delete-trigger gmw-custom-field-delete gmw-icon-cancel-circled gmw-tooltip" aria-label="<?php esc_attr_e( 'Click to delete field.', 'geo-my-wp' ); ?>"></i>
			</div>

			<div class="custom-field-settings gmw-settings-multiple-fields-wrapper gmw-settings-group-content">

				<div class="gmw-settings-panel-field custom-field-usage-option-wrap">

					<div class="gmw-settings-panel-header">
						<label class="gmw-settings-label"><?php esc_attr_e( 'Field Usage', 'geo-my-wp' ); ?></label>
					</div>

					<div class="custom-field-option-usage gmw-settings-panel-input-container">

						<select 
							<?php echo $disabled; // WPCS: XSS ok. ?>
							class="custom-field-usage-selector gmw-smartbox-not" 
							name="<?php echo $option_name . '[' . $field_name . '][usage]'; // WPCS: XSS ok. ?>"
						>
							<?php
							if ( empty( $field_values['usage'] ) ) {
								$field_values['usage'] = 'text';
							}
							?>

							<?php foreach ( $options['usage'] as $option_value => $option_label ) { ?>
								<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $option_value, $field_values['usage'], true ); ?>><?php echo esc_attr( $option_label ); ?></option>
							<?php } ?>
						</select>

					</div>

					<div class="gmw-settings-panel-description">
						<?php esc_attr_e( 'Select the field usage.', 'geo-my-wp' ); ?>
					</div>
				</div>

				<div class="gmw-settings-panel-field custom-field-smartbox-option-wrap" data-usage="select,multiselect">

					<div class="gmw-settings-panel-header">
						<label
							for="custom-field-smartbox-<?php echo $field_name; // WPCS: XSS ok. ?>"
							class="gmw-settings-label"><?php esc_html_e( 'Smart Select Field', 'geo-my-wp' ); ?></label>
					</div>

					<div class="custom-field-option-smartbox gmw-settings-panel-input-container">

						<?php
						if ( ! isset( $field_values['smartbox'] ) ) {
							$field_values['smartbox'] = 0;
						}
						?>

						<input
							id="custom-field-smartbox-<?php echo $field_name; // WPCS: XSS ok. ?>"
							type="checkbox"
							name="<?php echo $option_name . '[' . $field_name . '][smartbox]'; // WPCS: XSS ok. ?>"
							value="1"
							<?php echo $disabled; // WPCS: XSS ok. ?>
							<?php checked( $field_values['smartbox'], 1, true ); ?>
						/>

					</div>

					<div class="gmw-settings-panel-description">
						<?php esc_attr_e( 'Enable smart select field.', 'geo-my-wp' ); ?>
					</div>
				</div>

				<?php if ( isset( $options['type'] ) ) { ?>

					<div class="gmw-settings-panel-field custom-field-type-option-wrap" data-usage="text">

						<div class="gmw-settings-panel-header">
							<label class="gmw-settings-label"><?php esc_html_e( 'Field Type', 'geo-my-wp' ); ?></label>
						</div>

						<div class="custom-field-option-type gmw-settings-panel-input-container">

							<select 
								<?php echo $disabled; // WPCS: XSS ok. ?>
								class="custom-field-type-selector gmw-smartbox-not" 
								name="<?php echo $option_name . '[' . $field_name . '][type]'; // WPCS: XSS ok. ?>"
							>
								<?php
								if ( ! isset( $field_values['type'] ) ) {
									$field_values['type'] = 'CHAR';
								}
								?>

								<?php foreach ( $options['type'] as $option_value => $option_label ) { ?>
									<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $option_value, $field_values['type'], true ); ?>><?php echo esc_attr( $option_label ); ?></option>
								<?php } ?>
							</select>

						</div>

						<div class="gmw-settings-panel-description">
							<?php esc_html_e( 'Select the field type.', 'geo-my-wp' ); ?>
						</div>
					</div>

				<?php } ?>

				<?php if ( isset( $options['date_type'] ) ) { ?>

					<div class="gmw-settings-panel-field custom-field-date-format-option-wrap" data-usage="text,date,datetime">

						<div class="gmw-settings-panel-header">
							<label class="gmw-settings-label"><?php esc_html_e( 'Date Format', 'geo-my-wp' ); ?></label>
						</div>

						<div class="custom-field-option-date-format gmw-settings-panel-input-container">

							<select 
								<?php echo $disabled; // WPCS: XSS ok. ?>
								class="custom-field-date-format-selector gmw-smartbox-not" 
								name="<?php echo $option_name . '[' . $field_name . '][date_type]'; // WPCS: XSS ok. ?>">

								<?php
								if ( ! isset( $field_values['date_type'] ) ) {
									$field_values['date_type'] = 'yyyy/mm/dd';
								}
								?>

								<?php foreach ( $options['date_type'] as $option_value => $option_label ) { ?>
									<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $option_value, $field_values['date_type'], true ); ?>><?php echo esc_attr( $option_label ); ?></option>
								<?php } ?>	
							</select>
						</div>

						<div class="gmw-settings-panel-description">
							<?php esc_html_e( 'Select the date format.', 'geo-my-wp' ); ?>
						</div>
					</div>

				<?php } ?>

				<?php if ( isset( $options['step_time'] ) ) { ?>

					<div class="gmw-settings-panel-field custom-field-time-step-option-wrap" data-usage="time">

						<div class="gmw-settings-panel-header">
							<label class="gmw-settings-label"><?php esc_html_e( 'Time Step', 'geo-my-wp' ); ?></label>
						</div>

						<div class="custom-field-option-time-step gmw-settings-panel-input-container">

							<input 
								type="number" 
								name="<?php echo $option_name . '[' . $field_name . '][step_time]'; // WPCS: XSS ok. ?>" 
								value="<?php echo isset( $field_values['step_time'] ) ? esc_attr( stripcslashes( $field_values['step_time'] ) ) : ''; ?>"  
								<?php echo $disabled; // WPCS: XSS ok. ?>
							/>
						</div>

						<div class="gmw-settings-panel-description">
							<?php esc_html_e( 'Select the date format.', 'geo-my-wp' ); ?>
						</div>
					</div>

				<?php } ?>

				<?php if ( isset( $options['compare'] ) ) { ?>

					<div class="gmw-settings-panel-field custom-field-comparison-option-wrap" data-usage="hidden,text,number,select,radio">

						<div class="gmw-settings-panel-header">
							<label class="gmw-settings-label"><?php esc_html_e( 'Compare', 'geo-my-wp' ); ?></label>
						</div>

						<div class="custom-field-option-comparison gmw-settings-panel-input-container">

							<select 
								<?php echo $disabled; // WPCS: XSS ok. ?>
								class="custom-field-comparison-selector gmw-smartbox-not" 
								name="<?php echo $option_name . '[' . $field_name . '][compare]'; // WPCS: XSS ok. ?>"
							>
								<?php
								if ( ! isset( $field_values['compare'] ) ) {
									$field_values['compare'] = '=';
								}
								?>

								<?php foreach ( $options['compare'] as $option ) { ?>
									<option value="<?php echo esc_attr( str_replace( ' ', '_', $option ) ); ?>" <?php selected( $option, $field_values['compare'], true ); ?>><?php esc_attr_e( $option ); ?></option>
								<?php } ?>	
							</select>

						</div>

						<div class="gmw-settings-panel-description">
							<?php esc_html_e( 'Select the comparison operator.', 'geo-my-wp' ); ?>
						</div>
					</div>

				<?php } ?>

				<?php if ( isset( $options['date_compare'] ) ) { ?>

					<div class="gmw-settings-panel-field custom-field-date-comparison-option-wrap" data-usage="date">

						<div class="gmw-settings-panel-header">
							<label class="gmw-settings-label"><?php esc_html_e( 'Compare', 'geo-my-wp' ); ?></label>
						</div>

						<div class="custom-field-option-data-comparison gmw-settings-panel-input-container">

							<select 
								<?php echo $disabled; // WPCS: XSS ok. ?>
								class="custom-field-date-comparison-selector gmw-smartbox-not" 
								name="<?php echo $option_name . '[' . $field_name . '][date_compare]'; // WPCS: XSS ok. ?>"
							>
								<?php
								if ( ! isset( $field_values['date_compare'] ) ) {
									$field_values['date_compare'] = '=';
								}
								?>

								<?php foreach ( $options['date_compare'] as $option ) { ?>
									<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $option, $field_values['date_compare'], true ); ?>><?php echo esc_attr( $option ); ?></option>
								<?php } ?>	
							</select>

						</div>

						<div class="gmw-settings-panel-description">
							<?php esc_html_e( 'Select the comparison operator.', 'geo-my-wp' ); ?>
						</div>
					</div>

				<?php } ?>

				<?php if ( isset( $options['options'] ) ) { ?>

					<div class="gmw-settings-panel-field custom-field-options-option-wrap" data-usage="select,multiselect,checkboxes,radio">

						<div class="custom-field-option-label gmw-settings-panel-input-container gmw-settings-double-options-holder">

							<div class="gmw-settings-panel-inner-option">

								<div class="gmw-settings-panel-header">
									<label
										for="custom-field-options-<?php echo $field_name; // WPCS: XSS ok. ?>"
										class="gmw-settings-label"><?php esc_attr_e( 'Field Options', 'geo-my-wp' ); ?></label>
								</div>

								<div class="custom-field-option-options gmw-settings-panel-input-container">

									<?php
									if ( empty( $field_values['options'] ) ) {
										$field_values['options'] = '';
									}
									?>

									<textarea
										id="custom-field-options-<?php echo $field_name; // WPCS: XSS ok. ?>"
										name="<?php echo $option_name . '[' . $field_name . '][options]'; // WPCS: XSS ok. ?>"
										rows="10"
										cols="50"
										<?php echo $disabled; // WPCS: XSS ok. ?>
									/><?php echo isset( $field_values['options'] ) ? esc_textarea( stripcslashes( $field_values['options'] ) ) : ''; ?></textarea>

								</div>
							</div>

							<div class="gmw-settings-panel-inner-option custom-field-second-option">

								<div class="gmw-settings-panel-header">
									<label
										for="custom-field-options-<?php echo $field_name; // WPCS: XSS ok. ?>"
										class="gmw-settings-label"><?php esc_attr_e( 'Second Field Options', 'geo-my-wp' ); ?></label>
								</div>

								<div class="custom-field-option-options gmw-settings-panel-input-container">

									<?php
									if ( empty( $field_values['second_options'] ) ) {
										$field_values['second_options'] = '';
									}
									?>

									<textarea
										id="custom-field-options-<?php echo $field_name; // WPCS: XSS ok. ?>"
										name="<?php echo $option_name . '[' . $field_name . '][second_options]'; // WPCS: XSS ok. ?>"
										rows="10"
										cols="50"
										<?php echo $disabled; // WPCS: XSS ok. ?>
									/><?php echo isset( $field_values['second_options'] ) ? esc_textarea( stripcslashes( $field_values['second_options'] ) ) : ''; ?></textarea>

								</div>
							</div>

						</div>

						<div class="gmw-settings-panel-description">
							<?php esc_html_e( 'Enable smart select field.', 'geo-my-wp' ); ?>
						</div>
					</div>

				<?php } ?>

				<div class="gmw-settings-panel-field custom-field-label-option-wrap" data-usage="text,number,select,date,time,datetime,multiselect,checkboxes,radio">

					<div class="custom-field-option-label gmw-settings-panel-input-container gmw-settings-double-options-holder">

						<div class="gmw-settings-panel-inner-option">

							<div class="gmw-settings-panel-header">
								<label class="gmw-settings-label"><?php esc_attr_e( 'Field Label', 'geo-my-wp' ); ?></label>
							</div>

							<input 
								type="text" 
								name="<?php echo $option_name . '[' . $field_name . '][label]'; // WPCS: XSS ok. ?>" 
								value="<?php echo isset( $field_values['label'] ) ? esc_attr( stripcslashes( $field_values['label'] ) ) : ''; ?>"  
								<?php echo $disabled; // WPCS: XSS ok. ?>
							/>
						</div>

						<div class="gmw-settings-panel-inner-option custom-field-second-option">

							<div class="gmw-settings-panel-header">
								<label class="gmw-settings-label"><?php esc_attr_e( 'Second Field Label', 'geo-my-wp' ); ?></label>
							</div>

							<input 
								type="text" 
								name="<?php echo $option_name . '[' . $field_name . '][second_label]'; // WPCS: XSS ok. ?>" 
								value="<?php echo isset( $field_values['second_label'] ) ? esc_attr( stripcslashes( $field_values['second_label'] ) ) : ''; ?>"
								<?php echo $disabled; // WPCS: XSS ok. ?>
							/>
						</div>

					</div>

					<div class="gmw-settings-panel-description">
						<?php esc_attr_e( 'Enter the field lable or leave blank to hide it.', 'geo-my-wp' ); ?>
					</div>
				</div>

				<div class="gmw-settings-panel-field custom-field-placeholder-option-wrap" data-usage="text,number,date,time,datetime,select,multiselect">

					<div class="custom-field-option-placeholder gmw-settings-panel-input-container gmw-settings-double-options-holder">

						<div class="gmw-settings-panel-inner-option">

							<div class="gmw-settings-panel-header">
								<label class="gmw-settings-label"><?php esc_attr_e( 'Field Placeholder', 'geo-my-wp' ); ?></label>
							</div>

							<input 
								type="text" 
								name="<?php echo $option_name . '[' . $field_name . '][placeholder]'; // WPCS: XSS ok. ?>" 
								value="<?php echo isset( $field_values['placeholder'] ) ? esc_attr( stripcslashes( $field_values['placeholder'] ) ) : ''; ?>"  
								<?php echo $disabled; // WPCS: XSS ok. ?>
							/>
						</div>

						<div class="gmw-settings-panel-inner-option custom-field-second-option">

							<div class="gmw-settings-panel-header">
								<label class="gmw-settings-label"><?php esc_attr_e( 'Second Field Placeholder', 'geo-my-wp' ); ?></label>
							</div>

							<input 
								type="text" 
								name="<?php echo $option_name . '[' . $field_name . '][second_placeholder]'; // WPCS: XSS ok. ?>" 
								value="<?php echo isset( $field_values['second_placeholder'] ) ? esc_attr( stripcslashes( $field_values['second_placeholder'] ) ) : ''; ?>"
								<?php echo $disabled; // WPCS: XSS ok. ?>
							/>
						</div>
					</div>

					<div class="gmw-settings-panel-description">
						<?php esc_html_e( 'Enter the placeholder or leave blank to hide it.', 'geo-my-wp' ); ?>
					</div>
				</div>

				<?php if ( isset( $options['value'] ) ) { ?>

					<div class="gmw-settings-panel-field custom-field-value-option-wrap" data-usage="">

						<div class="custom-field-option-label gmw-settings-panel-input-container gmw-settings-double-options-holder">

							<div class="gmw-settings-panel-inner-option">

								<div class="gmw-settings-panel-header">
									<label class="gmw-settings-label"><?php esc_attr_e( 'Default Value', 'geo-my-wp' ); ?></label>
								</div>

								<div>
									<input 
										type="text" 
										name="<?php echo $option_name . '[' . $field_name . '][value]'; // WPCS: XSS ok. ?>" 
										value="<?php echo isset( $field_values['value'] ) ? esc_attr( stripcslashes( $field_values['value'] ) ) : ''; ?>"  
										<?php echo $disabled; // WPCS: XSS ok. ?>
									/>
								</div>
							</div>

							<div class="gmw-settings-panel-inner-option custom-field-second-option">

								<div class="gmw-settings-panel-header">
									<label class="gmw-settings-label"><?php esc_attr_e( 'Second Field Value', 'geo-my-wp' ); ?></label>
								</div>

								<div>
									<input 
										type="text" 
										name="<?php echo $option_name . '[' . $field_name . '][second_value]'; // WPCS: XSS ok. ?>" 
										value="<?php echo isset( $field_values['second_value'] ) ? esc_attr( stripcslashes( $field_values['second_value'] ) ) : ''; ?>"  
										<?php echo $disabled; // WPCS: XSS ok. ?>
									/>
								</div>
							</div>

						</div>

						<div class="gmw-settings-panel-description">
							<?php esc_attr_e( 'Enter a default value or leave blank.', 'geo-my-wp' ); ?>
						</div>
					</div>

				<?php } ?>

				<?php if ( isset( $options['required'] ) ) { ?>

					<div class="gmw-settings-panel-field custom-field-required-option-wrap" data-usage="text,number,select,date,time,datetime,multiselect,radio">

						<div class="gmw-settings-panel-header">

							<?php
							if ( ! isset( $field_values['required'] ) ) {
								$field_values['required'] = 0;
							}
							?>

							<input
								id="custom-field-required-?php echo $field_name; // WPCS: XSS ok. ?>"
								type="checkbox"
								name="<?php echo $option_name . '[' . $field_name . '][required]'; // WPCS: XSS ok. ?>"
								value="1"
								<?php echo $disabled; // WPCS: XSS ok. ?>
								<?php checked( $field_values['required'], 1, true ); ?>
							/>
							<label
								for="custom-field-required-<?php echo $field_name; // WPCS: XSS ok. ?>"
								class="gmw-settings-label"><?php esc_attr_e( 'Required', 'geo-my-wp' ); ?></label>
						</div>

						<div class="gmw-settings-panel-description">
							<?php esc_html_e( 'Make this field required.', 'geo-my-wp' ); ?>
						</div>
					</div>

				<?php } ?>

			</div>
		</div>
		<?php
	}

	/**
	 * Output custom fields generateor.
	 *
	 * @param  [type] $args  [description].
	 *
	 * @param  [type] $value [description].
	 *
	 * @param  [type] $form  [description].
	 */
	public static function get_custom_fields( $args, $value, $form ) {

		$defaults = array(
			'option_name'         => '',
			'get_fields_function' => 'gmw_get_custom_fields',
			'select_field_label'  => __( ' -- Select Field -- ', 'geo-my-wp' ),
			'add_field_label'     => __( 'Add Field', 'geo-my-wp' ),
		);

		$args = wp_parse_args( $args, $defaults );
		?>
		<div class="gmw-custom-fields-wrapper">

			<div id="gmw-custom-fields-new-field-picker">

				<span>
					<select id="gmw-custom-fields-picker" class="gmw-smartbox" data-gmw_ajax_load_options="<?php esc_attr_e( $args['get_fields_function'] ); ?>">
						<option value=""><?php esc_attr_e( $args['select_field_label'] ); ?></option>
					</select>
				</span>

				<input 
					type="button" 
					class="gmw-new-custom-field-button gmw-settings-action-button button-primary" style="grid-column: span 1;margin-top: 0;padding: 13px;"
					form_id="<?php esc_attr_e( $form['ID'] ); ?>"
					value="<?php esc_attr_e( $args['add_field_label'] ); ?>"
				/>
			</div>

			<?php
				$args = array(
					'option_name' => $args['option_name'],
					'name'        => '%%field_name%%',
				);

				self::get_custom_field( $args, $value );
			?>
			<div id="custom-fields-holder" class="gmw-setting-groups-container gmw-settings-group-draggable-area">
				<?php

				if ( ! empty( $value ) ) {

					foreach ( $value as $field_name => $values ) {

						$args = array(
							'option_name' => $args['option_name'],
							'name'        => $field_name,
							'is_original' => false,
						);

						self::get_custom_field( $args, $values );
					}
				}
				?>
			</div>	
		</div>
		<?php
	}
}
