<?php
/**
 * File containing: Finite state machine exception class
 *
 * @copyright Copyright 2015 {@link http://www.discoos.org DISCO OS Foundation}
 *
 * @since 28. March 2015
 *
 * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
 */

namespace RescueMe\Finite;

/**
 * Class FiniteException
 * @package RescueMe\Finite
 */
class FiniteException extends \Exception {

    const NOT_FOUND = 1;
    const ITEM_EXISTS = 2;
    const ILLEGAL_STATE = 3;
    const ILLEGAL_TYPE = 4;

}