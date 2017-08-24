<?php
/*
 * @package com_gscrm
 * @copyright (c)2017 Pedro L Bicudo Maschio / bicudomaschio@gmail.com
 * @license GNU General Public License version 2 or later
 */

namespace Gs\Gscrm\Site\View\Notes_opps;

// navigation buttons used in forms

use \JText;
use \Gs\Gscrm\Admin\Helper\Helper;
use \JDate;
use \JFactory;
use \JSession;

// no direct access
defined('_JEXEC') or die('Restricted access');

//get menu item ids 
global $menu_item;

//set navigation id 
$item_id = (int)$model->gscrm_opportunity_id;
$account = (int)$model->account;
$highlite = (int)$model->note;
$highlitetxt = 'alert alert-danger" role="alert';
$highlitetxt2 = 'alert alert-success" role="alert';

$jinput = \JFactory::getApplication()->input;

$filter_note = 1;

$nt_show = $jinput->get('nt_show', null, 'int');

if( $nt_show == 2 ) { $filter_note = 0 ;}

if($jinput->get('note', null, 'int')) { $highlite = $jinput->get('note', null, 'int') ;}	
	
	if($account < 1)
	{
		//account is zero if this is task=add; but if redirected with account_id we need to set it in this form add	
		$account = $jinput->get('account', null, 'int'); 
	}

$hide_note = 0; //future use, to block the checkbox open/closed notes if defined in preferences

//load all notes for this account; note that model filter is also applied, so enabled is filtered
$notes = Helper::Notes($account, $filter_note);

