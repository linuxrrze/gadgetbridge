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

return [
	'routes' => [
		['name' => 'frontend#show', 'url' => '/', 'verb' => 'GET'],
	],
	'ocs' => [
		['name' => 'api#selectDatabase', 'url' => '/api/v1/database', 'verb' => 'POST'],
		['name' => 'api#getDevices', 'url' => '/api/v1/{databaseId}/devices', 'verb' => 'GET'],
		['name' => 'api#getDeviceData', 'url' => '/api/v1/{databaseId}/devices/{deviceId}/samples/{startTimestamp}/{endTimestamp}', 'verb' => 'GET'],

	],
];
