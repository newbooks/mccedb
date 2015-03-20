<table style="text-align: left; width: 100%;" cellpadding="0" cellspacing="0" >
    <tr>
        <td width="50%">
            <div id="placeholder" style="width:600px;height:400px">Titration curve</div>
        </td>
        <td style="vertical-align: top; text-align: left">
            <div id="hoverdata">Mouse at pH=</span><span id="x">0.00</span>, Charge=<span id="y">0.00</div>
            <h3 id="mfe_residue">Mean Field Energy Analysis: <span/></h3>
            <!--div id="clickdata">Delta G comes here</div-->
            <div class="small_font_bold"> All energy terms are in pH unit. </div>
            <table id="mfe_table" style="width: 100%">
                <tbody>
                <tr><td style="width: 50%"></td><td></td></tr>
                <tr><td></td><td></td></tr>
                <tr><td></td><td></td></tr>
                <tr><td></td><td></td></tr>
                <tr><td></td><td></td></tr>
                <tr><td></td><td></td></tr>
                <tr><td></td><td></td></tr>
                <tr><td></td><td></td></tr>
                </tbody>
            </table>
            <p id="mfe_dG"><span/></p>
        </td>
    </tr>
    <tr><td colspan="2"> &nbsp;</td> </tr>
    <tr>
        <td id="interaction">
            <h3>Interaction Map</h3>
            <svg />
        </td>
        <td style="vertical-align: top" id="pairwise_list">
            <h3> Pairwise Interaction  (<?php require_once("private/env.php"); echo $PAIRWISE_CUTOFF ?> pH unit): </h3>
            <div/>
        </td>
    </tr>
</table>


