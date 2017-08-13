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

class Beads extends \FOF30\Model\DataModel
{
    protected function onBeforeLoadForm()
    {
	    //check if the user is logged before loading any form and raise error
		if (\JFactory::getUser()->guest) 
		{ 
			$this->container->platform->raiseError(403, \JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN')); 
		}
		
        parent::onBeforeLoadForm();
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
		           
        if($field3 = $this->getState('role', null, 'int'))
        {
            $query->where($db->qn('role').' = '.$db->q($field3));
        }
        //USER by name or id
        $fielduser = $this->getState('title', null, 'string');                          
       // user filter is not a number, find its id
        if ( !is_numeric($fielduser) && !empty($fielduser))
        {
	        $user_id = Helper::QueryLike('gscrm_bead_id', '#__gscrm_beads', 'user_name', $fielduser);
            $query->where($db->qn('gscrm_bead_id').' = '.$db->q($user_id));
        }
        //user filter is a number, taken as id
        if ( is_numeric($fielduser) && !empty($fielduser) )
        {
           $user_id = (int)$fielduser;
           $query->where($db->qn('gscrm_bead_id').' = '.$db->q($user_id));
        }                 
        return $query;

	}	
}