<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( dirname( __FILE__ )."/mdl.comment.php" );
class mdl_discuss extends mdl_comment
{

    var $idColumn = "comment_id";
    var $textColumn = "comment_id";
    var $appendCols = "adm_read_status";
    var $adminCtl = "goods/discuss";
    var $defaultCols = "title,author,goods_id,time,adm_read_status,reply_name,lastreply,display,p_index";
    var $defaultOrder = array
    (
        0 => "comment_id",
        1 => "DESC"
    );
    var $tableName = "sdb_comments";

    function _filter( $filter )
    {
        $filter['object_type'] = "discuss";
        $where[] = "for_comment_id IS NULL";
        if ( empty( $filter['author'] ) )
        {
            unset( $filter->'author' );
        }
        if ( isset( $filter['goods_name'] ) && $filter['goods_name'] !== "" )
        {
            $aId = array( 0 );
            foreach ( $this->db->select( "SELECT goods_id FROM sdb_goods WHERE name LIKE '%".addslashes( $filter['goods_name'] )."%'" ) as $rows )
            {
                $aId[] = $rows['goods_id'];
            }
            $where[] = "goods_id IN (".implode( ",", $aId ).")";
            unset( $filter->'goods_name' );
        }
        else if ( empty( $filter['goods_name'] ) )
        {
            unset( $filter->'goods_name' );
        }
        $where = implode( " AND ", $where );
        return mdl_comment::_filter( $filter )." AND ".$where;
    }

    function is_highlight( $row )
    {
        if ( $row['adm_read_status'] == "false" )
        {
            return 1;
        }
        return 0;
    }

    function searchoptions( )
    {
        $arr = mdl_comment::searchoptions( );
        $arr['author'] = __( "评论人" );
        return array_merge( $arr, array(
            "goods_name" => __( "商品名称" )
        ) );
    }

    function getcolumns( )
    {
        $now = mdl_comment::getcolumns( );
        $now['author']['label'] = __( "评论人" );
        $now['time']['label'] = __( "评论时间" );
        $now['ip']['label'] = __( "评论人IP" );
        return $now;
    }

}

?>
