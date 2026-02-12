<?php
if ( ! defined( 'ABSPATH' ) ) exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

if ( ! class_exists( '\Elementor\Widget_Base' ) ) return;

class P1_Elementor_SEO_Pagination_Widget extends Widget_Base {

    public function get_name() { return 'p1-seo-pagination'; }
    public function get_title() { return __( 'SEO Pagination', 'positie1-seo-pagination' ); }
    public function get_icon() { return 'eicon-pagination'; }
    public function get_categories() { return [ 'general' ]; }
    public function get_keywords() { return [ 'seo', 'pagination', 'paging' ]; }

    protected function register_controls() {

        // Content
        $this->start_controls_section(
            'section_content',
            [
                'label' => __( 'Content', 'positie1-seo-pagination' ),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'query_id',
            [
                'label'       => __( 'Query ID', 'positie1-seo-pagination' ),
                'type'        => Controls_Manager::TEXT,
                'placeholder' => __( 'e.g. producten', 'positie1-seo-pagination' ),
                'description' => __( 'Optional: adds data-query-id to the pagination wrapper (useful for AJAX/filter integrations).', 'positie1-seo-pagination' ),
                'default'     => '',
            ]
        );

        $this->add_control(
            'aria_label',
            [
                'label'   => __( 'Aria label', 'positie1-seo-pagination' ),
                'type'    => Controls_Manager::TEXT,
                'default' => __( 'Pagination', 'positie1-seo-pagination' ),
            ]
        );

        $this->add_control(
            'mid_size',
            [
                'label'   => __( 'Mid size', 'positie1-seo-pagination' ),
                'type'    => Controls_Manager::NUMBER,
                'min'     => 0,
                'max'     => 9,
                'step'    => 1,
                'default' => 1,
            ]
        );

        $this->add_control(
            'end_size',
            [
                'label'   => __( 'End size', 'positie1-seo-pagination' ),
                'type'    => Controls_Manager::NUMBER,
                'min'     => 0,
                'max'     => 9,
                'step'    => 1,
                'default' => 1,
            ]
        );

        $this->add_control(
            'prev_text',
            [
                'label'   => __( 'Prev text', 'positie1-seo-pagination' ),
                'type'    => Controls_Manager::TEXT,
                'default' => '←',
            ]
        );

        $this->add_control(
            'next_text',
            [
                'label'   => __( 'Next text', 'positie1-seo-pagination' ),
                'type'    => Controls_Manager::TEXT,
                'default' => '→',
            ]
        );

        $this->end_controls_section();

        // Wrapper
        $this->start_controls_section(
            'section_style_wrapper',
            [
                'label' => __( 'Wrapper', 'positie1-seo-pagination' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'justify',
            [
                'label' => __( 'Alignment', 'positie1-seo-pagination' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'flex-start' => [ 'title' => __( 'Left', 'positie1-seo-pagination' ), 'icon' => 'eicon-text-align-left' ],
                    'center'     => [ 'title' => __( 'Center', 'positie1-seo-pagination' ), 'icon' => 'eicon-text-align-center' ],
                    'flex-end'   => [ 'title' => __( 'Right', 'positie1-seo-pagination' ), 'icon' => 'eicon-text-align-right' ],
                ],
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .p1-seo-pagination' => 'justify-content: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'gap',
            [
                'label'      => __( 'Gap', 'positie1-seo-pagination' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
                'default' => [ 'unit' => 'px', 'size' => 18 ],
                'selectors' => [
                    '{{WRAPPER}} .p1-seo-pagination' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Items (Normal)
        $this->start_controls_section(
            'section_style_items',
            [
                'label' => __( 'Items', 'positie1-seo-pagination' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'item_size',
            [
                'label'      => __( 'Size', 'positie1-seo-pagination' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 20, 'max' => 140 ] ],
                'default' => [ 'unit' => 'px', 'size' => 56 ],
                'selectors' => [
                    '{{WRAPPER}} .p1-seo-pagination .page-numbers' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; min-width: {{SIZE}}{{UNIT}}; min-height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'item_padding',
            [
                'label'      => __( 'Padding', 'positie1-seo-pagination' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .p1-seo-pagination .page-numbers' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'typography',
                'selector' => '{{WRAPPER}} .p1-seo-pagination .page-numbers',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'border',
                'selector' => '{{WRAPPER}} .p1-seo-pagination .page-numbers',
            ]
        );

        $this->add_responsive_control(
            'radius',
            [
                'label'      => __( 'Border radius', 'positie1-seo-pagination' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range'      => [
                    'px' => [ 'min' => 0, 'max' => 300 ],
                    '%'  => [ 'min' => 0, 'max' => 100 ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .p1-seo-pagination .page-numbers' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label'     => __( 'Text color', 'positie1-seo-pagination' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .p1-seo-pagination .page-numbers' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'bg_color',
            [
                'label'     => __( 'Background', 'positie1-seo-pagination' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .p1-seo-pagination .page-numbers' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'box_shadow',
                'selector' => '{{WRAPPER}} .p1-seo-pagination .page-numbers',
            ]
        );

        $this->end_controls_section();

        // Hover
        $this->start_controls_section(
            'section_style_hover',
            [
                'label' => __( 'Hover', 'positie1-seo-pagination' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'hover_text_color',
            [
                'label'     => __( 'Text color', 'positie1-seo-pagination' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .p1-seo-pagination a.page-numbers:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'hover_bg_color',
            [
                'label'     => __( 'Background', 'positie1-seo-pagination' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .p1-seo-pagination a.page-numbers:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'hover_border_color',
            [
                'label'     => __( 'Border color', 'positie1-seo-pagination' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .p1-seo-pagination a.page-numbers:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'hover_translate',
            [
                'label'      => __( 'Lift on hover', 'positie1-seo-pagination' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => -12, 'max' => 12 ] ],
                'default'    => [ 'unit' => 'px', 'size' => -1 ],
                'selectors'  => [
                    '{{WRAPPER}} .p1-seo-pagination a.page-numbers:hover' => 'transform: translateY({{SIZE}}{{UNIT}});',
                ],
            ]
        );

        $this->end_controls_section();

        // Current
        $this->start_controls_section(
            'section_style_current',
            [
                'label' => __( 'Current / Active', 'positie1-seo-pagination' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'current_text_color',
            [
                'label'     => __( 'Text color', 'positie1-seo-pagination' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .p1-seo-pagination .page-numbers.current' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'current_bg_color',
            [
                'label'     => __( 'Background', 'positie1-seo-pagination' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .p1-seo-pagination .page-numbers.current' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'current_border_color',
            [
                'label'     => __( 'Border color', 'positie1-seo-pagination' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .p1-seo-pagination .page-numbers.current' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Prev/Next
        $this->start_controls_section(
            'section_style_prevnext',
            [
                'label' => __( 'Prev / Next', 'positie1-seo-pagination' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'prevnext_text_color',
            [
                'label'     => __( 'Text color', 'positie1-seo-pagination' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .p1-seo-pagination a.page-numbers.prev, {{WRAPPER}} .p1-seo-pagination a.page-numbers.next' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'prevnext_bg_color',
            [
                'label'     => __( 'Background', 'positie1-seo-pagination' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .p1-seo-pagination a.page-numbers.prev, {{WRAPPER}} .p1-seo-pagination a.page-numbers.next' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'prevnext_border_color',
            [
                'label'     => __( 'Border color', 'positie1-seo-pagination' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .p1-seo-pagination a.page-numbers.prev, {{WRAPPER}} .p1-seo-pagination a.page-numbers.next' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Dots
        $this->start_controls_section(
            'section_style_dots',
            [
                'label' => __( 'Dots (…) ', 'positie1-seo-pagination' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'dots_text_color',
            [
                'label'     => __( 'Text color', 'positie1-seo-pagination' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .p1-seo-pagination .page-numbers.dots' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'dots_bg_color',
            [
                'label'     => __( 'Background', 'positie1-seo-pagination' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .p1-seo-pagination .page-numbers.dots' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'dots_border_color',
            [
                'label'     => __( 'Border color', 'positie1-seo-pagination' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .p1-seo-pagination .page-numbers.dots' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Focus / Pressed
        $this->start_controls_section(
            'section_style_focus_active',
            [
                'label' => __( 'Focus / Pressed (A11y)', 'positie1-seo-pagination' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'focus_outline_color',
            [
                'label'     => __( 'Focus outline color', 'positie1-seo-pagination' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .p1-seo-pagination a.page-numbers:focus-visible' => 'outline-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'focus_outline_width',
            [
                'label'      => __( 'Focus outline width', 'positie1-seo-pagination' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 8 ] ],
                'default'    => [ 'unit' => 'px', 'size' => 2 ],
                'selectors'  => [
                    '{{WRAPPER}} .p1-seo-pagination a.page-numbers:focus-visible' => 'outline-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'focus_outline_offset',
            [
                'label'      => __( 'Focus outline offset', 'positie1-seo-pagination' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => -2, 'max' => 12 ] ],
                'default'    => [ 'unit' => 'px', 'size' => 3 ],
                'selectors'  => [
                    '{{WRAPPER}} .p1-seo-pagination a.page-numbers:focus-visible' => 'outline-offset: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'pressed_scale',
            [
                'label'      => __( 'Pressed scale', 'positie1-seo-pagination' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ '' ],
                'range'      => [ '' => [ 'min' => 0.85, 'max' => 1.0, 'step' => 0.01 ] ],
                'default'    => [ 'size' => 0.98 ],
                'selectors'  => [
                    '{{WRAPPER}} .p1-seo-pagination a.page-numbers:active' => 'transform: scale({{SIZE}});',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $s = $this->get_settings_for_display();

        $attrs = [];
        if ( ! empty( $s['query_id'] ) ) {
            $attrs['data-query-id'] = (string) $s['query_id'];
        }

        $args = [
            'class'      => 'p1-seo-pagination',
            'aria_label' => (string) $s['aria_label'],
            'mid_size'   => (int) $s['mid_size'],
            'end_size'   => (int) $s['end_size'],
            'prev_text'  => (string) $s['prev_text'],
            'next_text'  => (string) $s['next_text'],
            'attrs'      => $attrs,
        ];

        echo P1_SEO_Pagination_Core::render_pagination( $args );
    }

    public function get_style_depends() {
        return [ 'p1-seo-pagination-frontend' ];
    }
}
