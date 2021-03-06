<?php session_start(); ?>
<html>
<head>
    <meta content="text/html; charset=ISO-8859-1" http-equiv="content-type">
    <title>Protein pKa Database</title>

    <link rel="stylesheet" type="text/css" media="all" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/themes/base/jquery-ui.css">
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script type="text/javascript" src="https://code.jquery.com/ui/1.10.4/jquery-ui.min.js"></script>

    <link rel="stylesheet" type="text/css" href="mcce.css"/>
</head>

<body>


<?php include("header.php") ?>

<?php include("introduction.php") ?>
<hr>

<h2>How to search</h2>

<h3>Initial search</h3>

<p>You can do a fresh initial search from the top search bar at any time. It supports the following search format:</p>
<ul>
    <li>All field search: Just type in any words or values. This searches all fields.</li>
    <li>Keyword search: Use search string in format as "Keyword Operator Value"</li>
</ul>

<p>Examples:</p>
<table border="0" cellpadding="2" cellspacing="0" width="100%">
    <tr>
        <th style="text-align: left">Search string</th>
        <th style="text-align: left">Meaning</th>
    </tr>
    <tr>
        <td style="font-style: italic">PDB=1AKK</td>
        <td>Search calculations based on structure 1AKK</td>
    </tr>
    <tr>
        <td style="font-style: italic">pKa>7.0</td>
        <td>Search residues with calculated pKa greater than 7</td>
    </tr>
    <tr>
        <td style="font-style: italic">Residue=ASP</td>
        <td>Search residue ASP</td>
    </tr>
</table>



<h3>Refined search</h3>

<p>After initial search, a search refine options appear at the left panel. This allows user to add further constraints to the search.
    Some options and constraints are provided as checkboxes for convenience.
</p>


<h3>Supported keywords are (not case sensitive):</h3>
<table border="1" cellpadding="2" cellspacing="0" width="100%">
    <tr>
        <th style="width:20%">Keyword</th>
        <th style="width:10%">Search level*</th>
        <th style="width:10%">Supported operators</th>
        <th style="width:20%">Value</th>
        <th>Explanation</th>
    </tr>
    <tr>
        <td></td>
        <td>Protein</td>
        <td></td>
        <td>string</td>
        <td>This will be used to search PDB ID, protein name, taxonomy and remark fields.</td>
    </tr>

    <tr>
        <td>PDB</td>
        <td>Protein</td>
        <td>=</td>
        <td>string</td>
        <td>An optional chain ID can be appended with separator.</td>
    </tr>
    <tr>
        <td>PROTEIN</td>
        <td>Protein</td>
        <td>=</td>
        <td>string</td>
        <td>String in protein name.</td>
    </tr>
    <tr>
        <td>TAXONOMY</td>
        <td>Protein</td>
        <td>=</td>
        <td>string</td>
        <td>String in source organism's scientific name.</td>
    </tr>
    <tr>
        <td>STRUCTURE METHOD</td>
        <td>Protein</td>
        <td>=</td>
        <td>"X RAY" or "NMR"</td>
        <td>Structure method</td>
    </tr>
    <tr>
        <td>PKA METHOD</td>
        <td>Protein</td>
        <td>=</td>
        <td>"MCCE" or "Experiment"</td>
        <td>pKa method</td>
    </tr>
    <tr>
        <td>EPSILON</td>
        <td>Protein</td>
        <td>=</td>
        <td>"4.0" or "8.0"</td>
        <td>Solution dielectric constant pKa is calculated at.</td>
    </tr>
    <tr>
        <td>CHAIN IDS</td>
        <td>Residue</td>
        <td>=</td>
        <td>characters separate by ","</td>
        <td>Chain IDs. </td>
    </tr>
    <tr>
        <td>STRUCTURE SIZE</td>
        <td>Protein</td>
        <td><,>,=,<=,>=</td>
        <td>integer</td>
        <td>Structure size in number of residues.</td>
    </tr>
    <tr>
        <td>RESOLUTION</td>
        <td>Protein</td>
        <td><,></td>
        <td>floating point number</td>
        <td>Resolution in Angstroms by X-ray crystallography. </td>
    </tr>
    <tr>
        <td>MODEL</td>
        <td>Protein</td>
        <td><,>,=,<=,>=</td>
        <td>integer</td>
        <td>Model number of NMR structures. </td>
    </tr>
    <tr>
        <td>ISOELECTRIC POINT</td>
        <td>Protein</td>
        <td><,></td>
        <td>floating point number</td>
        <td>Calculated isoelectric point of the protein. </td>
    </tr>
    <tr>
        <td>REMARK</td>
        <td>Protein</td>
        <td>=</td>
        <td>string</td>
        <td>String appear in remarks.</td>
    </tr>
    <tr>
        <td>RESIDUE</td>
        <td>Residue</td>
        <td>=</td>
        <td>string</td>
        <td>3-character residue name.</td>
    </tr>
    <tr>
        <td>SEQUENCE</td>
        <td>Residue</td>
        <td><. =, ></td>
        <td>integer</td>
        <td>sequence number.</td>
    </tr>
    <tr>
        <td>PKA</td>
        <td>Residue</td>
        <td><, ></td>
        <td>floating point number</td>
        <td>Residue pKa value</td>
    </tr>
    <tr>
        <td>DSOLV</td>
        <td>MFE</td>
        <td><,></td>
        <td>floating point number</td>
        <td>Residue desolvation energy in pH unit, a measurement of how deep a residue is buried.</td>
    </tr>
    <tr>
        <td>TOTALPW</td>
        <td>MFE</td>
        <td><,></td>
        <td>floating point number</td>
        <td>Total pairwise residue interaction in pH unit.</td>
    </tr>
    <tr>
        <td>PAIRWISE</td>
        <td>Pairwise</td>
        <td><,></td>
        <td>floating point number</td>
        <td>Pairwise residue interaction in pH unit.</td>
    </tr>
</table>
<p style="font-size: smaller">* Search levels in order of Protein, Residue, MFE, and Pairwise take more computing resources.</p>


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
