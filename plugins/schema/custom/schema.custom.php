<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class schema_custom
{

    public $name = "自定义商品类型";
    public $version = "\$Id: schema.php 11689 2008-06-30 10:34:09Z qingo \$";
    public $use_brand = TRUE;
    public $use_params = FALSE;
    public $use_props = TRUE;
    public $use_minfo = FALSE;

    public function init( &$post )
    {
        if ( !isset( $post['is_physical'] ) )
        {
            $post['is_physical'] = TRUE;
        }
        return TRUE;
    }

}

?>
