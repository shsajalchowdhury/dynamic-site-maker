<?php
/**
 * Landing Page Class
 *
 * @package Dynamic_Site_Maker
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Class DSMK_Landing_Page
 *
 * Handles the creation of dynamic landing pages.
 */
class DSMK_Landing_Page {

    /**
     * Constructor
     */
    public function __construct() {
        // Nothing needed in constructor for now
    }

    /**
     * Create a new landing page
     *
     * @param array $data Form data.
     * @return int|WP_Error The page ID on success, WP_Error on failure.
     */
    public function create_page( $data ) {
        // Generate a unique page slug based on the name
        $slug = $this->generate_unique_slug( $data['name'] );

        // Create the page
        $page_args = array(
            'post_title'    => esc_html( $data['name'] . '\'s Landing Page' ),
            'post_name'     => $slug,
            'post_content'  => '', // Content will be handled by Elementor template
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_author'   => get_current_user_id() ?: 1, // Default to admin if no user
        );

        // Insert the page
        $page_id = wp_insert_post( $page_args, true );

        if ( is_wp_error( $page_id ) ) {
            return $page_id;
        }

        // Store the form data as meta fields
        update_post_meta( $page_id, '_dsmk_name', sanitize_text_field( $data['name'] ) );
        update_post_meta( $page_id, '_dsmk_email', sanitize_email( $data['email'] ) );
        update_post_meta( $page_id, '_dsmk_logo_id', absint( $data['logo_id'] ) );
        
        // Extract username from the affiliate link if possible
        $username = '';
        if (isset($data['username'])) {
            $username = sanitize_text_field($data['username']);
            update_post_meta($page_id, '_dsmk_username', $username);
        }
        
        // Ensure the affiliate link includes the username
        $affiliate_link = $data['affiliate_link'];
        if (!empty($username) && strpos($affiliate_link, $username) === false) {
            // If username is not already in the link, append it
            if (substr($affiliate_link, -1) !== '=') {
                $affiliate_link .= '=';
            }
            $affiliate_link .= $username;
        }
        
        update_post_meta($page_id, '_dsmk_affiliate_link', esc_url_raw($affiliate_link));
        error_log('Stored affiliate link in page meta: ' . $affiliate_link);

        // Apply the Elementor template to the page
        $this->apply_elementor_template( $page_id );

        return $page_id;
    }

