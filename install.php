<?php

/**
 * @package   Meral YOOtheme Pro SuperMailer
 * @author    Necati Meral https://meral.cloud
 * @source    https://meral.cloud
 * @copyright Copyright (C) Meral IT, DE
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;

// No direct access
defined('_JEXEC') or die;

class plgSystemYoosupermailerInstallerScript extends InstallerScript
{
    protected $minimumYOOthemeVersion = '4.0.0';

    public function __construct()
    {
        // Define the minumum versions to be supported.
        $this->minimumJoomla = '4.4';
        $this->minimumPhp    = '8.0.0';
    }

    public function uninstall($parent)
    {
    }

    public function update($parent)
    {
    }

    public function preflight($type, $parent)
    {
        $valid = parent::preflight($type, $parent);

        if ($valid) {
            // Check the minimum YOOtheme Pro version
            $yoothemeManifest = simplexml_load_file(JPATH_SITE . '/templates/yootheme/templateDetails.xml');
            if (!$yoothemeManifest or !version_compare((string) $yoothemeManifest->version, $this->minimumYOOthemeVersion, '>=')) {
                $msg = '<p>You need YOOtheme Pro ' . $this->minimumYOOthemeVersion . ' or later to install this plugin.</p>';
                Log::add($msg, Log::WARNING, 'jerror');

                $valid = false;
            }
        }

        return $valid;
    }

    public function postflight($type, $parent)
    {
        if (!in_array($type, ['install', 'update'])) {
            return;
        }

        if ($type === 'install') {
            $this->enablePlugin();
        }

        return true;
    }

    /**
     * Retrieve a database resource
     *
     * @return  DatabaseInterface  The requested resource
     *
     * @since   1.0
     * @throws  KeyNotFoundException
     */
    private function getDatabase()
    {
        return Factory::getContainer()->get(DatabaseInterface::class);
    }

    private function enablePlugin()
    {
        try {
            $db = $this->getDatabase();
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__extensions'))
                ->set($db->quoteName('enabled') . ' = ' . $db->quote(1))
                ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
                ->where($db->quoteName('element') . ' = ' . $db->quote('yoosupermailer'));
            $db->setQuery($query);
            $db->execute();
        } catch (\Exception $e) {
            return;
        }
    }
}
