function createchart(chartID, chartType, xAxisLabels, dataLabels, dataValues, dataCount) {
    var ctx = document.getElementById(chartID).getContext("2d");

    //Debug
    console.log(xAxisLabels);
    console.log(dataLabels);
    console.log(dataValues);
    console.log(dataCount);

    switch (chartType) {
        case "line":
            var lineChart = new Chart(ctx, {
                type: chartType,
                data: {
                    labels: xAxisLabels,
                    datasets: processDatasets(dataLabels, dataValues, dataCount)
                },
                options: {
                    title: { display: false },
                    scales: {
                        xAxes: [{
                            ticks: { fontSize: 10 },
                            maxRotation: 0,
                            autoSkip: false,
                            autoSkipPadding: 0
                        }]
                    },
                    //maintainAspectRatio: false
                }
            });
            break;
        default:
            break;
    }
}

function createRandomHex() {
    return "#" + Math.floor(Math.random() * 255).toString(16) + Math.floor(Math.random() * 255).toString(16) + Math.floor(Math.random() * 255).toString(16);
}

function processDatasets(dataLabels, dataValues, dataCount){
    var data = [];
    
    for (let index = 0; index < dataCount; index++) {
        data.push({
            label: dataLabels[index],
            data: dataValues[index],
            borderColor: createRandomHex(),
            fill: false
        });
    }

    console.log(data);
    return data;
}

$(function () {
    $("#data-preview-select").on('submit', function (event) {
        event.preventDefault();
        var data = $("#data-preview-select :input").serializeArray();
        //console.log(data);
        $.post("statsprocess.php", data, function (x) {
            $(".charts").html(x);
        });
    });
});