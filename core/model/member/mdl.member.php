<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "shopObject.php" );
class mdl_member extends shopobject
{

    var $defaultCols = "uname,name,mobile,member_lv_id,email,regtime,sex,remark,order_num,point,area";
    var $idColumn = "member_id";
    var $adminCtl = "member/member";
    var $textColumn = "uname";
    var $defaultOrder = array
    (
        0 => "member_id",
        1 => "desc"
    );
    var $tableName = "sdb_members";
    var $hasTag = true;
    var $typeName = "member";
    var $appendCols = "remark,remark_type";

    function getcolumns( )
    {
        $ret = shopobject::getcolumns( );
        $ret['_cmd'] = array(
            "label" => __( "操作" ),
            "width" => 70,
            "html" => "member/finder_command.html"
        );
        $ret['member_lv_id']['default'] = "";
        return $ret;
    }

    function &_columns( )
    {
        if ( !isset( $this->__table_define ) )
        {
            $schema =& $this->system->loadmodel( "utility/schemas" );
            $table = substr( $this->tableName, 4 );
            $define = require( CORE_DIR."/schemas/".$table.".php" );
            $this->__table_define =& $db[$table]['columns'];
            $this->__table_define['birthday'] = array( "width" => 100, "sql" => "CONCAT(b_year,\"-\",b_month,\"-\",b_day)", "label" => "生日" );
            $this->__table_define['remark'] = array(
                "width" => 50,
                "html" => "member/remark_row.html",
                "label" => "备注",
                "searchtype" => "has",
                "editable" => false,
                "filtertype" => "normal"
            );
            $appmgr =& $this->system->loadmodel( "system/appmgr" );
            if ( $appmgr->openid_loglist( ) )
            {
                $this->__table_define['member_refer'] = array( "width" => 25, "label" => "会员来源" );
                $this->__table_define['trust_name'] = array( "width" => 50, "label" => "信任登录ID", "sql" => "uname" );
            }
            $memattr =& $this->system->loadmodel( "member/memberattr" );
            $attributes = $memattr->getcustomoption( );
            foreach ( $attributes as $attr )
            {
                $attr_col_set = array(
                    "label" => $attr['attr_name'],
                    "width" => "75",
                    "custom" => true,
                    "searchable" => true,
                    "required" => $attr['attr_required'] == "true"
                );
                switch ( $attr['attr_type'] )
                {
                case "checkbox" :
                    $attr_col_set['type'] = "bool";
                    $attr_col_set['readonly'] = true;
                    break;
                case "select" :
                    $attr_col_set['type'] = unserialize( $attr['attr_option'] );
                    break;
                case "sex" :
                    $attr_col_set['type'] = "gender";
                    break;
                case "cal" :
                    $attr_col_set['type'] = "date";
                    break;
                default :
                    $attr_col_set['type'] = $attr['attr_type'];
                }
                $this->__table_define["attr__".$attr['attr_id']] = $attr_col_set;
            }
        }
        return $this->__table_define;
    }

    function getlist( $cols, $filter = "", $start = 0, $limit = 20, $orderType = null )
    {
        if ( $cols == "*" )
        {
            $table = substr( $this->tableName, 4 );
            $define = require( CORE_DIR."/schemas/".$table.".php" );
            $keyarray = array_keys( $db[$table]['columns'] );
            $cols = implode( ",", $keyarray );
        }
        $tmpcols = explode( ",", $cols );
        $nowcols = array( "member_id" );
        foreach ( $tmpcols as $key => $value )
        {
            if ( substr( $value, 0, 6 ) == "attr__" )
            {
                $custom[] = substr( $value, 6 );
            }
            else
            {
                $nowcols[] = $value;
            }
        }
        if ( preg_match( "/__+/", $orderType[0] ) )
        {
            $t_attr_id = explode( "__", $orderType[0] );
            $exc_sql = "SELECT a.member_id,b.value FROM sdb_members a LEFT JOIN sdb_member_mattrvalue b ON a.member_id = b.member_id WHERE b.attr_id";
            $exc_sql .= "='".$t_attr_id[1]."' ORDER BY b.value ".$orderType[1];
            $tmppdata = $this->db->select( $exc_sql );
            foreach ( $tmppdata as $k => $v )
            {
                if ( $v['value'] != "" )
                {
                    $tmpmid[] = $tmppdata[$k]['member_id'];
                }
            }
            if ( $orderType[1] == "desc" )
            {
                krsort( $tmpmid );
            }
            $orderType[0] = "FIELD(member_id,".implode( ",", $tmpmid ).")";
        }
        $cols = implode( ",", $nowcols );
        $list = shopobject::getlist( $cols, $filter, $start, $limit, $orderType );
        if ( $custom )
        {
            $map = array( );
            $rows = $this->db->select( "select mm.member_id,mm.value,mm.attr_id,ma.attr_type from sdb_member_mattrvalue mm left join sdb_member_attr ma on mm.attr_id = ma.attr_id where mm.attr_id in (".implode( ",", $custom ).")" );
            foreach ( $rows as $r )
            {
                if ( $r['attr_type'] == "checkbox" )
                {
                    $date = $this->getattrvalue( $r['member_id'], $r['attr_id'] );
                    $map[$r['member_id']]["attr__".$r['attr_id']] = "";
                    foreach ( $date as $k => $v )
                    {
                        $map[$r['member_id']]["attr__".$r['attr_id']] .= $date[$k]['value'].";";
                    }
                }
                else
                {
                    $map[$r['member_id']]["attr__".$r['attr_id']] = $r['value'];
                }
            }
            foreach ( $list as $k => $r )
            {
                if ( isset( $map[$r['member_id']] ) )
                {
                    $list[$k] = array_merge( $r, $map[$r['member_id']] );
                }
            }
        }
        return $list;
    }

