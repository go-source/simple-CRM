<?php
/*
 * @package com_gscrm
 * @copyright (c)2017 Pedro Bicudo / bicudomaschio@gmail.com
 * @license GNU General Public License version 2 or later
 */

namespace Gs\Gscrm\Site\View\Nav_notes;

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
$account = $model->account;
	if($account < 1)
	{
		//account is zero if this is task=add; but if redirected with account_id we neet to set it in this form add
		$jinput = \JFactory::getApplication()->input;
		$account = $jinput->get('account', null, 'int'); 
	}
//get account data
$acc_data = Helper::GetRowArray('#__gscrm_accounts', 'gscrm_account_id', $account);
if($acc_data)
	{
		if($acc_data['company'] > 0){ $rel_comp = Helper::QueryData('title', '#__gscrm_accounts', 'gscrm_account_id', $acc_data['company']) ;}else{$rel_comp = 0;}
		
		// set gravatar
		$email = $acc_data['email'] ;
		$grav_url = "https://www.gravatar.com/avatar/" . md5( strtolower( trim( $email ) ) ) . "&s=40";	
	} 
	else{
		$rel_comp = 0;
		$email = '';
		$grav_url = '';
	}

// set navigation buttons indexes
$prev_item = Helper::PrevItem('#__gscrm_accounts', 'gscrm_account_id', $account, 2);
if($prev_item < 1){$prev_item = $account;}

$next_item = Helper::NextItem('#__gscrm_accounts', 'gscrm_account_id', $account, 2);
if($next_item < 1){$next_item = $account;}	

// next build the navigation bar to navigate through accounts and see the calls/contacts/issues for each account
?>
<div class="col-sm-12 pl0 pr0">
<div class="col-sm-6">
<div class="btn-group col-sm-12" role="group" aria-label="...">
	<button type="button" class="btn btn-default" aria-label="Left Align" title="<?php echo JText::_('GS_CANCEL') ?>" onclick="Joomla.submitbutton('cancel')" >
	<span class="glyphicon glyphicon-home" aria-hidden="true"></span>
	<?php echo JText::_('GS_LIST') ?>
	</button>
				    
	<a role="button" class="btn" title="<?php echo JText::_('GS_TIP_PREV') ?>" href="index.php?option=com_gscrm&view=Note&account=<?php echo $prev_item ?>
		&Itemid=<?php echo $menu_item['note'] ?>&<?php echo JSession::getFormToken() ?>=1" >			
	<i class="glyphicon glyphicon-chevron-left"></i><?php echo JText::_('GS_PREV') ?></a>					    
				    				    
	<button type="button" class="btn btn-success" aria-label="Left Align" title="<?php echo JText::_('GS_TIP_ADD') ?>" onclick="Joomla.submitbutton('apply')" >
	<span class="glyphicon glyphicon-save-file" aria-hidden="true"></span>
	<?php echo JText::_('GS_ADD') ?>
	</button>	
	        
	<a role="button" class="btn" title="<?php echo JText::_('GS_TIP_NEXT') ?>" href="index.php?option=com_gscrm&view=Note&account=<?php echo $next_item ?>
		&Itemid=<?php echo $menu_item['note'] ?>&<?php echo JSession::getFormToken() ?>=1" >
	<i class="glyphicon glyphicon-chevron-right"></i><?php echo JText::_('GS_NEXT') ?></a>		
</div>

