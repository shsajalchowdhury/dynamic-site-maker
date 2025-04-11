/**
 * Dynamic Site Maker Form Handler
 * 
 * Handles the AJAX form submission, validation, and multi-step navigation.
 */
(function($) {
    'use strict';

    /**
     * Update username placeholders in affiliate links
     * 
     * @param {string} username The username to display in placeholders
     */
    function updateUsernamePlaceholders(username) {
        $('.dsmk-username-placeholder').text(username || '');
    }

    // Initialize the form handler when the document is ready
    $(document).ready(function() {
        initForm();
        initFileUpload();
        initUsernameGeneration();
        
        // Initialize username placeholders with current username value
        const initialUsername = $('#dsmk-username').val();
        if (initialUsername) {
            updateUsernamePlaceholders(initialUsername);
        }
        
        // Update username placeholders when username field changes
        $('#dsmk-username').on('input change', function() {
            updateUsernamePlaceholders($(this).val());
        });
        
        // Update hidden affiliate link field when a radio button is selected
        $('input[name="affiliate_link"]').on('change', function() {
            const username = $('#dsmk-username').val();
            const baseUrl = $(this).val();
            $('#dsmk-affiliate-link').val(baseUrl + username);
        });
    });

    /**
     * Initialize the form
     */
    function initForm() {
        const $form = $('#dsmk-form');
        
        if (!$form.length) {
            return;
        }

        // Initialize multi-step form navigation
        initMultiStepForm();
        
        // Initialize file upload handling
        initFileUpload();

        // Handle form submission
        $form.on('submit', function(e) {
            e.preventDefault();
            
            // Validate form before submission
            if (validateForm($form)) {
                submitForm($form);
            }
        });
    }

    /**
     * Initialize multi-step form navigation
     */
    function initMultiStepForm() {
        // Next button click handler
        $('.dsmk-button-next').on('click', function() {
            const nextStep = $(this).data('next');
            const currentStep = $(this).closest('.dsmk-form-step').data('step');
            
            // Validate current step before proceeding
            if (validateStep(currentStep)) {
                goToStep(nextStep);
            }
        });
        
        // Previous button click handler
        $('.dsmk-button-prev').on('click', function() {
            const prevStep = $(this).data('prev');
            goToStep(prevStep);
        });
        
        // Username generation button handler
        $('#dsmk-generate-username').on('click', function() {
            generateUsername();
        });
    }

    /**
     * Navigate to a specific step
     * 
     * @param {number} stepNumber The step number to navigate to
     */
    function goToStep(stepNumber) {
        // Hide all steps
        $('.dsmk-form-step').removeClass('active');
        
        // Show the target step
        $(`.dsmk-form-step[data-step="${stepNumber}"]`).addClass('active');
        
        // Update progress indicators
        updateProgress(stepNumber);
        
        // Scroll to top of form
        $('html, body').animate({
            scrollTop: $('.dsmk-form-progress').offset().top - 50
        }, 300);
    }

    /**
     * Update progress indicators
     * 
     * @param {number} currentStep The current active step
     */
    function updateProgress(currentStep) {
        // Reset all steps
        $('.dsmk-progress-step').removeClass('active completed');
        $('.dsmk-progress-connector').removeClass('active');
        
        // Mark steps as active or completed
        for (let i = 1; i <= 4; i++) {
            const $step = $(`.dsmk-progress-step[data-step="${i}"]`);
            
            if (i < currentStep) {
                // Previous steps are completed
                $step.addClass('completed');
                $step.next('.dsmk-progress-connector').addClass('active');
            } else if (i === parseInt(currentStep)) {
                // Current step is active
                $step.addClass('active');
                
                // If not the first step, activate the connector before this step
                if (i > 1) {
                    $step.prev('.dsmk-progress-connector').addClass('active');
                }
            }
        }
    }

    /**
     * Initialize file upload handling with drag and drop
     */
    function initFileUpload() {
        const $dropArea = $('#dsmk-file-drop-area');
        const $fileInput = $('.dsmk-file-input');
        const $browseButton = $('.dsmk-browse-button');
        const $filePreview = $('.dsmk-file-preview');
        const $previewImage = $('.dsmk-preview-image');
        const $fileName = $('.dsmk-file-name');
        const $fileSize = $('.dsmk-file-size');
        const $removeFile = $('.dsmk-remove-file');
        
        // Make the browse button trigger the file input
        $browseButton.on('click', function() {
            $fileInput.trigger('click');
        });
        
        // Handle file selection
        $fileInput.on('change', function() {
            handleFiles(this.files);
        });
        
        // Prevent default behavior for drag and drop events
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            $dropArea.on(eventName, preventDefaults);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        // Add visual indicator for drag enter and leave
        $dropArea.on('dragenter dragover', function() {
            $(this).addClass('dragover');
        });
        
        $dropArea.on('dragleave drop', function() {
            $(this).removeClass('dragover');
        });
        
        // Handle file drop
        $dropArea.on('drop', function(e) {
            const dt = e.originalEvent.dataTransfer;
            const files = dt.files;
            handleFiles(files);
        });
        
        // Remove file button
        $removeFile.on('click', function() {
            clearFilePreview();
            $fileInput.val('');
        });
        
        /**
         * Handle file processing and preview
         */
        function handleFiles(files) {
            if (files.length === 0) {
                return;
            }
            
            const file = files[0];
            
            // Validate file type
            const validTypes = ['image/jpeg', 'image/png', 'image/svg+xml'];
            if (!validTypes.includes(file.type)) {
                showMessage(
                    $('.dsmk-form__messages'),
                    'Please select a valid image file (JPG, PNG, or SVG).',
                    'error'
                );
                return;
            }
            
            // Validate file size (max 5MB)
            const maxSize = 5 * 1024 * 1024; // 5MB
            if (file.size > maxSize) {
                showMessage(
                    $('.dsmk-form__messages'),
                    'The selected file is too large. Maximum size is 5MB.',
                    'error'
                );
                return;
            }
            
            // Show file preview
            const reader = new FileReader();
            reader.onload = function(e) {
                if (file.type.startsWith('image/')) {
                    $previewImage.html(`<img src="${e.target.result}" alt="${file.name}">`);
                } else {
                    $previewImage.html('<span class="dashicons dashicons-media-default"></span>');
                }
                
                $fileName.text(file.name);
                $fileSize.text(formatFileSize(file.size));
                $filePreview.addClass('active');
                
                // Hide the upload content and show the preview
                $('.dsmk-file-upload-content').hide();
            };
            
            reader.readAsDataURL(file);
        }
        
        /**
         * Clear file preview
         */
        function clearFilePreview() {
            $previewImage.empty();
            $fileName.text('');
            $fileSize.text('');
            $filePreview.removeClass('active');
            $('.dsmk-file-upload-content').show();
        }
    }

    /**
     * Validate a specific step
     * 
     * @param {number} step The step number to validate
     * @return {boolean} Whether the step is valid
     */
    function validateStep(step) {
        const $step = $(`.dsmk-form-step[data-step="${step}"]`);
        let isValid = true;
        
        // Clear previous error messages
        $('.dsmk-form__messages').empty();
        
        // Check required fields in this step
        $step.find('[required]').each(function() {
            const $field = $(this);
            
            if (!$field.val()) {
                highlightField($field);
                showMessage($('.dsmk-form__messages'), 'Please fill in all required fields.', 'error');
                isValid = false;
            } else {
                unhighlightField($field);
            }
        });
        
        // If step 1 (name and email), validate format
        if (step === 1) {
            const $nameField = $step.find('#dsmk-name');
            const $emailField = $step.find('#dsmk-email');
            
            // Always validate name format regardless of whether it's required
            if ($nameField.val()) {
                // Check if name contains only alphanumeric characters and spaces
                const nameRegex = /^[a-zA-Z0-9\s]+$/;
                if (!nameRegex.test($nameField.val())) {
                    highlightField($nameField);
                    showMessage($('.dsmk-form__messages'), 'Please enter a valid name (only letters, numbers, and spaces allowed).', 'error');
                    isValid = false;
                } else if ($nameField.val().length < 2) {
                    highlightField($nameField);
                    showMessage($('.dsmk-form__messages'), 'Please enter a valid name (at least 2 characters).', 'error');
                    isValid = false;
                }
            }
            
            // Always validate email format regardless of whether it's required
            if ($emailField.val()) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test($emailField.val())) {
                    highlightField($emailField);
                    showMessage($('.dsmk-form__messages'), 'Please enter a valid email address.', 'error');
                    isValid = false;
                }
            }
        }
        
        // If step 2 (logo), validate file if one is selected (optional)
        if (step === 2) {
            const $fileInput = $step.find('.dsmk-file-input');
            if ($fileInput[0].files.length > 0) {
                // Validate file type and size if a file is selected
                const file = $fileInput[0].files[0];
                const validTypes = ['image/jpeg', 'image/png', 'image/svg+xml'];
                const maxSize = 1024 * 1024; // 1MB
                
                if (!validTypes.includes(file.type)) {
                    highlightField($fileInput.closest('.dsmk-file-upload-area'));
                    showMessage($('.dsmk-form__messages'), 'Please upload a valid image file (JPG, PNG, or SVG).', 'error');
                    isValid = false;
                } else if (file.size > maxSize) {
                    highlightField($fileInput.closest('.dsmk-file-upload-area'));
                    showMessage($('.dsmk-form__messages'), 'File size exceeds 1MB. Please upload a smaller file.', 'error');
                    isValid = false;
                }
            }
            // Logo is now optional, so we don't need to validate if it's empty
        }
        
        // If step 3 (username), validate username format
        if (step === 3) {
            const $usernameField = $step.find('#dsmk-username');
            if ($usernameField.val()) {
                // Check if username contains only alphanumeric characters, underscores, and hyphens
                const usernameRegex = /^[a-zA-Z0-9_-]+$/;
                if (!usernameRegex.test($usernameField.val())) {
                    highlightField($usernameField);
                    showMessage($('.dsmk-form__messages'), 'Please enter a valid username (only letters, numbers, underscores, and hyphens allowed).', 'error');
                    isValid = false;
                } else if ($usernameField.val().length < 2) {
                    highlightField($usernameField);
                    showMessage($('.dsmk-form__messages'), 'Please enter a valid username (at least 2 characters).', 'error');
                    isValid = false;
                }
            }
        }
        
        // If step 4 (affiliate link), validate radio selection
        if (step === 4) {
            const $radioSelected = $step.find('input[name="affiliate_link"]:checked');
            if (!$radioSelected.length) {
                showMessage($('.dsmk-form__messages'), 'Please select an affiliate link option.', 'error');
                isValid = false;
            } else {
                // Make sure the hidden field has the full URL with username
                const username = $('#dsmk-username').val();
                const baseUrl = $radioSelected.val();
                $('#dsmk-affiliate-link').val(baseUrl + username);
            }
        }
        
        // Clear error message if valid
        if (isValid) {
            $('.dsmk-form__messages').empty();
        }
        
        return isValid;
    }

    /**
     * Highlight a field with error
     * 
     * @param {jQuery} $field The field to highlight
     */
    function highlightField($field) {
        $field.addClass('dsmk-field-error').one('focus change', function() {
            $(this).removeClass('dsmk-field-error');
        });
    }

    /**
     * Remove error highlight from a field
     * 
     * @param {jQuery} $field The field to unhighlight
     */
    function unhighlightField($field) {
        $field.removeClass('dsmk-field-error');
    }

    /**
     * Validate the entire form
     * 
     * @param {jQuery} $form The form jQuery object
     * @return {boolean} Whether the form is valid
     */
    function validateForm($form) {
        let isValid = true;
        
        // Validate each step
        for (let i = 1; i <= 4; i++) {
            if (!validateStep(i)) {
                isValid = false;
                break;
            }
        }
        
        // Show general error message if form is invalid
        if (!isValid && !$('.dsmk-form__messages').children().length) {
            showMessage($('.dsmk-form__messages'), 'Please fill in all required fields correctly.', 'error');
            
            // Scroll to error message
            $('html, body').animate({
                scrollTop: $('.dsmk-form__messages').offset().top - 50
            }, 300);
        }
        
        return isValid;
    }

    /**
     * Validate URL format
     * 
     * @param {string} url The URL to validate
     * @return {boolean} Whether the URL is valid
     */
    function isValidUrl(url) {
        try {
            new URL(url);
            return true;
        } catch (e) {
            return false;
        }
    }

    /**
     * Format file size in human-readable format
     * 
     * @param {number} bytes The file size in bytes
     * @return {string} Formatted file size
     */
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    /**
     * Submit the form via AJAX
     * 
     * @param {jQuery} $form The form jQuery object
     */
    function submitForm($form) {
        // Show loading overlay
        $('.dsmk-form-loading').css('display', 'flex');

        // Create FormData object
        const formData = new FormData($form[0]);
        
        // Add AJAX action and nonce
        formData.append('action', 'dsmk_submit_form');
        formData.append('nonce', dsmk_ajax.nonce);
        
        // Ensure username is included
        if (!formData.has('username')) {
            formData.append('username', $('#dsmk-username').val());
        }

        // Send AJAX request
        $.ajax({
            url: dsmk_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                handleResponse(response, $form);
            },
            error: function(xhr, status, error) {
                // Hide loading overlay
                $('.dsmk-form-loading').hide();
                
                showMessage(
                    $('.dsmk-form__messages'), 
                    'An unexpected error occurred. Please try again.',
                    'error'
                );
                
                console.error('AJAX Error:', error);
            }
        });
    }

    /**
     * Handle AJAX response
     * 
     * @param {Object} response The AJAX response
     * @param {jQuery} $form The form jQuery object
     */
    function handleResponse(response, $form) {
        if (response.success) {
            // Hide form and show success message
            $form.hide();
            $('.dsmk-form-loading').hide();
            $('.dsmk-form-success').show();
            
            // Set up the Visit Site button with the correct URL
            const $visitButton = $('#dsmk-visit-site');
            if (response.data.url) {
                $visitButton.attr('href', response.data.url);
                
                // Scroll to the success message
                $('html, body').animate({
                    scrollTop: $('.dsmk-form-success').offset().top - 50
                }, 300);
                
                // Add a subtle highlight effect to the button to draw attention
                setTimeout(function() {
                    $visitButton.addClass('dsmk-button-highlight');
                    setTimeout(function() {
                        $visitButton.removeClass('dsmk-button-highlight');
                    }, 1500);
                }, 500);
            }
        } else {
            // Hide loading overlay
            $('.dsmk-form-loading').hide();
            
            // Show error message
            showMessage(
                $('.dsmk-form__messages'),
                response.data.message || 'An error occurred. Please try again.',
                'error'
            );
            
            // Scroll to error message
            $('html, body').animate({
                scrollTop: $('.dsmk-form__messages').offset().top - 50
            }, 300);
        }
    }

    /**
     * Show a message in the form
     * 
     * @param {jQuery} $container The container to show the message in
     * @param {string} message The message to show
     * @param {string} type The message type (error, success, info, warning)
     */
    function showMessage($container, message, type) {
        $container.html(`
            <div class="dsmk-notice dsmk-notice--${type}">
                <div class="dsmk-notice-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24">
                        ${getIconPath(type)}
                    </svg>
                </div>
                <div class="dsmk-notice-content">
                    <p>${message}</p>
                </div>
            </div>
        `);
    }

    /**
     * Generate a username based on the name or a random string
     */
    function generateUsername() {
        const $usernameField = $('#dsmk-username');
        const $nameField = $('#dsmk-name');
        const $emailField = $('#dsmk-email');
        const $messageContainer = $('.dsmk-form__messages');
        const $generateButton = $('#dsmk-generate-username');
        
        // Clear previous messages
        $messageContainer.empty();
        
        // Check if name and email are filled
        if (!$nameField.val().trim() || !$emailField.val().trim()) {
            showMessage(
                $messageContainer,
                'Please fill in your name and email before generating a username.',
                'warning'
            );
            return;
        }
        
        // Show loading indicator
        $usernameField.attr('disabled', true);
        $generateButton.attr('disabled', true).text('Generating...');
        
        // Get current username or generate a suggested one
        let suggestedUsername = $usernameField.val().trim();
        
        // If no username is entered, create a basic one from the name
        // This is just a fallback - the server will generate a better one
        if (!suggestedUsername) {
            const name = $nameField.val().trim();
            suggestedUsername = name.toLowerCase()
                .replace(/[^a-z0-9]/g, '')
                .substring(0, 12);
            
            if (suggestedUsername.length < 3) {
                suggestedUsername = 'user' + Math.floor(Math.random() * 1000);
            }
        }
        
        // Make AJAX call to generate username via API
        $.ajax({
            url: dsmk_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'dsmk_generate_username',
                nonce: dsmk_ajax.nonce,
                username: suggestedUsername,
                name: $nameField.val().trim(),
                email: $emailField.val().trim()
            },
            success: function(response) {
                if (response.success) {
                    // Clear the field first to ensure the change event triggers
                    $usernameField.val('');
                    
                    // Set the generated username with a slight delay to ensure UI updates
                    setTimeout(function() {
                        $usernameField.val(response.data.username);
                        $usernameField.trigger('change'); // Trigger change event to update any listeners
                        
                        // Highlight the username field to draw attention to it
                        $usernameField.addClass('dsmk-field-highlight');
                        setTimeout(function() {
                            $usernameField.removeClass('dsmk-field-highlight');
                        }, 1500);
                        
                        // Update username placeholders in affiliate links
                        updateUsernamePlaceholders(response.data.username);
                        
                        // Show success message
                        showMessage(
                            $messageContainer,
                            response.data.message || 'Username generated! You can edit it if you prefer a different one.',
                            'success'
                        );
                    }, 100);
                } else {
                    // Show error message
                    showMessage(
                        $messageContainer,
                        response.data.message || 'Failed to generate username. Please try again.',
                        'error'
                    );
                }
            },
            error: function() {
                // Show error message
                showMessage(
                    $messageContainer,
                    'An error occurred while generating the username. Please try again.',
                    'error'
                );
            },
            complete: function() {
                // Reset button state
                $('#dsmk-generate-username').attr('disabled', false).text('Generate Username');
                $usernameField.attr('disabled', false).focus();
            }
        });
    }
    
    /**
     * Get SVG icon path based on message type
     * 
     * @param {string} type The message type
     * @return {string} The SVG path data
     */
    function getIconPath(type) {
        switch (type) {
            case 'error':
                return '<path fill="currentColor" d="M12,2C17.53,2 22,6.47 22,12C22,17.53 17.53,22 12,22C6.47,22 2,17.53 2,12C2,6.47 6.47,2 12,2M15.59,7L12,10.59L8.41,7L7,8.41L10.59,12L7,15.59L8.41,17L12,13.41L15.59,17L17,15.59L13.41,12L17,8.41L15.59,7Z" />';
            case 'success':
                return '<path fill="currentColor" d="M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M11,16.5L18,9.5L16.59,8.09L11,13.67L7.91,10.59L6.5,12L11,16.5Z" />';
            case 'warning':
                return '<path fill="currentColor" d="M12,2L1,21H23M12,6L19.53,19H4.47M11,10V14H13V10M11,16V18H13V16" />';
            case 'info':
            default:
                return '<path fill="currentColor" d="M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M13,7H11V9H13V7M13,11H11V17H13V11Z" />';
        }
    }

})(jQuery);
