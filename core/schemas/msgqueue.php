<?php
/**
* @table msgqueue;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['msgqueue']=array (
  'columns' => 
  array (
    'queue_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
    ),
    'title' => 
    array (
      'type' => 'varchar(250)',
      'editable' => false,
    ),
    'target' => 
    array (
      'type' => 'varchar(250)',
      'required' => true,
      'default' => '',
      'editable' => false,
    ),
    'event_name' => 
    array (
      'type' => 'varchar(50)',
      'editable' => false,
    ),
    'data' => 
    array (
      'type' => 'longtext',
      'editable' => false,
    ),
    'tmpl_name' => 
    array (
      'type' => 'varchar(50)',
      'required' => true,
      'default' => '',
      'editable' => false,
    ),
    'level' => 
    array (
      'type' => 'tinyint unsigned',
      'default' => 5,
      'required' => true,
      'editable' => false,
    ),
    'sender' => 
    array (
      'type' => 'varchar(50)',
      'required' => true,
      'default' => '',
      'editable' => false,
    ),
    'sender_order' => 
    array (
      'type' => 'tinyint unsigned',
      'default' => 5,
      'required' => true,
      'editable' => false,
    ),
  ),
  'index' => 
  array (
    'ind_level' => 
    array (
      'columns' => 
      array (
        0 => 'level',
      ),
    ),
  ),
);