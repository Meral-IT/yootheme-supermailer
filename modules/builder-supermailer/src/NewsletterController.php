<?php

/**
 * @package   Meral YOOtheme Pro SuperMailer
 * @author    Necati Meral https://meral.cloud
 * @source    https://meral.cloud
 * @copyright Copyright (C) Meral IT, DE
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Meral\Builder\SuperMailer;

use YOOtheme\Http\Request;
use YOOtheme\Http\Response;
use function YOOtheme\app;
use function YOOtheme\trans;

// No direct access
defined('_JEXEC') or die;

class NewsletterController
{
    /**
     * @var array
     */
    protected $providers;

    /**
     * @var string
     */
    protected $secret;

    public function __construct(array $providers, string $secret)
    {
        $this->providers = $providers;
        $this->secret = $secret;
    }

    public function subscribe(Request $request, Response $response)
    {
        $hash = $request->getQueryParam('hash');
        $settings = $request->getParam('settings');

        $request->abortIf($hash !== $this->getHash($settings), 400, 'Invalid settings hash');

        try {
            $settings = $this->decodeData($settings);

            $request->abortIf(
                !($provider = $this->getProvider($settings['name'] ?? '')),
                400,
                'Invalid provider',
            );

            $provider->subscribe(
                $request->getParam('email'),
                [
                    'first_name' => $request->getParam('first_name', ''),
                    'last_name' => $request->getParam('last_name', ''),
                ],
                $settings,
            );
        } catch (\Exception $e) {
            return $response->withJson($e->getMessage(), 400);
        }

        $return = ['successful' => true];

        if ($settings['after_submit'] === 'redirect') {
            $return['redirect'] = $settings['redirect'];
        } else {
            $return['message'] = trans($settings['message']);
        }

        return $response->withJson($return);
    }

    public function confirm(Request $request, Response $response)
    {
        try {

            $request->abortIf(
                !($idOrEmail = $request->getParam('data')),
                400,
                'Missing data',
            );

            $request->abortIf(
                !($provider = $this->getProvider($request->getParam('provider') ?? '')),
                400,
                'Invalid provider',
            );

            $provider->confirm($idOrEmail);
        } catch (\Exception $e) {
            return $response->withJson($e->getMessage(), 400);
        }
    }

    public function unsubscribe(Request $request, Response $response)
    {
        try {

            $request->abortIf(
                !($idOrEmail = $request->getParam('guid')),
                400,
                'Missing data',
            );

            $providerName = $request->getParam('provider', 'supermailer');

            $request->abortIf(
                !($provider = $this->getProvider($providerName)),
                400,
                'Invalid provider',
            );

            $provider->unsubscribe($idOrEmail);
        } catch (\Exception $e) {
            return $response->withJson($e->getMessage(), 400);
        }
    }

    public function getHash(string $data): string
    {
        return hash('fnv132', hash_hmac('sha1', $data, $this->secret));
    }

    public function encodeData(array $data): string
    {
        return base64_encode(json_encode($data));
    }

    public function decodeData(string $data): array
    {
        return json_decode(base64_decode($data), true);
    }

    protected function getProvider(string $name): ?AbstractProvider
    {
        return isset($this->providers[$name]) ? app($this->providers[$name]) : null;
    }
}
