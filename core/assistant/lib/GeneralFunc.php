<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function as_find_files( $dir, $pattern )
{
    $files = array( );
    if ( $handle = opendir( $dir ) )
    {
        while ( FALSE !== ( $file = readdir( $handle ) ) )
        {
            if ( preg_match( $pattern, $file, $matches ) )
            {
                $files[$file] = $matches;
            }
        }
        closedir( $handle );
    }
    return $files;
}

?>
