<?php

/**
 * @package   Meral YOOtheme Pro SuperMailer
 * @author    Necati Meral https://meral.cloud
 * @source    https://meral.cloud
 * @copyright Copyright (C) Meral IT, DE
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Meral\Builder\SuperMailer;

use YOOtheme\Arr;
use YOOtheme\Builder\Newsletter\NewsletterController;
use YOOtheme\Metadata;
use YOOtheme\Path;
use YOOtheme\Url;
use function YOOtheme\app;

// No direct access
defined('_JEXEC') or die;

return [
    'transforms' => [
        'render' => function ($node) {
            /**
             * @var NewsletterController $controller
             * @var Metadata $meta
             */
            [$controller, $meta] = app(NewsletterController::class, Metadata::class);

            $provider = (array) $node->props['provider'];

            $node->settings = $controller->encodeData(
                array_merge($provider, (array) $node->props[$provider['name']]),
            );
            $node->form = [
                'action' => Url::route('theme/supermailer/subscribe', [
                    'hash' => $controller->getHash($node->settings),
                ]),
            ];

            $meta->set('script:newsletter', [
                'src' => Path::get('../../app/newsletter.js', __DIR__),
                'defer' => true,
            ]);
        },
    ],

    'updates' => [
        '1.22.0-beta.0.1' => function ($node) {
            Arr::updateKeys($node->props, ['gutter' => 'gap']);
        },

        '1.20.0-beta.1.1' => function ($node) {
            Arr::updateKeys($node->props, ['maxwidth_align' => 'block_align']);
        },
    ],
];
