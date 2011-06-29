<?php
require('paymentPlugin.php');
class pay_google extends paymentPlugin{

    var $name = 'Google Checkout';//Google Checkout
    var $logo = 'GOOGLE';
    var $version = 20070902;
    var $charset = 'utf-8';
    var $submitUrl = 'https://checkout.google.com/cws/v2/Merchant/';
    var $submitButton = 'http://img.alipay.com/pimg/button_alipaybutton_o_a.gif'; ##需要完善的地方
    var $supportCurrency = array("USD"=>"USD","EUR"=>"EUR","GBP"=>"GBP","MYR"=>"MYR");
    var $supportArea =  array("AREA_USD","AREA_EUR","AREA_GBP","AREA_MYR");
    var $desc = 'Google Checkout是由Google提供的一种在线支付接口，使用此接口必须要求商店服务器端安装并且支持国际标准的加密及身份认证通信协议SSL，并且在Google Checkout帐户后台设置返回地址为https://您的商店域名/plugins/payment/pay.GOOGLE.php ，这样才能正确使用此接口，如果您的服务器暂时不支持SSL协议，则无法使用Google Checkout。';
    var $orderby = 23;
    var $cur_trading = true;    //支持真实的外币交易

    function toSubmit($payment){
        $merId = $this->getConf($payment["M_OrderId"], 'member_id');
        $ikey = $this->getConf($payment["M_OrderId"], 'PrivateKey');

        $this->submitUrl = "https://checkout.google.com/cws/v2/Merchant/".$merId."/checkout";
        $this->callbackUrl = str_replace("http://", "https://", $this->callbackUrl);
        $ordAmount = number_format($payment["M_Amount"], 2, ".", "");//number_format($order->M_Amount, 2, ".", "");

        $cart =  new GoogleCart($merId, $ikey, "checkout");
        $item1 = new GoogleItem($payment["M_OrderId"],$payment["M_OrderNO"],1,$ordAmount);//($order->M_OrderId, $order->M_OrderNO, 1, $ordAmount);
        $cart->AddItem($item1);

        $return = $cart->getArr("large");

        return $return;
    }

