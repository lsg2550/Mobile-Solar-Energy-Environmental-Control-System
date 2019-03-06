$(function () {
    $("#control-panel-form").on('submit', function (event) {
        //Prevent default event of changing webpage
        event.preventDefault();

        //Get form data and button data
        var formData = $("#control-panel-form :input").serializeArray();
        // console.log(formData);

        $.post("vitalsthreshold.php", formData, function (x) {
            // console.log(x);
            if (x.toLowerCase() == "ok") {
                var fromJQ = { isJQ : true };
                // console.log(fromJQ);
                $.post("vitalsgeneratepanel.php", fromJQ, function (ex) {
                        // console.log(ex);
                        $("#generated-control-panel").html(ex);
                });
            }
        });
    });
});