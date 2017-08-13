<?php
/*
 * @package com_gscrm
 * @copyright (c)2017 Pedro Bicudo / gsasch.io
 * @license GNU General Public License version 2 or later
 */

namespace Gs\Gscrm\Site\View\New_account;

// button to create a new account from the note add form

use \JText;
use \Jfactory;
use \JSession;
use \Gs\Gscrm\Admin\Helper\Helper;

// no direct access
defined('_JEXEC') or die('Restricted access');

//get menu item ids 
global $menu_item;

$jinput = \JFactory::getApplication()->input;
$url_id = $jinput->get('id', null, 'int');
$url_account = $jinput->get('account', null, 'int');
$url_view = $jinput->get('view', null, 'string');

if (empty($url_id) && empty($url_account)) { ?>

<div class="col-sm-10 pl0">

	<input type="text" name="new_name_account" id="new_name_account" class="form-control col-sm-12 high28" 
			placeholder="<?php echo JText::_('COM_GSCRM_ACCOUNTS_TITLE_NANE') ?>">
	
</div>	

<?php } else { ?>

<div class="col-sm-10">

	<a role="button" class="btn-xs" title="<?php echo JText::_('COM_GSCRM_ACCOUNTS_ADD_TIP') ?>" 
			<?php
			if($url_view == 'Opportunity'){ ?>href="index.php?option=com_gscrm&view=Opportunity&Itemid=<?php echo $menu_item['opportunity'] ?>&<?php echo JSession::getFormToken() ?>=1"<?php ; }
			if($url_view == 'Note'){ ?>href="index.php?option=com_gscrm&view=Note&Itemid=<?php echo $menu_item['note'] ?>&<?php echo JSession::getFormToken() ?>=1"<?php ; } ?>
			 >
	<i class="glyphicon glyphicon-plus"></i><?php echo JText::_('COM_GSCRM_ACCOUNTS_ADD') ?>
	</a>

</div>	

<?php }
