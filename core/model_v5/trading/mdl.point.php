<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class mdl_point extends modelFactory
{

    public function savePointSetting( $aData )
    {
        foreach ( $aData as $k => $v )
        {
            $this->system->setConf( "point.".$k, $v );
        }
        return true;
    }

}

?>