    function _filter( $filter )
    {
        foreach ( $filter as $k => $v )
        {
            $filter[$k] = str_replace( "_", "\\_", $v );
        }
        $w = array( );
        foreach ( $filter as $k => $v )
        {
            if ( substr( $k, 0, 6 ) == "attr__" )
            {
                if ( is_array( $v ) )
                {
                    $r = array( );
                    foreach ( $v as $n )
                    {
                        $r[] = $this->db->quote( $n );
                    }
                    $w[] = "(attr_id=".intval( substr( $k, 6 ) )." and value in(".implode( ",", $r )."))";
                }
                else
                {
                    $w[] = "(attr_id=".intval( substr( $k, 6 ) )." and value=".$this->db->quote( $v ).")";
                }
                $less[substr( $value, 6 )] = $v;
                unset( $filter->$k );
            }
        }
        if ( $w )
        {
            $subselect = "select member_id from sdb_member_mattrvalue where ".implode( " and ", $w );
            if ( $this->db->dbver == 3 )
            {
                $m_id = array( 0 );
                foreach ( $this->db->select( $subselect ) as $r )
                {
                    $m_id[] = $r['member_id'];
                }
                $subselect = implode( ",", $m_id );
            }
            $where = shopobject::_filter( $filter );
            $where .= " and member_id in (".$subselect.")";
            return $where;
        }
        return shopobject::_filter( $filter );
    }

    function modifier_trust_name( &$rows )
    {
        foreach ( $rows as $k => $v )
        {
            $result[$k] = $this->db->selectrow( "select member_id from sdb_members where uname = \"".$v."\"" );
            $trust[$k] = $this->db->selectrow( "select uname from sdb_trust_login where member_id = \"".$result[$k]['member_id']."\"" );
            if ( $trust[$k]['uname'] )
            {
                $rows[$k] = $trust[$k]['uname'];
            }
            else
            {
                $rows[$k] = "-";
            }
        }
    }

    function modifier_member_refer( &$rows )
    {
        foreach ( $rows as $k => $v )
        {
            $result[$k] = $this->db->selectrow( "select plugin_name from sdb_plugins where plugin_ident like \"openid_".$v."\" and plugin_type =\"app\"" );
            if ( $result[$k]['plugin_name'] )
            {
                $rows[$k] = $result[$k]['plugin_name'];
            }
            else
            {
                $rows['local'] = "网店登录";
            }
        }
    }

    function countnum( )
    {
        $conunt = $this->db->selectrow( "select count(\"member_id\") as membernum from sdb_members where disabled=\"false\"" );
        return $conunt['membernum'];
    }

    function update( $data, $filter )
    {
        $oPointHistory =& $this->system->loadmodel( "trading/pointHistory" );
        $oMemberPoint =& $this->system->loadmodel( "trading/memberPoint" );
        foreach ( $data as $k => $v )
        {
            if ( substr( $k, 0, 6 ) == "attr__" )
            {
                foreach ( $this->getlist( "member_id", $filter ) as $key => $value )
                {
                    $mem['value'] = $v;
                    $mem['attr_id'] = substr( $k, 6, strlen( $k ) );
                    $mem['member_id'] = $value['member_id'];
                    $this->updatememattr( $mem['member_id'], $mem['attr_id'], $mem );
                }
                unset( $data->$k );
            }
            if ( $k == "point" )
            {
                if ( $v < 0 )
                {
                    $vPoint = 0;
                }
                else
                {
                    $vPoint = $v;
                }
                foreach ( $this->getlist( "member_id", $filter ) as $key => $value )
                {
                    $mem['member_id'] = $value['member_id'];
                    $nPoint = $oMemberPoint->getmemberpoint( $mem['member_id'] );
                    $nPoint = $vPoint - $nPoint;
                    $aPointHistory = array(
                        "member_id" => $mem['member_id'],
                        "point" => $nPoint,
                        "reason" => "operator_adjust",
                        "related_id" => $this->system->op_id
                    );
                    $oPointHistory->addhistory( $aPointHistory );
                }
            }
        }
        $result = shopobject::update( $data, $filter, $this );
        return $result;
    }

    function searchoptions( )
    {
        $mem_attr = array( );
        $searchoption = $this->db->select( "SELECT attr_id,attr_name FROM sdb_member_attr WHERE attr_group !='defalut' AND attr_search  = 'true'" );
        foreach ( $searchoption as $k => $v )
        {
            $mem_attr["attr__".$v['attr_id']] = $v['attr_name'];
        }
        return array_merge( $mem_attr, shopobject::searchoptions( ) );
    }

