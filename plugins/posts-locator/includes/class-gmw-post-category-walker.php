<?php
/**
 * GEO my WP posts category walker class.
 *
 * @package geo-my-wp.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'GMW_Post_Category_Walker' ) ) :

	/**
	 * GMW PT Category Walker class
	 */
	class GMW_Post_Category_Walker extends Walker {

		/**
		 * Taxonomy name
		 *
		 * @var string
		 */
		public $tree_type = 'category';

		/**
		 * DB fields.
		 *
		 * @var array
		 */
		public $db_fields = array(
			'parent' => 'parent',
			'id'     => 'term_id',
			'slug'   => 'slug',
		);

		/**
		 * Category icons.
		 *
		 * @var boolean
		 */
		public $category_icons = false;

		/**
		 * Start level.
		 *
		 * @param string $output Used to append additional content (passed by reference).
		 *
		 * @param int    $depth  Depth of the item.
		 *
		 * @param array  $args   An array of additional arguments.
		 *
		 * @return [type]           [description]
		 */
		public function start_lvl( &$output, $depth = 0, $args = array() ) {

			if ( 'checkbox' !== $args['usage'] ) {
				return;
			}

			$pad     = str_repeat( '&nbsp;', $depth * 3 );
			$output .= "<ul class='gmw-checkbox-children gmw-checkbox-level-{$depth}'>\n";
		}

		/**
		 * End level.
		 *
		 * @param string $output Used to append additional content (passed by reference).
		 *
		 * @param int    $depth  Depth of the item.
		 *
		 * @param array  $args   An array of additional arguments.
		 *
		 * @return [type]           [description]
		 */
		public function end_lvl( &$output, $depth = 0, $args = array() ) {

			if ( 'checkbox' !== $args['usage'] ) {
				return;
			}

			$pad     = str_repeat( '&nbsp;', $depth * 3 );
			$output .= '</ul>';
		}

		/**
		 * Start el.
		 *
		 * @param string $output            Used to append additional content (passed by reference).
		 * @param object $term              The data object.
		 * @param int    $depth             Depth of the item.
		 * @param array  $args              An array of additional arguments.
		 * @param int    $current_object_id ID of the current item.
		 *
		 * @return [type]                     [description]
		 */
		public function start_el( &$output, $term, $depth = 0, $args = array(), $current_object_id = 0 ) {

			if ( ! empty( $args['hierarchical'] ) ) {
				$pad = str_repeat( '&nbsp;', $depth * 3 );
			} else {
				$pad = '';
			}

			$value     = ! empty( $args['selected'] ) ? $args['selected'] : array();
			$term_name = $args['show_count'] ? $term->name . '&nbsp;(' . $term->count . ')' : $term->name;
			$term_id   = absint( $term->term_id );

			if ( 'checkbox' === $args['usage'] ) {

				$checked             = '';
				$icon_checked        = '';
				$category_icon_ok    = false;
				$category_icon_class = '';

				// get icons only once.
				if ( $args['category_icons'] && ! $this->category_icons ) {
					$icons                = gmw_get_icons();
					$this->category_icons = $icons['pt_category_icons'];
				}

				if ( in_array( $term->term_id, $value ) ) {
					$checked      = 'checked="checked"';
					$icon_checked = 'checked';
				}

				if ( isset( $this->category_icons['set_icons'][ $term->term_id ] ) ) {
					$category_icon_ok    = true;
					$category_icon_class = ' category-icon';
					$icon                = esc_url( $this->category_icons['url'] . $this->category_icons['set_icons'][ $term->term_id ] );
				}

				$checkbox  = '<li class="gmw-taxonomy-checkbox-wrapper term-' . $term_id . $category_icon_class . '">';
				$checkbox .= '<label>';
				$checkbox .= '<input type="checkbox" name="tax[' . esc_attr( $args['taxonomy'] ) . '][]" id="' . $term_id . '" class="gmw-taxonomy-checkbox" value="' . $term_id . '" ' . $checked . '/>';

				if ( $category_icon_ok ) {
					$checkbox .= '<img class="category-icon gmw-checkbox-cat-icon' . $icon_checked . '" src="' . $icon . '" onclick="jQuery(this).toggleClass(\'checked\');" />';
				}

				$checkbox .= esc_html( $term_name );
				$checkbox .= '</label>';
				$checkbox .= '</li>';
				$output   .= $checkbox;

			} else {
				$selected = in_array( $term->term_id, $value ) ? 'selected="selected"' : '';
				$output  .= "\t<option class=\"level-$depth\" value=\"$term_id\" $selected>$pad $term_name</option>\n";
			}

			return $output;
		}
	}

endif;
