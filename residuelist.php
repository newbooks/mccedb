<div class="removable_options">Residues</div>

<div class="scrollable">
    <div id="ProteinPI" />
    <div id="ResiduePKas" />
</div>

<script>



    var parts = window.location.search.substr(1).split("&");
    var $_GET = {};
    for (var i = 0; i < parts.length; i++) {
        var temp = parts[i].split("=");
        $_GET[decodeURIComponent(temp[0])] = decodeURIComponent(temp[1]);
    }

    var uniqueid = $_GET.id;
    // var $uniqueid = $_GET["id"]; // This works too

    // Get protein
    var xmlhttp = new XMLHttpRequest();
    var url = "dbengine.php?uniqueid=" + uniqueid + "&level=protein";

    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            var protein = JSON.parse(xmlhttp.responseText);
            document.write(protein.PDB_ID);
        }
    }
    xmlhttp.open("GET", url, true);
    xmlhttp.send();



    /* Get residue list and residue pKa
     var xmlhttp = new XMLHttpRequest();
     var url = "dbengine.php?uniqueid=" + uniqueid + "&level=residue";

     xmlhttp.onreadystatechange = function() {
     if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
     var residues = JSON.parse(xmlhttp.responseText);
     myFunction(residues);
     }
     }
     xmlhttp.open("GET", url, true);
     xmlhttp.send();

     function myFunction(residues) {


    }
    */

</script>

