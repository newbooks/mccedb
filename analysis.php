<?php session_start(); ?>
<html>
<head>
    <meta content="text/html; charset=ISO-8859-1" http-equiv="content-type">
    <title>MCCE pKa Database</title>
    <link rel="stylesheet" type="text/css" href="mcce.css"/>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="http://d3js.org/d3.v3.min.js" charset="utf-8"></script>
</head>

<body>

<?php include("header.php") ?>


<table style="text-align: left; width: 100%;" cellpadding="0" cellspacing="0">
    <tr>
        <td style="width:80%" id="mainarea">
            <?php include("detailpresentation.php"); ?>
        </td>
        <td id="optionpanel">
            <?php include("residuelist.php"); ?>

        </td>
    </tr>
</table>



<?php include("footer.php"); ?>
</body>
</html>
