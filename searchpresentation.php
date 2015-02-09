<?php
$keys = array_keys($options);

/** Compose sql search terms
 * Search priority: protein > residue > mfe > pairwise,
 * Once one search level is valid, get unique IDs to confine other searches
 */
print_r($options);
//echo "<br>";
// Top to bottom search priority, get UNIQUEID and apply to the rest
$query_proteins="";
$query_residues="";
$query_mfe="";
$query_pairwise="";

foreach ($keys as $key) {
    //echo $key;
    $operator = $options[$key]["operator"];
    $value = $options[$key]["value"];
    if ($key == 'ALL'){
        $query_proteins .= " PDB_ID LIKE \"%$value%\" OR PROTEIN_NAME LIKE \"%$value%\" OR TAXONOMY LIKE \"%$value%\" OR REMARK LIKE \"%$value%\"";
    } elseif (strcasecmp($key,'PDB') == 0){
        $query_proteins .= " PDB_ID LIKE \"$value\"";
    } elseif (strcasecmp($key,'PROTEIN') == 0){
        $query_proteins .= " PROTEIN_NAME LIKE \"%$value%\"";
    } elseif (strcasecmp($key,'TAXONOMY') == 0){
        $query_proteins .= " TAXONOMY LIKE \"%$value%\"";
    } elseif (strcasecmp($key,'STRUCTURE METHOD') == 0){
        if (strcasecmp($value,'X RAY') == 0 or strcasecmp($value,'XRD')==0 or strcasecmp($value,'X-RAY')==0 ) {
            $value = "X-RAY DIFFRACTION";
        }
        $query_proteins .= " STRUCTURE_METHOD = \"$value\"";
    } elseif (strcasecmp($key,'PKA METHOD') == 0) {
        $query_proteins .= " PKA_METHOD = \"$value\"";
    } elseif (strcasecmp($key,'EPSILON') == 0) {
        $query_proteins .= " EPSILON = \"$value\"";
    } elseif (strcasecmp($key,'CHAIN IDS') == 0) {
        $ids = explode(",", $value);
        $ids_query = array();
        foreach ($ids as $id) {
            $id = trim($id);
            $ids_query[] = " CID = \"$id\"";
        }
        $query_residues .= join(" OR", $ids_query);
    } elseif (strcasecmp($key,'STRUCTURE SIZE') == 0) {
        $query_proteins .= "  STRUCTURE_SIZE $operator \"$value\"";
    } elseif (strcasecmp($key,'RESOLUTION') == 0) {
        $query_proteins .= "  RESOLUTION $operator \"$value\"";
    } elseif (strcasecmp($key,'MODEL') == 0) {
        $query_proteins .= "  MODEL $operator \"$value\"";
    } elseif (strcasecmp($key,'ISOELECTRIC POINT') == 0) {
        $query_proteins .= "  ISOELECTRIC_POINT $operator \"$value\"";
    } elseif (strcasecmp($key,'REMARK') == 0) {
        $query_proteins .= " REMARK LIKE \"%$value%\"";
    } elseif (strcasecmp($key,'RESIDUE') == 0) {
        $value = strtoupper($value);
        $query_residues .= " RESNAME = \"$value\"";
    } elseif (strcasecmp($key,'SEQUENCE') == 0) {
        $query_residues .= " SEQ $operator \"$value\"";
    } elseif (strcasecmp($key,'PKA') == 0) {
        $query_residues .= " PKA $operator \"$value\"";
    } elseif (strcasecmp($key,'SEQUENCE') == 0) {
        $query_residues .= " SEQ $operator \"$value\"";
    } elseif (strcasecmp($key,'DSOL') == 0) {
        $query_mfe .= " DSOL $operator \"$value\"";
    } elseif (strcasecmp($key,'TOTALPW') == 0) {
        $query_mfe .= " TOTALPW $operator \"$value\"";
    } elseif (strcasecmp($key,'PAIRWISE') == 0) {
        $query_pairwise .= " PAIRWISE $operator \"$value\"";
    }
}


if (!empty($query_proteins)) {
    //echo $query_proteins."<br>";
    $query_proteins = "SELECT * FROM proteins WHERE ".$query_proteins;
    //echo $query_proteins."<br>";
}
if (!empty($query_residues)) {
    $query_residues="SELECT * FROM residues WHERE ".$query_residues;
}
if (!empty($query_mfe)) {
    //echo $query_proteins."<br>";
    $query_mfe = "SELECT * FROM mfe WHERE ".$query_mfe;
    //echo $query_proteins."<br>";
}
if (!empty($query_pairwise)) {
    //echo $query_proteins."<br>";
    $query_pairwise = "SELECT * FROM pairwise WHERE ".$query_pairwise;
    //echo $query_proteins."<br>";
}

echo $query_proteins."<br>";
echo $query_residues."<br>";
echo $query_mfe."<br>";
echo $query_pairwise."<br>";

/** get UNIQUEIDs from top to bottom search priority */


/** 1-20 results of 100 */

/** removable search  options */

/** switch view */

/** results */