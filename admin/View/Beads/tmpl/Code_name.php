<?php
/*
 * @package com_gscrm
 * @copyright Copyright (c)2017 Pedro L Bicudo Maschio / bicudomaschio@gmail.com
 * @license GNU General Public License version 2 or later
 */

namespace Gs\Gscrm\admin\View\Code_name;

// Show company code name with id

use \JText;
use \Gs\Gscrm\Admin\Helper\Helper;

// no direct access
defined('_JEXEC') or die('Restricted access');

//set navigation id 
$code_id = (int)$model->code;
$code_name = Helper::QueryData('title', '#__gscrm_codes', 'gscrm_code_id', $code_id);
$answer = '('.$code_id.')'.$code_name;

?>
<div>			        
	<a><?php echo $answer ?></a>	    
</div>
