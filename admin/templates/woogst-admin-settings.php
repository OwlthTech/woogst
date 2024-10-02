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


?>



<div class="wrap">
      <div id="gst-message" style="margin-top: 10px"></div>
      <!-- Print the page title -->
      <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
      <!-- Here are our tabs -->
      <nav class="nav-tab-wrapper">
            <a href="?page=gst-settings" class="nav-tab <?php if ($tab === null): ?>nav-tab-active<?php endif; ?>">Tax
                  settings</a>
            <a href="?page=gst-settings&tab=gst-reports"
                  class="nav-tab <?php if ($tab === 'gst-reports'): ?>nav-tab-active<?php endif; ?>">Reports</a>
            <a href="?page=gst-settings&tab=gst-slabs"
                  class="nav-tab <?php if ($tab === 'gst-slabs'): ?>nav-tab-active<?php endif; ?>">GST Slabs</a>
            <a href="?page=gst-settings&tab=permissions"
                  class="nav-tab <?php if ($tab === 'permissions'): ?>nav-tab-active<?php endif; ?>">Permissions</a>
            <a href="?page=gst-settings&tab=status"
                  class="nav-tab <?php if ($tab === 'status'): ?>nav-tab-active<?php endif; ?>">Status</a>
      </nav>

      <div class="tab-content">
            <div class="wrap">
                  <?php
                  if (!(woogst_validator()->is_woocommerce_active()) && (woogst_validator()->is_woocommerce_installed())) {
                        echo 'WooGST requires WooCommerce plugin, please activate';
                        return;
                  }

                  if (!(woogst_validator()->is_woocommerce_active()) && !(woogst_validator()->is_woocommerce_installed())) {
                        echo 'WooGST requires WooCommerce plugin, please install and activate';
                        return;
                  }
                  ?>
                  <?php switch ($tab):
                        case 'gst-slabs':
                              gst_slabs_tab_content('gst-slabs');
                              break;
                        case 'permissions':
                              permissions_tab_content('permissions');
                              break;
                        case 'status':
                              echo 'Status of the report and options';
                              break;
                        case 'gst-reports':
                              gst_report_tab_content('gst-reports');
                              break;
                        default:
                              tax_setting_tab_content('settings');
                              break;
                  endswitch; ?>
            </div>
      </div>
</div>