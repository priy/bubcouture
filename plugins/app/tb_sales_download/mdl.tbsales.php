<?php
$mode_dir =  ((!defined('SHOP_DEVELOPER') || !constant('SHOP_DEVELOPER')) && version_compare(PHP_VERSION,'5.0','>=')?'model_v5':'model');
require_once(CORE_DIR.'/'.$mode_dir.'/trading/mdl.order.php');
class mdl_tbsales extends mdl_order{

    function do_rate_sync($rate_data,$order_id){
        $this->system->call("traderate_download",$rate_data,$order_id);   
    }
}
?>