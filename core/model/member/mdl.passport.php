<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require_once( "plugin.php" );
class mdl_passport extends plugin
{

    var $plugin_type = "file";
    var $plugin_name = "passport";
    var $prefix = "passport.";
    var $_passport = null;

    function _verify( )
    {
        if ( ( $plugin = $this->getcurrentplugin( ) ) && $this->getfile( $plugin ) )
        {
            return true;
        }
        return false;
    }

    function &_load( )
    {
        if ( $plugin = $this->getcurrentplugin( ) )
        {
            if ( !$this->_passport )
            {
                $obj =& $this->load( $plugin );
                $this->_passport =& $obj;
                if ( method_exists( $obj, "getOptions" ) || method_exists( $obj, "getoptions" ) )
                {
                    $obj->setconfig( $this->getoptions( $plugin, true ) );
                    return $obj;
                }
            }
            else
            {
                $obj =& $this->_passport;
            }
            return $obj;
        }
    }

    function login( $userId, $url )
    {
        if ( $this->_verify( ) )
        {
            $obj =& $this->_load( );
            return $obj->login( $userId, $url );
        }
    }

    function ssosignin( )
    {
        if ( $this->_verify( ) )
        {
            $obj =& $this->_load( );
            return $obj->ssosignin( );
        }
    }

    function logout( $userId, $url )
    {
        if ( $this->_verify( ) )
        {
            $obj =& $this->_load( );
            return $obj->logout( $userId, $url );
        }
    }

    function regist( $userId, $url )
    {
        if ( $this->_verify( ) )
        {
            $status =& $this->system->loadmodel( "system/status" );
            $status->add( "MEMBER_REG" );
            $obj =& $this->_load( );
            return $obj->regist( $userId, $url );
        }
    }

    function setcurrentplugin( $plugin = "" )
    {
        return $this->system->setconf( "plugin.".$this->plugin_name.".config.current_use", $plugin );
    }

    function getcurrentplugin( )
    {
        return $this->system->getconf( "plugin.".$this->plugin_name.".config.current_use" );
    }

    function getlist( )
    {
        if ( $p = plugin::getlist( array( ), false ) )
        {
            $current = $this->getcurrentplugin( );
            foreach ( $p as $k => $v )
            {
                $p[$k]['ifvalid'] = $current == $k ? "true" : "false";
                $p[$k]['passport_type'] = $p[$k]['name'];
                unset( $p[$k]['passport_type']->'name' );
            }
        }
        return $p;
    }

    function savepassport( $aData, &$msg )
    {
        if ( !( $sType = $aData['passport_type'] ) )
        {
            trigger_error( __( "参数丢失" ), E_USER_ERROR );
        }
        if ( !$this->savecfg( $sType, $_POST['config'] ) )
        {
            return false;
        }
        $sCurrentPlugin = $this->getcurrentplugin( $sType );
        if ( $aData['passport_ifvalid'] == "true" )
        {
            if ( $sType != $sCurrentPlugin && !$this->setcurrentplugin( $sType ) )
            {
                return false;
            }
        }
        else
        {
            if ( $aData['passport_ifvalid'] == "false" && $sType == $sCurrentPlugin && !$this->setcurrentplugin( ) )
            {
                return false;
            }
        }
        if ( $obj = $this->function_judge( "implodeUserToUC" ) )
        {
            $obj->implodeusertouc( );
        }
        return true;
    }

    function passport_encrypt( $txt, $key )
    {
        srand( ( double )microtime( ) * 1000000 );
        $encrypt_key = md5( rand( 0, 32000 ) );
        $ctr = 0;
        $tmp = "";
        $i = 0;
        for ( ; $i < strlen( $txt ); ++$i )
        {
            $ctr = $ctr == strlen( $encrypt_key ) ? 0 : $ctr;
            $tmp .= $encrypt_key[$ctr].( $txt[$i] ^ $encrypt_key[$ctr++] );
        }
        return base64_encode( $this->passport_key( $tmp, $key ) );
    }

    function passport_decrypt( $txt, $key )
    {
        $txt = $this->passport_key( base64_decode( $txt ), $key );
        $tmp = "";
        $i = 0;
        for ( ; $i < strlen( $txt ); ++$i )
        {
            $md5 = $txt[$i];
            $tmp .= $txt[++$i] ^ $md5;
        }
        return $tmp;
    }

    function passport_key( $txt, $encrypt_key )
    {
        $encrypt_key = md5( $encrypt_key );
        $ctr = 0;
        $tmp = "";
        $i = 0;
        for ( ; $i < strlen( $txt ); ++$i )
        {
            $ctr = $ctr == strlen( $encrypt_key ) ? 0 : $ctr;
            $tmp .= $txt[$i] ^ $encrypt_key[$ctr++];
        }
        return $tmp;
    }

    function passport_encode( $array )
    {
        $arrayenc = array( );
        foreach ( $array as $key => $val )
        {
            $arrayenc[] = $key."=".urlencode( $val );
        }
        return implode( "&", $arrayenc );
    }

    function function_judge( $func )
    {
        if ( $this->_verify( ) )
        {
            $obj =& $this->_load( );
        }
        if ( is_object( $obj ) )
        {
            if ( method_exists( $obj, $func ) )
            {
                return $obj;
            }
            return false;
        }
        return false;
    }

}

?>
