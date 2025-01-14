<?php
// Add a metabox for file upload in the Haakpatroon post type
add_action('add_meta_boxes', function() {
    add_meta_box(
        'haakpatroon_free_file',
        __('Gratis Bestand Uploaden', 'haakpatroon-manager'),
        'haakpatroon_free_file_metabox',
        'haakpatroon',
        'side',
        'high'
    );
});

// Render the metabox
function haakpatroon_free_file_metabox($post) {
    wp_nonce_field('haakpatroon_file_nonce', 'haakpatroon_file_nonce');
    $uploaded_file = get_post_meta($post->ID, '_haakpatroon_free_file', true);
    echo '<p>' . __('Upload een bestand voor gratis haakpatronen:', 'haakpatroon-manager') . '</p>';
    echo '<input type="file" id="haakpatroon_free_file" name="haakpatroon_free_file">';
    if ($uploaded_file) {
        echo '<p>' . __('Huidig bestand:', 'haakpatroon-manager') . '</p>';
        echo '<a href="' . esc_url($uploaded_file) . '" target="_blank">' . basename($uploaded_file) . '</a>';
    }
}

// Save the uploaded file
add_action('save_post', function($post_id) {
    if (!isset($_POST['haakpatroon_file_nonce']) || !wp_verify_nonce($_POST['haakpatroon_file_nonce'], 'haakpatroon_file_nonce')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (isset($_FILES['haakpatroon_free_file']) && !empty($_FILES['haakpatroon_free_file']['tmp_name'])) {
        $file = $_FILES['haakpatroon_free_file'];
        $upload = wp_handle_upload($file, ['test_form' => false]);
        if (isset($upload['url'])) {
            update_post_meta($post_id, '_haakpatroon_free_file', esc_url($upload['url']));
        }
    }
});
?>

<?php
// Add a shortcode for the download page
add_shortcode('haakpatroon_download', function($atts) {
    $atts = shortcode_atts(['id' => 0], $atts, 'haakpatroon_download');
    $post_id = (int)$atts['id'];
    $post = get_post($post_id);

    if (!$post || $post->post_type !== 'haakpatroon') {
        return '<p>' . __('Haakpatroon niet gevonden.', 'haakpatroon-manager') . '</p>';
    }

    $file = get_post_meta($post_id, '_haakpatroon_free_file', true);
    if (!$file) {
        return '<p>' . __('Geen download beschikbaar voor dit haakpatroon.', 'haakpatroon-manager') . '</p>';
    }

    ob_start();
    ?>
    <div class="haakpatroon-download-page">
        <h2><?php echo esc_html($post->post_title); ?></h2>
        <?php if (has_post_thumbnail($post_id)) : ?>
            <div class="haakpatroon-thumbnail">
                <?php echo get_the_post_thumbnail($post_id, 'medium'); ?>
            </div>
        <?php endif; ?>
        <p><?php echo esc_html($post->post_excerpt); ?></p>
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['voornaam']) && isset($_POST['email'])): ?>
            <?php
            $voornaam = sanitize_text_field($_POST['voornaam']);
            $email = sanitize_email($_POST['email']);

            if ($voornaam && $email) {
                // Placeholder for ActiveCampaign API integration
                // Normally, you'd add the API request here
                echo '<p>' . __('Bedankt! Je ontvangt een e-mail met de downloadlink.', 'haakpatroon-manager') . '</p>';
                echo '<a href="' . esc_url($file) . '" class="haakpatroon-download-link">' . __('Download Gratis', 'haakpatroon-manager') . '</a>';
            } else {
                echo '<p>' . __('Er ging iets mis bij het verwerken van je gegevens. Probeer het opnieuw.', 'haakpatroon-manager') . '</p>';
            }
            ?>
        <?php else: ?>
            <form method="post" id="haakpatroon-download-form">
                <label for="voornaam"><?php _e('Voornaam', 'haakpatroon-manager'); ?></label>
                <input type="text" name="voornaam" id="voornaam" required>
                <label for="email"><?php _e('E-mailadres', 'haakpatroon-manager'); ?></label>
                <input type="email" name="email" id="email" required>
                <button type="submit"><?php _e('Download', 'haakpatroon-manager'); ?></button>
            </form>
        <?php endif; ?>
    </div>
    <?php

    return ob_get_clean();
});
?>

