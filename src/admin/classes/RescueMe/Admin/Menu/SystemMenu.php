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

use RescueMe\User;
use RescueMe\Menu\AbstractMenu;

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
        $adaptUser = array($this, 'adaptUser');
//        $pending = array($this, 'isPending');
        $canWrite = array($this, 'canWrite');


        $this->newAction(T_('Account'),'user/edit/id')
            ->setSelector($canWrite)
            ->setAdapter($adaptUser);

//        $notUser = array($this, 'isNotSessionUser');

//        $this->newMenu(T_('System'))->setId('system');


//        $this->newAction(T_('Approve'), 'user/edit/id')
//            ->setSelector($pending)
//            ->setAdapter($adapter);
//
//        $this->newAction(T_('Reject'), 'user/edit/id')
//            ->setSelector($pending)
//            ->setAdapter($adapter);
//
//        $this->newAction(T_('Enable'), 'user/edit/id')
//            ->setConfirm(T_('Do you want to enable %1$s?'))
//            ->setSelector(array($this, 'isDisabled'))
//            ->setAdapter($adapter);
//
//        $this->newDivider()
//            ->setSelector($pending);
//
//        $this->newAction(T_('Change password'), 'user/edit/id')
//            ->setAdapter($adapter);
//
//        $this->newAction(T_('Reset password'), 'user/edit/id')
//            ->setAdapter($adapter);
//
//        $this->newDivider();
//
//        $this->newAction(T_('Setup'), 'user/edit/id')
//            ->setIcon('icon-wrench')
//            ->setAdapter($adapter);
//
//        $this->newDivider()
//            ->setSelector($notUser);
//
//        $this->newAction(T_('Delete'), 'user/edit/id')
//            ->setConfirm(T_('Do you want to delete %1$s?'))
//            ->setIcon('icon-trash')
//            ->setSelector($notUser)
//            ->setAdapter($adapter);

        return true;
    }

    /**
     * Check if user is pending approval
     * @param array|User $object Menu user
     * @return boolean
     */
    public function isPending($object) {
        return is_object($object) ? $object->isState(User::PENDING) :
            User::PENDING === $object['state'];
    }

    /**
     * Check if user is disabled
     * @param array|User $object Menu user
     * @return boolean
     */
    public function isDisabled($object) {
        return is_object($object) ? $object->isState(User::DISABLED) :
            User::DISABLED === $object['state'];
    }

    /**
     * Check if object is same as logged in user
     * @param array|User $user Logged in user
     * @param array|User $object Menu target user
     * @return boolean
     */
    public function isNotSessionUser(User $user, $object) {
        return is_object($object) ? $user->id !== $object->id :
            $user->id !== $object['id'];
    }

    /**
     * Build item
     * @param array $item Item definition
     * @param array|User $user Authenticated user
     * @return array
     */
    public function adaptUser($item, $user) {
        $item[self::ID] = $user->id;
        return $item;
    }


}