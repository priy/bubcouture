<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class articles_1Validator extends BaseValidator
{

    public function articles_1Validator( $sys )
    {
        parent::basevalidator( $sys );
    }

    public function validateInsertBefore( &$row )
    {
        if ( !isset( $row['title'] ) || empty( $row['title'] ) )
        {
            return FALSE;
        }
        $row['disabled'] = isset( $row['disabled'] ) && $row['disabled'] ? "true" : "false";
        $row['ifpub'] = isset( $row['ifpub'] ) ? $row['ifpub'] : 1;
        $row['uptime'] = time( );
        return TRUE;
    }

    public function validateInsertAfter( &$row )
    {
        return TRUE;
    }

    public function validateUpdateBefore( &$row )
    {
        if ( isset( $row['disabled'] ) )
        {
            $row['disabled'] = $row['disabled'] ? "true" : "false";
        }
        if ( isset( $row['ifpub'] ) )
        {
            $row['ifpub'] = $row['ifpub'] ? 1 : 0;
        }
        $row['uptime'] = time( );
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
