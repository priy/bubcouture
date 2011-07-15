<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class mdl_search
{

    public function mdl_search( )
    {
        $this->map = array( "brand_id" => "b", "price" => "p", "tag" => "t", "name" => "n", "bn" => "f", "type_id" => "tp" );
    }

    public function join( $j )
    {
        $v = array( );
        foreach ( $j as $n )
        {
            $n = trim( $n );
            if ( $n !== "" )
            {
                $v[] = rawurlencode( $n );
            }
        }
        return 0 < count( $v ) ? implode( ",", $v ) : false;
    }

    public function encode( $filter )
    {
        $ret = array( );
        $tmpSpec = array( );
        foreach ( $filter as $k => $j )
        {
            if ( $p = $this->map[$k] )
            {
                if ( false !== ( $v = $this->join( $j ) ) )
                {
                    $ret[$p] = $p.",".$v;
                }
            }
            else if ( substr( $k, 0, 2 ) == "p_" )
            {
                if ( false !== ( $v = $this->join( $j ) ) )
                {
                    $ret[$n = substr( $k, 2 )] = $n.",".$v;
                }
            }
            else if ( substr( $k, 0, 2 ) == "s_" )
            {
                $ret[$k] = "s".substr( $k, 2 ).",".$this->join( $j );
            }
        }
        return implode( "_", $ret );
    }

    public function decode( $str, &$path, &$system )
    {
        $data = array( );
        if ( $str )
        {
            foreach ( explode( "_", $str ) as $substr )
            {
                $data[] = $substr;
                $columns = explode( ",", $substr );
                $part1 = array_shift( $columns );
                $map = array_flip( $this->map );
                if ( is_numeric( $part1 ) )
                {
                    $filter["p_".$part1] = $columns;
                    $title = "";
                    $p = $part1;
                }
                else if ( substr( $part1, 0, 1 ) == "s" )
                {
                    $filter["s_".substr( $part1, 1 )] = $columns;
                    $p = "s_".substr( $part1, 1 );
                    $columns[0] = substr( $part1, 1 ).",".$columns[0];
                }
                else if ( $p = $map[$part1] )
                {
                    $filter[$p] = $columns;
                }
                else
                {
                    $title = "";
                }
                $path[] = array(
                    "type" => $p,
                    "data" => $columns,
                    "str" => implode( "_", $data )
                );
            }
            return $filter;
        }
    }

}

?>
