<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class P1_Elementor_SEO_Pagination_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'p1_seo_pagination';
    }

    public function get_title() {
        return __( 'SEO Pagination', 'positie1-seo-pagination' );
    }

    public function get_icon() {
        return 'eicon-pagination';
    }

    public function get_categories() {
        return [ 'general' ];
    }

    public function get_style_depends() {
        return [ 'p1-seo-pagination-frontend' ];
    }

    protected function register_controls() {
        $this->start_controls_section('section_content', [
            'label' => __( 'Content', 'positie1-seo-pagination' ),
        ]);

        $this->add_control('query_id', [
            'label' => __( 'Query ID (optional)', 'positie1-seo-pagination' ),
            'type'  => \Elementor\Controls_Manager::TEXT,
            'default' => '',
        ]);

        $this->add_control('aria_label', [
            'label' => __( 'Aria label', 'positie1-seo-pagination' ),
            'type'  => \Elementor\Controls_Manager::TEXT,
            'default' => __( 'Pagination', 'positie1-seo-pagination' ),
        ]);

        $this->add_control('mid_size', [
            'label' => __( 'Mid size', 'positie1-seo-pagination' ),
            'type'  => \Elementor\Controls_Manager::NUMBER,
            'default' => 1,
            'min' => 0,
            'max' => 10,
        ]);

        $this->add_control('end_size', [
            'label' => __( 'End size', 'positie1-seo-pagination' ),
            'type'  => \Elementor\Controls_Manager::NUMBER,
            'default' => 1,
            'min' => 0,
            'max' => 10,
        ]);

        $this->add_control('prev_text', [
            'label' => __( 'Prev text', 'positie1-seo-pagination' ),
            'type'  => \Elementor\Controls_Manager::TEXT,
            'default' => '←',
        ]);

        $this->add_control('next_text', [
            'label' => __( 'Next text', 'positie1-seo-pagination' ),
            'type'  => \Elementor\Controls_Manager::TEXT,
            'default' => '→',
        ]);

        $this->add_control('class', [
            'label' => __( 'Wrapper class', 'positie1-seo-pagination' ),
            'type'  => \Elementor\Controls_Manager::TEXT,
            'default' => 'p1-seo-pagination',
        ]);

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        if ( wp_style_is( 'p1-seo-pagination-frontend', 'registered' ) ) {
            wp_enqueue_style( 'p1-seo-pagination-frontend' );
        }

        $atts = [
            'class'      => $settings['class'] ?? 'p1-seo-pagination',
            'aria_label' => $settings['aria_label'] ?? __( 'Pagination', 'positie1-seo-pagination' ),
            'mid_size'   => isset($settings['mid_size']) ? (int) $settings['mid_size'] : 1,
            'end_size'   => isset($settings['end_size']) ? (int) $settings['end_size'] : 1,
            'prev_text'  => $settings['prev_text'] ?? '←',
            'next_text'  => $settings['next_text'] ?? '→',
            'current_var'=> 'paged',
            'query_id'   => $settings['query_id'] ?? '',
        ];

        $attrs = [];
        if ( ! empty( $atts['query_id'] ) ) $attrs['data-query-id'] = (string) $atts['query_id'];
        $atts['attrs'] = $attrs;

        echo P1_SEO_Pagination_Core::render_pagination( $atts );
    }
}
