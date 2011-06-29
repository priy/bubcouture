<?php
define('SBT_BaseDir',0);
define('SBT_ImageDir',1);
define('SBT_CoreDir',2);
define('SBT_HomeDir',3);
define('SBT_PluginDir',4);
define('SBT_ThemeDir',5);
define('SBT_MediaDir',6);
define('SBT_DataBackDir',7);
define('SBT_UploadDir', 10);
define('SBT_AsDir',100);
define('SBT_AsServiceDir',101);
define('SBT_AsTmpDir',102);
define('SBT_AsLogDir',103);

class ServerUtils
{
    function getRealPath($baseType)
    {
        switch ($baseType)
        {
        case SBT_BaseDir:    return BASE_DIR;
        case SBT_ImageDir:    return ServerUtils::buildPath(BASE_DIR,'images');
        case SBT_CoreDir:    return CORE_DIR;
        case SBT_HomeDir:    return HOME_DIR;
        case SBT_PluginDir:    return PLUGIN_DIR;        
        case SBT_ThemeDir:    return THEME_DIR;
        case SBT_MediaDir:    return MEDIA_DIR;
        case SBT_DataBackDir:   return DATABACK_DIR;
        case SBT_UploadDir:    return ServerUtils::buildPath(HOME_DIR,'upload');
        case SBT_AsDir:        return AS_DIR;
        case SBT_AsServiceDir: return AS_SERVICE_DIR;
        case SBT_AsTmpDir:       return AS_TMP_DIR;
        case SBT_AsLogDir:       return AS_LOG_DIR;            
        }
        return '';
    }
            
    function buildPath($path1, $path2)
    {
        if (substr($path1, -1, 1) != '/') $path1 .= '/';        
        if (substr($path2, 1, 1) == '/') $path2 = substr($path2, 1);
        return $path1.$path2;
    }
    
    function combinePath($baseType, $filename)
    {        
        $file = ServerUtils::buildPath(ServerUtils::getRealPath($baseType), $filename);    
        return  ServerUtils::formalPath($file);
    }
    
    function formalPath($path)
    {
        $path = str_replace('//','/',$path);
        if ('/' != DIRECTORY_SEPARATOR) $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
        return $path;
    }
}
?>