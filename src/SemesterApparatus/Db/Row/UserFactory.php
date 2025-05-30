<?php

/**
 * User row gateway factory.
 *
 * PHP version 7
 *
 * Copyright (C) Villanova University 2017.
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
 * @link     https://vufind.org/wiki/development Wiki
 */

namespace SemesterApparatus\Db\Row;

use Psr\Container\ContainerInterface;
use VuFind\Db\Row\RowGatewayFactory;

/**
 * User row gateway factory.
 *
 * @category VuFind
 * @package  Db_Row
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Johannes Schultze <schultze@effective-webwork.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development Wiki
 */
class UserFactory extends RowGatewayFactory
{
    /**
     * Class name for private user class.
     *
     * @var string
     */
    protected $privateUserClass = __NAMESPACE__ . '\PrivateUser';

    /**
     * Create an object
     *
     * @param ContainerInterface $container     Service manager
     * @param string             $requestedName Service being created
     * @param null|array         $options       Extra options (optional)
     *
     * @return object
     *
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when
     * creating a service.
     * @throws ContainerException&\Throwable if any other error occurs
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    ) {
        if (!empty($options)) {
            throw new \Exception('Unexpected options sent to factory!');
        }
        $config = $container->get(\VuFind\Config\PluginManager::class)
            ->get('config');
        $privacy = isset($config->Authentication->privacy)
            && $config->Authentication->privacy;
        $rowClass = $privacy ? $this->privateUserClass : $requestedName;
        $prototype = parent::__invoke($container, $rowClass, $options);
        $prototype->setConfig($config);
        if ($privacy) {
            $sessionManager = $container
                ->get(\Laminas\Session\SessionManager::class);
            $session = new \Laminas\Session\Container('Account', $sessionManager);
            $prototype->setSession($session);
        }
        return $prototype;
    }
}
