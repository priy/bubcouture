<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$controllers = array(
    "article" => array(
        "index" => array( "name" => "article index", "title" => "article index" ),
        "showlist" => array( "name" => "article showlist", "title" => "article showlist" )
    ),
    "cart" => array(
        "ctl_cart" => array( "name" => "cart ctl_cart", "title" => "cart ctl_cart" ),
        "addpkgtocart" => array( "name" => "cart addpkgtocart", "title" => "cart addpkgtocart" ),
        "addgifttocart" => array( "name" => "cart addgifttocart", "title" => "cart addgifttocart" ),
        "addcoupontocart" => array( "name" => "cart addcoupontocart", "title" => "cart addcoupontocart" ),
        "addgoodstocart" => array( "name" => "cart addgoodstocart", "title" => "cart addgoodstocart" ),
        "removegoods" => array( "name" => "cart removegoods", "title" => "cart removegoods" ),
        "updatecart" => array( "name" => "cart updatecart", "title" => "cart updatecart" ),
        "ajaxadd" => array( "name" => "cart ajaxadd", "title" => "cart ajaxadd" ),
        "view" => array( "name" => "cart view", "title" => "cart view" ),
        "index" => array( "name" => "cart index", "title" => "cart index" ),
        "checkout" => array( "name" => "cart checkout", "title" => "cart checkout" ),
        "shipping" => array( "name" => "cart shipping", "title" => "cart shipping" ),
        "removecoupon" => array( "name" => "cart removecoupon", "title" => "cart removecoupon" ),
        "applycoupon" => array( "name" => "cart applycoupon", "title" => "cart applycoupon" ),
        "total" => array( "name" => "cart total", "title" => "cart total" )
    ),
    "comment" => array(
        "submit" => array( "name" => "comment submit", "title" => "comment submit" )
    ),
    "coupon" => array(
        "getexchangelist" => array( "name" => "coupon getexchangelist", "title" => "coupon getexchangelist" ),
        "exchange" => array( "name" => "coupon exchange", "title" => "coupon exchange" )
    ),
    "feeds" => array(
        "ctl_feeds" => array( "name" => "feeds ctl_feeds", "title" => "feeds ctl_feeds" ),
        "index" => array( "name" => "feeds index", "title" => "feeds index" ),
        "products" => array( "name" => "feeds products", "title" => "feeds products" ),
        "output" => array( "name" => "feeds output", "title" => "feeds output" )
    ),
    "gallery" => array(
        "index" => array( "name" => "gallery index", "title" => "gallery index" )
    ),
    "gift" => array(
        "showtypelist" => array( "name" => "gift showtypelist", "title" => "gift showtypelist" ),
        "showlist" => array( "name" => "gift showlist", "title" => "gift showlist" ),
        "showdetail" => array( "name" => "gift showdetail", "title" => "gift showdetail" )
    ),
    "index" => array(
        "home" => array( "name" => "index home", "title" => "index home" )
    ),
    "links" => array(
        "index" => array( "name" => "links index", "title" => "links index" )
    ),
    "member" => array(
        "ctl_member" => array( "name" => "member ctl_member", "title" => "member ctl_member" ),
        "partner" => array( "name" => "member partner", "title" => "member partner" ),
        "pagination" => array( "name" => "member pagination", "title" => "member pagination" ),
        "setting" => array( "name" => "member setting", "title" => "member setting" ),
        "agent" => array( "name" => "member agent", "title" => "member agent" ),
        "agreement" => array( "name" => "member agreement", "title" => "member agreement" ),
        "shared" => array( "name" => "member shared", "title" => "member shared" ),
        "orders" => array( "name" => "member orders", "title" => "member orders" ),
        "deposit" => array( "name" => "member deposit", "title" => "member deposit" ),
        "commission" => array( "name" => "member commission", "title" => "member commission" ),
        "balance" => array( "name" => "member balance", "title" => "member balance" ),
        "pointhistory" => array( "name" => "member pointhistory", "title" => "member pointhistory" ),
        "coupon" => array( "name" => "member coupon", "title" => "member coupon" ),
        "favorite" => array( "name" => "member favorite", "title" => "member favorite" ),
        "delfav" => array( "name" => "member delfav", "title" => "member delfav" ),
        "ajaxaddfav" => array( "name" => "member ajaxaddfav", "title" => "member ajaxaddfav" ),
        "ajaxdelfav" => array( "name" => "member ajaxdelfav", "title" => "member ajaxdelfav" ),
        "notify" => array( "name" => "member notify", "title" => "member notify" ),
        "delnotify" => array( "name" => "member delnotify", "title" => "member delnotify" ),
        "review" => array( "name" => "member review", "title" => "member review" ),
        "comment" => array( "name" => "member comment", "title" => "member comment" ),
        "index" => array( "name" => "member index", "title" => "member index" ),
        "outbox" => array( "name" => "member outbox", "title" => "member outbox" ),
        "track" => array( "name" => "member track", "title" => "member track" ),
        "viewmsg" => array( "name" => "member viewmsg", "title" => "member viewmsg" ),
        "delinboxmsg" => array( "name" => "member delinboxmsg", "title" => "member delinboxmsg" ),
        "deltrackmsg" => array( "name" => "member deltrackmsg", "title" => "member deltrackmsg" ),
        "deloutboxmsg" => array( "name" => "member deloutboxmsg", "title" => "member deloutboxmsg" ),
        "send" => array( "name" => "member send", "title" => "member send" ),
        "message" => array( "name" => "member message", "title" => "member message" ),
        "sendmsgtoopt" => array( "name" => "member sendmsgtoopt", "title" => "member sendmsgtoopt" ),
        "sendmsg" => array( "name" => "member sendmsg", "title" => "member sendmsg" ),
        "security" => array( "name" => "member security", "title" => "member security" ),
        "savesecurity" => array( "name" => "member savesecurity", "title" => "member savesecurity" ),
        "receiver" => array( "name" => "member receiver", "title" => "member receiver" ),
        "addreceiver" => array( "name" => "member addreceiver", "title" => "member addreceiver" ),
        "insertrec" => array( "name" => "member insertrec", "title" => "member insertrec" ),
        "setdefault" => array( "name" => "member setdefault", "title" => "member setdefault" ),
        "modifyreceiver" => array( "name" => "member modifyreceiver", "title" => "member modifyreceiver" ),
        "saverec" => array( "name" => "member saverec", "title" => "member saverec" ),
        "delrec" => array( "name" => "member delrec", "title" => "member delrec" ),
        "score" => array( "name" => "member score", "title" => "member score" ),
        "init" => array( "name" => "member init", "title" => "member init" ),
        "savemember" => array( "name" => "member savemember", "title" => "member savemember" ),
        "output" => array( "name" => "member output", "title" => "member output" )
    ),
    "message" => array(
        "index" => array( "name" => "message index", "title" => "message index" ),
        "putquestion" => array( "name" => "message putquestion", "title" => "message putquestion" )
    ),
    "order" => array(
        "create" => array( "name" => "order create", "title" => "order create" ),
        "pay" => array( "name" => "order pay", "title" => "order pay" ),
        "index" => array( "name" => "order index", "title" => "order index" )
    ),
    "page" => array(
        "ctl_page" => array( "name" => "page ctl_page", "title" => "page ctl_page" ),
        "output" => array( "name" => "page output", "title" => "page output" )
    ),
    "passport" => array(
        "ctl_passport" => array( "name" => "passport ctl_passport", "title" => "passport ctl_passport" ),
        "verifycode" => array( "name" => "passport verifycode", "title" => "passport verifycode" ),
        "index" => array( "name" => "passport index", "title" => "passport index" ),
        "create" => array( "name" => "passport create", "title" => "passport create" ),
        "recover" => array( "name" => "passport recover", "title" => "passport recover" ),
        "sendpsw" => array( "name" => "passport sendpsw", "title" => "passport sendpsw" ),
        "error" => array( "name" => "passport error", "title" => "passport error" ),
        "logout" => array( "name" => "passport logout", "title" => "passport logout" ),
        "verify" => array( "name" => "passport verify", "title" => "passport verify" )
    ),
    "paycenter" => array(
        "ctl_paycenter" => array( "name" => "paycenter ctl_paycenter", "title" => "paycenter ctl_paycenter" ),
        "result" => array( "name" => "paycenter result", "title" => "paycenter result" ),
        "order" => array( "name" => "paycenter order", "title" => "paycenter order" ),
        "deposit" => array( "name" => "paycenter deposit", "title" => "paycenter deposit" )
    ),
    "product" => array(
        "call" => array( "name" => "product call", "title" => "product call" ),
        "index" => array( "name" => "product index", "title" => "product index" ),
        "diff" => array( "name" => "product diff", "title" => "product diff" ),
        "photo" => array( "name" => "product photo", "title" => "product photo" ),
        "pic" => array( "name" => "product pic", "title" => "product pic" ),
        "notify" => array( "name" => "product notify", "title" => "product notify" ),
        "alert" => array( "name" => "product alert", "title" => "product alert" ),
        "productlist" => array( "name" => "product productlist", "title" => "product productlist" )
    ),
    "search" => array(
        "index" => array( "name" => "search index", "title" => "search index" ),
        "productlist" => array( "name" => "search productlist", "title" => "search productlist" )
    ),
    "sfile" => array(
        "ctl_sfile" => array( "name" => "sfile ctl_sfile", "title" => "sfile ctl_sfile" ),
        "script" => array( "name" => "sfile script", "title" => "sfile script" ),
        "res" => array( "name" => "sfile res", "title" => "sfile res" ),
        "jscript" => array( "name" => "sfile jscript", "title" => "sfile jscript" )
    ),
    "sitemaps" => array(
        "ctl_sitemaps" => array( "name" => "sitemaps ctl_sitemaps", "title" => "sitemaps ctl_sitemaps" )
    ),
    "smssend" => array(
        "send" => array( "name" => "smssend send", "title" => "smssend send" )
    ),
    "tools" => array(
        "setcur" => array( "name" => "tools setcur", "title" => "tools setcur" )
    )
);
?>
