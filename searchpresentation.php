<?php

function num_mode($start, $end, $num_result, $items_per_page, $view_mode) {
    echo '<table style="width: 100%;" cellpadding="0" cellspacing="0">';
    echo '<tr>';
    echo "    <td>Results in $start - $end of $num_result proteins.</td>";
    $pages=ceil($num_result/$items_per_page);
    $cur_page = ceil($start/$items_per_page);
    $start_page = max(1, $cur_page - 5);
    echo "<td>";
    if ($cur_page > 1) {
        echo ' <a href="searchresult.php?page='.($cur_page-1).'"  style="text-decoration:none;"><</a> ';
    }
    for ( $page = $start_page; $page <= min($pages, $start_page+10); $page++) {
        echo " <a href=\"searchresult.php?page=$page\">$page</a> ";
    }
    if ( $pages > $start_page+10) {
        echo " ... ";
    }
    if ($cur_page < $pages) {
        echo ' <a href="searchresult.php?page='.($cur_page+1).'"  style="text-decoration:none;">></a> ';
    }
    echo "</td>";

    if (strcasecmp($view_mode, "Protein") == 0) {
        echo '    <td style="text-align:right">Protein View | <a href="searchresult.php?switchview=Residue">Residue View</a></td>';
    } else {
        echo '    <td style="text-align:right"><a href="searchresult.php?switchview=Protein">Protein View</a> | Residue View</td>';
    }
    echo '</tr>';
    echo '</table>';
}


$keys = array_keys($options);

/** Compose sql search terms
 * Search priority: protein > residue > mfe > pairwise,
 * Once one search level is valid, get unique IDs to confine other searches
 */
//print_r($options);
//echo "<br><br>";
// Top to bottom search priority, get UNIQUEID and apply to the rest
$query_proteins=array();
$query_residues=array();
$query_mfe=array();
$query_pairwise=array();

foreach ($keys as $key) {
    //echo $key;
    $operator = $options[$key]["operator"];
    $value = $options[$key]["value"];
    if ($key == 'ANY'){
        $query_proteins[] = " ( PDB_ID LIKE \"%$value%\" OR PROTEIN_NAME LIKE \"%$value%\" OR TAXONOMY LIKE \"%$value%\" OR REMARK LIKE \"%$value%\" )";
    } elseif (strcasecmp($key,'PDB') == 0){
        $query_proteins[] = " PDB_ID LIKE \"$value\"";
    } elseif (strcasecmp($key,'PROTEIN') == 0){
        $query_proteins[] = " PROTEIN_NAME LIKE \"%$value%\"";
    } elseif (strcasecmp($key,'TAXONOMY') == 0){
        $query_proteins[] = " TAXONOMY LIKE \"%$value%\"";
    } elseif (strcasecmp($key,'STRUCTURE METHOD') == 0){
        if (strcasecmp($value,'X RAY') == 0 or strcasecmp($value,'XRD')==0 or strcasecmp($value,'X-RAY')==0 ) {
            $value = "X-RAY DIFFRACTION";
        }
        $query_proteins[] = " STRUCTURE_METHOD like \"%$value%\"";
    } elseif (strcasecmp($key,'PKA METHOD') == 0) {
        $query_proteins[] = " PKA_METHOD like \"$value\"";
    } elseif (strcasecmp($key,'EPSILON') == 0) {
        $query_proteins[] = " EPSILON = \"$value\"";
    } elseif (strcasecmp($key,'CHAIN IDS') == 0) {
        $ids = explode(",", $value);
        $ids_query = array();
        foreach ($ids as $id) {
            $id = trim($id);
            $ids_query[] = " CID = \"$id\"";
        }
        $query_residues[] = "( ".join(" OR", $ids_query)." )";
    } elseif (strcasecmp($key,'STRUCTURE SIZE') == 0) {
        $query_proteins[] = "  STRUCTURE_SIZE $operator \"$value\"";
    } elseif (strcasecmp($key,'RESOLUTION') == 0) {
        $query_proteins[] = "  RESOLUTION $operator \"$value\"";
    } elseif (strcasecmp($key,'MODEL') == 0) {
        $query_proteins[] = "  MODEL $operator \"$value\"";
    } elseif (strcasecmp($key,'ISOELECTRIC POINT') == 0) {
        $query_proteins[] = "  ISOELECTRIC_POINT $operator \"$value\"";
    } elseif (strcasecmp($key,'REMARK') == 0) {
        $query_proteins[] = " REMARK LIKE \"%$value%\"";
    } elseif (strcasecmp($key,'RESIDUE') == 0) {
        $value = strtoupper($value);
        $query_residues[] = " RESNAME = \"$value\"";
    } elseif (strcasecmp($key,'SEQUENCE') == 0) {
        $query_residues[] = " SEQ $operator \"$value\"";
    } elseif (strcasecmp($key,'PKA') == 0) {
        $query_residues[] = " PKA $operator \"$value\"";
    } elseif (strcasecmp($key,'SEQUENCE') == 0) {
        $query_residues[] = " SEQ $operator \"$value\"";
    } elseif (strcasecmp($key,'DSOL') == 0 or strcasecmp($key,'DSOLV') == 0) {
        $query_mfe[] = " DSOL $operator \"$value\"";
    } elseif (strcasecmp($key,'TOTALPW') == 0) {
        $query_mfe[] = " TOTALPW $operator \"$value\"";
    } elseif (strcasecmp($key,'PAIRWISE') == 0) {
        $query_pairwise[] = " PAIRWISE $operator \"$value\"";
    }
}

