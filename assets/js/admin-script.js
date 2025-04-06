/**
 * Dynamic Site Maker - Admin Scripts
 */
(function($) {
    'use strict';

    // Initialize when DOM is ready
    $(document).ready(function() {
        initAdminUI();
    });

    /**
     * Initialize Admin UI functionality
     */
    function initAdminUI() {
        // Initialize tabs
        initTabs();
        
        // Initialize copy shortcode functionality
        initCopyShortcode();
        
        // Initialize template selection
        initTemplateSelection();
        
        // Initialize template upload
        initTemplateUpload();
        
        // Initialize color picker
        initColorPicker();
        
        // Initialize tooltips
        initTooltips();
        
        // Initialize delete page confirmation
        initDeletePageConfirmation();
        
        // Initialize update content functionality
        initUpdateContent();
    }

    /**
     * Initialize tabbed interface
     */
    function initTabs() {
        // Handle tab clicks
        $('.dsmk-nav-tab').on('click', function(e) {
            // This is handled by WordPress admin URLs, but add animation
            const tabTarget = $(this).attr('href').split('tab=')[1];
            if (tabTarget) {
                localStorage.setItem('dsmk_active_tab', tabTarget);
            }
        });
        
        // Set active tab on page load
        const activeTab = localStorage.getItem('dsmk_active_tab');
        if (activeTab) {
            $('.dsmk-nav-tab[href*="tab=' + activeTab + '"]').addClass('active');
        }
    }

    /**
     * Initialize copy shortcode functionality
     */
    function initCopyShortcode() {
        $('.dsmk-copy-button').on('click', function() {
            const $button = $(this);
            const textToCopy = $button.data('clipboard-text');
            
            // Create temporary textarea
            const $temp = $('<textarea>');
            $('body').append($temp);
            $temp.val(textToCopy).select();
            
            // Copy to clipboard
            document.execCommand('copy');
            $temp.remove();
            
            // Show success feedback
            const originalIcon = $button.find('.dashicons').attr('class');
            $button.find('.dashicons').attr('class', 'dashicons dashicons-yes');
            
            setTimeout(function() {
                $button.find('.dashicons').attr('class', originalIcon);
            }, 1500);
        });
    }

    /**
     * Initialize template selection
     */
    function initTemplateSelection() {
        // Handle template selection
        $('.dsmk-button-select').on('click', function() {
            const $button = $(this);
            const template = $button.data('template');
            
            // Show loading state
            $button.prop('disabled', true).text('Activating...');
            
            // Send AJAX request to set template
            $.ajax({
                url: dsmk_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'dsmk_set_template',
                    template: template,
                    nonce: dsmk_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Update UI to show active template
                        $('.dsmk-template-item').removeClass('dsmk-template-active');
                        $button.closest('.dsmk-template-item').addClass('dsmk-template-active');
                        
                        // Update button text
                        $('.dsmk-button-select').text('Select');
                        $button.text('Active');
                        
                        // Show success message
                        showNotice('Template activated successfully!', 'success');
                    } else {
                        showNotice(response.data.message || 'Failed to activate template.', 'error');
                        $button.prop('disabled', false).text('Select');
                    }
                },
                error: function() {
                    showNotice('An error occurred while activating the template.', 'error');
                    $button.prop('disabled', false).text('Select');
                }
            });
        });
        
        // Handle template preview
        $('.dsmk-button-preview').on('click', function() {
            const template = $(this).data('template');
            
            // Open preview in modal or new window
            openTemplatePreview(template);
        });
    }

    /**
     * Open template preview
     * 
     * @param {string} template Template ID
     */
    function openTemplatePreview(template) {
        // Create modal for preview
        const $modal = $(`
            <div class="dsmk-modal">
                <div class="dsmk-modal-content">
                    <span class="dsmk-modal-close">&times;</span>
                    <h3>Template Preview</h3>
                    <div class="dsmk-modal-body">
                        <iframe src="${dsmk_admin.preview_url}?template=${template}" width="100%" height="600"></iframe>
                    </div>
                </div>
            </div>
        `);
        
        // Add modal to body
        $('body').append($modal);
        
        // Show modal with animation
        setTimeout(function() {
            $modal.addClass('dsmk-modal-visible');
        }, 10);
        
        // Handle close button click
        $modal.find('.dsmk-modal-close').on('click', function() {
            closeModal($modal);
        });
        
        // Handle click outside modal
        $modal.on('click', function(e) {
            if ($(e.target).hasClass('dsmk-modal')) {
                closeModal($modal);
            }
        });
    }

    /**
     * Close modal with animation
     * 
     * @param {jQuery} $modal Modal element
     */
    function closeModal($modal) {
        $modal.removeClass('dsmk-modal-visible');
        
        setTimeout(function() {
            $modal.remove();
        }, 300);
    }

    /**
     * Initialize template upload
     */
    function initTemplateUpload() {
        $('.dsmk-upload-button').on('click', function() {
            // Create file input element
            const $fileInput = $('<input type="file" accept=".json" style="display:none">');
            $('body').append($fileInput);
            
            // Trigger click to open file dialog
            $fileInput.trigger('click');
            
            // Handle file selection
            $fileInput.on('change', function() {
                if (!this.files || !this.files[0]) {
                    $fileInput.remove();
                    return;
                }
                
                const file = this.files[0];
                
                // Validate file type
                if (!file.name.endsWith('.json')) {
                    showNotice('Please select a valid template JSON file.', 'error');
                    $fileInput.remove();
                    return;
                }
                
                // Read file contents
                const reader = new FileReader();
                reader.onload = function(e) {
                    try {
                        const templateData = JSON.parse(e.target.result);
                        uploadTemplate(templateData, file.name);
                    } catch (error) {
                        showNotice('Invalid JSON file. Please upload a valid Elementor template.', 'error');
                    }
                    
                    $fileInput.remove();
                };
                
                reader.readAsText(file);
            });
        });
    }

    /**
     * Upload custom template
     * 
     * @param {Object} templateData Template JSON data
     * @param {string} fileName Original file name
     */
    function uploadTemplate(templateData, fileName) {
        // Show loading state
        showNotice('Uploading template...', 'info');
        
        // Send AJAX request to upload template
        $.ajax({
            url: dsmk_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'dsmk_upload_template',
                template_data: JSON.stringify(templateData),
                file_name: fileName,
                nonce: dsmk_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice('Template uploaded successfully! Refreshing page...', 'success');
                    
                    // Refresh page after short delay
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                } else {
                    showNotice(response.data.message || 'Failed to upload template.', 'error');
                }
            },
            error: function() {
                showNotice('An error occurred while uploading the template.', 'error');
            }
        });
    }

    /**
     * Initialize color picker
     */
    function initColorPicker() {
        // Make sure jQuery and wpColorPicker are available
        if (typeof jQuery !== 'undefined' && typeof jQuery.fn.wpColorPicker !== 'undefined') {
            jQuery(document).ready(function($) {
                $('.color-picker').wpColorPicker();
            });
        } else {
            // Fallback if wpColorPicker is not available immediately
            jQuery(document).ready(function($) {
                setTimeout(function() {
                    if ($.fn.wpColorPicker) {
                        $('.color-picker').wpColorPicker();
                    }
                }, 100);
            });
        }
    }

    /**
     * Initialize tooltips
     */
    function initTooltips() {
        $('.dsmk-tooltip').each(function() {
            const $this = $(this);
            const tooltipText = $this.data('tooltip');
            
            // Create tooltip element
            const $tooltip = $(`<span class="dsmk-tooltip-text">${tooltipText}</span>`);
            $this.append($tooltip);
            
            // Show/hide tooltip on hover
            $this.on('mouseenter', function() {
                $tooltip.addClass('dsmk-tooltip-visible');
            }).on('mouseleave', function() {
                $tooltip.removeClass('dsmk-tooltip-visible');
            });
        });
    }

    /**
     * Initialize delete page confirmation
     */
    function initDeletePageConfirmation() {
        $('.dsmk-delete-page').on('click', function(e) {
            e.preventDefault();
            
            const $link = $(this);
            const pageTitle = $link.data('page-title');
            
            if (confirm('Are you sure you want to delete "' + pageTitle + '"? This action cannot be undone.')) {
                window.location.href = $link.attr('href');
            }
        });
    }

    /**
     * Initialize update content functionality
     */
    function initUpdateContent() {
        // Handle "Update Content" button click
        $('.dsmk-update-content').on('click', function(e) {
            e.preventDefault();
            
            // Get data from data attributes
            const postId = $(this).data('post-id');
            const name = $(this).data('name');
            const email = $(this).data('email');
            const link = $(this).data('link');
            
            // Populate the form fields
            $('#dsmk-update-post-id').val(postId);
            $('#dsmk-update-name').val(name);
            $('#dsmk-update-email').val(email);
            $('#dsmk-update-affiliate-link').val(link);
            
            // Show the modal
            $('#dsmk-update-content-modal').css('display', 'block');
        });
        
        // Handle modal close button
        $('.dsmk-modal-close, .dsmk-modal-cancel').on('click', function() {
            $('#dsmk-update-content-modal').css('display', 'none');
        });
        
        // Close modal when clicking outside of it
        $(window).on('click', function(e) {
            if ($(e.target).is('.dsmk-modal')) {
                $('#dsmk-update-content-modal').css('display', 'none');
            }
        });
        
        // Handle form submission
        $('#dsmk-update-content-form').on('submit', function(e) {
            e.preventDefault();
            
            // Create FormData object to handle file uploads
            const formData = new FormData(this);
            formData.append('action', 'dsmk_update_content');
            
            // Show loading state
            const $submitButton = $(this).find('button[type="submit"]');
            const originalButtonText = $submitButton.text();
            $submitButton.text('Updating...').prop('disabled', true);
            
            // Send AJAX request
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        showNotice(response.data.message, 'success');
                        
                        // Close the modal
                        $('#dsmk-update-content-modal').css('display', 'none');
                        
                        // Reload the page after a short delay to show updated content
                        setTimeout(function() {
                            location.reload();
                        }, 3000); // Increased delay to ensure notice is visible
                    } else {
                        // Show error message
                        showNotice(response.data.message, 'error');
                        
                        // Reset button state
                        $submitButton.text(originalButtonText).prop('disabled', false);
                    }
                },
                error: function() {
                    // Show generic error message
                    showNotice('An error occurred while updating content.', 'error');
                    
                    // Reset button state
                    $submitButton.text(originalButtonText).prop('disabled', false);
                }
            });
        });
    }

    /**
     * Show admin notice
     * 
     * @param {string} message Notice message
     * @param {string} type Notice type (success, error, warning, info)
     */
    function showNotice(message, type) {
        // Remove existing notices
        $('.dsmk-admin-notice').remove();
        
        // Create notice element
        const $notice = $(`
            <div class="dsmk-admin-notice dsmk-admin-notice-${type}">
                <p>${message}</p>
                <button type="button" class="dsmk-notice-dismiss">
                    <span class="dashicons dashicons-dismiss"></span>
                </button>
            </div>
        `);
        
        // Add notice to page
        $('.dsmk-content').prepend($notice);
        
        // Show notice with animation
        setTimeout(function() {
            $notice.addClass('dsmk-admin-notice-visible');
        }, 10);
        
        // Handle dismiss button click
        $notice.find('.dsmk-notice-dismiss').on('click', function() {
            $notice.removeClass('dsmk-admin-notice-visible');
            
            setTimeout(function() {
                $notice.remove();
            }, 300);
        });
        
        // Auto-dismiss after delay (for success/info)
        if (type === 'success' || type === 'info') {
            setTimeout(function() {
                $notice.removeClass('dsmk-admin-notice-visible');
                
                setTimeout(function() {
                    $notice.remove();
                }, 300);
            }, 5000);
        }
    }

})(jQuery);
