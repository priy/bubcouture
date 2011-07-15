<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class gimages_1Validator extends BaseValidator
{

    public function gimages_1Validator( $sys )
    {
        parent::basevalidator( $sys );
    }

    public function validateInsertBefore( &$row )
    {
        $row['orderby'] = isset( $row['orderby'] ) && $row['orderby'] ? $row['orderby'] : 0;
        $row['src_size_width'] = isset( $row['src_size_width'] ) && $row['src_size_width'] ? $row['src_size_width'] : 0;
        $row['src_size_height'] = isset( $row['src_size_height'] ) && $row['src_size_height'] ? $row['src_size_height'] : 0;
        $row['up_time'] = isset( $row['up_time'] ) && $row['up_time'] ? $row['up_time'] : time( );
        $row['is_remote'] = isset( $row['is_remote'] ) && $row['is_remote'] ? "true" : "false";
        unset( $row['gimage_id'] );
        return TRUE;
    }

    public function validateInsertAfter( &$row )
    {
        return TRUE;
    }

    public function validateUpdateBefore( &$row )
    {
        $row['orderby'] = isset( $row['orderby'] ) && $row['orderby'] ? $row['orderby'] : 0;
        $row['src_size_width'] = isset( $row['src_size_width'] ) && $row['src_size_width'] ? $row['src_size_width'] : 0;
        $row['src_size_height'] = isset( $row['src_size_height'] ) && $row['src_size_height'] ? $row['src_size_height'] : 0;
        $row['up_time'] = isset( $row['up_time'] ) && $row['up_time'] ? $row['up_time'] : time( );
        $row['is_remote'] = isset( $row['is_remote'] ) && $row['is_remote'] ? "true" : "false";
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
