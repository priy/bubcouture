<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

if ( !defined( "IN_UC" ) )
{
    exit( "Access Denied" );
}
class tagcontrol extends base
{

    public function tagcontrol( )
    {
        $this->base( );
        $this->load( "tag" );
        $this->load( "misc" );
    }

    public function ongettag( $arr )
    {
        @extract( $arr, EXTR_SKIP );
        $return = $apparray = $appadd = array( );
        if ( $nums && is_array( $nums ) )
        {
            foreach ( $nums as $k => $num )
            {
                $apparray[$k] = $k;
            }
        }
        $data = $_ENV['tag']->get_tag_by_name( $tagname );
        if ( $data )
        {
            $apparraynew = array( );
            foreach ( $data as $tagdata )
            {
                $row = $r = array( );
                $tmp = explode( "\t", $tagdata['data'] );
                $type = $tmp[0];
                array_shift( $tmp );
                foreach ( $tmp as $tmp1 )
                {
                    if ( $tmp1 != "" )
                    {
                        $r[] = $_ENV['misc']->string2array( $tmp1 );
                    }
                }
                if ( in_array( $tagdata['appid'], $apparray ) )
                {
                    if ( 0 < $tagdata['expiration'] && 3600 < $this->time - $tagdata['expiration'] )
                    {
                        $appadd[] = $tagdata['appid'];
                        $_ENV['tag']->formatcache( $tagdata['appid'], $tagname );
                    }
                    else
                    {
                        $apparraynew[] = $tagdata['appid'];
                    }
                    $datakey = array( );
                    $count = 0;
                    foreach ( $r as $data )
                    {
                        $return[$tagdata['appid']]['data'][] = $data;
                        $return[$tagdata['appid']]['type'] = $type;
                        ++$count;
                        if ( $nums[$tagdata['appid']] <= $count )
                        {
                            break;
                        }
                    }
                }
            }
            $apparray = array_diff( $apparray, $apparraynew );
        }
        else
        {
            foreach ( $apparray as $appid )
            {
                $_ENV['tag']->formatcache( $appid, $tagname );
            }
        }
        if ( $apparray )
        {
            $this->load( "note" );
            $_ENV['note']->add( "gettag", "id={$tagname}", "", $appadd, -1 );
        }
        return $return;
    }

}

?>
