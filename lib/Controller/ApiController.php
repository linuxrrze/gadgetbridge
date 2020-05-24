<?php

/**
 * @copyright Copyright (c) 2017 Joas Schilling <coding@schilljs.com>
 * @copyright Copyright (c) 2020 Dan Meltzer <dmeltzer.devel@gmail.com>
 * 
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\GadgetBridge\Controller;

use OCP\IConfig;
use OCP\IRequest;
use OCP\Files\File;
use OCP\IUserSession;
use OCP\IDBConnection;
use OCP\AppFramework\Http;
use OCP\Files\IRootFolder;
use OCA\GadgetBridge\Database;
use OCP\Files\NotFoundException;
use OCP\AppFramework\OCSController;
use OCP\Files\InvalidPathException;
use OCP\AppFramework\Http\DataResponse;
use OCA\GadgetBridge\InvalidDatabaseException;

class ApiController extends OCSController {


	/** @var IDBConnection */
	protected $connection;
	/** @var IUserSession */
	protected $userSession;
	/** @var IRootFolder */
	protected $rootFolder;
	/** @var IConfig */
	protected $config;

	public function __construct($appName, IRequest $request, IDBConnection $connection, IUserSession $userSession, IRootFolder $rootFolder, IConfig $config) {
		parent::__construct($appName, $request);
		$this->connection = $connection;
		$this->userSession = $userSession;
		$this->rootFolder = $rootFolder;
		$this->config = $config;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @param string $path
	 * @return DataResponse
	 */
	public function selectDatabase($path) {
		$user = $this->userSession->getUser();
		$userFolder = $this->rootFolder->getUserFolder($user->getUID());

		try {
			$dataToImport = $userFolder->get($path);
			$fileId = $dataToImport->getId();
		} catch (NotFoundException $e) {
			return new DataResponse([], Http::STATUS_NOT_FOUND);
		} catch (InvalidPathException $e) {
			return new DataResponse([], Http::STATUS_NOT_FOUND);
		}

		if (!$dataToImport instanceof File) {
			return new DataResponse([], Http::STATUS_UNPROCESSABLE_ENTITY);
		}

		/** @var File $dataToImport */
		$storage = $dataToImport->getStorage();
		$tmpPath = $storage->getLocalFile($dataToImport->getInternalPath());

		try {
			$database = new Database($tmpPath);
		} catch(InvalidDatabaseException $e) {
			return new DataResponse(['error'=>$e], Http::STATUS_UNPROCESSABLE_ENTITY);
		}


		$this->config->setUserValue($user->getUID(), 'gadgetbridge', 'database_file', $fileId);

		return new DataResponse(['fileId' => $fileId]);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @param int $databaseId
	 * @return DataResponse
	 */
	public function getDevices($databaseId) {
		$database = $this->getDatabase($databaseId);

		$devices = $database->getDevices();

		return new DataResponse($devices);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @param int $databaseId
	 * @param int $deviceId
	 * @param int $year
	 * @param int $startTimestamp
	 * @param int $endTimestamp
	 * 
	 * @return DataResponse
	 */
	public function getDeviceData($databaseId, $deviceId, $startTimestamp, $endTimestamp) {
		$database = $this->getDatabase($databaseId);
		$device = $database->getDeviceById($deviceId);

		$start = \DateTime::createFromFormat('U', $startTimestamp);
		$end = \DateTime::createFromFormat('U', $endTimestamp);

		return new DataResponse($device->getSamples($start, $end));
	}

	/**
	 * @param int $database
	 * @return IDBConnection
	 * @throws NotFoundException
	 * @throws \InvalidArgumentException
	 */
	protected function getDatabase($database) {
		try {
			$user = $this->userSession->getUser();
			$userFolder = $this->rootFolder->getUserFolder($user->getUID());
			$databaseFile = $userFolder->getById($database);

			if (count($databaseFile) !== 1 && !$databaseFile[0] instanceof File) {
				throw new \InvalidArgumentException('Unprocessable entity', Http::STATUS_UNPROCESSABLE_ENTITY);
			}
			$databaseFile = $databaseFile[0];

			/** @var File $databaseFile */
			$storage = $databaseFile->getStorage();
			$tmpPath = $storage->getLocalFile($databaseFile->getInternalPath());
			$database = new Database($tmpPath);
		} catch (NotFoundException $e) {
			return new DataResponse([], Http::STATUS_NOT_FOUND);
		} catch (\InvalidArgumentException $e) {
			return new DataResponse([], Http::STATUS_BAD_REQUEST);
		} catch (InvalidDatabaseException $e) {
			return new DataResponse(['error' => $e], Http::STATUS_UNPROCESSABLE_ENTITY);
		}

		
		return $database;
	}
}
