<?php
/**
 * File containing: User editor menu class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 10. August 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Admin\Menu\User;

use RescueMe\Admin\Security\Accessible;
use RescueMe\Menu\MenuItem;
use RescueMe\User;
use RescueMe\Menu\AbstractMenu;
use Silex\Application;

/**
 * User editor menu class
 * @package RescueMe\Admin\Menu
 */
class EditorMenu extends AbstractMenu {

    const NAME = 'UserEditorMenu';

    function __construct()
    {
        parent::__construct('editor.twig');
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
        $isOther = array($this, 'isOther');
        $isPending = array($this, 'isPending');

        $this->newMenu(T_('Edit'))
            ->setId('edit')
            ->setRoute('user/edit/id');

//        $this->newItem(T_('Approve'))
//            ->setRoute('user/approve/id')
//            ->setSelector($isPending);
//
//        $this->newItem(T_('Reject'))
//            ->setRoute('user/reject/id')
//            ->setSelector($isPending);
//
//        $this->newItem(T_('Enable'))
//            ->setRoute('user/enable/id')
//            ->setConfirm(T_('Do you want to enable %1$s?'))
//            ->setSelector(array($this, 'isDisabled'));
//
//        $this->newDivider()
//            ->setSelector($isPending);

        $this->newItem(T_('Reset password'))
            ->setRoute('password/reset/id');

        $this->newItem(T_('Change password'))
            ->setRoute('password/change/id');

//        $this->newDivider();
//
//        $this->newItem(T_('Setup'))
//            ->setRoute( 'user/edit/id')
//            ->setIcon('icon-wrench');

//        $this->newDivider()
//            ->setSelector($isOther);
//
//        $this->newItem(T_('Delete'))
//            ->setRoute('user/delete/id')
//            ->setConfirm(T_('Do you want to delete %1$s?'))
//            ->setIcon('icon-trash')
//            ->setSelector($isOther);

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
            if(isset($item['confirm'])) {
                $item['confirm'] = sprintf($item['confirm'], is_object($object) ? $object->name : $object['name']);
            }
            if(endsWith(isset_get($item, MenuItem::ROUTE), '/id')) {
                $item[MenuItem::PARAMS] = array(MenuItem::ID => is_object($object) ? $object->id : $object['id']);
            }
        }
        return $item;
    }


}