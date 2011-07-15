<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( "shopCore.php" );
class shopPreview extends shopCore
{

    public function run( )
    {
    }

    public function setConf( $key, $value )
    {
        $this->__cfg[$key] = $value;
    }

    public function getConf( $key )
    {
        if ( isset( $this->__cfg[$key] ) )
        {
            return $this->__cfg[$key];
        }
        else
        {
            return parent::getconf( $key );
        }
    }

    public function view( $request )
    {
        $this->display( $this->_frontend( $request ) );
    }

}

?>
