<?php
/*
 * @package com_gscrm
 * @copyright (c)2017 Pedro Bicudo / bicudomaschio@gmail.com
 * @license GNU General Public License version 2 or later
 */

namespace Gs\Gscrm\Site\Model;

defined('_JEXEC') or die();

// include helper file where all functions are included
use \Gs\Gscrm\Admin\Helper\Helper;
use \FOF30\Model\DataModel;
// Include necessary Joomla core
use \JFactory;
use \JDatabase;
use \JText;

class Campaigns extends \FOF30\Model\DataModel
{
    protected function onBeforeLoadForm()
    {
	    //check if the user is logged before loading any form and raise error
		if (\JFactory::getUser()->guest) 
		{ 
			$this->container->platform->raiseError(403, \JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN')); 
		}
				
		// set company code based of user id
		$this->setFieldValue('code', Helper::GetCompanyCode()->gscrm_code_id, 'int');
		                		
        parent::onBeforeLoadForm();
    }
    
    protected function onBeforeCheck()
    {   
		//we will use $db->qn and $db->q
		$db = $this->getDbo();
		
		//we want to control if clear cache is necessary (0 = no need)
		$control_clear_cache = 0;
		
		//get user request to update cache
		if ($this->getState('update_list', null, 'int') ) { $control_clear_cache = 1; }
		
		//resulting id list
		$filter_list = array();	
		
		$filter_account = $this->getState('filter_account', null, 'int');
		$filter_relation = $this->getState('filter_relation', null, 'int');	
		
		//add first account according to user select
		$filter_list[] = $filter_account;		
		
		//get relations for this account and add to filter list (query key-table)		
		if( $filter_account > 0 && $filter_relation > 0)
		{
			$filter_query = $db->qn('child').' = '.$db->q($filter_account).' AND '.$db->qn('title').' = '.$db->q($filter_relation);
													
			$relations_array = Helper::GetIdArrayFilter('#__gscrm_relations', 'parent', $filter_query);
	
			//add relations to filter (yes, we may have several, i.e.: account is father and has many children)
			foreach ( $relations_array as $relation)
			{
				$filter_list[] = $relation;
			}	
		}

		//get other fields for filter
		$if_control = 0;
		if( $this->getState('filter_male', null, 'int'))	{ $filter_male 		= $this->getState('filter_male', null, 'int'); $if_control = 1;}
		if( $this->getState('filter_female', null, 'int'))	{ $filter_female	= $this->getState('filter_female', null, 'int'); $if_control = 1;}
		if( $this->getState('filter_notset', null, 'int'))	{ $filter_notset	= $this->getState('filter_notset', null, 'int'); $if_control = 1;}
		if( $this->getState('filter_person', null, 'int'))	{ $filter_person	= $this->getState('filter_person', null, 'int'); $if_control = 1;}
		if( $this->getState('filter_business', null, 'int')){ $filter_business= $this->getState('filter_business', null, 'int'); $if_control = 1;}
		
		if ( $if_control == 1)
		{
		//get cached accounts to search for male/female/person/business ... skip one large query using cached array
		$cache = \JFactory::getCache('com_gscrm', '');
		$cache->setCaching( 1 );
		$cache_account_key = 'account_campaign_'.$this->getState('code', null, 'int').'_id_'.$this->getState('gscrm_campaign_id', null, 'int');
		$accounts_cached = $cache->get($cache_account_key, 'com_gscrm_accounts');	

		//if cache was cleaned or expited we must run the query and cache again
		if ( empty($accounts_cached)) {
			$select_array = array('title','gscrm_account_id', 'type', 'gender', 'email', 'phone1');
			$where = $db->qn('code').' = '.$db->q($this->getState('code', null, 'int')).' AND '.$db->qn('enabled').' = '.$db->q('1');
			$accounts_cached = Helper::loadObjectListKey($select_array, 'accounts', $where, 'title', '1');
			$cache->store($accounts_cached, $cache_account_key, 'com_gscrm_accounts');
			}	
		
		//loop through accounts for type & gender
			foreach ($accounts_cached as $account )
			{
				if( $filter_male == 1 && $account->gender == 1 )	{ $filter_list[] = $account->gscrm_account_id;}
				if( $filter_female == 2 && $account->gender == 2 )	{ $filter_list[] = $account->gscrm_account_id;}
				if( $filter_notset == 3 && $account->gender == 0 )	{ $filter_list[] = $account->gscrm_account_id;}
				if( $filter_person == 2 && $account->type == 2)		{ $filter_list[] = $account->gscrm_account_id;}
				if( $filter_business == 1 && $account->type == 1)	{ $filter_list[] = $account->gscrm_account_id;}
			;}
		;} 
	//end if filter male/female/other/person/business

		//get addresses and return account_ids according to city OR state OR ZIP (query address table)
		$filter_city =	$this->getState('filter_city', null, 'string');
		$filter_state=	$this->getState('filter_state', null, 'string');
		$filter_zip =	$this->getState('filter_zip', null, 'string');
		
		//query select that will be used:
		//$select = array('account', 'city', 'state', 'zip');	
		$select = array('account', 'city', 'state', 'zip');
	/* 
	*	ZIP can be a straignt number like this example ... and it is just a  umber in most cases
	*	Borough		/ Neighborhood 					/ ZIP Codes
	*	Manhattan	Chelsea and Clinton				10001, 10011, 10018, 10019, 10020, 10036
	*	Manhattan	East Harlem						10029, 10035
	*	Manhattan	Gramercy Park and Murray Hill	10010, 10016, 10017, 10022
	*	Manhattan	Greenwich Village and Soho		10012, 10013, 10014
	*
	*	In this example, if we search from 9900 to 11000 we get all of Manhatan ZIP codes. 
	*	If not a number, we will query exactly the typed filter
	*/
		if( !empty($filter_zip) && is_numeric($filter_zip) )
		{		
			$filter_zip_min = $filter_zip - 100;
			$filter_zip_max = $filter_zip + 100;
			
			//the desired $db->qn does not work in this case: $Q1 = $db->qn('zip').' BETWEEN '.$db->q($filter_zip_min).' AND '.$db->q($filter_zip_max);
			$Q1 = 'zip BETWEEN '.$filter_zip_min.' AND '.$filter_zip_max;
			
			if( !empty($filter_city) ) { $Q2 = ' OR '.$db->qn('city').' LIKE '.$db->q($db->escape('%'.$filter_city.'%') ).' ';}else {$Q2 = '';}
			
			if( !empty($filter_state) ){ $Q3 = ' OR '.$db->qn('state').' LIKE '.$db->q($db->escape('%'.$filter_state.'%') ).' ';}else {$Q3 = '';}
							
			$filter_query = $Q1.$Q2.$Q3;
			
			$addresses = Helper::ShowAddressesFilter($select, $filter_query);
		} 
		// if ZIP is not a number
		if( !empty($filter_zip) && !is_numeric($filter_zip) )
		{					
			$Q1 = $db->qn('zip').' LIKE '.$db->q($db->escape('%'.$filter_zip.'%') );
			
			if( !empty($filter_city) ) { $Q2 = ' OR '.$db->qn('city').' LIKE '.$db->q($db->escape('%'.$filter_city.'%') ).' ';}else {$Q2 = '';}
			
			if( !empty($filter_state) ){ $Q3 = ' OR '.$db->qn('state').' LIKE '.$db->q($db->escape('%'.$filter_state.'%') ).' ';}else {$Q3 = '';}
							
			$filter_query = $Q1.$Q2.$Q3;
			
			$addresses = Helper::ShowAddressesFilter($select, $filter_query);
		} 
		// if no zip provided
		if( empty($filter_zip) )
		{					
			if( !empty($filter_city) ) { $Q2 = $db->qn('city').' LIKE '.$db->q($db->escape('%'.$filter_city.'%') ).' ';}else {$Q2 = '';}
			
			if( !empty($filter_state) ){ $Q3 = $db->qn('state').' LIKE '.$db->q($db->escape('%'.$filter_state.'%') ).' ';}else {$Q3 = '';}
							
			if( !empty($filter_city) && !empty($filter_state) ) { $addresses = Helper::ShowAddressesFilter($select, $Q2.' OR '.$Q3); }
			
			if( empty($filter_city) && !empty($filter_state) ) { $addresses = Helper::ShowAddressesFilter($select, $Q3); }
			
			if( !empty($filter_city) && empty($filter_state) ) { $addresses = Helper::ShowAddressesFilter($select, $Q2); }
		} 				
		// now we pass address array into filter list
		if( !empty($addresses) )
		{
			foreach ($addresses as $key => $address)
			{
				$filter_list[] = $address;
			}		
		}

		if( !empty($filter_list)) 
		{
		$cache = \JFactory::getCache('com_gscrm_campaign', '');
		$cache->setCaching( 1 );	
		//create unique cache id
		$filter_options_name = 'filter_options_'.$this->getState('code', null, 'int').'_id_'.$this->getState('gscrm_campaign_id', null, 'int');
		//cache filter result to load into campaign form (this is not the final result, that is why it is not saved into the database)
		$cache->store($filter_list, $filter_options_name, 'com_gscrm_campaign');
		}	

		if ($this->getState('count_lines', null, 'int'))
		{		
		//after user checked the names for the campaign, we can save the account list in the relations table
	    $count_lines = $this->getState('count_lines', null, 'int');
	    for ($i = 0; $i <= $count_lines; $i++ )
			{
				$line_name = 'check'.$i;
				$line_value = $this->getState($line_name, null, 'int');			
				//update relationship
				if($line_value > 0) 
				{
					//save territory to account data - UpdateField($tablename, $item_id, $update_field, $update_value)
					Helper::UpdateField('accounts', $line_value, 'campaign', $this->getState('gscrm_campaign_id', null, 'int'));
					
					//we saved a new account to the list -> need to reset cache
					$control_clear_cache = 1;			
				}
			}
		}	
		
		//create new note
		if($this->getState('new_note', null, 'string')) 
		{
			$idx = $this->getState('id_for_new_note', null, 'int');		
			//all notes from campaigns are flagged type 1 = lead or cold call | QuickCreateNote($note, $type, $account, $source, $source_id)
			Helper::QuickCreateNote( $this->getState('new_note', null, 'string'), '1', $idx, '0', '0' );
		}	
		//set cache reminder of index point
		if($this->getState('index_for_note', null, 'int'))
		{
			$cache = \JFactory::getCache('com_gscrm', '');
			$cache->setCaching( 1 );
			$cache_index_Key = 'campaign_index_'.$this->getState('code', null, 'int').'_id_'.$this->getState('gscrm_campaign_id', null, 'int');			
			$cache->store($this->getState('index_for_note', null, 'string'), $cache_index_Key, 'com_gscrm_campaign');
		}
		
		//clear cache when necessary
		if ( $control_clear_cache > 0 )
		{	
			// joomla remove(string $id, string $group = null) : boolean			
			$cache = \JFactory::getCache('com_gscrm_campaign', '');
			
			//clean list
			$cache_Key = 'campaign_listed_'.$this->getState('code', null, 'int').'_id_'.$this->getState('gscrm_campaign_id', null, 'int');
			$cache->remove($cache_Key, 'com_gscrm_campaign');
			
			//clean index
			$cache_Key = 'campaign_index_'.$this->getState('code', null, 'int').'_id_'.$this->getState('gscrm_campaign_id', null, 'int');
			$cache->remove($cache_Key, 'com_gscrm_campaign');
			
			//clean accounts
			$cache = \JFactory::getCache('com_gscrm_accounts', '');
			$cache_Key = 'account_campaign_'.$this->getState('code', null, 'int').'_id_'.$this->getState('gscrm_campaign_id', null, 'int');
			$cache->remove($cache_Key, 'com_gscrm_accounts');
		}		
		
		parent::onBeforeCheck();
	}	
		
	public function buildQuery($override = false)
	{
        $db = $this->getDbo();
		$query = parent::buildQuery($override);
		
		//first get company code from user id to filter
		$code = Helper::GetCompanyCode()->gscrm_code_id;		
		if ($code == 0){			
			JFactory::getApplication()->enqueueMessage(JText::_('GS_NO_CODE') );
			$query->where($db->qn('code').' = '.$db->q($code));
					
			} else { $query->where($db->qn('code').' = '.$db->q($code)); }

        if($field1 = $this->getState('title', null, 'string'))
        {
            $query->where($db->qn('title').' LIKE '.$db->q($db->escape('%'.$field1.'%')) );
        }                          
        if($field4 = $this->getState('currency', null, 'int'))
        {
            $query->where($db->qn('currency').' = '.$db->q($field4));
        }                    
        
        //F0F cannot filter zero, we need a workaround: query not equal and input filter as text      
        if($this->getState('enabled', null, 'string'))
        {
	        //get languages for Yes/No, it will return lowercase 'yes' ou 'no' or 'error' if finds no match. '1' returns as yes; 'zero' and null returns as 'no'
	        $keyword = Helper::YesOrNo($this->getState('enabled', null, 'string'));
	        
	        switch ($keyword)
	        {	        
		        case 'no' : $query->where($db->qn('enabled').' <> '.$db->q('1')); Break;
		        
		        case 'yes': $query->where($db->qn('enabled').' = '.$db->q('1')); Break;		        
		         
		        default: $query->where($db->qn('enabled').' = '.$db->q('2')); //this will force no result in any other case
	        }
	    }                     
        return $query;        
	}	
}
