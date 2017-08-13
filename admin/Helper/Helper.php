<?php
/*
 * @package com_gscrm
 * @copyright (c)2017 Pedro Bicudo / bicudomaschio@gmail.com
 * @license GNU General Public License version 2 or later
 *
 * This component uses F0F30  
*/

namespace Gs\Gscrm\Admin\Helper;

// Protect from unauthorized access
defined('_JEXEC') or die();

// Include necessary Joomla core
use JFactory;
use JDatabase;
use JHtml;
use JText;
use JDate;
use \FOF30\Inflector\Inflector;

class Helper
{

/**
* GetCompanyCode function.
* Sets company code for all filters 
* Group user named as company codes so many groups can use the same CRM instance with group privacy
* 
* @access public
* @static
* @return void
*/
		public static function GetCompanyCode()
		{		
			$current_user = JFactory::getUser()->id;
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select( array('a.*','b.*') );
				$query->from($db->quoteName('#__gscrm_beads', 'a'));
				$query->join('INNER', $db->quoteName('#__gscrm_codes', 'b') . ' ON (' . $db->quoteName('a.code') . ' = ' . $db->quoteName('b.gscrm_code_id') . ')');
				$query->where ($db->quoteName('a.user_id')." = ".$db->quote($current_user)
					   .'AND'. $db->quoteName('a.enabled')." = ".$db->quote('1')
				       .'AND'. $db->quoteName('b.enabled')." = ".$db->quote('1') );
				$db->setQuery($query);
				$code = $db->loadObject();
				return $code;
		}

//TODO create new docblocks to the entire class
/* 
* Bead is the same as user
* this method returns the crm user id based on the current Joomla user
*/				
		public static function GetBead()
		{
			$current_user = JFactory::getUser()->id;
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);			
			$query->select($db->qn('gscrm_bead_id'));
			$query->from($db->qn('#__gscrm_beads'));
			$query->where($db->qn('user_id') . ' = ' . $db->q($current_user));
			$db->setQuery($query);
			$bead = $db->loadResult();
			return $bead;			
		}
/* 
* We need to parse menu item id to URL when site is not using SEF
* If using SEF, FOF3 constructs the URL with menu item
*/				
		public static function GetMenus($language)
		{
			$comp = 'com_gscrm';
			//query component menus
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);			
			$query->select(array('link','id'));
			$query->from($db->qn('#__menu'));
			$query->where($db->qn('link').' LIKE '.$db->q($db->escape('%'.$comp.'%')).' AND '.$db->qn('language').' = '.$db->quote($language)
					.' || '.$db->qn('link').' LIKE '.$db->q($db->escape('%'.$comp.'%')).' AND '.$db->qn('language').' = '.$db->quote('*'));
			$db->setQuery($query);
			$items = $db->loadAssocList();
			
			//Build array menu_name => menu_item_id
			$menu_item = array();
			foreach ($items as $item)
			{
				//find menu option from link, just subtract "index.php?option=com_gscrm&view=" 32 caracters
				$view = substr($item['link'], 32);
				$itemid = $item['id'];
				$menu_item [(string)$view] = $itemid;
			}
			return $menu_item;			
		}		
/* 
* Check if account exists in the database
* this method returns the id of the account that has the provided UID
*/				
		public static function GetUID($uid, $acc_id)
		{
			$code = self::GetCompanyCode()->gscrm_code_id;			
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);			
			$query->select($db->qn('gscrm_account_id'));
			$query->from($db->qn('#__gscrm_accounts'));
			$query->where($db->qn('unique_id') . ' = ' . $db->q($uid) 
						.' AND '.$db->qn('code') . ' = ' . $db->q($code)
						.' AND '.$db->qn('gscrm_account_id') . ' <> ' . $db->q($acc_id) );
			$db->setQuery($query);
			$unique = (int)$db->loadResult();
			return $unique;			
		}
/* 
* Check if account exists in the database
* this method returns the id of the account that has the provided UID
*/				
		public static function TitleExist($title)
		{
			$code = self::GetCompanyCode()->gscrm_code_id;			
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);			
			$query->select($db->qn('gscrm_account_id'));
			$query->from($db->qn('#__gscrm_accounts'));
			$query->where($db->qn('title') . ' = ' . $db->q($title) );
			$db->setQuery($query);
			$exist_id = (int)$db->loadResult();
			return $exist_id;			
		}				
/* 
* Sets the next item number (id) based on filter and company code id 
* Prevents user of navigating into items that belongs to another company code
*/		
		public static function NextItem($tablename, $idname, $item_id, $type)
		{
			if(empty($type)){$type = 0;}
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);			
			$query->select($db->qn($idname));
			$query->from($db->qn($tablename));
			
			if($type == 0){ $query->where($db->qn($idname) . ' > ' . $db->q($item_id)
				.' AND '.$db->qn('code') . ' = ' . $db->q( self::GetCompanyCode()->gscrm_code_id ) ) ;}					
			
			if($type == 1){ $query->where($db->qn($idname) . ' > ' . $db->q($item_id)  //type 1 filters only business accounts
				.' AND '.$db->qn('code') . ' = ' . $db->q( self::GetCompanyCode()->gscrm_code_id )
				.' AND '.$db->qn('type') . ' = ' . $db->q('1') );}			
			
			if($type == 2){	$query->where($db->qn($idname) . ' > ' . $db->q($item_id)  //type 2 filters only person accounts
				.' AND '.$db->qn('code') . ' = ' . $db->q( self::GetCompanyCode()->gscrm_code_id )
				.' AND '.$db->qn('type') . ' = ' . $db->q('2') );}
				
			$query->order('ordering ASC');
			$db->setQuery($query);
			$item = $db->loadResult();
			return $item;			
		}
