<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "shopObject.php" );
class mdl_gift extends shopobject
{

    var $idColumn = "gift_id";
    var $textColumn = "name";
    var $defaultCols = "name,giftcat_id,limit_start_time,limit_end_time,point,storage,shop_iffb,orderlist,ifrecommend,limit_num";
    var $adminCtl = "sale/gift";
    var $defaultOrder = array
    (
        0 => "orderlist",
        1 => "desc"
    );
    var $tableName = "sdb_gift";

    function getcolumns( )
    {
        $ret = array(
            "_cmd" => array(
                "label" => __( "操作" ),
                "width" => 70,
                "html" => "sale/gift/command.html"
            )
        );
        return array_merge( $ret, shopobject::getcolumns( ) );
    }

    function getfilter( $p )
    {
        $oGift =& $this->system->loadmodel( "trading/giftcat" );
        $return['giftcat_ids'] = $oGift->gettypearr( );
        return $return;
    }

    function getgiftlist( $pageStart, $pageEnd, &$count, $filter )
    {
        $curTime = time( );
        if ( $filter['gid'] )
        {
            $_filter = " and A.giftcat_id=\"".$filter['gid']."\"";
        }
        if ( $filter['ifrecommend'] )
        {
            $_filter = " and A.ifrecommend=\"1\"";
        }
        $sSql = "SELECT * FROM sdb_gift as A\n                Left Join  sdb_gift_cat as B ON A.giftcat_id=B.giftcat_id and A.shop_iffb=\"1\" and A.limit_start_time<=".$curTime." and A.limit_end_time>".$curTime."\n                where B.shop_iffb=\"1\" ".$_filter."  and A.disabled!=\"true\"  order by A.orderlist desc";
        $count = $this->db->count( $sSql );
        $sSql .= " limit ".$pageStart.",".$pageEnd;
        return $this->db->select( $sSql );
    }

    function getalllist( )
    {
        $curTime = time( );
        $sSql = "SELECT cat,name,gift_id,B.giftcat_id FROM sdb_gift_cat as B Left Join sdb_gift as A ON A.giftcat_id=B.giftcat_id and A.shop_iffb=\"1\" and A.disabled!=\"true\" and A.limit_start_time<=".$curTime." and A.limit_end_time>".$curTime." where B.shop_iffb=\"1\" and B.disabled!=\"true\" order by B.orderlist desc";
        return $this->db->select( $sSql );
    }

    function isonsale( $aGift, $mlv, $num = 1 )
    {
        if ( !isset( $mlv ) )
        {
            return false;
        }
        if ( empty( $aGift['limit_level'] ) )
        {
            return false;
        }
        $aGiftLimitLevel = explode( ",", $aGift['limit_level'] );
        if ( $mlv <= 0 )
        {
            return false;
        }
        if ( $aGift['limit_start_time'] < time( ) && time( ) < $aGift['limit_end_time'] && $num <= $aGift['storage'] - $aGift['freez'] && ( $num <= $aGift['limit_num'] || intval( $aGift['limit_num'] == 0 ) ) && in_array( $mlv, $aGiftLimitLevel ) )
        {
            return true;
        }
        return false;
    }

    function getgiftbyids( $aGift )
    {
        if ( is_array( $aGift ) && !empty( $aGift ) )
        {
            $sSql = "SELECT * FROM sdb_gift WHERE gift_id in (".implode( ",", $aGift ).")";
            $aTemp = $this->db->select( $sSql );
            return $aTemp;
        }
        return false;
    }

    function checkstock( $giftId, $chgNum = "0" )
    {
        $chgNum = abs( $chgNum );
        $aGift = $this->getstock( $giftId ) - $this->getfreezstock( $giftId );
        if ( $aGift < $chgNum )
        {
            return false;
        }
        return true;
    }

    function getstock( $giftId )
    {
        $sSql = "SELECT storage FROM sdb_gift WHERE gift_id = ".intval( $giftId );
        $result = $this->db->selectrow( $sSql );
        return $result['storage'];
    }

    function getfreezstock( $giftId )
    {
        $sSql = "SELECT freez FROM sdb_gift WHERE gift_id = ".intval( $giftId );
        $result = $this->db->selectrow( $sSql );
        return $result['freez'];
    }

