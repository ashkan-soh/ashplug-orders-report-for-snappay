<?php

namespace WSOM\Admin;

defined( 'ABSPATH' ) || exit;

use WSOM\Admin\Orders_Report;

class Admin_Menu {

    /**
     * Name: Ashplug Orders Report for Snappay
     * URI:  https://ashplug.ir
     * Developer: Ashkan Sohrevardi
     * 
     */

    const MENU_SLUG     = 'ashplug-snappay-report';
    const PRO_MENU_SLUG = 'ashplug-snappay-report-pro';

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'register_menus' ] );
    }

    public function register_menus() {

        add_menu_page(
            __( 'Ashplug Orders Report for Snappay', 'ashplug-orders-report-for-snappay' ),
            __( 'Ashplug Snappay', 'ashplug-orders-report-for-snappay' ),
            'manage_options',
            self::MENU_SLUG,
            [ $this, 'render_orders_report' ],
            'dashicons-money-alt',
            57
        );

        add_submenu_page(
            self::MENU_SLUG,
            __( 'Orders Report for Snappay Gateway', 'ashplug-orders-report-for-snappay' ),
            __( 'Orders Report', 'ashplug-orders-report-for-snappay' ),
            'manage_options',
            self::MENU_SLUG,
            [ $this, 'render_orders_report' ]
        );

        add_submenu_page(
            self::MENU_SLUG,
            __( 'Pro Features', 'ashplug-orders-report-for-snappay' ),
            __( 'Pro Features', 'ashplug-orders-report-for-snappay' ),
            'manage_options',
            self::PRO_MENU_SLUG,
            [ $this, 'render_pro_features' ]
        );
    }

    public function render_orders_report() {

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to access this page.', 'ashplug-orders-report-for-snappay' ) );
        }

        $table = new Orders_Report();
        $table->prepare_items();

        echo '<div class="wrap wsom-content">';

        echo '<h1 class="wsom-title">' . esc_html__( 'Orders Report for Snappay Gateway', 'ashplug-orders-report-for-snappay' ) . '</h1>';

        echo '<p class="description">' . esc_html__( 'This report shows WooCommerce orders paid through the Snappay gateway. Use the table pagination to browse all matching orders.', 'ashplug-orders-report-for-snappay' ) . '</p>';

        $this->render_lite_upgrade_card();

        $table->display();

        echo '</div>';
    }

    private function render_lite_upgrade_card(): void {
        $pro_url = admin_url( 'admin.php?page=' . self::PRO_MENU_SLUG );
        ?>
        <div class="wsom-lite-card" aria-label="<?php echo esc_attr__( 'Plugin notice', 'ashplug-orders-report-for-snappay' ); ?>">
            <div class="wsom-lite-card__body">
                <span class="wsom-lite-card__eyebrow"><?php echo esc_html__( 'Read-only Report', 'ashplug-orders-report-for-snappay' ); ?></span>
                <p>
                    <?php echo esc_html__( 'This plugin provides a complete read-only orders report for payments made through the Snappay gateway. A separate Pro plugin adds financial workflows such as installment schedules, commission calculations, Jalali filters, and export tools.', 'ashplug-orders-report-for-snappay' ); ?>
                </p>
            </div>
            <a class="button button-primary wsom-pro-cta" href="<?php echo esc_url( $pro_url ); ?>">
                <?php echo esc_html__( 'View Pro Features', 'ashplug-orders-report-for-snappay' ); ?>
            </a>
        </div>
        <?php
    }

    public function render_pro_features() {

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to access this page.', 'ashplug-orders-report-for-snappay' ) );
        }

        $external_url = 'https://ashplug.ir/';
        ?>
        <div class="wrap wsom-content wsom-pro-page">
            <section class="wsom-pro-hero">
                <div class="wsom-pro-hero__content">
                    <span class="wsom-pro-kicker"><?php echo esc_html__( 'Ashplug Pro', 'ashplug-orders-report-for-snappay' ); ?></span>
                    <h1><?php echo esc_html__( 'Advanced financial reports for Snappay orders', 'ashplug-orders-report-for-snappay' ); ?></h1>
                    <p>
                        <?php echo esc_html__( 'The Pro version is built for WooCommerce stores that need a clearer financial view of orders paid through the Snappay gateway.', 'ashplug-orders-report-for-snappay' ); ?>
                    </p>
                    <div class="wsom-pro-actions">
                        <a class="button button-primary wsom-pro-hero__button" href="<?php echo esc_url( $external_url ); ?>" target="_blank" rel="noopener noreferrer">
                            <?php echo esc_html__( 'View Details on Ashplug.ir', 'ashplug-orders-report-for-snappay' ); ?>
                            <span class="dashicons dashicons-external" aria-hidden="true"></span>
                        </a>
                        <a class="button wsom-pro-hero__button wsom-pro-hero__button--secondary" href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::MENU_SLUG ) ); ?>">
                            <?php echo esc_html__( 'Back to Orders Report', 'ashplug-orders-report-for-snappay' ); ?>
                        </a>
                    </div>
                </div>
                <div class="wsom-pro-hero__panel" aria-hidden="true">
                    <div class="wsom-pro-metric"><span>4</span><?php echo esc_html__( 'Snappay payment models', 'ashplug-orders-report-for-snappay' ); ?></div>
                    <div class="wsom-pro-metric"><span>%</span><?php echo esc_html__( 'contract-based commission', 'ashplug-orders-report-for-snappay' ); ?></div>
                    <div class="wsom-pro-metric"><span>1–5</span><?php echo esc_html__( 'monthly settlement basis', 'ashplug-orders-report-for-snappay' ); ?></div>
                </div>
            </section>

            <section class="wsom-pro-grid" aria-label="<?php echo esc_attr__( 'Pro features', 'ashplug-orders-report-for-snappay' ); ?>">
                <?php
                $features = [
                    [
                        'icon'  => 'dashicons-chart-area',
                        'title' => __( 'Financial reconciliation views', 'ashplug-orders-report-for-snappay' ),
                        'text'  => __( 'Review Snappay payment data in finance-focused views designed for reconciliation and store operations.', 'ashplug-orders-report-for-snappay' ),
                    ],
                    [
                        'icon'  => 'dashicons-calendar-alt',
                        'title' => __( 'Jalali date filters', 'ashplug-orders-report-for-snappay' ),
                        'text'  => __( 'Filter reports with Persian calendar date ranges built for local store workflows.', 'ashplug-orders-report-for-snappay' ),
                    ],
                    [
                        'icon'  => 'dashicons-list-view',
                        'title' => __( 'Installment schedule', 'ashplug-orders-report-for-snappay' ),
                        'text'  => __( 'Split successful Snappay payments into monthly installment rows for easier follow-up.', 'ashplug-orders-report-for-snappay' ),
                    ],
                    [
                        'icon'  => 'dashicons-calculator',
                        'title' => __( 'Commission calculation', 'ashplug-orders-report-for-snappay' ),
                        'text'  => __( 'Calculate commission, tax, final receivable amount, and per-installment values.', 'ashplug-orders-report-for-snappay' ),
                    ],
                    [
                        'icon'  => 'dashicons-randomize',
                        'title' => __( 'Refund and cancellation insight', 'ashplug-orders-report-for-snappay' ),
                        'text'  => __( 'Separate cancelled, fully refunded, and partially refunded paid orders in reports.', 'ashplug-orders-report-for-snappay' ),
                    ],
                    [
                        'icon'  => 'dashicons-media-spreadsheet',
                        'title' => __( 'Export-ready reporting', 'ashplug-orders-report-for-snappay' ),
                        'text'  => __( 'Prepare practical financial reports for review, reconciliation, and store operations.', 'ashplug-orders-report-for-snappay' ),
                    ],
                ];

                foreach ( $features as $feature ) :
                    ?>
                    <article class="wsom-pro-feature-card">
                        <span class="dashicons <?php echo esc_attr( $feature['icon'] ); ?>" aria-hidden="true"></span>
                        <h2><?php echo esc_html( $feature['title'] ); ?></h2>
                        <p><?php echo esc_html( $feature['text'] ); ?></p>
                    </article>
                    <?php
                endforeach;
                ?>
            </section>

            <section class="wsom-pro-note">
                <strong><?php echo esc_html__( 'Independent plugin notice:', 'ashplug-orders-report-for-snappay' ); ?></strong>
                <?php echo esc_html__( 'This plugin is independently developed and is not affiliated with, endorsed by, or sponsored by Snappay or WooCommerce.', 'ashplug-orders-report-for-snappay' ); ?>
            </section>
        </div>
        <?php
    }
}
