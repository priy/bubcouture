<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class mdl_seo extends modelfactory
{

    function set_seo( $type, $id, $aData )
    {
        $where[] = 1;
        if ( $type )
        {
            $where[] .= " type=\"".$type."\"";
        }
        if ( $id )
        {
            $where[] = " source_id=\"".$id."\"";
        }
        $insertData = array(
            "type" => $type,
            "source_id" => $id
        );
        foreach ( $aData as $key => $value )
        {
            $insertData['store_key'] = $key;
            $insertData['value'] = $value !== null ? $value : "";
            $rs = $this->db->exec( "select * from sdb_seo WHERE ".implode( " and ", $where )." and store_key=\"".$key."\"" );
            $sql = $this->db->getupdatesql( $rs, $insertData, true );
            if ( $sql )
            {
                $this->db->exec( $sql );
            }
        }
        return true;
    }

    function get_seo( $type, $id, $filter = array
    (
        0 => "keywords",
        1 => "descript",
        2 => "title"
    ) )
    {
        $where[] = 1;
        if ( $type )
        {
            $where[] .= " type=\"".$type."\"";
        }
        if ( $id )
        {
            $where[] = " source_id=\"".$id."\"";
        }
        $where[] = "(store_key=\"".implode( "\" or store_key =\"", $filter )."\")";
        $result = $this->db->select( "select * from sdb_seo WHERE ".implode( " and ", $where ) );
        $return_result = array(
            "type" => $type,
            "source_id" => $id
        );
        foreach ( $result as $key => $value )
        {
            $return_result[$value['store_key']] = $value['value'];
        }
        return $return_result;
    }

}

?>
