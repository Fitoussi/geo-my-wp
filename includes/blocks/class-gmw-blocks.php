<?php
/**
 * GMW Blocks class.
 *
 * @author Eyal Fitoussi, Gravity Forms team
 *
 * This class was originally written by the Gravity Forms team and was modified to work with GEO my WP ( thank you!! ) .
 *
 * @since 4.0
 *
 * @package geo-my-wp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base GMW Blocks Class.
 *
 * @since 4.0
 *
 * Class GMW_Blocks
 */
class GMW_Blocks {

	/**
	 * Registered GMW editor blocks.
	 *
	 * @since 4.0
	 *
	 * @var   GMW_Block[]
	 */
	private static $_blocks = array();

	/**
	 * [__construct description].
	 */
	public function __construct() {

		if ( ! defined( 'GMW_BLOCKS_DIR' ) ) {
			define( 'GMW_BLOCKS_DIR', plugin_dir_path( __FILE__ ) );
		}

		if ( ! defined( 'GMW_BLOCKS_URL' ) ) {
			define( 'GMW_BLOCKS_URL', plugin_dir_url( __FILE__ ) );
		}

		require_once 'class-gmw-block.php';

		add_filter( 'block_categories_all', array( $this, 'register_blocks_categories' ), 99, 2 );
		add_filter( 'allowed_block_types_all', array( $this, 'allowed_blocks' ), 99, 2 );
		add_filter( 'block_editor_settings_all', array( $this, 'prevent_block_unlocking' ), 20, 2 );
		add_filter( 'block_type_metadata_settings', array( $this, 'define_parent_blocks' ), 99, 2 );
		add_action( 'enqueue_block_editor_assets', array( $this, 'register_styles' ) );
	}

	/**
	 * Register main styles for blocks.
	 *
	 * @since 4.0
	 */
	public function register_styles() {

		if ( ! wp_style_is( 'gmw-frontend', 'enqueued' ) ) {
			wp_enqueue_style( 'gmw-frontend' );
		}

		if ( ! wp_style_is( 'gmw-forms', 'enqueued' ) ) {
			wp_enqueue_style( 'gmw-forms' );
		}

		if ( ! wp_style_is( 'gmw-blocks', 'enqueued' ) ) {
			wp_enqueue_style( 'gmw-blocks', GMW_BLOCKS_URL . 'assets/css/gmw-blocks-editor.min.css', array(), GMW_VERSION );
		}
	}

	/**
	 * Register GEO my WP blocks category.
	 *
	 * @since 4.0
	 *
	 * @param  array  $categories categories.
	 *
	 * @param  object $block_editor_context block_editor_context.
	 *
	 * @return array
	 */
	public function register_blocks_categories( $categories, $block_editor_context ) {

		if ( empty( $block_editor_context->post ) && ( empty( $_GET['page'] ) || 'gmw-forms' !== $_GET['page'] ) ) { // phpcs:ignore: CSRF ok.
			return $categories;
		}

		$templates_category = array(
			array(
				'slug'  => 'geomywp-filters',
				'title' => 'GEO my WP Filters',
				'icon'  => '',
			),
		);

		$categories[] = array(
			'slug'  => 'geo-my-wp',
			'title' => 'GEO my WP',
			'icon'  => '',
		);

		// Register categories for gmw_template post type only.
		if ( 'gmw_template' === $block_editor_context->post->post_type || ( ! empty( $_GET['page'] ) && 'gmw-forms' === $_GET['page'] ) ) { // phpcs:ignore: CSRF ok.

			// Place the filters category first.
			$categories = array_merge( $templates_category, $categories );

		} else {

			$categories[] = $templates_category[0];
		}

		return $categories;
	}

	/**
	 * Allow only specific blocks based on some criteria ( post types, GMW template fype, etc... ).
	 *
	 * For example, we only need some blocks when creating a search form template for GEO my WP.
	 *
	 * @since 4.0
	 *
	 * @param  [type] $block_editor_context [description].
	 *
	 * @param  [type] $editor_context       [description].
	 *
	 * @return [type]                       [description]
	 */
	public function allowed_blocks( $block_editor_context, $editor_context ) {

		if ( ! empty( $editor_context->post ) ) {

			// Get all registered blocks.
			$registered_blocks  = WP_Block_Type_Registry::get_instance()->get_all_registered();
			$output             = array();
			$allowed_categories = array( 'design', 'text', 'geomywp-filters' );
			$disallowed_blocks  = array(
				'geomywp/form-container',
				'geomywp/modal-box',
				'core/nextpage',
				'core/more',
				'core/navigation-submenu',
				'core/navigation-link',
				'core/home-link',
				'core/comment-template',
				'core/freeform',
				'core/list',
				'core/button',
				'core/quote',
			);

			// Blocks allowed in gmw_template post type only.
			if ( 'gmw_template' === $editor_context->post->post_type ) {

				foreach ( $registered_blocks as $block_name => $block ) {

					if ( in_array( $block->category, $allowed_categories, true ) ) {

						if ( ! in_array( $block_name, $disallowed_blocks, true ) ) {
							$output[] = $block_name;
						}
					}
				}

				// Blocks allowed in all other post types.
			} else {

				foreach ( $registered_blocks as $block_name => $block ) {

					// phpcs:ignore.
					//if ( ! in_array( $block->category, array( 'geomywp-filters' ), true ) ) {
					$output[] = $block_name;
					// } // phpcs:ignore.
				}
			}

			return $output;
		}

		return $block_editor_context;
	}

