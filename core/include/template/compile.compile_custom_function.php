<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function compile_compile_custom_function( $function, $arguments, &$_result, &$object )
{
    if ( $function = $object->_plugin_exists( $function, "function" ) )
    {
        $_args = $object->_parse_arguments( $arguments );
        foreach ( $_args as $key => $value )
        {
            if ( is_bool( $value ) )
            {
                $value = $value ? "true" : "false";
            }
            else if ( is_null( $value ) )
            {
                $value = "null";
            }
            $_args[$key] = "'".$key."' => {$value}";
        }
        $_result = "<?php echo ";
        $_result .= $function."(array(".implode( ",", ( array )$_args )."), \$this);";
        $_result .= "?>";
        return true;
    }
    return false;
}

?>
