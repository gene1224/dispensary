<?php



add_action('init', 'register_template_editor_script');
function register_template_editor_script()
{

    wp_register_style('template_editor_css', plugins_url('../assets/css/style.css', __FILE__), false, '1.0.0', 'all');
}

function custom_shortcode_scripts()
{
    global $post;
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'template_editor')) {
        wp_register_script('sweetalert', '//cdn.jsdelivr.net/npm/sweetalert2@10', array('jquery'), 3.3);
        wp_register_script('template_editor_js', plugins_url('../assets/js/script.js', __FILE__), array('jquery', 'sweetalert'), '2.5.1');
        wp_localize_script('template_editor_js', 'wp_ajax', array(
            'url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ajax-nonce'),
        ));
        wp_enqueue_script('sweetalert');
        wp_enqueue_style('template_editor_css');
        wp_enqueue_script('template_editor_js');

    }
}
add_action('wp_enqueue_scripts', 'custom_shortcode_scripts');

function template_editor_function()
{

    $header_font_color = get_blog_option( 2, 'header_font_color', '');
    $sub_header_font_color = get_blog_option( 2, 'sub_header_font_color', '');
    $sub_header_background_color = get_blog_option( 2, 'sub_header_background_color', '');
    $header_background_color = get_blog_option( 2, 'header_background_color', '');

    ?>

<div class="template-theme-editor">
    <h1>Template Editor</h1>
    <form id="templateEditor">
        <input type="hidden" name="action" value="update_template_settings">
        <div class="template-editor-fields">
            <div class="template-editor-field">
                <label>Header Background Color</label>
                <input type="color"  name="header_background_color" value="<?=$header_background_color?>">
            </div>
            <div class="template-editor-field">
                <label>Header Font Color</label>
                <input type="color" name="sub_header_background_color" value="<?=$sub_header_background_color?>" >
            </div>

            <div class="template-editor-field">
                <label>Sub Header Background Color</label>
                <input type="color" name="header_font_color" value="<?=$header_font_color?>" >
            </div>
            <div class="template-editor-field">
                <label>Sub Header Font Color</label>
                <input type="color" name="sub_header_font_color" value="<?=$sub_header_font_color?>" >
            </div>
        </div>
        <div class="template-editor-actions">
            <button type="submit" id="templateSave">Save Changes</button>
        </div>
    </form>
</div>

<?php
}

add_shortcode('template_editor', 'template_editor_function');

function update_template_settings()
{

    if (!wp_verify_nonce($_POST['nonce'], 'ajax-nonce')) {
        die();
    }
    switch_to_blog(2);
    update_option('header_font_color', $_POST['header_font_color']);
    update_option('sub_header_font_color', $_POST['sub_header_font_color']);
    update_option('sub_header_background_color', $_POST['sub_header_background_color']);
    update_option('header_background_color', $_POST['header_background_color']);
    restore_current_blog();

    die();
}

add_action("wp_ajax_nopriv_update_template_settings", "update_template_settings");
add_action("wp_ajax_update_template_settings", "update_template_settings");