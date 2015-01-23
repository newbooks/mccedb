<?php session_start(); ?>
<html>
<head>
    <meta content="text/html; charset=ISO-8859-1" http-equiv="content-type">
    <title>Protein pKa Database</title>
    <link rel="stylesheet" type="text/css" href="mcce.css"/>
</head>

<body>


<table style="text-align: left; width: 100%;" cellpadding="0" cellspacing="0" id="header">
    <tr>
        <td><?php include("logo.php"); ?></td>
        <td><?php include("search.php"); ?></td>
    </tr>
</table>

<h2>How to search</h2>

<h3>Initial search</h3>

<p>You can do a fresh initial search from the top search bar at any time. It supports the following search format:</p>
<ul>
    <li>All field search: Just type in any words or values. This searches all fields.</li>
    <li>Keyword search: Use search string in format as "Keyword Operator Value"</li>
</ul>

<p>Examples:</p>
<table border="1" cellpadding="2" cellspacing="0" width="100%">
    <tr>
        <th>Search string</th>
        <th>Meaning</th>
    </tr>
    <tr>
        <td>PDB=1AKK</td>
        <td>Search calculations based on structure 1AKK</td>
    </tr>
    <tr>
        <td>PDB=1AKK.A</td>
        <td>Search calculations based on chain A of structure 1AKK</td>
    </tr>
    <tr>
        <td>pKa>7.0</td>
        <td>Search residues with calculated pKa greater than 7</td>
    </tr>
    <tr>
        <td>Residue=ASP</td>
        <td>Search residue ASP</td>
    </tr>
</table>

<p>Supported keywords are (not case sensitive):</p>
<table border="1" cellpadding="2" cellspacing="0" width="100%">
    <tr>
        <th>Keyword</th>
        <th>Supported operators</th>
        <th>Value</th>
        <th>Explanation</th>
    </tr>
    <tr>
        <td>ALL</td>
        <td></td>
        <td></td>
        <td>Special string to list all residues being calculated. Users can refine search later on.</td>
    </tr>
    <tr>
        <td>PDB</td>
        <td>=</td>
        <td>string</td>
        <td>An optional chain ID can be appended with separator "."</td>
    </tr>
    <tr>
        <td>RESIDUE</td>
        <td>=</td>
        <td>string</td>
        <td></td>
    </tr>
    <tr>
        <td>PKA</td>
        <td><, >, =, <=, >=</td>
        <td>floating point number</td>
        <td></td>
    </tr>
    <tr>
        <td>RESIDUE</td>
        <td>=</td>
        <td>string</td>
        <td>Residue desolvation energy, a measurement of how deep a residue is buried.</td>
    </tr>
    <tr>
        <td>SUBMITTER</td>
        <td>=</td>
        <td>string</td>
        <td>Submitter is the user name of working directories on computing server.</td>
    </tr>
    <tr>
        <td>METHOD</td>
        <td>=</td>
        <td>MCCE, Exp, Other</td>
        <td>One of these 3 strings</td>
    </tr>
    <tr>
        <td>RESOLUTION</td>
        <td><, >, =, <=, >=</td>
        <td>floating point number, or "NMR"</td>
        <td>When using "NMR", search returns NMR determined structures.</td>
    </tr>
</table>


<h3>Refined search</h3>

<p>After initial search, a search refine options appear at the left panel, and search combinations appear on the top
    line, and search results occupies the main page. Use these two areas to add or remove search restrictions.</p>

<p class="address" style="text-align:right">
<?php
$last_modified = filemtime("help.php");
print("Last Modified ");
print(date("m/j/y", $last_modified));
?>
</p>

<?php include("footer.php"); ?>

</body>
</html>
