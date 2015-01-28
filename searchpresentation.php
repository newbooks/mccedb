<?php
//Decompose search string, multiple match will use the last one in operators
$operators= array("=", "<", ">", ">=", "<=");
$trueoperators = array();
foreach ($operators as $a) {
    if (strpos($searchtxt,$a) !== FALSE) {
        $trueoperators[] = $a;
    }
}


if (sizeof($trueoperators) == 0) {
    $operator="";
} else {
    $operator=array_pop ( $trueoperators );
}
// echo $operator;

