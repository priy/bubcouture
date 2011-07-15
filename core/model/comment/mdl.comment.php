<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "shopObject.php" );
class mdl_comment extends shopobject
{

    function getfieldbyid( $id, $aFeild )
    {
        $sqlString = "SELECT ".implode( ",", $aFeild )." FROM sdb_comments WHERE  disabled='false' and comment_id = ".intval( $id );
        return $this->db->selectrow( $sqlString );
    }

    function getcommentbyid( $comment_id )
    {
        $aRet = $this->db->selectrow( "SELECT c.*, g.name AS goods_name FROM sdb_comments c\n                    LEFT JOIN sdb_goods g ON c.goods_id = g.goods_id\n                    WHERE comment_id = ".intval( $comment_id ) );
        return $aRet;
    }

    function gettopcomment( $limit )
    {
        return $this->db->select( "SELECT aGoods.name,aComment.comment_id,aComment.author,aComment.goods_id,aComment.comment,aComment.time FROM sdb_comments as aComment\n        left join sdb_goods as aGoods on aGoods.goods_id=aComment.goods_id\n        WHERE aComment.for_comment_id is Null and aComment.display=\"true\"  and aComment.object_type = \"discuss\"  and aComment.disabled = \"false\" ORDER BY aComment.comment_id desc limit 0,".intval( $limit ) );
    }

    function getcommentreply( $comment_id )
    {
        return $this->db->select( "SELECT * FROM sdb_comments WHERE for_comment_id = ".intval( $comment_id )." and disabled=\"false\" ORDER BY time" );
    }

    function toremove( $comment_id )
    {
        $row = $this->getcommentbyid( $comment_id );
        $this->db->exec( "DELETE FROM sdb_comments WHERE comment_id = ".intval( $comment_id )." OR for_comment_id = ".intval( $comment_id ) );
        $this->modelName = "member/account";
        $data['member_id'] = $row['author_id'];
        if ( $row['object_type'] == "discuss" )
        {
            $this->fireevent( "discuzz_del", $data, $row['author_id'] );
        }
        if ( $row['object_type'] == "ask" )
        {
            $this->fireevent( "advisory_del", $data, $row['author_id'] );
        }
        return true;
    }

    function todisplay( $comment_id, $status )
    {
        $this->db->exec( "UPDATE sdb_comments SET display = ".$this->db->quote( $status )." WHERE comment_id = ".intval( $comment_id ) );
        return true;
    }

    function toreply( $data )
    {
        $this->toinsert( $data );
        if ( $this->system->getconf( "comment.display.".$data['object_type'] ) == "reply" )
        {
            $aUpdate['display'] = "true";
        }
        $aUpdate['comment_id'] = $data['for_comment_id'];
        $aUpdate['lastreply'] = $data['time'];
        $aUpdate['reply_name'] = $data['author'];
        $aUpdate['mem_read_status'] = "false";
        $this->toupdate( $aUpdate );
        $this->db->exec( "UPDATE sdb_comments SET adm_read_status='false' WHERE comment_id = ".$data['for_comment_id'] );
        $objGoods =& $this->system->loadmodel( "trading/goods" );
        $objGoods->updaterank( $data['goods_id'], "comments_count", 1 );
        $status =& $this->system->loadmodel( "system/status" );
        $status->count_gdiscuss( );
        $status->count_gask( );
        $aTemp = $this->db->selectrow( "SELECT * FROM sdb_comments WHERE comment_id=".$data['for_comment_id'] );
        $data['member_id'] = $aTemp['author_id'];
        if ( $data['object_type'] == "discuss" )
        {
            $type = "discuzz_check";
        }
        else
        {
            $type = "advisory_replay";
        }
        $this->modelName = "member/account";
        $this->fireevent( $type, $data, $data['member_id'] );
        return true;
    }

    function tocomment( $data, $item, &$message )
    {
        $this->toinsert( $data );
        if ( $this->system->getconf( "comment.display.".$item ) == "soon" )
        {
            $message = $this->system->getconf( "comment.submit_display_notice.".$item );
        }
        else
        {
            $message = $this->system->getconf( "comment.submit_hidden_notice.".$item );
        }
        $objGoods =& $this->system->loadmodel( "trading/goods" );
        $objGoods->updaterank( $data['goods_id'], "comments_count", 1 );
        $status =& $this->system->loadmodel( "system/status" );
        $status->count_gdiscuss( );
        $status->count_gask( );
        $data['member_id'] = substr( $_COOKIE['MEMBER'], 0, strpos( $_COOKIE['MEMBER'], "-" ) );
        if ( $item == "ask" )
        {
            $type = "advisory_new";
        }
        else if ( $item == "discuss" )
        {
            $type = "discuzz_new";
        }
        $this->modelName = "member/account";
        $this->fireevent( $type, $data, $data['member_id'] );
        return true;
    }

