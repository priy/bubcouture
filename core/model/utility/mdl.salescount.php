<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "shopObject.php" );
class mdl_salescount extends shopobject
{

    function count_all( $dateFrom, $dateTo, $item, $search, $order )
    {
        $ordertype = array( "1" => "saleTimes", "2" => "salePrice" );
        $method = array( "1" => "ASC", "2" => "DESC" );
        if ( is_array( $order ) )
        {
            $order = " order by ".$ordertype[$order['order']]." ".$method[$order['method']];
        }
        else
        {
            $order = " order by ".$ordertype[1]." ".$method[2];
        }
        $where = "";
        switch ( $item )
        {
        case "order" :
            $where = " and P.order_id='".$search."'";
            break;
        case "username" :
            $where = " and M.uname='".$search."'";
        }
        $sql = "SELECT O.name,sum(O.nums) as saleTimes,sum(O.amount) as salePrice FROM sdb_payments as P\n        LEFT JOIN sdb_order_items as O ON P.order_id=O.order_id\n        LEFT JOIN sdb_orders as Q on P.order_id=Q.order_id\n        LEFT JOIN sdb_members as M on P.member_id=M.member_id\n        where P.t_end >=".$dateFrom." and P.t_end <=".$dateTo.$where." and P.disabled=\"false\" and P.status=\"succ\" Group By O.name".$order;
        return $this->db->select( $sql );
    }

