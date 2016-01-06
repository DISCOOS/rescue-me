<?php
/**
 * File containing: Menu service class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 6. August 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Admin\Service;

use RescueMe\Admin\Core\CallableResolver;
use RescueMe\Menu\MenuInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * Menu service class
 * @package RescueMe\Admin\Service
 */
class MenuService extends CallableResolver {

    const MENU = 'menu';
    const ITEMS = MenuInterface::ITEMS;
    const ID = MenuInterface::ID;
    const LABEL = MenuInterface::LABEL;
    const HREF = MenuInterface::HREF;
    const ICON = MenuInterface::ICON;

    /**
     * Template service
     * @var TemplateService
     */
    private $template;

    /**
     * Registered menus
     * @var array|callable
     */
    private $menus = array();

    /**
     * Constructor
     * @param TemplateService $template
     */
    function __construct($template)
    {
        $this->template = $template;
    }

    /**
     * Register menu
     * @param string $name Menu name
     * @param \RescueMe\Menu\MenuInterface $menu Menu instance
     * @return boolean
     */
    public function register($name, $menu) {
        $this->menus[$name] = $menu;
    }

    /**
     * Get registered menu
     * @param string $name Menu name
     * @return boolean|MenuInterface
     */
    public function get($name) {
        return isset($this->menus[$name]) ? $this->menus[$name] : false;
    }

    /**
     * Render request into menu as html
     * @param Application $app Silex application
     * @param Request $request Request instance
     * @param string $name Menu name
     * @param array $context Menu context
     * @return boolean|string
     */
    public function render(Application $app, Request $request, $name, array $context = array()) {

        /** @var MenuInterface $menu */
        if ($menu = $this->menus[$name]) {

            $context = $menu->getContext($app, $request, $context);

            $context = array_merge($app['context'], array(self::MENU => $context));

            $menu = $this->template->render($app, $menu->getTemplate(), $context);
        }

        return $menu;

    }



}