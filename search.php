<script>
    $(function() {
        var availableTags = [
            "PDB =",
            "Protein =",
            "Taxonomy =",
            "Structure Method = X RAY",
            "Structure Method = NMR",
            "pKa Method = MCCE",
            "pKa Method = Experiment",
            "Epsilon = 4.0",
            "Epsilon = 8.0",
            "Chain IDs = A",
            "Chain IDs = A,B",
            "pKa >",
            "pKa <",
            "DSOLV > 3",
            "DSOLV > 4",
            "DSOLV > 5",
            "DSOLV > 6"
        ];
        $( ".autoc" ).autocomplete({
            source: availableTags
        });
    });
</script>


<form action="searchresult.php" method="post">
    <div class="right">
        Search:
        <input type="text" placeholder="e.g. PDB=1AKK" name="newsearchtxt" size="40" class="autoc">
        <input type="submit" value="Go">
        <small><a href="help.php">Help</a></small>
    </div>
</form>

