<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class Woogst_Invoice
{
    /**
     * Gets an instance of this object.
     * Prevents duplicate instances which avoid artefacts and improves performance.
     *
     * @static
     * @access public
     * @return object
     * @since 1.0.0
     */
    public static function get_instance()
    {
        static $instance = null;
        if (null === $instance) {
            $instance = new self();
        }
        return $instance;
    }

    public function init()
    {
        add_filter('woocommerce_admin_order_actions', array($this, 'add_invoice_action_order_list_table'), 100, 2);
        add_action( 'wp_ajax_owlth_print_invoice', array($this, 'handle_print_invoice') );
    }

    /**
     * Adds invoice action in action column
     * @since 1.0.0
     * @param mixed $actions
     * @param mixed $order
     * @return mixed
     */
    public function add_invoice_action_order_list_table($actions, $order)
    {
        $order_id = method_exists($order, 'get_id') ? $order->get_id() : $order->id;
        $actions['print_invoice'] = array(
            'url'    => wp_nonce_url( admin_url( 'admin-ajax.php?action=owlth_print_invoice&order_id=' . $order_id ), 'owlth-print-invoice' ),
            'name'   => __( 'Print Invoice', 'owlth-invoice-packaging-slip' ),
            'action' => "invoice", // Custom class for invoice action
        );
        return $actions;
    }

    /**
     * Handles print invoice action
     * @since 1.0.0
     * @return void
     */
    public function handle_print_invoice() {
        if ( isset( $_GET['order_id'] ) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'owlth-print-invoice' ) ) {
            $order_id = intval( $_GET['order_id'] );
            $order = wc_get_order( $order_id );
            
            if ( $order ) {
                include WOOGST_INVOICE_TEMPLATE_DIR . 'invoice-template.php';
                exit;
            }
        }
        wp_die( 'Order not found' );
    }

}


// The main instance
if (!function_exists('woogst_invoice')) {
    /**
     * Return instance of  Woogst_Invoice class
     *
     * @since 1.0.0
     *
     * @return Woogst_Invoice
     */
    function woogst_invoice()
    {//phpcs:ignore
        return Woogst_Invoice::get_instance();
    }
}
