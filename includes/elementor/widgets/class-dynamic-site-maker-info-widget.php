<?php
/**
 * Dynamic Site Maker Info Widget
 *
 * @package Dynamic_Site_Maker
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Class DSMK_Info_Widget
 *
 * Custom Elementor widget for displaying dynamic site information.
 */
class DSMK_Info_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name
     *
     * @return string Widget name.
     */
    public function get_name() {
        return 'dsmk_info_widget';
    }

    /**
     * Get widget title
     *
     * @return string Widget title.
     */
    public function get_title() {
        return __( 'Dynamic Site Info', 'dynamic-site-maker' );
    }

    /**
     * Get widget icon
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-site-identity';
    }

    /**
     * Get widget categories
     *
     * @return array Widget categories.
     */
    public function get_categories() {
        return array( 'dynamic-site-maker' );
    }

    /**
     * Register widget controls
     */
    protected function _register_controls() {
        $this->start_controls_section(
            'content_section',
            array(
                'label' => __( 'Content', 'dynamic-site-maker' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'layout',
            array(
                'label'   => __( 'Layout', 'dynamic-site-maker' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'vertical',
                'options' => array(
                    'vertical'   => __( 'Vertical', 'dynamic-site-maker' ),
                    'horizontal' => __( 'Horizontal', 'dynamic-site-maker' ),
                    'centered'   => __( 'Centered', 'dynamic-site-maker' ),
                    'custom'     => __( 'Custom', 'dynamic-site-maker' ),
                ),
            )
        );

        $this->add_control(
            'alignment',
            array(
                'label'     => __( 'Alignment', 'dynamic-site-maker' ),
                'type'      => \Elementor\Controls_Manager::CHOOSE,
                'options'   => array(
                    'left'   => array(
                        'title' => __( 'Left', 'dynamic-site-maker' ),
                        'icon'  => 'eicon-text-align-left',
                    ),
                    'center' => array(
                        'title' => __( 'Center', 'dynamic-site-maker' ),
                        'icon'  => 'eicon-text-align-center',
                    ),
                    'right'  => array(
                        'title' => __( 'Right', 'dynamic-site-maker' ),
                        'icon'  => 'eicon-text-align-right',
                    ),
                ),
                'default'   => 'center',
                'selectors' => array(
                    '{{WRAPPER}} .dsmk-info-widget' => 'text-align: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'content_heading_name',
            array(
                'label'     => __( 'Name', 'dynamic-site-maker' ),
                'type'      => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_control(
            'show_name',
            array(
                'label'        => __( 'Show Name', 'dynamic-site-maker' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => __( 'Yes', 'dynamic-site-maker' ),
                'label_off'    => __( 'No', 'dynamic-site-maker' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'name_tag',
            array(
                'label'     => __( 'HTML Tag', 'dynamic-site-maker' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'default'   => 'h2',
                'options'   => array(
                    'h1'   => 'H1',
                    'h2'   => 'H2',
                    'h3'   => 'H3',
                    'h4'   => 'H4',
                    'h5'   => 'H5',
                    'h6'   => 'H6',
                    'div'  => 'div',
                    'span' => 'span',
                    'p'    => 'p',
                ),
                'condition' => array(
                    'show_name' => 'yes',
                ),
            )
        );

        $this->add_control(
            'name_prefix',
            array(
                'label'       => __( 'Name Prefix', 'dynamic-site-maker' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => '',
                'placeholder' => __( 'Welcome, ', 'dynamic-site-maker' ),
                'condition'   => array(
                    'show_name' => 'yes',
                ),
            )
        );

        $this->add_control(
            'name_suffix',
            array(
                'label'       => __( 'Name Suffix', 'dynamic-site-maker' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => '',
                'placeholder' => __( '!', 'dynamic-site-maker' ),
                'condition'   => array(
                    'show_name' => 'yes',
                ),
            )
        );

        $this->add_control(
            'content_heading_logo',
            array(
                'label'     => __( 'Logo', 'dynamic-site-maker' ),
                'type'      => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_control(
            'show_logo',
            array(
                'label'        => __( 'Show Logo', 'dynamic-site-maker' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => __( 'Yes', 'dynamic-site-maker' ),
                'label_off'    => __( 'No', 'dynamic-site-maker' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'logo_position',
            array(
                'label'     => __( 'Logo Position', 'dynamic-site-maker' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'default'   => 'above',
                'options'   => array(
                    'above'  => __( 'Above Name', 'dynamic-site-maker' ),
                    'below'  => __( 'Below Name', 'dynamic-site-maker' ),
                    'left'   => __( 'Left of Name', 'dynamic-site-maker' ),
                    'right'  => __( 'Right of Name', 'dynamic-site-maker' ),
                ),
                'condition' => array(
                    'show_logo' => 'yes',
                    'show_name' => 'yes',
                    'layout'    => 'custom',
                ),
            )
        );

        $this->add_control(
            'logo_link',
            array(
                'label'        => __( 'Logo Link', 'dynamic-site-maker' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => __( 'Yes', 'dynamic-site-maker' ),
                'label_off'    => __( 'No', 'dynamic-site-maker' ),
                'return_value' => 'yes',
                'default'      => 'no',
                'condition'    => array(
                    'show_logo' => 'yes',
                ),
            )
        );

        $this->add_control(
            'content_heading_affiliate',
            array(
                'label'     => __( 'Affiliate Link', 'dynamic-site-maker' ),
                'type'      => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_control(
            'show_affiliate_link',
            array(
                'label'        => __( 'Show Affiliate Link', 'dynamic-site-maker' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => __( 'Yes', 'dynamic-site-maker' ),
                'label_off'    => __( 'No', 'dynamic-site-maker' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->add_control(
            'button_text',
            array(
                'label'       => __( 'Button Text', 'dynamic-site-maker' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => __( 'Visit Affiliate Link', 'dynamic-site-maker' ),
                'placeholder' => __( 'Enter button text', 'dynamic-site-maker' ),
                'condition'   => array(
                    'show_affiliate_link' => 'yes',
                ),
            )
        );

        $this->add_control(
            'button_icon',
            array(
                'label'       => __( 'Button Icon', 'dynamic-site-maker' ),
                'type'        => \Elementor\Controls_Manager::ICONS,
                'default'     => array(
                    'value'   => 'fas fa-external-link-alt',
                    'library' => 'fa-solid',
                ),
                'condition'   => array(
                    'show_affiliate_link' => 'yes',
                ),
            )
        );

        $this->add_control(
            'icon_position',
            array(
                'label'     => __( 'Icon Position', 'dynamic-site-maker' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'default'   => 'after',
                'options'   => array(
                    'before' => __( 'Before', 'dynamic-site-maker' ),
                    'after'  => __( 'After', 'dynamic-site-maker' ),
                ),
                'condition' => array(
                    'show_affiliate_link' => 'yes',
                    'button_icon[value]!' => '',
                ),
            )
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'style_section',
            array(
                'label' => __( 'Style', 'dynamic-site-maker' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        // Container style
        $this->add_control(
            'container_heading',
            array(
                'label'     => __( 'Container', 'dynamic-site-maker' ),
                'type'      => \Elementor\Controls_Manager::HEADING,
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            array(
                'name'     => 'container_background',
                'label'    => __( 'Background', 'dynamic-site-maker' ),
                'types'    => array( 'classic', 'gradient' ),
                'selector' => '{{WRAPPER}} .dsmk-info-widget',
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            array(
                'name'     => 'container_border',
                'label'    => __( 'Border', 'dynamic-site-maker' ),
                'selector' => '{{WRAPPER}} .dsmk-info-widget',
            )
        );

        $this->add_control(
            'container_border_radius',
            array(
                'label'      => __( 'Border Radius', 'dynamic-site-maker' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', '%' ),
                'selectors'  => array(
                    '{{WRAPPER}} .dsmk-info-widget' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'container_box_shadow',
                'label'    => __( 'Box Shadow', 'dynamic-site-maker' ),
                'selector' => '{{WRAPPER}} .dsmk-info-widget',
            )
        );

        $this->add_responsive_control(
            'container_padding',
            array(
                'label'      => __( 'Padding', 'dynamic-site-maker' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', 'em', '%' ),
                'selectors'  => array(
                    '{{WRAPPER}} .dsmk-info-widget' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_responsive_control(
            'container_margin',
            array(
                'label'      => __( 'Margin', 'dynamic-site-maker' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', 'em', '%' ),
                'selectors'  => array(
                    '{{WRAPPER}} .dsmk-info-widget' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        // Name style
        $this->add_control(
            'name_style_heading',
            array(
                'label'     => __( 'Name', 'dynamic-site-maker' ),
                'type'      => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => array(
                    'show_name' => 'yes',
                ),
            )
        );

        $this->add_control(
            'name_color',
            array(
                'label'     => __( 'Text Color', 'dynamic-site-maker' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .dsmk-name' => 'color: {{VALUE}}',
                ),
                'condition' => array(
                    'show_name' => 'yes',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name'      => 'name_typography',
                'selector'  => '{{WRAPPER}} .dsmk-name',
                'condition' => array(
                    'show_name' => 'yes',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Shadow::get_type(),
            array(
                'name'      => 'name_text_shadow',
                'selector'  => '{{WRAPPER}} .dsmk-name',
                'condition' => array(
                    'show_name' => 'yes',
                ),
            )
        );

        $this->add_responsive_control(
            'name_margin',
            array(
                'label'      => __( 'Margin', 'dynamic-site-maker' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', 'em', '%' ),
                'selectors'  => array(
                    '{{WRAPPER}} .dsmk-name' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
                'condition'  => array(
                    'show_name' => 'yes',
                ),
            )
        );

        // Logo style
        $this->add_control(
            'logo_style_heading',
            array(
                'label'     => __( 'Logo', 'dynamic-site-maker' ),
                'type'      => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => array(
                    'show_logo' => 'yes',
                ),
            )
        );

        $this->add_responsive_control(
            'logo_width',
            array(
                'label'      => __( 'Width', 'dynamic-site-maker' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array( 'px', '%' ),
                'range'      => array(
                    'px' => array(
                        'min'  => 0,
                        'max'  => 500,
                        'step' => 1,
                    ),
                    '%'  => array(
                        'min'  => 0,
                        'max'  => 100,
                        'step' => 1,
                    ),
                ),
                'default'    => array(
                    'unit' => '%',
                    'size' => 100,
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .dsmk-logo img' => 'width: {{SIZE}}{{UNIT}};',
                ),
                'condition'  => array(
                    'show_logo' => 'yes',
                ),
            )
        );

        $this->add_responsive_control(
            'logo_max_width',
            array(
                'label'      => __( 'Max Width', 'dynamic-site-maker' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array( 'px', '%' ),
                'range'      => array(
                    'px' => array(
                        'min'  => 0,
                        'max'  => 1000,
                        'step' => 1,
                    ),
                    '%'  => array(
                        'min'  => 0,
                        'max'  => 100,
                        'step' => 1,
                    ),
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .dsmk-logo img' => 'max-width: {{SIZE}}{{UNIT}};',
                ),
                'condition'  => array(
                    'show_logo' => 'yes',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            array(
                'name'      => 'logo_border',
                'selector'  => '{{WRAPPER}} .dsmk-logo img',
                'condition' => array(
                    'show_logo' => 'yes',
                ),
            )
        );

        $this->add_control(
            'logo_border_radius',
            array(
                'label'      => __( 'Border Radius', 'dynamic-site-maker' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', '%' ),
                'selectors'  => array(
                    '{{WRAPPER}} .dsmk-logo img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
                'condition'  => array(
                    'show_logo' => 'yes',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            array(
                'name'      => 'logo_box_shadow',
                'selector'  => '{{WRAPPER}} .dsmk-logo img',
                'condition' => array(
                    'show_logo' => 'yes',
                ),
            )
        );

        $this->add_responsive_control(
            'logo_margin',
            array(
                'label'      => __( 'Margin', 'dynamic-site-maker' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', 'em', '%' ),
                'selectors'  => array(
                    '{{WRAPPER}} .dsmk-logo' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
                'condition'  => array(
                    'show_logo' => 'yes',
                ),
            )
        );

        $this->add_responsive_control(
            'logo_padding',
            array(
                'label'      => __( 'Padding', 'dynamic-site-maker' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', 'em', '%' ),
                'selectors'  => array(
                    '{{WRAPPER}} .dsmk-logo' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
                'condition'  => array(
                    'show_logo' => 'yes',
                ),
            )
        );

        // Button style
        $this->add_control(
            'button_style_heading',
            array(
                'label'     => __( 'Button', 'dynamic-site-maker' ),
                'type'      => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => array(
                    'show_affiliate_link' => 'yes',
                ),
            )
        );

        $this->start_controls_tabs( 'button_style_tabs' );

        // Normal tab
        $this->start_controls_tab(
            'button_style_normal',
            array(
                'label'     => __( 'Normal', 'dynamic-site-maker' ),
                'condition' => array(
                    'show_affiliate_link' => 'yes',
                ),
            )
        );

        $this->add_control(
            'button_text_color',
            array(
                'label'     => __( 'Text Color', 'dynamic-site-maker' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => array(
                    '{{WRAPPER}} .dsmk-button' => 'color: {{VALUE}}',
                ),
                'condition' => array(
                    'show_affiliate_link' => 'yes',
                ),
            )
        );

        $this->add_control(
            'button_background_color',
            array(
                'label'     => __( 'Background Color', 'dynamic-site-maker' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#4054b2',
                'selectors' => array(
                    '{{WRAPPER}} .dsmk-button' => 'background-color: {{VALUE}}',
                ),
                'condition' => array(
                    'show_affiliate_link' => 'yes',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            array(
                'name'      => 'button_border',
                'selector'  => '{{WRAPPER}} .dsmk-button',
                'condition' => array(
                    'show_affiliate_link' => 'yes',
                ),
            )
        );

        $this->end_controls_tab();

        // Hover tab
        $this->start_controls_tab(
            'button_style_hover',
            array(
                'label'     => __( 'Hover', 'dynamic-site-maker' ),
                'condition' => array(
                    'show_affiliate_link' => 'yes',
                ),
            )
        );

        $this->add_control(
            'button_text_color_hover',
            array(
                'label'     => __( 'Text Color', 'dynamic-site-maker' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .dsmk-button:hover' => 'color: {{VALUE}}',
                ),
                'condition' => array(
                    'show_affiliate_link' => 'yes',
                ),
            )
        );

        $this->add_control(
            'button_background_color_hover',
            array(
                'label'     => __( 'Background Color', 'dynamic-site-maker' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .dsmk-button:hover' => 'background-color: {{VALUE}}',
                ),
                'condition' => array(
                    'show_affiliate_link' => 'yes',
                ),
            )
        );

        $this->add_control(
            'button_border_color_hover',
            array(
                'label'     => __( 'Border Color', 'dynamic-site-maker' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .dsmk-button:hover' => 'border-color: {{VALUE}}',
                ),
                'condition' => array(
                    'show_affiliate_link' => 'yes',
                    'button_border_border!' => '',
                ),
            )
        );

        $this->add_control(
            'button_hover_animation',
            array(
                'label'     => __( 'Hover Animation', 'dynamic-site-maker' ),
                'type'      => \Elementor\Controls_Manager::HOVER_ANIMATION,
                'condition' => array(
                    'show_affiliate_link' => 'yes',
                ),
            )
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'button_border_radius',
            array(
                'label'      => __( 'Border Radius', 'dynamic-site-maker' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', '%' ),
                'selectors'  => array(
                    '{{WRAPPER}} .dsmk-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
                'condition'  => array(
                    'show_affiliate_link' => 'yes',
                ),
                'separator'  => 'before',
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            array(
                'name'      => 'button_box_shadow',
                'selector'  => '{{WRAPPER}} .dsmk-button',
                'condition' => array(
                    'show_affiliate_link' => 'yes',
                ),
            )
        );

        $this->add_responsive_control(
            'button_padding',
            array(
                'label'      => __( 'Padding', 'dynamic-site-maker' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', 'em', '%' ),
                'selectors'  => array(
                    '{{WRAPPER}} .dsmk-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
                'condition'  => array(
                    'show_affiliate_link' => 'yes',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name'      => 'button_typography',
                'selector'  => '{{WRAPPER}} .dsmk-button',
                'condition' => array(
                    'show_affiliate_link' => 'yes',
                ),
            )
        );

        $this->add_responsive_control(
            'button_margin',
            array(
                'label'      => __( 'Margin', 'dynamic-site-maker' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', 'em', '%' ),
                'selectors'  => array(
                    '{{WRAPPER}} .dsmk-button-wrapper' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
                'condition'  => array(
                    'show_affiliate_link' => 'yes',
                ),
            )
        );

        $this->end_controls_section();

        // Animation section
        $this->start_controls_section(
            'animation_section',
            array(
                'label' => __( 'Animation', 'dynamic-site-maker' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'enable_animation',
            array(
                'label'        => __( 'Enable Animation', 'dynamic-site-maker' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => __( 'Yes', 'dynamic-site-maker' ),
                'label_off'    => __( 'No', 'dynamic-site-maker' ),
                'return_value' => 'yes',
                'default'      => 'no',
            )
        );

        $this->add_control(
            'animation_type',
            array(
                'label'     => __( 'Animation Type', 'dynamic-site-maker' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'default'   => 'fadeIn',
                'options'   => array(
                    'fadeIn'      => __( 'Fade In', 'dynamic-site-maker' ),
                    'fadeInDown'  => __( 'Fade In Down', 'dynamic-site-maker' ),
                    'fadeInUp'    => __( 'Fade In Up', 'dynamic-site-maker' ),
                    'fadeInLeft'  => __( 'Fade In Left', 'dynamic-site-maker' ),
                    'fadeInRight' => __( 'Fade In Right', 'dynamic-site-maker' ),
                    'zoomIn'      => __( 'Zoom In', 'dynamic-site-maker' ),
                    'bounceIn'    => __( 'Bounce In', 'dynamic-site-maker' ),
                    'slideInUp'   => __( 'Slide In Up', 'dynamic-site-maker' ),
                    'slideInDown' => __( 'Slide In Down', 'dynamic-site-maker' ),
                    'slideInLeft' => __( 'Slide In Left', 'dynamic-site-maker' ),
                    'slideInRight' => __( 'Slide In Right', 'dynamic-site-maker' ),
                ),
                'condition' => array(
                    'enable_animation' => 'yes',
                ),
            )
        );

        $this->add_control(
            'animation_delay',
            array(
                'label'      => __( 'Animation Delay (ms)', 'dynamic-site-maker' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array( 'px' ),
                'range'      => array(
                    'px' => array(
                        'min'  => 0,
                        'max'  => 1000,
                        'step' => 50,
                    ),
                ),
                'default'    => array(
                    'unit' => 'px',
                    'size' => 0,
                ),
                'condition'  => array(
                    'enable_animation' => 'yes',
                ),
            )
        );

        $this->add_control(
            'animation_duration',
            array(
                'label'      => __( 'Animation Duration (ms)', 'dynamic-site-maker' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array( 'px' ),
                'range'      => array(
                    'px' => array(
                        'min'  => 100,
                        'max'  => 2000,
                        'step' => 50,
                    ),
                ),
                'default'    => array(
                    'unit' => 'px',
                    'size' => 1000,
                ),
                'condition'  => array(
                    'enable_animation' => 'yes',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Render widget output
     */
    protected function render() {
        global $post;
        
        if ( ! $post ) {
            return;
        }

        $settings = $this->get_settings_for_display();
        
        // Get dynamic content
        $name = get_post_meta( $post->ID, '_dsmk_name', true );
        $logo_id = get_post_meta( $post->ID, '_dsmk_logo_id', true );
        $affiliate_link = get_post_meta( $post->ID, '_dsmk_affiliate_link', true );
        
        if ( empty( $name ) && empty( $logo_id ) && empty( $affiliate_link ) ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo '<div class="dsmk-notice">' . esc_html__( 'This widget will display Dynamic Site Maker data if available.', 'dynamic-site-maker' ) . '</div>';
            }
            return;
        }
        
        // Set animation attributes
        $animation_class = '';
        $animation_attrs = '';
        if ( 'yes' === $settings['enable_animation'] ) {
            $animation_class = ' animated ' . $settings['animation_type'];
            $animation_attrs = ' data-animation="' . esc_attr( $settings['animation_type'] ) . '"';
            $animation_attrs .= ' data-animation-delay="' . esc_attr( $settings['animation_delay']['size'] ) . '"';
            $animation_attrs .= ' data-animation-duration="' . esc_attr( $settings['animation_duration']['size'] ) . '"';
            
            // Add inline styles for animation duration
            echo '<style>
                .elementor-element-' . esc_attr( $this->get_id() ) . ' .animated {
                    animation-duration: ' . esc_attr( $settings['animation_duration']['size'] ) . 'ms;
                    animation-delay: ' . esc_attr( $settings['animation_delay']['size'] ) . 'ms;
                    visibility: hidden;
                }
                .elementor-element-' . esc_attr( $this->get_id() ) . ' .animated.animation-loaded {
                    visibility: visible;
                }
            </style>';
            
            // Add animation script
            echo '<script>
                jQuery(document).ready(function($) {
                    setTimeout(function() {
                        $(".elementor-element-' . esc_attr( $this->get_id() ) . ' .animated").addClass("animation-loaded");
                    }, 50);
                });
            </script>';
        }
        
        // Set container classes based on layout
        $container_classes = 'dsmk-info-widget';
        $container_classes .= ' dsmk-layout-' . $settings['layout'];
        $container_classes .= $animation_class;
        
        echo '<div class="' . esc_attr( $container_classes ) . '"' . $animation_attrs . '>';
        
        // Create layout based on selected option
        switch ( $settings['layout'] ) {
            case 'horizontal':
                echo '<div class="dsmk-horizontal-layout">';
                $this->render_logo( $settings, $logo_id, $affiliate_link );
                $this->render_name( $settings, $name );
                $this->render_button( $settings, $affiliate_link );
                echo '</div>';
                break;
                
            case 'centered':
                echo '<div class="dsmk-centered-layout">';
                $this->render_logo( $settings, $logo_id, $affiliate_link );
                $this->render_name( $settings, $name );
                $this->render_button( $settings, $affiliate_link );
                echo '</div>';
                break;
                
            case 'custom':
                echo '<div class="dsmk-custom-layout">';
                
                // Custom layout based on logo position
                if ( 'yes' === $settings['show_logo'] && ! empty( $logo_id ) && 'yes' === $settings['show_name'] && ! empty( $name ) ) {
                    switch ( $settings['logo_position'] ) {
                        case 'above':
                            $this->render_logo( $settings, $logo_id, $affiliate_link );
                            $this->render_name( $settings, $name );
                            break;
                            
                        case 'below':
                            $this->render_name( $settings, $name );
                            $this->render_logo( $settings, $logo_id, $affiliate_link );
                            break;
                            
                        case 'left':
                            echo '<div class="dsmk-flex-layout">';
                            $this->render_logo( $settings, $logo_id, $affiliate_link );
                            $this->render_name( $settings, $name );
                            echo '</div>';
                            break;
                            
                        case 'right':
                            echo '<div class="dsmk-flex-layout">';
                            $this->render_name( $settings, $name );
                            $this->render_logo( $settings, $logo_id, $affiliate_link );
                            echo '</div>';
                            break;
                            
                        default:
                            $this->render_logo( $settings, $logo_id, $affiliate_link );
                            $this->render_name( $settings, $name );
                            break;
                    }
                } else {
                    if ( 'yes' === $settings['show_logo'] && ! empty( $logo_id ) ) {
                        $this->render_logo( $settings, $logo_id, $affiliate_link );
                    }
                    
                    if ( 'yes' === $settings['show_name'] && ! empty( $name ) ) {
                        $this->render_name( $settings, $name );
                    }
                }
                
                if ( 'yes' === $settings['show_affiliate_link'] && ! empty( $affiliate_link ) ) {
                    $this->render_button( $settings, $affiliate_link );
                }
                
                echo '</div>';
                break;
                
            case 'vertical':
            default:
                $this->render_logo( $settings, $logo_id, $affiliate_link );
                $this->render_name( $settings, $name );
                $this->render_button( $settings, $affiliate_link );
                break;
        }
        
        echo '</div>';
    }
    
    /**
     * Render the name element
     *
     * @param array  $settings The widget settings.
     * @param string $name     The user name.
     */
    protected function render_name( $settings, $name ) {
        if ( 'yes' !== $settings['show_name'] || empty( $name ) ) {
            return;
        }
        
        $name_tag = ! empty( $settings['name_tag'] ) ? $settings['name_tag'] : 'h2';
        $prefix = ! empty( $settings['name_prefix'] ) ? $settings['name_prefix'] : '';
        $suffix = ! empty( $settings['name_suffix'] ) ? $settings['name_suffix'] : '';
        
        echo '<' . $name_tag . ' class="dsmk-name">' . esc_html( $prefix ) . esc_html( $name ) . esc_html( $suffix ) . '</' . $name_tag . '>';
    }
    
    /**
     * Render the logo element
     *
     * @param array  $settings       The widget settings.
     * @param int    $logo_id        The logo attachment ID.
     * @param string $affiliate_link The affiliate link URL.
     */
    protected function render_logo( $settings, $logo_id, $affiliate_link ) {
        if ( 'yes' !== $settings['show_logo'] || empty( $logo_id ) ) {
            return;
        }
        
        echo '<div class="dsmk-logo">';
        
        $hover_animation = '';
        if ( ! empty( $settings['button_hover_animation'] ) ) {
            $hover_animation = ' elementor-animation-' . $settings['button_hover_animation'];
        }
        
        if ( 'yes' === $settings['logo_link'] && ! empty( $affiliate_link ) ) {
            echo '<a href="' . esc_url( $affiliate_link ) . '" target="_blank" rel="nofollow" class="' . esc_attr( $hover_animation ) . '">';
        }
        
        echo wp_get_attachment_image( $logo_id, 'full' );
        
        if ( 'yes' === $settings['logo_link'] && ! empty( $affiliate_link ) ) {
            echo '</a>';
        }
        
        echo '</div>';
    }
    
    /**
     * Render the button element
     *
     * @param array  $settings       The widget settings.
     * @param string $affiliate_link The affiliate link URL.
     */
    protected function render_button( $settings, $affiliate_link ) {
        if ( 'yes' !== $settings['show_affiliate_link'] || empty( $affiliate_link ) ) {
            return;
        }
        
        $button_text = ! empty( $settings['button_text'] ) ? $settings['button_text'] : __( 'Visit Affiliate Link', 'dynamic-site-maker' );
        $hover_animation = '';
        
        if ( ! empty( $settings['button_hover_animation'] ) ) {
            $hover_animation = ' elementor-animation-' . $settings['button_hover_animation'];
        }
        
        echo '<div class="dsmk-button-wrapper">';
        echo '<a href="' . esc_url( $affiliate_link ) . '" class="dsmk-button' . esc_attr( $hover_animation ) . '" target="_blank" rel="nofollow">';
        
        // Add icon before text
        if ( ! empty( $settings['button_icon']['value'] ) && 'before' === $settings['icon_position'] ) {
            echo '<span class="dsmk-button-icon dsmk-button-icon-before">';
            \Elementor\Icons_Manager::render_icon( $settings['button_icon'], [ 'aria-hidden' => 'true' ] );
            echo '</span>';
        }
        
        echo '<span class="dsmk-button-text">' . esc_html( $button_text ) . '</span>';
        
        // Add icon after text
        if ( ! empty( $settings['button_icon']['value'] ) && 'after' === $settings['icon_position'] ) {
            echo '<span class="dsmk-button-icon dsmk-button-icon-after">';
            \Elementor\Icons_Manager::render_icon( $settings['button_icon'], [ 'aria-hidden' => 'true' ] );
            echo '</span>';
        }
        
        echo '</a>';
        echo '</div>';
    }
}
