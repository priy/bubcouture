<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class goods_rate_1Validator extends BaseValidator
{

    public function goods_rate_1Validator( $sys )
    {
        parent::basevalidator( $sys );
    }

    public function validateInsertBefore( &$row )
    {
        if ( isset( $row['goods_1'], $row['goods_2'] ) )
        {
            $this->_db->exec( "delete from sdb_goods_rate where goods_1='".( integer )$row['goods_1']."' and goods_2='".( integer )$row['goods_1']."'" );
        }
        return TRUE;
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
