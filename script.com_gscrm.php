<?php
/**
 * @package      gscrm
 * @copyright    Copyright for package gscrm (c)2017 Pedro L B Maschio / bicudomaschio@gmail.com
 *
 * Original script kindly provided by Nicholas K. Dionysopoulos
 *
 * @copyright Copyright (c)2011-2016 Nicholas K. Dionysopoulos / AkeebaBackup.com
 * @license GNU GPL v3 or later
 */

// no direct access
defined('_JEXEC') or die();

// Load FOF if not already loaded
if (!defined('FOF30_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof30/include.php'))
{
	throw new RuntimeException('This component requires FOF 3.0.');
}

class Com_GsInstallerScript extends \FOF30\Utils\InstallScript
{
	/**
	 * The component's name
	 *
	 * @var   string
	 */
	protected $componentName = 'gscrm';

	/**
	 * The title of the component (printed on installation and uninstallation messages)
	 *
	 * @var string
	 */
	protected $componentTitle = 'Simple CRM';

	/**
	 * The minimum PHP version required to install this extension
	 *
	 * @var   string
	 */
	protected $minimumPHPVersion = '5.3.10';

	/**
	 * The minimum Joomla! version required to install this extension
	 *
	 * @var   string
	 */
	protected $minimumJoomlaVersion = '3.4.0';

	/**
	 * Obsolete files and folders to remove from both paid and free releases. This is used when you refactor code and
	 * some files inevitably become obsolete and need to be removed.
	 *
	 * @var   array
	 */
	protected $removeFilesAllVersions = array(
		//check com_ats for references of what to include here
			);

	public function postflight($type, $parent)
	{
		// Remove the update sites for this component on installation. The update sites are now handled at the package
		// level.
		$this->removeObsoleteUpdateSites($parent);

		// Call the parent method
		parent::postflight($type, $parent);
	}

	/**
	 * Renders the post-installation message
	 *
	 * @param  \JInstallerAdapterComponent
	 */
	protected function renderPostInstallation($parent)
	{
		$this->warnAboutJSNPowerAdmin();
?>

	<h1>Simple CRM</h1>

	<div>
		<p>You can send us your translation files to include in the installation package, contact us: bicudomaschio@gmail.com</p>.
	</div>

	<div>
		<h2> Welcome to Simple CRM!</h2>
		<p>Learn how to use at go-source.com/help</p>
	</div>

<?php
	}

	protected function renderPostUninstallation($parent)
	{
?>
<h2>Simple CRM Uninstallation</h2>
<p>We are sorry that you decided to uninstall. Check what is new at go-source.com</p>

<?php
		parent::renderPostUninstallation($parent);
	}

	/**
	 * Removes obsolete update sites created for the component (we are now using an update site for the package, not the
	 * component).
	 *
	 * @param   JInstallerAdapterComponent  $parent  The parent installer
	 */
	protected function removeObsoleteUpdateSites($parent)
	{
		$db = $parent->getParent()->getDBO();

		$query = $db->getQuery(true)
					->select($db->qn('extension_id'))
					->from($db->qn('#__extensions'))
					->where($db->qn('type') . ' = ' . $db->q('component'))
					->where($db->qn('name') . ' = ' . $db->q($this->componentName));
		$db->setQuery($query);
		$extensionId = $db->loadResult();

		if (!$extensionId)
		{
			return;
		}

		$query = $db->getQuery(true)
					->select($db->qn('update_site_id'))
					->from($db->qn('#__update_sites_extensions'))
					->where($db->qn('extension_id') . ' = ' . $db->q($extensionId));
		$db->setQuery($query);

		$ids = $db->loadColumn(0);

		if (!is_array($ids) && empty($ids))
		{
			return;
		}

		foreach ($ids as $id)
		{
			$query = $db->getQuery(true)
						->delete($db->qn('#__update_sites'))
						->where($db->qn('update_site_id') . ' = ' . $db->q($id));
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (\Exception $e)
			{
				// Do not fail in this case
			}
		}
	}
}