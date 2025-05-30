<?php

/**
 * Row Definition for user_resource
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

use VuFind\Db\Row\UserResource as VuFindUserResource;

/**
 * Row Definition for user_resource
 *
 * @category VuFind
 * @package  Db_Row
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Johannes Schultze <schultze@effective-webwork.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 *
 * @property string $annotationStudents
 * @property string $annotationStaff
 * @property string $scanStatus
 * @property int physicalAvailable
 */
class UserResource extends VuFindUserResource
{
    /**
     * Get student annotation
     *
     * @return string The annotation text for students, or empty string if no annotation is set
     */
    public function getAnnotationStudents()
    {
        return $this->annotationStudents;
    }

    /**
     * Get staff annotation
     *
     * @return string The annotation text for staff members, or empty string if no annotation is set
     */
    public function getAnnotationStaff()
    {
        return $this->annotationStaff;
    }

    /**
     * Get scanStatus
     *
     * @return string The scanStatus of the item
     */
    public function getScanStatus()
    {
        return $this->scanStatus;
    }

    /**
     * Retrieves the value of the physicalAvailable property.
     *
     * @return mixed The current value of the physicalAvailable property.
     */
    public function getPhysicalAvailable()
    {
        return $this->physicalAvailable;
    }
}
