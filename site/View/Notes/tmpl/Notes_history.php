<?php
/*
 * @package com_gscrm
 * @copyright (c)2017 Pedro L Bicudo Maschio / bicudomaschio@gmail.com
 * @license GNU General Public License version 2 or later
 */

namespace Gs\Gscrm\Site\View\Notes_history;

// navigation buttons used in forms

use \JText;
use \JDate;
use \JFactory;
use \JSession;
use \Gs\Gscrm\Admin\Helper\Helper;

// no direct access
defined('_JEXEC') or die('Restricted access');

//get menu item ids 
global $menu_item;

//set navigation id
$item_id = (int)$model->gscrm_note_id;
$account = (int)$model->account;

//but if this is a new note the $item_id and $account will be empty, let's get it from the URL
$jinput = \JFactory::getApplication()->input;

if($account < 1)
	{
		//account is zero if this is task=add; but if redirected with account_id we need to set it in this form add	
		$account = $jinput->get('account', null, 'int'); 
	}

// load all notes for this account; filtered according to flag show/not-show. 
// Because we use the enable filter in QueryBuild in the deafult browser view, we cannot use default F0F model - it is filtered already

if ($jinput->get('en_ch', null, 'int') == 2)
	{ 
		$enabled = 0;
		$note_items = Helper::Notes($account, 0);
	
	}else{
		
		$enabled = 1;
		$note_items = Helper::Notes($account, 1);
	}

$hide_note = 0; //future use, to block the checkbox open/closed notes if defined in preferences

?>
<div class="col-md-12 pl0 pr0 mt2">
	<div class="col-md-12 pl0 pr0">
		<div class="col-md-8 pl0">
			<p><?php echo JText::_('GS_CONTACT_HISTORY') ?>
			</p>
		</div>
		
		<div class="col-md-4">
			<?php if($enabled == 1){ ?>
			<a role="button" class="btn-xs" title="<?php echo JText::_('GS_SHOW_CLOSED_TIP') ?>"
					<?php if ($item_id) { ?>
						href="index.php?option=com_gscrm&view=Note&id=<?php echo $item_id ?>&en_ch=2&Itemid=<?php echo $menu_item['note'] ?>&<?php echo JSession::getFormToken() ?>=1" <?php ; 
						}else{ ?>
						href="index.php?option=com_gscrm&view=Note&account=<?php echo $account ?>&en_ch=2&Itemid=<?php echo $menu_item['note'] ?>&<?php echo JSession::getFormToken() ?>=1" <?php ; 
						} ?> 
					>
					<i class="glyphicon glyphicon-ban-circle"></i><?php echo ' '.JText::_('GS_SHOW_CLOSED') ?>
			</a>
			<?php } else { ?>
			<a role="button" class="btn-xs" title="<?php echo JText::_('GS_HIDE_CLOSED_TIP') ?>"
					<?php if ($item_id) { ?>
						href="index.php?option=com_gscrm&view=Note&id=<?php echo $item_id ?>&en_ch=1&Itemid=<?php echo $menu_item['note'] ?>&<?php echo JSession::getFormToken() ?>=1" <?php ; 
						}else{ ?>
						href="index.php?option=com_gscrm&view=Note&account=<?php echo $account ?>&en_ch=1&Itemid=<?php echo $menu_item['note'] ?>&<?php echo JSession::getFormToken() ?>=1" <?php ; 
						} ?> 
					>
					<i class="glyphicon glyphicon-ban-circle"></i><?php echo ' '.JText::_('GS_HIDE_CLOSED') ?>
			</a>
			<?php }	
			?> 	
		</div>
	</div>
	
	<div class="col-md-12 pl0 pr0">	

	<?php
		foreach($note_items as $item)
		{ ?>
			<!-- 	left column -->
			<div class="col-md-10 pl0 pr0">
				<p class="high10">
					<?php echo Helper::User_timezone( $item->created_on, 2 ).' | ';
							echo Helper::QueryData('user_name', '#__gscrm_beads', 'gscrm_bead_id', $item->owner).' | ';
							echo Helper::QueryData('title', '#__gscrm_accounts', 'gscrm_account_id', $item->account).' | ';
							if ($item->enabled == 1){ echo '<span class="text-danger">'.JText::_('GS_OPEN').'</span>'; } 
							else { echo '<span class="text-success">'.JText::_('GS_CLOSED').'</span>'; }					
					 ?>
				</p>
				<p class="high28 gs_border pt2p">			
			<span class="ml2"><?php echo $item->title ?></span></p>
			</div>
			<!--    right column -->
			<div class="col-md-2 pl0 pr0">
				<p>
				<a class="text-info" href="index.php?option=com_gscrm&view=Note&id=<?php echo $item->gscrm_note_id ?>
					&Itemid=<?php echo $menu_item['note'] ?>&<?php echo JSession::getFormToken() ?>=1"
					 title="<?php echo $item->content ?>">
					<i class="glyphicon glyphicon-comment"></i></a>							
				<?php
				if($item->enabled == 1){ ?>
				<a class="text-danger" href="index.php?option=com_gscrm&view=Note&id=<?php echo $item->gscrm_note_id ?>&flagchg=2&Chgnum=<?php echo $item->gscrm_note_id ?>&Itemid=<?php echo $menu_item['note'] ?>&<?php echo JSession::getFormToken() ?>=1"
					 title="<?php echo ' '.JText::_('GS_CHANGE_CLOSED') ?>" >
					<i class="glyphicon glyphicon-hand-left"></i></a>	
				<?php } else { ?>
				<a class="text-primary" href="index.php?option=com_gscrm&view=Note&id=<?php echo $item->gscrm_note_id ?>&flagchg=1&Chgnum=<?php echo $item->gscrm_note_id ?>&Itemid=<?php echo $menu_item['note'] ?>&<?php echo JSession::getFormToken() ?>=1"
					title="<?php echo ' '.JText::_('GS_CHANGE_OPEN') ?>" >
					<i class="glyphicon glyphicon-thumbs-up"></i></a>
				<?php } ?>
				<a class="" href="index.php?option=com_gscrm&view=Opportunity&note=<?php echo $item->gscrm_note_id ?>&account=<?php echo $item->account ?>&Itemid=<?php echo $menu_item['opportunity'] ?>&<?php echo JSession::getFormToken() ?>=1"
					title="<?php echo ' '.JText::_('COM_GSCRM_OPPORTUNITIES_CREATE') ?>"
						>
					<i class="glyphicon glyphicon-pushpin"></i></a>	
				<a class="" href="index.php?option=com_gscrm&view=Contract&note=<?php echo $item->gscrm_note_id ?>&account=<?php echo $item->account ?>&Itemid=<?php echo $menu_item['contract'] ?>&<?php echo JSession::getFormToken() ?>=1"
					title="<?php echo ' '.JText::_('COM_GSCRM_CONTRACTS_CREATE') ?>"
						>
					<i class="glyphicon glyphicon-briefcase"></i></a>	
				<a class="" href="index.php?option=com_gscrm&view=Order&note=<?php echo $item->gscrm_note_id ?>&account=<?php echo $item->account ?>&Itemid=<?php echo $menu_item['order'] ?>&<?php echo JSession::getFormToken() ?>=1"
					title="<?php echo ' '.JText::_('COM_GSCRM_ORDERS_CREATE') ?>"
						>
					<i class="glyphicon glyphicon-shopping-cart"></i></a>											
				</p>				
			</div>
	<?php } ?>
	</div>	    
</div>
