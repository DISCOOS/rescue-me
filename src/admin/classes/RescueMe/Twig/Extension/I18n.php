<?php
/**
 * File containing: Internationalization support class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 30. July 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Twig\Extension;


use Twig_Extension;
use Twig_SimpleFilter;
use Twig_SimpleFunction;

/**
 * Internationalization support.
 *
 * @package RescueMe\Admin\Core
 */
class I18n extends \Twig_Extension {

    public function getFilters() {
        return array_merge(parent::getFilters(), array(
                new Twig_SimpleFilter('T', 'T_'),
                new Twig_SimpleFilter('T_locale', 'T_locale'),
                new Twig_SimpleFilter('dial_code', array('RescueMe\\Locale','getDialCode')),
            ));
    }

    public function getFunctions() {
        return array(
                new Twig_SimpleFunction('T_', 'T_'),
                new Twig_SimpleFunction('T_locale', 'T_locale'),
                new Twig_SimpleFunction('dial_code', array('RescueMe\\Locale','getDialCode')),
        );
    }


    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'i18n';
    }
}

