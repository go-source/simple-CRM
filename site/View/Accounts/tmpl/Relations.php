<?php
/*
 * @package com_gscrm
 * @copyright (c)2017 Pedro L Bicudo Maschio / bicudomaschio@gmail.com
 * @license GNU General Public License version 2 or later
 */

namespace Gs\Gscrm\Site\View\Relations;
/*
* * * * * * * * * * * * * * * * * * * * * * * *
*  method to show relationships
* * * * * * * * * * * * * * * * * * * * * * * */

use \JText;
use \JSession;
use \Gs\Gscrm\Admin\Helper\Helper;

// no direct access
defined('_JEXEC') or die('Restricted access');

//get id of this account
$item_id = (int)$model->gscrm_account_id;

//get company code from user
$code = Helper::GetCompanyCode()->gscrm_code_id;

//get all accounts from model
$relatives = $model->where('code', '=', $code)->get();

//get existing relations - this function needs to be reviewed to return only valid relations; and use cache
$relations = Helper::GetRelations($item_id);
$count = count($relations);

//get all accounts from model filtered by company code
$relatives = $model->where('code', '=', $code)->get();

//build array of relationship names
$array_names = array(1=>"GS_CORPORATION", 2=>"GS_BZ_PARTNER", 3=>"GS_BZ_REPORTS_TO", 4=>"GS_BZ_ASSISTANT", 5=>"GS_SPONSOR", 6=>"GS_SOCIAL", 7=>"GS_PARTNER", 8=>"GS_WIFE", 9=>"GS_HUSBAND", 10=>"GS_EXWIFE", 11=>"GS_EXHUSBAND", 12=>"GS_CHILDREN", 13=>"GS_GRANDPARENT", 14=>"GS_GRANDCHILDREN", 15=>"GS_AUNT_UNCLE", 16=>"GS_NEPHEW_NIECE");

/*
* * * * * * * * * * * * * * * * * * * * * * * *
*  start of relations box 
* * * * * * * * * * * * * * * * * * * * * * * */

if(empty($relations))
{ ?>
	<div class="high28 mt2">
		<p><?php echo JText::_('GS_HAS_NO_RELATION') ?></p>
	</div>
	
<?php 
	;}
	?>
<div class="gs_border font12 mt1">
	<?php 
		foreach($relations as $kk => $relation)
		{
			//workaround to skip relations not valid for this form
			if( !is_numeric($relation['title']) ){ continue; }
		?>
			<p class="text-center">
				<a role="button" class="btn-xs high10 font10" href="index.php?option=com_gscrm&view=Account&id=<?php echo $relation['child'] ?>&<?php echo JSession::getFormToken() ?>=1"
					name="edit_relation<?php echo $kk ?>" id="edit_relation<?php echo $kk ?>"
					value="<?php echo $relation['gscrm_account_id'] ?>" title="<?php echo JText::_('GS_HAS_RELATION_EDIT') ?>"
					data-toggle="modal" data-target=".bs-rel<?php echo $kk ?>-modal-sm">						
					<i class="glyphicon glyphicon-pencil"></i></a>					
				<?php 
					
					if ($relation['child']) {
						$len = strlen(JText::_($array_names[$relation['title']]).': '.JText::_($relatives[$relation['child']]->title));
						
						//break line if too long
						if($len > 30){
						$txt1 = '<span>'.JText::_($array_names[$relation['title']]).': </span>';
						$txt2 = '<a href="index.php?option=com_gscrm&view=Account&id='.$relation['child'].
								'&'.JSession::getFormToken().'=1" >'.JText::_($relatives[$relation['child']]->title).'</a>';
						echo $txt1.'<br>'.$txt2;
						
						} else {
						echo '<span>'.JText::_($array_names[$relation['title']]).': </span><a href="index.php?option=com_gscrm&view=Account&id='.$relation['child'].'&'.JSession::getFormToken().'=1" >'.JText::_($relatives[$relation['child']]->title).'</a>';
						}
					}						
			?>	</p>		
		<?php 
		}
	?>
</div>
<!-- Add relation footnote -->
<div class="col-sm-10 font12">
	<?php if ($item_id > 0){
		?>
	<input type="checkbox" name="add_relation" data-toggle="modal" data-target=".bs-relation-modal-sm"><?php echo ' '.JText::_('GS_HAS_ADD_RELATION') ?>
	<input type="hidden" name="count_relations" id="count_relations" value="<?php echo $count ?>">	
	<?php
		} else { ?>
			<span><?php echo JText::_('GS_PLS_SAVE_BEFORE_RELATION') ?></span>
		<?php } 
			?>
</div>

<!-- First Relations Modal -->

