const viewWebAnalytics = () => {
  return `<h3 class="viewByH3">Today</h3>
    <table>
        <tr>
            <th><center><center>Total Website Visit</center></th>
            <th><center>Total Visitors Online</center></th>
            <th><center>Total Page Views</center></th>
        </tr>
        <tr>
            <td><center class="wa_visits">${wp_ajax.website_visits_today}</center></td>
            <td><center class="wa_visitors">${wp_ajax.website_visitors_today}</center></td>
            <td><center class="wa_sum_pages">${wp_ajax.website_sum_pages_today}</center></td>
        </tr>
    </table>`;
};
jQuery(document).ready(function ($) {
  $(".view-loading").hide();
  $(".view-container").html(viewWebAnalytics);
  $("#custom_date_from").datepicker({
    dateFormat: "yy-mm-dd",
  });
  $("#custom_date_to").datepicker({
    dateFormat: "yy-mm-dd",
  });
  $("i.fa-notif").hide();
  $(".viewBySelect").on("change", function () {
    if ($(this).val() == "today") {
      $(".wa-viewby-container h3.viewByH3").html("Today");
      $(".custom_date_range").hide();
      $(".wa_visits").html(wp_ajax.website_visits_today);
      $(".wa_visitors").html(wp_ajax.website_visitors_today);
      $(".wa_sum_pages").html(wp_ajax.website_sum_pages_today);
    } else if ($(this).val() == "yesterday") {
      $(".wa-viewby-container h3.viewByH3").html("Yesterday");
      $(".custom_date_range").hide();
      $(".wa_visits").html(wp_ajax.website_visits_yesterday);
      $(".wa_visitors").html(wp_ajax.website_visitors_yesterday);
      $(".wa_sum_pages").html(wp_ajax.website_sum_pages_yesterday);
    } else if ($(this).val() == "week") {
      $(".wa-viewby-container h3.viewByH3").html("This Week");
      $(".custom_date_range").hide();
      $(".wa_visits").html(wp_ajax.website_visits_week);
      $(".wa_visitors").html(wp_ajax.website_visitors_week);
      $(".wa_sum_pages").html(wp_ajax.website_sum_pages_week);
    } else if ($(this).val() == "month") {
      $(".wa-viewby-container h3.viewByH3").html("This Month");
      $(".custom_date_range").hide();
      $(".wa_visits").html(wp_ajax.website_visits_month);
      $(".wa_visitors").html(wp_ajax.website_visitors_month);
      $(".wa_sum_pages").html(wp_ajax.website_sum_pages_month);
    } else if ($(this).val() == "year") {
      $(".wa-viewby-container h3.viewByH3").html("This Year");
      $(".custom_date_range").hide();
      $(".wa_visits").html(wp_ajax.website_visits_year);
      $(".wa_visitors").html(wp_ajax.website_visitors_year);
      $(".wa_sum_pages").html(wp_ajax.website_sum_pages_year);
    } else if ($(this).val() == "custom_date") {
      $(".wa-viewby-container h3.viewByH3").html("Custom Date");
      $(".custom_date_range").show();
      $(".wa_visits").html("Checking...");
      $(".wa_visitors").html("Checking...");
      $(".wa_sum_pages").html("Checking...");
    } else {
      $(".wa-viewby-container h3.viewByH3").html("Today");
      $(".custom_date_range").hide();
      $(".wa_visits").html(wp_ajax.website_visits_today);
      $(".wa_visitors").html(wp_ajax.website_visitors_today);
      $(".wa_sum_pages").html(wp_ajax.website_sum_pages_today);
    }
  });
  $("#custom_date_filter").click(() => {
    const custom_date_from = $("#custom_date_from").val();
    const custom_date_to = $("#custom_date_to").val();
    $(".wa_visits").html("Checking...");
    $(".wa_visitors").html("Checking...");
    $(".wa_sum_pages").html("Checking...");
    if (custom_date_from == "" || custom_date_to == "") {
      $(".custom_date_range_notif").html("* Choose Date Range");
      $("i.fa-notif").hide();
    } else {
      $("i.fa-notif").show();
      $(".custom_date_range_notif").html("");
      $.ajax({
        url: `${wp_ajax.url}`,
        type: "GET",
        contentType: "application/json",
        data: {
          action: "get_custom_date",
          is_ajax: 1,
          date_from: custom_date_from,
          date_to: custom_date_to,
        },
        success: function (response) {
          $("i.fa-notif").hide();
          const obj = jQuery.parseJSON(response);
          const visits_custom = obj.website_visits_custom;
          const visitors_custom = obj.website_visitors_custom;
          const pages_custom = obj.website_sum_pages_custom;
          $(".wa_visits").html(visits_custom);
          $(".wa_visitors").html(visitors_custom);
          $(".wa_sum_pages").html(pages_custom);
        },
      });
      //console.log(custom_date_from);
      //console.log(custom_date_to);
    }
  });
});
