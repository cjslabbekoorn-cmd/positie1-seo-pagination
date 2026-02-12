<?php
/**
 * Plugin Name: Positie1 SEO Pagination
 * Description: Adds an Elementor "SEO Pagination" widget (fully styleable) plus SEO hygiene for paginated pages. Includes a simple [seo_pagination] shortcode as fallback.
 * Version: 1.5.4
 * Author: Positie1
 * Text Domain: positie1-seo-pagination
 */
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'P1_SEO_PAG_VERSION', '1.5.4' );
define( 'P1_SEO_PAG_PATH', plugin_dir_path( __FILE__ ) );
define( 'P1_SEO_PAG_URL',  plugin_dir_url( __FILE__ ) );

require_once P1_SEO_PAG_PATH . 'includes/class-p1-seo-pagination-core.php';

add_action( 'plugins_loaded', function () {
    P1_SEO_Pagination_Core::init();
}, 5 );

add_action( 'elementor/widgets/register', function( $widgets_manager ) {
    if ( ! class_exists( '\\Elementor\\Widget_Base' ) ) return;
    require_once P1_SEO_PAG_PATH . 'includes/class-p1-elementor-seo-pagination-widget.php';
    $widgets_manager->register( new \P1_Elementor_SEO_Pagination_Widget() );
}, 20 );

add_action( 'wp_enqueue_scripts', function () {
    if ( is_admin() ) return;

    // Alleen registreren (enqueuen gebeurt pas als widget/shortcode aanwezig is)
    $ver = P1_SEO_PAG_VERSION;
    if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
        $path = P1_SEO_PAG_PATH . 'assets/css/frontend.css';
        if ( file_exists( $path ) ) {
            $ver = (string) filemtime( $path );
        }
    }

    wp_register_style(
        'p1-seo-pagination-frontend',
        P1_SEO_PAG_URL . 'assets/css/frontend.css',
        [],
        $ver
    );
}, 5 ); 
