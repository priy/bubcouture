<?php

class ShopInfoService extends BaseService
{
    function init(&$server) 
    {                    
        parent::init($server);
                
        $server->wsdl->addComplexType(
            'ShopInfo',
            'complexType',
            'struct',
            'all',
            '',
            array(
                'timezone' => array('name'=>'timezone','type'=>'xsd:int')
            )
        );
        
        $server->register('GetShopInfo',
            array(),                            
            array('return' => 'tns:ShopInfo'),    
            'urn:shopexapi',                    
            'urn:shopexapi#GetShopInfo',               
            'rpc',                              
            'encoded',                          
            '');                    
    }    
}

function GetShopInfo()
{
    LogUtils::log_str('GetShopInfo Begin');
    $server = &$GLOBALS['as_server'];
    $sys = &$GLOBALS['system'];
    $db = $sys->database();    
    
    $info = array('timezone' => defined('SERVER_TIMEZONE') ? SERVER_TIMEZONE : 8);
    
    LogUtils::log_str('GetShopInfo Return:');
    LogUtils::log_obj($info);
    return $info;
}
?>