/* 
* Sets the next item number (id) based on filter and owner id 
* Prevents user of navigating into items that he/she does not own
*/			
		public static function PrevItem($tablename, $idname, $item_id, $type)
		{
			if(empty($type)){$type = 0;}
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);			
			$query->select('max('.$idname.')');
			$query->from($db->qn($tablename));
			
			if($type == 0){ $query->where($db->qn($idname) . ' < ' . $db->q($item_id)
				.' AND '.$db->qn('owner') . ' = ' . $db->q(Helper::GetBead()) );}					
			
			if($type == 1){ $query->where($db->qn($idname) . ' < ' . $db->q($item_id)	//type 1 filters only business accounts
				.' AND '.$db->qn('owner') . ' = ' . $db->q(Helper::GetBead())
				.' AND '.$db->qn('type') . ' = ' . $db->q('1') );}			
			
			if($type == 2){	$query->where($db->qn($idname) . ' < ' . $db->q($item_id)	//type 2 filters only person accounts
				.' AND '.$db->qn('owner') . ' = ' . $db->q(Helper::GetBead())
				.' AND '.$db->qn('type') . ' = ' . $db->q('2') );}
							
			$db->setQuery($query);
			$item = $db->loadResult();
			return $item;			
		}
/* 
* save emails
* this method checks if email exists before saving a new one to avoid duplicates
* changes main email flag when necessary; last typed email is always the main email
*/
		public static function SaveEmail($email, $acc_id)
		{
			//Get previous emails for this account
			$items = self::AccountEmails($acc_id);
			
			$i=0; $ii=0;
			//if empty we can save because it is the first one
			if (empty($items)) 
			{
						self::SaveNewEmail($email, $acc_id);
						return true;		
			}		
			foreach ($items as $item)
			{
				$i=$i+1; //count number of items
				
				//update main (flag) if email exist
				if( $item['title'] == $email ) 
				{ 
					self::UpdateField('emails', $item['gscrm_email_id'], 'main', '1'); 
				}
				//deselect main (flag) if another email was set as main
				if( $item['main'] == '1' && $item['title'] !== $email)
				{
					self::UpdateField('emails', $item['gscrm_email_id'], 'main', '0');							
				}
				//count if email does not exist
				if ($item['title'] !== $email) {$ii=$ii+1;}
			}
			//if email does not exist, save it
			if($i == $ii) { self::SaveNewEmail($email, $acc_id); }
			
			return true;			
		}
		protected function SaveNewEmail($email, $acc_id)
		{
			$control = self::GetCompanyCode();
			$current_user = $control->user_id;
			$code = $control->gscrm_code_id;
			$today = new JDate();						
						$db = JFactory::getDbo();
						$query = $db->getQuery(true);					
						$columns = array('gscrm_email_id', 'title', 'main', 'account', 'code', 'created_on', 'created_by');
						$values = array($db->q('null'), $db->q($email),$db->q('1'), $db->q($acc_id), $db->q($code), $db->q($today), $db->q($current_user));
						$query
							    ->insert($db->qn('#__gscrm_emails'))
							    ->columns($db->qn($columns))
							    ->values(implode(',', $values));
						$db->setQuery($query);
						$db->execute();	
						return true; 				
		}
