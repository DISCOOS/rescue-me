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
        $parser = array($this, 'parse');
        $pending = array($this, 'isPending');
        $notUser = array($this, 'isNotSessionUser');

        $this->newMenu(T_('Edit'))
            ->setRoute('user/edit/id')
            ->setParser($parser);

        $this->newItem(T_('Approve'), 'user/edit/id')
            ->setSelector($pending)
            ->setParser($parser);

        $this->newItem(T_('Reject'), 'user/edit/id')
            ->setSelector($pending)
            ->setParser($parser);

        $this->newItem(T_('Enable'), 'user/edit/id')
            ->setConfirm(T_('Do you want to enable %1$s?'))
            ->setSelector(array($this, 'isDisabled'))
            ->setParser($parser);

        $this->newDivider()
            ->setSelector($pending);

        $this->newItem(T_('Change password'), 'user/edit/id')
            ->setParser($parser);

        $this->newItem(T_('Reset password'), 'user/edit/id')
            ->setParser($parser);

        $this->newDivider();

        $this->newItem(T_('Setup'), 'user/edit/id')
            ->setIcon('icon-wrench')
            ->setParser($parser);

        $this->newDivider()
            ->setSelector($notUser);

        $this->newItem(T_('Delete'), 'user/edit/id')
            ->setConfirm(T_('Do you want to delete %1$s?'))
            ->setIcon('icon-trash')
            ->setSelector($notUser)
            ->setParser($parser);

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
        if(isset($item['confirm'])) {
            $item['confirm'] = sprintf($item['confirm'],(is_object($object) ? $object->name : $object['name']));
        }
        $item = $template->toArray();
        if($item[MenuItem::DIVIDER] === false) {
            switch($item[MenuItem::ROUTE]) {
                case 'user/edit/id':
                    $item[MenuItem::PARAMS] = array(MenuItem::ID => $user->id);
                    break;
                default:
                    $item[MenuItem::PARAMS] = array(MenuItem::ID => $object[self::ID]);
                    break;
            }
        }
        return $item;
    }


}