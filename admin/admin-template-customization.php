<?php

// function my_general_settings_register_fields()
// {
//     register_setting('general', 'stripe_account_id', 'esc_attr');
//     add_settings_field('stripe_account_id', '<label for="stripe_account_id">' . __('Str', 'stripe_account_id') . '</label>', 'stripe_account_id_HTML', 'general');
// }

// function stripe_account_id_HTML()
// {
//     $copyright_message = get_option('stripe_account_id', '');
//     if($copyright_message != ''){
//         echo "<p>Stripe Account : ******************************</p>";
//         echo '<input id="copyright_message" type="text" style="width: 35%;" type="text" name="stripe_account_id" />';
//     } else {
//         echo '<input id="copyright_message" style="width: 35%;" type="text" name="stripe_account_id" value="' . $copyright_message . '" />';
//     }

// }

// add_filter('admin_init', 'my_general_settings_register_fields');

function custom_template_color_picker()
{

    $location = 'general';

    register_setting($location, 'header_background_color', 'esc_attr');
    add_settings_field(
        'header_background_color',
        '<label for="header_background_color">Header Background Color</label>',
        'header_background_color_picker_HTML',
        $location
    );

    register_setting($location, 'sub_header_background_color', 'esc_attr');
    add_settings_field(
        'sub_header_background_color',
        '<label for="sub_header_background_color_picker">Sub Header Backgrounddary Color</label>',
        'sub_header_background_color_picker_HTML',
        $location
    );

    register_setting($location, 'sub_header_font_color', 'esc_attr');
    add_settings_field(
        'primary_font_color',
        '<label for="sub_header_font_color">Sub Header Font Color</label>',
        'sub_header_font_color_HTML',
        $location
    );

    register_setting($location, 'header_font_color', 'esc_attr');
    add_settings_field(
        'header_font_color',
        '<label for="header_font_color">Header Font Color</label>',
        'header_font_color_HTML',
        $location
    );

    register_setting($location, 'font_family', 'esc_attr');
    add_settings_field(
        'font_family',
        '<label for="secondary_font_color">Font Family</label>',
        'font_family_picker_HTML',
        $location
    );
}

function header_background_color_picker_HTML()
{
    $header_background_color = get_option('header_background_color', '');
    ?>
        <input type="color"  name="header_background_color" value="<?=$header_background_color?>">
    <?php

}

function sub_header_background_color_picker_HTML()
{
    $sub_header_background_color = get_option('sub_header_background_color', '');
    ?>
        <input type="color"  name="sub_header_background_color" value="<?=$sub_header_background_color?>">
    <?php

}

function header_font_color_HTML()
{
    $header_font_color = get_option('header_font_color', '');
    ?>
        <input type="color"  name="header_font_color" value="<?=$header_font_color?>">
    <?php
}

function sub_header_font_color_HTML()
{
    $sub_header_font_color = get_option('sub_header_font_color', '');
    ?>
        <input type="color"  name="sub_header_font_color" value="<?=$sub_header_font_color?>">
    <?php
}

function font_family_picker_HTML()
{
    $font_family = get_option('font_family', '');
    $available_fonts = array(
        array("name" => "Open Sans", "css" => "Open Sans"),
        array("name" => "Monteserrat", "css" => "Open Sans"),
        array("name" => "Roboto", "css" => "Open Sans"),
        array("name" => "Arial", "css" => "Open Sans"),
    );
    ?>
        <select name="font_family" id="font-selector">
            <?php foreach ($available_fonts as $font) {?>
                <option value="<?=$font['name']?>" <?=$font['name'] == $font_family ? "selected" : ""?> ><?=$font["name"]?></option>
            <?php }?>
        </select>

        <script>
        jQuery(document).ready(function() {
            jQuery('#font-selector').select2();
        });
        </script>
    <?php
}

// add_filter('admin_init', 'custom_template_color_picker');

// function enqueue_select2_jquery()
// {
//     wp_register_style('select2css', '//cdnjs.cloudflare.com/ajax/libs/select2/3.4.8/select2.css', false, '1.0', 'all');
//     wp_register_script('select2', '//cdnjs.cloudflare.com/ajax/libs/select2/3.4.8/select2.js', array('jquery'), '1.0', true);
//     wp_enqueue_style('select2css');
//     wp_enqueue_script('select2');
// }
// add_action('admin_enqueue_scripts', 'enqueue_select2_jquery');
