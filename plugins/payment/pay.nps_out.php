<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "paymentPlugin.php" );
class pay_nps_out extends paymentPlugin
{

    public $name = "NPS网上支付－外卡";
    public $logo = "NPS_OUT";
    public $version = 20070902;
    public $charset = "gb2312";
    public $submitUrl = "https://payment.nps.cn/ReceiveI18NMerchantOutcardAction.do";
    public $submitButton = "http://img.alipay.com/pimg/button_alipaybutton_o_a.gif";
    public $supportCurrency = array
    (
        "CNY" => "CNY",
        "HKD" => "HKD",
        "USD" => "USD",
        "EUR" => "EUR"
    );
    public $supportArea = array
    (
        0 => "AREA_CNY",
        1 => "AREA_HKD",
        2 => "AREA_USD",
        3 => "AREA_EUR"
    );
    public $intro = "支付过程中如果出现&nbsp;&nbsp;<b>“008错误,请与商家联系”</b>。可能是账号问题，请联系<a href=\"http://www.nps.cn/service/contact.jsp\" target=\"_blank\">NPS官方技术支持</a>解决该问题！";
    public $orderby = 20;
    public $cur_trading = TRUE;
    public $head_charset = "gb2312";

    public function toSubmit( $payment )
    {
        $merId = $this->getConf( $payment['M_OrderId'], "member_id" );
        $ikey = $this->getConf( $payment['M_OrderId'], "PrivateKey" );
        $payment['M_Language'] = "1";
        $state = "0";
        if ( $payment['R_Name'] == "" )
        {
            $payment['R_Name'] = "NA";
        }
        if ( $payment['R_Address'] == "" )
        {
            $payment['R_Address'] = "NA";
        }
        if ( $payment['R_PostCode'] == "" )
        {
            $payment['R_PostCode'] = "NA";
        }
        if ( $payment['R_Telephone'] == "" )
        {
            $payment['R_Telephone'] = "NA";
        }
        if ( $payment['R_Email'] == "" )
        {
            $payment['R_Email'] = "NA";
        }
        $m_info = $merId."|".$payment['M_OrderId']."|".$payment['M_Amount']."|".$payment['M_Currency']."|".$this->callbackUrl."|".$payment['M_Language'];
        $s_info = $this->R_Name."|".$this->R_Address."|".$this->R_PostCode."|".$this->R_Telephone."|".$this->R_Email;
        $r_info = $payment['R_Name']."|".$payment['R_Address']."|".$payment['R_PostCode']."|".$payment['R_Telephone']."|".$payment['R_Email']."|".$payment['M_Remark']."|".$state."|".date( "Ymd", $payment['M_Time'] );
        $OrderInfo = $m_info."|".$s_info."|".$r_info;
        $OrderInfo = $this->stringToHex( $this->des( $ikey, $OrderInfo, 1, 1, NULL ) );
        $digest = md5( $OrderInfo.$ikey );
        $return['M_ID'] = $merId;
        $return['procode'] = "php";
        $return['md5info'] = "null";
        $return['digest'] = $digest;
        $return['OrderMessage'] = $OrderInfo;
        return $return;
    }

    public function callback( $in, &$paymentId, &$money, &$message )
    {
        $OrderInfo = $in['OrderMessage'];
        $signMsg = $in['Digest'];
        $m_id = $in['m_id'];
        $OrderInfo = $this->HexToStr( $OrderInfo );
        $recovered_message = $this->des( $key, $OrderInfo, 0, 1, NULL );
        $orderArray = split( "[|]", $recovered_message );
        $m_id = $orderArray[0];
        $m_orderid = $orderArray[1];
        $m_oamount = $orderArray[2];
        $m_ocurrency = $orderArray[3];
        $m_url = $orderArray[4];
        $m_language = $orderArray[5];
        $s_name = $orderArray[6];
        $s_addr = $orderArray[7];
        $s_postcode = $orderArray[8];
        $s_tel = $orderArray[9];
        $s_eml = $orderArray[10];
        $r_name = $orderArray[11];
        $r_addr = $orderArray[12];
        $r_postcode = $orderArray[13];
        $r_tel = $orderArray[14];
        $r_eml = $orderArray[15];
        $m_ocomment = $orderArray[16];
        $modate = $orderArray[17];
        $Status = $orderArray[18];
        $orderId = $m_orderid;
        $money = $m_oamount;
        $paymentId = $m_orderid;
        $money = $m_oamount;
        $ikey = $this->getConf( $m_orderid, "PrivateKey" );
        $digest = md5( $OrderInfo.$ikey );
        if ( $digest != $signMsg )
        {
            $message = "支付信息不正确，可能被篡改。";
            return PAY_ERROR;
        }
        if ( $Status == 2 )
        {
            return PAY_SUCCESS;
        }
        else
        {
            $message = "更新数据库，支付失败。";
            return PAY_FAILED;
        }
    }

