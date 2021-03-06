function createchart(chartID, chartType, xAxisLabels, dataLabels, dataValues, dataCount, dataInterval = null) {
    var ctx = document.getElementById(chartID).getContext("2d");

    //Debug
    console.log(xAxisLabels);
    console.log(dataLabels);
    console.log(dataValues);
    console.log(dataCount);
    console.log(dataInterval);

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
                            type: 'time',
                            time: {
                                unit: 'minute',
                                unitStepSize: dataInterval,
                                tooltipFormat: 'MMM D, YYYY h:mm:ss a',
                                //displayFormats: 'MMM D, YYYY h:mm:ss a',
                                /*{ 
                                    //minute: 'MMM D, YYYY h:mm:ss a' 
                                }*/
                            },
                            ticks: {
                                fontSize: 12, 
                                minRotation: 0, 
                                maxRotation: 0,
                                autoSkip: true,
                                maxTicksLimit: 10
                            }
                        }]
                    },
                    maintainAspectRatio: false,
                }
            });
            break;
        default:
            break;
    }
}

function createRandomHex(returnAmount = -1) {
    if (returnAmount == -1) {
        return "#000000".replace(/0/g,function(){return (~~(Math.random()*16)).toString(16);});
    }

    var hexColorCodeArray = [];
    for (let index = 0; index < returnAmount; index++) {
        hexColorCodeArray.push("#000000".replace(/0/g,function(){return (~~(Math.random()*16)).toString(16);}));
    }

    return hexColorCodeArray;
}


function processDatasets(dataLabels, dataValues, dataCount, chartType) {
    var data = [];
    var innerData = {};
    var hexColorCode = createRandomHex();

    switch (chartType) {
        case "line":
            for (let index = 0; index < dataCount; index++) {
                //hexColorCode = createRandomHex();

                // This line of code is due to a request to hardcode colors for each node
                switch (dataLabels[index]) {
                    case "Battery Voltage":
                        hexColorCode = "#ffbf00";
                        break;
                    case "Battery Current":
                        hexColorCode = "#bc8d00";
                        break;
                    case "PV Voltage":
                        hexColorCode = "#ff5d00";
                        break;
                    case "PV Current":
                        hexColorCode = "#bc4500";
                        break;
                    case "CC Current":
                        hexColorCode = "#ff0000";
                        break;
                    case "Inside Temperature":
                        hexColorCode = "#1d00ff";
                        break;
                    case "Inside Humidity":
                        hexColorCode = "#5c47ff";
                        break;
                    case "Outside Temperature":
                        hexColorCode = "#00ff7b";
                        break;
                    case "Outside Humidity":
                        hexColorCode = "#47ffa0";
                        break;
                    case "Clarity":
                        hexColorCode = "#7bc3c4";
                        break;
                    case "Exhaust":
                        hexColorCode = "#7a827e";
                        break;
                    default:
                        break;
                }

                data.push({
                    label: dataLabels[index],
                    data: dataValues[index],
                    borderColor: hexColorCode,
                    backgroundColor: hexColorCode,
                    fill: false
                });
            }
            break;
        default:
            break;
    }

    console.log(data);
    return data;
}

function updateSensorSuccessRate(sensorData) {
    if (sensorData == -1) {
        document.getElementById("succ-read-ratio-inner").innerHTML = "N/A";
        document.getElementById("succ-read-ratio-outer").innerHTML = "N/A";
    } else {
        //Debug
        console.log(sensorData);
        document.getElementById("succ-read-ratio-inner").innerHTML = Math.round(sensorData["InnerSensor"] * 100) + "%";
        document.getElementById("succ-read-ratio-outer").innerHTML = Math.round(sensorData["OuterSensor"] * 100) + "%";
    }
}

function doesCSVEXist(url) {
    $.ajax({
        type: "HEAD",
        url: url,
        success: function (response) {
            window.location = url;
        },
        error: function (response) {
            //alert(response);
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
            // console.log(x);
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
                // console.log(window.location.protocol + "//" + window.location.host + "/" + x);
                var url = window.location.protocol + "//" + window.location.host + "/" + x;
                doesCSVEXist(url);
            }
        });
    });
});