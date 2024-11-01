<div style="width: 100%; height: 240px; position: relative">
    <canvas id="chart_canvas_shipment"></canvas>
</div>

<script>
    (function(){
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
        chartDataCreated = [],
        chartDataDelivered = [],
        chartDataPickedUp = [],
        chartDataUnclailmed = [],
        chartLabels = [];

      function loadData() {
          const request = new XMLHttpRequest();
          var url = "https://stat-api.viadelivery.pro/chart/daily-shipments?id=<?php echo esc_html($settings['shop_id']); ?>";

          request.open('GET', url);
          request.setRequestHeader('Content-Type', 'application/x-www-form-url');

          request.addEventListener("readystatechange", () => {

              if (request.readyState === 4 && request.status === 200) {
                  var jsonData = JSON.parse(request.responseText);

                  (chartDataCreated = []),
                      (chartDataDelivered = []),
                      (chartDataPickedUp = []),
                      (chartDataUnclailmed = []),
                      (chartLabels = []);
                  for (row of jsonData) {
                      chartDataCreated.push(Number(row.created));
                      chartDataDelivered.push(Number(row.delivered));
                      chartDataPickedUp.push(Number(row.picked_up));
                      chartDataUnclailmed.push(Number(row.unclaimed));
                      chartLabels.push(row.date);
                  }
                  startDrawing();
              }
          });
          request.send();
      }

      var dictonary = {
        en: {
          "Total shipped": "Total shipped",
          Delivered: "Delivered",
          Issued: "Picked up",
          Unclaimed: "Unclaimed",
        },
        ru: {
          "Total shipped": "Всего отгружено",
          Delivered: "Доставлено",
          Issued: "Выдано",
          Unclaimed: "Не востребовано",
        },
      };

      function t(phrase) {
        var locale = '<?php echo esc_html($locale); ?>' || "en";
        return dictonary[locale][phrase] || phrase;
      }

      function drawChart() {
        var ctx = document.getElementById("chart_canvas_shipment").getContext("2d");
        chart = new Chart(ctx, {
          type: "bar",
          data: {
            labels: chartLabels,
            datasets: [
              {
                label: t("Total shipped"),
                backgroundColor: "#A0AEC0",
                borderColor: "#A0AEC0",
                data: chartDataCreated,
              },
              {
                label: t("Delivered"),
                backgroundColor: "#FAF089",
                borderColor: "#FAF089",
                data: chartDataDelivered,
              },
              {
                label: t("Issued"),
                backgroundColor: "#68D391",
                borderColor: "#68D391",
                data: chartDataPickedUp,
              },
              {
                label: t("Unclaimed"),
                backgroundColor: "#F56565",
                borderColor: "#F56565",
                data: chartDataUnclailmed,
              },
            ],
          },
          options: {
            legend: {
              position: "bottom",
            },
            responsive: true,
            maintainAspectRatio: false,
          },
        });
      }
      function startDrawing() {
        // var container = document.getElementById("container");
        // if (container) {
        //   container.style.height = window.innerHeight + "px";
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