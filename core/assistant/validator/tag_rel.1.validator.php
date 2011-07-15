<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class tag_rel_1Validator extends BaseValidator
{

    public function tag_rel_1Validator( $sys )
    {
        parent::basevalidator( $sys );
    }

    public function validateInsertBefore( &$row )
    {
        if ( isset( $row['tag_id'] ) && is_numeric( $row['tag_id'] ) && isset( $row['rel_id'] ) && is_numeric( $row['rel_id'] ) )
        {
            $this->_db->exec( "delete from sdb_tag_rel where tag_id='".( integer )$row['tag_id']."' and rel_id='".( integer )$row['tag_id']."'" );
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
