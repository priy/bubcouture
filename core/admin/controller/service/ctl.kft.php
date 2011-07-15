<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

echo "<?";
echo "\nclass ctl_kft extends adminPage{\n    var \$workground ='setting';\n    function ctl_kft(){\n        parent::adminPage();\n        \$this->kft=&\$this->system->loadModel('service/kft');\n        \$this->certi_id=\$this->kft->getCerti();\n        \$this->token=\$this->kft->getToken();\n        \$this->API_URL='http://api-client.shopex.cn/api.php';\n    }\n    function index(){\n        /**\n            @kft_action:\n";
echo "                @TOCHECK    :    检测\n                @TOREG    :    注册\n                @TOOPEN    :    开通\n                @TOBIND    :    已经绑定\n        */\n        set_time_limit(0);\n        \$this->pagedata['kft']=\$this->kft->checkLicense();\n        switch(\$_POST['action']){\n            case 'toapply':\n                \$return=\$this->toapply();\n                break;\n            ca";
echo "se 'toreg':\n                \$return=\$this->toreg();\n                break;\n            case 'topen':\n                \$return=\$this->topen();\n                break;\n            case 'toclear':\n                \$return=\$this->toclear();\n                break;\n            default :\n                break;\n        }\n        if(false){\n            echo \$return;\n        }\n        \$this->pagedata['action']";
echo "=\$this->kft->getAction();\n        \$this->page('service/kft.html');\n    }\n    function toclear(){\n        /**\n            @清除状态\n        */\n        \$this->kft->setAction('TOAPPLY');\n    }\n    function toapply(){\n        \$this->pagedata['kft']=\$this->kft->checkLicense();\n        \$url=\$this->url();\n        \$result=\$this->kft->apply(\$url,'TOCHECK',\$aS);\n        \$this->kft->setAction(\$this->kft-";
echo ">checkstr(\$result));\n    }\n    function toreg(){\n        \$aS = array('email'=>\$_POST['email']);\n        \$result=\$this->kft->apply(\$this->url(),'TOREG',\$aS);\n        \$this->kft->setAction(\$this->kft->checkstr(\$result));\n    }\n    function topen(){\n        \$result=\$this->kft->apply(\$this->url(),'TOPEN',\$aS);\n        \$this->kft->setAction(\$this->kft->checkstr(\$result));\n    }\n    function url(){\n    ";
echo "    if(!\$this->kft->getKftUrl()){\n            \$url=\$this->kft->apply(\$this->API_URL,'ShopExKFT',\$aS);\n            \$this->kft->setKftUrl(\$url);\n        }\n        return \$url=\$this->kft->getKftUrl();\n    }\n}\n?>";
?>
