<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class shop_api_model_object
{

    public function shop_api_model_object( )
    {
        if ( !$this->system )
        {
            $this->system =& $GLOBALS['GLOBALS']['system'];
        }
        if ( !$this->db )
        {
            $this->db = $this->system->database( );
        }
    }

}

?>
