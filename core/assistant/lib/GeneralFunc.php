<?php

function as_find_files($dir, $pattern)
{    
    $files = array();    
    if ($handle = opendir($dir)) 
    {            
        while (false !== ($file = readdir($handle))) 
        {            
            if (preg_match($pattern, $file, $matches))
            {
                $files[$file] = $matches;
            }
         }
        closedir($handle);
    }
    
    return $files;
}

?>