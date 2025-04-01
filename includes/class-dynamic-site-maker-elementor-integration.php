<?php
/**
 * Elementor Integration Class
 *
 * @package Dynamic_Site_Maker
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Class DSMK_Elementor_Integration
 *
 * Handles integration with Elementor for dynamic content.
 */
class DSMK_Elementor_Integration {

    /**
     * Constructor
     */
    public function __construct() {
        // Add Elementor dynamic tags
        add_action( 'elementor/dynamic_tags/register_tags', array( $this, 'register_dynamic_tags' ) );
        
        // Filter Elementor template content on render
        add_filter( 'elementor/frontend/the_content', array( $this, 'process_dynamic_content' ) );
        
        // Add custom Elementor widget category
        add_action( 'elementor/elements/categories_registered', array( $this, 'add_elementor_widget_category' ) );
        
        // Register custom Elementor widgets
        add_action( 'elementor/widgets/widgets_registered', array( $this, 'register_widgets' ) );
    }

    /**
     * Register dynamic tags
     *
     * @param \Elementor\Core\DynamicTags\Manager $dynamic_tags The dynamic tags manager.
     */
    public function register_dynamic_tags( $dynamic_tags ) {
        // Make sure the class exists and Elementor Pro is active
        if ( class_exists( 'ElementorPro\Modules\DynamicTags\Tags\Base\Tag' ) ) {
            // Register dynamic tag group
            $dynamic_tags->register_group(
                'dsmk-tags',
                array(
                    'title' => __( 'Dynamic Site Maker', 'dynamic-site-maker' ),
                )
            );

            // Include dynamic tag classes
            require_once DSMK_PLUGIN_DIR . 'includes/elementor/tags/class-dynamic-site-maker-name-tag.php';
            require_once DSMK_PLUGIN_DIR . 'includes/elementor/tags/class-dynamic-site-maker-logo-tag.php';
            require_once DSMK_PLUGIN_DIR . 'includes/elementor/tags/class-dynamic-site-maker-affiliate-link-tag.php';

            // Register the tags
            $dynamic_tags->register_tag( 'DSMK_Name_Tag' );
            $dynamic_tags->register_tag( 'DSMK_Logo_Tag' );
            $dynamic_tags->register_tag( 'DSMK_Affiliate_Link_Tag' );
        }
    }

    /**
     * Process dynamic content in Elementor template
     *
     * @param string $content The content.
     * @return string Modified content.
     */
    public function process_dynamic_content( $content ) {
        global $post;
        
        if ( ! $post || ! is_singular( 'page' ) ) {
            return $content;
        }

        // Check if this is a Dynamic Site Maker page
        $name = get_post_meta( $post->ID, '_dsmk_name', true );
        if ( empty( $name ) ) {
            return $content;
        }

        // Get meta values
        $logo_id = get_post_meta( $post->ID, '_dsmk_logo_id', true );
        $logo_url = wp_get_attachment_url( $logo_id );
        $affiliate_link = get_post_meta( $post->ID, '_dsmk_affiliate_link', true );

        // Replace placeholders with actual values
        $replacements = array(
            '{{name}}' => esc_html( $name ),
            '{{logo_id}}' => esc_attr( $logo_id ),
            '{{logo_url}}' => esc_url( $logo_url ),
            '{{affiliate_link}}' => esc_url( $affiliate_link ),
        );

        foreach ( $replacements as $placeholder => $value ) {
            $content = str_replace( $placeholder, $value, $content );
        }

        return $content;
    }

    /**
     * Add Elementor widget category
     *
     * @param \Elementor\Elements_Manager $elements_manager Elements manager instance.
     */
    public function add_elementor_widget_category( $elements_manager ) {
        $elements_manager->add_category(
            'dynamic-site-maker',
            array(
                'title' => __( 'Dynamic Site Maker', 'dynamic-site-maker' ),
                'icon'  => 'fa fa-plug',
            )
        );
    }

    /**
     * Register custom Elementor widgets
     */
    public function register_widgets() {
        // Make sure Elementor is active
        if ( ! did_action( 'elementor/loaded' ) ) {
            return;
        }

        // Include widget files
        require_once DSMK_PLUGIN_DIR . 'includes/elementor/widgets/class-dynamic-site-maker-info-widget.php';

        // Register the widgets
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new DSMK_Info_Widget() );
    }

    /**
     * Create necessary directory structure for Elementor files
     */
    public static function create_elementor_directories() {
        $directories = array(
            DSMK_PLUGIN_DIR . 'includes/elementor',
            DSMK_PLUGIN_DIR . 'includes/elementor/tags',
            DSMK_PLUGIN_DIR . 'includes/elementor/widgets',
        );

        foreach ( $directories as $directory ) {
            if ( ! file_exists( $directory ) ) {
                wp_mkdir_p( $directory );
            }
        }
    }
}

// Create Elementor directories on plugin load
DSMK_Elementor_Integration::create_elementor_directories();
