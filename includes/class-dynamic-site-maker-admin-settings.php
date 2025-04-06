<?php
/**
 * Admin Settings Class
 *
 * @package Dynamic_Site_Maker
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Class DSMK_Admin_Settings
 *
 * Handles the plugin's admin settings page.
 */
class DSMK_Admin_Settings {

    /**
     * Constructor
     */
    public function __construct() {
        // Add admin menu
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        
        // Register settings
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        
        // Handle page deletion
        add_action( 'admin_init', array( $this, 'handle_page_deletion' ) );
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __( 'Dynamic Site Maker', 'dynamic-site-maker' ),
            __( 'Dynamic Site Maker', 'dynamic-site-maker' ),
            'manage_options',
            'dynamic-site-maker',
            array( $this, 'display_admin_page' ),
            'dashicons-admin-site-alt3',
            30
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        // Explicitly define sanitization callbacks
        $text_sanitize = 'sanitize_text_field';
        $int_sanitize = 'absint';
        
        register_setting(
            'dsmk_settings',
            'dsmk_page_title_format',
            array(
                'type'              => 'string',
                'description'       => __( 'Page title format', 'dynamic-site-maker' ),
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '{name}\'s Landing Page',
            )
        );

        register_setting(
            'dsmk_settings',
            'dsmk_redirect_delay',
            array(
                'type'              => 'integer',
                'description'       => __( 'Redirect delay in seconds', 'dynamic-site-maker' ),
                'sanitize_callback' => 'absint',
                'default'           => 2,
            )
        );

        register_setting(
            'dsmk_settings',
            'dsmk_form_success_message',
            array(
                'type'              => 'string',
                'description'       => __( 'Form success message', 'dynamic-site-maker' ),
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => __( 'Landing page created successfully! Redirecting...', 'dynamic-site-maker' ),
            )
        );
        
        register_setting(
            'dsmk_settings',
            'dsmk_elementor_template_id',
            array(
                'type'              => 'integer',
                'description'       => __( 'Elementor Template ID', 'dynamic-site-maker' ),
                'sanitize_callback' => 'absint',
                'default'           => 0,
            )
        );

        add_settings_section(
            'dsmk_general_settings',
            __( 'General Settings', 'dynamic-site-maker' ),
            array( $this, 'display_section_info' ),
            'dsmk_settings'
        );

        add_settings_field(
            'dsmk_page_title_format',
            __( 'Page Title Format', 'dynamic-site-maker' ),
            array( $this, 'page_title_format_callback' ),
            'dsmk_settings',
            'dsmk_general_settings'
        );

        add_settings_field(
            'dsmk_redirect_delay',
            __( 'Redirect Delay (seconds)', 'dynamic-site-maker' ),
            array( $this, 'redirect_delay_callback' ),
            'dsmk_settings',
            'dsmk_general_settings'
        );

        add_settings_field(
            'dsmk_form_success_message',
            __( 'Form Success Message', 'dynamic-site-maker' ),
            array( $this, 'form_success_message_callback' ),
            'dsmk_settings',
            'dsmk_general_settings'
        );
        
        add_settings_field(
            'dsmk_elementor_template_id',
            __( 'Elementor Template', 'dynamic-site-maker' ),
            array( $this, 'elementor_template_callback' ),
            'dsmk_settings',
            'dsmk_general_settings'
        );
        
        register_setting(
            'dsmk_customization_settings',
            'dsmk_shortcode_page_title',
            array(
                'type'              => 'string',
                'description'       => __( 'Shortcode page title', 'dynamic-site-maker' ),
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => 'Created Website',
            )
        );

        register_setting(
            'dsmk_customization_settings',
            'dsmk_shortcode_page_description',
            array(
                'type'              => 'string',
                'description'       => __( 'Shortcode page description', 'dynamic-site-maker' ),
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => 'Fill out the form below to generate your custom site.',
            )
        );

        register_setting(
            'dsmk_customization_settings',
            'dsmk_shortcode_header_bg_color',
            array(
                'type'              => 'string',
                'description'       => __( 'Header background color', 'dynamic-site-maker' ),
                'sanitize_callback' => 'sanitize_hex_color',
                'default'           => '#2196f3',
            )
        );

        register_setting(
            'dsmk_customization_settings',
            'dsmk_shortcode_button_color',
            array(
                'type'              => 'string',
                'description'       => __( 'Button color', 'dynamic-site-maker' ),
                'sanitize_callback' => 'sanitize_hex_color',
                'default'           => '#2196f3',
            )
        );

        register_setting(
            'dsmk_customization_settings',
            'dsmk_shortcode_text_color',
            array(
                'type'              => 'string',
                'description'       => __( 'Text color', 'dynamic-site-maker' ),
                'sanitize_callback' => 'sanitize_hex_color',
                'default'           => '#333333',
            )
        );

        register_setting(
            'dsmk_customization_settings',
            'dsmk_shortcode_step1_title',
            array(
                'type'              => 'string',
                'description'       => __( 'Step 1 title', 'dynamic-site-maker' ),
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => 'Your Information',
            )
        );

        register_setting(
            'dsmk_customization_settings',
            'dsmk_shortcode_step2_title',
            array(
                'type'              => 'string',
                'description'       => __( 'Step 2 title', 'dynamic-site-maker' ),
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => 'Upload Your Logo',
            )
        );

        register_setting(
            'dsmk_customization_settings',
            'dsmk_shortcode_step3_title',
            array(
                'type'              => 'string',
                'description'       => __( 'Step 3 title', 'dynamic-site-maker' ),
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => 'Add Your Links',
            )
        );

        register_setting(
            'dsmk_customization_settings',
            'dsmk_shortcode_name_label',
            array(
                'type'              => 'string',
                'description'       => __( 'Name field label', 'dynamic-site-maker' ),
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => 'Affiliate Name',
            )
        );

        register_setting(
            'dsmk_customization_settings',
            'dsmk_shortcode_email_label',
            array(
                'type'              => 'string',
                'description'       => __( 'Email field label', 'dynamic-site-maker' ),
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => 'Email Address',
            )
        );

        register_setting(
            'dsmk_customization_settings',
            'dsmk_shortcode_logo_label',
            array(
                'type'              => 'string',
                'description'       => __( 'Logo field label', 'dynamic-site-maker' ),
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => 'Your Logo',
            )
        );

        register_setting(
            'dsmk_customization_settings',
            'dsmk_shortcode_link_label',
            array(
                'type'              => 'string',
                'description'       => __( 'Link field label', 'dynamic-site-maker' ),
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => 'Affiliate Link',
            )
        );

        register_setting(
            'dsmk_customization_settings',
            'dsmk_shortcode_step1_label',
            array(
                'type'              => 'string',
                'description'       => __( 'Step 1 label', 'dynamic-site-maker' ),
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => 'Your Info',
            )
        );

        register_setting(
            'dsmk_customization_settings',
            'dsmk_shortcode_step2_label',
            array(
                'type'              => 'string',
                'description'       => __( 'Step 2 label', 'dynamic-site-maker' ),
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => 'Logo',
            )
        );

        register_setting(
            'dsmk_customization_settings',
            'dsmk_shortcode_step3_label',
            array(
                'type'              => 'string',
                'description'       => __( 'Step 3 label', 'dynamic-site-maker' ),
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => 'Links',
            )
        );

        register_setting(
            'dsmk_customization_settings',
            'dsmk_shortcode_name_placeholder',
            array(
                'type'              => 'string',
                'description'       => __( 'Name field placeholder', 'dynamic-site-maker' ),
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => 'Enter Affiliate Name',
            )
        );

        register_setting(
            'dsmk_customization_settings',
            'dsmk_shortcode_email_placeholder',
            array(
                'type'              => 'string',
                'description'       => __( 'Email field placeholder', 'dynamic-site-maker' ),
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => 'Enter Email Address',
            )
        );

        register_setting(
            'dsmk_customization_settings',
            'dsmk_shortcode_link_placeholder',
            array(
                'type'              => 'string',
                'description'       => __( 'Link field placeholder', 'dynamic-site-maker' ),
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => 'https://example.com/affiliate',
            )
        );

        add_settings_section(
            'dsmk_customization_settings_section',
            __( 'Shortcode Page Customization', 'dynamic-site-maker' ),
            array( $this, 'display_section_info' ),
            'dsmk_customization_settings'
        );

        add_settings_section(
            'dsmk_customization_colors_section',
            __( 'Color Settings', 'dynamic-site-maker' ),
            array( $this, 'display_section_info' ),
            'dsmk_customization_settings'
        );

        add_settings_section(
            'dsmk_customization_fields_section',
            __( 'Field Labels', 'dynamic-site-maker' ),
            array( $this, 'display_section_info' ),
            'dsmk_customization_settings'
        );

        add_settings_section(
            'dsmk_customization_steps_section',
            __( 'Step Labels', 'dynamic-site-maker' ),
            array( $this, 'display_section_info' ),
            'dsmk_customization_settings'
        );

        add_settings_section(
            'dsmk_customization_placeholders_section',
            __( 'Placeholder Text', 'dynamic-site-maker' ),
            array( $this, 'display_section_info' ),
            'dsmk_customization_settings'
        );

        add_settings_field(
            'dsmk_shortcode_page_title',
            __( 'Shortcode Page Title', 'dynamic-site-maker' ),
            array( $this, 'shortcode_page_title_callback' ),
            'dsmk_customization_settings',
            'dsmk_customization_settings_section'
        );

        add_settings_field(
            'dsmk_shortcode_page_description',
            __( 'Shortcode Page Description', 'dynamic-site-maker' ),
            array( $this, 'shortcode_page_description_callback' ),
            'dsmk_customization_settings',
            'dsmk_customization_settings_section'
        );

        add_settings_field(
            'dsmk_shortcode_header_bg_color',
            __( 'Header Background Color', 'dynamic-site-maker' ),
            array( $this, 'header_bg_color_callback' ),
            'dsmk_customization_settings',
            'dsmk_customization_colors_section'
        );

        add_settings_field(
            'dsmk_shortcode_button_color',
            __( 'Button Color', 'dynamic-site-maker' ),
            array( $this, 'button_color_callback' ),
            'dsmk_customization_settings',
            'dsmk_customization_colors_section'
        );

        add_settings_field(
            'dsmk_shortcode_text_color',
            __( 'Text Color', 'dynamic-site-maker' ),
            array( $this, 'text_color_callback' ),
            'dsmk_customization_settings',
            'dsmk_customization_colors_section'
        );

        add_settings_field(
            'dsmk_shortcode_step_titles',
            __( 'Step Titles', 'dynamic-site-maker' ),
            array( $this, 'step_titles_callback' ),
            'dsmk_customization_settings',
            'dsmk_customization_fields_section'
        );

        add_settings_field(
            'dsmk_shortcode_field_labels',
            __( 'Field Labels', 'dynamic-site-maker' ),
            array( $this, 'field_labels_callback' ),
            'dsmk_customization_settings',
            'dsmk_customization_fields_section'
        );

        add_settings_field(
            'dsmk_shortcode_step_labels',
            __( 'Step Labels', 'dynamic-site-maker' ),
            array( $this, 'step_labels_callback' ),
            'dsmk_customization_settings',
            'dsmk_customization_steps_section'
        );

        add_settings_field(
            'dsmk_shortcode_name_placeholder',
            __( 'Name Field Placeholder', 'dynamic-site-maker' ),
            array( $this, 'name_placeholder_callback' ),
            'dsmk_customization_settings',
            'dsmk_customization_placeholders_section'
        );

        add_settings_field(
            'dsmk_shortcode_email_placeholder',
            __( 'Email Field Placeholder', 'dynamic-site-maker' ),
            array( $this, 'email_placeholder_callback' ),
            'dsmk_customization_settings',
            'dsmk_customization_placeholders_section'
        );

        add_settings_field(
            'dsmk_shortcode_link_placeholder',
            __( 'Link Field Placeholder', 'dynamic-site-maker' ),
            array( $this, 'link_placeholder_callback' ),
            'dsmk_customization_settings',
            'dsmk_customization_placeholders_section'
        );
    }

