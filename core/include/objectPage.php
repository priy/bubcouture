<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class objectpage extends adminpage
{

    var $canRemove = true;
    var $deleteAble = true;
    var $allowImport = false;
    var $allowExport = true;
    var $noRecycle = false;
    var $detail_title = "finder/detail_title.html";
    var $default_lister = "finder/list.html";

    function objectpage( )
    {
        adminpage::adminpage( );
        $this->model =& $this->system->loadmodel( $this->object );
        $this->path[] = array(
            "text" => $this->name.__( "管理" ),
            "url" => "index.php?ctl=".$_GET['ctl']."&act=index"
        );
    }

    function save_col_setting( )
    {
        if ( 1 < count( $_POST['used'] ) )
        {
            $this->system->set_op_conf( "sort.".$this->object, implode( ",", $_POST['sort'] ) );
            $this->system->set_op_conf( "view.".$this->object, implode( ",", $_POST['used'] ) );
            echo __( "保存成功" );
        }
    }

    function detail( $object_id, $func = null )
    {
        if ( !method_exists( $this, "_detail" ) )
        {
            $this->system->responsecode( 404 );
            exit( );
        }
        $this->pagedata['_detail_func'] = $this->_detail( $object_id );
        $this->pagedata['_title_page'] = $this->detail_title;
        if ( !isset( $_GET['_ajax'] ) )
        {
            foreach ( $this->pagedata['_detail_func'] as $func => $item )
            {
                $this->$func( $object_id );
            }
            $data = $this->model->getlist( $this->model->textColumn, array(
                $this->model->idColumn => $object_id
            ), 0, 1 );
            $this->pagedata['title'] = $this->name.$data[0][$this->model->textColumn];
            $this->pagedata['_is_singlepage'] = true;
            $this->singlepage( "finder/detail-in-one.html" );
        }
        else
        {
            if ( !$func )
            {
                $func = key( $this->pagedata['_detail_func'] );
            }
            $this->$func( $object_id );
            $this->pagedata['_PAGE_'] = $this->pagedata['_detail_func'][$func]['tpl'];
            $this->pagedata['current_func'] = $func;
            $output = $this->fetch( "finder/detail.html" );
            $this->_send( $output );
        }
    }

    function do_export( $plugin_id )
    {
        set_time_limit( 0 );
        $this->system->__session_close( 0 );
        if ( !function_exists( "action_export" ) )
        {
            require( CORE_INCLUDE_DIR."/core/action.export.php" );
        }
        return action_export( $plugin_id, $this );
    }

    function colsetting( )
    {
        $used = $this->system->get_op_conf( "view.".$this->object );
        $allCols = $this->model->getcolumns( );
        if ( $this->model->hasTag )
        {
            $allCols['_tag_'] = array( "label" => "标签" );
        }
        $ret = array( );
        if ( $used )
        {
            $sort = $this->system->get_op_conf( "sort.".$this->object );
            $sort = array_flip( explode( ",", $sort ) );
            $used = array_flip( explode( ",", $used ) );
            foreach ( $allCols as $key => $col )
            {
                if ( !$allCols[$key]['hidden'] )
                {
                    if ( isset( $used[$key] ) )
                    {
                        $col['used'] = true;
                    }
                    if ( $key == "_cmd" )
                    {
                        $col['locked'] = true;
                    }
                    $ret[$key] = $col;
                    $sort_arr[] = $sort[$key];
                }
            }
            array_multisort( $ret, SORT_NUMERIC, $sort_arr );
        }
        else
        {
            $used = $this->finder_default_cols ? $this->finder_default_cols : $this->model->defaultCols;
            foreach ( explode( ",", $used ) as $key )
            {
                if ( !isset( $allCols[$key] ) && $allCols[$key]['hidden'] )
                {
                    $ret[$key] = $allCols[$key];
                    $ret[$key]['used'] = true;
                }
            }
            foreach ( $allCols as $key => $col )
            {
                if ( $key == "_cmd" )
                {
                    $col['locked'] = true;
                }
                if ( !$allCols[$key]['hidden'] )
                {
                    if ( isset( $ret[$key] ) )
                    {
                        $ret[$key] = array_merge( $col, $ret[$key] );
                    }
                    else
                    {
                        $ret[$key] = $col;
                    }
                }
            }
        }
        $this->pagedata['cols'] =& $ret;
        header( "Cache-Control:no-store, no-cache, must-revalidate" );
        $this->display( "finder/col_setting.html" );
    }

    function _views( )
    {
        return array( );
    }

    function cell_editor( )
    {
        $allCols = $this->model->getcolumns( );
        $params = $allCols[$_POST['key']];
        $data = $this->model->instance( $_POST['id'], $_POST['key'] );
        $params['value'] = $data[$_POST['key']];
        $params['name'] = "data";
        if ( is_array( $params['type'] ) )
        {
            $params['options'] = $params['type'];
            $params['type'] = "select";
        }
        if ( substr( $params['type'], 0, 7 ) == "object:" )
        {
            $params['nosearch'] = "true";
        }
        $this->pagedata['input'] =& $params;
        $this->display( "finder/cell_editor.html" );
    }

    function save_cell_value( $id, $key )
    {
        $this->begin( "index.php?ctl=".$_GET['ctl']."&act=index" );
        if ( $_POST['data'] === false )
        {
            $GLOBALS['_POST']['data'] = "false";
        }
        if ( $key == "marketable" )
        {
            $oGoods =& $this->system->loadmodel( "trading/goods" );
            $oGoods->updateupdowntime( $_POST['data'], $id );
        }
        if ( $id )
        {
            if ( method_exists( $this->model, $func = "modifier_".$key ) )
            {
                $this->model->$func( $_POST['data'] );
            }
            $rst = $this->model->update( array(
                $key => $_POST['data']
            ), array(
                $this->model->idColumn => $id
            ) );
            if ( !$rst )
            {
                $this->end( false, "保存失败" );
            }
        }
        $row = $this->model->instance( $id, $key );
        $data = $row[$key];
        if ( !isset( $data ) )
        {
            echo "ok:-";
        }
        else
        {
            if ( method_exists( $this->model, $func = "modifier_".$key ) )
            {
                $tmp = array(
                    $data => $data
                );
                $this->model->$func( $tmp );
            }
            else
            {
                $columns =& $this->model->_columns( );
                if ( is_array( $columns[$key]['type'] ) )
                {
                    $data = $columns[$key]['type'][$data];
                }
                else
                {
                    if ( substr( $columns[$key]['type'], 0, 7 ) == "object:" )
                    {
                        list( , $obj, $fkey ) = explode( ":", $columns[$key]['type'] );
                        $obj =& $this->system->loadmodel( $obj );
                        $fkey = $fkey ? $fkey : $obj->textColumn;
                        $r = $obj->getlist( $fkey, array(
                            $obj->idColumn => $data
                        ), 0, 1 );
                        $data = $r[0][$fkey];
                    }
                    else
                    {
                        if ( !function_exists( "type_modifier_bool" ) )
                        {
                            require( CORE_INCLUDE_DIR."/modifiers.php" );
                        }
                        if ( function_exists( $func = "type_modifier_".$columns[$key]['type'] ) )
                        {
                            $tmp = array(
                                $data => $data
                            );
                            $func( $tmp );
                        }
                    }
                }
            }
            echo "ok:".$data;
        }
    }

    function rentag( )
    {
        if ( $_POST['tag_id'] )
        {
            $tag =& $this->system->loadmodel( "system/tag" );
            $tag->rename( $_POST['tag_id'], $_POST['name'] );
        }
        header( "Location: index.php?ctl=".$this->controller."&act=tagmgr&_ajax=1&_wg=".$this->workground );
    }

    function deltag( )
    {
        if ( $_POST['tag_id'] )
        {
            $tag =& $this->system->loadmodel( "system/tag" );
            $tag->remove( $_POST['tag_id'] );
        }
        header( "Location: index.php?ctl=".$this->controller."&act=tagmgr&_ajax=1&_wg=".$this->workground );
    }

    function tagmgr( )
    {
        $this->path[] = array(
            "text" => $this->name.__( "标签管理" )
        );
        $this->pagedata['controller'] = $this->controller;
        $this->pagedata['tags'] = $this->model->taglist( 1 );
        $this->page( "system/tags/list.html" );
    }

    function newtag( )
    {
        $this->begin( "index.php?ctl=".$this->controller."&act=tagmgr" );
        $this->end( $this->model->newtag( $_POST['tag_name'], __( "标签添加成功" ) ) );
    }

    function settag( )
    {
        $tag = space_split( $_POST['_SET_TAGS_'] );
        unset( $_POST->'_SET_TAGS_' );
        if ( $this->model->settag( $_POST, $tag ) )
        {
            echo __( "标签已设置" );
        }
    }

    function taglist( $json = true )
    {
        if ( $result = $this->model->taglist( null, $_POST ) )
        {
            if ( $json )
            {
                echo json_encode( $result );
                exit( );
            }
            return $result;
        }
    }

    function gettaglist( )
    {
        if ( $result = $this->model->gettaglist( $_POST ) )
        {
            echo json_encode( $result );
            exit( );
        }
    }

    function index( )
    {
        if ( $_POST['_finder']['in_pager'] )
        {
            $this->finder_pager( );
        }
        else
        {
            $this->with_nav = false;
            if ( isset( $_GET['filter'] ) && ( $GLOBALS['_GET']['filter'] = ( array )unserialize( $_GET['filter'] ) ) )
            {
                $this->filter = array( );
                $this->filterInit = array( );
                foreach ( $GLOBALS['_GET']['filter'] as $key => $define )
                {
                    $this->filter[$key] = $define['v'];
                    if ( $_GET['vfilter'] != "hidden" )
                    {
                        $this->filterInit[] = array(
                            "label" => $define['t'],
                            "name" => $key,
                            "value" => $define['v']
                        );
                    }
                }
            }
            $this->page( "finder/common.html#".$_GET['ctl'] );
        }
    }

    function finder_pager( )
    {
        $this->pagedata['_finder'] = $_POST['_finder'];
        $this->pagedata['_finder']['params'] = $_POST;
        unset( $this->params->'_finder' );
        $this->pagedata['_finder']['var'] = "window.finderGroup['".$this->pagedata['_finder']['_name']."']";
        $this->display( "finder/lister.html#".$_GET['ctl'] );
    }

    function recycleindex( $options = null )
    {
        $o =& $this->model;
        $o->disabledMark = "recycle";
        $this->index( array( "disabled" => "true" ) );
    }

    function prefilter( $type )
    {
        $this->pagedata['type'] = $type;
        $this->pagedata['filter'] = $_POST['data'];
        $this->pagedata['_finder']['select'] = "none";
        $this->pagedata['options'] = $_POST;
        $this->display( "finder/pvfilter.html" );
    }

    function export( )
    {
        $addons =& $this->system->loadmodel( "system/addons" );
        foreach ( $addons->getlist( "plugin_name,plugin_struct,plugin_ident", array( "plugin_type" => "io" ) ) as $exporter )
        {
            $struct = unserialize( $exporter['plugin_struct'] );
            if ( isset( $struct['funcs']['export_rows'] ) )
            {
                if ( $struct['props']['exportforObjects'] )
                {
                    $a = array_flip( explode( ",", $struct['props']['exportforObjects'] ) );
                    if ( isset( $a[$this->ioType] ) )
                    {
                        $this->pagedata['exporter'][] = $exporter;
                    }
                }
                else
                {
                    $this->pagedata['exporter'][] = $exporter;
                }
            }
        }
        $this->display( "finder/export.html" );
    }

    function delete( )
    {
        if ( $this->model->delete( $_POST ) )
        {
            echo __( "选定记录已删除成功!" );
        }
        else
        {
            echo __( "选定记录无法删除!" );
        }
    }

    function recycle( )
    {
        if ( $this->model->recycle( $_POST ) )
        {
            echo __( "选定记录删入回收站" );
        }
        else
        {
            echo __( "选定记录无法删入回收站" );
        }
    }

    function active( )
    {
        if ( $this->model->active( $_POST ) )
        {
            echo __( "选定记录已从回收站恢复" );
        }
        else
        {
            echo __( "选定记录无法从回收站恢复" );
        }
    }

    function savevalue( )
    {
        foreach ( $GLOBALS['_POST']['items'] as $id => $item )
        {
            foreach ( $item as $k => $v )
            {
                $item[$k] = $this->model->columnvalue( $k, $v );
            }
            $this->model->update( $item, array(
                $this->model->idColumn => $id
            ) );
        }
    }

    function save( )
    {
        $this->begin( "index.php?ctl=".$_GET['ctl']."&act=index" );
        if ( $_POST[$this->model->idColumn] )
        {
            $this->end( $this->model->update( $_POST, array(
                $this->model->idColumn => $_POST[$this->model->idColumn]
            ) ) );
        }
        else
        {
            $this->end( $this->model->insert( $_POST ) );
        }
    }

}

?>
