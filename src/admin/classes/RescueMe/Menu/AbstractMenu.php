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
     * Get menu items
     * @param Application $app Silex application instance
     * @param Request $request Request instance
     * @param array $context Menu context
     * @return array Menu context as array
     */
    public function getContext(Application $app, Request $request, array $context = array())
    {
        // Called if not initialized.
        if($this->init)
            $this->init = !$this->configure();

        // Create menu parent context?
        if ($this->menu) {
            $context = $this->toItem($app, $request, $this->menu, $app['context']);
        }
        $context[self::ITEMS] = array();

        // Create menu items
        foreach($this->items as $template) {

            if(($item = $this->toItem($app, $request, $template, $app['context'])) !== false) {
                $context[self::ITEMS][] = $item;
            };
        }
        return $context;
    }

    private function toItem(Application $app, Request $request, $template, $context) {

        $args = array();

        /* @param MenuItem $template */
        $item = $template->toArray();
        $adapter = $template->getAdapter();
        $selector = $template->getSelector();

        $context['item'] = $item;

        // Build item?
        if($method = $this->getMethod($adapter)) {
            $args = $this->getArguments($method, $app, $request, $app['context']['user'], $context);
            /** @var callable $builder */
            $item = call_user_func_array($adapter, $args);
        }

        // Prepare conditional select?
        if($method = $this->getMethod($selector)) {
            $args = $this->getArguments($method, $app, $request, $app['context']['user'], $context);
        }

        return $selector === false || call_user_func_array($selector, $args) ? $item : false;

    }

    /**
     * Create menu
     * @param string $label Menu label
     * @return MenuItem
     */
    protected function newMenu($label)
    {
        $this->menu = new MenuItem();
        $this->menu->setLabel($label);
        return $this->menu;
    }


    /**
     * Add menu item
     * @param string $label Action label
     * @param string $href Uri to action
     * @return MenuItem
     */
    protected function newAction($label, $href)
    {
        $item = new MenuItem();
        $item->setLabel($label);
        $item->setHref($href);
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

    public function canRead(Application $app, $user, $object) {
        if ($object === false) {
            $object = $app['context'][Context::ACCESSIBLE];
        }
        return $app['security']->isGranted(Accessible::READ, $object, $user);
    }

    public function canWrite(Application $app, $user, $object) {
        if ($object === false) {
            $object = $app['context'][Context::ACCESSIBLE];
        }
        return $app['security']->isGranted(Accessible::WRITE, $object, $user);
    }

    public function isGranted(Application $app, $user, $object, $route) {
        return $app['security']->isGranted('write', $object, $user);
    }


}