<!-- second group: additional navigation -->
<div class="col-sm-12 mt2">
		
		<div class="btn-group">
			<button type="button" class="btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			<!--  <span class="caret"></span> -->
		    <i class="glyphicon glyphicon-eye-open"></i>
			</button>
				<ul class="dropdown-menu">
					<li><a href="index.php?option=com_gscrm&view=Opportunities&account=<?php echo $account ?>&Itemid=<?php echo $menu_item['opportunities'] ?>&<?php echo JSession::getFormToken() ?>=1">
						<?php echo JText::_('GS_SEE_OPPORTUNITIES') ?></a></li>
					<li><a href="index.php?option=com_gscrm&view=Contracts&account=<?php echo $account ?>&Itemid=<?php echo $menu_item['contracts'] ?>&<?php echo JSession::getFormToken() ?>=1">
						<?php echo JText::_('GS_SEE_CONTRACTS') ?></a></li>
					<li><a href="index.php?option=com_gscrm&view=Orders&account=<?php echo $account ?>&Itemid=<?php echo $menu_item['orders'] ?>&<?php echo JSession::getFormToken() ?>=1">
						<?php echo JText::_('GS_SEE_ORDERS') ?></a></li>
	  			</ul>
		</div>	
		
		<?php if ($model->title) { ?>
			<a role="button" class="btn-xs" title="<?php echo JText::_('GS_TIP_PLUS_NOTE') ?>"  
				href="index.php?option=com_gscrm&view=Note&account=<?php echo $account ?>&Itemid=<?php echo $menu_item['note'] ?>&<?php echo JSession::getFormToken() ?>=1"  >
			<i class="glyphicon glyphicon-repeat"></i><?php echo ' '.JText::_('GS_NEW_NOTE') ?></a>	
		<?php } ?>	
</div>  
</div>

<!-- third group: account information -->
<div class="col-sm-6 gs_border">

<?php if($acc_data) { ?>

	<div class="col-sm-2" >
		<a>
		<img class="mt3p" src="<?php echo $grav_url ?>" align="center" alt="gravatar image">
		</a>
		<a class="font10" href="https://en.gravatar.com/support/activating-your-account" >gravatar
		</a>
		<a role="button" class="btn-xs" href="index.php?option=com_gscrm&view=Account&id=<?php echo $account ?>&Itemid=<?php echo $menu_item['account'] ?>&<?php echo JSession::getFormToken() ?>=1"
			 title="<?php echo JText::_('COM_GSCRM_ACCOUNTS_EDIT') ?>">
			<i class="glyphicon glyphicon-pencil"></i></a> 
	</div>
	
	<div class="col-sm-10 " >
		<div class="col-sm-12" >	
			<p>	<span><?php echo $acc_data['title'].'<br>' ?></span>
				<span><?php if($rel_comp !== 0){ echo '<a href="index.php?option=com_gscrm&view=Account&id='.$acc_data['company'].'&Itemid='.$menu_item['account'].'&'.JSession::getFormToken().'=1" >'.$rel_comp.'</a><br>';} ?>
				</span>			
				<span><?php if($acc_data['email']){ echo '<a href="mailto:'.$acc_data['email'].'">'.$acc_data['email'].'</a><br>';} ?></span>
			</p>
		</div>
		<div class="col-sm-12" >
			<div class="col-sm-6 pl0" >
				<p>
				<?php if($acc_data['phone1']) {?><span><i class="glyphicon glyphicon-earphone"></i><?php echo ' '.$acc_data['phone1'] ?></span><br> <?php ;}
				
				if($acc_data['phone2']) {?><span><i class="glyphicon glyphicon-earphone"></i><?php echo ' '.$acc_data['phone2'] ?></span> <?php ;} ?>
				</p>
			</div>
			<div class="col-sm-6 pr0" >
				<p>
				<?php if($acc_data['phone3']) {?><span><i class="glyphicon glyphicon-earphone"></i><?php echo ' '.$acc_data['phone3'] ?></span><br> <?php ;}
				
				if($acc_data['phone4']) {?><span><i class="glyphicon glyphicon-earphone"></i><?php echo ' '.$acc_data['phone4'] ?></span> <?php ;} ?>
				</p>								
			</div
		</div>
	</div>
<?php } else  { 
				if ( $account > 0 ){ ?> <p><?php echo JText::_('GS_ACCOUNT_DELETED'); ?></p> 
		
				<?php } else { ?> 
		
				<p><?php echo JText::_('COM_GSCRM_ACCOUNTS_NONE'); ?></p> <?php }	
		} ?>
</div>
</div>