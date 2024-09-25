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
require_once plugin_dir_path(__FILE__) . '/inc/class-report-table.php';

class Woogst_Admin
{
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        // add_action('admin_menu', [$this, 'add_menu_items']);
        add_action( 'init', [$this,'order_report_post_type'], 0 );

        add_filter('manage_edit-gst-reports_columns', [$this, 'woogst_gst_reports_columns']);
        add_action('manage_gst-reports_posts_custom_column', [$this, 'woogst_gst_reports_custom_column'], 10, 2);

        
        add_filter('post_row_actions', [$this, 'woogst_gst_reports_row_actions'], 10, 2);
        add_action('admin_post_send_gst_report_email', [$this,'woogst_send_gst_report_email']);
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


    // Register Custom Post Type
function order_report_post_type() {

	$labels = array(
		'name'                  => _x( 'GST Reports', 'Post Type General Name', 'woogst' ),
		'singular_name'         => _x( 'GST Report', 'Post Type Singular Name', 'woogst' ),
		'menu_name'             => __( 'WooGST', 'woogst' ),
		'name_admin_bar'        => __( 'Post Type', 'woogst' ),
		'archives'              => __( 'Item Archives', 'woogst' ),
		'attributes'            => __( 'Item Attributes', 'woogst' ),
		'parent_item_colon'     => __( 'Parent Item:', 'woogst' ),
		'all_items'             => __( 'All Items', 'woogst' ),
		'add_new_item'          => __( 'Add New Item', 'woogst' ),
		'add_new'               => __( 'Add New', 'woogst' ),
		'new_item'              => __( 'New Item', 'woogst' ),
		'edit_item'             => __( 'Edit Item', 'woogst' ),
		'update_item'           => __( 'Update Item', 'woogst' ),
		'view_item'             => __( 'View Item', 'woogst' ),
		'view_items'            => __( 'View Items', 'woogst' ),
		'search_items'          => __( 'Search Item', 'woogst' ),
		'not_found'             => __( 'Not found', 'woogst' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'woogst' ),
		'featured_image'        => __( 'Featured Image', 'woogst' ),
		'set_featured_image'    => __( 'Set featured image', 'woogst' ),
		'remove_featured_image' => __( 'Remove featured image', 'woogst' ),
		'use_featured_image'    => __( 'Use as featured image', 'woogst' ),
		'insert_into_item'      => __( 'Insert into item', 'woogst' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'woogst' ),
		'items_list'            => __( 'Items list', 'woogst' ),
		'items_list_navigation' => __( 'Items list navigation', 'woogst' ),
		'filter_items_list'     => __( 'Filter items list', 'woogst' ),
	);
	$capabilities = array(
		'edit_post'             => 'edit_post',
		'read_post'             => 'read_post',
		'delete_post'           => 'delete_post',
		'edit_posts'            => 'edit_posts',
		'edit_others_posts'     => 'edit_others_posts',
		'publish_posts'         => 'publish_posts',
		'read_private_posts'    => 'read_private_posts',
	);
	$args = array(
        'label'                 => __( 'GST Report', 'woogst' ),
        'description'           => __( 'Registering post type for gst monthly reports', 'woogst' ),
        'labels'                => $labels,
        'supports'              => array( 'title', 'comments', 'trackbacks', 'revisions', 'page-attributes' ),
        'taxonomies'            => array( 'sale_type' ),
        'hierarchical'          => false,
        'public'                => true,  // Ensure it's public to enable editing
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-money',
        'capability_type'       => 'post',  // Standard post type capabilities
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => true,
        'publicly_queryable'    => false,  // You can keep this false if it's an admin-only feature
        'show_in_rest'          => true,
        'rest_base'             => 'gst_reports',
        'rest_controller_class' => 'WOO_GST_Order_Reports',
    );
	register_post_type( 'gst-reports', $args );

}



// Add custom columns to the GST Reports post type
function woogst_gst_reports_columns($columns) {
    // Remove some default columns if not needed
    unset($columns['date']);
    
    // Add custom columns
    $columns['email_status'] = __('Email Status', 'woogst');
    $columns['report_date'] = __('Report Date', 'woogst');
    
    return $columns;
}


// Populate custom columns with data
function woogst_gst_reports_custom_column($column, $post_id) {
    switch ($column) {
        case 'email_status':
            // Fetch the email_status from post meta
            $email_status = get_post_meta($post_id, 'email_status', true);
            echo esc_html($email_status ? $email_status : __('Not set', 'woogst'));
            break;

        case 'report_date':
            // Assuming you have a custom field 'report_date'
            $report_date = get_post_meta($post_id, 'report_date', true);
            echo esc_html($report_date ? $report_date : __('No Date', 'woogst'));
            break;
    }
}



// Add custom actions to the post row actions
function woogst_gst_reports_row_actions($actions, $post) {
    if ($post->post_type == 'gst-reports') {
        // Add custom action links
        $actions['send_email'] = '<a href="' . admin_url('admin-post.php?action=send_gst_report_email&post_id=' . $post->ID) . '">' . __('Send Email', 'woogst') . '</a>';
    }
    return $actions;
}