    public function getfields( )
    {
        return array(
            "member_id" => array( "label" => "客户号", "type" => "string" ),
            "PrivateKey" => array( "label" => "私钥", "type" => "string" )
        );
    }

    public function des( $key, $message, $encrypt, $mode, $iv )
    {
        $spfunction1 = array( 16843776, 0, 65536, 16843780, 16842756, 66564, 4, 65536, 1024, 16843776, 16843780, 1024, 16778244, 16842756, 16777216, 4, 1028, 16778240, 16778240, 66560, 66560, 16842752, 16842752, 16778244, 65540, 16777220, 16777220, 65540, 0, 1028, 66564, 16777216, 65536, 16843780, 4, 16842752, 16843776, 16777216, 16777216, 1024, 16842756, 65536, 66560, 16777220, 1024, 4, 16778244, 66564, 16843780, 65540, 16842752, 16778244, 16777220, 1028, 66564, 16843776, 1028, 16778240, 16778240, 0, 65540, 66560, 0, 16842756 );
        $spfunction2 = array( -2146402272, -2147450880, 32768, 1081376, 1048576, 32, -2146435040, -2147450848, -2147483616, -2146402272, -2146402304, -2.14748e+009, -2147450880, 1048576, 32, -2146435040, 1081344, 1048608, -2147450848, 0, -2.14748e+009, 32768, 1081376, -2146435072, 1048608, -2147483616, 0, 1081344, 32800, -2146402304, -2146435072, 32800, 0, 1081376, -2146435040, 1048576, -2147450848, -2146435072, -2146402304, 32768, -2146435072, -2147450880, 32, -2146402272, 1081376, 32, 32768, -2.14748e+009, 32800, -2146402304, 1048576, -2147483616, 1048608, -2147450848, -2147483616, 1048608, 1081344, 0, -2147450880, 32800, -2.14748e+009, -2146435040, -2146402272, 1081344 );
        $spfunction3 = array( 520, 134349312, 0, 134348808, 134218240, 0, 131592, 134218240, 131080, 134217736, 134217736, 131072, 134349320, 131080, 134348800, 520, 134217728, 8, 134349312, 512, 131584, 134348800, 134348808, 131592, 134218248, 131584, 131072, 134218248, 8, 134349320, 512, 134217728, 134349312, 134217728, 131080, 520, 131072, 134349312, 134218240, 0, 512, 131080, 134349320, 134218240, 134217736, 512, 0, 134348808, 134218248, 131072, 134217728, 134349320, 8, 131592, 131584, 134217736, 134348800, 134218248, 520, 134348800, 131592, 8, 134348808, 131584 );
        $spfunction4 = array( 8396801, 8321, 8321, 128, 8396928, 8388737, 8388609, 8193, 0, 8396800, 8396800, 8396929, 129, 0, 8388736, 8388609, 1, 8192, 8388608, 8396801, 128, 8388608, 8193, 8320, 8388737, 1, 8320, 8388736, 8192, 8396928, 8396929, 129, 8388736, 8388609, 8396800, 8396929, 129, 0, 0, 8396800, 8320, 8388736, 8388737, 1, 8396801, 8321, 8321, 128, 8396929, 129, 1, 8192, 8388609, 8193, 8396928, 8388737, 8193, 8320, 8388608, 8396801, 128, 8388608, 8192, 8396928 );
        $spfunction5 = array( 256, 34078976, 34078720, 1107296512, 524288, 256, 1073741824, 34078720, 1074266368, 524288, 33554688, 1074266368, 1107296512, 1107820544, 524544, 1073741824, 33554432, 1074266112, 1074266112, 0, 1073742080, 1107820800, 1107820800, 33554688, 1107820544, 1073742080, 0, 1107296256, 34078976, 33554432, 1107296256, 524544, 524288, 1107296512, 256, 33554432, 1073741824, 34078720, 1107296512, 1074266368, 33554688, 1073741824, 1107820544, 34078976, 1074266368, 256, 33554432, 1107820544, 1107820800, 524544, 1107296256, 1107820800, 34078720, 0, 1074266112, 1107296256, 524544, 33554688, 1073742080, 524288, 0, 1074266112, 34078976, 1073742080 );
        $spfunction6 = array( 536870928, 541065216, 16384, 541081616, 541065216, 16, 541081616, 4194304, 536887296, 4210704, 4194304, 536870928, 4194320, 536887296, 536870912, 16400, 0, 4194320, 536887312, 16384, 4210688, 536887312, 16, 541065232, 541065232, 0, 4210704, 541081600, 16400, 4210688, 541081600, 536870912, 536887296, 16, 541065232, 4210688, 541081616, 4194304, 16400, 536870928, 4194304, 536887296, 536870912, 16400, 536870928, 541081616, 4210688, 541065216, 4210704, 541081600, 0, 541065232, 16, 16384, 541065216, 4210704, 16384, 4194320, 536887312, 0, 541081600, 536870912, 4194320, 536887312 );
        $spfunction7 = array( 2097152, 69206018, 67110914, 0, 2048, 67110914, 2099202, 69208064, 69208066, 2097152, 0, 67108866, 2, 67108864, 69206018, 2050, 67110912, 2099202, 2097154, 67110912, 67108866, 69206016, 69208064, 2097154, 69206016, 2048, 2050, 69208066, 2099200, 2, 67108864, 2099200, 67108864, 2099200, 2097152, 67110914, 67110914, 69206018, 69206018, 2, 2097154, 67108864, 67110912, 2097152, 69208064, 2050, 2099202, 69208064, 2050, 67108866, 69208066, 69206016, 2099200, 0, 2, 69208066, 0, 2099202, 69206016, 2048, 67108866, 67110912, 2048, 2097154 );
        $spfunction8 = array( 268439616, 4096, 262144, 268701760, 268435456, 268439616, 64, 268435456, 262208, 268697600, 268701760, 266240, 268701696, 266304, 4096, 64, 268697600, 268435520, 268439552, 4160, 266240, 262208, 268697664, 268701696, 4160, 0, 0, 268697664, 268435520, 268439552, 266304, 262144, 266304, 262144, 268701696, 4096, 64, 268697664, 4096, 266304, 268439552, 64, 268435520, 268697600, 268697664, 268435456, 262144, 268439616, 0, 268701760, 262208, 268435520, 268697600, 268439552, 268439616, 0, 268701760, 266240, 266240, 4160, 4160, 262208, 268435456, 268701696 );
        $masks = array( 4.29497e+009, 2147483647, 1073741823, 536870911, 268435455, 134217727, 67108863, 33554431, 16777215, 8388607, 4194303, 2097151, 1048575, 524287, 262143, 131071, 65535, 32767, 16383, 8191, 4095, 2047, 1023, 511, 255, 127, 63, 31, 15, 7, 3, 1, 0 );
        $key = $this->HexToStr( $key );
        $keys = $this->des_createKeys( $key );
        $m = 0;
        $len = strlen( $message );
        $chunk = 0;
        $iterations = count( $keys ) == 32 ? 3 : 9;
        if ( $iterations == 3 )
        {
            $looping = $encrypt ? array( 0, 32, 2 ) : array( 30, -2, -2 );
        }
        else
        {
            $looping = $encrypt ? array( 0, 32, 2, 62, 30, -2, 64, 96, 2 ) : array( 94, 62, -2, 32, 64, 2, 30, -2, -2 );
        }
        $message .= chr( 0 ).chr( 0 ).chr( 0 ).chr( 0 ).chr( 0 ).chr( 0 ).chr( 0 ).chr( 0 );
        $result = "";
        $tempresult = "";
        if ( $mode == 1 )
        {
            $cbcleft = ord( $iv[$m++] ) << 24 | ord( $iv[$m++] ) << 16 | ord( $iv[$m++] ) << 8 | ord( $iv[$m++] );
            $cbcright = ord( $iv[$m++] ) << 24 | ord( $iv[$m++] ) << 16 | ord( $iv[$m++] ) << 8 | ord( $iv[$m++] );
            $m = 0;
        }
        while ( $m < $len )
        {
            $left = ord( $message[$m++] ) << 24 | ord( $message[$m++] ) << 16 | ord( $message[$m++] ) << 8 | ord( $message[$m++] );
            $right = ord( $message[$m++] ) << 24 | ord( $message[$m++] ) << 16 | ord( $message[$m++] ) << 8 | ord( $message[$m++] );
            if ( $mode == 1 )
            {
                if ( $encrypt )
                {
                    $left ^= $cbcleft;
                    $right ^= $cbcright;
                }
                else
                {
                    $cbcleft2 = $cbcleft;
                    $cbcright2 = $cbcright;
                    $cbcleft = $left;
                    $cbcright = $right;
                }
            }
            $temp = ( $left >> 4 & $masks[4] ^ $right ) & 252645135;
            $right ^= $temp;
            $left ^= $temp << 4;
            $temp = ( $left >> 16 & $masks[16] ^ $right ) & 65535;
            $right ^= $temp;
            $left ^= $temp << 16;
            $temp = ( $right >> 2 & $masks[2] ^ $left ) & 858993459;
            $left ^= $temp;
            $right ^= $temp << 2;
            $temp = ( $right >> 8 & $masks[8] ^ $left ) & 16711935;
            $left ^= $temp;
            $right ^= $temp << 8;
            $temp = ( $left >> 1 & $masks[1] ^ $right ) & 1431655765;
            $right ^= $temp;
            $left ^= $temp << 1;
            $left = $left << 1 | $left >> 31 & $masks[31];
            $right = $right << 1 | $right >> 31 & $masks[31];
            $j = 0;
            for ( ; $j < $iterations; $j += 3 )
            {
                $endloop = $looping[$j + 1];
                $loopinc = $looping[$j + 2];
                $i = $looping[$j];
                for ( ; $i != $endloop; $i += $loopinc )
                {
                    $right1 = $right ^ $keys[$i];
                    $right2 = ( $right >> 4 & $masks[4] | $right << 28 ) ^ $keys[$i + 1];
                    $temp = $left;
                    $left = $right;
                    $right = $temp ^ ( $spfunction2[$right1 >> 24 & $masks[24] & 63] | $spfunction4[$right1 >> 16 & $masks[16] & 63] | $spfunction6[$right1 >> 8 & $masks[8] & 63] | $spfunction8[$right1 & 63] | $spfunction1[$right2 >> 24 & $masks[24] & 63] | $spfunction3[$right2 >> 16 & $masks[16] & 63] | $spfunction5[$right2 >> 8 & $masks[8] & 63] | $spfunction7[$right2 & 63] );
                }
                $temp = $left;
                $left = $right;
                $right = $temp;
            }
            $left = $left >> 1 & $masks[1] | $left << 31;
            $right = $right >> 1 & $masks[1] | $right << 31;
            $temp = ( $left >> 1 & $masks[1] ^ $right ) & 1431655765;
            $right ^= $temp;
            $left ^= $temp << 1;
            $temp = ( $right >> 8 & $masks[8] ^ $left ) & 16711935;
            $left ^= $temp;
            $right ^= $temp << 8;
            $temp = ( $right >> 2 & $masks[2] ^ $left ) & 858993459;
            $left ^= $temp;
            $right ^= $temp << 2;
            $temp = ( $left >> 16 & $masks[16] ^ $right ) & 65535;
            $right ^= $temp;
            $left ^= $temp << 16;
            $temp = ( $left >> 4 & $masks[4] ^ $right ) & 252645135;
            $right ^= $temp;
            $left ^= $temp << 4;
            if ( $mode == 1 )
            {
                if ( $encrypt )
                {
                    $cbcleft = $left;
                    $cbcright = $right;
                }
                else
                {
                    $left ^= $cbcleft2;
                    $right ^= $cbcright2;
                }
            }
            $tempresult .= chr( $left >> 24 & $masks[24] ).chr( $left >> 16 & $masks[16] & 255 ).chr( $left >> 8 & $masks[8] & 255 ).chr( $left & 255 ).chr( $right >> 24 & $masks[24] ).chr( $right >> 16 & $masks[16] & 255 ).chr( $right >> 8 & $masks[8] & 255 ).chr( $right & 255 );
            $chunk += 8;
            if ( $chunk == 512 )
            {
                $result .= $tempresult;
                $tempresult = "";
                $chunk = 0;
            }
        }
        return $result.$tempresult;
    }

