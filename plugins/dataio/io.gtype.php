<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class io_gtype
{

    public $name = "类型定义文件";
    public $importforObjects = "gtype";

    public function import_rows( $xmlContent )
    {
        $system = $GLOBALS['system'];
        $xml = $system->loadModel( "utility/xml" );
        $arr = $xml->xml2array( $xmlContent );
        if ( $arr['goodstype'] )
        {
            return $arr;
        }
        else if ( $arr['goodstypes'] )
        {
            return $arr;
        }
        else
        {
            return array( );
        }
    }

}

?>
