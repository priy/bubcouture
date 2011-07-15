<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class BaseValidator
{

    public $_sys = NULL;
    public $_db = NULL;
    public $_tbpre = NULL;

    public function BaseValidator( $sys )
    {
        $this->_sys = $sys;
        $this->_db = $sys->database( );
        $this->_tbpre = isset( $GLOBALS['_tbpre'] ) ? $GLOBALS['_tbpre'] : NULL;
        if ( !$this->_tbpre && defined( "DB_PREFIX" ) )
        {
            $this->_tbpre = DB_PREFIX;
        }
    }

    public function validateInsertBefore( &$row )
    {
        return TRUE;
    }

    public function validateInsertAfter( &$row )
    {
        return TRUE;
    }

    public function validateUpdateBefore( &$row )
    {
        return TRUE;
    }

    public function validateUpdateAfter( &$row )
    {
        return TRUE;
    }

    public function validateDeleteBefore( &$row )
    {
        return TRUE;
    }

    public function validateDeleteAfter( &$row )
    {
        return TRUE;
    }

    public function loadValidators( $dir, $table, $sys )
    {
        $validators = array( );
        foreach ( as_find_files( $dir, "/^".$table."\\.([a-zA-Z0-9_]*)\\.validator\\.php\$/" ) as $file => $matches )
        {
            include_once( $dir.$file );
            $clsname = $table."_".$matches[1]."Validator";
            if ( class_exists( $clsname ) )
            {
                ( $sys );
                $cls = new $clsname( );
                if ( is_a( $cls, "BaseValidator" ) )
                {
                    $validators[] = $cls;
                }
            }
        }
        return $validators;
    }

    public function runValidateBefore( $validators, $action, &$row )
    {
    default :
        switch ( $action )
        {
            foreach ( $validators as $v )
            {
                LogUtils::log_str( "validate before ".$action.":".get_class( $v ) );
            case "insert" :
                if ( !$v->ValidateInsertBefore( $row ) )
                {
                    return FALSE;
                }
                break;
            case "update" :
                if ( !$v->ValidateUpdateBefore( $row ) )
                {
                    return FALSE;
                }
                break;
            case "delete" :
            }
            if ( !$v->ValidateDeleteBefore( $row ) )
            {
                return FALSE;
            }
            break;
        }
        return TRUE;
    }

    public function runValidateAfter( $validators, $action, &$row )
    {
    default :
        switch ( $action )
        {
            foreach ( $validators as $v )
            {
                LogUtils::log_str( "validate after ".$action.":".get_class( $v ) );
            case "insert" :
                if ( !$v->ValidateInsertAfter( $row ) )
                {
                    return FALSE;
                }
                break;
            case "update" :
                if ( !$v->ValidateUpdateAfter( $row ) )
                {
                    return FALSE;
                }
                break;
            case "delete" :
            }
            if ( !$v->ValidateDeleteAfter( $row ) )
            {
                return FALSE;
            }
            break;
        }
        return TRUE;
    }

}

?>
