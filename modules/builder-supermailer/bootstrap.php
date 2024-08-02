<?php

/**
 * @package   Meral YOOtheme Pro SuperMailer
 * @author    Necati Meral https://meral.cloud
 * @source    https://meral.cloud
 * @copyright Copyright (C) Meral IT, DE
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Meral\Builder\SuperMailer;

use YOOtheme\Builder;
use YOOtheme\Config;
use YOOtheme\Path;
use YOOtheme\HttpClientInterface;

// No direct access
defined('_JEXEC') or die;

return [
    'theme' => [
        'superMailerProvider' => [
            'supermailer' => SuperMailerProvider::class
        ],
    ],

    'routes' => [
        [
            'post',
            '/theme/supermailer/subscribe',
            NewsletterController::class . '@subscribe',
            ['csrf' => false, 'allowed' => true],
        ],
        [
            'get',
            '/theme/supermailer/confirm',
            NewsletterController::class . '@confirm',
            ['csrf' => false, 'allowed' => true],
        ],
    ],

    'extend' => [
        Builder::class => function (Builder $builder) {
            $builder->addTypePath(Path::get('./elements/*/element.json'));
        },
    ],

    'services' => [
        SuperMailerProvider::class => function (HttpClientInterface $client, Repository $repository) {
            return new SuperMailerProvider($client, $repository);
        },

        NewsletterController::class => function (Config $config) {
            return new NewsletterController(
                $config('theme.superMailerProvider'),
                $config('app.secret'),
            );
        },
    ],
];
