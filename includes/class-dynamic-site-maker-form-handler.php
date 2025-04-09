<?php
/**
 * Form Handler Class
 *
 * @package Dynamic_Site_Maker
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Class DSMK_Form_Handler
 *
 * Handles form submissions and data processing.
 */
class DSMK_Form_Handler {

    /**
     * Constructor
     */
    public function __construct() {
        // Register AJAX handler for form submission
        add_action( 'wp_ajax_dsmk_submit_form', array( $this, 'handle_form_submission' ) );
        add_action( 'wp_ajax_nopriv_dsmk_submit_form', array( $this, 'handle_form_submission' ) );
        
        // Register AJAX handler for content updates
        add_action( 'wp_ajax_dsmk_update_content', array( $this, 'handle_content_update' ) );
    }

    /**
     * Handle form submission via AJAX
     */
    public function handle_form_submission() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'dsmk_form_nonce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed.', 'dynamic-site-maker' ) ) );
        }

        // Check if this is an edit mode submission
        $is_edit_mode = isset( $_POST['edit_mode'] ) && $_POST['edit_mode'] === 'true';
        $existing_page_id = isset( $_POST['page_id'] ) ? absint( $_POST['page_id'] ) : 0;

        // Only check for spam protection on new submissions, not edits
        if ( !$is_edit_mode ) {
            // Check if user has already submitted the form
            $spam_protection = new DSMK_Spam_Protection();
            if ( $spam_protection->has_submitted() ) {
                wp_send_json_error( array( 'message' => __( 'You have already submitted a form. Only one submission is allowed per session.', 'dynamic-site-maker' ) ) );
            }
        }

        // Validate name
        if ( empty( $_POST['name'] ) ) {
            wp_send_json_error( array( 'message' => __( 'Name is required.', 'dynamic-site-maker' ) ) );
        }
        $name = sanitize_text_field( wp_unslash( $_POST['name'] ) );

        // Validate email
        if ( empty( $_POST['email'] ) ) {
            wp_send_json_error( array( 'message' => __( 'Email is required.', 'dynamic-site-maker' ) ) );
        }
        $email = sanitize_email( wp_unslash( $_POST['email'] ) );
        if ( ! is_email( $email ) ) {
            wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'dynamic-site-maker' ) ) );
        }

        // Validate affiliate link
        if ( empty( $_POST['affiliate_link'] ) ) {
            wp_send_json_error( array( 'message' => __( 'Affiliate link is required.', 'dynamic-site-maker' ) ) );
        }
        $affiliate_link = sanitize_text_field( wp_unslash( $_POST['affiliate_link'] ) );
        // Ensure it's a valid URL
        $affiliate_link = esc_url_raw( $affiliate_link );

        // Process logo upload
        $logo_id = 0;
        if ( ! empty( $_FILES['logo'] ) ) {
            if ( ! function_exists( 'wp_handle_upload' ) ) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
            }
            if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
                require_once ABSPATH . 'wp-admin/includes/image.php';
            }

            $upload_overrides = array(
                'test_form' => false,
            );

            // Validate file exists and has a name
            if ( ! isset( $_FILES['logo']['name'] ) || empty( $_FILES['logo']['name'] ) ) {
                wp_send_json_error( array( 'message' => __( 'No logo file was uploaded.', 'dynamic-site-maker' ) ) );
            }
            
            // Sanitize the file name
            $file_name = sanitize_file_name( wp_unslash( $_FILES['logo']['name'] ) );
            
            // Validate file type
            $file_info = wp_check_filetype( basename( $file_name ) );
            $allowed_types = array( 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'svg' => 'image/svg+xml' );
            
            if ( empty( $file_info['ext'] ) || ! array_key_exists( $file_info['ext'], $allowed_types ) ) {
                wp_send_json_error( array( 'message' => __( 'Invalid file format. Allowed formats: JPG, PNG, SVG.', 'dynamic-site-maker' ) ) );
            }

            // Validate file size (5MB max)
            if ( ! isset( $_FILES['logo']['size'] ) || $_FILES['logo']['size'] > 5 * 1024 * 1024 ) {
                wp_send_json_error( array( 'message' => __( 'Logo file size exceeds the limit of 5MB.', 'dynamic-site-maker' ) ) );
            }

            $movefile = wp_handle_upload( $_FILES['logo'], $upload_overrides );

            if ( $movefile && ! isset( $movefile['error'] ) ) {
                $file_path = $movefile['file'];
                $file_url = $movefile['url'];
                $file_type = $movefile['type'];
                $attachment = array(
                    'guid'           => $file_url,
                    'post_mime_type' => $file_type,
                    'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_path ) ),
                    'post_content'   => '',
                    'post_status'    => 'inherit',
                );
                $logo_id = wp_insert_attachment( $attachment, $file_path );
                
                if ( ! is_wp_error( $logo_id ) ) {
                    $attachment_data = wp_generate_attachment_metadata( $logo_id, $file_path );
                    wp_update_attachment_metadata( $logo_id, $attachment_data );
                } else {
                    wp_send_json_error( array( 'message' => __( 'Failed to save logo.', 'dynamic-site-maker' ) ) );
                }
            } else {
                wp_send_json_error( array( 'message' => __( 'Failed to upload logo.', 'dynamic-site-maker' ) ) );
            }
        } else {
            wp_send_json_error( array( 'message' => __( 'Logo is required.', 'dynamic-site-maker' ) ) );
        }

        // Check if we're editing an existing page or creating a new one
        if ( $is_edit_mode && $existing_page_id > 0 ) {
            // Update existing page
            update_post_meta( $existing_page_id, '_dsmk_name', $name );
            update_post_meta( $existing_page_id, '_dsmk_email', $email );
            update_post_meta( $existing_page_id, '_dsmk_logo_id', $logo_id );
            update_post_meta( $existing_page_id, '_dsmk_affiliate_link', $affiliate_link );
            
            // Update Elementor content if needed
            $this->update_elementor_content( $existing_page_id, 'logo', $logo_id );
            $this->update_elementor_content( $existing_page_id, 'affiliate_link', $affiliate_link );
            
            // Get the URL of the updated page
            $page_url = get_permalink( $existing_page_id );
            
            // Return success response
            wp_send_json_success( array(
                'message' => __( 'Site updated successfully!', 'dynamic-site-maker' ),
                'redirect' => $page_url,
                'page_id' => $existing_page_id,
                'is_update' => true,
            ) );
        } else {
            // Create new landing page
            $landing_page = new DSMK_Landing_Page();
            $page_id = $landing_page->create_page( $name, $email, $logo_id, $affiliate_link );
            
            if ( ! $page_id ) {
                wp_send_json_error( array( 'message' => __( 'Failed to create landing page.', 'dynamic-site-maker' ) ) );
            }
            
            // Mark as submitted to prevent multiple submissions
            $spam_protection->mark_as_submitted();
            
            // Get the URL of the created page
            $page_url = get_permalink( $page_id );
            
            // Return success response
            wp_send_json_success( array(
                'message' => __( 'Landing page created successfully!', 'dynamic-site-maker' ),
                'redirect' => $page_url,
                'page_id' => $page_id,
                'is_update' => false,
            ) );
        }
    }

    /**
     * Handle content update via AJAX
     */
    public function handle_content_update() {
        // Verify nonce
        if ( ! isset( $_POST['dsmk_update_content_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dsmk_update_content_nonce'] ) ), 'dsmk_update_content' ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed.', 'dynamic-site-maker' ) ) );
        }

        // Verify user capabilities (only admins can update content)
        if ( ! current_user_can( 'edit_pages' ) ) {
            wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'dynamic-site-maker' ) ) );
        }

        // Get post ID
        if ( empty( $_POST['post_id'] ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid page ID.', 'dynamic-site-maker' ) ) );
        }
        $post_id = intval( $_POST['post_id'] );

        // Verify post exists and is a landing page
        if ( ! get_post_meta( $post_id, '_dsmk_name', true ) ) {
            wp_send_json_error( array( 'message' => __( 'This is not a valid Dynamic Site Maker landing page.', 'dynamic-site-maker' ) ) );
        }

        $changes_made = false;

        // Handle affiliate link update
        if ( ! empty( $_POST['affiliate_link'] ) ) {
            $affiliate_link = esc_url_raw( wp_unslash( $_POST['affiliate_link'] ) );
            
            // Update the affiliate link in post meta
            update_post_meta( $post_id, '_dsmk_affiliate_link', $affiliate_link );
            
            // Update the link in the Elementor content
            $this->update_elementor_content( $post_id, 'affiliate_link', $affiliate_link );
            
            $changes_made = true;
        }

        // Handle logo update
        if ( ! empty( $_FILES['logo']['name'] ) ) {
            // Check file type
            $file_type = wp_check_filetype( $_FILES['logo']['name'] );
            if ( ! in_array( $file_type['ext'], array( 'jpg', 'jpeg', 'png', 'gif', 'webp' ), true ) ) {
                wp_send_json_error( array( 'message' => __( 'Invalid file type. Please upload an image file.', 'dynamic-site-maker' ) ) );
            }

            // Upload the file
            $upload = wp_handle_upload( $_FILES['logo'], array( 'test_form' => false ) );
            
            if ( isset( $upload['error'] ) ) {
                wp_send_json_error( array( 'message' => $upload['error'] ) );
            }

            if ( isset( $upload['file'] ) ) {
                // Create attachment
                $attachment = array(
                    'post_mime_type' => $upload['type'],
                    'post_title'     => sanitize_file_name( $_FILES['logo']['name'] ),
                    'post_content'   => '',
                    'post_status'    => 'inherit',
                );

                $attachment_id = wp_insert_attachment( $attachment, $upload['file'], $post_id );

                if ( ! is_wp_error( $attachment_id ) ) {
                    // Generate attachment metadata
                    require_once ABSPATH . 'wp-admin/includes/image.php';
                    $attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
                    wp_update_attachment_metadata( $attachment_id, $attachment_data );

                    // Update the logo attachment ID in post meta
                    update_post_meta( $post_id, '_dsmk_logo_id', $attachment_id );
                    
                    // Update the logo in the Elementor content
                    $this->update_elementor_content( $post_id, 'logo', $attachment_id );
                    
                    $changes_made = true;
                } else {
                    wp_send_json_error( array( 'message' => $attachment_id->get_error_message() ) );
                }
            }
        }

        if ( $changes_made ) {
            wp_send_json_success( array( 'message' => __( 'Content updated successfully.', 'dynamic-site-maker' ) ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'No changes were made.', 'dynamic-site-maker' ) ) );
        }
    }

    /**
     * Update Elementor content with new values
     *
     * @param int    $post_id Post ID.
     * @param string $type    Type of content to update ('logo' or 'affiliate_link').
     * @param mixed  $value   New value (attachment ID for logo, URL for affiliate link).
     */
    private function update_elementor_content( $post_id, $type, $value ) {
        // Get Elementor data
        $elementor_data = get_post_meta( $post_id, '_elementor_data', true );
        
        if ( empty( $elementor_data ) ) {
            error_log('Dynamic Site Maker: No Elementor data found for post ID ' . $post_id);
            return;
        }
        
        // Decode JSON data
        $elementor_data = json_decode( $elementor_data, true );
        
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            error_log('Dynamic Site Maker: Error decoding Elementor data for post ID ' . $post_id . ': ' . json_last_error_msg());
            return;
        }
        
        // Process the data recursively
        $updated_data = $this->process_elementor_elements( $elementor_data, $type, $value );
        
        // Save updated data
        update_post_meta( $post_id, '_elementor_data', wp_slash( json_encode( $updated_data ) ) );
        
        // Clear Elementor cache
        if ( class_exists( '\Elementor\Plugin' ) ) {
            \Elementor\Plugin::$instance->files_manager->clear_cache();
        }
        
        error_log('Dynamic Site Maker: Updated Elementor content for post ID ' . $post_id . ' with ' . $type);
    }

    /**
     * Process Elementor elements recursively
     *
     * @param array  $elements Elements to process.
     * @param string $type     Type of content to update ('logo' or 'affiliate_link').
     * @param mixed  $value    New value (attachment ID for logo, URL for affiliate link).
     * @return array Updated elements.
     */
    private function process_elementor_elements( $elements, $type, $value ) {
        foreach ( $elements as &$element ) {
            // Process this element
            if ( isset( $element['settings'] ) ) {
                // Check for logo
                if ( $type === 'logo' ) {
                    // Check if this is an image widget
                    if ( isset( $element['widgetType'] ) && $element['widgetType'] === 'image' ) {
                        // Check if this image has the dsmk-logo class
                        if ( isset( $element['settings']['_css_classes'] ) && strpos( $element['settings']['_css_classes'], 'dsmk-logo' ) !== false ) {
                            // Update the image
                            $element['settings']['image']['id'] = $value;
                            $element['settings']['image']['url'] = wp_get_attachment_url( $value );
                            error_log('Dynamic Site Maker: Updated logo image with ID ' . $value);
                        }
                    }
                }
                
                // Check for affiliate link
                if ( $type === 'affiliate_link' ) {
                    // Check for buttons
                    if ( isset( $element['widgetType'] ) && $element['widgetType'] === 'button' ) {
                        // Check if this button has the dsmk-affiliate class
                        if ( isset( $element['settings']['_css_classes'] ) && strpos( $element['settings']['_css_classes'], 'dsmk-affiliate' ) !== false ) {
                            // Update the button link
                            if ( isset( $element['settings']['link'] ) && is_array( $element['settings']['link'] ) ) {
                                $element['settings']['link']['url'] = $value;
                                error_log('Dynamic Site Maker: Updated button link to ' . $value);
                            }
                        }
                    }
                    
                    // Check for any element with a link containing the placeholder
                    if ( isset( $element['settings']['link'] ) && is_array( $element['settings']['link'] ) ) {
                        if ( isset( $element['settings']['link']['url'] ) && strpos( $element['settings']['link']['url'], '{{affiliate_link}}' ) !== false ) {
                            $element['settings']['link']['url'] = $value;
                            error_log('Dynamic Site Maker: Updated link with placeholder to ' . $value);
                        }
                    }
                    
                    // Additional check for href attribute in HTML widgets
                    if ( isset( $element['widgetType'] ) && $element['widgetType'] === 'html' && isset( $element['settings']['html'] ) ) {
                        $html = $element['settings']['html'];
                        if ( strpos( $html, 'dsmk-affiliate' ) !== false ) {
                            // Replace href in HTML
                            $pattern = '/(href=[\'"]).+?([\'"])/i';
                            $replacement = '$1' . $value . '$2';
                            $element['settings']['html'] = preg_replace( $pattern, $replacement, $html );
                            error_log('Dynamic Site Maker: Updated HTML widget with affiliate link');
                        }
                    }
                }
            }
            
            // Process child elements recursively
            if ( isset( $element['elements'] ) && is_array( $element['elements'] ) ) {
                $element['elements'] = $this->process_elementor_elements( $element['elements'], $type, $value );
            }
        }
        
        return $elements;
    }

    /**
     * Find existing page by email
     *
     * @param string $email Email address to search for
     * @return int|false Page ID if found, false otherwise
     */
    public function find_page_by_email($email) {
        $args = array(
            'post_type'      => 'page',
            'posts_per_page' => 1,
            'meta_query'     => array(
                array(
                    'key'     => '_dsmk_email',
                    'value'   => $email,
                    'compare' => '=',
                ),
            ),
        );
        
        $query = new WP_Query($args);
        
        if ($query->have_posts()) {
            return $query->posts[0]->ID;
        }
        
        return false;
    }

    /**
     * Get form fields
     *
     * @return array Form fields configuration.
     */
    public function get_form_fields() {
        return array(
            'name' => array(
                'type'        => 'text',
                'label'       => __( 'Affiliate Name', 'dynamic-site-maker' ),
                'placeholder' => __( 'Enter Affiliate Name', 'dynamic-site-maker' ),
                'required'    => true,
            ),
            'email' => array(
                'type'        => 'email',
                'label'       => __( 'Email Address', 'dynamic-site-maker' ),
                'placeholder' => __( 'Enter Email Address', 'dynamic-site-maker' ),
                'required'    => true,
            ),
            'logo' => array(
                'type'        => 'file',
                'label'       => __( 'Your Logo', 'dynamic-site-maker' ),
                'description' => __( 'Upload your logo (JPG, PNG, SVG - Max 5MB)', 'dynamic-site-maker' ),
                'required'    => true,
                'accept'      => 'image/jpeg,image/png,image/svg+xml',
            ),
            'affiliate_link' => array(
                'type'        => 'url',
                'label'       => __( 'Affiliate Link', 'dynamic-site-maker' ),
                'placeholder' => __( 'https://example.com/affiliate', 'dynamic-site-maker' ),
                'required'    => true,
            ),
        );
    }
}
