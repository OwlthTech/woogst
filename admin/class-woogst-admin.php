<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://owlth.tech
 * @since      1.0.0
 *
 * @package    Woogst
 * @subpackage Woogst/admin
 */

require_once plugin_dir_path(dirname(__FILE__)) . 'admin/inc/gst/class-gst.php';

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woogst
 * @subpackage Woogst/admin
 * @author     Owlth Tech <owlthtech@gmail.com>
 */

class Woogst_Admin
{
    // Declare properties for plugin name and version
    protected $plugin_name;
    protected $version;
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        add_filter('plugin_action_links_' . WOOGST_BASE_NAME, array($this, 'add_settings_link'));

        // Registers 'gst-reports' post type
        add_action('init', [$this, 'register_gst_report_post_type']);

        add_action('admin_menu', [$this, 'woogst_menu']);

        // Sets notice using transient options
        add_action('admin_notices', 'woo_gst_admin_notice_message');

        // Checks woocommerce installed & activated and sets admin notice
        add_action('admin_notices', 'set_wp_admin_notice_active_woo');

        add_action('wp_ajax_woogst_create_gst_tax_class', 'woogst_create_gst_tax_class');
        add_action('wp_ajax_nopriv_woogst_create_gst_tax_class', 'woogst_create_gst_tax_class');
        // add_action('init', 'woogst_create_gst_tax_rates', 10, 2);

        add_action('wp_ajax_woogst_get_tax_rates', 'woogst_get_tax_rates');
        add_action('wp_ajax_nopriv_woogst_get_tax_rates', 'woogst_get_tax_rates');

        add_action('wp_ajax_woogst_delete_gst_tax_rates', 'woogst_delete_gst_tax_rates');
        add_action('wp_ajax_nopriv_woogst_delete_gst_tax_rates', 'woogst_delete_gst_tax_rates');

        add_action('wp_ajax_woogst_save_permissions', array($this, 'save_permissions'));
        add_action('wp_ajax_nopriv_woogst_save_permissions', array($this, 'save_permissions'));