<div class="modal bs-relation-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel1">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
<!-- start form here -->
		<div class="container col-sm-6">
					    
		    <div class="form-group row">
		      <label for="rel_title" class="col-sm-2 col-form-label"><?php echo JText::_('GS_HAS_REL_TITLE') ?></label>
		      <div class="col-sm-6">
		        <select class="form-control high28" name="rel_title" id="rel_title" >	
					<?php
						foreach ($array_names as $k => $array_name)
						{ ?>			                
					<option value="<?php echo $k ?>"><?php echo JText::_($array_name) ?></option>
					<?php } ?>
				</select> 	        
		      </div>
		    </div>
		    
		    <div class="form-group row">
		      <label for="rel_account" class="col-sm-2 col-form-label"><?php echo JText::_('GS_HAS_REL_ACCOUNT') ?></label>
		      <div class="col-sm-6">	
				<select class="form-control" id="rel_account" name="rel_account" >
					<?php
						foreach ($relatives as $relative)
						{
							//skip if int ID is the same: cannot relate item to itself
							if( (int)$item_id !== (int)$relative->gscrm_account_id ) {
							?><option value="<?php echo $relative->gscrm_account_id ?>"><?php echo $relative->title ?></option>
					<?php }	
						} ?>
				</select>      
		      </div>
		    </div>
		    
		    <div class="form-group row">
		      <label for="new_name_account" class="col-sm-2 col-form-label"><?php echo JText::_('GS_NEM_NAME_ACCOUNT') ?></label>
		      <div class="col-sm-6">	
				<input type="text" name="new_name_account" id="new_name_account" class="form-control col-sm-2 high28" placeholder="<?php echo JText::_('GS_NEM_NAME_ACCOUNT_TIP') ?>">
				
				<div class="btn-group" data-toggle="buttons">
				  <label class="btn btn-primary ">
				    <input type="radio" name="type_new_name" id="option1" value="0" checked ><?php echo JText::_('GS_PE') ?> 
				  </label>
				  <label class="btn btn-primary">
				    <input type="radio" name="type_new_name" id="option2" value="1" ><?php echo JText::_('GS_BZ') ?> 
				  </label>	
				</div>			
				
		      </div>
		    </div>		    
		    		    	    		    		    		    		    
<!-- save buttons	 -->	    
			<div data-toggle="buttons">		
				<label for="new_relation" role="button" class="btn btn-primary btn-sm" ><?php echo JText::_('GS_HAS_REL_SAVE') ?>
				<input type="checkbox" name="new_relation" id="new_relation" value="1" onclick="Joomla.submitbutton('apply')" >	</label>
				<span class="font10 ml1"><?php echo JText::_('GS_PRESS_ESC_EXIT'); ?></span>	
			</div>	    
		</div>
<!-- end of form here -->     
    </div>
  </div>
</div>

<!-- Second Relations Modal -->

<?php 
	//loop: all modals according to the number of relations for this account	
	foreach($relations as $yy => $relation)
	{ 
	?>	
<div class="modal bs-rel<?php echo $yy ?>-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel1">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
<!-- start form here -->
		<div class="container col-sm-6">
		    
		    <div class="form-group row">
		      <label for="rel_title<?php echo $yy ?>" class="col-sm-2 col-form-label"><?php echo JText::_('GS_HAS_REL_TITLE') ?></label>
		       
		      <div class="col-sm-6"> 
		        <select class="form-control high28" name="rel_title<?php echo $yy ?>" id="rel_title<?php echo $yy ?>">
			        <option value="<?php echo $relation['title'] ?>" ><?php echo JText::_($array_names[$relation['title']]) ?></option>	
					<?php
						foreach ($array_names as $k => $array_name)
						{ ?>			                
					<option value="<?php echo $k ?>"><?php echo JText::_($array_name) ?></option>
					<?php } ?>
				</select> 	        
		      </div>
		    </div>
		    
		    <div class="form-group row">
		      <label for="rel_account<?php echo $yy ?>" class="col-sm-2 col-form-label"><?php echo JText::_('GS_HAS_REL_ACCOUNT') ?></label>
		      
		      <div class="col-sm-6">	
				<select class="form-control" id="rel_account<?php echo $yy ?>" name="rel_account<?php echo $yy ?>">
					<option value="<?php echo $relation['child'] ?>" ><?php echo $relatives[$relation['child']]->title ?></option>
					<?php
						foreach ($relatives as $relative)
						{
							//skip if int ID is the same: cannot relate item to itself
							if( (int)$item_id !== (int)$relative->gscrm_account_id ) {
							?><option value="<?php echo $relative->gscrm_account_id ?>"><?php echo $relative->title ?></option>
					<?php }	
						} ?>
				</select> 
				     
		      </div>
		    </div>		    	    		    		    		    		    
<!-- save buttons	 -->	    
			<div data-toggle="buttons">	
					
				<label for="relation<?php echo $yy ?>" role="button" class="btn btn-primary btn-sm" ><?php echo JText::_('GS_ADD') ?>
				<input type="checkbox" name="relation<?php echo $yy ?>" id="relation<?php echo $yy ?>" value="<?php echo $relation['gscrm_relation_id'] ?>" 
				onclick="Joomla.submitbutton('apply')" > </label>
				
				<label for="del_rel<?php echo $yy ?>" role="button" class="btn btn-danger btn-sm" title="<?php echo JText::_('GS_DEL_ADDR_WARNING_TIP') ?>">
				<?php echo JText::_('GS_DEL_ADDR') ?>
				<input type="checkbox" class=" " name="del_rel<?php echo $yy ?>" id="del_rel<?php echo $yy ?>" 
				value="<?php echo $relation['gscrm_relation_id'] ?>" onclick="Joomla.submitbutton('apply')" > </label>
				<span class="badge badge-warning"> Warning: cannot undo after delete !</span>	
		
			</div>	
			<span class="font10 ml1"><?php echo JText::_('GS_PRESS_ESC_EXIT'); ?></span>	    
		</div>
<!-- end of form here -->     
    </div>
  </div>
</div>
<?php }



