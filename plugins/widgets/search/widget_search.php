<?php
function widget_search(&$setting,&$system){
    $setting['search']=$GLOBALS['search'];
    $data=$system->getConf('search.show.range');
    //error_log($data,3,'log.log');
    return $data;
}
?>
