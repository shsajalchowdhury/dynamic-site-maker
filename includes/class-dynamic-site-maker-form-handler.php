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
    }

    /**
     * Handle form submission via AJAX
     */
    public function handle_form_submission() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'dsmk_form_nonce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed.', 'dynamic-site-maker' ) ) );
        }

        // Check if user has already submitted the form
        $spam_protection = new DSMK_Spam_Protection();
        if ( $spam_protection->has_submitted() ) {
            wp_send_json_error( array( 'message' => __( 'You have already submitted a form. Only one submission is allowed per session.', 'dynamic-site-maker' ) ) );
        }

        // Validate name
        if ( empty( $_POST['name'] ) ) {
            wp_send_json_error( array( 'message' => __( 'Name is required.', 'dynamic-site-maker' ) ) );
        }
        $name = sanitize_text_field( wp_unslash( $_POST['name'] ) );

        // Validate affiliate link
        if ( empty( $_POST['affiliate_link'] ) ) {
            wp_send_json_error( array( 'message' => __( 'Affiliate link is required.', 'dynamic-site-maker' ) ) );
        }
        $affiliate_link = esc_url_raw( trim( wp_unslash( $_POST['affiliate_link'] ) ) );

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

            // Validate file type
            $file_info = wp_check_filetype( basename( $_FILES['logo']['name'] ) );
            $allowed_types = array( 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'svg' => 'image/svg+xml' );
            
            if ( empty( $file_info['ext'] ) || ! array_key_exists( $file_info['ext'], $allowed_types ) ) {
                wp_send_json_error( array( 'message' => __( 'Invalid file format. Allowed formats: JPG, PNG, SVG.', 'dynamic-site-maker' ) ) );
            }

            // Validate file size (5MB max)
            if ( $_FILES['logo']['size'] > 5 * 1024 * 1024 ) {
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

        // Create a new landing page
        $landing_page = new DSMK_Landing_Page();
        $page_id = $landing_page->create_page(
            array(
                'name'           => $name,
                'logo_id'        => $logo_id,
                'affiliate_link' => $affiliate_link,
            )
        );

        if ( is_wp_error( $page_id ) ) {
            wp_send_json_error( array( 'message' => $page_id->get_error_message() ) );
        }

        // Mark user as having submitted the form
        $spam_protection->mark_as_submitted();

        // Return success with the URL of the new page
        wp_send_json_success( array(
            'message' => __( 'Landing page created successfully!', 'dynamic-site-maker' ),
            'url'     => get_permalink( $page_id ),
        ) );
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
                'label'       => __( 'Your Name', 'dynamic-site-maker' ),
                'placeholder' => __( 'Enter your name', 'dynamic-site-maker' ),
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
