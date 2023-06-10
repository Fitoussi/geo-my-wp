<?php
/**
 * GMW base Block class.
 *
 * @author Eyal Fitoussi, Gravity Forms team
 *
 * This class was written by the Gravity Forms team and was modified to work with GEO my WP ( thank you!! ) .
 *
 * @since 4.0
 *
 * @package geo-my-wp
 */

// Abort if accessed directly or if GMW_Blocks was not found.
if ( ! class_exists( 'GMW_Blocks' ) || ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base GEO my WP Block class.
 *
 * @since 4.0
 *
 * Class GMW_Block
 */
class GMW_Block {

	/**
	 * Contains an instance of this block, if available.
	 *
	 * @since  4.0
	 *
	 * @var    GMW_Block $_instance If available, contains an instance of this block.
	 */
	private static $_instance = null;

	/**
	 * Block slug.
	 *
	 * @since 4.0
	 *
	 * @var   string
	 */
	public $slug = '';

	/**
	 * Block type.
	 *
	 * @since 4.0
	 *
	 * @var   string
	 */
	public $type = '';

	/**
	 * Form ID for blocks for GEO my WP' forms.
	 *
	 * @var integer
	 */
	public $form_id = 0;

	/**
	 * Allowed post types.
	 *
	 * Array of post types this block is allowed in.
	 *
	 * Empty array for all post types.
	 *
	 * @since 4.0
	 *
	 * @var   string
	 */
	public $allowed_post_types = array();

	/**
	 * Disallowed post types.
	 *
	 * Array of post types this block is disallowed from.
	 *
	 * Empty array for all post types.
	 *
	 * @since 4.0
	 *
	 * @var   string
	 */
	public $disallowed_post_types = array();

	/**
	 * Block path.
	 *
	 * @since 4.0
	 *
	 * @var [type]
	 */
	public $path = __DIR__;

	/**
	 * Path to build folder.
	 *
	 * @since 4.0
	 *
	 * @var string
	 */
	public $build_path = '';

	/**
	 * Render via Server side.
	 *
	 * @since 4.0
	 *
	 * @var string
	 */
	public $server_side_render = true;

	/**
	 * Handle of primary block editor script.
	 *
	 * @since 4.0
	 *
	 * @var   string
	 */
	public $script_handle = '';

	/**
	 * Handle of primary block editor style.
	 *
	 * @since 4.0
	 *
	 * @var   string
	 */
	public $style_handle = '';

	/**
	 * Block attributes.
	 *
	 * Attributes already added in the block.json file but can be overriden here.
	 *
	 * @since 4.0
	 *
	 * @var   array
	 */
	public $attributes = array();

	/**
	 * Register block type.
	 *
	 * Enqueue editor assets.
	 *
	 * @since  4.0
	 *
	 * @uses   GMW_Block::register_block_type()
	 */
	public function init() {

		// Blocks require WP v5.8.
		if ( version_compare( get_bloginfo( 'version' ), '5.8', '<' ) ) {
			return;
		}

		$this->register_block_type();
		$this->register_block_assets();
	}

	/**
	 * Get block slug.
	 *
	 * @since  4.0
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Get block type.
	 *
	 * @since  4.0
	 *
	 * @return string
	 */
	public function get_type() {
		return ! empty( $this->type ) ? $this->type : 'geomywp/' . $this->slug;
	}

	/**
	 * Get form ID.
	 *
	 * @since  4.0
	 *
	 * @return mixed
	 */
	public function get_form_id() {

		if ( ! empty( $this->form_id ) ) {
			return $this->form_id;
		}

		global $pagenow;

		$form_id = 0;

		// For admin pages.
		if ( is_admin() ) {

			// When in form editor page.
			// phpcs:ignore.
			if ( isset( $_GET['page'] ) && 'gmw-forms' === $_GET['page'] && isset( $_GET['gmw_action'] ) && 'edit_form' === $_GET['gmw_action'] ) { // CSRF ok.

				$form_id = ! empty( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0; // phpcs:ignore: CSRF ok.

				// In Post Edit page. Not being used at the moment.
			} elseif ( 'post.php' === $pagenow ) {

				// phpcs:ignore.
				if ( isset( $_GET['post'] ) && isset( $_POST['post_ID'] ) && (int) $_GET['post'] !== (int) $_POST['post_ID'] ) {

					// Do nothing.

				} elseif ( isset( $_GET['post'] ) ) { // phpcs:ignore: CSRF ok.

					$post_id = (int) $_GET['post']; // phpcs:ignore: CSRF ok.

				} elseif ( isset( $_POST['post_ID'] ) ) { // phpcs:ignore: CSRF ok.

					$post_id = (int) $_POST['post_ID']; // phpcs:ignore: CSRF ok.
				}

				if ( ! empty( $post_id ) ) {

					// Get form ID from post custom field.
					$form_id = get_post_meta( $post_id, '_gmw_form_id', true );

					// phpcs:disable.
					/*if ( ! empty( $form_id ) ) {

						$form = gmw_get_form( $form_id ); // Get GEO my WP's form.

						if ( ! empty( $form['ID'] ) ) {
							$component = $form['component']; // Get form's component.
						}
				    }*/
					// phpcs:enable.
				}
			}
		} else {

		}
	}

	/**
	 * Get build type.
	 *
	 * @since  4.0
	 *
	 * @return string
	 */
	public function get_build_path() {
		return $this->build_path;
	}

	/**
	 * Do a check for specific block if allowed to registered on specific admin page.
	 *
	 * @return bool
	 */
	public function is_admin_page_allowed() {
		return true;
	}

	/**
	 * Get allowed post types.
	 *
	 * @since  4.0
	 *
	 * @return string
	 */
	public function get_allowed_post_types() {
		return $this->allowed_post_types;
	}

	/**
	 * Get disallowed post types.
	 *
	 * @since  4.0
	 *
	 * @return string
	 */
	public function get_disallowed_post_types() {
		return $this->disallowed_post_types;
	}

	/**
	 * Block properties.
	 *
	 * Properties are already generated in the block.json file but can be overriden in here.
	 *
	 * @since 4.0
	 *
	 * @return array
	 */
	protected function get_block_properties() {

		$properties['keywords'] = array(
			'address',
			'map',
			'location',
			'search',
			'nearby',
			'proximity',
		);

		if ( $this->server_side_render ) {
			$properties['render_callback'] = array( $this, 'render_block' );
		}

		return $properties;
	}

	/**
	 * Register block with WordPress.
	 *
	 * @since  4.0
	 */
	public function register_block_type() {

		// Allow blocks based on post types in the admin only.
		if ( is_admin() ) {

			if ( ! $this->is_admin_page_allowed() ) {
				return;
			}

			$post_types = array();
			$status     = true;

			if ( ! empty( $this->allowed_post_types ) ) {

				$post_types = $this->get_allowed_post_types();

			} elseif ( ! empty( $this->disallowed_post_types ) ) {

				$post_types = $this->get_disallowed_post_types();
				$status     = false;
			}

			if ( ! GMW_Blocks::verify_post_types( $post_types, $status ) ) {
				return;
			}
		}

		$properties = $this->get_block_properties();

		if ( ! empty( $this->attributes ) ) {
			$properties['attributes'] = $this->attributes;
		}

		register_block_type( $this->get_build_path(), $properties );
	}

	/**
	 * Enqueue/register the block's assets.
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function register_block_assets() {
		add_action( 'enqueue_block_editor_assets', array( $this, 'register_scripts' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'register_styles' ) );
	}

	/**
	 * Enqueue block scripts.
	 *
	 * @since  4.0
	 *
	 * @uses   GMW_Block::scripts()
	 */
	public function register_scripts() {

		// Get registered scripts.
		$scripts = $this->scripts();

		// Return if there are no scripts to enqueue.
		if ( empty( $scripts ) ) {
			return;
		}

		// Loop through scripts.
		foreach ( $scripts as $script ) {

			// Prepare parameters.
			$src       = isset( $script['src'] ) ? $script['src'] : false;
			$deps      = isset( $script['deps'] ) ? $script['deps'] : array();
			$version   = isset( $script['version'] ) ? $script['version'] : false;
			$in_footer = isset( $script['in_footer'] ) ? $script['in_footer'] : false;

			// Enqueue script.
			if ( $this->script_handle === $script['handle'] ) {

				// Support for the editor_style property, if a style_handle is defined. No need to enqueue.
				wp_register_script( $script['handle'], $src, $deps, $version, $in_footer );

			} else {

				// style_handle isn't defined, or this is an additional style. Enqueue it manually.
				wp_enqueue_script( $script['handle'], $src, $deps, $version, $in_footer );
			}

			// Localize script.
			if ( ! empty( $script['strings'] ) ) {
				wp_localize_script( $script['handle'], $script['handle'] . '_strings', $script['strings'] );
			}

			// Run script callback.
			if ( ! empty( $script['callback'] ) && is_callable( $script['callback'] ) ) {
				call_user_func( $script['callback'], $script );
			}
		}
	}

	/**
	 * Override this function to provide a list of scripts to be enqueued.
	 * Following is an example of the array that is expected to be returned by this function:
	 * <pre>
	 * <code>
	 *
	 *    Array(
	 *        array(
	 *            'handle'   => 'super_signature_script',
	 *            'src'      => $this->get_base_url() . '/super_signature/ss.js',
	 *            'version'  => $this->_version,
	 *            'deps'     => array( 'jquery'),
	 *            'callback' => array( $this, 'localize_scripts' ),
	 *            'strings'  => array(
	 *                // Accessible in JavaScript using the global variable "[script handle]_strings"
	 *                'stringKey1' => __( 'The string', 'geo-my-wp' ),
	 *                'stringKey2' => __( 'Another string.', 'geo-my-wp' )
	 *            )
	 *        )
	 *    );
	 *
	 * </code>
	 * </pre>
	 *
	 * @since  4.0
	 *
	 * @return array
	 */
	public function scripts() {
		return array();
	}

	/**
	 * Enqueue block styles.
	 *
	 * @since  4.0
	 */
	public function register_styles() {

		// Get registered styles.
		$styles = $this->styles();

		if ( ! is_array( $styles ) ) {
			$styles = array();
		}

		// Return if no styles are registered.
		if ( empty( $styles ) ) {
			return;
		}

		// Loop through styles.
		foreach ( $styles as $style ) {

			// Prepare parameters.
			$src     = isset( $style['src'] ) ? $style['src'] : false;
			$deps    = isset( $style['deps'] ) ? $style['deps'] : array();
			$version = isset( $style['version'] ) ? $style['version'] : false;
			$media   = isset( $style['media'] ) ? $style['media'] : 'all';

			if ( $this->style_handle === $style['handle'] ) {

				// Support for the editor_style property, if a style_handle is defined. No need to enqueue.
				wp_register_style( $style['handle'], $src, $deps, $version, $media );
			} else {

				// style_handle isn't defined, or this is an additional style. Enqueue it manually.
				wp_enqueue_style( $style['handle'], $src, $deps, $version, $media );
			}
		}
	}

	/**
	 * Override this function to provide a list of styles to be enqueued.
	 *
	 * @since  4.0
	 *
	 * @return array
	 */
	public function styles() {
		return array();
	}

	/**
	 * Render block on block editor and on the frontend.
	 *
	 * @since  4.0
	 *
	 * @param array $attributes Block attributes.
	 *
	 * @param mixed $content block content.
	 *
	 * @param array $block block before rendered.
	 *
	 * @return string
	 */
	public function render_block( $attributes = array(), $content = '', $block = array() ) {

		if ( method_exists( $this, 'render_block_editor' ) && ( is_admin() || defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {

			return $this->render_block_editor( $attributes, $content, $block );

		} elseif ( method_exists( $this, 'render_block_front_end' ) ) {

			return $this->render_block_front_end( $attributes, $content, $block );

		} else {
			return '';
		}
	}

	/**
	 * Render contents on block editor only.
	 *
	 * @since  4.0
	 *
	 * @param array $attributes Block attributes.
	 *
	 * @param mixed $content block content.
	 *
	 * @param array $block block before rendered.
	 *
	 * @return string
	 */
	public function render_block_editor( $attributes = array(), $content = array(), $block = array() ) {
		return $content;
	}

	/**
	 * Render contents on front-end only.
	 *
	 * @since  4.0
	 *
	 * @param array $attributes Block attributes.
	 *
	 * @param mixed $content block content.
	 *
	 * @param array $block block before rendered.
	 *
	 * @return string
	 */
	public function render_block_front_end( $attributes = array(), $content = array(), $block = array() ) {
		return $content;
	}

	/**
	 * Get list of GEO my WP's forms.
	 *
	 * @since 4.0
	 *
	 * @return array
	 */
	public function get_forms() {

		$forms = gmw_get_forms();

		if ( empty( $forms ) || ! is_array( $forms ) ) {
			$forms = array();
		}

		$form_options = array(
			array(
				'label' => 'Select a form',
				'value' => '0',
			),
		);

		// allowed forms in this widget.
		$allowed_forms = apply_filters( 'gmw_search_form_widget_allowed_forms', array( 'posts_locator', 'members_locator', 'bp_groups_locator', 'users_locator' ) );

		foreach ( $forms as $form ) {

			if ( empty( $form['ID'] ) ) {
				continue;
			}

			$form_id = absint( $form['ID'] );

			if ( ! empty( $form_id ) && in_array( $form['slug'], $allowed_forms, true ) ) {

				$form_name      = ! empty( $form['name'] ) ? $form['name'] : 'form_id_' . $form_id;
				$form_label     = 'Form ID ' . $form_id . ' ( ' . $form_name . ' )';
				$form_options[] = array(
					'label'    => esc_attr( $form_label ),
					'value'    => esc_attr( $form_id ),
					'disabled' => gmw_is_addon_active( $form['addon'] ) ? false : true,
				);
			}
		}

		if ( empty( $form_options ) ) {

			$form_options = array(
				array(
					'label' => 'No forms found',
					'value' => '0',
				),
			);
		}

		// Forms array can be modified.
		return apply_filters( 'gmw_block_form_forms', $form_options );
	}
}
