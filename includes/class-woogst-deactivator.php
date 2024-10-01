<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://owlth.tech
 * @since      1.0.0
 *
 * @package    Woogst
 * @subpackage Woogst/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Woogst
 * @subpackage Woogst/includes
 * @author     Owlth Tech <owlthtech@gmail.com>
 */
class Woogst_Deactivator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate()
	{
		wp_clear_scheduled_hook('woogst_send_monthly_tax_report');
	}

	public static function woogst_remove_admin_capabilities()
	{
		global $wp_roles;
		$roles = $wp_roles->roles;

		if (!empty($roles)) {
			foreach ($roles as $role_slug => $role_details) {
				// Get the role object
				$role = get_role($role_slug);

				// Ensure the role exists before attempting to remove capabilities
				if ($role) {
					// Remove custom capabilities for Woogst functionalities.
					$role->remove_cap('manage_woogst_settings');

					// Read
					$role->remove_cap('read_gst_reports');
					$role->remove_cap('read_private_gst_reports');

					// Edit
					$role->remove_cap('edit_gst_report');
					$role->remove_cap('edit_gst_reports');
					$role->remove_cap('edit_private_gst_reports');
					$role->remove_cap('edit_published_gst_reports');
					$role->remove_cap('edit_others_gst_reports');

					// Publish
					$role->remove_cap('publish_gst_reports');

					// Delete
					$role->remove_cap('delete_gst_reports');
					$role->remove_cap('delete_published_gst_reports');
					$role->remove_cap('delete_private_gst_reports');
					$role->remove_cap('delete_others_gst_reports');
				}
			}
		}
	}


}
