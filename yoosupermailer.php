<?php

/**
 * @package   Meral YOOtheme Pro SuperMailer
 * @author    Necati Meral https://meral.cloud
 * @source    https://meral.cloud
 * @copyright Copyright (C) Meral IT, DE
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Joomla\CMS\Plugin\CMSPlugin;
use YOOtheme\Application;
use function YOOtheme\app;

// No direct access
defined('_JEXEC') or die;

class plgSystemYoosupermailer extends CMSPlugin
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  1.0.0
     */
    protected $autoloadLanguage = true;

    public function onAfterInitialise()
    {
        // Check if YOOtheme Pro is loaded
        if (!class_exists(Application::class, false)) {
            return;
        }

        include_once __DIR__ . '/autoload.php';

        // Load all modules
        app()->load(__DIR__ . '/modules/*/bootstrap.php');
    }
}