    function adjuststock( $giftId, $chgNum, $isDirect = false )
    {
        $giftId = intval( $giftId );
        $rs = $this->db->selectrow( "SELECT freez FROM sdb_gift WHERE gift_id =".$giftId );
        if ( $rs['freez'] < abs( $chgNum ) )
        {
            $isDirect = true;
        }
        if ( $this->checkstock( $giftId ) )
        {
            if ( 0 < $chgNum )
            {
                $sSql = "UPDATE sdb_gift SET storage = storage + ".intval( $chgNum )." WHERE gift_id = ".$giftId;
            }
            else if ( $chgNum < 0 )
            {
                if ( $isDirect )
                {
                    $sSql = "UPDATE sdb_gift SET storage = storage-".abs( $chgNum )." WHERE gift_id = ".$giftId;
                }
                else
                {
                    $sSql = "UPDATE sdb_gift SET storage=storage-".abs( $chgNum ).",freez=freez-".abs( $chgNum )." WHERE gift_id = ".$giftId;
                }
            }
            else
            {
                return true;
            }
            if ( $this->db->exec( $sSql ) )
            {
                return true;
            }
            return false;
        }
        return false;
    }

    function freezstock( $giftId, $num )
    {
        $aData = $this->getfieldbyid( $giftId, array( "storage", "freez" ) );
        $nStorage = $aData['storage'];
        if ( $this->checkstock( $giftId, $num ) )
        {
            $sSql = "update sdb_gift set freez=freez+".abs( $num )." where gift_id=".intval( $giftId );
            return $this->db->exec( $sSql );
        }
        return false;
    }

    function unfreezstock( $giftId, $num )
    {
        if ( 0 < $num )
        {
            $rs = $this->db->selectrow( "SELECT freez FROM sdb_gift WHERE gift_id =".$giftId );
            if ( abs( $num ) <= $rs['freez'] )
            {
                $sSql = "update sdb_gift set freez = freez-".abs( $num )." where gift_id=".intval( $giftId );
                $this->db->exec( $sSql );
            }
            else
            {
                $sSql = "update sdb_gift set freez = 0 where gift_id=".intval( $giftId );
                $this->db->exec( $sSql );
            }
        }
    }

    function toconsign( $orderId, $giftId, $sendNum )
    {
        $sendNum = intval( $sendNum );
        if ( $this->adjuststock( $giftId, 0 - $sendNum ) )
        {
            $this->db->exec( "UPDATE sdb_gift_items set sendnum=sendnum+".intval( $sendNum )." WHERE order_id='".$this->db->quote( $orderId )."' and gift_id=".intval( $giftId ) );
            return true;
        }
        return false;
    }

    function tocancel( $orderId, $giftId )
    {
        $aItem = $this->db->selectrow( "SELECT nums FROM sdb_gift_items WHERE order_id='".$orderId."' and gift_id=".intval( $giftId ) );
        $this->unfreezstock( $giftId, $aItem['nums'] );
    }

    function getorderitemslist( $orderId, $aGiftId )
    {
        if ( is_array( $aGiftId ) && $aGiftId )
        {
            $sqlWhere = " AND gift_id in (".implode( ",", $aGiftId ).")";
        }
        $aRet = $this->db->select( "SELECT * FROM sdb_gift_items WHERE order_id = '".$orderId."'".$sqlWhere );
        return $aRet;
    }

    function gettypelist( $catName = "", $isFront = false )
    {
        $sTemp = "";
        if ( isset( $catName ) && $catName != "" )
        {
            $sTemp .= " and gc.cat like\"%".$catName."%\" ";
        }
        if ( $isFront )
        {
            $sTemp .= " and shop_iffb='1'";
        }
        $sSql = "SELECT * FROM sdb_gift_cat as gc where 1.".$sTemp." order by orderlist desc";
        return $this->db->select( $sSql );
    }

    function gettypebyid( $catid )
    {
        $sql = "SELECT * FROM sdb_gift_cat WHERE giftcat_id=".$catid;
        return $this->db->selectrow( $sql );
    }

