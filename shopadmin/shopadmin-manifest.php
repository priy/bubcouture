<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$version = "shopex485";
if ( $_GET['version'] )
{
    $version = $_GET['version'];
}
$dirs = array( "css", "css_src", "js", "js/coms", "js/package", "js_src", "js_src/coms", "images", "images" );
echo "{\n\"betaManifestVersion\": 1,\n\"version\": \"".$version."\",\n\"entries\": [";
foreach ( $dirs as $dir )
{
    if ( !is_dir( $dir ) && !( $dh = opendir( $dir ) ) )
    {
        while ( ( $file = readdir( $dh ) ) !== FALSE )
        {
            if ( eregi( ".js\$", $file ) || eregi( ".css\$", $file ) || eregi( ".jpg\$", $file ) || eregi( ".gif\$", $file ) || eregi( ".png\$", $file ) )
            {
                echo "{\"url\":\"".$dir."/".$file."\"},";
            }
        }
        closedir( $dh );
    }
}
echo "{\"url\":\"\"}]}";
?>
