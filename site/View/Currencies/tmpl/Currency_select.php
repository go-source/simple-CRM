<?php
/*
 * @package com_gscrm
 * @copyright Copyright (c)2017 Pedro L Bicudo Maschio / bicudomaschio@gmail.com
 * @license GNU General Public License version 2 or later
 */

namespace Gs\Gscrm\Site\View\Currencies;

// Show company code name with id

use \JText;
use \JFactory;
use \JDatabase;
use \Gs\Gscrm\Admin\Helper\Helper;

// no direct access
defined('_JEXEC') or die('Restricted access');

//get currency from model, if this is edit
$pre_currency_id = (int)$model->currency;
$flag = 0;
$enabled = 0;

//get company code (it has been provided to the model onBeforeLoadForm)
$code = (int)$model->code;
if($code == 0){$code = Helper::GetCompanyCode()->gscrm_code_id;}

//load currency options from database, because we want to apply custom filters
$db = JFactory::getDbo();
$query = $db->getQuery(true);
$query
		->select( array('gscrm_currency_id','symbol', 'enabled'))
		->from ($db->qn('#__gscrm_currencies'))
		->where ($db->qn('code')." = ".$db->q($code));
$db->setQuery($query);
$currencies_array = $db->loadObjectList();

//count enabled
foreach ($currencies_array as $count)
{
	if ($count->enabled == 1) { $enabled = $enabled + 1; }
}

//build html select
?>
<div class="col-sm-8 pl0">	
	
	<select class="form-control" id="currency" name="currency" >
	<?php
			foreach ($currencies_array as $currency)
			{
				if($pre_currency_id == 0 && $flag == 0 && $enabled > 1)
					{ $flag=1; ?><option selected="selected" value="0"><?php echo JText::_('GS_SELECT') ?></option><?php ;}	
				
				if($currency->gscrm_currency_id == $pre_currency_id)
					{?>	<option selected="selected" value="<?php echo $pre_currency_id ?>"><?php echo $currency->symbol ?></option><?php ;}	

				if($currency->gscrm_currency_id <> $pre_currency_id && $currency->enabled == 1)
					{?>	<option value="<?php echo $currency->gscrm_currency_id ?>"><?php echo $currency->symbol ?></option><?php ;}
			} ?>
	</select>      
</div>