    function trust_check( $trust )
    {
        $mem_uname = $this->db->selectrow( "SELECT member_id FROM sdb_members WHERE uname ='".$trust."'" );
        $trust_uname = $this->db->selectrow( "SELECT show_uname FROM sdb_trust_login WHERE member_id =".$mem_uname['member_id'] );
        return $trust_uname['show_uname'];
    }

    function getmemberinfo( $nMemberId )
    {
        $mem = $this->instance( $nMemberId, "member_id,name,firstname,lastname,sex,b_year,area,b_month,b_day,addr,zip,email,tel,mobile,custom" );
        $mem_uname = $this->db->selectrow( "SELECT show_uname FROM sdb_trust_login WHERE member_id =".$nMemberId );
        $mem['uname'] = $mem_uname['show_uname'];
        if ( $mem['uname'] )
        {
            return $mem;
        }
        return $this->instance( $nMemberId, "member_id,uname,name,firstname,lastname,sex,b_year,area,b_month,b_day,addr,zip,email,tel,mobile,custom" );
    }

    function getwelcomeinfo( $nMemberId )
    {
        $arr = $this->db->selectrow( "SELECT count(*) as oNum  FROM `sdb_orders` WHERE pay_status='0' and member_id=".$nMemberId." and status = 'active' and disabled = 'false'" );
        $totalOrder = $this->db->selectrow( "SELECT count(*) as totalOrder  FROM `sdb_orders` WHERE member_id=".$nMemberId." AND disabled='false'" );
        $arr['totalOrder'] = $totalOrder['totalOrder'];
        $mNum = $this->db->selectrow( "SELECT count(*) as mNum FROM `sdb_message` where `to_id`=".$nMemberId." and `unread`='0' and to_type=0 and disabled='false' and del_status != '1'" );
        $arr['mNum'] = $mNum['mNum'];
        $pNum = $this->db->selectrow( "SELECT sum(point) as pNum FROM sdb_members WHERE member_id=".$nMemberId );
        $arr['pNum'] = intval( $pNum['pNum'] );
        $advance = $this->db->selectrow( "SELECT advance FROM sdb_members WHERE member_id=".$nMemberId );
        $arr['aNum'] = $advance['advance'];
        $experience = $this->db->selectrow( "SELECT experience FROM sdb_members WHERE member_id=".$nMemberId );
        $arr['eNum'] = $experience['experience'];
        $couponNum = $this->db->selectrow( "SELECT count(*) as couponNum FROM sdb_member_coupon WHERE member_id=".$nMemberId );
        $arr['couponNum'] = $couponNum['couponNum'];
        $tmp = $this->db->select( "SELECT * FROM sdb_member_coupon as mc left join sdb_coupons as c on c.cpns_id=mc.cpns_id left join sdb_promotion as p on c.pmt_id=p.pmt_id WHERE member_id=".$nMemberId." ORDER BY mc.memc_gen_time DESC" );
        $now = time( );
        $cNum = 0;
        foreach ( $tmp as $a )
        {
            if ( $a['pmt_time_end'] - $now <= 1296000 )
            {
                ++$cNum;
            }
        }
        $arr['cNum'] = $cNum;
        $commentRNum = $this->db->selectrow( "SELECT count(*) as commentRNum FROM `sdb_comments` where `author_id`=".$nMemberId." and `display`='true' and lastreply>0" );
        $arr['commentRNum'] = $commentRNum['commentRNum'];
        $pa = $this->db->select( "SELECT pmta_name FROM `sdb_promotion_activity` WHERE `pmta_enabled`='true' and `pmta_time_end`>=".$now." and `pmta_time_begin`<={$now}" );
        $arr['pa'] = $pa;
        return $arr;
    }

    function getmemberbyuser( $username )
    {
        return $this->db->selectrow( "SELECT * FROM sdb_members WHERE uname = ".$this->db->quote( $username ) );
    }

    function getfieldbyid( $id, $aField = array
    (
        0 => "*"
    ) )
    {
        $sqlString = "SELECT ".implode( ",", $aField )." FROM sdb_members WHERE member_id=".intval( $id );
        return $this->db->selectrow( $sqlString );
    }

    function setlevel( $member_lv_id, $finderResult )
    {
        if ( 0 < count( $finderResult['items'] ) )
        {
            $member_id = "member_id in (".implode( ",", $finderResult['items'] ).")";
        }
        else
        {
            $member_id = "";
        }
        $where = $finderResult['filter'] ? $this->_filter( $finderResult['filter'] ) : $member_id;
        $sql = "update sdb_members set member_lv_id=".intval( $member_lv_id )." where ".$where;
        return $this->db->exec( $sql );
    }

    function save( $nMId, $aData, $event = "" )
    {
        foreach ( $aData as $key => $value )
        {
            if ( !( $key == "addon" ) )
            {
                $aData[$key] = $value;
            }
        }
        $row = $this->getfieldbyid( $nMId, array( "member_lv_id" ) );
        $aRs = $this->db->query( "SELECT * FROM sdb_members WHERE member_id=".intval( $nMId ) );
        $sSql = $this->db->getupdatesql( $aRs, $aData );
        if ( !$sSql && $this->db->exec( $sSql ) )
        {
            if ( $aData['member_lv_id'] != $row['member_lv_id'] )
            {
                $this->modelName = "member/account";
                $this->typeName = "account";
                $this->fireevent( "changelevel", $aData, $nMId );
                $this->typeName = "member";
            }
            return true;
        }
        return false;
    }

