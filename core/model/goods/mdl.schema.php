<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "plugin.php" );
class mdl_schema extends plugin
{

    var $plugin_type = "dir";
    var $plugin_name = "schema";
    var $prefix = "schema.";

    function runfunc( $gid, $action, $args )
    {
        $row = $this->db->selectrow( "select s.schema_id from sdb_goods g left join sdb_goods_cat c on c.cat_id=g.cat_id left join sdb_goods_type s on c.type_id=s.type_id where goods_id=".intval( $gid ) );
        include_once( SCHEMA_DIR.$row['schema_id']."/schema.php" );
        $action = "func_".$action;
        $class = "schema_".$row['schema_id'];
        $obj = new $class( );
        if ( is_callable( array(
            $class,
            $action
        ) ) )
        {
            call_user_func_array( array(
                $obj,
                $action
            ), $args );
        }
        else
        {
            echo "error:";
        }
        return false;
    }

    function load( $schema_id )
    {
        if ( $schema_id )
        {
            include_once( SCHEMA_DIR.$schema_id."/schema.".$schema_id.".php" );
            $this->_obj = new $schema_id( $this );
        }
    }

    function _get_schema_template( $tpl_name, &$tpl_source, &$smarty )
    {
        $tpl_source = file_get_contents( SCHEMA_DIR."/".$tpl_name );
        if ( !is_bool( $tpl_source ) )
        {
            return true;
        }
        return false;
    }

    function _get_schema_timestamp( $tpl_name, &$tpl_timestamp, &$smarty )
    {
        $tpl_timestamp = filemtime( SCHEMA_DIR."/".$tpl_name );
        if ( !is_bool( $tpl_timestamp ) )
        {
            return true;
        }
        return false;
    }

    function var_flip( $var )
    {
        $var = unserialize( $var );
        if ( is_array( $var ) )
        {
            $ret = array( );
            $i = 0;
            foreach ( $var as $p )
            {
                if ( $p['optionAlias'] )
                {
                    $optionAlias = $p['optionAlias'];
                    foreach ( $p['options'] as $k1 => $v1 )
                    {
                        $alias = $optionAlias[$k1] ? "{".$optionAlias[$k1]."}" : "";
                        $p['options'][$k1] = $p['options'][$k1].$alias;
                    }
                }
                foreach ( $p as $k => $v )
                {
                    if ( $k == "options" )
                    {
                        $ret[$k][$i] = implode( ",", $v );
                    }
                    else
                    {
                        $ret[$k][$i] = $v;
                    }
                }
                ++$i;
            }
            return $ret;
        }
        return $var;
    }

    function toedit( $type_id )
    {
        if ( $row = $this->db->selectrow( "select * from sdb_goods_type where type_id=".intval( $type_id ) ) )
        {
            if ( $row['props'] )
            {
                $row['props'] = $this->var_flip( $row['props'] );
            }
            if ( $row['minfo'] )
            {
                $row['minfo'] = $this->var_flip( $row['minfo'] );
            }
            if ( $row['setting'] )
            {
                $row['setting'] = unserialize( $row['setting'] );
            }
            if ( $row['params'] )
            {
                $params = unserialize( $row['params'] );
                $i = 0;
                foreach ( $params as $group => $groupitems )
                {
                    $p['group'][$i] = $group;
                    $j = 0;
                    foreach ( $groupitems as $itemid => $alias )
                    {
                        $p['name'][$i][$j] = $itemid;
                        $p['alias'][$i][$j] = $alias;
                        ++$j;
                    }
                    ++$i;
                }
                $row['params'] = $p;
            }
            foreach ( $this->db->select( "SELECT b.brand_id FROM sdb_type_brand t\n                        LEFT JOIN sdb_brand b ON t.brand_id = b.brand_id\n                        WHERE t.type_id=".intval( $type_id )." AND disabled = 'false'" ) as $r )
            {
                $row['brands'][] = $r['brand_id'];
            }
            $schema_id = $row['schema_id'];
            unset( $row->'schema_id' );
            $GLOBALS['_POST'] = $row;
            $this->dialog( $schema_id, "edit" );
        }
        else
        {
        }
        return false;
    }

