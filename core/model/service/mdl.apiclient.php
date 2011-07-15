<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class mdl_apiclient extends modelfactory
{

    var $url = "";
    var $key = "";
    var $response_type = "json";
    var $read_timeout = 5;
    var $_fp_timeout = 3;

    function _verify( )
    {
    }

    function native_svc( $service, $params )
    {
        if ( !$service && $this->url == "" )
        {
            return false;
        }
        $params['service'] = $service;
        $params['response_type'] = $this->response_type;
        if ( !isset( $params['certi_id'] ) && !$params['certi_id'] )
        {
            $oCerti = $this->system->loadmodel( "service/certificate" );
            $params['certi_id'] = $oCerti->getcerti( );
        }
        $aVersion = $this->system->version( );
        $params['shopex_version'] = $aVersion['app'].".".$aVersion['rev'];
        ksort( $params );
        $query = "";
        foreach ( $params as $k => $v )
        {
            $query .= $k."=".$v."&";
        }
        $sign = md5( substr( $query, 0, strlen( $query ) - 1 ).$this->system->getconf( "certificate.token" ) );
        $query .= "sign=".$sign;
        $params['sign'] = $sign;
        $this->net =& $this->system->loadmodel( "utility/http_client" );
        if ( $this->net_result = $this->net->post( $this->url, $params ) )
        {
            if ( substr( $this->net_result, 0, 1 ) == "{" && substr( $this->net_result, -1 ) == "}" )
            {
                if ( $this->response_type == "json" )
                {
                    return json_decode( $this->net_result, true );
                }
                if ( $this->response_type == "serialized" )
                {
                    return unserialize( $this->net_result );
                }
                return false;
            }
            return false;
        }
        return false;
    }

}

?>
