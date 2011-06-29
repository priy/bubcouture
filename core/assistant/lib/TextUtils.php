<?php
class TextUtils
{
    function csv2array($csvfile, $fields, $delimiter = ',', $enclosure = '"', $callback = NULL)
    {
        LogUtils::log_str('csv2array');
        $handle = fopen($csvfile, "r");
        LogUtils::log_obj($handle);
        if (!$handle) return array();
        $row = 1;
        $list = array();
        while ($data = fgetcsv($handle, 262144, $delimiter, $enclosure)) {
            if (count($data) > count($fields))  $data   = array_slice($data, 0, count($fields));
            if (count($fields) > count($data))  $fields = array_slice($fields, 0, count($data));
            
            foreach ($data as $key=>$item)
            {
                $data[$key] = str_replace("'","\'",$item);
            }
            
            $v = array();
            for ($i = 0; $i < count($fields); $i++)
            {
                $v[$fields[$i]] = $data[$i];
            }            
            //$v = array_combine($fields, $data);                
            $list[] = $v;
            if ($callback) 
            {
                call_user_func($callback, $v);                
            }
        }
        fclose($handle);
        
        return $list;
    }
}

?>