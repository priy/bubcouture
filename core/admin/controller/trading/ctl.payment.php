<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "objectPage.php" );
class ctl_payment extends objectPage
{

    public $workground = "setting";
    public $finder_action_tpl = "payment/finder_action.html";
    public $object = "trading/paymentcfg";
    public $editMode = TRUE;
    public $allowExport = FALSE;
    public $disableGridEditCols = "id";
    public $disableColumnEditCols = "id";
    public $disableGridShowCols = "id";
    public $filterUnable = TRUE;

    public function _detail( )
    {
        return array(
            "show_detail" => array(
                "label" => __( "支付配置" ),
                "tpl" => "payment/pay_edit.html"
            )
        );
    }

    public function index( )
    {
        $appmgr =& $this->system->loadModel( "system/appmgr" );
        $cilent =& $this->system->loadModel( "service/apiclient" );
        $cilent->url = "http://sds.ecos.shopex.cn/api.php";
        $cilent->key = "371e6dceb2c34cdfb489b8537477ee1c";
        $payment = $cilent->native_svc( "payment.get_all_payments" );
        if ( $payment['result'] == "succ" )
        {
            $paymentData = $payment['result_msg'];
            if ( is_array( $paymentData ) && isset( $paymentData[0]['pay_name'] ) )
            {
                $allApp = $appmgr->getPaydata( $paymentData );
                file_put_contents( HOME_DIR."/sendtmp/allApp.log", serialize( $allApp ) );
            }
            if ( is_array( $allApp ) && isset( $allApp[0]['pay_name'] ) )
            {
                $useApp = $appmgr->getUseapp( $allApp );
                file_put_contents( HOME_DIR."/sendtmp/useApp.log", serialize( $useApp ) );
            }
        }
        else
        {
            if ( file_exists( HOME_DIR."/sendtmp/useApp.log" ) )
            {
                $usep = file_get_contents( HOME_DIR."/sendtmp/useApp.log" );
            }
            if ( file_exists( HOME_DIR."/sendtmp/allApp.log" ) )
            {
                $allp = file_get_contents( HOME_DIR."/sendtmp/allApp.log" );
            }
            else
            {
                $allp = file_get_contents( HOME_DIR."/sendtmp/defaultApp.log" );
            }
            if ( is_string( $usep ) )
            {
                $useApp = unserialize( $usep );
            }
            if ( is_string( $allp ) )
            {
                $allApp = unserialize( $allp );
            }
        }
        $this->pagedata['allNum'] = count( $allApp );
        $this->pagedata['useNum'] = count( $useApp );
        $this->pagedata['allPay'] = $allApp;
        $this->pagedata['usePay'] = $useApp;
        if ( $_POST['china'] == "true" )
        {
            $this->display( "payment/pay_index_china.html" );
        }
        else if ( $_POST['other'] == "true" )
        {
            $this->display( "payment/pay_index_other.html" );
        }
        else
        {
            $this->page( "payment/pay_index.html" );
        }
    }

    public function show_detail( $id )
    {
        $oPay =& $this->system->loadModel( "trading/payment" );
        $aPay = $oPay->getPaymentById( $id );
        $this->pagedata['pay'] = $aPay;
        $this->pagedata['pay_info'] = $this->_getPayOpt( $aPay['pay_type'], $aPay['custom_name'], $aPay['fee'], $aPay['config'], 1 );
        $oPlu = $oPay->loadMethod( $aPay['pay_type'] );
        if ( $oPlu )
        {
            $this->pagedata['html'] = $oPlu->infoPad( );
        }
        $this->pagedata['pay_id'] = $id;
        $this->pagedata['order'] = $aPay['orderlist'];
        $this->pagedata['old_pay_type'] = $aPay['pay_type'];
        $this->pagedata['pay_des'] = $aPay['des'];
        $this->pagedata['pay_name'] = $aPay['custom_name'];
        $this->pagedata['paylist'] = $oPay->getPluginsArr( TRUE );
    }

