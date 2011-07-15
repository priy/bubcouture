<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

function tpl_function_pager( $params, &$smarty )
{
    if ( !$params['data']['current'] )
    {
        $params['data']['current'] = 1;
    }
    if ( !$params['data']['total'] )
    {
        $params['data']['total'] = 1;
    }
    if ( $params['data']['total'] < 2 )
    {
        return "";
    }
    $prev = 1 < $params['data']['current'] ? "<a href=\"".str_replace( $params['data']['token'], $params['data']['current'] - 1, $params['data']['link'] ).__( "\" class=\"sysiconBtnNoIcon\" title=\"上一页\">&laquo;上一页</a>" ) : "&nbsp;";
    $next = $params['data']['current'] < $params['data']['total'] ? "<a href=\"".str_replace( $params['data']['token'], $params['data']['current'] + 1, $params['data']['link'] ).__( "\" class=\"sysiconBtnNoIcon\" title=\"下一页\">下一页&raquo;</a>" ) : "&nbsp;";
    if ( $params['type'] == "mini" )
    {
        return "    <table>\n    <tr><td class=\"pagenum\"><span class=\"pagecurrent\">{$params['data']['current']}<span>/<span class=\"pageall\">{$params['data']['total']}</span></td>\n    <td>{$prev}</td>\n    <td>{$next}</td>\n    {$rand}\n    </tr></table>";
    }
    else
    {
        $c = $params['data']['current'];
        $t = $params['data']['total'];
        $v = array( );
        $l = $params['data']['link'];
        $p = $params['data']['token'];
        if ( $t < 11 )
        {
            $v[] = _pager_link( 1, $t, $l, $p, $c );
        }
        else if ( $t - $c < 8 )
        {
            $v[] = _pager_link( 1, 3, $l, $p );
            $v[] = _pager_link( $t - 8, $t, $l, $p, $c );
        }
        else if ( $c < 10 )
        {
            $v[] = _pager_link( 1, max( $c + 3, 10 ), $l, $p, $c );
            $v[] = _pager_link( $t - 1, $t, $l, $p );
        }
        else
        {
            $v[] = _pager_link( 1, 3, $l, $p );
            $v[] = _pager_link( $c - 2, $c + 3, $l, $p, $c );
            $v[] = _pager_link( $t - 1, $t, $l, $p );
        }
        $links = implode( "...", $v );
        return "    <table class=\"pager\" cellpadding=\"0\" cellspacing=\"0\" style=\"width:auto\"><tr>\n    <td style=\"padding-right:20px\">{$prev}&nbsp;{$links}&nbsp;{$next}</td>\n    </tr></table>";
    }
}

function _pager_link( $from, $to, $l, $p, $c = NULL )
{
    $i = $from;
    for ( ; $i < $to + 1; ++$i )
    {
        if ( $c == $i )
        {
            $r[] = " <span class=\"borderdown\" style=\"background:#fff; color:#000;font-weight:700;padding:2px 5px\">".$i."</span> ";
        }
        else
        {
            $r[] = " <a class=\"borderup\" href=\"".str_replace( $p, $i, $l )."\">".$i."</a> ";
        }
    }
    return implode( " ", $r );
}

?>
