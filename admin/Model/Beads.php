<?php
/*
 * @package com_gscrm
 * @copyright (c)2017 Pedro Bicudo / bicudomaschio@gmail.com
 * @license GNU General Public License version 2 or later
 */

namespace Gs\Gscrm\Admin\Model;

defined('_JEXEC') or die();

// include helper file where all functions are included
use \Gs\Gscrm\Admin\Helper\Helper;

// Include necessary Joomla core
use JFactory;

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
    
    protected function onBeforeCheck()
    {
	    //copy data from Joomla if radio selected
	    $jinput = \JFactory::getApplication()->input; 

        if ($jinput->get('copy_data', null, 'int') == 1)
        {
	        $user_name = Helper::QueryData('username', '#__users', 'id', $jinput->get('user_id', null, 'int') );
	        $this->setFieldValue('user_name', $user_name);
        }  
         
        parent::onBeforeCheck();
    }
}