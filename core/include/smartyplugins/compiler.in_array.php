<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_compiler_in_array( $attrs, &$smarty )
{
    $return = "    if (is_array(".$attrs['array']."))\r\n    {\r\n        if (in_array({$attrs['match']}, {$attrs['array']}))\r\n        {\r\n            echo {$attrs['returnvalue']};\r\n        }\r\n    }";
    return $return;
}

?>
