<?php
// haakpatroon-post-type.php

function haakpatroon_register_post_type() {
    register_post_type('haakpatroon', [
        'labels' => [
            'name' => __('Haakpatronen'),
            'singular_name' => __('Haakpatroon'),
            'add_new' => __('Nieuw Haakpatroon Toevoegen'),
            'add_new_item' => __('Nieuw Haakpatroon'),
            'edit_item' => __('Haakpatroon Bewerken'),
            'new_item' => __('Nieuw Haakpatroon'),
            'view_item' => __('Bekijk Haakpatroon'),
            'all_items' => __('Alle Haakpatronen'),
        ],
        'public' => true,
        'menu_icon' => 'dashicons-art',
        'supports' => ['title', 'editor', 'thumbnail'],
        'has_archive' => false,
        'rewrite' => ['slug' => 'haakpatronen'],
    ]);
}
add_action('init', 'haakpatroon_register_post_type');

function haakpatroon_add_meta_boxes() {
    add_meta_box(
        'haakpatroon_meta_box',
        'Haakpatroon Details',
        'haakpatroon_meta_box_html',
        'haakpatroon',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'haakpatroon_add_meta_boxes');

function haakpatroon_meta_box_html($post) {
    $url = get_post_meta($post->ID, '_url', true);
    $status = get_post_meta($post->ID, '_status', true);
    ?>
    <label for="url">URL:</label>
    <input type="text" name="url" value="<?php echo esc_attr($url); ?>" style="width: 100%;" />
    <br><br>
    <label for="status">Status:</label>
    <select name="status">
        <option value="gratis" <?php selected($status, 'gratis'); ?>>Gratis</option>
        <option value="haakclub" <?php selected($status, 'haakclub'); ?>>Haakclub</option>
    </select>
    <?php
}

function haakpatroon_save_meta_box_data($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!isset($_POST['url']) || !isset($_POST['status'])) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (array_key_exists('url', $_POST)) {
        update_post_meta($post_id, '_url', esc_url($_POST['url']));
    }
    if (array_key_exists('status', $_POST)) {
        update_post_meta($post_id, '_status', sanitize_text_field($_POST['status']));
    }
}
add_action('save_post', 'haakpatroon_save_meta_box_data');
