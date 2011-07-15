<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

echo "    ";
class mdl_systmpl extends modelfactory
{

    function fetch( $tplname, $data = null )
    {
        $smarty =& $this->system->loadmodel( "system/frontend" );
        $data['shopname'] = $this->system->getconf( "system.shopname" );
        $data['pay_time'] = date( "Y-m-d H:i:s", $data['acttime'] );
        $smarty->_vars =& $data;
        $output = $smarty->fetch( "systmpl:".$tplname );
        unset( $smarty );
        return $output;
    }

    function gettitle( $ident )
    {
        $row = $this->db->select( "select title,path from sdb_sitemaps where action='page:".$ident."'" );
        if ( $row[0]['path'] )
        {
            $row[0]['path'] = substr( $row[0]['path'], 0, strlen( $row[0]['path'] ) - 1 );
            $parentRow = $this->db->select( "select title,action as link from sdb_sitemaps where node_id in (".$row[0]['path'].")" );
            $parentRow[] = array(
                "title" => $row[0]['title'],
                "link" => $row[0]['action']
            );
            return $parentRow;
        }
        return $row;
    }

    function _file( $name )
    {
        if ( $p = strpos( $name, ":" ) )
        {
            $type = substr( $name, 0, $p );
            $name = substr( $name, $p + 1 );
            if ( $type == "messenger" )
            {
                return PLUGIN_DIR."/messenger/".$name.".html";
            }
        }
        else if ( defined( "CUSTOM_CORE_DIR" ) && is_file( CUSTOM_CORE_DIR."/html/".$name.".html" ) )
        {
            return CUSTOM_CORE_DIR."/html/".$name.".html";
        }
        else
        {
            return CORE_DIR."/html/".$name.".html";
        }
    }

    function get( $name )
    {
        if ( $aRet = $this->db->selectrow( "SELECT content FROM sdb_systmpl WHERE active='true' and tmpl_name = '".$name."'" ) )
        {
            return $aRet['content'];
        }
        return file_get_contents( $this->_file( $name ) );
    }

    function clear( $name )
    {
        $rs = $this->db->exec( "select * from sdb_systmpl where tmpl_name='".$name."'" );
        $sql = $this->db->getupdatesql( $rs, array(
            "edittime" => time( ),
            "active" => "false"
        ) );
        return $this->db->exec( $sql );
    }

    function tpl_src( $matches )
    {
        return "<{".html_entity_decode( $matches[1] )."}>";
    }

    function set( $name, $body )
    {
        $body = str_replace( array( "&lt;{", "}&gt;" ), array( "<{", "}>" ), $body );
        $body = preg_replace_callback( "/<{(.+?)}>/", array(
            $this,
            "tpl_src"
        ), $body );
        $rs = $this->db->exec( "select * from sdb_systmpl where tmpl_name='".$name."'" );
        $sql = $this->db->getupdatesql( $rs, array(
            "tmpl_name" => $name,
            "edittime" => time( ),
            "active" => "true",
            "content" => $body
        ), true );
        return $this->db->exec( $sql );
    }

    function getbytype( $type )
    {
        $aRet = $this->db->selectrow( "SELECT content FROM sdb_systmpl WHERE tmpl_name = '".$type."'" );
        return $aRet['content'];
    }

    function updatecontent( $type, $txt )
    {
        $rs = $this->db->exec( "select * from sdb_systmpl where tmpl_name='".$type."'" );
        $aData['content'] = $txt;
        $aData['t'] = time( );
        $aData['tmpl_name'] = $type;
        $sql = $this->db->getupdatesql( $rs, $aData, true );
        return $this->db->exec( $sql );
    }

}

?>
