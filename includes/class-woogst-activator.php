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
class Woogst_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		woogst_report()->schedule_report();
	}

}
