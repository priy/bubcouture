<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class modelFactory
{

    public $system = NULL;
    public $db = NULL;

    public function modelFactory( )
    {
        $this->system =& $system;
        $this->db =& $this->system->database( );
    }

    public function setError( $errorno = 0, $jumpto = "back", $msg, $links = array( ), $time = 3, $js = null )
    {
        $this->system->ErrorSet = array(
            "errorno" => $errorno,
            "message" => $msg,
            "jumpto" => $jumpto,
            "links" => $links,
            "time" => $time,
            "js" => $js
        );
    }

}

?>
