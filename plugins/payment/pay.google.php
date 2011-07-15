<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class XmlBuilder
{

    public $xml = NULL;
    public $indent = NULL;
    public $stack = array( );

    public function XmlBuilder( $indent = "  " )
    {
        $this->indent = $indent;
        $this->xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
    }

    public function _indent( )
    {
        $i = 0;
        $j = count( $this->stack );
        for ( ; $i < $j; ++$i )
        {
            $this->xml .= $this->indent;
        }
    }

    public function Push( $element, $attributes = array( ) )
    {
        $this->_indent( );
        $this->xml .= "<".$element;
        foreach ( $attributes as $key => $value )
        {
            $this->xml .= " ".$key."=\"".htmlentities( $value )."\"";
        }
        $this->xml .= ">\n";
        $this->stack[] = $element;
    }

    public function Element( $element, $content, $attributes = array( ) )
    {
        $this->_indent( );
        $this->xml .= "<".$element;
        foreach ( $attributes as $key => $value )
        {
            $this->xml .= " ".$key."=\"".htmlentities( $value )."\"";
        }
        $this->xml .= ">".htmlentities( $content )."</".$element.">"."\n";
    }

    public function EmptyElement( $element, $attributes = array( ) )
    {
        $this->_indent( );
        $this->xml .= "<".$element;
        foreach ( $attributes as $key => $value )
        {
            $this->xml .= " ".$key."=\"".htmlentities( $value )."\"";
        }
        $this->xml .= " />\n";
    }

    public function Pop( $pop_element )
    {
        $element = array_pop( $this->stack );
        $this->_indent( );
        if ( $element !== $pop_element )
        {
            exit( "XML Error: Tag Mismatch when trying to close \"".$pop_element."\"" );
        }
        else
        {
            $this->xml .= "</{$element}>\n";
        }
    }

    public function GetXML( )
    {
        if ( count( $this->stack ) != 0 )
        {
            exit( "XML Error: No matching closing tag found for \" ".array_pop( $this->stack )."\"" );
        }
        else
        {
            return $this->xml;
        }
    }

}

class XmlParser
{

    public $params = array( );
    public $root = NULL;
    public $global_index = -1;

    public function XmlParser( $input )
    {
        $xmlp = xml_parser_create( );
        xml_parse_into_struct( $xmlp, $input, $vals, $index );
        xml_parser_free( $xmlp );
        $this->root = strtolower( $vals[0]['tag'] );
        $this->params = $this->UpdateRecursive( $vals );
    }

    public function is_associative_array( $var )
    {
        return is_array( $var ) && !is_numeric( implode( "", array_keys( $var ) ) );
    }

    public function UpdateRecursive( $vals )
    {
        $this->global_index++;
        if ( count( $vals ) <= $this->global_index )
        {
            return;
        }
        $tag = strtolower( $vals[$this->global_index]['tag'] );
        $value = trim( $vals[$this->global_index]['value'] );
        $type = $vals[$this->global_index]['type'];
        if ( isset( $vals[$this->global_index]['attributes'] ) )
        {
            foreach ( $vals[$this->global_index]['attributes'] as $key => $val )
            {
                $key = strtolower( $key );
                $params[$tag][$key] = $val;
            }
        }
        if ( $type == "open" )
        {
            $new_arr = array( );
            while ( $vals[$this->global_index]['type'] != "close" && $this->global_index < count( $vals ) )
            {
                $arr = $this->UpdateRecursive( $vals );
                if ( 0 < count( $arr ) )
                {
                    $new_arr[] = $arr;
                }
            }
            $this->global_index++;
            foreach ( $new_arr as $arr )
            {
                foreach ( $arr as $key => $val )
                {
                    if ( isset( $params[$tag][$key] ) )
                    {
                        if ( $this->is_associative_array( $params[$tag][$key] ) )
                        {
                            $val_key = $params[$tag][$key];
                            array_splice( $params[$tag][$key], 0 );
                            $params[$tag][$key][0] = $val_key;
                            $params[$tag][$key][] = $val;
                        }
                        else
                        {
                            $params[$tag][$key][] = $val;
                        }
                    }
                    else
                    {
                        $params[$tag][$key] = $val;
                    }
                }
            }
        }
        else if ( $type == "complete" )
        {
            if ( $value != "" )
            {
                $params[$tag]['VALUE'] = $value;
            }
        }
        else
        {
            $params = array( );
        }
        return $params;
    }

    public function GetRoot( )
    {
        return $this->root;
    }

    public function GetData( )
    {
        return $this->params;
    }

}

class GoogleCart
{

    public $merchant_id = NULL;
    public $merchant_key = NULL;
    public $server_url = NULL;
    public $schema_url = NULL;
    public $base_url = NULL;
    public $checkout_url = NULL;
    public $checkout_diagnose_url = NULL;
    public $request_url = NULL;
    public $request_diagnose_url = NULL;
    public $cart_expiration = "";
    public $merchant_private_data = "";
    public $edit_cart_url = "";
    public $continue_shopping_url = "";
    public $request_buyer_phone = "";
    public $merchant_calculated = "";
    public $merchant_calculations_url = "";
    public $accept_merchant_coupons = "";
    public $accept_gift_certificates = "";
    public $default_tax_table = NULL;
    public $item_arr = NULL;
    public $shipping_arr = NULL;
    public $alternate_tax_table_arr = NULL;
    public $xml_data = NULL;

