<div id="container" style="width: 100%; height: 240px; position: relative">
    <canvas id="chart_canvas_cities"></canvas>
</div>

<script>
    (function() {
        var chart,
            loaded = false,
            chartDataTotal = [],
            chartDataPickedUp = [],
            chartDataUnclailmed = [],
            chartDataInprogress = [];
            chartLabels = [];

        var dictonary = {
            en: {
                Total: "Total",
                Issued: "Picked up",
                Unclaimed: "Unclaimed",
                "In Transit": "In Transit",
            },

            ru: {
                Total: "Всего создано",
                Issued: "Выдано",
                Unclaimed: "Не востребовано",
                "In Transit": "Еще доставляется",
            },
        };

        function t(phrase) {
            var locale = '<?php echo esc_html($locale); ?>' || "en";
            return dictonary[locale][phrase] || phrase;
        }

        function loadData() {
            const request = new XMLHttpRequest();
            var url = "https://stat-api.viadelivery.pro/chart/cities?id=<?php echo esc_html($settings['shop_id']); ?>";

            request.open('GET', url);
            request.setRequestHeader('Content-Type', 'application/x-www-form-url');

            request.addEventListener("readystatechange", () => {

                if (request.readyState === 4 && request.status === 200) {
                    var jsonData = JSON.parse(request.responseText);

                    chartDataTotal = [];
                    chartDataPickedUp = [];
                    chartDataUnclailmed = [];
                    chartDataInprogress = [];
                    chartLabels = [];

                    for (row of jsonData) {
                        chartDataTotal.push(Number(row.total));
                        chartDataPickedUp.push(Number(row.picked_up));
                        chartDataInprogress.push(Number(row.in_progress));
                        chartDataUnclailmed.push(Number(row.unclaimed));
                        chartLabels.push(row.city);
                    }
                    startDrawing();
                }
            });
            request.send();
        }

        function drawChart() {
            var ctx = document.getElementById("chart_canvas_cities").getContext("2d");
            chart = new Chart(ctx, {
                type: "bar",
                data: {
                    labels: chartLabels,
                    datasets: [
                    {
                        label: t("Total"),
                        backgroundColor: "rgb(54, 162, 235)",
                        borderColor: "rgb(54, 162, 235)",
                        data: chartDataTotal,
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
                    {
                        label: t("In Transit"),
                        backgroundColor: "#FAF089",
                        borderColor: "#FAF089",
                        data: chartDataInprogress,
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
                // container.style.height = window.innerHeight + "px";
                // container.style.width = window.innerWidth + "px";
                drawChart();
            // } else {
            //     setTimeout(startDrawing(), 1000);
            // }
        }

        var oldonload = window.onload || function() {};

        window.onload = function() {
            oldonload();
            loadData();
        }
    })();
</script>