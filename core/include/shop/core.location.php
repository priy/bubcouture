<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function shop_core_location( )
{
    if ( $_POST )
    {
        $html = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"\n            \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n            <html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en-US\" lang=\"en-US\" dir=\"ltr\">\n            <head></header><body>Redirecting...";
        $html .= "<form id=\"splash\" action=\"".$url."\" method=\"post\">".$this->_build_post( $_POST );
        $html .= "</form><script language=\"javascript\">\ndocument.getElementById('splash').submit();\n</script></html>";
        echo $html;
        exit( );
    }
    header( "Location: ".$url );
    exit( );
}

?>
