<?php	
	require_once(dirname(__FILE__) . "/class.pmprogateway.php");
	if(!class_exists("Interswitchcheckout"))
		require_once(dirname(__FILE__) . "/../../includes/lib/Interswitch/Interswitch.php");
	class PMProGateway_Interswitchcheckout
	{
		function PMProGateway_Interswitchcheckout($gateway = NULL)
		{
			$this->gateway = $gateway;
			return $this->gateway;
		}										
		
		function process(&$order)
		{						
			if(empty($order->code))
				$order->code = $order->getRandomCode();			
			
			//clean up a couple values
			$order->payment_type = "Interswitch";
			$order->CardType = "";
			$order->cardtype = "";
			
			//just save, the user will go to PayPal to pay
			$order->status = "review";														
			$order->saveOrder();

			return true;			
		}
		
		function sendToInterswitchcheckout(&$order)
		{						
			global $pmpro_currency;	

			process(&$order);
			// Set up credentialssetCredentials($product_id,$pay_item_id,$mac_key,$site_redirect_url)
			Interswitchcheckout::setCredentials( pmpro_getOption("Interswitchcheckout_product_id"), pmpro_getOption("Interswitchcheckout_pay_item_id"),pmpro_getOption("Interswitchcheckout_mac_key"),pmpro_getOption("Interswitchcheckout_site_redirect_url"));

			$tco_args = array(
				'product_id' => pmpro_getOption("Interswitchcheckout_product_id"),
				'pay_item_id' => pmpro_getOption("Interswitchcheckout_pay_item_id"),
				'txn_ref' => $order->code,
				'currency' =>$pmpro_currency,
				'site_redirect_url' =>pmpro_url("confirmation", "?level=" . $order->membership_level->id)
			);
			$paymentAmount;
			//taxes on initial amount
			$initial_payment = $order->InitialPayment;
			$initial_payment_tax = $order->getTaxForPrice($initial_payment);
			$initial_payment = round((float)$initial_payment + (float)$initial_payment_tax, 2);
			$paymentAmount = $initial_payment;
			// Recurring membership
			if( pmpro_isLevelRecurring( $order->membership_level ) ) {
				//$tco_args['li_0_startup_fee'] = $initial_payment;

				$recurring_payment = $order->membership_level->billing_amount;
				$recurring_payment_tax = $order->getTaxForPrice($recurring_payment);
				$recurring_payment = round((float)$recurring_payment + (float)$recurring_payment_tax, 2);
				$paymentAmount = $recurring_payment;

				//$tco_args['li_0_recurrance'] = ( $order->BillingFrequency == 1 ) ? $order->BillingFrequency . ' ' . $order->BillingPeriod : $order->BillingFrequency . ' ' . $order->BillingPeriod . 's';

				//if( property_exists( $order, 'TotalBillingCycles' ) )
				//	$tco_args['li_0_duration'] = ($order->BillingFrequency * $order->TotalBillingCycles ) . ' ' . $order->BillingPeriod;
			}
			// Non-recurring membership
			else {
				$tco_args['amount'] = $initial_payment;
			}

			// Demo mode?
			$environment = pmpro_getOption("gateway_environment");
			if("sandbox" === $environment || "beta-sandbox" === $environment)
				//$tco_args['demo'] = 'Y';

			//print_r( $tco_args );
			//print_r( $order );

			// Trial?
			//li_#_startup_fee	Any start up fees for the product or service. Can be negative to provide discounted first installment pricing, but cannot equal or surpass the product price.

			// Coupon?
			//coupon	Specify a 2Checkout created coupon code. If applicable, the coupon will be automatically applied to the sale.

			//taxes on the amount (NOT CURRENTLY USED)
			//$amount = $order->PaymentAmount;
			//$amount_tax = $order->getTaxForPrice($amount);						
			//$order->subtotal = $amount;
			//$amount = round((float)$amount + (float)$amount_tax, 2);			
			//$paymentAmount= $amount;
			if(is_decimal($paymentAmount))
			{
				$tco_args['amount'] = $paymentAmount;
				$removepoints = explode('.', $tco_args['amount']);
				$tco_args['amount'] =$removepoints[0].$removepoints[1];
			}else{
				$tco_args['amount'] = $paymentAmount;
				$tco_args['amount'] = $tco_args['amount'].'00';
			}
			
			Interswitchcheckout::form($tco_args);
			
			
			//$ptpStr = '';
			//foreach( $tco_args as $key => $value ) {
			//	reset( $tco_args ); // Used to verify whether or not we're on the first argument
			//	$ptpStr .= ( $key == key($tco_args) ) ? '?' . $key . '=' . urlencode( $value ) : '&' . $key . '=' . urlencode( $value );
			//}

			//anything modders might add
			//$additional_parameters = apply_filters( 'pmpro_Interswitchcheckout_return_url_parameters', array() );									
			//if( ! empty( $additional_parameters ) )
			//	foreach( $additional_parameters as $key => $value )				
			//		$ptpStr .= urlencode( "&" . $key . "=" . $value );

			//$ptpStr = apply_filters( 'pmpro_Interswitchcheckout_ptpstr', $ptpStr, $order );
			//echo "<br /><br />".$ptpStr. "<br /><br />";
			
			//redirect to paypal			
			//$tco_url = 'https://www.2checkout.com/checkout/purchase' . $ptpStr;
			
			//echo $tco_url;
			//die();
			//wp_redirect( $tco_url );
			//exit;
			
			
		}

		function cancel(&$order) {
		}
		function is_decimal( $val ){return is_numeric( $val ) && floor( $val ) != $val;}
	}
?>
