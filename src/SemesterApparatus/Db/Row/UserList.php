<?php

/**
 * Row Definition for user_list
 *
 * PHP version 7
 *
 * Copyright (C) Villanova University 2010.
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
 * @package  Db_Row
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Johannes Schultze <schultze@effective-webwork.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */

namespace SemesterApparatus\Db\Row;

use VuFind\Db\Row\UserList as VuFindUserList;
use VuFind\Exception\ListPermission as ListPermissionException;
use VuFind\Exception\MissingField as MissingFieldException;

;

/**
 * Row Definition for user_list
 *
 * @category VuFind
 * @package  Db_Row
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Johannes Schultze <schultze@effective-webwork.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 *
 * @property bool   $semesterApparatus
 * @property string $seminarTitle
 * @property string $nameOfLecturer
 * @property string $linkToMoodle
 * @property string $location
 */
class UserList extends VuFindUserList
{
    use \VuFind\Db\Table\DbTableAwareTrait;

    /**
     * Check if this list is a semester apparatus
     *
     * @return bool True if this is a semester apparatus list
     */
    public function isSemesterApparatus(): bool
    {
        return isset($this->semesterApparatus) && ($this->semesterApparatus == 1);
    }

    /**
     * Get the seminar title
     *
     * @return string The title of the associated seminar
     */
    public function getSeminarTitle(): string
    {
        return $this->seminarTitle;
    }

    /**
     * Get the name of the lecturer
     *
     * @return string The name of the lecturer for this seminar
     */
    public function getNameOfLecturer(): string
    {
        return $this->nameOfLecturer;
    }

    /**
     * Get the link to Moodle
     *
     * @return string The URL link to the associated Moodle course
     */
    public function getLinkToMoodle(): string
    {
        return $this->linkToMoodle;
    }

    /**
     * Get the location
     *
     * @return string The physical location of the seminar
     */
    public function getLocation(): string
    {
        return $this->location;
    }


    /**
     * Update and save the list object using a request object -- useful for
     * sharing form processing between multiple actions.
     *
     * @param \VuFind\Db\Row\User|bool   $user    Logged-in user (false if none)
     * @param \Laminas\Stdlib\Parameters $request Request to process
     *
     * @return int ID of newly created row
     * @throws ListPermissionException
     * @throws MissingFieldException
     */
    public function updateFromRequest($user, $request)
    {
        $this->title = $request->get('title');
        $this->description = $request->get('desc');
        $this->public = $request->get('public');
        $this->semesterApparatus = $request->get('semesterApparatus') === 'on' ? '1' : '0';
        $this->seminarTitle = $request->get('seminarTitle');
        $this->nameOfLecturer = $request->get('nameOfLecturer');
        $this->linkToMoodle = $request->get('linkToMoodle');
        $this->location = $request->get('location');
        $this->save($user);

        if (null !== ($tags = $request->get('tags'))) {
            $linker = $this->getDbTable('resourcetags');
            $linker->destroyListLinks($this->id, $user->id);
            foreach ($this->tagParser->parse($tags) as $tag) {
                $this->addListTag($tag, $user);
            }
        }

        return $this->id;
    }
}
