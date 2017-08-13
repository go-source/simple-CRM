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

class Notes extends \FOF30\Model\DataModel
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
		
	    //get parsed account id for new note
	    $jinput = \JFactory::getApplication()->input; 
		       
        //set account name for new contact/note on task=add
        if ($jinput->get('account', null, 'int'))
        {   
	        $this->setFieldValue('account', $jinput->get('account', null, 'int')); 
        } 
        
        //set flag to hide/show closed items
        if ($jinput->get('enabled', null, 'int'))
        {   
	        $this->setFieldValue('enabled', $jinput->get('enabled', null, 'int'));
        } 

        //change closed/open on click
        if ($jinput->get('flagchg', null, 'int'))
        { 
			//if flagchg 2 -> change from 1 to zero
	        if($jinput->get('flagchg', null, 'int') == 2) { Helper::UpdateField('notes', $jinput->get('Chgnum', null, 'int'), 'enabled', 0); }
	        
			//if flagchg 1 -> change from zero to 1
	        if($jinput->get('flagchg', null, 'int') == 1) { Helper::UpdateField('notes', $jinput->get('Chgnum', null, 'int'), 'enabled', 1); }	        
        }        
                       		
        parent::onBeforeLoadForm();
    }
    
    protected function onBeforeCheck()
    {
		//set title 
		$title = Helper::CreateTitle('notes', $this->getState('content', null, 'string'), $this->getState('gscrm_note_id', null, 'int'));
			
		$this->setFieldValue('title', $title);		
		
		//set owner from user assignment on beads table. Owner is who will handle the contact and not who created the note
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
			JFactory::getApplication()->enqueueMessage(JText::_('COM_GSCRM_NOTE_ERR_ACCOUNT_EMPTY'), error);
			return false;
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
			
		//prepare array key-for-types (related table will be done in future releases)
		$array_types = array(
				1=>JText::_('GS_COLD_CALL'),	
				2=>JText::_('GS_FOLLOW_UP'),
				3=>JText::_('GS_INQUIRY_PRE_SALES'),
				4=>JText::_('GS_INQUIRY_SUPPORT'),			
				5=>JText::_('GS_INQUIRY_CONTRACT'),		
				6=>JText::_('GS_INQUIRY_ORDER'),				
				7=>JText::_('GS_INQUIRY_INVOICE'),	
				8=>JText::_('GS_INQUIRY_EVENTS'),		
				9=>JText::_('GS_RETURN_GOODS'),
				99=>JText::_('GS_LOG') );

		//get fields for query
		
        if($field1 = $this->getState('title', null, 'string'))
        {
            $query->where($db->qn('title').' LIKE '.$db->q($db->escape('%'.$field1.'%')) );
        }
         
        if($field2 = $this->getState('content', null, 'string'))
        {
            $query->where($db->qn('content').' LIKE '.$db->q($db->escape('%'.$field2.'%')) );
        }              
        
        //Type by name or id:
        $field3 = $this->getState('type', null, 'string');                          
		// if filter is not a number, find its id
        if ( !is_numeric($field3) && !empty($field3))
        {
	        //$type_id = array_search($field3, $array_types);
	        $type_id = Helper::ArrayFind($field3, $array_types);
            $query->where($db->qn('type').' = '.$db->q($type_id));
        }
        //if filter is a number, taken as id
        if ( is_numeric($field3) && !empty($field3) )
        {
           $type_id = (int)$field3;
           $query->where($db->qn('type').' = '.$db->q($type_id));
        }
          
        //ACCOUNT by name or id
        $field4 = $this->getState('account', null, 'string');                          
       // user filter is not a number, find its id
        if ( !is_numeric($field4) && !empty($field4))
        {
	        $account = Helper::QueryLike('gscrm_account_id', '#__gscrm_accounts', 'title', $field4);
            $query->where($db->qn('account').' = '.$db->q($account));
        }          
        if ( is_numeric($field4) && !empty($field4) )
        {
           $account = (int)$field4;
           $query->where($db->qn('account').' = '.$db->q($account));
        } 
                                  
        if($field5 = $this->getState('opportunity', null, 'int'))
        {
            $query->where($db->qn('opportunity').' = '.$db->q($field5));
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