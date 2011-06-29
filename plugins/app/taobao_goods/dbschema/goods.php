<?php
$db['goods'] = array (
    'columns' =>
    array (
        'goods_id' =>
        array (
            'type' => 'mediumint(8)',
            'required' => true,
            'default'=>'0',
        ),
        'outer_id' =>
        array (
            'type' =>'varchar(50)',
            'required' => true,
            'default'=>'0',
        ),
        'outer_key'=>
        array (
            'type' =>'varchar(50)',
            'required' => true,
        ),
        'outer_content'=>
        array (
            'type' =>'text',
        ),
        'disabled' =>
        array (
          'type' => 'bool',
          'default' => 'false',
        )
    ),
    'index' =>
      array (
        'index_1' =>
        array (
          'columns' =>
          array (
            0 => 'goods_id',
            1 => 'outer_key',
          ),
        ),
    )
);