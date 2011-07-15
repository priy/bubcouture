<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_compiler_finder_lister( $params, &$compiler )
{
    $controller =& $compiler->_parent;
    $o =& $controller->model;
    $table_define = var_export( $o->_columns( ), 1 );
    $ret = "    \$o = &\$this->model;\n    \$o->__table_define = {$table_define};\n    if(!function_exists('action_finder_lister')){\n        require(CORE_INCLUDE_DIR.'/core/action.finder_lister.php');\n    }\n    action_finder_lister(\$this);\n    \$this->_vars['_finder']['id'] = '{$o->idColumn}';\n    \$this->_vars['_finder']['hasTag'] = '{$o->hasTag}';\n    echo \$this->_fetch_compile_include(\$this->_vars['_finder']['current_view'],array());\n    \$o = null;";
    return $ret;
}

?>
