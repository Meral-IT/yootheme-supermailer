<?php

/**
 * @package   Meral YOOtheme Pro SuperMailer
 * @author    Necati Meral https://meral.cloud
 * @source    https://meral.cloud
 * @copyright Copyright (C) Meral IT, DE
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Meral\Builder\SuperMailer;

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Mail\MailerInterface;
use Joomla\CMS\Mail\MailerFactoryInterface;
use YOOtheme\HttpClientInterface;
use YOOtheme\Url;

// No direct access
defined('_JEXEC') or die;

class SuperMailerProvider extends AbstractProvider
{
    /**
     * @var Repository
     */
    protected $repository;


    public function __construct(HttpClientInterface $client, Repository $repository)
    {
        $this->client = $client;
        $this->repository = $repository;
    }
    /**
     * @inheritdoc
     */
    public function subscribe($email, $data, $provider)
    {
        try {
            $this->repository->removeExpiredRegistrations();
        } catch (\Exception) {
        }

        if ($provider['double_optin']) {

            $expirationDays = intval($provider['double_optin_expiration']) ?: 0;
            $expiration = $expirationDays > 0
                ? date("Y-m-d H:i:s", strtotime(sprintf("+%d days", $expirationDays)))
                : null;

            $id = $this->repository->addSubscription(
                $email,
                $data,
                $provider['double_optin_recipient'],
                $expiration
            );

            $this->sendOptInMail($email, $provider, $id);
        } else {

            $id = $this->repository->addSubscription(
                $email,
                $data,
                $provider['double_optin_recipient'],
                null
            );

            $this->repository->confirmSubscription($id);

            $this->addSuperMailerRegistration(
                $id,
                $email,
                $provider['supermailer_recipient'],
                $data
            );
        }

        return true;
    }

    /**
     * @param string $emailOrId
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function confirm($emailOrId)
    {
        $id = $this->repository->getExistingGuid($emailOrId);
        if ($id) {
            $this->repository->confirmSubscription($id);
            $subscription = $this->repository->getSubscription($id);

            $this->addSuperMailerRegistration(
                $id,
                $subscription->email,
                $subscription->recipient,
                $subscription->payload
            );
            $this->redirect();
        }
    }

    private function redirect()
    {
        $baseUri = trim(Uri::base(), '/');
        $redirect = "Location: $baseUri";
        header($redirect);
        exit;
    }

    /**
     * Retrieve a mailer resource
     *
     * @return  MailerInterface  The requested resource
     *
     * @since   1.0
     * @throws  KeyNotFoundException
     */
    private function getMailer()
    {
        return Factory::getContainer()->get(MailerFactoryInterface::class)->createMailer();
    }

    private function sendOptInMail($email, $provider, $id)
    {
        $baseUri = trim(Uri::base(), '/');
        $confirmUri = $baseUri . Url::route('theme/supermailer/confirm', [
            'data' => $id,
            'provider' => $provider['name'],
        ]);

        $body = str_replace("[BESTAETIGUNGSLINK]", $confirmUri, $provider['double_optin_content']);

        $mailer = $this->getMailer();
        $mailer->addRecipient($email);
        $mailer->setSubject($provider['double_optin_subject']);
        $mailer->setBody($body);
        $mailer->send();
    }

    private function addSuperMailerRegistration($id, $email, $recipient, $data)
    {
        $body = "EMail: $email\n";
        $body .= "DatumZeit: " . date('m/j/y H:i:s') . "\n";
        $body .= "GUID: " . $id . "\n";

        $name = $this->getName($data);
        if ($name) {
            $body .= "Name: $name\n";
        }

        $mailer = $this->getMailer();
        $mailer->addRecipient($recipient);
        $mailer->setSubject('subscribe');
        $mailer->setBody($body);
        $mailer->send();
    }

    private function getName($data)
    {
        if (isset($data['first_name']) && isset($data['last_name'])) {
            return $data['first_name'] . ' ' . $data['last_name'];
        } else if (isset($data['first_name'])) {
            return $data['first_name'];
        } else if (isset($data['last_name'])) {
            return $data['last_name'];
        }
        return null;
    }
}
