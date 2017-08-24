<?php
/*
 * @package com_gscrm
 * @copyright (c)2017 Pedro L Bicudo Maschio / bicudomaschio@gmail.com
 * @license GNU General Public License version 2 or later
 */

namespace Gs\Gscrm\Site\View\Nav_orders; 

// navigation buttons used in forms

use \JText;
use \JFactory;
use \JSession;
use \Gs\Gscrm\Admin\Helper\Helper;

// no direct access
defined('_JEXEC') or die('Restricted access');

//get menu item ids 
global $menu_item;

//set navigation id 
$item_id = $model->gscrm_order_id;
$account = $model->account;
$contract = $model->contract;
$invoice = $model->invoice;

	if($item_id < 1)
	{
		//id is zero if this is task=add; but if redirected with parsed data we neet to set it in this form add
		$jinput = \JFactory::getApplication()->input;
		$account = $jinput->get('account', null, 'int');
		$contract = $jinput->get('contract', null, 'int');
		$invoice = $jinput->get('invoice', null, 'int');		 
	}
//get account data
$acc_data = Helper::GetRowArray('#__gscrm_accounts', 'gscrm_account_id', $account);
if($acc_data)
	{
		if($acc_data['company'] > 0)
				{ 
					//$rel_comp = related company
					$rel_comp = Helper::GetRowArray('#__gscrm_accounts', 'gscrm_account_id', $acc_data['company']); 
					$address = Helper::ShowAddresses($acc_data['company']);
					
					if($address){
					$on_st = $address[0]['number'].'|'.$address[0]['street'].'|'.$address[0]['additional']
							.'|'.$address[0]['city'].'|'.$address[0]['state'].'|'.$address[0]['zip'].'|'.$address[0]['country'];
					}
					//parse company name to order on save
					?>	<input type="hidden" name="bz_acc" value="<?php echo $rel_comp['title']; ?>">
						<input type="hidden" name="bz_acc_id" value="<?php echo $rel_comp['gscrm_account_id']; ?>">
						<input type="hidden" name="addr2_id" value="<?php echo $address[0]['gscrm_address_id']; ?>">
					<?php	
					} else {$rel_comp = 0;
				}
		//parse client name (individual) to contract on save
			
		?>	<input type="hidden" name="pe_acc" value="<?php echo $acc_data['title'] ?>">
			<input type="hidden" name="addr1_id" value="<?php echo $acc_data['address']; ?>"> 
		<?php	
							
		// set gravatar
		$email = $acc_data['email'] ;
		$grav_url = "https://www.gravatar.com/avatar/" . md5( strtolower( trim( $email ) ) ) . "&s=40";	
	} 
	else{
		$rel_comp = 0;
		$email = '';
		$grav_url = '';
	}
	//parse client names to order on save
	?>	<input type="hidden" name="start_save" value="2"> 
	<?php	
	
$jinput = \JFactory::getApplication()->input;
$show_bz = $jinput->get('show_bz', null, 'int');

// set navigation buttons indexes
$prev_item = Helper::PrevItem('#__gscrm_orders', 'gscrm_order_id', $item_id, 0);
if($prev_item < 1){$prev_item = $item_id;}

$next_item = Helper::NextItem('#__gscrm_orders', 'gscrm_order_id', $item_id, 0);
if($next_item < 1){$next_item = $item_id;}	

