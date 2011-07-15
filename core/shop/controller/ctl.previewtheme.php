<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class ctl_previewtheme extends shopPage
{

    public function index( $theme )
    {
        if ( $theme )
        {
            define( "TPL_ID", $theme );
            $this->system->in_preview_theme = $theme;
        }
        $GLOBALS['_SERVER']['QUERY_STRING'] = "index.html";
        $output = $this->system->_frontend( array( "query" => "index.html" ) );
        $output['cache'] = FALSE;
        $this->system->display( $output );
    }

}

?>
