<?php
/**
 * GEO my WP Forms table.
 *
 * @since 4.0
 *
 * @author Eyal Fitoussi.
 *
 * @package geo-my-wp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GMW_Forms_Table class.
 *
 * @since 4.0
 *
 * @author Eyal Fitoussi.
 */
class GMW_Grid_Stack {

	public $slug = 'search_form_builder';

	public $id = 'search-form-builder';

	public $class = 'gmw-form-wrapper';

	public $form = array();

	public $title = 'Search Form';

	public $widgets = array();

	public $action_buttons = array();

	//public $inserter = array();

	public static $grids = array();

	public $options = array();

	public $single_instance_widgets = array();

	private $inserter = array();

	public $children = array();
	public $default_template = array();

	public $inserter_enabled = true;

	public $saved_data = '';

	public $saved_field = '';

	public $output_element = '';

	private $default_grid_options = array(
		//'gridInserter' => '.search-form-inserter',
		//'id'            => 'search-form-builder',
		'class'         => 'search-form-builder',
		//'acceptWidgets' => '.grid-stack-sub-grid',
		'acceptWidgets' => true,
		'animate'       => false,
		'cellHeight'    => 12,
		'columns'       => 12,
		'margin'        => 15,
		'minRow'        => 40,
		//'disableResize' => true,
		'resizable'     => array(
			'handles' => '0',
		),
		/*'draggable'     => array(
			'handle' => '.widget-action-drag',
		),*/
		'handle'        => '.widget-action-drag',
		'children'      => array(),
		//'gridInserter'  => '', // this option is specifically for GEO my WP.
		//'auto'          => true,
		//'nonce'         => '',
		//'alwaysShowResizeHandle' => 'mobile',
		//'cellHeightThrottle' => '100',
		//'columnOpts'    => array(),
		//'disableDrag'   => false,
		//disableResize   => false,
		//draggable       => array(),
		//dragOut         => false,
		//engineClass     => GridStackEngine,
		//sizeToContent   => false,
		//float           => float,
		//handle          => '.grid-stack-item-content',
		//handleClass     => 'grid-stack-item-content',
		//itemClass       => 'grid-stack-item',

		//marginTop       => 10,
		//marginRight     => 10,
		//marginBottom    => 10,
		//marginLeft         => 10,
		//maxRow             => 0,

		//'placeholderClass' => 'grid-stack-placeholder',
		//min-height         => '0',
		//placeholderText    => '',

		//'removable'     => false,
		//removeTimeout => 2000,
		//row           => 0,
		//rtl             => 'auto',
		//staticGrid      => false,
		//styleInHead     => false,

		//'acceptWidgets' => true,
		//'float'         => false,


		/*'handle'        => '.widget-action-drag',
		'draggable'     => array(
			'handle' => '.widget-action-drag',
		),*/
		//'removable'     => false,
	);

	/**
	 * [__construct description]
	 */
	public function __construct( $args, $form ) {

		$this->enqueue_scripts();

		$this->form           = $form;
		$this->options        = gmw_wp_parse_args_recursive( $this->options, $this->default_grid_options );
		$this->action_buttons = $this->get_action_buttons();
		$this->widgets        = $this->generate_widgets_data();
		$this->saved_data     = ! empty( $form['search_form_builder'][ $this->saved_field ] ) ? json_decode( $form['search_form_builder'][ $this->saved_field ], true ) : '';
	}

