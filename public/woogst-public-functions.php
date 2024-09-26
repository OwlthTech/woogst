<?php

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
