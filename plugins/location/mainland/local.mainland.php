<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class local_mainland
{

    public $name = "中国地区";
    public $desc = "系统默认为中国地区设置，包括港、澳、台地区。";
    public $maxdepth = 3;

    public function install( )
    {
        if ( $handle = fopen( dirname( __FILE__ )."/area.txt", "r" ) )
        {
            $i = 0;
            $sql = "INSERT INTO `sdb_regions` (`region_id`, `package`, `p_region_id`,`region_path`,`region_grade`, `local_name`, `en_name`, `p_1`, `p_2`) VALUES ";
            while ( $data = fgets( $handle, 1000 ) )
            {
                $data = trim( $data );
                if ( substr( $data, -2 ) == "::" )
                {
                    if ( $aSql )
                    {
                        $sqlInsert = $sql.implode( ",", $aSql ).";";
                        $this->db->exec( $sqlInsert );
                        unset( $path );
                    }
                    ++$i;
                    $path[] = $i;
                    $regionPath = ",".implode( ",", $path ).",";
                    $aSql = array( );
                    $aTmp = explode( "::", $data );
                    $aSql[] = "(".$i.", 'mainland', NULL, '".$regionPath."', '".count( $path )."', '".$aTmp[0]."', NULL, NULL, NULL)";
                    $f_pid = $i;
                }
                else if ( strstr( $data, ":" ) )
                {
                    ++$i;
                    $aTmp = explode( ":", $data );
                    unset( $sPath );
                    $sPath[] = $f_pid;
                    $sPath[] = $i;
                    $regionPath = ",".implode( ",", $sPath ).",";
                    $aSql[] = "(".$i.", 'mainland', ".intval( $f_pid ).", '".$regionPath."', '".count( $sPath )."', '".$aTmp[0]."', NULL, NULL, NULL)";
                    if ( trim( $aTmp[1] ) )
                    {
                        $pid = $i;
                        $aTmp = explode( ",", trim( $aTmp[1] ) );
                        foreach ( $aTmp as $v )
                        {
                            ++$i;
                            $tmpPath = $regionPath.$i.",";
                            $grade = count( explode( ",", $tmpPath ) ) - 2;
                            $aSql[] = "(".$i.", 'mainland', ".intval( $pid ).", '".$tmpPath."', '".$grade."', '".$v."', NULL, NULL, NULL)";
                        }
                    }
                }
                else if ( $data )
                {
                    ++$i;
                    $tmpPath = $regionPath.$i.",";
                    $grade = count( explode( ",", $tmpPath ) ) - 2;
                    $aSql[] = "(".$i.", 'mainland', ".intval( $f_pid ).", '".$tmpPath."','".$grade."','".$data."', NULL, NULL, NULL)";
                }
            }
            fclose( $handle );
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

}

?>
