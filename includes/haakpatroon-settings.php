<?php
// haakpatroon-settings.php

function haakpatroon_register_settings() {
    add_option('haakpatroon_max_cards', 6);
    register_setting('haakpatroon_options_group', 'haakpatroon_max_cards', 'intval');
}
add_action('admin_init', 'haakpatroon_register_settings');

function haakpatroon_register_options_page() {
    add_options_page('Haakpatroon Instellingen', 'Haakpatroon', 'manage_options', 'haakpatroon', 'haakpatroon_options_page');
}
add_action('admin_menu', 'haakpatroon_register_options_page');

function haakpatroon_options_page() {
    ?>
    <div class="wrap">
        <h2>Haakpatroon Instellingen</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('haakpatroon_options_group');
            do_settings_sections('haakpatroon_options_group');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><label for="haakpatroon_max_cards">Maximaal aantal zichtbare kaarten:</label></th>
                    <td><input type="number" id="haakpatroon_max_cards" name="haakpatroon_max_cards" value="<?php echo esc_attr(get_option('haakpatroon_max_cards')); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