      // Admin notice
      function set_wp_admin_notice($message, $type)
      {
            wp_admin_notice($message, ['type' => $type, 'dismissible' => true]);
      }

// Handle the "Send Email" action
function woogst_send_gst_report_email() {
    // Verify post ID is set
    if (isset($_GET['post_id'])) {
        $post_id = intval($_GET['post_id']);
        
        // Get the email address and email content (adjust to your needs)
        $email_status = get_post_meta($post_id, 'email_status', true);
        $report_title = get_the_title($post_id);
        
        // For example purposes, let's assume we're sending to a hardcoded email
        $to = 'owlthtech@gmail.com';
        $subject = 'GST Report: ' . $report_title;
        $message = 'Here is the GST report: ' . $report_title . '. Status: ' . $email_status;
        
        // Send the email
        $email = wp_mail($to, $subject, $message);

        if($email) {
            $this->set_wp_admin_notice(__('Email sent successfully.'), 'success');
        } else {
            $this->set_wp_admin_notice(__('Unable to send email!'), 'error');
        }
        
        // Redirect back to the admin page
        wp_redirect(admin_url('edit.php?post_type=gst-reports'));
        exit;
    }
}





/***
 * 
 * 
 * 
 */


    public function add_menu_items()
    {
        global $gst_report_menu;

        // Add menu item
        $gst_report_menu = add_menu_page(
            __('WooGST', 'woogst'),
            __('WooGST', 'woogst'),
            'manage_options',
            'woo-gst-reports',
            array($this, 'render_woo_gst_report_list_table_page'),
            'dashicons-money'
        );

        // Add the screen option to menu item ($gst_report_menu) page
        add_action("load-$gst_report_menu", array($this, 'add_screen_options'));

    }


    /**
     * Add screen options to the plugin page.
     */
    public function add_screen_options()
    {
        global $list_table;

        $screen = get_current_screen();
        if (!is_object($screen) || $screen->id !== 'toplevel_page_woo-gst-reports') {
            return;
        }

        $args = [
            'label' => __('Reports per page', 'woogst'),
            'default' => 5,
            'option' => 'reports_per_page'
        ];
        add_screen_option('per_page', $args);

        $list_table = new Woo_Gst_Report_Table();

    }

    /**
     * Render the list page.
     */
    public function render_woo_gst_report_list_table_page()
    {
        global $list_table;
        $list_table = new Woo_Gst_Report_Table();
        echo '<div class="wrap">';
        echo '<h1 class="wp-heading-inline">' . __('Monthly GST Reports', 'woogst') . '</h1>';
        echo "<form id='woogst-report-form' method='post'>";
        // wp_nonce_field('woo_gst_reports', '_wpnonce', false);
        $list_table->prepare_items();
        $list_table->search_box('search', 'reports');
        $list_table->display();
        echo '</form>';
        echo '</div>';

    }





    /**
     * NOT USING ANYWHERE NOW
     * Clean up URL parameters after actions.
     */
    public function clean_up_admin_url()
    {
        // Check if we are on the specific admin page
        if (is_admin() && isset($_GET['page']) && $_GET['page'] === 'woo-gst-reports') {
            // Prepare the base URL without unwanted parameters
            $redirect_url = $this->prepare_redirect_url(admin_url('admin.php?page=woo-gst-reports'));

            // Check if the current URL contains any of the unwanted parameters
            if (isset($_GET['_wp_http_referer']) || isset($_GET['submission']) || isset($_GET['action2'])) {
                // Only redirect if unwanted parameters are found
                wp_redirect($redirect_url);
                // Stop further execution to prevent loops
                return;
            }
        }
    }

    /**
     * Prepare redirect URL with existing query parameters.
     */
    public function prepare_redirect_url($base_url)
    {
        // Remove specific query parameters if they exist
        $base_url = remove_query_arg(['_wp_http_referer', 'action2'], $base_url);

        if (!isset($_GET['page'])) {
            $base_url = add_query_arg('page', sanitize_text_field($_GET['fbf-submissions']), $base_url);
        }

        // Conditionally add existing parameters like 'orderby', 'order', 's' if they exist
        if (isset($_GET['orderby'])) {
            $base_url = add_query_arg('orderby', sanitize_text_field($_GET['orderby']), $base_url);
        }

        if (isset($_GET['order'])) {
            $base_url = add_query_arg('order', sanitize_text_field($_GET['order']), $base_url);
        }

        if (isset($_GET['s']) && !empty(trim($_GET['s']))) {
            $base_url = add_query_arg('s', sanitize_text_field($_GET['s']), $base_url);
        }

        return $base_url;
    }
}

