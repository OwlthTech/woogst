<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://owlth.tech
 * @since      1.0.0
 *
 * @package    Woogst
 * @subpackage Woogst/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Woogst
 * @subpackage Woogst/public
 * @author     Owlth Tech <owlthtech@gmail.com>
 */
class Woogst_Public
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */

	public $settings;

	public function __construct($plugin_name, $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;

		/**
		 * Add gst fields in checkout
		 * Possible hooks
		 * 'woocommerce_after_checkout_billing_form'	- default
		 * 'woocommerce_review_order_before_submit'
		 */
		$this->settings = woogst_get_option('gst-settings');
		if (isset($this->settings) && !empty($this->settings)) {
			if (isset($this->settings['gst_checkout']) && $this->settings['gst_checkout'] && isset($this->settings['enable_gst']) && $this->settings['enable_gst']) {
				if (isset($this->settings['gst_checkout_location']) && $this->settings['gst_checkout_location'] === 'before_payment') {
					add_action('woocommerce_review_order_before_payment', array($this, 'woogst_checkout_gst_fields'));
				}
				if (isset($this->settings['gst_checkout_location']) && $this->settings['gst_checkout_location'] === 'after_billing') {
					add_action('woocommerce_after_checkout_billing_form', array($this, 'woogst_checkout_gst_fields'));
				}
				// Remove optional label
				add_filter('woocommerce_form_field', array($this, 'checkout_remove_optional_text_from_gst_fields'), 10, 4);
				// Sanitise and Validate
				add_filter('woocommerce_checkout_process', array($this, 'woogst_checkout_fields_sanitize_and_validate'));
				// Save in order meta during checkout
				add_action('woocommerce_checkout_update_order_meta', array($this, 'woogst_checkout_fields_save_in_order_meta'));
				// VAT to GST replacement
				add_filter('gettext', array($this, 'vat_to_gst_replacement'), 20, 3);
			}
			// Add GST details (echo) html in email
			if (isset($this->settings['gst_checkout_email']) && $this->settings['gst_checkout_email'] && isset($this->settings['enable_gst']) && $this->settings['enable_gst']) {
				add_action('woocommerce_email_order_meta', array($this, 'gst_fields_add_in_email_display'), 10, 3);
				add_action('woocommerce_email_before_order_table', array($this, 'woogst_add_gst_details_to_email'), 10, 4);
			}

		}
	}

	/**
	 * Registers the JavaScript for the public-facing side of the site.
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		if (is_checkout() && isset($this->settings['gst_checkout']) && $this->settings['enable_gst']) {
			wp_enqueue_script('gst-validation', plugin_dir_url(__FILE__) . '/js/checkout-validations.js', array('jquery'), $this->version, true);
		}
	}

	/**
	 * Adds GST fields in checkout page
	 * @since    1.0.0
	 * @param mixed $checkout
	 * @return void
	 */
	public function woogst_checkout_gst_fields($checkout)
	{
		$billing_country = WC()->customer->get_billing_country();
		$is_india = ($billing_country == "IN");
		$style = $is_india ? '' : 'display:none;';
		$checkout = WC()->checkout();
		echo '<div id="billing_gst_fields" style="' . esc_attr($style) . '">';
		// Add "Claim your GST" checkbox
		woocommerce_form_field(
			'billing_claim_gst',
			array(
				'type' => 'checkbox',
				'label' => __('Use GSTIN for claiming input tax', 'woocommerce'),
				'required' => false,
				'class' => array('form-row-wide'),
				'clear' => true,
			)
		);
		echo '<div id="gst_fields" style="display:none;">';
		// Add Trade Name field (initially hidden)
		woocommerce_form_field(
			'billing_gst_trade_name',
			array(
				'type' => 'text',
				'label' => __('Trade Name', 'woocommerce'),
				'placeholder' => _x('GSTIN registration trade name', 'placeholder', 'woocommerce'),
				'required' => false,
				'class' => array('form-row-wide', 'gst-field'), // Hidden initially if needed
				'clear' => true,
			),
			$checkout->get_value('billing_gst_trade_name') // Prepopulate with existing value if available
		);
		// Add GST Number field
		woocommerce_form_field(
			'billing_gst_number',
			array(
				'type' => 'text',
				'label' => __('GST Number', 'woocommerce'),
				'placeholder' => _x('GSTIN registration number', 'placeholder', 'woocommerce'),
				'required' => false,
				'class' => array('form-row-wide', 'gst-field'),
				'clear' => true,
			),
			$checkout->get_value('billing_gst_number') // Prepopulate with existing value if available
		);
		echo '</div>';
		echo '</div>';
	}

	/**
	 * Removes (optional) text from label of the checkout fields
	 * @since    1.0.0
	 * @param mixed $field
	 * @param mixed $key
	 * @param mixed $args
	 * @param mixed $value
	 * @return mixed
	 */
	public function checkout_remove_optional_text_from_gst_fields($field, $key, $args, $value)
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

	/**
	 * Sanitizes and validates GST number during checkout process
	 * @since    1.0.0
	 * @return void
	 */
	public function woogst_checkout_fields_sanitize_and_validate()
	{
		if (get_woocommerce_currency() == "INR" && WC()->customer->get_billing_country() == "IN") {
			// billing_claim_gst checkbox
			if (!isset($_POST['billing_claim_gst']) || intval($_POST['billing_claim_gst']) != 1) {
				// If the checkbox is not checked, unset GST fields
				unset($_POST['billing_gst_number']);
				unset($_POST['billing_gst_trade_name']);
			} else {
				// Check if GST holder name is provided (assuming you have a field for it)
				if (!isset($_POST['billing_gst_trade_name']) || empty($_POST['billing_gst_trade_name'])) {
					wc_add_notice(__('GST trade name is required.', 'woogst'), 'error');
				}
				if (!isset($_POST['billing_gst_number']) || empty($_POST['billing_gst_number'])) {
					wc_add_notice(__('GSTIN number is required to claim GST.', 'woogst'), 'error');
				}
				if (isset($_POST['billing_gst_number']) && !empty($_POST['billing_gst_number'])) {
					$gst_number = strtoupper(sanitize_text_field($_POST['billing_gst_number']));
					if (!validate_gst_number($gst_number)) {
						wc_add_notice(__('GSTIN number is invalid.', 'woogst'), 'error');
					}
					if (isset($this->settings['gst_billing_state_validate']) && $this->settings['gst_billing_state_validate'] && validate_gst_number($gst_number)) {
						$billing_state = sanitize_text_field($_POST['billing_state']);
						$billing_state_code = $this->getBillingStateCode($billing_state);
						$gst_state_code = substr($gst_number, 0, 2);
						if ($gst_state_code != $billing_state_code) {
							wc_add_notice(__('GSTIN is not belongs to your billing location (billing state).'), 'error');
						}
					}
				}

			}
		}
	}

	public function getBillingStateCode($state = NULL)
	{
		$state = strtoupper($state);
		$states = array(
			'AN' => '35',
			'AP' => '37',
			'AR' => '12',
			'AS' => '18',
			'BR' => '10',
			'CH' => '04',
			'CT' => '22',
			'DN' => '26',
			'DD' => '25',
			'DL' => '07',
			'GA' => '30',
			'GJ' => '24',
			'HR' => '06',
			'HP' => '02',
			'JK' => '01',
			'JH' => '20',
			'KA' => '29',
			'KL' => '32',
			'LD' => '31',
			'MP' => '23',
			'MH' => '27',
			'MN' => '14',
			'ML' => '17',
			'MZ' => '15',
			'NL' => '13',
			'OR' => '21',
			'PY' => '34',
			'PB' => '03',
			'RJ' => '08',
			'SK' => '11',
			'TN' => '33',
			'TS' => '36',
			'TR' => '16',
			'UP' => '09',
			'UK' => '05',
			'WB' => '19',
		);

		return array_key_exists($state, $states) ? $states[$state] : '';
	}

	/**
	 * Saves gst fields into order meta during new checkout order
	 * @since    1.0.0
	 * @param mixed $order_id
	 * @return void
	 */
	public function woogst_checkout_fields_save_in_order_meta($order_id)
	{
		// _billing_claim_gst
		$gst_claim = isset($_POST['billing_claim_gst']) ? $_POST['billing_claim_gst'] : '';
		$gst_holder_name = isset($_POST['billing_gst_trade_name']) ? sanitize_text_field($_POST['billing_gst_trade_name']) : '';
		$gst_number = isset($_POST['billing_gst_number']) ? sanitize_text_field($_POST['billing_gst_number']) : '';
		if (!empty($gst_claim) && !empty($gst_holder_name) && !empty($gst_number)) {
			$order = wc_get_order($order_id);
			$order->update_meta_data('_billing_claim_gst', $gst_claim);
			$order->update_meta_data('_billing_gst_trade_name', $gst_holder_name);
			$order->update_meta_data('_billing_gst_number', $gst_number);
			$order->save();
		}
	}

	/**
	 * Adds GST fields into Email
	 * @since    1.0.0
	 * @param mixed $order
	 * @param mixed $sent_to_admin
	 * @param mixed $plain_text
	 * @return void
	 */
	public function gst_fields_add_in_email_display($order, $sent_to_admin = false, $plain_text = false)
	{
		if (is_a($order, 'WC_Order')) {
			$gst_holder_name = $order->get_meta('_billing_gst_trade_name');
			$gst_number = $order->get_meta('_billing_gst_number');

			if ($gst_number || $gst_holder_name) {
				echo '<p><strong><u>' . __('Order GST Details') . ':</u></strong><br>';
				if ($gst_holder_name) {
					echo __('GST Trade Name: ') . esc_html($gst_holder_name) . '<br>';
				}
				if ($gst_number) {
					echo __('GST Number: ') . esc_html($gst_number) . '</p>';
				}
			}
		}
	}

	/**
	 * Adds store GST details in order confirmation email
	 * @param mixed $order
	 * @param mixed $sent_to_admin
	 * @param mixed $plain_text
	 * @param mixed $email
	 * @return void
	 */
	public function woogst_add_gst_details_to_email($order, $sent_to_admin, $plain_text, $email) {
		// Check if GST is enabled and if it should be shown in emails
		if (woogst_get_option('gst-settings', 'enable_gst') && woogst_get_option('gst-settings', 'gst_checkout_email')) {
	  
		    // Retrieve GST store details from the settings
		    $store_gst_name = woogst_get_option('gst-settings', 'store_gst_name');
		    $store_gst_number = woogst_get_option('gst-settings', 'store_gst_number');
	  
		    // Check if both GST name and number are set before displaying
		    if (!empty($store_gst_name) && !empty($store_gst_number)) {
	  
			  // Format and display the GST details
			  $gst_details = sprintf(
				'<p><strong>%s:</strong> %s<br><strong>%s:</strong> %s</p>',
				__('Seller trade name', 'woogst'),
				esc_html($store_gst_name),
				__('Seller GST number', 'woogst'),
				esc_html($store_gst_number)
			  );
	  
			  // Output the GST details before the order table
			  echo wp_kses_post($gst_details);
		    }
		}
	  }

	/**
	 * Replaces VAT to GST
	 * @param mixed $translated_text
	 * @param mixed $text
	 * @return mixed
	 */
	public function vat_to_gst_replacement($translated_text, $text)
	{
		if ($text === 'VAT') {
			$translated_text = 'GST';
		}
		return $translated_text;
	}

}
