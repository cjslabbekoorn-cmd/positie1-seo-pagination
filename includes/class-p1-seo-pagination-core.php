<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class P1_SEO_Pagination_Core {

    const SHORTCODE_PRIMARY = 'seo_pagination';

    public static function init() : void {
        add_shortcode( self::SHORTCODE_PRIMARY, [ __CLASS__, 'shortcode' ] );

        // Yoast
        add_filter( 'wpseo_robots',   [ __CLASS__, 'yoast_robots_noindex_paginated' ] );
        add_filter( 'wpseo_canonical',[ __CLASS__, 'yoast_canonical_self_paginated' ] );

        // Rank Math
        add_filter( 'rank_math/frontend/robots',   [ __CLASS__, 'rank_math_robots_noindex_paginated' ] );
        add_filter( 'rank_math/frontend/canonical',[ __CLASS__, 'rank_math_canonical_self_paginated' ] );

        // Prev/Next links in head
        add_action( 'wp_head', [ __CLASS__, 'head_prev_next_links' ], 1 );
    }

    /**
     * Feature flags (can be overridden by constants).
     */
    public static function enabled( string $key ) : bool {
        $map = [
            'seo_noindex_paginated'  => 'P1_SEO_PAG_DISABLE_NOINDEX_PAGINATED',
            'seo_canonical_paginated'=> 'P1_SEO_PAG_DISABLE_CANONICAL_PAGINATED',
            'head_prev_next'         => 'P1_SEO_PAG_DISABLE_HEAD_PREV_NEXT',
        ];

        if ( isset( $map[ $key ] ) && defined( $map[ $key ] ) ) {
            return ! (bool) constant( $map[ $key ] );
        }

        // Defaults: enabled.
        return true;
    }

    public static function is_paginated_request() : bool {
        $paged = (int) get_query_var( 'paged' );
        if ( $paged > 1 ) return true;

        // For singular with <!--nextpage-->
        $page = (int) get_query_var( 'page' );
        return $page > 1;
    }

    public static function current_url() : string {
        // Try to preserve current request URL.
        $scheme = is_ssl() ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? '';
        $uri    = $_SERVER['REQUEST_URI'] ?? '';
        $url    = $scheme . '://' . $host . $uri;
        return esc_url_raw( $url );
    }

    // ---------------- SEO: Yoast ----------------

    public static function yoast_robots_noindex_paginated( $robots ) {
        if ( ! self::enabled( 'seo_noindex_paginated' ) ) return $robots;
        if ( self::is_paginated_request() ) {
            // Yoast accepts string "noindex,follow" or array depending on version.
            if ( is_array( $robots ) ) {
                $robots['index']  = 'noindex';
                $robots['follow'] = 'follow';
                return $robots;
            }
            return 'noindex,follow';
        }
        return $robots;
    }

    public static function yoast_canonical_self_paginated( $canonical ) {
        if ( ! self::enabled( 'seo_canonical_paginated' ) ) return $canonical;
        return self::is_paginated_request() ? self::current_url() : $canonical;
    }

    // ---------------- SEO: Rank Math ----------------

    public static function rank_math_robots_noindex_paginated( $robots ) {
        if ( ! self::enabled( 'seo_noindex_paginated' ) ) return $robots;
        if ( self::is_paginated_request() ) {
            if ( is_array( $robots ) ) {
                $robots['index']  = 'noindex';
                $robots['follow'] = 'follow';
            }
        }
        return $robots;
    }

    public static function rank_math_canonical_self_paginated( $canonical ) {
        if ( ! self::enabled( 'seo_canonical_paginated' ) ) return $canonical;
        return self::is_paginated_request() ? self::current_url() : $canonical;
    }

    // ---------------- Head prev/next ----------------

    public static function head_prev_next_links() : void {
        if ( ! self::enabled( 'head_prev_next' ) ) return;
        if ( is_admin() ) return;

        // Only for archives/search.
        if ( ! is_archive() && ! is_home() && ! is_search() ) return;

        global $wp_query;
        if ( empty( $wp_query ) || (int) $wp_query->max_num_pages <= 1 ) return;

        $current = max( 1, (int) get_query_var( 'paged' ) );
        $max     = (int) $wp_query->max_num_pages;

        if ( $current > 1 ) {
            $prev = get_pagenum_link( $current - 1 );
            echo "\n<link rel=\"prev\" href=\"" . esc_url( $prev ) . "\" />\n";
        }

        if ( $current < $max ) {
            $next = get_pagenum_link( $current + 1 );
            echo "\n<link rel=\"next\" href=\"" . esc_url( $next ) . "\" />\n";
        }
    }

    // ---------------- Shortcode + rendering ----------------

    public static function shortcode( $atts ) : string {
        // Load the registered CSS only when shortcode is actually used.
        if ( wp_style_is( 'p1-seo-pagination-frontend', 'registered' ) ) {
            wp_enqueue_style( 'p1-seo-pagination-frontend' );
        }

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
        if ( ! empty( $atts['query_id'] ) ) {
            $attrs['data-query-id'] = (string) $atts['query_id'];
        }
        $atts['attrs'] = $attrs;

        return self::render_pagination( $atts );
    }

    public static function render_pagination( array $atts ) : string {
        global $wp_query;

        if ( empty( $wp_query ) || (int) $wp_query->max_num_pages <= 1 ) {
            return '';
        }

        $current_var = isset( $atts['current_var'] ) ? (string) $atts['current_var'] : 'paged';
        $current     = (int) get_query_var( $current_var );
        if ( $current < 1 ) $current = 1;

        $total = (int) $wp_query->max_num_pages;

        $links = paginate_links( [
            'total'     => $total,
            'current'   => $current,
            'mid_size'  => max( 0, (int) $atts['mid_size'] ),
            'end_size'  => max( 0, (int) $atts['end_size'] ),
            'prev_text' => (string) $atts['prev_text'],
            'next_text' => (string) $atts['next_text'],
            'type'      => 'array',
        ] );

        if ( empty( $links ) || ! is_array( $links ) ) {
            return '';
        }

        $classes = trim( (string) ( $atts['class'] ?? 'p1-seo-pagination' ) );
        $aria    = trim( (string) ( $atts['aria_label'] ?? __( 'Pagination', 'positie1-seo-pagination' ) ) );

        $extra_attrs = '';
        if ( ! empty( $atts['attrs'] ) && is_array( $atts['attrs'] ) ) {
            foreach ( $atts['attrs'] as $k => $v ) {
                if ( $k === '' ) continue;
                $extra_attrs .= ' ' . esc_attr( $k ) . '="' . esc_attr( (string) $v ) . '"';
            }
        }

        $html  = '<nav class="' . esc_attr( $classes ) . '" aria-label="' . esc_attr( $aria ) . '"' . $extra_attrs . '>';
        $html .= '<ul class="p1-seo-pagination__list">';

        foreach ( $links as $link ) {
            $is_current = ( strpos( $link, 'current' ) !== false ) || ( strpos( $link, 'aria-current' ) !== false );
            $html .= '<li class="p1-seo-pagination__item' . ( $is_current ? ' is-active' : '' ) . '">' . $link . '</li>';
        }

        $html .= '</ul>';
        $html .= '</nav>';

        return $html;
    }
}