    function dialog( $schema_id, $typeid = 0 )
    {
        $tpl = "init.html";
        $data = array(
            "message" => time( )
        );
        $errmsg = "";
        $smarty =& $this->system->loadmodel( "system/frontend" );
        $smarty->template_dir = CORE_DIR."/admin/view/";
        $smarty->default_resource_type = "file";
        $smarty->_register_resource( "schema", array(
            array(
                $this,
                "_get_schema_template"
            ),
            array(
                $this,
                "_get_schema_timestamp"
            )
        ) );
        $smarty->_plugins['block']['form'] = array(
            $this,
            "_build_newType_form"
        );
        $smarty->schema_id = $schema_id;
        if ( $schema_id && include_once( SCHEMA_DIR.$schema_id."/schema.".$schema_id.".php" ) )
        {
            $type = "schema_".$schema_id;
            $obj = new $type( );
            $data['nextStep'] = "commit";
            if ( !isset( $_POST['setting'] ) )
            {
                $GLOBALS['_POST']['setting'] = get_object_vars( $obj );
            }
            $func = substr( $tpl, 0, strrpos( $tpl, "." ) );
            $data['_TPL_'] = "schema:".$schema_id."/view/".$tpl;
            if ( method_exists( $obj, $func ) && true !== $obj->$func( $_POST, $data['message'], $data, $errmsg ) )
            {
                $data['err_message'] = $errmsg;
                $data['_TPL_'] = "product/gtype/error.html";
            }
            $data['schema']['id'] = $schema_id;
            $data['page'] = $tpl;
            $data['_POST'] =& $GLOBALS['_POST'];
            $smarty->_vars =& $data;
            header( "Content-Type: text/html;charset=utf-8" );
            header( "Cache-Control: no-cache,no-store , must-revalidate" );
            header( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
            header( "Pragma: no-cache" );
            $smarty->fetch( "product/gtype/form.html", 1 );
        }
    }

    function save( $schema_id, $options, &$message, $methods )
    {
        if ( $this->db->selectrow( "select * from sdb_goods_type where name=".$this->db->quote( $options['name'] ).( $options['type_id'] ? " AND type_id != ".$options['type_id'] : "" ) ) )
        {
            echo __( "对不起，本类型名已存在。" );
            exit( );
        }
        $options['setting']['use_spec'] = $options['use_spec'];
        $options['schema_id'] = $schema_id;
        $aPropValue = array( );
        if ( is_array( $options['props']['name'] ) )
        {
            $inputLoop = 21;
            $selectLoop = 1;
            $props = array( );
            foreach ( $options['props']['name'] as $k => $v )
            {
                if ( $options['props']['name'][$k] )
                {
                    if ( $options['props']['type'][$k] <= 1 )
                    {
                        $props[$inputLoop++] = array(
                            "name" => trim( $v ),
                            "alias" => $options['props']['alias'][$k],
                            "type" => "input",
                            "search" => $options['props']['type'][$k] ? "disabled" : "input",
                            "show" => intval( $options['props']['show'][$k] ),
                            "ordernum" => $options['props']['ordernum'][$k]
                        );
                    }
                    else
                    {
                        $t_options = explode( ",", trim( $options['props']['options'][$k] ) );
                        foreach ( $t_options as $k1 => $v1 )
                        {
                            if ( !empty( $v1 ) )
                            {
                                $optionaliasv = strstr( $v1, "|" );
                                $t_options[$k1] = $optionaliasv === false ? $v1 : substr( $v1, 0, strpos( $v1, $optionaliasv ) );
                                $t_optionAlias[$k1] = substr( $optionaliasv, 1 );
                            }
                            else
                            {
                                unset( $t_options->$k1 );
                                unset( $t_optionAlias->$k1 );
                            }
                        }
                        switch ( $options['props']['type'][$k] )
                        {
                        case 2 :
                            $search = "nav";
                            break;
                        case 3 :
                            $search = "select";
                            break;
                        case 4 :
                            $search = "disabled";
                        }
                        $props[$selectLoop++] = array(
                            "name" => trim( $v ),
                            "alias" => $options['props']['alias'][$k],
                            "type" => "select",
                            "search" => $search,
                            "options" => $t_options,
                            "optionAlias" => $t_optionAlias,
                            "show" => intval( $options['props']['show'][$k] ),
                            "ordernum" => $options['props']['ordernum'][$k]
                        );
                    }
                }
            }
            $options['props'] =& $props;
        }
        else
        {
            $options['props'] = array( );
        }
        $aParams = array( );
        if ( is_array( $options['params']['group'] ) )
        {
            $aParams = array( );
            foreach ( $options['params']['group'] as $key => $name )
            {
                if ( !trim( $name ) && !is_array( $options['params']['name'][$key] ) && !( 0 < count( $options['params']['name'][$key] ) ) )
                {
                    $a = array( );
                    foreach ( $options['params']['name'][$key] as $k => $item )
                    {
                        $a[$item] = $options['params']['alias'][$key][$k];
                    }
                    $aParams[trim( $name )] = $a;
                }
            }
        }
        $options['params'] =& $aParams;
        if ( is_array( $options['minfo']['label'] ) )
        {
            $minfo = array( );
            $type = array( 0 => "input", 1 => "text", 2 => "select" );
            foreach ( $options['minfo']['label'] as $k => $v )
            {
                if ( !$options['minfo']['label'][$k] && !isset( $options['minfo']['type'][$k] ) )
                {
                    if ( trim( $options['minfo']['name'][$k] ) == "" )
                    {
                        $options['minfo']['name'][$k] = "M".md5( $options['minfo']['label'][$k] );
                    }
                    $m = array(
                        "label" => trim( $options['minfo']['label'][$k] ),
                        "name" => trim( $options['minfo']['name'][$k] ),
                        "type" => $type[$options['minfo']['type'][$k]]
                    );
                    if ( $options['minfo']['type'][$k] == 2 )
                    {
                        $m['options'] = preg_split( "/[\\s,]+/", trim( $options['minfo']['options'][$k] ) );
                    }
                    $minfo[] = $m;
                }
            }
            $options['minfo'] =& $minfo;
        }
        $options['is_physical'] = $options['is_physical'] ? "1" : "0";
        $options['dly_func'] = in_array( "delivery", $methods ) ? "1" : "0";
        $options['ret_func'] = in_array( "toreturn", $methods ) ? "1" : "0";
        if ( $options['type_id'] )
        {
            $rs = $this->db->query( "SELECT * FROM sdb_goods_type where type_id=".intval( $options['type_id'] ) );
            $sql = $this->db->getupdatesql( $rs, $options );
            if ( !$sql && $this->db->exec( $sql ) )
            {
                $gType =& $this->system->loadmodel( "goods/gtype" );
                $gType->checkdefined( );
                $this->save_brand( $options['type_id'], $options['brand'] );
            }
            else
            {
                $message = $sql.$this->db->errorinfo( );
                return false;
            }
        }
        else
        {
            unset( $options->'type_id' );
            $rs = $this->db->query( "select * from sdb_goods_type where 0=1" );
            $sql = $this->db->getinsertsql( $rs, $options );
            if ( $this->db->exec( $sql ) )
            {
                $typeid = $this->db->lastinsertid( );
                $gType =& $this->system->loadmodel( "goods/gtype" );
                $gType->checkdefined( );
                $this->save_brand( $typeid, $options['brand'] );
                $options['type_id'] = $typeid;
            }
            else
            {
                $message = $sql.$this->db->errorinfo( );
                return false;
            }
        }
        $this->save_spec( $options['type_id'], $options['spec_id'], $options['spec_type'] );
        return $options['type_id'];
    }

    function save_brand( $typeid, &$aParam )
    {
        $this->db->exec( "DELETE FROM sdb_type_brand WHERE type_id = ".$typeid );
        foreach ( $aParam as $brandId )
        {
            $aData['type_id'] = $typeid;
            $aData['brand_id'] = $brandId;
            $rs = $this->db->query( "SELECT * FROM sdb_type_brand WHERE 0=1" );
            $sql = $this->db->getinsertsql( $rs, $aData );
            $this->db->exec( $sql );
        }
    }

    function save_spec( $typeid, $aId, $aType )
    {
        $this->db->exec( "DELETE FROM sdb_goods_type_spec WHERE type_id = ".$typeid );
        $aData['type_id'] = $typeid;
        $aId = array_reverse( $aId );
        $aType = array_reverse( $aType );
        foreach ( $aId as $k => $specId )
        {
            if ( $specId )
            {
                $aData['spec_id'] = $specId;
                $aData['spec_style'] = $aType[$k];
                $rs = $this->db->exec( "SELECT * FROM sdb_goods_type_spec WHERE 0=1" );
                $sql = $this->db->getinsertsql( $rs, $aData );
                $this->db->exec( $sql );
            }
        }
    }

    function _build_newtype_form( $params, $content, &$smarty, &$repeat )
    {
        $action = $params['action'] ? $params['action'] : "commit";
        $params['action'] = "index.php?ctl=goods/gtype&act=toAdd&p[0]=".$smarty->schema_id."&p[1]=".$params['action'];
        $params['method'] = "post";
        $html = "<form";
        foreach ( $params as $k => $v )
        {
            $html .= " ".$k."=\"".$v."\"";
        }
        $html .= ">".$content."<div style=\"text-align:center\"><input type=\"hidden\" name=\"_SX[ROT][".$action."]\" value=\"".$smarty->inStep."\" /> ";
        if ( isset( $_POST['_SX']['ROT'][$smarty->inStep] ) )
        {
            $html .= __( "<input type=\"submit\" value=\"上一步\" onclick=\"this.form.action='index.php?ctl=goods/gtype&act=toAdd&p[0]=" ).$smarty->schema_id."&p[1]=".$_POST['_SX']['ROT'][$smarty->inStep]."'\" />";
        }
        return $html.__( " <b style=\"margin-left: 25em;\" class=\"submitBtn\"><input type=\"submit\" value=\"下一步\" /></b></div><input type=\"hidden\" name=\"_POST_\" value=\"" ).rawurlencode( serialize( $_POST ) )."\" /></form>";
    }

    function instance( $schema_id )
    {
        if ( !isset( $this->_inc[$schema_id] ) )
        {
            include_once( SCHEMA_DIR.$schema_id."/schema.".$schema_id.".php" );
            $class = "schema_".$schema_id;
            $this->_inc[$schema_id] = new $class( );
        }
        return $this->_inc[$schema_id];
    }

    function delivery( $schema_id, $minfo, $setting, $nums, &$logs )
    {
        $obj =& $this->instance( $schema_id );
        $class = "schema_".$schema_id;
        if ( is_callable( array(
            $class,
            "delivery"
        ) ) )
        {
            return $obj->delivery( $minfo, $nums, $setting, $logs );
        }
        $logs = $schema_id." don's not hav delivery method";
        return false;
    }

    function toreturn( $minfo, $nums, $setting, &$log )
    {
        echo time( );
        return true;
    }

}

?>
