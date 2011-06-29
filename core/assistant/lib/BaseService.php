<?php
class BaseService
{
    function init(&$server) 
    {
        set_error_handler(array(&$this, "baseErrorHandler"));
           
        $server->wsdl->addComplexType(
            'StringArray',
            'complexType',
            'array',
            '',
            'SOAP-ENC:Array',
            array(),
            array(
                array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'xsd:string[]')
            ),
            ''
        );
        $server->wsdl->addComplexType(
            'IntegerArray',
            'complexType',
            'array',
            '',
            'SOAP-ENC:Array',
            array(),
            array(
                array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'xsd:int[]')
            ),
            ''
        );
        $server->wsdl->addComplexType(
            'SyncItem',
            'complexType',
            'struct',
            'all',
            '',
            array(
                'guid' => array('name'=>'guid','type'=>'xsd:string'),                
                'id' => array('name'=>'id','type'=>'xsd:string'),
                'syncstate' => array('name'=>'syncstate','type'=>'xsd:int'),
                'succ' => array('name'=>'succ','type'=>'xsd:boolean'),
                'errmsg' => array('name'=>'errmsg','type'=>'xsd:string')
            )
        );         
        $server->wsdl->addComplexType(
            'SyncItemArray',
            'complexType',
            'array',
            '',
            'SOAP-ENC:Array',
            array(),
            array(
                array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:SyncItem[]')
            ),
            'tns:SyncItem'
        );         
        $server->wsdl->addComplexType(
            'SyncPack',
            'complexType',
            'struct',
            'all',
            '',
            array(                
                'items' => array('name'=>'items','type'=>'tns:SyncItemArray')
            )
        );        
    }
        
    function baseErrorHandler($errno, $errstr, $errfile, $errline){
        switch ($errno){
            case E_ERROR:
            //case E_WARNING:
            case E_PARSE:
            //case E_NOTICE:
            case E_CORE_ERROR:
            //case E_CORE_WARNING:
            case E_COMPILE_ERROR:
            //case E_COMPILE_WARNING:
            case E_USER_ERROR:
            //case E_USER_WARNING:
            //case E_USER_NOTICE:
            //case E_STRICT:
                LogUtils::log_str('[errno:'.$errno.'] '.$errstr);
                while(@ob_end_clean());
                $out = '<?xml version="1.0" encoding="UTF-8"?><SOAP-ENV:Envelope SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/">'.
                    '<SOAP-ENV:Body><SOAP-ENV:Fault><faultcode xsi:type="xsd:int">'.$errno.'</faultcode><faultactor xsi:type="xsd:string"></faultactor><faultstring xsi:type="xsd:string"><![CDATA['.$errstr.']]></faultstring><detail xsi:type="xsd:string"></detail></SOAP-ENV:Fault></SOAP-ENV:Body></SOAP-ENV:Envelope>';
                die($out);
                break;
        }    
    }        
}
?>