        $woo_gst = woogst_gst();
        $woo_gst->init();

    }
    public function add_settings_link($links)
    {
        $settings_link = '<a href="' . admin_url('admin.php?page=gst-settings') . '">' . __('Settings') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Woogst_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Woogst_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        $has_woocomemrce = class_exists('WooCommerce');
        if (!$has_woocomemrce) {
            return;
        }
        $screen_id = get_current_screen()->id ?: '';
        // var_dump($screen_id);
        if ($screen_id === wc_get_page_screen_id('shop-order')) {
            wp_enqueue_style($this->plugin_name . 'shop-order', plugin_dir_url(__FILE__) . 'css/woogst-admin.css', array(), $this->version, 'all');
        }
        if ($screen_id === 'woocommerce_page_gst-settings') {
            wp_enqueue_style($this->plugin_name . '-tabs', plugin_dir_url(__FILE__) . 'css/woogst-tabs.css', array(), $this->version, 'all');
        }
        if ($screen_id === 'woocommerce_page_gst-settings' && isset($_GET['tab']) && $_GET['tab'] === 'permissions') {
            wp_enqueue_style($this->plugin_name . 'permissions', plugin_dir_url(__FILE__) . 'css/woogst-permissions.css', array(), $this->version, 'all');
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Woogst_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Woogst_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        $has_woocomemrce = class_exists('WooCommerce');
        if (!$has_woocomemrce) {
            return;
        }
        $screen = get_current_screen();
        $screen_id = $screen ? $screen->id : '';
        // Check screen base and page
        if (class_exists('WooCommerce') && $screen_id === wc_get_page_screen_id('shop-order')) {
            wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/woogst-admin.js', array('jquery'), $this->version, false);
        }
        if ($screen_id === 'woocommerce_page_gst-settings' && isset($_GET['tab']) && $_GET['tab'] === 'gst-slabs') {
            wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/woogst-slabs.js', array('jquery'), $this->version, false);
        }
        if ($screen_id === 'woocommerce_page_gst-settings' && !isset($_GET['tab'])) {
            wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/woogst-valid-gst.js', array('jquery'), $this->version, false);
        }
    }

    /**
     * Registers custom post type 'gst-reports'
     * 
     */
    public function register_gst_report_post_type()
    {
        $labels = array(
            'name' => _x('GST Reports', 'Post Type General Name', 'woogst'),
            'singular_name' => _x('GST Report', 'Post Type Singular Name', 'woogst'),
            'menu_name' => __('WooGST', 'woogst'),
            'name_admin_bar' => __('Post Type', 'woogst'),
            'archives' => __('Item Archives', 'woogst'),
            'attributes' => __('Item Attributes', 'woogst'),
            'parent_item_colon' => __('Parent Item:', 'woogst'),
            'all_items' => __('GST Reports', 'woogst'),
            'add_new_item' => __('Add New Item', 'woogst'),
            'add_new' => __('Generate Report', 'woogst'),
            'new_item' => __('New Item', 'woogst'),
            'edit_item' => __('Edit Item', 'woogst'),
            'update_item' => __('Update Item', 'woogst'),
            'view_item' => __('View Item', 'woogst'),
            'view_items' => __('View Items', 'woogst'),
            'search_items' => __('Search Item', 'woogst'),
            'not_found' => __('Not found', 'woogst'),
            'not_found_in_trash' => __('Not found in Trash', 'woogst'),
            'featured_image' => __('Featured Image', 'woogst'),
            'set_featured_image' => __('Set featured image', 'woogst'),
            'remove_featured_image' => __('Remove featured image', 'woogst'),
            'use_featured_image' => __('Use as featured image', 'woogst'),
            'insert_into_item' => __('Insert into item', 'woogst'),
            'uploaded_to_this_item' => __('Uploaded to this item', 'woogst'),
            'items_list' => __('Items list', 'woogst'),
            'items_list_navigation' => __('Items list navigation', 'woogst'),
            'filter_items_list' => __('Filter items list', 'woogst'),
        );

        // Custom capabilities
        $capabilities = array(
            'edit_post' => 'edit_gst_report',         // Edit a single GST report
            'read_post' => 'view_gst_report',         // View a single GST report
            'delete_post' => 'delete_gst_report',       // Delete a single GST report
            'edit_posts' => 'edit_gst_reports',        // Edit multiple GST reports
            'edit_others_posts' => 'edit_others_gst_reports', // Edit other users' GST reports
            'publish_posts' => 'publish_gst_reports',     // Publish GST reports
            'read_private_posts' => 'read_private_gst_reports',// View private GST reports
            'delete_posts' => 'delete_gst_reports',      // Bulk delete GST reports
        );

        // Arguments for the custom post type
        $args = array(
            'label' => __('GST Report', 'woogst'),
            'description' => __('Registering post type for GST monthly reports', 'woogst'),
            'labels' => $labels,
            'supports' => array('title', 'comments', 'trackbacks', 'revisions', 'page-attributes'),
            'taxonomies' => array('sale_type'),
            'hierarchical' => false,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 5,
            'menu_icon' => 'dashicons-money',
            'capability_type' => array('gst_report', 'gst_reports'),  // Custom capability types
            'capabilities' => $capabilities,
            'map_meta_cap' => true,  // Map capabilities to users properly
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => false,
            'can_export' => true,
            'has_archive' => true,
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'show_in_rest' => true,
            'rest_base' => 'gst_reports',
            'rest_controller_class' => 'WOO_GST_Order_Reports',
        );

        register_post_type('gst-reports', $args);
    }



    public function woogst_menu()
    {
        add_submenu_page(
            'woocommerce',
            'GST Settings',
            'GST Settings',
            'manage_woogst_settings',
            'gst-settings',
            [$this, 'woogst_menu_page_callback']
        );
    }

    public function woogst_menu_page_callback()
    {
        if (!current_user_can('manage_woogst_settings')) {
            set_wp_admin_notice('You are not authorized to access this page.', 'error');
            return;
        }

        // Check if the form has been submitted
        if (isset($_POST['woogst_form_submitted']) && $_POST['woogst_form_submitted'] == 'yes') {
            if (isset($_POST['tab'])) {
                $tab = $_POST['tab'];
                $this->woogst_save_settings($tab);
            }
        }

        include plugin_dir_path(__FILE__) . 'templates/woogst-admin-settings.php';
    }

    public function woogst_save_settings($tab)
    {
        if (!current_user_can('manage_woogst_settings')) {
            set_wp_admin_notice('You are not authorized to change settings.', 'error');
            return;
        }
        if (isset($tab)) {
            switch ($tab) {
                case 'settings':
                    // Sanitize and collect the form data
                    $settings = [
                        'store_gst_name' => isset($_POST['store_gst_name']) ? $_POST['store_gst_name'] : '',
                        'store_gst_number' => isset($_POST['store_gst_number']) ? $_POST['store_gst_number'] : '',
                        'gst_checkout' => isset($_POST['gst_checkout']) ? 1 : 0,
                        'gst_billing_state_validate' => isset($_POST['gst_billing_state_validate']) ? 1 : 0
                    ];
                    $update_settings = update_option(WOOGST_OPTION_PREFIX . $tab, $settings);
                    break;

                case 'gst-reports':
                    // Sanitize and collect the form data
                    $settings = [
                        'schedule_report' => isset($_POST['schedule_report']) ? 1 : 0,
                        'schedule_report_email' => isset($_POST['schedule_report_email']) ? 1 : 0,
                        'schedule_report_email_id' => isset($_POST['schedule_report_email_id']) ? sanitize_email($_POST['schedule_report_email_id']) : '',
                        'schedule_report_private' => isset($_POST['schedule_report_private']) ? 1 : 0
                    ];
                    $update_settings = update_option(WOOGST_OPTION_PREFIX . $tab, $settings);
                    break;

                case 'gst-slabs':
                    // Sanitize and collect the form data
                    $settings = [
                        'gst_tax_class' => isset($_POST['gst_tax_class']) ? array_map('sanitize_text_field', $_POST['gst_tax_class']) : [],
                    ];
                    $update_settings = update_option(WOOGST_OPTION_PREFIX . $tab, $settings);
                    break;

                case 'permissions':
                    $this->woogst_save_permissions();
            }
        }

        if (isset($update_settings)) {
            set_wp_admin_notice('Settings are saved successfully', 'success');
        }
        wp_redirect($_SERVER['REQUEST_URI']);
    }

    /**
     * Handle AJAX request to save permissions.
     */
    public function woogst_save_permissions()
    {
        // Check that the current user has permission to manage Woogst settings
        if (!current_user_can('manage_woogst_settings')) {
            set_wp_admin_notice('Unauthorized request.', 'error');
            return;
        }

        global $wp_roles;
        $roles = $wp_roles->roles;

        foreach ($roles as $role_slug => $role_details) {
            // Skip the administrator role to avoid modifying its permissions
            if ($role_slug === 'administrator') {
                continue;
            }

            $role = get_role($role_slug);

            // Manage Woogst Settings
            if (isset($_POST['manage_woogst_settings'][$role_slug]) && $_POST['manage_woogst_settings'][$role_slug] == '1') {
                $role->add_cap('manage_woogst_settings');
            } else {
                $role->remove_cap('manage_woogst_settings');
            }

            // View GST Reports
            if (isset($_POST['read_gst_reports'][$role_slug]) && $_POST['read_gst_reports'][$role_slug] == '1') {
                $role->add_cap('read_gst_reports');
                $role->add_cap('read_private_gst_reports');
            } else {
                $role->remove_cap('read_gst_reports');
                $role->remove_cap('read_private_gst_reports');
            }

            // Edit GST Reports
            if (isset($_POST['edit_gst_reports'][$role_slug]) && $_POST['edit_gst_reports'][$role_slug] == '1') {
                $role->add_cap('edit_gst_report');
                $role->add_cap('edit_gst_reports');
                $role->add_cap('edit_private_gst_reports');
                $role->add_cap('edit_published_gst_reports');
                $role->add_cap('edit_others_gst_reports');
            } else {
                $role->remove_cap('edit_gst_report');
                $role->remove_cap('edit_gst_reports');
                $role->remove_cap('edit_private_gst_reports');
                $role->remove_cap('edit_published_gst_reports');
                $role->remove_cap('edit_others_gst_reports');
            }

            // Create GST Reports
            if (isset($_POST['publish_gst_reports'][$role_slug]) && $_POST['publish_gst_reports'][$role_slug] == '1') {
                $role->add_cap('publish_gst_reports');
            } else {
                $role->remove_cap('publish_gst_reports');
            }

            // Delete GST Reports
            if (isset($_POST['delete_gst_reports'][$role_slug]) && $_POST['delete_gst_reports'][$role_slug] == '1') {
                $role->add_cap('delete_gst_reports');
                $role->add_cap('delete_published_gst_reports');
                $role->add_cap('delete_private_gst_reports');
                $role->add_cap('delete_others_gst_reports');
            } else {
                $role->remove_cap('delete_gst_reports');
                $role->remove_cap('delete_published_gst_reports');
                $role->remove_cap('delete_private_gst_reports');
                $role->remove_cap('delete_others_gst_reports');
            }
        }

        set_wp_admin_notice("Permissions updated successfully", "success");
        wp_redirect($_SERVER['REQUEST_URI']);
        exit;
    }


}



