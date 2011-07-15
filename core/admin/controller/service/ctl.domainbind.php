<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class ctl_domainbind extends adminPage
{

    public $workground = "setting";

    public function index( )
    {
        $this->path[] = array(
            "text" => __( "域名绑定" )
        );
        $sass =& $this->system->loadModel( "service/saas" );
        $result = $sass->get_info( );
        $i = 0;
        if ( is_array( $result ) )
        {
        default :
            switch ( $v )
            {
                foreach ( $result['alias'] as $k => $v )
                {
                case "wait" :
                    $nowresult[$i]['alias'] = $k;
                    $nowresult[$i]['status'] = __( "待审核" );
                    ++$i;
                    break;
                case "verify" :
                    $nowresult[$i]['alias'] = $k;
                    $nowresult[$i]['status'] = __( "已生效" );
                    ++$i;
                    break;
                case "false" :
                }
                $nowresult[$i]['alias'] = $k;
                $nowresult[$i]['status'] = __( "未通过验证" );
                ++$i;
                break;
            }
            $this->pagedata['host_name'] = $result['host_name'];
            $this->pagedata['result1'] = $nowresult[0];
            $this->pagedata['result2'] = $nowresult[1];
        }
        else
        {
            $this->pagedata['err_msg'] = $result;
        }
        $this->page( "service/domainbind/index.html" );
        $this->output( );
    }

    public function canceldomainBinding( $alias )
    {
        $this->begin( "index.php?ctl=service/domainbind&act=index" );
        $sass =& $this->system->loadModel( "service/saas" );
        $message = $sass->del_alias( $alias );
        $this->pagedata['message'] = $message;
        if ( $message === TRUE )
        {
            $this->end( TRUE, __( "取消绑定成功" ) );
            exit( );
        }
        $this->end( FALSE, $message );
    }

    public function adddomainBinding( $message = "" )
    {
        if ( $message != "" )
        {
            $this->pagedata['url'] = $message;
        }
        $this->display( "service/domainbind/add.html" );
    }

    public function result( )
    {
        $sass =& $this->system->loadModel( "service/saas" );
        if ( !preg_match( "/\\//", $_POST['domain'] ) && $_POST['domain'] != "" )
        {
            $result = $sass->add_alias( $_POST['domain'] );
            if ( $_POST['edit'] == "true" )
            {
                $result = $sass->retry_alias( $_POST['domain'] );
            }
            if ( $result === TRUE )
            {
                $this->pagedata['result'] = "true";
            }
            else
            {
                $this->pagedata['result'] = "false";
                $this->pagedata['msg'] = $result;
            }
        }
        else
        {
            $this->pagedata['result'] = "false";
            $this->pagedata['msg'] = __( "对不起你的域名格式非法" );
        }
        $this->display( "service/domainbind/result.html" );
    }

}

?>
