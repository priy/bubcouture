<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class io_taobaoordercsv
{

    public $name = "csv-订单列表（兼容淘宝格式）";
    public $importforObjects = "order";
    public $exportforObjects = "order";
    public $columns = "order_id,member_id,member_id as shop_cat,final_amount,cost_freight,score_g as tpayscore,total_amount,total_amount,score_g,payed,score_g as tscore,pay_status,memo,ship_name,ship_addr,ship_zip,shipping_id,ship_tel,ship_mobile,createtime,acttime,tostr,tostr as tsort,order_id as delivery_id,shipping,mark_text";

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
            echo $this->charset->utf2local( "\"".implode( "\",\"", $this->_escape( removebom( $row ) ) )."\"", "zh" )."\r\n";
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