function woogst_get_options($tab)
{
    // Get the existing settings for 'woogst_settings'
    $gst_settings = get_option(WOOGST_OPTION_PREFIX . $tab, []);

    // Ensure it's an array and update the gst_tax_rates within the settings
    if (!is_array($gst_settings)) {
        set_wp_admin_notice('option ' . WOOGST_OPTION_PREFIX . $tab . ' is not an array', 'error');
        wp_redirect(get_admin_url(null, '/admin.php?page=gst-settings'));
        exit;
    }
    return $gst_settings;
}

/**
 * Woo Tax Create
 */

function woogst_create_gst_tax_class()
{
    // Ensure the required POST data is available
    if (!isset($_POST['gst_tax_class']) || !isset($_POST['gst_tax_rate'])) {
        wp_send_json_error(['message' => 'Required data missing.']);
    }

    // Sanitize the input
    $gst_class = sanitize_text_field($_POST['gst_tax_class']);
    $tax_rate = floatval($_POST['gst_tax_rate']);

    // Get existing WooCommerce tax classes
    $woo_tax_classes = WC_Tax::get_tax_classes();

    // Load saved plugin options for the 'gst-slabs' tab
    $settings = woogst_get_options('gst-slabs'); // Fetch the current gst-slabs settings

    // Ensure the gst_tax_class array exists in settings
    if (!isset($settings['gst_tax_class'])) {
        $settings['gst_tax_class'] = []; // Initialize if it doesn't exist
    }

    // Add to the settings GST tax class array if not already present
    if (!in_array($gst_class, $settings['gst_tax_class'])) {
        $settings['gst_tax_class'][] = $gst_class;
    }

    // Create tax class in WooCommerce if it doesn't exist
    if (!in_array($gst_class, $woo_tax_classes)) {
        WC_Tax::create_tax_class($gst_class, sanitize_title($gst_class));
    }

    // Insert tax rates for the given class
    woogst_create_gst_tax_rates($gst_class, $tax_rate);

    // Save the updated GST tax classes in the 'gst-slabs' option
    update_option(WOOGST_OPTION_PREFIX . 'gst-slabs', $settings);

    wp_send_json_success(['message' => 'Tax rates successfully created for ' . $gst_class]);
}



