<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class modelfactory
{

    var $system;
    var $db;

    function modelfactory( )
    {
        $this->system =& $GLOBALS['system'];
        $this->db =& $this->system->database( );
    }

    function seterror( $errorno = 0, $jumpto = "back", $msg, $links = array( ), $time = 3, $js = null )
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
