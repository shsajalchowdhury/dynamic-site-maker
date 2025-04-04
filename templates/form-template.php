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
?>

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
                        <path fill="#2196f3" d="M19,2H5C3.89,2 3,2.89 3,4V20A2,2 0 0,0 5,22H19A2,2 0 0,0 21,20V4C21,2.89 20.1,2 19,2M11,4H13V9H11V4M11,11H13V19H11V11M5,4H9V9H5V4M5,11H9V19H5V11M15,4H19V9H15V4M15,11H19V19H15V11Z"/>
                    </svg>
                </div>
                <h2><?php esc_html_e( 'Create Your Site', 'dynamic-site-maker' ); ?></h2>
                <p><?php esc_html_e( 'Fill out the form below to generate your custom site.', 'dynamic-site-maker' ); ?></p>
            </div>

            <!-- Progress Steps -->
            <div class="dsmk-form-progress">
                <div class="dsmk-progress-steps">
                    <div class="dsmk-progress-step active" data-step="1">
                        <div class="dsmk-step-number">1</div>
                        <div class="dsmk-step-label"><?php esc_html_e( 'Your Info', 'dynamic-site-maker' ); ?></div>
                    </div>
                    <div class="dsmk-progress-connector"></div>
                    <div class="dsmk-progress-step" data-step="2">
                        <div class="dsmk-step-number">2</div>
                        <div class="dsmk-step-label"><?php esc_html_e( 'Logo', 'dynamic-site-maker' ); ?></div>
                    </div>
                    <div class="dsmk-progress-connector"></div>
                    <div class="dsmk-progress-step" data-step="3">
                        <div class="dsmk-step-number">3</div>
                        <div class="dsmk-step-label"><?php esc_html_e( 'Links', 'dynamic-site-maker' ); ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Form Messages -->
            <div class="dsmk-form__messages"></div>
            
            <!-- Form Content -->
            <form id="dsmk-form" class="dsmk-form" method="post" enctype="multipart/form-data">
                <!-- Step 1 - Personal Information -->
                <div class="dsmk-form-step active" data-step="1">
                    <h3><?php esc_html_e( 'Your Information', 'dynamic-site-maker' ); ?></h3>
                    
                    <div class="dsmk-form-field">
                        <label for="dsmk-name" class="dsmk-form-label">
                            <?php esc_html_e( 'Affiliate Name', 'dynamic-site-maker' ); ?>
                            <span class="dsmk-form-required">*</span>
                        </label>
                        <div class="dsmk-input-wrapper">
                            <span class="dsmk-input-icon">
                                <svg viewBox="0 0 24 24" width="18" height="18">
                                    <path fill="currentColor" d="M12,4A4,4 0 0,1 16,8A4,4 0 0,1 12,12A4,4 0 0,1 8,8A4,4 0 0,1 12,4M12,14C16.42,14 20,15.79 20,18V20H4V18C4,15.79 7.58,14 12,14Z" />
                                </svg>
                            </span>
                            <input type="text" 
                                   id="dsmk-name" 
                                   name="name" 
                                   class="dsmk-form-input" 
                                   placeholder="<?php esc_attr_e( 'Enter Affiliate Name', 'dynamic-site-maker' ); ?>"
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
                    <h3><?php esc_html_e( 'Upload Your Logo', 'dynamic-site-maker' ); ?></h3>
                    
                    <div class="dsmk-form-field">
                        <label class="dsmk-form-label">
                            <?php esc_html_e( 'Your Logo', 'dynamic-site-maker' ); ?>
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
                                    <div class="dsmk-file-upload-icon">
                                        <svg viewBox="0 0 24 24" width="48" height="48">
                                            <path fill="currentColor" d="M13,9H18.5L13,3.5V9M6,2H14L20,8V20A2,2 0 0,1 18,22H6C4.89,22 4,21.1 4,20V4C4,2.89 4.89,2 6,2M6,20H15L18,20V12L14,16L12,14L6,20M8,9A2,2 0 0,0 6,11A2,2 0 0,0 8,13A2,2 0 0,0 10,11A2,2 0 0,0 8,9Z" />
                                        </svg>
                                    </div>
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
                                <?php esc_html_e( 'Upload your logo (JPG, PNG, SVG - Max 5MB)', 'dynamic-site-maker' ); ?>
                            </p>
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
                    <h3><?php esc_html_e( 'Add Your Links', 'dynamic-site-maker' ); ?></h3>
                    
                    <div class="dsmk-form-field">
                        <label for="dsmk-affiliate-link" class="dsmk-form-label">
                            <?php esc_html_e( 'Affiliate Link', 'dynamic-site-maker' ); ?>
                            <span class="dsmk-form-required">*</span>
                        </label>
                        <div class="dsmk-input-wrapper">
                            <span class="dsmk-input-icon">
                                <svg viewBox="0 0 24 24" width="18" height="18">
                                    <path fill="currentColor" d="M3.9,12C3.9,10.29 5.29,8.9 7,8.9H11V7H7A5,5 0 0,0 2,12A5,5 0 0,0 7,17H11V15.1H7C5.29,15.1 3.9,13.71 3.9,12M8,13H16V11H8V13M17,7H13V8.9H17C18.71,8.9 20.1,10.29 20.1,12C20.1,13.71 18.71,15.1 17,15.1H13V17H17A5,5 0 0,0 22,12A5,5 0 0,0 17,7Z" />
                                </svg>
                            </span>
                            <input type="url" 
                                   id="dsmk-affiliate-link" 
                                   name="affiliate_link" 
                                   class="dsmk-form-input" 
                                   placeholder="<?php esc_attr_e( 'https://example.com/affiliate', 'dynamic-site-maker' ); ?>"
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
