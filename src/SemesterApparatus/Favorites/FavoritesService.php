<?php

/**
 * Favorites service
 *
 * PHP version 7
 *
 * Copyright (C) Villanova University 2016.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category VuFind
 * @package  Favorites
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Johannes Schultze <schultze@effective-webwork.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Page
 */

namespace SemesterApparatus\Favorites;

use VuFind\Exception\LoginRequired as LoginRequiredException;
use VuFind\Favorites\FavoritesService as VuFindFavoritesService;
use VuFind\Record\Cache as RecordCache;
use SemesterApparatus\Db\Table\Resource as ResourceTable;
use SemesterApparatus\Db\Table\UserList as UserListTable;
use VuFind\RecordDriver\AbstractBase as RecordDriver;

/**
 * Favorites service
 *
 * @category VuFind
 * @package  Favorites
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Johannes Schultze <schultze@effective-webwork.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Page
 */
class FavoritesService extends VuFindFavoritesService
{
    /**
     * Constructor
     *
     * @param UserListTable $userList UserList table object
     * @param ResourceTable $resource Resource table object
     * @param RecordCache   $cache    Record cache
     */
    public function __construct(
        UserListTable $userList,
        ResourceTable $resource,
        RecordCache $cache = null
    ) {
        $this->recordCache = $cache;
        $this->userListTable = $userList;
        $this->resourceTable = $resource;
    }

    /**
     * Save this record to the user's favorites.
     *
     * @param array               $params Array with some or all of these keys:
     *  <ul>
     *    <li>mytags - Tag array to associate with record (optional)</li>
     *    <li>notes - Notes to associate with record (optional)</li>
     *    <li>list - ID of list to save record into (omit to create new list)</li>
     *  </ul>
     * @param \VuFind\Db\Row\User $user   The user saving the record
     * @param RecordDriver        $driver Record driver for record being saved
     *
     * @return array list information
     */
    public function saveSemesterApparatus(
        array $params,
        \SemesterApparatus\Db\Row\User $user,
        RecordDriver $driver
    ) {
        // Validate incoming parameters:
        if (!$user) {
            throw new LoginRequiredException('You must be logged in first');
        }

        // Get or create a list object as needed:
        $list = $this->getListObject(
            $params['list'] ?? '',
            $user
        );

        // Get or create a resource object as needed:
        $resource = $this->resourceTable->findResource(
            $driver->getUniqueId(),
            $driver->getSourceIdentifier(),
            true,
            $driver
        );

        // Persist record in the database for "offline" use
        $this->persistToCache($driver, $resource);

        // Add the information to the user's account:
        $user->saveResourceSemesterApparatus(
            $resource,
            $list,
            $params['mytags'] ?? [],
            $params['notes'] ?? '',
            $params['annotationStudents'] ?? '',
            $params['annotationStaff'] ?? '',
            $params['status'] ?? '',
        );
        return ['listId' => $list->id];
    }
}
