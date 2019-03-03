function createchart(chartID, chartType, xAxisLabels, dataLabels, dataValues, dataCount) {
    var ctx = document.getElementById(chartID).getContext("2d");

    //Debug
    // console.log(xAxisLabels);
    // console.log(dataLabels);
    // console.log(dataValues);
    // console.log(dataCount);

    switch (chartType) {
        case "line":
            var lineChart = new Chart(ctx, {
                type: chartType,
                data: {
                    labels: xAxisLabels,
                    datasets: processDatasets(dataLabels, dataValues, dataCount, chartType)
                },
                options: {
                    title: { display: true, text: "Vital('s) Time Series" },
                    scales: {
                        xAxes: [{
                            ticks: { fontSize: 10, autoSkip: true, maxTicksLimit: 2, minRotation: 0, maxRotation: 0 },
                        }]
                    },
                    maintainAspectRatio: false
                }
            });
            break;
        case "doughnut":
            var doughnutChart = new Chart(ctx, {
                type: chartType,
                data: {
                    labels: dataLabels,
                    datasets: processDatasets(dataLabels, dataValues, dataCount, chartType)
                },
                options: {
                    title: { display: true, text: "Sensor('s) Quality Ratio" },
                    cutoutPercentage: 50
                }
            });
            break;
        default:
            break;
    }
}

function createRandomHex(returnAmount = -1) {
    if (returnAmount == -1) {
        return "#" + Math.floor(Math.random() * 255).toString(16) + Math.floor(Math.random() * 255).toString(16) + Math.floor(Math.random() * 255).toString(16);
    }

    var hexColorCodeArray = [];
    for (let index = 0; index < returnAmount; index++) {
        hexColorCodeArray.push("#" + Math.floor(Math.random() * 255).toString(16) + Math.floor(Math.random() * 255).toString(16) + Math.floor(Math.random() * 255).toString(16));
    }

    return hexColorCodeArray;
}

function processDatasets(dataLabels, dataValues, dataCount, chartType) {
    var data = [];
    var hexColorCode = createRandomHex();

    switch (chartType) {
        case "line":
            for (let index = 0; index < dataCount; index++) {
                hexColorCode = createRandomHex();
                data.push({
                    label: dataLabels[index],
                    data: dataValues[index],
                    borderColor: hexColorCode,
                    backgroundColor: hexColorCode,
                    fill: false
                });
            }
            break;
        case "doughnut":
            hexColorCode = createRandomHex(dataCount);
            data.push({
                label: dataLabels,
                data: dataValues,
                borderColor: hexColorCode,
                backgroundColor: hexColorCode,
            });
            break;
        default:
            break;
    }

    //console.log(data);
    return data;
}

function updateSensorSuccessRate(sensorData) {
    if (sensorData == -1) {
        document.getElementById("succ-read-ratio-inner").innerHTML = "N/A";
        document.getElementById("succ-read-ratio-outer").innerHTML = "N/A";
    } else {
        //Debug
        console.log(sensorData);
        document.getElementById("succ-read-ratio-inner").innerHTML = Math.round(sensorData["Inside Temperature"] * 100) + "%";
        document.getElementById("succ-read-ratio-outer").innerHTML = Math.round(sensorData["Inside Temperature"] * 100) + "%";
    }
}

function doesCSVEXist(url){
    $.ajax({
        type: "HEAD",
        url: url,
        success: function (response) {
            window.location = url;
        },
        error: function (response) {
            alert(response);
        }
    });
}

$(function () {
    var buttonSelection = null;
    var isCharts = false;
    var isCSV = false;

    //On page load
    $(window).on("load", function (event) {
        //Prevent default event of changing webpage
        event.preventDefault();

        //Get form data and button data
        var formData = $("#data-preview-select :input").serializeArray();

        //Debug
        // console.log(formData);

        //Output
        $.post("statsprocess.php", formData, function (x) {
            console.log(x);
            $(".charts").html(x);
        });
    });

    //Get the value of the button clicked - User either wants charts to display or to download a csv
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

    //Grab all input, including button value, and submit to php for processing and output
    $("#data-preview-select-form").on('submit', function (event) {
        //Prevent default event of changing webpage
        event.preventDefault();

        //Get form data and button data
        var formData = $("#data-preview-select-form :input").serializeArray();
        formData.push({ name: "formaction", value: buttonSelection });

        //Debug
        console.log(formData);

        //Output
        $.post("statsprocess.php", formData, function (x) {
            if (isCharts) {
                // console.log(x);
                $(".charts").html(x);
            } else if (isCSV) {
                console.log(window.location.protocol + "//" + window.location.host + "/" + x);
                var url = window.location.protocol + "//" + window.location.host + "/" + x;
                doesCSVEXist(url);
            }
        });
    });
});