<?php
/*
 * @package com_gscrm
 * @copyright (c)2017 Pedro L Bicudo Maschio / bicudomaschio@gmail.com
 * @license GNU General Public License version 2 or later
 */

namespace Gs\Gscrm\Site\View\Email;

// navigation buttons used in forms

use \JText;
use \Gs\Gscrm\Admin\Helper\Helper;

// no direct access
defined('_JEXEC') or die('Restricted access');

//get id and emails
$item_id = (int)$model->gscrm_account_id;
$emails = Helper::AccountEmails($item_id);
$count = count($emails);

?>
<div class="col-sm-12">

		<input type="hidden" name="count_emails" id="count_emails" value="<?php echo $count ?>">
	<?php
		foreach( $emails as $ii => $email) 
		{  
	?>
			<p class="high10 ml1"><input type="checkbox" class="high10" name="del_email<?php echo $ii ?>" id="del_email<?php echo $ii ?>" 
			value="<?php echo $email['gscrm_email_id'] ?>" title="<?php echo JText::_('COM_GSCRM_ACCOUNTS_EMAIL_DELETE') ?>">
			<a href="mailto:<?php echo $email['title'] ?>"><?php echo $email['title'];?></a></p>
	<?php 
		} ?>
</div>

