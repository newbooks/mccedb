<div class="removable_options">Residues</div>

<div class="scrollable">
    <div id="ResiduePKas" />
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
    var xmlhttp = new XMLHttpRequest();
    var url = "dbengine.php?uniqueid=" + uniqueid + "&level=protein";

    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            var protein = JSON.parse(xmlhttp.responseText);
            titrations.Protein_PI = {"label": "Protein PI", "pka": protein.ISOELECTRIC_POINT, "data":[]};
            var fields=protein.PROTEIN_TITRATION.split(";");
            for (var i=0; i < fields.length; i++) {
                var point=fields[i];
                var point_value=point.split(":");
                titrations.Protein_PI.data.push([point_value[0],point_value[1]]);
            }
            console.log(titrations);

        }
    }
    xmlhttp.open("GET", url, true);
    xmlhttp.send();


    // Get residues
    var xmlhttp2 = new XMLHttpRequest();
    var url = "dbengine.php?uniqueid=" + uniqueid + "&level=residue";

    xmlhttp2.onreadystatechange = function() {
        if (xmlhttp2.readyState == 4 && xmlhttp2.status == 200) {
            var residues = JSON.parse(xmlhttp2.responseText);
/*
            for (i=0; i<residues.length; i++) {
                var res=residues[i];
                var reslabel=res.RESNAME + " " + res.CID + " " + res.SEQ;
                titrations[reslabel] = {"label": reslabel, "pka": res.PKA, "data":[]};
                var fields=res.PKA_TITRATION.split(";");
                for (var i=0; i < fields.length; i++) {
                    var point = fields[i];
                    var point_value = point.split(":");
                    titrations[reslabel].data.push([point_value[0], point_value[1]]);
                }
            }
*/

        }
    }
    xmlhttp2.open("GET", url, true);
    xmlhttp2.send();


</script>

