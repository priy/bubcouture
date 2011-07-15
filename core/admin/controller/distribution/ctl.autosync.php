<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "objectPage.php" );
class ctl_autosync extends objectPage
{

    public $name = "同步配置";
    public $workground = "distribution";
    public $object = "distribution/autosync";
    public $finder_action_tpl = "distribution/auto_finder_action.html";
    public $filterUnable = TRUE;
    public $allowExport = FALSE;

    public function addNew( )
    {
        $this->path[] = array( "text" => "添加配置" );
        $this->pagedata['rule_list'] = array(
            array( "rule_relation_id" => 0, "supplier_id" => 0, "pline_id" => 0 )
        );
        $this->_common( );
    }

    public function showEdit( $rule_id )
    {
        $this->path[] = array( "text" => "修改配置" );
        $oAutoSync = $this->system->loadModel( "distribution/autosync" );
        $aInfo = $oAutoSync->getRuleInfo( $rule_id );
        $aRuleList = $oAutoSync->getRuleRelationInfo( $rule_id );
        $aTemp = $oAutoSync->getSupplierOP( $aInfo['supplier_op_id'] );
        foreach ( $aTemp['op_items'] as $row )
        {
            $aLocalOPList[$row]['op_id'] = $row;
            $aLocalOPList[$row]['op_name'] = $oAutoSync->getLocalOP( $row );
            $aLocalOPList[$row]['checked'] = $row == $aInfo['local_op_id'] ? 1 : 0;
        }
        $this->pagedata['rule_id'] = $rule_id;
        $this->pagedata['rule_info'] = $aInfo;
        $this->pagedata['rule_list'] = $aRuleList;
        $this->pagedata['local_op_list'] = $aLocalOPList;
        $this->_common( );
    }

    public function _common( )
    {
        $oSupplier = $this->system->loadModel( "distribution/supplier" );
        $aSupplierList = $oSupplier->getList( "*", "", 0, 1000 );
        $oAutoSync = $this->system->loadModel( "distribution/autosync" );
        $aSupplierOPList = $oAutoSync->getSupplierOPList( );
        $this->pagedata['supplier_op_list'] = $aSupplierOPList;
        $this->pagedata['supplier_list'] = $aSupplierList;
        $this->page( "distribution/autosync_edit.html" );
    }

    public function addRow( )
    {
        $oSupplier = $this->system->loadModel( "distribution/supplier" );
        $aSupplierList = $oSupplier->getList( "*", "", 0, 1000 );
        $this->pagedata['supplier_list'] = $aSupplierList;
        $this->__tmpl = "distribution/autosync_rule_row.html";
        $this->output( );
    }

    public function changeSupplierOP( )
    {
        $oAutoSync = $this->system->loadModel( "distribution/autosync" );
        $aTemp = $oAutoSync->getSupplierOP( $_POST['supplier_op_id'] );
        foreach ( $aTemp['op_items'] as $row )
        {
            $aList[$row]['op_id'] = $row;
            $aList[$row]['op_name'] = $oAutoSync->getLocalOP( $row );
            $aList[$row]['checked'] = $row == $aTemp['checked'] ? 1 : 0;
        }
        $this->pagedata['local_op_list'] = $aList;
        $this->__tmpl = "distribution/autosync_local_op_row.html";
        $this->output( );
    }

    public function changeSupplier( )
    {
        $oSupplier = $this->system->loadModel( "distribution/supplier" );
        $aInfo = $oSupplier->getSupplierInfo( $_POST['supplier_id'] );
        $oDataSync = $this->system->loadModel( "distribution/autosync" );
        if ( $aInfo['supplier_pline'] )
        {
            $aPline = unserialize( $aInfo['supplier_pline'] );
            if ( !empty( $aPline ) )
            {
                $oDataSync = $this->system->loadModel( "distribution/datasync" );
                $aList = $oDataSync->getProductLine( $_POST['supplier_id'] );
                if ( $aList )
                {
                    $oAutoSync = $this->system->loadModel( "distribution/autosync" );
                    if ( !$oAutoSync->isExistPlineName( $aPline ) )
                    {
                        $aPline = $oAutoSync->fillPlineName( $_POST['supplier_id'], $aList );
                    }
                }
            }
        }
        else
        {
            $aPline = array( );
        }
        $this->pagedata['pline_list'] = $aPline;
        $this->__tmpl = "distribution/autosync_pline_list.html";
        $this->output( );
    }

    public function delete( )
    {
        $oDataSync = $this->system->loadModel( "distribution/autosync" );
        $oDataSync->deleteRuleRelation( $_POST['rule_id'] );
        parent::delete( );
    }

}

?>
