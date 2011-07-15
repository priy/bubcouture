<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "shopObject.php" );
class mdl_coupon extends shopobject
{

    var $idColumn = "cpns_id";
    var $textColumn = "cpns_name";
    var $defaultCols = "cpns_name,cpns_prefix,pmt_time_begin,pmt_time_end,cpns_id_c,cpns_type,cpns_status,cpns_gen_quantity,cpns_point";
    var $adminCtl = "sale/coupon";
    var $defaultOrder = array
    (
        0 => "cpns_id",
        1 => "desc"
    );
    var $tableName = "sdb_coupons";

    function getcolumns( )
    {
        $ret = array(
            "_cmd" => array(
                "label" => __( "操作" ),
                "width" => 120,
                "html" => "sale/coupon/command.html"
            ),
            "pmt_time_begin" => array(
                "label" => __( "开始时间" ),
                "width" => 75,
                "type" => "date"
            ),
            "pmt_time_end" => array(
                "label" => __( "结束时间" ),
                "width" => 75,
                "type" => "date"
            )
        );
        return array_merge( $ret, shopobject::getcolumns( ) );
    }

    function getlist( $cols, $filter, $start = 0, $limit = 20, $orderType = null )
    {
        $sql = "select ".$cols.",cpns_type=\"1\" as download_able,p.pmt_id from sdb_coupons\n            left join sdb_promotion as p on sdb_coupons.pmt_id=p.pmt_id\n            where ".$this->_filter( $filter );
        if ( $orderType )
        {
            $sql .= " order by ".implode( $orderType, " " );
        }
        return $this->db->selectlimit( $sql, $limit, $start );
    }

    function count( $filter = null )
    {
        $sql = "select count(*) as _count from sdb_coupons\n            left join sdb_promotion as p on sdb_coupons.pmt_id=p.pmt_id\n            where ".$this->_filter( $filter );
        $row = $this->db->select( $sql );
        return intval( $row[0]['_count'] );
    }

    function modifier_download( &$rows, $options = array( ) )
    {
        foreach ( $rows as $i => $key )
        {
            $aTmp = explode( "-", $key );
            $id = $aTmp[0];
            $type = $aTmp[1];
            if ( $type == 1 )
            {
                $rows[$i] = __( "<span onclick=\"var i=parseInt(prompt('请输入需要下载优惠券的数量：',50));if(i)window.open('index.php?ctl=sale/coupon&act=download&p[0]=" ).( boolean )$id.__( "&p[1]='+i,'download')\" class=\"lnk\">下载</span>" );
            }
            else
            {
                $rows[$i] = "";
            }
        }
    }

    function _filter( $filter )
    {
        $where = array( 1 );
        if ( $filter['cpns_name'] )
        {
            $where[] = "cpns_name like'%".$filter['cpns_name']."%'";
        }
        if ( is_array( $filter['cpns_id'] ) )
        {
            foreach ( $filter['cpns_id'] as $cpns_id )
            {
                if ( $cpns_id != "_ANY_" )
                {
                    $coupons[] = "sdb_coupons.cpns_id=".intval( $cpns_id );
                }
            }
            if ( 0 < count( $coupons ) )
            {
                $where[] = "(".implode( $coupons, " or " ).")";
            }
            unset( $filter->'cpns_id' );
        }
        if ( !empty( $filter['cpns_type'] ) || is_string( $filter['cpns_type'] ) )
        {
            $filter['cpns_type'] = explode( ",", $filter['cpns_type'] );
        }
        if ( is_array( $filter['cpns_type'] ) )
        {
            foreach ( $filter['cpns_type'] as $type )
            {
                if ( $type != "_ANY_" )
                {
                    $cpns_type[] = "sdb_coupons.cpns_type='".intval( $type )."'";
                }
            }
            if ( 0 < count( $cpns_type ) )
            {
                $where[] = "(".implode( $cpns_type, " or " ).")";
            }
            unset( $filter->'cpns_type' );
        }
        if ( isset( $filter['ifvalid'] ) && $filter['ifvalid'] == 1 )
        {
            $curTime = time( );
            $where[] = "cpns_status='1' and pmt_time_begin <= ".$curTime." and pmt_time_end >".$curTime;
        }
        return shopobject::_filter( $filter )." and ".implode( $where, " and " );
    }

    function checkprefix( $prefix )
    {
        if ( $this->db->select( "SELECT cpns_id from sdb_coupons WHERE cpns_prefix=\"".$prefix."\" limit 1" ) )
        {
            return true;
        }
        return false;
    }

