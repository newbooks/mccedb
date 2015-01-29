<?php
//Current search options
if (isset($_SESSION["options"])) {
    $options=array();
} else {
    $options=array();
}

//Decompose search string, multiple match will use the last one in operators
$searchtxt = $_GET['searchtxt'];
if (isset($_GET['searchtxt'])) {
    $operators = array("=", "<", ">", ">=", "<=");
    $trueoperators = array();
    $searchtxt = $_GET['searchtxt'];
    foreach ($operators as $a) {
        if (strpos($searchtxt, $a) !== FALSE) {
            $trueoperators[] = $a;
        }
    }


    if (sizeof($trueoperators) == 0) {
        $operator = "";
    } else {
        $operator = array_pop($trueoperators);
    }
    echo $operator;
} else {
    echo "None";
}

$_SESSION["options"] = $options;