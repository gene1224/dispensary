<?php

function product_import_shortcode_scripts()
{
    wp_register_script('sweetalert', '//cdn.jsdelivr.net/npm/sweetalert2@10', array('jquery'), 3.3);
    wp_register_script('product_import_utils', plugins_url('../assets/js/utils.js', __FILE__), array('jquery', 'sweetalert'), '2.5.1');
    wp_register_script('lightbox_js_qrx', 'https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js', array('jquery'), '2.5.1');
    wp_register_style('lightbox_css_qrx', 'https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.css', [], '1.0.0', 'all');
    wp_register_script('product_import_js', plugins_url('../assets/js/script.js', __FILE__), array('jquery', 'sweetalert', 'product_import_utils', 'lightbox_js_qrx'), '2.5.1');
    wp_register_script('product_import_done_js', plugins_url('../assets/js/product.js', __FILE__), array('jquery', 'sweetalert', 'product_import_utils'), '2.5.1');
    wp_register_script('product_import_cart_js', plugins_url('../assets/js/cart.js', __FILE__), array('jquery', 'sweetalert', 'product_import_utils'), '2.5.1');
    wp_register_style('product_import_css', plugins_url('../assets/css/style.css', __FILE__), [], '1.0.0', 'all');
    //added
    wp_register_script('website_analytics_js', plugins_url('../assets/js/website-analytics.js', __FILE__), array('jquery'), '1.0.1');
    wp_register_script('product_report_js', plugins_url('../assets/js/product-report.js', __FILE__), array('jquery'), '1.0.1');
    wp_register_script('jquery_ui_js', 'https://code.jquery.com/ui/1.12.1/jquery-ui.js', array('jquery'), '1.0.1');
    wp_register_style('website_analytics_css', plugins_url('../assets/css/website-analytics.css', __FILE__), [], '1.0.1', 'all');
    wp_register_style('jquery_ui_css', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css', [], '1.0.1', 'all');
    wp_register_script('dashboard_js', plugins_url('../assets/js/dashboard.js', __FILE__), array('jquery', 'sweetalert', 'product_import_utils', 'lightbox_js_qrx'), '1.0.1');
    wp_register_style('dashboard_css', plugins_url('../assets/css/dashboard.css', __FILE__), [], '1.0.1', 'all');
    wp_register_script('chart_js', 'https://www.jsdelivr.com/package/npm/chart.js?path=dist', array('jquery'), '1.0.1');

}
add_action('wp_enqueue_scripts', 'product_import_shortcode_scripts');

function dashboard_display()
{
    global $timber;
    global $wpdb;

    $imported_products = get_users_imported_products();
    $ordered_products = get_users_ordered_products();
    $ordered_total_sales = get_users_total_sales();
    $site_product = array(
        array(
            'url' => 'https://allstuff420.com',
            'api_key' => base64_encode('ck_2eff2c6b9cc435818aad646e1c7676d65af7f168:cs_2fd13443cf704e5c1ca201cbe786043505b8baaa'),
        ),
    );
    $listing_cart = get_user_meta(get_current_user_id(), 'listing_cart', true) ?: [];
    $memberships = wc_memberships_get_user_memberships($parent_id == 0 ? get_current_user_id() : $parent_id);
    $max_product = 10;
    $membership_plan_name = '';
    foreach ($memberships as $membership) {
        $product_limit = get_post_meta($membership->plan_id, 'dispensary_product_limit', true) ?: 0;
        if ($max_product < $product_limit) {
            $max_product = $product_limit;
            $membership_plan_name = $membership->plan->name;
        }
    }
    $max_product = apply_filters('max_products_to_import', $max_product);

    $site_id = 0;
    $site_url = '';
    $sites = get_blogs_of_user(get_current_user_id(), true);

    //visitors
    $website_visitors_total = 0;

    foreach ($sites as $site) {
        if ($site->userblog_id != 1) {
            $site_id = $site->userblog_id;
            $site_url = $site->siteurl;
            break;
        }
    }

    $table_visits = $wpdb->base_prefix . $site_id . '_statistics_visit';
    $table_visitors = $wpdb->base_prefix . $site_id . '_statistics_visitor';
    $result_visitors = $wpdb->get_results("SELECT * FROM $table_visitors", OBJECT);

    //for Total Visitors
    foreach ($result_visitors as $visitor_total) {
        $website_visitors_total += count($visitor_total->last_counter);
    }

    $context = array(
        'website_visitors_total' => $website_visitors_total,
        'imported_products' => $imported_products,
        'ordered_products' => $ordered_products,
        'ordered_total_sales' => $ordered_total_sales,
        'notifySent' => $notifySent,
        'site_product' => $site_product,
        'gird_url' => explode('?', home_url($_SERVER["REQUEST_URI"]))[0],
        'cart_url' => home_url($_SERVER["REQUEST_URI"]) . "?view=cart",
        'cart_count' => count($listing_cart) ?: 0,
        'imported_products_count' => count($imported_products) ?: 0,
        'max_products' => $max_product,
        'view' => $_REQUEST['view'] ?: 'home',
        'membership' => $membership_plan_name,
    );

    try {
        $listing_cart = array_values($listing_cart);
    } catch (\Throwable $th) {
        $listing_cart = [];
    }

    $js_objects = array(
        'url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ajax-nonce'),
        'website_visitors_total' => $website_visitors_total,
        'site_product' => $site_product,
        'imported_products' => $imported_products,
        'ordered_products' => $ordered_products,
        'ordered_total_sales' => $ordered_total_sales,
        'notifySent' => $notifySent,
        'default_site' => $site_product[0]['url'],
        'default_api_key' => $site_product[0]['api_key'],
        'max_products' => $max_product,
        'listing_cart' => $listing_cart ?: [],
        'cart_count' => count($listing_cart) ?: 0,
        'imported_products_count' => count($imported_products) ?: 0,
    );

    wp_enqueue_script('sweetalert');
    wp_enqueue_script('jquery_ui_js');
    wp_enqueue_script('dashboard_js');
    wp_enqueue_style('jquery_ui_css');
    wp_enqueue_style('dashboard_css');
    wp_enqueue_style('product_import_css');
    wp_localize_script('dashboard_js', 'wp_ajax', $js_objects);
    echo $timber->compile('dashboard.twig', $context);
}
add_shortcode('dashboard_views', 'dashboard_display');

