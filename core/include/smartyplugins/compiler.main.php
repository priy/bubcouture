<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_compiler_main( $attrs, &$smarty )
{
    return " echo  \$this->_fetch_compile_include(\$this->template_exists('user:'.\$this->theme.'/view/'.\$this->_vars['_MAIN_'])?'user:'.\$this->theme.'/view/'.\$this->_vars['_MAIN_']:'shop:'.\$this->_vars['_MAIN_'], array());";
}

?>
