<?php
/*
 * @package com_gscrm
 * @copyright (c)2017 Pedro L Bicudo Maschio / bicudomaschio@gmail.com
 * @license GNU General Public License version 2 or later
 */

namespace Gs\Gscrm\Site\View\Nav_account;

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
$item_id = (int)$model->gscrm_account_id;
$prev_item = Helper::PrevItem('#__gscrm_accounts', 'gscrm_account_id', $item_id, 0);
$next_item = Helper::NextItem('#__gscrm_accounts', 'gscrm_account_id', $item_id, 0);

$has_notes = Helper::GetColumnArray('title', '#__gscrm_notes', 'account', $item_id);	

//main nav
?>
<div class="btn-group col-sm-8" role="group" aria-label="...">
	<button type="button" class="btn btn-default" aria-label="Left Align" title="<?php echo JText::_('GS_CANCEL') ?>" onclick="Joomla.submitbutton('cancel')" >
	<span class="glyphicon glyphicon-home" aria-hidden="true"></span>
	<?php echo JText::_('GS_LIST') ?>
	</button>
				    
	<a role="button" class="btn" title="<?php echo JText::_('GS_TIP_PREV') ?>"
		<?php 	if ($prev_item > 0) { ?>href="index.php?option=com_gscrm&view=Account&id=<?php echo $prev_item; ?>&Itemid=<?php echo $menu_item['account'] ?>&<?php echo JSession::getFormToken() ?>=1" <?php } ?> >
	<i class="glyphicon glyphicon-chevron-left"></i><?php echo JText::_('GS_PREV') ?></a>					    
				    				    
	<button type="button" class="btn btn-success" aria-label="Left Align" title="<?php echo JText::_('GS_TIP_ADD') ?>" onclick="Joomla.submitbutton('apply')" >
	<span class="glyphicon glyphicon-save-file" aria-hidden="true"></span>
	<?php echo JText::_('GS_ADD') ?>
	</button>	
	
	<button type="button" class="btn btn-default" aria-label="Left Align" title="<?php echo JText::_('GS_TIP_NEW') ?>" onclick="Joomla.submitbutton('savenew')" >
	<span class="glyphicon glyphicon-save-file" aria-hidden="true"></span>
	<?php echo JText::_('GS_ADD_NEW') ?>
	</button>
	
	<button type="button" class="btn btn-danger" aria-label="Left Align" title="<?php echo JText::_('GS_DELETE_TIP') ?>" 
		onclick="if (confirm('<?php echo JText::_('GS_CONFIRM_DELETE') ?>')){Joomla.submitbutton('remove');}" >
	<span class="glyphicon glyphicon-remove-sign" aria-hidden="true"></span>
	<?php echo JText::_('GS_DELETE') ?>
	</button>			
				        
	<a role="button" class="btn" title="<?php echo JText::_('GS_TIP_NEXT') ?>"
		<?php  if ($next_item > 0) { ?> href="index.php?option=com_gscrm&view=Account&id=<?php echo $next_item; ?>&Itemid=<?php echo $menu_item['account'] ?>&<?php echo JSession::getFormToken() ?>=1" <?php ;} ?> >
	<i class="glyphicon glyphicon-chevron-right"></i><?php echo JText::_('GS_NEXT') ?></a>		
			    
</div>

<?php //right box nav ?>

<div class="col-sm-4">
		
	<a role="button" class="btn-xs" href="index.php?option=com_gscrm&view=Note&account=<?php echo $item_id ?>&Itemid=<?php echo $menu_item['note'] ?>&<?php echo JSession::getFormToken() ?>=1"
		 title="<?php echo JText::_('GS_TIP_PLUS_NOTE') ?>">
		<i class="glyphicon glyphicon-pencil"></i></a> 
		
	<a role="button" class="btn-xs" name="add_relation" data-toggle="modal" data-target=".bs-relation-modal-sm" title="<?php echo JText::_('GS_HAS_ADD_RELATION') ?>">
		<i class="glyphicon glyphicon-link"></i></a> 
		
	<a role="button" class="btn-xs" name="add_relation" data-toggle="modal" data-target=".bs-add-modal-sm" title="<?php echo JText::_('GS_ADD_ADDR') ?>">
		<i class="glyphicon glyphicon-home"></i></a>
	
	<a class="btn-xs" href="index.php?option=com_gscrm&view=Opportunity&account=<?php echo $item_id ?>&Itemid=<?php echo $menu_item['opportunity'] ?>&<?php echo JSession::getFormToken() ?>=1"
		 title="<?php echo ' '.JText::_('COM_GSCRM_OPPORTUNITIES_CREATE') ?>" >
		<i class="glyphicon glyphicon-pushpin"></i></a>	
	
	<a class="btn-xs" href="index.php?option=com_gscrm&view=Contract&account=<?php echo $item_id ?>&type=<?php echo $model->type ?>&Itemid=<?php echo $menu_item['contract'] ?>&<?php echo JSession::getFormToken() ?>=1"
		 title="<?php echo ' '.JText::_('COM_GSCRM_CONTRACTS_CREATE') ?>" >
		<i class="glyphicon glyphicon-briefcase"></i></a>	
	
	<a class="btn-xs" href="index.php?option=com_gscrm&view=Order&account=<?php echo $item_id ?>&type=<?php echo $model->type ?>&Itemid=<?php echo $menu_item['order'] ?>&<?php echo JSession::getFormToken() ?>=1"
		 title="<?php echo ' '.JText::_('COM_GSCRM_ORDERS_CREATE') ?>" >
		<i class="glyphicon glyphicon-shopping-cart"></i></a>	
		
	<div class="btn-group">
		<button type="button" class="btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		<!--  <span class="caret"></span> -->
	    <i class="glyphicon glyphicon-eye-open"></i>
		</button>
			<ul class="dropdown-menu">
				<li><a href="index.php?option=com_gscrm&view=Opportunities&account=<?php echo $item_id ?>&Itemid=<?php echo $menu_item['opportunities'] ?>&<?php echo JSession::getFormToken() ?>=1">
					<?php echo JText::_('GS_SEE_OPPORTUNITIES') ?></a></li>
				<li><a href="index.php?option=com_gscrm&view=Contracts&account=<?php echo $item_id ?>&Itemid=<?php echo $menu_item['contracts'] ?>&<?php echo JSession::getFormToken() ?>=1">
					<?php echo JText::_('GS_SEE_CONTRACTS') ?></a></li>
				<li><a href="index.php?option=com_gscrm&view=Orders&account=<?php echo $item_id ?>&Itemid=<?php echo $menu_item['orders'] ?>&<?php echo JSession::getFormToken() ?>=1">
					<?php echo JText::_('GS_SEE_ORDERS') ?></a></li>
  			</ul>
	</div>
		
	<?php if($model->type == 2) { ?>	
		<a href="mailto:<?php echo $model->email ?>">
			<img class="mt3p ml2 pics" src=<?php echo "https://www.gravatar.com/avatar/".md5(strtolower(trim($model->email)))."&s=40"?> align="center" alt="" ></a>		
		<?php ;} else { ?>		
		<a href="http://<?php echo $model->email ?>" target="_blank">
			<img class="mt3p ml2 pics" src="https://logo.clearbit.com/<?php echo $model->email ?>" align="center" alt=""></a>				
		<?php ;} ?>			
		 			
</div>