	public function enqueue_scripts() {

		if ( ! wp_script_is( 'gmw-grid-stack', 'enqueued' ) ) {
			wp_enqueue_style( 'grid-stack', 'https://cdnjs.cloudflare.com/ajax/libs/gridstack.js/10.1.2/gridstack.min.css', array(), GMW_VERSION );
			wp_enqueue_style( 'grid-stack-extra', 'https://cdnjs.cloudflare.com/ajax/libs/gridstack.js/10.1.2/gridstack-extra.min.css', array(), GMW_VERSION );
			wp_enqueue_style( 'gmw-grid-stack', GMW_URL . '/includes/grid-stack/assets/css/gmw.gridStack.css', array(), GMW_VERSION );
			wp_enqueue_script( 'grid-stack', 'https://cdnjs.cloudflare.com/ajax/libs/gridstack.js/10.1.2/gridstack-all.min.js', array( 'jquery' ), GMW_VERSION, true );
			wp_enqueue_script( 'gmw-grid-stack', GMW_URL . '/includes/grid-stack/assets/js/gmw.gridStack.js', array( 'grid-stack' ), GMW_VERSION, true );
		}
	}

	public static function localize_scripts() {
		wp_localize_script( 'gmw-grid-stack', 'gmwGridStacks', self::$grids );
	}

	public function get_widgets() {

		return array(
			'default_widget' => array(
				'id'               => '',
				'autoPosition'     => true,
				'w'                => '',
				'h'                => 13,
				'x'                => '',
				'y'                => 0,
				'minW'             => 2,
				'maxW'             => '',
				'minH'             => '',
				'maxH'             => '',
				'locked'           => false,
				'noResize'         => false,
				'noMove'           => false,
				'content'          => '',
				'sizeToContent'    => false,
				'function'         => '',
				'preserve_content' => false,
				'label'            => 'default',
				'icon'             => '',
				'inserter'         => false,
				'single_instance'  => false,
				'action_buttons'   => array( 'move', 'remove' ),
			),
			'columns'        => array(
				'id'               => 'columns',
				'atuoPosition'     => true,
				'x'                => 0,
				'w'                => 12,
				'h'                => 14,
				'y'                => 0,
				'noResize'         => true,
				'removable'        => true,
				'handle'           => '.widget-action-drag',
				'subGridOpts'      => array(
					'removable'     => false,
					'animate'       => false,
					'cellHeight'    => 11,
					'maxRow'        => 15,
					'acceptWidgets' => '.grid-stack-item:not( .grid-stack-sub-grid ):not( [gs-id="columns"] )',
					'marginTop'     => 2,
					'marginBottom'  => 14,
					'marginRight'   => 4,
					'marginLeft'    => 5,
					'noResize'      => false,
					'resizable'     => array(
						'handles' => 'e',
					),
					'handle'        => '.widget-action-drag',
					'children'      => array(),
					'columnOpts'    => array(
						//'columnWidth' => 200,
						'layout' => 'moveScale'
						//columnWidth: 'auto', // wanted width
					),
				),
				'function'         => '',
				'preserve_content' => false,
				'label'            => __( 'Columns', 'geo-my-wp' ),
				'icon'             => 'dashicons dashicons-columns',
				'inserter'         => true,
				'single_instance'  => false,
				'action_buttons'   => array( 'move', 'remove' ),
			),
			'spacer'         => array(
				'id'               => 'spacer',
				'autoPosition'     => true,
				'w'                => '',
				'h'                => 13,
				'x'                => '',
				'y'                => 0,
				'minW'             => 2,
				'maxW'             => '',
				'minH'             => '',
				'maxH'             => '',
				'locked'           => false,
				'noResize'         => false,
				'noMove'           => false,
				'content'          => '<div class="gmw-grid-spacer-title">Spacer</div>',
				'sizeToContent'    => false,
				'function'         => '',
				'preserve_content' => false,
				'label'            => __( 'Spacer', 'geo-my-wp' ),
				'icon'             => 'dashicons dashicons-arrow-up-alt',
				'inserter'         => true,
				'single_instance'  => false,
				'action_buttons'   => array( 'move', 'remove' ),
			),
			'action_hook'    => array(
				'id'               => 'action_hook',
				'autoPosition'     => true,
				'w'                => '',
				'h'                => 13,
				'x'                => '',
				'y'                => 0,
				'minW'             => 2,
				'maxW'             => '',
				'minH'             => '',
				'maxH'             => '',
				'locked'           => false,
				'noResize'         => false,
				'noMove'           => false,
				'content'          => '<div class="action-hooks-widget-content"><label>Action name</label><div class="action-hook-field" contentEditable="true">action_name</div></div>',
				'preserve_content' => true,
				'sizeToContent'    => false,
				'function'         => '',
				'label'            => __( 'Action Hooks', 'geo-my-wp' ),
				'icon'             => 'dashicons dashicons-arrow-up-alt',
				'inserter'         => true,
				'single_instance'  => false,
				'action_buttons'   => array( 'move', 'remove' ),
			),
		);
	}

