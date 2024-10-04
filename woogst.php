<?php

/**
 * @link              https://owlth.tech
 * @since             1.0.0
 * @package           Woogst
 *
 * @wordpress-plugin
 * Plugin Name:       WooGST
 * Plugin URI:        https://owlth.tech
 * Description:       A WooCommerce extension for managing and calculating GST (Goods and Services Tax) for orders in India. It adds GST fields to checkout and handles GST validation and report generation.
 * Version:           1.0.0
 * Requires Plugins:  woocommerce
 * WC requires at least: 9.0
 * WC tested up to: 9.0
 * Requires at least: 6.6
 * Tested up to:      6.6
 * Requires PHP:      8.0
 * Author:            Owlth Tech
 * Author URI:        https://owlth.tech/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woogst
 * Domain Path:       /languages
 */


// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('WOOGST_VERSION', '1.0.0');
define('WOOGST_BASE_NAME', plugin_basename(__FILE__));
define('WOOGST_OPTION_PREFIX', 'woogst_');
define( 'WOOGST_PLUGIN_FILE', __FILE__ );
define( 'WOOGST_INVOICE_TEMPLATE_DIR', plugin_dir_path( __FILE__ ) . 'admin/templates/invoice/');
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woogst-activator.php
 */
function activate_woogst()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-woogst-activator.php';
	Woogst_Activator::woogst_add_admin_capabilities();
	Woogst_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woogst-deactivator.php
 */
function deactivate_woogst()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-woogst-deactivator.php';
	Woogst_Deactivator::woogst_remove_admin_capabilities();
	Woogst_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_woogst');
register_deactivation_hook(__FILE__, 'deactivate_woogst');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-woogst.php';
// require_once plugin_dir_path(__FILE__) . 'utils/wc-utils.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */

 
function run_woogst()
{

	$plugin = new Woogst();
	$plugin->run();
	// woogst_validator()->init();

}
run_woogst();
