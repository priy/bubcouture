<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "objectPage.php" );
class ctl_package extends objectPage
{

    public $finder_action_tpl = "product/package/finder_action.html";
    public $finder_default_cols = "_cmd,name,mktprice,price,store,marketable";
    public $workground = "sale";
    public $object = "trading/package";
    public $filterUnable = TRUE;

    public function addPackage( )
    {
        $this->begin( "index.php?ctl=goods/package&act=index" );
        $oPackage =& $this->system->loadModel( "trading/package" );
        $GLOBALS['_POST']['store'] = $_POST['p_store'];
        $this->end( $oPackage->savePackage( $_POST, $msg ), __( "保存成功！" ) );
    }

    public function showAddPackage( $goodsId = NULL )
    {
        $this->path[] = array(
            "text" => __( "捆绑商品内容页" )
        );
        $oPackage =& $this->system->loadModel( "trading/package" );
        if ( $goodsId )
        {
            $this->pagedata['package'] = $oPackage->getPackageById( $goodsId );
            foreach ( $oPackage->getPackageProducts( $goodsId ) as $rows )
            {
                $aId[] = $rows['product_id'];
                $aNum[$rows['product_id']] = array(
                    "num" => $rows['pkgnum']
                );
                $bn[$rows['product_id']] = array(
                    "bn" => $rows['bn']
                );
            }
            $this->pagedata['package']['products'] = $aId;
            $this->pagedata['package']['moreinfo'] = $aNum;
            $this->pagedata['package']['bn'] = $bn;
        }
        else
        {
            $this->pagedata['package']['marketable'] = "true";
            $this->pagedata['package']['p_order'] = $oPackage->getInitOrder( );
        }
        $this->pagedata['point_setting'] = $this->system->getConf( "point.get_policy" );
        $this->pagedata['package']['params'] = array( "is_local" => 1 );
        $this->page( "product/package/addPackage.html" );
    }

    public function delete( )
    {
        $this->begin( "index.php?ctl=goods/package&act=index" );
        $oPackage =& $this->system->loadModel( "trading/package" );
        $goodsIds = $oPackage->delPackage( $_POST['goods_id'] );
        $this->end_only( );
    }

}

?>
