<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

define( "PAY_FAILED", -1 );
define( "PAY_TIMEOUT", 0 );
define( "PAY_SUCCESS", 1 );
define( "PAY_CANCEL", 2 );
define( "PAY_ERROR", 3 );
define( "PAY_PROGRESS", 4 );
define( "PAY_INVALID", 5 );
define( "PAY_MANUAL", 0 );
require_once( "shopObject.php" );
class mdl_payment extends shopobject
{

    var $M_OrderId;
    var $M_OrderNO;
    var $M_Amount;
    var $M_Def_Amount;
    var $M_Currency;
    var $M_Remark;
    var $M_Time;
    var $M_Language;
    var $R_Name;
    var $R_Address;
    var $R_Postcode;
    var $R_Telephone;
    var $R_Mobile;
    var $R_Email;
    var $P_Name;
    var $P_Address;
    var $P_PostCode;
    var $P_Telephone;
    var $P_Mobile;
    var $P_Email;
    var $adminCtl = "order/payment";
    var $idColumn = "payment_id";
    var $textColumn = "payment_id";
    var $defaultCols = "payment_id,money,currency,order_id,paymethod,account,bank,status,t_end";
    var $defaultOrder = array
    (
        0 => "payment_id",
        1 => "DESC"
    );
    var $tableName = "sdb_payments";
    var $plugin_case = LOWER_CASE;

    function getcolumns( )
    {
        $ret = shopobject::getcolumns( );
        $ret['pay_type']['default'] = "";
        $ret['status']['default'] = "";
        return $ret;
    }

    function getfilter( $p )
    {
        $return['payment'] = $this->getmethods( );
        return $return;
    }

    function edit( $aDetail )
    {
        $rPayment = $this->db->query( "select * from sdb_payments where payment_id=".$aDetail['payment_id'] );
        unset( $aDetail->'payment_id' );
        $sSql = $this->db->getupdatesql( $rPayment, $aDetail );
        return !$sSql || $this->db->exec( $sSql );
    }

    function getorderbilllist( $orderid )
    {
        return $this->db->select( "SELECT * FROM sdb_payments WHERE order_id = ".$orderid );
    }

    function getmethods( $type = "" )
    {
        if ( $type == "online" )
        {
            $sql = " AND pay_type NOT IN('OFFLINE','DEPOSIT')";
        }
        return $this->db->select( "SELECT * FROM sdb_payment_cfg WHERE disabled = 'false'".$sql." order by orderlist desc", PAGELIMIT );
    }

    function getallmethods( $type = "" )
    {
        return $this->db->select( "SELECT * FROM sdb_payment_cfg  order by orderlist desc", PAGELIMIT );
    }

    function loadmethod( $payPlugin )
    {
        if ( file_exists( PLUGIN_DIR."/app/pay_".$payPlugin."/pay_".$payPlugin.".php" ) )
        {
            require_once( PLUGIN_DIR."/app/pay_".$payPlugin."/pay_".$payPlugin.".php" );
            $className = "pay_".$payPlugin;
            $method = new $className( $this->system );
            return $method;
        }
    }

    function searchoptions( )
    {
        $arr = shopobject::searchoptions( );
        return array_merge( $arr, array(
            "uname" => __( "会员用户名" ),
            "username" => __( "操作员" )
        ) );
    }

    function _filter( $filter )
    {
        $where = array( 1 );
        if ( !empty( $filter['payment_id'] ) )
        {
            if ( is_array( $filter['payment_id'] ) )
            {
                if ( $filter['payment_id'][0] != "_ALL_" )
                {
                    if ( !isset( $filter['payment_id'][1] ) )
                    {
                        $where[] = "payment_id = ".$this->db->quote( $filter['payment_id'][0] )."";
                    }
                    else
                    {
                        $aOrder = array( );
                        foreach ( $filter['payment_id'] as $payment_id )
                        {
                            $aOrder[] = "payment_id=".$this->db->quote( $payment_id )."";
                        }
                        $where[] = "(".implode( " OR ", $aOrder ).")";
                        unset( $aOrder );
                    }
                }
            }
            else
            {
                $where[] = "payment_id = ".$this->db->quote( $filter['payment_id'] )."";
            }
            unset( $filter->'payment_id' );
        }
        if ( array_key_exists( "uname", $filter ) && trim( $filter['uname'] ) != "" )
        {
            $user_data = $this->db->select( "select member_id from sdb_members where uname = '".addslashes( $filter['uname'] )."'" );
            foreach ( $user_data as $tmp_user )
            {
                $now_user[] = $tmp_user['member_id'];
            }
            $where[] = "member_id IN ('".implode( "','", $now_user )."')";
            unset( $filter->'uname' );
        }
        else if ( isset( $filter['uname'] ) )
        {
            unset( $filter->'uname' );
        }
        if ( isset( $filter['username'] ) && trim( $filter['username'] ) )
        {
            $op_data = $this->db->select( "select op_id from sdb_operators where username = '".addslashes( $filter['username'] )."'" );
            foreach ( $op_data as $tmp_op )
            {
                $now_op[] = $tmp_op['op_id'];
            }
            $where[] = "op_id IN ('".implode( "','", $now_op )."')";
            unset( $filter->'username' );
        }
        else if ( isset( $filter['username'] ) )
        {
            unset( $filter->'username' );
        }
        return shopobject::_filter( $filter )." and ".implode( " AND ", $where );
    }