    function callback($in,&$paymentId,&$money,&$message){
        ##chdir("..");
        define('RESPONSE_HANDLER_LOG_FILE', 'googlemessage.log');

        //Setup the log file
        if (!$message_log = fopen(RESPONSE_HANDLER_LOG_FILE, "a")) {
                $message  = "Cannot open " . RESPONSE_HANDLER_LOG_FILE . " file.";
                return PAY_ERROR;
        }

        // Retrieve the XML sent in the HTTP POST request to the ResponseHandler
        $xml_response = $HTTP_RAW_POST_DATA;
        if (get_magic_quotes_gpc()) {
            $xml_response = stripslashes($xml_response);
        }
        $headers = getallheaders();
        fwrite($message_log, sprintf("\n\r%s:- %s\n",date("D M j G:i:s T Y"),$xml_response));

        $merchant_key = $this->getConf($in["M_OrderId"], 'PrivateKey');    //todo支付号没有
        // Create new response object
        //$merchant_id = "";  //Your Merchant ID
        //$merchant_key = "";  //Your Merchant Key
        $server_type = "checkout";

        $response = new GoogleResponse($merchant_id, $merchant_key, $xml_response, $server_type);
        $root = $response->root;
        $data = $response->data;
        fwrite($message_log, sprintf("\n\r%s:- %s\n",date("D M j G:i:s T Y"), $response->root));

        //Use the following two lines to log the associative array storing the XML data
        //fwrite($message_log, sprintf("\n\r%s:- %s\n",date("D M j G:i:s T Y"),$result));

        //Check status and take appropriate action
        $status = $response->HttpAuthentication($headers);

        /* Commands to send the various order processing APIs
        * Send charge order : $response->SendChargeOrder($data[$root]
        *    ['google-order-number']['VALUE'], <amount>, $message_log);
        * Send proces order : $response->SendProcessOrder($data[$root]
        *    ['google-order-number']['VALUE'], $message_log);
        * Send deliver order: $response->SendDeliverOrder($data[$root]
        *    ['google-order-number']['VALUE'], <carrier>, <tracking-number>,
        *    <send_mail>, $message_log);
        * Send archive order: $response->SendArchiveOrder($data[$root]
        *    ['google-order-number']['VALUE'], $message_log);
        *
        */

        switch ($root) {
            case "request-received": {
              break;
            }
            case "error": {
              break;
            }
            case "diagnosis": {
              break;
            }
            case "checkout-redirect": {
              break;
            }
            case "merchant-calculation-callback": {
              // Create the results and send it
              $merchant_calc = new GoogleMerchantCalculations();

              // Loop through the list of address ids from the callback
              $addresses = $this->get_arr_result($data[$root]['calculate']['addresses']['anonymous-address']);
              foreach($addresses as $curr_address) {
                $curr_id = $curr_address['id'];
                $country = $curr_address['country-code']['VALUE'];
                $city = $curr_address['city']['VALUE'];
                $region = $curr_address['region']['VALUE'];
                $postal_code = $curr_address['region']['VALUE'];

                // Loop through each shipping method if merchant-calculated shipping
                // support is to be provided
                if(isset($data[$root]['calculate']['shipping'])) {
                  $shipping = $this->get_arr_result($data[$root]['calculate']['shipping']['method']);
                  foreach($shipping as $curr_ship) {
                    $name = $curr_ship['name'];
                    //Compute the price for this shipping method and address id
                    $price = 10; // Modify this to get the actual price
                    $shippable = "true"; // Modify this as required
                    $merchant_result = new GoogleResult($curr_id);
                    $merchant_result->SetShippingDetails($name, $price, "USD",
                        $shippable);

                    if($data[$root]['calculate']['tax']['VALUE'] == "true") {
                      //Compute tax for this address id and shipping type
                      $amount = 15; // Modify this to the actual tax value
                      $merchant_result->SetTaxDetails($amount, "USD");
                    }

                    $codes = $this->get_arr_result($data[$root]['calculate']['merchant-code-strings']['merchant-code-string']);
                    foreach($codes as $curr_code) {
                      //Update this data as required to set whether the coupon is valid, the code and the amount
                      $coupons = new GoogleCoupons("true", $curr_code['code'], 5, "USD", "test2");
                      $merchant_result->AddCoupons($coupons);
                    }
                    $merchant_calc->AddResult($merchant_result);
                  }
                } else {
                  $merchant_result = new GoogleResult($curr_id);
                  if($data[$root]['calculate']['tax']['VALUE'] == "true") {
                    //Compute tax for this address id and shipping type
                    $amount = 15; // Modify this to the actual tax value
                    $merchant_result->SetTaxDetails($amount, "USD");
                  }
                  $codes = $this->get_arr_result($data[$root]['calculate']['merchant-code-strings']['merchant-code-string']);
                  foreach($codes as $curr_code) {
                    //Update this data as required to set whether the coupon is valid, the code and the amount
                    $coupons = new GoogleCoupons("true", $curr_code['code'], 5, "USD", "test2");
                    $merchant_result->AddCoupons($coupons);
                  }
                  $merchant_calc->AddResult($merchant_result);
                }
              }
              fwrite($message_log, sprintf("\n\r%s:- %s\n",date("D M j G:i:s T Y"),$merchant_calc->GetXML()));
              $response->ProcessMerchantCalculations($merchant_calc);
              break;
            }
            case "new-order-notification": {
              $response->SendAck();
              break;
            }
            case "order-state-change-notification": {
                  $response->SendAck();
                  $new_financial_state = $data[$root]['new-financial-order-state']['VALUE'];
                  $new_fulfillment_order = $data[$root]['new-fulfillment-order-state']['VALUE'];

                  switch($new_financial_state) {
                    case 'REVIEWING': {
                      break;
                    }
                    case 'CHARGEABLE': {
                      //$response->SendProcessOrder($data[$root]['google-order-number']['VALUE'],
                      //    $message_log);
                      //$response->SendChargeOrder($data[$root]['google-order-number']['VALUE'],
                      //    '', $message_log);
                      break;
                    }
                    case 'CHARGING': {
                      break;
                    }
                    case 'CHARGED': {
                      break;
                    }
                    case 'PAYMENT_DECLINED': {
                      break;
                    }
                    case 'CANCELLED': {
                      break;
                    }
                    case 'CANCELLED_BY_GOOGLE': {
                      //$response->SendBuyerMessage($data[$root]['google-order-number']['VALUE'],
                      //    "Sorry, your order is cancelled by Google", true, $message_log);
                      break;
                    }
                    default:
                      break;
               }

               switch($new_fulfillment_order) {
                    case 'NEW': {
                      break;
                    }
                    case 'PROCESSING': {
                      break;
                    }
                    case 'DELIVERED': {
                      break;
                    }
                    case 'WILL_NOT_DELIVER': {
                      break;
                    }
                    default:
                      break;
               }
            }
            case "charge-amount-notification": {
              $response->SendAck();
              //$response->SendDeliverOrder($data[$root]['google-order-number']['VALUE'],
              //    <carrier>, <tracking-number>, <send-email>, $message_log);
              //$response->SendArchiveOrder($data[$root]['google-order-number']['VALUE'],
              //    $message_log);
              break;
            }
            case "chargeback-amount-notification": {
              $response->SendAck();
              break;
            }
            case "refund-amount-notification": {
              $response->SendAck();
              break;
            }
            case "risk-information-notification": {
              $response->SendAck();
              break;
            }
            default: {
              break;
            }
        }
        return PAY_SUCCESS;##
    }

    function getfields(){
        return array(
                'member_id'=>array(
                        'label'=>'客户号',
                        'type'=>'string'
                ),
                'PrivateKey'=>array(
                        'label'=>'私钥',
                        'type'=>'string'
                )
            );
    }

    function get_arr_result($child_node) {
        $result = array();
        if(isset($child_node)) {
          if(is_associative_array($child_node)) {
            $result[] = $child_node;
          }
          else {
            foreach($child_node as $curr_node){
              $result[] = $curr_node;
            }
          }
        }
        return $result;
      }

    /* Returns true if a given variable represents an associative array */
    function is_associative_array( $var ) {
        return is_array( $var ) && !is_numeric( implode( '', array_keys( $var ) ) );
    }
}

