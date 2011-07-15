<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function database_split_sql( $sql, &$object )
{
    $ret = array( );
    $sql = trim( $sql );
    $sql_len = strlen( $sql );
    $char = "";
    $string_start = "";
    $in_string = FALSE;
    $sql = trim( $sql );
    if ( $object->dbver == 3 )
    {
        $sql = str_replace( SQL_MYISAM_SYNTAX, "", $sql );
        $sql = str_replace( SQL_HEAP_SYNTAX, "", $sql );
    }
    else if ( $object->dbver == 6 )
    {
        $sql = str_replace( SQL_MYISAM_SYNTAX, str_replace( "type = ", "engine = ", SQL_MYISAM_SYNTAX ), $sql );
        $sql = str_replace( SQL_HEAP_SYNTAX, str_replace( "type = ", "engine = ", SQL_HEAP_SYNTAX ), $sql );
    }
    preg_match_all( "/(INSERT|UPDATE|DELETE|DROP|CREATE)+[^\\n]+\\;/i", $sql, $matches );
    if ( is_array( $matches[0] ) )
    {
        return $matches[0];
    }
    else
    {
        return false;
    }
    return $ret;
}

define( "SQL_MYISAM_SYNTAX", "type = MyISAM DEFAULT CHARACTER SET utf8" );
define( "SQL_HEAP_SYNTAX", "type = HEAP DEFAULT CHARACTER SET utf8" );
?>
