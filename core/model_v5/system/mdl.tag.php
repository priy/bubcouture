<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "shopObject.php" );
class mdl_tag extends shopObject
{

    public $tableName = "sdb_tags";
    public $idColumn = "tag_id";
    public $textColumn = "tag_name";
    public $use_recycle = false;

    public function &tagList( $type, $count = false, $joinTable = null, $obj_id = null, $data = array( ), $where = false )
    {
        if ( $count )
        {
            if ( $joinTable && $obj_id )
            {
                if ( !$where )
                {
                    $sql = "select t.tag_id,t.tag_name,t.tag_type,count(o.{$obj_id}) as rel_count,{$obj_id} as ss,t.is_system\n                     FROM sdb_tags t\n                     LEFT JOIN sdb_tag_rel r ON r.tag_id=t.tag_id\n                     LEFT JOIN {$joinTable} o ON r.rel_id=o.{$obj_id} and o.disabled!='true'\n                     where tag_type='{$type}' group by t.tag_id";
                }
                else
                {
                    $sql = "select {$obj_id} as trel_id\n                     FROM sdb_tag_rel r\n                     LEFT JOIN {$joinTable} o ON r.rel_id=o.{$obj_id} and o.disabled!='true'\n                     where r.tag_id = {$where}";
                }
            }
            else
            {
                $sql = "select t.tag_id,t.tag_name,t.tag_type,count(r.rel_id) as rel_count,t.is_system FROM sdb_tags t LEFT JOIN sdb_tag_rel r ON r.tag_id=t.tag_id where tag_type='{$type}' group by t.tag_id";
            }
        }
        else
        {
            $sql = "select * FROM sdb_tags where tag_type='{$type}'";
        }
        $aRet = $this->db->select( $sql );
        if ( $where )
        {
            return $aRet;
        }
        if ( $data )
        {
            $tagList = $this->getSelctedTagList( $type, $data );
        }
        foreach ( $aRet as $key => $value )
        {
            if ( $tagList[$aRet[$key]['tag_id']] )
            {
                $aRet[$key]['status'] = $tagList[$aRet[$key]['tag_id']];
            }
            else
            {
                $aRet[$key]['status'] = "none";
            }
        }
        return $aRet;
    }

    public function getSelctedTagList( $type, $list )
    {
        if ( $list['goods_id'][0] == "_ALL_" && ( $type == "goods" || $type == "order" || $type == "member" ) )
        {
            $sql = "select t.tag_id,count(r.rel_id) as rel_count FROM sdb_tags t LEFT JOIN sdb_tag_rel r ON r.tag_id=t.tag_id where tag_type=\"".$type."\" group by t.tag_id";
            $result = $this->db->select( $sql );
            $oProducts = $this->system->loadModel( "goods/products" );
            $max = $oProducts->countNum( );
            foreach ( ( array )$result as $key => $value )
            {
                if ( $result[$key]['rel_count'] == $max )
                {
                    $list[$value['tag_id']] = "all";
                }
                else if ( $result[$key]['rel_count'] )
                {
                    $list[$value['tag_id']] = "part";
                }
            }
            return $list;
        }
        else if ( is_array( $list ) && $type )
        {
            $sql = "select t.tag_id,count(r.rel_id) as rel_count FROM sdb_tags t LEFT JOIN sdb_tag_rel r ON r.tag_id=t.tag_id where tag_type=\"".$type."\" and rel_id in (\"".implode( "\",\"", current( $list ) )."\") group by t.tag_id";
            $result = $this->db->select( $sql );
            $max = count( current( $list ) );
            foreach ( ( array )$result as $key => $value )
            {
                if ( $result[$key]['rel_count'] == $max )
                {
                    $list[$value['tag_id']] = "all";
                }
                else if ( $result[$key]['rel_count'] )
                {
                    $list[$value['tag_id']] = "part";
                }
            }
            return $list;
        }
        else
        {
            return false;
        }
    }

    public function getTagByName( $type, $name )
    {
        $aRet = $this->db->selectrow( "SELECT tag_id FROM sdb_tags WHERE tag_type='{$type}' AND tag_name='{$name}'" );
        return $aRet['tag_id'];
    }