    function getplugins( )
    {
        $dir = PLUGIN_DIR."/app/";
        $appmgr =& $this->system->loadmodel( "system/appmgr" );
        $disabled = 0;
        if ( file_exists( $dir."disabled_payments.txt" ) )
        {
            $disabledPayment = file( $dir."disabled_payments.txt" );
            if ( 0 < count( $disabledPayment ) )
            {
                foreach ( $disabledPayment as $k => $v )
                {
                    $disabledPayment[$k] = trim( $v );
                }
                $disabled = 1;
            }
        }
        if ( $handle = opendir( $dir ) )
        {
            $i = 50000;
            while ( false !== ( $app = readdir( $handle ) ) )
            {
                if ( !is_dir( $dir.$app ) && !( substr( $app, 0, 4 ) == "pay_" ) )
                {
                    continue;
                }
                $startApp = $appmgr->getappname( $app );
                while ( !( $handles = opendir( $dir.$startApp ) ) && !( false !== ( $file = readdir( $handles ) ) ) )
                {
                    if ( !is_file( $dir.$app."/".$file ) && !( substr( $file, 0, 4 ) == "pay_" ) )
                    {
                        $payName = substr( $file, 4, -4 );
                        if ( $payName == strtolower( $payName ) )
                        {
                            include_once( $dir.$app."/".$file );
                            $class_vars = "pay_".$payName;
                            if ( class_exists( $class_vars ) )
                            {
                                $o = new $class_vars( );
                            }
                            $class_vars = get_object_vars( $o );
                            unset( $class_vars->'system' );
                            $key = $class_vars['orderby'] ? $class_vars['orderby'] : $i;
                            if ( $disabled )
                            {
                                if ( !in_array( trim( $payName ), $disabledPayment ) )
                                {
                                    $return[$key] = $class_vars;
                                    $return[$key]['payment_id'] = $payName;
                                }
                            }
                            else
                            {
                                $return[$key] = $class_vars;
                                $return[$key]['payment_id'] = $payName;
                            }
                            ++$i;
                        }
                    }
                }
            }
            closedir( $handle );
        }
        ksort( $return );
        reset( $return );
        return $return;
    }

    function getsupportcur( &$oPayType )
    {
        if ( !is_object( $oPayType ) )
        {
            return false;
        }
        return $oPayType->supportCurrency;
    }

    function getbycur( $cur = -1, $type = "" )
    {
        if ( $cur == -1 || empty( $cur ) )
        {
            $defaultMark = 1;
            $cur = -1;
        }
        else
        {
            $oCur =& $this->system->loadmodel( "system/cur" );
            $aCur = $oCur->getcur( $cur, true );
            if ( $aCur['def_cur'] == "true" )
            {
                $defaultMark = 1;
            }
            else
            {
                $defaultMark = 0;
            }
        }
        if ( $type == "online" )
        {
            $sql = " AND pay_type NOT IN('OFFLINE','DEPOSIT')";
        }
        $rows = $this->db->select( "SELECT * FROM sdb_payment_cfg WHERE disabled = 'false'".$sql." ORDER BY orderlist desc" );
        foreach ( $rows as $k => $row )
        {
            $dir = PLUGIN_DIR."/app/pay_".$row['pay_type']."/";
            if ( is_file( $dir."pay_".$row['pay_type'].".php" ) )
            {
                include_once( $dir."pay_".$row['pay_type'].".php" );
                $class_name = "pay_".$row['pay_type'];
                $o = new $class_name( );
                $pInfo = get_object_vars( $o );
                unset( $pInfo->'system' );
                if ( $cur != -1 && is_array( $pInfo['supportCurrency'] ) )
                {
                    $sptCur = array( );
                    foreach ( $pInfo['supportCurrency'] as $s_cur => $s )
                    {
                        $sptCur[strtolower( $s_cur )] = 1;
                    }
                    if ( !isset( $sptCur[strtolower( $cur )] ) || !isset( $sptCur['all'] ) )
                    {
                        if ( $defaultMark && isset( $sptCur['default'] ) )
                        {
                            unset( $rows->$k );
                        }
                    }
                }
                else
                {
                    $rows[$k] = array_merge( $rows[$k], $pInfo );
                    $rows[$k]['custom_name'] = $rows[$k]['custom_name'] ? $rows[$k]['custom_name'] : $rows[$k]['name'];
                    ++$i;
                }
            }
            else
            {
                unset( $rows->$k );
            }
        }
        return $rows;
    }

    function getpluginsarr( $strKey = false )
    {
        $aTemp = $aPlugin = array( );
        $aTemp = $this->getplugins( );
        if ( $aTemp )
        {
            if ( !$strKey )
            {
                foreach ( $aTemp as $val )
                {
                    $aPlugin[] = array(
                        "pid" => $val['payment_id'],
                        "name" => $val['name'],
                        "cur" => $val['supportCurrency']
                    );
                }
                return $aPlugin;
            }
            foreach ( $aTemp as $val )
            {
                $aPlugin[] = array(
                    $val['payment_id'],
                    $val['name']
                );
            }
        }
        return $aPlugin;
    }

