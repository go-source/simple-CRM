<?php
/*
 * @package com_gscrm
 * @copyright (c)2017 Pedro Bicudo / bicudomaschio@gmail.com
 * @license GNU General Public License version 2 or later
 */

namespace Gs\Gscrm\Site\View\Notes_datetime;

/*
	*	change date timezone and format based on user preferences
	*	int (1) = joomla server timezone
	*	int (2) = timezone select by client in company code preferences
*/

use \JText;
use \Gs\Gscrm\Admin\Helper\Helper;

// no direct access
defined('_JEXEC') or die('Restricted access');

//set navigation id 
$field = $model->created_on;

?>
<div>
	<span><?php echo Helper::User_timezone( $field, 2) ?></span>
</div>