    /**
     * Generate a unique slug for the page
     *
     * @param string $name The user's name.
     * @return string Unique slug.
     */
    private function generate_unique_slug( $name ) {
        $base_slug = sanitize_title( $name );
        $slug = $base_slug;
        $counter = 1;

        // Check if the slug exists and increment counter until we find a unique slug
        while ( get_page_by_path( $slug, OBJECT, 'page' ) ) {
            $slug = $base_slug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Apply the Elementor template to the page
     *
     * @param int $page_id The page ID.
     * @return bool True on success, false on failure.
     */
    private function apply_elementor_template( $page_id ) {
        // Check if Elementor is active
        if ( ! did_action( 'elementor/loaded' ) ) {
            return false;
        }
        
        // Check if a logo was uploaded and get its details
        $logo_id = get_post_meta($page_id, '_dsmk_logo_id', true);
        $has_custom_logo = !empty($logo_id) && $logo_id > 0;
        
        // If we have a custom logo, ensure it exists and is valid
        if ($has_custom_logo) {
            $attachment = get_post($logo_id);
            if (!$attachment) {
                // Logo attachment doesn't exist, reset to default
                $has_custom_logo = false;
                $logo_id = 0;
                update_post_meta($page_id, '_dsmk_logo_id', 0);
                error_log('Warning: Logo attachment ID ' . $logo_id . ' does not exist, reverting to default logo');
            }
        }
        
        // Store whether we're using a custom logo or not
        update_post_meta($page_id, '_dsmk_has_custom_logo', $has_custom_logo);
        
        // Log the logo status
        error_log('Page ' . $page_id . ' has custom logo: ' . ($has_custom_logo ? 'yes' : 'no'));

        // Set the page to use Elementor
        update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );
        
        // Mark this as a Dynamic Site Maker landing page
        update_post_meta( $page_id, '_is_dsmk_landing_page', true );
        
        // Set page to use canvas template (no header/footer)
        update_post_meta( $page_id, '_wp_page_template', 'elementor_canvas' );
        
        // Get the template ID from the plugin settings
        $template_id = get_option( 'dsmk_elementor_template_id', 0 );
        
        if (empty($template_id)) {
            // If no template is set, use a default template
            $template_data = $this->get_default_template_data();
            
            if ($has_custom_logo) {
                // Process the template data with logo replacement
                $template_data = $this->process_template_data($template_data, $page_id);
            } else {
                // Only update affiliate links and button text
                $name = get_post_meta($page_id, '_dsmk_name', true);
                $affiliate_link = get_post_meta($page_id, '_dsmk_affiliate_link', true);
                $price = $this->extract_price_from_link($affiliate_link);
                
                // Only update text and links, never touch images
                $template_data = $this->update_text_and_links($template_data, $name, $affiliate_link, $price);
            }
            
            update_post_meta($page_id, '_elementor_data', wp_slash(json_encode($template_data)));
        } else {
            // Get the template content from Elementor's library
            $template_data = $this->get_elementor_template_data($template_id);
            
            if ($template_data) {
                if ($has_custom_logo) {
                    // Process the template with logo replacement
                    $template_data = $this->process_template_data($template_data, $page_id);
                } else {
                    // CRITICAL: When no logo is uploaded, only update text and links
                    // This ensures the default logo is preserved exactly as is
                    $name = get_post_meta($page_id, '_dsmk_name', true);
                    $affiliate_link = get_post_meta($page_id, '_dsmk_affiliate_link', true);
                    $price = $this->extract_price_from_link($affiliate_link);
                    
                    // Only update text and links, never touch images
                    $template_data = $this->update_text_and_links($template_data, $name, $affiliate_link, $price);
                }
                
                // Apply the processed template data to the page
                update_post_meta($page_id, '_elementor_data', wp_slash(json_encode($template_data)));
                
                // Copy all template meta data to ensure styles are preserved
                $template_meta = get_post_meta($template_id);
                if (!empty($template_meta)) {
                    foreach ($template_meta as $meta_key => $meta_value) {
                        // Skip keys we've already set or that shouldn't be copied
                        if (in_array($meta_key, array('_elementor_data', '_wp_page_template', 'post_content'))) {
                            continue;
                        }
                        
                        // Copy meta values that are related to Elementor
                        if (strpos($meta_key, '_elementor_') === 0 || strpos($meta_key, 'elementor_') === 0) {
                            update_post_meta($page_id, $meta_key, maybe_unserialize($meta_value[0]));
                        }
                    }
                }
            } else {
                // Fallback to default if template retrieval fails
                $template_data = $this->get_default_template_data();
                
                if ($has_custom_logo) {
                    // Process the template with logo replacement
                    $template_data = $this->process_template_data($template_data, $page_id);
                } else {
                    // Only update affiliate links and button text
                    $name = get_post_meta($page_id, '_dsmk_name', true);
                    $affiliate_link = get_post_meta($page_id, '_dsmk_affiliate_link', true);
                    $price = $this->extract_price_from_link($affiliate_link);
                    
                    // Only update text and links, never touch images
                    $template_data = $this->update_text_and_links($template_data, $name, $affiliate_link, $price);
                }
                
                update_post_meta($page_id, '_elementor_data', wp_slash(json_encode($template_data)));
            }
        }

        // Mark the page as having active template data
        update_post_meta( $page_id, '_elementor_template_type', 'wp-page' );
        update_post_meta( $page_id, '_elementor_version', ELEMENTOR_VERSION );
        update_post_meta( $page_id, '_elementor_css', '' ); // Force regeneration of CSS
        
        // Clear any existing content
        wp_update_post([
            'ID' => $page_id,
            'post_content' => '' // Empty the content
        ]);
        
        // Regenerate Elementor CSS files
        if ( class_exists( '\Elementor\Plugin' ) ) {
            \Elementor\Plugin::$instance->files_manager->clear_cache();
        }

        return true;
    }
    
    /**
     * Get Elementor template data by template ID
     *
     * @param int $template_id The Elementor template ID.
     * @return array|false Template data or false on failure.
     */
    private function get_elementor_template_data( $template_id ) {
        if ( ! class_exists( '\Elementor\Plugin' ) ) {
            return false;
        }
        
        $document = \Elementor\Plugin::$instance->documents->get( $template_id );
        
        if ( ! $document ) {
            return false;
        }
        
        $content = $document->get_elements_data();
        
        if ( empty( $content ) ) {
            return false;
        }
        
        return $content;
    }
    
    /**
     * Process template data to handle dynamic content
     * 
     * @param array $template_data The template data to process
     * @param int $page_id The page ID
     * @return array The processed template data
     */
    private function process_template_data($template_data, $page_id) {
        // Get page data
        $name = get_post_meta($page_id, '_dsmk_name', true);
        $email = get_post_meta($page_id, '_dsmk_email', true);
        $affiliate_link = get_post_meta($page_id, '_dsmk_affiliate_link', true);
        $logo_id = get_post_meta($page_id, '_dsmk_logo_id', true);
        
        // Determine if we have a custom logo - MUST be a valid, non-zero ID
        $has_custom_logo = !empty($logo_id) && $logo_id > 0;
        
        // Log the logo information for debugging
        error_log('Processing template data for page ' . $page_id);
        error_log('Logo ID: ' . $logo_id);
        error_log('Has custom logo: ' . ($has_custom_logo ? 'yes' : 'no'));
        
        // Get logo URL if available
        $logo_url = '';
        if ($has_custom_logo) {
            $logo_url = wp_get_attachment_url($logo_id);
            error_log('Logo URL: ' . ($logo_url ? $logo_url : 'Not found'));
            
            // Double-check that we have a valid logo URL
            if (empty($logo_url)) {
                $has_custom_logo = false;
                error_log('Logo URL is empty, treating as no custom logo');
            }
        }
        
        // Get the price from the affiliate link if available
        $price = $this->extract_price_from_link($affiliate_link);
        
        // Process the template data recursively
        if ($has_custom_logo) {
            // Only process for logo replacement if we have a custom logo
            $template_data = $this->process_elements_recursively($template_data, [
                'name' => $name,
                'email' => $email,
                'affiliate_link' => $affiliate_link,
                'has_custom_logo' => true,
                'logo_id' => $logo_id,
                'logo_url' => $logo_url,
                'price' => $price
            ]);
        } else {
            // Skip logo processing entirely if no custom logo
            $template_data = $this->process_elements_recursively($template_data, [
                'name' => $name,
                'email' => $email,
                'affiliate_link' => $affiliate_link,
                'has_custom_logo' => false,
                'logo_id' => 0,
                'logo_url' => '',
                'price' => $price,
                'skip_logo_processing' => true  // Add flag to skip logo processing
            ]);
        }
        
        return $template_data;
    }
    
    /**
     * Extract price from affiliate link
     * 
     * @param string $link The affiliate link
     * @return string The price or empty string if not found
     */
    private function extract_price_from_link($link) {
        // Default price if we can't extract it
        $default_price = '$97';
        
        // Check if link contains price indicators
        if (strpos($link, '814557804') !== false) {
            return '$97';
        } elseif (strpos($link, '1858795045') !== false) {
            return '$57';
        } elseif (strpos($link, '298281289') !== false) {
            return '$67';
        } elseif (strpos($link, '1233593608') !== false) {
            return '$47';
        } elseif (strpos($link, '798534830') !== false) {
            return '$37';
        }
        
        return $default_price;
    }
    
    /**
     * Update only text and links, never touch images
     * 
     * @param array $template_data The template data to process
     * @param string $name The user's name
     * @param string $affiliate_link The affiliate link
     * @param string $price The price from the affiliate link
     * @return array The processed template data with only text and links updated
     */
    private function update_text_and_links($template_data, $name, $affiliate_link, $price) {
        if (!is_array($template_data)) {
            return $template_data;
        }
        
        // Process each element recursively
        foreach ($template_data as &$element) {
            // Skip if not an array
            if (!is_array($element)) {
                continue;
            }
            
            // CRITICAL: Skip ALL image widgets completely
            // This ensures the default logo is preserved exactly as it is
            if (isset($element['widgetType']) && $element['widgetType'] === 'image') {
                continue;
            }
            
            // Process button widgets to update text with price
            if (isset($element['widgetType']) && $element['widgetType'] === 'button') {
                if (isset($element['settings']['text']) && strpos($element['settings']['text'], 'Reserve Your Seat') !== false) {
                    // Update button text with the correct price
                    $element['settings']['text'] = 'Reserve Your Seat For ' . $price;
                    error_log('Updated button text with price: ' . $price);
                }
                
                // Update button links that contain affiliate link placeholders
                if (isset($element['settings']['link']['url']) && strpos($element['settings']['link']['url'], '{{affiliate_link}}') !== false) {
                    $element['settings']['link']['url'] = str_replace('{{affiliate_link}}', $affiliate_link, $element['settings']['link']['url']);
                    error_log('Updated button link with affiliate link: ' . $affiliate_link);
                }
            }
            
            // Replace name placeholders in text
            if (isset($element['settings']['title']) && strpos($element['settings']['title'], '{{name}}') !== false) {
                $element['settings']['title'] = str_replace('{{name}}', $name, $element['settings']['title']);
                error_log('Updated title with name: ' . $name);
            }
            
            // Replace affiliate link placeholders in any URLs
            if (isset($element['settings']['link']['url']) && strpos($element['settings']['link']['url'], '{{affiliate_link}}') !== false) {
                $element['settings']['link']['url'] = str_replace('{{affiliate_link}}', $affiliate_link, $element['settings']['link']['url']);
                error_log('Updated link with affiliate link: ' . $affiliate_link);
            }
            
            // Process child elements recursively
            if (isset($element['elements']) && is_array($element['elements'])) {
                $element['elements'] = $this->update_text_and_links($element['elements'], $name, $affiliate_link, $price);
            }
        }
        
        return $template_data;
    }
    
    /**
     * Process elements recursively to replace placeholders and handle dynamic content
     * 
     * @param array $elements The elements to process
     * @param array $data The data to use for replacements
     * @return array The processed elements
     */
    private function process_elements_recursively($elements, $data) {
        if (!is_array($elements)) {
            return $elements;
        }
        
        foreach ($elements as &$element) {
            // Process button widgets to update text with price
            if (isset($element['widgetType']) && $element['widgetType'] === 'button') {
                if (isset($element['settings']['text']) && strpos($element['settings']['text'], 'Reserve Your Seat') !== false) {
                    // Update button text with the correct price
                    $element['settings']['text'] = 'Reserve Your Seat For ' . $data['price'];
                    error_log('Updated button text with price: ' . $data['price']);
                }
            }
            
            // Handle image widgets - for logo replacement or preservation
            if (isset($element['widgetType']) && $element['widgetType'] === 'image') {
                // CRITICAL: If no custom logo was uploaded, skip ALL image processing completely
                // This ensures the default template logo is preserved exactly as it is
                if (!$data['has_custom_logo']) {
                    // Do not process ANY images when no custom logo is uploaded
                    continue;
                }
                
                // From this point on, we know we have a custom logo
                // ONLY identify logo widgets by the CSS class 'dsmk-logo'
                $is_logo_widget = false;
                $logo_identifier = '';
                
                // Check CSS classes - STRICT check for 'dsmk-logo'
                if (isset($element['settings']['_css_classes'])) {
                    error_log('CSS classes: ' . $element['settings']['_css_classes']);
                    if (strpos($element['settings']['_css_classes'], 'dsmk-logo') !== false) {
                        $is_logo_widget = true;
                        $logo_identifier = 'CSS class dsmk-logo';
                        error_log('Found logo widget with CSS class dsmk-logo');
                    }
                }
                
                // Check element ID - STRICT check for 'dsmk-logo'
                if (isset($element['settings']['_element_id']) && !$is_logo_widget) {
                    error_log('Element ID: ' . $element['settings']['_element_id']);
                    if ($element['settings']['_element_id'] === 'dsmk-logo') {
                        $is_logo_widget = true;
                        $logo_identifier = 'element ID dsmk-logo';
                        error_log('Found logo widget with element ID dsmk-logo');
                    }
                }
                
                // Check custom CSS ID - STRICT check for 'dsmk-logo'
                if (isset($element['settings']['css_id']) && !$is_logo_widget) {
                    error_log('CSS ID: ' . $element['settings']['css_id']);
                    if ($element['settings']['css_id'] === 'dsmk-logo') {
                        $is_logo_widget = true;
                        $logo_identifier = 'CSS ID dsmk-logo';
                        error_log('Found logo widget with CSS ID dsmk-logo');
                    }
                }
                
                // If this is NOT a logo widget, skip it entirely
                if (!$is_logo_widget) {
                    error_log('This is NOT a logo widget - preserving original image');
                    // Skip to the next element - DO NOT modify this image
                    continue;
                }
                
                // Process the logo widget - we've already confirmed we have a custom logo
                error_log('Identified logo widget via ' . $logo_identifier);
                error_log('Replacing logo with custom logo ID: ' . $data['logo_id'] . ', URL: ' . $data['logo_url']);
                
                // Update all image properties to ensure the logo is properly replaced
                $element['settings']['image']['id'] = $data['logo_id'];
                $element['settings']['image']['url'] = $data['logo_url'];
                
                // Also update these properties if they exist
                if (isset($element['settings']['image']['source'])) {
                    $element['settings']['image']['source'] = 'library';
                }
                
                // Clear any default image settings that might override our custom logo
                if (isset($element['settings']['image']['default'])) {
                    unset($element['settings']['image']['default']);
                }
            }
            
            // Replace name placeholder
            if (isset($element['settings']['title']) && strpos($element['settings']['title'], '{{name}}') !== false) {
                $element['settings']['title'] = str_replace('{{name}}', $data['name'], $element['settings']['title']);
            }
            
            // Replace affiliate link
            if (isset($element['settings']['link']['url']) && strpos($element['settings']['link']['url'], '{{affiliate_link}}') !== false) {
                $element['settings']['link']['url'] = str_replace('{{affiliate_link}}', $data['affiliate_link'], $element['settings']['link']['url']);
            }
            
            // Process child elements recursively
            if (isset($element['elements']) && is_array($element['elements'])) {
                $element['elements'] = $this->process_elements_recursively($element['elements'], $data);
            }
        }
        
        return $elements;
    }

    /**
     * Get default template data
     *
     * @return array Default template data with dynamic fields.
     */
    private function get_default_template_data() {
        // Get default logo information
        $default_logo_id = get_option('dsmk_default_logo_id', 0);
        $default_logo_url = '';
        
        if ($default_logo_id > 0) {
            $default_logo_url = wp_get_attachment_url($default_logo_id);
        } else {
            // Use plugin's default logo if no custom default is set
            $default_logo_url = plugin_dir_url(dirname(__FILE__)) . 'assets/images/default-logo.png';
        }
        
        // Simple template with dynamic fields
        return array(
            array(
                'id' => 'unique_section_id',
                'elType' => 'section',
                'settings' => array(
                    'layout' => 'full_width',
                    'gap' => 'no',
                ),
                'elements' => array(
                    array(
                        'id' => 'unique_column_id',
                        'elType' => 'column',
                        'settings' => array(
                            '_column_size' => 100,
                        ),
                        'elements' => array(
                            array(
                                'id' => 'unique_heading_id',
                                'elType' => 'widget',
                                'widgetType' => 'heading',
                                'settings' => array(
                                    'title' => '{{name}}\'s Landing Page',
                                    'align' => 'center',
                                    'size' => 'xl',
                                ),
                            ),
                            array(
                                'id' => 'unique_image_id',
                                'elType' => 'widget',
                                'widgetType' => 'image',
                                'settings' => array(
                                    'image' => array(
                                        'id' => '{{logo_id}}',
                                        'url' => '{{logo_url}}',
                                        'default' => array(
                                            'id' => $default_logo_id,
                                            'url' => $default_logo_url,
                                        ),
                                    ),
                                    'align' => 'center',
                                ),
                            ),
                            array(
                                'id' => 'unique_button_id',
                                'elType' => 'widget',
                                'widgetType' => 'button',
                                'settings' => array(
                                    'text' => 'Visit Affiliate Link',
                                    'link' => array(
                                        'url' => '{{affiliate_link}}',
                                        'is_external' => 'true',
                                        'nofollow' => 'true',
                                    ),
                                    'align' => 'center',
                                    'size' => 'lg',
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );
    }
}
