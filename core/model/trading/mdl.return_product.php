<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "shopObject.php" );
class mdl_return_product extends shopobject
{

    var $idColumn = "return_id";
    var $textColumn = "title";
    var $tableName = "sdb_return_product";
    var $defaultCols = "order_id,title,member_id,status,add_time";
    var $defaultOrder = array
    (
        0 => "return_id",
        1 => " DESC",
        2 => ",add_time",
        3 => " DESC"
    );

    function searchoptions( )
    {
        return array(
            "order_id" => __( "订单号" ),
            "member_name" => __( "申请人" )
        );
    }

    function _filter( $filter )
    {
        $where = array( 1 );
        if ( $filter['no_handle'] )
        {
            $where[] = " (status!=4 and  status!=5) ";
        }
        if ( isset( $filter['member_name'] ) && $filter['member_name'] !== "" )
        {
            $aId = array( 0 );
            foreach ( $this->db->select( "SELECT member_id FROM sdb_members WHERE uname = '".addslashes( $filter['member_name'] )."'" ) as $rows )
            {
                $aId[] = $rows['member_id'];
            }
            $where[] = "member_id IN (".implode( ",", $aId ).")";
            unset( $filter->'member_name' );
        }
        return shopobject::_filter( $filter )." and ".implode( $where, " AND " );
    }

    function get_status( $status )
    {
        switch ( $status )
        {
        case 1 :
            $status = __( "申请中" );
            return $status;
        case 2 :
            $status = __( "审核中" );
            return $status;
        case 3 :
            $status = __( "接受申请" );
            return $status;
        case 4 :
            $status = __( "完成" );
            return $status;
        case 5 :
            $status = __( "拒绝" );
        }
        return $status;
    }

    function count_return_product( )
    {
        $result = $this->db->selectrow( "SELECT count(*) as counts from sdb_return_product where disabled=\"false\" and status!=\"4\" and status!=\"5\" " );
        return $result['counts'];
    }

    function load( $return_id )
    {
        if ( $row = $this->db->selectrow( "SELECT * from sdb_return_product where return_id =".$return_id ) )
        {
            $this->_info['return_id'] = $row['return_id'];
            $this->_info['order_id'] = $row['order_id'];
            $this->_info['member_id'] = $row['member_id'];
            $this->_info['order_id'] = $row['order_id'];
            $this->_info['title'] = $row['title'];
            $this->_info['status'] = $this->get_status( $row['status'] );
            $this->_info['status_int'] = $row['status'];
            $this->_info['content'] = $row['content'];
            $this->_info['add_time'] = $row['add_time'];
            $this->_info['disabled'] = $row['disabled'];
            if ( $row['image_file'] )
            {
                $this->_info['image_file'] = $row['image_file'];
            }
            if ( $row['product_data'] )
            {
                $this->_info['product_data'] = unserialize( $row['product_data'] );
            }
            if ( $row['comment'] )
            {
                $this->_info['comment'] = unserialize( $row['comment'] );
            }
            return $this->_info;
        }
        return false;
    }

    function change_status( $return_id, $status )
    {
        $data = array(
            "status" => $status
        );
        $filter = array(
            "return_id" => $return_id
        );
        if ( $this->update( $data, $filter ) )
        {
            $row = $this->instance( $return_id, "member_id" );
            $this->modelName = "member/account";
            $this->fireevent( "saleservice", $row, $row['member_id'] );
            return $this->get_status( $status );
        }
    }

    function send_comment( $return_id, $comment )
    {
        $info = $this->load( $return_id );
        $old_comment = $info['comment'];
        $new_comment = array(
            array(
                "time" => time( ),
                "content" => $comment
            )
        );
        if ( is_array( $old_comment ) )
        {
            $new_comment = array_merge( $new_comment, $old_comment );
        }
        $data = array(
            "comment" => serialize( $new_comment )
        );
        $filter = array(
            "return_id" => $return_id
        );
        return $this->update( $data, $filter );
    }

    function save( $aData )
    {
        $rs = $this->db->query( "select * from sdb_return_product where 0=1" );
        $sqlString = $this->db->getinsertsql( $rs, $aData );
        if ( $this->db->exec( $sqlString ) )
        {
            $this->modelName = "member/account";
            $this->fireevent( "saleservice", $aData, $aData['member_id'] );
            return true;
        }
        return false;
    }

    function file_download( $filename )
    {
        $file = fopen( $filename, "r" );
        header( "Content-type: application/octet-stream" );
        header( "Accept-Ranges: bytes" );
        header( "Accept-Length: ".filesize( $filename ) );
        header( "Content-Disposition: attachment; filename=".basename( $filename ) );
        echo fread( $file, filesize( $filename ) );
        fclose( $file );
    }

}

?>
