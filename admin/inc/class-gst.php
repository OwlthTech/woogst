<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) {
      exit;
}


class Gst
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
            /**
             * Checkout - frontend
             */
            // Add in checkout billing fields
            add_filter('woocommerce_billing_fields', 'gst_fields_add_in_checkout_billing_fields');
            // Remove optional label
            add_filter('woocommerce_form_field', 'remove_optional_text_from_gst_fields', 10, 4);
            // Sanitise and Validate
            add_filter('woocommerce_checkout_process', 'gst_fields_sanitize_and_validate');
            // Save in order meta during checkout
            add_action('woocommerce_checkout_update_order_meta', 'gst_fields_save_in_order_meta');
            // Add GST details (echo) html in email
            add_action('woocommerce_email_order_meta', 'gst_fields_add_in_email_display', 10, 3);
            // VAT to GST replacement
            add_filter('gettext', 'vat_to_gst_replacement', 20, 3);

            
            /**
             * Order list
             */
            // Populate custom columns with data
		add_action('manage_shop_order_posts_custom_column', 'display_wc_order_list_custom_column_content', 20, 2);
		add_action('manage_woocommerce_page_wc-orders_custom_column', 'display_wc_order_list_custom_column_content', 10, 2);
		
            // Filer in Order list for B2B orders
            add_action( 'woocommerce_order_list_table_restrict_manage_orders', 'dropdown_filter_for_gst_orders_list', 5 );
            add_filter('woocommerce_order_query_args', 'filtered_data_query_args_for_order_list_table');

            /**
             * Order edit - single order
             */
            // Get the field values to be displayed in admin Order edit pages
            add_filter('woocommerce_order_formatted_billing_address', 'add_gst_to_woocommerce_order_fields', 10, 2);
            // Make the custom GST billing fields editable in Admin order pages
            add_filter('woocommerce_admin_billing_fields', 'add_gst_to_woocommerce_admin_billing_fields');
            // Save and validate the custom GST fields when the order's billing address is updated in the admin order page
            add_action('woocommerce_process_shop_order_meta', 'save_and_validate_custom_gst_billing_fields', 10, 2);

      }
}

// The main instance
if (!function_exists('woogst_gst')) {
      /**
       * Return instance of Gst class
       *
       * @since 1.0.0
       *
       * @return Gst
       */
      function woogst_gst()
      {//phpcs:ignore
            return Gst::get_instance();
      }
}


/**
 * Add GST fields into checkout, adding fields such as
 * Checkout - billing_claim_gst
 * Input text - billing_gst_trade_name
 * Input text - billing_gst_number (Validating on checkout post request & jquery)
 * @since 1.0.0
 * @param   string       $fields          checkout fields
 * @return mixed
 */
if (!function_exists('gst_fields_add_in_checkout_billing_fields')) {
      function gst_fields_add_in_checkout_billing_fields($fields)
      {
            // Add "Claim your GST" checkbox
            $fields['billing_claim_gst'] = array(
                  'type' => 'checkbox',
                  'label' => __('Claim your GST', 'woocommerce'),
                  'required' => false,
                  'class' => array('form-row-wide'),
                  'clear' => true
            );

            // Add Trade Name field (initially hidden)
            $fields['billing_gst_trade_name'] = array(
                  'type' => 'text',
                  'label' => __('Trade Name', 'woocommerce'),
                  'placeholder' => _x('Trade Name as per GSTIN registration', 'placeholder', 'woocommerce'),
                  'required' => false,
                  'class' => array('form-row-wide', 'gst-field'), // Hidden initially
                  'clear' => true
            );

            // Add GST field to billing section
            $fields['billing_gst_number'] = array(
                  'type' => 'text',
                  'label' => __('GST Number', 'woocommerce'),
                  'placeholder' => _x('Enter your GSTIN registration number', 'placeholder', 'woocommerce'),
                  'required' => false,
                  'class' => array('form-row-wide', 'gst-field'),
                  'clear' => true
            );
            return $fields;
      }
}

/**
 * Just removing (optional) text from label of the checkout fields
 * @since 1.0.0
 * @param   string       $fields          checkout fields
 * @param   string       $key             checkout key for fields
 * @return mixed
 */
if (!function_exists('remove_optional_text_from_gst_fields')) {
      function remove_optional_text_from_gst_fields($field, $key, $args, $value)
      {
            // Check if the field is either the GST Holder Name or GST Number
            if ($key === 'billing_gst_trade_name' || $key === 'billing_gst_number') {
                  // Remove '(optional)' text if present in the label
                  if (strpos($field, '(optional)') !== false) {
                        $field = str_replace('(optional)', '', $field);
                  }
            }
            return $field;
      }
}

/**
 * Sanitize and validate GST number during checkout process
 * @since 1.0.0
 * @return void
 */
