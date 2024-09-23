<?php


if( !function_exists('is_hpos_enabled') ):
    function is_hpos_enabled() {
        // Check using the built-in method
        if (class_exists('\Automattic\WooCommerce\Utilities\OrderUtil')) {
            return \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
        }
    
        // Check using the option in the database (legacy method)
        return get_option('woocommerce_use_custom_orders_table', 'no') === 'yes';
    }
    endif;