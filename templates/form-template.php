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
/* Username Placeholder Highlight */
.dsmk-username-placeholder {
    display: inline-block;
    min-width: 50px;
    font-weight: bold;
    color: #0073aa;
    border-bottom: 1px dashed #0073aa;
    padding: 0 2px;
}

.dsmk-username-placeholder.has-username {
    background-color: #e6f3fa;
    border: 1px solid #0073aa;
    border-radius: 3px;
    padding: 2px 5px;
    margin: 0 2px;
    animation: pulse 1s ease-in-out;
}

@keyframes pulse {
    0% { background-color: #e6f3fa; }
    50% { background-color: #b3e0ff; }
    100% { background-color: #e6f3fa; }
}

/* Success Message Styling */
.dsmk-form-success {
    background-color: #f0f9e8;
    border: 1px solid #4caf50;
    border-radius: 5px;
    padding: 20px;
    margin: 20px 0;
    text-align: center;
}

.dsmk-form-success h3 {
    color: #2e7d32;
    margin-top: 0;
}

.dsmk-page-url-display {
    margin: 15px 0;
    padding: 15px;
    background-color: #f5f5f5;
    border-radius: 5px;
    border: 1px solid #e0e0e0;
}

.dsmk-page-url {
    display: block;
    font-weight: bold;
    word-break: break-all;
    padding: 10px;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 3px;
    margin: 10px 0;
    color: #0073aa;
    text-decoration: none;
}

.dsmk-page-url:hover {
    background-color: #f0f7fc;
    color: #00a0d2;
}

.dsmk-url-highlight {
    background-color: #e6f7ff;
    border-color: #0073aa;
    box-shadow: 0 0 5px rgba(0, 115, 170, 0.5);
}

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
                <div class="dsmk-progress-bar">
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
                        <div class="dsmk-step-label"><?php esc_html_e( 'Username', 'dynamic-site-maker' ); ?></div>
                    </div>
                    <div class="dsmk-progress-connector"></div>
                    <div class="dsmk-progress-step" data-step="4">
                        <div class="dsmk-step-number">4</div>
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
                                   pattern="[a-zA-Z0-9\s]+"
                                   title="<?php esc_attr_e('Only letters, numbers, and spaces are allowed', 'dynamic-site-maker'); ?>"
                                   required>
                        </div>
                        <p class="dsmk-form-description">
                            <?php esc_html_e( 'Only alphanumeric characters and spaces are allowed.', 'dynamic-site-maker' ); ?>
                        </p>
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
                        </label>
                        
                        <div class="dsmk-file-upload">
                            <div class="dsmk-notice dsmk-notice--info" style="margin-bottom: 15px;">
                                <div class="dsmk-notice-icon">
                                    <svg viewBox="0 0 24 24" width="24" height="24">
                                        <path fill="currentColor" d="M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M13,7H11V9H13V7M13,11H11V17H13V11Z" />
                                    </svg>
                                </div>
                                <div class="dsmk-notice-content">
                                    <p><?php esc_html_e( 'You can upload your own logo or use our default logo if you prefer.', 'dynamic-site-maker' ); ?></p>
                                </div>
                            </div>
                            <div class="dsmk-file-upload-area" id="dsmk-file-drop-area">
                                <input type="file" 
                                       id="dsmk-logo" 
                                       name="logo" 
                                       class="dsmk-file-input" 
                                       accept="image/jpeg,image/png,image/svg+xml">
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
                                    <li><?php esc_html_e( 'Leave empty to use our default logo', 'dynamic-site-maker' ); ?></li>
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
                
                <!-- Step 3 - Username -->
                <div class="dsmk-form-step" data-step="3">
                    <h3><?php esc_html_e( 'Enter Your Explodely Username', 'dynamic-site-maker' ); ?></h3>
                    
                    <div class="dsmk-notice dsmk-notice--info" style="margin-bottom: 15px;">
                        <div class="dsmk-notice-icon">
                            <svg viewBox="0 0 24 24" width="24" height="24">
                                <path fill="currentColor" d="M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M13,7H11V9H13V7M13,11H11V17H13V11Z" />
                            </svg>
                        </div>
                        <div class="dsmk-notice-content">
                            <p><?php esc_html_e( 'You need an Explodely affiliate account to use this service.', 'dynamic-site-maker' ); ?>
                            <br><a href="https://explodely.com/affiliate/signup" target="_blank" class="dsmk-link"><?php esc_html_e( 'Click here to sign up for an Explodely account', 'dynamic-site-maker' ); ?></a></p>
                        </div>
                    </div>
                    
                    <div class="dsmk-form-field">
                        <label for="dsmk-username" class="dsmk-form-label">
                            <?php esc_html_e( 'Your Explodely Username', 'dynamic-site-maker' ); ?>
                            <span class="dsmk-form-required">*</span>
                        </label>
                        <div class="dsmk-input-wrapper">
                            <input type="text" 
                                   id="dsmk-username" 
                                   name="username" 
                                   class="dsmk-form-input" 
                                   placeholder="<?php esc_attr_e( 'Enter your Explodely username', 'dynamic-site-maker' ); ?>"
                                   pattern="[a-zA-Z0-9_-]+"
                                   title="<?php esc_attr_e('Only letters, numbers, underscores, and hyphens are allowed', 'dynamic-site-maker'); ?>"
                                   required>
                        </div>
                        <p class="dsmk-form-description">
                            <?php esc_html_e( 'Enter your existing Explodely username. This is required to create your affiliate site.', 'dynamic-site-maker' ); ?>
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
                        
                        <button type="button" class="dsmk-button dsmk-button-next" data-next="4">
                            <?php esc_html_e( 'Next Step', 'dynamic-site-maker' ); ?>
                            <span class="dsmk-button-icon-right">
                                <svg viewBox="0 0 24 24" width="16" height="16">
                                    <path fill="currentColor" d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z" />
                                </svg>
                            </span>
                        </button>
                    </div>
                </div>
                
                <!-- Step 4 - Affiliate Link -->
                <div class="dsmk-form-step" data-step="4">
                    <h3><?php echo esc_html($step3_title); ?></h3>
                    
                    <div class="dsmk-form-field">
                        <label class="dsmk-form-label">
                            <?php echo esc_html($link_label); ?>
                            <span class="dsmk-form-required">*</span>
                        </label>
                        
                        <div class="dsmk-affiliate-links-wrapper">
                            <div class="dsmk-notice dsmk-notice--info" style="margin-bottom: 15px;">
                                <div class="dsmk-notice-icon">
                                    <svg viewBox="0 0 24 24" width="24" height="24">
                                        <path fill="currentColor" d="M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M13,7H11V9H13V7M13,11H11V17H13V11Z" />
                                    </svg>
                                </div>
                                <div class="dsmk-notice-content">
                                    <p><?php esc_html_e( 'Select one of the following affiliate links. Your username will be automatically added to the link.', 'dynamic-site-maker' ); ?></p>
                                </div>
                            </div>
                            
                            <div class="dsmk-affiliate-links-list">
                                <div class="dsmk-affiliate-link-option">
                                    <input type="radio" id="dsmk-link-option-1" name="affiliate_link" value="https://explodely.com/p/814557804?affiliate=" class="dsmk-affiliate-link-radio" required>
                                    <label for="dsmk-link-option-1" class="dsmk-affiliate-link-label">
                                        <span class="dsmk-affiliate-link-price">$97</span>
                                        <span class="dsmk-affiliate-link-url">https://explodely.com/p/814557804?affiliate=<span class="dsmk-username-placeholder"></span></span>
                                    </label>
                                </div>
                                
                                <div class="dsmk-affiliate-link-option">
                                    <input type="radio" id="dsmk-link-option-2" name="affiliate_link" value="https://explodely.com/p/1858795045?affiliate=" class="dsmk-affiliate-link-radio">
                                    <label for="dsmk-link-option-2" class="dsmk-affiliate-link-label">
                                        <span class="dsmk-affiliate-link-price">$57</span>
                                        <span class="dsmk-affiliate-link-url">https://explodely.com/p/1858795045?affiliate=<span class="dsmk-username-placeholder"></span></span>
                                    </label>
                                </div>
                                
                                <div class="dsmk-affiliate-link-option">
                                    <input type="radio" id="dsmk-link-option-3" name="affiliate_link" value="https://explodely.com/p/298281289?affiliate=" class="dsmk-affiliate-link-radio">
                                    <label for="dsmk-link-option-3" class="dsmk-affiliate-link-label">
                                        <span class="dsmk-affiliate-link-price">$67</span>
                                        <span class="dsmk-affiliate-link-url">https://explodely.com/p/298281289?affiliate=<span class="dsmk-username-placeholder"></span></span>
                                    </label>
                                </div>
                                
                                <div class="dsmk-affiliate-link-option">
                                    <input type="radio" id="dsmk-link-option-4" name="affiliate_link" value="https://explodely.com/p/1233593608?affiliate=" class="dsmk-affiliate-link-radio">
                                    <label for="dsmk-link-option-4" class="dsmk-affiliate-link-label">
                                        <span class="dsmk-affiliate-link-price">$47</span>
                                        <span class="dsmk-affiliate-link-url">https://explodely.com/p/1233593608?affiliate=<span class="dsmk-username-placeholder"></span></span>
                                    </label>
                                </div>
                                
                                <div class="dsmk-affiliate-link-option">
                                    <input type="radio" id="dsmk-link-option-5" name="affiliate_link" value="https://explodely.com/p/798534830?affiliate=" class="dsmk-affiliate-link-radio">
                                    <label for="dsmk-link-option-5" class="dsmk-affiliate-link-label">
                                        <span class="dsmk-affiliate-link-price">$37</span>
                                        <span class="dsmk-affiliate-link-url">https://explodely.com/p/798534830?affiliate=<span class="dsmk-username-placeholder"></span></span>
                                    </label>
                                </div>
                            </div>
                            
                            <input type="hidden" id="dsmk-affiliate-link" name="full_affiliate_link" value="">
                        </div>
                        
                        <p class="dsmk-form-description">
                            <?php esc_html_e( 'The selected link will be used in your landing page.', 'dynamic-site-maker' ); ?>
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
                <p><?php esc_html_e( 'Your new affiliate site is ready to use!', 'dynamic-site-maker' ); ?></p>
                <div class="dsmk-success-actions">
                    <a href="#" id="dsmk-visit-site" class="dsmk-button dsmk-button-primary" target="_blank">
                        <span class="dsmk-button-icon-left">
                            <svg viewBox="0 0 24 24" width="16" height="16">
                                <path fill="currentColor" d="M14,3V5H17.59L7.76,14.83L9.17,16.24L19,6.41V10H21V3M19,19H5V5H12V3H5C3.89,3 3,3.9 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V12H19V19Z" />
                            </svg>
                        </span>
                        <?php esc_html_e( 'Visit Your New Site', 'dynamic-site-maker' ); ?>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
