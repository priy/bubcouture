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
    $prev = 1 < $params['data']['current'] ? "<a href=\"".str_replace( $params['data']['token'], $params['data']['current'] - 1, $params['data']['link'] ).__( "\" class=\"prev\" onmouseover=\"this.className = 'onprev'\" onmouseout=\"this.className = 'prev'\" title=\"上一页\">上一页</a>" ) : __( "<span class=\"unprev\" title=\"已经是第一页\">已经是第一页</span>" );
    $next = $params['data']['current'] < $params['data']['total'] ? "<a href=\"".str_replace( $params['data']['token'], $params['data']['current'] + 1, $params['data']['link'] ).__( "\" class=\"next\" onmouseover=\"this.className = 'onnext'\" onmouseout=\"this.className = 'next'\" title=\"下一页\">下一页</a>" ) : __( "<span class=\"unnext\" title=\"已经是最后一页\">已经是最后一页</span>" );
    if ( $params['rand'] && 1 < $params['data']['total'] )
    {
        $r = rand( 1, $params['data']['total'] );
        $rand = "<td><input type=\"button\" onclick=\"window.location='".str_replace( $params['data']['token'], $r, $params['data']['link'] ).__( "'\" value=\"随便一页\" class=\"rand\"></td>" );
    }
    if ( $params['type'] == "mini" )
    {
        return "    <table class=\"pager\"><tr><td><span class=\"pagecurrent\">{$params['data']['current']}</span>/<span class=\"pageall\">{$params['data']['total']}</span></td>\n    <td>{$prev}</td>\n    <td>{$next}</td>\n    {$rand}\n    </tr></table>";
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
        return "      <div class=\"clearfix\">\n    <table class=\"pager floatRight\"><tr>\n    <td>{$prev}</td>\n    <td class=\"pagernum\">{$links}</td>\n    <td style=\"padding-right:20px\">{$next}</td>\n    <!-- <td>到第 <input type=\"text\" class=\"pagenum\"> 页</td>\n    <td><input type=\"button\" value=\"\" class=\"go\"></td> -->\n    {$rand}\n    </tr></table></div>";
    }
}

function _pager_link( $from, $to, $l, $p, $c = null )
{
    $i = $from;
    for ( ; $i < $to + 1; ++$i )
    {
        if ( $c == $i )
        {
            $r[] = " <strong class=\"pagecurrent\">".$i."</strong> ";
        }
        else
        {
            $r[] = " <a href=\"".str_replace( $p, $i, $l )."\">".$i."</a> ";
        }
    }
    return implode( " ", $r );
}

?>
