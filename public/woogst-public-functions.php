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