    public function GoogleCart( $id, $key, $server_type = "checkout" )
    {
        $this->merchant_id = $id;
        $this->merchant_key = $key;
        if ( strtolower( $server_type ) == "sandbox" )
        {
            $this->server_url = "https://sandbox.google.com/";
        }
        else
        {
            $this->server_url = "https://checkout.google.com/";
        }
        $this->schema_url = "http://checkout.google.com/schema/2";
        $this->base_url = $this->server_url."cws/v2/Merchant/".$this->merchant_id;
        $this->checkout_url = $this->base_url."/checkout";
        $this->checkout_diagnose_url = $this->base_url."/checkout/diagnose";
        $this->request_url = $this->base_url."/request";
        $this->request_diagnose_url = $this->base_url."/request/diagnose";
        $this->item_arr = array( );
        $this->shipping_arr = array( );
        $this->alternate_tax_table_arr = array( );
    }

    public function SetCartExpiration( $cart_expire )
    {
        $this->cart_expiration = $cart_expire;
    }

    public function SetMerchantPrivateData( $data )
    {
        $this->merchant_private_data = $data;
    }

    public function SetEditCartUrl( $url )
    {
        $this->edit_cart_url = $url;
    }

    public function SetContinueShoppingUrl( $url )
    {
        $this->continue_shopping_url = $url;
    }

    public function SetRequestBuyerPhone( $req )
    {
        $this->_SetBooleanValue( "request_buyer_phone", $req, "" );
    }

    public function SetMerchantCalculations( $url, $tax_option = "false", $coupons = "false", $gift_cert = "false" )
    {
        $this->merchant_calculations_url = $url;
        $this->_SetBooleanValue( "merchant_calculated", $tax_option, "false" );
        $this->_SetBooleanValue( "accept_merchant_coupons", $coupons, "false" );
        $this->_SetBooleanValue( "accept_gift_certificates", $gift_cert, "false" );
    }

    public function AddItem( $google_item )
    {
        $this->item_arr[] = $google_item;
    }

    public function AddShipping( $ship )
    {
        $this->shipping_arr[] = $ship;
    }

    public function AddTaxTables( $tax )
    {
        if ( $tax->type == "default" )
        {
            $this->default_tax_table = $tax;
        }
        else if ( $tax->type == "alternate" )
        {
            $this->alternate_tax_table_arr[] = $tax;
        }
    }