// checkbox selections to mysql query
if (isset($options['STRUCTMETHOD1']) or isset($options['STRUCTMETHOD2'])) {
    $query=array();
    if (isset($options['STRUCTMETHOD1'])) {
        $query[] = " STRUCTURE_METHOD like \"X-RAY DIFFRACTION\"";
    }
    if (isset($options['STRUCTMETHOD2'])) {
        $query[] = " STRUCTURE_METHOD like \"%NMR%\"";
    }
    $query_proteins[] = "( ".join(" OR ", $query)." )";
}
if (isset($options['PKAMETHOD1']) or isset($options['PKAMETHOD2'])) {
    $query=array();
    if (isset($options['PKAMETHOD1'])) {
        $query[] = " PKA_METHOD like \"EXPERIMENT\"";
    }
    if (isset($options['PKAMETHOD2'])) {
        $query[] = " PKA_METHOD like \"MCCE\"";
    }
    $query_proteins[] = "( ".join(" OR ", $query)." )";
}
if (isset($options['EPSILON1']) or isset($options['EPSILON2'])) {
    $query = array();
    if (isset($options['EPSILON1'])){
        $query[] = "  EPSILON=\"4.0\"";
    }
    if (isset($options['EPSILON2'])){
        $query[] = "  EPSILON=\"8.0\"";
    }
    $query_proteins[] = "( ".join(" OR ", $query)." )";
}


if (!empty($query_proteins)) {
    $mysql_proteins = join(" AND ", $query_proteins);
}
if (!empty($query_residues)) {
    $mysql_residues= join(" AND ", $query_residues);
}
if (!empty($query_mfe)) {
    $mysql_mfe = join(" AND ",$query_mfe);
}
if (!empty($query_pairwise)) {
    $mysql_pairwise = join(" AND ",$query_pairwise);
}

echo "<h2>Search results</h2>";
echo "<hr>";
/** removable search  options */
$keys=array_keys($options);
asort($keys);
foreach ($keys as $key) {
    $shownkey = trim($key, "123456789");
    $operator = $options["$key"]["operator"];
    $value = $options["$key"]["value"];
    echo "<span class='removable_options'>$shownkey$operator$value [<a href='searchresult.php?remove=$key'>X</a>]&nbsp;</span>";
}
echo "<hr>";


