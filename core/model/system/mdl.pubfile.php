<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "shopObject.php" );
class mdl_pubfile extends shopobject
{

    var $defaultCols = "file_name,file_ident,cdate,memo";
    var $idColumn = "file_id";
    var $textColumn = "file_name";
    var $defaultOrder = array
    (
        0 => "file_id",
        1 => "desc"
    );
    var $tableName = "sdb_pub_files";

    function _filter( $filter )
    {
        $where = array( 1 );
        if ( $filter['file_type'] )
        {
            $where[] = "file_type = ".intval( $filter['file_type'] );
        }
        return shopobject::_filter( $filter )." and ".implode( $where, " AND " );
    }

    function insert( $data )
    {
        $tags = $data['tags'];
        unset( $data->'tags' );
        if ( $imgId = shopobject::insert( $data ) )
        {
            $tag =& $this->system->loadmodel( "system/tag" );
            foreach ( $tags as $t )
            {
                if ( $tagid = $tag->tagid( $t, "image" ) )
                {
                    $tagList[] = $tagid;
                    $this->db->exec( "insert into sdb_tag_rel (tag_id,rel_id) values (".$tagid.",".$imgId.")" );
                }
            }
            $tag->recount( $tagList );
            return $imgId;
        }
        return false;
    }

}

?>
