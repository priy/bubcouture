<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class io_taobaogoodscsv
{

    public $name = "csv-订单商品（兼容淘宝格式）";
    public $importforObjects = "order";
    public $exportforObjects = "order";
    public $columns = "order_id";

    public function export_begin( $keys, $type, $count )
    {
        download( $type."-".date( "YmdHis" )."(".$count.").csv" );
        if ( $keys )
        {
            $this->export_rows( array(
                $keys
            ) );
        }
    }

    public function export_rows( $rows )
    {
        foreach ( $rows as $row )
        {
            echo $this->charset->utf2local( "\"".implode( "\",\"", $this->_escape( $row ) )."\"", "zh" )."\r\n";
        }
        flush( );
    }

    public function export_finish( )
    {
    }

    public function _escape( $arr )
    {
        foreach ( $arr as $k => $v )
        {
            $arr[$k] = str_replace( "\r", "\\r", str_replace( "\n", "\\n", str_replace( "\"", "\"\"", $v ) ) );
        }
        return $arr;
    }

    public function import_row( &$handle )
    {
        $data = fgetcsv( $handle, 100000, "," );
        foreach ( $data as $k => $v )
        {
            $data[$k] = $this->charset->local2utf( $v, "zh" );
        }
        return $data;
    }

    public function import_rows( &$handle )
    {
        return FALSE;
    }

}

?>
