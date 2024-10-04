<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://owlth.tech
 * @since      1.0.0
 *
 * @package    Woogst
 * @subpackage Woogst/admin/partials
 */

require_once plugin_dir_path(dirname(__FILE__)) . 'templates/settings/tax-settings.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'templates/settings/gst-slabs.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'templates/settings/gst-reports.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'templates/settings/gst-invoice.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'templates/settings/permissions.php';
?>


<?php
$default_tab = null;
$tab = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;

/**
 * Created 'GST' tax_class & tax_reates for that class
 * @param mixed $tax_classes
 * @param mixed $gst_tax_rate
 * @return void
 */

$gst_enable = woogst_get_option('gst-settings', 'enable_gst');
?>



<div class="wrap">
      <div class="woogst-logo-container">
            <img src="<?php echo plugin_dir_url(dirname(__FILE__)) . 'assets/woogst-trans.png'; ?>" alt="WooGST Logo" class="woogst-logo" />
      </div>
      <div id="gst-message" style="margin-top: 10px"></div>
      <!-- Print the page title -->
      <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
      <!-- Here are our tabs -->
      <nav class="nav-tab-wrapper">
            <a href="?post_type=gst-reports&page=gst-settings"
                  class="nav-tab <?php if ($tab === null): ?>nav-tab-active<?php endif; ?>">Tax
                  settings</a>

            <?php if ($gst_enable): ?>
                  <a href="?post_type=gst-reports&page=gst-settings&tab=gst-slabs"
                        class="nav-tab <?php if ($tab === 'gst-slabs'): ?>nav-tab-active<?php endif; ?>">GST Slabs</a>
                  <a href="?post_type=gst-reports&page=gst-settings&tab=gst-reports"
                        class="nav-tab <?php if ($tab === 'gst-reports'): ?>nav-tab-active<?php endif; ?>">GST Reports</a>
                  <a href="?post_type=gst-reports&page=gst-settings&tab=gst-invoice"
                        class="nav-tab <?php if ($tab === 'gst-invoice'): ?>nav-tab-active<?php endif; ?>">Order Invoices</a>
                  <a href="?post_type=gst-reports&page=gst-settings&tab=permissions"
                        class="nav-tab <?php if ($tab === 'permissions'): ?>nav-tab-active<?php endif; ?>">Permissions</a>
            <?php endif; ?>
            <a href="?post_type=gst-reports&page=gst-settings&tab=status"
                  class="nav-tab <?php if ($tab === 'status'): ?>nav-tab-active<?php endif; ?>">Status</a>
      </nav>

      <div class="tab-content">
            <div class="wrap">
                  <?php
                  if (!(woogst_validator()->is_woocommerce_active()) && (woogst_validator()->is_woocommerce_installed())) {
                        set_wp_admin_notice('WooGST requires WooCommerce plugin, please activate', 'error');
                        wp_redirect($_SERVER['REQUEST_URI']);
                  }

                  if (!(woogst_validator()->is_woocommerce_active()) && !(woogst_validator()->is_woocommerce_installed())) {
                        set_wp_admin_notice('WooGST requires WooCommerce plugin, please install and activate', 'error');
                        wp_redirect($_SERVER['REQUEST_URI']);
                  }
                  ?>
                  <?php switch ($tab):
                        case 'gst-slabs':
                              gst_slabs_tab_content($tab);
                              break;
                        case 'permissions':
                              permissions_tab_content($tab);
                              break;
                        case 'gst-reports':
                              gst_report_tab_content($tab);
                              break;
                        case 'status':
                              echo 'Coming soon...';
                              break;
                        case 'gst-invoice':
                              gst_invoice_tab_content($tab);
                              break;
                        default:
                              tax_setting_tab_content('gst-settings');
                              break;
                  endswitch; ?>
            </div>
      </div>
</div>