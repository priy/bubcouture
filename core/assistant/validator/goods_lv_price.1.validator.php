<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class goods_lv_price_1Validator extends BaseValidator
{

    public function goods_lv_price_1Validator( $sys )
    {
        parent::basevalidator( $sys );
    }

    public function validateInsertBefore( &$row )
    {
        if ( isset( $row['product_id'] ) && is_numeric( $row['product_id'] ) && isset( $row['level_id'] ) && is_numeric( $row['level_id'] ) && isset( $row['goods_id'] ) && is_numeric( $row['goods_id'] ) )
        {
            $this->_db->exec( "delete from sdb_goods_lv_price where product_id='".( integer )$row['product_id']."' and level_id='".( integer )$row['level_id']."' and goods_id='".( integer )$row['goods_id']."'" );
            return TRUE;
        }
        return FALSE;
    }

    public function validateInsertAfter( &$row )
    {
        return TRUE;
    }

    public function validateUpdateBefore( &$row )
    {
        return TRUE;
    }

    public function validateUpdateAfter( &$row )
    {
        return TRUE;
    }

    public function validateDeleteBefore( &$row )
    {
        return TRUE;
    }

    public function validateDeleteAfter( &$row )
    {
        return TRUE;
    }

}

?>
