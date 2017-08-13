<?php
/*
 * @package com_gscrm
 * @copyright (c)2017 Pedro L Bicudo Maschio / bicudomaschio@gmail.com
 * @license GNU General Public License version 2 or later
 */

namespace Gs\Gscrm\Site\View\Address_modal;

// navigation buttons used in forms

use \JText;
use \Gs\Gscrm\Admin\Helper\Helper;

// no direct access
defined('_JEXEC') or die('Restricted access');

$account_id = (int)$model->gscrm_account_id;
//filters may be different in other views
$addresses = Helper::ShowAddresses($account_id);
$count = count($addresses);
$prefcode = 0; //for future, will be used in preferences, codes table. Tells if number comes before street of after

if(empty($addresses))
{ ?>
	<div class="high28 mt2">
		<p><?php echo JText::_('GS_NO_ADDRESS') ?></p>
	</div>
	<!-- 	write division line -->
	<span class="gs_border col-sm-10"></span>
	
<?php ;} else { ?>

<div class="btn-toolbar" role="toolbar" aria-label="Toolbar with button groups">
  <div class="btn-group mr-2" role="group" aria-label="First group">
<?php foreach($addresses as $key => $address)
	{
		?> <button type="button" class="btn btn-secondary" data-toggle="modal" data-target=".bs-<?php echo $key ?>-modal-sm" 
		<?php //set title for each button ?>
			title="<?php
				if ($key !== 0 && $prefcode == 1){
					if($address['number']) 		{echo $address['number'].' ';}
					if($address['street'])		{echo $address['street'].'/ ';}
					}
				if ($key !== 0 && $prefcode == 0){
					if($address['street']) 		{echo $address['street'].', ';}
					if($address['number'])		{echo $address['number'].'/ ';}
					}					
				if ($key !== 0 ){	
					if($address['additional'])	{echo $address['additional'].'/ ';}
					if($address['city'])		{echo $address['city'].'/ ';}
					if($address['state'])		{echo $address['state'].'/ ';}
					if($address['zip'])			{echo $address['zip'].'/ ';}
					if($address['country'])		{echo $address['country'];}
				}	
			?>">
		
		<?php 
			//set button text for each button
			if ($key == 0) { echo JText::_('GS_MAIN_ADDR_TITLE'); } else {echo $key;}
		?>
		</button>
<?php } ?>

		<!-- address_total tells the model the number of addresses for the filtered account -->
		<input type="hidden" name="count_addresses" value="<?php echo $count ?>">	
  </div>
</div>

<div class="gs_border col-sm-10 font12 mr1">
		<p class="high10 "><?php if( $prefcode !== 1) 
					{ echo $addresses[0]['number'].' '.$addresses[0]['street']; } 
			else	{ echo $addresses[0]['street'].' '.$addresses[0]['number']; }
		?></p>
		<?php if(!empty($addresses[0]['additional'])){ ?><p class="high10 "><?php echo $addresses[0]['additional'] ?></p> <?php } ?>
		<?php if(!empty($addresses[0]['city'])){ ?><p class="high10 "><?php echo $addresses[0]['city'] ?></p> <?php } ?>			
		<?php if(!empty($addresses[0]['state'])){ ?><p class="high10 "><?php echo $addresses[0]['state'] ?></p> <?php } ?>
		<?php if(!empty($addresses[0]['zip'])){ ?><p class="high10 "><?php echo $addresses[0]['zip'] ?></p> <?php } ?>
		<?php if(!empty($addresses[0]['country'])){ ?><p class="high10 "><?php echo $addresses[0]['country'] ?></p> <?php } 
			
		//and parse field address interger for this main address
		?> <input name="address" type="hidden" value="<?php echo $address['gscrm_address_id'] ?>">  
</div>
<?php } ?>

<!-- Small modal -->
<div class="col-sm-10 font12">
	<span class="" title="<?php echo JText::_('GS_ADD_ADDR_TIP') ?>" >
	<input type="checkbox" name="checkbox_add" data-toggle="modal" data-target=".bs-add-modal-sm"><?php echo JText::_('GS_ADD_ADDR') ?>	
	</span>
</div>

