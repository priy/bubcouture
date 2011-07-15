<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class cachemgr
{

    var $_objects = null;
    var $_base_rev = "\$Rev: 46772 \$";

    function cachemgr( )
    {
        $this->system =& $GLOBALS['system'];
    }

    function setmodified( $key )
    {
        $now = time( );
        if ( is_array( $key ) )
        {
            foreach ( $key as $k )
            {
                $this->system->savemeta( $k, $now );
            }
        }
        else
        {
            $this->system->savemeta( $key, $now );
        }
    }

    function getmodified( $key )
    {
        if ( isset( $this->_vary_list[$key] ) )
        {
            return $this->_vary_list[$key];
        }
        return $this->_base_rev;
    }

    function set( $ident, $content, $varys = null )
    {
        $data = array(
            "content" => $content
        );
        if ( is_array( $varys ) && $varys )
        {
            $data['varys'] = array( );
            foreach ( $varys as $o )
            {
                $o = strtoupper( $o );
                $data['cotime'][$o] = $this->getmodified( $o );
                $data['varys'][] = $o;
            }
        }
        return $this->store( md5( $_SERVER['HTTP_HOST'].$ident.STORE_KEY ), $data );
    }

    function get( $ident, &$content )
    {
        if ( $this->fetch( md5( $_SERVER['HTTP_HOST'].$ident.STORE_KEY ), $data ) )
        {
            if ( 0 < count( $data['varys'] ) )
            {
                foreach ( $data['varys'] as $o )
                {
                    if ( isset( $data['cotime'][$o] ) && !( $data['cotime'][$o] != $this->getmodified( $o ) ) )
                    {
                        continue;
                    }
                    return false;
                }
            }
            $content = $data['content'];
            return true;
        }
        return $content = false;
    }

    function &exec( $func, $args, $ttl = 3600 )
    {
        if ( is_array( $func ) )
        {
            $ident = md5( get_class( $func[0] ).$func[1].implode( ",", $args ).STORE_KEY );
        }
        else
        {
            $ident = md5( $func.implode( ",", $args ).STORE_KEY );
        }
        $data =& $this->fetch( $ident );
        if ( !$data && $ttl < time( ) - $data['time'] )
        {
            $return = call_user_func_array( $func, $args );
            $data = array(
                "time" => time( ),
                "return" => $return
            );
            $this->store( $ident, $data, $ttl );
            return $return;
        }
        $return =& $data['return'];
        return $return;
    }

}

?>
