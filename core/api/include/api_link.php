<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

$method = array(
    "taobao_db_transfer" => array(
        "1.0" => array( "ctl" => "api/api_products", "act" => "import_taobao_database" )
    ),
    "taobao_cat_transfer" => array(
        "1.0" => array( "ctl" => "api/api_products", "act" => "taobao_cat_transfer" )
    ),
    "taobao_upload_complete" => array(
        "1.0" => array( "ctl" => "api/api_products", "act" => "taobao_upload_complete" )
    ),
    "search_goods_list" => array(
        "1.0" => array( "ctl" => "api/goods/api_1_0_goods", "act" => "search_goods_list_by_lastmodify", "columns" => "*" )
    ),
    "search_goods_detail" => array(
        "1.0" => array(
            "ctl" => "api/goods/api_1_0_goods",
            "act" => "search_goods_detail",
            "required" => array( "goods_id" ),
            "columns" => "*"
        )
    ),
    "get_goods_brand" => array(
        "1.0" => array( "ctl" => "api/goods/api_1_0_goods", "act" => "get_goods_brand", "columns" => "*" )
    ),
    "get_goods_cat" => array(
        "1.0" => array( "ctl" => "api/goods/api_1_0_goods", "act" => "get_goods_cat", "columns" => "*" )
    ),
    "add_goods_info" => array(
        "1.0" => array( "ctl" => "api/goods/api_1_0_goods", "act" => "add_goods_info", "columns" => "*" )
    ),
    "search_deleted_goods_list" => array(
        "1.0" => array(
            "ctl" => "api/goods/api_1_0_goods",
            "act" => "search_deleted_goods_list",
            "required" => array( "last_modify_st_time", "last_modify_en_time" ),
            "columns" => "*"
        )
    ),
    "search_products_list" => array(
        "1.0" => array( "ctl" => "api/product/api_1_0_product", "act" => "search_products_list", "columns" => "*" )
    ),
    "set_goods_bn" => array(
        "1.0" => array(
            "ctl" => "api/goods/api_1_0_goods",
            "act" => "set_goods_bn",
            "required" => array( "goods_id", "bn" )
        )
    ),
    "search_site_information" => array(
        "1.0" => array( "ctl" => "api/site/api_1_0_site", "act" => "search_site_information", "columns" => "*" )
    ),
    "search_site_information" => array(
        "1.0" => array( "ctl" => "api/site/api_1_0_site", "act" => "search_site_information", "columns" => "*" )
    ),
    "template_info" => array(
        "1.0" => array( "ctl" => "api/site/api_1_0_site", "act" => "template_info", "columns" => "*", "n_varify" => TRUE )
    ),
    "search_order_detail" => array(
        "1.0" => array(
            "ctl" => "api/order/api_1_0_order",
            "act" => "search_order_detail",
            "columns" => "*",
            "required" => array( "order_id" )
        )
    ),
    "search_order_list" => array(
        "1.0" => array( "ctl" => "api/order/api_1_0_order", "act" => "search_order_list", "columns" => "*" )
    ),
    "add_delivery_bill" => array(
        "1.0" => array( "ctl" => "api/order/api_b2c_1_0_delivery", "act" => "add_delivery_bill", "columns" => "*" )
    ),
    "add_reship_bill" => array(
        "1.0" => array( "ctl" => "api/order/api_b2c_1_0_delivery", "act" => "add_reship_bill", "columns" => "*" )
    ),
    "search_sync_goods_id" => array(
        "1.0" => array(
            "ctl" => "api/goods/api_b2b_1_0_goods",
            "act" => "search_sync_goods_id",
            "required" => array( "last_modify_st_time", "last_modify_en_time" )
        ),
        "2.0" => array(
            "ctl" => "api/goods/api_b2b_2_0_goods",
            "act" => "search_sync_goods_id",
            "required" => array( "start_version_id", "last_version_id" )
        )
    ),
    "search_sync_goods_detail" => array(
        "1.0" => array(
            "ctl" => "api/goods/api_b2b_1_0_goods",
            "act" => "search_sync_goods_detail",
            "columns" => "*",
            "required" => array( "goods_id" )
        ),
        "2.0" => array(
            "ctl" => "api/goods/api_b2b_2_0_goods",
            "act" => "search_sync_goods_detail",
            "columns" => "*",
            "required" => array( "goods_id" )
        ),
        "3.0" => array(
            "ctl" => "api/goods/api_b2b_3_0_goods",
            "act" => "search_sync_goods_detail",
            "columns" => "*",
            "required" => array( "goods_id" )
        )
    ),
    "reset_goods_sync_status" => array(
        "3.0" => array( "ctl" => "api/goods/api_b2b_3_0_goods", "act" => "reset_goods_sync_status" )
    ),
    "search_del_goods_id" => array(
        "1.0" => array(
            "ctl" => "api/goods/api_b2b_1_0_goods",
            "act" => "search_del_goods_id",
            "required" => array( "last_modify_st_time", "last_modify_en_time" )
        ),
        "2.0" => array(
            "ctl" => "api/goods/api_b2b_2_0_goods",
            "act" => "search_del_goods_id",
            "required" => array( "start_version_id", "last_version_id" )
        )
    ),
    "set_goods_del_succ" => array(
        "1.0" => array(
            "ctl" => "api/goods/api_b2b_1_0_goods",
            "act" => "set_goods_del_succ",
            "required" => array( "last_modify_st_time", "last_modify_en_time" )
        ),
        "2.0" => array(
            "ctl" => "api/goods/api_b2b_2_0_goods",
            "act" => "set_goods_del_succ",
            "required" => array( "start_version_id", "last_version_id" )
        )
    ),
    "search_gimage_info" => array(
        "1.0" => array(
            "ctl" => "api/gimage/api_b2b_1_0_gimage",
            "act" => "search_gimage_info",
            "columns" => "*",
            "required" => array( "gimage_id" )
        )
    ),
    "search_product_line" => array(
        "1.0" => array(
            "ctl" => "api/productline/api_b2b_1_0_productline",
            "act" => "search_product_line",
            "columns" => "*",
            "required" => array( "last_modify_st_time", "last_modify_en_time" )
        ),
        "2.0" => array(
            "ctl" => "api/productline/api_b2b_2_0_productline",
            "act" => "search_product_line",
            "columns" => "*",
            "required" => array( "start_version_id", "last_version_id" )
        )
    ),
    "search_product_line_dealer" => array(
        "1.0" => array( "ctl" => "api/productline/api_b2b_1_0_productline", "act" => "search_product_line_dealer", "columns" => "*" ),
        "2.0" => array( "ctl" => "api/productline/api_b2b_2_0_productline", "act" => "search_product_line_dealer", "columns" => "*" )
    ),
    "search_cat_list" => array(
        "1.0" => array(
            "ctl" => "api/site/api_b2b_1_0_cat",
            "act" => "search_cat_list",
            "columns" => "*",
            "required" => array( "last_modify_st_time", "last_modify_en_time" )
        ),
        "2.0" => array(
            "ctl" => "api/site/api_b2b_2_0_cat",
            "act" => "search_cat_list",
            "columns" => "*",
            "required" => array( "start_version_id", "last_version_id" )
        )
    ),
    "search_brand_list" => array(
        "1.0" => array(
            "ctl" => "api/site/api_b2b_1_0_brand",
            "act" => "search_brand_list",
            "columns" => "*",
            "required" => array( "last_modify_st_time", "last_modify_en_time" )
        ),
        "2.0" => array(
            "ctl" => "api/site/api_b2b_2_0_brand",
            "act" => "search_brand_list",
            "columns" => "*",
            "required" => array( "start_version_id", "last_version_id" )
        )
    ),
    "search_goodstype_list" => array(
        "1.0" => array(
            "ctl" => "api/site/api_b2b_1_0_goodstype",
            "act" => "search_goodstype_list",
            "columns" => "*",
            "required" => array( "last_modify_st_time", "last_modify_en_time" )
        ),
        "2.0" => array(
            "ctl" => "api/site/api_b2b_2_0_goodstype",
            "act" => "search_goodstype_list",
            "columns" => "*",
            "required" => array( "start_version_id", "last_version_id" )
        )
    ),
    "search_goodstype_brand" => array(
        "1.0" => array(
            "ctl" => "api/site/api_b2b_1_0_goodstype",
            "act" => "search_goodstype_brand",
            "columns" => "*",
            "required" => array( "last_modify_st_time", "last_modify_en_time" )
        ),
        "2.0" => array(
            "ctl" => "api/site/api_b2b_2_0_goodstype",
            "act" => "search_goodstype_brand",
            "columns" => "*",
            "required" => array( "start_version_id", "last_version_id" )
        )
    ),
    "search_goodstype_spec" => array(
        "1.0" => array(
            "ctl" => "api/site/api_b2b_1_0_goodstype",
            "act" => "search_goodstype_spec",
            "columns" => "*",
            "required" => array( "last_modify_st_time", "last_modify_en_time" )
        ),
        "2.0" => array(
            "ctl" => "api/site/api_b2b_2_0_goodstype",
            "act" => "search_goodstype_spec",
            "columns" => "*",
            "required" => array( "start_version_id", "last_version_id" )
        )
    ),
    "search_spec_list" => array(
        "1.0" => array(
            "ctl" => "api/site/api_b2b_1_0_spec",
            "act" => "search_spec_list",
            "columns" => array( "spec_id" ),
            "required" => array( "last_modify_st_time", "last_modify_en_time" )
        ),
        "2.0" => array(
            "ctl" => "api/site/api_b2b_2_0_spec",
            "act" => "search_spec_list",
            "columns" => array( "spec_id" ),
            "required" => array( "start_version_id", "last_version_id" )
        )
    ),
    "search_dly_area" => array(
        "1.0" => array( "ctl" => "api/shipping/api_b2b_1_0_area", "act" => "search_dly_area", "columns" => "*", "n_varify" => TRUE )
    ),
    "search_dly_corp" => array(
        "1.0" => array( "ctl" => "api/shipping/api_b2b_1_0_corp", "act" => "search_dly_corp", "columns" => "*", "n_varify" => TRUE )
    ),
    "search_dly_h_area" => array(
        "1.0" => array( "ctl" => "api/shipping/api_b2b_1_0_h_area", "act" => "search_dly_h_area", "columns" => "*", "n_varify" => TRUE )
    ),
    "get_dly_h_area_list" => array(
        "1.0" => array( "ctl" => "api/shipping/api_b2b_1_0_h_area", "act" => "get_dly_h_area_list", "columns" => "*", "n_varify" => TRUE )
    ),
    "search_dly_type" => array(
        "1.0" => array( "ctl" => "api/shipping/api_b2b_1_0_type", "act" => "search_dly_type", "columns" => "*", "n_varify" => TRUE )
    ),
    "get_dly_type_list" => array(
        "1.0" => array( "ctl" => "api/shipping/api_b2b_1_0_type", "act" => "get_dly_type_list", "columns" => "*", "n_varify" => TRUE )
    ),
    "search_payment_cfg_list" => array(
        "1.0" => array( "ctl" => "api/payment/api_b2b_1_0_payment_cfg", "act" => "search_payment_cfg_list", "columns" => "*", "n_varify" => TRUE ),
        "2.0" => array( "ctl" => "api/payment/api_b2b_2_0_payment_cfg", "act" => "search_payment_cfg_list", "columns" => "*", "n_varify" => TRUE )
    ),
    "get_payment_cfg_list" => array(
        "1.0" => array( "ctl" => "api/payment/api_b2b_1_0_payment_cfg", "act" => "get_payment_cfg_list", "columns" => "*" ),
        "2.0" => array( "ctl" => "api/payment/api_b2b_2_0_payment_cfg", "act" => "get_payment_cfg_list", "columns" => "*" )
    ),
    "search_product_by_bn" => array(
        "1.0" => array(
            "ctl" => "api/product/api_b2b_1_0_product",
            "act" => "search_product_by_bn",
            "columns" => "*",
            "required" => array( "dealer_id", "bns" )
        ),
        "2.0" => array(
            "ctl" => "api/product/api_b2b_2_0_product",
            "act" => "search_product_by_bn",
            "columns" => "*",
            "required" => array( "dealer_id", "bns" )
        )
    ),
    "filt_goods" => array(
        "3.0" => array(
            "ctl" => "api/product/api_b2b_3_0_product",
            "act" => "filt_goods",
            "columns" => "*",
            "required" => array( "dealer_id" )
        )
    ),
    "set_dead_order" => array(
        "1.0" => array(
            "ctl" => "api/order/api_b2b_1_0_order",
            "act" => "set_dead_order",
            "required" => array( "order_id" )
        ),
        "2.0" => array(
            "ctl" => "api/order/api_b2b_2_0_order",
            "act" => "set_dead_order",
            "required" => array( "order_id" )
        )
    ),
    "set_cancel_stop_shipping" => array(
        "1.0" => array(
            "ctl" => "api/order/api_b2b_1_0_order",
            "act" => "set_cancel_stop_shipping",
            "required" => array( "order_id" )
        ),
        "2.0" => array(
            "ctl" => "api/order/api_b2b_2_0_order",
            "act" => "set_cancel_stop_shipping",
            "required" => array( "order_id" )
        )
    ),
    "set_stop_shipping" => array(
        "1.0" => array(
            "ctl" => "api/order/api_b2b_1_0_order",
            "act" => "set_stop_shipping",
            "required" => array( "order_id" )
        ),
        "2.0" => array(
            "ctl" => "api/order/api_b2b_2_0_order",
            "act" => "set_stop_shipping",
            "required" => array( "order_id" )
        )
    ),
    "search_payments_by_order" => array(
        "1.0" => array(
            "ctl" => "api/payment/api_b2b_1_0_payment",
            "act" => "search_payments_by_order",
            "columns" => "*",
            "required" => array( "order_id" )
        ),
        "2.0" => array(
            "ctl" => "api/payment/api_b2b_2_0_payment",
            "act" => "search_payments_by_order",
            "columns" => "*",
            "required" => array( "order_id" )
        )
    ),
    "generate_order_record" => array(
        "1.0" => array(
            "ctl" => "api/order/api_b2b_1_0_order",
            "act" => "generate_order_record",
            "columns" => "*",
            "required" => array( "order" )
        ),
        "2.0" => array(
            "ctl" => "api/order/api_b2b_2_0_order",
            "act" => "generate_order_record",
            "columns" => "*",
            "required" => array( "order" )
        ),
        "3.1" => array(
            "ctl" => "api/order/api_b2b_3_1_order",
            "act" => "generate_order_record",
            "columns" => "*",
            "required" => array( "order" )
        )
    ),
    "get_order_setting" => array(
        "3.0" => array( "ctl" => "api/order/api_b2b_3_0_order", "act" => "get_order_setting" )
    ),
    "deduct_dealer_advance" => array(
        "1.0" => array(
            "ctl" => "api/member/api_b2b_1_0_advance",
            "act" => "deduct_dealer_advance",
            "columns" => "*",
            "required" => array( "dealer_id", "order_id", "pay_id" )
        ),
        "2.0" => array(
            "ctl" => "api/member/api_b2b_2_0_advance",
            "act" => "deduct_dealer_advance",
            "columns" => "*",
            "required" => array( "dealer_id", "order_id", "pay_id" )
        )
    ),
    "bind_license" => array(
        "3.0" => array(
            "ctl" => "api/member/api_b2b_3_0_member",
            "act" => "bind_license",
            "required" => array( "user_name", "user_pwd", "license_id" )
        )
    ),
    "change_order_info" => array(
        "1.0" => array(
            "ctl" => "api/order/api_b2b_1_0_order",
            "act" => "change_order_info",
            "columns" => "*",
            "required" => array( "order" )
        ),
        "2.0" => array(
            "ctl" => "api/order/api_b2b_2_0_order",
            "act" => "change_order_info",
            "columns" => "*",
            "required" => array( "order" )
        )
    ),
    "search_cur_list" => array(
        "1.0" => array( "ctl" => "api/site/api_b2b_1_0_cur", "act" => "search_cur_list", "n_varify" => TRUE )
    ),
    "online_pay_center" => array(
        "1.0" => array(
            "ctl" => "api/payment/api_b2b_1_0_payment",
            "act" => "online_pay_center",
            "required" => array( "order_id", "pay_id", "currency" ),
            "n_varify" => TRUE
        ),
        "2.0" => array(
            "ctl" => "api/payment/api_b2b_2_0_payment",
            "act" => "online_pay_center",
            "required" => array( "order_id", "pay_id", "currency" ),
            "n_varify" => TRUE
        )
    ),
    "search_sub_regions" => array(
        "1.0" => array(
            "ctl" => "api/shipping/api_b2b_1_0_region",
            "act" => "search_sub_regions",
            "required" => array( "p_region_id" ),
            "n_varify" => TRUE
        ),
        "2.0" => array(
            "ctl" => "api/shipping/api_b2b_2_0_region",
            "act" => "search_sub_regions",
            "required" => array( "p_region_id" ),
            "n_varify" => TRUE
        )
    ),
    "search_dltype_byarea" => array(
        "1.0" => array(
            "ctl" => "api/shipping/api_b2b_1_0_region",
            "act" => "search_dltype_byarea",
            "required" => array( "area_id" ),
            "n_varify" => TRUE
        ),
        "2.0" => array(
            "ctl" => "api/shipping/api_b2b_2_0_region",
            "act" => "search_dltype_byarea",
            "required" => array( "area_id" ),
            "n_varify" => TRUE
        )
    ),
    "search_dly_type_byid" => array(
        "1.0" => array(
            "ctl" => "api/shipping/api_b2b_1_0_region",
            "act" => "search_dly_type_byid",
            "columns" => "*",
            "required" => array( "area_id", "delivery_id" ),
            "n_varify" => TRUE
        ),
        "2.0" => array(
            "ctl" => "api/shipping/api_b2b_2_0_region",
            "act" => "search_dly_type_byid",
            "columns" => "*",
            "required" => array( "area_id", "delivery_id" ),
            "n_varify" => TRUE
        )
    ),
    "refund" => array(
        "1.0" => array( "ctl" => "api/order/api_b2b_1_0_refund", "act" => "" ),
        "2.0" => array( "ctl" => "api/order/api_b2b_2_0_refund", "act" => "" )
    ),
    "verify_member_valid" => array(
        "1.0" => array( "ctl" => "api/member/api_b2b_1_0_member", "act" => "" ),
        "2.0" => array( "ctl" => "api/member/api_b2b_2_0_member", "act" => "" )
    ),
    "check_member" => array(
        "3.0" => array( "ctl" => "api/member/api_b2b_3_0_member", "check_member" )
    ),
    "getDlTypeByArea" => array(
        "1.0" => array( "ctl" => "api/order/api_b2b_1_0_delivery", "act" => "" )
    ),
    "get_http" => array(
        "1.0" => array( "ctl" => "api/tools/api_b2b_1_0_tools", "act" => "" )
    ),
    "set_product_store" => array(
        "1.0" => array( "ctl" => "api/product/api_1_0_product", "act" => "set_product_store" )
    ),
    "set_products_store" => array(
        "1.0" => array( "ctl" => "api/product/api_1_0_product", "act" => "set_products_store" )
    ),
    "search_member_list" => array(
        "3.0" => array( "ctl" => "api/member/api_b2b_3_0_member", "act" => "search_member_list" )
    ),
    "match_goods" => array(
        "3.0" => array(
            "ctl" => "api/product/api_b2b_3_0_product",
            "act" => "match_goods",
            "required" => array( "dealer_id", "bn", "name", "specvalue" )
        )
    ),
    "set_product_freeze_store" => array(
        "1.0" => array( "ctl" => "api/product/api_1_0_product", "act" => "set_product_freeze_store" )
    ),
    "set_order_status" => array(
        "1.0" => array( "ctl" => "api/order/api_1_0_order", "act" => "set_order_status" )
    ),
    "set_order_status_center" => array(
        "1.0" => array( "ctl" => "api/order/api_1_0_order", "act" => "set_order_status_center" )
    ),
    "create_payments" => array(
        "1.0" => array( "ctl" => "api/payment/api_b2b_1_0_payment", "act" => "insert_payments" )
    ),
    "create_refunds" => array(
        "1.0" => array( "ctl" => "api/refund/api_1_0_refund", "act" => "insert_refunds" )
    ),
    "create_delivery" => array(
        "1.0" => array( "ctl" => "api/order/api_b2c_1_0_delivery", "act" => "insert_delivery" )
    ),
    "search_aftermarket_list" => array(
        "1.0" => array( "ctl" => "api/order/api_1_0_order", "act" => "search_aftermarket_list" )
    ),
    "set_aftermarket_status" => array(
        "1.0" => array( "ctl" => "api/order/api_1_0_order", "act" => "set_aftermarket_status" )
    ),
    "set_aftermarket_status_c" => array(
        "1.0" => array( "ctl" => "api/order/api_1_0_order", "act" => "set_aftermarket_status_c" )
    ),
    "get_currency_list" => array(
        "1.0" => array( "ctl" => "api/site/api_b2b_1_0_cur", "act" => "get_currency_list" )
    ),
    "get_order_log" => array(
        "1.0" => array( "ctl" => "api/order/api_1_0_order", "act" => "get_order_log" )
    ),
    "get_order_message" => array(
        "1.0" => array( "ctl" => "api/order/api_1_0_order", "act" => "get_order_message" )
    ),
    "get_order_promotion" => array(
        "1.0" => array( "ctl" => "api/order/api_1_0_order", "act" => "get_order_promotion" )
    ),
    "get_product_detail" => array(
        "1.0" => array( "ctl" => "api/product/api_1_0_product", "act" => "get_product_detail", "columns" => "*" )
    ),
    "notify_delivery" => array(
        "1.0" => array( "ctl" => "api/order/api_b2c_1_0_delivery", "act" => "notify_delivery", "columns" => "*" ),
        "2.0" => array( "ctl" => "api/order/api_b2c_2_0_delivery", "act" => "notify_delivery", "columns" => "*" )
    ),
    "set_order_message" => array(
        "1.0" => array( "ctl" => "api/order/api_1_0_order", "act" => "set_order_message" )
    ),
    "create_sell_log" => array(
        "1.0" => array( "ctl" => "api/goods/api_1_0_goods", "act" => "create_sell_log" )
    ),
    "goods_wltx_exp_list" => array(
        "1.0" => array( "ctl" => "api/goods/api_1_0_goods", "act" => "goods_wltx_exp_list" )
    ),
    "update_goods_store" => array(
        "1.0" => array( "ctl" => "api/goods/api_1_0_goods", "act" => "update_goods_store" )
    ),
    "get_payment_info" => array(
        "1.0" => array(
            "ctl" => "api/payment/api_b2b_1_0_payment",
            "act" => "get_payment_info",
            "columns" => "*",
            "required" => array( "payment_id" )
        )
    ),
    "sync_mark" => array(
        "2.0" => array( "ctl" => "api/site/api_b2b_2_0_sync", "act" => "sync_mark" )
    ),
    "get_spec_info" => array(
        "1.0" => array(
            "ctl" => "api/goods/api_1_0_goods",
            "act" => "get_spec_info",
            "required" => array( "spec_id" )
        )
    ),
    "get_spec_list" => array(
        "1.0" => array( "ctl" => "api/goods/api_1_0_goods", "act" => "get_spec_list" )
    ),
    "update_goods_image" => array(
        "1.0" => array(
            "ctl" => "api/goods/api_1_0_goods",
            "act" => "update_goods_image",
            "required" => array( "goods_id" )
        )
    ),
    "get_shop_goods_type" => array(
        "1.0" => array( "ctl" => "api/goods/api_1_0_goods", "act" => "get_shop_goods_type" )
    ),
    "get_goods_props" => array(
        "1.0" => array(
            "ctl" => "api/goods/api_1_0_goods",
            "act" => "get_goods_props",
            "required" => array( "type_id" )
        )
    ),
    "delete_goods_image" => array(
        "1.0" => array(
            "ctl" => "api/goods/api_1_0_goods",
            "act" => "delete_goods_image",
            "required" => array( "goods_id" )
        )
    ),
    "delete_goods_images" => array(
        "1.0" => array(
            "ctl" => "api/goods/api_1_0_goods",
            "act" => "delete_goods_images",
            "required" => array( "image_id" )
        )
    ),
    "update_goods_info" => array(
        "1.0" => array(
            "ctl" => "api/goods/api_1_0_goods",
            "act" => "update_goods_info",
            "required" => array( "goods_id" )
        )
    ),
    "add_products_info" => array(
        "1.0" => array(
            "ctl" => "api/goods/api_1_0_goods",
            "act" => "add_products_info",
            "required" => array( "goods_id" )
        )
    ),
    "delete_products" => array(
        "1.0" => array(
            "ctl" => "api/goods/api_1_0_goods",
            "act" => "delete_products",
            "required" => array( "goods_id", "products_id" )
        )
    ),
    "update_products_info" => array(
        "1.0" => array(
            "ctl" => "api/goods/api_1_0_goods",
            "act" => "update_products_info",
            "required" => array( "goods_id", "products_id" )
        )
    ),
    "create_order" => array(
        "1.0" => array( "ctl" => "api/order/api_1_0_order", "act" => "create_order" )
    ),
    "update_order_remark" => array(
        "1.0" => array(
            "ctl" => "api/order/api_1_0_order",
            "act" => "update_order_remark",
            "required" => array( "order_id", "mark_text" )
        )
    ),
    "update_order" => array(
        "1.0" => array(
            "ctl" => "api/order/api_1_0_order",
            "act" => "update_order",
            "required" => array( "order_id" )
        )
    ),
    "get_payments" => array(
        "1.0" => array(
            "ctl" => "api/order/api_1_0_order",
            "act" => "get_payments",
            "required" => array( "order_id" )
        )
    ),
    "get_refunds" => array(
        "1.0" => array(
            "ctl" => "api/order/api_1_0_order",
            "act" => "get_refunds",
            "required" => array( "order_id" )
        )
    ),
    "get_shippings" => array(
        "1.0" => array(
            "ctl" => "api/order/api_1_0_order",
            "act" => "get_shippings",
            "required" => array( "order_id" )
        )
    ),
    "get_returns" => array(
        "1.0" => array(
            "ctl" => "api/order/api_1_0_order",
            "act" => "get_returns",
            "required" => array( "order_id" )
        )
    ),
    "get_type_spec" => array(
        "1.0" => array(
            "ctl" => "api/goods/api_1_0_goods",
            "act" => "get_type_spec",
            "required" => array( "type_id" )
        )
    ),
    "del_images_byid" => array(
        "1.0" => array(
            "ctl" => "api/goods/api_1_0_goods",
            "act" => "del_images_byid",
            "required" => array( "goods_id" )
        )
    )
);
return $method;
?>
