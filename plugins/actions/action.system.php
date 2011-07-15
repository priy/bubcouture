<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class action_system
{

    public $name = "系统触发器可用动作";
    public $action_for = "system";

    public function actions( )
    {
        return array(
            "changelv" => array( "label" => "清除缓存" ),
            "a" => array( "label" => "维护数据库" ),
            "a" => array( "label" => "重新生成商品图片" )
        );
    }

}

?>
