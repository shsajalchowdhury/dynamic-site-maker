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
        
        // Process dynamic content in Elementor data before rendering
        add_filter( 'elementor/frontend/builder_content_data', array( $this, 'process_template_data' ), 10, 2 );
        
        // Add custom CSS for logo images
        add_action( 'wp_head', array( $this, 'add_custom_css' ) );
    }
    
    /**
     * Add custom CSS for logo images
     */
    public function add_custom_css() {
        // Only add CSS on landing pages
        if ( ! is_singular() || ! get_post_meta( get_the_ID(), '_is_dsmk_landing_page', true ) ) {
            return;
        }
        
        // No custom CSS needed as it was causing display issues
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
     * Process Elementor template data before rendering
     * This is where we modify the template to use the dynamic content
     *
     * @param array $data    The template data.
     * @param int   $post_id The post ID.
     * @return array Modified template data.
     */
    public function process_template_data( $data, $post_id ) {
        // Check if this is a Dynamic Site Maker page
        $is_dsmk_page = get_post_meta( $post_id, '_is_dsmk_landing_page', true );
        if ( ! $is_dsmk_page ) {
            return $data;
        }
        
        // Get meta values
        $logo_id = get_post_meta( $post_id, '_dsmk_logo_id', true );
        $logo_url = wp_get_attachment_url( $logo_id );
        $name = get_post_meta( $post_id, '_dsmk_name', true );
        $affiliate_link = get_post_meta( $post_id, '_dsmk_affiliate_link', true );
        
        // Process the template data recursively
        $data = $this->process_elements_recursively( $data, $logo_id, $logo_url, $name, $affiliate_link );
        
        return $data;
    }
    
    /**
     * Process elements recursively to replace dynamic content
     *
     * @param array  $elements       The elements to process.
     * @param int    $logo_id        The logo ID.
     * @param string $logo_url       The logo URL.
     * @param string $name           The user's name.
     * @param string $affiliate_link The affiliate link.
     * @return array Modified elements.
     */
    private function process_elements_recursively( $elements, $logo_id, $logo_url, $name, $affiliate_link ) {
        if ( ! is_array( $elements ) ) {
            return $elements;
        }
        
        foreach ( $elements as &$element ) {
            // Process all settings to replace placeholders
            if (isset($element['settings']) && is_array($element['settings'])) {
                // Convert settings to JSON to easily find and replace placeholders
                $settings_json = wp_json_encode($element['settings']);
                
                // Replace placeholders
                $settings_json = str_replace('{{logo_url}}', $logo_url, $settings_json);
                $settings_json = str_replace('{{logo_id}}', $logo_id, $settings_json);
                $settings_json = str_replace('{{affiliate_link}}', $affiliate_link, $settings_json);
                
                // Convert back to array
                $element['settings'] = json_decode($settings_json, true);
            }
            
            // Process image widgets for logo (specific handling for image widgets)
            if (isset($element['widgetType']) && $element['widgetType'] === 'image') {
                // CRITICAL: Skip logo processing completely if no custom logo is uploaded
                // This ensures the default template logo is preserved
                if (empty($logo_id) || empty($logo_url)) {
                    // Skip this image widget entirely when no custom logo exists
                    continue;
                }
                
                // Check if this is a placeholder image with class or ID containing 'logo'
                $has_logo_class = false;
                
                if (isset($element['settings']['_css_classes']) && 
                    (strpos($element['settings']['_css_classes'], 'dsmk-logo') !== false)) {
                    $has_logo_class = true;
                }
                
                if (isset($element['settings']['_element_id']) && 
                    (strpos($element['settings']['_element_id'], 'dsmk-logo') !== false)) {
                    $has_logo_class = true;
                }
                
                // Only replace images with the specific CSS class or ID
                if ($has_logo_class) {
                    // Simply update the ID and URL without changing other settings
                    $element['settings']['image']['id'] = $logo_id;
                    $element['settings']['image']['url'] = $logo_url;
                    
                    // Set image size to full to ensure proper display
                    if (isset($element['settings']['image_size'])) {
                        $element['settings']['image_size'] = 'full';
                    }
                }
            }
            
            // Process button widgets for affiliate link
            if (isset($element['widgetType']) && $element['widgetType'] === 'button') {
                
                // Check if this is a placeholder button with class or ID containing 'dsmk-affiliate'
                $has_affiliate_class = false;
                
                if (isset($element['settings']['_css_classes']) && 
                    (strpos($element['settings']['_css_classes'], 'dsmk-affiliate') !== false)) {
                    $has_affiliate_class = true;
                }
                
                if (isset($element['settings']['_element_id']) && 
                    (strpos($element['settings']['_element_id'], 'dsmk-affiliate') !== false)) {
                    $has_affiliate_class = true;
                }
                
                // Check if the button URL contains the placeholder
                $has_placeholder = false;
                if (isset($element['settings']['link']['url']) && 
                    strpos($element['settings']['link']['url'], '{{affiliate_link}}') !== false) {
                    $has_placeholder = true;
                }
                
                // Only replace buttons with the specific CSS class, ID, or placeholder
                if ($has_affiliate_class || $has_placeholder) {
                    // Make sure the link structure exists
                    if (!isset($element['settings']['link'])) {
                        $element['settings']['link'] = array();
                    }
                    
                    // Set the affiliate link URL
                    $element['settings']['link']['url'] = $affiliate_link;
                    
                    // Make sure it opens in a new tab
                    $element['settings']['link']['is_external'] = true;
                    
                    // Make sure it has nofollow for SEO
                    $element['settings']['link']['nofollow'] = true;
                }
            }
            
            // Process nested elements recursively
            if (!empty($element['elements'])) {
                $element['elements'] = $this->process_elements_recursively(
                    $element['elements'],
                    $logo_id,
                    $logo_url,
                    $name,
                    $affiliate_link
                );
            }
        }
        
        return $elements;
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