	public function generate_widgets_data() {

		$this->widgets = $this->get_widgets();

		foreach ( $this->widgets as &$widget ) {

			if ( ! empty( $widget['content'] ) && ! empty( $widget['preserve_content'] ) ) {

			} elseif ( ! empty( $widget['function'] ) ) {
				$widget['content'] = $this->gmw_get_field_content( $widget['function'] );
			}

			if ( ! empty( $widget['action_buttons'] ) ) {

				if ( ! is_array( $widget['action_buttons'] ) ) {
					$widget['action_buttons'] = explode( ',', $widget['action_buttons'] );
				}

				/*$action_buttons = '<div class="grid-item-action-buttons" style="display:none">';

				foreach ( $widget['action_buttons'] as $btn_id ) {

					if ( isset( $this->action_buttons[ $btn_id ] ) ) {

						$button = $this->action_buttons[ $btn_id ];
						$data   = '';

						if ( ! empty( $button['data'] ) ) {
							foreach ( $button['data'] as $data_attr => $data_value ) {
								$data .= 'data-' . esc_attr( $data_attr ) . '="' . esc_attr( $data_value ) . '" ';
							}
						}

						$action_buttons .= '<span class="gmw-item-action-button ' . esc_attr( $button['class'] ) . '"' . $data . '>';
						$action_buttons .= '<i class="' . esc_attr( $button['icon'] ) . '">' . esc_attr( $button['label'] ) . '</i>';
						$action_buttons .= '</span>';
					}
				}

				$action_buttons .= '</div>';*/

				if ( ! isset( $widget['content'] ) ) {
					$widget['content'] = '';
				}

				//$widget['content'] .= $action_buttons;
			}

			if ( $widget['inserter'] ) {
				$this->inserter[ $widget['id'] ] = $this->get_inserter_item( $widget );
			}
		}

		return apply_filters(
			'gmw_grid_stack_get_widgets',
			$this->widgets,
			$this
		);
	}

	public function get_widget( $widget ) {
		return ! empty( $this->widgets[ $widget['id'] ] ) ? gmw_wp_parse_args_recursive( $widget, $this->widgets[ $widget['id'] ] ) : gmw_wp_parse_args_recursive( $widget, $this->widgets['default_widget'] );
	}

	public function get_inserter_item( $widget ) {

		$width = ! empty( $widget['w'] ) ? $widget['w'] : $widget['minW'];

		$output  = '<div class="grid-stack-item grid-item-inserter" gs-id="' . esc_attr( $widget['id'] ) . '" gs-x="0" gs-w="' . esc_attr( $width ) . '" gs-h="' . esc_attr( $widget['h'] ) . '">';
		$output .= '<div class="grid-stack-item-content">';
		$output .= '<div class="grid-item-inserter-content">';
		$output .= '<div class="grid-inserter-icon"><i class="' . esc_attr( $widget['icon'] ) . '"></i></div>';
		$output .= '<div class="grid-inserter-label">' . esc_attr( $widget['label'] ) . '</div>';
		$output .= '</div>';
		$output .= '<div class="grid-item-content-holder" style="display: none;">' . $widget['content'] . '</div>';
		$output .= '</div>';
		$output .= '</div>';

		return $output;
	}