    public function getPayList( )
    {
        $this->path[] = array(
            "text" => __( "支付方式" )
        );
        $oPay =& $this->system->loadModel( "trading/payment" );
        $this->pagedata['items'] = $oPay->getMethods( );
        $this->page( "payment/pay_list.html" );
    }

    public function _getHtmlString( $key, &$val, $rs = array( ), &$eventScripts )
    {
        unset( $selOption );
        unset( $tOptions );
        switch ( $val['type'] )
        {
        case "string" :
            $aTemp = array(
                "labelName" => $val['label'],
                "params" => array(
                    "type" => "text",
                    "name" => $key,
                    "value" => $rs[$key] ? $rs[$key] : ""
                )
            );
            break;
        case "select" :
            foreach ( $val['options'] as $k => $v )
            {
                $tOptions[$k] = $v;
                if ( $rs[$key] == $k )
                {
                    $selOption = $k;
                }
            }
            $aTemp = array(
                "labelName" => $val['label'],
                "params" => array(
                    "type" => "select",
                    "name" => $key,
                    "value" => $selOption,
                    "options" => $tOptions
                )
            );
            break;
        case "number" :
            $aTemp = array(
                "labelName" => $val['label'],
                "params" => array(
                    "type" => "text",
                    "name" => $key,
                    "value" => $rs[$key] ? $rs[$key] : ""
                )
            );
            break;
        case "file" :
            $aTemp = array(
                "labelName" => $val['label'],
                "params" => array(
                    "type" => "file",
                    "name" => $key,
                    "value" => $selOption,
                    "options" => $tOptions
                )
            );
            break;
        case "radio" :
            foreach ( $val['options'] as $k => $v )
            {
                $checked = "";
                if ( $rs[$key] == $k )
                {
                    $checked = "checked";
                }
                $tOptions[$k] = $v;
                if ( $rs[$key] == $k )
                {
                    $selOption = $k;
                }
            }
            if ( $val['extendcontent'] )
            {
                unset( $extendContent );
                foreach ( $val['extendcontent'] as $ck => $cv )
                {
                    $scripts .= "<script>";
                    if ( isset( $rs[$key] ) )
                    {
                        if ( $rs[$key] )
                        {
                            $scripts .= "\$('".$cv['property']['extconId']."').show();";
                        }
                        else
                        {
                            $scripts .= "\$('".$cv['property']['extconId']."').hide();";
                        }
                    }
                    else if ( $cv['property']['display'] )
                    {
                        $scripts .= "\$('".$cv['property']['extconId']."').show();";
                    }
                    else
                    {
                        $scripts .= "\$('".$cv['property']['extconId']."').hide();";
                    }
                    $scripts .= "</script>";
                    $i = 0;
                    $type = $cv['property']['type'];
                    $name = $cv['property']['name'];
                    $size = $cv['property']['size'] ? $cv['property']['size'] : 4;
                    unset( $extendContent );
                    foreach ( $cv['value'] as $csk => $csv )
                    {
                        unset( $checked );
                        if ( !$rs )
                        {
                            $checked = "checked=true";
                        }
                        if ( in_array( $csv['value'], $rs[$name] ) )
                        {
                            $checked = "checked=true";
                        }
                        $csv['imgname'] = $csv['imgname'] ? "<img src=".$this->system->base_url( )."plugins/payment/images/".$csv['imgname'].">" : $csv['name'];
                        $val['extendcontent'][$ck]['value'][$csk]['imgname'] = $csv['imgname'];
                        $val['extendcontent'][$ck]['value'][$csk]['checked'] = $checked;
                    }
                }
            }
            $aTemp = array(
                "labelName" => $val['label'],
                "params" => array(
                    "type" => "radio",
                    "name" => $key,
                    "value" => $selOption,
                    "options" => $tOptions
                ),
                "extendContent" => $val['extendcontent']
            );
            if ( $val['event'] )
            {
                $aTemp['params']['onclick'] = $val['event']."(this);";
            }
            if ( $val['eventscripts'] )
            {
                $eventScripts = $val['eventscripts'].$scripts;
            }
            break;
        default :
            $aTemp = array(
                "labelName" => $val['label'],
                "params" => array(
                    "type" => "text",
                    "name" => $key,
                    "value" => $rs[$key] ? $rs[$key] : ""
                )
            );
            break;
        }
        return $aTemp;
    }

