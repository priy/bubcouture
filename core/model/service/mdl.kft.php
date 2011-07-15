<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class mdl_kft extends modelfactory
{

    function getcerti( )
    {
        if ( $this->system->getconf( "certificate.id" ) )
        {
            return $this->system->getconf( "certificate.id" );
        }
        return false;
    }

    function gettoken( )
    {
        if ( $this->system->getconf( "certificate.token" ) )
        {
            return $this->system->getconf( "certificate.token" );
        }
        return false;
    }

    function getaction( )
    {
        if ( $this->system->getconf( "certificate.kft.action" ) )
        {
            return $this->system->getconf( "certificate.kft.action" );
        }
        return false;
    }

    function setkfturl( $url )
    {
        $this->system->setconf( "certificate.kft.url", $url );
    }

    function getkfturl( )
    {
        if ( $this->system->getconf( "certificate.kft.url" ) )
        {
            return $this->system->getconf( "certificate.kft.url" );
        }
        return false;
    }

    function setaction( $action )
    {
        $this->system->setconf( "certificate.kft.action", $action );
    }

    function apply( $url, $function, $arr )
    {
        $check = "ShopEx_API";
        $submit['certi_id'] = $this->getcerti( );
        $submit['function'] = $function;
        foreach ( $arr as $index => $value )
        {
            $submit[$index] = $value;
        }
        $submit['ac'] = md5( $submit['certi_id'].$this->gettoken( ).$check );
        $result = $this->submit( $url, $submit );
        return $result;
    }

    function submit( $url, $submit )
    {
        $net =& $this->system->loadmodel( "utility/http_client" );
        return $this->net->post( $url, $submit );
    }

    function checkstr( $str )
    {
        $tmp = explode( "||", $str );
        return $tmp[1];
    }

    function checklicense( )
    {
        if ( $this->getcerti( ) && $this->gettoken( ) )
        {
            return true;
        }
        return false;
    }

}

?>
