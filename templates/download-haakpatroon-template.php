<?php
// Template for displaying the haakpatroon download page
get_header();

$haakpatroon_id = get_query_var('haakpatroon_id');
$post = get_post($haakpatroon_id);

if ($post && $post->post_type === 'haakpatroon') {
    $url = get_post_meta($post->ID, '_url', true);
    $status = get_post_meta($post->ID, '_status', true);
    ?>
    <div class="haakpatroon-download-page">
        <h1><?php echo get_the_title($post); ?></h1>
        <?php if ($status === 'gratis') : ?>
            <a href="<?php echo esc_url($url); ?>" class="haakpatroon-download-link">Download Gratis</a>
        <?php elseif ($status === 'haakclub') : ?>
            <a href="<?php echo esc_url($url); ?>" class="haakpatroon-download-link">Bekijk Haakpatroon</a>
        <?php endif; ?>
    </div>
    <?php
} else {
    echo '<p>Haakpatroon niet gevonden.</p>';
}

get_footer();
?>
