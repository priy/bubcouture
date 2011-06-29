<?php
    $db['order_items'] = array (
            'columns' =>
            array (
                'item_id'=>
                array(
                    'type'=>'mediumint(8)',
                    'required' => true,
                    'pkey' => true,
                    'extra' => 'auto_increment',
                ),
                'order_id' =>
                array (
                    'type' => 'varchar(100)',
                    'required' => true,
                    'default'=>'0',
                ),
                'tb_tid' =>
                array (
                    'type' =>'varchar(100)',
                    'required' => true,
                    'default'=>'0',
                ),
                'img_path'=>
                array (
                    'type' =>'varchar(100)',
                    'required' => true,
                    'default'=>'http://assets.taobaocdn.com/sys/common/img/nopic_50.png',
                ),
                'taobao_iid'=>
                array (
                    'type' =>'varchar(40)',
                    'required' => true,
                ),
                'refund_status'=>
                array (
                    'type' =>'tinyint(1)',
                    'required' => true,
                    'default'=>0,
                ),
                'disabled'=>
                array (
                    'type' =>'bool',
                    'required' => true,
                    'default'=>'false'
                ),
                'refund_id'=>
                array (
                    'type' =>'varchar(20)',
                    'required' => true,
                    'default'=>0,
                ),
                'status'=>
                array (
                    'type' =>array('finish'=>'完成','refund'=>'退款中','wait'=>'等待'),
                    'required' => true,
                    'default'=>'wait'
                ),
                'traderate'=>
                array (
                    'type' =>'tinyint(1)',
                    'required' => true,
                    'default'=>0,
                ),
                'barcode'=>
                array (
                    'type' =>'varchar(40)',
                ),
            ),
           'index' =>
            array (
            'ind_tb_tid' =>
                array (
                    'columns' =>
                    array (
                        0 => 'tb_tid',
                    ),
                ),
            'ind_tb_iid' =>
                array (
                    'columns' =>
                    array (
                        0 => 'taobao_iid',
                    ),
                ),
            ),
        );
?>