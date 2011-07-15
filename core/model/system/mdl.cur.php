<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "shopObject.php" );
class mdl_cur extends shopobject
{

    var $tableName = "sdb_currency";
    var $idColumn = "cur_code";
    var $textColumn = "cur_name";

    function mdl_cur( $system )
    {
        shopobject::modelfactory( $system );
        if ( defined( "IN_INSTALLER" ) )
        {
            return;
        }
        $this->_money_format = array(
            "decimals" => $this->system->getconf( "system.money.operation.decimals" ),
            "dec_point" => $this->system->getconf( "system.money.dec_point" ),
            "thousands_sep" => $this->system->getconf( "system.money.thousands_sep" ),
            "fonttend_decimal_type" => $this->system->getconf( "system.money.operation.carryset" ),
            "fonttend_decimal_remain" => $this->system->getconf( "system.money.operation.decimals" )
        );
    }

    function getsyscur( )
    {
        $CON_CURRENCY['CNY'] = __( "人民币" );
        $CON_CURRENCY['USD'] = __( "美元" );
        $CON_CURRENCY['EUR'] = __( "欧元" );
        $CON_CURRENCY['GBP'] = __( "英磅" );
        $CON_CURRENCY['CAD'] = __( "加拿大元" );
        $CON_CURRENCY['AUD'] = __( "澳元" );
        $CON_CURRENCY['RUB'] = __( "卢布" );
        $CON_CURRENCY['HKD'] = __( "港币" );
        $CON_CURRENCY['TWD'] = __( "新台币" );
        $CON_CURRENCY['KRW'] = __( "韩元" );
        $CON_CURRENCY['SGD'] = __( "新加坡元" );
        $CON_CURRENCY['NZD'] = __( "新西兰元" );
        $CON_CURRENCY['JPY'] = __( "日元" );
        $CON_CURRENCY['MYR'] = __( "马元" );
        $CON_CURRENCY['CHF'] = __( "瑞士法郎" );
        $CON_CURRENCY['SEK'] = __( "瑞典克朗" );
        $CON_CURRENCY['DKK'] = __( "丹麦克朗" );
        $CON_CURRENCY['PLZ'] = __( "兹罗提" );
        $CON_CURRENCY['NOK'] = __( "挪威克朗" );
        $CON_CURRENCY['HUF'] = __( "福林" );
        $CON_CURRENCY['CSK'] = __( "捷克克朗" );
        $CON_CURRENCY['MOP'] = __( "葡币" );
        return $CON_CURRENCY;
    }

    function curadd( $data )
    {
        if ( $data['def_cur'] == "true" )
        {
            $sql = "select cur_code from sdb_currency where def_cur=1 and cur_code<>'".$data['cur_code']."'";
            $dat = $this->db->select( $sql );
            if ( !empty( $dat[0]['cur_code'] ) )
            {
                $this->seterror( 2005001 );
                trigger_error( __( "不可重复设定默认货币" ), E_USER_ERROR );
                return false;
            }
        }
        $rs = $this->db->query( "select * from sdb_currency where 0=1" );
        $sql = $this->db->getinsertsql( $rs, $data );
        if ( !$sql && $this->db->query( $sql ) )
        {
            return true;
        }
        $this->seterror( 2005002 );
        trigger_error( __( "数据库插入失败" ) );
        return false;
    }

    function curall( )
    {
        return $this->db->select( "select * from sdb_currency" );
    }

    function curdel( $id )
    {
        $sql = "delete from sdb_currency where cur_code=\"".$id."\"";
        if ( $this->db->exec( $sql ) )
        {
            return true;
        }
        return false;
    }

    function getcur( $id, $getDef = false )
    {
        $aCur = $this->db->selectrow( "select * FROM sdb_currency where cur_code=\"".$id."\"" );
        if ( $aCur['cur_code'] || !$getDef )
        {
            return $this->_in_cur[$id] = $aCur;
        }
        if ( $this->_default_cur )
        {
            return $this->_default_cur;
        }
        $this->_default_cur = $this->getdefault( );
        return $this->_in_cur[$this->_default_cur['cur_code']] =& $this->_default_cur;
    }

