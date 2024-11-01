<div style="width: 100%; height: 240px; position: relative">
    <canvas id="chart_canvas_status"></canvas>
</div>

<script>
    (function() {
        const log = (...msg) => {
        console.log(msg.join(" "));
      };

      const logj = (obj) => {
        console.log(JSON.stringify(obj, null, 2));
      };

      function get(name) {
        if (
          (name = new RegExp(
            "[?&]" + encodeURIComponent(name) + "=([^&]*)"
          ).exec(location.search))
        )
          return decodeURIComponent(name[1]);
      }

      var chart,
        loaded = false,
        chartData = [];

      var dictonary = {
        en: {
          Unclaimed: "Unclaimed",
          "Picked up": "Picked up",
        },
        ru: {
          Unclaimed: "Не востребован",
          "Picked up": "Забран покупателем",
        },
      };

      function t(phrase) {
        var locale = '<?php echo esc_html($locale); ?>' || "en";
        return dictonary[locale][phrase] || phrase;
      }

      function loadData() {
          const request = new XMLHttpRequest();
          var url = "https://stat-api.viadelivery.pro/chart/unclaimed?id=<?php echo esc_html($settings['shop_id']); ?>";

          request.open('GET', url);
          request.setRequestHeader('Content-Type', 'application/x-www-form-url');

          request.addEventListener("readystatechange", () => {

              if (request.readyState === 4 && request.status === 200) {
                  var jsonData = JSON.parse(request.responseText);

                  chartDataTotal = [];
                  chartData = [];
                  var unclaimed_percent = 0;
                  var picked_up_percent = 0;
                  var inprogress_percent = 0;
                  var total = 0;
                  for (row of jsonData) {
                      if (row.unclaimed && row.picked_up) {
                          total = Number(row.unclaimed) + Number(row.picked_up); //+Number(row.in_progress)
                          unclaimed_percent = (Number(row.unclaimed) * 100) / total;
                          picked_up_percent = (Number(row.picked_up) * 100) / total;
                          inprogress_percent = (Number(row.in_progress) * 100) / total;
                      }
                  }
                  chartData.push(unclaimed_percent);
                  chartData.push(picked_up_percent);
                  //                chartData.push(inprogress_percent);
                  //            chartLabels = ['Не востребован', 'Забран покупателем', 'Еще доставляется'];
                  chartLabels = [
                      `${t("Unclaimed")}: ${unclaimed_percent.toFixed(1)}%`,
                      `${t("Picked up")}: ${picked_up_percent.toFixed(1)}%`,
                  ];
                  startDrawing();
              }
          });
          request.send();
      }

      function drawChart() {
        var ctx = document.getElementById("chart_canvas_status").getContext("2d");
        chart = new Chart(ctx, {
          // The type of chart we want to create
          type: "pie",

          // The data for our dataset
          data: {
            labels: chartLabels,
            datasets: [
              {
                data: chartData,
                backgroundColor: ["#F56565", "#68D391", "#FAF089"],
              },
            ],
          },
          options: {
            legend: {
              position: "right",
            },
            responsive: true,
            maintainAspectRatio: false,
            tooltips: {
              callbacks: {
                label: function (tooltipItem, chart) {
                  return (
                    ": " +
                    chart.datasets[tooltipItem.datasetIndex].data[
                      tooltipItem.index
                    ].toFixed(1) +
                    "%"
                  );
                },
              },
            },
          },
        });
      }
      function startDrawing() {
        // var container = document.getElementById("container");
        // if (container) {
        //   container.style.height = window.innerHeight - 10 + "px";
        //   container.style.width = window.innerWidth + "px";
          drawChart();
        // } else {
        //   setTimeout(startDrawing(), 1000);
        // }
      }

      var oldonload = window.onload || function() {};

        window.onload = function() {
            oldonload();
            loadData();
        }

    })();
</script>