<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_compile_modifier_cur( $attrs, &$compile )
{
    if ( !strpos( $attrs, "," ) || false !== strpos( $attrs, "," ) )
    {
        $compile->_head_stack['\$CURRENCY = &\$this->system->loadModel('system/cur')'] = 1;
        return $attrs = "\$CURRENCY->changer(".$attrs.")";
    }
}

?>
