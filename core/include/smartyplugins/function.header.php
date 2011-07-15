<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_function_header( $params, &$smarty )
{
    $system =& $system;
    $data['TITLE'] =& $smarty->title;
    $data['KEYWORDS'] =& $smarty->keywords;
    $data['DESCRIPTION'] =& $smarty->desc;
    $data['headers'] =& $system->ctl->header;
    $output =& $system->loadmodel( "system/frontend" );
    $data['theme_dir'] = $system->base_url( )."themes/".$output->theme;
    if ( $theme_info = $system->getconf( "site.theme_".$system->getconf( "system.ui.current_theme" )."_color" ) )
    {
        $data['theme_color_href'] = $system->base_url( )."themes/".$system->getconf( "system.ui.current_theme" )."/".$theme_info;
    }
    $shop = array(
        "set" => array( )
    );
    $shop['set']['path'] = substr( PHP_SELF, 0, strrpos( PHP_SELF, "/" ) + 1 );
    $shop['set']['buytarget'] = $system->getconf( "site.buy.target" );
    $shop['set']['dragcart'] = $system->getconf( "ux.dragcart" );
    $shop['set']['refer_timeout'] = $system->getconf( "site.refer_timeout" );
    $shop['url']['addcart'] = $system->mkurl( "cart", "ajaxadd" );
    $shop['url']['shipping'] = $system->mkurl( "cart", "shipping" );
    $shop['url']['payment'] = $system->mkurl( "cart", "payment" );
    $shop['url']['total'] = $system->mkurl( "cart", "total" );
    $shop['url']['viewcart'] = $system->mkurl( "cart", "view" );
    $shop['url']['ordertotal'] = $system->mkurl( "cart", "total" );
    $shop['url']['applycoupon'] = $system->mkurl( "cart", "applycoupon" );
    $shop['url']['diff'] = $system->mkurl( "product", "diff" );
    $data['shopDefine'] = json_encode( $shop );
    echo $smarty->_fetch_compile_include( "shop:common/header.html", $data );
}

?>
