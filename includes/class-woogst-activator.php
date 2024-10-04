<?php

/**
 * Fired during plugin activation
 *
 * @link       https://owlth.tech
 * @since      1.0.0
 *
 * @package    Woogst
 * @subpackage Woogst/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Woogst
 * @subpackage Woogst/includes
 * @author     Owlth Tech <owlthtech@gmail.com>
 */
class Woogst_Activator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate()
	{
		// Checks and schedules action if enabled
		woogst_report()->handle_scheduled_report_action();
		
		self::woogst_add_admin_capabilities();
	}

	public static function woogst_add_admin_capabilities()
	{
		// Get the administrator role.
		$admin_role = get_role('administrator');
		if ($admin_role) {
			// Add custom capabilities for Woogst functionalities.
			$admin_role->add_cap('manage_woogst_settings');

			// Read
			$admin_role->add_cap('read_gst_reports');
			$admin_role->add_cap('read_private_gst_reports');

			// Edit
			$admin_role->add_cap('edit_gst_report');
			$admin_role->add_cap('edit_gst_reports');
			$admin_role->add_cap('edit_private_gst_reports');
			$admin_role->add_cap('edit_published_gst_reports');
			$admin_role->add_cap('edit_others_gst_reports');

			// Publish
			$admin_role->add_cap('publish_gst_reports');

			// Delete
			$admin_role->add_cap('delete_gst_reports');
			$admin_role->add_cap('delete_published_gst_reports');
			$admin_role->add_cap('delete_private_gst_reports');
			$admin_role->add_cap('delete_others_gst_reports');
		}
	}
}


// Class

// Rate for class
// $rate = WC_Tax::get_rates('GST');
// $rates_for_tax_class = WC_Tax::get_rates_for_tax_class('GST');

