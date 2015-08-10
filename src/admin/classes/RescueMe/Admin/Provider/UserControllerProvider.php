<?php
/**
 * File containing: User pages controller class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 29. July 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Admin\Provider;

use RescueMe\Admin\Security\Accessible;
use RescueMe\DB;
use RescueMe\Locale;
use RescueMe\Manager;
use RescueMe\Missing;
use RescueMe\Operation;
use RescueMe\Roles;
use RescueMe\User;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;

/**
 * User pages controller class
 * @package RescueMe\Controller
 */
class UserControllerProvider extends AbstractControllerProvider {

    const REDIRECT = '/admin/user/list';
    const FILTER_ACTIVE = 'op_closed IS NOT NULL AND user_id=%1$d';
    const FILTER_LOCATED = 'missing_id IN(SELECT missing.missing_id FROM positions, missing, operations WHERE positions.missing_id = missing.missing_id AND missing.op_id = operations.op_id AND  operations.user_id = %1$d)';

    /**
     * Constructor
     */
    function __construct() {
        parent::__construct('user');
    }

    /**
     * Returns routes to connect to the given application.
     *
     * @param Application $app An Application instance
     *
     * @return ControllerCollection A ControllerCollection instance
     */
    public function connect(Application $app)
    {
        // PHP 5.3 workaround
        $page = $this;

        // Creates a new parent controller based on the default route
        $controllers = $app['controllers_factory'];

        // Register redirects
        $this->redirect($controllers, '/', self::REDIRECT);

        // Resolve user callback
        $object = array('RescueMe\\User','get');

        // Register write access for any user
        $write = $this->writeAny($app);

        // Handle write request
        $this->page($controllers, 'request', $write, array($this, 'getEditContext'))
            ->before(function(Request $request) use($app, $page) {
                if($page->isSecure($app)) {
                    return $app->redirect($request->getUriForPath('/user/new'));
                }
            });
        $this->post($controllers, 'request', array($this, 'request'), $write);

        // Register write access for authenticated users
        $write = $this->write($app, 'user', 'RescueMe\\User');

        // Handle new user from authenticated user
        $this->page($controllers, 'new', $write, array($this, 'getEditContext'));
        $this->post($controllers, 'new', array($this, 'insert'), $write);

        // Set write access to resolvable users
        $write = $write->with($object);

        // Handle edit user
        $this->page($controllers, 'edit', $write, array($this, 'getEditContext'));
        $this->post($controllers, 'edit', array($this, 'update'), $write);

        // set read access to list of resolvable users
        $read = $this->read($app, 'user', 'RescueMe\\User', false);

        // Handle list users
        $this->page($controllers, 'list', $read, array($this, 'getListContext'));

        // Handle view user page
        $this->page($controllers, '/', $read->with($object), array($this, 'getUserContext'));

        // Handle list tab
        $rows = RowServiceProvider::newInstance($app);
        $rows->connect(array($this, 'getUsers'), array($this, 'getColumnsContext'));
        $this->json($controllers, 'list/tab', array($rows, 'paginate'), $read);

        return $controllers;
    }


