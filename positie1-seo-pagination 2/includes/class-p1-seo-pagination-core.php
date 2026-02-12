<?php
if ( ! defined( 'ABSPATH' ) ) exit;

final class P1_SEO_Pagination_Core {

    const SHORTCODE_PRIMARY = 'seo_pagination';
    const SHORTCODE_LEGACY  = 'pagination'; // optional alias

    public static function init() : void {
        add_shortcode( self::SHORTCODE_PRIMARY, [ __CLASS__, 'shortcode' ] );
        add_shortcode( self::SHORTCODE_LEGACY,  [ __CLASS__, 'shortcode' ] );

        add_action( 'template_redirect', [ __CLASS__, 'redirect_page_one_variants' ], 1 );
        add_filter( 'wpseo_robots',      [ __CLASS__, 'yoast_robots_noindex_paginated' ], 20 );
        add_filter( 'wpseo_canonical',   [ __CLASS__, 'yoast_canonical_self_paginated' ], 20 );
        add_filter( 'rank_math/frontend/robots',   [ __CLASS__, 'rank_math_robots_noindex_paginated' ], 20 );
        add_filter( 'rank_math/frontend/canonical', [ __CLASS__, 'rank_math_canonical_self_paginated' ], 20 );
        add_action( 'wp_head',           [ __CLASS__, 'fallback_meta_if_no_yoast' ], 20 );
        add_filter( 'paginate_links',    [ __CLASS__, 'filter_paginate_links_cleanup' ], 10, 1 );
        add_action( 'wp_head',           [ __CLASS__, 'head_prev_next_links' ], 5 );
    }

    private static function enabled( string $feature ) : bool {
        $enabled = true;

        $const = 'P1_SEO_PAG_DISABLE_' . strtoupper( $feature );
        $const = str_replace( '-', '_', $const );

        if ( defined( $const ) && constant( $const ) ) {
            $enabled = false;
        }

        return (bool) apply_filters( 'p1_seo_pag_enabled_feature', $enabled, $feature );
    }

    public static function is_paginated_request() : bool {
        if ( is_admin() ) return false;

        $paged = (int) get_query_var( 'paged' );
        $page  = (int) get_query_var( 'page' );
        $cpage = (int) get_query_var( 'cpage' );

        if ( $paged >= 2 || $page >= 2 || $cpage >= 2 ) return true;

        $uri = (string) ( $_SERVER['REQUEST_URI'] ?? '' );
        $uri = strtok( $uri, '#' );

        if ( preg_match( '~\/page\/([2-9][0-9]*)\/?$~', $uri ) ) return true;
        if ( preg_match( '~[?&](?:paged|page|cpage|product-page)=([2-9][0-9]*)~', $uri ) ) return true;

        return false;
    }

    public static function current_url() : string {
        $uri = (string) ( $_SERVER['REQUEST_URI'] ?? '' );
        $uri = strtok( $uri, '#' );
        return home_url( $uri );
    }

    public static function clean_page_one_url( string $url ) : string {
        $url = preg_replace( '~\/page\/1\/?$~', '/', $url );
        $url = remove_query_arg( [ 'paged', 'page', 'cpage', 'product-page' ], $url );
        return $url;
    }

