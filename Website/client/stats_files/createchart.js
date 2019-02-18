function createchart(chartID, chartType, chartName, dataLabels, data) {
    var ctx = document.getElementById(chartID).getContext("2d");
    var colorCodes = createRandomHex(1);

    switch(chartType) {
        case "line":
            var chartOptions = {
                type: "LineAlt",
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
                    }
                },
                endPoint: 200,
                animation: false
            };
            var lineChart = new Chart(ctx).LineAlt(chartOptions, {
            });
            break;
        case "bar":
            var chartOptions = {
                type: "BarAlt",
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
                                beginAtZero:true
                            }
                        }]
                    },
                    endPoint: 200,
                    animation: false
                }
            };
            var barChart = new Chart(ctx, chartOptions);
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

Chart.controllers.BarAlt = Chart.controllers.Bar.extend({
    draw: function () {
        this.scale.endPoint = this.options.endPoint;
        Chart.types.Bar.prototype.draw.apply(this, arguments);
    }
});

Chart.controllers.LineAlt = Chart.controllers.Line.extend({
    draw: function () {
        this.scale.endPoint = this.options.endPoint;
        Chart.types.Line.prototype.draw.apply(this, arguments);
    }
});

/*
Try to manually set the width of the fisrt chart, 
set the maintainaspectratio to false and place both charts inside a flex div.
*/

$(function() { 
    $("#data-preview-select").on('submit', function(event) {
        event.preventDefault();
        var data = $("#data-preview-select :input").serializeArray();
        $.post("statsprocess.php", data, function(x){
            $(".charts").html(x);
        });
    });
});