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
use \FOF30\Inflector\Inflector;
// Include necessary Joomla core
use \JFactory;
use \JDatabase;
use \JText;

class Orders extends \FOF30\Model\DataModel
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
			
	    //get parsed account id for new order
	    $jinput = \JFactory::getApplication()->input; 

	    //get parsed opportunity data for new order
        if ($jinput->get('opps', null, 'string'))
        {   
	        $opps_array = base64_decode($jinput->get('opps', null, 'string'));
	        $opps_data = preg_split('[\;]', $opps_array);
	        $this->setFieldValue('title', $opps_data[0]);
	        $this->setFieldValue('notes', $opps_data[1]); 
	        $this->setFieldValue('currency', $opps_data[2]);
	        $this->setFieldValue('value', $opps_data[3]);
	        $this->setFieldValue('account', $opps_data[4]);
	        $this->setFieldValue('company', $opps_data[5]);
	        $this->setFieldValue('opportunity', $opps_data[6]);
        } 	    

        //set referenced note id to new order
        if ($jinput->get('note', null, 'int'))
        {   
	        $this->setFieldValue('note', $jinput->get('note', null, 'int')); 
        }
        //set referenced opportunity id to new order
        if ($jinput->get('opportunity', null, 'int'))
        {   
	        $this->setFieldValue('opportunity', $jinput->get('opportunity', null, 'int')); 
        }          
        //set referenced account id to new order 
        if ($jinput->get('account', null, 'int'))
        {       
	        $account_id = $jinput->get('account', null, 'int');
	        $this->setFieldValue('account', $account_id); 
	        
	        //check company name related to this account and set company id field
	        $company_id = Helper::QueryData('company', '#__gscrm_accounts', 'gscrm_account_id', $account_id);
	        if( $company_id == 0){$company_id = $account_id;}  //option if you want to force company name when no person account exists
			$this->setFieldValue('company', $company_id);
			
			//set type business or person opportunity
			$this->setFieldValue('type', $jinput->get('type', null, 'int'));	        
        }              
        //set referenced contract id to new order 
        if ($jinput->get('contract', null, 'int'))
        {   
	        $this->setFieldValue('contract', $jinput->get('contract', null, 'int')); 
        }                
        //set flag to hide/show closed items
        if ($jinput->get('nt_show', null, 'int'))
        {   
	        $this->setFieldValue('nt_show', $jinput->get('nt_show', null, 'int'));
        } 

        //change closed/open note on click
        if ($jinput->get('flagchg', null, 'int'))
        { 
			//if flagchg 2 -> change from 1 to zero
	        if($jinput->get('flagchg', null, 'int') == 2) { Helper::UpdateField('notes', $jinput->get('Chgnum', null, 'int'), 'enabled', 0); }
	        
			//if flagchg 1 -> change from zero to 1
	        if($jinput->get('flagchg', null, 'int') == 1) { Helper::UpdateField('notes', $jinput->get('Chgnum', null, 'int'), 'enabled', 1); }	
        }  
        //set flag to show business account
        if ($jinput->get('show_bz', null, 'int'))
        {   
	        $this->setFieldValue('show_bz', $jinput->get('show_bz', null, 'int'));
        }         
                		
        parent::onBeforeLoadForm();
    }
    
    protected function onBeforeCheck()
    {   
		//set owner from user assignment on beads table. Owner is who will handle the order and not who created the item
		if( $this->getState('owner', null, 'int') < 1) 
		{ 
			$this->setFieldValue('owner', Helper::GetBead());
		}
		
		$new_account = $this->getState('new_name_account', null, 'string');
		$account = $this->getState('account', null, 'int');
			
		if($new_account)
		{
			//create new account from new note page; it is a person (type=2) since contacts are always within persons
			$account = Helper::QuickCreateAccount($new_account, 2);
			$this->setFieldValue('account', $account);		
		}	

		if( empty($new_account) && empty($account))
		{							
			//raise error if there is no account on save
			JFactory::getApplication()->enqueueMessage(JText::_('COM_GSCRM_NOTE_ERR_ACCOUNT_EMPTY'), error);
			return false;
		}
		
		if($this->getState('new_note', null, 'string')) 
		{
			$type = 5; //all notes from orders are flagged 5 INQUIRY_CONTRACT
			$new_id = Helper::QuickCreateNote($this->getState('new_note', null, 'string'), $type, $account, 'orders', $this->getState('gscrm_order_id', null, 'int'));
			$this->setFieldValue('note', $new_id);
		}
		//set order closed if selected is service status 5
		if($this->getState('service', null, 'int') == 5 )
		{
			$this->setFieldValue('enabled', '0');
		}
		//set order open if selected in service status (returned goods or correction to service)
		if($this->getState('service', null, 'int') == 6 )
		{
			$this->setFieldValue('enabled', '1');
		}			
		
		//order number is YEAR-MONTH-DAY-NOTE_NUMBER to easy indicate the user what date created and note(log) related 
		if($this->getState('number', null, 'string') == 1)
		{
			//if type=business, do we have the business id ?
			if( $this->getState('type', null, 'int') == 1)
			{
					$type = 'Bz';
					$check_bz = Helper::QueryData('company', '#__gscrm_accounts', 'gscrm_account_id', $account);
					if($check_bz<1){
					//raise message if there is no business account on save and deal type is business
					JFactory::getApplication()->enqueueMessage(JText::_('COM_GSCRM_NOTE_ERR_NO_BZ_ACC'), error);
					return false;	
					}						
			
			} else { $type = 'Pe';}		
			
			$date = JFactory::getDate();
			$date_year = substr($date->year, 2, 4);
			$date_month = $date->month;
			$date_day = $date->day;
			//guess next id on save; assuming orders cannot be deleted this is OK
			$guess_id = Helper::NextId('orders');
			
			$ordernumber = $date_year.$date_month.$date_day.'.'.$guess_id;
			
			//set field value that will be used on save item
			$this->setFieldValue('number', $ordernumber);
			
			//set new value to model state, so that we can read it after save
			$this->setState('number', $ordernumber, 'int');
			
			//set new value to model state, this flags that this is form add
			$this->setState('start_save', '1', 'int');
		}

		//get document data for name/address 
			$doc_data = Helper::DocData( $account, $this->getState('type', null, 'int') );		
		
		//create invoice data			
			$lognote =	array(	
							'type' => $this->getState('type', null, 'int'), 
							'title' => 		$this->getState('title', null, 'string'),							 
							'acc_ttl' => 	$doc_data['acc_ttl'], 
							'acc_uid' => 	$doc_data['acc_uid'],
							'acc_num' => 	$doc_data['acc_num'],
							'acc_str' => 	$doc_data['acc_str'],
							'acc_city' => 	$doc_data['acc_city'],
							'acc_st' => 	$doc_data['acc_st'],
							'acc_zip' => 	$doc_data['acc_zip'],
							'acc_ctry' => 	$doc_data['acc_ctry'],
							'bz_ttl' => 	$doc_data['bz_ttl'],
							'bz_uid' => 	$doc_data['bz_uid'],
							'bz_num' => 	$doc_data['bz_num'],
							'bz_str' => 	$doc_data['bz_str'],
							'bz_adtl' => 	$doc_data['bz_adtl'],
							'bz_city' => 	$doc_data['bz_city'],
							'bz_st' => 		$doc_data['bz_st'],
							'bz_zip' => 	$doc_data['bz_zip'],
							'bz_ctry' => 	$doc_data['bz_ctry'],
							'currency' => 	$this->getState('currency', null, 'int'), 
							'value' => 		$this->getState('value', null, 'string'),
							'owner' => 		$this->getState('owner', null, 'string') );

			//convert array to Json
			$log = json_encode($lognote);
			//set for save
			$this->setFieldValue('params', $log);		

		parent::onBeforeCheck();
	}
	
	protected function onAfterSave()
	{
		//check if this is the first time we save this order to create log
		if( $this->getState('start_save', null, 'int') == 1)
		{
			//get new created id
			$db = JFactory::getDbo();
			$acc_id = $db->insertid();
			
			//recover order number from model state
			$new_num = $this->getState('number', null, 'int');
			
			//log note  ... needs improvement, this method is no good enough
			$log_title = JText::_('GS_LOG_ORDER').' #'.$this->getState('number', null, 'string').'-'.$this->getState('title', null, 'string');
			
			if($this->getState('type', null, 'int') == 1) {$type = 'Bz';} else { $type = 'Pe';}
			
			Helper::CreateNote($log_title, $type.' | '.$log_title, '99', $this->getState('account', null, 'int'), 'orders', $acc_id);
			
			//set note closed
			if( $this->getState('note', null, 'int'))
			{
				Helper::UpdateField('notes', $this->getState('note', null, 'int'), 'enabled', '0');
			}
									
		}	
		
		parent::onAfterSave();
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

		//prepare array key-for-stages (related table will be done in future releases)
		$array_types = array(
				1=>JText::_('COM_GSCRM_ORDERS_ST1'),	
				2=>JText::_('COM_GSCRM_ORDERS_ST2'),
				3=>JText::_('COM_GSCRM_ORDERS_ST3'),
				4=>JText::_('COM_GSCRM_ORDERS_ST4'),			
				5=>JText::_('COM_GSCRM_ORDERS_ST5'),		
				6=>JText::_('COM_GSCRM_ORDERS_ST6') );			

        //Service by name or id:
        $field3 = $this->getState('service', null, 'string');                          
		// if filter is not a number, find its id
        if ( !is_numeric($field3) && !empty($field3))
        {
	        $type_id = Helper::ArrayFind($field3, $array_types);
            $query->where($db->qn('service').' = '.$db->q($type_id));
        }
        //if filter is a number, taken as id
        if ( is_numeric($field3) && !empty($field3) )
        {
           $type_id = (int)$field3;
           $query->where($db->qn('service').' = '.$db->q($type_id));
        }
		
        if($field1 = $this->getState('title', null, 'string'))
        {
            $query->where($db->qn('title').' LIKE '.$db->q($db->escape('%'.$field1.'%')) );
        } 
        if($field2 = $this->getState('notes', null, 'string'))
        {
            $query->where($db->qn('notes').' LIKE '.$db->q($db->escape('%'.$field2.'%')) );
        }                            
        if($field4 = $this->getState('currency', null, 'int'))
        {
            $query->where($db->qn('currency').' = '.$db->q($field4));
        }                    
            
        //ACCOUNT by name or id
        $field5 = $this->getState('account', null, 'string');                          
       // user filter is not a number, find its id
        if ( !is_numeric($field5) && !empty($field5))
        {
	        $account = Helper::QueryLike('gscrm_account_id', '#__gscrm_accounts', 'title', $field5);
	        
	        //the query may be null for account but ok for company
            $query->where($db->qn('account').' = '.$db->q($account).' OR '.$db->qn('company').' = '.$db->q($account));
        }
        
        //user filter is a number, taken as id 
        if ( is_numeric($field5) && !empty($field5) )
        {
           $account = (int)$field5;
           $query->where($db->qn('account').' = '.$db->q($account).' OR '.$db->qn('company').' = '.$db->q($account));
        }             

        if($field6 = $this->getState('invoice', null, 'int'))
        {
            $query->where($db->qn('invoice').' = '.$db->q($field6));
        }
        if($field7 = $this->getState('contract', null, 'int'))
        {
            $query->where($db->qn('contract').' = '.$db->q($field7));
        }
        if($field8 = $this->getState('owner', null, 'int'))
        {
            $query->where($db->qn('owner').' = '.$db->q($field8));
        }        
        if($field9 = $this->getState('note', null, 'int'))
        {
            $query->where($db->qn('note').' = '.$db->q($field9));
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