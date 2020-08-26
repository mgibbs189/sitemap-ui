<?php
/*
Plugin Name: Sitemap UI
Description: Configure WP core sitemaps within the admin UI
Version: 0.1
Author: FacetWP, LLC
Author URI: https://facetwp.com/

Copyright 2020 FacetWP, LLC

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/>.
*/

defined( 'ABSPATH' ) or exit;

class SMUI_Plugin
{

    public $settings;
    private static $instance;

    function __construct() {

        // setup variables
        define( 'SMUI_VERSION', '0.1' );
        define( 'SMUI_DIR', dirname( __FILE__ ) );
        define( 'SMUI_URL', plugins_url( '', __FILE__ ) );
        define( 'SMUI_BASENAME', plugin_basename( __FILE__ ) );

        add_action( 'init', [ $this, 'init' ] );

        // get the gears turning
        $this->apply_sitemap_rules();
    }


    /**
     * Singleton
     */
    public static function instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self;
        }
        return self::$instance;
    }


    function init() {
        add_action( 'admin_menu', [ $this, 'admin_menu' ] );
    }


    function admin_menu() {
        add_options_page( 'Sitemap UI', 'Sitemap UI', 'manage_options', 'smui', [ $this, 'settings_page' ] );
    }


    function settings_page() {
        include( SMUI_DIR . '/templates/page-settings.php' );
    }


    function save_settings() {
        $settings = json_encode( $_POST['data'] );
        update_option( 'smui_settings', $settings );
    }


    function get_post_types() {
        $post_types = get_post_types( [ 'public' => true ] );
        unset( $post_types['attachment'] );
        return $post_types;
    }


    function get_taxonomies() {
        return get_taxonomies( [ 'public' => true ] );
    }


    function apply_sitemap_rules() {
        $settings = get_option( 'smui_settings' );
        $this->settings = json_decode( $settings, true );

        // disable sitemaps
        if ( isset( $this->settings['objects']['all'] ) ) {
            add_filter( 'wp_sitemaps_enabled', '__return_false' );
            return;
        }

        // post type rules
        if ( isset( $this->settings['objects']['post_types'] ) ) {
            add_filter( 'wp_sitemaps_post_types', '__return_empty_array' );
        }
        else {
            if ( isset( $this->settings['post_types'] ) ) {
                add_filter( 'wp_sitemaps_post_types', function( $post_types ) {
                    foreach ( $post_types as $name => $obj ) {
                        if ( in_array( $name, SMUI()->settings['post_types'] ) ) {
                            unset( $post_types[ $name ] );
                        }
                    }
                    return $post_types;
                });
            }

            if ( isset( $this->settings['post_ids'] ) ) {
                add_filter( 'wp_sitemaps_posts_query_args', function( $query_args ) {
                    $excluded_ids = preg_replace( "/\s+/", '', SMUI()->settings['post_ids'] );
                    $excluded_ids = explode( ',', $excluded_ids );
                    $query_args['post__not_in'] = $excluded_ids;
                    return $query_args;
                });
            }
        }

        // taxonomy rules
        if ( isset( $this->settings['objects']['taxonomies'] ) ) {
            add_filter( 'wp_sitemaps_taxonomies', '__return_empty_array' );
        }
        else {
            if ( isset( $this->settings['taxonomies'] ) ) {
                add_filter( 'wp_sitemaps_taxonomies', function( $taxonomies ) {
                    foreach ( $taxonomies as $name => $obj ) {
                        if ( in_array( $name, SMUI()->settings['taxonomies'] ) ) {
                            unset( $taxonomies[ $name ] );
                        }
                    }
                    return $taxonomies;
                });
            }

            if ( isset( $this->settings['term_ids'] ) ) {
                add_filter( 'wp_sitemaps_taxonomies_query_args', function( $query_args ) {
                    $excluded_ids = preg_replace( "/\s+/", '', SMUI()->settings['term_ids'] );
                    $excluded_ids = explode( ',', $excluded_ids );
                    $query_args['exclude'] = $excluded_ids;
                    return $query_args;
                });
            }
        }

        // user rules
        if ( isset( $this->settings['objects']['users'] ) ) {
            add_filter( 'wp_sitemaps_users_query_args', function( $query_args ) {
                $query_args['include'] = [ 0 ];
                return $query_args;
            });
        }
    }
}


function SMUI() {
    return SMUI_Plugin::instance();
}


SMUI();
