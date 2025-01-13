<?php
/*
Plugin Name: Haakpatroon Manager
Description: Plugin om haakpatronen te beheren en tonen in een responsive grid met filteropties.
Version: 3.91
Author: Timooow & Copilot
*/

// Include the different parts of the plugin.
$includes_path = plugin_dir_path(__FILE__) . 'includes/';

$files_to_include = [
    'haakpatroon-post-type.php',
    'haakpatroon-settings.php',
    'haakpatroon-ajax.php',
    'haakpatroon-dynamic-download.php' // Include the dynamic download file
];

foreach ($files_to_include as $file) {
    $filepath = $includes_path . $file;
    if (file_exists($filepath)) {
        require_once $filepath;
    } else {
        error_log('Haakpatroon Manager Plugin: Kan het bestand ' . $file . ' niet vinden in de includes map.');
    }
}

// Shortcode: Haakpatronen Grid or Slider
function haakpatroon_display_grid($atts) {
    $atts = shortcode_atts([
        'filter' => 'all', // Default to showing all patterns
        'layout' => 'slider', // Default layout is slider
    ], $atts);

    $args = [
        'post_type' => 'haakpatroon',
        'posts_per_page' => -1, // Show all items
    ];

    // Filter for gratis or haakclub patterns if specified
    if ($atts['filter'] === 'gratis') {
        $args['meta_query'] = [
            [
                'key' => '_status',
                'value' => 'gratis',
                'compare' => '=',
            ],
        ];
    } elseif ($atts['filter'] === 'haakclub') {
        $args['meta_query'] = [
            [
                'key' => '_status',
                'value' => 'haakclub',
                'compare' => '=',
            ],
        ];
    }

    $query = new WP_Query($args);

    if ($atts['layout'] === 'grid') {
        $output = '<div class="haakpatroon-grid-layout">';
    } else {
        $output = '<div class="haakpatroon-grid">'; // Ensure slider layout uses correct class
    }

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            $url = get_post_meta(get_the_ID(), '_url', true);
            $status = get_post_meta(get_the_ID(), '_status', true);

            $output .= '<div class="haakpatroon-item">';
            if ($status === 'gratis') {
                $output .= '<span class="haakpatroon-gratis-pill">Gratis</span>';
            } elseif ($status === 'haakclub') {
                $output .= '<span class="haakpatroon-haakclub-pill">Haakclub</span>';
            }
            if (has_post_thumbnail()) {
                $output .= '<div class="haakpatroon-thumbnail">' . get_the_post_thumbnail(get_the_ID(), 'medium') . '</div>';
            }
            $output .= '<div class="haakpatroon-title-container">
                            <h3 class="haakpatroon-title">' . get_the_title() . '</h3>';
            if ($status === 'gratis') {
                $output .= '<a href="' . esc_url(site_url('/download-haakpatroon/' . get_the_ID())) . '" class="haakpatroon-link">Download Gratis</a>';
            } elseif ($status === 'haakclub') {
                $output .= '<a href="' . esc_url($url) . '" class="haakpatroon-link">Bekijk Haakpatroon</a>';
            }
            $output .= '</div>';
            $output .= '</div>';
        }
    } else {
        $output .= '<p>Geen haakpatronen gevonden.</p>';
    }
    $output .= '</div>';

    wp_reset_postdata();

    return $output;
}
add_shortcode('haakpatronen_grid', 'haakpatroon_display_grid');

// Add styles for the haakpatronen grid by enqueuing a separate CSS file
function haakpatroon_enqueue_styles() {
    wp_enqueue_style('haakpatroon-styles', plugin_dir_url(__FILE__) . 'assets/css/haakpatroon-styles.css');
}
add_action('wp_enqueue_scripts', 'haakpatroon_enqueue_styles');

// Enqueue JavaScript for AJAX load more functionality
function haakpatroon_enqueue_scripts() {
    wp_enqueue_script('haakpatroon-ajax', plugin_dir_url(__FILE__) . 'assets/js/haakpatroon-ajax.js', ['jquery'], null, true);
    wp_localize_script('haakpatroon-ajax', 'haakpatroon_ajax_params', [
        'ajax_url' => admin_url('admin-ajax.php')
    ]);
}
add_action('wp_enqueue_scripts', 'haakpatroon_enqueue_scripts');

// Handle AJAX request for loading more haakpatronen
function haakpatroon_load_more() {
    $paged = (isset($_POST['paged'])) ? intval($_POST['paged']) : 1;
    $max_cards = get_option('haakpatroon_max_cards', 6);
    
    $args = [
        'post_type' => 'haakpatroon',
        'posts_per_page' => $max_cards,
        'paged' => $paged,
    ];

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            $url = get_post_meta(get_the_ID(), '_url', true);
            $status = get_post_meta(get_the_ID(), '_status', true);

            echo '<div class="haakpatroon-item">';
            if ($status == 'gratis') {
                echo '<span class="haakpatroon-gratis-pill">Gratis</span>';
            } elseif ($status == 'haakclub') {
                echo '<span class="haakpatroon-haakclub-pill">Haakclub</span>';
            }
            if (has_post_thumbnail()) {
                echo '<div class="haakpatroon-thumbnail">' . get_the_post_thumbnail(get_the_ID(), 'medium') . '</div>';
            }
            echo '<div class="haakpatroon-title-container">
                    <h3 class="haakpatroon-title">' . get_the_title() . '</h3>';
            if ($status == 'gratis') {
                echo '<a href="' . esc_url($url) . '" class="haakpatroon-link">Download Gratis</a>';
            } elseif ($status == 'haakclub') {
                echo '<a href="' . esc_url($url) . '" class="haakpatroon-link">Bekijk Haakpatroon</a>';
            }
            echo '</div>';
            echo '</div>';
        }
    }
    wp_reset_postdata();
    wp_die(); // Important to properly terminate AJAX requests
}
add_action('wp_ajax_haakpatroon_load_more', 'haakpatroon_load_more');
add_action('wp_ajax_nopriv_haakpatroon_load_more', 'haakpatroon_load_more');

// Flush rewrite rules on activation
register_activation_hook(__FILE__, function() {
    flush_rewrite_rules();
});

// Flush rewrite rules on deactivation
register_deactivation_hook(__FILE__, function() {
    flush_rewrite_rules();
});

// Log a message when the plugin is loaded to verify debugging works
add_action('plugins_loaded', function() {
    error_log('Haakpatroon Manager Plugin Loaded: Debugging is active');
});
