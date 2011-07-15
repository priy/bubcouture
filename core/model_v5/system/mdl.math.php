<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class mdl_math extends modelFactory
{

    public $operationDecimals = 0;
    public $operationCarryset = 0;
    public $goodsShowDecimals = 0;
    public $operationFunc = null;

    public function mdl_math( )
    {
        parent::modelfactory( );
        $this->operationDecimals = $this->system->getConf( "system.money.operation.decimals" );
        $this->operationCarryset = $this->system->getConf( "system.money.operation.carryset" );
        $this->goodsShowDecimals = $this->system->getConf( "system.money.decimals" );
        $this->getFunc( );
    }

    public function getFunc( )
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
            break;
        }
    }

    public function plus( $numbers )
    {
        if ( !is_array( $numbers ) )
        {
            return $this->getOperationNumber( $numbers );
        }
        $rs = 0;
        foreach ( $numbers as $n )
        {
            $rs += $this->getOperationNumber( $n );
        }
        return $rs;
    }

    public function minus( $numbers )
    {
        if ( !is_array( $numbers ) )
        {
            return $this->getOperationNumber( $numbers );
        }
        $rs = $this->getOperationNumber( $numbers[0] );
        $i = 1;
        for ( ; $i < count( $numbers ); ++$i )
        {
            $rs -= $this->getOperationNumber( $numbers[$i] );
        }
        return $rs;
    }

    public function multiple( $numbers )
    {
        if ( !is_array( $numbers ) )
        {
            return $this->getOperationNumber( $numbers );
        }
        $rs = 1;
        foreach ( $numbers as $n )
        {
            $rs = $this->getOperationNumber( $rs * $this->getOperationNumber( $n ) );
        }
        return $rs;
    }

    public function get( $number )
    {
        return call_user_func_array( "floor", $number * pow( 10, $this->goodsShowDecimals ) ) / pow( 10, $this->goodsShowDecimals );
    }

    public function getOperationNumber( $number )
    {
        return call_user_func_array( $this->operationFunc, $number * pow( 10, $this->operationDecimals )."" ) / pow( 10, $this->operationDecimals );
    }

}

?>
