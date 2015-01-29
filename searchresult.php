<?php session_start(); ?>
<html>
<head>
    <meta content="text/html; charset=ISO-8859-1" http-equiv="content-type">
    <title>MCCE pKa Database</title>
    <link rel="stylesheet" type="text/css" href="mcce.css"/>
</head>

<body>

<?php include("header.php") ?>


<table style="text-align: left; width: 100%;" cellpadding="0" cellspacing="0">
    <tr>
        <td style="width:20%" id="leftpanel">
            <?php
                include("searchoptions.php");
            ?>
        </td>
        <td id="mainarea">
            <?php
                include("searchpresentation.php");
            ?>
        </td>
    </tr>
</table>



<?php include("footer.php"); ?>
</body>
</html>