//quick save new account and return new id		
		public static function QuickCreateAccount($name, $type)
		{
			//avoid duplicates
			if(self::TitleExist($name) > 0)
			{
				JFactory::getApplication()->enqueueMessage(JText::_('COM_GSCRM_NOTE_ERR_ACCOUNT_EXIST').$name, error);
				return false;
			}
			$control = self::GetCompanyCode();
			$current_user = $control->user_id;
			$owner = $control->gscrm_bead_id;
			$code = $control->gscrm_code_id;
			$today = new JDate();						
						$db = JFactory::getDbo();
						$query = $db->getQuery(true);					
						$columns = array(
										  'gscrm_account_id',
										  'title',
										  'notes',
										  'type', 
										  'code',  
										  'owner',
										  'created_on',
										  'created_by');
						$values = array(
										$db->q('null'), 
										$db->q($name),
										$db->q(JText::_('GS_NEW_NAME_ACCOUNT_NOTE')),
										$db->q($type),										 
										$db->q($code), 
										$db->q($owner), 
										$db->q($today), 
										$db->q($current_user));
						$query
							    ->insert($db->qn('#__gscrm_accounts'))
							    ->columns($db->qn($columns))
							    ->values(implode(',', $values));
						$db->setQuery($query);
						$db->execute();
						$new_id = $db->insertid();
						
						return $new_id; 								
		}
		
		/**
		 * CreateNote function.
		 * 
		 * @access public
		 * @static
		 * @param mixed $title
		 * @param mixed $note
		 * @param mixed $type
		 * @param mixed $account
		 * @param mixed $source
		 * @param mixed $source_id
		 * @return void
		 *
		 * quick save new account and return new id. Available option for $type:
				<option value="1">gs_COLD_CALL</option>		
				<option value="2">gs_FOLLOW_UP</option>
				<option value="3">gs_INQUIRY_PRE_SALES</option>
				<option value="4">gs_INQUIRY_SUPPORT</option>			
				<option value="5">gs_INQUIRY_CONTRACT</option>		
				<option value="6">gs_INQUIRY_ORDER</option>				
				<option value="7">gs_INQUIRY_INVOICE</option>	
				<option value="8">gs_INQUIRY_EVENTS</option>		
				<option value="9">gs_RETURN_GOODS</option>
				internal system use 99 = system created log note
		*/		 
		public static function CreateNote($title, $note, $type, $account, $source, $source_id)
		{			
			//set variables default value. Source should be the table name or the id of the inquiry/call
			$opportunity=0; $contract=0; $order=0; $invoice=0;
			
			//get other data before save
			$control = self::GetCompanyCode();
			$current_user = $control->user_id;
			$code = $control->gscrm_code_id;
			if ($type == 99) { $enabled=0 ;} else { $enabled=1; }
			
			$today = new JDate();	
			//choose where to save $source_id
			switch ($source)
				{
					case 'contracts':
					$contract = $source_id;
					break;
					
					case 'orders':
					$order = $source_id;
					break;
					
					case 'invoices':
					$invoice = $source_id;
					break;										

					case 'opportunities':
					$opportunity = $source_id;
					break;	
				}					
						$db = JFactory::getDbo();
						$query = $db->getQuery(true);					
						$columns = array(
										  'gscrm_note_id',
										  'title',
										  'content',
										  'type', 
										  'account',
										  'opportunity',
										  'contract',
										  'order_id',
										  'invoice',
										  'owner',
										  'code', 
										  'created_on',
										  'created_by',
										  'enabled');		  
						$values = array(
										$db->q('null'), 
										$db->q($title),
										$db->q($note),
										$db->q($type),
										$db->q($account),
										$db->q($opportunity),
										$db->q($contract),
										$db->q($order),
										$db->q($invoice),										  
										$db->q($current_user),
										$db->q($code), 
										$db->q($today), 
										$db->q($current_user),
										$db->q($enabled)
										);
						$query
							    ->insert($db->qn('#__gscrm_notes'))
							    ->columns($db->qn($columns))
							    ->values(implode(',', $values));
						$db->setQuery($query);
						$db->execute();
						//get last inserted id
						$new_id = $db->insertid();
						
						return $new_id; 								
		}
	
		/**
		 * QuickCreateNote function.
		 * 
		 * Log note when creating contracts, orders and invoices for auditing purposes, if cliend accidentally changes data
		 *
		 * @access public
		 * @static
		 * @param mixed $note
		 * @param mixed $type
		 * @param mixed $account
		 * @param mixed $source
		 * @param mixed $source_id
		 * @return void
		 */
		public static function QuickCreateNote($note, $type, $account, $source, $source_id)
		{
			//adds title before creating note
			$title = self::CreateTitle($source, $note, $source_id);
			$new_id = self::CreateNote($title, $note, $type, $account, $source, $source_id);
			return $new_id;
		}				
//get the list of emails for an account		
		public static function AccountEmails($acc_id)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);			
			$query->select(array('gscrm_email_id','title','main'));
			$query->from($db->qn('#__gscrm_emails'));
			$query->where($db->qn('account') . ' = ' . $db->q($acc_id));
			$db->setQuery($query);
			$items = $db->loadAssocList();
			return $items;
		}	
/* 
* Save new address
* Method does not check if address exists, allows duplicates
*/				
		public static function SaveAddress($acc_id, $street, $number, $additional, $city, $state, $country, $zip, $main_addr, $owner )
		{
			//clear main address flag if it exists
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);			
			$query->select($db->qn('gscrm_address_id'));
			$query->from($db->qn('#__gscrm_addresses'));
			$query->where($db->qn('account') . ' = ' . $db->q($acc_id).' AND '.$db->qn('main') . ' = ' . $db->q('1'));
			$db->setQuery($query);
			$marked = $db->loadResult();

			if($marked)
			{
				self::UpdateField('addresses', $marked, 'main', '0');			
			}
			//get additional data and save address. Duplicated address may happen, it is due to user to fix it
			if(empty($street) || empty($city))
			{
				JFactory::getApplication()->enqueueMessage(JText::_('GS_ERROR_ADDRESS_EMPTY'), error);
				
				return false;
			}
			else {
				
			$control = self::GetCompanyCode();
			$current_user = $control->user_id;
			$code = $control->gscrm_code_id;
			$today = new JDate();						
						$query = $db->getQuery(true);					
						$columns = array('gscrm_address_id', 'main', 'street', 'number', 'additional', 
										'city', 'state', 'country', 'zip', 'account', 'code','created_on', 'created_by');
						$values = array($db->q('null'), $db->q($main_addr), $db->q($street), $db->q($number), $db->q($additional), 
										$db->q($city), $db->q($state), $db->q($country), $db->q($zip), $db->q($acc_id), $db->q($code),
										$db->q($today), $db->q($current_user));
						$query
							    ->insert($db->qn('#__gscrm_addresses'))
							    ->columns($db->qn($columns))
							    ->values(implode(',', $values));
						$db->setQuery($query);
						$db->execute();
						//get last saved id
						$new_id = $db->insertid();
						return $new_id;
			}														
		}