    function addmemberbyadmin( $aData, &$message )
    {
        if ( empty( $aData['uname'] ) )
        {
            trigger_error( __( "保存失败：未输入会员名称" ), E_USER_ERROR );
            $message = __( "保存失败：未输入会员名称" );
            return false;
        }
        if ( strlen( $aData['uname'] ) < 3 )
        {
            trigger_error( __( "保存失败：会员名称不能小于3位" ), E_USER_ERROR );
            $message = __( "保存失败：会员名称不能小于3位" );
            return false;
        }
        $tmp_uname = explode( " ", $aData['uname'] );
        if ( 1 < count( $tmp_uname ) )
        {
            $message = __( "保存失败：会员名称不允许使用空格" );
            return false;
        }
        $aInfo = $this->db->select( $sql = "SELECT uname,email FROM sdb_members WHERE uname = ".$this->db->quote( $aData['uname'] )." OR email = ".$this->db->quote( $aData['email'] ) );
        foreach ( $aInfo as $key => $value )
        {
            if ( !( $value['uname'] == $aData['uname'] ) )
            {
                continue;
            }
            trigger_error( __( "保存失败：存在相同会员名称" ), E_USER_ERROR );
            $message = __( "保存失败：存在相同会员名称" );
            return false;
        }
        if ( !$aData['member_lv_id'] )
        {
            trigger_error( __( "保存失败：未选择会员等级" ), E_USER_ERROR );
            $message = __( "保存失败：未选择会员等级" );
            return false;
        }
        if ( empty( $aData['password'] ) )
        {
            trigger_error( __( "保存失败：密码输入不正确" ), E_USER_ERROR );
            $message = __( "保存失败：密码输入不正确" );
            return false;
        }
        if ( strlen( $aData['password'] ) < 4 )
        {
            trigger_error( __( "保存失败：密码不能小于4位" ), E_USER_ERROR );
            $message = __( "保存失败：密码不能小于4位" );
            return false;
        }
        if ( empty( $aData['psw_confirm'] ) )
        {
            trigger_error( __( "保存失败：确认密码不能为空" ), E_USER_ERROR );
            $message = __( "保存失败：确认密码不能为空" );
            return false;
        }
        if ( strlen( $aData['psw_confirm'] ) < 4 )
        {
            trigger_error( __( "保存失败：确认密码不能小于4位" ), E_USER_ERROR );
            $message = __( "保存失败：确认密码不能小于4位" );
            return false;
        }
        if ( $aData['psw_confirm'] != $aData['password'] )
        {
            trigger_error( __( "保存失败：两次密码输入不一致" ), E_USER_ERROR );
            $message = __( "保存失败：两次密码输入不一致" );
            return false;
        }
        if ( empty( $aData['email'] ) )
        {
            trigger_error( __( "保存失败：Email输入不正确" ), E_USER_ERROR );
            $message = __( "保存失败：Email输入不正确" );
            return false;
        }
        if ( $this->checkusertouc( $aData['uname'], $aData['password'], $aData['email'], $uid, $message ) )
        {
            if ( !empty( $message ) )
            {
                trigger_error( $message, E_USER_ERROR );
            }
            else
            {
                $aData['member_id'] = $uid;
            }
        }
        $aData['foreign_id'] = $aData['member_id'];
        unset( $aData->'member_id' );
        $aData['regtime'] = time( );
        $aData['password'] = md5( $aData['password'] );
        $aData['reg_ip'] = remote_addr( );
        $aRs = $this->db->query( "SELECT * FROM sdb_members WHERE 0" );
        $sSql = $this->db->getinsertsql( $aRs, $aData );
        if ( $this->db->exec( $sSql ) )
        {
            $insertID = $this->db->lastinsertid( );
            $status =& $this->system->loadmodel( "system/status" );
            $status->add( "MEMBER_REG" );
            return $insertID;
        }
        return "";
    }

    function savememattr( $data )
    {
        $selsql = "select attr_type from sdb_member_attr where attr_id = ".$data['attr_id']."";
        $tmpdate = $this->db->select( $selsql );
        if ( !( $tmpdate[0]['attr_type'] == "checkbox" ) && $data['value'] != "%no%" || $tmpdate[0]['attr_type'] != "checkbox" )
        {
            $aRs = $this->db->query( "SELECT * FROM sdb_member_mattrvalue WHERE 0" );
            $sSql = $this->db->getinsertsql( $aRs, $data );
            if ( $this->db->exec( $sSql ) )
            {
                return true;
            }
            return false;
        }
    }