function woogst_create_gst_tax_rates($gst_class, $input_tax_rate)
{

    $gst_tax_rate = array(
        1 => array(
            'tax_rate_country' => 'IN',
            'tax_rate_state' => '',
            'tax_rate' => $input_tax_rate,
            'tax_rate_name' => 'IGST',
            'tax_rate_priority' => 2,
            'tax_rate_compound' => 0,
            'tax_rate_shipping' => 0,
            'tax_rate_order' => 0,
            'tax_rate_class' => $gst_class,
        ),
        2 => array(
            'tax_rate_country' => 'IN',
            'tax_rate_state' => WC()->countries->get_base_state() ?: '',
            'tax_rate' => $input_tax_rate / 2,
            'tax_rate_name' => 'SGST',
            'tax_rate_priority' => 1,
            'tax_rate_compound' => 0,
            'tax_rate_shipping' => 0,
            'tax_rate_order' => 2,
            'tax_rate_class' => $gst_class,
        ),
        3 => array(
            'tax_rate_country' => 'IN',
            'tax_rate_state' => WC()->countries->get_base_state() ?: '',
            'tax_rate' => $input_tax_rate / 2,
            'tax_rate_name' => 'CGST',
            'tax_rate_priority' => 2,
            'tax_rate_compound' => 0,
            'tax_rate_shipping' => 0,
            'tax_rate_order' => 1,
            'tax_rate_class' => $gst_class,
        )
    );

    if (!in_array($gst_class, WC_Tax::get_tax_classes())) {
        set_wp_admin_notice($gst_class . ' tax class not found', 'error');
        wp_redirect(get_admin_url(null, '/admin.php?page=gst-settings'));
        exit;
    }

    foreach ($gst_tax_rate as $rate) {
        WC_Tax::_insert_tax_rate($rate);
    }

    wp_send_json_success(['message' => 'Tax rates successfully created for ' . $gst_class]);

}


