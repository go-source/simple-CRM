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

class Accounts extends \FOF30\Model\DataModel
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
		//set owner id if empty or zero; get id from user assignment on beads table
		if( $this->getState('owner', null, 'int') < 1) 
		{ 
			$this->setFieldValue('owner', Helper::GetBead());
		}
				
		//check if unique identifier already exists in the database filtered by company code. If task=add account id is zero, no problem.
		$has_uid = Helper::GetUID($this->getState('unique_id', null, 'int'), $this->getState('gscrm_account_id', null, 'int'));

		if ($has_uid > 0)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('GS_UID_EXISTS').'
			<a href="index.php?option=com_gscrm&view=Account&id='.$has_uid.'"> ID:'.$has_uid, notice);
		}
		
		/*   Set address id  
			* if address is new we will set id after save
			* new_addr = button submit new address 1; we do not know the id after save of address
			* is_main_addr = checkbox 1			
			*
			* if address is update we set it in the model before check
			* up_addr = button submit update address and sends the address id
			* up_main = checkbox 1 if this is to update the main address
		*/
		if( $this->getState('up_addr', null, 'int') > 1 && $this->getState('up_main', null, 'int') == 1 )
		{
			$this->setFieldValue('address', $this->getState('up_addr', null, 'int') );
		}			
			
		parent::onBeforeCheck();
	}
	
    protected function onAfterSave()
    {		    
		//On task=edit the account_id comes from model getState
		$acc_id = $this->getState('gscrm_account_id', null, 'int');
		
		//On task=add the id has just been created, we need to get it from JFactory
		if($acc_id < 1)
		{
			$db = JFactory::getDbo();
			$acc_id = $db->insertid();			
		}
		
		//create new account from one-line account name - it is done from new relation form
			if( $this->getState('new_name_account', null, 'string'))
			{
				//This could also be done with F0F "Create new records"
				if($this->getState('type_new_name', null, 'int')) 
					{
						$type_new_relation = $this->getState('type_new_name', null, 'int');} 
					else {
						$type_new_relation = 2;
					}
				
				$new_name_account_id = Helper::QuickCreateAccount($this->getState('new_name_account', null, 'string'), $type_new_relation);
			
				//if new relation just created is a "belongs to a company", we neet to update the database field 'company'
				if( $this->getState('rel_title', null, 'int') == 1 )
				{
					Helper::UpdateField('accounts', $acc_id, 'company', $new_name_account_id);
				}
			}		
				
		//include new relationship
		if( $this->getState('new_relation', null, 'int') > 0 )
		{
			$name_relation = $this->getState('rel_title', null, 'int');
			if ($new_name_account_id > 0){ $name_child = $new_name_account_id; } else { $name_child = $this->getState('rel_account', null, 'int');}
			
			Helper::SaveRelation($name_relation, (int)$acc_id, $name_child);		 
		}

		//set relation for company; SaveRelation function will check if relation exists before saving
		if( $this->getState('company', null, 'int') > 0 )
		{
			Helper::SaveRelation('1', $acc_id, $this->getState('company', null, 'int'));
		}		
		
	    //update or delete relationship
	    $count_relations = $this->getState('count_relations', null, 'int');
	    for ($i = 0; $i <= $count_relations; $i++ )
		    {
			    $setrelupdate = 'relation'.$i;
			    $getrelupdate = $this->getState($setrelupdate, null, 'int');			
			//update relationship
			if($getrelupdate > 0) {
				$change_title = $this->getState('rel_title'.$i, null, 'int');
				$change_child = $this->getState('rel_account'.$i, null, 'int');
				
				Helper::EditRelation($getrelupdate, $change_title, $acc_id, $change_child);
				}
			    
			    $setreldelete = 'del_rel'.$i;
			    $getreldelete = $this->getState($setreldelete, null, 'int');			
			//delete relationship
			if($getreldelete > 0) {				
				Helper::DeleteById('#__gscrm_relations', 'gscrm_relation_id', $getreldelete);
				
			//NOTE: company field from account table will no be erased so, on next save the account-company relationship will be re-created if user does not change it
				}
		    }
		
		//create new email in related table	 	    		
	    if($email = $this->getState('email', null, 'string'))
		    {
		        //set field value in this model
		        $is_main = $this->getState('is_main', null, 'string');
		        Helper::SaveEmail($email, $acc_id);
		    }
	    //delete email
	    $count_email = $this->getState('count_emails', null, 'int');
	    for ($ie = 0; $ie <= $count_email; $ie++ )
	    	{
				$deletemailfield = 'del_email'.$ie;
			    $getdeletemail = $this->getState($deletemailfield, null, 'int');
				//delete email
			    if ($getdeletemail > 0)
			    { 
				    Helper::DeleteById('#__gscrm_emails', 'gscrm_email_id', $getdeletemail); 
				}		    
	    	}
	    
	    //update, del or create new address
	    $count_addresses = $this->getState('count_addresses', null, 'int');
	    for ($i = 0; $i <= $count_addresses; $i++ )
		    {
			    $setfieldupdate = 'up_addr'.$i;
			    $getfieldupdate = $this->getState($setfieldupdate, null, 'int');			
			//update address
			if($getfieldupdate > 0) {
				$change_street = $this->getState('up_street'.$i, null, 'string');
				$change_number = $this->getState('up_number'.$i, null, 'string');
				$change_additional = $this->getState('up_additional'.$i, null, 'string');
				$change_city = $this->getState('up_city'.$i, null, 'string');
				$change_state = $this->getState('up_state'.$i, null, 'string');
				$change_country = $this->getState('up_country'.$i, null, 'string');
				$change_zip = $this->getState('up_zip'.$i, null, 'string');				
				$change_main = $this->getState('up_main'.$i, null, 'int'); 
				Helper::UpdateAddress($getfieldupdate, $change_street, $change_number, $change_additional, $change_city, $change_state, $change_country, $change_zip, $change_main);
				}

			//delete address    
			    $setfielddelete = 'del_addr'.$i;
			    $getfielddelete = $this->getState($setfielddelete, null, 'int');			
			if($getfielddelete > 0) {				
				Helper::DeleteById('#__gscrm_addresses', 'gscrm_address_id', $getfielddelete);
				}
		    }
	    
	    //save address 
	    if($new_addr = $this->getState('new_addr', null, 'int'))
		    {  
		       $street = $this->getState('add_street', null, 'string');
		       $number = $this->getState('add_number', null, 'string');
		       $additional = $this->getState('add_additional', null, 'string');
		       $city = $this->getState('add_city', null, 'string');
		       $state = $this->getState('add_state', null, 'string');
		       $country = $this->getState('add_country', null, 'string');
		       $zip = $this->getState('add_zip', null, 'string');
			   $main_addr = $this->getState('is_main_addr', null, 'int');

		       $new_addr_id = Helper::SaveAddress($acc_id, $street, $number, $additional, $city, $state, $country, $zip, $main_addr, $this->getState('owner', null, 'int') );
		        
			/*   Set address id  
			* if address is new we set it here, after save
			* new_addr = button submit new address 1; we do not know the id after save of address
			* is_main_addr = checkbox 1			
			*
			* if addres is update we have set it in the model before check
			*/
			if( $this->getState('address', null, 'int') < 1 || $main_addr == 1 )
				{
					Helper::UpdateField('accounts', $acc_id, 'address', $new_addr_id);
				}		        
		    }		    		    
		    			
		parent::onAfterSave();   
	}

    protected function onBeforeDelete()
    {	
		//Get id before delete to delete all relations in other tables
		$acc_id = $this->getState('gscrm_account_id', null, 'int');
		
		Helper::DeleteAccountsRelationships($acc_id);
		
		parent::onBeforeDelete();
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
        if($field2 = $this->getState('company', null, 'string'))
        {
            $company_id = Helper::QueryLike('gscrm_account_id', '#__gscrm_accounts', 'title', $field2);
            $query->where($db->qn('company').' = '.$db->q($company_id));
        }              
        if($field3 = $this->getState('type', null, 'int'))
        {
            $query->where($db->qn('type').' = '.$db->q($field3));
        }
        if($field4 = $this->getState('gender', null, 'int'))
        {
            $query->where($db->qn('gender').' = '.$db->q($field4));
        }                    
        if($field5 = $this->getState('owner', null, 'int'))
        {
            $query->where($db->qn('owner').' = '.$db->q($field5));
        }     

        //Territory by name or id
        $field6 = $this->getState('territory', null, 'string');                          
       // if filter is not a number, find its id using QueryLike($select, $tablename, $querywhere, $querylike)
        if ( !is_numeric($field6) && !empty($field6))
        {
	        $territory_id = Helper::QueryLike('gscrm_territory_id', '#__gscrm_territories', 'title', $field6);
            $query->where($db->qn('territory').' = '.$db->q($territory_id));
        }
        //if filter is a number, taken as id
        if ( is_numeric($field6) && !empty($field6) )
        {
           $territory_id = (int)$field6;
           $query->where($db->qn('territory').' = '.$db->q($territory_id));
        } 
        //Campaign by name or id
        $field7 = $this->getState('campaign', null, 'string');                          
       // if filter is not a number, find its id
        if ( !is_numeric($field7) && !empty($field7))
        {
	        $campaign_id = Helper::QueryLike('gscrm_campaign_id', '#__gscrm_campaigns', 'title', $field7);
            $query->where($db->qn('campaign').' = '.$db->q($campaign_id));
        }
        //risk filter is a number, taken as id
        if ( is_numeric($field7) && !empty($field7) )
        {
           $campaign_id = (int)$field7;
           $query->where($db->qn('campaign').' = '.$db->q($campaign_id));
        } 
        
        //USER by name or id
        $fielduser = $this->getState('owner', null, 'string');                          
       // user filter is not a number, find its id
        if ( !is_numeric($fielduser) && !empty($fielduser))
        {
	        $user_id = Helper::QueryLike('gscrm_bead_id', '#__gscrm_beads', 'user_name', $fielduser);
            $query->where($db->qn('owner').' = '.$db->q($user_id));
        }
        //user filter is a number, taken as id
        if ( is_numeric($fielduser) && !empty($fielduser) )
        {
           $user_id = (int)$fielduser;
           $query->where($db->qn('owner').' = '.$db->q($user_id));
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