/*
  Copyright (C) 2006 Google Inc.

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; either version 2
  of the License, or (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

/*
 * Class used to generate XML data
 * Based on sample code available at http://simon.incutio.com/code/php/XmlWriter.class.php.txt
 */

  class XmlBuilder {
    var $xml;
    var $indent;
    var $stack = array();

    function XmlBuilder($indent = '  ') {
      $this->indent = $indent;
      $this->xml = '<?xml version="1.0" encoding="utf-8"?>'."\n";
    }

    function _indent() {
      for ($i = 0, $j = count($this->stack); $i < $j; $i++) {
        $this->xml .= $this->indent;
      }
    }

    //Used when an element has sub-elements
    // This function adds an open tag to the output
    function Push($element, $attributes = array()) {
      $this->_indent();
      $this->xml .= '<'.$element;
      foreach ($attributes as $key => $value) {
        $this->xml .= ' '.$key.'="'.htmlentities($value).'"';
      }
      $this->xml .= ">\n";
      $this->stack[] = $element;
    }

    //Used when an element has no subelements.
    //Data within the open and close tags are provided with the
    //contents variable
    function Element($element, $content, $attributes = array()) {
      $this->_indent();
      $this->xml .= '<'.$element;
      foreach ($attributes as $key => $value) {
        $this->xml .= ' '.$key.'="'.htmlentities($value).'"';
      }
      $this->xml .= '>'.htmlentities($content).'</'.$element.'>'."\n";
    }

    function EmptyElement($element, $attributes = array()) {
      $this->_indent();
      $this->xml .= '<'.$element;
      foreach ($attributes as $key => $value) {
        $this->xml .= ' '.$key.'="'.htmlentities($value).'"';
      }
      $this->xml .= " />\n";
    }

    //Used to close an open tag
    function Pop($pop_element) {
      $element = array_pop($this->stack);
      $this->_indent();
      if($element !== $pop_element)
        die('XML Error: Tag Mismatch when trying to close "'. $pop_element. '"');
      else
        $this->xml .= "</$element>\n";
    }

    function GetXML() {
      if(count($this->stack) != 0)
        die ('XML Error: No matching closing tag found for " '. array_pop($this->stack). '"');
      else
        return $this->xml;
    }
  }

  /* This uses SAX parser to convert XML data into PHP associative arrays
 * When invoking the constructor with the input data, strip out the first XML line
 *
 * Member field Description:
 * $params: This stores the XML data. The attributes and contents of XML tags
 * can be accessed as follows
 *
 * <addresses>
 *  <anonymous-address id="123"> <test>data 1 </test>
 *  </anonymous-address>
 *  <anonymous-address id="456"> <test>data 2 </test>
 *  </anonymous-address>
 * </addresses>
 *
 Array
(
    [addresses] => Array
        (
            [anonymous-address] => Array
                (
                    [0] => Array
                        (
                            [id] => 123
                            [test] => Array
                                (
                                    [VALUE] => data 1
                                )

                        )

                    [1] => Array
                        (
                            [id] => 456
                            [test] => Array
                                (
                                    [VALUE] => data 2
                                )

                        )

                )

        )

)
  * XmlParser returns an empty params array if it encounters
  * any error during parsing
  */
  class XmlParser {

    var $params= array(); //Stores the object representation of XML data
    var $root;
    var $global_index = -1;

   /* Constructor for the class
    * Takes in XML data as input( do not include the <xml> tag
    */
    function XmlParser($input) {
      $xmlp = xml_parser_create();
      xml_parse_into_struct($xmlp, $input, $vals, $index);
      xml_parser_free($xmlp);
      $this->root = strtolower($vals[0]['tag']);
      $this->params = $this->UpdateRecursive($vals);
    }

   /* Returns true if a given variable represents an associative array */
    function is_associative_array( $var ) {
      return is_array( $var ) && !is_numeric( implode( '', array_keys( $var ) ) );
    }

   /* Converts the output of SAX parser into a PHP associative array similar to the
    * DOM parser output
    */
    function UpdateRecursive($vals) {
      $this->global_index++;
      //Reached end of array
      if($this->global_index >= count($vals))
        return;

      $tag = strtolower($vals[$this->global_index]['tag']);
      $value = trim($vals[$this->global_index]['value']);
      $type = $vals[$this->global_index]['type'];

      //Add attributes
      if(isset($vals[$this->global_index]['attributes'])) {
        foreach($vals[$this->global_index]['attributes'] as $key=>$val) {
          $key = strtolower($key);
          $params[$tag][$key] = $val;
        }
      }

      if($type == 'open') {
        $new_arr = array();

        //Read all elements at the next levels and add to an array
        while($vals[$this->global_index]['type'] != 'close' &&
              $this->global_index < count($vals)) {
          $arr = $this->UpdateRecursive($vals);
          if(count($arr) > 0) {
            $new_arr[] = $arr;
          }
        }
        $this->global_index++;
        foreach($new_arr as $arr) {
          foreach($arr as $key=>$val) {
            if(isset($params[$tag][$key])) {
              //If this key already exists
              if($this->is_associative_array($params[$tag][$key])) {
                //If this is an associative array and not an indexed array
                // remove exisiting value and convert to an indexed array
                $val_key = $params[$tag][$key];
                array_splice($params[$tag][$key], 0);
                $params[$tag][$key][0] =  $val_key;
                $params[$tag][$key][] =  $val;
              } else {
                $params[$tag][$key][] =  $val;
              }
            } else {
              $params[$tag][$key] =  $val;
            }
          }
        }
      }
      else if ($type ==  'complete') {
        if($value != '')
          $params[$tag]['VALUE'] = $value;
      }
      else
        $params = array();
      return $params;
    }

    /* Returns the root of the XML data */
    function GetRoot() {
      return $this->root;
    }

    /* Returns the array representing the XML data */
    function GetData() {
      return $this->params;
    }
  }


 /* This class is used to create a Google Checkout shopping cart and post it
  * to the Sandbox or Production environment
  * A very useful function is the CheckoutButtonCode() which returns the HTML
  * code to post the cart using the standard technique
  * Refer demo/cartdemo.php for different use case scenarios for this code
  */

  class GoogleCart {
    var $merchant_id;
    var $merchant_key;
    var $server_url;
    var $schema_url;
    var $base_url;
    var $checkout_url;
    var $checkout_diagnose_url;
    var $request_url;
    var $request_diagnose_url;

    var $cart_expiration = "";
    var $merchant_private_data = "";
    var $edit_cart_url = "";
    var $continue_shopping_url = "";
    var $request_buyer_phone = "";
    var $merchant_calculated = "";
    var $merchant_calculations_url = "";
    var $accept_merchant_coupons = "";
    var $accept_gift_certificates = "";
    var $default_tax_table;

    var $item_arr;
    var $shipping_arr;
    var $alternate_tax_table_arr;
    var $xml_data;

    //The Constructor method which requires a merchant id, merchant key
    //and the operation type(sandbox or checkout)
    function GoogleCart($id, $key, $server_type ="checkout") {
      $this->merchant_id = $id;
      $this->merchant_key = $key;

      if(strtolower($server_type) == "sandbox")
        $this->server_url = "https://sandbox.google.com/";
      else
        $this->server_url=  "https://checkout.google.com/";

      $this->schema_url = "http://checkout.google.com/schema/2";
      $this->base_url = $this->server_url."cws/v2/Merchant/" .
          $this->merchant_id;
      $this->checkout_url =  $this->base_url . "/checkout";
      $this->checkout_diagnose_url = $this->base_url .
          "/checkout/diagnose";
      $this->request_url = $this->base_url . "/request";
      $this->request_diagnose_url = $this->base_url .
          "/request/diagnose";

      //The item, shipping and tax table arrays are initialized
      $this->item_arr = array();
      $this->shipping_arr = array();
      $this->alternate_tax_table_arr = array();
    }

    function SetCartExpiration($cart_expire) {
      $this->cart_expiration = $cart_expire;
    }

    function SetMerchantPrivateData($data) {
      $this->merchant_private_data = $data;
    }

    function SetEditCartUrl($url) {
      $this->edit_cart_url= $url;
    }

    function SetContinueShoppingUrl($url) {
      $this->continue_shopping_url = $url;
    }

    function SetRequestBuyerPhone($req) {
      $this->_SetBooleanValue('request_buyer_phone', $req, "");
    }

    function SetMerchantCalculations($url, $tax_option = "false" ,
        $coupons="false",
        $gift_cert ="false") {
      $this->merchant_calculations_url = $url;
      $this->_SetBooleanValue('merchant_calculated', $tax_option, "false");
      $this->_SetBooleanValue('accept_merchant_coupons', $coupons, "false");
      $this->_SetBooleanValue('accept_gift_certificates', $gift_cert, "false");
    }

    function AddItem($google_item) {
      $this->item_arr[] = $google_item;
    }

    function AddShipping($ship) {
      $this->shipping_arr[] = $ship;
    }

    function AddTaxTables($tax) {
      if($tax->type == "default")
        $this->default_tax_table = $tax;
      else if ($tax->type == "alternate")
        $this->alternate_tax_table_arr[] = $tax;
    }

    function GetXML() {

      $xml_data = new XmlBuilder();

      $xml_data->Push('checkout-shopping-cart',
          array('xmlns' => $this->schema_url));
      $xml_data->Push('shopping-cart');

      //Add cart expiration if set
      if($this->cart_expiration != "") {
        $xml_data->Push('cart-expiration');
        $xml_data->Element('good-until-date', $this->cart_expiration);
        $xml_data->Pop('cart-expiration');
      }

      //Add XML data for each of the items
      $xml_data->Push('items');
      foreach($this->item_arr as $item) {
        $xml_data->Push('item');
        $xml_data->Element('item-name', $item->item_name);
        $xml_data->Element('item-description', $item->item_description);
        $xml_data->Element('unit-price', $item->unit_price,
            array('currency'=> $item->currency));
        $xml_data->Element('quantity', $item->quantity);
        if($item->merchant_private_data != '')
            $xml_data->Element('merchant-private-date',
            $item->merchant_private_data);
        if($item->tax_table_selector != '')
          $xml_data->Element('tax-table-selector', $item->tax_table_selector);
        $xml_data->Pop('item');
      }
      $xml_data->Pop('items');

      if($this->merchant_private_data != '')
        $xml_data->Element('merchant-private-data',
            $this->merchant_private_data);

      $xml_data->Pop('shopping-cart');

      $xml_data->Push('checkout-flow-support');
      $xml_data->Push('merchant-checkout-flow-support');
      if($this->edit_cart_url != '')
        $xml_data->Element('edit-cart-url', $this->edit_cart_url);
      if($this->continue_shopping_url != '')
        $xml_data->Element('continue-shopping-url',
            $this->continue_shopping_url);

      if(count($this->shipping_arr) > 0)
        $xml_data->Push('shipping-methods');

      //Add the shipping methods
      foreach($this->shipping_arr as $ship) {
        //Pickup shipping handled in else part
        if($ship->type == "flat-rate" ||
            $ship->type == "merchant-calculated") {
          $xml_data->Push($ship->type.'-shipping',
              array('name' => $ship->name));
          $xml_data->Element('price', $ship->price,
              array('currency' => $ship->currency));

          //Check if shipping restrictions have been specifd=ied
          if( $ship->allowed_restrictions  ||
              $ship->excluded_restrictions) {
            $xml_data->Push('shipping-restrictions');

            //Check if allowed restrictions specified
            if($ship->allowed_restrictions) {
              $xml_data->Push('allowed-areas');
              if($ship->allowed_country_area != "")
                $xml_data->Element('us-country-area','',
                    array('country-area' =>
                    $ship->allowed_country_area));
              foreach($ship->allowed_state_areas_arr as $current) {
                $xml_data->Push('us-state-area');
                $xml_data->Element('state', $current);
                $xml_data->Pop('us-state-area');
              }
              foreach($ship->allowed_zip_patterns_arr as $current) {
                $xml_data->Push('us-zip-area');
                $xml_data->Element('zip-pattern', $current);
                $xml_data->Pop('us-zip-area');
              }
              $xml_data->Pop('allowed-areas');
            }

            if($ship->excluded_restrictions) {
              $xml_data->Push('allowed-areas');
              $xml_data->Pop('allowed-areas');
              $xml_data->Push('excluded-areas');
              if($ship->excluded_country_area != "")
                $xml_data->Element('us-country-area','',
                    array('country-area' =>
                    $ship->excluded_country_area));
              foreach($ship->excluded_state_areas_arr as $current) {
                $xml_data->Push('us-state-area');
                $xml_data->Element('state', $current);
                $xml_data->Pop('us-state-area');
              }
              foreach($ship->excluded_zip_patterns_arr as $current) {
                $xml_data->Push('us-zip-area');
                $xml_data->Element('zip-pattern', $current);
                $xml_data->Pop('us-zip-area');
              }
              $xml_data->Pop('excluded-areas');
            }
            $xml_data->Pop('shipping-restrictions');
          }
          $xml_data->Pop($ship->type.'-shipping');
        }
        else if ($ship->type == "pickup") {
          $xml_data->Push('pickup', array('name' => $ship->name));
          $xml_data->Element('price', $ship->price,
              array('currency' => $ship->currency));
          $xml_data->Pop('pickup');
        }
      }
      if(count($this->shipping_arr) > 0)
        $xml_data->Pop('shipping-methods');

      if($this->request_buyer_phone != "")
        $xml_data->Element('request-buyer-phone-number',
            $this->request_buyer_phone);

      if($this->merchant_calculations_url != "") {
        $xml_data->Push('merchant-calculations');
        $xml_data->Element('merchant-calculations-url',
            $this->merchant_calculations_url);
        if($this->accept_merchant_coupons != "")
          $xml_data->Element('accept-merchant-coupons',
              $this->accept_merchant_coupons);
        if($this->accept_gift_certificates != "")
          $xml_data->Element('accept-gift-certificates',
              $this->accept_gift_certificates);
        $xml_data->Pop('merchant-calculations');
      }

      //Set Default and Alternate tax tables
      if( (count($this->alternate_tax_table_arr) != 0) || (isset($this->default_tax_table))) {
        if($this->merchant_calculated != "")
          $xml_data->Push('tax-tables', array('merchant-calculated' => $this->merchant_calculated));
        else
          $xml_data->Push('tax-tables');

        if(isset($this->default_tax_table)) {
          $curr_table = $this->default_tax_table;
          foreach($curr_table->tax_rules_arr as $curr_rule) {


              $xml_data->Push('default-tax-table');
                  $xml_data->Push('tax-rules');
                        foreach($curr_rule->state_areas_arr as $current) {
                    $xml_data->Push('default-tax-rule');

                        $xml_data->Element('shipping-taxed', $curr_rule->shipping_taxed);
                        $xml_data->Element('rate', $curr_rule->tax_rate);
                        $xml_data->Push('tax-area');
                        if($curr_rule->country_area != "")
                          $xml_data->Element('us-country-area','', array('country-area' => $curr_rule->country_area));
                          $xml_data->Push('us-state-area');
                              $xml_data->Element('state', $current);
                          $xml_data->Pop('us-state-area');

                        $xml_data->Pop('tax-area');
                    $xml_data->Pop('default-tax-rule');
                            }
                            foreach($curr_rule->zip_patterns_arr as $current) {
                    $xml_data->Push('default-tax-rule');

                        $xml_data->Element('shipping-taxed', $curr_rule->shipping_taxed);
                        $xml_data->Element('rate', $curr_rule->tax_rate);
                        $xml_data->Push('tax-area');

                        if($curr_rule->country_area != "")
                          $xml_data->Element('us-country-area','', array('country-area' => $curr_rule->country_area));
                          $xml_data->Push('us-zip-area');
                              $xml_data->Element('zip-pattern', $current);
                          $xml_data->Pop('us-zip-area');
                        $xml_data->Pop('tax-area');
                    $xml_data->Pop('default-tax-rule');
                            }
              $xml_data->Pop('tax-rules');
              $xml_data->Pop('default-tax-table');

          }

        }

        if(count($this->alternate_tax_table_arr) != 0) {
          $xml_data->Push('alternate-tax-tables');
          foreach($this->alternate_tax_table_arr as $curr_table) {
                        foreach($curr_table->tax_rules_arr as $curr_rule) {
                   $xml_data->Push('alternate-tax-table',array('standalone' => $curr_table->standalone, 'name' => $curr_table->name));
                          $xml_data->Push('alternate-tax-rules');
                                foreach($curr_rule->state_areas_arr as $current) {
                            $xml_data->Push('alternate-tax-rule');

                                $xml_data->Element('shipping-taxed', $curr_rule->shipping_taxed);
                                $xml_data->Element('rate', $curr_rule->tax_rate);
                                $xml_data->Push('tax-area');
                                if($curr_rule->country_area != "")
                                  $xml_data->Element('us-country-area','', array('country-area' => $curr_rule->country_area));
                                  $xml_data->Push('us-state-area');
                                      $xml_data->Element('state', $current);
                                  $xml_data->Pop('us-state-area');

                                $xml_data->Pop('tax-area');
                            $xml_data->Pop('alternate-tax-rule');
                                    }
                                    foreach($curr_rule->zip_patterns_arr as $current) {
                            $xml_data->Push('alternate-tax-rule');

                                $xml_data->Element('shipping-taxed', $curr_rule->shipping_taxed);
                                $xml_data->Element('rate', $curr_rule->tax_rate);
                                $xml_data->Push('tax-area');

                                if($curr_rule->country_area != "")
                                  $xml_data->Element('us-country-area','', array('country-area' => $curr_rule->country_area));
                                  $xml_data->Push('us-zip-area');
                                      $xml_data->Element('zip-pattern', $current);
                                  $xml_data->Pop('us-zip-area');
                                $xml_data->Pop('tax-area');
                            $xml_data->Pop('alternate-tax-rule');
                                    }
                    $xml_data->Pop('alternate-tax-rules');
                $xml_data->Pop('alternate-tax-table');
              }
             }
             $xml_data->Pop('alternate-tax-tables');
        }
        $xml_data->Pop('tax-tables');
      }
      $xml_data->Pop('merchant-checkout-flow-support');
      $xml_data->Pop('checkout-flow-support');
      $xml_data->Pop('checkout-shopping-cart');

      return $xml_data->GetXML();
    }

    function getArr(){
        return array(
                'cart'=>base64_encode($this->GetXML()),
                'signature'=>base64_encode($this->CalcHmacSha1($this->GetXML()))
            );
    }

    //Code for generating Checkout button
    function CheckoutButtonCode($size="large", $style="white", $variant="text", $loc="en_US") {

      switch ($size) {
        case "large":
            $width = "180";
            $height = "46";
            break;

        case "medium":
            $width = "168";
            $height = "44";
            break;

        case "small":
            $width = "160";
            $height = "43";
            break;

        default:
            break;
      }

      if ($variant == "text") {
        $data =
          "<p><input type=\"hidden\" name=\"cart\" value=\"". base64_encode($this->GetXML()) ."\">
           <input type=\"hidden\" name=\"signature\" value=\"". base64_encode($this->CalcHmacSha1($this->GetXML())). "\"></p>";
      } elseif ($variant == "disabled") {
        $data = "<p><img alt=\"Checkout\"
              src=\"". $this->server_url."buttons/checkout.gif?merchant_id=".$this->merchant_id."&w=".$width. "&h=".$height."&style=".$style."&variant=".$variant."&loc=".$loc."\"
              height=\"".$height."\" width=\"".$width. "\" /></p>";
      }
      return $data;
    }

    //Method which returns the encrypted google cart to make sure that the carts are not tampered with
    function CalcHmacSha1($data) {
      $key = $this->merchant_key;
      $blocksize = 64;
      $hashfunc = 'sha1';
      if (strlen($key) > $blocksize) {
        $key = pack('H*', $hashfunc($key));
      }
      $key = str_pad($key, $blocksize, chr(0x00));
      $ipad = str_repeat(chr(0x36), $blocksize);
      $opad = str_repeat(chr(0x5c), $blocksize);
      $hmac = pack(
                    'H*', $hashfunc(
                            ($key^$opad).pack(
                                    'H*', $hashfunc(
                                            ($key^$ipad).$data
                                    )
                            )
                    )
                );
      return $hmac;
    }

    //Method used internally to set true/false cart variables
    function _SetBooleanValue($string, $value, $default) {
      $value = strtolower($value);
      if($value== "true" || $value == "false")
        eval('$this->'.$string.'="'.$value.'";');
      else
        eval('$this->'.$string.'="'.$default.'";');
    }

  }


 /* This class is used to create items to be added to the shopping cart
  * Invoke a separate instance of this class for each item to be
  * added to the cart.
  * Required fields are the item name, description, quantity and price
  * The private-data and tax-selector for each item can be set in the
  * constructor call or using individual Set functions
  */
  class GoogleItem {

    var $item_name;
    var $item_description;
    var $unit_price;
    var $currency;
    var $quantity;
    var $merchant_private_data;
    var $tax_table_selector;

    function GoogleItem($name, $desc, $qty, $price, $money= "USD",
      $private_data = "", $tax_selector = "") {
      $this->item_name = $name;
      $this->item_description= $desc;
      $this->unit_price = $price;
      $this->quantity = $qty;
      $this->currency= $money;
      $this->merchant_private_data = $private_data;
      $this->tax_table_selector= $tax_selector;
    }

    function SetMerchantPrivateData($private_data) {
      $this->merchant_private_data = $private_data;
    }

    function SetTaxTableSelector($tax_selector) {
      $this->tax_table_selector= $tax_selector;
    }
  }

  /* This class is used to create the merchant callback results
  * when a merchant-calculated-feedback structure is received
  *
  * Multiple results are generated depending on the possible
  * combinations for shipping options and address ids
  *
  * Refer demo/responsehandler.php for generating these results
  */

  class GoogleMerchantCalculations {
     var $results_arr;
     var $schema_url = "http://checkout.google.com/schema/2";

    function GoogleMerchantCalculations() {
      $this->results_arr = array();
    }

    function AddResult($results) {
      $this->results_arr[] = $results;
    }

    function GetXML() {

      $xml_data = new XmlBuilder();
      $xml_data->Push('merchant-calculation-results', array('xmlns' => $this->schema_url));
      $xml_data->Push('results');

      foreach($this->results_arr as $result) {
        if($result->shipping_name != "") {
          $xml_data->Push('result', array('shipping-name' => $result->shipping_name, 'address-id' => $result->address_id));
          $xml_data->Element('shipping-rate', $result->ship_price, array('currency' => $result->ship_currency));
          $xml_data->Element('shippable', $result->shippable);
        } else
          $xml_data->Push('result', array('address-id' => $result->address_id));

        if($result->tax_amount != "")
          $xml_data->Element('total-tax', $result->tax_amount, array('currency' => $result->tax_currency));

        if((count($result->coupon_arr) != 0) || (count($result->giftcert_arr) != 0) ){
          $xml_data->Push('merchant-code-results');

          foreach($result->coupon_arr as $curr_coupon) {
            $xml_data->Push('coupon-result');
            $xml_data->Element('valid', $curr_coupon->coupon_valid);
            $xml_data->Element('code', $curr_coupon->coupon_code);
            $xml_data->Element('calculated-amount', $curr_coupon->coupon_amount,
                array('currency'=> $curr_coupon->coupon_currency));
            $xml_data->Element('message', $curr_coupon->coupon_message);
            $xml_data->Pop('coupon-result');
          }
          foreach($result->giftcert_arr as $curr_gift) {
            $xml_data->Push('gift-result');
            $xml_data->Element('valid', $curr_gift->gift_valid);
            $xml_data->Element('code', $curr_gift->gift_code);
            $xml_data->Element('calculated-amount', $curr_gift->gift_amount,
                array('currency'=> $curr_gift->gift_currency));
            $xml_data->Element('message', $curr_gift->gift_message);
            $xml_data->Pop('gift-result');
          }
          $xml_data->Pop('merchant-code-results');
        }
        $xml_data->Pop('result');
      }
      $xml_data->Pop('results');
      $xml_data->Pop('merchant-calculation-results');
      return $xml_data->GetXML();
    }
  }

   /* This class is instantiated everytime any notification or
  * order processing commands are received.
  *
  * It has a SendReq function to post different requests to the Google Server
  * Send functions are provided for most of the commands that are supported
  * by the server
  * Refer demo/responsehandlerdemo.php for different use case scenarios
  * for this code
  */

  class GoogleResponse {
    var $merchant_id;
    var $merchant_key;
    var $server_url;
    var $schema_url;
    var $base_url;
    var $checkout_url;
    var $checkout_diagnose_url;
    var $request_url;
    var $request_diagnose_url;

    var $response;
    var $root;
    var $data;
    var $xml_parser;

    function GoogleResponse($id, $key, $response, $server_type ="checkout") {
      $this->merchant_id = $id;
      $this->merchant_key = $key;

      if($server_type == "sandbox")
        $this->server_url = "https://sandbox.google.com/";
      else
        $this->server_url = "https://checkout.google.com/";

      $this->schema_url = "http://checkout.google.com/schema/2";
      $this->base_url = $this->server_url."cws/v2/Merchant/" .
          $this->merchant_id;
      $this->checkout_url =  $this->base_url . "/checkout";
      $this->checkout_diagnose_url = $this->base_url . "/checkout/diagnose";
      $this->request_url = $this->base_url . "/request";
      $this->request_diagnose_url = $this->base_url . "/request/diagnose";

      $this->response = $response;

      if(strpos(__FILE__, ':') !== false)
        $path_delimiter = ';';
      else
        $path_delimiter = ':';

      ini_set('include_path', ini_get('include_path').$path_delimiter.'.');
      $this->xml_parser = new XmlParser($response);
      $this->root = $this->xml_parser->GetRoot();
      $this->data = $this->xml_parser->GetData();
    }

    function HttpAuthentication($headers) {
      if(isset($headers['Authorization'])) {
        $auth_encode = $headers['Authorization'];
        $auth = base64_decode(substr($auth_encode,
            strpos($auth_encode, " ") + 1));
        $compare_mer_id = substr($auth, 0, strpos($auth,":"));
        $compare_mer_key = substr($auth, strpos($auth,":")+1);
      } else {
        return false;
      }
      if($compare_mer_id != $this->merchant_id ||
          $compare_mer_key != $this->merchant_key)
        return false;
      return true;
    }

    function SendChargeOrder($google_order, $amount='', $message_log) {
      $postargs = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                   <charge-order xmlns=\"".$this->schema_url.
                   "\" google-order-number=\"". $google_order. "\">";
      if ($amount != '') {
        $postargs .= "<amount currency=\"USD\">" . $amount . "</amount>";
      }
      $postargs .= "</charge-order>";
      return $this->SendReq($this->request_url, $this->GetAuthenticationHeaders(),
          $postargs, $message_log);
    }

    function SendRefundOrder($google_order, $amount, $reason, $comment, $message_log) {
      $postargs = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                   <refund-order xmlns=\"".$this->schema_url.
                   "\" google-order-number=\"". $google_order. "\">
                   <reason>". $reason . "</reason>
                   <amount currency=\"USD\">".htmlentities($amount)."</amount>
                   <comment>". htmlentities($comment) . "</comment>
                  </refund-order>";
      return $this->SendReq($this->request_url, $this->GetAuthenticationHeaders(),
          $postargs, $message_log);
    }

    function SendCancelOrder($google_order, $reason, $comment, $message_log) {
      $postargs = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                   <cancel-order xmlns=\"".$this->schema_url.
                   "\" google-order-number=\"". $google_order. "\">
                   <reason>". htmlentities($reason) . "</reason>
                   <comment>". htmlentities($comment) . "</comment>
                  </cancel-order>";
      return $this->SendReq($this->request_url, $this->GetAuthenticationHeaders(),
          $postargs, $message_log);
    }

    function SendTrackingData($google_order, $carrier, $tracking_no, $message_log) {
      $postargs = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                   <add-tracking-data xmlns=\"". $this->schema_url .
                   "\" google-order-number=\"". $google_order . "\">
                   <tracking-data>
                   <carrier>". htmlentities($carrier) . "</carrier>
                   <tracking-number>". $tracking_no . "</tracking-number>
                   </tracking-data>
                   </add-tracking-data>";
      return $this->SendReq($this->request_url, $this->GetAuthenticationHeaders(),
          $postargs, $message_log);
    }

    function SendMerchantOrderNumber($google_order, $merchant_order, $message_log) {
      $postargs = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                   <add-merchant-order-number xmlns=\"". $this->schema_url .
                   "\" google-order-number=\"". $google_order . "\">
                     <merchant-order-number>" . $merchant_order .
                     "</merchant-order-number>
                   </add-merchant-order-number>";
      return $this->SendReq($this->request_url, $this->GetAuthenticationHeaders(),
          $postargs, $message_log);
    }

    function SendBuyerMessage($google_order, $message, $send_mail="true",$message_log) {
      $postargs = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                   <send-buyer-message xmlns=\"". $this->schema_url .
                   "\" google-order-number=\"". $google_order . "\">
                     <message>" . $message . "</message>
                     <send-mail>" . $send_mail . "</send-mail>
                   </send-buyer-message>";
      return $this->SendReq($this->request_url, $this->GetAuthenticationHeaders(),
          $postargs, $message_log);
    }

    function SendProcessOrder($google_order, $message_log) {
      $postargs = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                  <process-order xmlns=\"".$this->schema_url    .
                  "\" google-order-number=\"". $google_order. "\"/> ";
      return $this->SendReq($this->request_url, $this->GetAuthenticationHeaders(),
          $postargs, $message_log);
    }

    function SendDeliverOrder($google_order, $carrier, $tracking_no, $send_mail = "true", $message_log) {
      $postargs = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                   <deliver-order xmlns=\"". $this->schema_url .
                   "\" google-order-number=\"". $google_order . "\">
                   <tracking-data>
                   <carrier>". htmlentities($carrier) . "</carrier>
                   <tracking-number>". $tracking_no . "</tracking-number>
                   </tracking-data>
                   <send-email>". $send_mail . "</send-email>
                   </deliver-order>";
      return $this->SendReq($this->request_url, $this->GetAuthenticationHeaders(),
          $postargs, $message_log);
    }

    function SendArchiveOrder($google_order, $message_log) {
      $postargs = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                   <archive-order xmlns=\"".$this->schema_url.
                   "\" google-order-number=\"". $google_order. "\"/>";
      return $this->SendReq($this->request_url, $this->GetAuthenticationHeaders(),
          $postargs, $message_log);
    }

    function SendUnarchiveOrder($google_order, $message_log) {
      $postargs = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                   <unarchive-order xmlns=\"".
                   $this->schema_url."\" google-order-number=\"".
                   $google_order. "\"/>";
      return $this->SendReq($this->request_url, $this->GetAuthenticationHeaders(),
          $postargs, $message_log);
    }

    function ProcessMerchantCalculations($merchant_calc) {
      $result = $merchant_calc->GetXML();
      echo $result;
    }

    function GetAuthenticationHeaders() {
      $headers = array();
      $headers[] = "Authorization: Basic ".base64_encode(
          $this->merchant_id.':'.$this->merchant_key);
      $headers[] = "Content-Type: application/xml";
      $headers[] = "Accept: application/xml";
      return $headers;
    }

    function SendReq($url, $header_arr, $postargs, $message_log) {
      // Get the curl session object
      $session = curl_init($url);

      // Set the POST options.
      curl_setopt($session, CURLOPT_POST, true);
      curl_setopt($session, CURLOPT_HTTPHEADER, $header_arr);
      curl_setopt($session, CURLOPT_POSTFIELDS, $postargs);
      curl_setopt($session, CURLOPT_HEADER, true);
      curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

      // Do the POST and then close the session
      $response = curl_exec($session);
      if (curl_errno($session)) {
        die(curl_error($session));
      } else {
        curl_close($session);
      }

      // Get HTTP Status code from the response
      $status_code = array();
      preg_match('/\d\d\d/', $response, $status_code);

      // Check for errors
      switch( $status_code[0] ) {
        case 200:
          // Success
          break;
        case 503:
          die('Error 503: Service unavailable.');
          break;
        case 403:
          die('Error 403: Forbidden.');
          break;
        case 400:
          die('Error 400: Bad request.');
          break;
        default:
          echo $response;
          die('Error :' . $status_code[0]);
      }
      fwrite($message_log, sprintf("\n\r%s:- %s\n",date("D M j G:i:s T Y"),
          $response));
    }

    function SendAck() {
      $acknowledgment = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" .
                        "<notification-acknowledgment xmlns=\"" .
                        $this->schema_url . "\"/>";
      echo $acknowledgment;
    }

  }


  /* This class is used to create a Google Checkout result for merchant
  * as a response to merchant-calculations feedback structure
  * Refer demo/responsehandlerdemo.php for usage of this code
  *
  * Methods are provided to set the shipping, tax, coupons and gift certificate
  * options
  */

  class GoogleResult {
    var $shipping_name;
    var $address_id;
    var $shippable;
    var $ship_price;
    var $ship_currency;

    var $tax_currency;
    var $tax_amount;

    var $coupon_arr = array();
    var $giftcert_arr = array();

    function GoogleResult($address_id) {
      $this->address_id = $address_id;
    }

    function SetShippingDetails($name, $price, $money = "USD",
        $shippable = "true") {
      $this->shipping_name = $name;
      $this->ship_price = $price;
      $this->ship_currency = $money;
      $this->shippable = $shippable;


    }

    function SetTaxDetails($amount, $currency = "USD") {
      $this->tax_amount = $amount;
      $this->tax_currency = $currency;
    }

    function AddCoupons($coupon) {
      $this->coupon_arr[] = $coupon;
    }

    function AddGiftCertificates($gift) {
      $this->giftcert_arr[] = $gift;
    }
  }

 /* This is a class used to return the results of coupons
  * that the buyer entered code for on the place order page
  */
  class GoogleCoupons {
    var $coupon_valid;
    var $coupon_code;
    var $coupon_currency;
    var $coupon_amount;
    var $coupon_message;

    function googlecoupons($valid, $code, $amount, $currency, $message) {
      $this->coupon_valid = $valid;
      $this->coupon_code = $code;
      $this->coupon_currency = $currency;
      $this->coupon_amount = $amount;
      $this->coupon_message = $message;
    }
  }

 /* This is a class used to return the results of gift certificates
  * that the buyer entered code for on the place order page
  */
  class GoogleGiftcerts {
    var $gift_valid;
    var $gift_code;
    var $gift_currency;
    var $gift_amount;
    var $gift_message;

    function googlegiftcerts($valid, $code, $amount, $currency, $message) {
      $this->gift_valid = $valid;
      $this->gift_code = $code;
      $this->gift_currency = $currency;
      $this->gift_amount = $amount;
      $this->gift_message = $message;
    }
  }
?>
