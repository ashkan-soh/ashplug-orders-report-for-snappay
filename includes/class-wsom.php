<?php

namespace WSOM;

defined( 'ABSPATH' ) || exit;

use WSOM\Admin\Admin_Menu;

final class Plugin {

    /**
     * Name: Ashplug Orders Report for Snappay
     * URI:  https://ashplug.ir
     * Developer: Ashkan Sohrevardi
     * 
     */

    private static $instance = null;

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        if ( ! $this->check_dependencies() ) {
            return;
        }

        $this->includes();
        $this->init_hooks();
    }

    private function check_dependencies(): bool {

        if ( ! class_exists( 'WooCommerce' ) ) {
            $this->deactivate_with_notice(
                __( 'Ashplug Orders Report for Snappay requires WooCommerce to be installed and active.', 'ashplug-orders-report-for-snappay' )
            );
            return false;
        }

        if ( ! class_exists( 'WC_Gateway_SnappPay' ) ) {
            $this->deactivate_with_notice(
                __( 'Ashplug Orders Report for Snappay requires a compatible Snappay payment gateway plugin to be installed and active.', 'ashplug-orders-report-for-snappay' )
            );
            return false;
        }

        return true;
    }

    private function deactivate_with_notice( $message ): void {

        add_action( 'admin_notices', function () use ( $message ) {
            if ( ! current_user_can( 'activate_plugins' ) ) {
                return;
            }

            echo '<div class="notice notice-error"><p><strong>';
            echo esc_html( $message );
            echo '</strong></p></div>';
        } );

        add_action( 'admin_init', function () {
            if ( ! function_exists( 'deactivate_plugins' ) ) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

            deactivate_plugins( plugin_basename( WSOM_FILE ) );
        } );
    }

    private function includes(): void {
        if ( is_admin() ) {
            require_once WSOM_PATH . 'includes/helpers/class-snappay.php';
            require_once WSOM_PATH . 'includes/admin/class-orders-report.php';
            require_once WSOM_PATH . 'includes/class-admin-menu.php';
        }
    }

    private function is_allowed_user(): bool {

        if ( is_multisite() && is_super_admin() ) {
            return true;
        }

        return current_user_can( 'manage_options' );
    }

    private function init_hooks(): void {
        if ( is_admin() ) {
            if ( ! $this->is_allowed_user() ) {
                return;
            }

            new Admin_Menu();

            add_action( 'admin_enqueue_scripts', function() {
                $page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

                $allowed_pages = [
                    \WSOM\Admin\Admin_Menu::MENU_SLUG,
                    \WSOM\Admin\Admin_Menu::PRO_MENU_SLUG,
                ];

                if ( ! in_array( $page, $allowed_pages, true ) ) {
                    return;
                }

                wp_enqueue_style(
                    'wsom-admin',
                    plugin_dir_url( WSOM_FILE ) . 'assets/css/admin.css',
                    [],
                    WSOM_VERSION
                );
            } );
        }
    }
}
