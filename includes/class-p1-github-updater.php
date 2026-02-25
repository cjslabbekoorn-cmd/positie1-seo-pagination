<?php
/**
 * GitHub Updater for Positie1 SEO Pagination
 * - Uses GitHub "latest release" endpoint
 * - Downloads ONLY from release assets (browser_download_url)
 * - Avoids zipball/tarball because of unpredictable folder names (WP install fails)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class P1_SEO_Pagination_GitHub_Updater {

    const TRANSIENT_KEY = 'p1_seo_pag_gh_release';

    public static function init() : void {
        add_filter( 'pre_set_site_transient_update_plugins', [ __CLASS__, 'inject_update' ] );
        add_filter( 'plugins_api', [ __CLASS__, 'plugins_api' ], 10, 3 );
    }

    protected static function plugin_basename() : string {
        return plugin_basename( P1_SEO_PAG_PATH . 'positie1-seo-pagination.php' );
    }

    protected static function slug() : string {
        return 'positie1-seo-pagination';
    }

    protected static function repo() : string {
        return (string) P1_SEO_PAG_GH_REPO; // e.g. "cjslabbekoorn-cmd/positie1-seo-pagination"
    }

    protected static function github_headers() : array {
        $headers = [
            'Accept'     => 'application/vnd.github+json',
            'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url( '/' ),
        ];

        if ( defined( 'P1_GITHUB_TOKEN' ) && P1_GITHUB_TOKEN ) {
            $headers['Authorization'] = 'Bearer ' . P1_GITHUB_TOKEN;
        }

        return $headers;
    }

    protected static function fetch_latest_release() : ?array {
        $cached = get_site_transient( self::TRANSIENT_KEY );
        if ( is_array( $cached ) ) {
            return $cached;
        }

        $url = 'https://api.github.com/repos/' . rawurlencode( self::repo() ) . '/releases/latest';

        $res = wp_remote_get( $url, [
            'timeout' => 12,
            'headers' => self::github_headers(),
        ] );

        if ( is_wp_error( $res ) ) {
            return null;
        }

        $code = (int) wp_remote_retrieve_response_code( $res );
        if ( $code < 200 || $code >= 300 ) {
            return null;
        }

        $body = wp_remote_retrieve_body( $res );
        $data = json_decode( $body, true );

        if ( ! is_array( $data ) || empty( $data['tag_name'] ) ) {
            return null;
        }

        // Cache for 10 minutes.
        set_site_transient( self::TRANSIENT_KEY, $data, 10 * MINUTE_IN_SECONDS );

        return $data;
    }

    protected static function normalize_version( string $tag ) : string {
        $tag = trim( $tag );
        return ltrim( $tag, 'vV' ); // accept v1.2.3 or 1.2.3
    }

    /**
     * Find the correct downloadable ZIP asset from the release.
     *
     * Behavior:
     * - If P1_SEO_PAG_GH_ASSET_PREFIX is defined AND not empty:
     *     pick the first asset whose name starts with that prefix and ends with .zip
     * - Else:
     *     pick the first asset that ends with .zip
     */
    protected static function find_asset_url( array $release ) : ?string {
        if ( empty( $release['assets'] ) || ! is_array( $release['assets'] ) ) {
            return null;
        }

        $prefix = '';
        if ( defined( 'P1_SEO_PAG_GH_ASSET_PREFIX' ) ) {
            $prefix = trim( (string) P1_SEO_PAG_GH_ASSET_PREFIX );
        }

        // Helper: validate asset entry.
        $is_valid = static function( $asset ) : bool {
            return is_array( $asset )
                && ! empty( $asset['name'] )
                && ! empty( $asset['browser_download_url'] );
        };

        // 1) Prefix-based match (strict).
        if ( $prefix !== '' ) {
            foreach ( $release['assets'] as $asset ) {
                if ( ! $is_valid( $asset ) ) continue;

                $name = (string) $asset['name'];
                if ( strpos( $name, $prefix ) === 0 && substr( $name, -4 ) === '.zip' ) {
                    return (string) $asset['browser_download_url'];
                }
            }
            return null;
        }

        // 2) Fallback: first .zip asset.
        foreach ( $release['assets'] as $asset ) {
            if ( ! $is_valid( $asset ) ) continue;

            $name = (string) $asset['name'];
            if ( substr( $name, -4 ) === '.zip' ) {
                return (string) $asset['browser_download_url'];
            }
        }

        return null;
    }

    public static function inject_update( $transient ) {
        if ( ! is_object( $transient ) ) {
            return $transient;
        }

        $release = self::fetch_latest_release();
        if ( ! $release ) {
            return $transient;
        }

        $new_version = self::normalize_version( (string) $release['tag_name'] );

        // No update needed.
        if ( version_compare( $new_version, P1_SEO_PAG_VERSION, '<=' ) ) {
            return $transient;
        }

        // IMPORTANT: use ONLY release asset ZIP.
        // GitHub zipball/tarball have unpredictable top folder names -> WP install fails.
        $package = self::find_asset_url( $release );
        if ( ! $package ) {
            return $transient;
        }

        $item = (object) [
            'slug'        => self::slug(),
            'plugin'      => self::plugin_basename(),
            'new_version' => $new_version,
            'url'         => 'https://github.com/' . self::repo(),
            'package'     => $package,
        ];

        $transient->response[ self::plugin_basename() ] = $item;

        return $transient;
    }

    public static function plugins_api( $result, $action, $args ) {
        if ( $action !== 'plugin_information' ) return $result;
        if ( empty( $args->slug ) || $args->slug !== self::slug() ) return $result;

        $release = self::fetch_latest_release();

        $info = new stdClass();
        $info->name     = 'Positie1 SEO Pagination';
        $info->slug     = self::slug();
        $info->version  = $release ? self::normalize_version( (string) $release['tag_name'] ) : P1_SEO_PAG_VERSION;
        $info->author   = 'Positie1';
        $info->homepage = 'https://github.com/' . self::repo();

        // IMPORTANT: show only asset ZIP as download link.
        $info->download_link = $release ? ( self::find_asset_url( $release ) ?: '' ) : '';

        $info->sections = [
            'description' => __( 'Adds an Elementor "SEO Pagination" widget plus SEO hygiene for paginated pages.', 'positie1-seo-pagination' ),
        ];

        if ( $release && ! empty( $release['body'] ) ) {
            $info->sections['changelog'] = wp_kses_post( (string) $release['body'] );
        }

        return $info;
    }
}