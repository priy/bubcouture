<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class mdl_point extends modelfactory
{

    function savepointsetting( $aData )
    {
        foreach ( $aData as $k => $v )
        {
            $this->system->setconf( "point.".$k, $v );
        }
        return true;
    }

}

?>