function woogst_get_tax_rates()
{
    if (!isset($_POST['gst_tax_class'])) {
        wp_send_json_error(['message' => 'Tax class not specified.']);
    }

    $gst_class = sanitize_text_field($_POST['gst_tax_class']);

    // Fetch the tax rates for the given class
    $tax_rates = WC_Tax::get_rates_for_tax_class($gst_class);

    if (!empty($tax_rates)) {
        $rates = [];
        foreach ($tax_rates as $rate) {
            $rates[] = [
                'tax_rate_name' => $rate->tax_rate_name,
                'tax_rate' => $rate->tax_rate
            ];
        }
        wp_send_json_success(['rates' => $rates]);
    } else {
        wp_send_json_error(['message' => 'No tax rates found for this class.']);
    }
}

function woogst_delete_gst_tax_rates()
{
    if (!isset($_POST['gst_tax_class'])) {
        wp_send_json_error(['message' => 'Tax class not specified.']);
    }

    $gst_class = sanitize_text_field($_POST['gst_tax_class']);

    // Fetch the tax rates for the given class
    $tax_rates = WC_Tax::get_rates_for_tax_class($gst_class);

    if (!empty($tax_rates)) {
        // Remove each tax rate for this class
        foreach ($tax_rates as $rate_id => $rate) {
            WC_Tax::_delete_tax_rate($rate_id);
        }

        // Also delete tax class
        WC_Tax::delete_tax_class_by('name', $gst_class);
        // Return a success message
        wp_send_json_success(['message' => 'Tax rates removed for ' . $gst_class]);
    } else {
        wp_send_json_error(['message' => 'No tax rates found for this class.']);
    }
}

/**
 * Check if a tax class already has tax rates.
 *
 * @param string $tax_class The tax class slug.
 * @return bool True if the tax class has existing rates, false otherwise.
 */
function woogst_tax_class_has_rates($tax_class)
{
    // Get all rates for this tax class
    $rates = WC_Tax::get_rates_for_tax_class($tax_class);

    // Return true if any rates exist, false otherwise
    return !empty($rates);
}