    function gen_id( )
    {
        $i = rand( 0, 9999 );
        do
        {
            if ( 9999 == $i )
            {
                $i = 0;
            }
            ++$i;
            $payment_id = time( ).str_pad( $i, 4, "0", STR_PAD_LEFT );
            $row = $this->db->selectrow( "select payment_id from sdb_payments where payment_id ='".$payment_id."'" );
        } while ( $row );
        return $payment_id;
    }

    function tocreate( )
    {
        $this->payment_id = $this->gen_id( );
        $this->t_begin = time( );
        $this->t_end = time( );
        $this->ip = remote_addr( );
        if ( !$this->cur_trading || $this->currency != "CNY" )
        {
            $this->cur_money = $this->money;
        }
        $oCur =& $this->system->loadmodel( "system/cur" );
        if ( $payCfg = $this->db->selectrow( "SELECT pay_type,fee,custom_name FROM sdb_payment_cfg WHERE id=".intval( $this->payment ) ) )
        {
            $this->paycost = $oCur->formatnumber( $this->paycost, false );
            $this->paymethod = $payCfg['custom_name'];
        }
        $aRs = $this->db->query( "SELECT * FROM sdb_payments WHERE 0=1" );
        $sSql = $this->db->getinsertsql( $aRs, $this );
        if ( $this->db->exec( $sSql ) )
        {
            return $this->payment_id;
        }
        return false;
    }

    function getbyid( $paymentId )
    {
        $aTemp = $this->db->selectrow( "SELECT * FROM sdb_payments WHERE payment_id='".$paymentId."'" );
        if ( $aTemp['payment_id'] )
        {
            return $aTemp;
        }
        return false;
    }

    function setpaystatus( $paymentId, $status, &$payInfo )
    {
        if ( !$paymentId )
        {
            $this->seterror( 10001 );
            trigger_error( __( "单据号传递出错" ), E_USER_ERROR );
            return false;
        }
        $aPayInfo = $this->getbyid( $paymentId );
        if ( !$aPayInfo )
        {
            $this->seterror( 10001 );
            trigger_error( __( "支付记录不存在，可能参数传递出错" ), E_USER_ERROR );
            return false;
        }
        if ( $aPayInfo['status'] == "succ" )
        {
            return true;
        }
        if ( $aPayInfo['status'] == "progress" && $status == PAY_PROGRESS )
        {
            return true;
        }
        if ( $aPayInfo['pay_type'] == "recharge" && $aPayInfo['bank'] == "deposit" )
        {
            $payInfo['memo'] .= __( "#不能用预存款支付来充值预存款！" );
            $status = PAY_FAILED;
        }
        if ( $payInfo['cur_money'] && $aPayInfo['cur_money'] != $payInfo['money'] )
        {
            $status = PAY_ERROR;
            $payInfo['memo'] .= __( "#实际支付金额与支付单中的金额不一致！" );
        }
        switch ( $status )
        {
        case PAY_IGNORE :
            return false;
        case PAY_FAILED :
            $payInfo['status'] = "failed";
            break;
        case PAY_TIMEOUT :
            $payInfo['status'] = "timeout";
            break;
        case PAY_PROGRESS :
            $aPayInfo['pay_assure'] = true;
            $aPayInfo['pay_progress'] = "PAY_PROGRESS";
            $payInfo['status'] = "progress";
            break;
        case PAY_SUCCESS :
            $payInfo['status'] = "succ";
            break;
        case PAY_CANCEL :
            $payInfo['status'] = "cancel";
            break;
        case PAY_ERROR :
            $payInfo['status'] = "error";
            break;
        case PAY_REFUND_SUCCESS :
            $Rs = $this->db->selectrow( "select order_id from sdb_payments where payment_id='".$paymentId."'" );
            if ( $Rs )
            {
                $GLOBALS['_POST']['order_id'] = $Rs['order_id'];
                if ( $this->op->opid )
                {
                    $GLOBALS['_POST']['opid'] = $this->op->opid;
                    $GLOBALS['_POST']['opname'] = $this->op->loginName;
                }
                else
                {
                    $opeRs = $this->db->selectrow( "select op_id,username from sdb_operators where status=1 and super=1" );
                    $GLOBALS['_POST']['opid'] = $opeRs['op_id'];
                    $GLOBALS['_POST']['opname'] = $opeRs['username'];
                }
                $order = $this->system->loadmodel( "trading/order" );
                if ( $order->refund( $_POST ) )
                {
                    $this->seterror( 10001 );
                    return true;
                }
                $this->seterror( 10002 );
                return false;
            }
            return false;
        }
        $payInfo['t_end'] = time( );
        $aRs = $this->db->query( "SELECT * FROM sdb_payments WHERE payment_id='".$paymentId."' AND status!='succ'" );
        $sSql = $this->db->getupdatesql( $aRs, $payInfo );
        if ( ( !$sSql && $this->db->exec( $sSql ) ) && $this->db->affect_row( ) == 1 )
        {
            if ( ( $status == PAY_PROGRESS || $status == PAY_SUCCESS ) && !$this->onsuccess( $aPayInfo, $payInfo['memo'] ) )
            {
                return false;
            }
            return true;
        }
        return false;
    }

