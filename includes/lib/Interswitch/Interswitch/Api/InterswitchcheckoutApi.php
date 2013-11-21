<?php

class Interswitchcheckout_Api_Requester
{
    public $apiBaseUrl;
    private $product_id;
    private $mac_key;

	function __construct() {
            $this->product_id = Interswitchcheckout::$product_id;
            $this->mac_key = Interswitchcheckout::$mac_key;
            $this->apiBaseUrl = Interswitchcheckout::$apiBaseUrl;
    }

	function do_call($urlSuffix, $data=array())
    {   
		$hash_string = $product_id.$data["txnref"].$mac_key;
		$hash = hash('sha512', $hash_string);
		
		
        $url = $this->apiBaseUrl . $urlSuffix.'productid='.$product_id.'&transactionreference='.$data["txn_ref"].'&amount='.$data["amount"];
        $ch = curl_init($url);
        curl_setopt($ch,CURLOPT_HTTPHEADER,array("Hash: $hash", "UserAgent: Mozilla/4.0 compatible; MSIE 6.0; MS Web Services Client Protocol 4.0.30319.239"));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
        $resp = curl_exec($ch);
        curl_close($ch);
		return $resp;
	}

}
