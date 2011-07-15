<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_function_json( $params, &$smarty )
{
    return json_encode( $params['from'] );
}

?>