if (!function_exists('gst_fields_sanitize_and_validate')) {
      function gst_fields_sanitize_and_validate()
      {
            // billing_claim_gst checkbox
            if (!isset($_POST['billing_claim_gst']) || $_POST['billing_claim_gst'] !== '1') {
                  // If the checkbox is not checked, unset GST fields
                  unset($_POST['billing_gst_number']);
                  unset($_POST['billing_gst_trade_name']);

            } else {

                  if (!isset($_POST['billing_gst_number']) || empty($_POST['billing_gst_number'])) {
                        wc_add_notice(__('GSTIN number is required to claim GST.', 'owlth-wp-manager'), 'error');
                  }

                  if (isset($_POST['billing_gst_number']) && !empty($_POST['billing_gst_number'])) {
                        $gst_number = strtoupper(sanitize_text_field($_POST['billing_gst_number']));
                        if (!validate_gst_number($gst_number)) {
                              wc_add_notice(__('GSTIN number is invalid.', 'owlth-wp-manager'), 'error');
                        }
                  }

                  // Check if GST holder name is provided (assuming you have a field for it)
                  if (!isset($_POST['billing_gst_trade_name']) || empty($_POST['billing_gst_trade_name'])) {
                        wc_add_notice(__('GST trade name is required.', 'owlth-wp-manager'), 'error');
                  }
            }
      }
}
/**
 * Utility function to validate GST number
 * @since 1.0.0
 * @param   string       $gst_number        gst_number
 * @return bool|int
 */
if (!function_exists('validate_gst_number')):
      function validate_gst_number($gst_number)
      {
            // Add your logic to validate GST number format or other rules
            return preg_match('/^[0-9]{2}[A-Z]{3}[ABCFGHLJPTF]{1}[A-Z]{1}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/', $gst_number);
      }
endif;


/**
 * Save gst fields into order meta during new checkout order
 * @since 1.0.0
 * @param   string       $order_id        current order id
 * @return void
 */
if (!function_exists('gst_fields_save_in_order_meta')) {
      function gst_fields_save_in_order_meta($order_id)
      {
            // error_log(print_r($_POST, true));
            // _billing_claim_gst
            $claim_gst = isset($_POST['billing_claim_gst']) && $_POST['billing_claim_gst'] === '1';
            $gst_holder_name = isset($_POST['billing_gst_trade_name']) ? sanitize_text_field($_POST['billing_gst_trade_name']) : '';
            $gst_number = isset($_POST['billing_gst_number']) ? sanitize_text_field($_POST['billing_gst_number']) : '';

            // HPOS check
            $is_hpos_enabled = is_hpos_enabled();

            $order = wc_get_order($order_id);
            if ($claim_gst) {
                  // Save GST Holder Name and GST Number only if the checkbox is checked and fields are not empty
                  if (!empty($gst_holder_name)) {
                        if ($is_hpos_enabled) {
                              $order->update_meta_data('_billing_gst_trade_name', $gst_holder_name);
                        } else {
                              update_post_meta($order_id, '_billing_gst_trade_name', $gst_holder_name);
                        }
                  }

                  if (!empty($gst_number)) {
                        if ($is_hpos_enabled) {
                              $order->update_meta_data('_billing_gst_number', $gst_number);
                        } else {
                              update_post_meta($order_id, '_billing_gst_number', $gst_number);
                        }
                  }

            } else {
                  // If the checkbox is not checked, remove any previously saved GST data
                  if ($is_hpos_enabled) {
                        $order = wc_get_order($order_id);
                        $order->delete_meta_data('_billing_gst_trade_name');
                        $order->delete_meta_data('_billing_gst_number');
                  } else {
                        delete_post_meta($order_id, '_billing_gst_trade_name');
                        delete_post_meta($order_id, '_billing_gst_number');
                  }
            }

            // Save the meta in the database for HPOS
            if ($is_hpos_enabled) {
                  $order->save();
            }
      }
}


/**
 * Add GST fields into Email
 * @since 1.0.0
 * @param   object       $order       current order object
 * @return void
 */
if (!function_exists('gst_fields_add_in_email_display')) {
      function gst_fields_add_in_email_display($order, $sent_to_admin = false, $plain_text = false)
      {
            if (is_a($order, 'WC_Order')) {
                  $gst_holder_name = $order->get_meta('_billing_gst_trade_name');
                  $gst_number = $order->get_meta('_billing_gst_number');

                  if ($gst_number || $gst_holder_name) {
                        echo '<p><strong>' . __('GST Information') . ':</strong><br>';
                        if ($gst_holder_name) {
                              echo __('GST Holder Name: ') . esc_html($gst_holder_name) . '<br>';
                        }
                        if ($gst_number) {
                              echo __('GST Number: ') . esc_html($gst_number) . '</p>';
                        }
                  }
            }
      }
}


/**
 * Replace VAT to GST
 * @since 1.0.0
 * @param   string       $translated_text       text to replace with (VAT)
 * @param   string       $text                  text to replace (GST)
 * @return mixed
 */
if (!function_exists('vat_to_gst_replacement')) {
      function vat_to_gst_replacement($translated_text, $text)
      {
            if ($text === 'VAT') {
                  $translated_text = 'GST';
            }
            return $translated_text;
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

