<a href="https://github.com/newbooks/mccedb/issues/new"><img src="bug.jpg" width="50px"></a>
<div id="footer">
<table style="width: 100%;" cellpadding="0" cellspacing="0">
    <tbody>
    <tr>
        <td>
            <div style="text-align: left;">
                <small> Gunner Lab at City College of New York</small>
            </div>
        </td>
        <td>
            <div style="text-align: right;">
                <small><a href="mailto:<?php require_once('private/env.php'); echo $AdminEmail;?>">Contact us</a></small>
            </div>
        </td>
    </tr>
    </tbody>
</table>


<script src="src/tooltip.js"></script>
<script src="src/jquery.glossarize.js"></script>
<script>

    $(function(){

        $('.content').glossarizer({
            sourceURL: 'glossary.json',
            lookupTagName : 'p, ul, a, td',
            callback: function(){

                // Callback fired after glossarizer finishes its job

                new tooltip();

            }
        });


    });

</script>
