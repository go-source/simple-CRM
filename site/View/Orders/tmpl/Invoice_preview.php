<?php
/*
 * @package com_gscrm
 * @copyright (c)2017 Pedro L Bicudo Maschio / bicudomaschio@gmail.com
 * @license GNU General Public License version 2 or later
 */

namespace Gs\Gscrm\Site\View\Invoice_preview; 

// navigation buttons used in forms

use \JText;
use \JFactory;
use \Gs\Gscrm\Admin\Helper\Helper;

// no direct access
defined('_JEXEC') or die('Restricted access');

//get menu item ids 
global $menu_item;

//get order data is saved  
$invoice_data = json_decode($model->params);

// show data
?>
<div class="col-sm-12 pl0 pr0">

<?php if( !$invoice_data) { ?> <p><?php echo JText::_('GS_INVOICE_PREVIEW_NO_DATA') ?></p> <?php ;} else { ?>

<p><?php 	echo JText::_('GS_INVOICE_PREVIEW_TYPE'); 
			if ( $invoice_data->type == 1 ){ echo JText::_('GS_INVOICE_PREVIEW_BUSINESS'); } else { echo JText::_('GS_INVOICE_PREVIEW_PERSON'); }
			
			?> / <?php echo JText::_('GS_INVOICE_PREVIEW_UID');
			if ( $invoice_data->type == 1 ){ echo $invoice_data->bz_uid; } else { echo $invoice_data->acc_uid; } ?></p>
			
<p><?php 	echo JText::_('GS_INVOICE_PREVIEW_NAME');
			if ( $invoice_data->type == 1 ){ echo $invoice_data->bz_ttl; } else { echo $invoice_data->acc_ttl; } ?></p>
			
<p><?php 	echo JText::_('GS_INVOICE_PREVIEW_NUM');
			if ( $invoice_data->type == 1 ){ echo $invoice_data->bz_num; } else { echo $invoice_data->acc_num; } ?></p>	
			
<p><?php 	echo JText::_('GS_INVOICE_PREVIEW_STR');
			if ( $invoice_data->type == 1 ){ echo $invoice_data->bz_str; } else { echo $invoice_data->acc_str; } ?></p>						
			
<p><?php 	echo JText::_('GS_INVOICE_PREVIEW_ADD');
			if ( $invoice_data->type == 1 ){ echo $invoice_data->bz_adtl; } else { echo $invoice_data->bz_adtl; } ?></p>						
			
<p><?php 	echo JText::_('GS_INVOICE_PREVIEW_CITY');
			if ( $invoice_data->type == 1 ){ echo $invoice_data->bz_city; } else { echo $invoice_data->bz_city; } ?></p>										
			
<p><?php 	echo JText::_('GS_INVOICE_PREVIEW_STATE');
			if ( $invoice_data->type == 1 ){ echo $invoice_data->bz_st; } else { echo $invoice_data->bz_st; } ?></p>						
			
<p><?php 	echo JText::_('GS_INVOICE_PREVIEW_ZIP');
			if ( $invoice_data->type == 1 ){ echo $invoice_data->bz_zip; } else { echo $invoice_data->bz_zip; } ?></p>						
			
<p><?php 	echo JText::_('GS_INVOICE_PREVIEW_COUN');
			if ( $invoice_data->type == 1 ){ echo $invoice_data->bz_ctry; } else { echo $invoice_data->bz_ctry; } ?></p>							
			
<p><?php 	echo JText::_('GS_INVOICE_PREVIEW_VALUE');
			//get currency symbol as provided by user. Please do not change to SET_LOCALE - we want the user to decide the symbol
			$currency_symbol = Helper::QueryData('symbol', '#__gscrm_currencies', 'gscrm_currency_id', $invoice_data->currency);
			
			//possible improvement - function to set thousands separators as defined by client 
			echo $currency_symbol; echo money_format('%n', $invoice_data->value); ?>	
			</p>	

<p><?php 	echo JText::_('GS_INVOICE_PREVIEW_TEXT').$invoice_data->title; ?></p>
										
<?php ;} ?>
</div>