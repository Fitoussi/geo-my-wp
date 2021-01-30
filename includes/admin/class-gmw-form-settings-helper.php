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
	 * Form settings xprofile fields function.
	 *
	 * @param  [type] $value     [description].
	 *
	 * @param  [type] $name_attr [description].
	 *
	 * @return [type]            [description]
	 */
	public static function bp_xprofile_fields( $value, $name_attr ) {

		global $bp;

		// show message if Xprofile Fields component deactivated.
		if ( ! class_exists( 'Buddypress' ) || ! bp_is_active( 'xprofile' ) ) {
			return esc_attr_e( 'Buddypress xprofile fields component is required for this feature.', 'geo-my-wp' );
		}

		$fields = self::get_xprofile_fields();

		if ( ! array_filter( $fields ) ) {
			return array();
		}
		?>
		<div class="gmw-options-box">
			<div class="single-option">
				<label><?php esc_attr_e( 'Select Profile Fields', 'geo-my-wp' ); ?></label>
				<div class="option-content">
					<select 
						name="<?php echo esc_attr( $name_attr . '[fields][]' ); ?>" 
						multiple 
						data-placehoder="<?php esc_attr_e( 'Select profile fields', 'geo-my-wp' ); ?>" 
					>
					<?php
					foreach ( $fields['fields'] as $field_id => $field_name ) {
						$selected = ( isset( $value['fields'] ) && in_array( $field_id, $value['fields'] ) ) ? 'selected="slected"' : '';
						?>
							?>
							<option value="<?php echo esc_attr( $field_id ); ?>" <?php echo $selected; // WPCS: XSS OK. ?>>
								<?php echo esc_html( $field_name ); ?>
							</option>
							<?php
					}
					?>
					</select>
					<p class="description">
						<?php esc_attr_e( 'Select the profile fields to be used as filters in the search form.', 'geo-my-wp' ); ?>
					</p>
				</div>

				<label><?php esc_attr_e( 'Select date field as "Age range" filter.', 'geo-my-wp' ); ?></label>   

					<div class="option-content">
						<select name="<?php echo esc_attr( $name_attr . '[date_field]' ); ?>">
							<option value="" selected="selected"><?php esc_attr_e( 'N/A', 'geo-my-wp' ); ?></option>
							<?php foreach ( $fields['date_field'] as $field_value => $field_name ) { ?>
								<?php $selected = ( ! empty( $value['date_field'] ) && $value['date_field'] == $field_value ) ? 'selected="selected"' : ''; ?>
								<option value="<?php echo esc_attr( $field_value ); ?>" <?php echo $selected; // WPCS: XSS OK. ?> >
									<?php echo esc_html( $field_name ); ?>        
								</option>
							<?php } ?>
						</select>
						<p class="description">
							<?php esc_attr_e( 'select a date field to be used as a age range filter in the search form.', 'geo-my-wp' ); ?>
						</p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Validate xprofile fields.
	 *
	 * @param  [type] $output [description].
	 *
	 * @return [type]         [description]
	 */
	public static function validate_bp_xprofile_fields( $output ) {

		$output['fields']     = ! empty( $output['fields'] ) ? array_map( 'intval', $output['fields'] ) : array();
		$output['date_field'] = ! empty( $output['date_field'] ) ? intval( $output['date_field'] ) : '';

		return $output;
	}
}