    function toinsert( &$data )
    {
        $data['title'] = $data['title'];
        $data['comment'] = safehtml( $data['comment'] );
        $rs = $this->db->query( "SELECT * FROM sdb_comments WHERE 0=1" );
        $sql = $this->db->getinsertsql( $rs, $data );
        return $this->db->exec( $sql );
    }

    function toupdate( &$data )
    {
        if ( !empty( $data['comment'] ) )
        {
            $data['comment'] = safehtml( $data['comment'] );
        }
        $rs = $this->db->query( "SELECT * FROM sdb_comments WHERE comment_id=".intval( $data['comment_id'] ) );
        $sql = $this->db->getupdatesql( $rs, $data );
        return !$sql || $this->db->exec( $sql );
    }

    function setreaded( $comment_id )
    {
        if ( is_array( $comment_id ) )
        {
            $rs = $this->db->query( "SELECT * FROM sdb_comments WHERE comment_id IN (".implode( ",", $comment_id ).")" );
        }
        else
        {
            $rs = $this->db->query( "SELECT * FROM sdb_comments WHERE comment_id=".intval( $comment_id ) );
        }
        $aUpdate['adm_read_status'] = "true";
        $sql = $this->db->getupdatesql( $rs, $aUpdate );
        if ( $sql )
        {
            if ( $this->db->exec( $sql ) )
            {
                $status =& $this->system->loadmodel( "system/status" );
                $status->count_gdiscuss( );
                $status->count_gask( );
                return 1;
            }
            return false;
        }
        return true;
    }

    function setindexorder( $comment_id )
    {
        $aRet = $this->getfieldbyid( $comment_id, array( "p_index" ) );
        if ( $aRet['p_index'] == 1 )
        {
            $aRet['p_index'] = 0;
        }
        else
        {
            $aRet['p_index'] = 1;
        }
        $rs = $this->db->query( "SELECT * FROM sdb_comments WHERE comment_id=".intval( $comment_id ) );
        $sql = $this->db->getupdatesql( $rs, $aRet );
        return !$sql || $this->db->exec( $sql );
    }

    function getgoodsindexcomments( $goods_id, $item = "ask" )
    {
        $sql = "SELECT * FROM sdb_comments WHERE goods_id = ".intval( $goods_id )." AND for_comment_id IS NULL AND object_type = '".$item."' and disabled='false' AND display = 'true'";
        $aRet['total'] = $this->db->count( $sql );
        $sql = "SELECT * FROM sdb_comments WHERE goods_id = ".intval( $goods_id )." AND for_comment_id IS NULL AND object_type = '".$item."' and disabled='false' AND display = 'true' ORDER BY p_index ASC, time DESC LIMIT ".$this->system->getconf( "comment.index.listnum" );
        $aRet['data'] = $this->db->select( $sql );
        return $aRet;
    }

    function getcommentsreply( $aId, $display = false )
    {
        if ( $display )
        {
            $sql = " AND display = 'true'";
        }
        return $this->db->select( "SELECT * FROM sdb_comments WHERE for_comment_id IN (".implode( ",", $aId ).")".$sql." and disabled='false' ORDER BY time" );
    }

    function getgoodscommentlist( $goods_id, $item = "ask", $page = 1 )
    {
        if ( $page < 1 )
        {
            $page = 1;
        }
        $pagenum = $this->system->getconf( "comment.list.listnum" );
        $sql = "SELECT * FROM sdb_comments\n            WHERE goods_id = ".intval( $goods_id )." AND for_comment_id IS NULL AND object_type = '".$item."' and disabled='false' AND display = 'true'";
        $aRet['total'] = $this->db->count( $sql );
        $maxPage = ceil( $aRet['total'] / $pagenum );
        if ( $maxPage < $page )
        {
            $page = $maxPage;
        }
        $start = ( $page - 1 ) * $pagenum;
        $start = $start < 0 ? 0 : $start;
        $sql = "SELECT * FROM sdb_comments\n            WHERE goods_id = ".intval( $goods_id )." AND for_comment_id IS NULL AND object_type = '".$item.( "' AND display = 'true' and disabled='false' ORDER BY time DESC LIMIT ".$start."," ).$pagenum;
        $aRet['page'] = $maxPage;
        $aRet['data'] = $this->db->select( $sql );
        return $aRet;
    }

