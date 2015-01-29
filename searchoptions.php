<?php
//Current search options
if (isset($_SESSION["options"])) {
    $options=$_SESSION["options"];
} else {
    $options=array();
}

//Decompose search string, multiple match will use the last one in operators
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
        $operator = "=";
        $key = "ALL";
        $value = "$searchtxt";
    } else {
        $operator = array_pop($trueoperators);
        $fields = explode($operator,$searchtxt);
        $key=trim($fields[0]);
        $value=trim($fields[1]);
    }
    //echo $key.$operator,$value."<br>";
    $new = TRUE;
    foreach ($options as $a) {
        if ($a["key"] == $key and $a["operator"] == $operator and $a["value"] == $value) {
            $new = FALSE;
            break;
        }
    }
    if ($new) {
        $options[] = array("key" => $key, "operator" => $operator, "value" => $value);
    }

}

$_SESSION["options"] = $options;
print_r($options);

// Refine by
echo <<<HTML
<form action="searchresult.php" method="get">
    <div style="color: #606060">Refine by:</div>
        <input type="text" name="searchtxt" size="24">
        <input type="submit" value="Go">
</form>
<hr>
HTML;

echo <<<HTML
<form action="searchresult.php" method="get">
    <div style="color: #606060">Structure Method:</div>
    <hr>

    <div style="color: #606060">Structure Method:</div>
    <hr>

</form>
HTML;
