<?php 
	global $wpdb, $current_user, $pmpro_invoice, $pmpro_msg, $pmpro_msgt, $pmpro_currency_symbol,$display_invoice,$gtpay_error,$gtpay_tranx_status_msg;
	$payment_transaction_id ="n/a";
	$msg_returened ="n/a";
	if(pmpro_getOption("gateway") == "gtpay" && !pmpro_isLevelFree($pmpro_invoice->membership_level))
		{
			if(isset($_REQUEST['status']) && isset($pmpro_invoice))
			{
				
				$mode = pmpro_getOption("gateway_environment");		
				$ordercode =$pmpro_invoice->code;
				$txnamount=$pmpro_invoice->total;
				
				
				$decode = gtpay_get_transaction_status($ordercode,$txnamount,$mode );
				//var_dump($decode);
				
				$return=$decode["ResponseCode"];
				$data= "";
				if($return == "00" || $return == "001")
				{	$pmpro_invoice->payment_transaction_id =$decode["PaymentReference"];
					$payment_transaction_id=$decode["PaymentReference"];
					$display_invoice = true;	
					$data.= '<p>Note: your transaction reference is:<strong>'.$decode["MerchantReference"].'</strong> </p>';
					$data.= '<p>Payment Reference:'.$decode["PaymentReference"].'</p>';
					$data.=  '<p><strong>Transaction Message</strong></p>';
					$data.=  '<hr/>';
					$data.=  '<p><em style="color:green">'.$decode["ResponseDescription"].'</em></p>';
					$msg_returened = $decode["ResponseDescription"]." | Code:".$return." | Approved Amount:".$decode["Amount"]." | Card Ending:...".$decode["CardNumber"];
				}else
					{
						$display_invoice = false;
						$data.= '<p>Note: your transaction reference is:<strong>'.$pmpro_invoice->code.'</strong> </p>';
						$data.= '<p>Payment Reference:'.$decode["PaymentReference"].'</p>';
						$data.=  '<p><strong>Transaction Message</strong></p>';
						$data.=  '<hr/>';
						$data.=  '<p><em style="color:red"> Transaction Failed:'.$decode["ResponseDescription"].'</em></p>';
						$pmpro_invoice->payment_transaction_id =$decode["PaymentReference"];
						$payment_transaction_id=$decode["PaymentReference"];
						$gtpay_error = "show-error";
						$msg_returened = $decode["ResponseDescription"]." | Code:".$return." | Approved Amount:".$decode["Amount"]." | Card Ending:...".$decode["CardNumber"];
						
					}	
							$gtpay_tranx_status_msg =$data;
							
			}else
			{
				$gtpay_error = "show-paynow";
			}
		}
	
	
	
	
	if($display_invoice){
	echo $gtpay_tranx_status_msg;
	if($pmpro_msg)
	{
	?>
		<div class="pmpro_message <?php echo $pmpro_msgt?>"><?php echo $pmpro_msg?></div>
	<?php
	}
	
	if(empty($current_user->membership_level))
		$confirmation_message = "<p>" . __('Your payment has been submitted. Your membership will be activated shortly.', 'pmpro') . "</p>";
	else
		$confirmation_message = "<p>" . sprintf(__('Thank you for your membership to %s. Your %s membership is now active.', 'pmpro'), get_bloginfo("name"), $current_user->membership_level->name) . "</p>";		
	
	//confirmation message for this level
	$level_message = $wpdb->get_var("SELECT l.confirmation FROM $wpdb->pmpro_membership_levels l LEFT JOIN $wpdb->pmpro_memberships_users mu ON l.id = mu.membership_id WHERE mu.status = 'active' AND mu.user_id = '" . $current_user->ID . "' LIMIT 1");
	if(!empty($level_message))
		$confirmation_message .= "\n" . stripslashes($level_message) . "\n";
?>	

<?php if(!empty($pmpro_invoice)) { ?>		
	
	<?php
		$pmpro_invoice->getUser();
		$pmpro_invoice->getMembershipLevel();			
				
		$confirmation_message .= "<p>" . sprintf(__('Below are details about your membership account and a receipt for your initial membership invoice. A welcome email with a copy of your initial membership invoice has been sent to %s.', 'pmpro'), $pmpro_invoice->user->user_email) . "</p>";
		
		//check instructions		
		if($pmpro_invoice->gateway == "check" && !pmpro_isLevelFree($pmpro_invoice->membership_level))
			$confirmation_message .= wpautop(pmpro_getOption("instructions"));
		
		$confirmation_message = apply_filters("pmpro_confirmation_message", $confirmation_message, $pmpro_invoice);				
		
		echo apply_filters("the_content", $confirmation_message);		
	?>
	
	
	<h3>
		<?php printf(_x('Invoice #%s on %s', 'Invoice # header. E.g. Invoice #ABCDEF on 2013-01-01.', 'pmpro'), $pmpro_invoice->code, date(get_option('date_format'), $pmpro_invoice->timestamp));?>		
	</h3>
	<a class="pmpro_a-print" href="javascript:window.print()"><?php _e('Print', 'pmpro');?></a>
	<ul>
		<?php do_action("pmpro_invoice_bullets_top", $pmpro_invoice); ?>
		<li><strong><?php _e('Account', 'pmpro');?>:</strong> <?php echo $pmpro_invoice->user->display_name?> (<?php echo $pmpro_invoice->user->user_email?>)</li>
		<li><strong><?php _e('Membership Level', 'pmpro');?>:</strong> <?php echo $current_user->membership_level->name?></li>
		<?php if($current_user->membership_level->enddate) { ?>
			<li><strong><?php _e('Membership Expires', 'pmpro');?>:</strong> <?php echo date(get_option('date_format'), $current_user->membership_level->enddate)?></li>
		<?php } ?>
		<?php if($pmpro_invoice->getDiscountCode()) { ?>
			<li><strong><?php _e('Discount Code', 'pmpro');?>:</strong> <?php echo $pmpro_invoice->discount_code->code?></li>
		<?php } ?>
		<?php do_action("pmpro_invoice_bullets_bottom", $pmpro_invoice); ?>
	</ul>
	
	<table id="pmpro_confirmation_table" class="pmpro_invoice" width="100%" cellpadding="0" cellspacing="0" border="0">
		<thead>
			<tr>
				<?php if(!empty($pmpro_invoice->billing->name)) { ?>
				<th><?php _e('Billing Address', 'pmpro');?></th>
				<?php } ?>
				<th><?php _e('Payment Method', 'pmpro');?></th>
				<th><?php _e('Membership Level', 'pmpro');?></th>
				<th><?php _e('Total Billed', 'pmpro');?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<?php if(!empty($pmpro_invoice->billing->name)) { ?>
				<td>
					<?php echo $pmpro_invoice->billing->name?><br />
					<?php echo $pmpro_invoice->billing->street?><br />						
					<?php if($pmpro_invoice->billing->city && $pmpro_invoice->billing->state) { ?>
						<?php echo $pmpro_invoice->billing->city?>, <?php echo $pmpro_invoice->billing->state?> <?php echo $pmpro_invoice->billing->zip?> <?php echo $pmpro_invoice->billing->country?><br />												
					<?php } ?>
					<?php echo formatPhone($pmpro_invoice->billing->phone)?>
				</td>
				<?php } ?>
				<td>
					<?php if($pmpro_invoice->accountnumber) { ?>
						<?php echo $pmpro_invoice->cardtype?> <?php _e('ending in', 'credit card type {ending in} xxxx', 'pmpro');?> <?php echo last4($pmpro_invoice->accountnumber)?><br />
						<small><?php _e('Expiration', 'pmpro');?>: <?php echo $pmpro_invoice->expirationmonth?>/<?php echo $pmpro_invoice->expirationyear?></small>
					<?php } elseif($pmpro_invoice->payment_type) { ?>
						<?php echo $pmpro_invoice->payment_type?>
					<?php } ?>
				</td>
				<td><?php echo $pmpro_invoice->membership_level->name?></td>					
				<td><?php if($pmpro_invoice->total) echo $pmpro_currency_symbol . number_format($pmpro_invoice->total, 2); else echo "---";?></td>
			</tr>
		</tbody>
	</table>		
<?php 
	} 
	else 
	{
		$confirmation_message .= "<p>" . sprintf(__('Below are details about your membership account. A welcome email with has been sent to %s.', 'pmpro'), $current_user->user_email) . "</p>";
		
		$confirmation_message = apply_filters("pmpro_confirmation_message", $confirmation_message, false);
		
		echo $confirmation_message;
	?>	
	<ul>
		<li><strong><?php _e('Account', 'pmpro');?>:</strong> <?php echo $current_user->display_name?> (<?php echo $current_user->user_email?>)</li>
		<li><strong><?php _e('Membership Level', 'pmpro');?>:</strong> <?php if(!empty($current_user->membership_level)) echo $current_user->membership_level->name; else _ex("Pending", "User without membership is in {pending} status.", "pmpro");?></li>
	</ul>	
<?php 
	} 
?>  
<nav id="nav-below" class="navigation" role="navigation">
	<div class="nav-next alignright">
		<?php if(!empty($current_user->membership_level)) { ?>
			<a href="<?php echo pmpro_url("account")?>"><?php _e('View Your Membership Account &rarr;', 'pmpro');?></a>
		<?php } else { ?>
			<?php _e('If your account is not activated within a few minutes, please contact the site owner.', 'pmpro');?>
		<?php } ?>
	</div>
</nav>
<?php 

						//echo $gtpay_tranx_status_msg;
						$pmpro_invoice->getUser();
						$pmpro_invoice->getMembershipLevel();
						$pmpro_invoice->Gateway->paymentsuccessfull($pmpro_invoice,$payment_transaction_id,$msg_returened);
						pmpro_changeMembershipLevel($pmpro_invoice->membership_id, $pmpro_invoice->user->ID);
} else{
if($gtpay_error === "show-error")
{
			echo $gtpay_tranx_status_msg;
			
			//$pmpro_invoice->Gateway->void($pmpro_invoice);
			$pmpro_invoice->Gateway->paymentfailed($pmpro_invoice,$payment_transaction_id,$msg_returened);
			pmpro_changeMembershipLevel(0, $pmpro_invoice->user->ID); //need to remove from order membership level 	

}
if($gtpay_error === "show-paynow")
{
		$pmpro_invoice->getUser();
		$pmpro_invoice->getMembershipLevel();
		$pmpro_invoice->Gateway->sendToWebPay($pmpro_invoice);
		
		}


}
 ?>
 <?php if($gateway == "gtpay"){ ?>
			<img src="http://ndlink.org/wp-content/uploads/2013/12/interswitch-webpay-logo.jpg" alt=""/>
			<?php } ?>
