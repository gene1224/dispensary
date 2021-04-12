<?php
function calculate_visitor_total($site_id)
{
    global $wpdb;

    $website_visitors_total = 0;

    $table_visitors = $wpdb->base_prefix . $site_id . '_statistics_visitor';

    $result_visitors = $wpdb->get_results("SELECT * FROM $table_visitors", OBJECT);

    foreach ($result_visitors as $visitor_total) {
        $website_visitors_total += count($visitor_total->last_counter);
    }
    return $website_visitors_total;
}

//ADD RANGES MONTH/YEAR/WEEK
function get_page_view_count($site_id)
{
    global $wpdb;

    $page_views_table = $table_visitors = $wpdb->base_prefix . $site_id . '_statistics_pages';

    $page_views_sql = "SELECT cast(`date` as date) as date_visited, SUM(`count`) as count FROM `" . $page_views_table . "` GROUP BY cast(`date` as date) ORDER BY `date_visited` DESC LIMIT 7";

    return $wpdb->get_results($page_views_sql, ARRAY_A);
}

//ADD RANGES MONTH/YEAR/WEEK
function get_visitor_counts($site_id)
{
    global $wpdb;

    $visitors_table = $table_visitors = $wpdb->base_prefix . $site_id . '_statistics_visitor';

    $visitors_sql = "SELECT cast(`last_counter` as date) as date_visited, COUNT(ID) as count FROM `" . $visitors_table . "` GROUP BY cast(`last_counter` as date) ORDER BY `date_visited` DESC LIMIT 7";

    return $wpdb->get_results($visitors_sql, ARRAY_A);
}

function get_recent_orders($user_id = 0)
{
    if ($user_id == 0) {
        return [];
    }

    $site_id = get_user_site_id($user_id);

    switch_to_blog($site_id);

    $query = new WC_Order_Query(array(
        'limit' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
        'status' => 'completed',
        'date_created' => date('Y-m-d', strtotime('-7 days')) . '...' . date('Y-m-d', strtotime('today')),
    ));

    $orders = $query->get_orders();

    $current_week_sales = [];

    foreach ($orders as $order) {
        $date = date_format($order->get_date_created(), 'Y-m-d');

        $found = array_filter($current_week_sales, function ($sales) use ($date) {
            return $sales['date'] == $date;
        });

        if (!$found) {

            $current_week_sales[] = array(
                'total' => number_format($order->get_total(), 2),
                'date' => $date,
            );
        } else {

            $mapped_sales = array_map(function ($sales) use ($date, $order) {
                if ($sales['date'] == $date) {

                    return array(
                        'date' => $date,
                        'total' => number_format($sales['total'] + $order->get_total(), 2),
                    );
                } else {
                    return $sales;
                }
            }, $current_week_sales);

            $current_week_sales = $mapped_sales;
        }
    }
    restore_current_blog();

    return $current_week_sales;
}

function get_visitor_data($site_id, $group = 'monthly', $args = [])
{
    global $wpdb;

    $visitors_table = $table_visitors = $wpdb->base_prefix . $site_id . '_statistics_visitor';

    if (!isset($args['year']) && $args['year'] == 0000) {
       $args['year'] = date("Y");
    }

    $groupBy = 'MONTH';
    
    $view_month = isset($args['view_month']) ? $args['view_month'] : false;
    
    $where_clause = "WHERE  YEAR(`last_counter`) = " . $args['year'];
    
    $view_year = isset($args['view_year']) ? $args['view_year'] : false;
    
    $group = $view_year ? 'monthly' : $group;

    switch ($group) {
        case 'monthly':
            $groupBy = $view_month ? 'MONTH' : 'DATE';
            
            $month = $view_month && isset($args['month']) ?  $args['month'] : date('m');
            if($view_month) {
                $where_clause .= "AND MONTH(`last_counter`) = ".date('m');
            }
            
            break;
        case 'weekly':
            $groupBy = 'WEEK';
            break;
        case 'daily':
            $groupBy = 'DATE';
            break;
        default:
            $groupBy = 'YEAR';
            break;
    }
    
    
    

    $visitors_sql = "SELECT " . $groupBy . "(`last_counter`) as `visited`, COUNT(ID) as count FROM `" . $visitors_table . "` " . $where_clause . " GROUP BY " . $groupBy . "(`last_counter`) ORDER BY `visited` ";
    echo $visitors_sql;
    return $wpdb->get_results($visitors_sql, ARRAY_A);
}

function test_data() {
    echo '<pre>';
    print_r(get_visitor_data(201,'monthly'));
    die();
}
add_action('wp_ajax_test_data', 'test_data');