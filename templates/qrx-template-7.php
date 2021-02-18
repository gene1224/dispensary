<?php 
function custom_template_css()
{
    $header_font_color = get_option('header_font_color', '');
    $sub_header_font_color = get_option('sub_header_font_color', '');
    $sub_header_background_color = get_option('sub_header_background_color', '');
    $header_background_color = get_option('header_background_color', '');
    $font_family = get_option('font_family', '');

    header('Content-Type: text/css');
//STYLE SHEET GOES HERE
    ?>
#qrx-sub-header {
    <?= template_background($sub_header_background_color); ?>
}
#qrx-sub-header a, #qrx-sub-header span, #qrx-sub-header h3, #qrx-sub-header h2, #qrx-sub-header i {
    <?= template_color($sub_header_background_color); ?>
}

#qrx-main-header {
    <?= template_background($header_background_color); ?>
}
#qrx-main-header a, #qrx-main-header span, #qrx-main-header h3, #qrx-main-header h2, #qrx-main-header i {
    <?= template_color($sub_header_font_color); ?>
}
    <?php

    die();

}

add_action("wp_ajax_nopriv_custom_template_css", "custom_template_css");
add_action("wp_ajax_custom_template_css", "custom_template_css");

function template_background($value)
{
    return $value ? 'background-color:' . $value : '';
}

function template_color($value)
{
    return $value ? 'color:' . $value : '';
}