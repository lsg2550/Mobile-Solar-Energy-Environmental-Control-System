function createchart(chartID, chartType, chartName, dataLabels, data) {
    var ctx = document.getElementById(chartID).getContext("2d");
    var myChart = new Chart(ctx, {
        type: chartType,
        data: {
            labels: dataLabels,
            datasets: [{
                data: data,
                label: chartName,
                borderColor: "#3e95cd",
                fill: false
            }]
        },
        options: {
            title: {
                display: false
            }
        }
    });
}

$(function() { 
    $("#data-preview-select").on('submit', function(event) {
        event.preventDefault();
        var data = $("#data-preview-select :input").serializeArray();
        $.post("statsprocess.php", data, function(x){
            $(".charts").html(x);
        });
    });
});