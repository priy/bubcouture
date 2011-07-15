<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class TextUtils
{

    public function csv2array( $csvfile, $fields, $delimiter = ",", $enclosure = "\"", $callback = NULL )
    {
        LogUtils::log_str( "csv2array" );
        $handle = fopen( $csvfile, "r" );
        LogUtils::log_obj( $handle );
        if ( !$handle )
        {
            return array( );
        }
        $row = 1;
        $list = array( );
        while ( $data = fgetcsv( $handle, 262144, $delimiter, $enclosure ) )
        {
            if ( count( $fields ) < count( $data ) )
            {
                $data = array_slice( $data, 0, count( $fields ) );
            }
            if ( count( $data ) < count( $fields ) )
            {
                $fields = array_slice( $fields, 0, count( $data ) );
            }
            foreach ( $data as $key => $item )
            {
                $data[$key] = str_replace( "'", "\\'", $item );
            }
            $v = array( );
            $i = 0;
            for ( ; $i < count( $fields ); ++$i )
            {
                $v[$fields[$i]] = $data[$i];
            }
            $list[] = $v;
            if ( $callback )
            {
                call_user_func( $callback, $v );
            }
        }
        fclose( $handle );
        return $list;
    }

}

?>