    public function savePayment( )
    {
        $this->begin( "index.php?ctl=trading/payment&act=index" );
        $oPay =& $this->system->loadModel( "trading/payment" );
        if ( $_POST['paymethod'] == 1 )
        {
            $GLOBALS['_POST']['fee'] = $_POST['fee'] / 100;
        }
        if ( $_FILES )
        {
            $file =& $this->system->loadModel( "system/sfile" );
            foreach ( $_FILES as $key => $val )
            {
                if ( 0 < intval( $val['size'] ) )
                {
                    $GLOBALS['_POST'][$key] = $val['name'];
                    switch ( $_POST['pay_type'] )
                    {
                    case "ICBC" :
                        if ( $key == "keyFile" )
                        {
                            if ( substr( $val['name'], strrpos( $val['name'], "." ) + 1, strlen( $val['name'] ) ) != "key" )
                            {
                                trigger_error( __( "商户私钥文件格式有误,请上传key格式文件" ), E_USER_ERROR );
                                exit( );
                            }
                        }
                        else if ( ( $key == "certFile" || $key == "icbcFile" ) && substr( $val['name'], strrpos( $val['name'], "." ) + 1, strlen( $val['name'] ) ) != "crt" )
                        {
                            if ( $key == "certFile" )
                            {
                                trigger_error( __( "商户公钥文件格式有误,请上传crt格式文件" ), E_USER_ERROR );
                            }
                            else
                            {
                                trigger_error( __( "工行公钥文件格式有误,请上传crt格式文件" ), E_USER_ERROR );
                            }
                            exit( );
                        }
                        break;
                    case "HYL" :
                        if ( $key == "keyFile" )
                        {
                            if ( substr( $val['name'], strrpos( $val['name'], "." ) + 1, strlen( $val['name'] ) ) != "pem" )
                            {
                                trigger_error( __( "私钥文件格式有误,请上传key格式文件" ), E_USER_ERROR );
                                exit( );
                            }
                        }
                        else if ( $key == "certFile" && substr( $val['name'], strrpos( $val['name'], "." ) + 1, strlen( $val['name'] ) ) != "cer" )
                        {
                            trigger_error( __( "公钥文件格式有误,请上传cer格式文件" ), E_USER_ERROR );
                            exit( );
                        }
                        break;
                    default :
                        break;
                    }
                    $file->UploadPaymentFile( $val, $_POST['pay_type'] );
                }
            }
        }
        if ( isset( $_POST['id'] ) && $_POST['id'] != "" )
        {
            $operation_type = "config";
        }
        else
        {
            $operation_type = "add";
        }
        $this->sendRequestAsync( $_POST['pay_ident'], $operation_type );
        if ( $oPay->updatePay( $_POST ) )
        {
            $this->end( TRUE, __( "保存成功！" ) );
        }
        else
        {
            $this->end( FALSE, __( "保存失败！" ) );
        }
    }

    public function disable( $id )
    {
        $payment = $this->system->loadModel( "trading/payment" );
        $this->begin( "index.php?ctl=trading/payment&act=index" );
        $this->clear_all_cache( );
        $this->end( $payment->deletePay( $id ) );
    }