/** switch view */
if (isset($_SESSION["view_mode"])) {
    $view_mode = $_SESSION["view_mode"];
} else {
    $view_mode = "Protein"; //default view mode
}

require_once("private/env.php");
$con = @mysql_connect("localhost",$MySQL_user,$MySQL_passwd) or die('Could not connect: ' . mysql_error());
mysql_select_db($MySQL_database, $con);


/** get UNIQUEIDs from top to bottom search priority */
$uniqueids = array();
if (isset($mysql_proteins)) {
    $query = "SELECT UNIQUEID from proteins WHERE" . $mysql_proteins;
    //echo $query."<br>";
    $result = @mysql_query($query) or die('Invalid query: ' . mysql_error());
    while ($row = mysql_fetch_array($result)) {
        $uniqueids[] = $row['UNIQUEID'];
    }
    mysql_free_result($result);
}
if (isset($mysql_residues)) {
    $uniqueids_temp = array();
    $query = "SELECT DISTINCT(UNIQUEID) as UNIQUEID from residues WHERE" . $mysql_residues;
    //echo $query."<br>";
    $result = @mysql_query($query) or die('Invalid query: ' . mysql_error());
    while ($row = mysql_fetch_array($result)) {
        $uniqueids_temp[] = $row['UNIQUEID'];
    }

    if (empty($uniqueids)) { //fresh search starting from residue level
        unset($uniqueids);
        $uniqueids=$uniqueids_temp;
    } else { // refine from existing $uniqueids
        $uniqueids = array_intersect($uniqueids,$uniqueids_temp);
    }
    mysql_free_result($result);
}
if (isset($mysql_mfe)) {
    $uniqueids_temp = array();
    $query = "SELECT DISTINCT(UNIQUEID) as UNIQUEID from mfe WHERE" . $mysql_mfe;
    //echo $query."<br>";
    $result = @mysql_query($query) or die('Invalid query: ' . mysql_error());
    while ($row = mysql_fetch_array($result)) {
        $uniqueids_temp[] = $row['UNIQUEID'];
    }

    if (empty($uniqueids)) { //fresh search starting from residue level
        unset($uniqueids);
        $uniqueids=$uniqueids_temp;
    } else { // refine from existing $uniqueids
        $uniqueids = array_intersect($uniqueids,$uniqueids_temp);
    }
    mysql_free_result($result);
}
if (isset($mysql_pairwise)) {
    $uniqueids_temp = array();
    $query = "SELECT DISTINCT(UNIQUEID) as UNIQUEID from pairwise WHERE" . $mysql_pairwise;
    $result = @mysql_query($query) or die('Invalid query: ' . mysql_error());
    while ($row = mysql_fetch_array($result)) {
        $uniqueids_temp[] = $row['UNIQUEID'];
    }

    if (empty($uniqueids)) { //fresh search starting from residue level
        unset($uniqueids);
        $uniqueids=$uniqueids_temp;
    } else { // refine from existing $uniqueids
        $uniqueids = array_intersect($uniqueids,$uniqueids_temp);
    }
    mysql_free_result($result);
}

$num_result = count($uniqueids);


