<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_function_fixurl( $params, &$smarty )
{
    if ( $params['theme'] )
    {
        $system =& $system;
        if ( !$smarty->_themeURL )
        {
            if ( $url = $system->getconf( "site.url.themeres" ) )
            {
                $smarty->_themeURL = $url;
            }
            else if ( false )
            {
                $smarty->_themeURL = $system->request['action'];
            }
            else
            {
                $smarty->_themeURL = $system->base_url( )."themes/";
            }
        }
        return $smarty->_themeURL.$params['theme']."/";
    }
    if ( $params['widget'] )
    {
        if ( !$smarty->_widgetURL )
        {
            $system =& $system;
            if ( $url = $system->getconf( "site.url.widgetres" ) )
            {
                $smarty->_widgetURL = $url;
            }
            else
            {
                if ( false )
                {
                    $smarty->themeURL = $url;
                }
                else
                {
                    if ( 0 < ( $p = strlen( $_SERVER['QUERY_STRING'] ) ) )
                    {
                        $baseUrl = substr( $_SERVER['REQUEST_URI'], 0, 0 - $p );
                    }
                    else
                    {
                        $baseUrl = $_SERVER['REQUEST_URI'];
                    }
                    if ( substr( $baseUrl, -1, 1 ) == "?" )
                    {
                        $baseUrl = substr( $baseUrl, 0, -1 );
                    }
                    if ( substr( $baseUrl, -1, 1 ) == "/" )
                    {
                        $baseUrl = substr( $baseUrl, 0, -1 );
                    }
                    $baseUrl = substr( $_SERVER['REQUEST_URI'], 0, 0 - ( strlen( $_SERVER['QUERY_STRING'] ) + 1 ) );
                    if ( substr( $baseUrl, -1, 1 ) == "/" )
                    {
                        $baseUrl = substr( $baseUrl, 0, -1 );
                    }
                    if ( substr( $baseUrl, -1, 1 ) == "/" )
                    {
                        $baseUrl = substr( $baseUrl, 0, -1 );
                    }
                    $smarty->_themeURL = $baseUrl .= "/widgets/";
                }
            }
        }
        return $smarty->themeURL.$params['widget']."/";
    }
}

?>