    public static function render_pagination( array $args = [] ) : string {
        global $wp_query;

        if ( empty( $wp_query ) || (int) $wp_query->max_num_pages <= 1 ) return '';

        $defaults = [
            'class'       => 'p1-seo-pagination',
            'aria_label'  => __( 'Pagination', 'positie1-seo-pagination' ),
            'mid_size'    => 1,
            'end_size'    => 1,
            'prev_text'   => '←',
            'next_text'   => '→',
            'current_var' => 'paged',
            'attrs'       => [],
        ];
        $args = wp_parse_args( $args, $defaults );

        $big    = 999999999;
        $base   = str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) );
        $format = get_option( 'permalink_structure' ) ? 'page/%#%/' : '&paged=%#%';

        $current_var = preg_replace( '~[^a-z0-9_\-]~i', '', (string) $args['current_var'] );
        $current     = max( 1, (int) get_query_var( $current_var ) );
        $total       = (int) $wp_query->max_num_pages;

        $links = paginate_links( [
            'base'      => $base,
            'format'    => $format,
            'current'   => $current,
            'total'     => $total,
            'type'      => 'array',
            'mid_size'  => max( 0, (int) $args['mid_size'] ),
            'end_size'  => max( 0, (int) $args['end_size'] ),
            'prev_text' => (string) $args['prev_text'],
            'next_text' => (string) $args['next_text'],
        ] );

        if ( empty( $links ) ) return '';

        $classes = array_filter( array_map( 'sanitize_html_class', preg_split( '~\s+~', (string) $args['class'] ) ) );

        $attr_html = '';
        $extra = is_array( $args['attrs'] ) ? $args['attrs'] : [];
        foreach ( $extra as $k => $v ) {
            $k = strtolower( preg_replace( '~[^a-z0-9\-\_:]~i', '', (string) $k ) );
            if ( $k === '' ) continue;
            $attr_html .= ' ' . esc_attr( $k ) . '="' . esc_attr( (string) $v ) . '"';
        }

        $html  = '<nav class="' . esc_attr( implode( ' ', $classes ) ) . '" aria-label="' . esc_attr( (string) $args['aria_label'] ) . '"' . $attr_html . '>';
        foreach ( (array) $links as $l ) $html .= $l;
        $html .= '</nav>';

        return $html;
    }

    public static function shortcode( $atts ) : string {
        
        // Laad de geregistreerde CSS alleen wanneer de shortcode wordt gebruikt.
        wp_enqueue_style( 'p1-seo-pagination-frontend' );

        $atts = shortcode_atts( [
            'class'       => 'p1-seo-pagination',
            'aria_label'  => __( 'Pagination', 'positie1-seo-pagination' ),
            'mid_size'    => 1,
            'end_size'    => 1,
            'prev_text'   => '←',
            'next_text'   => '→',
            'current_var' => 'paged',
            'query_id'    => '',
        ], (array) $atts, self::SHORTCODE_PRIMARY );

        $attrs = [];
        if ( ! empty( $atts['query_id'] ) ) $attrs['data-query-id'] = (string) $atts['query_id'];
        $atts['attrs'] = $attrs;

        return self::render_pagination( $atts );
    }

    public static function redirect_page_one_variants() : void {
        if ( ! self::enabled( 'seo_redirect_page1' ) ) return;
        if ( is_admin() || is_preview() ) return;

        $request_uri = (string) ( $_SERVER['REQUEST_URI'] ?? '' );
        $request_uri = strtok( $request_uri, '#' );

        $has_page1_path   = (bool) preg_match( '~\/page\/1\/?$~', $request_uri );
        $paged_is_1       = isset($_GET['paged']) && (string) $_GET['paged'] === '1';
        $page_is_1        = isset($_GET['page']) && (string) $_GET['page'] === '1';
        $cpage_is_1       = isset($_GET['cpage']) && (string) $_GET['cpage'] === '1';
        $productpage_is_1 = isset($_GET['product-page']) && (string) $_GET['product-page'] === '1';

        if ( ! $has_page1_path && ! $paged_is_1 && ! $page_is_1 && ! $cpage_is_1 && ! $productpage_is_1 ) return;

        $current = self::current_url();
        $target  = self::clean_page_one_url( $current );

        if ( $target && $target !== $current ) {
            wp_safe_redirect( $target, 301 );
            exit;
        }
    }

    public static function yoast_robots_noindex_paginated( $robots ) {
        if ( ! self::enabled( 'seo_noindex_paginated' ) ) return $robots;
        return self::is_paginated_request() ? 'noindex,follow' : $robots;
    }

    public static function yoast_canonical_self_paginated( $canonical ) {
        if ( ! self::enabled( 'seo_canonical_paginated' ) ) return $canonical;
        if ( ! self::is_paginated_request() ) return $canonical;
        $url = self::current_url();
        return $url ?: $canonical;
    }


    /**
     * Ondersteuning voor Rank Math Robots meta
     */
    public static function rank_math_robots_noindex_paginated( $robots ) {
        if ( ! self::enabled( 'seo_noindex_paginated' ) ) return $robots;

        if ( self::is_paginated_request() && is_array( $robots ) ) {
            $robots['index']  = 'noindex';
            $robots['follow'] = 'follow';
        }

        return $robots;
    }

    /**
     * Ondersteuning voor Rank Math Canonical
     */
    public static function rank_math_canonical_self_paginated( $canonical ) {
        if ( ! self::enabled( 'seo_canonical_paginated' ) ) return $canonical;
        return self::is_paginated_request() ? self::current_url() : $canonical;
    }


    public static function fallback_meta_if_no_yoast() : void {
        if ( ! self::enabled( 'seo_fallback_meta' ) ) return;
        if ( defined( 'WPSEO_VERSION' ) ) return;
        if ( ! self::is_paginated_request() ) return;

        echo "<meta name=\"robots\" content=\"noindex, follow\" />\n";
        $url = esc_url( self::current_url() );
        if ( $url ) echo "<link rel=\"canonical\" href=\"{$url}\" />\n";
    }

    public static function filter_paginate_links_cleanup( $link ) {
        if ( ! self::enabled( 'seo_paginate_links_cleanup' ) ) return $link;
        if ( is_admin() ) return $link;

        $link = self::clean_page_one_url( (string) $link );
        return str_replace(
            [ '?paged=1', '&paged=1', '?page=1', '&page=1', '?cpage=1', '&cpage=1', '?product-page=1', '&product-page=1' ],
            '',
            $link
        );
    }

    public static function head_prev_next_links() : void {
        if ( ! self::enabled( 'seo_prev_next' ) ) return;
        if ( is_admin() ) return;
        if ( ! is_archive() && ! is_home() && ! is_post_type_archive() && ! is_tax() && ! is_search() ) return;

        global $wp_query;
        if ( empty( $wp_query ) || (int) $wp_query->max_num_pages <= 1 ) return;

        $current = max( 1, (int) get_query_var( 'paged' ) );
        $total   = (int) $wp_query->max_num_pages;

        if ( $current > 1 ) echo "<link rel=\"prev\" href=\"" . esc_url( get_pagenum_link( $current - 1 ) ) . "\" />\n";
        if ( $current < $total ) echo "<link rel=\"next\" href=\"" . esc_url( get_pagenum_link( $current + 1 ) ) . "\" />\n";
    }
}
