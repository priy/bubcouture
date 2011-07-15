<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function widget_cfg_article( $system )
{
    $o = $system->loadModel( "content/article" );
    $arrow = array(
        "arrow" => array( "arrow_1.gif" => "箭头1", "arrow_2.gif" => "箭头2", "arrow_3.gif" => "箭头3", "arrow_4.gif" => "箭头4", "arrow_5.gif" => "箭头5", 6 => "自定义" )
    );
    return array_merge( array(
        "cat" => $o->getCategorys( )
    ), $arrow );
}

?>
