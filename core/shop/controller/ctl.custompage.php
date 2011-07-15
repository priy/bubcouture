<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

echo "<?";
echo "\n    class ctl_custompage extends shopPage{\n\n    function index(\$nid){\n        \$this->_register_resource(\"custom\", array(array(&\$this,\"__resource_db_source\"),\n                                       array(&\$this,\"__resource_db_timestamp\")));\n\n        \$oTemplate=&\$this->system->loadModel('system/template');\n        \$theme = \$oTemplate->applyTheme(constant('TPL_ID'));\n        \$this->theme = \$theme['t";
echo "heme'];\n        \$this->fetch('custom:'.\$nid,1);\n    }\n\n\n    function __resource_db_source(\$tpl_name, &\$tpl_source, &\$smarty)\n    {\n        \$tmpl = &\$this->system->loadModel('content/systmpl');\n        \$tpl_source = \$tmpl->get(md5(\$tpl_name));\n        \$tpl_source = str_replace(\"[header]\",'<{require file=\"block/header.html\"}>',\$tpl_source);\n        \$tpl_source = str_replace(\"[footer]\",'<{require file=";
echo "\"block/footer.html\"}>',\$tpl_source);\n        if (\$tpl_source!==false) {\n            return true;\n        } else {\n            return false;\n        }\n    }\n\n    function __resource_db_timestamp(\$tpl_name, &\$tpl_timestamp, &\$smarty)\n    {\n        \$tpl_timestamp = time();\n        if (is_int(\$tpl_timestamp)){\n            return true;\n        } else {\n            return false;\n        }\n    }\n\n}\n\n?>";
?>
