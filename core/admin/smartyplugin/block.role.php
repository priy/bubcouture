<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_block_role( $params, $content, &$smarty, $s )
{
    if ( NULL !== $content )
    {
        $system =& $GLOBALS['GLOBALS']['system'];
        if ( $system->op_is_super )
        {
            return $content;
        }
        $opmod =& $system->loadModel( "admin/operator" );
        $act =& $opmod->getActions( $system->op_id );
        $require = explode( ",", $params['require'] );
        if ( 1 < count( $require ) )
        {
            if ( $params['mode'] == "or" )
            {
                $pass = 0;
                foreach ( $require as $r )
                {
                    if ( isset( $act[$r] ) )
                    {
                        return $content;
                    }
                }
                return;
            }
            else
            {
                foreach ( $require as $r )
                {
                    if ( !isset( $act[$r] ) )
                    {
                        return;
                    }
                }
            }
        }
        else if ( !isset( $act[$require[0]] ) )
        {
            return;
        }
        return $content;
    }
}

?>