function product_import_display()
{
    global $timber;

    $sites = array(
        array(
            'url' => 'https://allstuff420.com',
            'api_key' => base64_encode('ck_2eff2c6b9cc435818aad646e1c7676d65af7f168:cs_2fd13443cf704e5c1ca201cbe786043505b8baaa'),
        ),
    );

    $imported_products = get_users_imported_products();

    wp_enqueue_script('sweetalert');
    wp_enqueue_style('product_import_css');

    if (!get_user_meta(get_current_user_id(), 'site_created', true) && !get_user_meta(get_current_user_id(), 'created_on_my_account', true)) {
        echo $timber->compile('no-site.twig', $context);
        return;
    }

    $parent_id = 0;
    if (get_user_meta(get_current_user_id(), 'created_on_my_account', true)) {
        $parent_id = get_user_meta(get_current_user_id(), 'created_by_user_id', true);
    }
    $listing_cart = get_user_meta(get_current_user_id(), 'listing_cart', true) ?: [];

    $memberships = wc_memberships_get_user_memberships($parent_id == 0 ? get_current_user_id() : $parent_id);

    $max_product = 10;
    $membership_plan_name = '';
    foreach ($memberships as $membership) {
        $product_limit = get_post_meta($membership->plan_id, 'dispensary_product_limit', true) ?: 0;
        if ($max_product < $product_limit) {
            $max_product = $product_limit;
            $membership_plan_name = $membership->plan->name;
        }

    }

    $max_product = apply_filters('max_products_to_import', $max_product);

    $context = array(
        'sites' => $sites,
        'gird_url' => explode('?', home_url($_SERVER["REQUEST_URI"]))[0],
        'cart_url' => home_url($_SERVER["REQUEST_URI"]) . "?view=cart",
        'cart_count' => count($listing_cart) ?: 0,
        'imported_products_count' => count($imported_products) ?: 0,
        'imported_products' => $imported_products,
        'max_products' => $max_product,
        'view' => $_REQUEST['view'] ?: 'home',
        'membership' => $membership_plan_name,
    );

    try {
        $listing_cart = array_values($listing_cart);
    } catch (\Throwable $th) {
        $listing_cart = [];
    }

    $js_objects = array(
        'url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ajax-nonce'),
        'default_site' => $sites[0]['url'],
        'default_api_key' => $sites[0]['api_key'],
        'imported_products' => $imported_products,
        'max_products' => $max_product,
        'listing_cart' => $listing_cart ?: [],
    );

    wp_localize_script('product_import_utils', 'wp_ajax', $js_objects);

    switch ($_REQUEST['view']) {
        case 'imported':
            wp_localize_script('product_import_done_js', 'wp_ajax', $js_objects);
            wp_enqueue_script('product_import_done_js');
            echo $timber->compile('imported-products.twig', $context);
            break;
        case 'cart':
            $batch = check_imported_products(get_current_user_id());
            $js_objects["import_status"] = count($batch["remaining_skus"]);
            wp_localize_script('product_import_cart_js', 'wp_ajax', $js_objects);
            wp_enqueue_script('product_import_cart_js');
            echo $timber->compile('import-cart.twig', $context);
            break;
        default:
            wp_localize_script('product_import_js', 'wp_ajax', $js_objects);
            wp_enqueue_script('product_import_js');
            wp_enqueue_style('lightbox_css_qrx');
            echo $timber->compile('product-import.twig', $context);
            break;
    }
}
add_shortcode('product_import_views', 'product_import_display');

