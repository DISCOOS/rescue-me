<?php
/**
 * File containing: Trace menu class
 *
 * @copyright Copyright 2016 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 5. January 2016
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Admin\Trace;

use RescueMe\Admin\Security\Accessible;
use RescueMe\Menu\MenuItem;
use RescueMe\User;
use RescueMe\Menu\AbstractMenu;
use Silex\Application;

/**
 * Trace menu class
 * @package RescueMe\Admin\Menu
 */
class TraceMenu extends AbstractMenu {

    const NAME = 'TraceMenu';

    function __construct()
    {
        parent::__construct('trace.menu.twig');
    }

    /**
     * Called if not initialized.
     *
     * Return true if initialized, false otherwise (will be called again)
     *
     * @return boolean
     */
    protected function configure()
    {
        // Access rights
        $write = Accessible::write('operations');

        $this->newItem(T_('New trace'))
            ->setRoute('page:trace/new')
            ->setIcon('icon-plus-sign')
            ->setAccess($write);

        $this->newDivider();

        $this->newItem(T_('Traces'))
            ->setRoute('page:trace/list')
            ->setIcon('icon-th-list')
            ->setAccess($write);

        return true;
    }

}