//Update a single address
		Public static function UpdateAddress($item_id, $street, $number, $additional, $city, $state, $country, $zip, $main)
		{
			$current_user = JFactory::getUser()->id;
			$today = new JDate();
						
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);				
				$query
						->update($db->qn('#__gscrm_addresses'))
						->set(array(
									$db->qn('main').' = '.$db->q($main),
									$db->qn('street').' = '.$db->q($street), 
									$db->qn('number').' = '.$db->q($number),
									$db->qn('additional').' = '.$db->q($additional),
									$db->qn('city').' = '.$db->q($city),
									$db->qn('state').' = '.$db->q($state),
									$db->qn('country').' = '.$db->q($country),
									$db->qn('zip').' = '.$db->q($zip),
									$db->qn('modified_on').' = '.$db->q($today),
									$db->qn('modified_by').' = '.$db->q($current_user) 
									))
						->where($db->qn('gscrm_address_id').' = '.$db->q($item_id));						
				$db->setQuery($query);
				$rx = $db->execute();				
				return true;													
		}	
/* 
* Delete a single item from table according to filter
* We do not use soft-delete to prevent from increasing the table size without need
* Enable field is reserved to show/not-show item in certain circustances
*/
		Public static function DeleteById($tablename, $filtercolumn, $filtervalue)	
		{	
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$conditions = array(
								$db->quoteName($filtercolumn) . ' = ' . $db->q($filtervalue) 
								);
			$query->delete($db->quoteName($tablename));
			$query->where($conditions);
			$db->setQuery($query);
			$rx = $db->execute();
			return true;
		}		

/* 
* Delete the related information before deleting an account
* We do not use soft-delete to prevent from increasing the table size without need
* Enable field is reserved to show/not-show item in certain circustances
*/
		Public static function DeleteAccountsRelationships($acc_id)
		{
			//get all emails and delete
			//$emails = self::AccountEmails($acc_id);
			$emails = self::GetIdArrayFilter('#__gscrm_emails', 'gscrm_email_id', 'account = '.$acc_id);
			foreach( $emails as $email)
			{
				self::DeleteById('#__gscrm_emails', 'gscrm_email_id', $email);
			}
			//get all addresses and delete
			$addresses = self::GetIdArrayFilter('#__gscrm_addresses', 'gscrm_address_id', 'account = '.$acc_id);
			foreach( $addresses as $address)
			{
				self::DeleteById('#__gscrm_addresses', 'gscrm_address_id', $address);
			}			
			//get all relationships and delete
			$relationships = self::GetIdArrayFilter('#__gscrm_relations', 'gscrm_relation_id', 'parent = '.$acc_id.' OR child = '.$acc_id);
			foreach( $relationships as $relationship)
			{
				self::DeleteById('#__gscrm_relations', 'gscrm_relation_id', $relationship);
			}						
			return true;
		}		
/* 
* Returns a list of addresses for a given account
* Method does not check if address exists and returns duplicates
*/		
		public static function ShowAddresses($account_id)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);			
			$query->select(array('gscrm_address_id', 'main', 'street', 'number', 'additional','city', 'state', 'country', 'zip'));
			$query->from($db->qn('#__gscrm_addresses'));
			$query->where($db->qn('account') . ' = ' . $db->q($account_id));
			$query->order($db->qn('main') . ' DESC');
			$db->setQuery($query);
			$addresses = $db->loadAssocList();	
			return $addresses;			
		}
/* 
* Returns a list of addresses for a given account
* Method does not check if address exists and returns duplicates
*/		
		public static function ShowAddressesFilter($select, $filter)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);			
			$query->select($select);
			$query->from($db->qn('#__gscrm_addresses'));
			$query->where($filter);
			$query->order($db->qn('main') . ' DESC');
			$db->setQuery($query);
			$addresses = $db->loadAssocList();	
			return $addresses;			
		}