    function getinfobyid( $nMId, $sField = NULL )
    {
        $aFiltrate = array(
            "basic" => array( "member_id" => 1, "member_lv_id" => 1, "uname" => 1, "password" => 1, "firstname" => 1, "lastname" => 1, "name" => 1, "sex" => 1, "b_year" => 1, "b_month" => 1, "b_day" => 1, "area" => 1, "addr" => 1, "zip" => 1, "email" => 1, "tel" => 1, "mobile" => 1, "regtime" => 1, "reg_ip" => 1, "score_rate", "score" => 1, "score_history" => 1, "score_freeze" => 1, "advance" => 1, "biz_money" => 1, "pay_time" => 1, "state" => 1, "pw_question" => 1, "pw_answer" => 1, "fav_tags" => 1 ),
            "ext" => array( "vocation" => 1, "education" => 1, "wedlock" => 1, "interest" => 1 )
        );
        if ( $aFiltrate['basic'][$sField] )
        {
            return $this->getbasicinfobyid( $nMId );
        }
        if ( $aFiltrate['ext'][$sField] )
        {
            return $this->getextinfobyid( $nMId );
        }
        return false;
    }

    function getbasicinfobyid( $nMId )
    {
        return $this->db->selectrow( "SELECT m.*,l.name AS lv_name\n            FROM sdb_members m\n            LEFT JOIN sdb_member_lv l\n            ON m.member_lv_id=l.member_lv_id\n            WHERE m.member_id=".intval( $nMId ) );
    }

    function getextinfobyid( $nMId )
    {
        $aTemp = $this->db->selectrow( "SELECT vocation,education,wedlock,interest,custom FROM sdb_members WHERE member_id=".intval( $nMId ) );
        if ( $aTemp )
        {
            $aCustom = unserialize( $aTemp['custom'] );
            unset( $aTemp->'custom' );
            $aExt = is_array( $aCustom ) ? array_merge( $aTemp, $aCustom ) : $aTemp;
            return $aExt;
        }
        return $aTemp;
    }

    function getmemberaddr( $nMemberId )
    {
        return $this->db->select( "SELECT * FROM sdb_member_addrs WHERE member_id=".intval( $nMemberId )." ORDER BY def_addr DESC" );
    }

    function settodef( $addrId, $member_id, &$message, $disabled )
    {
        if ( $addrId )
        {
            $aTemp = array( "def_addr" => 1 );
            $oldDefId = $this->db->selectrow( "SELECT addr_id FROM sdb_member_addrs WHERE def_addr=1 AND addr_id!='".$addrId."' AND member_id=".intval( $member_id ) );
            if ( is_array( $oldDefId ) && $disabled == "2" )
            {
                $message = __( "已存在默认收货地址，不能重复设置" );
                return false;
            }
            $aRs = $this->db->query( "SELECT def_addr FROM sdb_member_addrs WHERE addr_id=".intval( $addrId ) );
            $sSql = $this->db->getupdatesql( $aRs, $aTemp );
            if ( !$sSql && $this->db->exec( $sSql ) )
            {
                if ( $disabled )
                {
                    unset( $sSql );
                    $aTemp = array(
                        "def_addr" => $disabled == 1 ? 0 : 1
                    );
                    $aRs = $this->db->query( "SELECT def_addr FROM sdb_member_addrs WHERE addr_id=".$addrId );
                    $sSql = $this->db->getupdatesql( $aRs, $aTemp );
                    if ( $sSql )
                    {
                        $this->db->exec( $sSql );
                    }
                }
                return true;
            }
            $message = __( "设置失败！" );
            return false;
        }
        return false;
    }

    function getaddrbyid( $nId )
    {
        if ( $aRet = $this->db->selectrow( "SELECT * FROM sdb_member_addrs WHERE addr_id=".intval( $nId ) ) )
        {
            return $aRet;
        }
        return false;
    }

    function getdefaultaddr( $mid )
    {
        if ( $mid )
        {
            $aAddr = $this->db->selectrow( "SELECT * FROM sdb_member_addrs WHERE member_id=".intval( $mid )." AND def_addr = 1" );
            if ( !$aAddr['addr_id'] )
            {
                $aAddr = $this->db->selectrow( "SELECT member_id,name,area,zip,tel,mobile,addr,point FROM sdb_members WHERE member_id=".intval( $mid ) );
            }
        }
        return $aAddr;
    }

    function insertrec( $aData, $nMId, &$message )
    {
    default :
        switch ( $key )
        {
            foreach ( $aData as $key => $val )
            {
                $aData[$key] = trim( $val );
                if ( !empty( $aData[$key] ) )
                {
                    continue;
                }
            case "name" :
                $message = __( "姓名不能为空！" );
            }
            return false;
        case "addr" :
            $message = __( "地址不能为空！" );
            return false;
        }
        if ( $aData['tel'] == "" && $aData['mobile'] == "" )
        {
            $message = __( "联系电话和手机不能都为空！" );
            return false;
        }
        $aData['member_id'] = $nMId;
        $aRs = $this->db->query( "SELECT * FROM sdb_member_addrs WHERE 0" );
        $sSql = $this->db->getinsertsql( $aRs, $aData );
        if ( !$sSql && $this->db->query( $sSql ) )
        {
            $message = __( "保存成功！" );
            return true;
        }
        $message = __( "保存失败！" );
        return false;
    }

    function saverec( $aData, $member_id, &$message )
    {
        if ( $aData['def_addr'] && !$this->settodef( $aData['addr_id'], $member_id, $message, 2 ) )
        {
            return false;
        }
        $rs = $this->db->query( "SELECT * FROM sdb_member_addrs WHERE addr_id=".intval( $aData['addr_id'] ) );
        $sql = $this->db->getupdatesql( $rs, $aData );
        if ( !$sql && $this->db->exec( $sql ) )
        {
            return true;
        }
        return false;
    }

