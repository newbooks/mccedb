<div class="removable_options">Residues</div>

<div class="scrollable">
    <div id="ResiduePKas"> </div>
</div>


<script type="text/javascript">

    var parts = window.location.search.substr(1).split("&");
    var $_GET = {};
    for (var i = 0; i < parts.length; i++) {
        var temp = parts[i].split("=");
        $_GET[decodeURIComponent(temp[0])] = decodeURIComponent(temp[1]);
    }

    var uniqueid = $_GET.id;
    var titrations = {};

    // Get protein
    $.ajax({
        dataType: "json",
        url: "dbengine.php",
        data: {"uniqueid": uniqueid, "level":"protein"},
        success: function (protein) {
            var fields=protein.PROTEIN_TITRATION.split(";");
            titrations.Protein_PI = {};
            titrations.Protein_PI.label = "Protein PI";
            titrations.Protein_PI.pKa = protein.ISOELECTRIC_POINT;
            titrations.Protein_PI.data = [];
            for (var i=0; i < fields.length; i++) {
                var point=fields[i];
                var point_value=point.split(":");
                titrations.Protein_PI.data.push([point_value[0],point_value[1]]);
            }
        }

    });


    // Get residues
    $.ajax({
        dataType: "json",
        url: "dbengine.php",
        data: {"uniqueid": uniqueid, "level":"residue"},
        success: function (residues) {
            for (i=0; i<residues.length; i++) {
                var fields = residues[i].PKA_TITRATION.split(";");
                var name = residues[i].RESNAME+" "+residues[i].CID+" "+residues[i].SEQ;
                titrations[name] = {};
                titrations[name].label = name;
                titrations[name].pKa = residues[i].PKA;
                titrations[name].data = [];
                for (var j = 0; j < fields.length; j++) {
                    var point = fields[j];
                    var point_value = point.split(":");
                    titrations[name].data.push([point_value[0], point_value[1]]);
                }
            }
            console.log(titrations);

            var choiceContainer = $("#ResiduePKas");

            $.each(titrations, function(key, val) {
                if (val.label == 'Protein PI') {
                    choiceContainer.append('<br/><smallfont><input type="checkbox" name="' + key +
                    '" checked="checked" id="id' + key + '">' +
                    '<label for="id' + key + '">'
                    + val.label + '<span style="font-style:italic">' + '&nbsp;&nbsp;PI=' + val.titration +'</span></label>' + '</smallfont>');
                }
                else {
                    choiceContainer.append('<br/><smallfont><input type="checkbox" name="' + key +
                    '" notchecked="checked" id="id' + key + '">' +
                    '<label for="id' + key + '">'
                    + val.label + '<span style="font-style:italic">&nbsp;&nbsp;pKa=' + val.titration + '</span></label>'+'</smallfont>');
                }});
            choiceContainer.find("input").click(plotAccordingToChoices);
            function plotAccordingToChoices() {
                var data = [];
                choiceContainer.find("input:checked").each(function () {
                    var key = $(this).attr("name");
                    if (key && titrations[key])
                        data.push(titrations[key]);
                });


                if (data.length > 0)
                    return $.plot($("#placeholder"), data, {
                        xaxis: { tickDecimals: 0 },
                        series: { lines: {show: true}, points: {show: true}}, grid: {hoverable: true, clickable: true}
                    });

            }

            var plot=plotAccordingToChoices();


            function showTooltip(x, y, contents) {
                $('<div id="tooltip">' + contents + '</div>').css( {
                    position: 'absolute',
                    display: 'none',
                    top: y + 5,
                    left: x + 5,
                    border: '1px solid #fdd',
                    padding: '2px',
                    'background-color': '#fee',
                    opacity: 0.80
                }).appendTo("body").fadeIn(200);
            }
            var previousPoint = null;
            $("#placeholder").bind("plothover", function (event, pos, item) {
                $("#x").text(pos.x.toFixed(2));
                $("#y").text(pos.y.toFixed(2));
                if (item) {
                    if (previousPoint != item.dataIndex) {
                        previousPoint = item.dataIndex;
                        $("#tooltip").remove();
                        var x = item.datapoint[0].toFixed(2),
                            y = item.datapoint[1].toFixed(2);
                        showTooltip(item.pageX, item.pageY,
                            item.series.label + " at pH " + x + " = " + y);
                    }
                }
                else {
                    $("#tooltip").remove();
                    previousPoint = null;
                }
            });
            $("#placeholder").bind("plotclick", function (event, pos, item) {
                if (item) {
                    $("#clickdata").html("You clicked point <br>" + item.dataIndex + " in " + item.series.data[item.dataIndex] + ".");
                    console.log(plot);
                    //plot.highlight(item.series, item.datapoint);
                }
            });


        }
    });






</script>

