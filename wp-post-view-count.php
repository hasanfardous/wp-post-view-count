<?php

/**
 * Plugin Name:       Post View Count
 * Plugin URI:        https://github.com/hasanfardous/wp-post-view-count
 * Description:       The Post View Count plugin will record the number of views a post has received. It will display the view count for each post and shortcode with the copy facility by clicking in the admin post list table using custom columns. The shortcode that accepts a post ID and returns the view count to the bottom of post for the post with it's ID.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.2
 * Author:            Hasan Fardous
 * Author URI:        https://github.com/hasanfardous/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-post-view-count
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class WP_Post_View_Count {
    private $plugin_dir;
    private $plugin_url;
    
    function __construct() {
        $this->plugin_dir = plugin_dir_path( __FILE__ );
        $this->plugin_url = plugin_dir_url( __FILE__ );
        
        // Initiate the plugin
        add_action('init', [$this, 'init']);
    }

    // Initiate the necessary functions
    function init() {
        // Remove issues with prefetching adding extra views
        remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);

        // Add Post View Column
        add_filter('manage_posts_columns', [$this, 'post_column']);

        // Make the Post View Column Sortable
        add_filter('manage_edit-post_sortable_columns', [$this, 'post_sortable_column']);

        // Display Post Views to the Column's content
        add_action('manage_posts_custom_column', [$this, 'display_posts_views'], 10, 2);

        // Modify query to sort by views count
        add_action('pre_get_posts', [$this, 'custom_views_column_orderby']);

        // Enqueue Scripts
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);

        // Admin Enqueue Scripts
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);

        // Add the Shortcode
        add_shortcode('post-view-count', [$this, 'shortcode_post_view_count']);
    }

    // Get Plugin TextDomain
    function get_plugin_text_domain() {
        if( ! function_exists('get_plugin_data') ){
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }
        $plugin_data = get_plugin_data( __FILE__ );
        return $plugin_data['TextDomain'];
    }

    // Add Post View Column
    function post_column( $columns ) {
        $columns['post_views']              = __( 'Post Views', $this->get_plugin_text_domain() );
        $columns['post_views_shortcode']    = __( 'Shortcode', $this->get_plugin_text_domain() );
        return $columns;
    }

    // Make the Post View Column sortable
    function post_sortable_column( $columns ) {
        $columns['post_views'] = 'post_views';
        return $columns;
    }

    // Display Post Views to the Column's content
    function display_posts_views( $column, $post_id ) {
        if ($column == 'post_views'){
            echo get_post_meta( $post_id, '_wp_post_view_count', true ) ?: 0;
        }
        if ($column == 'post_views_shortcode'){
            $shortcode_el = '<span class="wpvc-shortcode">[post-view-count id="' . esc_html($post_id) . '"]</span>';
            $shortcode_el .= '<span class="wpvc-copy-button">'. __( 'Copy', $this->get_plugin_text_domain() ).'</span>';
            echo $shortcode_el;
        }
    }

    // Modify query to sort by views count
    function custom_views_column_orderby($query) {
        $orderby = $query->get('orderby');
        
        if ('post_views' === $orderby) {
            $query->set('meta_key', '_wp_post_view_count');
            $query->set('orderby', 'meta_value_num');
        }
    }

    //Enqueue Scripts Callback
    function enqueue_scripts() {
        // Enqueue styles
        wp_enqueue_style(
            'wpvc-styles', 
            $this->plugin_url . 'assets/css/styles.css'
        );
    }

    // Admin Enqueue Scripts Callback
    function admin_enqueue_scripts() {
        // Enqueue styles
        wp_enqueue_style(
            'wpvc-admin-styles', 
            $this->plugin_url . 'assets/css/admin-styles.css'
        );
        // Enqueue script
        wp_enqueue_script(
            'wpvc-admin-script', 
            $this->plugin_url . 'assets/js/admin-script.js'
        );
    }

	// Load Plugin Textdomain
	function load_textdomain() {
		load_plugin_textdomain( 
			$this->get_plugin_text_domain(), 
			false, 
			$this->plugin_dir . "/languages" 
		);
	}

    // Shortcode Callback   
    function shortcode_post_view_count( $atts ) {
        $atts = shortcode_atts( [
            'id' => get_the_ID(),
        ], $atts, 'post-view-count' );
        ob_start();
        $post_id = $atts['id'];
        $post_views = absint( get_post_meta( $post_id, '_wp_post_view_count', true ) ?: 0 );
        $post_views++;

        // Updating Post Meta
        update_post_meta( $post_id, '_wp_post_view_count', $post_views );
        ?>
        <div class="wp-post-view-count">
            <p><?php esc_html_e( 'Post Views', $this->get_plugin_text_domain() ); ?></p>
            <p><?php esc_html_e( 'Total Views', $this->get_plugin_text_domain() ); ?></p>
            <p><?php echo $post_views; ?></p>
        </div>
        <?php
        $post_view_count = ob_get_clean();
        return $post_view_count;
    }
}

// Initialize the Class
new WP_Post_View_Count();

?>