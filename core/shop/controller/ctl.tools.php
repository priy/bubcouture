<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class ctl_tools extends shopPage
{

    public $noCache = TRUE;

    public function setcur( )
    {
        if ( isset( $this->member ) )
        {
            $oMem =& $this->system->loadModel( "member/member" );
            $oMem->save( $this->member['member_id'], array(
                "cur" => $_POST['cur']
            ) );
            header( "Location:".$_SERVER['HTTP_REFERER'] );
        }
        else
        {
            setcookie( "S[CUR]", $_POST['cur'] );
            header( "Location:".$_SERVER['HTTP_REFERER'] );
        }
    }

    public function ver( )
    {
        header( "Content-type: text/plain" );
        foreach ( $this->system->version( ) as $k => $v )
        {
            $out .= $k."=".$v."\n";
        }
        echo $out;
        $this->system->_succ = TRUE;
        exit( );
    }

    public function checkSession( $sess_id )
    {
        $this->certi_model =& $this->system->loadModel( "service/certificate" );
        $result = $this->certi_model->getSess( $sess_id );
        if ( $result )
        {
            echo json_encode( array( "res" => "succ", "rsp" => "succ" ) );
        }
        else
        {
            echo json_encode( array( "res" => "fail", "rsp" => "fail" ) );
        }
    }

    public function code( )
    {
        $code =& $this->system->loadModel( "utility/vcode" );
        $code->init( );
    }

    public function history( )
    {
        $this->title = __( "浏览过的商品" );
        $this->output( );
    }

    public function products( )
    {
        $objGoods =& $this->system->loadModel( "goods/products" );
        $filter = array( );
        foreach ( explode( ",", $_POST['goods'] ) as $gid )
        {
            $filter['goods_id'][] = $gid;
        }
        $this->pagedata['products'] = $objGoods->getList( $objGoods->defaultCols.",find_in_set(goods_id,\"".$_POST['goods']."\") as rank", $filter, 0, -1, array( "rank", "asc" ) );
        $view = $this->system->getConf( "gallery.default_view" );
        if ( $view == "index" )
        {
            $view = "list";
        }
        $this->__tmpl = "gallery/type/".$view.".html";
        $this->output( );
    }

    public function sel_region( $path, $depth )
    {
        header( "Content-type: text/html;charset=utf8" );
        $local =& $this->system->loadModel( "system/local" );
        if ( $ret = $local->get_area_select( $path ) )
        {
            echo "&nbsp;-&nbsp;".$local->get_area_select( $path, array(
                "depth" => $depth
            ) );
        }
        else
        {
            echo "";
        }
    }

}

?>