    /**
     * Insert new user into database
     * @param Application $app Silex application instance
     * @param Request $request Request object
     * @param boolean|User $user Authenticated user
     * @return mixed
     */
    public function insert(Application $app, Request $request, $user) {

        if(true === ($response = PasswordControllerProvider::assertPassword($request))) {

            $email = $request->request->get('email');
            $password = $request->request->get('password');

            // Sanity checks
            if(!User::safe($email)) {

                // Email not valid
                return $this->error($app, $request,
                    T_('Email must contain at least one alphanumeric character'));
            }
            if(User::unique($email) === false) {

                // User name not unique
                return $this->error($app, $request,
                    sprintf(T_('User with e-mail %1$s already exist'), $email));
            }
            // Is page secure?
            $secure = $this->isSecure($app);

            // Encode password
            $password = PasswordControllerProvider::encodePassword($app, $user, $password);

            // Attempt to create new user
            if(false !== ($user = User::create(
                    $request->request->get('name'),
                    $email, $password,
                    $request->request->get('mobile_country'),
                    $request->request->get('mobile'),
                    (int)$request->request->get('role_id'),
                    $secure ? User::ACTIVE : User::PENDING))) {

                if($secure) {

                    // Prepare user modules
                    Manager::prepare($user->id,
                        $request->request->getBoolean('use_system_sms_provider'));

                    // User inserted OK
                    return $app->redirect($request->getUriForPath('/user/list'));
                }

                // Request processed OK
                return $this->confirmation($app, $request, $user, $this,
                    T_('You will receive an SMS when the request is processed'), '/login');
            }

            // Update failed for some unknown reason (see logs for more information)
            return $this->error($app, $request,
                sentences(T_('User not created'),
                    sprintf('<a href="%1$s/logs">%2$s</a>', $request->getBasePath(), T_('Check logs'))));

        }

        // Password is invalid
        return $this->error($app, $request, $response);
    }

    /**
     * Update user
     * @param Application $app Silex application instance
     * @param Request $request Request object
     * @param boolean|User $object Edited user
     * @return mixed
     */
    public function update(Application $app, Request $request, $object) {

        $email = $request->request->get('email');

        // Sanity checks
        if(!User::safe($email)) {

            // Email not valid
            return $this->error($app, $request,
                T_('Email must contain at least one alphanumeric character'));
        }
        if(User::unique($email) === false) {

            // Email not unique
            return $this->error($app, $request,
                sprintf(T_('User with e-mail %1$s already exist'), $email));
        }

        // Attempt to update user
        if(false !== $object->update(
                $request->request->get('name'), $email,
                $request->request->get('mobile_country'),
                $request->request->get('mobile'),
                (int)$request->request->get('role_id'))) {

            // Prepare user modules TODO: Check if prepare succeeded.
            Manager::prepare($object->id,
                $request->request->getBoolean('use_system_sms_provider'));

            // User updated OK
            return $app->redirect($request->getUriForPath('/user/list'));
        }

        // Update failed for some unknown reason (see logs for more information)
        return $this->error($app, $request,
            sentences(T_('User not created'),
                sprintf('<a href="%1$s/logs">%2$s</a>', $request->getBasePath(), T_('Check logs'))));
    }



    /**
     * Get context for routes '/'
     * @param Application $app Silex application
     * @param User $user Authenticated user
     * @param User $object Selected user
     * @return array
     */
    public function getUserContext(Application $app, User $user, $object) {

        // TODO: Add to statistics package
        $traces = array();
        $traces['total'] = (int)Missing::countAll('', $object->id);
        $filter = sprintf(self::FILTER_LOCATED, $object->id);
        $traces['located'] = DB::count(Missing::TABLE, $filter);
        $traces['success'] = $traces['total'] ? ceil(doubleval($traces['located']/$traces['total'])*100) : 0;
        $filter = sprintf(self::FILTER_ACTIVE, $object->id);
        $traces['active'] = DB::count(Operation::TABLE, $filter);

        // Minimal context
        $context = array('traces' => $traces);

        // Allowed to write to user context?
        if($this->isGranted($app, self::WRITE, $object, $user)) {
            $context['editor'] = $this->getEditorMenu($user, $object);
        }
        return $context;
    }


    /**
     * Get context for routes 'new', 'edit', 'request'
     *
     * @param User $user Authenticated user
     * @param User $object Edited user
     * @return array
     */
    public function getEditContext(User $user, $object) {
        return array(
            'roles' => Roles::getOptions(),
            'countries' => Locale::getCountryNames(),
            'other' => !$object || $user->id !== $object->id
        );
    }


    /**
     * Get context for route 'list'
     * @param Request $request
     * @return array
     */
    public function getListContext(Request $request) {
        return array(
            'tabs' => User::getStates(),
            'columns' => $this->getColumnsContext(),
            'ajax_url' => $request->getUriForPath('/user/list/tab')
        );
    }