    function addtype( $aData )
    {
        if ( $aData['giftcat_id'] )
        {
            $aRs = $this->db->query( "SELECT * FROM sdb_gift_cat WHERE giftcat_id=".$aData['giftcat_id'] );
            $sSql = $this->db->getupdatesql( $aRs, $aData );
            return !$sSql || $this->db->exec( $sSql );
        }
        $aRs = $this->db->query( "SELECT * FROM sdb_gift_cat WHERE 0" );
        $sSql = $this->db->getinsertsql( $aRs, $aData );
        if ( $this->db->exec( $sSql ) )
        {
            return $this->db->lastinsertid( );
        }
        return false;
    }

    function gettypearr( )
    {
        return $this->db->select( "SELECT giftcat_id,cat FROM sdb_gift_cat WHERE disabled = 'false' ORDER BY orderlist desc" );
    }

    function getgiftbyid( $nGift )
    {
        $sSql = "SELECT g.*,gc.cat FROM sdb_gift as g\n                        left join sdb_gift_cat as gc on g.giftcat_id=gc.giftcat_id\n                        WHERE g.gift_id=".intval( $nGift );
        if ( $aTemp = $this->db->selectrow( $sSql ) )
        {
            return $aTemp;
        }
        return false;
    }

    function getfieldbyid( $giftId, $aField = array
    (
        0 => "*"
    ) )
    {
        return $this->db->selectrow( "SELECT ".implode( ",", $aField ).( " FROM sdb_gift WHERE gift_id='".$giftId."'" ) );
    }

    function getinitorder( )
    {
        $aTemp = $this->db->selectrow( "select max(orderlist) as orderlist from sdb_gift" );
        return $aTemp['orderlist'] + 1;
    }

    function savegift( $aData )
    {
        $oTemplate = $this->system->loadmodel( "system/template" );
        if ( !$aData['small_pic'] )
        {
            unset( $aData->'small_pic' );
        }
        if ( !$aData['thumbnail_pic'] )
        {
            unset( $aData->'thumbnail_pic' );
        }
        $aData['limit_level'] = implode( ",", $aData['limit_level'] );
        if ( isset( $aData['limit_start_time'] ) )
        {
            $aData['limit_start_time'] = intval( $aData['limit_start_time'] );
        }
        if ( isset( $aData['limit_end_time'] ) )
        {
            $aData['limit_end_time'] = intval( $aData['limit_end_time'] );
        }
        $storager =& $this->system->loadmodel( "system/storager" );
        if ( $_FILES['thumbnail_pic']['name'] )
        {
            $aData['thumbnail_pic'] = $storager->save_upload( $_FILES['thumbnail_pic'], "gift", array(
                $aData['gift_id'],
                "thumbnail"
            ) );
        }
        if ( $_FILES['small_pic']['name'] )
        {
            $aData['small_pic'] = $storager->save_upload( $_FILES['small_pic'], "gift", array(
                $aData['gift_id'],
                "small"
            ) );
        }
        if ( $_FILES['big_pic']['name'] )
        {
            $aData['big_pic'] = $storager->save_upload( $_FILES['big_pic'], "gift", array(
                $aData['gift_id'],
                "big"
            ) );
        }
        if ( $aData['gift_id'] )
        {
            $aData['update_time'] = time( );
            $aRs = $this->db->query( "SELECT * FROM sdb_gift WHERE gift_id=".$aData['gift_id'] );
            $sSql = $this->db->getupdatesql( $aRs, $aData );
            $oTemplate->update_template( "gift", $aData['gift_id'], $aData['gift_template'], "gift" );
            return !$sSql || $this->db->exec( $sSql );
        }
        $aData['insert_time'] = time( );
        $aRs = $this->db->query( "SELECT * FROM sdb_gift WHERE 0" );
        $sSql = $this->db->getinsertsql( $aRs, $aData );
        if ( $this->db->exec( $sSql ) )
        {
            $gift_id = $this->db->lastinsertid( );
            $oTemplate->update_template( "gift", $gift_id, $aData['gift_template'], "gift" );
            return $gift_id;
        }
        return false;
    }

}

?>
