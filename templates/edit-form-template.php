<?php
/**
 * Edit Form Template
 *
 * @package Dynamic_Site_Maker
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Get form handler instance
$form_handler = new DSMK_Form_Handler();

// Initialize variables
$email = '';
$page_id = 0;
$page_data = array();
$search_submitted = false;
$search_error = '';

// Check if search form was submitted
if ( isset( $_POST['dsmk_search_email'] ) && ! empty( $_POST['dsmk_search_email'] ) ) {
    $search_submitted = true;
    $email = sanitize_email( wp_unslash( $_POST['dsmk_search_email'] ) );
    
    // Find page by email
    $page_id = $form_handler->find_page_by_email( $email );
    
    if ( $page_id ) {
        // Get page data
        $page_data = array(
            'name' => get_post_meta( $page_id, '_dsmk_name', true ),
            'email' => get_post_meta( $page_id, '_dsmk_email', true ),
            'logo_id' => get_post_meta( $page_id, '_dsmk_logo_id', true ),
            'affiliate_link' => get_post_meta( $page_id, '_dsmk_affiliate_link', true ),
        );
    } else {
        $search_error = __( 'No site found for this email address.', 'dynamic-site-maker' );
    }
}

// Get customization settings
$page_title = __( 'Edit Your Site', 'dynamic-site-maker' );
$page_description = __( 'Update your logo and affiliate link.', 'dynamic-site-maker' );
$header_bg_color = get_option('dsmk_shortcode_header_bg_color', '#2196f3');
$button_color = get_option('dsmk_shortcode_button_color', '#2196f3');
$text_color = get_option('dsmk_shortcode_text_color', '#333333');
$logo_label = get_option('dsmk_shortcode_logo_label', 'Your Logo');
$link_label = get_option('dsmk_shortcode_link_label', 'Affiliate Link');
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
.dsmk-form-step h3,
.dsmk-form-label {
    color: {$text_color};
}

.dsmk-success-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-top: 20px;
}

.dsmk-button-secondary {
    background-color: #f5f5f5;
    color: #333 !important;
}

.dsmk-button-secondary:hover {
    background-color: #e0e0e0;
}

.dsmk-form-success {
    display: none;
    text-align: center;
    padding: 30px;
}

.dsmk-form-success.active {
    display: block;
}

.dsmk-success-icon {
    margin-bottom: 20px;
    color: #4CAF50;
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
    <div class="dsmk-form-wrapper">
        <!-- Form Header with Logo and Title -->
        <div class="dsmk-form-header">
            <div class="dsmk-form-logo">
                <svg viewBox="0 0 24 24" width="48" height="48">
                    <path fill="#ffffff" d="M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4M11,16.5L6.5,12L7.91,10.59L11,13.67L16.59,8.09L18,9.5L11,16.5Z" />
                </svg>
            </div>
            <h2><?php echo esc_html($page_title); ?></h2>
            <p><?php echo esc_html($page_description); ?></p>
        </div>
        
        <!-- Form Messages -->
        <div class="dsmk-form__messages">
            <?php if ( $search_submitted && !empty($search_error) ) : ?>
                <div class="dsmk-notice dsmk-notice--error">
                    <div class="dsmk-notice-icon">
                        <span class="dashicons dashicons-warning"></span>
                    </div>
                    <div class="dsmk-notice-content">
                        <p><?php echo esc_html($search_error); ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ( !$page_id ) : ?>
            <!-- Search Form -->
            <form id="dsmk-search-form" class="dsmk-form" method="post">
                <div class="dsmk-form-step active">
                    <h3><?php esc_html_e('Find Your Site', 'dynamic-site-maker'); ?></h3>
                    
                    <div class="dsmk-form-field">
                        <label for="dsmk-search-email" class="dsmk-form-label">
                            <?php esc_html_e('Email Address', 'dynamic-site-maker'); ?>
                            <span class="dsmk-form-required">*</span>
                        </label>
                        <div class="dsmk-input-wrapper">
                            <input type="email" 
                                   id="dsmk-search-email" 
                                   name="dsmk_search_email" 
                                   class="dsmk-form-input" 
                                   placeholder="<?php esc_attr_e('Enter the email you used to create your site', 'dynamic-site-maker'); ?>"
                                   value="<?php echo esc_attr($email); ?>"
                                   required>
                        </div>
                        <p class="dsmk-form-description">
                            <?php esc_html_e('Enter the email address you used when creating your site.', 'dynamic-site-maker'); ?>
                        </p>
                    </div>
                    
                    <div class="dsmk-form-actions">
                        <button type="submit" class="dsmk-button">
                            <?php esc_html_e('Find My Site', 'dynamic-site-maker'); ?>
                            <span class="dsmk-button-icon-right">
                                <svg viewBox="0 0 24 24" width="16" height="16">
                                    <path fill="currentColor" d="M9.5,3A6.5,6.5 0 0,1 16,9.5C16,11.11 15.41,12.59 14.44,13.73L14.71,14H15.5L20.5,19L19,20.5L14,15.5V14.71L13.73,14.44C12.59,15.41 11.11,16 9.5,16A6.5,6.5 0 0,1 3,9.5A6.5,6.5 0 0,1 9.5,3M9.5,5C7,5 5,7 5,9.5C5,12 7,14 9.5,14C12,14 14,12 14,9.5C14,7 12,5 9.5,5Z" />
                                </svg>
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        <?php else : ?>
            <!-- Edit Form -->
            <form id="dsmk-edit-form" class="dsmk-form" method="post" enctype="multipart/form-data">
                <div class="dsmk-form-step active">
                    <h3><?php esc_html_e('Update Your Site', 'dynamic-site-maker'); ?></h3>
                    
                    <div class="dsmk-form-field">
                        <label class="dsmk-form-label">
                            <?php esc_html_e('Affiliate Name', 'dynamic-site-maker'); ?>
                        </label>
                        <div class="dsmk-input-wrapper">
                            <input type="text" 
                                   id="dsmk-name" 
                                   name="name" 
                                   class="dsmk-form-input" 
                                   value="<?php echo esc_attr($page_data['name']); ?>"
                                   readonly>
                        </div>
                    </div>
                    
                    <div class="dsmk-form-field">
                        <label class="dsmk-form-label">
                            <?php esc_html_e('Email Address', 'dynamic-site-maker'); ?>
                        </label>
                        <div class="dsmk-input-wrapper">
                            <input type="email" 
                                   id="dsmk-email" 
                                   name="email" 
                                   class="dsmk-form-input" 
                                   value="<?php echo esc_attr($page_data['email']); ?>"
                                   readonly>
                        </div>
                    </div>
                    
                    <div class="dsmk-form-field">
                        <label class="dsmk-form-label">
                            <?php echo esc_html($logo_label); ?>
                            <span class="dsmk-form-required">*</span>
                        </label>
                        
                        <div class="dsmk-file-upload">
                            <?php if ( !empty($page_data['logo_id']) ) : 
                                $logo_url = wp_get_attachment_url($page_data['logo_id']);
                            ?>
                                <div class="dsmk-current-logo">
                                    <p><?php esc_html_e('Current Logo:', 'dynamic-site-maker'); ?></p>
                                    <img src="<?php echo esc_url($logo_url); ?>" alt="<?php esc_attr_e('Current Logo', 'dynamic-site-maker'); ?>" style="max-width: 200px; max-height: 100px;">
                                </div>
                            <?php endif; ?>
                            
                            <div class="dsmk-file-upload-area" id="dsmk-file-drop-area">
                                <input type="file" 
                                       id="dsmk-logo" 
                                       name="logo" 
                                       class="dsmk-file-input" 
                                       accept="image/jpeg,image/png,image/svg+xml">
                                <div class="dsmk-file-upload-message">
                                    <div class="dsmk-file-icon">
                                        <svg viewBox="0 0 24 24" width="32" height="32">
                                            <path fill="currentColor" d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M13.5,16V19H10.5V16H8L12,12L16,16H13.5M13,9V3.5L18.5,9H13Z" />
                                        </svg>
                                    </div>
                                    <div class="dsmk-file-message">
                                        <?php esc_html_e('Drag & drop your logo here or', 'dynamic-site-maker'); ?>
                                        <button type="button" class="dsmk-browse-button">
                                            <?php esc_html_e('Browse Files', 'dynamic-site-maker'); ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="dsmk-form-description">
                            <?php esc_html_e('Upload a new logo (JPG, PNG, SVG - Max 5MB). Leave empty to keep current logo.', 'dynamic-site-maker'); ?>
                        </p>
                    </div>
                    
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
                                   value="<?php echo esc_url($page_data['affiliate_link']); ?>"
                                   required>
                        </div>
                        <p class="dsmk-form-description">
                            <?php esc_html_e('Update your affiliate link.', 'dynamic-site-maker'); ?>
                        </p>
                    </div>
                    
                    <div class="dsmk-form-actions">
                        <button type="submit" class="dsmk-button dsmk-button-submit">
                            <?php esc_html_e('Update Site', 'dynamic-site-maker'); ?>
                            <span class="dsmk-button-icon-right">
                                <svg viewBox="0 0 24 24" width="16" height="16">
                                    <path fill="currentColor" d="M21,7L9,19L3.5,13.5L4.91,12.09L9,16.17L19.59,5.59L21,7Z" />
                                </svg>
                            </span>
                        </button>
                    </div>
                </div>
                
                <?php wp_nonce_field('dsmk_form_nonce', 'dsmk_nonce'); ?>
                <input type="hidden" name="edit_mode" value="true">
                <input type="hidden" name="page_id" value="<?php echo esc_attr($page_id); ?>">
                
                <!-- Loading Overlay -->
                <div class="dsmk-form-loading">
                    <div class="dsmk-loading-spinner">
                        <div class="dsmk-spinner-inner"></div>
                    </div>
                    <p><?php esc_html_e('Updating your site...', 'dynamic-site-maker'); ?></p>
                </div>
            </form>
            
            <!-- Success Message -->
            <div class="dsmk-form-success">
                <div class="dsmk-success-icon">
                    <svg viewBox="0 0 24 24" width="64" height="64">
                        <path fill="currentColor" d="M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4M11,16.5L6.5,12L7.91,10.59L11,13.67L16.59,8.09L18,9.5L11,16.5Z" />
                    </svg>
                </div>
                <h3><?php esc_html_e('Site updated successfully!', 'dynamic-site-maker'); ?></h3>
                <p><?php esc_html_e('Your changes have been saved.', 'dynamic-site-maker'); ?></p>
                <div class="dsmk-success-actions">
                    <a href="#" id="dsmk-view-page" class="dsmk-button" target="_blank"><?php esc_html_e('View Updated Page', 'dynamic-site-maker'); ?></a>
                    <button type="button" id="dsmk-edit-more" class="dsmk-button dsmk-button-secondary"><?php esc_html_e('Make More Changes', 'dynamic-site-maker'); ?></button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // File upload handling
    const $dropArea = $('#dsmk-file-drop-area');
    const $fileInput = $('.dsmk-file-input');
    const $browseButton = $('.dsmk-browse-button');
    
    // Trigger file input when browse button is clicked
    $browseButton.on('click', function() {
        $fileInput.click();
    });
    
    // Handle file selection
    $fileInput.on('change', function() {
        const file = this.files[0];
        if (file) {
            handleFile(file);
        }
    });
    
    // Prevent default behavior for drag and drop
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        $dropArea.on(eventName, preventDefaults);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    // Highlight drop area when file is dragged over
    ['dragenter', 'dragover'].forEach(eventName => {
        $dropArea.on(eventName, highlight);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        $dropArea.on(eventName, unhighlight);
    });
    
    function highlight() {
        $dropArea.addClass('highlight');
    }
    
    function unhighlight() {
        $dropArea.removeClass('highlight');
    }
    
    // Handle dropped files
    $dropArea.on('drop', handleDrop);
    
    function handleDrop(e) {
        const dt = e.originalEvent.dataTransfer;
        const file = dt.files[0];
        handleFile(file);
    }
    
    function handleFile(file) {
        // Display file name
        const fileType = file.type;
        const validTypes = ['image/jpeg', 'image/png', 'image/svg+xml'];
        
        if (validTypes.indexOf(fileType) === -1) {
            alert('Invalid file type. Please upload JPG, PNG, or SVG.');
            return;
        }
        
        if (file.size > 5 * 1024 * 1024) {
            alert('File is too large. Maximum size is 5MB.');
            return;
        }
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            const $preview = $('<div class="dsmk-file-preview"></div>');
            const $img = $('<img>').attr('src', e.target.result).attr('alt', file.name);
            const $fileName = $('<div class="dsmk-file-name"></div>').text(file.name);
            
            // Remove any existing preview
            $('.dsmk-file-preview').remove();
            
            $preview.append($img).append($fileName);
            $dropArea.append($preview);
        };
        reader.readAsDataURL(file);
    }
    
    // Form submission
    $('#dsmk-edit-form').on('submit', function(e) {
        e.preventDefault();
        
        // Show loading overlay
        $('.dsmk-form-loading').addClass('active');
        
        // Create FormData object
        const formData = new FormData(this);
        formData.append('action', 'dsmk_submit_form');
        formData.append('nonce', dsmk_form.nonce);
        
        // Send AJAX request
        $.ajax({
            url: dsmk_form.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                // Hide loading overlay
                $('.dsmk-form-loading').removeClass('active');
                
                console.log('Response:', response); // Debug response
                
                if (response.success) {
                    // Store the page URL for later use
                    let pageUrl = '';
                    if (response.data && response.data.redirect) {
                        pageUrl = response.data.redirect;
                    } else if (response.data && response.data.url) {
                        pageUrl = response.data.url;
                    }
                    
                    console.log('Page URL:', pageUrl); // Debug URL
                    
                    // Show success message
                    $('.dsmk-form').hide();
                    $('.dsmk-form-success').addClass('active');
                    
                    // Set up the view page button
                    if (pageUrl) {
                        $('#dsmk-view-page').attr('href', pageUrl);
                    } else {
                        // If no URL is provided, use the current page ID
                        let pageId = $('input[name="page_id"]').val();
                        if (pageId) {
                            $('#dsmk-view-page').attr('href', '?page_id=' + pageId);
                        }
                    }
                    
                    // Handle "Make More Changes" button
                    $('#dsmk-edit-more').on('click', function() {
                        $('.dsmk-form-success').removeClass('active');
                        $('.dsmk-form').show();
                    });
                } else {
                    // Show error message
                    const errorMessage = response.data.message || 'An error occurred. Please try again.';
                    const $errorNotice = $(`
                        <div class="dsmk-notice dsmk-notice--error">
                            <div class="dsmk-notice-icon">
                                <span class="dashicons dashicons-warning"></span>
                            </div>
                            <div class="dsmk-notice-content">
                                <p>${errorMessage}</p>
                            </div>
                        </div>
                    `);
                    
                    $('.dsmk-form__messages').html($errorNotice);
                    
                    // Scroll to error message
                    $('html, body').animate({
                        scrollTop: $('.dsmk-form__messages').offset().top - 50
                    }, 500);
                }
            },
            error: function() {
                // Hide loading overlay
                $('.dsmk-form-loading').removeClass('active');
                
                // Show error message
                const $errorNotice = $(`
                    <div class="dsmk-notice dsmk-notice--error">
                        <div class="dsmk-notice-icon">
                            <span class="dashicons dashicons-warning"></span>
                        </div>
                        <div class="dsmk-notice-content">
                            <p>An error occurred while processing your request. Please try again.</p>
                        </div>
                    </div>
                `);
                
                $('.dsmk-form__messages').html($errorNotice);
                
                // Scroll to error message
                $('html, body').animate({
                    scrollTop: $('.dsmk-form__messages').offset().top - 50
                }, 500);
            }
        });
    });
});
</script>