<!-- First Modal -->
<div class="modal bs-add-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel1">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
<!-- start form here -->
		<div class="container col-sm-6">
			  
		    <div class="form-group row">
		      <label for="add_street" class="col-sm-2 col-form-label"><?php echo JText::_('GS_STREET') ?></label>
		      <div class="col-sm-6">
		        <input type="text" class="form-control high28" name="add_street" id="add_street" placeholder="<?php echo JText::_('GS_STREET') ?>">
		      </div>
		    </div>
		    
		    <div class="form-group row">
		      <label for="add_number" class="col-sm-2 col-form-label"><?php echo JText::_('GS_NUMBER') ?></label>
		      <div class="col-sm-6">
		        <input type="text" class="form-control high28" name="add_number" id="add_number" placeholder="<?php echo JText::_('GS_NUMBER') ?>">
		      </div>
		    </div>
		    
		    <div class="form-group row">
		      <label for="add_additional" class="col-sm-2 col-form-label"><?php echo JText::_('GS_ADDITIONAL') ?></label>
		      <div class="col-sm-6">
		        <input type="text" class="form-control high28" name="add_additional" id="add_additional" placeholder="<?php echo JText::_('GS_ADDITIONAL_PLC') ?>">
		      </div>
		    </div>
		    		    
		    <div class="form-group row">
		      <label for="add_city" class="col-sm-2 col-form-label"><?php echo JText::_('GS_CITY') ?></label>
		      <div class="col-sm-6">
		        <input type="text" class="form-control high28" name="add_city" id="add_city" placeholder="<?php echo JText::_('GS_CITY') ?>">
		      </div>
		    </div>		    
		    		    		    
		    <div class="form-group row">
		      <label for="add_state" class="col-sm-2 col-form-label"><?php echo JText::_('GS_STATE') ?></label>
		      <div class="col-sm-6">
		        <input type="text" class="form-control high28" name="add_state" id="add_state" placeholder="<?php echo JText::_('GS_STATE') ?>">
		      </div>
		    </div>	
		    		    
		    <div class="form-group row">
		      <label for="add_country" class="col-sm-2 col-form-label"><?php echo JText::_('GS-COUNTRY') ?></label>
		      <div class="col-sm-6">
		        <input type="text" class="form-control high28" name="add_country" id="add_country" placeholder="<?php echo JText::_('GS-COUNTRY') ?>">
		      </div>
		    </div>
		    
		    <div class="form-group row">
		      <label for="add_zip" class="col-sm-2 col-form-label"><?php echo JText::_('GS_ZIP') ?></label>
		      <div class="col-sm-6">
		        <input type="text" class="form-control high28" name="add_zip" id="add_zip" placeholder="<?php echo JText::_('GS_ZIP') ?>">
		      </div>
		    </div>		    	
		    <div class="form-group row">
		      <label for="is_main_addr" class="col-sm-2 col-form-label" title="<?php echo JText::_('GS_MAIN_ADDR_TIP') ?>"><?php echo JText::_('GS_MAIN_ADDR') ?></label>
		      <div class="col-sm-6">
		        <input type="checkbox" class="form-control high28" name="is_main_addr" id="is_main_addr" value="1">
		      </div>
		    </div>		    		    		    		    
<!-- save buttons	 -->	    
	<div data-toggle="buttons">		
		<label for="new_addr" role="button" class="btn btn-primary btn-sm" title="<?php echo JText::_('GS_ADD_ADDR_WARNING_TIP') ?>"><?php echo JText::_('GS_ADD') ?>
		<input type="checkbox" class="transparent" name="new_addr" id="new_addr" value="1" onclick="Joomla.submitbutton('apply')" >	</label>
		
			<span class="font10 ml1"><?php echo JText::_('GS_PRESS_ESC_EXIT'); ?></span>	
	</div>
   		    
		</div>
<!-- end of form here -->     
    </div>
  </div>
</div>

