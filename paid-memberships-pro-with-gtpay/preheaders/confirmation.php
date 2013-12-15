<?php
	global $current_user, $pmpro_invoice;
	
	//must be logged in
	if(empty($current_user->ID) || (empty($current_user->membership_level->ID) && pmpro_getOption("gateway") != "paypalstandard" && pmpro_getOption("gateway") != "twocheckout") && pmpro_getOption("gateway") != "gtpay")
		wp_redirect(home_url());
	
	//if membership is a paying one, get invoice from DB
	if(!empty($current_user->membership_level) && !pmpro_isLevelFree($current_user->membership_level))
	{
		$pmpro_invoice = new MemberOrder();
		$pmpro_invoice->getLastMemberOrder($current_user->ID, apply_filters("pmpro_confirmation_order_status", array("success", "pending","review")));
		
		if(pmpro_getOption("gateway") == "gtpay" && !pmpro_isLevelFree($pmpro_invoice->membership_level))
		{
			if( !isset($_POST['gtpay_tranx_status_code']) && isset($pmpro_invoice))
			{
				$pmpro_invoice->Gateway->sendToWebPay($pmpro_invoice);
			}else
			{
			$pmpro_invoice->Gateway->authorize($pmpro_invoice);
			$pmpro_invoice->Gateway->subscribe($pmpro_invoice);
			}
		}
	}