    function getcouponbyids( $aCoupon )
    {
        if ( is_array( $aCoupon ) && !empty( $aCoupon ) )
        {
            $sSql = "SELECT * FROM sdb_coupons WHERE cpns_id in (".implode( ",", $aCoupon ).")";
            $aTemp = $this->db->select( $sSql );
            return $aTemp;
        }
        return false;
    }

    function getusercouponarr( )
    {
        return $this->db->select( "SELECT cpns_id,cpns_name FROM sdb_coupons WHERE cpns_type='1' and cpns_point is null ORDER BY cpns_id desc" );
    }

    function exchange( $userId, $cpnsId )
    {
        $sSql = "select cpns_point from sdb_coupons where cpns_status='1' and cpns_type='1' and cpns_point is not null and cpns_id=".intval( $cpnsId );
        if ( $aCoupon = $this->db->selectrow( $sSql ) )
        {
            $nPoint = $aCoupon['cpns_point'];
            $oCoupon =& $this->system->loadmodel( "trading/coupon" );
            $oMemberPoint =& $this->system->loadmodel( "trading/memberPoint" );
            if ( $oMemberPoint->chgpoint( $userId, 0 - abs( $nPoint ), "exchange_coupon" ) )
            {
                return $oCoupon->generatecoupon( $cpnsId, $userId, 1 );
            }
            return false;
        }
        return false;
    }

    function getmembercoupon( $userId, $nPage )
    {
        $aData = $this->db->selectpager( "SELECT * FROM sdb_member_coupon as mc\n                                            left join sdb_coupons as c on c.cpns_id=mc.cpns_id\n                                            left join sdb_promotion as p on c.pmt_id=p.pmt_id\n                                            WHERE member_id=".$userId." ORDER BY mc.memc_gen_time DESC", $nPage, PERPAGE );
        return $aData;
    }

    function islevelallowuse( $pmtId, $mLvId, &$cpnspoint )
    {
        if ( $this->db->select( "select pmt_id from sdb_pmt_member_lv where member_lv_id=".intval( $mLvId )." and pmt_id=".intval( $pmtId ) ) )
        {
            $member = $this->system->loadmodel( "member/member" );
            $row = $member->getmemberbyuser( $_COOKIE['UNAME'] );
            if ( $cpnspoint <= $row['point'] )
            {
                return true;
            }
            return false;
        }
        return false;
    }

    function getcouponbyid( $cpnsId )
    {
        return $this->db->selectrow( "SELECT *,c.pmt_id as pmt_id,c.cpns_id as cpns_id FROM sdb_coupons as c\n            left join sdb_promotion as p on c.pmt_id=p.pmt_id and pmt_type='1'\n            WHERE c.cpns_id=".intval( $cpnsId ) );
    }

    function addcoupon( $aData )
    {
        switch ( $aData['cpns_type'] )
        {
        case 0 :
            $flag = "A";
            break;
        case 1 :
            $flag = "B";
            break;
        case 2 :
        }
        $aData['cpns_prefix'] = $flag.$aData['cpns_prefix'];
        if ( $aData['cpns_id'] )
        {
            $aRs = $this->db->query( "SELECT * FROM sdb_coupons WHERE cpns_id=".$aData['cpns_id'] );
            $sSql = $this->db->getupdatesql( $aRs, $aData );
            return !$sSql || $this->db->exec( $sSql );
        }
        $aData['cpns_key'] = $this->generate_key( );
        $aData['cpns_gen_quantity'] = intval( $aData['cpns_gen_quantity'] );
        $aRs = $this->db->query( "SELECT * FROM sdb_coupons WHERE 0" );
        $sSql = $this->db->getinsertsql( $aRs, $aData );
        if ( $this->db->exec( $sSql ) )
        {
            return $this->db->lastinsertid( );
        }
        return false;
    }

    function generate_key( )
    {
        $n = rand( 4, 7 );
        $str = "";
        $j = 0;
        for ( ; $j < $n; ++$j )
        {
            $str .= chr( rand( 21, 126 ) );
        }
        return $str;
    }

    function _verifycoupontype( $couponFlag )
    {
        $_allCouponType = array( "A", "B", "S" );
        return in_array( $couponFlag, $_allCouponType );
    }

