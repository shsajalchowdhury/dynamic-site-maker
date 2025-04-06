<?php
/**
 * Form template
 *
 * @package Dynamic_Site_Maker
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Get form fields
$form_handler = new DSMK_Form_Handler();
$fields = $form_handler->get_form_fields();

// Get the spam protection instance
$spam_protection = new DSMK_Spam_Protection();

// Check if the user has already submitted the form
$has_submitted = $spam_protection->has_submitted();

// Get customization settings
$page_title = get_option('dsmk_shortcode_page_title', 'Create Your Site');
$page_description = get_option('dsmk_shortcode_page_description', 'Fill out the form below to generate your custom site.');
$header_bg_color = get_option('dsmk_shortcode_header_bg_color', '#2196f3');
$button_color = get_option('dsmk_shortcode_button_color', '#2196f3');
$text_color = get_option('dsmk_shortcode_text_color', '#333333');
$step1_title = get_option('dsmk_shortcode_step1_title', 'Your Information');
$step2_title = get_option('dsmk_shortcode_step2_title', 'Upload Your Logo');
$step3_title = get_option('dsmk_shortcode_step3_title', 'Add Your Links');
$name_label = get_option('dsmk_shortcode_name_label', 'Affiliate Name');
$email_label = get_option('dsmk_shortcode_email_label', 'Email Address');
$logo_label = get_option('dsmk_shortcode_logo_label', 'Your Logo');
$link_label = get_option('dsmk_shortcode_link_label', 'Affiliate Link');
$step1_label = get_option('dsmk_shortcode_step1_label', 'Your Info');
$step2_label = get_option('dsmk_shortcode_step2_label', 'Logo');
$step3_label = get_option('dsmk_shortcode_step3_label', 'Links');
$name_placeholder = get_option('dsmk_shortcode_name_placeholder', 'Enter Affiliate Name');
$email_placeholder = get_option('dsmk_shortcode_email_placeholder', 'Enter Email Address');
$link_placeholder = get_option('dsmk_shortcode_link_placeholder', 'https://example.com/affiliate');

// Generate custom CSS based on settings
$custom_css = "
.dsmk-form-header {
    background: linear-gradient(135deg, {$header_bg_color}, " . adjustBrightness($header_bg_color, -20) . ");
}
.dsmk-button {
    background-color: {$button_color};
    color: #ffffff !important;
}
.dsmk-button:hover {
    background-color: " . adjustBrightness($button_color, -10) . ";
}
.dsmk-button-prev {
    background-color: #f5f5f5;
    color: #333333 !important;
}
.dsmk-button-prev:hover {
    background-color: #e0e0e0;
    color: #333333 !important;
}
.dsmk-progress-step.active .dsmk-step-number,
.dsmk-progress-connector.active {
    background-color: {$button_color};
}
.dsmk-progress-step.active .dsmk-step-label {
    color: {$button_color};
}
.dsmk-form-step h3,
.dsmk-form-label {
    color: {$text_color};
}
";

/**
 * Helper function to adjust color brightness
 *
 * @param string $hex Hex color code
 * @param int $steps Steps to adjust brightness (-255 to 255)
 * @return string Adjusted hex color
 */
function adjustBrightness($hex, $steps) {
    // Remove # if present
    $hex = ltrim($hex, '#');
    
    // Convert to RGB
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    // Adjust brightness
    $r = max(0, min(255, $r + $steps));
    $g = max(0, min(255, $g + $steps));
    $b = max(0, min(255, $b + $steps));
    
    // Convert back to hex
    return '#' . sprintf('%02x%02x%02x', $r, $g, $b);
}
?>

<style>
<?php echo $custom_css; ?>
</style>

