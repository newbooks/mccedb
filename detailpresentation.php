<table style="text-align: left; width: 100%;" cellpadding="0" cellspacing="0" border="1">
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
        <td>
            <h3>Interaction Network ( > <span id="currentval">0.25</span> pH unit)</h3>

            <!--
            <div id="defaultval">
                Pairwise Cutoff (pH unit): <span id="currentval"></span>
            </div>
            -->

            <div id="defaultslide"></div>


            <div style="display: inline">Charge scale: </div>
            <div style="display: inline">-1.0</div>
            <div style="display: inline"  id="vis1"></div>
            <div style="display: inline">0.0</div>
            <div style="display: inline" id="vis2"></div>
            <div style="display: inline">1.0</div>



            <div id="interaction"></div>

            <svg style="display:block;"></svg>


        </td>
        <td style="vertical-align: top">
            <h3> Pairwise Interaction: </h3>
            <table id="pairwise_list" style="width: 100%">
                <tbody></tbody>
            </table>



        </td>
    </tr>
</table>