    public function addPayment( )
    {
        $this->begin( "index.php?ctl=trading/payment&act=index" );
        $oPay =& $this->system->loadModel( "trading/payment" );
        if ( $_POST['paymethod'] == 1 )
        {
            $GLOBALS['_POST']['fee'] = $_POST['fee'] / 100;
        }
        if ( $_FILES )
        {
            $file =& $this->system->loadModel( "system/sfile" );
            foreach ( $_FILES as $key => $val )
            {
                if ( 0 < intval( $val['size'] ) )
                {
                    $GLOBALS['_POST'][$key] = $val['name'];
                    switch ( $_POST['pay_type'] )
                    {
                    case "ICBC" :
                        if ( $key == "keyFile" )
                        {
                            if ( substr( $val['name'], strrpos( $val['name'], "." ) + 1, strlen( $val['name'] ) ) != "key" )
                            {
                                trigger_error( __( "文件格式有误,请上传key格式文件" ), E_USER_ERROR );
                                exit( );
                            }
                        }
                        else if ( ( $key == "certFile" || $key == "icbcFile" ) && substr( $val['name'], strrpos( $val['name'], "." ) + 1, strlen( $val['name'] ) ) != "crt" )
                        {
                            trigger_error( __( "文件格式有误,请上传crt格式文件" ), E_USER_ERROR );
                            exit( );
                        }
                        break;
                    case "HYL" :
                        if ( $key == "keyFile" )
                        {
                            if ( substr( $val['name'], strrpos( $val['name'], "." ) + 1, strlen( $val['name'] ) ) != "pem" )
                            {
                                trigger_error( __( "文件格式有误,请上传pem格式文件" ), E_USER_ERROR );
                                exit( );
                            }
                        }
                        else if ( $key == "certFile" && substr( $val['name'], strrpos( $val['name'], "." ) + 1, strlen( $val['name'] ) ) != "cer" )
                        {
                            trigger_error( __( "文件格式有误,请上传cer格式文件" ), E_USER_ERROR );
                            exit( );
                        }
                        break;
                    case "skypay" :
                        if ( ( $key == "keyFile" || $key == "certFile" ) && substr( $val['name'], strrpos( $val['name'], "." ) + 1, strlen( $val['name'] ) ) != "key" )
                        {
                            trigger_error( __( "文件格式有误,请上传key格式文件" ), E_USER_ERROR );
                            exit( );
                        }
                        break;
                    default :
                        break;
                    }
                    $file->UploadPaymentFile( $val, $_POST['pay_type'] );
                }
            }
        }
        $this->end( $oPay->insertPay( $_POST, $msg ), $msg );
    }

    public function delPayment( $sId )
    {
        $this->begin( "index.php?ctl=trading/payment&act=index" );
        $oPay =& $this->system->loadModel( "trading/payment" );
        $this->end( $oPay->deletePay( $sId ), __( "删除成功！" ) );
    }

    public function editPayment( $payId, $payName, $paytype )
    {
        $payName = urldecode( $payName );
        $this->path[] = array(
            "text" => __( "编辑支付方式" )
        );
        $oPay =& $this->system->loadModel( "trading/payment" );
        if ( isset( $payId ) )
        {
            $aPay = $oPay->getPaymentById( $payId );
            $paytype = substr( $paytype, 4 );
            if ( !empty( $aPay['pay_type'] ) )
            {
                $paytype = $aPay['pay_type'];
            }
            if ( isset( $aPay ) )
            {
                $this->pagedata['pay'] = $aPay;
                $this->loadJs( );
                $this->pagedata['pay_info'] = $this->_getPayOpt( $paytype, $aPay['custom_name'], $aPay['fee'], $aPay['config'], 1 );
                $oPlu = $oPay->loadMethod( $paytype );
                if ( $oPlu )
                {
                    $this->pagedata['html'] = $oPlu->infoPad( );
                }
                $this->pagedata['centerName'] = $payName;
                $this->pagedata['sPayName'] = $aPay['custom_name'];
                $this->pagedata['paytype'] = $paytype;
                $this->pagedata['order'] = $aPay['orderlist'];
                $this->pagedata['old_pay_type'] = $aPay['pay_type'];
                $this->pagedata['pay_des'] = $aPay['des'];
                $this->pagedata['pay_id'] = $aPay['id'];
                $this->pagedata['custom_name'] = $aPay['custom_name'];
                $this->pagedata['paylist'] = $oPay->getPluginsArr( TRUE );
            }
            else
            {
                $this->pagedata['centerName'] = $payName;
                $this->pagedata['custom_name'] = $payName;
                $this->pagedata['paytype'] = $paytype;
                $this->pagedata['paylist'] = $oPay->getPluginsArr( TRUE );
                $this->pagedata['pay_info'] = $this->_getPayOpt( $paytype, "", "", "", 1 );
                $oPlu = $oPay->loadMethod( $paytype );
                if ( $oPlu )
                {
                    $this->pagedata['html'] = $oPlu->infoPad( );
                }
            }
        }
        $this->pagedata['pay_ident'] = "pay_".$paytype;
        $this->page( "payment/pay_new.html" );
    }

