<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "shopCore.php" );
class shoppreview extends shopcore
{

    function run( )
    {
    }

    function setconf( $key, $value )
    {
        $this->__cfg[$key] = $value;
    }

    function getconf( $key )
    {
        if ( isset( $this->__cfg[$key] ) )
        {
            return $this->__cfg[$key];
        }
        return shopcore::getconf( $key );
    }

    function view( $request )
    {
        $this->display( $this->_frontend( $request ) );
    }

}

?>
