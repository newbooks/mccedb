<?php

function num_mode($num_result, $view_mode) {
    echo '<table style="width: 100%;" cellpadding="0" cellspacing="0">';
    echo '<tr>';
    echo '    <td>Your search returned '.$num_result.' results.</td>';
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
        $query_proteins[] = " STRUCTURE_METHOD like \"$value\"";
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
    } elseif (strcasecmp($key,'DSOL') == 0) {
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
        $query[] = " STRUCTURE_METHOD like \"NMR\"";
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
} else {
    $view_mode = "Residue";
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
num_mode($num_result, $view_mode);


if (strcasecmp($view_mode,"Protein")==0) {
    $ids = '"'.join('","', $uniqueids).'"'; // needs to be quoted otherwise - in uniqueid is an illegal char
    $query = "SELECT * FROM proteins WHERE UNIQUEID IN ($ids)";
    //echo $query;
    $result = @mysql_query($query) or die('Invalid query: ' . mysql_error());
    while ($row = mysql_fetch_array($result)) {
        echo $row['PDB_ID'];
        echo "<br>";
    }
    mysql_free_result($result);
}


/** 1-20 results of 100 */


/** results */

