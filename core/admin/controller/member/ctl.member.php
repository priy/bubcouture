<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "objectPage.php" );
class ctl_member extends objectPage
{

    public $workground = "member";
    public $object = "member/member";
    public $finder_action_tpl = "member/finder_action.html";
    public $finder_filter_tpl = "member/finder_filter.html";
    public $finder_default_cols = "uname,member_lv_id,name,sex,email,regtime,point,mobile,arearefer_id,remark,remark_type,area,trust_name,member_refer";

    public function index( )
    {
        $oLev =& $this->system->loadModel( "member/level" );
        $this->pagedata['member_lv'] = $oLev->getMLevel( );
        $messenger =& $this->system->loadModel( "system/messenger" );
        $this->pagedata['messenger'] = $messenger->getList( );
        parent::index( );
    }

    public function _detail( $nMId )
    {
        $oMsg =& $this->system->loadModel( "resources/msgbox" );
        $oComm =& $this->system->loadModel( "comment/comment" );
        $oOrder =& $this->system->loadModel( "trading/order" );
        $messagenum = $oMsg->getTotalMsg( $nMId );
        $discussnum = $oComm->getTotalNum( $nMId, "discuss" );
        $asknum = $oComm->getTotalNum( $nMId, "ask" );
        return array(
            "show_detail" => array(
                "label" => __( "会员信息" ),
                "tpl" => "member/member_items.html"
            ),
            "detailEdit" => array(
                "label" => __( "编辑会员" ),
                "tpl" => "member/sub_edit.html"
            ),
            "detailOrders" => array(
                "label" => __( "订单" ),
                "tpl" => "member/sub_orders.html"
            ),
            "showModifyPoint" => array(
                "label" => __( "积分" ),
                "tpl" => "member/modify_point.html"
            ),
            "showExperience" => array(
                "label" => __( "经验值" ),
                "tpl" => "member/modify_experience.html"
            ),
            "showPointHistory" => array(
                "label" => __( "积分历史" ),
                "tpl" => "member/sub_point_history.html"
            ),
            "advance" => array(
                "label" => __( "预存款" ),
                "tpl" => "member/advance_list.html"
            ),
            "detailMsg" => array(
                "label" => __( "信件(" ).$messagenum.")",
                "tpl" => "member/sub_message.html"
            ),
            "member_ask" => array(
                "label" => __( "咨询(" ).$asknum.")",
                "tpl" => "member/sub_review.html"
            ),
            "member_feedback" => array(
                "label" => __( "评论(" ).$discussnum.")",
                "tpl" => "member/sub_discuss.html"
            ),
            "Remark" => array(
                "label" => __( "会员备注" ),
                "tpl" => "member/remark.html"
            )
        );
    }

    public function show_detail( $nMId )
    {
        $memattr =& $this->system->loadModel( "member/memberattr" );
        $oMem =& $this->system->loadModel( "member/member" );
        $tmpMem = $oMem->getBasicInfoById( $nMId );
        if ( $tmpMem )
        {
            foreach ( $tmpMem as $key => $val )
            {
                if ( $key == "remark" )
                {
                    $tmpMem[$key] = str_replace( "\n", "<br>", $val );
                }
            }
        }
        $attr = $memattr->getCustomValueById( $nMId );
        $memberattrvalue = $oMem->getMemberAttrvalue( $nMId );
        $arrt_count = count( $attr );
        $arrtv_count = count( $memberattrvalue );
        $i = 0;
        for ( ; $i < $arrt_count; ++$i )
        {
            $j = 0;
            for ( ; $j < $arrtv_count; ++$j )
            {
                if ( $attr[$i]['attr_id'] == $memberattrvalue[$j]['attr_id'] )
                {
                    if ( $attr[$i]['attr_type'] == "cal" )
                    {
                        $attr[$i]['value'] = date( "Y-m-d", $memberattrvalue[$j]['value'] );
                    }
                    else
                    {
                        $attr[$i]['value'] = $memberattrvalue[$j]['value'];
                    }
                    if ( $attr[$i]['attr_type'] == "checkbox" )
                    {
                        $date = $oMem->getattrvalue( $nMId, $attr[$i]['attr_id'] );
                        $attr[$i]['value'] = "";
                        foreach ( $date as $k => $v )
                        {
                            $attr[$i]['value'] .= $date[$k]['value'].";";
                        }
                    }
                }
            }
        }
        $this->pagedata['custom'] = $attr;
        $this->pagedata['mem'] = $tmpMem;
        $this->pagedata['ordernum'] = 4;
        $this->pagedata['member_id'] = $nMId;
    }

