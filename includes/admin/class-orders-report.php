<?php

namespace WSOM\Admin;

defined( 'ABSPATH' ) || exit;

use WP_List_Table;
use WSOM\Helpers\Snappay;

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Orders_Report extends WP_List_Table {

    /**
     * Name: Ashplug Orders Report for Snappay
     * URI:  https://ashplug.ir
     * Developer: Ashkan Sohrevardi
     * 
     */

    /** @var int */
    protected $total_items = 0;

    /** @var int */
    protected $total_all_snappay = 0;

    public function __construct() {
        parent::__construct( [
            'singular' => 'order',
            'plural'   => 'orders',
            'ajax'     => false,
        ] );
    }

    public function get_columns() {
        return [
            'customer'       => esc_html__( 'Customer', 'ashplug-orders-report-for-snappay' ),
            'order_id'       => esc_html__( 'Order ID', 'ashplug-orders-report-for-snappay' ),
            'order_status'   => esc_html__( 'Order Status', 'ashplug-orders-report-for-snappay' ),
            'transaction_id' => esc_html__( 'Transaction ID', 'ashplug-orders-report-for-snappay' ),
            'paid_date'      => esc_html__( 'Paid Date', 'ashplug-orders-report-for-snappay' ),
            'amount'         => esc_html__( 'Amount', 'ashplug-orders-report-for-snappay' ),
            'payment_status' => esc_html__( 'Payment Status', 'ashplug-orders-report-for-snappay' ),
        ];
    }

    public function prepare_items() {

        $per_page     = 20;
        $current_page = max( 1, absint( $this->get_pagenum() ) );

        $query_args = [
            'type'           => 'shop_order',
            'status'         => 'any',
            'orderby'        => 'date_created',
            'order'          => 'DESC',
            'return'         => 'objects',
            'payment_method' => 'WC_Gateway_SnappPay',
            'limit'          => $per_page,
            'page'           => $current_page,
            'paginate'       => true,
        ];

        $result = Snappay::get_snappay_orders_page( $query_args );
        $orders = $result['orders'] ?? [];

        $this->total_items       = isset( $result['total'] ) ? (int) $result['total'] : 0;
        $this->total_all_snappay = Snappay::count_snappay_orders();

        $this->set_pagination_args( [
            'total_items' => $this->total_items,
            'per_page'    => $per_page,
            'total_pages' => $per_page > 0 ? (int) ceil( $this->total_items / $per_page ) : 0,
        ] );

        $items = [];

        foreach ( $orders as $order ) {

            if ( ! $order || ! is_a( $order, '\\WC_Order' ) ) {
                continue;
            }

            $paid_date     = $order->get_date_paid();
            $order_state   = $this->get_order_state( $order );
            $state_label   = $this->get_order_state_label( $order_state );
            $transaction_id =
                $order->get_meta( 'transaction_id' )
                ?: $order->get_meta( '_transactionId' )
                ?: '—';

            $items[] = [
                'customer'          => $order->get_formatted_billing_full_name(),
                'order_id'          => $order->get_id(),
                'order_status'      => wc_get_order_status_name( $order->get_status() ),
                'order_state'       => $order_state,
                'order_state_label' => $state_label,
                'transaction_id'    => $transaction_id,
                'paid_date'         => $paid_date ? $paid_date->date_i18n( 'Y/m/d H:i' ) : '—',
                'amount'            => wc_price( $order->get_total() ),
                'payment_status'    => $paid_date ? 'success' : 'failed',
            ];
        }

        $this->items = $items;

        $this->_column_headers = [ $this->get_columns(), [], [] ];
    }

    protected function get_order_state( $order ): string {
        $status         = $order->get_status();
        $total          = (float) $order->get_total();
        $total_refunded = method_exists( $order, 'get_total_refunded' ) ? (float) $order->get_total_refunded() : 0.0;

        if ( 'cancelled' === $status ) {
            return 'cancelled';
        }

        if ( 'refunded' === $status || ( $total > 0 && $total_refunded >= $total ) ) {
            return 'refunded';
        }

        if ( $total_refunded > 0 ) {
            return 'partially-refunded';
        }

        return 'normal';
    }

    protected function get_order_state_label( string $state ): string {
        switch ( $state ) {
            case 'cancelled':
                return __( 'Cancelled', 'ashplug-orders-report-for-snappay' );
            case 'refunded':
                return __( 'Refunded', 'ashplug-orders-report-for-snappay' );
            case 'partially-refunded':
                return __( 'Partially refunded', 'ashplug-orders-report-for-snappay' );
        }

        return '';
    }

    public function single_row( $item ) {
        $classes = [ 'wsom-order-row' ];

        if ( ! empty( $item['order_state'] ) && 'normal' !== $item['order_state'] ) {
            $classes[] = 'wsom-order-row-' . sanitize_html_class( $item['order_state'] );
        }

        echo '<tr class="' . esc_attr( implode( ' ', $classes ) ) . '">';
        $this->single_row_columns( $item );
        echo '</tr>';
    }

    protected function column_order_id( $item ) {

        $order_id = absint( $item['order_id'] );
        $url      = admin_url( 'post.php?post=' . $order_id . '&action=edit' );

        return sprintf(
            '<a href="%s" class="wsom-order-id" target="_blank" rel="noopener noreferrer">%d</a>',
            esc_url( $url ),
            $order_id
        );
    }

    protected function column_order_status( $item ) {
        $status = esc_html( (string) ( $item['order_status'] ?? '—' ) );
        $state  = $item['order_state'] ?? 'normal';
        $label  = $item['order_state_label'] ?? '';

        if ( 'normal' === $state || '' === $label ) {
            return $status;
        }

        return sprintf(
            '<span class="wsom-order-status-text">%s</span><span class="wsom-order-state-badge wsom-order-state-badge-%s">%s</span>',
            $status,
            esc_attr( sanitize_html_class( $state ) ),
            esc_html( $label )
        );
    }

    protected function column_payment_status( $item ) {

        $is_success = $item['payment_status'] === 'success';

        $class = $is_success
            ? 'wsom-status-success'
            : 'wsom-status-failed';

        $icon = $is_success
            ? 'dashicons-yes-alt'
            : 'dashicons-no-alt';

        return sprintf(
            '<span class="wsom-payment-status %s">
                <span class="dashicons %s"></span>
            </span>',
            esc_attr( $class ),
            esc_attr( $icon )
        );
    }

    protected function column_default( $item, $column_name ) {
        $value = $item[ $column_name ] ?? '—';

        switch ( $column_name ) {
            case 'customer':
            case 'transaction_id':
            case 'paid_date':
                return esc_html( (string) $value );

            case 'amount':
                // wc_price() returns safe HTML.
                return wp_kses_post( (string) $value );
        }

        return esc_html( (string) $value );
    }

    public function extra_tablenav( $which ) {

        if ( $which !== 'top' ) {
            return;
        }

        $first_snappay_order_ts = Snappay::get_first_snappay_order_date();
        $first_snappay_order_label = $first_snappay_order_ts
            ? ( function_exists( 'wp_date' )
                ? wp_date( 'Y/m/d', $first_snappay_order_ts )
                : date( 'Y/m/d', $first_snappay_order_ts )
            )
            : '—';

        ?>
        <div class="wsom-filter-bar">
            <div class="wsom-report-summary">
                <span class="wsom-report-summary__label"><?php echo esc_html__( 'First Snappay order date:', 'ashplug-orders-report-for-snappay' ); ?></span>
                <span class="wsom-report-summary__count">
                    <?php echo esc_html( $first_snappay_order_label ); ?>
                </span>

                <span class="wsom-report-summary__label"><?php echo esc_html__( 'Total Snappay orders in store:', 'ashplug-orders-report-for-snappay' ); ?></span>
                <span class="wsom-report-summary__count">
                    <?php echo number_format_i18n( (int) $this->total_all_snappay ); ?>
                </span>

                <span class="wsom-report-summary__label wsom-report-summary__label--strong"><?php echo esc_html__( 'Snappay orders in report:', 'ashplug-orders-report-for-snappay' ); ?></span>
                <span class="wsom-report-summary__count wsom-report-summary__count--strong">
                    <?php echo number_format_i18n( (int) $this->total_items ); ?>
                </span>
            </div>
        </div>
        <?php
    }
}
