<?php
// Current search options

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
            $key = "ANY";
            $value = $searchtxt;
        } else {
            $operator = array_pop($trueoperators);
            $fields = explode($operator,$searchtxt);
            $key=strtoupper(trim($fields[0]));
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
            $key = "ANY";
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

//scan checkbox. must add the checkbox value in this list for proper scan
if (isset($_POST['checkboxes'])) {
    $all_items = array("STRUCTMETHOD1=xray", "STRUCTMETHOD2=nmr", "EPSILON1=4.0", "EPSILON2=8.0", "PKAMETHOD1=experiment",
        "PKAMETHOD2=mcce");
    foreach ($all_items as $item) {
        if (in_array($item, $_POST['checkboxes'])) {
            //selected items
            $options = Add_option($item, $options);
        } else {
            $options = Remove_option($item, $options);
        }
    }
}

//remove option
if (isset($_GET['remove'])) {
    unset($options[$_GET['remove']]);
}

/* The following is to remove "ANY=" when other constraints exist and add this when non other constraint exists */
if (!empty($options)) {
    $item="ANY=";
    $options = Remove_option($item, $options);
}
if (empty($options)) {
    $options = ["ANY" => ["operator"=>"=", "value"=>""]];
}

$_SESSION["options"] = $options;
/** Print page to get new search options
 */

//view mode switch
if (isset($_GET['switchview'])) {
    $_SESSION['view_mode'] = $_GET['switchview'];
}


//print_r($options);

// Refine by

echo <<<HTML
<form action="searchresult.php" method="post">
    <div style="color: #606060">Refine by:</div>
        <input type="text" name="refinesearchtxt" size="24" class="autoc">
        <input type="submit" value="Go">
</form>
<hr>
HTML;

// Suggestions, check boxes, Any checkbox value should also be added to the scan section above
echo '<form id="form" action="searchresult.php" method="post">';

/* This is a flag, whose function is to indicate an event in checkbox click, even if all are unchecked */
echo '   <input type="checkbox" class="hidden" value="flag" name="checkboxes[]" checked="checked" style="display:none"/>';

echo '   <div style="color: #606060">Structure Method:</div>';
echo '   <input type="checkbox" class="checkbox" value="STRUCTMETHOD1=xray" name="checkboxes[]" ';
if (isset($options['STRUCTMETHOD1'])) {
    echo ' checked="checked" ';
}
echo '> X Ray<br></input>';
echo '    <input type="checkbox" class="checkbox" value="STRUCTMETHOD2=nmr" name="checkboxes[]" ';
if (isset($options['STRUCTMETHOD2'])) {
    echo ' checked="checked" ';
}
echo '> NMR<br></input>';
echo '  <hr> ';

echo '   <div style="color: #606060">pKa Method:</div>';
echo '   <input type="checkbox" class="checkbox" value="PKAMETHOD1=experiment" name="checkboxes[]" ';
if (isset($options['PKAMETHOD1'])) {
    echo ' checked="checked" ';
}
echo '> Experiment<br></input>';
echo '   <input type="checkbox" class="checkbox" value="PKAMETHOD2=mcce" name="checkboxes[]" ';
if (isset($options['PKAMETHOD2'])) {
    echo ' checked="checked" ';
}
echo '> MCCE<br></input>';
echo '<hr>';

echo '   <div style="color: #606060">MCCE restrictions:</div>';
echo '   <input type="checkbox" class="checkbox" value="EPSILON1=4.0" name="checkboxes[]" ';
if (isset($options['EPSILON1'])) {
    echo ' checked="checked" ';
}
echo '> epsilon=4<br></input>';
echo '   <input type="checkbox" class="checkbox" value="EPSILON2=8.0" name="checkboxes[]" ';
if (isset($options['EPSILON2'])) {
    echo ' checked="checked" ';
}
echo '> epsilon=8<br></input>';

echo '   <hr>';
echo  '</form>';

/**  Links to related pdb entries when viewing a pdb or a pdb search has no result
 * Entry and link
 *      SI
 *      Method
 *      epsilon
 */
echo '   <div style="color: #606060">Related entries:</div>';

?>


<script type="text/javascript">
    $(function(){
     $('.checkbox').on('change',function(){
        $('#form').submit();
        });
    });
</script>