    function delrec( $addrId, &$message )
    {
        if ( $addrId )
        {
            return $this->db->query( "DELETE FROM sdb_member_addrs WHERE addr_id=".intval( $addrId ) );
        }
        $meesage = __( "参数有误" );
        return false;
    }

    function addfav( $nMid, $nGid )
    {
        $aRs = $this->db->selectrow( "SELECT addon FROM sdb_members WHERE member_id=".intval( $nMid ) );
        if ( isset( $aRs ) )
        {
            $aRs = unserialize( $aRs['addon'] );
            $aRs['fav'][] = $nGid;
            $aRs['fav'] = array_unique( $aRs['fav'] );
            return $this->save( $nMid, array(
                "addon" => serialize( $aRs )
            ) );
        }
        return false;
    }

    function getfavorite( $nMemberId, $nPage )
    {
        $oGood =& $this->system->loadmodel( "trading/goods" );
        return $oGood->getfavorite( $nMemberId, $nPage );
    }

    function delfav( $nMid, $nGid )
    {
        $aRs = $this->db->selectrow( "SELECT addon FROM sdb_members WHERE member_id=".intval( $nMid ) );
        if ( $aRs && $aRs['addon'] != "" )
        {
            $aRs = unserialize( $aRs['addon'] );
            $key = isset( $aRs['fav'] ) ? array_search( $nGid, $aRs['fav'] ) : false;
            if ( is_int( $key ) )
            {
                unset( $this->fav->$key );
            }
            return $this->save( $nMid, array(
                "addon" => serialize( $aRs )
            ) );
        }
        return false;
    }

    function delallfav( $nMid )
    {
        return $this->db->query( "DELETE addon FROM sdb_members WHERE member_id=".intval( $nMid ) );
    }

    function savecart( $nMid, $sCart )
    {
        $aRs = $this->db->selectrow( "SELECT addon FROM sdb_members WHERE member_id=".intval( $nMid ) );
        if ( isset( $aRs ) )
        {
            $aRs = unserialize( $aRs['addon'] );
            $aRs['cart'] = $sCart;
            return $this->save( $nMid, array(
                "addon" => serialize( $aRs )
            ) );
        }
        return false;
    }

    function getcart( $nMid )
    {
        $aRs = $this->db->selectrow( "SELECT addon FROM sdb_members WHERE member_id=".intval( $nMid ) );
        if ( isset( $aRs ) )
        {
            $aRs = unserialize( $aRs['addon'] );
            return $aRs['cart'];
        }
        return false;
    }

    function getnotify( $nMemberId )
    {
        $oGood =& $this->system->loadmodel( "trading/goods" );
        $aRet = $oGood->getnotify( $nMemberId );
        foreach ( $aRet['data'] as $k => $rows )
        {
            $rows['pdt_desc'] = unserialize( $rows['pdt_desc'] );
            if ( $rows['pdt_desc'] )
            {
                if ( $rows['pdt_desc'][$rows['product_id']] )
                {
                    $rows['pdt_desc'] = $rows['pdt_desc'][$rows['product_id']];
                    $oPdt =& $this->system->loadmodel( "goods/products" );
                    $aPdt = $oPdt->getfieldbyid( $rows['product_id'], array( "store" ) );
                    $rows['store'] = $aPdt['store'];
                }
                else
                {
                    $rows['pdt_desc'] = __( "该物品已经下架或者已被删除！" );
                    $rows['store'] = -1;
                }
            }
            else
            {
                $rows['pdt_desc'] = "";
            }
            $aRet['data'][$k] = $rows;
        }
        return $aRet;
    }

    function delnotify( $nMid, $id )
    {
        $notify =& $this->system->loadmodel( "goods/goodsNotify" );
        $aData = $notify->getfieldbyid( $id );
        $sSql = "DELETE FROM sdb_gnotify WHERE gnotify_id =".intval( $id )." AND member_id =".intval( $nMid );
        $this->db->exec( $sSql );
        $notify->updategoodsnum( $aData['goods_id'] );
        return true;
    }

    function getlevellist( $limit = true )
    {
        if ( $limit )
        {
            return $this->db->selectlimit( "SELECT * FROM sdb_member_lv WHERE disabled = 'false' ", 0, PAGELIMIT );
        }
        return $this->db->selectlimit( "SELECT * FROM sdb_member_lv WHERE disabled = 'false' " );
    }

    function savelevel( $aData, $nLvId, $event = "" )
    {
        if ( $aData['lv_type'] == "wholesale" )
        {
            $aData['point'] = 0;
        }
        $aRs = $this->db->query( "SELECT * FROM sdb_member_lv WHERE member_lv_id=".intval( $nLvId ) );
        $sSql = $this->db->getupdatesql( $aRs, $aData );
        return !$sSql || $this->db->query( $sSql );
    }

