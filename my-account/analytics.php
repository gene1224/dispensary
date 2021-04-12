<?php

class WebAnalytics
{
    public function __construct()
    {
        add_shortcode('website_analytics_views', [$this, 'website_analytics_display']);
        add_action('wp_ajax_get_custom_date', [$this, 'website_analytics_display']);
        add_action('wp_ajax_get_notify', [$this, 'website_analytics_display']);

    }

    public function website_analytics_display()
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

}

$webAnalytics = new WebAnalytics();