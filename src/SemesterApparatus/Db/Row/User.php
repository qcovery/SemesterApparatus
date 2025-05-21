<?php

/**
 * Row Definition for user
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
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */

namespace SemesterApparatus\Db\Row;

use VuFind\Db\Row\User as VuFindUser;

/**
 * Row Definition for user
 *
 * @category VuFind
 * @package  Db_Row
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 *
 */
class User extends VuFindUser
{
    /**
     * Add/update a resource in the user's account.
     *
     * @param \VuFind\Db\Row\Resource $resource        The resource to add/update
     * @param \VuFind\Db\Row\UserList $list            The list to store the resource
     * in.
     * @param array                   $tagArray        An array of tags to associate
     * with the resource.
     * @param string                  $notes           User notes about the resource.
     * @param string                  $annotationStudents   Annotations for students to associate with link
     * @param string                  $annotationStaff      Annotations for staff to associate with link
     * @param bool                    $replaceExisting Whether to replace all
     * existing tags (true) or append to the existing list (false).
     *
     * @return void
     */
    public function saveResourceSemesterApparatus(
        $resource,
        $list,
        $tagArray,
        $notes,
        $annotationStudents,
        $annotationStaff,
        $replaceExisting = true
    ) {
        // Create the resource link if it doesn't exist and update the notes in any
        // case:
        $linkTable = $this->getDbTable('UserResource');
        $linkTable->createOrUpdateLinkSemesterApparatus($resource->id, $this->id, $list->id, $notes, $annotationStudents, $annotationStaff);

        // If we're replacing existing tags, delete the old ones before adding the
        // new ones:
        if ($replaceExisting) {
            $resource->deleteTags($this, $list->id);
        }

        // Add the new tags:
        foreach ($tagArray as $tag) {
            $resource->addTag($tag, $this, $list->id);
        }
    }
}