	public function get_inserter() {

		if ( ! $this->inserter_enabled ) {
			return;
		}

		$this->inserter = apply_filters(
			'gmw_grid_stack_inserter_items',
			$this->inserter,
			$this
		);

		if ( empty( $this->inserter ) ) {
			return;
		}

		$output  = '<div class="gmw-grid-stack-inserter-wrapper">';
		$output .= '<div class="gmw-grid-stack-inserter">';

		foreach ( $this->inserter as $item ) {
			$output .= $item;
		};

		$output .= '</div>';
		$output .= '</div>';

		return $output;
	}

	public function get_action_buttons() {

		return apply_filters(
			'gmw_grid_stack_action_buttons',
			array(
				'move'            => array(
					'id'    => 'move',
					'icon'  => 'dashicons dashicons-move',
					'label' => '',
					'class' => 'widget-action-drag',
				),
				'remove'          => array(
					'id'    => 'remove',
					'icon'  => 'dashicons dashicons-trash',
					'label' => '',
					'class' => 'widget-action-remove',
				),
				'column_size_15'  => array(
					'id'        => 'column_size_15',
					'icon'      => '',
					'label'     => '15%',
					'class'     => 'widget-action-columns-size',
					'data_attr' => array(
						'widget_size' => 2,
					),
				),
				'column_size_25'  => array(
					'id'        => 'column_size_25',
					'icon'      => '',
					'label'     => '25%',
					'class'     => 'widget-action-columns-size',
					'data_attr' => array(
						'widget_size' => 3,
					),
				),
				'column_size_33'  => array(
					'id'        => 'column_size_33',
					'icon'      => '',
					'label'     => '33%',
					'class'     => 'widget-action-columns-size',
					'data_attr' => array(
						'widget_size' => 4,
					),
				),
				'column_size_50'  => array(
					'id'        => 'column_size_50',
					'icon'      => '',
					'label'     => '50%',
					'class'     => 'widget-action-columns-size',
					'data_attr' => array(
						'widget_size' => 6,
					),
				),
				'column_size_100' => array(
					'id'        => 'column_size_100',
					'icon'      => '',
					'label'     => '100%',
					'class'     => 'widget-action-columns-size',
					'data_attr' => array(
						'widget_size' => 12,
					),
				),
			),
			$this
		);
	}

	public function gmw_get_field_content( $function = '' ) {

		$output = '';

		if ( ! empty( $function ) && function_exists( $function ) ) {
			$output = call_user_func( $function, $this->form );
		}

		return $output;
	}

	public function get_children() {

		if ( ! empty( $this->saved_data ) ) {
			$template = $this->saved_data['children'];
		} else {
			$template = $this->default_template;
		}

		foreach ( $template as $row => &$child ) {

			if ( ! is_array( $child ) ) {
				continue;
			}

			/*if ( empty( $child['subGridOpts'] ) ) {

				$columns                              = $this->widgets['columns'];
				$columns['subGridOpts']['children'][] = $child;
				$child                                = $columns;

			}*/

			$child = $this->get_widget( $child );

			if ( $child['single_instance'] ) {

				if ( in_array( $child['id'], $this->single_instance_widgets, true ) ) {

					unset( $template[ $row ] );

					continue;

				} else {
					$this->single_instance_widgets[] = $child['id'];
				}
			}

			if ( ! empty( $child['subGridOpts'] ) ) {

				foreach ( $child['subGridOpts']['children'] as $key => &$sub_child ) {

					$sub_child = $this->get_widget( $sub_child );

					if ( empty( $sub_child['content'] ) ) {
						unset( $child['subGridOpts']['children'][ $key ] );
					}

					if ( $sub_child['single_instance'] ) {

						if ( in_array( $sub_child['id'], $this->single_instance_widgets, true ) ) {
							unset( $child['subGridOpts']['children'][ $key ] );
						} else {
							$this->single_instance_widgets[] = $sub_child['id'];
						}
					}
				}

				$column_count = 0 === count( $child['subGridOpts']['children'] ) ? 12 : 12 / count( $child['subGridOpts']['children'] );

				foreach ( $child['subGridOpts']['children'] as $key => &$sub_child ) {

					if ( empty( $sub_child['w'] ) ) {
						$sub_child['w'] = $column_count;
					}
				}

				$child['subGridOpts']['children'] = array_values( $child['subGridOpts']['children'] );

			} else {

				$child['w'] = 12;

				if ( empty( $child['content'] ) ) {
					unset( $template[ $row ] );
				}

				if ( $child['single_instance'] ) {

					if ( in_array( $child['id'], $this->single_instance_widgets, true ) ) {
						unset( $template[ $row ] );
					} else {
						$this->single_instance_widgets[] = $child['id'];
					}
				}
			}

			if ( empty( $child['y'] ) ) {
				$child['y']            = (int) $row * 13;
				$child['autoPosition'] = false;
			}
		}


		$template = array_values( $template );
		return $template;
	}

