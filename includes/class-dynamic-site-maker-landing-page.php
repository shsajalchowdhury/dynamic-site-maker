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
        update_post_meta( $page_id, '_dsmk_affiliate_link', esc_url_raw( $data['affiliate_link'] ) );

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

        // Set the page to use Elementor
        update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );
        
        // Mark this as a Dynamic Site Maker landing page
        update_post_meta( $page_id, '_is_dsmk_landing_page', true );
        
        // Set page to use canvas template (no header/footer)
        update_post_meta( $page_id, '_wp_page_template', 'elementor_canvas' );
        
        // Get the template ID from the plugin settings
        $template_id = get_option( 'dsmk_elementor_template_id', 0 );
        
        if ( empty( $template_id ) ) {
            // If no template is set, use a default template
            $template_data = $this->get_default_template_data();
            update_post_meta( $page_id, '_elementor_data', wp_slash( json_encode( $template_data ) ) );
        } else {
            // Get the template content from Elementor's library
            $template_data = $this->get_elementor_template_data( $template_id );
            
            if ( $template_data ) {
                // Apply the template data to the page
                update_post_meta( $page_id, '_elementor_data', wp_slash( json_encode( $template_data ) ) );
                
                // Copy all template meta data to ensure styles are preserved
                $template_meta = get_post_meta( $template_id );
                if ( ! empty( $template_meta ) ) {
                    foreach ( $template_meta as $meta_key => $meta_value ) {
                        // Skip keys we've already set or that shouldn't be copied
                        if ( in_array( $meta_key, array( '_elementor_data', '_wp_page_template', 'post_content' ) ) ) {
                            continue;
                        }
                        
                        // Copy meta values that are related to Elementor
                        if ( strpos( $meta_key, '_elementor_' ) === 0 || strpos( $meta_key, 'elementor_' ) === 0 ) {
                            update_post_meta( $page_id, $meta_key, maybe_unserialize( $meta_value[0] ) );
                        }
                    }
                }
            } else {
                // Fallback to default if template retrieval fails
                $template_data = $this->get_default_template_data();
                update_post_meta( $page_id, '_elementor_data', wp_slash( json_encode( $template_data ) ) );
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
     * Get default template data
     *
     * @return array Default template data with dynamic fields.
     */
    private function get_default_template_data() {
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
