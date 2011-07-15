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
class feedcontrol extends base
{

    public function feedcontrol( )
    {
        $this->base( );
    }

    public function onadd( $arr )
    {
        $this->load( "misc" );
        @extract( $arr, EXTR_SKIP );
        $title_template = $this->_parsetemplate( $title_template );
        $body_template = $this->_parsetemplate( $body_template );
        $hash_template = md5( $title_template.$body_template );
        $body_data = $_ENV['misc']->array2string( $body_data );
        $title_data = $_ENV['misc']->array2string( $title_data );
        $hash_data = md5( $title_template.$title_data.$body_template.$body_data );
        $dateline = $this->time;
        $this->db->query( "INSERT INTO ".UC_DBTABLEPRE."feeds SET appid='{$appid}', icon='{$icon}', uid='{$uid}', username='{$username}',\n            title_template='{$title_template}', title_data='{$title_data}', body_template='{$body_template}', body_data='{$body_data}', body_general='{$body_general}',\n            image_1='{$image_1}', image_1_link='{$image_1_link}', image_2='{$image_2}', image_2_link='{$image_2_link}',\n            image_3='{$image_3}', image_3_link='{$image_3_link}', image_4='{$image_4}', image_4_link='{$image_4_link}',\n            hash_template='{$hash_template}', hash_data='{$hash_data}', target_ids='{$target_ids}', dateline='{$dateline}'" );
        return $this->db->insert_id( );
    }

    public function onget( $arr )
    {
        @extract( $arr, EXTR_SKIP );
        $this->load( "misc" );
        $feedlist = $this->db->fetch_all( "SELECT * FROM ".UC_DBTABLEPRE."feeds ORDER BY feedid LIMIT {$limit}" );
        if ( $feedlist )
        {
            foreach ( $feedlist as $key => $feed )
            {
                $feed['body_data'] = $_ENV['misc']->string2array( $feed['body_data'] );
                $feed['title_data'] = $_ENV['misc']->string2array( $feed['title_data'] );
                $feedlist[$key] = $feed;
            }
        }
        if ( !empty( $feedlist ) )
        {
            $maxfeed = array_pop( $feedlist );
            $maxfeedid = $maxfeed['feedid'];
            $feedlist = array_merge( $feedlist, array(
                $maxfeed
            ) );
            $this->_delete( 0, $maxfeedid );
        }
        return $feedlist;
    }

    public function _delete( $start, $end )
    {
        $this->db->query( "DELETE FROM ".UC_DBTABLEPRE."feeds WHERE feedid>='{$start}' AND feedid<='{$end}'" );
    }

    public function _parsetemplate( $template )
    {
        $template = str_replace( array( "\r", "\n" ), "", $template );
        $template = str_replace( array( "<br>", "<br />", "<BR>", "<BR />" ), "\n", $template );
        $template = str_replace( array( "<b>", "<B>" ), "[B]", $template );
        $template = str_replace( array( "<i>", "<I>" ), "[I]", $template );
        $template = str_replace( array( "<u>", "<U>" ), "[U]", $template );
        $template = str_replace( array( "</b>", "</B>" ), "[/B]", $template );
        $template = str_replace( array( "</i>", "</I>" ), "[/I]", $template );
        $template = str_replace( array( "</u>", "</U>" ), "[/U]", $template );
        $template = htmlspecialchars( $template );
        $template = nl2br( $template );
        $template = str_replace( array( "[B]", "[I]", "[U]", "[/B]", "[/I]", "[/U]" ), array( "<b>", "<i>", "<u>", "</b>", "</i>", "</u>" ), $template );
        return $template;
    }

}

?>
