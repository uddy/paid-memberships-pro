<?php

class Interswitchcheckout_Return extends Interswitchcheckout
{

    public static function check($params=array(), $secretWord, $format='json')
    {
        $hashSecretWord = $secretWord;
        $hashSid = $params['sid'];
        $hashTotal = $params['total'];
        $hashOrder = $params['order_number'];
        $StringToHash = strtoupper(md5($hashSecretWord . $hashSid . $hashOrder . $hashTotal));
        if ($StringToHash != $params['key']) {
            $result = Interswitchcheckout_Message::message('Fail', 'Hash Mismatch');
        } else {
            $result = Interswitchcheckout_Message::message('Success', 'Hash Matched');
        }
        return Interswitchcheckout_Util::return_resp($result, $format);
    }

}