    function getdefault( )
    {
        if ( $cur = $this->db->selectrow( "select * from sdb_currency where def_cur=1" ) )
        {
            return $cur;
        }
        return $this->db->selectrow( "select * FROM sdb_currency" );
    }

    function curedit( $id, $data, $old_cur_code )
    {
        if ( $data['def_cur'] == "true" )
        {
            $sql = "select cur_code from sdb_currency where def_cur=1 and cur_code<>'".$old_cur_code."'";
            $dat = $this->db->select( $sql );
            if ( !empty( $dat[0]['cur_code'] ) )
            {
                $this->seterror( 2005003 );
                trigger_error( __( "不可重复设定默认货币" ), E_USER_ERROR );
                return false;
            }
        }
        $rs = $this->db->query( "select * from sdb_currency where cur_code=\"".$old_cur_code."\"" );
        $sql = $this->db->getupdatesql( $rs, $data );
        if ( $sql )
        {
            if ( $this->db->exec( $sql ) )
            {
                return true;
            }
            trigger_error( __( "输入参数有误" ), E_USER_ERROR );
            return false;
        }
        return true;
    }

    function getformat( $cur )
    {
        $ret = array( );
        $cursign = $this->getcur( $cur, true );
        $ret = $this->_money_format;
        $ret['sign'] = $cursign['cur_sign'];
        return $ret;
    }

    function changer( $money, $currency = "", $is_sign = false, $is_rate = false )
    {
        $cur_money = $this->get_cur_money( $money, $currency );
        if ( $is_rate )
        {
            $cur_money = $money;
        }
        if ( $is_sign )
        {
            return $this->formatnumber( $cur_money, false );
        }
        return $this->_in_cur['cur_sign'].$this->formatnumber( $cur_money );
    }

    function get_cur_money( $money, $currency = "" )
    {
        if ( empty( $currency ) )
        {
            $currency = $this->system->request['cur'];
        }
        if ( $currency || empty( $this->_in_cur['cur_rate'] ) )
        {
            $this->_in_cur = $this->getcur( $currency, true );
        }
        return $money * ( $this->_in_cur['cur_rate'] ? $this->_in_cur['cur_rate'] : 1 );
    }

    function amount( $money, $currency = "", $basicFormat = false, $chgval = true, $is_order )
    {
        if ( empty( $currency ) )
        {
            $currency = $this->system->request['cur'];
        }
        if ( $currency || empty( $this->_in_cur['cur_rate'] ) )
        {
            $this->_in_cur = $this->getcur( $currency, true );
        }
        if ( $chgval )
        {
            $money *= $this->_in_cur['cur_rate'] ? $this->_in_cur['cur_rate'] : 1;
        }
        $oMath = $this->system->loadmodel( "system/math" );
        if ( $is_order )
        {
            $this->is_order = true;
            $oMath->operationCarryset = $this->system->getconf( "site.decimal_type" );
            $oMath->getfunc( );
        }
        $money = $oMath->getoperationnumber( $money );
        $money = $this->formatnumber( $money );
        if ( $basicFormat )
        {
            return $money;
        }
        $precision = $this->system->getconf( "site.decimal_digit" );
        $decimal_type = $this->system->getconf( "site.decimal_type" );
        $mul = "1".str_repeat( "0", $precision );
        switch ( $decimal_type )
        {
        case 0 :
            $money = round( $money, $precision );
            break;
        case 1 :
            $money = ceil( trim( $money ) * $mul ) / $mul;
            break;
        case 2 :
            $money = floor( trim( $money ) * $mul ) / $mul;
        }
        return $this->_in_cur['cur_sign'].sprintf( "%.0".$precision."f", $money );
    }

    function formatnumber( $number, $is_str = true )
    {
        $oMath = $this->system->loadmodel( "system/math" );
        if ( $this->is_order )
        {
            $oMath->operationCarryset = $this->system->getconf( "site.decimal_type" );
            $oMath->getfunc( );
        }
        $number = $oMath->getoperationnumber( $number );
        if ( $is_str )
        {
            return number_format( trim( $number ), $this->_money_format['decimals'], $this->_money_format['dec_point'], $this->_money_format['thousands_sep'] );
        }
        return number_format( trim( $number ), $this->_money_format['decimals'], ".", "" );
    }

}

?>
