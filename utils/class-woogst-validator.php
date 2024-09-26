<?php

/**
 * Adds helper function logics to validate
 */

class Woogst_Validator
{
    protected $is_woocommerce_installed;
    protected $is_woocommerce_active;
    protected $is_woo_tax_active;
    protected $is_woo_gst_tax_class_exist;

    function __construct()
    {

    }

    public static function is_woocommerce_installed()
    {
        $all_plugins = get_plugins();
        return array_key_exists('woocommerce/woocommerce.php', $all_plugins);
    }

    public static function is_woocommerce_active()
    {
        return in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')));
    }

    public static function is_woo_tax_active()
    {
        return wc_tax_enabled();
    }

    public static function is_woo_gst_tax_class_slug_exist($slug = null)
    {
        if (is_null($slug)) {
            $slug = 'gst';
        }
        $tax_class_slugs = WC_Tax::get_tax_class_slugs();
        return in_array($slug, $tax_class_slugs);
    }

}



if (!function_exists('validate_gst_number')) {
    /**
     * Validates gst number regex pattern
     * @param mixed $gst_number
     * @return string
     */
    function validate_gst_number($gst_number)
    {
        // Add your logic to validate GST number format or other rules
        return preg_match('/^[0-9]{2}[A-Z]{3}[ABCFGHLJPTF]{1}[A-Z]{1}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/', $gst_number);
    }
}