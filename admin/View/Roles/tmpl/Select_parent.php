<?php
/*
 * @package com_gscrm
 * @copyright Copyright (c)2017 Pedro L Bicudo Maschio / bicudomaschio@gmail.com
 * @license GNU General Public License version 2 or later
 */

namespace Gs\Gscrm\admin\View\Select_parent;

// Select parent and child from same model

// no direct access
defined('_JEXEC') or die('Restricted access');

use \JText;

if( $model->code )
	{
	//get model list of roles
	$db = $model->getDbo();
	$items = $model->whereRaw( $db->qn('code') . ' = ' . $db->q($model->code) )->get()->sortByDesc('created_on');			
	?>
	<div>			        
		<select class="" name="parent" id="parent">
			<option value="0" ><?php echo JText::_('GS_NA_OR_SELECT') ?></option>	
			<?php
				foreach ($items as $k => $item)
				{ 
				
				if( $item->gscrm_role_id !== $model->gscrm_role_id ){ ?><option value="<?php echo $k ?>"><?php echo $item->title ?></option>			                

				<?php } } ?>
		</select> 	    
	</div>
<?php
	}
	else
	{ ?>
		<div>
			<p class="gs_red_box"><?php echo JText::_('GS_PLEASE_SAVE_CODE_BEFORE_HIERARCHY') ?></p>
		</div>
	<?php	
	}