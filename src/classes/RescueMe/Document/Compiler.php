<?php
/**
 * File containing: Markdown compiler
 *
 * @copyright Copyright 2014 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 15. August 2014
 *
 * @author Kenneth Gulbrandsøy <discoos.org>
 */

namespace RescueMe\Document;

use RescueMe\Locale;
use RescueMe\User;

/**
 * Class Compiler
 *
 * @package RescueMe\Document
 */
class Compiler {

    /**
     * @var string
     */
    private $root;

    /**
     * @var \Twig_Loader_Filesystem
     */
    private $loader;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var \ParsedownExtra
     */
    private $parser;

    /**
     * @var array
     */
    private $properties;

    /**
     * Constructor. Initializes compiler resources.
     */
    public function __construct($root, $loader = false) {

        $this->root = $root;

        if($loader instanceof \Twig_Loader_Filesystem)
            $this->loader = $loader;
        else
            $this->loader = new \Twig_Loader_Filesystem($root.'layout');

        $this->twig = new \Twig_Environment($this->loader);
        $this->parser = new \ParsedownExtra();

    }

    private function isMarkdown($filename) {
        return file_exists($filename) &&
            in_array(strtolower(pathinfo($filename, PATHINFO_EXTENSION)), array('md'));
    }


    private function getConfig($content) {

        $config = '';
        $content = trim($content);

        $i = strpos($content, '---');
        if ($i === 0)
        {
            $i += 3;
            $n = strpos($content, '---', $i) - $i;
            $config = substr($content, $i, $n);

        }

        $content = str_replace('---'.$config.'---', '', $content);

        $lines = preg_split('#\R#', $config);
        $config = array();
        foreach($lines as $line) {
            $line = trim($line, '\n\r');
            $line = explode(':', $line);
            if(count($line) === 2) {
                $config[trim($line[0])] = trim($line[1]);
            }
        }

        return array($config, $content);

    }

    private function replace($content, $properties) {
        foreach($properties as $name => $value) {

            $pattern = preg_quote('{{'.$name.'}}');
            $content = preg_replace("#$pattern#i", $value, $content);

        }

        return $content;

    }

    public function get($name) {

        return isset_get($this->getProperties(), $name,'');

    }


    public function set($name,  $value) {

        $this->getProperties();

        $old = isset_get($this->properties, $name,'');

        $this->properties[$name] = $value;

        return $old;

    }


    public function getAll() {

        if(isset($this->properties) === false) {

            $user = User::current();

            $this->properties = array(
                'title' => TITLE,
                'version' => defined('VERSION') ? VERSION : VERSION_NOT_SET,
                'user' => $user === false ? T_('System') : $user->name
            );
        }

        return $this->properties;
    }


    public function parse($name, $locale = true) {

        $html = false;

        if($locale === true) {
            $locale = Locale::getCurrentLocale();
        }

        if($locale && $locale !== 'en_US') {
            $filename = implode(DIRECTORY_SEPARATOR, array('locale', $locale, $name));
        } else {
            $filename = $name;
        }

        $filename = $this->root . $filename . '.md';

        if($this->isMarkdown($filename)) {

            $content = file_get_contents($filename);

            $content = $this->replace($content, $this->getAll());

            list($config, $content) = $this->getConfig($content);

            $content = $this->parser->text($content);

            $title = isset_get($config,'title',TITLE);
            $layout = isset_get($config,'layout','default') . '.twig';

            try {

                $html = $this->twig->render($layout,
                    array(
                        'title' => $title,
                        'content' => $content
                    ));

            }
            catch(\Twig_Error_Loader $e)
            {
                return $e->getMessage();
            }
        } else if($locale) {

            // Retry default locale
            return $this->parse($name, false);

        }

        return $html;
    }
}