<?php

/**
 * Adds helper function logics to validate
 */

class Woogst_Validator
{
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

    public function __construct()
    {
        add_action('before_woocommerce_init', array($this, 'wc_compatability'));
    }

    public static function wc_compatability()
    {
        if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', WOOGST_PLUGIN_FILE, true );
        }
    }

    public static function is_woocommerce_installed()
    {
        $all_plugins = get_plugins();
        return array_key_exists('woocommerce/woocommerce.php', $all_plugins);
    }

    public static function is_woo_tax_active()
    {
        return wc_tax_enabled();
    }

    public static function is_woocommerce_active()
    {
        $active_plugins = (array) get_option('active_plugins', array());
        if (is_multisite()) {
            $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
        }
        return in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins);
    }

}


// The main instance
if (!function_exists('woogst_validator')) {
    /**
     * Return instance of  Woogst_Validator class
     *
     * @since 1.0.0
     *
     * @return Woogst_Validator
     */
    function woogst_validator()
    {//phpcs:ignore
        return Woogst_Validator::get_instance();
    }
}