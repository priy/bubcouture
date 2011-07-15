<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "shopObject.php" );
class mdl_pubfile extends shopObject
{

    public $defaultCols = "file_name,file_ident,cdate,memo";
    public $idColumn = "file_id";
    public $textColumn = "file_name";
    public $defaultOrder = array
    (
        0 => "file_id",
        1 => "desc"
    );
    public $tableName = "sdb_pub_files";

    public function _filter( $filter )
    {
        $where = array( 1 );
        if ( $filter['file_type'] )
        {
            $where[] = "file_type = ".intval( $filter['file_type'] );
        }
        return parent::_filter( $filter )." and ".implode( $where, " AND " );
    }

    public function insert( $data )
    {
        $tags = $data['tags'];
        unset( $data['tags'] );
        if ( $imgId = parent::insert( $data ) )
        {
            $tag =& $this->system->loadModel( "system/tag" );
            foreach ( $tags as $t )
            {
                if ( $tagid = $tag->tagId( $t, "image" ) )
                {
                    $tagList[] = $tagid;
                    $this->db->exec( "insert into sdb_tag_rel (tag_id,rel_id) values (".$tagid.",".$imgId.")" );
                }
            }
            $tag->recount( $tagList );
            return $imgId;
        }
        else
        {
            return false;
        }
    }

}

?>