<!-- show address modal -->
<?php 
	//loop: all modals according to the number of addresses stored for this account	
	foreach($addresses as $ii => $address)
	{ 	
	?>
<div class="modal bs-<?php echo $ii ?>-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel1">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
<!-- start form here -->
		<div class="container col-sm-6">			
		    
		    <div class="form-group row">
		      <div class="col-sm-6">
		        <input type="text" class="form-control high28" name="up_street<?php echo $ii ?>" id="up_street<?php echo $ii ?>" placeholder="<?php echo JText::_('GS_STREET') ?>"
		        value="<?php echo $address['street'] ?>">
		      </div>
		    </div>	
		    
		    <div class="form-group row">
		      <div class="col-sm-6">
		        <input type="text" class="form-control high28" name="up_number<?php echo $ii ?>" id="up_number<?php echo $ii ?>" placeholder="<?php echo JText::_('GS_NUMBER') ?>"
		        value="<?php echo $address['number'] ?>">
		      </div>
		    </div>		      
		    
		    <div class="form-group row">
		      <div class="col-sm-6">
		        <input type="text" class="form-control high28" name="up_additional<?php echo $ii ?>" id="up_additional<?php echo $ii ?>" 
		        placeholder="<?php echo JText::_('GS_ADDITIONAL_PLC') ?>"
		        value="<?php echo $address['additional'] ?>">
		      </div>
		    </div>		    		    
		    
		    <div class="form-group row">
		      <div class="col-sm-6">
		        <input type="text" class="form-control high28" name="up_city<?php echo $ii ?>" id="up_city<?php echo $ii ?>" placeholder="<?php echo JText::_('GS_CITY') ?>"
		        value="<?php echo $address['city'] ?>">
		      </div>
		    </div>		    		    		    		    
		    
		    <div class="form-group row">
		      <div class="col-sm-6">
		        <input type="text" class="form-control high28" name="up_state<?php echo $ii ?>" id="up_state<?php echo $ii ?>" placeholder="<?php echo JText::_('GS_STATE') ?>"
		        value="<?php echo $address['state'] ?>">
		      </div>
		    </div>			    		    
		    
		    <div class="form-group row">
		      <div class="col-sm-6">
		        <input type="text" class="form-control high28" name="up_country<?php echo $ii ?>" id="up_country<?php echo $ii ?>" placeholder="<?php echo JText::_('GS-COUNTRY') ?>"
		        value="<?php echo $address['country'] ?>">
		      </div>
		    </div>
		    
		    <div class="form-group row">
		      <div class="col-sm-6">
		        <input type="text" class="form-control high28" name="up_zip<?php echo $ii ?>" id="up_zip<?php echo $ii ?>" placeholder="<?php echo JText::_('GS_ZIP') ?>"
		        value="<?php echo $address['zip'] ?>">
		      </div>
		    </div>		    	
		    
		    <div class="form-group row">
		      <div class="col-sm-6">
		        <input type="checkbox" class="form-control font12" name="up_main<?php echo $ii ?>" id="up_main<?php echo $ii ?>" value="1">
		        <span class="ml1" title="<?php echo JText::_('GS_MAIN_ADDR_TIP') ?>"><?php echo JText::_('GS_MAIN_ADDR') ?></span>
		      </div>
		    </div>	
<!-- 	save and warning buttons -->	    		    		    		    		    
	<div data-toggle="buttons">	
				    
		<label for="up_addr<?php echo $ii ?>" role="button" class="btn btn-primary btn-sm mr1" title="<?php echo JText::_('GS_UPDATE_ADDR_WARNING_TIP') ?>"><?php echo JText::_('GS_UPDATE') ?>
		<input type="checkbox" class=" " name="up_addr<?php echo $ii ?>" id="up_addr<?php echo $ii ?>" 
		value="<?php echo $address['gscrm_address_id'] ?>" onclick="Joomla.submitbutton('apply')" >	</label>
	    
		<label for="del_addr<?php echo $ii ?>" role="button" class="btn btn-danger btn-sm" title="<?php echo JText::_('GS_DEL_ADDR_WARNING_TIP') ?>"><?php echo JText::_('GS_DEL_ADDR') ?>
		<input type="checkbox" class=" " name="del_addr<?php echo $ii ?>" id="del_addr<?php echo $ii ?>" 
		value="<?php echo $address['gscrm_address_id'] ?>" onclick="Joomla.submitbutton('apply')" >	</label>
		<span class="badge badge-warning"> Warning: cannot undo after delete !</span>
	</div>			    
			  		    		    		    
<!-- exit with ESC message -->
		<span class="font10"><?php echo JText::_('GS_PRESS_ESC_EXIT'); ?></span>	
		</div>	    
<!-- end of form here -->     
    </div>
  </div>
</div>
<?php } 