    function insertlevel( $aData, &$message )
    {
        if ( $this->checkfield( "name", "sdb_member_lv", "WHERE name='".$aData['name']."'" ) )
        {
            $message = __( "有同名会员等级存在！" );
            return false;
        }
        if ( $aData['default_lv'] == "" && $this->checkfield( "member_lv_id", "sdb_member_lv", "WHERE pre_id=0" ) )
        {
            $message = __( "默认等级已经存在！" );
            return false;
        }
        if ( $aData['lv_type'] == "wholesale" )
        {
            $aData['point'] = 0;
        }
        $aRs = $this->db->query( "SELECT * FROM sdb_member_lv WHERE member_lv_id=0" );
        $sSql = $this->db->getinsertsql( $aRs, $aData );
        return !$sSql || $this->db->query( $sSql );
    }

    function checkfield( $sField, $sTable, $sWhere = "" )
    {
        return $this->db->selectrow( "SELECT ".$sField." FROM ".$sTable." ".$sWhere );
    }

    function checkusertouc( $uname, $password, $email, &$uid, &$message )
    {
        $pObj =& $this->system->loadmodel( "member/passport" );
        if ( $obj = $pObj->function_judge( "checkuser" ) )
        {
            $isuser = $obj->checkuser( $aData['uname'] );
            if ( $isuser == "-3" )
            {
                $message = __( "您开启了UCenter整合，且UCenter中存在该用户名" );
            }
            else
            {
                $uid = $obj->regist_user( $uname, $password, $email );
                switch ( $uid )
                {
                case -1 :
                    $message = __( "无效的用户名" );
                    break;
                case -2 :
                    $message = __( "用户名不允许注册" );
                    break;
                case -3 :
                    $message = __( "已经存在一个相同的用户名" );
                    break;
                case -4 :
                    $message = __( "无效的email地址" );
                    break;
                case -5 :
                    $message = __( "邮件不允许" );
                    break;
                case -6 :
                    $message = __( "该邮件地址已经存在" );
                }
            }
            return true;
        }
        return false;
    }

    function getlevelbypoint( $nPoint )
    {
        $sSql = "SELECT member_lv_id, name FROM sdb_member_lv WHERE point <= ".$nPoint." AND lv_type='retail' AND disabled=\"false\" ORDER BY point DESC";
        return $this->db->selectrow( $sSql );
    }

    function getlevelbyexperience( $nExpierence )
    {
        $sSql = "SELECT member_lv_id, name FROM sdb_member_lv WHERE experience <= ".$nExpierence." AND lv_type='retail' AND disabled=\"false\" ORDER BY experience DESC";
        if ( $row = $this->db->selectrow( $sSql ) )
        {
            return $row;
        }
        return $this->db->selectrow( "SELECT member_lv_id, name FROM sdb_member_lv WHERE lv_type='retail' AND disabled=\"false\" ORDER BY experience ASC" );
    }

    function isallowaddr( $memid )
    {
        $sql = "SELECT count(*) as num FROM sdb_member_addrs WHERE member_id = ".intval( $memid );
        $aTmp = $this->db->selectrow( $sql );
        if ( $aTmp['num'] < 5 )
        {
            return true;
        }
        return false;
    }

    function getuserforbbs( )
    {
        $sql = "select member_id,uname,email,password,regtime,reg_ip from sdb_members";
        $data = $this->db->select( $sql );
        return $data;
    }

    function getremark( $memid )
    {
        $sql = "select remark,remark_type from sdb_members where member_id = ".intval( $memid );
        $aData = $this->db->selectrow( $sql );
        return $aData;
    }

    function addremark( $memid, $in )
    {
        $sql = "select remark,remark_type from sdb_members where member_id = ".intval( $memid );
        $rs = $this->db->query( $sql );
        $sql = $this->db->getupdatesql( $rs, $in );
        return !$sql || $this->db->exec( $sql );
    }

    function updatememattr( $member_id, $attr_id, $data )
    {
        $selsql = "select attr_type from sdb_member_attr where attr_id = ".$attr_id."";
        $tmpdate = $this->db->select( $selsql );
        $sql = "select * from sdb_member_mattrvalue where member_id = ".intval( $member_id )." and attr_id = ".intval( $attr_id );
        $rs = $this->db->select( $sql );
        if ( count( $rs ) == 0 )
        {
            return $this->savememattr( $data );
        }
        $rs1 = $this->db->query( $sql );
        $updatesql = $this->db->getupdatesql( $rs1, $data );
        return !$updatesql || $this->db->exec( $updatesql );
    }

    function getcontactobject( $member_id )
    {
        if ( 0 < $member_id )
        {
            $sql = "SELECT * FROM sdb_member_mattrvalue AS ma, sdb_member_attr AS at WHERE ma.attr_id = at.attr_id and ma.member_id = '".$member_id."' and at.attr_group = 'contact' AND attr_show = 'true' order by at.attr_order asc";
            return $this->db->select( $sql );
        }
    }

    function getmemidbyname( $name )
    {
        $sql = "SELECT member_id FROM sdb_members where uname = '".$name."'";
        return $this->db->select( $sql );
    }

    function getmemberattrvalue( $member_id )
    {
        return $this->db->select( "SELECT * FROM sdb_member_mattrvalue where member_id = '".$member_id."'" );
    }

    function getmemberbyid( $member_id )
    {
        return $this->db->select( "SELECT * FROM sdb_members where member_id = '".$member_id."'" );
    }

