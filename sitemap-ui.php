<?php
/*
Plugin Name: Sitemap UI
Description: Configure WP core sitemaps within the admin UI
Version: 1.2
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
        define( 'SMUI_VERSION', '1.2' );
        define( 'SMUI_DIR', dirname( __FILE__ ) );
        define( 'SMUI_URL', plugins_url( '', __FILE__ ) );
        define( 'SMUI_BASENAME', plugin_basename( __FILE__ ) );

        add_action( 'init', [ $this, 'init' ] );
        add_action( 'smui_cron', [ $this, 'send_ping' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );

        // get the gears turning
        $this->apply_sitemap_rules();
        $this->run_cron();
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


    function admin_scripts( $hook ) {
        if ( 'settings_page_smui' == $hook ) {
            $settings = get_option( 'smui_settings', '{}' );

            wp_enqueue_script( 'fselect', SMUI_URL . '/assets/vendor/fSelect/fSelect.js', [ 'jquery' ], SMUI_VERSION );
            wp_enqueue_script( 'smui', SMUI_URL . '/assets/js/admin.js', [], SMUI_VERSION );
            wp_enqueue_style( 'fselect', SMUI_URL . '/assets/vendor/fSelect/fSelect.css', [], SMUI_VERSION );
            wp_enqueue_style( 'smui', SMUI_URL . '/assets/css/admin.css', [], SMUI_VERSION );
            wp_add_inline_script( 'smui', "var SMUI = $settings;" );
        }
    }


    function settings_page() {
        include( SMUI_DIR . '/templates/page-settings.php' );
    }


    function sanitize( $input ) {
        if ( is_array( $input ) ) {
            $output = [];

            foreach ( $input as $key => $val ) {
                $output[ $key ] = $this->sanitize( $val );
            }
        }
        else {
            $output = sanitize_text_field( $input );
        }

        return $output;
    }


    function is_valid_nonce( $name = 'smui_nonce' ) {
        return isset( $_POST[ $name ] ) && wp_verify_nonce( $_POST[ $name ], $name );
    }


    function save_settings() {
        $sanitized = $this->sanitize( $_POST['data'] );
        update_option( 'smui_settings', json_encode( $sanitized ) );
    }


    function get_post_types() {
        $post_types = get_post_types( [ 'public' => true ] );
        unset( $post_types['attachment'] );
        return $post_types;
    }


    function get_taxonomies() {
        return get_taxonomies( [ 'public' => true ] );
    }


    function get_settings() {
        $settings = get_option( 'smui_settings' );
        return json_decode( $settings, true );
    }


    function apply_sitemap_rules() {
        $this->settings = $this->get_settings();

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


    function run_cron() {
        if ( ! wp_next_scheduled( 'smui_cron' ) ) {
            wp_schedule_single_event( time() + 86400, 'smui_cron' );
        }
    }


    function send_ping() {
        $settings = $this->get_settings();

        if ( ! isset( $settings['objects']['all'] ) ) {
            $url = get_home_url() . '/wp-sitemap.xml';

            $response = wp_remote_get( "http://www.google.com/ping?sitemap=$url", [
                'blocking' => false,
                'timeout' => 0.02
            ] );
        }
    }
}


function SMUI() {
    return SMUI_Plugin::instance();
}


SMUI();
