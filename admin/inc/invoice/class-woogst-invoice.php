<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class WoogstInvoice
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
        // Store the instance locally to avoid private static replication.
        static $instance = null;

        // Only run these methods if they haven't been ran previously.
        if (null === $instance) {
            $instance = new self();
        }

        // Always return the instance.
        return $instance;
    }

    public function init()
    {
        // Add your custom order status action button (for orders with "processing" status)
        add_filter('woocommerce_admin_order_actions', 'add_invoice_action_order_list_table', 100, 2);

        // Print invoice action
        add_action( 'wp_ajax_owlth_print_invoice', 'handle_print_invoice' );
    }

}


// The main instance
if (!function_exists('woogst_invoice')) {
    /**
     * Return instance of  WoogstInvoice class
     *
     * @since 1.0.0
     *
     * @return Gst
     */
    function woogst_invoice()
    {//phpcs:ignore
        return WoogstInvoice::get_instance();
    }
}


if (!function_exists('add_invoice_action_order_list_table')) :
function add_invoice_action_order_list_table($actions, $order)
{
    // Display the button for all orders that have a 'processing' status
    $order_id = method_exists($order, 'get_id') ? $order->get_id() : $order->id;
    
    $actions['print_invoice'] = array(
        'url'    => wp_nonce_url( admin_url( 'admin-ajax.php?action=owlth_print_invoice&order_id=' . $order_id ), 'owlth-print-invoice' ),
        'name'   => __( 'Print Invoice', 'owlth-invoice-packaging-slip' ),
        'action' => "invoice", // Custom class for invoice action
    );

    return $actions;
}
endif;


if (!function_exists("handle_print_invoice")) :
// Handle printing invoice
function handle_print_invoice() {
    if ( isset( $_GET['order_id'] ) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'owlth-print-invoice' ) ) {
        $order_id = intval( $_GET['order_id'] );
        $order = wc_get_order( $order_id );
        
        if ( $order ) {
            include plugin_dir_path( dirname( __FILE__ ) ) . 'templates/invoice/invoice-template.php';
            exit;
        }
    }
    wp_die( 'Order not found' );
}
endif;