    function onsuccess( $info, &$message )
    {
        if ( $info['pay_type'] == "recharge" )
        {
            $oCur =& $this->system->loadmodel( "system/cur" );
            $aCur = $oCur->getcur( $info['currency'] );
            $info['money'] = $info['money'] - $info['paycost'];
            if ( $aCur['def_cur'] == "false" )
            {
                $info['money'] /= $aCur['cur_rate'];
            }
            $info['money'] = $oCur->formatnumber( $info['money'], false );
            $message .= "预存款充值：支付单号{".$info['payment_id']."}";
            $advance = $this->system->loadmodel( "member/advance" );
            if ( !$info['pay_assure'] )
            {
                return $advance->add( $info['member_id'], $info['money'], $message, $message, $info['payment_id'], "", $info['paymethod'], "在线充值" );
            }
            return true;
        }
        $order =& $this->system->loadmodel( "trading/order" );
        return $order->payed( $info, $message );
    }

    function pay_install( $ident, $url, $is_update = false )
    {
        if ( !$url )
        {
            $url = "http://app.shopex.cn/appdatas/payments/".$ident.".tar";
        }
        include( CORE_DIR."/admin/controller/service/ctl.download.php" );
        $download = new ctl_download( );
        if ( !class_exists( "ctl_payment" ) )
        {
            include( CORE_DIR."/admin/controller/trading/ctl.payment.php" );
        }
        $payment = new ctl_payment( );
        $GLOBALS['_POST'] = array(
            "download_list" => array(
                $url
            ),
            "succ_url" => "http://".$_SERVER['HTTP_HOST'].dirname( $_SERVER['PHP_SELF'] )."/index.php?ctl=trading/payment&act=do_install_online"
        );
        $download->set = "true";
        $download->start( );
        if ( $is_update )
        {
            $ident = date( "Ymd" ).substr( md5( time( ).rand( 0, 9999 ) ), 0, 5 );
            $download->run( $download->ident, 0 );
            $GLOBALS['_GET']['download'] = $download->ident;
            $payment->do_install_online( );
        }
    }

    function progress( $paymentId, $status, $info )
    {
        $sendPay['payment'] = $paymentId;
        $sendPay['amount'] = $info['money'];
        $sendPay['order_id'] = $info['trade_no'];
        $sendPay['pay_status'] = $status;
        $system =& $system;
        $base_url = $system->base_url( );
        $base_url = substr( substr( $base_url, 0, strrpos( $base_url, "/" ) ), 0, strrpos( substr( $base_url, 0, strrpos( $base_url, "/" ) ), "/" ) )."/";
        $url = $system->realurl( "paycenter", $act = "result", "", "html", $base_url );
        $payStatus = $this->setpaystatus( $paymentId, $status, $info );
        $html = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"\n       \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n       <html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en-US\" lang=\"en-US\" dir=\"ltr\">\n       <head></header><body>Redirecting...";
        $html .= "<form id=\"payment\" action=\"".$url."\" method=\"post\"><input type=\"hidden\" name=\"payment_id\" value=\"".$paymentId."\">";
        $html .= "      </form>\n      <script language=\"javascript\">\n      document.getElementById('payment').submit();\n      </script>\n    </html>";
        echo $html;
    }

    function addqueue( $sender, $target, $title, $data, $tmpl_name, $level = 5, $event_name = "" )
    {
        $sqlData = array(
            "tmpl_name" => $tmpl_name,
            "level" => $level,
            "event_name" => $event_name,
            "title" => $title,
            "target" => $target,
            "sender" => $sender,
            "data" => $data
        );
        $rs = $this->db->exec( "select * from sdb_msgqueue where 0=1" );
        $sql = $this->db->getinsertsql( $rs, $sqlData );
        $this->db->exec( $sql );
    }

    function getaccount( )
    {
        $query = "SELECT DISTINCT bank, account FROM sdb_payments WHERE status=\"succ\"";
        return $this->db->select( $query );
    }

    function refund( $nStart, $nLimit, $aParame )
    {
        if ( !$limit )
        {
            $limit = 20;
        }
        foreach ( $aParame as $k => $v )
        {
            if ( $k == "t_begin" && $v != "" )
            {
                $sTmp .= " and ".$k.">=\"".$v."\"";
            }
            else if ( $k == "t_end" && $v != "" )
            {
                $sTmp .= " and ".$k."<=\"".$v."\"";
            }
            else if ( $v != "" )
            {
                $sTmp .= " and ".$k."=\"".$v."\"";
            }
        }
        $aData = $this->db->selectrow( "select count(*) as total from sdb_payments p,sdb_members m where p.member_id=m.member_id and type=\"orderrefund\"".$sTmp );
        $aData['main'] = $this->db->selectlimit( "select p.*,m.name as m_name from sdb_payments p,sdb_members m where p.member_id=m.member_id and type=\"orderrefund\"".$sTmp, intval( $nLimit ), intval( $nStart ), false, true );
        return $aData;
    }

