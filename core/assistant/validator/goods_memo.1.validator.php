<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class goods_memo_1Validator extends BaseValidator
{

    public function goods_memo_1Validator( $sys )
    {
        parent::basevalidator( $sys );
    }

    public function validateInsertBefore( &$row )
    {
        if ( isset( $row['goods_id'] ) && is_numeric( $row['goods_id'] ) && isset( $row['p_key'] ) )
        {
            $this->_db->exec( "delete from sdb_goods_memo where goods_id=".( integer )$row['goods_id']." and p_key=".$this->_db->quote( $row['p_key'] ) );
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