    public function GetXML( )
    {
        ( );
        $xml_data = new XmlBuilder( );
        $xml_data->Push( "checkout-shopping-cart", array(
            "xmlns" => $this->schema_url
        ) );
        $xml_data->Push( "shopping-cart" );
        if ( $this->cart_expiration != "" )
        {
            $xml_data->Push( "cart-expiration" );
            $xml_data->Element( "good-until-date", $this->cart_expiration );
            $xml_data->Pop( "cart-expiration" );
        }
        $xml_data->Push( "items" );
        foreach ( $this->item_arr as $item )
        {
            $xml_data->Push( "item" );
            $xml_data->Element( "item-name", $item->item_name );
            $xml_data->Element( "item-description", $item->item_description );
            $xml_data->Element( "unit-price", $item->unit_price, array(
                "currency" => $item->currency
            ) );
            $xml_data->Element( "quantity", $item->quantity );
            if ( $item->merchant_private_data != "" )
            {
                $xml_data->Element( "merchant-private-date", $item->merchant_private_data );
            }
            if ( $item->tax_table_selector != "" )
            {
                $xml_data->Element( "tax-table-selector", $item->tax_table_selector );
            }
            $xml_data->Pop( "item" );
        }
        $xml_data->Pop( "items" );
        if ( $this->merchant_private_data != "" )
        {
            $xml_data->Element( "merchant-private-data", $this->merchant_private_data );
        }
        $xml_data->Pop( "shopping-cart" );
        $xml_data->Push( "checkout-flow-support" );
        $xml_data->Push( "merchant-checkout-flow-support" );
        if ( $this->edit_cart_url != "" )
        {
            $xml_data->Element( "edit-cart-url", $this->edit_cart_url );
        }
        if ( $this->continue_shopping_url != "" )
        {
            $xml_data->Element( "continue-shopping-url", $this->continue_shopping_url );
        }
        if ( 0 < count( $this->shipping_arr ) )
        {
            $xml_data->Push( "shipping-methods" );
        }
        foreach ( $this->shipping_arr as $ship )
        {
            if ( $ship->type == "flat-rate" || $ship->type == "merchant-calculated" )
            {
                $xml_data->Push( $ship->type."-shipping", array(
                    "name" => $ship->name
                ) );
                $xml_data->Element( "price", $ship->price, array(
                    "currency" => $ship->currency
                ) );
                if ( $ship->allowed_restrictions || $ship->excluded_restrictions )
                {
                    $xml_data->Push( "shipping-restrictions" );
                    if ( $ship->allowed_restrictions )
                    {
                        $xml_data->Push( "allowed-areas" );
                        if ( $ship->allowed_country_area != "" )
                        {
                            $xml_data->Element( "us-country-area", "", array(
                                "country-area" => $ship->allowed_country_area
                            ) );
                        }
                        foreach ( $ship->allowed_state_areas_arr as $current )
                        {
                            $xml_data->Push( "us-state-area" );
                            $xml_data->Element( "state", $current );
                            $xml_data->Pop( "us-state-area" );
                        }
                        foreach ( $ship->allowed_zip_patterns_arr as $current )
                        {
                            $xml_data->Push( "us-zip-area" );
                            $xml_data->Element( "zip-pattern", $current );
                            $xml_data->Pop( "us-zip-area" );
                        }
                        $xml_data->Pop( "allowed-areas" );
                    }
                    if ( $ship->excluded_restrictions )
                    {
                        $xml_data->Push( "allowed-areas" );
                        $xml_data->Pop( "allowed-areas" );
                        $xml_data->Push( "excluded-areas" );
                        if ( $ship->excluded_country_area != "" )
                        {
                            $xml_data->Element( "us-country-area", "", array(
                                "country-area" => $ship->excluded_country_area
                            ) );
                        }
                        foreach ( $ship->excluded_state_areas_arr as $current )
                        {
                            $xml_data->Push( "us-state-area" );
                            $xml_data->Element( "state", $current );
                            $xml_data->Pop( "us-state-area" );
                        }
                        foreach ( $ship->excluded_zip_patterns_arr as $current )
                        {
                            $xml_data->Push( "us-zip-area" );
                            $xml_data->Element( "zip-pattern", $current );
                            $xml_data->Pop( "us-zip-area" );
                        }
                        $xml_data->Pop( "excluded-areas" );
                    }
                    $xml_data->Pop( "shipping-restrictions" );
                }
                $xml_data->Pop( $ship->type."-shipping" );
            }
            else if ( $ship->type == "pickup" )
            {
                $xml_data->Push( "pickup", array(
                    "name" => $ship->name
                ) );
                $xml_data->Element( "price", $ship->price, array(
                    "currency" => $ship->currency
                ) );
                $xml_data->Pop( "pickup" );
            }
        }
        if ( 0 < count( $this->shipping_arr ) )
        {
            $xml_data->Pop( "shipping-methods" );
        }
        if ( $this->request_buyer_phone != "" )
        {
            $xml_data->Element( "request-buyer-phone-number", $this->request_buyer_phone );
        }
        if ( $this->merchant_calculations_url != "" )
        {
            $xml_data->Push( "merchant-calculations" );
            $xml_data->Element( "merchant-calculations-url", $this->merchant_calculations_url );
            if ( $this->accept_merchant_coupons != "" )
            {
                $xml_data->Element( "accept-merchant-coupons", $this->accept_merchant_coupons );
            }
            if ( $this->accept_gift_certificates != "" )
            {
                $xml_data->Element( "accept-gift-certificates", $this->accept_gift_certificates );
            }
            $xml_data->Pop( "merchant-calculations" );
        }
        if ( count( $this->alternate_tax_table_arr ) != 0 || isset( $this->default_tax_table ) )
        {
            if ( $this->merchant_calculated != "" )
            {
                $xml_data->Push( "tax-tables", array(
                    "merchant-calculated" => $this->merchant_calculated
                ) );
            }
            else
            {
                $xml_data->Push( "tax-tables" );
            }
            if ( isset( $this->default_tax_table ) )
            {
                $curr_table = $this->default_tax_table;
                foreach ( $curr_table->tax_rules_arr as $curr_rule )
                {
                    $xml_data->Push( "default-tax-table" );
                    $xml_data->Push( "tax-rules" );
                    foreach ( $curr_rule->state_areas_arr as $current )
                    {
                        $xml_data->Push( "default-tax-rule" );
                        $xml_data->Element( "shipping-taxed", $curr_rule->shipping_taxed );
                        $xml_data->Element( "rate", $curr_rule->tax_rate );
                        $xml_data->Push( "tax-area" );
                        if ( $curr_rule->country_area != "" )
                        {
                            $xml_data->Element( "us-country-area", "", array(
                                "country-area" => $curr_rule->country_area
                            ) );
                        }
                        $xml_data->Push( "us-state-area" );
                        $xml_data->Element( "state", $current );
                        $xml_data->Pop( "us-state-area" );
                        $xml_data->Pop( "tax-area" );
                        $xml_data->Pop( "default-tax-rule" );
                    }
                    foreach ( $curr_rule->zip_patterns_arr as $current )
                    {
                        $xml_data->Push( "default-tax-rule" );
                        $xml_data->Element( "shipping-taxed", $curr_rule->shipping_taxed );
                        $xml_data->Element( "rate", $curr_rule->tax_rate );
                        $xml_data->Push( "tax-area" );
                        if ( $curr_rule->country_area != "" )
                        {
                            $xml_data->Element( "us-country-area", "", array(
                                "country-area" => $curr_rule->country_area
                            ) );
                        }
                        $xml_data->Push( "us-zip-area" );
                        $xml_data->Element( "zip-pattern", $current );
                        $xml_data->Pop( "us-zip-area" );
                        $xml_data->Pop( "tax-area" );
                        $xml_data->Pop( "default-tax-rule" );
                    }
                    $xml_data->Pop( "tax-rules" );
                    $xml_data->Pop( "default-tax-table" );
                }
            }
            if ( count( $this->alternate_tax_table_arr ) != 0 )
            {
                $xml_data->Push( "alternate-tax-tables" );
                foreach ( $this->alternate_tax_table_arr as $curr_table )
                {
                    foreach ( $curr_table->tax_rules_arr as $curr_rule )
                    {
                        $xml_data->Push( "alternate-tax-table", array(
                            "standalone" => $curr_table->standalone,
                            "name" => $curr_table->name
                        ) );
                        $xml_data->Push( "alternate-tax-rules" );
                        foreach ( $curr_rule->state_areas_arr as $current )
                        {
                            $xml_data->Push( "alternate-tax-rule" );
                            $xml_data->Element( "shipping-taxed", $curr_rule->shipping_taxed );
                            $xml_data->Element( "rate", $curr_rule->tax_rate );
                            $xml_data->Push( "tax-area" );
                            if ( $curr_rule->country_area != "" )
                            {
                                $xml_data->Element( "us-country-area", "", array(
                                    "country-area" => $curr_rule->country_area
                                ) );
                            }
                            $xml_data->Push( "us-state-area" );
                            $xml_data->Element( "state", $current );
                            $xml_data->Pop( "us-state-area" );
                            $xml_data->Pop( "tax-area" );
                            $xml_data->Pop( "alternate-tax-rule" );
                        }
                        foreach ( $curr_rule->zip_patterns_arr as $current )
                        {
                            $xml_data->Push( "alternate-tax-rule" );
                            $xml_data->Element( "shipping-taxed", $curr_rule->shipping_taxed );
                            $xml_data->Element( "rate", $curr_rule->tax_rate );
                            $xml_data->Push( "tax-area" );
                            if ( $curr_rule->country_area != "" )
                            {
                                $xml_data->Element( "us-country-area", "", array(
                                    "country-area" => $curr_rule->country_area
                                ) );
                            }
                            $xml_data->Push( "us-zip-area" );
                            $xml_data->Element( "zip-pattern", $current );
                            $xml_data->Pop( "us-zip-area" );
                            $xml_data->Pop( "tax-area" );
                            $xml_data->Pop( "alternate-tax-rule" );
                        }
                        $xml_data->Pop( "alternate-tax-rules" );
                        $xml_data->Pop( "alternate-tax-table" );
                    }
                }
                $xml_data->Pop( "alternate-tax-tables" );
            }
            $xml_data->Pop( "tax-tables" );
        }
        $xml_data->Pop( "merchant-checkout-flow-support" );
        $xml_data->Pop( "checkout-flow-support" );
        $xml_data->Pop( "checkout-shopping-cart" );
        return $xml_data->GetXML( );
    }

