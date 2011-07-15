<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class ctl_sfile extends adminPage
{

    public function getDB( $ident )
    {
        $sfile =& $this->system->loadModel( "system/sfile" );
        $sfile->outputDB( $ident );
    }

    public function upload( )
    {
        $sfile =& $this->system->loadModel( "system/sfile" );
        $finfo = $sfile->save( $_FILES['Filedata'], array(
            "usedby" => $_POST['usedby']
        ) );
        if ( $_POST['handle'] && ( $p = strpos( $_POST['handle'], ":" ) ) )
        {
            $cls = substr( $_POST['handle'], 0, $p );
            $act = substr( $_POST['handle'], $p + 1 );
            if ( $cls =& $this->system->loadModel( $cls ) )
            {
                $finfo = $cls->$act( ( $p = strpos( $_POST['usedby'], ":" ) ) ? substr( $_POST['usedby'], $p + 1 ) : $_POST['usedby'], $finfo );
            }
        }
        echo json_encode( $finfo );
    }

    public function goodsicon( $schema_id )
    {
        header( "Content-type: image/png" );
        $this->system->sfile( PLUGIN_DIR."/schema/".$schema_id."/icon-48x48.png" );
    }

}

?>
