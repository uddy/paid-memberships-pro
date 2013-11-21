<?php

class Interswitchcheckout_Company extends Interswitchcheckout
{

    public static function retrieve($format='json')
    {
        $request = new Interswitchcheckout_Api_Requester();
        $urlSuffix = 'acct/detail_company_info';
        $result = $request->do_call($urlSuffix);
        return Interswitchcheckout_Util::return_resp($result, $format);
    }
}

class Interswitchcheckout_Contact extends Interswitchcheckout
{

    public static function retrieve($format='json')
    {
        $request = new Interswitchcheckout_Api_Requester();
        $urlSuffix = 'acct/detail_contact_info';
        $result = $request->do_call($urlSuffix);
        return Interswitchcheckout_Util::return_resp($result, $format);
    }
}