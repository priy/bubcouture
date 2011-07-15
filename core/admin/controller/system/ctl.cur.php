<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class ctl_cur extends adminPage
{

    public $workground = "setting";

    public function index( )
    {
        $this->path[] = array(
            "text" => __( "货币列表" )
        );
        $ocur =& $this->system->loadModel( "system/cur" );
        $this->pagedata['currency'] = $ocur->curAll( );
        $this->page( "system/cur/cur.html" );
    }

    public function curDetail( $id )
    {
        $this->path[] = array(
            "text" => __( "货币内容页" )
        );
        $ocur =& $this->system->loadModel( "system/cur" );
        if ( $id )
        {
            $data = $ocur->getcur( $id );
        }
        $aCur = $ocur->getSysCur( );
        $data['selcur'] = array_merge( array(
            "" => __( "请选择" )
        ), $aCur );
        $aCur = $ocur->curAll( );
        if ( $aCur )
        {
            foreach ( $aCur as $item )
            {
                if ( $item['cur_code'] != $id )
                {
                    unset( $this->selcur[$item['cur_code']] );
                }
            }
        }
        if ( count( $data['selcur'] ) == 1 )
        {
            $this->begin( "index.php?ctl=system/cur&act=index" );
            $this->end( FALSE, __( "系统货币已经全部添加完毕" ) );
        }
        $data['defopt'] = array(
            "true" => __( "是" ),
            "false" => __( "否" )
        );
        if ( !$data['def_cur'] )
        {
            $data['def_cur'] = "false";
        }
        $this->pagedata['currency'] = $data;
        $this->page( "system/cur/curDetail.html" );
    }

    public function curDel( $id )
    {
        $this->begin( "index.php?ctl=system/cur&act=index" );
        $o =& $this->system->loadModel( "system/cur" );
        $this->end( $o->curDel( $id ), __( "删除成功" ) );
    }

    public function curAdd( )
    {
        $this->begin( "index.php?ctl=system/cur&act=index" );
        $ocur =& $this->system->loadModel( "system/cur" );
        $data['cur_code'] = $_POST['cur_code'];
        $data['cur_sign'] = $_POST['cur_sign'];
        $data['cur_rate'] = $_POST['cur_rate'];
        $data['def_cur'] = $_POST['def_cur'];
        $data['cur_name'] = $_POST['cur_name'];
        if ( $data['def_cur'] == "true" )
        {
            $data['cur_rate'] = 1;
        }
        if ( $ocur->curAdd( $data ) )
        {
            $this->end( TRUE, __( "货币新增成功" ) );
        }
        else
        {
            $this->end( FALSE, __( "货币新增失败" ) );
        }
    }

    public function curEdit( )
    {
        $this->begin( "index.php?ctl=system/cur&act=index" );
        $ocur =& $this->system->loadModel( "system/cur" );
        $data['cur_code'] = $_POST['cur_code'];
        $data['cur_sign'] = $_POST['cur_sign'];
        $data['cur_rate'] = $_POST['cur_rate'];
        $data['def_cur'] = $_POST['def_cur'];
        $data['cur_name'] = $_POST['cur_name'];
        $this->end( $ocur->curEdit( $_POST['cur_code'], $data, $_POST['old_cur_code'] ), __( "货币保存成功" ) );
    }

}

?>
