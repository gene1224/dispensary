<?php

/**
 * DispensaryTemplates Custom Post Type
 */
class DispensaryTemplates
{

    protected $post_type = 'dispensary_templates';

    public function __construct()
    {
        add_action('init', [$this, 'create_posttype']);
        add_action('pre_get_posts', [$this, 'add_to_query']);
        add_action('add_meta_boxes', [$this, 'meta_box'], 40);
        add_action('save_post', [$this, 'save_template_meta']);
    }

    public function create_posttype()
    {

        register_post_type($this->post_type,
            array(
                'labels' => array(
                    'name' => 'Template',
                    'singular_name' => 'Templates',
                ),
                'supports' => array('title', 'thumbnail', 'revisions', 'custom-fields'),
                'public' => true,
                'has_archive' => true,
                'rewrite' => array('slug' => 'dispensary-templates'),
                'show_in_rest' => true,

            )
        );
    }

    public function add_to_query($query)
    {
        if (is_home() && $query->is_main_query()) {
            $query->set('post_type', array('post', $this->post_type));
        }

        return $query;
    }

    public function meta_box()
    {
        add_meta_box(
            'templatess-data-box',
            'Template Details',
            [$this, 'meta_fields'],
            $this->post_type,
            'normal',
            'high',
            null
        );
    }

    public function meta_fields()
    {
        global $timber;

        $post_id = get_the_ID();

        $sites = get_sites(array('archived' => 1));

        $template_site_id = get_post_meta($post_id, 'template_site_id', true);

        $used_ids = array_map(
            function ($template) {
                return get_post_meta($template->ID, 'template_site_id', true);
            },
            get_posts(array('post_type' => 'dispensary_templates'))
        );

        $available_sites = array_filter($sites, function ($site) use ($used_ids) {
            return !in_array($site->blog_id, $used_ids) || $template_site_id == $site->blog_id;
        });

        $site_template_map = array_map(function ($site) {
            return array(
                'blog_id' => $site->blog_id,
                'domain' => $site->domain,
            );
        }, $available_sites);

        print_r(get_templates());
        $context = array(
            'site_template_map' => $site_template_map,
        );

        echo $timber->compile('admin/admin-template-meta-box.twig', $context);

    }

    public function save_template_meta($post_id)
    {
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (isset($_POST['template_site_id'])) {
            update_post_meta($post_id, 'template_site_id', sanitize_text_field($_POST['template_site_id']));
        } else {
            update_post_meta($post_id, 'template_site_id', 0);
        }

    }
}

$dispensaryTemplates = new DispensaryTemplates();