    function getattrvalue( $member_id, $attr_id )
    {
        return $this->db->select( "SELECT * FROM sdb_member_mattrvalue where member_id = '".$member_id."' and attr_id = '".$attr_id."' order by id" );
    }

    function deletemattrvalues( $attr_id, $member_id )
    {
        return $this->db->exec( "DELETE FROM sdb_member_mattrvalue where member_id = '".$member_id."' and attr_id = '".$attr_id."'" );
    }

    function deletememberidattrid( $attr_id, $value )
    {
        $sql = "DELETE FROM sdb_member_mattrvalue where value = '".$value."' and attr_id = '".$attr_id."'";
        return $this->db->exec( $sql );
    }

    function deleteallmattrvalues( $attr_id, $member_id, $value )
    {
        return $this->db->exec( "DELETE FROM sdb_member_mattrvalue where member_id = '".$member_id."' and attr_id = '".$attr_id."'        and value ='".$value."'" );
    }

    function delete( $filter )
    {
        if ( method_exists( $this, "pre_delete" ) )
        {
            $this->pre_delete( $filter );
        }
        if ( method_exists( $this, "post_delete" ) )
        {
            $this->post_delete( $filter );
        }
        $this->disabledMark = "recycle";
        $deleteattr = $filter['member_id'];
        $i = 0;
        for ( ; $i < count( $deleteattr ); ++$i )
        {
            $sSql = "delete from sdb_member_mattrvalue where member_id = '".$deleteattr[$i]."'";
            $this->db->exec( $sSql );
        }
        $appmgr =& $this->system->loadmodel( "system/appmgr" );
        if ( $appmgr->openid_loglist( ) )
        {
            $i = 0;
            for ( ; $i < count( $deleteattr ); ++$i )
            {
                $sSql = "delete from sdb_trust_login where member_id = '".$deleteattr[$i]."'";
                $return = $this->db->exec( $sSql );
            }
        }
        $sql = "delete from ".$this->tableName." where ".$this->_filter( $filter );
        if ( $this->db->exec( $sql ) )
        {
            if ( $this->db->affect_row( ) )
            {
                return $this->db->affect_row( );
            }
            return true;
        }
        return false;
    }

    function checkmemberhasadvance( $filter, $disabled = "" )
    {
        if ( $disabled )
        {
            $this->disabledMark = $disabled;
        }
        $sql = "SELECT COUNT(member_id) AS c FROM sdb_members WHERE advance <> 0 AND ".$this->_filter( $filter );
        $rs = $this->db->selectrow( $sql );
        return $rs['c'];
    }

    function thirdlogininfo( $row )
    {
        foreach ( $row as $key => $val )
        {
            foreach ( $val as $attr_key => $attr_val )
            {
                $row_value = $this->db->selectrow( "select attr_name from sdb_member_attr where attr_name=".$this->db->quote( $attr_key )."" );
                if ( $row_value )
                {
                    break;
                }
                $MemAttrp['attr_name'] = $attr_key;
                $MemAttrp['attr_valtype'] = "";
                $MemAttrp['attr_tyname'] = "输入内容不限制";
                $MemAttrp['attr_type'] = "text";
                $MemAttrp['attr_group'] = "input";
                $MemAttrp['attr_option'] = "";
                $MemAttrp['attr_show'] = "false";
                $MemAttrp['attr_search'] = "false";
                $MemAttrObj =& $this->system->loadmodel( "member/memberattr" );
                $order = $MemAttrObj->getmaxorder( );
                $MemAttrp['attr_order'] = $order[0]['attr_order'] + 1;
                $MemAttrObj->save( $MemAttrp );
            }
        }
        $user_def = array( );
        $user_nodef = array( );
        foreach ( $row as $key_insert => $val_insert )
        {
            foreach ( $val_insert as $attr_key_insert => $attr_val_insert )
            {
                $result = $this->db->selectrow( "select attr_id from sdb_member_attr where attr_group!='defalut' and attr_name=".$this->db->quote( $attr_key_insert )."" );
                if ( $result )
                {
                    $user_nodef['member_id'] = $row['member_id'];
                    $user_nodef['attr_id'] = $result['attr_id'];
                    $user_nodef['value'] = $attr_val_insert;
                    unset( $row->$key_insert );
                }
                else
                {
                    $default = true;
                    if ( $key_insert == "birthday" )
                    {
                        $ex_birthday = explode( "-", $attr_val_insert );
                        $re['value']['b_year'] = $ex_birthday[0];
                        $re['value']['b_month'] = $ex_birthday[1];
                        $re['value']['b_day'] = $ex_birthday[2];
                    }
                    else if ( $key_insert == "sex" )
                    {
                        if ( $attr_val_insert == "s" || $attr_val_insert == "m" )
                        {
                            $re['value']['sex'] = 1;
                        }
                        else
                        {
                            $re['value']['sex'] = 0;
                        }
                    }
                    else
                    {
                        $re['value'] = array(
                            $key_insert => $attr_val_insert
                        );
                    }
                }
                if ( $default != true )
                {
                    $this->updatememattr( $row['member_id'], $result['attr_id'], $user_nodef );
                }
                else
                {
                    $this->save( $row['member_id'], $re['value'] );
                }
            }
        }
    }

}

?>
