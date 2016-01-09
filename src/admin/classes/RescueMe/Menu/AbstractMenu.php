<?php
/**
 * File containing: Abstract menu class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 10. August 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Menu;

use RescueMe\Admin\Context;
use RescueMe\Admin\Core\CallableResolver;
use RescueMe\Admin\Security\Accessible;
use RescueMe\User;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * Abstract menu class
 * @package RescueMe\Admin\Menu
 */
abstract class AbstractMenu extends CallableResolver implements MenuInterface {

    /**
     * Rebuild flag
     * @var boolean
     */
    private $init = true;

    /**
     * Menu parent item
     * @var MenuItem
     */
    private $menu = false;

    /**
     * Twig template name
     * @var string Template name
     */
    private $template;

    /**
     * Menu item templates
     * @var array
     */
    private $items = array();


    /**
     * Default constructor
     *
     * @param string $template
     */
    function __construct($template = 'menu.twig')
    {
        $this->template = $template;
    }

    /**
     * Get template name
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }


    /**
     * Called if not initialized.
     *
     * Return true if initialized, false otherwise (will be called again)
     *
     * @return boolean
     */
    abstract protected function configure();

    /**
     * Get menu context
     * @param Application $app Silex application instance
     * @param Request $request Request instance
     * @param array $context Menu context
     * @return array Menu context as array
     */
    public function getContext(Application $app, Request $request, array $context = array())
    {
        $menu = array();
        $previous = null;

        // Called if not initialized.
        if($this->init)
            $this->init = !$this->configure();

        // Get authenticated user
        $user = $app['context']['user'];

        // Create menu parent context?
        if ($this->menu) {
            $menu = $this->toItem($app, $request, $user, $this->menu, $context);
        }
        $menu[self::ITEMS] = array();

        // Create menu items
        foreach($this->items as $template) {

            // Prevent two consecutive dividers being added
            if(!$template->isDivider() || !$previous || !$previous->isDivider()) {

                if(($item = $this->toItem($app, $request, $user, $template, $context)) !== false) {
                    $menu[self::ITEMS][] = $item;
                }
            }
            $previous = $template;
        }
        return $menu;
    }

    /**
     * @param Application $app
     * @param Request $request
     * @param User $user Authenticated user
     * @param MenuItem $template MenuItem template
     * @param array $context Menu context
     * @return boolean|mixed
     */
    private function toItem(Application $app, Request $request, $user, $template, $context) {

        $item = false;

        if($this->isGranted($app, $template)) {

            $context['template'] = $template;

            // Prepare item selector?
            $selector = $template->getSelector();
            if($method = $this->getMethod($selector)) {
                $args = $this->getArguments($method, $app, $request, $user, $context);
                if(call_user_func_array($selector, $args) === false) {
                    return false;
                }
            }

            // Parse menu template into item?
            $parser = $template->getParser();
            if($method = $this->getMethod($parser)) {
                $args = $this->getArguments($method, $app, $request, $user, $context);
                $item = call_user_func_array($parser, $args);
            } else {
                $item = $this->parse($app, $template, $user, isset_get($context, 'object', false));
            }

            // Implode attributes?
            if(isset($item[MenuItem::ATTRIBUTES])) {
                $attributes = array();
                foreach($item[MenuItem::ATTRIBUTES] as $key => $value) {
                    $attributes[] = $key . '="' . $value . '"';
                }
                $item[MenuItem::ATTRIBUTES] = implode(" ", $attributes);
            }

        }

        return $item;

    }

    /**
     * Create menu
     * @param string $label Menu label
     * @param boolean|string $icon Item icon
     * @return MenuItem
     */
    protected function newMenu($label, $icon = false)
    {
        $this->menu = new MenuItem();
        $this->menu->setLabel($label);
        if($icon) $this->menu->setIcon($icon);
        return $this->menu;
    }


    /**
     * Add menu item
     * @param string $label Item label
     * @param boolean|string $icon Item icon
     * @return MenuItem
     */
    protected function newItem($label, $icon = false)
    {
        $item = new MenuItem();
        $item->setLabel($label);
        if($icon) $item->setIcon($icon);
        $this->items[] = $item;
        return $item;
    }


    /**
     * Add menu divider
     */
    protected function newDivider()
    {
        $item = new MenuItem();
        $item->setDivider();
        $this->items[] = $item;
        return $item;
    }

    /**
     * Assert if authenticated user is authorized to invoke menu action
     * @param Application $app
     * @param MenuItem $item
     * @return mixed
     */
    protected function isGranted(Application $app, MenuItem $item) {
        if (($object = $item->getAccess()) === false) {
                $object = $app['context'][Context::ACCESSIBLE];
        }
        return $app['security']->isGranted(Accessible::WRITE, $object, $app['context']['user']);
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
        return $item;
    }


}