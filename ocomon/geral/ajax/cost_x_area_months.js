/* $(document).ready(function () {
    showTotalGraph_06();
}); */

function cost_x_area_months(canvasId) {
    $.ajax({
        url: "../geral/cost_x_area_months.php",
        method: "POST",
        dataType: "json",
    })
    .done(function (data) {
        // console.log(data);
        // Declare the variables for your graph (for X and Y Axis)
        var months = []; // X Axis Label
        var total = []; // Value and Y Axis basis
        var areas = []; //inner labels - Legends
        var chartTitle = [];

        //console.log(data.length);

        for (var i in data.months) {
            months.push(data.months[i]);
        }

        for (var j in data.areas) {
            areas.push(data.areas[j]);
        }

        for (var l in data.totais) {
            total.push(data.totais[l]);
        }
        
        chartTitle.push(data.chart_title);


        var dataSetValue = [];
        var count = areas.length; /* Quantidade de areas recebidas */

        for (var k = 0; k < count; k++) {
            dataSetValue[k] = {
                label: areas[k],
                fill: true,
                // data: [Math.round(Math.random() * 10), Math.round(Math.random() * 10), Math.round(Math.random() * 10)]
                data: total[k]
            };
        }

        var chartdata = {
            labels: months,
            datasets : dataSetValue
        };

        var options = {
            responsive: true,
            title: {
                display: true,
                text: chartTitle,
            },
            legend: {
                display: true,
                position: "top",
                align: "start",
            },
            
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        let area = data.datasets[tooltipItem.datasetIndex].label;
                        let label = data.labels[tooltipItem.index];
                        let value = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                        return ' ' + area + ': R$ ' + formatMoney(value);
                    }
                },
            },

            // // String - Template string for single tooltips
            // tooltipTemplate: "<%if (label){%><%=label %>: <%}%><%= value + ' %' %>",
            // // String - Template string for multiple tooltips
            // multiTooltipTemplate: "<%= value + ' %' %>",
            
            plugins: {
                colorschemes: {
                    scheme: 'brewer.Paired12'
                },
                datalabels: {
                    //formatar como moeda
                    formatter: function(value, context) {
                        return 'R$ ' + formatMoney(value);
                    }
                }
            },
            scales: {
                xAxes: [
                {
                    display: true,
                },
                ],
                yAxes: [
                {
                    ticks: {
                        beginAtZero: true,
                        callback: function(value, index, values) {
                            return 'R$ ' + formatMoney(value);
                        }
                    },
                },
                ],
            },
        };

        //This is the div ID (within the HTML content) where you want to display the chart
        // var graphTarget = $("#dashboard-06");
        var graphTarget = $('#' + canvasId);
        var barGraph = new Chart(graphTarget, {
            type: "line",
            data: chartdata,
            options: options,
        });
    })
    .fail(function () {
        // $('#divError').html('<p class="text-danger text-center"><?= TRANS('FETCH_ERROR'); ?></p>');
    });
    
    return false;
}

function formatMoney(n, c, d, t) {
    c = isNaN(c = Math.abs(c)) ? 2 : c, d = d == undefined ? "," : d, t = t == undefined ? "." : t, s = n < 0 ? "-" : "", i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", j = (j = i.length) > 3 ? j % 3 : 0;
    return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
}


