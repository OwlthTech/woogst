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

include plugin_dir_path(dirname(__FILE__)) . 'templates/settings/default.php';
?>


<?php
$default_tab = null;
$tab = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;

/**
 * Get methods for tax
 */
$store_gst_details = [];
// All class name - Returns array of all tax_class Names only

// error_log(print_r($tax_classes, true));

// Returns array of tax_class Name & Slug by slug params 'gst'. False if not found.
$tax_classes_by_gst_slug = WC_Tax::get_tax_class_by('slug', 'gst');
// error_log(print_r($tax_classes_by_gst_slug, true));

// Returns array of an object for tax rates for parsed tax_class 'GST'
$tax_rates_for_gst_class = WC_Tax::get_rates_for_tax_class('GST');
// error_log(print_r($tax_rates_for_gst_class, true));
// $store_gst_details['tax_classes'] = $tax_classes;
// $store_gst_details['tax_classes_by_gst_slug'] = $tax_classes_by_gst_slug;
// $store_gst_details['tax_rates_for_gst_class'] = $tax_rates_for_gst_class;
// error_log(print_r($store_gst_details, true));
// Array of an GST tax rates


$store_state = WC()->countries->get_base_state();
$store_country = WC()->countries->get_base_country();

/**
 * Created 'GST' tax_class & tax_reates for that class
 * @param mixed $tax_classes
 * @param mixed $gst_tax_rate
 * @return void
 */


?>



<div class="wrap">
      <!-- Print the page title -->
      <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
      <!-- Here are our tabs -->
      <nav class="nav-tab-wrapper">
            <a href="?page=gst-settings" class="nav-tab <?php if ($tab === null): ?>nav-tab-active<?php endif; ?>">Tax
                  settings</a>
            <a href="?page=gst-settings&tab=permissions"
                  class="nav-tab <?php if ($tab === 'permissions'): ?>nav-tab-active<?php endif; ?>">Permissions</a>
            <a href="?page=gst-settings&tab=status"
                  class="nav-tab <?php if ($tab === 'status'): ?>nav-tab-active<?php endif; ?>">Status</a>
      </nav>

      <div class="tab-content">
            <div class="wrap">
                  <?php switch ($tab):
                        case 'permissions':
                              echo 'Permissions';
                              break;
                        case 'status':
                              echo 'Status of the report and options';
                              break;
                        default:
                              setting_tab_content();
                              break;
                  endswitch; ?>
            </div>
      </div>
</div>