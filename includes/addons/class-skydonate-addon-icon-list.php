<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Icons_Manager;


class Skydonate_Icon_List extends Widget_Base {

    public function get_name() {
        return 'skyweb_donation_icon_list';
    }

    public function get_title() {
        return __('Donation Icon List', 'skydonate');
    }

    public function get_icon() {
        return 'eicon-navigation-horizontal';
    }

    public function get_categories() {
        return ['skydonate'];
    }

    public function get_style_depends() {
        return ['donation-icon-list', 'skydonate-swiper', 'donation-card', 'swiper-override'];
    }

    public function get_script_depends() {
        return ['donation-icon-list', 'skydonate-swiper'];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Icon List', 'skydonate'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new Repeater();

        // Content Tab for Donation Item, Icon, and Title
        $repeater->start_controls_tabs('donation_tabs');

        // Content Tab
        $repeater->start_controls_tab(
            'content_tab',
            [
                'label' => __('Content', 'skydonate'),
            ]
        );

        $repeater->add_control(
            'donation_item',
            [
                'label' => __('Donation Item', 'skydonate'),
                'type' => Controls_Manager::SELECT,
                'options' => Skydonate_Functions::Get_Title('product', 'ids'), // Ensure this returns product IDs
                'default' => '',
            ]
        );


        $repeater->add_control(
            'action_status',
            [
                'label' => esc_html__( 'Action Status', 'skydonate' ),
                'type' => Controls_Manager::SELECT,
                'default' => 'link',
                'options' => [
                    'link'  => __( 'Link', 'skydonate' ),
                    'popup'  => __( 'Popup', 'skydonate' ),
                ]
            ]
        );

        $repeater->add_control(
            'donation_icon',
            [
                'label' => __('Icon', 'skydonate'),
                'type' => Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-donate',
                    'library' => 'fa-solid',
                ],
            ]
        );

        $repeater->add_control(
            'donation_title',
            [
                'label' => __('Title', 'skydonate'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Donation Item', 'skydonate'),
                'label_block' => true,
            ]
        );

        $repeater->end_controls_tab(); // End Content Tab

        // Style Tab for Icon Color, Icon Background, and Title Color
        $repeater->start_controls_tab(
            'style_tab',
            [
                'label' => __('Style', 'skydonate'),
            ]
        );

        $repeater->add_control(
            'icon_color',
            [
                'label' => __('Icon Color', 'skydonate'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} {{CURRENT_ITEM}} .donation-icon' => 'color: {{VALUE}};',
                ],
            ]
        );