    /**
     * Get context for route 'list/tab'
     * @return array
     */
    public function getTabContext() {
        return array(
            'no_data' => T_('No users found')
        );
    }


    /**
     * Get columns context
     * @return array
     */
    public function getColumnsContext() {
        return array(
            array('name' => 'name', 'title' => T_("Name")),
            array('name' => 'role', 'title' => T_("Role")),
            array('name' => 'mobile', 'title' => T_("Mobile"), 'class' => 'hidden-phone'),
            array('name' => 'email', 'title' => T_("E-mail"), 'class' => 'hidden-phone')
        );
    }

    /**
     * Get users from given query
     * @param Application $app Silex application
     * @param User $user Authenticated user
     * @param string $name Tab name
     * @param string $filter Row filter
     * @param integer $start Start from user number
     * @param integer $max Get maximum number of users
     * @internal param string $page Row page
     * @return array
     */
    public function getUsers(Application $app, User $user, $name, $filter, $start, $max) {
        $filter = User::filter($filter, 'OR');
        if($users = User::count($name, $filter)) {
            $users = User::getRows($name, $filter, $start, $max);
            $roles = Roles::getOptions();
            // Current user allowed to write to all users?
            $write = Accessible::write('user', 'RescueMe\\User');
            $all = $this->isGranted($app, self::WRITE, $write, $user);
            foreach($users as $id => $row) {
                $row['id'] = $id;
                $row['role'] = $roles[$row['role_id']];
                $row['target'] = 'user';
                // Allowed to write to given user?
                if($all || $this->isGranted($app, self::WRITE, $write->with($row), $user)) {
                    $row['editor'] = $this->getEditorMenu($user, $row);
                }
                $users[$id] = $row;
            }
        }
        return $users;
    }


    /**
     * Get editor menu for given user
     * @param User $user Authenticated user
     * @param array|User $object Target user
     * @return array
     */
    public function getEditorMenu($user, $object) {
        $items = array();
        $object = (array)$object;
        $other = $user->id !== $object['id'];

        if($other) {
            switch($object['state']) {
                case User::PENDING:
                    $items[] = $this->createAction(
                        $object['id'], T_('Approve'), 'user/edit/id'
                    );
                    $items[] = $this->createAction(
                        $object['id'], T_('Reject'), 'user/edit/id'
                    );
                    break;

                case User::DISABLED:
                    $items[] = $this->createConfirmAction(
                        $object['id'],
                        T_('Enable'),
                        'user/edit/id',
                        sprintf(T_('Do you want to enable %1$s?'), $object['name'])
                    );
                    break;
                default:
                    $items[] = $this->createConfirmAction(
                        $object['id'],
                        T_('Disable'),
                        'user/edit/id',
                        sprintf(T_('Do you want to disable %1$s?'), $object['name'])
                    );
                    break;
            }
            $items[] = array('divider' => true);
        }
        $items[] = $this->createAction(
            $object['id'], T_('Change password'), 'user/edit/id'
        );
        $items[] = $this->createAction(
            $object['id'], T_('Reset password'), 'user/edit/id'
        );
        $items[] = array('divider' => true);
        $items[] = $this->createAction(
            $object['id'], T_('Setup'), 'user/edit/id', 'icon-wrench'
        );
        if($other) {
            $items[] = array('divider' => true);
            $items[] = $this->createConfirmAction(
                $object['id'],
                T_('Delete'),
                'user/edit/id',
                sprintf(T_('Do you want to delete %1$s?'), $object['name']),
                'icon-trash'
            );
        }

        return $items;
    }

    private function createConfirmAction($id, $title, $href, $content, $icon = false) {
        $item = $this->createAction($id, $title, $href, $icon);
        $item['confirm'] = $content;
        return $item;

    }

    private function createAction($id, $title, $href, $icon = false) {
        return array(
            'id' => $id,
            'title' => $title,
            'href' => $href,
            'icon' => $icon
        );
    }

}