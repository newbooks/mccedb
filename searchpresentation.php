<?php
$keys = array_keys($options);

/** Compose sql search terms
 * Search priority: protein > residue > mfe > pairwise,
 * Once one search level is valid, get unique IDs to confine other searches
 */
//print_r($options);
//echo "<br>";
$query_proteins="";
$query_residues="";
$query_mfe="SELECT * FROM proteins WHERE ";
$query_pairwise="SELECT * FROM proteins WHERE ";
foreach ($keys as $key) {
    //echo $key;
    $operator = $options[$key]["operator"];
    $value = $options[$key]["value"];
    if ($key == 'ALL'){
        $query_proteins = " PDB_ID LIKE \"%$value%\" OR PROTEIN_NAME LIKE \"%$value%\" OR TAXONOMY LIKE \"%$value%\" OR REMARK LIKE \"%$value%\"";
    } elseif (strcasecmp($key,'PDB') == 0){
        $query_proteins = " PDB_ID LIKE \"$value\"";
    } elseif (strcasecmp($key,'PROTEIN') == 0){
        $query_proteins = " PROTEIN_NAME LIKE \"%$value%\"";
    } elseif (strcasecmp($key,'TAXONOMY') == 0){
        $query_proteins = " TAXONOMY LIKE \"%$value%\"";
    } elseif (strcasecmp($key,'STRUCTURE METHOD') == 0){
        if (strcasecmp($value,'X RAY') == 0 or strcasecmp($value,'XRD')==0 or strcasecmp($value,'X-RAY')==0 ) {
            $value = "X-RAY DIFFRACTION";
        }
        $query_proteins = " STRUCTURE_METHOD = \"%$value%\"";
    }

    echo $query_proteins;
}

if (!empty($query_proteins)) {
    $query_proteins="SELECT * FROM proteins WHERE ".$query_proteins;
}



/** 1-20 results of 100 */

/** removable search  options */

/** switch view */

/** results */