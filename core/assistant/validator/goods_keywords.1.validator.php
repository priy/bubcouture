<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class goods_keywords_1Validator extends BaseValidator
{

    public function goods_keywords_1Validator( $sys )
    {
        parent::basevalidator( $sys );
    }

    public function validateInsertBefore( &$row )
    {
        if ( isset( $row['goods_id'] ) && is_numeric( $row['goods_id'] ) && isset( $row['keyword'] ) )
        {
            if ( empty( $row['res_type'] ) )
            {
                $row['res_type'] = "goods";
            }
            $this->_db->exec( "delete from sdb_goods_keywords where goods_id=".( integer )$row['goods_id']." and keyword=".$this->_db->quote( $row['keyword'] ) );
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