    function mdl_dosearch( $date_From, $date_To = "", $dateCompare_From, $dateCompare_To, $type )
    {
        if ( $type == "day" )
        {
            $result = "";
            $dateFrom = strtotime( $date_From );
            if ( empty( $date_To ) || $date_To == "" )
            {
                $dateTo = strtotime( "+1 month", $dateFrom );
            }
            else
            {
                $dateTo = strtotime( $date_To ) + 86399;
            }
            $head = __( "<graph caption='从" ).date( __( "Y年m月d日" ), $dateFrom ).__( "至" ).date( __( "Y年m月d日" ), $dateTo )."'>";
            $dur = ( $dateTo - $dateFrom ) / 86400;
            if ( $dur <= 0 )
            {
                return "error";
            }
            $sql = "SELECT P.t_end,P.money FROM sdb_payments as P\n            where P.t_end >=".$dateFrom." and P.status=\"succ\" and P.t_end <=".$dateTo." and P.disabled=\"false\" and P.order_id is not NULL";
            $s_from = $this->db->select( $sql );
            foreach ( $s_from as $q => $m )
            {
                $f_date[floor( ( $m['t_end'] - $dateFrom ) / 86400 )][] = $m['money'];
            }
            $i = 0;
            for ( ; $i <= $dur; ++$i )
            {
                $diff = strtotime( $date_From ) + $i * 86400;
                $sum = @array_sum( $f_date[$i] ) ? array_sum( $f_date[$i] ) : 0;
                $result .= "<set name='".date( "m-d", $diff )."' value='".$sum."'/>";
            }
            if ( $dateCompare_To && $dateCompare_From )
            {
                $head = __( "<graph caption='从" ).date( __( "Y年m月d日" ), strtotime( $date_From ) ).__( "至" ).date( __( "Y年m月d日" ), strtotime( $date_To ) ).__( "对比 " ).date( __( "Y年m月d日" ), strtotime( $dateCompare_From ) ).__( " 至 " ).date( __( "Y年m月d日" ), strtotime( $dateCompare_To ) ).__( " 结果' type='compare'>" );
                $dateCompareTo = strtotime( $dateCompare_To );
                $dateCompareFrom = strtotime( $dateCompare_From );
                $dur2 = ( strtotime( $dateCompare_To ) - strtotime( $dateCompare_From ) ) / 86400;
                if ( $dur2 <= 0 )
                {
                    return "error";
                }
                $sql = "SELECT P.t_end,P.money FROM sdb_payments as P\n                where P.t_end >=".$dateCompareFrom." and P.status=\"succ\" and P.t_end <=".$dateCompareTo."and P.order_id is not NULL";
                $c_from = $this->db->select( $sql );
                foreach ( $c_from as $q => $m )
                {
                    $t_date[floor( ( $m['t_end'] - $dateCompareFrom ) / 86400 )][] = $m['money'];
                }
                $i = 0;
                for ( ; $i <= $dur2; ++$i )
                {
                    $diff = strtotime( $dateCompare_From ) + $i * 86400;
                    $sum = @array_sum( $t_date[$i] ) ? array_sum( $t_date[$i] ) : 0;
                    $result .= "<compared name='".date( "m-d", $diff )."' value='".$sum."'/>";
                }
            }
            $result = $head.$result."</graph>";
        }
        if ( $type == "month" )
        {
            $result = "";
            $head = __( "<graph caption='从" ).date( __( "Y年m月" ), strtotime( $date_From ) ).__( "至" ).date( __( "Y年m月" ), strtotime( $date_To ) )."'>";
            if ( empty( $date_From ) || empty( $date_To ) )
            {
                return "error";
            }
            $dateFrom = strtotime( $date_From );
            $dateTo = strtotime( $date_To );
            $dur = date( "m", $dateTo ) - date( "m", $dateFrom ) + ( date( "Y", $dateTo ) - date( "Y", $dateFrom ) ) * 12;
            if ( $dur <= 0 )
            {
                return "error";
            }
            $sql = "SELECT P.t_end,P.money FROM sdb_payments as P\n           where P.t_end >=".$dateFrom." and P.status=\"succ\" and P.t_end <=".$dateTo." and P.disabled=\"false\" and P.order_id is not NULL";
            $s_from = $this->db->select( $sql );
            foreach ( $s_from as $q => $m )
            {
                $f_date[date( "Y-m", $m['t_end'] )][] = $m['money'];
            }
            $i = 0;
            for ( ; $i <= $dur; ++$i )
            {
                $diff = strtotime( "+".$i." month", $dateFrom );
                $sum = @array_sum( $f_date[date( "Y-m", $diff )] ) ? array_sum( $f_date[date( "Y-m", $diff )] ) : 0;
                $result .= "<set name='".date( "y-m", $diff )."' value='".$sum."'/>";
            }
            if ( $dateCompare_To && $dateCompare_From )
            {
                $head = __( "<graph caption='从" ).date( __( "Y年m月" ), strtotime( $date_From ) ).__( "至" ).date( __( "Y年m月" ), strtotime( $date_To ) ).__( "对比 " ).date( __( "Y年m月" ), strtotime( $dateCompare_From ) ).__( " 至 " ).date( __( "Y年m月" ), strtotime( $dateCompare_To ) ).__( " 结果' type='compare'>" );
                $dateCompareTo = strtotime( $dateCompare_To );
                $dateCompareFrom = strtotime( $dateCompare_From );
                $dur2 = date( "m", $dateCompareTo ) - date( "m", $dateCompareFrom ) + ( date( "Y", $dateCompareTo ) - date( "Y", $dateCompareFrom ) ) * 12;
                if ( $dur2 <= 0 )
                {
                    return "error";
                }
                $sql = "SELECT P.t_end,P.money FROM sdb_payments as P\n                where P.t_end >=".$dateCompareFrom." and P.status=\"succ\" and P.t_end <=".$dateCompareTo."and P.order_id is not NULL";
                $c_from = $this->db->select( $sql );
                foreach ( $c_from as $q => $m )
                {
                    $t_date[date( "y-m", $m['t_end'] )][] = $m['money'];
                }
                $i = 0;
                for ( ; $i <= $dur2; ++$i )
                {
                    $diff = strtotime( "+".$i." month", $dateCompareFrom );
                    $sum = @array_sum( $t_date[date( "Y-m", $diff )] ) ? array_sum( $t_date[date( "Y-m", $diff )] ) : 0;
                    $result .= "<compared name='".date( "y-m", $diff )."' value='".$sum."'/>";
                }
            }
            $result = $head.$result."</graph>";
        }
        return $result;
    }

    function member_count( $dateFrom, $dateTo, $order )
    {
        $ordertype = array( "1" => "saleTimes", "2" => "salePrice" );
        $method = array( "1" => "ASC", "2" => "DESC" );
        if ( is_array( $order ) )
        {
            $order = " order by ".$ordertype[$order['order']]." ".$method[$order['method']];
        }
        else
        {
            $order = " order by ".$ordertype[1]." ".$method[2];
        }
        $sql = "select M.uname as username , M.name as realname,count(1) as saleTimes,sum(O.payed) as salePrice from sdb_orders as O \n        left join sdb_members as M on O.member_id=M.member_id \n        where (O.payed>0) and O.createtime >=".$dateFrom." and O.createtime <=".$dateTo." group by M.uname".$order;
        return $this->db->select( $sql );
    }

