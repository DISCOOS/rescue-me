<?php
/**
 * File containing: Menu interface
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 10. August 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Menu;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * Menu interface
 * @package RescueMe\Admin\Menu
 */
interface MenuInterface {

    const ID = MenuItem::ID;
    const LABEL = MenuItem::LABEL;
    const HREF = MenuItem::HREF;
    const ICON = MenuItem::ICON;

    const ITEMS = 'items';

    /**
     * Get template name
     * @return string
     */
    public function getTemplate();

    /**
     * Get menu template context
     * @param Application $app Silex application instance
     * @param Request $request Request instance
     * @param array $context Menu context
     * @return array Menu items
     */
    public function getContext(Application $app, Request $request, array $context = array());

}