    public function showModifyPoint( $nMId )
    {
        $oMemberPoint =& $this->system->loadModel( "trading/memberPoint" );
        $this->pagedata['point'] = $oMemberPoint->getMemberPoint( $nMId );
        $this->pagedata['member_id'] = $nMId;
    }

    public function showExperience( $nMId )
    {
        $oMemberExp =& $this->system->loadModel( "trading/memberExperience" );
        $this->pagedata['experience'] = $oMemberExp->getMemberExperience( $nMId );
        $this->pagedata['member_id'] = $nMId;
    }

    public function modifyPoint( )
    {
        $this->begin( "index.php?ctl=member/member&act=detail&p[0]=".$_POST['member_id']."&p[1]=showModifyPoint" );
        if ( $_POST['modify_point'] )
        {
            if ( 0 < $_POST['member_id'] )
            {
                $oMemberPoint =& $this->system->loadModel( "trading/memberPoint" );
                $this->end( $oMemberPoint->chgPoint( $_POST['member_id'], $_POST['modify_point'], "operator_adjust", $this->system->op_id ), __( "修改成功！" ) );
            }
            else
            {
                $this->end( FALSE, __( "会员ID参数丢失" ) );
            }
        }
        else
        {
            $this->end( FALSE, __( "输入积分无效" ) );
        }
    }

    public function modifyExperience( )
    {
        $this->begin( "index.php?ctl=member/member&act=detail&p[0]=".$_POST['member_id']."&p[1]=showExperience" );
        if ( $_POST['modify_experience'] )
        {
            if ( 0 < $_POST['member_id'] )
            {
                $oMemberExp =& $this->system->loadModel( "trading/memberExperience" );
                $this->end( $oMemberExp->chgExperience( $_POST['member_id'], $_POST['modify_experience'], "operator_adjust", $this->system->op_id ), __( "修改成功！" ) );
            }
            else
            {
                $this->end( FALSE, __( "会员ID参数丢失" ) );
            }
        }
        else
        {
            $this->end( FALSE, __( "输入经验值无效" ) );
        }
    }

    public function modifyDeposit( )
    {
        $this->begin( "index.php?ctl=member/member&act=detail&p[0]=".$_POST['member_id']."&p[1]=advance" );
        if ( $_POST['modify_advance'] )
        {
            if ( 0 < $_POST['member_id'] )
            {
                $oAdv =& $this->system->loadModel( "member/advance" );
                if ( 0 < $_POST['modify_advance'] )
                {
                    $this->end( $oAdv->add( $_POST['member_id'], $_POST['modify_advance'], $_POST['modify_memo'], __( "修改成功！" ), "", "", "", $this->system->op_name.__( "代充值" ) ) );
                }
                else
                {
                    $this->end( $oAdv->deduct( $_POST['member_id'], abs( $_POST['modify_advance'] ), $_POST['modify_memo'], __( "修改成功！" ), "", "", "", $this->system->op_name.__( "代扣费" ) ) );
                }
            }
            else
            {
                $this->end( FALSE, __( "会员ID参数丢失" ) );
            }
        }
        else
        {
            $this->end( FALSE, __( "输入预存款金额无效" ) );
        }
    }

    public function showPointHistory( $nMId )
    {
        $oPointHistory =& $this->system->loadModel( "trading/pointHistory" );
        $this->pagedata['historys'] = $oPointHistory->getPointHistoryList( $nMId );
    }

    public function detailExtInfo( $nMId )
    {
        $oMem =& $this->system->loadModel( "member/member" );
        $aItems = $oMem->getExtInfoById( $nMId );
        $this->pagedata['mem'] = $aItems;
        $this->display( "member/sub_ext_info.html" );
    }

    public function batchEdit( )
    {
        $filter = $_POST;
        $ret = $this->model->getColumns( $filter );
        $to_cfg = array(
            "member_lv_id" => $ret['member_lv_id'],
            "point" => $ret['point']
        );
        $rowCount = $this->model->count( $filter );
        $this->pagedata['editInfo'] = array(
            "cols" => $to_cfg,
            "count" => $rowCount
        );
        $this->pagedata['filter'] = htmlspecialchars( serialize( $_POST ) );
        $this->pagedata['finder'] = $_GET['finder'];
        $this->display( "member/batch_edit.html" );
    }

