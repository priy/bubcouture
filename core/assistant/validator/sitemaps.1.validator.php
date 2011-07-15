<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class sitemaps_1Validator extends BaseValidator
{

    public function sitemaps_1Validator( $sys )
    {
        parent::basevalidator( $sys );
    }

    public function validateInsertBefore( &$row )
    {
        if ( !isset( $row['title'] ) || empty( $row['title'] ) )
        {
            return FALSE;
        }
        $row['hidden'] = isset( $row['hidden'] ) && $row['hidden'] ? "true" : "false";
        $row['manual'] = isset( $row['manual'] ) && $row['manual'] ? 1 : 0;
        $row['p_node_id'] = isset( $row['p_node_id'] ) ? $row['p_node_id'] : 0;
        return TRUE;
    }

    public function validateInsertAfter( &$row )
    {
        return TRUE;
    }

    public function validateUpdateBefore( &$row )
    {
        if ( isset( $row['hidden'] ) )
        {
            $row['hidden'] = $row['hidden'] ? "true" : "false";
        }
        if ( isset( $row['manual'] ) )
        {
            $row['manual'] = $row['manual'] ? 1 : 0;
        }
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