<div class="dsmk-form-container">
    <?php if ( $has_submitted ) : ?>
        <div class="dsmk-notice dsmk-notice--warning">
            <div class="dsmk-notice-icon">
                <span class="dashicons dashicons-warning"></span>
            </div>
            <div class="dsmk-notice-content">
                <h3><?php esc_html_e( 'Already Submitted', 'dynamic-site-maker' ); ?></h3>
                <p><?php esc_html_e( 'You have already submitted a form. Only one submission is allowed per session.', 'dynamic-site-maker' ); ?></p>
            </div>
        </div>
    <?php else : ?>
        <div class="dsmk-form-wrapper">
            <!-- Form Header with Logo and Title -->
            <div class="dsmk-form-header">
                <div class="dsmk-form-logo">
                    <svg viewBox="0 0 24 24" width="48" height="48">
                        <path fill="#ffffff" d="M19,2H5C3.89,2 3,2.89 3,4V20A2,2 0 0,0 5,22H19A2,2 0 0,0 21,20V4C21,2.89 20.1,2 19,2M11,4H13V9H11V4M11,11H13V19H11V11M5,4H9V9H5V4M5,11H9V19H5V11M15,4H19V9H15V4M15,11H19V19H15V11Z"/>
                    </svg>
                </div>
                <h2><?php echo esc_html($page_title); ?></h2>
                <p><?php echo esc_html($page_description); ?></p>
            </div>

            <!-- Progress Steps -->
            <div class="dsmk-form-progress">
                <div class="dsmk-progress-steps">
                    <div class="dsmk-progress-step active" data-step="1">
                        <div class="dsmk-step-number">1</div>
                        <div class="dsmk-step-label"><?php echo esc_html($step1_label); ?></div>
                    </div>
                    <div class="dsmk-progress-connector"></div>
                    <div class="dsmk-progress-step" data-step="2">
                        <div class="dsmk-step-number">2</div>
                        <div class="dsmk-step-label"><?php echo esc_html($step2_label); ?></div>
                    </div>
                    <div class="dsmk-progress-connector"></div>
                    <div class="dsmk-progress-step" data-step="3">
                        <div class="dsmk-step-number">3</div>
                        <div class="dsmk-step-label"><?php echo esc_html($step3_label); ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Form Messages -->
            <div class="dsmk-form__messages"></div>
            
            <!-- Form Content -->
            <form id="dsmk-form" class="dsmk-form" method="post" enctype="multipart/form-data">
                <!-- Step 1 - Personal Information -->
                <div class="dsmk-form-step active" data-step="1">
                    <h3><?php echo esc_html($step1_title); ?></h3>
                    
                    <div class="dsmk-form-field">
                        <label for="dsmk-name" class="dsmk-form-label">
                            <?php echo esc_html($name_label); ?>
                            <span class="dsmk-form-required">*</span>
                        </label>
                        <div class="dsmk-input-wrapper">
                            <input type="text" 
                                   id="dsmk-name" 
                                   name="name" 
                                   class="dsmk-form-input" 
                                   placeholder="<?php echo esc_attr($name_placeholder); ?>"
                                   required>
                        </div>
                    </div>
                    
                    <div class="dsmk-form-field">
                        <label for="dsmk-email" class="dsmk-form-label">
                            <?php echo esc_html($email_label); ?>
                            <span class="dsmk-form-required">*</span>
                        </label>
                        <div class="dsmk-input-wrapper">
                            <input type="email" 
                                   id="dsmk-email" 
                                   name="email" 
                                   class="dsmk-form-input" 
                                   placeholder="<?php echo esc_attr($email_placeholder); ?>"
                                   required>
                        </div>
                    </div>
                    
                    <div class="dsmk-form-actions">
                        <button type="button" class="dsmk-button dsmk-button-next" data-next="2">
                            <?php esc_html_e( 'Next Step', 'dynamic-site-maker' ); ?>
                            <span class="dsmk-button-icon-right">
                                <svg viewBox="0 0 24 24" width="16" height="16">
                                    <path fill="currentColor" d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z" />
                                </svg>
                            </span>
                        </button>
                    </div>
                </div>
                
                <!-- Step 2 - Logo Upload -->
                <div class="dsmk-form-step" data-step="2">
                    <h3><?php echo esc_html($step2_title); ?></h3>
                    
                    <div class="dsmk-form-field">
                        <label class="dsmk-form-label">
                            <?php echo esc_html($logo_label); ?>
                            <span class="dsmk-form-required">*</span>
                        </label>
                        
                        <div class="dsmk-file-upload">
                            <div class="dsmk-file-upload-area" id="dsmk-file-drop-area">
                                <input type="file" 
                                       id="dsmk-logo" 
                                       name="logo" 
                                       class="dsmk-file-input" 
                                       accept="image/jpeg,image/png,image/svg+xml"
                                       required>
                                <div class="dsmk-file-upload-content">
                                    <div class="dsmk-file-upload-text">
                                        <span class="dsmk-drag-text"><?php esc_html_e( 'Drag & drop your logo here', 'dynamic-site-maker' ); ?></span>
                                        <span class="dsmk-or-text"><?php esc_html_e( 'or', 'dynamic-site-maker' ); ?></span>
                                        <button type="button" class="dsmk-button dsmk-browse-button">
                                            <?php esc_html_e( 'Browse Files', 'dynamic-site-maker' ); ?>
                                        </button>
                                    </div>
                                </div>
                                <div class="dsmk-file-preview">
                                    <div class="dsmk-preview-image"></div>
                                    <div class="dsmk-file-info">
                                        <div class="dsmk-file-name"></div>
                                        <div class="dsmk-file-size"></div>
                                    </div>
                                    <button type="button" class="dsmk-remove-file">
                                        <svg viewBox="0 0 24 24" width="16" height="16">
                                            <path fill="currentColor" d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <p class="dsmk-form-description">
                                <?php esc_html_e( 'Upload your logo (JPG, PNG, SVG - Max 1MB)', 'dynamic-site-maker' ); ?>
                            </p>
                            <div class="dsmk-logo-size-guide">
                                <h4><?php esc_html_e( 'Logo Size Guide:', 'dynamic-site-maker' ); ?></h4>
                                <ul>
                                    <li><?php esc_html_e( 'Recommended size: 716x138 pixels', 'dynamic-site-maker' ); ?></li>
                                    <li><?php esc_html_e( 'Upload a logo image (maximum size: 1MB)', 'dynamic-site-maker' ); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="dsmk-form-actions">
                        <button type="button" class="dsmk-button dsmk-button-prev" data-prev="1">
                            <span class="dsmk-button-icon-left">
                                <svg viewBox="0 0 24 24" width="16" height="16">
                                    <path fill="currentColor" d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z" />
                                </svg>
                            </span>
                            <?php esc_html_e( 'Previous', 'dynamic-site-maker' ); ?>
                        </button>
                        
                        <button type="button" class="dsmk-button dsmk-button-next" data-next="3">
                            <?php esc_html_e( 'Next Step', 'dynamic-site-maker' ); ?>
                            <span class="dsmk-button-icon-right">
                                <svg viewBox="0 0 24 24" width="16" height="16">
                                    <path fill="currentColor" d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z" />
                                </svg>
                            </span>
                        </button>
                    </div>
                </div>
                
                <!-- Step 3 - Affiliate Link -->
                <div class="dsmk-form-step" data-step="3">
                    <h3><?php echo esc_html($step3_title); ?></h3>
                    
                    <div class="dsmk-form-field">
                        <label for="dsmk-affiliate-link" class="dsmk-form-label">
                            <?php echo esc_html($link_label); ?>
                            <span class="dsmk-form-required">*</span>
                        </label>
                        <div class="dsmk-input-wrapper">
                            <input type="url" 
                                   id="dsmk-affiliate-link" 
                                   name="affiliate_link" 
                                   class="dsmk-form-input" 
                                   placeholder="<?php echo esc_attr($link_placeholder); ?>"
                                   required>
                        </div>
                        <p class="dsmk-form-description">
                            <?php esc_html_e( 'This link will be used in your site.', 'dynamic-site-maker' ); ?>
                        </p>
                    </div>
                    
                    <div class="dsmk-form-actions">
                        <button type="button" class="dsmk-button dsmk-button-prev" data-prev="2">
                            <span class="dsmk-button-icon-left">
                                <svg viewBox="0 0 24 24" width="16" height="16">
                                    <path fill="currentColor" d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z" />
                                </svg>
                            </span>
                            <?php esc_html_e( 'Previous', 'dynamic-site-maker' ); ?>
                        </button>
                        
                        <button type="submit" class="dsmk-button dsmk-button-submit">
                            <?php esc_html_e( 'Generate Site', 'dynamic-site-maker' ); ?>
                            <span class="dsmk-button-icon-right">
                                <svg viewBox="0 0 24 24" width="16" height="16">
                                    <path fill="currentColor" d="M21,7L9,19L3.5,13.5L4.91,12.09L9,16.17L19.59,5.59L21,7Z" />
                                </svg>
                            </span>
                        </button>
                    </div>
                </div>
                
                <?php wp_nonce_field( 'dsmk_form_nonce', 'dsmk_nonce' ); ?>
                
                <!-- Loading Overlay -->
                <div class="dsmk-form-loading">
                    <div class="dsmk-loading-spinner">
                        <div class="dsmk-spinner-inner"></div>
                    </div>
                    <p><?php esc_html_e( 'Creating your site...', 'dynamic-site-maker' ); ?></p>
                </div>
            </form>
            
            <!-- Success Message -->
            <div class="dsmk-form-success">
                <div class="dsmk-success-icon">
                    <svg viewBox="0 0 24 24" width="64" height="64">
                        <path fill="currentColor" d="M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4M11,16.5L6.5,12L7.91,10.59L11,13.67L16.59,8.09L18,9.5L11,16.5Z" />
                    </svg>
                </div>
                <h3><?php echo esc_html( get_option( 'dsmk_form_success_message', __( 'Site created successfully!', 'dynamic-site-maker' ) ) ); ?></h3>
                <p><?php esc_html_e( 'Redirecting you to your new site...', 'dynamic-site-maker' ); ?></p>
                <div class="dsmk-redirect-progress">
                    <div class="dsmk-redirect-bar"></div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
