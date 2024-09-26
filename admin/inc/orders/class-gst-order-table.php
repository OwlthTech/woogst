<?php

class Gst_Order_Table {
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
            /**
             * Woocommerce > Orders
             */
            
            // Add columns header
            add_filter('manage_edit-shop_order_columns', 'add_admin_order_list_custom_column', 20);
            add_filter( 'manage_woocommerce_page_wc-orders_columns', 'add_admin_order_list_custom_column', 20);

            // Add columns data
            add_action('manage_shop_order_posts_custom_column', 'display_wc_order_list_custom_column_content', 20, 2);
            add_action('manage_woocommerce_page_wc-orders_custom_column', 'display_wc_order_list_custom_column_content', 10, 2);

            // Filer in Order list for B2B orders
            add_action('woocommerce_order_list_table_restrict_manage_orders', 'dropdown_filter_for_gst_orders_list', 5);
            add_filter('woocommerce_order_query_args', 'filtered_data_query_args_for_order_list_table');
      }
}

// The main instance
if (!function_exists('woogst_admin_gst_order_table')) {
      /**
       * Return instance of Gst_Order_Table class
       *
       * @since 1.0.0
       *
       * @return Gst_Order_Table
       */
      function woogst_admin_gst_order_table()
      {//phpcs:ignore
            return Gst_Order_Table::get_instance();
      }
}

/**
 * Order list - column header
 * @since 1.0.0
 * @param   array       $columns    columns array
 * @return array 
 */

 if (!function_exists('add_admin_order_list_custom_column')):
      function add_admin_order_list_custom_column($columns)
      {
            $reordered_columns = array();

            // Inserting columns to a specific location
            foreach ($columns as $key => $column) {
                  $reordered_columns[$key] = $column;
                  if ($key == 'order_status') {
                        // Inserting after "Status" column
                        $reordered_columns['gst_claim'] = __('GST Claim?', 'theme_domain');
                        $reordered_columns['gst_details'] = __('GST Details', 'theme_domain');
                  }
            }
            return $reordered_columns;
      }
endif;


/**
 * Order list - column data
 * @since 1.0.0
 * @param   string       $column    column key string
 * 
 */
if (!function_exists('display_wc_order_list_custom_column_content')):
      function display_wc_order_list_custom_column_content($column, $order)
      {
            $claim_gst = $order->get_meta('_billing_claim_gst', true);
            switch ($column) {
                  case 'gst_claim':
                        echo $claim_gst ? "✔️" : "❌";
                        break;

                  case 'gst_details':
                        if ($claim_gst) {
                              $gst_holder_name = $order->get_meta('_billing_gst_trade_name', true);
                              if (empty($gst_holder_name)) {
                                    $gst_holder_name = get_post_meta($order->get_id(), '_billing_gst_trade_name', true);
                              }

                              $gst_number = $order->get_meta('_billing_gst_number', true);
                              if (empty($gst_number)) {
                                    $gst_number = get_post_meta($order->get_id(), '_billing_gst_number', true);
                              }
                        }

                        if (!empty($gst_holder_name)) {
                              echo "<strong>Trade name:</strong> " . $gst_holder_name . "<br/>";
                        }
                        if (!empty($gst_number)) {
                              echo "<strong>GST No:</strong> " . $gst_number;
                        } else {
                              echo '<small>-</small>';
                        }
                        break;

            }
      }
endif;


/**
 * @since 1.0.0
 * Order list - filter for b2b orders (orders having gst details)
 * @return void
 */
if (!function_exists('dropdown_filter_for_gst_orders_list')):
      function dropdown_filter_for_gst_orders_list()
      {
            $selected = isset($_GET['metadata']) ? esc_attr($_GET['metadata']) : '';
            $options = array(
                  '' => __('All Types', 'woocommerce'),
                  'b2b_orders' => __('B2B Orders', 'woocommerce')
            );

            echo '<select name="metadata" id="dropdown_shop_order_metadata">';
            foreach ($options as $value => $label_name) {
                  printf('<option value="%s" %s>%s</option>', $value, selected($selected, $value, false), $label_name);
            }
            echo '</select>';
      }
endif;


/**
 * @since 1.0.0
 * Order list - filtered order data for b2b orders (orders having gst details)
 * @return mixed
 */
if (!function_exists('filtered_data_query_args_for_order_list_table')):
      function filtered_data_query_args_for_order_list_table($query_args)
      {
            if (isset($_GET['metadata']) && $_GET['metadata'] === 'b2b_orders') {
                  $query_args['meta_key'] = '_billing_claim_gst';
                  $query_args['meta_value'] = '1';
            }
            return $query_args;
      }
endif;



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

