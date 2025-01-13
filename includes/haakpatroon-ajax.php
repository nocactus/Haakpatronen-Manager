<?php
// haakpatroon-ajax.php

if (!function_exists('haakpatroon_load_more')) {
    function haakpatroon_load_more() {
        if (!isset($_POST['paged']) || !is_numeric($_POST['paged'])) {
            wp_die('Ongeldige aanvraag.');
        }

        $paged = intval($_POST['paged']);
        $max_cards = 6;
        
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

                echo '<div class="haakpatroon-item" style="flex: 0 0 auto; width: 100%; max-width: 280px; margin-right: 10px;">';
                if ($status == 'gratis') {
                    echo '<span class="haakpatroon-gratis-pill">Gratis</span>';
                }
                if (has_post_thumbnail()) {
                    echo '<div class="haakpatroon-thumbnail">' . get_the_post_thumbnail(get_the_ID(), 'medium') . '</div>';
                }
                echo '<div class="haakpatroon-title-container">
                        <h3 class="haakpatroon-title">' . get_the_title() . '</h3>';
                if ($status == 'gratis') {
                    echo '<a href="' . esc_url($url) . '" class="haakpatroon-link">Download Gratis</a>';
                }
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<p>Geen haakpatronen gevonden.</p>';
        }
        wp_reset_postdata();
        wp_die(); // Important to properly terminate AJAX requests
    }
    add_action('wp_ajax_haakpatroon_load_more', 'haakpatroon_load_more');
    add_action('wp_ajax_nopriv_haakpatroon_load_more', 'haakpatroon_load_more');
}