    public function getArr( )
    {
        return array(
            "cart" => base64_encode( $this->GetXML( ) ),
            "signature" => base64_encode( $this->CalcHmacSha1( $this->GetXML( ) ) )
        );
    }

    public function CheckoutButtonCode( $size = "large", $style = "white", $variant = "text", $loc = "en_US" )
    {
        switch ( $size )
        {
        case "large" :
            $width = "180";
            $height = "46";
            break;
        case "medium" :
            $width = "168";
            $height = "44";
            break;
        case "small" :
            $width = "160";
            $height = "43";
            break;
        default :
            break;
        }
        if ( $variant == "text" )
        {
            $data = "<p><input type=\"hidden\" name=\"cart\" value=\"".base64_encode( $this->GetXML( ) )."\">\n           <input type=\"hidden\" name=\"signature\" value=\"".base64_encode( $this->CalcHmacSha1( $this->GetXML( ) ) )."\"></p>";
        }
        else if ( $variant == "disabled" )
        {
            $data = "<p><img alt=\"Checkout\"\n              src=\"".$this->server_url."buttons/checkout.gif?merchant_id=".$this->merchant_id."&w=".$width."&h=".$height."&style=".$style."&variant=".$variant."&loc=".$loc."\"\n              height=\"".$height."\" width=\"".$width."\" /></p>";
        }
        return $data;
    }

    public function CalcHmacSha1( $data )
    {
        $key = $this->merchant_key;
        $blocksize = 64;
        $hashfunc = "sha1";
        if ( $blocksize < strlen( $key ) )
        {
            $key = pack( "H*", $hashfunc( $key ) );
        }
        $key = str_pad( $key, $blocksize, chr( 0 ) );
        $ipad = str_repeat( chr( 54 ), $blocksize );
        $opad = str_repeat( chr( 92 ), $blocksize );
        $hmac = pack( "H*", $hashfunc( ( $key ^ $opad ).pack( "H*", $hashfunc( ( $key ^ $ipad ).$data ) ) ) );
        return $hmac;
    }

    public function _SetBooleanValue( $string, $value, $default )
    {
        $value = strtolower( $value );
        if ( $value == "true" || $value == "false" )
        {
            eval( "\$this->".$string."=\"".$value."\";" );
        }
        else
        {
            eval( "\$this->".$string."=\"".$default."\";" );
        }
    }

}

class GoogleItem
{

    public $item_name = NULL;
    public $item_description = NULL;
    public $unit_price = NULL;
    public $currency = NULL;
    public $quantity = NULL;
    public $merchant_private_data = NULL;
    public $tax_table_selector = NULL;

    public function GoogleItem( $name, $desc, $qty, $price, $money = "USD", $private_data = "", $tax_selector = "" )
    {
        $this->item_name = $name;
        $this->item_description = $desc;
        $this->unit_price = $price;
        $this->quantity = $qty;
        $this->currency = $money;
        $this->merchant_private_data = $private_data;
        $this->tax_table_selector = $tax_selector;
    }

    public function SetMerchantPrivateData( $private_data )
    {
        $this->merchant_private_data = $private_data;
    }

    public function SetTaxTableSelector( $tax_selector )
    {
        $this->tax_table_selector = $tax_selector;
    }

}

class GoogleMerchantCalculations
{