    /**
     * Display admin page
     */
    public function display_admin_page() {
        // Get current tab with nonce verification
        $current_tab = 'settings'; // Default tab
        
        if ( isset( $_GET['tab'] ) ) {
            // Verify the request is legitimate
            if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'dsmk_switch_tab' ) ) {
                $current_tab = sanitize_text_field( wp_unslash( $_GET['tab'] ) );
            } else {
                // If no valid nonce, still allow tab switching but add an admin notice
                $current_tab = sanitize_text_field( wp_unslash( $_GET['tab'] ) );
                add_action( 'admin_notices', array( $this, 'display_admin_notice' ) );
            }
        }
        
        // Standard WordPress wrap - this ensures notices appear in the correct location
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            
            <?php
            // Display settings errors/notices
            settings_errors();
            
            // Enqueue admin assets after the standard WordPress header
            wp_enqueue_style( 'wp-color-picker' );
            wp_enqueue_style( 'dsmk-admin-style', DSMK_PLUGIN_URL . 'assets/css/admin-style.css', array(), DSMK_VERSION );
            wp_enqueue_script( 'dsmk-admin-script', DSMK_PLUGIN_URL . 'assets/js/admin-script.js', array( 'jquery', 'jquery-ui-tabs', 'wp-color-picker' ), DSMK_VERSION, true );
            wp_localize_script( 'dsmk-admin-script', 'dsmk_admin', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'dsmk_admin_nonce' ),
            ));
            ?>
            
            <div class="dsmk-admin">
                <div class="dsmk-version">
                    <span><?php echo esc_html__( 'Version', 'dynamic-site-maker' ) . ' ' . esc_html( DSMK_VERSION ); ?></span>
                </div>
                
                <div class="dsmk-nav-container">
                    <nav class="dsmk-nav">
                        <a href="?page=dynamic-site-maker&tab=settings&_wpnonce=<?php echo esc_attr( wp_create_nonce( 'dsmk_switch_tab' ) ); ?>" class="dsmk-nav-tab <?php echo $current_tab === 'settings' ? 'active' : ''; ?>">
                            <span class="dashicons dashicons-admin-settings"></span>
                            <?php esc_html_e( 'Settings', 'dynamic-site-maker' ); ?>
                        </a>
                        <a href="?page=dynamic-site-maker&tab=customization&_wpnonce=<?php echo esc_attr( wp_create_nonce( 'dsmk_switch_tab' ) ); ?>" class="dsmk-nav-tab <?php echo $current_tab === 'customization' ? 'active' : ''; ?>">
                            <span class="dashicons dashicons-admin-customizer"></span>
                            <?php esc_html_e( 'Customization', 'dynamic-site-maker' ); ?>
                        </a>
                        <a href="?page=dynamic-site-maker&tab=pages&_wpnonce=<?php echo esc_attr( wp_create_nonce( 'dsmk_switch_tab' ) ); ?>" class="dsmk-nav-tab <?php echo $current_tab === 'pages' ? 'active' : ''; ?>">
                            <span class="dashicons dashicons-admin-page"></span>
                            <?php esc_html_e( 'Created Website', 'dynamic-site-maker' ); ?>
                        </a>
                        <a href="?page=dynamic-site-maker&tab=help&_wpnonce=<?php echo esc_attr( wp_create_nonce( 'dsmk_switch_tab' ) ); ?>" class="dsmk-nav-tab <?php echo $current_tab === 'help' ? 'active' : ''; ?>">
                            <span class="dashicons dashicons-editor-help"></span>
                            <?php esc_html_e( 'Help', 'dynamic-site-maker' ); ?>
                        </a>
                    </nav>
                </div>
                
                <div class="dsmk-content">
                    <?php
                    // Display current tab content
                    switch ( $current_tab ) {
                        case 'pages':
                            $this->display_pages_tab();
                            break;
                        case 'help':
                            $this->display_help_tab();
                            break;
                        case 'customization':
                            $this->display_customization_tab();
                            break;
                        default:
                            $this->display_settings_tab();
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Display admin notice
     */
    public function display_admin_notice() {
        ?>
        <div class="notice notice-warning is-dismissible">
            <p><?php esc_html_e( 'Dynamic Site Maker: Tab switched without nonce verification.', 'dynamic-site-maker' ); ?></p>
        </div>
        <?php
    }

    /**
     * Display settings tab
     */
    private function display_settings_tab() {
        ?>
        <div class="dsmk-settings-container">
            <div class="dsmk-card">
                <div class="dsmk-card-header">
                    <h2><?php esc_html_e( 'General Settings', 'dynamic-site-maker' ); ?></h2>
                </div>
                <div class="dsmk-card-body">
                    <form method="post" action="options.php" class="dsmk-form">
                        <?php
                        settings_fields( 'dsmk_settings' );
                        do_settings_sections( 'dsmk_settings' );
                        submit_button( __( 'Save Settings', 'dynamic-site-maker' ), 'primary dsmk-button' );
                        ?>
                    </form>
                </div>
            </div>
            
            <div class="dsmk-card">
                <div class="dsmk-card-header">
                    <h2><?php esc_html_e( 'Shortcode', 'dynamic-site-maker' ); ?></h2>
                </div>
                <div class="dsmk-card-body">
                    <p><?php esc_html_e( 'To add the form to any page or post, use the following shortcode:', 'dynamic-site-maker' ); ?></p>
                    <div class="dsmk-shortcode-copy">
                        <code>[dsmk_form]</code>
                        <button type="button" class="dsmk-copy-button" data-clipboard-text="[dsmk_form]">
                            <span class="dashicons dashicons-clipboard"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Display pages tab
     */
    private function display_pages_tab() {
        ?>
        <div class="dsmk-pages-container">
            <div class="dsmk-card">
                <div class="dsmk-card-header">
                    <h2><?php esc_html_e( 'Created Websites', 'dynamic-site-maker' ); ?></h2>
                </div>
                <div class="dsmk-card-body">
                    <?php $this->display_created_pages(); ?>
                </div>
            </div>
        </div>

        <!-- Update Content Modal -->
        <div id="dsmk-update-content-modal" class="dsmk-modal">
            <div class="dsmk-modal-content">
                <span class="dsmk-modal-close">&times;</span>
                <h2><?php esc_html_e( 'Update Page Content', 'dynamic-site-maker' ); ?></h2>
                
                <form id="dsmk-update-content-form" method="post" enctype="multipart/form-data">
                    <?php wp_nonce_field( 'dsmk_update_content', 'dsmk_update_content_nonce' ); ?>
                    <input type="hidden" id="dsmk-update-post-id" name="post_id" value="">
                    
                    <div class="dsmk-form-field">
                        <label for="dsmk-update-name" class="dsmk-form-label">
                            <?php esc_html_e( 'Name', 'dynamic-site-maker' ); ?>
                        </label>
                        <div class="dsmk-input-wrapper">
                            <input type="text" id="dsmk-update-name" name="name" class="dsmk-form-input" readonly>
                        </div>
                    </div>
                    
                    <div class="dsmk-form-field">
                        <label for="dsmk-update-email" class="dsmk-form-label">
                            <?php esc_html_e( 'Email', 'dynamic-site-maker' ); ?>
                        </label>
                        <div class="dsmk-input-wrapper">
                            <input type="email" id="dsmk-update-email" name="email" class="dsmk-form-input" readonly>
                        </div>
                    </div>
                    
                    <div class="dsmk-form-field">
                        <label for="dsmk-update-logo" class="dsmk-form-label">
                            <?php esc_html_e( 'Update Logo', 'dynamic-site-maker' ); ?>
                        </label>
                        <div class="dsmk-input-wrapper">
                            <input type="file" id="dsmk-update-logo" name="logo" class="dsmk-form-input" accept="image/*">
                            <p class="description"><?php esc_html_e( 'Upload a new logo to replace the existing one. Leave empty to keep the current logo.', 'dynamic-site-maker' ); ?></p>
                        </div>
                    </div>
                    
                    <div class="dsmk-form-field">
                        <label for="dsmk-update-affiliate-link" class="dsmk-form-label">
                            <?php esc_html_e( 'Affiliate Link', 'dynamic-site-maker' ); ?>
                        </label>
                        <div class="dsmk-input-wrapper">
                            <input type="url" id="dsmk-update-affiliate-link" name="affiliate_link" class="dsmk-form-input">
                        </div>
                    </div>
                    
                    <div class="dsmk-form-actions">
                        <button type="submit" class="dsmk-button dsmk-button-primary">
                            <?php esc_html_e( 'Update', 'dynamic-site-maker' ); ?>
                        </button>
                        <button type="button" class="dsmk-button dsmk-button-secondary dsmk-modal-cancel">
                            <?php esc_html_e( 'Cancel', 'dynamic-site-maker' ); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Display help tab
     */
    private function display_help_tab() {
        ?>
        <div class="dsmk-help-container">
            <div class="dsmk-card">
                <div class="dsmk-card-header">
                    <h2><?php esc_html_e( 'How to Use', 'dynamic-site-maker' ); ?></h2>
                </div>
                <div class="dsmk-card-body">
                    <div class="dsmk-help-section">
                        <h3><span class="dashicons dashicons-format-aside"></span> <?php esc_html_e( 'Getting Started', 'dynamic-site-maker' ); ?></h3>
                        <ol>
                            <li><?php esc_html_e( 'Add the shortcode [dsmk_form] to any page or post.', 'dynamic-site-maker' ); ?></li>
                            <li><?php esc_html_e( 'Users will submit their name, logo, and affiliate link.', 'dynamic-site-maker' ); ?></li>
                            <li><?php esc_html_e( 'The plugin will automatically create a landing page using your selected template.', 'dynamic-site-maker' ); ?></li>
                            <li><?php esc_html_e( 'User data will be dynamically inserted into the template.', 'dynamic-site-maker' ); ?></li>
                        </ol>
                    </div>
                    
                    <div class="dsmk-help-section">
                        <h3><span class="dashicons dashicons-admin-customizer"></span> <?php esc_html_e( 'Customizing Templates', 'dynamic-site-maker' ); ?></h3>
                        <p><?php esc_html_e( 'You can use any Elementor template with your plugin. Follow these steps to set up dynamic content:', 'dynamic-site-maker' ); ?></p>
                        
                        <h4><?php esc_html_e( 'For Logo Images:', 'dynamic-site-maker' ); ?></h4>
                        <ol>
                            <li><?php esc_html_e( 'Add an Image widget to your Elementor template', 'dynamic-site-maker' ); ?></li>
                            <li><?php esc_html_e( 'Go to the Advanced tab in the widget settings', 'dynamic-site-maker' ); ?></li>
                            <li><?php esc_html_e( 'Add "dsmk-logo" to the CSS Classes field', 'dynamic-site-maker' ); ?></li>
                            <li><?php esc_html_e( 'This image will be automatically replaced with the user\'s uploaded logo', 'dynamic-site-maker' ); ?></li>
                        </ol>
                        
                        <h4><?php esc_html_e( 'For Affiliate Links:', 'dynamic-site-maker' ); ?></h4>
                        <ol>
                            <li><?php esc_html_e( 'Add a Button widget to your Elementor template', 'dynamic-site-maker' ); ?></li>
                            <li><?php esc_html_e( 'Either:', 'dynamic-site-maker' ); ?>
                                <ul>
                                    <li><?php esc_html_e( 'Go to the Advanced tab and add "dsmk-affiliate" to the CSS Classes field, or', 'dynamic-site-maker' ); ?></li>
                                    <li><?php esc_html_e( 'Set the button link URL to "{{affiliate_link}}"', 'dynamic-site-maker' ); ?></li>
                                </ul>
                            </li>
                            <li><?php esc_html_e( 'This button will automatically link to the user\'s affiliate URL', 'dynamic-site-maker' ); ?></li>
                        </ol>
                        
                        <div class="dsmk-notice dsmk-notice--info">
                            <p><strong><?php esc_html_e( 'Important:', 'dynamic-site-maker' ); ?></strong> <?php esc_html_e( 'The plugin will only replace elements with the specific CSS classes mentioned above. Other elements in your template will remain unchanged.', 'dynamic-site-maker' ); ?></p>
                        </div>
                    </div>
                    
                    <div class="dsmk-help-section">
                        <h3><span class="dashicons dashicons-editor-help"></span> <?php esc_html_e( 'Need Help?', 'dynamic-site-maker' ); ?></h3>
                        <p><?php esc_html_e( 'For additional support, please contact:', 'dynamic-site-maker' ); ?></p>
                        <a href="mailto:bdsajalinfo@gmail.com" class="dsmk-button dsmk-button-secondary">
                            <span class="dashicons dashicons-email"></span>
                            <?php esc_html_e( 'Email Support', 'dynamic-site-maker' ); ?>
                        </a>
                        <a href="https://easywptools.com" target="_blank" class="dsmk-button dsmk-button-secondary">
                            <span class="dashicons dashicons-admin-site"></span>
                            <?php esc_html_e( 'Visit Website', 'dynamic-site-maker' ); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Display created pages
     */
    private function display_created_pages() {
        $args = array(
            'post_type'      => 'page',
            'posts_per_page' => 10,
            'meta_query'     => array(
                array(
                    'key'     => '_dsmk_name',
                    'compare' => 'EXISTS',
                ),
            ),
        );

        $query = new WP_Query( $args );

        if ( $query->have_posts() ) {
            ?>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Page Title', 'dynamic-site-maker' ); ?></th>
                        <th><?php esc_html_e( 'Submitter Name', 'dynamic-site-maker' ); ?></th>
                        <th><?php esc_html_e( 'Email', 'dynamic-site-maker' ); ?></th>
                        <th><?php esc_html_e( 'Date Created', 'dynamic-site-maker' ); ?></th>
                        <th><?php esc_html_e( 'Actions', 'dynamic-site-maker' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ( $query->have_posts() ) : $query->the_post(); ?>
                        <tr>
                            <td>
                                <a href="<?php the_permalink(); ?>" target="_blank"><?php the_title(); ?></a>
                            </td>
                            <td><?php echo esc_html( get_post_meta( get_the_ID(), '_dsmk_name', true ) ); ?></td>
                            <td><?php echo esc_html( get_post_meta( get_the_ID(), '_dsmk_email', true ) ); ?></td>
                            <td><?php echo esc_html( get_the_date() ); ?></td>
                            <td>
                                <a href="<?php echo esc_url( get_edit_post_link() ); ?>"><?php esc_html_e( 'Edit', 'dynamic-site-maker' ); ?></a> |
                                <a href="<?php the_permalink(); ?>" target="_blank"><?php esc_html_e( 'View', 'dynamic-site-maker' ); ?></a> |
                                <a href="#" class="dsmk-update-content" data-post-id="<?php echo esc_attr( get_the_ID() ); ?>" data-name="<?php echo esc_attr( get_post_meta( get_the_ID(), '_dsmk_name', true ) ); ?>" data-email="<?php echo esc_attr( get_post_meta( get_the_ID(), '_dsmk_email', true ) ); ?>" data-link="<?php echo esc_attr( get_post_meta( get_the_ID(), '_dsmk_affiliate_link', true ) ); ?>"><?php esc_html_e( 'Update Content', 'dynamic-site-maker' ); ?></a>
                                <?php
                                // Add delete action with confirmation
                                $delete_url = add_query_arg(
                                    array(
                                        'page' => 'dynamic-site-maker',
                                        'tab' => 'pages',
                                        'action' => 'delete',
                                        'post_id' => get_the_ID(),
                                        '_wpnonce' => wp_create_nonce( 'dsmk_delete_page' ),
                                    ),
                                    admin_url( 'admin.php' )
                                );
                                ?>
                                | <a href="<?php echo esc_url( $delete_url ); ?>" class="dsmk-delete-page" data-page-title="<?php the_title_attribute(); ?>"><?php esc_html_e( 'Delete', 'dynamic-site-maker' ); ?></a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php
        } else {
            echo '<p>' . esc_html__( 'No Websites have been created yet.', 'dynamic-site-maker' ) . '</p>';
        }

        wp_reset_postdata();
    }

    /**
     * Display section info
     *
     * @param array $args The section arguments.
     */
    public function display_section_info( $args ) {
        switch ( $args['id'] ) {
            case 'dsmk_general_settings':
                echo '<p>' . esc_html__( 'Configure general settings for Dynamic Site Maker.', 'dynamic-site-maker' ) . '</p>';
                break;
            case 'dsmk_customization_settings_section':
                echo '<p>' . esc_html__( 'Customize the shortcode page.', 'dynamic-site-maker' ) . '</p>';
                break;
            case 'dsmk_customization_colors_section':
                echo '<p>' . esc_html__( 'Customize the colors of the shortcode page.', 'dynamic-site-maker' ) . '</p>';
                break;
            case 'dsmk_customization_fields_section':
                echo '<p>' . esc_html__( 'Customize the field labels of the shortcode page.', 'dynamic-site-maker' ) . '</p>';
                break;
            case 'dsmk_customization_steps_section':
                echo '<p>' . esc_html__( 'Customize the step labels of the shortcode page.', 'dynamic-site-maker' ) . '</p>';
                break;
            case 'dsmk_customization_placeholders_section':
                echo '<p>' . esc_html__( 'Customize the placeholder text of the shortcode page.', 'dynamic-site-maker' ) . '</p>';
                break;
            default:
                break;
        }
    }

    /**
     * Page title format field callback
     */
    public function page_title_format_callback() {
        ?>
        <input type="text" id="dsmk_page_title_format" name="dsmk_page_title_format" value="<?php echo esc_attr( get_option( 'dsmk_page_title_format' ) ); ?>" class="regular-text">
        <p class="description"><?php esc_html_e( 'The format for the page title. Use {name} for the user\'s name.', 'dynamic-site-maker' ); ?></p>
        <?php
    }

    /**
     * Redirect delay field callback
     */
    public function redirect_delay_callback() {
        ?>
        <input type="number" id="dsmk_redirect_delay" name="dsmk_redirect_delay" value="<?php echo esc_attr( get_option( 'dsmk_redirect_delay' ) ); ?>" class="small-text">
        <p class="description"><?php esc_html_e( 'The delay in seconds before redirecting to the created landing page.', 'dynamic-site-maker' ); ?></p>
        <?php
    }

    /**
     * Form success message field callback
     */
    public function form_success_message_callback() {
        ?>
        <input type="text" id="dsmk_form_success_message" name="dsmk_form_success_message" value="<?php echo esc_attr( get_option( 'dsmk_form_success_message' ) ); ?>" class="regular-text">
        <p class="description"><?php esc_html_e( 'The message displayed after the form is submitted successfully.', 'dynamic-site-maker' ); ?></p>
        <?php
    }
    
    /**
     * Elementor template field callback
     */
    public function elementor_template_callback() {
        $template_id = get_option( 'dsmk_elementor_template_id', 0 );
        
        // Get all Elementor templates
        $templates = $this->get_elementor_templates();
        ?>
        <select id="dsmk_elementor_template_id" name="dsmk_elementor_template_id" class="regular-text">
            <option value="0"><?php esc_html_e( 'Select a template', 'dynamic-site-maker' ); ?></option>
            <?php foreach ( $templates as $id => $name ) : ?>
                <option value="<?php echo esc_attr( $id ); ?>" <?php selected( $template_id, $id ); ?>>
                    <?php echo esc_html( $name ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description"><?php esc_html_e( 'Select an Elementor template to use for landing pages.', 'dynamic-site-maker' ); ?></p>
        <p class="description"><?php esc_html_e( 'To mark an image as a logo, add the CSS class "dsmk-logo" to the image widget.', 'dynamic-site-maker' ); ?></p>
        <p class="description"><?php esc_html_e( 'To mark a button for affiliate link, add the CSS class "dsmk-affiliate" to the button widget or use {{affiliate_link}} as the URL.', 'dynamic-site-maker' ); ?></p>
        <?php
    }
    
    /**
     * Get all Elementor templates
     *
     * @return array Array of template IDs and names.
     */
    private function get_elementor_templates() {
        $templates = array();
        
        // Check if Elementor is active
        if ( ! did_action( 'elementor/loaded' ) ) {
            return $templates;
        }
        
        // Get all Elementor templates
        $args = array(
            'post_type'      => 'elementor_library',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        );
        
        $query = new WP_Query( $args );
        
        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $templates[ get_the_ID() ] = get_the_title();
            }
        }
        
        wp_reset_postdata();
        
        return $templates;
    }

    /**
     * Handle page deletion
     */
    public function handle_page_deletion() {
        // Check if we're on the right page and have the right action
        if ( ! isset( $_GET['page'] ) || 'dynamic-site-maker' !== $_GET['page'] ) {
            return;
        }
        
        if ( ! isset( $_GET['action'] ) || 'delete' !== $_GET['action'] ) {
            return;
        }
        
        if ( ! isset( $_GET['post_id'] ) || ! isset( $_GET['_wpnonce'] ) ) {
            return;
        }
        
        // Verify nonce
        if ( ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'dsmk_delete_page' ) ) {
            wp_die( esc_html__( 'Security check failed. Please try again.', 'dynamic-site-maker' ) );
        }
        
        // Check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to delete this page.', 'dynamic-site-maker' ) );
        }
        
        $post_id = absint( $_GET['post_id'] );
        
        // Check if the post exists and is a landing page
        $post = get_post( $post_id );
        if ( ! $post || 'page' !== $post->post_type || ! get_post_meta( $post_id, '_dsmk_name', true ) ) {
            wp_die( esc_html__( 'The specified website does not exist.', 'dynamic-site-maker' ) );
        }
        
        // Delete the page
        wp_delete_post( $post_id, true );
        
        // Redirect back to the landing pages tab with a success message
        wp_safe_redirect( add_query_arg( 
            array(
                'page' => 'dynamic-site-maker',
                'tab' => 'pages',
                'deleted' => '1',
                '_wpnonce' => wp_create_nonce( 'dsmk_switch_tab' ),
            ),
            admin_url( 'admin.php' )
        ) );
        exit;
    }

    /**
     * Display customization tab
     */
    private function display_customization_tab() {
        ?>
        <div class="dsmk-customization-container">
            <div class="dsmk-card">
                <div class="dsmk-card-header">
                    <h2><?php esc_html_e( 'Shortcode Page Customization', 'dynamic-site-maker' ); ?></h2>
                </div>
                <div class="dsmk-card-body">
                    <form method="post" action="options.php" class="dsmk-form">
                        <?php
                        settings_fields( 'dsmk_customization_settings' );
                        do_settings_sections( 'dsmk_customization_settings' );
                        submit_button( __( 'Save Customization', 'dynamic-site-maker' ), 'primary dsmk-button' );
                        ?>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Shortcode page title field callback
     */
    public function shortcode_page_title_callback() {
        ?>
        <input type="text" id="dsmk_shortcode_page_title" name="dsmk_shortcode_page_title" value="<?php echo esc_attr( get_option( 'dsmk_shortcode_page_title' ) ); ?>" class="regular-text">
        <p class="description"><?php esc_html_e( 'The title for the shortcode page.', 'dynamic-site-maker' ); ?></p>
        <?php
    }
    
    /**
     * Shortcode page description field callback
     */
    public function shortcode_page_description_callback() {
        ?>
        <textarea id="dsmk_shortcode_page_description" name="dsmk_shortcode_page_description" class="large-text"><?php echo esc_textarea( get_option( 'dsmk_shortcode_page_description' ) ); ?></textarea>
        <p class="description"><?php esc_html_e( 'The description for the shortcode page.', 'dynamic-site-maker' ); ?></p>
        <?php
    }
    
    /**
     * Header background color field callback
     */
    public function header_bg_color_callback() {
        ?>
        <input type="text" id="dsmk_shortcode_header_bg_color" name="dsmk_shortcode_header_bg_color" value="<?php echo esc_attr( get_option( 'dsmk_shortcode_header_bg_color' ) ); ?>" class="color-picker" data-alpha="true">
        <p class="description"><?php esc_html_e( 'The background color of the header.', 'dynamic-site-maker' ); ?></p>
        <?php
    }
    
    /**
     * Button color field callback
     */
    public function button_color_callback() {
        ?>
        <input type="text" id="dsmk_shortcode_button_color" name="dsmk_shortcode_button_color" value="<?php echo esc_attr( get_option( 'dsmk_shortcode_button_color' ) ); ?>" class="color-picker" data-alpha="true">
        <p class="description"><?php esc_html_e( 'The color of the buttons.', 'dynamic-site-maker' ); ?></p>
        <?php
    }
    
    /**
     * Text color field callback
     */
    public function text_color_callback() {
        ?>
        <input type="text" id="dsmk_shortcode_text_color" name="dsmk_shortcode_text_color" value="<?php echo esc_attr( get_option( 'dsmk_shortcode_text_color' ) ); ?>" class="color-picker" data-alpha="true">
        <p class="description"><?php esc_html_e( 'The color of the text.', 'dynamic-site-maker' ); ?></p>
        <?php
    }
    
    /**
     * Step titles field callback
     */
    public function step_titles_callback() {
        ?>
        <div class="dsmk-step-titles">
            <div class="dsmk-step-title">
                <label for="dsmk_shortcode_step1_title"><?php esc_html_e( 'Step 1 Title', 'dynamic-site-maker' ); ?></label>
                <input type="text" id="dsmk_shortcode_step1_title" name="dsmk_shortcode_step1_title" value="<?php echo esc_attr( get_option( 'dsmk_shortcode_step1_title' ) ); ?>" class="regular-text">
            </div>
            <div class="dsmk-step-title">
                <label for="dsmk_shortcode_step2_title"><?php esc_html_e( 'Step 2 Title', 'dynamic-site-maker' ); ?></label>
                <input type="text" id="dsmk_shortcode_step2_title" name="dsmk_shortcode_step2_title" value="<?php echo esc_attr( get_option( 'dsmk_shortcode_step2_title' ) ); ?>" class="regular-text">
            </div>
            <div class="dsmk-step-title">
                <label for="dsmk_shortcode_step3_title"><?php esc_html_e( 'Step 3 Title', 'dynamic-site-maker' ); ?></label>
                <input type="text" id="dsmk_shortcode_step3_title" name="dsmk_shortcode_step3_title" value="<?php echo esc_attr( get_option( 'dsmk_shortcode_step3_title' ) ); ?>" class="regular-text">
            </div>
        </div>
        <p class="description"><?php esc_html_e( 'The titles for each step of the form.', 'dynamic-site-maker' ); ?></p>
        <?php
    }
    
    /**
     * Field labels field callback
     */
    public function field_labels_callback() {
        ?>
        <div class="dsmk-field-labels">
            <div class="dsmk-field-label">
                <label for="dsmk_shortcode_name_label"><?php esc_html_e( 'Name Field Label', 'dynamic-site-maker' ); ?></label>
                <input type="text" id="dsmk_shortcode_name_label" name="dsmk_shortcode_name_label" value="<?php echo esc_attr( get_option( 'dsmk_shortcode_name_label' ) ); ?>" class="regular-text">
            </div>
            <div class="dsmk-field-label">
                <label for="dsmk_shortcode_email_label"><?php esc_html_e( 'Email Field Label', 'dynamic-site-maker' ); ?></label>
                <input type="text" id="dsmk_shortcode_email_label" name="dsmk_shortcode_email_label" value="<?php echo esc_attr( get_option( 'dsmk_shortcode_email_label' ) ); ?>" class="regular-text">
            </div>
            <div class="dsmk-field-label">
                <label for="dsmk_shortcode_logo_label"><?php esc_html_e( 'Logo Field Label', 'dynamic-site-maker' ); ?></label>
                <input type="text" id="dsmk_shortcode_logo_label" name="dsmk_shortcode_logo_label" value="<?php echo esc_attr( get_option( 'dsmk_shortcode_logo_label' ) ); ?>" class="regular-text">
            </div>
            <div class="dsmk-field-label">
                <label for="dsmk_shortcode_link_label"><?php esc_html_e( 'Link Field Label', 'dynamic-site-maker' ); ?></label>
                <input type="text" id="dsmk_shortcode_link_label" name="dsmk_shortcode_link_label" value="<?php echo esc_attr( get_option( 'dsmk_shortcode_link_label' ) ); ?>" class="regular-text">
            </div>
        </div>
        <p class="description"><?php esc_html_e( 'The labels for each field of the form.', 'dynamic-site-maker' ); ?></p>
        <?php
    }
    
    /**
     * Step labels field callback
     */
    public function step_labels_callback() {
        ?>
        <div class="dsmk-step-labels">
            <div class="dsmk-step-label">
                <label for="dsmk_shortcode_step1_label"><?php esc_html_e( 'Step 1 Label', 'dynamic-site-maker' ); ?></label>
                <input type="text" id="dsmk_shortcode_step1_label" name="dsmk_shortcode_step1_label" value="<?php echo esc_attr( get_option( 'dsmk_shortcode_step1_label' ) ); ?>" class="regular-text">
            </div>
            <div class="dsmk-step-label">
                <label for="dsmk_shortcode_step2_label"><?php esc_html_e( 'Step 2 Label', 'dynamic-site-maker' ); ?></label>
                <input type="text" id="dsmk_shortcode_step2_label" name="dsmk_shortcode_step2_label" value="<?php echo esc_attr( get_option( 'dsmk_shortcode_step2_label' ) ); ?>" class="regular-text">
            </div>
            <div class="dsmk-step-label">
                <label for="dsmk_shortcode_step3_label"><?php esc_html_e( 'Step 3 Label', 'dynamic-site-maker' ); ?></label>
                <input type="text" id="dsmk_shortcode_step3_label" name="dsmk_shortcode_step3_label" value="<?php echo esc_attr( get_option( 'dsmk_shortcode_step3_label' ) ); ?>" class="regular-text">
            </div>
        </div>
        <p class="description"><?php esc_html_e( 'The labels for each step of the form.', 'dynamic-site-maker' ); ?></p>
        <?php
    }
    
    /**
     * Name placeholder field callback
     */
    public function name_placeholder_callback() {
        ?>
        <input type="text" id="dsmk_shortcode_name_placeholder" name="dsmk_shortcode_name_placeholder" value="<?php echo esc_attr( get_option( 'dsmk_shortcode_name_placeholder' ) ); ?>" class="regular-text">
        <p class="description"><?php esc_html_e( 'The placeholder text for the name field.', 'dynamic-site-maker' ); ?></p>
        <?php
    }
    
    /**
     * Email placeholder field callback
     */
    public function email_placeholder_callback() {
        ?>
        <input type="text" id="dsmk_shortcode_email_placeholder" name="dsmk_shortcode_email_placeholder" value="<?php echo esc_attr( get_option( 'dsmk_shortcode_email_placeholder' ) ); ?>" class="regular-text">
        <p class="description"><?php esc_html_e( 'The placeholder text for the email field.', 'dynamic-site-maker' ); ?></p>
        <?php
    }
    
    /**
     * Link placeholder field callback
     */
    public function link_placeholder_callback() {
        ?>
        <input type="text" id="dsmk_shortcode_link_placeholder" name="dsmk_shortcode_link_placeholder" value="<?php echo esc_attr( get_option( 'dsmk_shortcode_link_placeholder' ) ); ?>" class="regular-text">
        <p class="description"><?php esc_html_e( 'The placeholder text for the link field.', 'dynamic-site-maker' ); ?></p>
        <?php
    }
}
