const monthNumbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];

const getDaysInMonth = function (month, year) {
  return new Date(year, month, 0).getDate();
};

function random_rgb() {
  var o = Math.round,
    r = Math.random,
    s = 255;
  return "rgb(" + o(r() * s) + "," + o(r() * s) + "," + o(r() * s) + ")";
}

const monthDays = Array.from(
  { length: getDaysInMonth(4, 2021) },
  (_, i) => i + 1
);

const months = Array.from({ length: 12 }, (item, i) => {
  return new Date(0, i).toLocaleString("en-US", { month: "long" });
});
const date_today = new Date();
const last_week_query = "mode=date_range&last_week";
const last_two_weeks_query = "mode=date_range&last_two_weeks";
const current_year_query = `mode=yearly&year=${date_today.getFullYear()}`;
const current_month_query = `mode=monthly&year=${date_today.getFullYear()}&month=${
  date_today.getMonth() + 1
}`;

const graphData = (label, data) => {
  return {
    label: label,
    data: data,
    fill: false,
    borderColor: random_rgb(),
    tension: 0.5,
    yAxisID: "y",
    xAxisID: "x",
  };
};

jQuery(document).ready(function ($) {
  const visitChart = document.getElementById("visitChart");
  const pageChart = document.getElementById("pageChart");

  $.ajax({
    url: `${wp_ajax.url}&${last_week_query}`,
  }).done(function (data) {
    const visitorData = JSON.parse(data).visitor_data;
    const pageData = JSON.parse(data).page_data;

    const visitorGraphData = graphData(
      "This weeks visitors",
      visitorData.map((d) => d.count)
    );
    const pageGraphData = graphData(
      "This weeks page views",
      pageData.map((d) => d.count)
    );

    const visitor_labels =
      groupBy == "yearly" ? months : visitorData.map((d) => d.label);
    const page_labels =
      groupBy == "yearly" ? months : pageData.map((d) => d.label);

    lineGraph(visitChart, visitorGraphData, visitor_labels, "Visitors");
    lineGraph(pageChart, pageGraphData, page_labels, "Page Views");
  });

  const groupByEl = $("#groupBy");
  groupByEl.change(() => {
    const groupBy = groupByEl.val();
    $(".date-range-container").hide();
    if (groupBy == "monthly") {
      $(".graph-field.month").fadeIn();
    } else if (groupBy == "yearly") {
      $(".graph-field.month").fadeOut();
    } else {
      $(".date-range-container").fadeIn();
    }
  });

  const loadData = (customQuery = "") => {
    const groupBy = groupByEl.val();
    const year = $("#yearSelection").val();
    const month = $("#monthSelection").val();
    const start = $("#startDate").val();
    const end = $("#endDate").val();
    let query = {};
    let graphName = "";
    if (groupBy == "monthly") {
      query = {
        year,
        month,
        mode: "monthly",
      };
      graphName = `${months[month]} ${year} Visitors`;
    } else if (groupBy == "yearly") {
      query = {
        year,
        mode: "yearly",
      };
      graphName = `${year} Visitors`;
    } else {
      query = {
        start,
        end,
        mode: "date_range",
      };
      graphName = `${start} to ${end} Visitors`;
    }

    const params =
      customQuery == "" ? customQuery : new URLSearchParams(query).toString();

    $.ajax({
      url: `${wp_ajax.url}&${params}`,
    }).done(function (data) {
      const visitorData = JSON.parse(data).visitor_data;
      const pageData = JSON.parse(data).page_data;

      const visitorGraphData = graphData(
        "This weeks visitors",
        visitorData.map((d) => d.count)
      );

      const pageGraphData = graphData(
        "This weeks page views",
        pageData.map((d) => d.count)
      );

      const visitor_labels =
        groupBy == "yearly" ? months : visitorData.map((d) => d.label);
      const page_labels =
        groupBy == "yearly" ? months : pageData.map((d) => d.label);

      lineGraph(visitChart, visitorGraphData, visitor_labels, "Visitors");
      lineGraph(pageChart, pageGraphData, page_labels, "Page Views");
    });
  };

  $(".graph-preset").click(function () {
    const preset = $(this).attr("preset");

    switch (preset) {
      case "last_week_query":
        loadData(last_week_query);
        break;
      case "last_two_weeks_query":
        loadData(last_two_weeks_query);
        break;
      case "current_year_query":
        loadData(current_year_query);
        break;
      case "current_month_query":
        loadData(current_month_query);
        break;
      default:
        break;
    }
  });
  $("#submitData").click(() => {
    loadData();
  });
});

function lineGraph(el, data, labels, title = "") {
  let visitorLineChart = new Chart(el, {
    type: "line",
    data: {
      labels: labels,
      datasets: [data],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        title: {
          display: true,
          text: title,
          font: {
            size: 23,
            weight: "lighter",
          },
        },
        legend: {
          position: "bottom",
          labels: {
            boxWidth: 20,
            boxHeight: 16,
            font: {
              size: 20,
              weight: "normal",
            },
          },
        },
      },
      scales: {
        x: {
          ticks: {
            font: {
              size: 15,
            },
          },
        },
        y: {
          ticks: {
            font: {
              size: 15,
            },
          },
        },
      },
    },
  });
}
