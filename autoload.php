<?php

/**
 * @package   Meral YOOtheme Pro SuperMailer
 * @author    Necati Meral https://meral.cloud
 * @source    https://meral.cloud
 * @copyright Copyright (C) Meral IT, DE
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

$class_map = array_merge(
    // Core Plugin classes
    include __DIR__ . '/autoload_classmap.php'
);

spl_autoload_register(
    function ($class) use ($class_map) {
        if (isset($class_map[$class]) && file_exists($class_map[$class])) {
            require_once $class_map[$class];

            return true;
        }
    },
    true,
    true
);
