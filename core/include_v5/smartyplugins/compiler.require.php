<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_compiler_require( $attrs, &$smarty )
{
    $_to_require = "\$this->_get_resource('user:'.\$this->theme.'/'.{$attrs['file']})?('user:'.\$this->theme.'/'.{$attrs['file']}):('shop:'.{$attrs['file']})";
    return " echo \$this->_fetch_compile_include({$_to_require}, array());";
}

?>
