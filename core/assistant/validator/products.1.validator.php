<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class products_1Validator extends BaseValidator
{

    public function products_1Validator( $sys )
    {
        parent::basevalidator( $sys );
    }

    public function validateInsertBefore( &$row )
    {
        $row['disabled'] = isset( $row['disabled'] ) && $row['disabled'] ? "true" : "false";
        $row['marketable'] = isset( $row['marketable'] ) && $row['marketable'] ? "true" : "false";
        $row['price'] = isset( $row['price'] ) ? $row['price'] : 0;
        $row['name'] = isset( $row['name'] ) ? $row['name'] : "";
        $row['last_modify'] = isset( $row['last_modify'] ) && !empty( $row['last_modify'] ) ? intval( $row['last_modify'] ) : time( );
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
        if ( isset( $row['marketable'] ) )
        {
            $row['marketable'] = $row['marketable'] ? "true" : "false";
        }
        if ( isset( $row['last_modify'], $row['last_modify'] ) )
        {
            $row['last_modify'] = time( );
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
