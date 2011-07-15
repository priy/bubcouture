<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class spec_values_1Validator extends BaseValidator
{

    public function spec_values_1Validator( $sys )
    {
        parent::basevalidator( $sys );
    }

    public function validateInsertBefore( &$row )
    {
        if ( isset( $row['spec_id'] ) && is_numeric( $row['spec_id'] ) && isset( $row['spec_value'] ) )
        {
            $this->_db->exec( "delete from sdb_spec_values where spec_id='".( integer )$row['spec_id']."' and spec_value=".$this->_db->quote( $row['spec_value'] ) );
            return TRUE;
        }
        else if ( isset( $row['spec_value'] ) )
        {
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