/* 
* Returns the related information for contracts, orders and invoices
* Method filter and join to reduce number of queries
* Type = 1 means account is person and has no business
*/		
		public static function DocData($account_id, $type = null)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			
		if($type == 1) {
			$query	
				->select(array('a.title as acc_ttl', 'a.unique_id as acc_uid', 'c.number as acc_num', 'c.street as acc_str','c.additional as acc_adtl', 
								'c.city as acc_city', 'c.state as acc_st', 'c.zip as acc_zip', 'c.country as acc_ctry', 
								'b.title as bz_ttl', 'b.unique_id as bz_uid', 'd.number as bz_num', 'd.street as bz_str','d.additional as bz_adtl', 
								'd.city as bz_city', 'd.state as bz_st', 'd.zip as bz_zip', 'd.country as bz_ctry' ))
								
				->from($db->qn('#__gscrm_accounts', 'a'))
				->join('RIGHT', $db->qn('#__gscrm_accounts', 'b') . ' ON (' . $db->qn('a.company') . ' = ' . $db->qn('b.gscrm_account_id') . ')')
				->join('LEFT', $db->qn('#__gscrm_addresses', 'c') . ' ON (' . $db->qn('a.address') . ' = ' . $db->qn('c.gscrm_address_id') . ')')
				->join('RIGHT', $db->qn('#__gscrm_addresses', 'd') . ' ON (' . $db->qn('b.address') . ' = ' . $db->qn('d.gscrm_address_id') . ')')
				->where($db->qn('a.gscrm_account_id') . ' = ' . $db->q($account_id));
				
			}
		else {
			$query	
				->select(array('a.title as acc_ttl', 'a.unique_id as acc_uid', 'c.number as acc_num', 'c.street as acc_str','c.additional as acc_adtl', 
								'c.city as acc_city', 'c.state as acc_st', 'c.zip as acc_zip', 'c.country as acc_ctry' ))
								
				->from($db->qn('#__gscrm_accounts', 'a'))
				->join('RIGHT', $db->qn('#__gscrm_addresses', 'c') . ' ON (' . $db->qn('a.address') . ' = ' . $db->qn('c.gscrm_address_id') . ')')
				->where($db->qn('a.gscrm_account_id') . ' = ' . $db->q($account_id));
			}
			$db->setQuery($query);
			$data = $db->loadAssoc();	

			return $data;	
			/* retrieval array names for easier cut-and-paste:
			* account person:	'acc_ttl' 'acc_uid' 'acc_num' 'acc_str' 'acc_adtl' 'acc_city' 'acc_st' 'acc_zip' 'acc_ctry']
			* account business:	 'bz_ttl' 'bz_uid' 'bz_num' 'bz_str' 'bz_adtl' 'bz_city' 'bz_st' 'bz_zip' 'bz_ctry'			
			*/				
		}
//Returns the relations
		public static function GetRelations($parent)
		{
			$code = self::GetCompanyCode()->gscrm_code_id;
				
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select(array('gscrm_relation_id', 'title', 'code', 'parent', 'child'));
						$query->from($db->qn('#__gscrm_relations'));
						$query->where($db->qn('code')." = ".$db->q($code)
								." AND ". $db->qn('parent')." = ".$db->q($parent)
								);
						$db->setQuery($query);		
						$relations = $db->loadAssocList();	
			return $relations;
		}
		
		
//save n:n relationsihips
		public static function SaveRelation($title, $parent, $child)
		{
			if(self::CheckRelationExist($title, $parent, $child ) == 0)
			{
			$code = self::GetCompanyCode()->gscrm_code_id;
						$db = JFactory::getDbo();
						$query = $db->getQuery(true);					
						$columns = array('gscrm_relation_id', 'title', 'code', 'parent', 'child');
						$values = array($db->q('null'), $db->q($title), $db->q($code), $db->q($parent), $db->q($child));
						$query
							    ->insert($db->qn('#__gscrm_relations'))
							    ->columns($db->qn($columns))
							    ->values(implode(',', $values));
						$db->setQuery($query);
						$db->execute();	
						return true;
			}
			//JFactory::getApplication()->enqueueMessage('exists');
			return false;																	
		}
//Update a single relationship
		Public static function EditRelation($item_id, $title, $parent, $child)
		{	
			if(self::CheckRelationExist($title, $parent, $child ) == 0)
			{
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);				
				$query
						->update($db->qn('#__gscrm_relations'))
						->set(array(
									$db->qn('title').' = '.$db->q($title),
									$db->qn('parent').' = '.$db->q($parent), 
									$db->qn('child').' = '.$db->q($child)
									))
						->where($db->qn('gscrm_relation_id').' = '.$db->q($item_id));						
				$db->setQuery($query);
				$rx = $db->execute();		
				return true;	
			}
			return false;													
		}			
// check if a relation exists to avoid duplicates - returns 1 if exists, 0 if not
		public static function CheckRelationExist($querytitle, $parent, $child )
		{
			$code = self::GetCompanyCode()->gscrm_code_id;
			
						if (!empty($parent) AND !empty($child))
						{				
				        $db = JFactory::getDbo();
				        $query = $db->getQuery(true);
						$query->select('COUNT(*)');
						$query->from($db->qn('#__gscrm_relations'));
						$query->where($db->qn('title')." = ".$db->q($querytitle)." AND "
								. $db->qn('code')." = ".$db->q($code)." AND "						
								. $db->qn('parent')." = ".$db->q($parent)." AND "
								. $db->qn('child')." = ".$db->q($child) );
						$db->setQuery($query);		
						$count1 = $db->loadResult();	
						if ($count1 > 0) { return 1; } else { return 0 ;}
						} 
						// if parent or child is empty, fake answer yes: exist
						return 1;
		}
