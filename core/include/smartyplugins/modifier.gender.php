<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_modifier_gender( $result )
{
    switch ( $result )
    {
    case "male" :
        return "男";
    case "female" :
        return "女";
    }
}

?>
