<?php
/**
 * MyResearch Controller
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
 * @package  Controller
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Johannes Schultze <schultze@effective-webwork.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
namespace SemesterApparatus\Controller;

use Laminas\ServiceManager\ServiceLocatorInterface;
use VuFind\Exception\Auth as AuthException;
use VuFind\Exception\Forbidden as ForbiddenException;
use VuFind\Exception\ILS as ILSException;
use VuFind\Exception\ListPermission as ListPermissionException;
use VuFind\Exception\Mail as MailException;
use VuFind\Search\RecommendListener;
use Laminas\Stdlib\Parameters;
use Laminas\View\Model\ViewModel;

/**
 * Controller for the user account area.
 *
 * @category VuFind
 * @package  Controller
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Johannes Schultze <schultze@effective-webwork.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
class MyResearchController extends \VuFind\Controller\MyResearchController
{
    /**
     * SemesterApparatus configuration
     *
     * @var \Laminas\Config\Config
     */
    protected $semesterApparatusConfig;

    /**
     * Mail configuration
     *
     * @var \Laminas\Config\Config
     */
    protected $mailConfig;

    /**
     * Mailer service
     *
     * @var \VuFind\Mailer\Mailer
     */
    protected $mailer;

    /**
     * Constructor
     *
     * @param ServiceLocatorInterface $sm Service manager
     */
    public function __construct(ServiceLocatorInterface $sm)
    {
        parent::__construct($sm);

        $this->semesterApparatusConfig = $sm->get('VuFind\Config\PluginManager')->get('SemesterApparatus');
        $this->mailConfig = $sm->get('VuFind\Config\PluginManager')->get('config');
        $this->mailer = $sm->get(\VuFind\Mailer\Mailer::class);
    }


    /**
     * Send user's saved favorites from a particular list to the edit view
     *
     * @return mixed
     */
    public function editlistAction()
    {
        // Fail if lists are disabled:
        if (!$this->listsEnabled()) {
            throw new ForbiddenException('Lists disabled');
        }

        // User must be logged in to edit list:
        $user = $this->getUser();
        if ($user == false) {
            return $this->forceLogin();
        }

        // Is this a new list or an existing list?  Handle the special 'NEW' value
        // of the ID parameter:
        $id = $this->params()->fromRoute('id', $this->params()->fromQuery('id'));
        $table = $this->getTable('UserList');
        $newList = ($id == 'NEW');
        $list = $newList ? $table->getNew($user) : $table->getExisting($id);

        // Make sure the user isn't fishing for other people's lists:
        if (!$newList && !$list->editAllowed($user)) {
            throw new ListPermissionException('Access denied.');
        }

        // Process form submission:
        if ($this->formWasSubmitted('submit')) {
            if ($redirect = $this->processEditList($user, $list)) {
                return $redirect;
            }
        }

        $listTags = null;
        if ($this->listTagsEnabled() && !$newList) {
            $listTags = $user->formatTagString($list->getListTags());
        }

        // Send the list to the view:
        return $this->createViewModel(
            [
                'list' => $list,
                'newList' => $newList,
                'listTags' => $listTags,
                // eWW / HAW
                'semesterApparatusLocations' => $this->semesterApparatusConfig->Libraries->locations,
                // eWW / HAW
        ]
        );
    }

    /**
     * Edit record
     *
     * @return mixed
     */
    public function editAction()
    {
        // Force login:
        $user = $this->getUser();
        if (!$user) {
            return $this->forceLogin();
        }

        // Get current record (and, if applicable, selected list ID) for convenience:
        $id = $this->params()->fromPost('id', $this->params()->fromQuery('id'));
        $source = $this->params()->fromPost(
            'source',
            $this->params()->fromQuery('source', DEFAULT_SEARCH_BACKEND)
        );
        $driver = $this->getRecordLoader()->load($id, $source, true);
        $listID = $this->params()->fromPost(
            'list_id',
            $this->params()->fromQuery('list_id', null)
        );

        // Process save action if necessary:
        if ($this->formWasSubmitted('submit')) {
            return $this->processEditSubmit($user, $driver, $listID);
        }

        // Get saved favorites for selected list (or all lists if $listID is null)
        $userResources = $user->getSavedData($id, $listID, $source);
        $savedData = [];
        foreach ($userResources as $current) {
            $savedData[] = [
                'listId' => $current->list_id,
                'listTitle' => $current->list_title,
                'notes' => $current->notes,
                'annotationStudents'  => $current->annotationStudents,
                'annotationStaff'  => $current->annotationStaff,
                'tags' => $user->getTagString($id, $current->list_id, $source),
                'scanStatus' => $current->scanStatus,
                'orderStatus' => $current->orderStatus,
                'physicalAvailable' => $current->physicalAvailable,
            ];
        }

        // In order to determine which lists contain the requested item, we may
        // need to do an extra database lookup if the previous lookup was limited
        // to a particular list ID:
        $containingLists = [];
        if (!empty($listID)) {
            $userResources = $user->getSavedData($id, null, $source);
        }
        foreach ($userResources as $current) {
            $containingLists[] = $current->list_id;
        }

        // Send non-containing lists to the view for user selection:
        $userLists = $user->getLists();
        $lists = [];
        foreach ($userLists as $userList) {
            if (!in_array($userList->id, $containingLists)) {
                $lists[$userList->id] = $userList->title;
            }
        }

        $semesterApparatusScanStatuses = $this->semesterApparatusConfig->Items->scanStatuses;

        $isPhysicalFormat = true;
        $formats = $driver->getFormats();
        foreach ($this->semesterApparatusConfig->items->nonPhysicalFormats as $nonPhysicalFormat) {
            if (in_array($nonPhysicalFormat, $formats)) {
                $isPhysicalFormat = false;
            }
        }

        return $this->createViewModel(
            compact('driver', 'lists', 'savedData', 'listID', 'semesterApparatusScanStatuses', 'isPhysicalFormat')
        );
    }

    /**
     * Process the submission of the edit favorite form.
     *
     * @param \VuFind\Db\Row\User               $user   Logged-in user
     * @param \VuFind\RecordDriver\AbstractBase $driver Record driver for favorite
     * @param int                               $listID List being edited (null
     * if editing all favorites)
     *
     * @return object
     */
    protected function processEditSubmit($user, $driver, $listID)
    {
        $lists = $this->params()->fromPost('lists', []);
        $tagParser = $this->serviceLocator->get(\VuFind\Tags::class);
        $favorites = $this->serviceLocator
            ->get(\SemesterApparatus\Favorites\FavoritesService::class);
        $didSomething = false;
        $physicalAvailable  = $this->params()->fromPost('physicalAvailable', 0);
        if ($physicalAvailable == '') {
            $physicalAvailable = 0;
        }
        $orderStatus  = $this->params()->fromPost('orderStatus', 0);


        // Get the current record status from the database
        $id = $this->params()->fromPost('id', $this->params()->fromQuery('id'));
        $source = $this->params()->fromPost(
            'source',
            $this->params()->fromQuery('source', DEFAULT_SEARCH_BACKEND)
        );

        $userResources = $user->getSavedData($id, $listID, $source);
        $currentStatus = null;
        foreach ($userResources as $current) {
            $currentStatus = $current;
        }

        // Send mail for physical available status change
        if ($physicalAvailable == '1') {
            // Only send email if the status changed from 0 to 1
            if (!$currentStatus || $currentStatus->physicalAvailable != 1) {
                $orderStatus = 1;

                try {
                    $translator = $this->serviceLocator->get(\Laminas\Mvc\I18n\Translator::class);
                    $from = $this->mailConfig->Site->email;
                    $to = $this->semesterApparatusConfig->Order->mail_library;
                    $subject = $translator->translate('Physical item for semester apparatus');
                    $message = $translator->translate('Please insert the following item to the semester apparatus') . ': ' . $driver->getTitle();
                    $this->mailer->send($to, $from, $subject, $message);
                } catch (\Exception $e) {
                }
            }
        }
        if ($orderStatus == '2') {
            if (!$currentStatus || $currentStatus->orderStatus == 1) {
                try {
                    // Get user from database
                    $userTable = $this->getTable('User');
                    $userRecord = $userTable->getById($currentStatus->user_id);
                    if (!$userRecord) {
                        throw new \Exception('User not found');
                    }

                    $translator = $this->serviceLocator->get(\Laminas\Mvc\I18n\Translator::class);
                    $from = $this->mailConfig->Site->email;
                    $to = $userRecord->email;
                    $subject = $translator->translate('Physical item for semester apparatus is available');
                    $message = $translator->translate('The following item is available in the semester apparatus') . ': ' . $driver->getTitle();
                    $this->mailer->send($to, $from, $subject, $message);
                } catch (\Exception $e) {
                }
            }
        }

        foreach ($lists as $list) {
            $tags = $this->params()->fromPost('tags' . $list);
            $favorites->saveSemesterApparatus(
                [
                    'list'  => $list,
                    'mytags'  => $tagParser->parse($tags),
                    'notes' => $this->params()->fromPost('notes' . $list),
                    'annotationStudents'  => $this->params()->fromPost('annotationStudents', []),
                    'annotationStaff'  => $this->params()->fromPost('annotationStaff', []),
                    'scanStatus'  => $this->params()->fromPost('scanStatus', []),
                    'orderStatus'  => $orderStatus,
                    'physicalAvailable'  => $physicalAvailable,
                ],
                $user,
                $driver
            );
            $didSomething = true;
        }
        // add to a new list?
        $addToList = $this->params()->fromPost('addToList');
        if ($addToList > -1) {
            $didSomething = true;
            $favorites->save(['list' => $addToList], $user, $driver);
        }
        if ($didSomething) {
            $this->flashMessenger()->addMessage('edit_list_success', 'success');
        }

        $newUrl = null === $listID
            ? $this->url()->fromRoute('myresearch-favorites')
            : $this->url()->fromRoute('userList', ['id' => $listID]);
        return $this->redirect()->toUrl($newUrl);
    }

    /**
     * Send user's saved favorites from a particular list to the view
     *
     * @return mixed
     */
    public function mylistAction()
    {
        // Fail if lists are disabled:
        if (!$this->listsEnabled()) {
            throw new ForbiddenException('Lists disabled');
        }

        // Check for "delete item" request; parameter may be in GET or POST depending
        // on calling context.
        $deleteId = $this->params()->fromPost(
            'delete',
            $this->params()->fromQuery('delete')
        );
        if ($deleteId) {
            $deleteSource = $this->params()->fromPost(
                'source',
                $this->params()->fromQuery('source', DEFAULT_SEARCH_BACKEND)
            );
            // If the user already confirmed the operation, perform the delete now;
            // otherwise prompt for confirmation:
            $confirm = $this->params()->fromPost(
                'confirm',
                $this->params()->fromQuery('confirm')
            );
            if ($confirm) {
                $success = $this->performDeleteFavorite($deleteId, $deleteSource);
                if ($success !== true) {
                    return $success;
                }
            } else {
                return $this->confirmDeleteFavorite($deleteId, $deleteSource);
            }
        }

        // If we got this far, we just need to display the favorites:
        try {
            $runner = $this->serviceLocator->get(\VuFind\Search\SearchRunner::class);

            // We want to merge together GET, POST and route parameters to
            // initialize our search object:
            $request = $this->getRequest()->getQuery()->toArray()
                + $this->getRequest()->getPost()->toArray()
                + ['id' => $this->params()->fromRoute('id')];

            // Set up listener for recommendations:
            $rManager = $this->serviceLocator
                ->get(\VuFind\Recommend\PluginManager::class);
            $setupCallback = function ($runner, $params, $searchId) use ($rManager) {
                $listener = new RecommendListener($rManager, $searchId);
                $listener->setConfig(
                    $params->getOptions()->getRecommendationSettings()
                );
                $listener->attach($runner->getEventManager()->getSharedManager());
            };

            $results = $runner->run($request, 'Favorites', $setupCallback);
            $listTags = [];

            if ($this->listTagsEnabled()) {
                if ($list = $results->getListObject()) {
                    foreach ($list->getListTags() as $tag) {
                        $listTags[$tag->id] = $tag->tag;
                    }
                }
            }

            $savedData = [];
            if (isset($request['id'])) {
                $table = $this->getTable('UserResource');
                $userResources = $table->getSavedDataForListId($request['id']);
                foreach ($userResources as $current) {
                    $savedData[] = [
                        'id' => $current->id,
                        'user_id' => $current->user_id,
                        'resource_id' => $current->resource_id,
                        'list_id' => $current->list_id,
                        'notes' => $current->notes,
                        'saved' => $current->saved,
                        'annotationStudents' => $current->annotationStudents,
                        'annotationStaff' => $current->annotationStaff,
                        'scanStatus' => $current->scanStatus,
                        'orderStatus' => $current->orderStatus,
                        'record_id' => $current->record_id,
                        'title' => $current->title,
                        'author' => $current->author,
                        'year' => $current->year,
                        'source' => $current->source,
                        'extra_metadata' => $current->extra_metadata,
                    ];
                }
            }

            return $this->createViewModel(
                [
                    'params' => $results->getParams(), 'results' => $results,
                    'listTags' => $listTags, 'savedData' => $savedData
                ]
            );
        } catch (ListPermissionException $e) {
            if (!$this->getUser()) {
                return $this->forceLogin();
            }
            throw $e;
        }
    }
}
