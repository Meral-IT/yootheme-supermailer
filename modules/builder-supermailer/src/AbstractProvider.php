<?php

/**
 * @package   Meral YOOtheme Pro SuperMailer
 * @author    Necati Meral https://meral.cloud
 * @source    https://meral.cloud
 * @copyright Copyright (C) Meral IT, DE
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Meral\Builder\SuperMailer;

use YOOtheme\HttpClientInterface;

// No direct access
defined('_JEXEC') or die;

abstract class AbstractProvider
{
    /**
     * @var HttpClientInterface
     */
    protected $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $email
     * @param array  $data
     * @param array  $provider
     *
     * @throws \Exception
     *
     * @return bool
     */
    abstract public function subscribe($email, $data, $provider);

    /**
     * @param string $emailOrId
     *
     * @throws \Exception
     *
     * @return bool
     */
    abstract public function confirm($emailOrId);
}