    public function detailPayment( $id )
    {
        $this->path[] = array(
            "text" => __( "支付方式配置" )
        );
        $oPay =& $this->system->loadModel( "trading/payment" );
        $aPay = $oPay->getPaymentById( $id );
        $this->pagedata['pay'] = $aPay;
        $this->pagedata['pay_info'] = $this->_getPayOpt( $aPay['pay_type'], $aPay['custom_name'], $aPay['fee'], $aPay['config'] );
        $this->pagedata['pay_id'] = $id;
        $this->pagedata['order'] = $aPay['orderlist'];
        $this->pagedata['old_pay_type'] = $aPay['pay_type'];
        $this->pagedata['pay_des'] = $aPay['des'];
        $this->pagedata['pay_name'] = $aPay['custom_name'];
        $this->pagedata['paylist'] = $oPay->getPluginsArr( TRUE );
        $this->page( "payment/pay_edit.html" );
    }

    public function getPayOpt( $sType, $sPayName = "" )
    {
        header( "Content-Type: text/html;charset=utf-8" );
        if ( !$sType )
        {
            echo " ";
        }
        else
        {
            echo $this->_getPayOpt( $sType, $sPayName );
            $this->loadJs( );
        }
    }

    public function _getPayOpt( $sType, $sPayName = "", $nFee = "", $config = "", $fetch = 0 )
    {
        $oPay =& $this->system->loadModel( "trading/payment" );
        $oPlu = $oPay->loadMethod( $sType );
        if ( $aThisPayCur = $oPay->getSupportCur( $oPlu ) )
        {
            if ( $aThisPayCur['DEFAULT'] )
            {
                $curName = __( "商店默认货币" );
            }
            else
            {
                $oCur =& $this->system->loadModel( "system/cur" );
                $aCurLang = $oCur->getSysCur( );
                if ( $aThisPayCur['ALL'] )
                {
                    $aThisPayCur = $aCurLang;
                }
                foreach ( $aThisPayCur as $k => $v )
                {
                    $curName .= $aCurLang[$k].",&nbsp;";
                    $curName = $curName ? rtrim( $curName, ",&nbsp;" ) : "";
                }
            }
        }
        if ( $oPlu )
        {
            $aTemp = unserialize( $config );
            if ( $aTemp )
            {
                foreach ( $aTemp as $key => $val )
                {
                    if ( $key != "method" && $key != "fee" )
                    {
                        $aPay[$key] = $val;
                    }
                }
            }
            $aField = $oPlu->getfields( );
            foreach ( $aField as $key => $val )
            {
                $PayPlugItem[] = $this->_getHtmlString( $key, $val, $aPay, $eventScripts );
            }
        }
        if ( $aTemp['method'] == 1 || !isset( $aTemp['method'] ) )
        {
            $check1 = "checked";
        }
        else if ( $aTemp['method'] == 2 )
        {
            $check2 = "checked";
        }
        $this->pagedata['sPayName'] = $sPayName;
        $this->pagedata['curName'] = $curName;
        $this->pagedata['PayPlugItem'] = $PayPlugItem;
        $this->pagedata['fee'] = array(
            $nFee,
            $aTemp['fee']
        );
        $this->pagedata['checked'] = array(
            $check1,
            $check2
        );
        $this->pagedata['eventScripts'] = $eventScripts;
        $this->pagedata['hiddenmethod'] = $aTemp['method'];
    }