    public function des_createKeys( $key )
    {
        $pc2bytes0 = array( 0, 4, 536870912, 536870916, 65536, 65540, 536936448, 536936452, 512, 516, 536871424, 536871428, 66048, 66052, 536936960, 536936964 );
        $pc2bytes1 = array( 0, 1, 1048576, 1048577, 67108864, 67108865, 68157440, 68157441, 256, 257, 1048832, 1048833, 67109120, 67109121, 68157696, 68157697 );
        $pc2bytes2 = array( 0, 8, 2048, 2056, 16777216, 16777224, 16779264, 16779272, 0, 8, 2048, 2056, 16777216, 16777224, 16779264, 16779272 );
        $pc2bytes3 = array( 0, 2097152, 134217728, 136314880, 8192, 2105344, 134225920, 136323072, 131072, 2228224, 134348800, 136445952, 139264, 2236416, 134356992, 136454144 );
        $pc2bytes4 = array( 0, 262144, 16, 262160, 0, 262144, 16, 262160, 4096, 266240, 4112, 266256, 4096, 266240, 4112, 266256 );
        $pc2bytes5 = array( 0, 1024, 32, 1056, 0, 1024, 32, 1056, 33554432, 33555456, 33554464, 33555488, 33554432, 33555456, 33554464, 33555488 );
        $pc2bytes6 = array( 0, 268435456, 524288, 268959744, 2, 268435458, 524290, 268959746, 0, 268435456, 524288, 268959744, 2, 268435458, 524290, 268959746 );
        $pc2bytes7 = array( 0, 65536, 2048, 67584, 536870912, 536936448, 536872960, 536938496, 131072, 196608, 133120, 198656, 537001984, 537067520, 537004032, 537069568 );
        $pc2bytes8 = array( 0, 262144, 0, 262144, 2, 262146, 2, 262146, 33554432, 33816576, 33554432, 33816576, 33554434, 33816578, 33554434, 33816578 );
        $pc2bytes9 = array( 0, 268435456, 8, 268435464, 0, 268435456, 8, 268435464, 1024, 268436480, 1032, 268436488, 1024, 268436480, 1032, 268436488 );
        $pc2bytes10 = array( 0, 32, 0, 32, 1048576, 1048608, 1048576, 1048608, 8192, 8224, 8192, 8224, 1056768, 1056800, 1056768, 1056800 );
        $pc2bytes11 = array( 0, 16777216, 512, 16777728, 2097152, 18874368, 2097664, 18874880, 67108864, 83886080, 67109376, 83886592, 69206016, 85983232, 69206528, 85983744 );
        $pc2bytes12 = array( 0, 4096, 134217728, 134221824, 524288, 528384, 134742016, 134746112, 16, 4112, 134217744, 134221840, 524304, 528400, 134742032, 134746128 );
        $pc2bytes13 = array( 0, 4, 256, 260, 0, 4, 256, 260, 1, 5, 257, 261, 1, 5, 257, 261 );
        $masks = array( 4.29497e+009, 2147483647, 1073741823, 536870911, 268435455, 134217727, 67108863, 33554431, 16777215, 8388607, 4194303, 2097151, 1048575, 524287, 262143, 131071, 65535, 32767, 16383, 8191, 4095, 2047, 1023, 511, 255, 127, 63, 31, 15, 7, 3, 1, 0 );
        $iterations = 24 <= strlen( $key ) ? 3 : 1;
        $keys = array( );
        $shifts = array( 0, 0, 1, 1, 1, 1, 1, 1, 0, 1, 1, 1, 1, 1, 1, 0 );
        $m = 0;
        $n = 0;
        $j = 0;
        for ( ; $j < $iterations; ++$j )
        {
            $left = ord( $key[$m++] ) << 24 | ord( $key[$m++] ) << 16 | ord( $key[$m++] ) << 8 | ord( $key[$m++] );
            $right = ord( $key[$m++] ) << 24 | ord( $key[$m++] ) << 16 | ord( $key[$m++] ) << 8 | ord( $key[$m++] );
            $temp = ( $left >> 4 & $masks[4] ^ $right ) & 252645135;
            $right ^= $temp;
            $left ^= $temp << 4;
            $temp = ( $right >> 16 & $masks[16] ^ $left ) & 65535;
            $left ^= $temp;
            $right ^= $temp << -16;
            $temp = ( $left >> 2 & $masks[2] ^ $right ) & 858993459;
            $right ^= $temp;
            $left ^= $temp << 2;
            $temp = ( $right >> 16 & $masks[16] ^ $left ) & 65535;
            $left ^= $temp;
            $right ^= $temp << -16;
            $temp = ( $left >> 1 & $masks[1] ^ $right ) & 1431655765;
            $right ^= $temp;
            $left ^= $temp << 1;
            $temp = ( $right >> 8 & $masks[8] ^ $left ) & 16711935;
            $left ^= $temp;
            $right ^= $temp << 8;
            $temp = ( $left >> 1 & $masks[1] ^ $right ) & 1431655765;
            $right ^= $temp;
            $left ^= $temp << 1;
            $temp = $left << 8 | $right >> 20 & $masks[20] & 240;
            $left = $right << 24 | $right << 8 & 16711680 | $right >> 8 & $masks[8] & 65280 | $right >> 24 & $masks[24] & 240;
            $right = $temp;
            $i = 0;
            for ( ; $i < count( $shifts ); ++$i )
            {
                if ( 0 < $shifts[$i] )
                {
                    $left = $left << 2 | $left >> 26 & $masks[26];
                    $right = $right << 2 | $right >> 26 & $masks[26];
                }
                else
                {
                    $left = $left << 1 | $left >> 27 & $masks[27];
                    $right = $right << 1 | $right >> 27 & $masks[27];
                }
                $left &= -15;
                $right &= -15;
                $lefttemp = $pc2bytes0[$left >> 28 & $masks[28]] | $pc2bytes1[$left >> 24 & $masks[24] & 15] | $pc2bytes2[$left >> 20 & $masks[20] & 15] | $pc2bytes3[$left >> 16 & $masks[16] & 15] | $pc2bytes4[$left >> 12 & $masks[12] & 15] | $pc2bytes5[$left >> 8 & $masks[8] & 15] | $pc2bytes6[$left >> 4 & $masks[4] & 15];
                $righttemp = $pc2bytes7[$right >> 28 & $masks[28]] | $pc2bytes8[$right >> 24 & $masks[24] & 15] | $pc2bytes9[$right >> 20 & $masks[20] & 15] | $pc2bytes10[$right >> 16 & $masks[16] & 15] | $pc2bytes11[$right >> 12 & $masks[12] & 15] | $pc2bytes12[$right >> 8 & $masks[8] & 15] | $pc2bytes13[$right >> 4 & $masks[4] & 15];
                $temp = ( $righttemp >> 16 & $masks[16] ^ $lefttemp ) & 65535;
                $keys[$n++] = $lefttemp ^ $temp;
                $keys[$n++] = $righttemp ^ $temp << 16;
            }
        }
        return $keys;
    }

    public function stringToHex( $s )
    {
        $r = "";
        $hexes = array( "0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f" );
        $i = 0;
        for ( ; $i < strlen( $s ); ++$i )
        {
            $r .= $hexes[ord( $s[$i] ) >> 4].$hexes[ord( $s[$i] ) & 15];
        }
        return $r;
    }

    public function HexToStr( $hex )
    {
        $string = "";
        $i = 0;
        for ( ; $i < strlen( $hex ) - 1; $i += 2 )
        {
            $string .= chr( hexdec( $hex[$i].$hex[$i + 1] ) );
        }
        return $string;
    }

    public function StrToHex( $string )
    {
        $hex = "";
        $i = 0;
        for ( ; $i < strlen( $string ); ++$i )
        {
            $hex .= dechex( ord( $string[$i] ) );
        }
        $hex = strtoupper( $hex );
        return $hex;
    }

}

?>
