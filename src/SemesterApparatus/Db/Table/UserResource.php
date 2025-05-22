<?php

/**
 * Table Definition for user_resource
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
 * @package  Db_Table
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Page
 */

namespace SemesterApparatus\Db\Table;

use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use VuFind\Db\Table\UserResource as VuFindUserResource;

/**
 * Table Definition for user_resource
 *
 * @category VuFind
 * @package  Db_Table
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Page
 */
class UserResource extends VuFindUserResource
{
    /**
     * Create link if one does not exist; update notes if one does.
     *
     * @param string $resource_id ID of resource to link up
     * @param string $user_id     ID of user creating link
     * @param string $list_id     ID of list to link up
     * @param string $notes       Notes to associate with link
     * @param string $annotationStudents  Annotations for students to associate with link
     * @param string $annotationStaff     Annotations for staff to associate with link
     *
     * @return \VuFind\Db\Row\UserResource
     */
    public function createOrUpdateLinkSemesterApparatus(
        $resource_id,
        $user_id,
        $list_id,
        $notes = '',
        $annotationStudents = '',
        $annotationStaff = '',
        $status = ''
    ) {
        $params = [
            'resource_id' => $resource_id, 'list_id' => $list_id,
            'user_id' => $user_id,
        ];
        $result = $this->select($params)->current();

        // Only create row if it does not already exist:
        if (empty($result)) {
            $result = $this->createRow();
            $result->resource_id = $resource_id;
            $result->list_id = $list_id;
            $result->user_id = $user_id;
        }

        // Update the notes:
        $result->notes = $notes;
        $result->annotationStudents = $annotationStudents;
        $result->annotationStaff = $annotationStaff;
        $result->status = $status;
        $result->save();
        return $result;
    }

    /**
     * Get information saved in a user's favorites for a particular record.
     *
     * @param string $resourceId ID of record being checked.
     * @param string $source     Source of record to look up
     * @param int    $listId     Optional list ID (to limit results to a particular
     * list).
     * @param int    $userId     Optional user ID (to limit results to a particular
     * user).
     *
     * @return \Laminas\Db\ResultSet\AbstractResultSet
     */
    public function getSavedDataForListId(
        $listId = null
    ) {
        $callback = function ($select) use ($listId) {
            $select->columns(
                [
                    new Expression(
                        'DISTINCT(?)',
                        ['user_resource.id'],
                        [Expression::TYPE_IDENTIFIER]
                    ), Select::SQL_STAR,
                ]
            );
            $select->join(
                ['r' => 'resource'],
                'r.id = user_resource.resource_id',
                ['*'],
                $select::JOIN_LEFT
            );
            if (null !== $listId) {
                $select->where->equalTo('user_resource.list_id', $listId);
            }
        };
        return $this->select($callback);
    }
}
