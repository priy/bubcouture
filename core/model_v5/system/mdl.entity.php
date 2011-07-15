<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class entity
{

}

class mdl_entity extends modelFactory
{

    public $changer = null;
    public $_entity_obj = null;

    public function mdl_entity( )
    {
        parent::modelfactory( );
        if ( !class_exists( "sdf_changer" ) )
        {
            require( CORE_DIR."/lib/sdf/sdf_changer.php" );
        }
        $this->changer = new sdf_changer( );
    }

    public function get_entity( $entity )
    {
        if ( !isset( $this->_entity_obj[$entity] ) )
        {
            if ( !class_exists( "entity_".$entity ) )
            {
                require( CORE_DIR."/entity/entity.".$entity.".php" );
            }
            $class_name = "entity_".$entity;
            $object = new $class_name( );
            $object->db =& $this->db;
            $object->system =& $this->system;
            $this->_entity_obj[$entity] =& $object;
        }
        return $this->_entity_obj[$entity];
    }

    public function get_sdf( $entity, $entity_id, $type = "xml" )
    {
        $entity_obj =& $this->get_entity( $entity );
        $sdf_array =& $entity_obj->export_sdf_array( $entity_id );
        if ( $type == "json" )
        {
            return json_encode( $sdf_array );
        }
        else
        {
            return $this->changer->array_to_xml( $sdf_array );
        }
    }

}

?>
