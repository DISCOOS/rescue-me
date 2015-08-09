<?php
/**
 * File containing: Template service class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 29. July 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Admin\Service;

use RescueMe\Context;
use RescueMe\Document\Compiler;
use RescueMe\Locale;
use RescueMe\User;
use Silex\Application;

/**
 * Template service class
 * @package RescueMe\Admin\Core
 */
class TemplateService {

    /**
     * Resource root path
     * @var string
     */
    private $root;

    /**
     * Twig resource loader
     * @var \Twig_Loader_Filesystem
     */
    private $loader;


    /**
     * Constructor
     */
    function __construct() {
        $this->root = get_path(Context::getPath(), 'gui', true);
        $this->loader = new \Twig_Loader_Filesystem($this->root);
    }


    /**
     * Create template context
     * @param Application $app Silex application instance
     * @param boolean|mixed $id Page id
     * @param boolean|object $object Match page id with given object.
     * @param boolean|User $user Authorized user
     * @param array $context Page context.
     * @return array
     */
    protected function createPageContext($app, $id = false, $object = false, $user = false, $context = array())
    {
        // Set default page context
        $context['id'] = $id ? $id : null;
        $context['object'] = $object ? $object : null;
        $context['user'] = $user instanceof User ? $user : false;
        $context['secure'] = $context['user'] !== false;
        $context['locale'] = Locale::getCurrentLocale();
        $context['country'] = Locale::getCurrentCountryCode();
        $context['menu'] = $this->createMenuContext($user);
        $context['footer'] = $this->createFooter($app);

        // Merge with application context
        return array_merge(Context::toArray(true), $context);
    }

    /**
     * Create menu context
     * @param User $user Current user
     * @return string
     */
    private function createMenuContext($user) {
        if($user instanceof User) {
            return array(
                'page' => insert_trace_menu($user, 'trace', false),
                'system' => insert_system_menu($user, 'system', false)
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
        $twig->setLoader($this->loader);
        $compiler = new Compiler($this->root, $twig);
        $footer = $compiler->parse('footer');
        $twig->setLoader($loader);
        return $footer;
    }


    /**
     * Render page into html
     * @param Application $app Silex application instance
     * @param string $template Page template name
     * @param boolean|mixed $id Page id
     * @param boolean|object $object Match page id with given object.
     * @param boolean|User $user Authorized user
     * @param array $context Page context.
     * @return string
     */
    public function page(Application $app, $template, $id = false, $object = false, $user = false, $context = array())
    {
        $context = $this->createPageContext($app, $id, $object, $user, $context);

        return $this->render($app, $template, $context);

    }


    /**
     * Render template
     * @param Application $app
     * @param string $template
     * @param array $context
     * @return string
     */
    public function render(Application $app, $template, array $context) {

        /** @var \Twig_Environment $twig */
        $twig = $app['twig'];

        return $twig->render($template, $context);

    }

}