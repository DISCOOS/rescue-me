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
     * @param string $root Template filesystem root path
     */
    function __construct($root) {
        $this->root = $root;
        $this->loader = new \Twig_Loader_Filesystem($this->root);
    }

    /**
     * @return \Twig_Loader_Filesystem
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * @return string
     */
    public function getRoot()
    {
        return $this->root;
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