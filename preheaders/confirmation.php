<?php
	global $current_user, $pmpro_invoice;
	
	//must be logged in
	if(empty($current_user->ID) || (empty($current_user->membership_level->ID) && pmpro_getOption("gateway") != "paypalstandard" && pmpro_getOption("gateway") != "twocheckout" && pmpro_getOption("gateway") != "interswitch"))
		wp_redirect(home_url());
	
	//if membership is a paying one, get invoice from DB
	if(!empty($current_user->membership_level) && !pmpro_isLevelFree($current_user->membership_level))
	{
		if(pmpro_getOption("gateway") == "interswitch")
		{
		
			if( isset($_POST['txnref']) )
			{	
				$pmpro_invoice = new MemberOrder();
				$pmpro_invoice->getMemberOrderByCode($_POST['txnref']);
				if(empty($pmpro_invoice->payment_transaction_id))
				$pmpro_invoice->payment_transaction_id = $_POST['retRef'];			
			
				
				
				$postamount;
				$tco_args = array('product_id' => pmpro_getOption("Interswitchcheckout_product_id"));
				
				global $wpdb;
				$Mamount = $wpdb->get_var("SELECT initial_payment FROM $wpdb->pmpro_membership_levels WHERE id = '" . esc_sql($pmpro_invoice->membership_id) . "' LIMIT 1");
				$pmpro_invoice->saveOrder();
				
				if(is_numeric( $Mamount ) && floor( $Mamount) != $Mamount)
				{
					
					$tco_args['amount'] = $Mamount;
					$removepoints = explode('.', $tco_args['amount']);
					$tco_args['amount'] =$removepoints[0].$removepoints[1];
				}else{
					$tco_args['amount'] = $Mamount;
					$tco_args['amount'] = $tco_args['amount'].'00';
				}
				$InterswitchProduct_id = pmpro_getOption("Interswitchcheckout_product_id");
				$Interswitchmac_key = pmpro_getOption("Interswitchcheckout_mac_key");
				$hash_string = $product_id.$_POST['txnref'].$Interswitchmac_key;

				$hash = hash('sha512', $hash_string);
				$url = "http://webpay.interswitchng.com/paydirect/api/v1/gettransaction.json?";
				$environment = pmpro_getOption("gateway_environment");
				if("sandbox" === $environment || "beta-sandbox" === $environment)
				{
				$url = "https://stageserv.interswitchng.com/test_paydirect/api/v1/gettransaction.json?";
				}
				
				$url=$url.'productid='$InterswitchProduct_id.'&transactionreference='.$_POST['txnref'].'&amount='.$tco_args['amount'];

				$session = curl_init($url);

				curl_setopt($session,CURLOPT_HTTPHEADER,array("Hash: $hash", "UserAgent: Mozilla/4.0 compatible; MSIE 6.0; MS Web Services Client Protocol 4.0.30319.239"))

				curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

				$response = curl_exec($session);

				curl_close($session);
				 if($response)
				 {    

					 $response_array = json_decode($response,true); 

				if($response_array['ResponseCode'] == "00"){

				// This indicates that the transaction is successful. Update necessary tables.
					$pmpro_invoice->updateStatus("success");
				}

				else
				{
					$pmpro_invoice->updateStatus("failed");
				//Transaction failed, you can get the response description to display to the user from $response_array['ResponseDescription'].

				//and the response code from $response_array['ResponseCode'].

				} 

				} 
				
				 
			}
		}
		
		
		
		$pmpro_invoice = new MemberOrder();
		$pmpro_invoice->getLastMemberOrder($current_user->ID, apply_filters("pmpro_confirmation_order_status", array("success", "pending")));
		
		


	}