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
                            ticks: { fontSize: 10, autoSkip: false, minRotation: 90, maxRotation: 90 },
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

function processDatasets(dataLabels, dataValues, dataCount) {
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
    var buttonSelection = null;
    var isCharts = false;
    var isCSV = false;

    $(document).on('click', ':submit', function (event) {
        buttonSelection = $(this).val();

        if (buttonSelection == "csv") {
            isCharts = false;
            isCSV = true;
        } else if (buttonSelection == "chart") {
            isCharts = true;
            isCSV = false;
        }
    });

    $("#data-preview-select").on('submit', function (event) {
        //Prevent default event of changing webpage
        event.preventDefault();

        //Get form data and button data
        var formData = $("#data-preview-select :input").serializeArray();
        formData.push({ name: "formaction", value: buttonSelection });

        //Debug
        console.log(formData);

        //Output
        $.post("statsprocess.php", formData, function (x) {
            if (isCharts) {
                console.log(x);
                $(".charts").html(x);
            } else if (isCSV) {
                console.log(window.location.protocol + "//" + window.location.host + "/" + x);
                window.location.href = window.location.protocol + "//" + window.location.host + "/" + x;
            }
        });
    });
});