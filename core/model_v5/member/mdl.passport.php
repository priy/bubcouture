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

    public $plugin_type = "file";
    public $plugin_name = "passport";
    public $prefix = "passport.";
    public $_passport = null;

    public function _verify( )
    {
        if ( ( $plugin = $this->getCurrentPlugin( ) ) && $this->getFile( $plugin ) )
        {
            return true;
        }
        return false;
    }

    public function &_load( )
    {
        if ( $plugin = $this->getCurrentPlugin( ) )
        {
            if ( !$this->_passport )
            {
                $obj =& $this->load( $plugin );
                $this->_passport =& $obj;
                if ( method_exists( $obj, "getOptions" ) || method_exists( $obj, "getoptions" ) )
                {
                    $obj->setConfig( $this->getOptions( $plugin, true ) );
                }
            }
            else
            {
                $obj =& $this->_passport;
            }
            return $obj;
        }
    }

    public function login( $userId, $url )
    {
        if ( $this->_verify( ) )
        {
            $obj =& $this->_load( );
            return $obj->login( $userId, $url );
        }
    }

    public function ssoSignin( )
    {
        if ( $this->_verify( ) )
        {
            $obj =& $this->_load( );
            return $obj->ssoSignin( );
        }
    }

    public function logout( $userId, $url )
    {
        if ( $this->_verify( ) )
        {
            $obj =& $this->_load( );
            return $obj->logout( $userId, $url );
        }
    }

    public function regist( $userId, $url )
    {
        if ( $this->_verify( ) )
        {
            $status =& $this->system->loadModel( "system/status" );
            $status->add( "MEMBER_REG" );
            $obj =& $this->_load( );
            return $obj->regist( $userId, $url );
        }
    }

    public function setCurrentPlugin( $plugin = "" )
    {
        return $this->system->setConf( "plugin.".$this->plugin_name.".config.current_use", $plugin );
    }

    public function getCurrentPlugin( )
    {
        return $this->system->getConf( "plugin.".$this->plugin_name.".config.current_use" );
    }

    public function getList( )
    {
        if ( $p = parent::getlist( array( ), false ) )
        {
            $current = $this->getCurrentPlugin( );
            foreach ( $p as $k => $v )
            {
                $p[$k]['ifvalid'] = $current == $k ? "true" : "false";
                $p[$k]['passport_type'] = $p[$k]['name'];
                unset( $p[$k]['name']['name'] );
            }
        }
        return $p;
    }

    public function savePassport( $aData, &$msg )
    {
        if ( !( $sType = $aData['passport_type'] ) )
        {
            trigger_error( __( "参数丢失" ), E_USER_ERROR );
        }
        if ( !$this->saveCfg( $sType, $_POST['config'] ) )
        {
            return false;
        }
        $sCurrentPlugin = $this->getCurrentPlugin( $sType );
        if ( $aData['passport_ifvalid'] == "true" )
        {
            if ( $sType != $sCurrentPlugin && !$this->setCurrentPlugin( $sType ) )
            {
                return false;
            }
        }
        else if ( $aData['passport_ifvalid'] == "false" && $sType == $sCurrentPlugin && !$this->setCurrentPlugin( ) )
        {
            return false;
        }
        if ( $obj = $this->function_judge( "implodeUserToUC" ) )
        {
            $obj->implodeUserToUC( );
        }
        return true;
    }

    public function passport_encrypt( $txt, $key )
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

    public function passport_decrypt( $txt, $key )
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

    public function passport_key( $txt, $encrypt_key )
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

    public function passport_encode( $array )
    {
        $arrayenc = array( );
        foreach ( $array as $key => $val )
        {
            $arrayenc[] = $key."=".urlencode( $val );
        }
        return implode( "&", $arrayenc );
    }

    public function function_judge( $func )
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
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

}

?>
