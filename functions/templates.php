<?php

function get_templates()
{
    $templates = get_posts(array('numberposts' => 100, 'post_type' => 'dispensary_templates', 'orderby' => 'name', 'order' => 'ASC'));

    return array_map(function ($template) {
        return array(
            'blog_id' => get_post_meta($template->ID, 'template_site_id', true),
            'name' => $template->post_title,
            'post_name' => $template->post_name,
            'thumbnail' => get_the_post_thumbnail_url($template->ID),
        );
    }, $templates);
}