    function getmembercommentlist( $member_id, $page = 1 )
    {
        if ( $page < 1 )
        {
            $page = 1;
        }
        $pagenum = $this->system->getconf( "comment.list.listnum" );
        $sql = "SELECT * FROM sdb_comments\n            WHERE author_id = ".intval( $member_id )." AND for_comment_id IS NULL AND display = 'true' and disabled='false' ORDER BY time DESC";
        $aRet['total'] = $this->db->count( $sql );
        $maxPage = ceil( $aRet['total'] / $pagenum );
        if ( $maxPage < $page )
        {
            $page = $maxPage;
        }
        $start = ( $page - 1 ) * $pagenum;
        $start = $start < 0 ? 0 : $start;
        $sql = "SELECT * FROM sdb_comments\n            WHERE author_id = ".intval( $member_id ).( " AND for_comment_id IS NULL AND display = 'true' and disabled='false' ORDER BY time DESC LIMIT ".$start."," ).$pagenum;
        $aRet['page'] = $maxPage;
        $aRet['data'] = $this->db->select( $sql );
        return $aRet;
    }

    function tovalidate( $item, $gid, $memInfo, &$message )
    {
        if ( $this->system->getconf( "comment.switch.".$item ) != "on" )
        {
            $this->system->error( 404 );
            return false;
        }
        if ( $this->system->getconf( "comment.power.".$item ) != "null" && !isset( $memInfo['member_id'] ) )
        {
            $message = __( "非会员不能发表!" );
            return false;
        }
        if ( $this->system->getconf( "comment.power.".$item ) == "buyer" && $memInfo['member_id'] )
        {
            $aRet = $this->db->selectrow( "SELECT count(*) AS countRows FROM sdb_order_items i\n                        LEFT JOIN sdb_orders o ON i.order_id = o.order_id\n                        LEFT JOIN sdb_products p ON i.product_id = p.product_id\n                        WHERE o.member_id=".intval( $memInfo['member_id'] )." AND p.goods_id=".intval( $gid )." AND (o.pay_status>\"0\" OR o.ship_status>\"0\")" );
            if ( $aRet['countRows'] == 0 )
            {
                $message = __( "未购买过该商品不能发表!" );
                return false;
            }
        }
        return true;
    }

    function getsetting( $item )
    {
        $aOut['switch'][$item] = $this->system->getconf( "comment.switch.".$item );
        $aOut['display'][$item] = $this->system->getconf( "comment.display.".$item );
        $aOut['power'][$item] = $this->system->getconf( "comment.power.".$item );
        $aOut['null_notice'][$item] = $this->system->getconf( "comment.null_notice.".$item );
        $aOut['submit_display_notice'][$item] = $this->system->getconf( "comment.submit_display_notice.".$item );
        $aOut['submit_hidden_notice'][$item] = $this->system->getconf( "comment.submit_hidden_notice.".$item );
        $aOut['index'] = intval( $this->system->getconf( "comment.index.listnum" ) );
        $aOut['list'] = intval( $this->system->getconf( "comment.list.listnum" ) );
        $aOut['verifyCode'][$item] = $this->system->getconf( "comment.verifyCode.".$item );
        return $aOut;
    }

    function setsetting( $item, $aData )
    {
        $this->system->setconf( "comment.switch.".$item, $aData['switch'][$item] );
        $this->system->setconf( "comment.display.".$item, $aData['display'][$item] );
        $this->system->setconf( "comment.power.".$item, $aData['power'][$item] );
        $this->system->setconf( "comment.null_notice.".$item, $aData['null_notice'][$item] );
        $this->system->setconf( "comment.submit_display_notice.".$item, $aData['submit_display_notice'][$item] );
        $this->system->setconf( "comment.submit_hidden_notice.".$item, $aData['submit_hidden_notice'][$item] );
        $this->system->setconf( "comment.index.listnum", $aData['indexnum'] );
        $this->system->setconf( "comment.list.listnum", $aData['listnum'] );
        $this->system->setconf( "comment.verifyCode.".$item, $aData['verifyCode'][$item] );
    }

    function gettotalnum( $nMId, $item = "" )
    {
        if ( $item )
        {
            $sql = " AND object_type='".$item."'";
        }
        $aRow = $this->db->selectrow( "SELECT count(*) AS num FROM sdb_comments WHERE  disabled=\"false\" and author_id=".$nMId.$sql );
        return $aRow['num'];
    }

    function getcommlistbymemid( $nMId, $item )
    {
        if ( $item )
        {
            $sql = " AND object_type='".$item."'";
        }
        return $this->db->select( "SELECT comment_id,author,contact,title,comment,time FROM sdb_comments WHERE author_id=".$nMId.$sql." ORDER BY time DESC" );
    }

}

?>
