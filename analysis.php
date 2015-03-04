<?php session_start(); ?>
<html>
<head>
    <meta content="text/html; charset=ISO-8859-1" http-equiv="content-type">
    <title>MCCE pKa Database</title>
    <link rel="stylesheet" type="text/css" href="mcce.css"/>
    <!-- script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script -->
    <script language="javascript" type="text/javascript" src="jquery.js"></script>
    <script language="javascript" type="text/javascript" src="jquery.flot.js"></script>
    <script src="http://d3js.org/d3.v3.min.js" charset="utf-8"></script>


</head>

<body>

<?php include("header.php") ?>


<table style="text-align: left; width: 100%;" cellpadding="0" cellspacing="0">
    <tr>
        <td colspan="2">
            <h2>Calculation Details</h2>
            <hr>
            <?php include("protein_summary.php"); ?>
            <hr>
        </td>
    </tr>
    <tr>
        <td style="width:80%" id="mainarea">
            <?php include("detailpresentation.php"); ?>
        </td>
        <td id="optionpanel">
            <?php include("residuelist.php"); ?>

        </td>
    </tr>
</table>



<?php include("footer.php"); ?>


<script type="text/javascript">

    /* Residue list and MFE */
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
        data: {"uniqueid": uniqueid, "level": "protein"},
        success: function (protein) {
            var fields = protein.PROTEIN_TITRATION.split(";");
            titrations.Protein_PI = {};
            titrations.Protein_PI.label = "Protein PI";
            titrations.Protein_PI.pKa = protein.ISOELECTRIC_POINT;
            titrations.Protein_PI.data = [];
            for (var i = 0; i < fields.length; i++) {
                var point = fields[i];
                var point_value = point.split(":");
                titrations.Protein_PI.data.push([point_value[0], point_value[1]]);
            }
        }
    });


    // Get residues
    $.ajax({
        dataType: "json",
        url: "dbengine.php",
        data: {"uniqueid": uniqueid, "level": "residue"},
        success: function (residues) {

            for (i = 0; i < residues.length; i++) {
                var fields = residues[i].PKA_TITRATION.split(";");
                var name = residues[i].RESNAME + " " + residues[i].CID + " " + residues[i].SEQ;
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
            //console.log(titrations);

            var choiceContainer = $("#ResiduePKas");


            $.each(titrations, function (key, val) {
                if (val.label == 'Protein PI') {
                    choiceContainer.append('<div class="small_font" ><input type="checkbox" name="' + key +
                    '" checked="checked" id="id' + key + '"> <label for="id' + key + '">'
                    + val.label + '&nbsp;&nbsp;PI=' + val.pKa + '</label></div>');
                }
                else {
                    choiceContainer.append('<div class="small_font"><input type="checkbox" name="' + key +
                    '" notchecked="checked" id="id' + key + '"> <label for="id' + key + '">'
                    + val.label + '&nbsp;&nbsp;pKa=' + val.pKa + '</label></div>');
                }
            });


            choiceContainer.find("input").click(plotAccordingToChoices);
            function plotAccordingToChoices() {
                var datap = [];
                choiceContainer.find("input:checked").each(function () {
                    var key = $(this).attr("name");
                    if (key && titrations[key])
                        datap.push(titrations[key]);
                });

                if (datap.length > 0)
                    return $.plot($("#placeholder"), datap, {
                        xaxis: {tickDecimals: 0},
                        series: {lines: {show: true}, points: {show: true}}, grid: {hoverable: true, clickable: true}
                    });

            }

            var plot = plotAccordingToChoices();


            function showTooltip(x, y, contents) {
                $('<div id="tooltip">' + contents + '</div>').css({
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
                    $("#clickdata").html("You clicked point <br>" + item.dataIndex + " in " + item.series.data[item.dataIndex] + " of " + item.series.label + ".");
                    /* At this point, we can extract residue from item.series.label
                     * pH from item.series.data[item.dataIndex]
                     * uniqueid from global variable

                    item.series.label
                    uniqueid
                    item.series.data[item.dataIndex]
                    */



                }
            });
        }
    });

    $.ajax({
        dataType: "json",
        url: "dbengine.php",
        data: {"uniqueid": uniqueid, "level": "protein"},
        success: function (protein) {

            d3.select("body")
                .select("#protein_image")
                .append("svg")
                .append("image")
                .attr("xlink:href", "http://www.pdb.org/pdb/images/" + protein.PDB_ID + "_bio_r_500.jpg")
                .attr('x',0)
                .attr('y',0)
                .attr('width', 150)
                .attr('height',200)
                .attr("preserveAspectRatio","xMinYMin meet")

            d3.select("#PDB_ID")
                .text(protein.PDB_ID)

            d3.select("#CHAIN_IDS")
                .text(protein.CHAIN_IDS)

            d3.select("#STRUCTURE_SIZE")
                .text(protein.STRUCTURE_SIZE)

            d3.select("#STRUCTURE_METHOD")
                .text(protein.STRUCTURE_METHOD)

            d3.select("#RESOLUTION")
                .text(protein.RESOLUTION)

            d3.select("#MODEL")
                .text(protein.MODEL)

            d3.select("#PROTEIN_NAME")
                .text(protein.PROTEIN_NAME)

            d3.select("#TAXONOMY")
                .text(protein.TAXONOMY)

            d3.select("#PKA_METHOD")
                .text(protein.PKA_METHOD)

            d3.select("#EPSILON")
                .text(protein.EPSILON)

            d3.select("#REMARK")
                .text(protein.REMARK)


        }
    });





</script>


</body>
</html>
