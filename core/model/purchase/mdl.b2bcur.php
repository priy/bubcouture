<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class mdl_b2bcur extends modelfactory
{

    function mdl_b2bcur( $system )
    {
        modelfactory::modelfactory( $system );
    }

    function changer( $money, $supplier_id )
    {
        $number .= "";
        $obj_order_po = $this->system->loadmodel( "purchase/order_po" );
        $this->_money_format = $obj_order_po->getordersetting( $supplier_id );
        if ( $this->_money_format['carryset'] )
        {
            $mul = 1;
            $mul = pow( 10, $this->_money_format['decimals'] );
            switch ( $this->_money_format['carryset'] )
            {
            case 0 :
                $money = number_format( trim( $money ), $this->_money_format['decimals'], ".", "" );
                break;
            case 1 :
                $money = ceil( trim( $money ) * $mul ) / $mul;
                break;
            case 2 :
                $money = floor( trim( $money ) * $mul ) / $mul;
            }
        }
        return $this->_money_format['cur_sign'].number_format( $money, $this->_money_format['decimals'], $this->_money_format['dec_point'], $this->_money_format['thousands_sep'] );
    }

    function getorderdecimal( $number, $supplier_id, $currency = "" )
    {
        if ( empty( $currency ) )
        {
            $currency = $this->system->request['cur'];
        }
        if ( $currency || empty( $this->_in_cur['cur_rate'] ) )
        {
            $this->_in_cur = $this->getcur( $currency, true );
        }
        $number .= "";
        $obj_order_po = $this->system->loadmodel( "purchase/order_po" );
        $this->_money_format = $obj_order_po->getordersetting( $supplier_id );
        $decimal_digit = $this->_money_format['decimal_digit'];
        $decimal_type = $this->_money_format['decimal_type'];
        if ( $decimal_digit < 3 )
        {
            $mul = 1;
            $mul = pow( 10, $decimal_digit );
            switch ( $decimal_type )
            {
            case 0 :
                $number = number_format( $number, $decimal_digit, ".", "" );
                break;
            case 1 :
                $number = ceil( $number * $mul ) / $mul;
                break;
            case 2 :
                $number = floor( $number * $mul ) / $mul;
            }
        }
        return $this->_in_cur['cur_sign'].$number;
    }

    function getcur( $id, $getDef = false )
    {
        $aCur = $this->db->selectrow( "select * FROM sdb_currency where cur_code=\"".$id."\"" );
        if ( $aCur['cur_code'] || !$getDef )
        {
            return $this->_in_cur = $aCur;
        }
        return $this->_in_cur = $this->getdefault( );
    }

    function getdefault( )
    {
        if ( $cur = $this->db->selectrow( "select * from sdb_currency where def_cur=1" ) )
        {
            return $cur;
        }
        return $this->db->selectrow( "select * FROM sdb_currency" );
    }

}

?>