?>
<div class="col-md-12 pl0 pr0 mt2 gs_border">
	<div class="col-md-12 pl0 pr0">
		<div class="col-md-8">
			<p><?php echo JText::_('GS_CONTACT_HISTORY') ?>
			</p>
		</div>
		
		<div class="col-md-4">
			<?php if( $nt_show == 1 || $filter_note == 1 ){ ?>
			<a role="button" class="btn-xs" title="<?php echo JText::_('GS_SHOW_CLOSED_TIP') ?>"
					<?php if ($item_id) { ?>
						href="index.php?option=com_gscrm&view=Opportunity&id=<?php echo $item_id ?>&nt_show=2&Itemid=<?php echo $menu_item['opportunity'] ?>&<?php echo JSession::getFormToken() ?>=1 "
						<?php ; 
						}else{ ?>
						href="index.php?option=com_gscrm&view=Opportunity&account=<?php echo $account ?>&nt_show=2&Itemid=<?php echo $menu_item['opportunity'] ?>&<?php echo JSession::getFormToken() ?>=1 "
						<?php ; 
						} ?> 
					>
					<i class="glyphicon glyphicon-ban-circle"></i><?php echo ' '.JText::_('GS_SHOW_CLOSED') ?>
			</a>
			<?php } else { ?>
			<a role="button" class="btn-xs" title="<?php echo JText::_('GS_HIDE_CLOSED_TIP') ?>"
					<?php if ($item_id) { ?>
						href="index.php?option=com_gscrm&view=Opportunity&id=<?php echo $item_id ?>&nt_show=1&Itemid=<?php echo $menu_item['opportunity'] ?>&<?php echo JSession::getFormToken() ?>=1" 
						<?php ;
							}else{ ?>
						href="index.php?option=com_gscrm&view=Opportunity&account=<?php echo $account ?>&nt_show=1&Itemid=<?php echo $menu_item['opportunity'] ?>&<?php echo JSession::getFormToken() ?>=1"
						<?php ; 
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
		foreach($notes as $note)
		{ ?>
			<!-- 	left column -->
			<div class="col-md-10 pr0">
				<p class="high10">
					<?php echo Helper::User_timezone($note->created_on, 2).' | ';
							echo Helper::QueryData('user_name', '#__gscrm_beads', 'gscrm_bead_id', $note->owner).' | ';
							echo Helper::QueryData('title', '#__gscrm_accounts', 'gscrm_account_id', $note->account).' | ';
							if ($note->enabled == 1){ echo '<span class="text-danger">'.JText::_('GS_OPEN').'</span>'; } 
							else { echo '<span class="text-success">'.JText::_('GS_CLOSED').'</span>'; }					
					 ?>
				</p>
				<p class="high28 gs_border pt2p <?php 
					if( $highlite == $note->gscrm_note_id){ echo $highlitetxt ;}  //this is the note that originated this opportunity
															
					if( $item_id == $note->opportunity){ echo $highlitetxt2 ;}		//this note was created by this opportunity follow-up
													?>" >			
			<span class="ml2"><?php echo $note->title ?></span></p>
			</div>
			<!--    right column -->
			<div class="col-md-2 pl0 pr0">
				<p>
				<a class="text-info" href="index.php?option=com_gscrm&view=Note&id=<?php echo $note->gscrm_note_id ?>&Itemid=<?php echo $menu_item['note'] ?>&<?php echo JSession::getFormToken() ?>=1"
					 title="<?php echo $note->content ?>">
					<i class="glyphicon glyphicon-comment"></i></a>							
				<?php
				if($note->enabled == 1){ ?>
				<a class="text-danger" href="index.php?option=com_gscrm&view=Opportunity&id=<?php echo $item_id ?>&account=<?php echo $account ?>&flagchg=2&Chgnum=<?php echo $note->gscrm_note_id ?>&nt_show=<?php echo $nt_show ?>&Itemid=<?php echo $menu_item['opportunity'] ?>&<?php echo JSession::getFormToken() ?>=1"
					 title="<?php echo ' '.JText::_('GS_CHANGE_CLOSED') ?>" >
					<i class="glyphicon glyphicon-hand-left"></i></a>	
				<?php } else { ?>
				<a class="text-primary" href="index.php?option=com_gscrm&view=Opportunity&id=<?php echo $item_id ?>&account=<?php echo $account ?>&flagchg=1&Chgnum=<?php echo $note->gscrm_note_id ?>&nt_show=<?php echo $nt_show ?>&Itemid=<?php echo $menu_item['opportunity'] ?>&<?php echo JSession::getFormToken() ?>=1"
					title="<?php echo ' '.JText::_('GS_CHANGE_OPEN') ?>" >
					<i class="glyphicon glyphicon-thumbs-up"></i></a>
				<?php } ?>
				<a class="" href="index.php?option=com_gscrm&view=Opportunity&note=<?php echo $note->gscrm_note_id ?>&account=<?php echo $note->account ?>&Itemid=<?php echo $menu_item['opportunity'] ?>&<?php echo JSession::getFormToken() ?>=1"
					title="<?php echo ' '.JText::_('COM_GSCRM_OPPORTUNITIES_CREATE') ?>"
						>
					<i class="glyphicon glyphicon-pushpin"></i></a>	
<!--
				<a class="" href="index.php?option=com_gscrm&view=Contract&note=<?php echo $note->gscrm_note_id ?>&account=<?php echo $note->account ?>&Itemid=<?php echo $menu_item['contract'] ?>&<?php echo JSession::getFormToken() ?>=1"
					title="<?php echo ' '.JText::_('COM_GSCRM_CONTRACTS_CREATE') ?>"
						>
					<i class="glyphicon glyphicon-briefcase"></i></a>
-->	
				<a class="" href="index.php?option=com_gscrm&view=Order&note=<?php echo $note->gscrm_note_id ?>&account=<?php echo $note->account ?>&Itemid=<?php echo $menu_item['order'] ?>&<?php echo JSession::getFormToken() ?>=1"
					title="<?php echo ' '.JText::_('COM_GSCRM_ORDERS_CREATE') ?>"
						>
					<i class="glyphicon glyphicon-shopping-cart"></i></a>											
				</p>				
			</div>
	<?php } ?>
	</div>	    
</div>