    public $results_arr = NULL;
    public $schema_url = "http://checkout.google.com/schema/2";

    public function GoogleMerchantCalculations( )
    {
        $this->results_arr = array( );
    }

    public function AddResult( $results )
    {
        $this->results_arr[] = $results;
    }

    public function GetXML( )
    {
        ( );
        $xml_data = new XmlBuilder( );
        $xml_data->Push( "merchant-calculation-results", array(
            "xmlns" => $this->schema_url
        ) );
        $xml_data->Push( "results" );
        foreach ( $this->results_arr as $result )
        {
            if ( $result->shipping_name != "" )
            {
                $xml_data->Push( "result", array(
                    "shipping-name" => $result->shipping_name,
                    "address-id" => $result->address_id
                ) );
                $xml_data->Element( "shipping-rate", $result->ship_price, array(
                    "currency" => $result->ship_currency
                ) );
                $xml_data->Element( "shippable", $result->shippable );
            }
            else
            {
                $xml_data->Push( "result", array(
                    "address-id" => $result->address_id
                ) );
            }
            if ( $result->tax_amount != "" )
            {
                $xml_data->Element( "total-tax", $result->tax_amount, array(
                    "currency" => $result->tax_currency
                ) );
            }
            if ( count( $result->coupon_arr ) != 0 || count( $result->giftcert_arr ) != 0 )
            {
                $xml_data->Push( "merchant-code-results" );
                foreach ( $result->coupon_arr as $curr_coupon )
                {
                    $xml_data->Push( "coupon-result" );
                    $xml_data->Element( "valid", $curr_coupon->coupon_valid );
                    $xml_data->Element( "code", $curr_coupon->coupon_code );
                    $xml_data->Element( "calculated-amount", $curr_coupon->coupon_amount, array(
                        "currency" => $curr_coupon->coupon_currency
                    ) );
                    $xml_data->Element( "message", $curr_coupon->coupon_message );
                    $xml_data->Pop( "coupon-result" );
                }
                foreach ( $result->giftcert_arr as $curr_gift )
                {
                    $xml_data->Push( "gift-result" );
                    $xml_data->Element( "valid", $curr_gift->gift_valid );
                    $xml_data->Element( "code", $curr_gift->gift_code );
                    $xml_data->Element( "calculated-amount", $curr_gift->gift_amount, array(
                        "currency" => $curr_gift->gift_currency
                    ) );
                    $xml_data->Element( "message", $curr_gift->gift_message );
                    $xml_data->Pop( "gift-result" );
                }
                $xml_data->Pop( "merchant-code-results" );
            }
            $xml_data->Pop( "result" );
        }
        $xml_data->Pop( "results" );
        $xml_data->Pop( "merchant-calculation-results" );
        return $xml_data->GetXML( );
    }

}

class GoogleResponse
{

    public $merchant_id = NULL;
    public $merchant_key = NULL;
    public $server_url = NULL;
    public $schema_url = NULL;
    public $base_url = NULL;
    public $checkout_url = NULL;
    public $checkout_diagnose_url = NULL;
    public $request_url = NULL;
    public $request_diagnose_url = NULL;
    public $response = NULL;
    public $root = NULL;
    public $data = NULL;
    public $xml_parser = NULL;

    public function GoogleResponse( $id, $key, $response, $server_type = "checkout" )
    {
        $this->merchant_id = $id;
        $this->merchant_key = $key;
        if ( $server_type == "sandbox" )
        {
            $this->server_url = "https://sandbox.google.com/";
        }
        else
        {
            $this->server_url = "https://checkout.google.com/";
        }
        $this->schema_url = "http://checkout.google.com/schema/2";
        $this->base_url = $this->server_url."cws/v2/Merchant/".$this->merchant_id;
        $this->checkout_url = $this->base_url."/checkout";
        $this->checkout_diagnose_url = $this->base_url."/checkout/diagnose";
        $this->request_url = $this->base_url."/request";
        $this->request_diagnose_url = $this->base_url."/request/diagnose";
        $this->response = $response;
        if ( strpos( __FILE__, ":" ) !== FALSE )
        {
            $path_delimiter = ";";
        }
        else
        {
            $path_delimiter = ":";
        }
        ini_set( "include_path", ini_get( "include_path" ).$path_delimiter."." );
        ( $response );
        $this->xml_parser = new XmlParser( );
        $this->root = $this->xml_parser->GetRoot( );
        $this->data = $this->xml_parser->GetData( );
    }

    public function HttpAuthentication( $headers )
    {
        if ( isset( $headers['Authorization'] ) )
        {
            $auth_encode = $headers['Authorization'];
            $auth = base64_decode( substr( $auth_encode, strpos( $auth_encode, " " ) + 1 ) );
            $compare_mer_id = substr( $auth, 0, strpos( $auth, ":" ) );
            $compare_mer_key = substr( $auth, strpos( $auth, ":" ) + 1 );
        }
        else
        {
            return FALSE;
        }
        if ( $compare_mer_id != $this->merchant_id || $compare_mer_key != $this->merchant_key )
        {
            return FALSE;
        }
        return TRUE;
    }

    public function SendChargeOrder( $google_order, $amount = "", $message_log )
    {
        $postargs = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n                   <charge-order xmlns=\"".$this->schema_url."\" google-order-number=\"".$google_order."\">";
        if ( $amount != "" )
        {
            $postargs .= "<amount currency=\"USD\">".$amount."</amount>";
        }
        $postargs .= "</charge-order>";
        return $this->SendReq( $this->request_url, $this->GetAuthenticationHeaders( ), $postargs, $message_log );
    }