    function getpaymentbyid( $id )
    {
        return $this->db->selectrow( "SELECT * FROM sdb_payment_cfg WHERE id=".intval( $id ) );
    }

    function insertpay( $aData, &$msg )
    {
        if ( $aData['pay_type'] )
        {
            $obj = $this->loadmethod( $aData['pay_type'] );
            if ( $obj )
            {
                $aField = $obj->getfields( );
                $aTemp = array( );
                foreach ( $aField as $key => $val )
                {
                    $aTemp[$key] = trim( $aData[$key] );
                    if ( $val['extendcontent'] )
                    {
                        foreach ( $val['extendcontent'] as $k => $v )
                        {
                            $aTemp[$v['property']['name']] = $aData[$v['property']['name']];
                        }
                    }
                }
                $aTemp['method'] = $aData['paymethod'];
                if ( $aData['paymethod'] == "2" )
                {
                    $aTemp['fee'] = $aData['fee'];
                    unset( $aData->'fee' );
                }
                $aData['config'] = serialize( $aTemp );
            }
            $aRs = $this->db->query( "SELECT * FROM sdb_payment_cfg WHERE 0" );
            $sSql = $this->db->getinsertsql( $aRs, $aData );
            if ( !$sSql && $this->db->exec( $sSql ) )
            {
                $msg = __( "保存成功！" );
                return true;
            }
            $msg = __( "数据库操作失败！" );
            return false;
        }
        $msg = __( "参数丢失，请选择支付类型！" );
        return false;
    }

    function insertpaymentapp( $aData )
    {
        $aRs = $this->db->query( "SELECT * FROM sdb_payment_cfg WHERE 0" );
        $sSql = $this->db->getinsertsql( $aRs, $aData );
        return $this->db->exec( $sSql );
    }

    function updatepay( $aData, &$msg )
    {
        $appmgr = $this->system->loadmodel( "system/appmgr" );
        $app_model = $appmgr->load( "pay_".$aData['pay_type'] );
        if ( method_exists( $app_model, "pay_other_operation" ) )
        {
            $center_return = $app_model->pay_other_operation( $aData );
            if ( !$center_return )
            {
                return false;
            }
        }
        $obj = $this->loadmethod( $aData['pay_type'] );
        if ( $obj )
        {
            $aField = $obj->getfields( );
            $aTemp = array( );
            $d = $this->db->selectrow( "SELECT * FROM sdb_payment_cfg WHERE id =\"".$aData['id']."\"" );
            if ( is_array( $d ) )
            {
                $d_config = unserialize( $d['config'] );
            }
            foreach ( $aField as $key => $val )
            {
                if ( $aData[$key] != "" )
                {
                    if ( strstr( strtolower( $key ), "file" ) && !$aData[$key] || $d_config[$key] )
                    {
                        $aTemp[$key] = trim( $d_config[$key] );
                    }
                    else if ( isset( $aData[$key] ) )
                    {
                        if ( $aData['pay_type'] == "chinapay" && ( $key == "MerPrk" || $key == "PubPk" ) )
                        {
                            if ( $pos = strpos( $aData[$key], "." ) )
                            {
                                $suffix = substr( $aData[$key], $pos );
                                $max_len = 7 - strlen( $suffix );
                                if ( $max_len < strlen( substr( $aData[$key], 0, $pos ) ) )
                                {
                                    $aData[$key] = substr( $aData[$key], 0, $max_len ).$suffix;
                                }
                            }
                            else if ( 7 < strlen( $aData[$key] ) )
                            {
                                $aData[$key] = substr( $aData[$key], 0, 7 );
                            }
                        }
                        $aTemp[$key] = trim( $aData[$key] );
                    }
                    else
                    {
                        $aTemp[$key] = trim( $d_config[$key] );
                    }
                }
                else
                {
                    $aTemp[$key] = trim( $d_config[$key] );
                }
                if ( $val['extendcontent'] )
                {
                    foreach ( $val['extendcontent'] as $k => $v )
                    {
                        if ( $aData[$v['property']['name']] )
                        {
                            $aTemp[$v['property']['name']] = $aData[$v['property']['name']];
                        }
                        else
                        {
                            $aTemp[$v['property']['name']] = $dt_config[$v['property']['name']];
                        }
                    }
                }
            }
            $aTemp['method'] = $aData['paymethod'];
            if ( $aData['paymethod'] == 2 )
            {
                $aTemp['fee'] = $aData['fee'];
                unset( $aData->'fee' );
            }
            else
            {
                $aTemp['fee'] = $d_config['fee'];
            }
            unset( $aData->'paymethod' );
            $aData['config'] = serialize( $aTemp );
        }
        if ( is_array( $d ) )
        {
            $aRs = $this->db->query( "SELECT * FROM sdb_payment_cfg WHERE id=\"".$aData['id']."\"" );
            $sSql = $this->db->getupdatesql( $aRs, $aData );
        }
        else
        {
            $aRs = $this->db->query( "SELECT * FROM sdb_payment_cfg WHERE 0" );
            $sSql = $this->db->getinsertsql( $aRs, $aData );
        }
        return !$sSql || $this->db->exec( $sSql );
    }

