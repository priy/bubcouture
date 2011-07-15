<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function widget_orderlist( &$setting, &$system )
{
    $order =& $system->loadModel( "trading/order" );
    $number = intval( $setting['rowNum'] ) ? intval( $setting['rowNum'] ) : 5;
    if ( $setting['smallPic'] )
    {
        $setting['smallPic'] = $system->base_url( )."statics/icons/".$setting['smallPic'];
    }
    $result = $order->getLastestOrder( $number );
    foreach ( $result as $key => $val )
    {
        $aTmp[$key]['order_id'] = $val['order_id'];
        $aTmp[$key]['ship_name'] = $val['ship_name'];
        $aTmp[$key]['sex'] = $val['sex'];
        $aTmp[$key]['date'] = date( "Y-m-d", $val['createtime'] );
        $aTmp[$key]['total_amount'] = $val['total_amount'];
    }
    return $aTmp;
}

?>
