<?php	
	require_once(dirname(__FILE__) . "/class.pmprogateway.php");
	class PMProGateway_gtpay
	{
		function PMProGateway_gtpay($gateway = NULL)
		{
			$this->gateway = $gateway;
			return $this->gateway;
		}										
		
		function process(&$order)
		{						
			if(empty($order->code))
				$order->code = $order->getRandomCode();			
			
			//clean up a couple values
			$order->payment_type = "GT WebPay";
			$order->CardType = "";
			$order->cardtype = "";
			
			if(floatval($order->InitialPayment) == 0)
			{
				//auth first, then process
				if($this->authorize($order))
				{						
					$this->void($order);										
					if(!pmpro_isLevelTrial($order->membership_level))
					{
						//subscription will start today with a 1 period trial
						$order->ProfileStartDate = date("Y-m-d") . "T0:0:0";
						$order->TrialBillingPeriod = $order->BillingPeriod;
						$order->TrialBillingFrequency = $order->BillingFrequency;													
						$order->TrialBillingCycles = 1;
						$order->TrialAmount = 0;
						
						//add a billing cycle to make up for the trial, if applicable
						if(!empty($order->TotalBillingCycles))
							$order->TotalBillingCycles++;
					}
					elseif($order->InitialPayment == 0 && $order->TrialAmount == 0)
					{
						//it has a trial, but the amount is the same as the initial payment, so we can squeeze it in there
						$order->ProfileStartDate = date("Y-m-d") . "T0:0:0";														
						$order->TrialBillingCycles++;
						
						//add a billing cycle to make up for the trial, if applicable
						if($order->TotalBillingCycles)
							$order->TotalBillingCycles++;
					}
					else
					{
						//add a period to the start date to account for the initial payment
						$order->ProfileStartDate = date("Y-m-d", strtotime("+ " . $order->BillingFrequency . " " . $order->BillingPeriod)) . "T0:0:0";				
					}
					
					$order->ProfileStartDate = apply_filters("pmpro_profile_start_date", $order->ProfileStartDate, $order);
					$order->status = "success";	//saved on checkout page																			
					$order->saveOrder();						
					return $this->subscribe($order);
				}
				else
				{
					if(empty($order->error))
						$order->error = __("Unknown error: Authorization failed.", "pmpro");
					return false;
				}
			}
			else
			{
				//charge first payment
				if($this->charge($order))
				{							
					//setup recurring billing					
					if(pmpro_isLevelRecurring($order->membership_level))
					{						
						if(!pmpro_isLevelTrial($order->membership_level))
						{
							//subscription will start today with a 1 period trial
							$order->ProfileStartDate = date("Y-m-d") . "T0:0:0";
							$order->TrialBillingPeriod = $order->BillingPeriod;
							$order->TrialBillingFrequency = $order->BillingFrequency;													
							$order->TrialBillingCycles = 1;
							$order->TrialAmount = 0;
							
							//add a billing cycle to make up for the trial, if applicable
							if(!empty($order->TotalBillingCycles))
								$order->TotalBillingCycles++;
						}
						elseif($order->InitialPayment == 0 && $order->TrialAmount == 0)
						{
							//it has a trial, but the amount is the same as the initial payment, so we can squeeze it in there
							$order->ProfileStartDate = date("Y-m-d") . "T0:0:0";														
							$order->TrialBillingCycles++;
							
							//add a billing cycle to make up for the trial, if applicable
							if(!empty($order->TotalBillingCycles))
								$order->TotalBillingCycles++;
						}
						else
						{
							//add a period to the start date to account for the initial payment
							$order->ProfileStartDate = date("Y-m-d", strtotime("+ " . $this->BillingFrequency . " " . $this->BillingPeriod)) . "T0:0:0";				
						}
						
						$order->ProfileStartDate = apply_filters("pmpro_profile_start_date", $order->ProfileStartDate, $order);
						$order->status = "pending";	//saved on checkout page																			
						$order->saveOrder();						
						return true;
													
						
					}
					else
					{
						//only a one time charge
						$order->status = "pending";	//saved on checkout page																			
						$order->saveOrder();						
						return true;
					}
				}
				else
				{
					if(empty($order->error))
						$order->error = __("Unknown error: Payment failed.", "pmpro");
					
					return false;
				}	
			
			}
		}
		
		function authorize(&$order)
		{
			//create a code for the order
			if(empty($order->code))
				$order->code = $order->getRandomCode();
			
			//simulate a successful authorization
			
			$order->updateStatus("authorized");													
			return true;					
		}
		
		function void(&$order)
		{
			//need a transaction id
			if(empty($order->payment_transaction_id))
				return false;
				
			//simulate a successful void
			
			$order->updateStatus("voided");					
			return true;
		}	
		
		function charge(&$order)
		{
			//create a code for the order
			if(empty($order->code))
				$order->code = $order->getRandomCode();
			
			//simulate a successful charge
			
			//$order->payment_transaction_id = "txn-" . $order->code;
			
			$order->updateStatus("pending");					
			return true;						
		}
		
		function subscribe(&$order)
		{
			//create a code for the order
			if(empty($order->code))
				$order->code = $order->getRandomCode();
			
			//filter order before subscription. use with care.
			$order = apply_filters("pmpro_subscribe_order", $order, $this);
			
			//simulate a successful subscription processing
			
			$order->status = "success";		
			$order->subscription_transaction_id = "txn-" . $order->code;
			$order->updateStatus("success");
			return true;
		}	
		function paymentsuccessfull(&$order,$paymentref,$msg)
		{
			//create a code for the order
			if(empty($order->code))
				$order->code = $order->getRandomCode();
			
			//filter order before subscription. use with care.
			$order = apply_filters("pmpro_subscribe_order", $order, $this);
			
			//simulate a successful subscription processing
			global $wpdb;
			
		 $wpdb->query("Update $wpdb->pmpro_membership_orders  SET payment_transaction_id ='".esc_sql($paymentref)."',subscription_transaction_id='".esc_sql("txn-" . $order->code)."',notes='".esc_sql($msg)."'	 WHERE code= '" . esc_sql($order->code) . "'");
			
			$order->updateStatus("success");
			return true;
		}
		function paymentfailed(&$order,$paymentref,$msg)
		{
			//create a code for the order
			if(empty($order->code))
				$order->code = $order->getRandomCode();
			
			
			//simulate a successful subscription processing
			global $wpdb;
			
		 $wpdb->query("Update $wpdb->pmpro_membership_orders  SET payment_transaction_id ='".esc_sql($paymentref)."',subscription_transaction_id='".esc_sql("txn-" . $order->code)."',notes='".esc_sql($msg)."'	 WHERE code= '" . esc_sql($order->code) . "'");
			
			$order->updateStatus("voided");
			return true;
		}
		function update(&$order)
		{
			//simulate a successful billing update
			return true;
		}
		
		function cancel(&$order)
		{
			//require a subscription id
			if(empty($order->subscription_transaction_id))
				return false;
			
			//simulate a successful cancel			
			$order->updateStatus("cancelled");					
			return true;
		}
		
		function sendToWebPay(&$order)
		{						
			global $pmpro_currency;	
			$gtpay_interswitch_merchantid =pmpro_getOption("gtpay_interswitch_merchantid");
			$gtpay_merchantid =	pmpro_getOption("gtpay_merchantid");
			$gtpay_post_url =	pmpro_getOption("gtpay_post_url");
			$gtpay_return_url =	pmpro_getOption("gtpay_return_url");
			$gtpay_tranx_curr =	pmpro_getOption("gtpay_tranx_curr");
			$gtpay_gway_first =	pmpro_getOption("gtpay_gway_first");
			$gtpay_gway_name =	pmpro_getOption("gtpay_gway_name");
	
			//taxes on initial amount
			$initial_payment = $order->InitialPayment;
			$initial_payment_tax = $order->getTaxForPrice($initial_payment);
			$initial_payment = round((float)$initial_payment + (float)$initial_payment_tax, 2);
			
			//taxes on the amount
			$amount = $order->PaymentAmount;
			$amount_tax = $order->getTaxForPrice($amount);						
			$order->subtotal = $amount;
			$amount = round((float)$amount + (float)$amount_tax, 2);			
			
			//build PayPal Redirect	
			$environment = pmpro_getOption("gateway_environment");
			if("sandbox" === $environment || "beta-sandbox" === $environment)
				$gtpay_url =  $gtpay_post_url;    //"https://www.sandbox.paypal.com/cgi-bin/webscr?business=" . urlencode(pmpro_getOption("gateway_email"));
			else
				$gtpay_url =$gtpay_post_url;// "https://www.paypal.com/cgi-bin/webscr?business=" . urlencode(pmpro_getOption("gateway_email"));
			
			
				$gtpay_currency = $gtpay_tranx_curr;
						
				 $amt = $order->total;//number_format($order->total, 2);
				$formatAmt =strval($amt);
				$amtarry = explode('.', $formatAmt);
				$gtpay_tranx_amt = $amtarry[0].$amtarry[1];
				$cust_id = uniqid();
				//other args
				$gtpay_args = array( 
					'gtpay_mert_id'         => $gtpay_merchantid, 
					'gtpay_tranx_id'		=> $order->code,         
					'gtpay_tranx_amt'		=> $gtpay_tranx_amt."00",
					'gtpay_tranx_curr'		=> $gtpay_currency,
					'gtpay_gway_first'      => $gtpay_gway_first,
					'gtpay_cust_id'			=> $cust_id,
					'gtpay_cust_name'		=> $order->Email,
					'gtpay_tranx_memo'		=> substr("NDLink Membership Level: ".$order->membership_level->name . " at " . get_bloginfo("name"), 0, 127),
					'gtpay_gway_name'     	=> $gtpay_gway_name,					
					'gtpay_tranx_noti_url'  => $gtpay_return_url.$order->membership_level->membership_id.'&status=done', 
					'gtpay_echo_data'       => $order->code
					
				);					
								
				
			
			
			global $pmpro_currency_symbol;
			echo '<form id="2checkout" action="'.$gtpay_post_url.'" method="post">';
			echo '<p>Note: your transaction reference is:<strong>'.$order->code.'</strong></p>';
			echo '<p>(Quote it in event of any problems.)</p>';
			echo '<p>Payment For:'.substr("NDLink Membership Level <strong>[".$order->membership_level->name . "] ", 0, 127).'</strong> (valid for 1yr) </p>';
			echo '<p>Amount:<strong>'.$pmpro_currency_symbol.number_format($order->total, 2).'</strong>(valid for 1yr) </p>';
			echo '<hr/>';
			//echo '<p><em> Click "PAY NOW" or </em></p>';
			//print_r($gtpay_args);
			
			if (is_array($gtpay_args))
			{

				foreach ($gtpay_args as $key => $value)
				{
					echo '<input type="hidden" name="'.$key.'" value="'.$value.'"/>';
				}
			}

		   
			echo '<input type="submit" value="Pay Now" class="btn btn-primary" />   <input type="button" value="Back" class="btn pull-right" onclick="BackToCheckout()" /></form>';
			 // echo '<script type="text/javascript">function submitForm() {document.getElementById("2checkout").submit();}
					   // setTimeout("submitForm()", 10000);
				   // </script>';
   
		}
		
	}
?>
