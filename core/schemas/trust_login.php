<?php
/**
* @table trust_login;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['trust_login'] = array(
    'columns'=>array(
        'login_id'=>array(
         'type'=>'number', 
         'pkey'=>true,
         'required'=>true,
         'extra'=>'auto_increment',
      ),
      'member_id'=>array(
         'type'=>'number',
         'required'=>true,
         'default'=>0,
      ),
      'uname'=>array(
         'type'=>'varchar(50)',
         'label' => __('信任登陆用户名'),
         'required'=>true,
         'editable' => false,
         'default'=>''
      ),
      'member_refer'=>array(
         'type'=>'varchar(50)',
         'label' => __('信任登陆来源'),
         'required'=>true,
         'editable' => false,
         'default'=>'',
      ),
     'show_uname'=>array(
         'type'=>'varchar(50)',
         'default'=>''
      ),
    ),
   'index'=>array(
      'ind_id'=>array(
         'columns'=>array(
            0=>'member_id',
         ),
      ),
   ),

);