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

jQuery(document).ready(function ($) {
  const visitChart = document.getElementById("visitChart");

  //YEARLY BY MONTH
  const visitorData = monthNumbers.map((month) => {
    const record = wp_ajax.visit_data.find((data) => month == data.visited);
    return record ? Number(record.count) : 0;
  });

  const dvisitorData = monthDays.map((day) => {
    const record = wp_ajax.visit_data.find(
      (data) => day == data.visited.split("-")[2]
    );
    return record ? Number(record.count) : 0;
  });

  const visitorGraphData = {
    label: "Visitors",
    data: visitorData,
    fill: false,
    borderColor: "rgb(34, 139, 34)",
    tension: 0.5,
  };

  lineGraph(visitChart, visitorGraphData, months, "Visitors");
  //lineGraph(visitChart, mvisitorGraphData, monthDays, 'Visitors');

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

  $("#submitData").click(() => {
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
    const params = new URLSearchParams(query).toString();
    $.ajax({
      url: `${wp_ajax.url}&${params}`,
    }).done(function (data) {
      const graphData = JSON.parse(data);

      const visitorGraphData = {
        label: "Visitors",
        data: graphData.map((d) => d.count),
        fill: false,
        borderColor: random_rgb(),
        tension: 0.5,
      };

      const labels =
        groupBy == "yearly" ? months : graphData.map((d) => d.label);

      lineGraph(visitChart, visitorGraphData, labels, graphName);
    });
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
        },
      },
      scales: {
        yAxes: [
          {
            ticks: {
              beginAtZero: true,
              maxTicksLimit: 10,
              stepSize: 1,
            },
          },
        ],
      },
    },
  });
}
