<?php

class Interswitchcheckout_Charge extends Interswitchcheckout
{

    public static function form($params, $type='Checkout')
    {
		$Interswitchpayurl ="https://stageserv.interswitchng.com/test_paydirect/pay";
		$InterswitchMACkey ="AC43543FA32234HB23423AFH843535";
		
		$payhash = $params["tnx_ref"].$params["product_id"].$params["pay_item_id"].$params["amount"].$params["site_redirect_url"].$params["mac_key"];
		
		
        echo '<form id="2checkout" action="'.$Interswitchpayurl.'" method="post">';

        foreach ($params as $key => $value)
        {
			if($key != "mac_key"){
            echo '<input type="hidden" name="'.$key.'" value="'.$value.'"/>';
			}
			
        }		
		
		 echo '<input type="hidden" name="hash" value="'.hash('sha512',$payhash).'"/>';
        if ($type == 'auto') {
            echo '<input type="submit" value="Click here if you are not redirected automatically" /></form>';
            echo '<script type="text/javascript">document.getElementById("2checkout").submit();</script>';
        } else {
            echo '<input type="submit" value="'.$type.'" />';
            echo '</form>';
        }
    }

  //s  public static function direct($params, $type='Checkout')
   // {
    //    echo '<form id="2checkout" action="https://www.2checkout.com/checkout/purchase" method="post">';
//
   //     foreach ($params as $key => $value)
   //     {
//            echo '<input type="hidden" name="'.$key.'" value="'.$value.'"/>';
   //     }
//
     //   if ($type == 'auto') {
    //        echo '<input type="submit" value="Click here if the payment form does not open automatically." /></form>';
    //        echo '<script type="text/javascript">
    //                function submitForm() {
   //                     document.getElementById("tco_lightbox").style.display = "block";
    //                    document.getElementById("2checkout").submit();
    //                }
    //                setTimeout("submitForm()", 2000);
    //              </script>';
   //     } else {
   ///         echo '<input type="submit" value="'.$type.'" />';
   //         echo '</form>';
   //     }
//
   //     echo '<script src="https://www.2checkout.com/static/checkout/javascript/direct.min.js"></script>';
   // }

   // public static function link($params)
   // {
   //     $url = 'https://www.2checkout.com/checkout/purchase?'.http_build_query($params, '', '&amp;');
   //     return $url;
   // }

   // public static function redirect($params)
   // {
    //    $url = 'https://www.2checkout.com/checkout/purchase?'.http_build_query($params, '', '&amp;');
    //    header("Location: $url");
   // }

}