//get all notes from account
		public static function Notes($account, $nt_show)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);			
			$query->select(array('*'));
			$query->from($db->qn('#__gscrm_notes'));
			if($nt_show > 0) 
			{ 
				$query->where($db->qn('account') . ' = ' . $db->q($account).' AND '.$db->qn('enabled') . ' = ' . $db->q('1'));
				}else{
					$query->where($db->qn('account') . ' = ' . $db->q($account));
				} 
			$query->order ($db->qn('created_on').' DESC');
			$db->setQuery($query);
			$items = $db->loadObjectList();
			return $items;			
		}

//get the list of emails for an account		 
		public static function Territories()
		{
			$code = self::GetCompanyCode()->gscrm_code_id;
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);			
			$query->select(array('gscrm_territory_id','title'));
			$query->from($db->qn('#__gscrm_territories'));
			$query->where($db->qn('code') . ' = ' . $db->q($code).' AND '.$db->qn('enabled') . ' = ' . $db->q('1'));
			$db->setQuery($query);
			$items = $db->loadObjectList();
			return $items;
		}		
		public static function Campaigns()
		{
			$code = self::GetCompanyCode()->gscrm_code_id;
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);			
			$query->select(array('gscrm_campaign_id','title'));
			$query->from($db->qn('#__gscrm_campaigns'));
			$query->where($db->qn('code') . ' = ' . $db->q($code).' AND '.$db->qn('enabled') . ' = ' . $db->q('1'));
			$db->setQuery($query);
			$items = $db->loadObjectList();
			return $items;
		}	

//update any item, one field. Also used to change enabled/disabled for any item
		public static function UpdateField($tablename, $item_id, $update_field, $update_value)
		{
			$table = '#__gscrm_'.$tablename;
			$idname = 'gscrm_'.(new Inflector)->Singularize($tablename).'_id';		
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);				
			$query
				->update($db->qn($table))
						->set(array( $db->qn($update_field).' = '.$db->q($update_value) ))
						->where($db->qn($idname).' = '.$db->q($item_id));						
				$db->setQuery($query);
				$rx = $db->execute();				
				return true;			
		}		
						
//query like
		public static function QueryLike($select, $tablename, $querywhere, $querylike)
		{
			$code = self::GetCompanyCode()->gscrm_code_id;
			
						$db = JFactory::getDbo();
						$query = $db->getQuery(true);
						$query->select($db->qn($select));
						$query->from($db->qn($tablename));
						$query->where($db->qn($querywhere).' LIKE '.$db->q($db->escape('%'.$querylike.'%')).
								' AND '. $db->qn('code').' = '.$db->q($code) );
						$db->setQuery($query);
						$returndata = $db->loadResult();
						return $returndata;
		}
		
//Query a table to get a value; i.e.: convert name to number or ID - select,table,where,equal
		public static function QueryData($select, $tablename, $querywhere, $queryequal)
		{
						$db = JFactory::getDbo();
						$query = $db->getQuery(true);
						$query->select($db->qn($select));
						$query->from($db->qn($tablename));
						$query->where($db->qn($querywhere).' = '.$db->q($queryequal));
						$db->setQuery($query);
						$returndata = $db->loadResult();
						return $returndata;
		}				
		
//COUNT Get table size or number of rows
		public static function CountTableRows($tablename, $where)
		{
					$db = JFactory::getDbo();
					$query = $db->getQuery(true);
					$query->select('COUNT(*)');
					$query->from($db->qn($tablename));
					if(!empty($where)) {$query->where($where); }
					$db->setQuery($query);
					$count_rows = $db->loadResult();
			if ($count_rows !== null)	{ return $count_rows; } else { return 0; }	
		}
		
// Get IDs array from table
		public static function GetIdArray($tablename, $idname)
					{
					$db = JFactory::getDbo();
						$query = $db->getQuery(true);
					    $query->select($db->qn($idname));
						$query->from ($db->qn($tablename));
					$db->setQuery($query);
					$array_ids = (array)($db->loadColumn());
					return $array_ids;
					}
						
// Get IDs array from table with filter
		public static function GetIdArrayFilter($tablename, $idname, $filter)
					{
					$db = JFactory::getDbo();
						$query = $db->getQuery(true);
					    $query->select($db->qn($idname));
						$query->from ($db->qn($tablename));
						$query->where ($filter);
					$db->setQuery($query);
					$array_ids = (array)($db->loadColumn());
					return $array_ids;
					}
										
// Get one ROW array by id					
		public static function GetRowArray($tablename, $columnname, $itemid)
					{
					$db = JFactory::getDbo();
						$query = $db->getQuery(true);
					    $query->select('*');
						$query->from ($db->qn($tablename));
						$query->where ($db->qn($columnname)." = ".$db->q($itemid));
					$db->setQuery($query);
					$array_row = (array)($db->loadAssoc());
					return $array_row;
					}
