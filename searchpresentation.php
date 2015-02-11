<?php
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

// checkbox selections
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


/* Get UNIQUEIDs */
if (isset($mysql_proteins)) {
    $query="SELECT COUNT(*) from proteins WHERE".$mysql_proteins;
    //echo $query."<br>";
    $result=@mysql_query($query) or die('Invalid query: ' .mysql_error());
    $num_results = mysql_fetch_array($result, MYSQL_NUM);
    $num_result  = $num_results[0];
    echo '<table style="text-align: left; width: 100%;" cellpadding="0" cellspacing="0">';
    echo '<tr>';
    echo '    <td>Total result: '.$num_result.'</td>';

    if (strcasecmp($view_mode, "Protein") == 0) {
        echo '    <td>Protein View | <a href="searchresult.php?switchview=Residue">Residue View</a></td>';
    } else {
        echo '    <td style="text-align:right"><a href="searchresult.php?switchview=Protein">Protein View</a> | Residue View</td>';
    }
    echo '</tr>';
    echo '</table>';


    mysql_free_result($result);
} else {
    $view_mode = "Residue"; //no protein level search. switch default to residues
}

if (isset($mysql_residues)) {
    echo $mysql_residues."<br>";
}
if (isset($mysql_mfe)) {
    echo $mysql_mfe."<br>";
}
if (isset($mysql_pairwise)) {
    echo $mysql_pairwise."<br>";
}



/** get UNIQUEIDs from top to bottom search priority */


/** 1-20 results of 100 */


/** results */