    public function SendRefundOrder( $google_order, $amount, $reason, $comment, $message_log )
    {
        $postargs = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n                   <refund-order xmlns=\"".$this->schema_url."\" google-order-number=\"".$google_order."\">\n                   <reason>".$reason."</reason>\n                   <amount currency=\"USD\">".htmlentities( $amount )."</amount>\n                   <comment>".htmlentities( $comment )."</comment>\n                  </refund-order>";
        return $this->SendReq( $this->request_url, $this->GetAuthenticationHeaders( ), $postargs, $message_log );
    }

    public function SendCancelOrder( $google_order, $reason, $comment, $message_log )
    {
        $postargs = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n                   <cancel-order xmlns=\"".$this->schema_url."\" google-order-number=\"".$google_order."\">\n                   <reason>".htmlentities( $reason )."</reason>\n                   <comment>".htmlentities( $comment )."</comment>\n                  </cancel-order>";
        return $this->SendReq( $this->request_url, $this->GetAuthenticationHeaders( ), $postargs, $message_log );
    }

    public function SendTrackingData( $google_order, $carrier, $tracking_no, $message_log )
    {
        $postargs = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n                   <add-tracking-data xmlns=\"".$this->schema_url."\" google-order-number=\"".$google_order."\">\n                   <tracking-data>\n                   <carrier>".htmlentities( $carrier )."</carrier>\n                   <tracking-number>".$tracking_no."</tracking-number>\n                   </tracking-data>\n                   </add-tracking-data>";
        return $this->SendReq( $this->request_url, $this->GetAuthenticationHeaders( ), $postargs, $message_log );
    }

    public function SendMerchantOrderNumber( $google_order, $merchant_order, $message_log )
    {
        $postargs = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n                   <add-merchant-order-number xmlns=\"".$this->schema_url."\" google-order-number=\"".$google_order."\">\n                     <merchant-order-number>".$merchant_order."</merchant-order-number>\n                   </add-merchant-order-number>";
        return $this->SendReq( $this->request_url, $this->GetAuthenticationHeaders( ), $postargs, $message_log );
    }

    public function SendBuyerMessage( $google_order, $message, $send_mail = "true", $message_log )
    {
        $postargs = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n                   <send-buyer-message xmlns=\"".$this->schema_url."\" google-order-number=\"".$google_order."\">\n                     <message>".$message."</message>\n                     <send-mail>".$send_mail."</send-mail>\n                   </send-buyer-message>";
        return $this->SendReq( $this->request_url, $this->GetAuthenticationHeaders( ), $postargs, $message_log );
    }

    public function SendProcessOrder( $google_order, $message_log )
    {
        $postargs = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n                  <process-order xmlns=\"".$this->schema_url."\" google-order-number=\"".$google_order."\"/> ";
        return $this->SendReq( $this->request_url, $this->GetAuthenticationHeaders( ), $postargs, $message_log );
    }

    public function SendDeliverOrder( $google_order, $carrier, $tracking_no, $send_mail = "true", $message_log )
    {
        $postargs = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n                   <deliver-order xmlns=\"".$this->schema_url."\" google-order-number=\"".$google_order."\">\n                   <tracking-data>\n                   <carrier>".htmlentities( $carrier )."</carrier>\n                   <tracking-number>".$tracking_no."</tracking-number>\n                   </tracking-data>\n                   <send-email>".$send_mail."</send-email>\n                   </deliver-order>";
        return $this->SendReq( $this->request_url, $this->GetAuthenticationHeaders( ), $postargs, $message_log );
    }

    public function SendArchiveOrder( $google_order, $message_log )
    {
        $postargs = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n                   <archive-order xmlns=\"".$this->schema_url."\" google-order-number=\"".$google_order."\"/>";
        return $this->SendReq( $this->request_url, $this->GetAuthenticationHeaders( ), $postargs, $message_log );
    }

    public function SendUnarchiveOrder( $google_order, $message_log )
    {
        $postargs = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n                   <unarchive-order xmlns=\"".$this->schema_url."\" google-order-number=\"".$google_order."\"/>";
        return $this->SendReq( $this->request_url, $this->GetAuthenticationHeaders( ), $postargs, $message_log );
    }

    public function ProcessMerchantCalculations( $merchant_calc )
    {
        $result = $merchant_calc->GetXML( );
        echo $result;
    }

    public function GetAuthenticationHeaders( )
    {
        $headers = array( );
        $headers[] = "Authorization: Basic ".base64_encode( $this->merchant_id.":".$this->merchant_key );
        $headers[] = "Content-Type: application/xml";
        $headers[] = "Accept: application/xml";
        return $headers;
    }

    public function SendReq( $url, $header_arr, $postargs, $message_log )
    {
        $session = curl_init( $url );
        curl_setopt( $session, CURLOPT_POST, TRUE );
        curl_setopt( $session, CURLOPT_HTTPHEADER, $header_arr );
        curl_setopt( $session, CURLOPT_POSTFIELDS, $postargs );
        curl_setopt( $session, CURLOPT_HEADER, TRUE );
        curl_setopt( $session, CURLOPT_RETURNTRANSFER, TRUE );
        $response = curl_exec( $session );
        if ( curl_errno( $session ) )
        {
            exit( curl_error( $session ) );
        }
        else
        {
            curl_close( $session );
        }
        $status_code = array( );
        preg_match( "/\\d\\d\\d/", $response, $status_code );
        switch ( $status_code[0] )
        {
        case 200 :
            break;
        case 503 :
            exit( "Error 503: Service unavailable." );
            break;
        case 403 :
            exit( "Error 403: Forbidden." );
            break;
        case 400 :
            exit( "Error 400: Bad request." );
            break;
        default :
            echo $response;
            exit( "Error :".$status_code[0] );
        }
        fwrite( $message_log, sprintf( "\n\r%s:- %s\n", date( "D M j G:i:s T Y" ), $response ) );
    }

