<?php

class Interswitchcheckout_Notification extends Interswitchcheckout
{

    public static function check($insMessage, $secretWord, $format='json')
    {
        $hashSid = $insMessage['vendor_id'];
        $hashOrder = $insMessage['sale_id'];
        $hashInvoice = $insMessage['invoice_id'];
        $StringToHash = strtoupper(md5($hashOrder . $hashSid . $hashInvoice . $secretWord));
        if ($StringToHash != $insMessage['md5_hash']) {
            $result = Interswitchcheckout_Message::message('Fail', 'Hash Mismatch');
        } else {
            $result = Interswitchcheckout_Message::message('Success', 'Hash Matched');
        }
        return Interswitchcheckout_Util::return_resp($result, $format);
    }

}
