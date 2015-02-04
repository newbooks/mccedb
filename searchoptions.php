<?php
/* Current search options
 */

function Add_option($option, $options) {
    if (isset($option)) {
        $operators = array("=", "<", ">", ">=", "<=");
        $trueoperators = array();
        $searchtxt = $option;
        foreach ($operators as $a) {
            if (strpos($searchtxt, $a) !== FALSE) {
                $trueoperators[] = $a;
            }
        }

        if (sizeof($trueoperators) == 0) {
            $operator = "=";
            $key = "ALL";
            $value = $searchtxt;
        } else {
            $operator = array_pop($trueoperators);
            $fields = explode($operator,$searchtxt);
            $key=trim($fields[0]);
            $value=trim($fields[1]);
        }
        // Remove redundant options
        $new = TRUE;
        foreach ($options as $a) {
            if (key($a) == $key and $a["operator"] == $operator and $a["value"] == $value) {
                $new = FALSE;
                break;
            }
        }
        if ($new) {
            $options[$key] = array("operator" => $operator, "value" => $value);
        }

    }
    return $options;
}

function Remove_option($option, $options) {
    if (isset($option)) {
        $operators = array("=", "<", ">", ">=", "<=");
        $trueoperators = array();
        $searchtxt = $option;
        foreach ($operators as $a) {
            if (strpos($searchtxt, $a) !== FALSE) {
                $trueoperators[] = $a;
            }
        }

        if (sizeof($trueoperators) == 0) {
            $operator = "=";
            $key = "ALL";
            $value = $searchtxt;
        } else {
            $operator = array_pop($trueoperators);
            $fields = explode($operator,$searchtxt);
            $key=trim($fields[0]);
            $value=trim($fields[1]);
        }
        while ($a=current($options)) {
            if (key($options) == $key and $a["operator"] == $operator and $a["value"] == $value) {
                unset($options[$key]);
                echo "DONE";
            }
            next($options);
        }

    }
    return $options;
}


if (isset($_SESSION["options"])) {
    $options=$_SESSION["options"];
} else {
    $options=array();
}

//Decompose search string, multiple match will use the last one in operators
if (isset($_POST['newsearchtxt'])) {
    unset($options);
    $options=array();
    $option = $_POST['newsearchtxt'];
} elseif (isset($_POST['refinesearchtxt'])) {
    $option = $_POST['refinesearchtxt'];
}
$options=Add_option($option, $options);

//scan checkbox
$all_items = array("structmethod1=xray", "structmethod2=nmr");
foreach ($all_items as $item) {
    if (in_array($item, $_POST['checkboxes'])) {
        //selected items
        $options = Add_option($item, $options);
    } else {
        $options = Remove_option($item, $options);
    }
}



// Scan radio selection states


/* Print page to get new search options

 */



$_SESSION["options"] = $options;
print_r($options);

// Refine by
echo <<<HTML
<form action="searchresult.php" method="post">
    <div style="color: #606060">Refine by:</div>
        <input type="text" name="refinesearchtxt" size="24">
        <input type="submit" value="Go">
</form>
<hr>
HTML;

// Suggestions, check boxes
//print_r($_POST);

echo '<form id="form" action="searchresult.php" method="post">';
echo '   <div style="color: #606060">Structure Method:</div>';
echo '   <input type="checkbox" class="checkbox" value="structmethod1=xray" name="checkboxes[]" ';
if (isset($options['structmethod1'])) {
    echo ' checked="checked" ';
}
echo '> X Ray</input>';
echo '    <input type="checkbox" class="checkbox" value="structmethod2=nmr" name="checkboxes[]" ';
if (isset($options['structmethod2'])) {
    echo ' checked="checked" ';
}
echo '> NMR</input> <hr> ';

echo '   <div style="color: #606060">pKa Method:</div>';
echo '   <hr>';
echo  '</form>';


echo <<<HTML
<script type="text/javascript">
    $(function(){
     $('.checkbox').on('change',function(){
        $('#form').submit();
        });
    });
</script>
HTML;

//Suggestion radio selection