if (strcasecmp($view_mode,"Protein")==0) {
    if (isset($_GET['page'])) {
        $current_page = $_GET['page'];
    } else {
        $current_page = 1;
    }
    $start = ($current_page-1)*$PROTEINS_PER_PAGE+1;
    $end = min($num_result, $start+$PROTEINS_PER_PAGE-1);
    $uniqueids_page = array_slice($uniqueids, $start-1, $PROTEINS_PER_PAGE);

    if ($end < $start) {
        $start = $end;
    }
    num_mode($start, $end, $num_result, $PROTEINS_PER_PAGE, $view_mode);
    echo "<hr>";

    $ids = '"'.join('","', $uniqueids_page).'"'; // needs to be quoted otherwise - in uniqueid is an illegal char
    $query = "SELECT * FROM proteins WHERE UNIQUEID IN ($ids)";
    //echo $query;
    $result = @mysql_query($query) or die('Invalid query: ' . mysql_error());


    echo '<div class="protein_view">';
    while ($row = mysql_fetch_array($result)) {
        $pdb=$row['PDB_ID'];
        echo '<div style="display: inline-block; white-space: nowrap">';
        echo '<table style="width: 100%;" cellpadding="0" cellspacing="0">';
        echo "<tr>";

        echo '<td style="width: 150px"><image src="http://www.pdb.org/pdb/images/'.$pdb.'_bio_r_500.jpg" alter="assembly" style="width:150px"></image></td>';
        echo '<td style="width: auto; vertical-align: bottom">';

        echo '<table style="width: 100%;" cellpadding="0" cellspacing="0">';
        echo "<tr><td style='text-align: right; white-space:nowrap; width:1%'>PDB:</td><td style='width: 5px'/>";
        echo "<td style='font-style: italic'>".$row['PDB_ID']."</td></tr>";
        echo "<tr><td style='text-align: right; white-space:nowrap; width:1%'>Chain IDs:</td><td style='width: 5px'/>";
        echo "<td style='font-style: italic'>".$row['CHAIN_IDS']."</td></tr>";
        echo "<tr><td style='text-align: right; white-space:nowrap; width:1%'>Name:</td><td style='width: 5px'/>";
        echo "<td style='font-style: italic'>".$row['PROTEIN_NAME']."</td></tr>";
        echo "<tr><td style='text-align: right; white-space:nowrap; width:1%'>Source:</td><td style='width: 5px'/>";
        echo "<td style='font-style: italic'>".$row['TAXONOMY']."</td></tr>";
        echo "<tr><td style='text-align: right; white-space:nowrap; width:1%'>pKa Method:</td><td style='width: 5px'/>";
        echo "<td style='font-style: italic'>".$row['PKA_METHOD']."</td></tr>";
        echo "<tr><td style='text-align: right; white-space:nowrap; width:1%'>Dielectric Constant:</td><td style='width: 5px'/>";
        echo "<td style='font-style: italic'>".$row['EPSILON']."</td></tr>";
        echo "<tr><td style='text-align: right; white-space:nowrap; width:1%'>Remark:</td><td style='width: 5px'/>";
        echo "<td style='font-style: italic'>".$row['REMARK']."</td></tr>";
        echo "</table>";


        echo "<hr>";
        echo "</td>";
        echo "</tr>";
        echo "</table>";
        echo "</div>";
    }
    echo "</div>";


    mysql_free_result($result);

} else { //show residues match paged uniqueids list and other residue level queries
    //$_SESSION["options"] = $options;

    if (isset($_GET["export"])) {
        if ($_GET["export"] == "residues") {
            $count=0;
            echo '<a href="searchresult.php" style="font-size: small; font-family: Sans-serif">Normal view</a>';
            echo "<hr>";
            echo '<p class="expand-res"> <a href="#">Click to show residues</a></p>';
            echo '<p class="content-res">';
            foreach($uniqueids as $uniqueid) {
                // protein level information
                $query = "SELECT PDB_ID, PKA_METHOD, EPSILON from proteins WHERE UNIQUEID = \"$uniqueid\"";
                $result = @mysql_query($query) or die('Invalid query: ' . mysql_error());
                $row = mysql_fetch_array($result);
                $pdb = $row['PDB_ID'];
                $pka_method = $row['PKA_METHOD'];
                $epsilon = $row['EPSILON'];
                mysql_free_result($result);

                $residues_to_show = array();
                if (isset($mysql_residues)) { // This doesn't need array intersect as conditions are included already
                    $query = 'SELECT RESNAME, CID, SEQ, PKA from residues WHERE UNIQUEID = "' . $uniqueid . '" AND ' . $mysql_residues . ' ORDER BY CID, SEQ';
                } else {
                    $query = 'SELECT RESNAME, CID, SEQ, PKA from residues WHERE UNIQUEID = "' . $uniqueid . '" ORDER BY CID, SEQ';
                }
                $result = @mysql_query($query) or die('Invalid query: ' . mysql_error());
                while ($row = mysql_fetch_array($result)) {
                    $residues_to_show[$row['RESNAME'] . ":" . $row['CID'] . ":" . $row['SEQ']] = $row['PKA'];
                }
                mysql_free_result($result);

                if (isset($mysql_mfe)) {
                    $residues_to_show_temp = array();
                    $query = 'SELECT DISTINCT RESNAME, CID, SEQ from mfe WHERE UNIQUEID = "' . $uniqueid . '" AND ' . $mysql_mfe . ' ORDER BY CID, SEQ';
                    $result = @mysql_query($query) or die('Invalid query: ' . mysql_error());
                    while ($row = mysql_fetch_array($result)) {
                        $residues_to_show_temp[$row['RESNAME'] . ":" . $row['CID'] . ":" . $row['SEQ']] = "";
                    }

                    $residues_to_show = array_intersect_key($residues_to_show, $residues_to_show_temp);
                    mysql_free_result($result);

                }

                if (isset($mysql_pairwise)) {
                    $residues_to_show_temp = array();
                    $query = 'SELECT DISTINCT RESNAME, CID, SEQ from pairwise WHERE UNIQUEID = "' . $uniqueid . '" AND ' . $mysql_pairwise . ' ORDER BY CID, SEQ';
                    $result = @mysql_query($query) or die('Invalid query: ' . mysql_error());
                    while ($row = mysql_fetch_array($result)) {
                        $residues_to_show_temp[$row['RESNAME'] . ":" . $row['CID'] . ":" . $row['SEQ']] = "";
                    }

                    $residues_to_show = array_intersect_key($residues_to_show, $residues_to_show_temp);
                    mysql_free_result($result);
                }
                $count += count($residues_to_show);
                foreach ($residues_to_show as $residue => $pka) {
                    $fields = explode(":", $residue);
                    printf("%s; %s; %s; %s; %s; %s; %s<br>", $pdb, $pka_method, $epsilon, $fields[0], $fields[1],$fields[2],$pka);
                }
            }
            echo '</p>';
            echo "<hr>";
            echo 'Total count:'.$count.' residues<br>';
        }

    } else {
        if (isset($_GET['page'])) {
            $current_page = $_GET['page'];
        } else {
            $current_page = 1;
        }
        $start = ($current_page-1)*$PROTEINS_PER_PAGE+1;
        $end = min($num_result, $start+$PROTEINS_PER_PAGE-1);
        $uniqueids_page = array_slice($uniqueids, $start-1, $PROTEINS_PER_PAGE);

        if ($end < $start) {
            $start = $end;
        }
        num_mode($start, $end, $num_result, $PROTEINS_PER_PAGE, $view_mode);
        echo "<hr>";

        echo '<a href="searchresult.php?export=residues" style="font-size: small; font-family: Sans-serif">All in one page</a>';
        echo "<hr>";
        foreach ($uniqueids_page as $uniqueid) {
            // protein level information
            $query = "SELECT PDB_ID, PKA_METHOD, EPSILON from proteins WHERE UNIQUEID = \"$uniqueid\"";
            $result = @mysql_query($query) or die('Invalid query: ' . mysql_error());
            $row = mysql_fetch_array($result);
            $pdb = $row['PDB_ID'];
            $pka_method = $row['PKA_METHOD'];
            $epsilon = $row['EPSILON'];
            mysql_free_result($result);

            // Residue level information

            // residue level restriction
            $residues_to_show = array();
            if (isset($mysql_residues)) { // This doesn't need array intersect as conditions are included already
                $query = 'SELECT RESNAME, CID, SEQ, PKA from residues WHERE UNIQUEID = "' . $uniqueid . '" AND ' . $mysql_residues . ' ORDER BY CID, SEQ';
            } else {
                $query = 'SELECT RESNAME, CID, SEQ, PKA from residues WHERE UNIQUEID = "' . $uniqueid . '" ORDER BY CID, SEQ';
            }
            $result = @mysql_query($query) or die('Invalid query: ' . mysql_error());
            while ($row = mysql_fetch_array($result)) {
                $residues_to_show[$row['RESNAME'] . ":" . $row['CID'] . ":" . $row['SEQ']] = $row['PKA'];
            }
            mysql_free_result($result);

            if (isset($mysql_mfe)) {
                $residues_to_show_temp = array();
                $query = 'SELECT DISTINCT RESNAME, CID, SEQ from mfe WHERE UNIQUEID = "' . $uniqueid . '" AND ' . $mysql_mfe . ' ORDER BY CID, SEQ';
                $result = @mysql_query($query) or die('Invalid query: ' . mysql_error());
                while ($row = mysql_fetch_array($result)) {
                    $residues_to_show_temp[$row['RESNAME'] . ":" . $row['CID'] . ":" . $row['SEQ']] = "";
                }

                $residues_to_show = array_intersect_key($residues_to_show, $residues_to_show_temp);
                mysql_free_result($result);

            }

            if (isset($mysql_pairwise)) {
                $residues_to_show_temp = array();
                $query = 'SELECT DISTINCT RESNAME, CID, SEQ from pairwise WHERE UNIQUEID = "' . $uniqueid . '" AND ' . $mysql_pairwise . ' ORDER BY CID, SEQ';
                $result = @mysql_query($query) or die('Invalid query: ' . mysql_error());
                while ($row = mysql_fetch_array($result)) {
                    $residues_to_show_temp[$row['RESNAME'] . ":" . $row['CID'] . ":" . $row['SEQ']] = "";
                }

                $residues_to_show = array_intersect_key($residues_to_show, $residues_to_show_temp);
                mysql_free_result($result);
            }

            echo '<table style="width: 100%;" cellpadding="0" cellspacing="0">';
            echo '<tr><td style="width: 25%; vertical-align: top;" >';

            echo '<table style="width: 100%;" cellpadding="0" cellspacing="0">';
            echo '<tr><td style="text-align: center" colspan="3"><image src="http://www.pdb.org/pdb/images/' . $pdb . '_bio_r_500.jpg" alter="assembly" style="width:100px"/></td></tr>';
            echo "<tr><td style='text-align: right; white-space:nowrap; width:50%'>PDB:</td><td style='width: 5px'/>";
            echo "<td style='font-style: italic'> $pdb </td></tr>";
            echo "<tr><td style='text-align: right; white-space:nowrap; width:50%'>pKa Method:</td><td style='width: 5px'/>";
            echo "<td style='font-style: italic'> $pka_method </td></tr>";
            echo "<tr><td style='text-align: right; white-space:nowrap; width:50%'>Dielectric:</td><td style='width: 5px'/>";
            echo "<td style='font-style: italic'> $epsilon </td></tr>";
            echo '</table>';


            echo '</td>';

            echo '<td>';

            echo '<table style="width: 100%;" cellpadding="0" cellspacing="0">';
            echo '<tr>';
            echo "<th>Residue</th>";
            echo "<th>Chain ID</th>";
            echo "<th>Sequence</th>";
            echo "<th>pKa</th>";
            echo "</tr>";
            $c = False;
            foreach ($residues_to_show as $residue => $pka) {
                echo '<tr ' . (($c = !$c) ? ' class="odd_line"' : '') . '>';
                $fields = explode(":", $residue);
                echo "<td>$fields[0]</td>";
                echo "<td>$fields[1]</td>";
                echo "<td>$fields[2]</td>";
                echo "<td>$pka</td>";
                echo "</tr>";
            }
            echo "</table>";


            echo '</td></tr>';
            echo "</table>";
            echo "<hr>";
        }
    }
}
?>

<script type="text/javascript">
        $('.expand-res') . click(function () {
        $('.content-res') . slideToggle('slow');
        });
</script>