// next build the navigation bar to navigate through accounts and see the calls/contacts/issues for each account
?>
<div class="col-sm-12 pl0 pr0">
<div class="col-sm-6 pl0">
<div class="btn-group col-sm-12" role="group" aria-label="...">
	
	<button type="button" class="btn btn-default" aria-label="Left Align" title="<?php echo JText::_('GS_CANCEL') ?>" onclick="Joomla.submitbutton('cancel')" >
	<span class="glyphicon glyphicon-home" aria-hidden="true"></span>
	<?php echo JText::_('GS_LIST') ?>
	</button>
				    
	<a role="button" class="btn" title="<?php echo JText::_('GS_TIP_PREV') ?>" href="index.php?option=com_gscrm&view=Order&id=<?php echo $prev_item ?>
		&Itemid=<?php echo $menu_item['order'] ?>&<?php echo JSession::getFormToken() ?>=1" >			
	<i class="glyphicon glyphicon-chevron-left"></i><?php echo JText::_('GS_PREV') ?></a>					    
				    				    
	<button type="button" class="btn btn-success" aria-label="Left Align" title="<?php echo JText::_('GS_TIP_ADD') ?>" onclick="Joomla.submitbutton('apply')" >
	<span class="glyphicon glyphicon-save-file" aria-hidden="true"></span>
	<?php echo JText::_('GS_ADD') ?>
	</button>	
	        
	<a role="button" class="btn" title="<?php echo JText::_('GS_TIP_NEXT') ?>" href="index.php?option=com_gscrm&view=Order&id=<?php echo $next_item ?>&Itemid=<?php echo $menu_item['order'] ?>&<?php echo JSession::getFormToken() ?>=1" >
	<i class="glyphicon glyphicon-chevron-right"></i><?php echo JText::_('GS_NEXT') ?></a>		
</div>

<!-- second group: additional navigation -->
<div class="col-sm-12 mt2">	
			
		<a role="button" class="btn-xs" href="index.php?option=com_gscrm&view=Notes&account=<?php echo $account ?>&Itemid=<?php echo $menu_item['notes'] ?>&<?php echo JSession::getFormToken() ?>=1"
			 title="<?php echo ' '.JText::_('GS_TIP_SEE_NOTES') ?>">
			<i class="glyphicon glyphicon-pencil"></i><?php echo ' '.JText::_('GS_SEE_NOTES') ?></a> 
					
<!--
		<a class="btn-xs" href="index.php?option=com_gscrm&view=Contract&account=<?php echo $account ?>&Itemid=<?php echo $menu_item['contract'] ?>&<?php echo JSession::getFormToken() ?>=1" 
			title="<?php echo ' '.JText::_('COM_GSCRM_CONTRACTS_CREATE') ?>" >
			<i class="glyphicon glyphicon-briefcase"></i><?php echo ' '.JText::_('COM_GSCRM_CONTRACT_NEW') ?></a>
-->	
	
		<a class="btn-xs" href="index.php?option=com_gscrm&view=Order&Itemid=<?php echo $menu_item['order'] ?>&<?php echo JSession::getFormToken() ?>=1" 
			title="<?php echo ' '.JText::_('COM_GSCRM_ORDERS_CREATE') ?>" >
			<i class="glyphicon glyphicon-shopping-cart"></i><?php echo ' '.JText::_('COM_GSCRM_ORDER_NEW') ?></a>	
		
<!--	
		<a class="btn-xs" href="index.php?option=com_gscrm&view=Invoice&account=<?php echo $account ?>&Order=<?php echo $item_id ?>&Itemid=<?php echo $menu_item['invoice'] ?>&<?php echo JSession::getFormToken() ?>=1" 
			title="<?php echo ' '.JText::_('COM_GSCRM_INVOICES_CREATE') ?>" >
			<i class="glyphicon glyphicon-usd"></i><?php echo ' '.JText::_('COM_GSCRM_INVOICE_NEW') ?></a>				
-->
</div>
<div class="col-sm-12 mt2">
	
		<div class="btn-group mr2">
		<button type="button" class="btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		<!--  <span class="caret"></span> -->
	    <i class="glyphicon glyphicon-eye-open"></i>
		</button>
			<ul class="dropdown-menu">
				<li><a href="index.php?option=com_gscrm&view=Opportunities&account=<?php echo $account ?>&Itemid=<?php echo $menu_item['opportunities'] ?>&<?php echo JSession::getFormToken() ?>=1">
					<?php echo JText::_('GS_SEE_OPPORTUNITIES') ?></a></li>
