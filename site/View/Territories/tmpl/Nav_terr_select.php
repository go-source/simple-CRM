<?php
/*
 * @package com_gscrm
 * @copyright (c)2017 Pedro L Bicudo Maschio / bicudomaschio@gmail.com
 * @license GNU General Public License version 2 or later
 */

namespace Gs\Gscrm\Site\View\Nav_terr_select;

use \JText;
use \JFactory;
use \JSession;
use \Gs\Gscrm\Admin\Helper\Helper;

// no direct access
defined('_JEXEC') or die('Restricted access');
/*
* Select accounts for the territory
* 
* filter_account (int)
* filter_relation (int)
* filter_male (int)
* filter_female (int)
* filter_notset (int)
* filter_person (int)
* filter_business  (int)
* filter_city (string)
* filter_state (string)
* filter_zip (string)
*/

//get menu item ids 
global $menu_item;

//set navigation id 
$item_id = (int)$model->gscrm_territory_id;

//load acounts from cache
$cache = \JFactory::getCache('com_gscrm', '');
$cache->setCaching( 1 );
$cache_account_key = 'account_territory_'.$model->code.'_id_'.$item_id;
$accounts_cached = $cache->get($cache_account_key, 'com_gscrm_accounts');

//if cache was cleaned or expited we must run the query and cache again
$db = JFactory::getDbo();

if ( empty($accounts_cached)) {
	$select_array = array('title','gscrm_account_id', 'type', 'gender', 'email', 'phone1','territory');
	$where = $db->qn('code').' = '.$db->q($model->code).' AND '.$db->qn('enabled').' = '.$db->q('1');
	$accounts_cached = Helper::loadObjectListKey($select_array, 'accounts', $where, 'title', '1');
	$cache->store($accounts_cached, $cache_account_key, 'com_gscrm_accounts');
	}

//array names for relations select - this is the reversed relations so, names are inverted
$array_names = array(1=>"GS_REV_CORPORATION", 2=>"GS_BZ_PARTNER", 3=>"GS_REV_BZ_REPORTS_TO", 4=>"GS_REV_BZ_ASSISTANT", 5=>"GS_REV_SPONSOR", 6=>"GS_SOCIAL", 7=>"GS_PARTNER", 8=>"GS_REV_WIFE", 9=>"GS_REV_HUSBAND", 10=>"GS_REV_EXWIFE", 11=>"GS_REV_EXHUSBAND", 12=>"GS_REV_CHILDREN", 13=>"GS_REV_GRANDPARENT", 14=>"GS_REV_GRANDCHILDREN", 15=>"GS_REV_AUNT_UNCLE", 16=>"GS_REV_NEPHEW_NIECE");

//main selector form
if($item_id == 0){?><div><p><?php echo JText::_('COM_GSCRM_TERRITORIES_SELECTOR_EMPTY');}
	
else{ ?>

<div>
      <span>
      <select class="input-group ml2 high28" id="filter_account" name="filter_account" >
	      <option value="0"><?php echo JText::_('COM_GSCRM_TERRITORIES_SELECTOR_NONE') ?></option>
		  <?php
			foreach ($accounts_cached as $account)
			{ ?>			                
			<option value="<?php echo $account->gscrm_account_id ?>"><?php echo JText::_($account->title) ?></option>
			<?php } ?>
      </select>
      <select class="input-group ml2 high28" id="filter_relation" name="filter_relation" >
	      <option value="0"><?php echo JText::_('COM_GSCRM_TERRITORIES_SELECTOR_RELATIVES') ?></option>
		  <?php
			foreach ($array_names as $k => $array_name)
			{ ?>			                
			<option value="<?php echo $k ?>"><?php echo JText::_($array_name) ?></option>
			<?php } ?>
      </select>
 
	<button type="button" class="btn high28" aria-label="Left Align" title="<?php echo JText::_('GS_QUERY_TIP') ?>" onclick="Joomla.submitbutton('apply')" >
	<?php echo JText::_('GS_QUERY') ?>
	</button>
 
      </span>      
      <p><?php echo JText::_('COM_GSCRM_TERRITORIES_SELECTOR_CHOICES') ?></p>
</div>

<div class="row">
	
  <div class="col-lg-12">
    <div class="input-group">
      <span class="input-group-addon btn-primary">
      		<input name="filter_male" type="checkbox" value="1" label="<?php echo JText::_('GS_MA') ?>">
      		<span><?php echo JText::_('GS_MA') ?></span></span>
      <span class="input-group-addon btn-primary">
      		<input name="filter_female" type="checkbox" value="2" label="<?php echo JText::_('GS_FE') ?>">
      		<span><?php echo JText::_('GS_FE') ?></span></span>
      <span class="input-group-addon btn-primary">
      		<input name="filter_notset" type="checkbox" value="3" label="<?php echo JText::_('GS_NOT_SET') ?>">
      		<span><?php echo JText::_('GS_NOT_SET') ?></span></span>
      <span class="input-group-addon btn-primary">
      		<input name="filter_person" type="checkbox" value="2" label="<?php echo JText::_('GS_PE') ?>">
      		<span><?php echo JText::_('GS_PE') ?></span></span>
      <span class="input-group-addon btn-primary">
      		<input name="filter_business" type="checkbox" value="1" label="<?php echo JText::_('GS_BZ') ?>">
      		<span><?php echo JText::_('GS_BZ') ?></span></span>
      <span class="input-group-addon ">
      		<input name="filter_city" type="text" class="gs_max_width100 high28" label="<?php echo JText::_('GS_CITY') ?>">
      		<span><?php echo JText::_('GS_CITY') ?></span></span>
      <span class="input-group-addon ">
      		<input name="filter_state" type="text" class="gs_max_width100 high28" label="<?php echo JText::_('GS_STATE') ?>">
      		<span><?php echo JText::_('GS_STATE') ?></span></span>
      <span class="input-group-addon ">
      		<input name="filter_zip" type="text" class="gs_max_width100 high28" label="<?php echo JText::_('GS_ZIP') ?>">
      		<span><?php echo JText::_('GS_ZIP') ?></span></span>   
    </div><!-- /input-group -->
  </div><!-- /.col-lg-6 -->
  
</div>

<?php ;}
		//create unique cache id to recover query results
		$filter_options_name = 'filter_options_'.$model->code.'_id_'.$item_id;
		//get cached data
		$storedData = $cache->get($filter_options_name, 'com_gscrm_territory');
		
	$count = count($storedData);
	$line = 0;
	