    function deletepay( $sId = null )
    {
        if ( $sId )
        {
            $sSql = "DELETE FROM sdb_payment_cfg WHERE id in (".$sId.")";
            return !$sSql || $this->db->exec( $sSql );
        }
        return false;
    }

    function getpaymentinfo( $method = "" )
    {
        $o =& $this->system->loadmodel( "trading/order" );
        $m =& $this->system->loadmodel( "member/member" );
        $order = $o->instance( $this->order_id );
        $member = $m->instance( $order['member_id'] );
        $payment['M_OrderId'] = $this->payment_id;
        $payment['M_OrderNO'] = $method == "recharge" ? $this->payment_id : $this->order_id;
        $payment['M_Amount'] = $this->money;
        $payment['M_Def_Amount'] = $this->money;
        $payment['M_Currency'] = $this->currency;
        $payment['M_Remark'] = $order['memo'];
        $payment['M_Time'] = $this->t_begin;
        $payment['M_Goods'] = $order['tostr'];
        $payment['M_Language'] = "zh_CN";
        $payment['R_Name'] = $order['ship_name'];
        $payment['R_Address'] = $order['ship_addr'];
        $payment['R_Postcode'] = $order['ship_zip'];
        $payment['R_Telephone'] = $order['ship_tel'];
        $payment['R_Mobile'] = $order['ship_mobile'];
        $payment['R_Email'] = $order['ship_email'];
        $payment['P_Name'] = $member['name'];
        $payment['P_Address'] = $member['addr'];
        $payment['P_PostCode'] = $member['zip'];
        $payment['P_Telephone'] = $member['tel'];
        $payment['P_Mobile'] = $member['mobile'];
        $payment['P_Email'] = $member['email'];
        $payment['K_key'] = $this->system->getconf( "certificate.token" );
        $payment['payExtend'] = unserialize( $order['extend'] );
        $payment['M_Method'] = $method;
        if ( $this->pay_type == "recharge" )
        {
            $member = $m->instance( $this->member_id );
            $payment['R_Name'] = $member['name'] ? $member['name'] : $member['uname'];
            $payment['R_Telephone'] = $member['mobile'] ? $member['mobile'] : $member['tel'] ? $member['tel'] : "13888888888";
        }
        $configinfo = $this->getpaymentbyid( $order['payment'] );
        $pma = $this->getpaymentfilename( $configinfo['config'], $configinfo['pay_type'] );
        if ( is_array( $pma ) )
        {
            foreach ( $pma as $key => $val )
            {
                $payment[$key] = $val;
            }
        }
        return $payment;
    }

    function disapp( $id )
    {
        $sql = "update sdb_payment_cfg  set disabled='true' where id = ".$id."";
        return $this->db->exec( $sql );
    }

    function startapp( $id )
    {
        $sql = "update sdb_payment_cfg  set disabled='false' where id = ".$id."";
        return $this->db->exec( $sql );
    }

    function dopay( $method = "", $order_id )
    {
        $gOrder =& $this->system->loadmodel( "trading/order" );
        if ( $gOrder->freez_time( ) == "pay" )
        {
            $objCart =& $this->system->loadmodel( "trading/cart" );
            $objGift =& $this->system->loadmodel( "trading/gift" );
            if ( isset( $order_id ) )
            {
                $rs = $this->db->select( "SELECT product_id,nums,name  FROM sdb_order_items  WHERE order_id = ".$order_id." " );
                $rsG = $this->db->select( "SELECT gift_id,nums  FROM sdb_gift_items   WHERE order_id = ".$order_id." " );
                foreach ( $rs as $k => $p )
                {
                    if ( $objCart->_checkstore( $p['product_id'], $p['nums'] ) )
                    {
                        continue;
                    }
                    return false;
                }
                foreach ( $rsG as $key => $val )
                {
                    if ( $objGift->checkstock( $val['gift_id'], $val['nums'] ) )
                    {
                        continue;
                    }
                    return false;
                }
            }
        }
        $payObj = $this->loadmethod( $this->type );
        $pay_vars = get_object_vars( $payObj );
        $this->cur_trading = $pay_vars['cur_trading'];
        if ( $this->tocreate( ) )
        {
            if ( $payObj->head_charset )
            {
                header( "Content-Type: text/html;charset=".$payObj->head_charset );
            }
            $html = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"\n                \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n                <html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en-US\" lang=\"en-US\" dir=\"ltr\">\n                <head>\n</header><body><div>Redirecting...</div>";
            $payObj->_payment = $this->payment;
            $toSubmit = $payObj->tosubmit( $this->getpaymentinfo( $method ) );
            if ( "utf8" != strtolower( $payObj->charset ) )
            {
                $charset =& $this->system->loadmodel( "utility/charset" );
                foreach ( $toSubmit as $k => $v )
                {
                    if ( !is_numeric( $v ) )
                    {
                        $toSubmit[$k] = $charset->utf2local( $v, "zh" );
                    }
                }
            }
            $html .= "<form id=\"payment\" action=\"".$payObj->submitUrl."\" method=\"".$payObj->method."\">";
            foreach ( $toSubmit as $k => $v )
            {
                if ( $k != "ikey" )
                {
                    $html .= "<input name=\"".urldecode( $k )."\" type=\"hidden\" value=\"".htmlspecialchars( $v )."\" />";
                    if ( $v )
                    {
                        $buffer .= urldecode( $k )."=".$v."&";
                    }
                }
            }
            if ( strtoupper( $this->type ) == "TENPAYTRAD" )
            {
                $buffer = substr( $buffer, 0, strlen( $buffer ) - 1 );
                $md5_sign = strtoupper( md5( $buffer."&key=".$toSubmit['ikey'] ) );
                $url = $payObj->submitUrl."?".$buffer."&sign=".$md5_sign;
                echo "<script language='javascript'>";
                echo "window.location.href='".$url."';";
                echo "</script>";
            }
            $html .= "\n</form>\n<script language=\"javascript\">\ndocument.getElementById('payment').submit();\n</script>\n</html>";
        }
        else
        {
            $html = "<html>\n<meta http-equiv=\\\"Content-Type\\\" content=\\\"text/html;charset=utf-8\\\"/>\n<script language=\"javascript\">\nalert('创建支付流水号错误！');\n//location.href=document.referrer;\n</script>\n</html>";
        }
        echo $html;
        $this->system->_succ = true;
        exit( );
    }

