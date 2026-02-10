
function get_issues_cost_datacharts(issueID, modalID = null) {
    
    let canvasIDPrefix = 'canvas_';
    let canvasID = canvasIDPrefix + issueID;

    if (modalID) {
        canvasID = modalID;
    }
    
    $.ajax({
        url: "../geral/get_issues_cost_datacharts.php",
        method: "POST",
        data: {
            issueID: issueID
        },
        dataType: "json",
    })
    .done(function (data) {
        // Declare the variables for your graph (for X and Y Axis)

        var formStatusVar = []; // X Axis Label
        var total = []; // Value and Y Axis basis
        var chartTitle = [];

        //console.log(data.length);

        for (var i in data) {
            // formStatus is taken from JSON output (see above)
            
            if (data[i].mes !== undefined) {
                formStatusVar.push(data[i].mes);
            }
            if (data[i].total !== undefined) {
                total.push(data[i].total);
            }
            // if (data.chart_title !== undefined) {
            //     chartTitle.push(data.chart_title);
            // }
        }

        chartTitle.push(data.chart_title);

        var options = {
            responsive: true,
            maintainAspectRatio: true,
            // aspectRatio: 0.5, 
            title: {
                display: true,
                text: chartTitle[0],
            },
            legend: {
                display: false,
                position: "left",
                align: "start",
            },
            plugins: {
                colorschemes: {
                    scheme: 'tableau.Tableau20'
                },
                datalabels: {
                    //formatar como moeda
                    formatter: function(value, context) {
                        return 'R$ ' + formatMoney(value);
                    }
                }
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        let mes = data.datasets[tooltipItem.datasetIndex].label;
                        let label = data.labels[tooltipItem.index];
                        let value = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                        return 'R$ ' + formatMoney(value);
                    }
                },
            },
            scales: {
                xAxes: [
                    {
                        display: true,
                    },
                ],
                yAxes: [
                    {
                        display: true,
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

        var chartdata = {
            labels: formStatusVar,
            datasets: [
                {
                    label: "Total",
                    backgroundColor: [
                        "rgba(255, 99, 132, 0.8)",
                        "rgba(54, 162, 235, 0.8)",
                        "rgba(255, 206, 86, 0.8)",
                        "rgba(75, 192, 192, 0.8)",
                        "rgba(153, 102, 255, 0.8)",
                        "rgba(255, 159, 64, 0.8)",
                    ],
                    borderColor: [
                        "rgba(255, 99, 132, 1)",
                        "rgba(54, 162, 235, 1)",
                        "rgba(255, 206, 86, 1)",
                        "rgba(75, 192, 192, 1)",
                        "rgba(153, 102, 255, 1)",
                        "rgba(255, 159, 64, 1)",
                    ],
                    hoverBackgroundColor: "#CCCCCC",
                    hoverBorderColor: "#666666",

                    data: total,
                },
            ],
        };

        //This is the div ID (within the HTML content) where you want to display the chart
        var graphTarget = $('#' + canvasID);
        var barGraph = new Chart(graphTarget, {
            // type: "pie",
            // type: "line",
            type: "bar",
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