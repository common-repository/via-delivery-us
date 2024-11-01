<div style="width: 100%; height: 240px; position: relative">
    <canvas id="chart_canvas_payment"></canvas>
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
           "Prepaid": "Prepaid",
           "On the day of delivery": "On the day of delivery",
           "On the day of creation": "On the day of creation",
           "Upon delivery": "Upon delivery",
         },
         ru: {
           "Prepaid": "Предоплата",
           "On the day of delivery": "В день создания",
           "On the day of creation": "В день доставки",
           "Upon delivery": "Во время доставки",
         },
       };

      function t(phrase) {
         var locale = '<?php echo esc_html($locale); ?>' || "en";
         return dictonary[locale][phrase] || phrase;
       }

       function loadData() {
           const request = new XMLHttpRequest();
           var url = "https://stat-api.viadelivery.pro/chart/payment_method?id=<?php echo esc_html($settings['shop_id']); ?>";

           request.open('GET', url);
           request.setRequestHeader('Content-Type', 'application/x-www-form-url');

           request.addEventListener("readystatechange", () => {

               if (request.readyState === 4 && request.status === 200) {
                   var jsonData = JSON.parse(request.responseText);

                   chartDataTotal = [];
                   chartData = [];
                   var prepaid_percent = 0;
                   var when_created_percent = 0;
                   var when_delivered_percent = 0;
                   var during_shipment_percent = 0;
                   for (row of jsonData) {
                       prepaid_percent = Number(row.prepaid_percent);
                       when_created_percent = Number(row.when_created_percent);
                       when_delivered_percent = Number(row.when_delivered_percent);
                       during_shipment_percent = Number(row.during_shipment_percent);
                   }
                   chartData.push(prepaid_percent);
                   chartData.push(when_created_percent);
                   chartData.push(when_delivered_percent);
                   chartData.push(during_shipment_percent);
                   chartLabels = [
                       `${t('Prepaid')}: ${prepaid_percent.toFixed(1)}%`,
                       `${t('On the day of delivery')}: ${when_created_percent.toFixed(1)}%`,
                       `${t('On the day of creation')}: ${when_delivered_percent.toFixed(1)}%`,
                       `${t('Upon delivery')}: ${during_shipment_percent.toFixed(1)}%`,
                   ];
                   startDrawing();
               }
           });
           request.send();
       }

       function drawChart() {
         var ctx = document.getElementById("chart_canvas_payment").getContext("2d");
         chart = new Chart(ctx, {
           type: "pie",
           data: {
             labels: chartLabels,
             datasets: [
               {
                 data: chartData,
                 backgroundColor: [
                   "#68D391",
                   "rgb(54, 162, 235)",
                   "#F56565",
                   "#FAF089",
                 ],
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
        //  var container = document.getElementById("container");
        //  if (container) {
        //    container.style.height = window.innerHeight - 10 + "px";
        //    container.style.width = window.innerWidth + "px";
           drawChart();
        //  } else {
        //    setTimeout(startDrawing(), 1000);
        //  }
       }

       var oldonload = window.onload || function() {};

        window.onload = function() {
            oldonload();
            loadData();
        }
    })();

</script>

       