	/**
	 * No forms found message.
	 */
	public function output() {

		$grid             = $this->options;
		$grid['children'] = $this->get_children();
		?>
		<div id="<?php echo esc_attr( $this->id ); ?>" class="gmw-grid-stack-wrapper">

			<?php if ( ! empty( $this->title ) ) { ?>
				<div class="gmw-grid-stack-title"><?php echo esc_attr( $this->title ); ?></div>
			<?php } ?>

			<div class="gmw-grid-stack-inner">
				<?php echo $this->get_inserter(); ?>

				<div class="gmw-grid-stack-content">

					<div class="action-buttons-holder">

						<?php
						$action_buttons = '';
						foreach ( $this->action_buttons as $button ) {

							//if ( isset( $this->action_buttons[ $btn_id ] ) ) {

								//$button = $this->action_buttons[ $btn_id ];
								$data   = '';

								if ( ! empty( $button['data'] ) ) {
									foreach ( $button['data'] as $data_attr => $data_value ) {
										$data .= 'data-' . esc_attr( $data_attr ) . '="' . esc_attr( $data_value ) . '" ';
									}
								}

								$action_buttons .= '<span data-id="' . $button['id'] . '" class="gmw-item-action-button ' . esc_attr( $button['class'] ) . '"' . $data . '>';
								$action_buttons .= '<i class="' . esc_attr( $button['icon'] ) . '">' . esc_attr( $button['label'] ) . '</i>';
								$action_buttons .= '</span>';
							//}
						}

						echo $action_buttons;
						?>
					</div>
					<div class="gmw-grid-stack gmw-element-wrapper <?php echo esc_attr( $this->options['class'] ); ?> <?php echo esc_attr( $this->class ); ?>"></div>
				</div>
			</div>

		</div>
		<?php

		self::$grids[ $this->slug ] = array(
			'options' => array(
				'slug'          => $this->slug,
				'id'            => $this->id,
				'widgets'       => $this->widgets,
				'outputElement' => $this->output_element,
			),
			'grid'    => $grid,
		);
	}
}
add_action( 'admin_footer', array( 'GMW_Grid_Stack', 'localize_scripts' ) );

class GMW_Search_Form_Builder extends GMW_Grid_Stack {

	public $slug = 'search_form_builder';

	public $id = 'search-form-builder';

	public $class = 'gmw-form-wrapper';

	public $title = 'Search Form';

	public $options = array(
		'class' => 'search-form-builder',
	);

	public $saved_field = 'search_form_value';

	public $output_element = '#setting-search_form_builder-search_form_value';

