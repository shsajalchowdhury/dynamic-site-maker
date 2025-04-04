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
    }

    /**
     * Display admin page
     */
    public function display_admin_page() {
        // Enqueue admin assets
        wp_enqueue_style( 'dsmk-admin-style', DSMK_PLUGIN_URL . 'assets/css/admin-style.css', array(), DSMK_VERSION );
        wp_enqueue_script( 'dsmk-admin-script', DSMK_PLUGIN_URL . 'assets/js/admin-script.js', array( 'jquery', 'jquery-ui-tabs', 'wp-color-picker' ), DSMK_VERSION, true );
        wp_localize_script( 'dsmk-admin-script', 'dsmk_admin', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'dsmk_admin_nonce' ),
        ));
        
        // Get current tab
        $current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'settings';
        ?>
        <div class="wrap dsmk-admin">
            <div class="dsmk-header">
                <div class="dsmk-logo">
                    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
                </div>
                <div class="dsmk-version">
                    <span><?php echo esc_html__( 'Version', 'dynamic-site-maker' ) . ' ' . DSMK_VERSION; ?></span>
                </div>
            </div>
            
            <div class="dsmk-nav-container">
                <nav class="dsmk-nav">
                    <a href="?page=dynamic-site-maker&tab=settings" class="dsmk-nav-tab <?php echo $current_tab === 'settings' ? 'active' : ''; ?>">
                        <span class="dashicons dashicons-admin-settings"></span>
                        <?php esc_html_e( 'Settings', 'dynamic-site-maker' ); ?>
                    </a>
                    <a href="?page=dynamic-site-maker&tab=templates" class="dsmk-nav-tab <?php echo $current_tab === 'templates' ? 'active' : ''; ?>">
                        <span class="dashicons dashicons-layout"></span>
                        <?php esc_html_e( 'Templates', 'dynamic-site-maker' ); ?>
                    </a>
                    <a href="?page=dynamic-site-maker&tab=pages" class="dsmk-nav-tab <?php echo $current_tab === 'pages' ? 'active' : ''; ?>">
                        <span class="dashicons dashicons-admin-page"></span>
                        <?php esc_html_e( 'Landing Pages', 'dynamic-site-maker' ); ?>
                    </a>
                    <a href="?page=dynamic-site-maker&tab=help" class="dsmk-nav-tab <?php echo $current_tab === 'help' ? 'active' : ''; ?>">
                        <span class="dashicons dashicons-editor-help"></span>
                        <?php esc_html_e( 'Help', 'dynamic-site-maker' ); ?>
                    </a>
                </nav>
            </div>
            
            <div class="dsmk-content">
                <?php
                // Display current tab content
                switch ( $current_tab ) {
                    case 'templates':
                        $this->display_templates_tab();
                        break;
                    case 'pages':
                        $this->display_pages_tab();
                        break;
                    case 'help':
                        $this->display_help_tab();
                        break;
                    default:
                        $this->display_settings_tab();
                }
                ?>
            </div>
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
     * Display templates tab
     */
    private function display_templates_tab() {
        ?>
        <div class="dsmk-templates-container">
            <div class="dsmk-card">
                <div class="dsmk-card-header">
                    <h2><?php esc_html_e( 'Available Templates', 'dynamic-site-maker' ); ?></h2>
                </div>
                <div class="dsmk-card-body">
                    <div class="dsmk-template-selection">
                        <div class="dsmk-template-item dsmk-template-active">
                            <div class="dsmk-template-preview">
                                <img src="<?php echo esc_url( DSMK_PLUGIN_URL . 'assets/images/template-default.jpg' ); ?>" alt="<?php esc_attr_e( 'Default Template', 'dynamic-site-maker' ); ?>">
                                <div class="dsmk-template-actions">
                                    <button type="button" class="dsmk-button dsmk-button-secondary dsmk-button-preview" data-template="default">
                                        <?php esc_html_e( 'Preview', 'dynamic-site-maker' ); ?>
                                    </button>
                                    <button type="button" class="dsmk-button dsmk-button-primary dsmk-button-select" data-template="default">
                                        <?php esc_html_e( 'Active', 'dynamic-site-maker' ); ?>
                                    </button>
                                </div>
                            </div>
                            <div class="dsmk-template-info">
                                <h3><?php esc_html_e( 'Default Template', 'dynamic-site-maker' ); ?></h3>
                                <p><?php esc_html_e( 'A clean and modern design for your landing pages.', 'dynamic-site-maker' ); ?></p>
                            </div>
                        </div>
                        
                        <div class="dsmk-template-item">
                            <div class="dsmk-template-preview">
                                <img src="<?php echo esc_url( DSMK_PLUGIN_URL . 'assets/images/template-business.jpg' ); ?>" alt="<?php esc_attr_e( 'Business Template', 'dynamic-site-maker' ); ?>">
                                <div class="dsmk-template-actions">
                                    <button type="button" class="dsmk-button dsmk-button-secondary dsmk-button-preview" data-template="business">
                                        <?php esc_html_e( 'Preview', 'dynamic-site-maker' ); ?>
                                    </button>
                                    <button type="button" class="dsmk-button dsmk-button-primary dsmk-button-select" data-template="business">
                                        <?php esc_html_e( 'Select', 'dynamic-site-maker' ); ?>
                                    </button>
                                </div>
                            </div>
                            <div class="dsmk-template-info">
                                <h3><?php esc_html_e( 'Business Template', 'dynamic-site-maker' ); ?></h3>
                                <p><?php esc_html_e( 'Professional design for business and corporate landing pages.', 'dynamic-site-maker' ); ?></p>
                            </div>
                        </div>
                        
                        <div class="dsmk-template-upload">
                            <div class="dsmk-upload-placeholder">
                                <span class="dashicons dashicons-plus"></span>
                                <h3><?php esc_html_e( 'Custom Template', 'dynamic-site-maker' ); ?></h3>
                                <p><?php esc_html_e( 'Upload your own Elementor template', 'dynamic-site-maker' ); ?></p>
                                <button type="button" class="dsmk-button dsmk-button-primary dsmk-upload-button">
                                    <?php esc_html_e( 'Upload Template', 'dynamic-site-maker' ); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="dsmk-card">
                <div class="dsmk-card-header">
                    <h2><?php esc_html_e( 'Template Configuration', 'dynamic-site-maker' ); ?></h2>
                </div>
                <div class="dsmk-card-body">
                    <p><?php esc_html_e( 'Configure how dynamic fields are inserted into your template.', 'dynamic-site-maker' ); ?></p>
                    
                    <form class="dsmk-form">
                        <div class="dsmk-form-field">
                            <label for="dsmk-name-selector"><?php esc_html_e( 'Name Field Selector', 'dynamic-site-maker' ); ?></label>
                            <input type="text" id="dsmk-name-selector" name="dsmk_name_selector" value="{{name}}" class="regular-text">
                            <p class="description"><?php esc_html_e( 'The placeholder for user name in your template.', 'dynamic-site-maker' ); ?></p>
                        </div>
                        
                        <div class="dsmk-form-field">
                            <label for="dsmk-logo-selector"><?php esc_html_e( 'Logo Field Selector', 'dynamic-site-maker' ); ?></label>
                            <input type="text" id="dsmk-logo-selector" name="dsmk_logo_selector" value="{{logo_id}}" class="regular-text">
                            <p class="description"><?php esc_html_e( 'The placeholder for logo ID in your template.', 'dynamic-site-maker' ); ?></p>
                        </div>
                        
                        <div class="dsmk-form-field">
                            <label for="dsmk-affiliate-selector"><?php esc_html_e( 'Affiliate Link Selector', 'dynamic-site-maker' ); ?></label>
                            <input type="text" id="dsmk-affiliate-selector" name="dsmk_affiliate_selector" value="{{affiliate_link}}" class="regular-text">
                            <p class="description"><?php esc_html_e( 'The placeholder for affiliate link in your template.', 'dynamic-site-maker' ); ?></p>
                        </div>
                        
                        <button type="submit" class="dsmk-button dsmk-button-primary">
                            <?php esc_html_e( 'Save Configuration', 'dynamic-site-maker' ); ?>
                        </button>
                    </form>
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
                    <h2><?php esc_html_e( 'Created Landing Pages', 'dynamic-site-maker' ); ?></h2>
                </div>
                <div class="dsmk-card-body">
                    <?php $this->display_created_pages(); ?>
                </div>
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
                        <p><?php esc_html_e( 'You can use any Elementor template with your plugin. Just make sure to include the following placeholders:', 'dynamic-site-maker' ); ?></p>
                        <ul>
                            <li><code>{{name}}</code> - <?php esc_html_e( 'For the user\'s name', 'dynamic-site-maker' ); ?></li>
                            <li><code>{{logo_id}}</code> - <?php esc_html_e( 'For the logo image ID', 'dynamic-site-maker' ); ?></li>
                            <li><code>{{logo_url}}</code> - <?php esc_html_e( 'For the logo image URL', 'dynamic-site-maker' ); ?></li>
                            <li><code>{{affiliate_link}}</code> - <?php esc_html_e( 'For the affiliate link', 'dynamic-site-maker' ); ?></li>
                        </ul>
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
                            <td><?php echo esc_html( get_the_date() ); ?></td>
                            <td>
                                <a href="<?php echo esc_url( get_edit_post_link() ); ?>"><?php esc_html_e( 'Edit', 'dynamic-site-maker' ); ?></a> |
                                <a href="<?php the_permalink(); ?>" target="_blank"><?php esc_html_e( 'View', 'dynamic-site-maker' ); ?></a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php
        } else {
            echo '<p>' . esc_html__( 'No landing pages have been created yet.', 'dynamic-site-maker' ) . '</p>';
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
}
