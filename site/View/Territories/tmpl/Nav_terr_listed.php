<?php
/*
 * @package com_gscrm
 * @copyright (c)2017 Pedro L Bicudo Maschio / bicudomaschio@gmail.com
 * @license GNU General Public License version 2 or later
 */

namespace Gs\Gscrm\Site\View\Nav_terr_listed;

//navigate through accounts listed in a territory
//uses cache because this form is re-loaded every time the user press next or previous item

use \JText;
use \JFactory;
use \JSession;
use \Gs\Gscrm\Admin\Helper\Helper;

// no direct access
defined('_JEXEC') or die('Restricted access');

//get menu item ids 
global $menu_item;

//set navigation id 
$item_id = (int)$model->gscrm_territory_id;
$index = 0;

//skip if comapaign has not been created
if($item_id == 0){ ?><div><p><?php echo JText::_('COM_GSCRM_TERRITORIES_NAV_EMPTY') ?></p></div><?php ;} 	
else {

//get list from cache or database
$cache = \JFactory::getCache('com_gscrm_territory', '');
$cache->setCaching( 1 );

//get index from cache if exist
$cache_index_Key = 'territory_index_'.$model->code.'_id_'.$item_id;
$index = $cache->get($cache_index_Key, 'com_gscrm_territory');

//get index from cache if exist
$cache_listed_key = 'territory_listed_'.$model->code.'_id_'.$item_id;
$territory = array();
$territory = $cache->get($cache_listed_key, 'com_gscrm_territory');

//get accounts
$cache_account_key = 'account_territory_'.$model->code.'_id_'.$item_id;
$accounts_cached = $cache->get($cache_account_key, 'com_gscrm_accounts');

//if cache was cleaned or expited we must run the query and cache again
$db = JFactory::getDbo();

if ( empty($accounts_cached)) {
	$select_array = array('title','gscrm_account_id', 'type', 'gender', 'email', 'phone1', 'territory');
	$where = $db->qn('code').' = '.$db->q($model->code).' AND '.$db->qn('enabled').' = '.$db->q('1');
	$accounts_cached = Helper::loadObjectListKey($select_array, 'accounts', $where, 'title', '1');
	$cache->store($accounts_cached, $cache_account_key, 'com_gscrm_accounts');
	}
	
if ( empty($territory)) {
	//search for list
	foreach ( $accounts_cached as $account_ref)
	{
		if( $account_ref->territory == $item_id){ 
			$territory[] =  $account_ref->gscrm_account_id;
			}
	}
	$cache->store($territory, $cache_listed_key, 'com_gscrm_territory');
	}			
//set navigation indexes
$index_max = count($territory)-1;

//skip if a list has not been saved
if(!$territory){ ?><div><p><?php echo JText::_('COM_GSCRM_TERRITORIES_NAV_EMPTY') ?></p></div><?php ;} 	
else {

//get account index from URL and set current account to show
$jinput = \JFactory::getApplication()->input;
if ($jinput->get('index', null, 'int')) { $index = $jinput->get('index', null, 'int'); }

if( $index > 1 ) { $prev_item = $index - 1; }else{ $prev_item = 0; }
if( $index < $index_max ){ $next_item = $index + 1; }else{ $next_item = $index_max; }

$account = $territory[$index];

//LastComment($source, $source_id);
$last_note = Helper::LastComment('account', $account);

?>	
<div class="btn-group col-sm-12" role="group" aria-label="...">
	
<a role="button" class="btn col-sm-2" title="<?php echo JText::_('GS_TIP_PREV') ?>"
		<?php 	if ($index > 0) { ?>href="index.php?option=com_gscrm&view=Territory&id=<?php echo $item_id; ?>&Itemid=<?php echo $menu_item['territory'] ?>&index=<?php echo $prev_item ?>&<?php echo JSession::getFormToken() ?>=1" >
	<i class="glyphicon glyphicon-chevron-left"></i><?php echo JText::_('GS_PREV') ?></a><?php ;}else{ ?> > <?php echo '#1' ?></a><?php ;} ?>
	
<a role="button" class="btn col-sm-8 btn-primary text-overflow" title=" "
	href="index.php?option=com_gscrm&view=Account&id=<?php echo $account ?>&Itemid=<?php echo $menu_item['accounts']?>&<?php echo JSession::getFormToken() ?>=1" >
	<?php echo $accounts_cached[$account]->title ?></a>
	
<a role="button" class="btn col-sm-2" title="<?php echo JText::_('GS_TIP_NEXT') ?>"
		<?php  if ($index < $index_max) { ?> href="index.php?option=com_gscrm&view=Territory&id=<?php echo $item_id; ?>&Itemid=<?php echo $menu_item['territory'] ?>&index=<?php echo $next_item ?>&<?php echo JSession::getFormToken() ?>=1" >
	<i class="glyphicon glyphicon-chevron-right"></i><?php echo JText::_('GS_NEXT') ?></a><?php ;}else{ ?> > <?php echo 'O' ?></a><?php ;} ?>	
			
</div>	

<div class="col-lg-12">
	<table class="table table-condensed">
		<thead>
			<tr>
				<th>#</th>
				<th><?php echo JText::_('COM_GSCRM_ACCOUNTS_EMAIL_LABEL') ?></th>
				<th><?php echo JText::_('COM_GSCRM_ACCOUNTS_PHONE_LABEL') ?></th>
			</tr>
		</thead>
		<tbody>			
			<tr>
				<th scope="row"><?php echo ($index+1).'/'.($index_max+1) ?></th>					
					
					<td><?php
							//if is not email, give link 
							if (strpos( $accounts_cached[$account]->email, '@') == FALSE) { 
									?><a href="http://<?php echo $accounts_cached[$account]->email ?>"> <?php ;}
							else { 
									?><a href="mailto:<?php echo $accounts_cached[$account]->email ?>"> <?php ;}
						
						echo $accounts_cached[$account]->email; ?></a></td>	
						
					<td><?php echo $accounts_cached[$account]->phone1; ?></td>
			</tr>
		</tbody>
	</table>
</div>

<div class="col-lg-12">
	
<!-- 	links to lists -->
	<div class="btn-group">
	<button type="button" class="btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	<!--  <span class="caret"></span> -->
	<i class="glyphicon glyphicon-eye-open"></i>
	</button>
		<ul class="dropdown-menu">
			<li><a href="index.php?option=com_gscrm&view=Opportunities&account=<?php echo $account ?>&Itemid=<?php echo $menu_item['opportunities'] 
				?>&<?php echo JSession::getFormToken() ?>=1">
				<?php echo JText::_('GS_SEE_OPPORTUNITIES') ?></a></li>
			
			<li><a href="index.php?option=com_gscrm&view=Orders&account=<?php echo $account ?>&Itemid=<?php echo $menu_item['orders'] 
				?>&<?php echo JSession::getFormToken() ?>=1">
				<?php echo JText::_('GS_SEE_ORDERS') ?></a></li>
							
			<li><a href="index.php?option=com_gscrm&view=Notes&account=<?php echo $account ?>&Itemid=<?php echo $menu_item['notes'] 
				?>&<?php echo JSession::getFormToken() ?>=1">
				<?php echo JText::_('GS_SEE_NOTES') ?></a></li>
  		</ul>
	</div>	
	
<!-- 	links to orders / opportunities -->
	
	<a class="btn-xs" href="index.php?option=com_gscrm&view=Opportunity&account=<?php echo $account 
		?>&Itemid=<?php echo $menu_item['opportunity'] ?>&<?php echo JSession::getFormToken() ?>=1"
		title="<?php echo ' '.JText::_('COM_GSCRM_OPPORTUNITIES_CREATE') ?>" >
		<i class="glyphicon glyphicon-pushpin"></i></a>	
		
	<a class="btn-xs" href="index.php?option=com_gscrm&view=Order&account=<?php echo $account ?>&type=<?php echo $accounts_cached[$account]->type 
		?>&Itemid=<?php echo $menu_item['order'] ?>&<?php echo JSession::getFormToken() ?>=1"
		title="<?php echo ' '.JText::_('COM_GSCRM_ORDERS_CREATE') ?>" >
		<i class="glyphicon glyphicon-shopping-cart"></i></a>	
		
	<p class="high10"><?php echo JText::_('GS_LAST_NOTE') ?>:</p>
	
</div>
<div class="col-lg-12 mb2">

	<p class="high28 gs_border"><?php echo $last_note ?></p>

	<p><input name="new_note" value="1" type="checkbox" class="checkbox-inline high10"> <?php echo JText::_('GS_SAVE_NEW_NOTE') ?>

	<p><input class="high28 col-lg-12 pt0" name="new_note" type="text "></p>
	
	<input type="hidden" name="id_for_new_note" value="<?php echo $account ?>">
	
	<input type="hidden" name="index_for_note" value="<?php echo $index ?>">

</div>
<!-- footnote -->
<div class="col-lg-12">
	<p><input name="update_list" value="1" type="checkbox" class="checkbox-inline"> <?php echo JText::_('GS_CLEAN_CACHE') ?></p>
</div>

<?php 
	;} // if index_max > 0
	;} // if item_id >0