    public function tagId( $tag, $type )
    {
        $tag = addslashes( $tag );
        $rs = $this->db->exec( "select * from sdb_tags where tag_name=\"".$tag."\" and tag_type=\"".$type."\"" );
        if ( $rows = $this->db->getRows( $rs ) )
        {
            return $rows[0]['tag_id'];
        }
        else
        {
            $sql = $this->db->getInsertSQL( $rs, array(
                "tag_name" => $tag,
                "tag_type" => $type
            ) );
            if ( $this->db->exec( $sql ) )
            {
                return $this->db->lastInsertId( );
            }
            else
            {
                return false;
            }
        }
    }

    public function removeObjTag( $objid )
    {
        foreach ( $this->db->select( "select DISTINCT(tag_id) FROM sdb_tag_rel where rel_id=".intval( $objid ) ) as $rows )
        {
            $aDel[] = $rows['tag_id'];
        }
        $this->db->exec( "DELETE FROM sdb_tag_rel where rel_id=".intval( $objid ) );
        return $this->recount( $aDel );
    }

    public function begin( )
    {
    }

    public function addTag( $tag_id, $obj_id )
    {
        $this->db->exec( "INSERT INTO `sdb_tag_rel` ( `tag_id`,`rel_id` ) VALUES ( ".intval( $tag_id ).",".$obj_id." )" );
        return $this->recount( array(
            $tag_id
        ) );
    }

    public function end( )
    {
    }

    public function newTag( $tagName, $type )
    {
        if ( !( $row = $this->db->selectrow( "select * from sdb_tags where tag_name =\"".$tagName."\" and tag_type=\"".$type."\"" ) ) )
        {
            $tagName = trim( $tagName );
            if ( strstr( $tagName, "" ) == " " )
            {
                trigger_error( "��ǩ���в��ܻ��пθ��ַ�", E_USER_ERROR );
                return;
            }
            $rs = $this->db->exec( "select * FROM sdb_tags" );
            $sql = $this->db->getInsertSQL( $rs, array(
                "tag_name" => $tagName,
                "tag_type" => $type
            ) );
            if ( $this->db->exec( $sql ) )
            {
                return $this->db->lastInsertId( );
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    public function rename( $tag_id, $name )
    {
        $rs = $this->db->exec( "select tag_name from sdb_tags where tag_id=".intval( $tag_id ) );
        $sql = $sql = $this->db->getUpdateSQL( $rs, array(
            "tag_name" => $name
        ) );
        return !$sql || $this->db->exec( $sql );
    }

    public function remove( $tag_id )
    {
        $row = $this->db->select( "select filter,virtual_cat_id from sdb_goods_virtual_cat" );
        foreach ( $row as $key => $val )
        {
            if ( strpos( $row[$key]['filter'], "&tag[]=".intval( $tag_id ) ) )
            {
                $ab = $row[$key]['filter'];
                $ap = explode( "&", $ab );
                $b = array_values( $ap );
                $i = 0;
                for ( ; $i <= count( $b ); ++$i )
                {
                    if ( $b[$i] == "tag[]=".intval( $tag_id ) )
                    {
                        unset( $b[$i] );
                        break;
                    }
                }
                $result[] = implode( "&", $b );
                $this->db->exec( "update sdb_goods_virtual_cat set filter = \"".$result[0]."\" where virtual_cat_id=".intval( $row[$key]['virtual_cat_id'] ) );
            }
        }
        $this->db->exec( "delete from sdb_tags where tag_id=".intval( $tag_id ) );
        $this->db->exec( "delete from sdb_tag_rel where tag_id=".intval( $tag_id ) );
    }

    public function recount( $tags )
    {
        foreach ( $tags as $tag_id )
        {
            $row = $this->db->selectrow( "select count(*) as count from sdb_tag_rel where tag_id=".intval( $tag_id ) );
            $this->db->exec( "update sdb_tags set rel_count=".intval( $row['count'] )." where tag_id=".intval( $tag_id ) );
        }
    }

    public function getTagRel( $tagid, $relid )
    {
        $row = $this->db->selectrow( "select tag_id from sdb_tag_rel where tag_id=".$tagid." and rel_id=".$relid );
        if ( $row )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

}

?>
