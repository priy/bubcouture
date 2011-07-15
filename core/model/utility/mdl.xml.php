<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class mdl_xml
{

    function xml2arrayvalues( $contents, $get_attributes = 1 )
    {
        if ( !$contents )
        {
            return array( );
        }
        if ( !function_exists( "xml_parser_create" ) )
        {
            return array( );
        }
        $parser = xml_parser_create( "UTF-8" );
        xml_parser_set_option( $parser, XML_OPTION_CASE_FOLDING, 0 );
        xml_parser_set_option( $parser, XML_OPTION_SKIP_WHITE, 0 );
        xml_parse_into_struct( $parser, $contents, $xml_values );
        xml_parser_free( $parser );
        if ( !$xml_values )
        {
            return;
        }
        $xml_array = array( );
        $parents = array( );
        $opened_tags = array( );
        $arr = array( );
        $current =& $xml_array;
        foreach ( $xml_values as $data )
        {
            unset( $attributes );
            unset( $value );
            extract( $data );
            $result = "";
            if ( $get_attributes )
            {
                $result = array( );
                if ( isset( $value ) )
                {
                    $result['value'] = trim( $value );
                }
                if ( isset( $attributes ) )
                {
                    foreach ( $attributes as $attr => $val )
                    {
                        if ( $get_attributes == 1 )
                        {
                            $result['attr'][$attr] = trim( $val );
                        }
                    }
                }
            }
            else if ( isset( $value ) )
            {
                $result = trim( $value );
            }
            if ( $type == "open" )
            {
                $parent[$level - 1] =& $current;
                if ( is_array( $current ) )
                {
                }
                if ( !in_array( $tag, array_keys( $current ) ) )
                {
                    $current[$tag] = $result;
                    $current =& $current[$tag];
                }
                else
                {
                    if ( isset( $current[$tag][0] ) )
                    {
                        array_push( $current[$tag], $result );
                    }
                    else
                    {
                        $current[$tag] = array(
                            $current[$tag],
                            $result
                        );
                    }
                    $last = count( $current[$tag] ) - 1;
                    $current =& $current[$tag][$last];
                }
            }
            else if ( $type == "complete" )
            {
                if ( !isset( $current[$tag] ) )
                {
                    $current[$tag] = $result;
                }
                else if ( !is_array( $current[$tag] ) && $get_attributes == 0 || isset( $current[$tag][0] ) && is_array( $current[$tag][0] ) && $get_attributes == 1 )
                {
                    array_push( $current[$tag], $result );
                }
                else
                {
                    $current[$tag] = array(
                        $current[$tag],
                        $result
                    );
                }
            }
            else if ( $type == "close" )
            {
                $current =& $parent[$level - 1];
            }
        }
        return $xml_array;
    }

    function xml2array( $contents, $output_tag = null )
    {
        if ( !$contents )
        {
            return array( );
        }
        if ( !function_exists( "xml_parser_create" ) )
        {
            return array( );
        }
        $parser = xml_parser_create( "UTF-8" );
        xml_parser_set_option( $parser, XML_OPTION_CASE_FOLDING, 0 );
        xml_parser_set_option( $parser, XML_OPTION_SKIP_WHITE, 0 );
        xml_parse_into_struct( $parser, $contents, $xml_values );
        xml_parser_free( $parser );
        if ( !$xml_values )
        {
            return;
        }
        $xml_array = array( );
        $parents = array( );
        $opened_tags = array( );
        $arr = array( );
        $current =& $xml_array;
        $number = 0;
        foreach ( $xml_values as $data )
        {
            unset( $attributes );
            unset( $value );
            extract( $data );
            $result = "";
            if ( $tag == "item" )
            {
                if ( !is_null( $value ) )
                {
                    $result = trim( $value );
                }
                $tag = $number;
                ++$number;
            }
            else if ( !is_null( $value ) )
            {
                $result = trim( $value );
            }
            if ( $type == "open" )
            {
                $parent[$level - 1] =& $current;
                if ( is_array( $current ) )
                {
                }
                if ( !in_array( $tag, array_keys( $current ) ) )
                {
                    $current[$tag] = $result;
                    $current =& $current[$tag];
                }
                else
                {
                    if ( isset( $current[$tag][0] ) )
                    {
                        array_push( $current[$tag], $result );
                    }
                    else
                    {
                        $current[$tag] = array(
                            $current[$tag],
                            $result
                        );
                    }
                    $last = count( $current[$tag] ) - 1;
                    $current =& $current[$tag][$last];
                }
            }
            else if ( $type == "complete" )
            {
                if ( !isset( $current[$tag] ) )
                {
                    $current[$tag] = $result;
                }
                else if ( !is_array( $current[$tag] ) && $get_attributes == 0 || isset( $current[$tag][0] ) && is_array( $current[$tag][0] ) && $get_attributes == 1 )
                {
                    array_push( $current[$tag], $result );
                }
                else
                {
                    $current[$tag] = array(
                        $current[$tag],
                        $result
                    );
                }
            }
            else if ( $type == "close" )
            {
                $current =& $parent[$level - 1];
            }
        }
        if ( $tag == "item" )
        {
            $number = 0;
        }
        if ( $output_tag )
        {
            return $xml_array[$output_tag];
        }
        return $xml_array;
    }

    function getpath( $xml, $tagName, $attr = null )
    {
        $parser = xml_parser_create( "UTF-8" );
        xml_parser_set_option( $parser, XML_OPTION_CASE_FOLDING, 0 );
        xml_parser_set_option( $parser, XML_OPTION_SKIP_WHITE, 0 );
        xml_parse_into_struct( $parser, $xml, $xml_values );
        xml_parser_free( $parser );
        $node = null;
        foreach ( $xml_values as $k => $v )
        {
            if ( !( $tagName == $v['attributes']['type'] ) )
            {
                continue;
            }
            if ( $attr )
            {
                if ( !( count( array_diff_assoc( $attr, $v['attributes'] ) ) == 0 ) )
                {
                    continue;
                }
                $node =& $xml_values[$k];
            }
            else
            {
                $node =& $xml_values[$k];
            }
            break;
        }
        $path = array( );
        if ( $node )
        {
            $level = $node['level'];
            for ( ; -1 < $k; --$k )
            {
                if ( $xml_values[$k]['level'] == $level )
                {
                    array_unshift( $path, $xml_values[$k] );
                    --$level;
                }
            }
            return $path;
        }
        return false;
    }

    function array2xml( $data, $root = "root" )
    {
        $xml = "<".$root.">";
        $this->_array2xml( $data, $xml );
        $xml .= "</".$root.">";
        return $xml;
    }

    function _array2xml( &$data, &$xml )
    {
        if ( is_array( $data ) )
        {
            foreach ( $data as $k => $v )
            {
                if ( is_numeric( $k ) )
                {
                    $xml .= "<item>";
                    $xml .= $this->_array2xml( $v, $xml );
                    $xml .= "</item>";
                }
                else
                {
                    $xml .= "<".$k.">";
                    $xml .= $this->_array2xml( $v, $xml );
                    $xml .= "</".$k.">";
                }
            }
        }
        else if ( is_numeric( $data ) )
        {
            $xml .= $data;
        }
        else if ( is_string( $data ) )
        {
            $xml .= "<![CDATA[".$data."]]>";
        }
    }

    function isnumericarray( $array )
    {
        if ( 0 < count( $array ) && !empty( $array[0] ) )
        {
            return true;
        }
        return false;
    }

    function array_xml( $keytag, $array )
    {
        $attributes = "";
        $tagcontent = "";
        if ( is_array( $array ) )
        {
            foreach ( $array as $key => $value )
            {
                if ( in_array( $key, $member_element[$keytag] ) && !is_array( $value ) )
                {
                    $attributes .= $key."=\"{$value}\" ";
                }
                else if ( is_array( $value ) )
                {
                    if ( $this->isnumericarray( $value ) )
                    {
                        $i = 0;
                        for ( ; do
 {
 $i < count( $value ); ++$i, )
                            {
                                $tagcontent .= $this->array_xml( $key, $value[$i] );
                            }
                        } while ( 1 );
                    }
                    $tagcontent .= $this->array_xml( $key, $value );
                }
                else if ( $key == "value" )
                {
                    $tagcontent .= $value;
                }
                else
                {
                    $tagcontent .= "<".$key.">{$value}</{$key}>";
                }
            }
        }
        return "<".$keytag." {$attributes}>{$tagcontent}</{$keytag}>";
    }

    function orderarray_xml( $keytag, $array )
    {
        $attributes = "";
        $tagcontent = "";
        if ( is_array( $array ) )
        {
            foreach ( $array as $key => $value )
            {
                if ( in_array( $key, $order_element[$keytag] ) && !is_array( $value ) )
                {
                    $attributes .= $key."=\"{$value}\" ";
                }
                else if ( is_array( $value ) )
                {
                    if ( $this->isnumericarray( $value ) )
                    {
                        $i = 0;
                        for ( ; do
 {
 $i < count( $value ); ++$i, )
                            {
                                $tagcontent .= $this->orderarray_xml( $key, $value[$i] );
                            }
                        } while ( 1 );
                    }
                    $tagcontent .= $this->orderarray_xml( $key, $value );
                }
                else if ( $key == "value" )
                {
                    $tagcontent .= $value;
                }
                else
                {
                    $tagcontent .= "<".$key.">{$value}</{$key}>";
                }
            }
        }
        return "<".$keytag." {$attributes}>{$tagcontent}</{$keytag}>";
    }

    function goodsarray_xml( $keytag, $array )
    {
        $attributes = "";
        $tagcontent = "";
        if ( is_array( $array ) )
        {
            foreach ( $array as $key => $value )
            {
                if ( in_array( $key, $element[$keytag] ) && !is_array( $value ) )
                {
                    $attributes .= $key."=\"{$value}\" ";
                }
                else if ( is_array( $value ) )
                {
                    if ( $this->isnumericarray( $value ) )
                    {
                        $i = 0;
                        for ( ; do
 {
 $i < count( $value ); ++$i, )
                            {
                                $tagcontent .= $this->goodsarray_xml( $key, $value[$i] );
                            }
                        } while ( 1 );
                    }
                    $tagcontent .= $this->goodsarray_xml( $key, $value );
                }
                else if ( $key == "value" )
                {
                    $tagcontent .= $value;
                }
                else
                {
                    $tagcontent .= "<".$key.">{$value}</{$key}>";
                }
            }
        }
        return "<".$keytag." {$attributes}>{$tagcontent}</{$keytag}>";
    }

}

?>