if ( $storedData ) {	
?>
<div class="col-lg-12 mt1"><p><?php echo JText::_('COM_GSCRM_TERRITORIES_SELECTOR_LIST') ?></p></div>

<div class="col-lg-12">
	<table class="table table-condensed">
		<thead>
			<tr>
				<th><?php echo JText::_('COM_GSCRM_TERRITORIES_SELECT') ?></th>
				<th><?php echo JText::_('COM_GSCRM_ACCOUNTS_TITLE_LABEL') ?></th>
				<th><?php echo JText::_('COM_GSCRM_ACCOUNTS_EMAIL_LABEL') ?></th>
				<th><?php echo JText::_('COM_GSCRM_ACCOUNTS_TYPE_LABEL') ?></th>
				<th><?php echo JText::_('COM_GSCRM_ACCOUNTS_GENDER_LABEL') ?></th>
				<th><?php echo JText::_('GS_CITY') ?></th>
				<th><?php echo JText::_('GS_STREET') ?></th>
				<th><?php echo JText::_('GS_STATE') ?></th>
			</tr>
		</thead>
		<tbody>
			<?php 
			foreach( $storedData as $data)
			{
			if( $data == 0){ 
							//skip from table but create index
							?><input name="<?php echo 'check'.$line ?>" type="hidden" value="0" ><?php 
							$line++;
							continue;}
			?> 
			<tr>
				<th scope="row"><input name=<?php echo 'check'.$line; $line++; ?> type="checkbox" 
										value='<?php if (is_array($data)){ echo $data['account']; }else{ echo $data; } ?>'></th>
					
					<td class="text-overflow">
						<?php if (is_array($data)){ echo $accounts_cached[$data['account']]->title; }else{ echo $accounts_cached[$data]->title; } ?></td>
					
					<td><?php if (is_array($data)){ echo $accounts_cached[$data['account']]->email; }else{ echo $accounts_cached[$data]->email; } ?></td>
					
					<td><?php if (is_array($data)){ 
													if($accounts_cached[$data['account']]->type == 1 ){ echo JText::_('GS_BZ');}
													if($accounts_cached[$data['account']]->type == 2 ){ echo JText::_('GS_PE');}
							} else { 
									if($accounts_cached[$data]->type == 1 ){ echo JText::_('GS_BZ');}
									if($accounts_cached[$data]->type == 2 ){ echo JText::_('GS_PE');}
							} ?></td>
					
					<td><?php if (is_array($data)){ 
													if($accounts_cached[$data['account']]->gender == 1 ){ echo JText::_('GS_MA');}
													if($accounts_cached[$data['account']]->gender == 2 ){ echo JText::_('GS_FE');}
													if($accounts_cached[$data['account']]->gender == 0 ){ echo JText::_('GS_NOT_SET');}
							} else { 
									if($accounts_cached[$data]->gender == 1 ){ echo JText::_('GS_MA');}
									if($accounts_cached[$data]->gender == 2 ){ echo JText::_('GS_FE');}
									if($accounts_cached[$data]->gender == 0 ){ echo JText::_('GS_NOT_SET');}
							} ?></td>
					
					<td><?php if (is_array($data)){ echo $data['city']; }else{ echo 'n.a.'; } ?></td>
					<td><?php if (is_array($data)){ echo $data['state']; }else{ echo 'n.a.'; } ?></td>
					<td><?php if (is_array($data)){ echo $data['zip']; }else{ echo 'n.a.'; } ?></td>
			</tr>
			<?php ;} ?>
		</tbody>
	</table>
	<p></p>
</div>

<input name="count_lines" type="hidden" value="<?php echo $count ?>" >

<?php
	}
?>
