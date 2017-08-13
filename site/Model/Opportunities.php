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

class Opportunities extends \FOF30\Model\DataModel
{
    /**
     * onBeforeLoadForm function.
     * 
     * @access protected
     * @return void
     */
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

        //set referenced note id to new opportunity
        if ($jinput->get('note', null, 'int'))
        {   
	        $this->setFieldValue('note', $jinput->get('note', null, 'int')); 
        } 
        //set referenced account id to new opportunity 
        if ($jinput->get('account', null, 'int'))
        {   
	        $account_id = $jinput->get('account', null, 'int');
	        $this->setFieldValue('account', $account_id); 
	        
	        //check company name related to this account and set company id field
	        $company_id = Helper::QueryData('company', '#__gscrm_accounts', 'gscrm_account_id', $account_id);
	        if( $company_id == 0){$company_id = $account_id;}  //option if you want to force company name when no person account exists
			$this->setFieldValue('company', $company_id);
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
              		
        parent::onBeforeLoadForm();
    }
    
    /**
     * onBeforeCheck function.
     * 
     * @access protected
     * @return void
     */
    protected function onBeforeCheck()
    {   
		//set owner from user assignment on beads table. Owner is who will handle the opportunity and not who created the note
		if( $this->getState('owner', null, 'int') < 1) 
		{ 
			$this->setFieldValue('owner', Helper::GetBead());
		}
		
		$new_account = $this->getState('new_name_account', null, 'string');
		$account = $this->getState('account', null, 'int');
			
		if($new_account)
		{
			//create new account from new note page; it is a person (type=2) since contacts are always within persons
			$new_name_account_id = Helper::QuickCreateAccount($new_account, 2);
			$this->setFieldValue('account', $new_name_account_id);			
		}	

		if( empty($new_account) && empty($account))
		{							
			//raise error if there is no account on save
			\JFactory::getApplication()->enqueueMessage(JText::_('COM_GSCRM_NOTE_ERR_ACCOUNT_EMPTY'), error);
			return false;
		}
		
		if($this->getState('new_note', null, 'string')) 
		{
			$type = 2; //all notes from opportunities are flagged 2 = follow-up call
			$new_id = Helper::QuickCreateNote($this->getState('new_note', null, 'string'), $type, $account, 'opportunities', $this->gscrm_opportunity_id );
			$this->setFieldValue('note', $new_id);
		}
		
		//set opportunity closed if selected won or lost in opportunity stage
		if($this->getState('stage', null, 'int') > 4 )
		{
			$this->setFieldValue('enabled', '0');
			
			//if won send a reminder to the user to create the order
			if($this->getState('stage', null, 'int') == 5 ) 
			{
				$build = $this->title.
						';'.$this->notes.
						';'.$this->currency.
						';'.$this->value.
						';'.$this->account.
						';'.$this->company.
						';'.$this->gscrm_opportunity_id;
				$opps_data = base64_encode($build);
				$lang = JFactory::getLanguage()->getTag();
				$ItemId_order = Helper::GetMenus($lang);
				$message = '<a class="" href="index.php?option=com_gscrm&view=Order&opps='.$opps_data.'&Itemid='.$ItemId_order['order'].' "
				 title="'.JText::_('COM_GSCRM_ORDERS_CREATE').' " >'.JText::_('GS_CREATE_ORDER').' <i class="glyphicon glyphicon-shopping-cart"></i></a>'; 
					
				\JFactory::getApplication()->enqueueMessage($message);
			}
		}
		//set opportunity open if selected in opportunity stage (changed stage to reopen)
		if($this->getState('stage', null, 'int') < 5 )
		{
			$this->setFieldValue('enabled', '1');
		}					
						
		parent::onBeforeCheck();
	}
	
	/**
	 * buildQuery function.
	 * 
	 * @access public
	 * @param bool $override (default: false)
	 * @return void
	 */
	public function buildQuery($override = false)
	{
        $db = $this->getDbo();
		$query = parent::buildQuery($override);
		
		//first get company code from user id to filter
		$code = Helper::GetCompanyCode()->gscrm_code_id;		
		if ($code == 0){			
			\JFactory::getApplication()->enqueueMessage(JText::_('GS_NO_CODE') );
			$query->where($db->qn('code').' = '.$db->q($code));
					
			} else { $query->where($db->qn('code').' = '.$db->q($code)); }

		//prepare array key-for-stages (related table will be done in future releases)
		$array_types = array(
				1=>JText::_('GS_SHOW_INTEREST'),	
				2=>JText::_('GS_QUALIFICATION'),
				3=>JText::_('GS_PROPOSAL'),
				4=>JText::_('GS_NEGOTIATION'),			
				5=>JText::_('GS_WON'),		
				6=>JText::_('GS_LOST') );
						
		//get fields for query
		
        if($field1 = $this->getState('title', null, 'string'))
        {
            $query->where($db->qn('title').' LIKE '.$db->q($db->escape('%'.$field1.'%')) );
        } 
        if($field2 = $this->getState('notes', null, 'string'))
        {
            $query->where($db->qn('notes').' LIKE '.$db->q($db->escape('%'.$field2.'%')) );
        }              
        
        //Stage by name or id:
        $field3 = $this->getState('stage', null, 'string');                          
		// if filter is not a number, find its id
        if ( !is_numeric($field3) && !empty($field3))
        {
	        $type_id = Helper::ArrayFind($field3, $array_types);
            $query->where($db->qn('stage').' = '.$db->q($type_id));
        }
        //if filter is a number, taken as id
        if ( is_numeric($field3) && !empty($field3) )
        {
           $type_id = (int)$field3;
           $query->where($db->qn('stage').' = '.$db->q($type_id));
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
        //if filter is a number, taken as id
        if ( is_numeric($field5) && !empty($field5) )
        {
           $account = (int)$field5;
           $query->where($db->qn('account').' = '.$db->q($account));
        } 

        if($field6 = $this->getState('contract', null, 'int'))
        {
            $query->where($db->qn('contract').' = '.$db->q($field6));
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
	        $keywork = Helper::YesOrNo($this->getState('enabled', null, 'string'));
	        
	        switch ($keywork)
	        {	        
		        case 'no' : $query->where($db->qn('enabled').' <> '.$db->q('1')); Break;
		        
		        case 'yes': $query->where($db->qn('enabled').' = '.$db->q('1')); Break;		        
		         
		        default: $query->where($db->qn('enabled').' = '.$db->q('2')); //this will force no result in any other case
	        }
	    }                     
        return $query;        
	}	
}