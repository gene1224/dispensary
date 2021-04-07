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

    $page_views_sql = "SELECT cast(`date` as date) as date_visited, SUM(`count`) as count FROM `". $page_views_table ."` GROUP BY cast(`date` as date) ORDER BY `date_visited` DESC LIMIT 7";

    return $wpdb->get_results($page_views_sql, ARRAY_A);
}

//ADD RANGES MONTH/YEAR/WEEK
function get_visitor_counts($site_id)
{
    global $wpdb;
    
    $visitors_table = $table_visitors = $wpdb->base_prefix . $site_id . '_statistics_visitor';

    $visitors_sql = "SELECT cast(`last_counter` as date) as date_visited, COUNT(ID) as count FROM `". $visitors_table ."` GROUP BY cast(`last_counter` as date) ORDER BY `date_visited` DESC LIMIT 7";
    
    return $wpdb->get_results($visitors_sql, ARRAY_A);
}
