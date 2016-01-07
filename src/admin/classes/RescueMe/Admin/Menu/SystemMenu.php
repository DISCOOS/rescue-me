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
        $readUser = Accessible::read('user');
        $writeUser = Accessible::write('user');
        $writeUserAll = Accessible::write('user.all');

        $this->newItem(T_('Account'),'user/edit/id')
            ->setIcon('icon-user')
            ->setAccess($writeUser);

        $this->newItem(T_('Change password'),'password/change/id')
            ->setIcon('icon-lock')
            ->setAccess($writeUser);

//        $this->newAction(T_('Setup'),'setup')
        $this->newItem(T_('Setup'),'user/edit/id')
            ->setIcon('icon-wrench')
            ->setAccess($writeUser);

        $this->newDivider()
            ->setAccess($writeUser);

//        $this->newAction(T_('Setup'),'user/new')
        $this->newItem(T_('New user'),'user/edit/id')
            ->setIcon('icon-plus-sign')
            ->setAccess($writeUserAll);

//        $this->newAction(T_('Email users'),'user/email')
        $this->newItem(T_('Email users'),'user/edit/id')
            ->setIcon('icon-envelope')
            ->setAccess($writeUserAll);

        $this->newDivider()
            ->setAccess($writeUserAll);

//        $this->newAction(T_('Users'),'user/list')
        $this->newItem(T_('Users'),'user/edit/id')
            ->setId('users')
            ->setIcon('icon-th-list')
            ->setAccess($writeUserAll);

//        $this->newAction(T_('Users'),'roles/list')
        $this->newItem(T_('Roles'),'user/edit/id')
            ->setIcon('icon-th-list')
            ->setAccess(Accessible::write('roles'));

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
        if(!$item[MenuItem::DIVIDER]) {
            $item[MenuItem::PARAMS] = array(MenuItem::ID => $user->id);
            if('users' === $item[MenuItem::ID]) {
                if ($count = User::count(array(User::PENDING))) {
                    $item[MenuItem::CONTENT] = ' <span class="badge badge-important">'.$count.'</span>';
                }
            }
        }
        return $item;
    }

}