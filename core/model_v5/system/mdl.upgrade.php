<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class Upgrade
{

    public $step = NULL;
    public $msg = NULL;
    public $status = NULL;
    public $title = "";
    public $funcList = array( );
    public $runFunc = "first";
    public $version = NULL;
    public $updateMsg = NULL;

    public function Upgrade( )
    {
        $this->system =& $system;
        $this->db =& $this->system->database( );
        set_time_limit( 0 );
        foreach ( array_diff( get_class_methods( $this ), get_class_methods( "Upgrade" ) ) as $func )
        {
            if ( substr( $func, 0, 8 ) != "upgrade_" )
            {
                continue;
            }
            $this->funcList[] = substr( $func, 8 );
        }
    }

    public function upgrade_first( )
    {
    }

    public function upgrade_last( )
    {
    }

    public function __Upgrade( )
    {
        eval( "\$this->status = \$this->upgrade_".$this->runFunc."();" );
        echo $this->updateMsg;
        if ( $this->runFunc == "first" )
        {
            $this->title = $this->title ? $this->title : __( "初始化升级" );
            $this->runFunc = array_key_exists( 0, $this->funcList ) ? $this->funcList[0] : "last";
            $this->status = $this->status ? $this->status : "finish";
        }
        else if ( $this->runFunc == "last" )
        {
            $this->status = "all-finish";
            echo "<input type=\"hidden\" class=\"upgrade-notice\" value='".json_encode( $this->noticeMsg )."'/>";
        }
        else
        {
            switch ( $this->status )
            {
            case "continue" :
                $this->title = $this->title ? __( "正在升级 " ).$this->title : __( "升级中" );
                break;
            case "finish" :
                $this->title = $this->title ? $this->title : __( "升级中" );
                $funcKey = array_search( $this->runFunc, $this->funcList );
                if ( array_key_exists( $funcKey + 1, $this->funcList ) )
                {
                    $this->runFunc = $this->funcList[$funcKey + 1];
                }
                else
                {
                    $this->title = __( "回收升级临时数据" );
                    $this->runFunc = "last";
                }
                break;
            case "error" :
                echo "<input type=\"hidden\" class=\"upgrade-notice\" value='".json_encode( $this->noticeMsg )."'/>";
                $this->title .= __( " 升级出错" );
                break;
            default :
                $this->status = "all-finish";
                break;
            }
        }
        echo "<input type=\"hidden\" class=\"up-title\" value=\"".$this->title."\"/>";
        echo "<input type=\"hidden\" class=\"runFunc\" value=\"".$this->runFunc."\" />";
        echo "<input type=\"hidden\" class=\"run-status\" value=\"".$this->status."\"/>";
    }

}

function update_message( $msg, $etype = 0, $errinfo = null )
{
    $upgradeVersion = substr( $_POST['file'], 0, -4 );
    $logFileName = HOME_DIR."/logs/upgrade_".$upgradeVersion."_".$_POST['timeline'].".log.php";
    switch ( $etype )
    {
    case E_WARNING :
    case E_USER_WARNING :
        error_log( __( "出错 " ).$msg.( $errinfo ? "->".$errinfo : "" )." \n \n", 3, $logFileName );
        return "<div class=\"runsqldetail\"><div class=\"sql-body\">".$msg.__( "</div><div class=\"error\">[出错]" ).$errinfo."</div></div>";
    case E_ERROR :
    case E_USER_ERROR :
        error_log( __( "警告 " ).$msg.( $errinfo ? "->".$errinfo : "" )." \n \n", 3, $logFileName );
        return "<div class=\"runsqldetail\"><div class=\"sql-body\">".$msg.__( "</div><div class=\"warning\">[警告]" ).$errinfo."</div></div>";
    default :
        error_log( __( "成功 " ).$msg.( $errinfo ? "->".$errinfo : "" )." \n \n", 3, $logFileName );
        return __( "<div class=\"runsqldetail\"><div class=\"succ\">[成功]" ).$msg."</div></div>";
    }
}

class mdl_upgrade extends modelFactory
{

    public $line = 0;
    public $sql = null;
    public $succ = false;

    public function exec( $step )
    {
        if ( method_exists( $this, $action = "action_".$step ) )
        {
            $this->$action( );
        }
        else
        {
            $this->action_welcome( );
        }
    }

    public function action_welcome( )
    {
        $versionTxt = $this->system->version( );
        $dbver = $_GET['force_ver'] ? $_GET['force_ver'] : $this->dbVersion( );
        $this->system->cache->clear( );
        $smarty =& $this->system->loadModel( "system/frontend" );
        $smarty->compile_dir = HOME_DIR."/cache/front_tmpl";
        $smarty->clear_all_cache( );
        if ( $this->checkFileList( BASE_DIR."/upgrade.php", $failedFiles, 1 ) || $_GET['ignore_lost'] )
        {
            $scripts = $this->scripts( $dbver, $versionTxt['rev'] );
            $data['scripts'] =& $scripts;
            $data['scripts_count'] = count( $scripts );
            if ( $data['scripts_count'] )
            {
                $this->output( "ready.html", $data );
            }
            else
            {
                $this->output( "done.html", $data );
            }
        }
        else
        {
            $data['files'] = $failedFiles;
            $this->output( "uploading.html", $data );
        }
    }

