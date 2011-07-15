<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

if ( !class_exists( "shopObject" ) )
{
    require( "shopObject.php" );
}
class mdl_magicvars extends shopObject
{

    public $defaultCols = "var_name,var_title,var_value,var_remark";
    public $idColumn = "var_name";
    public $defaultOrder = array
    (
        0 => "var_name",
        1 => "desc"
    );
    public $tableName = "sdb_magicvars";
    public $filter = "";

    public function getColumns( $filter )
    {
        $ret = array(
            "_cmd" => array(
                "label" => __( "操作" ),
                "width" => 70,
                "html" => "system/magicvars/finder_command.html"
            )
        );
        return array_merge( $ret, parent::getcolumns( ) );
    }

    public function _filter( $filter )
    {
        if ( $this->filter )
        {
            return parent::_filter( $filter ).$this->filter;
        }
        else
        {
            return parent::_filter( $filter );
        }
    }

    public function modifier_var_value( &$rows )
    {
        foreach ( $rows as $key => $val )
        {
            $rows[$key] = preg_replace( "/images\\//", $this->system->base_url( )."images/", $val );
        }
    }

    public function insert( $data, &$message )
    {
        if ( !$this->findError( $data, $message ) )
        {
            return false;
        }
        parent::insert( $data );
        return true;
    }

    public function update( $data, $filter, &$message )
    {
        if ( !$this->findError( $data, $message ) )
        {
            return false;
        }
        parent::update( $data, $filter );
        return true;
    }

    public function findError( $data, &$message )
    {
        if ( substr( $data['var_name'], 0, 1 ) != "{" || substr( $data['var_name'], -1 ) != "}" )
        {
            $message = __( "变量名不符合格式，请重新更换填写" );
            return false;
        }
        $vars = $this->getList( "var_name", "", 0, -1 );
        foreach ( $vars as $k => $val )
        {
            $tempvars[] = $val['var_name'];
        }
        if ( !$data['is_editing'] && in_array( $data['var_name'], $tempvars ) )
        {
            $message = __( "该变量名已经存在，请重新更换填写" );
            return false;
        }
        return true;
    }

}

?>
