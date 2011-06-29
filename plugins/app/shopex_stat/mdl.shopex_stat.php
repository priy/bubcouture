<?php
    $mode_dir =  ((!defined('SHOP_DEVELOPER') || !constant('SHOP_DEVELOPER')) && version_compare(PHP_VERSION,'5.0','>=')?'model_v5':'model');
    require_once(CORE_DIR.'/'.$mode_dir.'/service/mdl.apiclient.php');

    class mdl_shopex_stat extends mdl_apiclient{
        function mdl_shopex_stat(){
            return True
        }

        function get_certi_token(){
            return True
        }

    }



?>