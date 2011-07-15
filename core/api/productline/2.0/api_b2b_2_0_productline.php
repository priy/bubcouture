<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( CORE_DIR."/api/shop_api_object.php" );
class api_b2b_2_0_productline extends shop_api_object
{

    public $app_error = array
    (
        "member has not sell permission" => array
        (
            "no" => "b_productline_001",
            "debug" => "",
            "level" => "error",
            "desc" => "会员没分销权限",
            "info" => ""
        ),
        "member has not product line" => array
        (
            "no" => "b_productline_002",
            "debug" => "",
            "level" => "error",
            "desc" => "会员没有指定产品线",
            "info" => ""
        ),
        "serivce data error" => array
        (
            "no" => "b_productline_003",
            "debug" => "",
            "level" => "error",
            "desc" => "内部数据异常",
            "info" => ""
        ),
        "member has not set sell permission" => array
        (
            "no" => "b_productline_004",
            "debug" => "",
            "level" => "error",
            "desc" => "会员没有设置代销权限",
            "info" => ""
        )
    );

    public function getColumns( )
    {
        $columns = array(
            "pline_id" => array( "type" => "int" ),
            "pline_name" => array( "type" => "string" ),
            "custom_name" => array( "type" => "string" ),
            "disabled" => array( "type" => "string" ),
            "cat_id" => array( "type" => "int" ),
            "brand_id" => array( "type" => "int" ),
            "last_modify" => array( "type" => "int" )
        );
        return $columns;
    }

    public function search_product_line( $data )
    {
        $data['disabled'] = "false";
        $data['orderby'] = "pline_id";
        $where = $this->before_filter( $data );
        $result = $this->db->selectrow( "select count(*) as all_counts from sdb_product_line where ".implode( " and ", $where ) );
        $result['start_version_id'] = $data['start_version_id'];
        $result['last_version_id'] = $data['last_version_id'];
        $where = $this->_filter( $data );
        $data_info = $this->db->select( "select ".implode( ",", $data['columns'] )." from sdb_product_line".$where );
        $result['counts'] = count( $data_info );
        $result['data_info'] = $data_info;
        $this->api_response( "true", FALSE, $result );
    }

    public function search_product_line_dealer( $data )
    {
        if ( isset( $data['start_version_id'] ) )
        {
            unset( $data['start_version_id'] );
        }
        if ( isset( $data['last_version_id'] ) )
        {
            unset( $data['last_version_id'] );
        }
        if ( isset( $data['disabled'] ) )
        {
            unset( $data['disabled'] );
        }
        $data['orderby'] = "member_id,pline_id";
        $where = $this->before_filter( $data );
        $result = $this->db->selectrow( "select count(*) as all_counts from sdb_pline_to_dealer where ".implode( " and ", $where ) );
        $where = $this->_filter( $data );
        $pline_to_dealer_list = $this->db->select( "select member_id,pline_id,last_modify from sdb_pline_to_dealer".$where );
        if ( $pline_to_dealer_list )
        {
            $arr_certificate_id = array( );
            foreach ( $pline_to_dealer_list as $k => $pline_to_dealer )
            {
                if ( !isset( $arr_certificate_id[$pline_to_dealer['member_id']] ) )
                {
                    $member = $this->db->selectrow( "select certificate_id from sdb_members where member_id=".$pline_to_dealer['member_id'] );
                    $arr_certificate_id[$pline_to_dealer['member_id']] = $member['certificate_id'];
                    $dealer_id = $member['certificate_id'];
                }
                else
                {
                    $dealer_id = $arr_certificate_id[$pline_to_dealer['member_id']];
                }
                $pline_to_dealer_list[$k]['dealer_id'] = $dealer_id;
                unset( $Var_1272['member_id'] );
            }
        }
        $data_info = $pline_to_dealer_list;
        $result['counts'] = count( $data_info );
        $result['data_info'] = $data_info;
        $this->api_response( "true", FALSE, $result );
    }

    public function before_filter( $filter )
    {
        $where = array( 1 );
        if ( isset( $filter['start_version_id'] ) )
        {
            $where[] = "version_id >=".intval( $filter['start_version_id'] );
        }
        if ( isset( $filter['last_version_id'] ) )
        {
            $where[] = "version_id <".intval( $filter['last_version_id'] );
        }
        if ( isset( $filter['disabled'] ) )
        {
            $where[] = "disabled=\"".$filter['disabled']."\"";
        }
        return $where;
    }

    public function _filter( $filter )
    {
        $where = $this->before_filter( $filter );
        return parent::_filter( $where, $filter );
    }

    public function getPlineListByMember( $nMId )
    {
        return $this->db->select( "SELECT member_id,pline_id,last_modify FROM sdb_pline_to_dealer WHERE member_id = ".$nMId );
    }

