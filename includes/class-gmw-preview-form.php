<?php
/**
 * GEO my WP Preview Form.
 *
 * @since 4.0
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class GMW_Preview_Form {

    protected $form_id = '';

    public function __construct() {

        if ( ! isset( $_GET['gmw_preview_form'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.
        	return;
        }

        $this->form_id = sanitize_text_field( wp_unslash( $_GET['gmw_preview_form'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.

        add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );

        add_filter('the_title', array( $this, 'the_title' ) );

        remove_filter( 'the_content', 'wpautop' );
        remove_filter( 'the_excerpt', 'wpautop' );

        add_filter('the_content', array( $this, 'the_content' ), 9001 );
        add_filter('get_the_excerpt', array( $this, 'the_content' ) );
        add_filter('template_include', array( $this, 'template_include' ) );
        add_filter('post_thumbnail_html', array( $this, 'post_thumbnail_html' ) );
    }

    public function pre_get_posts( $query ) {

    	remove_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );

		$query->set( 'posts_per_page', 1 );
    }

    public function the_title( $title ) {

    	remove_filter('the_title', array( $this, 'the_title' ) );

        if ( ! in_the_loop() ) {
        	return $title;
        }

        return 'GEO my WP Form ' . $this->form_id . ' ' . esc_html__( 'Preview', 'geo-my-wp' );
    }

    /**
     * Modify the content of the page and inject the form shortcode to it.
     *
     * @return [type] [description]
     */
    public function the_content() {

    	remove_filter('the_content', array( $this, 'the_content' ), 9001 );

        return do_shortcode( '[gmw form="' . absint( $this->form_id ) . '"]' );
    }

    /**
     * @return string
     */
    public function template_include() {
        return locate_template( array( 'page.php', 'single.php', 'index.php' ) );
    }

    public function post_thumbnail_html() {

    	remove_filter('post_thumbnail_html', array( $this, 'post_thumbnail_html' ) );

    	return '';
    }
}
new GMW_Preview_Form();