<?php
// Function to send data to ActiveCampaign
function send_to_activecampaign($voornaam, $email) {
    $api_url = 'https://your-account-name.api-us1.com/api/3/contacts'; // Replace with actual ActiveCampaign API URL
    $api_key = 'YOUR_API_KEY'; // Replace with your actual API key

    $data = [
        'contact' => [
            'email' => $email,
            'firstName' => $voornaam,
            'tags' => ['gratis-download'], // Replace with desired tag(s)
            'list' => [1] // Replace with the desired list ID
        ]
    ];

    $response = wp_remote_post($api_url, [
        'method' => 'POST',
        'headers' => [
            'Api-Token' => $api_key,
            'Content-Type' => 'application/json',
        ],
        'body' => wp_json_encode($data),
    ]);

    return wp_remote_retrieve_response_code($response) === 201; // Success if 201 Created
}

// Modify the shortcode to use ActiveCampaign integration
add_shortcode('haakpatroon_download', function($atts) {
    $atts = shortcode_atts(['id' => 0], $atts, 'haakpatroon_download');
    $post_id = (int)$atts['id'];
    $post = get_post($post_id);

    if (!$post || $post->post_type !== 'haakpatroon') {
        return '<p>' . __('Haakpatroon niet gevonden.', 'haakpatroon-manager') . '</p>';
    }

    $file = get_post_meta($post_id, '_haakpatroon_free_file', true);
    if (!$file) {
        return '<p>' . __('Geen download beschikbaar voor dit haakpatroon.', 'haakpatroon-manager') . '</p>';
    }

    ob_start();
    ?>
    <div class="haakpatroon-download-page">
        <h2><?php echo esc_html($post->post_title); ?></h2>
        <?php if (has_post_thumbnail($post_id)) : ?>
            <div class="haakpatroon-thumbnail">
                <?php echo get_the_post_thumbnail($post_id, 'medium'); ?>
            </div>
        <?php endif; ?>
        <p><?php echo esc_html($post->post_excerpt); ?></p>
        <form method="post" id="haakpatroon-download-form">
            <label for="voornaam"><?php _e('Voornaam', 'haakpatroon-manager'); ?></label>
            <input type="text" name="voornaam" id="voornaam" required>
            <label for="email"><?php _e('E-mailadres', 'haakpatroon-manager'); ?></label>
            <input type="email" name="email" id="email" required>
            <button type="submit"><?php _e('Download', 'haakpatroon-manager'); ?></button>
        </form>
    </div>
    <?php

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $voornaam = sanitize_text_field($_POST['voornaam']);
        $email = sanitize_email($_POST['email']);

        if ($voornaam && $email) {
            $success = send_to_activecampaign($voornaam, $email);
            if ($success) {
                echo '<p>' . __('Bedankt! Je ontvangt een e-mail met de downloadlink.', 'haakpatroon-manager') . '</p>';
            } else {
                echo '<p>' . __('Er ging iets mis bij het verwerken van je gegevens. Probeer het opnieuw.', 'haakpatroon-manager') . '</p>';
            }
        }
    }

    return ob_get_clean();
});
?>

// Add a metabox for file upload in the Haakpatroon post type
add_action('add_meta_boxes', function() {
    add_meta_box(
        'haakpatroon_free_file',
        __('Gratis Bestand Uploaden', 'haakpatroon-manager'),
        'haakpatroon_free_file_metabox',
        'haakpatroon',
        'side',
        'high'
    );
});

// Render the metabox
function haakpatroon_free_file_metabox($post) {
    wp_nonce_field('haakpatroon_file_nonce', 'haakpatroon_file_nonce');
    $uploaded_file = get_post_meta($post->ID, '_haakpatroon_free_file', true);
    echo '<p>' . __('Upload een bestand voor gratis haakpatronen:', 'haakpatroon-manager') . '</p>';
    echo '<input type="file" id="haakpatroon_free_file" name="haakpatroon_free_file">';
    if ($uploaded_file) {
        echo '<p>' . __('Huidig bestand:', 'haakpatroon-manager') . '</p>';
        echo '<a href="' . esc_url($uploaded_file) . '" target="_blank">' . basename($uploaded_file) . '</a>';
    }
}

// Save the uploaded file
add_action('save_post', function($post_id) {
    if (!isset($_POST['haakpatroon_file_nonce']) || !wp_verify_nonce($_POST['haakpatroon_file_nonce'], 'haakpatroon_file_nonce')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (isset($_FILES['haakpatroon_free_file']) && !empty($_FILES['haakpatroon_free_file']['tmp_name'])) {
        $file = $_FILES['haakpatroon_free_file'];
        $upload = wp_handle_upload($file, ['test_form' => false]);
        if (isset($upload['url'])) {
            update_post_meta($post_id, '_haakpatroon_free_file', esc_url($upload['url']));
        }
    }
});