    public function loadJs( )
    {
        echo "<script>";
        echo "var ipt=\$('setfee').getElements('input[type=radio]').addEvent('click',function(){";
        echo "setDisable(this.value);";
        echo "});";
        echo "var setDisable=function(v){";
        echo "if (v==2){";
        echo "      \$('fix').show().getElements('input[type=text]').set('disabled',false);";
        echo "      \$('rate').hide().getElements('input[type=text]').set('disabled',true);";
        echo "}else{";
        echo "      \$('rate').show().getElements('input[type=text]').set('disabled',false);";
        echo "      \$('fix').hide().getElements('input[type=text]').set('disabled',true);";
        echo "}";
        echo "};";
        echo "var mtd=\$('hiddenmethod').get('text');";
        echo "setDisable(mtd);";
        echo "</script>";
    }

    public function do_install( $ident, $type = "offline", $is_update = FALSE )
    {
        $app_path = PLUGIN_DIR."/app/";
        if ( is_dir( $app_path.$ident ) )
        {
            $this->install_app( $ident );
        }
        else
        {
            $this->install_online( $ident );
        }
    }

    public function install_online( $ident, $url, $is_update = FALSE )
    {
        if ( !$url )
        {
            $url = "http://sds.ecos.shopex.cn/payments/apps/".$ident.".tar";
        }
        include( CORE_DIR."/admin/controller/service/ctl.download.php" );
        ( );
        $download = new ctl_download( );
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
            $this->do_install_online( );
        }
    }

    public function install_app( $ident )
    {
        $appmgr = $this->system->loadModel( "system/appmgr" );
        $refesh =& $this->system->loadModel( "system/addons" );
        $payment = $this->system->loadModel( "trading/payment" );
        if ( $appmgr->install( $ident, "1" ) )
        {
            if ( !$_SESSION['updatePayment'] )
            {
                $plugin_name = $appmgr->getAppName( $ident );
                $data['custom_name'] = $plugin_name['plugin_name'];
                $data['pay_type'] = substr( $ident, 4 );
                $data['disabled'] = "true";
                $payment->insertPaymentApp( $data );
            }
            if ( file_exists( HOME_DIR."/sendtmp/allApp.log" ) )
            {
                $allApp = file_get_contents( HOME_DIR."/sendtmp/allApp.log" );
            }
            else
            {
                $allApp = file_get_contents( HOME_DIR."/sendtmp/defaultApp.log" );
            }
            if ( !file_exists( HOME_DIR."/sendtmp/useApp.log" ) )
            {
                fwrite( HOME_DIR."/sendtmp/useApp.log", "" );
            }
            $useApp = file_get_contents( HOME_DIR."/sendtmp/useApp.log" );
            if ( is_string( $allApp ) )
            {
                $allApp = unserialize( $allApp );
            }
            if ( is_string( $useApp ) )
            {
                $useApp = unserialize( $useApp );
            }
            foreach ( $allApp as $key => $val )
            {
                if ( $val['pay_ident'] == $ident )
                {
                    $allApp[$key]['disable'] = "false";
                    $useApp[] = $val;
                }
            }
            file_put_contents( HOME_DIR."/sendtmp/useApp.log", serialize( $useApp ) );
            file_put_contents( HOME_DIR."/sendtmp/allApp.log", serialize( $allApp ) );
            unset( $_SESSION['updatePayment'] );
            $this->sendRequestAsync( $ident, "install" );
            $this->clear_all_cache( );
            echo "<script>W.page('index.php?ctl=trading/payment&act=index',{onComplete:function(){\$('main').setStyle('width',window.mainwidth);}})</script>";
        }
        else
        {
            $this->end( FALSE, "安装失败" );
        }
    }

    public function do_install_online( )
    {
        $task = HOME_DIR."/tmp/".$_GET['download'];
        $temp_mess = file_get_contents( $task."/task.php" );
        $down_data = unserialize( $temp_mess );
        if ( $url = $down_data['download_list'][0] )
        {
            $filename = substr( $url, strrpos( $url, "/" ) + 1 );
            $file_path = $task."/".$filename;
            $dir_name = substr( $filename, 0, strrpos( $filename, "." ) );
            if ( file_exists( $file_path ) )
            {
                $appmgr = $this->system->loadModel( "system/appmgr" );
                $appmgr->instal_ol_app( $file_path, $dir_name, $msg, TRUE );
                $this->install_app( $dir_name );
            }
        }
    }

    public function updateNewPayment( $ident )
    {
        if ( isset( $ident ) )
        {
            $_SESSION['updatePayment'] = TRUE;
            $this->install_online( $ident );
            $this->sendRequestAsync( $ident, $_GET['operation_type'], $_GET['app_version'] );
        }
    }

    public function disApp( $id )
    {
        $this->begin( "index.php?ctl=trading/payment&act=index" );
        $this->sendRequestAsync( $_GET['ident'], $_GET['operation_type'] );
        $paymentObj =& $this->system->loadModel( "trading/payment" );
        $this->end( $paymentObj->disApp( $id ), __( "修改成功！" ) );
    }

    public function startApp( $id )
    {
        $this->begin( "index.php?ctl=trading/payment&act=index" );
        $this->sendRequestAsync( $_GET['ident'], $_GET['operation_type'] );
        $paymentObj =& $this->system->loadModel( "trading/payment" );
        $this->end( $paymentObj->startApp( $id ), __( "修改成功！" ) );
    }

    public function deletePayment( $id )
    {
        $oPayment = $this->system->loadModel( "trading/payment" );
        $plugin = $oPayment->getPaymentById( $id );
        $ident = "pay_".$plugin['pay_type'];
        $this->begin( "index.php?ctl=trading/payment&act=index" );
        $this->sendRequestAsync( $ident, $_GET['operation_type'] );
        if ( $oPayment->deletePayment( $ident ) )
        {
            $this->clear_all_cache( );
            if ( file_exists( PLUGIN_DIR."/app/".$ident ) )
            {
                deletedir( PLUGIN_DIR."/app/".$ident );
            }
            $this->end( TRUE, "操作成功" );
        }
        else
        {
            $this->end( FALSE, "操作失败" );
        }
    }

    public function sendRequestAsync( $ident, $operation_type, $version = "" )
    {
        $cet_ping = ping_url( "http://esb.shopex.cn/api.php" );
        if ( !strstr( $cet_ping, "HTTP/1.1 200 OK" ) )
        {
            return;
        }
        if ( !$version )
        {
            $oAppmgr = $this->system->loadModel( "system/appmgr" );
            $appInfo = $oAppmgr->getPluginInfoByident( $ident );
            $version = $appInfo['plugin_version'];
        }
        echo "<script>new Request().post('index.php?ctl=trading/payment&act=sendDataToCenter',{pay_ident:'{$ident}',version:'{$version}',operation_type:'{$operation_type}'});</script>";
    }

    public function sendDataToCenter( )
    {
        $oApiClient = $this->system->loadModel( "service/apiclient" );
        $oApiClient->url = "http://esb.shopex.cn/api.php";
        $return = $oApiClient->native_svc( "payment.count_payment", array(
            "certi_id" => $this->system->getConf( "certificate.id" ),
            "pay_key" => $_POST['pay_ident'],
            "version" => $_POST['version'],
            "type" => $_POST['operation_type'],
            "time" => time( )
        ) );
    }

}

?>