	public $default_template = array(
		array(
			'id'          => 'columns',
			'subGridOpts' => array(
				'children' => array(
					array(
						'id' => 'keywords_field',
					),
					array(
						'id' => 'address_field',
					),
					array(
						'id' => 'radius_field',
					),
					array(
						'id' => 'units_field',
					),
				),
			),
		),
		array(
			'id'          => 'columns',
			'subGridOpts' => array(
				'children' => array(
					array(
						'id' => 'locator_button',
					),
				),
			),
		),
		array(
			'id'          => 'columns',
			'subGridOpts' => array(
				'children' => array(
					array(
						'id' => 'modal_box_toggle',
					),
					array(
						'id' => 'reset_button',
					),
					array(
						'id' => 'submit_button',
					),
				),
			),
		),
		array(
			'id' => 'submit_button',
		),
		array(
			'id'       => 'custom_fields',
			'function' => 'gmw_get_search_form_custom_fields',
		),
	);

	public function get_widgets() {

		return parent::get_widgets() + array(
			'keywords_field'   => array(
				'id'              => 'keywords_field',
				'autoPosition'    => true,
				'w'               => '',
				'h'               => 13,
				'x'               => '',
				'y'               => 0,
				'minW'            => 2,
				'maxW'            => '',
				'minH'            => '',
				'maxH'            => '',
				'locked'          => false,
				'noResize'        => false,
				'noMove'          => false,
				'content'         => '',
				'sizeToContent'   => false,
				'function'        => 'gmw_get_search_form_keywords_field',
				'label'           => __( 'Keywords', 'geo-my-wp' ),
				'icon'            => 'dashicons dashicons-arrow-up-alt',
				'inserter'        => true,
				'single_instance' => true,
				'action_buttons'  => array( 'move', 'remove' ),
			),
			'address_field'    => array(
				'id'              => 'address_field',
				'autoPosition'    => true,
				'w'               => '',
				'h'               => 13,
				'x'               => '',
				'y'               => 0,
				'minW'            => 2,
				'maxW'            => '',
				'minH'            => '',
				'maxH'            => '',
				'locked'          => false,
				'noResize'        => false,
				'noMove'          => false,
				'content'         => '',
				'sizeToContent'   => false,
				'function'        => 'gmw_get_search_form_address_field',
				'label'           => __( 'Address', 'geo-my-wp' ),
				'icon'            => 'dashicons dashicons-arrow-up-alt',
				'inserter'        => true,
				'single_instance' => true,
				'action_buttons'  => array( 'move', 'remove' ),
			),
			'radius_field'     => array(
				'id'              => 'radius_field',
				'autoPosition'    => true,
				'w'               => '',
				'h'               => 13,
				'x'               => '',
				'y'               => 0,
				'minW'            => 2,
				'maxW'            => '',
				'minH'            => '',
				'maxH'            => '',
				'locked'          => false,
				'noResize'        => false,
				'noMove'          => false,
				'content'         => '',
				'sizeToContent'   => false,
				'function'        => 'gmw_get_search_form_radius',
				'label'           => __( 'Radius', 'geo-my-wp' ),
				'icon'            => 'dashicons dashicons-arrow-up-alt',
				'inserter'        => true,
				'single_instance' => true,
				'action_buttons'  => array( 'move', 'remove' ),
			),
			'units_field'      => array(
				'id'              => 'units_field',
				'autoPosition'    => true,
				'w'               => '',
				'h'               => 13,
				'x'               => '',
				'y'               => 0,
				'minW'            => 2,
				'maxW'            => '',
				'minH'            => '',
				'maxH'            => '',
				'locked'          => false,
				'noResize'        => false,
				'noMove'          => false,
				'content'         => '',
				'sizeToContent'   => false,
				'function'        => 'gmw_get_search_form_units',
				'label'           => __( 'Units', 'geo-my-wp' ),
				'icon'            => 'dashicons dashicons-arrow-up-alt',
				'inserter'        => true,
				'single_instance' => true,
				'action_buttons'  => array( 'move', 'remove' ),
			),
			'locator_button'   => array(
				'id'              => 'locator_button',
				'autoPosition'    => true,
				'w'               => '',
				'h'               => 13,
				'x'               => '',
				'y'               => 0,
				'minW'            => 2,
				'maxW'            => '',
				'minH'            => '',
				'maxH'            => '',
				'locked'          => false,
				'noResize'        => false,
				'noMove'          => false,
				'content'         => '',
				'sizeToContent'   => false,
				'function'        => 'gmw_get_search_form_locator_button',
				'label'           => __( 'Locator Button', 'geo-my-wp' ),
				'icon'            => 'dashicons dashicons-arrow-up-alt',
				'inserter'        => true,
				'single_instance' => false,
				'action_buttons'  => array( 'move', 'remove' ),
			),
			'modal_box_button' => array(
				'id'              => 'modal_box_button',
				'autoPosition'    => true,
				'w'               => '',
				'h'               => 13,
				'x'               => '',
				'y'               => 0,
				'minW'            => 2,
				'maxW'            => '',
				'minH'            => '',
				'maxH'            => '',
				'locked'          => false,
				'noResize'        => false,
				'noMove'          => false,
				'content'         => '',
				'sizeToContent'   => false,
				'function'        => 'gmw_get_search_form_modal_box_toggle',
				'label'           => __( 'Modal Box Button', 'geo-my-wp' ),
				'icon'            => 'dashicons dashicons-arrow-up-alt',
				'inserter'        => true,
				'single_instance' => false,
				'action_buttons'  => array( 'move', 'remove' ),
			),
			'reset_button'     => array(
				'id'              => 'reset_button',
				'autoPosition'    => true,
				'w'               => '',
				'h'               => 13,
				'x'               => '',
				'y'               => 0,
				'minW'            => 2,
				'maxW'            => '',
				'minH'            => '',
				'maxH'            => '',
				'locked'          => false,
				'noResize'        => false,
				'noMove'          => false,
				'content'         => '',
				'sizeToContent'   => false,
				'function'        => 'gmw_get_search_form_reset_button',
				'label'           => __( 'Reset Button', 'geo-my-wp' ),
				'icon'            => 'dashicons dashicons-arrow-up-alt',
				'inserter'        => true,
				'single_instance' => false,
				'action_buttons'  => array( 'move', 'remove' ),
			),
			'submit_button'    => array(
				'id'              => 'submit_button',
				'autoPosition'    => true,
				'w'               => '',
				'h'               => 13,
				'x'               => '',
				'y'               => 0,
				'minW'            => 2,
				'maxW'            => '',
				'minH'            => '',
				'maxH'            => '',
				'locked'          => false,
				'noResize'        => false,
				'noMove'          => false,
				'content'         => '',
				'sizeToContent'   => false,
				'function'        => 'gmw_get_search_form_submit_button',
				'label'           => __( 'Submit Button', 'geo-my-wp' ),
				'icon'            => 'dashicons dashicons-arrow-up-alt',
				'inserter'        => true,
				'single_instance' => false,
				'action_buttons'  => array( 'move', 'remove' ),
			),
		);
	}
}

class GMW_Search_Form_Modal_Box_Builder extends GMW_Search_Form_Builder {

	public $slug = 'search_form_modal_box_builder';

	public $id = 'search-form-modal-box-builder';

	public $class = 'gmw-form-wrapper';

	public $title = 'Modal Box';

	public $options = array(
		'class' => 'search-form-modal-box-builder',
	);
	public $saved_field = 'modal_box_value';

	public $output_element = '#setting-search_form_builder-modal_box_value';

	public $inserter_enabled = false;
	public $default_template = array(
		array(
			'id'          => 'columns',
			'subGridOpts' => array(
				'children' => array(
					array(
						'id'       => 'custom_fields',
					),
				),
			),
		),

	);
}

