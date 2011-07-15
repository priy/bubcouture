<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function widget_flashview( &$setting, &$system )
{
    $setting['allimg'] = "";
    $setting['allurl'] = "";
    if ( !$setting['flash'] )
    {
        foreach ( $setting['img'] as $value )
        {
            $setting['allimg'] .= $rvalue."|";
            $setting['allurl'] .= urlencode( $value['url'] )."|";
        }
    }
    else
    {
        foreach ( $setting['flash'] as $key => $value )
        {
            if ( $value['pic'] )
            {
                if ( $value['url'] )
                {
                    $value['link'] = $value['url'];
                }
                $setting['allimg'] .= $rvalue."|";
                $setting['allurl'] .= urlencode( $value['link'] )."|";
            }
        }
        krsort( $setting['flash'] );
    }
    return $setting;
}

?>