    public function action_runscript( )
    {
        header( "Content-type: text/html;charset=utf-8" );
        set_time_limit( 0 );
        $file = $_POST['file'];
        if ( !file_exists( HOME_DIR."/logs/upgrade_".substr( $_POST['file'], 0, -4 )."_".$_POST['timeline'].".log.php" ) )
        {
            error_log( "<?php exit()?> \n \n", 3, HOME_DIR."/logs/upgrade_".substr( $_POST['file'], 0, -4 )."_".$_POST['timeline'].".log.php" );
        }
        switch ( ext_name( $file ) )
        {
        case ".php" :
            include( CORE_DIR."/updatescripts/".$file );
            if ( !class_exists( "UpgradeScript" ) )
            {
                break;
            }
            $oUpgrade = new UpgradeScript( );
            $oUpgrade->step = $_POST['step'] ? $_POST['step'] : "1";
            $oUpgrade->runFunc = $_POST['runFunc'] ? $_POST['runFunc'] : "first";
            $oUpgrade->status = $_POST['runStatus'] ? $_POST['runStatus'] : "all-finish";
            $oUpgrade->version = substr( $file, 0, -4 );
            $oUpgrade->__Upgrade( );
            $this->setDbver( substr( $file, 0, -4 ) );
            break;
        case ".sql" :
            $this->run_update_sql( $file );
            break;
        case ".db" :
            $this->diff_db( );
            break;
        }
    }

    public function setDbver( $ver )
    {
        $this->db->exec( "drop table if exists ".DB_PREFIX."dbver" );
        $this->db->exec( "create table ".DB_PREFIX."dbver(`".$ver."` varchar(255))" );
        $this->db->exec( "INSERT INTO ".DB_PREFIX."dbver VALUES('".time( )."')" );
    }

    public function diff_db( )
    {
        $schema =& $this->system->loadModel( "utility/schemas" );
        $dbtables = $schema->get_system_schemas( );
        foreach ( $dbtables as $tbname => $struct )
        {
            if ( $diffsql = $schema->diff( $tbname, $struct ) )
            {
                foreach ( $diffsql as $sql )
                {
                    $this->db->exec( $sql );
                }
            }
        }
        return true;
    }

    public function run_update_sql( $file )
    {
        echo "success!";
    }

    public function output( $output, $data )
    {
        $data['page'] = $output;
        $smarty =& $this->system->loadModel( "system/frontend" );
        $smarty->template_dir = CORE_DIR."/admin/view/";
        $data['version'] = $this->system->version( );
        $smarty->_vars =& $data;
        header( "Content-Type: text/html;charset=utf-8" );
        $smarty->display( "upgrade/page.html" );
    }

    public function checkFileList( $file, &$list, $ignore_lines = 0 )
    {
        $list = array( );
        $handle = fopen( $file, "r" );
        while ( $line = fgetcsv( $handle, 1000, "," ) )
        {
            if ( $i++ < $ignore_lines )
            {
                continue;
            }
            if ( md5_file( BASE_DIR."/".$line[0] ) != $line[1] )
            {
                $list[] = $line[0];
            }
        }
        fclose( $handle );
        return 0 == count( $list );
    }

    public function scripts( $from, $to )
    {
        $dir = CORE_DIR."/updatescripts";
        if ( $handle = opendir( $dir ) )
        {
            while ( false !== ( $file = readdir( $handle ) ) )
            {
                if ( !( is_file( $dir."/".$file ) && $file[0] != "." && !strstr( $file, ".sql" ) ) && !preg_match( "/^([0-9]+)\\.(sql|php)\$/i", $file, $match ) && !( $from < $match[1] && $match[1] <= $to ) )
                {
                    $step[] = $file;
                    if ( $match[2] == "php" )
                    {
                        $order[] = $match[1] * 2 + 1;
                    }
                    else
                    {
                        $order[] = $match[1] * 2;
                    }
                }
            }
            closedir( $handle );
        }
        array_multisort( $order, $step );
        return array_merge( array( "upgrade.db" ), $step );
    }

    public function dbVersion( )
    {
        $rs = $this->db->exec( "SHOW TABLES like \"".DB_PREFIX."dbver\"" );
        if ( $this->db->getRows( $rs, 1 ) )
        {
            $rs = $this->db->exec( "select * from ".DB_PREFIX."dbver" );
            $col = mysql_fetch_field( $rs['rs'], 0 );
            $time = mysql_fetch_row( $rs['rs'] );
            $rs = null;
            if ( $col->name == "dbver" )
            {
                $col->name = 35789;
            }
            $dbver = $col->name;
        }
        else
        {
            $dbver = 35789;
        }
        if ( $time[0] && time( ) - 180 < $time[0] )
        {
            return $dbver;
        }
        else
        {
            return $dbver - 1;
        }
    }

}

?>
