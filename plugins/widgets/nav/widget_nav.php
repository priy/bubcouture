<?php
function widget_nav($setting,&$system,$env){
	foreach($GLOBALS['runtime']['path'] as $key=>&$value){
		if($value["title"] == "首页"){
			$value["title"] = "home";
		}
	}
    return $GLOBALS['runtime']['path'];
}
?>
