<?php
/*
 * @package com_gscrm = Simple CRM for Joomla
 * @copyright Copyright (c)2017 Pedro Bicudo / bicudomaschio@gmail.com
 * @license GNU General Public License version 2 or later
 */

defined('_JEXEC') or die();

/*
* We do not need GscrmBuildRoute(&$query) and ParseRoute($segments) because FOF does that for us
*
* But if Joomla Global Configuration "Search Engine Friendly URLs" is set to NO, the menu item id is not parsed in some cases
* and browse view may fall to the default template or home page menu-item. If default or home use different template layout, i.e. a different screen size
* the user will see trunkated lists (when not SEF URL)
*
* To avoid that case, we need to parse menu id to the URL 
*/


// Load FOF
if (!defined('FOF30_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof30/include.php'))
{
    throw new RuntimeException('FOF 3.0 is not installed', 500);
}
// Get the container so our autoloader gets registered
FOF30\Container\Container::getInstance('com_gscrm');

use \Gs\Gscrm\Admin\Helper\Helper;

//get language to apply filter
$lang = JFactory::getLanguage()->getTag();

// define superglobals for menu ids
global $menu_item;
$menu_item = Helper::GetMenus($lang);


