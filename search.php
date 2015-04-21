<script>
    $(function() {
        var availableTags = [
            "PDB =",
            "Protein =",
            "Taxonomy =",
            "Structure Method = X RAY",
            "Structure Method = NMR",
            "pKa >",
            "pKa <",
            "DSOLV >"
        ];
        $( "#tags" ).autocomplete({
            source: availableTags
        });
    });
</script>


<form action="searchresult.php" method="post">
    <div class="right">
        Search:
        <input type="text" placeholder="e.g. PDB=1AKK" name="newsearchtxt" size="40" id="tags">
        <input type="submit" value="Go">
        <small><a href="help.php">Help</a></small>
    </div>
</form>

