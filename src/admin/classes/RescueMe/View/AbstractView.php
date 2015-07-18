<?php
/**
 * File containing: Abstract view class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 28. April 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\View;

use Twig_Environment;


/**
 * Class AbstractView
 * @package RescueMe\View
 */
abstract class AbstractView {

    /**
     * Twig instance
     *
     * @var Twig_Environment
     */
    protected $twig;

    /**
     * View name
     *
     * @var string
     */
    protected $name;

    /**
     * Default constructor
     *
     * @param $twig
     * @param $name
     */
    function __construct($twig, $name) {
        $this->twig = $twig;
        $this->name = $name;
    }


    /**
     * Render view
     * @param array $context
     * @return string View markup
     */
    public function render(array $context = array()) {
        return $this->twig->render($this->name, $context);
    }

}