function website_analytics_display()
{
    global $timber;
    global $wpdb;

    $imported_products = get_users_imported_products();
    $ordered_products = get_users_ordered_products();
    $ordered_total_sales = get_users_total_sales();

    $memberships = wc_memberships_get_user_memberships($parent_id == 0 ? get_current_user_id() : $parent_id);

    $site_id = 0;
    $site_url = '';
    $sites = get_blogs_of_user(get_current_user_id(), true);
    $dateToday = date("Y-m-d");
    $dateYesterday = date("Y-m-d", strtotime("yesterday"));
    $dateWeek = new DateTime();
    $dateWeek->setISODate(date('Y'), date('W'));
    $dateWeekFrom = $dateWeek->format('Y-m-d');
    $dateWeek->modify('+6 days');
    $dateWeekTo = $dateWeek->format('Y-m-d');
    $dateMonthStart = new DateTime('first day of this month');
    $dateMonthEnd = new DateTime('last day of this month');
    $dateMonthFrom = $dateMonthStart->format('Y-m-d');
    $dateMonthTo = $dateMonthEnd->format('Y-m-d');
    $dateYearStart = new DateTime('first day of january this year');
    $dateYearEnd = new DateTime('last day of december this year');
    $dateYearFrom = $dateYearStart->format('Y-m-d');
    $dateYearTo = $dateYearEnd->format('Y-m-d');
    $notifySent = "";

    //visits
    $website_visits_today = 0;
    $website_visits_yesterday = 0;
    $website_visits_week = 0;
    $website_visits_month = 0;
    $website_visits_year = 0;
    $website_visits_custom = 0;

    //visitors
    $website_visitors_today = 0;
    $website_visitors_yesterday = 0;
    $website_visitors_week = 0;
    $website_visitors_month = 0;
    $website_visitors_year = 0;
    $website_visitors_custom = 0;

    //page visits
    $website_sum_pages_today = 0;
    $website_sum_pages_yesterday = 0;
    $website_sum_pages_week = 0;
    $website_sum_pages_month = 0;
    $website_sum_pages_year = 0;
    $website_sum_pages_custom = 0;

    foreach ($sites as $site) {
        if ($site->userblog_id != 1) {
            $site_id = $site->userblog_id;
            $site_url = $site->siteurl;
            break;
        }
    }

    $table_visits = $wpdb->base_prefix . $site_id . '_statistics_visit';
    $table_visitors = $wpdb->base_prefix . $site_id . '_statistics_visitor';
    $table_pages = $wpdb->base_prefix . $site_id . '_statistics_pages';
    $result_visits = $wpdb->get_results("SELECT * FROM $table_visits", OBJECT);
    $result_visits_week = $wpdb->get_results("SELECT * FROM $table_visits WHERE last_counter BETWEEN '$dateWeekFrom' AND '$dateWeekTo'", OBJECT);
    $result_visits_month = $wpdb->get_results("SELECT * FROM $table_visits WHERE last_counter BETWEEN '$dateMonthFrom' AND '$dateMonthTo'", OBJECT);
    $result_visits_year = $wpdb->get_results("SELECT * FROM $table_visits WHERE last_counter BETWEEN '$dateYearFrom' AND '$dateYearTo'", OBJECT);
    $result_visitors = $wpdb->get_results("SELECT * FROM $table_visitors", OBJECT);
    $result_visitors_week = $wpdb->get_results("SELECT * FROM $table_visitors WHERE last_counter BETWEEN '$dateWeekFrom' AND '$dateWeekTo'", OBJECT);
    $result_visitors_month = $wpdb->get_results("SELECT * FROM $table_visitors WHERE last_counter BETWEEN '$dateMonthFrom' AND '$dateMonthTo'", OBJECT);
    $result_visitors_year = $wpdb->get_results("SELECT * FROM $table_visitors WHERE last_counter BETWEEN '$dateYearFrom' AND '$dateYearTo'", OBJECT);
    $result_pages_today = $wpdb->get_results("SELECT * FROM $table_pages WHERE date = '$dateToday'", OBJECT);
    $result_pages_yesterday = $wpdb->get_results("SELECT * FROM $table_pages WHERE date = '$dateYesterday'", OBJECT);
    $result_pages_week = $wpdb->get_results("SELECT * FROM $table_pages WHERE date BETWEEN '$dateWeekFrom' AND '$dateWeekTo'", OBJECT);
    $result_pages_month = $wpdb->get_results("SELECT * FROM $table_pages WHERE date BETWEEN '$dateMonthFrom' AND '$dateMonthTo'", OBJECT);
    $result_pages_year = $wpdb->get_results("SELECT * FROM $table_pages WHERE date BETWEEN '$dateYearFrom' AND '$dateYearTo'", OBJECT);

    $membership_plan_name = '';
    foreach ($memberships as $membership) {
        $membership_plan_name = $membership->plan->name;
    }

    //for Today Visits
    foreach ($result_visits as $visit_today) {
        if ($visit_today->last_counter == $dateToday) {
            $website_visits_today = $visit_today->visit;
            break;
        }
    }

    //for Yesterday Visits
    foreach ($result_visits as $visit_yesterday) {
        if ($visit_yesterday->last_counter == $dateYesterday) {
            $website_visits_yesterday = $visit_yesterday->visit;
            break;
        }
    }

    //for Week Visits
    foreach ($result_visits_week as $visit_week) {
        $website_visits_week += $visit_week->visit;
    }

    //for Month Visits
    foreach ($result_visits_month as $visit_month) {
        $website_visits_month += $visit_month->visit;
    }

    //for Year Visits
    foreach ($result_visits_year as $visit_year) {
        $website_visits_year += $visit_year->visit;
    }

    //for Today Visitors
    foreach ($result_visitors as $visitor_today) {
        if ($visitor_today->last_counter == $dateToday) {
            $website_visitors_today += count($visitor_today->last_counter);
        }
    }

    //for Yesterday Visitors
    foreach ($result_visitors as $visitor_yesterday) {
        if ($visitor_yesterday->last_counter == $dateYesterday) {
            $website_visitors_yesterday += count($visitor_yesterday->last_counter);
        }
    }

    //for Week Visitors
    foreach ($result_visitors_week as $visitor_week) {
        $website_visitors_week += count($visitor_week->last_counter);
    }

    //for Month Visitors
    foreach ($result_visitors_month as $visitor_month) {
        $website_visitors_month += count($visitor_month->last_counter);
    }

    //for Year Visitors
    foreach ($result_visitors_year as $visitor_year) {
        $website_visitors_year += count($visitor_year->last_counter);
    }

    //for Page Count Today
    foreach ($result_pages_today as $page_today) {
        $website_sum_pages_today += $page_today->count;
    }

    //for Page Count Yesterday
    foreach ($result_pages_yesterday as $page_yesterday) {
        $website_sum_pages_yesterday += $page_yesterday->count;
    }

    //for Page Count Week
    foreach ($result_pages_week as $page_week) {
        $website_sum_pages_week += $page_week->count;
    }

    //for Page Count Month
    foreach ($result_pages_month as $page_month) {
        $website_sum_pages_month += $page_month->count;
    }

    //for Page Count Month
    foreach ($result_pages_year as $page_year) {
        $website_sum_pages_year += $page_year->count;
    }

    $context = array(
        'website_visits_today' => $website_visits_today,
        'website_visits_yesterday' => $website_visits_yesterday,
        'website_visits_week' => $website_visits_week,
        'website_visits_month' => $website_visits_month,
        'website_visits_year' => $website_visits_year,
        'website_visitors_today' => $website_visitors_today,
        'website_visitors_yesterday' => $website_visitors_yesterday,
        'website_visitors_week' => $website_visitors_week,
        'website_visitors_month' => $website_visitors_month,
        'website_visitors_year' => $website_visitors_year,
        'website_sum_pages_today' => $website_sum_pages_today,
        'website_sum_pages_yesterday' => $website_sum_pages_yesterday,
        'website_sum_pages_week' => $website_sum_pages_week,
        'website_sum_pages_month' => $website_sum_pages_month,
        'website_sum_pages_year' => $website_sum_pages_year,
        'imported_products' => $imported_products,
        'ordered_products' => $ordered_products,
        'notifySent' => $notifySent,
        'membership' => $membership_plan_name,
        'ordered_total_sales' => $ordered_total_sales,
    );

    $js_objects = array(
        'url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ajax-nonce'),
        'website_visits_today' => $website_visits_today,
        'website_visits_yesterday' => $website_visits_yesterday,
        'website_visits_week' => $website_visits_week,
        'website_visits_month' => $website_visits_month,
        'website_visits_year' => $website_visits_year,
        'website_visitors_today' => $website_visitors_today,
        'website_visitors_yesterday' => $website_visitors_yesterday,
        'website_visitors_week' => $website_visitors_week,
        'website_visitors_month' => $website_visitors_month,
        'website_visitors_year' => $website_visitors_year,
        'website_sum_pages_today' => $website_sum_pages_today,
        'website_sum_pages_yesterday' => $website_sum_pages_yesterday,
        'website_sum_pages_week' => $website_sum_pages_week,
        'website_sum_pages_month' => $website_sum_pages_month,
        'website_sum_pages_year' => $website_sum_pages_year,
        'imported_products' => $imported_products,
        'ordered_products' => $ordered_products,
        'notifySent' => $notifySent,
        'membership' => $membership_plan_name,
        'ordered_total_sales' => $ordered_total_sales,
    );

    wp_enqueue_script('website_analytics_js');
    wp_enqueue_script('product_report_js');
    wp_enqueue_script('jquery_ui_js');
    wp_enqueue_script('chart_js');
    wp_enqueue_style('website_analytics_css');
    wp_enqueue_style('jquery_ui_css');
    wp_localize_script('website_analytics_js', 'wp_ajax', $js_objects);
    wp_localize_script('product_report_js', 'wp_ajax', $js_objects);

    if (isset($_GET['is_ajax']) && $_GET['is_ajax'] == 1) {
        $dateCustomFrom = $_GET['date_from'];
        $dateCustomTo = $_GET['date_to'];
        if ($dateCustomFrom == $dateCustomTo) {
            $result_visits_custom = $wpdb->get_results("SELECT * FROM $table_visits WHERE last_counter = '$dateCustomTo'", OBJECT);
            $result_visitors_custom = $wpdb->get_results("SELECT * FROM $table_visitors WHERE last_counter = '$dateCustomTo'", OBJECT);
            $result_pages_custom = $wpdb->get_results("SELECT * FROM $table_pages WHERE date = '$dateCustomTo'", OBJECT);

            foreach ($result_visits_custom as $visit_custom) {
                $website_visits_custom += $visit_custom->visit;
            }

            foreach ($result_visitors_custom as $visitor_custom) {
                $website_visitors_custom += count($visitor_custom->last_counter);
            }

            foreach ($result_pages_custom as $page_custom) {
                $website_sum_pages_custom += $page_custom->count;
            }

            echo json_encode(array(
                'website_visits_custom' => $website_visits_custom,
                'website_visitors_custom' => $website_visitors_custom,
                'website_sum_pages_custom' => $website_sum_pages_custom,
            ));
        } elseif ($dateCustomFrom > $dateCustomTo) {
            $result_visits_custom = $wpdb->get_results("SELECT * FROM $table_visits WHERE last_counter BETWEEN '$dateCustomTo' AND '$dateCustomFrom'", OBJECT);
            $result_visitors_custom = $wpdb->get_results("SELECT * FROM $table_visitors WHERE last_counter BETWEEN '$dateCustomTo' AND '$dateCustomFrom'", OBJECT);
            $result_pages_custom = $wpdb->get_results("SELECT * FROM $table_pages WHERE date BETWEEN '$dateCustomTo' AND '$dateCustomFrom'", OBJECT);

            foreach ($result_visits_custom as $visit_custom) {
                $website_visits_custom += $visit_custom->visit;
            }

            foreach ($result_visitors_custom as $visitor_custom) {
                $website_visitors_custom += count($visitor_custom->last_counter);
            }

            foreach ($result_pages_custom as $page_custom) {
                $website_sum_pages_custom += $page_custom->count;
            }

            echo json_encode(array(
                'website_visits_custom' => $website_visits_custom,
                'website_visitors_custom' => $website_visitors_custom,
                'website_sum_pages_custom' => $website_sum_pages_custom,
            ));
        } elseif ($dateCustomFrom < $dateCustomTo) {
            $result_visits_custom = $wpdb->get_results("SELECT * FROM $table_visits WHERE last_counter BETWEEN '$dateCustomFrom' AND '$dateCustomTo'", OBJECT);
            $result_visitors_custom = $wpdb->get_results("SELECT * FROM $table_visitors WHERE last_counter BETWEEN '$dateCustomFrom' AND '$dateCustomTo'", OBJECT);
            $result_pages_custom = $wpdb->get_results("SELECT * FROM $table_pages WHERE date BETWEEN '$dateCustomFrom' AND '$dateCustomTo'", OBJECT);

            foreach ($result_visits_custom as $visit_custom) {
                $website_visits_custom += $visit_custom->visit;
            }

            foreach ($result_visitors_custom as $visitor_custom) {
                $website_visitors_custom += count($visitor_custom->last_counter);
            }

            foreach ($result_pages_custom as $page_custom) {
                $website_sum_pages_custom += $page_custom->count;
            }

            echo json_encode(array(
                'website_visits_custom' => $website_visits_custom,
                'website_visitors_custom' => $website_visitors_custom,
                'website_sum_pages_custom' => $website_sum_pages_custom,
            ));
        } else {
            echo json_encode(array(
                'website_visits_custom' => 'Checking...',
                'website_visitors_custom' => 'Checking...',
                'website_sum_pages_custom' => 'Checking...',
            ));
        }
        die();
    } else {
        echo $timber->compile('website-analytics/website-analytics.twig', $context);
    }
}
add_shortcode('website_analytics_views', 'website_analytics_display');

add_action('wp_ajax_get_custom_date', 'website_analytics_display');
add_action('wp_ajax_get_notify', 'website_analytics_display');
