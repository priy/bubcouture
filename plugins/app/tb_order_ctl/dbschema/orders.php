<?php
    $db['orders'] = array (
        'columns' =>
        array (
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
            'alipay_payid'=>
            array (
                'type' =>'bigint(16)',                                              
                'required' => true,
                'default'=>0,
            ),
            'consign_time'=>
            array (
                'type' =>'int(11)',
                'required' => true,
                'default'=>0,
            ),
            'pay_time'=>
            array (
                'type' =>'int(11)',
                'required' => true,
                'default'=>0,
            ),
            'delivery_time'=>
            array (
                'type' =>'int(11)',
                'required' => true,
                'default'=>0,
            ),
            'shipping_type'=>
            array(
                'type' => array('free'=>'卖家承担运费','post'=>'平邮','express'=>'快递','ems'=>'EMS','virtual'=>'虚拟物品'),
                'defalut'=> 'express',
            ),
            'refund_id'=>
            array (
                'type' =>'varchar(20)',
                'required' => true,
                'default'=>'0',
            ),
            'tb_nick'=>
            array (
                'type' =>'varchar(50)',
                'default'=>'0',
            ),
        ),
        'index' =>
        array (
        'ind_order_id' =>
            array (
                'columns' =>
                array (
                    0 => 'order_id',
                ),
            ),
         ),
    );

?>