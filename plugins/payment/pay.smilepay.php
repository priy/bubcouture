<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "paymentPlugin.php" );
class pay_smilepay extends paymentPlugin
{

    public $name = "SMSe在线支付";
    public $logo = "SMILEPAY";
    public $version = 20070615;
    public $charset = "utf-8";
    public $submitUrl = "https://ssl.smse.com.tw/pay/pay_a_utf.asp?Rvg2c=1&Dcvc=107";
    public $submitButton = "http://www.smilepay.net/images/SmilePay_LOGO03.jpg";
    public $supportCurrency = array
    (
        "TWD" => "",
        "CNY" => ""
    );
    public $supportArea = array
    (
        0 => "AREA_TWD",
        1 => "AREA_CNY"
    );
    public $status = NULL;
    public $desc = "SmilePay";
    public $orderby = 430;
    public $cur_trading = TRUE;

    public function toSubmit( $payment )
    {
        $return['Dcvc'] = $this->getConf( $payment['M_OrderId'], "member_id" );
        $return['Rvg2c'] = 1;
        $return['Od_sob'] = $payment['M_OrderNO'];
        $return['Data_id'] = $payment['M_OrderId'];
        $return['Deadline_date'] = "";
        $return['Deadline_time'] = "";
        $return['Amount'] = $payment['M_Amount'];
        $return['Pur_name'] = $payment['R_Name'];
        $return['Tel_number'] = $payment['R_Telephone'];
        $return['Mobile_number'] = $payment['R_Mobile'];
        $return['Address'] = $payment['R_Address'];
        $return['Email'] = $payment['R_Email'];
        $return['Invoice_name'] = $payment['R_Name'];
        $return['Invoice_num'] = $payment['R_Postcode'];
        $return['Remark'] = $payment['M_Remark'];
        $return['Roturl'] = $this->callbackUrl;
        return $return;
    }

    public function callback( $in, &$paymentId, &$money, &$message )
    {
        $Classif = $in['Classif'];
        $Od_sob = $in['Od_sob'];
        $Data_id = $in['Data_id'];
        $Process_date = $in['Process_date'];
        $Process_time = $in['Process_time'];
        $Response_id = $in['Response_id'];
        $Auth_code = $in['Auth_code'];
        $Moneytype = $in['Moneytype'];
        $Purchamt = $in['Purchamt'];
        $Amount = $in['Amount'];
        $Errdesc = $in['Errdesc'];
        $Pur_name = $in['Pur_name'];
        $Tel_number = $in['Tel_number'];
        $Mobile_number = $in['Mobile_number'];
        $Address = $in['Address'];
        $Email = $in['Email'];
        $Invoice_num = $in['Invoice_num'];
        $Remark = $in['Remark'];
        $Smseid = $in['Smseid'];
        $paymentId = $Data_id;
        $money = $Amount;
        unset( $in );
        $message .= "<br/>付費方式:";
        switch ( $Classif )
        {
        case "A" :
            $message .= "刷卡";
            break;
        case "B" :
            $message .= "ATM / 匯款";
            break;
        case "C" :
            $message .= "超商代收";
            break;
        case "D" :
            $message .= "刷卡(大陸金融卡)";
            break;
        case "E" :
            $message .= "7-11 ibon";
            break;
        default :
            break;
        }
        $message .= "<br/>訂單號碼：".$Data_id;
        $message .= "<br/>流水號碼：".$Od_sob;
        $message .= "<br/>交易日期：".$Process_date;
        $message .= "<br/>交易時間：".$Process_time;
        if ( $Classif == "A" )
        {
            $message .= "<br/>授權質料:".( $Response_id == 1 ? "授權成功" : "授權失敗" );
            $temp = $Response_id == 1 ? $Amount : 0;
            if ( $Response_id == 0 )
            {
                $error = "<br/>交易失敗原因:".$Errdesc;
            }
        }
        if ( $Classif == "A" || $Classif == "E" )
        {
            $message .= "<br/>授權碼:".$Auth_code;
        }
        $message .= "<br/>幣別：".( $Moneytype == "CN" ? "人民幣" : "台幣" );
        $message .= "<br/>交易金額：".$Purchamt;
        $message .= "<br/>成交金額：".$Classif == "A" ? $temp : $Amount;
        $message .= "<br/>姓名：".$Pur_name;
        $message .= "<br/>聯絡電話：".$Tel_number;
        $message .= "<br/>行動電話：".$Mobile_number;
        $message .= "<br/>送貨地址：".$Address;
        $message .= "<br/>電子信箱：".$Email;
        $message .= "<br/>統一編號：".$Invoice_num;
        $message .= "<br/>備註：".$Remark;
        $message .= "<br/>追蹤碼：".$Smseid;
        if ( $Classif == "A" && $Response_id == 0 )
        {
            return PAY_FAILED;
        }
        return PAY_SUCCESS;
    }

    public function getfields( )
    {
        return array(
            "member_id" => array( "label" => "SmilePay用户ID", "type" => "string" ),
            "PrivateKey" => array( "label" => "私钥", "type" => "string" )
        );
    }

}

?>
