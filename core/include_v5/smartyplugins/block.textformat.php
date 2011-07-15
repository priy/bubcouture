<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_block_textformat( $params, $content, &$template_object )
{
    $style = null;
    $indent = 0;
    $indent_first = 0;
    $indent_char = " ";
    $wrap = 80;
    $wrap_char = "\n";
    $wrap_cut = false;
    $assign = null;
    if ( $content == null )
    {
        return true;
    }
    extract( $params );
    if ( $style == "email" )
    {
        $wrap = 72;
    }
    $paragraphs = preg_split( "![\\r\\n][\\r\\n]!", $content );
    foreach ( $paragraphs as $paragraph )
    {
        if ( $paragraph == "" )
        {
            continue;
        }
        $paragraph = preg_replace( array( "!\\s+!", "!(^\\s+)|(\\s+\$)!" ), array( " ", "" ), $paragraph );
        if ( 0 < $indent_first )
        {
            $paragraph = str_repeat( $indent_char, $indent_first ).$paragraph;
        }
        $paragraph = wordwrap( $paragraph, $wrap - $indent, $wrap_char, $wrap_cut );
        if ( 0 < $indent )
        {
            $paragraph = preg_replace( "!^!m", str_repeat( $indent_char, $indent ), $paragraph );
        }
        $output .= $paragraph.$wrap_char.$wrap_char;
    }
    if ( $assign != null )
    {
        $template_object->_vars[$assign] = $output;
    }
    else
    {
        echo $output;
    }
}

?>
