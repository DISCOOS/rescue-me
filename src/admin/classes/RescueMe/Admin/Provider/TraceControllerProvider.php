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

    const REDIRECT = '/admin/missing';

    /**
     * Constructor
     */
    function __construct() {
        parent::__construct('missing');
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

        // Register routes
        $this->page($controllers, 'list', $read);

        $this->page($controllers, '/', $read->with(function($id) {
                return Missing::get($id);
            })
        );

        $write = $this->write($app, 'user', 'RescueMe\\User', false);

        $this->page($controllers, 'new', $write);

        $this->page($controllers, 'edit', $write->with(function($id) {
                return Missing::get($id);
            })
        );

        return $controllers;
    }
}