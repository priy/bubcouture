<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class goods_spec_index_1Validator extends BaseValidator
{

    public function goods_spec_index_1Validator( $sys )
    {
        parent::basevalidator( $sys );
    }

    public function validateInsertBefore( &$row )
    {
        if ( isset( $row['product_id'], $row['spec_value_id'] ) )
        {
            $this->_db->exec( "delete from sdb_goods_spec_index where product_id=".intval( $row['product_id'] )." and spec_value_id=".intval( $row['spec_value_id'] ) );
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
