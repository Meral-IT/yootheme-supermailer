<?php

/**
 * @package   Meral YOOtheme Pro SuperMailer
 * @author    Necati Meral https://meral.cloud
 * @source    https://meral.cloud
 * @copyright Copyright (C) Meral IT, DE
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Meral\Builder\SuperMailer;

use DateTime;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

// No direct access
defined('_JEXEC') or die;

class Repository
{
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

	/**
	 * @param string $email
	 * @param array  $data
	 * @param array  $provider
	 * @param string $recipient
	 * @param DateTime $expiration
	 *
	 * @throws \Exception
	 *
	 * @return string
	 */
	function addSubscription($email, $data, $recipient, $expiration)
	{
		$id = $this->getExistingGuid($email);
		if (!is_null($id)) {
			return $id;
		}

		$id = $this->guidv4();
		$db = $this->getDatabase();

		$query = $db->getQuery(true);
		$query
			->insert($db->quoteName('#__supermailer'))
			->columns(
				[
					$db->quoteName('id'),
					$db->quoteName('email'),
					$db->quoteName('recipient'),
					$db->quoteName('expiration'),
					$db->quoteName('payload')
				]
			)
			->values(
				implode(
					',',
					$query->bindArray(
						[$id, $email, $recipient, $expiration, json_encode($data)],
						[ParameterType::STRING, ParameterType::STRING, ParameterType::STRING, ParameterType::STRING, ParameterType::STRING]
					)
				)
			);

		$db->setQuery($query);
		$db->execute();

		return $id;
	}

	function confirmSubscription($guid)
	{
		$db = $this->getDatabase();
		$query = $db->getQuery(true)
			->update($db->quoteName('#__supermailer'))
			->set($db->quoteName('confirmation') . ' = CURRENT_TIMESTAMP()')
			->set($db->quoteName('state') . ' = ' .  $db->quote(2))
			->where($db->quoteName('id') . ' = ' . $db->quote($guid));
		$db->setQuery($query);
		$db->execute();
	}

	function removeExpiredRegistrations()
	{
		$db = $this->getDatabase();
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__supermailer'))
			->where($db->quoteName('expiration') . ' <= CURRENT_TIMESTAMP() and ' . $db->quoteName('state') . ' = 0');
		$db->setQuery($query);
		$db->execute();
	}

	function getExistingGuid($emailOrGuid)
	{
		$db = $this->getDatabase();
		$query = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from($db->quoteName('#__supermailer'))
			->where(
				"upper(" . $db->quoteName('email') . ') = upper(' . $db->quote($emailOrGuid) . ')'
					. ' OR ' .
					"upper(" . $db->quoteName('id') . ') = upper(' . $db->quote($emailOrGuid) . ')'
			);

		$db->setQuery($query);
		$db->execute();

		return $db->loadResult();
	}

	/**
	 * Delete an item from the database
	 *
	 * @param string $id The id of the item to delete
	 *
	 * @throws \RuntimeException
	 *
	 * @return boolean
	 */
	function removeSubscription(string $id): bool
	{
		$db = $this->getDatabase();
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__supermailer'))
			->where(
				"upper(" . $db->quoteName('id') . ') = upper(' . $db->quote($id) . ')'
			);

		$db->setQuery($query);
		return $db->execute();
	}

	function getSubscription($emailOrGuid)
	{
		if ($this->getExistingGuid($emailOrGuid) == null) {
			return false;
		}

		$db = $this->getDatabase();
		$query = $db->getQuery(true)
			->select(
				$db->quoteName(
					[
						'id',
						'email',
						'recipient',
						'expiration',
						'registration',
						'confirmation',
						'payload',
						'state',
					]
				)
			)
			->from($db->quoteName('#__supermailer'))
			->where(
				"upper(" . $db->quoteName('email') . ') = upper(' . $db->quote($emailOrGuid) . ')'
					. ' OR ' .
					"upper(" . $db->quoteName('id') . ') = upper(' . $db->quote($emailOrGuid) . ')'
			);

		$db->setQuery($query);
		$data = $db->loadObject();

		if ($data !== null) {
			$data->payload = json_decode($data->payload, true);
		}

		return $data;
	}

	// source: http://php.net/manual/de/function.com-create-guid.php
	function GUIDv4($trim = true)
	{
		// Windows
		if (function_exists('com_create_guid') === true) {
			if ($trim === true)
				return trim(com_create_guid(), '{}');
			else
				return com_create_guid();
		}

		// OSX/Linux
		if (function_exists('openssl_random_pseudo_bytes') === true) {
			$data = openssl_random_pseudo_bytes(16);
			$data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // set version to 0100
			$data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // set bits 6-7 to 10
			return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
		}

		// Fallback (PHP 4.2+)
		mt_srand((float)microtime() * 10000);
		$charid = strtolower(md5(uniqid(rand(), true)));
		$hyphen = chr(45);                  // "-"
		$lbrace = $trim ? "" : chr(123);    // "{"
		$rbrace = $trim ? "" : chr(125);    // "}"
		$guidv4 = $lbrace .
			substr($charid,  0,  8) . $hyphen .
			substr($charid,  8,  4) . $hyphen .
			substr($charid, 12,  4) . $hyphen .
			substr($charid, 16,  4) . $hyphen .
			substr($charid, 20, 12) .
			$rbrace;
		return $guidv4;
	}
}