    public function saveBatchEdit( )
    {
        $filter = unserialize( $_POST['filter'] );
        $set = array( );
        foreach ( $_POST['enable'] as $k => $v )
        {
            if ( $k == "member_lv_id" && $_POST['set'][$k] == "" )
            {
                $this->splash( "failed", "index.php?ctl=member/member", __( "保存失败：您还没选择会员等级" ) );
                exit( );
            }
            if ( $k == "point" && ( $_POST['set'][$k] < 0 || !is_numeric( $_POST['set'][$k] ) ) )
            {
                $this->splash( "failed", "index.php?ctl=member/member", __( "保存失败：积分必须是大于等于0的数字" ) );
                exit( );
            }
            $set[$k] = $this->model->columnValue( $k, $_POST['set'][$k] );
        }
        ini_set( "track_errors", "1" );
        restore_error_handler( );
        if ( @$this->model->update( $set, $filter ) )
        {
            echo "ok";
        }
        else
        {
            echo $GLOBALS['php_errormsg'];
        }
    }

    public function detailEdit( $nMId, $output = 0 )
    {
        $oMem =& $this->system->loadModel( "member/member" );
        $aItems = $oMem->getBasicInfoById( $nMId );
        if ( $aItems )
        {
            foreach ( $aItems as $key => $val )
            {
                switch ( $key )
                {
                case "score_rate" :
                    $aItems[$key] = dateformat( $val );
                    break;
                default :
                    break;
                }
            }
        }
        $this->pagedata['member'] = $aItems;
        $this->pagedata['member']['birthday'] = $aItems['b_year']."-".$aItems['b_month']."-".$aItems['b_day'];
        $aLevel = array( );
        $oLev =& $this->system->loadModel( "member/level" );
        foreach ( $oLev->getMLevel( ) as $aItems )
        {
            $aLevel[$aItems['member_lv_id']] = $aItems['name'];
        }
        $this->pagedata['member']['level'] = $aLevel;
        $Memattr =& $this->system->loadModel( "member/memberattr" );
        $filter['attr_show'] = "true";
        $attr = $Memattr->getList( "*", $filter, 0, -1, array( "attr_order", "asc" ) );
        $memberinfo = $oMem->getMemberByid( $nMId );
        $memberattrvalue = $oMem->getMemberAttrvalue( $nMId );
        $attr_count = count( $attr );
        $i = 0;
        for ( ; $i < $attr_count; ++$i )
        {
            if ( $attr[$i]['attr_type'] == "checkbox" || $attr[$i]['attr_type'] == "select" )
            {
                $attr[$i]['attr_option'] = unserialize( $attr[$i]['attr_option'] );
            }
            if ( $attr[$i]['attr_group'] == "defalut" )
            {
                switch ( $attr[$i]['attr_type'] )
                {
                case "area" :
                    $attr[$i]['value'] = $memberinfo[0]['area'];
                    $regionId = substr( $memberinfo[0]['area'], strrpos( $memberinfo[0]['area'], ":" ) + 1 );
                    $dArea =& $this->system->loadModel( "trading/deliveryarea" );
                    $row = $dArea->getById( $regionId );
                    if ( $row )
                    {
                        $attr[$i]['rStatus'] = TRUE;
                    }
                    break;
                case "name" :
                    $attr[$i]['value'] = $memberinfo[0]['name'];
                    break;
                case "mobile" :
                    $attr[$i]['value'] = $memberinfo[0]['mobile'];
                    break;
                case "tel" :
                    $attr[$i]['value'] = $memberinfo[0]['tel'];
                    break;
                case "zip" :
                    $attr[$i]['value'] = $memberinfo[0]['zip'];
                    break;
                case "addr" :
                    $attr[$i]['value'] = $memberinfo[0]['addr'];
                    break;
                case "sex" :
                    $attr[$i]['value'] = $memberinfo[0]['sex'];
                    break;
                case "date" :
                    $attr[$i]['value'] = $memberinfo[0]['b_year']."-".$memberinfo[0]['b_month']."-".$memberinfo[0]['b_day'];
                    if ( $attr[$i]['value'] == "--" )
                    {
                        $attr[$i]['value'] = "";
                    }
                    break;
                case "pw_answer" :
                    $attr[$i]['value'] = $memberinfo[0]['pw_answer'];
                    break;
                case "pw_question" :
                    $attr[$i]['value'] = $memberinfo[0]['pw_question'];
                    break;
                }
            }
            else
            {
                $memvalue_count = count( $memberattrvalue );
                $j = 0;
                for ( ; $j < $memvalue_count; ++$j )
                {
                    if ( $attr[$i]['attr_id'] == $memberattrvalue[$j]['attr_id'] )
                    {
                        $attr[$i]['value'] = $memberattrvalue[$j]['value'];
                        if ( $attr[$i]['attr_type'] == "cal" )
                        {
                            $attr[$i]['value'] = $memberattrvalue[$j]['value'];
                        }
                        if ( $attr[$i]['attr_type'] == "checkbox" )
                        {
                            $date = $oMem->getattrvalue( $nMId, $attr[$i]['attr_id'] );
                            $attr[$i]['value'] = $date;
                        }
                    }
                }
            }
        }
        $this->pagedata['tree'] = $attr;
        if ( $output == 1 )
        {
            $this->pagedata['title'] = $this->pagedata['member']['uname'];
            $this->pagedata['nosubmit'] = TRUE;
            $this->singlepage( "member/member_edit.html" );
        }
    }

