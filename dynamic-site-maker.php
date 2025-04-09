<?php
/**
 * Plugin Name: Dynamic Site Maker
 * Description: Dynamically generates landing pages using submitted form data and a pre-designed Elementor template.
 * Version: 1.0.0
 * Author: Push Profit
 * Text Domain: dynamic-site-maker
 * Domain Path: /languages
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package Dynamic_Site_Maker
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Currently plugin version.
 */
define( 'DSMK_VERSION', '1.0.0' );

/**
 * Plugin directory path.
 */
define( 'DSMK_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Plugin directory URL.
 */
define( 'DSMK_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin prefix for functions, classes, and database keys.
 */
define( 'DSMK_PREFIX', 'dsmk_' );

/**
 * The code that runs during plugin activation.
 */
function dsmk_activate() {
    // Check if Elementor is installed and activated
    if ( ! did_action( 'elementor/loaded' ) ) {
        add_action( 'admin_notices', 'dsmk_elementor_notice' );
    }
    
    // Create necessary directories if they don't exist
    if ( ! file_exists( DSMK_PLUGIN_DIR . 'uploads' ) ) {
        wp_mkdir_p( DSMK_PLUGIN_DIR . 'uploads' );
    }
}

/**
 * The code that runs during plugin deactivation.
 */
function dsmk_deactivate() {
    // Nothing to do here for now
}

/**
 * Notice for Elementor dependency.
 */
function dsmk_elementor_notice() {
    ?>
    <div class="notice notice-warning is-dismissible">
        <p><?php esc_html_e( 'Dynamic Site Maker requires Elementor to be installed and activated.', 'dynamic-site-maker' ); ?></p>
    </div>
    <?php
}

register_activation_hook( __FILE__, 'dsmk_activate' );
register_deactivation_hook( __FILE__, 'dsmk_deactivate' );

/**
 * Load required files.
 */
require_once DSMK_PLUGIN_DIR . 'includes/class-dynamic-site-maker-form-handler.php';
require_once DSMK_PLUGIN_DIR . 'includes/class-dynamic-site-maker-landing-page.php';
require_once DSMK_PLUGIN_DIR . 'includes/class-dynamic-site-maker-admin-settings.php';
require_once DSMK_PLUGIN_DIR . 'includes/class-dynamic-site-maker-elementor-integration.php';
require_once DSMK_PLUGIN_DIR . 'includes/class-dynamic-site-maker-spam-protection.php';

/**
 * Initialize the plugin.
 */
function dsmk_init() {
    // Initialize classes
    new DSMK_Form_Handler();
    new DSMK_Landing_Page();
    new DSMK_Admin_Settings();
    new DSMK_Elementor_Integration();
    new DSMK_Spam_Protection();
    
    // Load text domain
    load_plugin_textdomain( 'dynamic-site-maker', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    
    // Register shortcodes
    add_shortcode( 'dynamic_site_maker', 'dsmk_shortcode' );
    add_shortcode( 'dynamic_site_maker_edit', 'dsmk_edit_shortcode' );
    add_shortcode( 'dsmk_form', 'dsmk_form_shortcode' );
}
add_action( 'plugins_loaded', 'dsmk_init' );

/**
 * Shortcode callback for the main form
 *
 * @return string Shortcode output
 */
function dsmk_shortcode() {
    // Start output buffering
    ob_start();
    
    // Include form template
    include_once DSMK_PLUGIN_DIR . 'templates/form-template.php';
    
    // Get the buffered content
    $output = ob_get_clean();
    
    return $output;
}

/**
 * Shortcode callback for the edit form
 *
 * @return string Shortcode output
 */
function dsmk_edit_shortcode() {
    // Start output buffering
    ob_start();
    
    // Include edit form template
    include_once DSMK_PLUGIN_DIR . 'templates/edit-form-template.php';
    
    // Get the buffered content
    $output = ob_get_clean();
    
    return $output;
}

/**
 * Register shortcode for displaying the form.
 */
function dsmk_form_shortcode() {
    ob_start();
    include_once DSMK_PLUGIN_DIR . 'templates/form-template.php';
    return ob_get_clean();
}

/**
 * Enqueue scripts and styles.
 */
function dsmk_enqueue_scripts() {
    // Enqueue CSS
    wp_enqueue_style( 'dsmk-style', DSMK_PLUGIN_URL . 'assets/css/style.css', array(), DSMK_VERSION );
    
    // Enqueue JS
    wp_enqueue_script( 'dsmk-script', DSMK_PLUGIN_URL . 'assets/js/dynamic-site-maker-form-handler.js', array( 'jquery' ), DSMK_VERSION, true );
    
    // Add our Elementor widget styles
    wp_enqueue_style( 'dsmk-elementor-widgets', DSMK_PLUGIN_URL . 'assets/css/elementor-widgets.css', array(), DSMK_VERSION );
    
    // Localize script for both regular and edit forms
    wp_localize_script( 'dsmk-script', 'dsmk_ajax', array(
        'ajax_url'      => admin_url( 'admin-ajax.php' ),
        'nonce'         => wp_create_nonce( 'dsmk_form_nonce' ),
    ) );
    
    // Add form-specific variables
    wp_localize_script( 'dsmk-script', 'dsmk_form', array(
        'ajax_url'       => admin_url( 'admin-ajax.php' ),
        'nonce'          => wp_create_nonce( 'dsmk_form_nonce' ),
        'redirect_delay' => absint( get_option( 'dsmk_redirect_delay', 2 ) ),
    ) );
}
add_action( 'wp_enqueue_scripts', 'dsmk_enqueue_scripts' );
