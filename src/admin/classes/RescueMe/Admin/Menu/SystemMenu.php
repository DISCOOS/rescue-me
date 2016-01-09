<?php
/**
 * File containing: System menu class
 *
 * @copyright Copyright 2016 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 5. January 2016
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Admin\Menu;

use RescueMe\Admin\Security\Accessible;
use RescueMe\Menu\MenuItem;
use RescueMe\User;
use RescueMe\Menu\AbstractMenu;
use Silex\Application;

/**
 * System menu class
 * @package RescueMe\Admin\Menu
 */
class SystemMenu extends AbstractMenu {

    const NAME = 'SystemMenu';

    function __construct()
    {
        parent::__construct('system.menu.twig');
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
//        $readUser = Accessible::read('user');
        $writeUser = Accessible::write('user');

        $this->newItem(T_('Account'))
            ->setRoute('page:user/edit/id')
            ->setIcon('icon-user')
            ->setAccess($writeUser);

        $this->newItem(T_('Change password'))
            ->setRoute('page:password/change/id')
            ->setIcon('icon-lock')
            ->setAccess($writeUser);

//        $this->newItem(T_('Setup'))
//            ->seRoute('page:setup')
//            ->setIcon('icon-wrench')
//            ->setAccess($writeUser);

        $this->newDivider();

        $this->newItem(T_('New user'))
            ->setRoute('page:user/new')
            ->setIcon('icon-plus-sign')
            ->setAccess($writeUser);

//        $this->newItem(T_('Email users'))
//            ->setRoute('page:user/email')
//            ->setIcon('icon-envelope')
//            ->setAccess($writeUserAll);

        $this->newDivider();

        $this->newItem(T_('Users'))
            ->setId('users')
            ->setRoute('page:user/list')
            ->setIcon('icon-th-list')
            ->setAccess($writeUser);

//        $this->newItem(T_('Roles'))
//            ->setRoute('page:role/list')
//            ->setIcon('icon-th-list')
//            ->setAccess(Accessible::write('roles'));

        $this->newDivider();

        $this->newItem(T_('Logout'))
            ->setRoute('logout')
            ->setIcon('icon-eject')
            ->setConfirm(T_('Do you want to logout?'));

        return true;
    }


    /**
     * Parse menu template into item.
     * @param Application $app Application
     * @param MenuItem $template Menu template
     * @param User $user Authenticated user
     * @param boolean|object|array $object Resolved object
     * @return array
     */
    protected function parse(Application $app, MenuItem $template, User $user, $object = false) {
        $item = $template->toArray();
        if(isset_get($item, MenuItem::DIVIDER, false) === false) {
            if('users' === isset_get($item,MenuItem::ID)) {
                if ($count = User::count(array(User::PENDING))) {
                    $item[MenuItem::CONTENT] = ' <span class="badge badge-important">'.$count.'</span>';
                }
            } else if(endsWith(isset_get($item, MenuItem::ROUTE), '/id')) {
                $item[MenuItem::PARAMS] = array(MenuItem::ID => $user->id);
            }
        }
        return $item;
    }

}