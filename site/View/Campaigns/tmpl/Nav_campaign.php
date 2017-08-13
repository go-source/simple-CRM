<?php
/*
 * @package com_gscrm
 * @copyright (c)2017 Pedro L Bicudo Maschio / bicudomaschio@gmail.com
 * @license GNU General Public License version 2 or later
 */

namespace Gs\Gscrm\Site\View\Nav_campaign;

// navigation buttons used in forms

use \JText;
use \Gs\Gscrm\Admin\Helper\Helper;

// no direct access
defined('_JEXEC') or die('Restricted access');

//main nav
?>	
<div class="btn-group col-sm-8 pl0" role="group" aria-label="...">
	<button type="button" class="btn btn-default" aria-label="Left Align" title="<?php echo JText::_('GS_CANCEL') ?>" onclick="Joomla.submitbutton('cancel')" >
	<span class="glyphicon glyphicon-home" aria-hidden="true"></span>
	<?php echo JText::_('GS_LIST') ?>
	</button>
				    				    				    
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
</div>