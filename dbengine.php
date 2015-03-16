<?php
/**
 * Database engine
 * Provide requested data as json
 * Input: http request and get method
 *        uniqueid = ...
 *        level = protein | residue | mfe | pairwise
 *        res = ... (mfe and pairwise level)
 *        ph = ... (pairwise level)
 * Output: json file
 */
require_once("private/env.php");
$con = @mysql_connect("localhost",$MySQL_user,$MySQL_passwd) or die('Could not connect: ' . mysql_error());
mysql_select_db($MySQL_database, $con);

if (isset($_GET["uniqueid"])) {
    $uniqueid = $_GET["uniqueid"];
    if (isset($_GET["level"])) {
        if ($_GET["level"] == "protein") {
            $query = 'SELECT * from proteins WHERE UNIQUEID = "' . $uniqueid . '"';
            $result = @mysql_query($query) or die('Invalid query: ' . mysql_error());
            $row = mysql_fetch_array($result);
            echo json_encode($row);
            mysql_free_result($result);
        } elseif ($_GET["level"] == "residue") {
            $query = 'SELECT * from residues WHERE UNIQUEID = "' . $uniqueid . '" ORDER BY CID, SEQ';
            $result = @mysql_query($query) or die('Invalid query: ' . mysql_error());
            $allresidues = array();
            while ($row = mysql_fetch_array($result)) {
                $allresidues[]=$row;
            }
            mysql_free_result($result);
            echo json_encode($allresidues);
        } elseif ($_GET["level"] == "mfe") {
            $fields = explode(" ", $_GET["residue"]);
            $resname = $fields[0];
            $cid = $fields[1];
            $seq = $fields[2];
            $ph = $_GET["ph"];
            $query = 'SELECT * from mfe WHERE UNIQUEID = "' . $uniqueid . '" AND PH="' . $ph . '" AND RESNAME="' . $resname . '" AND CID="' .$cid. '" AND SEQ="' .$seq. '"';
            $result = @mysql_query($query) or die('Invalid query: ' . mysql_error());
            $row = mysql_fetch_array($result);
            mysql_free_result($result);
            echo json_encode($row);
        } elseif ($_GET["level"] == "pairwise") {
            $ph = $_GET["ph"];
            if (isset($_GET["residue"])) {//inquire interaction to one residue

            } else { //inquire all residue interactions
                // source
                $query = 'SELECT DISTINCT RESNAME2, CID2, SEQ2, CHARGE from pairwise WHERE UNIQUEID = "' . $uniqueid . '" AND PH="' . $ph . '"';
                $result = @mysql_query($query) or die('Invalid query: ' . mysql_error());
                $nodes = array();
                while ($row = mysql_fetch_array($result)) {
                    $nodes[$row['RESNAME2'] . " " . $row['CID2'] . " " . $row['SEQ2']] = $row['CHARGE'];
                }
                mysql_free_result($result);
                // target, no charge info
                $query = 'SELECT DISTINCT RESNAME, CID, SEQ, CHARGE from mfe WHERE UNIQUEID = "' . $uniqueid . '" AND PH="' . $ph . '"';
                $result = @mysql_query($query) or die('Invalid query: ' . mysql_error());
                while ($row = mysql_fetch_array($result)) {
                    if (!isset($nodes[$row['RESNAME'] . " " . $row['CID'] . " " . $row['SEQ']])) {
                        $nodes[$row['RESNAME'] . " " . $row['CID'] . " " . $row['SEQ']] = $row['CHARGE'];
                    }
                }
                mysql_free_result($result);

                $links = array();
                foreach ($nodes as $target => $charge) {
                    $fields = explode(" ", $target);
                    $query = 'SELECT RESNAME2, CID2, SEQ2, PAIRWISE from pairwise WHERE UNIQUEID = "' . $uniqueid . '" AND PH="' . $ph . '" AND RESNAME="'. $fields[0].'" AND CID="'.$fields[1].'" AND SEQ="'.$fields[2].'"' ;
                    $result = @mysql_query($query) or die('Invalid query: ' . mysql_error());
                    while ($row = mysql_fetch_array($result)) {
                        $links[] = ["source"=> $row["RESNAME2"]." ".$row["CID2"]." ".$row["SEQ2"], "target" => $target, "value" =>$row["PAIRWISE"]];
                    }
                    mysql_free_result($result);

                }


                echo json_encode($links);
            }
        }
    }
}