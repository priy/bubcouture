<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class XML
{

    public $parser = NULL;
    public $document = NULL;
    public $parent = NULL;
    public $stack = NULL;
    public $last_opened_tag = NULL;

    public function XML( )
    {
        $this->parser = xml_parser_create( "ISO-8859-1" );
        xml_parser_set_option( $this->parser, XML_OPTION_CASE_FOLDING, FALSE );
        xml_set_object( $this->parser, $this );
        xml_set_element_handler( $this->parser, "open", "close" );
        xml_set_character_data_handler( $this->parser, "data" );
    }

    public function destruct( )
    {
        xml_parser_free( $this->parser );
    }

    public function parse( &$data )
    {
        $this->document = array( );
        $this->stack = array( );
        $this->parent =& $this->document;
        return xml_parse( $this->parser, $data, TRUE ) ? $this->document : NULL;
    }

    public function open( &$parser, $tag, $attributes )
    {
        $this->data = "";
        $this->last_opened_tag = $tag;
        if ( is_array( $this->parent ) && array_key_exists( $tag, $this->parent ) )
        {
            if ( is_array( $this->parent[$tag] ) && array_key_exists( 0, $this->parent[$tag] ) )
            {
                $key = count_numeric_items( $this->parent[$tag] );
            }
            else
            {
                if ( array_key_exists( $tag."_attr", $this->parent ) )
                {
                    $arr = array(
                        "0_attr" => $this->parent[$tag."_attr"],
                        $this->parent[$tag]
                    );
                    unset( $this->parent[$tag."_attr"] );
                }
                else
                {
                    $arr = array(
                        $this->parent[$tag]
                    );
                }
                $this->parent[$tag] =& $arr;
                $key = 1;
            }
            $this->parent =& $this->parent[$tag];
        }
        else
        {
            $key = $tag;
        }
        if ( $attributes )
        {
            $this->parent[$key."_attr"] = $attributes;
        }
        $this->parent =& $this->parent[$key];
        $this->stack[] =& $this->parent;
    }

    public function data( &$parser, $data )
    {
        if ( $this->last_opened_tag != NULL )
        {
            $this->data .= $data;
        }
    }

    public function close( &$parser, $tag )
    {
        if ( $this->last_opened_tag == $tag )
        {
            $this->parent = $this->data;
            $this->last_opened_tag = NULL;
        }
        array_pop( $this->stack );
        if ( $this->stack )
        {
            $this->parent =& $this->stack[count( $this->stack ) - 1];
        }
    }

}

function xml_unserialize( &$xml )
{
    ( );
    $xml_parser = new XML( );
    $data = $xml_parser->parse( $xml );
    $xml_parser->destruct( );
    $arr = xml_format_array( $data );
    return $arr['root'];
}

function xml_serialize( &$data, $htmlon = 0, $level = 1 )
{
    $space = str_repeat( "\t", $level );
    $cdatahead = $htmlon ? "<![CDATA[" : "";
    $cdatafoot = $htmlon ? "]]>" : "";
    $s = "";
    if ( !empty( $data ) )
    {
        foreach ( $data as $key => $val )
        {
            if ( !is_array( $val ) )
            {
                $val = "{$cdatahead}{$val}{$cdatafoot}";
                if ( is_numeric( $key ) )
                {
                    $s .= "{$space}<item_{$key}>{$val}</item_{$key}>";
                }
                else if ( $key === "" )
                {
                    $s .= "";
                }
                else
                {
                    $s .= "{$space}<{$key}>{$val}</{$key}>";
                }
            }
            else if ( is_numeric( $key ) )
            {
                $s .= "{$space}<item_{$key}>".xml_serialize( $val, $htmlon, $level + 1 )."{$space}</item_{$key}>";
            }
            else if ( $key === "" )
            {
                $s .= "";
            }
            else
            {
                $s .= "{$space}<{$key}>".xml_serialize( $val, $htmlon, $level + 1 )."{$space}</{$key}>";
            }
        }
    }
    $s = preg_replace( "/([\x01-\t\x0B-\x0C\x0E-\x1F])+/", " ", $s );
    return ( $level == 1 ? "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?><root>" : "" ).$s.( $level == 1 ? "</root>" : "" );
}

function xml_format_array( $arr, $level = 0 )
{
    foreach ( ( array )$arr as $key => $val )
    {
        if ( is_array( $val ) )
        {
            $val = xml_format_array( $val, $level + 1 );
        }
        if ( is_string( $key ) && strpos( $key, "item_" ) === 0 )
        {
            $arr[intval( substr( $key, 5 ) )] = $val;
            unset( $arr[$key] );
        }
        else
        {
            $arr[$key] = $val;
        }
    }
    return $arr;
}

function count_numeric_items( &$array )
{
    return is_array( $array ) ? count( array_filter( array_keys( $array ), "is_numeric" ) ) : 0;
}

?>
