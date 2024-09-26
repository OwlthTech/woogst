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
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        // Registers 'gst-reports' post type
        add_action('init', [$this, 'register_gst_report_post_type']);

        // Sets notice using transient options
        add_action('admin_notices', 'woo_gst_admin_notice_message');

        // Checks woocommerce installed & activated and sets admin notice
        add_action('admin_notices', 'set_wp_admin_notice_active_woo');

        $woo_gst = woogst_gst();
        $woo_gst->init();

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
        $screen = get_current_screen();
        $screen_id = $screen ? $screen->id : '';

        if ($screen_id === wc_get_page_screen_id('shop-order')) {
            wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/woogst-admin.css', array(), $this->version, 'all');
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
            'all_items' => __('All Items', 'woogst'),
            'add_new_item' => __('Add New Item', 'woogst'),
            'add_new' => __('Add New', 'woogst'),
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
        $capabilities = array(
            'edit_post' => 'edit_post',
            'read_post' => 'read_post',
            'delete_post' => 'delete_post',
            'edit_posts' => 'edit_posts',
            'edit_others_posts' => 'edit_others_posts',
            'publish_posts' => 'publish_posts',
            'read_private_posts' => 'read_private_posts',
        );
        $args = array(
            'label' => __('GST Report', 'woogst'),
            'description' => __('Registering post type for gst monthly reports', 'woogst'),
            'labels' => $labels,
            'supports' => array('title', 'comments', 'trackbacks', 'revisions', 'page-attributes'),
            'taxonomies' => array('sale_type'),
            'hierarchical' => false,
            'public' => false,  // Ensure it's public to enable editing
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 5,
            'menu_icon' => 'dashicons-money',
            'capability_type' => 'post',  // Standard post type capabilities
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => true,
            'can_export' => true,
            'has_archive' => true,
            'exclude_from_search' => true,
            'publicly_queryable' => false,  // You can keep this false if it's an admin-only feature
            'show_in_rest' => true,
            'rest_base' => 'gst_reports',
            'rest_controller_class' => 'WOO_GST_Order_Reports',
        );
        register_post_type('gst-reports', $args);

    }


}






/**
 * Admin notice and transients for notice
 * 
 */

function set_wp_admin_notice($message, $type){
    set_transient('woogst_admin_notice', ['message' => $message, 'type' => $type], 30);
}

// Checks transient 'woogst_admin_notice' and adds admin notice and deletes transient
function woo_gst_admin_notice_message(){
    // Retrieve the transient
    $notice = get_transient('woogst_admin_notice');

    if ($notice) {
        $class = $notice['type'] === 'success' ? 'notice-success' : 'notice-error';
        ?>
        <div class="notice <?php echo $class; ?> is-dismissible">
            <p><?php echo esc_html($notice['message']); ?></p>
        </div>
        <?php
        // Delete the transient so it doesn't persist
        delete_transient('woogst_admin_notice');
    }
}

// Checks WooCommerce in installed & active plugins and sets admin notice
function set_wp_admin_notice_active_woo(){
    if (!Woogst_Validator::is_woocommerce_installed() && !Woogst_Validator::is_woocommerce_active()) {
        set_wp_admin_notice("Please install & activate woocommerce plugin", 'error');
    }
}