    public function detailMsg( $nMId )
    {
        $oMsg =& $this->system->loadModel( "resources/msgbox" );
        $aMsg = $oMsg->getMsgListByMemId( $nMId );
        $this->pagedata['itemss'] = $aMsg;
        $this->pagedata['member_id'] = $nMId;
    }

    public function member_ask( $nMId )
    {
        $this->_detailComm( $nMId, "ask" );
    }

    public function member_feedback( $nMId )
    {
        $this->_detailComm( $nMId, "discuss" );
    }

    public function _detailComm( $nMId, $item )
    {
        $oComm =& $this->system->loadModel( "comment/comment" );
        $aComm = $oComm->getCommListByMemId( $nMId, $item );
        if ( $item == "ask" )
        {
            $this->pagedata['ask_items'] = $aComm;
        }
        else
        {
            $this->pagedata['discuss'] = $aComm;
        }
        $this->pagedata['member_id'] = $nMId;
    }

    public function detailOrders( $nMId )
    {
        $oOrder =& $this->system->loadModel( "trading/order" );
        $aOrder = $oOrder->getOrderListByMemberId( $nMId );
        $this->pagedata['items'] = $aOrder;
        $this->pagedata['member_id'] = $nMId;
    }

    public function advance( $nMId )
    {
        $oAdv =& $this->system->loadModel( "member/advance" );
        $advList = $oAdv->getFrontAdvList( $nMId, 0, 10 );
        $this->pagedata['itemstotal'] = $advList['total'];
        $this->pagedata['items_adv'] = $advList['data'];
        $oMem =& $this->system->loadModel( "member/member" );
        $this->pagedata['member'] = $oMem->getBasicInfoById( $nMId );
    }

    public function save( )
    {
        $oMem =& $this->system->loadModel( "member/member" );
        $this->begin( "index.php?ctl=member/member&act=detail&p[0]=".$_POST['member_id']."&p[1]=detailEdit" );
        if ( 0 < $_POST['member_id'] )
        {
            if ( $_POST['birthday'] )
            {
                $aTmp = explode( "-", $_POST['birthday'] );
                $GLOBALS['_POST']['b_year'] = $aTmp[0];
                $GLOBALS['_POST']['b_month'] = $aTmp[1];
                $GLOBALS['_POST']['b_day'] = $aTmp[2];
            }
            $allkeys = array_keys( $_POST );
            $count = 0;
            $keys_count = count( $allkeys );
            $i = 0;
            for ( ; $i < $keys_count; ++$i )
            {
                if ( is_numeric( $allkeys[$i] ) )
                {
                    if ( !is_array( $_POST[$allkeys[$i]] ) )
                    {
                        $memattr[$count]['member_id'] = $_POST['member_id'];
                        $memattr[$count]['attr_id'] = $allkeys[$i];
                        $memattr[$count]['value'] = htmlspecialchars( $_POST[$allkeys[$i]] );
                        $oMem->updateMemAttr( $_POST['member_id'], $allkeys[$i], $memattr[$count] );
                        ++$count;
                    }
                    else
                    {
                        $tmp = $_POST[$allkeys[$i]];
                        $oMem->deleteMattrvalues( $allkeys[$i], $_POST['member_id'] );
                        $tmp_countn = count( $tmp );
                        $j = 0;
                        for ( ; $j < $tmp_countn; ++$j )
                        {
                            $tmpdate['member_id'] = $_POST['member_id'];
                            $tmpdate['attr_id'] = $allkeys[$i];
                            $tmpdate['value'] = htmlspecialchars( $tmp[$j] );
                            $oMem->saveMemAttr( $tmpdate );
                        }
                    }
                }
            }
            $this->end( $oMem->save( $_POST['member_id'], $_POST ), __( "修改成功！" ) );
        }
        else
        {
            $this->end( FALSE, __( "会员信息丢失，保存失败！" ) );
        }
    }

