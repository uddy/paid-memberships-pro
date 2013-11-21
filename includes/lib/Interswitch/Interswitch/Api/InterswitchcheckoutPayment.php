<?php

class Interswitchcheckout_Payment extends Interswitchcheckout
{

    public static function retrieve($format='json',$data=array())
    {
        $request = new Interswitchcheckout_Api_Requester();
        $urlSuffix = '/test_paydirect/api/v1/gettransaction.json?';
        $result = $request->do_call($urlSuffix,$data);
        $response = Interswitchcheckout_Util::return_resp($result, $format);
        return $response;
    }

        public static function pending($format='json')
    {
        $request = new Interswitchcheckout_Api_Requester();
        $urlSuffix = 'acct/detail_pending_payment';
        $result = $request->do_call($urlSuffix);
        $response = Interswitchcheckout_Util::return_resp($result, $format);
        return $response;
    }

}