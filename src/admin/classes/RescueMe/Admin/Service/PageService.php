<?php
/**
 * File containing: Page service class
 *
 * @copyright Copyright 2016 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 3. January 2016
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Admin\Service;

use RescueMe\Admin\Context;
use RescueMe\Admin\Menu\SystemMenu;
use RescueMe\Admin\Provider\MenuServiceProvider;
use RescueMe\Document\Compiler;
use RescueMe\Locale;
use RescueMe\User;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * Page service class
 * @package RescueMe\Admin\Core
 */
class PageService {

    const ALERTS = 'alerts';
    const LOCALE = 'locale';
    const COUNTRY = 'country';
    const SECURE = 'secure';
    const MENU = 'menu';
    const FOOTER = 'footer';

    /**
     * @var TemplateService
     */
    private $template;

    /**
     * @var string
     */
    private $menu;

    /**
     * Create page service
     * @param TemplateService $template
     */
    function __construct($template)
    {
        $this->template = $template;
    }

    /**
     * Set page menu name
     * @param string $menu
     */
    public function setMenu($menu)
    {
        $this->menu = $menu;
    }

    /**
     * Get page menu name
     * @return string
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * Create template context
     * @param Application $app Silex application instance
     * @param Request $request Request object.
     * @return array
     */
    protected function createPageContext(Application $app, Request $request)
    {
        // Set default page context
        $context[self::LOCALE] = Locale::getCurrentLocale();
        $context[self::COUNTRY] = Locale::getCurrentCountryCode();
        $context[self::SECURE] = $app['context'][Context::USER] !== false;
        $context[self::MENU] = $this->createMenuContext($app, $request, $app['context'][Context::USER]);
        $context[self::FOOTER] = $this->createFooter($app);

        // Merge with application context
        return array_merge(Context::toArray(true), $context);
    }

    /**
     * Create menu context
     * @param Application $app Silex application instance
     * @param Request $request Request object.
     * @param boolean|User $user Authenticated user
     * @return string
     */
    private function createMenuContext(Application $app, Request $request, $user) {
        if($user instanceof User) {
            $menus = MenuServiceProvider::get($app);
            return array(
                'page' => isset($this->menu) ? $menus->render($app, $request, $this->menu) : '',
                'system' => $menus->render($app, $request, SystemMenu::NAME),
            );
        }
        return false;
    }

    /**
     * Create footer
     * @param Application $app Silex application instance
     * @return bool|string
     */
    private function createFooter($app) {
        /** @var \Twig_Environment $twig */
        $twig = $app['twig'];
        $loader = $twig->getLoader();
        $twig->setLoader($this->template->getLoader());
        $compiler = new Compiler($this->template->getRoot(), $twig);
        $footer = $compiler->parse('footer');
        $twig->setLoader($loader);
        return $footer;
    }


    /**
     * Render page into html
     * @param Application $app Silex application instance
     * @param Request $request Request object.
     * @param string $template Page template name
     * @param array $context Page context.
     * @return string
     */
    public function page(Application $app, Request $request, $template, $context = array())
    {
        $context = array_merge($this->createPageContext($app, $request), $context);

        return $this->template->render($app, $template, $context);

    }

}