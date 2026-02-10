function general_chart(dataFull, dataChartIndex, columm, amountColumn, canvasId, tooltipCallbackFunction = null, chartType = 'doughnut') {

    /* Possible chartTypes: doughnut | horizontalBar | line | bar */

    var ctx = $('#' + canvasId);
    var dataToChart = dataFull[dataChartIndex];
    var chartTitle = dataFull[dataChartIndex + '_chart_title'];

    var labels = []; // X Axis Label
    var total = []; // Value and Y Axis basis

    for (var i in dataToChart) {
        if (dataToChart[i][columm] !== undefined) {
            labels.push(dataToChart[i][columm]);
            total.push(dataToChart[i][amountColumn]);
        }
    }


    return new Chart(ctx, {
        type: chartType,
        data: {
            labels: labels,
            datasets: [{
                label: chartTitle,
                data: total,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            title: {
                display: true,
                text: chartTitle,
            },
            scales: {
                yAxes: [{
                    display: false,
                    ticks: {
                        beginAtZero: true
                    }
                }]
            },

            plugins: {
                colorschemes: {
                    // scheme: 'brewer.Paired12'
                    scheme: 'tableau.Tableau20'
                },
            },

            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        let label = data.labels[tooltipItem.index] + ': ' + data.datasets[0].data[tooltipItem.index];
                        
                        if (tooltipCallbackFunction !== null) {
                            if (tooltipCallbackFunction === 'secondsToHms') {
                                label = data.labels[tooltipItem.index] + ': ' + secondsToHms(data.datasets[0].data[tooltipItem.index]);
                            }
                        }

                        return label;
                    }
                }
            }
        }
    });
}


function secondsToHms(d) {
    d = Number(d);
    var h = Math.floor(d / 3600);
    var m = Math.floor(d % 3600 / 60);
    var s = Math.floor(d % 3600 % 60);

    var hDisplay = h > 0 ? h + (h == 1 ? ' hora, ' : ' horas, ') : '';
    var mDisplay = m > 0 ? m + (m == 1 ? ' minuto, ' : ' minutos, ') : '';
    var sDisplay = s > 0 ? s + (s == 1 ? ' segundo' : ' segundos') : '';
    return hDisplay + mDisplay + sDisplay;
}

