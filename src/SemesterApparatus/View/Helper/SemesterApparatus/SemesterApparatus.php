<?php

/**
 * SemesterApparatus view helper
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
 * @package  View_Helpers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Johannes Schultze <schultze@effective-webwork.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development Wiki
 */
namespace SemesterApparatus\View\Helper\SemesterApparatus;

/**
 * SemesterApparatus view helper
 *
 * @category VuFind
 * @package  View_Helpers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Johannes Schultze <schultze@effective-webwork.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development Wiki
 */
class SemesterApparatus extends \Laminas\View\Helper\AbstractHelper
{
    /**
     * Configuration
     *
     * @var \Laminas\Config\Config
     */
    protected $config;

    /**
     * Current user
     */
    protected $user;

    /**
     * Class constructor
     *
     * @param mixed $config Configuration settings
     * @param mixed|null $user Optional user object or data
     * @return void
     */
    public function __construct($config, $user = null)
    {
        $this->config = $config;
        $this->user = $user;
    }

    /**
     * Get user types
     *
     * @return array
     */
    public function getUserTypes() {
        $userTypes = [];
        if ($this->user) {
            if (isset($this->user['type'])) {
                if (is_array($this->user['type'])) {
                    $userType = $this->user['type'][0];
                }
                $userType = $this->user['type'];
            }
        }
        if ($userType && isset($userType[0]) && !empty($userType[0])) {
            $userTypeArray = explode(':', $userType[0]);
            if (isset($userTypeArray[2]) && !empty($userTypeArray[2])) {
                if (in_array($userTypeArray[2], $this->config->UserTypes->lecturer->toArray())) {
                    $userTypes[] = 'lecturer';
                }
                if (in_array($userTypeArray[2], $this->config->UserTypes->library->toArray())) {
                    $userTypes[] = 'library';
                }
            }
        }
        return $userTypes;
    }


    /**
     * Check if user is lecturer
     *
     * @return bool
     */
    public function isLecturer() {
        return in_array('lecturer', $this->getUserTypes());
    }

    /**
     * Check if user is library staff
     *
     * @return bool
     */
    public function isLibrary() {
        return in_array('library', $this->getUserTypes());
    }

}
