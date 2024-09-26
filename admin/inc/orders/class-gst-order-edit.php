<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
      exit;
}

class Gst_Order_Edit {
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
            add_filter('woocommerce_order_formatted_billing_address', 'add_gst_to_woocommerce_order_fields', 10, 2);
            // Make the custom GST billing fields editable in Admin order pages
            add_filter('woocommerce_admin_billing_fields', 'add_gst_to_woocommerce_admin_billing_fields');
            // Save and validate the custom GST fields when the order's billing address is updated in the admin order page
            add_action('woocommerce_process_shop_order_meta', 'save_and_validate_custom_gst_billing_fields', 10, 2);
      }
}

// The main instance
if (!function_exists('woogst_admin_gst_single_order_edit')) {
      /**
       * Return instance of Gst_Order_Edit class
       *
       * @since 1.0.0
       *
       * @return Gst_Order_Edit
       */
      function woogst_admin_gst_single_order_edit()
      {
            return Gst_Order_Edit::get_instance();
      }
}


/**
 * Adding two GST fields into Billing fields ( gst_trade_name, gst_number )
 * @since 1.0.0
 * @param   array       $address    array of an address fields
 * @param   object      $order      current order object
 * @return mixed
 */
if (!function_exists('add_gst_to_woocommerce_order_fields')):
      function add_gst_to_woocommerce_order_fields($address, $order)
      {
            $address['gst_trade_name'] = $order->get_meta('_billing_gst_trade_name');
            $address['gst_number'] = $order->get_meta('_billing_gst_number');

            return $address;
      }
endif;



/**
 * Adding two GST fields into Billing fields ( gst_trade_name, gst_number )
 * @since 1.0.0
 * @param   array       $billing_fields    billing fields
 * @return array 
 */
if (!function_exists('add_gst_to_woocommerce_admin_billing_fields')):
      function add_gst_to_woocommerce_admin_billing_fields($billing_fields)
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
endif;


/**
 * Single order edit - validate & save with notice
 * @since 1.0.0
 * @param   string       $order_id    id of an order
 * @param   string       $post_data    (not in use)
 * @return void 
 */
if (!function_exists('save_and_validate_custom_gst_billing_fields')):
      function save_and_validate_custom_gst_billing_fields($order_id, $post_data)
      {
            // Get the order object
            $order = wc_get_order($order_id);

            // Define the GST pattern for validation
            $gst_pattern = '/^[0-9]{2}[A-Z]{3}[ABCFGHLJPTF]{1}[A-Z]{1}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/';

            // HPOS check
            $is_hpos_enabled = is_hpos_enabled();

            // Save the GST Holder Name if it is provided
            if (isset($_POST['_billing_gst_trade_name'])) {
                  $gst_holder_name = sanitize_text_field($_POST['_billing_gst_trade_name']);

                  if ($is_hpos_enabled) {
                        $order->update_meta_data('_billing_gst_trade_name', $gst_holder_name);
                  } else {
                        update_post_meta($order_id, '_billing_gst_trade_name', $gst_holder_name);
                  }
            }

            // Sanitize and validate the GST Number if it is provided
            if (isset($_POST['_billing_gst_number'])) {
                  $gst_number = sanitize_text_field($_POST['_billing_gst_number']);

                  if (!preg_match($gst_pattern, $gst_number)) {
                        // Show an error and stop execution
                        return WC_Admin_Meta_Boxes::add_error(__('Invalid GST Number. Please enter a valid GST Number.', 'woocommerce'));
                  }

                  // Update the GST Number if validation passes
                  if ($is_hpos_enabled) {
                        $order->update_meta_data('_billing_gst_number', $gst_number);
                  } else {
                        update_post_meta($order_id, '_billing_gst_number', $gst_number);
                  }
            }


            if ($is_hpos_enabled) {
                  $order->save();
            }

      }
endif;

