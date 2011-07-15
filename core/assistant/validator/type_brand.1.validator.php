<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class type_brand_1Validator extends BaseValidator
{

    public function type_brand_1Validator( $sys )
    {
        parent::basevalidator( $sys );
    }

    public function validateInsertBefore( &$row )
    {
        if ( isset( $row['type_id'], $row['brand_id'] ) )
        {
            $this->_db->exec( "delete from sdb_type_brand where type_id=".intval( $row['type_id'] )." and brand_id=".intval( $row['brand_id'] ) );
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