    public function SendAck( )
    {
        $acknowledgment = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><notification-acknowledgment xmlns=\"".$this->schema_url."\"/>";
        echo $acknowledgment;
    }

}

class GoogleResult
{

    public $shipping_name = NULL;
    public $address_id = NULL;
    public $shippable = NULL;
    public $ship_price = NULL;
    public $ship_currency = NULL;
    public $tax_currency = NULL;
    public $tax_amount = NULL;
    public $coupon_arr = array( );
    public $giftcert_arr = array( );

    public function GoogleResult( $address_id )
    {
        $this->address_id = $address_id;
    }

    public function SetShippingDetails( $name, $price, $money = "USD", $shippable = "true" )
    {
        $this->shipping_name = $name;
        $this->ship_price = $price;
        $this->ship_currency = $money;
        $this->shippable = $shippable;
    }

    public function SetTaxDetails( $amount, $currency = "USD" )
    {
        $this->tax_amount = $amount;
        $this->tax_currency = $currency;
    }

    public function AddCoupons( $coupon )
    {
        $this->coupon_arr[] = $coupon;
    }

    public function AddGiftCertificates( $gift )
    {
        $this->giftcert_arr[] = $gift;
    }

}

class GoogleCoupons
{

    public $coupon_valid = NULL;
    public $coupon_code = NULL;
    public $coupon_currency = NULL;
    public $coupon_amount = NULL;
    public $coupon_message = NULL;

    public function googlecoupons( $valid, $code, $amount, $currency, $message )
    {
        $this->coupon_valid = $valid;
        $this->coupon_code = $code;
        $this->coupon_currency = $currency;
        $this->coupon_amount = $amount;
        $this->coupon_message = $message;
    }

}

class GoogleGiftcerts
{

    public $gift_valid = NULL;
    public $gift_code = NULL;
    public $gift_currency = NULL;
    public $gift_amount = NULL;
    public $gift_message = NULL;

    public function googlegiftcerts( $valid, $code, $amount, $currency, $message )
    {
        $this->gift_valid = $valid;
        $this->gift_code = $code;
        $this->gift_currency = $currency;
        $this->gift_amount = $amount;
        $this->gift_message = $message;
    }

}

require( "paymentPlugin.php" );
class pay_google extends paymentPlugin
{

    public $name = "Google Checkout";
    public $logo = "GOOGLE";
    public $version = 20070902;
    public $charset = "utf-8";
    public $submitUrl = "https://checkout.google.com/cws/v2/Merchant/";
    public $submitButton = "http://img.alipay.com/pimg/button_alipaybutton_o_a.gif";
    public $supportCurrency = array
    (
        "USD" => "USD",
        "EUR" => "EUR",
        "GBP" => "GBP",
        "MYR" => "MYR"
    );
    public $supportArea = array
    (
        0 => "AREA_USD",
        1 => "AREA_EUR",
        2 => "AREA_GBP",
        3 => "AREA_MYR"
    );
    public $desc = "Google Checkout是由Google提供的一种在线支付接口，使用此接口必须要求商店服务器端安装并且支持国际标准的加密及身份认证通信协议SSL，并且在Google Checkout帐户后台设置返回地址为https://您的商店域名/plugins/payment/pay.GOOGLE.php ，这样才能正确使用此接口，如果您的服务器暂时不支持SSL协议，则无法使用Google Checkout。";
    public $orderby = 23;
    public $cur_trading = TRUE;

    public function toSubmit( $payment )
    {
        $merId = $this->getConf( $payment['M_OrderId'], "member_id" );
        $ikey = $this->getConf( $payment['M_OrderId'], "PrivateKey" );
        $this->submitUrl = "https://checkout.google.com/cws/v2/Merchant/".$merId."/checkout";
        $this->callbackUrl = str_replace( "http://", "https://", $this->callbackUrl );
        $ordAmount = number_format( $payment['M_Amount'], 2, ".", "" );
        ( $merId, $ikey, "checkout" );
        $cart = new GoogleCart( );
        ( $payment['M_OrderId'], $payment['M_OrderNO'], 1, $ordAmount );
        $item1 = new GoogleItem( );
        $cart->AddItem( $item1 );
        $return = $cart->getArr( "large" );
        return $return;
    }