        $repeater->add_control(
            'icon_hover_color',
            [
                'label' => __('Icon Hover Color', 'skydonate'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} {{CURRENT_ITEM}}:hover .donation-icon' => 'color: {{VALUE}};',
                ],
            ]
        );

        $repeater->add_control(
            'icon_background',
            [
                'label' => __('Icon Background', 'skydonate'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} {{CURRENT_ITEM}} .donation-icon' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $repeater->add_control(
            'icon_hover_background',
            [
                'label' => __('Icon Hover Background', 'skydonate'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} {{CURRENT_ITEM}}:hover .donation-icon' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $repeater->add_control(
            'title_color',
            [
                'label' => __('Title Color', 'skydonate'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} {{CURRENT_ITEM}} .donation-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $repeater->end_controls_tab(); // End Style Tab

        $repeater->end_controls_tabs();

        $this->add_control(
            'donation_icon_list',
            [
                'label' => __('Donation List', 'skydonate'),
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'donation_title' => __('Donation Item #1', 'skydonate'),
                    ],
                ],
                'title_field' => '{{{ donation_title }}}',
            ]
        );


        $this->add_control(
            'show_secure_donation',
            [
                'label' => __('Show Secure Badge', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'skydonate'),
                'label_off' => __('Hide', 'skydonate'),
                'return_value' => 'yes',
                'default' => 'yes',
                'condition' => [
                    'target_donation!' => '',
                ],
            ]
        );
        
        $this->add_control(
            'secure_donation_text',
            [
                'label' => __('Secure Donation Text', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Secure Donation', 'skydonate'),
                'placeholder' => __('Enter secure donation text', 'skydonate'),
                'condition' => [
                    'target_donation!' => '',
                    'show_secure_donation' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'secure_donation_icon',
            [
                'label' => __('Secure Donation Icon', 'skydonate'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-lock',
                    'library' => 'solid',
                ],
                'condition' => [
                    'target_donation!' => '',
                    'show_secure_donation' => 'yes',
                ],
            ]
        );

                                                                                                                                                       
        $this->add_control(                                                                                                                                     
            'slider_on',                                                                                                                                        
            [                                                                                                                                                   
                'label'         => __( 'Slider', 'skydonate' ),                                                                                            
                'type'          => Controls_Manager::SWITCHER,                                                                                                  
                'label_on'      => __( 'On', 'skydonate' ),                                                                                                
                'label_off'     => __( 'Off', 'skydonate' ),                                                                                               
                'return_value'  => 'yes',                                                                                                                       
                'default'       => 'yes',                                                                                                                       
            ]                                                                                                                                                   
        );
    
        $this->add_control(
            'item_column',
            [
                'label' => __( 'Column', 'skydonate' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    '1grid' => [
                        'title' => __( 'One Column', 'skydonate' ),
                        'icon' => 'icon-grid-1',
                    ],
                    '2grid' => [
                        'title' => __( 'Two Columns', 'skydonate' ),
                        'icon' => 'icon-grid-2',
                    ],
                    '3grid' => [
                        'title' => __( 'Three Columns', 'skydonate' ),
                        'icon' => 'icon-grid-3',
                    ],
                    '4grid' => [
                        'title' => __( 'Four Columns', 'skydonate' ),
                        'icon' => 'icon-grid-4',
                    ],
                ],
                'default' => '3grid',
                'toggle' => true,
                'condition' => [
                    'slider_on!' => 'yes',
                ]
            ]
        );
    
        $this->add_control(
            'grid_space',
            [
                'label' => esc_html__( 'Grid Space', 'skydonate' ),
                'type' => Controls_Manager::SELECT,
                'default' => 'g-4',
                'options' => [
                    'g-1'  => __( 'One', 'skydonate' ),
                    'g-2'  => __( 'Two', 'skydonate' ),
                    'g-3'  => __( 'Three', 'skydonate' ),
                    'g-4'  => __( 'Four', 'skydonate' ),
                    'g-5'  => __( 'Five', 'skydonate' ),
                ],
                'condition' => [
                    'slider_on!' => 'yes',
                ]
            ]
        );

        $this->end_controls_section(); 
            
        $this->start_controls_section(                                                                                                                              
            'slider_option',                                                                                                                                        
            [                                                                                                                                                       
                'label' => esc_html__( 'Slider Option', 'skydonate' ),                                                                                                
                'condition'=>[                                                                                                                                      
                    'slider_on'=>'yes',                                                                                                                             
                ]                                                                                                                                                   
            ]                                                                                                                                                       
        );     

        $this->add_control(                                                                                                                                     
            'sl_navigation',                                                                                                                                    
            [                                                                                                                                                   
                'label' => esc_html__( 'Navigation', 'skydonate' ),                                                                                                    
                'type' => Controls_Manager::SWITCHER,                                                                                                           
                'return_value' => 'yes',                                                                                                                        
                'default' => 'yes',                                                                                                                             
            ]                                                                                                                                                   
        );                                                                                                                                                      
                                                                                                                                                             
                 
        $this->add_control(
            'sl_nav_prev_icon',
            [
                'label' => __('Previus Icon', 'skydonate'),
                'type' => Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-angle-left',
                    'library' => 'fa-solid',
                ],                                                                                                              
                'condition'=>[                                                                                                                                  
                    'sl_navigation'=>'yes',                                                                                                                
                ]                
            ]
        );                                                                                                                                                 
                    
        $this->add_control(
            'sl_nav_next_icon',
            [
                'label' => __('Next Icon', 'skydonate'),
                'type' => Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-angle-right',
                    'library' => 'fa-solid',
                ],                                                                                                              
                'condition'=>[                                                                                                                                  
                    'sl_navigation'=>'yes',                                                                                                                
                ]                
            ]
        );                                                                                                                                                   
                                                                                                                                                                
        $this->add_control(                                                                                                                                     
            'slpaginate',                                                                                                                                       
            [                                                                                                                                                   
                'label' => esc_html__( 'Paginate', 'skydonate' ),                                                                                                 
                'type' => Controls_Manager::SWITCHER,                                                                                                           
                'return_value' => 'yes',                                                                                                                        
                'default' => 'no',                                                                                                          
                'separator' => 'before',                                                                                                                           
            ]                                                                                                                                                   
        );     

        $this->add_responsive_control(
            'slscrollbar_desktop',
            [
                'label' => esc_html__('Scrollbar', 'skydonate'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'block' => esc_html__('Show', 'skydonate'),  // Option to display block
                    'none'  => esc_html__('Hide', 'skydonate'),  // Option to hide
                ],
                'default' => 'block',  // Default value: Show
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .swiper-scrollbar' => 'display: {{VALUE}};',
                ]
            ]
        );
        

        $this->add_control(                                                                                                                                     
            'sleffect',                                                                                                                                         
            [                                                                                                                                                   
                'label' => esc_html__( 'Effect', 'skydonate' ),                                                                                                   
                'type' => Controls_Manager::SELECT,                                                                                                             
                'default' => 'slide',                                                                                                       
                'separator' => 'before',                                                                                                                           
                'options' => [                                                                                                                                  
                    'slide'  => __( 'Slide', 'skydonate' ),                                                                                                       
                    'fade'  => __( 'Fade', 'skydonate' ),                                                                                                         
                    'cube'  => __( 'Cube', 'skydonate' ),                                                                                                         
                    'coverflow'  => __( 'Coverflow', 'skydonate' ),                                                                                               
                    'flip'  => __( 'Flip', 'skydonate' ),                                                                                                         
                ],                                                                                                                                              
            ]                                                                                                                                                   
        );   

        $this->add_control(                                                                                                                                     
            'coverflow_option_heading',                                                                                                                                   
            [                                                                                                                                                   
                'label' => __( 'Coverflow Options', 'skydonate' ),                                                                                                           
                'type' => Controls_Manager::HEADING,                                                                                                            
                'separator' => 'before',   
                'condition' => [
                    'sleffect' => 'coverflow',
                ]                                                                                                                      
            ]                                                                                                                                                   
        );            
                                                                                                                                                                            
        $this->add_control(                                                                                                                                     
            'coverflow_rotate',                                                                                                                                
            [                                                                                                                                                   
                'label' => __('Rotate', 'skydonate'),                                                                                                       
                'type' => Controls_Manager::NUMBER,                                                                                                             
                'min' => 0,                                                                                                                                     
                'max' => 360,                                                                                                                                     
                'step' => 1,                                                                                                                                    
                'default' => 0,      
                'condition' => [
                    'sleffect' => 'coverflow',
                ]                                                                                                                                 
            ]                                                                                                                                                   
        );                                                                                                                                                      
                                                                                                                                                                                               
        $this->add_control(                                                                                                                                     
            'coverflow_stretch',                                                                                                                                
            [                                                                                                                                                   
                'label' => __('Stretch', 'skydonate'),                                                                                                       
                'type' => Controls_Manager::NUMBER,                                                                                                             
                'min' => 0,                                                                                                                                     
                'max' => 9999,                                                                                                                                     
                'step' => 1,                                                                                                                                    
                'default' => 0,      
                'condition' => [
                    'sleffect' => 'coverflow',
                ]                                                                                                                                 
            ]                                                                                                                                                   
        );              
                                                                                                                                                                 
        $this->add_control(                                                                                                                                     
            'coverflow_depth',                                                                                                                                
            [                                                                                                                                                   
                'label' => __('Depth', 'skydonate'),                                                                                                       
                'type' => Controls_Manager::NUMBER,                                                                                                             
                'min' => 0,                                                                                                                                     
                'max' => 9999,                                                                                                                                     
                'step' => 1,                                                                                                                                    
                'default' => 0,      
                'condition' => [
                    'sleffect' => 'coverflow',
                ],                                                                                                                                          
            ]                                                                                                                                                   
        );                                                                                                                                                                  
        $this->add_control(                                                                                                                                     
            'coverflow_shadow',                                                                                                                                           
            [                                                                                                                                                   
                'label' => esc_html__( 'Shadow', 'skydonate' ),                                                                                                     
                'type' => Controls_Manager::SWITCHER,                                                                                                           
                'return_value' => 'yes',                                                                                                                        
                'default' => 'no',                                                                                   
                'separator' => 'after',                                                                                                                         
            ]                                                                                                                                                   
        );                                                                                                                                                                    
        $this->add_control(                                                                                                                                     
            'slloop',                                                                                                                                           
            [                                                                                                                                                   
                'label' => esc_html__( 'Loop', 'skydonate' ),                                                                                                     
                'type' => Controls_Manager::SWITCHER,                                                                                                           
                'return_value' => 'yes',                                                                                                                        
                'default' => 'yes',                                                                                                                             
            ]                                                                                                                                                   
        );  

        $this->add_control(                                                                                                                                     
            'slautolay',                                                                                                                                        
            [                                                                                                                                                   
                'label' => esc_html__( 'Autoplay', 'skydonate' ),                                                                                                 
                'type' => Controls_Manager::SWITCHER,                                                                                                           
                'return_value' => 'yes',                                                                                                                        
                'default' => 'yes',                                                                                                                             
            ]                                                                                                                                                   
        );                                                                                                                                                      
                                                                                                                                                                
        $this->add_control(
            'slautolaydelay',
            [
                'label' => __('Autoplay Delay', 'skydonate'),
                'type' => Controls_Manager::NUMBER,
                'default' => 6500,
            ]
        );

        $this->add_control(
            'slfreemode',
            [
                'label' => esc_html__( 'Free Mode', 'skydonate' ),
                'type' => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        $this->add_control(
            'slcenter',                                                                                                                                         
            [                                                                                                                                                   
                'label' => esc_html__( 'Center', 'skydonate' ),                                                                                                   
                'type' => Controls_Manager::SWITCHER,                                                                                                           
                'return_value' => 'yes',                                                                                                                        
                'default' => 'no',                                                                                                                              
            ]                                                                                                                                                   
        );                                                                                                                                                      
                                                                                                                                                                
                                                                                                                                                                
        $this->add_control(                                                                                                                                     
            'sldisplay_columns',                                                                                                                                
            [                                                                                                                                                   
                'label' => __('Slider Per View', 'skydonate'),                                                                                                       
                'type' => Controls_Manager::NUMBER,                                                                                                             
                'min' => 1,                                                                                                                                     
                'max' => 8,                                                                                                                                     
                'step' => 1,                                                                                                                                    
                'default' => 1,                                                                                                                                 
            ]                                                                                                                                                   
        );                                                                                                                                                      
                                                                                                                                                                
        $this->add_control(                                                                                                                                     
            'slcenter_padding',                                                                                                                                 
            [                                                                                                                                                   
                'label' => __( 'Center padding', 'skydonate' ),                                                                                                   
                'type' => Controls_Manager::NUMBER,                                                                                                             
                'min' => 0,                                                                                                                                     
                'max' => 500,                                                                                                                                   
                'step' => 1,                                                                                                                                    
                'default' => 30,                                                                                                                                
            ]                                                                                                                                                   
        );                                                                                                                                                      
                                                                                                                                                                
        $this->add_control(                                                                                                                                     
            'slanimation_speed',                                                                                                                                
            [                                                                                                                                                   
                'label' => __('Slide Speed', 'skydonate'),                                                                                                        
                'type' => Controls_Manager::NUMBER,                                                                                                             
                'default' => 1000,                                                                                                                              
            ]                                                                                                                                                   
        );                                                                                                                                                   
        $this->add_control(                                                                                                                                     
            'sldirection',                                                                                                                                         
            [                                                                                                                                                   
                'label' => esc_html__( 'Direction', 'skydonate' ),                                                                                                   
                'type' => Controls_Manager::SELECT,                                                                                                             
                'default' => 'horizontal',                                                                                                                           
                'options' => [                                                                                                                                  
                    'horizontal'  => __( 'horizontal', 'skydonate' ),                                                                                                       
                    'vertical'  => __( 'vertical', 'skydonate' ),                                                                                                       
                ],                                                                                                                                              
            ]                                                                                                                                                   
        );        
    
        $this->add_responsive_control(
            'slider_height',
            [
                'label' => __( 'Height', 'skydonate' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'vh', 'vw' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                    'vh' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                    'vw' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .swiper-container' => 'height: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'sldirection' => 'vertical',
                ]
            ]
        );                                                                                                                                                  
                                                                                                                                                                
        $this->add_control(                                                                                                                                     
            'heading_laptop',                                                                                                                                   
            [                                                                                                                                                   
                'label' => __( 'Laptop', 'skydonate' ),                                                                                                           
                'type' => Controls_Manager::HEADING,                                                                                                            
                'separator' => 'after',                                                                                                                         
            ]                                                                                                                                                   
        );                                                                                                                                                      
                                                                                                                                                                
        $this->add_control(                                                                                                                                     
            'sllaptop_width',                                                                                                                                   
            [                                                                                                                                                   
                'label' => __('Laptop Resolution', 'skydonate'),                                                                                                  
                'description' => __('The resolution to laptop.', 'skydonate'),                                                                                    
                'type' => Controls_Manager::NUMBER,                                                                                                             
                'default' => 1200,                                                                                                                              
            ]                                                                                                                                                   
        );                                                                                                                                                      
                                                                                                                                                                
        $this->add_control(                                                                                                                                     
            'sllaptop_display_columns',                                                                                                                         
            [                                                                                                                                                   
                'label' => __('Slider Per View', 'skydonate'),                                                                                                       
                'type' => Controls_Manager::NUMBER,                                                                                                             
                'min' => 1,                                                                                                                                     
                'max' => 8,                                                                                                                                     
                'step' => 1,                                                                                                                                    
                'default' => 3,                                                                                                                                 
            ]                                                                                                                                                   
        );                                                                                                                                                      
                                                                                                                                                                
        $this->add_control(                                                                                                                                     
            'sllaptop_padding',                                                                                                                                 
            [                                                                                                                                                   
                'label' => __( 'Center padding', 'skydonate' ),                                                                                                   
                'type' => Controls_Manager::NUMBER,                                                                                                             
                'min' => 0,                                                                                                                                     
                'max' => 500,                                                                                                                                   
                'step' => 1,                                                                                                                                    
                'default' => 30,                                                                                                                                
            ]                                                                                                                                                   
        );                                                                                                                                                      
                                                                                                                                                                
        $this->add_control(                                                                                                                                     
            'heading_tablet',                                                                                                                                   
            [                                                                                                                                                   
                'label' => __( 'Tablet', 'skydonate' ),                                                                                                           
                'type' => Controls_Manager::HEADING,                                                                                                            
                'separator' => 'after',                                                                                                                         
            ]                                                                                                                                                   
        );                                                                                                                                                      
                                                                                                                                                                
        $this->add_control(                                                                                                                                     
            'sltablet_width',                                                                                                                                   
            [                                                                                                                                                   
                'label' => __('Tablet Resolution', 'skydonate'),                                                                                                  
                'description' => __('The resolution to tablet.', 'skydonate'),                                                                                    
                'type' => Controls_Manager::NUMBER,                                                                                                             
                'default' => 992,                                                                                                                               
            ]                                                                                                                                                   
        );                                                                                                                                                      
                                                                                                                                                                
        $this->add_control(                                                                                                                                     
            'sltablet_display_columns',                                                                                                                         
            [                                                                                                                                                   
                'label' => __('Slider Per View', 'skydonate'),                                                                                                       
                'type' => Controls_Manager::NUMBER,                                                                                                             
                'min' => 1,                                                                                                                                     
                'max' => 8,                                                                                                                                     
                'step' => 1,                                                                                                                                    
                'default' => 2,                                                                                                                                 
            ]                                                                                                                                                   
        );                                                                                                                                                      
                                                                                                                                                                
        $this->add_control(                                                                                                                                     
            'sltablet_padding',                                                                                                                                 
            [                                                                                                                                                   
                'label' => __( 'Center padding', 'skydonate' ),                                                                                                   
                'type' => Controls_Manager::NUMBER,                                                                                                             
                'min' => 0,                                                                                                                                     
                'max' => 768,                                                                                                                                   
                'step' => 1,                                                                                                                                    
                'default' => 30,                                                                                                                                
            ]                                                                                                                                                   
        );                                                                                                                                                      
                                                                                                                                                                
        $this->add_control(                                                                                                                                     
            'heading_mobile',                                                                                                                                   
            [                                                                                                                                                   
                'label' => __( 'Mobile Phone', 'skydonate' ),                                                                                                     
                'type' => Controls_Manager::HEADING,                                                                                                            
                'separator' => 'after',                                                                                                                         
            ]                                                                                                                                                   
        );                                                                                                                                                      
                                                                                                                                                                
        $this->add_control(                                                                                                                                     
            'slmobile_width',                                                                                                                                   
            [                                                                                                                                                   
                'label' => __('Mobile Resolution', 'skydonate'),                                                                                                  
                'description' => __('The resolution to mobile.', 'skydonate'),                                                                                    
                'type' => Controls_Manager::NUMBER,                                                                                                             
                'default' => 768,                                                                                                                               
            ]                                                                                                                                                   
        );                                                                                                                                                      
                                                                                                                                                                
        $this->add_control(                                                                                                                                     
            'slmobile_display_columns',                                                                                                                         
            [                                                                                                                                                   
                'label' => __('Slider Per View', 'skydonate'),                                                                                                       
                'type' => Controls_Manager::NUMBER,                                                                                                             
                'min' => 1,                                                                                                                                     
                'max' => 4,                                                                                                                                     
                'step' => 1,                                                                                                                                    
                'default' => 1,                                                                                                                                 
            ]                                                                                                                                                   
        );                                                                                                                                                      
                                                                                                                                                                
        $this->add_control(                                                                                                                                     
            'slmobile_padding',                                                                                                                                 
            [                                                                                                                                                   
                'label' => __( 'Center padding', 'skydonate' ),                                                                                                   
                'type' => Controls_Manager::NUMBER,                                                                                                             
                'min' => 0,                                                                                                                                     
                'max' => 500,                                                                                                                                   
                'step' => 1,                                                                                                                                    
                'default' => 30,                                                                                                                                
            ]                                                                                                                                                   
        );   
        
        $this->end_controls_section();
        
        $this->Donation_Form_Button_Control();

        // Icon Style Tab
        $this->start_controls_section(
            'icon_style_section',
            [
                'label' => __('Icon', 'skydonate'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->start_controls_tabs('icon_style_tabs');

        // Normal Icon Style
        $this->start_controls_tab('icon_normal_tab', ['label' => __('Normal', 'skydonate')]);

        $this->add_control('icon_color', [
            'label' => __('Color', 'skydonate'),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .donation-icon' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_control('icon_background', [
            'label' => __('Background', 'skydonate'),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .donation-icon' => 'background-color: {{VALUE}};',
            ],
        ]);

        $this->add_responsive_control('icon_width', [
            'label' => __('Width', 'skydonate'),
            'type' => Controls_Manager::SLIDER,
            'size_units' => ['px', '%'],
            'selectors' => [
                '{{WRAPPER}} .donation-icon' => 'width: {{SIZE}}{{UNIT}};',
            ],
        ]);
        
        $this->add_responsive_control('icon_svg_width', [
            'label' => __('SVG Width', 'skydonate'),
            'type' => Controls_Manager::SLIDER,
            'size_units' => ['px', '%'],
            'selectors' => [
                '{{WRAPPER}} .donation-icon svg' => 'width: {{SIZE}}{{UNIT}};',
            ],
        ]);

        $this->add_responsive_control('icon_height', [
            'label' => __('Height', 'skydonate'),
            'type' => Controls_Manager::SLIDER,
            'size_units' => ['px', '%'],
            'selectors' => [
                '{{WRAPPER}} .donation-icon' => 'height: {{SIZE}}{{UNIT}};',
            ],
        ]);

        $this->add_group_control(Group_Control_Border::get_type(), [
            'name' => 'icon_border',
            'selector' => '{{WRAPPER}} .donation-icon',
        ]);

        $this->add_control('icon_border_radius', [
            'label' => __('Border Radius', 'skydonate'),
            'type' => Controls_Manager::SLIDER,
            'size_units' => ['px', '%'],
            'selectors' => [
                '{{WRAPPER}} .donation-icon' => 'border-radius: {{SIZE}}{{UNIT}};',
            ],
        ]);

        $this->add_group_control(Group_Control_Box_Shadow::get_type(), [
            'name' => 'icon_box_shadow',
            'selector' => '{{WRAPPER}} .donation-icon',
        ]);

        $this->end_controls_tab();

        // Hover Icon Style
        $this->start_controls_tab('icon_hover_tab', ['label' => __('Hover', 'skydonate')]);

        $this->add_control('icon_hover_color', [
            'label' => __('Hover Color', 'skydonate'),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .donation-icon:hover' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_control('icon_hover_background', [
            'label' => __('Hover Background', 'skydonate'),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .donation-icon:hover' => 'background-color: {{VALUE}};',
            ],
        ]);

        $this->add_group_control(Group_Control_Box_Shadow::get_type(), [
            'name' => 'icon_hover_shadow',
            'selector' => '{{WRAPPER}} .donation-icon:hover',
        ]);

        $this->add_control('icon_hover_border_color', [
            'label' => __('Hover Border Color', 'skydonate'),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .donation-icon:hover' => 'border-color: {{VALUE}};',
            ],
        ]);

        $this->end_controls_tab();

        $this->end_controls_tabs();
        $this->end_controls_section();

        // Title Style Tab
        $this->start_controls_section(
            'title_style_section',
            [
                'label' => __('Title', 'skydonate'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control('title_color', [
            'label' => __('Color', 'skydonate'),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .donation-title' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_group_control(Group_Control_Typography::get_type(), [
            'name' => 'title_typography',
            'selector' => '{{WRAPPER}} .donation-title',
        ]);

        $this->add_group_control(Group_Control_Text_Shadow::get_type(), [
            'name' => 'title_text_shadow',
            'selector' => '{{WRAPPER}} .donation-title',
        ]);

        $this->add_responsive_control('title_padding', [
            'label' => __('Padding', 'skydonate'),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', '%', 'em'],
            'selectors' => [
                '{{WRAPPER}} .donation-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->add_responsive_control('title_margin', [
            'label' => __('Margin', 'skydonate'),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', '%', 'em'],
            'selectors' => [
                '{{WRAPPER}} .donation-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->end_controls_section();
    }

    protected function render( $instance = [] ) {      
        $column = "col-lg-4 col-md-6";                                                                                                   
        $settings = $this->get_settings_for_display();                                                                                         
        
        // Set base wrapper attributes                                                                                                                                   
        $this->add_render_attribute( 'wrapper_attributes', 'class', 'skydonate-icon-list' );      
        
        // Handle slider settings
        if (!empty($settings['slider_on']) && $settings['slider_on'] === 'yes') {                                                                                                                      
            $this->add_render_attribute( 'wrapper_attributes', 'class', 'swiper-container' );                                                                       
            $slider_settings = [                                                                                                                                    
                'sl_effect' => $settings['sl_effect'] ?? 'slide',                                                                                                                
                'sldirection' => $settings['sldirection'] ?? 'horizontal',                                                                                                               
                'slloop' => !empty($settings['slloop']) && $settings['slloop'] === 'yes',                                                                                            
                'slpaginate' => !empty($settings['slpaginate']) && $settings['slpaginate'] === 'yes',                                                                                  
                'slautolay' => !empty($settings['slautolay']) && $settings['slautolay'] === 'yes',
                'slautolaydelay' => absint($settings['slautolaydelay'] ?? 0),
                'slfreemode' => !empty($settings['slfreemode']) && $settings['slfreemode'] === 'yes',
                'slanimation_speed' => absint($settings['slanimation_speed'] ?? 0),   
                'coverflow_rotate' => absint($settings['coverflow_rotate'] ?? 0),    
                'coverflow_stretch' => absint($settings['coverflow_stretch'] ?? 0),    
                'coverflow_depth' => absint($settings['coverflow_depth'] ?? 0),                                                                                                   
                'coverflow_shadow' => $settings['coverflow_shadow'] ?? '',  
                'sldisplay_columns' => $settings['sldisplay_columns'] ?? 0,                                                                                              
                'slcenter' => !empty($settings['slcenter']) && $settings['slcenter'] === 'yes',                                                                                           
                'slcenter_padding' => $settings['slcenter_padding'] ?? '',                                                                                                
                'laptop_width' => $settings['sllaptop_width'] ?? '',                                                                                                      
                'laptop_padding' => $settings['sllaptop_padding'] ?? '',                                                                                                  
                'laptop_display_columns' => $settings['sllaptop_display_columns'] ?? 0,                                                                                  
                'tablet_width' => $settings['sltablet_width'] ?? '',                                                                                                      
                'tablet_padding' => $settings['sltablet_padding'] ?? '',                                                                                                  
                'tablet_display_columns' => $settings['sltablet_display_columns'] ?? 0,                                                                                  
                'mobile_width' => $settings['slmobile_width'] ?? '',                                                                                                      
                'mobile_padding' => $settings['slmobile_padding'] ?? '',                                                                                                  
                'mobile_display_columns' => $settings['slmobile_display_columns'] ?? 0,                                                                                  
            ];                                                                                                                                                      
            $this->add_render_attribute( 'wrapper_attributes', 'data-settings', wp_json_encode( $slider_settings ) );    
            $this->add_render_attribute( 'wrapper_attributes', 'style', 'display: none;' );
        } else {
            $this->add_render_attribute( 'wrapper_attributes', 'class', ['row', esc_attr($settings['grid_space'] ?? 'default-spacing')] );
            
            // Adjust column classes based on item column setting
            switch ($settings['item_column'] ?? '') {
                case "1grid":
                    $column = "col-lg-12";
                    break;
                case "2grid":
                    $column = "col-lg-6 col-md-12";
                    break;
                case "3grid":
                    $column = "col-4";
                    break;
                default:
                    $column = "col-xl-3 col-lg-4 col-md-6";
            }
        }
    
        if (!empty($settings['donation_icon_list'])) {
            echo '<div ' . $this->get_render_attribute_string( "wrapper_attributes" ) . '>';
    
            if (!empty($settings['slider_on']) && $settings['slider_on'] === 'yes') {
                echo '<div class="swiper-wrapper">';
                foreach ($settings['donation_icon_list'] as $item) {
                    echo '<div class="swiper-slide">';
                    $this->card_content($settings, $item);                                                                                                          
                    echo '</div>';
                }
                echo '</div>';
                if (!empty($settings['sl_navigation'])) {
                    echo '<div class="swiper-navigation">';
                        echo '<div class="swiper-arrow swiper-prev">';
                            Icons_Manager::render_icon($settings['sl_nav_prev_icon'], ['aria-hidden' => 'true']);
                        echo '</div>';
                        echo '<div class="swiper-arrow swiper-next">';
                            Icons_Manager::render_icon($settings['sl_nav_next_icon'], ['aria-hidden' => 'true']);
                        echo '</div>';
                    echo '</div>';
                }
                if (!empty($settings['slpaginate'])) {
                    echo '<div class="swiper-pagination"></div>';
                }
                echo '<div class="swiper-scrollbar"></div>';
            } else {
                foreach ($settings['donation_icon_list'] as $item) {
                    $this->card_content($settings, $item);
                }
            }
            echo '</div>';
        }    
        
        foreach ($settings['donation_icon_list'] as $item) {
            if($item['action_status'] == 'popup'){
                $this->modal_content($settings, $item);
            }
        }                                                                                                                                       
    }
    
    public function card_content($settings, $item) {
        $widget_id = $this->get_id();                   
        $product_id = $item['donation_item'] ?? null;
        if (empty($product_id)) {
            return;
        }
        $action = $item['action_status'] ?? 'link';
        echo '<div class="skydonate-icon-item">';
            if($action === 'link'){
                $product_url = esc_url(get_permalink($product_id));
                echo '<a href="' . $product_url . '" class="icon-action">';
            } else {
                echo '<div class="icon-action donation-modal-button" data-target="'.$widget_id.'-'.$product_id.'" >';
            }
            if (!empty($item['donation_icon']['value'])) {
                echo '<span class="donation-icon" style="color:' . esc_attr($item['icon_color'] ?? '') . '; background-color:' . esc_attr($item['icon_background'] ?? '') . ';">';
                Icons_Manager::render_icon($item['donation_icon'], ['aria-hidden' => 'true']);
                echo '</span>';
            }
            // Render title with link to product page
            echo '<h4 class="donation-title" style="color:' . esc_attr($item['title_color'] ?? '') . ';">';
            echo esc_html($item['donation_title'] ?? get_the_title($product_id)); // Fallback to product title
            echo '</h4>';
            if ($action === 'link') {
                echo '</a>';
            } else {
                echo '</div>';
            }
        echo '</div>';
    }

    public function modal_content($settings, $item){
        $product_id = $item['donation_item'] ?? null;
        $widget_id = $this->get_id();                   
        
        if (empty($product_id)) {
            return;
        }

        $secure_donation_text = esc_html($settings['secure_donation_text'] ?? __('Secure Donation', 'skydonate'));
        $secure_donation_icon = $settings['secure_donation_icon'] ?? null;
        $show_secure_donation = !empty($settings['show_secure_donation']) && $settings['show_secure_donation'] === 'yes';

        echo '<div class="quick-modal" id="'.esc_attr($widget_id.'-'.$product_id).'" >';
        echo '<div class="quick-modal-overlay"></div>';
        echo '<div class="quick-modal-body">';
        echo '<span class="quick-modal-close"></span>';
        echo '<div class="quick-modal-content">';

        if ($show_secure_donation) {
            echo '<div class="secure-donation">';
            if ($secure_donation_icon) {
                echo '<span class="secure-donation-icon">';
                \Elementor\Icons_Manager::render_icon($secure_donation_icon, ['aria-hidden' => 'true']);
                echo '</span>';
            }
            echo $secure_donation_text;
            echo '</div>';
        }

        // Before icon
        $before_icon = '';
        if (isset($settings['icon_before_media_type'])) {
            if ($settings['icon_before_media_type'] === 'icon' && !empty($settings['icon_before_icon']['value'])) {
                $before_icon = Skydonate_Icon_Manager::render_icon($settings['icon_before_icon'], ['aria-hidden' => 'true']);
            } elseif ($settings['icon_before_media_type'] === 'image' && !empty($settings['icon_before_image']['url'])) {
                $before_icon = \Elementor\Group_Control_Image_Size::get_attachment_image_html($settings, 'icon_before_image_size', 'icon_before_image');
            }
        }

        // After icon
        $after_icon = '';
        if (isset($settings['icon_after_media_type'])) {
            if ($settings['icon_after_media_type'] === 'icon' && !empty($settings['icon_after_icon']['value'])) {
                $after_icon = Skydonate_Icon_Manager::render_icon($settings['icon_after_icon'], ['aria-hidden' => 'true']);
            } elseif ($settings['icon_after_media_type'] === 'image' && !empty($settings['icon_after_image']['url'])) {
                $after_icon = \Elementor\Group_Control_Image_Size::get_attachment_image_html($settings, 'icon_after_image_size', 'icon_after_image');
            }
        }

        // Build shortcode attributes dynamically
        $atts = [
            'id'             => $product_id,
            'button_text'    => !empty($settings['donation_button_text']) ? esc_attr($settings['donation_button_text']) : __('Donate and Support', 'skydonate'),
            'before_icon'    => !empty($before_icon) ? esc_attr($before_icon) : '',
            'after_icon'     => !empty($after_icon) ? esc_attr($after_icon) : '',
        ];

        // Build shortcode string
        $shortcode = '[skydonate_form';
        foreach ($atts as $key => $value) {
            if ($value !== '') {
                $shortcode .= ' ' . $key . '="' . $value . '"';
            }
        }
        $shortcode .= ']';
        echo do_shortcode($shortcode);

        echo '</div>'; // End .quick-modal-content
        echo '</div>'; // End .quick-modal-body
        echo '</div>'; // End .quick-modal
    }

    protected function Donation_Form_Button_Control(){
        // ===========================
        // Section: Donation Button
        // ===========================
        $this->start_controls_section(
            'donation_form_button',
            [
                'label' => __('Submit Button', 'skydonate'),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // ===== Button Text =====
        $this->add_control(
            'donation_button_text',
            [
                'label' => __('Button Text', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Donate and Support', 'skydonate'),
                'placeholder' => __('Enter button text', 'skydonate'),
            ]
        );


        // ===========================
        // Tabs: Button Icons
        // ===========================
        $this->start_controls_tabs('donation_button_icon_tabs');

        // ===== Icon Before Tab =====
        $this->start_controls_tab(
            'donation_button_icon_before_tab',
            [
                'label' => __('Icon Before', 'skydonate'),
            ]
        );

        // ---- Media Type Selector ----
        $this->add_control(
            'icon_before_media_type',
            [
                'label' => __('Media Type', 'skydonate'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'label_block' => false,
                'options' => [
                    'icon' => [
                        'title' => __('Icon', 'skydonate'),
                        'icon' => 'eicon-star',
                    ],
                    'image' => [
                        'title' => __('Image', 'skydonate'),
                        'icon' => 'eicon-image',
                    ],
                ],
                'default' => 'icon',
            ]
        );

        // ---- Image Control ----
        $this->add_control(
            'icon_before_image',
            [
                'label' => __('Image', 'skydonate'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
                'condition' => [
                    'icon_before_media_type' => 'image',
                ],
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        // ---- Image Size Control ----
        $this->add_group_control(
            \Elementor\Group_Control_Image_Size::get_type(),
            [
                'name' => 'icon_before_image_size',
                'default' => 'large',
                'separator' => 'none',
                'exclude' => [
                    'full',
                    'custom',
                    'large',
                    'shop_catalog',
                    'shop_single',
                    'shop_thumbnail',
                ],
                'condition' => [
                    'icon_before_media_type' => 'image',
                ],
            ]
        );

        // ---- Icon Control ----
        $this->add_control(
            'icon_before_icon',
            [
                'label' => __('Icon', 'skydonate'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'label_block' => true,
                'condition' => [
                    'icon_before_media_type' => 'icon',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_before_width',
            [
                'label' => __( 'Width', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range' => [
                    'px' => [ 'min' => 0, 'max' => 200 ],
                    '%'  => [ 'min' => 0, 'max' => 100 ],
                ],
                'condition' => [
                    'icon_before_media_type' => 'image',
                ],
                'selectors' => [
                    '{{WRAPPER}} .donation-form-wrapper .donation-form .form-submit-button .icon.left' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_before_height',
            [
                'label' => __( 'Height', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range' => [
                    'px' => [ 'min' => 0, 'max' => 200 ],
                    '%'  => [ 'min' => 0, 'max' => 100 ],
                ],
                'condition' => [
                    'icon_before_media_type' => 'image',
                ],
                'selectors' => [
                    '{{WRAPPER}} .donation-form-wrapper .donation-form .form-submit-button .icon.left' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_before_size',
            [
                'label' => __( 'Icon Size', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'range' => [
                    'px'  => [ 'min' => 0, 'max' => 200 ],
                    'em'  => [ 'min' => 0, 'max' => 10 ],
                    'rem' => [ 'min' => 0, 'max' => 10 ],
                ],
                'condition' => [
                    'icon_before_media_type' => 'icon',
                ],
                'selectors' => [
                    '{{WRAPPER}} .donation-form-wrapper .donation-form .form-submit-button .icon.left' => 'font-size: {{SIZE}}{{UNIT}};width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .donation-form-wrapper .donation-form .form-submit-button .icon.left svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_before_margin',
            [
                'label' => __( 'Margin', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-form-wrapper .donation-form .form-submit-button .icon.left' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );


        $this->end_controls_tab();


        // ===== Icon After Tab =====
        $this->start_controls_tab(
            'donation_button_icon_after_tab',
            [
                'label' => __('Icon After', 'skydonate'),
            ]
        );

        // ---- Media Type Selector ----
        $this->add_control(
            'icon_after_media_type',
            [
                'label' => __('Media Type', 'skydonate'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'label_block' => false,
                'options' => [
                    'icon' => [
                        'title' => __('Icon', 'skydonate'),
                        'icon' => 'eicon-star',
                    ],
                    'image' => [
                        'title' => __('Image', 'skydonate'),
                        'icon' => 'eicon-image',
                    ],
                ],
                'default' => 'icon',
            ]
        );

        // ---- Image Control ----
        $this->add_control(
            'icon_after_image',
            [
                'label' => __('Image', 'skydonate'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
                'condition' => [
                    'icon_after_media_type' => 'image',
                ],
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        // ---- Image Size Control ----
        $this->add_group_control(
            \Elementor\Group_Control_Image_Size::get_type(),
            [
                'name' => 'icon_after_image_size',
                'default' => 'large',
                'separator' => 'none',
                'exclude' => [
                    'full',
                    'custom',
                    'large',
                    'shop_catalog',
                    'shop_single',
                    'shop_thumbnail',
                ],
                'condition' => [
                    'icon_after_media_type' => 'image',
                ],
            ]
        );

        // ---- Icon Control ----
        $this->add_control(
            'icon_after_icon',
            [
                'label' => __('Icon', 'skydonate'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'label_block' => true,
                'default' => [
                    'value' => 'fas fa-arrow-right',
                    'library' => 'solid',
                ],
                'condition' => [
                    'icon_after_media_type' => 'icon',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_after_width',
            [
                'label' => __( 'Width', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range' => [
                    'px' => [ 'min' => 0, 'max' => 200 ],
                    '%'  => [ 'min' => 0, 'max' => 100 ],
                ],
                'condition' => [
                    'icon_after_media_type' => 'image',
                ],
                'selectors' => [
                    '{{WRAPPER}} .donation-form-wrapper .donation-form .form-submit-button .icon.right' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_after_height',
            [
                'label' => __( 'Height', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range' => [
                    'px' => [ 'min' => 0, 'max' => 200 ],
                    '%'  => [ 'min' => 0, 'max' => 100 ],
                ],
                'condition' => [
                    'icon_after_media_type' => 'image',
                ],
                'selectors' => [
                    '{{WRAPPER}} .donation-form-wrapper .donation-form .form-submit-button .icon.right' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_after_size',
            [
                'label' => __( 'Icon Size', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'range' => [
                    'px'  => [ 'min' => 0, 'max' => 200 ],
                    'em'  => [ 'min' => 0, 'max' => 10 ],
                    'rem' => [ 'min' => 0, 'max' => 10 ],
                ],
                'condition' => [
                    'icon_after_media_type' => 'icon',
                ],
                'selectors' => [
                    '{{WRAPPER}} .donation-form-wrapper .donation-form .form-submit-button .icon.right' => 'font-size: {{SIZE}}{{UNIT}};width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .donation-form-wrapper .donation-form .form-submit-button .icon.right svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_after_margin',
            [
                'label' => __( 'Margin', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-form-wrapper .donation-form .form-submit-button .icon.right' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );


        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();


    }
    
}
