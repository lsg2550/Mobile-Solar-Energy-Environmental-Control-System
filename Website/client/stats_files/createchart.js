function createchart(chartID, chartType, chartName, dataLabels, data) {
    var ctx = document.getElementById(chartID).getContext("2d");
    var colorCodes = createRandomHex(1);

    //Debug
    //console.log(dataLabels);
    //console.log(data);

    switch (chartType) {
        case "line":
            var lineChart = new Chart(ctx, {
                type: chartType,
                data: {
                    labels: dataLabels,
                    datasets: [{
                        data: data,
                        label: chartName,
                        borderColor: colorCodes,
                        fill: false
                    }]
                },
                options: {
                    title: {
                        display: false
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                fontSize: 10
                            }
                        }],
                        xAxes: [{
                            maxRotation: 0,
                            autoSkip: false,
                            autoSkipPadding: 0
                        }]
                    },
                    maintainAspectRatio: false
                }
            });
            break;
        case "bar":
            var barChart = new Chart(ctx, {
                type: chartType,
                data: {
                    labels: dataLabels,
                    datasets: [{
                        data: data,
                        label: chartName,
                        backgroundColor: colorCodes,
                        borderColor: colorCodes,
                        borderWidth: 1,
                    }]
                },
                options: {
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                fontSize: 10
                            }
                        }],
                        xAxes: [{
                            maxRotation: 0,
                            autoSkip: false,
                            autoSkipPadding: 0
                        }]
                    },
                    maintainAspectRatio: false
                }
            });
            break;
        default:
            break;
    }
}

function createRandomHex(sizeOfData) {
    var colorCodeArr = [];

    for (let index = 0; index < sizeOfData; index++) {
        colorCodeArr.push("#"
            + Math.floor(Math.random() * 255).toString(16)
            + Math.floor(Math.random() * 255).toString(16)
            + Math.floor(Math.random() * 255).toString(16)
        );
    }

    return colorCodeArr;
}

$(function () {
    $("#data-preview-select").on('submit', function (event) {
        event.preventDefault();
        var data = $("#data-preview-select :input").serializeArray();
        $.post("statsprocess.php", data, function (x) {
            $(".charts").html(x);
        });
    });
});