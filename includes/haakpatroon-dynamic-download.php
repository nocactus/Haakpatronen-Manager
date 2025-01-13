<?php
// Add rewrite rule for dynamic download page
add_action('init', function() {
    add_rewrite_rule(
        '^download-haakpatroon/([0-9]+)/?$',
        'index.php?haakpatroon_id=$matches[1]',
        'top'
    );
    error_log('Rewrite rule added for /download-haakpatroon/{ID}');
});

// Add haakpatroon_id to query vars
add_filter('query_vars', function($vars) {
    $vars[] = 'haakpatroon_id';
    error_log('Current query vars: ' . print_r($vars, true));
    return $vars;
});

// Log all rewrite rules for debugging
add_action('init', function() {
    global $wp_rewrite;
    error_log('Active rewrite rules: ' . print_r($wp_rewrite->wp_rewrite_rules(), true));
}, 20);

// Template redirection for dynamic download page
add_action('template_redirect', function() {
    $haakpatroon_id = get_query_var('haakpatroon_id');
    if ($haakpatroon_id) {
        error_log('Detected haakpatroon_id: ' . $haakpatroon_id);
        $post = get_post($haakpatroon_id);
        if ($post && $post->post_type === 'haakpatroon') {
            error_log('Valid haakpatroon post found: ' . print_r($post, true));
            status_header(200);
            $template_path = plugin_dir_path(__FILE__) . '../templates/download-haakpatroon-template.php';
            if (file_exists($template_path)) {
                error_log('Template exists: ' . $template_path);
                include $template_path;
            } else {
                error_log('Template missing: ' . $template_path);
                wp_die(__('Template bestand niet gevonden.', 'haakpatroon-manager'));
            }
            exit;
        } else {
            error_log('Invalid haakpatroon_id or post type mismatch: ' . $haakpatroon_id);
            wp_redirect(home_url('/404'));
            exit;
        }
    } else {
        error_log('No haakpatroon_id detected in query vars');
    }
});
