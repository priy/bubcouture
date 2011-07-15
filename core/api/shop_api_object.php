<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class shop_api_object
{

    public $data_format = "xml";
    public $version = "1.0";
    public $columns_split = "|";
    public $select_limited = 20;
    public $model_instance = NULL;
    public $gzip = NULL;
    public $application_error = array( );

    public function shop_api_object( )
    {
        if ( !$this->system )
        {
            $this->system =& $GLOBALS['GLOBALS']['system'];
        }
        if ( !$this->db )
        {
            $this->db = $this->system->database( );
        }
    }

    public function load_api_instance( $act, $api_version )
    {
        if ( !$this->method )
        {
            $this->method = include( CORE_DIR."/api/include/api_link.php" );
        }
        $callmethod = $this->method[$act][$api_version];
        if ( $ctl = $callmethod['ctl'] )
        {
            if ( !$this->model_instance[$ctl] )
            {
                include_once( CORE_DIR."/".dirname( $ctl )."/".$api_version."/".basename( $ctl ).".php" );
                $ctl = basename( $ctl );
                ( );
                $this->model_instance[$ctl] = new $ctl( );
                $this->model_instance[$ctl]->data_format = $this->data_format;
            }
            return $this->model_instance[$ctl];
        }
    }

    public function return_date( $data )
    {
        switch ( $this->data_format )
        {
        case "string" :
            $result = print_r( $data, TRUE );
            break;
        case "json" :
            $this->_header( "text/html" );
            $data = function_exists( "ucs_encode" ) ? ucs_encode( $data ) : $data;
            $result = json_encode( $data );
            break;
        case "xml" :
            $this->_header( "text/xml" );
            $xml =& $this->system->loadModel( "utility/xml" );
            $result = $xml->array2xml( $data, "shopex" );
            break;
        case 3 :
            break;
        case "soap" :
            break;
        default :
            $this->api_response( "fail", "language error", $data );
            break;
        }
        if ( $this->gzip && function_exists( "gzencode" ) )
        {
            echo gzencode( $result );
        }
        else
        {
            echo $result;
        }
        exit( );
    }

    public function varify_date_whole( &$data )
    {
        $aData = $this->getColumns( );
        foreach ( $data as $key => $v )
        {
            if ( $aData[$key] )
            {
                $result[$key] = $v;
                unset( $data[$key] );
            }
        }
        if ( $data )
        {
            $this->api_response( "fail", "data fail", $data );
        }
        return $result;
    }

    public function _header( $content = "text/html", $charset = "utf-8" )
    {
        header( "Content-type: ".$content.";charset=".$charset );
        if ( $this->gzip && function_exists( "gzencode" ) )
        {
            header( "Content-Encoding: gzip" );
        }
        header( "Cache-Control: no-cache,no-store , must-revalidate" );
        $expires = gmdate( "D, d M Y H:i:s", time( ) + 20 );
        header( "Expires: ".$expires." GMT" );
    }

    public function api_error_log( $msg, $data )
    {
        $path = HOME_DIR."/logs";
        $handle = fopen( $path."/".date( "Ymd" ).".log", "a+" );
        $content = "data:".date( "Y m d H:i:s" ).print_r( $data, TRUE )."\r\n";
        fwrite( $content );
        fclose( $handle );
    }

    public function _filter( $where = array
    (
        0 => 1
    ), $filter )
    {
        $filter['pages'] = $filter['pages'] ? intval( $filter['pages'] ) : 1;
        $filter['counts'] = $filter['counts'] ? intval( $filter['counts'] ) : $this->select_limited;
        if ( $this->select_limited < $filter['counts'] )
        {
            $filter['counts'] = $this->select_limited;
        }
        $limit = " limit ".intval( $filter['pages'] - 1 ) * $filter['counts'].",".$filter['counts'];
        if ( 1 < count( $where ) )
        {
            $result = " where ".implode( $where, " and " );
        }
        else
        {
            $result = " where ".$where[0];
        }
        if ( $filter['orderby'] )
        {
            $result .= " order by ".$filter['orderby'];
        }
        if ( $filter['sort_type'] )
        {
            $result .= " ".$filter['sort_type'];
        }
        $result .= $limit;
        if ( trim( $result ) == "where limit 0,100" )
        {
            $result = $limit;
        }
        return $result;
    }

    public function load_model( $path, $apiversion = "1.0" )
    {
        $file = API_DIR."/".dirname( $path )."/".$apiversion."/model/mdl.".basename( $path ).".php";
        if ( file_exists( $file ) )
        {
            require_once( $file );
            $mdl_instalce = "mdl_".basename( $path );
            if ( !$this->model[$path] )
            {
                ( );
                $this->model[$path] = new $mdl_instalce( );
            }
            else
            {
                return $this->model[$path];
            }
        }
    }

    public function verify_data( &$data, &$key_value )
    {
        if ( $key_value['required'] )
        {
            foreach ( $key_value['required'] as $value )
            {
                if ( !isset( $data[$value] ) )
                {
                    $this->api_response( "fail", "data fail" );
                }
            }
        }
        if ( $key_value['columns'] )
        {
            if ( $data['columns'] )
            {
                $data['columns'] = explode( "|", $data['columns'] );
                $_tmpcolumns = "";
                $columns = $this->getColumns( );
                foreach ( $data['columns'] as $key => $v )
                {
                    if ( $columns[$v] )
                    {
                        if ( $columns[$v]['join'] )
                        {
                            $data['columns_join'][$v] = TRUE;
                            unset( $this->columns[$key] );
                        }
                        if ( $columns[$v]['name'] )
                        {
                            $data['columns'][$key] = $columns[$v]['name']." as ".$v;
                        }
                    }
                }
            }
            else if ( method_exists( $this, "getColumns" ) )
            {
                foreach ( ( array )$this->getColumns( ) as $key => $v )
                {
                    if ( $v['join'] )
                    {
                        $data['columns_join'][$key] = TRUE;
                    }
                    else if ( $v['name'] )
                    {
                        $data['columns'][] = $v['name']." as ".$key;
                    }
                    else
                    {
                        $data['columns'][] = $key;
                    }
                }
            }
            if ( is_array( $key_value['columns'] ) )
            {
                foreach ( $key_value['columns'] as $value )
                {
                    if ( !in_array( $value, $data['columns'] ) )
                    {
                        $this->api_response( "fail", "data fail" );
                    }
                }
            }
        }
        return TRUE;
    }

    public function add_application_error( $code, $debug, $info, $desc )
    {
        if ( $debug )
        {
            $this->app_error[$code]['debug'] = $debug;
        }
        if ( $info )
        {
            $this->app_error[$code]['info'] = $info;
        }
        if ( $desc )
        {
            $this->app_error[$code]['desc'] = $desc;
        }
        if ( $this->app_error[$code] )
        {
            $this->application_error[] = $this->app_error[$code];
            if ( $this->app_error[$code]['level'] == "error" )
            {
                $this->api_response( "fail", "data fail", NULL, "application error" );
            }
        }
    }

    public function api_erro_table( $code )
    {
        return $error( $code );
    }

    public function api_response( $resCode, $errorCode = FALSE, $data = NULL, $info = NULL )
    {
        $resposilbe = array( "true" => "success", "fail" => "fail", "wait" => "wait" );
        if ( $errorCode )
        {
            $error = $this->error_code( $errorCode );
            if ( constant( "API_ERROR_LOG" ) )
            {
                if ( constant( "API_ERROR_LOG_LEVEL" ) )
                {
                    if ( API_ERROR_LOG_LEVEL <= $error['level'] )
                    {
                        $this->api_error_log( $error, $data );
                    }
                }
                else
                {
                    $this->api_error_log( $error, $data );
                }
            }
            $result['result'] = $resposilbe[$resCode];
            $result['msg'] = $error['code'];
            $result['info'] = $info ? $info : $errorCode;
        }
        else
        {
            foreach ( $data['data_info'] as $key => $value )
            {
                if ( !$data['data_info'][$key] && $data['data_info'][$key] !== "0" && $data['data_info'][$key] !== 0 )
                {
                    $data['data_info'][$key] = "";
                }
            }
            $result['result'] = $resposilbe[$resCode];
            $result['msg'] = "";
            $result['info'] = $data;
        }
        if ( $this->application_error && is_array( $this->application_error ) )
        {
            $result['application_error'] = $this->application_error;
        }
        echo $this->return_date( $result );
        exit( );
    }

    public function &error_code( $code )
    {
        if ( $this->error )
        {
            $this->error = include( "include/api_error_handle.php" );
        }
        return $this->error[$code];
    }

}

?>
