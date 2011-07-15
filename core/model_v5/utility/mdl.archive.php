<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class mdl_archive extends modelFactory
{

    public function write( $table, $file )
    {
        $file = dirname( __FILE__ )."/".basename( $file );
        $rs = $this->db->exec( "select * from ".$table );
        $col_count = mysql_num_fields( $rs['rs'] );
        $i = 0;
        for ( ; $i < $col_count; ++$i )
        {
            $column = mysql_fetch_field( $rs['rs'], $i );
            $columns[] = $column->name;
        }
        $fp = fopen( $file, "w+" );
        fputcsv( $fp, $columns );
        while ( $row = mysql_fetch_row( $rs['rs'] ) )
        {
            fputcsv( $fp, $row );
        }
        fclose( $fp );
    }

    public function read( $table, $file )
    {
        $file = dirname( __FILE__ )."/".basename( $file );
        $handle = fopen( $file, "r" );
        $row = fgetcsv( $handle, 1024, "," );
        do
        {
            if ( $row = fgetcsv( $handle, 10240, "," ) )
            {
                break;
            }
        } while ( 1 );
        fclose( $handle );
    }

}

if ( !function_exists( "fputcsv" ) )
{
    function fputcsv( $filePointer, $dataArray, $delimiter, $enclosure )
    {
        $string = "";
        $writeDelimiter = FALSE;
        foreach ( $dataArray as $dataElement )
        {
            $dataElement = str_replace( "\"", "\"\"", $dataElement );
            if ( $writeDelimiter )
            {
                $string .= $delimiter;
            }
            $string .= $enclosure.$dataElement.$enclosure;
            $writeDelimiter = TRUE;
        }
        $string .= "\n";
        fwrite( $filePointer, $string );
    }
}
?>
