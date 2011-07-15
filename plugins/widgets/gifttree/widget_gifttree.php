<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function widget_gifttree( &$setting, &$system )
{
    $gift =& $system->loadModel( "trading/gift" );
    $result = $gift->getAllList( );
    foreach ( $result as $k => $v )
    {
        $return[$v['cat']]['link'] = $v['giftcat_id'];
        $return[$v['cat']]['sub'][$k]['name'] = $v['name'];
        $return[$v['cat']]['sub'][$k]['gift_id'] = $v['gift_id'];
    }
    return $return;
}

?>
