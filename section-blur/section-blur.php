<?php
/**
 * Plugin Name: Section Blur
 * Plugin URI: https://example.com/section-blur
 * Description: Adds a customizable blur effect to sections as they leave the viewport
 * Version: 1.0.0
 * Author: Gary Angelone Jr.
 * License: GPL2
 */


if (!defined('ABSPATH')) {
    exit;
}

class SectionBlur {
    
    private $options;
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    public function add_plugin_page() {
        add_options_page(
            'Section Blur Settings',
            'Section Blur',
            'manage_options',
            'section-blur',
            array($this, 'create_admin_page')
        );
    }
    
    public function create_admin_page() {
        $this->options = get_option('section_blur_options');
        ?>
        <div class="wrap">
            <h1>Section Blur Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('section_blur_option_group');
                do_settings_sections('section-blur-admin');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
    
    public function page_init() {
        register_setting(
            'section_blur_option_group',
            'section_blur_options',
            array($this, 'sanitize')
        );
        
        add_settings_section(
            'section_blur_setting_section',
            'General Settings',
            array($this, 'section_info'),
            'section-blur-admin'
        );
        
        add_settings_field(
            'enabled',
            'Enable Blur Effect',
            array($this, 'enabled_callback'),
            'section-blur-admin',
            'section_blur_setting_section'
        );
        
        add_settings_field(
            'selector',
            'CSS Selector',
            array($this, 'selector_callback'),
            'section-blur-admin',
            'section_blur_setting_section'
        );
        
        add_settings_field(
            'max_blur',
            'Maximum Blur (px)',
            array($this, 'max_blur_callback'),
            'section-blur-admin',
            'section_blur_setting_section'
        );
        
        add_settings_field(
            'threshold_top',
            'Top Threshold (% of viewport)',
            array($this, 'threshold_top_callback'),
            'section-blur-admin',
            'section_blur_setting_section'
        );
        
        add_settings_field(
            'threshold_bottom',
            'Bottom Threshold (% of viewport)',
            array($this, 'threshold_bottom_callback'),
            'section-blur-admin',
            'section_blur_setting_section'
        );
    }
    
    public function sanitize($input) {
        $sanitary_values = array();
        
        if (isset($input['enabled'])) {
            $sanitary_values['enabled'] = $input['enabled'];
        }
        
        if (isset($input['selector'])) {
            $sanitary_values['selector'] = sanitize_text_field($input['selector']);
        }
        
        if (isset($input['max_blur'])) {
            $sanitary_values['max_blur'] = absint($input['max_blur']);
        }
        
        if (isset($input['threshold_top'])) {
            $sanitary_values['threshold_top'] = floatval($input['threshold_top']);
        }
        
        if (isset($input['threshold_bottom'])) {
            $sanitary_values['threshold_bottom'] = floatval($input['threshold_bottom']);
        }
        
        return $sanitary_values;
    }
    
    public function section_info() {
        echo '<p>Configure the section blur effect settings below.</p>';
    }
    
    public function enabled_callback() {
        $enabled = isset($this->options['enabled']) ? $this->options['enabled'] : '0';
        ?>
        <label class="switch">
            <input type="checkbox" name="section_blur_options[enabled]" value="1" <?php checked($enabled, '1'); ?>>
            <span class="slider"></span>
        </label>
        <style>
            .switch {
                position: relative;
                display: inline-block;
                width: 60px;
                height: 34px;
            }
            .switch input {
                opacity: 0;
                width: 0;
                height: 0;
            }
            .slider {
                position: absolute;
                cursor: pointer;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: #ccc;
                transition: .4s;
                border-radius: 34px;
            }
            .slider:before {
                position: absolute;
                content: "";
                height: 26px;
                width: 26px;
                left: 4px;
                bottom: 4px;
                background-color: white;
                transition: .4s;
                border-radius: 50%;
            }
            input:checked + .slider {
                background-color: #2196F3;
            }
            input:checked + .slider:before {
                transform: translateX(26px);
            }
        </style>
        <?php
    }
    
    public function selector_callback() {
        $selector = isset($this->options['selector']) ? $this->options['selector'] : '.fusion-fullwidth > div';
        printf(
            '<input class="regular-text" type="text" name="section_blur_options[selector]" id="selector" value="%s">',
            esc_attr($selector)
        );
        echo '<p class="description">Enter the CSS selector for elements to apply the blur effect to.</p>';
    }
    
    public function max_blur_callback() {
        $max_blur = isset($this->options['max_blur']) ? $this->options['max_blur'] : '20';
        ?>
        <div class="slider-container">
            <input type="range" name="section_blur_options[max_blur]" id="max_blur" value="<?php echo esc_attr($max_blur); ?>" min="0" max="100" step="1" class="blur-slider">
            <span class="slider-value" id="max_blur_value"><?php echo esc_attr($max_blur); ?>px</span>
        </div>
        <p class="description">Maximum blur amount in pixels (default: 20).</p>
        <script>
            document.getElementById('max_blur').addEventListener('input', function(e) {
                document.getElementById('max_blur_value').textContent = e.target.value + 'px';
            });
        </script>
        <style>
            .slider-container {
                display: flex;
                align-items: center;
                gap: 15px;
            }
            .blur-slider {
                width: 300px;
                height: 8px;
                -webkit-appearance: none;
                appearance: none;
                background: #ddd;
                outline: none;
                border-radius: 5px;
            }
            .blur-slider::-webkit-slider-thumb {
                -webkit-appearance: none;
                appearance: none;
                width: 20px;
                height: 20px;
                background: #2196F3;
                cursor: pointer;
                border-radius: 50%;
            }
            .blur-slider::-moz-range-thumb {
                width: 20px;
                height: 20px;
                background: #2196F3;
                cursor: pointer;
                border-radius: 50%;
                border: none;
            }
            .slider-value {
                min-width: 60px;
                font-weight: bold;
                color: #2196F3;
            }
        </style>
        <?php
    }
    
    public function threshold_top_callback() {
        $threshold_top = isset($this->options['threshold_top']) ? $this->options['threshold_top'] : '0.40';
        $percentage = $threshold_top * 100;
        ?>
        <div class="slider-container">
            <input type="range" name="section_blur_options[threshold_top]" id="threshold_top" value="<?php echo esc_attr($threshold_top); ?>" min="0" max="1" step="0.01" class="blur-slider">
            <span class="slider-value" id="threshold_top_value"><?php echo esc_attr($percentage); ?>%</span>
        </div>
        <p class="description">Threshold for top blur (default: 40% of viewport height).</p>
        <script>
            document.getElementById('threshold_top').addEventListener('input', function(e) {
                document.getElementById('threshold_top_value').textContent = Math.round(e.target.value * 100) + '%';
            });
        </script>
        <?php
    }
    
    public function threshold_bottom_callback() {
        $threshold_bottom = isset($this->options['threshold_bottom']) ? $this->options['threshold_bottom'] : '0.33';
        $percentage = $threshold_bottom * 100;
        ?>
        <div class="slider-container">
            <input type="range" name="section_blur_options[threshold_bottom]" id="threshold_bottom" value="<?php echo esc_attr($threshold_bottom); ?>" min="0" max="1" step="0.01" class="blur-slider">
            <span class="slider-value" id="threshold_bottom_value"><?php echo esc_attr($percentage); ?>%</span>
        </div>
        <p class="description">Threshold for bottom blur (default: 33% of viewport height).</p>
        <script>
            document.getElementById('threshold_bottom').addEventListener('input', function(e) {
                document.getElementById('threshold_bottom_value').textContent = Math.round(e.target.value * 100) + '%';
            });
        </script>
        <?php
    }
    
    public function enqueue_scripts() {
        $options = get_option('section_blur_options');
        $enabled = isset($options['enabled']) ? $options['enabled'] : '0';
        
        if ($enabled === '1') {
            wp_enqueue_script(
                'section-blur',
                plugin_dir_url(__FILE__) . 'js/section-blur.js',
                array(),
                '1.0.0',
                true
            );
            
            $selector = isset($options['selector']) ? $options['selector'] : '.fusion-fullwidth > div';
            $max_blur = isset($options['max_blur']) ? $options['max_blur'] : 20;
            $threshold_top = isset($options['threshold_top']) ? $options['threshold_top'] : 0.40;
            $threshold_bottom = isset($options['threshold_bottom']) ? $options['threshold_bottom'] : 0.33;
            
            wp_localize_script('section-blur', 'sectionBlurSettings', array(
                'selector' => $selector,
                'maxBlur' => $max_blur,
                'thresholdTop' => $threshold_top,
                'thresholdBottom' => $threshold_bottom
            ));
        }
    }
}

if (is_admin() || !is_admin()) {
    $section_blur = new SectionBlur();
}