<!--
				<li><a href="index.php?option=com_gscrm&view=Contracts&account=<?php echo $account ?>&Itemid=<?php echo $menu_item['contracts'] ?>&<?php echo JSession::getFormToken() ?>=1">
					<?php echo JText::_('GS_SEE_CONTRACTS') ?></a></li>
-->
				<li><a href="index.php?option=com_gscrm&view=Orders&account=<?php echo $account ?>&Itemid=<?php echo $menu_item['orders'] ?>&<?php echo JSession::getFormToken() ?>=1">
					<?php echo JText::_('GS_SEE_ORDERS') ?></a></li>
  			</ul>
		</div>	
	
		<span class="ml2"> <i class="glyphicon glyphicon-shopping-cart"></i><?php echo ' '.JText::_('COM_GSCRM_ORDER_NUMBER').' '.$model->number ?></span>
		<?php if( $model->number < 1) { $co_num = 1; } else { $co_num = $model->number; }?>
		<input type="hidden" name="number" id="number" value="<?php echo $co_num ?>">	

		<!-- modal editor for future use -->		
		<!-- <input type="checkbox" name="add_relation" data-toggle="modal" data-target=".bs-editor-modal-sm"> -->
		
</div>

</div>

<!-- third group: account information -->

<div class="col-sm-6 gs_border pr0">

