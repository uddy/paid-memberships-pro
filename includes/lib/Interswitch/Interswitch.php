<?php

abstract class Interswitchcheckout
{
    public static $product_id;
    public static $site_redirect_url;
	public static $pay_item_id;
	public static $mac_key;
    public static $format = "json";
    public static $apiBaseUrl = "https://stageserv.interswitchng.com";
    public static $error;
	
    const VERSION = '0.1.2';

    static function setCredentials($product_id,$pay_item_id,$mac_key,$site_redirect_url)
    {
        self::$product_id = $product_id;
        self::$pay_item_id = $pay_item_id;
		self::$mac_key = $mac_key;
		self::$site_redirect_url = $site_redirect_url;
    }
}

require(dirname(__FILE__) . '/Interswitch/Api/InterswitchcheckoutAccount.php');
require(dirname(__FILE__) . '/Interswitch/Api/InterswitchcheckoutPayment.php');
require(dirname(__FILE__) . '/Interswitch/Api/InterswitchcheckoutApi.php');
require(dirname(__FILE__) . '/Interswitch/Api/InterswitchcheckoutSale.php');
require(dirname(__FILE__) . '/Interswitch/Api/InterswitchcheckoutProduct.php');
require(dirname(__FILE__) . '/Interswitch/Api/InterswitchcheckoutCoupon.php');
require(dirname(__FILE__) . '/Interswitch/Api/InterswitchcheckoutOption.php');
require(dirname(__FILE__) . '/Interswitch/Api/InterswitchcheckoutUtil.php');
require(dirname(__FILE__) . '/Interswitch/Api/InterswitchcheckoutError.php');
require(dirname(__FILE__) . '/Interswitch/InterswitchcheckoutReturn.php');
require(dirname(__FILE__) . '/Interswitch/InterswitchcheckoutNotification.php');
require(dirname(__FILE__) . '/Interswitch/InterswitchcheckoutCharge.php');
require(dirname(__FILE__) . '/Interswitch/InterswitchcheckoutMessage.php');
