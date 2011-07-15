<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$datatypes = array(
    "money" => array(
        "sql" => "decimal(20,3)",
        "searchparams" => array( "than" => "大于", "lthan" => "小于", "nequal" => "等于", "sthan" => "小于等于", "bthan" => "大于等于" )
    ),
    "email" => array(
        "sql" => "varchar(255)",
        "searchparams" => array( "has" => "包含", "tequal" => "等于", "head" => "开头等于", "foot" => "结尾等于", "nohas" => "不包含" )
    ),
    "bn" => array(
        "sql" => "varchar(255)",
        "searchparams" => array( "has" => "包含", "tequal" => "等于", "nohas" => "不包含" )
    ),
    "html" => array( "sql" => "text" ),
    "bool" => array(
        "sql" => "enum('true','false')",
        "searchparams" => array( "has" => "包含", "nohas" => "不包含" )
    ),
    "time" => array(
        "sql" => "integer(10) unsigned",
        "searchparams" => array( "than" => "大于", "lthan" => "小于", "nequal" => "等于" )
    ),
    "cdate" => array( "sql" => "integer(10) unsigned" ),
    "intbool" => array( "sql" => "enum('0','1')" ),
    "region" => array( "sql" => "varchar(255)" ),
    "tinybool" => array( "sql" => "enum('Y','N')" ),
    "number" => array(
        "sql" => "mediumint unsigned",
        "searchparams" => array( "than" => "大于", "lthan" => "小于", "nequal" => "等于", "sthan" => "小于等于", "bthan" => "大于等于" )
    ),
    "mediumint" => array(
        "sql" => "mediumint",
        "searchparams" => array( "than" => "大于", "lthan" => "小于", "nequal" => "等于", "sthan" => "小于等于", "bthan" => "大于等于" )
    ),
    "gender" => array( "sql" => "enum('male','female')" ),
    "ipaddr" => array( "sql" => "varchar(20)" )
);
?>