// Get many rows where equal						
		public static function RowsloadObjectList($tablename, $querywhere, $queryequal, $order, $direction)
					{
					if($direction > 0) { $dir = ' ASC'; } else { $dir = ' DESC'; }
					
					$db = JFactory::getDbo();
						$query = $db->getQuery(true);
					    $query->select('*');
						$query->from ($db->qn($tablename));
						$query->where ($db->qn($querywhere)." = ".$db->q($queryequal));
						$query->order ($db->qn($order).$dir);   //($db->quoteName('column') . ' DESC');
					$db->setQuery($query);
					$array = $db->loadObjectList();
					return $array;
					}					
						
		/**
		 * SelectArray function.
		 * 
		 * @access public
		 * @static
		 * @param mixed $tablename
		 * @param mixed $querywhere
		 * @param mixed $queryequal
		 * @param mixed $order
		 * @param mixed $direction
		 * @return void
		 */
		public static function SelectArray($select_array, $tablename, $querywhere, $queryequal, $order, $direction)
					{
					if($direction > 0) { $dir = ' ASC'; } else { $dir = ' DESC'; }

					$db = JFactory::getDbo();
						$query = $db->getQuery(true);
					    $query->select($select_array);
						$query->from ($db->qn($tablename));
						$query->where ($db->qn($querywhere)." = ".$db->q($queryequal));
						$query->order ($db->qn($order).$dir);   //($db->quoteName('column') . ' DESC');
					$db->setQuery($query);
					$array = $db->loadObjectList();
					return $array;
					}
		
		/**
		 * loadObjectListKey function.
		 * 
		 * @access public
		 * @static
		 * @param mixed $select_array
		 * @param mixed $short_tablename
		 * @param mixed $where
		 * @param mixed $order
		 * @param mixed $direction
		 * @return void
		 */
		public static function loadObjectListKey($select_array, $short_tablename, $where, $order, $direction)
					{
					if($direction > 0) { $dir = ' ASC'; } else { $dir = ' DESC'; }
					$tablename = '#__gscrm_'.$short_tablename;
					$index = 'gscrm_'.(new Inflector)->Singularize($short_tablename).'_id';
					
					$db = JFactory::getDbo();
						$query = $db->getQuery(true);
					    $query->select($select_array);
						$query->from ($db->qn($tablename));
						$query->where ($where);
						$query->order ($db->qn($order).$dir);   //($db->quoteName('column') . ' DESC');
					$db->setQuery($query);
					$object = $db->loadObjectList($index);
					return $object;
					}					
									
// Get COLUMNs array where condition					
		public static function GetColumnArray($select, $tablename, $columnname, $filter)
					{
					$db = JFactory::getDbo();
						$query = $db->getQuery(true);
					    $query->select($db->qn($select));
						$query->from ($db->qn($tablename));
						$query->where ($db->qn($columnname)." = ".$db->q($filter));
					$db->setQuery($query);
					$array_column = (array)($db->loadColumn());
					return $array_column;
					}
// Clears formating and code		
		public static function ClearText($text)
		{	
			$text = (string) preg_replace("'<script[^>]*>*?</script>'si", '', $text);
			$text = (string) preg_replace('/<a\s+.*?href="([^"]+)"[^>]*>([^<]+)<\/a>/is', '\2 (\1)', $text);
			$text = (string) preg_replace('/<!--.+?-->/', '', $text);
			$text = (string) preg_replace('/{.+?}/', '', $text);  					
			$text = (string) preg_replace('/&nbsp;/', ' ', $text);
			$text = (string) preg_replace('/&amp;/', ' ', $text);
			$text = (string) preg_replace('/&quot;/', ' ', $text);
			$text = (string) strip_tags($text);
			$text = (string) htmlspecialchars($text, ENT_COMPAT, 'UTF-8');
			$text = (string) preg_replace('/[^A-Za-z0-9\/+ =.,()$-ÀàÃãÁáÂâÉéÍíÓóÔôõÕÚúÇçüÚñ]/iu', '', $text);
			return $text;
		}
// Next id on save		
		public static function NextId($table_short_name_plural)
		{
			$idname = 'gscrm_'.(new Inflector)->Singularize($table_short_name_plural).'_id';
			$tablename = '#__gscrm_'.$table_short_name_plural;
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select('max('.$idname.')')
				->from ($db->qn($tablename));
			$db->setQuery($query);
			$last = (int)$db->loadResult();
			return $last+1;
		}	

//Create title from description		
		public static function CreateTitle($tablename, $content, $source_id)	
		{		
			//add code according to table name
			switch ($tablename)
				{
					case 'opportunities': $title_code = 'OP';
					break;
					
					case 'contracts': $title_code = 'CO';
					break;
					
					case 'orders': $title_code = 'OD';
					break;
					
					case 'invoices': $title_code = 'IN';
					break;										

					default: $title_code = 'NT';
				}
			//get description's first 49 digits
			$content_short = substr($content, 0, 49);
			if(strlen($content) > 49){ $desc_short = $content_short.'...';} else { $desc_short = $content_short; }
			
			//build title and set field value for save
			$title = $title_code.' '.$desc_short;	
			
			return $title;
		}
					
