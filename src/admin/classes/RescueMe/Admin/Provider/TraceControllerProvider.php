<?php
/**
 * File containing: Trace pages controller class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 2. August 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Admin\Provider;

use RescueMe\Missing;
use Silex\Application;
use Silex\ControllerCollection;

/**
 * Trace pages controller class
 * @package RescueMe\Controller
 */
class TraceControllerProvider extends AbstractControllerProvider {

    const REDIRECT = '/admin/trace';

    /**
     * Constructor
     */
    function __construct() {
        parent::__construct('trace');
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
        // Creates a new parent controller based on the default route
        $controllers = $app['controllers_factory'];

        // Register redirects
        $this->redirect($controllers, 'view', self::REDIRECT);

        $read = $this->read($app, 'user', 'RescueMe\\User', false);

        // Handle admin/trace/list
        $this->page($controllers, 'list', $read);

        $this->page($controllers, '{id}', $read->with(function($id) {
                return Missing::get($id);
            })
        )->assert('id', '\d+');

        $write = $this->write($app, 'user', 'RescueMe\\User', false);

        // Handle admin/trace/new
        $this->page($controllers, 'new', $write);

        // Handle admin/trace/edit/{id}
        $this->page($controllers, 'edit/{id}', $write->with(function($id) {
                return Missing::get($id);
            })
        )->assert('id', '\d+');

        return $controllers;
    }
}