<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class mdl_apiclient extends modelFactory
{

    public $url = "";
    public $key = "";
    public $response_type = "json";
    public $read_timeout = 5;
    public $_fp_timeout = 3;

    public function _verify( )
    {
    }

    public function native_svc( $service, $params )
    {
        if ( !$service || $this->url == "" )
        {
            return false;
        }
        $params['service'] = $service;
        $params['response_type'] = $this->response_type;
        if ( !isset( $params['certi_id'] ) || !$params['certi_id'] )
        {
            $oCerti = $this->system->loadModel( "service/certificate" );
            $params['certi_id'] = $oCerti->getCerti( );
        }
        $aVersion = $this->system->version( );
        $params['shopex_version'] = $aVersion['app'].".".$aVersion['rev'];
        ksort( $params );
        $query = "";
        foreach ( $params as $k => $v )
        {
            $query .= $k."=".$v."&";
        }
        $sign = md5( substr( $query, 0, strlen( $query ) - 1 ).$this->system->getConf( "certificate.token" ) );
        $query .= "sign=".$sign;
        $params['sign'] = $sign;
        $this->net =& $this->system->loadModel( "utility/http_client" );
        if ( $this->net_result = $this->net->post( $this->url, $params ) )
        {
            if ( substr( $this->net_result, 0, 1 ) == "{" && substr( $this->net_result, -1 ) == "}" )
            {
                if ( $this->response_type == "json" )
                {
                    return json_decode( $this->net_result, true );
                }
                else if ( $this->response_type == "serialized" )
                {
                    return unserialize( $this->net_result );
                }
                else
                {
                    return false;
                }
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

}

?>