<?php if($acc_data) { ?>

	<div class="col-sm-2" >
		
		<?php if($show_bz < 1) { ?>
		
		<a href="mailto:<?php echo $acc_data['email'] ?>">
			<img class="mt3p" src="<?php echo $grav_url ?>" align="center" alt="photo" ></a>
		<a class="font10" href="https://en.gravatar.com/support/activating-your-account" >gravatar.com</a>
		
		<a role="button" class="btn-xs" href="index.php?option=com_gscrm&view=Account&id=<?php echo $account ?>&Itemid=<?php echo $menu_item['account'] ?>&<?php echo JSession::getFormToken() ?>=1" 
			title="<?php echo JText::_('COM_GSCRM_ACCOUNTS_EDIT') ?>">
		<i class="glyphicon glyphicon-pencil"></i></a>
		
		<?php if($acc_data['company'] > 0) { ?>
		<a role="button" class="btn-xs" href="index.php?option=com_gscrm&view=Order&id=<?php echo $item_id ?>&show_bz=1&account=<?php echo $account ?>
											&Itemid=<?php echo $menu_item['order'] ?>&<?php echo JSession::getFormToken() ?>=1" 
			title="<?php echo JText::_('COM_GSCRM_SHOW_BZ') ?>">
		<i class="glyphicon glyphicon-credit-card"></i></a>
		
		<?php ;} ;} else { ?>
		
		<a href="http://<?php echo $rel_comp['email'] ?>" target="_blank">
			<img class="mt3p pics" src="https://logo.clearbit.com/<?php echo $rel_comp['email'] ?>" align="center" alt="logo"></a>
		<a class="font10" href="http://blog.clearbit.com/logo/" >clearbit.com</a> 
		
		<a role="button" class="btn-xs" href="index.php?option=com_gscrm&view=Account&id=<?php echo $rel_comp['gscrm_account_id'] ?>&Itemid=<?php echo $menu_item['account'] ?>&<?php echo JSession::getFormToken() ?>=1" 
			title="<?php echo JText::_('COM_GSCRM_ACCOUNTS_EDIT') ?>">
			<i class="glyphicon glyphicon-pencil"></i>
		</a>
		
		<a role="button" class="btn-xs" href="index.php?option=com_gscrm&view=Order&id=<?php echo $item_id ?>&show_bz=0&account=<?php echo $account ?>
											&Itemid=<?php echo $menu_item['order'] ?>&<?php echo JSession::getFormToken() ?>=1" 
			title="<?php echo JText::_('COM_GSCRM_SHOW_PE') ?>">
			<i class="glyphicon glyphicon-user"></i>
		</a>
		<?php ;} ?>						 
	</div>
	
	<div class="col-sm-10 " >
		
		<?php if($show_bz < 1) { ?>
		<div class="col-sm-12" >
			<p>	<span><?php echo $acc_data['title'].'<br>' ?></span>
				<span><?php if($rel_comp['gscrm_account_id'] !== 0){ 
							echo '<a href="index.php?option=com_gscrm&view=Account&id='.$acc_data['company'].'&Itemid='.$menu_item['account'].'&'.JSession::getFormToken().'=1" >'
							.$rel_comp['title'].'</a><br>';}  ?></span>		
				<span><?php if($acc_data['email']){ echo '<a href="mailto:'.$acc_data['email'].'">'.$acc_data['email'].'</a><br>';} ?></span>
			</p>
		</div>
		<div class="col-sm-12" >
			<div class="col-sm-6 pl0" >
				<p>
				<?php 
				if($acc_data['phone1']) {?><span><i class="glyphicon glyphicon-earphone"></i><?php echo ' '.$acc_data['phone1'] ?></span><br> <?php ;}
				
				if($acc_data['phone2']) {?><span><i class="glyphicon glyphicon-earphone"></i><?php echo ' '.$acc_data['phone2'] ?></span> <?php ;} 
				?>
				</p>
			</div>
			<div class="col-sm-6 pr0" >
				<p>
				<?php 
				if($acc_data['phone3']) {?><span><i class="glyphicon glyphicon-earphone"></i><?php echo ' '.$acc_data['phone3'] ?></span><br> <?php ;}
				
				if($acc_data['phone4']) {?><span><i class="glyphicon glyphicon-earphone"></i><?php echo ' '.$acc_data['phone4'] ?></span> <?php ;} 
				?>
				</p>								
			</div
		</div>
		
		<?php }else{ ?>
		<div class="col-sm-12" >
			<p>	
				<?php 	
					if($rel_comp['title']) { ?><span><?php echo $rel_comp['title'].'<br>' ?></span><?php ;}
					if($rel_comp['unique_id']) { ?><span><?php echo JText::_('GS_UID').': '.$rel_comp['unique_id'].'<br>' ?></span><?php ;}
					if($address){
						if($address[0]['street']) { ?><span><?php echo $address[0]['number'].' '.$address[0]['street'].'<br>' ?></span><?php ;}
						if($address[0]['additional']) { ?><span><?php echo $address[0]['additional'].'<br>' ?></span><?php ;}
						if($address[0]['city']) { ?><span><?php echo $address[0]['city'].' '.$address[0]['state'].'<br>' ?></span><?php ;}
						if($address[0]['zip']) { ?><span><?php echo $address[0]['zip'].' ' ?></span><?php ;}
						if($address[0]['country']) { ?><span><?php echo $address[0]['country'].'<br>' ?></span><?php ;}else{ echo '<br>' ?></span><?php ;}
					}
				?>
			</p>
		</div>	
		<?php } ?>
		
	</div>
<?php } else  { 
				if ( $account > 0 ){ ?> <p><?php echo JText::_('GS_ACCOUNT_DELETED'); ?></p> 
		
				<?php } else { ?> 
		
				<p><?php echo JText::_('COM_GSCRM_ACCOUNTS_NONE'); ?></p> <?php }	
	} ?>
</div>
</div> 

<!--
<div class="modal bs-editor-modal-sm" tabindex="-1" role="dialog" aria-labelledby="any_label">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
-->   
<!-- modal starts form here -->
<!--
		<div class="container col-sm-6">
			<div class="form-group row">
				<label><input name="notes" type="Editor" ></label>
			</div>
		</div>
    </div>
  </div>
</div>
-->

			
