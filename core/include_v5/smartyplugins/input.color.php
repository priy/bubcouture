<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_input_color( $params, $ctl )
{
    if ( !$params['id'] )
    {
        $domid = "colorPicker_".substr( md5( rand( 0, time( ) ) ), 0, 6 );
        $params['id'] = $domid;
    }
    else
    {
        $domid = $params['id'];
    }
    if ( $params['value'] == "" )
    {
        $params['value'] = "default";
    }
    return buildtag( $params, "input autocomplete=\"off\"" )." <input type=\"button\" id=\"c_".$domid."\" style=\"width:22px;height:22px;background-color:".$params['value'].";border:0px #ccc solid;cursor:pointer\"/><script>\n    new GoogColorPicker(\"c_".$domid."\",{\n       onSelect:function(hex,rgb,el){\n          \$(\"".$domid."\").set(\"value\",hex);\n          el.setStyle(\"background-color\",hex);\n       }\n    })</script>";
}

?>