    public function getInfo( $pline_id )
    {
        return $this->db->selectRow( "SELECT * FROM sdb_product_line WHERE pline_id=".intval( $pline_id ) );
    }

    public function checkDealerPurview( $member = "", $arr_goods )
    {
        if ( !is_array( $arr_goods ) || count( $arr_goods ) <= 0 )
        {
            return FALSE;
        }
        $dealer_purview = $member['dealer_purview'];
        $arr_dealer_purview = array( 0, 1, 2, 3 );
        if ( !empty( $member ) && in_array( $dealer_purview, $arr_dealer_purview ) )
        {
            $member_id = $member['member_id'];
            $member_lv_id = $member['member_lv_id'];
            switch ( $dealer_purview )
            {
            case 0 :
                $this->add_application_error( "member has not set sell permission" );
                break;
            case 1 :
                $this->add_application_error( "member has not sell permission" );
                break;
            case 2 :
                break;
            case 3 :
                $objPline =& $this->system->loadModel( "trading/pline" );
                $objMemberPline =& $this->system->loadModel( "member/memberpline" );
                $objGoods =& $this->system->loadModel( "trading/goods" );
                $member_line_list = $objMemberPline->getPlineListByMember( $member_id );
                if ( $member_line_list )
                {
                    $is_dealer_cat = array( );
                    foreach ( $member_line_list as $pline )
                    {
                        $pline_info = $objPline->getInfo( $pline['pline_id'] );
                        if ( $pline_info['cat_id'] != -1 && !in_array( $pline_info['cat_id'], $is_dealer_cat ) )
                        {
                            $arr_sub_cat_id = $this->_getSubCatId( $pline_info['cat_id'] );
                            if ( !empty( $arr_sub_cat_id ) )
                            {
                                $arr_sub_cat_id[] = $pline_info['cat_id'];
                                $is_dealer_cat = array_merge( $is_dealer_cat, $arr_sub_cat_id );
                            }
                            else
                            {
                                $is_dealer_cat[] = $pline_info['cat_id'];
                            }
                        }
                        foreach ( $arr_goods as $k => $goods )
                        {
                            if ( isset( $goods['is_dealer'] ) && $goods['is_dealer'] )
                            {
                                continue;
                            }
                            if ( !isset( $goods['cat_id'] ) || !isset( $goods['brand_id'] ) && !is_null( $goods['brand_id'] ) )
                            {
                                $goods = $objGoods->getFieldById( $goods['goods_id'] );
                            }
                            if ( $pline_info['cat_id'] == -1 && $pline_info['brand_id'] == -1 )
                            {
                                $arr_goods[$k]['is_dealer'] = TRUE;
                            }
                            else if ( $pline_info['cat_id'] == -1 && $pline_info['brand_id'] == $goods['brand_id'] )
                            {
                                $arr_goods[$k]['is_dealer'] = TRUE;
                            }
                            else if ( $pline_info['brand_id'] == -1 && in_array( $goods['cat_id'], $is_dealer_cat ) )
                            {
                                $arr_goods[$k]['is_dealer'] = TRUE;
                            }
                            else if ( in_array( $goods['cat_id'], $is_dealer_cat ) && $pline_info['brand_id'] == $goods['brand_id'] )
                            {
                                $arr_goods[$k]['is_dealer'] = TRUE;
                            }
                            else
                            {
                                $arr_goods[$k]['is_dealer'] = FALSE;
                            }
                        }
                    }
                    foreach ( $arr_goods as $k => $goods )
                    {
                        if ( !$arr_goods[$k]['is_dealer'] )
                        {
                            $this->add_application_error( "member has not sell permission" );
                        }
                    }
                }
                else
                {
                    $this->add_application_error( "member has not product line" );
                }
                break;
            default :
                $this->add_application_error( "member has not sell permission" );
            }
        }
        else
        {
            $this->add_application_error( "serivce data error" );
        }
    }

    public function _getSubCatId( $parent_id )
    {
        $arr_sub_catid = array( );
        $cat_list = $this->db->select( "select cat_id from sdb_goods_cat where parent_id=".$parent_id );
        foreach ( $cat_list as $cat )
        {
            if ( $this->_hasSubTree( $cat['cat_id'] ) )
            {
                $children = $this->_getSubCatId( $cat['cat_id'] );
                array_unshift( $children, $cat['cat_id'] );
                $arr_sub_catid = array_merge( $children, $arr_sub_catid );
            }
            else
            {
                $arr_sub_catid[] = $cat['cat_id'];
            }
        }
        return $arr_sub_catid;
    }

    public function _hasSubTree( $cat_id )
    {
        $cat = $this->db->selectrow( "select count(*) as row_count from sdb_goods_cat where parent_id=".$cat_id );
        if ( 0 < $cat['row_count'] )
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

}

?>
