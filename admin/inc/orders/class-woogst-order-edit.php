<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
      exit;
}

class Woogst_Order_Edit {
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

      /**
       * Handles order edits customizations
       * - Adds gst fields to the filters for woocommerce edit order billing fields (making it editable along with other billing fields)
       * - Validates and saves gst fields
       * @return void
       */
      public function init()
      {
            // Get the field values to be displayed in admin Order edit pages
            add_filter('woocommerce_order_formatted_billing_address', array($this, 'add_gst_to_woocommerce_order_fields'), 10, 2);
            // Make the custom GST billing fields editable in Admin order pages
            add_filter('woocommerce_admin_billing_fields', array($this, 'add_gst_to_woocommerce_admin_billing_fields'));
            // Save and validate the custom GST fields when the order's billing address is updated in the admin order page
            add_action('woocommerce_process_shop_order_meta', array($this, 'save_and_validate_custom_gst_billing_fields'), 10, 2);
      }

      /**
       * Adding two GST fields into Billing fields ( gst_trade_name, gst_number )
       * @since 1.0.0
       * @param mixed $address
       * @param mixed $order
       * @return mixed
       */
      public function add_gst_to_woocommerce_order_fields($address, $order)
      {
            $address['gst_trade_name'] = $order->get_meta('_billing_gst_trade_name');
            $address['gst_number'] = $order->get_meta('_billing_gst_number');

            return $address;
      }

      /**
       * Single order edit - validate & save with notice
       * @since 1.0.0
       * @param mixed $billing_fields
       * @return mixed
       */
      public function add_gst_to_woocommerce_admin_billing_fields($billing_fields)
      {
            // GST Holder Name
            $billing_fields['gst_trade_name'] = array(
                  'label' => __('GST Holder Name', 'woocommerce'),
                  'show' => true,
            );

            // GST Number
            $billing_fields['gst_number'] = array(
                  'label' => __('GST Number', 'woocommerce'),
                  'show' => true,
            );

            return $billing_fields;
      }

      /**
       * Single order edit - validate & save with notice
       * @since 1.0.0
       * @param mixed $order_id
       * @param mixed $post_data
       * @return void
       */
      public function save_and_validate_custom_gst_billing_fields($order_id, $post_data)
      {
            // Get the order object
            $order = wc_get_order($order_id);
            
            $gst_holder_name = isset($_POST['_billing_gst_trade_name']) ? sanitize_text_field($_POST['_billing_gst_trade_name']) : '';
            $gst_number = isset($_POST['_billing_gst_number']) ? sanitize_text_field($_POST['_billing_gst_number']) : '';

            // HPOS check
            $is_hpos_enabled = is_hpos_enabled();

            // Save the GST Holder Name if it is provided
            if (!empty($gst_holder_name) && !empty($gst_number)) {
                  if (!validate_gst_number($gst_number)) {
                        // Show an error and stop execution
                        WC_Admin_Meta_Boxes::add_error(__('Invalid GST Number. Please enter a valid GST Number.', 'woocommerce'));
                        wp_redirect($_SERVER['HTTP_REFERER']);
                        exit;
                  }
                  if ($is_hpos_enabled) {
                        $order->update_meta_data('_billing_claim_gst', 1);
                        $order->update_meta_data('_billing_gst_number', $gst_number);
                        $order->update_meta_data('_billing_gst_trade_name', $gst_holder_name);
                        $order->save();
                  } else {
                        update_post_meta($order_id, '_billing_claim_gst', 1);
                        update_post_meta($order_id, '_billing_gst_number', $gst_number);
                        update_post_meta($order_id, '_billing_gst_trade_name', $gst_holder_name);
                  }
            }
      }
}

// The main instance
if (!function_exists('woogst_order_edit')) {
      /**
       * Return instance of Woogst_Order_Edit class
       * @since 1.0.0
       * @return Woogst_Order_Edit
       */
      function woogst_order_edit()
      {
            return Woogst_Order_Edit::get_instance();
      }
}