    function generatecoupon( $cpnsId, $userId, $nums, $orderId = "" )
    {
        $curTime = time( );
        $sSql = "select * from sdb_coupons as c\n            left join sdb_promotion as p on c.pmt_id=p.pmt_id\n            where cpns_status='1' and cpns_type='1' and c.cpns_id=".$cpnsId." and\n            pmt_time_begin <= ".$curTime." and pmt_time_end >".$curTime;
        if ( $aCoupon = $this->db->selectrow( $sSql ) )
        {
            $i = 1;
            for ( ; $i <= $nums; ++$i )
            {
                if ( $couponCode = $this->_makecouponcode( $aCoupon['cpns_gen_quantity'] + $i, $aCoupon['cpns_prefix'], $aCoupon['cpns_key'] ) )
                {
                    $aData = array(
                        "memc_code" => $couponCode,
                        "cpns_id" => $cpnsId,
                        "member_id" => $userId,
                        "memc_gen_orderid" => $orderId,
                        "memc_gen_time" => time( )
                    );
                    $rRs = $this->db->query( "SELECT * FROM sdb_member_coupon WHERE 0=1" );
                    $sSql = $this->db->getinsertsql( $rRs, $aData );
                    $this->db->exec( $sSql );
                    $aData = array(
                        "cpns_gen_quantity" => $aCoupon['cpns_gen_quantity'] + $i
                    );
                    $rRs = $this->db->query( "SELECT * FROM sdb_coupons WHERE cpns_id=".intval( $cpnsId ) );
                    $sSql = $this->db->getupdatesql( $rRs, $aData );
                    if ( !$sSql )
                    {
                        continue;
                    }
                    $this->db->exec( $sSql );
                }
                else
                {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    function downloadcoupon( $cpnsId, $nums )
    {
        $curTime = time( );
        $aRes = array( );
        $sSql = "select * from sdb_coupons as c\n            left join sdb_promotion as p on c.pmt_id=p.pmt_id\n            where cpns_status='1' and cpns_type='1' and c.cpns_id=".$cpnsId." and\n            pmt_time_begin <= ".$curTime." and pmt_time_end >".$curTime;
        if ( $aCoupon = $this->db->selectrow( $sSql ) )
        {
            $i = 1;
            for ( ; $i <= $nums; ++$i )
            {
                if ( $couponCode = $this->_makecouponcode( $aCoupon['cpns_gen_quantity'] + $i, $aCoupon['cpns_prefix'], $aCoupon['cpns_key'] ) )
                {
                    $aRes[] = array(
                        $couponCode
                    );
                }
                else
                {
                    return false;
                }
            }
            $aData = array(
                "cpns_gen_quantity" => $aCoupon['cpns_gen_quantity'] + $nums
            );
            $rRs = $this->db->query( "SELECT * FROM sdb_coupons WHERE cpns_id=".intval( $cpnsId ) );
            $sSql = $this->db->getupdatesql( $rRs, $aData );
            if ( $sSql )
            {
                $this->db->exec( $sSql );
            }
            return $aRes;
        }
        return false;
    }

    function getcouponbyprefix( $prefix )
    {
        return $this->db->selectrow( "select * from sdb_coupons where cpns_prefix=".$this->db->quote( trim( $prefix ) ) );
    }

    function getprefixfromcouponcode( $couponCode )
    {
        $prefix = substr( $couponCode, 0, strlen( $couponCode ) - ( $this->system->getconf( "coupon.code.count_len" ) + $this->system->getconf( "coupon.code.encrypt_len" ) ) );
        return $prefix;
    }

    function _verifycouponcode( $couponCode )
    {
        $couponFlag = $this->getflagfromcouponcode( $couponCode );
        if ( $this->_verifycoupontype( $couponFlag ) )
        {
            switch ( $couponFlag )
            {
            case "A" :
            case "S" :
                return true;
            case "B" :
                $prefix = $this->getprefixfromcouponcode( $couponCode );
                if ( $aCoupon = $this->getcouponbyprefix( $prefix ) )
                {
                    $serial_number = substr( $couponCode, 0 - $this->system->getconf( "coupon.code.count_len" ) );
                    $check_number = substr( $couponCode, strlen( $prefix ), $this->system->getconf( "coupon.code.encrypt_len" ) );
                    $new_check_number = strtoupper( substr( md5( $aCoupon['cpns_key'].$serial_number.$prefix ), 0, $this->system->getconf( "coupon.code.encrypt_len" ) ) );
                    if ( $check_number == $new_check_number )
                    {
                        return true;
                    }
                    return false;
                }
                return false;
            }
            else
            {
                return false;
            }
        }
    }

    function _makecouponcode( $iNo, $prefix, $key )
    {
        if ( strlen( strval( $iNo ) ) <= $this->system->getconf( "coupon.code.count_len" ) )
        {
            $iNo = str_pad( $this->dec2b36( $iNo ), $this->system->getconf( "coupon.code.count_len" ), "0", STR_PAD_LEFT );
            $checkCode = md5( $key.$iNo.$prefix );
            $checkCode = strtoupper( substr( $checkCode, 0, $this->system->getconf( "coupon.code.encrypt_len" ) ) );
            $memberCoupon = $prefix.$checkCode.$iNo;
            return $memberCoupon;
        }
        return false;
    }

    function dec2b36( $int )
    {
        $b36 = array( 0 => "0", 1 => "1", 2 => "2", 3 => "3", 4 => "4", 5 => "5", 6 => "6", 7 => "7", 8 => "8", 9 => "9", 10 => "A", 11 => "B", 12 => "C", 13 => "D", 14 => "E", 15 => "F", 16 => "G", 17 => "H", 18 => "I", 19 => "J", 20 => "K", 21 => "L", 22 => "M", 23 => "N", 24 => "O", 25 => "P", 26 => "Q", 27 => "R", 28 => "S", 29 => "T", 30 => "U", 31 => "V", 32 => "W", 33 => "X", 34 => "Y", 35 => "Z" );
        $retstr = "";
        if ( 0 < $int )
        {
            while ( 0 < $int )
            {
                $retstr = $b36[$int % 36].$retstr;
                $int = floor( $int / 36 );
            }
        }
        else
        {
            $retstr = "0";
        }
        return $retstr;
    }

    function getflagfromcouponcode( $couponCode )
    {
        return substr( $couponCode, 0, 1 );
    }

    function applymembercoupon( $cpnsId, $couponCode, $orderId, $userId )
    {
        if ( !$userId )
        {
            return false;
        }
        $couponFlag = $this->getflagfromcouponcode( $couponCode );
        if ( !$this->_verifycouponcode( $couponCode ) )
        {
            return false;
        }
        switch ( $couponFlag )
        {
        case "A" :
        case "B" :
            $aMeberCoupon = $this->db->selectrow( "select *  from sdb_member_coupon where memc_code='".$couponCode."'" );
            if ( $aMeberCoupon )
            {
                if ( $aMeberCoupon['memc_enabled'] == "true" && $aMeberCoupon['memc_used_times'] < $this->system->getconf( "coupon.mc.use_times" ) )
                {
                    $aRs = $this->db->query( "SELECT * FROM sdb_member_coupon where memc_code='".$couponCode."'" );
                    $aData['memc_used_times'] = $aMeberCoupon['memc_used_times'] + 1;
                    $sSql = $this->db->getupdatesql( $aRs, $aData );
                    return !$sSql || $this->db->exec( $sSql );
                }
                trigger_error( __( "此优惠券已被取消/使用次数已经用满" ), E_USER_NOTICE );
                return false;
            }
            $aData['memc_code'] = $couponCode;
            $aData['cpns_id'] = $cpnsId;
            $aData['member_id'] = $userId;
            $aData['memc_used_times'] = 1;
            $aData['memc_gen_time'] = time( );
            $aRs = $this->db->query( "SELECT * FROM sdb_member_coupon WHERE 0" );
            $sSql = $this->db->getinsertsql( $aRs, $aData );
            return !$sSql || $this->db->exec( $sSql );
        case "S" :
        }
    }

    function usemembercoupon( $couponCode, $mlvid, $goods_ids, $brand_ids, $cat_ids, $orderPrice = 0 )
    {
        $couponFlag = $this->getflagfromcouponcode( $couponCode );
        if ( !$this->_verifycouponcode( $couponCode ) )
        {
            trigger_error( __( "优惠券无效" ), E_USER_WARNING );
            return false;
        }
        $curTime = time( );
        switch ( $couponFlag )
        {
        case "A" :
            $prefix = $couponCode;
            $cpnsType = 0;
            break;
        case "B" :
            if ( !$mlvid )
            {
                return false;
            }
            $prefix = $this->getprefixfromcouponcode( $couponCode );
            $cpnsType = 1;
            break;
        case "S" :
            if ( !$mlvid )
            {
                return false;
            }
            $cpnsType = 2;
        }
        if ( constant( "DB_OLDVERSION" ) )
        {
            $sSql = "select * from sdb_coupons as c\n                left join sdb_promotion as p on c.pmt_id=p.pmt_id\n                where cpns_prefix='".$prefix."' and cpns_status='1' and cpns_type='".$cpnsType."' and\n                pmt_time_begin <= ".$curTime." and pmt_time_end >".$curTime;
            $rows = $this->db->select( $sSql );
            $m = array( );
            foreach ( $rows as $k => $r )
            {
                $m[$r['pmt_id']] = $r[$k];
            }
            $sn = array( );
            $aCoupon = false;
            foreach ( $this->db->select( "select member_lv_id from sdb_pmt_member_lv where pmt_id in (".implode( ",", array_keys( $m ) ).")" ) as $sr )
            {
                if ( !isset( $m[$sr['pmt_id']] ) )
                {
                    continue;
                }
                $aCoupon = $m[$sr['pmt_id']];
                break;
            }
        }
        else
        {
            $sSql = "select * from sdb_coupons as c\n                left join sdb_promotion as p on c.pmt_id=p.pmt_id\n                where cpns_prefix='".$prefix."' and cpns_status='1' and cpns_type='".$cpnsType."' and\n                (".intval( $mlvid )." in (select member_lv_id from sdb_pmt_member_lv where pmt_id=p.pmt_id)) and pmt_time_begin <= ".$curTime." and pmt_time_end >".$curTime;
            $aCoupon = $this->db->selectrow( $sSql );
        }
        if ( $aCoupon )
        {
            switch ( $couponFlag )
            {
            case "B" :
                if ( !( $aTmp = $this->db->selectrow( "select memc_used_times,memc_enabled from sdb_member_coupon where memc_code='".$couponCode."'" ) ) && !( $this->system->getconf( "coupon.mc.use_times" ) <= $aTmp['memc_used_times'] ) || !( $aTmp['memc_enabled'] == "false" ) )
                {
                    break;
                }
                $this->seterror( "30000" );
                trigger_error( __( "优惠券无效" ), E_USER_WARNING );
                return false;
            case "A" :
            case "S" :
            }
            $oPromotion =& $this->system->loadmodel( "trading/promotion" );
            if ( $aCoupon['pmt_basic_type'] == "goods" )
            {
                if ( $aPmtGoods = $oPromotion->filter_goods( $aCoupon['pmt_id'], $aCoupon['pmt_bond_type'], $goods_ids, $brand_ids, $cat_ids ) )
                {
                    $aResult[$couponCode]['type'] = "goods";
                    $aResult[$couponCode]['pmt_id'] = $aCoupon['pmt_id'];
                    $aResult[$couponCode]['goods_ids'] = $aPmtGoods;
                    $aResult[$couponCode]['cpns_id'] = $aCoupon['cpns_id'];
                    $aResult[$couponCode]['cpns_type'] = $aCoupon['cpns_type'];
                    return $aResult;
                }
                trigger_error( __( "没有优惠券所绑定的商品" ), E_USER_WARNING );
                return false;
            }
            if ( $aCoupon['pmt_basic_type'] == "order" )
            {
                if ( $aCoupon['order_money_from'] <= $orderPrice && $orderPrice < $aCoupon['order_money_to'] )
                {
                    $aResult[$couponCode]['type'] = "order";
                    $aResult[$couponCode]['pmt_id'] = $aCoupon['pmt_id'];
                    $aResult[$couponCode]['cpns_id'] = $aCoupon['cpns_id'];
                    $aResult[$couponCode]['cpns_type'] = $aCoupon['cpns_type'];
                    return $aResult;
                }
                trigger_error( __( "订单金额不符合优惠券限制金额" ), E_USER_WARNING );
                return false;
            }
            trigger_error( __( "暂无此优惠类型" ), E_USER_WARNING );
            return false;
        }
        trigger_error( __( "优惠券无效" ), E_USER_WARNING );
        return false;
    }

    function delete( $filter )
    {
        $arrId = $filter['cpns_id'];
        if ( $arrId )
        {
            $sSql = "select pmt_id from sdb_coupons where cpns_id in (".implode( $arrId, "," ).")";
            $aData = $this->db->select( "select pmt_id from sdb_coupons where cpns_id in (".implode( $arrId, "," ).")" );
            if ( $aData )
            {
                $aPmtIds = array_item( $aData, "pmt_id" );
                $this->db->exec( "delete from sdb_promotion where pmt_id in (".implode( $aPmtIds, "," ).")" );
            }
            $sSql = "delete from sdb_coupons where  cpns_id in (".implode( $arrId, "," ).")";
            if ( $this->db->exec( $sSql ) )
            {
                $related_tables = array( "sdb_member_coupon", "sdb_pmt_gen_coupon" );
                foreach ( $related_tables as $table )
                {
                    $this->db->exec( "delete from ".$table." where  cpns_id in (".implode( $arrId, "," ).")" );
                }
                return true;
            }
            $msg = __( "数据删除失败！" );
            return false;
        }
        $msg = "no select";
        return false;
    }

}

?>
