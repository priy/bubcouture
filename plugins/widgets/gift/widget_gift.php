<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function widget_gift( &$setting, &$system )
{
    $gift =& $system->loadModel( "trading/gift" );
    $result = $gift->getGiftList( 0, $setting['giftnum'] ? $setting['giftnum'] : 5, $giftCount, array( "ifrecommend" => 1 ) );
    return $result;
}

?>