	/**
	 * Prevent unlocking of GEO my WP blocks.
	 *
	 * We do this to make sure that the form-container and modal-box blocks cannot be removed.
	 *
	 * @since 4.0
	 *
	 * @param array $settings settings.
	 *
	 * @param mixed $context content.
	 *
	 * @return array $settings.
	 */
	public static function prevent_block_unlocking( $settings, $context ) {

		// Disable for GMW Templates post type.
		if ( $context->post && 'gmw_template' === $context->post->post_type ) {
			$settings['canLockBlocks'] = false;
		}

		return $settings;
	}

	/**
	 * Change the parent settings of all blocks in the gmw_template post type.
	 *
	 * This is to make sure that blocks cannot be added to the lower level of the block editor but only to the form container and modal box blocks.
	 *
	 * @since 4.0
	 *
	 * @param  [type] $settings [description].
	 *
	 * @param  [type] $metadata [description].
	 *
	 * @return [type]           [description]
	 */
	public function define_parent_blocks( $settings, $metadata ) {

		// Verify post type.
		if ( ! self::verify_post_types( array( 'gmw_template' ) ) ) {
			return $settings;
		}

		$settings['parent'] = array( 'geomywp/form-container', 'geomywp/modal-box', 'geomywp/flexbox', 'core/group' );

		return $settings;
	}

	/**
	 * Register a block.
	 *
	 * @since  4.0
	 *
	 * @param GMW_Block $block Block class.
	 *
	 * @return bool|WP_Error
	 */
	public static function register( $block ) {

		if ( ! is_subclass_of( $block, 'GMW_Block' ) ) {
			return new WP_Error( 'block_not_subclass', 'Must be a subclass of GMW_Block' );
		}

		// Get block type.
		$block_type = $block->get_type();

		if ( empty( $block_type ) ) {
			return new WP_Error( 'block_type_undefined', 'The type must be set' );
		}

		if ( isset( self::$_blocks[ $block_type ] ) ) {
			return new WP_Error( 'block_already_registered', 'Block type already registered: ' . $block_type );
		}

		// Register block.
		self::$_blocks[ $block_type ] = $block;

		// phpcs:ignore.
		// call_user_func( array( $block, 'init' ) );

		add_action( 'init', array( $block, 'init' ) );

		return true;
	}

	/**
	 * Get instance of block.
	 *
	 * @since  4.0
	 *
	 * @param string $block_type Block type.
	 *
	 * @return GMW_Block|bool
	 */
	public static function get( $block_type ) {
		return isset( self::$_blocks[ $block_type ] ) ? self::$_blocks[ $block_type ] : false;
	}

	/**
	 * Returns an array of registered block types.
	 *
	 * @since 4.0
	 *
	 * @return array
	 */
	public static function get_all_types() {
		return array_keys( self::$_blocks );
	}

	/**
	 * Verify that we are in a specific post type page.
	 *
	 * @since 4.0
	 *
	 * @param  array $post_types array of post types to allow or disallow.
	 *
	 * @param  array $allowed    true to allow || false to diallowe the post types.
	 *
	 * @return [type]                [description]
	 */
	public static function verify_post_types( $post_types = array(), $allowed = true ) {

		// Allow all type when empty.
		if ( empty( $post_types ) ) {
			return true;
		}

		// Check if this is the intended custom post type.
		if ( is_admin() ) {

			global $pagenow;

			$typenow = '';

			if ( 'post-new.php' === $pagenow ) {

				// phpcs:ignore.
				if ( isset( $_REQUEST['post_type'] ) && post_type_exists( $_REQUEST['post_type'] ) ) { // CSRF ok.
					$typenow = sanitize_text_field( wp_unslash( $_REQUEST['post_type'] ) ); // phpcs:ignore: CSRF ok.
				}
			} elseif ( 'post.php' === $pagenow ) {

				// phpcs:ignore.
				if ( isset( $_GET['post'] ) && isset( $_POST['post_ID'] ) && (int) $_GET['post'] !== (int) $_POST['post_ID'] ) { // CSRF ok.

					// Do nothing.

				} elseif ( isset( $_GET['post'] ) ) { // phpcs:ignore: CSRF ok.

					$post_id = (int) $_GET['post']; // phpcs:ignore: CSRF ok.

				} elseif ( isset( $_POST['post_ID'] ) ) { // phpcs:ignore: CSRF ok.

					$post_id = (int) $_POST['post_ID']; // phpcs:ignore: CSRF ok.
				}

				if ( $post_id ) {

					$post    = get_post( $post_id );
					$typenow = $post->post_type;
				}
			}

			if ( $allowed && ! in_array( $typenow, $post_types, true ) ) {

				return false;

			} elseif ( ! $allowed && in_array( $typenow, $post_types, true ) ) {

				return false;
			}
		}

		return true;
	}
}

new GMW_Blocks();