    public function callback( $in, &$paymentId, &$money, &$message )
    {
        define( "RESPONSE_HANDLER_LOG_FILE", "googlemessage.log" );
        if ( !( $message_log = fopen( RESPONSE_HANDLER_LOG_FILE, "a" ) ) )
        {
            $message = "Cannot open ".RESPONSE_HANDLER_LOG_FILE." file.";
            return PAY_ERROR;
        }
        $xml_response = $HTTP_RAW_POST_DATA;
        if ( get_magic_quotes_gpc( ) )
        {
            $xml_response = stripslashes( $xml_response );
        }
        $headers = getallheaders( );
        fwrite( $message_log, sprintf( "\n\r%s:- %s\n", date( "D M j G:i:s T Y" ), $xml_response ) );
        $merchant_key = $this->getConf( $in['M_OrderId'], "PrivateKey" );
        $server_type = "checkout";
        ( $merchant_id, $merchant_key, $xml_response, $server_type );
        $response = new GoogleResponse( );
        $root = $response->root;
        $data = $response->data;
        fwrite( $message_log, sprintf( "\n\r%s:- %s\n", date( "D M j G:i:s T Y" ), $response->root ) );
        $status = $response->HttpAuthentication( $headers );
        switch ( $root )
        {
        case "request-received" :
            break;
        case "error" :
            break;
        case "diagnosis" :
            break;
        case "checkout-redirect" :
            break;
        case "merchant-calculation-callback" :
            ( );
            $merchant_calc = new GoogleMerchantCalculations( );
            $addresses = $this->get_arr_result( $data[$root]['calculate']['addresses']['anonymous-address'] );
            foreach ( $addresses as $curr_address )
            {
                $curr_id = $curr_address['id'];
                $country = $curr_address['country-code']['VALUE'];
                $city = $curr_address['city']['VALUE'];
                $region = $curr_address['region']['VALUE'];
                $postal_code = $curr_address['region']['VALUE'];
                if ( isset( $data[$root]['calculate']['shipping'] ) )
                {
                    $shipping = $this->get_arr_result( $data[$root]['calculate']['shipping']['method'] );
                    foreach ( $shipping as $curr_ship )
                    {
                        $name = $curr_ship['name'];
                        $price = 10;
                        $shippable = "true";
                        ( $curr_id );
                        $merchant_result = new GoogleResult( );
                        $merchant_result->SetShippingDetails( $name, $price, "USD", $shippable );
                        if ( $data[$root]['calculate']['tax']['VALUE'] == "true" )
                        {
                            $amount = 15;
                            $merchant_result->SetTaxDetails( $amount, "USD" );
                        }
                        $codes = $this->get_arr_result( $data[$root]['calculate']['merchant-code-strings']['merchant-code-string'] );
                        foreach ( $codes as $curr_code )
                        {
                            ( "true", $curr_code['code'], 5, "USD", "test2" );
                            $coupons = new GoogleCoupons( );
                            $merchant_result->AddCoupons( $coupons );
                        }
                        $merchant_calc->AddResult( $merchant_result );
                    }
                }
                else
                {
                    ( $curr_id );
                    $merchant_result = new GoogleResult( );
                    if ( $data[$root]['calculate']['tax']['VALUE'] == "true" )
                    {
                        $amount = 15;
                        $merchant_result->SetTaxDetails( $amount, "USD" );
                    }
                    $codes = $this->get_arr_result( $data[$root]['calculate']['merchant-code-strings']['merchant-code-string'] );
                    foreach ( $codes as $curr_code )
                    {
                        ( "true", $curr_code['code'], 5, "USD", "test2" );
                        $coupons = new GoogleCoupons( );
                        $merchant_result->AddCoupons( $coupons );
                    }
                    $merchant_calc->AddResult( $merchant_result );
                }
            }
            fwrite( $message_log, sprintf( "\n\r%s:- %s\n", date( "D M j G:i:s T Y" ), $merchant_calc->GetXML( ) ) );
            $response->ProcessMerchantCalculations( $merchant_calc );
            break;
        case "new-order-notification" :
            $response->SendAck( );
            break;
        case "order-state-change-notification" :
            $response->SendAck( );
            $new_financial_state = $data[$root]['new-financial-order-state']['VALUE'];
            $new_fulfillment_order = $data[$root]['new-fulfillment-order-state']['VALUE'];
            switch ( $new_financial_state )
            {
            case "REVIEWING" :
                break;
            case "CHARGEABLE" :
                break;
            case "CHARGING" :
                break;
            case "CHARGED" :
                break;
            case "PAYMENT_DECLINED" :
                break;
            case "CANCELLED" :
                break;
            case "CANCELLED_BY_GOOGLE" :
                break;
            default :
                break;
            }
            switch ( $new_fulfillment_order )
            {
            case "NEW" :
                break;
            case "PROCESSING" :
                break;
            case "DELIVERED" :
                break;
            case "WILL_NOT_DELIVER" :
                break;
            default :
                break;
            }
        case "charge-amount-notification" :
            $response->SendAck( );
            break;
        case "chargeback-amount-notification" :
            $response->SendAck( );
            break;
        case "refund-amount-notification" :
            $response->SendAck( );
            break;
        case "risk-information-notification" :
            $response->SendAck( );
            break;
        default :
            break;
        }
        return PAY_SUCCESS;
    }

    public function getfields( )
    {
        return array(
            "member_id" => array( "label" => "客户号", "type" => "string" ),
            "PrivateKey" => array( "label" => "私钥", "type" => "string" )
        );
    }

    public function get_arr_result( $child_node )
    {
        $result = array( );
        if ( isset( $child_node ) )
        {
            if ( is_associative_array( $child_node ) )
            {
                $result[] = $child_node;
            }
            else
            {
                foreach ( $child_node as $curr_node )
                {
                    $result[] = $curr_node;
                }
            }
        }
        return $result;
    }

    public function is_associative_array( $var )
    {
        return is_array( $var ) && !is_numeric( implode( "", array_keys( $var ) ) );
    }

}

?>
