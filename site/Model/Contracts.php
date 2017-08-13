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
use JFactory;
use JDatabase;
use JText;

class Contracts extends \FOF30\Model\DataModel
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
			
	    //get parsed account id for new opportunity
	    $jinput = \JFactory::getApplication()->input; 

        //set referenced note id to new contract
        if ($jinput->get('note', null, 'int'))
        {   
	        $this->setFieldValue('note', $jinput->get('note', null, 'int')); 
        } 
        //set referenced account id to new contract 
        if ($jinput->get('account', null, 'int'))
        {   
	        $this->setFieldValue('account', $jinput->get('account', null, 'int')); 
        }              
         //set referenced invoice id to new contract 
        if ($jinput->get('invoice', null, 'int'))
        {   
	        $this->setFieldValue('invoice', $jinput->get('invoice', null, 'int')); 
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
		//set owner from user assignment on beads table. Owner is who will handle the contract and not who created this item
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
			$type = 5; //all notes from contract forms are flagged 5 INQUIRY_CONTRACT
			$new_id = Helper::QuickCreateNote($this->getState('new_note', null, 'string'), $type, $account, 'contracts', $this->getState('gscrm_contract_id', null, 'int'));
			$this->setFieldValue('note', $new_id);
		}
		
		//contract number is UNIX time
		if($this->getState('number', null, 'int') == 1)
		{
			$date = JFactory::getDate();
			$contractnumber = $date->toUnix();
			
			//set field value that will be used on save item
			$this->setFieldValue('number', $contractnumber);
			
			//set new value to model state, so that we can read it after save
			$this->setState('number', $contractnumber, 'int');
			
			//set new value to model state, this flags that this is form add
			$this->setState('start_save', '1', 'int');	
		}
	
		parent::onBeforeCheck();
	}
	
	protected function onAfterSave()
	{
		//check if this is the first time we save this contract
		if( $this->getState('start_save', null, 'int') == 1)
		{
			//get new created id
			$db = JFactory::getDbo();
			$acc_id = $db->insertid();
			
			//recover contract number from model state
			$new_num = $this->getState('number', null, 'int');
			
			//log note ... needs to be develped, this method is no good
			$log_title = JText::_('GS_LOG_CONTRACT').' #'.$this->getState('number', null, 'int').'-'.$this->getState('title', null, 'string');
			
			if($this->getState('type', null, 'int') == 1) {$type = 'Bz';} else { $type = 'Pe';}
			
			$lognote =	$type.'|'.$this->getState('title', null, 'string').'|AC('.$this->getState('account', null, 'int').')'.$this->getState('pe_acc', null, 'string').
						'|adr1('.$this->getState('addr1_id', null, 'int').')BZ('.$this->getState('bz_acc_id', null, 'int').')'.$this->getState('bz_acc', null, 'string').
						'|adr2('.$this->getState('addr2_id', null, 'int').')CV('.$this->getState('currency', null, 'int').')'.$this->getState('value', null, 'string').
						'|SD'.$this->getState('sdate', null, 'string').'|ED'.$this->getState('edate', null, 'string').'|Ow-'.$this->getState('owner', null, 'string').'|';
			
			
			Helper::CreateNote($log_title, $lognote, '99', $this->getState('account', null, 'int'), 'contracts', $acc_id);						
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
						
		//get fields for query
		
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
            $query->where($db->qn('account').' = '.$db->q($account));
        }
        //user filter is a number, taken as id
        if ( is_numeric($field5) && !empty($field5) )
        {
           $account = (int)$field5;
           $query->where($db->qn('account').' = '.$db->q($account));
        }             

        if($field6 = $this->getState('invoice', null, 'int'))
        {
            $query->where($db->qn('invoice').' = '.$db->q($field6));
        }
        if($field7 = $this->getState('order_id', null, 'int'))
        {
            $query->where($db->qn('order_id').' = '.$db->q($field7));
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