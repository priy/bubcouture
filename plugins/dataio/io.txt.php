<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class io_txt
{

    public $name = "txt-制表符分隔的文本文件";

    public function export_begin( $keys, $type, $count )
    {
        download( $type."-".date( "YmdHis" )."(".$count.").txt" );
        echo implode( "\t", $keys )."\r\n";
        flush( );
    }

    public function export_rows( $rows )
    {
        foreach ( $rows as $row )
        {
            foreach ( $row as $k => $v )
            {
                $row[$k] = str_replace( "\n", "\\n", $v );
            }
            echo implode( "\t", $row )."\r\n";
        }
        flush( );
    }

    public function export_finish( )
    {
    }

}

?>
