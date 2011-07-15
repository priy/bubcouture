<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

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
    return base64_encode( passport_key( $tmp, $key ) );
}

function passport_decrypt( $txt, $key )
{
    $txt = passport_key( base64_decode( $txt ), $key );
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

class passport_discuz extends modelFactory
{

    public $passport_name = "Discuz论坛V6.0";
    public $passport_memo = "";
    public $_config = NULL;
    public $forward = 1;

    public function setConfig( $config )
    {
        $this->_config = $config;
    }

    public function verifylogin( $login, $passwd )
    {
    }

    public function decode( $responseData )
    {
    }

    public function getoptions( )
    {
        return array(
            "URL" => array( "label" => "论坛系统URL：", "type" => "input", "required" => "true" ),
            "PrivateKey" => array( "label" => "连接私钥：", "type" => "input" ),
            "encoding" => array(
                "label" => "论坛系统编码：",
                "type" => "select",
                "options" => array( "utf8" => "国际化编码(utf-8)", "zh" => "简体中文", "big5" => "繁体中文", "en" => "英文" )
            )
        );
    }

    public function validKey( )
    {
        $passport_key = $this->system->getConf( "plugin.passport.discuz.config.PrivateKey" );
        if ( $_GET['verify'] != md5( $_GET['action'].$_GET['auth'].$_GET['forward'].$passport_key ) )
        {
            return FALSE;
        }
        return $passport_key;
    }

    public function ssoSignin( )
    {
        switch ( $_GET['action'] )
        {
        case "login" :
            return $this->ssoLogin( );
        case "logout" :
            return $this->ssoLoginOut( );
        }
    }

    public function ssoLoginOut( )
    {
        return FALSE;
    }

    public function ssoLogin( )
    {
        $passport_key = $this->validKey( );
        if ( !$passport_key )
        {
            return FALSE;
        }
        $oPassport = $this->system->loadModel( "member/passport" );
        parse_str( $oPassport->passport_decrypt( $_GET['auth'], $passport_key ), $member );
        if ( ( $UN_1 || $SITE_EODING == "UTF-8" ) && $this->_config['encoding'] != "utf8" )
        {
            $charset = $this->system->loadModel( "utility/charset" );
            foreach ( $member as $index => $value )
            {
                $member[$index] = $charset->local2utf( $value );
            }
        }
        $memberObj = $this->system->loadModel( "member/account" );
        $info = $memberObj->verifyPassportLogin( $member );
        if ( !$info )
        {
            $info = $memberObj->createPassport( $member );
            if ( !$info )
            {
                return FALSE;
            }
            return $memberObj->toLogin( $member );
        }
        return $memberObj->toLogin( $member );
    }

    public function login( $userId, $rurl )
    {
        $oMember = $this->system->loadModel( "member/member" );
        $aMember = $oMember->getFieldById( $userId );
        $username = $aMember['uname'];
        $charset = $this->system->loadModel( "utility/charset" );
        if ( ( $UN_1 || $SITE_EODING == "UTF-8" ) && $this->_config['encoding'] != "utf8" )
        {
            $username = $charset->utf2local( $username, $this->_config['encoding'] );
        }
        $member = array(
            "cookietime" => 31536000,
            "time" => time( ),
            "username" => $username,
            "password" => $aMember['password'],
            "gender" => $aMember['sex'],
            "email" => $aMember['email'],
            "credits" => $aMember['point'],
            "regip" => $aMember['reg_ip'],
            "regdate" => $aMember['regtime'],
            "qq" => ""
        );
        $auth = passport_encrypt( passport_encode( $member ), $this->_config['PrivateKey'] );
        if ( substr( $this->_config['URL'], -1 ) == "/" )
        {
            $shop_loginapi_url = $this->_config['URL']."api/shopex.php";
        }
        else if ( strtoupper( substr( $this->_config['URL'], -10, 6 ) ) == "SHOPEX" )
        {
            $shop_loginapi_url = $this->_config['URL'];
        }
        else
        {
            $shop_loginapi_url = $this->_config['URL']."/api/shopex.php";
        }
        header( "Location: ".$shop_loginapi_url."?action=login&auth=".rawurlencode( $auth )."&forward=".rawurlencode( $rurl )."&verify=".md5( "login".$auth.$rurl.$this->_config['PrivateKey'] ) );
        exit( );
    }

    public function regist( $userId, $rurl )
    {
        $oMember = $this->system->loadModel( "member/member" );
        $charset = $this->system->loadModel( "utility/charset" );
        $aMember = $oMember->getFieldById( $userId );
        $username = $aMember['uname'];
        if ( ( $UN_1 || $SITE_EODING == "UTF-8" ) && $this->_config['encoding'] != "utf8" )
        {
            $username = $charset->utf2local( $username, $this->_config['encoding'] );
        }
        $member = array(
            "cookietime" => 31536000,
            "time" => time( ),
            "username" => $username,
            "password" => $aMember['password'],
            "gender" => $aMember['sex'],
            "email" => $aMember['email'],
            "credits" => $aMember['point'],
            "regip" => $aMember['reg_ip'],
            "regdate" => $aMember['regtime'],
            "qq" => ""
        );
        $rurl .= "index.php?passport-create.html";
        $this->setPlugCookie( 1 );
        $auth = passport_encrypt( passport_encode( $member ), $this->_config['PrivateKey'] );
        $shop_loginapi_url = substr( $this->_config['URL'], -1 ) == "/" ? $this->_config['URL']."api/shopex.php" : $this->_config['URL'];
        header( "Location: ".$shop_loginapi_url."?action=login&auth=".rawurlencode( $auth )."&forward=".rawurlencode( $rurl )."&verify=".md5( "login".$auth.$rurl.$this->_config['PrivateKey'] ) );
        exit( );
    }

    public function logout( $userId, $rurl )
    {
        $shop_loginapi_url = substr( $this->_config['URL'], -1 ) == "/" ? $this->_config['URL']."api/shopex.php" : $this->_config['URL'];
        header( "Location: ".$shop_loginapi_url."?action=logout&forward=".rawurlencode( $rurl )."&verify=".md5( "logout".$rurl.$this->_config['PrivateKey'] ) );
        exit( );
    }

    public function getPlugCookie( $cName = "CType" )
    {
        $account = $this->system->loadModel( "member/account" );
        return $account->getPlugCookie( $cName );
    }

    public function setPlugCookie( $val )
    {
        $account = $this->system->loadModel( "member/account" );
        if ( $val )
        {
            $account->setPlugCookie( "CType", "discuzz" );
            $account->setPlugCookie( "Common", 1 );
        }
        else
        {
            $account->setPlugCookie( "CType", "" );
            $account->setPlugCookie( "Common", "" );
        }
    }

}

?>