    public function showNew( )
    {
        $oLev =& $this->system->loadModel( "member/level" );
        $this->pagedata['memLv'] = $oLev->getMLevel( );
        $this->pagedata['defLv'] = $oLev->getDefauleLv( );
        $mematt =& $this->system->loadModel( "member/memberattr" );
        $filter['attr_show'] = "true";
        $tmpdate = $mematt->getList( "*", $filter, 0, -1, array( "attr_order", "asc" ) );
        $t_count = count( $tmpdate );
        $i = 0;
        for ( ; $i < $t_count; ++$i )
        {
            if ( $tmpdate[$i]['attr_type'] == "select" || $tmpdate[$i]['attr_type'] == "checkbox" )
            {
                $tmpdate[$i]['attr_option'] = unserialize( $tmpdate[$i]['attr_option'] );
            }
        }
        $this->pagedata['tree'] = $tmpdate;
        $this->path[] = array(
            "text" => __( "添加会员" )
        );
        $this->singlepage( "member/member_new.html" );
    }

    public function addMemByAdmin( )
    {
        $this->begin( "index.php?ctl=member/member&act=index" );
        $oMem =& $this->system->loadModel( "member/member" );
        $GLOBALS['_POST']['uname'] = trim( strtolower( $_POST['uname'] ) );
        $GLOBALS['_POST']['sex'] = intval( $_POST['sex'] );
        if ( $_POST['birthday'] )
        {
            $GLOBALS['_POST']['birthday'] = date( "Y-m-d", $_POST['birthday'] );
            $aTmp = explode( "-", $_POST['birthday'] );
            $GLOBALS['_POST']['b_year'] = $aTmp[0];
            $GLOBALS['_POST']['b_month'] = $aTmp[1];
            $GLOBALS['_POST']['b_day'] = $aTmp[2];
        }
        $id = $oMem->addMemberByAdmin( $_POST, $message );
        if ( $id != "" && $id )
        {
            $allkeys = array_keys( $_POST );
            $count = 0;
            $keys_co = count( $allkeys );
            $i = 0;
            for ( ; $i < $keys_co; ++$i )
            {
                if ( is_numeric( $allkeys[$i] ) )
                {
                    if ( is_array( $_POST[$allkeys[$i]] ) )
                    {
                        $ar = $_POST[$allkeys[$i]];
                        $ar_count = count( $ar );
                        $j = 0;
                        for ( ; $j < $ar_count; ++$j )
                        {
                            $arra[0]['member_id'] = $id;
                            $arra[0]['attr_id'] = $allkeys[$i];
                            $arra[0]['value'] = htmlspecialchars( $ar[$j] );
                            $oMem->saveMemAttr( $arra[0] );
                        }
                    }
                    else
                    {
                        $memattr['member_id'] = $id;
                        $memattr['attr_id'] = $allkeys[$i];
                        $memattr['value'] = htmlspecialchars( $_POST[$allkeys[$i]] );
                        $oMem->saveMemAttr( $memattr );
                    }
                }
            }
            $this->end( TRUE, __( "添加成功！" ) );
        }
        else
        {
            $this->end( FALSE, $message );
        }
    }

