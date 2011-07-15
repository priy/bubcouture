<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class goods_type_spec_1Validator extends BaseValidator
{

    public function goods_type_spec_1Validator( $sys )
    {
        parent::basevalidator( $sys );
    }

    public function validateInsertBefore( &$row )
    {
        $row['spec_style'] = isset( $row['spec_style'] ) ? $row['spec_type'] : "flat";
        if ( isset( $row['spec_id'], $row['type_id'] ) )
        {
            $this->_db->exec( "delete from sdb_goods_type_spec where spec_id=".intval( $row['spec_id'] )." and type_id=".intval( $row['type_id'] ) );
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