    function visit_sale_compare( $dateFrom, $dateTo, $order )
    {
        $ordertype = array( "1" => "view_count", "2" => "saleTimes" );
        $method = array( "1" => "ASC", "2" => "DESC" );
        if ( is_array( $order ) )
        {
            $order = " order by ".$ordertype[$order['order']]." ".$method[$order['method']];
        }
        else
        {
            $order = " order by ".$ordertype[1]." ".$method[2];
        }
        $sql = "SELECT G.name , G.view_count , count(O.product_id) as saleTimes  FROM sdb_payments as P\n        LEFT JOIN sdb_order_items as O ON P.order_id=O.order_id\n        LEFT JOIN sdb_products as D ON O.product_id = D.product_id\n        LEFT JOIN sdb_goods as G ON G.goods_id=D.goods_id\n        where P.t_end >=".$dateFrom." and P.t_end <=".$dateTo." and P.status=\"succ\" and P.disabled=\"false\" Group By G.goods_id".$order;
        return $this->db->select( $sql );
    }

    function average_order_sales( $dateFrom, $dateTo )
    {
        $sql = "SELECT  count(order_id) as orderTimes , Sum(P.money) as sumMoney  FROM sdb_payments as P\n        where P.t_end >=".$dateFrom." and P.t_end <=".$dateTo." and P.status=\"succ\" and P.disabled=\"false\"";
        return $this->db->selectrow( $sql );
    }

    function count_all_visite( $dateFrom, $dateTo )
    {
        $sql = "SELECT  sum(view_count) as allView FROM sdb_payments as P\n        LEFT JOIN sdb_order_items as O ON P.order_id=O.order_id\n        LEFT JOIN sdb_products as D ON O.product_id = D.product_id\n        LEFT JOIN sdb_goods as G ON G.goods_id=D.goods_id\n        where P.t_end >=".$dateFrom." and P.t_end <=".$dateTo." and P.status=\"succ\" and P.disabled=\"false\"";
        return $this->db->selectrow( $sql );
    }

    function have_order_member( $dateFrom, $dateTo )
    {
        $sql = "SELECT  COUNT(DISTINCT P.member_id) as orderMember FROM sdb_payments as P\n        where P.t_end >=".$dateFrom." and P.t_end <=".$dateTo." and P.status=\"succ\" and P.disabled=\"false\" and P.member_id!=0 and P.member_id is not NULL";
        return $this->db->selectrow( $sql );
    }

    function all_member( )
    {
        $sql = "SELECT  COUNT(member_id) as allMember FROM sdb_members where disabled=\"false\"";
        return $this->db->selectrow( $sql );
    }

    function orderwithoutpay( $type )
    {
        $result = $this->db->selectrow( "SELECT count(order_id) as ordercount FROM sdb_orders where status=\"active\" and disabled=\"false\" and pay_status=\"0\"" );
        return $result['ordercount'];
    }

    function count_yesterday_order( )
    {
        $result = $this->db->selectrow( "SELECT count(*) as counts FROM sdb_orders where status=\"active\" and disabled=\"false\" and pay_status=\"1\" and createtime>".strtotime( "-2 day" )." and createtime<".strtotime( "-1 day" ) );
        return $result['counts'];
    }

    function playwithoutdeliever( )
    {
        $result = $this->db->selectrow( "SELECT count(order_id) as ordercount FROM sdb_orders where status=\"active\" and disabled=\"false\" and pay_status=\"1\" and ship_status=\"0\"" );
        return $result['ordercount'];
    }

    function todaysorder( )
    {
        $now = strtotime( date( "Y-m-d" ) );
        $result = $this->db->selectrow( "SELECT count(order_id) as ordercount FROM sdb_orders where  disabled=\"false\"  and acttime>=".$now." and acttime<".( $now + 86400 ) );
        return $result['ordercount'];
    }

}

?>