//get las comment or note
		public static function LastComment($source, $source_id)	
		{
				$code = self::GetCompanyCode()->gscrm_code_id;
				
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select($db->qn( 'title' ));
				$query->from($db->qn('#__gscrm_notes'));
				$query->where($db->qn($source).' = '.$db->q($source_id).'AND '. $db->qn('code')." = ".$db->q($code) );
				$query->order($db->qn('created_on') . ' DESC');
				$db->setQuery($query);
				$content = $db->loadResult();
		return $content;				
		}
// dates difference (days) 			
		public static function DiffDays($date2, $date1)
		{
			return round(($date2->toUnix() - $date1->toUnix())/86400);
		}

/*
* USER_TIME convertion. Provided datetime shall be in Joomla date format
* Remember that system created date/time is saved in UTC
* The time reference is set in php.ini but, as it is not a default configuration, it is possible that php.ini is not correct
* If php.ini timezone is not set, the root reference comes from the server clock and operating system setup
* if you are running on a cloud based server you cannot set the clock and UTC reference, but usually it is UTC
* if you get wrong date/time conversion, check your php.ini before changing this code
* php.ini hour can be retrived with date('h:i O') and timezone with date('h:i T')
*
* User provided dates are as typed or selected in forms so, we prefer not to change timezone when retrieving
*
* This method is intended to converte system created dates (like logs) to show to the user under company code prefered timezone
*/			
		public static function User_timezone($parsed_date, $parsed_timezone = null) 
		{			
			//get user preference
			$preferences = self::GetCompanyCode();
			//set parameters from preferences
			$dateformat = $preferences->date_format;
			$hourformat = $preferences->hour_format;
			$formatstring = $dateformat.' '.$hourformat;
			
			//select timezone
			switch ($parsed_timezone)
			{
				case '1': 
						$timezone = date_default_timezone_get();   //joomla server timezone
						Break;
				case '2':
						$timezone = $preferences->timezone;    //timezone select by client in company code preferences
						Break;
				default:
						$timezone = 'UTC';						
			}
			
			if(empty($timezone))( $timezone = 'UTC');
			
			$date_show = JHtml::date($parsed_date, $formatstring, $timezone);

			return $date_show;
		}		
		
//search array %LIKE and return array id
		public static function ArrayFind($needle, $haystack)
		{
			$clear_needle = self::ClearText($needle);
			
			$needle = strtolower($clear_needle);
			
			$haystack = array_map('strtolower', $haystack);
			
			foreach ($haystack as $ii => $item)
			{
		      if (strpos($item, $needle) !== FALSE)
		      {
		        return $ii;
		        break;
		      }
		   }
		return true;	
		}		
//Yes or No in any language
		public static function YesOrNo($word)
		{
			$find = strtolower($word);
		
			switch ($find)
			{
				case strtolower(JTEXT::_('GS_NO')): $ans = 'no'; Break;
				case strtolower(JTEXT::_('GS_CLOSED')): $ans = 'no'; Break;
				case strtolower(JTEXT::_('GS_UNPUBLISHED')): $ans = 'no'; Break;
				case 'zero': $ans = 'no'; Break;
				case 'null': $ans = 'no'; Break;
				case '0': $ans = 'no'; Break;
				case 'n': $ans = 'no'; Break;
				case '1': $ans = 'yes'; Break;
				case 'y': $ans = 'yes'; Break;
				case strtolower(JTEXT::_('GS_YES')): $ans = 'yes'; Break;
				case strtolower(JTEXT::_('GS_OPEN')): $ans = 'yes'; Break;
				case strtolower(JTEXT::_('GS_PUBLISHED')): $ans = 'yes'; Break;
				default: $ans = "error";
			}
		return $ans;
		}
		
// percent time between two dates		
		public static function PercentCalc($ref_day, $direction, $tablename, $idfieldname, $idnumber)	
		{
			//get sdate and edate from table - remember idfieldname is like gscrm_table_id
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select( array('sdate', 'edate') );
			$query->from ($db->qn($tablename));
			$query->where ($db->qn($idfieldname)." = ".$db->q($idnumber));
			$db->setQuery($query);
			$array_dates = (array)($db->loadColumn());
			
			$sdate = $array_dates[0];
			$edate = $array_dates[1];
		
			//sets $ref_day - remember the JDate is UTC !
			if ( $ref_day == 'today' || $ref_day == '1') {$ref_day = new JDate();}
		
			//calculate direction			
			if ($direction == 0) { $difference = self::DiffDays($ref_day, $sdate);}			
			if ($direction == 1) { $difference = self::DiffDays($edate, $ref_day);}
			
			//calculate percentages
			$range = self::DiffDays($edate, $sdate);
			
			$percentage = round(( $difference * 100 ) / $range);
						
			return $percentage;
		}	
// end of helper class			
}
		