    function getpaymentfilebytype( $type )
    {
        $tmp_ary = $this->db->selectrow( "SELECT * FROM sdb_payment_cfg WHERE pay_type=".$type );
        $payment = $this->getpaymentfilename( $tmp_ary['config'], $type );
        return $payment;
    }

    function getpaymentfilename( $config, $ptype )
    {
        if ( !empty( $config ) )
        {
            $pmt = $this->loadmethod( $ptype );
            $field = $pmt->getfields( );
            $config = unserialize( $config );
            if ( is_array( $config ) )
            {
                foreach ( $field as $k => $v )
                {
                    if ( !( strtoupper( $v['type'] ) == "FILE" ) || !( $k == "keyPass" ) )
                    {
                        $payment[$k] = $config[$k];
                    }
                }
            }
        }
        return $payment;
    }

    function ispaybillsuccess( $payment_id )
    {
        $row = $this->db->selectrow( "select payment_id from sdb_payments WHERE payment_id='".$payment_id."' and status='succ'" );
        if ( $row )
        {
            return true;
        }
        return false;
    }

    function getsuccorderbilllist( $orderid )
    {
        return $this->db->select( "SELECT * FROM sdb_payments WHERE order_id = ".$orderid." and status IN ('succ','progress')" );
    }

    function showpayextendcon( &$payments, &$payExtend )
    {
        if ( $payExtend )
        {
            $payExtend = unserialize( $payExtend );
        }
        if ( $payments )
        {
            foreach ( $payments as $key => $val )
            {
                $showExtend = false;
                $fields = $this->getplugfields( $val['pay_type'] );
                if ( !is_array( $val['config'] ) )
                {
                    $config = unserialize( $val['config'] );
                }
                else
                {
                    $config = $val['config'];
                }
                foreach ( $fields as $k => $v )
                {
                    if ( $v['extendcontent'] )
                    {
                        foreach ( $v['extendcontent'] as $k1 => $v1 )
                        {
                            if ( !isset( $v1['property'] ) && !$v1['property']['display'] )
                            {
                                continue;
                            }
                            $showExtend = true;
                            break;
                        }
                        if ( !$config[$k] || !$showExtend )
                        {
                            foreach ( $v['extendcontent'] as $extk => $extv )
                            {
                                if ( $config[$extv['property']['name']] )
                                {
                                    $tmpValue = array( );
                                    foreach ( $config[$extv['property']['name']] as $conk => $conv )
                                    {
                                        foreach ( $extv['value'] as $evk => $evv )
                                        {
                                            if ( !( $conv == $evv['value'] ) )
                                            {
                                                continue;
                                            }
                                            $evv['imgurl'] = $evv['imgname'] ? "<img src=".$this->system->base_url( )."plugins/payment/images/".$evv['imgname'].">" : "";
                                            if ( $payExtend )
                                            {
                                                if ( is_array( $payExtend[$extv['property']['name']] ) )
                                                {
                                                    if ( in_array( $evv['value'], $payExtend[$extv['property']['name']] ) )
                                                    {
                                                        $evv['checked'] = "checked";
                                                    }
                                                }
                                                else if ( $payExtend[$extv['property']['name']] == $evv['value'] )
                                                {
                                                    $evv['checked'] = "checked";
                                                }
                                            }
                                            $tmpValue[] = $evv;
                                            break;
                                        }
                                    }
                                    $payments[$key]['extend'][] = array(
                                        "name" => $extv['property']['name'],
                                        "fronttype" => $extv['property']['fronttype'],
                                        "frontsize" => $extv['property']['frontsize'],
                                        "value" => $tmpValue,
                                        "extconId" => $extv['property']['frontname']
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    function recgextend( &$data, &$postInfo, &$extendInfo )
    {
        $paymentcfg = $this->system->loadmodel( "trading/paymentcfg" );
        $cfg = $paymentcfg->instance( $data['payment'], "pay_type" );
        if ( $cfg['pay_type'] )
        {
            $fields = $this->getplugfields( $cfg['pay_type'] );
            if ( is_array( $fields ) )
            {
                foreach ( $fields as $fkey => $fval )
                {
                    if ( $fval['extendcontent'] )
                    {
                        foreach ( $fval['extendcontent'] as $ffkey => $ffval )
                        {
                            if ( isset( $postInfo[$ffval['property']['name']] ) )
                            {
                                $extendInfo[$ffval['property']['name']] = $postInfo[$ffval['property']['name']];
                            }
                        }
                    }
                }
            }
        }
    }

    function ordmemextend( &$order, &$extendInfo )
    {
        $order['pay_extend'] = unserialize( $order['pay_extend'] );
        if ( is_array( $order['pay_extend'] ) )
        {
            $fields = $this->getplugfields( $order['paytype'] );
            $paymentcfg = $this->system->loadmodel( "trading/paymentcfg" );
            $cfg = $paymentcfg->instance( $order['payment'], "config" );
            if ( is_array( $fields ) )
            {
                $config = unserialize( $cfg['config'] );
                foreach ( $fields as $fkey => $fval )
                {
                    if ( $fval['extendcontent'] )
                    {
                        foreach ( $fval['extendcontent'] as $ffkey => $ffval )
                        {
                            $tmp = array( );
                            if ( isset( $config[$ffval['property']['name']] ) )
                            {
                                foreach ( $ffval['value'] as $fffkey => $fffval )
                                {
                                    $fffval['imgname'] = $fffval['imgname'] ? "<img src=".$this->system->base_url( )."plugins/payment/images/".$fffval['imgname'].">" : "";
                                    if ( in_array( $fffval['value'], $config[$ffval['property']['name']] ) )
                                    {
                                        if ( is_array( $order['pay_extend'][$ffval['property']['name']] ) )
                                        {
                                            if ( in_array( $fffval['value'], $order['pay_extend'][$ffval['property']['name']] ) )
                                            {
                                                $fffval['checked'] = "checked";
                                            }
                                        }
                                        else if ( $fffval['value'] == $order['pay_extend'][$ffval['property']['name']] )
                                        {
                                            $fffval['checked'] = "checked";
                                        }
                                        $tmp[] = $fffval;
                                    }
                                }
                                $extendInfo[$ffval['property']['name']] = array(
                                    "type" => $ffval['property']['fronttype'],
                                    "value" => $tmp
                                );
                            }
                        }
                    }
                }
            }
        }
    }

    function getextendofplug( $payid = "", $paytype = "", &$extfields )
    {
        $fields = $this->getplugfields( $paytype, $payid );
        foreach ( $fields as $k => $v )
        {
            if ( $v['extendcontent'] )
            {
                foreach ( $v['extendcontent'] as $key => $val )
                {
                    $extfields[] = $val['property']['name'];
                }
            }
        }
    }

    function getplugfields( $paytype = "", $payid = "" )
    {
        if ( !$paytype )
        {
            $paymentcfg = $this->system->loadmodel( "trading/paymentcfg" );
            $cfg = $this->getpaymentbyid( $payid );
            $paytype = $cfg['pay_type'];
        }
        $method = $this->loadmethod( $paytype );
        $fields = $method->getfields( );
        return $fields;
    }

    function getextendcon( $config, $payid )
    {
        $config = is_array( $config ) ? $config : unserialize( $config );
        if ( $config )
        {
            $fields = $this->getplugfields( "", $payid );
            $this->getextendofplug( $payid, "", $extfields );
            if ( $extfields )
            {
                foreach ( $fields as $key => $val )
                {
                    if ( $extendContent = $val['extendcontent'] )
                    {
                        foreach ( $extfields as $extk => $extv )
                        {
                            if ( $extendContent[$extk]['value'] )
                            {
                                foreach ( $extendContent[$extk]['value'] as $sk => $sv )
                                {
                                    if ( $sv['value'] == $config[$extv] )
                                    {
                                        $extendCon[] = $sv['imgname'] ? "<img src='".$this->system->base_url( )."plugins/payment/images/".$sv['imgname']."' tip='".$sv['name']."' alt='".$sv['name']."'>" : $sv['name'];
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return $extendCon;
        }
    }

    function _getbyorderid( $id )
    {
        return $this->db->select( "SELECT * FROM sdb_payments WHERE delivery_id='".$id."'" );
    }

    function deletepayment( $ident )
    {
        $result = true;
        if ( file_exists( PLUGIN_DIR."/app/".$ident ) )
        {
            $oAppmgr = $this->system->loadmodel( "system/appmgr" );
            $app = $oAppmgr->load( $ident );
            $oAppmgr->disable( $ident );
            $result = $app->uninstall( );
        }
        else
        {
            $this->db->exec( "delete from sdb_payment_cfg where pay_type =\"".substr( $ident, 4 )."\"" );
        }
        $this->db->exec( "delete from sdb_plugins where plugin_package='".$ident."'" );
        return $result;
    }

}

?>
