<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class mdl_math extends modelfactory
{

    var $operationDecimals = 0;
    var $operationCarryset = 0;
    var $goodsShowDecimals = 0;
    var $operationFunc = null;

    function mdl_math( )
    {
        modelfactory::modelfactory( );
        $this->operationDecimals = $this->system->getconf( "system.money.operation.decimals" );
        $this->operationCarryset = $this->system->getconf( "system.money.operation.carryset" );
        $this->goodsShowDecimals = $this->system->getconf( "system.money.decimals" );
        $this->getfunc( );
    }

    function getfunc( )
    {
        switch ( $this->operationCarryset )
        {
        case "0" :
            $this->operationFunc = "round";
            break;
        case "1" :
            $this->operationFunc = "ceil";
            break;
        case "2" :
            $this->operationFunc = "floor";
        }
    }

    function plus( $numbers )
    {
        if ( !is_array( $numbers ) )
        {
            return $this->getoperationnumber( $numbers );
        }
        $rs = 0;
        foreach ( $numbers as $n )
        {
            $rs += $this->getoperationnumber( $n );
        }
        return $rs;
    }

    function minus( $numbers )
    {
        if ( !is_array( $numbers ) )
        {
            return $this->getoperationnumber( $numbers );
        }
        $rs = $this->getoperationnumber( $numbers[0] );
        $i = 1;
        for ( ; $i < count( $numbers ); ++$i )
        {
            $rs -= $this->getoperationnumber( $numbers[$i] );
        }
        return $rs;
    }

    function multiple( $numbers )
    {
        if ( !is_array( $numbers ) )
        {
            return $this->getoperationnumber( $numbers );
        }
        $rs = 1;
        foreach ( $numbers as $n )
        {
            $rs = $this->getoperationnumber( $rs * $this->getoperationnumber( $n ) );
        }
        return $rs;
    }

    function get( $number )
    {
        return call_user_func_array( "floor", $number * pow( 10, $this->goodsShowDecimals ) ) / pow( 10, $this->goodsShowDecimals );
    }

    function getoperationnumber( $number )
    {
        return call_user_func_array( $this->operationFunc, $number * pow( 10, $this->operationDecimals )."" ) / pow( 10, $this->operationDecimals );
    }

}

?>