    public function Remark( $nMId )
    {
        $oMem =& $this->system->loadModel( "member/member" );
        $tmpdata = $oMem->getRemark( $nMId );
        $this->pagedata['member_id'] = $nMId;
        $this->pagedata['remark'] = $tmpdata['remark'];
        $this->pagedata['remark_type'] = $tmpdata['remark_type'];
    }

    public function addRemark( $nMId )
    {
        $this->begin( "index.php?ctl=member/member&act=detail&p[0]=".$nMId."&p[1]=Remark" );
        $oMem =& $this->system->loadModel( "member/member" );
        $this->end( $oMem->addRemark( $nMId, $_POST ), __( "添加成功！" ) );
    }

    public function updatePassword( $nMId, $email, $uname, $name )
    {
        $this->pagedata['member_id'] = $nMId;
        $this->pagedata['email'] = $email;
        $this->pagedata['uname'] = $uname;
        $this->pagedata['name'] = $name;
        $this->display( "member/sub_password.html" );
    }

    public function toUpdatePassword( $nMId, $email, $uname, $name )
    {
        $name = $_POST['name'];
        $errinfo = "";
        if ( strlen( $_POST['newPassword'] ) < 4 )
        {
            $errinfo = __( "新密码不能小于4位" );
        }
        else if ( strlen( $_POST['confirmPassword'] ) < 4 )
        {
            $errinfo = __( "确认密码不能小于4位" );
        }
        else if ( $_POST['newPassword'] != $_POST['confirmPassword'] )
        {
            $errinfo = __( "您两次输入的密码不一样，请重新输入。" );
        }
        if ( !empty( $errinfo ) )
        {
            $this->splash( "failed", "index.php?ctl=member/member&act=updatePassword&p[0]=".$nMId."&p[1]=".$email, __( $errinfo ) );
            exit( );
        }
        $oMem =& $this->system->loadModel( "member/account" );
        if ( $oMem->adminUpdateMemberPassword( $nMId, array(
            "password" => md5( trim( $_POST['newPassword'] ) ),
            "passwd" => $_POST['newPassword'],
            "email" => $email,
            "uname" => $uname,
            "name" => $name
        ), $_POST['sendemail'] ) )
        {
            echo "<div class='success'>密码修改成功!</div><script>\$('editMemberPassword-'+{$nMId}).retrieve('dialog').close.delay(300,\$('editMemberPassword-'+{$nMId}).retrieve('dialog'))</script>";
        }
        else
        {
            $this->splash( "failed", "index.php?ctl=member/member&act=updatePassword&p[0]=".$nMId."&p[1]=".$email, __( "密码更新操作失败！" ) );
        }
    }

    public function recycle( )
    {
        $memberHasAdvanceNums = $this->model->checkMemberHasAdvance( $_POST );
        $GLOBALS['_POST']['minadvance'] = 0;
        $GLOBALS['_POST']['maxadvance'] = 0.001;
        $returnStr = "";
        if ( 0 < $memberHasAdvanceNums )
        {
            $returnStr = $memberHasAdvanceNums.__( "个会员预付款余额不为零，无法删除。请先扣除预付款，再删除会员。" );
        }
        else
        {
            $rs = $this->model->recycle( $_POST );
            if ( $rs )
            {
                $returnStr = __( "选定记录删入回收站" );
            }
            else
            {
                $returnStr = __( "选定记录无法删入回收站" );
            }
        }
        echo $returnStr;
    }

    public function delete( )
    {
        $memberHasAdvanceNums = $this->model->checkMemberHasAdvance( $_POST, "recycle" );
        $GLOBALS['_POST']['minadvance'] = 0;
        $GLOBALS['_POST']['maxadvance'] = 0.001;
        $rs = $this->model->delete( $_POST );
        $returnStr = "";
        if ( 0 < $memberHasAdvanceNums )
        {
            $returnStr = $memberHasAdvanceNums.__( "个会员预付款余额不为零，无法删除。请先扣除预付款，再删除会员。" );
        }
        else if ( $rs )
        {
            $returnStr = __( "选定记录已删除成功!" );
        }
        else
        {
            $returnStr = __( "选定记录无法删除!" );
        }
        echo $returnStr;
    }

    public function orderInfo( $order_id )
    {
        $this->pagedata['order_id'] = $order_id;
        $this->display( "member/member_ordertab.html" );
    }

}

?>
