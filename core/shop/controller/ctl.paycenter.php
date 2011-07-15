<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class ctl_paycenter extends shopPage
{

    public $noCache = TRUE;

    public function ctl_paycenter( &$system )
    {
        parent::shoppage( $system );
        $this->payment =& $this->system->loadModel( "trading/payment" );
        $this->_verifyMember( FALSE );
    }

    public function order( )
    {
        if ( floatval( $_POST['money'] ) <= 0 )
        {
            $this->splash( "failed", $_SERVER['HTTP_REFERER'], __( "支付失败：支付金额非法" ) );
            return FALSE;
        }
        $oOrder =& $this->system->loadModel( "trading/order" );
        $order = $oOrder->load( $_POST['order_id'] );
        if ( $order['status'] != "active" )
        {
            $this->splash( "failed", $this->system->mkUrl( "order", "index", array(
                $_POST['order_id']
            ) ), __( "订单状态锁定，不能支付！" ) );
        }
        if ( 0 < $order['pay_status'] && $order['pay_status'] != 3 )
        {
            $this->splash( "failed", $this->system->mkUrl( "order", "index", array(
                $_POST['order_id']
            ) ), __( "订单已支付，不能重复支付！" ) );
        }
        if ( !$GLOBALS['_POST']['money'] = $oOrder->chgPayment( $_POST['order_id'], $_POST['payment']['payment'], $_POST['money'] ) )
        {
            $this->splash( "failed", $_SERVER['HTTP_REFERER'], __( "支付失败：订单号非法" ) );
        }
        if ( empty( $_POST['currency'] ) )
        {
            $this->splash( "failed", $_SERVER['HTTP_REFERER'], __( "支付失败：缺少支付货币参数" ) );
        }
        if ( count( $oOrder->checkPaymentCfg( $_POST['payment']['payment'] ) ) < 1 )
        {
            $this->splash( "failed", $_SERVER['HTTP_REFERER'], __( "该支付方式已被禁用，请选择其他支付方式" ) );
        }
        $payment = $this->system->loadModel( "trading/payment" );
        $tmpRow = $payment->getPaymentById( $_POST['payment']['payment'] );
        $payment->getExtendOfPlug( "", $tmpRow['pay_type'], $extfields );
        if ( $extfields )
        {
            foreach ( $extfields as $key => $val )
            {
                if ( isset( $_POST[$val] ) )
                {
                    $extend[$val] = $_POST[$val];
                }
            }
        }
        else
        {
            $extend = "";
        }
        $cur = $this->system->loadModel( "trading/cur" );
        $def_cur = $cur->getDefault( );
        $oOrder->updateExtend( $_POST['order_id'], $extend );
        $this->_init( $_POST['payment']['payment'] );
        $this->payment->order_id = $_POST['order_id'];
        $this->payment->member_id = $this->member['member_id'];
        if ( strtoupper( $_POST['currency'] ) != $def_cur['cur_code'] )
        {
            $this->payment->money = number_format( $_POST['cur_money'], 2, ".", "" );
        }
        else
        {
            $this->payment->money = $_POST['money'];
        }
        $this->payment->cur_money = $_POST['cur_money'];
        $this->payment->currency = $_POST['currency'];
        if ( $this->payment->pay_type == "deposit" )
        {
            $oAdvance =& $this->system->loadModel( "member/advance" );
            $status = $oAdvance->checkAccount( $this->member['member_id'], $_POST['money'], $message, $rows );
            if ( !$status )
            {
                if ( $status === 0 )
                {
                    $this->pagedata['payment'] = array_merge( $_POST, $rows[0] );
                    $this->output( );
                }
                else
                {
                    $this->splash( "failed", $_SERVER['HTTP_REFERER'], __( "支付失败：" ).$message );
                }
            }
            else
            {
                $this->payment->pay_account = $this->member['uname'];
                if ( !$this->payment->doPay( "", $_POST['order_id'] ) )
                {
                    $this->splash( "failed", $_SERVER['HTTP_REFERER'], __( "商品库存不足" ) );
                }
                setcookie( "S[order_payed]", 1 );
            }
        }
        else if ( !$this->payment->doPay( "", $_POST['order_id'] ) )
        {
            $this->splash( "failed", $_SERVER['HTTP_REFERER'], __( "商品库存不足" ) );
        }
    }

    public function recharge( )
    {
        $this->_init( $_POST['payment']['payment'] );
        if ( $this->payment->type == "deposit" )
        {
            $this->splash( "failed", $_SERVER['HTTP_REFERER'], __( "充值失败：不能使用预存款支付" ) );
        }
        $this->payment->pay_type = "recharge";
        $this->payment->cur_money = $_POST['money'];
        $this->payment->member_id = $_POST['member_id'];
        $oCur =& $this->system->loadModel( "system/cur" );
        $aCur = $oCur->getcur( $_POST['payment']['currency'], TRUE );
        if ( empty( $aCur['cur_code'] ) )
        {
            $this->splash( "failed", $_SERVER['HTTP_REFERER'], __( "充值失败：缺少支付货币参数" ) );
        }
        $this->payment->currency = $aCur['cur_code'];
        $this->payment->money = $aCur['cur_rate'] ? $_POST['money'] / $aCur['cur_rate'] : $_POST['money'];
        if ( $this->payment->config['method'] == 1 )
        {
            $this->payment->paycost = $this->payment->fee == 0 ? 0 : $this->payment->money * $this->payment->fee;
            $this->payment->money = $this->payment->fee == 0 ? $this->payment->money : $this->payment->money * ( 1 + $this->payment->fee );
        }
        else if ( $this->payment->config['method'] == 2 )
        {
            $this->payment->money = $this->payment->fee == 0 ? $this->payment->money : $this->payment->money + $this->payment->fee;
            $this->payment->paycost = $this->payment->fee == 0 ? 0 : $this->payment->fee;
        }
        else
        {
            $this->payment->money = $this->payment->money;
        }
        $this->payment->doPay( "recharge" );
    }

    public function result( )
    {
        $pyd = array_merge( $_GET, $_POST );
        $payment = $this->payment->getById( $pyd['payment_id'] );
        $this->_verifyMember( FALSE );
        if ( !$payment['member_id'] || $payment['member_id'] == $this->member['member_id'] )
        {
            if ( $payment['status'] == "succ" && $_COOKIE['order_payed'] )
            {
                setcookie( "S[order_payed]", "" );
            }
            $this->pagedata['payment'] =& $payment;
            $this->output( );
        }
        else
        {
            $this->system->error( 404 );
            exit( );
        }
    }

    public function _init( $payment_id )
    {
        $aPayment = $this->payment->getPaymentById( $payment_id );
        if ( $aPayment['id'] < 1 )
        {
            $this->splash( "failed", $_SERVER['HTTP_REFERER'], __( "支付失败：请选择支付方式！" ) );
        }
        else
        {
            if ( $aPayment['pay_type'] == "offline" )
            {
                if ( $this->member['member_id'] )
                {
                    $this->splash( "failed", $this->system->mkUrl( "member", "orderdetail", array(
                        $_POST['order_id']
                    ) ), __( "订单已成功提交了:" ).$aPayment['custom_name'] );
                }
                else
                {
                    $this->splash( "failed", $this->system->mkUrl( "order", "index", array(
                        $_POST['order_id']
                    ) ), __( "订单已成功提交了:" ).$aPayment['custom_name'] );
                }
            }
            else
            {
                if ( $aPayment['pay_type'] == "deposit" )
                {
                    $this->_verifyMember( );
                    $this->payment->pay_type = "deposit";
                }
                else
                {
                    $this->payment->pay_type = "online";
                }
                $this->payment->fee = $aPayment['fee'];
                $this->payment->type = $aPayment['pay_type'];
                $this->payment->bank = $aPayment['pay_type'];
                $aPayment['config'] = unserialize( $aPayment['config'] );
                $this->payment->config = $aPayment['config'];
                $this->payment->account = $aPayment['config']['member_id'];
                $this->payment->payment = $payment_id;
                $this->payment->paymethod = $aPayment['custom_name'];
                $this->payment->status = "ready";
                $this->payment->ip = remote_addr( );
                $this->payment->t_begin = time( );
                $this->payment->memo = __( "会员支付自动生成" );
            }
        }
    }

}

?>
